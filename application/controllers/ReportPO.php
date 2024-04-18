<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class ReportPO extends MY_Controller 
	{
		public $excel_flag = 0;
		public $maxtimeout = 900;
		
		public function __construct()
		{
			parent::__construct();
			$this->load->model('HelperModel');
			$this->load->helper('FormLibrary');
			$this->load->library('excel');
			ini_set("max_execution_time", 1500);
			ini_set('memory_limit', '1G');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
			$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
		}
		
		public function index()
		{
			$data = array();
			$api = 'APITES';
			
			set_time_limit(60);
			// die($this->API_URL);

			// $check_supplier = json_decode(file_get_contents($this->API_URL."/MsSupplier/GetSupplierList?api=".$api));
			$curl = curl_init($this->API_URL."/MsSupplier/GetSupplierList?api=".$api);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_ENCODING, '');
			$response = curl_exec($curl);
			curl_close($curl);
			$check_supplier = json_decode($response);
			// die(json_encode($check_supplier));
			
			// $check_laporan = json_decode(file_get_contents($this->API_URL."/ReportPO/CheckLaporan?api=".$api));
			$curl = curl_init($this->API_URL."/ReportPO/CheckLaporan?api=".$api);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_ENCODING, '');
			$response = curl_exec($curl);
			curl_close($curl);
			$check_laporan = json_decode($response);
			// die(json_encode($check_laporan));
			
			// $check_wilayah = json_decode(file_get_contents($this->API_URL."/MsWilayah/GetListWilayah?api=".$api));
			$curl = curl_init($this->API_URL."/MsWilayah/GetListWilayah?api=".$api);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_ENCODING, '');
			$response = curl_exec($curl);
			curl_close($curl);
			$check_wilayah = json_decode($response);
			// die(json_encode($check_wilayah));
			
			// $check_gudang = json_decode(file_get_contents($this->API_URL."/MsWilayah/GetListGudangD01?api=".$api));
			$curl = curl_init($this->API_URL."/MsWilayah/GetListGudangD01?api=".$api);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_ENCODING, '');
			$response = curl_exec($curl);
			curl_close($curl);
			$check_gudang = json_decode($response);
			// die(json_encode($check_gudang));
			
			// $check_divisi = json_decode(file_get_contents($this->API_URL."/MsDivisi/GetListDivisi?api=".$api));
			$curl = curl_init($this->API_URL."/MsDivisi/GetListDivisi?api=".$api);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_ENCODING, '');
			$response = curl_exec($curl);
			curl_close($curl);
			$check_divisi = json_decode($response);
			// die(json_encode($check_divisi));
			
			$data['title'] = 'Laporan Order Pembelian | '.WEBTITLE;
			$data['supplier'] = $check_supplier;
			$data['laporan'] = $check_laporan;
			$data['wilayah'] = $check_wilayah;
			$data['gudang'] = $check_gudang;
			$data['divisi'] = $check_divisi;

			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module']="LAPORAN ORDER PEMBELIAN"; 
	   	$params['TrxID'] = date("YmdHis");
			$params['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN ORDER PEMBELIAN";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params); 
			// print_r($data);
			$this->RenderView('ReportPOFormView',$data);
		}
		
		public function Proses()
		{
			// echo json_encode($_POST);die;
			$data = array();
			$page_title = 'Laporan Order Pembelian';
			
			if(isset($_POST["btnPreview"])){
				$this->excel_flag = 0;

				$params = array();   
				$params['LogDate'] = date("Y-m-d H:i:s");
				$params['UserID'] = $_SESSION["logged_in"]["userid"];
				$params['UserName'] = $_SESSION["logged_in"]["username"];
				$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
				$params['Module']="LAPORAN ORDER PEMBELIAN"; 
		   	$params['TrxID'] = date("YmdHis");
				$params['Description']=$_SESSION["logged_in"]["username"]." PROSES PREVIEW LAPORAN ".$_POST["laporan"];
				$params['Remarks']="";
				$params['RemarksDate'] = 'NULL';
				$this->ActivityLogModel->insert_activity($params); 
			}
			else{
				$this->excel_flag = 1;

				$params = array();   
				$params['LogDate'] = date("Y-m-d H:i:s");
				$params['UserID'] = $_SESSION["logged_in"]["userid"];
				$params['UserName'] = $_SESSION["logged_in"]["username"];
				$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
				$params['Module']="LAPORAN ORDER PEMBELIAN"; 
		   	$params['TrxID'] = date("YmdHis");
				$params['Description']=$_SESSION["logged_in"]["username"]." PROSES EXPORT EXCEL ".$_POST["laporan"];
		   	$params['Remarks']="";
		   	$params['RemarksDate'] = 'NULL';
		   	$this->ActivityLogModel->insert_activity($params); 
			}
		
			$this->load->library('form_validation');
			$this->form_validation->set_rules('laporan','Nama Laporan','required');
			$this->form_validation->set_rules('dp1','Tanggal Awal','required');
			$this->form_validation->set_rules('dp2','Tanggal Akhir','required');
			if(isset($_POST['supplier']))
			{
				$this->form_validation->set_rules('supplier','Supplier','required');				

				if ($_POST["laporan"]=='D01' ||$_POST["laporan"]=='D02' || $_POST["laporan"]=='D03')
				{
					$this->form_validation->set_rules('wilayah','Po Type','required');
					$this->form_validation->set_rules('gudang','Po Type','required'); 
				} 
				else if ($_POST["laporan"]!="A01" & $_POST["laporan"]!="A02")
				{
					$this->form_validation->set_rules('potype','Po Type','required');
				}
			}  else if(substr($_POST['laporan'],0,1)!='C'){
				$this->form_validation->set_rules('supplier','Supplier','required');
				$this->form_validation->set_rules('potype','Po Type','required');
			}
      
			if($this->form_validation->run())
			{
				if($_POST['supplier']!='')
				{
					$supplier = explode("#",$_POST["supplier"]);
					
					if($_POST["laporan"]=='A01'){
						$this->PreviewA01($_POST["laporan"],$_POST["dp1"], $_POST["dp2"], $supplier[0], $_POST["kategori"], $supplier[1], $_SESSION["logged_in"]["branch_id"], $params);
					}
					else if($_POST["laporan"]=='A02'){
						$this->PreviewA02($_POST["laporan"],$_POST["dp1"], $_POST["dp2"], $supplier[0], $_POST["kategori"], $supplier[1], $_SESSION["logged_in"]["branch_id"], $params);
					}
					else if ($_POST["laporan"]=='B01'){
						$this->Export_Pdf_B01($_POST["laporan"],$_POST["dp1"], $_POST["dp2"], $supplier[0], $_POST["kategori"], $supplier[1], $_POST["potype"], $_SESSION["logged_in"]["branch_id"], $params); 
					}
					else if ($_POST["laporan"]=='B02'){
						$this->Export_Pdf_B02($_POST["laporan"],$_POST["dp1"], $_POST["dp2"], $supplier[0], $_POST["kategori"], $supplier[1], $_POST["potype"], $_SESSION["logged_in"]["branch_id"], $params); 
					}
					else if ($_POST["laporan"]=='B03'){
						$this->Export_Pdf_B03($_POST["laporan"],$_POST["dp1"], $_POST["dp2"], $supplier[0], $_POST["kategori"], $supplier[1], $_POST["potype"], $_SESSION["logged_in"]["branch_id"], $params); 
					}
					else if ($_POST["laporan"]=='D01'){ 
						$this->Export_Pdf_D01($_POST["laporan"],$_POST["dp1"], $_POST["dp2"], $supplier[0], $_POST["kategori"],$_POST["wilayah"],$_POST["gudang"], $_POST["divisi"], $_SESSION["logged_in"]["branch_id"], $params); 
					}
					else if ($_POST["laporan"]=='D02'){
						$this->Export_Pdf_D02($_POST["laporan"],$_POST["dp1"], $_POST["dp2"], $supplier[0], $_POST["kategori"],$_POST["wilayah"],$_POST["gudang"], $_POST["divisi"], $_SESSION["logged_in"]["branch_id"], $params); 
					}
					else if ($_POST["laporan"]=='D03'){
						$this->Export_Pdf_D03($_POST["laporan"],$_POST["dp1"], $_POST["dp2"], $supplier[0], $_POST["kategori"],$_POST["wilayah"],$_POST["gudang"], $_POST["divisi"], $_SESSION["logged_in"]["branch_id"], $params); 

					}
					else redirect("ReportPO");
				} else {
				
					if ($_POST["laporan"]=='C01'){
						$this->PreviewC01($_POST["laporan"],$_POST["dp1"], $_POST["dp2"],$_POST["kategori"],$params); 
					}
					else if ($_POST["laporan"]=='C02'){
						$this->PreviewC02($_POST["laporan"],$_POST["dp1"], $_POST["dp2"],$_POST["kategori"],$params); 
					}
					else if ($_POST["laporan"]=='C03'){
						$this->PreviewC03($_POST["laporan"],$_POST["dp1"], $_POST["dp2"],$_POST["kategori"],$params); 
					}
					else redirect("ReportPO");
				}
			}
			else
			{
				redirect("ReportPO");
			}

		}
			
		public function set_warna($persen){
			$warna = '';
			if(floatval($persen) < 25){
				$warna = 'FF0000'; // red
			}
			elseif(floatval($persen) < 50){
				$warna = 'E66205'; // orange
			}
			elseif(floatval($persen) < 75){
				$warna = '0000FF'; // blue
			}
			else{
				$warna = '0D421B'; //dark green
			}
			return $warna;
			
		}
		
		public function PreviewA01($page_title, $p_tgl1, $p_tgl2, $p_supplier, $p_kategori, $p_supplier_nama, $p_branch, $params)
		{
			$data = array();
			$api = 'APITES';
			// $PemenuhanPO = json_decode(file_get_contents(JAVA_API_URL."/ReportPO/ProsesA01?api=".$api."&p_tgl1=".urlencode($p_tgl1)."&p_tgl2=".urlencode($p_tgl2)."&p_supplier=".urlencode($p_supplier)."&p_kategori=".urlencode($p_kategori)));
			$url = $this->API_URL."/ReportPO/ProsesA01?api=".$api."&p_tgl1=".urlencode($p_tgl1)."&p_tgl2=".urlencode($p_tgl2)."&p_supplier=".urlencode($p_supplier)."&p_kategori=".urlencode($p_kategori)."&p_branch=".urlencode($p_branch);
			// die($url);

			//$PemenuhanPO = json_decode(file_get_contents($url));
			// die($this->API_URL."/LaporanPemenuhanPO/ProsesA01?api=".$api."&p_tgl1=".urlencode($p_tgl1)."&p_tgl2=".urlencode($p_tgl2)."&p_supplier=".urlencode($p_supplier)."&p_kategori=".urlencode($p_kategori));
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => $this->maxtimeout,
				// CURLOPT_IGNORE_CONTENT_LENGTH => 1,
			));
			$PemenuhanPO =  curl_exec($curl);
			curl_close($curl);

			$PemenuhanPO = json_decode($PemenuhanPO);
			
			if(count($PemenuhanPO->detail)==0){
				$params['Remarks']="FAILED - Tidak Ada Data";
			  $params['RemarksDate'] = date("Y-m-d H:i:s");
			  $this->ActivityLogModel->update_activity($params);
				exit('Tidak ada data');
			}
			
			$style_col_ganjil = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#ffffcc;";
			$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#fff;";
			$style_col_total = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#f2f2f2;";
			$style_col_brg = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:left;padding-right:5px;padding-left:10px;background-color:#ccffcc;";
			
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			if($p_kategori=='P'){
				$p_kategori = "PRODUK";
			}
			
			if($p_kategori=='S'){
				$p_kategori = "SPAREPART";
			}
			
			$content_html = "<html>";
			$content_html = "<head>";
			
			$content_html = "<style>
			body{font-family:'Calibri',Arial;}
			table{padding:0;margin:0;border-collapse:collapse}
			td, th { border:1px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			</style>";
			
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "	<div><b style='size:+1;'>LAPORAN PEMENUHAN PO<b></div>";
			$content_html.= "	<div><b>Supplier : ".$p_supplier_nama."</b></div>";
			$content_html.= "	<div><b>Periode: ".$p_tgl1." sd ".$p_tgl2."</b></div>";
			$content_html.= "	<div><b>Kategori: ".$p_kategori."</b></div>";
			$content_html.= "</div>";	//close div_header
			
			if($this->excel_flag == 1){
				$sheet->setTitle('LaporanPemenuhanPO');
				$sheet->setCellValue('A1', 'LAPORAN PEMENUHAN PO');
				$sheet->getStyle('A1')->getFont()->setSize(20);
				$sheet->setCellValue('A2', 'Supplier : '.$p_supplier_nama);
				$sheet->setCellValue('A3', 'Periode : '.$p_tgl1.' sd '.$p_tgl2);
				$sheet->setCellValue('A4', 'Kategori : '.$p_kategori);
			}
			
			
			$content_html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
			
			$content_html.= "<table style='font-size:10pt!important'>";
			$content_html.= "<tr style='background-color:#ffe53b;'>";
			
			$content_html.= "	<th rowspan='2' style='min-width:80px'><br>Divisi</th>";
			$content_html.= "	<th rowspan='2' style='min-width:80px'><br>Merk</th>";
			$content_html.= "	<th rowspan='2' style='min-width:200px'><br>Kode Barang</th>";
			$content_html.= "	<th rowspan='2' style='min-width:300px'><br>Nama Produk/Sparepart</th>";
			
			$currcol = 1;
			$currrow = 6;
			
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
				$sheet->getColumnDimension('A')->setWidth(15);
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
				$sheet->getColumnDimension('B')->setWidth(15);
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Barang');
				$sheet->getColumnDimension('C')->setWidth(25);
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama Produk/Sparepart');
				$sheet->getColumnDimension('D')->setWidth(45);
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
				$currcol += 1;
			}
			
			
			foreach($PemenuhanPO->header as $hd){
				$content_html.= "<th colspan='3'>".$hd."</th>";
				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $hd);
					$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol + 2, $currrow);
					$currcol += 3;
				}
			}
			$content_html.= "<th colspan='3'>TOTAL</th>";
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "TOTAL");
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol + 2, $currrow);
				$currcol += 3;
			}
			$content_html.= "</tr>";
			
			$currcol = 5;
			$currrow++;
			
			$content_html.= "<tr style='background-color:#ffe53b;'>";
			foreach($PemenuhanPO->header as $hd){
				$content_html.= "<th style='width:80px'>QTY PO</th>";
				$content_html.= "<th style='width:80px'>QTY BPB</th>";
				$content_html.= "<th style='width:80px'>[%]</th>";
				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "QTY PO");
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "QTY BPB");
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "[%]");
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
					$currcol += 1;
				}
			}

			$content_html.= "<th style='width:80px'>QTY PO</th>";
			$content_html.= "<th style='width:80px'>QTY BPB</th>";
			$content_html.= "<th style='width:80px'>[%]</th>";
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "QTY PO");
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "QTY BPB");
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "[%]");
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
				$currcol += 1;
			}
			$content_html.= "</tr>";
			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			foreach($PemenuhanPO->detail as $Kd_Brg => $details) {
				$content_html.= "<tr>";
				$content_html.= "<td>".$details[0]->Divisi."</td>";
				$content_html.= "<td>".$details[0]->Merk."</td>";
				$content_html.= "<td>".$Kd_Brg."</td>";
				$content_html.= "<td>".$details[0]->Nama."</td>";
				if($this->excel_flag == 1){
					$currrow++;
					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $details[0]->Divisi);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $details[0]->Merk);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $Kd_Brg);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $details[0]->Nama);
					$currcol += 1;
				}
				
				
				$TotalQtyPO = 0;
				$TotalQtyBPB= 0;
				$PersentaseTotal =0;
				
				$warna_po = 'F5F5F5';
				$warna_bpb = 'FFF5EE';
				foreach ($PemenuhanPO->header as $col) {
					$ada = 0;
					foreach ($details as $detail) {
						if($col==$detail->Kd_Lokasi){
							$TotalQtyPO += $detail->QTY_PO;
							$TotalQtyBPB += $detail->Qty_BPB;

							$content_html.= "<td class='td-right' style='background:#".$warna_po."'>".number_format($detail->QTY_PO,0)."</td>";
							$content_html.= "<td class='td-right' style='background:#".$warna_bpb."'>".number_format($detail->Qty_BPB,0)."</td>";
							$content_html.= "<td class='td-right' style='font-weight:bold;color:#".$this->set_warna(floatval($detail->PemenuhanPO))."'>".number_format($detail->PemenuhanPO,2)."</td>";
							
							if($this->excel_flag == 1){
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->QTY_PO);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_po);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->Qty_BPB);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_bpb);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($detail->PemenuhanPO,2));
								
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->getColor()->setARGB($this->set_warna(floatval($detail->PemenuhanPO)));
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
								
								$currcol += 1;
							}
							$ada = 1;
						}
					}
					if($ada==0){
						$content_html.= "<td style='background:#".$warna_po."'></td><td style='background:#".$warna_bpb."'></td><td></td>";	
						if($this->excel_flag == 1){
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_po);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_bpb);
							$currcol += 1;
							$currcol += 1;
						}
					}
				}

				if ($TotalQtyPO != 0) {
					$PersentaseTotal = (($TotalQtyBPB*100)/$TotalQtyPO);
				} else {
					$PersentaseTotal = 0;
				}
				
				$content_html.= "<td class='td-right' style='background:#".$warna_po."'>".number_format($TotalQtyPO,0)."</td>";
				$content_html.= "<td class='td-right' style='background:#".$warna_bpb."'>".number_format($TotalQtyBPB,0)."</td>";
				$content_html.= "<td class='td-right' style='font-weight:bold;color:#".$this->set_warna(floatval($PersentaseTotal))."'>".number_format($PersentaseTotal,2)."</td>";
				
				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalQtyPO);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_po);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalQtyBPB);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_bpb);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($PersentaseTotal,2));
					
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->getColor()->setARGB($this->set_warna(floatval($PersentaseTotal)));
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
					
					$currcol += 1;
				}
				$content_html.= "</tr>";
			}
			$content_html.= "</table>";
			
			
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
			
			if($this->excel_flag == 1){
				//PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
				// $sheet->mergeCells('A1:J1');
				// for ($i = 'A'; $i !=   $sheet->getHighestColumn(); $i++) {
				// $sheet->getColumnDimension($i)->setAutoSize(TRUE);
				// }
				
				$filename='LaporanPemenuhanPO['.date('Ymd').']'; //save our workbook as this file name
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
			
			$content_html.= "</div>";
			$content_html.= "</body></html>";
			
			$data['title'] = $page_title;
			$data['content_html'] = $content_html;
			
	        
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			// $this->load->view('LaporanResultView',$data);
			$this->RenderView('LaporanResultView',$data);
		}
		
		public function PreviewA02($page_title, $p_tgl1, $p_tgl2, $p_supplier, $p_kategori, $p_supplier_nama, $p_branch, $params)
		{
			
			$data = array();
			$api = 'APITES';
			//set_time_limit(600);
			

			$url_preview= $this->API_URL."/ReportPO/ProsesA02?api=".$api."&p_tgl1=".urlencode($p_tgl1)."&p_tgl2=".urlencode($p_tgl2)."&p_supplier=".urlencode($p_supplier)."&p_kategori=".urlencode($p_kategori)."&p_branch=".urlencode($p_branch);
			// $url_preview = JAVA_API_URL."/ReportPO/ProsesA02?api=".$api."&p_tgl1=".urlencode($p_tgl1)."&p_tgl2=".urlencode($p_tgl2)."&p_supplier=".urlencode($p_supplier)."&p_kategori=".urlencode($p_kategori)."&p_user_name=".urlencode($p_supplier_nama);
			// die($url_preview);

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url_preview,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => $this->maxtimeout,
				// CURLOPT_IGNORE_CONTENT_LENGTH => 1,
			));
			$PemenuhanPO =  curl_exec($curl);
			curl_close($curl);

			$PemenuhanPO = json_decode($PemenuhanPO);

			if(count($PemenuhanPO->detail)==0){
				$params['Remarks']="FAILED - Tidak Ada Data";
			  $params['RemarksDate'] = date("Y-m-d H:i:s");
			  $this->ActivityLogModel->update_activity($params);
				exit('Tidak ada data');
			}
			
			$style_col_ganjil = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#ffffcc;";
			$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#fff;";
			$style_col_total = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#f2f2f2;";
			$style_col_brg = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:left;padding-right:5px;padding-left:10px;background-color:#ccffcc;";
			
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			
			if($p_kategori=='P'){
				$p_kategori = "PRODUK";
			}
			
			if($p_kategori=='S'){
				$p_kategori = "SPAREPART";
			}
			
			$content_html = "<html>";
			$content_html = "<head>";
			
			$content_html = "<style>
			body{font-family:'Calibri',Arial;}
			table{padding:0;margin:0;border-collapse:collapse}
			td, th { border:1px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			</style>";
			
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "	<div><h2>LAPORAN PEMENUHAN PO BERDASARKAN JENIS PO</h2></div>";
			$content_html.= "	<div><b>Supplier : ".$p_supplier_nama."</b></div>";
			$content_html.= "	<div><b>Periode: ".$p_tgl1." sd ".$p_tgl2."</b></div>";
			$content_html.= "	<div><b>Kategori: ".$p_kategori."</b></div>";
			$content_html.= "</div>";	//close div_header
			
			if($this->excel_flag == 1){
				$sheet->setTitle('LaporanPemenuhanPOPerJenisPO');
				$sheet->setCellValue('A1', 'LAPORAN PEMENUHAN PO BERDASARKAN JENIS PO');
				$sheet->getStyle('A1')->getFont()->setSize(20);
				$sheet->setCellValue('A2', 'Supplier : '.$p_supplier_nama);
				$sheet->setCellValue('A3', 'Periode : '.$p_tgl1.' sd '.$p_tgl2);
				$sheet->setCellValue('A4', 'Kategori : '.$p_kategori);
			}
			
			
			$content_html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
			
			$content_html.= "<table style='font-size:10pt!important'>";
			$content_html.= "<tr style='background-color:#ffe53b;'>";
			
			$content_html.= "	<th rowspan='2' style='min-width:80px'><br>Divisi</th>";
			$content_html.= "	<th rowspan='2' style='min-width:80px'><br>Merk</th>";
			$content_html.= "	<th rowspan='2' style='min-width:200px'><br>Kode Barang</th>";
			$content_html.= "	<th rowspan='2' style='min-width:300px'><br>Nama Produk/Sparepart</th>";
			
			$currcol = 1;
			$currrow = 6;
			
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
				$sheet->getColumnDimension('A')->setWidth(15);
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
				$sheet->getColumnDimension('B')->setWidth(15);
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Barang');
				$sheet->getColumnDimension('C')->setWidth(25);
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama Produk/Sparepart');
				$sheet->getColumnDimension('D')->setWidth(45);
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
				$currcol += 1;
			}
			
			
			foreach($PemenuhanPO->header as $hd){
				$content_html.= "<th colspan='15'>".$hd."</th>";
				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $hd);
					$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol + 14, $currrow);
					$currcol += 15;
				}
			}
			$content_html.= "<th colspan='12'>TOTAL</th>";
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "TOTAL");
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol + 14, $currrow);
				$currcol += 15;
			}
			$content_html.= "</tr>";
			
			$currcol = 5;
			$currrow++;
			
			$content_html.= "<tr style='background-color:#ffe53b;'>";
			foreach($PemenuhanPO->header as $hd){
				$content_html.= "<th style='width:80px'>PO MAJOR</th>";
				$content_html.= "<th style='width:80px'>BPB MAJOR</th>";
				$content_html.= "<th style='width:80px'>[%] MAJOR</th>";
				$content_html.= "<th style='width:80px'>PO RO</th>";
				$content_html.= "<th style='width:80px'>BPB RO</th>";
				$content_html.= "<th style='width:80px'>[%] RO</th>";
				$content_html.= "<th style='width:80px'>PO RO CAMPAIGN</th>";
				$content_html.= "<th style='width:80px'>BPB RO CAMPAIGN</th>";
				$content_html.= "<th style='width:80px'>[%] RO CAMPAIGN</th>";
				$content_html.= "<th style='width:80px'>PO OTHER</th>";
				$content_html.= "<th style='width:80px'>BPB OTHER</th>";
				$content_html.= "<th style='width:80px'>[%] OTHER</th>";
				$content_html.= "<th style='width:80px'>QTY PO</th>";
				$content_html.= "<th style='width:80px'>QTY BPB</th>";
				$content_html.= "<th style='width:80px'>[%]</th>";
				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "PO MAJOR");
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "BPB MAJOR");
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "[%] MAJOR");
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "PO RO");
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "BPB RO");
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "[%] RO");
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "PO RO CAMPAIGN");
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "BPB RO CAMPAIGN");
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "[%] RO CAMPAIGN");
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "PO OTHER");
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "BPB OTHER");
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "[%] OTHER");
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "QTY PO");
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "QTY BPB");
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "[%]");
					$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
					$currcol += 1;
				}
			}

			$content_html.= "<th style='width:80px'>QTY MAJOR</th>";
			$content_html.= "<th style='width:80px'>BPB MAJOR</th>";
			$content_html.= "<th style='width:80px'>[%] MAJOR</th>";
			$content_html.= "<th style='width:80px'>QTY RO</th>";
			$content_html.= "<th style='width:80px'>BPB RO</th>";
			$content_html.= "<th style='width:80px'>[%] RO</th>";
			$content_html.= "<th style='width:80px'>QTY RO CAMPAIGN</th>";
			$content_html.= "<th style='width:80px'>BPB RO CAMPAIGN</th>";
			$content_html.= "<th style='width:80px'>[%] RO CAMPAIGN</th>";
			$content_html.= "<th style='width:80px'>QTY OTHER</th>";
			$content_html.= "<th style='width:80px'>BPB OTHER</th>";
			$content_html.= "<th style='width:80px'>[%] OTHER</th>";
			$content_html.= "<th style='width:80px'>TOTAL PO</th>";
			$content_html.= "<th style='width:80px'>TOTAL BPB</th>";
			$content_html.= "<th style='width:80px'>[%] TOTAL</th>";
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "PO MAJOR");
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "BPB MAJOR");
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "[%] MAJOR");
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "PO RO");
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "BPB RO");
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "[%] RO");
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "PO RO CAMPAIGN");
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "BPB RO CAMPAIGN");
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "[%] RO CAMPAIGN");
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "PO OTHER");
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "BPB OTHER");
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "[%] OTHER");
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "TOTAL PO");
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "TOTAL BPB");
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "[%] TOTAL");
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
				$currcol += 1;
			}
			$content_html.= "</tr>";
			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			foreach($PemenuhanPO->detail as $Kd_Brg => $details) {
				$content_html.= "<tr>";
				$content_html.= "<td>".$details[0]->Divisi."</td>";
				$content_html.= "<td>".$details[0]->Merk."</td>";
				$content_html.= "<td>".$Kd_Brg."</td>";
				$content_html.= "<td>".$details[0]->Nama."</td>";
				if($this->excel_flag == 1){
					$currrow++;
					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $details[0]->Divisi);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $details[0]->Merk);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $Kd_Brg);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $details[0]->Nama);
					$currcol += 1;
				}
				
				
				$TotalQtyMAJOR = 0;
				$TotalBPBMAJOR = 0;
				$PersentaseTotalMAJOR = 0;
				$TotalQtyRO = 0;
				$TotalBPBRO = 0;
				$PersentaseTotalRO = 0;
				$TotalQtyROCampaign = 0;
				$TotalBPBROCampaign = 0;
				$PersentaseTotalROCampaign = 0;
				$TotalQtyOTHER = 0;
				$TotalBPBOTHER = 0;
				$PersentaseTotalOTHER = 0;
				$TotalQtyPO = 0;
				$TotalQtyBPB= 0;
				$PersentaseTotal =0;
				
				$warna_major = 'F0FFF0';
				$warna_ro = 'F0F8FF';
				$warna_ro_campaign = 'C5D9F1';
				$warna_other = 'FFF8DC';
				
				$warna_po = 'F5F5F5';
				$warna_bpb = 'FFF5EE';
				foreach ($PemenuhanPO->header as $col) {
					$ada = 0;
					// die(json_encode($details));	
					foreach ($details as $detail) {
						if($col==$detail->Kd_Lokasi){
							$TotalQtyMAJOR += $detail->QTY_MAJOR;
							$TotalQtyRO += $detail->QTY_RO;
							$TotalQtyROCampaign += $detail->QTY_RO_CAMPAIGN;
							$TotalQtyOTHER += $detail->QTY_OTHER;
							$TotalQtyPO += $detail->QTY_PO;
							$TotalBPBMAJOR += $detail->QTY_BPB_MAJOR;
							$TotalBPBRO += $detail->QTY_BPB_RO;
							$TotalBPBROCampaign += $detail->QTY_BPB_RO_CAMPAIGN;
							$TotalBPBOTHER += $detail->QTY_BPB_OTHER;
							$TotalQtyBPB += $detail->Qty_BPB;

							$content_html.= "<td class='td-right' style='background:#".$warna_major."'>".number_format($detail->QTY_MAJOR,0)."</td>";
							$content_html.= "<td class='td-right' style='background:#".$warna_major."'>".number_format($detail->QTY_BPB_MAJOR,0)."</td>";
							$content_html.= "<td class='td-right' style='background:#".$warna_major.";font-weight:bold;color:#".$this->set_warna(floatval($detail->PemenuhanPO_MAJOR))."'>".number_format($detail->PemenuhanPO_MAJOR,2)."</td>";
							$content_html.= "<td class='td-right' style='background:#".$warna_ro."'>".number_format($detail->QTY_RO,0)."</td>";
							$content_html.= "<td class='td-right' style='background:#".$warna_ro."'>".number_format($detail->QTY_BPB_RO,0)."</td>";
							$content_html.= "<td class='td-right' style='background:#".$warna_ro.";font-weight:bold;color:#".$this->set_warna(floatval($detail->PemenuhanPO_RO))."'>".number_format($detail->PemenuhanPO_RO,2)."</td>";
							
							$content_html.= "<td class='td-right' style='background:#".$warna_ro_campaign."'>".number_format($detail->QTY_RO_CAMPAIGN,0)."</td>";
							$content_html.= "<td class='td-right' style='background:#".$warna_ro_campaign."'>".number_format($detail->QTY_BPB_RO_CAMPAIGN,0)."</td>";
							$content_html.= "<td class='td-right' style='background:#".$warna_ro_campaign.";font-weight:bold;color:#".$this->set_warna(floatval($detail->PemenuhanPO_RO_CAMPAIGN))."'>".number_format($detail->PemenuhanPO_RO_CAMPAIGN,2)."</td>";
							
							$content_html.= "<td class='td-right' style='background:#".$warna_other."'>".number_format($detail->QTY_OTHER,0)."</td>";
							$content_html.= "<td class='td-right' style='background:#".$warna_other."'>".number_format($detail->QTY_BPB_OTHER,0)."</td>";
							$content_html.= "<td class='td-right' style='background:#".$warna_other.";font-weight:bold;color:#".$this->set_warna(floatval($detail->PemenuhanPO_OTHER))."'>".number_format($detail->PemenuhanPO_OTHER,2)."</td>";
							$content_html.= "<td class='td-right' style='background:#".$warna_po.";font-weight:bold;'>".number_format($detail->QTY_PO,0)."</td>";
							$content_html.= "<td class='td-right' style='background:#".$warna_bpb.";font-weight:bold;'>".number_format($detail->Qty_BPB,0)."</td>";
							$content_html.= "<td class='td-right' style='font-weight:bold;color:#".$this->set_warna(floatval($detail->PemenuhanPO))."'>".number_format($detail->PemenuhanPO,2)."</td>";
							
							if($this->excel_flag == 1){
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->QTY_MAJOR);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_major);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->QTY_BPB_MAJOR);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_major);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($detail->PemenuhanPO_MAJOR,2));
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_major);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->getColor()->setARGB($this->set_warna(floatval($detail->PemenuhanPO_MAJOR)));
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);								
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->QTY_RO);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_ro);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->QTY_BPB_RO);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_ro);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($detail->PemenuhanPO_RO,2));
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_ro);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->getColor()->setARGB($this->set_warna(floatval($detail->PemenuhanPO_RO)));
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
								$currcol += 1;

								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->QTY_RO_CAMPAIGN);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_ro_campaign);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->QTY_BPB_RO_CAMPAIGN);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_ro_campaign);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($detail->PemenuhanPO_RO_CAMPAIGN,2));
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_ro_campaign);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->getColor()->setARGB($this->set_warna(floatval($detail->PemenuhanPO_RO_CAMPAIGN)));
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
								$currcol += 1;
								
								
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->QTY_OTHER);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_other);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->QTY_BPB_OTHER);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_other);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($detail->PemenuhanPO_OTHER,2));
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_other);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->getColor()->setARGB($this->set_warna(floatval($detail->PemenuhanPO_OTHER)));
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->QTY_PO);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_po);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->Qty_BPB);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_bpb);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($detail->PemenuhanPO,2));
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->getColor()->setARGB($this->set_warna(floatval($detail->PemenuhanPO)));
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
								
								$currcol += 1;
							}
							$ada = 1;
						}
					}
					if($ada==0){
						$content_html.= "
						<td style='background:#".$warna_major."'></td>
						<td style='background:#".$warna_major."'></td>
						<td style='background:#".$warna_major."'></td>
						<td style='background:#".$warna_ro."'></td>
						<td style='background:#".$warna_ro."'></td>
						<td style='background:#".$warna_ro."'></td>
						
						<td style='background:#".$warna_ro_campaign."'></td>
						<td style='background:#".$warna_ro_campaign."'></td>
						<td style='background:#".$warna_ro_campaign."'></td>
						
						<td style='background:#".$warna_other."'></td>
						<td style='background:#".$warna_other."'></td>
						<td style='background:#".$warna_other."'></td>
						<td style='background:#".$warna_po."'></td>
						<td style='background:#".$warna_bpb."'></td>
						<td></td>
						";	
						if($this->excel_flag == 1){
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_major);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_major);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_major);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_ro);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_ro);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_ro);
							$currcol += 1;
							
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_ro_campaign);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_ro_campaign);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_ro_campaign);
							$currcol += 1;
							
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_other);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_other);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_other);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_po);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_bpb);
							$currcol += 1;
							$currcol += 1;
						}
					}
				}

				$PersentaseTotalMAJOR = (($TotalQtyMAJOR==0)?0:(($TotalBPBMAJOR*100)/$TotalQtyMAJOR));
				$PersentaseTotalRO = (($TotalQtyRO==0)?0:(($TotalBPBRO*100)/$TotalQtyRO));
				
				$PersentaseTotalROCampaign = (($TotalQtyROCampaign==0)?0:(($TotalBPBROCampaign*100)/$TotalQtyROCampaign));
				
				$PersentaseTotalOTHER = (($TotalQtyOTHER==0)?0:(($TotalBPBOTHER*100)/$TotalQtyOTHER));
				$PersentaseTotal = (($TotalQtyPO==0)?0:(($TotalQtyBPB*100)/$TotalQtyPO));

				$content_html.= "<td class='td-right' style='background:#".$warna_major."'>".number_format($TotalQtyMAJOR,0)."</td>";
				$content_html.= "<td class='td-right' style='background:#".$warna_major."'>".number_format($TotalBPBMAJOR,0)."</td>";
				$content_html.= "<td class='td-right' style='background:#".$warna_major.";font-weight:bold;color:#".$this->set_warna(floatval($PersentaseTotalMAJOR))."'>".number_format($PersentaseTotalMAJOR,2)."</td>";
				$content_html.= "<td class='td-right' style='background:#".$warna_ro."'>".number_format($TotalQtyRO,0)."</td>";
				$content_html.= "<td class='td-right' style='background:#".$warna_ro."'>".number_format($TotalBPBRO,0)."</td>";
				$content_html.= "<td class='td-right' style='background:#".$warna_ro.";font-weight:bold;color:#".$this->set_warna(floatval($PersentaseTotalRO))."'>".number_format($PersentaseTotalRO,2)."</td>";
				
				$content_html.= "<td class='td-right' style='background:#".$warna_ro_campaign."'>".number_format($TotalQtyROCampaign,0)."</td>";
				$content_html.= "<td class='td-right' style='background:#".$warna_ro_campaign."'>".number_format($TotalBPBROCampaign,0)."</td>";
				$content_html.= "<td class='td-right' style='background:#".$warna_ro_campaign.";font-weight:bold;color:#".$this->set_warna(floatval($PersentaseTotalROCampaign))."'>".number_format($PersentaseTotalROCampaign,2)."</td>";
				
				$content_html.= "<td class='td-right' style='background:#".$warna_other."'>".number_format($TotalQtyOTHER,0)."</td>";
				$content_html.= "<td class='td-right' style='background:#".$warna_other."'>".number_format($TotalBPBOTHER,0)."</td>";
				$content_html.= "<td class='td-right' style='background:#".$warna_other.";font-weight:bold;color:#".$this->set_warna(floatval($PersentaseTotalOTHER))."'>".number_format($PersentaseTotalOTHER,2)."</td>";
				$content_html.= "<td class='td-right' style='background:#".$warna_po.";font-weight:bold;'>".number_format($TotalQtyPO,0)."</td>";
				$content_html.= "<td class='td-right' style='background:#".$warna_bpb.";font-weight:bold;'>".number_format($TotalQtyBPB,0)."</td>";
				$content_html.= "<td class='td-right' style='font-weight:bold;color:#".$this->set_warna(floatval($PersentaseTotal))."'>".number_format($PersentaseTotal,2)."</td>";
				
				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalQtyMAJOR);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_major);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalBPBMAJOR);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_major);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($PersentaseTotalMAJOR,2));
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_major);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->getColor()->setARGB($this->set_warna(floatval($PersentaseTotalMAJOR)));
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalQtyRO);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_ro);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalBPBRO);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_ro);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($PersentaseTotalRO,2));
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_ro);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->getColor()->setARGB($this->set_warna(floatval($PersentaseTotalRO)));
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
					$currcol += 1;
					
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalQtyROCampaign);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_ro_campaign);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalBPBROCampaign);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_ro_campaign);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($PersentaseTotalROCampaign,2));
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_ro_campaign);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->getColor()->setARGB($this->set_warna(floatval($PersentaseTotalROCampaign)));
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
					$currcol += 1;
					
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalQtyOTHER);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_other);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalBPBOTHER);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_other);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($PersentaseTotalOTHER,2));
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_other);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->getColor()->setARGB($this->set_warna(floatval($PersentaseTotalOTHER)));
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalQtyPO);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_po);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalQtyBPB);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_bpb);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($PersentaseTotal,2));
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->getColor()->setARGB($this->set_warna(floatval($PersentaseTotal)));
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
					
					$currcol += 1;
				}
				$content_html.= "</tr>";
			}
			$content_html.= "</table>";
			
			
			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A6:'.$max_col.'7')->getAlignment()->setHorizontal($alignment_center);
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A6:'.$max_col.'7')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			// wrap header
			$sheet->getStyle('A6:'.$max_col.'7')->getAlignment()->setWrapText(true);
			// border
			$sheet->getStyle("A1:".$max_col."7")->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet ->getStyle('A6:'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');

			// die("here");
			
			if($this->excel_flag == 1){
				//PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
				// $sheet->mergeCells('A1:J1');
				// for ($i = 'A'; $i !=   $sheet->getHighestColumn(); $i++) {
				// $sheet->getColumnDimension($i)->setAutoSize(TRUE);
				// }
				
				$filename='LaporanPemenuhanPOPerJenisPO['.date('Ymd').']'; //save our workbook as this file name
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
			
			$content_html.= "</div>";
			$content_html.= "</body></html>";
			// die($content_html);
			$data['title'] = $page_title;
			$data['content_html'] = $content_html;

			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			// die(json_encode($data));
			// $this->load->view('LaporanResultView',$data);
			$this->RenderView('LaporanResultView',$data);
		} 
 
		public function Export_Pdf_B01($page_title, $p_tgl1, $p_tgl2, $p_supplier, $p_kategori, $p_supplier_nama, $p_potype, $p_branch, $params)
		{ 
			//ini_set('max_execution_time', '300');
			ini_set("pcre.backtrack_limit", "5000000");
			$api = 'APITES'; 

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->API_URL."/ReportPO/ProsesB01?api=".$api."&p_tgl1=".urlencode($p_tgl1)."&p_tgl2=".urlencode($p_tgl2)."&p_supplier=".urlencode($p_supplier)."&p_kategori=".urlencode($p_kategori)."&p_potype=".urlencode($p_potype). "&p_kode=B01"."&p_branch=".urlencode($p_branch),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => $this->maxtimeout,
				// CURLOPT_IGNORE_CONTENT_LENGTH => 1,
			));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $result;die;
			$result = json_decode($result,true);

			if(count($result['detail'])==0){
				$params['Remarks']="FAILED - Tidak Ada Data";
			  $params['RemarksDate'] = date("Y-m-d H:i:s");
			  $this->ActivityLogModel->update_activity($params);
				echo "Tidak ada data";
				die;
			}

			if($p_kategori=='P'){
				$p_kategori = "PRODUK";
			}
			
			if($p_kategori=='S'){
				$p_kategori = "SPAREPART";
			}

			if($p_potype=='IMPORT'){
				$p_potype = "PO Barang Import";
			}
			else
			if($p_potype=='MKT'){
				$p_potype = "PO Barang MP";
			} 
			else
			if($p_potype=='GA'){
				$p_potype = "PO Barang Umum";
			} 
			else
			{
				$p_potype = "PO Barang Lokal";
			} 

			$nama_laporan = 'Laporan PO Per Nomor'; 
			$html = '';

			$html .='
				<style>
				.table-bordered {border-collapse:collapse}
				.table-bordered th, .table-bordered td{border-left:0.5px solid #555;border-bottom:0.5px solid #555;padding:2px}
				.border{border-top:0.5px solid #555;border-right:0.5px solid #555;}
				.bold{font-weight:bold;}
				.right{text-align:right;}
				.center{text-align:center;}
				.bigXL{font-size:180%;}
				.big{font-size:150%;}
				.italic{font-style:italic;}
				.w100{width:100%;}
				.mb-10{margin-bottom:10px}
				td{vertical-align:top}
				.group{page-break-inside:avoid;}
				</style>
			'; 

			$i = 0;  


				
			$html .='<div  style="padding-top: 10px" class="group">';

			$html .='
				<table class="table-bordered w100 border">  
				'; 
			$html .= '<tr> 
				<td class="center" width="80px"> 
					<b> No PO </b>
				</td>
				<td class="center" width="80px"> 
					<b> Tgl PO </b>
				</td>
				<td class="center" width="60px"> 
					<b> Divisi </b>
				</td>
				<td class="center" width="60px"> 
					<b> Kd Lokasi </b>
				</td>
				<td class="center" width="80px"> 
					<b> Nama Supplier </b>
				</td>
				<td class="center" width="80px"> 
					<b> Kode Barang </b>
				</td>
				<td class="center" width="80px"> 
					<b> Qty </b>
				</td>
				<td class="center" width="80px"> 
					<b> Harga </b>
				</td>
				<td class="center" width="80px"> 
					<b> Disc </b>
				</td>
				<td class="center" width="80px"> 
					<b> DPP </b>
				</td>
				<td class="center" width="80px"> 
					<b> PPN </b>
				</td>
				<td class="center" width="80px"> 
					<b> Total </b>
				</td> 
			</tr>';

 

			$subTotal = 0;
			$totalPO = 0;
			foreach($result['detail'] as $data){
			 	$i++;
				
				$html .= '
					<tr> 
					<td>  
						'.$data['NoPO'].' 
					</td> 
					<td>  
						'.date("d-M-Y",strtotime($data['TglPO'])).' 
					</td> 
					<td>  
						'.$data['Divisi'].' 
					</td> 
					<td class="center"> 
						'.$data['Kd_Lokasi'].' 
					</td> 
					<td>  
						'.$data['Nm_Supplier'].' 
					</td> 
					<td class="center"> 
						'.$data['Kd_Brg'].' 
					</td> 
					<td class="right">  
						'.number_format($data['Qty'],0).' 
					</td> 
					<td class="right">  
						'.number_format($data['Harga'],2).' 
					</td> 
					<td class="right">  
						'.number_format($data['Disc'],2).' 
					</td > 
					<td class="right">  
						'.number_format($data['DPP'],2).' 
					</td > 
					<td class="right">  
						'.number_format($data['PPN'],2).' 
					</td > 
					<td class="right">  
						'.number_format($data['Total'],2).' 
					</td> 
					</tr>
					'; 


				$subTotal += $data['Total'] ;
				$totalPO++;
			} 

			$html .= '<tr> 
				<td class="left" colspan= "10"> 
					<b> Total No PO : '.$totalPO.'</b>
				</td>  
				<td class="center"> 
					<b> SUB TOTAL </b>
				</td>
				<td class="right"> 
					<b> '.number_format($subTotal,0).' </b>
				</td> 
			</tr>';

			$html .=' </table> '; 

			$html .='</div>';

			// echo $html;die;

			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'margin_left' => 10,
				'margin_right' => 10,
				'margin_top' => 45,
				'margin_bottom' => 20,
				'margin_header' => 10,
				'margin_footer' => 10,
				'orientation' => 'L'
			)); 
			$mpdf->SetHTMLHeader('
				<table width="100%">
					<tr>
						<td>
							'.date('d-M-Y H:i:s').'
						</td>
						<td class="right">
							Halaman {PAGENO} / {nbpg}
						</td>
					</tr>
				</table>
				<div class="big bold center">'.$nama_laporan.'</div>
				<div class="center mb-10">Periode '.date('d-M-Y',strtotime($p_tgl1)).' s/d '.date('d-M-Y',strtotime($p_tgl2)).'</div> 
				<table> 
					<tr> 
					<td> 
						Nama Supplier
					</td>
					<td> 
						:
					</td>
					<td> 
						'.$p_supplier_nama.'
					</td>
					</tr>
					<tr> 
					<td> 
						Kategori
					</td>
					<td> 
						:
					</td>
					<td> 
						'.$p_kategori.'
					</td>
					</tr>
					<tr> 
					<td> 
						PO type
					</td>
					<td> 
						:
					</td>
					<td> 
						'.$p_potype.'
					</td>
					</tr>
					</table>');
			$mpdf->WriteHTML($html);
			$mpdf->Output();
		}

		public function Export_Pdf_B02($page_title, $p_tgl1, $p_tgl2, $p_supplier, $p_kategori, $p_supplier_nama, $p_potype, $p_branch, $params)
		{ 
			//ini_set('max_execution_time', '300');
			ini_set("pcre.backtrack_limit", "5000000");
			$api = 'APITES'; 

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->API_URL."/ReportPO/ProsesB01?api=".$api."&p_tgl1=".urlencode($p_tgl1)."&p_tgl2=".urlencode($p_tgl2)."&p_supplier=".urlencode($p_supplier)."&p_kategori=".urlencode($p_kategori)."&p_potype=".urlencode($p_potype)."&p_kode=B02"."&p_branch=".urlencode($p_branch),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => $this->maxtimeout,
				// CURLOPT_IGNORE_CONTENT_LENGTH => 1,
			));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $result;die;
			$result = json_decode($result,true);

			if(count($result['detail'])==0){
				$params['Remarks']="FAILED - Tidak Ada Data";
			  $params['RemarksDate'] = date("Y-m-d H:i:s");
			  $this->ActivityLogModel->update_activity($params);
				echo "Tidak ada data";
				die;
			}

			if($p_kategori=='P'){
				$p_kategori = "PRODUK";
			}
			
			if($p_kategori=='S'){
				$p_kategori = "SPAREPART";
			}

			if($p_potype=='IMPORT'){
				$p_potype = "PO Barang Import";
			}
			else
			if($p_potype=='MKT'){
				$p_potype = "PO Barang MP";
			} 
			else
			if($p_potype=='GA'){
				$p_potype = "PO Barang Umum";
			} 
			else
			{
				$p_potype = "PO Barang Lokal";
			} 

			$nama_laporan = 'Laporan PO Per Nomor Per Supplier'; 
			$html = '';

			$html .='
				<style>
				.table-bordered {border-collapse:collapse}
				.table-bordered th, .table-bordered td{border-left:0.5px solid #555;border-bottom:0.5px solid #555;padding:2px}
				.border{border-top:0.5px solid #555;border-right:0.5px solid #555;}
				.bold{font-weight:bold;}
				.right{text-align:right;}
				.center{text-align:center;}
				.bigXL{font-size:180%;}
				.big{font-size:150%;}
				.italic{font-style:italic;}
				.w100{width:100%;}
				.mb-10{margin-bottom:10px}
				td{vertical-align:top}
				.group{page-break-inside:avoid;}
				</style>
			'; 

			$i = 0;  
 

			$html .='<div  class="group">';
  
				

			$nama_supp = '';
			$subTotal = 0;
			$totalPO =0;

			foreach($result['detail'] as $data){
				if ($nama_supp<>$data['Nm_Supplier'])
				{
					if ($nama_supp <> '')
					{
						$html .= '<tr> 
								<td class="left" colspan= "9"> 
									<b> Total No PO : '.$totalPO.'</b>
								</td>  
								<td class="center"> 
									<b> SUB TOTAL </b>
								</td>
								<td class="right"> 
									<b> '.number_format($subTotal,0).' </b>
								</td> 
							</tr>';
						$html .= '</table>';
					}  

					$html .='<div  style="padding-top: 10px" class="group">';
					$html .= '<table > <tr>  <td> Nama Supplier </td> <td> : </td> <td> '.$data['Nm_Supplier'].' </td> </tr>  </table>';  
					$html .='</div >';
					$html .='
					<table class="table-bordered w100 border">  
					'; 

				 	$html .= '<tr> 
								<td class="center" width="80px"> 
								<b> No PO </b>
								</td>
								<td class="center" width="80px"> 
									<b> Tgl PO </b>
								</td>
								<td class="center" width="80px"> 
									<b> Divisi </b>
								</td>
								<td class="center" width="80px"> 
									<b> Kd Lokasi </b>
								</td> 
								<td class="center" width="80px"> 
									<b> Kode Barang </b>
								</td>
								<td class="center" width="80px"> 
									<b> Qty </b>
								</td>
								<td class="center" width="80px"> 
									<b> Harga </b>
								</td>
								<td class="center" width="80px"> 
									<b> Disc </b>
								</td>
								<td class="center" width="80px"> 
									<b> DPP </b>
								</td>
								<td class="center" width="80px"> 
									<b> PPN </b>
								</td>
								<td class="center" width="80px"> 
									<b> Total </b>
								</td> 
								</tr>';

					$subTotal = 0;
					$totalPO =0;
				}
				$html .= '
					<tr> 
					<td>  
						'.$data['NoPO'].' 
					</td> 
					<td>  
						'.date("d-M-Y",strtotime($data['TglPO'])).' 
					</td> 
					<td>  
						'.$data['Divisi'].' 
					</td> 
					<td class="center"> 
						'.$data['Kd_Lokasi'].' 
					</td>  
					<td class="center"> 
						'.$data['Kd_Brg'].' 
					</td> 
					<td class="right">  
						'.number_format($data['Qty'],0).' 
					</td> 
					<td class="right">  
						'.number_format($data['Harga'],2).' 
					</td> 
					<td class="right">  
						'.number_format($data['Disc'],20).' 
					</td > 
					<td class="right">  
						'.number_format($data['DPP'],2).' 
					</td > 
					<td class="right">  
						'.number_format($data['PPN'],2).' 
					</td > 
					<td class="right">  
						'.number_format($data['Total'],2).' 
					</td> 
					</tr>
					'; 
					
					$subTotal += $data['Total'] ;
					$totalPO++;

				 	$nama_supp = $data['Nm_Supplier'];
			}  
			$html .= '<tr> 
							<td class="left" colspan= "9"> 
								<b> Total No PO : '.$totalPO.'</b>
							</td>  
							<td class="center"> 
								<b> SUB TOTAL </b>
							</td>
							<td class="right"> 
								<b> '.number_format($subTotal,0).' </b>
							</td> 
						</tr>';
			$html .=' </table>'; 

			$html .='</div>';
 
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			// echo $html;die;
			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'margin_left' => 10,
				'margin_right' => 10,
				'margin_top' => 45,
				'margin_bottom' => 20,
				'margin_header' => 10,
				'margin_footer' => 10,
				'orientation' => 'L'
			)); 
			$mpdf->SetHTMLHeader('
			<table width="100%">
				<tr>
					<td>
						'.date('d-M-Y H:i:s').'
					</td>
					<td class="right">
						Halaman {PAGENO} / {nbpg}
					</td>
				</tr>
			</table>
			<div class="big bold center">'.$nama_laporan.'</div>
				<div class="center mb-10">Periode '.date('d-M-Y',strtotime($p_tgl1)).' s/d '.date('d-M-Y',strtotime($p_tgl2)).'</div> 
				<table> 
					<tr> 
					<td> 
						Nama Supplier
					</td>
					<td> 
						:
					</td>
					<td> 
						'.$p_supplier_nama.'
					</td>
					</tr>
					<tr> 
					<td> 
						Kategori
					</td>
					<td> 
						:
					</td>
					<td> 
						'.$p_kategori.'
					</td>
					</tr>
					<tr> 
					<td> 
						PO type
					</td>
					<td> 
						:
					</td>
					<td> 
						'.$p_potype.'
					</td>
					</tr>
					</table>');
			$mpdf->WriteHTML($html);
			$mpdf->Output();
		}		

		public function Export_Pdf_B03($page_title, $p_tgl1, $p_tgl2, $p_supplier, $p_kategori, $p_supplier_nama, $p_potype, $p_branch, $params)
		{ 
			//ini_set('max_execution_time', '300');
			ini_set("pcre.backtrack_limit", "5000000");
			$api = 'APITES'; 

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->API_URL."/ReportPO/ProsesB01?api=".$api."&p_tgl1=".urlencode($p_tgl1)."&p_tgl2=".urlencode($p_tgl2)."&p_supplier=".urlencode($p_supplier)."&p_kategori=".urlencode($p_kategori)."&p_potype=".urlencode($p_potype)."&p_kode=B03"."&p_branch=".urlencode($p_branch),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => $this->maxtimeout,
				// CURLOPT_IGNORE_CONTENT_LENGTH => 1,
			));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $result;die;
			$result = json_decode($result,true);

			if(count($result['detail'])==0){
				$params['Remarks']="FAILED - Tidak Ada Data";
			  $params['RemarksDate'] = date("Y-m-d H:i:s");
			  $this->ActivityLogModel->update_activity($params);
				echo "Tidak ada data";
				die;
			}

			if($p_kategori=='P'){
				$p_kategori = "PRODUK";
			}
			
			if($p_kategori=='S'){
				$p_kategori = "SPAREPART";
			}

			if($p_potype=='IMPORT'){
				$p_potype = "PO Barang Import";
			}
			else
			if($p_potype=='MKT'){
				$p_potype = "PO Barang MP";
			} 
			else
			if($p_potype=='GA'){
				$p_potype = "PO Barang Umum";
			} 
			else
			{
				$p_potype = "PO Barang Lokal";
			} 

			$nama_laporan = 'Laporan PO Per Nomor Per Supplier dan Cabang'; 
			$html = '';

			$html .='
				<style>
				.table-bordered {border-collapse:collapse}
				.table-bordered th, .table-bordered td{border-left:0.5px solid #555;border-bottom:0.5px solid #555;padding:2px}
				.border{border-top:0.5px solid #555;border-right:0.5px solid #555;}
				.bold{font-weight:bold;}
				.right{text-align:right;}
				.center{text-align:center;}
				.bigXL{font-size:180%;}
				.big{font-size:150%;}
				.italic{font-style:italic;}
				.w100{width:100%;}
				.mb-10{margin-bottom:10px}
				td{vertical-align:top}
				.group{page-break-inside:avoid;}
				</style>
			'; 

			$i = 0; 
  


			$html .='<div   class="group">';
  
				

			$nama_supp = '';
			$subTotal = 0;
			$totalPO =0;

			foreach($result['detail'] as $data){
				if ($nama_supp<>$data['Kd_Lokasi'])
				{
					if ($nama_supp <> '')
					{
						$html .= '<tr> 
							<td class="left" colspan= "9"> 
								<b> Total No PO : '.$totalPO.'</b>
							</td>  
							<td class="center"> 
								<b> SUB TOTAL </b>
							</td>
							<td class="right"> 
								<b> '.number_format($subTotal,0).' </b>
							</td> 
						</tr>';
						$html .= '</table>';
					} 
					$html .='<div  style="padding-top: 10px" class="group">';
					$html .= '<table> <tr>  <td> Kode Lokasi </td> <td> : </td> <td> '.$data['Kd_Lokasi'].' </td> </tr>  </table>'; 
					$html .='</div>';

					$html .='
					<table class="table-bordered w100 border">  
					'; 

				 	$html .= '<tr> 
								<td class="center" width="80px"> 
								<b> No PO </b>
								</td>
								<td class="center" width="80px"> 
									<b> Tgl PO </b>
								</td>
								<td class="center" width="80px"> 
									<b> Divisi </b>
								</td>
								<td class="center" width="80px"> 
									<b> Nama Supplier </b>
								</td> 
								<td class="center" width="80px"> 
									<b> Kode Barang </b>
								</td>
								<td class="center" width="80px"> 
									<b> Qty </b>
								</td>
								<td class="center" width="80px"> 
									<b> Harga </b>
								</td>
								<td class="center" width="80px"> 
									<b> Disc </b>
								</td>
								<td class="center" width="80px"> 
									<b> DPP </b>
								</td>
								<td class="center" width="80px"> 
									<b> PPN </b>
								</td>
								<td class="center" width="80px"> 
									<b> Total </b>
								</td> 
								</tr>';

					$subTotal = 0;
					$totalPO =0;
				}
				$html .= '
					<tr> 
					<td>  
						'.$data['NoPO'].' 
					</td> 
					<td>  
						'.date("d-M-Y",strtotime($data['TglPO'])).' 
					</td> 
					<td>  
						'.$data['Divisi'].' 
					</td> 
					<td class="center"> 
						'.$data['Nm_Supplier'].' 
					</td>  
					<td class="center"> 
						'.$data['Kd_Brg'].' 
					</td> 
					<td class="right">  
						'.number_format($data['Qty'],0).' 
					</td> 
					<td class="right">  
						'.number_format($data['Harga'],2).' 
					</td> 
					<td class="right">  
						'.number_format($data['Disc'],2).' 
					</td > 
					<td class="right">  
						'.number_format($data['DPP'],2).' 
					</td > 
					<td class="right">  
						'.number_format($data['PPN'],2).' 
					</td > 
					<td class="right">  
						'.number_format($data['Total'],2).' 
					</td> 
					</tr>
					'; 
					
					$subTotal += $data['Total'] ;
					$totalPO++;

			 	$nama_supp = $data['Kd_Lokasi'];
			 }  
			 $html .= '<tr> 
							<td class="left" colspan= "9"> 
								<b> Total No PO : '.$totalPO.'</b>
							</td>  
							<td class="center"> 
								<b> SUB TOTAL </b>
							</td>
							<td class="right"> 
								<b> '.number_format($subTotal,0).' </b>
							</td> 
						</tr>';
			 $html .=' </table>'; 

			$html .='</div>';
 
			// echo $html;die;
 
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'margin_left' => 10,
				'margin_right' => 10,
				'margin_top' => 45,
				'margin_bottom' => 20,
				'margin_header' => 10,
				'margin_footer' => 10,
				'orientation' => 'L'
			)); 
			$mpdf->SetHTMLHeader('
			<table width="100%">
				<tr>
					<td>
						'.date('d-M-Y H:i:s').'
					</td>
					<td class="right">
						Halaman {PAGENO} / {nbpg}
					</td>
				</tr>
			</table>
			<div class="big bold center">'.$nama_laporan.'</div>
				<div class="center mb-10">Periode '.date('d-M-Y',strtotime($p_tgl1)).' s/d '.date('d-M-Y',strtotime($p_tgl2)).'</div> 
				<table> 
					<tr> 
					<td> 
						Nama Supplier
					</td>
					<td> 
						:
					</td>
					<td> 
						'.$p_supplier_nama.'
					</td>
					</tr>
					<tr> 
					<td> 
						Kategori
					</td>
					<td> 
						:
					</td>
					<td> 
						'.$p_kategori.'
					</td>
					</tr>
					<tr> 
					<td> 
						PO type
					</td>
					<td> 
						:
					</td>
					<td> 
						'.$p_potype.'
					</td>
					</tr>
					</table>');
			$mpdf->WriteHTML($html);
			$mpdf->Output();
		}

		public function Export_Pdf_D01($page_title, $p_tgl1, $p_tgl2, $p_supplier, $p_kategori, $p_wilayah, $p_gudang, $p_divisi, $p_branch, $params)
		{ 
			//ini_set('max_execution_time', '300');
			ini_set("pcre.backtrack_limit", "5000000");
			$api = 'APITES';


			$url = $_SESSION["conn"]->AlamatWebService;

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url.$this->API_BKT."/ReportPO/ProsesD01?api=".$api."&p_tgl1=".urlencode($p_tgl1)."&p_tgl2=".urlencode($p_tgl2)."&p_supplier=".urlencode($p_supplier)."&p_kategori=".urlencode($p_kategori)."&p_kota=".urlencode($p_wilayah)."&p_gudang=".urlencode($p_gudang)."&p_divisi=".urlencode($p_divisi)."&p_branch=".urlencode($p_branch),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => $this->maxtimeout,
				// CURLOPT_IGNORE_CONTENT_LENGTH => 1,
			));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $result;die;
			$result = json_decode($result,true);
			
			if(count($result['detail'])==0){
				$params['Remarks']="FAILED - Tidak Ada Data";
			   	$params['RemarksDate'] = date("Y-m-d H:i:s");
			   	$this->ActivityLogModel->update_activity($params);
				echo "Tidak ada data";
				die;
			}

			$nama_laporan = 'LAPORAN ORDER PEMBELIAN DETAIL PER PO'; 
			$html = '';

			$html .='
				<style>
				.table-bordered {border-collapse:collapse}
				.table-bordered th, .table-bordered td{border-left:0.5px solid #555;border-bottom:0.5px solid #555;padding:2px}
				.border{border-top:0.5px solid #555;border-right:0.5px solid #555;}
				.bold{font-weight:bold;}
				.right{text-align:right;}
				.center{text-align:center;}
				.bigXL{font-size:180%;}
				.big{font-size:150%;}
				.italic{font-style:italic;}
				.w100{width:100%;}
				.mb-10{margin-bottom:10px}
				td{vertical-align:top}
				.group{page-break-inside:avoid;}
				</style>
			'; 

			$nm_sup = "empty";
			$nm_wil = "empty";
			$kd_gud = "empty";
			$nm_gud = "empty";
			$i = 0;
			foreach($result['detail'] as $data){
				$html .='<div class="group">';

				if ($nm_sup<>$data['Nm_Supl'])
				{

					$html .='<div style="padding: 10px" class="center mb-10"><b>Nama Supplier '.$data['Nm_Supl'].'</b></div> 
						';  
				}					
				if ($nm_wil<>$data['Kota'])
				{
					$html .='
					<table>
					<tr><td>Wilayah </td>		<td>: <b>'.$data['Kota'].'</b></td></tr>
					</table>
					'; 
				}
				if ($kd_gud<>$data['KD_GUDANG'])
				{
					$html .='
					<table>
					<tr> <td> <b>'.$data['KD_GUDANG'].' - '.$data['Nm_Gudang'].'</b></td></tr>
					</table>
					'; 
				}
				$nm_sup = $data['Nm_Supl'];
				$nm_wil = $data['Kota'];
				$kd_gud = $data['KD_GUDANG'];
				$nm_gud = $data['Nm_Gudang'];

				$html .='
					<table class="table-bordered w100 border">  <tr> 
					'; 
 
				//Detail PO

				$totalQtyPo = 0;
				$tglPo = "";
				$html .='<td style="border: 1;"> <div class="group"> <table >';
				foreach($data['detailpo'] as $detailpo){ 
					if ($tglPo<>$detailpo['Tgl_PO'])
					{
						$html .=' 
						<tr> <td   style="border: none;"> <b>'.$detailpo['No_PO'].'</b></td> <td class="right"  style="border: none;"> <p style="color:red">('.$detailpo['Status'].')</p></td></tr> 
						'; 
						$html .=' 
						<tr> <td colspan=2   style="border: none;"> '.date('d-M-Y',strtotime($detailpo['Tgl_PO'])).' </td></tr> 
						';  
					}
					$tglPo = $detailpo['Tgl_PO'];

					$html .=' 
					<tr> <td width="150px"   style="border: none;"> '.$detailpo['Kd_Brg'].' </td>  <td    style="border: none;" width="80px" class="right"> '.number_format($detailpo['Qty']).' </td> </tr> 
					';  
					$totalQtyPo += number_format($detailpo['Qty']);
				}
				$html .=' 
					<tr> <td  style="border: none;" width="150px"> <b>Total Order </b></td>  <td   style="border: none;" width="80px" class="right"> <b> '.$totalQtyPo.'  </b></td> </tr> 
					'; 
				$html .='</table> </div> </td> ';

				//Detail DO
				$html .=' <td style="border: 1;"> <div class="group"> <table >'; 

				$totalQtyDo = 0;
				$No_DO = "";
				foreach($data['detaildo'] as $detaildo){ 

					if ($No_DO<>$detaildo['No_DO'])
					{
						$html .=' 
						<tr> <td   style="border: none;"> <b>'.$detaildo['No_DO'].'</b></td> </tr> 
						'; 
						$html .=' 
						<tr> <td colspan=2   style="border: none;"> '.date('d-M-Y',strtotime($detaildo['Tgl'])).' </td></tr> 
						';  
					}
					$No_DO = $detaildo['No_DO'];

					$html .=' 
					<tr> <td width="150px"   style="border: none;"> '.$detaildo['Kd_Brg'].' </td>  <td    style="border: none;" width="80px" class="right"> '.number_format($detaildo['Qty']).' </td> </tr> 
					';  
					$totalQtyDo += number_format($detaildo['Qty']);
				}
				$html .=' 
					<tr> <td  style="border: none;" width="150px"> <b>Total Kirim </b></td>  <td   style="border: none;" width="80px" class="right"> <b> '.$totalQtyDo.'  </b></td> </tr> 
					'; 
				$html .='</table> </div> </td> ';
  
				//Detail PU
				$html .=' <td style="border: 1;"> <div class="group"> <table >'; 

				$totalQtyPu = 0;
				$No_PU = "";

				foreach($data['detailpu'] as $detailpu){ 

					if ($No_PU<>$detailpu['No_PU'])
					{
						$html .=' 
						<tr> <td   style="border: none;"> <b>'.$detailpu['No_PU'].'</b></td> </tr> 
						'; 
						$html .=' 
						<tr> <td colspan=2   style="border: none;"> '.date('d-M-Y',strtotime($detailpu['Tgl_PU'])).' </td></tr> 
						';  
					}
					$No_PU = $detailpu['No_PU'];

					$html .=' 
					<tr> <td width="150px"   style="border: none;"> '.$detailpu['Kd_Brg'].' </td>  <td    style="border: none;" width="80px" class="right"> '.number_format($detailpu['Qty']).' </td> </tr> 
					';  
					$totalQtyPu += number_format($detailpu['Qty']);
				}
				$html .=' 
					<tr> <td  style="border: none;" width="150px"> <b>Total Terima </b></td>  <td   style="border: none;" width="80px" class="right"> <b> '.$totalQtyPu.'  </b></td> </tr> 
					'; 
				$html .='</table> </div> </td> ';

				$html .='</tr></table> '; 
 
				$html .='</div>';

				$i++;
			}

			// echo $html;die;

			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'margin_left' => 10,
				'margin_right' => 10,
				'margin_top' => 25.4,
				'margin_bottom' => 20,
				'margin_header' => 10,
				'margin_footer' => 10,
				'orientation' => 'P'
			)); 
			$mpdf->SetHTMLHeader('
			<table width="100%">
				<tr>
					<td>
						'.date('d-M-Y H:i:s').'
					</td>
					<td class="right">
						Halaman {PAGENO} / {nbpg}
					</td>
				</tr>
			</table>
			<div class="big bold center">'.$nama_laporan.'</div>
			<div class="center mb-10">Periode '.date('d-M-Y',strtotime($p_tgl1)).' s/d '.date('d-M-Y',strtotime($p_tgl2)).'</div>
			');
			$mpdf->WriteHTML($html);
			$mpdf->Output();
		}
 
		public function Export_Pdf_D02($page_title, $p_tgl1, $p_tgl2, $p_supplier, $p_kategori, $p_wilayah, $p_gudang, $p_divisi, $p_branch, $params)
		{ 
			//ini_set('max_execution_time', '300');
			ini_set("pcre.backtrack_limit", "5000000");
			$api = 'APITES';


			$url = $_SESSION["conn"]->AlamatWebService;

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url.$this->API_BKT."/ReportPO/ProsesD02?api=".$api."&p_tgl1=".urlencode($p_tgl1)."&p_tgl2=".urlencode($p_tgl2)."&p_supplier=".urlencode($p_supplier)."&p_kategori=".urlencode($p_kategori)."&p_kota=".urlencode($p_wilayah)."&p_gudang=".urlencode($p_gudang)."&p_divisi=".urlencode($p_divisi)."&p_branch=".urlencode($p_branch),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => $this->maxtimeout,
				// CURLOPT_IGNORE_CONTENT_LENGTH => 1,
			));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $result;die;
			$result = json_decode($result,true);
			
			if (count($result['detail'])==0) 
			{
				$params['Remarks']="FAILED - Tidak Ada Data";
			  $params['RemarksDate'] = date("Y-m-d H:i:s");
			  $this->ActivityLogModel->update_activity($params);
				echo "Tidak ada data";
				die;
			}

			$nama_laporan = 'Laporan Selisih DO dan BPB'; 
			$html = '';

			$html .='
				<style>
				.table-bordered {border-collapse:collapse}
				.table-bordered th, .table-bordered td{border-left:0.5px solid #555;border-bottom:0.5px solid #555;padding:2px}
				.border{border-top:0.5px solid #555;border-right:0.5px solid #555;}
				.bold{font-weight:bold;}
				.right{text-align:right;}
				.center{text-align:center;}
				.bigXL{font-size:180%;}
				.big{font-size:150%;}
				.italic{font-style:italic;}
				.w100{width:100%;}
				.mb-10{margin-bottom:10px}
				td{vertical-align:top}
				.group{page-break-inside:avoid;}
				</style>
			'; 

			$kd_sup = "empty";
			$nm_sup = "empty";
			$kd_wil = "empty";
			$nm_wil = "empty";
			$kd_gud = "empty"; 
			$nm_gud = "empty"; 
			$i = 0;

			$totalDO_gudang = 0;
			$totalPU_gudang = 0;
			$totalSelisih_gudang = 0;

			$totalDO_wilayah = 0;
			$totalPU_wilayah = 0;
			$totalSelisih_wilayah = 0;

			$totalDO_supplier = 0;
			$totalPU_supplier = 0;
			$totalSelisih_supplier = 0;

			foreach($result['detail'] as $data){
				//$html .='<div class="group">';

				if ($nm_sup<>$data['Nm_Supl'])
				{
					
					if ($nm_sup <> "empty" )
					{ 
						$html .=' <tr> ' ; 
						$html .=' <td class="right"><b> TOTAL '.$kd_gud.' - '.$nm_gud.' </b></td> ' ; 
						$html .=' <td class="right"><b>'.$totalDO_gudang.'</b></td> ' ; 
						$html .=' <td class="right"><b>'.$totalPU_gudang.'</b></td> ' ; 
						$html .=' <td class="right"><b>'.$totalSelisih_gudang.'</b></td> ' ; 
						$html .=' </tr> ' ;  

						$html .=' <tr> ' ; 
						$html .=' <td class="right"><b> TOTAL '.$kd_wil.' - '.$nm_wil.' </b></td> ' ; 
						$html .=' <td class="right"><b>'.$totalDO_wilayah.'</b></td> ' ; 
						$html .=' <td class="right"><b>'.$totalPU_wilayah.'</b></td> ' ; 
						$html .=' <td class="right"><b>'.$totalSelisih_wilayah.'</b></td> ' ; 
						$html .=' </tr> ' ;  

						$html .=' <tr> ' ; 
						$html .=' <td class="right"><b> TOTAL '.$kd_sup.' - '.$nm_sup.' </b></td> ' ; 
						$html .=' <td class="right"><b>'.$totalDO_supplier.'</b></td> ' ; 
						$html .=' <td class="right"><b>'.$totalPU_supplier.'</b></td> ' ; 
						$html .=' <td class="right"><b>'.$totalSelisih_supplier.'</b></td> ' ; 
						$html .=' </tr> ' ;  
						$html .=' </table> ' ;  
						$nm_wil = "empty";
						$kd_gud = "empty"; 

						$totalDO_gudang = 0;
						$totalPU_gudang = 0;
						$totalSelisih_gudang = 0;

						$totalDO_wilayah = 0;
						$totalPU_wilayah = 0;
						$totalSelisih_wilayah = 0;

						$totalDO_supplier = 0;
						$totalPU_supplier = 0;
						$totalSelisih_supplier = 0;
					}

					$html .='<div style="padding: 10px" class="center mb-10"><b>Nama Supplier '.$data['Nm_Supl'].'</b></div> ';  
				}					
				if ($nm_wil<>$data['Wilayah'])
				{
					if ($nm_wil <> "empty" )
					{ 					
						$html .=' <tr> ' ; 
						$html .=' <td class="right"><b> TOTAL '.$kd_gud.' - '.$nm_gud.' </b></td> ' ; 
						$html .=' <td class="right"><b>'.$totalDO_gudang.'</b></td> ' ; 
						$html .=' <td class="right"><b>'.$totalPU_gudang.'</b></td> ' ; 
						$html .=' <td class="right"><b>'.$totalSelisih_gudang.'</b></td> ' ; 
						$html .=' </tr> ' ;  

						$html .=' <tr> ' ; 
						$html .=' <td class="right"><b> TOTAL '.$kd_wil.' - '.$nm_wil.' </b></td> ' ; 
						$html .=' <td class="right"><b>'.$totalDO_wilayah.'</b></td> ' ; 
						$html .=' <td class="right"><b>'.$totalPU_wilayah.'</b></td> ' ; 
						$html .=' <td class="right"><b>'.$totalSelisih_wilayah.'</b></td> ' ; 
						$html .=' </tr> ' ; 

						$html .=' </table> ' ;    
						$kd_gud = "empty"; 

						$totalDO_gudang = 0;
						$totalPU_gudang = 0;
						$totalSelisih_gudang = 0;

						$totalDO_wilayah = 0;
						$totalPU_wilayah = 0;
						$totalSelisih_wilayah = 0; 
					}
					$html .='
					<table>
					<tr><td>Wilayah </td> <td>: <b>'.$data['Kd_Wilayah'].' - '.$data['Wilayah'].'</b></td></tr>
					</table>
					'; 
				}
				if ($kd_gud<>$data['Kd_Gudang'])
				{
					if ($kd_gud <> "empty" )
					{  
						$html .=' <tr> ' ; 
						$html .=' <td class="right"><b> TOTAL '.$kd_gud.' - '.$nm_gud.' </b></td> ' ; 
						$html .=' <td class="right"><b>'.$totalDO_gudang.'</b></td> ' ; 
						$html .=' <td class="right"><b>'.$totalPU_gudang.'</b></td> ' ; 
						$html .=' <td class="right"><b>'.$totalSelisih_gudang.'</b></td> ' ; 
						$html .=' </tr> ' ; 
						$html .=' </table> ' ;  
 
						$totalDO_gudang = 0;
						$totalPU_gudang = 0;
						$totalSelisih_gudang = 0; 
					}

					$html .='
					<table>
					<tr> <td> <b>'.$data['Kd_Gudang'].' - '.$data['Nm_Gudang'].'</b></td></tr>
					</table>
					'; 

					$html .=' <table class="table-bordered w100 border"> ' ; 
					$html .=' <tr> ' ; 
					$html .=' <td class="center"><b> Kode Barang </b></td> ' ; 
					$html .=' <td class="center"><b> QTY DO  </b></td> ' ; 
					$html .=' <td class="center"><b> QTY PU  </b></td> ' ; 
					$html .=' <td class="center"><b> Selisih  </b></td> ' ; 
					$html .=' </tr> ' ;   
				}
				$kd_sup = $data['Kd_Supl'];
				$nm_sup = $data['Nm_Supl'];
				$kd_wil = $data['Kd_Wilayah'];
				$nm_wil = $data['Wilayah'];
				$kd_gud = $data['Kd_Gudang']; 
				$nm_gud = $data['Nm_Gudang']; 
 
 				$qtyDO = number_format($data['QtyDO']);
 				$qtyPU = number_format($data['QtyPU']);
 				$qtySelisih = number_format($data['QtyDO']-$data['QtyPU']);

				$html .='<tr> <td> '.$data['Kd_Brg'].' </td>' ;
				$html .='<td class="right"> '.number_format($data['QtyDO']).' </td>' ;
				$html .='<td class="right"> '.number_format($data['QtyPU']).' </td>' ;
				$html .='<td class="right"> '.$qtySelisih.' </td> </tr>' ; 
				  
				//$html .='</div>';
 
				$totalDO_gudang = $totalDO_gudang + $data['QtyDO'];
				$totalDO_wilayah = $totalDO_wilayah + $data['QtyDO'];
				$totalDO_supplier = $totalDO_supplier + $data['QtyDO'];

				$totalPU_gudang = $totalPU_gudang + $data['QtyPU'];
				$totalPU_wilayah = $totalPU_wilayah + $data['QtyPU'];
				$totalPU_supplier = $totalPU_supplier + $data['QtyPU'];


				$totalSelisih_gudang = $totalDO_gudang-$totalPU_gudang;
				$totalSelisih_wilayah = $totalDO_wilayah-$totalPU_wilayah;
				$totalSelisih_supplier = $totalDO_supplier-$totalPU_supplier;

				$i++;
			}
			$html .=' </table>' ;  
			// echo $html;die;

			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'margin_left' => 10,
				'margin_right' => 10,
				'margin_top' => 25.4,
				'margin_bottom' => 20,
				'margin_header' => 10,
				'margin_footer' => 10,
				'orientation' => 'P'
			)); 
			$mpdf->SetHTMLHeader('
			<table width="100%">
				<tr>
					<td>
						'.date('d-M-Y H:i:s').'
					</td>
					<td class="right">
						Halaman {PAGENO} / {nbpg}
					</td>
				</tr>
			</table>
			<div class="big bold center">'.$nama_laporan.'</div>
			<div class="center mb-10">Periode '.date('d-M-Y',strtotime($p_tgl1)).' s/d '.date('d-M-Y',strtotime($p_tgl2)).'</div>
			');
			$mpdf->WriteHTML($html);
			$mpdf->Output();
		}

		public function PreviewC01($laporan, $tgl1, $tgl2, $kategori, $params)
		{
			//ini_set('max_execution_time', '300');
			ini_set("pcre.backtrack_limit", "5000000");
			$api = 'APITES';
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->API_URL."/ReportPO/ProsesC01?api=".$api."&tgl1=".urlencode($tgl1)."&tgl2=".urlencode($tgl2)."&kategori=".urlencode($kategori),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60,
				// CURLOPT_IGNORE_CONTENT_LENGTH => 1,
			));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $result;die;
			$result = json_decode($result,true);
			
			if(count($result)==0){
				$params['Remarks']="FAILED - Tidak Ada Data";
			   	$params['RemarksDate'] = date("Y-m-d H:i:s");
			   	$this->ActivityLogModel->update_activity($params);
				echo "Tidak ada data";
				die;
			}
			
			$nama_laporan = 'LAPORAN PO PER BARANG';
			$html = '';
			$html .='
				<style>
				.table-bordered {border-collapse:collapse}
				.table-bordered th, .table-bordered td{border-left:0.5px solid #555;border-bottom:0.5px solid #555;padding:2px}
				.border{border-top:0.5px solid #555;border-right:0.5px solid #555;}
				.bold{font-weight:bold;}
				.right{text-align:right;}
				.center{text-align:center;}
				.bigXL{font-size:180%;}
				.big{font-size:150%;}
				.italic{font-style:italic;}
				.w100{width:100%;}
				.mb-10{margin-bottom:10px}
				td{vertical-align:top}
				</style>
			';
			
			$html .='<table class="table-bordered w100 border">';
			$total = 0;
			foreach($result as $r){	
				$html .='<tr>
						<td width="100px">'.$r['Divisi'].'</td>
						<td width="80px">'.$r['Merk'].'</td>
						<td>'.$r['Kd_Brg'].'</td>
						<td width="60px" class="right">'.number_format($r['Qty']).'</td>
						<td width="80px" class="right">'.number_format($r['Harga']).'</td>
						<td width="80px" class="right">'.number_format($r['Disc']).'</td>
						<td width="100px" class="right">'.number_format($r['Total']).'</td>
					</tr>';
				$total += ROUND($r['Total']);
			}	
			$html .='<tr>
						<td colspan="6" class="right bold">Total</td>
						<td width="100px" class="right bold">'.number_format($total).'</td>
					</tr>';	
					
			$html .='</table>';
			 
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'margin_left' => 10,
				'margin_right' => 10,
				'margin_top' => 31.5,
				'margin_bottom' => 20,
				'margin_header' => 10,
				'margin_footer' => 10,
				'orientation' => 'P'
			)); 
			$mpdf->defaultfooterline = 1;
			$mpdf->SetHTMLHeader('
				<table width="100%">
					<tr>
						<td>
							'.date('d-M-Y H:i:s').'
						</td>
						<td class="right">
							Halaman {PAGENO} / {nbpg}
						</td>
					</tr>
				</table>
				<div class="big bold center">'.$nama_laporan.'</div>
				<div class="center mb-10">Periode '.date('d-M-Y',strtotime($tgl1)).' s/d '.date('d-M-Y',strtotime($tgl2)).'</div>
				<table class="table-bordered w100 border">
				<tr>
					<th width="100px">Divisi</th>
					<th width="80px">Merk</th>
					<th>Kode Barang</th>
					<th width="60px" class="right">Qty</th>
					<th width="80px" class="right">Harga</th>
					<th width="80px" class="right">Disc</th>
					<th width="100px" class="right">Total</th>
				</tr>
				</table>		
			');
			$mpdf->SetFooter('');
			$mpdf->WriteHTML($html);
			$mpdf->Output(); 
		}
		
		public function PreviewC02($laporan, $tgl1, $tgl2, $kategori, $params)
		{
			//ini_set('max_execution_time', '300');
			ini_set("pcre.backtrack_limit", "5000000");
			$data = array();
			$api = 'APITES';
			set_time_limit(60);
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->API_URL."/ReportPO/ProsesC02?api=".$api."&tgl1=".urlencode($tgl1)."&tgl2=".urlencode($tgl2)."&kategori=".urlencode($kategori),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60,
				// CURLOPT_IGNORE_CONTENT_LENGTH => 1,
			));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $result;die;
			$result = json_decode($result,true);
			
			if(count($result)==0){
				$params['Remarks']="FAILED - Tidak Ada Data";
			   	$params['RemarksDate'] = date("Y-m-d H:i:s");
			   	$this->ActivityLogModel->update_activity($params);
				echo "Tidak ada data";
				die;
			}
			
			$nama_laporan = 'LAPORAN PO PER CABANG PER BARANG';
			$TglAwal = '2022-12-01';
			$TglAkhir = '2022-12-15';
			$html = '';
			
			$html .='
				<style>
				.table-bordered {border-collapse:collapse}
				.table-bordered th, .table-bordered td{border-left:0.5px solid #555;border-bottom:0.5px solid #555;padding:2px}
				.border{border-top:0.5px solid #555;border-right:0.5px solid #555;}
				.bold{font-weight:bold;}
				.right{text-align:right;}
				.center{text-align:center;}
				.bigXL{font-size:180%;}
				.big{font-size:150%;}
				.italic{font-style:italic;}
				.w100{width:100%;}
				.mb-10{margin-bottom:10px}
				td{vertical-align:top}
				</style>
			';
			
			$i = 0;
			foreach($result as $kd_lokasi => $data){
				$html .='<htmlpageheader name="header_'.$i.'">							
						<table width="100%">
							<tr>
								<td>
									'.date('d-M-Y H:i:s').'
								</td>
								<td class="right">
									Halaman {PAGENO} / {nbpg}
								</td>
							</tr>
						</table>
						<div class="big bold center">'.$nama_laporan.'</div>
						<div class="center mb-10">Periode '.date('d-M-Y',strtotime($tgl1)).' s/d '.date('d-M-Y',strtotime($tgl2)).'</div>
						Cabang : <b>'.$kd_lokasi.'</b>
						<table class="table-bordered w100 border">
						<tr>
							<th width="100px">Divisi</th>
							<th width="80px">Merk</th>
							<th>Kode Barang</th>
							<th width="60px" class="right">Qty</th>
							<th width="80px" class="right">Harga</th>
							<th width="80px" class="right">Disc</th>
							<th width="100px" class="right">Total</th>
						</tr>
						</table>
					</htmlpageheader>';
			}
			
			$html .='<sethtmlpageheader name="header_'.$i.'" value="on" show-this-page="1" />';
			
			$html .='<table class="table-bordered w100 border">';
			$total = 0;
			
			foreach($data as $r){
				$html .='<tr>
						<td width="100px">'.$r['Divisi'].'</td>
						<td width="80px">'.$r['Merk'].'</td>
						<td>'.$r['Kd_Brg'].'</td>
						<td width="60px" class="right">'.number_format($r['Qty']).'</td>
						<td width="80px" class="right">'.number_format($r['Harga']).'</td>
						<td width="80px" class="right">'.number_format($r['Disc']).'</td>
						<td width="100px" class="right">'.number_format($r['Total']).'</td>
					</tr>';
				$total += ROUND($r['Total']);
			}	

			$html .='<tr>
						<td colspan="6" class="right bold">Total</td>
						<td width="100px" class="right bold">'.number_format($total).'</td>
					</tr>';	
			$html .='</table>';
			
			$i++;
			if($i<count($result)){
				$html .= '<pagebreak />';
			}
			// echo $html;die;
			
		        
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'margin_left' => 10,
				'margin_right' => 10,
				'margin_top' => 35.3,
				'margin_bottom' => 20,
				'margin_header' => 10,
				'margin_footer' => 10,
				'orientation' => 'P'
			)); 
			$mpdf->WriteHTML($html);
			$mpdf->Output(); 
		}
				
		public function PreviewC03($laporan, $tgl1, $tgl2, $kategori, $params)
		{
			//ini_set('max_execution_time', '300');
			ini_set("pcre.backtrack_limit", "5000000");
			$data = array();
			$api = 'APITES';
			set_time_limit(60);
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->API_URL."/ReportPO/ProsesC03?api=".$api."&tgl1=".urlencode($tgl1)."&tgl2=".urlencode($tgl2)."&kategori=".urlencode($kategori),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60,
				// CURLOPT_IGNORE_CONTENT_LENGTH => 1,
			));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $result;die;
			$result = json_decode($result,true);
			
			if(count($result)==0){
				$params['Remarks']="FAILED - Tidak Ada Data";
			   	$params['RemarksDate'] = date("Y-m-d H:i:s");
			   	$this->ActivityLogModel->update_activity($params);
				echo "Tidak ada data";
				die;
			}
			
			$nama_laporan = 'LAPORAN PO PER BARANG PER CABANG';
			$TglAwal = '2022-12-01';
			$TglAkhir = '2022-12-15';
			$html = '';
			
			$html .='
				<style>
				.table-bordered {border-collapse:collapse}
				.table-bordered th, .table-bordered td{border-left:0.5px solid #555;border-bottom:0.5px solid #555;padding:2px}
				.border{border-top:0.5px solid #555;border-right:0.5px solid #555;}
				.bold{font-weight:bold;}
				.right{text-align:right;}
				.center{text-align:center;}
				.bigXL{font-size:180%;}
				.big{font-size:150%;}
				.italic{font-style:italic;}
				.w100{width:100%;}
				.mb-10{margin-bottom:10px}
				td{vertical-align:top}
				.group{page-break-inside:avoid;}
				</style>
			';
			
			$i = 0;
			foreach($result as $kd_brg => $data){
				$html .='<div class="group">';
				$html .='
						<table>
						<tr><td>Divisi</td>		<td>: <b>'.$data['Divisi'].'</b></td></tr>
						<tr><td>Merk</td>		<td>: <b>'.$data['Merk'].'</b></td></tr>
						<tr><td>Kode Barang</td><td>: <b>'.$data['Kd_Brg'].'</b></td></tr>
						</table>
						';
				$html .='<table class="table-bordered w100 border mb-10">';
				
				$html .='<tr>
							<th>Kode Barang</th>
							<th width="80px">Kode Lokasi</th>
							<th width="60px" class="right">Qty</th>
							<th width="80px" class="right">Harga</th>
							<th width="80px" class="right">Disc</th>
							<th width="100px" class="right">Total</th>
						</tr>';
				$total = 0;
				foreach($data['Details'] as $r){
				$html .='<tr>
							<td>'.$r['Kd_Brg'].'</td>
							<td width="100px" class="center">'.$r['Kd_Lokasi'].'</td>
							<td width="60px" class="right">'.number_format($r['Qty']).'</td>
							<td width="80px" class="right">'.number_format($r['Harga']).'</td>
							<td width="80px" class="right">'.number_format($r['Disc']).'</td>
							<td width="100px" class="right">'.number_format($r['Total']).'</td>
						</tr>';
						$total += ROUND($r['Total']);
				}	
				$html .='<tr>
							<td colspan="5" class="right bold">Total</td>
							<td width="100px" class="right bold">'.number_format($total).'</td>
						</tr>';	
				$html .='</table>';
				$html .='</div>';
				
				$i++;
			}
			
			// echo $html;die;
			
	        
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'margin_left' => 10,
				'margin_right' => 10,
				'margin_top' => 25.4,
				'margin_bottom' => 20,
				'margin_header' => 10,
				'margin_footer' => 10,
				'orientation' => 'P'
			)); 
			$mpdf->SetHTMLHeader('
			<table width="100%">
				<tr>
					<td>
						'.date('d-M-Y H:i:s').'
					</td>
					<td class="right">
						Halaman {PAGENO} / {nbpg}
					</td>
				</tr>
			</table>
			<div class="big bold center">'.$nama_laporan.'</div>
			<div class="center mb-10">Periode '.date('d-M-Y',strtotime($tgl1)).' s/d '.date('d-M-Y',strtotime($tgl2)).'</div>
			');
			$mpdf->WriteHTML($html);
			$mpdf->Output();
		}
		 
		public function Export_Pdf_D03($page_title, $p_tgl1, $p_tgl2, $p_supplier, $p_kategori, $p_wilayah, $p_gudang, $p_divisi, $p_branch, $params)
		{ 
			//ini_set('max_execution_time', '300');
			ini_set("pcre.backtrack_limit", "5000000");
			$api = 'APITES';


			$url = $_SESSION["conn"]->AlamatWebService;

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url.$this->API_BKT."/ReportPO/ProsesD03?api=".$api."&p_tgl1=".urlencode($p_tgl1)."&p_tgl2=".urlencode($p_tgl2)."&p_supplier=".urlencode($p_supplier)."&p_kategori=".urlencode($p_kategori)."&p_kota=".urlencode($p_wilayah)."&p_gudang=".urlencode($p_gudang)."&p_divisi=".urlencode($p_divisi)."&p_branch=".urlencode($p_branch),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => $this->maxtimeout,
				// CURLOPT_IGNORE_CONTENT_LENGTH => 1,
			));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $result;die;
			$result = json_decode($result,true);

      if(count($result['detail'])==0){
				$params['Remarks']="FAILED - Tidak Ada Data";
			  $params['RemarksDate'] = date("Y-m-d H:i:s");
			  $this->ActivityLogModel->update_activity($params);
				echo "Tidak ada data";
				die;
			}

			$nama_laporan = 'Laporan PO Aktif Gantung'; 
			$html = '';


			$html .='
				<style>
				.table-bordered {border-collapse:collapse}
				.table-bordered th, .table-bordered td{border-left:0.5px solid #555;border-bottom:0.5px solid #555;padding:2px}
				.border{border-top:0.5px solid #555;border-right:0.5px solid #555;}
				.bold{font-weight:bold;}
				.right{text-align:right;}
				.center{text-align:center;}
				.bigXL{font-size:180%;}
				.big{font-size:150%;}
				.italic{font-style:italic;}
				.w100{width:100%;}
				.mb-10{margin-bottom:10px}
				td{vertical-align:top}
				.group{page-break-inside:avoid;}
				</style>
			'; 

			$nm_sup = "empty";
			$nm_wil = "empty";
			$kd_gud = "empty";
			$nm_gud = "empty";
			$i = 0;
			foreach($result['detail'] as $data){
				$html .='<div class="group">';

				if ($nm_sup<>$data['Nm_Supl'])
				{

					$html .='<div style="padding: 10px" class="center mb-10"><b>Nama Supplier '.$data['Nm_Supl'].'</b></div> 
						';  
				}					
				if ($nm_wil<>$data['Kota'])
				{
					$html .='
					<table>
					<tr><td>Wilayah </td>		<td>: <b>'.$data['Kota'].'</b></td></tr>
					</table>
					'; 
				}
				if ($kd_gud<>$data['KD_GUDANG'])
				{
					$html .='
					<table>
					<tr> <td> <b>'.$data['KD_GUDANG'].' - '.$data['Nm_Gudang'].'</b></td></tr>
					</table>
					'; 
				}
				$nm_sup = $data['Nm_Supl'];
				$nm_wil = $data['Kota'];
				$kd_gud = $data['KD_GUDANG'];
				$nm_gud = $data['Nm_Gudang'];

				$html .='
					<table>
					<tr> <td   style="border: none;"> <b>'.$data['No_PO'].'</b></td> <td class="right"  style="border: none;"> <p style="color:red">('.$data['Status'].')</p></td> <td colspan=2   style="border: none;"> '.date('d-M-Y',strtotime($data['Tgl_PO'])).' </td></tr> 
					</table>
					';  

				$html .='
					<table class="table-bordered w100 border">  <tr> 
					'; 
 
				//Detail PO 
				$html .='<td style="border: 1;"> <div class="group"> <table >';

				$totalQtyPo = 0;
				$tglPo = "";

				foreach($data['detailpo'] as $detailpo){  
					$html .=' 
					<tr> <td width="150px"   style="border: none;"> '.$detailpo['Kd_Brg'].' </td>  <td    style="border: none;" width="80px" class="right"> '.number_format($detailpo['Qty']).' </td> </tr> 
					';  
					$totalQtyPo += number_format($detailpo['Qty']);
				}
				$html .=' 
					<tr> <td  style="border: none;" width="150px"> <b>Total Order </b></td>  <td   style="border: none;" width="80px" class="right"> <b> '.$totalQtyPo.'  </b></td> </tr> 
					'; 
				$html .='</table> </div> </td> ';

				//Detail DO
				$html .=' <td style="border: 1;"> <div class="group"> <table >'; 

				$totalQtyDo = 0;
				$No_DO = "";
 
				foreach($data['detaildo'] as $detaildo){   
					$html .=' 
					<tr> <td width="150px"   style="border: none;"> '.$detaildo['Kd_Brg'].' </td>  <td    style="border: none;" width="80px" class="right"> '.number_format($detaildo['Qty']).' </td> </tr> 
					';  
					$totalQtyDo += number_format($detaildo['Qty']);
				}
				$html .=' 
					<tr> <td  style="border: none;" width="150px"> <b>Total Kirim </b></td>  <td   style="border: none;" width="80px" class="right"> <b> '.$totalQtyDo.'  </b></td> </tr> 
					'; 
				$html .='</table> </div> </td> ';
  
				//Detail PO GANTUNG
				$html .=' <td style="border: 1;"> <div class="group"> <table >'; 

				$totalQtyPu = 0;
				$No_PU = "";

				foreach($data['detailpo'] as $detailpo){  
					$qtybarang = number_format($detailpo['Qty']);
					foreach($data['detaildo'] as $detaildo){ 
						if ($detailpo['Kd_Brg']==$detaildo['Kd_Brg'])
						{ 
							$qtybarang = $qtybarang - number_format($detaildo['Qty']);
						}

					} 
					$html .=' 
					<tr> <td width="150px"   style="border: none;"> '.$detailpo['Kd_Brg'].' </td>  <td    style="border: none;" width="80px" class="right"> '.$qtybarang.' </td> </tr> 
					';  
					$totalQtyPu += $qtybarang;
				}

				$html .=' 
					<tr> <td  style="border: none;" width="150px"> <b>Total PO Gantung </b></td>  <td   style="border: none;" width="80px" class="right"> <b> '.$totalQtyPu.'  </b></td> </tr> 
					'; 
				$html .='</table> </div> </td> ';

				$html .='</tr></table> '; 
 
				$html .='</div>';

				$i++;
			}

	        
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			// echo $html;die;
			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'margin_left' => 10,
				'margin_right' => 10,
				'margin_top' => 25.4,
				'margin_bottom' => 20,
				'margin_header' => 10,
				'margin_footer' => 10,
				'orientation' => 'P'
			)); 
			$mpdf->SetHTMLHeader('
			<table width="100%">
				<tr>
					<td>
						'.date('d-M-Y H:i:s').'
					</td>
					<td class="right">
						Halaman {PAGENO} / {nbpg}
					</td>
				</tr>
			</table>
			<div class="big bold center">'.$nama_laporan.'</div>
			<div class="center mb-10">Periode '.date('d-M-Y',strtotime($p_tgl1)).' s/d '.date('d-M-Y',strtotime($p_tgl2)).'</div>
			');
			$mpdf->WriteHTML($html);
			$mpdf->Output();
		} 
	}		
?>