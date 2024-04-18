<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class LaporanDepoPenjualanDanRetur extends MY_Controller 
	{
		public function __construct()
		{
			parent::__construct();
			$this->load->model('HelperModel');
			$this->load->model('GzipDecodeModel');
			$this->load->helper('FormLibrary');
			$this->load->library('excel');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		}
		
		// public function index()
		// {
		// 	$data = array();
		// 	$api = 'APITES';
			
		// 	set_time_limit(60);
			
		// 	$data['title'] = 'Laporan DEPO Penjualan Dan Retur | '.WEBTITLE;
			
		// 	// print_r($data);
		// 	// die;
		// 	$this->RenderView('LaporanDepoPenjualanDanReturView',$data);
		// }
		
		
		public function index()
		{
			// ActivityLog
			$params = array(); 
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN DEPO PENJUALAN & RETUR";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN DEPO PENJUALAN & RETUR";
			$params['Remarks']="";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$data = array();
			$api = 'APITES';
			
			set_time_limit(60);
			
			// $dealers = json_decode(file_get_contents($this->API_URL."/MsDealer/GetListAllDealer?api=".$api));
			
			// $gudangs = json_decode(file_get_contents($this->API_URL."/MsGudang/GetListGudang?api=".$api));	
			$gudangs = file_get_contents($this->API_URL."/MsGudang/GetListGudang?api=".$api);
			$gudangs = $this->GzipDecodeModel->_decodeGzip($gudangs);

			$merks = json_decode(file_get_contents($this->API_URL."/MsBarang/GetMerkList?api=".$api));

			// $wilayahs = json_decode(file_get_contents($this->API_URL."/MsDealer/GetListWilayah?api=".$api));
			$wilayahs = file_get_contents($this->API_URL."/MsDealer/GetListWilayah?api=".$api);
			$wilayahs = $this->GzipDecodeModel->_decodeGzip($wilayahs);

			// $partnertypes = json_decode(file_get_contents($this->API_URL."/MsDealer/GetListPartnerType?api=".$api));
			$partnertypes = file_get_contents($this->API_URL."/MsDealer/GetListPartnerType?api=".$api);
			$partnertypes = $this->GzipDecodeModel->_decodeGzip($partnertypes);

			$data['title'] = 'Laporan DEPO Penjualan Dan Retur | '.WEBTITLE;
			// $data["dealers"] = $dealers;
			$data["gudangs"] = $gudangs;
			$data["merks"] = $merks;
			$data["wilayahs"] = $wilayahs;
			$data["partnertypes"] = $partnertypes;

			// print_r($data);
			// die;

			// ActivityLog SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('LaporanDepoPenjualanDanReturView',$data);
		}
		
		public function Proses()
		{						
			// ActivityLog
			$params = array(); 
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN DEPO PENJUALAN & RETUR";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES LAPORAN DEPO PENJUALAN & RETUR";
			$params['Remarks']="";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$page_title = 'LaporanDEPOPenjualan&Retur';
					
			$dealer=$_POST["dealer"];						
			$gudang=$_POST["gudang"];
			
			if (empty($_POST["chkformatpiutang"]) ){ 
				$_POST["chkformatpiutang"]="N";
			}
			if (empty($_POST["chkperalamatkirim"]) ){ 
				$_POST["chkperalamatkirim"]="N";
			}
			// if (empty($_POST["chkpotongpda"]) ){ 
			// 	$_POST["chkpotongpda"]="N";
			// }
			if (empty($_POST["chkpotongposting"]) ){ 
				$_POST["chkpotongposting"]="N";
			}

			if ($_POST["chkformatpiutang"] == "Y" && $_POST["chkperalamatkirim"] == "Y"){ 
				$opsi="formatpiutang1";
			}
			elseif ($_POST["chkformatpiutang"] == "Y" && $_POST["chkperalamatkirim"] == "N"){ 
				$opsi="formatpiutang2";
			}
			// elseif ($_POST["chkpotongpda"] == "Y"){ 
			// 	$opsi="potongpda";
			// }
			elseif ($_POST["chkpotongposting"] == "Y"){ 
				$opsi="potongposting";
			}
			else{
				$opsi="00";
			}

			// print_r($_POST);
			// die;

			$this->Preview( $page_title, $_POST["dp1"], $_POST["dp2"], $_POST["kategori"], $_POST["jns_trx"],
				$dealer, $_POST["wilayah_khusus"], $gudang, $_POST["merk"], $_POST["wilayah"], $_POST["partner_type"], $opsi,$params );
			
		}
					
		
		public function Preview ( $page_title, $dp1, $dp2, $kategori, $jns_trx,
			$dealer, $wilayah_khusus, $gudang, $merk, $wilayah, $partner_type, $opsi,$params )
		{						
			$api = 'APITES';
									
			set_time_limit(60);

			// print_r($opsi);
			// die;

			$URL = $this->API_URL."/LaporanDepoPenjualanDanRetur/GetLaporanDepoPenjualanDanRetur?api=".$api.
				"&dp1=".urlencode($dp1)."&dp2=".urlencode($dp2)."&kategori=".urlencode($kategori)."&jns_trx=".urlencode($jns_trx).
				"&dealer=".urlencode($dealer)."&wilayah_khusus=".urlencode($wilayah_khusus)."&gudang=".urlencode($gudang).
				"&merk=".urlencode($merk)."&wilayah=".urlencode($wilayah)."&partner_type=".urlencode($partner_type)."&opsi=".urlencode($opsi);
			// die($URL);
			$DataLaporan = json_decode ( file_get_contents($URL) );

			// print_r($DataLaporan);
			//die;
			
			if(empty($DataLaporan)){
				// ActivityLog FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit('Tidak ada data');
			}
			elseif(count($DataLaporan->data) == 0){
				// ActivityLog FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit('Tidak ada data');
			}
			
			if($opsi=="formatpiutang1" || $opsi=="formatpiutang2"){
				$this->PreviewFormatPiutang ($page_title, $DataLaporan, $dp1, $dp2, $kategori, $jns_trx,
											$dealer, $wilayah_khusus, $gudang, $merk, $wilayah, $partner_type, $opsi,$params);
			}
			// elseif ($opsi=="potongpda"){
			// 	$this->PreviewPotongPDA ($page_title, $DataLaporan, $dp1, $dp2, $kategori, $jns_trx,
			// 								$dealer, $wilayah_khusus, $gudang, $merk, $wilayah);
			// }
			elseif ($opsi=="potongposting"){
				$this->PreviewPotongPosting ($page_title, $DataLaporan, $dp1, $dp2, $kategori, $jns_trx,
										$dealer, $wilayah_khusus, $gudang, $merk, $wilayah, $partner_type,$params);
			}
			else {
				$this->Preview00 ($page_title, $DataLaporan, $dp1, $dp2, $kategori, $jns_trx,
										$dealer, $wilayah_khusus, $gudang, $merk, $wilayah, $partner_type,$params);
			}
		}

		public function PreviewFormatPiutang ( $page_title, $DataLaporan, $dp1, $dp2, $kategori, $jns_trx,
										$dealer, $wilayah_khusus, $gudang, $merk, $wilayah, $partner_type, $opsi,$params)
		{				
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', 'LAPORAN Piutang');
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', 'Periode : '.$dp1.' sd '.$dp2);
            $sheet->setCellValue('A3', 'Partner Type : '.$partner_type);
			$sheet->setCellValue('A4', 'Wilayah : '.$wilayah);
								
			$currcol = 1;
			$currrow = 8;
			$colheaderrow = $currrow;
			$colheaderrow2= $currrow+1;

			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Dealer');
			$sheet->getColumnDimension('A')->setWidth(35);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Alm Kirim');
			$sheet->getColumnDimension('B')->setWidth(35);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
			$sheet->getColumnDimension('C')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tanggal Pajak');
			$sheet->getColumnDimension('D')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Faktur');
			$sheet->getColumnDimension('E')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'BBT');
			$sheet->getColumnDimension('F')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Debit');
			$sheet->getColumnDimension('G')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kredit');
			$sheet->getColumnDimension('H')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
			$sheet->getColumnDimension('I')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
											
			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
						
			// print_r($DataLaporan->data);
			// die;

			// Detail
			foreach($DataLaporan->data as $BM) {
				
				$currrow++;
				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->nm_plg);
				$currcol += 1;
				if ($opsi == "formatpiutang1"){
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Nm_Wil);
				}
				else {
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
				}				
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Merk);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("m-d-Y",strtotime($BM->Tgl_Faktur)));
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->No_Faktur);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->GrandTotal);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
				$currcol += 1;
			}
			
			
			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A$colheaderrow:'.$max_col.'$colheaderrow2')->getAlignment()->setHorizontal($alignment_center);
			
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A$colheaderrow:'.$max_col.'$colheaderrow2')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
			// border
			$sheet->getStyle("A1:".$max_col."$colheaderrow2")->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet->getStyle('A$colheaderrow:'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');
			
				
			$filename=$page_title.'['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 

			// ActivityLog SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			exit();
		
		}			

		public function PreviewPotongPDA ( $page_title, $DataLaporan, $dp1, $dp2, $kategori, $jns_trx,
										$dealer, $wilayah_khusus, $gudang, $merk, $wilayah, $partner_type,$params)
		{
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			if ($jns_trx=="J"){
				$sheet->setCellValue('A1', 'LAPORAN BUKU PENJUALAN');
			}
			else {
				$sheet->setCellValue('A1', 'LAPORAN RETUR PENJUALAN');
			}
			
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', 'Periode : '.$dp1.' sd '.$dp2);
            $sheet->setCellValue('A3', 'Gudang : '.$gudang);
            $sheet->setCellValue('A4', 'Partner Type : '.$partner_type);
			$sheet->setCellValue('A5', 'Wilayah : '.$wilayah);
								
			$currcol = 1;
			$currrow = 8;
			$colheaderrow = $currrow;
			$colheaderrow2= $currrow+1;

			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
			$sheet->getColumnDimension('A')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tgl Faktur');
			$sheet->getColumnDimension('B')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Faktur');
			$sheet->getColumnDimension('C')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Faktur Baru');
			$sheet->getColumnDimension('D')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Kelompok');
			$sheet->getColumnDimension('E')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Dealer');
			$sheet->getColumnDimension('F')->setWidth(35);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Barang');
			$sheet->getColumnDimension('G')->setWidth(25);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Qty');
			$sheet->getColumnDimension('H')->setWidth(10);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Harga');
			$sheet->getColumnDimension('I')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Disc');
			$sheet->getColumnDimension('J')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SubTotal');
			$sheet->getColumnDimension('K')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Keterangan');
			$sheet->getColumnDimension('L')->setWidth(35);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Disc Tambahan');
			$sheet->getColumnDimension('M')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DPP');
			$sheet->getColumnDimension('N')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PPN');
			$sheet->getColumnDimension('O')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Faktur');
			$sheet->getColumnDimension('P')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
											
			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
						
			$GTotal = 0;
			$Total = 0;
			$DPP = 0;
			$PPN = 0;
			$DiscTambahan = 0;
			$NoFak = "!@#$%";

			// print_r($DataLaporan->data);
			// die;

			// Detail
			foreach($DataLaporan->data as $BM) {
				
				if ($NoFak != "!@#$%" And $NoFak != $BM->No_Faktur){
					//CetakTotal
					//$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DiscTambahan);		
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');			
					
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DPP);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');

					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $PPN);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');

					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $GTotal);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					
					//$currrow++;

					$GTotal = 0;
					$Total = 0;
					$DPP = 0;
					$PPN = 0;
				}
				else {
					//CetakTotal
					//$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, ' ');					
					
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, ' ');

					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, ' ');

					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, ' ');
					
				}

				$Disc = $BM->Harga - (($BM->Harga * (100-$BM->Disc1)/100 * (100-$BM->Disc2)/100 * (100-$BM->Disc3)/100) - $BM->Disc4);

				IF ($BM->Tipe_PPN != "I"){
					$SubTotal = $BM->Qty * ($BM->Harga - $Disc);
					$Total = $Total + $SubTotal;
					$DiscTambahan = $BM->Disc_Tambahan;
					$DPP = $Total - $DiscTambahan;
					$PPN = $DPP * $BM->PPN / 100;				
					$GTotal = $DPP + $PPN;
				}
				else {
					$SubTotal = $BM->TotalPerItem;
					$Total = $Total + $SubTotal;
					$DiscTambahan = $BM->Disc_Tambahan;
					$GTotal = $Total - $DiscTambahan;
					$DPP = ($GTotal) / (100 + $BM->PPN);
					$PPN = $DPP * $BM->PPN / 100;
				}


				$currrow++;
				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Merk);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("m-d-Y",strtotime($BM->Tgl_Faktur)));							
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->No_Faktur);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->No_Faktur_Baru);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->No_Kelompok);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Nm_Plg);
				$currcol += 1;			
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Kd_Brg);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Qty);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Harga);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $Disc);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SubTotal);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Ket);
				$currcol += 1;

				$NoFak = $BM->No_Faktur;
			}
			
			//CetakTotal
			//$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DiscTambahan);					
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');

			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DPP);
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');

			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $PPN);
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');

			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $GTotal);
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');

			
			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A'.$colheaderrow.':'.$max_col.$colheaderrow2)->getAlignment()->setHorizontal($alignment_center);
			
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A'.$colheaderrow.':'.$max_col.$colheaderrow2)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
			// border
			$sheet->getStyle("A1:".$max_col.$colheaderrow2)->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet->getStyle('A'.$colheaderrow.':'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');
			
				
			$filename=$page_title.'['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 

			// ActivityLog SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			exit();
		}

		public function PreviewPotongPosting ( $page_title, $DataLaporan, $dp1, $dp2, $kategori, $jns_trx,
										$dealer, $wilayah_khusus, $gudang, $merk, $wilayah, $partner_type,$params)
		{
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle($page_title);
			if ($jns_trx=="J"){
				$sheet->setCellValue('A1', 'LAPORAN BUKU PENJUALAN');
			}
			else {
				$sheet->setCellValue('A1', 'LAPORAN RETUR PENJUALAN');
			}
			
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', 'Periode : '.$dp1.' sd '.$dp2);
            $sheet->setCellValue('A3', 'Gudang : '.$gudang);
            $sheet->setCellValue('A4', 'Partner Type : '.$partner_type);
			$sheet->setCellValue('A5', 'Wilayah : '.$wilayah);
								
			$currcol = 1;
			$currrow = 8;
						
			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
			$sheet->getColumnDimension('A')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tgl Faktur');
			$sheet->getColumnDimension('B')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Faktur');
			$sheet->getColumnDimension('C')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Kelompok');
			$sheet->getColumnDimension('D')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Dealer');
			$sheet->getColumnDimension('E')->setWidth(25);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Barang');
			$sheet->getColumnDimension('F')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Qty');
			$sheet->getColumnDimension('G')->setWidth(10);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Harga');
			$sheet->getColumnDimension('H')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Keterangan');
			$sheet->getColumnDimension('I')->setWidth(50);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
											
			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
						
			// print_r (count($DataLaporan->data));
			// die;

			// Detail
			foreach($DataLaporan->data as $BM) {
				
				// print_r ($BM->Merk);
				// die;

				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Merk);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("m-d-Y",strtotime($BM->Tgl_Faktur)));							
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->No_Faktur);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->No_Kelompok);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Nm_Plg);
				$currcol += 1;			
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Kd_Brg);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Qty);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Harga);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Ket);
				$currcol += 1;

				$currrow += 1;
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
			
				
			$filename=$page_title.'['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 

			// ActivityLog SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			
			exit();
		}

		public function Preview00 ( $page_title, $DataLaporan, $dp1, $dp2, $kategori, $jns_trx,
									$dealer, $wilayah_khusus, $gudang, $merk, $wilayah, $partner_type,$params)
		{
			$this->PreviewPotongPDA ($page_title, $DataLaporan, $dp1, $dp2, $kategori, $jns_trx,
									$dealer, $wilayah_khusus, $gudang, $merk, $wilayah, $partner_type, $params);
		}

	}												