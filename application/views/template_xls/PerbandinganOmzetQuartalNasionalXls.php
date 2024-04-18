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

    $Quartal = [
        1 => "Q1",
        2 => "Q2",
        3 => "Q3",
        4 => "Q4"
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

    $dataArray = array();

    $i=1;
    $z=1;  
    $x=3;

    foreach($report['data'] as $bln => $data){

        if($i==$x){
            $x=$x*2;
            $z++;
        }

        if(empty($dataArray[$z]['TotalPeriodeAwal'])){
            $dataArray[$z]['TotalPeriodeAwal']=0;
        }
        if(empty($dataArray[$z]['TotalPeriodeAkhir'])){
            $dataArray[$z]['TotalPeriodeAkhir']=0;
        }

        $dataArray[$z]['TotalPeriodeAwal']+=$data['TotalPeriodeAwal'];
        $dataArray[$z]['TotalPeriodeAkhir']+=$data['TotalPeriodeAkhir'];
        $i++;
    }

    foreach ($dataArray as $key => $da) {
        $Persentase = ceil((($dataArray[$key]['TotalPeriodeAkhir']/$dataArray[$key]['TotalPeriodeAwal'])-1)*100).'%';
        $dataArray[$key]['Selisih']=$dataArray[$key]['TotalPeriodeAkhir']-$dataArray[$key]['TotalPeriodeAwal'];
        $dataArray[$key]['Persentase']=$Persentase;
    }


    foreach($dataArray as $Q => $data){
        $currcol=1;
        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Quartal[$Q]);
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