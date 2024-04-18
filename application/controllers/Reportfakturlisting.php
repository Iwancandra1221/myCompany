<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Reportfakturlisting extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('HelperModel');
        $this->load->helper('FormLibrary');
        $this->load->model('GzipDecodeModel');
        $this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
    }


    public function index() {
        $data = array();

        $api = 'APITES';
        set_time_limit(0);
        
        $branch_id = $_SESSION['logged_in']['branch_id'];
        if ($branch_id == "JKT") { $branch_id = "DMI"; }
        $data["branch_id"] = $branch_id;

        $mainUrl = $_SESSION["conn"]->AlamatWebService . $this->API_BKT;
        $data["mainurl"] = $mainUrl;

        // $dbgudang = json_decode(file_get_contents($mainUrl."/MasterGudang/GetListDbGudang?api=".$api."&branch_id=".urlencode($branch_id)));
        $dbgudang = file_get_contents($mainUrl."/MasterGudang/GetListDbGudang?api=".$api."&branch_id=".urlencode($branch_id));
        $dbgudang = $this->GzipDecodeModel->_decodeGzip($dbgudang);
        $data["dbgudang"] = $dbgudang;                  
                
        // $dealers = json_decode(file_get_contents($this->API_URL."/MsDealer/GetListAllDealer?api=".$api));
        $dealers = file_get_contents($this->API_URL."/MsDealer/GetListAllDealer?api=".$api);
        $dealers = $this->GzipDecodeModel->_decodeGzip($dealers);

		$merks = json_decode(file_get_contents($this->API_URL."/MsBarang/GetMerkList?api=".$api));

		$data["merks"] = $merks; 
		$data["dealers"] = $dealers; 

        // print_r (count($merks->data));
		// print_r ($dealers->data[0]->KD_PLG);
        // die;

        $data['title'] = 'Laporan Faktur Listing | '.WEBTITLE;
            
        $this->RenderView('Reportfakturlistingview',$data);

    }


    public function Proses() {
		// print_r ($_POST);
        // die;

        $page_title = 'Laporan Faktur Listing';
		$api = 'APITES';

		$db_gudang = $_POST["dbgudang"];
		$mainUrl = $_SESSION["conn"]->AlamatWebService . $this->API_BKT;
		// $dbgudang = json_decode(file_get_contents($mainUrl."/MasterGudang/GetDbGudang?api=".$api."&kd_gudang=".urlencode($db_gudang)));
		$dbgudang = file_get_contents($mainUrl."/MasterGudang/GetDbGudang?api=".$api."&kd_gudang=".urlencode($db_gudang));
        $dbgudang = $this->GzipDecodeModel->_decodeGzip($dbgudang);

		$server = $dbgudang->data[0]->Server;
		$db = $dbgudang->data[0]->DB;

        $tgl1 = date_format(date_create($_POST["dp1"]),'m-d-Y');
		$tgl2 = date_format(date_create($_POST["dp2"]),'m-d-Y');
		$tgl1a = $_POST["dp1"];
		$tgl2a = $_POST["dp2"];

		$merk = $_POST["merk"];

		$dealer = $_POST["dealer"];
		list($kd_plg, $nm_plg) = explode("---", $dealer);
		$kd_plg = trim($kd_plg);
		$nm_plg = trim($nm_plg);

        if ($_POST["pilihanlaporan"] == "A" ){ 
            // A. Laporan Faktur yang Sudah DiListing
            $laporan="A";     
            $judul = "Laporan Faktur yang Sudah DiListing";       
        }
        else{ 
            // B. Laporan Faktur yang Sudah Dipotong PDA
            $laporan="B";
            $judul = "Laporan Faktur yang Sudah Dipotong PDA"; 	
        }

        $periode = "Periode : ".$tgl1a." s/d ".$tgl2a;
        $printdate = "Print Date : " . date("d-m-Y h:i:sa");

		if ( $kd_plg == "ALL" ) {
			$dealer = "Dealer : ALL";
		}
		else {
			$dealer = "Dealer : ".$dealer;
		}
        

        // print_r ($mainUrl."/Reportfakturlisting/ProsesLaporan?api=".$api
        //                 ."&laporan=".urlencode($laporan)
        //                 ."&server=".urlencode($server)
        //                 ."&db=".urlencode($db)
        //                 ."&page_title=".urlencode($page_title)
        //                 ."&tgl1=".urlencode($tgl1)
        //                 ."&tgl2=".urlencode($tgl2)
        //                 ."&merk=".urlencode($merk)
        //                 ."&dealer=".urlencode($kd_plg));
        // die;

        set_time_limit(0);
        $datalaporan = json_decode(file_get_contents($mainUrl."/Reportfakturlisting/ProsesLaporan?api=".$api
															."&laporan=".urlencode($laporan)
															."&server=".urlencode($server)
															."&db=".urlencode($db)
															."&page_title=".urlencode($page_title)
															."&tgl1=".urlencode($tgl1)
															."&tgl2=".urlencode($tgl2)
															."&merk=".urlencode($merk)
															."&dealer=".urlencode($kd_plg)
		));
						
		// print_r ($datalaporan);
        // die;

        $this->Preview_A ( $page_title, $datalaporan, $periode, $printdate, $judul, $merk, $dealer );
        
    }


    // A. Laporan Faktur yang Sudah DiListing
	// B. Laporan Faktur yang Sudah Dipotong PDA
    public function Preview_A ( $page_title, $datalaporan, $periode, $printdate, $judul, $merk, $dealer ) {
        ini_set('max_execution_time', '1500');
		ini_set("pcre.backtrack_limit", "10000000");
		set_time_limit(0);

		$mpdf = new \Mpdf\Mpdf(array(
			'mode' => '',
			'format' => 'Legal',
			'default_font_size' => 8,
			'default_font' => 'arial',
			'margin_left' => 10,
			'margin_right' => 10,
			'margin_top' => 30,
			'margin_bottom' => 10,
			'margin_header' => 10,
			'margin_footer' => 5,
			'orientation' => 'P'
		));

        $style_col_ganjil ="float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#ffffcc;";
		$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
		$style_summary= "float:left;height:30px;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#cccccc;";
	
		$kanan= "text-align:right;padding-right:10px;";
		$kiri = "text-align:left; padding-left:10px;";
	
        $header_html = "";
        $content_html = "";

        $header_html.= "<table id='div_header' style='margin-bottom:20px;padding-left:1px;width:100%'>";
		$header_html.= "	<tr><td width='50%'><b>" .$judul. "</b></td>
							<td align='right'>" .$printdate. "</td></tr> 
		";
		$header_html.= "	<tr><td ><b>" .$periode. "</b></td>
							<td align='right'></td></tr>
		";
		$header_html.= "	<tr><td ><b>Merk : ".$merk. "</b></td>
							<td align='right'></td></tr> 
		";
        $header_html.= "	<tr><td ><b>".$dealer. "</b></td>
							<td align='right'></td></tr> 
		";									
		$header_html.= "</table>";	//close div_header


		$content_html.= "<style> th, td { font-size:9px; } </style>";			
		$content_html.= "<div class='div_body' style='overflow-x:padding-left:1px;'>";
		$content_html.= "	<table style='width:100%'><tr>";
		$content_html.= "		<th style='width:8%; border-bottom:thin solid #333; border-top:thin solid #333;'>Tgl Faktur</th>";
		$content_html.= "		<th style='width:14%; border-bottom:thin solid #333; border-top:thin solid #333;'>No Bukti</th>";
		$content_html.= "		<th style='width:18%; border-bottom:thin solid #333; border-top:thin solid #333;'>PELANGGAN</th>";
		$content_html.= "		<th style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>Barang</th>";
		$content_html.= "		<th style='width:5%;  border-bottom:thin solid #333; border-top:thin solid #333;'>Qty</th>";
		$content_html.= "		<th style='width:8%; border-bottom:thin solid #333; border-top:thin solid #333;'>Tgl Print</th>";
		$content_html.= "		<th style='width:8%; border-bottom:thin solid #333; border-top:thin solid #333;'>Tgl Listing</th>";
		$content_html.= "		<th style='width:8%; border-bottom:thin solid #333; border-top:thin solid #333;'>Tgl Ketik</th>";
		$content_html.= "		<th style='width:18%; border-bottom:thin solid #333; border-top:thin solid #333;'>User Name</th>";
		$content_html.= "	</tr>";

        // TmpKelompokGdg.No_Bukti, TmpKelompokGdg.Tgl_Faktur, TmpKelompokGdg.entry_time, 
        // TmpKelompokGdg.kd_brg, TmpKelompokGdg.qty, TmpKelompokGdg.Tgl_Print, 
        // max (WaktuTransfer.Entry_Time), TmpKelompokGdg.[User_Name],  TblInheader.merk, TblMsDealer.Nm_Plg
                        
        $M = "!@#$%";

		$jml=count($datalaporan->data);
		for($i=0;$i<$jml;$i++) {	           	

            if ( $M != trim($datalaporan->data[$i]->merk) ) {
                if ( $M != "!@#$%" ) {
                    $content_html.= "		<tr><td colspan='9'></td></tr>";
                }
                $content_html.= "		<tr><td colspan='9'>Merk :".$datalaporan->data[$i]->merk."</td></tr>";                
            }

            $content_html.= "		<tr><td>".date("d-M-Y", strtotime($datalaporan->data[$i]->Tgl_Faktur))."</td>";           
            $content_html.= "			<td>".$datalaporan->data[$i]->No_Bukti."</td>";
            $content_html.= "			<td>".$datalaporan->data[$i]->Nm_Plg."</td>";
            $content_html.= "			<td>".$datalaporan->data[$i]->kd_brg."</td>";
            $content_html.= "			<td align='right'>".number_format($datalaporan->data[$i]->qty)."</td>";
            $content_html.= "			<td>".date("d-M-Y", strtotime($datalaporan->data[$i]->Tgl_Print))."</td>";
            $content_html.= "			<td>".date("d-M-Y", strtotime($datalaporan->data[$i]->WaktuTransfer_Entry_Time))."</td>";
            $content_html.= "			<td>".date("d-M-Y", strtotime($datalaporan->data[$i]->entry_time))."</td>";
            $content_html.= "			<td>".$datalaporan->data[$i]->Nm_Plg."</td>";	
            
            $content_html.= "	</tr>";

            $M = trim($datalaporan->data[$i]->merk);
		}

		$content_html.= "</table>";

		// $content_html.= "		</div>";
		// $content_html.= "	</div>";
		// $content_html.= "</div>";
		// $content_html.= "</body></html>";


		// echo $content_html;
		// die();

        set_time_limit(0);
		$mpdf->SetHTMLHeader($header_html,'','1');
		// die("!");
		// $mpdf->WriteHTML($content_html);
		$mpdf->WriteHTML(utf8_encode($content_html));
		// die("!");
		$mpdf->Output();	
    }



}

