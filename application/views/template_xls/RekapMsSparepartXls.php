<?php
require FCPATH.'application/controllers/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
$sheet = $spreadsheet->getActiveSheet(0);
$sheet->setTitle('Rekap Master Sparepart');
$row = 1;
$sheet->setCellValue('A'. $row, 'Daftar Sparepart');
$row += 1;
$sheet->setCellValue('A'. $row, 'Tanggal = '.date('d M Y'));
if($rekap!=null){
	foreach($rekap as $headerKey => $header){
		$row += 1;
		$sheet->setCellValue('A'. $row, 'Merk: '.$header[$headerKey][0]['Merk'].' Jenis Sparepart: '.$headerKey);
		$row += 1;
		$sheet->setCellValue('A'. $row, 'Kode Sparepart');
		$sheet->setCellValue('B'. $row, 'Nama Sparepart');
		$sheet->setCellValue('C'. $row, 'Harga Jual');
		$sheet->setCellValue('D'. $row, 'Disc 1');
		$sheet->setCellValue('E'. $row, 'Disc 2');
		$sheet->setCellValue('F'. $row, 'Disc 3');
		$sheet->setCellValue('G'. $row, 'Aktif');
		$sheet->setCellValue('H'. $row, 'User Name');
		$sheet->setCellValue('I'. $row, 'Last Update');

		$row +=1;
		if($header){
			foreach ($header as $detailKey => $detail) {
			
				$sheet->setCellValue('A'. $row, $detail['Kd_sparepart']);
				$sheet->setCellValue('B'. $row, $detail['Nm_sparepart']);
				$sheet->setCellValue('C'. $row, $detail['Harga_Jual']);
				$sheet->setCellValue('D'. $row, $detail['Disc1']);
				$sheet->setCellValue('E'. $row, $detail['Disc2']);
				$sheet->setCellValue('F'. $row, $detail['Disc3']);
				$sheet->setCellValue('G'. $row, $detail['Aktif']);
				$sheet->setCellValue('H'. $row, $detail['User_Name']);
				$sheet->setCellValue('I'. $row, $detail['LastUpdate']);
				$row+=1;
			}
		}
		$sheet->setCellValue('A'. $row, '');
		$row+=1;
	}
}


$filename='RekapMSProduk['.date('YmdHis').']'; //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');	// download file 
exit();