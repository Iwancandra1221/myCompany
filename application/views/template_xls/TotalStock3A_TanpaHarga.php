<?php
require FCPATH.'application/controllers/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
$sheet = $spreadsheet->getActiveSheet(0);
$sheet->setTitle('CetakTotalStock3A');
$sheet->setCellValue('A1', 'LAPORAN TOTAL STOCK '.$tipeProdukTmp);
$sheet->setCellValue('A2', 'PERIODE: '.date('d-M-Y',strtotime($tglAwal)).' S/D '.date('d-M-Y',strtotime($tglAkhir)));

$row = 4;
foreach ($getStock as $gudangKey => $gudangValue) {
	$sheet->setCellValue('A'.$row, $gudangKey.' '.$getGudang[$gudangKey]);//4
	$row+=1;
	$row+=1;
	foreach($gudangValue as $divisiKey => $divisiValue){
		$sheet->setCellValue('A'.$row, $divisiKey);//5

		$row+=1;
		$sheet->setCellValue('A'.$row,'Kode Sparepart');//6
		$sheet->setCellValue('B'.$row,'Nama Sparepart');
		$sheet->setCellValue('C'.$row,'S Awal');
		$sheet->setCellValue('D'.$row,'Beli');
		$sheet->setCellValue('E'.$row,'Jual');
		$sheet->setCellValue('F'.$row,'Return');
		$sheet->setCellValue('G'.$row,'Mutasi T');
		$sheet->setCellValue('H'.$row,'Mutasi K');
		$sheet->setCellValue('I'.$row,'Terima');
		$sheet->setCellValue('J'.$row,'Keluar');
		$sheet->setCellValue('K'.$row,'S Akhir');

		$row+=1;
		foreach($divisiValue as $merkKey => $merkValue){
			$sheet->setCellValue('A'.$row, $merkKey);//7

			$saldoAwal = 0;
			$beli = 0;
			$jual = 0;
			$retur = 0;
			$mutasiTerima = 0;
			$mutasiKeluar = 0;
			$terima = 0;
			$keluar = 0;
			$saldoAkhir = 0;
			foreach($merkValue as $key => $value){

				$saldoAwal += $value['Saldo_Awal'];
				$beli += $value['Beli'];
				$jual += $value['Jual'];
				$retur += $value['Retur'];
				$mutasiTerima += $value['Mutasi_Terima'];
				$mutasiKeluar += $value['Mutasi_Keluar'];
				$terima += $value['Terima'];
				$keluar += $value['Keluar'];
				$saldoAkhir += $value['Saldo_Akhir'];

				$row+=1;
				$sheet->setCellValue('A'.$row,$value['Kd_Sparepart']);//8
				$sheet->setCellValue('B'.$row,$value['Nm_Sparepart']);
				$sheet->setCellValue('C'.$row,$value['Saldo_Awal']);
				$sheet->setCellValue('D'.$row,$value['Beli']);
				$sheet->setCellValue('E'.$row,$value['Jual']);
				$sheet->setCellValue('F'.$row,$value['Retur']);
				$sheet->setCellValue('G'.$row,$value['Mutasi_Terima']);
				$sheet->setCellValue('H'.$row,$value['Mutasi_Keluar']);
				$sheet->setCellValue('I'.$row,$value['Terima']);
				$sheet->setCellValue('J'.$row,$value['Keluar']);
				$sheet->setCellValue('K'.$row,$value['Saldo_Akhir']);

			}
			$row+=1;
			$sheet->setCellValue('A'.$row, 'TOTAL');//9
			$sheet->setCellValue('B'.$row, $merkKey); 
			$sheet->setCellValue('C'.$row, ( $saldoAwal == 0 ? '0' : $saldoAwal));
			$sheet->setCellValue('D'.$row, ( $beli == 0 ? '0' : $beli));
			$sheet->setCellValue('E'.$row, ( $jual == 0 ? '0' : $jual));
			$sheet->setCellValue('F'.$row, ( $retur == 0 ? '0' : $retur));
			$sheet->setCellValue('G'.$row, ( $mutasiTerima == 0 ? '0' : $mutasiTerima));
			$sheet->setCellValue('H'.$row, ( $mutasiKeluar == 0 ? '0' : $mutasiKeluar));
			$sheet->setCellValue('I'.$row, ( $terima == 0 ? '0' : $terima));
			$sheet->setCellValue('J'.$row, ($keluar == 0 ? '0' : $keluar));
			$sheet->setCellValue('K'.$row, ($saldoAkhir == 0 ? '0' : $saldoAkhir));

			$row+=1;
		}
		$row+=1;
	}
}

$filename='CetakTotalStock3A_['.date('YmdHis').']'; //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');	// download file 
exit();