<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class LaporanPenjualanNasionalv2_ extends MY_Controller 
	{
		public $excel_flag = 0;
		public $nama_bulan = array('','JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER');
		public $wilayah_group = array();
		public $maxtimeout = 900;
		public $memorylimit = '256m';
		public $currrow = 0;
		public $currcol = 0;
		
		public function __construct()
		{
			parent::__construct();
			$this->load->model('HelperModel');
			$this->load->helper('FormLibrary');
			$this->load->library('excel');
			$this->load->model('MasterReportWilayahModel', 'ReportModel');
			$this->reportByTarget = array("A04", "B03", "B04", "C03", "D02");
			$this->bulan = array("JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC");
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
			$this->apiurl = $this->API_URL."/LaporanPenjualanNasionalv2/";
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
		public function index($err=1)
		{
			$data = array();
			$api = 'APITES';
			
			set_time_limit($this->maxtimeout);
			
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
			if ($err==1) {
				$_SESSION["error"] = "Ada Error";
			}
			
			$data['title'] = 'Laporan Penjualan Nasional';

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('LaporanPenjualanNasionalFormView2',$data);
		}
		
		public function LoadWilayahGroup()
		{
			$this->wilayah_group = $this->ReportModel->getList('PENJUALAN NASIONAL', 'WILAYAH');
			// die(json_encode($this->wilayah_group));
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

				$data = array();
				$p_bln = 0;
				$p_thn = 0;

				if(substr($_POST["laporan"],0,1)=='A')
				{
					$tgl = explode("/",$_POST["dp1"]);
					// $p_bln = intval($tgl[0]);
					$p_bln = $tgl[0];
					$p_thn = $tgl[1];
					$_POST["dp1"] = $p_bln."/01/".$p_thn;
					$_POST["dp2"] = date("m/t/Y", strtotime($_POST["dp1"]));
				} else if(substr($_POST["laporan"],0,1)=='C')
				{
					$p_thn = $_POST["dp1"];
					$_POST["dp1"] = "01/01/".$p_thn;
					$_POST["dp2"] = "12/31/".$p_thn;
				} else {
					$this->form_validation->set_rules('dp2','Tanggal Akhir','required');
					$tgl = explode("/",$_POST["dp1"]);
					$_POST["dp1"] = $tgl[1]."/".$tgl[0]."/".$tgl[2];
					$tgl = explode("/",$_POST["dp2"]);
					$_POST["dp2"] = $tgl[1]."/".$tgl[0]."/".$tgl[2];
				}
				if($_POST["laporan"]=='D01'){
					$this->form_validation->set_rules('dp2','Tanggal Akhir','required');
				}

				$ex_cash = (ISSET($_POST['ex_cash'])) ? 1 : 0;
				$ex_bass = (ISSET($_POST['ex_bass'])) ? 1 : 0;
				$grup_subkategori = (ISSET($_POST['grup_subkategori'])) ? 1 : 0;
				$supl = "x";
				$user = strtoupper(trim($_SESSION["logged_in"]["useremail"]));
				if ($user == "USER@PABRIK.KG") $supl = "JKTK001";
				if ($user == "QR@PABRIK.KG") $supl = "JKTK001";
				if ($user == "USER@PABRIK.PTRI") $supl = "JKTR001";
				if ($user == "QR@PABRIK.PTRI") $supl = "JKTR001";
				if ($user == "USER@PABRIK.TIN") $supl = "JKTT003";
				if ($user == "QR@PABRIK.TIN") $supl = "JKTT003";

				//reegan1 A03, A04, B01, B02, B03, B04, C02, C03, D01 dan D02
				if($this->form_validation->run())
				{
					$data["p_laporan"] = $_POST["laporan"];
					$data["p_nama_laporan"] = "";
					$data['p_bln'] = $p_bln;
					$data['p_thn'] = $p_thn;
					$data["p_tgl1"] = date("Y-m-d", strtotime($_POST["dp1"]));
					$data["p_tgl2"] = date("Y-m-d", strtotime($_POST["dp2"]));
					$data["p_kategori"] = $_POST["kategori"];
					$data["p_divisi"] = trim($_POST["divisi"]);
					$data["p_type"] = $_POST["type"];
					$data["ex_cash"] = $ex_cash;
					$data["ex_bass"] = $ex_bass;
					$data["grup_subkategori"] = $grup_subkategori;
					$data["supl"] = $supl;
					$data["p_wilayah"] = trim($_POST["wilayah"]);
					
					$dataReport = $this->getDataReport($data);
					// die(json_encode($dataReport));

					if (count($dataReport["detail"])==0) {
						$params['Remarks']="FAILED - Data Tidak Ditemukan";
						$params['RemarksDate'] = date("Y-m-d H:i:s");
						$this->ActivityLogModel->update_activity($params);

						$_SESSION["error"] = "Data Tidak Ditemukan";
						redirect("LaporanPenjualanNasionalv2");
					} else {
						// ActivityLog Update SUCCESS
						$params['Remarks']="SUCCESS";
						$params['RemarksDate'] = date("Y-m-d H:i:s");
						$this->ActivityLogModel->update_activity($params);
					}

					$judulReport = "";
					if ($data['p_laporan']=="A01") $judulReport = "LAPORAN PENJUALAN HARIAN GABUNGAN (Rp)";
					if ($data['p_laporan']=="A02") $judulReport = "LAPORAN PENJUALAN HARIAN ALL CABANG (Rp)";
					if ($data['p_laporan']=="A03") $judulReport = "LAPORAN PENJUALAN HARIAN ALL WILAYAH (Rp)";
					if ($data['p_laporan']=="A04") $judulReport = "LAPORAN PENJUALAN HARIAN ALL WILAYAH (Rp)";
					if ($data['p_laporan']=="A05") $judulReport = "LAPORAN PENJUALAN HARIAN GABUNGAN (Qty)";
					if ($data['p_laporan']=="B01") $judulReport = "LAPORAN PENJUALAN PER BARANG ALL WILAYAH (Rp)";
					if ($data['p_laporan']=="B02") $judulReport = "LAPORAN PENJUALAN PER BARANG ALL WILAYAH (Qty)";
					if ($data['p_laporan']=="B03") $judulReport = "LAPORAN PENJUALAN PER BARANG ALL WILAYAH (Rp)";
					if ($data['p_laporan']=="B04") $judulReport = "LAPORAN PENJUALAN PER BARANG ALL WILAYAH (Qty)";
					if ($data['p_laporan']=="C01") $judulReport = "LAPORAN PENJUALAN QTY BULANAN PER BARANG GABUNGAN";
					if ($data['p_laporan']=="C02") $judulReport = "LAPORAN PENJUALAN QTY BULANAN PER BARANG ALL WILAYAH";
					if ($data['p_laporan']=="C03") $judulReport = "LAPORAN PENJUALAN QTY BULANAN PER BARANG WILAYAH (GRUP TARGET)";
					if ($data['p_laporan']=="D01") $judulReport = "PENJUALAN PER DIVISI ALL WILAYAH (RP)";
					if ($data['p_laporan']=="D02") $judulReport = "PENJUALAN PER DIVISI ALL WILAYAH (RP)";
					
					$data["p_nama_laporan"] = $judulReport;
					$this->Previewv2($data, $dataReport);

				} else {
					// ActivityLog Update SUCCESS
					$params['Remarks']="FAILED - Invalid Input";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

					$_SESSION["error"] = "Invalid Input";
					redirect("LaporanPenjualanNasionalv2");
				}
			} else {
				redirect("LaporanPenjualanNasionalv2");
			}
		}

		public function getDataReport($params)
		{
			$p_bln = 0;
			$p_thn = 0;

			$params["api"] = "APITES";
			$params["grup_wilayah"] = array();
			if (in_array($params["p_laporan"], $this->reportByTarget)) {
				$params["grup_wilayah"] = $this->ReportModel->getList('PENJUALAN NASIONAL', 'WILAYAH');				
			}
			$data = json_encode($params);
			// die($data);
			$url = $this->apiurl."Prosesv2";

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => $this->maxtimeout,
				CURLOPT_POST => 1,
				CURLOPT_ENCODING => '',
				CURLOPT_POSTFIELDS => $data,
				CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));		
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// die($response."<br><br>");
			
			if (substr($response,0,11)=="Fatal Error") {
				$_SESSION["error"] = $response;
				redirect("LaporanPenjualanNasionalv2");
			} else {
				return json_decode($response, true);
			}
		}

		public function Previewv2($data, $dataReport)
		{			
			$warna_table_header = 'f2f2f2';
			$warna_table_footer = 'b7dee8';
			$warna_table_grandtotal = '7CFC00';

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			$this->currrow = 1;
			$this->currcol = 1;
			
			if($data['p_type']=='ALL'){
				$data['p_type'] = "LOKAL & IMPORT";
			}
			
			if($data['p_kategori']=='P'){
				$p_kategori = "PRODUK";
			}
			else if($data['p_kategori']=='S'){
				$p_kategori = "SPAREPART";
			}
			else{
				$p_kategori = "PRODUK & SPAREPART";
			}
			
			$exclude_cash = '';
			if($data['ex_cash']==1){
				$exclude_cash .= '-EXCLUDE CASH KONSUMEN ';
			}

			$exclude_bass = '';
			if($data['ex_bass']==1){
				$exclude_bass .= '-EXCLUDE BASS '; 
			}

			$content_html = "";
			$p_bln = (int)$data['p_bln'];
			
			if ($this->excel_flag == 0) {
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
				$content_html.= "	<div><h2>".$data['p_nama_laporan']."</h2></div>";
				if (in_array($data["p_laporan"], $this->reportByTarget)) {
				$content_html.= "	<div><h3>Group By Wilayah Target</h3></div>";
				}	
				if (substr($data["p_laporan"],0,1)=="A") {
				$content_html.= "	<div style='float:left;width:50%'>PERIODE: ".$this->nama_bulan[$p_bln]." ".$data['p_thn']."</div>";
				} else if (substr($data["p_laporan"],0,1)=="B") {
				$content_html.= "	<div style='float:left;width:50%'>PERIODE: ".$data['p_tgl1']." - ".$data['p_tgl2']."</div>";
				} else {
				$content_html.= "	<div style='float:left;width:50%'>PERIODE: ".$data['p_tgl1']." - ".$data['p_tgl2']."</div>";
				}
				$content_html.= "	<div style='float:left;width:30%'>DIVISI: ".$data['p_divisi']."</div>";
				$content_html.= "	<div style='float:left;width:20%'>".$exclude_cash."</div>";
				$content_html.= "	<div style='float:left;width:50%'>KATEGORI: ".$data['p_kategori']."</div>";
				$content_html.= "	<div style='float:left;width:30%'>".$data['p_type']."</div>";
				$content_html.= "	<div style='float:left;width:20%'>".$exclude_bass."</div>";
				$content_html.= "	<div style='clear:both'></div>";
				$content_html.= "	<div style=''>Printed Date: ".date("d-m-Y H:i:s")."</div>";
				$content_html.= "</div>";
			} else {
				$sheet->setTitle(substr($data['p_nama_laporan'], 0, 31));
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $data['p_nama_laporan']);
				$sheet->getStyle('A1')->getFont()->setSize(12);
				$this->currrow++;
				if (in_array($data["p_laporan"], $this->reportByTarget)) {
				$content_html.= "	<div><h3>Group By Wilayah Target</h3></div>";
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "Group By Wilayah Target");
				$this->currrow++;
				}	

				$this->currcol = 1;
				if (substr($data["p_laporan"],0,1)=="A") {
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'PERIODE : '.$this->nama_bulan[$p_bln].' '.$data['p_thn']);
				} else if (substr($data["p_laporan"],0,1)=="B") {
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'PERIODE : '.$data['p_tgl1'].' '.$data['p_tgl2']);
				} else {
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'PERIODE : '.$data['p_tgl1'].' '.$data['p_tgl2']);
				}
				$this->currcol = 9;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'DIVISI : '.$data['p_divisi']);
				$this->currcol = 12;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $exclude_cash); 
				$this->currrow++;

				$this->currcol = 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'KATEGORI : '.$data['p_kategori']);
				$this->currcol = 9;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $data['p_type']);
				$this->currcol = 12;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $exclude_bass);
				$this->currrow++;
			}
			// die($content_html);

			$content_report = "";
			if ($data["p_laporan"]=="A01" || $data["p_laporan"]=="A02") {
				$content_report = $this->PreviewA01A02($data, $dataReport, $sheet);
			} else if ($data["p_laporan"]=="A03" || $data["p_laporan"]=="A04") {
				$content_report = $this->PreviewA03A04($data, $dataReport, $sheet);
			} else if ($data["p_laporan"]=="A05") {
				$content_report = $this->PreviewA05($data, $dataReport, $sheet);
			} else if (substr($data["p_laporan"],0,1)=="B") {
				$content_report = $this->PreviewB($data, $dataReport, $sheet);
			} else if (substr($data["p_laporan"],0,1)=="C") {
				$content_report = $this->PreviewC($data, $dataReport, $sheet);
			} else if (substr($data["p_laporan"],0,1)=="D") {
				$content_report = $this->PreviewD($data, $dataReport, $sheet);
			}
			// die($content_report);

			if($this->excel_flag == 1){				
				$sheet->setSelectedCell('A1');
				
				$filename=$data['p_nama_laporan'].'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 

				exit();
			} else {		
				$content_html.= "<div style='clear:both'></div>";
				$content_html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
				$content_html = (($content_html==null)? "" : $content_html).$content_report;
				$content_html.= "</div>";
				$content_html.= "</body></html>";
			
				$param = array();
				$param['title'] = $data['p_nama_laporan'];
				$param['content_html'] = $content_html;
				$this->load->view('LaporanResultView',$param);
			}
		}

		public function PreviewA01A02($data, $dataReport, $sheet)
		{			
			$warna_table_header = 'f2f2f2';
			$warna_table_footer = 'b7dee8';
			$warna_table_grandtotal = '7CFC00';
			$content_html = "";
			$table_start_row = 0;
			
			if ($this->excel_flag == 0) {			
				$content_html.= "<table style='font-size:10pt!important'>";
				$content_html.= "<tr style='background-color:#".$warna_table_header.";'>";
				
				$content_html.= "	<th style='min-width:100px'>Divisi</th>";
				$content_html.= "	<th style='min-width:100px'>Merk</th>";
				$content_html.= "	<th style='min-width:100px'>Cabang</th>";
			} else {		
				$this->currcol = 1;				
				$this->currrow++;
				$table_start_row = $this->currrow;

				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Divisi');
				$sheet->getColumnDimension('A')->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Merk');
				$sheet->getColumnDimension('B')->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Cabang');
				$sheet->getColumnDimension('C')->setWidth(15);
				$this->currcol += 1;
			}
			
			$date = mktime(0,0,0,$data['p_bln'],1,$data['p_thn']);
			$days= date('t',$date);
			
			$grand_total = array();
			$grand_total[0] = 0;
			
			$divisi_total = array();
			
			for($i=1; $i<=$days;$i++){
				$grand_total[$i] = 0;

				if($this->excel_flag == 0){
					$content_html.= "<th width='100px'>".str_pad($i, 2, '0', STR_PAD_LEFT)."</th>";
				} else {
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $i);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('00');
					$this->currcol += 1;
				}
			}
			
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'SUBTOTAL');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
				$this->currcol += 1;
			} else {
				$content_html.= "<th width='100px'>SUBTOTAL</th>";
				$content_html.= "</tr>";
			}
						
			$max_col = $this->currcol-2;

			$PenjualanNasional = $dataReport["detail"];
			$headers = $dataReport["data"];

			for ($x=0; $x<count($headers); $x++) {
				$hd = $headers[$x];

				if ($this->excel_flag == 0) {
					$content_html.= "<tr>";
					$content_html.= "<td>".$hd["Divisi"]."</td>";
					$content_html.= "<td>".$hd["Merk"]."</td>";
					$content_html.= "<td>".$hd["Wilayah"]."</td>";
				} else {
					$this->currrow++;
					$this->currcol = 1;
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Divisi"]);
					$this->currcol += 1;
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Merk"]);
					$this->currcol += 1;
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Wilayah"]);
					$this->currcol += 1;
				}

				$sub_total= 0;
				for($i=1; $i<=$days;$i++){
					$ada = 0;
					for($y=0; $y<count($PenjualanNasional); $y++) {
					// foreach ($details as $detail) {
						$detail = $PenjualanNasional[$y];

						if($i==$detail["Tgl"] && $hd["Divisi"]==$detail["Divisi"] && $hd["Merk"]==$detail["Merk"] && $hd["Wilayah"]==$detail["Wilayah"]){
							$sub_total += ($detail["Total"]);
							$grand_total[$i] += ($detail["Total"]);
							
							$divisi_total[$detail["Divisi"]][$i] = (ISSET($divisi_total[$detail["Divisi"]][$i]) ? $divisi_total[$detail["Divisi"]][$i] : 0) + ($detail["Total"]);

							if($this->excel_flag == 0){
								$content_html.= "<td class='td-right'>".number_format($detail["Total"],0)."</td>";
							} else {
								$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $detail["Total"]);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
								$this->currcol += 1;
							}
							$ada = 1;
							break;
						}
					}
					if($ada == 0){
						if($this->excel_flag == 0){
							$content_html.= "<td class='td-right'>0</td>";
						} else {
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 0);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
							$this->currcol += 1;
						}
					}
				}

				if ($this->excel_flag == 0) {
					$content_html.= "<td class='td-right td-bold'>".number_format($sub_total,0)."</td>";
					$content_html.= "</tr>";
				} else {	
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $sub_total);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getFont()->setBold(true);	
					$this->currcol += 1;
				}
				
				//subtotal per divisi
				$next_x = $x+1;
				if((ISSET($headers[$next_x]) && $headers[$next_x]['Divisi']!=$headers[$x]['Divisi']) || ($x==count($headers)-1)){
					if ($this->excel_flag == 0) {
						$content_html.= "<tr style='background-color:#".$warna_table_footer.";'>";
						$content_html.= "<td colspan='3' class='td-bold'>".$headers[$x]['Divisi']."</td>";
					} else {
						$this->currrow++;
						$this->currcol = 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $headers[$x]['Divisi']);
						$sheet->mergeCellsByColumnAndRow($this->currcol, $this->currrow, $this->currcol+2 , $this->currrow);
						$this->currcol += 3;
					
					}
					$subtotal_divisi = 0;
					for($i=1; $i<=$days;$i++){
						$cur_total_divisi = (ISSET($divisi_total[$headers[$x]['Divisi']][$i]) ? $divisi_total[$headers[$x]['Divisi']][$i] : 0);
						$subtotal_divisi += $cur_total_divisi;
						
						if ($this->excel_flag == 0) {
							$content_html.= "<td class='td-right td-bold'>".number_format($cur_total_divisi,0)."</td>";
						}
						else {
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $cur_total_divisi);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getFont()->setBold(true);	
							$this->currcol += 1;
						}
					}
					
					if ($this->excel_flag == 0) {
						$content_html.= "<td class='td-right td-bold'>".number_format($subtotal_divisi,0)."</td>";
						$content_html.= "</tr>";
					} else {
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $subtotal_divisi);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle("A".$this->currrow.":".PHPExcel_Cell::stringFromColumnIndex($max_col).$this->currrow)->getFont()->setBold(true);	
						
						$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
						$sheet->getStyle('A'.$this->currrow.':'.PHPExcel_Cell::stringFromColumnIndex($max_col).$this->currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_footer);
					}
				}
			}
			
			
			if($this->excel_flag == 0){
				$content_html.= "<tr style='background-color:#".$warna_table_footer.";'>";
				$content_html.= "<td colspan='3' class='td-bold'>GRANDTOTAL</td>";
			} else {
				$this->currrow++;
				$this->currcol = 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'GRANDTOTAL');
				$sheet->mergeCellsByColumnAndRow($this->currcol, $this->currrow, $this->currcol+2 , $this->currrow);
				$this->currcol += 3;
			}
			
			$grand_total_all = 0;
			for($i=1; $i<=$days;$i++){
				if ($this->excel_flag == 0) {
					$content_html.= "<td class='td-right td-bold'>".number_format($grand_total[$i])."</td>";
				} else {
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total[$i]);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
					$this->currcol += 1;
				}
				$grand_total_all += $grand_total[$i];
			}
			if($this->excel_flag == 0){
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
				$content_html.= "</tr>";
				$content_html.= "</table>";				

				return($content_html);
			} else {
			
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
				$this->currcol += 1;
			
				$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
				
				$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
				$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
				
				$sheet->getStyle('A'.$table_start_row.':C'.$table_start_row)->getAlignment()->setHorizontal($alignment_left);			
				$sheet->getStyle('D'.$table_start_row.':'.$max_col.'5')->getAlignment()->setHorizontal($alignment_center);
				
				$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
				
				$sheet->getStyle('A'.$table_start_row.':'.$max_col.$table_start_row)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
				$sheet->getStyle('A'.$this->currrow.':'.$max_col.$this->currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_footer);
				
				// bold
				$sheet->getStyle("A1:".$max_col.$table_start_row)->getFont()->setBold(true);
				$sheet->getStyle("A".$this->currrow.":".$max_col.$this->currrow)->getFont()->setBold(true);	
				// border
				$styleArray = [
				'borders' => [
				'allBorders' => [
				'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
				],
				];
				$sheet ->getStyle('A'.$table_start_row.':'.$max_col.$this->currrow)->applyFromArray($styleArray);				

				return "";
			}
		}

		public function PreviewA03A04($data, $dataReport, $sheet)
		{			
			$warna_table_header = 'f2f2f2';
			$warna_table_footer = 'b7dee8';
			$warna_table_grandtotal = '7CFC00';
			$content_html = "";
			$table_start_row = 0;

			if ($this->excel_flag == 0) {			
				$content_html.= "<table style='font-size:10pt!important'>";
				$content_html.= "<tr style='background-color:#".$warna_table_header.";'>";
				
				$content_html.= "	<th style='min-width:100px'>Divisi</th>";
				$content_html.= "	<th style='min-width:100px'>Merk</th>";
				$content_html.= "	<th style='min-width:100px'>Wilayah</th>";
			} else {		
				$this->currcol = 1;				
				$this->currrow++;
				$table_start_row = $this->currrow;

				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Divisi');
				$sheet->getColumnDimension('A')->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Merk');
				$sheet->getColumnDimension('B')->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Wilayah');
				$sheet->getColumnDimension('C')->setWidth(15);
				$this->currcol += 1;
			}
			
			$date = mktime(0,0,0,$data['p_bln'],1,$data['p_thn']);
			$days= date('t',$date);
			
			$grand_total = array();
			$grand_total[0] = 0;
			
			$divisi_total = array();
			
			for($i=1; $i<=$days;$i++){
				$grand_total[$i] = 0;

				if($this->excel_flag == 0){
					$content_html.= "<th width='100px'>".str_pad($i, 2, '0', STR_PAD_LEFT)."</th>";
				} else {
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $i);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('00');
					$this->currcol += 1;
				}
			}
			
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'SUBTOTAL');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
				$this->currcol += 1;
			} else {
				$content_html.= "<th width='100px'>SUBTOTAL</th>";
				$content_html.= "</tr>";
			}
						
			$max_col = $this->currcol-2;

			$PenjualanNasional = $dataReport["detail"];
			$headers = $dataReport["data"];
			// $productList = $dataReport["productList"];

			for ($x=0; $x<count($headers); $x++) {
				$hd = $headers[$x];

				if ($this->excel_flag == 0) {
					$content_html.= "<tr>";
					$content_html.= "<td>".$hd["Divisi"]."</td>";
					$content_html.= "<td>".$hd["Merk"]."</td>";
					$content_html.= "<td>".$hd["Wilayah"]."</td>";
				} else {
					$this->currrow++;
					$this->currcol = 1;
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Divisi"]);
					$this->currcol += 1;
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Merk"]);
					$this->currcol += 1;
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Wilayah"]);
					$this->currcol += 1;
				}

				$sub_total= 0;
				for($i=1; $i<=$days;$i++){
					$ada = 0;
					for($y=0; $y<count($PenjualanNasional); $y++) {
						$detail = $PenjualanNasional[$y];

						if($i==$detail["Tgl"] && $hd["Divisi"]==$detail["Divisi"] && $hd["Merk"]==$detail["Merk"] && $hd["Wilayah"]==$detail["Wilayah"]){
							$sub_total += ($detail["Total"]);
							$grand_total[$i] += ($detail["Total"]);
							
							$divisi_total[$detail["Divisi"]][$i] = (ISSET($divisi_total[$detail["Divisi"]][$i]) ? $divisi_total[$detail["Divisi"]][$i] : 0) + ($detail["Total"]);

							if($this->excel_flag == 0){
								$content_html.= "<td class='td-right'>".number_format($detail["Total"],0)."</td>";
							} else {
								$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $detail["Total"]);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
								$this->currcol += 1;
							}
							$ada = 1;
							break;
						}
					}
					if($ada == 0){
						if($this->excel_flag == 0){
							$content_html.= "<td class='td-right'>0</td>";
						} else {
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 0);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
							$this->currcol += 1;
						}
					}
				}

				if ($this->excel_flag == 0) {
					$content_html.= "<td class='td-right td-bold'>".number_format($sub_total,0)."</td>";
					$content_html.= "</tr>";
				} else {	
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $sub_total);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getFont()->setBold(true);	
					$this->currcol += 1;
				}
				
				//subtotal per divisi
				$next_x = $x+1;
				if((ISSET($headers[$next_x]) && $headers[$next_x]['Divisi']!=$headers[$x]['Divisi']) || ($x==count($headers)-1)){
					if ($this->excel_flag == 0) {
						$content_html.= "<tr style='background-color:#".$warna_table_footer.";'>";
						$content_html.= "<td colspan='3' class='td-bold'>TOTAL DIVISI ".$headers[$x]['Divisi']."</td>";
					} else {
						$this->currrow++;
						$this->currcol = 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "TOTAL DIVISI ".$headers[$x]['Divisi']);
						$sheet->mergeCellsByColumnAndRow($this->currcol, $this->currrow, $this->currcol+2 , $this->currrow);
						$this->currcol += 3;
					}
					$subtotal_divisi = 0;
					for($i=1; $i<=$days;$i++){
						$cur_total_divisi = (ISSET($divisi_total[$headers[$x]['Divisi']][$i]) ? $divisi_total[$headers[$x]['Divisi']][$i] : 0);
						$subtotal_divisi += $cur_total_divisi;
						
						if ($this->excel_flag == 0) {
							$content_html.= "<td class='td-right td-bold'>".number_format($cur_total_divisi,0)."</td>";
						}
						else {
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $cur_total_divisi);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getFont()->setBold(true);	
							$this->currcol += 1;
						}
					}
					
					if ($this->excel_flag == 0) {
						$content_html.= "<td class='td-right td-bold'>".number_format($subtotal_divisi,0)."</td>";
						$content_html.= "</tr>";
					} else {
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $subtotal_divisi);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle("A".$this->currrow.":".PHPExcel_Cell::stringFromColumnIndex($max_col).$this->currrow)->getFont()->setBold(true);	
						
						$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
						$sheet->getStyle('A'.$this->currrow.':'.PHPExcel_Cell::stringFromColumnIndex($max_col).$this->currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_footer);
					}
				}
			}
			
			
			if($this->excel_flag == 0){
				$content_html.= "<tr style='background-color:#".$warna_table_grandtotal.";'>";
				$content_html.= "<td colspan='3' class='td-bold'>GRANDTOTAL</td>";
			} else {
				$this->currrow++;
				$this->currcol = 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'GRANDTOTAL');
				$sheet->mergeCellsByColumnAndRow($this->currcol, $this->currrow, $this->currcol+2 , $this->currrow);
				$this->currcol += 3;
			}
			
			$grand_total_all = 0;
			for($i=1; $i<=$days;$i++){
				if ($this->excel_flag == 0) {
					$content_html.= "<td class='td-right td-bold'>".number_format($grand_total[$i])."</td>";
				} else {
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total[$i]);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
					$this->currcol += 1;
				}
				$grand_total_all += $grand_total[$i];
			}
						if($this->excel_flag == 0){
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
				$content_html.= "</tr>";
				$content_html.= "</table>";				

				return($content_html);
			} else {
			
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
				$this->currcol += 1;
			
				$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
				
				$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
				$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
				
				$sheet->getStyle('A'.$table_start_row.':C'.$table_start_row)->getAlignment()->setHorizontal($alignment_left);			
				$sheet->getStyle('D'.$table_start_row.':'.$max_col.'5')->getAlignment()->setHorizontal($alignment_center);
				
				$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
				
				$sheet->getStyle('A'.$table_start_row.':'.$max_col.$table_start_row)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
				$sheet->getStyle('A'.$this->currrow.':'.$max_col.$this->currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_grandtotal);
				
				// bold
				$sheet->getStyle("A1:".$max_col.$table_start_row)->getFont()->setBold(true);
				$sheet->getStyle("A".$this->currrow.":".$max_col.$this->currrow)->getFont()->setBold(true);	
				// border
				$styleArray = [
				'borders' => [
				'allBorders' => [
				'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
				],
				];
				$sheet ->getStyle('A'.$table_start_row.':'.$max_col.$this->currrow)->applyFromArray($styleArray);				

				return "";
			}
		}

		public function PreviewA05($data, $dataReport, $sheet)
		{			
			$warna_table_header = 'f2f2f2';
			$warna_table_footer = 'b7dee8';
			$warna_table_grandtotal = '7CFC00';
			$content_html = "";
			$table_start_row = 0;

			if ($this->excel_flag == 0) {			
				$content_html.= "<table style='font-size:10pt!important'>";
				$content_html.= "<tr style='background-color:#".$warna_table_header.";'>";
				
				$content_html.= "	<th style='min-width:100px'>Divisi</th>";
				$content_html.= "	<th style='min-width:100px'>Merk</th>";
				$content_html.= "	<th style='min-width:100px'>Cabang</th>";
			} else {		
				$this->currcol = 1;				
				$this->currrow++;
				$table_start_row = $this->currrow;

				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Divisi');
				$sheet->getColumnDimension('A')->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Merk');
				$sheet->getColumnDimension('B')->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Cabang');
				$sheet->getColumnDimension('C')->setWidth(15);
				$this->currcol += 1;
			}
			
			$date = mktime(0,0,0,$data['p_bln'],1,$data['p_thn']);
			$days= date('t',$date);
			
			$grand_total = array();
			$grand_total[0] = 0;
			
			for($i=1; $i<=$days;$i++){
				$grand_total[$i] = 0;

				if($this->excel_flag == 0){
					$content_html.= "<th width='100px'>".str_pad($i, 2, '0', STR_PAD_LEFT)."</th>";
				} else {
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $i);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('00');
					$this->currcol += 1;
				}
			}
			
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'SUBTOTAL');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
				$this->currcol += 1;
			} else {
				$content_html.= "<th width='100px'>SUBTOTAL</th>";
				$content_html.= "</tr>";
			}
						
			$max_col = $this->currcol-2;

			$PenjualanNasional = $dataReport["detail"];
			$headers = $dataReport["data"];

			for ($x=0; $x<count($headers); $x++) {
				$hd = $headers[$x];

				if ($this->excel_flag == 0) {
					$content_html.= "<tr>";
					$content_html.= "<td>".$hd["Divisi"]."</td>";
					$content_html.= "<td>".$hd["Merk"]."</td>";
					$content_html.= "<td>".$hd["Wilayah"]."</td>";
				} else {
					$this->currrow++;
					$this->currcol = 1;
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Divisi"]);
					$this->currcol += 1;
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Merk"]);
					$this->currcol += 1;
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Wilayah"]);
					$this->currcol += 1;
				}

				$sub_total= 0;
				for($i=1; $i<=$days;$i++){
					$ada = 0;
					for($y=0; $y<count($PenjualanNasional); $y++) {
					// foreach ($details as $detail) {
						$detail = $PenjualanNasional[$y];

						if($i==$detail["Tgl"] && $hd["Divisi"]==$detail["Divisi"] && $hd["Merk"]==$detail["Merk"] && $hd["Wilayah"]==$detail["Wilayah"]){
							$sub_total += ($detail["Total"]);
							$grand_total[$i] += ($detail["Total"]);

							if($this->excel_flag == 0){
								$content_html.= "<td class='td-right'>".number_format($detail["Total"],0)."</td>";
							} else {
								$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $detail["Total"]);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
								$this->currcol += 1;
							}
							$ada = 1;
							break;
						}
					}
					if($ada == 0){
						if($this->excel_flag == 0){
							$content_html.= "<td class='td-right'>0</td>";
						} else {
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 0);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
							$this->currcol += 1;
						}
					}
				}

				if ($this->excel_flag == 0) {
					$content_html.= "<td class='td-right td-bold'>".number_format($sub_total,0)."</td>";
					$content_html.= "</tr>";
				} else {	
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $sub_total);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getFont()->setBold(true);	
					$this->currcol += 1;
				}	
			}
			
			
			if($this->excel_flag == 0){
				$content_html.= "<tr style='background-color:#".$warna_table_footer.";'>";
				$content_html.= "<td colspan='3'>GRANDTOTAL</td>";
			} else {
				$this->currrow++;
				$this->currcol = 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'GRANDTOTAL');
				$sheet->mergeCellsByColumnAndRow($this->currcol, $this->currrow, $this->currcol+2 , $this->currrow);
				$this->currcol += 3;
			}
			
			$grand_total_all = 0;
			for($i=1; $i<=$days;$i++){
				if ($this->excel_flag == 0) {
					$content_html.= "<td class='td-right td-bold'>".number_format($grand_total[$i])."</td>";
				} else {
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total[$i]);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
					$this->currcol += 1;
				}
				$grand_total_all += $grand_total[$i];
			}

			if($this->excel_flag == 0){
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
				$content_html.= "</tr>";
				$content_html.= "</table>";				

				return($content_html);
			} else {
			
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
				$this->currcol += 1;
			
				$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
				
				$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
				$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
				
				$sheet->getStyle('A'.$table_start_row.':C'.$table_start_row)->getAlignment()->setHorizontal($alignment_left);			
				$sheet->getStyle('D'.$table_start_row.':'.$max_col.'5')->getAlignment()->setHorizontal($alignment_center);
				
				$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
				
				$sheet->getStyle('A'.$table_start_row.':'.$max_col.$table_start_row)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
				$sheet->getStyle('A'.$this->currrow.':'.$max_col.$this->currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_footer);
				
				// bold
				$sheet->getStyle("A1:".$max_col.$table_start_row)->getFont()->setBold(true);
				$sheet->getStyle("A".$this->currrow.":".$max_col.$this->currrow)->getFont()->setBold(true);	
				// border
				$styleArray = [
				'borders' => [
				'allBorders' => [
				'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
				],
				];
				$sheet ->getStyle('A'.$table_start_row.':'.$max_col.$this->currrow)->applyFromArray($styleArray);				

				return "";
			}
		}

		public function PreviewB($data, $dataReport, $sheet)
		{			
			$warna_table_header = 'f2f2f2';
			$warna_table_kategori = 'b7dee8';
			$warna_table_merk = 'fbff80';
			$warna_table_divisi = '9aed9e';
			$warna_table_grandtotal = 'c7c985';
			$content_html = "";
			$table_start_row = 0;

			$PenjualanNasional = $dataReport["detail"];
			$headers = $dataReport["data"];
			$wilayahList = $dataReport["wilayahList"];

			if ($this->excel_flag == 0) {			
				$content_html.= "<table style='font-size:10pt!important'>";
				$content_html.= "<tr style='background-color:#".$warna_table_header.";'>";
				
				$content_html.= "	<th style='min-width:100px'>Divisi</th>";
				$content_html.= "	<th style='min-width:100px'>Merk</th>";
				$content_html.= "	<th style='min-width:100px'>Kode Barang</th>";
				$content_html.= "	<th style='min-width:100px'>Kategori</th>";
			} else {		
				$this->currcol = 1;				
				$this->currrow++;
				$table_start_row = $this->currrow;

				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Divisi');
				$sheet->getColumnDimension('A')->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Merk');
				$sheet->getColumnDimension('B')->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Kode Barang');
				$sheet->getColumnDimension('C')->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Kategori');
				$sheet->getColumnDimension('D')->setWidth(15);
				$this->currcol += 1;
			}
			
			$date = mktime(0,0,0,$data['p_bln'],1,$data['p_thn']);
			$days= date('t',$date);
			
			$total_kategori = array();
			$total_merk = array();
			$total_divisi = array();
			$grand_total = array();

			$total_kategori_all = 0;
			$total_merk_all = 0;
			$total_divisi_all = 0;
			
			for($i=0; $i<count($wilayahList); $i++){
				$grand_total[$i] = 0;

				if($this->excel_flag == 0){
					$content_html.= "<th width='100px'>".$wilayahList[$i]["Wilayah"]."</th>";
				} else {
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $wilayahList[$i]["Wilayah"]);
					$this->currcol += 1;
				}
			}

			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'SUBTOTAL');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'SUBTOTAL KATEGORI');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'SUBTOTAL MERK');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'SUBTOTAL DIVISI');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
				$this->currcol += 1;
			} else {
				$content_html.= "<th width='100px'>SUBTOTAL</th>";
				$content_html.= "<th width='100px'>SUBTOTAL KATEGORI</th>";
				$content_html.= "<th width='100px'>SUBTOTAL MERK</th>";
				$content_html.= "<th width='100px'>SUBTOTAL DIVISI</th>";
				$content_html.= "</tr>";
			}
						
			$max_col = $this->currcol-2;
			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			

			$last_division = "";
			$last_brand = "";
			$last_category = "";


			for($y=0; $y <= count($PenjualanNasional); $y++) {
				if ($y == count($PenjualanNasional)) {
					$hd["Kategori"] = "LAST";
					$hd["Merk"] = "LAST";
					$hd["Divisi"] = "LAST";
				} else {
					$hd = $PenjualanNasional[$y];					
				}

				if ($last_category == "") {
					$last_category = $hd["Kategori"];
					for($i=0; $i<count($wilayahList); $i++){
						$total_kategori[$i] = 0;
					}
				} else if ($last_division <> $hd["Divisi"] || $last_brand <> $hd["Merk"] || $last_category <> $hd["Kategori"]) {
					if($this->excel_flag == 0){
						$content_html.= "<tr style='background-color:#".$warna_table_kategori.";'>";
						$content_html.= "<td colspan='4' class='td-right td-bold'>TOTAL $last_division - $last_brand - $last_category</td>";
					} else {
						$this->currrow++;
						$this->currcol = 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'TOTAL '.$last_division.' - '.$last_brand.' - '.$last_category);
						$sheet->mergeCellsByColumnAndRow($this->currcol, $this->currrow, $this->currcol+3 , $this->currrow);
						$this->currcol += 4;
					}
					
					$total_kategori_all = 0;
					for($i=0; $i<count($wilayahList); $i++){
						if ($this->excel_flag == 0) {
							$content_html.= "<td class='td-right td-bold'>".number_format($total_kategori[$i])."</td>";
						} else {
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $total_kategori[$i]);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle("A".$this->currrow.":".$max_col.$this->currrow)->getFont()->setBold(true);	
							$sheet->getStyle('A'.$this->currrow.':'.$max_col.$this->currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_kategori);
							$this->currcol += 1;
						}
						$total_kategori_all += $total_kategori[$i];
					}
					
					if($this->excel_flag == 0){
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".number_format($total_kategori_all)."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "</tr>";

					} else {
					
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $total_kategori_all);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
					}
					//baris total kategori
					$last_category = $hd["Kategori"];
					$total_kategori = array();
					for($i=0; $i<count($wilayahList); $i++){
						$total_kategori[$i] = 0;
					}
				}
				if ($last_brand == "") {
					$last_brand = $hd["Merk"];
					for($i=0; $i<count($wilayahList); $i++){
						$total_merk[$i] = 0;
					}
				} else if ($last_division <> $hd["Divisi"] || $last_brand <> $hd["Merk"]) {
					if($this->excel_flag == 0){
						$content_html.= "<tr style='background-color:#".$warna_table_merk.";'>";
						$content_html.= "<td colspan='4' class='td-right td-bold'>TOTAL $last_division - $last_brand</td>";
					} else {
						$this->currrow++;
						$this->currcol = 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'TOTAL '.$last_division.' - '.$last_brand);
						$sheet->mergeCellsByColumnAndRow($this->currcol, $this->currrow, $this->currcol+3 , $this->currrow);
						$sheet->getStyle("A".$this->currrow.":".$max_col.$this->currrow)->getFont()->setBold(true);	
						$sheet->getStyle('A'.$this->currrow.':'.$max_col.$this->currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_merk);
						$this->currcol += 4;
					}
					
					$total_merk_all = 0;
					for($i=0; $i<count($wilayahList); $i++){
						if ($this->excel_flag == 0) {
							$content_html.= "<td class='td-right td-bold'>".number_format($total_merk[$i])."</td>";
						} else {
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $total_merk[$i]);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
							$this->currcol += 1;
						}
						$total_merk_all += $total_merk[$i];
					}
					
					if($this->excel_flag == 0){
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".number_format($total_merk_all)."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "</tr>";

					} else {
					
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $total_merk_all);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
					}
					
					$last_brand = $hd["Merk"];
					$total_merk = array();
					for($i=0; $i<count($wilayahList); $i++){
						$total_merk[$i] = 0;
					}
				}
				if ($last_division == "") {
					$last_division = $hd["Divisi"];
					for($i=0; $i<count($wilayahList); $i++){
						$total_divisi[$i] = 0;
					}
				} else if ($last_division <> $hd["Divisi"]) {
					if($this->excel_flag == 0){
						$content_html.= "<tr style='background-color:#".$warna_table_divisi.";'>";
						$content_html.= "<td colspan='4' class='td-right td-bold'>TOTAL $last_division</td>";
					} else {
						$this->currrow++;
						$this->currcol = 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'TOTAL '.$last_division);
						$sheet->mergeCellsByColumnAndRow($this->currcol, $this->currrow, $this->currcol+3 , $this->currrow);
						$sheet->getStyle("A".$this->currrow.":".$max_col.$this->currrow)->getFont()->setBold(true);	
						$sheet->getStyle('A'.$this->currrow.':'.$max_col.$this->currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_divisi);
						$this->currcol += 4;
					}
					
					$total_divisi_all = 0;
					for($i=0; $i<count($wilayahList); $i++){
						if ($this->excel_flag == 0) {
							$content_html.= "<td class='td-right td-bold'>".number_format($total_divisi[$i])."</td>";
						} else {
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $total_divisi[$i]);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
							$this->currcol += 1;
						}
						$total_divisi_all += $total_divisi[$i];
					}
					
					if($this->excel_flag == 0){
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".number_format($total_divisi_all)."</td>";
						$content_html.= "</tr>";

					} else {
					
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $total_divisi_all);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
						$this->currcol += 1;
					}
					
					$last_division = $hd["Divisi"];
					$total_divisi = array();
					for($i=0; $i<count($wilayahList); $i++){
						$total_divisi[$i] = 0;
					}
				}

				if ($y < count($PenjualanNasional)) {

					if ($this->excel_flag == 0) {
						$content_html.= "<tr>";
						$content_html.= "<td>".$hd["Divisi"]."</td>";
						$content_html.= "<td>".$hd["Merk"]."</td>";
						$content_html.= "<td>".$hd["Kd_Brg"]."</td>";
						$content_html.= "<td>".$hd["Kategori"]."</td>";
					} else {
						$this->currrow++;
						$this->currcol = 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Divisi"]);
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Merk"]);
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Kd_Brg"]);
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Kategori"]);
						$this->currcol += 1;
					}

					$sub_total= 0;

					for($i=0; $i<count($wilayahList); $i++){
						$w = $wilayahList[$i]["Wilayah"];
						// die($hd[$w]);

						$total = (($hd[$w]==null)? 0: $hd[$w]);

						$total_kategori[$i] += $total;
						$total_merk[$i] += $total;
						$total_divisi[$i] += $total;
		
						$sub_total += $total;
						$grand_total[$i] += $total;

						if($this->excel_flag == 0){
							$content_html.= "<td class='td-right'>".number_format($total,0)."</td>";
						} else {
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $total);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
							$this->currcol += 1;
						}
					}

					if ($this->excel_flag == 0) {
						$content_html.= "<td class='td-right td-bold'>".number_format($sub_total,0)."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "</tr>";
					} else {	
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $sub_total);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getFont()->setBold(true);	
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
					}

				}
			}

			if($this->excel_flag == 0){
				$content_html.= "<tr style='background-color:#".$warna_table_grandtotal.";'>";
				$content_html.= "<td colspan='4' class='td-right td-bold'>GRANDTOTAL</td>";
			} else {
				$this->currrow++;
				$this->currcol = 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'GRANDTOTAL');
				$sheet->mergeCellsByColumnAndRow($this->currcol, $this->currrow, $this->currcol+3 , $this->currrow);
				$this->currcol += 4;
			}
			
			$grand_total_all = 0;
			for($i=0; $i<count($wilayahList); $i++){
				if ($this->excel_flag == 0) {
					$content_html.= "<td class='td-right td-bold'>".number_format($grand_total[$i])."</td>";
				} else {
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total[$i]);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
					$this->currcol += 1;
				}
				$grand_total_all += $grand_total[$i];
			}
			
			if($this->excel_flag == 0){
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
				$content_html.= "</tr>";
				$content_html.= "</table>";				

				return($content_html);

			} else {
			
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
				$this->currcol += 1;
			
				
				
				$sheet->getStyle('A'.$table_start_row.':C'.$table_start_row)->getAlignment()->setHorizontal($alignment_left);			
				$sheet->getStyle('D'.$table_start_row.':'.$max_col.'5')->getAlignment()->setHorizontal($alignment_center);
				
				$sheet->getStyle('A'.$table_start_row.':'.$max_col.$table_start_row)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
				$sheet->getStyle('A'.$this->currrow.':'.$max_col.$this->currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_grandtotal);
				
				// bold
				$sheet->getStyle("A1:".$max_col.$table_start_row)->getFont()->setBold(true);
				$sheet->getStyle("A".$this->currrow.":".$max_col.$this->currrow)->getFont()->setBold(true);	
				// border
				$styleArray = [
				'borders' => [
				'allBorders' => [
				'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
				],
				];
				$sheet ->getStyle('A'.$table_start_row.':'.$max_col.$this->currrow)->applyFromArray($styleArray);				

				return "";
			}
		}
		
		public function PreviewC($data, $dataReport, $sheet)
		{			
			$warna_table_header = 'f2f2f2';
			$warna_table_kdbrg = '9aed9e';
			$warna_table_kategori = 'b7dee8';
			$warna_table_merk = 'fbff80';
			$warna_table_divisi = '9aed9e';
			$warna_table_grandtotal = 'c7c985';
			$content_html = "";
			$table_start_row = 0;

			$PenjualanNasional = $dataReport["detail"];
			$headers = $dataReport["data"];
			$wilayahList = $dataReport["wilayahList"];

			if ($this->excel_flag == 0) {			
				$content_html.= "<table style='font-size:10pt!important'>";
				$content_html.= "<tr style='background-color:#".$warna_table_header.";'>";
				
				$content_html.= "	<th style='min-width:100px'>Divisi</th>";
				$content_html.= "	<th style='min-width:100px'>Merk</th>";
				$content_html.= "	<th style='min-width:100px'>Kategori</th>";
				$content_html.= "	<th style='min-width:100px'>Kode Barang</th>";
				$content_html.= "	<th style='min-width:100px'>Wilayah</th>";
			} else {		
				$this->currcol = 1;				
				$this->currrow++;
				$table_start_row = $this->currrow;

				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Divisi');
				$sheet->getColumnDimension('A')->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Merk');
				$sheet->getColumnDimension('B')->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Kategori');
				$sheet->getColumnDimension('D')->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Kode Barang');
				$sheet->getColumnDimension('C')->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Wilayah');
				$sheet->getColumnDimension('C')->setWidth(15);
				$this->currcol += 1;
			}
			
			$date = mktime(0,0,0,$data['p_bln'],1,$data['p_thn']);
			$days= date('t',$date);
			
			$total_product = array();
			$total_kategori = array();
			$total_merk = array();
			$total_divisi = array();
			$grand_total = array();

			$total_product_all = 0;
			$total_kategori_all = 0;
			$total_merk_all = 0;
			$total_divisi_all = 0;
			
			for($i=0; $i<12; $i++){
				$grand_total[$i] = 0;

				if($this->excel_flag == 0){
					$content_html.= "<th width='100px'>".$this->bulan[$i]."</th>";
				} else {
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $this->bulan[$i]);
					$this->currcol += 1;
				}
			}

			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'SUBTOTAL');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'SUBTOTAL BARANG');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'SUBTOTAL KATEGORI');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'SUBTOTAL MERK');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'SUBTOTAL DIVISI');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
				$this->currcol += 1;
			} else {
				$content_html.= "<th width='100px'>SUBTOTAL</th>";
				$content_html.= "<th width='100px'>SUBTOTAL BARANG</th>";
				$content_html.= "<th width='100px'>SUBTOTAL KATEGORI</th>";
				$content_html.= "<th width='100px'>SUBTOTAL MERK</th>";
				$content_html.= "<th width='100px'>SUBTOTAL DIVISI</th>";
				$content_html.= "</tr>";
			}
						
			$max_col = $this->currcol-2;
			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			

			$last_division = "";
			$last_brand = "";
			$last_category = "";
			$last_product = "";


			for($y=0; $y <= count($PenjualanNasional); $y++) {
				if ($y == count($PenjualanNasional)) {
					$hd["Kd_Brg"] = "LAST";
					$hd["Kategori"] = "LAST";
					$hd["Merk"] = "LAST";
					$hd["Divisi"] = "LAST";
				} else {
					$hd = $PenjualanNasional[$y];					
				}

				if ($last_product == "") {
					$last_product = $hd["Kd_Brg"];
					for($i=0; $i<12; $i++){
						$total_product[$i] = 0;
					}
				} else if ($last_product <> $hd["Kd_Brg"]) {
					if ($data['p_laporan']!="C01") {
						if($this->excel_flag == 0){
							$content_html.= "<tr style='background-color:#".$warna_table_kdbrg.";'>";
							$content_html.= "<td colspan='5' class='td-right td-bold'>TOTAL $last_product</td>";
						} else {
							$this->currrow++;
							$this->currcol = 1;
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'TOTAL '.$last_product);
							$sheet->mergeCellsByColumnAndRow($this->currcol, $this->currrow, $this->currcol+4 , $this->currrow);
							$this->currcol += 5;
						}
						
						$total_product_all = 0;
						for($i=0; $i<12; $i++){
							if ($this->excel_flag == 0) {
								$content_html.= "<td class='td-right td-bold'>".number_format($total_product[$i])."</td>";
							} else {
								$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $total_product[$i]);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
								$sheet->getStyle("A".$this->currrow.":".$max_col.$this->currrow)->getFont()->setBold(true);	
								$sheet->getStyle('A'.$this->currrow.':'.$max_col.$this->currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_kdbrg);
								$this->currcol += 1;
							}
							$total_product_all += $total_product[$i];
						}
						
						if($this->excel_flag == 0){
							$content_html.= "<td class='td-right td-bold'>".""."</td>";
							$content_html.= "<td class='td-right td-bold'>".number_format($total_product_all)."</td>";
							$content_html.= "<td class='td-right td-bold'>".""."</td>";
							$content_html.= "<td class='td-right td-bold'>".""."</td>";
							$content_html.= "<td class='td-right td-bold'>".""."</td>";
							$content_html.= "</tr>";

						} else {
						
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
							$this->currcol += 1;
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $total_product_all);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
							$this->currcol += 1;
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
							$this->currcol += 1;
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
							$this->currcol += 1;
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
							$this->currcol += 1;
						}
					}
					//baris total kategori
					$last_product = $hd["Kd_Brg"];
					$total_product = array();
					for($i=0; $i<12; $i++){
						$total_product[$i] = 0;
					}
				}

				if ($last_category == "") {
					$last_category = $hd["Kategori"];
					for($i=0; $i<12; $i++){
						$total_kategori[$i] = 0;
					}
				} else if ($last_category <> $hd["Kategori"]) {
					if($this->excel_flag == 0){
						$content_html.= "<tr style='background-color:#".$warna_table_kategori.";'>";
						$content_html.= "<td colspan='5' class='td-right td-bold'>TOTAL $last_division - $last_brand - $last_category</td>";
					} else {
						$this->currrow++;
						$this->currcol = 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'TOTAL '.$last_division.' - '.$last_brand.' - '.$last_category);
						$sheet->mergeCellsByColumnAndRow($this->currcol, $this->currrow, $this->currcol+4 , $this->currrow);
						$this->currcol += 5;
					}
					
					$total_kategori_all = 0;
					for($i=0; $i<12; $i++){
						if ($this->excel_flag == 0) {
							$content_html.= "<td class='td-right td-bold'>".number_format($total_kategori[$i])."</td>";
						} else {
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $total_kategori[$i]);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle("A".$this->currrow.":".$max_col.$this->currrow)->getFont()->setBold(true);	
							$sheet->getStyle('A'.$this->currrow.':'.$max_col.$this->currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_kategori);
							$this->currcol += 1;
						}
						$total_kategori_all += $total_kategori[$i];
					}
					
					if($this->excel_flag == 0){
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".number_format($total_kategori_all)."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "</tr>";

					} else {
					
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $total_kategori_all);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
					}
					//baris total kategori
					$last_category = $hd["Kategori"];
					$total_kategori = array();
					for($i=0; $i<12; $i++){
						$total_kategori[$i] = 0;
					}
				}

				if ($last_brand == "") {
					$last_brand = $hd["Merk"];
					for($i=0; $i<12; $i++){
						$total_merk[$i] = 0;
					}
				} else if ($last_brand <> $hd["Merk"]) {
					if($this->excel_flag == 0){
						$content_html.= "<tr style='background-color:#".$warna_table_merk.";'>";
						$content_html.= "<td colspan='5' class='td-right td-bold'>TOTAL $last_division - $last_brand</td>";
					} else {
						$this->currrow++;
						$this->currcol = 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'TOTAL '.$last_division.' - '.$last_brand);
						$sheet->mergeCellsByColumnAndRow($this->currcol, $this->currrow, $this->currcol+4 , $this->currrow);
						$sheet->getStyle("A".$this->currrow.":".$max_col.$this->currrow)->getFont()->setBold(true);	
						$sheet->getStyle('A'.$this->currrow.':'.$max_col.$this->currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_merk);
						$this->currcol += 5;
					}
					
					$total_merk_all = 0;
					for($i=0; $i<12; $i++){
						if ($this->excel_flag == 0) {
							$content_html.= "<td class='td-right td-bold'>".number_format($total_merk[$i])."</td>";
						} else {
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $total_merk[$i]);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
							$this->currcol += 1;
						}
						$total_merk_all += $total_merk[$i];
					}
					
					if($this->excel_flag == 0){
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".number_format($total_merk_all)."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "</tr>";

					} else {
					
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $total_merk_all);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
					}
					
					$last_brand = $hd["Merk"];
					$total_merk = array();
					for($i=0; $i<12; $i++){
						$total_merk[$i] = 0;
					}
				}

				if ($last_division == "") {
					$last_division = $hd["Divisi"];
					for($i=0; $i<12; $i++){
						$total_divisi[$i] = 0;
					}
				} else if ($last_division <> $hd["Divisi"]) {
					if($this->excel_flag == 0){
						$content_html.= "<tr style='background-color:#".$warna_table_divisi.";'>";
						$content_html.= "<td colspan='5' class='td-right td-bold'>TOTAL $last_division</td>";
					} else {
						$this->currrow++;
						$this->currcol = 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'TOTAL '.$last_division);
						$sheet->mergeCellsByColumnAndRow($this->currcol, $this->currrow, $this->currcol+4 , $this->currrow);
						$sheet->getStyle("A".$this->currrow.":".$max_col.$this->currrow)->getFont()->setBold(true);	
						$sheet->getStyle('A'.$this->currrow.':'.$max_col.$this->currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_divisi);
						$this->currcol += 5;
					}
					
					$total_merk_all = 0;
					for($i=0; $i<12; $i++){
						if ($this->excel_flag == 0) {
							$content_html.= "<td class='td-right td-bold'>".number_format($total_divisi[$i])."</td>";
						} else {
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $total_divisi[$i]);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
							$this->currcol += 1;
						}
						$total_divisi_all += $total_divisi[$i];
					}
					
					if($this->excel_flag == 0){
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".number_format($total_divisi_all)."</td>";
						$content_html.= "</tr>";

					} else {
					
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $total_divisi_all);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
						$this->currcol += 1;
					}
					
					$last_division = $hd["Divisi"];
					$total_divisi = array();
					for($i=0; $i<12; $i++){
						$total_divisi[$i] = 0;
					}
				}

				if ($y < count($PenjualanNasional)) {

					if ($this->excel_flag == 0) {
						$content_html.= "<tr>";
						$content_html.= "<td>".$hd["Divisi"]."</td>";
						$content_html.= "<td>".$hd["Merk"]."</td>";
						$content_html.= "<td>".$hd["Kd_Brg"]."</td>";
						$content_html.= "<td>".$hd["Kategori"]."</td>";
						$content_html.= "<td>".$hd["Wilayah"]."</td>";
					} else {
						$this->currrow++;
						$this->currcol = 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Divisi"]);
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Merk"]);
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Kategori"]);
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Kd_Brg"]);
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Wilayah"]);
						$this->currcol += 1;
					}

					$sub_total= 0;

					for($i=0; $i<12; $i++){
						$w = $this->bulan[$i];
						$total = (($hd[$w]==null)? 0: $hd[$w]);

						$total_product[$i] += $total;
						$total_kategori[$i] += $total;
						$total_merk[$i] += $total;
						$total_divisi[$i] += $total;
		
						$sub_total += $total;
						$grand_total[$i] += $total;

						if($this->excel_flag == 0){
							$content_html.= "<td class='td-right'>".number_format($total,0)."</td>";
						} else {
							$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $total);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
							$this->currcol += 1;
						}
					}

					if ($this->excel_flag == 0) {
						$content_html.= "<td class='td-right td-bold'>".number_format($sub_total,0)."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "<td class='td-right td-bold'>".""."</td>";
						$content_html.= "</tr>";
					} else {	
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $sub_total);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getFont()->setBold(true);	
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, "");
						$this->currcol += 1;
					}

				}
			}

			if($this->excel_flag == 0){
				$content_html.= "<tr style='background-color:#".$warna_table_grandtotal.";'>";
				$content_html.= "<td colspan='4' class='td-right td-bold'>GRANDTOTAL</td>";
			} else {
				$this->currrow++;
				$this->currcol = 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'GRANDTOTAL');
				$sheet->mergeCellsByColumnAndRow($this->currcol, $this->currrow, $this->currcol+3 , $this->currrow);
				$this->currcol += 4;
			}
			
			$grand_total_all = 0;
			for($i=0; $i<12; $i++){
				if ($this->excel_flag == 0) {
					$content_html.= "<td class='td-right td-bold'>".number_format($grand_total[$i])."</td>";
				} else {
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total[$i]);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
					$this->currcol += 1;
				}
				$grand_total_all += $grand_total[$i];
			}
			
			if($this->excel_flag == 0){
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
				$content_html.= "</tr>";
				$content_html.= "</table>";				

				return($content_html);

			} else {
			
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
				$this->currcol += 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
				$this->currcol += 1;
			
				
				
				$sheet->getStyle('A'.$table_start_row.':C'.$table_start_row)->getAlignment()->setHorizontal($alignment_left);			
				$sheet->getStyle('D'.$table_start_row.':'.$max_col.'5')->getAlignment()->setHorizontal($alignment_center);
				
				$sheet->getStyle('A'.$table_start_row.':'.$max_col.$table_start_row)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
				$sheet->getStyle('A'.$this->currrow.':'.$max_col.$this->currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_grandtotal);
				
				// bold
				$sheet->getStyle("A1:".$max_col.$table_start_row)->getFont()->setBold(true);
				$sheet->getStyle("A".$this->currrow.":".$max_col.$this->currrow)->getFont()->setBold(true);	
				// border
				$styleArray = [
				'borders' => [
				'allBorders' => [
				'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
				],
				];
				$sheet ->getStyle('A'.$table_start_row.':'.$max_col.$this->currrow)->applyFromArray($styleArray);				

				return "";
			}
		}

		public function PreviewD($data, $dataReport, $sheet)
		{			
			$warna_table_header = 'f2f2f2';
			$warna_table_kategori = 'b7dee8';
			$warna_table_merk = 'fbff80';
			$warna_table_divisi = '9aed9e';
			$warna_table_grandtotal = 'c7c985';
			$content_html = "";
			$table_start_row = 0;

			$PenjualanNasional = $dataReport["detail"];
			$headers = $dataReport["data"];
			$wilayahList = $dataReport["wilayahList"];

			if ($this->excel_flag == 0) {			
				$content_html.= "<table style='font-size:10pt!important'>";
				$content_html.= "<tr style='background-color:#".$warna_table_header.";'>";
				
				$content_html.= "	<th style='min-width:100px'>Wilayah</th>";
			} else {		
				$this->currcol = 1;				
				$this->currrow++;
				$table_start_row = $this->currrow;

				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'Wilayah');
				$sheet->getColumnDimension('A')->setWidth(15);
				$this->currcol += 1;
			}
			
			$date = mktime(0,0,0,$data['p_bln'],1,$data['p_thn']);
			$days= date('t',$date);
			
			// $total_kategori = array();
			// $total_merk = array();
			// $total_divisi = array();
			$grand_total = array();

			// $total_kategori_all = 0;
			// $total_merk_all = 0;
			// $total_divisi_all = 0;
			
			for($i=0; $i<count($wilayahList); $i++){
				$grand_total[$i] = 0;

				if($this->excel_flag == 0){
					$content_html.= "<th width='100px'>".$wilayahList[$i]["Divisi"]."</th>";
				} else {
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $wilayahList[$i]["Divisi"]);
					$this->currcol += 1;
				}
			}

			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'SUBTOTAL');
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1))->setWidth(15);
				$this->currcol += 1;
			} else {
				$content_html.= "<th width='100px'>SUBTOTAL</th>";
				$content_html.= "</tr>";
			}
						
			$max_col = $this->currcol-2;
			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			

			$last_division = "";
			$last_brand = "";
			$last_category = "";


			for($y=0; $y < count($PenjualanNasional); $y++) {
				$hd = $PenjualanNasional[$y];



				if ($this->excel_flag == 0) {
					$content_html.= "<tr>";
					$content_html.= "<td>".$hd["Wilayah"]."</td>";
				} else {
					$this->currrow++;
					$this->currcol = 1;
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $hd["Wilayah"]);
					$this->currcol += 1;
				}

				$sub_total= 0;

				for($i=0; $i<count($wilayahList); $i++){
					$w = $wilayahList[$i]["Divisi"];
					// die($hd[$w]);

					$total = (($hd[$w]==null)? 0: $hd[$w]);

					$sub_total += $total;
					$grand_total[$i] += $total;

					if($this->excel_flag == 0){
						$content_html.= "<td class='td-right'>".number_format($total,0)."</td>";
					} else {
						$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $total);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
						$this->currcol += 1;
					}
				}

				if ($this->excel_flag == 0) {
					$content_html.= "<td class='td-right td-bold'>".number_format($sub_total,0)."</td>";
					$content_html.= "</tr>";
				} else {	
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $sub_total);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getFont()->setBold(true);	
					$this->currcol += 1;
				}
				
			}

			if($this->excel_flag == 0){
				$content_html.= "<tr style='background-color:#".$warna_table_grandtotal.";'>";
				$content_html.= "<td class='td-right td-bold'>GRANDTOTAL</td>";
			} else {
				$this->currrow++;
				$this->currcol = 1;
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, 'GRANDTOTAL');
				$this->currcol ++;
			}
			
			$grand_total_all = 0;
			for($i=0; $i<count($wilayahList); $i++){
				if ($this->excel_flag == 0) {
					$content_html.= "<td class='td-right td-bold'>".number_format($grand_total[$i])."</td>";
				} else {
					$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total[$i]);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
					$this->currcol += 1;
				}
				$grand_total_all += $grand_total[$i];
			}
			
			if($this->excel_flag == 0){
				$content_html.= "<td class='td-right td-bold'>".number_format($grand_total_all)."</td>";
				$content_html.= "</tr>";
				$content_html.= "</table>";				

				return($content_html);

			} else {
			
				$sheet->setCellValueByColumnAndRow($this->currcol, $this->currrow, $grand_total_all);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($this->currcol-1).$this->currrow)->getNumberFormat()->setFormatCode('#,##0');
				$this->currcol += 1;		
				
				
				$sheet->getStyle('A'.$table_start_row.':C'.$table_start_row)->getAlignment()->setHorizontal($alignment_left);			
				$sheet->getStyle('D'.$table_start_row.':'.$max_col.'5')->getAlignment()->setHorizontal($alignment_center);
				
				$sheet->getStyle('A'.$table_start_row.':'.$max_col.$table_start_row)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
				$sheet->getStyle('A'.$this->currrow.':'.$max_col.$this->currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_grandtotal);
				
				// bold
				$sheet->getStyle("A1:".$max_col.$table_start_row)->getFont()->setBold(true);
				$sheet->getStyle("A".$this->currrow.":".$max_col.$this->currrow)->getFont()->setBold(true);	
				// border
				$styleArray = [
				'borders' => [
				'allBorders' => [
				'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				],
				],
				];
				$sheet ->getStyle('A'.$table_start_row.':'.$max_col.$this->currrow)->applyFromArray($styleArray);				

				return "";
			}
		}

	}																																			