<?php
require FCPATH.'application/controllers/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
$spreadsheet = new Spreadsheet();
$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
$sheet = $spreadsheet->getActiveSheet(0);

$row = 1;
//echo '<pre>';
//echo print_r($rekap);
//echo '</pre>';
$sheet->setCellValue('A' . $row, 'DAFTAR MASTER SUPPLIER');
$row += 1;
$sheet->setCellValue('A' . $row, 'Kode');
$sheet->setCellValue('B' . $row, 'Nama Supplier');
$sheet->setCellValue('C' . $row, 'Alamat');
$sheet->setCellValue('D' . $row, 'NPWP');
$sheet->setCellValue('E' . $row, 'Email');
$sheet->setCellValue('F' . $row, 'Telepon');
$sheet->setCellValue('G' . $row, 'Fax');
$sheet->setCellValue('H' . $row, 'Bank');
$sheet->setCellValue('I' . $row, 'Nama Pemilik');
$sheet->setCellValue('J' . $row, 'No Rekening');
$sheet->setCellValue('K' . $row, 'Keterangan');
if ($rekap != null) {
  foreach($rekap as $key => $report){
    $row +=1;
    $sheet->setCellValue('A' . $row, rtrim($report['Kd_Supl'], ' '));
    $sheet->setCellValue('B' . $row, rtrim($report['Nm_Supl'], ' '));
    $sheet->setCellValue('C' . $row, rtrim($report['Alm_Supl'], ' '));
    $sheet->setCellValue('D' . $row, rtrim($report['NPWP'], ' '));
    $sheet->setCellValue('E' . $row, rtrim($report['Email'], ' '));
    $sheet->setCellValue('F' . $row, rtrim($report['Telp'], ' '));
    $sheet->setCellValue('G' . $row, rtrim($report['Fax'], ' '));
    $sheet->setCellValue('H' . $row, rtrim($report['Bank'], ' '));
    $sheet->setCellValue('I' . $row, rtrim($report['Nm_Pemilik'], ' '));
    $sheet->setCellValue('J' . $row, rtrim($report['No_Rekening'], ' '));
    $sheet->setCellValue('K' . $row, rtrim($report['Ket'], ' '));
  }
}
$sheet->getStyle("D2:J".$row)->getNumberFormat()->setFormatCode('0');
$sheet->getStyle("D2:J".$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT); // Rata kanan

$sheet->getStyle("A1:J2")->getFont()->setBold(true);
$sheet->getStyle("A2:J2")->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('D3D3D3');
$sheet->getStyle('A2:J'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

foreach (range('A', 'J') as $col) {
	$sheet->getColumnDimension($col)->setAutoSize(true);
} 

$filename='RekapMsSupplier['.date('YmdHis').']'; //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');	// download file 
exit();
