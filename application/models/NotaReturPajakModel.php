<?php

class NotaReturPajakModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
	}

	function search($data)
	{
		$url = $_SESSION["conn"]->AlamatWebService.$this->API_BKT;

		// $fakturpajak = json_decode(file_get_contents($this->API_BKT."/NotaReturPajak/SeachNumber?api=APITES&number=".$data),true);
		$fakturpajak = json_decode(file_get_contents($url."/NotaReturPajak/SeachNumber?api=APITES&number=".$data),true);

		print_r(json_encode($fakturpajak));
	}

	function wilayah()
	{
		// print_r($this->API_URL."/MsWilayah/GetWilayahHO?api=APITES");
		// die("!");

		$wilayah = json_decode(file_get_contents($this->API_URL."/MsWilayah/GetWilayahHO?api=APITES"),true);
		return $wilayah;
	}

	function TipeFaktur()
	{
		// print_r($_SESSION["conn"]->AlamatWebService);
		$url = $_SESSION["conn"]->AlamatWebService.$this->API_BKT;

		// print_r($url."/NotaReturPajak/TipeFaktur?api=APITES");
		// die("!");

		$TipeFaktur = json_decode(file_get_contents($url."/NotaReturPajak/TipeFaktur?api=APITES"),true);

		// print_r($TipeFaktur);
		// die("!");

		return $TipeFaktur;
	}

}
?>