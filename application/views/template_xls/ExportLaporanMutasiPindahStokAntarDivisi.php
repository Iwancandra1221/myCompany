<?php
require FCPATH.'application/controllers/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
$sheet = $spreadsheet->getActiveSheet(0);
$sheet->setTitle('Sheet 1');
$sheet->setCellValue('A1', 'PT BHAKTI IDOLA TAMA');
$sheet->setCellValue('A2', 'Laporan Mutasi Pindah Stock Dalam 1 Lokasi');
$sheet->setCellValue('I1', 'Tanggal');
$sheet->setCellValue('I2', 'Cabang');

$sheet->setCellValue('A3', 'Lokasi WH');
$sheet->setCellValue('B3', 'Gudang Sumber');
$sheet->setCellValue('E3', 'Gudang Target');
$sheet->setCellValue('H3', 'Item Yang Dimutasi');
$sheet->setCellValue('K3', 'Ket');

$sheet->setCellValue('B4', 'Kode Gudang');
$sheet->setCellValue('C4', 'Nama Gudang');
$sheet->setCellValue('D4', 'No Mutasi "K"');
$sheet->setCellValue('E4', 'Kode Gudang');
$sheet->setCellValue('F4', 'Nama Gudang');
$sheet->setCellValue('G4', 'No Mutasi "K"');
$sheet->setCellValue('H4', 'Kode Produk');
$sheet->setCellValue('I4', 'Nama Produk');
$sheet->setCellValue('J4', 'QTY');

$sheet->mergeCells("A3:A4");
$sheet->mergeCells("B3:D3");
$sheet->mergeCells("E3:G3");
$sheet->mergeCells("E3:G3");
$sheet->mergeCells("H3:J3");
$sheet->mergeCells("K3:K4");

$horizontal_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
$vertical_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;

$sheet->getStyle("A3:K4")->getAlignment()->setHorizontal($horizontal_center);
$sheet->getStyle("A3:K4")->getAlignment()->setVertical($vertical_center);
$row = 5;
foreach($report as $value){
	$sheet->setCellValue('A'.$row, rtrim($value['Kd_LokasiWH'],' '));
	$sheet->setCellValue('B'.$row, rtrim($value['Gudang_Sumber'],' '));
	$sheet->setCellValue('C'.$row, rtrim($value['Nm_Gudang_Sumber'],' '));
	$sheet->setCellValue('D'.$row, rtrim($value['No_Mutasi'],' '));
	$sheet->setCellValue('E'.$row, rtrim($value['Gudang_Target'],' '));
	$sheet->setCellValue('F'.$row, rtrim($value['Nm_GUdang_Target'],' '));
	$sheet->setCellValue('G'.$row, rtrim($value['No_Ref'],' '));
	$sheet->setCellValue('H'.$row, rtrim($value['Kd_Brg'],' '));
	$sheet->setCellValue('I'.$row, rtrim($value['NM_BRG'],' '));
	$sheet->setCellValue('J'.$row, rtrim($value['Qty'],' '));
	$sheet->setCellValue('K'.$row, rtrim($value['Ket'],' '));
	$row+=1;
}

$filename='Laporan_Mutasi_Pindah_Stock_Antar_Divisi_['.date('YmdHis').']'; //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');	// download file 
exit();
?>