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
$sheet->setCellValue('A'. $row, 'Tanggal = '.date('d M Y'));
if($rekap!=null){
	foreach($rekap as $headerKey => $header){
		$row += 1;
		$sheet->setCellValue('A'. $row, 'Divisi: '.$header[0]['Divisi'].' Merk: '.$header[0]['Merk'].' Jenis Barang: '.$headerKey);
		$row += 1;
		$sheet->setCellValue('A'. $row, 'Kode Barang');
		$sheet->setCellValue('B'. $row, 'Nama Barang');
		$sheet->setCellValue('C'. $row, 'HS Code');
		$sheet->setCellValue('D'. $row, 'Harga Jual');
		$sheet->setCellValue('E'. $row, 'Disc 1');
		$sheet->setCellValue('F'. $row, 'Disc 2');
		$sheet->setCellValue('G'. $row, 'Disc 3');
		$sheet->setCellValue('H'. $row, 'Tgl Ganti Harga');
		$sheet->setCellValue('I'. $row, 'Aktif');
		$sheet->setCellValue('J'. $row, 'User Name');
		$sheet->setCellValue('K'. $row, 'Last Update');
		$sheet->setCellValue('L'. $row, 'Divisi');
		$sheet->setCellValue('M'. $row, 'Tipe Brg');

		$row +=1;
		if($header){
			foreach ($header as $detailKey => $detail) {
			
				$sheet->setCellValue('A'. $row, $detail['Kd_Brg']);
				$sheet->setCellValue('B'. $row, $detail['Nm_Brg']);
				$sheet->setCellValue('C'. $row, $detail['HS_Code']);
				$sheet->setCellValue('D'. $row, $detail['HARGA_JUAL']);
				$sheet->setCellValue('E'. $row, $detail['Disc1']);
				$sheet->setCellValue('F'. $row, $detail['Disc2']);
				$sheet->setCellValue('G'. $row, $detail['Disc3']);
				$sheet->setCellValue('H'. $row, $detail['Tgl_Ganti_Harga2']);
				$sheet->setCellValue('I'. $row, $detail['Aktif']);
				$sheet->setCellValue('J'. $row, $detail['User_Name']);
				$sheet->setCellValue('K'. $row, $detail['LastUpdate']);
				$sheet->setCellValue('L'. $row, $detail['Div']);
				$sheet->setCellValue('M'. $row, $detail['Type_Barang']);
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