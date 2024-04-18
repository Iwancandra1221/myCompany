<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Master extends MY_Controller 
{
	public $excel_flag = 0;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('HelperModel');
		$this->load->model('GzipDecodeModel');
		$this->load->helper('FormLibrary');
		$this->load->library('email');
		$this->load->library('excel');
	}

	public function GetDealer($kd_plg)
	{
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}

		$kd_plg = urldecode($kd_plg);
		$api = 'APITES';
		set_time_limit(60);
		
		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;
		$pwd = SQL_PWD;
		//$this->encrypt->encode(SQL_PWD, ENCRYPT_KEY);

		/*die($url."/bktAPI/MasterWilayah/GetListWilayahPLG?api=".urlencode($api)."&svr=".urlencode($svr)
							."&db=".urlencode($db)."&pwd=".urlencode($pwd));*/
		$Dealer = file_get_contents($url.API_BKT."/MasterDealer/GetDealer?api=".urlencode($api)."&plg=".urlencode($kd_plg)
							."&svr=".urlencode($svr)."&db=".urlencode($db)."&pwd=".urlencode($pwd));
		$Dealer = $this->GzipDecodeModel->_decodeGzip_true($Dealer);
		if ($Dealer["result"]=="sukses") {
			$plg = $Dealer["data"];
			$this->Preview_MsDealer($plg);
		} else {
			die($Dealer["error"]);
		}
	}

	public function Preview_MsDealer($plg)
	{
		$data = array();
		$page_title = 'MS DEALER';

		
		$api = 'APITES';
		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;
		$pwd = SQL_PWD;


		$style= "float:left;min-height:30px;line-height:30px;vertical-align:middle;bottom-border:1px solid blue;padding-bottom:2px;word-wrap:normal;";
		$kanan= "text-align:right;padding-right:10px;";
		$kiri = "text-align:left; padding-left:10px;";

		$content_html = "<html><body>";
		$content_html.= "<div id='div_header' style='margin-bottom:20px;padding-left:10px;'>";
		$content_html.= "	<div><h2>DATA DEALER/TOKO</h2></div>";
		$content_html.= "	<div><b>Printed Date/Printed Time : ".date("d-M-Y")." / ".date("h:i:s")."</b></div>";
		$content_html.= "	<div><b><font color='red'>MASIH DALAM PENGERJAAN</font></b></div>";
		$content_html.= "</div>";	//close div_header
		
		$content_html.= "<div class='div_body' style='padding-left:10px;'>";
		$content_html.= "	<div style='width:1000px!important;clear:both;'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>Kode Dealer</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:75%;".$style.$kiri."'><b>".$plg["KD_PLG"]."</b></div>";
		$content_html.= "	</div>";
		$content_html.= "	<div style='width:1000px!important;clear:both;'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>Nama Dealer</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:75%;".$style.$kiri."'><b>".$plg["NM_PLG"]."</b></div>";
		$content_html.= "	</div>";
		$content_html.= "	<div style='width:1000px!important;clear:both;'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>Nama Toko</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:75%;".$style.$kiri."'><b>".$plg["NM_TOKO"]."</b></div>";
		$content_html.= "	</div>";
		$content_html.= "	<div style='width:1000px!important;clear:both;'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>Alamat</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:75%;".$style.$kiri."'><b>".$plg["ALM_PLG"]."</b></div>";
		$content_html.= "	</div>";
		$content_html.= "	<div style='width:1000px!important;clear:both;'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>Kota</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:75%;".$style.$kiri."'><b>".$plg["KOTA"]."</b></div>";
		$content_html.= "	</div>";
		$content_html.= "	<div style='width:1000px!important;clear:both;'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>Wilayah</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:75%;".$style.$kiri."'><b>".$plg["WILAYAH"]."</b></div>";
		$content_html.= "	</div>";

		$content_html.= "	<div style='width:1000px!important;clear:both;'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>Aktif</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:75%;".$style.$kiri."'><b>".$plg["AKTIF"]."</b></div>";
		$content_html.= "	</div>";
		$content_html.= "	<div style='width:1000px!important;clear:both;'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>CL MISHIRIN</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:25%;".$style.$kiri."'><b>".number_format($plg["CL_MISHIRIN"])."</b></div>";
		//$content_html.= "		<div style='width:25%;".$style.$kiri."color:blue;' id='kalkulasi_mishirin'>KALKULASI PIUTANG</div>";
		$content_html.= "	</div>";
		$content_html.= "	<div style='width:1000px!important;clear:both;' id='piutang_mishirin'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>PIUTANG MISHIRIN</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:25%;".$style.$kiri."' id='piutang_mishirin_value'></div>";
		$content_html.= "	</div>";
		$content_html.= "	<div style='width:1000px!important;clear:both;'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>CL CO&SANITARY</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:25%;".$style.$kiri."'><b>".number_format($plg["CL_COSANITARY"])."</b></div>";
		//$content_html.= "		<div style='width:25%;".$style.$kiri."color:blue;' id='kalkulasi_cosanitary'>KALKULASI PIUTANG</div>";
		$content_html.= "	</div>";
		$content_html.= "	<div style='width:1000px!important;clear:both;' id='piutang_cosanitary'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>PIUTANG CO&SANITARY</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:25%;".$style.$kiri."' id='piutang_cosanitary_value'></div>";
		$content_html.= "	</div>";

		$content_html.= "	<div style='width:1000px!important;clear:both;'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>NPWP</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:75%;".$style.$kiri."'><b>".$plg["NPWP"]."</b></div>";
		$content_html.= "	</div>";		
		$content_html.= "	<div style='width:1000px!important;clear:both;'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>Kelompok PKP</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:75%;".$style.$kiri."'><b>".$plg["KELOMPOK_PKP"]."</b></div>";
		$content_html.= "	</div>";		
		$content_html.= "	<div style='width:1000px!important;clear:both;'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>Jenis PPH</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:75%;".$style.$kiri."'><b>".$plg["JENIS_PPH"]."</b></div>";
		$content_html.= "	</div>";
		$content_html.= "	<div style='width:1000px!important;clear:both;'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>No Rekening</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:75%;".$style.$kiri."'><b>".$plg["NO_REKENING"]."</b></div>";
		$content_html.= "	</div>";
		$content_html.= "	<div style='width:1000px!important;clear:both;'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>Bank</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:75%;".$style.$kiri."'><b>".$plg["NAMA_BANK"]."</b></div>";
		$content_html.= "	</div>";
		$content_html.= "	<div style='width:1000px!important;clear:both;'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>Cabang Bank</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:75%;".$style.$kiri."'><b>".$plg["CABANG_BANK"]."</b></div>";
		$content_html.= "	</div>";
		$content_html.= "	<div style='width:1000px!important;clear:both;'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>Nama Pemilik</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:75%;".$style.$kiri."'><b>".$plg["NAMAPEMILIK_REKENING"]."</b></div>";
		$content_html.= "	</div>";
		$content_html.= "	<div style='width:1000px!important;clear:both;'>";
		$content_html.= "		<div style='width:20%;".$style.$kiri."'>No VA BCA</div>";
		$content_html.= "		<div style='width:5%;".$style.$kiri."'>:</div>";
		$content_html.= "		<div style='width:75%;".$style.$kiri."'><b>".$plg["NO_VA"]."</b></div>";
		$content_html.= "	</div>";

		$content_html.= "</div>";
		$content_html.= "</body></html>";

		$content_html.= "<script>";
		$content_html.= "	$(document).ready(function(){";
		$content_html.= "		$('#piutang_mishirin').hide();";
		$content_html.= "		$('#piutang_cosanitary').hide();";
		$content_html.= "	});";
		$content_html.= "</script>";
		$content_html.= "<style>";
		$content_html.= "	#kalkulasi_mishirin:hover, #kalkulasi_cosanitary:hover { ";
		$content_html.= "		cursor:pointer; background-color:black;";
		$content_html.= "	}";
		$content_html.= "</style>";

		$data['title'] = $page_title;
		$data['content_html'] = $content_html;

		$this->RenderView('ReportResult',$data);
		// $this->SetTemplate('template/login');
	}


}