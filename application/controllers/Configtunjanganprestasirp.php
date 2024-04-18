<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Configtunjanganprestasirp extends MY_Controller {

	function __construct()
	{
		parent::__construct();  
		$this->load->model('Configtunjanganprestasirpmodel');
	}
	 
	public function Index()
	{
		$data = array();
		$data["emp_position"] = $this->emp_position();
		$data["emp_level"] = $this->emp_level();
		
		$opt = $this->get_filter();
		$data["optEmpPosition"] = $opt['optEmpPosition'];
		$data["optEmpLevel"] = $opt['optEmpLevel'];
		$data["optStartDate"] = $opt['optStartDate'];
		$this->RenderView("Configtunjanganprestasirpview", $data);
	}

	public function get_detail()
	{ 	
		// echo json_encode($_POST); die;
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => API_URL."/Configtunjanganprestasirp/get_detail",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($_POST),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $response;die;
		if($httpcode==200){
			$res['result'] ='success';
			$res['data'] = json_decode($response, true);
			$res['error'] = '';
			echo json_encode($res);
		}
		else{
			$res['result'] ='failed';
			$res['data'] = array();
			$res['error'] = API_URL.' tidak bisa diakses!';
			echo json_encode($res);
		}
	}

	public function save()
	{ 
		// echo json_encode($_POST); die;
		$_POST['user'] = $_SESSION['logged_in']['username'];
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => API_URL."/Configtunjanganprestasirp/save",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($_POST),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		echo($response);
	}

	public function delete()
	{ 
		// echo json_encode($_POST); die;
		// $_POST['user'] = $_SESSION['logged_in']['username'];
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => API_URL."/Configtunjanganprestasirp/delete",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($_POST),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		echo($response);
	}
	
	public function datatable_config()
	{
		$param = $_GET;
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => API_URL."/Configtunjanganprestasirp/datatable_config",
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
	
	public function get_filter()
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => API_URL."/Configtunjanganprestasirp/get_filter",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $response;die;
		if($httpcode==200){
			return json_decode($response, true);
		}
		else return array('optEmpPosition'=>[],'optEmpLevel'=>[],'optStartDate'=>[]);
	}
	
	public function emp_position()
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => API_URL."/Configtunjanganprestasirp/emp_position",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		if($httpcode==200){
			return json_decode($response, true);
		}
		else return array();
	}
	
	public function emp_level()
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => API_URL."/Configtunjanganprestasirp/emp_level",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		if($httpcode==200){
			return json_decode($response, true);
		}
		else return array();
	}
	
}
