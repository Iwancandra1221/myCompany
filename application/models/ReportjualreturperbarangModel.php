<?php
	class ReportjualreturperbarangModel extends CI_Model
	{
		public function __construct(){
			parent::__construct();
			$this->load->model('GzipDecodeModel');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
			$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
		}

		function pergroup_item_fokus()
		{
			$item_fokus = json_decode(file_get_contents($this->API_URL."/MasterGroupItemFokus/itemfocus?api=APITES"),true);
			return $item_fokus;
		} 

		function tipe_faktur()
		{
			$item_fokus = json_decode(file_get_contents($this->API_URL."/TipeFaktur/gets?api=APITES"),true);
			return $item_fokus;
		}

		function wilayah()
		{
			$item_fokus = json_decode(file_get_contents($this->API_URL."/MsWilayah/wilayah?api=APITES&location=".$_SESSION['conn']->LocationCode),true);
			return $item_fokus;
		}

		function ParentDiv()
		{
			$item_fokus = json_decode(file_get_contents($this->API_URL."/MsDivisi/ParentDiv?api=APITES"),true);
			return $item_fokus;
		}

		function Divisi()
		{
			$item_fokus = json_decode(file_get_contents($this->API_URL."/MsDivisi/GetListDivisi?api=APITES"),true);
			return $item_fokus;
		}

		function Dealer($wilayah='ALL')
		{
			// $item_fokus = json_decode(file_get_contents($this->API_URL."/MsDealer/Dealer?api=APITES&wilayah=".$wilayah),true);
			$item_fokus = file_get_contents($this->API_URL."/MsDealer/Dealer?api=APITES&wilayah=".$wilayah);
			$item_fokus = $this->GzipDecodeModel->_decodeGzip_true($item_fokus);
			return $item_fokus;
		}

		function list615($data){
			$URLAPI = $_SESSION['conn']->AlamatWebService.$this->API_BKT.'/Reportjualreturperbarang/list615/?api=APITES';
			// $URLAPI = 'http://localhost/bktAPI/Reportjualreturperbarang/list615/?api=APITES';

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $URLAPI);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$response = curl_exec($curl);
			return $response; 
			
		}

		function list616($data){
			$URLAPI = $_SESSION['conn']->AlamatWebService.$this->API_BKT.'/Reportjualreturperbarang/list615/?api=APITES';
			// $URLAPI = $_SESSION['conn']->AlamatWebService.'/bktAPI/Reportjualreturperbarang/list615/?api=APITES';

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