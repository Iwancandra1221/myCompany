<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ReportPenerimaanPembayaran extends MY_Controller 
{
	public $excel_flag = 0;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('HelperModel');
		$this->load->helper('FormLibrary');
		$this->load->library('email');
		$this->load->library('excel');
		ini_set("max_execution_time", 600);
	}

	public function index()
	{
		// include_once('/../includes/CheckModule.php');
		$data = array();

		$api = 'APITES';
		set_time_limit(60);
		
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}

		$params = array();	
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "REPORT PENERIMAAN PEMBAYARAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU REPORT PENERIMAAN PEMBAYARAN";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;
		//$pwd = SQL_PWD;
		//$this->encrypt->encode(SQL_PWD, ENCRYPT_KEY);

		/*die($url.API_BKT."/MasterWilayah/GetListWilayahPLG?api=".urlencode($api)."&svr=".urlencode($svr)
							."&db=".urlencode($db));*/

		$GetWilayahs = json_decode(file_get_contents($url.API_BKT."/MasterWilayah/GetListWilayahPLG?api=".urlencode($api)."&svr=".urlencode($svr)."&db=".urlencode($db)),true);
		if ($GetWilayahs["result"]=="sukses") {
			$data["wilayah"] = $GetWilayahs["data"];
		} else {
			$data["wilayah"] = array();
		} 

		$data['title'] = 'REPORT PENERIMAAN PEMBAYARAN | '.WEBTITLE;

		$this->RenderView('ReportPenerimaanPembayaranForm',$data);
	}

	public function Proses()
	{
		$data = array();
		$page_title = 'Report Finance';

		if(isset($_POST["btnExcel"])){
			$this->excel_flag = 1;
		}
		else{
			$this->excel_flag = 0;
		}

		if(isset($_POST['dealer']))
		{
			if ($_POST["dealer"] == "")
			{
				$p_dealer = "ALL";
			}
			else
			{ 
				$p_dealer = $_POST["dealer"];
			}
		}
		else
		{ 
			$p_dealer = "ALL";
		} 


		if(isset($_POST['wilayah']))
		{

			$this->load->library('form_validation');
			$this->form_validation->set_rules('dp1','Tanggal Awal','required');
			$this->form_validation->set_rules('dp2','Tanggal Akhir','required');
			$this->form_validation->set_rules('wilayah','Wilayah','required');

			if($this->form_validation->run())
			{

				$params = array();			
				$params['LogDate'] = date("Y-m-d H:i:s");
				$params['UserID'] = $_SESSION["logged_in"]["userid"];
				$params['UserName'] = $_SESSION["logged_in"]["username"];
				$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
				$params['Module'] = "REPORT PENERIMAAN PEMBAYARAN";
				$params['TrxID'] = date("YmdHis");
				$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES REPORT PENERIMAAN PEMBAYARAN WILAYAH ".$_POST["wilayah"]." TIPE PEMBAYARAN ".$_POST["opsi"]." PERIODE ".date("d-M-Y", strtotime($_POST["dp1"]))." S/D ".date("d-M-Y", strtotime($_POST["dp2"]));
				$params['Remarks'] = "";
				$params['RemarksDate'] = 'NULL';
				$this->ActivityLogModel->insert_activity($params);

				$this->Proses_PenerimaanPembayaranOrderByTagihan($page_title, $_POST["wilayah"], $p_dealer, $_POST["dp1"], $_POST["dp2"], $_POST["opsi"], $params);

				/*if ($_POST["opsi"]=="C01") {
					$this->Preview_DelapanPeriode("GABUNGAN", $page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"], $_POST["cbox1"]);
				} else if ($_POST["opsi"]=="C02") {
					$this->Preview_DelapanPeriode("KOTA", $page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"], $_POST["cbox1"]);
				} else if ($_POST["opsi"]=="C03") {
					$this->Preview_DelapanPeriode("GROUP GUDANG", $page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"]);
				} else if ($_POST["opsi"]=="A01") {
					$this->Preview_SatuPeriodeAllKota($page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"], $_POST["cbox1"]);
				} else if ($_POST["opsi"]=="A02") {
					$this->Preview_SatuPeriodeAllPT($page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"], $_POST["cbox1"]);
				} else if ($_POST["opsi"]=="B01") {
					$this->Preview_JualBeliAllKota("JUAL", $page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"], $_POST["cbox1"]);
				} else if ($_POST["opsi"]=="B02") {
					$this->Preview_JualBeliAllKota("BELI", $page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"], $_POST["cbox1"]);
				} else if ($_POST["opsi"]=="B03") {
					$this->Preview_JualBeliAllPT("JUAL", $page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"], $_POST["cbox1"]);
				} else if ($_POST["opsi"]=="B04") {
					$this->Preview_JualBeliAllPT("BELI", $page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"], $_POST["cbox1"]);
				}*/
			} else  {
				//die("not valid");
				redirect("ReportPenerimaanPembayaran");
			}
		}
		else
		{
			//die("no wilayah");
			redirect("ReportPenerimaanPembayaran");
		}
	}

	public function Proses_PenerimaanPembayaranOrderByTagihan($page_title, $p_wil, $p_dealer, $p_tgl1, $p_tgl2, $p_opsi="P01", $params)
	{
		
		$data = array();
		$api = 'APITES';
		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;
		//$pwd = SQL_PWD;

		/*die($url.API_BKT."/ReportFinance/ReportPenerimaanOrderByTagihan?api=".urlencode($api)."&wil=".urlencode($p_wil)."&dealer=".urlencode($p_dealer)."&tgl1=".urlencode($p_tgl1)."&tgl2=".urlencode($p_tgl2)."&opsi=".urlencode($p_opsi)."&svr=".urlencode($svr)."&db=".urlencode($db));*/
		
		set_time_limit(60);
		$GetPayments = json_decode(file_get_contents($url.API_BKT."/ReportFinance/ReportPenerimaanOrderByTagihan?api=".urlencode($api)."&wil=".urlencode($p_wil)."&dealer=".urlencode($p_dealer)."&tgl1=".urlencode($p_tgl1)."&tgl2=".urlencode($p_tgl2)."&opsi=".urlencode($p_opsi)."&svr=".urlencode($svr)."&db=".urlencode($db)), true);

		if ($GetPayments["result"]=="sukses") {

			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->Preview_PenerimaanPembayaranOrderByTagihan($page_title, $p_wil, $p_tgl1, $p_tgl2, $p_opsi, $GetPayments["data"]);
		} else {
			$params['Remarks']="FAILED - Data Tidak Ditemukan";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
		}
	}

	public function Preview_PenerimaanPembayaranOrderByTagihan($page_title, $p_wil, $p_tgl1, $p_tgl2, $p_opsi, $data) 
	{
		//die(json_encode($data));

		$style_col_ganjil ="float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#ffffcc;";
		$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
		$style_summary= "float:left;height:30px;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#cccccc;";

		$kanan= "text-align:right;padding-right:10px;";
		$kiri = "text-align:left; padding-left:10px;";

		$content_html = "<style> body { font-size:9pt; } </style>";
		$content_html.= "<html><body>";
		$content_html.= "<div id='div_header' style='margin-bottom:20px;padding-left:10px;'>";
		$content_html.= "	<div><h2>LAPORAN PENERIMAAN PEMBAYARAN</h2></div>";
		$content_html.= "	<div><b>Wilayah : ".$p_wil."</b></div>";
		$content_html.= "	<div><b>Periode : ".date("d-M-Y", strtotime($p_tgl1))." - ".date("d-M-Y", strtotime($p_tgl2))."</b></div>";
		$content_html.= "</div>";	//close div_header

		if($this->excel_flag == 1){
			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->setTitle('LaporanPenerimaanPembayaran');
			$this->excel->getActiveSheet()->setCellValue('A1', 'LAPORAN PENERIMAAN PEMBAYARAN');
			$this->excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
			$this->excel->getActiveSheet()->setCellValue('A2', 'Wilayah : '.$p_wil);
			$this->excel->getActiveSheet()->setCellValue('A3', 'Periode : '.date("d-M-Y", strtotime($p_tgl1))." - ".date("d-M-Y", strtotime($p_tgl2)));
		}
		$currcol = 0;
		$currrow = 5;

		$style_col = $style_col_genap;
		
		$content_html.= "<div class='div_body' style='font-size:9pt;overflow-x:scroll;padding-left:10px;'>";
		$content_html.= "	<div id='div_column_header' style='width:1500px!important;line-height:90px;vertical-align:middle;'>";
		$content_html.= "		<div style='width:20%;".$style_summary.$kiri."height:60px!important;'><b>Nama Dealer</b></div>";
		$content_html.= "		<div style='width:6%;".$style_summary.$kiri."height:60px!important;'><b>Kode Dealer</b></div>";
		$content_html.= "		<div style='width:6%;".$style_summary.$kiri."height:60px!important;'><b>Marker</b></div>";
		$content_html.= "		<div style='width:68%;".$style_summary."height:60px!important;'><div>";
		$content_html.= "			<div style='width:15%;".$style_summary.$kiri."height:60px!important;'><b>No Tagihan</b></div>";
		$content_html.= "			<div style='width:10%;".$style_summary.$kiri."height:60px!important;'><b>Tgl JT</b></div>";
		$content_html.= "			<div style='width:15%;".$style_summary.$kanan."height:60px!important;'><b>Total Tagihan</b></div>";
		$content_html.= "			<div style='width:10%;".$style_summary.$kiri."height:60px!important;'><b>No Penerimaan</b></div>";
		$content_html.= "			<div style='width:10%;".$style_summary.$kiri."height:60px!important;'><b>Tgl Penerimaan</b></div>";
		$content_html.= "			<div style='width:10%;".$style_summary.$kiri."height:60px!important;'><b>Tipe Penerimaan</b></div>";
		$content_html.= "			<div style='width:15%;".$style_summary.$kanan."height:60px!important;'><b>Total Penerimaan</b></div>";
		$content_html.= "			<div style='width:15%;".$style_summary.$kanan."height:60px!important;'><b>Sisa Tagihan</b></div>";
		$content_html.= "		</div></div>";	//close div_column_header
		$content_html.= "	</div>";	//close div_column_header
		$content_html.= "	<div style='clear:both;'></div>";


		if($this->excel_flag == 1){
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Nama Dealer');
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Dealer');
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Marker');
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'No Tagihan');
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Tgl JT');
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Total Tagihan');
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'No Penerimaan');
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Tgl Penerimaan');
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Tipe Penerimaan');
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Total Penerimaan');
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Sisa Tagihan');
			$currcol += 1;

			$currrow = 6;
			$currcol = 0;
		}


		$PLG = "";
		$NM_PLG = "";
		$KD_PLG = "";
		$MARKER = "";
		$TOT_PLG = 0;
		$PEN_PLG = 0;
		$SISA_PLG= 0;

		$TAGIHAN = "";
		$NO_TAGIHAN = "";
		$JT_TAGIHAN = "";
		$TOT_TAGIHAN = 0;
		$SISA_TAGIHAN= 0;

		$TAG_COUNTER = 0;
		$PLG_COUNTER = 0;

		$content_tagihan = "";
		$style_col_str="";

		for($i=0;$i<count($data);$i++)
		{

			if ($PLG==trim($data[$i]["NM_PLG"])." - ".trim($data[$i]["KD_PLG"])) {
				//$NM_PLG = "&nbsp;";
				//$KD_PLG = "&nbsp;";
				//$MARKER = "&nbsp;";
				$TAG_COUNTER+=1;
				$PEN_PLG += $data[$i]["TOTAL_PENERIMAAN"];
			} else {
				if ($style_col_str=="" || $style_col_str=="genap") {
					$style_col = $style_col_ganjil;
					$style_col_str = "ganjil";
				} else {
					$style_col = $style_col_genap;
					$style_col_str = "genap";
				}

				if ($PLG!="") {

					$height = $TAG_COUNTER*30;
					$SISA_PLG = (($TOT_PLG - $PEN_PLG)<0)?0:($TOT_PLG - $PEN_PLG);

					$content_html.= "	<div id='div_column_header' style='width:1500px;line-height:90px;vertical-align:middle;'>";
					$content_html.= "		<div style='width:20%;height:".$height."px;".$style_col.$kiri."'>".$NM_PLG."</div>";
					$content_html.= "		<div style='width:6%;height:".$height."px;".$style_col.$kiri."'>".$KD_PLG."</div>";
					$content_html.= "		<div style='width:6%;height:".$height."px;".$style_col.$kiri."'>".$MARKER."</div>";
					$content_html.= "		<div style='width:68%;".$style_col."'>";
					$content_html.= $content_tagihan;
					$content_html.= "		</div>";
					$content_html.= "	</div>";
					$content_html.= "	<div style='clear:both;'></div>";
					$content_html.= "	<div id='div_column_header' style='width:1500px;line-height:90px;vertical-align:middle;'>";
					$content_html.= "		<div style='width:32%;".$style_summary.$kiri."'><b>".$PLG."</b></div>";
					$content_html.= "		<div style='width:68%;".$style_summary."'>";
					$content_html.= "		<div style='clear:both;'>";
					$content_html.= "			<div style='width:15%;".$style_summary.$kiri."'>&nbsp;</div>";
					$content_html.= "			<div style='width:10%;".$style_summary.$kiri."'>&nbsp;</div>";
					$content_html.= "			<div style='width:15%;".$style_summary.$kanan."'><b>".number_format($TOT_PLG)."</b></div>";
					$content_html.= "			<div style='width:10%;".$style_summary."'>&nbsp;</div>";
					if(isset($data[$i]["TGL_PENERIMAAN"])) {
						$content_html.= "		<div style='width:10%;".$style_summary.$kiri."'>&nbsp;</div>";
					} else {
						$content_html.= "		<div style='width:10%;".$style_summary.$kiri."'>&nbsp;</div>";
					}
					$content_html.= "			<div style='width:10%;".$style_summary.$kiri."'>&nbsp;</div>";
					$content_html.= "			<div style='width:15%;".$style_summary.$kanan."'><b>".number_format($PEN_PLG)."</b></div>";
					$content_html.= "			<div style='width:15%;".$style_summary.$kanan."'><b>".number_format($SISA_PLG)."</b></div>";
					$content_html.= "		</div>";
					$content_html.= "		</div>";
					$content_html.= "	</div>";
					$content_html.= "	<div style='clear:both;'></div>";

					if($this->excel_flag == 1){
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $PLG);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, "");
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, "");
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, "");
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, "");
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $TOT_PLG);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, "");
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, "");
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, "");
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $PEN_PLG);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $SISA_PLG);
						$currcol += 1;

						$currrow+=1;
						$currcol =0;
					}


				}

				$TAGIHAN = "";
				$PLG_COUNTER += 1;
				$TAG_COUNTER = 1;
				$TOT_PLG = 0;
				$SISA_PLG = 0;
				$PEN_PLG = $data[$i]["TOTAL_PENERIMAAN"];


				$PLG = trim($data[$i]["NM_PLG"])." - ".trim($data[$i]["KD_PLG"]);
				$KD_PLG = trim($data[$i]["KD_PLG"]);
				//$NM_PLG = "<a href='".site_url("Master/GetDealer/".urlencode($KD_PLG))."' target='blank'>".trim($data[$i]["NM_PLG"])."</a>";
				$NM_PLG = trim($data[$i]["NM_PLG"]);
				$MARKER = trim($data[$i]["MARKER"]);
				$content_tagihan = "";

			}

			if ($TAGIHAN!=trim($data[$i]["NO_TAGIHAN"])) {
				$TAGIHAN = trim($data[$i]["NO_TAGIHAN"]);
				//$NO_TAGIHAN = "<a href='".site_url("Finance/GetTagihan/".urlencode($TAGIHAN))."' target='blank'>".trim($data[$i]["NO_TAGIHAN"])."</a>";
				$NO_TAGIHAN = trim($data[$i]["NO_TAGIHAN"]);
				$JT_TAGIHAN = date("d-M-Y", strtotime($data[$i]["TGL_JTTAGIHAN"]));
				$TOT_TAGIHAN = number_format($data[$i]["TOTAL_TAGIHAN"]);
				$SISA_TAGIHAN= number_format($data[$i]["SISA_TAGIHAN"]);

				$TOT_PLG += $data[$i]["TOTAL_TAGIHAN"];
				$SISA_PLG += $data[$i]["SISA_TAGIHAN"];
			} else {
				$NO_TAGIHAN  = "&nbsp;";
				$JT_TAGIHAN  = "&nbsp;";
				$TOT_TAGIHAN = "&nbsp;";
				$SISA_TAGIHAN= "&nbsp;";
			}

			

			$content_tagihan.= "		<div style='clear:both;'>";
			$content_tagihan.= "			<div style='width:15%;".$style_col.$kiri."'>".$NO_TAGIHAN."</div>";
			$content_tagihan.= "			<div style='width:10%;".$style_col.$kiri."'>".$JT_TAGIHAN."</div>";
			$content_tagihan.= "			<div style='width:15%;".$style_col.$kanan."'>".$TOT_TAGIHAN."</div>";
			if(isset($data[$i]["NO_PENERIMAAN"]) && $data[$i]["NO_PENERIMAAN"]!="") {
				//$content_tagihan.= "		<div style='width:10%;".$style_col.$kiri."'>"."<a href='".site_url("Master/GetPenerimaan/".urlencode($data[$i]["NO_PENERIMAAN"]))."' target='blank'>".$data[$i]["NO_PENERIMAAN"]."</a></div>";
				$content_tagihan.= "		<div style='width:10%;".$style_col.$kiri."'>".$data[$i]["NO_PENERIMAAN"]."</div>";
			} else {
				$content_tagihan.= "		<div style='width:10%;".$style_col.$kiri."'>-</div>";
			}
			if(isset($data[$i]["TGL_PENERIMAAN"])) {
				$content_tagihan.= "		<div style='width:10%;".$style_col.$kiri."'>".date("d-M-Y", strtotime($data[$i]["TGL_PENERIMAAN"]))."</div>";
			} else {
				$content_tagihan.= "		<div style='width:10%;".$style_col.$kiri."'>-</div>";
			}
			if(isset($data[$i]["TYPE_PENERIMAAN"])) {
				$TYPE_PENERIMAAN = ((trim($data[$i]["TYPE_PENERIMAAN"]) == "TRANSFER VA") ? "VA" : $data[$i]["TYPE_PENERIMAAN"]);
				$content_tagihan.= "			<div style='width:10%;".$style_col.$kiri."'>".$TYPE_PENERIMAAN."</div>";
			} else {
				$content_tagihan.= "			<div style='width:10%;".$style_col.$kiri."'>-</div>";
			}
			if(isset($data[$i]["TOTAL_PENERIMAAN"])) {
				$content_tagihan.= "			<div style='width:15%;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_PENERIMAAN"])."</div>";
			} else {
				$content_tagihan.= "			<div style='width:15%;".$style_col.$kanan."'>0</div>";	
			}
			$content_tagihan.= "			<div style='width:15%;".$style_col.$kanan."'>".$SISA_TAGIHAN."</div>";
			$content_tagihan.= "		</div>";


			if($this->excel_flag == 1){
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $NM_PLG);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $KD_PLG);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $MARKER);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $NO_TAGIHAN);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $JT_TAGIHAN);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $TOT_TAGIHAN);
				$currcol += 1;
				if(isset($data[$i]["NO_PENERIMAAN"]) && $data[$i]["NO_PENERIMAAN"]!="") 
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $data[$i]["NO_PENERIMAAN"]);
				else
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, "-");
				$currcol += 1;
				if(isset($data[$i]["TGL_PENERIMAAN"])) 
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, date("d-M-Y", strtotime($data[$i]["TGL_PENERIMAAN"])));
				else
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, "-");
				$currcol += 1;
				if(isset($data[$i]["TYPE_PENERIMAAN"])) {
					$TYPE_PENERIMAAN = ((trim($data[$i]["TYPE_PENERIMAAN"]) == "TRANSFER VA") ? "VA" : $data[$i]["TYPE_PENERIMAAN"]);
				} else {
					$TYPE_PENERIMAAN = "-";
				}
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $TYPE_PENERIMAAN);
				$currcol += 1;
				if(isset($data[$i]["TOTAL_PENERIMAAN"])) {
					$TOTAL_PENERIMAAN = $data[$i]["TOTAL_PENERIMAAN"];
				} else {
					$TOTAL_PENERIMAAN = 0;
				}
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL_PENERIMAAN);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $SISA_TAGIHAN);
				$currcol += 1;

				$currrow+=1;
				$currcol =0;
			}

		}

		if ($PLG!="") {
			$height = $TAG_COUNTER*30;
			$SISA_PLG = (($TOT_PLG - $PEN_PLG)<0)?0:($TOT_PLG - $PEN_PLG);

			$content_html.= "	<div id='div_column_header' style='width:1500px;line-height:90px;vertical-align:middle;'>";
			$content_html.= "		<div style='width:20%;height:".$height."px;".$style_col.$kiri."'>".$NM_PLG."</div>";
			$content_html.= "		<div style='width:6%;height:".$height."px;".$style_col.$kiri."'>".$KD_PLG."</div>";
			$content_html.= "		<div style='width:6%;height:".$height."px;".$style_col.$kiri."'>".$MARKER."</div>";
			$content_html.= "		<div style='width:68%;".$style_col."'>";
			$content_html.= $content_tagihan;
			$content_html.= "		</div>";
			$content_html.= "	</div>";
			$content_html.= "	<div style='clear:both;'></div>";
			$content_html.= "	<div id='div_column_header' style='width:1500px;line-height:90px;vertical-align:middle;'>";
			$content_html.= "		<div style='width:32%;".$style_summary.$kiri."'><b>".$PLG."</b></div>";
			$content_html.= "		<div style='width:68%;".$style_summary."'>";
			$content_html.= "			<div>";
			$content_html.= "			<div style='width:15%;".$style_summary.$kiri."'>&nbsp;</div>";
			$content_html.= "			<div style='width:10%;".$style_summary.$kiri."'>&nbsp;</div>";
			$content_html.= "			<div style='width:15%;".$style_summary.$kanan."'><b>".number_format($TOT_PLG)."</b></div>";
			$content_html.= "			<div style='width:10%;".$style_summary."'>&nbsp;</div>";
			if(isset($data[$i]["TGL_PENERIMAAN"])) {
				$content_html.= "		<div style='width:10%;".$style_summary.$kiri."'>&nbsp;</div>";
			} else {
				$content_html.= "		<div style='width:10%;".$style_summary.$kiri."'>&nbsp;</div>";
			}
			$content_html.= "			<div style='width:10%;".$style_summary.$kiri."'>&nbsp;</div>";
			$content_html.= "			<div style='width:15%;".$style_summary.$kanan."'><b>".number_format($PEN_PLG)."</b></div>";
			$content_html.= "			<div style='width:15%;".$style_summary.$kanan."'><b>".number_format($SISA_PLG)."</b></div>";
			$content_html.= "			</div>";
			$content_html.= "		</div>";
			$content_html.= "	</div>";
			$content_html.= "	<div style='clear:both;height:50px;'></div>";

			if($this->excel_flag == 1){
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $PLG);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, "");
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, "");
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, "");
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, "");
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $TOT_PLG);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, "");
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, "");
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, "");
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $PEN_PLG);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $SISA_PLG);
				$currcol += 1;

				$currrow+=1;
				$currcol =0;
			}		
		}		
		
		if($this->excel_flag == 1){
			//PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
			$this->excel->getActiveSheet()->mergeCells('A1:J1');
			for ($i = 'A'; $i !=   $this->excel->getActiveSheet()->getHighestColumn(); $i++) {
			    $this->excel->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
			}

		    $filename='PenerimaanPembayaran.xls'; //save our workbook as this file name
		    header('Content-Type: application/vnd.ms-excel'); //mime type
		    header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
		    header('Cache-Control: max-age=0'); //no cache
		    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
		    //force user to download the Excel file without writing it to server's HD
		    $objWriter->save('php://output');
		} else {

			$content_html.= "		</div>";
			$content_html.= "	</div>";
			$content_html.= "</div>";
			$content_html.= "</body></html>";


			$data['title'] = $page_title;
			$data['content_html'] = $content_html;
			$this->RenderView('ReportFinanceResult',$data);
		}
	}
 
	public function findDealer()
	{ 
	    if(isset($_POST['dealer'])) {
	        $selectdealer = $_POST['dealer']; 
	        $selectwilayah = $_POST['wilayah']; 
	        $api = 'APITES';
	        $url = $_SESSION["conn"]->AlamatWebService;
	        $svr = $_SESSION["conn"]->Server;
	        $db  = $_SESSION["conn"]->Database;

	        // Perbaikan: Tambahkan tanda kutip pada URL
	        $GetDealers = json_decode(file_get_contents($url.API_BKT."/MasterDealer/findDealer?api=".urlencode($api)."&svr=".urlencode($svr)."&db=".urlencode($db)."&kdwill=".urlencode($selectwilayah)."&kddealer=".urlencode($selectdealer)), true);
	        ob_end_clean();

	        $result = "";

	        if ($GetDealers["result"] == "sukses") { 
	            foreach ($GetDealers["data"] as $dealer) {  
	        		header('Content-Type: application/json');
	        		echo json_encode(array('success' => true, 'result' => $dealer['NM_PLG']));
		        }
	        } else {
	        		header('Content-Type: application/json');
	        		echo json_encode(array('success' => false));
	        }  
	         
	    } else { 
	        header('Content-Type: application/json'); 
	        echo json_encode(array('success' => false));
	    }
	}


	public function LoadMasterDealer()
	{ 
	   		$data['api']		    = 'APITES'; 
			$data['sSearch']		= $this->input->get('sSearch');
			$data['sSortDir_0']		= $this->input->get('sSortDir_0');
			$data['iSortingCols']	= $this->input->get('iSortingCols');
			$data['iDisplayStart']	= $this->input->get('iDisplayStart');
			$data['iDisplayLength']	= $this->input->get('iDisplayLength');
			$data['wilayah'] 		= $this->input->get('wilayah');
			$data['url']			= $this->MasterDbModel->get($_SESSION['conn']->DatabaseId)->AlamatWebService;
			$data['svr']			= $_SESSION['conn']->Server;
			$data['db']				= $_SESSION['conn']->Database;
   
 
	        $url = $data['url']; 
    		$url = $url.API_BKT."/MasterDealer/list";
			$ch = curl_init($url);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

			$hasildata = curl_exec($ch);
			if (curl_errno($ch)) {
			    echo 'Error: ' . curl_error($ch);
			}

			curl_close($ch);
  
			$hasildata = json_decode($hasildata, true);
			$data_list=array();
			$data_hasil=array();
			$total=0;
 
			if(!empty($hasildata['result']) && $hasildata['result']=='success'){ 
				if (isset($hasildata['data']['list']) && is_array($hasildata['data']['list']) && count($hasildata['data']['list']) > 0) { 
				    foreach ($hasildata['data']['list'] as $key => $r) {
				        $list = array();
				        $list[] = $r['KD_PLG'];
				        $list[] = $r['NM_PLG'];
				        $data_list[] = $list;
				    } 
					$total=$hasildata['data']['total'];
				}  

			}

				if(!empty($this->input->get('sEcho'))){
					$secho = $this->input->get('sEcho');
				}else{
					$secho = 1;
				}

				$data_hasil['sEcho']=$secho;
				$data_hasil['iTotalRecords']=count($data_list);
				$data_hasil['iTotalDisplayRecords']=$total;
				$data_hasil['aaData']=$data_list;

			print_r(json_encode($data_hasil));
	}

}