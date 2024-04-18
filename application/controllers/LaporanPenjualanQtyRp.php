<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LaporanPenjualanQtyRp extends MY_Controller 
{
	public $pdf_flag = 0;
	public $confirm_flag=0;

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
		$p_tgl1 = $this->input->get("dp1");
		$p_tgl2 = $this->input->get("dp2");
		//die("here");
		$this->Proses_PenggunaanEMeterai("", $p_tgl1, $p_tgl2, "PERIODE");
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

		$opt = $_POST["ReportOption"];

		if ($opt=="TANGGAL") {
			$dp1 = $_POST["dp1"];
			$dp2 = $_POST["dp2"];

			$this->load->library('form_validation');
			$this->form_validation->set_rules('dp1','Tanggal Awal','required');
			$this->form_validation->set_rules('dp2','Tanggal Akhir','required');

		} else {
			if ($_POST["Periode"]=="01") {
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

		if($this->form_validation->run())
		{
			//$this->Proses_PenggunaanEMeterai($page_title, $_POST["dtbase"], $_POST["dp1"], $_POST["dp2"]);
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
		/*die($url."bktAPI/ReportFinance/ReportPenggunaanEMeterai?api=".urlencode($api)
			."&tgl1=".urlencode($p_tgl1)."&tgl2=".urlencode($p_tgl2)
			."&svr=".urlencode($svr)."&db=".urlencode($db));*/

		$GetData = json_decode(file_get_contents($url.API_BKT."/ReportFinance/ReportPenggunaanEMeterai?api=".urlencode($api)
			."&tgl1=".urlencode($p_tgl1)."&tgl2=".urlencode($p_tgl2)
			."&svr=".urlencode($svr)."&db=".urlencode($db)), true);

		if ($GetData["result"]=="sukses") {
			$this->Preview_PenggunaanEMeterai($opt, $p_tgl1, $p_tgl2, $GetData["data"]);
		} else {
			die($GetData["error"]);
		}
	}

	public function Preview_PenggunaanEMeterai($opt, $p_tgl1, $p_tgl2, $data) 
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

		$style_col = $style_col_genap;
		
		$group_header = "	<div id='div_column_header' style='width:95%!important;'>";
		$group_header.= "		<div style='width:3%;".$style_header.$center."'><b>NO</b></div>";
		$group_header.= "		<div style='width:3%;".$style_header.$kiri."'><b>LOK</b></div>";
		$group_header.= "		<div style='width:10%;".$style_header.$kiri."'><b>WILAYAH</b></div>";
		$group_header.= "		<div style='width:10%;".$style_header.$kiri."'><b>No Bukti</b></div>";
		$group_header.= "		<div style='width:8%;".$style_header.$kiri."'><b>Tgl Trans</b></div>";
		$group_header.= "		<div style='width:6%;".$style_header.$kiri."'><b>Kode</b></div>";
		$group_header.= "		<div style='width:18%;".$style_header.$kiri."'><b>Nama Pelanggan</b></div>";
		$group_header.= "		<div style='width:10%;".$style_header.$kiri."'><b>Ket</b></div>";
		$group_header.= "		<div style='width:8%;".$style_header.$kanan."'><b>Total Trans</b></div>";
		$group_header.= "		<div style='width:4%;".$style_header.$center."'><b>3000</b></div>";
		$group_header.= "		<div style='width:4%;".$style_header.$center."'><b>6000</b></div>";
		$group_header.= "		<div style='width:5%;".$style_header.$kanan."'><b>Meterai</b></div>";
		$group_header.= "	</div>";	//close div_column_header
		$group_header.= "	<div style='clear:both;'></div>";

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

		$TOTAL_EMETERAI = 0;
		$EMETERAI_COUNTER = 0;
		$user = $_SESSION["logged_in"]["useremail"];

		$content_html = "<div class='div_body' style='font-size:9pt!important;'>";

		for($i=0;$i<count($data);$i++)
		{
			$n = $i+1;

			if ($i%2==1)
				$style_col = $style_col_genap;
			else
				$style_col = $style_col_ganjil;

			if ($IJIN==trim($data[$i]["IJINEMETERAI"])) {

				$height = 20;

				$content_html.= "	<div id='div_column_header' style='width:95%!important;'>";
				$content_html.= "		<div style='width:3%;max-height:".$height."px;".$style_col.$center."'>".$n."</div>";
				$content_html.= "		<div style='width:3%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["KD_LOKASI"]."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["WILAYAH"]."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["NO_BUKTI"]."</div>";
				$content_html.= "		<div style='width:8%;max-height:".$height."px;".$style_col.$kiri."'>".date("d-M-Y", strtotime($data[$i]["TGL_TRANS"]))."</div>";
				$content_html.= "		<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["KD_PLG"]."</div>";
				$content_html.= "		<div style='width:18%;max-height:".$height."px;".$style_col.$kiri."'>".trim($data[$i]["NM_PLG"])."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".(($data[$i]["TYPE_TRANS"]=="CASH BEFORE")?"CASH BEFORE":"JT : ".date("d-M",strtotime($data[$i]["TGL_JATUHTEMPO"])))."</div>";
				$content_html.= "		<div style='width:8%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_BUKTI"])."</div>";
				$content_html.= "		<div style='width:4%;max-height:".$height."px;".$style_col.$center."'>".(($data[$i]["EMETERAI3000"]==0)?"&nbsp;":"x")."</div>";
				$content_html.= "		<div style='width:4%;max-height:".$height."px;".$style_col.$center."'>".(($data[$i]["EMETERAI6000"]==0)?"&nbsp;":"x")."</div>";
				$content_html.= "		<div style='width:5%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["EMETERAI_VALUE"])."</div>";
				$content_html.= "	</div>";
				$content_html.= "	<div style='clear:both;'></div>";

				$EMETERAI_COUNTER += 1;
				$TOTAL_EMETERAI += $data[$i]["EMETERAI_VALUE"];
				if ($data[$i]["EMETERAI_VALUE"]==3000) {
					$METERAI_3000 += 1;
				} else {
					$METERAI_6000 += 1;
				}
			} else {

				if ($IJIN!="") {
					
					if ($this->confirm_flag==1) {
						$url = $this->API_URL."/Billing/SaveEMeteraiUsage?lok=".urlencode($LOK)."&ijin=".urlencode($IJIN)
							."&tgl1=".urlencode(date("m/d/Y",strtotime($p_tgl1)))."&tgl2=".urlencode(date("m/d/Y",strtotime($p_tgl2)))
							."&total=".urlencode($TOTAL_EMETERAI)."&met3=".urlencode($METERAI_3000)."&met6=".urlencode($METERAI_6000)."&tipe=".urlencode("KWITANSI TAGIHAN")
							."&user=".urlencode($user);
						//die($url);
						$simpanData = json_decode(file_get_contents($url), true);
					}

					$group_footer = "	<div id='div_column_header' style='width:95%!important;'>";
					$group_footer.= "		<div style='width:3%;".$style_footer.$center."'>&nbsp;</div>";
					$group_footer.= "		<div style='width:3%;".$style_footer.$kiri."'>&nbsp;</div>";
					$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>&nbsp;</div>";
					$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>&nbsp;</div>";
					$group_footer.= "		<div style='width:8%;".$style_footer.$kiri."'>&nbsp;</div>";
					$group_footer.= "		<div style='width:6%;".$style_footer.$kanan."'><b>TOTAL</b></div>";
					$group_footer.= "		<div style='width:18%;".$style_footer.$kiri."'><b>".$IJIN."</b></div>";
					$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>&nbsp;</div>";
					$group_footer.= "		<div style='width:8%;".$style_footer.$kanan."'>&nbsp;</div>";
					$group_footer.= "		<div style='width:4%;".$style_footer.$center."'><b>".$METERAI_3000."</b></div>";
					$group_footer.= "		<div style='width:4%;".$style_footer.$center."'><b>".$METERAI_6000."</b></div>";
					$group_footer.= "		<div style='width:5%;".$style_footer.$kanan."'><b>".number_format($TOTAL_EMETERAI)."</b></div>";
					$group_footer.= "	</div>";	//close div_column_header

					$content_html.= $group_footer;					
				}

				$height = 20;
				$content_html.= "	<div id='div_column_header' style='width:95%!important;line-height:50px;vertical-align:middle;'>";
				$content_html.= "		<div style='width:30%;height:".$height."px;float:left;".$kiri."'>IJIN PEMBUBUHAN BEA METERAI :</div>";
				$content_html.= "		<div style='width:65%;height:".$height."px;float:left;".$kiri."'><b>".$data[$i]["IJINEMETERAI"]."</b></div>";
				$content_html.= "	</div>";
				$content_html.= "	<div style='clear:both;height:15px;'></div>";
				$content_html.= $group_header;
				$content_html.= "	<div id='div_column_header' style='width:95%!important;'>";
				$content_html.= "		<div style='width:3%;max-height:".$height."px;".$style_col.$center."'>".$n."</div>";
				$content_html.= "		<div style='width:3%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["KD_LOKASI"]."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["WILAYAH"]."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["NO_BUKTI"]."</div>";
				$content_html.= "		<div style='width:8%;max-height:".$height."px;".$style_col.$kiri."'>".date("d-M-Y", strtotime($data[$i]["TGL_TRANS"]))."</div>";
				$content_html.= "		<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["KD_PLG"]."</div>";
				$content_html.= "		<div style='width:18%;max-height:".$height."px;".$style_col.$kiri."'>".trim($data[$i]["NM_PLG"])."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".(($data[$i]["TYPE_TRANS"]=="CASH BEFORE")?"CASH BEFORE":"JT : ".date("d-M",strtotime($data[$i]["TGL_JATUHTEMPO"])))."</div>";
				$content_html.= "		<div style='width:8%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_BUKTI"])."</div>";
				$content_html.= "		<div style='width:4%;max-height:".$height."px;".$style_col.$center."'>".(($data[$i]["EMETERAI3000"]==0)?"":"x")."</div>";
				$content_html.= "		<div style='width:4%;max-height:".$height."px;".$style_col.$center."'>".(($data[$i]["EMETERAI6000"]==0)?"":"x")."</div>";
				$content_html.= "		<div style='width:5%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["EMETERAI_VALUE"])."</div>";
				$content_html.= "	</div>";
				$content_html.= "	<div style='clear:both;'></div>";
				
				$IJIN = $data[$i]["IJINEMETERAI"];
				$LOK = $data[$i]["KD_LOKASI"];

				$EMETERAI_COUNTER = 1;
				$TOTAL_EMETERAI = $data[$i]["EMETERAI_VALUE"];
				if ($data[$i]["EMETERAI_VALUE"]==3000) {
					$METERAI_3000 = 1;
					$METERAI_6000 = 0;
				} else {
					$METERAI_3000 = 0;
					$METERAI_6000 = 1;
				}
			}
		}

		if ($IJIN!="" && $this->confirm_flag==1) {		
			$url = $this->API_URL."/Billing/SaveEMeteraiUsage?lok=".urlencode($LOK)."&ijin=".urlencode($IJIN)
				."&tgl1=".urlencode(date("m/d/Y",strtotime($p_tgl1)))."&tgl2=".urlencode(date("m/d/Y",strtotime($p_tgl2)))
				."&total=".urlencode($TOTAL_EMETERAI)."&met3=".urlencode($METERAI_3000)."&met6=".urlencode($METERAI_6000)."&tipe=".urlencode("KWITANSI TAGIHAN")
				."&user=".urlencode($user);
			//die($url);
			$simpanData = json_decode(file_get_contents($url), true);
		}

		$group_footer = "	<div id='div_column_header' style='width:95%!important;'>";
		$group_footer.= "		<div style='width:3%;".$style_footer.$center."'>&nbsp;</div>";
		$group_footer.= "		<div style='width:3%;".$style_footer.$kiri."'>&nbsp;</div>";
		$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>&nbsp;</div>";
		$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>&nbsp;</div>";
		$group_footer.= "		<div style='width:8%;".$style_footer.$kiri."'>&nbsp;</div>";
		$group_footer.= "		<div style='width:6%;".$style_footer.$kanan."'><b>TOTAL</b></div>";
		$group_footer.= "		<div style='width:18%;".$style_footer.$kiri."'><b>".$IJIN."</b></div>";
		$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>&nbsp;</div>";
		$group_footer.= "		<div style='width:8%;".$style_footer.$kanan."'>&nbsp;</div>";
		$group_footer.= "		<div style='width:4%;".$style_footer.$center."'><b>".$METERAI_3000."</b></div>";
		$group_footer.= "		<div style='width:4%;".$style_footer.$center."'><b>".$METERAI_6000."</b></div>";
		$group_footer.= "		<div style='width:5%;".$style_footer.$kanan."'><b>".number_format($TOTAL_EMETERAI)."</b></div>";
		$group_footer.= "	</div>";	//close div_column_header

		$content_html.= $group_footer;
		$content_html.= "</div>";

		if ($this->confirm_flag==1) {

			$this->Pdf_Report($header_html, $content_html, "", $p_tgl1, $p_tgl2);

		} else if ($this->pdf_flag==1) {

			$this->Pdf_Report($header_html, $content_html);

		} else {
			$row_btn = "";

			if ($opt=="PERIODE") {
				$row_btn.= "<form action='Confirm'>";
				$row_btn.= '<input type="hidden" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1" value="'.date("m/d/Y",strtotime($p_tgl1)).'">';
				$row_btn.= '<input type="hidden" class="form-control" id="dp2" placeholder="mm/dd/yyyy" name="dp2" value="'.date("m/d/Y",strtotime($p_tgl2)).'">';
				$row_btn.= '<input type="submit" value="Confirm">';
				$row_btn.= '</form>';
			}

			$view['title'] = "Report Pemakaian Meterai Elektronik";
			$view['content_html'] = $row_btn.$header_html.$content_html;
			$this->RenderView('ReportFinanceResult',$view);
		}
	}

	public function Pdf_Report($header="", $content="", $footer="", $tgl1="", $tgl2="")
	{
		$data = array();
		set_time_limit(60);
    	require_once __DIR__ . '\vendor\autoload.php';
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
        	$lok = $_SESSION["conn"]->BranchId;
		
	 		$main_dir = "D:/";
	 		$th = date("Y", strtotime($tgl1));
	 		$bl = date("m", strtotime($tgl1));
			$pdf_dir = $main_dir."/Report/EMeterai/".$th;
			$nm_file = "pemakaian_emeterai_".$lok."_".$th."_".$bl."_".date("d",strtotime($tgl1)).date("d",strtotime($tgl2)).".pdf";
	        //Jika folder save belum ada maka create dahulu
	        if (!is_dir($pdf_dir)) {
				mkdir($pdf_dir, 0777, TRUE);	
			}
	        $mpdf->Output($pdf_dir."/".$nm_file, \Mpdf\Output\Destination::FILE);

			$this->email->clear(true);
			$this->email->from("bitautoemail.noreply@gmail.com", "MYCOMPANY.ID AUTO-EMAIL");
			$this->email->to("ebilling.bhakti.jkt@gmail.com");
			$this->email->cc("itdev.dist@bhakti.co.id");
			$this->email->attach($pdf_dir."/".$nm_file);
			$email_content = $_SESSION["logged_in"]["username"]." mengirimkan Laporan Pemakaian Meterai Elektronik<br>";
			$email_content.= "Periode : ".date("d-M-Y",strtotime($tgl1))." s/d ".date("d-M-Y", strtotime($tgl2));
			$this->email->subject("Report EMeterai [".$lok."][".$th."][".$bl."][".date("d",strtotime($tgl1))."-".date("d",strtotime($tgl2))."]");
			$this->email->message($email_content);
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

	    } else {
	        $mpdf->Output();
	    }
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