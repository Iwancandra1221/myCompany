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
  ),
);

$datax = array();
$periode_start_nr = new DateTime($data['dr1']);
$bln_start_nr = $periode_start_nr->format('F');
$year_start_nr = substr($periode_start_nr->format('Y'),2);

$periode_end_nr = new DateTime($data['dr2']);
$bln_end_nr = $periode_end_nr->format('F');
$year_end_nr = substr($periode_end_nr->format('Y'),2);

$periode_start_bpb = new DateTime($data['db1']);
$bln_start_bpb = $periode_start_bpb->format('F');
$year_start_bpb = substr($periode_start_bpb->format('Y'), 2);

$periode_end_bpb = new DateTime($data['db2']);
$bln_end_bpb = $periode_end_bpb->format('F');
$year_end_bpb = substr($periode_end_bpb->format('Y'), 2);

if($data['in_nr'] == 1) {
  $check = 'INCLUDE NRP TANPA BPB';
} else {
  $check = 'EXCLUDE NRP TANPA BPB';
}

$LogDate = date("d-F-Y H:i:s");


if ($rekap != null) {
  foreach ($rekap as $key => $report) {
    $date = new DateTime($report['Tgl_Retur']);
    if (!isset($datax[$date->format("F")][$date->format("Y")])) {
      $datax[$date->format("F")][$date->format("Y")] = array();
    }
    $datax[$date->format("F")][$date->format("Y")][] = $report;
  };
  //echo '<pre>';
  //echo die(print_r($datax));
  //echo '</pre>';

}

$row = 1;

$sheet->setCellValue('A' . $row, 'NOTA RETUR BARANG YANG MASIH DALAM PERJALANAN');
$sheet->mergeCells('A1:G1');
$sheet->getStyle('A'.$row)->getFont()->setBold(TRUE);
$sheet->getStyle('A' . $row)->getFont()->setSize(12);

$row += 1;
$sheet->setCellValue('A' . $row, 'PERIODE NRP: '  . $bln_start_nr . '-' . $year_start_nr . ' S/D ' . $bln_end_nr . '-' . $year_end_nr);
$sheet->getStyle("A" . $row . ":D" . $row)->getFont()->setBold(TRUE);
$sheet->getStyle("A" . $row . ":D" . $row)->getFont()->setSize(12);

$row += 1;
$sheet->setCellValue('A' . $row, 'PERIODE BPB: '  . $bln_start_bpb . '-' . $year_start_bpb . ' S/D ' . $bln_end_bpb . '-' . $year_end_bpb);
$sheet->mergeCells("A" . $row . ":D" . $row);
$sheet->getStyle("A" . $row . ":D" . $row)->getFont()->setBold(TRUE);
$sheet->getStyle("A" . $row . ":D" . $row)->getFont()->setSize(12);

$row += 1;
$sheet->setCellValue('A' . $row, 'NRP: ' .  $check);
$sheet->mergeCells("A" . $row . ":D" . $row);
$sheet->getStyle("A" . $row . ":D" . $row)->getFont()->setBold(TRUE);
$sheet->getStyle("A" . $row . ":D" . $row)->getFont()->setSize(12);

$row += 1;
$sheet->setCellValue('A' . $row, 'PROCESSED ON: ' . $LogDate);
$sheet->mergeCells("A" . $row . ":D" . $row);
$sheet->getStyle("A" . $row . ":D" . $row)->getFont()->setBold(TRUE);
$sheet->getStyle("A" . $row . ":D" . $row)->getFont()->setSize(12);

$row += 2;
$sheet->setCellValue('A' . $row, 'TANGGAL');
$sheet->getColumnDimension('A')->setWidth(20);
$sheet->setCellValue('B' . $row, 'NO.RETUR');
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->setCellValue('C' . $row, 'NO FAKTUR FAJAK');
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->setCellValue('D' . $row, 'TANGGAL');
$sheet->getColumnDimension('D')->setWidth(20);
$sheet->setCellValue('E' . $row, 'NPWP PENJUAL/');
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->setCellValue('F' . $row, 'NAMA PKP/');
$sheet->getColumnDimension('F')->setWidth(35);
$sheet->setCellValue('G' . $row, 'DPP');
$sheet->getColumnDimension('G')->setWidth(20);
$sheet->setCellValue('H' . $row, 'PPN');
$sheet->getColumnDimension('H')->setWidth(20);
$sheet->setCellValue('I' . $row, 'TOTAL');
$sheet->getColumnDimension('I')->setWidth(20);
$sheet->setCellValue('J' . $row, 'KODE BARANG');
$sheet->getColumnDimension('J')->setWidth(20);
$sheet->setCellValue('K' . $row, 'QTY');
$sheet->getColumnDimension('K')->setWidth(10);
$sheet->setCellValue('L' . $row, 'NOMOR BPRPJ');
$sheet->getColumnDimension('L')->setWidth(20);
$sheet->setCellValue('M' . $row, 'TANGGAL BPRPJ');
$sheet->getColumnDimension('M')->setWidth(20);
$sheet->setCellValue('N' . $row, 'TANGGAL');
$sheet->getColumnDimension('N')->setWidth(20);
$sheet->getStyle('A' . $row . ":N" . $row)->getFont()->setBold(TRUE);
$sheet->getStyle('A' . $row . ":N" . $row)->getFont()->setSize(10);
$sheet->getStyle("A" . $row . ":N" . $row)->getAlignment()->setHorizontal($horizontal_center);
$sheet->getStyle("A" . $row . ":N" . $row)->getAlignment()->setVertical($vertical_center);

$row += 1;
$sheet->setCellValue('A'. $row, 'RETUR');
$sheet->setCellValue('B' . $row, '');
$sheet->setCellValue('C' . $row, 'YANG DI RETUR');
$sheet->setCellValue('D' . $row, 'FAKTUR PAJAK');
$sheet->setCellValue('E' . $row, 'PEMBERI JASA');
$sheet->setCellValue('F' . $row, 'PEMBERI JASA');
$sheet->setCellValue('G' . $row, '');
$sheet->setCellValue('H' . $row, '');
$sheet->setCellValue('I' . $row, '');
$sheet->setCellValue('J' . $row, '');
$sheet->setCellValue('K' . $row, '');
$sheet->setCellValue('L' . $row, '');
$sheet->setCellValue('M' . $row, '');
$sheet->setCellValue('N' . $row, 'INPUT');
$sheet->getStyle('A' . $row . ":N" . $row)->getFont()->setBold(TRUE);
$sheet->getStyle('A' . $row . ":N" . $row)->getFont()->setSize(10);
$sheet->getStyle("A" . $row . ":N" . $row)->getAlignment()->setHorizontal($horizontal_center);
$sheet->getStyle("A" . $row . ":N" . $row)->getAlignment()->setVertical($vertical_center);

$row += 1;
$sheet->setCellValue('A' . $row, '');
$sheet->setCellValue('B' . $row, '');
$sheet->setCellValue('C' . $row, '');
$sheet->setCellValue('D' . $row, '');
$sheet->setCellValue('E' . $row, '');
$sheet->setCellValue('F' . $row, '');
$sheet->setCellValue('G' . $row, '(Rp.)');
$sheet->setCellValue('H' . $row, '(Rp.)');
$sheet->setCellValue('I' . $row, '');
$sheet->setCellValue('J' . $row, '');
$sheet->setCellValue('K' . $row, '');
$sheet->setCellValue('L' . $row, '');
$sheet->setCellValue('M' . $row, '');
$sheet->setCellValue('N' . $row, 'DATA');
$sheet->getStyle('A' . $row . ":N" . $row)->getFont()->setBold(TRUE);
$sheet->getStyle('A' . $row . ":N" . $row)->getFont()->setSize(10);
$sheet->getStyle("A" . $row . ":N" . $row)->getAlignment()->setHorizontal($horizontal_center);
$sheet->getStyle("A" . $row . ":N" . $row)->getAlignment()->setVertical($vertical_center);

$sheet->getStyle('A7:N'. $row)->applyFromArray($styleArray);

$row +=1;

if ($datax != null) {
  foreach ($datax as $bln => $reportMonth) {
    foreach($reportMonth as $year => $reportYear) {
      $row += 1;
      $sheet->setCellValue('A' . $row, $bln .'-'.substr($year,-2));
      $sheet->getStyle("A" . $row)->getAlignment()->setHorizontal($horizontal_center);
      $sheet->getStyle("A" . $row)->getAlignment()->setVertical($vertical_center);
      $sheet->getStyle('A' . $row)->getFont()->setBold(TRUE);
      $sheet->getStyle('A' . $row)->getFont()->setSize(10);
      foreach ($reportYear as $report) {
        $row += 1;
        $tgl_bprpj = rtrim($report['Tgl_BPRPJ'], ' ');
        if($tgl_bprpj!=''){
          $tgl_bprpj = date('d/m/Y',strtotime($tgl_bprpj));
        }

        $sheet->setCellValue('A' . $row, date('d/m/Y',strtotime( rtrim($report['Tgl_Retur'], ' ') )));
        $sheet->setCellValue('B' . $row, rtrim($report['No_Retur'], ' '));
        $sheet->setCellValue('C' . $row, rtrim($report['No_Faktur_Pajak'], ' '));
        $sheet->setCellValue('D' . $row, date('d/m/Y', strtotime(rtrim($report['Tgl_Faktur_Pajak'], ' '))));
        $sheet->setCellValue('E' . $row, rtrim($report['Npwp'], ' '));
        $sheet->setCellValue('F' . $row, rtrim($report['Nm_Pkp'], ' '));
        $sheet->setCellValue('G' . $row, number_format(rtrim($report['Dpp'], ' '),0));
        $sheet->setCellValue('H' . $row, number_format(rtrim($report['Ppn'], ' '),0));
        $sheet->setCellValue('I' . $row, number_format(rtrim($report['Total'], ' '),0));
        $sheet->setCellValue('J' . $row, rtrim($report['Kd_Barang'], ' '));
        $sheet->setCellValue('K' . $row, rtrim($report['Qty'], ' '));
        $sheet->setCellValue('L' . $row, rtrim($report['No_BPRPJ'], ' '));
        $sheet->setCellValue('M' . $row, $tgl_bprpj);
        $sheet->setCellValue('N' . $row, date('d/m/Y',strtotime( rtrim($report['Tgl_Input'], ' ') )));
        $sheet->getStyle("A" . $row . ":F" . $row)->getAlignment()->setHorizontal($horizontal_center);
        $sheet->getStyle("A" . $row . ":F" . $row)->getAlignment()->setVertical($vertical_center);
        $sheet->getStyle("G" . $row . ":I" . $row)->getAlignment()->setHorizontal($horizontal_right);
        $sheet->getStyle("J" . $row)->getAlignment()->setHorizontal($horizontal_left);
        $sheet->getStyle("K" . $row . ":N" . $row)->getAlignment()->setHorizontal($horizontal_center);
        $sheet->getStyle("K" . $row . ":N" . $row)->getAlignment()->setVertical($vertical_center);
      }
      $row += 1;
      $sheet->getStyle('A7:N' . $row)->applyFromArray($styleArray);
    }
  }
}

$filename = 'LaporanBarangDalamPerjalanan[' . date('YmdHis') . ']'; //save our workbook as this file name
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
header('Cache-Control: max-age=0');
ob_end_clean();
$writer->save('php://output');  // download file 
exit();