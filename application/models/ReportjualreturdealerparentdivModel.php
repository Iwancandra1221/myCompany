<?php
	class ReportjualreturdealerparentdivModel extends CI_Model
	{

		public function __construct(){
			parent::__construct();
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
			$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
		}

		public function divisi(){
			$url =$this->API_URL.'/MsDivisi/GetListParentDiv/?api=APITES';
			$divisi = json_decode(file_get_contents($url), true);
			return $divisi;
		}

		public function wilayah(){
			$url =$this->API_URL.'/MsWilayah/GetList/?api=APITES';
			$wilayah = json_decode(file_get_contents($url), true);
			return $wilayah;
		}

		public function tipe_faktur(){
			$url =$this->API_URL.'/MsTipeFaktur/GetList/?api=APITES';
			$wilayah = json_decode(file_get_contents($url), true);
			return $wilayah;
		}

		public function partner_type(){
			$url =$this->API_URL.'/MsPartnerType/GetList/?api=APITES';
			$partner_type = json_decode(file_get_contents($url), true);
			return $partner_type;

		}

		public function GetData($data){
			$url =$_SESSION['conn']->AlamatWebService.$this->API_BKT.'/Reportjualreturdealerparentdiv/dealer_per_parent_divisi/?api=APITES';

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