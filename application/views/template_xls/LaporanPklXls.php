<?php
require FCPATH.'application/controllers/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$currentDate = date('m/d/Y');
$tgl1 = date('d-m-Y',strtotime( rtrim($tgl1, ' ') ));
$tgl2 = date('d-m-Y',strtotime( rtrim($tgl2, ' ') ));

$spreadsheet = new Spreadsheet();
$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
$sheet = $spreadsheet->getActiveSheet(0);

$sheet->mergeCells('A1:F1');
$sheet->mergeCells('A2:F2');
$sheet->mergeCells('A3:F3');

$sheet->getStyle('A1:F3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1:F3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

$sheet->setTitle('Rekap PKL');
$row = 1;
$sheet->setCellValue('A'. $row, 'REKAP PKL');
$row += 1;
$sheet->setCellValue('A'. $row, ''.$nmDealer);
$row += 1;
$sheet->setCellValue('A'. $row, 'Periode '.$tgl1.' s/d '.$tgl2);

if($laporan!='' && $laporan['result']=='SUCCESS'){
	$total = 0;

	$row += 1;
	$sheet->setCellValue('A'. $row, 'Tgl SJ');
	$sheet->setCellValue('B'. $row, 'No SJ');
	$sheet->setCellValue('C'. $row, 'No PBB');
	$sheet->setCellValue('D'. $row, 'No DO');
	$sheet->setCellValue('E'. $row, 'No Faktur');
	$sheet->setCellValue('F'. $row, 'NO PO');
	
	foreach($laporan['data'] as $value){
		
		$row+=1;

		$col1 = date('d M Y',strtotime( rtrim($value['Tgl_Faktur'], ' ') )); 
		$col2 = rtrim($value['No_Faktur'], ' ');
		$col3 = rtrim($value['No_PU'], ' ');
		$col4 = rtrim($value['No_DO']);
		$col5 = rtrim($value['No_Faktur_Baru'], ' ');
		$col6 = rtrim($value['No_PO'], ' ');

		$sheet->setCellValue('A'. $row, $col1);
		$sheet->setCellValue('B'. $row, $col2);
		$sheet->setCellValue('C'. $row, $col3);
		$sheet->setCellValue('D'. $row, $col4);
		$sheet->setCellValue('E'. $row, $col5);
		$sheet->setCellValue('F'. $row, $col6);
	}

	$row+=1;
}


$filename=$title.'['.date('YmdHis').']'; //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');	// download file 
exit();