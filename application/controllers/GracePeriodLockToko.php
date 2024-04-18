<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class GracePeriodLockToko extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function index($sukses=0)
	{
		$data = array();
		$url = $this->API_URL."/ConfigPenjualan/GetGracePeriodLockTokoList?api=APITES&user=".urlencode($_SESSION["logged_in"]["username"]);
		// die($url);
		// echo json_encode($url); die;
		$GracePeriodLockToko = json_decode(file_get_contents($url), true);
		if ($GracePeriodLockToko["result"]=="sukses") {
			$data["result"] = $GracePeriodLockToko["data"];
		} else {
			echo json_encode(array('error'=>$GracePeriodLockToko["error"]));
		}
       	if ($sukses==1) {
       		$data["alert"] = "Update Berhasil";
       	}
		$this->RenderView('GracePeriodLockTokoView',$data);
	}

	public function Update()
	{
		$post = $this->PopulatePost();
		$data = [
			"api" => "APITES",
			"GracePeriod_LockToko_ModernOutlet" => $post["GracePeriod_LockToko_ModernOutlet"],
			"GracePeriod_LockToko_Proyek" => $post["GracePeriod_LockToko_Proyek"],
			"GracePeriod_LockToko_Tradisional" => $post["GracePeriod_LockToko_Tradisional"],
			"user" => $_SESSION["logged_in"]["username"]
		];
					
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->API_URL."/ConfigPenjualan/UpdateGracePeriod",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		
		// echo json_encode($response);die;
		
		$result = json_decode($response);
		if($result->result=='sukses'){
			redirect('GracePeriodLockToko?success=1');
		}
		else{
			redirect('GracePeriodLockToko?success=0');
		}
	}
}