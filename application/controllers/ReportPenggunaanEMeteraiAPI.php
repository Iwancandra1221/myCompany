<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require 'vendor/autoload.php';
require 'vendor/setasign/fpdi/src/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportPenggunaanEMeteraiAPI extends NS_Controller 
{
	public $pdf_flag = 0;
	public $confirm_flag=0;
	public $excel_flag = 0;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('HelperModel');
		$this->load->helper('FormLibrary');
		$this->load->library('email');
		$this->load->library('excel');
		$this->load->helper('directory');
		$this->load->helper('file');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function index()
	{
		$data = array();
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}		
		$data['title'] = 'MY COMPANY | FINANCE | REPORT PENGGUNAAN EMETERAI';
		$data["err"] = "";

		$this->RenderView('ReportPenggunaanEMeteraiForm',$data);
	}

	public function Confirm()
	{
		$data = array();
		$this->confirm_flag = 1;
		$this->pdf_flag=1;
		$p_tgl1 = $this->input->get("dp1");
		$p_tgl2 = $this->input->get("dp2");
		//die("here");
		$this->Proses_PenggunaanEMeterai("", $p_tgl1, $p_tgl2, "PERIODE");
	}

	public function JobSendEMeterai()
	{
		$data = array();
		$this->confirm_flag = 1;		
		$this->pdf_flag=1;

		$p_tgl1 = $this->input->get("dp1");
		$p_tgl2 = $this->input->get("dp2");
		//die("here");
		//$this->Proses_PenggunaanEMeterai("", $p_tgl1, $p_tgl2, "PERIODE");
		
		$api = 'APITES';
		$url = urldecode($this->input->get("url"));
		$svr = urldecode($this->input->get("svr"));
		$db  = urldecode($this->input->get("db"));
		$lok = urldecode($this->input->get("lok"));

		set_time_limit(60);
		$url = $url.API_BKT."/ReportFinance/ReportPenggunaanEMeterai?api=".urlencode($api)
			."&tgl1=".urlencode($p_tgl1)."&tgl2=".urlencode($p_tgl2)
			."&svr=".urlencode($svr)."&db=".urlencode($db);
		//die($url);
		$GetData = json_decode(file_get_contents($url), true);

		if ($GetData["result"]=="sukses") {
			$this->Preview_PenggunaanEMeterai("", $p_tgl1, $p_tgl2, $GetData["data"], "JOB", $lok);
			$hasil = json_encode("SUKSES");
			header('HTTP/1.1: 200');
			header('Status: 200');
			header('Content-Length: '.strlen($hasil));
			exit($hasil);	
		} else {
			$hasil = json_encode("AMBIL DATA DARI BHAKTI GAGAL");
			header('HTTP/1.1: 200');
			header('Status: 200');
			header('Content-Length: '.strlen($hasil));
			exit($hasil);	
		}		
	}

	public function Proses()
	{
		$data = array();
		$page_title = 'Report Finance';
		$this->confirm_flag = 0;
		if (isset($_POST["btnPdf"])) {
			$this->pdf_flag = 1;
		} else {
			$this->pdf_flag = 0;
		}
		if (isset($_POST["btnExcel"])) {
			$this->excel_flag = 1;
		} else {
			$this->excel_flag = 0;
		}

		$opt = $_POST["ReportOption"];

		if ($opt=="TANGGAL") {
			$dp1 = $_POST["dp1"];
			$dp2 = $_POST["dp2"];

			$this->load->library('form_validation');
			$this->form_validation->set_rules('dp1','Tanggal Awal','required');
			$this->form_validation->set_rules('dp2','Tanggal Akhir','required');

		} else {
			if ($_POST["Periode"]=="04") {
				$dp1 = $_POST["Bulan"]."/"."01"."/".$_POST["Tahun"];

				$tgl = new DateTime($_POST["Tahun"]."-".$_POST["Bulan"]."-"."01");
				$tgl->modify("+1 month");
				$tgl->modify("-1 day");				
				$dp2 = $tgl->format("Y-m-d");
			} else if ($_POST["Periode"]=="01") {
				$dp1 = $_POST["Bulan"]."/"."01"."/".$_POST["Tahun"];
				$dp2 = $_POST["Bulan"]."/"."10"."/".$_POST["Tahun"];
			} else if ($_POST["Periode"]=="02") {
				$dp1 = $_POST["Bulan"]."/"."11"."/".$_POST["Tahun"];
				$dp2 = $_POST["Bulan"]."/"."20"."/".$_POST["Tahun"];
			} else {
				$tgl = new DateTime($_POST["Tahun"]."-".$_POST["Bulan"]."-"."01");
				$tgl->modify("+1 month");
				$tgl->modify("-1 day");
				
				$dp1 = $_POST["Bulan"]."/"."21"."/".$_POST["Tahun"];
				$dp2 = $tgl->format("Y-m-d");
			}

			$this->load->library('form_validation');
			$this->form_validation->set_rules('Tahun','Tahun','required');
			$this->form_validation->set_rules('Bulan','Bulan','required');
			$this->form_validation->set_rules('Periode','Periode','required');
		}

		if($this->form_validation->run()) {
			$this->Proses_PenggunaanEMeterai($page_title, $dp1, $dp2, $opt);
		} else  {
			redirect("ReportPenggunaanEMeterai");
		}
	}

	public function Proses_PenggunaanEMeterai($page_title, $p_tgl1, $p_tgl2, $opt)
	{
		$api = 'APITES';
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}
		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;
		//$pwd = SQL_PWD;
		
		set_time_limit(60);
		$url = $url.API_BKT."/ReportFinance/ReportPenggunaanEMeterai?api=".urlencode($api)
			."&tgl1=".urlencode($p_tgl1)."&tgl2=".urlencode($p_tgl2)
			."&svr=".urlencode($svr)."&db=".urlencode($db);
		// die($url);

		$GetData = json_decode(file_get_contents($url), true);
		if ($GetData["result"]=="sukses") {
			$this->Preview_PenggunaanEMeterai($opt, $p_tgl1, $p_tgl2, $GetData["data"], $_SESSION["logged_in"]["useremail"], $_SESSION["conn"]->BranchId);
		} else {
			die($GetData["error"]);
		}
	}

	public function Preview_PenggunaanEMeterai($opt, $p_tgl1, $p_tgl2, $data, $user, $lok="") 
	{
		$style_col_ganjil ="float:left;line-height:15px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#ffffcc;font-size:10px;";
		$style_col_genap = "float:left;line-height:15px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#fff;font-size:10px;";
		$style_header= "float:left;min-height:20px;line-height:15px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#cccccc;font-size:10px;";
		$style_footer= "float:left;min-height:20px;line-height:20px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#cccccc;font-size:10px;";

		$kanan = "text-align:right;padding-right:5px;";
		$kiri  = "text-align:left; padding-left: 5px;";
		$center= "text-align:center;";

		$header_html = "<div style='clear:both;height:25px;'></div>";
		$header_html.= "<div id='div_header' style='padding-left:10px;'>";
		$header_html.= "	<div><h2>LAPORAN PEMAKAIAN BEA METERAI ELEKTRONIK</h2></div>";
		//$header_html.= "	<div><b>Wilayah : ".$p_wil."</b></div>";
		$header_html.= "	<div><b>Periode : ".date("d-M-Y", strtotime($p_tgl1))." - ".date("d-M-Y", strtotime($p_tgl2))."</b></div>";
		$header_html.= "</div>";	//close div_header

		$TAHUN = date("Y", strtotime($p_tgl1));
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);
		$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
		if($this->excel_flag == 1){
			//$this->excel->setActiveSheetIndex(0);
			$sheet->setTitle('PemakaianBeaEMeterai');
			$sheet->setCellValue('A1', 'LAPORAN PEMAKAIAN BEA METERAI ELEKTRONIK');
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->mergeCells('A1:J1');
			$sheet->setCellValue('A2', 'Periode : '.date("d-M-Y", strtotime($p_tgl1))." - ".date("d-M-Y", strtotime($p_tgl2)));
			$sheet->getStyle("A1:A2")->getFont()->setBold(true);
			$sheet->mergeCells('A2:J2');
			$currrow = 2;
		}

		$style_col = $style_col_genap;
		
		$group_header = "	<tr style='background-color:#ffe53b;'>";
		$group_header.= "		<th width='3%;' class='td-center'>NO</th>";
		$group_header.= "		<th width='3%;'><b>LOK</b></th>";
		$group_header.= "		<th width='10%;'><b>WILAYAH</b></th>";
		$group_header.= "		<th width='12%;'><b>No Bukti</b></th>";
		$group_header.= "		<th width='8%;'><b>Tgl Trans</b></th>";
		$group_header.= "		<th width='6%;'><b>Kode</b></th>";
		$group_header.= "		<th width='18%;'><b>Nama Pelanggan</b></th>";
		$group_header.= "		<th width='8%;'><b>Ket</b></th>";
		$group_header.= "		<th width='8%;' class='td-right'><b>Total Trans</b></th>";
		
		if($TAHUN<2021){
			$group_header.= "		<th width='4%;' class='td-center'><b>3000</b></th>";
			$group_header.= "		<th width='4%;' class='td-center'><b>6000</b></th>";
		}
		
		$group_header.= "		<th width='5%;' class='td-right'><b>Meterai</b></th>";
		
		
		$group_header.= "	</tr>";	//close div_column_header

		$IJIN = "";
		$LOK = "";
		$WILAYAH = "";

		$PLG = "";
		$NM_PLG = "";
		$KD_PLG = "";
		$MARKER = "";
		$TOT_PLG = 0;
		$PEN_PLG = 0;
		$SISA_PLG= 0;

		$TAGIHAN = "";
		$NO_BUKTI = "";
		$TGL_TRANS = "";
		$TGL_JT = "";
		$TOTAL_TRANS = 0;
		
		$METERAI_3000=0;
		$METERAI_6000=0;
		$METERAI_10000=0;

		$TOTAL_EMETERAI = 0;
		$EMETERAI_COUNTER = 0;

		$content_html = "<style>";
		$content_html.= "	td, th { border:1px solid #ccc; padding:2px!important; }";
		$content_html.= "	.td-center { text-align: center; }";
		$content_html.= "	.td-right { text-align:right;}";
		$content_html.= "</style>";
		$content_html.= "<div class='div_body' style='font-size:9pt!important;'>";

		$TABLESTART = 0;
		$TABLEEND = 0;

		for($i=0;$i<count($data);$i++)
		{
			$n = $i+1;

			if ($i%2==1)
				$style_col = $style_col_genap;
			else
				$style_col = $style_col_ganjil;

			if (trim($IJIN)==trim($data[$i]["IJINEMETERAI"])) {

				$height = 20;

				$ket = (($data[$i]["TYPE_TRANS"]=="CASH BEFORE")? "CASH BEFORE" : (($data[$i]["TYPE_TRANS"]=="INVOICE")? $data[$i]["NO_REF"] : "JT : ".date("d-M",strtotime($data[$i]["TGL_JATUHTEMPO"]))));

				$content_html.= "	<tr>";
				$content_html.= "		<td class='td-center'>".$n."</td>";
				$content_html.= "		<td>".$data[$i]["KD_LOKASI"]."</td>";
				$content_html.= "		<td>".$data[$i]["WILAYAH"]."</td>";
				$content_html.= "		<td>".$data[$i]["NO_BUKTI"]."</td>";
				$content_html.= "		<td>".date("d-M-Y", strtotime($data[$i]["TGL_TRANS"]))."</td>";
				$content_html.= "		<td>".$data[$i]["KD_PLG"]."</td>";
				$content_html.= "		<td>".trim($data[$i]["NM_PLG"])."</td>";
				$content_html.= "		<td>".$ket."</td>";
				$content_html.= "		<td class='td-right'>".number_format($data[$i]["TOTAL_BUKTI"])."</td>";
				
				if($TAHUN<2021){
					$content_html.= "	<td class='td-center'>".(($data[$i]["EMETERAI3000"]==0)?"&nbsp;":"x")."</td>";
					$content_html.= "	<td class='td-center'>".(($data[$i]["EMETERAI6000"]==0)?"&nbsp;":"x")."</td>";
				}
				
				$content_html.= "		<td class='td-right'>".number_format($data[$i]["EMETERAI_VALUE"])."</td>";
				$content_html.= "	</tr>";

				if ($this->excel_flag==1) {
					if ("CONTENTROW"=="CONTENTROW") {
						$currcol = 1;
						$currrow+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $n);
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["KD_LOKASI"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["WILAYAH"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["NO_BUKTI"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["TGL_TRANS"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["KD_PLG"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["NM_PLG"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $ket);
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $data[$i]["TOTAL_BUKTI"]);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol+= 1;
						
						if($TAHUN<2021){
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, (($data[$i]["EMETERAI3000"]==0)?"":"x"));
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setHorizontal($alignment_center);
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, (($data[$i]["EMETERAI6000"]==0)?"":"x"));
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setHorizontal($alignment_center);
							$currcol+= 1;
						}
						
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $data[$i]["EMETERAI_VALUE"]);		
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');				
					}
				}

				$EMETERAI_COUNTER += 1;
				$TOTAL_EMETERAI += $data[$i]["EMETERAI_VALUE"];
				if ($data[$i]["EMETERAI_VALUE"]==3000) {
					$METERAI_3000 += 1;
				}
				else if ($data[$i]["EMETERAI_VALUE"]==6000){
					$METERAI_6000 += 1;
				}
				else{
					$METERAI_10000 += 1;
				}
			} else {

				if ($IJIN!="") {
					if ($this->confirm_flag==1) {
						// $url = $this->API_URL."/Billing/SaveEMeteraiUsage?lok=".urlencode($LOK)."&ijin=".urlencode($IJIN)
							// ."&tgl1=".urlencode(date("m/d/Y",strtotime($p_tgl1)))."&tgl2=".urlencode(date("m/d/Y",strtotime($p_tgl2)))
							// ."&total=".urlencode($TOTAL_EMETERAI)."&met3=".urlencode($METERAI_3000)."&met6=".urlencode($METERAI_6000)."&tipe=".urlencode("KWITANSI TAGIHAN")
							// ."&user=".urlencode($user);
						$url = $this->API_URL."/Billing/SaveEMeteraiUsage?lok=".urlencode($LOK)."&ijin=".urlencode($IJIN)
							."&tgl1=".urlencode(date("m/d/Y",strtotime($p_tgl1)))."&tgl2=".urlencode(date("m/d/Y",strtotime($p_tgl2)))
							."&total=".urlencode($TOTAL_EMETERAI)."&met3=".urlencode($METERAI_3000)."&met6=".urlencode($METERAI_6000)."&met10=".urlencode($METERAI_10000)."&tipe=".urlencode("KWITANSI TAGIHAN")
							."&user=".urlencode($user);
						// die($url);
						$simpanData = json_decode(file_get_contents($url), true);
					}

					$group_footer = "	<tr style='background-color:#ffe53b;'>";
					$group_footer.= "		<td>&nbsp;</td>";
					$group_footer.= "		<td>&nbsp;</td>";
					$group_footer.= "		<td>&nbsp;</td>";
					$group_footer.= "		<td>&nbsp;</td>";
					$group_footer.= "		<td>&nbsp;</td>";
					$group_footer.= "		<td><b>TOTAL</b></td>";
					$group_footer.= "		<td><b>".$IJIN."</b></td>";
					
					if($TAHUN<2021){
						$group_footer.= "	<td>&nbsp;</td>";
						$group_footer.= "	<td>&nbsp;</td>";
						$group_footer.= "	<td class='td-center'><b>".$METERAI_3000."</b></td>";
						$group_footer.= "	<td class='td-center'><b>".$METERAI_6000."</b></td>";
					}
					else{
						$group_footer.= "	<td class='td-center'><b>".$METERAI_10000."</b></td>";
						$group_footer.= "	<td>&nbsp;</td>";
					}					
					
					$group_footer.= "		<td class='td-right'><b>".number_format($TOTAL_EMETERAI)."</b></td>";
					$group_footer.= "	</tr>";	//close div_column_header
					$group_footer.= "</table>";	

					if ($this->excel_flag==1) {
						$currcol = 6;
						$currrow+= 1;
						$TABLEEND = $currrow;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, "TOTAL");
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $IJIN);
						$currcol+= 1;
			
						if($TAHUN<2021){
							$currcol+= 2;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $METERAI_3000);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setHorizontal($alignment_center);
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $METERAI_6000);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setHorizontal($alignment_center);
							$currcol+= 1;
						}
						else{
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $METERAI_10000);
							$currcol+= 2;
						}
						
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL_EMETERAI);	
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);

						if ($TABLESTART!=0 && $TABLEEND!=0) {
							$max_col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol+1);
							// for ($x = 'A'; $x != 'K'; $x++) {
							for ($x = 'A'; $x != $max_col; $x++) {
								for($y=$TABLESTART;$y<=$TABLEEND;$y++) {
									$cell = $x.$y;
									$sheet->getStyle($cell)
									    ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
									$sheet->getStyle($cell)
									    ->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
									$sheet->getStyle($cell)
									    ->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
									$sheet->getStyle($cell)
									    ->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
								}
							}			
							$TABLESTART = 0;
							$TABLEEND = 0;
						}						

					}

					$content_html.= $group_footer;					
				}

				$ket = (($data[$i]["TYPE_TRANS"]=="CASH BEFORE")? "CASH BEFORE" : (($data[$i]["TYPE_TRANS"]=="INVOICE")? $data[$i]["NO_REF"] : "JT : ".date("d-M",strtotime($data[$i]["TGL_JATUHTEMPO"]))));

				$height = 20;
				$content_html.= "	<div style='clear:both;height:15px;'></div>";
				$content_html.= "	<div id='div_column_header' style='width:95%!important;line-height:50px;vertical-align:middle;'>";
				$content_html.= "		<div style='width:30%;height:".$height."px;float:left;".$kiri."'>IJIN PEMBUBUHAN BEA METERAI :</div>";
				$content_html.= "		<div style='width:65%;height:".$height."px;float:left;".$kiri."'><b>".$data[$i]["IJINEMETERAI"]."</b></div>";
				$content_html.= "	</div>";
				$content_html.= "	<div style='clear:both;height:15px;'></div>";
				$content_html.= "	<table>";
				$content_html.= $group_header;
				$content_html.= "	<tr>";
				$content_html.= "		<td class='td-center'>".$n."</td>";
				$content_html.= "		<td>".$data[$i]["KD_LOKASI"]."</td>";
				$content_html.= "		<td>".$data[$i]["WILAYAH"]."</td>";
				$content_html.= "		<td>".$data[$i]["NO_BUKTI"]."</td>";
				$content_html.= "		<td>".date("d-M-Y", strtotime($data[$i]["TGL_TRANS"]))."</td>";
				$content_html.= "		<td>".$data[$i]["KD_PLG"]."</td>";
				$content_html.= "		<td>".trim($data[$i]["NM_PLG"])."</td>";
				$content_html.= "		<td>".$ket."</td>";
				$content_html.= "		<td class='td-right'>".number_format($data[$i]["TOTAL_BUKTI"])."</td>";
				
				if($TAHUN<2021){
					$content_html.= "		<td class='td-center'>".(($data[$i]["EMETERAI3000"]==0)?"":"x")."</td>";
					$content_html.= "		<td class='td-center'>".(($data[$i]["EMETERAI6000"]==0)?"":"x")."</td>";
				}
				
				
				$content_html.= "		<td class='td-right'>".number_format($data[$i]["EMETERAI_VALUE"])."</td>";
				$content_html.= "	</tr>";
	

				if($this->excel_flag == 1){
					if ("HEADERLABEL"=="HEADERLABEL") {
						$currcol = 1;
						$currrow+= 3;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'IJIN PEMBUBUHAN BEA METERAI :');
						$sheet->mergeCells('A'.$currrow.':C'.$currrow);
						$currcol = 4;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $data[$i]["IJINEMETERAI"]);
						$sheet->mergeCells('D'.$currrow.':E'.$currrow);
						$sheet->getStyle("A".$currrow.":E".$currrow)->getFont()->setBold(true);
					}
					if ("HEADERROW"=="HEADERROW") {
						$currcol = 1;
						$currrow+= 1;
						$TABLESTART = $currrow;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Lok');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Wilayah');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NoBukti');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TglTrans');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NamaPelanggan');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Ket');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TotalTrans');
						$currcol+= 1;
						if($TAHUN<2021){
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, '3000');
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, '6000');
							$currcol+= 1;
						}
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Meterai');
						$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
					}
					if ("CONTENTROW"=="CONTENTROW") {
						$currcol = 1;
						$currrow+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $n);
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["KD_LOKASI"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["WILAYAH"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["NO_BUKTI"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["TGL_TRANS"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["KD_PLG"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["NM_PLG"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $ket);
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $data[$i]["TOTAL_BUKTI"]);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol+= 1;
						
						if($TAHUN<2021){
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, (($data[$i]["EMETERAI3000"]==0)?"":"x"));
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setHorizontal($alignment_center);
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, (($data[$i]["EMETERAI6000"]==0)?"":"x"));
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setHorizontal($alignment_center);
							$currcol+= 1;	
						}
						
						
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $data[$i]["EMETERAI_VALUE"]);	
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');					
					}
				}
				
				$IJIN = trim($data[$i]["IJINEMETERAI"]);
				$LOK = trim($data[$i]["KD_LOKASI"]);

				$EMETERAI_COUNTER = 1;
				$TOTAL_EMETERAI = $data[$i]["EMETERAI_VALUE"];
				if ($data[$i]["EMETERAI_VALUE"]==3000) {
					$METERAI_3000 = 1;
					$METERAI_6000 = 0;
					$METERAI_10000 = 0;
				} elseif ($data[$i]["EMETERAI_VALUE"]==6000) {
					$METERAI_3000 = 0;
					$METERAI_6000 = 1;
					$METERAI_10000 = 0;
				}
				else{
					$METERAI_3000 = 0;
					$METERAI_6000 = 0;
					$METERAI_10000 = 1;
				}
			}
		}

		if ($IJIN!="" && $this->confirm_flag==1) {		
			// $url = $this->API_URL."/Billing/SaveEMeteraiUsage?lok=".urlencode($LOK)."&ijin=".urlencode($IJIN)
				// ."&tgl1=".urlencode(date("m/d/Y",strtotime($p_tgl1)))."&tgl2=".urlencode(date("m/d/Y",strtotime($p_tgl2)))
				// ."&total=".urlencode($TOTAL_EMETERAI)."&met3=".urlencode($METERAI_3000)."&met6=".urlencode($METERAI_6000)."&tipe=".urlencode("KWITANSI TAGIHAN")
				// ."&user=".urlencode($user);	
			$url = $this->API_URL."/Billing/SaveEMeteraiUsage?lok=".urlencode($LOK)."&ijin=".urlencode($IJIN)
				."&tgl1=".urlencode(date("m/d/Y",strtotime($p_tgl1)))."&tgl2=".urlencode(date("m/d/Y",strtotime($p_tgl2)))
				."&total=".urlencode($TOTAL_EMETERAI)."&met3=".urlencode($METERAI_3000)."&met6=".urlencode($METERAI_6000)."&met10=".urlencode($METERAI_10000)."&tipe=".urlencode("KWITANSI TAGIHAN")
				."&user=".urlencode($user);
			//die($url);
			$simpanData = json_decode(file_get_contents($url), true);
		}

		$group_footer = "	<tr style='background-color:#ffe53b;'>";
		$group_footer.= "		<td>&nbsp;</td>";
		$group_footer.= "		<td>&nbsp;</td>";
		$group_footer.= "		<td>&nbsp;</td>";
		$group_footer.= "		<td>&nbsp;</td>";
		$group_footer.= "		<td>&nbsp;</td>";
		$group_footer.= "		<td><b>TOTAL</b></td>";
		$group_footer.= "		<td><b>".$IJIN."</b></td>";
		
		if($TAHUN<2021){
			$group_footer.= "		<td>&nbsp;</td>";
			$group_footer.= "		<td>&nbsp;</td>";
			$group_footer.= "		<td class='td-center'><b>".$METERAI_3000."</b></td>";
			$group_footer.= "		<td class='td-center'><b>".$METERAI_6000."</b></td>";
		}
		else{
			$group_footer.= "		<td class='td-center'><b>".$METERAI_10000."</b></td>";
			$group_footer.= "		<td>&nbsp;</td>";
		}
		
		$group_footer.= "		<td class='td-right'><b>".number_format($TOTAL_EMETERAI)."</b></td>";
		$group_footer.= "	</tr>";	//close div_column_header
		$group_footer.= "</table>";

		if ($this->excel_flag==1) {
			$currcol = 6;
			$currrow+= 1;
			$TABLEEND = $currrow;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "TOTAL");
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $IJIN);
			$currcol+= 1;
			
			if($TAHUN<2021){
				$currcol+= 2;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $METERAI_3000);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setHorizontal($alignment_center);
				$currcol+= 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $METERAI_6000);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setHorizontal($alignment_center);
				$currcol+= 1;
			}
			else{
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $METERAI_10000);
				$currcol+= 2;
			}
			
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL_EMETERAI);	
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);				
			
			if ($TABLESTART!=0 && $TABLEEND!=0) {
				// for ($x = 'A'; $x != 'K'; $x++) {
				$max_col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol+1);
				for ($x = 'A'; $x != $max_col; $x++) {
					for($y=$TABLESTART;$y<=$TABLEEND;$y++) {
						$cell = $x.$y;
						$sheet->getStyle($cell)
						    ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$sheet->getStyle($cell)
						    ->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$sheet->getStyle($cell)
						    ->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$sheet->getStyle($cell)
						    ->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
					}
				}			
				$TABLESTART = 0;
				$TABLEEND = 0;
			}
		}

		$content_html.= $group_footer;
		$content_html.= "</div>";

		//die($content_html);
		$th = date("Y", strtotime($p_tgl1));
		$bl = date("m", strtotime($p_tgl1));
		if ($this->excel_flag==1) {
			for ($i = 'A'; $i != 'J'; $i++) {
			    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
			}

			$filename='PemakaianEMeterai['.$lok.']['.$th.']['.$bl.']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
		}

		if ($this->confirm_flag==1) {
			if ($this->pdf_flag==1) {
				$this->Pdf_Report($header_html, $content_html, "", $p_tgl1, $p_tgl2, $user, $lok);
			} else if ($this->excel_flag==1) {
		 		$main_dir = "D:/";
				$pdf_dir = $main_dir."/Report/EMeterai/".$th;
				$nm_file = "pemakaian_emeterai_".$lok."_".$th."_".$bl."_".date("d",strtotime($tgl1)).date("d",strtotime($tgl2)).".xlsx";
		        //Jika folder save belum ada maka create dahulu
		        if (!is_dir($pdf_dir)) {
					mkdir($pdf_dir, 0777, TRUE);	
				}
				$namafile = $pdf_dir."/".$nm_file;
		        $writer->save($namafile);	// download file 

				$email_content = $user." mengirimkan Laporan Pemakaian Meterai Elektronik<br>";
				$email_content.= "Periode : ".date("d-M-Y",strtotime($tgl1))." s/d ".date("d-M-Y", strtotime($tgl2));
				$subject = "Report EMeterai [".$lok."][".$th."][".$bl."][".date("d",strtotime($tgl1))."-".date("d",strtotime($tgl2))."]";
				$this->EmailReport($namafile, $subject, $email_content);
				unlink($namafile);
			}
		} else if ($this->pdf_flag==1) {
			$this->Pdf_Report($header_html, $content_html, "","","","",$lok);
		} else if ($this->excel_flag==1) {
	        $writer->save('php://output');	// download file 
	        exit();			
		} else {
			$row_btn = "";

			if ($opt=="PERIODE") {
				if ($_SESSION['logged_in']["branch_id"]=="JKT") {
					$row_btn.= "<form action='Confirm'>";
					$row_btn.= '<input type="hidden" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1" value="'.date("m/d/Y",strtotime($p_tgl1)).'">';
					$row_btn.= '<input type="hidden" class="form-control" id="dp2" placeholder="mm/dd/yyyy" name="dp2" value="'.date("m/d/Y",strtotime($p_tgl2)).'">';
					$row_btn.= '<input type="submit" value="Confirm">';
					$row_btn.= '</form>';
				}
			}
			
			$view['title'] = "Report Pemakaian Meterai Elektronik";
			$view['content_html'] = $row_btn.$header_html.$content_html;
	        $this->SetTemplate('template/notemplate');
			$this->RenderView('ReportFinanceResult',$view);
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
					/*'default_font_size' => 8,*/
					'default_font' => 'tahoma',
					'margin_left' => 10,
					'margin_right' => 10,
					'margin_top' => 30,
					'margin_bottom' => 10,
					'margin_header' => 0,
					'margin_footer' => 0,
					'orientation' => 'L'
				));

		$mpdf->SetHTMLHeader($header);				//Yang diulang di setiap awal halaman  (Header)
        $mpdf->WriteHTML(utf8_encode($content));

        if ($this->confirm_flag==1) {
		
	 		$main_dir = "D:/";
	 		$th = date("Y", strtotime($tgl1));
	 		$bl = date("m", strtotime($tgl1));
			$pdf_dir = $main_dir."/Report/EMeterai/".$th;
			$nm_file = "pemakaian_emeterai_".$lok."_".$th."_".$bl."_".date("d",strtotime($tgl1)).date("d",strtotime($tgl2)).".pdf";
	        //Jika folder save belum ada maka create dahulu
	        if (!is_dir($pdf_dir)) {
				mkdir($pdf_dir, 0777, TRUE);	
			}
			$namafile = $pdf_dir."/".$nm_file;
	        $mpdf->Output($namafile, \Mpdf\Output\Destination::FILE);

			$email_content = $user." mengirimkan Laporan Pemakaian Meterai Elektronik ".$lok."<br>";
			$email_content.= "Periode : ".date("d-M-Y",strtotime($tgl1))." s/d ".date("d-M-Y", strtotime($tgl2));
			$subject = "Report EMeterai [".$lok."][".$th."][".$bl."][".date("d",strtotime($tgl1))."-".date("d",strtotime($tgl2))."]";
			$this->EmailReport($namafile, $subject, $email_content);
			unlink($namafile);
	    } else {
	        $mpdf->Output();
	    }
	}

	public function EmailReport($filename, $subject, $content)
	{
		$this->email->clear(true);
		$this->email->from("bhaktiautoemail.noreply@bhakti.co.id", "BHAKTI.CO.ID AUTO-EMAIL");
		$this->email->to("kasir@bhakti.co.id");
		$this->email->cc("hadianto@bhakti.co.id");
		$this->email->bcc("bhaktiautoemail.noreply@bhakti.co.id");
		$this->email->attach($filename);
		$this->email->subject($subject);
		$this->email->message($content);
		if ($this->email->send()) {
			$data["content_html"] = '<script language="javascript">';
	        $data["content_html"].= 'alert("Data Confirmed and Email Sent")';
	        $data["content_html"].= '</script>';
	        $data["content_html"].= "<script>window.close();</script>";
		} else {
			$data["content_html"] = '<script language="javascript">';
	        $data["content_html"].= 'alert("Email Not Sent")';
	        $data["content_html"].= '</script>';
	        $data["content_html"].= "<script>window.close();</script>";
		}
	    $this->RenderView("CustomPageResult", $data);
	}

	public function Get()
	{
		$post = $this->PopulatePost();
		$th = $post["Tahun"];
		$bl = $post["Bulan"];
		$cbg= $_SESSION["conn"]->BranchId;

		$files = array();
	    $pdf_dir = "D:/Report/EMeterai/".$th;

	    if (is_dir($pdf_dir)) {
			$dir = directory_map($pdf_dir);

			while (($subdir_name = current($dir)) !== FALSE) {
			$content_key = key($dir);

			if (is_array($dir[$content_key])) {
			  //echo $content_key." : folder<br />";
			} else {
				$filename = $dir[$content_key];
	
				if ($cbg=="JKT") {
					if (substr($filename,23,4)==$th && substr($filename,28,2)==$bl) {
						array_push($files, $filename);
					}
				} else {
					$filename = $dir[$content_key];
					if (substr($filename,19,3)==$e->NO_FAKTURP_2) {
					$this->zip->read_file($efk_dir."/".$dir[$content_key]);
					$FP_found = true;
					}
				}
			}
			next($dir);
			}
	    } else {
	    }

	    $data["pdf_dir"] = $pdf_dir;
	    $data["files"] = $files;


		if(isset($post['EmployeeID']))
		{
			$e = $this->EmployeeModel->Get($post['EmployeeID']);
			if($e != null)
			{
				echo json_encode($e);
			}
			else
				echo json_encode(array('error'=>'Invalid Request'));
		}
		else
			echo json_encode(array('error'=>'Invalid Request'));
	}
	

}