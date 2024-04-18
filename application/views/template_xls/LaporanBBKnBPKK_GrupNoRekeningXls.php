<?php
require FCPATH.'application/controllers/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
$sheet = $spreadsheet->getActiveSheet(0);
$sheet->setTitle($title);
$row = 1;
$sheet->setCellValue('A'. $row, $title);
$row += 1;
$sheet->setCellValue('A'. $row, 'Periode '.$tgl1.' s/d '.$tgl2);
$tmpNoRekening = 0;
$total = 0;
//hitung jumlah no rekening yang beda

$iterasi = 0;
if($laporan!=null){
	$row += 1;
	$sheet->setCellValue('A'. $row, 'NO BUKTI');
	$sheet->setCellValue('B'. $row, 'TGL TRANS');
	$sheet->setCellValue('C'. $row, 'TOTAL');
	$sheet->setCellValue('D'. $row, 'SUPPLIER');
	//$sheet->setCellValue('E'. $row, 'BANK');
	//$sheet->setCellValue('F'. $row, 'NO GIRO');
	//$sheet->setCellValue('G'. $row, 'TGL JT');
	$sheet->setCellValue('E'. $row, 'KET');
	
	foreach($laporan as $key => $value){
		
		

		$col0 = 'NO. REKENING : '.rtrim($value['No_Rekening'],' ').' - '.$value['Bank'].' - PT.BHAKTI IDOLA TAMA';
		$col1 = rtrim($value['No_bukti'], ' ');
		$col2 = date('d/m/Y',strtotime( rtrim($value['Tgl_trans'], ' ') ));
		$col3 = rtrim($value['Total'], ' ');
		$col4 = rtrim($value['Nm_supl']);
		$col5 = rtrim($value['Bank'], ' ');
		$col6 = rtrim($value['No_giro'], ' ');
		$col7 = date('m/d/Y',strtotime( rtrim($value['Tgl_jatuhTempo'], ' ') ));
		$col8 = rtrim($value['Ket'], ' ');
		
		//ini disimpen biar tau apakah iterasi sudah berubah
		$tmpIterasi = $iterasi;
		if($tmpNoRekening!=$value['No_Rekening']){
			$tmpNoRekening = $value['No_Rekening'];
			//jika iterasi ke 2 maka sisipin ini diatas no rekening untuk total rekening di atasnya lagi
			if($iterasi>0){
				$total = $total;
				$noRekening_Sebelumnya = $laporan[($key-1)]['No_Rekening'];
				$row+=1;
				$sheet->setCellValue('A'. $row, 'Ttl Rek '.$noRekening_Sebelumnya);
				$sheet->setCellValue('C'. $row, $total);
				$sheet->setCellValue('D'. $row, '');

				$row+=1;
				$sheet->setCellValue('A'. $row, '');

				$total = 0;				
			}

			$row+=1;
			$sheet->setCellValue('A'. $row, $col0);
			$iterasi+=1;
		}

		$total += $value['Total'];
		$row+=1;
		$sheet->setCellValue('A'. $row, $col1);
		$sheet->setCellValue('B'. $row, $col2);
		$sheet->setCellValue('C'. $row, $col3);
		$sheet->setCellValue('D'. $row, $col4);
		//$sheet->setCellValue('E'. $row, $col5);
		//$sheet->setCellValue('F'. $row, $col6);
		//$sheet->setCellValue('G'. $row, $col7);
		$sheet->setCellValue('E'. $row, $col8);
	}
	$total = number_format($total,2);
	$noRekening_Sebelumnya = $laporan[(count($laporan)-1)]['No_Rekening'];
	
	$row+=1;
	$sheet->setCellValue('A'. $row, 'Ttl Rek '.$noRekening_Sebelumnya);
	$sheet->setCellValue('C'. $row, $total);
	$sheet->setCellValue('D'. $row, '');

	$row+=1;
	$sheet->setCellValue('A'. $row, '');
}


$filename=$title.'['.date('YmdHis').']'; //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');	// download file 
exit();