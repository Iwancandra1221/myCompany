<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require 'vendor/autoload.php';
require 'vendor/setasign/fpdi/src/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ReportPenggunaanEMeterai extends MY_Controller 
{
	public $pdf_flag = 0;
	public $confirm_flag = 0;
	public $excel_flag = 0;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('ReportModel');
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
		$data['title'] = 'REPORT METERAI ELEKTRONIK | '.WEBTITLE;
		$data['meterai_type'] = 'METERAI ELEKTRONIK';
		$data['databases'] = $this->MasterDbModel->getList();
		$data['err'] = '';
		$data['userBranch'] = $_SESSION["branchID"];
		// $data['logs'] = $this->ReportModel->GetImportLogs(date("Y"), date("m"));

		$params = array();	
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN PENGGUNAAN EMETERAI";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN PENGGUNAAN EMETERAI";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$this->RenderView('ReportPenggunaanEMeteraiForm',$data);
	}

	public function Komputerisasi()
	{
		$data = array();
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}		
		$data['title'] = 'REPORT METERAI KOMPUTERISASI | '.WEBTITLE;
		$data['meterai_type'] = 'METERAI KOMPUTERISASI';
		$data['err'] = '';
		$data['databases'] = $this->MasterDbModel->getList();
		$data['userBranch'] = $_SESSION["branchID"];

		$params = array();	
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN PENGGUNAAN EMETERAI";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN PENGGUNAAN EMETERAI";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);
		
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

	public function Proses($meteraiType="Komputerisasi")
	{
		$data = array();
		$page_title = 'Report Finance';
		$this->confirm_flag = 0;
		
		if ($meteraiType=="Komputerisasi") {
			$DatabaseId = 0;
		} else {
			if(!empty($_POST["Database"])){
				$DatabaseId = $_POST["Database"];
			}else{
				$DatabaseId = 'error';
			}
		}
		if($DatabaseId!=='error'){
			if (isset($_POST["btnListMeterai"])){
				redirect("ReportPenggunaanEMeterai/Proses_PenggunaanMeteraiNew?db=".$DatabaseId."&th=".$_POST["Tahun"]."&bl=".$_POST["Bulan"]."&import=0");
			} else if (isset($_POST["btnSend"])) {
				redirect("ReportPenggunaanEMeterai/Proses_PenggunaanMeteraiNew?db=".$DatabaseId."&th=".$_POST["Tahun"]."&bl=".$_POST["Bulan"]."&import=1");			
			}


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


			$dateOpt = $_POST["DateOption"];
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
				$this->Proses_PenggunaanEMeterai($page_title, $dp1, $dp2, $opt, $meteraiType, $dateOpt, $DatabaseId);
			} else if ($meteraiType=="Elektronik")  {
				redirect("ReportPenggunaanEMeterai");
			} else {
				redirect("ReportPenggunaanEMeterai/Komputerisasi");
			}
		}else{
			redirect("ReportPenggunaanEMeterai");
		}
	}

	public function Proses_PenggunaanEMeterai($page_title, $p_tgl1, $p_tgl2, $opt, $meteraiType, $dateOpt="", $DatabaseId=0)
	{
		$null = 0;
		$api = 'APITES';
		$paramDb = array();
		if ($DatabaseId==0) {
			if(!isset($_SESSION["conn"])) {
				redirect("ConnectDB");
			}
			$url = $_SESSION["conn"]->AlamatWebService;
			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;
			$branchId = $_SESSION["conn"]->BranchId;
		} else {
			$GetDb = $this->MasterDbModel->get($DatabaseId);
			$url = $GetDb->AlamatWebService;
			$svr = $GetDb->Server;
			$db  = $GetDb->Database; 
			$branchId = $GetDb->BranchId;
		}

		set_time_limit(60);
		// $url = "http://localhost/";
		$url = $url.API_BKT."/ReportFinance/ReportPenggunaanEMeterai?api=".urlencode($api)
			."&tgl1=".urlencode($p_tgl1)."&tgl2=".urlencode($p_tgl2)
			."&svr=".urlencode($svr)."&db=".urlencode($db)."&jns=".urlencode($meteraiType)
			."&dateopt=".urlencode($dateOpt);
		// die($url);
		$GetContents = @file_get_contents($url);

		if ($GetContents!==null) {

			$GetData = json_decode($GetContents, true);
			
			if ($GetData["result"]=="sukses") {
				$result = $GetData["data"];

				if (strtoupper($meteraiType)=="KOMPUTERISASI") {
					$this->Preview_PenggunaanMeteraiKomputerisasi($opt, $dateOpt, $p_tgl1, $p_tgl2, $result, $_SESSION["logged_in"]["useremail"], $branchId);
				} else {
					$this->Preview_PenggunaanMeteraiElektronik($opt, $dateOpt, $p_tgl1, $p_tgl2, $result, $_SESSION["logged_in"]["useremail"], $branchId);
				}

			} else {
				$null++;
			}

		}else{
			$null++;
		}

		if($null>0){
	?>
			<script type="text/javascript">
				if (confirm("Data tidak ditemukan.")) {
				  window.location.href = "<?php echo site_url('ReportPenggunaanEMeterai'); ?>";
				} else {
				  window.location.href = "<?php echo site_url('ReportPenggunaanEMeterai'); ?>";
				}
			</script>
	<?php
		}
	
	}

	public function Proses_PenggunaanEMeterai2($page_title, $p_tgl1, $p_tgl2, $opt, $meteraiType, $dateOpt="")
	{
		
		$api = 'APITES';
		// if(!isset($_SESSION["conn"])) {
		// 	redirect("ConnectDB");
		// }
		// $url = $_SESSION["conn"]->AlamatWebService;
		// $svr = $_SESSION["conn"]->Server;
		// $db  = $_SESSION["conn"]->Database;

		$result = array();

		$DBs = $this->MasterDbModel->getListForExport();
		if (count($DBs)>0) {
			foreach($DBs as $d) {
				$url = $d->AlamatWebService;
				$svr = $d->Server;
				$db = $d->Database;

				set_time_limit(60);
				// $url = "http://localhost/";
				$url = $url.API_BKT."/ReportFinance/ReportPenggunaanEMeterai?api=".urlencode($api)
					."&tgl1=".urlencode($p_tgl1)."&tgl2=".urlencode($p_tgl2)
					."&svr=".urlencode($svr)."&db=".urlencode($db)."&jns=".urlencode($meteraiType)
					."&dateopt=".urlencode($dateOpt);
				// die($url);
				$GetContents = @file_get_contents($url);
				if ($GetContents==null) {
					array_push($result, array("branch"=>$d->BranchId, "result"=>"API not connected", "data"=>array()));
				} else {
					$GetData = json_decode($GetContents, true);
			
					if ($GetData["result"]=="sukses") {
						array_push($result, array("branch"=>$d->BranchId, "result"=>"SUCCESS", "data"=>$GetData["data"]));
					} else {
						array_push($result, array("branch"=>$d->BranchId, "result"=>$GetData["error"], "data"=>array()));
					}
				}
			}

			if (strtoupper($meteraiType)=="KOMPUTERISASI") {
				$this->Preview_PenggunaanMeteraiKomputerisasi($opt, $dateOpt, $p_tgl1, $p_tgl2, $result, $_SESSION["logged_in"]["useremail"], $_SESSION["conn"]->BranchId);
			} else {
				$this->Preview_PenggunaanMeteraiElektronik($opt, $dateOpt, $p_tgl1, $p_tgl2, $result, $_SESSION["logged_in"]["useremail"], $_SESSION["conn"]->BranchId);
			}
		} else {			
			die();
		}	
	}

	public function Preview_PenggunaanMeteraiElektronik2($opt, $dateOpt, $p_tgl1, $p_tgl2, $p_data, $user, $lok="") 
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
		$header_html.= "	<div><h2>LAPORAN PEMAKAIAN BEA METERAI ELEKTRONIK</h2></div>";
		$header_html.= "	<div><b>Berdasarkan : ".((strtoupper($dateOpt)=="TANGGAL STAMPING")?"Tanggal Pembubuhan Meterai":"Tanggal Request S/N")."</b></div>";
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
			$sheet->setCellValue('A2', 'Berdasarkan : '.((strtoupper($dateOpt)=="TANGGAL STAMPING")?"Tanggal Pembubuhan Meterai":"Tanggal Request S/N"));
			$sheet->setCellValue('A3', 'Periode : '.date("d-M-Y", strtotime($p_tgl1))." - ".date("d-M-Y", strtotime($p_tgl2)));

			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->getStyle("A1:A3")->getFont()->setBold(true);
			$sheet->mergeCells('A1:L1');
			$sheet->mergeCells('A2:L2');
			$sheet->mergeCells('A3:L3');
			$currrow = 3;
		}

		$style_col = $style_col_genap;

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

		$EMETERAI_COUNTER = 0;
		
		$group_header = "	<tr style='background-color:#ffe53b;'>";
		$group_header.= "		<th width='3%;' class='td-center'>NO</th>";
		$group_header.= "		<th width='15%;'><b>S/N Meterai</b></th>";
		$group_header.= "		<th width='3%;'><b>Lok</b></th>";
		$group_header.= "		<th width='7%;'><b>Tgl Request S/N</b></th>";
		$group_header.= "		<th width='7%;'><b>Tgl Pembubuhan</b></th>";
		$group_header.= "		<th width='10%;'><b>No Bukti</b></th>";
		$group_header.= "		<th width='16%;'><b>Ket</b></th>";
		$group_header.= "		<th width='8%;'><b>Wilayah</b></th>";
		$group_header.= "		<th width='12%;'><b>Lawan Transaksi</b></th>";
		$group_header.= "		<th width='6%;'><b>Kode Lawan</b></th>";
		$group_header.= "		<th width='8%;' class='td-right'><b>Total Trans</b></th>";
		$group_header.= "		<th width='5%;' class='td-right'><b>Meterai</b></th>";
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
		$METERAI_10000=0;
		$TOTAL_SUKSES = 0;
		$TOTAL_GAGAL = 0;
		$TOTAL_EMETERAI = 0;

		//ini untuk menampung total keseluruhan 
		$METERAI_10000_ALL=0;
		$TOTAL_SUKSES_ALL=0;
		$TOTAL_GAGAL_ALL=0;
		$TOTAL_EMETERAI_ALL=0;
		//Hitung Dahulu Total Keseluruhan
		for($j=0;$j<count($p_data);$j++) {
			$data = $p_data[$j]["data"];
			for($i=0;$i<count($data);$i++)
			{
				if ($data[$i]["EMETERAI_VALUE"]>0) {
					$METERAI_10000_ALL++;
					if ($data[$i]["ISCANCELLED"]==0) {
						$TOTAL_SUKSES_ALL++;
					} else {
						$TOTAL_GAGAL_ALL++;
					}
					$TOTAL_EMETERAI_ALL += $data[$i]["EMETERAI_VALUE"];
				}
			}
		}

		for($j=0;$j<count($p_data);$j++) {
			$data = $p_data[$j]["data"];
			for($i=0;$i<count($data);$i++)
			{
				$n = $i+1;

				if ($i%2==1)
					$style_col = $style_col_genap;
				else
					$style_col = $style_col_ganjil;

				if (trim($LOK)==trim($data[$i]["KD_LOKASI"])) {
					$height = 20;

					$content_html.= "	<tr>";
					$content_html.= "		<td class='td-center'>".$n."</td>";
					$content_html.= "		<td>".$data[$i]["IJINEMETERAI"]."</td>";
					$content_html.= "		<td>".$data[$i]["KD_LOKASI"]."</td>";
					$content_html.= "		<td>".date("d-M-Y", strtotime($data[$i]["CDATE"]))."</td>";
					$content_html.= "		<td>".date("d-M-Y", strtotime($data[$i]["TGL_TRANS"]))."</td>";
					$content_html.= "		<td>".$data[$i]["NO_BUKTI"]."</td>";
					$content_html.= "		<td>".$data[$i]["KET"]."</td>";
					if ($data[$i]["ISCANCELLED"]==0) {
					$content_html.= "		<td>".$data[$i]["WILAYAH"]."</td>";
					$content_html.= "		<td>".trim($data[$i]["NM_PLG"])."</td>";
					$content_html.= "		<td>".$data[$i]["KD_PLG"]."</td>";
					$content_html.= "		<td class='td-right'>".number_format($data[$i]["TOTAL_BUKTI"])."</td>";
					} else {
					$content_html.= "		<td colspan='4'><font color='red'><b>BATAL : ".$data[$i]["CANCELLEDNOTE"]."</font></td>";
					}
					$content_html.= "		<td class='td-right'>".number_format($data[$i]["EMETERAI_VALUE"])."</td>";
					$content_html.= "	</tr>";

					if ($this->excel_flag==1) { 
							$currcol = 1;
							$currrow+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $n);
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["IJINEMETERAI"]));
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["KD_LOKASI"]));
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["CDATE"]));
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["TGL_TRANS"]));
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["NO_BUKTI"]));
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["KET"]));
							$currcol+= 1;
							if ($data[$i]["ISCANCELLED"]==0){
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["WILAYAH"]));
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["NM_PLG"]));
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["KD_PLG"]));
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $data[$i]["TOTAL_BUKTI"]);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$currcol+= 1;
							} else {
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["CANCELLEDNOTE"]));
							$sheet->mergeCells("H".$currrow.":K".$currrow);	
							$currcol+= 4;
							}
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $data[$i]["EMETERAI_VALUE"]);		
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');		 
					}

					$EMETERAI_COUNTER += 1;
					$METERAI_10000++;
					$TOTAL_EMETERAI += $data[$i]["EMETERAI_VALUE"];
					if ($data[$i]["ISCANCELLED"]==0) {
						$TOTAL_SUKSES++;
					} else {
						$TOTAL_GAGAL++;
					}
				} else {
					if ($LOK!="") {
						if ($this->confirm_flag==1) {
							$url = $this->API_URL."/Billing/SaveEMeteraiUsage?lok=".urlencode($LOK)."&ijin=".urlencode($IJIN)
								."&tgl1=".urlencode(date("m/d/Y",strtotime($p_tgl1)))."&tgl2=".urlencode(date("m/d/Y",strtotime($p_tgl2)))
								."&total=".urlencode($TOTAL_EMETERAI)."&met3=".urlencode($METERAI_3000)."&met6=".urlencode($METERAI_6000)."&met10=".urlencode($METERAI_10000)."&tipe=".urlencode("KWITANSI TAGIHAN")
								."&user=".urlencode($user);
							// die($url);
							$simpanData = json_decode(file_get_contents($url), true);
						}

						$group_footer = "	<tr style='background-color:#ffe53b;'>";
						$group_footer.= "		<td colspan='4'></td>";
						$group_footer.= "		<td colspan='2'><b>TOTAL METERAI ".$LOK.":".$METERAI_10000."</b></td>";
						$group_footer.= "		<td colspan='2'><b>SUKSES: ".$TOTAL_SUKSES."</b></td>";
						$group_footer.= "		<td colspan='2'><b>GAGAL: ".$TOTAL_GAGAL."</b></td>";
						$group_footer.= "		<td colspan='2' class='td-right'><b>".number_format($TOTAL_EMETERAI)."</b></td>";
						$group_footer.= "	</tr>";	//close div_column_header
						$group_footer.= "</table>";	

						if ($this->excel_flag==1) {
							$currrow+= 1;
							$TABLEEND = $currrow;
							$currcol = 5;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, "TOTAL ".$LOK.": ".$METERAI_10000);
							$currcol+= 2;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, "SUKSES: ".$METERAI_10000);
							$currcol+= 2;			
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, "GAGAL: ".$METERAI_10000);
							$currcol+= 2;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL_EMETERAI);	
							$currcol+= 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
							$sheet->mergeCells('A'.$currrow.':D'.$currrow);
							$sheet->mergeCells('E'.$currrow.':F'.$currrow);
							$sheet->mergeCells('G'.$currrow.':H'.$currrow);
							$sheet->mergeCells('I'.$currrow.':J'.$currrow);
							$sheet->mergeCells('K'.$currrow.':L'.$currrow);

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

					$height = 20;
					$content_html.= "	<div style='clear:both;height:15px;'></div>";
					$content_html.= "	<div id='div_column_header' style='width:95%!important;line-height:50px;vertical-align:middle;'>";
					$content_html.= "		LOKASI :<b>".$data[$i]["KD_LOKASI"]."</b>";
					$content_html.= "	</div>";
					$content_html.= "	<table>";
					$content_html.= $group_header;
					$content_html.= "	<tr>";
					$content_html.= "		<td class='td-center'>".$n."</td>";
					$content_html.= "		<td>".$data[$i]["IJINEMETERAI"]."</td>";
					$content_html.= "		<td>".$data[$i]["KD_LOKASI"]."</td>";
					$content_html.= "		<td>".date("d-M-Y", strtotime($data[$i]["CDATE"]))."</td>";
					$content_html.= "		<td>".date("d-M-Y", strtotime($data[$i]["TGL_TRANS"]))."</td>";
					$content_html.= "		<td>".$data[$i]["NO_BUKTI"]."</td>";
					$content_html.= "		<td>".$data[$i]["KET"]."</td>";
					if ($data[$i]["ISCANCELLED"]==0) {
					$content_html.= "		<td>".$data[$i]["WILAYAH"]."</td>";
					$content_html.= "		<td>".trim($data[$i]["NM_PLG"])."</td>";
					$content_html.= "		<td>".$data[$i]["KD_PLG"]."</td>";
					$content_html.= "		<td class='td-right'>".number_format($data[$i]["TOTAL_BUKTI"])."</td>";
					} else {
					$content_html.= "		<td colspan='4'><font color='red'><b>BATAL : ".$data[$i]["CANCELLEDNOTE"]."</font></td>";
					}
					$content_html.= "		<td class='td-right'>".number_format($data[$i]["EMETERAI_VALUE"])."</td>";
					$content_html.= "	</tr>";
		

					if($this->excel_flag == 1){ 
							$currcol = 1;
							$currrow+= 3;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'LOKASI :');
							$sheet->mergeCells('A'.$currrow.':C'.$currrow);
							$currcol = 4;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $data[$i]["KD_LOKASI"]);
							$sheet->mergeCells('D'.$currrow.':E'.$currrow);
							$sheet->getStyle("A".$currrow.":E".$currrow)->getFont()->setBold(true); 
							$currcol = 1;
							$currrow+= 1;
							$TABLESTART = $currrow;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No');
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SNMeterai');
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Lok');
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TglRequest S/N');
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TglPembubuhan');
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NoBukti');
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Ket');
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Wilayah');
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NamaPelanggan');
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode');
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TotalTrans');
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Meterai');
							$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true); 
							$currcol = 1;
							$currrow+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $n);
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["IJINEMETERAI"]));
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["KD_LOKASI"]));
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["CDATE"]));
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["TGL_TRANS"]));
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["NO_BUKTI"]));
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["KET"]));
							$currcol+= 1;
							if ($data[$i]["ISCANCELLED"]==0) {
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["WILAYAH"]));
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["NM_PLG"]));
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["KD_PLG"]));
							$currcol+= 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $data[$i]["TOTAL_BUKTI"]);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$currcol+= 1;
							} else {
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, "BATAL : ".trim($data[$i]["CANCELLEDNOTE"]));
							$currcol+= 4;	
							$sheet->mergeCells("H".$currrow.":K".$currrow);
							}
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $data[$i]["EMETERAI_VALUE"]);	
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');		 
					}
					
					//SET NILAI AWAL GROUP LOKASI
					$LOK = trim($data[$i]["KD_LOKASI"]);
					$EMETERAI_COUNTER = 1;
					$TOTAL_EMETERAI = $data[$i]["EMETERAI_VALUE"];
					$METERAI_10000 = 1;
					if ($data[$i]["ISCANCELLED"]==0) {
						$TOTAL_SUKSES=1;
						$TOTAL_GAGAL=0;
					} else {
						$TOTAL_SUKSES=0;
						$TOTAL_GAGAL=1;
					}
					//SET NILAI AWAL GROUP LOKASI - END
				}
			}
		}
		if ($IJIN!="" && $this->confirm_flag==1) {		
			$url = $this->API_URL."/Billing/SaveEMeteraiUsage?lok=".urlencode($LOK)."&ijin=".urlencode($IJIN)
				."&tgl1=".urlencode(date("m/d/Y",strtotime($p_tgl1)))."&tgl2=".urlencode(date("m/d/Y",strtotime($p_tgl2)))
				."&total=".urlencode($TOTAL_EMETERAI)."&met3=".urlencode($METERAI_3000)."&met6=".urlencode($METERAI_6000)."&met10=".urlencode($METERAI_10000)."&tipe=".urlencode("KWITANSI TAGIHAN")
				."&user=".urlencode($user);
			//die($url);
			$simpanData = json_decode(file_get_contents($url), true);
		}

		$group_footer = "	<tr style='background-color:#ffe53b;'>";
		$group_footer.= "		<td colspan='4'></td>";
		$group_footer.= "		<td colspan='2'><b>TOTAL METERAI ".$LOK.":".$METERAI_10000."</b></td>";
		$group_footer.= "		<td colspan='2'><b>SUKSES: ".$TOTAL_SUKSES."</b></td>";
		$group_footer.= "		<td colspan='2'><b>GAGAL: ".$TOTAL_GAGAL."</b></td>";
		$group_footer.= "		<td colspan='2' class='td-right'><b>".number_format($TOTAL_EMETERAI)."</b></td>";
		$group_footer.= "	</tr>";	//close div_column_header
		$group_footer.= "</table>";

		if ($this->excel_flag==1) {
			$currrow+= 1;
			$TABLEEND = $currrow;
			$currcol = 5;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "TOTAL ".$LOK.": ".$METERAI_10000);
			$currcol+= 2;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "SUKSES: ".$TOTAL_SUKSES);
			$currcol+= 2;			
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "GAGAL: ".$TOTAL_GAGAL);
			$currcol+= 2;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL_EMETERAI);	
			$currcol+= 1;
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$sheet->mergeCells('A'.$currrow.':D'.$currrow);
			$sheet->mergeCells('E'.$currrow.':F'.$currrow);
			$sheet->mergeCells('G'.$currrow.':H'.$currrow);
			$sheet->mergeCells('I'.$currrow.':J'.$currrow);
			$sheet->mergeCells('K'.$currrow.':L'.$currrow);
			
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

	public function Preview_PenggunaanMeteraiElektronik($opt, $dateOpt, $p_tgl1, $p_tgl2, $data, $user, $lok="") 
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
		$header_html.= "	<div><h2>LAPORAN PEMAKAIAN BEA METERAI ELEKTRONIK</h2></div>";
		$header_html.= "	<div><b>Berdasarkan : ".((strtoupper($dateOpt)=="TANGGAL STAMPING")?"Tanggal Pembubuhan Meterai":"Tanggal Request S/N")."</b></div>";
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
			$sheet->setCellValue('A2', 'Berdasarkan : '.((strtoupper($dateOpt)=="TANGGAL STAMPING")?"Tanggal Pembubuhan Meterai":"Tanggal Request S/N"));
			$sheet->setCellValue('A3', 'Periode : '.date("d-M-Y", strtotime($p_tgl1))." - ".date("d-M-Y", strtotime($p_tgl2)));

			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->getStyle("A1:A3")->getFont()->setBold(true);
			$sheet->mergeCells('A1:L1');
			$sheet->mergeCells('A2:L2');
			$sheet->mergeCells('A3:L3');
			$currrow = 3;
		}

		$style_col = $style_col_genap;

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

		$EMETERAI_COUNTER = 0;
		
		$group_header = "	<tr style='background-color:#ffe53b;'>";
		$group_header.= "		<th width='3%;' class='td-center'>NO</th>";
		$group_header.= "		<th width='15%;'><b>S/N Meterai</b></th>";
		$group_header.= "		<th width='3%;'><b>Lok</b></th>";
		$group_header.= "		<th width='7%;'><b>Tgl Request S/N</b></th>";
		$group_header.= "		<th width='7%;'><b>Tgl Pembubuhan</b></th>";
		$group_header.= "		<th width='10%;'><b>No Bukti</b></th>";
		$group_header.= "		<th width='16%;'><b>Ket</b></th>";
		$group_header.= "		<th width='8%;'><b>Wilayah</b></th>";
		$group_header.= "		<th width='12%;'><b>Lawan Transaksi</b></th>";
		$group_header.= "		<th width='6%;'><b>Kode Lawan</b></th>";
		$group_header.= "		<th width='8%;' class='td-right'><b>Total Trans</b></th>";
		$group_header.= "		<th width='5%;' class='td-right'><b>Meterai</b></th>";
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
		$METERAI_10000=0;
		$TOTAL_SUKSES = 0;
		$TOTAL_GAGAL = 0;
		$TOTAL_EMETERAI = 0;

		//ini untuk menampung total keseluruhan 
		$METERAI_10000_ALL=0;
		$TOTAL_SUKSES_ALL=0;
		$TOTAL_GAGAL_ALL=0;
		$TOTAL_EMETERAI_ALL=0;
		//Hitung Dahulu Total Keseluruhan
		for($i=0;$i<count($data);$i++)
		{
			if ($data[$i]["EMETERAI_VALUE"]>0) {
				$METERAI_10000_ALL++;
				if ($data[$i]["ISCANCELLED"]==0) {
					$TOTAL_SUKSES_ALL++;
				} else {
					$TOTAL_GAGAL_ALL++;
				}
				$TOTAL_EMETERAI_ALL += $data[$i]["EMETERAI_VALUE"];
			}
		}

		for($i=0;$i<count($data);$i++)
		{
			$n = $i+1;

			if ($i%2==1)
				$style_col = $style_col_genap;
			else
				$style_col = $style_col_ganjil;

			if (trim($LOK)==trim($data[$i]["KD_LOKASI"])) {
				$height = 20;

				$content_html.= "	<tr>";
				$content_html.= "		<td class='td-center'>".$n."</td>";
				$content_html.= "		<td>".$data[$i]["IJINEMETERAI"]."</td>";
				$content_html.= "		<td>".$data[$i]["KD_LOKASI"]."</td>";
				$content_html.= "		<td>".date("d-M-Y", strtotime($data[$i]["CDATE"]))."</td>";
				$content_html.= "		<td>".date("d-M-Y", strtotime($data[$i]["TGL_TRANS"]))."</td>";
				$content_html.= "		<td>".$data[$i]["NO_BUKTI"]."</td>";
				$content_html.= "		<td>".$data[$i]["KET"]."</td>";
				if ($data[$i]["ISCANCELLED"]==0) {
				$content_html.= "		<td>".$data[$i]["WILAYAH"]."</td>";
				$content_html.= "		<td>".trim($data[$i]["NM_PLG"])."</td>";
				$content_html.= "		<td>".$data[$i]["KD_PLG"]."</td>";
				$content_html.= "		<td class='td-right'>".number_format($data[$i]["TOTAL_BUKTI"])."</td>";
				} else {
				$content_html.= "		<td colspan='4'><font color='red'><b>BATAL : ".$data[$i]["CANCELLEDNOTE"]."</font></td>";
				}
				$content_html.= "		<td class='td-right'>".number_format($data[$i]["EMETERAI_VALUE"])."</td>";
				$content_html.= "	</tr>";

				if ($this->excel_flag==1) {
					 
						$currcol = 1;
						$currrow+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $n);
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["IJINEMETERAI"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["KD_LOKASI"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["CDATE"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["TGL_TRANS"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["NO_BUKTI"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["KET"]));
						$currcol+= 1;
						if ($data[$i]["ISCANCELLED"]==0){
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["WILAYAH"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["NM_PLG"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["KD_PLG"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $data[$i]["TOTAL_BUKTI"]);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol+= 1;
						} else {
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["CANCELLEDNOTE"]));
						$sheet->mergeCells("H".$currrow.":K".$currrow);	
						$currcol+= 4;
						}
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $data[$i]["EMETERAI_VALUE"]);		
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');				
					 
				}

				$EMETERAI_COUNTER += 1;
				$METERAI_10000++;
				$TOTAL_EMETERAI += $data[$i]["EMETERAI_VALUE"];
				if ($data[$i]["ISCANCELLED"]==0) {
					$TOTAL_SUKSES++;
				} else {
					$TOTAL_GAGAL++;
				}
			} else {
				if ($LOK!="") {
					if ($this->confirm_flag==1) {
						$url = $this->API_URL."/Billing/SaveEMeteraiUsage?lok=".urlencode($LOK)."&ijin=".urlencode($IJIN)
							."&tgl1=".urlencode(date("m/d/Y",strtotime($p_tgl1)))."&tgl2=".urlencode(date("m/d/Y",strtotime($p_tgl2)))
							."&total=".urlencode($TOTAL_EMETERAI)."&met3=".urlencode($METERAI_3000)."&met6=".urlencode($METERAI_6000)."&met10=".urlencode($METERAI_10000)."&tipe=".urlencode("KWITANSI TAGIHAN")
							."&user=".urlencode($user);
						// die($url);
						$simpanData = json_decode(file_get_contents($url), true);
					}

					$group_footer = "	<tr style='background-color:#ffe53b;'>";
					$group_footer.= "		<td colspan='4'></td>";
					$group_footer.= "		<td colspan='2'><b>TOTAL METERAI ".$LOK.":".$METERAI_10000."</b></td>";
					$group_footer.= "		<td colspan='2'><b>SUKSES: ".$TOTAL_SUKSES."</b></td>";
					$group_footer.= "		<td colspan='2'><b>GAGAL: ".$TOTAL_GAGAL."</b></td>";
					$group_footer.= "		<td colspan='2' class='td-right'><b>".number_format($TOTAL_EMETERAI)."</b></td>";
					$group_footer.= "	</tr>";	//close div_column_header
					$group_footer.= "</table>";	

					if ($this->excel_flag==1) {
						$currrow+= 1;
						$TABLEEND = $currrow;
						$currcol = 5;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, "TOTAL ".$LOK.": ".$METERAI_10000);
						$currcol+= 2;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, "SUKSES: ".$METERAI_10000);
						$currcol+= 2;			
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, "GAGAL: ".$METERAI_10000);
						$currcol+= 2;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL_EMETERAI);	
						$currcol+= 1;
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
						$sheet->mergeCells('A'.$currrow.':D'.$currrow);
						$sheet->mergeCells('E'.$currrow.':F'.$currrow);
						$sheet->mergeCells('G'.$currrow.':H'.$currrow);
						$sheet->mergeCells('I'.$currrow.':J'.$currrow);
						$sheet->mergeCells('K'.$currrow.':L'.$currrow);

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

				$height = 20;
				$content_html.= "	<div style='clear:both;height:15px;'></div>";
				$content_html.= "	<div id='div_column_header' style='width:95%!important;line-height:50px;vertical-align:middle;'>";
				$content_html.= "		LOKASI :<b>".$data[$i]["KD_LOKASI"]."</b>";
				$content_html.= "	</div>";
				$content_html.= "	<table>";
				$content_html.= $group_header;
				$content_html.= "	<tr>";
				$content_html.= "		<td class='td-center'>".$n."</td>";
				$content_html.= "		<td>".$data[$i]["IJINEMETERAI"]."</td>";
				$content_html.= "		<td>".$data[$i]["KD_LOKASI"]."</td>";
				$content_html.= "		<td>".date("d-M-Y", strtotime($data[$i]["CDATE"]))."</td>";
				$content_html.= "		<td>".date("d-M-Y", strtotime($data[$i]["TGL_TRANS"]))."</td>";
				$content_html.= "		<td>".$data[$i]["NO_BUKTI"]."</td>";
				$content_html.= "		<td>".$data[$i]["KET"]."</td>";
				if ($data[$i]["ISCANCELLED"]==0) {
				$content_html.= "		<td>".$data[$i]["WILAYAH"]."</td>";
				$content_html.= "		<td>".trim($data[$i]["NM_PLG"])."</td>";
				$content_html.= "		<td>".$data[$i]["KD_PLG"]."</td>";
				$content_html.= "		<td class='td-right'>".number_format($data[$i]["TOTAL_BUKTI"])."</td>";
				} else {
				$content_html.= "		<td colspan='4'><font color='red'><b>BATAL : ".$data[$i]["CANCELLEDNOTE"]."</font></td>";
				}
				$content_html.= "		<td class='td-right'>".number_format($data[$i]["EMETERAI_VALUE"])."</td>";
				$content_html.= "	</tr>";
	

				if($this->excel_flag == 1){
					 
						$currcol = 1;
						$currrow+= 3;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'LOKASI :');
						$sheet->mergeCells('A'.$currrow.':C'.$currrow);
						$currcol = 4;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $data[$i]["KD_LOKASI"]);
						$sheet->mergeCells('D'.$currrow.':E'.$currrow);
						$sheet->getStyle("A".$currrow.":E".$currrow)->getFont()->setBold(true);
					 
						$currcol = 1;
						$currrow+= 1;
						$TABLESTART = $currrow;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SNMeterai');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Lok');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TglRequest S/N');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TglPembubuhan');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NoBukti');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Ket');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Wilayah');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NamaPelanggan');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TotalTrans');
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Meterai');
						$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
					 
						$currcol = 1;
						$currrow+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $n);
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["IJINEMETERAI"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["KD_LOKASI"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["CDATE"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["TGL_TRANS"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["NO_BUKTI"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["KET"]));
						$currcol+= 1;
						if ($data[$i]["ISCANCELLED"]==0) {
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["WILAYAH"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["NM_PLG"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($data[$i]["KD_PLG"]));
						$currcol+= 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $data[$i]["TOTAL_BUKTI"]);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol+= 1;
						} else {
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, "BATAL : ".trim($data[$i]["CANCELLEDNOTE"]));
						$currcol+= 4;	
						$sheet->mergeCells("H".$currrow.":K".$currrow);
						}
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $data[$i]["EMETERAI_VALUE"]);	
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');					
					 
				}
				
				//SET NILAI AWAL GROUP LOKASI
				$LOK = trim($data[$i]["KD_LOKASI"]);
				$EMETERAI_COUNTER = 1;
				$TOTAL_EMETERAI = $data[$i]["EMETERAI_VALUE"];
				$METERAI_10000 = 1;
				if ($data[$i]["ISCANCELLED"]==0) {
					$TOTAL_SUKSES=1;
					$TOTAL_GAGAL=0;
				} else {
					$TOTAL_SUKSES=0;
					$TOTAL_GAGAL=1;
				}
				//SET NILAI AWAL GROUP LOKASI - END
			}
		}

		if ($IJIN!="" && $this->confirm_flag==1) {		
			$url = $this->API_URL."/Billing/SaveEMeteraiUsage?lok=".urlencode($LOK)."&ijin=".urlencode($IJIN)
				."&tgl1=".urlencode(date("m/d/Y",strtotime($p_tgl1)))."&tgl2=".urlencode(date("m/d/Y",strtotime($p_tgl2)))
				."&total=".urlencode($TOTAL_EMETERAI)."&met3=".urlencode($METERAI_3000)."&met6=".urlencode($METERAI_6000)."&met10=".urlencode($METERAI_10000)."&tipe=".urlencode("KWITANSI TAGIHAN")
				."&user=".urlencode($user);
			//die($url);
			$simpanData = json_decode(file_get_contents($url), true);
		}

		$group_footer = "	<tr style='background-color:#ffe53b;'>";
		$group_footer.= "		<td colspan='4'></td>";
		$group_footer.= "		<td colspan='2'><b>TOTAL METERAI ".$LOK.":".$METERAI_10000."</b></td>";
		$group_footer.= "		<td colspan='2'><b>SUKSES: ".$TOTAL_SUKSES."</b></td>";
		$group_footer.= "		<td colspan='2'><b>GAGAL: ".$TOTAL_GAGAL."</b></td>";
		$group_footer.= "		<td colspan='2' class='td-right'><b>".number_format($TOTAL_EMETERAI)."</b></td>";
		$group_footer.= "	</tr>";	//close div_column_header
		$group_footer.= "</table>";

		if ($this->excel_flag==1) {
			$currrow+= 1;
			$TABLEEND = $currrow;
			$currcol = 5;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "TOTAL ".$LOK.": ".$METERAI_10000);
			$currcol+= 2;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "SUKSES: ".$TOTAL_SUKSES);
			$currcol+= 2;			
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "GAGAL: ".$TOTAL_GAGAL);
			$currcol+= 2;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL_EMETERAI);	
			$currcol+= 1;
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$sheet->mergeCells('A'.$currrow.':D'.$currrow);
			$sheet->mergeCells('E'.$currrow.':F'.$currrow);
			$sheet->mergeCells('G'.$currrow.':H'.$currrow);
			$sheet->mergeCells('I'.$currrow.':J'.$currrow);
			$sheet->mergeCells('K'.$currrow.':L'.$currrow);
			
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

	public function Preview_PenggunaanMeteraiKomputerisasi($opt, $dateOpt, $p_tgl1, $p_tgl2, $data, $user, $lok="") 
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
		$header_html.= "	<div><h2>LAPORAN PEMAKAIAN BEA METERAI KOMPUTERISASI</h2></div>";
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
			$sheet->setCellValue('A1', 'LAPORAN PEMAKAIAN BEA METERAI KOMPUTERISASI');
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

				$ket = (($data[$i]["TYPE_TRANS"]=="CASH BEFORE")? "CASH BEFORE" : (($data[$i]["TYPE_TRANS"]=="INVOICE" or $data[$i]["TYPE_TRANS"]=="STAMPDUTYDOCS")? $data[$i]["NO_REF"] : "JT : ".date("d-M",strtotime($data[$i]["TGL_JATUHTEMPO"]))));

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

				$ket = (($data[$i]["TYPE_TRANS"]=="CASH BEFORE")? "CASH BEFORE" : (($data[$i]["TYPE_TRANS"]=="INVOICE" or $data[$i]["TYPE_TRANS"]=="STAMPDUTYDOCS")? $data[$i]["NO_REF"] : "JT : ".date("d-M",strtotime($data[$i]["TGL_JATUHTEMPO"]))));

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
					 
						$currcol = 1;
						$currrow+= 3;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'IJIN PEMBUBUHAN BEA METERAI :');
						$sheet->mergeCells('A'.$currrow.':C'.$currrow);
						$currcol = 4;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $data[$i]["IJINEMETERAI"]);
						$sheet->mergeCells('D'.$currrow.':E'.$currrow);
						$sheet->getStyle("A".$currrow.":E".$currrow)->getFont()->setBold(true);
		 
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
		
		if (count($data)<1)
		{
			exit("Tidak Ada Data");
		}
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

	public function Proses_PenggunaanMeteraiNew()
	{		
		$api = 'APITES';
		$th = $this->input->get("th");
		$bl = $this->input->get("bl");
		$DatabaseId = $this->input->get("db");
		$import = $this->input->get("import");

		// die($DatabaseId);
		// $paramDb = array();
		if ($DatabaseId==0) {
			if(!isset($_SESSION["conn"])) {
				redirect("ConnectDB");
			}
			$url = $_SESSION["conn"]->AlamatWebService;
			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;
			$branchId = $_SESSION["conn"]->BranchId;
		} else {
			$GetDb = $this->MasterDbModel->get($DatabaseId);
			$url = $GetDb->AlamatWebService;
			$svr = $GetDb->Server;
			$db  = $GetDb->Database; 
			$branchId = $GetDb->BranchId;
		}

		set_time_limit(300);
		$data = array();
		$data["branch"] = $branchId;
		$data["databaseId"] = $DatabaseId;
		$data["server"] = $svr;
		$data["database"] = $db;

		$data["meteraiNotStamped"] = array();
		$data["meteraiStamped"] = array();
		$data["meteraiCancelled"] = array();

		$streamContext = stream_context_create(
			array('http'=>array('timeout' => 300))
		);

		$url = HO.API_BKT."/ReportFinance/GetListEMeterai?api=".urlencode($api)."&jns=".urlencode("METERAI GANTUNG")
			."&th=".$th."&bl=".$bl
			."&svr=".urlencode($svr)."&db=".urlencode($db)."&uid=".SQL_UID."&pwd=".SQL_PWD;
		// die($url);
		$GetContents = @file_get_contents($url, false, $streamContext);
		if ($GetContents!=null) {
			$GetData = json_decode($GetContents, true);			
			if ($GetData["result"]=="sukses") {
				$data["meteraiNotStamped"] = $GetData["data"];
			}
		}
		// die(json_encode($data["meteraiNotStamped"]));

		$url = HO.API_BKT."/ReportFinance/GetListEMeterai?api=".urlencode($api)."&jns=".urlencode("METERAI STAMPED")
			."&th=".$th."&bl=".$bl
			."&svr=".urlencode($svr)."&db=".urlencode($db)."&uid=".SQL_UID."&pwd=".SQL_PWD;
		// die($url);
		$GetContents = @file_get_contents($url, false, $streamContext);
		if ($GetContents!=null) {
			$GetData = json_decode($GetContents, true);			
			if ($GetData["result"]=="sukses") {
				$data["meteraiStamped"] = $GetData["data"];
			}
		}
		// die(json_encode($data["meteraiStamped"]));

		$url = HO.API_BKT."/ReportFinance/GetListEMeterai?api=".urlencode($api)."&jns=".urlencode("METERAI CANCELLED")
			."&th=".$th."&bl=".$bl
			."&svr=".urlencode($svr)."&db=".urlencode($db)."&uid=".SQL_UID."&pwd=".SQL_PWD;
		// die($url);
		$GetContents = @file_get_contents($url, false, $streamContext);
		if ($GetContents!=null) {
			$GetData = json_decode($GetContents, true);			
			if ($GetData["result"]=="sukses") {
				$data["meteraiCancelled"] = $GetData["data"];
			}
		}
		// die(json_encode($data["meteraiCancelled"]));

		if ($import==1) {
			$result = array();
			$result["INSERTED"] = 0;
			$result["ALREADYLOCKED"] = 0;
			$result["MODIFIEDLOCKED"] = 0;
			$result["MODIFIED"] = 0;
			$result["TOTAL"] = 0;
			$result["STAMP"] = 0;
			$result["NOTSTAMP"] = 0;

			if(!empty($data["meteraiNotStamped"])){
				$simpan = $this->ReportModel->SimpanDataMeterai_Bhakti($data["meteraiNotStamped"]);
				$result["INSERTED"]+=$simpan["INSERTED"];
				$result["ALREADYLOCKED"]+=$simpan["ALREADYLOCKED"];
				$result["MODIFIEDLOCKED"]+=$simpan["MODIFIEDLOCKED"];
				$result["MODIFIED"]+=$simpan["MODIFIED"];
				$result["TOTAL"]+=$simpan["TOTAL"];
			}
			if(!empty($data["meteraiStamped"])){
				$simpan = $this->ReportModel->SimpanDataMeterai_Bhakti($data["meteraiStamped"]);
				$result["INSERTED"]+=$simpan["INSERTED"];
				$result["ALREADYLOCKED"]+=$simpan["ALREADYLOCKED"];
				$result["MODIFIEDLOCKED"]+=$simpan["MODIFIEDLOCKED"];
				$result["MODIFIED"]+=$simpan["MODIFIED"];
				$result["TOTAL"]+=$simpan["TOTAL"];
			}
			if(!empty($data["meteraiCancelled"])){
				$simpan = $this->ReportModel->SimpanDataMeterai_Bhakti($data["meteraiCancelled"]);
				$result["INSERTED"]+=$simpan["INSERTED"];
				$result["ALREADYLOCKED"]+=$simpan["ALREADYLOCKED"];
				$result["MODIFIEDLOCKED"]+=$simpan["MODIFIEDLOCKED"];
				$result["MODIFIED"]+=$simpan["MODIFIED"];
				$result["TOTAL"]+=$simpan["TOTAL"];
			}

			$simpan = $this->ReportModel->WriteLog($branchId, $th, $bl, $result);			

			$data["content_html"] = '<script language="javascript">';
	        $data["content_html"].= 'alert("';
	        $data["content_html"].= '	Data Meterai Berhasil Diimport\n';
	        $data["content_html"].= '	Branch:'.$branchId.'\n';
	        $data["content_html"].= '	Bulan/Tahun:'.$bl.'/'.$th.'\n';
	        $data["content_html"].= '	Total Record:'.number_format($result["TOTAL"]).'\n';
	        $data["content_html"].= '")';
	        $data["content_html"].= '</script>';
	        $data["content_html"].= "<script>window.close();</script>";					    	
	        $this->SetTemplate('template/notemplate');
		    $this->RenderView("CustomPageResult", $data);

		} else {
			$this->RenderView('ReportEMeteraiResult',$data);
		}	
	}
	
	public function CheckDocument()
	{		
		$api = 'APITES';
		$post = $this->PopulatePost();

		$DatabaseId = $post["DbId"];
		if ($DatabaseId==0) {
			if(!isset($_SESSION["conn"])) {
				redirect("ConnectDB");
			}
			$url = $_SESSION["conn"]->AlamatWebService;
			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;
			$branchId = $_SESSION["conn"]->BranchId;
		} else {
			$GetDb = $this->MasterDbModel->get($DatabaseId);
			$url = $GetDb->AlamatWebService;
			$svr = $GetDb->Server;
			$db  = $GetDb->Database; 
			$branchId = $GetDb->BranchId;
		}

		set_time_limit(60);
		$data = array();
		$url = HO.API_BKT."/ReportFinance/CekDokumenBermeterai?api=".urlencode($api)
			."&svr=".urlencode($svr)."&db=".urlencode($db)
			."&jns=".urlencode($post["DocType"])."&id=".urlencode($post["DocNo"]);
		// die($url);

		$GetContents = @file_get_contents($url);
		if ($GetContents==null) {
			echo(json_encode(array("result"=>"failed", "stamp"=>null, "err"=>"Gagal Meminta Data ke Database Bhakti")));
		} else {
			$GetData = json_decode($GetContents, true);
			
			if ($GetData["result"]=="sukses") {
				echo(json_encode(array("result"=>"success", "stamp"=>$GetData["data"], "err"=>"")));
			} else {
				echo(json_encode(array("result"=>"failed", "stamp"=>null, "err"=>$GetData["error"])));
			}
		}
	}


	public function CancelMeterai()
	{		
		$api = 'APITES';
		$post = $this->PopulatePost();

		$DatabaseId = $post["DbId"];
		if ($DatabaseId==0) {
			if(!isset($_SESSION["conn"])) {
				redirect("ConnectDB");
			}
			$url = $_SESSION["conn"]->AlamatWebService;
			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;
			$branchId = $_SESSION["conn"]->BranchId;
		} else {
			$GetDb = $this->MasterDbModel->get($DatabaseId);
			$url = $GetDb->AlamatWebService;
			$svr = $GetDb->Server;
			$db  = $GetDb->Database; 
			$branchId = $GetDb->BranchId;
		}

		set_time_limit(60);
		$data = array();
		$url = HO.API_BKT."/ReportFinance/CancelMeterai?api=".urlencode($api)
			."&svr=".urlencode($svr)."&db=".urlencode($db)."&sn=".urlencode($post["SN"])
			."&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD)."&user=".urlencode($_SESSION["logged_in"]["username"])
			."&jns=".urlencode($post["DocType"])."&id=".urlencode($post["DocNo"])
			."&ket=".urlencode($post["CancelNote"]);
		// die($url);

		$GetContents = @file_get_contents($url);
		if ($GetContents==null) {
			echo(json_encode(array("result"=>"failed", "stamp"=>"")));
		} else {
			$GetData = json_decode($GetContents, true);
			if ($GetData["result"]=="sukses") {
				$this->ReportModel->CancelMeterai($post["SN"], $post["CancelNote"]);
				echo(json_encode(array("result"=>"success", "stamp"=>$GetData["data"])));
			} else {
				echo(json_encode(array("result"=>"failed", "stamp"=>$GetData["error"])));
			}
		}
	}

	public function SetNotStamp()
	{		
		$api = 'APITES';
		$post = $this->PopulatePost();

		$DatabaseId = $post["DbId"];
		if ($DatabaseId==0) {
			if(!isset($_SESSION["conn"])) {
				redirect("ConnectDB");
			}
			$url = $_SESSION["conn"]->AlamatWebService;
			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;
			$branchId = $_SESSION["conn"]->BranchId;
		} else {
			$GetDb = $this->MasterDbModel->get($DatabaseId);
			$url = $GetDb->AlamatWebService;
			$svr = $GetDb->Server;
			$db  = $GetDb->Database; 
			$branchId = $GetDb->BranchId;
		}

		set_time_limit(60);
		$data = array();
	
		$url = HO.API_BKT."/ReportFinance/SetNotStamp?api=".urlencode($api)
			."&svr=".urlencode($svr)."&db=".urlencode($db)."&sn=".urlencode($post["SN"])
			."&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD)."&user=".urlencode($_SESSION["logged_in"]["username"])
			."&jns=".urlencode($post["DocType"])."&id=".urlencode($post["DocNo"]);
		// die($url);

		$GetContents = @file_get_contents($url);
		if ($GetContents==null) {
			echo(json_encode(array("result"=>"failed", "data"=>"")));
		} else {
			$GetData = json_decode($GetContents, true);
			
			if ($GetData["result"]=="sukses") {
				echo(json_encode(array("result"=>"success", "data"=>$GetData["data"])));
			} else {
				echo(json_encode(array("result"=>"failed", "data"=>"")));
			}
		}
	}

	public function SetStamp()
	{		
		$api = 'APITES';
		$post = $this->PopulatePost();

		$DatabaseId = $post["DbId"];
		if ($DatabaseId==0) {
			if(!isset($_SESSION["conn"])) {
				redirect("ConnectDB");
			}
			$url = $_SESSION["conn"]->AlamatWebService;
			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;
			$branchId = $_SESSION["conn"]->BranchId;
		} else {
			$GetDb = $this->MasterDbModel->get($DatabaseId);
			$url = $GetDb->AlamatWebService;
			$svr = $GetDb->Server;
			$db  = $GetDb->Database; 
			$branchId = $GetDb->BranchId;
		}

		set_time_limit(60);
		$data = array();
	
		$url = HO.API_BKT."/ReportFinance/SetStamp?api=".urlencode($api)
			."&svr=".urlencode($svr)."&db=".urlencode($db)."&sn=".urlencode($post["SN"])."&tgl=".urlencode($post["StampDate"])
			."&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD)."&user=".urlencode($_SESSION["logged_in"]["username"])
			."&jns=".urlencode($post["DocType"])."&id=".urlencode($post["DocNo"]);
		// die($url);

		$GetContents = @file_get_contents($url);
		if ($GetContents==null) {
			echo(json_encode(array("result"=>"failed", "data"=>"")));
		} else {
			$GetData = json_decode($GetContents, true);			
			if ($GetData["result"]=="sukses") {
				$this->ReportModel->SetStamp($post["SN"],$post["StampDate"]);
				echo(json_encode(array("result"=>"success", "data"=>$GetData["data"])));
			} else {
				echo(json_encode(array("result"=>"failed", "data"=>"")));
			}
		}
	}

	public function ChangeDoc()
	{		
		$api = 'APITES';
		$post = $this->PopulatePost();

		$DatabaseId = $post["DbId"];
		if ($DatabaseId==0) {
			if(!isset($_SESSION["conn"])) {
				redirect("ConnectDB");
			}
			$url = $_SESSION["conn"]->AlamatWebService;
			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;
			$branchId = $_SESSION["conn"]->BranchId;
		} else {
			$GetDb = $this->MasterDbModel->get($DatabaseId);
			$url = $GetDb->AlamatWebService;
			$svr = $GetDb->Server;
			$db  = $GetDb->Database; 
			$branchId = $GetDb->BranchId;
		}

		set_time_limit(60);
		$data = array();
		$url = HO.API_BKT."/ReportFinance/ChangeDoc?api=".urlencode($api)
			."&svr=".urlencode($svr)."&db=".urlencode($db)."&sn=".urlencode($post["SN"])
			."&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD)."&user=".urlencode($_SESSION["logged_in"]["username"])
			."&jns=".urlencode($post["DocType"])."&id=".urlencode($post["DocNo"])
			."&ket=".urlencode($post["CancelNote"]);
		// die($url);

		$GetContents = @file_get_contents($url);
		if ($GetContents==null) {
			echo(json_encode(array("result"=>"failed", "error"=>"Gagal Memanggil API Bhakti")));
		} else {
			$GetData = json_decode($GetContents, true);
			if ($GetData["result"]=="sukses") {
				$this->ReportModel->ChangeDoc($post["SN"], $post["CancelNote"]);
				echo(json_encode(array("result"=>"success", "error"=>$GetData["error"])));
			} else {
				echo(json_encode(array("result"=>"failed", "error"=>$GetData["error"])));
			}
		}
	}

	public function Remove()
	{		
		$api = 'APITES';
		$post = $this->PopulatePost();

		$DatabaseId = $post["DbId"];
		if ($DatabaseId==0) {
			if(!isset($_SESSION["conn"])) {
				redirect("ConnectDB");
			}
			$url = $_SESSION["conn"]->AlamatWebService;
			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;
			$branchId = $_SESSION["conn"]->BranchId;
		} else {
			$GetDb = $this->MasterDbModel->get($DatabaseId);
			$url = $GetDb->AlamatWebService;
			$svr = $GetDb->Server;
			$db  = $GetDb->Database; 
			$branchId = $GetDb->BranchId;
		}

		set_time_limit(60);
		$data = array();
		$url = HO.API_BKT."/ReportFinance/Remove?api=".urlencode($api)
			."&svr=".urlencode($svr)."&db=".urlencode($db)."&sn=".urlencode($post["SN"])
			."&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD)."&user=".urlencode($_SESSION["logged_in"]["username"])
			."&jns=".urlencode($post["DocType"])."&id=".urlencode($post["DocNo"])
			."&ket=".urlencode($post["CancelNote"]);
		// die($url);

		$GetContents = @file_get_contents($url);
		if ($GetContents==null) {
			echo(json_encode(array("result"=>"failed", "error"=>"Gagal Memanggil API Bhakti")));
		} else {
			$GetData = json_decode($GetContents, true);
			if ($GetData["result"]=="sukses") {
				$this->ReportModel->Remove($post["SN"], $post["CancelNote"]);
				echo(json_encode(array("result"=>"success", "error"=>$GetData["error"])));
			} else {
				echo(json_encode(array("result"=>"failed", "error"=>$GetData["error"])));
			}
		}
	}

	public function LoadLogs()
	{	
		$post = $this->PopulatePost();

		$List = $this->ReportModel->GetImportLogs($post["src"], $post["th"], $post["bl"]);
		//die(json_encode($List));
		if (count($List)>0) {
			$result["result"] = "sukses";
			$result["list"] = $List;
			$result["error"]  = "";
		} else {
			$result["result"] = "gagal";
			$result["list"] = array();
			$result["error"]  = "TIDAK ADA DATA";
		}
		
		$hasil = json_encode($result);
		header('HTTP/1.1: 200');
		header('Status: 200');
		header('Content-Length: '.strlen($hasil));
		exit($hasil);		
	}

	public function ImportSettlement()
	{
		$post = $this->PopulatePost();
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION); // Ambil ekstensi filenya apa
        $tmp_file = $_FILES['file']['tmp_name'];
        // die($tmp_file);

		$nama_file = "settlement_".$post["STahun"]."_".$post["SBulan"].".".$ext;

		// // Cek apakah terdapat file data.xlsx pada folder tmp
        if (is_file('upload/' . $nama_file)) // Jika file tersebut ada
            unlink('upload/' . $nama_file); // Hapus file tersebut

        // Cek apakah file yang diupload adalah file Excel 2007 (.xlsx)
        if ($ext == "xlsx") {
            // Upload file yang dipilih ke folder tmp
            // dan rename file tersebut menjadi data{tglsekarang}.xlsx
            // {tglsekarang} diganti jadi tanggal sekarang dengan format yyyymmddHHiiss
            // Contoh nama file setelah di rename : data20210814192500.xlsx
            copy($tmp_file, 'upload/'.$nama_file);
            // move_uploaded_file($tmp_file, 'tmp/' . $nama_file);
            
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load('upload/' . $nama_file); // Load file yang tadi diupload ke folder tmp
            $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);


			$result = array();
			$result["INSERTED"] = 0;
			$result["ALREADYLOCKED"] = 0;
			$result["MODIFIEDLOCKED"] = 0;
			$result["MODIFIED"] = 0;
			$result["TOTAL"] = 0;
			$result["STAMP"] = 0;
			$result["NOTSTAMP"] = 0;

            $param = array();
            $param["TAHUN"] = $post["STahun"];
            $param["BULAN"] = $post["SBulan"];
            $param["ID"] = date("YmdHis");

	    	$this->ReportModel->PreSimpanDataSettlement($param);

            $numrow = 0;
            foreach ($sheet as $row) { // Lakukan perulangan dari data yang ada di excel
                $numrow++; // Tambah 1 setiap kali looping

                // Ambil data pada excel sesuai Kolom
                $param["SN"] = $row['A']; 			// Ambil data Serial Number
                $param["STATUS"] = $row['B']; 		// Ambil data Status
                $param["DESC"] = $row['C']; 		// Ambil data Deskripsi
                $param["FILE"] = $row['D']; 		// Ambil data Nama File
                $param["TGL"] = $row['E']; 			// Ambil data Tgl
                $param["PRICE"] = $row['G']; 		// Ambil data Nilai Meterai

                // Cek jika semua data tidak diisi
                if ($numrow==1)
                    continue; // Lewat data pada baris ini (masuk ke looping selanjutnya / baris selanjutnya)
            	else if ($param["STATUS"]=="STAMP") 
            		continue;

            	$import = $this->ReportModel->SimpanDataSettlement($param);
				// $result["INSERTED"] += $import["INSERTED"];
				// $result["ALREADYLOCKED"] += $import["ALREADYLOCKED"];
				// $result["MODIFIEDLOCKED"] += $import["MODIFIEDLOCKED"];
				// $result["MODIFIED"] += $import["MODIFIED"];
				// $result["TOTAL"]++;
            }

            $numrow = 0;
            foreach ($sheet as $row) { // Lakukan perulangan dari data yang ada di excel
                $numrow++; // Tambah 1 setiap kali looping

                // Ambil data pada excel sesuai Kolom
                $param["SN"] = $row['A']; 			// Ambil data Serial Number
                $param["STATUS"] = $row['B']; 		// Ambil data Status
                $param["DESC"] = $row['C']; 		// Ambil data Deskripsi
                $param["FILE"] = $row['D']; 		// Ambil data Nama File
                $param["TGL"] = $row['E']; 			// Ambil data Tgl
                $param["PRICE"] = $row['G']; 		// Ambil data Nilai Meterai

                // Cek jika semua data tidak diisi
                if ($numrow==1)
                    continue; // Lewat data pada baris ini (masuk ke looping selanjutnya / baris selanjutnya)
            	else if ($param["STATUS"]=="NOTSTAMP") 
            		continue;

            	// $result["STAMP"]++;
            	$import = $this->ReportModel->SimpanDataSettlement($param);
				// $result["INSERTED"] += $import["INSERTED"];
				// $result["ALREADYLOCKED"] += $import["ALREADYLOCKED"];
				// $result["MODIFIEDLOCKED"] += $import["MODIFIEDLOCKED"];
				// $result["MODIFIED"] += $import["MODIFIED"];
				// $result["TOTAL"]++;
            }            

            $summary = $this->ReportModel->HitungSettlementSummary($param);
            if ($summary==null) {
	            $result["STAMP"] = 0;
	            $result["NOTSTAMP"] = 0;
	        } else {
	            $result["STAMP"] = $summary->TOTAL_STAMP;
	            $result["NOTSTAMP"] = $summary->TOTAL_NOT_STAMP;
	        }
	        $result["TOTAL"] = $result["STAMP"] + $result["NOTSTAMP"];

			$simpan = $this->ReportModel->WriteLog("SETTLEMENT", $param["TAHUN"], $param["BULAN"], $result);			

			$data["content_html"] = '<script language="javascript">';
	        $data["content_html"].= 'alert("';
	        $data["content_html"].= '	Data Settlement Berhasil Diimport\n';
	        $data["content_html"].= '	Bulan/Tahun:'.$param["BULAN"].'/'.$param["TAHUN"].'\n';
	        $data["content_html"].= '	Total RECORD:'.number_format($result["TOTAL"]).'\n';
	        $data["content_html"].= '	Total STAMP:'.number_format($result["STAMP"]).'\n';
	        $data["content_html"].= '	Total NOT STAMP:'.number_format($result["NOTSTAMP"]).'\n';
	        $data["content_html"].= '")';
	        $data["content_html"].= '</script>';
	        $data["content_html"].= "<script>window.close();</script>";					    	
	        $this->SetTemplate('template/notemplate');
		    $this->RenderView("CustomPageResult", $data);
        }
	}

	public function PreviewDataGabungan()
	{		
		$api = 'APITES';
		$th = $this->input->get("th");
		$bl = $this->input->get("bl");

		set_time_limit(60);
		$data = array();
		$data["BULAN"] = $bl;
		$data["TAHUN"] = $th; 

		$data["SETTLEMENTONLYSTAMP"] = $this->ReportModel->GetDataGabungan($th, $bl, "SETTLEMENT ONLY STAMP");
		$data["SETTLEMENTONLYNOTSTAMP"] = $this->ReportModel->GetDataGabungan($th, $bl, "SETTLEMENT ONLY NOT STAMP");
		$data["SETTLEMENTNOTSTAMP"] = $this->ReportModel->GetDataGabungan($th, $bl, "SETTLEMENT NOT STAMP BHAKTI STAMP");
		$data["SETTLEMENTSTAMP"] = $this->ReportModel->GetDataGabungan($th, $bl, "SETTLEMENT STAMP BHAKTI NOT STAMP");
		$data["BOTHNOTSTAMP"] = $this->ReportModel->GetDataGabungan($th, $bl, "BOTH NOT STAMP");
		$data["BOTHSTAMP"] = $this->ReportModel->GetDataGabungan($th, $bl, "BOTH STAMP");
		$data["BHAKTIONLYSTAMP"] = $this->ReportModel->GetDataGabungan($th, $bl, "BHAKTI ONLY STAMP");
		$data["BHAKTIONLYNOTSTAMP"] = $this->ReportModel->GetDataGabungan($th, $bl, "BHAKTI ONLY NOT STAMP");

		$this->RenderView('ReportEMeteraiGabunganResult',$data);
	}

	public function ExcelDataGabungan()
	{		
		$api = 'APITES';
		$th = $this->input->get("th");
		$bl = $this->input->get("bl");
		
		set_time_limit(60);
		$data = array();
		$data["BULAN"] = $bl;
		$data["TAHUN"] = $th; 

		$data["SETTLEMENTONLYSTAMP"] = $this->ReportModel->GetDataGabungan($th, $bl, "SETTLEMENT ONLY STAMP");
		$data["SETTLEMENTONLYNOTSTAMP"] = $this->ReportModel->GetDataGabungan($th, $bl, "SETTLEMENT ONLY NOT STAMP");
		$data["SETTLEMENTNOTSTAMP"] = $this->ReportModel->GetDataGabungan($th, $bl, "SETTLEMENT NOT STAMP BHAKTI STAMP");
		$data["SETTLEMENTSTAMP"] = $this->ReportModel->GetDataGabungan($th, $bl, "SETTLEMENT STAMP BHAKTI NOT STAMP");
		$data["BOTHNOTSTAMP"] = $this->ReportModel->GetDataGabungan($th, $bl, "BOTH NOT STAMP");
		$data["BOTHSTAMP"] = $this->ReportModel->GetDataGabungan($th, $bl, "BOTH STAMP");
		$data["BHAKTIONLYSTAMP"] = $this->ReportModel->GetDataGabungan($th, $bl, "BHAKTI ONLY STAMP");
		$data["BHAKTIONLYNOTSTAMP"] = $this->ReportModel->GetDataGabungan($th, $bl, "BHAKTI ONLY NOT STAMP");

		$TOTAL_STAMP = count($data["SETTLEMENTSTAMP"])+count($data["SETTLEMENTONLYSTAMP"])+count($data["BOTHSTAMP"]);
		$TOTAL_NOTSTAMP = count($data["SETTLEMENTNOTSTAMP"])+count($data["SETTLEMENTONLYNOTSTAMP"])+count($data["BOTHNOTSTAMP"]);
		$TOTAL_METERAI = $TOTAL_STAMP+$TOTAL_NOTSTAMP;
		
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);
		$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
		$vertical_top = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP;
		
		$borderStyleArray = [
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
			],
		];
		
		$this->excel->setActiveSheetIndex(0);
		$sheet->setTitle('Meterai Elektronik + Settlement');
		$sheet->setCellValue('A1', 'SUMMARY SETTLEMENT PERIODE: '.$bl.' / '.$th);
		$sheet->setCellValue('A2', 'TOTAL METERAI : '.number_format($TOTAL_METERAI));
		
		$sheet->setCellValue('A3', 'TOTAL SETTLEMENT ONLY SUDAH STAMP: '.number_format(count($data["SETTLEMENTONLYSTAMP"])));
		$sheet->setCellValue('A4', 'TOTAL SETTLEMENT SUDAH STAMP - BHAKTI BELUM: '.number_format(count($data["SETTLEMENTSTAMP"])));
		$sheet->setCellValue('A5', 'TOTAL SETTLEMENT & BHAKTI SUDAH STAMP: '.number_format(count($data["BOTHSTAMP"])));
		$sheet->setCellValueExplicit("A6", "==========================================================================================",PHPExcel_Cell_DataType::TYPE_STRING);
		$sheet->setCellValue('A7', 'TOTAL SETTLEMENT SUDAH STAMP: '.number_format($TOTAL_STAMP));
		
		$sheet->setCellValue('F3', 'TOTAL SETTLEMENT ONLY BELUM STAMP: '.number_format(count($data["SETTLEMENTONLYNOTSTAMP"])));
		$sheet->setCellValue('F4', 'TOTAL SETTLEMENT BELUM STAMP - BHAKTI SUDAH: '.number_format(count($data["SETTLEMENTNOTSTAMP"])));
		$sheet->setCellValue('F5', 'TOTAL SETTLEMENT & BHAKTI BELUM STAMP: '.number_format(count($data["BOTHNOTSTAMP"])));
		$sheet->setCellValueExplicit("F6", "==========================================================================================",PHPExcel_Cell_DataType::TYPE_STRING);
		$sheet->setCellValue('F7', 'TOTAL SETTLEMENT BELUM STAMP: '.number_format($TOTAL_NOTSTAMP));
		
		
		$TOTAL_STAMP = count($data["BHAKTIONLYSTAMP"])+count($data["SETTLEMENTNOTSTAMP"])+count($data["BOTHSTAMP"]);
		$TOTAL_NOTSTAMP = count($data["BHAKTIONLYNOTSTAMP"])+count($data["SETTLEMENTSTAMP"])+count($data["BOTHNOTSTAMP"]);
		$TOTAL_METERAI = $TOTAL_STAMP+$TOTAL_NOTSTAMP;
		$sheet->setCellValue('A9', 'SUMMARY BHAKTI PERIODE: '.$bl.' / '.$th);
		$sheet->setCellValue('A10', 'TOTAL METERAI : '.number_format($TOTAL_METERAI));
			
		$sheet->setCellValue('A11', 'TOTAL BHAKTI SUDAH STAMP: '.number_format($TOTAL_STAMP));
		$sheet->setCellValue('F11', 'TOTAL BHAKTI BELUM STAMP: '.number_format($TOTAL_NOTSTAMP));
		
		
		$sheet->getStyle('A1')->getFont()->setSize(20);
		$sheet->getStyle('A2')->getFont()->setSize(20);
		$sheet->getStyle('A9')->getFont()->setSize(20);
		$sheet->getStyle('A10')->getFont()->setSize(20);
		

		$currrow = 12;
		
		$currcol = 0;
		$currrow+= 2;
		$currcol+= 1;
		$sheet->setCellValue('A'.$currrow, 'HANYA ADA DI SETTLEMENT SUDAH STAMP');
		$sheet->getStyle('A'.$currrow)->getFont()->setSize(20);
		
		$currcol = 0;
		$currrow+= 1;
		$startrow = $currrow;
		
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SERIAL NUMBER');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'CREATED DATE');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'JENIS');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA FILE');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'STATUS');
		$sheet->getStyle("A".$currrow.":Z".$currrow)->getFont()->setBold(true);


		$no = 0;
		$rows = $data['SETTLEMENTONLYSTAMP'];
		foreach($rows as $d) {
			$no++;
			$currcol = 0;
			$currrow+= 1;
			
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->EMeterai_SN);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-M-Y", strtotime($d->Settlement_Date)));
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Settlement_Desc);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Settlement_File);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Settlement_Status);
		}
		
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->applyFromArray($borderStyleArray);
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setWrapText(true);
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setVertical($vertical_top);
		
		$currcol = 0;
		$currrow+= 2;
		$currcol+= 1;
		$sheet->setCellValue('A'.$currrow, 'HANYA ADA DI SETTLEMENT BELUM STAMP');
		$sheet->getStyle('A'.$currrow)->getFont()->setSize(20);
		
		$currcol = 0;
		$currrow+= 1;
		$startrow = $currrow;
		
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SERIAL NUMBER');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'CREATED DATE');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'JENIS');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA FILE');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'STATUS');
		$sheet->getStyle("A".$currrow.":Z".$currrow)->getFont()->setBold(true);
		
		$no = 0;
		$rows = $data['SETTLEMENTONLYNOTSTAMP'];
		foreach($rows as $d) { 
		
			$no++;
			$currcol = 0;
			$currrow+= 1;
			
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->EMeterai_SN);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-M-Y", strtotime($d->Settlement_Date)));
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Settlement_Desc);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Settlement_File);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Settlement_Status);
		}
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->applyFromArray($borderStyleArray);
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setWrapText(true);
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setVertical($vertical_top);

		$currcol = 0;
		$currrow+= 2;
		$currcol+= 1;
		$sheet->setCellValue('A'.$currrow, 'SETTLEMENT BELUM STAMP - BHAKTI SUDAH STAMP');
		$sheet->getStyle('A'.$currrow)->getFont()->setSize(20);
		
		$currcol = 0;
		$currrow+= 1;
		$startrow = $currrow;
		
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SERIAL NUMBER');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'CREATED DATE');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'JENIS');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA FILE');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO DOKUMEN');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'LAWAN TRANSAKSI');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NILAI DOKUMEN');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'STAMPING DATE');
		$sheet->getStyle("A".$currrow.":Z".$currrow)->getFont()->setBold(true);

		$no = 0;
		$rows = $data['SETTLEMENTNOTSTAMP'];
		foreach($rows as $d) { 
		
			$no++;
			$currcol = 0;
			$currrow+= 1;
			
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->EMeterai_SN);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-M-Y", strtotime($d->Settlement_Date)));
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Settlement_Desc);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Settlement_File);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Document_No);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->NamaLawanTransaksi."\n".$d->KodeLawanTransaksi."\n".$d->NPWPLawanTransaksi);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Document_Value);
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->EMeterai_StampingDate);
		}
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->applyFromArray($borderStyleArray);
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setWrapText(true);
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setVertical($vertical_top);
		
		$currcol = 0;
		$currrow+= 2;
		$currcol+= 1;
		$sheet->setCellValue('A'.$currrow, 'SETTLEMENT SUDAH STAMP - BHAKTI BELUM STAMP');
		$sheet->getStyle('A'.$currrow)->getFont()->setSize(20);
		
		$currcol = 0;
		$currrow+= 1;
		$startrow = $currrow;
		
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SERIAL NUMBER');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'CREATED DATE');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'JENIS');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA FILE');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO DOKUMEN');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'LAWAN TRANSAKSI');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NILAI DOKUMEN');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'CATATAN');
		$sheet->getStyle("A".$currrow.":Z".$currrow)->getFont()->setBold(true);

		$no = 0;
		$rows = $data['SETTLEMENTSTAMP'];
		foreach($rows as $d) { 
		
			$no++;
			$currcol = 0;
			$currrow+= 1;
			
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->EMeterai_SN);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-M-Y", strtotime($d->Settlement_Date)));
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Settlement_Desc);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Settlement_File);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Document_No);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->NamaLawanTransaksi."\n".$d->KodeLawanTransaksi."\n".$d->NPWPLawanTransaksi);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Document_Value);
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->ErrorCode.":".$d->ErrorMessage);
		}
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->applyFromArray($borderStyleArray);
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setWrapText(true);
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setVertical($vertical_top);

		$currcol = 0;
		$currrow+= 2;
		$currcol+= 1;
		$sheet->setCellValue('A'.$currrow, 'SETTLEMENT & BHAKTI BELUM STAMP');
		$sheet->getStyle('A'.$currrow)->getFont()->setSize(20);
		
		$currcol = 0;
		$currrow+= 1;
		$startrow = $currrow;
		
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SERIAL NUMBER');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'CREATED DATE');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'JENIS');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA FILE');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO DOKUMEN');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'LAWAN TRANSAKSI');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NILAI DOKUMEN');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'CATATAN');
		$sheet->getStyle("A".$currrow.":Z".$currrow)->getFont()->setBold(true);
		
		$no = 0;
		$rows = $data['BOTHNOTSTAMP'];
		foreach($rows as $d) { 
		
			$no++;
			$currcol = 0;
			$currrow+= 1;
			
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->EMeterai_SN);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-M-Y", strtotime($d->Settlement_Date)));
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Settlement_Desc);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Settlement_File);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Document_No);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->NamaLawanTransaksi."\n".$d->KodeLawanTransaksi."\n".$d->NPWPLawanTransaksi);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Document_Value);
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->ErrorCode.":".$d->ErrorMessage);
		}
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->applyFromArray($borderStyleArray);
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setWrapText(true);
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setVertical($vertical_top);
		
		$currcol = 0;
		$currrow+= 2;
		$currcol+= 1;
		$sheet->setCellValue('A'.$currrow, 'SETTLEMENT & BHAKTI SUDAH STAMP');
		$sheet->getStyle('A'.$currrow)->getFont()->setSize(20);
		
		$currcol = 0;
		$currrow+= 1;
		$startrow = $currrow;
		
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SERIAL NUMBER');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'CREATED DATE');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'JENIS');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA FILE');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO DOKUMEN');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'LAWAN TRANSAKSI');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL DOKUMEN');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'STAMPING DATE');
		$sheet->getStyle("A".$currrow.":Z".$currrow)->getFont()->setBold(true);
		
		$no = 0;
		$rows = $data['BOTHSTAMP'];
		foreach($rows as $d) { 
		
			$no++;
			$currcol = 0;
			$currrow+= 1;
			
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->EMeterai_SN);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-M-Y", strtotime($d->Settlement_Date)));
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Settlement_Desc);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Settlement_File);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Document_No);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->NamaLawanTransaksi."\n".$d->KodeLawanTransaksi."\n".$d->NPWPLawanTransaksi);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Document_Value);
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->EMeterai_StampingDate);
		}
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->applyFromArray($borderStyleArray);
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setWrapText(true);
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setVertical($vertical_top);

		$currcol = 0;
		$currrow+= 2;
		$currcol+= 1;
		$sheet->setCellValue('A'.$currrow, 'HANYA ADA DI BHAKTI SUDAH STAMP');
		$sheet->getStyle('A'.$currrow)->getFont()->setSize(20);
		
		$currcol = 0;
		$currrow+= 1;
		$startrow = $currrow;
		
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SERIAL NUMBER');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'CREATED DATE');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'JENIS');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO DOKUMEN');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'LAWAN TRANSAKSI');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL DOKUMEN');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'STAMPING DATE');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA FILE');
		$sheet->getStyle("A".$currrow.":Z".$currrow)->getFont()->setBold(true);
		
		$no = 0;
		$rows = $data['BHAKTIONLYSTAMP'];
		foreach($rows as $d) { 
		
			$no++;
			$currcol = 0;
			$currrow+= 1;
			
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->EMeterai_SN);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->EMeterai_RequestDate);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Document_Type);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Document_No);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->NamaLawanTransaksi."\n".$d->KodeLawanTransaksi."\n".$d->NPWPLawanTransaksi);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Document_Value);
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->EMeterai_StampingDate);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Document_FileName);
		}
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->applyFromArray($borderStyleArray);
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setWrapText(true);
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setVertical($vertical_top);
		
		$currcol = 0;
		$currrow+= 2;
		$currcol+= 1;
		$sheet->setCellValue('A'.$currrow, 'HANYA ADA DI BHAKTI BELUM STAMP');
		$sheet->getStyle('A'.$currrow)->getFont()->setSize(20);
		
		$currcol = 0;
		$currrow+= 1;
		$startrow = $currrow;
		
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SERIAL NUMBER');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'CREATED DATE');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'JENIS');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO DOKUMEN');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'LAWAN TRANSAKSI');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL DOKUMEN');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'STAMPING DATE');
		$currcol+= 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA FILE');
		
		$sheet->getStyle("A".$currrow.":Z".$currrow)->getFont()->setBold(true);
		
		$no = 0;
		$rows = $data['BHAKTIONLYNOTSTAMP'];
		foreach($rows as $d) { 
		
			$no++;
			$currcol = 0;
			$currrow+= 1;
			
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->EMeterai_SN);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->EMeterai_RequestDate);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Document_Type);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Document_No);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->NamaLawanTransaksi."\n".$d->KodeLawanTransaksi."\n".$d->NPWPLawanTransaksi);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Document_Value);
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->EMeterai_StampingDate);
			$currcol+= 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $d->Document_FileName);
		}
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->applyFromArray($borderStyleArray);
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setWrapText(true);
		$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setVertical($vertical_top);

		$sheet->getColumnDimension('A')->setWidth(5);
		$sheet->getColumnDimension('B')->setWidth(28);
		$sheet->getColumnDimension('C')->setWidth(25);
		$sheet->getColumnDimension('D')->setWidth(20);
		$sheet->getColumnDimension('E')->setWidth(20);
		$sheet->getColumnDimension('F')->setWidth(30);
		$sheet->getColumnDimension('G')->setWidth(20);
		$sheet->getColumnDimension('H')->setWidth(25);
		$sheet->getColumnDimension('I')->setAutoSize(true);

		
		$filename='MeteraiElektronik&Settlement['.$th.']['.$bl.']'; //save our workbook as this file name
		$writer = new Xlsx($spreadsheet);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
		header('Cache-Control: max-age=0');
		ob_end_clean();
	
		$writer->save('php://output');	// download file 
		exit();
	}

	public function PreviewDataGabunganPajakku()
	{		
		$api = 'APITES';
		$th = $this->input->get("th");
		$bl = $this->input->get("bl");

		set_time_limit(60);
		$data = array();
		$data["BULAN"] = $bl;
		$data["TAHUN"] = $th;
 
  
		$data["ALLBHAKTISTAMP"] = $this->ReportModel->GetDataGabunganPajakku($th, $bl, "BHAKTI ALL STAMP");
		$data["ALLBHAKTINOTSTAMP"] = $this->ReportModel->GetDataGabunganPajakku($th, $bl, "BHAKTI ALL NOT STAMP");

		$this->RenderView('ReportEMeteraiGabunganPajakkuResult',$data);
	}

	public function ExcelDataGabunganPajakku()
	{		
		$api = 'APITES';
		$th = $this->input->get("th");
		$bl = $this->input->get("bl");
		   
		$ALLBHAKTISTAMP = $this->ReportModel->GetDataGabunganPajakku($th, $bl, "BHAKTI ALL STAMP");
		$ALLBHAKTINOTSTAMP = $this->ReportModel->GetDataGabunganPajakku($th, $bl, "BHAKTI ALL NOT STAMP"); 
		
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);
		$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
		$vertical_top = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP; 
		$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID; 
		
		$borderStyleArray = [
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
			],
		];

		$this->excel->setActiveSheetIndex(0);
		$sheet->setTitle('Dokumen Penerimaan Uang');

		$sheet->getColumnDimension('B')->setWidth(5); 
		$sheet->getColumnDimension('C')->setWidth(20); 
		$sheet->getColumnDimension('D')->setWidth(20); 
		$sheet->getColumnDimension('E')->setWidth(20); 
		$sheet->getColumnDimension('F')->setWidth(20); 
		$sheet->getColumnDimension('G')->setWidth(20); 
		$sheet->getColumnDimension('H')->setWidth(20); 
		$sheet->getColumnDimension('I')->setWidth(20); 
		$sheet->getColumnDimension('J')->setWidth(20); 
		$sheet->getColumnDimension('K')->setWidth(20); 
		$sheet->getColumnDimension('L')->setWidth(20); 

		$sheet->setCellValue('B2', 'NAMA PERUSAHAAN : ');
		$sheet->setCellValue('D2', WEBTITLE); 
		$sheet->setCellValue('B3', 'BULAN : '); 
		$sheet->setCellValue('D3', date('F', strtotime(date('d-'.$bl).'-Y'))); 
		$sheet->setCellValue('B4', 'TAHUN : ');
		$sheet->setCellValue('D4', $th);
		$sheet->mergeCells('B2:C2');
		$sheet->mergeCells('B3:C3');
		$sheet->mergeCells('B4:C4');
 
		$sheet->setCellValue('B6', 'KETERANGAN');
		$sheet->setCellValue('E6', 'JUMLAH'); 

		$sheet->getStyle('B6:E6')->getAlignment()->setHorizontal($alignment_center);
		$sheet->getStyle('B6:E6')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('FFFACD');
		$sheet->getStyle('B2:E6')->getFont()->setBold(true);

		$sheet->setCellValue('B7', 'Jumlah Keping Material Awal Bulan');
		$sheet->setCellValue('E7', '');
		$sheet->setCellValue('B8', 'Total Permintaan Kuota Ke Pajakku');
		$sheet->setCellValue('E8', '');
		$sheet->setCellValue('B9', 'Total Berhasil Stamping');
		$sheet->setCellValue('E9', count($ALLBHAKTISTAMP));
		$sheet->setCellValue('B10', 'Total Gagal Stamping');
		$sheet->setCellValue('E10', count($ALLBHAKTINOTSTAMP));
		$sheet->getStyle('E9:E10')->getAlignment()->setHorizontal($alignment_center);
		$sheet->getStyle('E9:E10')->getFont()->setBold(true);
		$sheet->setCellValue('B11', 'Jumlah Keping Material Akhir Bulan');
		$sheet->setCellValue('E11', '');

		$indexCode = 6;
		for ($i=0; $i < 6; $i++) {   
			$sheet->mergeCells('B'.$indexCode.':D'.$indexCode);  
			$this->setBorderStyle($sheet,'B'.$indexCode); 
			$this->setBorderStyle($sheet,'C'.$indexCode); 
			$this->setBorderStyle($sheet,'D'.$indexCode); 
			$this->setBorderStyle($sheet,'E'.$indexCode);  
			$indexCode++;
		} 
 
		$sheet->setCellValue('B13', '* Nilai yang dicantumkan dalam laporan ini adalah data untuk bulan yang dilaporkan'); 
		$sheet->setCellValue('B14', '** Jumlah Keping Material Akhir Bulan/Saat Laporan Dibuat Juga dikirimkan dalam bentuk lampiran gambar'); 
		$sheet->setCellValue('B15', '*** Total Gagal Stamping Wajib Di Detailkan Pada Format Dibawah Ini'); 

		$styleArray = array(
		    'font'  => array( 
        		'bold'  => true,
		        'color' => array('rgb' => 'FF0000'),  
		    ));

		$sheet->getStyle('B13:B15')->applyFromArray($styleArray); 



		//DETAIL DATA GAGAL STAMPING
		$sheet->setCellValue('B17', 'DETAIL DATA GAGAL STAMPING'); 

		$sheet->mergeCells('B17:L17'); 
		$sheet->getStyle('B17')->getFont()->setBold(true); 
		$sheet->getStyle('B17')->getAlignment()->setHorizontal($alignment_center);
		$sheet->getStyle('B17')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('FFFACD'); 
		$this->setBorderStyle($sheet,'B17:L17');

		$sheet->setCellValue('B18', 'No'); 
		$sheet->setCellValue('C18', 'Nama Dokumen'); 
		$sheet->setCellValue('D18', 'Status Stamping');  
		$sheet->setCellValue('E18', 'Pesan Stamping'); 
		$sheet->setCellValue('F18', 'No Dokumen'); 
		$sheet->setCellValue('G18', 'Jenis Dokumen'); 
		$sheet->setCellValue('H18', 'Tgl Dokumen'); 
		$sheet->setCellValue('I18', 'Serial Number'); 
		$sheet->setCellValue('J18', 'Checksum'); 
		$sheet->setCellValue('K18', 'Dibuat oleh'); 
		$sheet->setCellValue('L18', 'Dibuat Tanggal'); 
		$this->setBorderStyle($sheet,'B18'); 
		$this->setBorderStyle($sheet,'C18'); 
		$this->setBorderStyle($sheet,'D18'); 
		$this->setBorderStyle($sheet,'E18'); 
		$this->setBorderStyle($sheet,'F18'); 
		$this->setBorderStyle($sheet,'G18');  
		$this->setBorderStyle($sheet,'H18');  
		$this->setBorderStyle($sheet,'I18');  
		$this->setBorderStyle($sheet,'J18');  
		$this->setBorderStyle($sheet,'K18');  
		$this->setBorderStyle($sheet,'L18');    
		$sheet->getStyle('B18:L18')->getFont()->setBold(true); 
		$sheet->getStyle('B18:L18')->getAlignment()->setHorizontal($alignment_center);
		$sheet->getStyle('B17:L18')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('FFFACD'); 

		$currentColumn = 19; 
		$indexno = 1;
		foreach ($ALLBHAKTINOTSTAMP as $key => $value) { 

			if ($value->Settlement_Status==null || $value->Settlement_Status =="")
			{
				$value->Settlement_Status = "NOTSTAMP";
			}

			$sheet->setCellValue('B'.$currentColumn, $indexno); 
			$sheet->setCellValue('C'.$currentColumn, $value->Document_FileName); 
			$sheet->setCellValue('D'.$currentColumn, $value->Settlement_Status); 
			$sheet->setCellValue('E'.$currentColumn, $value->ErrorMessage); 
			$sheet->setCellValue('F'.$currentColumn, $value->Document_No); 
			$sheet->setCellValue('G'.$currentColumn, $value->Document_Type); 
			$sheet->setCellValue('H'.$currentColumn, date("d-M-Y",strtotime($value->Document_Date))); 
			$sheet->setCellValue('I'.$currentColumn, $value->EMeterai_SN); 
			$sheet->setCellValue('J'.$currentColumn, ''); 
			$sheet->setCellValue('K'.$currentColumn, $value->EMeterai_RequestBy); 
			$sheet->setCellValue('L'.$currentColumn, date("d-M-Y",strtotime($value->EMeterai_StampingDate))); 
 
			$this->setBorderStyle($sheet,'B'.$currentColumn); 
			$this->setBorderStyle($sheet,'C'.$currentColumn);
			$this->setBorderStyle($sheet,'D'.$currentColumn);
			$this->setBorderStyle($sheet,'E'.$currentColumn); 
			$this->setBorderStyle($sheet,'F'.$currentColumn); 
			$this->setBorderStyle($sheet,'G'.$currentColumn);  
			$this->setBorderStyle($sheet,'H'.$currentColumn); 
			$this->setBorderStyle($sheet,'I'.$currentColumn);  
			$this->setBorderStyle($sheet,'J'.$currentColumn); 
			$this->setBorderStyle($sheet,'K'.$currentColumn); 
			$this->setBorderStyle($sheet,'L'.$currentColumn);

			$currentColumn ++;
			$indexno++;
		}

		$currentColumn++;
		//DETAIL DATA BERHSASIL STAMPING
		$sheet->setCellValue('B'.$currentColumn, 'DETAIL DATA BERHASIL STAMPING'); 

		$sheet->mergeCells('B'.$currentColumn.':L'.$currentColumn); 
		$sheet->getStyle('B'.$currentColumn)->getFont()->setBold(true); 
		$sheet->getStyle('B'.$currentColumn)->getAlignment()->setHorizontal($alignment_center);
		$sheet->getStyle('B'.$currentColumn)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('FFFACD'); 
		$this->setBorderStyle($sheet,'B'.$currentColumn.':L'.$currentColumn);  

		$currentColumn++;
		$sheet->setCellValue('B'.$currentColumn, 'No'); 
		$sheet->setCellValue('C'.$currentColumn, 'Nama Dokumen'); 
		$sheet->setCellValue('D'.$currentColumn, 'Status Stamping');  
		$sheet->setCellValue('E'.$currentColumn, 'Pesan Stamping'); 
		$sheet->setCellValue('F'.$currentColumn, 'No Dokumen'); 
		$sheet->setCellValue('G'.$currentColumn, 'Jenis Dokumen'); 
		$sheet->setCellValue('H'.$currentColumn, 'Tgl Dokumen'); 
		$sheet->setCellValue('I'.$currentColumn, 'Serial Number'); 
		$sheet->setCellValue('J'.$currentColumn, 'Checksum'); 
		$sheet->setCellValue('K'.$currentColumn, 'Dibuat oleh'); 
		$sheet->setCellValue('L'.$currentColumn, 'Dibuat Tanggal'); 
		$this->setBorderStyle($sheet,'B'.$currentColumn); 
		$this->setBorderStyle($sheet,'C'.$currentColumn); 
		$this->setBorderStyle($sheet,'D'.$currentColumn); 
		$this->setBorderStyle($sheet,'E'.$currentColumn); 
		$this->setBorderStyle($sheet,'F'.$currentColumn); 
		$this->setBorderStyle($sheet,'G'.$currentColumn);  
		$this->setBorderStyle($sheet,'H'.$currentColumn);  
		$this->setBorderStyle($sheet,'I'.$currentColumn);  
		$this->setBorderStyle($sheet,'J'.$currentColumn);  
		$this->setBorderStyle($sheet,'K'.$currentColumn);  
		$this->setBorderStyle($sheet,'L'.$currentColumn);   
		$crcol =  $currentColumn-1;
		$sheet->getStyle('B'.$currentColumn.':L'.$currentColumn)->getFont()->setBold(true); 
		$sheet->getStyle('B'.$currentColumn.':L'.$currentColumn)->getAlignment()->setHorizontal($alignment_center);
		$sheet->getStyle('B'.$crcol.':L'.$currentColumn)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('FFFACD'); 

		$currentColumn++; 
		$indexno = 1;
		foreach ($ALLBHAKTISTAMP as $key => $value) { 

			if ($value->ErrorMessage!=null || $value->ErrorMessage !="")
			{
				$value->ErrorMessage = "";
			}

			$sheet->setCellValue('B'.$currentColumn, $indexno); 
			$sheet->setCellValue('C'.$currentColumn, $value->Document_FileName); 
			$sheet->setCellValue('D'.$currentColumn, $value->Settlement_Status); 
			$sheet->setCellValue('E'.$currentColumn, $value->ErrorMessage); 
			$sheet->setCellValue('F'.$currentColumn, $value->Document_No); 
			$sheet->setCellValue('G'.$currentColumn, $value->Document_Type); 
			$sheet->setCellValue('H'.$currentColumn, date("d-M-Y",strtotime($value->Document_Date))); 
			$sheet->setCellValue('I'.$currentColumn, $value->EMeterai_SN); 
			$sheet->setCellValue('J'.$currentColumn, ''); 
			$sheet->setCellValue('K'.$currentColumn, $value->EMeterai_RequestBy); 
			$sheet->setCellValue('L'.$currentColumn, date("d-M-Y",strtotime($value->EMeterai_StampingDate))); 
 
			$this->setBorderStyle($sheet,'B'.$currentColumn); 
			$this->setBorderStyle($sheet,'C'.$currentColumn);
			$this->setBorderStyle($sheet,'D'.$currentColumn);
			$this->setBorderStyle($sheet,'E'.$currentColumn); 
			$this->setBorderStyle($sheet,'F'.$currentColumn); 
			$this->setBorderStyle($sheet,'G'.$currentColumn);  
			$this->setBorderStyle($sheet,'H'.$currentColumn); 
			$this->setBorderStyle($sheet,'I'.$currentColumn);  
			$this->setBorderStyle($sheet,'J'.$currentColumn); 
			$this->setBorderStyle($sheet,'K'.$currentColumn); 
			$this->setBorderStyle($sheet,'L'.$currentColumn);

			$currentColumn ++;
			$indexno++;
		}
		  
		$filename='Dokumen penerimaan uang (lebih dari 5 juta)'; //save our workbook as this file name
		$writer = new Xlsx($spreadsheet);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
		header('Cache-Control: max-age=0');
		ob_end_clean();
	
		$writer->save('php://output');	// download file 
		exit();
	}

	public function setBorderStyle($sheet,$name)
	{
		$borderthin = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
		$sheet->getStyle($name)->getBorders()->getTop()->setBorderStyle($borderthin);
		$sheet->getStyle($name)->getBorders()->getBottom()->setBorderStyle($borderthin);
		$sheet->getStyle($name)->getBorders()->getLeft()->setBorderStyle($borderthin);
		$sheet->getStyle($name)->getBorders()->getRight()->setBorderStyle($borderthin); 
	} 
}