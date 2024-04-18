<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MsLandingPage extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('MsLandingPageModel');
		$this->load->model('MsConfigModel');
		$this->ConfigType = 'LANDING PAGE';
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function getBhaktiURL(){
		$result = $this->MsConfigModel->GetConfigValue($this->ConfigType,'URL');
		for($i=0;$i<count($result);$i++) {
			if(trim($result[$i]->ConfigName)=='URL'){
				return $result[$i]->ConfigValue;
			}
		}
		return null;
	}

	public function GetGroup(){
		$x = explode(".",$_SESSION['logged_in']['username']);
		$GROUP = end($x);
		if($GROUP=='KG' || $GROUP=='PTRI' || $GROUP=='TIN'){
			return $GROUP;
		}
		else return 'ALL'; //JIKA BUKAN USER PABRIK MAKA GROUP ALL
	}
	
	public function index()
	{
		$data = array();
		$MslandingPage = $this->MsLandingPageModel->GetList();
		$merks = $this->GetMerkListByGroup();
		$data["merks"] = $merks;
		$data["result"] = $MslandingPage;
		
		$this->RenderView('MsLandingPageView',$data);
	}
	
	public function TipeBarang()
	{
		$data = array();
		$MslandingPage = $this->MsLandingPageModel->GetListTipeBarang();
		$data['merks'] = $this->GetMerkListByGroup();
		$data["result"] = $MslandingPage;
		// echo json_encode($data);die;
		$this->RenderView('MsLandingPageTipeBarangView',$data);
	}

	public function GetList()
	{
		$id= $this->input->get('id');
		$MslandingPage = $this->MsLandingPageModel->GetList($id);
		
		$MslandingPage->qrcode = $this->CreateQRCode($MslandingPage->url);
		$MslandingPage->filename = $this->CreateQRCodeName($MslandingPage->merk,$MslandingPage->tipe,$MslandingPage->lokasi_qr_code,$MslandingPage->created_date);
		// echo json_encode($MslandingPage->qrcode);die;
		echo json_encode($MslandingPage);
	}

	public function GetListTipeBarang()
	{
		$id= $this->input->get('id');
		$data = array();
		$MsLandingPage = $this->MsLandingPageModel->GetListTipeBarangById($id);
		
		$MsLandingPage->barang = $this->ListBarang($MsLandingPage->merk);
		$data["result"] = $MsLandingPage;
		echo json_encode($MsLandingPage);
	}
	
	public function GetMerkListByGroup()
	{
		$result = $this->MsConfigModel->GetConfigValue($this->ConfigType, 'MERK', $this->GetGroup());
		$merk = array();
		foreach($result as $row){
			array_push($merk, $row->ConfigValue);
		}
		return $merk;
	}
	
	public function ListBarang($merk='')
	{
		$barang = json_decode(file_get_contents($this->API_URL."/index.php/MsBarang/BarangListExcludeHadiahGET?api=APITES&mishirin=1"), true);
		// echo json_encode($barang);die;
		if ($barang["result"]=="sukses") {
			$barangpermerk = array();
				foreach($barang["data"] as $d){
					if($d['MERK']==$merk){
						array_push($barangpermerk, $d['KD_BRG'].' | '.$d['NM_BRG']);
					}
				}
				return $barangpermerk;
		} else {
			return array();
		}
	}
	
	public function GetBarangList()
	{
		$merk = '';
		if($this->input->get('merk')){
			$merk = $this->input->get('merk');
		}
		echo json_encode($this->ListBarang($merk));
	}
	
	public function GetMerkList()
	{
		$merk = $this->MsConfigModel->GetConfigValue($this->ConfigType, 'MERK', $this->GetGroup());
		return $merk;
	}

	public function Add()
	{
		$merk = array();
		$merk_group = array();
		$result = $this->GetMerkList();
		for($i=0;$i<count($result);$i++) {
			$merk[] = trim($result[$i]->ConfigValue);
			$merk_group[] =  array('merk'=>trim($result[$i]->ConfigValue), 'group'=> trim($result[$i]->Group));
		}		
		// echo json_encode($result);die;
		
		$tipe= array();
		$tipe_merk= array();
		$ListTipeBarang = $this->MsLandingPageModel->GetListTipeBarang($merk);
		for($i=0;$i<count($ListTipeBarang);$i++) {
			$tipe[] = trim($ListTipeBarang[$i]->tipe);
			$group = '';
			for($j=0;$j<count($merk_group);$j++) {
				if(trim($ListTipeBarang[$i]->merk)==$merk_group[$j]['merk']){
					$group = $merk_group[$j]['group'];
				}
			}
			$tipe_merk[] =  array('tipe'=>trim($ListTipeBarang[$i]->tipe), 'merk'=> trim($ListTipeBarang[$i]->merk), 'group'=> $group);
		}
		
		$group_param= array();
		$result = $this->MsConfigModel->GetConfigValue($this->ConfigType,'PARAM', $this->GetGroup());
		for($i=0;$i<count($result);$i++) {
			// $param[] = trim($result[$i]->ConfigValue);
			$group_param[] = array('group'=>trim($result[$i]->Group),'param'=>trim($result[$i]->ConfigValue));
		}
		
		$lokasi_qrcode= array();
		$result = $this->MsConfigModel->GetConfigValue($this->ConfigType,'LOKASI QR CODE');
		for($i=0;$i<count($result);$i++) {
			$lokasi_qrcode[] = array( 'LokasiQRCode'=> trim($result[$i]->ConfigValue),'AddInfo'=> trim($result[$i]->AddInfo),  'AddInfoParam'=> trim($result[$i]->AddInfoParam));
		}
		
		$data = array();
		$data['LokasiQRCode'] = $lokasi_qrcode;
		$data["tipe"] = $tipe; //untuk autocomplete
		$data["tipe_merk"] = $tipe_merk;
		$data["group_param"] = $group_param;
		// echo json_encode($data);die;
		$this->RenderView('MsLandingPageAdd',$data);
	}
	
	public function TipeBarangAdd()
	{
		$data = array();
		$data['LokasiQRCode'] = $this->MsLandingPageModel->GetListLokasiQRCode();
		$data['merks'] = $this->GetMerkListByGroup();
		$this->RenderView('MsLandingPageTipeBarangAdd',$data);
	}

	public function Edit($id)
	{
		$data = array();
		$MslandingPage = $this->MsLandingPageModel->GetList($id);
		$data["result"] = $MslandingPage;
		$this->RenderView('MsLandingPageEdit',$data);
	}

	public function Insert()
	{
		$post = $this->PopulatePost();
		// echo json_encode($post);die;
		
		// CEK APAKAH ADA PARAM YG SAMA
		// PARAM LAMA
		$params = array();
		$param_names = array();
		$param_values = array();
		if(ISSET($post['old_param_name'])){
			for($i=0;$i<count($post['old_param_name']);$i++){
				$params[] = array('name'=>$post['old_param_name'][$i],'value'=>$post['old_param_value'][$i]);
			}
		}
		
		// PARAM BARU
		if(ISSET($post['param_name'])){
			for($i=0;$i<count($post['param_name']);$i++){
				if (in_array($post['param_name'][$i], array_column($params,'name'))){
					//NOTIF ADA KESAMAAN NAMA PARAM
					$msg = array();
					$msg['result'] = "FAILED";
					$msg['message'] = 'Param '.$post['param_name'][$i].' sudah ada sebelumnya!';
					echo json_encode($msg);
					die;
				}
				else{
					$params[] = array('name'=>$post['param_name'][$i],'value'=>$post['param_value'][$i]);
				}
			}
		}
		// echo json_encode($params);die;
		
		//CREATE URL
		$url = $this->getBhaktiURL();
		
		$url .= "?tipe=".urlencode($post['tipe']);
		$url .= "&merk=".urlencode($post['merk']);
		$url .= "&lokasi_qr_code=".urlencode($post['lokasi_qr_code']);
		
		if($post['AddInfoParamName']!=''){
			$url .= "&".$post['AddInfoParamName']."=".urlencode($post['AddInfoParam']);
		}
		
		foreach($params as $param){
			$url .= "&".$param['name']."=".urlencode($param['value']);
		}
		
		// echo json_encode($url); die;
		
		$post['url'] = $url;
		$result = $this->MsLandingPageModel->Insert($post, $params);
		
		if($result=='SUKSES'){
			$msg = array();
			$msg['result'] = "SUKSES";
			$msg['message'] = "Data Berhasil Disimpan";
			$msg['url'] = $url;
			$msg['qrcode'] = $this->CreateQRCode($url);
			$msg['lokasi_qr_code'] = $post['lokasi_qr_code'];
			$msg['filename'] = $this->CreateQRCodeName($post['merk'],$post['tipe'],$post['lokasi_qr_code'],date('Y-m-d'));
			echo json_encode($msg);
		}
		else{
			$msg = array();
			$msg['result'] = "FAILED";
			$msg['message'] = $result;
			echo json_encode($msg);
		}
	}

	public function TipeBarangInsert()
	{
		$post = $this->PopulatePost();	
		// echo json_encode($post);die;
		$result = $this->MsLandingPageModel->TipeBarangInsert($post);
		
		if($result=='SUKSES'){
			$msg = array();
			$msg['result'] = "SUKSES";
			$msg['message'] = "Data Berhasil Di-update";
			echo json_encode($msg);
		}
		else{
			$msg = array();
			$msg['result'] = "FAILED";
			$msg['message'] = $result;
			echo json_encode($msg);
		}
	}

	public function TipeBarangUpdate()
	{
		$post = $this->PopulatePost();
		// echo json_encode($post);die;
		
		$result = $this->MsLandingPageModel->TipeBarangUpdate($post);
		
		if($result=='SUKSES'){
			$msg = array();
			$msg['result'] = "SUKSES";
			$msg['message'] = "Data Berhasil Di-update";
			echo json_encode($msg);
		}
		else{
			$msg = array();
			$msg['result'] = "FAILED";
			$msg['message'] = $result;
			echo json_encode($msg);
		}
	}

	public function Update()
	{
		$post = $this->PopulatePost();	
		$result = $this->MsLandingPageModel->Update($post);
		if($result=='SUKSES'){
			$msg = array();
			$msg['result'] = "SUKSES";
			$msg['message'] = "Data Berhasil Di-update";
		}
		else{
			$msg = array();
			$msg['result'] = "FAILED";
			$msg['message'] = $result;
		}
		echo json_encode($msg);
	}
	
	public function CreateQRCode($url='')
	{
		ob_start();
		$this->load->library('ciqrcode');
		// header("Content-Type: image/png");
		// $file_QR = 'assets/QRCODE.png';
        // $params['savename'] = $file_QR; //simpan image QR CODE ke folder assets/images/
		$params['data'] = $url;
        $params['level'] = 'H'; //H=High
        $params['size'] = 1024;
		$this->ciqrcode->generate($params);
		$imageString = base64_encode( ob_get_contents() );
		ob_end_clean();
		return $imageString;
	}
	
	public function CreateQRCodeName($merk, $tipe, $lokasi, $tgl)
	{
		$filename = str_replace(' ', '', $merk).'_'.str_replace(' ', '', $tipe).'_'.str_replace(' ', '', $lokasi).'_'.date('Ymd',strtotime($tgl)).'.png';
		return $filename;
	}

}