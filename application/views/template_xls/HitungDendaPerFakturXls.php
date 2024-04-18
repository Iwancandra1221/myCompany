<?php
	require FCPATH.'application/controllers/vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

	// require 'vendor/autoload.php';
	// use PhpOffice\PhpSpreadsheet\Spreadsheet;
	// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
		

	$spreadsheet = new Spreadsheet();
	// $fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
	$sheet = $spreadsheet->getActiveSheet(0);

	$sheet->setTitle('Hitung Denda Per Faktur');
	$sheet->setCellValue('A1', $Judul);
	$sheet->getStyle('A1')->getFont()->setSize(20);
	$sheet->setCellValue('A2', $Salesman);
	$sheet->setCellValue('A3', $Periode);
									
	$currcol = 1;
	$currrow = 5;
	$colheaderrow = $currrow;
	$colheaderrow2= $currrow+1;

	$total_grandTotal_Bln = 0;
	$total_grandTotal = 0;
	$total_tunjKenaDenda = 0;
	$total_tunjAtasFaktur = 0;
	$total_denda = 0;
	$tunjKenaDenda = 0;
	$totaldendaawal = 0;

	// Header
	$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO FAKTUR');
	$sheet->getColumnDimension('A')->setWidth(35);
	$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
	$currcol += 1;
	$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL FAKTUR');
	$sheet->getColumnDimension('B')->setWidth(35);
	$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
	$currcol += 1;
	$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL JATUH TEMPO');
	$sheet->getColumnDimension('C')->setWidth(15);
	$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
	$currcol += 1;
	$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRAND TOTAL');
	$sheet->getColumnDimension('D')->setWidth(15);
	$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
	$currcol += 1;
	$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TELAT BAYAR');
	$sheet->getColumnDimension('E')->setWidth(20);
	$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
	$currcol += 1;
	$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TUNJ KENA DENDA');
	$sheet->getColumnDimension('F')->setWidth(15);
	$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
	$currcol += 1;
	$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TUNJ ATAS FAKTUR');
	$sheet->getColumnDimension('G')->setWidth(15);
	$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
	$currcol += 1;
	$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PRESENTASE DENDA');
	$sheet->getColumnDimension('H')->setWidth(15);
	$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
	$currcol += 1;
	$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DENDA DENDA');
	$sheet->getColumnDimension('I')->setWidth(15);
	$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
	$currcol += 1;
									
	$max_col = $currcol-2;

	$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

	$currrow += 1;


	//  utk Grouping
	$Bulan = "0";

	if($laporan!=null){
		

		//  utk Hitung Total di Bln yg sama
		$Bln = "0";
		$laporan=json_encode($laporan->data);
		$laporan=json_decode($laporan,true);
		
		foreach($laporan as $key => $value){

			// // Total Grouping Bulan & Tahun
			if ( ($Bulan != $value['Bulan']."/".$value['Tahun']) and $Bulan != "0" ) {

				$currrow += 1;
				// $currcol = 1;
				// $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Faktur');
				// $currcol += 1;
				// $sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_grandTotal);
				// $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				// $currcol += 1;
				// $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Denda Awal');
				// $currcol += 1;
				// $sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_tunjKenaDenda);
				// $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				// $currcol += 1;
				// $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Tunj atas Faktur');
				// $currcol += 1;
				// $sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_tunjAtasFaktur);
				// $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				// $currcol += 1;
				// $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Denda');
				// $currcol += 1;
				// $sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_denda);
				// $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				// $currrow += 1;
				// $currcol -= 1;
				// $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Selisih Denda');
				// $currcol += 1;
				// $sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_tunjKenaDenda - $total_denda);
				// $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				// $currrow += 1;

				$total_grandTotal_Bln = 0;
				// $total_grandTotal = 0;
				// $total_tunjKenaDenda = 0;
				// $total_tunjAtasFaktur = 0;
				// $total_denda = 0;
			}

			// echo $Bulan;

			//  Hitung Total di Bln yg sama
			if ( $Bulan != $value['Bulan']."/".$value['Tahun'] or $Bulan == "0" ) {
				$Bln = $value['Bulan']."/".$value['Tahun'];
				
				$total_grandTotal_Bln = 0;

				foreach($laporan as $value2){
					if ( $Bln == $value2['Bulan']."/".$value2['Tahun'] ) {
						$total_grandTotal_Bln += rtrim($value2['Grandtotal'], ' ');					
					}			
				}

				// echo $Bulan." = ".$total_grandTotal;
			}
			
			$tunjKenaDenda = $value['TotalTunjangan'] - $value['Subsidi_Penjualan'];		
			$tunjAtasFaktur = round(rtrim($value['Grandtotal'],' ') * $tunjKenaDenda / $total_grandTotal_Bln ,0);
			$denda = round(rtrim($value['Denda_Incentive'], ' ') * $tunjAtasFaktur / 100 ,0);
			$totaldendaawal = $value['TotalDendaAwal'];

			
			$currrow += 1;
			$currcol = 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($value['No_Faktur']));
			// $sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_grandTotal);
			// $sheet->setCellValueByColumnAndRow($currcol, $currrow,$value['Bulan']."/".$value['Tahun']);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("m-d-Y",strtotime(rtrim($value['Tgl_Faktur']))));
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("m-d-Y",strtotime(rtrim($value['Tgl_JatuhTempo']))));
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($value['Grandtotal']));
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $value['TelatBayar']);
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tunjKenaDenda);
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tunjAtasFaktur);
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $value['Denda_Incentive']);
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $denda);
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0');
		
			$total_tunjKenaDenda += $tunjKenaDenda;
			$total_tunjAtasFaktur += $tunjAtasFaktur;
			$total_denda += $denda;
			$total_grandTotal += rtrim($value['Grandtotal'], ' ');

			$Bulan = $value['Bulan']."/".$value['Tahun'];
			
		}

		

		// Total
		$currrow += 1;
		$currcol = 3;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Faktur');
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
		$currcol += 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_grandTotal);
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0');
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
		$currcol += 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Tunj atas Faktur');
		$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+1 , $currrow);
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
		$currcol += 2;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_tunjAtasFaktur);
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0');
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
		$currcol += 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Denda');
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
		$currcol += 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_denda);
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0');
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
		$currrow += 1;

		$currcol -= 3;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Denda Awal');
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
		$currcol += 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, $totaldendaawal);
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0');
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
		$currcol += 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Selisih Denda');
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
		$currcol += 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, $totaldendaawal - $total_denda);
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0');
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);

	}	


	$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
				
	// rata tengah header
	$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
	$sheet->getStyle('A5:'.$max_col.'6')->getAlignment()->setHorizontal($alignment_center);

	// warna header
	$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
	$sheet->getStyle('A5:'.$max_col.'6')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');

	// border
	$sheet->getStyle("A1:".$max_col."6")->getFont()->setBold(true);
	$styleArray = [
	'borders' => [
	'allBorders' => [
	'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
	],
	],
	];
	$sheet->getStyle('A5:'.$max_col.$currrow)->applyFromArray($styleArray);
	$sheet->setSelectedCell('A1');

		
	$filename= $Judul.' ['.date('Ymd').']'; //save our workbook as this file name
	$writer = new Xlsx($spreadsheet);
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
	header('Cache-Control: max-age=0');
	ob_end_clean();
	$writer->save('php://output');	// download file 
	exit();
