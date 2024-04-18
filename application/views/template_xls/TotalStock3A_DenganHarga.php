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
		$sheet->setCellValue('C'.$row,'Harga');
		$sheet->setCellValue('D'.$row,'S Awal');
		$sheet->setCellValue('E'.$row,'Beli');
		$sheet->setCellValue('F'.$row,'Jual');
		$sheet->setCellValue('G'.$row,'Return');
		$sheet->setCellValue('H'.$row,'Mutasi T');
		$sheet->setCellValue('I'.$row,'Mutasi K');
		$sheet->setCellValue('J'.$row,'Terima');
		$sheet->setCellValue('K'.$row,'Keluar');
		$sheet->setCellValue('L'.$row,'S Akhir');
		$sheet->setCellValue('M'.$row,'Grand Total');

		$row+=1;
		foreach($divisiValue as $merkKey => $merkValue){
			$sheet->setCellValue('A'.$row, $merkKey);//7

			$harga = 0;
			$saldoAwal = 0;
			$beli = 0;
			$jual = 0;
			$retur = 0;
			$mutasiTerima = 0;
			$mutasiKeluar = 0;
			$terima = 0;
			$keluar = 0;
			$saldoAkhir = 0;
			$totalGrandTotal = 0;
			foreach($merkValue as $key => $value){

				$harga += $value['Harga'];
				$saldoAwal += $value['Saldo_Awal'];
				$beli += $value['Beli'];
				$jual += $value['Jual'];
				$retur += $value['Retur'];
				$mutasiTerima += $value['Mutasi_Terima'];
				$mutasiKeluar += $value['Mutasi_Keluar'];
				$terima += $value['Terima'];
				$keluar += $value['Keluar'];
				$saldoAkhir += $value['Saldo_Akhir'];

				$grandTotal = $harga + $saldoAwal + $beli + $jual + $retur + $mutasiTerima + $mutasiKeluar + $terima + $keluar + $saldoAkhir;
				$totalGrandTotal += $grandTotal;

				$row+=1;
				$sheet->setCellValue('A'.$row,$value['Kd_Sparepart']);//8
				$sheet->setCellValue('B'.$row,$value['Nm_Sparepart']);
				$sheet->setCellValue('C'.$row,$value['Harga']);
				$sheet->setCellValue('D'.$row,$value['Saldo_Awal']);
				$sheet->setCellValue('E'.$row,$value['Beli']);
				$sheet->setCellValue('F'.$row,$value['Jual']);
				$sheet->setCellValue('G'.$row,$value['Retur']);
				$sheet->setCellValue('H'.$row,$value['Mutasi_Terima']);
				$sheet->setCellValue('I'.$row,$value['Mutasi_Keluar']);
				$sheet->setCellValue('J'.$row,$value['Terima']);
				$sheet->setCellValue('K'.$row,$value['Keluar']);
				$sheet->setCellValue('L'.$row,$value['Saldo_Akhir']);
				$sheet->setCellValue('M'.$row,$grandTotal);

			}
			$row+=1;
			$harga = '';
			$sheet->setCellValue('A'.$row, 'TOTAL');//9
			$sheet->setCellValue('B'.$row, $merkKey); 
			$sheet->setCellValue('C'.$row, ( $harga == 0 ? '0' : $harga));
			$sheet->setCellValue('D'.$row, ( $saldoAwal == 0 ? '0' : $saldoAwal));
			$sheet->setCellValue('E'.$row, ( $beli == 0 ? '0' : $beli));
			$sheet->setCellValue('F'.$row, ( $jual == 0 ? '0' : $jual));
			$sheet->setCellValue('G'.$row, ( $retur == 0 ? '0' : $retur));
			$sheet->setCellValue('H'.$row, ( $mutasiTerima == 0 ? '0' : $mutasiTerima));
			$sheet->setCellValue('I'.$row, ( $mutasiKeluar == 0 ? '0' : $mutasiKeluar));
			$sheet->setCellValue('J'.$row, ( $terima == 0 ? '0' : $terima));
			$sheet->setCellValue('K'.$row, ($keluar == 0 ? '0' : $keluar));
			$sheet->setCellValue('L'.$row, ($saldoAkhir == 0 ? '0' : $saldoAkhir));
			$sheet->setCellValue('M'.$row, ($totalGrandTotal == 0 ? '0' : $totalGrandTotal));

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