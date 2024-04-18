<?php
require FCPATH.'application/controllers/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
$sheet = $spreadsheet->getActiveSheet(0);

$horizontal_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
$vertical_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;

//$sheet->setTitle('Sheet 1');
$row = 1;
if($report!=null){
	$total_col5 = 0;
	$total_col6 = 0;
	$total_col7 = 0;
	$total_col8 = 0;
	$total_col9 = 0;
	$total_col10 = 0;
	$total_col11 = 0;

	$sheet->setCellValue('A'.$row, ''.date('d-M-Y H:i:s'));
	$row+=1;
	$sheet->setCellValue('A'.$row, 'LAPORAN PEMASUKAN SERVICE');
	$row+=1;
	$sheet->setCellValue('A'.$row,  $tanggal1.': '.$dp11.' S/D '.$dp12);
	$row+=1;
	$row+=1;
	$sheet->setCellValue('A'.$row, '');
	$sheet->setCellValue('B'.$row, 'Tgl Srv');
	$sheet->setCellValue('C'.$row, 'Tgl Trans');
	$sheet->setCellValue('D'.$row, 'No Srv');
	$sheet->setCellValue('E'.$row, 'Ongkos Kerja');
	$sheet->setCellValue('F'.$row, 'PPN');
	$sheet->setCellValue('G'.$row, 'Transport');
	$sheet->setCellValue('H'.$row, 'PPH');
	$sheet->setCellValue('I'.$row, 'Subtotal');
	$sheet->setCellValue('J'.$row, 'Sparepart');
	$sheet->setCellValue('K'.$row, 'Total');
	$sheet->setCellValue('L'.$row, 'Metode Bayar');
	$row+=1;

	foreach($report as $key => $value){	
		$col1 = ($key+=1);
		$col2 = date('d-M-y',strtotime($value->Tgl_Svc));
		$col3 = date('d-M-y',strtotime($value->Tgl_Trans));
		$col4 = $value->No_Svc;
		$col5 = $value->Ongkos_Svc;
		$col6 = $value->PPN;
		$col7 = '0';
		$col8 = $value->PPH;
		$col9 = ($value->Ongkos_Svc + $value->PPN);
		$col10 = $value->grandtotal;
		$col11 = ($col9 + $col10);
		$col12 = ($value->Metode_Bayar);

		$total_col5 += $col5;
		$total_col6 += $col6;
		$total_col7 += $col7;
		$total_col8 += $col8;
		$total_col9 += $col9;
		$total_col10+= $col10;
		$total_col11 += $col11;

		// $col5 =  number_format($col5,0);
		// $col6 = number_format($col6,0);

		// $col9 =  number_format($col9,0);
		// $col10 = number_format($col10,0);
		// $col11 = number_format($col11,0);

		$sheet->setCellValue('A'.$row, $col1);
		$sheet->setCellValue('B'.$row, $col2);
		$sheet->setCellValue('C'.$row, $col3);
		$sheet->setCellValue('D'.$row, $col4);
		$sheet->setCellValue('E'.$row, $col5);
		$sheet->setCellValue('F'.$row, $col6);
		$sheet->setCellValue('G'.$row, $col7);
		$sheet->setCellValue('H'.$row, $col8);
		$sheet->setCellValue('I'.$row, $col9);
		$sheet->setCellValue('J'.$row, $col10);
		$sheet->setCellValue('K'.$row, $col11);
		$sheet->setCellValue('L'.$row, $col12);

		$row+=1;

		
		//$sheet->mergeCells('A'.($row-1).':A'.$row);
		//$sheet->getStyle('A'.($row-1).':A'.$row)->getAlignment()->setWrapText(true);

	}
	$sheet->mergeCells('C'.$row.':D'.$row);
	$sheet->setCellValue('A'.$row, 'Total CASH');
	$sheet->setCellValue('E'.$row, $total_col5);
	$sheet->setCellValue('F'.$row, $total_col6);
	$sheet->setCellValue('G'.$row, $total_col7);
	$sheet->setCellValue('H'.$row, $total_col8);
	$sheet->setCellValue('I'.$row, $total_col9);
	$sheet->setCellValue('J'.$row, $total_col10);
	$sheet->setCellValue('K'.$row, $total_col11);
	//$sheet->getStyle('A5:M'.$row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
	//$sheet->getStyle('A5:M'.$row)->getBorders()->getAllBorders()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK));
}








$filename='LAPORAN_PEMASUKAN_SERVICE_['.date('YmdHis').']'; //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');	// download file 
exit();
?>