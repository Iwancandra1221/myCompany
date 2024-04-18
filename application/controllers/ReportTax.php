<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportTax extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('HelperModel');
		$this->load->model('GzipDecodeModel');
		$this->load->helper('FormLibrary');
		$this->load->library('email');
		$this->load->library('excel');
	}
	private function _postRequest($url,$data){
		// $options = array(
		//     'http' => array(
		//    	 	'method' => 'POST',
		//     	'content' => http_build_query($data),
		//     	'header'  => 'Content-type: application/x-www-form-urlencoded',
		// 	),
		    
		// );
		// $stream = stream_context_create($options);
		// $getContent = file_get_contents($url, false, $stream);
		// $result = json_decode($getContent,true);
		// return $result;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
		//curl_setopt($ch, CURLOPT_POSTFIELDS,"postvar1=value1&postvar2=value2&postvar3=value3");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);
		return $result;
	}
	public function exportPajakKeluaran(){
		//4D3.Faktur Pajak (H2H PAJAKKU)
		$submit = $this->input->post('submit');
		if($submit==''){
			$data = array(

			);

			// $wilayah = json_decode(file_get_contents(HO.API_BKT."/MasterDealer/GetListWilayah?api=APITES&ubranch="),true);
			$wilayah = file_get_contents(HO.API_BKT."/MasterDealer/GetListWilayah?api=APITES&ubranch=");
			$wilayah = $this->GzipDecodeModel->_decodeGzip_true($wilayah);
			$data["wilayah"] = $wilayah;
			if ($wilayah["result"]=="sukses") {
				$data["wilayah"] = $wilayah["data"];
			}
			else {
				$data["wilayah"] = array();
			}

			
			$data['title'] = 'REPORT TAX | EXPORT DATA PAJAK';
			$data['reportOption'] = "EXPORT DATA PAJAK";
			$data['formDest'] = "ReportTax/exportPajakKeluaran";
			$this->RenderView('ReportTax',$data);
		} else {
			$api = 'APITES';
			set_time_limit(120);
			
			if(!isset($_SESSION["conn"])) {
				redirect("ConnectDB");
			}
			
			$url = $_SESSION["conn"]->AlamatWebService;
			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;
			//--------------

			$kriteria = $this->input->post('kriteria');

			$chkProduk = (int)$this->input->post('chk_produk');
			$chkSparepart = (int)$this->input->post('chk_sparepart');
			$chkService = (int)$this->input->post('chk_service');

			//----
			
			$tipeEFaktur = $this->input->post('tipe_e_faktur');//0 = e-faktur | 1 = e-faktur-new
			//----

			$tglAwal = $this->input->post('tgl_awal');
			$tglAkhir = $this->input->post('tgl_akhir');

			$tglAwal2 = $this->input->post('tgl_awal2');
			$tglAkhir2 = $this->input->post('tgl_akhir2');

			$wilayah = $this->input->post('wilayah');
			$tglEditFp = $this->input->post('tgl_edit_fp');//0 = tgl FP | 1 = tgl edit FP
			$kodeCabang = $this->input->post('kode_cabang');
			$tipePkp = $this->input->post('tipe_pkp');//PKP | NON PKP | ALL = ''
			$tipeFaktur = $this->input->post('tipe_faktur');//ALL = '' | R P H O A

			////PRODUK | SPAREPART | SERVICE
			if($tglAwal!='') $tglAwal = date('Y-m-d',strtotime($tglAwal));
			if($tglAkhir!='') $tglAkhir = date('Y-m-d',strtotime($tglAkhir));

			if($tglAwal2!='') $tglAwal2 = date('Y-m-d',strtotime($tglAwal2));
			if($tglAkhir2!='') $tglAkhir2 = date('Y-m-d',strtotime($tglAkhir2));

			$kategori = '';
			
			$dataContent = array(
				'api' => $api, 
				'svr' => $svr,
				'db' => $db,
				'kriteria' => $kriteria,
				'kategori' => $kategori,
				'tipe_e_faktur' => $tipeEFaktur,
				'tgl_awal' => $tglAwal, 
				'tgl_akhir' => $tglAkhir,
				'tgl_awal2' => $tglAwal2, 
				'tgl_akhir2' => $tglAkhir2,
				'wilayah' => $wilayah,
				'tgl_edit_fp' => $tglEditFp,
				'kode_cabang' => $kodeCabang,
				'tipe_pkp' => $tipePkp,
				'tipe_faktur' => $tipeFaktur
			);

			if($kriteria=='FAKTUR'){
				if($chkProduk==1){
					// $kategori = 'PRODUK';
					$dataContent["kategori"] = 'PRODUK';
					$this->_exportXlsPajakKeluaran($dataContent);
				}
				if($chkSparepart==1){
					$dataContent["kategori"] = 'SPAREPART';
					$this->_exportXlsPajakKeluaran($dataContent);
				}
				if($chkService==1){
					$dataContent["kategori"] = 'SERVICE';
					$this->_exportXlsPajakKeluaran($dataContent);
				}
			}

			// switch ($tipeEFaktur) {
			// 	case '0':
			// 		//e-faktur
					
			// 		if($kriteria=='FAKTUR'){
			// 			if($chkProduk==1){
			// 				$kategori = 'PRODUK';
			// 				$this->_exportXls($dataContent,$kategori);
			// 			}
			// 			if($chkSparepart==1){
			// 				$kategori = 'SPAREPART';
			// 				$this->_exportXls($dataContent,$kategori);
			// 			}
			// 			if($chkService==1){
			// 				$kategori = 'SERVICE';
			// 				$this->_exportXls($dataContent,$kategori);
			// 			}
			// 		}
			// 		else if($kriteria=='RETUR'){
			// 			if($chkProduk==1){
			// 				$kategori = 'PRODUK';
			// 				$this->_exportXls($dataContent,$kategori);
			// 			}
			// 			if($chkSparepart==1){
			// 				$kategori = 'SPAREPART';
			// 				$this->_exportXls($dataContent,$kategori);
			// 			}
			// 		}
			// 		break;
			// 	case '1':
			// 		//e-faktur-new
			// 		if($kriteria=='FAKTUR'){
			// 			if($chkProduk==1){
			// 				$kategori = 'PRODUK';
			// 				$this->_exportXls($dataContent,$kategori);
			// 			}
			// 			if($chkSparepart==1){
			// 				$kategori = 'SPAREPART';
			// 				$this->_exportXls($dataContent,$kategori);
			// 			}
			// 			if($chkService==1){
			// 				$kategori = 'SERVICE';
			// 				$this->_exportXls($dataContent,$kategori);
			// 			}
			// 		}
			// 		else if($kriteria=='RETUR'){
			// 			if($chkProduk==1){
			// 				$kategori = 'PRODUK';
			// 				$this->_exportXls($dataContent,$kategori);
			// 			}
			// 			if($chkSparepart==1){
			// 				$kategori = 'SPAREPART';
			// 				$this->_exportXls($dataContent,$kategori);
			// 			}
			// 		}
			// 		break;
			// 	default:
			// 		// code...
			// 		break;
			// }
			
			
		}
	}
	private function _exportXlsPajakKeluaran($dataContent){
		$api = 'APITES';
		set_time_limit(120);
		
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}
		
		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;
		//--------------
		
		$urlCetakkStock = $url.API_BKT."/ReportTax/exportPajakKeluaran";
		//$urlCetakkStock = $url.'ci3'."/ReportTax/exportPajakKeluaran";

		// $options = array(
		//     'http' => array(
		//     	'method' => 'POST',
		//     	'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
	   	// 	 	'content' => http_build_query($dataContent)
   		//  	)
		// );
		// $stream = stream_context_create($options);
		$getResult =  $this->_postRequest($urlCetakkStock, $dataContent);
		$resultArray = json_decode($getResult,true);
		// echo '<pre>';
		// print_r($dataContent);
		// echo '</pre>';

		// echo '<pre>';
		// print_r($resultArray);
		// echo '</pre>';
		//------
		$data = array(
			'params' => $dataContent,
			'spreadsheet' => new Spreadsheet(),
			'resultArray' => $resultArray,
		);
		$this->load->view('template_xls/ExportPajakKeluaran',$data);
	}


}
