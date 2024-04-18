<?php
require FCPATH.'application/controllers/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
$sheet = $spreadsheet->getActiveSheet(0);
$sheet->setTitle('Sheet 1');

$sheet->setCellValue('A1', ''.date('d-M-Y H:i:s'));

$sheet->setCellValue('A3', 'LAPORAN TRANSAKSI BANK');
$sheet->setCellValue('A4', 'BANK');
$sheet->setCellValue('A5', 'NO REK');
$sheet->setCellValue('A6', 'A/N');

$sheet->setCellValue('B4', ': '.rtrim($Bank,' ').' '.rtrim($Cabang,' '));
$sheet->setCellValue('B5', ': '.$NoRekening);
$sheet->setCellValue('B6', ': '.$Nm_Pemilik);
$sheet->setCellValue('F6','Periode '.date('d-M-Y',strtotime($TglAwal)).' s/d '.date('d-M-Y',strtotime($TglAkhir)));

$total_debet = 0;
$total_kredit = 0;
$saldo = 0;
$row = 7;

$sheet->setCellValue('A'.$row, 'Tgl. Trans');
$sheet->setCellValue('B'.$row, 'No. Bukti');
$sheet->setCellValue('C'.$row, 'Keterangan');
$sheet->setCellValue('D'.$row, 'Debet');
$sheet->setCellValue('E'.$row, 'Kredit');
$sheet->setCellValue('F'.$row, 'Saldo');

$styleArray = array(
    'borders' => array(
        'outline' => array(
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
            'color' => array('argb' => '000000'),
        ),
    ),
);

$sheet->getStyle('A7:F7')->applyFromArray($styleArray);

$row+=1;
foreach($res->data as $dt){
	$saldo += $dt->Saldo;

	$sheet->setCellValue('A'.$row, ''.date('d-M-Y',strtotime($dt->Tgl_Trans)));
	$sheet->setCellValue('B'.$row, ''.$dt->No_Bukti);
	$sheet->setCellValue('C'.$row, ''.$dt->Keterangan);
	$sheet->setCellValue('D'.$row, ''.$dt->Kredit);
	$sheet->setCellValue('E'.$row, ''.$dt->Debet);
	$sheet->setCellValue('F'.$row, ''.$saldo);

	$total_debet += $dt->Debet;
	$total_kredit += $dt->Kredit;
	$row+=1;
}
$sheet->setCellValue('A'.$row, ''.date('d-M-Y',strtotime($TglAkhir)));
$sheet->setCellValue('C'.$row, 'Saldo Akhir');
$sheet->setCellValue('F'.$row, ''.$saldo);

$row+=1;
$sheet->setCellValue('C'.$row, 'Total');
$sheet->setCellValue('D'.$row, ''.$total_kredit);
$sheet->setCellValue('E'.$row, ''.$total_debet);
$sheet->getStyle('A'.$row.':F'.$row)->applyFromArray($styleArray);



$filename='LAPORAN_TRANSAKSI_BANK_['.date('YmdHis').']'; //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');	// download file 
exit();
?>