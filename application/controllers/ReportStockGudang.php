<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


    class ReportStockGudang extends MY_Controller 
    {

        public function __construct()
        {
            parent::__construct();
            $this->load->model('HelperModel');
            $this->load->helper('FormLibrary');
            $this->load->library('excel');
        }

        public function index()
        {
            $data = array();

            $api = 'APITES';
            set_time_limit(60);
            
            $branch_id = $_SESSION['logged_in']['branch_id'];
            if ($branch_id == "JKT") { $branch_id = "DMI"; }
            $data["branch_id"] = $branch_id;

            $mainUrl = $_SESSION["conn"]->AlamatWebService . API_BKT;
            $data["mainurl"] = $mainUrl;

            $dbgudang = json_decode(file_get_contents($mainUrl."/MasterGudang/GetListDbGudang?api=".$api
                                                    ."&branch_id=".urlencode($branch_id)));
			$data["dbgudang"] = $dbgudang;
                       
            // print_r ($data["dbgudang"]);
            // die;
            
            $data['title'] = 'Laporan Stok Gudang | '.WEBTITLE;
                
            $this->RenderView('ReportStockGudangView',$data);

        }

        public function Proses()
		{						
			$page_title = 'ReportStockGudang';
			$api = 'APITES';

            // print_r($_POST["dp2"]);
            // die;

			$db_gudang = $_POST["dbgudang"];
			$mainUrl = $_SESSION["conn"]->AlamatWebService . API_BKT;
			$dbgudang = json_decode(file_get_contents($mainUrl."/MasterGudang/GetDbGudang?api=".$api
															."&kd_gudang=".urlencode($db_gudang)));
			$server = $dbgudang->data[0]->Server;
			$db = $dbgudang->data[0]->DB;

			$kategori_gudang = $_POST["kategori_gudang"];
			$tgl1 = $_POST["dp1"];
			$tgl2 = $_POST["dp2"];
			$divisi = $_POST["divisi"];
			$jenis_barang = $_POST["jenis_barang"];

			// print_r($tgl2);
            // die;

			$radgudang = $_POST["radgudang"];
			if ($radgudang == "gudang"){
				$gudang = $_POST["gudang"];
				$grupgudang = "";
				$grupgudang2 = "";
				$kd_gudang = $gudang;
			}
			else {
				$gudang = "";
				$kdgrupgudang = $_POST["grupgudang"];
				$kd_gudang = $kdgrupgudang;

				$dbgrupgudang = json_decode(file_get_contents($mainUrl."/MasterGudang/GetGrupGudang?api=".$api
															."&server=".urlencode($server)
															."&db=".urlencode($db)
															."&kdgrupgudang=".urlencode($kdgrupgudang)));
				$grupgudang = "";
				$grupgudang2 = "";

				$jum= count($dbgrupgudang->data);
				for($i=0; $i<$jum; $i++){	
					if ($grupgudang != ""){
						$grupgudang .= ", ";
						$grupgudang2 .= ", ";
					}				
					$grupgudang .= "'" .$dbgrupgudang->data[$i]->Kd_Gudang. "'";	
					$grupgudang2 .= "''" .$dbgrupgudang->data[$i]->Kd_Gudang. "''";					
				}	

				// print_r($grupgudang);
				// die;

			}

			if (empty($_POST["btnPreview"]) ){ 
				$proses="EXCEL";
			}
			else {
				$proses="PREVIEW";
			}

            if ($_POST["pilihanlaporan"] == "A" ){ 
				$laporan="A";                
			}
            elseif ($_POST["pilihanlaporan"] == "B" ){ 
                $laporan="B";	
			}
            elseif ($_POST["pilihanlaporan"] == "C" ){ 
				$laporan="C";
			}
            else{
                $laporan="D";
            }

            if ($laporan == "A") {
                $tanggalproses = "";
            }
            else {
                if ($_POST["tanggalproses"] == "tanggal_faktur" ){ 
                    $tanggalproses = "tanggal_faktur";
                }
                else {
                    $tanggalproses = "tanggal_keluar_barang ";   
                }
            }
            			
			if (empty($_POST["inctransferstok"]) ){ 
				$inctransferstok = "N";
			}
			else {
				$inctransferstok = $_POST["inctransferstok"];
			}

			// print_r($proses);
			// 	die;

			////Proses Data
			////A. Laporan Faktur yang Belum Dipotong PDA
			////B. Retur Barang
			////C. Detail Kartu Barang
			////D. Rekap Kartu Barang
			

			// print_r($mainUrl."/ReportStockGudang/ProsesLaporan?api=".$api
			// ."&laporan=".urlencode($laporan)
			// ."&server=".urlencode($server)
			// ."&db=".urlencode($db)
			// ."&page_title=".urlencode($page_title)
			// ."&kategori_gudang=".urlencode($kategori_gudang)
			// ."&tgl1=".urlencode($tgl1)
			// ."&tgl2=".urlencode($tgl2)
			// ."&divisi=".urlencode($divisi)
			// ."&jenis_barang=".urlencode($jenis_barang)
			// ."&radgudang=".urlencode($radgudang)
			// ."&gudang=".urlencode($gudang)
			// ."&grupgudang=".urlencode($grupgudang)
			// ."&grupgudang2=".urlencode($grupgudang2)
			// ."&tanggalproses=".urlencode($tanggalproses)
			// ."&inctransferstok=".urlencode($inctransferstok)
			// );
			// die;	

			set_time_limit(60);
            $datalaporan = json_decode(file_get_contents($mainUrl."/ReportStockGudang/ProsesLaporan?api=".$api
															."&laporan=".urlencode($laporan)
															."&server=".urlencode($server)
															."&db=".urlencode($db)
															."&page_title=".urlencode($page_title)
															."&kategori_gudang=".urlencode($kategori_gudang)
															."&tgl1=".urlencode($tgl1)
															."&tgl2=".urlencode($tgl2)
															."&divisi=".urlencode($divisi)
															."&jenis_barang=".urlencode($jenis_barang)
															."&radgudang=".urlencode($radgudang)
															."&gudang=".urlencode($gudang)
															."&grupgudang=".urlencode($grupgudang)
															."&grupgudang2=".urlencode($grupgudang2)
															."&tanggalproses=".urlencode($tanggalproses)
															."&inctransferstok=".urlencode($inctransferstok)
														));
            
			// print_r($datalaporan);
			// die;												

			//// Judul
			if ($laporan=="A") {
				if ($kategori_gudang=="PRODUK") {
					$judul = "Laporan Produk Faktur Yang Sudah Dicetak dan Belum Dipotong";
				}
				else {
					$judul = "Laporan Sparepart Faktur Yang Sudah Dicetak dan Belum Dipotong";
				}				
			}

			elseif ($laporan=="B") {		
				if ($kategori_gudang=="PRODUK") {
					if ( $tanggalproses == "tanggal_faktur") {
						if ($radgudang == "gudang"){
							$judul = "Laporan RETUR STOCK BARANG Sesuai Dengan Tanggal Faktur";			
						}
						else {
							$judul = "Laporan RETUR STOCK BARANG Gudang Gabungan Sesuai Dengan Tanggal Faktur";
						}	
					}
					else {
						if ($radgudang == "gudang"){
							$judul = "Laporan RETUR STOCK BARANG Sesuai Dengan Tanggal Keluar";			
						}
						else {
							$judul = "Laporan RETUR STOCK BARANG Gudang Gabungan Sesuai Dengan Tanggal Keluar";
						}	
					}					
				}
				else {
					if ( $tanggalproses == "tanggal_faktur") {
						$judul = "Laporan RETUR STOCK SPAREPART Sesuai Dengan Tanggal Faktur";
					}
					else {
						$judul = "Laporan RETUR STOCK SPAREPART Sesuai Dengan Tanggal Keluar";
					}					
				}
			}

			elseif ($laporan=="C") {
				if ($kategori_gudang=="PRODUK") {
					if ( $tanggalproses == "tanggal_faktur") {
						if ($radgudang == "gudang"){
							$judul = "Laporan KARTU STOCK BARANG Sesuai Dengan Tanggal Faktur";			
						}
						else {
							$judul = "Laporan KARTU STOCK BARANG Gudang Gabungan Sesuai Dengan Tanggal Faktur";
						}	
					}
					else {
						if ($radgudang == "gudang"){
							$judul = "Laporan KARTU STOCK BARANG Sesuai Dengan Tanggal Keluar";			
						}
						else {
							$judul = "Laporan KARTU STOCK BARANG Gudang Gabungan Sesuai Dengan Tanggal Keluar";
						}	
					}					
				}
				else {
					if ( $tanggalproses == "tanggal_faktur") {
						$judul = "Laporan KARTU STOCK SPAREPART Sesuai Dengan Tanggal Faktur";
					}
					else {
						$judul = "Laporan KARTU STOCK SPAREPART Sesuai Dengan Tanggal Keluar";
					}	
				}
			}
			
			else {
				if ($kategori_gudang=="PRODUK") {
					if ( $tanggalproses == "tanggal_faktur") {
						if ($radgudang == "gudang"){
							$judul = "Laporan REKAP KARTU STOCK BARANG Sesuai Dengan Tanggal Faktur";			
						}
						else {
							$judul = "Laporan REKAP KARTU STOCK BARANG Gudang Gabungan Sesuai Dengan Tanggal Faktur";
						}	
					}
					else {
						if ($radgudang == "gudang"){
							$judul = "Laporan REKAP KARTU STOCK BARANG Sesuai Dengan Tanggal Keluar";			
						}
						else {
							$judul = "Laporan REKAP KARTU STOCK BARANG Gudang Gabungan Sesuai Dengan Tanggal Keluar";
						}	
					}					
				}
				else {
					if ( $tanggalproses == "tanggal_faktur") {
						$judul = "Laporan REKAP KARTU STOCK SPAREPART Sesuai Dengan Tanggal Faktur";
					}
					else {
						$judul = "Laporan REKAP KARTU STOCK SPAREPART Sesuai Dengan Tanggal Keluar";
					}	
				}
			}  
		

			if ($proses=="PREVIEW") {
				if ($laporan=="A") {										
					$this->Preview_A ( $page_title, $datalaporan, $tgl1, $tgl2, $judul, $radgudang, $kd_gudang );
				}

				elseif ($laporan=="B") {					
					$this->Preview_B ( $page_title, $datalaporan, $tgl1, $tgl2, $judul, $radgudang, $kd_gudang );
				}
				elseif ($laporan=="C") {
					$this->Preview_C ( $page_title, $datalaporan, $tgl1, $tgl2, $judul, $radgudang, $kd_gudang );
				}
				else {
					$this->Preview_D ( $page_title, $datalaporan, $tgl1, $tgl2, $judul, $radgudang, $kd_gudang );
				}  
			}
			else {
				if ($laporan=="B") {
					$this->Excel_B ( $page_title, $datalaporan, $tgl1, $tgl2, $judul, $radgudang, $kd_gudang );
				}
				elseif ($laporan=="C") {
					$this->Excel_C ( $page_title, $datalaporan, $tgl1, $tgl2, $judul, $radgudang, $kd_gudang );
				}
				else {
					$this->Excel_D ( $page_title, $datalaporan, $tgl1, $tgl2, $judul, $radgudang, $kd_gudang );
				}  
			} 
		}


		public function Preview_A ( $page_title, $datalaporan, $tgl1, $tgl2, $judul, $radgudang, $kd_gudang ) {
			$style_col_ganjil ="float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#ffffcc;";
			$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_summary= "float:left;height:30px;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#cccccc;";
	
			$kanan= "text-align:right;padding-right:10px;";
			$kiri = "text-align:left; padding-left:10px;";
	
			$content_html = "<style> body { font-size:9pt; } </style>";
			$content_html.= "<html><body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;padding-left:10px;'>";
			$content_html.= "	<div><h2>" .$judul. "</h2></div>";			
			$content_html.= "	<div><b>Periode : ".$tgl1." - ".$tgl2."</b></div>";
			
			if ( $radgudang == "gudang") {
				$content_html.= "	<div><b>Gudang : ".$kd_gudang."</b></div>";
			}
			else {
				$content_html.= "	<div><b>Grup Gudang: ".$kd_gudang."</b></div>";
			}
			
			$content_html.= "</div>";	//close div_header


			$content_html.= "<div class='div_body' style='overflow-x:padding-left:10px;'>";
			$content_html.= "	<div id='div_column_header' style='width:1500px!important;line-height:90px;vertical-align:middle;'>";
			$content_html.= "		<div style='width:8%;".$style_summary.$kiri."height:60px!important;'><u>Gudang</u></div>";
			$content_html.= "		<div style='width:8%;".$style_summary.$kiri."height:60px!important;'><u>Tgl Trans</u></div>";
			$content_html.= "		<div style='width:15%;".$style_summary.$kiri."height:60px!important;'><u>No Bukti</u></div>";
			$content_html.= "		<div style='width:20%;".$style_summary.$kiri."height:60px!important;'><u>Pelanggan</u></div>";
			$content_html.= "		<div style='width:20%;".$style_summary.$kiri."height:60px!important;'><u>Barang</u></div>";
			$content_html.= "		<div style='width:5%;".$style_summary.$kanan."height:60px!important;'><u>Qty</u></div>";
			$content_html.= "	<div style='clear:both;'></div>";

			
			$jml=count($datalaporan->data);
			for($i=0;$i<$jml;$i++) {
				
				$content_html.= "			<div style='width:8%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->Kd_Gudang."</b></div>";
				$content_html.= "			<div style='width:8%;".$style_summary.$kiri."'><b>".date("d-m-Y",strtotime($datalaporan->data[$i]->Tgl_Trans))."</b></div>";
				$content_html.= "			<div style='width:15%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->No_Bukti."</b></div>";
				$content_html.= "			<div style='width:20%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->Nm_Plg."</b></div>";			
				$content_html.= "			<div style='width:20%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->Kd_Brg."</b></div>";
				$content_html.= "			<div style='width:5%;".$style_summary.$kanan."'><b>".number_format($datalaporan->data[$i]->Qty)."</b></div>";
				$content_html.= "	<div style='clear:both;'></div>";
			}

			$content_html.= "		</div>";
			$content_html.= "	</div>";
			$content_html.= "</div>";
			$content_html.= "</body></html>";


			$data['title'] = $page_title;
			$data['content_html'] = $content_html;
			$this->RenderView('ReportStockGudangResult',$data);
		}

		public function Preview_B ( $page_title, $datalaporan, $tgl1, $tgl2, $judul, $radgudang, $kd_gudang ) {
			$style_col_ganjil ="float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#ffffcc;";
			$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_summary= "float:left;height:30px;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#cccccc;";
	
			$kanan= "text-align:right;padding-right:10px;";
			$kiri = "text-align:left; padding-left:10px;";
	
			$content_html = "<style> body { font-size:9pt; } </style>";
			$content_html.= "<html><body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;padding-left:1px;'>";
			$content_html.= "	<div><h2>" .$judul. "</h2></div>";			
			$content_html.= "	<div><b>Periode : ".$tgl1." - ".$tgl2."</b></div>";
			
			if ( $radgudang == "gudang") {
				$content_html.= "	<div><b>Gudang : ".$kd_gudang."</b></div>";
			}
			else {
				$content_html.= "	<div><b>Grup Gudang: ".$kd_gudang."</b></div>";
			}
			
			$content_html.= "</div>";	//close div_header


			$content_html.= "<div class='div_body' style='overflow-x:padding-left:1px;'>";
			$content_html.= "	<div id='div_column_header' style='width:1500px!important;line-height:90px;vertical-align:middle;'>";
			$content_html.= "		<div style='width:8%;".$style_summary.$kiri."height:60px!important;'><u>Gudang</u></div>";
			$content_html.= "		<div style='width:8%;".$style_summary.$kiri."height:60px!important;'><u>Divisi</u></div>";
			$content_html.= "		<div style='width:8%;".$style_summary.$kiri."height:60px!important;'><u>Merk</u></div>";
			$content_html.= "		<div style='width:15%;".$style_summary.$kiri."height:60px!important;'><u>Barang</u></div>";
			$content_html.= "		<div style='width:10%;".$style_summary.$kiri."height:60px!important;'><u>Tanggal Faktur</u></div>";
			$content_html.= "		<div style='width:10%;".$style_summary.$kiri."height:60px!important;'><u>Tanggal Keluar</u></div>";
			$content_html.= "		<div style='width:10%;".$style_summary.$kiri."height:60px!important;'><u>No Bukti</u></div>";
			$content_html.= "		<div style='width:15%;".$style_summary.$kiri."height:60px!important;'><u>Dealer</u></div>";
			$content_html.= "		<div style='width:5%;".$style_summary.$kanan."height:60px!important;'><u>Qty</u></div>";
			$content_html.= "	<div style='clear:both;'></div>";

			
			$jml=count($datalaporan->data);
			for($i=0;$i<$jml;$i++) {
				
				$content_html.= "			<div style='width:8%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->kd_gudang."</b></div>";
				$content_html.= "			<div style='width:8%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->Divisi."</b></div>";
				$content_html.= "			<div style='width:8%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->Merk."</b></div>";	
				$content_html.= "			<div style='width:15%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->Kd_Brg."</b></div>";
				$content_html.= "			<div style='width:10%;".$style_summary.$kiri."'><b>".date("d-m-Y",strtotime($datalaporan->data[$i]->Tgl_Trans))."</b></div>";
				$content_html.= "			<div style='width:10%;".$style_summary.$kiri."'><b>".date("d-m-Y",strtotime($datalaporan->data[$i]->entry_time))."</b></div>";
				$content_html.= "			<div style='width:10%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->No_Bukti."</b></div>";
				$content_html.= "			<div style='width:15%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->Kd_Plg."</b></div>";			
				$content_html.= "			<div style='width:5%;".$style_summary.$kanan."'><b>".number_format($datalaporan->data[$i]->Qty)."</b></div>";
				$content_html.= "	<div style='clear:both;'></div>";

			}

			$content_html.= "		</div>";
			$content_html.= "	</div>";
			$content_html.= "</div>";
			$content_html.= "</body></html>";


			$data['title'] = $page_title;
			$data['content_html'] = $content_html;
			$this->RenderView('ReportStockGudangResult',$data);
		}

		public function Preview_C ( $page_title, $datalaporan, $tgl1, $tgl2, $judul, $radgudang, $kd_gudang ) {
			$style_col_ganjil ="float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#ffffcc;";
			$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_summary= "float:left;height:30px;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#cccccc;";
	
			$kanan= "text-align:right;padding-right:10px;";
			$kiri = "text-align:left; padding-left:10px;";
	
			$content_html = "<style> body { font-size:9pt; } </style>";
			$content_html.= "<html><body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;padding-left:1px;'>";
			$content_html.= "	<div><h2>" .$judul. "</h2></div>";			
			$content_html.= "	<div><b>Periode : ".$tgl1." - ".$tgl2."</b></div>";
			
			if ( $radgudang == "gudang") {
				$content_html.= "	<div><b>Gudang : ".$kd_gudang."</b></div>";
			}
			else {
				$content_html.= "	<div><b>Grup Gudang: ".$kd_gudang."</b></div>";
			}
			
			$content_html.= "</div>";	//close div_header


			$content_html.= "<div class='div_body' style='overflow-x:padding-left:1px;'>";
			$content_html.= "	<div id='div_column_header' style='width:1500px!important;line-height:90px;vertical-align:middle;'>";
			$content_html.= "		<div style='width:8%;".$style_summary.$kiri."height:60px!important;'><u>Gudang</u></div>";
			$content_html.= "		<div style='width:8%;".$style_summary.$kiri."height:60px!important;'><u>Divisi</u></div>";
			$content_html.= "		<div style='width:8%;".$style_summary.$kiri."height:60px!important;'><u>Merk</u></div>";
			$content_html.= "		<div style='width:15%;".$style_summary.$kiri."height:60px!important;'><u>Barang</u></div>";
			$content_html.= "		<div style='width:10%;".$style_summary.$kiri."height:60px!important;'><u>Tanggal Faktur</u></div>";
			$content_html.= "		<div style='width:10%;".$style_summary.$kiri."height:60px!important;'><u>Tanggal Keluar</u></div>";
			$content_html.= "		<div style='width:10%;".$style_summary.$kiri."height:60px!important;'><u>Dealer / Gudang</u></div>";
			$content_html.= "		<div style='width:5%;".$style_summary.$kanan."height:60px!important;'><u>Masuk</u></div>";
			$content_html.= "		<div style='width:5%;".$style_summary.$kanan."height:60px!important;'><u>Keluar</u></div>";
			$content_html.= "		<div style='width:5%;".$style_summary.$kanan."height:60px!important;'><u>Sisa</u></div>";
			$content_html.= "	<div style='clear:both;'></div>";


			$SubTotalMasuk = 0;
			$SubTotalKeluar = 0;
			$Sisa = 0;
			$Kd_Brg = "!@#$%";
			
			$jml=count($datalaporan->data);
			for($i=0;$i<$jml;$i++) {
				
				if ( $Kd_Brg != "!@#$%" ){
					if( $Kd_Brg != $datalaporan->data[$i]->Kd_Brg ) { 
						////CetakTotal
						$content_html.= "			<div style='width:69%;".$style_summary.$kiri."'></div>";
						$content_html.= "			<div style='width:15%;".$style_summary.$kanan."'><b>Masuk:".number_format($SubTotalMasuk)."     Keluar:".number_format($SubTotalKeluar)."     Sisa:".number_format($Sisa)."</b></div>";
						$content_html.= "	<div style='clear:both;'></div>";

						$SubTotalMasuk = 0;
						$SubTotalKeluar = 0;
						$Sisa = 0;

						////Stok Awal
						$content_html.= "			<div style='width:69%;".$style_summary.$kiri."'></div>";
						$content_html.= "			<div style='width:15%;".$style_summary.$kanan."'><b>Stok Awal:".number_format($datalaporan->data[$i]->curStock_Awal)."</b></div>";
						$content_html.= "	<div style='clear:both;'></div>";						
					}										
				}
				else {
					////Stok Awal
					$content_html.= "			<div style='width:69%;".$style_summary.$kiri."'></div>";
					$content_html.= "			<div style='width:15%;".$style_summary.$kanan."'><b>Stok Awal:".number_format($datalaporan->data[$i]->curStock_Awal)."</b></div>";
					$content_html.= "	<div style='clear:both;'></div>";					
				}

				$Masuk=0;
				$Keluar=0;

				if ( $datalaporan->data[$i]->Type_Trans == "KP" || $datalaporan->data[$i]->Type_Trans == "RT"
						|| $datalaporan->data[$i]->Type_Trans == "PU" || $datalaporan->data[$i]->Type_Trans == "BK"
						|| $datalaporan->data[$i]->Type_Trans == "FU" ) {
					$Masuk = $datalaporan->data[$i]->Qty;
				}
				else {
					$Masuk=0;
				}

				if ( $datalaporan->data[$i]->Type_Trans == "MT"  || $datalaporan->data[$i]->Type_Trans == "FK" ) {
					$Keluar = $datalaporan->data[$i]->Qty;
				}
				else {
					$Keluar=0;
				}

				//// Sisa = !curstock_awal + SubTotalMasuk - SubTotalKeluar
				$SubTotalMasuk += $Masuk;
				$SubTotalKeluar += $Keluar;
				$Sisa = $datalaporan->data[$i]->curStock_Awal + $SubTotalMasuk - $SubTotalKeluar;

				//// Detail
				$content_html.= "			<div style='width:8%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->Kd_Gudang."</b></div>";
				$content_html.= "			<div style='width:8%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->Divisi."</b></div>";
				$content_html.= "			<div style='width:8%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->Merk."</b></div>";	
				$content_html.= "			<div style='width:15%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->Kd_Brg."</b></div>";
				$content_html.= "			<div style='width:10%;".$style_summary.$kiri."'><b>".date("d-m-Y",strtotime($datalaporan->data[$i]->Tgl_Trans))."</b></div>";
				$content_html.= "			<div style='width:10%;".$style_summary.$kiri."'><b>".date("d-m-Y",strtotime($datalaporan->data[$i]->Entry_Time))."</b></div>";
				$content_html.= "			<div style='width:10%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->Kd_Plg."</b></div>";		
				$content_html.= "			<div style='width:5%;".$style_summary.$kanan."'><b>".number_format($Masuk)."</b></div>";
				$content_html.= "			<div style='width:5%;".$style_summary.$kanan."'><b>".number_format($Keluar)."</b></div>";
				$content_html.= "			<div style='width:5%;".$style_summary.$kanan."'><b>".number_format($Sisa)."</b></div>";
				$content_html.= "	<div style='clear:both;'></div>";

				$Kd_Brg = $datalaporan->data[$i]->Kd_Brg;
			}

			////CetakTotal
			$content_html.= "			<div style='width:69%;".$style_summary.$kiri."'></div>";
			$content_html.= "			<div style='width:15%;".$style_summary.$kanan."'><b>Masuk:".number_format($SubTotalMasuk)."     Keluar:".number_format($SubTotalKeluar)."     Sisa:".number_format($Sisa)."</b></div>";
			$content_html.= "	<div style='clear:both;'></div>";

			$content_html.= "		</div>";
			$content_html.= "	</div>";
			$content_html.= "</div>";
			$content_html.= "</body></html>";


			$data['title'] = $page_title;
			$data['content_html'] = $content_html;
			$this->RenderView('ReportStockGudangResult',$data);
		}

		public function Preview_D ( $page_title, $datalaporan, $tgl1, $tgl2, $judul, $radgudang, $kd_gudang ) {
			$style_col_ganjil ="float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#ffffcc;";
			$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
			$style_summary= "float:left;height:30px;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#cccccc;";
	
			$kanan= "text-align:right;padding-right:10px;";
			$kiri = "text-align:left; padding-left:10px;";
	
			$content_html = "<style> body { font-size:9pt; } </style>";
			$content_html.= "<html><body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;padding-left:1px;'>";
			$content_html.= "	<div><h2>" .$judul. "</h2></div>";			
			$content_html.= "	<div><b>Periode : ".$tgl1." - ".$tgl2."</b></div>";
			
			if ( $radgudang == "gudang") {
				$content_html.= "	<div><b>Gudang : ".$kd_gudang."</b></div>";
			}
			else {
				$content_html.= "	<div><b>Grup Gudang: ".$kd_gudang."</b></div>";
			}
			
			$content_html.= "</div>";	//close div_header


			$content_html.= "<div class='div_body' style='overflow-x:padding-left:1px;'>";
			$content_html.= "	<div id='div_column_header' style='width:1500px!important;line-height:90px;vertical-align:middle;'>";
			$content_html.= "		<div style='width:8%;".$style_summary.$kiri."height:60px!important;'><u>Gudang</u></div>";
			$content_html.= "		<div style='width:8%;".$style_summary.$kiri."height:60px!important;'><u>Divisi</u></div>";
			$content_html.= "		<div style='width:8%;".$style_summary.$kiri."height:60px!important;'><u>Merk</u></div>";
			$content_html.= "		<div style='width:15%;".$style_summary.$kiri."height:60px!important;'><u>Barang</u></div>";
			$content_html.= "		<div style='width:5%;".$style_summary.$kanan."height:60px!important;'><u>Awal</u></div>";
			$content_html.= "		<div style='width:5%;".$style_summary.$kanan."height:60px!important;'><u>Beli</u></div>";
			$content_html.= "		<div style='width:5%;".$style_summary.$kanan."height:60px!important;'><u>Jual</u></div>";
			$content_html.= "		<div style='width:5%;".$style_summary.$kanan."height:60px!important;'><u>Mutasi</u></div>";
			$content_html.= "		<div style='width:5%;".$style_summary.$kanan."height:60px!important;'><u>Retur</u></div>";
			$content_html.= "		<div style='width:5%;".$style_summary.$kanan."height:60px!important;'><u>sisa</u></div>";
			$content_html.= "	<div style='clear:both;'></div>";

			$Sisa = 0;

			$jml=count($datalaporan->data);
			for($i=0;$i<$jml;$i++) {
				
				$Sisa = $datalaporan->data[$i]->curStock_Awal 
						+ $datalaporan->data[$i]->tmpBeli 
						- $datalaporan->data[$i]->tmpJual 
						- $datalaporan->data[$i]->tmpMutasi 
						+ $datalaporan->data[$i]->tmpRetur ;

				$content_html.= "			<div style='width:8%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->kd_gudang."</b></div>";
				$content_html.= "			<div style='width:8%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->Divisi."</b></div>";
				$content_html.= "			<div style='width:8%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->merk."</b></div>";	
				$content_html.= "			<div style='width:15%;".$style_summary.$kiri."'><b>".$datalaporan->data[$i]->kd_brg."</b></div>";
				$content_html.= "			<div style='width:5%;".$style_summary.$kanan."'><b>".number_format($datalaporan->data[$i]->curStock_Awal)."</b></div>";
				$content_html.= "			<div style='width:5%;".$style_summary.$kanan."'><b>".number_format($datalaporan->data[$i]->tmpBeli)."</b></div>";
				$content_html.= "			<div style='width:5%;".$style_summary.$kanan."'><b>".number_format($datalaporan->data[$i]->tmpJual)."</b></div>";
				$content_html.= "			<div style='width:5%;".$style_summary.$kanan."'><b>".number_format($datalaporan->data[$i]->tmpMutasi)."</b></div>";			
				$content_html.= "			<div style='width:5%;".$style_summary.$kanan."'><b>".number_format($datalaporan->data[$i]->tmpRetur)."</b></div>";
				$content_html.= "			<div style='width:5%;".$style_summary.$kanan."'><b>".number_format($Sisa)."</b></div>";
				$content_html.= "	<div style='clear:both;'></div>";
			}

			$content_html.= "		</div>";
			$content_html.= "	</div>";
			$content_html.= "</div>";
			$content_html.= "</body></html>";


			$data['title'] = $page_title;
			$data['content_html'] = $content_html;
			$this->RenderView('ReportStockGudangResult',$data);
		}

		public function Excel_B ( $page_title, $datalaporan, $tgl1, $tgl2, $judul, $radgudang, $kd_gudang ) {
			// print_r($datalaporan);
			// die;	

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', $judul);
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', 'Periode : '.$tgl1.' sd '.$tgl2);

			if ($radgudang == "") {
				$sheet->setCellValue('A3', 'Gudang : '.$kd_gudang);
			}
			else {
				$sheet->setCellValue('A3', 'Grup Gudang : '.$kd_gudang);
			}
            								
			$currcol = 1;
			$currrow = 6;
						
			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Gudang');
			$sheet->getColumnDimension('A')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
			$sheet->getColumnDimension('B')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
			$sheet->getColumnDimension('C')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Barang');
			$sheet->getColumnDimension('D')->setWidth(35);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tanggal Faktur');
			$sheet->getColumnDimension('E')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tanggal Keluar');
			$sheet->getColumnDimension('F')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Bukti');
			$sheet->getColumnDimension('G')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Dealer');
			$sheet->getColumnDimension('H')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Qty');
			$sheet->getColumnDimension('I')->setWidth(10);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
														
			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
						
			
			// Detail
			$jum= count($datalaporan->data);
			for($i=0; $i<$jum; $i++){
				
				$currrow++;
				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->kd_gudang);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->Divisi);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->Merk);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->Kd_Brg);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("m-d-Y",strtotime($datalaporan->data[$i]->Tgl_Trans)));
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("m-d-Y",strtotime($datalaporan->data[$i]->entry_time)));
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->No_Bukti);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->Kd_Plg);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->Qty);
				$currcol += 1;
			}
			
			// print_r ($judul);

			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A6:'.$max_col.'7')->getAlignment()->setHorizontal($alignment_center);
			
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A6:'.$max_col.'7')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
			// border
			$sheet->getStyle("A1:".$max_col."7")->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet->getStyle('A6:'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');
			
				
			$filename= $judul. ' ['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 
			exit();
		}
		
		public function Excel_C ( $page_title, $datalaporan, $tgl1, $tgl2, $judul, $radgudang, $kd_gudang ) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', $judul);
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', 'Periode : '.$tgl1.' sd '.$tgl2);

            if ($radgudang == "") {
				$sheet->setCellValue('A3', 'Gudang : '.$kd_gudang);
			}
			else {
				$sheet->setCellValue('A3', 'Grup Gudang : '.$kd_gudang);
			}
								
			$currcol = 1;
			$currrow = 6;
						
			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Gudang');
			$sheet->getColumnDimension('A')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
			$sheet->getColumnDimension('B')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
			$sheet->getColumnDimension('C')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Barang');
			$sheet->getColumnDimension('D')->setWidth(35);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tanggal Faktur');
			$sheet->getColumnDimension('E')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tanggal Keluar');
			$sheet->getColumnDimension('F')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Dealer / Gudang');
			$sheet->getColumnDimension('G')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Masuk');
			$sheet->getColumnDimension('H')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Keluar');
			$sheet->getColumnDimension('I')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Sisa');
			$sheet->getColumnDimension('J')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
														
			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
						
			// print_r ($datalaporan);
			// die;

			// Detail

			$SubTotalMasuk = 0;
			$SubTotalKeluar = 0;
			$Sisa = 0;
			$Kd_Brg = "!@#$%";

						

			$jum= count($datalaporan->data);
			for($i=0; $i<$jum; $i++){
				
				if ( $Kd_Brg != "!@#$%" ){
					if( $Kd_Brg != $datalaporan->data[$i]->Kd_Brg ) { 
						//CetakTotal
						$currrow++;
						$currcol = 8;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Masuk : ".$SubTotalMasuk);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Keluar : ".$SubTotalKeluar);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Sisa : ".$Sisa);
						$currrow++;

						$SubTotalMasuk = 0;
						$SubTotalKeluar = 0;
						$Sisa = 0;

						$currrow++;
						$currcol = 10;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Stok Awal : ".$datalaporan->data[$i]->curStock_Awal);
					}										
				}
				else {
					$currrow++;
					$currcol = 10;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Stok Awal : ".$datalaporan->data[$i]->curStock_Awal);
				}

				$Masuk=0;
				$Keluar=0;

				if ( $datalaporan->data[$i]->Type_Trans == "KP" || $datalaporan->data[$i]->Type_Trans == "RT"
						|| $datalaporan->data[$i]->Type_Trans == "PU" || $datalaporan->data[$i]->Type_Trans == "BK"
						|| $datalaporan->data[$i]->Type_Trans == "FU" ) {
					$Masuk = $datalaporan->data[$i]->Qty;
				}
				else {
					$Masuk=0;
				}

				if ( $datalaporan->data[$i]->Type_Trans == "MT"  || $datalaporan->data[$i]->Type_Trans == "FK" ) {
					$Keluar = $datalaporan->data[$i]->Qty;
				}
				else {
					$Keluar=0;
				}

				// Sisa = !curstock_awal + SubTotalMasuk - SubTotalKeluar
				$SubTotalMasuk += $Masuk;
				$SubTotalKeluar += $Keluar;
				$Sisa = $datalaporan->data[$i]->curStock_Awal + $SubTotalMasuk - $SubTotalKeluar;

				
				$currrow++;
				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->Kd_Gudang);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->Divisi);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->Merk);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->Kd_Brg);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("m-d-Y",strtotime($datalaporan->data[$i]->Tgl_Trans)));
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("m-d-Y",strtotime($datalaporan->data[$i]->Entry_Time)));
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->Kd_Plg);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $Masuk);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $Keluar);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $Sisa);
				$currcol += 1;

				$Kd_Brg = $datalaporan->data[$i]->Kd_Brg;
			}
			
			//CetakTotal
			$currrow++;
			$currcol = 8;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Masuk : ".$SubTotalMasuk);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Keluar : ".$SubTotalKeluar);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Sisa : ".$Sisa);
			
			
			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A6:'.$max_col.'7')->getAlignment()->setHorizontal($alignment_center);
			
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A6:'.$max_col.'7')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
			// border
			$sheet->getStyle("A1:".$max_col."7")->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet->getStyle('A6:'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');
			
				
			$filename= $judul. ' ['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 
			exit();
		}

		public function Excel_D ( $page_title, $datalaporan, $tgl1, $tgl2, $judul, $radgudang, $kd_gudang ) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', $judul);
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', 'Periode : '.$tgl1.' sd '.$tgl2);

            if ($radgudang == "") {
				$sheet->setCellValue('A3', 'Gudang : '.$kd_gudang);
			}
			else {
				$sheet->setCellValue('A3', 'Grup Gudang : '.$kd_gudang);
			}
								
			$currcol = 1;
			$currrow = 6;
						
			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Gudang');
			$sheet->getColumnDimension('A')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
			$sheet->getColumnDimension('B')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
			$sheet->getColumnDimension('C')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Barang');
			$sheet->getColumnDimension('D')->setWidth(25);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Stok Awal');
			$sheet->getColumnDimension('E')->setWidth(10);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Beli');
			$sheet->getColumnDimension('F')->setWidth(10);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Jual');
			$sheet->getColumnDimension('G')->setWidth(10);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Mutasi');
			$sheet->getColumnDimension('H')->setWidth(10);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Retur');
			$sheet->getColumnDimension('I')->setWidth(10);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Sisa');
			$sheet->getColumnDimension('J')->setWidth(10);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
														
			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
						
			
			// Detail
			$Sisa = 0;
			$jum= count($datalaporan->data);
			for($i=0; $i<$jum; $i++){							

				$Sisa = $datalaporan->data[$i]->curStock_Awal 
						+ $datalaporan->data[$i]->tmpBeli 
						- $datalaporan->data[$i]->tmpJual 
						- $datalaporan->data[$i]->tmpMutasi 
						+ $datalaporan->data[$i]->tmpRetur ;

				$currrow++;
				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->kd_gudang);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->Divisi);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->merk);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->kd_brg);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->curStock_Awal );
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->tmpBeli);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->tmpJual);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->tmpMutasi);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->tmpRetur);				
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $Sisa);
				$currcol += 1;

			}			
			
			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A6:'.$max_col.'7')->getAlignment()->setHorizontal($alignment_center);
			
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A6:'.$max_col.'7')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
			// border
			$sheet->getStyle("A1:".$max_col."7")->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet->getStyle('A6:'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');
			
				
			$filename= $judul. ' ['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 
			exit();
		}



    }


