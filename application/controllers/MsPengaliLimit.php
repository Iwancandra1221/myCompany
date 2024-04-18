<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MsPengaliLimit extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function index($sukses=0)
	{
		
		$data = array();
		$url = $this->API_URL."/ConfigPenjualan/GetPengaliLimit?api=APITES";
		// echo json_encode($url); die;

		$MsPengaliLimit = json_decode(file_get_contents($url), true);
		if ($MsPengaliLimit["result"]=="sukses") {
			$data["PengaliLimitByWilayah"] = $MsPengaliLimit["PengaliLimitByWilayah"];
			$data["PengaliLimitByToko"] = $MsPengaliLimit["PengaliLimitByToko"];
		} else {
			echo json_encode(array('error'=>$MsPengaliLimit["error"]));
		}
       	if ($sukses==1) {
       		$data["alert"] = "Simpan Berhasil";
       	}
		$this->RenderView('MsPengaliLimitView',$data);
	}

	public function Add($sukses=0)
	{
		$data = array();
		// $partner_type = array("TRADISIONAL") ;
		// $data["partner_type"] = $partner_type;

		// $wilayah = json_decode(file_get_contents($this->API_URL."/ConfigPenjualan/GetWilayah?api=APITES&partner_type="));
		// $data["wilayah"] = $wilayah;

		
		$dealer = json_decode(file_get_contents($this->API_URL."/ConfigPenjualan/GetDealer?api=APITES"));
		
		$new = array();
		foreach($dealer as $d){
			$new[] = $d->KD_PLG.' | '.$d->NM_PLG;
		}
		
		$data["dealer"] = $new;
		
		$this->RenderView('MsPengaliLimitAdd',$data);
	}

	public function Edit()
	{
		$divisi = urlencode($this->input->get('divisi'));
		$partner_type = urlencode($this->input->get('partner_type'));
		$data = array();
		
		if($this->input->get('wilayah')){
			$wilayah = $this->input->get('wilayah');
			$url = $this->API_URL."/ConfigPenjualan/GetPengaliLimitByWilayah?api=APITES&divisi=".urlencode($divisi)."&wilayah=".urlencode($wilayah)."&partner_type=".urlencode($partner_type);
		}
		else{
			$kd_plg = $this->input->get('kd_plg');
			$url = $this->API_URL."/ConfigPenjualan/GetPengaliLimitByToko?api=APITES&divisi=".urlencode($divisi)."&kd_plg=".urlencode($kd_plg);
		}
		// echo $url; die;
		$MsPengaliLimit = json_decode(file_get_contents($url));
		if ($MsPengaliLimit->result=="sukses") {
			$data["result"] = $MsPengaliLimit->data;
		} else {
			echo json_encode(array('error'=>$MsPengaliLimit->error));
		}
		
		$this->RenderView('MsPengaliLimitEdit',$data);
	}

	public function Save()
	{
		$post = $this->PopulatePost();
		$post['api'] = 'APITES';
		$post['user'] = $_SESSION["logged_in"]["username"];
		// echo json_encode($post);die;
		
		// var_dump($post);

		// print_r($post);
		// die("!");

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->API_URL."/ConfigPenjualan/SavePengaliLimit",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($post),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $response;die;

		
		$result = json_decode($response);

		// print_r($response);
		// print_r($result);
		// die("!");

		if($result->result=='sukses'){
			$this->session->set_flashdata('success','Data berhasil disimpan!');
			redirect('MsPengaliLimit');
		}
		else{
			$this->session->set_flashdata('error','Data gagal disimpan! '.$result->error);
			redirect('MsPengaliLimit');
		}

		
	}


    public function Delete()
	{
		$divisi = $this->input->get('divisi');
		$partner_type = urlencode($this->input->get('partner_type'));

        $data = [
			"api" => "APITES",
			"divisi" => $divisi,
			"partner_type" => $partner_type,
			"user" => $_SESSION["logged_in"]["username"]
		];
		if($this->input->get('wilayah')){
			$data['wilayah'] = $this->input->get('wilayah');
		}
		if($this->input->get('kd_plg')){
			$data['kd_plg'] = $this->input->get('kd_plg');
		}
		
		// echo json_encode($data);die;
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->API_URL."/ConfigPenjualan/DeletePengaliLimit",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		
		// echo $response;die;
		
		$result = json_decode($response);
		if($result->result=='sukses'){
			$this->session->set_flashdata('success','Data berhasil dihapus!');
			redirect('MsPengaliLimit');
		}
		else{
			$this->session->set_flashdata('error','Data gagal dihapus! '.$result->error);
			redirect('MsPengaliLimit');
		}
		
    }
	
	public function Export(){
	
		$opt = $this->input->get('opt');
		$data = array();
		$data["mode"] = "PENGALI LIMIT";
		$data["opt"] = $opt;
		$data["databases"] = $this->MasterDbModel->getListForExport("", "NamaDb");
		$this->RenderView('MsPengaliLimitExport',$data);	
	
	}
	
	public function Wilayah($data=''){
		$data=addslashes(str_replace("%20"," ",$data));
		print_r($this->ConfigPenjualanModel->GetWilayah($data));
	}
	
	Public function ExportKeCabang()
	{
		$err = "";
		$post = $this->PopulatePost();
		// echo json_encode($post); die;

		if(isset($post['db'])){
			$dbId = $post["db"];
			$db = $this->MasterDbModel->get($dbId);
			//die(json_encode($db));

			$URL = $this->API_URL;
			if($post['opt']=='wilayah'){
				$URL.= "/ConfigPenjualan/GetPengaliLimitByWilayah?api=APITES";
			}
			else{
				$URL.= "/ConfigPenjualan/GetPengaliLimitByToko?api=APITES";
			}
    		// die(json_encode($URL));
			
	        $list = json_decode(file_get_contents($URL), true);
			
	        // die(json_encode($list));
	        
	        if ($list["result"]=="sukses") {
	        	$PG = $list["data"];
	        	$lanjut = true;
		        if ($lanjut) {

					$data = [
						"api" => "APITES",
						"list" => $PG,
						"user" => $_SESSION["logged_in"]["username"],
						"svr"=>$db->Server,
						"db" => $db->Database,
						"uid" => SQL_UID,
						"pwd" => SQL_PWD
					];
					// die(json_encode($data));
					$urlBhakti = HO;
					
					// die(json_encode($urlBhakti.API_BKT."/MsPengaliLimit/Save"));

					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_URL => $urlBhakti.API_BKT."/ConfigPenjualan/SavePengaliLimit",
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_TIMEOUT => 60,
						CURLOPT_POST => 1,
						CURLOPT_POSTFIELDS => json_encode($data),
						CURLOPT_HTTPHEADER => array("Content-type: application/json")
					));
					
					// die(json_encode($urlBhakti));
					$response = curl_exec($curl);
					$err = curl_error($curl);
					curl_close($curl);
					
					// die(json_encode($response));

					if ($response===false) {
						$lanjut = false;
						$err = "API Tujuan OFFLINE";
					} else {
				        if (json_decode($response)=="sukses") {
				        	$lanjut=true;
				       	} else {
				       		$lanjut=false;
				       		$err=json_decode($response);
				       	}
			        }
		        }

	        	if ($lanjut) {
		        	echo json_encode(array("result"=>"sukses", "error"=>"Sukses Update Pengali Limit", "list"=>$PG));
	        	} else {
					echo json_encode(array("result"=>"gagal", "error"=>$err, "list"=>$PG)); //"Error Update Kategori Barang"));
	        	}
	        } else {
	        	echo json_encode(array("result"=>$list["result"], "error"=>$list["error"], "list"=>array()));
	        }
		} else {
			echo json_encode(array("result"=>"gagal", 'error'=>'Param CBG Belum Diberikan', "list"=>array()));
		}
	}
	
}