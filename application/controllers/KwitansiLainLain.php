<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class KwitansiLainLain extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('MsConfigModel');
	}

	
	public function index()
	{
		// echo json_encode($_SESSION['logged_in']); die; //debug
		// echo json_encode($_SESSION['conn']); die; //debug
		
		if(isset($_SESSION['conn'])) {

			$params = array();			
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "KWITANSI LAIN LAIN";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU KWITANSI LAIN LAIN";
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$data = [
			"api" => "APITES",
			"svr"=> $_SESSION["conn"]->Server,
			"db" => $_SESSION["conn"]->Database,
			"uid" => SQL_UID,
			"pwd" => SQL_PWD
			];
			
			$urlBhakti = $_SESSION["conn"]->AlamatWebService;
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.API_BKT."/Kwitansi",
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
				$lanjut = false;
				$err = "API Tujuan OFFLINE";

				$params['Remarks']="FAILED - API Tujuan OFFLINE";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				}
				else {
					$result = json_decode($response);
					$data = array();
					$data['result'] = $result->result;

					$params['Remarks']="SUCCESS";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

					$this->RenderView('KwitansiLainLain',$data);
			}
		}
		else{
			redirect('KwitansiLainLain');
		}
	}
	
	public function Add()
	{
		if(isset($_SESSION['conn'])) {

			$params = array();			
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "KWITANSI LAIN LAIN";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." TAMBAH KWITANSI LAIN LAIN";
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$data = [
			"api" => "APITES",
			"svr"=> $_SESSION["conn"]->Server,
			"db" => $_SESSION["conn"]->Database,
			"uid" => SQL_UID,
			"pwd" => SQL_PWD
			];
			
			$urlBhakti = $_SESSION["conn"]->AlamatWebService;
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.API_BKT."/Kwitansi/Add",
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
				$data['vas'] = $result->vas;
				$data['banks'] = $result->banks;
				$data['setting'] = $result->setting;

				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				$this->RenderView('KwitansiLainLainAdd',$data);
			}
		}
		else{
			redirect('KwitansiLainLain');
		}
	}
	
	public function Edit()
	{
		$post = $this->PopulatePost();
		if(isset($_SESSION['conn'])) {

			$params = array();			
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "KWITANSI LAIN LAIN";
			$params['TrxID'] = $post['NoKwitansi'];
			$params['Description'] = $_SESSION["logged_in"]["username"]." EDIT KWITANSI LAIN LAIN";
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);
		
			$data = [
			"NoKwitansi" => $post['NoKwitansi'],
			"api" => "APITES",
			"svr"=> $_SESSION["conn"]->Server,
			"db" => $_SESSION["conn"]->Database,
			"uid" => SQL_UID,
			"pwd" => SQL_PWD
			];
			
			$urlBhakti = $_SESSION["conn"]->AlamatWebService;
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.API_BKT."/Kwitansi/Edit",
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
				$data['vas'] = $result->vas;
				$data['banks'] = $result->banks;
				$data['setting'] = $result->setting;
				$data['result'] = $result->result;

				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				$this->RenderView('KwitansiLainLainAdd',$data);
			}
			
		}
		else{
			redirect('KwitansiLainLain');
		}
	}
	
	public function Delete()
	{
		$post = $this->PopulatePost();
		if(isset($_SESSION['conn'])) {


			$params = array();			
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "KWITANSI LAIN LAIN";
			$params['TrxID'] = $post['NoKwitansi'];
			$params['Description'] = $_SESSION["logged_in"]["username"]." DELETE KWITANSI LAIN LAIN";
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);
		
			$data = [
			"user" => $_SESSION['logged_in']['username'],
			"NoKwitansi" => $post['NoKwitansi'],
			"DeletedNote" => $post['DeletedNote'],
			"api" => "APITES",
			"svr"=> $_SESSION["conn"]->Server,
			"db" => $_SESSION["conn"]->Database,
			"uid" => SQL_UID,
			"pwd" => SQL_PWD
			];
			
			$urlBhakti = $_SESSION["conn"]->AlamatWebService;
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.API_BKT."/Kwitansi/Delete",
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
			redirect('KwitansiLainLain');
		}
	}
	
	public function Uncancelled()
	{
		$NoKwitansi = $this->input->get('NoKwitansi');
		if(isset($_SESSION['conn'])) {

			$params = array();			
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "KWITANSI LAIN LAIN";
			$params['TrxID'] = $NoKwitansi;
			$params['Description'] = $_SESSION["logged_in"]["username"]." UNCANCELLED KWITANSI LAIN LAIN";
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);
		
			$data = [
			"user" => $_SESSION['logged_in']['username'],
			"NoKwitansi" => $NoKwitansi,
			"api" => "APITES",
			"svr"=> $_SESSION["conn"]->Server,
			"db" => $_SESSION["conn"]->Database,
			"uid" => SQL_UID,
			"pwd" => SQL_PWD
			];
			
			$urlBhakti = $_SESSION["conn"]->AlamatWebService;
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.API_BKT."/Kwitansi/Uncancelled",
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
			redirect('KwitansiLainLain');
		}
	}
	
	public function GenerateAutoNoKwitansi()
	{
		$post = $this->PopulatePost();
		if(isset($_SESSION['conn'])) {

			$params = array();			
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "KWITANSI LAIN LAIN";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." GENERATE NO KWITANSI LAIN LAIN";
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$data = [
			"th" => $post["th"],
			"bl" => $post["bl"],
			"api" => "APITES",
			"svr"=> $_SESSION["conn"]->Server,
			"db" => $_SESSION["conn"]->Database,
			"uid" => SQL_UID,
			"pwd" => SQL_PWD
			];
			
			$urlBhakti = $_SESSION["conn"]->AlamatWebService;
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.API_BKT."/Kwitansi/GenerateAutoNoKwitansi",
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
					echo "API Tujuan OFFLINE";

				$params['Remarks']="FAILED - API Tujuan OFFLINE";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
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
		if(isset($_SESSION['conn'])) {

			$params = array();			
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "KWITANSI LAIN LAIN";
			$params['TrxID'] = $post["NoKwitansi"];
			$params['Description'] = $_SESSION["logged_in"]["username"]." CHECK KWITANSI LAIN LAIN";
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$data = [
			"NoKwitansi" => $post["NoKwitansi"],
			"api" => "APITES",
			"svr"=> $_SESSION["conn"]->Server,
			"db" => $_SESSION["conn"]->Database,
			"uid" => SQL_UID,
			"pwd" => SQL_PWD
			];
			
			$urlBhakti = $_SESSION["conn"]->AlamatWebService;
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.API_BKT."/Kwitansi/Check",
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
				echo $result['result'];
			}
			
		}
		else{
			echo "FAILED";
		}
	}
	
	public function Save()
	{
		$post = $this->PopulatePost();
	
		if(isset($_SESSION['conn'])) {

			$params = array();			
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "KWITANSI LAIN LAIN";
			$params['TrxID'] = $post["NoKwitansi"];
			$params['Description'] = $_SESSION["logged_in"]["username"]." SAVE KWITANSI LAIN LAIN";
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$config = [
			"api" => "APITES",
			"svr"=> $_SESSION["conn"]->Server,
			"db" => $_SESSION["conn"]->Database,
			"uid" => SQL_UID,
			"pwd" => SQL_PWD
			];
			
			$post['config'] = $config;
			$post['user'] = $_SESSION['logged_in']['username'];
			$post['kd_lokasi'] = $_SESSION['logged_in']['branch_id'];
			
			$urlBhakti = $_SESSION["conn"]->AlamatWebService;
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.API_BKT."/Kwitansi/Save",
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
			
			// echo($response);
			
			if ($httpcode!=200) {
				$res = array();
				$res['msg'] = 'FAILED';
				$res['error'] = 'API Tujuan OFFLINE';

				$params['Remarks']="FAILED - API Tujuan OFFLINE";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			else {

				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				$result = json_decode($response, true);
				$res = array();
				$res['msg'] = $result['msg'];
				$res['error'] = $result['error'];
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
		if(isset($_SESSION['conn'])) {

			$params = array();			
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "KWITANSI LAIN LAIN";
			$params['TrxID'] = $NoKwitansi;
			$params['Description'] = $_SESSION["logged_in"]["username"]." VIEW PDF KWITANSI LAIN LAIN";
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);
		
			$data = [
			"NoKwitansi" => $NoKwitansi,
			"api" => "APITES",
			"svr"=> $_SESSION["conn"]->Server,
			"db" => $_SESSION["conn"]->Database,
			"uid" => SQL_UID,
			"pwd" => SQL_PWD
			];
			
			$urlBhakti = $_SESSION["conn"]->AlamatWebService;
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $urlBhakti.API_BKT."/Kwitansi/ViewPDF",
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
					echo '<object data="data:application/pdf;base64,'.$result->pdf.'#toolbar=0" type="application/pdf" width="100%" height="100%">';
					echo '<p>Unable to display PDF file.</p>';
					echo '</object>';
				}
				else die("File PDF tidak ditemukan!");
			}
			
		}
		else{
			redirect('KwitansiLainLain');
		}
	}
}