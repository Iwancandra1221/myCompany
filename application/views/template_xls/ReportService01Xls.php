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
	$sheet->setCellValue('A'.$row, ''.date('d-M-Y H:i:s'));
	$row+=1;
	$sheet->setCellValue('A'.$row, 'LAPORAN BUKU HARIAN SERVICE');
	$row+=1;
	$sheet->setCellValue('A'.$row, $dp11.' S/D '.$dp12);
	$row+=1;
	$row+=1;
	$sheet->setCellValue('A'.$row, '');
	$sheet->setCellValue('B'.$row, 'Tgl Srv');
	$sheet->setCellValue('C'.$row, 'No Srv');
	$sheet->setCellValue('D'.$row, 'Nama Pelanggan');
	$sheet->setCellValue('E'.$row, 'Merk');
	$sheet->setCellValue('F'.$row, 'Kode Brg');
	$sheet->setCellValue('G'.$row, 'No Seri');
	$sheet->setCellValue('H'.$row, 'Selesai');
	$sheet->setCellValue('I'.$row, 'Kembali');
	$sheet->setCellValue('J'.$row, 'Teknisi');
	$sheet->setCellValue('K'.$row, 'Ongkos');
	$sheet->setCellValue('L'.$row, 'Home Svc');
	$sheet->setCellValue('M'.$row, 'PPN');
	$row+=1;
	foreach($report as $key => $value){	
		$col1 = ''.($key+=1);
		$col2 = ''.date('d-M-y',strtotime($value->tgl_svc));
		$col3 = ''.$value->no_svc;
		$col4 = ''.$value->Nm_plg;
		$col5 = ''.$value->merk;
		$col6 = ''.$value->Kd_Brg;
		$col7 = ''.$value->No_Seri;
		$col8 = ''.$value->selesai;
		$col9 = ''.$value->Kembali;
		$col10 = ''.$value->Nm_Teknisi;
		$col11 = ''.$value->Ongkos_Svc;
		$col12 = ''.$value->Home_Svc;
		$col13 = ''.$value->Total_PPN;

		$col14 = ''.$value->type_svc;
		$col15 = ''.$value->Pengaduan;
		$col16 = ''.$value->Perbaikan;

		$sheet->setCellValue('A'.$row, $col1);
		$sheet->setCellValue('B'.$row, $col2." ".$col14);
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
		$sheet->setCellValue('M'.$row, $col13);
		$row+=1;

		$sheet->setCellValue('C'.$row, $col15);//C-E
		$sheet->setCellValue('F'.$row, $col16);//F-I
		$sheet->setCellValue('J'.$row, '');//J-M

		$sheet->mergeCells('A'.($row-1).':A'.$row);
		$sheet->getStyle('A'.($row-1).':A'.$row)->getAlignment()->setWrapText(true);

		$sheet->mergeCells('B'.($row-1).':B'.$row);
		$sheet->getStyle('B'.($row-1).':B'.$row)->getAlignment()->setWrapText(true);

		$sheet->mergeCells('C'.$row.':E'.$row);
		$sheet->mergeCells('F'.$row.':I'.$row);
		$sheet->mergeCells('J'.$row.':M'.$row);
		$row+=1;
		// $sheet->getStyle("A".$row.":H".$row)->getAlignment()->setHorizontal($horizontal_center);
		// $sheet->getStyle("A".$row.":H".$row)->getAlignment()->setVertical($vertical_center);
	}
	$sheet->getStyle('A5:M'.$row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
	$sheet->getStyle('A5:M'.$row)->getBorders()->getAllBorders()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK));
}








$filename='LAPORAN_BUKU_HARIAN_SERVICE_['.date('YmdHis').']'; //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');	// download file 
exit();
?>