<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
header('Access-Control-Allow-Origin: *');

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanMutasiPindahStokAntarDivisi extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('HelperModel');
		$this->load->helper('FormLibrary');
		$this->load->library("Excel");
	}
	public function index(){
		
		$submit = $this->input->post('submit');

		$urlService = $_SESSION["conn"]->AlamatWebService;
		$urlService = "http://localhost/";

		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;
		
		if($submit==''){
			
			$url = $urlService.API_BKT.'/LaporanMutasiPindahStokAntarDivisi/ListCabang';
			$payload = array(
				'api' => 'APITES',
				'svr' => $svr,
				'db' => $db,

			);
			$listCabang = $this->_postRequest($url,$payload);
			
			$formDest = base_url().'LaporanMutasiPindahStokAntarDivisi';
			$data = array(
				'formDest' => $formDest,
				'listCabang' => $listCabang,
			);

			$this->RenderView('LaporanMutasiPindahStokAntarDivisiView', $data);
		}
		else{
			$tipeLaporan = $this->input->post('tipe_laporan');
			$tgl = $this->input->post('tgl');
			$cabang = $this->input->post('cabang');
			
			if($tgl!=''){
				$date = DateTime::createFromFormat("M/d/Y",$tgl);				
				$tgl = '';
				if($date!=null) $tgl = $date->format('Y-m-d');
					
			}
			$url = $urlService.API_BKT.'/LaporanMutasiPindahStokAntarDivisi/Laporan';
			$payload = array(
				'api' => 'APITES',
				'svr' => $svr,
				'db' => $db,
				'tipe_laporan' => $tipeLaporan,
				'tgl' => $tgl,
				'cabang' => $cabang,
			);
			$result = $this->_postRequest($url,$payload);
			// echo '<pre>';
			// print_r($result);
			// echo '</pre>';
			if($submit=='PREVIEW'){
				$data = array(
					'report' => $result,
					'tipe_laporan' => $tipeLaporan,
					'tgl' => $tgl,
					'cabang' => $cabang,
				);
				$this->load->view('LaporanMutasiPindahStokAntarDivisiPreviewView',$data);
			}
			else{
				//EXCEL
				$data = array(
					'report' => $result,
					'tipe_laporan' => $tipeLaporan,
					'tgl' => $tgl,
					'cabang' => $cabang,
				);
				$this->load->view('template_xls/ExportLaporanMutasiPindahStokAntarDivisi',$data);
			}
		}
		
		
		
	}
	private function _postRequest($url,$data){

		$options = array(
		    'http' => array(
		   	 	'method' => 'POST',
		    	'content' => http_build_query($data),
		    	'header'  => 'Content-type: application/x-www-form-urlencoded',
			),
		    
		);
		$stream = stream_context_create($options);
		$getContent = file_get_contents($url, false, $stream);
		$result = json_decode($getContent,true);
		return $result;
	}
}