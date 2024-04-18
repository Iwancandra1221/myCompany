<?php
class ReportnkndModel extends CI_Model{
	public function __construct(){
		parent::__construct();
	}

	public function type_nota(){
		$url =$_SESSION['conn']->AlamatWebService.API_BKT.'/Reportnknd/type_nota/?api=APITES';
		$type_nota = json_decode(file_get_contents($url), true);
		return $type_nota;
	}

	public function kategori_khusus(){
		$url =$_SESSION['conn']->AlamatWebService.API_BKT.'/Reportnknd/kategori_khusus/?api=APITES';
		$kategori_khusus = json_decode(file_get_contents($url), true);
		return $kategori_khusus;
	}

	public function partner_type(){
		$url =$_SESSION['conn']->AlamatWebService.API_BKT.'/Reportnknd/partner_type/?api=APITES';
		$partner_type = json_decode(file_get_contents($url), true);
		return $partner_type;
	}

	public function wilayah(){
		$url =$_SESSION['conn']->AlamatWebService.API_BKT.'/Reportnknd/wilayah/?api=APITES';
		$wilayah = json_decode(file_get_contents($url), true);
		return $wilayah;
	}

	public function dealer($wilayah='ALL'){
		set_time_limit(60); 

		$url =$_SESSION['conn']->AlamatWebService.API_BKT.'/Reportnknd/dealer/?api=APITES&wilayah='.$wilayah;
		$dealer = json_decode(file_get_contents($url), true);
		return $dealer;

	}

	public function GetData($data=''){
		$url =$_SESSION['conn']->AlamatWebService.API_BKT.'/Reportnknd/?api=APITES';

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($curl);
		return $response; 
	}
}
?>