<?php
	Class PreOrderPembelianModel extends CI_Model
	{
		function __construct()
		{
			parent::__construct();
		}
		
		function RejectCabang($kd_lokasi,$no_prepo, $rejectnote){
			$strCabang = "";
			if($kd_lokasi=='DMI'){
				$strCabang = "select * from MsDatabase where branchid = '".$kd_lokasi."' and NamaDb= 'JAKARTA'";
			}
			else if($kd_lokasi=='BOG'){
				$strCabang = "select * from MsDatabase where branchid = 'JKT' and NamaDb= 'BOGOR'";
			}
			else if($kd_lokasi=='KRW'){
				$strCabang = "select * from MsDatabase where branchid = 'JKT' and NamaDb= 'KARAWANG'";
			}
			else if($kd_lokasi=='SRY'){
				$strCabang = "select * from MsDatabase where branchid = 'SBY'";
			}
			else{
				$strCabang = "select * from MsDatabase where branchid = '".$kd_lokasi."'";
			}
			
			$res = $this->db->query($strCabang);
			$cabangs = $res->result();
			
			foreach ($cabangs as $cabang) {
				
				$data['api'] = 'APITES';
				$data['Server'] = $cabang->Server;
				$data['Database'] = $cabang->Database;
				$data['Uid'] = SQL_UID;
				$data['Pwd'] = SQL_PWD;
				$data['no_prepo'] = $no_prepo;
				$data['reject_note'] = $rejectnote;
				
				$curl = curl_init();
				curl_setopt_array($curl, array(
				CURLOPT_URL => $cabang->AlamatWebService. "bktAPI/PreOrderPembelian/Reject",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 300,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => http_build_query($data),
				));
				
				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);
				// print_r($response);
				// die;
			}
		}
		
		function ApproveCabang($kd_lokasi,$no_prepo,$confirmed_by, $kd_brg){
		
			$strCabang = "";
			if($kd_lokasi=='DMI'){
				$strCabang = "select * from MsDatabase where branchid = '".$kd_lokasi."' and NamaDb= 'JAKARTA'";
			}
			else if($kd_lokasi=='BOG'){
				$strCabang = "select * from MsDatabase where branchid = 'JKT' and NamaDb= 'BOGOR'";
			}
			else if($kd_lokasi=='KRW'){
				$strCabang = "select * from MsDatabase where branchid = 'JKT' and NamaDb= 'KARAWANG'";
			}
			else if($kd_lokasi=='SRY'){
				$strCabang = "select * from MsDatabase where branchid = 'SBY'";
			}
			else{
				$strCabang = "select * from MsDatabase where branchid = '".$kd_lokasi."'";
			}
			
			$res = $this->db->query($strCabang);
			$cabangs = $res->result();
			
			foreach ($cabangs as $cabang) {
			
				$data['api'] = 'APITES';
				$data['Server'] = $cabang->Server;
				$data['Database'] = $cabang->Database;
				$data['Uid'] = SQL_UID;
				$data['Pwd'] = SQL_PWD;
				$data['no_prepo'] = $no_prepo;
				$data['confirmed_by'] = $confirmed_by;
				$data['kd_brg'] = $kd_brg;
				
				$curl = curl_init();
				curl_setopt_array($curl, array(
				CURLOPT_URL => $cabang->AlamatWebService. "bktAPI/PreOrderPembelian/Approve",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 300,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => http_build_query($data),
				));
				
				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);
				
				return $response;
			}
			
		}
	}
?>