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
$sheet->getColumnDimension('A')->setWidth(13);
$sheet->getColumnDimension('B')->setWidth(2);
$sheet->getColumnDimension('D')->setWidth(2);
$sheet->getColumnDimension('G')->setWidth(2);
$sheet->getColumnDimension('J')->setWidth(2);


$row = 1;
// $sheet->setCellValue('A'.$row, 'PERBANDINGAN LAPORAN OMSET');
$sheet->setCellValue('A'.$row, 'PERBANDINGAN LAPORAN '.$judul);
$sheet->setCellValue('F'.$row, 'ESTIMASI');
$sheet->setCellValue('K'.$row, $nama_ppn.' PPN');

$row = 3;
$sheet->mergeCells('A'.$row.':A4');
$sheet->setCellValue('A'.$row, 'PERIODE');


$sheet->mergeCells('C'.$row.':C4');
$sheet->setCellValue('C'.$row, 'DIVISI');

//year end
//baris ke-3 kolom E - F
$sheet->mergeCells('E'.$row.':F'.$row);
$sheet->setCellValue('E'.$row, $year_end);
//year start
$sheet->mergeCells('H'.$row.':I'.$row);
$sheet->setCellValue('H'.$row, $year_start);

//selisih
$sheet->mergeCells('K'.$row.':K4');
$sheet->setCellValue('K'.$row, 'SELISIH');

$row = 4;
$sheet->setCellValue('E'.$row, $nama_bulan);
$sheet->setCellValue('F'.$row, 'TOTAL');



$sheet->setCellValue('H'.$row, $nama_bulan);
$sheet->setCellValue('I'.$row, 'TOTAL');

$sheet->getStyle("A3:K4")->getAlignment()->setHorizontal($horizontal_center);
$sheet->getStyle("A3:K4")->getAlignment()->setVertical($vertical_center);

$boldStyle = $sheet->getStyle('A3:K4')->getFont();
$boldStyle->setBold(true);

$row+=1;

$divisiArray=[];
$grandTotalDivisiEnd = [];
$grandTotalDivisiStart = [];
$grandTotalDivisiSelisih=0;
$subTotalArray = [];
$subTotalPosArray = [];
$periodeIndex = 0;
foreach($report['by_tgl'] as $value){
	$dateStart = rtrim($value['date_start'],' ');
	$dateEnd =  rtrim($value['date_end'],' ');
	$sheet->setCellValue('A'.$row, "(".$dateStart ."-". $dateEnd.")");
	$totalStart = 0;
	$totalEnd = 0;
	$rowTmp = $row;
	foreach($value['periode'] as $key2 => $value2){
		$divisi = rtrim($value2['divisi'],' ');
		$dppStart = rtrim($value2['dpp_start'],' ');
		$dppEnd = rtrim($value2['dpp_end'],' ');

		$sheet->setCellValue('C'.$row, $divisi);
		$sheet->setCellValue('E'.$row, $dppEnd);
		$sheet->setCellValue('H'.$row, $dppStart);
		
		if(!isset($grandTotalDivisiEnd[$divisi])){
			$grandTotalDivisiEnd[$divisi] = 0;
		}
		if(!isset($grandTotalDivisiStart[$divisi])){
			$grandTotalDivisiStart[$divisi] = 0;
		}
		$grandTotalDivisiEnd[$divisi] += $dppEnd;
		$grandTotalDivisiStart[$divisi] += $dppStart;
		$divisiArray[$divisi] = $divisi;
		

		$totalStart += $dppStart;
		$totalEnd += $dppEnd;
		
		$row+=1;
	}

	$selisih = $totalEnd - $totalStart;

	$grandTotalDivisiSelisih += $selisih;
	$sheet->setCellValue('F'.$rowTmp, $totalEnd);
	$sheet->setCellValue('I'.$rowTmp, $totalStart);
	$sheet->setCellValue('K'.$rowTmp, $selisih);
	
	$row+=1;

	$subTotalPosArray[] = $row;
	$subTotalArray[] = array(
		'total_end' => $totalEnd,
		'total_start' => $totalStart,
		'selisih' => $selisih,
	);

	$row+=1;
	$periodeIndex+=1;
}
// echo '<pre>';
// print_r($subTotalArray);
// echo '</pre>';
// echo '<pre>';
// print_r($subTotalPosArray);
// echo '</pre>';
foreach($subTotalPosArray as $key => $valueRow){
	
	if(($key+1) < $periodeIndex-1){
		$subTotalEnd =  ($subTotalArray[($key+1)]['total_end'] + $subTotalArray[$key]['total_end']);
		$subTotalStart = ($subTotalArray[($key+1)]['total_start'] + $subTotalArray[$key]['total_start']);
		$subTotalSelisih = ($subTotalArray[($key+1)]['selisih'] + $subTotalArray[$key]['selisih']);

		//echo ($key+1) .'-'. $key.'---- '.$subTotalEnd." - ".$subTotalStart.' - '.$subTotalSelisih.'<br>';
		//echo ($subTotalArray[($key+1)] - $subTotalArray[$key]).'<br>';
		$sheet->setCellValue('A'.$valueRow, 'PERIODE 1-'.($key+2));
		$sheet->setCellValue('B'.$valueRow, 'SUB-TOTAL');
		$sheet->setCellValue('F'.$valueRow, ''.$subTotalEnd );
		$sheet->setCellValue('I'.$valueRow, ''.$subTotalEnd );
		$sheet->setCellValue('K'.$valueRow, ''.$subTotalEnd );
	}
	
	
}
$row-=1;
$rowTmp = $row;
$sheet->setCellValue('A'.$row,'TOTAL');
$totalEnd = 0;
$totalStart = 0;
foreach($divisiArray as $key => $value){
	$sheet->setCellValue('C'.$row,$value);
	$sheet->setCellValue('E'.$row,$grandTotalDivisiEnd[$value]);
	$sheet->setCellValue('H'.$row,$grandTotalDivisiStart[$value]);

	$totalEnd += $grandTotalDivisiEnd[$value];
	$totalStart += $grandTotalDivisiStart[$value];
	$row+=1;
}
$sheet->setCellValue('F'.$rowTmp,$totalEnd);
$sheet->setCellValue('I'.$rowTmp,$totalStart);
$sheet->setCellValue('K'.$rowTmp,$grandTotalDivisiSelisih);
$row+=1;
// echo '<pre>';
// print_r($report['by_bulan']);
// echo '</pre>';
$sheet->getRowDimension($row)->setRowHeight(30);
$sheet->getStyle("A".$row.":K".$row)->getAlignment()->setHorizontal($horizontal_center);
$sheet->getStyle("A".$row.":K".$row)->getAlignment()->setVertical($vertical_center);

$namaBulanJanSampai = strtoupper("Jan-".substr($nama_bulan,0,3));
$selisihJanSampai = $report['by_bulan']['periode']['year_end'] -  $report['by_bulan']['periode']['year_start'];
$sheet->mergeCells('A'.$row.':C'.$row);
$substrinNamaPpn = substr(strtoupper($nama_ppn),0,3);
$sheet->setCellValue('A'.$row,'OMSET '.$namaBulanJanSampai.' ('.$substrinNamaPpn.' PPN)');
$sheet->setCellValue('F'.$row,''.$report['by_bulan']['periode']['year_end']);
$sheet->setCellValue('I'.$row,''.$report['by_bulan']['periode']['year_start']);
$sheet->setCellValue('K'.$row,''.$selisihJanSampai);

$spreadsheet->getActiveSheet()->getStyle('E5:K'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
$spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(19);
$spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(19);
$spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(19);
$spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(19);
$spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(19);
// echo '<pre>';
// print_r($grandTotalDivisiStart);
// echo '</pre>';

// echo '<pre>';
// print_r($grandTotalDivisiEnd);
// echo '</pre>';

// echo '<pre>';
// print_r($divisiArray);
// echo '</pre>';

//$filename='PerbandinganOmzet'; //save our workbook as this file name
//save tanpa download
//$writer = new Xlsx($spreadsheet);
//$writer->save('reegan.xlsx');	// download file 


ob_end_clean();
$filename='PerbandinganOmzet_'.date('Y-m-d His');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
header("Pragma: no-cache");
header("Expires: 0");
ob_end_clean();
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
?>