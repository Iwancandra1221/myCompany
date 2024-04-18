<?php
require FCPATH . 'application/controllers/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
$sheet = $spreadsheet->getActiveSheet(0);

$row = 1;
//echo '<pre>';
//echo print_r($rekap);
//echo '</pre>';
$sheet->setCellValue('A' . $row, 'DAFTAR MASTER SALESMAN');
$row += 1;
$sheet->setCellValue('A' . $row, 'Cabang');
$sheet->setCellValue('B' . $row, 'Kode');
$sheet->setCellValue('C' . $row, 'Nama Salesman');
$sheet->setCellValue('D' . $row, 'User ID');
$sheet->setCellValue('E' . $row, 'Level Salesman');
$sheet->setCellValue('F' . $row, 'Atasan');
$sheet->setCellValue('G' . $row, 'Aktif');
$sheet->setCellValue('H' . $row, 'Kode Lokasi');
if ($rekap != null) {
  foreach ($rekap as $key => $report) {
    $row += 1;
    $sheet->setCellValue('A' . $row, rtrim($report['Cabang'], ' '));
    $sheet->setCellValue('B' . $row, rtrim($report['Kd_Slsman'], ' '));
    $sheet->setCellValue('C' . $row, rtrim($report['Nm_Slsman'], ' '));
    $sheet->setCellValue('D' . $row, rtrim($report['UserID'], ' '));
    $sheet->setCellValue('E' . $row, rtrim($report['Nm_Lvl_Slsman'], ' '));
    $sheet->setCellValue('F' . $row, rtrim($report['Nm_Spv'], ' '));
    $sheet->setCellValue('G' . $row, rtrim($report['AKTIF'], ' '));
    $sheet->setCellValue('H' . $row, rtrim($report['Kd_Lokasi'], ' '));
  }
}

$filename = 'RekapMsSalesman[' . date('YmdHis') . ']'; //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');  // download file 
exit();