<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require 'vendor/autoload.php';
require 'vendor/setasign/fpdi/src/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Reportpurchasingbook extends MY_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('HelperModel');
		$this->load->model('GzipDecodeModel');
		$this->load->helper('FormLibrary');
		$this->load->library('email');
		$this->load->library('excel');
		$this->load->helper('directory');
		$this->load->helper('file');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
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
		// curl_setopt($ch, CURLOPT_POSTFIELDS,
		//             "postvar1=value1&postvar2=value2&postvar3=value3");

		// In real life you should use something like:
		curl_setopt($ch, CURLOPT_POSTFIELDS, 
		         http_build_query($data));

		$headers = array(
		    'Content-type: application/x-www-form-urlencoded',
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		// Receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec($ch);

		curl_close($ch);
		// $server_output = json_decode($server_output,true);
		$server_output = $this->GzipDecodeModel->_decodeGzip_true($server_output);

		return $server_output;
	}
	public function index(){
		$submit = $this->input->post('submit');
		if($submit==''){
			
			//supplier
			$url = $this->API_URL."/MsSupplier/GetSupplierList?api=APITES";
			$supplier = json_decode(file_get_contents($url),true);

			//cabang
			$url = $this->API_URL."/Cabang/GetMstCabang?api=APITES";
			$cabang = json_decode(file_get_contents($url),true);
			if($cabang!='' && $cabang['data']!=null)
				$cabang = $cabang['data'];
			else
				$cabang = array();

			//gudang
			$url = $this->API_URL."/MsGudang/RekapMsGudang";
			$payload = array(
				'api' => 'APITES',
				'location' => '',
				'jenis' => '',
				'status' => 1,
			);
			$gudang = $this->_postRequest($url,$payload);
			



			// die(json_encode($GetData["data"]));
			$data = array(
				'title' => 'LAPORAN PEMBELIAN BERDASARKAN FAKTUR PAJAK | '.WEBTITLE,
				'err' => '',
				'formDest' => '',
				'supplier' => $supplier,
				'cabang' => $cabang,
				'gudang' => $gudang,
			);
			$this->RenderView('ReportpurchasingbookView',$data);
		}
		else{
			$tipeSupplier = (string)$this->input->post('tipe_supplier');
			$katBrg = (string)$this->input->post('kat_brg');
			$tglBpbStart = (string)$this->input->post('tgl_bpb_start');
			$tglBpbEnd = (string)$this->input->post('tgl_bpb_end');
			$tipePeriode = (string)$this->input->post('tipe_periode');
			$periodeStart = (string)$this->input->post('periode_start');
			$periodeEnd = (string)$this->input->post('periode_end');
			$kdSupplier = (string)$this->input->post('kd_supplier');
			$kdCabang = (string)$this->input->post('kd_cabang');
			$kdGudang = (string)$this->input->post('kd_gudang');
			$tipeLaporan = (string)$this->input->post('tipe_laporan');

			if($kdSupplier=='ALL') $kdSupplier = '';
			if($kdCabang=='ALL') $kdCabang = '';
			if($kdGudang=='ALL') $kdGudang = '';

			if($tglBpbStart!=''){
				$date=date_create_from_format("d-M-Y",$tglBpbStart);
				if($date->format("d-M-Y") == $tglBpbStart){
					$tglBpbStart = $date->format("Y-m-d");
				}
				else{
					$tglBpbStart = '';
				}
			}
			if($tglBpbEnd!=''){
				$date=date_create_from_format("d-M-Y",$tglBpbEnd);
				if($date->format("d-M-Y") == $tglBpbEnd){
					$tglBpbEnd = $date->format("Y-m-d");
				}
				else{
					$tglBpbEnd = '';
				}
			}

			if($periodeStart!=''){
				$date=date_create_from_format("d-M-Y",$periodeStart);
				if($date->format("d-M-Y") == $periodeStart){
					$periodeStart = $date->format("Y-m-d");
				}
				else{
					$periodeStart = '';
				}
			}
			if($periodeEnd!=''){
				$date=date_create_from_format("d-M-Y",$periodeEnd);
				if($date->format("d-M-Y") == $periodeEnd){
					$periodeEnd = $date->format("Y-m-d");
				}
				else{
					$periodeEnd = '';
				}
			}
			

			$url = $this->API_URL."/Reportpurchasingbook/GetPembelianLocalImport?api=APITES";

			$payload = array(
				'tipe_supplier' => $tipeSupplier,
				'kat_brg' => $katBrg,
				'tgl_bpb_start' => $tglBpbStart,
				'tgl_bpb_end' => $tglBpbEnd,
				'tipe_periode' => $tipePeriode,
				'periode_start' => $periodeStart,
				'periode_end' => $periodeEnd,
				'kd_supplier' => $kdSupplier,
				'kd_cabang' => $kdCabang,
				'kd_gudang' => $kdGudang,
				'tipe_laporan' => $tipeLaporan,
			);
			$rekapTmp = $this->_postRequest($url,$payload);
			switch($submit){
				case 'EXCEL':
					
				break;
				case 'PDF':
					if($tipeLaporan=='SUMMARY'){
						$this->_pdfSummary($rekapTmp,$payload);
					}
					else if($tipeLaporan=='GABUNGAN'){
						$this->_pdfGabungan($rekapTmp,$payload);
					}
					else if($tipeLaporan=='PER_SUPPLIER'){
						$this->_pdfPerSupplier($rekapTmp,$payload);
					}
					else if($tipeLaporan=='PER_CABANG'){
						
						$this->_pdfPerCabang($rekapTmp,$payload);
					}
					else if($tipeLaporan=='BELUM_EDIT_PAJAK'){
						$this->_pdfBelumEditPajak($rekapTmp,$payload);
					}
				break;
				default:
					echo 'submit tidak dikenal';
				break;
			}
			
		}
		
	}
	private function _pdfBelumEditPajak($rekapTmp,$payload){
		require_once __DIR__ . '\vendor\autoload.php';
		$mpdf = new \Mpdf\Mpdf(array(
			//'mode' => '',
			'format' => 'A4',
			//'default_font_size' => 8,
			'default_font' => 'tahoma',
			'margin_left' => 10,
			'margin_right' => 10,
			'margin_top' => 30,
			'margin_bottom' => 10,
			'margin_header' => 10,
			'margin_footer' => 0,
			'orientation' => 'P'
		));
		$data['rekapTmp'] = array();
		$rekap = array();
		$ketPeriodePbb = '';//"Semua Periode BPB";
		$ketTglFakturPajak = "";
		if($payload['tgl_bpb_start']!='' && $payload['tgl_bpb_end']!=''){
			$ketPeriodePbb = date('d M Y',strtotime($payload['tgl_bpb_start'])).' S/D '.date('d M Y',strtotime($payload['tgl_bpb_end']));
		}
		if($payload['periode_start']!='' && $payload['periode_end']!=''){
			$ketTglFakturPajak = date('d M Y',strtotime($payload['periode_start'])).' S/D '.date('d M Y',strtotime($payload['periode_end']));
		}

		if($rekapTmp!=null && $rekapTmp['code']==1){


			foreach($rekapTmp['data'] as $value){
				$rekap['rekapTmp'][$value['Nm_Gudang']][] = $value;
			}
			$length = count($rekap['rekapTmp']);
			$iterasi = 0;

			foreach($rekap['rekapTmp'] as $key => $value){
				$data['gudang'] = $key;
				$data['rekapTmp'] = $value;
				$content = $this->load->view('template_pdf/Reportpurchasingbook_BelumEditPajakPdf',$data,true);
				$dateNow = date('d/M/Y H:i:s');
				$header = <<<HTML
					<p style="margin:0px;text-align:left;">{$dateNow}</p>
					<p style="font-weight: bold;margin:0px;text-align:center;">PEMBELIAN BELUM EDIT PAJAK</p>
					<!--<p style="font-weight: bold;margin:0px;text-align:center;">{$ketPeriodePbb}</p>-->
					<p style="font-weight: bold;margin:0px;text-align:center;">{$ketTglFakturPajak}</p>
HTML;
				
				$mpdf->SetHTMLHeader($header,'','1'); //Yang diulang di setiap awal halaman  (Header)
				$mpdf->WriteHTML($content);
				if($iterasi<$length-1){
					$mpdf->AddPage();
				}
				$iterasi +=1;

			}
			
			//$mpdf->simpleTables = true;
			$mpdf->packTableData = true;
			$mpdf->keep_table_proportions = TRUE;
			$mpdf->shrink_tables_to_fit=1;
			$mpdf->shrink_tables_to_fit = 1;
			
			$mpdf->Output();
			
		}
	}
	private function _pdfPerCabang($rekapTmp,$payload){
		require_once __DIR__ . '\vendor\autoload.php';
		$mpdf = new \Mpdf\Mpdf(array(
			//'mode' => '',
			'format' => 'A4',
			//'default_font_size' => 8,
			'default_font' => 'tahoma',
			'margin_left' => 10,
			'margin_right' => 10,
			'margin_top' => 30,
			'margin_bottom' => 10,
			'margin_header' => 10,
			'margin_footer' => 0,
			'orientation' => 'L'
		));
		$data['rekapTmp'] = array();
		$rekap = array();
		$ketPeriodePbb = "Semua Periode BPB";
		$ketTglFakturPajak = "";
		if($payload['tgl_bpb_start']!='' && $payload['tgl_bpb_end']!=''){
			$ketPeriodePbb = date('d M Y',strtotime($payload['tgl_bpb_start'])).' S/D '.date('d M Y',strtotime($payload['tgl_bpb_end']));
		}
		if($payload['periode_start']!='' && $payload['periode_end']!=''){
			$ketTglFakturPajak = 'PERIODE : Tgl Faktur Pajak '.date('d M Y',strtotime($payload['periode_start'])).' S/D '.date('d M Y',strtotime($payload['periode_end']));
		}
		
		if($rekapTmp!=null && $rekapTmp['code']==1){


			foreach($rekapTmp['data'] as $value){
				$data['rekapTmp'][$value['Kategori_Brg']][$value['Kd_Lokasi2']][$value['Nm_Supl']][] = $value;
			}
			// echo '<pre>';
			// print_r($data);
			// echo '</pre>';
			$content = $this->load->view('template_pdf/Reportpurchasingbook_PerCabangPdf',$data,true);
			$dateNow = date('d/M/Y H:i:s');
			$header = <<<HTML
				<p style="margin:0px;text-align:left;">{$dateNow}</p>
				<p style="font-weight: bold;margin:0px;text-align:center;">REKAP BUKU PEMBELIAN Per CABANG</p>
				<p style="font-weight: bold;margin:0px;text-align:center;">{$ketPeriodePbb}</p>
				<p style="font-weight: bold;margin:0px;text-align:center;">{$ketTglFakturPajak}</p>
HTML;
			//$mpdf->simpleTables = true;
			$mpdf->packTableData = true;
			$mpdf->keep_table_proportions = TRUE;
			$mpdf->shrink_tables_to_fit=1;
			$mpdf->shrink_tables_to_fit = 1;
			$mpdf->SetHTMLHeader($header,'','1'); //Yang diulang di setiap awal halaman  (Header)
			$mpdf->WriteHTML($content);
			$mpdf->Output();
			
		}
	}
	private function _pdfPerSupplier($rekapTmp,$payload){
		require_once __DIR__ . '\vendor\autoload.php';
		$mpdf = new \Mpdf\Mpdf(array(
			//'mode' => '',
			'format' => 'A4',
			//'default_font_size' => 8,
			'default_font' => 'tahoma',
			'margin_left' => 10,
			'margin_right' => 10,
			'margin_top' => 30,
			'margin_bottom' => 10,
			'margin_header' => 10,
			'margin_footer' => 0,
			'orientation' => 'L'
		));
		$data['rekapTmp'] = array();
		$rekap = array();
		$ketPeriodePbb = "Semua Periode BPB";
		$ketTglFakturPajak = "";
		if($payload['tgl_bpb_start']!='' && $payload['tgl_bpb_end']!=''){
			$ketPeriodePbb = date('d M Y',strtotime($payload['tgl_bpb_start'])).' S/D '.date('d M Y',strtotime($payload['tgl_bpb_end']));
		}
		if($payload['periode_start']!='' && $payload['periode_end']!=''){
			$ketTglFakturPajak = 'PERIODE : Tgl Faktur Pajak '.date('d M Y',strtotime($payload['periode_start'])).' S/D '.date('d M Y',strtotime($payload['periode_end']));
		}
		
		if($rekapTmp!=null && $rekapTmp['code']==1){
			foreach($rekapTmp['data'] as $value){
				$data['rekapTmp'][$value['Kategori_Brg']][$value['Nm_Supl']][] = $value;
			}
			$dateNow = date('d/M/Y H:i:s');
			$content = $this->load->view('template_pdf/Reportpurchasingbook_PerSupplierPdf',$data,true);
			$header = <<<HTML
				<p style="margin:0px;text-align:left;">{$dateNow}</p>
				<p style="font-weight: bold;margin:0px;text-align:center;">REKAP BUKU PEMBELIAN Per SUPPLIER</p>
				<p style="font-weight: bold;margin:0px;text-align:center;">{$ketPeriodePbb}</p>
				<p style="font-weight: bold;margin:0px;text-align:center;">{$ketTglFakturPajak}</p>
HTML;
			//$mpdf->simpleTables = true;
			$mpdf->packTableData = true;
			$mpdf->keep_table_proportions = TRUE;
			$mpdf->shrink_tables_to_fit=1;
			$mpdf->shrink_tables_to_fit = 1;
			$mpdf->SetHTMLHeader($header,'','1'); //Yang diulang di setiap awal halaman  (Header)
			$mpdf->WriteHTML($content);
			
			
		}
		else{

			$dateNow = date('d/M/Y H:i:s');
			$content = $this->load->view('template_pdf/Reportpurchasingbook_PerSupplierPdf',$data,true);
			$header = <<<HTML
				<p style="margin:0px;text-align:left;">{$dateNow}</p>
				<p style="font-weight: bold;margin:0px;text-align:center;">REKAP BUKU PEMBELIAN Per SUPPLIER</p>
				<p style="font-weight: bold;margin:0px;text-align:center;">{$ketPeriodePbb}</p>
				<p style="font-weight: bold;margin:0px;text-align:center;">{$ketTglFakturPajak}</p>
HTML;
			$mpdf->SetHTMLHeader($header,'','1'); //Yang diulang di setiap awal halaman  (Header)
			$mpdf->WriteHTML($content);
		}
		$mpdf->Output();
	}
	private function _pdfGabungan($rekapTmp,$payload){
		require_once __DIR__ . '\vendor\autoload.php';
		$mpdf = new \Mpdf\Mpdf(array(
			'mode' => '',
			'format' => 'A4',
			'default_font_size' => 8,
			'default_font' => 'tahoma',
			'margin_left' => 10,
			'margin_right' => 10,
			'margin_top' => 30,
			'margin_bottom' => 10,
			'margin_header' => 10,
			'margin_footer' => 0,
			'orientation' => 'L'
		));
		$data['rekap'] = array();
		$rekap = array();
		$ketPeriodePbb = "Semua Periode BPB";
		$ketTglFakturPajak = "";
		if($payload['tgl_bpb_start']!='' && $payload['tgl_bpb_end']!=''){
			$ketPeriodePbb = date('d M Y',strtotime($payload['tgl_bpb_start'])).' S/D '.date('d M Y',strtotime($payload['tgl_bpb_end']));
		}
		if($payload['periode_start']!='' && $payload['periode_end']!=''){
			$ketTglFakturPajak = date('d M Y',strtotime($payload['periode_start'])).' S/D '.date('d M Y',strtotime($payload['periode_end']));
		}
		if($rekapTmp!=null && $rekapTmp['code']==1){
			
			foreach($rekapTmp['data'] as $value){
				$data['rekap'][] = $value;
			}

		}
		$dateNow = date('d/M/Y H:i:s');
		$length = count($rekap);
		$content = $this->load->view('template_pdf/Reportpurchasingbook_GabunganPdf',$data,true);
		$header = <<<HTML
			<p style="margin:0px;text-align:left;">{$dateNow}</p>
			<p style="font-weight: bold;margin:0px;text-align:center;">REKAP BUKU PEMBELIAN ALL SUPPLIER</p>
			<p style="font-weight: bold;margin:0px;text-align:center;">{$ketPeriodePbb}</p>
			<p style="font-weight: bold;margin:0px;text-align:center;">{$ketTglFakturPajak}</p>
HTML;

		$mpdf->SetHTMLHeader($header,'','1'); //Yang diulang di setiap awal halaman  (Header)
		$mpdf->WriteHTML($content);
		$mpdf->Output();
	}
	private function _pdfSummary($rekapTmp,$payload){
		require_once __DIR__ . '\vendor\autoload.php';
		$mpdf = new \Mpdf\Mpdf(array(
			'mode' => '',
			'format' => 'A4',
			'default_font_size' => 8,
			'default_font' => 'tahoma',
			'margin_left' => 10,
			'margin_right' => 10,
			'margin_top' => 30,
			'margin_bottom' => 10,
			'margin_header' => 10,
			'margin_footer' => 0,
			'orientation' => 'L'
		));
		$data['rekap'] = array();
		$rekap = array();
		$ketPeriodePbb = "Semua Periode BPB";
		$ketTglFakturPajak = "";
		if($payload['tgl_bpb_start']!='' && $payload['tgl_bpb_end']!=''){
			$ketPeriodePbb = date('d M Y',strtotime($payload['tgl_bpb_start'])).' S/D '.date('d M Y',strtotime($payload['tgl_bpb_end']));
		}
		if($payload['periode_start']!='' && $payload['periode_end']!=''){
			$ketTglFakturPajak = date('d M Y',strtotime($payload['periode_start'])).' S/D '.date('d M Y',strtotime($payload['periode_end']));
		}
		if($rekapTmp!=null && $rekapTmp['code']==1){
			
			foreach($rekapTmp['data'] as $value){
				$data['rekap'][] = $value;
			}
			$length = count($rekap);
		}
		$dateNow = date('d/M/Y H:i:s');
		$content = $this->load->view('template_pdf/Reportpurchasingbook_SummaryPdf',$data,true);
		$header = <<<HTML
			<p style="margin:0px;text-align:left;">{$dateNow}</p>
			<p style="font-weight: bold;margin:0px;text-align:center;">REKAP BUKU PEMBELIAN</p>
			<p style="font-weight: bold;margin:0px;text-align:center;">{$ketPeriodePbb}</p>
			<p style="font-weight: bold;margin:0px;text-align:center;">{$ketTglFakturPajak}</p>
HTML;

		$mpdf->SetHTMLHeader($header,'','1'); //Yang diulang di setiap awal halaman  (Header)
		$mpdf->WriteHTML($content);
		$mpdf->Output();
	}
}
