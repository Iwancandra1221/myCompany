<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class HitungDendaPerFaktur extends MY_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('GzipDecodeModel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	private function _postRequest($url,$data){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
		//curl_setopt($ch, CURLOPT_POSTFIELDS,"postvar1=value1&postvar2=value2&postvar3=value3");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);
		curl_close($ch);

		// echo '<pre>';
		// print_r($server_output);
		// echo '</pre>';
		// return false;
		return $server_output;
	}


	public function index(){
				
		$url = $this->API_URL."/MsSalesman/GetSalesmanList?isactive=y";
		$GetSalesman = '';
		$GetSalesman = file_get_contents($url);
		$GetSalesman = $this->GzipDecodeModel->_decodeGzip_true($GetSalesman);
		// $GetSalesman = json_decode($GetSalesman,true);
		if($GetSalesman!=null && $GetSalesman['success']){
			$GetSalesman = $GetSalesman['data'];
		}

		$body = array(
			'title' => 'Hitung Denda Per Faktur | '.WEBTITLE,
			'salesman' => $GetSalesman,
		);

		$params = array();	
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "HITUNG DENDA PER FAKTUR";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU HITUNG DENDA PER FAKTUR";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$this->RenderView('HitungDendaPerFakturView',$body);
		
	}

	function ceklistdata(){

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->API_URL."/HitungDendaPerFaktur/CekIncentive1",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 1000,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => http_build_query($this->input->post())
		));

		$result = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		                       
		print_r($result);
	}

	public function ProsesExcel() {
			$api =  $this->input->get('api');
			$Kd_Slsman = $this->input->get('Kd_Slsman');
			$Nm_Slsman = base64_decode($this->input->get('Nm_Slsman'));
			$TahunAwal = $this->input->get('TahunAwal');
			$BulanAwal = $this->input->get('BulanAwal');
			$TahunAkhir = $this->input->get('TahunAkhir');
			$BulanAkhir = $this->input->get('BulanAkhir');
			
			// print_r($Nm_Slsman);
			// die;

			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "HITUNG DENDA PER FAKTUR";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES HITUNG DENDA PER FAKTUR";
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$url = $this->API_URL."/HitungDendaPerFaktur/GetIncentive1?api=APITES".
																"&Kd_Slsman=".$Kd_Slsman.
																"&TahunAwal=".$TahunAwal.
																"&BulanAwal=".$BulanAwal.
																"&TahunAkhir=".$TahunAkhir.
																"&BulanAkhir=".$BulanAkhir
			;
			
			// print_r($url);
			// die;

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "");
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type:application/json',
				));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$DataLaporan = json_decode(curl_exec($ch));
			
			// print_r($DataLaporan);
			// die;

			$Judul = "Hitung Denda Per Faktur";
			$Salesman = "Salesman = ".$Kd_Slsman." - ".$Nm_Slsman;
			$Periode = "Periode = ".$BulanAwal."/".$TahunAwal." S/D ".$BulanAkhir."/".$TahunAkhir;
			$page_title = $Judul;
			
			$body = array(
				'Judul' => $Judul,
				'Periode' => $Periode,
				'Salesman' => $Salesman,
				'laporan' => $DataLaporan,
			);

			$this->load->view('template_xls/HitungDendaPerFakturXls',$body);
				
			

	}
}
?>