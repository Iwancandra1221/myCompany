<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require FCPATH.'application/controllers/vendor/autoload.php';
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;



class ReportKasir extends MY_Controller {

	public function __construct(){

		parent::__construct();
		$this->load->model('HelperModel');
		$this->load->helper('FormLibrary');
		$this->load->library('excel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		$this->bktAPI = $this->ConfigSysModel->Get()->bktapi_appname;
		$this->title = $this->ConfigSysModel->Get()->company_name;
		ini_set("memory_limit", "1G");
		ini_set("max_execution_time", 300);
	}


	private function _postRequest($url,$data){

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
		curl_setopt($ch, CURLOPT_ENCODING, '');
		//curl_setopt($ch, CURLOPT_POSTFIELDS,"postvar1=value1&postvar2=value2&postvar3=value3");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec($ch);
		$result = json_decode($server_output,true);
		if($result==null){
			// $GLOBALS['bugsnag']->leaveBreadcrumb(
			//     $server_output,
			//     \Bugsnag\Breadcrumbs\Breadcrumb::ERROR_TYPE,
			//     [
			//     	'url' => $url,
			//     	'payload' => $data,
			// 	]
			// );
			// $GLOBALS['bugsnag']->notifyError('ErrorType', 'ReportKasir result kosong - CEK TAB BREADCUMS');
			
		}

		return $result;
	}

	public function index(){
	}

	public function LaporanBBKnBPKK(){
		
		$api = 'APITES';		
		$submit = $this->input->post('submit');

		if($submit==''){
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN BBK & BPKK";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN BBK & BPKK ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);

			// $getWilayahList = $this->_postRequest($this->API_URL.'/MsWilayah/GetListWilayahKirim?api='.$api,array());
			// if($getWilayahList!=null && count($getWilayahList['data'])>0){
			// 	$getWilayahList = $getWilayahList['data'];
			// }
			$getSupplierList = $this->_postRequest($this->API_URL.'/MsSupplier/GetSupplierList?api='.$api,array());
			$getRekeningList = $this->_postRequest($this->API_URL.'/MsRekening/GetRekeningList?api='.$api,array());
			$getNoBuktiBankListBpkk = $this->_postRequest($this->API_URL.'/Cabang/GetNoBuktiBankList?api='.$api.'&type_trans=BPKK',array());
			$getNoBuktiBankListBkk = $this->_postRequest($this->API_URL.'/Cabang/GetNoBuktiBankList?api='.$api.'&type_trans=BKK',array());
			//
			// echo '<pre>';
			// print_r($getSupplierList);
			// echo '</pre>';

			$body = array(
				'title' => 'Laporan BBK & BKK | '.WEBTITLE,
				'formUrl' => base_url()."ReportKasir/LaporanBBKnBPKK",
				'getBbk_Bpkk' => $getNoBuktiBankListBpkk,
				'getBbk_Bkk' => $getNoBuktiBankListBkk,
				'supplier' => $getSupplierList,
				'rekening' => $getRekeningList,
				//'wilayah' => $getWilayahList,
			);

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('LaporanBBKnBPKKView',$body);
		}
		else{
			
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN BBK & BPKK";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES LAPORAN BBK & BPKK ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$mainUrl = $_SESSION["conn"]->AlamatWebService;
			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;
			//tipeReport
				//bbk
				//bpkk

			//opsi
				//1 = Gabungan
				//2 = Grup No Rekening
				//3 = No Rekening

			$noBbk = '';
			$tipeReport = $this->input->post('tipe_report');
			$tgl1 = $this->input->post('dp1');
			$tgl2 = $this->input->post('dp2');
			$noBbk_bpkk = $this->input->post('no_bbk_bpkk');
			$noBbk_bkk = $this->input->post('no_bbk_bkk');
			$kdSupplier = $this->input->post('kd_supplier');
			$opsi = $this->input->post('opsi');
			$noRekening = $this->input->post('no_rekening');
			$kdWilayah = $this->input->post('kd_wilayah');

			$date=date_create_from_format("m/d/Y",$tgl1);
			if($date!=null) $date = date_format($date,"Y-m-d");
			$tgl1 = $date;

			$date=date_create_from_format("m/d/Y",$tgl2);
			if($date!=null) $date = date_format($date,"Y-m-d");
			$tgl2 = $date;

			if($tipeReport=='bbk'){
				//$payload[''] = '';
			}
			else if($tipeReport=='bpkk'){
				$noBbk = $noBbk_bpkk;
			}
			else if($tipeReport=='bkk'){
				$noBbk = $noBbk_bkk;
			}

			$mainUrl.=$this->bktAPI.'/ReportKasir/LaporanBBKnBPKK';
			$payload = array(
				'api' => 'APITES',
				'svr' => $svr,
				'db' => $db,
				'tipe_report' => $tipeReport,
				'tgl1' => $tgl1,
				'tgl2' => $tgl2,
				'kd_supplier' => $kdSupplier,
				'no_bbk' => $noBbk,
				'opsi' => $opsi,
				'no_rekening' => $noRekening,
				'kd_wilayah' => $kdWilayah,
				// 'submit' => '1',
			);


			
			//$mainUrl = "http://localhost:90/bktAPI/ReportKasir/LaporanBBKnBPKK";
			$result = $this->_postRequest($mainUrl,$payload);
			// echo '<pre>';
			// print_r($result);
			// echo '</pre>';
			// echo '<pre>';
			// print_r($payload);
			// echo '</pre>';

			if($tgl1!='') $tgl1 = date('d-M-Y',strtotime($tgl1));
			if($tgl2!='') $tgl2 = date('d-M-Y',strtotime($tgl2));

			$title = strtoupper('Laporan Buku Harian '.$tipeReport);
			$printDate = date('M/d/Y H:i:s');
			$body = array(
				'laporan' => $result,
				'title' => $title,
				'tgl1' => $tgl1,
				'tgl2' => $tgl2, 
				'printDate' => $printDate,
			);
			// echo '<pre>';
			// print_r($body);
			// echo '</pre>';
			// die();
			switch($submit){
				case 'EXPORT EXCEL':
					if($opsi==1){
						// ActivityLog Update SUCCESS
						$params['Remarks']="SUCCESS";
						$params['RemarksDate'] = date("Y-m-d H:i:s");
						$this->ActivityLogModel->update_activity($params);
						if($tipeReport=='bbk'){
							$this->load->view('template_xls/LaporanBBKnBPKK_bbk_GabunganXls',$body);
						}
						else{
							$this->load->view('template_xls/LaporanBBKnBPKK_GabunganXls',$body);
						}
						
					}
					else if($opsi==2 || $opsi==3){
						// ActivityLog Update SUCCESS
						$params['Remarks']="SUCCESS";
						$params['RemarksDate'] = date("Y-m-d H:i:s");
						$this->ActivityLogModel->update_activity($params);
						if($tipeReport=='bbk'){
							$this->load->view('template_xls/LaporanBBKnBPKK_bbk_GrupNoRekeningXls',$body);
						}
						else{
							$this->load->view('template_xls/LaporanBBKnBPKK_GrupNoRekeningXls',$body);
						}
						
					}

					break;

				case 'EXPORT PDF':
					require_once APPPATH . 'libraries/tcpdf/config/tcpdf_config.php';
					require_once APPPATH . 'libraries/tcpdf/tcpdf.php';
					$tcpdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
					$tcpdf->setPrintHeader(false);
					$tcpdf->AddPage();

					require_once __DIR__ . '\vendor\autoload.php';
					ini_set('pcre.backtrack_limit', 10000000);
					$mpdf = new \Mpdf\Mpdf(array(
						'mode' => '',
						'format' => 'A4',
						//'default_font_size' => 12,
						'default_font' => 'tahoma',
						'margin_left' => 10,
						'margin_right' => 10,
						'margin_top' => 40,
						'margin_bottom' => 10,
						'margin_header' => 10,
						'margin_footer' => 0,
						'orientation' => 'P'
					));



					$content = '';
					
					$header = <<<HTML
						<p style="font-size:9px;">{$printDate}</p>
						<p style="text-align:center;">{$title}</p>
						<p class="" style="text-align:center;">Periode : {$tgl1} s/d {$tgl2}</p>
HTML;
					if($opsi==1){
						if($tipeReport=='bbk'){
							$content = $this->load->view('template_pdf/LaporanBBKnBPKK_bbk_GabunganPdf',$body,true);
						}
						else{
							$content = $this->load->view('template_pdf/LaporanBBKnBPKK_GabunganPdf',$body,true);
						}
						
					}
					else if($opsi==2 || $opsi==3){
						if($tipeReport=='bbk'){
							$content = $this->load->view('template_pdf/LaporanBBKnBPKK_bbk_GrupNoRekeningPdf',$body,true);
						}
						else{
							$content = $this->load->view('template_pdf/LaporanBBKnBPKK_GrupNoRekeningPdf',$body,true);
						}
						
				
					}

					if( ($opsi==1 || $opsi==2 ) && $tipeReport=='bbk'){
						$tcpdf->writeHTML($content, true, false, true, false, '');
						$tcpdf->Output($title.'.pdf', 'I');//D download //I Inline => tanpil dibrowser
					}
					else{
						$mpdf->shrink_tables_to_fit = 1;
						$mpdf->SetHTMLHeader($header,'','1'); //Yang diulang di setiap awal halaman  (Header)
						$mpdf->WriteHTML($content);
						$mpdf->Output();
					}
					
					// $mpdf->shrink_tables_to_fit = 1;
					// $mpdf->SetHTMLHeader($header,'','1'); //Yang diulang di setiap awal halaman  (Header)
					// $mpdf->WriteHTML($content);
					// $mpdf->Output();
					
					
				 	

					// ActivityLog Update SUCCESS
					$params['Remarks']="SUCCESS";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

				break;
			}
		}
	}

	public function LaporanTransaksiBank(){
		$api = 'APITES';
		$submit = $this->input->post('submit');
		$error = '';
		
		if($submit==''){
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN TRANSAKSI BANK";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN TRANSAKSI BANK ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$getRekeningList = $this->_postRequest($this->API_URL.'/MsRekening/GetRekeningList?api='.$api,array());
			// echo json_encode($getRekeningList); die;
			$body = array(
				'title' => 'Laporan Transaksi Bank | '.WEBTITLE,
				'formUrl' => base_url()."ReportKasir/LaporanTransaksiBank",
				'rekening' => $getRekeningList,
			);

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('LaporanTransaksiBankView',$body);
		}
		else{
			// echo json_encode($_POST);die;
			
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN TRANSAKSI BANK";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES LAPORAN TRANSAKSI BANK ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$url = $_SESSION["conn"]->AlamatWebService;
			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;
			
			// $svr = '10.17.32.3';
			
			$TglAwal = $this->input->post('dp1');
			$TglAkhir = $this->input->post('dp2');
			$x = explode(' | ',$this->input->post('no_rekening'));
			$NoRekening = $x[0];
			$TglSaldoAwal = $x[1];
			$Bank = $x[2];
			$Cabang = $x[3];
			$Nm_Pemilik = $x[4];
			
			$data = [
				"api" => "APITES",
				"svr"=> $svr,
				"db" => $db,
				"uid" => SQL_UID,
				"pwd" => SQL_PWD,
				"filter" => array(
					"TglAwal" => date('Ymd',strtotime($TglAwal)),
					"TglAkhir" => date('Ymd',strtotime($TglAkhir)),
					"NoRekening" => $NoRekening,
					"TglSaldoAwal" => $TglSaldoAwal,
				)
			];
			// die(json_encode($data));
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url.$this->bktAPI."/ReportKasir/LaporanTransaksiBank",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 90,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => json_encode($data),
    			CURLOPT_ENCODING => '',
				CURLOPT_IGNORE_CONTENT_LENGTH => 1,
				CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			
			// die($response);
			
			if ($response===false) {
				$lanjut = false;
				$err = "API Tujuan OFFLINE";
			} else {
				$res = json_decode($response);
				if ($res->result=="sukses") {
					$lanjut=true;
				} else {
					$lanjut=false;
					$error=$res->error;
				}
			}
			
			if($lanjut==true){
				$body = array(
					'Bank' => $Bank,
					'Cabang' => $Cabang,
					'NoRekening' => $NoRekening,
					'Nm_Pemilik' => $Nm_Pemilik,
					'res' => $res,
					'TglAwal' => $TglAwal,
					'TglAkhir' => $TglAkhir,
				);
				switch($submit){
					case 'EXPORT EXCEL':
						// ActivityLog Update SUCCESS
						$params['Remarks']="SUCCESS";
						$params['RemarksDate'] = date("Y-m-d H:i:s");
						$this->ActivityLogModel->update_activity($params);

						$this->load->view('template_xls/LaporanTransaksiBankXls',$body);
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
							'margin_top' => 39,
							'margin_bottom' => 16,
							'margin_header' => 10,
							'margin_footer' => 10,
							'orientation' => 'P'
						));

						$content = $this->load->view('template_pdf/LaporanTransaksiBankPdf',$body,true);

						$mpdf->defaultfooterline = 1;
						$mpdf->SetFooter('
						<table width="100%">
							<tr>
								<td class="right">
									Halaman {PAGENO} / {nbpg}
								</td>
							</tr>
						</table>');
						$mpdf->keepColumns = true;
						$mpdf->WriteHTML($content);
						$mpdf->Output();

						// ActivityLog Update SUCCESS
						$params['Remarks']="SUCCESS";
						$params['RemarksDate'] = date("Y-m-d H:i:s");
						$this->ActivityLogModel->update_activity($params);

					break;
				}
				

			}
			else{
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				
				die($error);
			}
				
			
		}
	}


}