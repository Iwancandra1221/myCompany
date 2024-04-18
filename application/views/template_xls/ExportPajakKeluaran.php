<?php
require FCPATH.'application/controllers/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
$sheet = $spreadsheet->getActiveSheet(0);
$sheet->setTitle('Data Pajak E Faktur');

$row = 1;
$sheet->setCellValue('A'. $row, 'fk');
$sheet->setCellValue('B'. $row, 'kd jenis transaksi');
$sheet->setCellValue('C'. $row, 'fg pengganti');
$sheet->setCellValue('D'. $row, 'nomor faktur');
$sheet->setCellValue('E'. $row, 'masa pajak');
$sheet->setCellValue('F'. $row, 'tahun pajak');
$sheet->setCellValue('G'. $row, 'tanggal faktur');
$sheet->setCellValue('H'. $row, 'npwp');
$sheet->setCellValue('I'. $row, 'nama');
$sheet->setCellValue('J'. $row, 'alamat lengkap');
$sheet->setCellValue('K'. $row, 'jumlah dpp');
$sheet->setCellValue('L'. $row, 'jumlah ppn');
$sheet->setCellValue('M'. $row, 'jumlah ppnbm');
$sheet->setCellValue('N'. $row, 'id keterangan tambahan');
$sheet->setCellValue('O'. $row, 'fg uang muka');
$sheet->setCellValue('P'. $row, 'uang muka dpp');
$sheet->setCellValue('Q'. $row, 'uang muka ppn');
$sheet->setCellValue('R'. $row, 'uang muka ppnbm');
$sheet->setCellValue('S'. $row, 'referensi');
$sheet->setCellValue('T'. $row, 'branch');
$sheet->setCellValue('U'. $row, 'field tambahan 1');
$sheet->setCellValue('V'. $row, 'field tambahan 2');
$sheet->setCellValue('W'. $row, 'field tambahan 3');
$sheet->setCellValue('X'. $row, 'field tambahan 4');
$sheet->setCellValue('Y'. $row, 'field tambahan 5');

$row += 1;

$sheet->setCellValue('A'. $row, 'of');
$sheet->setCellValue('B'. $row, 'kode objek');
$sheet->setCellValue('C'. $row, 'nama');
$sheet->setCellValue('D'. $row, 'harga satuan');
$sheet->setCellValue('E'. $row, 'jumlah barang');
$sheet->setCellValue('F'. $row, 'harga total');
$sheet->setCellValue('G'. $row, 'diskon');
$sheet->setCellValue('H'. $row, 'dpp');
$sheet->setCellValue('I'. $row, 'ppn');
$sheet->setCellValue('J'. $row, 'tarif_ppnbm');
$sheet->setCellValue('K'. $row, 'ppnbm');

$row +=1;
if($resultArray){
	foreach ($resultArray as $headerKey => $header) {
	
		$sheet->setCellValue('A'. $row, $header['fk']);
		$sheet->setCellValue('B'. $row, $header['kd_jenis_transaksi']);
		$sheet->setCellValue('C'. $row, $header['fg_pengganti']);
		$sheet->setCellValue('D'. $row, $header['nomor_faktur']);
		$sheet->setCellValue('E'. $row, $header['masa_pajak']);
		$sheet->setCellValue('F'. $row, $header['tahun_pajak']);
		$sheet->setCellValue('G'. $row, $header['tanggal_faktur']);
		$sheet->setCellValue('H'. $row, $header['npwp']);
		$sheet->setCellValue('I'. $row, $header['nama']);
		$sheet->setCellValue('J'. $row, $header['alamat_lengkap']);
		$sheet->setCellValue('K'. $row, $header['jumlah_dpp']);
		$sheet->setCellValue('L'. $row, $header['jumlah_ppn']);
		$sheet->setCellValue('M'. $row, $header['jumlah_ppnbm']);
		$sheet->setCellValue('N'. $row, $header['id_keterangan_tambahan']);
		$sheet->setCellValue('O'. $row, $header['fg_uang_muka']);
		$sheet->setCellValue('P'. $row, $header['uang_muka_dpp']);
		$sheet->setCellValue('Q'. $row, $header['uang_muka_ppn']);
		$sheet->setCellValue('R'. $row, $header['uang_muka_ppnbm']);
		$sheet->setCellValue('S'. $row, $header['referensi']);
		$sheet->setCellValue('T'. $row, $header['branch']);
		$sheet->setCellValue('U'. $row, $header['field_tambahan_1']);
		$sheet->setCellValue('V'. $row, $header['field_tambahan_2']);
		$sheet->setCellValue('W'. $row, $header['field_tambahan_3']);
		$sheet->setCellValue('X'. $row, $header['field_tambahan_4']);
		$sheet->setCellValue('Y'. $row, $header['field_tambahan_5']);
		$row+=1;
		foreach($header['detail'] as $detailKey => $detail){
			$sheet->setCellValue('A'. $row, $detail['of']);
			$sheet->setCellValue('B'. $row, $detail['kode_objek']);
			$sheet->setCellValue('C'. $row, $detail['nama']);
			$sheet->setCellValue('D'. $row, $detail['harga_satuan']);
			$sheet->setCellValue('E'. $row, $detail['jumlah_barang']);
			$sheet->setCellValue('F'. $row, $detail['harga_total']);
			$sheet->setCellValue('G'. $row, $detail['diskon']);
			$sheet->setCellValue('H'. $row, $detail['dpp']);
			$sheet->setCellValue('I'. $row, $detail['ppn']);
			$sheet->setCellValue('J'. $row, $detail['tarif_ppnbm']);
			$sheet->setCellValue('K'. $row, $detail['ppnbm']);
			$row+=1;
		}
		$row+=1;
	}
}

$filename='';
$filename=$params["kriteria"]."_".$params["kategori"]."_".date('YmdHis');
// $filename='Data_Pajak_E_Faktur['.date('YmdHis').']'; //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');	// download file 
exit();