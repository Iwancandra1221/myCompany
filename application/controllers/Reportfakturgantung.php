<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class Reportfakturgantung extends MY_Controller 
	{
        public function __construct()
		{
			parent::__construct();
			$this->load->model('HelperModel');
			$this->load->helper('FormLibrary');
			$this->load->library('excel');
			$this->load->model('ConfigSysModel');
			$this->load->model('GzipDecodeModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		}


        public function Rekapfakturgantung () {
            $data = array();
			$api = 'APITES';
			
			set_time_limit(60);
			
			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "REKAP FAKTUR GANTUNG";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU REKAP FAKTUR GANTUNG";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);
           
            // print_r($this->API_URL."/ReportBass/GetListCabang?api=".$api."&basspusat=".$BassPusat."&kodecabang=".$KodeCabang);
		    // die;

            $listpartnertype = json_decode(file_get_contents($this->API_URL."/MsPartnerType/GetListPartnerType?api=".$api));	
			$data["listpartnertype"] = $listpartnertype;

            $listwilayah = json_decode(file_get_contents($this->API_URL."/MsWilayah/GetListWilayah_ReportOPJ?api=".$api));	
			$data["listwilayah"] = $listwilayah;

            $listsalesman = file_get_contents($this->API_URL."/MsSalesman/GetListSalesman_ReportOPJ?api=".$api);	
            $listsalesman = $this->GzipDecodeModel->_decodeGzip($listsalesman);
			$data["listsalesman"] = $listsalesman;

            $listdivisi = json_decode(file_get_contents($this->API_URL."/MsDivisi/GetListDivisionProduct?api=".$api));	
			$data["listdivisi"] = $listdivisi;

			$data['title'] = 'Laporan Faktur Gantung | '.WEBTITLE;
						
			$this->RenderView('Reportfakturgantungview',$data);

        }


        public function Reportfakturgantung_Proses() {
			$page_title = 'Report OPJ';
			$api = 'APITES';

			set_time_limit(60);
						
			$partnertype = $_POST["partnertype"];
			$wilayah = $_POST["wilayah"];

			$parts = explode("|", $_POST["salesman"]);
			$salesman = $parts[0];
			$nmsalesman = $parts[1];
		
			// nama toko | kd toko
			// $toko = $_POST["toko"];
			$parts2 = explode("|", $_POST["toko"]);
			$toko = $parts2[1];
			$nmtoko = $parts2[0];

			$divisi = $_POST["divisi"];
			$status = $_POST["status"];
			$hari = $_POST["hari"];

			if (empty($_POST["btnPreview"]) ){ 
				$proses="EXCEL";
			}
			else {
				$proses="PREVIEW";
			}
			
			$mainUrl = $_SESSION["conn"]->AlamatWebService. API_BKT;

			// print_r($mainUrl."/Reportfakturgantung/Reportfakturgantung_Proses?api=".$api
			// ."&page_title=".urlencode($page_title)															
			// ."&partnertype=".urlencode($partnertype)
			// ."&wilayah=".urlencode($wilayah)
			// ."&divisi=".urlencode($divisi)
			// ."&toko=".urlencode($toko)
			// ."&status=".urlencode($status)
			// ."&hari=".urlencode($hari));
			// die;		
			
			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "REKAP FAKTUR GANTUNG";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES REKAP FAKTUR GANTUNG";
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$url = $mainUrl."/Reportfakturgantung/Reportfakturgantung_Proses?api=".$api
						."&page_title=".urlencode($page_title)															
						."&partnertype=".urlencode($partnertype)
						."&wilayah=".urlencode($wilayah)
						."&salesman=".urlencode($salesman)
						."&divisi=".urlencode($divisi)
						."&toko=".urlencode($toko)
						."&status=".urlencode($status)
						."&hari=".urlencode($hari);

			// print_r($url);
            // die;

            // $datalaporan = json_decode(file_get_contents($url));

			$ch = curl_init();

			// Set URL yang akan diambil kontennya
			curl_setopt($ch, CURLOPT_URL, $url);

			// Set opsi untuk mengembalikan respons sebagai string
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			// Lakukan permintaan HTTP
			$output = curl_exec($ch);

			// Cek apakah permintaan berhasil atau tidak
			if(curl_errno($ch)) {
				echo 'Error: ' . curl_error($ch);
				// die;
			}

			// Tutup curl
			curl_close($ch);

			// print_r($output);
			// die;

			$datalaporan = json_decode(str_replace(',"error":"','}',str_replace(',"error":"}','}',$output)));
			// $datalaporan = json_decode($output);

			if (empty($datalaporan)) {
				$datalaporan = json_decode($output);
			}
			
			// print_r($datalaporan);
            // die;

			if (empty($datalaporan)) {

				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				echo "Tidak Ada Data !!!";
			}
			else {				
				$partnertype = "Partner Type : " .$partnertype;
				$wilayah = "Wilayah : " .$wilayah;

				if ($salesman != 'all') {
					$salesman = "Salesman : " .$salesman." - ".$nmsalesman;
				}
				else {
					$salesman = "Salesman : " .$salesman;
				}
				
				$divisi = "Divisi : " .$divisi;

				if ($salesman != 'all') {
					$nmtoko = "Toko : " .$toko." - ".$nmtoko;
				}
				else {
					$nmtoko = "Toko : " .$toko;
				}
				
				$sts = "Status : " .$status;
				$hari = "Jumlah Hari Telat Min : " .$hari;

				$judul = "Laporan Faktur Gantung / Belum Lunas";

				if ($proses=="EXCEL"){				
					$this->Reportfakturgantung_Excel ( $page_title, $datalaporan, $judul, $partnertype, $wilayah, $salesman, $divisi, $nmtoko, $sts, $hari, $params );
				}
				else {
					$this->Reportfakturgantung_Preview ( $page_title, $datalaporan, $judul, $partnertype, $wilayah, $salesman, $divisi, $nmtoko, $sts, $hari, $params );
				}
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
						'orientation' => 'P'

					));

			$mpdf->SetHTMLHeader($header);				//Yang diulang di setiap awal halaman  (Header)
			$mpdf->WriteHTML(utf8_encode($content));

			$mpdf->Output();
			
		}

		public function Reportfakturgantung_Preview ( $page_title, $datalaporan, $judul, $partnertype, $wilayah, $salesman, $divisi, $nmtoko, $sts, $hari, $params ) {
			//ini_set('max_execution_time', '1500');
			ini_set("pcre.backtrack_limit", "10000000");
			set_time_limit(60);

			$mpdf = new \Mpdf\Mpdf(array(
			'mode' => '',
			'format' => 'Legal',
			'default_font_size' => 8,
			'default_font' => 'arial',
			'margin_left' => 10,
			'margin_right' => 10,
			'margin_top' => 39,
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
			
			// print_r($datalaporan);
            // die;

			$header_html="";
			$content_html= "";

			$xPartnerType = "!@#$%^&*";
			$xWilayah = "!@#$%^&*";
			$xDealer = "!@#$%^&*";
			$gtotal_PartnerType = 0;
			$stotal_PartnerType = 0;
			$gtotal_Wilayah = 0;
			$stotal_Wilayah = 0;
			$gtotal_Dealer = 0;
			$stotal_Dealer = 0;
			$gtotal = 0;
			$stotal = 0;

			// $content_html. = "<style> body { font-size:9pt; } </style>";
			$content_html.= "<html><body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;padding-left:1px;'>";
			$content_html.= "	<div><b>".$judul."</b></div>";			
			$content_html.= "	<div><b>".$partnertype."</b></div>";	
			$content_html.= "	<div><b>".$wilayah."</b></div>";	
			$content_html.= "	<div><b>".$salesman."</b></div>";	
			$content_html.= "	<div><b>".$divisi."</b></div>";	
			$content_html.= "	<div><b>".$nmtoko."</b></div>";	
			$content_html.= "	<div><b>".$sts."</b></div>";	
			$content_html.= "	<div><b>".$hari."</b></div>";	

			$content_html.= "</div>";	//close div_header							

			$content_html.= "<div class='div_body' style='overflow-x:padding-left:1px;'>";
			$content_html.= "	<table style='width:100%'>";			

			// PARTNER_TYPE, WILAYAH, KD_PLG, NM_PLG, DIVISI, NO_FAKTUR, TGL_FAKTUR, 
			// 			TGL_JATUHTEMPO, LAMA_TELAT, GRANDTOTAL, SISA_FAKTUR 

			

			$jml=count($datalaporan->data);
			for($i=0;$i<$jml;$i++) {
				
				if ( $xPartnerType != $datalaporan->data[$i]->PARTNER_TYPE ) {
					
						// Total 
						if ($xPartnerType != "!@#$%^&*") {		
							
							$content_html.= "	<tr><td colspan='5' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL DEALER : ".$xDealer." </b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($gtotal_Dealer)."</b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($stotal_Dealer)."</b></td>
												</tr>
												<tr><td>&nbsp;</td></tr>";	

							$content_html.= "	<tr><td colspan='5' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL WILAYAH : ".$xWilayah." </b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($gtotal_Wilayah)."</b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($stotal_Wilayah)."</b></td>
												</tr>
												<tr><td>&nbsp;</td></tr>";	

							$content_html.= "	<tr><td colspan='5' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL PARTNER TYPE : ".$xPartnerType." </b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($gtotal_PartnerType)."</b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($stotal_PartnerType)."</b></td>
												</tr>
												<tr><td>&nbsp;</td></tr>";	
	
							$gtotal_PartnerType = 0;
							$stotal_PartnerType = 0;
							$gtotal_Wilayah = 0;
							$stotal_Wilayah = 0;
							$gtotal_Dealer = 0;
							$stotal_Dealer = 0;
						}
	
						// Sub 
						$content_html.= "		<tr><td colspan='7'><b> PARTNER TYPE : ".$datalaporan->data[$i]->PARTNER_TYPE."</b></td></tr>";

						$content_html.= "		<tr><td colspan='7'><b> WILAYAH : ".$datalaporan->data[$i]->WILAYAH."</b></td></tr>";

						$content_html.= "		<tr><td colspan='7'><b> DEALER : ".$datalaporan->data[$i]->NM_PLG."</b></td></tr>";


						// Header
						$content_html.= "	<tr>";
						$content_html.= "		<td style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TGL FAKTUR</td>";
						$content_html.= "		<td style='width:20%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO FAKTUR</td>";
						$content_html.= "		<td style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>DIVISI</td>";
						$content_html.= "		<td style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TGL JATUHTEMPO</td>";
						$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>SISA HARI</td>";
						$content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TOTAL FAKTUR</td>";
						$content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>SISA FAKTUR</td>";
						$content_html.= "	</tr>";		
				}
				else if ( $xWilayah != $datalaporan->data[$i]->WILAYAH ) {
					if ($xWilayah != "!@#$%^&*") {		
							
						$content_html.= "	<tr><td colspan='5' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL DEALER : ".$xDealer." </b></td>
											<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($gtotal_Dealer)."</b></td>
											<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($stotal_Dealer)."</b></td>
											</tr>
											<tr><td>&nbsp;</td></tr>";	

						$content_html.= "	<tr><td colspan='5' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL WILAYAH : ".$xWilayah." </b></td>
											<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($gtotal_Wilayah)."</b></td>
											<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($stotal_Wilayah)."</b></td>
											</tr>
											<tr><td>&nbsp;</td></tr>";	

						$gtotal_Wilayah = 0;
						$stotal_Wilayah = 0;
						$gtotal_Dealer = 0;
						$stotal_Dealer = 0;
					}

					// Sub 
					$content_html.= "		<tr><td colspan='7'><b> WILAYAH : ".$datalaporan->data[$i]->WILAYAH."</b></td></tr>";

					$content_html.= "		<tr><td colspan='7'><b> DEALER : ".$datalaporan->data[$i]->KD_PLG." - ".$datalaporan->data[$i]->NM_PLG."</b></td></tr>";


					// Header
					$content_html.= "	<tr>";
					$content_html.= "		<td style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TGL FAKTUR</td>";
					$content_html.= "		<td style='width:20%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO FAKTUR</td>";
					$content_html.= "		<td style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>DIVISI</td>";
					$content_html.= "		<td style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TGL JATUHTEMPO</td>";
					$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>SISA HARI</td>";
					$content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TOTAL FAKTUR</td>";
					$content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>SISA FAKTUR</td>";
					$content_html.= "	</tr>";		

				}
				else if ( $xDealer != $datalaporan->data[$i]->KD_PLG." - ".$datalaporan->data[$i]->NM_PLG ) {
					if ($xDealer != "!@#$%^&*") {		
							
						$content_html.= "	<tr><td colspan='5' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL DEALER : ".$xDealer." </b></td>
											<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($gtotal_Dealer)."</b></td>
											<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($stotal_Dealer)."</b></td>
											</tr>
											<tr><td>&nbsp;</td></tr>";	

						$gtotal_Dealer = 0;
						$stotal_Dealer = 0;
					}

					// Sub 
					$content_html.= "		<tr><td colspan='7'><b> DEALER : ".$datalaporan->data[$i]->KD_PLG." - ".$datalaporan->data[$i]->NM_PLG."</b></td></tr>";


					// Header
					$content_html.= "	<tr>";
					$content_html.= "		<td style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TGL FAKTUR</td>";
					$content_html.= "		<td style='width:20%; border-bottom:thin solid #333; border-top:thin solid #333;'>NO FAKTUR</td>";
					$content_html.= "		<td style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>DIVISI</td>";
					$content_html.= "		<td style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TGL JATUHTEMPO</td>";
					$content_html.= "		<td style='width:10%; border-bottom:thin solid #333; border-top:thin solid #333;'>SISA HARI</td>";
					$content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TOTAL FAKTUR</td>";
					$content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>SISA FAKTUR</td>";
					$content_html.= "	</tr>";		
				}

					// PARTNER_TYPE, WILAYAH, KD_PLG, NM_PLG, DIVISI, NO_FAKTUR, TGL_FAKTUR, 
					// 			TGL_JATUHTEMPO, LAMA_TELAT, GRANDTOTAL, SISA_FAKTUR 


				// nobukti					
				$content_html.= "	<tr><td>".date("d-m-Y",strtotime($datalaporan->data[$i]->TGL_FAKTUR))."</td>";
				$content_html.= "		<td>".$datalaporan->data[$i]->NO_FAKTUR."</td>";
				$content_html.= "		<td>".$datalaporan->data[$i]->DIVISI."</td>";	
				$content_html.= "		<td>".date("d-m-Y",strtotime($datalaporan->data[$i]->TGL_JATUHTEMPO))."</td>";
				$content_html.= "		<td>".$datalaporan->data[$i]->LAMA_TELAT."</td>";
				$content_html.= "		<td align='right'>".number_format($datalaporan->data[$i]->GRANDTOTAL)."</td>";
				$content_html.= "		<td align='right'>".number_format($datalaporan->data[$i]->SISA_FAKTUR)."</td>";	
				$content_html.= "	</tr>";

				$gtotal_PartnerType += $datalaporan->data[$i]->GRANDTOTAL;
				$stotal_PartnerType += $datalaporan->data[$i]->SISA_FAKTUR;
				$gtotal_Wilayah += $datalaporan->data[$i]->GRANDTOTAL;
				$stotal_Wilayah += $datalaporan->data[$i]->SISA_FAKTUR;
				$gtotal_Dealer += $datalaporan->data[$i]->GRANDTOTAL;
				$stotal_Dealer += $datalaporan->data[$i]->SISA_FAKTUR;
				$gtotal += $datalaporan->data[$i]->GRANDTOTAL;
				$stotal += $datalaporan->data[$i]->SISA_FAKTUR;
					
				$xPartnerType = $datalaporan->data[$i]->PARTNER_TYPE;
				$xWilayah = $datalaporan->data[$i]->WILAYAH;
				$xDealer = $datalaporan->data[$i]->KD_PLG." - ".$datalaporan->data[$i]->NM_PLG;
				
			}		
											
			// Total 										
			$content_html.= "	<tr><td colspan='5' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL DEALER : ".$xDealer." </b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($gtotal_Dealer)."</b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($stotal_Dealer)."</b></td>
								</tr>
								<tr><td>&nbsp;</td></tr>";	

			$content_html.= "	<tr><td colspan='5' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL WILAYAH : ".$xWilayah." </b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($gtotal_Wilayah)."</b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($stotal_Wilayah)."</b></td>
								</tr>
								<tr><td>&nbsp;</td></tr>";	

			$content_html.= "	<tr><td colspan='5' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL PARTNER TYPE : ".$xPartnerType." </b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($gtotal_PartnerType)."</b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($stotal_PartnerType)."</b></td>
								</tr>
								<tr><td>&nbsp;</td></tr>";	
		
			$content_html.= "</table>";
			$content_html.= "</body></html>";

			if ($jml==0){
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} else {
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			} 

			// echo $content_html;
			set_time_limit(60);
			$mpdf->SetHTMLHeader($header_html,'','1');
			$mpdf->WriteHTML($content_html);
			$mpdf->Output();

			// $this->Pdf_Report($header_html, $content_html, "","","","","");

			$data['title'] = $page_title;
			$data['content_html'] = $content_html;
			// $this->RenderView('ReportFinanceBBTResult',$data);


		}

		public function Reportfakturgantung_Excel ( $page_title, $datalaporan, $judul, $partnertype, $wilayah, $salesman, $divisi, $nmtoko, $sts, $hari, $params ) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', $judul);
			$sheet->getStyle('A1')->getFont()->setSize(20);
			
			$sheet->setCellValue('A2', $partnertype);	
			$sheet->setCellValue('A3', $wilayah);	
			$sheet->setCellValue('A4', $salesman);	
			$sheet->setCellValue('A5', $divisi);	
			$sheet->setCellValue('A6', $nmtoko);	
			$sheet->setCellValue('A7', $sts);	
			$sheet->setCellValue('A8', $hari);	
		
            								
			$currcol = 1;
			$currrow = 10;							

			// PARTNER_TYPE, WILAYAH, KD_PLG, NM_PLG, DIVISI, NO_FAKTUR, TGL_FAKTUR, 
					// 			TGL_JATUHTEMPO, LAMA_TELAT, GRANDTOTAL, SISA_FAKTUR 

			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARTNER TYPE');
			$sheet->getColumnDimension('A')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'WILAYAH');
			$sheet->getColumnDimension('B')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DEALER');
			$sheet->getColumnDimension('C')->setWidth(30);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL FAKTUR');
			$sheet->getColumnDimension('D')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO FAKTUR');
			$sheet->getColumnDimension('E')->setWidth(25);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DIVISI');
			$sheet->getColumnDimension('F')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL JATUHTEMPO');
			$sheet->getColumnDimension('G')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SISA HARI');
			$sheet->getColumnDimension('H')->setWidth(10);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL FAKTUR');
			$sheet->getColumnDimension('I')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SISA FAKTUR');
			$sheet->getColumnDimension('J')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;

			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
									
			// Detail
			$jum= count($datalaporan->data);
			for($i=0; $i<$jum; $i++){
				
				// PARTNER_TYPE, WILAYAH, KD_PLG, NM_PLG, DIVISI, NO_FAKTUR, TGL_FAKTUR, 
					// 			TGL_JATUHTEMPO, LAMA_TELAT, GRANDTOTAL, SISA_FAKTUR 
 

				$currrow++;
				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->PARTNER_TYPE);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->WILAYAH);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->KD_PLG." - ".$datalaporan->data[$i]->NM_PLG);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-m-Y",strtotime($datalaporan->data[$i]->TGL_FAKTUR)));
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_FAKTUR);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->DIVISI);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-m-Y",strtotime($datalaporan->data[$i]->TGL_JATUHTEMPO)));
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->LAMA_TELAT);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->GRANDTOTAL));
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$currcol += 1;				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->SISA_FAKTUR));
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$currcol += 1;				
			}
			
			// print_r ($judul);

			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A10:'.$max_col.'11')->getAlignment()->setHorizontal($alignment_center);
			
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A10:'.$max_col.'11')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
			// border
			$sheet->getStyle("A1:".$max_col."11")->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet->getStyle('A10:'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');
			
				
			$filename= $judul.' '.$sts. ' ['.date('Ymd').']'; //save our workbook as this file name
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



    }

?>

