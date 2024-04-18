<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class LaporanPenjualanNasional extends MY_Controller 
	{
		public $excel_flag = 0;
		public $nama_bulan = array('','JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER');
		public $wilayah_group = array();
		
		
		public function __construct()
		{
			parent::__construct();
			$this->load->model('HelperModel');
			$this->load->helper('FormLibrary');
			$this->load->library('excel');
			$this->load->model('MasterReportWilayahModel', 'ReportModel');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
			//$this->API_URL = "http://localhost:90/webAPI";
			$this->apiurl = $this->API_URL."/LaporanPenjualanNasional/";

			$this->max_exec_time = 240;
			$this->memory_limit = '300m';
			ini_set('max_execution_time', $this->max_exec_time);
			ini_set('memory_limit', $this->memory_limit);
		}
		function _decodeGzip($str){
		    // Try to decode the data
		    $decodedData = @gzdecode($str);

		    // Check if decoding was successful
		    if ($decodedData !== false) {
		        return $decodedData; // The string is gzip-encoded

		    } else {
		        return $str; // The string is not gzip-encoded
		    }
		}
		public function index()
		{
			$data = array();
			$api = 'APITES';
						
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN PENJUALAN NASIONAL";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN PENJUALAN NASIONAL ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);

			if ($_SESSION["logged_in"]["isUserPabrik"]==0) {
				$check_divisi = json_decode(file_get_contents($this->apiurl."CheckDivisi?api=".$api."&p_user=".urlencode($_SESSION['logged_in']['useremail'])));
				$data['divisi'] = $check_divisi;
			} else {
				$data["divisi"] = array();
			}

			$check_laporan = json_decode(file_get_contents($this->apiurl."CheckLaporan?api=".$api."&pabrik=".$_SESSION["logged_in"]["isUserPabrik"]));
			$data['laporan'] = $check_laporan;

			$check_wilayah = $this->ReportModel->getOptWilayahGroup('PENJUALAN NASIONAL');
			$data['wilayah'] = $check_wilayah;
			// die(json_encode($check_wilayah));
			
			$data['title'] = 'Laporan Penjualan Nasional';

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('LaporanPenjualanNasionalFormView',$data);
		}
		
		public function LoadWilayahGroup()
		{
			$this->wilayah_group = $this->ReportModel->getList('PENJUALAN NASIONAL', 'WILAYAH');
		}
		
		public function GetWilayahGroup($partnertype, $wilayah, $kota="")
		{
			$ketemu = false;
			/* PRIORITAS PERTAMA CARI KOTA YG TERSIMPANNYA BUKAN ALL */
			foreach($this->wilayah_group as $wg){
				if((strtoupper(trim($wg->Wilayah))==strtoupper(trim($wilayah))) && (strtoupper(trim($wg->PartnerType))==strtoupper(trim($partnertype)))) {
					$ketemu = true;
					return $wg->WilayahGroup;
				}
			}
			
			/* PRIORITAS KEDUA CARI KOTA YG TERSIMPANNYA ALL */
			foreach($this->wilayah_group as $wg){
				if((strtoupper(trim($wg->PartnerType))==strtoupper(trim($partnertype))) && (strtoupper(trim($wg->Wilayah))=='ALL')){
					$ketemu = true;
					return $wg->WilayahGroup;
				}
			}

			/* GAK KETEMU AMBIL NAMA WILAYAHNYA */
			if ($ketemu==false) {
				return $wilayah;
			} else {
				return null;
			}
		}
		
		public function Proses()
		{

			$data = array();
			// die(json_encode($_POST));
			
			if(isset($_POST["btnPreview"])){
				$this->excel_flag = 0;
			}
			else{
				$this->excel_flag = 1;
			}
			
			if(isset($_POST['laporan']))
			{

				// ActivityLog
				$params = array();   
				$params['LogDate'] = date("Y-m-d H:i:s");
				$params['UserID'] = $_SESSION["logged_in"]["userid"];
				$params['UserName'] = $_SESSION["logged_in"]["username"];
				$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
				$params['Module'] = "LAPORAN PENJUALAN NASIONAL";
				$params['TrxID'] = date("YmdHis");
				$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES LAPORAN PENJUALAN NASIONAL ".$_POST["laporan"];
				$params['Remarks'] = "";
				$params['RemarksDate'] =  'NULL';
				$this->ActivityLogModel->insert_activity($params);

				$this->load->library('form_validation');
				$this->form_validation->set_rules('laporan','Laporan','required');
				$this->form_validation->set_rules('dp1','Tanggal Awal','required');
				$this->form_validation->set_rules('kategori','Kategori','required');
				$this->form_validation->set_rules('divisi','Divisi','required');
				$this->form_validation->set_rules('type','Lokal/Import','required');

				
				// if($_POST["laporan"]=='A01' || $_POST["laporan"]=='A02' || $_POST["laporan"]=='A03' || $_POST["laporan"]=='A04' || $_POST["laporan"]=='A05')
				if(substr($_POST["laporan"],0,1)=='A')
				{
					$tgl = explode("/",$_POST["dp1"]);
					// $p_bln = intval($tgl[0]);
					$p_bln = $tgl[0];
					$p_thn = $tgl[1];
					$_POST["dp1"] = $p_bln."/01/".$p_thn;
					$_POST["dp2"] = date("m/t/Y", strtotime($_POST["dp1"]));
				} else if(substr($_POST["laporan"],0,1)=='C') {
					$p_thn = $_POST["dp1"];
					// $p_bln = intval($tgl[0]);
					$_POST["dp1"] = "01/01/".$p_thn;
					$_POST["dp2"] = "12/31/".$p_thn;
				} else {
					// if($_POST["laporan"]=='B01' || $_POST["laporan"]=='B02' || $_POST["laporan"]=='B03' || $_POST["laporan"]=='B04'){
					$this->form_validation->set_rules('dp2','Tanggal Akhir','required');
					$tgl = explode("/",$_POST["dp1"]);
					$_POST["dp1"] = $tgl[1]."/".$tgl[0]."/".$tgl[2];
					$tgl = explode("/",$_POST["dp2"]);
					$_POST["dp2"] = $tgl[1]."/".$tgl[0]."/".$tgl[2];
				}
				if($_POST["laporan"]=='D01'){
					$this->form_validation->set_rules('dp2','Tanggal Akhir','required');
				}
				// die($_POST["dp1"]."-".$_POST["dp2"]);

				
				$ex_cash = (ISSET($_POST['ex_cash'])) ? 1 : 0;
				$ex_bass = (ISSET($_POST['ex_bass'])) ? 1 : 0;
				$grup_subkategori = (ISSET($_POST['grup_subkategori'])) ? 1 : 0;
				$supl = "x";

				// $user = strtoupper($_SESSION["logged_in"]["useremail"]);
				// if ($user == "USER@PABRIK.KG"){
				// 	$supl = "JKTK001";
				// }
				// if ($user == "QR@PABRIK.KG"){ 
				// 	$supl = "JKTK001";
				// }
				// if ($user == "USER@PABRIK.PTRI"){ 
				// 	$supl = "JKTR001";
				// }
				// if ($user == "QR@PABRIK.PTRI"){ 
				// 	$supl = "JKTR001";
				// }
				// if ($user == "USER@PABRIK.TIN"){ 
				// 	$supl = "JKTT003";
				// }
				// if ($user == "QR@PABRIK.TIN"){ 
				// 	$supl = "JKTT003";
				// }

				/*Tambahan 23 April 2021: untuk User Pabrik bisa Buka Laporan Penjualan QTY */
				// die(json_encode($_POST));
				// die($supl);
				$_POST["divisi"] = trim($_POST["divisi"]);

				//reegan1 A03, A04, B01, B02, B03, B04, C02, C03, D01 dan D02
				if($this->form_validation->run())
				{
					if($_POST["laporan"]=='A01'){
						$this->PreviewA01A05($_POST["laporan"], 'LAPORAN PENJUALAN HARIAN GABUNGAN (Rp)', $_POST["kategori"], $_POST["divisi"], $_POST["type"], 
						$p_bln, $p_thn,$ex_cash,$ex_bass,$supl, $params);
					}
					else if($_POST["laporan"]=='A02'){
						$this->PreviewA02A03($_POST["laporan"], 'LAPORAN PENJUALAN HARIAN ALL CABANG (Rp)', $_POST["kategori"], $_POST["divisi"], $_POST["type"], 
						$p_bln, $p_thn,$ex_cash,$ex_bass,$supl, $params);
					}
					else if($_POST["laporan"]=='A03'){
						//reegan1
						$this->PreviewA02A03($_POST["laporan"], 'LAPORAN PENJUALAN HARIAN ALL WILAYAH (Rp)', $_POST["kategori"], $_POST["divisi"], $_POST["type"], 
						$p_bln, $p_thn,$ex_cash,$ex_bass,$supl, $params);
					}
					else if($_POST["laporan"]=='A04'){
						//reegan1
						$this->PreviewA04($_POST["laporan"], 'LAPORAN PENJUALAN HARIAN ALL WILAYAH (Rp)', $_POST["kategori"], $_POST["divisi"], $_POST["type"], 
						$p_bln, $p_thn,$ex_cash,$ex_bass,$supl, $params);
					}
					else if($_POST["laporan"]=='A05'){
						$this->PreviewA01A05($_POST["laporan"], 'LAPORAN PENJUALAN HARIAN GABUNGAN (Rp)', $_POST["kategori"], $_POST["divisi"], $_POST["type"], 
						$p_bln, $p_thn,$ex_cash,$ex_bass,$supl, $params);
					}
					
					else if($_POST["laporan"]=='B01'){
						//reegan1
						$this->PreviewB01B02($_POST["laporan"], 'LAPORAN PENJUALAN PER BARANG ALL WILAYAH (Rp)', $_POST["kategori"], $_POST["divisi"], $_POST["type"], 
						$_POST["dp1"], $_POST["dp2"],$ex_cash,$ex_bass,$grup_subkategori,$supl, $params);
					}
					
					else if($_POST["laporan"]=='B02'){
						//reegan1
						$this->PreviewB01B02($_POST["laporan"], 'LAPORAN PENJUALAN PER BARANG ALL WILAYAH (Qty)', $_POST["kategori"], $_POST["divisi"], $_POST["type"], 
						$_POST["dp1"], $_POST["dp2"],$ex_cash,$ex_bass,$grup_subkategori,$supl, $params);
					}
					else if($_POST["laporan"]=='B03'){
						//reegan1
						$this->PreviewB03B04($_POST["laporan"], 'LAPORAN PENJUALAN PER BARANG ALL WILAYAH (Rp)', $_POST["kategori"], $_POST["divisi"], $_POST["type"], 
						$_POST["dp1"], $_POST["dp2"],$ex_cash,$ex_bass,$grup_subkategori,$supl, $params);
					}
					// else if($_POST["laporan"]=='B04'){
					// 	//reegan1
					// 	$this->Previewv2($_POST["laporan"], 'LAPORAN PENJUALAN PER BARANG ALL WILAYAH (Qty)', $_POST["kategori"], $_POST["divisi"], $_POST["type"], 
					// 	$_POST["dp1"], $_POST["dp2"],$ex_cash,$ex_bass,$grup_subkategori,$supl, $params);
					// }
					else if($_POST["laporan"]=='B04'){
						//reegan1
						$this->PreviewB03B04($_POST["laporan"], 'LAPORAN PENJUALAN PER BARANG ALL WILAYAH (Qty)', $_POST["kategori"], $_POST["divisi"], $_POST["type"], 
						$_POST["dp1"], $_POST["dp2"],$ex_cash,$ex_bass,$grup_subkategori,$supl, $params);
					}
					else if($_POST["laporan"]=='C01'){
						$thn_lalu = intval($_POST["dp1"])-1;
						$this->PreviewC01($_POST["laporan"], 'LAPORAN PENJUALAN QTY BULANAN PER BARANG GABUNGAN', $_POST["kategori"], $_POST["divisi"], $_POST["type"], 
						$thn_lalu, $_POST["dp1"],$ex_cash,$ex_bass, $_POST["wilayah"],$supl, $params);
					}
					else if($_POST["laporan"]=='C02'){
						//reegan1
						$this->PreviewC02($_POST["laporan"], 'LAPORAN PENJUALAN QTY BULANAN PER BARANG ALL WILAYAH', $_POST["kategori"], $_POST["divisi"], $_POST["type"], 
						0, $_POST["dp1"],$ex_cash,$ex_bass,$supl, $params);
					}
					else if($_POST["laporan"]=='C03'){
						//reegan1
						$this->PreviewC03($_POST["laporan"], 'LAPORAN PENJUALAN QTY BULANAN PER BARANG WILAYAH (GRUP TARGET)', $_POST["kategori"], $_POST["divisi"], $_POST["type"], 
						0, $_POST["dp1"],$ex_cash,$ex_bass,$supl, $params);
					}
					
					else if($_POST["laporan"]=='D01'){
						//reegan1
						$this->PreviewD01($_POST["laporan"], 'PENJUALAN PER DIVISI ALL WILAYAH (RP)', $_POST["kategori"], $_POST["divisi"], $_POST["type"], 
						$_POST["dp1"], $_POST["dp2"],$ex_cash,$ex_bass,$grup_subkategori,$supl, $params);
					}
					
					else if($_POST["laporan"]=='D02'){
						//reegan1
						$this->PreviewD02($_POST["laporan"], 'PENJUALAN PER DIVISI ALL WILAYAH (RP)', $_POST["kategori"], $_POST["divisi"], $_POST["type"], 
						$_POST["dp1"], $_POST["dp2"],$ex_cash,$ex_bass,$grup_subkategori,$supl, $params);
					}
					
					else{
						redirect("LaporanPenjualanNasional");
					}
				}
				else
				{
					redirect("LaporanPenjualanNasional");
				}
			}
			else
			{
				redirect("LaporanPenjualanNasional");
			}
		}
		
		
		public function PreviewC03($p_laporan,$p_nama_laporan,$p_kategori, $p_divisi, $p_type, $p_bln, $p_thn, $ex_cash, $ex_bass, $supl="", $params)
		{
			
			$data = array();
			$api = 'APITES';
			$streamContext = stream_context_create(
				array('http'=>
					array(
						'timeout' => $this->max_exec_time,  //600 seconds = 10 menit
						'header' => 'Accept-Encoding: gzip, deflate', // Menambahkan header Accept-Encoding
					)
				)
			);

			$URL = $this->apiurl."ProsesHarianC03?api=".$api."&p_laporan=".urlencode($p_laporan)."&p_bln=".urlencode($p_bln).
					"&p_thn=".urlencode($p_thn)."&p_kategori=".urlencode($p_kategori)."&p_divisi=".urlencode($p_divisi)."&p_type=".urlencode($p_type).
					"&ex_cash=".urlencode($ex_cash)."&ex_bass=".urlencode($ex_bass)."&supl=".urlencode($supl);
			
			// die($URL);
			$response = file_get_contents($URL, false, $streamContext);
			$response = $this->_decodeGzip($response);
			$json = json_decode($response);
			
			if(count($json)==0){
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit('Tidak ada data');
			}
			
			
			$this->LoadWilayahGroup();
			
			$details = array();
			$array_cabang = array();
			
			foreach($json as $new) {
			
			
				// Mencari nama wilayahgroup by wilayah dan kota.
				$WilayahGroup = $this->GetWilayahGroup(trim($new->Partner_Type), trim($new->Wilayah));
				if($WilayahGroup=='') exit('Silahkan isi konfigurasi di Konfigurasi Wilayah Group Report untuk partner type:'.$new->Partner_Type.' dan wilayah:'.$new->Wilayah);
				$detail = array(
					'Cabang'=>$WilayahGroup,
					'Bln'=>$new->Bln,
					'Total'=>$new->Total
				);
				$divisi = trim($new->Divisi);
				$merk = trim($new->Merk);
				$SubKategori_Brg = trim($new->SubKategori_Brg);
				$kode = trim($new->Kode);
				
				// cek apakah array sudah ada untuk group divisi, merk, cabang,
				if(ISSET($details[$divisi][$merk][$SubKategori_Brg][$kode])){
					$ada = 0;
					// maka cek apakah sudah ada tanggal nya
					foreach($details[$divisi][$merk][$SubKategori_Brg][$kode] as $k => $val){
						//jika ada tanggal yg sama untuk wilayah group yg sama, maka akan di-sum
						if($WilayahGroup == $val['Cabang'] && $new->Bln == $val['Bln']){ 
							$details[$divisi][$merk][$SubKategori_Brg][$kode][$k]['Total'] += $new->Total; 
							$ada = 1;
						}
					}
					if($ada == 0){ // jika tidak ada tanggalnya , maka tambah data baru
						$details[$divisi][$merk][$SubKategori_Brg][$kode][] = $detail;
					}
				}
				else{
					// jika tidak ada array-nya, maka tambah data baru juga
					$details[$divisi][$merk][$SubKategori_Brg][$kode][] = $detail;
				}
				
				array_push($array_cabang, $WilayahGroup);
				ksort($details[$divisi][$merk]); //sort kategori
				ksort($details[$divisi][$merk][$SubKategori_Brg]); //sort kode
				
			}
			$array_cabang = array_unique($array_cabang);
			sort($array_cabang);
			$PenjualanNasional = array('header'=>$array_cabang, 'detail'=>$details);
			$PenjualanNasional = json_decode(json_encode($PenjualanNasional));
			
			$warna_table_header = 'f2f2f2';
			
			$warna_table_divisi = 'c4bd97';
			$warna_table_merk = '808080';
			$warna_table_sub = 'a6a6a6';
			$warna_table_kode = 'd9d9d9';
			
			$warna_table_grandtotal = 'fabf8f';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			if($p_type=='ALL'){
				$p_type = "LOKAL & IMPORT";
			}
			
			if($p_kategori=='P'){
				$p_kategori = "PRODUK";
			}
			else if($p_kategori=='S'){
				$p_kategori = "SPAREPART";
			}
			else{
				$p_kategori = "PRODUK & SPAREPART";
			}
			
			$exclude_cash = '';
			if($ex_cash==1){
				$exclude_cash .= '-EXCLUDE CASH KONSUMEN ';
			}
			$exclude_bass = '';
			if($ex_bass==1){
				$exclude_bass .= '-EXCLUDE BASS '; 
			}
			
			$content_html = "<html>";
			$content_html = "<head>";
			
			$content_html = "<style>
			body{font-family:'Calibri',Arial,sans-serif;}
			table{padding:0;margin:0;border-collapse:collapse}
			td, th { border:1px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "	<div><h2>".$p_nama_laporan."</h2></div>";
			$content_html.= "	<div style='float:left;width:50%'>PERIODE: ".$p_thn."</div>";
			$content_html.= "	<div style='float:left;width:30%'>DIVISI: ".$p_divisi."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_cash."</div>";
			$content_html.= "	<div style='float:left;width:50%'>KATEGORI: ".$p_kategori."</div>";
			$content_html.= "	<div style='float:left;width:30%'>".$p_type."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_bass."</div>";
			$content_html.= "	<div style='clear:both'></div>";
			$content_html.= "</div>";	//close div_header
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($p_nama_laporan, 0, 31));
				$sheet->setCellValue('A1', $p_nama_laporan);
				$sheet->getStyle('A1')->getFont()->setSize(12);
				$sheet->setCellValue('A2', 'PERIODE : '.$p_thn);
				$sheet->setCellValue('A3', 'KATEGORI : '.$p_kategori);
				$sheet->setCellValue('I2', 'DIVISI : '.$p_divisi);
				$sheet->setCellValue('I3', $p_type);
				$sheet->setCellValue('K2', $exclude_cash);
				$sheet->setCellValue('K3', $exclude_bass);
			}
			
			$content_html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
			
			$content_html.= "<table style='font-size:10pt!important'>";
			$content_html.= "<tr style='background-color:#".$warna_table_header.";'>";
			
			$content_html.= "	<th style='min-width:20px'>No</th>";
			$content_html.= "	<th style='min-width:100px'>Divisi</th>";
			$content_html.= "	<th style='min-width:100px'>Merk</th>";
			$content_html.= "	<th style='min-width:100px'>Kategori</th>";
			$content_html.= "	<th style='min-width:100px'>Kode Barang</th>";
			$content_html.= "	<th style='min-width:80px'>BULAN</th>";
			
			$currcol = 0;
			$currrow = 5;
			
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No');
				$sheet->getColumnDimension('A')->setWidth(5);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
				$sheet->getColumnDimension('B')->setWidth(15);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
				$sheet->getColumnDimension('C')->setWidth(15);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kategori');
				$sheet->getColumnDimension('D')->setWidth(25);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Barang');
				$sheet->getColumnDimension('E')->setWidth(25);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'BULAN');
				$sheet->getColumnDimension('F')->setWidth(15);
			}
			
			
			$total_cabang_divisi = array();
			$total_cabang_merk = array();
			$total_cabang_sub = array();
			$total_cabang_kode = array();
			
			foreach($PenjualanNasional->header as $hd){
				$total_cabang_divisi[$hd] = 0;
				$total_cabang_merk[$hd] = 0;
				$total_cabang_sub[$hd] = 0;
				$total_cabang_kode[$hd] = 0;
				$grand_total[$hd] = 0;
				$content_html.= "<th style='min-width:80px'>".$hd."</th>";
				if($this->excel_flag == 1){
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $hd);
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
				}
			}
			
			
			$content_html.= "<th width='80px'>SUBTOTAL</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SUBTOTAL');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
			}
			$content_html.= "<th width='80px'>TOTAL PER BARANG</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL PER BARANG');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
			}
			$content_html.= "<th width='80px'>TOTAL KATEGORI</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL KATEGORI');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
			}
			$content_html.= "<th width='80px'>TOTAL MERK</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL MERK');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
			}
			
			$content_html.= "</tr>";
			$max_col = $currcol; // index kolom terakhir (paling kanan)
			$max_col_name = PHPExcel_Cell::stringFromColumnIndex($max_col-1); // nama kolom terakhir (paling kanan)
			
			
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			foreach($PenjualanNasional->detail as $dt_divisi => $merks) {
				$no = 0;
				$grand_total_merk = 0;
				$grand_total_sub = 0;
				$grand_total_kode = 0;
				$grand_total_bulan = 0;
				foreach($merks as $dt_merk => $subs) {
					$total_merk = 0;
					foreach($subs as $dt_sub => $kodes) {
						$total_sub = 0;
						foreach($kodes as $dt_kode => $details) {
							$total_kode = 0;
							for($bln=1;$bln<=12;$bln++){
								$no++;
								$content_html.= "<tr>";
								$content_html.= "<td>".$no."</td>";
								$content_html.= "<td>".$dt_divisi."</td>";
								$content_html.= "<td>".$dt_merk."</td>";
								$content_html.= "<td>".$dt_sub."</td>";
								$content_html.= "<td>".$dt_kode."</td>";
								$content_html.= "<td>".SUBSTR($this->nama_bulan[intval($bln)],0,3)."</td>";
								if($this->excel_flag == 1){
									$currrow++;
									$currcol = 1;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_divisi);
									$currcol += 1 ;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_merk);
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_sub);
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_kode);
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, SUBSTR($this->nama_bulan[intval($bln)],0,3));
								}
								$sub_total = 0;
								
								foreach($PenjualanNasional->header as $hd){
									$ada = 0;
									foreach ($details as $detail) {
										if($bln == $detail->Bln && $hd==$detail->Cabang){
											$sub_total += ($detail->Total);
											$total_cabang_kode[$hd] += ($detail->Total);
											$content_html.= "<td class='td-right'>".number_format($detail->Total,0)."</td>";
											
											//debug total
											/*if ($dt_kode=="PS-128BIT") {
												echo($hd." : ".number_format($detail->Total)."<br>");
												echo("Subtotal : ".number_format($sub_total)."<br>");
											}*/
											
											if($this->excel_flag == 1){
												$currcol += 1;
												$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->Total);
												$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
											}
											$ada = 1;											
										}
									}
									if($ada == 0){
										$content_html.= "<td class='td-right'>0</td>";
										if($this->excel_flag == 1){
											$currcol += 1;
											$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
											$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
										}
									}
								}
								
								$content_html.= "<td class='td-right td-bold'>".number_format($sub_total,0)."</td><td></td><td></td><td></td>";
								
								if($this->excel_flag == 1){	
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, $sub_total);
									$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
									$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
								}
								$content_html.= "</tr>";
								$grand_total_bulan += $sub_total;
								$total_kode += $sub_total;
							}
							
							$content_html.= "<tr style='background-color:#".$warna_table_kode.";'><td></td><td colspan='4' class='td-right'>TOTAL ".$dt_kode."</td><td></td>";
							
							$currrow++;
							if($this->excel_flag == 1){
								$currcol = 2;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_kode);
								$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
								$sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal($alignment_right);
								$sheet->setCellValueByColumnAndRow($max_col-2, $currrow, $total_kode);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-3).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_kode);
								$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
							}
							
							$currcol = 6;
							foreach($PenjualanNasional->header as $hd){
								$total_cabang_sub[$hd] += ($total_cabang_kode[$hd]);
								$content_html.= "<td class='td-right td-bold'>".number_format($total_cabang_kode[$hd],0)."</td>";
								if($this->excel_flag == 1){
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_cabang_kode[$hd]);
									$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
									$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
								}
								$total_cabang_kode[$hd] = 0;
							}
							
							$content_html.="<td></td><td class='td-right td-bold'>".number_format($total_kode,0)."</td><td></td><td></td></tr>";
							
							$grand_total_kode += $total_kode;
							$total_sub += $total_kode;
						}
						
						$content_html.= "<tr style='background-color:#".$warna_table_sub.";'><td></td><td colspan='4' class='td-right'>TOTAL ".$dt_divisi." - ".$dt_merk." - ".$dt_sub."</td><td></td>";
						
						$currrow++;
						if($this->excel_flag == 1){
							$currcol = 2;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi.' - '.$dt_merk.' - '.$dt_sub);
							$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
							$sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal($alignment_right);
							$sheet->setCellValueByColumnAndRow($max_col-1, $currrow, $total_sub);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-2).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_sub);
							$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
						}
						$currcol = 6;
						foreach($PenjualanNasional->header as $hd){
							$total_cabang_merk[$hd] += ($total_cabang_sub[$hd]);
							$content_html.= "<td class='td-right td-bold'>".number_format($total_cabang_sub[$hd],0)."</td>";
							if($this->excel_flag == 1){
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_cabang_sub[$hd]);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
							}
							$total_cabang_sub[$hd] = 0;
						}
						$content_html.="<td></td><td></td><td class='td-right td-bold'>".number_format($total_sub,0)."</td><td></td></tr>";
						
						$grand_total_sub += $total_sub;
						$total_merk += $total_sub;
						
					}
					$content_html.= "<tr style='background-color:#".$warna_table_merk.";'><td></td><td colspan='4' class='td-right'>TOTAL ".$dt_divisi." - ".$dt_merk."</td><td></td>";
					
					$currrow++;
					if($this->excel_flag == 1){
						$currcol = 2;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi.' - '.$dt_merk);
						$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
						$sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal($alignment_right);
						$sheet->setCellValueByColumnAndRow($max_col, $currrow, $total_merk);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_merk);
						$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
					}
					
					$currcol = 6;
					foreach($PenjualanNasional->header as $hd){
						$total_cabang_divisi[$hd] += ($total_cabang_merk[$hd]);
						$content_html.= "<td class='td-right td-bold'>".number_format($total_cabang_merk[$hd],0)."</td>";
						if($this->excel_flag == 1){
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_cabang_merk[$hd]);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
						}
						$total_cabang_merk[$hd] = 0;
					}
					$content_html.= "<td></td><td></td><td></td><td class='td-right td-bold'>".number_format($total_merk,0)."</td></tr>";
					
					$grand_total_merk += $total_merk;
				}
				
				
				$content_html.= "<tr style='background-color:#".$warna_table_divisi.";'><td></td><td colspan='4' class='td-right'>TOTAL ".$dt_divisi."</td><td></td>";
				
				$currrow++;
				if($this->excel_flag == 1){
					$currcol = 2;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi);
					$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
					$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_divisi);
					$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);
					$sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal($alignment_right);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				}
				$currcol = 6;
				foreach($PenjualanNasional->header as $hd){
					$grand_total[$hd] += ($total_cabang_divisi[$hd]);
					$content_html.= "<td class='td-right td-bold'>".number_format($total_cabang_divisi[$hd],0)."</td>";
					if($this->excel_flag == 1){
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_cabang_divisi[$hd]);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
					}
					
					$total_cabang_divisi[$hd] = 0;
				}
				
				$content_html.= "
				<td class='td-right td-bold'>".number_format($grand_total_bulan,0)."</td>
				<td class='td-right td-bold'>".number_format($grand_total_kode,0)."</td>
				<td class='td-right td-bold'>".number_format($grand_total_sub,0)."</td>
				<td class='td-right td-bold'>".number_format($grand_total_merk,0)."</td>
				</tr>";
				
				if($this->excel_flag == 1){
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_bulan);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_kode);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_sub);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_merk);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				}
				
			}
			
			$content_html.= "</tr>";
			$content_html.= "</table>";
			
			if($this->excel_flag == 1){
				// rata tengah header
				$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
				$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
				
				$sheet->getStyle('A5:C5')->getAlignment()->setHorizontal($alignment_left);			
				$sheet->getStyle('D5:'.$max_col_name.'5')->getAlignment()->setHorizontal($alignment_center);
				
				
				$sheet->getStyle('A5:'.$max_col_name.'5')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
				$sheet->getStyle('A5:'.$max_col_name.'5')->getAlignment()->setWrapText(true); 
				
				// bold
				$sheet->getStyle("A1:".$max_col_name."5")->getFont()->setBold(true);
				// border
				$styleArray = [
				'borders' => [
				'allBorders' => [
				'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
				],
				];
				$sheet ->getStyle('A5:'.$max_col_name.$currrow)->applyFromArray($styleArray);
				// $sheet->freezePane('A6');
				$sheet->setSelectedCell('A1');
				$filename=$p_nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 

				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit();
			}
			
			$content_html.= "</div>";
			$content_html.= "</body></html>";
			
			$data['title'] = $p_nama_laporan;
			$data['content_html'] = $content_html;
			
			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->load->view('LaporanResultView',$data);
		}
				
		public function PreviewC02($p_laporan,$p_nama_laporan,$p_kategori, $p_divisi, $p_type, $p_bln, $p_thn, $ex_cash, $ex_bass, $supl="", $params)
		{
			
			$data = array();
			$api = 'APITES';
			$streamContext = stream_context_create(
				array('http'=>
					array(
						'timeout' => $this->max_exec_time,  //600 seconds = 10 menit
						'header' => 'Accept-Encoding: gzip, deflate', // Menambahkan header Accept-Encoding
					)
				)
			);

			$URL = $this->apiurl."ProsesHarianC02?api=".$api."&p_laporan=".urlencode($p_laporan)."&p_bln=".urlencode($p_bln).
					"&p_thn=".urlencode($p_thn)."&p_kategori=".urlencode($p_kategori)."&p_divisi=".urlencode($p_divisi)."&p_type=".urlencode($p_type).
					"&ex_cash=".urlencode($ex_cash)."&ex_bass=".urlencode($ex_bass)."&supl=".urlencode($supl);
			
			// die($URL);
			$response = file_get_contents($URL, false, $streamContext);
			$response = $this->_decodeGzip($response);
			$PenjualanNasional = json_decode($response);
		
			
			if($PenjualanNasional==''){
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit('Tidak ada data');
			}
			
			$warna_table_header = 'f2f2f2';
			
			$warna_table_divisi = 'c4bd97';
			$warna_table_merk = '808080';
			$warna_table_sub = 'a6a6a6';
			$warna_table_kode = 'd9d9d9';
			
			$warna_table_grandtotal = 'fabf8f';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			if($p_type=='ALL'){
				$p_type = "LOKAL & IMPORT";
			}
			
			if($p_kategori=='P'){
				$p_kategori = "PRODUK";
			}
			else if($p_kategori=='S'){
				$p_kategori = "SPAREPART";
			}
			else{
				$p_kategori = "PRODUK & SPAREPART";
			}
			
			$exclude_cash = '';
			if($ex_cash==1){
				$exclude_cash .= '-EXCLUDE CASH KONSUMEN ';
			}
			$exclude_bass = '';
			if($ex_bass==1){
				$exclude_bass .= '-EXCLUDE BASS '; 
			}
			
			$content_html = "<html>";
			$content_html = "<head>";
			
			$content_html = "<style>
			body{font-family:'Calibri',Arial,sans-serif;}
			table{padding:0;margin:0;border-collapse:collapse}
			td, th { border:1px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "	<div><h2>".$p_nama_laporan."</h2></div>";
			$content_html.= "	<div style='float:left;width:50%'>PERIODE: ".$p_thn."</div>";
			$content_html.= "	<div style='float:left;width:30%'>DIVISI: ".$p_divisi."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_cash."</div>";
			$content_html.= "	<div style='float:left;width:50%'>KATEGORI: ".$p_kategori."</div>";
			$content_html.= "	<div style='float:left;width:30%'>".$p_type."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_bass."</div>";
			$content_html.= "	<div style='clear:both'></div>";
			$content_html.= "</div>";	//close div_header
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($p_nama_laporan, 0, 31));
				$sheet->setCellValue('A1', $p_nama_laporan);
				$sheet->getStyle('A1')->getFont()->setSize(12);
				$sheet->setCellValue('A2', 'PERIODE : '.$p_thn);
				$sheet->setCellValue('A3', 'KATEGORI : '.$p_kategori);
				$sheet->setCellValue('I2', 'DIVISI : '.$p_divisi);
				$sheet->setCellValue('I3', $p_type);
				$sheet->setCellValue('K2', $exclude_cash);
				$sheet->setCellValue('K3', $exclude_bass);
			}
			
			$content_html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
			
			$content_html.= "<table style='font-size:10pt!important'>";
			$content_html.= "<tr style='background-color:#".$warna_table_header.";'>";
			
			$content_html.= "	<th style='min-width:20px'>No</th>";
			$content_html.= "	<th style='min-width:100px'>Divisi</th>";
			$content_html.= "	<th style='min-width:100px'>Merk</th>";
			$content_html.= "	<th style='min-width:100px'>Kategori</th>";
			$content_html.= "	<th style='min-width:100px'>Kode Barang</th>";
			$content_html.= "	<th style='min-width:80px'>BULAN</th>";
			
			$currcol = 0;
			$currrow = 5;
			
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No');
				$sheet->getColumnDimension('A')->setWidth(5);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
				$sheet->getColumnDimension('B')->setWidth(15);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
				$sheet->getColumnDimension('C')->setWidth(15);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kategori');
				$sheet->getColumnDimension('D')->setWidth(25);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Barang');
				$sheet->getColumnDimension('E')->setWidth(25);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'BULAN');
				$sheet->getColumnDimension('F')->setWidth(15);
			}
			
			
			$total_cabang_divisi = array();
			$total_cabang_merk = array();
			$total_cabang_sub = array();
			$total_cabang_kode = array();
			
			foreach($PenjualanNasional->header as $hd){
				$total_cabang_divisi[$hd] = 0;
				$total_cabang_merk[$hd] = 0;
				$total_cabang_sub[$hd] = 0;
				$total_cabang_kode[$hd] = 0;
				$grand_total[$hd] = 0;
				$content_html.= "<th style='min-width:80px'>".$hd."</th>";
				if($this->excel_flag == 1){
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $hd);
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
				}
			}
			
			
			$content_html.= "<th width='80px'>SUBTOTAL</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SUBTOTAL');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
			}
			$content_html.= "<th width='80px'>TOTAL PER BARANG</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL PER BARANG');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
			}
			$content_html.= "<th width='80px'>TOTAL KATEGORI</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL KATEGORI');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
			}
			$content_html.= "<th width='80px'>TOTAL MERK</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL MERK');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
			}
			
			$content_html.= "</tr>";
			$max_col = $currcol; // index kolom terakhir (paling kanan)
			$max_col_name = PHPExcel_Cell::stringFromColumnIndex($max_col-1); // nama kolom terakhir (paling kanan)
			
			
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			foreach($PenjualanNasional->detail as $dt_divisi => $merks) {
				$no = 0;
				$grand_total_merk = 0;
				$grand_total_sub = 0;
				$grand_total_kode = 0;
				$grand_total_bulan = 0;
				foreach($merks as $dt_merk => $subs) {
					$total_merk = 0;
					foreach($subs as $dt_sub => $kodes) {
						$total_sub = 0;
						foreach($kodes as $dt_kode => $details) {
							$total_kode = 0;
							for($bln=1;$bln<=12;$bln++){
								$no++;
								$content_html.= "<tr>";
								$content_html.= "<td>".$no."</td>";
								$content_html.= "<td>".$dt_divisi."</td>";
								$content_html.= "<td>".$dt_merk."</td>";
								$content_html.= "<td>".$dt_sub."</td>";
								$content_html.= "<td>".$dt_kode."</td>";
								$content_html.= "<td>".SUBSTR($this->nama_bulan[intval($bln)],0,3)."</td>";
								if($this->excel_flag == 1){
									$currrow++;
									$currcol = 1;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_divisi);
									$currcol += 1 ;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_merk);
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_sub);
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_kode);
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, SUBSTR($this->nama_bulan[intval($bln)],0,3));
								}
								$sub_total = 0;
								
								foreach($PenjualanNasional->header as $hd){
									$ada = 0;
									foreach ($details as $detail) {
										if($bln == $detail->Bln && $hd==$detail->Cabang){
											$sub_total += ($detail->Total);
											$total_cabang_kode[$hd] += ($detail->Total);
											$content_html.= "<td class='td-right'>".number_format($detail->Total,0)."</td>";
											
											//debug total
											/*if ($dt_kode=="PS-128BIT") {
												echo($hd." : ".number_format($detail->Total)."<br>");
												echo("Subtotal : ".number_format($sub_total)."<br>");
											}*/
											
											if($this->excel_flag == 1){
												$currcol += 1;
												$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->Total);
												$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
											}
											$ada = 1;											
										}
									}
									if($ada == 0){
										$content_html.= "<td class='td-right'>0</td>";
										if($this->excel_flag == 1){
											$currcol += 1;
											$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
											$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
										}
									}
								}
								
								$content_html.= "<td class='td-right td-bold'>".number_format($sub_total,0)."</td><td></td><td></td><td></td>";
								
								if($this->excel_flag == 1){	
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, $sub_total);
									$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
									$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
								}
								$content_html.= "</tr>";
								$grand_total_bulan += $sub_total;
								$total_kode += $sub_total;
							}
							
							$content_html.= "<tr style='background-color:#".$warna_table_kode.";'><td></td><td colspan='4' class='td-right'>TOTAL ".$dt_kode."</td><td></td>";
							
							$currrow++;
							if($this->excel_flag == 1){
								$currcol = 2;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_kode);
								$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
								$sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal($alignment_right);
								$sheet->setCellValueByColumnAndRow($max_col-2, $currrow, $total_kode);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-3).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_kode);
								$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
							}
							
							$currcol = 6;
							foreach($PenjualanNasional->header as $hd){
								$total_cabang_sub[$hd] += ($total_cabang_kode[$hd]);
								$content_html.= "<td class='td-right td-bold'>".number_format($total_cabang_kode[$hd],0)."</td>";
								if($this->excel_flag == 1){
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_cabang_kode[$hd]);
									$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
									$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
								}
								$total_cabang_kode[$hd] = 0;
							}
							
							$content_html.="<td></td><td class='td-right td-bold'>".number_format($total_kode,0)."</td><td></td><td></td></tr>";
							
							$grand_total_kode += $total_kode;
							$total_sub += $total_kode;
						}
						
						$content_html.= "<tr style='background-color:#".$warna_table_sub.";'><td></td><td colspan='4' class='td-right'>TOTAL ".$dt_divisi." - ".$dt_merk." - ".$dt_sub."</td><td></td>";
						
						$currrow++;
						if($this->excel_flag == 1){
							$currcol = 2;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi.' - '.$dt_merk.' - '.$dt_sub);
							$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
							$sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal($alignment_right);
							$sheet->setCellValueByColumnAndRow($max_col-1, $currrow, $total_sub);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-2).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_sub);
							$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
						}
						$currcol = 6;
						foreach($PenjualanNasional->header as $hd){
							$total_cabang_merk[$hd] += ($total_cabang_sub[$hd]);
							$content_html.= "<td class='td-right td-bold'>".number_format($total_cabang_sub[$hd],0)."</td>";
							if($this->excel_flag == 1){
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_cabang_sub[$hd]);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
							}
							$total_cabang_sub[$hd] = 0;
						}
						$content_html.="<td></td><td></td><td class='td-right td-bold'>".number_format($total_sub,0)."</td><td></td></tr>";
						
						$grand_total_sub += $total_sub;
						$total_merk += $total_sub;
						
					}
					$content_html.= "<tr style='background-color:#".$warna_table_merk.";'><td></td><td colspan='4' class='td-right'>TOTAL ".$dt_divisi." - ".$dt_merk."</td><td></td>";
					
					$currrow++;
					if($this->excel_flag == 1){
						$currcol = 2;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi.' - '.$dt_merk);
						$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
						$sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal($alignment_right);
						$sheet->setCellValueByColumnAndRow($max_col, $currrow, $total_merk);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_merk);
						$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
					}
					
					$currcol = 6;
					foreach($PenjualanNasional->header as $hd){
						$total_cabang_divisi[$hd] += ($total_cabang_merk[$hd]);
						$content_html.= "<td class='td-right td-bold'>".number_format($total_cabang_merk[$hd],0)."</td>";
						if($this->excel_flag == 1){
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_cabang_merk[$hd]);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
						}
						$total_cabang_merk[$hd] = 0;
					}
					$content_html.= "<td></td><td></td><td></td><td class='td-right td-bold'>".number_format($total_merk,0)."</td></tr>";
					
					$grand_total_merk += $total_merk;
				}
				
				
				$content_html.= "<tr style='background-color:#".$warna_table_divisi.";'><td></td><td colspan='4' class='td-right'>TOTAL ".$dt_divisi."</td><td></td>";
				
				$currrow++;
				if($this->excel_flag == 1){
					$currcol = 2;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi);
					$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
					$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_divisi);
					$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);
					$sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal($alignment_right);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				}
				$currcol = 6;
				foreach($PenjualanNasional->header as $hd){
					$grand_total[$hd] += ($total_cabang_divisi[$hd]);
					$content_html.= "<td class='td-right td-bold'>".number_format($total_cabang_divisi[$hd],0)."</td>";
					if($this->excel_flag == 1){
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_cabang_divisi[$hd]);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
					}
					
					$total_cabang_divisi[$hd] = 0;
				}
				
				$content_html.= "
				<td class='td-right td-bold'>".number_format($grand_total_bulan,0)."</td>
				<td class='td-right td-bold'>".number_format($grand_total_kode,0)."</td>
				<td class='td-right td-bold'>".number_format($grand_total_sub,0)."</td>
				<td class='td-right td-bold'>".number_format($grand_total_merk,0)."</td>
				</tr>";
				
				if($this->excel_flag == 1){
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_bulan);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_kode);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_sub);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_merk);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				}
				
			}
			
			$content_html.= "</tr>";
			$content_html.= "</table>";
			
			if($this->excel_flag == 1){
				// rata tengah header
				$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
				$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
				
				$sheet->getStyle('A5:C5')->getAlignment()->setHorizontal($alignment_left);			
				$sheet->getStyle('D5:'.$max_col_name.'5')->getAlignment()->setHorizontal($alignment_center);
				
				
				$sheet->getStyle('A5:'.$max_col_name.'5')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
				$sheet->getStyle('A5:'.$max_col_name.'5')->getAlignment()->setWrapText(true); 
				
				// bold
				$sheet->getStyle("A1:".$max_col_name."5")->getFont()->setBold(true);
				// border
				$styleArray = [
				'borders' => [
				'allBorders' => [
				'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
				],
				];
				$sheet ->getStyle('A5:'.$max_col_name.$currrow)->applyFromArray($styleArray);
				// $sheet->freezePane('A6');
				$sheet->setSelectedCell('A1');
				$filename=$p_nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 

				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit();
			}
			
			$content_html.= "</div>";
			$content_html.= "</body></html>";
			
			$data['title'] = $p_nama_laporan;
			$data['content_html'] = $content_html;
			
			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->load->view('LaporanResultView',$data);	
		}
		
		
		public function PreviewC01($p_laporan,$p_nama_laporan,$p_kategori, $p_divisi, $p_type, $p_bln, $p_thn, $ex_cash, $ex_bass, $p_wilayah, $supl="", $params)
		{
			
			$data = array();
			$api = 'APITES';
			$streamContext = stream_context_create(
				array('http'=>
					array(
						'timeout' => $this->max_exec_time,  //600 seconds = 10 menit
						'header' => 'Accept-Encoding: gzip, deflate', // Menambahkan header Accept-Encoding
					)
				)
			);

			// $URL = $this->apiurl."ProsesHarianC01?api=".$api."&p_laporan=".urlencode($p_laporan)."&p_bln=".urlencode($p_bln)."&p_thn=".urlencode($p_thn)."&p_kategori=".urlencode($p_kategori)."&p_divisi=".urlencode($p_divisi)."&p_type=".urlencode($p_type)."&ex_cash=".urlencode($ex_cash)."&ex_bass=".urlencode($ex_bass);
			$URL = $this->apiurl."ProsesHarianC01?api=".$api."&p_laporan=".urlencode($p_laporan)."&p_bln=".urlencode($p_bln).
					"&p_thn=".urlencode($p_thn)."&p_kategori=".urlencode($p_kategori)."&p_divisi=".urlencode($p_divisi)."&p_type=".urlencode($p_type).
					"&ex_cash=".urlencode($ex_cash)."&ex_bass=".urlencode($ex_bass)."&supl=".urlencode($supl);
			
			// die($URL);
			$response = file_get_contents($URL, false, $streamContext);
			$response = $this->_decodeGzip($response);
			$json = json_decode($response);
			if(count($json)==0){
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				
				exit('Tidak ada data');
			}
			
			
			$this->LoadWilayahGroup();
			
			$details = array();
			// $array_cabang = array();
			
			foreach($json as $new) {
			
			
				// Mencari nama wilayahgroup by wilayah dan kota.
				$WilayahGroup = $this->GetWilayahGroup(trim($new->Partner_Type), trim($new->Wilayah));
				if($WilayahGroup=='') exit('Silahkan isi konfigurasi di Konfigurasi Wilayah Group Report untuk partner type:'.$new->Partner_Type.' dan wilayah:'.$new->Wilayah);
				if($p_wilayah=='ALL' || $p_wilayah==$WilayahGroup){
				$detail = array(
					// 'Cabang'=>$WilayahGroup,
					'Bln'=>$new->Bln,
					'Total'=>$new->Total,
					'Avg'=>$new->Avg
				);
				$divisi = trim($new->Divisi);
				$merk = trim($new->Merk);
				$SubKategori_Brg = trim($new->SubKategori_Brg);
				$kode = trim($new->Kode);
				
				// cek apakah array sudah ada untuk group divisi, merk, cabang,
				if(ISSET($details[$divisi][$merk][$SubKategori_Brg][$kode])){
					$ada = 0;
					// maka cek apakah sudah ada tanggal nya
					foreach($details[$divisi][$merk][$SubKategori_Brg][$kode] as $k => $val){
						//jika ada tanggal yg sama untuk wilayah group yg sama, maka akan di-sum
						// if($WilayahGroup == $val['Cabang'] && $new->Bln == $val['Bln']){ 
						if($new->Bln == $val['Bln']){ 
							$details[$divisi][$merk][$SubKategori_Brg][$kode][$k]['Total'] += $new->Total; 
							$details[$divisi][$merk][$SubKategori_Brg][$kode][$k]['Avg'] += $new->Avg; 
							$ada = 1;
						}
					}
					if($ada == 0){ // jika tidak ada tanggalnya , maka tambah data baru
						$details[$divisi][$merk][$SubKategori_Brg][$kode][] = $detail;
					}
				}
				else{
					// jika tidak ada array-nya, maka tambah data baru juga
					$details[$divisi][$merk][$SubKategori_Brg][$kode][] = $detail;
				}
				
				// array_push($array_cabang, $WilayahGroup);
				ksort($details[$divisi][$merk]); //sort kategori
				ksort($details[$divisi][$merk][$SubKategori_Brg]); //sort kode
				}
			}
			
			// $array_cabang = array_unique($array_cabang);
			// sort($array_cabang);
			// $PenjualanNasional = array('header'=>$array_cabang, 'detail'=>$details);
			$PenjualanNasional = $details;
			$PenjualanNasional = json_decode(json_encode($PenjualanNasional));
			
			
			// var_dump($PenjualanNasional);
			// exit;
					
			
			$warna_table_header = 'f2f2f2';
			$warna_table_footer = 'b7dee8';
			
			$warna_table_divisi = 'c4bd97';
			$warna_table_merk = 'a6a6a6';
			$warna_table_sub = 'd9d9d9';
			
			
			$warna_table_grandtotal = 'fabf8f';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			if($p_type=='ALL'){
				$p_type = "LOKAL & IMPORT";
			}
			
			if($p_kategori=='P'){
				$p_kategori = "PRODUK";
			}
			else if($p_kategori=='S'){
				$p_kategori = "SPAREPART";
			}
			else{
				$p_kategori = "PRODUK & SPAREPART";
			}
			
			$exclude_cash = '';
			if($ex_cash==1){
				$exclude_cash .= '-EXCLUDE CASH KONSUMEN ';
			}
			$exclude_bass = '';
			if($ex_bass==1){
				$exclude_bass .= '-EXCLUDE BASS '; 
			}
			
			$content_html = "<html>";
			$content_html = "<head>";
			
			$content_html = "<style>
			body{font-family:'Calibri',Arial,sans-serif;}
			table{padding:0;margin:0;border-collapse:collapse}
			td, th { border:1px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "	<div><h2>".$p_nama_laporan."</h2>WILAYAH: ".$p_wilayah."</div>";
			$content_html.= "	<div style='float:left;width:50%'>PERIODE: ".$p_thn."</div>";
			$content_html.= "	<div style='float:left;width:30%'>DIVISI: ".$p_divisi."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_cash."</div>";
			$content_html.= "	<div style='float:left;width:50%'>KATEGORI: ".$p_kategori."</div>";
			$content_html.= "	<div style='float:left;width:30%'>".$p_type."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_bass."</div>";
			$content_html.= "	<div style='clear:both'></div>";
			$content_html.= "</div>";	//close div_header
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($p_nama_laporan, 0, 31));
				$sheet->setCellValue('A1', $p_nama_laporan);
				$sheet->getStyle('A1')->getFont()->setSize(12);
				$sheet->setCellValue('A2', 'WILAYAH : '.$p_wilayah);
				$sheet->setCellValue('A3', 'PERIODE : '.$p_thn);
				$sheet->setCellValue('A4', 'KATEGORI : '.$p_kategori);
				$sheet->setCellValue('I3', 'DIVISI : '.$p_divisi);
				$sheet->setCellValue('I4', $p_type);
				$sheet->setCellValue('K3', $exclude_cash);
				$sheet->setCellValue('K4', $exclude_bass);
			}
			
			
			$content_html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
			
			$content_html.= "<table style='font-size:10pt!important'>";
			$content_html.= "<tr style='background-color:#".$warna_table_header.";'>";
			
			$content_html.= "	<th style='min-width:20px'>No</th>";
			$content_html.= "	<th style='min-width:100px'>Divisi</th>";
			$content_html.= "	<th style='min-width:100px'>Merk</th>";
			$content_html.= "	<th style='min-width:100px'>Kategori</th>";
			$content_html.= "	<th style='min-width:100px'>Kode Barang</th>";
			
			$currcol = 0;
			$currrow = 6;
			
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No');
				$sheet->getColumnDimension('A')->setWidth(5);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
				$sheet->getColumnDimension('B')->setWidth(15);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
				$sheet->getColumnDimension('C')->setWidth(15);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kategori');
				$sheet->getColumnDimension('D')->setWidth(25);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Barang');
				$sheet->getColumnDimension('E')->setWidth(25);
			}
			
			
			$total_cabang_divisi = array();
			$total_cabang_merk = array();
			$total_cabang_sub = array();
			$grand_total = array();
			
			foreach($this->nama_bulan as $hd => $bln){
				$total_cabang_divisi[$hd] = 0;
				$total_cabang_merk[$hd] = 0;
				$total_cabang_sub[$hd] = 0;
				$grand_total[$hd] = 0;
				if($hd>0){
					$content_html.= "<th width='80px'>".SUBSTR($bln,0,3)."</th>";
					if($this->excel_flag == 1){
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, SUBSTR($bln,0,3));
						$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
					}
				}
			}
			
			$content_html.= "<th width='80px'>SUBTOTAL</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SUBTOTAL');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
			}
			$content_html.= "<th width='80px'>AVERAGE ".$p_bln."</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'AVERAGE '.$p_bln);
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
			}
			$content_html.= "<th width='80px'>AVERAGE ".$p_thn."</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'AVERAGE '.$p_thn);
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
			}
			// $content_html.= "<th width='80px'>% AVERAGE</th>";
			// if($this->excel_flag == 1){
				// $currcol += 1;
				// $sheet->setCellValueByColumnAndRow($currcol, $currrow, '% AVERAGE');
				// $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
			// }
			
			$content_html.= "</tr>";
			
			$max_col = $currcol; // index kolom terakhir (paling kanan)
			$max_col_name = PHPExcel_Cell::stringFromColumnIndex($max_col-1); // nama kolom terakhir (paling kanan)
			
			
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			foreach($PenjualanNasional as $dt_divisi => $merks) {
				$no = 0;
				// $total_divisi = 0;
				foreach($merks as $dt_merk => $subs) {
					// $total_merk = 0;
					foreach($subs as $dt_sub => $kodes) {
						// $total_sub = 0;
						foreach($kodes as $dt_kode => $details) {
							$no++;
							$avg_lalu = $details[0]->Avg;
							$content_html.= "<tr>";
							$content_html.= "<td>".$no."</td>";
							$content_html.= "<td>".$dt_divisi."</td>";
							$content_html.= "<td>".$dt_merk."</td>";
							$content_html.= "<td>".$dt_sub."</td>";
							$content_html.= "<td>".$dt_kode."</td>";
							if($this->excel_flag == 1){
								$currrow++;
								$currcol = 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_divisi);
								$currcol += 1 ;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_merk);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_sub);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_kode);
							}
							$sub_total = 0;
							
							//echo($dt_kode."<br>");
							//echo(json_encode($details));
							//echo("<br>");
							
							$flag_start = 0;
							for($hd=1;$hd<=12;$hd++){
								$ada = 0;
								
								foreach ($details as $detail) {
									if($hd==$detail->Bln){
										
										//$sub_total += intval($detail->Total);
										$sub_total += ($detail->Total);
										$total_cabang_sub[$hd] += ($detail->Total);
										$content_html.= "<td class='td-right'>".number_format($detail->Total,0)."</td>";
										
										//debug total
										/*if ($dt_kode=="PS-128BIT") {
											echo($hd." : ".number_format($detail->Total)."<br>");
											echo("Subtotal : ".number_format($sub_total)."<br>");
										}*/
										
										if($this->excel_flag == 1){
											$currcol += 1;
											$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->Total);
											$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
										}
										$ada = 1;
										
										if($detail->Total > 0){
											if($flag_start == 0)
											{
												$flag_start = $hd; //rekam bulan aktif/launching , variabel dipakai nanti jika avg_tahun_lalu = 0;
											}
										}
										
									}
								}
								if($ada == 0){
									$content_html.= "<td class='td-right'>0</td>";
									if($this->excel_flag == 1){
										$currcol += 1;
										$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
										$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
									}
								}
							}
							$pembagi_start = 1;
							if($avg_lalu==0){
								$pembagi_start = $flag_start;
							}
							$jumlah_bulan = (date('Y') > ($p_thn)) ? 12 : date('m');
							$pembagi = $jumlah_bulan - $pembagi_start + 1;
							$avg = $sub_total/$pembagi;
							// $avg_persen = ($avg_lalu==0) ? 0 : (($avg-$avg_lalu)/$avg_lalu)*100;
							$content_html.= "
							<td class='td-right td-bold'>".number_format($sub_total,0)."</td>
							<td class='td-right'>".number_format($avg_lalu,0)."</td>
							<td class='td-right'>".number_format($avg,0)."</td>";
							// <td class='td-right'>".number_format($avg_persen,2)."</td>
							
							if($this->excel_flag == 1){	
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $sub_total);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $avg_lalu);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $avg);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
								
								
								
								// $currcol += 1;
								// $sheet->setCellValueByColumnAndRow($currcol, $currrow, $avg_lalu);
								// $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								
								
								
								// $currcol += 1;
								// $sheet->setCellValueByColumnAndRow($currcol, $currrow, $avg_persen);
								// $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0.00');
								// $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
							}
							$content_html.= "</tr>";
							// $total_sub += $sub_total;
						}
						
						
						$currrow++;
						$currcol = 5;
						$content_html.= "<tr style='background-color:#".$warna_table_sub.";'><td></td><td colspan='4' class='td-right'>TOTAL ".$dt_divisi." - ".$dt_merk." - ".$dt_sub."</td>";
						
						
						for($hd=1;$hd<=12;$hd++){
							$total_cabang_merk[$hd] += ($total_cabang_sub[$hd]);
							$content_html.= "<td class='td-right td-bold'>".number_format($total_cabang_sub[$hd],0)."</td>";
							if($this->excel_flag == 1){
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_cabang_sub[$hd]);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
							}
							$total_cabang_sub[$hd] = 0;
						}
						
						
						$content_html.="<td></td><td class='td-right td-bold'></td><td></td></tr>";
						
						
						if($this->excel_flag == 1){
							$currcol = 2;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi.' - '.$dt_merk.' - '.$dt_sub);
							$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
							$sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal($alignment_right);
							// $sheet->setCellValueByColumnAndRow($max_col-2, $currrow, $total_sub);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-3).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_sub);
							$sheet->getStyle('B'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
						}
						// $grand_total_sub += $total_sub;
						// $total_merk += $total_sub;
						
						
					}
					$currrow++;
					$currcol = 5;
					$content_html.= "<tr style='background-color:#".$warna_table_merk.";'><td></td><td colspan='4' class='td-right'>TOTAL ".$dt_divisi." - ".$dt_merk."</td>";
					
					for($hd=1;$hd<=12;$hd++){
						$total_cabang_divisi[$hd] += ($total_cabang_merk[$hd]);
						$content_html.= "<td class='td-right td-bold'>".number_format($total_cabang_merk[$hd],0)."</td>";
						if($this->excel_flag == 1){
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_cabang_merk[$hd]);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
						}
						
						$total_cabang_merk[$hd] = 0;
					}
					$content_html.= "<td></td><td></td><td class='td-right td-bold'></td></tr>";
					
					if($this->excel_flag == 1){
						$currcol = 2;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi.' - '.$dt_merk);
						$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
						$sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal($alignment_right);
						// $sheet->setCellValueByColumnAndRow($max_col-1, $currrow, $total_merk);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-2).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_merk);
						$sheet->getStyle('B'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
					}
					// $grand_total_merk += $total_merk;
					// $total_divisi += $total_merk;
				}
				
				
				$currrow++;
				$currcol = 5;
				$content_html.= "<tr style='background-color:#".$warna_table_divisi.";'><td></td><td colspan='4' class='td-right'>TOTAL ".$dt_divisi."</td>";
				
				
				for($hd=1;$hd<=12;$hd++){
					$grand_total[$hd] += ($total_cabang_divisi[$hd]);
					$content_html.= "<td class='td-right td-bold'>".number_format($total_cabang_divisi[$hd],0)."</td>";
					if($this->excel_flag == 1){
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_cabang_divisi[$hd]);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
					}
					
					$total_cabang_divisi[$hd] = 0;
				}
				
				$content_html.="<td></td><td></td><td></td></tr>";
				
				if($this->excel_flag == 1){
					$currcol = 2;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi);
					$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
					$sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal($alignment_right);
					// $sheet->setCellValueByColumnAndRow($max_col, $currrow, $total_divisi);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_divisi);
					$sheet->getStyle('B'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
				}
				// $grand_total_divisi += $total_divisi;
			}
			
			
			// $content_html.= "<tr style='background-color:#".$warna_table_grandtotal.";'>";
			// $content_html.= "<td></td><td colspan='4'>GRANDTOTAL</td>";
			// $content_html.= "<td></td>";
			
			// if($this->excel_flag == 1){
			// $currrow++;
			// $currcol = 2;
			// $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');
			// $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
			// $sheet->getStyle('B'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
			// }
			
			// $currcol += 3;
			// $grand_total_all = 0;
			
			
			// for($hd=1;$hd<=12;$hd++){
			// $content_html.= "<td class='td-right td-bold'>".number_format($grand_total[$hd])."</td>";
			
			// if($this->excel_flag == 1){
			// $currcol += 1;
			// $sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total[$hd]);
			// $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			// $sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_grandtotal);
			// }
			// $grand_total_all += $grand_total[$hd];
			// }
			// $content_html.= "<td class='td-right td-bold'></td>";
			// $content_html.= "<td class='td-right td-bold'></td>";
			// $content_html.= "<td class='td-right td-bold'></td>";
			// $content_html.= "<td class='td-right td-bold'></td>";
			
			if($this->excel_flag == 1){
				// $currcol += 1;
				// $sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_all);
				// $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				// $currcol += 1;
				// $sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_sub);
				// $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				// $currcol += 1;
				// $sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_merk);
				// $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				// $currcol += 1;
				// $sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_divisi);
				// $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			}
			
			$content_html.= "</tr>";
			$content_html.= "</table>";
			
			if($this->excel_flag == 1){
				// rata tengah header
				
				$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
				$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
				
				$sheet->getStyle('A6:C6')->getAlignment()->setHorizontal($alignment_left);			
				$sheet->getStyle('D6:'.$max_col_name.'6')->getAlignment()->setHorizontal($alignment_center);
				
				
				$sheet->getStyle('A6:'.$max_col_name.'6')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
				// $sheet->getStyle('A'.$currrow.':'.$max_col.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_footer);
				
				// bold
				$sheet->getStyle("A1:".$max_col_name."6")->getFont()->setBold(true);
				// border
				$styleArray = [
				'borders' => [
				'allBorders' => [
				'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
				],
				];
				$sheet ->getStyle('A6:'.$max_col_name.$currrow)->applyFromArray($styleArray);
				// $sheet->freezePane('A6');
				$sheet->setSelectedCell('A1');
				$filename=$p_nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 

				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit();
			}
			
			$content_html.= "</div>";
			$content_html.= "</body></html>";
			
			$data['title'] = $p_nama_laporan;
			$data['content_html'] = $content_html;
			
			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->load->view('LaporanResultView',$data);
		}
		
		public function PreviewA01A05($p_laporan,$p_nama_laporan,$p_kategori, $p_divisi, $p_type, $p_bln, $p_thn,$ex_cash,$ex_bass, $supl="", $params)
		{
			
			$data = array();
			$api = 'APITES';

			$streamContext = stream_context_create(
				array('http'=>
					array(
						'timeout' => $this->max_exec_time,  //600 seconds = 10 
						'header' => 'Accept-Encoding: gzip, deflate', // Menambahkan header Accept-Encodingmenit
					)
				)
			);
			$url = $this->apiurl."ProsesHarianA01A05?api=".$api.
									"&p_laporan=".urlencode($p_laporan)."&p_bln=".urlencode($p_bln)."&p_thn=".urlencode($p_thn).
									"&p_kategori=".urlencode($p_kategori)."&p_divisi=".urlencode($p_divisi)."&p_type=".urlencode($p_type).
									"&ex_cash=".urlencode($ex_cash)."&ex_bass=".urlencode($ex_bass)."&supl=".urlencode($supl);
			$response = file_get_contents($url, false, $streamContext);
			$response = $this->_decodeGzip($response);

			$PenjualanNasional = json_decode($response);

			if($PenjualanNasional==''){
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit('Tidak ada data');
			}
			
			$warna_table_header = 'f2f2f2';
			$warna_table_footer = 'b7dee8';
			$warna_table_grandtotal = '7CFC00';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			if($p_type=='ALL'){
				$p_type = "LOKAL & IMPORT";
			}
			
			if($p_kategori=='P'){
				$p_kategori = "PRODUK";
			}
			else if($p_kategori=='S'){
				$p_kategori = "SPAREPART";
			}
			else{
				$p_kategori = "PRODUK & SPAREPART";
			}
			
			$exclude_cash = '';
			if($ex_cash==1){
				$exclude_cash .= '-EXCLUDE CASH KONSUMEN ';
			}
			$exclude_bass = '';
			if($ex_bass==1){
				$exclude_bass .= '-EXCLUDE BASS '; 
			}
			
			$content_html = "<html>";
			$content_html = "<head>";
			
			$content_html = "<style>
			body{font-family:'Calibri',Arial,sans-serif;}
			table{padding:0;margin:0;border-collapse:collapse}
			td, th { border:1px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "	<div><h2>".$p_nama_laporan."</h2></div>";
			$content_html.= "	<div style='float:left;width:50%'>PERIODE: ".$this->nama_bulan[intval($p_bln)]." ".$p_thn."</div>";
			$content_html.= "	<div style='float:left;width:30%'>DIVISI: ".$p_divisi."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_cash."</div>";
			$content_html.= "	<div style='float:left;width:50%'>KATEGORI: ".$p_kategori."</div>";
			$content_html.= "	<div style='float:left;width:30%'>".$p_type."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_bass."</div>";
			$content_html.= "	<div style='clear:both'></div>";
			$content_html.= "</div>";	//close div_header
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($p_nama_laporan, 0, 31));
				$sheet->setCellValue('A1', $p_nama_laporan);
				$sheet->getStyle('A1')->getFont()->setSize(12);
				$sheet->setCellValue('A2', 'PERIODE : '.$this->nama_bulan[intval($p_bln)].' '.$p_thn);
				$sheet->setCellValue('A3', 'KATEGORI : '.$p_kategori);
				$sheet->setCellValue('I2', 'DIVISI : '.$p_divisi);
				$sheet->setCellValue('I3', $p_type);
				$sheet->setCellValue('K2', $exclude_cash);
				$sheet->setCellValue('K3', $exclude_bass);
			}
			
			
			$content_html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
			
			$content_html.= "<table style='font-size:10pt!important'>";
			$content_html.= "<tr style='background-color:#".$warna_table_header.";'>";
			
			$content_html.= "	<th style='min-width:100px'>Divisi</th>";
			$content_html.= "	<th style='min-width:100px'>Merk</th>";
			$content_html.= "	<th style='min-width:100px'>Cabang</th>";
			
			$currcol = 1;
			$currrow = 5;
			
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
				$sheet->getColumnDimension('A')->setWidth(15);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
				$sheet->getColumnDimension('B')->setWidth(15);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Cabang');
				$sheet->getColumnDimension('C')->setWidth(15);
				$currcol += 1;
			}
			
			$date = mktime(0,0,0,$p_bln,1,$p_thn);
			$days= date('t',$date);
			
			$grand_total = array();
			$grand_total[0] = 0;
			
			for($i=1; $i<=$days;$i++){
				$grand_total[$i] = 0;
				$content_html.= "<th width='100px'>".str_pad($i, 2, '0', STR_PAD_LEFT)."</th>";
				if($this->excel_flag == 1){
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $i);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('00');
					$currcol += 1;
				}
			}
			
			$content_html.= "<th width='100px'>SUBTOTAL</th>";
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SUBTOTAL');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
				$currcol += 1;
			}
			
			$content_html.= "</tr>";
			
			$max_col = $currcol-2;
			
			foreach($PenjualanNasional as $dt_divisi => $subs) {
				foreach($subs as $dt_merk => $details) {
					$content_html.= "<tr>";
					$content_html.= "<td>".$dt_divisi."</td>";
					$content_html.= "<td>".$dt_merk."</td>";
					$content_html.= "<td>".$details[0]->Cabang."</td>";
					if($this->excel_flag == 1){
						$currrow++;
						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_divisi);
					}
					if($this->excel_flag == 1){
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_merk);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'ALL');
						$currcol += 1;
					}
					$sub_total= 0;
					for($i=1; $i<=$days;$i++){
						$ada = 0;
						foreach ($details as $detail) {
							if($i==$detail->Tgl){
								$sub_total += ($detail->Total);
								$grand_total[$i] += ($detail->Total);
								$content_html.= "<td class='td-right'>".number_format($detail->Total,0)."</td>";
								
								
								if($this->excel_flag == 1){
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->Total);
									$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
									$currcol += 1;
								}
								$ada = 1;
							}
						}
						if($ada == 0){
							$content_html.= "<td class='td-right'>0</td>";
							if($this->excel_flag == 1){
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$currcol += 1;
							}
						}
					}
					$content_html.= "<td class='td-right td-bold'>".number_format($sub_total,0)."</td>";
					
					if($this->excel_flag == 1){
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $sub_total);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);	
						$currcol += 1;
					}
					$content_html.= "</tr>";
				}
			}
			
			$currrow++;
			$currcol = 1;
			$content_html.= "<tr style='background-color:#".$warna_table_footer.";'>";
			$content_html.= "<td colspan='3'>GRANDTOTAL</td>";
			
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+2 , $currrow);
				$currcol += 3;
			}
			
			$grand_total_all = 0;
			for($i=1; $i<=$days;$i++){
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total[$i])."</td>";
				
				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total[$i]);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$currcol += 1;
				}
				$grand_total_all += $grand_total[$i];
			}
			$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
			}
			
			$content_html.= "</tr>";
			$content_html.= "</table>";
			
			if($this->excel_flag == 1){
				$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
				// rata tengah header
				
				$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
				$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
				
				$sheet->getStyle('A5:C5')->getAlignment()->setHorizontal($alignment_left);			
				$sheet->getStyle('D5:'.$max_col.'5')->getAlignment()->setHorizontal($alignment_center);
				
				$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
				
				$sheet->getStyle('A5:'.$max_col.'5')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
				$sheet->getStyle('A'.$currrow.':'.$max_col.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_footer);
				
				// bold
				$sheet->getStyle("A1:".$max_col."5")->getFont()->setBold(true);
				$sheet->getStyle("A".$currrow.":".$max_col.$currrow)->getFont()->setBold(true);	
				// border
				$styleArray = [
				'borders' => [
				'allBorders' => [
				'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
				],
				];
				$sheet ->getStyle('A5:'.$max_col.$currrow)->applyFromArray($styleArray);
				
				$sheet->setSelectedCell('A1');
				
				$filename=$p_nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 

				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit();
			}
			
			$content_html.= "</div>";
			$content_html.= "</body></html>";
			
			$data['title'] = $p_nama_laporan;
			$data['content_html'] = $content_html;
			
			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->load->view('LaporanResultView',$data);
		}

		public function PreviewA01($p_laporan,$p_nama_laporan,$p_kategori, $p_divisi, $p_type, $p_bln, $p_thn,$ex_cash,$ex_bass, $supl="", $params)
		{
			
			$data = array();
			$api = 'APITES';

			// $streamContext = stream_context_create(
			// 	array('http'=>
			// 		array(
			// 			'timeout' => $this->max_exec_time,  //600 seconds = 10 menit
			// 		)
			// 	)
			// );
			// $url = $this->apiurl."ProsesHarianA01A05?api=".$api.
			// 						"&p_laporan=".urlencode($p_laporan)."&p_bln=".urlencode($p_bln)."&p_thn=".urlencode($p_thn).
			// 						"&p_kategori=".urlencode($p_kategori)."&p_divisi=".urlencode($p_divisi)."&p_type=".urlencode($p_type).
			// 						"&ex_cash=".urlencode($ex_cash)."&ex_bass=".urlencode($ex_bass)."&supl=".urlencode($supl);
			// $PenjualanNasional = json_decode(file_get_contents($url, false, $streamContext));
			// //die(json_encode($PenjualanNasional));
			// //var_dump($PenjualanNasional);
			// // echo $this->apiurl."ProsesHarianA01?api=".$api."&p_laporan=".urlencode($p_laporan)."&p_bln=".urlencode($p_bln)."&p_thn=".urlencode($p_thn)."&p_kategori=".urlencode($p_kategori)."&p_divisi=".urlencode($p_divisi)."&p_type=".urlencode($p_type)."&ex_cash=".urlencode($ex_cash)."&ex_bass=".urlencode($ex_bass);
			// // exit;

			$data["api"] = "APITES";
			$data["p_laporan"] = $p_laporan;
			$data["p_bln"] = $p_bln;
			$data["p_thn"] = $p_thn;
			$data["p_kategori"] = $p_kategori;
			$data["p_divisi"] = $p_divisi;
			$data["p_type"] = $p_type;
			$data["ex_cash"] = $ex_cash;
			$data["ex_bass"] = $ex_bass;
			$data["supl"] = $supl;
			$data = json_encode($data);

			$url = $this->apiurl."ProsesHarianA01A05v2";

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => $this->max_exec_time,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $data,
				CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));		
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);

			$PenjualanNasional = json_decode($response, true);

			if($PenjualanNasional==''){
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit('Tidak ada data');
			}
			
			$warna_table_header = 'f2f2f2';
			$warna_table_footer = 'b7dee8';
			$warna_table_grandtotal = '7CFC00';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			if($p_type=='ALL'){
				$p_type = "LOKAL & IMPORT";
			}
			
			if($p_kategori=='P'){
				$p_kategori = "PRODUK";
			}
			else if($p_kategori=='S'){
				$p_kategori = "SPAREPART";
			}
			else{
				$p_kategori = "PRODUK & SPAREPART";
			}
			
			$exclude_cash = '';
			if($ex_cash==1){
				$exclude_cash .= '-EXCLUDE CASH KONSUMEN ';
			}
			$exclude_bass = '';
			if($ex_bass==1){
				$exclude_bass .= '-EXCLUDE BASS '; 
			}
			
			$content_html = "<html>";
			$content_html = "<head>";
			
			$content_html = "<style>
			body{font-family:'Calibri',Arial,sans-serif;}
			table{padding:0;margin:0;border-collapse:collapse}
			td, th { border:1px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "	<div><h2>".$p_nama_laporan."</h2></div>";
			$content_html.= "	<div style='float:left;width:50%'>PERIODE: ".$this->nama_bulan[intval($p_bln)]." ".$p_thn."</div>";
			$content_html.= "	<div style='float:left;width:30%'>DIVISI: ".$p_divisi."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_cash."</div>";
			$content_html.= "	<div style='float:left;width:50%'>KATEGORI: ".$p_kategori."</div>";
			$content_html.= "	<div style='float:left;width:30%'>".$p_type."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_bass."</div>";
			$content_html.= "	<div style='clear:both'></div>";
			$content_html.= "</div>";	//close div_header
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($p_nama_laporan, 0, 31));
				$sheet->setCellValue('A1', $p_nama_laporan);
				$sheet->getStyle('A1')->getFont()->setSize(12);
				$sheet->setCellValue('A2', 'PERIODE : '.$this->nama_bulan[intval($p_bln)].' '.$p_thn);
				$sheet->setCellValue('A3', 'KATEGORI : '.$p_kategori);
				$sheet->setCellValue('I2', 'DIVISI : '.$p_divisi);
				$sheet->setCellValue('I3', $p_type);
				$sheet->setCellValue('K2', $exclude_cash);
				$sheet->setCellValue('K3', $exclude_bass);
			}
			
			
			$content_html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
			
			$content_html.= "<table style='font-size:10pt!important'>";
			$content_html.= "<tr style='background-color:#".$warna_table_header.";'>";
			
			$content_html.= "	<th style='min-width:100px'>Divisi</th>";
			$content_html.= "	<th style='min-width:100px'>Merk</th>";
			$content_html.= "	<th style='min-width:100px'>Cabang</th>";
			
			$currcol = 1;
			$currrow = 5;
			
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
				$sheet->getColumnDimension('A')->setWidth(15);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
				$sheet->getColumnDimension('B')->setWidth(15);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Cabang');
				$sheet->getColumnDimension('C')->setWidth(15);
				$currcol += 1;
			}
			
			$date = mktime(0,0,0,$p_bln,1,$p_thn);
			$days= date('t',$date);
			
			$grand_total = array();
			$grand_total[0] = 0;
			
			for($i=1; $i<=$days;$i++){
				$grand_total[$i] = 0;
				$content_html.= "<th width='100px'>".str_pad($i, 2, '0', STR_PAD_LEFT)."</th>";
				if($this->excel_flag == 1){
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $i);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('00');
					$currcol += 1;
				}
			}
			
			$content_html.= "<th width='100px'>SUBTOTAL</th>";
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SUBTOTAL');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
				$currcol += 1;
			}
			
			$content_html.= "</tr>";
			
			$max_col = $currcol-2;
			
			foreach($PenjualanNasional as $dt_divisi => $subs) {
				foreach($subs as $dt_merk => $details) {
					$content_html.= "<tr>";
					$content_html.= "<td>".$dt_divisi."</td>";
					$content_html.= "<td>".$dt_merk."</td>";
					$content_html.= "<td>".$details[0]->Cabang."</td>";
					if($this->excel_flag == 1){
						$currrow++;
						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_divisi);
					}
					if($this->excel_flag == 1){
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_merk);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'ALL');
						$currcol += 1;
					}
					$sub_total= 0;
					for($i=1; $i<=$days;$i++){
						$ada = 0;
						foreach ($details as $detail) {
							if($i==$detail->Tgl){
								$sub_total += ($detail->Total);
								$grand_total[$i] += ($detail->Total);
								$content_html.= "<td class='td-right'>".number_format($detail->Total,0)."</td>";
								
								
								if($this->excel_flag == 1){
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->Total);
									$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
									$currcol += 1;
								}
								$ada = 1;
							}
						}
						if($ada == 0){
							$content_html.= "<td class='td-right'>0</td>";
							if($this->excel_flag == 1){
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$currcol += 1;
							}
						}
					}
					$content_html.= "<td class='td-right td-bold'>".number_format($sub_total,0)."</td>";
					
					if($this->excel_flag == 1){
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $sub_total);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);	
						$currcol += 1;
					}
					$content_html.= "</tr>";
				}
			}
			
			$currrow++;
			$currcol = 1;
			$content_html.= "<tr style='background-color:#".$warna_table_footer.";'>";
			$content_html.= "<td colspan='3'>GRANDTOTAL</td>";
			
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+2 , $currrow);
				$currcol += 3;
			}
			
			$grand_total_all = 0;
			for($i=1; $i<=$days;$i++){
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total[$i])."</td>";
				
				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total[$i]);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$currcol += 1;
				}
				$grand_total_all += $grand_total[$i];
			}
			$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
			}
			
			$content_html.= "</tr>";
			$content_html.= "</table>";
			
			if($this->excel_flag == 1){
				$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
				// rata tengah header
				
				$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
				$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
				
				$sheet->getStyle('A5:C5')->getAlignment()->setHorizontal($alignment_left);			
				$sheet->getStyle('D5:'.$max_col.'5')->getAlignment()->setHorizontal($alignment_center);
				
				$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
				
				$sheet->getStyle('A5:'.$max_col.'5')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
				$sheet->getStyle('A'.$currrow.':'.$max_col.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_footer);
				
				// bold
				$sheet->getStyle("A1:".$max_col."5")->getFont()->setBold(true);
				$sheet->getStyle("A".$currrow.":".$max_col.$currrow)->getFont()->setBold(true);	
				// border
				$styleArray = [
				'borders' => [
				'allBorders' => [
				'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
				],
				];
				$sheet ->getStyle('A5:'.$max_col.$currrow)->applyFromArray($styleArray);
				
				$sheet->setSelectedCell('A1');
				
				$filename=$p_nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 

				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit();
			}
			
			$content_html.= "</div>";
			$content_html.= "</body></html>";
			
			$data['title'] = $p_nama_laporan;
			$data['content_html'] = $content_html;
			
			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->load->view('LaporanResultView',$data);
		}
		
		public function PreviewA02A03($p_laporan,$p_nama_laporan,$p_kategori, $p_divisi, $p_type, $p_bln, $p_thn,$ex_cash,$ex_bass, $supl="", $params)
		{
			
			$data = array();
			$api = 'APITES';
			$streamContext = stream_context_create(
				array('http'=>
					array(
						'timeout' => $this->max_exec_time,  //600 seconds = 10 menit
						'header' => 'Accept-Encoding: gzip, deflate', // Menambahkan header Accept-Encoding
					)
				)
			);
			$response = file_get_contents($this->apiurl."ProsesHarianA02A03?api=".$api.
									"&p_laporan=".urlencode($p_laporan)."&p_bln=".urlencode($p_bln)."&p_thn=".urlencode($p_thn).
									"&p_kategori=".urlencode($p_kategori)."&p_divisi=".urlencode($p_divisi)."&p_type=".urlencode($p_type).
									"&ex_cash=".urlencode($ex_cash)."&ex_bass=".urlencode($ex_bass)."&supl=".urlencode($supl),false,$streamContext);
			$response = $this->_decodeGzip($response);
			$PenjualanNasional = json_decode($response);
			//die(json_encode($PenjualanNasional));
			// var_dump($PenjualanNasional);
			// echo $this->apiurl."ProsesHarian?api=".$api."&p_laporan=".urlencode($p_laporan)."&p_bln=".urlencode($p_bln)."&p_thn=".urlencode($p_thn)."&p_kategori=".urlencode($p_kategori)."&p_divisi=".urlencode($p_divisi)."&p_type=".urlencode($p_type);
			// exit;
			
			if($PenjualanNasional==''){
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit('Tidak ada data');
			}
			
			$warna_table_header = 'f2f2f2';
			$warna_table_footer = 'b7dee8';
			$warna_table_merk = 'd9d9d9';
			$warna_table_divisi = 'c4bd97';
			$warna_table_grandtotal = 'b7dee8';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			if($p_type=='ALL'){
				$p_type = "LOKAL & IMPORT";
			}
			
			if($p_kategori=='P'){
				$p_kategori = "PRODUK";
			}
			else if($p_kategori=='S'){
				$p_kategori = "SPAREPART";
			}
			else{
				$p_kategori = "PRODUK & SPAREPART";
			}
			
			$exclude_cash = '';
			if($ex_cash==1){
				$exclude_cash .= '-EXCLUDE CASH KONSUMEN ';
			}
			$exclude_bass = '';
			if($ex_bass==1){
				$exclude_bass .= '-EXCLUDE BASS '; 
			}
			
			$content_html = "<html>";
			$content_html = "<head>";
			
			$content_html = "<style>
			body{font-family:'Calibri',Arial,sans-serif;}
			table{padding:0;margin:0;border-collapse:collapse}
			td, th { border:1px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "	<div><h2>".$p_nama_laporan."</h2></div>";
			$content_html.= "	<div style='float:left;width:50%'>PERIODE: ".$this->nama_bulan[intval($p_bln)]." ".$p_thn."</div>";
			$content_html.= "	<div style='float:left;width:30%'>DIVISI: ".$p_divisi."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_cash."</div>";
			$content_html.= "	<div style='float:left;width:50%'>KATEGORI: ".$p_kategori."</div>";
			$content_html.= "	<div style='float:left;width:30%'>".$p_type."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_bass."</div>";
			$content_html.= "	<div style='clear:both'></div>";
			$content_html.= "</div>";	//close div_header
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($p_nama_laporan, 0, 31));
				$sheet->setCellValue('A1', $p_nama_laporan);
				$sheet->getStyle('A1')->getFont()->setSize(12);
				$sheet->setCellValue('A2', 'PERIODE : '.$this->nama_bulan[intval($p_bln)].' '.$p_thn);
				$sheet->setCellValue('A3', 'KATEGORI : '.$p_kategori);
				$sheet->setCellValue('I2', 'DIVISI : '.$p_divisi);
				$sheet->setCellValue('I3', $p_type);
				$sheet->setCellValue('K2', $exclude_cash);
				$sheet->setCellValue('K3', $exclude_bass);
			}
			
			
			$content_html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
			
			$content_html.= "<table style='font-size:10pt!important'>";
			$content_html.= "<tr style='background-color:#".$warna_table_header.";'>";
			
			$content_html.= "	<th style='min-width:100px'>Divisi</th>";
			$content_html.= "	<th style='min-width:100px'>Merk</th>";
			if ($p_laporan=="2") {
				$content_html.= "	<th style='min-width:100px'>Cabang</th>";
				} else {
				$content_html.= "	<th style='min-width:100px'>Wilayah</th>";	
			}
			$currcol = 0;
			$currrow = 5;
			
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
				$sheet->getColumnDimension('A')->setWidth(15);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
				$sheet->getColumnDimension('B')->setWidth(15);
				$currcol += 1;
				if ($p_laporan=="2") {
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Cabang');
					} else {
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Wilayah');
				}
				$sheet->getColumnDimension('C')->setWidth(15);
			}
			
			$date = mktime(0,0,0,$p_bln,1,$p_thn);
			$days= date('t',$date);
			
			$grand_total = array();
			$grand_total[0] = 0;
			$grand_total_merk = 0;
			$grand_total_divisi = 0;
			
			for($i=1; $i<=$days;$i++){
				$grand_total[$i] = 0;
				$content_html.= "<th width='100px'>".str_pad($i, 2, '0', STR_PAD_LEFT)."</th>";
				if($this->excel_flag == 1){
					$currcol += 1;
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $i);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('00');
				}
			}
			
			$content_html.= "<th width='100px'>SUBTOTAL</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SUBTOTAL');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
			}
			$content_html.= "<th width='100px'>SUBTOTAL MERK</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SUBTOTAL MERK');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			}
			$content_html.= "<th width='100px'>SUBTOTAL DIVISI</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SUBTOTAL DIVISI');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			}
			
			$content_html.= "</tr>";
			
			$max_col = $currcol; // index kolom terakhir (paling kanan)
			$max_col_name = PHPExcel_Cell::stringFromColumnIndex($max_col-1); // nama kolom terakhir (paling kanan)
			
			
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			foreach($PenjualanNasional as $dt_divisi => $merks) {
				$total_divisi = 0;
				foreach($merks as $dt_merk => $cabangs) {
					$total_merk = 0;
					foreach($cabangs as $dt_cabang => $details) {
						$content_html.= "<tr>";
						$content_html.= "<td>".$dt_divisi."</td>";
						$content_html.= "<td>".$dt_merk."</td>";
						$content_html.= "<td>".$dt_cabang."</td>";
						if($this->excel_flag == 1){
							$currrow++;
							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_divisi);
							$currcol += 1 ;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_merk);
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_cabang);
						}
						$sub_total= 0;
						for($i=1; $i<=$days;$i++){
							$ada = 0;
							foreach ($details as $detail) {
								if($i==$detail->Tgl){
									$sub_total += ($detail->Total_Rp);
									$grand_total[$i] += ($detail->Total_Rp);
									$content_html.= "<td class='td-right'>".number_format($detail->Total_Rp,0)."</td>";
									
									
									if($this->excel_flag == 1){
										$currcol += 1;
										$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->Total_Rp);
										$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
									}
									$ada = 1;
								}
							}
							if($ada == 0){
								$content_html.= "<td class='td-right td-bold'>0</td>";
								if($this->excel_flag == 1){
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
									$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								}
							}
						}
						$content_html.= "<td class='td-right td-bold'>".number_format($sub_total,0)."</td><td></td><td></td>";
						
						if($this->excel_flag == 1){	
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $sub_total);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
						}
						$content_html.= "</tr>";
						$total_merk += $sub_total;
					}
					$content_html.= "<tr style='background-color:#".$warna_table_merk.";'><td colspan='3' class='td-right'>TOTAL ".$dt_divisi." - ".$dt_merk."</td>".str_repeat("<td></td>", $days+1)."<td class='td-right td-bold'>".number_format($total_merk,0)."</td><td></td></tr>";
					
					if($this->excel_flag == 1){
						$currrow++;
						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi.' - '.$dt_merk);
						$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+2 , $currrow);
						$sheet->getStyle('A'.$currrow)->getAlignment()->setHorizontal($alignment_right);
						$sheet->setCellValueByColumnAndRow($max_col-1, $currrow, $total_merk);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-2).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_merk);
						$sheet->getStyle("A".$currrow.":".$max_col_name.$currrow)->getFont()->setBold(true);	
					}
					$grand_total_merk += $total_merk;
					$total_divisi += $total_merk;
				}
				$content_html.= "<tr style='background-color:#".$warna_table_divisi.";'><td colspan='3' class='td-right'>TOTAL ".$dt_divisi."</td>".str_repeat("<td></td>", $days+2)."<td class='td-right td-bold'>".number_format($total_divisi,0)."</td></tr>";
				
				if($this->excel_flag == 1){
					$currrow++;
					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi);
					$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+2 , $currrow);
					$sheet->getStyle('A'.$currrow)->getAlignment()->setHorizontal($alignment_right);
					$sheet->setCellValueByColumnAndRow($max_col, $currrow, $total_divisi);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_divisi);
					$sheet->getStyle("A".$currrow.":".$max_col_name.$currrow)->getFont()->setBold(true);	
				}
				$grand_total_divisi += $total_divisi;
			}
			
			$currrow++;
			$currcol = 1;
			
			$content_html.= "<tr style='background-color:#".$warna_table_grandtotal.";'>";
			$content_html.= "<td colspan='3'>GRANDTOTAL</td>";
			
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+2 , $currrow);
				$sheet->getStyle("A".$currrow.":".$max_col_name.$currrow)->getFont()->setBold(true);	
			}
			
			$currcol += 2;
			$grand_total_all = 0;
			for($i=1; $i<=$days;$i++){
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total[$i])."</td>";
				
				if($this->excel_flag == 1){
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total[$i]);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_footer);
				}
				$grand_total_all += $grand_total[$i];
			}
			$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
			$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_merk)."</td>";
			$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_divisi)."</td>";
			
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_merk);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_divisi);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			}
			
			$content_html.= "</tr>";
			$content_html.= "</table>";
			
			
			if($this->excel_flag == 1){
				
				$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
				$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
				
				$sheet->getStyle('A5:C5')->getAlignment()->setHorizontal($alignment_left);			
				$sheet->getStyle('D5:'.$max_col_name.'5')->getAlignment()->setHorizontal($alignment_center);
				
				
				$sheet->getStyle('A5:'.$max_col_name.'5')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
				// $sheet->getStyle('A'.$currrow.':'.$max_col.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_footer);
				
				// bold
				$sheet->getStyle("A1:".$max_col_name."5")->getFont()->setBold(true);
				// border
				$styleArray = [
				'borders' => [
				'allBorders' => [
				'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
				],
				];
				$sheet ->getStyle('A5:'.$max_col_name.$currrow)->applyFromArray($styleArray);
				
				$sheet->setSelectedCell('A1');
				$filename=$p_nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 

				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit();
			}
			
			$content_html.= "</div>";
			$content_html.= "</body></html>";
			
			$data['title'] = $p_nama_laporan;
			$data['content_html'] = $content_html;
			
			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->load->view('LaporanResultView',$data);
		}
		
		public function PreviewB01B02($p_laporan,$p_nama_laporan,$p_kategori, $p_divisi, $p_type, $p_tgl1, $p_tgl2,$ex_cash,$ex_bass,$grup_subkategori, $supl="", $params)
		{
			
			$data = array();
			$api = 'APITES';
			$streamContext = stream_context_create(
				array('http'=>
					array(
						'timeout' => $this->max_exec_time,  //600 seconds = 10 menit
						'header' => 'Accept-Encoding: gzip, deflate', // Menambahkan header Accept-Encoding
					)
				)
			);

			$url = $this->apiurl."ProsesHarianB01B02?api=".$api."&p_laporan=".urlencode($p_laporan).
			"&p_tgl1=".urlencode($p_tgl1)."&p_tgl2=".urlencode($p_tgl2)."&p_kategori=".urlencode($p_kategori)."&p_divisi=".urlencode($p_divisi).
			"&p_type=".urlencode($p_type)."&ex_cash=".urlencode($ex_cash)."&ex_bass=".urlencode($ex_bass).
			"&grup_subkategori=".urlencode($grup_subkategori)."&supl=".urlencode($supl);
			//die($url);

			$response = file_get_contents($url, false, $streamContext);
			$response = $this->_decodeGzip($response);
			$PenjualanNasional = json_decode($response);
			
			if(count($PenjualanNasional->detail)==0){
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit('Tidak ada data');
			}
			
			$warna_table_header = 'f2f2f2';
			$warna_table_footer = 'b7dee8';
			
			$warna_table_type = 'd9d9d9';
			$warna_table_merk = 'ddd9c4';
			$warna_table_divisi = 'b7dee8';
			
			$warna_table_grandtotal = 'fabf8f';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			if($p_type=='ALL'){
				$p_type = "LOKAL & IMPORT";
			}
			
			if($p_kategori=='P'){
				$p_kategori = "PRODUK";
			}
			else if($p_kategori=='S'){
				$p_kategori = "SPAREPART";
			}
			else{
				$p_kategori = "PRODUK & SPAREPART";
			}
			
			$exclude_cash = '';
			if($ex_cash==1){
				$exclude_cash .= '-EXCLUDE CASH KONSUMEN ';
			}
			$exclude_bass = '';
			if($ex_bass==1){
				$exclude_bass .= '-EXCLUDE BASS '; 
			}
			
			$content_html = "<html>";
			$content_html = "<head>";
			
			$content_html = "<style>
			body{font-family:'Calibri',Arial,sans-serif;}
			table{padding:0;margin:0;border-collapse:collapse}
			td, th { border:1px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			$x_tgl1 = explode('/',$p_tgl1);
			$x_tgl2 = explode('/',$p_tgl2);
			
			$new_tgl1 = $x_tgl1[1]."-".substr($this->nama_bulan[intval($x_tgl1[0])],0,3)."-".$x_tgl1[2];
			$new_tgl2 = $x_tgl2[1]."-".substr($this->nama_bulan[intval($x_tgl2[0])],0,3)."-".$x_tgl2[2];
			
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "	<div><h2>".$p_nama_laporan."</h2></div>";
			$content_html.= "	<div style='float:left;width:50%'>PERIODE: ".$new_tgl1." s/d ".$new_tgl2."</div>";
			$content_html.= "	<div style='float:left;width:30%'>DIVISI: ".$p_divisi."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_cash."</div>";
			$content_html.= "	<div style='float:left;width:50%'>KATEGORI: ".$p_kategori."</div>";
			$content_html.= "	<div style='float:left;width:30%'>".$p_type."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_bass."</div>";
			$content_html.= "	<div style='clear:both'></div>";
			$content_html.= "</div>";	//close div_header
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($p_nama_laporan, 0, 31));
				$sheet->setCellValue('A1', $p_nama_laporan);
				$sheet->getStyle('A1')->getFont()->setSize(12);
				$sheet->setCellValue('A2', 'PERIODE : '.$new_tgl1.' s/d '.$new_tgl2);
				$sheet->setCellValue('A3', 'KATEGORI : '.$p_kategori);
				$sheet->setCellValue('I2', 'DIVISI : '.$p_divisi);
				$sheet->setCellValue('I3', $p_type);
				$sheet->setCellValue('K2', $exclude_cash);
				$sheet->setCellValue('K3', $exclude_bass);
			}
			
			
			$content_html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
			
			$content_html.= "<table style='font-size:10pt!important'>";
			$content_html.= "<tr style='background-color:#".$warna_table_header.";'>";
			
			$content_html.= "	<th style='min-width:20px'><br>No</th>";
			$content_html.= "	<th style='min-width:100px'><br>Divisi</th>";
			$content_html.= "	<th style='min-width:100px'><br>Merk</th>";
			$content_html.= "	<th style='min-width:100px'><br>Kode Barang</th>";
			$content_html.= "	<th style='min-width:100px'><br>Kategori</th>";
			
			$currcol = 0;
			$currrow = 5;
			
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No');
				$sheet->getColumnDimension('A')->setWidth(5);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
				$sheet->getColumnDimension('B')->setWidth(15);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
				$sheet->getColumnDimension('C')->setWidth(15);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Barang');
				$sheet->getColumnDimension('D')->setWidth(25);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kategori');
				$sheet->getColumnDimension('E')->setWidth(15);
			}
			
			$grand_total_type = 0;
			$grand_total_merk = 0;
			$grand_total_divisi = 0;
			
			$total_cabang_type = array();
			$total_cabang_merk = array();
			$total_cabang_divisi = array();
			$grand_total = array();
			
			foreach($PenjualanNasional->header as $hd){
				$total_cabang_type[$hd] = 0;
				$total_cabang_merk[$hd] = 0;
				$total_cabang_divisi[$hd] = 0;
				$grand_total[$hd] = 0;
				$content_html.= "<th width='100px'><br>".trim($hd)."</th>";
				if($this->excel_flag == 1){
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($hd));
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
				}
			}
			
			$content_html.= "<th width='100px'>TOTAL PER<br>BARANG</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL PER BARANG');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			}
			$content_html.= "<th width='100px'>TOTAL<br>KATEGORI</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL KATEGORI');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			}
			$content_html.= "<th width='100px'>TOTAL<br>MERK</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL MERK');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			}
			$content_html.= "<th width='100px'>TOTAL<br>DIVISI</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL DIVISI');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			}
			
			$content_html.= "</tr>";
			
			$max_col = $currcol; // index kolom terakhir (paling kanan)
			$max_col_name = PHPExcel_Cell::stringFromColumnIndex($max_col-1); // nama kolom terakhir (paling kanan)
			
			
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			foreach($PenjualanNasional->detail as $dt_divisi => $merks) {
				$no = 0;
				$total_divisi = 0;
				foreach($merks as $dt_merk => $types) {
					$total_merk = 0;
					foreach($types as $dt_type => $kodes) {
						$total_type = 0;
						foreach($kodes as $dt_kode => $details) {
							$no++;
							$content_html.= "<tr>";
							$content_html.= "<td>".$no."</td>";
							$content_html.= "<td>".$dt_divisi."</td>";
							$content_html.= "<td>".$dt_merk."</td>";
							$content_html.= "<td>".$dt_kode."</td>";
							$content_html.= "<td>".$dt_type."</td>";
							if($this->excel_flag == 1){
								$currrow++;
								$currcol = 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_divisi);
								$currcol += 1 ;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_merk);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_kode);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_type);
							}
							$sub_total = 0;
							
							//echo($dt_kode."<br>");
							//echo(json_encode($details));
							//echo("<br>");
							
							foreach($PenjualanNasional->header as $hd){
								$ada = 0;
								
								foreach ($details as $detail) {
									if($hd==$detail->Cabang){
										
										//$sub_total += intval($detail->Total);
										$sub_total += ($detail->Total);
										$total_cabang_type[$hd] += ($detail->Total);
										$content_html.= "<td class='td-right'>".number_format($detail->Total,0)."</td>";
										
										//debug total
										/*if ($dt_kode=="PS-128BIT") {
											echo($hd." : ".number_format($detail->Total)."<br>");
											echo("Subtotal : ".number_format($sub_total)."<br>");
										}*/
										
										if($this->excel_flag == 1){
											$currcol += 1;
											$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->Total);
											$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
										}
										$ada = 1;
									}
								}
								if($ada == 0){
									$content_html.= "<td class='td-right'>0</td>";
									if($this->excel_flag == 1){
										$currcol += 1;
										$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
										$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
									}
								}
							}
							$content_html.= "<td class='td-right td-bold'>".number_format($sub_total,0)."</td><td></td><td></td><td></td>";
							
							if($this->excel_flag == 1){	
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $sub_total);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
							}
							$content_html.= "</tr>";
							$total_type += $sub_total;
						}
						
						
						$currrow++;
						$currcol = 5;
						$content_html.= "<tr style='background-color:#".$warna_table_type.";'><td></td><td colspan='4' class='td-right'>TOTAL ".$dt_divisi." - ".$dt_merk." - ".$dt_type."</td>";
						
						
						foreach($PenjualanNasional->header as $hd){
							$total_cabang_merk[$hd] += ($total_cabang_type[$hd]);
							$content_html.= "<td class='td-right td-bold'>".number_format($total_cabang_type[$hd],0)."</td>";
							if($this->excel_flag == 1){
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_cabang_type[$hd]);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
							}
							$total_cabang_type[$hd] = 0;
						}
						
						
						$content_html.="<td></td><td class='td-right td-bold'>".number_format($total_type,0)."</td><td></td><td></td></tr>";
						
						
						if($this->excel_flag == 1){
							$currcol = 2;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi.' - '.$dt_merk.' - '.$dt_type);
							$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
							$sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal($alignment_right);
							$sheet->setCellValueByColumnAndRow($max_col-2, $currrow, $total_type);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-3).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_type);
							$sheet->getStyle('B'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
						}
						$grand_total_type += $total_type;
						$total_merk += $total_type;
						
						
					}
					$currrow++;
					$currcol = 5;
					$content_html.= "<tr style='background-color:#".$warna_table_merk.";'><td></td><td colspan='4' class='td-right'>TOTAL ".$dt_divisi." - ".$dt_merk."</td>";
					
					foreach($PenjualanNasional->header as $hd){
						$total_cabang_divisi[$hd] += ($total_cabang_merk[$hd]);
						$content_html.= "<td class='td-right td-bold'>".number_format($total_cabang_merk[$hd],0)."</td>";
						if($this->excel_flag == 1){
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_cabang_merk[$hd]);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
						}
						
						$total_cabang_merk[$hd] = 0;
					}
					$content_html.= "<td></td><td></td><td class='td-right td-bold'>".number_format($total_merk,0)."</td><td></td></tr>";
					
					
					
					
					if($this->excel_flag == 1){
						$currcol = 2;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi.' - '.$dt_merk);
						$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
						$sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal($alignment_right);
						$sheet->setCellValueByColumnAndRow($max_col-1, $currrow, $total_merk);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-2).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_merk);
						$sheet->getStyle('B'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
					}
					$grand_total_merk += $total_merk;
					$total_divisi += $total_merk;
				}
				
				
				$currrow++;
				$currcol = 5;
				$content_html.= "<tr style='background-color:#".$warna_table_divisi.";'><td></td><td colspan='4' class='td-right'>TOTAL ".$dt_divisi."</td>";
				
				
				foreach($PenjualanNasional->header as $hd){
					$grand_total[$hd] += ($total_cabang_divisi[$hd]);
					$content_html.= "<td class='td-right td-bold'>".number_format($total_cabang_divisi[$hd],0)."</td>";
					if($this->excel_flag == 1){
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_cabang_divisi[$hd]);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
					}
					
					$total_cabang_divisi[$hd] = 0;
				}
				
				$content_html.="<td></td><td></td><td></td><td class='td-right td-bold'>".number_format($total_divisi,0)."</td></tr>";
				
				if($this->excel_flag == 1){
					$currcol = 2;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi);
					$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
					$sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal($alignment_right);
					$sheet->setCellValueByColumnAndRow($max_col, $currrow, $total_divisi);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_divisi);
					$sheet->getStyle('B'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
				}
				$grand_total_divisi += $total_divisi;
			}
			
			
			$content_html.= "<tr style='background-color:#".$warna_table_grandtotal.";'>";
			$content_html.= "<td></td><td colspan='4'>GRANDTOTAL</td>";
			
			if($this->excel_flag == 1){
				$currrow++;
				$currcol = 2;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
				$sheet->getStyle('B'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
			}
			
			$currcol += 3;
			$grand_total_all = 0;
			
			
			foreach($PenjualanNasional->header as $hd){
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total[$hd])."</td>";
				
				if($this->excel_flag == 1){
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total[$hd]);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_grandtotal);
				}
				$grand_total_all += $grand_total[$hd];
			}
			$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
			$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_type)."</td>";
			$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_merk)."</td>";
			$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_divisi)."</td>";
			
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_type);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_merk);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_divisi);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			}
			
			$content_html.= "</tr>";
			$content_html.= "</table>";
			
			if($this->excel_flag == 1){
				// rata tengah header
				
				$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
				$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
				
				$sheet->getStyle('A5:C5')->getAlignment()->setHorizontal($alignment_left);			
				$sheet->getStyle('D5:'.$max_col_name.'5')->getAlignment()->setHorizontal($alignment_center);
				
				
				$sheet->getStyle('A5:'.$max_col_name.'5')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
				// $sheet->getStyle('A'.$currrow.':'.$max_col.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_footer);
				
				// bold
				$sheet->getStyle("A1:".$max_col_name."5")->getFont()->setBold(true);
				// border
				$styleArray = [
				'borders' => [
				'allBorders' => [
				'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
				],
				];
				$sheet ->getStyle('A5:'.$max_col_name.$currrow)->applyFromArray($styleArray);
				// $sheet->freezePane('A6');
				$sheet->setSelectedCell('A1');
				$filename=$p_nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 

				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit();
			}
			
			$content_html.= "</div>";
			$content_html.= "</body></html>";
			
			$data['title'] = $p_nama_laporan;
			$data['content_html'] = $content_html;
			
			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->load->view('LaporanResultView',$data);
		}
		
		public function PreviewA04($p_laporan,$p_nama_laporan,$p_kategori, $p_divisi, $p_type, $p_bln, $p_thn,$ex_cash,$ex_bass, $supl="", $params)
		{
			
			$data = array();
			$api = 'APITES';
			$streamContext = stream_context_create(
				array('http'=>
					array(
						'timeout' => $this->max_exec_time,  //600 seconds = 10 menit
						'header' => 'Accept-Encoding: gzip, deflate', // Menambahkan header Accept-Encoding
					)
				)
			);
			$URL = $this->apiurl."ProsesHarianA04?api=".$api."&p_laporan=".urlencode($p_laporan).
							"&p_bln=".urlencode($p_bln)."&p_thn=".urlencode($p_thn)."&p_kategori=".urlencode($p_kategori)."&p_divisi=".urlencode($p_divisi).
							"&p_type=".urlencode($p_type)."&ex_cash=".urlencode($ex_cash)."&ex_bass=".urlencode($ex_bass)."&supl=".urlencode($supl);
			// die($URL);
			$response = file_get_contents($URL, false, $streamContext);
			$response = $this->_decodeGzip($response);
			$json = json_decode($response);
			
			if(count($json)==0){
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit('Tidak ada data');
			}
			
			$this->LoadWilayahGroup();
			
			$PenjualanNasional = array();
			
			foreach($json as $new) {
				// Mencari nama wilayahgroup by wilayah dan kota.
				$WilayahGroup = $this->GetWilayahGroup(trim($new->Partner_Type), trim($new->Wilayah));
				if($WilayahGroup=='') exit('Silahkan isi konfigurasi di Konfigurasi Wilayah Group Report untuk partner type:'.$new->Partner_Type.' dan wilayah:'.$new->Wilayah);
				$detail = array(
				'Tgl'=>$new->Tgl,
				'Total_Rp'=>$new->Total_Rp
				);
				$divisi = trim($new->Divisi);
				$merk = trim($new->Merk);
				$cabang = trim($WilayahGroup);
				
				// cek apakah array sudah ada untuk group divisi, merk, cabang,
				if(ISSET($PenjualanNasional[$divisi][$merk][$cabang])){
					$ada = 0;
					// maka cek apakah sudah ada tanggal nya
					foreach($PenjualanNasional[$divisi][$merk][$cabang] as $k => $val){
						//jika ada tanggal yg sama untuk wilayah group yg sama, maka akan di-sum
						if($new->Tgl == $val['Tgl']){ 
							$PenjualanNasional[$divisi][$merk][$cabang][$k]['Total_Rp'] += $new->Total_Rp; 
							$ada = 1;
						}
					}
					if($ada == 0){ // jika tidak ada tanggalnya , maka tambah data baru
						$PenjualanNasional[$divisi][$merk][$cabang][] = $detail;
					}
				}
				else{
					// jika tidak ada array-nya, maka tambah data baru juga
					$PenjualanNasional[$divisi][$merk][$cabang][] = $detail;
				}
				
				ksort($PenjualanNasional[$divisi][$merk]); //sort cabang
				
			}
			// echo "<pre>";
			// print_r($PenjualanNasional);
			// echo "</pre>";
			// ksort($PenjualanNasional);
			// echo "<pre>";
			// print_r($PenjualanNasional);
			// echo "</pre>";
			// exit;
			
			$warna_table_header = 'f2f2f2';
			$warna_table_footer = 'b7dee8';
			$warna_table_merk = 'd9d9d9';
			$warna_table_divisi = 'c4bd97';
			$warna_table_grandtotal = 'b7dee8';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			if($p_type=='ALL'){
				$p_type = "LOKAL & IMPORT";
			}
			
			if($p_kategori=='P'){
				$p_kategori = "PRODUK";
			}
			else if($p_kategori=='S'){
				$p_kategori = "SPAREPART";
			}
			else{
				$p_kategori = "PRODUK & SPAREPART";
			}
			
			$exclude_cash = '';
			if($ex_cash==1){
				$exclude_cash .= '-EXCLUDE CASH KONSUMEN ';
			}
			$exclude_bass = '';
			if($ex_bass==1){
				$exclude_bass .= '-EXCLUDE BASS '; 
			}
			
			$content_html = "<html>";
			$content_html = "<head>";
			
			$content_html = "<style>
			body{font-family:'Calibri',Arial,sans-serif;}
			table{padding:0;margin:0;border-collapse:collapse}
			td, th { border:1px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "	<div><h2>".$p_nama_laporan."</h2></div>";
			$content_html.= "	<div><b>WILAYAH BERDASARKAN PEMBAGIAN TARGET</b></div>";
			$content_html.= "	<div style='float:left;width:50%'>PERIODE: ".$this->nama_bulan[intval($p_bln)]." ".$p_thn."</div>";
			$content_html.= "	<div style='float:left;width:30%'>DIVISI: ".$p_divisi."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_cash."</div>";
			$content_html.= "	<div style='float:left;width:50%'>KATEGORI: ".$p_kategori."</div>";
			$content_html.= "	<div style='float:left;width:30%'>".$p_type."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_bass."</div>";
			$content_html.= "	<div style='clear:both'></div>";
			$content_html.= "</div>";	//close div_header
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($p_nama_laporan, 0, 31));
				$sheet->setCellValue('A1', $p_nama_laporan);
				$sheet->getStyle('A1')->getFont()->setSize(12);
				$sheet->setCellValue('A2', "WILAYAH BERDASARKAN PEMBAGIAN TARGET");
				$sheet->getStyle('A2')->getFont()->setSize(11);
				$sheet->setCellValue('A3', 'PERIODE : '.$this->nama_bulan[intval($p_bln)].' '.$p_thn);
				$sheet->setCellValue('A4', 'KATEGORI : '.$p_kategori);
				$sheet->setCellValue('I3', 'DIVISI : '.$p_divisi);
				$sheet->setCellValue('I4', $p_type);
				$sheet->setCellValue('K2', $exclude_cash);
				$sheet->setCellValue('K3', $exclude_bass);
			}
			
			
			$content_html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
			
			$content_html.= "<table style='font-size:10pt!important'>";
			$content_html.= "<tr style='background-color:#".$warna_table_header.";'>";
			
			$content_html.= "	<th style='min-width:100px'>Divisi</th>";
			$content_html.= "	<th style='min-width:100px'>Merk</th>";
			if ($p_laporan=="2") {
				$content_html.= "	<th style='min-width:100px'>Cabang</th>";
				} else {
				$content_html.= "	<th style='min-width:100px'>Wilayah</th>";	
			}
			$currcol = 0;
			$currrow = 6;
			
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
				$sheet->getColumnDimension('A')->setWidth(15);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
				$sheet->getColumnDimension('B')->setWidth(15);
				$currcol += 1;
				if ($p_laporan=="2") {
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Cabang');
					} else {
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Wilayah');
				}
				$sheet->getColumnDimension('C')->setWidth(15);
			}
			
			$date = mktime(0,0,0,$p_bln,1,$p_thn);
			$days= date('t',$date);
			
			$grand_total = array();
			$grand_total[0] = 0;
			$grand_total_merk = 0;
			$grand_total_divisi = 0;
			
			for($i=1; $i<=$days;$i++){
				$grand_total[$i] = 0;
				$content_html.= "<th width='100px'>".str_pad($i, 2, '0', STR_PAD_LEFT)."</th>";
				if($this->excel_flag == 1){
					$currcol += 1;
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $i);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('00');
				}
			}
			
			$content_html.= "<th width='100px'>SUBTOTAL</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SUBTOTAL');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
			}
			$content_html.= "<th width='100px'>SUBTOTAL MERK</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SUBTOTAL MERK');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			}
			$content_html.= "<th width='100px'>SUBTOTAL DIVISI</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SUBTOTAL DIVISI');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			}
			
			$content_html.= "</tr>";
			
			$max_col = $currcol; // index kolom terakhir (paling kanan)
			$max_col_name = PHPExcel_Cell::stringFromColumnIndex($max_col-1); // nama kolom terakhir (paling kanan)
			
			
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			foreach($PenjualanNasional as $dt_divisi => $merks) {
				$total_divisi = 0;
				foreach($merks as $dt_merk => $cabangs) {
					$total_merk = 0;
					foreach($cabangs as $dt_cabang => $details) {
						$content_html.= "<tr>";
						$content_html.= "<td>".$dt_divisi."</td>";
						$content_html.= "<td>".$dt_merk."</td>";
						$content_html.= "<td>".$dt_cabang."</td>";
						if($this->excel_flag == 1){
							$currrow++;
							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_divisi);
							$currcol += 1 ;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_merk);
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_cabang);
						}
						$sub_total= 0;						
						
						for($i=1; $i<=$days;$i++){
							$ada = 0;
							foreach ($details as $detail) {
								if($i==$detail['Tgl']){
									$sub_total += ($detail['Total_Rp']);
									$grand_total[$i] += ($detail['Total_Rp']);
									$content_html.= "<td class='td-right'>".number_format($detail['Total_Rp'],0)."</td>";
									if($this->excel_flag == 1){
										$currcol += 1;
										$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail['Total_Rp']);
										$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
									}
									$ada = 1;
								}
							}
							if($ada == 0){
								$content_html.= "<td class='td-right td-bold'>0</td>";
								if($this->excel_flag == 1){
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
									$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								}
							}
						}
						$content_html.= "<td class='td-right td-bold'>".number_format($sub_total,0)."</td><td></td><td></td>";
						
						if($this->excel_flag == 1){	
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $sub_total);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
						}
						$content_html.= "</tr>";
						$total_merk += $sub_total;
					}
					$content_html.= "<tr style='background-color:#".$warna_table_merk.";'><td colspan='3' class='td-right'>TOTAL ".$dt_divisi." - ".$dt_merk."</td>".str_repeat("<td></td>", $days+1)."<td class='td-right td-bold'>".number_format($total_merk,0)."</td><td></td></tr>";
					
					if($this->excel_flag == 1){
						$currrow++;
						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi.' - '.$dt_merk);
						$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+2 , $currrow);
						$sheet->getStyle('A'.$currrow)->getAlignment()->setHorizontal($alignment_right);
						$sheet->setCellValueByColumnAndRow($max_col-1, $currrow, $total_merk);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-2).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_merk);
						$sheet->getStyle("A".$currrow.":".$max_col_name.$currrow)->getFont()->setBold(true);	
					}
					$grand_total_merk += $total_merk;
					$total_divisi += $total_merk;
				}
				$content_html.= "<tr style='background-color:#".$warna_table_divisi.";'><td colspan='3' class='td-right'>TOTAL ".$dt_divisi."</td>".str_repeat("<td></td>", $days+2)."<td class='td-right td-bold'>".number_format($total_divisi,0)."</td></tr>";
				
				if($this->excel_flag == 1){
					$currrow++;
					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi);
					$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+2 , $currrow);
					$sheet->getStyle('A'.$currrow)->getAlignment()->setHorizontal($alignment_right);
					$sheet->setCellValueByColumnAndRow($max_col, $currrow, $total_divisi);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_divisi);
					$sheet->getStyle("A".$currrow.":".$max_col_name.$currrow)->getFont()->setBold(true);	
				}
				$grand_total_divisi += $total_divisi;
			}
			
			$currrow++;
			$currcol = 1;
			
			$content_html.= "<tr style='background-color:#".$warna_table_grandtotal.";'>";
			$content_html.= "<td colspan='3'>GRANDTOTAL</td>";
			
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+2 , $currrow);
				$sheet->getStyle("A".$currrow.":".$max_col_name.$currrow)->getFont()->setBold(true);	
			}
			
			$currcol += 2;
			$grand_total_all = 0;
			for($i=1; $i<=$days;$i++){
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total[$i])."</td>";
				
				if($this->excel_flag == 1){
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total[$i]);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_footer);
				}
				$grand_total_all += $grand_total[$i];
			}
			$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
			$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_merk)."</td>";
			$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_divisi)."</td>";
			
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_merk);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_divisi);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			}
			
			$content_html.= "</tr>";
			$content_html.= "</table>";
			
			
			if($this->excel_flag == 1){
				
				$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
				$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
				
				$sheet->getStyle('A6:C6')->getAlignment()->setHorizontal($alignment_left);			
				$sheet->getStyle('D6:'.$max_col_name.'6')->getAlignment()->setHorizontal($alignment_center);
				
				
				$sheet->getStyle('A6:'.$max_col_name.'6')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
				// $sheet->getStyle('A'.$currrow.':'.$max_col.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_footer);
				
				// bold
				$sheet->getStyle("A1:".$max_col_name."6")->getFont()->setBold(true);
				// border
				$styleArray = [
				'borders' => [
				'allBorders' => [
				'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
				],
				];
				$sheet ->getStyle('A6:'.$max_col_name.$currrow)->applyFromArray($styleArray);
				
				$sheet->setSelectedCell('A1');
				$filename=$p_nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 

				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit();
			}
			
			$content_html.= "</div>";
			$content_html.= "</body></html>";
			
			$data['title'] = $p_nama_laporan;
			$data['content_html'] = $content_html;
			
			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->load->view('LaporanResultView',$data);
		}
		
		public function PreviewB03B04($p_laporan,$p_nama_laporan,$p_kategori, $p_divisi, $p_type, $p_tgl1, $p_tgl2,$ex_cash,$ex_bass,$grup_subkategori, $supl="", $params)
		{
			
			$data = array();
			$api = 'APITES';
			//-------------------------------------------------- start (aliat 09/11/2020)
			$streamContext = stream_context_create(
				array('http'=>
					array(
						'timeout' => $this->max_exec_time,  //600 seconds = 10 menit
						'header' => 'Accept-Encoding: gzip, deflate', // Menambahkan header Accept-Encoding
					)
				)
			);
			//Pass this stream context to file_get_contents via the third parameter.
			// $result = file_get_contents('http://localhost/example/', false, $streamContext);
			//-------------------------------------------------- end
			
			$url = $this->apiurl."ProsesHarianB03B04?api=".$api."&p_laporan=".urlencode($p_laporan).
			"&p_bln=".urlencode($p_tgl1)."&p_thn=".urlencode($p_tgl2)."&p_kategori=".urlencode($p_kategori).
			"&p_divisi=".urlencode($p_divisi)."&p_type=".urlencode($p_type)."&ex_cash=".urlencode($ex_cash).
			"&ex_bass=".urlencode($ex_bass)."&grup_subkategori=".urlencode($grup_subkategori)."&supl=".urlencode($supl);
			// die($url);
			// exit;
			$response = file_get_contents($url, false, $streamContext);
			$response = $this->_decodeGzip($response);
			$json = json_decode($response);
			
			if(count($json)==0){
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit('Tidak ada data');
			}

			// die(json_encode($json));
			
			$this->LoadWilayahGroup();
			
			$details = array();
			$array_cabang = array();
			
			foreach($json as $new) {
				// Mencari nama wilayahgroup by wilayah dan kota.
				$WilayahGroup = $this->GetWilayahGroup((trim($new->Partner_Type)), (trim($new->Wilayah)));
				if($WilayahGroup=='') exit('Silahkan isi konfigurasi di Konfigurasi Wilayah Group Report untuk partner type:'.$new->Partner_Type.' dan wilayah:'.$new->Wilayah);
				$detail = array(
					'WilayahGroup' => $WilayahGroup,
					'Total' => $new->Total
				);
				$divisi = trim($new->Divisi);
				$merk = trim($new->Merk);
				$type = trim($new->Type);
				$kode = trim($new->Kode);
				
				// cek apakah array sudah ada untuk group divisi, merk, cabang,
				if(ISSET($details[$divisi][$merk][$type][$kode])){
					$ada = 0;
					// maka cek apakah sudah ada tanggal nya
					foreach($details[$divisi][$merk][$type][$kode] as $k => $val){					
						//jika ada tanggal yg sama untuk wilayah group yg sama, maka akan di-sum
						if($WilayahGroup == $val['WilayahGroup']){ 
							$details[$divisi][$merk][$type][$kode][$k]['Total'] += $new->Total; 
							$ada = 1;
						}
					}
					if($ada == 0){ // jika tidak ada tanggalnya , maka tambah data baru
						$details[$divisi][$merk][$type][$kode][] = $detail;
					}
				}
				else{
					// jika tidak ada array-nya, maka tambah data baru juga
					$details[$divisi][$merk][$type][$kode][] = $detail;
				}
				
				array_push($array_cabang, $WilayahGroup);
				ksort($details[$divisi][$merk][$type]); //sort cabang
				
			}
			
			$array_cabang = array_unique($array_cabang);
			sort($array_cabang);
			
			// echo(json_encode($array_cabang));
			// echo("<br><br>");
			// die(json_encode($details));


			$PenjualanNasional = array('header'=>$array_cabang, 'detail'=>$details);			
			$PenjualanNasional = json_decode(json_encode($PenjualanNasional));
			
			$warna_table_header = 'f2f2f2';
			$warna_table_footer = 'b7dee8';
			
			$warna_table_type = 'd9d9d9';
			$warna_table_merk = 'ddd9c4';
			$warna_table_divisi = 'b7dee8';
			
			$warna_table_grandtotal = 'fabf8f';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			if($p_type=='ALL'){
				$p_type = "LOKAL & IMPORT";
			}
			
			if($p_kategori=='P'){
				$p_kategori = "PRODUK";
			}
			else if($p_kategori=='S'){
				$p_kategori = "SPAREPART";
			}
			else{
				$p_kategori = "PRODUK & SPAREPART";
			}
			
			$exclude_cash = '';
			if($ex_cash==1){
				$exclude_cash .= '-EXCLUDE CASH KONSUMEN ';
			}
			$exclude_bass = '';
			if($ex_bass==1){
				$exclude_bass .= '-EXCLUDE BASS '; 
			}
			
			$content_html = "<html>";
			$content_html = "<head>";
			
			$content_html = "<style>
			body{font-family:'Calibri',Arial,sans-serif;}
			table{padding:0;margin:0;border-collapse:collapse}
			td, th { border:1px solid #555; padding:2px!important; }
			th {vertical-align:bottom}
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			$x_tgl1 = explode('/',$p_tgl1);
			$x_tgl2 = explode('/',$p_tgl2);
			
			$new_tgl1 = $x_tgl1[1]."-".substr($this->nama_bulan[intval($x_tgl1[0])],0,3)."-".$x_tgl1[2];
			$new_tgl2 = $x_tgl2[1]."-".substr($this->nama_bulan[intval($x_tgl2[0])],0,3)."-".$x_tgl2[2];
			
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "	<div><h2>".$p_nama_laporan."</h2><br></div>";
			$content_html.= "	<div><b>WILAYAH BERDASARKAN PEMBAGIAN TARGET</b></div>";
			$content_html.= "	<div style='float:left;width:50%'>PERIODE: ".$new_tgl1." s/d ".$new_tgl2."</div>";
			$content_html.= "	<div style='float:left;width:30%'>DIVISI: ".$p_divisi."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_cash."</div>";
			$content_html.= "	<div style='float:left;width:50%'>KATEGORI: ".$p_kategori."</div>";
			$content_html.= "	<div style='float:left;width:30%'>".$p_type."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_bass."</div>";
			$content_html.= "	<div style='clear:both'></div>";
			$content_html.= "</div>";	//close div_header
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($p_nama_laporan, 0, 31));
				$sheet->setCellValue('A1', $p_nama_laporan);
				$sheet->getStyle('A1')->getFont()->setSize(12);
				$sheet->setCellValue('A2', "WILAYAH BERDASARKAN PEMBAGIAN TARGET");
				$sheet->getStyle('A2')->getFont()->setSize(11);
				$sheet->setCellValue('A3', 'PERIODE : '.$new_tgl1.' s/d '.$new_tgl2);
				$sheet->setCellValue('A4', 'KATEGORI : '.$p_kategori);
				$sheet->setCellValue('I3', 'DIVISI : '.$p_divisi);
				$sheet->setCellValue('I4', $p_type);
				$sheet->setCellValue('K2', $exclude_cash);
				$sheet->setCellValue('K3', $exclude_bass);
			}
			
			
			$content_html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
			
			$content_html.= "<table style='font-size:10pt!important'>";
			$content_html.= "<tr style='background-color:#".$warna_table_header.";'>";
			
			$content_html.= "	<th style='min-width:20px'>No</th>";
			$content_html.= "	<th style='min-width:100px'>Divisi</th>";
			$content_html.= "	<th style='min-width:100px'>Merk</th>";
			$content_html.= "	<th style='min-width:100px'>Kode Barang</th>";
			$content_html.= "	<th style='min-width:100px'>Kategori</th>";
			
			$currcol = 0;
			$currrow = 6;
			
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No');
				$sheet->getColumnDimension('A')->setWidth(5);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
				$sheet->getColumnDimension('B')->setWidth(15);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
				$sheet->getColumnDimension('C')->setWidth(15);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Barang');
				$sheet->getColumnDimension('D')->setWidth(25);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kategori');
				$sheet->getColumnDimension('E')->setWidth(15);
			}
			
			$grand_total_type = 0;
			$grand_total_merk = 0;
			$grand_total_divisi = 0;
			
			$total_cabang_type = array();
			$total_cabang_merk = array();
			$total_cabang_divisi = array();
			$grand_total = array();
			
			foreach($PenjualanNasional->header as $hd){
				$total_cabang_type[$hd] = 0;
				$total_cabang_merk[$hd] = 0;
				$total_cabang_divisi[$hd] = 0;
				$grand_total[$hd] = 0;
				$content_html.= "<th width='100px'>".trim($hd)."</th>";
				if($this->excel_flag == 1){
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($hd));
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
				}
			}
			
			$content_html.= "<th width='100px'>TOTAL PER<br>BARANG</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL PER BARANG');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			}
			$content_html.= "<th width='100px'>TOTAL<br>KATEGORI</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL KATEGORI');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			}
			$content_html.= "<th width='100px'>TOTAL<br>MERK</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL MERK');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			}
			$content_html.= "<th width='100px'>TOTAL<br>DIVISI</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL DIVISI');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			}
			
			$content_html.= "</tr>";
			
			$max_col = $currcol; // index kolom terakhir (paling kanan)
			$max_col_name = PHPExcel_Cell::stringFromColumnIndex($max_col-1); // nama kolom terakhir (paling kanan)
			
			
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			foreach($PenjualanNasional->detail as $dt_divisi => $merks) {
				$no = 0;
				$total_divisi = 0;
				foreach($merks as $dt_merk => $types) {
					$total_merk = 0;
					foreach($types as $dt_type => $kodes) {
						$total_type = 0;
						foreach($kodes as $dt_kode => $details) {
							$no++;
							$content_html.= "<tr>";
							$content_html.= "<td>".$no."</td>";
							$content_html.= "<td>".$dt_divisi."</td>";
							$content_html.= "<td>".$dt_merk."</td>";
							$content_html.= "<td>".$dt_kode."</td>";
							$content_html.= "<td>".$dt_type."</td>";
							if($this->excel_flag == 1){
								$currrow++;
								$currcol = 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_divisi);
								$currcol += 1 ;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_merk);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_kode);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_type);
							}
							$sub_total = 0;
							
							foreach($PenjualanNasional->header as $hd){
								$ada = 0;
								foreach ($details as $detail) {
									if($hd==$detail->WilayahGroup){
										//$sub_total += intval($detail->Total);
										$sub_total += ($detail->Total);
										$total_cabang_type[$hd] += ($detail->Total);
										$content_html.= "<td class='td-right'>".number_format($detail->Total,0)."</td>";
										
										//debug total
										/*if ($dt_kode=="PS-128BIT") {
											echo($hd." : ".number_format($detail->Total)."<br>");
											echo("Subtotal : ".number_format($sub_total)."<br>");
										}*/
										
										if($this->excel_flag == 1){
											$currcol += 1;
											$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->Total);
											$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
										}
										$ada = 1;
									}
								}
								if($ada == 0){
									$content_html.= "<td class='td-right'>0</td>";
									if($this->excel_flag == 1){
										$currcol += 1;
										$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
										$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
									}
								}
							}
							$content_html.= "<td class='td-right td-bold'>".number_format($sub_total,0)."</td><td></td><td></td><td></td>";
							
							if($this->excel_flag == 1){	
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $sub_total);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
							}
							$content_html.= "</tr>";
							$total_type += $sub_total;
						}
						
						
						$currrow++;
						$currcol = 5;
						$content_html.= "<tr style='background-color:#".$warna_table_type.";'><td></td><td colspan='4' class='td-right'>TOTAL ".$dt_divisi." - ".$dt_merk." - ".$dt_type."</td>";
						
						
						foreach($PenjualanNasional->header as $hd){
							$total_cabang_merk[$hd] += ($total_cabang_type[$hd]);
							$content_html.= "<td class='td-right td-bold'>".number_format($total_cabang_type[$hd],0)."</td>";
							if($this->excel_flag == 1){
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_cabang_type[$hd]);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
							}
							$total_cabang_type[$hd] = 0;
						}
						
						
						$content_html.="<td></td><td class='td-right td-bold'>".number_format($total_type,0)."</td><td></td><td></td></tr>";
						
						
						if($this->excel_flag == 1){
							$currcol = 2;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi.' - '.$dt_merk.' - '.$dt_type);
							$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
							$sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal($alignment_right);
							$sheet->setCellValueByColumnAndRow($max_col-2, $currrow, $total_type);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-3).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_type);
							$sheet->getStyle('B'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
						}
						$grand_total_type += $total_type;
						$total_merk += $total_type;
						
						
					}
					$currrow++;
					$currcol = 5;
					$content_html.= "<tr style='background-color:#".$warna_table_merk.";'><td></td><td colspan='4' class='td-right'>TOTAL ".$dt_divisi." - ".$dt_merk."</td>";
					
					foreach($PenjualanNasional->header as $hd){
						$total_cabang_divisi[$hd] += ($total_cabang_merk[$hd]);
						$content_html.= "<td class='td-right td-bold'>".number_format($total_cabang_merk[$hd],0)."</td>";
						if($this->excel_flag == 1){
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_cabang_merk[$hd]);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
						}
						
						$total_cabang_merk[$hd] = 0;
					}
					$content_html.= "<td></td><td></td><td class='td-right td-bold'>".number_format($total_merk,0)."</td><td></td></tr>";
					
					
					
					
					if($this->excel_flag == 1){
						$currcol = 2;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi.' - '.$dt_merk);
						$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
						$sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal($alignment_right);
						$sheet->setCellValueByColumnAndRow($max_col-1, $currrow, $total_merk);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-2).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_merk);
						$sheet->getStyle('B'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
					}
					$grand_total_merk += $total_merk;
					$total_divisi += $total_merk;
				}
				
				
				$currrow++;
				$currcol = 5;
				$content_html.= "<tr style='background-color:#".$warna_table_divisi.";'><td></td><td colspan='4' class='td-right'>TOTAL ".$dt_divisi."</td>";
				
				
				foreach($PenjualanNasional->header as $hd){
					$grand_total[$hd] += ($total_cabang_divisi[$hd]);
					$content_html.= "<td class='td-right td-bold'>".number_format($total_cabang_divisi[$hd],0)."</td>";
					if($this->excel_flag == 1){
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_cabang_divisi[$hd]);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
					}
					
					$total_cabang_divisi[$hd] = 0;
				}
				
				$content_html.="<td></td><td></td><td></td><td class='td-right td-bold'>".number_format($total_divisi,0)."</td></tr>";
				
				if($this->excel_flag == 1){
					$currcol = 2;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$dt_divisi);
					$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
					$sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal($alignment_right);
					$sheet->setCellValueByColumnAndRow($max_col, $currrow, $total_divisi);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($max_col-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_divisi);
					$sheet->getStyle('B'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
				}
				$grand_total_divisi += $total_divisi;
			}
			
			
			$content_html.= "<tr style='background-color:#".$warna_table_grandtotal.";'>";
			$content_html.= "<td></td><td colspan='4'>GRANDTOTAL</td>";
			
			if($this->excel_flag == 1){
				$currrow++;
				$currcol = 2;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+3 , $currrow);
				$sheet->getStyle('B'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
			}
			
			$currcol += 3;
			$grand_total_all = 0;
			
			
			foreach($PenjualanNasional->header as $hd){
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total[$hd])."</td>";
				
				if($this->excel_flag == 1){
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total[$hd]);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_grandtotal);
				}
				$grand_total_all += $grand_total[$hd];
			}
			$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
			$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_type)."</td>";
			$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_merk)."</td>";
			$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_divisi)."</td>";
			
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_type);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_merk);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_divisi);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			}
			
			$content_html.= "</tr>";
			$content_html.= "</table>";
			
			if($this->excel_flag == 1){
				// rata tengah header
				
				$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
				$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
				
				$sheet->getStyle('A6:C6')->getAlignment()->setHorizontal($alignment_left);			
				$sheet->getStyle('D6:'.$max_col_name.'6')->getAlignment()->setHorizontal($alignment_center);
				
				
				$sheet->getStyle('A6:'.$max_col_name.'6')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
				// $sheet->getStyle('A'.$currrow.':'.$max_col.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_footer);
				
				// bold
				$sheet->getStyle("A1:".$max_col_name."6")->getFont()->setBold(true);
				// border
				$styleArray = [
				'borders' => [
				'allBorders' => [
				'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
				],
				];
				$sheet ->getStyle('A6:'.$max_col_name.$currrow)->applyFromArray($styleArray);
				// $sheet->freezePane('A6');
				$sheet->setSelectedCell('A1');
				$filename=$p_nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 

				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit();
			}
			
			$content_html.= "</div>";
			$content_html.= "</body></html>";
			
			$data['title'] = $p_nama_laporan;
			$data['content_html'] = $content_html;
			
			$this->load->view('LaporanResultView',$data);
		}
			
		public function PreviewD01($p_laporan,$p_nama_laporan,$p_kategori, $p_divisi, $p_type, $p_tgl1, $p_tgl2,$ex_cash,$ex_bass,$grup_subkategori, $supl="", $params)
		{
			
			$data = array();
			$api = 'APITES';
			$streamContext = stream_context_create( 
				array('http'=>
					array(
						'timeout' => $this->max_exec_time,  //6000 seconds = 100 menit
					)
				)
			);

			$url = $this->apiurl."ProsesHarianD01?api=".$api."&p_laporan=".urlencode($p_laporan).
			"&p_tgl1=".urlencode($p_tgl1)."&p_tgl2=".urlencode($p_tgl2)."&p_kategori=".urlencode($p_kategori)."&p_divisi=".urlencode($p_divisi).
			"&p_type=".urlencode($p_type)."&ex_cash=".urlencode($ex_cash)."&ex_bass=".urlencode($ex_bass).
			"&grup_subkategori=".urlencode($grup_subkategori)."&supl=".urlencode($supl);
			// die($url);
			$response = file_get_contents($url, false, $streamContext);
			$response = $this->_decodeGzip($response);
			$PenjualanNasional = json_decode($response);
			
			if(count($PenjualanNasional->detail)==0){
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit('Tidak ada data');
			}
			
			$warna_table_header = 'f2f2f2';
			$warna_table_footer = 'b7dee8';
			
			$warna_table_grandtotal = 'fabf8f';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			if($p_type=='ALL'){
				$p_type = "LOKAL & IMPORT";
			}
			
			if($p_kategori=='P'){
				$p_kategori = "PRODUK";
			}
			else if($p_kategori=='S'){
				$p_kategori = "SPAREPART";
			}
			else{
				$p_kategori = "PRODUK & SPAREPART";
			}
			
			$exclude_cash = '';
			if($ex_cash==1){
				$exclude_cash .= '-EXCLUDE CASH KONSUMEN ';
			}
			$exclude_bass = '';
			if($ex_bass==1){
				$exclude_bass .= '-EXCLUDE BASS '; 
			}
			
			$content_html = "<html>";
			$content_html = "<head>";
			
			$content_html = "<style>
			body{font-family:'Calibri',Arial,sans-serif;}
			table{padding:0;margin:0;border-collapse:collapse}
			td, th { border:1px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			$x_tgl1 = explode('/',$p_tgl1);
			$x_tgl2 = explode('/',$p_tgl2);
			
			$new_tgl1 = $x_tgl1[1]."-".substr($this->nama_bulan[intval($x_tgl1[0])],0,3)."-".$x_tgl1[2];
			$new_tgl2 = $x_tgl2[1]."-".substr($this->nama_bulan[intval($x_tgl2[0])],0,3)."-".$x_tgl2[2];
			
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "	<div><h2>".$p_nama_laporan."</h2></div>";
			$content_html.= "	<div style='float:left;width:50%'>PERIODE: ".$new_tgl1." s/d ".$new_tgl2."</div>";
			$content_html.= "	<div style='float:left;width:30%'>DIVISI: ".$p_divisi."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_cash."</div>";
			$content_html.= "	<div style='float:left;width:50%'>KATEGORI: ".$p_kategori."</div>";
			$content_html.= "	<div style='float:left;width:30%'>".$p_type."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_bass."</div>";
			$content_html.= "	<div style='clear:both'></div>";
			$content_html.= "</div>";	//close div_header
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($p_nama_laporan, 0, 31));
				$sheet->setCellValue('A1', $p_nama_laporan);
				$sheet->getStyle('A1')->getFont()->setSize(12);
				$sheet->setCellValue('A2', 'PERIODE : '.$new_tgl1.' s/d '.$new_tgl2);
				$sheet->setCellValue('A3', 'KATEGORI : '.$p_kategori);
				$sheet->setCellValue('E2', 'DIVISI : '.$p_divisi);
				$sheet->setCellValue('E3', $p_type);
				$sheet->setCellValue('H2', $exclude_cash);
				$sheet->setCellValue('H3', $exclude_bass);
			}
			
			
			$content_html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
			
			$content_html.= "<table style='font-size:10pt!important'>";
			$content_html.= "<tr style='background-color:#".$warna_table_header.";'>";
			
			$content_html.= "	<th style='min-width:20px'><br>NO</th>";
			$content_html.= "	<th style='min-width:100px'><br>WILAYAH</th>";
			
			$currcol = 0;
			$currrow = 5;
			
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO');
				$sheet->getColumnDimension('A')->setWidth(5);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'WILAYAH');
				$sheet->getColumnDimension('B')->setWidth(15);
			}
			
			
			$total_divisi_cabang = array();
			$grand_total = array();
			
			foreach($PenjualanNasional->header as $hd){
				$grand_total[$hd] = 0;
				$content_html.= "<th width='100px'><br>".trim($hd)."</th>";
				if($this->excel_flag == 1){
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($hd));
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
				}
			}
			
			$content_html.= "<th width='100px'>TOTAL<br>WILAYAH</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL WILAYAH');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			}
			
			$content_html.= "</tr>";
			
			$max_col = $currcol; // index kolom terakhir (paling kanan)
			$max_col_name = PHPExcel_Cell::stringFromColumnIndex($max_col-1); // nama kolom terakhir (paling kanan)
			
			
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			$no = 0;
			foreach($PenjualanNasional->detail as $dt_cabang => $divisis) {
				$no++;
				$content_html.= "<tr>";
				$content_html.= "<td>".$no."</td>";
				$content_html.= "<td>".$dt_cabang."</td>";
				if($this->excel_flag == 1){
					$currrow++;
					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_cabang);
				}
				$sub_total = 0;
				
				foreach($PenjualanNasional->header as $hd){
					$ada = 0;
					foreach($divisis as $detail) {
						if($hd==$detail->Divisi){
							$sub_total += ($detail->Total);
							$grand_total[$hd] += ($detail->Total);
							$content_html.= "<td class='td-right'>".number_format($detail->Total,0)."</td>";
							if($this->excel_flag == 1){
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->Total);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							}
							$ada = 1;
						}
					}
					if($ada == 0){
						$content_html.= "<td class='td-right'>0</td>";
						if($this->excel_flag == 1){
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						}
					}
				}
				$content_html.= "<td class='td-right td-bold'>".number_format($sub_total,0)."</td>";
				
				if($this->excel_flag == 1){	
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $sub_total);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
				}
				$content_html.= "</tr>";
			}
			
			$grand_total_all = 0;
			
			$content_html.= "<tr style='background-color:#".$warna_table_grandtotal.";'>";
			$content_html.= "<td></td><td>GRANDTOTAL</td>";
			if($this->excel_flag == 1){
				$currrow++;
				$currcol = 2;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow);
				$sheet->getStyle('B'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
			}
			
			foreach($PenjualanNasional->header as $hd){
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total[$hd])."</td>";
				
				if($this->excel_flag == 1){
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total[$hd]);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_grandtotal);
				}
				$grand_total_all += $grand_total[$hd];
			}
			
			$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
			
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			}
			
			$content_html.= "</tr>";
			$content_html.= "</table>";
			
			if($this->excel_flag == 1){
				// rata tengah header
				
				$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
				$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
				
				$sheet->getStyle('A5:C5')->getAlignment()->setHorizontal($alignment_left);			
				$sheet->getStyle('D5:'.$max_col_name.'5')->getAlignment()->setHorizontal($alignment_center);
				
				
				$sheet->getStyle('A5:'.$max_col_name.'5')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
				// $sheet->getStyle('A'.$currrow.':'.$max_col.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_footer);
				
				// bold
				$sheet->getStyle("A1:".$max_col_name."5")->getFont()->setBold(true);
				// border
				$styleArray = [
				'borders' => [
				'allBorders' => [
				'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
				],
				];
				$sheet ->getStyle('A5:'.$max_col_name.$currrow)->applyFromArray($styleArray);
				// $sheet->freezePane('A6');
				$sheet->setSelectedCell('A1');
				$filename=$p_nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 

				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit();
			}
			
			$content_html.= "</div>";
			$content_html.= "</body></html>";
			
			$data['title'] = $p_nama_laporan;
			$data['content_html'] = $content_html;
			
			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->load->view('LaporanResultView',$data);
		}
		
		
		public function PreviewD02($p_laporan,$p_nama_laporan,$p_kategori, $p_divisi, $p_type, $p_tgl1, $p_tgl2,$ex_cash,$ex_bass,$grup_subkategori, $supl="", $params)
		{
			
			$data = array();
			$api = 'APITES';
			//-------------------------------------------------- start (aliat 09/11/2020)
			$streamContext = stream_context_create(
				array('http'=>
					array(
						'timeout' => $this->max_exec_time,  //600 seconds = 10 menit
						'header' => 'Accept-Encoding: gzip, deflate', // Menambahkan header Accept-Encoding
					)
				)
			);
			//-------------------------------------------------- end
			
			$url = $this->apiurl."ProsesHarianD02?api=".$api."&p_laporan=".urlencode($p_laporan).
			"&p_bln=".urlencode($p_tgl1)."&p_thn=".urlencode($p_tgl2)."&p_kategori=".urlencode($p_kategori).
			"&p_divisi=".urlencode($p_divisi)."&p_type=".urlencode($p_type)."&ex_cash=".urlencode($ex_cash).
			"&ex_bass=".urlencode($ex_bass)."&grup_subkategori=".urlencode($grup_subkategori)."&supl=".urlencode($supl);
			// die($url);
			$response = file_get_contents($url, false, $streamContext);
			$response = $this->_decodeGzip($response);
			$json = json_decode($response);
			
			if(count($json)==0){
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				
				exit('Tidak ada data');
			}
			
			$this->LoadWilayahGroup();
			
			$details = array();
			$array_divisi = array();
			
			foreach($json as $new) {
				// Mencari nama wilayahgroup by wilayah dan kota.
				$WilayahGroup = $this->GetWilayahGroup(trim($new->Partner_Type), trim($new->Wilayah));
				if($WilayahGroup=='') exit('Silahkan isi konfigurasi di Konfigurasi Wilayah Group Report untuk partner type:'.$new->Partner_Type.' dan wilayah:'.$new->Wilayah);
				$detail = array(
				'Divisi' => trim($new->Divisi),
				'Total' => $new->Total
				);
				$Wilayah = $WilayahGroup;
				
				// cek apakah array sudah ada untuk group divisi, merk, cabang,
				if(ISSET($details[$Wilayah])){
					$ada = 0;
					// maka cek apakah sudah ada tanggal nya
					foreach($details[$Wilayah] as $k => $val){					
						//jika ada tanggal yg sama untuk wilayah group yg sama, maka akan di-sum
						if(trim($new->Divisi) == $val['Divisi']){ 
							$details[$Wilayah][$k]['Total'] += $new->Total; 
							$ada = 1;
						}
					}
					if($ada == 0){ // jika tidak ada tanggalnya , maka tambah data baru
						$details[$Wilayah][] = $detail;
					}
				}
				else{
					// jika tidak ada array-nya, maka tambah data baru juga
					$details[$Wilayah][] = $detail;
				}
				
				array_push($array_divisi, trim($new->Divisi));
				
				ksort($details); //sort cabang
				
			}
			
			$array_divisi = array_unique($array_divisi);
			sort($array_divisi);
			
			$PenjualanNasional = array('header'=>$array_divisi, 'detail'=>$details);
			
			$PenjualanNasional = json_decode(json_encode($PenjualanNasional));
			
			$warna_table_header = 'f2f2f2';
			$warna_table_footer = 'b7dee8';
			
			$warna_table_type = 'd9d9d9';
			$warna_table_merk = 'ddd9c4';
			$warna_table_divisi = 'b7dee8';
			
			$warna_table_grandtotal = 'fabf8f';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			if($p_type=='ALL'){
				$p_type = "LOKAL & IMPORT";
			}
			
			if($p_kategori=='P'){
				$p_kategori = "PRODUK";
			}
			else if($p_kategori=='S'){
				$p_kategori = "SPAREPART";
			}
			else{
				$p_kategori = "PRODUK & SPAREPART";
			}
			
			$exclude_cash = '';
			if($ex_cash==1){
				$exclude_cash .= '-EXCLUDE CASH KONSUMEN ';
			}
			$exclude_bass = '';
			if($ex_bass==1){
				$exclude_bass .= '-EXCLUDE BASS '; 
			}
			
			$content_html = "<html>";
			$content_html = "<head>";
			
			$content_html = "<style>
			body{font-family:'Calibri',Arial,sans-serif;}
			table{padding:0;margin:0;border-collapse:collapse}
			td, th { border:1px solid #555; padding:2px!important; }
			th {vertical-align:bottom}
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			$x_tgl1 = explode('/',$p_tgl1);
			$x_tgl2 = explode('/',$p_tgl2);
			
			$new_tgl1 = $x_tgl1[1]."-".substr($this->nama_bulan[intval($x_tgl1[0])],0,3)."-".$x_tgl1[2];
			$new_tgl2 = $x_tgl2[1]."-".substr($this->nama_bulan[intval($x_tgl2[0])],0,3)."-".$x_tgl2[2];
			
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "	<div><h2>".$p_nama_laporan."</h2><br></div>";
			$content_html.= "	<div><b>WILAYAH BERDASARKAN PEMBAGIAN TARGET</b></div>";
			$content_html.= "	<div style='float:left;width:50%'>PERIODE: ".$new_tgl1." s/d ".$new_tgl2."</div>";
			$content_html.= "	<div style='float:left;width:30%'>DIVISI: ".$p_divisi."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_cash."</div>";
			$content_html.= "	<div style='float:left;width:50%'>KATEGORI: ".$p_kategori."</div>";
			$content_html.= "	<div style='float:left;width:30%'>".$p_type."</div>";
			$content_html.= "	<div style='float:left;width:20%'>".$exclude_bass."</div>";
			$content_html.= "	<div style='clear:both'></div>";
			$content_html.= "</div>";	//close div_header
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($p_nama_laporan, 0, 31));
				$sheet->setCellValue('A1', $p_nama_laporan);
				$sheet->getStyle('A1')->getFont()->setSize(12);
				$sheet->setCellValue('A2', "WILAYAH BERDASARKAN PEMBAGIAN TARGET");
				$sheet->getStyle('A2')->getFont()->setSize(11);
				$sheet->setCellValue('A3', 'PERIODE : '.$new_tgl1.' s/d '.$new_tgl2);
				$sheet->setCellValue('A4', 'KATEGORI : '.$p_kategori);
				$sheet->setCellValue('E3', 'DIVISI : '.$p_divisi);
				$sheet->setCellValue('E4', $p_type);
				$sheet->setCellValue('H3', $exclude_cash);
				$sheet->setCellValue('H4', $exclude_bass);
			}
			
			
			$content_html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
			
			$content_html.= "<table style='font-size:10pt!important'>";
			$content_html.= "<tr style='background-color:#".$warna_table_header.";'>";
			
			$content_html.= "	<th style='min-width:20px'>No</th>";
			$content_html.= "	<th style='min-width:100px'>Wilayah</th>";
			
			$currcol = 0;
			$currrow = 6;
			
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No');
				$sheet->getColumnDimension('A')->setWidth(5);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Wilayah');
				$sheet->getColumnDimension('B')->setWidth(15);
			}
			
			
			$grand_total = array();
			
			foreach($PenjualanNasional->header as $hd){
				$total_cabang_type[$hd] = 0;
				$total_cabang_merk[$hd] = 0;
				$total_cabang_divisi[$hd] = 0;
				$grand_total[$hd] = 0;
				$content_html.= "<th width='100px'>".trim($hd)."</th>";
				if($this->excel_flag == 1){
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($hd));
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
				}
			}
			
			$content_html.= "<th width='100px'>TOTAL<br>WILAYAH</th>";
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL WILAYAH');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			}
			
			$content_html.= "</tr>";
			
			$max_col = $currcol; // index kolom terakhir (paling kanan)
			$max_col_name = PHPExcel_Cell::stringFromColumnIndex($max_col-1); // nama kolom terakhir (paling kanan)
			
			
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
				$no = 0;
				$total_divisi = 0;
			foreach($PenjualanNasional->detail as $dt_divisi => $details) {
				$no++;
				$content_html.= "<tr>";
				$content_html.= "<td>".$no."</td>";
				$content_html.= "<td>".$dt_divisi."</td>";
				if($this->excel_flag == 1){
					$currrow++;
					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt_divisi);
				}
				$sub_total = 0;
				
				foreach($PenjualanNasional->header as $hd){
					$ada = 0;
					foreach ($details as $detail) {
						if($hd==$detail->Divisi){
							$sub_total += ($detail->Total);
							$grand_total[$hd] += ($detail->Total);
							$content_html.= "<td class='td-right'>".number_format($detail->Total,0)."</td>";
							
							if($this->excel_flag == 1){
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->Total);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							}
							$ada = 1;
						}
					}
					if($ada == 0){
						$content_html.= "<td class='td-right'>0</td>";
						if($this->excel_flag == 1){
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						}
					}
				}
				$content_html.= "<td class='td-right td-bold'>".number_format($sub_total,0)."</td>";
				
				if($this->excel_flag == 1){	
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $sub_total);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
				}
				$content_html.= "</tr>";
			}
			
			$content_html.= "<tr style='background-color:#".$warna_table_grandtotal.";'>";
			$content_html.= "<td></td><td>GRANDTOTAL</td>";
			
			if($this->excel_flag == 1){
				$currrow++;
				$currcol = 2;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow);
				$sheet->getStyle('B'.$currrow.':'.$max_col_name.$currrow)->getFont()->setBold(true);	
			}
			
			$grand_total_all = 0;
			foreach($PenjualanNasional->header as $hd){
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total[$hd])."</td>";
				if($this->excel_flag == 1){
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total[$hd]);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle('A'.$currrow.':'.$max_col_name.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_grandtotal);
				}
				$grand_total_all += $grand_total[$hd];
			}
			$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
			
			if($this->excel_flag == 1){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			}
			
			$content_html.= "</tr>";
			$content_html.= "</table>";
			
			if($this->excel_flag == 1){
				// rata tengah header
				
				$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
				$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
				
				$sheet->getStyle('A6:C6')->getAlignment()->setHorizontal($alignment_left);			
				$sheet->getStyle('D6:'.$max_col_name.'6')->getAlignment()->setHorizontal($alignment_center);
				
				
				$sheet->getStyle('A6:'.$max_col_name.'6')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
				// $sheet->getStyle('A'.$currrow.':'.$max_col.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_footer);
				
				// bold
				$sheet->getStyle("A1:".$max_col_name."6")->getFont()->setBold(true);
				// border
				$styleArray = [
				'borders' => [
				'allBorders' => [
				'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
				],
				];
				$sheet ->getStyle('A6:'.$max_col_name.$currrow)->applyFromArray($styleArray);
				// $sheet->freezePane('A6');
				$sheet->setSelectedCell('A1');
				$filename=$p_nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 

				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit();
			}
			
			$content_html.= "</div>";
			$content_html.= "</body></html>";
			
			$data['title'] = $p_nama_laporan;
			$data['content_html'] = $content_html;
			
			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->load->view('LaporanResultView',$data);
		}
		
		
		
	}																																			
