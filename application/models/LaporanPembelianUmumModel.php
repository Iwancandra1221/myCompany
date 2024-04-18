<?php
class LaporanPembelianUmumModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$CI = &get_instance();
		$this->load->model('GzipDecodeModel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	function GetSupplier(){
		$url =$this->API_URL.'/MsSupplier/getListSupplier_X/?api=APITES';
		$supplier = json_decode(file_get_contents($url), true);
		return $supplier;
	}
	function GetCabang(){
		$url =$this->API_URL.'/Cabang/getListCabang_X/?api=APITES';
		$cabang = json_decode(file_get_contents($url), true);
		return $cabang;
	}
	function GetGudang(){
		$url =$this->API_URL.'/MsGudang/GetListGudang_X/?api=APITES';
		// $gudang = json_decode(file_get_contents($url), true);
		$gudang = file_get_contents($url);
		$gudang = $this->GzipDecodeModel->_decodeGzip_true($gudang);
		return $gudang;
	}
	function GetData($data=''){
		$URLAPI = $this->API_URL.'/LaporanPembelianUmum/?api=APITES';

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $URLAPI);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($curl);
		return $response; 

	}
}
?>