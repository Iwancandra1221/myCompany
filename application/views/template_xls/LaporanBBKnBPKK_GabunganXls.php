<?php
require FCPATH.'application/controllers/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
$sheet = $spreadsheet->getActiveSheet(0);
$sheet->setTitle('Rekap Master Produk');
$row = 1;
$sheet->setCellValue('A'. $row, 'Daftar Produk');
$row = 1;
$sheet->setCellValue('A'. $row, 'Periode '.$tgl1.' s/d '.$tgl2);

if($laporan!=null){
	$total = 0;

	$row += 1;
	$sheet->setCellValue('A'. $row, 'NO BUKTI');
	$sheet->setCellValue('B'. $row, 'TGL TRANS');
	$sheet->setCellValue('C'. $row, 'TOTAL');
	$sheet->setCellValue('D'. $row, 'SUPPLIER');
	$sheet->setCellValue('E'. $row, 'BANK');
	// $sheet->setCellValue('F'. $row, 'NO GIRO');
	//$sheet->setCellValue('G'. $row, 'TGL JT');
	$sheet->setCellValue('F'. $row, 'KET');
	
	foreach($laporan as $value){
		
		$row+=1;

		$col1 = rtrim($value['No_bukti'], ' ');
		$col2 = date('d/m/Y',strtotime( rtrim($value['Tgl_trans'], ' ') ));
		$col3 = rtrim($value['Total'], ' ');
		$col4 = rtrim($value['Nm_supl']);
		$col5 = rtrim($value['Bank'], ' ');
		$col6 = rtrim($value['No_giro'], ' ');
		$col7 = date('m/d/Y',strtotime( rtrim($value['Tgl_jatuhTempo'], ' ') ));
		$col8 = rtrim($value['Ket'], ' ').($col6 == '' ? '' :  (' - '.$col6) );

		$sheet->setCellValue('A'. $row, $col1);
		$sheet->setCellValue('B'. $row, $col2);
		$sheet->setCellValue('C'. $row, $col3);
		$sheet->setCellValue('D'. $row, $col4);
		$sheet->setCellValue('E'. $row, $col5);
		// $sheet->setCellValue('F'. $row, $col6);
		//$sheet->setCellValue('G'. $row, $col7);
		$sheet->setCellValue('F'. $row, $col8);

		$total += rtrim($value['Total'], ' ');
	}

	$row+=1;
	$sheet->setCellValue('B'. $row, 'Total');
	$sheet->setCellValue('C'. $row, $total);
}


$filename=$title.'['.date('YmdHis').']'; //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');	// download file 
exit();