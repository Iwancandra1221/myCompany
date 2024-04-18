<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MsSmartQR extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('MsSmartQRModel');
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
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS BRANCH SYNC";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU MS SMARTQR ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);


		$data = array();
		$MsSmartQR = $this->MsSmartQRModel->GetList();
		$merks = $this->GetMerkListByGroup();
		$data["merks"] = $merks;
		$data["result"] = $MsSmartQR;	
					
		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView('MsSmartQRView',$data);
	}

	public function GetList()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS BRANCH SYNC";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." GET LIST MS SMARTQR ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$id= $this->input->get('id');
		$MsSmartQR = $this->MsSmartQRModel->GetList($id);
		$MsSmartQR->qrcode = $this->CreateQRCode($MsSmartQR->url);
		$MsSmartQR->filename = $this->CreateQRCodeName($MsSmartQR->merk,$MsSmartQR->tipe,$MsSmartQR->lokasi_qr_code,$MsSmartQR->created_date);
		$MsSmartQR->param = $this->MsSmartQRModel->GetParam($id);
		// echo json_encode($MsSmartQR->qrcode);die;

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		echo json_encode($MsSmartQR);
	}

	public function GetListGroupProduct()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS BRANCH SYNC";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]."  MS SMARTQR GetListGroupProduct";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$id= $this->input->get('id');
		$data = array();
		$GroupProduct = $this->MsSmartQRModel->GetListGroupProductById($id);
		$GroupProduct->barang = $this->ListBarang($GroupProduct->merk);
		$data["data"] = $GroupProduct;

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);


		echo json_encode($GroupProduct);
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
					if($d['MERK']==$merk || $merk==''){
						array_push($barangpermerk, array($d['KD_BRG'], $d['NM_BRG'], $d['MERK']));
					}
				}
				return $barangpermerk;
		} else {
			return array();
		}
	}
	
	public function GetBarangList()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS BRANCH SYNC";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]."  MS SMARTQR GetBarangList";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$merk = '';
		if($this->input->get('merk')){
			$merk = $this->input->get('merk');
		}

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		echo json_encode($this->ListBarang($merk));
	}
	
	public function GetMerkList()
	{
		$merk = $this->MsConfigModel->GetConfigValue($this->ConfigType, 'MERK', $this->GetGroup());
		return $merk;
	}

	public function SmartQRAdd()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS BRANCH SYNC";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." ADD MS SMARTQR ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

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
		$ListGroupProduct = $this->MsSmartQRModel->GetListGroupProduct($merk);
		for($i=0;$i<count($ListGroupProduct);$i++) {
			$tipe[] = trim($ListGroupProduct[$i]->tipe);
			$group = '';
			for($j=0;$j<count($merk_group);$j++) {
				if(trim($ListGroupProduct[$i]->merk)==$merk_group[$j]['merk']){
					$group = $merk_group[$j]['group'];
				}
			}
			$tipe_merk[] =  array('tipe'=>trim($ListGroupProduct[$i]->tipe), 'merk'=> trim($ListGroupProduct[$i]->merk), 'group'=> $group);
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
			
		$kd_brg= array();
		$kd_brg_merk= array();
		$result = $this->ListBarang();
		for($i=0;$i<count($result);$i++) {
			$kd_brg[] = trim($result[$i][0]).' | '.trim($result[$i][1]);
			
			$group = '';
			for($j=0;$j<count($merk_group);$j++) {
				if(trim($result[$i][2])==$merk_group[$j]['merk']){
					$group = $merk_group[$j]['group'];
				}
			}
			
			$kd_brg_merk[] = array( 'KD_BRG'=> trim($result[$i][0]),'NM_BRG'=> trim($result[$i][1]),'MERK'=> trim($result[$i][2]), 'group'=> $group);
		}
		// echo json_encode($kd_brg_merk);die;
		
		
		$data = array();
		$data['LokasiQRCode'] = $lokasi_qrcode;
		$data["tipe"] = $tipe; //untuk autocomplete
		$data["tipe_merk"] = $tipe_merk;
		$data["kd_brg"] = $kd_brg; //untuk autocomplete
		$data["kd_brg_merk"] = $kd_brg_merk;
		$data["group_param"] = $group_param;
		// echo json_encode($data);die;

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView('MsSmartQRAdd',$data);
	}
	
	public function SmartQREdit($id)
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS BRANCH SYNC";
		$params['TrxID'] = $id;
		$params['Description'] = $_SESSION["logged_in"]["username"]." EDIT MS SMARTQR ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$data = array();
		$MsSmartQR = $this->MsSmartQRModel->GetList($id);
		$data["result"] = $MsSmartQR;

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView('MsSmartQREdit',$data);
	}

	public function SmartQRInsert()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS BRANCH SYNC";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." INSERT MS SMARTQR ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$post = $this->PopulatePost();
		// echo json_encode($post);die;
		
		// CEK APAKAH ADA PARAM YG SAMA
		// PARAM LAMA
		
		$param = array();
		$param_names = array();
		$param_values = array();
		if(ISSET($post['old_param_name'])){
			for($i=0;$i<count($post['old_param_name']);$i++){
				$param[] = array('name'=>$post['old_param_name'][$i],'value'=>$post['old_param_value'][$i]);
			}
		}
		
		// PARAM BARU
		if(ISSET($post['param_name'])){
			for($i=0;$i<count($post['param_name']);$i++){
				if (in_array($post['param_name'][$i], array_column($param,'name'))){
					//NOTIF ADA KESAMAAN NAMA PARAM
					$msg = array();
					$msg['result'] = "FAILED";
					$msg['message'] = 'Param '.$post['param_name'][$i].' sudah ada sebelumnya!';
					echo json_encode($msg);
					die;
				}
				else{
					$param[] = array('name'=>$post['param_name'][$i],'value'=>$post['param_value'][$i]);
				}
			}
		}
		// echo json_encode($param);die;
		
		$post['bhakti_url'] = $this->getBhaktiURL();
		$res = $this->MsSmartQRModel->SmartQRInsert($post, $param);
		
		if($res['result']=='SUKSES'){
			$result = $this->MsSmartQRModel->GetList($res['id']);
			$result->result = "SUKSES";
			$result->qrcode = $this->CreateQRCode($res['url']);
			$result->filename = $this->CreateQRCodeName($result->merk,$result->tipe,$result->lokasi_qr_code,$result->created_date);
			$result->param = $this->MsSmartQRModel->GetParam($res['id']);

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			echo json_encode($result);
		}
		else{
			// ActivityLog Update FAILED
			$params['Remarks']="FAILED - iNSERT GAGAL";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			echo json_encode($res);
		}
	}

	public function SmartQRUpdate()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS BRANCH SYNC";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." UPDATE MS SMARTQR ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$post = $this->PopulatePost();
		$result = $this->MsSmartQRModel->SmartQRUpdate($post);
		if($result=='SUKSES'){
			$msg = array();
			$msg['result'] = "SUKSES";
			$msg['message'] = "URL Landing Page berhasil diupdate";

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

		}
		else{
			$msg = array();
			$msg['result'] = "FAILED";
			$msg['message'] = $result;

			// ActivityLog Update FAILED
			$params['Remarks']="FAILED - UPDATE GAGAL";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

		}
		echo json_encode($msg);
	}

	public function SmartQRViewUpdate()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS BRANCH SYNC";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." VIEW UPDATE MS SMARTQR ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);


		$post = $this->PopulatePost();
		// echo json_encode($post); die;
		$data = array();
		$result = $this->MsSmartQRModel->SmartQRUpdate($post);
		if($result=='SUKSES'){
			// $this->session->set_flashdata(['success_update' => 'URL Landing Page berhasil diupdate']);
			$data['msg'] = "success";
			$data['description'] = "URL Landing Page berhasil diupdate";

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
		}
		else{
			$data['msg'] = "failed";
			$data['description'] = $result;

			// ActivityLog Update FAILED
			$params['Remarks']="FAILED - UPDATE GAGAL";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
		}
		// echo json_encode($msg);
		$MsSmartQR = $this->MsSmartQRModel->GetList();
		$merks = $this->GetMerkListByGroup();
		$data["merks"] = $merks;
		$data["result"] = $MsSmartQR;	
		// echo json_encode($data);die;
		$this->RenderView('MsSmartQRView',$data);
	}
	
	public function SmartQRDelete()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS BRANCH SYNC";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." DELETE MS SMARTQR ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$post = $this->PopulatePost();
		// echo json_encode($post); die;
		$data = array();
		$result = $this->MsSmartQRModel->SmartQRDelete($post);
		if($result=='SUKSES'){
			$data['msg'] = "success";
			$data['description'] = 'Master QR Code Tipe Barang '.$post['tipe'].', Lokasi QR Code '.$post['lokasi_qr_code'].', Merk '.$post['merk'].' has been successfully deleted';

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
		}
		else{
			$data['msg'] = "failed";
			$data['description'] = $result;

			// ActivityLog Update FAILED
			$params['Remarks']="FAILED - DELETE GAGAL";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

		}
		// echo json_encode($msg);
		$MsSmartQR = $this->MsSmartQRModel->GetList();
		$merks = $this->GetMerkListByGroup();
		$data["merks"] = $merks;
		$data["result"] = $MsSmartQR;	
					
		$this->RenderView('MsSmartQRView',$data);
	}
	
	//--------------------------------------------------------------------------------------------
	public function GroupProduct()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "GROUP PRODUCT";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." GROUP PRODUCT ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$data = array();
		$MsSmartQR = $this->MsSmartQRModel->GetListGroupProduct();
		$data['merks'] = $this->GetMerkListByGroup();
		$data["result"] = $MsSmartQR;
		// echo json_encode($data);die;

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView('MsSmartQRGroupProductView',$data);
	}
	
	public function GroupProductAdd()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "GROUP PRODUCT";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." ADD GROUP PRODUCT ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$data = array();
		$data['LokasiQRCode'] = $this->MsSmartQRModel->GetListLokasiQRCode();
		$data['merks'] = $this->GetMerkListByGroup();
		// echo json_encode($data);die;

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView('MsSmartQRGroupProductAdd',$data);
	}

	public function GroupProductInsert()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "GROUP PRODUCT";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." INSERT GROUP PRODUCT ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$post = $this->PopulatePost();	
		// echo json_encode($post);die;
		$data = array();
		$result = $this->MsSmartQRModel->GroupProductInsert($post);
		if($result=='SUKSES'){
			// $this->session->set_flashdata(['success_insert' => 'Master Group Tipe Barang has been successfully created']);
			$data['msg'] = "success";
			$data['description'] = "Master Group Tipe Barang has been successfully created";

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

		}
		else{
			$data['msg'] = "failed";
			$data['description'] = $result;

			// ActivityLog Update FAILED
			$params['Remarks']="FAILED - INSERT GAGAL";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
		}
		
		if(ISSET($post['save'])){
			$MsSmartQR = $this->MsSmartQRModel->GetListGroupProduct();
			$data['merks'] = $this->GetMerkListByGroup();
			$data["result"] = $MsSmartQR;
			$this->RenderView('MsSmartQRGroupProductView',$data);
		
		}
		else{
			$data['LokasiQRCode'] = $this->MsSmartQRModel->GetListLokasiQRCode();
			$data['merks'] = $this->GetMerkListByGroup();
			$this->RenderView('MsSmartQRGroupProductAdd',$data);
		}
		
		
		
	}

	public function GroupProductUpdate()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "GROUP PRODUCT";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." UPDATE GROUP PRODUCT ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);


		$post = $this->PopulatePost();
		// echo json_encode($post);die;
		$data = array();
		$result = $this->MsSmartQRModel->GroupProductUpdate($post);
		if($result=='SUKSES'){
			// $this->session->set_flashdata(['success_update' => 'Master Group Tipe Barang has been successfully updated']);
			$data['msg'] = "success";
			$data['description'] = "Master Group Tipe Barang has been successfully updated";
			// echo json_encode($msg);

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
		}
		else{
			$data['msg'] = "failed";
			$data['description'] = $result;
			// echo json_encode($msg);

			// ActivityLog Update FAILED
			$params['Remarks']="FAILED - UPDATE GAGAL";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
		}
		$MsSmartQR = $this->MsSmartQRModel->GetListGroupProduct();
		$data['merks'] = $this->GetMerkListByGroup();
		$data["result"] = $MsSmartQR;
		$this->RenderView('MsSmartQRGroupProductView',$data);
	}

	public function GroupProductDelete()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "GROUP PRODUCT";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." DELETE GROUP PRODUCT ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);


		$post = $this->PopulatePost();
		$data = array();
		$result = $this->MsSmartQRModel->GroupProductDelete($post);
		if($result=='SUKSES'){
			// $this->session->set_flashdata(['success_delete' => 'Master Group Tipe Barang '.$post['tipe'].', Merk '.$post['merk'].' has been successfully deleted']);
			// $msg = array();
			$data['msg'] = "success";
			$data['description'] = 'Master Group Tipe Barang '.$post['tipe'].', Merk '.$post['merk'].' has been successfully deleted';
		
			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
		}
		else{
			// $this->session->set_flashdata(['failed_delete' => 'Group Tipe Barang '.$post['tipe'].', Merk '.$post['merk'].' gagal dihapus karena sudah pernah dipakai']);
			// $msg = array();
			$data['msg'] = "failed";
			$data['description'] = 'Group Tipe Barang '.$post['tipe'].', Merk '.$post['merk'].' gagal dihapus karena sudah pernah dipakai';
		
			// ActivityLog Update FAILED
			$params['Remarks']="FAILED - DELETE GAGAL";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
		}

		// echo json_encode($msg);
		$MsSmartQR = $this->MsSmartQRModel->GetListGroupProduct();
		$data['merks'] = $this->GetMerkListByGroup();
		$data["result"] = $MsSmartQR;
		$this->RenderView('MsSmartQRGroupProductView',$data);
	}
	
	public function CreateQRCode($url='')
	{
		ob_start();
		$this->load->library('ciqrcode');
		// header("Content-Type: image/png");
		// $file_QR = 'assets/QRCODE.png';
        // $params['savename'] = $file_QR; //simpan image QR CODE ke folder assets/images/
		
		// -----------------------------
		// Level L: up to 7% error correction capability.
		// Level M: up to 15% error correction capability.
		// Level Q: up to 25% error correction capability.
		// Level H: up to 30% error correction capability.
		// -----------------------------

		$params['data'] = $url;
        $params['level'] = 'L'; //H=High(titik banyak dan kecil) L=Smallest (titik sedikit)
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

	public function viewlog(){

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "VIEW LOG QR";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." VIEW LOG QR ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);


		$submit = $this->input->post('submit');
		$merk = $this->input->post('merk');
		$lokasiQrCode = $this->input->post('lokasi_qr_code');
		$logDateStart = $this->input->post('log_date_start');
		$logDateEnd = $this->input->post('log_date_end');
		$scanResult = $this->input->post('scan_result');
		$tipe =$this->input->post('tipe');


		$where = array();
		if($submit!=null){
			
			if($merk!='') $where['merk'] = $merk;

			if($lokasiQrCode!='') $where["lokasi_qr_code"] = $lokasiQrCode;

			if($logDateStart!=''){
				$date=date_create_from_format("d-M-Y",$logDateStart);
				if($date!=null) $where["LogDate >="] = date_format($date,"Y-m-d H:i:s");
			}
			if($logDateEnd!=''){
				$date=date_create_from_format("d-M-Y",$logDateEnd);
				if($date!=null) $where["LogDate <="] = date_format($date,"Y-m-d H:i:s");
			}
			if($scanResult!='') $where["result"] = $scanResult;

			if($tipe!='') $where["tipe"] = $tipe;

		}

		$getMerk = json_decode(file_get_contents($this->API_URL.'/MsBarang/GetMerkList2?api=APITES'),true);
		$dataLog = $this->MsSmartQRModel->getLog($where);
		$body = array(
			'getMerk' => $getMerk,
			'dataLog' => $dataLog,
		);

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);
		
		$this->RenderView('SmartQrViewLogView',$body);
	}

}