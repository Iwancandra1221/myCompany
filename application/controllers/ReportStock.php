<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportStock extends MY_Controller 
{
	public $excel_flag = 0;
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('MsDatabaseModel'); 
		$this->load->model('HelperModel');
		$this->load->helper('FormLibrary');
		$this->load->library('email');
		$this->load->library('excel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
	}

	private function _postRequest($url,$data,$isJson = false){
		$ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_ENCODING , '');
	    curl_setopt($ch, CURLOPT_TIMEOUT, (60*3)); // Set timeout to 60 seconds for the whole operation
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60); // Set connection timeout to 60 seconds
	    if ($isJson) {
	        // Jika data adalah JSON, encode ke JSON dan atur header
	        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	    } else {
	        // Jika data adalah form data, atur payload dengan http_build_query
	        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	    }

	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	    $result = curl_exec($ch);

	    if (curl_errno($ch)) {
	        echo 'Curl error: ' . curl_error($ch);
	    }

	    curl_close($ch);

	    return $result;
	}
	public function Stock()
	{
		
		$api = 'APITES';
		
		$gudang = json_decode(file_get_contents($this->API_URL."/ReportStock/GetGudangList?kd_gudang=".urlencode('%')."&api=".urlencode($api)),true);
		// print_r($gudang);
		// die;
		$data["gudang"] = $gudang;
		
		if ($gudang["result"]=="sukses") {
			$data["gudang"] = $gudang["data"];
		}
		else {
			$data["gudang"] = array();
		}
		
		$cabang = json_decode(file_get_contents($this->API_URL."/ReportStock/GetCabangList?cabang=".urlencode('%')."&api=".urlencode($api)),true);
		// print_r($cabang);
		// print_r($this->API_URL."/ReportStock/GetCabangList?cabang=".urlencode('%')."&api=".urlencode($api));
		// die;
		$data["cabang"] = $cabang;
		
		if ($cabang["result"]=="sukses") {
			$data["cabang"] = $cabang["data"];
		}
		else {
			$data["cabang"] = array();
		}

		if ($_SESSION["logged_in"]["isUserPabrik"]==0) {
			$divisi = json_decode(file_get_contents($this->API_URL."/MsBarang/GetDivisiList?api=".urlencode($api)),true);
			$data["divisi"] = $divisi;
			
			if ($divisi["result"]=="sukses") {
				$data["divisi"] = $divisi["data"];
				} else {
				$data["divisi"] = array();
			}
		} else {
			$data["divisi"] = array();
		}
		
		$divisi = json_decode(file_get_contents($this->API_URL."/MsBarang/GetDivisiList?api=".urlencode($api)),true);
		$data["divisi"] = $divisi;
		
		if ($divisi["result"]=="sukses") {
			$data["divisi"] = $divisi["data"];
		} else {
			$data["divisi"] = array();
		}
		
		$data['title'] = 'MY COMPANY | STOCK | REPORT STOCK TOTAL';
		$data['reportOption'] = "STOCK TOTAL";
		$data['formDest'] = "ReportStock/ProsesStock";

		$paramsLog = array();   
	 	$paramsLog['LogDate'] = date("Y-m-d H:i:s");
	  	$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
	  	$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
	  	$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="REPORT STOCK"; 
	 	$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU REPORT STOCK";
	 	$paramsLog['Remarks']="SUCCESS";
	  	$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($paramsLog); 
		
		$this->RenderView('ReportStockForm',$data);
	}
	
	public function ProsesStock()
	{
		$data = array();
		$page_title = 'Report Stock';
		
		$group = $_POST['grouping'];
		$tanggal = $_POST['tanggal'];
		$kategori = $_POST['kategori'];
		$cabang = $_POST['cabang'];
		$gudang = $_POST['gudang'];
		$divisi = $_POST['divisi'];
		
		if(isset($_POST["btnPreview"])){
			$this->excel_flag = 0; 
			$paramsLog = array();   
			$paramsLog['LogDate'] = date("Y-m-d H:i:s");
			$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
			$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
			$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$paramsLog['Module']="REPORT STOCK"; 
			$paramsLog['TrxID'] = date("YmdHis");
			$paramsLog['Description']=$_SESSION["logged_in"]["username"]." PROSES PREVIEW REPORT STOCK";
			$paramsLog['Remarks']="";
			$paramsLog['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($paramsLog); 
		}
		else{
			$this->excel_flag = 1; 
			$paramsLog = array();   
			$paramsLog['LogDate'] = date("Y-m-d H:i:s");
			$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
			$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
			$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$paramsLog['Module']="REPORT STOCK"; 
			$paramsLog['TrxID'] = date("YmdHis");
			$paramsLog['Description']=$_SESSION["logged_in"]["username"]." PROSES EXPORT EXCEL REPORT STOCK";
			$paramsLog['Remarks']="";
			$paramsLog['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($paramsLog); 
		}
		
		if($group=='gudang'){
			$this->Proses_StockGudang($tanggal,$kategori,$gudang,$divisi,$paramsLog);
		}
		else{
			$this->Proses_StockCabang($tanggal,$kategori,$cabang,$divisi,$paramsLog);
		}
	}
	
	public function Proses_StockGudang($tanggal,$kategori,$gudang,$divisi,$paramsLog)
	{
		ini_set('memory_limit', '128M');

		$api = 'APITES';
		$streamContext = stream_context_create(
			array('http'=>
				array(
					'timeout' => 60,  //600 seconds = 10 menit
				)
			)
		);
		
		$kd_brg = '%'; //ambil semua kode barang
		
		$URL = $this->API_URL."/ReportStock/StockPerGudang?api=".urlencode($api)."&kategori=".urlencode($kategori)."&divisi=".urlencode($divisi)."&kd_brg=".urlencode($kd_brg)."&tgl_akhir=".urlencode($tanggal)."&kd_gudang=".urlencode($gudang);
		$json = $this->_postRequest($URL, array(),false);
		$json = json_decode($json);
		// die('Proses_StockGudang');
		// die($url);
		//$json = json_decode(file_get_contents($URL, false, $streamContext));
		// print_r($json);
		// die;
		
		if($json==null){
			$paramsLog['Remarks']="FAILED - Gagal Mengambil Data Report Stock Per Gudang ".$URL;
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
			exit('Tidak ada data');
		}
		

		// $gudanglist = json_decode(file_get_contents($this->API_URL."/ReportStock/GetGudangList?aktif=Y&kd_gudang=".urlencode($gudang)."&api=".urlencode($api)),true);
		$URL = $this->API_URL."/ReportStock/GetGudangList?aktif=Y&kd_gudang=".urlencode($gudang)."&api=".urlencode($api);
		$gudanglist = $this->_postRequest($URL, array(),false);
		$gudanglist = json_decode($gudanglist,true);

		if ($gudanglist["result"]=="sukses") {
			$kolom = $gudanglist["data"];
		}
		else{
			$kolom = array();
		}
		// print_r($kolom);
		// die;
		
		$warna_table_header = 'aaaaaa';
		$warna_divisi = 'd5d5d5';
		$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
		$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
		$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
		$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
		
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);
		
		$p_nama_laporan = 'Report Stock Per Gudang';
		$kategori = ($kategori=='P') ? 'PRODUK' : 'SPAREPART';
		$html = "<html>";
		$html .= "<head>";
		$html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
		
		$html .= "<style>
		body{font-family:'Calibri',Arial;}
		.table{padding:0;margin:0;border-collapse:collapse}
		.table td, .table th { border:1px solid #555; padding:2px!important; }
		.td-center { text-align: center; }
		.td-right { text-align:right;}
		.td-bold { font-weight:bold}
		</style>";
		
		$html.= "</head>";
		$html.= "<body>";
		$html.= "<div id='div_header' style='margin-bottom:20px;'>";
		$html.= "<div><h2>".$p_nama_laporan."</h2></div>";
		$html.= "<table>
		<tr><td>KATEGORI </td><td class='td-bold'>: ".$kategori." </td></tr>
		<tr><td>DIVISI </td><td class='td-bold'>: ".$divisi."  </td></tr>
		<tr><td>GUDANG </td><td class='td-bold'>: ".$gudang."  </td></tr>
		<tr><td>PERIODE </td><td class='td-bold'>: ".$tanggal."  </td></tr>
		</table>
		</div>";
		
		if($this->excel_flag == 1){
			$sheet->setTitle(substr($p_nama_laporan, 0, 31));
			$sheet->setCellValue('A1', $p_nama_laporan);
			$sheet->getStyle('A1')->getFont()->setSize(12);
			$sheet->setCellValue('A2', 'KATEGORI : '.$kategori);
			$sheet->setCellValue('A3', 'DIVISI : '.$divisi);
			$sheet->setCellValue('A4', 'GUDANG : '.$gudang);
			$sheet->setCellValue('A5', 'PERIODE : '.$tanggal);
		}
		
		$html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
		$html.= "<div style='clear:both'></div>";
		
		$html.= "<table class='table' style='font-size:10pt!important'>";
		$html.='<tr style="background:#'.$warna_table_header.'">';
		$html.='<th>No</th>';
		$html.='<th>KODE BARANG</th>';
		
		$grand_total = 0;
		$jumlah_all = array();
		for($i=0;$i<count($kolom);$i++){
			$html.='<th style="width:60px">'.$kolom[$i]['Kd_Gudang'].'</th>';
			$jumlah_all[$i] = 0;
		}
		$html.='<th style="width:60px">TOTAL</th>';
		$html.='</tr>';
		
		$currcol = 0;
		$currrow = 6;
		
		if($this->excel_flag == 1){
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO');
			$sheet->getColumnDimension('A')->setWidth(5);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KODE BARANG');
			$sheet->getColumnDimension('B')->setWidth(60);
			
			foreach($kolom as $k){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $k['Kd_Gudang']);
				$col_name = PHPExcel_Cell::stringFromColumnIndex($currcol-1);
				$sheet->getColumnDimension($col_name)->setWidth(10);
			}
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
			$col_name = PHPExcel_Cell::stringFromColumnIndex($currcol-1);
			$sheet->getColumnDimension($col_name)->setWidth(10);
		}
		$max_col = $currcol; // index kolom terakhir (paling kanan)
		$max_col_name = PHPExcel_Cell::stringFromColumnIndex($max_col-1);
		
		foreach($json as $divisi => $kd_brgs) {
			// $html.='<tr><td></td><td class="td-bold">'.$divisi.'</td>';
			// foreach($kolom as $k){
				// $html.='<td></td>';
			// }
			// $html.='<td></td>';
			// $html.='</tr>';
			
			$no = 0;
			$jumlah_divisi = array_fill(0, COUNT($kolom), 0);
			$total_divisi = 0;
			foreach($kd_brgs as $kd_brg => $details) {
				$no++;
				$total = 0;
				$html.='<tr>';
				$html.='<td>'.$no.'</td>';
				$html.='<td>'.$kd_brg.'</td>';					
				if($this->excel_flag == 1){
					$currcol = 0;
					$currrow++;
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $kd_brg);
				}
				
				for($i=0;$i<count($kolom);$i++){
					$stok = 0;
					
					foreach($details as $d){
						if($d->Kd_Gudang == $kolom[$i]['Kd_Gudang'] ){
							$stok = $d->Stock_Akhir;
						}
					}
					
					$total += $stok;
					$jumlah_divisi[$i] += $stok;
					$jumlah_all[$i] += $stok;
					$html.='<td class="td-right">'.number_format($stok,0).'</td>';
					if($this->excel_flag == 1){
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $stok);
					}
				}
				$html.='<td class="td-right">'.number_format($total,0).'</td>';
				$html.='</tr>';
				
				if($this->excel_flag == 1){
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total);
				}
				$total_divisi += $total;
			}
			
			$html.='<tr style="background:#'.$warna_divisi.'">';
			$html.='<td></td>';
			$html.='<td class="td-bold">Total '.$divisi.'</td>';
			for($i=0;$i<count($kolom);$i++){
				$html.='<td class="td-right td-bold">'.number_format($jumlah_divisi[$i]).'</td>';
			}
			$html.='<td class="td-right td-bold">'.number_format($total_divisi).'</td>';
			$html.='</tr>';
			
			if($this->excel_flag == 1){
				$currcol = 0;
				$currrow++;
				
				$currcol += 1;
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $divisi);				
				for($i=0;$i<count($kolom);$i++){
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jumlah_divisi[$i]);
					$col_name = PHPExcel_Cell::stringFromColumnIndex($currcol-1);
				}
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_divisi);
				
				$sheet->getStyle('A'.($currrow).':'.$max_col_name.($currrow))->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_divisi);
			}
			$grand_total += $total_divisi;
		}
		
		$html.='<tr style="background:#'.$warna_table_header.'">';
		$html.='<td></td>';
		$html.='<td class="td-bold">TOTAL</td>';
		for($i=0;$i<count($kolom);$i++){
			$html.='<td class="td-right td-bold">'.number_format($jumlah_all[$i]).'</td>';
		}
		$html.='<td class="td-right td-bold">'.number_format($grand_total).'</td>';
		$html.='</tr>';
		
		if($this->excel_flag == 1){
			$currcol = 0;
			$currrow++;
			
			$currcol += 1;
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');				
			for($i=0;$i<count($kolom);$i++){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jumlah_all[$i]);
				$col_name = PHPExcel_Cell::stringFromColumnIndex($currcol-1);
			}
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total);
			$sheet->getStyle('A'.($currrow).':'.$max_col_name.($currrow))->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
			$sheet->getStyle('C1:'.$max_col_name.($currrow))->getNumberFormat()->setFormatCode('#,##0');
		}
		
		$html.='</table>';
		$html.= "</div>";
		$html.= "</body></html>";
		
		if($this->excel_flag == 1){
			$sheet->getStyle('C6:'.$max_col_name.'6')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A6:'.$max_col_name.'6')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
			// $sheet->getStyle('A6:'.$max_col_name.'6')->getAlignment()->setWrapText(true); 
			
			$sheet->getStyle('B6:B'.$currrow)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );


			
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

			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
			exit();
		}
		
		$data['title'] = $p_nama_laporan;
		$data['content_html'] = $html;

		$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
		
		$this->load->view('LaporanResultView',$data);
	}
	
	public function Proses_StockCabang($tanggal,$kategori,$cabang,$divisi,$paramsLog)
	{
		ini_set('memory_limit', '128M');

		$api = 'APITES';
		$streamContext = stream_context_create(
		array('http'=>
		array(
		'timeout' => 60,  //600 seconds = 10 menit
		)
		)
		);
		
		$kd_brg = '%'; //ambil semua kode barang
		$supl = "x";

		/*Tambahan 23 April 2021: untuk User Pabrik bisa Buka Laporan Penjualan QTY */
		if ($_SESSION["logged_in"]["isUserPabrik"]==1) {
			if (strtoupper($_SESSION["logged_in"]["useremail"])=="USER@PABRIK.KG") {
				$supl = "JKTK001";
			} else if (strtoupper($_SESSION["logged_in"]["useremail"])=="USER@PABRIK.PTRI") {
				$supl = "JKTR001";
			} else if (strtoupper($_SESSION["logged_in"]["useremail"])=="USER@PABRIK.TIN") {
				$supl = "JKTT003";
			} else {
				$supl = "x";
			}
		}
		
		$URL = $this->API_URL."/ReportStock/StockPerCabang?api=".urlencode($api)."&kategori=".urlencode($kategori).
			"&divisi=".urlencode($divisi)."&kd_brg=".urlencode($kd_brg)."&tgl_akhir=".urlencode($tanggal)."&cabang=".urlencode($cabang).
			'&supl='.urlencode($supl);
		// die($URL);

		$json = $this->_postRequest($URL, array(),false);
		$json = json_decode($json);
		// echo '<pre>';
		// print_r($json);
		// echo '</pre>';
		// die('Proses_StockCabang');

		//$json = json_decode(file_get_contents($URL, false, $streamContext));
		// print_r($json);
		// die;
		
		if($json==null){ 
			$paramsLog['Remarks']="FAILED - Gagal Mengambil Data Report Stock Per Cabang ".$URL;
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
			exit('Tidak ada data');
		}
		
		$URL = $this->API_URL."/ReportStock/GetCabangList2?aktif=Y&cabang=".urlencode($cabang)."&api=".urlencode($api)."&kategori=".urlencode($kategori).
			"&divisi=".urlencode($divisi)."&kd_brg=".urlencode($kd_brg)."&tgl_akhir=".urlencode($tanggal).'&supl='.urlencode($supl);
		// die($URL);

		$cabanglist = $this->_postRequest($URL, array(),false);
		$cabanglist = json_decode($cabanglist,true);
		//$cabanglist = json_decode(file_get_contents($URL),true);
		// die(json_encode($cabanglist));

		if ($cabanglist["result"]=="sukses") {
			$kolom = $cabanglist["data"];
		}
		else{
			$kolom = array();
		}
		
		$warna_table_header = 'aaaaaa';
		$warna_divisi = 'd5d5d5';
		$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
		$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
		$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
		$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
		
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);
		
		$p_nama_laporan = 'Report Stock Per Cabang';
		$kategori = ($kategori=='P') ? 'PRODUK' : 'SPAREPART';
		$html = "<html>";
		$html .= "<head>";
		
		$html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
		$html .= "<style>
		body{font-family:'Calibri',Arial;}
		.table{padding:0;margin:0;border-collapse:collapse}
		.table td, .table th { border:1px solid #555; padding:2px!important; }
		.td-center { text-align: center; }
		.td-right { text-align:right;}
		.td-bold { font-weight:bold}
		</style>";
		
		$html.= "</head>";
		$html.= "<body>";
		$html.= "<div id='div_header' style='margin-bottom:20px;'>";
		$html.= "<div><h2>".$p_nama_laporan."</h2></div>";
		$html.= "<table>
		<tr><td>KATEGORI </td><td class='td-bold'>: ".$kategori." </td></tr>
		<tr><td>DIVISI </td><td class='td-bold'>: ".$divisi."  </td></tr>
		<tr><td>CABANG </td><td class='td-bold'>: ".$cabang."  </td></tr>
		<tr><td>Tanggal </td><td class='td-bold'>: ".$tanggal."  </td></tr>
		</table>
		</div>";
		
		if($this->excel_flag == 1){
			
			

			$sheet->setTitle(substr($p_nama_laporan, 0, 31));
			$sheet->setCellValue('A1', $p_nama_laporan);
			$sheet->getStyle('A1')->getFont()->setSize(12);
			$sheet->setCellValue('A2', 'KATEGORI : '.$kategori);
			$sheet->setCellValue('A3', 'DIVISI : '.$divisi);
			$sheet->setCellValue('A4', 'CABANG : '.$cabang);
			$sheet->setCellValue('A5', 'PERIODE : '.$tanggal);
		}
		
		$html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
		$html.= "<div style='clear:both'></div>";
		
		$html.= "<table class='table' style='font-size:10pt!important'>";
		$html.='<tr style="background:#'.$warna_table_header.'">';
		$html.='<th>No</th>';
		$html.='<th>KODE BARANG</th>';
		
		$grand_total = 0;
		$jumlah_all = array();
		for($i=0;$i<count($kolom);$i++){
			$html.='<th style="width:60px">'.$kolom[$i]['Nm_Lokasi'].'</th>';
			$jumlah_all[$i] = 0;
		}
		$html.='<th style="width:60px">TOTAL</th>';
		$html.='</tr>';
		
		$currcol = 0;
		$currrow = 6;
		
		if($this->excel_flag == 1){
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO');
			$sheet->getColumnDimension('A')->setWidth(5);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KODE BARANG');
			$sheet->getColumnDimension('B')->setWidth(60);
			
			foreach($kolom as $k){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $k['Nm_Lokasi']);
				$col_name = PHPExcel_Cell::stringFromColumnIndex($currcol-1);
				$sheet->getColumnDimension($col_name)->setWidth(10);
			}
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
			$col_name = PHPExcel_Cell::stringFromColumnIndex($currcol-1);
			$sheet->getColumnDimension($col_name)->setWidth(10);
		}
		$max_col = $currcol; // index kolom terakhir (paling kanan)
		$max_col_name = PHPExcel_Cell::stringFromColumnIndex($max_col-1);
		
		foreach($json as $divisi => $kd_brgs) {
			// $html.='<tr><td></td><td class="td-bold">'.$divisi.'</td>';
			// foreach($kolom as $k){
				// $html.='<td></td>';
			// }
			// $html.='<td></td>';
			// $html.='</tr>';
			
			$no = 0;
			$jumlah_divisi = array_fill(0, COUNT($kolom), 0);
			$total_divisi = 0;
			foreach($kd_brgs as $kd_brg => $details) {
				$no++;
				$total = 0;
				$html.='<tr>';
				$html.='<td>'.$no.'</td>';
				$html.='<td>'.$kd_brg.'</td>';					
				if($this->excel_flag == 1){
					$currcol = 0;
					$currrow++;
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $kd_brg);
				}
				
				for($i=0;$i<count($kolom);$i++){
					$stok = 0;
					foreach($details as $d){
						if($d->Kd_Lokasi == $kolom[$i]['Nm_Lokasi'] ){
							$stok = $d->Stock_Akhir;
						}
					}
					$total += $stok;
					$jumlah_divisi[$i] += $stok;
					$jumlah_all[$i] += $stok;
					$html.='<td class="td-right">'.number_format($stok,0).'</td>';
					if($this->excel_flag == 1){
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $stok);
					}
				}
				$html.='<td class="td-right">'.number_format($total,0).'</td>';
				$html.='</tr>';
				
				if($this->excel_flag == 1){
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total);
				}
				$total_divisi += $total;
			}
			
			$html.='<tr style="background:#'.$warna_divisi.'">';
			$html.='<td></td>';
			$html.='<td class="td-bold">Total '.$divisi.'</td>';
			for($i=0;$i<count($kolom);$i++){
				$html.='<td class="td-right td-bold">'.number_format($jumlah_divisi[$i]).'</td>';
			}
			$html.='<td class="td-right td-bold">'.number_format($total_divisi).'</td>';
			$html.='</tr>';
			
			if($this->excel_flag == 1){
				$currcol = 0;
				$currrow++;
				
				$currcol += 1;
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $divisi);				
				for($i=0;$i<count($kolom);$i++){
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jumlah_divisi[$i]);
					$col_name = PHPExcel_Cell::stringFromColumnIndex($currcol-1);
				}
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_divisi);
				
				$sheet->getStyle('A'.($currrow).':'.$max_col_name.($currrow))->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_divisi);
			}
			$grand_total += $total_divisi;
		}
		
		$html.='<tr style="background:#'.$warna_table_header.'">';
		$html.='<td></td>';
		$html.='<td class="td-bold">TOTAL</td>';
		for($i=0;$i<count($kolom);$i++){
			$html.='<td class="td-right td-bold">'.number_format($jumlah_all[$i]).'</td>';
		}
		$html.='<td class="td-right td-bold">'.number_format($grand_total).'</td>';
		$html.='</tr>';
		
		if($this->excel_flag == 1){
			$currcol = 0;
			$currrow++;
			
			$currcol += 1;
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');				
			for($i=0;$i<count($kolom);$i++){
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jumlah_all[$i]);
				$col_name = PHPExcel_Cell::stringFromColumnIndex($currcol-1);
			}
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total);
			$sheet->getStyle('A'.($currrow).':'.$max_col_name.($currrow))->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);

			$sheet->getStyle('C1:'.$max_col_name.($currrow))->getNumberFormat()->setFormatCode('#,##0');
		}
		
		$html.='</table>';
		$html.= "</div>";
		$html.= "</body></html>";
		
		if($this->excel_flag == 1){
			$sheet->getStyle('C6:'.$max_col_name.'6')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A6:'.$max_col_name.'6')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
			// $sheet->getStyle('A6:'.$max_col_name.'6')->getAlignment()->setWrapText(true); 
			
			$sheet->getStyle('B6:B'.$currrow)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );


			
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

			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
			exit();
			
		}
		
		$data['title'] = $p_nama_laporan;
		$data['content_html'] = $html;
		 
		$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);

		$this->load->view('LaporanResultView',$data);
	}
	
	public function StockTotal()
	{
		$data = array();
		
		$api = 'APITES';
		set_time_limit(60);
		
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}
		
		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;
		
		/*die($url.$this->API_BKT."/MasterWilayah/GetListWilayahPLG?api=".urlencode($api)."&svr=".urlencode($svr)
		."&db=".urlencode($db));*/
		// $GetGudang = json_decode(file_get_contents($url.$this->API_BKT."/MasterGudang/GetListGudang?aktif=Y&api=".urlencode($api)."&svr=".urlencode($svr)."&db=".urlencode($db)),true);
		$GetGudang = file_get_contents($url.$this->API_BKT."/MasterGudang/GetListGudang?aktif=Y&api=".urlencode($api)."&svr=".urlencode($svr)."&db=".urlencode($db));
		$GetGudang = $this->GzipDecodeModel->_decodeGzip_true($GetGudang);

		if ($GetGudang["result"]=="sukses") {
			$data["gudang"] = $GetGudang["data"];
			} else {
			$data["gudang"] = array();
		}
		$GetMerk = json_decode(file_get_contents($url.$this->API_BKT."/MasterBarang/GetListMerk?api=".urlencode($api).
		"&svr=".urlencode($svr)."&db=".urlencode($db)),true);
		if ($GetMerk["result"]=="sukses") {
			$data["merk"] = $GetMerk["data"];
			} else {
			$data["merk"] = array();
		}
		$GetDivisi = json_decode(file_get_contents($url.$this->API_BKT."/MasterBarang/GetListDivisi?api=".urlencode($api).
		"&svr=".urlencode($svr)."&db=".urlencode($db)),true);
		if ($GetDivisi["result"]=="sukses") {
			$data["divisi"] = $GetDivisi["divisi"];
			} else {
			$data["divisi"] = array();
		}
		$data['title'] = 'MY COMPANY | STOCK | REPORT STOCK TOTAL';
		$data['reportOption'] = "STOCK TOTAL";
		$data['formDest'] = "ReportStock/ProsesStockTotal";
		$this->RenderView('ReportStockForm',$data);
	}
	
	public function ProsesStockTotal()
	{
		$data = array();
		$page_title = 'Report Stock';
		
		if(isset($_POST["btnExcel"])){
			$this->excel_flag = 1;
		}
		else{
			$this->excel_flag = 0;
		}
		
		if(isset($_POST['dp1']))
		{
			
			$this->load->library('form_validation');
			$this->form_validation->set_rules('dp1','Tanggal Awal','required');
			$this->form_validation->set_rules('dp2','Tanggal Akhir','required');
			$this->form_validation->set_rules('kategori','Kategori Barang','required');
			
			if($this->form_validation->run())
			{
				$divisi = (($_POST["kategori"]=="P") ? $_POST["divisi"] : $_POST["merk"]);
				$this->Proses_StockTotal($page_title, $_POST["kategori"], $_POST["dp1"], $_POST["dp2"], $_POST["gudang"], $divisi);
				} else  {
				//die("not valid");
				redirect("ReportStock");
			}
		}
		else
		{
			//die("no wilayah");
			redirect("ReportStock");
		}
	}
	
	public function Proses_StockTotal($page_title, $p_kategori, $p_tgl1, $p_tgl2, $p_gudang, $p_divisi)
	{
		
		$data = array();
		$api = 'APITES';
		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;
		
		set_time_limit(60);
		$url = $url.$this->API_BKT."/ReportStock/ReportStockTotal?api=".urlencode($api)."&kategori=".urlencode($p_kategori)
		."&tgl1=".urlencode($p_tgl1)."&tgl2=".urlencode($p_tgl2)."&gudang=".urlencode($p_gudang)
		."&divisi=".urlencode($p_divisi)."&svr=".urlencode($svr)."&db=".urlencode($db);
		$GetStock = json_decode(file_get_contents($url), true);
		
		if ($GetStock["result"]=="sukses") {
			$this->Preview_StockTotal($page_title, $p_kategori, $p_tgl1, $p_tgl2, $p_gudang, $p_divisi, $GetStock["data"]);
			} else {
			die($GetStock["error"]."<br><br>".$url);
		}
	}
	
	public function Preview_StockTotal($page_title, $p_kategori, $p_tgl1, $p_tgl2, $p_gudang, $p_divisi, $data) 
	{
		$style_col_ganjil ="float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#ffffcc;";
		$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#fff;";
		$style_summary= "float:left;height:30px;line-height:30px;vertical-align:middle;border-right:1px solid #ccc;border-left:1px solid #ccc;background-color:#cccccc;";
		
		$kanan= "text-align:right;padding-right:10px;";
		$kiri = "text-align:left; padding-left:10px;";
		
		$content_html = "<html><body>";
		$content_html.= "<div id='div_header' style='margin-bottom:20px;padding-left:10px;'>";
		$content_html.= "	<div><h2>LAPORAN TOTAL STOCK ".(($p_kategori=="P")?"PRODUK":"SPAREPART")."</h2></div>";
		$content_html.= "	<div><b>Gudang : ".$p_gudang."</b></div>";
		$content_html.= "	<div><b>Divisi : ".$p_divisi."</b></div>";
		$content_html.= "	<div><b>Periode : ".date("d-M-Y", strtotime($p_tgl1))." - ".date("d-M-Y", strtotime($p_tgl2))."</b></div>";
		$content_html.= "</div>";	//close div_header
		
		/*if($this->excel_flag == 1){
			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->setTitle('LaporanForecastBeliJual');
			$this->excel->getActiveSheet()->setCellValue('A1', 'LAPORAN FORECAST BELI/JUAL/STOCK PER PERIODE ALL KOTA');
			$this->excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
			$this->excel->getActiveSheet()->setCellValue('A2', 'Divisi : '.$p_divisi);
			$this->excel->getActiveSheet()->setCellValue('A3', 'Periode : '.$p_pp);
		}*/
		
		$style_col = $style_col_genap;
		
		$content_html.= "<table>";
		$content_html.= "	<tr>";
		$content_html.= "	<div id='div_column_header' style='width:1500px!important;line-height:90px;vertical-align:middle;'>";
		$content_html.= "		<div style='width:20%;".$style_summary.$kiri."height:60px!important;'><b>Kode Barang</b></div>";
		$content_html.= "		<div style='width:6%;".$style_summary.$kiri."height:60px!important;'><b>Nama Barang</b></div>";
		$content_html.= "		<div style='width:6%;".$style_summary.$kiri."height:60px!important;'><b>Stock Awal</b></div>";
		$content_html.= "		<div style='width:68%;".$style_summary."height:60px!important;'><div>";
		$content_html.= "			<div style='width:15%;".$style_summary.$kiri."height:60px!important;'><b>No Tagihan</b></div>";
		$content_html.= "			<div style='width:10%;".$style_summary.$kiri."height:60px!important;'><b>Tgl JT</b></div>";
		$content_html.= "			<div style='width:15%;".$style_summary.$kanan."height:60px!important;'><b>Total Tagihan</b></div>";
		$content_html.= "			<div style='width:10%;".$style_summary.$kiri."height:60px!important;'><b>No Penerimaan</b></div>";
		$content_html.= "			<div style='width:10%;".$style_summary.$kiri."height:60px!important;'><b>Tgl Penerimaan</b></div>";
		$content_html.= "			<div style='width:10%;".$style_summary.$kiri."height:60px!important;'><b>Tipe Penerimaan</b></div>";
		$content_html.= "			<div style='width:15%;".$style_summary.$kanan."height:60px!important;'><b>Total Penerimaan</b></div>";
		$content_html.= "			<div style='width:15%;".$style_summary.$kanan."height:60px!important;'><b>Sisa Tagihan</b></div>";
		$content_html.= "		</div></div>";	//close div_column_header
		$content_html.= "	</div>";	//close div_column_header
		$content_html.= "	<div style='clear:both;'></div>";
		
		$currcol = 0;
		$currrow = 5;
		
		if($this->excel_flag == 1){
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $p_wil.' S_Awal');
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $p_wil.' Beli');
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $p_wil.' Jual');
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $p_wil.' S_Akhir');
			$currcol += 1;
		}
		if($this->excel_flag == 1){
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Total Beli');
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Total Jual');
			$currcol += 1;
		}
		
		$currrow = 7;
		$currcol = 1;
		
		$PLG = "";
		$NM_PLG = "";
		$KD_PLG = "";
		$MARKER = "";
		$TOT_PLG = 0;
		$PEN_PLG = 0;
		$SISA_PLG= 0;
		
		$TAGIHAN = "";
		$NO_TAGIHAN = "";
		$JT_TAGIHAN = "";
		$TOT_TAGIHAN = 0;
		$SISA_TAGIHAN= 0;
		
		$TAG_COUNTER = 0;
		$PLG_COUNTER = 0;
		
		$content_tagihan = "";
		$style_col_str="";
		
		for($i=0;$i<count($data);$i++)
		{
			
			if ($PLG==trim($data[$i]["NM_PLG"])." - ".trim($data[$i]["KD_PLG"])) {
				//$NM_PLG = "&nbsp;";
				//$KD_PLG = "&nbsp;";
				//$MARKER = "&nbsp;";
				$TAG_COUNTER+=1;
				$PEN_PLG += $data[$i]["TOTAL_PENERIMAAN"];
				} else {
				if ($style_col_str=="" || $style_col_str=="genap") {
					$style_col = $style_col_ganjil;
					$style_col_str = "ganjil";
					} else {
					$style_col = $style_col_genap;
					$style_col_str = "genap";
				}
				
				if ($PLG!="") {
					
					$height = $TAG_COUNTER*30;
					$SISA_PLG = (($TOT_PLG - $PEN_PLG)<0)?0:($TOT_PLG - $PEN_PLG);
					
					$content_html.= "	<div id='div_column_header' style='width:1500px;line-height:90px;vertical-align:middle;'>";
					$content_html.= "		<div style='width:20%;height:".$height."px;".$style_col.$kiri."'>".$NM_PLG."</div>";
					$content_html.= "		<div style='width:6%;height:".$height."px;".$style_col.$kiri."'>".$KD_PLG."</div>";
					$content_html.= "		<div style='width:6%;height:".$height."px;".$style_col.$kiri."'>".$MARKER."</div>";
					$content_html.= "		<div style='width:68%;".$style_col."'>";
					$content_html.= $content_tagihan;
					$content_html.= "		</div>";
					$content_html.= "	</div>";
					$content_html.= "	<div style='clear:both;'></div>";
					$content_html.= "	<div id='div_column_header' style='width:1500px;line-height:90px;vertical-align:middle;'>";
					$content_html.= "		<div style='width:32%;".$style_summary.$kiri."'><b>".$PLG."</b></div>";
					$content_html.= "		<div style='width:68%;".$style_summary."'>";
					$content_html.= "		<div style='clear:both;'>";
					$content_html.= "			<div style='width:15%;".$style_summary.$kiri."'>&nbsp;</div>";
					$content_html.= "			<div style='width:10%;".$style_summary.$kiri."'>&nbsp;</div>";
					$content_html.= "			<div style='width:15%;".$style_summary.$kanan."'><b>".number_format($TOT_PLG)."</b></div>";
					$content_html.= "			<div style='width:10%;".$style_summary."'>&nbsp;</div>";
					if(isset($data[$i]["TGL_PENERIMAAN"])) {
						$content_html.= "		<div style='width:10%;".$style_summary.$kiri."'>&nbsp;</div>";
						} else {
						$content_html.= "		<div style='width:10%;".$style_summary.$kiri."'>&nbsp;</div>";
					}
					$content_html.= "			<div style='width:10%;".$style_summary.$kiri."'>&nbsp;</div>";
					$content_html.= "			<div style='width:15%;".$style_summary.$kanan."'><b>".number_format($PEN_PLG)."</b></div>";
					$content_html.= "			<div style='width:15%;".$style_summary.$kanan."'><b>".number_format($SISA_PLG)."</b></div>";
					$content_html.= "		</div>";
					$content_html.= "		</div>";
					$content_html.= "	</div>";
					$content_html.= "	<div style='clear:both;'></div>";
					
				}
				
				$TAGIHAN = "";
				$PLG_COUNTER += 1;
				$TAG_COUNTER = 1;
				$TOT_PLG = 0;
				$SISA_PLG = 0;
				$PEN_PLG = $data[$i]["TOTAL_PENERIMAAN"];
				
				
				$PLG = trim($data[$i]["NM_PLG"])." - ".trim($data[$i]["KD_PLG"]);
				$KD_PLG = trim($data[$i]["KD_PLG"]);
				//$NM_PLG = "<a href='".site_url("Master/GetDealer/".urlencode($KD_PLG))."' target='blank'>".trim($data[$i]["NM_PLG"])."</a>";
				$NM_PLG = trim($data[$i]["NM_PLG"]);
				$MARKER = trim($data[$i]["MARKER"]);
				$content_tagihan = "";
				
			}
			
			if ($TAGIHAN!=trim($data[$i]["NO_TAGIHAN"])) {
				$TAGIHAN = trim($data[$i]["NO_TAGIHAN"]);
				//$NO_TAGIHAN = "<a href='".site_url("Finance/GetTagihan/".urlencode($TAGIHAN))."' target='blank'>".trim($data[$i]["NO_TAGIHAN"])."</a>";
				$NO_TAGIHAN = trim($data[$i]["NO_TAGIHAN"]);
				$JT_TAGIHAN = date("d-M-Y", strtotime($data[$i]["TGL_JTTAGIHAN"]));
				$TOT_TAGIHAN = number_format($data[$i]["TOTAL_TAGIHAN"]);
				$SISA_TAGIHAN= number_format($data[$i]["SISA_TAGIHAN"]);
				
				$TOT_PLG += $data[$i]["TOTAL_TAGIHAN"];
				$SISA_PLG += $data[$i]["SISA_TAGIHAN"];
				} else {
				$NO_TAGIHAN  = "&nbsp;";
				$JT_TAGIHAN  = "&nbsp;";
				$TOT_TAGIHAN = "&nbsp;";
				$SISA_TAGIHAN= "&nbsp;";
			}
			
			
			
			$content_tagihan.= "		<div style='clear:both;'>";
			$content_tagihan.= "			<div style='width:15%;".$style_col.$kiri."'>".$NO_TAGIHAN."</div>";
			$content_tagihan.= "			<div style='width:10%;".$style_col.$kiri."'>".$JT_TAGIHAN."</div>";
			$content_tagihan.= "			<div style='width:15%;".$style_col.$kanan."'>".$TOT_TAGIHAN."</div>";
			if(isset($data[$i]["NO_PENERIMAAN"]) && $data[$i]["NO_PENERIMAAN"]!="") {
				//$content_tagihan.= "		<div style='width:10%;".$style_col.$kiri."'>"."<a href='".site_url("Master/GetPenerimaan/".urlencode($data[$i]["NO_PENERIMAAN"]))."' target='blank'>".$data[$i]["NO_PENERIMAAN"]."</a></div>";
				$content_tagihan.= "		<div style='width:10%;".$style_col.$kiri."'>".$data[$i]["NO_PENERIMAAN"]."</div>";
				} else {
				$content_tagihan.= "		<div style='width:10%;".$style_col.$kiri."'>-</div>";
			}
			if(isset($data[$i]["TGL_PENERIMAAN"])) {
				$content_tagihan.= "		<div style='width:10%;".$style_col.$kiri."'>".date("d-M-Y", strtotime($data[$i]["TGL_PENERIMAAN"]))."</div>";
				} else {
				$content_tagihan.= "		<div style='width:10%;".$style_col.$kiri."'>-</div>";
			}
			if(isset($data[$i]["TYPE_PENERIMAAN"])) {
				$TYPE_PENERIMAAN = ((trim($data[$i]["TYPE_PENERIMAAN"]) == "TRANSFER VA") ? "VA" : $data[$i]["TYPE_PENERIMAAN"]);
				$content_tagihan.= "			<div style='width:10%;".$style_col.$kiri."'>".$TYPE_PENERIMAAN."</div>";
				} else {
				$content_tagihan.= "			<div style='width:10%;".$style_col.$kiri."'>-</div>";
			}
			if(isset($data[$i]["TOTAL_PENERIMAAN"])) {
				$content_tagihan.= "			<div style='width:15%;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_PENERIMAAN"])."</div>";
				} else {
				$content_tagihan.= "			<div style='width:15%;".$style_col.$kanan."'>0</div>";	
			}
			$content_tagihan.= "			<div style='width:15%;".$style_col.$kanan."'>".$SISA_TAGIHAN."</div>";
			$content_tagihan.= "		</div>";
			
			
			/*if($this->excel_flag == 1){
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$i, $currrow+$j, $qty_s_awal);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$i, $currrow+$j, $qty_t_beli);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$i, $currrow+$j, $qty_t_jual);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$i, $currrow+$j, $qty_s_akhir);
			}*/
			
		}
		
		if ($PLG!="") {
			$height = $TAG_COUNTER*30;
			$SISA_PLG = (($TOT_PLG - $PEN_PLG)<0)?0:($TOT_PLG - $PEN_PLG);
			
			$content_html.= "	<div id='div_column_header' style='width:1500px;line-height:90px;vertical-align:middle;'>";
			$content_html.= "		<div style='width:20%;height:".$height."px;".$style_col.$kiri."'>".$NM_PLG."</div>";
			$content_html.= "		<div style='width:6%;height:".$height."px;".$style_col.$kiri."'>".$KD_PLG."</div>";
			$content_html.= "		<div style='width:6%;height:".$height."px;".$style_col.$kiri."'>".$MARKER."</div>";
			$content_html.= "		<div style='width:68%;".$style_col."'>";
			$content_html.= $content_tagihan;
			$content_html.= "		</div>";
			$content_html.= "	</div>";
			$content_html.= "	<div style='clear:both;'></div>";
			$content_html.= "	<div id='div_column_header' style='width:1500px;line-height:90px;vertical-align:middle;'>";
			$content_html.= "		<div style='width:32%;".$style_summary.$kiri."'><b>".$PLG."</b></div>";
			$content_html.= "		<div style='width:68%;".$style_summary."'>";
			$content_html.= "			<div>";
			$content_html.= "			<div style='width:15%;".$style_summary.$kiri."'>&nbsp;</div>";
			$content_html.= "			<div style='width:10%;".$style_summary.$kiri."'>&nbsp;</div>";
			$content_html.= "			<div style='width:15%;".$style_summary.$kanan."'><b>".number_format($TOT_PLG)."</b></div>";
			$content_html.= "			<div style='width:10%;".$style_summary."'>&nbsp;</div>";
			if(isset($data[$i]["TGL_PENERIMAAN"])) {
				$content_html.= "		<div style='width:10%;".$style_summary.$kiri."'>&nbsp;</div>";
				} else {
				$content_html.= "		<div style='width:10%;".$style_summary.$kiri."'>&nbsp;</div>";
			}
			$content_html.= "			<div style='width:10%;".$style_summary.$kiri."'>&nbsp;</div>";
			$content_html.= "			<div style='width:15%;".$style_summary.$kanan."'><b>".number_format($PEN_PLG)."</b></div>";
			$content_html.= "			<div style='width:15%;".$style_summary.$kanan."'><b>".number_format($SISA_PLG)."</b></div>";
			$content_html.= "			</div>";
			$content_html.= "		</div>";
			$content_html.= "	</div>";
			$content_html.= "	<div style='clear:both;height:50px;'></div>";
			
			//$content_html.= "	<div id='div_column_header' style='height:1px; border-bottom:1px solid #ccc;width:1000px;'></div>";
		}		
		
		/*if($this->excel_flag == 1){
			//PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
			$this->excel->getActiveSheet()->mergeCells('A1:J1');
			for ($i = 'A'; $i !=   $this->excel->getActiveSheet()->getHighestColumn(); $i++) {
		    $this->excel->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
			}
			
			$filename='LaporanForecastBeliJualStockPerPeriodeAllKota['.date('Ymd').'].xls'; //save our workbook as this file name
			header('Content-Type: application/vnd.ms-excel'); //mime type
			header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
			header('Cache-Control: max-age=0'); //no cache
			$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
			//force user to download the Excel file without writing it to server's HD
			$objWriter->save('php://output');
		}*/
		
		
		$content_html.= "		</div>";
		$content_html.= "	</div>";
		$content_html.= "</div>";
		$content_html.= "</body></html>";
		
		
		$data['title'] = $page_title;
		$data['content_html'] = $content_html;
		
		$this->RenderView('ReportFinanceResult',$data);
		// $this->SetTemplate('template/login');
	}
	
	public function ReportStok(){
		$submit = $this->input->post('submit');

		$api = 'APITES';
		set_time_limit(60);
		
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}
		
		$url = $_SESSION["conn"]->AlamatWebService;
		// $url = 'http://localhost/';
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;

		if($submit==''){
			//blm di submit
			$data =array(
				'formDest' => site_url().'/ReportStock/ReportStok',
			);

			// $GetGudang = json_decode(file_get_contents($url.$this->API_BKT."/MasterGudang/GetListGudang?aktif=Y&api=".urlencode($api)."&svr=".urlencode($svr)."&db=".urlencode($db)),true);

			$GetGudang = file_get_contents($url.$this->API_BKT."/MasterGudang/GetListGudang?aktif=Y&api=".urlencode($api)."&svr=".urlencode($svr)."&db=".urlencode($db));
			$GetGudang = $this->GzipDecodeModel->_decodeGzip_true($GetGudang);

			if ($GetGudang["result"]=="sukses") {
				$data["gudang"] = $GetGudang["data"];
				} else {
				$data["gudang"] = array();
			}
			
			$GetMerk = json_decode(file_get_contents($this->API_URL."/index.php/MsBarang/GetMerkList?api=".urlencode($api)."&divisi=all"),true);
			if ($GetMerk["result"]=="sukses") {
				$data["merk"] = $GetMerk["data"];
				} else {
				$data["merk"] = array();
			}
			
			$GetDivisi = json_decode(file_get_contents($this->API_URL."/index.php/MsBarang/GetDivisiList?api=".urlencode($api)),true);
			if ($GetDivisi["result"]=="sukses") {
				$data["divisi"] = $GetDivisi["data"];
				} else {
				$data["divisi"] = array();
			}
			
			// // $GetBarang = json_decode(file_get_contents($this->API_URL."/index.php/MsBarang/GetBarangListGET?api=".urlencode($api)),true);
			
			// // // echo json_encode($GetBarang);die;
			
			// // if ($GetBarang["result"]=="sukses") {
				// // // $data["produk"] = $GetBarang["data"];
				// // $produk = array();
				// // foreach($GetBarang["data"] as $d){
					// // $produk[$d['MERK']][] = $d['KD_BRG'].' | '.$d['NM_BRG'];
				// // }
				// // // echo json_encode($produk); die;
				// // $data["produk"] = json_encode($produk);
				
			// // }
			// // else {
				// // $data["produk"] = array();
			// // }
			
			
			// // $GetSparepart = json_decode(file_get_contents($this->API_URL."/index.php/MsBarang/GetSparepartList?api=".urlencode($api)),true);
			
			// // // echo json_encode($GetSparepart);die;
			
			// // if ($GetSparepart["result"]=="sukses") {
				// // // $data["sparepart"] = $GetSparepart["data"];
				// // $sparepart = array();
				// // foreach($GetSparepart["data"] as $d){
					// // $sparepart[$d['MERK']][] = $d['KD_SPAREPART'].' | '.$d['NM_SPAREPART'];
				// // }
				// // // echo json_encode($sparepart); die;
				// // $data["sparepart"] = json_encode($sparepart);
			// // }
			// // else {
				// // $data["sparepart"] = array();
			// // }

			$data['title'] = 'MY COMPANY | STOCK | REPORT STOCK';
			$this->RenderView('CetakTotalStock3A',$data);
		}
		else{
			$report = $this->input->post('report');

			switch($report){
				case 'A':
					//GBKTKPK1 Gudang Bhakti Kapuk
					$this->_CetakTotalStock3A($_POST);
					break;
				case 'B':
					$this->_CetakTotalStock3F($_POST);
					break;
				case 'C':
					$this->_CetakTotalStock3C($_POST);
					break;	
			}
		}
	
	}
	
	private function _CetakTotalStock3A($post){
		$_POST = $post;

		$api = 'APITES';
		set_time_limit(60);
		
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}
		
		$url = $_SESSION["conn"]->AlamatWebService;
		// $url = 'http://localhost/';
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;

		$submit = $this->input->post('submit');
		$tahun = $this->input->post('tahun');
		$bulan = $this->input->post('bulan');
		$tipeProduk = $this->input->post('tipe_produk');
		$kdGudang = $this->input->post('kd_gudang');
		$divisi = $this->input->post('divisi');
		$checkInTanpaHarga = (int) $this->input->post('check_in_tanpa_harga');
		$checkUrutPart = (int)$this->input->post('check_urut_part');
		$tipeGrouping = $this->input->post('tipe_grouping');
		$chkSemuaGdg = (int)$this->input->post('chk_semua_gdg');
		$chkGudangAktif = (int)$this->input->post('chk_gudang_aktif');
		

		$tglAwal = date('Y-m-d',strtotime($tahun.'-'.$bulan.'-01'));
		$tglAkhir = date('Y-m-t',strtotime($tahun.'-'.$bulan.'-01'));

		$urlCetakkStock = $url.$this->API_BKT."/ReportStock/CetakTotalStock3A";
		$dataContent = array(
			'api' => $api, 
			'svr' => $svr,
			'db' => $db,
			'tgl_awal' => $tglAwal, 
			'tgl_akhir' => $tglAkhir,
			'tipe_produk' => $tipeProduk,//1 produk | 0 sparepart
			'kd_gudang' => $kdGudang,
			'divisi' => $divisi,
			'check_in_tanpa_harga' => $checkInTanpaHarga,
			'check_urut_part' => $checkUrutPart,
			'tipe_grouping' => $tipeProduk,
			'chk_semua_gdg' => $chkSemuaGdg,
			'chk_gudang_aktif' => $chkGudangAktif,
		);
		$options = array(
		    'http' => array(
		    	'method' => 'POST',
		    	'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
	   		 	'content' => http_build_query($dataContent)
   		 	)
		);
		$stream = stream_context_create($options);
		$getResult =  file_get_contents($urlCetakkStock, false, $stream );
		$resultArray = json_decode($getResult,true);

		$tipeProdukTmp = $tipeProduk == 1 ? 'PRODUK' : 'SPAREPART';
		if($resultArray!=null){
			set_time_limit(60);
			
			$groupStock = array();
			$groupGudang = array();
			foreach ($resultArray as $key => $value) {
				$groupGudang[$value['Kd_Gudang']] = $value['Nm_Gudang'];
				$groupStock[$value['Kd_Gudang']][$value['Divisi']][$value['Merk']][] = $value; 
			}
			//log_message('error','groupStock '.print_r($groupStock,true));
			//log_message('error','groupGudang '.print_r($groupGudang,true));

			if($submit=='PREVIEW'){
				require_once __DIR__ . '\vendor\autoload.php';
				$mpdf = new \Mpdf\Mpdf(array(
					'mode' => '',
					'format' => 'A4',
					'default_font_size' => 8,
					'default_font' => 'tahoma',
					'margin_left' => 10,
					'margin_right' => 10,
					'margin_top' => 30,
					'margin_bottom' => 10,
					'margin_header' => 10,
					'margin_footer' => 0,
					'orientation' => 'P'
				));

				$data = array(
					'getStock' => $groupStock,
					'getGudang' => $groupGudang,
				);
				
				if($checkInTanpaHarga==1){
					//$this->load->view('template_pdf/TotalStock3A_TanpaHarga',$data);
					$content = $this->load->view('template_pdf/TotalStock3A_TanpaHarga',$data,true);
				}
				else{
					//$this->load->view('template_pdf/TotalStock3A_TanpaHarga',$data);
					$content = $this->load->view('template_pdf/TotalStock3A_DenganHarga',$data,true);
				}
				
				$header = '<h1 style="margin-top:10%;margin-bottom:0;width:100%;text-align:center;">LAPORAN TOTAL STOCK '.$tipeProdukTmp.'</h1>';
				$header .= '<h2 style="margin-top:0;width:100%;text-align:center;font-weight:none;">PERIODE: '.date('d-M-Y',strtotime($tglAwal)).' S/D '.date('d-M-Y',strtotime($tglAkhir)).'</h2>';
				
				$mpdf->SetHTMLHeader($header); //Yang diulang di setiap awal halaman  (Header)
    			$mpdf->WriteHTML($content);
				$mpdf->Output();
			}
			else if($submit=='EXCEL'){

				$data = array(
					'spreadsheet' => new Spreadsheet(),
					'tipeProdukTmp' => $tipeProdukTmp,
					'tglAwal' => $tglAwal,
					'tglAkhir' => $tglAkhir,
					'getStock' => $groupStock,
					'getGudang' => $groupGudang,

				);
				if($checkInTanpaHarga==1){
					$this->load->view('template_xls/TotalStock3A_TanpaHarga',$data);
				}
				else{
					$this->load->view('template_xls/TotalStock3A_DenganHarga',$data);
				}

			}
			else{
				echo 'submit tidak dikenal';
			}
		}
	
	}
		
	public function Compare(){
		$data['title'] = 'MY COMPANY | STOCK | REPORT STOCK COMPARE';
		$data['formDest'] = "ReportStock/ProsesStock"; 
		$this->RenderView('ReportStockCompareForm',$data);
	}
		
	public function Preview()
	{
		if(isset($_POST["btnPreview"])){
			$this->excel_flag = 0;
		}
		else{
			$this->excel_flag = 1;
		}
		if(ISSET($_FILES['kantor']['name']) && ISSET($_FILES['gudang']['name'])) {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
			$spreadsheet = $reader->load($_FILES['kantor']['tmp_name']);
			$kantor = $spreadsheet->getSheet(0)->toArray(null, true, false, false);
			$spreadsheet = $reader->load($_FILES['gudang']['tmp_name']);
			$gudang = $spreadsheet->getSheet(0)->toArray(null, true, false, false);
			
			// echo json_encode($kantor); die;
			
			// echo json_encode($kantor[1][0]); die;
			
			$error='';
			$valid=1;
			if(ISSET($kantor[1][0])){
				$periode = str_replace('Periode = ','',$kantor[1][0]); 
				$tgl = explode(" s/d ",$periode);
				if(ISSET($tgl[0]) && ISSET($tgl[1])){
					$kantorStartDate = $tgl[0];
					$kantorEndDate = $tgl[1];
				}
				else {
					$valid=0;
					$error= 'File excel stock kantor tidak valid';
				}
			}
			
			if($valid==1 && ISSET($gudang[1][0])){
				// $periode = str_replace('Periode = ','',$kantor[1][0]); 
				$tgl = explode(" s/d ",$gudang[1][0]);
				if(ISSET($tgl[0]) && ISSET($tgl[1])){
					$gudangStartDate = $tgl[0];
					$gudangEndDate = $tgl[1];
				}
				else {
					$valid=0;
					$error= 'File excel stock gudang tidak valid';
				}
			}
			
			if($valid==1){
				if(strtotime($kantorStartDate) != strtotime($gudangStartDate) || strtotime($kantorEndDate) != strtotime($gudangEndDate)){
					$valid=0;
					$error= 'Tanggal awal dan tanggal akhir tidak sesuai';
				}
			}
			
			if($valid==1){
				$i_divisi = 0;
				$i_merk = 0;
				$i_item=0;
				$divisi = '';
				$merk = '';
				$data_kantor = array();
				for($i=0;$i<count($kantor);$i++){
					
					if($i<4) continue;
					if($kantor[$i][0]=='') continue;
					
						if (strpos($kantor[$i][0],'Divisi : ') !== false) {
							$i_divisi++;
							$i_merk = 0;
							$divisi = str_replace('Divisi : ','',$kantor[$i][0]);
							$data_kantor[$i_divisi] = array('divisi'=>$divisi);
							$data_kantor[$i_divisi]['merk'] = array();
							
							$i=$i+1;
						}
						
						elseif (strpos($kantor[$i][0],'Merk : ') !== false) {
							$i_merk++;
							$i_item=0;
							$merk = str_replace('Merk : ','',$kantor[$i][0]);
							$data_kantor[$i_divisi]['merk'][$i_merk] = array('merk'=>$merk);
							$data_kantor[$i_divisi]['merk'][$i_merk]['item'] = array();
							$i=$i+1;
						}
					else{
						$i_item++;
						$item = array(
							"kantor_kd_brg" => $kantor[$i][0],
							"kantor_nm_brg" => $kantor[$i][2],
							"kantor_awal" => $kantor[$i][4],
							"kantor_beli" => $kantor[$i][5],
							"kantor_jual" => $kantor[$i][6],
							"kantor_retur" => $kantor[$i][7],
							"kantor_mutasi_t" => $kantor[$i][8],
							"kantor_mutasi_k" => $kantor[$i][9],
							"kantor_terima" => $kantor[$i][10],
							"kantor_keluar" => $kantor[$i][11],
							"kantor_akhir" => $kantor[$i][12],
							"gudang_kd_brg" => "",
							"gudang_nm_brg" => "",
							"gudang_awal" => 0,
							"gudang_beli" => 0,
							"gudang_jual" => 0,
							"gudang_mutasi" => 0,
							"gudang_retur" => 0,
							"gudang_sisa" => 0,
							"selisih" => 1
							
							);
						$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item] = $item;
					}
				
				}
				// echo json_encode($data_kantor); die;
				
				$divisi = '';
				$merk = '';
				$data_gudang = array();
				$dt = array();
				for($i=0;$i<count($gudang);$i++){
					if($i<4) continue;
					if($gudang[$i][0]=='') continue;
					if (strpos($gudang[$i][0],'Divisi : ') !== false) {
						$divisi = str_replace('Divisi : ','',$gudang[$i][0]);
						$i=$i+1;
					}
					elseif (strpos($gudang[$i][0],'Merk : ') !== false) {
						$merk = str_replace('Merk : ','',$gudang[$i][0]);
						$i=$i+1;
					}
					else{
						$d = array(
							"divisi" => $divisi,
							"merk" => $merk,
							"gudang_kd_brg" => $gudang[$i][0],
							"gudang_nm_brg" => $gudang[$i][1],
							"gudang_awal" => $gudang[$i][2],
							"gudang_beli" => $gudang[$i][3],
							"gudang_jual" => $gudang[$i][4],
							"gudang_mutasi" => $gudang[$i][5],
							"gudang_retur" => $gudang[$i][6],
							"gudang_sisa" => $gudang[$i][7],
							);
					
						$gudang_kd_brg = $gudang[$i][0];
						
						foreach($data_kantor as $i_divisi => $array_divisi) {
							if($array_divisi['divisi'] == $divisi){
								foreach($array_divisi['merk'] as $i_merk => $array_merk) {
									if($array_merk['merk'] == $merk){
										$exist = 0;
										foreach($array_merk['item'] as $i_item => $item) {
											if($gudang_kd_brg==$item['kantor_kd_brg']){
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['gudang_kd_brg'] = $gudang[$i][0];
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['gudang_nm_brg'] = $gudang[$i][1];
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['gudang_awal'] = $gudang[$i][2];
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['gudang_beli'] = $gudang[$i][3];
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['gudang_jual'] = $gudang[$i][4];
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['gudang_mutasi'] = $gudang[$i][5];
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['gudang_retur'] = $gudang[$i][6];
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['gudang_sisa'] = $gudang[$i][7];
																									
												$awal = ($item['kantor_awal']) - ($gudang[$i][2]);
												$akhir = ($item['kantor_akhir']) - ($gudang[$i][7]);
												$masuk = (($item['kantor_beli']) + ($item['kantor_retur']) + ($item['kantor_mutasi_t']) + ($item['kantor_terima'])) - (($gudang[$i][3]) + ($gudang[$i][6]));
												$keluar = (($item['kantor_jual']) + ($item['kantor_mutasi_k']) + ($item['kantor_keluar'])) - (($gudang[$i][4]) + ($gudang[$i][5]));
												
												$selisih = 0;
												if($awal!=0 || $akhir!=0 || $masuk!=0 || $keluar!=0){
													// $selisih = '|awal='.$awal.'|akhir='.$akhir.'|masuk='.$masuk.'|keluar='.$keluar;
													$selisih = 1;
												}
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['selisih'] = $selisih;
												
												$exist = 1;
											}
										}
										if($exist==0){
											$i_item++;
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['kantor_kd_brg']=$gudang[$i][0];
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['kantor_nm_brg']=$gudang[$i][1];
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['kantor_awal']=0;
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['kantor_beli']=0;
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['kantor_jual']=0;
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['kantor_retur']=0;
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['kantor_mutasi_t']=0;
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['kantor_mutasi_k']=0;
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['kantor_terima']=0;
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['kantor_keluar']=0;
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['kantor_akhir']=0;
								
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['gudang_kd_brg'] = $gudang[$i][0];
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['gudang_nm_brg'] = $gudang[$i][1];
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['gudang_awal'] = $gudang[$i][2];
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['gudang_beli'] = $gudang[$i][3];
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['gudang_jual'] = $gudang[$i][4];
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['gudang_mutasi'] = $gudang[$i][5];
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['gudang_retur'] = $gudang[$i][6];
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['gudang_sisa'] = $gudang[$i][7];
												
												$data_kantor[$i_divisi]['merk'][$i_merk]['item'][$i_item]['selisih'] = 1;
												
										}
									}
								}
							}
						}
					}
				}
				
				// echo json_encode($data_kantor,JSON_NUMERIC_CHECK);die;
				
				$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
				$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
				$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
				$vertical_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
				$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
				
				$warna_selisih = 'F08080';
				$warna_merk = 'C0C0C0';
				$warna_divisi = 'A9A9A9';
				$warna_total = '808080';
				
				$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet(0);
				
				$p_nama_laporan = 'REPORT STOCK COMPARE';
				$html = "";
				$html .= "<html>";
				$html .= "<head>";
				
				$html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
				$html .= "<style>
				body{font-family:'Calibri',Arial;}
				table{padding:0;margin:0;border-collapse:collapse}
				 td, th { border:0.5px solid #555; padding:2px!important; }
				.td-center { text-align: center; }
				.td-right { text-align:right;}
				.td-bold { font-weight:bold}
				</style>";
				
				$html.= "</head>";
				$html.= "<body>";
				
				$html .= '<center>';
				$html .= '<h2>'.$p_nama_laporan.'<br><small>Periode: '.$kantorStartDate.' sd '.$gudangEndDate.'</small></h2>';
				$html .= '</center>';
				
				$currcol = 1;
				$currrow = 1;
				
				if($this->excel_flag == 1){
					$sheet->getStyle('A'.$currrow)->getFont()->setBold(true);
					$sheet->getStyle('A'.$currrow)->getFont()->setSize(14);
					$sheet->setCellValueByColumnAndRow($currcol, $currrow++, $p_nama_laporan);
					$sheet->getStyle('A'.$currrow)->getFont()->setBold(true);
					$sheet->getStyle('A'.$currrow)->getFont()->setSize(14);
					$sheet->setCellValueByColumnAndRow($currcol, $currrow++, 'Periode: '.$kantorStartDate.' sd '.$gudangEndDate);
				}
				
				$grand_total_kantor_awal = 0;
				$grand_total_kantor_beli = 0;
				$grand_total_kantor_jual = 0;
				$grand_total_kantor_retur = 0;
				$grand_total_kantor_mutasi_t = 0;
				$grand_total_kantor_mutasi_k = 0;
				$grand_total_kantor_terima = 0;
				$grand_total_kantor_keluar = 0;
				$grand_total_kantor_akhir = 0;
				$grand_total_gudang_awal = 0;
				$grand_total_gudang_beli = 0;
				$grand_total_gudang_jual = 0;
				$grand_total_gudang_mutasi = 0;
				$grand_total_gudang_retur = 0;
				$grand_total_gudang_sisa = 0;
				
				$html .= '<table>';
				$html .= '<tr>';
				$html .= '<th width="100px" rowspan="2">KODE BARANG</th>';
				$html .= '<th width="400px" rowspan="2">NAMA BARANG</th>';
				$html .= '<th colspan="9">KANTOR</th>';
				$html .= '<th colspan="9">GUDANG</th>';
				$html .= '</tr>';
				$html .= '<tr>';
				$html .= '<th width="60px">STOK AWAL</th>';
				$html .= '<th width="60px">BELI</th>';
				$html .= '<th width="60px">JUAL</th>';
				$html .= '<th width="60px">RETUR</th>';
				$html .= '<th width="60px">MUTASI T</th>';
				$html .= '<th width="60px">MUTASI K</th>';
				$html .= '<th width="60px">TERIMA</th>';
				$html .= '<th width="60px">KELUAR</th>';
				$html .= '<th width="60px">STOK AKHIR</th>';
				$html .= '<th width="60px">STOK AWAL</th>';
				$html .= '<th width="60px">BELI</th>';
				$html .= '<th width="60px">JUAL</th>';
				$html .= '<th width="60px">MUTASI</th>';
				$html .= '<th width="60px">RETUR</th>';
				$html .= '<th width="60px">STOK AKHIR</th>';
				$html .= '</tr>';		
				foreach($data_kantor as $i_divisi => $array_divisi) {
					
					$total_divisi_kantor_awal = 0;
					$total_divisi_kantor_beli = 0;
					$total_divisi_kantor_jual = 0;
					$total_divisi_kantor_retur = 0;
					$total_divisi_kantor_mutasi_t = 0;
					$total_divisi_kantor_mutasi_k = 0;
					$total_divisi_kantor_terima = 0;
					$total_divisi_kantor_keluar = 0;
					$total_divisi_kantor_akhir = 0;
					$total_divisi_gudang_awal = 0;
					$total_divisi_gudang_beli = 0;
					$total_divisi_gudang_jual = 0;
					$total_divisi_gudang_mutasi = 0;
					$total_divisi_gudang_retur = 0;
					$total_divisi_gudang_sisa = 0;
					
					$html .= '<tr><td class="td-bold" colspan="17">Divisi: '.$array_divisi['divisi'].'</td></tr>';
					
					if($this->excel_flag == 1){
						$currcol= 1;
						$currrow++;
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Divisi: '.$array_divisi['divisi']);
						$sheet->getStyle('A'.$currrow)->getFont()->setBold(true);
						// $sheet->getStyle('A'.$currrow)->getFont()->setSize(14);
						
					}
					foreach($array_divisi['merk'] as $i_merk => $array_merk) {
					
						$html .= '<tr><td class="td-bold" colspan="17">Merk: '.$array_merk['merk'].'</td></tr>';
						
						if($this->excel_flag == 1){
							$currcol= 1;
							$currrow++;
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Merk: '.$array_merk['merk']);
							$sheet->getStyle('A'.$currrow)->getFont()->setBold(true);
							// $sheet->getStyle('A'.$currrow)->getFont()->setSize(14);
							$currcol= 1;
							$currrow++;
							
							$start_row = $currrow;
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'KODE BARANG');
							$sheet->mergeCells('A'.$currrow.':A'.($currrow+1));
							$sheet->getStyle('A'.$currrow)->getAlignment()->setHorizontal($vertical_center);
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'NAMA BARANG');
							$sheet->mergeCells('B'.$currrow.':B'.($currrow+1));
							$sheet->getStyle('A'.$currrow)->getAlignment()->setHorizontal($vertical_center);
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'KANTOR');
							$sheet->mergeCells('C'.$currrow.':K'.$currrow);
							$sheet->getStyle('C'.$currrow)->getAlignment()->setHorizontal($alignment_center);
							$currcol+=8;
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'GUDANG');
							$sheet->mergeCells('L'.$currrow.':Q'.$currrow);
							$sheet->getStyle('L'.$currrow)->getAlignment()->setHorizontal($alignment_center);
							
							$currcol= 3;
							$currrow++;
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Stok Awal');
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Beli');
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Jual');
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Retur');
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Mutasi T');
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Mutasi K');
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Terima');
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Keluar');
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Stok Akhir');
							
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Stok Awal');
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Beli');
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Jual');
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Mutasi');
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Retur');
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Stok Akhir');
							
							$sheet->getStyle('A'.($currrow-1).':Q'.$currrow)->getFont()->setBold(true);
						}
						
						//urutkan yg selisih di atas
						array_multisort(array_map(function($element) {
							  return $element['selisih'];
						  }, $array_merk['item']), SORT_DESC, $array_merk['item']);
						  
						  
						$total_kantor_awal = 0;
						$total_kantor_beli = 0;
						$total_kantor_jual = 0;
						$total_kantor_retur = 0;
						$total_kantor_mutasi_t = 0;
						$total_kantor_mutasi_k = 0;
						$total_kantor_terima = 0;
						$total_kantor_keluar = 0;
						$total_kantor_akhir = 0;
						$total_gudang_awal = 0;
						$total_gudang_beli = 0;
						$total_gudang_jual = 0;
						$total_gudang_mutasi = 0;
						$total_gudang_retur = 0;
						$total_gudang_sisa = 0;


						foreach($array_merk['item'] as $i_item => $item) {
							$warna = '';
							if(($item['selisih'])>0)
							$warna = ' style="background:#'.$warna_selisih.'"';
						
							$html .= '<tr '.$warna.'>';
							$html .= '<td>'.$item['kantor_kd_brg'].'</td>';
							$html .= '<td>'.$item['kantor_nm_brg'].'</td>';
							$html .= '<td class="td-right">'.number_format($item['kantor_awal']).'</td>';
							$html .= '<td class="td-right">'.number_format($item['kantor_beli']).'</td>';
							$html .= '<td class="td-right">'.number_format($item['kantor_jual']).'</td>';
							$html .= '<td class="td-right">'.number_format($item['kantor_retur']).'</td>';
							$html .= '<td class="td-right">'.number_format($item['kantor_mutasi_t']).'</td>';
							$html .= '<td class="td-right">'.number_format($item['kantor_mutasi_k']).'</td>';
							$html .= '<td class="td-right">'.number_format($item['kantor_terima']).'</td>';
							$html .= '<td class="td-right">'.number_format($item['kantor_keluar']).'</td>';
							$html .= '<td class="td-right td-bold">'.number_format($item['kantor_akhir']).'</td>';
							$html .= '<td class="td-right">'.number_format($item['gudang_awal']).'</td>';
							$html .= '<td class="td-right">'.number_format($item['gudang_beli']).'</td>';
							$html .= '<td class="td-right">'.number_format($item['gudang_jual']).'</td>';
							$html .= '<td class="td-right">'.number_format($item['gudang_mutasi']).'</td>';
							$html .= '<td class="td-right">'.number_format($item['gudang_retur']).'</td>';
							$html .= '<td class="td-right td-bold">'.number_format($item['gudang_sisa']).'</td>';
							$html .= '</tr>';
							
							
							if($this->excel_flag == 1){
								$currcol= 1;
								$currrow++;
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $item['kantor_kd_brg']);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $item['kantor_nm_brg']);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $item['kantor_awal']);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $item['kantor_beli']);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $item['kantor_jual']);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $item['kantor_retur']);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $item['kantor_mutasi_t']);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $item['kantor_mutasi_k']);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $item['kantor_terima']);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $item['kantor_keluar']);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $item['kantor_akhir']);
								$sheet->getStyle('K'.$currrow)->getFont()->setBold(true);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $item['gudang_awal']);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $item['gudang_beli']);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $item['gudang_jual']);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $item['gudang_mutasi']);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $item['gudang_retur']);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $item['gudang_sisa']);
								$sheet->getStyle('Q'.$currrow)->getFont()->setBold(true);
								// $sheet->setCellValueByColumnAndRow($currcol++, $currrow, $item['selisih']);
								
								if(($item['selisih'])>0)
								$sheet->getStyle('A'.($currrow).':Q'.($currrow))->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_selisih);
							}
							
							$total_kantor_awal += $item['kantor_awal'];
							$total_kantor_beli += $item['kantor_beli'];
							$total_kantor_jual += $item['kantor_jual'];
							$total_kantor_retur += $item['kantor_retur'];
							$total_kantor_mutasi_t += $item['kantor_mutasi_t'];
							$total_kantor_mutasi_k += $item['kantor_mutasi_k'];
							$total_kantor_terima += $item['kantor_terima'];
							$total_kantor_keluar += $item['kantor_keluar'];
							$total_kantor_akhir += $item['kantor_akhir'];
							$total_gudang_awal += $item['gudang_awal'];
							$total_gudang_beli += $item['gudang_beli'];
							$total_gudang_jual += $item['gudang_jual'];
							$total_gudang_mutasi += $item['gudang_mutasi'];
							$total_gudang_retur += $item['gudang_retur'];
							$total_gudang_sisa += $item['gudang_sisa'];
							
						}
						
						
						$html .= '<tr style="background:#'.$warna_merk.'">';
						$html .= '<td class="td-right td-bold" colspan="2">Total Merk '.$array_merk['merk'].'</td>';
						$html .= '<td class="td-right td-bold">'.number_format($total_kantor_awal).'</td>';
						$html .= '<td class="td-right td-bold">'.number_format($total_kantor_beli).'</td>';
						$html .= '<td class="td-right td-bold">'.number_format($total_kantor_jual).'</td>';
						$html .= '<td class="td-right td-bold">'.number_format($total_kantor_retur).'</td>';
						$html .= '<td class="td-right td-bold">'.number_format($total_kantor_mutasi_t).'</td>';
						$html .= '<td class="td-right td-bold">'.number_format($total_kantor_mutasi_k).'</td>';
						$html .= '<td class="td-right td-bold">'.number_format($total_kantor_terima).'</td>';
						$html .= '<td class="td-right td-bold">'.number_format($total_kantor_keluar).'</td>';
						$html .= '<td class="td-right td-bold">'.number_format($total_kantor_akhir).'</td>';
						$html .= '<td class="td-right td-bold">'.number_format($total_gudang_awal).'</td>';
						$html .= '<td class="td-right td-bold">'.number_format($total_gudang_beli).'</td>';
						$html .= '<td class="td-right td-bold">'.number_format($total_gudang_jual).'</td>';
						$html .= '<td class="td-right td-bold">'.number_format($total_gudang_mutasi).'</td>';
						$html .= '<td class="td-right td-bold">'.number_format($total_gudang_retur).'</td>';
						$html .= '<td class="td-right td-bold">'.number_format($total_gudang_sisa).'</td>';
						$html .= '</tr>';
							
						if($this->excel_flag == 1){
							$styleArray = [
								'borders' => [
									'allBorders' => [
										'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
									],
								],
							];
							
							$sheet ->getStyle('A'.$start_row.':Q'.$currrow)->applyFromArray($styleArray);
						}
						
						if($this->excel_flag == 1){
						
								$currcol= 1;
								$currrow++;
								$currcol++;
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Total Merk '.$array_merk['merk']);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_kantor_awal);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_kantor_beli);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_kantor_jual);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_kantor_retur);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_kantor_mutasi_t);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_kantor_mutasi_k);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_kantor_terima);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_kantor_keluar);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_kantor_akhir);
								
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_gudang_awal);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_gudang_beli);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_gudang_jual);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_gudang_mutasi);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_gudang_retur);
								$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_gudang_sisa);
								$sheet->getStyle('A'.($currrow).':Q'.$currrow)->getFont()->setBold(true);
								$sheet->getStyle('C'.$start_row.':Q'.$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$sheet->getStyle('A'.($currrow).':Q'.($currrow))->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_merk);
								
						}
						
						
						$total_divisi_kantor_awal += $total_kantor_awal;
						$total_divisi_kantor_beli += $total_kantor_beli;
						$total_divisi_kantor_jual += $total_kantor_jual;
						$total_divisi_kantor_retur += $total_kantor_retur;
						$total_divisi_kantor_mutasi_t += $total_kantor_mutasi_t;
						$total_divisi_kantor_mutasi_k += $total_kantor_mutasi_k;
						$total_divisi_kantor_terima += $total_kantor_terima;
						$total_divisi_kantor_keluar += $total_kantor_keluar;
						$total_divisi_kantor_akhir += $total_kantor_akhir;
						$total_divisi_gudang_awal += $total_gudang_awal;
						$total_divisi_gudang_beli += $total_gudang_beli;
						$total_divisi_gudang_jual += $total_gudang_jual;
						$total_divisi_gudang_mutasi += $total_gudang_mutasi;
						$total_divisi_gudang_retur += $total_gudang_retur;
						$total_divisi_gudang_sisa += $total_gudang_sisa;
						
						
					}
					
					
					$html .= '<tr style="background:#'.$warna_divisi.'">';
					$html .= '<td class="td-right td-bold" colspan="2">Total Divisi '.$array_divisi['divisi'].'</td>';
					$html .= '<td class="td-right td-bold">'.number_format($total_divisi_kantor_awal).'</td>';
					$html .= '<td class="td-right td-bold">'.number_format($total_divisi_kantor_beli).'</td>';
					$html .= '<td class="td-right td-bold">'.number_format($total_divisi_kantor_jual).'</td>';
					$html .= '<td class="td-right td-bold">'.number_format($total_divisi_kantor_retur).'</td>';
					$html .= '<td class="td-right td-bold">'.number_format($total_divisi_kantor_mutasi_t).'</td>';
					$html .= '<td class="td-right td-bold">'.number_format($total_divisi_kantor_mutasi_k).'</td>';
					$html .= '<td class="td-right td-bold">'.number_format($total_divisi_kantor_terima).'</td>';
					$html .= '<td class="td-right td-bold">'.number_format($total_divisi_kantor_keluar).'</td>';
					$html .= '<td class="td-right td-bold">'.number_format($total_divisi_kantor_akhir).'</td>';
					$html .= '<td class="td-right td-bold">'.number_format($total_divisi_gudang_awal).'</td>';
					$html .= '<td class="td-right td-bold">'.number_format($total_divisi_gudang_beli).'</td>';
					$html .= '<td class="td-right td-bold">'.number_format($total_divisi_gudang_jual).'</td>';
					$html .= '<td class="td-right td-bold">'.number_format($total_divisi_gudang_mutasi).'</td>';
					$html .= '<td class="td-right td-bold">'.number_format($total_divisi_gudang_retur).'</td>';
					$html .= '<td class="td-right td-bold">'.number_format($total_divisi_gudang_sisa).'</td>';
					$html .= '</tr>';
						
					if($this->excel_flag == 1){
							$currcol= 1;
							$currrow++;
							$currcol++;
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Total Divisi '.$array_divisi['divisi']);
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_divisi_kantor_awal);
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_divisi_kantor_beli);
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_divisi_kantor_jual);
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_divisi_kantor_retur);
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_divisi_kantor_mutasi_t);
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_divisi_kantor_mutasi_k);
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_divisi_kantor_terima);
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_divisi_kantor_keluar);
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_divisi_kantor_akhir);
							
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_divisi_gudang_awal);
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_divisi_gudang_beli);
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_divisi_gudang_jual);
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_divisi_gudang_mutasi);
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_divisi_gudang_retur);
							$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_divisi_gudang_sisa);
							$sheet->getStyle('A'.($currrow).':Q'.$currrow)->getFont()->setBold(true);
							$sheet->getStyle('A'.($currrow).':Q'.$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle('A'.($currrow).':Q'.($currrow))->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_divisi);
							$sheet->getStyle('A'.($currrow).':Q'.$currrow)->getFont()->setSize(12);
							$currrow++;
					}
					
					$grand_total_kantor_awal += $total_divisi_kantor_awal;
					$grand_total_kantor_beli += $total_divisi_kantor_beli;
					$grand_total_kantor_jual += $total_divisi_kantor_jual;
					$grand_total_kantor_retur += $total_divisi_kantor_retur;
					$grand_total_kantor_mutasi_t += $total_divisi_kantor_mutasi_t;
					$grand_total_kantor_mutasi_k += $total_divisi_kantor_mutasi_k;
					$grand_total_kantor_terima += $total_divisi_kantor_terima;
					$grand_total_kantor_keluar += $total_divisi_kantor_keluar;
					$grand_total_kantor_akhir += $total_divisi_kantor_akhir;
					$grand_total_gudang_awal += $total_divisi_gudang_awal;
					$grand_total_gudang_beli += $total_divisi_gudang_beli;
					$grand_total_gudang_jual += $total_divisi_gudang_jual;
					$grand_total_gudang_mutasi += $total_divisi_gudang_mutasi;
					$grand_total_gudang_retur += $total_divisi_gudang_retur;
					$grand_total_gudang_sisa += $total_divisi_gudang_sisa;
				}
				
				$html .= '<tr style="background:#'.$warna_total.'">';
				$html .= '<td class="td-right td-bold" colspan="2">GRAND TOTAL</td>';
				$html .= '<td class="td-right td-bold">'.number_format($grand_total_kantor_awal).'</td>';
				$html .= '<td class="td-right td-bold">'.number_format($grand_total_kantor_beli).'</td>';
				$html .= '<td class="td-right td-bold">'.number_format($grand_total_kantor_jual).'</td>';
				$html .= '<td class="td-right td-bold">'.number_format($grand_total_kantor_retur).'</td>';
				$html .= '<td class="td-right td-bold">'.number_format($grand_total_kantor_mutasi_t).'</td>';
				$html .= '<td class="td-right td-bold">'.number_format($grand_total_kantor_mutasi_k).'</td>';
				$html .= '<td class="td-right td-bold">'.number_format($grand_total_kantor_terima).'</td>';
				$html .= '<td class="td-right td-bold">'.number_format($grand_total_kantor_keluar).'</td>';
				$html .= '<td class="td-right td-bold">'.number_format($grand_total_kantor_akhir).'</td>';
				$html .= '<td class="td-right td-bold">'.number_format($grand_total_gudang_awal).'</td>';
				$html .= '<td class="td-right td-bold">'.number_format($grand_total_gudang_beli).'</td>';
				$html .= '<td class="td-right td-bold">'.number_format($grand_total_gudang_jual).'</td>';
				$html .= '<td class="td-right td-bold">'.number_format($grand_total_gudang_mutasi).'</td>';
				$html .= '<td class="td-right td-bold">'.number_format($grand_total_gudang_retur).'</td>';
				$html .= '<td class="td-right td-bold">'.number_format($grand_total_gudang_sisa).'</td>';
				$html .= '</tr>';
						
				if($this->excel_flag == 1){
					$currcol= 1;
					$currrow++;
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'GRAND TOTAL');
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $grand_total_kantor_awal);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $grand_total_kantor_beli);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $grand_total_kantor_jual);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $grand_total_kantor_retur);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $grand_total_kantor_mutasi_t);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $grand_total_kantor_mutasi_k);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $grand_total_kantor_terima);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $grand_total_kantor_keluar);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $grand_total_kantor_akhir);
					
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $grand_total_gudang_awal);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $grand_total_gudang_beli);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $grand_total_gudang_jual);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $grand_total_gudang_mutasi);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $grand_total_gudang_retur);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $grand_total_gudang_sisa);
					$sheet->getStyle('A'.($currrow).':Q'.$currrow)->getFont()->setBold(true);
					$sheet->getStyle('A'.($currrow).':Q'.$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle('A'.($currrow).':Q'.($currrow))->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
					$sheet->getStyle('A'.($currrow).':Q'.$currrow)->getFont()->setSize(12);
					$currrow++;
				}
				
				
				$html .= '</table>';
				$html.= "</body>";
				$html.= "</html>";
				
				if($this->excel_flag == 1){
					foreach(range('A','Q') as $columnID) {
						if($columnID=='A')
							$sheet->getColumnDimension($columnID)->setWidth(15);
						elseif($columnID=='B')
							$sheet->getColumnDimension($columnID)->setWidth(40);
						else
							$sheet->getColumnDimension($columnID)->setWidth(10);	
					}
					$sheet->setSelectedCell('A1');
					$filename=$p_nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
					$writer = new Xlsx($spreadsheet);
					header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
					header('Cache-Control: max-age=0');
					ob_end_clean();
					$writer->save('php://output');	// download file 
					exit();
				}
				
				$data['title'] = $p_nama_laporan;
				$data['content_html'] = $html;
				$this->load->view('LaporanResultView',$data);
			}
			else{
				die($error);
			}
			
		}
	}
	
	private function _CetakTotalStock3F($post)
	{
		// echo json_encode($post);die;
		
		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;
		
		// $tahun = $this->input->post('tahun');
		// $bulan = $this->input->post('bulan');
		$tipeProduk = $this->input->post('tipe_produk');
		$kdGudang = $this->input->post('kd_gudang');
		
		$divisi = $this->input->post('divisi');
		
		$chkBookingStock = (int)$this->input->post('chk_booking_stock_aktif');
		$chkHideStok0 = (int)$this->input->post('chk_hide_stok_0_aktif');
		
		// echo $chkBookingStockAktif; die;
		
		// echo json_encode($post);die;
		
		$data = [
			"api" => "APITES",
			"svr"=>$svr,
			"db" => $db,
			"uid" => SQL_UID,
			"pwd" => SQL_PWD,
			"filter" => array(
				"tipeProduk" => $tipeProduk,
				"kdGudang" => $kdGudang,
				"divisi" => $divisi,
				"chkHideStok0" => $chkHideStok0,
				"chkBookingStock" => $chkBookingStock,
			)
		];
		// die(json_encode($data));

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url.$this->API_BKT."/ReportStock/CetakTotalStock3F",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		
		// die($response);

		if ($response===false) {
			$lanjut = false;
			$err = "API Tujuan OFFLINE";
		} else {
			$res = json_decode($response);
			if ($res->result=="sukses") {
				$lanjut=true;
			} else {
				$lanjut=false;
				$error=$res->error;
			}
		}
		
		if($lanjut==true){
		
			$nama_laporan = 'LAPORAN INVENTORY STOCK';
			
			if($post['submit']=='PREVIEW'){
				$this->excel_flag = 0;
			}
			else{
				$this->excel_flag = 1;
			}
			
			$warna_parent 	= '696969';
			$warna_divisi	= '808080';
			$warna_merk		= 'A9A9A9';
			$warna_group 	= 'C0C0C0';
			$warna_jenis 	= 'D3D3D3';
			
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			$spreadsheet = new Spreadsheet();
			
			$styleArray = [
				'borders' => [
					'allBorders' => [
						'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
					],
				],
			];
			
			$html = '';
			
			$data = array();
			foreach($res->data as $dt){
				$data[$dt->GUDANG][$dt->PARENTDIV][$dt->DIVISI][$dt->MERK][$dt->DIVISION_GROUP][$dt->JNS_BRG][] = $dt;
			}
			// echo json_encode($data);die;
			
			$html .='
				<style>
				.border{border:1px solid #555;}
				.bold{font-weight:bold;}
				.right{text-align:right;}
				.center{text-align:center;}
				.bigXL{font-size:180%;}
				.big{font-size:150%;}
				.italic{font-style:italic;}
				</style>
			';
			
			$i = 0;
			
			//revisi file 'C:\xampp\htdocs\mycompany\application\controllers\vendor\mpdf\mpdf\src\Mpdf.php' line 24909
			
			foreach($data as $gudang => $gudangs){
				$html .='<htmlpageheader name="header_'.$i.'">
						'.date('d-M-Y H:i:s').'
							<div class="bigXL bold center">'.$nama_laporan.'</div>
							<div class="big right">'.$gudang.'</div>
						</htmlpageheader>';
				$html .='<columns column-count="1" vAlign="J" column-gap="10" />';
				$html .='<sethtmlpageheader name="header_'.$i.'" value="on" show-this-page="1" />';
				$html .='<columns column-count="4" vAlign="J" column-gap="5" />';
				$html .='<table width="100%">';
				
				if($this->excel_flag == 1){
					$sheet_name = explode(' ',$gudang);
					$spreadsheet->setActiveSheetIndex($i);
					$sheet = $spreadsheet->getActiveSheet($i);
					$sheet->setTitle($sheet_name[0]);
					$sheet->setCellValue('A1', $nama_laporan);
					$sheet->getStyle('A1')->getFont()->setBold(true);
					$sheet->getStyle('A1')->getFont()->setSize(16);
					$sheet->setCellValue('A2', $gudang);
					$sheet->getStyle('A2')->getFont()->setBold(true);
					$sheet->getStyle('A2')->getFont()->setSize(14);
				}
				
				$currcol = -2;
				foreach($gudangs as $parent => $parents){
					$header_parent = 0;
					foreach($parents as $divisi => $divisis){
						$header_divisi = 0;
						foreach($divisis as $merk => $merks){
							$header_merk = 0;
							foreach($merks as $group => $groups){
								$header_group = 0;
								foreach($groups as $jenis => $jeniss){
									$header_jenis = 0;
									foreach($jeniss as $dt){
										if($header_parent==0){
											$header_parent=1;
											$html .='<tr><td class="border bold" colspan="2">'.$parent.'</td></tr>';
											if($this->excel_flag == 1){
												$currrow=4;
												$currcol += 3;
												$sheet->setCellValueByColumnAndRow($currcol, $currrow, $parent);
												
												$sheet->mergeCells(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow);
												
												$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
												
												$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_parent);
												
												$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->getColor()->setRGB('FFFFFF');
												
												// $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->applyFromArray($styleArray);
												
												$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setSize(14);
												
												$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(30);
												$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol))->setWidth(10);
											}
					
										}
										if($header_divisi==0){
											$header_divisi=1;
											$html .='<tr><td class="bold" colspan="2">DIVISI: '.$divisi.'</td></tr>';
											if($this->excel_flag == 1){
												$currrow++;
												$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DIVISI: '.$divisi);
												$sheet->mergeCells(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow);
												$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
												$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_divisi);
											}
										}
										if($header_merk==0){
											$header_merk=1;
											$html .='<tr><td class="bold" colspan="2">MERK: '.$merk.'</td></tr>';
											if($this->excel_flag == 1){
												$currrow++;
												$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'MERK: '.$merk);
												$sheet->mergeCells(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow);
												$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
												$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_merk);
											}
										}
										if($header_group==0){
											$header_group=1;
											$html .='<tr><td class="bold" colspan="2">DIVISION GROUP: '.$group.'</td></tr>';
											if($this->excel_flag == 1){
												$currrow++;
												$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DIVISION GROUP: '.$group);
												$sheet->mergeCells(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow);
												$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
												$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_group);
											}
										}
										if($header_jenis==0){
											$header_jenis=1;
											$html .='<tr><td class="bold" colspan="2">'.$jenis.'</td></tr>';
											if($this->excel_flag == 1){
												$currrow++;
												$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jenis);
												$sheet->mergeCells(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow.':'.PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow);
												$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
												$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_jenis);
											}
										}
										if($dt->AKTIF=='N' && ($dt->STOCK)==0){
											//Kode Barang tidak aktif akan hilang dari laporan ini jika Stocknya 0 (Nol)
										}
										else{
											$html .='<tr><td>'.$dt->KD_BRG.' '.(($dt->AKTIF=='N')? '*' : '').'</td><td class="right">'.number_format($dt->STOCK).'</td></tr>';
										
											if($this->excel_flag == 1){
												$currrow++;
												$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->KD_BRG);
												$sheet->setCellValueByColumnAndRow(($currcol+1), $currrow, $dt->STOCK);
												$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');
											}
										}
										
									}
								}
							}
						}
					}
				}
				$html .='</table>';
				
				$i++;
				if($i<count($data)){
					$html .= '<pagebreak />';
					$spreadsheet->createSheet();
				}
			}
		
			// echo $html;
			
			if($this->excel_flag == 1){
				$filename= $nama_laporan.' ['.date('Ymd').']';
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
					'margin_left' => 10,
					'margin_right' => 10,
					'margin_top' => 30,
					'margin_bottom' => 20,
					'margin_header' => 10,
					'margin_footer' => 10,
					'orientation' => 'P'
				));
				$mpdf->defaultfooterline = 1;
				$mpdf->SetFooter('
				<table width="100%">
					<tr>
						<td class="italic">
							Tanda * menunjukkan Kode Barang Sudah Tidak Aktif<br>
							Kode Barang tidak aktif akan hilang dari laporan ini jika Stocknya 0 (Nol)
						</td>
						<td class="right">
							Halaman {PAGENO} / {nbpg}
						</td>
					</tr>
				</table>');
				$mpdf->keepColumns = true;
				$mpdf->WriteHTML($html);
				$mpdf->Output();
			}
		}
		else{
			die($error);
		}
	}

	private function _CetakTotalStock3C($post)
	{
		// echo json_encode($post);die;
		
		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;
		
		$tipeProduk = $this->input->post('tipe_produk');
		$merk = $this->input->post('merk');
		$chkAllProduk = (int)$this->input->post('chkAllProduk');
		$kd_brg = ($this->input->post('kd_brg')) ? $this->input->post('kd_brg') : '';
		$kdGudang = $this->input->post('kd_gudang');
		$TglAwal = $this->input->post('dp1');
		$TglAkhir = $this->input->post('dp2');
		
		if($kdGudang=='ALL'){
			die('Pilih salah satu gudang!');
		}
		
		if($kd_brg!=''){
			$x = explode(' | ',$this->input->post('kd_brg'));
			$kd_brg = $x[0];
		}
		
		$data = [
			"api" => "APITES",
			"svr"=>$svr,
			"db" => $db,
			"uid" => SQL_UID,
			"pwd" => SQL_PWD,
			"filter" => array(
				"tipeProduk" => $tipeProduk,
				"merk" => $merk,
				"chkAllProduk" => $chkAllProduk,
				"kd_brg" => $kd_brg,
				"kdGudang" => $kdGudang,
				"TglAwal" => $TglAwal,
				"TglAkhir" => $TglAkhir
			)
		];
		// die(json_encode($data));

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url.$this->API_BKT."/ReportStock/CetakTotalStock3C",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		
		// die($response);

		if ($response===false) {
			$lanjut = false;
			$err = "API Tujuan OFFLINE";
		} else {
			$res = json_decode($response);
			if ($res->result=="sukses") {
				$lanjut=true;
			} else {
				$lanjut=false;
				$error=$res->error;
			}
		}
		
		if($lanjut==true){
		
			$nama_laporan = 'LAPORAN TRANSAKSI STOCK';
			
			if($post['submit']=='PREVIEW'){
				$this->excel_flag = 0;
			}
			else{
				$this->excel_flag = 1;
			}
			
			$warna_parent 	= '696969';
			$warna_divisi	= '808080';
			$warna_merk		= 'A9A9A9';
			$warna_group 	= 'C0C0C0';
			$warna_jenis 	= 'D3D3D3';
			
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			$spreadsheet = new Spreadsheet();
			
			$border = [
				'borders' => [
					'allBorders' => [
						'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
					],
				],
			];
			$borderOutline = [
				'borders' => [
					'outline' => [
						'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
					],
				],
			];
			
			$html = '';
			
			$data = array();
			foreach($res->data as $dt){
				$data[trim($dt->Kd_Brg)]['NM_BRG'] = $dt->Nm_Brg;
				$data[trim($dt->Kd_Brg)]['STOK_AWAL'] = $dt->Stock_Awal;
				$data[trim($dt->Kd_Brg)]['STOK_AKHIR'] = $dt->Stock_Akhir;
				$data[trim($dt->Kd_Brg)]['data'][] = $dt;
			}
			// echo json_encode($data);die;
			
			$html .='
				<style>
				.border{border:1px solid #555;}
				.bold{font-weight:bold;}
				.right{text-align:right;}
				.center{text-align:center;}
				.bigXL{font-size:180%;}
				.big{font-size:150%;}
				.italic{font-style:italic;}
				.w100{width:100%;}
				</style>
			';
			
			
			$i = 0;
			
			foreach($data as $kd_brg => $details){
				
				$html .='<htmlpageheader name="header_'.$i.'">							
							<div class="big bold center">'.$nama_laporan.'</div>
							<div class="center">Periode '.date('d-M-Y',strtotime($TglAwal)).' s/d '.date('d-M-Y',strtotime($TglAkhir)).'</div>
							
							<table class="w100">
								<tr><td width="80px">Gudang</td><td class>: <b>'.$kdGudang.'</b></td><td width="80px"></td><td width="40px"></td></tr>
								<tr><td>Kode Barang</td><td>: <b>'.$kd_brg.'</b></td><td></td><td></td></tr>
								<tr><td>Nama Barang</td><td>: <b>'.$data[$kd_brg]['NM_BRG'].'</b></b></td><td></td></tr><td></td>
								<tr><td>Saldo Awal</td>	<td>: <b>'.number_format($data[$kd_brg]['STOK_AWAL']).'</td><td>Saldo Akhir :</td>
									<td class="bold right">'.number_format($data[$kd_brg]['STOK_AKHIR']).'</td></tr>
							</table>
							
							<table class="w100 border">
								<tr>
									<td width="80px">Tgl. Trans</td>
									<td width="120px">No. Bukti</td>
									<td>Nama</td>
									<td width="40px" class="right">Jual</td>
									<td width="40px" class="right">Beli</td>
									<td width="40px" class="right">Retur</td>
									<td width="50px" class="right">M_Terima</td>
									<td width="50px" class="right">M_Keluar</td>
									<td width="40px" class="right">Terima</td>
									<td width="40px" class="right">Keluar</td>
									<td width="40px" class="right">Booking</td>
								</tr>
							</table>
						</htmlpageheader>';
				$html .='<sethtmlpageheader name="header_'.$i.'" value="on" show-this-page="1" />';
				$html .='<table class="w100 border">';
				
		
			
				if($this->excel_flag == 1){
					$spreadsheet->setActiveSheetIndex($i);
					$sheet = $spreadsheet->getActiveSheet($i);
					$sheet->setTitle(substr(trim($kd_brg),0,31));
					$sheet->setCellValue('A1', $nama_laporan);
					$sheet->getStyle('A1')->getFont()->setBold(true);
					$sheet->getStyle('A1')->getFont()->setSize(16);
					$sheet->setCellValue('A2', 'Periode '.date('d-M-Y',strtotime($TglAwal)).' s/d '.date('d-M-Y',strtotime($TglAkhir)));
					$sheet->getStyle('A2')->getFont()->setSize(12);
			
			
					$sheet->getColumnDimension('A')->setWidth(12);
					$sheet->getColumnDimension('B')->setWidth(25);
					$sheet->getColumnDimension('C')->setWidth(40);
					$sheet->getColumnDimension('D')->setWidth(10);
					$sheet->getColumnDimension('E')->setWidth(10);
					$sheet->getColumnDimension('F')->setWidth(10);
					$sheet->getColumnDimension('G')->setWidth(10);
					$sheet->getColumnDimension('H')->setWidth(10);
					$sheet->getColumnDimension('I')->setWidth(10);
					$sheet->getColumnDimension('J')->setWidth(10);
					$sheet->getColumnDimension('K')->setWidth(10);
				
					$sheet->setCellValue('A3', 'Gudang');
					$sheet->setCellValue('B3', $kdGudang);
					$sheet->setCellValue('A4', 'Kode Barang');
					$sheet->setCellValue('B4', $kd_brg);
					$sheet->setCellValue('A5', 'Nama Barang');
					$sheet->setCellValue('B5', $data[$kd_brg]['NM_BRG']);
					$sheet->setCellValue('A6', 'Saldo Awal');
					$sheet->setCellValue('B6', $data[$kd_brg]['STOK_AWAL']);
					$sheet->setCellValue('J6', 'Saldo Akhir');
					$sheet->setCellValue('K6', $data[$kd_brg]['STOK_AKHIR']);
					$sheet->getStyle('B3:B6')->getFont()->setBold(true);
					$sheet->getStyle('K6')->getFont()->setBold(true);
					$sheet->getStyle('B6')->getAlignment()->setHorizontal($alignment_left);
					$sheet->getStyle('B6')->getNumberFormat()->setFormatCode('#,##0');
					$sheet->getStyle('K6')->getNumberFormat()->setFormatCode('#,##0');
					
					$sheet->setCellValue('A7', 'Tgl. Trans');
					$sheet->setCellValue('B7', 'No. Bukti');
					$sheet->setCellValue('C7', 'Nama');
					$sheet->setCellValue('D7', 'Jual');
					$sheet->setCellValue('E7', 'Beli');
					$sheet->setCellValue('F7', 'Retur');
					$sheet->setCellValue('G7', 'M_Terima');
					$sheet->setCellValue('H7', 'M_Keluar');
					$sheet->setCellValue('I7', 'Terima');
					$sheet->setCellValue('J7', 'Keluar');
					$sheet->setCellValue('K7', 'Booking');
					
					$sheet->getStyle('A7:K7')->applyFromArray($borderOutline);
				}
				
				$currcol = 0;
				$currrow = 7;
				
				$total_jual = 0;
				$total_beli = 0;
				$total_retur = 0;
				$total_mutasi_terima = 0;
				$total_mutasi_keluar = 0;
				$total_terima = 0;
				$total_keluar = 0;
				$total_booking = 0;
				foreach($details['data'] as $dt){
					$html .='
					<tr>
					<td width="80px">'.date('d-M-Y', strtotime($dt->Tgl_Trans)).'</td>
					<td width="120px">'.$dt->No_Bukti.'</td>
					<td>'.(($dt->Nm_Plg=='') ? $dt->Nm_Supl : $dt->Nm_Plg).'</td>
					<td width="40px" class="right">'.number_format($dt->Jual).'</td>
					<td width="40px" class="right">'.number_format($dt->Beli).'</td>
					<td width="40px" class="right">'.number_format($dt->Retur).'</td>
					<td width="50px" class="right">'.number_format($dt->Mutasi_Terima).'</td>
					<td width="50px" class="right">'.number_format($dt->Mutasi_Keluar).'</td>
					<td width="40px" class="right">'.number_format($dt->Terima).'</td>
					<td width="40px" class="right">'.number_format($dt->Keluar).'</td>
					<td width="40px" class="right">'.number_format($dt->Booking).'</td>
					</tr>';
				
					if($this->excel_flag == 1){
						$currcol = 0;
						$currcol++;
						$currrow++;
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, date('d-M-Y', strtotime($dt->Tgl_Trans)));
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $dt->No_Bukti);
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, (($dt->Nm_Plg=='') ? $dt->Nm_Supl : $dt->Nm_Plg));
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $dt->Jual);
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $dt->Beli);
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $dt->Retur);
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $dt->Mutasi_Terima);
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $dt->Mutasi_Keluar);
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $dt->Terima);
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $dt->Keluar);
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $dt->Booking);
					}
						
					$total_jual += ($dt->Jual);
					$total_beli += ($dt->Beli);
					$total_retur += ($dt->Retur);
					$total_mutasi_terima += ($dt->Mutasi_Terima);
					$total_mutasi_keluar += ($dt->Mutasi_Keluar);
					$total_terima += ($dt->Terima);
					$total_keluar += ($dt->Keluar);
					$total_booking += ($dt->Booking);
				}
				
				if($this->excel_flag == 1){
					$sheet->getStyle('A7:K'.$currrow)->applyFromArray($borderOutline);
				}	
					
				$html .='</table>';
				$html .='<table class="w100">';
				$html .='
				<tr>
				<td width="80px"></td>
				<td width="120px"></td>
				<td class="bold right">Total</td>
				<td width="40px" class="bold right">'.number_format($total_jual).'</td>
				<td width="40px" class="bold right">'.number_format($total_beli).'</td>
				<td width="40px" class="bold right">'.number_format($total_retur).'</td>
				<td width="50px" class="bold right">'.number_format($total_mutasi_terima).'</td>
				<td width="50px" class="bold right">'.number_format($total_mutasi_keluar).'</td>
				<td width="40px" class="bold right">'.number_format($total_terima).'</td>
				<td width="40px" class="bold right">'.number_format($total_keluar).'</td>
				<td width="40px" class="bold right">'.number_format($total_booking).'</td>
				</tr>';
				$html .='</table>';
				
				if($this->excel_flag == 1){
					$currcol = 0;
					$currcol++;
					$currrow++;
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, '');
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, '');
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'Total');
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_jual);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_beli);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_retur);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_mutasi_terima);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_mutasi_keluar);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_terima);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_keluar);
					$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_booking);
					$sheet->getStyle('A'.$currrow.':K'.$currrow)->getFont()->setBold(true);
					$sheet->getStyle('D8:K'.$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$sheet->setSelectedCell('A1');
				}
				
				$i++;
				if($i<count($data)){
					$html .= '<pagebreak />';
					$spreadsheet->createSheet();
				}
			}
			// echo $html;
			
			if($this->excel_flag == 1){
				$filename= $nama_laporan.' ['.$kdGudang.']['.date('Ymd').']';
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
					'margin_left' => 10,
					'margin_right' => 10,
					'margin_top' => 43,
					'margin_bottom' => 20,
					'margin_header' => 10,
					'margin_footer' => 10,
					'orientation' => 'P'
				));
				$mpdf->defaultfooterline = 1;
				$mpdf->SetFooter('
				<table width="100%">
					<tr>
						<td>
							'.date('d-M-Y H:i:s').'
						</td>
						<td class="right">
							Halaman {PAGENO} / {nbpg}
						</td>
					</tr>
				</table>');
				// $mpdf->keepColumns = true;
				$mpdf->WriteHTML($html);
				$mpdf->Output();
			}
		}
		else{
			die($error);
		}
	}

	public function GetBarangList()
	{
		$merk = $this->input->get('merk');
		$barang = json_decode(file_get_contents($this->API_URL."/index.php/MsBarang/GetBarangListGET?api=APITES"), true);
		// echo json_encode($barang);die;
		if ($barang["result"]=="sukses") {
			$barangpermerk = array();
				foreach($barang["data"] as $d){
					if($d['MERK']==$merk || $merk='ALL'){
						array_push($barangpermerk, $d['KD_BRG'].' | '.$d['NM_BRG']);
					}
				}
				echo json_encode( $barangpermerk);
		} else {
				echo json_encode(array());
		}
	}
	public function GetSparepartList()
	{
		$merk = $this->input->get('merk');
		$barang = json_decode(file_get_contents($this->API_URL."/index.php/MsBarang/GetSparepartList?api=APITES"), true);
		// echo json_encode($barang);die;
		if ($barang["result"]=="sukses") {
			$barangpermerk = array();
				foreach($barang["data"] as $d){
					if($d['MERK']==$merk || $merk='ALL'){
						array_push($barangpermerk, $d['KD_SPAREPART'].' | '.$d['NM_SPAREPART']);
					}
				}
				echo json_encode( $barangpermerk);
		} else {
				echo json_encode(array());
		}
	}


	
	public function ReportStockQuickCount($error=''){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		if($_SESSION['can_read']==true){

			//ini_set('max_execution_time', '300');
			ini_set("pcre.backtrack_limit", "5000000");
			$param=array("tgl"=>"ALL","merk"=>"ALL","jnsbrg"=>"ALL","kdbrg"=>"ALL","wil"=>"ALL","divisi"=>"ALL");

			$data['opt']			= 'Report Stock Quick Count';

			// $data['Divisi']			=json_decode(file_get_contents($this->API_URL."/StockQuickCount/GetListDivisiIN?api=APITES"),true);
			$url = $this->API_URL."/StockQuickCount/GetListDivisi?api=APITES";
			$master = json_decode($this->callAPI($url, $param), true);
			$data['Divisi']	= $master["data"];

			// $data['Merk']			= json_decode(file_get_contents($this->API_URL."/StockQuickCount/GetListMerk?api=APITES"),true);
			$url = $this->API_URL."/StockQuickCount/GetListMerk?api=APITES";
			$master = json_decode($this->callAPI($url, $param), true);
			$data['Merk']	= $master["data"];

			// $data['JenisBarang']	= json_decode(file_get_contents($this->API_URL."/StockQuickCount/GetListJenisBarang?api=APITES"),true);
			$url = $this->API_URL."/StockQuickCount/GetListJenisBarang?api=APITES";
			$master = json_decode($this->callAPI($url, $param), true);
			$data['JenisBarang'] = $master["data"];

			// $url = $this->API_URL."/StockQuickCount/GetListKodeBarang?api=APITES";
			// $master = json_decode($this->callAPI($url, $param), true);
			// $data['KodeBarang']		= $master["data"];
			$data["KodeBarang"] = array();

			// if(!empty($error)){
			// 	$error = 'data tidak ditemukan';
			// }
			// $data['error']			=$error;
			$data["error"] = "";

			// $date["Wilayah"] = json_decode(file_get_contents($this->API_URL."/StockQuickCount/GetListWilayah?api=APITES"),true);
			$url = $this->API_URL."/StockQuickCount/GetListWilayah?api=APITES";

			$master = json_decode($this->callAPI($url, $param), true);
			$data['Wilayah']		= $master["data"];

			$this->RenderView('ReportStock',$data);
		}else{
			redirect('dashboard');
		}
	}

	public function callAPI($url, $param) {
		$curl_data = curl_init();
		curl_setopt($curl_data, CURLOPT_URL, $url);
		curl_setopt($curl_data, CURLOPT_POST, 1);
		curl_setopt($curl_data, CURLOPT_POSTFIELDS, $param);
		curl_setopt($curl_data, CURLOPT_RETURNTRANSFER, 1);

		$GetData =  curl_exec($curl_data);
		curl_close($curl_data);
		return $GetData;
	}

	public function ProsesStockQuickCount()
	{

		if(count($_POST)>0){

			$Bulan = array('01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember');

			$alpabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

			$tanggal 		= urldecode(rtrim($this->input->post("tanggal")));
			$merk 			= urldecode(rtrim($this->input->post("merk")));
			$jenisbarang 	= urldecode(rtrim($this->input->post("jenisbarang")));
			$kodebarang 	= urldecode(rtrim($this->input->post("kodebarang")));
			$wilayah 		= urldecode(rtrim($this->input->post("wilayah")));
			$divisi 		= urldecode(rtrim($this->input->post("divisi")));
			$param = array("tgl"=>$tanggal,"merk"=>$merk,"jnsbrg"=>$jenisbarang,"kdbrg"=>$kodebarang,"wil"=>$wilayah,"divisi"=>$divisi);

			// $GetWilayah 	= json_decode(file_get_contents($this->API_URL."/StockQuickCount/GetListWilayah?api=APITES&wil=".urlencode(trim($wilayah))."&tgl=".date('Y-m-d', strtotime($tanggal))),true);
			$url = $this->API_URL."/StockQuickCount/GetListWilayah?api=APITES";
			$master = json_decode($this->callAPI($url, $param), true);
			$DataWilayah		= $master["data"];

			if($wilayah=='ALL'){
				$count = count($DataWilayah)+2;
			}else{
				$count = 3;
			}

			$alpabetline = $alpabet[$count];

			$page_title = 'Report Stock Quick Count';
			
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->mergeCells('B2:'.$alpabetline.'2');
			$sheet->getStyle('B2')->getFont()->setSize(13);
			$sheet->getStyle('B2:'.$alpabetline.'2')->getAlignment()->setHorizontal('center');
			$sheet->setCellValue('B2', 'REKAP STOCK HARIAN CABANG');

			$sheet->mergeCells('B3:'.$alpabetline.'3');
			$sheet->getStyle('B3')->getFont()->setSize(12);
			$sheet->getStyle('B3:'.$alpabetline.'3')->getAlignment()->setHorizontal('center');
			$sheet->setCellValue('B3', 'BULAN '.strtoupper($Bulan[date_format(date_create($tanggal),'m')]).' '.date_format(date_create($tanggal),'Y'));

			$sheet->mergeCells('B4:'.$alpabetline.'4');
			$sheet->getStyle('B4')->getFont()->setSize(11);
			$sheet->getStyle('B4:'.$alpabetline.'4')->getAlignment()->setHorizontal('center');
			$sheet->setCellValue('B4', 'TANGGAL : '.date_format(date_create($tanggal),'d-m-Y'));

			$sheet->mergeCells('B5:'.$alpabetline.'5');
			$sheet->getStyle('B5')->getFont()->setSize(10);
			$sheet->getStyle('B5:'.$alpabetline.'5')->getAlignment()->setHorizontal('center');
			$sheet->setCellValue('B5',  'TANGGAL PRINT : '.date('d-m-Y H-i-s'));;
				

			$i_col=3;
			$i_row=7;
			for ($w=0; $w<count($DataWilayah); $w++) {
				$sheet->setCellValueByColumnAndRow($i_col, $i_row, $DataWilayah[$w]['wilayah']);
				$i_col++;
			}
			$sheet->setCellValueByColumnAndRow($i_col, $i_row, 'TOTAL');

			$url= $this->API_URL."/StockQuickCount/GetListKodeBarang?api=APITES";
			$master = json_decode($this->callAPI($url, $param), true);
			$DataBarang		= $master["data"];

			//Awal Ambil Data TblStockMaster 
			// die(json_encode($data));
			$url= $this->API_URL."/StockQuickCount/ProsesStockQuickCount?api=APITES";
			$GetStock = json_decode($this->callAPI($url, $param), true);
			$DataStock = $GetStock["data"];
			//Akhir Ambil Data TblStockMaster
			$totalAll = 0;
			$totalQty = array();
			for($w=0; $w<count($DataWilayah);$w++) {
				$wil = $DataWilayah[$w]["wilayah"];
				$totalQty[$wil] = 0;
			}

			if($GetStock["code"]=="SUCCESS") {
				$i_col=2;
				$i_row=8;

				for($s=0; $s<count($DataStock); $s++) {
					$total = 0;
					$dataQty = array();
					$stock = $DataStock[$s];

					for($w=0; $w<count($DataWilayah);$w++) {
						$wil = $DataWilayah[$w]["wilayah"];
						if (isset($stock[$wil])==false || $stock[$wil]==null) {
							$qty = 0;
						} else {
							$qty = $stock[$wil];
						}
						$dataQty[$wil] = $qty;
						$totalQty[$wil]+=$qty;
						$total += $qty;
						$totalAll+=$qty;
					}

					if ($total>0) {
						$sheet->setCellValueByColumnAndRow($i_col, $i_row, $stock["kd_brg"]);
						$i_col++;
						for($w=0; $w<count($DataWilayah);$w++) {
							$wil = $DataWilayah[$w]["wilayah"];
							$sheet->setCellValueByColumnAndRow($i_col, $i_row, $dataQty[$wil]);
							$i_col++;
						}
						$sheet->setCellValueByColumnAndRow($i_col, $i_row, $total);
						$i_row++;
						$i_col=2;
					}
				}

				$sheet->setCellValueByColumnAndRow($i_col, $i_row, "TOTAL");
				$i_col++;
				for($w=0; $w<count($DataWilayah);$w++) {
					$wil = $DataWilayah[$w]["wilayah"];
					$sheet->setCellValueByColumnAndRow($i_col, $i_row, $totalQty[$wil]);
					$i_col++;
				}
				$sheet->setCellValueByColumnAndRow($i_col, $i_row, $totalAll);


				$filename=$page_title.'['.date('Ymd').']';
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 
				exit();
			}else{
				redirect('ReportStock/ReportStockQuickCount/nofound');
			}

		}else{
			redirect('ReportStock/ReportStockQuickCount');
		}
	}
	

	public function sync_data() { 
        $wilayah = rtrim($this->input->post('wilayah')); 

        $conn = $this->MsDatabaseModel->get($wilayah);
        if ($conn!=null) {
        	$AlamatWebService = $conn->AlamatWebService;
        	$db = $conn->Database;
        	$server = $conn->Server; 
			$api = 'APITES';
			//$response = $AlamatWebService.$this->API_BKT."/ReportStock/GetStockCountCabang?api=".urlencode($api)."&wilayah=".urlencode($wilayah)."&svr=".urlencode($server)."&db=".urlencode($db);

			$response = file_get_contents($AlamatWebService.$this->API_BKT."/ReportStock/GetStockCountCabang?api=".urlencode($api)."&wilayah=".urlencode($wilayah)."&svr=".urlencode($server)."&db=".urlencode($db));

			$response = json_decode($response,true); 
        }
        else {
            $response = "Alamat WEB Service Belum Disetting";
        }   
        echo $response;
    }
}	