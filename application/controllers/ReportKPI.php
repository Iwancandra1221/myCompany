<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require 'vendor/autoload.php';
require 'vendor/setasign/fpdi/src/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportKPI extends MY_Controller 
{
	public $pdf_flag = 0;
	public $confirm_flag=0;
	public $excel_flag = 0;

	public $currrow = array();
	// public $currcol = array();
	public $tableStart = array();
	public $tableEnd = array();
	public $NO = array();
	public $MONTH = array("JANUARI", "FEBRUARI", "MARET", "APRIL", "MEI", "JUNI", "JULI", "AGUSTUS", "SEPTEMBER", "OKTOBER", "NOVEMBER", "DESEMBER");

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
	function _decodeGzip($str){
	    // Try to decode the data
	    $decodedData = @gzdecode($str);

	    // Check if decoding was successful
	    if ($decodedData !== false) {
	        return $decodedData; // The string is gzip-encoded

	    } else {
	        return $str; // The string is not gzip-encoded
	    }
	}
	public function MonitoringAchievement()
	{
		$data = array();
		$url = $this->API_URL."/ReportKPI/GetKPICategories?api=".urlencode("APITES");
		// die($url);
		$GetData = json_decode(HttpGetRequest($url, $this->API_URL, "Ambil Kategori KPI"),true);
		// die(json_encode($GetData["data"]));
		$data['title'] = 'MONITORING ACHIEVEMENT KPI | '.WEBTITLE;
		$data['categories'] = $GetData["data"];
		$data['err'] = '';

		$paramsLog = array();   
	 	$paramsLog['LogDate'] = date("Y-m-d H:i:s");
	  	$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
	  	$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
	  	$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MONITORING ACHIEVEMENT KPI"; 
	 	$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." MEMBUKA MONITORING ACHIEVEMENT KPI";
	 	$paramsLog['Remarks']="SUCCESS";
	  	$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($paramsLog); 

		$this->RenderView('ReportMonitoringKPIForm',$data);
	}

	public function Proses($report="MonitoringAchievement")
	{
		$data = array();
		$page_title = 'Report KPI';
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

		$params = array();
		$params["tahun"] = $_POST["Tahun"];
		$params["bulan"] = $_POST["Bulan"];
		$params["kategori"] = $_POST["Category"];

		$this->load->library('form_validation');
		$this->form_validation->set_rules('Tahun','Tahun','required');
		$this->form_validation->set_rules('Bulan','Bulan','required');

		if($this->form_validation->run()) {
			$this->Proses_MonitoringAchievement($page_title, $params);
		} else if (strtoupper($report)=="MONITORINGACHIEVEMENT")  {
			redirect("ReportKPI/MonitoringAchievement");
		} else {

		}
	}

	public function Proses_MonitoringAchievement($page_title, $params)
	{
		
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MONITORING ACHIEVEMENT KPI"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." PROSES EXPORT EXCEL MONITORING ACHIEVEMENT KPI";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog); 

		$api = 'APITES';
		set_time_limit(60);
		$url = $this->API_URL."/ReportKPI/ReportMonitoringAchievement?api=APITES&thn=".urlencode($params["tahun"]).
				"&bln=".urlencode($params["bulan"])."&kategori=".urlencode($params["kategori"]);
		// die($url);
		$response = HttpGetRequest($url, $this->API_URL, "Ambil Data Achievement KPI", 300000);
		$response = $this->_decodeGzip($response);
		$GetData = json_decode($response, true);
		// die(json_encode($GetData));
		if ($GetData["result"]=="SUCCESS") {			 
			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
			// die($GetData["data"]);
			$this->Preview_MonitoringAchievement($params, $GetData["data"], $_SESSION["logged_in"]["useremail"]);
		} else {
			$paramsLog['Remarks']="FAILED - Gagal Mengambil Data Achievement KPI ".$url;
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
			die($url."<br>Gagal Mengambil Data Achievement KPI");			
			// die($GetData["error"]);
		}	
	}

	public function FillExcelSheetHeader($params, $spreadsheet, $idx, $name)
	{
		$bl = (int)$params["bulan"]-1;

		if ($idx>0) {
			$sheet = $spreadsheet->createSheet($idx);
		} else {
			$sheet = $spreadsheet->getActiveSheet($idx);
		}
		$sheet->setTitle($name);
		$sheet->setCellValue('A1', 'LAPORAN ACHIEVEMENT KPI');
		$sheet->setCellValue('A2', 'Kategori : '.strtoupper($params["kategori"]));
		$sheet->setCellValue('A3', 'Periode : '.$this->MONTH[$bl]." ".$params["tahun"]);
		$sheet->setCellValue('A4', 'Area : '.$name);

		$sheet->getStyle('A1')->getFont()->setSize(20);
		$sheet->getStyle("A1:A4")->getFont()->setBold(true);
		$sheet->mergeCells('A1:L1');
		$sheet->mergeCells('A2:L2');
		$sheet->mergeCells('A3:L3');
		$sheet->mergeCells('A4:L4');
		$this->currrow[$idx] = 5;


		$currcol = 1;
		$this->currrow[$idx] += 1;
		$this->tableStart[$idx] = $this->currrow[$idx];
		$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], "NO");
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], "LOKASI");
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], "NAMA");
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], "KPI");
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], "BOBOT");
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], $this->MONTH[$bl]." ".$params["tahun"]);
		$sheet->mergeCells('F'.$this->currrow[$idx].':H'.$this->currrow[$idx]);

		$prevrow = $this->currrow[$idx];
		$nextrow = $this->currrow[$idx]+1;
		$sheet->mergeCells('A'.$this->currrow[$idx].':A'.$nextrow);
		$sheet->mergeCells('B'.$this->currrow[$idx].':B'.$nextrow);
		$sheet->mergeCells('C'.$this->currrow[$idx].':C'.$nextrow);
		$sheet->mergeCells('D'.$this->currrow[$idx].':D'.$nextrow);
		$sheet->mergeCells('E'.$this->currrow[$idx].':E'.$nextrow);

		$this->currrow[$idx] += 1;
		$currcol = 6;
		$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], "TARGET");
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], "ACH");
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], "%");	
		$sheet->getStyle("A".$prevrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$this->currrow[$idx])->getFont()->setBold(true);

		$this->NO[$idx] = 0;
	}

	public function FillExcelSheetContent($params, $spreadsheet, $idx, $data)
	{
		// $sheet = $spreadsheet->getActiveSheet($idx);
		$sheet = $spreadsheet->setActiveSheetIndex($idx);
		$this->NO[$idx] += 1;
		
		$currcol = 1;
		$this->currrow[$idx] += 1;
		$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], $this->NO[$idx]);
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], trim($data["KODE_LOKASI"]));
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], trim($data["NAMA_KARYAWAN"]).(($data["STATUS_ACHIEVEMENT"]!="APPROVED")?"***UNAPPROVED***":""));
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], trim($data["NAMA_KPI"]));
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], $data["BOBOT_KPI"]);
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], $data["TARGET_KPI"]);
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], $data["TOTAL_ACHIEVEMENT"]);
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], $data["PERSEN_ACHIEVEMENT"]);

		$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-3).$this->currrow[$idx])->getNumberFormat()->setFormatCode('#,##0');				
		$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-2).$this->currrow[$idx])->getNumberFormat()->setFormatCode('#,##0');				
		$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$this->currrow[$idx])->getNumberFormat()->setFormatCode('#,##0');				
		$sheet->getStyle("H".$this->currrow[$idx])->getNumberFormat()->setFormatCode("#,##0.00");		

		$currcol+= 3;
		$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], $data["STATUS_ACHIEVEMENT"]);		
	}

	public function FillExcelSheetFooter($params, $spreadsheet, $idx, $data)
	{
		// $sheet = $spreadsheet->getActiveSheet($idx);
		$sheet = $spreadsheet->setActiveSheetIndex($idx);
		$startRow = $this->tableStart[$idx]+2;
		$endRow = $this->currrow[$idx];
		$start = 0;
		$end = 0;
		$end_r = 0;
		$EMP = "";
		$this->NO[$idx] = 0;
		$statusEnd = 0;
		for($i=$startRow; $i<=$endRow;$i++) {
			//$colLetter = PHPExcel_Cell::stringFromColumnIndex( 8 );
			$persenBobot = '(E'.$i.'*H'.$i.')/100';
			$sheet->setCellValue(('I'.''.$i) , "=$persenBobot");
			
			if ($i==$startRow) {
				$statusEnd = 0;
				$start = $i;
				$end = $i;
				$EMP = $sheet->getCell("C".$i)->getValue();		
				$this->NO[$idx]++;
				$sheet->setCellValueByColumnAndRow(9, 6, '%BOBOT');
				$sheet->setCellValueByColumnAndRow(10, 6, 'TOTAL%');
				$sheet->setCellValueByColumnAndRow(11, 6, 'APPROVAL STATUS');
				$sheet->mergeCells('I6:I7');
				$sheet->mergeCells('J6:J7');
				$sheet->mergeCells('K6:K7');

				$sheet->getStyle('I6:K7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$sheet->getStyle('I6:K7')->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$sheet->getStyle('J6:K7')->getAlignment()->setWrapText(true);
			} 
			else if ($EMP==$sheet->getCell("C".$i)->getValue()) {
				$end = $i;
				$statusEnd = 0;
				if ($i==$endRow) {
					//ini masuk jika baris terakhir lebih dari 1 baris
					$statusEnd = $i;
					$sheet->mergeCells('A'.$start.':A'.$end);
					$sheet->setCellValueByColumnAndRow(1, $start, $this->NO[$idx]);
					$sheet->mergeCells('B'.$start.':B'.$end);
					$sheet->mergeCells('C'.$start.':C'.$end);
					$sheet->mergeCells('J'.$start.':J'.$end);
					$sheet->mergeCells('K'.$start.':K'.$end);
					$sheet->getStyle('J'.$start.':J'.$end)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$sheet->getStyle('J'.$start.':J'.$end)->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$sheet->getStyle('K'.$start.':K'.$end)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$sheet->getStyle('K'.$start.':K'.$end)->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					//$sheet->setCellValueByColumnAndRow(10, $end, 'reegan '.$i);

					//log_message('error','sheet_Id '.$idx.' start '.$start.' '.$end.' reegan');
					$sheet->getStyle('A'.$start)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
					$sheet->getStyle('B'.$start)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
					$sheet->getStyle('C'.$start)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
					$sheet->getStyle('J'.$start)->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$sheet->getStyle('J'.$start)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				}

			} else {	
				$sheet->mergeCells('A'.$start.':A'.$end);
				$sheet->setCellValueByColumnAndRow(1, $start, $this->NO[$idx]);
				$sheet->mergeCells('B'.$start.':B'.$end);
				$sheet->mergeCells('C'.$start.':C'.$end);
				$sheet->mergeCells('J'.$start.':J'.$end);
				$sheet->mergeCells('K'.$start.':K'.$end);

				

				$sheet->getStyle('A'.$start)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
				$sheet->getStyle('B'.$start)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
				$sheet->getStyle('C'.$start)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
				$sheet->getStyle('J'.$start)->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$sheet->getStyle('J'.$start)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

				$sheet->getStyle('K'.$start)->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$sheet->getStyle('J'.$start)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

				$start = $i;
				$end = $i;
				
				$EMP = $sheet->getCell("C".$i)->getValue();		
				$this->NO[$idx]++;
				$jmlhRow = 1;
				$statusEnd = ($i-1);
				
			}
			//log_message('error','sheet_Id '.$idx.' start '.$start.' end '.$end.' i '.$i.' statusEnd '.$statusEnd);
			
			$sheet->getStyle('J'.$start)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$totalPersen = 'ROUND(SUM(I'.$start.':I'.$end.'),0)';
			$sheet->setCellValue(('J'.''.$start) , "=$totalPersen");
			
		}

		$KPI = $data["KPI"];

		for ($i=0;$i<count($KPI);$i++) {
			$this->currrow[$idx]+= 1;
			$PERSEN = (($KPI[$i]["TARGET_KPI"]==0)? 0: round(($KPI[$i]["TOTAL_ACHIEVEMENT"] * 100)/$KPI[$i]["TARGET_KPI"],2));
			$currcol = 4;
			$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], $KPI[$i]["NAMA_KPI"]);
			$currcol+= 2;
			$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], $KPI[$i]["TARGET_KPI"]);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], $KPI[$i]["TOTAL_ACHIEVEMENT"]);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $this->currrow[$idx], $PERSEN);	
			$currcol+= 1;
			// $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$this->currrow[$idx])->getNumberFormat()->setFormatCode('#,##0');
			$sheet->getStyle("A".$this->currrow[$idx].":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$this->currrow[$idx])->getFont()->setBold(true);
			$sheet->mergeCells('A'.$this->currrow[$idx].':C'.$this->currrow[$idx]);
			$sheet->mergeCells('D'.$this->currrow[$idx].':E'.$this->currrow[$idx]);
			$this->tableEnd[$idx] = $this->currrow[$idx];
		}

		if ($this->tableStart[$idx]!=0 && $this->tableEnd[$idx]!=0) {
			$max_col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol);
	
			for ($x = 'A'; $x != $max_col; $x++) {
				for($y=$this->tableStart[$idx];$y<=$this->tableEnd[$idx];$y++) {
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
		}

		for ($i = 'A'; $i != 'H'; $i++) {
		    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
		}

	}

	public function Preview_MonitoringAchievement($params, $data, $user) 
	{
		$style_col_ganjil ="float:left;line-height:15px;vertical-align:top;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#ffffcc;font-size:10px;";
		$style_col_genap = "float:left;line-height:15px;vertical-align:top;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#fff;font-size:10px;";
		$style_header= "float:left;min-height:20px;line-height:15px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#cccccc;font-size:10px;";
		$style_footer= "float:left;min-height:20px;line-height:20px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#cccccc;font-size:10px;";

		$kanan = "text-align:right;padding-right:5px;";
		$kiri  = "text-align:left; padding-left: 5px;";
		$center= "text-align:center;";


		$header_html = "<div style='clear:both;height:25px;'></div>";
		$header_html.= "<div id='div_header' style='padding-left:10px;'>";
		$header_html.= "	<div><h2>LAPORAN ACHIEVEMENT KPI</h2></div>";
		$header_html.= "	<div><b>Kategori : ".strtoupper($params["kategori"])."</b></div>";
		$header_html.= "	<div><b>Periode : ".$params["bulan"]." / ".$params["tahun"]."</b></div>";
		$header_html.= "</div>";	//close div_header

		$LIST_LOK = array();
		$LIST_LOK[0]["NAMA"] = "NASIONAL";
		$LIST_LOK[0]["KPI"] = array();

		$LIST_KPI = array();

		$TAHUN = $params["tahun"];
		$spreadsheet = new Spreadsheet();
		$sheet = array();

		if($this->excel_flag == 1){
			$this->FillExcelSheetHeader($params, $spreadsheet, 0, "NASIONAL");
		}

		$style_col = $style_col_genap;

		$group_header = "	<tr style='background-color:#ffe53b;'>";
		$group_header.= "		<th width='4%;' rowspan='2' class='td-center'>NO</th>";
		$group_header.= "		<th width='10%;' rowspan='2'><b>Cabang</b></th>";
		$group_header.= "		<th width='20%;' rowspan='2'><b>Karyawan</b></th>";
		$group_header.= "		<th width='38%;' rowspan='2'><b>Template</b></th>";
		$group_header.= "		<th width='7%;' rowspan='2'><b>Bobot</b></th>";
		$group_header.= "		<th width='21%;' colspan='3'><b>Periode</b></th>";
		$group_header.= "	</tr>";	//close div_column_header
		$group_header = "	<tr style='background-color:#ffe53b;'>";
		$group_header.= "		<th width='7%;'><b>Target</b></th>";
		$group_header.= "		<th width='7%;'><b>ACH</b></th>";
		$group_header.= "		<th width='7%;'><b>%</b></th>";
		$group_header.= "	</tr>";	//close div_column_header

		$content_html = "<style>";
		$content_html.= "	td, th { border:1px solid #ccc; padding:2px!important; }";
		$content_html.= "	.td-center { text-align: center; }";
		$content_html.= "	.td-right { text-align:right;}";
		$content_html.= "</style>";
		$content_html.= "<div class='div_body' style='font-size:9pt!important;'>";

		$TABLESTART = 0;
		$TABLEEND = 0;

		//ini untuk menampung total per LOK
		$ARR_LOK = array();
		$TOTAL_KARYAWAN = 0;
		$TOTAL_SUKSES = 0;
		$TOTAL_GAGAL = 0;

		//ini untuk menampung total keseluruhan 
		$TOTAL_KARYAWAN_ALL=0;
		$TOTAL_SUKSES_ALL=0;
		$TOTAL_GAGAL_ALL=0;
		$TOTAL_EMETERAI_ALL=0;

		$LOKCOUNT = 1;
		$LOKFOUND = false;
		$LOKIndex = 0;

		$KPICOUNT = 0;
		$KPIFOUND = false;
		$KPIIndex = 0;

		for($i=0;$i<count($data);$i++)
		{
			$LOKCOUNT = count($LIST_LOK);
			$LOKFOUND = false;
			$LOKIndex = 0;
			for($x=1;$x<$LOKCOUNT;$x++) {
				if ($LIST_LOK[$x]["NAMA"]==$data[$i]["KODE_LOKASI"]) {
					$LOKIndex = $x;
					$LOKFOUND = true;
					break;
				}				
			}
			if ($LOKFOUND==false) {
				$LOKIndex = $LOKCOUNT;
				$LIST_LOK[$LOKIndex]["NAMA"] = $data[$i]["KODE_LOKASI"];
				$LIST_LOK[$LOKIndex]["KPI"] = array();
				$this->FillExcelSheetHeader($params, $spreadsheet, $LOKIndex, $data[$i]["KODE_LOKASI"]);
			}			

			$LIST_KPI = $LIST_LOK[0]["KPI"];
			$KPICOUNT = count($LIST_KPI);
			$KPIFOUND = false;
			$KPIIndex = 0;
			for($x=0;$x<$KPICOUNT;$x++) {
				if ($LIST_KPI[$x]["NAMA_KPI"]==$data[$i]["NAMA_KPI"]) {
					$KPIIndex = $x;
					$KPIFOUND = true;
					break;
				}
			}
			if ($KPIFOUND==false) {
				$KPIIndex = $KPICOUNT;
				$LIST_KPI[$KPIIndex]["NAMA_KPI"] = $data[$i]["NAMA_KPI"];
				$LIST_KPI[$KPIIndex]["TARGET_KPI"] = 0;
				$LIST_KPI[$KPIIndex]["TOTAL_ACHIEVEMENT"] = 0;
				$LIST_KPI[$KPIIndex]["PERSEN_ACHIEVEMENT"] = 0;
			}
			$LIST_KPI[$KPIIndex]["TARGET_KPI"]+=$data[$i]["TARGET_KPI"];
			$LIST_KPI[$KPIIndex]["TOTAL_ACHIEVEMENT"]+=$data[$i]["TOTAL_ACHIEVEMENT"];
			$LIST_KPI[$KPIIndex]["PERSEN_ACHIEVEMENT"]+=$data[$i]["PERSEN_ACHIEVEMENT"];
			$LIST_LOK[0]["KPI"] = $LIST_KPI;

			$LIST_KPI = $LIST_LOK[$LOKIndex]["KPI"];
			$KPICOUNT = count($LIST_KPI);
			$KPIFOUND = false;
			$KPIIndex = 0;
			for($x=0;$x<$KPICOUNT;$x++) {
				if ($LIST_KPI[$x]["NAMA_KPI"]==$data[$i]["NAMA_KPI"]) {
					$KPIIndex = $x;
					$KPIFOUND = true;
					break;
				}
			}
			if ($KPIFOUND==false) {
				$KPIIndex = $KPICOUNT;
				$LIST_KPI[$KPIIndex]["NAMA_KPI"] = $data[$i]["NAMA_KPI"];
				$LIST_KPI[$KPIIndex]["TARGET_KPI"] = 0;
				$LIST_KPI[$KPIIndex]["TOTAL_ACHIEVEMENT"] = 0;
				$LIST_KPI[$KPIIndex]["PERSEN_ACHIEVEMENT"] = 0;
			}
			$LIST_KPI[$KPIIndex]["TARGET_KPI"]+=$data[$i]["TARGET_KPI"];
			$LIST_KPI[$KPIIndex]["TOTAL_ACHIEVEMENT"]+=$data[$i]["TOTAL_ACHIEVEMENT"];
			$LIST_KPI[$KPIIndex]["PERSEN_ACHIEVEMENT"]+=$data[$i]["PERSEN_ACHIEVEMENT"];
			$LIST_LOK[$LOKIndex]["KPI"] = $LIST_KPI;

			$data[$i]["NO"] = $i+1;
			if ($i%2==1)
				$style_col = $style_col_genap;
			else
				$style_col = $style_col_ganjil;

			$height = 20;
			/*(case when KodeLokasi='DMI' then 'JKT' else KodeLokasi end) as KODE_LOKASI,
			EmpId as KODE_KARYAWAN, EmpName as NAMA_KARYAWAN, EmpCategory as KATEGORI_KARYAWAN, 
			Tahun as TAHUN, Bulan as BULAN, Training as TRAINING, KodeTarget as KODE_TARGET, 
			TotalAchievement as TOTAL_ACHIEVEMENT_KARYAWAN, KPICode as KODE_KPI, KPIName as NAMA_KPI,
			KPIBobot as BOBOT_KPI, KPITarget as TARGET_KPI, AcvTotal as TOTAL_ACHIEVEMENT,
			AcvPersen as PERSEN_ACHIEVEMENT, AcvBobot as BOBOT_ACHIEVEMENT,
			RequestStatus as STATUS_ACHIEVEMENT*/

			$content_html.= "	<tr>";
			$content_html.= "		<td class='td-center'>".$data[$i]["NO"]."</td>";
			$content_html.= "		<td>".$data[$i]["KODE_LOKASI"]."</td>";
			$content_html.= "		<td>".$data[$i]["NAMA_KARYAWAN"]."</td>";
			$content_html.= "		<td>".$data[$i]["NAMA_KPI"]."</td>";
			$content_html.= "		<td>".$data[$i]["BOBOT_KPI"]."</td>";
			$content_html.= "		<td>".$data[$i]["TARGET_KPI"]."</td>";
			$content_html.= "		<td>".$data[$i]["TOTAL_ACHIEVEMENT"]."</td>";
			$content_html.= "		<td>".$data[$i]["PERSEN_ACHIEVEMENT"]."</td>";
			$content_html.= "	</tr>";

			if ($this->excel_flag==1) {
				if ("CONTENTROW"=="CONTENTROW") {
					$this->FillExcelSheetContent($params, $spreadsheet, 0, $data[$i]);
					$this->FillExcelSheetContent($params, $spreadsheet, $LOKIndex, $data[$i]);
				}
			}
		}


		// $group_footer = "	<tr style='background-color:#ffe53b;'>";
		// $group_footer.= "		<td colspan='4'></td>";
		// $group_footer.= "		<td colspan='2'><b>TOTAL METERAI ".$LOK.":".$METERAI_10000."</b></td>";
		// $group_footer.= "		<td colspan='2'><b>SUKSES: ".$TOTAL_SUKSES."</b></td>";
		// $group_footer.= "		<td colspan='2'><b>GAGAL: ".$TOTAL_GAGAL."</b></td>";
		// $group_footer.= "		<td colspan='2' class='td-right'><b>".number_format($TOTAL_EMETERAI)."</b></td>";
		// $group_footer.= "	</tr>";	//close div_column_header
		// $group_footer.= "</table>";

		if ($this->excel_flag==1) {
			for($x=0;$x<$LOKCOUNT;$x++) {
				$this->FillExcelSheetFooter($params, $spreadsheet, $x, $LIST_LOK[$x]);
			}
		}

		// $content_html.= $group_footer;
		// $content_html.= "</div>";

		// //die($content_html);
		// $th = date("Y", strtotime($p_tgl1));
		// $bl = date("m", strtotime($p_tgl1));
		if ($this->excel_flag==1) {
			$filename='MonitoringAchievement['.$params["tahun"].']['.$params["bulan"].']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
		}

		// if ($this->confirm_flag==1) {
		// 	if ($this->pdf_flag==1) {
		// 		$this->Pdf_Report($header_html, $content_html, "", $p_tgl1, $p_tgl2, $user, $lok);
		// 	} else if ($this->excel_flag==1) {
		//  		$main_dir = "D:/";
		// 		$pdf_dir = $main_dir."/Report/EMeterai/".$th;
		// 		$nm_file = "pemakaian_emeterai_".$lok."_".$th."_".$bl."_".date("d",strtotime($tgl1)).date("d",strtotime($tgl2)).".xlsx";
		//         //Jika folder save belum ada maka create dahulu
		//         if (!is_dir($pdf_dir)) {
		// 			mkdir($pdf_dir, 0777, TRUE);	
		// 		}
		// 		$namafile = $pdf_dir."/".$nm_file;
		//         $writer->save($namafile);	// download file 

		// 		$email_content = $user." mengirimkan Laporan Pemakaian Meterai Elektronik<br>";
		// 		$email_content.= "Periode : ".date("d-M-Y",strtotime($tgl1))." s/d ".date("d-M-Y", strtotime($tgl2));
		// 		$subject = "Report EMeterai [".$lok."][".$th."][".$bl."][".date("d",strtotime($tgl1))."-".date("d",strtotime($tgl2))."]";
		// 		$this->EmailReport($namafile, $subject, $email_content);
		// 		unlink($namafile);
		// 	}
		// } else 
		if ($this->pdf_flag==1) {
			// $this->Pdf_Report($header_html, $content_html, "","","","",$lok);
		} else if ($this->excel_flag==1) {
	        $writer->save('php://output');	// download file 
	        exit();			
		} else {
			$row_btn = "";

			// if ($opt=="PERIODE") {
			// 	if ($_SESSION['logged_in']["branch_id"]=="JKT") {
			// 		$row_btn.= "<form action='Confirm'>";
			// 		$row_btn.= '<input type="hidden" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1" value="'.date("m/d/Y",strtotime($p_tgl1)).'">';
			// 		$row_btn.= '<input type="hidden" class="form-control" id="dp2" placeholder="mm/dd/yyyy" name="dp2" value="'.date("m/d/Y",strtotime($p_tgl2)).'">';
			// 		$row_btn.= '<input type="submit" value="Confirm">';
			// 		$row_btn.= '</form>';
			// 	}
			// }
			
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
					'margin_top' => 35,
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