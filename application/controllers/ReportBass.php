<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class ReportBass extends MY_Controller 
	{
        public function __construct()
		{
			parent::__construct();
			$this->load->model('HelperModel');
			$this->load->model('GzipDecodeModel');
			$this->load->helper('FormLibrary');
			$this->load->library('excel');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
			ini_set("memory_limit", "1G");
		}



		//=======================================//=======================================

        public function ReportClaim()
		{
            $data = array();
			$api = 'APITES';
			
			set_time_limit(60);

			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "REPORT BASS";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU REPORT BASS";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);
			
            // print_r($this->API_URL."/ReportBass/BASS_SETUP?api=".$api);
		    // die;

            $BASS_SETUP = json_decode(file_get_contents($this->API_URL."/ReportBass/BASS_SETUP?api=".$api));
			$data["basssetup"] = $BASS_SETUP;

            $ServerWeb = $BASS_SETUP->data[0]->oConWeb; 
            $DtBaseWeb = $BASS_SETUP->data[0]->DtBaseWeb;
            $KodeBass = $BASS_SETUP->data[0]->Kode_Bass;
            $KodeCabang = $BASS_SETUP->data[0]->Kode_Cabang;
            $BassPusat = $BASS_SETUP->data[0]->Flag;

            // print_r($this->API_URL."/ReportBass/GetListCabang?api=".$api."&basspusat=".$BassPusat."&kodecabang=".$KodeCabang);
		    // die;

            $listcabang = json_decode(file_get_contents($this->API_URL."/ReportBass/GetListCabang?api=".$api."&basspusat=".$BassPusat."&kodecabang=".$KodeCabang));	
			$data["listcabang"] = $listcabang;

            // print_r($listcabang);
            // die;


			$data['title'] = 'Laporan Claim Bass | '.WEBTITLE;
			$data['laporan'] = "claim";


			// print_r($data);
			// die;
			
			$this->RenderView('ReportBassView',$data);

        }

		public function ReportClaimBass_Proses() {
			$page_title = 'Report Claim Bass';

			$api = 'APITES';

			set_time_limit(60);

            // print_r($_POST["dp2"]);
            // die;
						

			$tgl1 = date_format(date_create($_POST["dp1"]),'m-d-Y');
			$tgl2 = date_format(date_create($_POST["dp2"]),'m-d-Y');

			$tgl11 = date_format(date_create($_POST["dp1"]),'d-m-Y');
			$tgl22 = date_format(date_create($_POST["dp2"]),'d-m-Y');

			$cabang = $_POST["cabang"];
			$bass = $_POST["bass"];
			$status = $_POST["radstatus"];

			//print_r($tgl2);
            //die;


			if (empty($_POST["btnPreview"]) ){ 
				$proses="EXCEL";
			}
			else {
				$proses="PREVIEW";
			}



			// print_r($this->API_URL."/ReportBass/ReportClaimBass_ProsesLaporan?api=".$api

			// ."&page_title=".urlencode($page_title)															
			// ."&tgl1=".urlencode($tgl1)
			// ."&tgl2=".urlencode($tgl2)
			// ."&cabang=".urlencode($cabang)
			// ."&bass=".urlencode($bass)
			// ."&status=".urlencode($status));
			// die;

			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "REPORT BASS";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES REPORT BASS PERIODE ".date("d-M-Y", strtotime($_POST["dp1"]))." S/D ".date("d-M-Y", strtotime($_POST["dp2"]));
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);

			// print_r	($this->API_URL."/ReportBass/ReportClaimBass_ProsesLaporan?api=".$api
			// ."&page_title=".urlencode($page_title)															
			// ."&tgl1=".urlencode($tgl1)
			// ."&tgl2=".urlencode($tgl2)
			// ."&cabang=".urlencode($cabang)
			// ."&bass=".urlencode($bass)
			// ."&status=".urlencode($status));										
			// die;

            $datalaporan = json_decode(file_get_contents($this->API_URL."/ReportBass/ReportClaimBass_ProsesLaporan?api=".$api
															."&page_title=".urlencode($page_title)															
															."&tgl1=".urlencode($tgl1)
															."&tgl2=".urlencode($tgl2)
															."&cabang=".urlencode($cabang)
															."&bass=".urlencode($bass)
															."&status=".urlencode($status)
														));
			// print_r	($datalaporan);										
			// die;

			if ($datalaporan->result == "gagal") {
				echo ($datalaporan->result." - ".$datalaporan->error);
				
			} else {	
				$AturanPajak = json_decode(file_get_contents($this->API_URL."/ReportBass/AturanPajak?api=".$api));
				$kota = $AturanPajak->data[0]->KOTA; 
				$kepalasvc = $AturanPajak->data[0]->KEPALA_SERVICE; 

				$judul = "CLAIM SERVICE BASS";
				if ( $status =="noclaim" ) {
					$judul .= " Berdasarkan Nomor Claim";
				}
				else if ( $status =="nonota" ) {
					$judul .= " Berdasarkan Nomor Nota";
				}
				else {
					$judul .= " Berdasarkan Kode Barang";
				}

				$periode = "Periode " .$tgl11. " S/D " .$tgl22;


				// print_r($AturanPajak);
				// die;	

				if ($proses=="PREVIEW") {

					$this->ReportClaimBass_Preview ( $page_title, $datalaporan, $periode, $judul, $kota, $kepalasvc, $status, $params );				
				}
				else {
					$this->ReportClaimBass_Excel ( $page_title, $datalaporan, $periode, $judul, $kota, $kepalasvc, $status, $params );
				}			
			}
		}

		public function Pdf_Report($header="", $content="", $footer="", $tgl1="", $tgl2="", $user="", $lok="")
		{
			$data = array();
			set_time_limit(60);
			//require_once __DIR__ . '\vendor\autoload.php';
			//require_once __DIR__ . '\vendor\setasign\fpdi\src\autoload.php';

			$mpdf = new \Mpdf\Mpdf(array(
						'mode' => '',
						'format' => 'A4',
						'default_font_size' => 8,
						'default_font' => 'tahoma',
						'margin_left' => 10,
						'margin_right' => 10,
						'margin_top' => 10,
						'margin_bottom' => 10,
						'margin_header' => 0,

						'margin_footer' => 0,
						'orientation' => 'P'

					));

			$mpdf->SetHTMLHeader($header);				//Yang diulang di setiap awal halaman  (Header)
			$mpdf->WriteHTML(utf8_encode($content));

			$mpdf->Output();
			
		}


		public function ReportClaimBass_Preview ( $page_title, $datalaporan, $periode, $judul, $kota, $kepalasvc, $status, $params ) 

		{
			$style_col_ganjil ="float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_summary= "float:left;height:30px;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
	
			$kanan= "text-align:right;padding-right:10px;";
			$kiri = "text-align:left; padding-left:10px;";
			
			$header_html="";
			$content_html= "";

			$xgroup = "!@#$%^&*";
			$gtotalbiaya = 0;
			$totalbiaya = 0;
			$gtotaltransport = 0;
			$totaltransport = 0;

			// $content_html. = "<style> body { font-size:9pt; } </style>";
			$content_html.= "<html><body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;padding-left:1px;'>";
			$content_html.= "	<div><b>" .$judul. "</b></div>";			
			$content_html.= "	<div><b>".$periode."</b></div>";	
			
			$content_html.= "</div>";	//close div_header
								

			$content_html.= "<div class='div_body' style='overflow-x:padding-left:1px;'>";
			$content_html.= "	<table style='width:100%'>";
			 
			if ( $status !="noclaim" ) {

				$content_html.= '		<tr><td colspan="9"><b>'.str_replace('“','"',str_replace('”','"',$datalaporan->data[0]->NAMA_BASS)).'</b></td></tr>';

				// Header
				$content_html.= "	<tr>";
				$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>NOMOR NOTA</td>";
				$content_html.= "		<td style='width:8%; border-bottom:thin solid #333; border-top:thin solid #333;'>MERK</td>";
				$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>KODE PRODUK</td>";
				$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>NOMOR_SERI</td>";
				$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO CLAIM</td>";
				$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>KODE PENGADUAN</td>";
				$content_html.= "		<td style='width:8%; border-bottom:thin solid #333; border-top:thin solid #333;'>NAMA PERBAIKAN</td>";
				$content_html.= "		<td align='right' style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;' align='right'>BIAYA</td>";
				$content_html.= "		<td align='right' style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>TRANSPORT</td>";	
				$content_html.= "	</tr>";		
			}

			$jml=count($datalaporan->data);
			for($i=0;$i<$jml;$i++) {

				if ( $status =="noclaim" ) {
					if ( $xgroup != $datalaporan->data[$i]->KODE_CLAIM ) {
					
						// Total 
						if ($xgroup != "!@#$%^&*") {						
							$content_html.= "	<tr><td colspan='7' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL NO CLAIM : ".$xgroup." </b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totalbiaya)."</b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totaltransport)."</b></td>
												</tr>
												<tr><td>&nbsp;</td></tr>";	
	
							$totalbiaya = 0;
							$totaltransport = 0;
						}
	
						// Sub 

						$content_html.= '		<tr><td colspan="9"><b>'.str_replace('“','"',str_replace('”','"',$datalaporan->data[$i]->NAMA_BASS)).'</b></td></tr>';

						$content_html.= "		<tr><td colspan='9'><b>"."NO CLAIM : ".$datalaporan->data[$i]->KODE_CLAIM."</b></td></tr>";
						
						// Header
						$content_html.= "	<tr>";
						$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>NOMOR NOTA</td>";
						$content_html.= "		<td style='width:8%; border-bottom:thin solid #333; border-top:thin solid #333;'>MERK</td>";
						$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>KODE PRODUK</td>";
						$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>NOMOR_SERI</td>";
						$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO CLAIM</td>";
						$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>KODE PENGADUAN</td>";
						$content_html.= "		<td style='width:8%; border-bottom:thin solid #333; border-top:thin solid #333;'>NAMA PERBAIKAN</td>";
						$content_html.= "		<td align='right' style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;' align='right'>BIAYA</td>";
						$content_html.= "		<td align='right' style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>TRANSPORT</td>";	
						$content_html.= "	</tr>";		
					}
				}	

					// TR_SERVICE_REQUEST.KODE_BASS, TR_CLAIM_REQUEST.TANGGAL, MS_BASS.NAMA_BASS, TR_SERVICE_REQUEST.KODE_CLAIM,
					// 			TR_SERVICE_REQUEST.NOMOR_NOTA, TR_SERVICE_REQUEST.KODE_SERVICE, TBLINHEADER.MERK, 
					// 			TR_SERVICE_REQUEST.KODE_PRODUK, TR_SERVICE_REQUEST.NOMOR_SERI, TBLINHEADER.JNS_BRG,
					// 			TR_SERVICE_REQUEST.KODE_PENGADUAN, TR_SERVICE_REQUEST.NAMA_PERBAIKAN, 
					// 			TR_SERVICE_REQUEST.BIAYA, TR_SERVICE_REQUEST.BIAYA_TRANSPORT  

				if ( $datalaporan->data[$i]->NOMOR_NOTA == "" ) {
					$xNOMOR_NOTA = $datalaporan->data[$i]->KODE_SERVICE;
				}
				else {
					$xNOMOR_NOTA = $datalaporan->data[$i]->NOMOR_NOTA;
				}

				// nobukti					
				$content_html.= "	<tr><td>".$xNOMOR_NOTA."</td>";
				$content_html.= "		<td>".$datalaporan->data[$i]->MERK."</td>";
				$content_html.= "		<td>".$datalaporan->data[$i]->KODE_PRODUK."</td>";	
				$content_html.= "		<td>".$datalaporan->data[$i]->NOMOR_SERI."</td>";
				$content_html.= "		<td>".$datalaporan->data[$i]->KODE_CLAIM."</td>";
				$content_html.= "		<td>".$datalaporan->data[$i]->KODE_PENGADUAN."</td>";
				$content_html.= "		<td>".$datalaporan->data[$i]->NAMA_PERBAIKAN."</td>";
				$content_html.= "		<td align='right'>".number_format($datalaporan->data[$i]->BIAYA)."</td>";
				$content_html.= "		<td align='right'>".number_format($datalaporan->data[$i]->BIAYA_TRANSPORT)."</td>";	
				$content_html.= "	</tr>";


				$totalbiaya += $datalaporan->data[$i]->BIAYA;
				$totaltransport += $datalaporan->data[$i]->BIAYA_TRANSPORT;

				$gtotalbiaya += $datalaporan->data[$i]->BIAYA;
				$gtotaltransport += $datalaporan->data[$i]->BIAYA_TRANSPORT;


				if ( $status =="noclaim" ) {
					$xgroup = $datalaporan->data[$i]->KODE_CLAIM;
				}

				if ( $status =="nonota" ) {
					$xgroup = $datalaporan->data[$i]->NOMOR_NOTA;
				}				
			}

			if ($xgroup != "!@#$%^&*") {
				if ( $status =="noclaim" ) {								
					// Total 										
					$content_html.= "	<tr><td colspan='7' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL NO CLAIM : ".$xgroup." </b></td>
										<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totalbiaya)."</b></td>
										<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totaltransport)."</b></td>
										</tr>
										<tr><td>&nbsp;</td></tr>";	
				}
			}
						
			// GTotal
			$content_html.= "		<tr><td colspan='7' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>Grand Total</b></td>
									<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($gtotalbiaya)."</b></td>
									<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($gtotaltransport)."</b></td>
									</tr>";
		
			$content_html.= "</table>";
			$content_html.= "</body></html>";

			if ($jml==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} 

			// echo $content_html;
			$this->Pdf_Report($header_html, $content_html, "","","","","");

			// $data['title'] = $page_title;
			// $data['content_html'] = $content_html;
			// $this->RenderView('ReportFinanceBBTResult',$data);
		}

		public function ReportClaimBass_Excel ( $page_title, $datalaporan, $periode, $judul, $kota, $kepalasvc, $status, $params ) 

		{
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', $judul);
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', $periode);			
            								
			$currcol = 1;
			$currrow = 4;							

			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA BASS');
			$sheet->getColumnDimension('A')->setWidth(25);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NOMOR NOTA');
			$sheet->getColumnDimension('B')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'MERK');
			$sheet->getColumnDimension('C')->setWidth(10);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KODE PRODUK');
			$sheet->getColumnDimension('D')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NOMOR SERI');
			$sheet->getColumnDimension('E')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO CLAIM');
			$sheet->getColumnDimension('F')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KODE_PENGADUAN');
			$sheet->getColumnDimension('G')->setWidth(25);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA PERBAIKAN');
			$sheet->getColumnDimension('H')->setWidth(25);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'BIAYA');
			$sheet->getColumnDimension('I')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TRANSPORT');
			$sheet->getColumnDimension('J')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;

			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
									
			// Detail
			$jum= count($datalaporan->data);
			for($i=0; $i<$jum; $i++){
				
				// TR_SERVICE_REQUEST.KODE_BASS, TR_CLAIM_REQUEST.TANGGAL, MS_BASS.NAMA_BASS, TR_SERVICE_REQUEST.KODE_CLAIM,
					// 			TR_SERVICE_REQUEST.NOMOR_NOTA, TR_SERVICE_REQUEST.KODE_SERVICE, TBLINHEADER.MERK, 
					// 			TR_SERVICE_REQUEST.KODE_PRODUK, TR_SERVICE_REQUEST.NOMOR_SERI, TBLINHEADER.JNS_BRG,
					// 			TR_SERVICE_REQUEST.KODE_PENGADUAN, TR_SERVICE_REQUEST.NAMA_PERBAIKAN, 
					// 			TR_SERVICE_REQUEST.BIAYA, TR_SERVICE_REQUEST.BIAYA_TRANSPORT  
				
				if ( $datalaporan->data[$i]->NOMOR_NOTA == "" ) {
					$xNOMOR_NOTA = $datalaporan->data[$i]->KODE_SERVICE;
				}
				else {
					$xNOMOR_NOTA = $datalaporan->data[$i]->NOMOR_NOTA;
				}

				$currrow++;
				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NAMA_BASS);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $xNOMOR_NOTA);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->MERK);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->KODE_PRODUK);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NOMOR_SERI);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->KODE_CLAIM);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->KODE_PENGADUAN);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NAMA_PERBAIKAN);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->BIAYA));
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$currcol += 1;				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->BIAYA_TRANSPORT));
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$currcol += 1;				
			}
			
			// print_r ($judul);

			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A4:'.$max_col.'5')->getAlignment()->setHorizontal($alignment_center);
			
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A4:'.$max_col.'5')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
			// border
			$sheet->getStyle("A1:".$max_col."5")->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet->getStyle('A4:'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');
			
				
			$filename= $judul.' '.$status. ' ['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 

			if ($jum==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} 

			exit();
		}

		//=======================================//=======================================



    	//=======================================//=======================================

		
		
		//LINA - LAPORAN CLAIM BASS SUMMARY, FROM HERE
		public function reportclaimsummary()
		{
			$data = array();
			$api = 'APITES';

			set_time_limit(60);

			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "REPORT BASS SUMMARY";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU REPORT BASS SUMMARY";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

			//cek Kode_Bass, Kode_Cabang, Flag di BASS_T_SETUP di db BHAKTI masing2 cabang
			//http://localhost:90/bktAPI/MasterDealer/BASS_T_SETUP
            $res = $this->MasterDbModel->getByLocationCode($_SESSION['logged_in']['branch_id']);
            $AlamatWebService = $res->AlamatWebService;
			$url = $AlamatWebService.API_BKT."/MasterDealer/BASS_T_SETUP";
            // $BASS_T_SETUP = json_decode(file_get_contents($url), true);
            $BASS_T_SETUP = file_get_contents($url);
			$BASS_T_SETUP = $this->GzipDecodeModel->_decodeGzip_true($BASS_T_SETUP);
            $data["BASS_T_SETUP"] = $BASS_T_SETUP;		
			$data["BASS_T_SETUP_KODE_BASS"] = $BASS_T_SETUP[0]["Kode_Bass"];
			$data["BASS_T_SETUP_KODE_CABANG"] = $BASS_T_SETUP[0]["Kode_Cabang"];
			$data["BASS_T_SETUP_FLAG"] = json_encode($BASS_T_SETUP[0]["Flag"]);
			//die($_SESSION['logged_in']['branch_id'].' - '.$AlamatWebService.API_BKT.' - '.$BASS_T_SETUP[0]["Kode_Bass"].' - '.$BASS_T_SETUP[0]["Kode_Cabang"].' - '.$data["BASS_T_SETUP_FLAG"]);

			if ($data["BASS_T_SETUP_FLAG"] == 1){
				//die("PUSAT");
				//http://localhost:90/webAPI/Reportbass/loadbasspusat
				$url = $this->API_URL."/Reportbass2/loadbasspusat";
				//die($url);
				$MS_BASS = json_decode(file_get_contents($url), true);
				$data["MS_BASS"] = $MS_BASS;
			} else {
				//die("CABANG");
				//http://localhost:90/webAPI/Reportbass/loadbasscabang?api=APITES&Kode_Bass=C001
				$url = $this->API_URL."/Reportbass2/loadbasscabang?api=".$api."&Kode_Bass=".urlencode($data["BASS_T_SETUP_KODE_CABANG"]);
				//die($url);
				$MS_BASS = json_decode(file_get_contents($url), true);
				$data["MS_BASS"] = $MS_BASS;
			}

			$data["CabangSelected"] = "";
			//echo json_encode($MS_BASS);
			//echo json_encode($MS_BASS[0]["KODE_BASS"]);
			
			//die;
			$data['title'] = 'Laporan Claim Bass Per Cabang';
			$this->RenderView('LaporanClaimBassPerCabangView',$data);
		}

		public function proses_reportclaimsummary()
		{
			$data = array();
			$api = 'APITES';

			//date("Y-m-d H:i:s")
			$_POST = $this->PopulatePost();
			$TGL = date("Y-m-d");		
			$TGL_AWAL = $_POST["dp1"];
			$TGL_AKHIR = $_POST["dp2"];
			$CABANG = $_POST["cabang"];

			$page_title = "Laporan Claim Bass Per Cabang";

			set_time_limit(60);

			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "REPORT BASS SUMMARY";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES REPORT BASS SUMMARY PERIODE ".date("d-M-Y", strtotime($_POST["dp1"]))." S/D ".date("d-M-Y", strtotime($_POST["dp2"]));
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);

			//http://localhost:90/webAPI/Reportbass/confaturanservice?api=APITES&tgl=2022-01-01
            $url = $this->API_URL."/Reportbass2/confaturanservice?api=".$api."&tgl=".urlencode($TGL);
            //die($url);
            $confaturanservice = json_decode(file_get_contents($url), true);
            
			//http://localhost:90/webAPI/Reportbass/reportclaimsummary?api=APITES&tglawal=2022-01-01&tglakhir=2022-01-31&cabang=C001
            $url = $this->API_URL."/Reportbass2/reportclaimsummary?api=".$api."&tglawal=".urlencode($TGL_AWAL)."&tglakhir=".urlencode($TGL_AKHIR).
                        "&cabang=".urlencode($CABANG);
            //die($url);
            $reportclaimsummary = json_decode(file_get_contents($url), true);

            //http://localhost:90/webAPI/Reportbass/loadbasscabang?api=APITES&Kode_Bass=C001
			$url = $this->API_URL."/Reportbass2/loadbasscabang?api=".$api."&Kode_Bass=".urlencode($CABANG);
            //die($url);
            $detailcabang = json_decode(file_get_contents($url), true);

			//echo json_encode($reportclaimsummary);
            if(isset($_POST['btnExcel'])) {
				$this->proses_reportclaimsummary_excel($page_title, $confaturanservice, $reportclaimsummary, $detailcabang, $CABANG, $TGL_AWAL, $TGL_AKHIR, $TGL, $params);
			} else if(isset($_POST['btnPDF'])) {
				$this->proses_reportclaimsummary_pdf($page_title, $confaturanservice, $reportclaimsummary, $detailcabang, $CABANG, $TGL_AWAL, $TGL_AKHIR, $TGL, $params);
			} 
		}

		public function proses_reportclaimsummary_excel ($page_title, $DataConfig, $DataLaporan, $detailcabang, $cabang, $dp1, $dp2, $tgl, $params)
		{				
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', 'LAPORAN BASS CABANG '.$cabang.' - '.$detailcabang[0]['NAMA_BASS']);
			$sheet->setCellValue('A2', 'Periode : '.date_format(date_create($dp1),'d-M-Y').' s.d '.date_format(date_create($dp2),'d-M-Y'));
            $sheet->setCellValue('A4', 'NO.');
			$sheet->setCellValue('B4', 'KOTA/NAMA BASS');
			$sheet->setCellValue('C4', 'BANYAK SERVICE');
			$sheet->setCellValue('D4', 'ONGKOS KERJA');
			$sheet->getColumnDimension('A')->setWidth(10);
			$sheet->getColumnDimension('B')->setWidth(30);
			$sheet->getColumnDimension('C')->setWidth(18);
			$sheet->getColumnDimension('D')->setWidth(18);

			$currcol = 1;
			$startrow = 4;
			$currrow = 4;

			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			
			$no = 0;
			$Sum_Banyak_Service = 0;
			$Sum_Ongkos_Kerja = 0;

			$jum= count($DataLaporan);
			for($i=0; $i<$jum; $i++){						
				$no++;
				$currrow++;
				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);			
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]["NAMA_BASS"]);			
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]["Banyak_Service"]);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]["Ongkos_Kerja"]);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				//$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
				$Sum_Banyak_Service += $DataLaporan[$i]["Banyak_Service"];
				$Sum_Ongkos_Kerja += $DataLaporan[$i]["Ongkos_Kerja"];
			}

			$currrow++;
			$currcol = 2;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "JUMLAH");			
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $Sum_Banyak_Service);
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $Sum_Ongkos_Kerja);
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			//$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
			$endrow = $currrow;

			$currrow++;
			$currrow++;
			$currcol = 4;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataConfig[0]["Kota"].', '.date_format(date_create($tgl),'d-M-Y'));					
			
			$currrow++;
			$currrow++;
			$currrow++;
			$currrow++;
			$currcol = 4;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataConfig[0]["Kepala_Service"]);	

			$currrow++;
			$currcol = 4;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataConfig[0]["Jabatan_Kepala_Service"]);
			
			$sheet->setSelectedCell('A1');
			
			$filename=$page_title.'['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 

			if ($jum==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} 

			exit();
		}	

		public function proses_reportclaimsummary_pdf ($page_title, $DataConfig, $DataLaporan, $detailcabang, $cabang, $dp1, $dp2, $tgl, $params)
		{	
			//ini_set('max_execution_time', '1500');
			ini_set("pcre.backtrack_limit", "10000000");
			set_time_limit(60);

			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'arial',
				'margin_left' => 10,
				'margin_right' => 10,
				'margin_top' => 30,
				'margin_bottom' => 10,
				'margin_header' => 10,
				'margin_footer' => 5,
				'orientation' => 'L'
			));

			$header = '<table border="0" style="width:100%; font-size:15px;">
					<tr>
						<td align="center">
							<b>
								'.$page_title.'
							</b>
						</td>
					</tr>
					<tr>
						<td align="center" style="font-size:12px;">
							PERIODE '.date_format(date_create($dp1),'d-M-Y').' <b>S/D</b> '.date_format(date_create($dp2),'d-M-Y').'
						</td>
					</tr>
				</table>';

			$content = '
			<table width="100%">
				<tr>
					<td></td>
				</tr>
			</table>';

			$content .= '<table style="width:100%; border-collapse: collapse;" border="1">
			<tr>
				<th style="text-align: left; width: 10%; font-size: 12px; padding:5px;">NO.</th>
				<th style="text-align: left; width: 10%; font-size: 12px; padding:5px;">KOTA/NAMA BASS</th>
				<th style="text-align: left; width: 10%; font-size: 12px; padding:5px;" align="right">BANYAK SERVICE</th>
				<th style="text-align: left; width: 10%; font-size: 12px; padding:5px;" align="right">ONGKOS KERJA</th>
			</tr>';

			$no = 0;
			$Sum_Banyak_Service = 0;
			$Sum_Ongkos_Kerja = 0;

			$jum= count($DataLaporan);
			for($i=0; $i<$jum; $i++){	
				$no++;
				$content .='<tr>
								<td style="padding:5px;" align="center">'.$no.'</td>
								<td style="padding:5px;">'.$DataLaporan[$i]["NAMA_BASS"].'</td>
								<td style="padding:5px;" align="right">'.number_format($DataLaporan[$i]["Banyak_Service"],0,",",".").'</td>
								<td style="padding:5px;" align="right">'.number_format($DataLaporan[$i]["Ongkos_Kerja"],0,",",".").'</td>
							</tr>';

				$Sum_Banyak_Service += $DataLaporan[$i]["Banyak_Service"];
				$Sum_Ongkos_Kerja += $DataLaporan[$i]["Ongkos_Kerja"];
			}

			$content .='<br>';
			$content .='<tr>
							<td style="padding:5px;"></td>
							<td style="padding:5px;">JUMLAH</td>
							<td style="padding:5px;" align="right">'.number_format($Sum_Banyak_Service,0,",",".").'</td>
							<td style="padding:5px;" align="right">'.number_format($Sum_Ongkos_Kerja,0,",",".").'</td>
						</tr></table>';	

			$content .='<br>';
			$content .='<br>';
			$content .='<table><tr>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;" align="right"></td>
							<td style="padding:5px;" align="left">'.$DataConfig[0]["Kota"].', '.date_format(date_create($tgl),'d-M-Y').'</td>
						</tr>';				
			
			$content .='<br>';
			$content .='<br>';
			$content .='<br>';
			$content .='<br>';		
			$content .='<tr>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;" align="right"></td>
							<td style="padding:5px;" align="left">'.$DataConfig[0]["Kepala_Service"].'</td>
						</tr>';		

			$content .='<br>';
			$content .='<tr>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;" align="right"></td>
							<td style="padding:5px;" align="left">'.$DataConfig[0]["Jabatan_Kepala_Service"].'</td>
						</tr></table>';	
			
			if ($jum==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} 

			set_time_limit(60);
			$mpdf->SetHTMLHeader($header,'','1');
			$mpdf->WriteHTML($content);
			$mpdf->Output();
		}
		//LINA - LAPORAN CLAIM BASS SUMMARY, UNTIL HERE

		//=======================================//=======================================



		public function ReportPOBass()
		{
            $data = array();
			$api = 'APITES';
			
			set_time_limit(60);

			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "REPORT PO BASS";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU REPORT PO BASS";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);
			
            // print_r($this->API_URL."/ReportBass/GetListCabang?api=".$api."&basspusat=".$BassPusat."&kodecabang=".$KodeCabang);
		    // die;

            $BASS_SETUP = json_decode(file_get_contents($this->API_URL."/ReportBass/BASS_SETUP?api=".$api));
			$data["basssetup"] = $BASS_SETUP;

            $ServerWeb = $BASS_SETUP->data[0]->oConWeb; 
            $DtBaseWeb = $BASS_SETUP->data[0]->DtBaseWeb;
            $KodeBass = $BASS_SETUP->data[0]->Kode_Bass;
            $KodeCabang = $BASS_SETUP->data[0]->Kode_Cabang;
            $BassPusat = $BASS_SETUP->data[0]->Flag;

            // print_r($this->API_URL."/ReportBass/GetListCabang?api=".$api."&basspusat=".$BassPusat."&kodecabang=".$KodeCabang);
		    // die;

            $listcabang = json_decode(file_get_contents($this->API_URL."/ReportBass/GetListCabang?api=".$api."&basspusat=".$BassPusat."&kodecabang=".$KodeCabang));	
			$data["listcabang"] = $listcabang;

            // print_r($listcabang);
            // die;


			$data['title'] = 'Laporan PO Bass | '.WEBTITLE;
			$data['laporan'] = "po";

			// print_r($data);
			// die;
			
			$this->RenderView('ReportBassView',$data);

        }

		public function ReportPOBass_Proses() {
			$page_title = 'Report PO Bass';
			$api = 'APITES';

			set_time_limit(60);

            // print_r($_POST["dp2"]);
            // die;
						

			$tgl1 = date_format(date_create($_POST["dp1"]),'m-d-Y');
			$tgl2 = date_format(date_create($_POST["dp2"]),'m-d-Y');

			$tgl11 = date_format(date_create($_POST["dp1"]),'d-m-Y');
			$tgl22 = date_format(date_create($_POST["dp2"]),'d-m-Y');

			$cabang = $_POST["cabang"];
			$bass = $_POST["bass"];
			$status = $_POST["radstatus"];
			
			// print_r($tgl2);
            // die;

			if (empty($_POST["btnPreview"]) ){ 
				$proses="EXCEL";
			}
			else {
				$proses="PREVIEW";
			}


			// print_r($this->API_URL."/ReportBass/ReportPOBass_ProsesLaporan?api=".$api
			// ."&page_title=".urlencode($page_title)															
			// ."&tgl1=".urlencode($tgl1)
			// ."&tgl2=".urlencode($tgl2)
			// ."&cabang=".urlencode($cabang)
			// ."&bass=".urlencode($bass)
			// ."&status=".urlencode($status));
			// die;

			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "REPORT PO BASS";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES REPORT PO BASS PERIODE ".date("d-M-Y", strtotime($_POST["dp1"]))." S/D ".date("d-M-Y", strtotime($_POST["dp2"]));
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);

			// print_r($this->API_URL."/ReportBass/ReportPOBass_ProsesLaporan?api=".$api
			// ."&page_title=".urlencode($page_title)															
			// ."&tgl1=".urlencode($tgl1)
			// ."&tgl2=".urlencode($tgl2)
			// ."&cabang=".urlencode($cabang)
			// ."&bass=".urlencode($bass)
			// ."&status=".urlencode($status));
			// die;

            $datalaporan = json_decode(file_get_contents($this->API_URL."/ReportBass/ReportPOBass_ProsesLaporan?api=".$api
															."&page_title=".urlencode($page_title)															
															."&tgl1=".urlencode($tgl1)
															."&tgl2=".urlencode($tgl2)
															."&cabang=".urlencode($cabang)
															."&bass=".urlencode($bass)
															."&status=".urlencode($status)
														));
			
			// print_r($datalaporan->result);
            // die;

			if ($datalaporan->result == "gagal") {
				echo ($datalaporan->result." - ".$datalaporan->error);

			} else {			
				$judul = "LAPORAN PO";


				if ( $status =="summary" ) {
					$judul .= " SUMMARY";
				}
				else {
					$judul .= " DETAIL";
				}

				$periode = "Periode " .$tgl11. " S/D " .$tgl22;

				if ( $status == "summary" ) {
					if ($proses=="PREVIEW") {
						$this->ReportPOBassSummary_Preview ( $page_title, $datalaporan, $periode, $judul, $status, $params );				
					}
					else {
						$this->ReportPOBassSummary_Excel ( $page_title, $datalaporan, $periode, $judul, $status, $params );
					}
				}
				else {
					if ($proses=="PREVIEW") {
						$this->ReportPOBassDetail_Preview ( $page_title, $datalaporan, $periode, $judul, $status, $params );				
					}
					else {
						$this->ReportPOBassDetail_Excel ( $page_title, $datalaporan, $periode, $judul, $status, $params );
					}
				}
			}			
		}

		public function ReportPOBassSummary_Preview ( $page_title, $datalaporan, $periode, $judul, $status, $params ) {
			$style_col_ganjil ="float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_summary= "float:left;height:30px;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
	
			$kanan= "text-align:right;padding-right:10px;";
			$kiri = "text-align:left; padding-left:10px;";
			
			$header_html="";
			$content_html= "";

			$xgroup = "!@#$%^&*";

			// $content_html. = "<style> body { font-size:9pt; } </style>";
			$content_html.= "<html><body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;padding-left:1px;'>";
			$content_html.= "	<div><b>" .$judul. "</b></div>";			
			$content_html.= "	<div><b>".$periode."</b></div>";	
			
			$content_html.= "</div>";	//close div_header
								

			$content_html.= "<div class='div_body' style='overflow-x:padding-left:1px;'>";
			$content_html.= "	<table style='width:100%'>";
			
			// BASS 
			$content_html.= '		<tr><td colspan="9"><b>'.'BASS : '.str_replace('“','"',str_replace('”','"',$datalaporan->data[0]->NAMA_BASS)).'</b></td></tr>';

			// Header
			$content_html.= "	<tr>";
			$content_html.= "		<td style='width:20%; border-bottom:thin solid #333; border-top:thin solid #333;'>TANGGAL PO</td>";
			$content_html.= "		<td style='width:20%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO PO</td>";
			$content_html.= "		<td style='width:20%; border-bottom:thin solid #333; border-top:thin solid #333;'>TANGGAL INVOICE</td>";
			$content_html.= "		<td style='width:20%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO INVOICE</td>";	
			$content_html.= "	</tr>";			
			

			$jml=count($datalaporan->data);
			for($i=0;$i<$jml;$i++) {

				if ( $xgroup != $datalaporan->data[$i]->NO_INVOICE ) {									
					// a.KODE_BASS, e.NAMA_BASS, a.TANGGAL, a.NO_PO, c.NO_INVOICE, d.TANGGAL_INVOICE

					// detail					
					$content_html.= "	<tr><td>".date("d-m-Y",strtotime($datalaporan->data[$i]->TANGGAL))."</td>";
					$content_html.= "		<td>".$datalaporan->data[$i]->NO_PO."</td>";
					$content_html.= "		<td>".date("d-m-Y",strtotime($datalaporan->data[$i]->TANGGAL_INVOICE))."</td>";	
					$content_html.= "		<td>".$datalaporan->data[$i]->NO_INVOICE."</td>";	
					$content_html.= "	</tr>";											
				}	
									
				$xgroup = $datalaporan->data[$i]->NO_INVOICE;
				
			}
		
			$content_html.= "</table>";
			$content_html.= "</body></html>";

			if ($jml==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} 

			// echo $content_html;
			$this->Pdf_Report($header_html, $content_html, "","","","","");

			// $data['title'] = $page_title;
			// $data['content_html'] = $content_html;
			// $this->RenderView('ReportFinanceBBTResult',$data);
		}

		public function ReportPOBassDetail_Preview ( $page_title, $datalaporan, $periode, $judul, $status, $params ) {
			$style_col_ganjil ="float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_summary= "float:left;height:30px;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
	
			$kanan= "text-align:right;padding-right:10px;";
			$kiri = "text-align:left; padding-left:10px;";
			
			$header_html="";
			$content_html= "";

			$xgroup1 = "!@#$%^&*";
			$xgroup2 = "!@#$%^&*";
			$xsisa = 0;
			// $xqtyinv = 0;

			// $content_html. = "<style> body { font-size:9pt; } </style>";
			$content_html.= "<html><body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;padding-left:1px;'>";
			$content_html.= "	<div><b>" .$judul. "</b></div>";			
			$content_html.= "	<div><b>".$periode."</b></div>";	
			
			$content_html.= "</div>";	//close div_header
								

			$content_html.= "<div class='div_body' style='overflow-x:padding-left:1px;'>";
			$content_html.= "	<table style='width:100%'>";
			
			// BASS 
			$content_html.= '		<tr><td colspan="9"><b>'.'BASS : '.str_replace('“','"',str_replace('”','"',$datalaporan->data[0]->NAMA_BASS)).'</b></td></tr>';

			// Header
			$content_html.= "	<tr>";
			$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>TANGGAL PO</td>";
			$content_html.= "		<td style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO PO</td>";
			$content_html.= "		<td style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>PART ID</td>";
			$content_html.= "		<td style='width:25%; border-bottom:thin solid #333; border-top:thin solid #333;'>NAMA PART</td>";	
			$content_html.= "		<td align='right'; style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>QTY PO</td>";
			$content_html.= "		<td align='right'; style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>QTY INVOICE</td>";	
			$content_html.= "		<td align='right'; style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>SISA</td>";
			$content_html.= "	</tr>";			
			

			$jml=count($datalaporan->data);
			for($i=0;$i<$jml;$i++) {

				if ( $xgroup1 != $datalaporan->data[$i]->NO_PO ) {									
					// a.KODE_BASS, e.NAMA_BASS, a.TANGGAL, a.NO_PO, b.PartID AS PARTID, 
					// f.nm_sparepart AS NM_SPAREPART, b.QUANTITY, c.NO_INVOICE
					// QTYINCOICE

					$xsisa = $datalaporan->data[$i]->QUANTITY - $datalaporan->data[$i]->QTYINCOICE;

					// Detail					
					$content_html.= "	<tr><td>".date("d-m-Y",strtotime($datalaporan->data[$i]->TANGGAL))."</td>";
					$content_html.= "		<td>".$datalaporan->data[$i]->NO_PO."</td>";
					$content_html.= "		<td>".$datalaporan->data[$i]->PARTID."</td>";	
					$content_html.= "		<td>".$datalaporan->data[$i]->NM_SPAREPART."</td>";	
					$content_html.= "		<td align='right'; >".number_format($datalaporan->data[$i]->QUANTITY)."</td>";
					$content_html.= "		<td align='right'; >".number_format($datalaporan->data[$i]->QTYINCOICE)."</td>";	
					$content_html.= "		<td align='right'; >".number_format($xsisa)."</td>";	
					$content_html.= "	</tr>";			
					
					// $xqtyinv = 0;
				}	
				else {
					if ( $xgroup2 != $datalaporan->data[$i]->PARTID ) {
						$xsisa = $datalaporan->data[$i]->QUANTITY - $datalaporan->data[$i]->QTYINCOICE;

						// Detail					
						$content_html.= "	<tr><td>".date("d-m-Y",strtotime($datalaporan->data[$i]->TANGGAL))."</td>";
						$content_html.= "		<td>".$datalaporan->data[$i]->NO_PO."</td>";
						$content_html.= "		<td>".$datalaporan->data[$i]->PARTID."</td>";	
						$content_html.= "		<td>".$datalaporan->data[$i]->NM_SPAREPART."</td>";	
						$content_html.= "		<td align='right'; >".number_format($datalaporan->data[$i]->QUANTITY)."</td>";
						$content_html.= "		<td align='right'; >".number_format($datalaporan->data[$i]->QTYINCOICE)."</td>";	
						$content_html.= "		<td align='right'; >".number_format($xsisa)."</td>";	
						$content_html.= "	</tr>";			
						
						// $xqtyinv = 0;
					}
				}
									
				$xgroup1 = $datalaporan->data[$i]->NO_PO;
				$xgroup2 = $datalaporan->data[$i]->PARTID;
				// $xqtyinv += $datalaporan->data[$i]->QUANTITY;
			}
		
			$content_html.= "</table>";
			$content_html.= "</body></html>";

			if ($jml==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} 

			// echo $content_html;
			$this->Pdf_Report($header_html, $content_html, "","","","","");

			// $data['title'] = $page_title;
			// $data['content_html'] = $content_html;
			// $this->RenderView('ReportFinanceBBTResult',$data);
		}

		public function ReportPOBassSummary_Excel ( $page_title, $datalaporan, $periode, $judul, $status, $params ) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', $judul);
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', $periode);			
            								
			$currcol = 1;
			$currrow = 4;							

			// a.KODE_BASS, e.NAMA_BASS, a.TANGGAL, a.NO_PO, c.NO_INVOICE, d.TANGGAL_INVOICE

			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA BASS');
			$sheet->getColumnDimension('A')->setWidth(30);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TANGGAL PO');
			$sheet->getColumnDimension('B')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO PO');
			$sheet->getColumnDimension('C')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TANGGAL INVOICE');
			$sheet->getColumnDimension('D')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO INVOICE');
			$sheet->getColumnDimension('E')->setWidth(25);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;

			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
									
			// Detail
			$jum= count($datalaporan->data);
			for($i=0; $i<$jum; $i++){
				
				// a.KODE_BASS, e.NAMA_BASS, a.TANGGAL, a.NO_PO, c.NO_INVOICE, d.TANGGAL_INVOICE
				
				$currrow++;
				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NAMA_BASS);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-m-Y",strtotime($datalaporan->data[$i]->TANGGAL)));
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_PO);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-m-Y",strtotime($datalaporan->data[$i]->TANGGAL_INVOICE)));
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_INVOICE);
				$currcol += 1;		
			}
			
			// print_r ($judul);

			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A4:'.$max_col.'5')->getAlignment()->setHorizontal($alignment_center);
			
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A4:'.$max_col.'5')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
			// border
			$sheet->getStyle("A1:".$max_col."5")->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet->getStyle('A4:'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');
			
				
			$filename= $judul.' '.$status. ' ['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 

			if ($jum==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} 

			exit();
		}

		public function ReportPOBassDetail_Excel ( $page_title, $datalaporan, $periode, $judul, $status, $params ) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', $judul);
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', $periode);			
            								
			$currcol = 1;
			$currrow = 4;							

			// a.KODE_BASS, e.NAMA_BASS, a.TANGGAL, a.NO_PO, b.PartID AS PARTID, 
					// f.nm_sparepart AS NM_SPAREPART, b.QUANTITY, c.NO_INVOICE
					// QTYINCOICE

			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA BASS');
			$sheet->getColumnDimension('A')->setWidth(30);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TANGGAL PO');
			$sheet->getColumnDimension('B')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO PO');
			$sheet->getColumnDimension('C')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PART ID');
			$sheet->getColumnDimension('D')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA PART');
			$sheet->getColumnDimension('E')->setWidth(30);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY PO');
			$sheet->getColumnDimension('F')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY INCOICE');
			$sheet->getColumnDimension('G')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SISA');
			$sheet->getColumnDimension('H')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;

			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
						
			$xgroup1 = "!@#$%^&*";
			$xgroup2 = "!@#$%^&*";
			$xsisa = 0;
			$xqtyinv = 0;

			// Detail
			$jum= count($datalaporan->data);
			for($i=0; $i<$jum; $i++){
				
				// a.KODE_BASS, e.NAMA_BASS, a.TANGGAL, a.NO_PO, b.PartID AS PARTID, 
					// f.nm_sparepart AS NM_SPAREPART, b.QUANTITY, c.NO_INVOICE
					// QTYINCOICE
				
					$xsisa = $datalaporan->data[$i]->QUANTITY - $datalaporan->data[$i]->QTYINCOICE;

					$currrow++;
					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NAMA_BASS);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-m-Y",strtotime($datalaporan->data[$i]->TANGGAL)));
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_PO);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->PARTID);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NM_SPAREPART);
					$currcol += 1;	
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->QUANTITY));
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->QTYINCOICE));
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xsisa));
					$currcol += 1;	
							
			}
			
			// print_r ($judul);

			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A4:'.$max_col.'5')->getAlignment()->setHorizontal($alignment_center);
			
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A4:'.$max_col.'5')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
			// border
			$sheet->getStyle("A1:".$max_col."5")->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet->getStyle('A4:'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');
			
				
			$filename= $judul.' '.$status. ' ['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 

			if ($jum==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} 

			exit();
		}
		//=======================================//=======================================




		//=======================================//=======================================
		public function ReportServiceBass()
		{
            $data = array();
			$api = 'APITES';
			
			set_time_limit(60);

			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "REPORT SERVICE BASS";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU REPORT SERVICE BASS";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);
			
            // print_r($this->API_URL."/ReportBass/GetListCabang?api=".$api."&basspusat=".$BassPusat."&kodecabang=".$KodeCabang);
		    // die;

            $BASS_SETUP = json_decode(file_get_contents($this->API_URL."/ReportBass/BASS_SETUP?api=".$api));
			$data["basssetup"] = $BASS_SETUP;

            $ServerWeb = $BASS_SETUP->data[0]->oConWeb; 
            $DtBaseWeb = $BASS_SETUP->data[0]->DtBaseWeb;
            $KodeBass = $BASS_SETUP->data[0]->Kode_Bass;
            $KodeCabang = $BASS_SETUP->data[0]->Kode_Cabang;
            $BassPusat = $BASS_SETUP->data[0]->Flag;

            // print_r($this->API_URL."/ReportBass/GetListCabang?api=".$api."&basspusat=".$BassPusat."&kodecabang=".$KodeCabang);
		    // die;

            $listcabang = json_decode(file_get_contents($this->API_URL."/ReportBass/GetListCabang?api=".$api."&basspusat=".$BassPusat."&kodecabang=".$KodeCabang));	
			$data["listcabang"] = $listcabang;

            // print_r($listcabang);
            // die;


			$data['title'] = 'Laporan Service Bass | '.WEBTITLE;
			$data['laporan'] = "service";

			// print_r($data);
			// die;
			
			$this->RenderView('ReportBassView',$data);

        }

		public function ReportServiceBass_Proses() {
			$page_title = 'Report Service Bass';
			$api = 'APITES';

			set_time_limit(60);

            // print_r($_POST["dp2"]);
            // die;
						

			$tgl1 = date_format(date_create($_POST["dp1"]),'m-d-Y');
			$tgl2 = date_format(date_create($_POST["dp2"]),'m-d-Y');

			$tgl11 = date_format(date_create($_POST["dp1"]),'d-m-Y');
			$tgl22 = date_format(date_create($_POST["dp2"]),'d-m-Y');

			$cabang = $_POST["cabang"];
			$bass = $_POST["bass"];
			$status = $_POST["radstatus"];
			
			// print_r($tgl2);
            // die;

			if (empty($_POST["btnPreview"]) ){ 
				$proses="EXCEL";
			}
			else {
				$proses="PREVIEW";
			}


			// print_r($this->API_URL."/ReportBass/ReportServiceBass_ProsesLaporan?api=".$api
			// ."&page_title=".urlencode($page_title)															
			// ."&tgl1=".urlencode($tgl1)
			// ."&tgl2=".urlencode($tgl2)
			// ."&cabang=".urlencode($cabang)
			// ."&bass=".urlencode($bass)
			// ."&status=".urlencode($status));
			// die;

			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "REPORT SERVICE BASS";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES REPORT SERVICE BASS PERIODE ".date("d-M-Y", strtotime($_POST["dp1"]))." S/D ".date("d-M-Y", strtotime($_POST["dp2"]));
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);

            $datalaporan = json_decode(file_get_contents($this->API_URL."/ReportBass/ReportServiceBass_ProsesLaporan?api=".$api
															."&page_title=".urlencode($page_title)															
															."&tgl1=".urlencode($tgl1)
															."&tgl2=".urlencode($tgl2)
															."&cabang=".urlencode($cabang)
															."&bass=".urlencode($bass)
															."&status=".urlencode($status)
														));

			// print_r($datalaporan);
			// die;

			if ($datalaporan->result == "gagal") {
				echo ($datalaporan->result." - ".$datalaporan->error);
				
			} else {												
				$AturanPajak = json_decode(file_get_contents($this->API_URL."/ReportBass/AturanPajak?api=".$api));
				$kota = $AturanPajak->data[0]->KOTA; 
				$kepalasvc = $AturanPajak->data[0]->KEPALA_SERVICE; 

				$judul = "LAPORAN SERVICE BASS";
				if ( $status =="garansi" ) {
					$judul .= " Berdasarkan GARANSI";
				}
				else if ( $status =="nongaransi" ) {
					$judul .= " Berdasarkan NON GARANSI";
				}
				else {
					$judul .= " Berdasarkan GARANSI & NON GARANSI";
				}

				$periode = "Periode " .$tgl11. " S/D " .$tgl22;


				// print_r($AturanPajak);
				// die;	

				if ($proses=="PREVIEW") {
					$this->ReportServiceBass_Preview ( $page_title, $datalaporan, $periode, $judul, $kota, $kepalasvc, $status, $params );				
				}
				else {
					$this->ReportServiceBass_Excel ( $page_title, $datalaporan, $periode, $judul, $kota, $kepalasvc, $status, $params );
				}			
			}
		}

		public function ReportServiceBass_Preview ( $page_title, $datalaporan, $periode, $judul, $kota, $kepalasvc, $status, $params ) 
		{

			// echo count($datalaporan->data);die;

			$style_col_ganjil ="float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_summary= "float:left;height:30px;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
	
			$kanan= "text-align:right;padding-right:10px;";
			$kiri = "text-align:left; padding-left:10px;";
			
			$header_html="";
			$content_html= "";

			$xgroup1 = "!@#$%^&*";
			$xgroup2 = "!@#$%^&*";
			$gtotalbiaya = 0;
			$totalbiaya = 0;
			$gtotaltransport = 0;
			$totaltransport = 0;

			// $content_html. = "<style> body { font-size:9pt; } </style>";
			$content_html.= "<html><body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;padding-left:1px;'>";
			$content_html.= "	<div><b>" .$judul. "</b></div>";			
			$content_html.= "	<div><b>".$periode."</b></div>";	
			
			$content_html.= "</div>";	//close div_header
								

			$content_html.= "<div class='div_body' style='overflow-x:padding-left:1px;'>";
			$content_html.= "	<table style='width:100%'>";			
			$content_html.= '		<tr><td colspan="9"><b>'.str_replace('“','"',str_replace('”','"',$datalaporan->data[0]->NAMA_BASS)).'</b></td></tr>';

			$jml=count($datalaporan->data);
			for($i=0;$i<$jml;$i++) {

				if ( $xgroup1 != $datalaporan->data[$i]->GARANSI ) {
					// Total 
					if ($xgroup2 != "!@#$%^&*") {						
						$content_html.= "	<tr><td colspan='8' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL NO CLAIM : ".$xgroup2." </b></td>
											<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totalbiaya)."</b></td>
											<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totaltransport)."</b></td>
											</tr>
											<tr><td>&nbsp;</td></tr>";	

						$totalbiaya = 0;
						$totaltransport = 0;
					}

					// Sub 					
					$content_html.= "		<tr><td colspan='9'><b>".$datalaporan->data[$i]->GARANSI."</b></td></tr>";
					$content_html.= "		<tr><td colspan='9'><b>"."NO CLAIM : ".$datalaporan->data[$i]->KODE_CLAIM."</b></td></tr>";
					
					// Header
					$content_html.= "	<tr>";
					$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>NOMOR NOTA</td>";
					$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>TANGGAL</td>";
					$content_html.= "		<td style='width:8%; border-bottom:thin solid #333; border-top:thin solid #333;'>MERK</td>";
					$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>KODE PRODUK</td>";
					$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>NOMOR_SERI</td>";
					$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>JENIS</td>";
					$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>KODE PENGADUAN</td>";
					$content_html.= "		<td style='width:8%; border-bottom:thin solid #333; border-top:thin solid #333;'>NAMA PERBAIKAN</td>";
					$content_html.= "		<td align='right' style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>BIAYA</td>";
					$content_html.= "		<td align='right' style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>TRANSPORT</td>";	
					$content_html.= "	</tr>";	
				}
				else {
					if ( $xgroup2 != $datalaporan->data[$i]->KODE_CLAIM ) {
					
						// Total 
						if ($xgroup2 != "!@#$%^&*") {						
							$content_html.= "	<tr><td colspan='8' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL NO CLAIM : ".$xgroup2." </b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totalbiaya)."</b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totaltransport)."</b></td>
												</tr>
												<tr><td>&nbsp;</td></tr>";	
	
							$totalbiaya = 0;
							$totaltransport = 0;
						}
	
						// Sub 
						$content_html.= "		<tr><td colspan='9'><b>".$datalaporan->data[$i]->GARANSI."</b></td></tr>";
						$content_html.= "		<tr><td colspan='9'><b>"."NO CLAIM : ".$datalaporan->data[$i]->KODE_CLAIM."</b></td></tr>";
						
						// Header
						$content_html.= "	<tr>";
						$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>NOMOR NOTA</td>";
						$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>TANGGAL</td>";
						$content_html.= "		<td style='width:8%; border-bottom:thin solid #333; border-top:thin solid #333;'>MERK</td>";
						$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>KODE PRODUK</td>";
						$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>NOMOR_SERI</td>";
						$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>JENIS</td>";
						$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>KODE PENGADUAN</td>";
						$content_html.= "		<td style='width:8%; border-bottom:thin solid #333; border-top:thin solid #333;'>NAMA PERBAIKAN</td>";
						$content_html.= "		<td align='right' style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>BIAYA</td>";
						$content_html.= "		<td align='right' style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>TRANSPORT</td>";	
						$content_html.= "	</tr>";		
					}
				}	

					// TR_SERVICE_REQUEST.KODE_BASS, TR_CLAIM_REQUEST.TANGGAL, MS_BASS.NAMA_BASS, TR_SERVICE_REQUEST.KODE_CLAIM,
					// 			TR_SERVICE_REQUEST.NOMOR_NOTA, TR_SERVICE_REQUEST.KODE_SERVICE, TBLINHEADER.MERK, 
					// 			TR_SERVICE_REQUEST.KODE_PRODUK, TR_SERVICE_REQUEST.NOMOR_SERI, TBLINHEADER.JNS_BRG,
					// 			TR_SERVICE_REQUEST.KODE_PENGADUAN, TR_SERVICE_REQUEST.NAMA_PERBAIKAN, 
					// 			TR_SERVICE_REQUEST.BIAYA, TR_SERVICE_REQUEST.BIAYA_TRANSPORT  


				// nobukti					
				$content_html.= "	<tr><td>".$datalaporan->data[$i]->KODE_SERVICE."</td>";
				$content_html.= "		<td>".date("d-m-Y",strtotime($datalaporan->data[$i]->TANGGAL))."</td>";
				$content_html.= "		<td>".$datalaporan->data[$i]->MERK."</td>";
				$content_html.= "		<td>".$datalaporan->data[$i]->KODE_PRODUK."</td>";	
				$content_html.= "		<td>".$datalaporan->data[$i]->NOMOR_SERI."</td>";
				$content_html.= "		<td>".$datalaporan->data[$i]->JNS_BRG."</td>";
				$content_html.= "		<td>".$datalaporan->data[$i]->KODE_PENGADUAN."</td>";
				$content_html.= "		<td>".$datalaporan->data[$i]->NAMA_PERBAIKAN."</td>";
				$content_html.= "		<td align='right'>".number_format($datalaporan->data[$i]->BIAYA)."</td>";
				$content_html.= "		<td align='right'>".number_format($datalaporan->data[$i]->BIAYA_TRANSPORT)."</td>";	
				$content_html.= "	</tr>";

				$totalbiaya += $datalaporan->data[$i]->BIAYA;
				$totaltransport += $datalaporan->data[$i]->BIAYA_TRANSPORT;

				$gtotalbiaya += $datalaporan->data[$i]->BIAYA;
				$gtotaltransport += $datalaporan->data[$i]->BIAYA_TRANSPORT;

				$xgroup1 = $datalaporan->data[$i]->GARANSI;
				$xgroup2 = $datalaporan->data[$i]->KODE_CLAIM;
								
			}
											
			// Total 										
			$content_html.= "	<tr><td colspan='8' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL NO CLAIM : ".$xgroup2." </b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totalbiaya)."</b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totaltransport)."</b></td>
								</tr>
								<tr><td>&nbsp;</td></tr>";	
			
						
			// GTotal
			$content_html.= "		<tr><td colspan='8' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>Grand Total</b></td>
									<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($gtotalbiaya)."</b></td>
									<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($gtotaltransport)."</b></td>
									</tr>";
		
			$content_html.= "</table>";
			$content_html.= "</body></html>";

			if ($jml==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} 

			// echo $content_html;
			$this->Pdf_Report($header_html, $content_html, "","","","","");

			// $data['title'] = $page_title;
			// $data['content_html'] = $content_html;
			// $this->RenderView('ReportFinanceBBTResult',$data);
		}

		public function ReportServiceBass_Excel ( $page_title, $datalaporan, $periode, $judul, $kota, $kepalasvc, $status, $params ) 
		{
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', $judul);
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', $periode);			
            								
			$currcol = 1;
			$currrow = 4;							

			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA BASS');
			$sheet->getColumnDimension('A')->setWidth(25);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GARANSI');
			$sheet->getColumnDimension('B')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NOMOR NOTA');
			$sheet->getColumnDimension('C')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TANGGAL');
			$sheet->getColumnDimension('D')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'MERK');
			$sheet->getColumnDimension('E')->setWidth(10);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KODE PRODUK');
			$sheet->getColumnDimension('F')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NOMOR SERI');
			$sheet->getColumnDimension('G')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'JENIS');
			$sheet->getColumnDimension('H')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KODE_PENGADUAN');
			$sheet->getColumnDimension('I')->setWidth(25);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA PERBAIKAN');
			$sheet->getColumnDimension('J')->setWidth(25);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'BIAYA');
			$sheet->getColumnDimension('K')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TRANSPORT');
			$sheet->getColumnDimension('L')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;

			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
									
			// Detail
			$jum= count($datalaporan->data);
			for($i=0; $i<$jum; $i++){
				
				// TR_SERVICE_REQUEST.KODE_BASS, TR_CLAIM_REQUEST.TANGGAL, MS_BASS.NAMA_BASS, TR_SERVICE_REQUEST.KODE_CLAIM,
					// 			TR_SERVICE_REQUEST.NOMOR_NOTA, TR_SERVICE_REQUEST.KODE_SERVICE, TBLINHEADER.MERK, 
					// 			TR_SERVICE_REQUEST.KODE_PRODUK, TR_SERVICE_REQUEST.NOMOR_SERI, TBLINHEADER.JNS_BRG,
					// 			TR_SERVICE_REQUEST.KODE_PENGADUAN, TR_SERVICE_REQUEST.NAMA_PERBAIKAN, 
					// 			TR_SERVICE_REQUEST.BIAYA, TR_SERVICE_REQUEST.BIAYA_TRANSPORT  
				
				// if ( $datalaporan->data[$i]->NOMOR_NOTA == "" ) {
				// 	$xNOMOR_NOTA = $datalaporan->data[$i]->KODE_SERVICE;
				// }
				// else {
				// 	$xNOMOR_NOTA = $datalaporan->data[$i]->NOMOR_NOTA;
				// }

				$currrow++;
				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NAMA_BASS);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->GARANSI);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->KODE_SERVICE);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-m-Y",strtotime($datalaporan->data[$i]->TANGGAL)));
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->MERK);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->KODE_PRODUK);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NOMOR_SERI);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->JNS_BRG);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->KODE_PENGADUAN);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NAMA_PERBAIKAN);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->BIAYA));
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$currcol += 1;				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->BIAYA_TRANSPORT));
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$currcol += 1;				
			}
			
			// print_r ($judul);

			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A4:'.$max_col.'5')->getAlignment()->setHorizontal($alignment_center);
			
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A4:'.$max_col.'5')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
			// border
			$sheet->getStyle("A1:".$max_col."5")->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet->getStyle('A4:'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');
			
				
			$filename= $judul.' '.$status. ' ['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 

			if ($jum==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} 

			exit();
		}
		//=======================================//=======================================





	



		public function HapusFakturBass(){
		
			$data['title'] = 'Hapus Faktur BASS';
			$this->RenderView('HapusFakturBassView',$data);
		}
		
		public function CariFaktur(){
		
			$api = 'APITES';
			$post = $this->PopulatePost();
			$url = $this->API_URL."/ReportBass/GetFakturBass?api=".$api."&no_faktur=".urlencode($post['no_faktur']);
			// echo $url;die;
			$request = file_get_contents($url);
			
			$result["result"] = "gagal";
			$result["data"] = null;
			$result["error"] = "URL sedang offline";
			
			if($request!== false){
				$result = json_decode($request);
			}
		
			$hasil = json_encode($result);
			header('HTTP/1.1: 200');
			header('Status: 200');
			header('Content-Length: '.strlen($hasil));
			exit($hasil);
		}

		public function GetFakturList() {

			$datatableRequest = $this->input->get();

			$api = 'APITES';

			$no_invoice = $datatableRequest['sSearch'];
			$page_size = $datatableRequest["iDisplayLength"];
			$page_number = $datatableRequest["iDisplayStart"] + 1;

			// die($this->API_URL);

			$url = $this->API_URL."/ReportBass/GetFakturListBass?api=".$api
						."&page_size=".$page_size."&page_number=".$page_number."&no_invoice=".$no_invoice;
			// echo $url;die;
			$request = file_get_contents($url);
			
			$result["result"] = "gagal";
			$result["data"] = null;
			$result["error"] = "URL sedang offline";
			
			if($request!== false){
				$result = json_decode($request);
			}
		
			$hasil = json_encode($result->data);
			// print_r($result->data); die;

			$contents = $result->data->content;
			$totalRecords = $result->data->count;

			$data_list=array();
			foreach($contents as $content) {
				$tamp=array();
				$tamp[] = $content->NO_INVOICE;
				$tamp[] = $content->TANGGAL;
				$tamp[] = $content->STATUS;
				$tamp[] = $content->NO_PO;
				// $tamp[]='<button class="btn btn-dark" id="btntest" title="Pilih" type="button"><span class="glyphicon glyphicon-pencil"></span></button>';

				$data_list[]=$tamp;
			}

			$data_hasil['aaData'] = $data_list;
			$data_hasil['iTotalDisplayRecords'] = $totalRecords;

			header('HTTP/1.1: 200');
			header('Status: 200');
			header('Content-Length: '.strlen($hasil));
			exit(json_encode($data_hasil));
		}
		
		public function HapusFaktur(){
		
			$api = 'APITES';
			$post = $this->PopulatePost();
			$url = $this->API_URL."/ReportBass/HapusFakturBass?api=".$api."&no_faktur=".urlencode($post['no_faktur'])."&no_po=".urlencode($post['no_po']);
			// echo $url;die;
			$request = file_get_contents($url);
			
			$result["result"] = "gagal";
			$result["data"] = null;
			$result["error"] = "URL sedang offline";
			
			if($request!== false){
				$result = json_decode($request);
			}
		
			$hasil = json_encode($result);
			header('HTTP/1.1: 200');
			header('Status: 200');
			header('Content-Length: '.strlen($hasil));
			exit($hasil);
		}

	}
	
?>

