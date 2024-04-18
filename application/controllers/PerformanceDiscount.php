<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class PerformanceDiscount extends MY_Controller 
{
	public $pdf_flag = 0;
	public $confirm_flag=0;
	public $excel_flag=0;
	public $email_flag=0;

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
		$this->load->model("PerformanceDiscountModel", "PDModel");
	}

	public function index()
	{
		$data = array();
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}		
		$data['title'] = 'PERFORMANCE DISCOUNT';
		$data["opt"] = "";
		$data["formURL"] = "PerformanceDiscount";
		$data["btnPDF"] = 0;
		$data["btnExcel"] = 0;
		$data["err"] = "";

		$api = 'APITES';
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}
		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;

		//$url = str_replace("//","/", $url."/PerformanceDiscount/ListPD?api=APITES";
        //$GetListPD = json_decode(file_get_contents($url), true);

		$this->RenderView('PerformanceDiscountForm',$data);
	}

	public function PerformanceDiscount()
	{
		$dp1=$th."-".$bl."-"."01";
		$dp =date_create($dp1);
		date_add($dp, date_interval_create_from_date_string("1 month"));
		date_add($dp, date_interval_create_from_date_string("-1 day"));
		$dp2=$dp->format("Y-m-d");

		$api = 'APITES';
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}
		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;
		//$pwd = SQL_PWD;
		
		set_time_limit(60);
		/*die($url.API_BKT."/ReportOmzet/ReportOmzetBulanan?api=".urlencode($api)
			."&tgl1=".urlencode($dp1)."&tgl2=".urlencode($dp2)
			."&svr=".urlencode($svr)."&db=".urlencode($db));*/

		$GetData = json_decode(file_get_contents($url.API_BKT."/PerformanceDiscount/PerformanceDiscount?api=".urlencode($api)), true);
		//die(json_encode($GetData));

		if ($GetData["result"]=="sukses") {
			$this->Preview_ReportOmzetCabang($th, $bl, $dp1, $dp2, $GetData["data"]);
		} else {
			die($GetData["error"]);
		}
	}

	public function Preview_ReportOmzetCabang($th, $bl, $dp1, $dp2, $data) 
	{
		//die(json_encode($data));

		$style_col_ganjil ="float:left;line-height:15px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#ffffcc;font-size:10px;";
		$style_col_genap = "float:left;line-height:15px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#fff;font-size:10px;";
		$style_header= "float:left;min-height:20px;line-height:15px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#cccccc;font-size:10px;";
		$style_footer= "float:left;min-height:20px;line-height:20px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#cccccc;font-size:10px;";

		$kanan = "text-align:right;padding-right:5px;";
		$kiri  = "text-align:left; padding-left: 5px;";
		$center= "text-align:center;";

		$header_html = "<div style='clear:both;height:25px;'></div>";
		$header_html.= "<div id='div_header' style='padding-left:10px;'>";
		$header_html.= "	<div><h2>DATA OMZET BULANAN</h2></div>";
		$header_html.= "	<div><b>Periode : ".date("F Y", strtotime($dp1))."</b></div>";
		$header_html.= "</div>";	//close div_header

		$style_col = $style_col_genap;
		
		$group_header = "	<div id='div_column_header' style='width:95%!important;'>";
		$group_header.= "		<div style='width:10%;".$style_header.$kiri."'><b>Wilayah</b></div>";
		$group_header.= "		<div style='width:10%;".$style_header.$kiri."'><b>Divisi</b></div>";
		$group_header.= "		<div style='width:10%;".$style_header.$kiri."'><b>Merk</b></div>";
		$group_header.= "		<div style='width:10%;".$style_header.$kanan."'><b>Jual</b></div>";
		$group_header.= "		<div style='width:9%;".$style_header.$kanan."'><b>ReturBagus</b></div>";
		$group_header.= "		<div style='width:9%;".$style_header.$kanan."'><b>ReturCacat</b></div>";
		$group_header.= "		<div style='width:9%;".$style_header.$kanan."'><b>Disc</b></div>";
		$group_header.= "		<div style='width:10%;".$style_header.$kanan."'><b>OmzetNetto</b></div>";
		$group_header.= "		<div style='width:10%;".$style_header.$kiri."'><b>Status</b></div>";
		$group_header.= "	</div>";	//close div_column_header
		$group_header.= "	<div style='clear:both;'></div>";

		$KATEGORI_BRG = "";
		$LOK = "";
		$WILAYAH = "";
		$DIVISI = "";
		$MERK = "";
		$JUAL = 0;
		$RETURB = 0;
		$RETURC = 0;
		$DISC=0;
		$OMZET_NETTO=0;

		$user = $_SESSION["logged_in"]["useremail"];

		$TOTAL_JUAL = 0;
		$TOTAL_RETURB = 0;
		$TOTAL_RETURC = 0;
		$TOTAL_DISC = 0;
		$TOTAL_OMZET = 0;

		$content_html = "<div class='div_body' style='font-size:9pt!important;'>";
		$RESULTS = array();

		for($i=0;$i<count($data);$i++)
		{
			if ($this->confirm_flag==1) {
				echo("Kategori Barang :".$data[$i]["KATEGORI_BRG"]."; ");
				echo("Wilayah :".$data[$i]["WILAYAH"]."; ");
				echo("Divisi :".$data[$i]["DIVISI"]."; ");
				echo("Merk :".$data[$i]["MERK"].";<br>");
			}
			$n = $i+1;

			if ($i%2==1)
				$style_col = $style_col_genap;
			else
				$style_col = $style_col_ganjil;

			$data[$i]["STATUS"] = $this->OmzetModel->CheckStatusOmzet($th, $bl, $data[$i]);
			//die($data[$i]["STATUS"]);
			if ($this->confirm_flag==1) {
				if ($data[$i]["STATUS"]=="SAVED") {
					echo("DelDataOmzet ..<br>");
					$this->OmzetModel->DelDataOmzet($data[$i]["KATEGORI_BRG"],$data[$i]["WILAYAH"], $th, $bl, $data[$i]["KD_LOKASI"]);
					echo("DelDataOmzet Done<br>");
				}
				if ($data[$i]["STATUS"]!="SAVED AND LOCKED") {
					echo("Saving Data ...<br>");
					$this->OmzetModel->SaveDataOmzet($th, $bl, $data[$i], $user);
					echo("Saving Data Done<br>");
				}
			}

			//die(json_encode($data));
			//die($data[$i]["KATEGORI_BRG"]);
			if ($KATEGORI_BRG==trim($data[$i]["KATEGORI_BRG"])) {

				$height = 20;

				$content_html.= "	<div id='div_column_header' style='width:95%!important;'>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["WILAYAH"]."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["DIVISI"]."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["MERK"]."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_JUAL"])."</div>";
				$content_html.= "		<div style='width:9%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_RETURB"])."</div>";
				$content_html.= "		<div style='width:9%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_RETURC"])."</div>";
				$content_html.= "		<div style='width:9%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_DISC"])."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["OMZET_NETTO"])."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["STATUS"]."</div>";
				$content_html.= "	</div>";
				$content_html.= "	<div style='clear:both;'></div>";

				$TOTAL_JUAL  += $data[$i]["TOTAL_JUAL"];
				$TOTAL_RETURB+= $data[$i]["TOTAL_RETURB"];
				$TOTAL_RETURC+= $data[$i]["TOTAL_RETURC"];
				$TOTAL_DISC  += $data[$i]["TOTAL_DISC"];
				$TOTAL_OMZET += $data[$i]["OMZET_NETTO"];
				
				//die ($content_html);
			} else {

				if ($KATEGORI_BRG!="") {
					
					$group_footer = "	<div id='div_column_header' style='width:95%!important;'>";
					$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>&nbsp;</div>";
					$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>TOTAL</div>";
					$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>&nbsp;</div>";
					$group_footer.= "		<div style='width:10%;".$style_footer.$kanan."'><b>".number_format($TOTAL_JUAL)."</b></div>";
					$group_footer.= "		<div style='width:9%;".$style_footer.$kanan."'><b>".number_format($TOTAL_RETURB)."</b></div>";
					$group_footer.= "		<div style='width:9%;".$style_footer.$kanan."'><b>".number_format($TOTAL_RETURC)."</b></div>";
					$group_footer.= "		<div style='width:9%;".$style_footer.$kanan."'><b>".number_format($TOTAL_DISC)."</b></div>";
					$group_footer.= "		<div style='width:10%;".$style_footer.$kanan."'><b>".number_format($TOTAL_OMZET)."</b></div>";
					$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'><b>&nbsp;</b></div>";
					$group_footer.= "	</div>";	//close div_column_header
					$group_footer.= "	<div style='clear:both;'></div>";

					$content_html.= $group_footer;					
				}
				

				$height = 40;
				$content_html.= "	<div id='div_column_header' style='width:95%!important;line-height:50px;vertical-align:middle;'>";
				$content_html.= "		<div style='width:15%;height:".$height."px;float:left;".$kiri."'>KATEGORI BARANG :</div>";
				$content_html.= "		<div style='width:75%;height:".$height."px;float:left;".$kiri."'><b>".(($data[$i]["KATEGORI_BRG"]=="P")?"PRODUK":"SPAREPART")."</b></div>";
				$content_html.= "	</div>";
				$content_html.= "	<div style='clear:both;height:5px;'></div>";
				$content_html.= $group_header;
				$content_html.= "	<div id='div_column_header' style='width:95%!important;'>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["WILAYAH"]."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["DIVISI"]."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["MERK"]."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_JUAL"])."</div>";
				$content_html.= "		<div style='width:9%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_RETURB"])."</div>";
				$content_html.= "		<div style='width:9%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_RETURC"])."</div>";
				$content_html.= "		<div style='width:9%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_DISC"])."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["OMZET_NETTO"])."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["STATUS"]."</div>";
				$content_html.= "	</div>";
				$content_html.= "	<div style='clear:both;'></div>";
				


				$KATEGORI_BRG = $data[$i]["KATEGORI_BRG"];
				$LOK = $data[$i]["KD_LOKASI"];

				$TOTAL_JUAL  = $data[$i]["TOTAL_JUAL"];
				$TOTAL_RETURB= $data[$i]["TOTAL_RETURB"];
				$TOTAL_RETURC= $data[$i]["TOTAL_RETURC"];
				$TOTAL_DISC  = $data[$i]["TOTAL_DISC"];
				$TOTAL_OMZET = $data[$i]["OMZET_NETTO"];

				
			}
		}

		//die("content_html");

		$group_footer = "	<div id='div_column_header' style='width:95%!important;'>";
		$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>&nbsp;</div>";
		$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>TOTAL</div>";
		$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>&nbsp;</div>";
		$group_footer.= "		<div style='width:10%;".$style_footer.$kanan."'><b>".number_format($TOTAL_JUAL)."</b></div>";
		$group_footer.= "		<div style='width:9%;".$style_footer.$kanan."'><b>".number_format($TOTAL_RETURB)."</b></div>";
		$group_footer.= "		<div style='width:9%;".$style_footer.$kanan."'><b>".number_format($TOTAL_RETURC)."</b></div>";
		$group_footer.= "		<div style='width:9%;".$style_footer.$kanan."'><b>".number_format($TOTAL_DISC)."</b></div>";
		$group_footer.= "		<div style='width:10%;".$style_footer.$kanan."'><b>".number_format($TOTAL_OMZET)."</b></div>";
		$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>&nbsp;</div>";
		$group_footer.= "	</div>";	//close div_column_header

		$content_html.= $group_footer;
		$content_html.= "</div>";
		$content_html.= "<div style='clear:both;height:80px;'></div>";



		if ($this->confirm_flag==1) {

			$this->Pdf_Report($header_html, $content_html, "", $dp1, $dp2);

		} else if ($this->pdf_flag==1) {

			$this->Pdf_Report($header_html, $content_html);

		} else {
			$row_btn = "";

			$row_btn.= "<form action='ConfirmOmzetCabang'>";
			$row_btn.= "<div style='position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;padding:10px;'>";
			$row_btn.= '	<input type="hidden" class="form-control" id="th" name="th" value="'.$th.'">';
			$row_btn.= '	<input type="hidden" class="form-control" id="bl" name="bl" value="'.$bl.'">';
			$row_btn.= '	<input type="submit" value="SIMPAN">';
			$row_btn.= '</div>';
			$row_btn.= '</form>';

			$view['title'] = "Report Omzet Bulanan";
			$view['content_html'] = $row_btn.$header_html.$content_html;
	        $this->SetTemplate('template/notemplate');
			$this->RenderView('ReportResultView',$view);
		}
	}

	public function Pdf_Report($header="", $content="", $footer="", $tgl1="", $tgl2="")
	{
		echo("Create Pdf_Report <br>");
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
        	//die($_SESSION["conn"]->BranchId);
        	$lok = $_SESSION["conn"]->BranchId;
		
	 		$main_dir = "C:/";
	 		$th = date("Y", strtotime($tgl1));
	 		$bl = date("m", strtotime($tgl1));
			$pdf_dir = $main_dir."/Report/OmzetCabang/".$th;
			$nm_file = "omzet_".$lok."_".$th."_".$bl.".pdf";
	        //Jika folder save belum ada maka create dahulu
	        if (!is_dir($pdf_dir)) {
				mkdir($pdf_dir, 0777, TRUE);	
			}
			//die("here");
	        $mpdf->Output($pdf_dir."/".$nm_file, \Mpdf\Output\Destination::FILE);
	        echo("Pdf_Report Done <br>");

			$this->email->clear(true);
			$this->email->from("bitautoemail.noreply@gmail.com", "MYCOMPANY.ID AUTO-EMAIL");
			$this->email->to("ina@bhakti.co.id");
			$this->email->cc("itdev.dist@bhakti.co.id");
			$this->email->attach($pdf_dir."/".$nm_file);
			$email_content = $_SESSION["logged_in"]["username"]." mengirimkan Data Omzet Cabang<br>";
			$email_content.= "Periode : ".date("d-M-Y",strtotime($tgl1))." s/d ".date("d-M-Y", strtotime($tgl2));
			$this->email->subject("Data Omzet Cabang [".$lok."][".$th."][".$bl."]");
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
			echo("Email Sent <br>");
	    } else {
	        $mpdf->Output();
	    }
	}

	public function OmzetNasional()
	{
		$data = array();
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}		
		$data['title'] = 'OMZET | REPORT OMZET NASIONAL';
		$data["formURL"] = "ReportOmzet/RekapOmzetNasional";
		$data["btnPDF"] = 0;
		$data["btnExcel"] = 0;
		$data["opt"] = "OMZET NASIONAL";
		$data["err"] = "";
		$this->RenderView('ReportOmzetNettoForm',$data);
	}

	public function RekapOmzetNasional()
	{
		$data = array();
		$page_title = 'Report Omzet';
		$this->confirm_flag = 0;
		
		if (isset($_POST["btnExportExcel"]) || isset($_POST["btnEmailExcel"])) {
			$this->excel_flag = 1;
		} else {
			$this->excel_flag = 0;
		}

		if (isset($_POST["btnEmailExcel"])) {
			$this->email_flag = 1;
		} else {
			$this->email_flag = 0;
		}

		$th = $_POST["Tahun"];
		$bl = $_POST["Bulan"];

		$wilayahP = $this->OmzetModel->OmzetBulanan_GetListWilayah("P", $th, $bl);
		$wilayahSP = $this->OmzetModel->OmzetBulanan_GetListWilayah("S", $th, $bl);
		$omzetP = $this->OmzetModel->OmzetBulanan_Gets("P", $th, $bl);
		$omzetSP = $this->OmzetModel->OmzetBulanan_Gets("S", $th, $bl);
		$omzetDivMerkP = $this->OmzetModel->OmzetBulanan_OmzetDivisiMerk("P",$th, $bl);
		$omzetDivMerkSP = $this->OmzetModel->OmzetBulanan_OmzetDivisiMerk("S",$th, $bl);
		$omzetWilayahP = $this->OmzetModel->OmzetBulanan_OmzetWilayah("P",$th, $bl);
		$omzetWilayahSP = $this->OmzetModel->OmzetBulanan_OmzetWilayah("S",$th, $bl);

	    $tgl = $th."-".$bl."-"."01";

	    $header = "<b>PT.BHAKTI IDOLA TAMA</b><br>";
	    $header.= "<b>REPORT OMZET NASIONAL</b><br>";
	    $header.= "<b>Periode : ".date("F Y",strtotime($tgl))."</b>";

		if($this->excel_flag == 1){
			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->setTitle('ReportOmzetNasional');
			$this->excel->getActiveSheet()->setCellValue('A1', 'REPORT OMZET NASIONAL');
			$this->excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
			$this->excel->getActiveSheet()->setCellValue('A2', 'Periode : '.date("F Y", strtotime($tgl)));
			$this->excel->getActiveSheet()->getStyle("A1:A2")->getFont()->setBold(true);
		}
		$currcol = 0;
		$currrow = 5;


	    $body = "<style>";
	    $body.= " .td-center { text-align:center;width:500px!important;font-size:9pt;padding:2px 10px 2px 10px; } ";
	    $body.= " .td-right { text-align:right;font-size:9pt;width:100px!important;padding:2px 10px 2px 10px; } ";
	    $body.= " .td-left { text-align:left;font-size:9pt;width:150px!important;padding:2px 10px 2px 10px; } ";
	    $body.= " .cellHd { background-color:black;color:white;font-weight:bold; } ";
	    $body.= " .cellFt { background-color:#c2e0ab;color:#000;font-weight:bold;border:1px solid #ccc; } ";
	    $body.= "</style>";

	    $KATEGORI_BRG = "";
	    $WILAYAH = "";
	    $WilayahP = array();
	    $WilayahSP= array();
	    $wp = 0;
	    $ws = 0;
	    $TotalOmzetP = array();
	    $TotalOmzetSP= array();


		if($this->excel_flag == 1){
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Omzet Produk (Dalam Rp.)');
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);

			$currrow+= 2;
			$currcol = 0;
		}

	    //PRODUK - START
	    $tableP = array("header"=>"","body"=>"","footer"=>"");
	    $tableP["header"].="  <tr>";
	    $tableP["header"].="    <td class='td-left cellHd' rowspan='2'>Divisi</td>";
	    $tableP["header"].="    <td class='td-left cellHd' rowspan='2'>Merk</td>";
	    foreach($wilayahP as $w)
	    {
	      $wp+=1;
	      $tableP["header"].="    <td colspan='5' class='td-center cellHd'>".$w->Wilayah."</td>";
	      array_push($WilayahP, $w->Wilayah);
	    }
	    $tableP["header"].="    <td rowspan='2' class='td-right cellHd'><b>Total</b></td>";
	    $tableP["header"].="  </tr>";
	    $tableP["header"].="  <tr>";
	    for ($i=0;$i<$wp;$i++) {
	      $tableP["header"].="    <td class='td-right cellHd'>Sale</td>";
	      $tableP["header"].="    <td class='td-right cellHd'>Retur Bagus</td>";
	      $tableP["header"].="    <td class='td-right cellHd'>Retur Cacat</td>";
	      $tableP["header"].="    <td class='td-right cellHd'>Disc</td>";
	      $tableP["header"].="    <td class='td-right cellHd'>Total</td>";
	    }
	    $tableP["header"].="  </tr>";

	    $starttable=$currrow;
		if($this->excel_flag == 1){
			$nextrow = $currrow+1;
			$this->excel->getActiveSheet()->mergeCells('A'.(string)$currrow.':'.'A'.(string)$nextrow);
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
			$currcol += 1;
			$this->excel->getActiveSheet()->mergeCells('B'.(string)$currrow.':'.'B'.(string)$nextrow);	
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
			$currcol += 1;
			foreach($wilayahP as $w) {			
				$startcol = PHPExcel_Cell::stringFromColumnIndex($currcol);
				$currcol += 4;
				$endcol = PHPExcel_Cell::stringFromColumnIndex($currcol);
				$this->excel->getActiveSheet()->mergeCells($startcol.(string)$currrow.':'.$endcol.(string)$currrow);
				$currcol -= 4;
				$this->excel->getActiveSheet()->getStyle($startcol.(string)$currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $w->Wilayah);
				$currcol += 5;
			}
			$thiscol = PHPExcel_Cell::stringFromColumnIndex($currcol);
			$this->excel->getActiveSheet()->mergeCells($thiscol.(string)$currrow.':'.$thiscol.(string)$nextrow);	
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	

			$currrow+= 1;
			$currcol = 0;
			$lastrow = $currrow-1;
			//$this->excel->getActiveSheet()->mergeCells('A'.(string)$lastrow.':'.'A'.(string)$currrow);
			//$this->excel->getActiveSheet()->mergeCells('B'.(string)$lastrow.':'.'B'.(string)$currrow);			
			$currcol+= 2;
			foreach($wilayahP as $w) {
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Sale');
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Retur Bagus');
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Retur Cacat');
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Disc');
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
				$currcol += 1;
			}
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	
			$currrow+= 1;
			$currcol = 0;
		}

	    foreach($omzetDivMerkP as $odm) {
			$tableP["body"].=" <tr>";
			$tableP["body"].="  <td class='td-left'><b>".$odm->Divisi."</b></td>";
			$tableP["body"].="  <td class='td-left'>".$odm->Merk."</td>";
			for($i=0;$i<count($WilayahP);$i++) {
			$ketemu = false;
			foreach($omzetP as $o) {
			  if ($o->Divisi==$odm->Divisi && $o->Merk==$odm->Merk && $o->Wilayah==$WilayahP[$i] && $ketemu==false) {
			    $tableP["body"].="  <td class='td-right'>".number_format($o->TotalJual)."</td>";
			    $tableP["body"].="  <td class='td-right'>".number_format($o->TotalReturBagus)."</td>";
			    $tableP["body"].="  <td class='td-right'>".number_format($o->TotalReturCacat)."</td>";
			    $tableP["body"].="  <td class='td-right'>".number_format($o->TotalDisc)."</td>";
			    $tableP["body"].="  <td class='td-right'>".number_format($o->OmzetNetto)."</td>";
			    $ketemu = true;     
			  }
			}
			if ($ketemu==false) {
			  $tableP["body"].="  <td class='td-right'>0</td>";
			  $tableP["body"].="  <td class='td-right'>0</td>";
			  $tableP["body"].="  <td class='td-right'>0</td>";
			  $tableP["body"].="  <td class='td-right'>0</td>";
			  $tableP["body"].="  <td class='td-right'>0</td>";
			}
			}
			$tableP["body"].="  <td class='td-right'>".number_format($odm->TotalOmzetNetto)."</td>";
			$tableP["body"].="</tr>";

			if($this->excel_flag == 1){
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $odm->Divisi);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $odm->Merk);
				$currcol += 1;
				for($i=0;$i<count($WilayahP);$i++) {
					$ketemu=false;
					foreach($omzetP as $o) {
					  	if ($o->Divisi==$odm->Divisi && $o->Merk==$odm->Merk && $o->Wilayah==$WilayahP[$i] && $ketemu==false) {
							$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalJual);
							$currcol += 1;
							$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturBagus);
							$currcol += 1;
							$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturCacat);
							$currcol += 1;
							$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalDisc);
							$currcol += 1;
							$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->OmzetNetto);
							$currcol += 1;
							$ketemu=true;
						}
					}
					if ($ketemu==false) {
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;					
					}
				}
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $odm->TotalOmzetNetto);

				$currrow+= 1;
				$currcol = 0;
			}
	    }

	    //row total
	    $TOTAL = 0;
	    $tableP["footer"] =" <tr>";
	    $tableP["footer"].="  <td class='td-left cellFt'><b>Total</b></td>";
	    $tableP["footer"].="  <td class='td-left cellFt'>&nbsp;</td>";
	    for($i=0;$i<count($WilayahP);$i++) {
	      $ketemu = false;
	      foreach($omzetWilayahP as $o) {
	        if ($o->Wilayah==$WilayahP[$i]) {
	          $tableP["footer"].="  <td class='td-right cellFt'>".number_format($o->TotalJual)."</td>";
	          $tableP["footer"].="  <td class='td-right cellFt'>".number_format($o->TotalReturBagus)."</td>";
	          $tableP["footer"].="  <td class='td-right cellFt'>".number_format($o->TotalReturCacat)."</td>";
	          $tableP["footer"].="  <td class='td-right cellFt'>".number_format($o->TotalDisc)."</td>";
	          $tableP["footer"].="  <td class='td-right cellFt'>".number_format($o->OmzetNetto)."</td>";
	          $ketemu = true;     
	          $TOTAL+=$o->OmzetNetto;
	        }
	      }
	      if ($ketemu==false) {
	        $tableP["footer"].="  <td class='td-right cellFt'>0</td>";
	        $tableP["footer"].="  <td class='td-right cellFt'>0</td>";
	        $tableP["footer"].="  <td class='td-right cellFt'>0</td>";
	        $tableP["footer"].="  <td class='td-right cellFt'>0</td>";
	        $tableP["footer"].="  <td class='td-right cellFt'>0</td>";
	      }
	    }
	    $tableP["footer"].="  <td class='td-right cellFt'>".number_format($TOTAL)."</td>";
	    $tableP["footer"].="</tr>";

		if($this->excel_flag == 1){
			$TOTAL = 0;
			$this->excel->getActiveSheet()->mergeCells('A'.(string)$currrow.':'.'B'.(string)$currrow);
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
			$currcol += 2;
		    for($i=0;$i<count($WilayahP);$i++) {
				$ketemu = false;
				foreach($omzetWilayahP as $o) {
					if ($o->Wilayah==$WilayahP[$i]) {
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalJual);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturBagus);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturCacat);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalDisc);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->OmzetNetto);
						$currcol += 1;
					    $ketemu = true;     
					  	$TOTAL+=$o->OmzetNetto;
					}
				}
				if ($ketemu==false) {
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
				}
			}
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	

		}	    
	    //PRODUK - END

		$currrow+= 2;
		$currcol = 0;

	    //SPAREPART - START
		if($this->excel_flag == 1){
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Omzet Sparepart (Dalam Rp.)');
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);

			$currrow+= 2;
			$currcol = 0;
		}

	    $tableS = array("header"=>"","body"=>"","footer"=>"");
	    $tableS["header"].="  <tr>";
	    $tableS["header"].="    <td rowspan='2' class='td-left cellHd'><b>Divisi</b></td>";
	    $tableS["header"].="    <td rowspan='2' class='td-left cellHd'><b>Merk</b></td>";
	    foreach($wilayahP as $w)
	    {
	      $ws+=1;
	      $tableS["header"].="    <td colspan='5' class='td-center cellHd'><b>".$w->Wilayah."</b></td>";
	      //array_push($WilayahSP, $w->Wilayah);
	    }
	    $tableS["header"].="    <td rowspan='2' class='td-right cellHd'><b>Total</b></td>";
	    $tableS["header"].="  </tr>";
	    $tableS["header"].="  <tr>";
	    for ($i=0;$i<$wp;$i++) {
	      $tableS["header"].="    <td class='td-right cellHd'><b>Sale</b></td>";
	      $tableS["header"].="    <td class='td-right cellHd'><b>Retur Bagus</b></td>";
	      $tableS["header"].="    <td class='td-right cellHd'><b>Retur Cacat</b></td>";
	      $tableS["header"].="    <td class='td-right cellHd'><b>Disc</b></td>";
	      $tableS["header"].="    <td class='td-right cellHd'><b>Total</b></td>";
	    }
	    $tableS["header"].="  </tr>";
		if($this->excel_flag == 1){
			$nextrow = $currrow+1;
			$this->excel->getActiveSheet()->mergeCells('A'.(string)$currrow.':'.'A'.(string)$nextrow);
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
			$currcol += 1;
			$this->excel->getActiveSheet()->mergeCells('B'.(string)$currrow.':'.'B'.(string)$nextrow);	
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
			$currcol += 1;
			foreach($wilayahP as $w) {
				$startcol = PHPExcel_Cell::stringFromColumnIndex($currcol);
				$currcol += 4;
				$endcol = PHPExcel_Cell::stringFromColumnIndex($currcol);
				$currcol -= 4;
				$this->excel->getActiveSheet()->mergeCells($startcol.(string)$currrow.':'.$endcol.(string)$currrow);
				$this->excel->getActiveSheet()->getStyle($startcol.(string)$currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $w->Wilayah);
				$currcol += 5;
			}
			$thiscol = PHPExcel_Cell::stringFromColumnIndex($currcol);
			$this->excel->getActiveSheet()->mergeCells($thiscol.(string)$currrow.':'.$thiscol.(string)$nextrow);	
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	

			$currrow+= 1;
			$currcol = 0;
			$lastrow = $currrow-1;
			//$this->excel->getActiveSheet()->mergeCells('A'.(string)$lastrow.':'.'A'.(string)$currrow);
			//$this->excel->getActiveSheet()->mergeCells('B'.(string)$lastrow.':'.'B'.(string)$currrow);			
			$currcol+= 2;
			foreach($wilayahP as $w) {
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Sale');
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Retur Bagus');
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Retur Cacat');
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Disc');
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
				$currcol += 1;
			}
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	

			$currrow+= 1;
			$currcol = 0;
		}

	    foreach($omzetDivMerkSP as $odm) {
			$tableS["body"].=" <tr>";
			$tableS["body"].="  <td class='td-left'>".$odm->Divisi."</td>";
			$tableS["body"].="  <td class='td-left'>".$odm->Merk."</td>";
			for($i=0;$i<count($WilayahP);$i++) {
			$ketemu = false;
			foreach($omzetSP as $o) {
			  if ($o->Divisi==$odm->Divisi && $o->Merk==$odm->Merk && $o->Wilayah==$WilayahP[$i]) {
			    $tableS["body"].="  <td class='td-right'>".number_format($o->TotalJual)."</td>";
			    $tableS["body"].="  <td class='td-right'>".number_format($o->TotalReturBagus)."</td>";
			    $tableS["body"].="  <td class='td-right'>".number_format($o->TotalReturCacat)."</td>";
			    $tableS["body"].="  <td class='td-right'>".number_format($o->TotalDisc)."</td>";
			    $tableS["body"].="  <td class='td-right'>".number_format($o->OmzetNetto)."</td>";
			    $ketemu = true;            
			  }
			}
			if ($ketemu==false) {
			  $tableS["body"].="  <td class='td-right'>0</td>";
			  $tableS["body"].="  <td class='td-right'>0</td>";
			  $tableS["body"].="  <td class='td-right'>0</td>";
			  $tableS["body"].="  <td class='td-right'>0</td>";
			  $tableS["body"].="  <td class='td-right'>0</td>";
			}
			}
			$tableS["body"].="  <td class='td-right'>".number_format($odm->TotalOmzetNetto)."</td>";
			$tableS["body"].="</tr>";

			if($this->excel_flag == 1){
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $odm->Divisi);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $odm->Merk);
				$currcol += 1;
				for($i=0;$i<count($WilayahP);$i++) {
					$ketemu=false;
					foreach($omzetSP as $o) {
					  	if ($o->Divisi==$odm->Divisi && $o->Merk==$odm->Merk && $o->Wilayah==$WilayahP[$i]) {
							$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalJual);
							$currcol += 1;
							$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturBagus);
							$currcol += 1;
							$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturCacat);
							$currcol += 1;
							$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalDisc);
							$currcol += 1;
							$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->OmzetNetto);
							$currcol += 1;
							$ketemu=true;
						}
					}
					if ($ketemu==false) {
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;					
					}					
				}
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $odm->TotalOmzetNetto);

				$currrow+= 1;
				$currcol = 0;
			}	      
	    }
	    //row total
	    $TOTAL = 0;
	    $tableS["footer"] =" <tr>";
	    $tableS["footer"].="  <td class='td-left cellFt'><b>Total</b></td>";
	    $tableS["footer"].="  <td class='td-left cellFt'>&nbsp;</td>";
	    for($i=0;$i<count($WilayahP);$i++) {
	      $ketemu = false;
	      foreach($omzetWilayahSP as $o) {
	        if ($o->Wilayah==$WilayahP[$i]) {
	          $tableS["footer"].="  <td class='td-right cellFt'>".number_format($o->TotalJual)."</td>";
	          $tableS["footer"].="  <td class='td-right cellFt'>".number_format($o->TotalReturBagus)."</td>";
	          $tableS["footer"].="  <td class='td-right cellFt'>".number_format($o->TotalReturCacat)."</td>";
	          $tableS["footer"].="  <td class='td-right cellFt'>".number_format($o->TotalDisc)."</td>";
	          $tableS["footer"].="  <td class='td-right cellFt'>".number_format($o->OmzetNetto)."</td>";
	          $ketemu = true;     
	          $TOTAL+=$o->OmzetNetto;
	        }
	      }
	      if ($ketemu==false) {
	        $tableS["footer"].="  <td class='td-right cellFt'>0</td>";
	        $tableS["footer"].="  <td class='td-right cellFt'>0</td>";
	        $tableS["footer"].="  <td class='td-right cellFt'>0</td>";
	        $tableS["footer"].="  <td class='td-right cellFt'>0</td>";
	        $tableS["footer"].="  <td class='td-right cellFt'>0</td>";
	      }
	    }
	    $tableS["footer"].="  <td class='td-right cellFt'>".number_format($TOTAL)."</td>";
	    $tableS["footer"].="</tr>";

		if($this->excel_flag == 1){
			$TOTAL = 0;
			$this->excel->getActiveSheet()->mergeCells('A'.(string)$currrow.':'.'B'.(string)$currrow);
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
			$currcol += 2;
		    for($i=0;$i<count($WilayahP);$i++) {
				$ketemu = false;
				foreach($omzetWilayahSP as $o) {
					if ($o->Wilayah==$WilayahP[$i]) {
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalJual);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturBagus);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturCacat);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalDisc);
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->OmzetNetto);
						$currcol += 1;
					    $ketemu = true;     
					  	$TOTAL+=$o->OmzetNetto;
					}
				}
				if ($ketemu==false) {
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
				}
			}
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	
		}	    	   
	    //SPAREPART - END

		$currrow+= 2;
		$currcol = 0;

	    //GABUNGAN
		if($this->excel_flag == 1){
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'GABUNGAN');
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);

			$currrow+= 2;
			$currcol = 0;
		}

	    $tableG = array("header"=>"","body"=>"","footer"=>"");
	    $tableG["header"].="  <tr>";
	    $tableG["header"].="    <td class='td-left cellHd' colspan='2' rowspan='2'>Kategori Barang</td>";
	    foreach($wilayahP as $w)
	    {
	      $tableG["header"].="    <td colspan='5' class='td-center cellHd'>".$w->Wilayah."</td>";
	    }
	    $tableG["header"].="    <td rowspan='2' class='td-right cellHd'><b>Total</b></td>";
	    $tableG["header"].="  </tr>";
	    $tableG["header"].="  <tr>";
	    for ($i=0;$i<$wp;$i++) {
	      $tableG["header"].="    <td class='td-right cellHd'>Sale</td>";
	      $tableG["header"].="    <td class='td-right cellHd'>Retur Bagus</td>";
	      $tableG["header"].="    <td class='td-right cellHd'>Retur Cacat</td>";
	      $tableG["header"].="    <td class='td-right cellHd'>Disc</td>";
	      $tableG["header"].="    <td class='td-right cellHd'>Total</td>";
	    }
	    $tableG["header"].="  </tr>";

		if($this->excel_flag == 1){
			$nextrow = $currrow+1;
			$this->excel->getActiveSheet()->mergeCells('A'.(string)$currrow.':'.'B'.(string)$nextrow);
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Kategori Barang');
			$currcol += 2;
			foreach($wilayahP as $w) {
				$startcol = PHPExcel_Cell::stringFromColumnIndex($currcol);
				$currcol += 4;
				$endcol = PHPExcel_Cell::stringFromColumnIndex($currcol);
				$this->excel->getActiveSheet()->mergeCells($startcol.(string)$currrow.':'.$endcol.(string)$currrow);
				$currcol -= 4;
				$this->excel->getActiveSheet()->getStyle($startcol.(string)$currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $w->Wilayah);
				$currcol += 5;
			}
			$thiscol = PHPExcel_Cell::stringFromColumnIndex($currcol);
			$this->excel->getActiveSheet()->mergeCells($thiscol.(string)$currrow.':'.$thiscol.(string)$nextrow);	
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	

			$currrow+= 1;
			$currcol = 0;
			$lastrow = $currrow-1;
			//$this->excel->getActiveSheet()->mergeCells('A'.(string)$lastrow.':'.'A'.(string)$currrow);
			//$this->excel->getActiveSheet()->mergeCells('B'.(string)$lastrow.':'.'B'.(string)$currrow);			
			$currcol+= 2;
			foreach($wilayahP as $w) {
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Sale');
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Retur Bagus');
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Retur Cacat');
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Disc');
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
				$currcol += 1;
			}
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	

			$currrow+= 1;
			$currcol = 0;
		}

	    $TOTAL = 0;
	    $tableG["body"].=" <tr>";
	    $tableG["body"].="  <td class='td-left' colspan='2'><b>PRODUK</b></td>";
	    for($i=0;$i<count($WilayahP);$i++) {
	      $ketemu = false;
	      foreach($omzetWilayahP as $o) {
	        if ($o->Wilayah==$WilayahP[$i]) {
	          $tableG["body"].="  <td class='td-right'>".number_format($o->TotalJual)."</td>";
	          $tableG["body"].="  <td class='td-right'>".number_format($o->TotalReturBagus)."</td>";
	          $tableG["body"].="  <td class='td-right'>".number_format($o->TotalReturCacat)."</td>";
	          $tableG["body"].="  <td class='td-right'>".number_format($o->TotalDisc)."</td>";
	          $tableG["body"].="  <td class='td-right'>".number_format($o->OmzetNetto)."</td>";
	          $ketemu = true;     
	          $TOTAL+=$o->OmzetNetto;
	        }
	      }
	      if ($ketemu==false) {
	        $tableG["body"].="  <td class='td-right'>0</td>";
	        $tableG["body"].="  <td class='td-right'>0</td>";
	        $tableG["body"].="  <td class='td-right'>0</td>";
	        $tableG["body"].="  <td class='td-right'>0</td>";
	        $tableG["body"].="  <td class='td-right'>0</td>";
	      }
	    }
	    $tableG["body"].="  <td class='td-right'>".number_format($TOTAL)."</td>";
	    $tableG["body"].="</tr>";
	    $TOTAL_ALL = $TOTAL;

		if($this->excel_flag == 1){
		$this->excel->getActiveSheet()->mergeCells('A'.(string)$currrow.':'.'B'.(string)$currrow);
		$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'PRODUK');
		$currcol += 2;
	    for($i=0;$i<count($WilayahP);$i++) {
	      $ketemu = false;
	      foreach($omzetWilayahP as $o) {
	        if ($o->Wilayah==$WilayahP[$i]) {
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalJual);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturBagus);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturCacat);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalDisc);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->OmzetNetto);
				$currcol += 1;
	          	$ketemu = true;     
	        }
	      }
	      if ($ketemu==false) {
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
				$currcol += 1;
	      }
	    }
		$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL);

		$currrow+= 1;
		$currcol = 0;
		}

	    $tableG["body"].=" <tr>";
	    $tableG["body"].="  <td class='td-left' colspan='2'><b>SPAREPART</b></td>";
	    for($i=0;$i<count($WilayahP);$i++) {
	      $ketemu = false;
	      foreach($omzetWilayahSP as $o) {
	        if ($o->Wilayah==$WilayahP[$i]) {
	          $tableG["body"].="  <td class='td-right'>".number_format($o->TotalJual)."</td>";
	          $tableG["body"].="  <td class='td-right'>".number_format($o->TotalReturBagus)."</td>";
	          $tableG["body"].="  <td class='td-right'>".number_format($o->TotalReturCacat)."</td>";
	          $tableG["body"].="  <td class='td-right'>".number_format($o->TotalDisc)."</td>";
	          $tableG["body"].="  <td class='td-right'>".number_format($o->OmzetNetto)."</td>";
	          $ketemu = true;     
	          $TOTAL+=$o->OmzetNetto;
	        }
	      }
	      if ($ketemu==false) {
	        $tableG["body"].="  <td class='td-right'>0</td>";
	        $tableG["body"].="  <td class='td-right'>0</td>";
	        $tableG["body"].="  <td class='td-right'>0</td>";
	        $tableG["body"].="  <td class='td-right'>0</td>";
	        $tableG["body"].="  <td class='td-right'>0</td>";
	      }
	    }
	    $tableG["body"].="  <td class='td-right'>".number_format($TOTAL)."</td>";
	    $tableG["body"].="</tr>";
	    $TOTAL_ALL+= $TOTAL;

		if($this->excel_flag == 1){
		$this->excel->getActiveSheet()->mergeCells('A'.(string)$currrow.':'.'B'.(string)$currrow);
		$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'SPAREPART');
		$currcol += 2;
	    for($i=0;$i<count($WilayahP);$i++) {
	      $ketemu = false;
	      foreach($omzetWilayahSP as $o) {
	        if ($o->Wilayah==$WilayahP[$i]) {
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalJual);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturBagus);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturCacat);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalDisc);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $o->OmzetNetto);
				$currcol += 1;
	          	$ketemu = true;     
	        }
	      }
	      if ($ketemu==false) {
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 0);
				$currcol += 1;
	      }
	    }
		$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL);

		$currrow+= 1;
		$currcol = 0;
		}

	    //row total
	    $tableG["footer"] =" <tr>";
	    $tableG["footer"].="  <td class='td-left cellFt' colspan='2'><b>Total</b></td>";

	    if ($this->excel_flag==1) {
			$this->excel->getActiveSheet()->mergeCells('A'.(string)$currrow.':'.'B'.(string)$currrow);
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
			$currcol += 2;
		}

	    for($i=0;$i<count($WilayahP);$i++) {
	      $TotalJual = 0;
	      $TotalReturBagus = 0;
	      $TotalReturCacat = 0;
	      $TotalDisc = 0;
	      $OmzetNetto = 0;

	      foreach($omzetWilayahP as $o) {
	        if ($o->Wilayah==$WilayahP[$i]) {
	          $TotalJual+=$o->TotalJual;
	          $TotalReturBagus+=$o->TotalReturBagus;
	          $TotalReturCacat+=$o->TotalReturCacat;
	          $TotalDisc+=$o->TotalDisc;
	          $OmzetNetto+=$o->OmzetNetto;
	        }
	      }

	      foreach($omzetWilayahSP as $o) {
	        if ($o->Wilayah==$WilayahP[$i]) {
	          $TotalJual+=$o->TotalJual;
	          $TotalReturBagus+=$o->TotalReturBagus;
	          $TotalReturCacat+=$o->TotalReturCacat;
	          $TotalDisc+=$o->TotalDisc;
	          $OmzetNetto+=$o->OmzetNetto;
	        }
	      }
	      $tableG["footer"].="  <td class='td-right cellFt'>".number_format($TotalJual)."</td>";
	      $tableG["footer"].="  <td class='td-right cellFt'>".number_format($TotalReturBagus)."</td>";
	      $tableG["footer"].="  <td class='td-right cellFt'>".number_format($TotalReturCacat)."</td>";
	      $tableG["footer"].="  <td class='td-right cellFt'>".number_format($TotalDisc)."</td>";
	      $tableG["footer"].="  <td class='td-right cellFt'>".number_format($OmzetNetto)."</td>";

	      if ($this->excel_flag==1) {
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $TotalJual);
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $TotalReturBagus);
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $TotalReturCacat);
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $TotalDisc);
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $OmzetNetto);
			$currcol += 1;	      	
	      }
	    }
	    $tableG["footer"].="  <td class='td-right cellFt'>".number_format($TOTAL_ALL)."</td>";
	    $tableG["footer"].="</tr>";
	    if ($this->excel_flag==1) {
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL_ALL);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$this->excel->getActiveSheet()->getStyle("A".$currrow.":".PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	
			$currrow+= 1;
			$currcol = 0;
		}


	    $body.= "<div><h3>Omzet Produk (Dalam Rp.)</h3></div>";
	    $body.= "<table>".$tableP["header"].$tableP["body"].$tableP["footer"]."</table>";
	    $body.= "<div style='clear:both;height:20px;'></div>";
	    $body.= "<div><h3>Omzet Sparepart (Dalam Rp.)</h3></div>";
	    $body.= "<table>".$tableS["header"].$tableS["body"].$tableS["footer"]."</table>";
	    $body.= "<div style='clear:both;height:20px;'></div>";
	    $body.= "<div><h3>GABUNGAN</h3></div>";
	    $body.= "<table>".$tableG["header"].$tableG["body"].$tableG["footer"]."</table>";
	    $body.= "<div style='clear:both;height:80px;'></div>";


	    $footer = form_open('ReportOmzet/RekapOmzetNasional', array('target'=>'_blank'));
		$footer.= '<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;">';
		$footer.= '  <input type="text" name="Tahun" id="Tahun" value="'.$th.'" style="display:none;">';
		$footer.= '  <input type="text" name="Bulan" id="Bulan" value="'.$bl.'" style="display:none;">';
		$footer.= '  <div style="clear:both;">';
		$footer.= "    <div style='margin:10px;float:left;'>";
		$footer.= '      <input type = "submit" name="btnExportExcel" value="Export Excel"/>  ';
		$footer.= '    </div>';
		$footer.= "    <div style='margin:10px;float:left;display:none;'>";
		$footer.= '      <input type = "submit" name="btnEmailExcel" value="Email Excel"/>';
		$footer.= '    </div>';
		$footer.= '</div>';
		$footer.= form_close();

	    if ($this->excel_flag==1) {
			for ($i = 'A'; $i !=   $this->excel->getActiveSheet()->getHighestColumn(); $i++) {
			    $this->excel->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
			}
		}


		if ($this->excel_flag==1) {
	        //$filename='LaporanPreOrderPembelianBulanan['.date('Ymd').'].xls'; //save our workbook as this file name
	        $filename='OmzetNasional[".$th."][".$bl."]".xls'; //save our workbook as this file name
	        header('Content-Type: application/vnd.ms-excel'); //mime type
	        header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
	        header('Cache-Control: max-age=0'); //no cache
	        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');  
	        //force user to download the Excel file without writing it to server's HD
	        $objWriter->save('php://output');
	    } else {
			$data["th"] = $th;
			$data["bl"] = $bl;
			$data["content_html"] = $header."<br>".$body.$footer;
			$this->RenderView('ReportOmzetNasionalResult',$data);
		}
	}

	public function SummaryOmzetNasional()
	{
		$post = $this->PopulatePost();
		if(isset($post['th']) && isset($post["bl"]))
		{
			$summaries = $this->OmzetModel->Summary($post['th'],$post['bl']);
			if($summaries != null)
				echo json_encode(array("result"=>"sukses","data"=>$summaries,"error"=>""));
			else
				echo json_encode(array("result"=>"sukses","data"=>array(),'error'=>'Data Tidak Ada'));
		} else {
			echo json_encode(array("result"=>"gagal","data"=>$summaries,'error'=>'Parameter Tidak Lengkap'));
		}
	}
}