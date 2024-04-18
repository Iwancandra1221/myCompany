<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class Reportjualreturdivisisummary extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('HelperModel');
		$this->load->helper('FormLibrary');
		$this->load->library('email');
		$this->load->library('excel');
		// $this->load->model('MasterReportWilayahModel', 'ReportModel');
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
		$result = json_decode($server_output,true);
		return $result;
	}
	public function index(){
		ini_set('pcre.backtrack_limit', '10000000');
		$submit = $this->input->post('submit');
		if($submit==''){
			$productGroup = $this->_postRequest($this->API_URL.'/MsProductGroup/GetListProductGroup?api=APITES',array());
		
			if($productGroup!=null && $productGroup['data']!=null){
				$productGroup = $productGroup['data'];
			}
			else{
				$productGroup = array();
			}

			$parentDivisi = $this->_postRequest($this->API_URL.'/MsDivisi/GetListParentDiv?api=APITES',array());
			$wilayah = $this->_postRequest($this->API_URL.'/MsWilayah/GetListWilayah?api=APITES',array());
			$partnerType = $this->_postRequest($this->API_URL.'/MsPartnerType/GetListPartnerType?api=APITES',array());
			if($partnerType!=null && $partnerType['data']!=null){
				$partnerType = $partnerType['data'];
			}
			else{
				$partnerType = array();
			}
			$tipeFaktur = $this->_postRequest($this->API_URL.'/MsTipeFaktur/GetList?api=APITES',array());
			
			$data = array(
				'title' => 'Laporan Jual Retur Divisi Summary',
				'formUrl' => '',
				'parentDivisi' => $parentDivisi,
				'wilayah' => $wilayah,
				'partnerType' => $partnerType,
				'tipeFaktur' => $tipeFaktur,
				'productGroup' => $productGroup,
			);

			$this->RenderView('LaporanJualReturDivisiSummaryView',$data);
		}
		else{
			$msg = "";
			$chk_per_group_item_fokus = $this->input->post('chk_per_group_item_fokus');
			$per_group_item_fokus = $this->input->post('per_group_item_fokus');
			$periode_start = $this->input->post('periode_start');
			$periode_end = $this->input->post('periode_end');
			$parent_divisi = $this->input->post('parent_divisi');
			$kd_wil = $this->input->post('kd_wil');
			$kat_brg = $this->input->post('kat_brg');
			$partner_type = $this->input->post('partner_type');
			$tipe_faktur = $this->input->post('tipe_faktur');
			$tipe_laporan = $this->input->post('tipe_laporan');

			// echo '<pre>';
			// print_r($_POST);
			// echo '</pre>';
			if($chk_per_group_item_fokus!='on'){
				$per_group_item_fokus = '';
			}
			if($periode_start!=''){
				$date = DateTime::createFromFormat('m/d/Y',$periode_start);
				if($date!=null && $date->format('m/d/Y')==$periode_start){
					$periode_start = $date->format('Y-m-d');
				}
				else{
					$periode_start = '';
					$msg = 'format tanggal awal tidak sesuai MM/DD/YYYY';
				}
			}

			if($periode_end!=''){
				$date = DateTime::createFromFormat('m/d/Y',$periode_end);
				if($date!=null && $date->format('m/d/Y')==$periode_end){
					$periode_end = $date->format('Y-m-d');
				}
				else{
					$periode_end = '';
					$msg =  'format tanggal akhir tidak sesuai MM/DD/YYYY';
				}
			}
			if($parent_divisi=='ALL') $parent_divisi= '';
			if($kd_wil=='ALL') $kd_wil= '';
			if($tipe_faktur=='ALL') $tipe_faktur= '';

			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database; 
			$url = $_SESSION['conn']->AlamatWebService;
			$url = "http://localhost/";
			$url = $url.API_BKT.'/Reportjualreturdivisisummary/GetReport?svr='.$svr.'&db='.$db;
			$payload = array(
				'per_group_item_fokus' => $per_group_item_fokus,
				'periode_start' => $periode_start,
				'periode_end' => $periode_end,
				'parent_divisi' => $parent_divisi,
				'kd_wil' => $kd_wil,
				'kat_brg' => $kat_brg,
				'partner_type' => $partner_type,
				'tipe_faktur' => $tipe_faktur,
				'tipe_laporan' => $tipe_laporan,
			);
			// echo '<pre>';
			// print_r($payload);
			// echo '</pre>';

			$getReport = $this->_postRequest($url,$payload);
			// echo '<pre>';
			// print_r($getReport);
			// echo '</pre>';
			$reportTmp = array();
			$data = array(
				'reportTmp' => array(),
			);
			switch($submit){
				case 'EXPORT EXCEL':
					
					if($tipe_laporan==1){
						//
						if($getReport!=null && $getReport['code']==1){
							foreach($getReport['data'] as $value){
								$reportTmp[$value['DIVISI']][] = $value;
							}
							$data['reportTmp'] = $reportTmp;
						}
		
						$data['title'] = 'LAPORAN JUAL-RETUR PER DIVISI PER WILAYAH';
						$this->load->view('template_xls/LaporanJualReturPerDivisiPerWilayahXls',$data,true);
					}
					else if($tipe_laporan==2){
						//
						if($getReport!=null && $getReport['code']==1){
							foreach($getReport['data'] as $value){
								$reportTmp[$value['WILAYAH']][] = $value;
							}
							$data['reportTmp'] = $reportTmp;
						}
						$data['title'] = 'LAPORAN JUAL-RETUR PER WILAYAH PER DIVISI';
						$this->load->view('template_xls/LaporanJualReturPerWilayahPerDivisiXls',$data,true);
					}
					
				break;
				case 'EXPORT PDF':
					
					require_once __DIR__ . '\vendor\autoload.php';
					$mpdf = new \Mpdf\Mpdf(array(
						'mode' => '',
						'format' => 'A4',
						'default_font_size' => 8,
						'default_font' => 'tahoma',
						'margin_left' => 10,
						'margin_right' => 10,
						'margin_top' => 40,
						'margin_bottom' => 10,
						'margin_header' => 10,
						'margin_footer' => 0,
						'orientation' => 'L',
					));

					$content = null;
					$header = '';
					$title = '';
					$tgl1 = date('d-M-Y',strtotime($periode_start));
					$tgl2 = date('d-M-Y',strtotime($periode_end));
					$printDate = date('d/M/Y H:i:s');
					if($kat_brg=='') $kat_brg = 'PRODUCT & SPAREPART';


					if($tipe_laporan==1){
						//
						if($getReport!=null && $getReport['code']==1){
							foreach($getReport['data'] as $value){
								$reportTmp[$value['DIVISI']][] = $value;
							}
							$data['reportTmp'] = $reportTmp;
						}
		
						$title = 'LAPORAN JUAL-RETUR PER DIVISI PER WILAYAH';
						$content = $this->load->view('template_pdf/LaporanJualReturPerDivisiPerWilayahPdf',$data,true);
					}
					else if($tipe_laporan==2){
						//
						if($getReport!=null && $getReport['code']==1){
							foreach($getReport['data'] as $value){
								$reportTmp[$value['WILAYAH']][] = $value;
							}
							$data['reportTmp'] = $reportTmp;
						}
						$title = 'LAPORAN JUAL-RETUR PER WILAYAH PER DIVISI';
						$content = $this->load->view('template_pdf/LaporanJualReturPerWilayahPerDivisiPdf',$data,true);
					}
					

					$header = <<<HTML
						<p style="margin:0 0;text-align:left;">{$printDate}</p>
						<p style="margin:0 0;text-align:center;">{$title}</p>
						<p style="margin:0 0;text-align:center;">{$tgl1} S/D {$tgl2}</p>
						<p style="margin:10 0mm;margin-top:0px;width:100%;text-align:center;">{$kat_brg}</p>
HTML;
					$mpdf->SetHTMLHeader($header,'','1'); //Yang diulang di setiap awal halaman  (Header)
					$mpdf->WriteHTML($content);
					$mpdf->Output();
				break;
			}
		}
	}
}