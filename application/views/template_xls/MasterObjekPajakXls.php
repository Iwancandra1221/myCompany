<?php
require FCPATH.'application/controllers/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
$sheet = $spreadsheet->getActiveSheet(0);
$sheet->setTitle('Sheet 1');
$sheet->setCellValue('A1', 'Kode Objek Pajak');
$sheet->setCellValue('B1', 'Nama Objek Pajak');
$sheet->setCellValue('C1', 'PPH Pasal');
$sheet->setCellValue('D1', 'Status');

$horizontal_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
$vertical_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;

$sheet->getStyle("A1:D1")->getAlignment()->setHorizontal($horizontal_center);
$sheet->getStyle("A1:D1")->getAlignment()->setVertical($vertical_center);

// Mengatur warna latar belakang sel A1
$cellStyle = $sheet->getStyle('A1:D1');
$cellStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
$cellStyle->getFill()->getStartColor()->setARGB('FFDDDDDD'); 

$row = 2;
foreach($report as $value){
	$isActive = rtrim($value['is_active'],' ');
	$sheet->setCellValue('A'.$row, rtrim($value['kode_objek_pajak'],' '));
	$sheet->setCellValue('B'.$row, rtrim($value['nama_objek_pajak'],' '));
	$sheet->setCellValue('C'.$row, rtrim($value['pasal_pph'],' '));
	$sheet->setCellValue('D'.$row, $isActive == 1 ? 'aktif' : 'tidak aktif' );
	
	$row+=1;
}

// Mengatur border dari A1 hingga D4
$styleArray = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        ],
    ],
];

$sheet->getStyle('A1:D'.$row)->applyFromArray($styleArray);

$filename='draf 510. tagihan_rev_'.date('YmdHis').''; //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');	// download file 
exit();
?>