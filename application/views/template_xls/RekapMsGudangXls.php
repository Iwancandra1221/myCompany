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
$rekap = array();
if($rekapTmp!=null){
	foreach($rekapTmp as $value){
		$rekap[$value['Jenis']][] = $value;
	}
	$iterasi = 0;
	$length = count($rekap);
	$sheet->setCellValue('A'.$row, 'DAFTAR MASTER GUDANG');
	$sheet->setCellValue('H'.$row, date('d-M-Y H:i:s'));
	$row+=1;
	foreach($rekap as $key => $report){	
		$row+=1;
		$sheet->setCellValue('A'.$row, 'JENIS : '.$key);
		$row+=1;
		$sheet->setCellValue('A'.$row, 'Kode');
		$sheet->setCellValue('B'.$row, 'Nama Gudang');
		$sheet->setCellValue('C'.$row, 'Alamat');
		$sheet->setCellValue('D'.$row, 'Location');
		$sheet->setCellValue('E'.$row, 'Kategori');
		$sheet->setCellValue('F'.$row, 'Aktif');
		$sheet->setCellValue('G'.$row, 'Tipe Gudang');
		$sheet->setCellValue('H'.$row, 'Kd Lokasi WH');
		$sheet->setCellValue('I'.$row, 'Entry Time');
		$sheet->setCellValue('J'.$row, 'Modified Date');

		// $sheet->getStyle("A".$row.":H".$row)->getAlignment()->setHorizontal($horizontal_center);
		// $sheet->getStyle("A".$row.":H".$row)->getAlignment()->setVertical($vertical_center);

		foreach($report as $value){
			$row+=1;
			$sheet->setCellValue('A'.$row, rtrim($value['Kd_Gudang'],' '));
			$sheet->setCellValue('B'.$row, rtrim($value['Nm_Gudang'],' '));
			$sheet->setCellValue('C'.$row, rtrim($value['Alm_Gudang'],' '));
			$sheet->setCellValue('D'.$row, rtrim($value['location'],' '));
			$sheet->setCellValue('E'.$row, rtrim($value['Kategori'],' '));
			$sheet->setCellValue('F'.$row, rtrim($value['Aktif'],' '));
			$sheet->setCellValue('G'.$row, rtrim($value['TipeGudang'],' '));
			$sheet->setCellValue('H'.$row, rtrim($value['Kd_LokasiWH'],' '));
			$sheet->setCellValue('I'.$row, rtrim($value['Entry_Time'],' '));
			$sheet->setCellValue('J'.$row, rtrim($value['modified_date'],' '));
		}
		$row+=1;
		$iterasi+=1;
	}
}








$filename='RekapMsGudang['.date('YmdHis').']'; //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');	// download file 
exit();
?>