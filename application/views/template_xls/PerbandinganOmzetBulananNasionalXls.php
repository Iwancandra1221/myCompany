<?php
    require FCPATH.'application/controllers/vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    $spreadsheet = new Spreadsheet();
    $fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;


    $horizontal_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
    $horizontal_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;

    $vertical_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
    $vertical_bottom = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM;

    $sheet = $spreadsheet->getActiveSheet(0);

    $namaBulan = [
        1 => "Januari",
        2 => "Februari",
        3 => "Maret",
        4 => "April",
        5 => "Mei",
        6 => "Juni",
        7 => "Juli",
        8 => "Agustus",
        9 => "September",
        10 => "Oktober",
        11 => "November",
        12 => "Desember"
    ];

    $report = json_decode($report,true);

    $currrow=1;
    $currcol=1;

    $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'ESTIMASI PERBANDINGAN LAPORAN '.$report['Judul']);
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
    $sheet->mergeCells('A1:E1');
    $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal('center');
    $currrow++;
    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $report['TipePPN'].' PPN');
    $sheet->mergeCells('A2:E2');
    $sheet->getStyle('A2:E2')->getAlignment()->setHorizontal('center');

    $sheet->getStyle('A1')->getFont()->setSize(10);

    $currrow=$currrow+3;
    $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'BULAN');
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
    $currcol++;
    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $report['TahunAwal']);
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
    $currcol++;
    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $report['TahunAkhir']);
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
    $currcol++;
    $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Selisih');
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
    $currcol++;
    $sheet->setCellValueByColumnAndRow($currcol, $currrow, '%Kenaikan (Penurunan) Total Omzet Net');
    $style = $sheet->getStyleByColumnAndRow($currcol, $currrow);
    $style->getAlignment()->setWrapText(true);
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
    $sheet->getStyle('E5')->getAlignment()->setHorizontal('center');


    $borderStyle = [
        'borders' => [
            'bottom' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ],
    ];
    $currcol=1;
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($borderStyle);
    $currcol++;
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($borderStyle);
    $currcol++;
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($borderStyle);
    $currcol++;
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($borderStyle);

    $currrow++;

    $total_awal=0;
    $total_akhir=0;

    foreach($report['data'] as $bln => $data){
        $currcol=1;
        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $namaBulan[$bln]);
        $currcol++;
        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $data['TotalPeriodeAwal']);
        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0.00');
        $currcol++;
        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $data['TotalPeriodeAkhir']);
        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0.00');
        $currcol++;
        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $data['Selisih']);
        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0.00');
        $currcol++;
        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $data['Persentase']);
        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('0.00%');
        $currrow++;

        $total_awal     +=$data['TotalPeriodeAwal'];
        $total_akhir    +=$data['TotalPeriodeAkhir'];
    }

    $currcol=1;
    $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
    $sheet->getStyle('A'.$currrow.':D'.$currrow)->getAlignment()->setHorizontal('right');
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
    $currcol++;
    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_awal);
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
    $currcol++;
    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_akhir);
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
    $currcol++;
    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_akhir-$total_awal);
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
    $currcol++;
    $sheet->setCellValueByColumnAndRow($currcol, $currrow, ceil((($total_akhir/$total_awal)-1)*100).'%');
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
    $currrow++;

    $currcol=1;
    $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Avg');
    $sheet->getStyle('A'.$currrow.':D'.$currrow)->getAlignment()->setHorizontal('right');
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
    $currcol++;
    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $report['avgAwal']);
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
    $currcol++;
    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $report['avgAkhir']);
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
    $currcol++;
    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $report['SelisihAvg']);
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
    $currcol++;
    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $report['persentaseAvg']);
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
    $currrow++;


    $sheet->getColumnDimension('A')->setAutoSize(TRUE);
    $sheet->getColumnDimension('B')->setAutoSize(TRUE);
    $sheet->getColumnDimension('C')->setAutoSize(TRUE);
    $sheet->getColumnDimension('D')->setAutoSize(TRUE);
    $sheet->getColumnDimension('E')->setWidth(20);
    

    ob_end_clean();
    $filename=$filenameTambahan.'_'.date('Y-m-d His');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
    header("Pragma: no-cache");
    header("Expires: 0");
    ob_end_clean();
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
?>