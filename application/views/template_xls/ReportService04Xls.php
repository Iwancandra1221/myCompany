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
	$sheet->setCellValue('A'.$row, 'LAPORAN SERVICE BY TEKNISI');
	$row+=1;
	$sheet->setCellValue('A'.$row,  $tanggal1.': '.$dp11.' S/D '.$dp12);
	$row+=1;
	$row+=1;
	
	foreach($report as $key => $report_2){

		$kd_teknisi = '';
		$total_service = 0;

		$sheet->setCellValue('A'.$row, 'Nama Teknisi: '.$key);
		$row+=1;
		$sheet->setCellValue('A'.$row, 'Tgl Masuk');
		$sheet->setCellValue('B'.$row, 'Tgl Trans');
		$sheet->setCellValue('C'.$row, 'No Svr');
		$sheet->setCellValue('D'.$row, 'Kode Barang');
		$sheet->setCellValue('E'.$row, 'Tgl Selesai');
		$sheet->setCellValue('F'.$row, 'Pebaikan');
		$row+=1;
	
		foreach($report_2 as $key_2 => $value_2){
			$kd_teknisi = $value_2->Kd_Teknisi;
			$total_service+=1;
			$col1 = ''.date('d M Y',strtotime($value_2->tgl_svc));
			$col2 = ''.date('d M Y',strtotime($value_2->Tgl_Trans));
			$col3 = ''.$value_2->no_svc;
			$col4 = ''.$value_2->Kd_Brg;
			$col5 = ''.date('d M Y',strtotime($value_2->Tgl_Selesai));
			$col6 = ''.$value_2->Perbaikan;

			$sheet->setCellValue('A'.$row, $col1);
			$sheet->setCellValue('B'.$row, $col2);
			$sheet->setCellValue('C'.$row, $col3);
			$sheet->setCellValue('D'.$row, $col4);
			$sheet->setCellValue('E'.$row, $col5);
			$sheet->setCellValue('F'.$row, $col6);

			$row+=1;
		}
		$sheet->setCellValue('D'.$row, $kd_teknisi);
		$sheet->setCellValue('E'.$row, $key);
		$sheet->setCellValue('F'.$row, 'Total Banyak Service : '.$total_service);

		$row+=1;
		$row+=1;

	}
	// $sheet->mergeCells('C'.$row.':D'.$row);

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