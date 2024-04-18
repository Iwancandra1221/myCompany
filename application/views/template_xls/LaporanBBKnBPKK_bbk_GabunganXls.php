<?php
require FCPATH.'application/controllers/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;

$horizontal_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
$horizontal_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
$horizontal_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;

$vertical_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
$vertical_top = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP;

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

	$sheet->setCellValue('C'. $row, 'NO INVOICE');
	$sheet->setCellValue('D'. $row, 'NO FP/NO REF');

	$sheet->setCellValue('E'. $row, 'TOTAL');

	$sheet->setCellValue('F'. $row, 'SUPPLIER');
	$sheet->setCellValue('G'. $row, 'NPWP');
	$sheet->setCellValue('H'. $row, 'ALAMAT');
	$sheet->setCellValue('I'. $row, 'DPP');
	$sheet->setCellValue('J'. $row, 'PPN');
	$sheet->setCellValue('K'. $row, 'PPH');
	$sheet->setCellValue('L'. $row, 'BANK');
	$sheet->setCellValue('M'. $row, 'KET');
	$sheet->setCellValue('N'. $row, 'NO OP');

	$dataNoBukti = array();
	$noBuktiArray = array_column($laporan, 'No_bukti');
	$noBuktiRowNum = array_count_values($noBuktiArray);
	//[BBK/BKT/2101/0003] => 8

	// $totalArray = array_column($laporan, 'Total', 'No_bukti');
	// log_message('error',print_r($totalArray,true));
	//[BBK/BKT/2101/0003] => 65707325

	$NextMergeRow = $row+1;
	foreach($laporan as $value){
		
		$row+=1;

		$col1 = rtrim($value['No_Bukti_Origin'], ' ');
		$col2 = date('d/m/Y',strtotime( rtrim($value['Tgl_trans'], ' ') ));

		$col3 = rtrim($value['No_Invoice'], ' ');
		$col4 = rtrim($value['No_Ref'], ' ');

		// $col5 = rtrim($value['Total'], ' ');
		$col5 = rtrim($value['Total_Detail_DP'], ' ');

		$col6 = rtrim($value['Nm_supl']);
		$col7 = rtrim($value['NPWP']);
		$col8 = rtrim($value['Alm_Supl']);
		$col9 = rtrim($value['dpp']);
		$col10 = rtrim($value['Ppn']);
		$col11 = rtrim($value['PPh']);

		$col12 = rtrim($value['Bank'], ' ');
		$col13 = rtrim($value['Ket'], ' ');
		$col14 = rtrim($value['No_DP'], ' ');

		// if($row == $NextMergeRow){
		// 	$rowNum = $noBuktiRowNum[$value['No_bukti']];
		// 	$sheet->mergeCells('A'.$row.':A'.($row+$rowNum - 1));
		// 	$sheet->mergeCells('E'.$row.':E'.($row+$rowNum - 1));
		// }

		
		
		$sheet->setCellValue('A'. $row, $col1);
		$sheet->setCellValue('B'. $row, $col2);
		$sheet->setCellValue('C'. $row, $col3);
		$sheet->setCellValue('D'. $row, $col4);

		//fungsi ini agar ketika di merge value ini tidak ada disemua row
		//yang no buktinya sama. agar ketika disum menggunakan formula, tidak ikut kehitung
		// if($row == $NextMergeRow){
		// 	$sheet->setCellValue('E'. $row, $col5);
		// 	$NextMergeRow = ($row+$rowNum);
		// 	$total += rtrim($value['Total_Detail_DP'], ' ');
		// }
		$total += rtrim($value['Total_Detail_DP'], ' ');
		$sheet->setCellValue('E'. $row, $col5);

		$sheet->setCellValue('F'. $row, $col6);
		$sheet->setCellValue('G'. $row, $col7);
		$sheet->setCellValue('H'. $row, $col8);
		$sheet->setCellValue('I'. $row, $col9);
		$sheet->setCellValue('J'. $row, $col10);
		$sheet->setCellValue('K'. $row, $col11);
		$sheet->setCellValue('L'. $row, $col12);
		$sheet->setCellValue('M'. $row, $col13);
		$sheet->setCellValue('N'. $row, $col14);
		
		
		
	}
	// foreach($totalArray as $value){
	// 	$total += $value;
	// }
	$row+=1;
	$sheet->setCellValue('D'. $row, 'Total');
	$sheet->setCellValue('E'. $row, $total);

	$sheet->getStyle('A1:A'.$row)->getAlignment()->setVertical($vertical_center);

	$sheet->getStyle('E1:E'.$row)->getAlignment()->setHorizontal($horizontal_right);
	$sheet->getStyle('E1:E'.$row)->getAlignment()->setVertical($vertical_top);
	$sheet->getStyle('E1:E'.$row)->getNumberFormat()->setFormatCode('#,##0');

	$sheet->getStyle('I1:K'.$row)->getNumberFormat()->setFormatCode('#,##0');
}


$filename=$title.'['.date('YmdHis').']'; //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');	// download file 
exit();