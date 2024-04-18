<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Connection extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
	} 
    public function GetConnection()
	{
		$api = $this->input->get('api');
		$lok = $this->input->get('lok');
		$data = array();
		if("APITES"==$api) {
			$result = $this->MasterDbModel->GetConnection($lok);
			if (count($result)>0) {
				$data["result"] = "sukses";
				$data["conns"] = $result;
				$data["error"] = "";
			} else {
				$data["result"] = "gagal";
				$data["conns"] = $result;
				$data["error"] = "Tidak ada Data";
			}
		}
		else {
			$data["result"] = "gagal";
			$data["conns"] = array();
			$data["error"] = "Kode API Tidak Dikenali";
		}		

		$hasil = json_encode($data);
		//die($hasil);
		header('HTTP/1.1: 200');
		header('Status: 200');
		header('Content-Length: '.strlen($hasil));
		exit($hasil);
	}
}