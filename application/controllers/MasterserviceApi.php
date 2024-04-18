<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MasterserviceApi extends CI_Controller {

	public function __construct()
	{
		parent::__construct(); 
		$this->load->model('MasterserviceModel');
	} 

	public function get_data_service()
	{  
		set_time_limit(600);
		
		$page = 1;
		$page_count = 0;
		$dataPerbaikan = [];
		while($page>0){
			$res = $this->MasterserviceModel->getSyncData('ms_service_perbaikan', $page, $page_count);
			$dataPerbaikan = array_merge($dataPerbaikan, $res['data']);
			$page = $res['page'];
			$page_count = $res['page_count'];
		}
		
		$page = 1;
		$page_count = 0;
		$dataPenyebab = [];
		while($page>0){
			$res = $this->MasterserviceModel->getSyncData('ms_service_penyebab', $page, $page_count);
			$dataPenyebab = array_merge($dataPenyebab, $res['data']);
			$page = $res['page'];
			$page_count = $res['page_count'];
		}
		
		$page = 1;
		$page_count = 0;
		$dataKerusakan = [];
		while($page>0){
			$res = $this->MasterserviceModel->getSyncData('ms_service_kerusakan', $page, $page_count);
			$dataKerusakan = array_merge($dataKerusakan, $res['data']);
			$page = $res['page'];
			$page_count = $res['page_count'];
		}
		
		
		$page = 1;
		$page_count = 0;
		$dataJnsBrg = [];
		while($page>0){
			$res = $this->MasterserviceModel->getSyncData('ms_service_jnsbrg', $page, $page_count);
			$dataJnsBrg = array_merge($dataJnsBrg, $res['data']);
			$page = $res['page'];
			$page_count = $res['page_count'];
		}
		
		$page = 1;
		$page_count = 0;
		$dataService = [];
		while($page>0){
			$res = $this->MasterserviceModel->getSyncData('ms_service', $page, $page_count);
			$dataService = array_merge($dataService, $res['data']);
			$page = $res['page'];
			$page_count = $res['page_count'];
		}
		// echo json_encode($dataService);
		// die;
		
		$result["result"] = "sukses";
		$result["dataPerbaikan"] = $dataPerbaikan;
		$result["dataPenyebab"] = $dataPenyebab;
		$result["dataKerusakan"] = $dataKerusakan;
		$result["dataJnsBrg"] = $dataJnsBrg;
		$result["dataService"] = $dataService;
		$result["error"]  = "";
		
		$hasil = json_encode($result);
		header('HTTP/1.1: 200');
		header('Status: 200');
		header('Content-Length: '.strlen($hasil));
		exit($hasil);
	}
}
?>