<?php
require FCPATH . 'application/controllers/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
$sheet = $spreadsheet->getActiveSheet(0);
$sheet->setTitle('Sheet 1');
$horizontal_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
$horizontal_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
$horizontal_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
$vertical_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
$styleArray = array(
  'borders' => array(
    'outline' => array(
      'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
      'color' => array('argb' => '000000'),
    ),
    'vertical' => array(
      'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
      'color' => array('argb' => '000000'),
    ),
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => 'FFFF00')
      )
  ),
);

$cabang = $data['cabang_name'];
$periode = new DateTime($data['date1']);
$bln = $periode->format('F');
$year = substr($periode->format('Y'),2);
$sales = array();
$service = array();
$dealer = array();
if ($rekap != null) {
  $sales= $rekap['Sales'];
  $service = $rekap['Service'];
  $dealer = $rekap['Dealer'];
}

//echo '<pre>';
//echo print_r($sales);
//echo print_r($service);
//echo print_r($dealer);
//echo print_r($data);
//echo print_r($dealer);
//die();
//echo '</pre>';


$row = 1;

$sheet->setCellValue('A' . $row, 'LAPORAN CASH BEFORE');
$sheet->mergeCells('A1:B1');
$sheet->getStyle('A'.$row)->getFont()->setBold(TRUE);
$sheet->getStyle('A' . $row)->getFont()->setSize(12);

$row +=1;
$sheet->setCellValue('A' . $row, 'CABANG:');
$sheet->setCellValue('B' . $row, $cabang);
$sheet->mergeCells('A1:B1');
$sheet->getStyle("A" . $row .":B" . $row)->getFont()->setBold(TRUE);
$sheet->getStyle("A" . $row .":B" . $row)->getFont()->setSize(10);

$row += 1;
$sheet->setCellValue('A' . $row, 'PERIODE');
$sheet->setCellValue('B' . $row, $bln . '-' . $year);
$sheet->mergeCells('A1:B1');
$sheet->getStyle("A" . $row . ":B" . $row)->getFont()->setBold(TRUE);
$sheet->getStyle("A" . $row . ":B" . $row)->getFont()->setSize(10);

$row += 2;
$sheet->setCellValue('A' . $row, 'No');
$sheet->getColumnDimension('A')->setWidth(10);
$sheet->setCellValue('B' . $row, 'Nama WP');
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->setCellValue('C' . $row, 'Nomor Seri FP');
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->setCellValue('D' . $row, 'TANGGAL FP');
$sheet->getColumnDimension('D')->setWidth(20);
$sheet->setCellValue('E' . $row, 'DPP');
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->setCellValue('F' . $row, 'PPN');
$sheet->getColumnDimension('F')->setWidth(35);
$sheet->setCellValue('G' . $row, 'Nominal Invoice');
$sheet->getColumnDimension('G')->setWidth(20);
$sheet->setCellValue('I' . $row, 'Nomor Faktur Penjualan');
$sheet->getColumnDimension('I')->setWidth(20);
$sheet->setCellValue('J' . $row, 'Tanggal Faktur Penjualan');
$sheet->getColumnDimension('J')->setWidth(20);
$sheet->setCellValue('K' . $row, 'Tanggal Bayar');
$sheet->getColumnDimension('K')->setWidth(20);
$sheet->getStyle('A' . $row . ":K" . $row)->getFont()->setBold(TRUE);
$sheet->getStyle('A' . $row . ":K" . $row)->getFont()->setSize(10);
$sheet->getStyle("A" . $row . ":K" . $row)->getAlignment()->setHorizontal($horizontal_center);
$sheet->getStyle("A" . $row . ":K" . $row)->getAlignment()->setVertical($vertical_center);

$sheet->getStyle('A5:G'. $row)->applyFromArray($styleArray);
$sheet->getStyle('I5:K'. $row)->applyFromArray($styleArray);
$sheet->getStyle('A6:G'. $row)->applyFromArray($styleArray);
$sheet->getStyle('I6:K'. $row)->applyFromArray($styleArray);

$i=1;
$j=1;
$k=1;
$row +=1;

if ($sales != null) {
  $sheet->setCellValue('B' . $row, 'CASH ' .$cabang);
  foreach ($sales as $key => $val) {
    $row += 1;
    $sheet->setCellValue('A' . $row, $i++);
    $sheet->setCellValue('B' . $row, rtrim($val['Nm_Pajak'], ' ') );
    $sheet->setCellValue('C' . $row, rtrim($val['No_FakturP'], ' ') );
    $sheet->setCellValue('D' . $row, date('d/m/Y', strtotime(rtrim($val['Tgl_FakturP'], ' '))));
    $sheet->setCellValue('E' . $row, rtrim($val['DPP'], ' '));
    $sheet->setCellValue('F' . $row, rtrim($val['PPN'], ' '));
    $sheet->setCellValue('G' . $row, rtrim($val['Nominal_Invoice'], ' '));
    $sheet->setCellValue('I' . $row, rtrim($val['No_Faktur'], ' '));
    $sheet->setCellValue('J' . $row, date('d/m/Y', strtotime(rtrim($val['Tgl_Faktur'], ' '))));
    $sheet->setCellValue('K' . $row, date('d/m/Y', strtotime(rtrim($val['Tgl_Bayar'], ' '))));
    $sheet->getStyle("A" . $row)->getAlignment()->setHorizontal($horizontal_center);
    $sheet->getStyle("A" . $row)->getAlignment()->setVertical($vertical_center);
    $sheet->getStyle("B" . $row . ":C" . $row)->getAlignment()->setHorizontal($horizontal_left);
    $sheet->getStyle("D" . $row)->getAlignment()->setHorizontal($horizontal_center);
    $sheet->getStyle("D" . $row)->getAlignment()->setVertical($vertical_center);
    $sheet->getStyle("E" . $row .":G" . $row)->getAlignment()->setHorizontal($horizontal_right);
    $sheet->getStyle("I" . $row)->getAlignment()->setHorizontal($horizontal_left);
    $sheet->getStyle("J" . $row. ":K" . $row)->getAlignment()->setHorizontal($horizontal_center);
    $sheet->getStyle("J" . $row . ":K" . $row)->getAlignment()->setVertical($vertical_center);
  }
  $row +=1;
}
$row += 1;
if ($service != null) {
  $sheet->setCellValue('B' . $row, 'CASH SERVICE');
  foreach ($service as $key => $val) {
    $row += 1;
    $sheet->setCellValue('A' . $row, $j++);
    $sheet->setCellValue('B' . $row, rtrim($val['Nm_Pajak'], ' ') );
    $sheet->setCellValue('C' . $row, rtrim($val['No_FakturP'], ' '));
    $sheet->setCellValue('D' . $row, date('d/m/Y', strtotime(rtrim($val['Tgl_FakturP'], ' '))));
    $sheet->setCellValue('E' . $row, rtrim($val['DPP'], ' '));
    $sheet->setCellValue('F' . $row, rtrim($val['PPN'], ' '));
    $sheet->setCellValue('G' . $row, rtrim($val['Nominal_Invoice'], ' '));
    $sheet->setCellValue('I' . $row, rtrim($val['No_Faktur'], ' '));
    $sheet->setCellValue('J' . $row, date('d/m/Y', strtotime(rtrim($val['Tgl_Faktur'], ' '))));
    $sheet->setCellValue('K' . $row, date('d/m/Y', strtotime(rtrim($val['Tgl_Bayar'], ' '))));
    $sheet->getStyle("A" . $row)->getAlignment()->setHorizontal($horizontal_center);
    $sheet->getStyle("A" . $row)->getAlignment()->setVertical($vertical_center);
    $sheet->getStyle("B" . $row . ":C" . $row)->getAlignment()->setHorizontal($horizontal_left);
    $sheet->getStyle("D" . $row)->getAlignment()->setHorizontal($horizontal_center);
    $sheet->getStyle("D" . $row)->getAlignment()->setVertical($vertical_center);
    $sheet->getStyle("E" . $row .":G" . $row)->getAlignment()->setHorizontal($horizontal_right);
    $sheet->getStyle("I" . $row)->getAlignment()->setHorizontal($horizontal_left);
    $sheet->getStyle("J" . $row. ":K" . $row)->getAlignment()->setHorizontal($horizontal_center);
    $sheet->getStyle("J" . $row . ":K" . $row)->getAlignment()->setVertical($vertical_center);
  }
  $row += 1;
}
$row += 1;
if ($dealer != null) {
  $sheet->setCellValue('B' . $row, 'DEALER');
  foreach ($dealer as $key => $val) {
    $row += 1;
    $sheet->setCellValue('A' . $row, $k++);
    $sheet->setCellValue('B' . $row, rtrim($val['Nm_Pajak'], ' ') );
    $sheet->setCellValue('C' . $row, rtrim($val['No_FakturP'], ' ') );
    $sheet->setCellValue('D' . $row, date('d/m/Y', strtotime(rtrim($val['Tgl_FakturP'], ' '))));
    $sheet->setCellValue('E' . $row, rtrim($val['DPP'], ' '));
    $sheet->setCellValue('F' . $row, rtrim($val['PPN'], ' '));
    $sheet->setCellValue('G' . $row, rtrim($val['Nominal_Invoice'], ' '));
    $sheet->setCellValue('I' . $row, rtrim($val['No_Faktur'], ' '));
    $sheet->setCellValue('J' . $row, date('d/m/Y', strtotime(rtrim($val['Tgl_Faktur'], ' '))));
    $sheet->setCellValue('K' . $row, date('d/m/Y', strtotime(rtrim($val['Tgl_Bayar'], ' '))));
    $sheet->getStyle("A" . $row)->getAlignment()->setHorizontal($horizontal_center);
    $sheet->getStyle("A" . $row)->getAlignment()->setVertical($vertical_center);
    $sheet->getStyle("B" . $row . ":C" . $row)->getAlignment()->setHorizontal($horizontal_left);
    $sheet->getStyle("D" . $row)->getAlignment()->setHorizontal($horizontal_center);
    $sheet->getStyle("D" . $row)->getAlignment()->setVertical($vertical_center);
    $sheet->getStyle("E" . $row .":G" . $row)->getAlignment()->setHorizontal($horizontal_right);
    $sheet->getStyle("I" . $row)->getAlignment()->setHorizontal($horizontal_left);
    $sheet->getStyle("J" . $row. ":K" . $row)->getAlignment()->setHorizontal($horizontal_center);
    $sheet->getStyle("J" . $row . ":K" . $row)->getAlignment()->setVertical($vertical_center);
  }
  $row +=1;
}

$sheet->getStyle('A5:G'. $row)->applyFromArray($styleArray);
$sheet->getStyle('I5:K'. $row)->applyFromArray($styleArray);

$filename = 'LaporanCashBefore[' . date('YmdHis') . ']'; //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');  // download file 
exit();