<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class Reportomzetnettosummary extends MY_Controller 
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
		$submit = $this->input->post('submit');
		if($submit==''){
			// die("1");

			$parentDivisi = $this->_postRequest($this->API_URL.'/MsDivisi/GetListParentDiv?api=APITES',array());
			$parentDivisi = $parentDivisi['data'];

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
				'title' => 'OMZET | NETTO SUMMARY',
				'formUrl' => '',
				'parentDivisi' => $parentDivisi,
				'wilayah' => $wilayah,
				'partnerType' => $partnerType,
				'tipeFaktur' => $tipeFaktur,
			);

			$this->RenderView('LaporanOmzetNettoSummaryView',$data);
		}
		else{
			// die("2");
			$msg = "";

			$periode_start = $this->input->post('periode_start');
			$periode_end = $this->input->post('periode_end');
			$parent_divisi = trim($this->input->post('parent_divisi'));
			$kd_wil = $this->input->post('kd_wil');
			$kat_brg = $this->input->post('kat_brg');
			$partner_type = $this->input->post('partner_type');
			$tipe_faktur = $this->input->post('tipe_faktur');
			$tipe_laporan = $this->input->post('tipe_laporan');

			// echo '<pre>';
			// print_r($_POST);
			// echo '</pre>';

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
			// $url = "http://localhost/";
			$url = $url.API_BKT.'/Reportomzetnettosummary/GetReport?svr='.$svr.'&db='.$db;
			// die($url);
			$payload = array(
				'periode_start' => $periode_start,
				'periode_end' => $periode_end,
				'parent_divisi' => $parent_divisi,
				'kd_wil' => $kd_wil,
				'kat_brg' => $kat_brg,
				'partner_type' => $partner_type,
				'tipe_faktur' => $tipe_faktur,
				'tipe_laporan' => $tipe_laporan,
			);
			// die(json_encode($payload));
			// echo '<pre>';
			// print_r($payload);
			// echo '</pre>';

			$getReport = $this->_postRequest($url,$payload);
			die(json_encode($getReport));
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
								$reportTmp[$value['Partner_Type']][$value['ParentDiv']][] = $value;
							}
							$data['reportTmp'] = $reportTmp;
						}
						$data['title'] = 'LAPORAN_OMZET_NETTO_PER_PARENTDIV_PER_WILAYAH';
						$this->load->view('template_xls/LaporanOmzetNettoPerParentDivPerWilayahXls',$data);
					}
					else if($tipe_laporan==2){
						//
						if($getReport!=null && $getReport['code']==1){
							foreach($getReport['data'] as $value){
								$reportTmp[$value['Partner_Type']][$value['Wilayah']][] = $value;
							}
							$data['reportTmp'] = $reportTmp;
						}
						$data['title'] =  'LAPORAN_OMZET_NETTO_PER_WILAYAH_PER_PARENTDIV';
						$this->load->view('template_xls/LaporanOmzetNettoPerWilayahPerParentDivXls',$data);
					}
					else if($tipe_laporan==3){
						//
						if($getReport!=null && $getReport['code']==1){
							foreach($getReport['data'] as $value){
								$reportTmp[$value['Partner_Type']][$value['Wilayah']][] = $value;
							}
							$data['reportTmp'] = $reportTmp;
						}
						$data['title'] = 'LAPORAN_OMZET_NETTO_DEALER_SUMMARY';
						$this->load->view('template_xls/LaporanOmzetNettoDealerSummaryXls',$data);
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
								$reportTmp[$value['Partner_Type']][$value['ParentDiv']][] = $value;
							}
							$data['reportTmp'] = $reportTmp;
						}
						//$this->load->view('template_pdf/ReportOmzetNettoSummaryPdf',$data);
						$title = 'LAPORAN OMZET NETTO PER PARENTDIV PER WILAYAH';
						$content = $this->load->view('template_pdf/LaporanOmzetNettoPerParentDivPerWilayahPdf',$data,true);
					}
					else if($tipe_laporan==2){
						//
						if($getReport!=null && $getReport['code']==1){
							foreach($getReport['data'] as $value){
								$reportTmp[$value['Partner_Type']][$value['Wilayah']][] = $value;
							}
							$data['reportTmp'] = $reportTmp;
						}
						//$this->load->view('template_pdf/ReportOmzetNettoSummaryPdf',$data);
						$title = 'LAPORAN OMZET NETTO PER WILAYAH PER PARENTDIV';
						$content = $this->load->view('template_pdf/LaporanOmzetNettoPerWilayahPerParentDivPdf',$data,true);
					}
					else if($tipe_laporan==3){
						//
						if($getReport!=null && $getReport['code']==1){
							foreach($getReport['data'] as $value){
								$reportTmp[$value['Partner_Type']][$value['Wilayah']][] = $value;
							}
							$data['reportTmp'] = $reportTmp;
						}
						//$this->load->view('template_pdf/ReportOmzetNettoSummaryPdf',$data);
						$title = 'LAPORAN OMZET NETTO DEALER SUMMARY';
						$content = $this->load->view('template_pdf/LaporanOmzetNettoDealerSummaryPdf',$data,true);
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
?>
