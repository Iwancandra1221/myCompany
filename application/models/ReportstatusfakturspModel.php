<?php
	class ReportstatusfakturspModel extends CI_Model
	{
		public function __construct()
		{
			parent::__construct();
		}

		public function Getnofaktur(){
			$url =$_SESSION['conn']->AlamatWebService.API_BKT.'/Reportstatusfaktursp/Getnofaktur/?api=APITES&svr='.$_SESSION['conn']->Server.'&db='.$_SESSION['conn']->Database;
			$no_faktur = json_decode(file_get_contents($url), true);
			return $no_faktur;
		}
		
		public function Getgudang(){
			$url =$_SESSION['conn']->AlamatWebService.API_BKT.'/Reportstatusfaktursp/Getgudang/?api=APITES&svr='.$_SESSION['conn']->Server.'&db='.$_SESSION['conn']->Database;
			$no_faktur = json_decode(file_get_contents($url), true);
			return $no_faktur;
		}

		public function proses_data($data=''){
		
			$url =$_SESSION['conn']->AlamatWebService.API_BKT.'/Reportstatusfaktursp/ProsesData/?api=APITES&svr='.$_SESSION['conn']->Server.'&db='.$_SESSION['conn']->Database;

			$data['userid']=$_SESSION['logged_in']["userid"];

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$response = curl_exec($curl);
			return $response; 
			
		}

		public function ProsesApiData($proses='',$acak=''){

			$url =$_SESSION['conn']->AlamatWebService.API_BKT.'/Reportstatusfaktursp/ProsesApiData/?api=APITES&svr='.$_SESSION['conn']->Server.'&db='.$_SESSION['conn']->Database.'&proses='.$proses;
			$data['acak']	= $_SESSION['logged_in']["userid"];
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