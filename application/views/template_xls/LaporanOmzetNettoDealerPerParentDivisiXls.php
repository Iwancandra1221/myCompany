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
$grandGrandTotal2 = 0;
$grandGrandTotal3 = 0;
$grandGrandTotal4 = 0;
$grandGrandTotal5 = 0;
$grandGrandTotal6 = 0;
foreach($reportTmp as $keyTmp => $valueTmp){
	$row+=1;
	$sheet->setCellValue('A'. $row, 'PARTNER TYPE :'.$keyTmp);

	foreach($valueTmp as $keyTmp2 => $valueTmp2){
		$row+=1;
		$sheet->setCellValue('A'. $row, 'WILAYAH :'.$keyTmp2);
		
		$granTotalCol2 = 0;
		$granTotalCol3 = 0;
		$granTotalCol4 = 0;
		$granTotalCol5 = 0;
		$granTotalCol6 = 0;
		foreach($valueTmp2 as $keyParentDiv => $valueParentDiv){
			$tbody = "";
			$totalCol2 = 0;
			$totalCol3 = 0;
			$totalCol4 = 0;
			$totalCol5 = 0;
			$totalCol6 = 0;

			$row += 1;
			$sheet->setCellValue('A'. $row, 'ParentDiv');
			$sheet->setCellValue('B'. $row, 'Total Jual');
			$sheet->setCellValue('C'. $row, 'Total RB');
			$sheet->setCellValue('D'. $row, 'Total RC');
			$sheet->setCellValue('E'. $row, 'Total Disc');
			$sheet->setCellValue('F'. $row, 'Omzet Netto');
			foreach($valueParentDiv as $key => $value){

				$col1 = $value['ParentDiv'];
				$col2 = $value['Total_Jual'];
				$col3 = $value['Total_RB'];
				$col4 = $value['Total_RC'];
				$col5 = $value['Total_Disc'];
				$col6 = $value['Omzet_Netto'];

				$totalCol2 += $col2;
				$totalCol3 += $col3;
				$totalCol4 += $col4;
				$totalCol5 += $col5;
				$totalCol6 += $col6;


				$row +=1;
				$sheet->setCellValue('A'. $row, $col1);
				$sheet->setCellValue('B'. $row, $col2);
				$sheet->setCellValue('C'. $row, $col3);
				$sheet->setCellValue('D'. $row, $col4);
				$sheet->setCellValue('E'. $row, $col5);
				$sheet->setCellValue('F'. $row, $col6);

				$style = $sheet->getStyle('A'.$row.':F'.$row);
				$style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
			}
			$granTotalCol2 += $totalCol2;
			$granTotalCol3 += $totalCol3;
			$granTotalCol4 += $totalCol4;
			$granTotalCol5 += $totalCol5;
			$granTotalCol6 += $totalCol6;

			$row+=1;
			$sheet->setCellValue('A'. $row, $keyParentDiv);
			$sheet->setCellValue('B'. $row, $totalCol2);
			$sheet->setCellValue('C'. $row, $totalCol3);
			$sheet->setCellValue('D'. $row, $totalCol4);
			$sheet->setCellValue('E'. $row, $totalCol5);
			$sheet->setCellValue('F'. $row, $totalCol6);

			$style = $sheet->getStyle('B'.$row.':F'.$row);
			$style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
			
		}
		$grandGrandTotal2 += $granTotalCol2;
		$grandGrandTotal3 += $granTotalCol3;
		$grandGrandTotal4 += $granTotalCol4;
		$grandGrandTotal5 += $granTotalCol5;
		$grandGrandTotal6 += $granTotalCol6;

		// $granTotalCol2 = $granTotalCol2 == 0 ? '0' : number_format($granTotalCol2,2);
		// $granTotalCol3 = $granTotalCol3 == 0 ? '0' : number_format($granTotalCol3,2);
		// $granTotalCol4 = $granTotalCol4 == 0 ? '0' : number_format($granTotalCol4,2);
		// $granTotalCol5 = $granTotalCol5 == 0 ? '0' : number_format($granTotalCol5,2);
		// $granTotalCol6 = $granTotalCol6 == 0 ? '0' : number_format($granTotalCol6,2);
		
		$row+=1;
		$sheet->setCellValue('A'. $row, 'Total PARTNER TYPE :');
		$sheet->setCellValue('B'. $row, $granTotalCol2);
		$sheet->setCellValue('C'. $row, $granTotalCol3);
		$sheet->setCellValue('D'. $row, $granTotalCol4);
		$sheet->setCellValue('E'. $row, $granTotalCol5);
		$sheet->setCellValue('F'. $row, $granTotalCol6);

		$row+=1;
		$sheet->setCellValue('A'. $row, $keyTmp);
		$row+=1;
		$row+=1;
	}
}
// $grandGrandTotal2 = $grandGrandTotal2 == 0 ? '0' : number_format($grandGrandTotal2,2);
// $grandGrandTotal3 = $grandGrandTotal3 == 0 ? '0' : number_format($grandGrandTotal3,2);
// $grandGrandTotal4 = $grandGrandTotal4 == 0 ? '0' : number_format($grandGrandTotal4,2);
// $grandGrandTotal5 = $grandGrandTotal5 == 0 ? '0' : number_format($grandGrandTotal5,2);
// $grandGrandTotal6 = $grandGrandTotal6 == 0 ? '0' : number_format($grandGrandTotal6,2);


$row+=1;
$sheet->setCellValue('A'. $row, 'GRAND TOTAL');
$sheet->setCellValue('B'. $row, $grandGrandTotal2);
$sheet->setCellValue('C'. $row, $grandGrandTotal3);
$sheet->setCellValue('D'. $row, $grandGrandTotal4);
$sheet->setCellValue('E'. $row, $grandGrandTotal5);
$sheet->setCellValue('F'. $row, $grandGrandTotal6);

$row+=1;
$sheet->setCellValue('A'. $row, $keyTmp);
$row+=1;
$row+=1;

$filename = $title.'_'.date('YmdHis'); //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');	// download file 
exit();
?>
