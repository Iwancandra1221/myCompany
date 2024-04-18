
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class Reportjualreturparentdivsummary extends MY_Controller 
	{
        public function __construct()
		{
			parent::__construct();
			$this->load->model('HelperModel');
			$this->load->helper('FormLibrary');
			$this->load->library('excel');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		}


        public function Reportjualminreturparentdivsummary () {
            $data = array();
			$api = 'APITES';
			
			set_time_limit(60);

			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "REPORT JUAL MIN RETUR PARENT DIV SUMMARY";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU REPORT JUAL MIN RETUR PARENT DIV SUMMARY";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);
           
            // print_r($this->API_URL."/ReportBass/GetListCabang?api=".$api."&basspusat=".$BassPusat."&kodecabang=".$KodeCabang);
		    // die;

            $listparentdiv = json_decode(file_get_contents($this->API_URL."/MsDivisi/GetListParentDiv?api=".$api));	
			$data["listparentdiv"] = $listparentdiv;

            $listpartnertype = json_decode(file_get_contents($this->API_URL."/MsPartnerType/GetListPartnerType?api=".$api));	
			$data["listpartnertype"] = $listpartnertype;

            $listwilayah = json_decode(file_get_contents($this->API_URL."/MsWilayah/GetListWilayah_ReportOPJ?api=".$api));	
			$data["listwilayah"] = $listwilayah;

            $listtipefaktur = json_decode(file_get_contents($this->API_URL."/TipeFaktur/GetsList2?api=".$api));	
			$data["listtipefaktur"] = $listtipefaktur;

			// print_r($listwilayah);
			// print_r($listtipefaktur);
            // die;

			$data['title'] = 'Laporan Jual Min Retur Parent Divisi Summary | '.WEBTITLE;
						
			$this->RenderView('Reportjualreturparentdivsummaryview',$data);
        }

        public function Reportjualreturparentdivsummary_Proses() {
			$page_title = 'Report Jual Min Retur Parent Divisi Summary';
			$api = 'APITES';

			set_time_limit(60);
					
            $tgl1 = date_format(date_create($_POST["dp1"]),'m-d-Y');
			$tgl2 = date_format(date_create($_POST["dp2"]),'m-d-Y');
            $parentdiv = $_POST["parentdiv"];
			$partnertype = $_POST["partnertype"];
			$wilayah = $_POST["wilayah"];
            $kategoribrg = $_POST["kategoribrg"];
            $tipefaktur = $_POST["tipefaktur"];

            if (empty($_POST["perkategoriinsentif"]) ){ 
				$perkategoriinsentif = "N";
			}
			else {
				$perkategoriinsentif = $_POST["perkategoriinsentif"];
			}

            $report = $_POST["report"];

			if (empty($_POST["btnPreview"]) ){ 
				$proses="EXCEL";
			}
			else {
				$proses="PREVIEW";
			}
			
			$mainUrl = $_SESSION["conn"]->AlamatWebService. API_BKT;

			// print_r($mainUrl."/Reportjualreturparentdivsummary/Reportjualreturparentdivsummary_Proses?api=".$api
            // ."&page_title=".urlencode($page_title)															
            // ."&tgl1=".urlencode($tgl1)
            // ."&tgl2=".urlencode($tgl2)
            // ."&parentdiv=".urlencode($parentdiv)
            // ."&partnertype=".urlencode($partnertype)
            // ."&wilayah=".urlencode($wilayah)
            // ."&kategoribrg=".urlencode($kategoribrg)
            // ."&tipefaktur=".urlencode($tipefaktur)
            // ."&perkategoriinsentif=".urlencode($perkategoriinsentif)
            // ."&report=".urlencode($report));
			// die;		

			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "REPORT JUAL MIN RETUR PARENT DIV SUMMARY";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES REPORT JUAL MIN RETUR PARENT DIV SUMMARY KODE REPORT ".$_POST["report"]." PERIODE ".date("d-M-Y", strtotime($_POST["dp1"]))." S/D ".date("d-M-Y", strtotime($_POST["dp2"]));
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);
           
			
			$url = $mainUrl."/Reportjualreturparentdivsummary/Reportjualreturparentdivsummary_Proses?api=".$api
						."&page_title=".urlencode($page_title)															
						."&tgl1=".urlencode($tgl1)
                        ."&tgl2=".urlencode($tgl2)
                        ."&parentdiv=".urlencode($parentdiv)
                        ."&partnertype=".urlencode($partnertype)
						."&wilayah=".urlencode($wilayah)
						."&kategoribrg=".urlencode($kategoribrg)
						."&tipefaktur=".urlencode($tipefaktur)
						."&perkategoriinsentif=".urlencode($perkategoriinsentif)
						."&report=".urlencode($report);

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
                $periode = "Periode " .$tgl1. " S/D " .$tgl2;		
				$parentdiv = "Parent Divisi : " .$parentdiv;
                $partnertype = "Partner Type : " .$partnertype;
				$wilayah = "Wilayah : " .$wilayah;
				$kategoribrg = "Kategori Barang : " .$kategoribrg;
                $tipefaktur = "Tipe Faktur : " .$tipefaktur;
                $perkategoriinsentif = "Per Kategori Insentif : " .$perkategoriinsentif;
							

                //  <option value="0">LAPORAN PER PARENT DIVISI PER WILAYAH</option>
                //  <option value="1">LAPORAN PER WILAYAH PER PARENT DIVISI </option>

				// 	<option value="2">LAPORAN PER DEALER SUMMARY</option>

				// 	<option value="3">LAPORAN PER PARENT DIVISI PER PARTNER TYPE</option>
				// 	<option value="4">LAPORAN PER PARTNER TYPE PER PARENT DIVISI </option>
				
				$judul = "LAPORAN JUAL MIN RETUR SUMMARY";

                if ( $report == "0" || $report == "1" ) {
                    if ( $report == "0") {
                        $judul .= " PER PARENT DIVISI PER WILAYAH";
                    }
                    else {
                        $judul .= " PER WILAYAH PER PARENT DIVISI";
                    }                    

                    if ($proses=="EXCEL"){				
                        $this->Reportjualminreturparentdivsummary01_Excel ( $page_title, $datalaporan, $judul, $periode, $parentdiv, $partnertype, $wilayah, $kategoribrg, $tipefaktur, $perkategoriinsentif, $report, $params);
                    }
                    else {
                        $this->Reportjualminreturparentdivsummary01_Preview ( $page_title, $datalaporan, $judul, $periode, $parentdiv, $partnertype, $wilayah, $kategoribrg, $tipefaktur, $perkategoriinsentif, $report, $params);
                    }
                }
                
                else if ( $report == "2") {
                    $judul .= " PER DEALER SUMMARY";

                    if ($proses=="EXCEL"){				
                        $this->Reportjualminreturparentdivsummary2_Excel ( $page_title, $datalaporan, $judul, $periode, $parentdiv, $partnertype, $wilayah, $kategoribrg, $tipefaktur, $perkategoriinsentif, $report, $params);
                    }
                    else {
                        $this->Reportjualminreturparentdivsummary2_Preview ( $page_title, $datalaporan, $judul, $periode, $parentdiv, $partnertype, $wilayah, $kategoribrg, $tipefaktur, $perkategoriinsentif, $report, $params);
                    }
                }
                else if ( $report == "3" || $report == "4" ) {
                    if ( $report == "3") {
                        $judul .= " PER PARENT DIVISI PER PARTNER TYPE";
                    }
                    else {
                        $judul .= " PER PARTNER TYPE PER PARENT DIVISI";
                    }      
                    
                    if ($proses=="EXCEL"){				
                        $this->Reportjualminreturparentdivsummary34_Excel ( $page_title, $datalaporan, $judul, $periode, $parentdiv, $partnertype, $wilayah, $kategoribrg, $tipefaktur, $perkategoriinsentif, $report, $params);
                    }
                    else {
                        $this->Reportjualminreturparentdivsummary34_Preview ( $page_title, $datalaporan, $judul, $periode, $parentdiv, $partnertype, $wilayah, $kategoribrg, $tipefaktur, $perkategoriinsentif, $report, $params);
                    }
                }                
			}
        }


        public function Reportjualminreturparentdivsummary01_Preview ( $page_title, $datalaporan, $judul, $periode, $parentdiv, $partnertype, $wilayah, $kategoribrg, $tipefaktur, $perkategoriinsentif, $report, $params) {
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
            $xGroup = "!@#$%^&*";		

            $TotalJual_PartnerType = 0;
            $TotalRetur_PartnerType = 0;
            $Total_PartnerType = 0;

            $TotalJual_Group = 0;
            $TotalRetur_Group = 0;
            $Total_Group = 0;

            $GTotalJual = 0;
            $GTotalRetur = 0;
            $GTotal = 0;


			// $content_html. = "<style> body { font-size:9pt; } </style>";
			$content_html.= "<html><body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;padding-left:1px;'>";
			$content_html.= "	<div><b>".$judul."</b></div>";			
			$content_html.= "	<div><b>".$periode."</b></div>";	
            $content_html.= "	<div><b>".$parentdiv."</b></div>";	
            $content_html.= "	<div><b>".$partnertype."</b></div>";	
			$content_html.= "	<div><b>".$wilayah."</b></div>";	
			$content_html.= "	<div><b>".$kategoribrg."</b></div>";	
			$content_html.= "	<div><b>".$tipefaktur."</b></div>";	
			$content_html.= "	<div><b>".$perkategoriinsentif."</b></div>";	

			$content_html.= "</div>";	//close div_header							

			$content_html.= "<div class='div_body' style='overflow-x:padding-left:1px;'>";
			$content_html.= "	<table style='width:100%'>";			

			// PARTNER_TYPE, WILAYAH, PARENTDIV, TOTAL_JUAL, TOTAL_RETUR, TOTAL
			
			$jml=count($datalaporan->data);
			for($i=0;$i<$jml;$i++) {
				
				if ( $xPartnerType != $datalaporan->data[$i]->PARTNER_TYPE ) {
					
						// Total 
						if ($xPartnerType != "!@#$%^&*") {																

                            if ($report == "0" ) {
                                $content_html.= "		<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL PARENT DIVISI : ".$xGroup." </b></td>";
                            }
                            else {
                                $content_html.= "		<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL WILAYAH : ".$xGroup." </b></td>";
                            }
							$content_html.= "	<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalJual_Group)."</b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalRetur_Group)."</b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($Total_Group)."</b></td>
                                                </tr>
												<tr><td>&nbsp;</td></tr>
                            ";	

							$content_html.= "	<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL PARTNER TYPE : ".$xPartnerType." </b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalJual_PartnerType)."</b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalRetur_PartnerType)."</b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($Total_PartnerType)."</b></td>
                                                </tr>
												<tr><td>&nbsp;</td></tr>
                            ";	
	
                            $TotalJual_PartnerType = 0;
                            $TotalRetur_PartnerType = 0;
                            $Total_PartnerType = 0;
                                    
                            $TotalJual_Group = 0;
                            $TotalRetur_Group = 0;
                            $Total_Group = 0;
						}
	
						// Sub 
						$content_html.= "		<tr><td colspan='4'><b> PARTNER TYPE : ".$datalaporan->data[$i]->PARTNER_TYPE."</b></td></tr>";

                        if ($report == "0" ) {
                            $content_html.= "		<tr><td colspan='4'><b> PARENT DIVISI : ".$datalaporan->data[$i]->PARENTDIV."</b></td></tr>";
                        }
                        else {
                            $content_html.= "		<tr><td colspan='4'><b> WILAYAH : ".$datalaporan->data[$i]->WILAYAH."</b></td></tr>";
                        }
						
						// Header
						$content_html.= "	<tr>";

                        if ($report == "0" ) {
                            $content_html.= "		<td style='width:25%; border-bottom:thin solid #333; border-top:thin solid #333;'>WILAYAH</td>";
                        }
                        else {
                            $content_html.= "		<td style='width:25%; border-bottom:thin solid #333; border-top:thin solid #333;'>PARENT DIVISI</td>";
                        }

						$content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TOTAL JUAL</td>";
						$content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TOTAL RETUR</td>";
						$content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TOTAL</td>";
						$content_html.= "	</tr>";		
				}

				else if ( ($report == "0" && $xGroup != $datalaporan->data[$i]->PARENTDIV) || ($report == "1" && $xGroup != $datalaporan->data[$i]->WILAYAH) ) {
					if ($xGroup != "!@#$%^&*") {		
							
						if ($report == "0" ) {
                            $content_html.= "		<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL PARENT DIVISI : ".$xGroup." </b></td>";
                        }
                        else {
                            $content_html.= "		<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL WILAYAH : ".$xGroup." </b></td>";
                        }
                        $content_html.= "	<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalJual_Group)."</b></td>
                                            <td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalRetur_Group)."</b></td>
                                            <td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($Total_Group)."</b></td>
                                            </tr>
                                            <tr><td>&nbsp;</td></tr>
                        ";		

						$TotalJual_Group = 0;
                        $TotalRetur_Group = 0;
                        $Total_Group = 0;
					}

					// Sub 
					if ($report == "0" ) {
                        $content_html.= "		<tr><td colspan='4'><b> PARENT DIVISI : ".$datalaporan->data[$i]->PARENTDIV."</b></td></tr>";
                    }
                    else {
                        $content_html.= "		<tr><td colspan='4'><b> WILAYAH : ".$datalaporan->data[$i]->WILAYAH."</b></td></tr>";
                    }
					

					// Header
                    $content_html.= "	<tr>";

                    if ($report == "0" ) {
                        $content_html.= "		<td style='width:25%; border-bottom:thin solid #333; border-top:thin solid #333;'>WILAYAH</td>";
                    }
                    else {
                        $content_html.= "		<td style='width:25%; border-bottom:thin solid #333; border-top:thin solid #333;'>PARENT DIVISI</td>";
                    }

                    $content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TOTAL JUAL</td>";
                    $content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TOTAL RETUR</td>";
                    $content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TOTAL</td>";
                    $content_html.= "	</tr>";		

				}

				// PARTNER_TYPE, WILAYAH, PARENTDIV, TOTAL_JUAL, TOTAL_RETUR, TOTAL

				// nobukti			
                if ($report == "0" ) {
                    $content_html.= "		<tr><td>".$datalaporan->data[$i]->WILAYAH."</td>";
                }
                else {
                    $content_html.= "		<tr><td>".$datalaporan->data[$i]->PARENTDIV."</td>";
                }

				$content_html.= "		<td align='right'>".number_format($datalaporan->data[$i]->TOTAL_JUAL)."</td>";
				$content_html.= "		<td align='right'>".number_format($datalaporan->data[$i]->TOTAL_RETUR)."</td>";	
                $content_html.= "		<td align='right'>".number_format($datalaporan->data[$i]->TOTAL)."</td>";	
				$content_html.= "	</tr>";


                $TotalJual_PartnerType += $datalaporan->data[$i]->TOTAL_JUAL;
                $TotalRetur_PartnerType += $datalaporan->data[$i]->TOTAL_RETUR;
                $Total_PartnerType += $datalaporan->data[$i]->TOTAL;

                $TotalJual_Group += $datalaporan->data[$i]->TOTAL_JUAL;
                $TotalRetur_Group += $datalaporan->data[$i]->TOTAL_RETUR;
                $Total_Group += $datalaporan->data[$i]->TOTAL;

                $GTotalJual += $datalaporan->data[$i]->TOTAL_JUAL;
                $GTotalRetur += $datalaporan->data[$i]->TOTAL_RETUR;
                $GTotal += $datalaporan->data[$i]->TOTAL;
					
				$xPartnerType = $datalaporan->data[$i]->PARTNER_TYPE;

                if ($report == "0" ) {
                    $xGroup = $datalaporan->data[$i]->PARENTDIV;
                }
                else {
                    $xGroup = $datalaporan->data[$i]->WILAYAH;
                }				
				
			}		
											
			// Total 							
			if ($report == "0" ) {
                $content_html.= "		<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL PARENT DIVISI : ".$xGroup." </b></td>";
            }
            else {
                $content_html.= "		<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL WILAYAH : ".$xGroup." </b></td>";
            }
            $content_html.= "	<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalJual_Group)."</b></td>
                                <td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalRetur_Group)."</b></td>
                                <td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($Total_Group)."</b></td>
                                </tr>
                                <tr><td>&nbsp;</td></tr>
            ";		

			$content_html.= "	<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL PARTNER TYPE : ".$xPartnerType." </b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalJual_PartnerType)."</b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalRetur_PartnerType)."</b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($Total_PartnerType)."</b></td>
                                </tr>
								<tr><td>&nbsp;</td></tr>
            ";	

            $content_html.= "	<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>GRAND TOTAL </b></td>
                            <td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($GTotalJual)."</b></td>
                            <td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($GTotalRetur)."</b></td>
                            <td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($GTotal)."</b></td>
                            </tr>
                            <tr><td>&nbsp;</td></tr>
            ";	
		
			$content_html.= "</table>";
			$content_html.= "</body></html>";


			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);			

			// echo $content_html;
			set_time_limit(60);
			$mpdf->SetHTMLHeader($header_html,'','1');
			$mpdf->WriteHTML($content_html);
			$mpdf->Output();

			// $this->Pdf_Report($header_html, $content_html, "","","","","");

			// $data['title'] = $page_title;
			// $data['content_html'] = $content_html;
			// $this->RenderView('ReportFinanceBBTResult',$data);
        }

        public function Reportjualminreturparentdivsummary2_Preview ( $page_title, $datalaporan, $judul, $periode, $parentdiv, $partnertype, $wilayah, $kategoribrg, $tipefaktur, $perkategoriinsentif, $report, $params) {
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
            $xGroup = "!@#$%^&*";		

            $TotalJual_PartnerType = 0;
            $TotalRetur_PartnerType = 0;
            $Total_PartnerType = 0;

            $TotalJual_Group = 0;
            $TotalRetur_Group = 0;
            $Total_Group = 0;

            $GTotalJual = 0;
            $GTotalRetur = 0;
            $GTotal = 0;


			// $content_html. = "<style> body { font-size:9pt; } </style>";
			$content_html.= "<html><body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;padding-left:1px;'>";
			$content_html.= "	<div><b>".$judul."</b></div>";			
			$content_html.= "	<div><b>".$periode."</b></div>";	
            // $content_html.= "	<div><b>".$parentdiv."</b></div>";	
            $content_html.= "	<div><b>".$partnertype."</b></div>";	
			$content_html.= "	<div><b>".$wilayah."</b></div>";	
			$content_html.= "	<div><b>".$kategoribrg."</b></div>";	
			$content_html.= "	<div><b>".$tipefaktur."</b></div>";	
			$content_html.= "	<div><b>".$perkategoriinsentif."</b></div>";	

			$content_html.= "</div>";	//close div_header							

			$content_html.= "<div class='div_body' style='overflow-x:padding-left:1px;'>";
			$content_html.= "	<table style='width:100%'>";			

			// PARTNER_TYPE, WILAYAH, KD_PLG, NM_PLG, TOTAL_JUAL, TOTAL_RETUR, TOTAL 		

			$jml=count($datalaporan->data);
			for($i=0;$i<$jml;$i++) {
				
				if ( $xPartnerType != $datalaporan->data[$i]->PARTNER_TYPE ) {
					
						// Total 
						if ($xPartnerType != "!@#$%^&*") {																

							$content_html.= "	<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL WILAYAH : ".$xGroup." </b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalJual_Group)."</b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalRetur_Group)."</b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($Total_Group)."</b></td>
                                                </tr>
												<tr><td>&nbsp;</td></tr>
                            ";	

							$content_html.= "	<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL PARTNER TYPE : ".$xPartnerType." </b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalJual_PartnerType)."</b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalRetur_PartnerType)."</b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($Total_PartnerType)."</b></td>
                                                </tr>
												<tr><td>&nbsp;</td></tr>
                            ";	
	
                            $TotalJual_PartnerType = 0;
                            $TotalRetur_PartnerType = 0;
                            $Total_PartnerType = 0;
                                    
                            $TotalJual_Group = 0;
                            $TotalRetur_Group = 0;
                            $Total_Group = 0;
						}
	
						// Sub 
						$content_html.= "		<tr><td colspan='4'><b> PARTNER TYPE : ".$datalaporan->data[$i]->PARTNER_TYPE."</b></td></tr>";

                        $content_html.= "		<tr><td colspan='4'><b> WILAYAH : ".$datalaporan->data[$i]->WILAYAH."</b></td></tr>";
                        
						
						// Header
						$content_html.= "	<tr>";
                        
                        $content_html.= "		<td style='width:25%; border-bottom:thin solid #333; border-top:thin solid #333;'>DEALER</td>";
                        $content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TOTAL JUAL</td>";
						$content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TOTAL RETUR</td>";
						$content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TOTAL</td>";
						$content_html.= "	</tr>";		
				}

				else if ( $xGroup != $datalaporan->data[$i]->WILAYAH ) {
					if ($xGroup != "!@#$%^&*") {		
							
						$content_html.= "	<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL WILAYAH : ".$xGroup." </b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalJual_Group)."</b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalRetur_Group)."</b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($Total_Group)."</b></td>
                                            </tr>
											<tr><td>&nbsp;</td></tr>
                        ";		

						$TotalJual_Group = 0;
                        $TotalRetur_Group = 0;
                        $Total_Group = 0;
					}

					// Sub 
					$content_html.= "		<tr><td colspan='4'><b> WILAYAH : ".$datalaporan->data[$i]->WILAYAH."</b></td></tr>";
                    					

					// Header
                    $content_html.= "	<tr>";
                    $content_html.= "		<td style='width:25%; border-bottom:thin solid #333; border-top:thin solid #333;'>DEALER</td>";
                    $content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TOTAL JUAL</td>";
                    $content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TOTAL RETUR</td>";
                    $content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TOTAL</td>";
                    $content_html.= "	</tr>";		

				}

				// PARTNER_TYPE, WILAYAH, KD_PLG, NM_PLG, TOTAL_JUAL, TOTAL_RETUR, TOTAL 

				// nobukti			
                $content_html.= "		<tr><td>".$datalaporan->data[$i]->KD_PLG." - ".$datalaporan->data[$i]->NM_PLG."</td>";
                $content_html.= "		<td align='right'>".number_format($datalaporan->data[$i]->TOTAL_JUAL)."</td>";
				$content_html.= "		<td align='right'>".number_format($datalaporan->data[$i]->TOTAL_RETUR)."</td>";	
                $content_html.= "		<td align='right'>".number_format($datalaporan->data[$i]->TOTAL)."</td>";	
				$content_html.= "	</tr>";


                $TotalJual_PartnerType += $datalaporan->data[$i]->TOTAL_JUAL;
                $TotalRetur_PartnerType += $datalaporan->data[$i]->TOTAL_RETUR;
                $Total_PartnerType += $datalaporan->data[$i]->TOTAL;

                $TotalJual_Group += $datalaporan->data[$i]->TOTAL_JUAL;
                $TotalRetur_Group += $datalaporan->data[$i]->TOTAL_RETUR;
                $Total_Group += $datalaporan->data[$i]->TOTAL;

                $GTotalJual += $datalaporan->data[$i]->TOTAL_JUAL;
                $GTotalRetur += $datalaporan->data[$i]->TOTAL_RETUR;
                $GTotal += $datalaporan->data[$i]->TOTAL;
					
				$xPartnerType = $datalaporan->data[$i]->PARTNER_TYPE;
                $xGroup = $datalaporan->data[$i]->WILAYAH;               		
				
			}		
											
			// Total 							
			$content_html.= "	<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL WILAYAH : ".$xGroup." </b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalJual_Group)."</b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalRetur_Group)."</b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($Total_Group)."</b></td>
                                </tr>
								<tr><td>&nbsp;</td></tr>
            ";	

			$content_html.= "	<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL PARTNER TYPE : ".$xPartnerType." </b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalJual_PartnerType)."</b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalRetur_PartnerType)."</b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($Total_PartnerType)."</b></td>
                                </tr>
								<tr><td>&nbsp;</td></tr>
            ";	

            $content_html.= "	<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>GRAND TOTAL </b></td>
                            <td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($GTotalJual)."</b></td>
                            <td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($GTotalRetur)."</b></td>
                            <td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($GTotal)."</b></td>
                            </tr>
                            <tr><td>&nbsp;</td></tr>
            ";	
		
			$content_html.= "</table>";
			$content_html.= "</body></html>";

			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			// echo $content_html;
			set_time_limit(60);
			$mpdf->SetHTMLHeader($header_html,'','1');
			$mpdf->WriteHTML($content_html);
			$mpdf->Output();

			// $this->Pdf_Report($header_html, $content_html, "","","","","");

			// $data['title'] = $page_title;
			// $data['content_html'] = $content_html;
			// $this->RenderView('ReportFinanceBBTResult',$data);
        }

        public function Reportjualminreturparentdivsummary34_Preview ( $page_title, $datalaporan, $judul, $periode, $parentdiv, $partnertype, $wilayah, $kategoribrg, $tipefaktur, $perkategoriinsentif, $report, $params) {
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

			// $xPartnerType = "!@#$%^&*";
            $xGroup = "!@#$%^&*";		

            // $TotalJual_PartnerType = 0;
            // $TotalRetur_PartnerType = 0;
            // $Total_PartnerType = 0;

            $TotalJual_Group = 0;
            $TotalRetur_Group = 0;
            $Total_Group = 0;

            $GTotalJual = 0;
            $GTotalRetur = 0;
            $GTotal = 0;


			// $content_html. = "<style> body { font-size:9pt; } </style>";
			$content_html.= "<html><body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;padding-left:1px;'>";
			$content_html.= "	<div><b>".$judul."</b></div>";			
			$content_html.= "	<div><b>".$periode."</b></div>";	
            $content_html.= "	<div><b>".$parentdiv."</b></div>";	
            $content_html.= "	<div><b>".$partnertype."</b></div>";	
			$content_html.= "	<div><b>".$wilayah."</b></div>";	
			$content_html.= "	<div><b>".$kategoribrg."</b></div>";	
			$content_html.= "	<div><b>".$tipefaktur."</b></div>";	
			$content_html.= "	<div><b>".$perkategoriinsentif."</b></div>";	

			$content_html.= "</div>";	//close div_header							

			$content_html.= "<div class='div_body' style='overflow-x:padding-left:1px;'>";
			$content_html.= "	<table style='width:100%'>";			

			// PARTNER_TYPE, WILAYAH, PARENTDIV, TOTAL_JUAL, TOTAL_RETUR, TOTAL
			
			$jml=count($datalaporan->data);
			for($i=0;$i<$jml;$i++) {
				
				if ( ($report == "3" && $xGroup != $datalaporan->data[$i]->PARENTDIV) || ($report == "4" && $xGroup != $datalaporan->data[$i]->PARTNER_TYPE) ) {
					if ($xGroup != "!@#$%^&*") {		
							
                        if ($report == "3" ) {
                            $content_html.= "		<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL PARENT DIVISI : ".$xGroup." </b></td>";
                        }
                        else {
                            $content_html.= "		<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL PARTNER TYPE : ".$xGroup." </b></td>";
                        }

						$content_html.= "	    <td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalJual_Group)."</b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalRetur_Group)."</b></td>
												<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($Total_Group)."</b></td>
                                                </tr>
												<tr><td>&nbsp;</td></tr>
                        ";		

						$TotalJual_Group = 0;
                        $TotalRetur_Group = 0;
                        $Total_Group = 0;
					}

					// Sub 
					if ($report == "3" ) {
                        $content_html.= "		<tr><td colspan='4'><b> PARENT DIVISI : ".$datalaporan->data[$i]->PARENTDIV."</b></td></tr>";
                    }
                    else {
                        $content_html.= "		<tr><td colspan='4'><b> PARTNER TYPE : ".$datalaporan->data[$i]->PARTNER_TYPE."</b></td></tr>";
                    }
					

					// Header
                    $content_html.= "	<tr>";

                    if ($report == "3" ) {
                        $content_html.= "		<td style='width:25%; border-bottom:thin solid #333; border-top:thin solid #333;'>PARTNER TYPE</td>";
                    }
                    else {
                        $content_html.= "		<td style='width:25%; border-bottom:thin solid #333; border-top:thin solid #333;'>PARENT DIVISI</td>";
                    }

                    $content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TOTAL JUAL</td>";
                    $content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TOTAL RETUR</td>";
                    $content_html.= "		<td align='right' style='width:15%; border-bottom:thin solid #333; border-top:thin solid #333;'>TOTAL</td>";
                    $content_html.= "	</tr>";		

				}

				// PARTNER_TYPE, WILAYAH, PARENTDIV, TOTAL_JUAL, TOTAL_RETUR, TOTAL

				// nobukti			
                if ($report == "3" ) {
                    $content_html.= "		<tr><td>".$datalaporan->data[$i]->PARTNER_TYPE."</td>";
                }
                else {
                    $content_html.= "		<tr><td>".$datalaporan->data[$i]->PARENTDIV."</td>";
                }

				$content_html.= "		<td align='right'>".number_format($datalaporan->data[$i]->TOTAL_JUAL)."</td>";
				$content_html.= "		<td align='right'>".number_format($datalaporan->data[$i]->TOTAL_RETUR)."</td>";	
                $content_html.= "		<td align='right'>".number_format($datalaporan->data[$i]->TOTAL)."</td>";	
				$content_html.= "	</tr>";


                // $TotalJual_PartnerType += $datalaporan->data[$i]->TOTAL_JUAL;
                // $TotalRetur_PartnerType += $datalaporan->data[$i]->TOTAL_RETUR;
                // $Total_PartnerType += $datalaporan->data[$i]->TOTAL;

                $TotalJual_Group += $datalaporan->data[$i]->TOTAL_JUAL;
                $TotalRetur_Group += $datalaporan->data[$i]->TOTAL_RETUR;
                $Total_Group += $datalaporan->data[$i]->TOTAL;

                $GTotalJual += $datalaporan->data[$i]->TOTAL_JUAL;
                $GTotalRetur += $datalaporan->data[$i]->TOTAL_RETUR;
                $GTotal += $datalaporan->data[$i]->TOTAL;
					
				// $xPartnerType = $datalaporan->data[$i]->PARTNER_TYPE;

                if ($report == "3" ) {
                    $xGroup = $datalaporan->data[$i]->PARENTDIV;
                }
                else {
                    $xGroup = $datalaporan->data[$i]->PARTNER_TYPE;
                }				
				
			}		
											
			// Total 	
            if ($report == "3" ) {
                $content_html.= "		<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL PARENT DIVISI : ".$xGroup." </b></td>";
            }
            else {
                $content_html.= "		<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>TOTAL PARTNER TYPE : ".$xGroup." </b></td>";
            }						
			$content_html.= "	<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalJual_Group)."</b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($TotalRetur_Group)."</b></td>
								<td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($Total_Group)."</b></td>
                                </tr>
								<tr><td>&nbsp;</td></tr>
            ";				

            $content_html.= "	<tr><td colspan='1' align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>GRAND TOTAL </b></td>
                            <td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($GTotalJual)."</b></td>
                            <td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($GTotalRetur)."</b></td>
                            <td align='right' style='border-bottom:thin solid #333; border-top:thin solid #333;'><b>".number_format($GTotal)."</b></td>
                            </tr>
                            <tr><td>&nbsp;</td></tr>
            ";	
		
			$content_html.= "</table>";
			$content_html.= "</body></html>";

			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			// echo $content_html;
			set_time_limit(60);
			$mpdf->SetHTMLHeader($header_html,'','1');
			$mpdf->WriteHTML($content_html);
			$mpdf->Output();

			// $this->Pdf_Report($header_html, $content_html, "","","","","");

			// $data['title'] = $page_title;
			// $data['content_html'] = $content_html;
			// $this->RenderView('ReportFinanceBBTResult',$data);
        }


        public function Reportjualminreturparentdivsummary01_Excel ( $page_title, $datalaporan, $judul, $periode, $parentdiv, $partnertype, $wilayah, $kategoribrg, $tipefaktur, $perkategoriinsentif, $report, $params) {
            $spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', $judul);
			$sheet->getStyle('A1')->getFont()->setSize(20);
			
			$sheet->setCellValue('A2', $periode);	
            $sheet->setCellValue('A3', $parentdiv);	
            $sheet->setCellValue('A4', $partnertype);	
			$sheet->setCellValue('A5', $wilayah);	
			$sheet->setCellValue('A6', $kategoribrg);	
			$sheet->setCellValue('A7', $tipefaktur);	
			$sheet->setCellValue('A8', $perkategoriinsentif);	
		
            								
			$currcol = 1;
			$currrow = 10;							

			// PARTNER_TYPE, WILAYAH, PARENTDIV, TOTAL_JUAL, TOTAL_RETUR, TOTAL 

			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARTNER TYPE');
			$sheet->getColumnDimension('A')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;

            if ($report == "0") {
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARENT DIVISI');
                $sheet->getColumnDimension('B')->setWidth(15);
			    $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			    $currcol += 1;

                $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'WILAYAH');
                $sheet->getColumnDimension('C')->setWidth(15);
			    $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			    $currcol += 1;
            }
            else {
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'WILAYAH');
                $sheet->getColumnDimension('B')->setWidth(15);
			    $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			    $currcol += 1;

                $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARENT DIVISI');
                $sheet->getColumnDimension('C')->setWidth(15);
			    $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			    $currcol += 1;

            }
			
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL JUAL');
			$sheet->getColumnDimension('D')->setWidth(30);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL RETUR');
			$sheet->getColumnDimension('E')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
			$sheet->getColumnDimension('F')->setWidth(25);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;

			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
									
			// Detail
			$jum= count($datalaporan->data);
			for($i=0; $i<$jum; $i++){
				
				// PARTNER_TYPE, WILAYAH, PARENTDIV, TOTAL_JUAL, TOTAL_RETUR, TOTAL  
 

				$currrow++;
				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->PARTNER_TYPE);
				$currcol += 1;

                if ($report == "0") {
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->PARENTDIV);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->WILAYAH);
                    $currcol += 1;
                }
                else {
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->WILAYAH);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->PARENTDIV);
                    $currcol += 1;
                }
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->TOTAL_JUAL));
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$currcol += 1;				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->TOTAL_RETUR));
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$currcol += 1;	
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->TOTAL));
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
			
				
			$filename= $judul.' '.' ['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 

			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			exit();
        }

        public function Reportjualminreturparentdivsummary2_Excel ( $page_title, $datalaporan, $judul, $periode, $parentdiv, $partnertype, $wilayah, $kategoribrg, $tipefaktur, $perkategoriinsentif, $report, $params) {
            $spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', $judul);
			$sheet->getStyle('A1')->getFont()->setSize(20);
			
			$sheet->setCellValue('A2', $periode);	
            $sheet->setCellValue('A3', $parentdiv);	
            $sheet->setCellValue('A4', $partnertype);	
			$sheet->setCellValue('A5', $wilayah);	
			$sheet->setCellValue('A6', $kategoribrg);	
			$sheet->setCellValue('A7', $tipefaktur);	
			$sheet->setCellValue('A8', $perkategoriinsentif);	
		
            								
			$currcol = 1;
			$currrow = 10;							

			// PARTNER_TYPE, WILAYAH, KD_PLG, NM_PLG, TOTAL_JUAL, TOTAL_RETUR, TOTAL

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
            $sheet->getColumnDimension('C')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;            			
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL JUAL');
			$sheet->getColumnDimension('D')->setWidth(30);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL RETUR');
			$sheet->getColumnDimension('E')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
			$sheet->getColumnDimension('F')->setWidth(25);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;

			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
									
			// Detail
			$jum= count($datalaporan->data);
			for($i=0; $i<$jum; $i++){
				
				// PARTNER_TYPE, WILAYAH, KD_PLG, NM_PLG, TOTAL_JUAL, TOTAL_RETUR, TOTAL  
 
				$currrow++;
				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->PARTNER_TYPE);
				$currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->WILAYAH);
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->KD_PLG." - ".$datalaporan->data[$i]->NM_PLG);
                $currcol += 1;               
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->TOTAL_JUAL));
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$currcol += 1;				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->TOTAL_RETUR));
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$currcol += 1;	
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->TOTAL));
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
			
				
			$filename= $judul.' '.' ['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 

			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			exit();
        }

        public function Reportjualminreturparentdivsummary34_Excel ( $page_title, $datalaporan, $judul, $periode, $parentdiv, $partnertype, $wilayah, $kategoribrg, $tipefaktur, $perkategoriinsentif, $report, $params) {
            $spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', $judul);
			$sheet->getStyle('A1')->getFont()->setSize(20);
			
			$sheet->setCellValue('A2', $periode);	
            $sheet->setCellValue('A3', $parentdiv);	
            $sheet->setCellValue('A4', $partnertype);	
			$sheet->setCellValue('A5', $wilayah);	
			$sheet->setCellValue('A6', $kategoribrg);	
			$sheet->setCellValue('A7', $tipefaktur);	
			$sheet->setCellValue('A8', $perkategoriinsentif);	
		
            								
			$currcol = 1;
			$currrow = 10;							

			// PARTNER_TYPE, WILAYAH, PARENTDIV, TOTAL_JUAL, TOTAL_RETUR, TOTAL 

			// Header		
            if ($report == "3") {
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARENT DIVISI');
                $sheet->getColumnDimension('A')->setWidth(15);
			    $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			    $currcol += 1;

                $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARTNER TYPE');
                $sheet->getColumnDimension('B')->setWidth(15);
			    $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			    $currcol += 1;
            }
            else {
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARTNER TYPE');
                $sheet->getColumnDimension('A')->setWidth(15);
			    $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			    $currcol += 1;

                $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARENT DIVISI');
                $sheet->getColumnDimension('B')->setWidth(15);
			    $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			    $currcol += 1;
            }
			
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL JUAL');
			$sheet->getColumnDimension('C')->setWidth(30);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL RETUR');
			$sheet->getColumnDimension('D')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
			$sheet->getColumnDimension('E')->setWidth(25);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;

			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
									
			// Detail
			$jum= count($datalaporan->data);
			for($i=0; $i<$jum; $i++){
				
				// PARTNER_TYPE, WILAYAH, PARENTDIV, TOTAL_JUAL, TOTAL_RETUR, TOTAL
 
				$currrow++;
				$currcol = 1;
				
                if ($report == "3") {
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->PARENTDIV);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->PARTNER_TYPE);
                    $currcol += 1;
                }
                else {
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->PARTNER_TYPE);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->PARENTDIV);
                    $currcol += 1;
                }
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->TOTAL_JUAL));
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$currcol += 1;				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->TOTAL_RETUR));
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$currcol += 1;	
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->TOTAL));
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
			
				
			$filename= $judul.' '.' ['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 

			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			
			exit();
        }


    }
?>


