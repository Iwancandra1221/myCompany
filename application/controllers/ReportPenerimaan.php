<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class ReportPenerimaan extends MY_Controller 
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
			$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
		}


        public function index()
		{
            $data = array();
			$api = 'APITES';
			
			set_time_limit(60);
			
            $mainUrl = $_SESSION["conn"]->AlamatWebService . $this->API_BKT;
            $data["mainurl"] = $mainUrl;

            $svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;

            // print_r($mainUrl."/MasterAccountBank/GetListAllAccountBank?api=".$api
			// ."&svr=".$svr."&db=".$db);
		    // die;

            $listtypeterima = json_decode(file_get_contents($mainUrl."/ReportPenerimaan/GetListTypeTerima?api=".$api
															."&svr=".$svr."&db=".$db));
			$data["listtypeterima"] = $listtypeterima;

            // $listwilayah = json_decode(file_get_contents($this->API_URL."/MsDealer/GetListWilayahDealer?api=".$api));	
            $listwilayah = file_get_contents($this->API_URL."/MsDealer/GetListWilayahDealer?api=".$api);	
            $listwilayah = $this->GzipDecodeModel->_decodeGzip($listwilayah);
			$data["listwilayah"] = $listwilayah;
			
            $listbank = json_decode(file_get_contents($this->API_URL."/MsBank/GetListBank?api=".$api));	
			$data["listbank"] = $listbank;

			$data['title'] = 'Laporan Penerimaan | '.WEBTITLE;
			
			// print_r($data);
			// die;
			
			$this->RenderView('ReportPenerimaanView',$data);
		}


        public function ReportPenerimaan_Proses() {
			$page_title = 'Report Penerimaan';
			$api = 'APITES';

			set_time_limit(60);

            // print_r($_POST["dp2"]);
            // die;
						
			$tgl1 = $_POST["dp1"];
			$tgl2 = $_POST["dp2"];
			$tipe_terima = $_POST["typeterima"];
			$bank = $_POST["bank"];
			$wilayah = $_POST["wilayah"];
			$wil = $_POST["wil"];
			$wil_list = $_POST["wil_list"];
			$status = $_POST["radstatus"];
			
			// print_r($tgl2);
            // die;

			if (empty($_POST["btnPreview"]) ){ 
				$proses="EXCEL";
			}
			else {
				$proses="PREVIEW";
			}

			if (empty($_POST["cetak_detail"]) ){ 
				$cetak_detail = "N";
			}
			else {
				$cetak_detail = $_POST["cetak_detail"];
			}


			// AmbilData
			$mainUrl = $_SESSION["conn"]->AlamatWebService . $this->API_BKT;
            // $data["mainurl"] = $mainUrl;

            $svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;


			// print_r($mainUrl."/ReportPenerimaan/ReportPenerimaan_ProsesLaporan?api=".$api
			// ."&server=".urlencode($svr)
			// ."&db=".urlencode($db)
			// ."&page_title=".urlencode($page_title)															
			// ."&tgl1=".urlencode($tgl1)
			// ."&tgl2=".urlencode($tgl2)
			// ."&tipe_terima=".urlencode($tipe_terima)
			// ."&bank=".urlencode($bank)
			// ."&wil_list=".urlencode($wil_list)
			// ."&status=".urlencode($status));
			// die;


            $datalaporan = json_decode(file_get_contents($mainUrl."/ReportPenerimaan/ReportPenerimaan_ProsesLaporan?api=".$api
															."&server=".urlencode($svr)
															."&db=".urlencode($db)
															."&page_title=".urlencode($page_title)															
															."&tgl1=".urlencode($tgl1)
															."&tgl2=".urlencode($tgl2)
															."&tipe_terima=".urlencode($tipe_terima)
															."&bank=".urlencode($bank)
															."&wil_list=".urlencode($wil_list)
															."&status=".urlencode($status)
														));


			// print_r($datalaporan);
			// die;	

			if ( $cetak_detail == "N" ) {
				$judul = "Laporan Penerimaan Pembayaran";
			}
			else {
				$judul = "Laporan Penerimaan Pembayaran - Detail";
			}
													
			$periode = "Periode " .$tgl1. " S/D " .$tgl2;

			if ($wilayah == "ALL") {
				$wilayah2 = " Wilayah : ALL";
			}
			else {
				$wilayah2 = " Wilayah : " .$wil;
			}

			if ($status == "semua") {
				$sts = "Status : Semua Penerimaan";
			}			
			elseif ($status == "gantung") {
				$sts = "Status : Penerimaan Gantung (Belum ada BBT)";
			}			
			else {
				$sts = "Status : Penerimaan yang sudah dijadikan BBT";
			}		
			
			if ($proses=="PREVIEW") {
				$this->ReportPenerimaan_Preview ( $page_title, $datalaporan, $periode, $judul, $wilayah2, $tipe_terima, $bank, $sts, $cetak_detail );				
			}
			else {
				$this->ReportPenerimaan_Excel ( $page_title, $datalaporan, $periode, $judul, $wilayah2, $tipe_terima, $bank, $sts, $cetak_detail );
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
						'orientation' => 'L'
					));

			$mpdf->SetHTMLHeader($header);				//Yang diulang di setiap awal halaman  (Header)
			$mpdf->WriteHTML(utf8_encode($content));

			$mpdf->Output();
			
		}


		public function ReportPenerimaan_Preview ( $page_title, $datalaporan, $periode, $judul, $wilayah2, $tipe_terima, $bank, $status, $cetak_detail ) {
			$style_col_ganjil ="float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_summary= "float:left;height:30px;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
	
			$kanan= "text-align:right;padding-right:10px;";
			$kiri = "text-align:left; padding-left:10px;";
			
			$header_html="";
			$content_html= "";

			$xnobukti = "!@#$%^&*";
			$xtipe_terima = "!@#$%^&*";
			$xnofaktur= "";
			$gtotal = 0;
			$totaltipeterima = 0;

			// $content_html. = "<style> body { font-size:9pt; } </style>";
			$content_html.= "<html><body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;padding-left:1px;'>";
			$content_html.= "	<div><b>" .$judul. "</b></div>";			
			$content_html.= "	<div><b>".$periode."</b></div>";			
			$content_html.= "	<div><b>Tipe Terima : ".$tipe_terima."</b></div>";
			$content_html.= "	<div><b>".$bank."</b></div>";
			$content_html.= "	<div><b>".$status."</b></div>";			
			$content_html.= "	<div><b>".$wilayah2."</b></div>";
			
			$content_html.= "</div>";	//close div_header


			// TblPenerimaanPemb.Type_Terima AS TYPE_TERIMA, TblPenerimaanPemb.Tgl_Trans AS TGL_TRANS, 
			// 			TblMsDealer.Nm_Plg AS NM_PLG, TblPenerimaanPemb.No_Penerimaan AS NO_PENERIMAAN, 
			// 			TblPenerimaanPembFaktur.No_Faktur AS NO_FAKTUR, TblPenerimaanPemb.Divisi AS DIVISI, 
			// 			TblPenerimaanPemb.Bank AS BANK, TblPenerimaanPemb.No_giro AS NO_GIRO,
			// 			TblPenerimaanPemb.Tgl_jatuhTempo AS TGL_JATUHTEMPO, TblPenerimaanPemb.Jumlah AS JUMLAH, 
			// 			TblPenerimaanPemb.Status AS STATUS, TblPenerimaanPemb.Ket AS KET, 
			// 			isnull(TblPenerimaanPemb.Proses_BBT,'N') AS PROSES_BBT

			$content_html.= "<div class='div_body' style='overflow-x:padding-left:1px;'>";
			$content_html.= "	<table style='width:100%'>";
			

			$jml=count($datalaporan->data);
			for($i=0;$i<$jml;$i++) {
				
				if ( $xtipe_terima != $datalaporan->data[$i]->TYPE_TERIMA ) {
					
					// No Faktur
					if ($xnobukti != "!@#$%^&*" && $cetak_detail != "N" ) {						
						$content_html.= "		<tr><td colspan='1'></td>
												<td colspan='6'>".$xnofaktur."</td></tr>";	
					}

					// Total Rekening
					if ($xtipe_terima != "!@#$%^&*") {						
						$content_html.= "	<tr><td colspan='7' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>Total Tipe Terima : ".$xtipe_terima." </b></td>
											<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totaltipeterima)."</b></td>
											<td colspan='3' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td></tr>
											<tr><td>&nbsp;</td></tr>";	

						$totaltipeterima = 0;
					}

					// Sub tipeterima
					$content_html.= "		<tr><td colspan='9'><b>"."Tipe Terima : ".$datalaporan->data[$i]->TYPE_TERIMA."</b></td></tr>";
					
					// Header
					$content_html.= "	<tr>";
					$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO PENERIMAAN</td>";
					$content_html.= "		<td style='width:8%; border-bottom:thin solid #333; border-top:thin solid #333;'>TANGGAL</td>";
					$content_html.= "		<td style='width:20%; border-bottom:thin solid #333; border-top:thin solid #333;'>PELANGGAN</td>";
					$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>DIVISI</td>";
					$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>BANK</td>";
					$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO GIRO</td>";
					$content_html.= "		<td style='width:8%; border-bottom:thin solid #333; border-top:thin solid #333;'>TGL JT TEMPO </td>";
					$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;' align='right'>JUMLAH</td>";
					$content_html.= "		<td align='center' style='width:3%; border-bottom:thin solid #333; border-top:thin solid #333;'>STS</td>";					
					$content_html.= "		<td style='width:20%; border-bottom:thin solid #333; border-top:thin solid #333;'>KETERANGAN</td>";					
					$content_html.= "		<td align='center' style='width:4%; border-bottom:thin solid #333; border-top:thin solid #333;'>PROSES BBT</td>";
					$content_html.= "	</tr>";

					// nobukti						
					$content_html.= "			<tr><td>".$datalaporan->data[$i]->NO_PENERIMAAN."</td>";
					$content_html.= "			<td>".date("d-M-Y", strtotime($datalaporan->data[$i]->TGL_TRANS))."</td>";
					$content_html.= "			<td>".$datalaporan->data[$i]->NM_PLG."</td>";	
					$content_html.= "			<td>".$datalaporan->data[$i]->DIVISI."</td>";
					$content_html.= "			<td>".$datalaporan->data[$i]->BANK."</td>";
					$content_html.= "			<td>".$datalaporan->data[$i]->NO_GIRO."</td>";
					$content_html.= "			<td>".date("d-M-Y", strtotime($datalaporan->data[$i]->TGL_JATUHTEMPO))."</td>";
					$content_html.= "			<td align='right'>".number_format($datalaporan->data[$i]->JUMLAH)."</td>";
					$content_html.= "			<td align='center'>".$datalaporan->data[$i]->STATUS."</td>";					
					$content_html.= "			<td>".$datalaporan->data[$i]->KET."</td>";		
					$content_html.= "			<td align='center'>".$datalaporan->data[$i]->PROSES_BBT."</td>";
					$content_html.= "	</tr>";
	
					$xnofaktur = "";									
					
				}

				elseif ($xnobukti != $datalaporan->data[$i]->NO_PENERIMAAN) {
					
					if ($xnobukti != "!@#$%^&*" && $cetak_detail != "N" ) {						
						$content_html.= "		<tr><td colspan='1'></td>
												<td colspan='6'>".$xnofaktur."</td></tr>";	
					}

					$content_html.= "			<tr><td>".$datalaporan->data[$i]->NO_PENERIMAAN."</td>";
					$content_html.= "			<td>".date("d-M-Y", strtotime($datalaporan->data[$i]->TGL_TRANS))."</td>";
					$content_html.= "			<td>".$datalaporan->data[$i]->NM_PLG."</td>";	
					$content_html.= "			<td>".$datalaporan->data[$i]->DIVISI."</td>";
					$content_html.= "			<td>".$datalaporan->data[$i]->BANK."</td>";
					$content_html.= "			<td>".$datalaporan->data[$i]->NO_GIRO."</td>";
					$content_html.= "			<td>".date("d-M-Y", strtotime($datalaporan->data[$i]->TGL_JATUHTEMPO))."</td>";
					$content_html.= "			<td align='right'>".number_format($datalaporan->data[$i]->JUMLAH)."</td>";
					$content_html.= "			<td align='center'>".$datalaporan->data[$i]->STATUS."</td>";					
					$content_html.= "			<td>".$datalaporan->data[$i]->KET."</td>";		
					$content_html.= "			<td align='center'>".$datalaporan->data[$i]->PROSES_BBT."</td>";
					$content_html.= "	</tr>";

					$xnofaktur = "";
				}
				
				if ( $datalaporan->data[$i]->NO_FAKTUR != "" ){
					$xnofaktur .= rtrim($datalaporan->data[$i]->NO_FAKTUR). ";  ";
				}
				
				$gtotal += $datalaporan->data[$i]->JUMLAH;
				$totaltipeterima += $datalaporan->data[$i]->JUMLAH;

				$xtipe_terima = $datalaporan->data[$i]->TYPE_TERIMA;
				$xnobukti = $datalaporan->data[$i]->NO_PENERIMAAN;
			}

			if ($xnobukti != "!@#$%^&*" && $cetak_detail != "N" ) {						
				$content_html.= "		<tr><td colspan='1'></td>
										<td colspan='6'>".$xnofaktur."</td></tr>";	
			}
			
			// Total Rekening					
			$content_html.= "		<tr><td colspan='7' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>Total Tipe Terima : ".$xtipe_terima." </b></td>
									<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totaltipeterima)."</b></td>
									<td colspan='3' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td></tr>
									<tr><td>&nbsp;</td></tr>";	
			
			// GTotal
			$content_html.= "		<tr><td colspan='7' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>Grand Total</b></td>
									<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($gtotal)."</b></td>
									<td colspan='3' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td></tr>";
		
			$content_html.= "</table>";
			$content_html.= "</body></html>";

			// echo $content_html;
			$this->Pdf_Report($header_html, $content_html, "","","","","");

			// $data['title'] = $page_title;
			// $data['content_html'] = $content_html;
			// $this->RenderView('ReportFinanceBBTResult',$data);

		}



		public function ReportPenerimaan_Excel ( $page_title, $datalaporan, $periode, $judul, $wilayah2, $tipe_terima, $bank, $status, $cetak_detail ) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', $judul);
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', $periode);			
			$sheet->setCellValue('A3', 'Tipe Terima : '.$tipe_terima);
			$sheet->setCellValue('A4', $bank);
			$sheet->setCellValue('A5', $status);
			$sheet->setCellValue('A6', $wilayah2);
            								
			$currcol = 1;
			$currrow = 8;
						

			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TYPE TERIMA');
			$sheet->getColumnDimension('A')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL TRANS');
			$sheet->getColumnDimension('B')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NM PLG');
			$sheet->getColumnDimension('C')->setWidth(35);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO PENERIMAAN');
			$sheet->getColumnDimension('D')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DIVISI');
			$sheet->getColumnDimension('E')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'BANK');
			$sheet->getColumnDimension('F')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO GIRO');
			$sheet->getColumnDimension('G')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL JATUH TEMPO');
			$sheet->getColumnDimension('H')->setWidth(10);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'JUMLAH');
			$sheet->getColumnDimension('I')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'STATUS');
			$sheet->getColumnDimension('J')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KETERANGAN');
			$sheet->getColumnDimension('K')->setWidth(30);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PROSES BBT');
			$sheet->getColumnDimension('L')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;

			if ( $cetak_detail != "N" ) {
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO FAKTUR');
				$sheet->getColumnDimension('M')->setWidth(50);
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
				$currcol += 1;
			}

			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
						
			$xNO_BUKTI = "!@#$%^&*";
			$xNO_FAKTUR = "";
			// $xPROSES_BBT = "";
			
			// Detail
			$jum= count($datalaporan->data);
			for($i=0; $i<$jum; $i++){
				
				if ( $xNO_BUKTI != $datalaporan->data[$i]->NO_PENERIMAAN ) {

					// No Faktur
					if ( $xNO_BUKTI = "!@#$%^&*" ) {
						if ( $cetak_detail != "N" ) {
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $xNO_FAKTUR);
							$currcol += 1;

							$xNO_FAKTUR = "";
						}
					}
				
					$currrow++;
					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->TYPE_TERIMA);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("m-d-Y",strtotime($datalaporan->data[$i]->TGL_TRANS)));
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NM_PLG);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_PENERIMAAN);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->DIVISI);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->BANK);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_GIRO);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("m-d-Y",strtotime($datalaporan->data[$i]->TGL_JATUHTEMPO)));
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->JUMLAH));
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$currcol += 1;				
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->STATUS);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->KET);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->PROSES_BBT);
					$currcol += 1;
				}

				if ( $cetak_detail != "N" && $datalaporan->data[$i]->NO_FAKTUR != "" ) {
					$xNO_FAKTUR .= rtrim($datalaporan->data[$i]->NO_FAKTUR). ";  ";					
				}
				
				$xNO_BUKTI = $datalaporan->data[$i]->NO_PENERIMAAN;
			}
			
			// print_r ($judul);

			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A8:'.$max_col.'9')->getAlignment()->setHorizontal($alignment_center);
			
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A8:'.$max_col.'9')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
			// border
			$sheet->getStyle("A1:".$max_col."9")->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet->getStyle('A8:'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');
			
				
			$filename= $judul.' '.$status. ' ['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 
			exit();

		}

    }