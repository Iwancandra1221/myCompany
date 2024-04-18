<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class KwitansiLainLainV2 extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('MsConfigModel');
		$this->load->model('BranchModel');
		$this->load->model('MasterDbModel');
		$this->load->model('ConfigSysModel');
		$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
		$this->module = 'Kwitansi Lain-lain';
	}

	
	public function index()
	{
		$data = array();
		
		$db = json_decode(json_encode($this->MasterDbModel->getList()),true);
		$col = array_column($db,"NamaDb");
		array_multisort($col,SORT_ASC,$db); // sort nama cabang
		$data['db'] = $db;
		// echo json_encode($data);die;
		$this->RenderView('KwitansiLainLainV2',$data);
	}
	
	public function GetKwitansi()
	{
		$post = $this->PopulatePost();
		$db = $this->MasterDbModel->getByBranchId($post['BranchCode']);
		if(isset($db)){
			$data = [
			"api" => "APITES",
			"svr"=> $db->Server,
			"db" => $db->Database,
			];
			
			$urlBhakti = $db->AlamatWebService;
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.$this->API_BKT."/Kwitansi",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			// die($response);
			
			if ($httpcode!=200) {
				$data['result'] = 'FAILED';
				$data['error'] = 'API Tujuan <u>'.$urlBhakti.'</u> OFFLINE';
			}
			else {
				$result = json_decode($response);
				$data = array();
				$data['result'] = 'SUCCESS';
				$data['data'] = $result->result;
				$data['error'] = '';
			}
		}
		else {
			$data['result'] = 'FAILED';
			$data['error'] = 'Tidak Ditemukan Data Master Cabang '.$post['BranchCode'];
		}
		echo json_encode($data);
	}
	
	public function Add()
	{
		$db = json_decode(json_encode($this->MasterDbModel->getList()),true);
		$col = array_column($db,"NamaDb");
		array_multisort($col,SORT_ASC,$db); // sort nama cabang
		$data['db'] = $db;
		$this->RenderView('KwitansiLainLainV2Add', $data);
	}
	
	public function LoadData()
	{
		$post = $this->PopulatePost();
		// echo json_encode($post); die;
		$db = $this->MasterDbModel->getByBranchId($post['BranchCode']);
		if(isset($db)) {
			$data = [
			"api" => "APITES",
			"svr"=> $db->Server,
			"db" => $db->Database,
			];
			
			$urlBhakti = $db->AlamatWebService;
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.$this->API_BKT."/Kwitansiv2/LoadData",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			// die($response);
			
			if ($httpcode!=200) {
				$data['result'] = 'FAILED';
				$data['error'] = 'API Tujuan <u>'.$urlBhakti.'</u> OFFLINE';
			}
			else {
				$result = json_decode($response);
				$data = array();
				$data['result'] = 'SUCCESS';
				$data['setting'] = $result->setting;
			}
		}
		else{
			$data['result'] = 'FAILED';
			$data['error'] = 'Tidak Ditemukan Data Master Cabang '.$post['BranchCode'];
		}
		echo json_encode($data);
	}
	
	public function Edit()
	{
		$post = $this->PopulatePost();
		
		$params["LogDate"] = date('Y-m-d H:i:s');
		$params["UserID"] = $_SESSION['logged_in']['userid'];
		$params["UserName"] = $_SESSION['logged_in']['username'];
		$params["UserEmail"] = $_SESSION['logged_in']['useremail'];
		$params["Module"] = $this->module;
		$params["TrxID"] = $post['NoKwitansi'];
		$params["Description"] = 'Edit';
		$params["Remarks"] = '';
		$params["RemarksDate"] = '';
		$this->ActivityLogModel->insert_activity($params);
		
		$db = $this->MasterDbModel->getByBranchId($post['BranchCode']);
		if(isset($db)){
		
			$data = [
			"NoKwitansi" => $post['NoKwitansi'],
			"api" => "APITES",
			"svr"=> $db->Server,
			"db" => $db->Database,
			];
			
			$urlBhakti = $db->AlamatWebService;			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.$this->API_BKT."/Kwitansiv2/Edit",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			// die($response);
			
			if ($httpcode!=200) {

				$params['Remarks']="FAILED - API Tujuan OFFLINE";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				die("API Tujuan <u>".$urlBhakti."</u> OFFLINE");
			}
			else {
				$result = json_decode($response);
				$data = array();
				// $data['vas'] = $result->vas;
				// $data['banks'] = $result->banks;
				$data['setting'] = $result->setting;
				
				$db = json_decode(json_encode($this->MasterDbModel->getList()),true);
				$col = array_column($db,"NamaDb");
				array_multisort($col,SORT_ASC,$db); // sort nama cabang
				
				$data['db'] = $db;
				$data['result'] = $result->result;

				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				$this->RenderView('KwitansiLainLainV2Add',$data);
			}
		}
		else{
			redirect('KwitansiLainLainV2');
		}
	}
	
	public function Stamping()
	{
		$NoKwitansi = $this->input->get('NoKwitansi');
		$BranchCode = $this->input->get('BranchCode');
		
		$params["LogDate"] = date('Y-m-d H:i:s');
		$params["UserID"] = $_SESSION['logged_in']['userid'];
		$params["UserName"] = $_SESSION['logged_in']['username'];
		$params["UserEmail"] = $_SESSION['logged_in']['useremail'];
		$params["Module"] = $this->module;
		$params["TrxID"] = $NoKwitansi;
		$params["Description"] = 'Stamping';
		$params["Remarks"] = '';
		$params["RemarksDate"] = '';
		$this->ActivityLogModel->insert_activity($params);
		
		$db = $this->MasterDbModel->getByBranchId($BranchCode);
		if(isset($db)){
		
			$data = [
			"user" => $_SESSION['logged_in']['username'],
			"NoKwitansi" => $NoKwitansi,
			// "signature" => "", // tidak dipakai lagi
			"api" => "APITES",
			"svr"=> $db->Server,
			"db" => $db->Database,
			];
			
			$urlBhakti = $db->AlamatWebService;
			$urlStamping = $urlBhakti.$this->API_BKT."/Kwitansiv2/Stamping";
			//echo($urlStamping."<br><br>".json_encode($data));

			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlStamping,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			// die($response);
			
			if ($httpcode!=200) {
				$res = array();
				$res['msg'] = 'FAILED';
				$res['error'] = 'API Tujuan OFFLINE';

				$params['Remarks']="FAILED - API Tujuan OFFLINE";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			else {
				$result = json_decode($response, true);
				$res = array();
				$res['msg'] = strtoupper($result['msg']);
				$res['error'] =  $result['error'];

				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			echo json_encode($res);
			
		}
		else{
			redirect('KwitansiLainLainV2');
		}
	}
	
	public function Lock()
	{
		$NoKwitansi = $this->input->get('NoKwitansi');
		$BranchCode = $this->input->get('BranchCode');
		
		$params["LogDate"] = date('Y-m-d H:i:s');
		$params["UserID"] = $_SESSION['logged_in']['userid'];
		$params["UserName"] = $_SESSION['logged_in']['username'];
		$params["UserEmail"] = $_SESSION['logged_in']['useremail'];
		$params["Module"] = $this->module;
		$params["TrxID"] = $NoKwitansi;
		$params["Description"] = 'Lock';
		$params["Remarks"] = '';
		$params["RemarksDate"] = '';
		$this->ActivityLogModel->insert_activity($params);
		
		$db = $this->MasterDbModel->getByBranchId($BranchCode);
		if(isset($db)){
		
			$data = [
			"user" => $_SESSION['logged_in']['username'],
			"NoKwitansi" => $NoKwitansi,
			// "signature" => "", // tidak dipakai lagi
			"api" => "APITES",
			"svr"=> $db->Server,
			"db" => $db->Database,
			];
			
			$urlBhakti = $db->AlamatWebService;
			$urlLock = $urlBhakti.$this->API_BKT."/Kwitansiv2/Lock";
			//echo($urlStamping."<br><br>".json_encode($data));

			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlLock,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			die($response);
			
			if ($httpcode!=200) {
				$res = array();
				$res['msg'] = 'FAILED';
				$res['error'] = 'API Tujuan OFFLINE';

				$params['Remarks']="FAILED - API Tujuan OFFLINE";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			else {
				$result = json_decode($response, true);
				$res = array();
				$res['msg'] = strtoupper($result['msg']);
				$res['error'] =  $result['error'];

				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			echo json_encode($res);
			
		}
		else{
			redirect('KwitansiLainLainV2');
		}
	}
	
	public function Unlock()
	{
		$NoKwitansi = $this->input->get('NoKwitansi');
		$BranchCode = $this->input->get('BranchCode');
		
		$params["LogDate"] = date('Y-m-d H:i:s');
		$params["UserID"] = $_SESSION['logged_in']['userid'];
		$params["UserName"] = $_SESSION['logged_in']['username'];
		$params["UserEmail"] = $_SESSION['logged_in']['useremail'];
		$params["Module"] = $this->module;
		$params["TrxID"] = $NoKwitansi;
		$params["Description"] = 'Unlock';
		$params["Remarks"] = '';
		$params["RemarksDate"] = '';
		$this->ActivityLogModel->insert_activity($params);
		
		$db = $this->MasterDbModel->getByBranchId($BranchCode);
		if(isset($db)){
		
			$data = [
			"user" => $_SESSION['logged_in']['username'],
			"NoKwitansi" => $NoKwitansi,
			// "signature" => "", // tidak dipakai lagi
			"api" => "APITES",
			"svr"=> $db->Server,
			"db" => $db->Database,
			];
			
			$urlBhakti = $db->AlamatWebService;
			$urlLock = $urlBhakti.$this->API_BKT."/Kwitansiv2/Unlock";
			//echo($urlStamping."<br><br>".json_encode($data));

			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlLock,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			// die($response);
			
			if ($httpcode!=200) {
				$res = array();
				$res['msg'] = 'FAILED';
				$res['error'] = 'API Tujuan OFFLINE';

				$params['Remarks']="FAILED - API Tujuan OFFLINE";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			else {
				$result = json_decode($response, true);
				$res = array();
				$res['msg'] = strtoupper($result['msg']);
				$res['error'] =  $result['error'];

				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			echo json_encode($res);
			
		}
		else{
			redirect('KwitansiLainLainV2');
		}
	}
	
	public function Delete()
	{
		$post = $this->PopulatePost();
		
		$params["LogDate"] = date('Y-m-d H:i:s');
		$params["UserID"] = $_SESSION['logged_in']['userid'];
		$params["UserName"] = $_SESSION['logged_in']['username'];
		$params["UserEmail"] = $_SESSION['logged_in']['useremail'];
		$params["Module"] = $this->module;
		$params["TrxID"] = $post['NoKwitansi'];
		$params["Description"] = 'Delete';
		$params["Remarks"] = '';
		$params["RemarksDate"] = '';
		$this->ActivityLogModel->insert_activity($params);
		
		$db = $this->MasterDbModel->getByBranchId($post['BranchCode']);
		if(isset($db)){
		
			$data = [
			"api" => "APITES",
			"user" => $_SESSION['logged_in']['username'],
			"NoKwitansi" => $post['NoKwitansi'],
			"DeletedNote" => $post['DeletedNote'],
			"api" => "APITES",
			"svr"=> $db->Server,
			"db" => $db->Database,
			];
			
			$urlBhakti = $db->AlamatWebService;
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.$this->API_BKT."/Kwitansiv2/Delete",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			// die($response);
			
			if ($httpcode!=200) {
				$res = array();
				$res['msg'] = 'FAILED';
				$res['error'] = 'API Tujuan OFFLINE';

				$params['Remarks']="FAILED - API Tujuan OFFLINE";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			else {
				$result = json_decode($response);
				$res = array();
				$res['msg'] = 'SUCCESS';
				$res['error'] = '';

				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			echo json_encode($res);
			
		}
		else{
			redirect('KwitansiLainLainV2');
		}
	}
	
	public function Uncancelled()
	{
		$NoKwitansi = $this->input->get('NoKwitansi');
		$BranchCode = $this->input->get('BranchCode');
		
		$params["LogDate"] = date('Y-m-d H:i:s');
		$params["UserID"] = $_SESSION['logged_in']['userid'];
		$params["UserName"] = $_SESSION['logged_in']['username'];
		$params["UserEmail"] = $_SESSION['logged_in']['useremail'];
		$params["Module"] = $this->module;
		$params["TrxID"] = $NoKwitansi;
		$params["Description"] = 'Uncancelled';
		$params["Remarks"] = '';
		$params["RemarksDate"] = '';
		$this->ActivityLogModel->insert_activity($params);
		
		$db = $this->MasterDbModel->getByBranchId($BranchCode);
		if(isset($db)) {
			$data = [
			"user" => $_SESSION['logged_in']['username'],
			"NoKwitansi" => $NoKwitansi,
			"api" => "APITES",
			"svr"=> $db->Server,
			"db" => $db->Database,
			];
			
			$urlBhakti = $db->AlamatWebService;
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.$this->API_BKT."/Kwitansiv2/Uncancelled",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			// die($response);
			
			if ($httpcode!=200) {
				$res = array();
				$res['msg'] = 'FAILED';
				$res['error'] = 'API Tujuan OFFLINE';

				$params['Remarks']="FAILED - API Tujuan OFFLINE";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			else {
				$result = json_decode($response);
				$res = array();
				$res['msg'] = 'SUCCESS';
				$res['error'] = '';

				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			echo json_encode($res);
			
		}
		else{
			redirect('KwitansiLainLainV2');
		}
	}
	
	public function GenerateAutoNoKwitansi()
	{
		$post = $this->PopulatePost();
		
		$params["LogDate"] = date('Y-m-d H:i:s');
		$params["UserID"] = $_SESSION['logged_in']['userid'];
		$params["UserName"] = $_SESSION['logged_in']['username'];
		$params["UserEmail"] = $_SESSION['logged_in']['useremail'];
		$params["Module"] = $this->module;
		$params["TrxID"] = $post['BranchCode'];
		$params["Description"] = 'GenerateAutoNoKwitansi';
		$params["Remarks"] = '';
		$params["RemarksDate"] = '';
		$this->ActivityLogModel->insert_activity($params);
		
		$db = $this->MasterDbModel->getByBranchId($post['BranchCode']);
		if(isset($db)){
			$data = [
			"th" => $post["th"],
			"bl" => $post["bl"],
			"api" => "APITES",
			"svr"=> $db->Server,
			"db" => $db->Database,
			];
			
			$urlBhakti = $db->AlamatWebService;
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.$this->API_BKT."/Kwitansiv2/GenerateAutoNoKwitansi",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			// die($response);
			
			if ($httpcode!=200) {
				$params['Remarks']="FAILED - API Tujuan OFFLINE";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				echo "API Tujuan OFFLINE";
			}
			else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				$result = json_decode($response, true);
				echo $result['no_kwitansi'];
			}
		}
		else{
			echo "FAILED";
		}
	}
	
	public function Check()
	{
		$post = $this->PopulatePost();
		$db = $this->MasterDbModel->getByBranchId($post['BranchCode']);
		if(isset($db)){
			$data = [
			"NoKwitansi" => $post["NoKwitansi"],
			"api" => "APITES",
			"svr"=> $db->Server,
			"db" => $db->Database,
			];
			
			$urlBhakti = $db->AlamatWebService;
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.$this->API_BKT."/Kwitansiv2/Check",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			// die($response);
			
			if ($httpcode!=200) {
				// $params['Remarks']="FAILED - API Tujuan OFFLINE";
				// $params['RemarksDate'] = date("Y-m-d H:i:s");
				// $this->ActivityLogModel->update_activity($params);

				echo "API Tujuan OFFLINE";
			}
			else {

				// $params['Remarks']="SUCCESS";
				// $params['RemarksDate'] = date("Y-m-d H:i:s");
				// $this->ActivityLogModel->update_activity($params);

				$result = json_decode($response, true);
				echo $result['result'];
			}
			
		}
		else{
			echo "FAILED. Tidak bisa terhubung ke server ".$post['kd_lokasi'];
		}
	}
	
	public function Save()
	{
		$post = $this->PopulatePost();
		// echo json_encode($post);die;
		
		/*
		$config = $this->MsConfigModel->GetConfigValue('SIGNATURE','FINANCE MANAGER');
		if($config){
			$signature = $config[0]->ConfigValue;
		}
		else{
			$res = array();
			$res['msg'] = 'FAILED';
			$res['error'] = 'ConfigType "SIGNATURE" dan ConfigName "FINANCE MANAGER" belum disetting!';
			echo json_encode($res); die;
		}
		*/
		
		$db = $this->MasterDbModel->getByBranchId($post['BranchCode']);
		if(isset($db)) {
			$config = [
			"api" => "APITES",
			"svr"=> $db->Server,
			"db" => $db->Database,
			];
			
			$post['config'] = $config;
			$post['user'] = $_SESSION['logged_in']['username'];
			$post['Total'] = str_replace(',','',$post['Total']); // hapus koma dari total
			$post['UntukPembayaran'] = str_replace(array("\r\n", "\n\r", "\r", "\n"), "<br>", $post['UntukPembayaran']); // hapus enter dari textarea
			$post['KeteranganInternal'] = str_replace(array("\r\n", "\n\r", "\r", "\n"), "<br>", $post['KeteranganInternal']); // hapus enter dari textarea
			// $post['signature'] = $signature; // tidak dipakai lagi
			// echo json_encode($post); die;
			
			$urlBhakti = $db->AlamatWebService;
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.$this->API_BKT."/Kwitansiv2/Save",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($post),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			
			echo($response);die;
			
			if ($httpcode!=200) {
				$res = array();
				$res['msg'] = 'FAILED';
				$res['error'] = 'API Tujuan OFFLINE';

				// $params['Remarks']="FAILED - API Tujuan OFFLINE";
				// $params['RemarksDate'] = date("Y-m-d H:i:s");
				// $this->ActivityLogModel->update_activity($params);
			}
			else {

				$result = json_decode($response, true);
				$res = array();
				$res['msg'] = $result['msg'];
				$res['error'] = $result['error'];

				// $params['Remarks']="SUCCESS";
				// $params['RemarksDate'] = date("Y-m-d H:i:s");
				// $this->ActivityLogModel->update_activity($params);
			}
		}
		else{
			$res = array();
			$res['msg'] = 'FAILED';
			$res['error'] = 'Koneksi Gagal!';
		}
		echo json_encode($res);
		
	}
	
	public function ViewPDF()
	{
		$NoKwitansi = $this->input->post('NoKwitansi');
		$BranchCode = $this->input->post('BranchCode');
		
		$params["LogDate"] = date('Y-m-d H:i:s');
		$params["UserID"] = $_SESSION['logged_in']['userid'];
		$params["UserName"] = $_SESSION['logged_in']['username'];
		$params["UserEmail"] = $_SESSION['logged_in']['useremail'];
		$params["Module"] = $this->module;
		$params["TrxID"] = $NoKwitansi;
		$params["Description"] = 'ViewPDF';
		$params["Remarks"] = '';
		$params["RemarksDate"] = '';
		$this->ActivityLogModel->insert_activity($params);
		
		$db = $this->MasterDbModel->getByBranchId($BranchCode);
		if(isset($db)) {
			$data = [
			"NoKwitansi" => $NoKwitansi,
			"api" => "APITES",
			"svr"=> $db->Server,
			"db" => $db->Database,
			];
			
			$urlBhakti = $db->AlamatWebService;
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.$this->API_BKT."/Kwitansiv2/ViewPDF",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// die($response);
			
			if ($response===false) {
				$params['Remarks']="FAILED - API Tujuan OFFLINE";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				die('API Tujuan <u>'.$urlBhakti.'</u> OFFLINE');
			}
			else {

				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				$result = json_decode($response);
				if(ISSET($result->pdf)){
					$data = base64_decode($result->pdf);
					
					// #1 bisa didownload
					// header('Content-Type: application/pdf');
					// header('Content-Length: '.strlen($data));
					// header('Content-disposition: inline; filename="'.$NoKwitansi.'.pdf"');
					// header('Expires: 0'); 
					// header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); 
					// echo $data;
					
					// #2 Preview saja
					echo '<object data="data:application/pdf;base64,'.$result->pdf.'" type="application/pdf" width="100%" height="100%">';
					echo '<p>Unable to display PDF file.</p>';
					echo '</object>';
				}
				else die("File PDF tidak ditemukan!");
			}
			
		}
		else{
			redirect('KwitansiLainLainV2');
			}
	}
	
	public function datatable_kwitansi()
	{
		$param = $_GET;
		$db = $this->MasterDbModel->getByBranchId($param['branch_code']);
		if(isset($db)) {
			$config = [
				"svr"=> $db->Server,
				"db" => $db->Database,
			];
			$param['config'] = $config;
			$urlBhakti = $db->AlamatWebService;
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.$this->API_BKT."/Kwitansiv2/datatable_kwitansi",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 6000,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($param),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			echo($response);
		}
	}
	
	public function datatable_va()
	{
		$param = $_GET;
		$db = $this->MasterDbModel->getByBranchId($param['branch_code']);
		if(isset($db)) {
			$config = [
				"svr"=> $db->Server,
				"db" => $db->Database,
			];
			$param['config'] = $config;
			$urlBhakti = $db->AlamatWebService;
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.$this->API_BKT."/Kwitansiv2/datatable_va",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 6000,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($param),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			echo($response);
		}
	}
	
	public function datatable_dealer()
	{
		$param = $_GET;
		$db = $this->MasterDbModel->getByBranchId($param['branch_code']);
		if(isset($db)) {
			$config = [
				"svr"=> $db->Server,
				"db" => $db->Database,
			];
			$param['config'] = $config;
			$urlBhakti = $db->AlamatWebService;
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.$this->API_BKT."/Kwitansiv2/datatable_dealer",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 6000,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($param),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			echo($response);
		}
	}
	
	public function datatable_bank()
	{
		$param = $_GET;
		$db = $this->MasterDbModel->getByBranchId($param['branch_code']);
		if(isset($db)) {
			$config = [
				"svr"=> $db->Server,
				"db" => $db->Database,
			];
			$param['config'] = $config;
			$urlBhakti = $db->AlamatWebService;
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.$this->API_BKT."/Kwitansiv2/datatable_bank",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 6000,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($param),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			echo($response);
		}
	}
	
	public function datatable_bukti()
	{
		$param = $_GET;
		$db = $this->MasterDbModel->getByBranchId($param['branch_code']);
		if(isset($db)) {
			$config = [
				"svr"=> $db->Server,
				"db" => $db->Database,
			];
			$param['config'] = $config;
			$urlBhakti = $db->AlamatWebService;
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.$this->API_BKT."/Kwitansiv2/datatable_bukti",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 6000,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($param),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			echo($response);
		}
	}
	
	public function datatable_supplier()
	{
		$param = $_GET;
		$db = $this->MasterDbModel->getByBranchId($param['branch_code']);
		if(isset($db)) {
			$config = [
				"svr"=> $db->Server,
				"db" => $db->Database,
			];
			$param['config'] = $config;
			$urlBhakti = $db->AlamatWebService;
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.$this->API_BKT."/Kwitansiv2/datatable_supplier",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 6000,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($param),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			echo($response);
		}
	}
	
}