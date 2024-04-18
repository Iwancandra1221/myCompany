<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	
	class Bass extends MY_Controller
	{
	
		public function __construct()
		{
			parent::__construct();
			$this->load->model('GzipDecodeModel');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
			$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
		}

		public function index()
		{
			//http://localhost:90/myCompany/Bass
			//$post = $this->PopulatePost();
			$data = array();
			$data["url"] = $this->API_URL;

			$data["OptMapping"] = "UNMAPPED BASS";
			$data["FilterMapped"] = 'N';
			$data["FilterBass"] = '';
			$data["FilterCabang"] = 'C001';
			
			$UnmappedBass = $this->CallBhaktiPurnaJual_listdealertomapping($data["FilterMapped"], $data["FilterBass"], $data["FilterCabang"]);
			//echo json_encode($UnmappedBass);
			// $data["UnmappedBass"] = array();
			$data["UnmappedBass"] = $UnmappedBass;
			$data["mappedBass"] = array();

			$ListDealer = $this->CallBhakti_listalldealeraktif();
			// echo json_encode($ListDealer);
			$Dealers = array();
			// if($Dealers!=null){
				foreach($ListDealer['data'] as $e) {
					// array_push($Dealers, trim($e['kd_Plg'])." - ".trim($e['nm_plg'])." - ".trim($e['Kd_Wil'])." - ".trim($e['Wilayah'])." - ".trim($e['alamat'])." - ".trim($e['kd_Lokasi']));
					array_push($Dealers, trim($e['KD_PLG'])." - ".trim($e['NM_PLG'])." - ".trim($e['Kd_Wil'])." - ".trim($e['WILAYAH'])." - ".trim($e['ALM_PLG'])." - ".trim($e['KD_LOKASI']));
				}
			// }
			
			$data["ListDealer"] = $ListDealer;
			$data["Dealers"] = $Dealers;

			$data["BranchIDList"] = "";

			$this->RenderView('BassView',$data);


		}
		
		private function _postRequest($url,$data){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
			//curl_setopt($ch, CURLOPT_POSTFIELDS,"postvar1=value1&postvar2=value2&postvar3=value3");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$result = curl_exec($ch);
			return $result;
		}

		public function mappedBass()
		{
			$data = array();
			$data["url"] = $this->API_URL;

			$data["OptMapping"] = "MAPPED BASS";
			$data["FilterMapped"] = 'Y';
			$data["FilterBass"] = '';
			$data["FilterCabang"] = 'C001';
			
			$mappedBass = $this->CallBhaktiPurnaJual_listdealertomapping($data["FilterMapped"], $data["FilterBass"], $data["FilterCabang"]);
			//echo json_encode($mappedBass);
			$data["UnmappedBass"] = array();
			$data["mappedBass"] = $mappedBass;

			$ListDealer = $this->CallBhakti_listalldealeraktif();
			//echo json_encode($ListDealer);

			$Dealers = array();
			foreach($ListDealer['data'] as $e) {
				array_push($Dealers, trim($e['KD_PLG'])." - ".trim($e['NM_PLG'])." - ".trim($e['Kd_Wil'])." - ".trim($e['WILAYAH'])." - ".trim($e['ALM_PLG'])." - ".trim($e['KD_LOKASI']));
			}
			$data["ListDealer"] = $ListDealer;
			$data["Dealers"] = $Dealers;

			$data["BranchIDList"] = "";

			$this->RenderView('BassView',$data);


		}

		public function CallBhaktiPurnaJual_listdealertomapping($MAPPED='',$KODE_BASS='',$KODE_CABANG='')
		{	
			$url = $this->API_URL."/MsDealer/getlistdealertomapping?MAPPED=".urlencode($MAPPED)."&KODE_BASS=".urlencode($KODE_BASS).
						"&KODE_CABANG=".urlencode($KODE_CABANG);
			//die($url);
			$CallBhaktiPurnaJual_listdealertomapping = json_decode($this->_postRequest($url,array()), true);
			return $CallBhaktiPurnaJual_listdealertomapping;
		}

		public function CallBhakti_listalldealeraktif()
		{	
			$url = $this->API_URL."/MsDealer/getlistalldealeraktif?api=APITES";
			// $url = "http://localhost:90/webAPI/MsDealer/GetListAllDealer?api=APITES";
			//die($url);
			$CallBhakti_listalldealeraktif = $this->_postRequest($url,array());
			$CallBhakti_listalldealeraktif = $this->GzipDecodeModel->_decodeGzip_true($CallBhakti_listalldealeraktif);
			return $CallBhakti_listalldealeraktif;
		}

		public function CaribktAPI_webAPI()
		{
			$post = $this->PopulatePost();
			$location_code = $post["location_code"];
			if ($location_code == 'DMI'){
				$location_code = 'JKT';
			}
			$KODE_BASS = $post["kode_bass"];
			$KD_PLG = $post["kode_plg"];
			$KD_WIL = $post["kode_wil"];

			$res = $this->MasterDbModel->getByLocationCode($location_code);
			$AlamatWebService = $res->AlamatWebService;

			$bass = array();
			$bass = $this->CallBhaktiPurnaJual_listdealertomapping('A', $KODE_BASS, 'C001');

			$KODE_BASS = $bass[0]['KODE_BASS'];
			$NAMA_BASS = $bass[0]['NAMA_BASS'];
			$ALAMAT_BASS = $bass[0]['ALAMAT_BASS'];
			$NOMOR_TELP = $bass[0]['NOMOR_TELP'];
			$KOTA = $bass[0]['KOTA'];
			$CONTACT_PERSON = $bass[0]['CONTACT_PERSON'];
			$EMAIL = $bass[0]['EMAIL'];
			$INPUTTED_BY = $bass[0]['INPUTTED_BY'];
			$INPUTTED_BY_BASS = $bass[0]['INPUTTED_BY_BASS'];
			$INPUTTED_DATE = $bass[0]['INPUTTED_DATE'];
			$TYPE = $bass[0]['TYPE'];
			$FLAG = $bass[0]['FLAG'];

			$result = array();

			//WebAPI
			//http://localhost:90/webAPI/MsDealer/mappingbass?KODE_BASS=B195&KD_PLG=DMIC049&KD_WIL=NNNN
			$url = $this->API_URL."/MsDealer/mappingbass?KODE_BASS=".urlencode($KODE_BASS)."&KD_PLG=".urlencode($KD_PLG).
						"&KD_WIL=".urlencode($KD_WIL);
			$result = file_get_contents($url);

			$url = $AlamatWebService.$this->API_BKT."/MasterDealer/mappingbass";
			//die($url);
			$fields = array(
				'KODE_BASS' => $KODE_BASS,
				'NAMA_BASS' => $NAMA_BASS,
				'ALAMAT_BASS' => $ALAMAT_BASS,
				'NOMOR_TELP' => $NOMOR_TELP,
				'KOTA' => $KOTA,
				'CONTACT_PERSON' => $CONTACT_PERSON,
				'EMAIL' => $EMAIL,
				'KD_PLG' => $KD_PLG,
				'KD_WIL' => $KD_WIL,
				'INPUTTED_BY' => $INPUTTED_BY,
				'INPUTTED_BY_BASS' => $INPUTTED_BY_BASS,
				'INPUTTED_DATE' => $INPUTTED_DATE,
				'TYPE' => $TYPE,
				'FLAG' => $FLAG
			 );
			 
			 // build the urlencoded data
			 $postvars = http_build_query($fields);
			 
			 // open connection
			 $ch = curl_init();
			 
			 // set the url, number of POST vars, POST data
			 curl_setopt($ch, CURLOPT_URL, $url);
			 curl_setopt($ch, CURLOPT_POST, count($fields));
			 curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
			 
			 // execute post
			 $result = curl_exec($ch);
			 // close connection
			 curl_close($ch);



		}

		public function Test()
		{
			//http://localhost:90/myCompany/Bass/Test?location_code=DMI
			$location_code = urldecode($this->input->get('location_code'));
			$KODE_BASS = 'B195';
			$KD_PLG = 'DMIC049';
			$KD_WIL = 'NNNN';
			if ($location_code == 'DMI'){
				$location_code = 'JKT';
			}

			$res = $this->MasterDbModel->getByLocationCode($location_code);
			$AlamatWebService = $res->AlamatWebService;

			$bass = array();
			$bass = $this->CallBhaktiPurnaJual_listdealertomapping('Y', $KODE_BASS, 'C001');

			$KODE_BASS = $bass[0]['KODE_BASS'];
			$NAMA_BASS = $bass[0]['NAMA_BASS'];
			$ALAMAT_BASS = $bass[0]['ALAMAT_BASS'];
			$NOMOR_TELP = $bass[0]['NOMOR_TELP'];
			$KOTA = $bass[0]['KOTA'];
			$CONTACT_PERSON = $bass[0]['CONTACT_PERSON'];
			$EMAIL = $bass[0]['EMAIL'];
			$INPUTTED_BY = $bass[0]['INPUTTED_BY'];
			$INPUTTED_BY_BASS = $bass[0]['INPUTTED_BY_BASS'];
			$INPUTTED_DATE = $bass[0]['INPUTTED_DATE'];
			$TYPE = $bass[0]['TYPE'];
			$FLAG = $bass[0]['FLAG'];

			$result = array();
			$url = $AlamatWebService.$this->API_BKT."/MasterDealer/mappingbass";
			//die($url);
			$fields = array(
				'KODE_BASS' => $KODE_BASS,
				'NAMA_BASS' => $NAMA_BASS,
				'ALAMAT_BASS' => $ALAMAT_BASS,
				'NOMOR_TELP' => $NOMOR_TELP,
				'KOTA' => $KOTA,
				'CONTACT_PERSON' => $CONTACT_PERSON,
				'EMAIL' => $EMAIL,
				'KD_PLG' => $KD_PLG,
				'KD_WIL' => $KD_WIL,
				'INPUTTED_BY' => $INPUTTED_BY,
				'INPUTTED_BY_BASS' => $INPUTTED_BY_BASS,
				'INPUTTED_DATE' => $INPUTTED_DATE,
				'TYPE' => $TYPE,
				'FLAG' => $FLAG
			 );
			 
			 // build the urlencoded data
			 $postvars = http_build_query($fields);
			 
			 // open connection
			 $ch = curl_init();
			 
			 // set the url, number of POST vars, POST data
			 curl_setopt($ch, CURLOPT_URL, $url);
			 curl_setopt($ch, CURLOPT_POST, count($fields));
			 curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
			 
			 // execute post
			 $result = curl_exec($ch);
			 // close connection
			 curl_close($ch);

			 echo $result;

		}

	}
?>