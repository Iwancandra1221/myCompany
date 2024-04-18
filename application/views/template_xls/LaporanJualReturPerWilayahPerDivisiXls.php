<?php
require FCPATH.'application/controllers/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create new spreadsheet object
$spreadsheet = new Spreadsheet();

// Set document properties
$spreadsheet->getProperties()
            ->setCreator("Your Name")
            ->setLastModifiedBy("Your Name")
            ->setTitle("Example Spreadsheet")
            ->setDescription("This is an example spreadsheet generated using PhpSpreadsheet.")
            ->setKeywords("phpspreadsheet excel example");
$row = 0;
$sheet = $spreadsheet->getActiveSheet();

$granTotalCol2 = 0;
$granTotalCol3 = 0;
$granTotalCol4 = 0;
foreach($reportTmp as $keyTmp => $valueTmp){
	$row+=1;
	$sheet->setCellValue('A'. $row, 'WILAYAH : '.$keyTmp);
	$tbody = "";
	$totalCol2 = 0;
	$totalCol3 = 0;
	$totalCol4 = 0;

	$row += 1;
	$sheet->setCellValue('A'. $row, 'DIVISI');
	$sheet->setCellValue('B'. $row, 'Total Jual');
	$sheet->setCellValue('C'. $row, 'Total Retur');
	$sheet->setCellValue('D'. $row, 'Total');
	foreach($valueTmp as $key => $value){

		$col1 = $value['DIVISI'];
		$col2 = $value['TOTAL_JUAL'];
		$col3 = $value['TOTAL_RETUR'];
		$col4 = $value['TOTAL'];

		$totalCol2 += $col2;
		$totalCol3 += $col3;
		$totalCol4 += $col4;

		$row +=1;
		$sheet->setCellValue('A'. $row, $col1);
		$sheet->setCellValue('B'. $row, $col2);
		$sheet->setCellValue('C'. $row, $col3);
		$sheet->setCellValue('D'. $row, $col4);

		$style = $sheet->getStyle('B'.$row.':D'.$row);
		$style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
	}
	$granTotalCol2 += $totalCol2;
	$granTotalCol3 += $totalCol3;
	$granTotalCol4 += $totalCol4;

	$row+=1;
	$sheet->setCellValue('A'. $row, $keyTmp);
	$sheet->setCellValue('B'. $row, $totalCol2);
	$sheet->setCellValue('C'. $row, $totalCol3);
	$sheet->setCellValue('D'. $row, $totalCol4);

	$style = $sheet->getStyle('B'.$row.':D'.$row);
	$style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
}

$row+=1;
$sheet->setCellValue('A'. $row, 'Grand Total');
$sheet->setCellValue('B'. $row, $granTotalCol2);
$sheet->setCellValue('C'. $row, $granTotalCol3);
$sheet->setCellValue('D'. $row, $granTotalCol4);

$filename = $title.'_'.date('YmdHis'); //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');	// download file 
exit();
?>
