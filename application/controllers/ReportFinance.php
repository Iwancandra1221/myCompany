<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class ReportFinance extends MY_Controller 
	{
        public function __construct()
		{
			parent::__construct();
			$this->load->model('HelperModel');
			$this->load->model('ReportFinanceModel');
			$this->load->helper('FormLibrary');
			$this->load->library('excel');
			
			$this->api = 'APITES';
			$this->laporan = array (1=>'LAPORAN SISA FAKTUR PER JATUH TEMPO (SUMMARY)',
									2=>'LAPORAN SISA FAKTUR PER JATUH TEMPO (PER WILAYAH)',
									3=>'LAPORAN SISA FAKTUR PER JATUH TEMPO (PER TOKO)',
									4=>'LAPORAN SISA FAKTUR PER JATUH TEMPO (PER FAKTUR)',
									5=>'REPORT AGING WILAYAH',
									6=>'REPORT AGING DEALER',
									7=>'REPORT AGING FAKTUR');
			$this->excel_flag = 0;
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
			$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;

			ini_set("max_execution_time", 300);
			ini_set('memory_limit', '256m');	
		}
		private function _postRequest($url,$data,$jsonDecode=true){

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
			curl_setopt($ch, CURLOPT_ENCODING, '');
			//curl_setopt($ch, CURLOPT_POSTFIELDS,"postvar1=value1&postvar2=value2&postvar3=value3");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$server_output = curl_exec($ch);
			$result = json_decode($server_output,$jsonDecode);
			if($result==null){
				$GLOBALS['bugsnag']->leaveBreadcrumb(
				    $server_output,
				    \Bugsnag\Breadcrumbs\Breadcrumb::ERROR_TYPE,
				    [
				    	'url' => $url,
				    	'payload' => $data,
					]
				);
				$GLOBALS['bugsnag']->notifyError('ErrorType', 'result kosong - CEK TAB BREADCUMS');
				
			}

			return $result;
		}
        public function ReportFinanceBBT()
		{
            $data = array();
			$api = 'APITES';
			            
            $mainUrl = $_SESSION["conn"]->AlamatWebService . $this->API_BKT;

            //Nanti Di COMMENT
            //$mainUrl = "http://localhost/" . $this->API_BKT; 

			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "REPORT FINANCE BBT";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU REPORT FINANCE BBT";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

            $data["mainurl"] = $mainUrl;

            $svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;
            
		    $url = $mainUrl."/MasterWilayah/GetListAllWilayah_RptBBT?api=".$api."&svr=".$svr."&db=".$db;
		    $dbwilayah = $this->_postRequest($url,array());
            $data["dbwilayah"] = $dbwilayah;
			
			$url = $mainUrl."/MasterAccountBank/GetListAllAccountBank?api=".$api."&svr=".$svr."&db=".$db;
			$dbaccountbank = $this->_postRequest($url,array());
            $data["dbaccountbank"] = $dbaccountbank;

			// $url = $this->API_URL."/Ms_TypeTrans/AmbilListTypeTransBBT?api=".$api;
			$tipetrans = $this->_postRequest($this->API_URL."/Ms_TypeTrans/AmbilListTypeTransBBT?api=".$api,array());
			$data["tipetrans"] = $tipetrans;

			$data['title'] = 'Laporan BBT | '.WEBTITLE;
			
			$this->RenderView('ReportFinanceBBTView',$data);
		}

        public function ReportFinanceBBT_Proses()
        {
			$page_title = 'ReportFinanceBBT';
			$api = 'APITES';

			// format wilayah : AC | MDN | ACEH
			$wilayah = $_POST["wilayah"];
			list($wilayah1, $wilayah2, $wilayah3) = explode("|", $wilayah);
			$wilayah1 = trim($wilayah1);
			$wilayah2 = trim($wilayah2);
			$wilayah3 = trim($wilayah3);

			$tgl1 = date_format(date_create($_POST["dp1"]),'m-d-Y');
			$tgl2 = date_format(date_create($_POST["dp2"]),'m-d-Y');
			$tgl1a = $_POST["dp1"];
			$tgl2a = $_POST["dp2"];

			$tipetrans = $_POST["tipetrans"];
			$tipe_terima = $_POST["tipe_terima"];
			$status = $_POST["status"];

			// format rekening : BCA BATAM   |   7195430888   |   PT BHAKTI IDOLA TAMA
			$radrekening = $_POST["radrekening"];
			if ($radrekening == "norekening"){

				$norekening = $_POST["rekening"];		
				list($norekening1, $norekening2, $norekening3) = explode("|", $norekening);
				$norekening1 = trim($norekening1);
				$norekening2 = trim($norekening2);
				$norekening3 = trim($norekening3);	

			}
			else {
				$norekening = "";
				$norekening1 = "";
				$norekening2 = "";
				$norekening3 = "";	
			}

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
            //Nanti Di COMMENT
            //$mainUrl = "http://localhost/" . $this->API_BKT; 

            $svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;

			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "REPORT FINANCE BBT";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES REPORT FINANCE BBT ".$wilayah2." TIPE TRANS ".$tipetrans." PERIODE ".date("d-M-Y", strtotime($tgl1))." S/D ".date("d-M-Y", strtotime($tgl2));
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

			$url = $mainUrl."/ReportFinance/ReportFinanceBBT_ProsesLaporan?api=".$api
															."&server=".urlencode($svr)
															."&db=".urlencode($db)
															."&page_title=".urlencode($page_title)
															."&wilayah1=".urlencode($wilayah1)
															."&wilayah2=".urlencode($wilayah2)
															."&tgl1=".urlencode($tgl1)
															."&tgl2=".urlencode($tgl2)
															."&tipetrans=".urlencode($tipetrans)
															."&tipe_terima=".urlencode($tipe_terima)
															."&status=".urlencode($status)
															."&radrekening=".urlencode($radrekening)
															."&norekening=".urlencode($norekening2)
															."&cetak_detail=".urlencode($cetak_detail);
			// die($url);

            // $response = file_get_contents($url);
            // $decodedData = @gzdecode($response);
			// $datalaporan = json_decode($decodedData);

			$datalaporan = $this->_postRequest($url,array(),false);

			// print_r($datalaporan);
			// die;	

			$periode = "Periode " .$tgl1a. " S/D " .$tgl2a	;
			$printdate = "Print Date : " . date("d-m-Y h:i:sa");

			if ($cetak_detail == "N") {
				if ($radrekening == "gabungan"){

					// Laporan Buku Harian BBT
					$judul = "Laporan Buku Harian ";
					if ($tipetrans=="BMK") {
						$judul.= "BMK";
					} else if ($tipetrans=="ALL" || $tipetrans=="") {
						$judul.= "BBT & BMK";
					} else {
						$judul.= "BBT";
					}

					$rek = "Rekening : GABUNGAN";

					if ($proses=="PREVIEW") {
						$this->ReportFinanceBBT_Preview_A ( $page_title, $datalaporan, $periode, $judul, $wilayah, $tipetrans, $tipe_terima, $status, $rek, $printdate, $params );						
					}
					else {
						$this->ReportFinanceBBT_Excel_A ( $page_title, $datalaporan, $periode, $judul, $wilayah, $tipetrans, $tipe_terima, $status, $rek, $printdate, $params );
					}
				}
				else {
					
					// Laporan Buku Harian BBT GrupBy NoRek
					$judul = "Laporan Buku Harian ";
					if ($tipetrans=="BMK") {
						$judul.= "BMK";
					} else if ($tipetrans=="ALL" || $tipetrans=="") {
						$judul.= "BBT & BMK";
					} else {
						$judul.= "BBT";
					}

					if ($radrekening == "gruprekening"){
						$rek = "Rekening : GRUP REKENING";
					}
					else {
						$rek = "Rekening : " .$norekening;
					} 

					if ($proses=="PREVIEW") {
						$this->ReportFinanceBBT_Preview_B ( $page_title, $datalaporan, $periode, $judul, $wilayah, $tipetrans, $tipe_terima, $status, $rek, $printdate, $params );						
					}
					else {
						$this->ReportFinanceBBT_Excel_B ( $page_title, $datalaporan, $periode, $judul, $wilayah, $tipetrans, $tipe_terima, $status, $rek, $printdate, $params );
					}
				}
			}
			else {
				if ($radrekening == "gabungan"){
					// Laporan Buku Harian BBT DT
					$judul = "Laporan Buku Harian ";
					if ($tipetrans=="BMK") {
						$judul.= "BMK";
					} else if ($tipetrans=="ALL" || $tipetrans=="") {
						$judul.= "BBT & BMK";
					} else {
						$judul.= "BBT";
					}
					$judul.= " Detail";
					$rek = "Rekening : GABUNGAN";

					if ($proses=="PREVIEW") {
						$this->ReportFinanceBBT_Preview_C ( $page_title, $datalaporan, $periode, $judul, $wilayah, $tipetrans, $tipe_terima, $status, $rek, $printdate, $params );						
					}
					else {
						$this->ReportFinanceBBT_Excel_C ( $page_title, $datalaporan, $periode, $judul, $wilayah, $tipetrans, $tipe_terima, $status, $rek, $printdate, $params );
					}
				}
				else {
					// Laporan Buku Harian BBT DT GrupBy NoRek
					$judul = "Laporan Buku Harian ";
					if ($tipetrans=="BMK") {
						$judul.= "BMK";
					} else if ($tipetrans=="ALL" || $tipetrans=="") {
						$judul.= "BBT & BMK";
					} else {
						$judul.= "BBT";
					}
					$judul.= " Detail";						
					if ($radrekening == "gruprekening"){
						$rek = "Rekening : GRUP REKENING";
					}
					else {
						$rek = "Rekening : " .$norekening;
					}

					if ($proses=="PREVIEW") {						
						$this->ReportFinanceBBT_Preview_D ( $page_title, $datalaporan, $periode, $judul, $wilayah, $tipetrans, $tipe_terima, $status, $rek, $printdate, $params );						
					}
					else {
						$this->ReportFinanceBBT_Excel_D ( $page_title, $datalaporan, $periode, $judul, $wilayah, $tipetrans, $tipe_terima, $status, $rek, $printdate, $params );
					}
				}
			}
        }


		// Laporan Buku Harian BBT
		public function ReportFinanceBBT_Preview_A ( $page_title, $datalaporan, $periode, $judul, $wilayah, $tipetrans, $tipe_terima, $status, $rek, $printdate, $params ) {
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

			$style_col_ganjil ="float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_summary= "float:left;height:30px;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
	
			$kanan= "text-align:right;padding-right:10px;";
			$kiri = "text-align:left; padding-left:10px;";
			
			$header_html="";
			$content_html= "";
			$nobukti = "!@#$%^&*";
			$nofaktur= "";
			$total = 0;

			$header_html.= "<table id='div_header' style='margin-bottom:20px;padding-left:1px;width:100%'>";

			$header_html.= "	<tr><td width='50%'><b>" .$judul. "</b></td>
								<td align='right'><b>" .$printdate. "</b></td></tr> 
			";

			$header_html.= "	<tr><td ><b>" .$periode. "</b></td>
								<td align='right'><b>Tipe Terima : " .$tipe_terima. "</b></td></tr>
			";

			$header_html.= "	<tr><td ><b>Wilayah : ".$wilayah. "</b></td>
								<td align='right'><b>Status : " .$status. "</b></td></tr> 
			";
								
			if ($tipetrans == "BBT") {
				$header_html.= "	<tr><td ><b>Tipe Trans : BBTP, BBTL, BBTS</b></td>
									<td align='right'><b>" .$rek. "</b></td></tr> 
				";
			}
			else {
				$header_html.= "	<tr><td ><b>Tipe Trans : ".$tipetrans. "</b></td>
									<td align='right'><b>" .$rek. "</b></td></tr> 
				";
			}
						
			$header_html.= "</table>";	//close div_header


			// TblBuktiBank.No_bukti as NO_BUKTI, TblBuktiBank.Tgl_trans as TGL_TRANS,  
			// TblMsDealer.kd_plg as KD_PLG, TblMsDealer.nm_plg as NM_PLG,
			// TblBuktiBank.Total as TOTAL, TblBuktiBank.Bank as BANK, TblBuktiBank.No_giro as NO_GIRO, 
			// TblBuktiBank.Ket as KET, TblBuktiBank.Status as STATUS,
			// isnull(tblpenerimaanpembfaktur.no_bukti,'') as NO_BUKTI2,
			// isnull(TblPenerimaanPembFaktur.No_Faktur,'') as NO_FAKTUR, TblBuktiBank.No_Rekening as NO_REKENING, 
			// TblAccountBank.Bank as NMBANK, TblAccountBank.Nm_Pemilik AS NM_PEMILIK,
			// TblBuktiBank.Tgl_jatuhTempo as TGL_JATUHTEMPO, TblBuktiBankDetail.KodeTemplateTrx as KODETEMPLATE_TRX, 
			// MsTemplateTrxHD.NamaTemplateTrx as NAMATEMPLATETRX, TblBuktiBank.Type_terima AS TYPE_TERIMA,
			// TblBuktiBankDetail.Total as TOTAL, TblBuktiBankDetail.Catatan as CATATAN
			// , TblBuktiBank.Type_trans as TYPE_TRANS

			$content_html.= "<style> th, td { font-size:9px; } </style>";			
			$content_html.= "<div class='div_body' style='overflow-x:padding-left:1px;'>";
			$content_html.= "	<table style='width:100%'><tr>";
			$content_html.= "		<th style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO BUKTI</th>";
			$content_html.= "		<th style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>TANGGAL</th>";
			$content_html.= "		<th style='width:20%; border-bottom:thin solid #333; border-top:thin solid #333;'>PELANGGAN</th>";
			$content_html.= "		<th style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;' align='right'>TOTAL</th>";
			// $content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;' align='center'>BANK</td>";
			// $content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO GIRO</td>";
			$content_html.= "		<th style='width:14%; border-bottom:thin solid #333; border-top:thin solid #333;'>KETERANGAN</th>";
			$content_html.= "		<th style='width:7%; border-bottom:thin solid #333; border-top:thin solid #333;'>STATUS</th>";
			$content_html.= "		<th style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>TYPE TERIMA</th>";
			$content_html.= "		<th style='width:8%; border-bottom:thin solid #333; border-top:thin solid #333;'>TYPE TRANS</th>";
			$content_html.= "	</tr>";

			// die(json_encode($datalaporan));
			$jml_bbt = 0;

			$jum = 0;
			if($datalaporan!=null && isset($datalaporan->data)){
				$jum=count($datalaporan->data);
				for($i=0;$i<$jum;$i++) {
					if ($nobukti != $datalaporan->data[$i]->NO_BUKTI) {
						$jml_bbt++;
						$total += $datalaporan->data[$i]->TOTAL;

						if ($nobukti != "!@#$%^&*" && trim($nofaktur) != "") {						
							$content_html.= "		<tr><td colspan='1'></td>
													<td colspan='7'>"."*** No Faktur = ".$nofaktur."</td></tr> ";	

							$content_html.= "		<tr><td colspan='8' style='border-bottom:thin solid #333;'></td></tr>";
						}

						$content_html.= "			<tr><td>".$datalaporan->data[$i]->NO_BUKTI."</td>";
						$content_html.= "			<td>".date("d-M-Y", strtotime($datalaporan->data[$i]->TGL_TRANS))."</td>";
						$content_html.= "			<td>".$datalaporan->data[$i]->NM_PLG."</td>";	
						$content_html.= "			<td align='right'>".number_format($datalaporan->data[$i]->TOTAL)."</td>";
						// $content_html.= "			<td align='center'>".$datalaporan->data[$i]->BANK."</td>";
						// $content_html.= "			<td>".$datalaporan->data[$i]->NO_GIRO."</td>";
						$content_html.= "			<td>".$datalaporan->data[$i]->KET." ".$datalaporan->data[$i]->NO_GIRO."</td>";		
						$content_html.= "			<td align='center'>".$datalaporan->data[$i]->STATUS."</td>";
						$content_html.= "			<td align='center'>".$datalaporan->data[$i]->TYPE_TERIMA."</td>";
						$content_html.= "			<td align='center'>".$datalaporan->data[$i]->TYPE_TRANS."</td>";
						
						$content_html.= "	</tr>";

						$nofaktur = "";
					}
					
					if (trim($datalaporan->data[$i]->NO_FAKTUR)!="") {
						$nofaktur .= "&nbsp;&nbsp;".$datalaporan->data[$i]->NO_FAKTUR;
					}
					$nobukti = $datalaporan->data[$i]->NO_BUKTI;
				}
			}
			

			if ($nobukti != "!@#$%^&*" && trim($nofaktur) != "") {						
				$content_html.= "		<tr><td colspan='1'></td>
										<td colspan='7'>"."*** No Faktur = ".$nofaktur."</td></tr>";	
				$content_html.= "		<tr><td colspan='8' style='border-bottom:thin solid #333;'></td></tr>";
			}

			$content_html.= "			<tr><td colspan='2' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".$jml_bbt." Bukti Bank</b></td>
										<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>Total</b></td>
										<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($total)."</b></td>
										<td colspan='4' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td></tr>";


			$content_html.= "</table>";

			// echo $content_html;
			// die();

			if ($jum==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}

			$mpdf->SetHTMLHeader($header_html,'','1');
			$mpdf->WriteHTML(utf8_encode($content_html));
			$mpdf->Output();	
		}

		// Laporan Buku Harian BBT GrupBy NoRek
		public function ReportFinanceBBT_Preview_B ( $page_title, $datalaporan, $periode, $judul, $wilayah, $tipetrans, $tipe_terima, $status, $rek, $printdate, $params ) {

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

			$style_col_ganjil ="float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_summary= "float:left;height:30px;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
	
			$kanan= "text-align:right;padding-right:10px;";
			$kiri = "text-align:left; padding-left:10px;";
			
			$header_html="";
			$content_html= "";
			$nobukti = "!@#$%^&*";
			$norekening = "!@#$%^&*";
			$nofaktur= "";
			$total = 0;
			$totalrek = 0;


			$header_html.= "<table id='div_header' style='margin-bottom:20px;padding-left:1px;width:100%'>";

			$header_html.= "	<tr><td width='50%'><b>" .$judul. "</b></td>
								<td align='right'><b>" .$printdate. "</b></td></tr> 
			";

			$header_html.= "	<tr><td ><b>" .$periode. "</b></td>
								<td align='right'><b>Tipe Terima : " .$tipe_terima. "</b></td></tr>
			";

			$header_html.= "	<tr><td ><b>Wilayah : ".$wilayah. "</b></td>
								<td align='right'><b>Status : " .$status. "</b></td></tr> 
			";
								
			if ($tipetrans == "BBT") {
				$header_html.= "	<tr><td ><b>Tipe Trans : BBTP, BBTL, BBTS</b></td>
									<td align='right'><b>" .$rek. "</b></td></tr> 
				";
			}
			else {
				$header_html.= "	<tr><td ><b>Tipe Trans : ".$tipetrans. "</b></td>
									<td align='right'><b>" .$rek. "</b></td></tr> 
				";
			}
			
			$header_html.= "</table>";	//close div_header

			// TblBuktiBank.No_bukti as NO_BUKTI, TblBuktiBank.Tgl_trans as TGL_TRANS,  
			// TblMsDealer.kd_plg as KD_PLG, TblMsDealer.nm_plg as NM_PLG,
			// TblBuktiBank.Total as TOTAL, TblBuktiBank.Bank as BANK, TblBuktiBank.No_giro as NO_GIRO, 
			// TblBuktiBank.Ket as KET, TblBuktiBank.Status as STATUS,
			// isnull(tblpenerimaanpembfaktur.no_bukti,'') as NO_BUKTI2,
			// isnull(TblPenerimaanPembFaktur.No_Faktur,'') as NO_FAKTUR, TblBuktiBank.No_Rekening as NO_REKENING, 
			// TblAccountBank.Bank as NMBANK, TblAccountBank.Nm_Pemilik AS NM_PEMILIK,
			// TblBuktiBank.Tgl_jatuhTempo as TGL_JATUHTEMPO, TblBuktiBankDetail.KodeTemplateTrx as KODETEMPLATE_TRX, 
			// MsTemplateTrxHD.NamaTemplateTrx as NAMATEMPLATETRX, TblBuktiBank.Type_terima AS TYPE_TERIMA,
			// TblBuktiBankDetail.Total as TOTAL, TblBuktiBankDetail.Catatan as CATATAN
			// , TblBuktiBank.Type_trans as TYPE_TRANS
			$content_html.= "<style> th, td { font-size:9px; } </style>";			

			// $content_html.= "<div class='div_body' style='overflow-x:padding-left:1px;'>";
			$content_html.= "	<table style='width:100%'>";
			
			$norekeningonly = "";
			$jml_bbt = 0;
			$jml_bbt_all=0;
			$jum = 0;
			if($datalaporan!=null && isset($datalaporan->data)){
				$jum=count($datalaporan->data);
				for($i=0;$i<$jum;$i++) {
				
					if ($norekening != $datalaporan->data[$i]->NO_REKENING.' - '.$datalaporan->data[$i]->NMBANK.' - '.$datalaporan->data[$i]->NM_PEMILIK) {
						
						// No Faktur
						if ($nobukti != "!@#$%^&*" && $nofaktur != "") {						
							$content_html.= "		<tr><td colspan='1'></td>
													<td colspan='7' style='font-size:9;'>"."*** No Faktur = ".$nofaktur."</td></tr>";
													
							$content_html.= "		<tr><td colspan='8' style='border-bottom:thin solid #333;'></td></tr>";
						}

						// Total Rekening
						if ($norekening != "!@#$%^&*") {						
							$content_html.= "	<tr><td colspan='3' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333; font-size:10px;'><b>Total Rekening = ".$norekeningonly." </b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333; font-size:10px;'><b>".number_format($totalrek)."</b></td>
												<td colspan='4' style='border-bottom:thin solid #333; border-top:thin solid #333; font-size:10px'>&nbsp;&nbsp;&nbsp;<b>".$jml_bbt." Bukti Bank</b></td></tr>
												<tr><td>&nbsp;</td></tr>";	

							$totalrek = 0;
						}
						$jml_bbt = 1;
						$jml_bbt_all++;
						$norekeningonly = "";
						$total += $datalaporan->data[$i]->TOTAL;
						$totalrek = $datalaporan->data[$i]->TOTAL;

						// Sub Rekening
						$content_html.= "		<tr><td colspan='8'><b>"."No Rekening = ".$datalaporan->data[$i]->NO_REKENING.' - '.$datalaporan->data[$i]->NMBANK.' - '.$datalaporan->data[$i]->NM_PEMILIK."</b></td></tr>";
						
						// Header
						$content_html.= "	<tr>";
						$content_html.= "		<th style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO BUKTI</th>";
						$content_html.= "		<th style='width:8%; border-bottom:thin solid #333; border-top:thin solid #333;'>TANGGAL</th>";
						$content_html.= "		<th style='width:20%; border-bottom:thin solid #333; border-top:thin solid #333;'>PELANGGAN</th>";
						$content_html.= "		<th style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;' align='right'>TOTAL</th>";
						// $content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;' align='center'>BANK</td>";
						// $content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO GIRO</td>";
						$content_html.= "		<th style='width:20%; border-bottom:thin solid #333; border-top:thin solid #333;'>KETERANGAN</th>";
						$content_html.= "		<th style='width:7%; border-bottom:thin solid #333; border-top:thin solid #333;'>STATUS</th>";
						$content_html.= "		<th style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>TYPE TERIMA</th>";
						$content_html.= "		<th style='width:8%; border-bottom:thin solid #333; border-top:thin solid #333;'>TYPE TRANS</th>";
						$content_html.= "	</tr>";

						
						// nobukti						
						$content_html.= "			<tr><td>".$datalaporan->data[$i]->NO_BUKTI."</td>";
						$content_html.= "			<td>".date("d-M-Y", strtotime($datalaporan->data[$i]->TGL_TRANS))."</td>";
						$content_html.= "			<td>".$datalaporan->data[$i]->NM_PLG."</td>";	
						$content_html.= "			<td align='right'>".number_format($datalaporan->data[$i]->TOTAL)."</td>";
						// $content_html.= "			<td align='center'>".$datalaporan->data[$i]->BANK."</td>";
						// $content_html.= "			<td>".$datalaporan->data[$i]->NO_GIRO."</td>";
						$content_html.= "			<td>".$datalaporan->data[$i]->KET." ".$datalaporan->data[$i]->NO_GIRO."</td>";		
						$content_html.= "			<td align='center'>".$datalaporan->data[$i]->STATUS."</td>";
						$content_html.= "			<td align='center'>".$datalaporan->data[$i]->TYPE_TERIMA."</td>";
						$content_html.= "			<td align='center'>".$datalaporan->data[$i]->TYPE_TRANS."</td>";
						$content_html.= "	</tr>";
		
						$nofaktur = "";									
						
					}

					elseif ($nobukti != $datalaporan->data[$i]->NO_BUKTI) {
						$jml_bbt++;
						$jml_bbt_all++;
						$total += $datalaporan->data[$i]->TOTAL;
						$totalrek += $datalaporan->data[$i]->TOTAL;

						if ($nobukti != "!@#$%^&*" && $nofaktur != "") {						
							// $content_html.= "		<tr><td></td>
							// 						<td colspan='7'>>"."*** No Faktur = ".str_replace(',', ', ', $nofaktur)."</td></tr>";	

							$nofakturArray = explode(', ', $nofaktur);
							$nofakturArray = array_unique($nofakturArray);

							$nofakturFormatted = '';
							$jum_faktur = 0;
							$total_max 	= 10;
							$hasildata_awal = 0;
							foreach ($nofakturArray as $key => $value) {
							    $nofakturFormatted .= $value;
							    if($jum_faktur==$total_max){
							    	if($jum_faktur==10){
							    		$nofakturFormatted = rtrim($nofakturFormatted, ', ');
										$content_html.= "<tr><td></td><td colspan='7'>*** No Faktur = ".$nofakturFormatted."</td></tr>";	
										$nofakturFormatted='';
										$hasildata_awal++;
									}else{
										$nofakturFormatted = rtrim($nofakturFormatted, ', ');
										$content_html.= "<tr><td></td><td colspan='7'>".$nofakturFormatted."</td></tr>";	
										$nofakturFormatted='';
										$hasildata_awal++;
									}
									$total_max=$total_max+10;
							    }else{
							    	$nofakturFormatted .=', ';
							    }
							    $jum_faktur++;
							}				

							if($jum_faktur<$total_max){
								if($hasildata_awal==0){
									$nofakturFormatted = rtrim($nofakturFormatted, ', ');
									$content_html.= "<tr><td></td><td colspan='7'>*** No Faktur = ".$nofakturFormatted."</td></tr>";
									$nofakturFormatted='';	
								}else{
									$nofakturFormatted = rtrim($nofakturFormatted, ', ');
									$content_html.= "<tr><td></td><td colspan='7'>".$nofakturFormatted."</td></tr>";
									$nofakturFormatted='';
									$hasildata_awal = 0;
								}

							}

							$content_html.= "		<tr><td colspan='8' style='border-bottom:thin solid #333;'></td></tr>";
						}

						$content_html.= "			<tr><td>".$datalaporan->data[$i]->NO_BUKTI."</td>";
						$content_html.= "			<td>".date("d-M-Y", strtotime($datalaporan->data[$i]->TGL_TRANS))."</td>";
						$content_html.= "			<td>".$datalaporan->data[$i]->NM_PLG."</td>";	
						$content_html.= "			<td align='right'>".number_format($datalaporan->data[$i]->TOTAL)."</td>";
						// $content_html.= "			<td align='center'>".$datalaporan->data[$i]->BANK."</td>";
						// $content_html.= "			<td>".$datalaporan->data[$i]->NO_GIRO."</td>";
						$content_html.= "			<td>".$datalaporan->data[$i]->KET." ".$datalaporan->data[$i]->NO_GIRO."</td>";		
						$content_html.= "			<td align='center'>".$datalaporan->data[$i]->STATUS."</td>";
						$content_html.= "			<td align='center'>".$datalaporan->data[$i]->TYPE_TERIMA."</td>";
						$content_html.= "			<td align='center'>".$datalaporan->data[$i]->TYPE_TRANS."</td>";
						$content_html.= "	</tr>";

						$nofaktur = "";
						$nofakturFormatted = '';
					}
					
					if (trim($datalaporan->data[$i]->NO_FAKTUR)!="") {
						$nofaktur .= " ".$datalaporan->data[$i]->NO_FAKTUR;
					}

					$norekening = $datalaporan->data[$i]->NO_REKENING.' - '.$datalaporan->data[$i]->NMBANK.' - '.$datalaporan->data[$i]->NM_PEMILIK;
					$norekeningonly = $datalaporan->data[$i]->NO_REKENING;
					$nobukti = $datalaporan->data[$i]->NO_BUKTI;
				}
			}
			

			if ($nobukti != "!@#$%^&*" && $nofaktur != "") {						
				// $content_html.= "	<tr><td></td>
				// 					<td colspan='7'>"."*** No Faktur = ".str_replace(',', ', ', $nofaktur)."</td></tr>";	

				$nofakturArray = explode(', ', $nofaktur);
				$nofakturArray = array_unique($nofakturArray);
				$nofakturFormatted = '';
				$jum_faktur = 0;
				$total_max 	= 10;
				$hasildata_awal = 0;
				foreach ($nofakturArray as $key => $value) {
					$nofakturFormatted .= $value;
					if($jum_faktur==$total_max){
						if($jum_faktur==10){
							$nofakturFormatted = rtrim($nofakturFormatted, ', ');
							$content_html.= "<tr><td></td><td colspan='7'>*** No Faktur = ".$nofakturFormatted."</td></tr>";	
							$nofakturFormatted='';
							$hasildata_awal++;
						}else{
							$nofakturFormatted = rtrim($nofakturFormatted, ', ');
							$content_html.= "<tr><td></td><td colspan='7'>".$nofakturFormatted."</td></tr>";	
							$nofakturFormatted='';
							$hasildata_awal++;
						}
						$total_max=$total_max+10;
					}else{
						$nofakturFormatted .=', ';
					}
					$jum_faktur++;
				}				

				if($jum_faktur<$total_max){
					if($hasildata_awal==0){
						$nofakturFormatted = rtrim($nofakturFormatted, ', ');
						$content_html.= "<tr><td></td><td colspan='7'>*** No Faktur = ".$nofakturFormatted."</td></tr>";
						$nofakturFormatted='';	
					}else{
						$nofakturFormatted = rtrim($nofakturFormatted, ', ');
						$content_html.= "<tr><td></td><td colspan='7'>".$nofakturFormatted."</td></tr>";
						$nofakturFormatted='';
						$hasildata_awal = 0;
					}

				}	

				$content_html.= "<tr><td colspan='8' style='border-bottom:thin solid #333;'></td></tr>";
			}
			
			// Total Rekening					
			$content_html.= "		<tr>
									<td colspan='3' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333; font-size:10px;'><b>Total Rekening = ".$norekeningonly." </b></td>
									<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333; font-size:10px;'><b>".number_format($totalrek)."</b></td>
									<td colspan='4' style='border-bottom:thin solid #333; border-top:thin solid #333; font-size:10px;'>&nbsp;&nbsp;&nbsp;<b>".$jml_bbt." Bukti Bank</b></td></tr>
									<tr><td>&nbsp;</td></tr>";	
			
			// Total
			$content_html.= "		<tr><td colspan='3' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333; font-size:10px;'><b>Total</b></td>
									<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333; font-size:10px;'><b>".number_format($total)."</b></td>
									<td colspan='4' style='border-bottom:thin solid #333; border-top:thin solid #333; font-size:10px;'>&nbsp;&nbsp;&nbsp;<b>".$jml_bbt_all." Bukti Bank</b></td></tr>";
		
			$content_html.= "</table>";

			if ($jum==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}

			// echo $content_html;die();
			
			$mpdf->SetHTMLHeader($header_html,'','1');
			// die("!");
			// $mpdf->WriteHTML($content_html);
			$mpdf->WriteHTML(utf8_encode($content_html));
			// die("!");
			$mpdf->Output();			
			
		}

		// Laporan Buku Harian BBT DT
		public function ReportFinanceBBT_Preview_C ( $page_title, $datalaporan, $periode, $judul, $wilayah, $tipetrans, $tipe_terima, $status, $rek, $printdate, $params ) {

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

			// die("Preview C");
			$style_col_ganjil ="float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_summary= "float:left;height:30px;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
	
			$kanan= "text-align:right;padding-right:10px;";
			$kiri = "text-align:left; padding-left:10px;";
			
			$header_html="";
			$content_html= "";
			$nobukti = "!@#$%^&*";
			$nofaktur= "";
			$total = 0;
			$totalnobukti = 0;

			$header_html.= "<table id='div_header' style='margin-bottom:20px;padding-left:1px;width:100%'>";

			$header_html.= "	<tr><td width='50%'><b>" .$judul. "</b></td>
								<td align='right'><b>" .$printdate. "</b></td></tr> 
			";

			$header_html.= "	<tr><td ><b>" .$periode. "</b></td>
								<td align='right'><b>Tipe Terima : " .$tipe_terima. "</b></td></tr>
			";

			$header_html.= "	<tr><td ><b>Wilayah : ".$wilayah. "</b></td>
								<td align='right'><b>Status : " .$status. "</b></td></tr> 
			";
								
			if ($tipetrans == "BBT") {
				$header_html.= "	<tr><td ><b>Tipe Trans : BBTP, BBTL, BBTS</b></td>
									<td align='right'><b>" .$rek. "</b></td></tr> 
				";
			}
			else {
				$header_html.= "	<tr><td ><b>Tipe Trans : ".$tipetrans. "</b></td>
									<td align='right'><b>" .$rek. "</b></td></tr> 
				";
			}
			
			$header_html.= "</table>";	//close div_header

			// TblBuktiBank.No_bukti as NO_BUKTI, TblBuktiBank.Tgl_trans as TGL_TRANS,  
			// TblMsDealer.kd_plg as KD_PLG, TblMsDealer.nm_plg as NM_PLG,
			// TblBuktiBank.Total as TOTAL, TblBuktiBank.Bank as BANK, TblBuktiBank.No_giro as NO_GIRO, 
			// TblBuktiBank.Ket as KET, TblBuktiBank.Status as STATUS,
			// isnull(tblpenerimaanpembfaktur.no_bukti,'') as NO_BUKTI2,
			// isnull(TblPenerimaanPembFaktur.No_Faktur,'') as NO_FAKTUR, TblBuktiBank.No_Rekening as NO_REKENING, 
			// TblAccountBank.Bank as NMBANK, TblAccountBank.Nm_Pemilik AS NM_PEMILIK,
			// TblBuktiBank.Tgl_jatuhTempo as TGL_JATUHTEMPO, TblBuktiBankDetail.KodeTemplateTrx as KODETEMPLATE_TRX, 
			// MsTemplateTrxHD.NamaTemplateTrx as NAMATEMPLATETRX, TblBuktiBank.Type_terima AS TYPE_TERIMA,
			// TblBuktiBankDetail.Total as TOTAL, TblBuktiBankDetail.Catatan as CATATAN
			// , TblBuktiBank.Type_trans as TYPE_TRANS

			$content_html.= "<div class='div_body' style='overflow-x:padding-left:1px;'>";
			$content_html.= "	<table style='width:100%'><tr>";
			$content_html.= "		<td style='width:12%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO BUKTI</td>";
			$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>TANGGAL</td>";
			$content_html.= "		<td style='width:20%; border-bottom:thin solid #333; border-top:thin solid #333;'>PELANGGAN</td>";
			// $content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>BANK</td>";
			// $content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO GIRO</td>";
			$content_html.= "		<td style='width:17%; border-bottom:thin solid #333; border-top:thin solid #333;' align='center'>STATUS</td>";
			$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;' align='center'>TYPE TERIMA</td>";
			$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;' align='center'>TYPE TRANS</td>";
			// $content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>TGL JATUH TEMPO</td>";
			$content_html.= "	</tr>";

			$jum = 0;
			if($datalaporan!=null && isset($datalaporan->data)){
				$jum=count($datalaporan->data);
				for($i=0;$i<$jum;$i++) {
				
					if ($nobukti != $datalaporan->data[$i]->NO_BUKTI) {
						
						if ($nobukti != "!@#$%^&*") {						
							
							// Total No Bukti
							$content_html.= "			<tr><td colspan='2' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td>
							<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>Total</b></td>
							<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totalnobukti)."</b></td>
							<td colspan='2' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td></tr>
							<tr><td>&nbsp;</td></tr>";

							$totalnobukti = 0;
						}

						$content_html.= "	<tr><td>".$datalaporan->data[$i]->NO_BUKTI."</td>";
						$content_html.= "		<td>".date("d-M-Y", strtotime($datalaporan->data[$i]->TGL_TRANS))."</td>";
						$content_html.= "		<td>".$datalaporan->data[$i]->NM_PLG."</td>";	
						// $content_html.= "		<td>".$datalaporan->data[$i]->BANK."</td>";
						// $content_html.= "		<td>".$datalaporan->data[$i]->NO_GIRO."</td>";						
						$content_html.= "		<td align='center'>".$datalaporan->data[$i]->STATUS."</td>";
						$content_html.= "		<td align='center'>".$datalaporan->data[$i]->TYPE_TERIMA."</td>";
						$content_html.= "		<td align='center'>".$datalaporan->data[$i]->TYPE_TRANS."</td>";
						// $content_html.= "		<td>".date("d-M-Y", strtotime($datalaporan->data[$i]->TGL_JATUHTEMPO))."</td>";
						$content_html.= "	</tr>";

					}
					

					// Detail
					$content_html.= "	<tr><td>&nbsp;</td>";
					$content_html.= "		<td>".$datalaporan->data[$i]->KODETEMPLATE_TRX."</td>";		

					if ((trim($datalaporan->data[$i]->TYPE_TRANS)=="BBTP")||(trim($datalaporan->data[$i]->TYPE_TRANS)=="BBTS"))
					{
						$content_html.= "		<td>".$datalaporan->data[$i]->NO_FAKTUR."</td>";
					}
					else
					{
						$content_html.= "		<td>".$datalaporan->data[$i]->NAMATEMPLATETRX."</td>";
					}

					$content_html.= "		<td align='right';>".number_format($datalaporan->data[$i]->TOTALDT)."</td>";
					$content_html.= "		<td colspan='2'>".$datalaporan->data[$i]->CATATAN."</td>";	
					$content_html.= "	</tr>";


					$totalnobukti += $datalaporan->data[$i]->TOTALDT;
					$total += $datalaporan->data[$i]->TOTALDT;

					$nobukti = $datalaporan->data[$i]->NO_BUKTI;
				}
			}
			

			// Total No Bukti
			$content_html.= "			<tr><td colspan='2' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td>
											<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>Total</b></td>
											<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totalnobukti)."</b></td>
											<td colspan='2' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td></tr>
										<tr><td>&nbsp;</td></tr>";

			// Total
			$content_html.= "			<tr><td colspan='2' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td>
										<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>Grand Total</b></td>
										<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($total)."</b></td>
										<td colspan='2' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td></tr>
										<tr><td>&nbsp;</td></tr>";


			$content_html.= "</table>";

			if ($jum==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}

			$mpdf->SetHTMLHeader($header_html,'','1');
			// die("!");
			// $mpdf->WriteHTML($content_html);
			$mpdf->WriteHTML(utf8_encode($content_html));
			// die("!");
			$mpdf->Output();	
		}

		// Laporan Buku Harian BBT DT GrupBy NoRek
		public function ReportFinanceBBT_Preview_D ( $page_title, $datalaporan, $periode, $judul, $wilayah, $tipetrans, $tipe_terima, $status, $rek, $printdate, $params ) {

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

			// die("Preview D");
			$style_col_ganjil ="float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_summary= "float:left;height:30px;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
	
			$kanan= "text-align:right;padding-right:10px;";
			$kiri = "text-align:left; padding-left:10px;";
			
			$header_html="";
			$content_html= "";
			$nobukti = "!@#$%^&*";
			$total = 0;
			$totalnobukti = 0;
			$norekening = "!@#$%^&*";		
			$totalrek = 0;

			$header_html.= "<table id='div_header' style='margin-bottom:20px;padding-left:1px;width:100%'>";

			$header_html.= "	<tr><td width='50%'><b>" .$judul. "</b></td>
								<td align='right'><b>" .$printdate. "</b></td></tr> 
			";

			$header_html.= "	<tr><td ><b>" .$periode. "</b></td>
								<td align='right'><b>Tipe Terima : " .$tipe_terima. "</b></td></tr>
			";

			$header_html.= "	<tr><td ><b>Wilayah : ".$wilayah. "</b></td>
								<td align='right'><b>Status : " .$status. "</b></td></tr> 
			";
					
			if ($tipetrans == "BBT") {
				$header_html.= "	<tr><td ><b>Tipe Trans : BBTP, BBTL, BBTS</b></td>
									<td align='right'><b>" .$rek. "</b></td></tr> 
				";
			}
			else {
				$header_html.= "	<tr><td ><b>Tipe Trans : ".$tipetrans. "</b></td>
									<td align='right'><b>" .$rek. "</b></td></tr> 
				";
			}
			
			$header_html.= "</table>";	//close div_header


			// TblBuktiBank.No_bukti as NO_BUKTI, TblBuktiBank.Tgl_trans as TGL_TRANS,  
			// TblMsDealer.kd_plg as KD_PLG, TblMsDealer.nm_plg as NM_PLG,
			// TblBuktiBank.Total as TOTAL, TblBuktiBank.Bank as BANK, TblBuktiBank.No_giro as NO_GIRO, 
			// TblBuktiBank.Ket as KET, TblBuktiBank.Status as STATUS,
			// isnull(tblpenerimaanpembfaktur.no_bukti,'') as NO_BUKTI2,
			// isnull(TblPenerimaanPembFaktur.No_Faktur,'') as NO_FAKTUR, TblBuktiBank.No_Rekening as NO_REKENING, 
			// TblAccountBank.Bank as NMBANK, TblAccountBank.Nm_Pemilik AS NM_PEMILIK,
			// TblBuktiBank.Tgl_jatuhTempo as TGL_JATUHTEMPO, TblBuktiBankDetail.KodeTemplateTrx as KODETEMPLATE_TRX, 
			// MsTemplateTrxHD.NamaTemplateTrx as NAMATEMPLATETRX, TblBuktiBank.Type_terima AS TYPE_TERIMA,
			// TblBuktiBankDetail.Total as TOTALDT, TblBuktiBankDetail.Catatan as CATATAN
			// , TblBuktiBank.Type_trans as TYPE_TRANS

			$content_html.= "<style> th, td { font-size:9px; } </style>";			

			$content_html.= "<div class='div_body' style='overflow-x:padding-left:1px;'>";
			$content_html.= "	<table style='width:100%'>";
			
			$jum = 0;
			if($datalaporan!=null && isset($datalaporan->data)){
				$jum=count($datalaporan->data);
				for($i=0;$i<$jum;$i++) {
				
					if ($norekening != $datalaporan->data[$i]->NO_REKENING.' - '.$datalaporan->data[$i]->NMBANK.' - '.$datalaporan->data[$i]->NM_PEMILIK) {
						
						// Total No Bukti
						if ($nobukti != "!@#$%^&*") {				
							$content_html.= "			<tr><td colspan='2' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td>
							<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>Total</b></td>
							<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totalnobukti)."</b></td>
							<td colspan='2' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td></tr>
							<tr><td>&nbsp;</td></tr>";

							$totalnobukti = 0;
						}

						// Total Rekening
						if ($norekening != "!@#$%^&*") {						
							$content_html.= "	<tr><td colspan='3' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>Total Rekening = ".$norekening." </b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totalrek)."</b></td>
												<td colspan='2' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td></tr>
												<tr><td>&nbsp;</td></tr>";	

							$totalnobukti = 0;
							$totalrek = 0;
						}

						// Sub Rekening
						$content_html.= "		<tr><td colspan='6'><b>"."No Rekening = ".$datalaporan->data[$i]->NO_REKENING.' - '.$datalaporan->data[$i]->NMBANK.' - '.$datalaporan->data[$i]->NM_PEMILIK."</b></td></tr>";
						
						// Header
						$content_html.= "		<tr>";
						$content_html.= "		<th style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO BUKTI</th>";
						$content_html.= "		<th style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TANGGAL</th>";
						$content_html.= "		<th style='width:20%; border-bottom:thin solid #333; border-top:thin solid #333;'>PELANGGAN</th>";
						// $content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>BANK</td>";
						// $content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO GIRO</td>";
						$content_html.= "		<th style='width:17%; border-bottom:thin solid #333; border-top:thin solid #333;' align='center'>STATUS</th>";
						$content_html.= "		<th style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;' align='center'>TYPE TERIMA</th>";
						$content_html.= "		<th style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;' align='center'>TYPE TRANS</th>";
						// $content_html.= "		<th style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>TGL JATUH TEMPO</th>";
						$content_html.= "	</tr>";

						// No Bukti
						$content_html.= "	<tr><td>".$datalaporan->data[$i]->NO_BUKTI."</td>";
						$content_html.= "		<td>".date("d-M-Y", strtotime($datalaporan->data[$i]->TGL_TRANS))."</td>";
						$content_html.= "		<td>".$datalaporan->data[$i]->NM_PLG."</td>";	
						// $content_html.= "		<td>".$datalaporan->data[$i]->BANK."</td>";
						// $content_html.= "		<td>".$datalaporan->data[$i]->NO_GIRO."</td>";						
						$content_html.= "		<td align='center'>".$datalaporan->data[$i]->STATUS."</td>";
						$content_html.= "		<td align='center'>".$datalaporan->data[$i]->TYPE_TERIMA."</td>";
						$content_html.= "		<td align='center'>".$datalaporan->data[$i]->TYPE_TRANS."</td>";
						// $content_html.= "		<td>".date("d-M-Y", strtotime($datalaporan->data[$i]->TGL_JATUHTEMPO))."</td>";
						$content_html.= "	</tr>";
								
						
					}

					elseif ($nobukti != $datalaporan->data[$i]->NO_BUKTI) {
						
						if ($nobukti != "!@#$%^&*") {						
							
							// Total No Bukti
							$content_html.= "			<tr><td colspan='2' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td>
							<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>Total</b></td>
							<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totalnobukti)."</b></td>
							<td colspan='2' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td></tr>
							<tr><td>&nbsp;</td></tr>";

							$totalnobukti = 0;
						}

						// Header
						$content_html.= "		<tr>";
						$content_html.= "		<th style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO BUKTI</th>";
						$content_html.= "		<th style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TANGGAL</th>";
						$content_html.= "		<th style='width:20%; border-bottom:thin solid #333; border-top:thin solid #333;'>PELANGGAN</th>";
						// $content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>BANK</td>";
						// $content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO GIRO</td>";
						$content_html.= "		<th style='width:17%; border-bottom:thin solid #333; border-top:thin solid #333;' align='center'>STATUS</th>";
						$content_html.= "		<th style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;' align='center'>TYPE TERIMA</th>";
						$content_html.= "		<th style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;' align='center'>TYPE TRANS</th>";
						// $content_html.= "		<th style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>TGL JATUH TEMPO</th>";
						$content_html.= "	</tr>";

						// No Bukti
						$content_html.= "	<tr><td>".$datalaporan->data[$i]->NO_BUKTI."</td>";
						$content_html.= "		<td>".date("d-M-Y", strtotime($datalaporan->data[$i]->TGL_TRANS))."</td>";
						$content_html.= "		<td>".$datalaporan->data[$i]->NM_PLG."</td>";	
						// $content_html.= "		<td>".$datalaporan->data[$i]->BANK."</td>";
						// $content_html.= "		<td>".$datalaporan->data[$i]->NO_GIRO."</td>";						
						$content_html.= "		<td>".$datalaporan->data[$i]->STATUS."</td>";
						$content_html.= "		<td>".$datalaporan->data[$i]->TYPE_TERIMA."</td>";
						$content_html.= "		<td>".$datalaporan->data[$i]->TYPE_TRANS."</td>";
						// $content_html.= "		<td>".date("d-M-Y", strtotime($datalaporan->data[$i]->TGL_JATUHTEMPO))."</td>";
						$content_html.= "	</tr>";

					}
					

					// Detail
					$content_html.= "	<tr><td>&nbsp;</td>";
					$content_html.= "		<td>".$datalaporan->data[$i]->KODETEMPLATE_TRX."</td>";		

					if ((trim($datalaporan->data[$i]->TYPE_TRANS)=="BBTP")||(trim($datalaporan->data[$i]->TYPE_TRANS)=="BBTS"))
					{
						$content_html.= "		<td>".$datalaporan->data[$i]->NO_FAKTUR."</td>";
					}
					else
					{
						$content_html.= "		<td>".$datalaporan->data[$i]->NAMATEMPLATETRX."</td>";
					}

					$content_html.= "		<td align='right';>".number_format($datalaporan->data[$i]->TOTALDT)."</td>";
					$content_html.= "		<td colspan='2'>".$datalaporan->data[$i]->CATATAN."</td>";	
					$content_html.= "	</tr>";


					$totalnobukti += $datalaporan->data[$i]->TOTALDT;
					$total += $datalaporan->data[$i]->TOTALDT;
					$totalrek += $datalaporan->data[$i]->TOTALDT;

					$nobukti = $datalaporan->data[$i]->NO_BUKTI;
					$norekening = $datalaporan->data[$i]->NO_REKENING.' - '.$datalaporan->data[$i]->NMBANK.' - '.$datalaporan->data[$i]->NM_PEMILIK;
				}
			}
			

			// Total No Bukti
			$content_html.= "			<tr><td colspan='2' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td>
											<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>Total</b></td>
											<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totalnobukti)."</b></td>
											<td colspan='2' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td></tr>
										<tr><td>&nbsp;</td></tr>";

			// Total Rekening
			if ($norekening != "!@#$%^&*") {						
				$content_html.= "	<tr><td colspan='3' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>Total Rekening = ".$norekening." </b></td>
									<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($totalrek)."</b></td>
									<td colspan='2' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td></tr>
									<tr><td>&nbsp;</td></tr>";	

			}							

			// G Total
			$content_html.= "			<tr><td colspan='2' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td>
										<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>Grand Total</b></td>
										<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($total)."</b></td>
										<td colspan='2' style='border-bottom:thin solid #333; border-top:thin solid #333;'></td></tr>
										<tr><td>&nbsp;</td></tr>";


			$content_html.= "</table>";

			if ($jum==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}

			// echo $content_html;
			$mpdf->SetHTMLHeader($header_html,'','1');
			// $mpdf->WriteHTML($content_html);
			$mpdf->WriteHTML(utf8_encode($content_html));
			$mpdf->Output();							
		}


		// Laporan Buku Harian BBT
		public function ReportFinanceBBT_Excel_A ( $page_title, $datalaporan, $periode, $judul, $wilayah, $tipetrans, $tipe_terima, $status, $rek, $printdate, $params ) {

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', $judul);
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', $periode);
			$sheet->setCellValue('A3', 'Wilayah : '.$wilayah);

			if ($tipetrans == "BBT") {
				$sheet->setCellValue('A4', 'Tipe Trans : BBTP, BBTL, BBTS');
			}
			else {
				$sheet->setCellValue('A4', 'Tipe Trans : '.$tipetrans);
			}

			$sheet->setCellValue('A5', 'Tipe Terima : '.$tipe_terima);
			$sheet->setCellValue('A6', 'Status : '.$status);
			$sheet->setCellValue('A7', $rek);
            								
			$currcol = 1;
			$currrow = 9;
						

			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO BUKTI');
			$sheet->getColumnDimension('A')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TANGGAL');
			$sheet->getColumnDimension('B')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PELANGGAN');
			$sheet->getColumnDimension('C')->setWidth(35);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
			$sheet->getColumnDimension('D')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'BANK');
			$sheet->getColumnDimension('E')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO GIRO');
			$sheet->getColumnDimension('F')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KETERANGAN');
			$sheet->getColumnDimension('G')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'STATUS');
			$sheet->getColumnDimension('H')->setWidth(10);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TYPE TERIMA');
			$sheet->getColumnDimension('I')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TYPE TRANS');
			$sheet->getColumnDimension('J')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO FAKTUR');
			$sheet->getColumnDimension('K')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
														
			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
						
			$no_bukti_temp = "";
			$no_faktur_temp = ""; 
			
			// Detail
			$jum = 0;
			if($datalaporan!=null && isset($datalaporan->data)){
				$jum=count($datalaporan->data);
				for($i=0; $i<$jum; $i++){
					if ($no_bukti_temp != $datalaporan->data[$i]->NO_BUKTI)
					{   
						$currrow++; 
						$no_bukti_temp = $datalaporan->data[$i]->NO_BUKTI;
						$no_faktur_temp = $datalaporan->data[$i]->NO_FAKTUR;
					}
					else
					{  
						$no_faktur_temp = $no_faktur_temp .','.$datalaporan->data[$i]->NO_FAKTUR; 
					}

					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_BUKTI);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("m-d-Y",strtotime($datalaporan->data[$i]->TGL_TRANS)));
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NM_PLG);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->TOTAL));
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->BANK);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_GIRO);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->KET);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->STATUS);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->TYPE_TERIMA);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->TYPE_TRANS);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no_faktur_temp); 
				}
			}


			
			
			// print_r ($judul);

			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A9:'.$max_col.'10')->getAlignment()->setHorizontal($alignment_center);
			
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A9:'.$max_col.'10')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
			// border
			$sheet->getStyle("A1:".$max_col."10")->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet->getStyle('A9:'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');
			
				
			$filename= $judul.' '.$rek. ' ['.date('Ymd').']'; //save our workbook as this file name
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

		// Laporan Buku Harian BBT GrupBy NoRek
		public function ReportFinanceBBT_Excel_B ( $page_title, $datalaporan, $periode, $judul, $wilayah, $tipetrans, $tipe_terima, $status, $rek, $printdate, $params ) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', $judul);
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', $periode);
			$sheet->setCellValue('A3', 'Wilayah : '.$wilayah);

			if ($tipetrans == "BBT") {
				$sheet->setCellValue('A4', 'Tipe Trans : BBTP, BBTL, BBTS');
			}
			else {
				$sheet->setCellValue('A4', 'Tipe Trans : '.$tipetrans);
			}

			$sheet->setCellValue('A5', 'Tipe Terima : '.$tipe_terima);
			$sheet->setCellValue('A6', 'Status : '.$status);
			$sheet->setCellValue('A7', $rek);
            								
			$currcol = 1;
			$currrow = 9;
						
			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO REKENING');
			$sheet->getColumnDimension('A')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO BUKTI');
			$sheet->getColumnDimension('B')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TANGGAL');
			$sheet->getColumnDimension('C')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PELANGGAN');
			$sheet->getColumnDimension('D')->setWidth(35);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
			$sheet->getColumnDimension('E')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'BANK');
			$sheet->getColumnDimension('F')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO GIRO');
			$sheet->getColumnDimension('G')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KETERANGAN');
			$sheet->getColumnDimension('H')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'STATUS');
			$sheet->getColumnDimension('I')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TYPE TERIMA');
			$sheet->getColumnDimension('J')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TYPE TRANS');
			$sheet->getColumnDimension('K')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO FAKTUR');
			$sheet->getColumnDimension('L')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;

			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
						
			$no_rek_temp = "";
			$no_bukti_temp = "";
			$no_faktur_temp = "";

			// Detail
			$jum = 0;
			if($datalaporan!=null && isset($datalaporan->data)){
				$jum=count($datalaporan->data);
				for($i=0; $i<$jum; $i++){
					if (($no_rek_temp != $datalaporan->data[$i]->NO_REKENING) || ($no_bukti_temp != $datalaporan->data[$i]->NO_BUKTI))
					{  
						$currrow++;
						$no_rek_temp = $datalaporan->data[$i]->NO_REKENING;
						$no_bukti_temp = $datalaporan->data[$i]->NO_BUKTI;
						$no_faktur_temp = $datalaporan->data[$i]->NO_FAKTUR;
					}
					else
					{ 
						$no_faktur_temp = $no_faktur_temp .','.$datalaporan->data[$i]->NO_FAKTUR; 
					} 
						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_REKENING.' - '.$datalaporan->data[$i]->NMBANK);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_BUKTI);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("m-d-Y",strtotime($datalaporan->data[$i]->TGL_TRANS)));
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NM_PLG);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->TOTAL));
						$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->BANK);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_GIRO);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->KET);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->STATUS);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->TYPE_TERIMA);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->TYPE_TRANS);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no_faktur_temp); 

				}
			}
			
			
			
			// print_r ($judul);

			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A9:'.$max_col.'10')->getAlignment()->setHorizontal($alignment_center);
			
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A9:'.$max_col.'10')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
			// border
			$sheet->getStyle("A1:".$max_col."10")->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet->getStyle('A9:'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');
			
				
			$filename= $judul.' '.$rek. ' ['.date('Ymd').']'; //save our workbook as this file name
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

		// Laporan Buku Harian BBT DT
		public function ReportFinanceBBT_Excel_C ( $page_title, $datalaporan, $periode, $judul, $wilayah, $tipetrans, $tipe_terima, $status, $rek, $printdate, $params ) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', $judul);
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', $periode);
			$sheet->setCellValue('A3', 'Wilayah : '.$wilayah);

			if ($tipetrans == "BBT") {
				$sheet->setCellValue('A4', 'Tipe Trans : BBTP, BBTL, BBTS');
			}
			else {
				$sheet->setCellValue('A4', 'Tipe Trans : '.$tipetrans);
			}

			$sheet->setCellValue('A5', 'Tipe Terima : '.$tipe_terima);
			$sheet->setCellValue('A6', 'Status : '.$status);
			$sheet->setCellValue('A7', $rek);
            								
			$currcol = 1;
			$currrow = 9;
					

			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO BUKTI');
			$sheet->getColumnDimension('A')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KODE TEMPLATE TRX');
			$sheet->getColumnDimension('B')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA TEMPLATE TRX');
			$sheet->getColumnDimension('C')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL DT');
			$sheet->getColumnDimension('D')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TANGGAL');
			$sheet->getColumnDimension('E')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PELANGGAN');
			$sheet->getColumnDimension('F')->setWidth(35);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
			$sheet->getColumnDimension('G')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'BANK');
			$sheet->getColumnDimension('H')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO GIRO');
			$sheet->getColumnDimension('I')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'STATUS');
			$sheet->getColumnDimension('J')->setWidth(10);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TYPE TERIMA');
			$sheet->getColumnDimension('K')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TYPE TRANS');
			$sheet->getColumnDimension('L')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL JATUH TEMPO');
			$sheet->getColumnDimension('M')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'CATATAN');
			$sheet->getColumnDimension('N')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;

			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1; 
			
			// Detail
			$jum = 0;
			if($datalaporan!=null && isset($datalaporan->data)){
				$jum=count($datalaporan->data);
				for($i=0; $i<$jum; $i++){ 
 
					$currrow++; 
					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_BUKTI);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->KODETEMPLATE_TRX);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NAMATEMPLATETRX);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->TOTALDT));
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("m-d-Y",strtotime($datalaporan->data[$i]->TGL_TRANS)));
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NM_PLG);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->TOTAL));
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->BANK);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_GIRO);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->STATUS);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->TYPE_TERIMA);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->TYPE_TRANS);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("m-d-Y",strtotime($datalaporan->data[$i]->TGL_JATUHTEMPO)));
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->CATATAN); 
				}
			}
			
			
			// print_r ($judul);

			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A9:'.$max_col.'10')->getAlignment()->setHorizontal($alignment_center);
			
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A9:'.$max_col.'10')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
			// border
			$sheet->getStyle("A1:".$max_col."10")->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet->getStyle('A9:'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');
			
				
			$filename= $judul.' '.$rek. ' ['.date('Ymd').']'; //save our workbook as this file name
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

		// Laporan Buku Harian BBT DT GrupBy NoRek
		public function ReportFinanceBBT_Excel_D ( $page_title, $datalaporan, $periode, $judul, $wilayah, $tipetrans, $tipe_terima, $status, $rek, $printdate, $params ) {

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', $judul);
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', $periode);
			$sheet->setCellValue('A3', 'Wilayah : '.$wilayah);

			if ($tipetrans == "BBT") {
				$sheet->setCellValue('A4', 'Tipe Trans : BBTP, BBTL, BBTS');
			}
			else {
				$sheet->setCellValue('A4', 'Tipe Trans : '.$tipetrans);
			}

			$sheet->setCellValue('A5', 'Tipe Terima : '.$tipe_terima);
			$sheet->setCellValue('A6', 'Status : '.$status);
			$sheet->setCellValue('A7', $rek);
            								
			$currcol = 1;
			$currrow = 9;
					

			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO REKENING');
			$sheet->getColumnDimension('A')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO BUKTI');
			$sheet->getColumnDimension('B')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KODE TEMPLATE TRX');
			$sheet->getColumnDimension('C')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA TEMPLATE TRX');
			$sheet->getColumnDimension('D')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL DT');
			$sheet->getColumnDimension('E')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TANGGAL');
			$sheet->getColumnDimension('F')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PELANGGAN');
			$sheet->getColumnDimension('G')->setWidth(35);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
			$sheet->getColumnDimension('H')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'BANK');
			$sheet->getColumnDimension('I')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO GIRO');
			$sheet->getColumnDimension('J')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'STATUS');
			$sheet->getColumnDimension('K')->setWidth(10);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TYPE TERIMA');
			$sheet->getColumnDimension('L')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TYPE TRANS');
			$sheet->getColumnDimension('M')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL JATUH TEMPO');
			$sheet->getColumnDimension('N')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'CATATAN');
			$sheet->getColumnDimension('O')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;

			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1; 
			
			// Detail
			$jum = 0;
			if($datalaporan!=null && isset($datalaporan->data)){
				$jum=count($datalaporan->data);
				for($i=0; $i<$jum; $i++){
					$currrow++; 
					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_REKENING.' - '.$datalaporan->data[$i]->NMBANK);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_BUKTI);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->KODETEMPLATE_TRX);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NAMATEMPLATETRX);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->TOTALDT));
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("m-d-Y",strtotime($datalaporan->data[$i]->TGL_TRANS)));
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NM_PLG);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->TOTAL));
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->BANK);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_GIRO);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->STATUS);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->TYPE_TERIMA);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->TYPE_TRANS);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("m-d-Y",strtotime($datalaporan->data[$i]->TGL_JATUHTEMPO)));
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->CATATAN); 
				}
			}
			
			
			// print_r ($judul);

			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A9:'.$max_col.'10')->getAlignment()->setHorizontal($alignment_center);
			
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A9:'.$max_col.'10')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
			// border
			$sheet->getStyle("A1:".$max_col."10")->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet->getStyle('A9:'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');
			
				
			$filename= $judul.' '.$rek. ' ['.date('Ymd').']'; //save our workbook as this file name
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




		public function LaporanSisaFaktur()
		{
			$data["report"] = $this->laporan;
			$data["cabang"] = array();
			$data["partner_type"] = $this->ReportFinanceModel->GetListPartnerType();
			$cabang = json_decode(file_get_contents($this->API_URL."/Cabang/GetALLCabangList?api=".urlencode($this->api)));
			if ($cabang->result=="SUCCESS") {
				$data["cabang"] = $cabang->data;
			}
			// echo json_encode($data);die;

			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN SISA FAKTUR";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN SISA FAKTUR";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

			$this->RenderView('LaporanSisaFaktur',$data);
		}
		
		public function PreviewSisaFaktur()
		{
			$post = $this->PopulatePost();
			
			$post['api']='APITES';
			$cabang = explode('#',$post['cabang']);
			$post['cabang']=$cabang[0];
			$post['nama_cabang']=$cabang[1];
			
			if(isset($_POST['btnExcel'])){
				$this->excel_flag = 1;
			}


			$params = array();			
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN SISA FAKTUR";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES LAPORAN SISA FAKTUR";
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);

			if($post['laporan']=='1'){
				$this->PreviewSisaFaktur1($post, $params);
			}
			if($post['laporan']=='2'){
				$this->PreviewSisaFaktur2($post, $params);
			}
			if($post['laporan']=='3'){
				$this->PreviewSisaFaktur3($post, $params);
			}
			if($post['laporan']=='4'){
				$this->PreviewSisaFaktur4($post, $params);
			}
			if($post['laporan']=='5'){
				$this->PreviewSisaFaktur5($post, $params);
			}
			if($post['laporan']=='6'){
				$this->PreviewSisaFaktur6($post, $params);
			}
			if($post['laporan']=='7'){
				$this->PreviewSisaFaktur7($post, $params);
			}
		}
		
		public function PreviewSisaFaktur1($post, $params)
		{

			$URL = $this->API_URL."/ReportFinance/Summary";
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($post),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			
			if($httpcode!=200){
				die('URL "'.$URL.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode);
			}
			
			$result = json_decode($response);
			if(COUNT($result->data)==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				die('Tidak ada data.');
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			$warna_jatuhtempo 	= '909090';
			$warna_wilayah	= 'A9A9A9';
			$warna_toko		= 'C0C0C0';
			$warna_total	= 'D3D3D3';
			
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			$nama_laporan = $this->laporan[$post['laporan']];
			$html = "<html>";
			$html .= "<head>";
			$html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
			
			$html .= "<style>
			*, h2{margin:0}
			body{font-family:'Calibri',Arial;}
			.table{padding:0;margin:0;border-collapse:collapse}
			.table td, .table th { border:0.5px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			$html.= "</head>";
			$html.= "<body>";
			$html.= "<div id='div_header' style='margin-bottom:10px;text-align:center'>";
			$html.= "<div><h2>".$nama_laporan."</h2></div>";
			$html.= "<table>
			<tr><td>PER TANGGAL </td><td class='td-bold'>: ".$post['tanggal']."  </td></tr>
			<tr><td>CABANG </td><td class='td-bold'>: ".$post['nama_cabang']."  </td></tr>
			<tr><td>PARTNER TYPE </td><td class='td-bold'>: ".implode(", ",$post['partner_type'])."  </td></tr>
			<tr><td>PRINT DATE </td><td class='td-bold'>: ".date('d-M-Y H:i:s')."  </td></tr>
			</table>
			</div>";
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($nama_laporan, 0, 31));
				$sheet->getStyle('A1')->getFont()->setSize(12);
				
				$sheet->setCellValue('A1', $nama_laporan);
				$sheet->setCellValue('A2', 'PER TANGGAL : '.$post['tanggal']);
				$sheet->setCellValue('A3', 'CABANG : '.$post['nama_cabang']);
				$sheet->setCellValue('A4', 'PARTNER TYPE : '.implode(", ",$post['partner_type']));
				$sheet->setCellValue('A5', 'PRINT DATE : '.date('d-M-Y H:i:s'));
				
				$sheet->mergeCells('A1:E1');
				$sheet->mergeCells('A2:E2');
				$sheet->mergeCells('A3:E3');
				$sheet->mergeCells('A4:E4');
				$sheet->mergeCells('A5:E5');
				
				$sheet->getStyle("A1:A5")->getFont()->setBold(true);
				$sheet->getStyle('A1:A5')->getAlignment()->setHorizontal($alignment_center);
			}
			
			$html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$html.= "<div style='clear:both'></div>";
			
			$html.= "<table class='table' style='font-size:10pt!important;width:100%'>";
			$html.='<tr style="background:#'.$warna_total.'">';
			$html.='<th width="5%">No</th>';
			$html.='<th width="35%">TGL. JATUH TEMPO</th>';
			$html.='<th width="20%">TOTAL FAKTUR</th>';
			$html.='<th width="20%">TOTAL TERBAYAR</th>';
			$html.='<th width="20%">SISA FAKTUR</th>';
			$html.='</tr>';
			
			$curcol = 1;
			$currow = 5;
			
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO');
				$sheet->getColumnDimension('A')->setWidth(5);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TGL. JATUH TEMPO');
				$sheet->getColumnDimension('B')->setWidth(30);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL FAKTUR');
				$sheet->getColumnDimension('C')->setWidth(20);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL TERBAYAR');
				$sheet->getColumnDimension('D')->setWidth(20);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'SISA FAKTUR');
				$sheet->getColumnDimension('E')->setWidth(20);
				$sheet->getStyle("A".$currow.":E".$currow)->getFont()->setBold(true);
				$sheet->getStyle("A".$currow.":E".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
			}
			$total_faktur = 0;
			$total_terbayar = 0;
			$total_sisa = 0;
			$no = 0;
			foreach($result->data as $row){	
				$no++;
				$html.='<tr>';
				$html.='<td class="">'.$no.'</td>';
				$html.='<td class="">'.date('d-M-Y',strtotime($row->TGLJATUHTEMPO)).'</td>';
				$html.='<td class="td-right">'.number_format($row->TOTAL_FAKTUR).'</td>';
				$html.='<td class="td-right">'.number_format($row->TOTAL_TERBAYAR).'</td>';
				$html.='<td class="td-right">'.number_format($row->SISA_FAKTUR).'</td>';
				$html.='</tr>';
				
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $no);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, date('d-M-Y',strtotime($row->TGLJATUHTEMPO)));
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->TOTAL_FAKTUR);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->TOTAL_TERBAYAR);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->SISA_FAKTUR);
					$sheet->getStyle('C'.$currow.':E'.$currow)->getNumberFormat()->setFormatCode('#,##0');	
				}
				
				$total_faktur += $row->TOTAL_FAKTUR;
				$total_terbayar += $row->TOTAL_TERBAYAR;
				$total_sisa += $row->SISA_FAKTUR;
			}
			
			$html.='<tr style="background:#'.$warna_total.'">';
			$html.='<td class="td-center td-bold" colspan="2">TOTAL</td>';
			$html.='<td class="td-right td-bold">'.number_format($total_faktur).'</td>';
			$html.='<td class="td-right td-bold">'.number_format($total_terbayar).'</td>';
			$html.='<td class="td-right td-bold">'.number_format($total_sisa).'</td>';
			$html.='</tr>';
			
			if($this->excel_flag == 1){
				$currow++;
				$curcol = 1;
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL');
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_faktur);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_terbayar);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_sisa);
				$sheet->mergeCells("A".$currow.":B".$currow);
				$sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_center);
				$sheet->getStyle("A".$currow.":E".$currow)->getFont()->setBold(true);
				$sheet->getStyle("A".$currow.":E".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
				$sheet->getStyle('C'.$currow.':E'.$currow)->getNumberFormat()->setFormatCode('#,##0');
			}
			
			$html.='</table>';
			$html.= "</div>";
			$html.= "</body></html>";
			
			if($this->excel_flag == 1){
				$styleArray = [
					'borders' => [
						'allBorders' => [
							'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
						],
					],
				];
				$sheet ->getStyle('A4:E'.$currow)->applyFromArray($styleArray);
				// $sheet->freezePane('A6');
				$sheet->setSelectedCell('A1');
				$filename=$nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 
				exit();
			}
			else{
				require_once __DIR__ . '\vendor\autoload.php';
				$mpdf = new \Mpdf\Mpdf(array(
					'mode' => '',
					'format' => 'A4',
					'default_font_size' => 8,
					'default_font' => 'tahoma',
					'orientation' => 'P'
				));
				$mpdf->defaultfooterline = 1;
				$mpdf->SetFooter('
				<table width="100%">
					<tr>
						<td>'.date('d-M-Y H:i:s').'</td>
						<td class="td-right">Halaman {PAGENO} / {nbpg}</td>
					</tr>
				</table>');
				// $mpdf->keepColumns = true;
				$mpdf->WriteHTML($html);
				$mpdf->Output();
			
			}
			
			$data['title'] = $nama_laporan;
			$data['content_html'] = $html;
			
			$this->load->view('LaporanResultView',$data);
		}
		
		public function PreviewSisaFaktur2($post, $params)
		{
			$URL = $this->API_URL."/ReportFinance/PerWilayah";
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($post),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			
			if($httpcode!=200){
				die('URL "'.$URL.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode);
			}
			
			$result = json_decode($response);
			if(COUNT($result->data)==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				die('Tidak ada data.');
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			$warna_jatuhtempo 	= '909090';
			$warna_wilayah	= 'A9A9A9';
			$warna_toko		= 'C0C0C0';
			$warna_total	= 'D3D3D3';
			
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			$nama_laporan = $this->laporan[$post['laporan']];
			$html = "<html>";
			$html .= "<head>";
			$html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
			
			$html .= "<style>
			*, h2{margin:0}
			body{font-family:'Calibri',Arial;}
			.table{padding:0;margin:0;border-collapse:collapse}
			.table td, .table th { border:0.5px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			$html.= "</head>";
			$html.= "<body>";
			$html.= "<div id='div_header' style='margin-bottom:10px;text-align:center'>";
			$html.= "<div><h2>".$nama_laporan."</h2></div>";
			$html.= "<table>
			<tr><td>PER TANGGAL </td><td class='td-bold'>: ".$post['tanggal']."  </td></tr>
			<tr><td>CABANG </td><td class='td-bold'>: ".$post['nama_cabang']."  </td></tr>
			<tr><td>PARTNER TYPE </td><td class='td-bold'>: ".implode(", ",$post['partner_type'])."</td></tr>
			<tr><td>PRINT DATE </td><td class='td-bold'>: ".date('d-M-Y H:i:s')."</td></tr>
			</table>
			</div>";
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($nama_laporan, 0, 31));
				$sheet->getStyle('A1')->getFont()->setSize(12);
				
				$sheet->setCellValue('A1', $nama_laporan);
				$sheet->setCellValue('A2', 'PER TANGGAL : '.$post['tanggal']);
				$sheet->setCellValue('A3', 'CABANG : '.$post['nama_cabang']);
				$sheet->setCellValue('A4', 'PARTNER TYPE : '.implode(", ",$post['partner_type']));
				$sheet->setCellValue('A5', 'PRINT DATE : '.date('d-M-Y H:i:s'));
				
				$sheet->mergeCells('A1:E1');
				$sheet->mergeCells('A2:E2');
				$sheet->mergeCells('A3:E3');
				$sheet->mergeCells('A4:E4');
				$sheet->mergeCells('A5:E5');
				
				$sheet->getStyle("A1:A5")->getFont()->setBold(true);
				$sheet->getStyle('A1:A5')->getAlignment()->setHorizontal($alignment_center);
			}
			
			$html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$html.= "<div style='clear:both'></div>";
			$html.= "<table class='table' style='font-size:10pt!important;width:100%'>";
			
			$currow = 5;
			
			$total_jatuhtempo_faktur = 0;
			$total_jatuhtempo_terbayar = 0;
			$total_jatuhtempo_sisa = 0;
			
			
			foreach($result->data as $tgljatuhtempo => $wilayah){
			
				$html.='<tr style="background:#'.$warna_toko.'">';
				$html.='<td class="td-bold" colspan="5">JT '.date('d-M-Y',strtotime($tgljatuhtempo)).'</td>';
				$html.='</tr>';
				
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'JT '.date('d-M-Y',strtotime($tgljatuhtempo)));
					$sheet->getStyle("A".$currow)->getFont()->setBold(true);
					$sheet->mergeCells("A".$currow.":E".$currow);
					$sheet->getStyle("A".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
				}
				
				$html.='<tr style="background:#'.$warna_total.'">';
				$html.='<th width="5%">No</th>';
				$html.='<th width="35%">WILAYAH</th>';
				$html.='<th width="20%">TOTAL FAKTUR</th>';
				$html.='<th width="20%">TOTAL TERBAYAR</th>';
				$html.='<th width="20%">SISA FAKTUR</th>';
				$html.='</tr>';
				
				
				if($this->excel_flag == 1){
					$curcol = 1;
					$currow++;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO');
					$sheet->getColumnDimension('A')->setWidth(5);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'WILAYAH');
					$sheet->getColumnDimension('B')->setWidth(30);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL FAKTUR');
					$sheet->getColumnDimension('C')->setWidth(20);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL TERBAYAR');
					$sheet->getColumnDimension('D')->setWidth(20);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'SISA FAKTUR');
					$sheet->getColumnDimension('E')->setWidth(20);
					$sheet->getStyle("A".$currow.":E".$currow)->getFont()->setBold(true);
					$sheet->getStyle("A".$currow.":E".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
				}
				$total_faktur = 0;
				$total_terbayar = 0;
				$total_sisa = 0;
				$no = 0;
			
				foreach($wilayah as $row){
					$no++;
					$html.='<tr>';
					$html.='<td class="td-center">'.$no.'</td>';
					$html.='<td class="">'.$row->WILAYAH.'</td>';
					$html.='<td class="td-right">'.number_format($row->TOTAL_FAKTUR).'</td>';
					$html.='<td class="td-right">'.number_format($row->TOTAL_TERBAYAR).'</td>';
					$html.='<td class="td-right">'.number_format($row->SISA_FAKTUR).'</td>';
					$html.='</tr>';
					
					if($this->excel_flag == 1){
						$currow++;
						$curcol = 1;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $no);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->WILAYAH);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->TOTAL_FAKTUR);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->TOTAL_TERBAYAR);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->SISA_FAKTUR);
						$sheet->getStyle('C'.$currow.':E'.$currow)->getNumberFormat()->setFormatCode('#,##0');	
					}
					
					$total_faktur += $row->TOTAL_FAKTUR;
					$total_terbayar += $row->TOTAL_TERBAYAR;
					$total_sisa += $row->SISA_FAKTUR;
				}
				
				$html.='<tr style="background:#'.$warna_total.'">';
				$html.='<td class="td-bold" colspan="2">TOTAL [JT '.date('d-M-Y',strtotime($tgljatuhtempo)).']</td>';
				$html.='<td class="td-right td-bold">'.number_format($total_faktur).'</td>';
				$html.='<td class="td-right td-bold">'.number_format($total_terbayar).'</td>';
				$html.='<td class="td-right td-bold">'.number_format($total_sisa).'</td>';
				$html.='</tr>';
				
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL [JT '.date('d-M-Y',strtotime($tgljatuhtempo)).']');
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_faktur);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_terbayar);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_sisa);
					$sheet->mergeCells("A".$currow.":B".$currow);
					// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_right);
					$sheet->getStyle("A".$currow.":E".$currow)->getFont()->setBold(true);
					$sheet->getStyle("A".$currow.":E".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
					$sheet->getStyle('C'.$currow.':E'.$currow)->getNumberFormat()->setFormatCode('#,##0');
				}
				
				$total_jatuhtempo_faktur += $total_faktur;
				$total_jatuhtempo_terbayar += $total_terbayar;
				$total_jatuhtempo_sisa += $total_sisa;
				
			}
			
			
			
			$html.='<tr style="background:#'.$warna_toko.'">';
			$html.='<td class="td-right td-bold" colspan="2">GRAND TOTAL</td>';
			$html.='<td class="td-right td-bold">'.number_format($total_jatuhtempo_faktur).'</td>';
			$html.='<td class="td-right td-bold">'.number_format($total_jatuhtempo_terbayar).'</td>';
			$html.='<td class="td-right td-bold">'.number_format($total_jatuhtempo_sisa).'</td>';
			$html.='</tr>';
			
			if($this->excel_flag == 1){
				$currow++;
				$curcol = 1;
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'GRAND TOTAL');
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_jatuhtempo_faktur);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_jatuhtempo_terbayar);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_jatuhtempo_sisa);
				$sheet->mergeCells("A".$currow.":B".$currow);
				$sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_right);
				$sheet->getStyle("A".$currow.":E".$currow)->getFont()->setBold(true);
				$sheet->getStyle("A".$currow.":E".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
				$sheet->getStyle('C'.$currow.':E'.$currow)->getNumberFormat()->setFormatCode('#,##0');
			}
			
			$html.='</table>';
			$html.= "</div>";
			$html.= "</body></html>";
			
			if($this->excel_flag == 1){
				$styleArray = [
					'borders' => [
						'allBorders' => [
							'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
						],
					],
				];
				$sheet ->getStyle('A4:E'.$currow)->applyFromArray($styleArray);
				// $sheet->freezePane('A6');
				$sheet->setSelectedCell('A1');
				$filename=$nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 
				exit();
			}
			else{
				require_once __DIR__ . '\vendor\autoload.php';
				$mpdf = new \Mpdf\Mpdf(array(
					'mode' => '',
					'format' => 'A4',
					'default_font_size' => 8,
					'default_font' => 'tahoma',
					'orientation' => 'P'
				));
				$mpdf->defaultfooterline = 1;
				$mpdf->SetFooter('
				<table width="100%">
					<tr>
						<td>'.date('d-M-Y H:i:s').'</td>
						<td class="td-right">Halaman {PAGENO} / {nbpg}</td>
					</tr>
				</table>');
				$mpdf->WriteHTML($html);
				$mpdf->Output();
			
			}
			
			$data['title'] = $nama_laporan;
			$data['content_html'] = $html;
			
			$this->load->view('LaporanResultView',$data);
		}
		
		public function PreviewSisaFaktur3($post, $params)
		{			
			$URL = $this->API_URL."/ReportFinance/PerToko";
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($post),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			
			if($httpcode!=200){
				die('URL "'.$URL.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode);
			}
			
			$result = json_decode($response);
			if(COUNT($result->data)==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				die('Tidak ada data.');
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			$warna_jatuhtempo 	= '909090';
			$warna_wilayah	= 'A9A9A9';
			$warna_toko		= 'C0C0C0';
			$warna_total	= 'D3D3D3';
			
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			$nama_laporan = $this->laporan[$post['laporan']];
			$html = "<html>";
			$html .= "<head>";
			$html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
			
			$html .= "<style>
			*, h2{margin:0}
			body{font-family:'Calibri',Arial;}
			.table{padding:0;margin:0;border-collapse:collapse}
			.table td, .table th { border:0.5px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			$html.= "</head>";
			$html.= "<body>";
			$html.= "<div id='div_header' style='margin-bottom:10px;text-align:center'>";
			$html.= "<div><h2>".$nama_laporan."</h2></div>";
			$html.= "<table>
			<tr><td>PER TANGGAL </td><td class='td-bold'>: ".$post['tanggal']."  </td></tr>
			<tr><td>CABANG </td><td class='td-bold'>: ".$post['nama_cabang']."  </td></tr>
			<tr><td>PARTNER TYPE </td><td class='td-bold'>: ".implode(", ",$post['partner_type'])."</td></tr>
			<tr><td>PRINT DATE </td><td class='td-bold'>: ".date('d-M-Y H:i:s')."</td></tr>
			</table>
			</div>";
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($nama_laporan, 0, 31));
				$sheet->getStyle('A1')->getFont()->setSize(12);
				
				$sheet->setCellValue('A1', $nama_laporan);
				$sheet->setCellValue('A2', 'PER TANGGAL : '.$post['tanggal']);
				$sheet->setCellValue('A3', 'CABANG : '.$post['nama_cabang']);
				$sheet->setCellValue('A4', 'PARTNER TYPE : '.implode(", ",$post['partner_type']));
				$sheet->setCellValue('A5', 'PRINT DATE : '.date('d-M-Y H:i:s'));
				
				$sheet->mergeCells('A1:E1');
				$sheet->mergeCells('A2:E2');
				$sheet->mergeCells('A3:E3');
				$sheet->mergeCells('A4:E4');
				$sheet->mergeCells('A5:E5');
				
				$sheet->getStyle("A1:A5")->getFont()->setBold(true);
				$sheet->getStyle('A1:A5')->getAlignment()->setHorizontal($alignment_center);
			}
			
			$html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$html.= "<div style='clear:both'></div>";
			
			
			$html.= "<table class='table' style='font-size:10pt!important;width:100%'>";
			
			$currow = 5;
			
			$total_jatuhtempo_faktur = 0;
			$total_jatuhtempo_terbayar = 0;
			$total_jatuhtempo_sisa = 0;
			
			foreach($result->data as $tgljatuhtempo => $wilayah){
			
				$html.='<tr style="background:#'.$warna_jatuhtempo.'">';
				$html.='<td class="td-bold" colspan="5">[JT '.date('d-M-Y',strtotime($tgljatuhtempo)).']</td>';
				$html.='</tr>';
				
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, '[JT '.date('d-M-Y',strtotime($tgljatuhtempo)).']');
					$sheet->mergeCells('A'.$currow.':E'.$currow);
					$sheet->getStyle("A".$currow)->getFont()->setBold(true);
					$sheet->getStyle("A".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_jatuhtempo);
				}
				
				$total_wilayah_faktur = 0;
				$total_wilayah_terbayar = 0;
				$total_wilayah_sisa = 0;
				
				foreach($wilayah as $nama_wilayah => $toko){	
					$html.='<tr style="background:#'.$warna_wilayah.'">';
					$html.='<td class="td-bold" colspan="5">[JT '.date('d-M-Y',strtotime($tgljatuhtempo)).'] ['.$nama_wilayah.']</td>';
					$html.='</tr>';
					
					if($this->excel_flag == 1){
						$currow++;
						$curcol = 1;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, '[JT '.date('d-M-Y',strtotime($tgljatuhtempo)).'] ['.$nama_wilayah.']');
						$sheet->mergeCells('A'.$currow.':E'.$currow);
						$sheet->getStyle("A".$currow)->getFont()->setBold(true);
						$sheet->getStyle("A".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_wilayah);
					}
					$html.='<tr style="background:#'.$warna_total.'">';
					$html.='<th width="5%">No</th>';
					$html.='<th width="35%">WILAYAH</th>';
					$html.='<th width="20%">TOTAL FAKTUR</th>';
					$html.='<th width="20%">TOTAL TERBAYAR</th>';
					$html.='<th width="20%">SISA FAKTUR</th>';
					$html.='</tr>';
					
					
					if($this->excel_flag == 1){
						$curcol = 1;
						$currow++;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO');
						$sheet->getColumnDimension('A')->setWidth(5);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'WILAYAH');
						$sheet->getColumnDimension('B')->setWidth(30);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL FAKTUR');
						$sheet->getColumnDimension('C')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL TERBAYAR');
						$sheet->getColumnDimension('D')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'SISA FAKTUR');
						$sheet->getColumnDimension('E')->setWidth(20);
						$sheet->getStyle("A".$currow.":E".$currow)->getFont()->setBold(true);
						$sheet->getStyle("A".$currow.":E".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
					}
					$total_faktur = 0;
					$total_terbayar = 0;
					$total_sisa = 0;
					$no = 0;
				
					foreach($toko as $row){	
						$no++;
						$html.='<tr>';
						$html.='<td class="td-center">'.$no.'</td>';
						$html.='<td class="">'.$row->PELANGGAN.'</td>';
						$html.='<td class="td-right">'.number_format($row->TOTAL_FAKTUR).'</td>';
						$html.='<td class="td-right">'.number_format($row->TOTAL_TERBAYAR).'</td>';
						$html.='<td class="td-right">'.number_format($row->SISA_FAKTUR).'</td>';
						$html.='</tr>';
						
						if($this->excel_flag == 1){
							$currow++;
							$curcol = 1;
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $no);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->PELANGGAN);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->TOTAL_FAKTUR);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->TOTAL_TERBAYAR);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->SISA_FAKTUR);
							$sheet->getStyle('C'.$currow.':E'.$currow)->getNumberFormat()->setFormatCode('#,##0');	
						}
						
						$total_faktur += $row->TOTAL_FAKTUR;
						$total_terbayar += $row->TOTAL_TERBAYAR;
						$total_sisa += $row->SISA_FAKTUR;
					}
					
					$html.='<tr style="background:#'.$warna_total.'">';
					$html.='<td class="td-bold" colspan="2">TOTAL [JT '.date('d-M-Y',strtotime($tgljatuhtempo)).'] ['.$nama_wilayah.']</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_faktur).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_terbayar).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_sisa).'</td>';
					$html.='</tr>';
					
					if($this->excel_flag == 1){
						$currow++;
						$curcol = 1;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL [JT '.date('d-M-Y',strtotime($tgljatuhtempo)).'] ['.$nama_wilayah.']');
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_faktur);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_terbayar);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_sisa);
						$sheet->mergeCells("A".$currow.":B".$currow);
						// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_center);
						$sheet->getStyle("A".$currow.":E".$currow)->getFont()->setBold(true);
						$sheet->getStyle("A".$currow.":E".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
						$sheet->getStyle('C'.$currow.':E'.$currow)->getNumberFormat()->setFormatCode('#,##0');
					}
						
					$total_wilayah_faktur += $total_faktur;
					$total_wilayah_terbayar += $total_terbayar;
					$total_wilayah_sisa += $total_sisa;
					
				}
			
				$html.='<tr style="background:#'.$warna_toko.'">';
				$html.='<td class="td-bold" colspan="2">TOTAL [JT '.date('d-M-Y',strtotime($tgljatuhtempo)).']</td>';
				$html.='<td class="td-right td-bold">'.number_format($total_wilayah_faktur).'</td>';
				$html.='<td class="td-right td-bold">'.number_format($total_wilayah_terbayar).'</td>';
				$html.='<td class="td-right td-bold">'.number_format($total_wilayah_sisa).'</td>';
				$html.='</tr>';
				
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL [JT '.date('d-M-Y',strtotime($tgljatuhtempo)).']');
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_wilayah_faktur);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_wilayah_terbayar);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_wilayah_sisa);
					$sheet->mergeCells("A".$currow.":B".$currow);
					// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_center);
					$sheet->getStyle("A".$currow.":E".$currow)->getFont()->setBold(true);
					$sheet->getStyle("A".$currow.":E".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
					$sheet->getStyle('C'.$currow.':E'.$currow)->getNumberFormat()->setFormatCode('#,##0');
				}
						
				$total_jatuhtempo_faktur += $total_wilayah_faktur;
				$total_jatuhtempo_terbayar += $total_wilayah_terbayar;
				$total_jatuhtempo_sisa += $total_wilayah_sisa;
			}
			
			$html.='<tr style="background:#'.$warna_wilayah.'">';
			$html.='<td class="td-bold" colspan="2">GRAND TOTAL</td>';
			$html.='<td class="td-right td-bold">'.number_format($total_jatuhtempo_faktur).'</td>';
			$html.='<td class="td-right td-bold">'.number_format($total_jatuhtempo_terbayar).'</td>';
			$html.='<td class="td-right td-bold">'.number_format($total_jatuhtempo_sisa).'</td>';
			$html.='</tr>';
			
			if($this->excel_flag == 1){
				$currow++;
				$curcol = 1;
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'GRAND TOTAL');
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_jatuhtempo_faktur);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_jatuhtempo_terbayar);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_jatuhtempo_sisa);
				$sheet->mergeCells("A".$currow.":B".$currow);
				// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_center);
				$sheet->getStyle("A".$currow.":E".$currow)->getFont()->setBold(true);
				$sheet->getStyle("A".$currow.":E".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_wilayah);
				$sheet->getStyle('C'.$currow.':E'.$currow)->getNumberFormat()->setFormatCode('#,##0');
			}
			
			$html.='</table>';
			$html.= "</div>";
			$html.= "</body></html>";
			
			if($this->excel_flag == 1){
				$styleArray = [
					'borders' => [
						'allBorders' => [
							'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
						],
					],
				];
				$sheet ->getStyle('A4:E'.$currow)->applyFromArray($styleArray);
				// $sheet->freezePane('A6');
				$sheet->setSelectedCell('A1');
				$filename=$nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 
				exit();
			}
			else{
				require_once __DIR__ . '\vendor\autoload.php';
				$mpdf = new \Mpdf\Mpdf(array(
					'mode' => '',
					'format' => 'A4',
					'default_font_size' => 8,
					'default_font' => 'tahoma',
					'orientation' => 'P'
				));
				$mpdf->defaultfooterline = 1;
				$mpdf->SetFooter('
				<table width="100%">
					<tr>
						<td>'.date('d-M-Y H:i:s').'</td>
						<td class="td-right">Halaman {PAGENO} / {nbpg}</td>
					</tr>
				</table>');
				$mpdf->WriteHTML($html);
				$mpdf->Output();
			
			}
			
			$data['title'] = $nama_laporan;
			$data['content_html'] = $html;
			
			$this->load->view('LaporanResultView',$data);
		}
		
		public function PreviewSisaFaktur4($post, $params)
		{
			$URL = $this->API_URL."/ReportFinance/PerFaktur";
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($post),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			
			if($httpcode!=200){
				die('URL "'.$URL.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode);
			}
			
			$result = json_decode($response);
			if(COUNT($result->data)==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				die('Tidak ada data.');
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			$warna_jatuhtempo 	= '909090';
			$warna_wilayah	= 'A9A9A9';
			$warna_toko		= 'C0C0C0';
			$warna_total	= 'D3D3D3';
			
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			$nama_laporan = $this->laporan[$post['laporan']];
			$html = "<html>";
			$html .= "<head>";
			$html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
			
			$html .= "<style>
			*, h2{margin:0}
			body{font-family:'Calibri',Arial;}
			.table{padding:0;margin:0;border-collapse:collapse}
			.table td, .table th { border:0.5px solid #000; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			$html.= "</head>";
			$html.= "<body>";
			$html.= "<div id='div_header' style='margin-bottom:10px;text-align:center'>";
			$html.= "<div><h2>".$nama_laporan."</h2></div>";
			$html.= "<table>
			<tr><td>PER TANGGAL </td><td class='td-bold'>: ".$post['tanggal']."  </td></tr>
			<tr><td>CABANG </td><td class='td-bold'>: ".$post['nama_cabang']."  </td></tr>
			<tr><td>PARTNER TYPE </td><td class='td-bold'>: ".implode(", ",$post['partner_type'])."</td></tr>
			<tr><td>PRINT DATE </td><td class='td-bold'>: ".date('d-M-Y H:i:s')."</td></tr>
			</table>
			</div>";
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($nama_laporan, 0, 31));
				$sheet->getStyle('A1')->getFont()->setSize(12);
				
				$sheet->setCellValue('A1', $nama_laporan);
				$sheet->setCellValue('A2', 'PER TANGGAL : '.$post['tanggal']);
				$sheet->setCellValue('A3', 'CABANG : '.$post['nama_cabang']);
				$sheet->setCellValue('A4', 'PARTNER TYPE : '.implode(", ",$post['partner_type']));
				$sheet->setCellValue('A5', 'PRINT DATE : '.date('d-M-Y H:i:s'));
				
				$sheet->mergeCells('A1:E1');
				$sheet->mergeCells('A2:E2');
				$sheet->mergeCells('A3:E3');
				$sheet->mergeCells('A4:E4');
				$sheet->mergeCells('A5:E5');
				
				$sheet->getStyle("A1:A5")->getFont()->setBold(true);
				$sheet->getStyle('A1:A5')->getAlignment()->setHorizontal($alignment_center);
			}
			
			$html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$html.= "<div style='clear:both'></div>";
			$html.= "<table class='table' style='font-size:10pt!important;width:100%'>";
			
			$currow = 5;
			
			$total_jatuhtempo_faktur = 0;
			$total_jatuhtempo_terbayar = 0;
			$total_jatuhtempo_sisa = 0;
			
			foreach($result->data as $tgljatuhtempo => $wilayah){
			
				$html.='<tr style="background:#'.$warna_jatuhtempo.'">';
				$html.='<td class="td-bold" colspan="5">[JT '.date('d-M-Y',strtotime($tgljatuhtempo)).']</td>';
				$html.='</tr>';
				
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, '[JT '.date('d-M-Y',strtotime($tgljatuhtempo)).']');
					$sheet->mergeCells('A'.$currow.':E'.$currow);
					$sheet->getStyle("A".$currow)->getFont()->setBold(true);
					$sheet->getStyle("A".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_jatuhtempo);
				}
				
				$total_wilayah_faktur = 0;
				$total_wilayah_terbayar = 0;
				$total_wilayah_sisa = 0;
				
				foreach($wilayah as $nama_wilayah => $toko){	
					$html.='<tr style="background:#'.$warna_wilayah.'">';
					$html.='<td class="td-bold" colspan="5">[JT '.date('d-M-Y',strtotime($tgljatuhtempo)).'] ['.$nama_wilayah.']</td>';
					$html.='</tr>';
					
					if($this->excel_flag == 1){
						$currow++;
						$curcol = 1;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, '[JT '.date('d-M-Y',strtotime($tgljatuhtempo)).'] ['.$nama_wilayah.']');
						$sheet->mergeCells('A'.$currow.':E'.$currow);
						$sheet->getStyle("A".$currow)->getFont()->setBold(true);
						$sheet->getStyle("A".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_wilayah);
					}
				
					$total_toko_faktur = 0;
					$total_toko_terbayar = 0;
					$total_toko_sisa = 0;
					
					foreach($toko as $nama_toko => $faktur){	
						$html.='<tr style="background:#'.$warna_toko.'">';
						$html.='<td class="td-bold" colspan="5">[JT '.date('d-M-Y',strtotime($tgljatuhtempo)).'] ['.$nama_wilayah.'] ['.$nama_toko.']</td>';
						$html.='</tr>';
						
						if($this->excel_flag == 1){
							$currow++;
							$curcol = 1;
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, '[JT '.date('d-M-Y',strtotime($tgljatuhtempo)).'] ['.$nama_wilayah.'] ['.$nama_toko.']');
							$sheet->mergeCells('A'.$currow.':E'.$currow);
							$sheet->getStyle("A".$currow)->getFont()->setBold(true);
							$sheet->getStyle("A".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
						}
						$html.='<tr style="background:#'.$warna_total.'">';
						$html.='<th width="5%">No</th>';
						$html.='<th width="35%">NO FAKTUR</th>';
						$html.='<th width="20%">TOTAL FAKTUR</th>';
						$html.='<th width="20%">TOTAL TERBAYAR</th>';
						$html.='<th width="20%">SISA FAKTUR</th>';
						$html.='</tr>';
						
						
						if($this->excel_flag == 1){
							$curcol = 1;
							$currow++;
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO');
							$sheet->getColumnDimension('A')->setWidth(5);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO FAKTUR');
							$sheet->getColumnDimension('B')->setWidth(30);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL FAKTUR');
							$sheet->getColumnDimension('C')->setWidth(20);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL TERBAYAR');
							$sheet->getColumnDimension('D')->setWidth(20);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'SISA FAKTUR');
							$sheet->getColumnDimension('E')->setWidth(20);
							$sheet->getStyle("A".$currow.":E".$currow)->getFont()->setBold(true);
							$sheet->getStyle("A".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
						}
						$total_faktur = 0;
						$total_terbayar = 0;
						$total_sisa = 0;
						$no = 0;
					
						foreach($faktur as $row){	
							$no++;
							$html.='<tr>';
							$html.='<td class="td-center">'.$no.'</td>';
							$html.='<td class="">'.$row->NO_FAKTUR.'</td>';
							$html.='<td class="td-right">'.number_format($row->TOTAL_FAKTUR).'</td>';
							$html.='<td class="td-right">'.number_format($row->TOTAL_TERBAYAR).'</td>';
							$html.='<td class="td-right">'.number_format($row->SISA_FAKTUR).'</td>';
							$html.='</tr>';
							
							if($this->excel_flag == 1){
								$currow++;
								$curcol = 1;
								$sheet->setCellValueByColumnAndRow($curcol++, $currow, $no);
								$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->NO_FAKTUR);
								$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->TOTAL_FAKTUR);
								$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->TOTAL_TERBAYAR);
								$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->SISA_FAKTUR);
								$sheet->getStyle('C'.$currow.':E'.$currow)->getNumberFormat()->setFormatCode('#,##0');	
							}
							
							$total_faktur += $row->TOTAL_FAKTUR;
							$total_terbayar += $row->TOTAL_TERBAYAR;
							$total_sisa += $row->SISA_FAKTUR;
						}
						
						$html.='<tr style="background:#'.$warna_total.'">';
						$html.='<td class="td-bold" colspan="2">TOTAL</td>';
						$html.='<td class="td-right td-bold">'.number_format($total_faktur).'</td>';
						$html.='<td class="td-right td-bold">'.number_format($total_terbayar).'</td>';
						$html.='<td class="td-right td-bold">'.number_format($total_sisa).'</td>';
						$html.='</tr>';
						$html.='<tr><td colspan="5">&nbsp;</td></tr>';
						
						if($this->excel_flag == 1){
							$currow++;
							$curcol = 1;
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL');
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_faktur);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_terbayar);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_sisa);
							$sheet->mergeCells("A".$currow.":B".$currow);
							// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_center);
							$sheet->getStyle("A".$currow.":E".$currow)->getFont()->setBold(true);
							$sheet->getStyle("A".$currow.":E".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
							$sheet->getStyle('C'.$currow.':E'.$currow)->getNumberFormat()->setFormatCode('#,##0');
							$currow++;
							$sheet->mergeCells('A'.$currow.':E'.$currow);
						}
						$total_toko_faktur += $total_faktur;
						$total_toko_terbayar += $total_terbayar;
						$total_toko_sisa += $total_sisa;
						
					}
						
					$html.='<tr style="background:#'.$warna_toko.'">';
					$html.='<td class="td-bold" colspan="2">TOTAL [JT '.date('d-M-Y',strtotime($tgljatuhtempo)).'] ['.$nama_wilayah.']</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_toko_faktur).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_toko_terbayar).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_toko_sisa).'</td>';
					$html.='</tr>';
					$html.='<tr><td colspan="5">&nbsp;</td></tr>';
					
					if($this->excel_flag == 1){
						$currow++;
						$curcol = 1;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL [JT '.date('d-M-Y',strtotime($tgljatuhtempo)).'] ['.$nama_wilayah.']');
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_toko_faktur);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_toko_terbayar);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_toko_sisa);
						$sheet->mergeCells("A".$currow.":B".$currow);
						// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_center);
						$sheet->getStyle("A".$currow.":E".$currow)->getFont()->setBold(true);
						$sheet->getStyle("A".$currow.":E".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
						$sheet->getStyle('C'.$currow.':E'.$currow)->getNumberFormat()->setFormatCode('#,##0');
						$currow++;
						$sheet->mergeCells('A'.$currow.':E'.$currow);
					}
					$total_wilayah_faktur += $total_toko_faktur;
					$total_wilayah_terbayar += $total_toko_terbayar;
					$total_wilayah_sisa += $total_toko_sisa;
				}
						
				$html.='<tr style="background:#'.$warna_wilayah.'">';
				$html.='<td class="td-bold" colspan="2">TOTAL [JT '.date('d-M-Y',strtotime($tgljatuhtempo)).']</td>';
				$html.='<td class="td-right td-bold">'.number_format($total_wilayah_faktur).'</td>';
				$html.='<td class="td-right td-bold">'.number_format($total_wilayah_terbayar).'</td>';
				$html.='<td class="td-right td-bold">'.number_format($total_wilayah_sisa).'</td>';
				$html.='</tr>';
				$html.='<tr><td colspan="5">&nbsp;</td></tr>';
				
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL ['.date('d-M-Y',strtotime($tgljatuhtempo)).']');
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_wilayah_faktur);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_wilayah_terbayar);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_wilayah_sisa);
					$sheet->mergeCells("A".$currow.":B".$currow);
					// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_center);
					$sheet->getStyle("A".$currow.":E".$currow)->getFont()->setBold(true);
					$sheet->getStyle("A".$currow.":E".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_wilayah);
					$sheet->getStyle('C'.$currow.':E'.$currow)->getNumberFormat()->setFormatCode('#,##0');
					$currow++;
					$sheet->mergeCells('A'.$currow.':E'.$currow);
				}
				$total_jatuhtempo_faktur += $total_wilayah_faktur;
				$total_jatuhtempo_terbayar += $total_wilayah_terbayar;
				$total_jatuhtempo_sisa += $total_wilayah_sisa;
			}
			
					
			$html.='<tr style="background:#'.$warna_jatuhtempo.'">';
			$html.='<td class="td-bold" colspan="2">GRAND TOTAL</td>';
			$html.='<td class="td-right td-bold">'.number_format($total_jatuhtempo_faktur).'</td>';
			$html.='<td class="td-right td-bold">'.number_format($total_jatuhtempo_terbayar).'</td>';
			$html.='<td class="td-right td-bold">'.number_format($total_jatuhtempo_sisa).'</td>';
			$html.='</tr>';
			
			if($this->excel_flag == 1){
				$currow++;
				$curcol = 1;
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'GRAND TOTAL');
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_jatuhtempo_faktur);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_jatuhtempo_terbayar);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_jatuhtempo_sisa);
				$sheet->mergeCells("A".$currow.":B".$currow);
				// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_center);
				$sheet->getStyle("A".$currow.":E".$currow)->getFont()->setBold(true);
				$sheet->getStyle("A".$currow.":E".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_jatuhtempo);
				$sheet->getStyle('C'.$currow.':E'.$currow)->getNumberFormat()->setFormatCode('#,##0');
				$currow++;
				$sheet->mergeCells('A'.$currow.':E'.$currow);
			}
			$html.='</table>';
			$html.= "</div>";
			$html.= "</body></html>";
			
			if($this->excel_flag == 1){
				$styleArray = [
					'borders' => [
						'allBorders' => [
							'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
						],
					],
				];
				$sheet ->getStyle('A4:E'.$currow)->applyFromArray($styleArray);
				// $sheet->freezePane('A6');
				$sheet->setSelectedCell('A1');
				$filename=$nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 
				exit();
			}
			else{
				require_once __DIR__ . '\vendor\autoload.php';
				$mpdf = new \Mpdf\Mpdf(array(
					'mode' => '',
					'format' => 'A4',
					'default_font_size' => 8,
					'default_font' => 'tahoma',
					'orientation' => 'P'
				));
				$mpdf->defaultfooterline = 1;
				$mpdf->SetFooter('
				<table width="100%">
					<tr>
						<td>'.date('d-M-Y H:i:s').'</td>
						<td class="td-right">Halaman {PAGENO} / {nbpg}</td>
					</tr>
				</table>');
				$mpdf->WriteHTML($html);
				$mpdf->Output();
			
			}
			
			$data['title'] = $nama_laporan;
			$data['content_html'] = $html;
			
			$this->load->view('LaporanResultView',$data);
		}
		
		public function PreviewSisaFaktur5($post, $params)
		{
			$URL = $this->API_URL."/ReportFinance/AgingPerWilayah";
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($post),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $response; die;
			if($httpcode!=200){
				die('URL "'.$URL.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode);
			}
			
			$result = json_decode($response);
			if(COUNT($result->data)==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				die('Tidak ada data.');
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			
			$alignment_middle = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			$warna_jatuhtempo 	= '909090';
			$warna_wilayah	= 'A9A9A9';
			$warna_toko		= 'C0C0C0';
			$warna_total	= 'D3D3D3';
			
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			$nama_laporan = $this->laporan[$post['laporan']];
			$html = "<html>";
			$html .= "<head>";
			$html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
			
			$html .= "<style>
			*, h2{margin:0}
			body{font-family:'Calibri',Arial;}
			.table{padding:0;margin:0;border-collapse:collapse}
			.table td, .table th { border:0.5px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			$html.= "</head>";
			$html.= "<body>";
			$html.= "<div id='div_header' style='margin-bottom:10px;text-align:center'>";
			$html.= "<div><h2>".$nama_laporan."</h2></div>";
			$html.= "<table>
			<tr><td>PER TANGGAL </td><td class='td-bold'>: ".$post['tanggal']."  </td></tr>
			<tr><td>CABANG </td><td class='td-bold'>: ".$post['nama_cabang']."  </td></tr>
			<tr><td>PARTNER TYPE </td><td class='td-bold'>: ".implode(", ",$post['partner_type'])."  </td></tr>
			<tr><td>PRINT DATE </td><td class='td-bold'>: ".date('d-M-Y H:i:s')." </td></tr>
			</table>
			</div>";
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($nama_laporan, 0, 31));
				$sheet->getStyle('A1')->getFont()->setSize(12);
				
				$sheet->setCellValue('A1', $nama_laporan);
				$sheet->setCellValue('A2', 'PER TANGGAL : '.$post['tanggal']);
				$sheet->setCellValue('A3', 'CABANG : '.$post['nama_cabang']);
				$sheet->setCellValue('A4', 'PARTNER TYPE : '.implode(", ",$post['partner_type']));
				$sheet->setCellValue('A5', 'PRINT DATE : '.date('d-M-Y H:i:s'));
				
				$sheet->getStyle("A1:A5")->getFont()->setBold(true);
				$sheet->getStyle('A1:A5')->getAlignment()->setHorizontal($alignment_center);
			}
			
			$html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$html.= "<div style='clear:both'></div>";
			$html.= "<table class='table' style='font-size:10pt!important;width:100%'>";
			
			$currow = 5;
			$html.='<tr style="background:#'.$warna_total.'">';
			$html.='<th width="30px" rowspan="2">NO</th>';
			$html.='<th width="*" rowspan="2">WILAYAH</th>';
			
			if($this->excel_flag == 1){
				$curcol = 1;
				$currow++;
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO');
				$sheet->getColumnDimension('A')->setWidth(5);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'WILAYAH');
				$sheet->getColumnDimension('B')->setWidth(30);
				
				$sheet->mergeCells("A".$currow.":A".($currow+1));
				$sheet->mergeCells("B".$currow.":B".($currow+1));
			}
			
			for($i=1;$i<=($result->grup);$i++){
				$grup = (($i-1)*($post['range'])+1).' - '.($i*(($post['range'])));
				$html.='<th colspan="2">'.$grup.'</th>';
				
				if($this->excel_flag == 1){
					$sheet->mergeCells(PHPExcel_Cell::stringFromColumnIndex($curcol-1).$currow.":".PHPExcel_Cell::stringFromColumnIndex($curcol).$currow);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grup);
					$curcol++;
				}
			}
			
			$html.='<th colspan="2">TOTAL</th>';
			if($this->excel_flag == 1){
				$sheet->mergeCells(PHPExcel_Cell::stringFromColumnIndex($curcol-1).$currow.":".PHPExcel_Cell::stringFromColumnIndex($curcol).$currow);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL');
			}
			$html.='</tr>';
			$html.='<tr style="background:#'.$warna_total.'">';
			$total_sisa_t = array();
			$total_sisa = array();
			$currow++;
			$curcol = 3;
			for($i=1;$i<=($result->grup);$i++){
				$html.='<th width="10%">SISA(T)</th>';
				$html.='<th width="10%">SISA</th>';
				
				if($this->excel_flag == 1){
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($curcol-1))->setWidth(20);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'SISA(T)');
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($curcol-1))->setWidth(20);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'SISA');
				}
				
				$total_sisa_t[$i] = 0;
				$total_sisa[$i] = 0;
			}	
			$html.='<th width="10%">SISA(T)</th>';
			$html.='<th width="10%">SISA</th>';
					
			if($this->excel_flag == 1){
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($curcol-1))->setWidth(20);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'SISA(T)');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($curcol-1))->setWidth(20);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'SISA');
			}
			
			$html.='</tr>';
			
			$no = 0;
			foreach($result->data as $wilayah => $data){
				$no++;
				$html.='<tr>';
				$html.='<td class="td-center">'.$no.'</td>';
				$html.='<td class="">'.$wilayah.'</td>';
				
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $no);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $wilayah);	
				}
					
				$grup_sisa_t = 0;
				$grup_sisa = 0;
			
				for($i=1;$i<=($result->grup);$i++){
					
					$ada = 0;
					foreach($data as $row){
						if($row->GRUP==$i){
							$ada = 1;
							$html.='<td class="td-right">'.number_format($row->SISA_T).'</td>';
							$html.='<td class="td-right">'.number_format($row->SISA).'</td>';
							if($this->excel_flag == 1){
								$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->SISA_T);
								$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->SISA);
							}
							$grup_sisa_t += $row->SISA_T;
							$grup_sisa +=$row->SISA;
							
							$total_sisa_t[$i] += $row->SISA_T;
							$total_sisa[$i] += $row->SISA;
						}
					}
					if($ada==0){
						$html.='<td class="td-right">0</td>';
						$html.='<td class="td-right">0</td>';
						if($this->excel_flag == 1){
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, 0);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, 0);	
						}
					}
				}
				
				$html.='<td class="td-right">'.number_format($grup_sisa_t).'</td>';
				$html.='<td class="td-right">'.number_format($grup_sisa).'</td>';
				$html.='</tr>';
				
				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grup_sisa_t);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grup_sisa);
				}
			}
			
			$html.='<tr style="background:#'.$warna_toko.'">';
			$html.='<td class="td-center td-bold" colspan="2">TOTAL</td>';
			
			$currow++;
			$curcol=1;
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL');
				$curcol++;
			}
				
			$grandtotal_sisa_t = 0;
			$grandtotal_sisa = 0;
			for($i=1;$i<=($result->grup);$i++){
				$html.='<td class="td-right td-bold">'.number_format($total_sisa_t[$i]).'</td>';
				$html.='<td class="td-right td-bold">'.number_format($total_sisa[$i]).'</td>';
				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_sisa_t[$i]);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_sisa[$i]);
				}
				$grandtotal_sisa_t += $total_sisa_t[$i];
				$grandtotal_sisa += $total_sisa[$i];
			}
			$html.='<td class="td-right td-bold">'.number_format($grandtotal_sisa_t).'</td>';
			$html.='<td class="td-right td-bold">'.number_format($grandtotal_sisa).'</td>';
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_sisa_t);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_sisa);
			
			}
			$html.='</tr>';
			
			$highestColumn = $sheet->getHighestColumn();			
				
			$sheet->mergeCells('A1:'.$highestColumn.'1');
			$sheet->mergeCells('A2:'.$highestColumn.'2');
			$sheet->mergeCells('A3:'.$highestColumn.'3');
			$sheet->mergeCells('A4:'.$highestColumn.'4');
			$sheet->mergeCells('A5:'.$highestColumn.'5');
			
			$sheet->getStyle('A6:'.$highestColumn.'7')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A6:'.$highestColumn.'7')->getAlignment()->setVertical($alignment_middle);
			$sheet->getStyle('A6:'.$highestColumn.'7')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
			$sheet->getStyle('A6:'.$highestColumn.'7')->getFont()->setBold(true);
			$sheet->getStyle('C8:'.$highestColumn.$currow)->getNumberFormat()->setFormatCode('#,##0');
				
			$sheet->getStyle('A'.$currow.':'.$highestColumn.$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
			$sheet->getStyle('A'.$currow.':'.$highestColumn.$currow)->getFont()->setBold(true);
			
			$sheet->mergeCells('A'.$currow.':B'.$currow);
			$sheet->getStyle('A'.$currow.':B'.$currow)->getAlignment()->setHorizontal($alignment_center);
			
			
			$html.='</table>';
			$html.= "</div>";
			$html.= "</body></html>";
			
			if($this->excel_flag == 1){
				$styleArray = [
					'borders' => [
						'allBorders' => [
							'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
						],
					],
				];
				$sheet ->getStyle('A6:'.$highestColumn.$currow)->applyFromArray($styleArray);
				$sheet->setSelectedCell('A1');
				$filename=$nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 
				exit();
			}
			else{
				require_once __DIR__ . '\vendor\autoload.php';
				$mpdf = new \Mpdf\Mpdf(array(
					'mode' => '',
					'format' => 'A4',
					'default_font_size' => 8,
					'default_font' => 'tahoma',
					'orientation' => 'L'
				));
				$mpdf->defaultfooterline = 1;
				$mpdf->SetFooter('
				<table width="100%">
					<tr>
						<td>'.date('d-M-Y H:i:s').'</td>
						<td class="td-right">Halaman {PAGENO} / {nbpg}</td>
					</tr>
				</table>');
				$mpdf->WriteHTML($html);
				$mpdf->Output();
			
			}
			
			$data['title'] = $nama_laporan;
			$data['content_html'] = $html;
			
			$this->load->view('LaporanResultView',$data);
		}
		
		public function PreviewSisaFaktur6($post, $params)
		{
			$URL = $this->API_URL."/ReportFinance/AgingPerToko";
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($post),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $response; die;
			if($httpcode!=200){
				die('URL "'.$URL.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode);
			}
			
			$result = json_decode($response);
			if(COUNT($result->data)==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				die('Tidak ada data.');
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			
			$alignment_middle = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			$warna_jatuhtempo 	= '909090';
			$warna_wilayah	= 'A9A9A9';
			$warna_toko		= 'C0C0C0';
			$warna_total	= 'D3D3D3';
			
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			$nama_laporan = $this->laporan[$post['laporan']];
			$html = "<html>";
			$html .= "<head>";
			$html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
			
			$html .= "<style>
			*, h2{margin:0}
			body{font-family:'Calibri',Arial;}
			.table{padding:0;margin:0;border-collapse:collapse}
			.table td, .table th { border:0.5px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			$html.= "</head>";
			$html.= "<body>";
			$html.= "<div id='div_header' style='margin-bottom:10px;text-align:center'>";
			$html.= "<div><h2>".$nama_laporan."</h2></div>";
			$html.= "<table>
			<tr><td>PER TANGGAL </td><td class='td-bold'>: ".$post['tanggal']."  </td></tr>
			<tr><td>CABANG </td><td class='td-bold'>: ".$post['nama_cabang']."  </td></tr>
			<tr><td>PARTNER TYPE </td><td class='td-bold'>: ".implode(", ",$post['partner_type'])."</td></tr>
			<tr><td>PRINT DATE </td><td class='td-bold'>: ".date('d-M-Y H:i:s')."</td></tr>
			</table>
			</div>";
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($nama_laporan, 0, 31));
				$sheet->getStyle('A1')->getFont()->setSize(12);
				
				$sheet->setCellValue('A1', $nama_laporan);
				$sheet->setCellValue('A2', 'PER TANGGAL : '.$post['tanggal']);
				$sheet->setCellValue('A3', 'CABANG : '.$post['nama_cabang']);
				$sheet->setCellValue('A4', 'PARTNER TYPE : '.implode(", ",$post['partner_type']));
				$sheet->setCellValue('A5', 'PRINT DATE : '.date('d-M-Y H:i:s'));
				
				$sheet->getStyle("A1:A5")->getFont()->setBold(true);
				$sheet->getStyle('A1:A5')->getAlignment()->setHorizontal($alignment_center);
			}
			
			$html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$html.= "<div style='clear:both'></div>";
			$html.= "<table class='table' style='font-size:10pt!important;width:100%'>";
			
			$currow = 5;
			$html.='<tr style="background:#'.$warna_total.'">';
			$html.='<th width="30px" rowspan="2">NO</th>';
			$html.='<th width="100px" rowspan="2">WILAYAH</th>';
			$html.='<th width="*" rowspan="2">DEALER</th>';
			
			if($this->excel_flag == 1){
				$curcol = 1;
				$currow++;
				$sheet->getColumnDimension('A')->setWidth(5);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO');
				$sheet->getColumnDimension('B')->setWidth(20);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'WILAYAH');
				$sheet->getColumnDimension('C')->setWidth(40);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'DEALER');
				
				$sheet->mergeCells("A".$currow.":A".($currow+1));
				$sheet->mergeCells("B".$currow.":B".($currow+1));
				$sheet->mergeCells("C".$currow.":C".($currow+1));
			}
			
			for($i=1;$i<=($result->grup);$i++){
				$grup = (($i-1)*($post['range'])+1).' - '.($i*(($post['range'])));
				$html.='<th colspan="2">'.$grup.'</th>';
				
				if($this->excel_flag == 1){
					$sheet->mergeCells(PHPExcel_Cell::stringFromColumnIndex($curcol-1).$currow.":".PHPExcel_Cell::stringFromColumnIndex($curcol).$currow);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grup);
					$curcol++;
				}
			}
			
			$html.='<th colspan="2">TOTAL</th>';
			if($this->excel_flag == 1){
				$sheet->mergeCells(PHPExcel_Cell::stringFromColumnIndex($curcol-1).$currow.":".PHPExcel_Cell::stringFromColumnIndex($curcol).$currow);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL');
			}
			$html.='</tr>';
			$html.='<tr style="background:#'.$warna_total.'">';
			$total_sisa_t = array();
			$total_sisa = array();
			$currow++;
			$curcol = 4;
			for($i=1;$i<=($result->grup);$i++){
				$html.='<th width="100px">SISA(T)</th>';
				$html.='<th width="100px">SISA</th>';
				
				if($this->excel_flag == 1){
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($curcol-1))->setWidth(20);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'SISA(T)');
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($curcol-1))->setWidth(20);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'SISA');
				}
				
				$total_sisa_t[$i] = 0;
				$total_sisa[$i] = 0;
			}	
			$html.='<th width="100px">SISA(T)</th>';
			$html.='<th width="100px">SISA</th>';
					
			if($this->excel_flag == 1){
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($curcol-1))->setWidth(20);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'SISA(T)');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($curcol-1))->setWidth(20);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'SISA');
			}
			
			$html.='</tr>';
			
			$no = 0;
			foreach($result->data as $wilayah => $dealer){
				foreach($dealer as $nama_dealer => $data){
					$no++;
					$html.='<tr>';
					$html.='<td class="td-center">'.$no.'</td>';
					$html.='<td class="">'.$wilayah.'</td>';
					$html.='<td class="">'.$nama_dealer.'</td>';
					
					if($this->excel_flag == 1){
						$currow++;
						$curcol = 1;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $no);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $wilayah);	
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $nama_dealer);	
					}
					
					$grup_sisa_t = 0;
					$grup_sisa = 0;
						for($i=1;$i<=($result->grup);$i++){
							
							$ada = 0;
							foreach($data as $row){
								if($row->GRUP==$i){
									$ada = 1;
									$html.='<td class="td-right">'.number_format($row->SISA_T).'</td>';
									$html.='<td class="td-right">'.number_format($row->SISA).'</td>';
									if($this->excel_flag == 1){
										$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->SISA_T);
										$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->SISA);
									}
									$grup_sisa_t += $row->SISA_T;
									$grup_sisa +=$row->SISA;
									
									
									$total_sisa_t[$i] += $row->SISA_T;
									$total_sisa[$i] += $row->SISA;
								}
							}
							if($ada==0){
								$html.='<td class="td-right">0</td>';
								$html.='<td class="td-right">0</td>';
								if($this->excel_flag == 1){
									$sheet->setCellValueByColumnAndRow($curcol++, $currow, 0);
									$sheet->setCellValueByColumnAndRow($curcol++, $currow, 0);	
								}
							}
						}
					
					$html.='<td class="td-right">'.number_format($grup_sisa_t).'</td>';
					$html.='<td class="td-right">'.number_format($grup_sisa).'</td>';
					$html.='</tr>';
					
					if($this->excel_flag == 1){
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grup_sisa_t);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grup_sisa);
					}
				}
			}
			
			
			$html.='<tr style="background:#'.$warna_toko.'">';
			$html.='<td class="td-center td-bold" colspan="3">TOTAL</td>';
			
			$currow++;
			$curcol=1;
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL');
				$curcol++;
				$curcol++;
			}
				
			$grandtotal_sisa_t = 0;
			$grandtotal_sisa = 0;
			for($i=1;$i<=($result->grup);$i++){
				$html.='<td class="td-right td-bold">'.number_format($total_sisa_t[$i]).'</td>';
				$html.='<td class="td-right td-bold">'.number_format($total_sisa[$i]).'</td>';
				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_sisa_t[$i]);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_sisa[$i]);
				}
				$grandtotal_sisa_t += $total_sisa_t[$i];
				$grandtotal_sisa += $total_sisa[$i];
			}
			$html.='<td class="td-right td-bold">'.number_format($grandtotal_sisa_t).'</td>';
			$html.='<td class="td-right td-bold">'.number_format($grandtotal_sisa).'</td>';
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_sisa_t);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_sisa);
			
			}
			$html.='</tr>';
			
			$highestColumn = $sheet->getHighestColumn();			
				
			$sheet->mergeCells('A1:'.$highestColumn.'1');
			$sheet->mergeCells('A2:'.$highestColumn.'2');
			$sheet->mergeCells('A3:'.$highestColumn.'3');
			$sheet->mergeCells('A4:'.$highestColumn.'4');
			$sheet->mergeCells('A5:'.$highestColumn.'5');
			$sheet->getStyle('A6:'.$highestColumn.'7')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A6:'.$highestColumn.'7')->getAlignment()->setVertical($alignment_middle);
			$sheet->getStyle('A6:'.$highestColumn.'7')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
			$sheet->getStyle('A6:'.$highestColumn.'7')->getFont()->setBold(true);
			$sheet->getStyle('D8:'.$highestColumn.$currow)->getNumberFormat()->setFormatCode('#,##0');
				
			$sheet->getStyle('A'.$currow.':'.$highestColumn.$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
			$sheet->getStyle('A'.$currow.':'.$highestColumn.$currow)->getFont()->setBold(true);
			
			$sheet->mergeCells('A'.$currow.':C'.$currow);
			$sheet->getStyle('A'.$currow.':C'.$currow)->getAlignment()->setHorizontal($alignment_center);
			
			
			$html.='</table>';
			$html.= "</div>";
			$html.= "</body></html>";
			
			if($this->excel_flag == 1){
				$styleArray = [
					'borders' => [
						'allBorders' => [
							'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
						],
					],
				];
				$sheet ->getStyle('A6:'.$highestColumn.$currow)->applyFromArray($styleArray);
				$sheet->setSelectedCell('A1');
				$filename=$nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 
				exit();
			}
			else{
				require_once __DIR__ . '\vendor\autoload.php';
				$mpdf = new \Mpdf\Mpdf(array(
					'mode' => '',
					'format' => 'Legal',
					'default_font_size' => 8,
					'default_font' => 'tahoma',
					'orientation' => 'L'
				));
				$mpdf->defaultfooterline = 1;
				$mpdf->SetFooter('
				<table width="100%">
					<tr>
						<td>'.date('d-M-Y H:i:s').'</td>
						<td class="td-right">Halaman {PAGENO} / {nbpg}</td>
					</tr>
				</table>');
				$mpdf->WriteHTML($html);
				$mpdf->Output();
			
			}
			
			$data['title'] = $nama_laporan;
			$data['content_html'] = $html;
			
			$this->load->view('LaporanResultView',$data);
		}
		
		public function PreviewSisaFaktur7($post, $params)
		{
			$URL = $this->API_URL."/ReportFinance/AgingPerFaktur";
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($post),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $response; die;
			if($httpcode!=200){
				die('URL "'.$URL.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode);
			}
			
			$result = json_decode($response);
			if(COUNT($result->data)==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				die('Tidak ada data.');
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			
			$alignment_middle = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			$warna_jatuhtempo 	= '909090';
			$warna_wilayah	= 'A9A9A9';
			$warna_toko		= 'C0C0C0';
			$warna_total	= 'D3D3D3';
			
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			$nama_laporan = $this->laporan[$post['laporan']];
			$html = "<html>";
			$html .= "<head>";
			$html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
			
			$html .= "<style>
			*, h2{margin:0}
			body{font-family:'Calibri',Arial;}
			.table{padding:0;margin:0;border-collapse:collapse}
			.table td, .table th { border:0.5px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			$html.= "</head>";
			$html.= "<body>";
			$html.= "<div id='div_header' style='margin-bottom:10px;text-align:center'>";
			$html.= "<div><h2>".$nama_laporan."</h2></div>";
			$html.= "<table>
			<tr><td>PER TANGGAL </td><td class='td-bold'>: ".$post['tanggal']."  </td></tr>
			<tr><td>CABANG </td><td class='td-bold'>: ".$post['nama_cabang']."  </td></tr>
			<tr><td>PARTNER TYPE </td><td class='td-bold'>: ".implode(", ",$post['partner_type'])."</td></tr>
			<tr><td>PRINT DATE </td><td class='td-bold'>: ".date('d-M-Y H:i:s')."</td></tr>
			</table>
			</div>";
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($nama_laporan, 0, 31));
				$sheet->getStyle('A1')->getFont()->setSize(12);
				
				$sheet->setCellValue('A1', $nama_laporan);
				$sheet->setCellValue('A2', 'PER TANGGAL : '.$post['tanggal']);
				$sheet->setCellValue('A3', 'CABANG : '.$post['nama_cabang']);
				$sheet->setCellValue('A4', 'PARTNER TYPE : '.implode(", ",$post['partner_type']));
				$sheet->setCellValue('A5', 'PRINT DATE : '.date('d-M-Y H:i:s'));
				
				$sheet->getStyle("A1:A5")->getFont()->setBold(true);
				$sheet->getStyle('A1:A5')->getAlignment()->setHorizontal($alignment_center);
			}
			
			$html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$html.= "<div style='clear:both'></div>";
			$html.= "<table class='table' style='font-size:10pt!important;width:100%'>";
			
			$currow = 5;
			$html.='<tr style="background:#'.$warna_total.'">';
			$html.='<th width="30px" rowspan="2">NO</th>';
			$html.='<th width="100px" rowspan="2">WILAYAH</th>';
			$html.='<th width="*" rowspan="2">DEALER</th>';
			$html.='<th width="80px" rowspan="2">NO FAKTUR</th>';
			
			if($this->excel_flag == 1){
				$curcol = 1;
				$currow++;
				$sheet->getColumnDimension('A')->setWidth(5);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO');
				$sheet->getColumnDimension('B')->setWidth(20);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'WILAYAH');
				$sheet->getColumnDimension('C')->setWidth(40);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'DEALER');
				$sheet->getColumnDimension('D')->setWidth(25);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO FAKTUR');
				
				$sheet->mergeCells("A".$currow.":A".($currow+1));
				$sheet->mergeCells("B".$currow.":B".($currow+1));
				$sheet->mergeCells("C".$currow.":C".($currow+1));
				$sheet->mergeCells("D".$currow.":D".($currow+1));
			}
			
			for($i=1;$i<=($result->grup);$i++){
				$grup = (($i-1)*($post['range'])+1).' - '.($i*(($post['range'])));
				$html.='<th colspan="2">'.$grup.'</th>';
				
				if($this->excel_flag == 1){
					$sheet->mergeCells(PHPExcel_Cell::stringFromColumnIndex($curcol-1).$currow.":".PHPExcel_Cell::stringFromColumnIndex($curcol).$currow);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grup);
					$curcol++;
				}
			}
			
			$html.='<th colspan="2">TOTAL</th>';
			if($this->excel_flag == 1){
				$sheet->mergeCells(PHPExcel_Cell::stringFromColumnIndex($curcol-1).$currow.":".PHPExcel_Cell::stringFromColumnIndex($curcol).$currow);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL');
			}
			$html.='</tr>';
			$html.='<tr style="background:#'.$warna_total.'">';
			$total_sisa_t = array();
			$total_sisa = array();
			$currow++;
			$curcol = 5;
			for($i=1;$i<=($result->grup);$i++){
				$html.='<th width="100px">SISA(T)</th>';
				$html.='<th width="100px">SISA</th>';
				
				if($this->excel_flag == 1){
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($curcol-1))->setWidth(15);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'SISA(T)');
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($curcol-1))->setWidth(15);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'SISA');
				}
				
				$total_sisa_t[$i] = 0;
				$total_sisa[$i] = 0;
			}	
			$html.='<th width="100px">SISA(T)</th>';
			$html.='<th width="100px">SISA</th>';
					
			if($this->excel_flag == 1){
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($curcol-1))->setWidth(15);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'SISA(T)');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($curcol-1))->setWidth(15);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'SISA');
			}
			
			$html.='</tr>';
			
			$no = 0;
			foreach($result->data as $wilayah => $dealer){
				foreach($dealer as $nama_dealer => $faktur){
					foreach($faktur as $no_faktur => $data){
						$no++;
						$html.='<tr>';
						$html.='<td class="td-center">'.$no.'</td>';
						$html.='<td class="">'.$wilayah.'</td>';
						$html.='<td class="">'.$nama_dealer.'</td>';
						$html.='<td class="">'.$no_faktur.'</td>';
						
						if($this->excel_flag == 1){
							$currow++;
							$curcol = 1;
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $no);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $wilayah);	
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $nama_dealer);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $no_faktur);
						}
						
						$grup_sisa_t = 0;
						$grup_sisa = 0;
							for($i=1;$i<=($result->grup);$i++){
								
								$ada = 0;
								foreach($data as $row){
									if($row->GRUP==$i){
										$ada = 1;
										$html.='<td class="td-right">'.number_format($row->SISA_T).'</td>';
										$html.='<td class="td-right">'.number_format($row->SISA).'</td>';
										if($this->excel_flag == 1){
											$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->SISA_T);
											$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->SISA);
										}
										$grup_sisa_t += $row->SISA_T;
										$grup_sisa +=$row->SISA;
										
										
										$total_sisa_t[$i] += $row->SISA_T;
										$total_sisa[$i] += $row->SISA;
									}
								}
								if($ada==0){
									$html.='<td class="td-right">0</td>';
									$html.='<td class="td-right">0</td>';
									if($this->excel_flag == 1){
										$sheet->setCellValueByColumnAndRow($curcol++, $currow, 0);
										$sheet->setCellValueByColumnAndRow($curcol++, $currow, 0);	
									}
								}
							}
						
						$html.='<td class="td-right">'.number_format($grup_sisa_t).'</td>';
						$html.='<td class="td-right">'.number_format($grup_sisa).'</td>';
						$html.='</tr>';
						
						if($this->excel_flag == 1){
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grup_sisa_t);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grup_sisa);
						}
					}
				}
			}
			
			
			$html.='<tr style="background:#'.$warna_toko.'">';
			$html.='<td class="td-center td-bold" colspan="4">TOTAL</td>';
			
			$currow++;
			$curcol=1;
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL');
				$curcol++;
				$curcol++;
				$curcol++;
			}
				
			$grandtotal_sisa_t = 0;
			$grandtotal_sisa = 0;
			for($i=1;$i<=($result->grup);$i++){
				$html.='<td class="td-right td-bold">'.number_format($total_sisa_t[$i]).'</td>';
				$html.='<td class="td-right td-bold">'.number_format($total_sisa[$i]).'</td>';
				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_sisa_t[$i]);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_sisa[$i]);
				}
				$grandtotal_sisa_t += $total_sisa_t[$i];
				$grandtotal_sisa += $total_sisa[$i];
			}
			$html.='<td class="td-right td-bold">'.number_format($grandtotal_sisa_t).'</td>';
			$html.='<td class="td-right td-bold">'.number_format($grandtotal_sisa).'</td>';
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_sisa_t);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_sisa);
			
			}
			$html.='</tr>';
			
			$highestColumn = $sheet->getHighestColumn();			
				
			$sheet->mergeCells('A1:'.$highestColumn.'1');
			$sheet->mergeCells('A2:'.$highestColumn.'2');
			$sheet->mergeCells('A3:'.$highestColumn.'3');
			$sheet->mergeCells('A4:'.$highestColumn.'4');
			$sheet->mergeCells('A5:'.$highestColumn.'5');
			
			$sheet->getStyle('A6:'.$highestColumn.'7')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A6:'.$highestColumn.'7')->getAlignment()->setVertical($alignment_middle);
			$sheet->getStyle('A6:'.$highestColumn.'7')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
			$sheet->getStyle('A6:'.$highestColumn.'7')->getFont()->setBold(true);
			$sheet->getStyle('D8:'.$highestColumn.$currow)->getNumberFormat()->setFormatCode('#,##0');
				
			$sheet->getStyle('A'.$currow.':'.$highestColumn.$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
			$sheet->getStyle('A'.$currow.':'.$highestColumn.$currow)->getFont()->setBold(true);
			
			$sheet->mergeCells('A'.$currow.':D'.$currow);
			$sheet->getStyle('A'.$currow.':D'.$currow)->getAlignment()->setHorizontal($alignment_center);
			
			
			$html.='</table>';
			$html.= "</div>";
			$html.= "</body></html>";
			
			if($this->excel_flag == 1){
				$styleArray = [
					'borders' => [
						'allBorders' => [
							'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
						],
					],
				];
				$sheet ->getStyle('A6:'.$highestColumn.$currow)->applyFromArray($styleArray);
				$sheet->setSelectedCell('A1');
				$filename=$nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 
				exit();
			}
			else{
				require_once __DIR__ . '\vendor\autoload.php';
				$mpdf = new \Mpdf\Mpdf(array(
					'mode' => '',
					'format' => 'Legal',
					'default_font_size' => 8,
					'default_font' => 'tahoma',
					'orientation' => 'L'
				));
				$mpdf->defaultfooterline = 1;
				$mpdf->SetFooter('
				<table width="100%">
					<tr>
						<td>'.date('d-M-Y H:i:s').'</td>
						<td class="td-right">Halaman {PAGENO} / {nbpg}</td>
					</tr>
				</table>');
				$mpdf->WriteHTML($html);
				$mpdf->Output();
			
			}
			
			$data['title'] = $nama_laporan;
			$data['content_html'] = $html;
			
			$this->load->view('LaporanResultView',$data);
		}
		
    }


