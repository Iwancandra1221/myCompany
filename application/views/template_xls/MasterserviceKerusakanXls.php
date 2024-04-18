<?php
require FCPATH.'application/controllers/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; 

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet(0);
$sheet->setTitle($title);
$sheet->setCellValue('A1', $title);
$sheet->getStyle('A1')->getFont()->setSize(20);

$currrow = 1;
$currcol = 1;

$sheet->setCellValueByColumnAndRow($currcol, $currrow, $title);
$sheet->mergeCells('A1:E1');
$sheet->getStyle('A1:E1')->getAlignment()->setHorizontal('center');
$currcol = 1;
$currrow = 3;
$spreadsheet->getActiveSheet()->getStyle('A3:E3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('eaeaea');
$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KODE KERUSAKAN');
$currcol ++;
$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA KERUSAKAN');
$currcol ++;
$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'ACTIVE');
$currcol ++;
$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'MODIFIED BY');
$currcol ++;
$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'MODIFIED DATE');
$currcol ++;
$currrow++;

$data = json_decode($rekap, true);
foreach ($data['data'] as $key => $value) {
$value['is_active'] = $value['is_active'] == 1 ? 'Aktif' : 'Tidak Aktif';
$currcol = 1;
// Menggunakan trim pada nilai-nilai yang dimasukkan ke dalam sel Excel
$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($value['kd_kerusakan']));
$currcol++;
$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($value['nm_kerusakan']));
$currcol++;
$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($value['is_active']));
$currcol++;
$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($value['modified_by']));
$currcol++;
$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');
$currcol++;
$currrow++;
}

for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
$sheet->getColumnDimension($i)->setAutoSize(TRUE);
}
$filename= $title.' ['.date('Y-m-d H-i-s').']'; //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"');
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');	// download file
exit();