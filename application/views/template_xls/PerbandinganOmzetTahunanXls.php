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
foreach($report as $keyWil => $valueWil){
    $row = 1;
    $wilayah = $keyWil;
    $dataDivisi = $valueWil['divisi'];
    $databyDivisi = $valueWil['by_divisi'];

    $sheet->setTitle($wilayah);

    //width
    $sheet->getColumnDimension('A')->setWidth(21);
    $sheet->getColumnDimension('B')->setWidth(20);
    $sheet->getColumnDimension('C')->setWidth(17);
    $sheet->getColumnDimension('D')->setWidth(17);

    $sheet->setCellValue('A'.$row, ''.$wilayah);


    $totalPerBulanAwal = array();
    $totalPerBulanAkhir = array();
    foreach($namaBulan as $keyBulan => $valueBulan){
        $index = ($keyBulan-1);
        $totalPerBulanAwal[$index] = 0;
        $totalPerBulanAkhir[$index] = 0;
    }
    

    $totalPerDivisiAwal = array();
    $totalPerDivisiAkhir = array();
    foreach($dataDivisi as $keyDiv => $valueDiv){
        $totalPerDivisiAwal[$valueDiv['Divisi']] = 0;
        $totalPerDivisiAkhir[$valueDiv['Divisi']] = 0;
    }
    // echo '<pre>';
    // print_r($databyDivisi);
    // echo '</pre>';
    foreach($databyDivisi as $keyDiv => $valueDiv){
        $divisi = $valueDiv['divisi'];
        $dataPeriode = $valueDiv['periode'];

        //create style
        $style = $sheet->getStyle('A'.$row.':D'.($row+1));
        //bold
        $font = $style->getFont();
        $font->setBold(true);
        //wrap
        $alignment = $style->getAlignment();
        $alignment->setWrapText(true);
        //border
        $borders = $style->getBorders();
        $borders->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        //align
        $sheet->getStyle("A".$row.":D".($row+1))->getAlignment()->setHorizontal($horizontal_center);
        $sheet->getStyle("A".$row.":D".($row+1))->getAlignment()->setVertical($vertical_bottom);

        $sheet->getRowDimension($row)->setRowHeight(18);
        $sheet->mergeCells('D'.$row.':D'.($row+1));
        $sheet->setCellValue('D'.$row, '% Kenaikan (Penurunan)     Total Omzet Net '.$year_start.' Vs '.$year_end);
        $row+=1;
        $sheet->getRowDimension($row)->setRowHeight(27);
        $sheet->setCellValue('A'.$row, ''.$divisi);
        $sheet->setCellValue('B'.$row, ''.$year_start);
        $sheet->setCellValue('C'.$row, ''.$year_end);

        if($dataPeriode!=''){
            $totalPeriodeAwal = 0;
            $totalPeriodeAkhir = 0;
            $jmlhDataAwal = 0;
            $jmlhDataAkhir = 0;
            foreach($namaBulan as $keyBulan => $valueBulan){
            // foreach($dataPeriode as $keyPeriode => $valuePeriode){
                $index = ($keyBulan-1);

                $dppStart = "-";
                $dppEnd = "-";
                $persentase = "";
                $row+=1;
                if(isset($dataPeriode[$index])){
                    //$month = $dataPeriode[$index]['month'];
                    $dppStart = floatval($dataPeriode[$index]['dpp_start']);
                    $dppEnd = floatval($dataPeriode[$index]['dpp_end']);

                    if($dppStart!==0 && $dppStart!=''){
                         $jmlhDataAwal+=1;
                    }
                    if($dppEnd!==0 && $dppEnd !='' ){
                        $jmlhDataAkhir+=1;
                    }

                    $totalPeriodeAwal += $dppStart;
                    $totalPeriodeAkhir += $dppEnd;

                    $totalPerBulanAwal[$index] += $dppStart;
                    $totalPerBulanAkhir[$index] += $dppEnd;

                    $persentase = 0;
                    if($totalPeriodeAwal!==0 && $totalPeriodeAwal!='' && $totalPeriodeAkhir!==0 && $totalPeriodeAkhir!=''){
                        $persentase =  ceil((($totalPeriodeAkhir/$totalPeriodeAwal)-1)*100).'%';
                    }
                }
                $sheet->getStyle("D".$row.":D".($row))->getAlignment()->setHorizontal($horizontal_right);
                $sheet->setCellValue('A'.$row, ''.$valueBulan);
                $sheet->setCellValue('B'.$row, ''.$dppStart);
                $sheet->setCellValue('C'.$row, ''.$dppEnd);
                $sheet->setCellValue('D'.$row, ''.$persentase);

            }
            $totalPerDivisiAwal[$divisi] = $totalPeriodeAwal;
            $totalPerDivisiAkhir[$divisi] = $totalPeriodeAkhir;

            $row+=1;
            $sheet->setCellValue('A'.$row, '');
            $sheet->setCellValue('B'.$row, ''.$totalPeriodeAwal);
            $sheet->setCellValue('C'.$row, ''.$totalPeriodeAkhir);

            $avgAwal = 0;
            $avgAkhir = 0;
            if($totalPeriodeAwal!==0 && $jmlhDataAwal!==0){
                $avgAwal = ($totalPeriodeAwal/$jmlhDataAwal);
            }
            if($totalPeriodeAkhir!==0 && $jmlhDataAkhir!==0){
                $avgAkhir = ($totalPeriodeAkhir/$jmlhDataAkhir);
            }
            
            $row+=1;
            $sheet->setCellValue('A'.$row, 'Avg');
            $sheet->setCellValue('B'.$row, ''.$avgAwal);
            $sheet->setCellValue('C'.$row, ''.$avgAkhir);
            $persentaseAvg = "";
            if($avgAwal!==0 && $avgAkhir!==0){
                $persentaseAvg = ceil((($avgAkhir/$avgAwal)-1)*100)."%";
            }
            
            $row+=1;
            $sheet->getStyle("C".$row.":C".($row))->getAlignment()->setHorizontal($horizontal_right);
            $sheet->setCellValue('A'.$row, '%');
            $sheet->setCellValue('B'.$row, '');
            $sheet->setCellValue('C'.$row, ''.$persentaseAvg);


        }
        $row+=3;
        
    }
    //-------------------------- TOTAL ALL PRODUCT -----------------
    //create style
    $style = $sheet->getStyle('A'.$row.':D'.($row));
    //bold
    $font = $style->getFont();
    $font->setBold(true);
    //wrap
    $alignment = $style->getAlignment();
    $alignment->setWrapText(true);
    //border
    $borders = $style->getBorders();
    $borders->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //align
    $sheet->getStyle("A".$row.":D".($row))->getAlignment()->setHorizontal($horizontal_center);
    $sheet->getStyle("A".$row.":D".($row))->getAlignment()->setVertical($vertical_bottom);


    $persentaseTotal = '';
    $sheet->setCellValue('A'.$row, 'TOTAL ALL PRODUCT');
    $sheet->setCellValue('B'.$row, ''.$year_start);
    $sheet->setCellValue('C'.$row, ''.$year_end);
    $sheet->setCellValue('D'.$row, '');

    $totalBulanAwal = 0;
    $totalBulanAkhir = 0;

    $jmlhDataAwal = 0;
    $jmlhDataAkhir = 0;
    
    foreach($namaBulan as $keyBulan => $valueBulan){
        $index = ($keyBulan-1);
        $row+=1;

        $totalBulanAwal+=$totalPerBulanAwal[$index];
        $totalBulanAkhir+=$totalPerBulanAkhir[$index];

        if($totalPerBulanAwal[$index]!==0 && $totalPerBulanAwal[$index]!=''){
            $jmlhDataAwal+=1;
        }
        if($totalPerBulanAkhir[$index]!==0 && $totalPerBulanAkhir[$index]!=''){
            $jmlhDataAkhir+=1;
        }

        if($totalPerBulanAwal[$index]!==0 && $totalPerBulanAwal[$index]!='' && $totalPerBulanAkhir[$index]!==0 && $totalPerBulanAkhir[$index]!=''){
            $persentaseTotal = ceil((($totalBulanAwal/$totalBulanAkhir)-1)*100);
        }
        $sheet->setCellValue('A'.$row, ''.$valueBulan);
        $sheet->setCellValue('B'.$row, ''.$totalPerBulanAwal[$index]);
        $sheet->setCellValue('C'.$row, ''.$totalPerBulanAkhir[$index]);
    }
    
    $row+=1;
    $sheet->setCellValue('A'.$row, '');
    $sheet->setCellValue('B'.$row, ''.$totalBulanAwal);
    $sheet->setCellValue('C'.$row, ''.$totalBulanAkhir);


    $avgAwal = 0;
    $avgAkhir = 0;
    if($totalBulanAwal!==0 && $jmlhDataAwal!==0){
        $avgAwal = ($totalPeriodeAwal/$jmlhDataAwal);
    }
    if($totalBulanAkhir!==0 && $jmlhDataAkhir!==0){
        $avgAkhir = ($totalPeriodeAkhir/$jmlhDataAkhir);
    }
    $row+=1;
    $sheet->setCellValue('A'.$row, 'Avg');
    $sheet->setCellValue('B'.$row, ''.$avgAwal);
    $sheet->setCellValue('C'.$row, ''.$avgAkhir);


    $persentaseAvg = "";
    if($avgAwal!==0 && $avgAkhir!==0){
        $persentaseAvg = ceil((($avgAkhir/$avgAwal)-1)*100)."%";
    }
    $row+=1;
    $sheet->getStyle("C".$row.":C".($row))->getAlignment()->setHorizontal($horizontal_right);
    $sheet->setCellValue('A'.$row, '%');
    $sheet->setCellValue('B'.$row, '');
    $sheet->setCellValue('C'.$row, ''.$persentaseAvg);

    

    //------------------------------ Omzet Net per Tahun Total Product Terhadap Total All Product ---------------
    $row+=3;
    //create style
    $style = $sheet->getStyle('B'.$row.':C'.($row+1));
    //bold
    $font = $style->getFont();
    $font->setBold(true);
    //wrap
    $alignment = $style->getAlignment();
    $alignment->setWrapText(true);
    //border
    $borders = $style->getBorders();
    $borders->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //align
    $sheet->getStyle("B".$row.":C".($row))->getAlignment()->setHorizontal($horizontal_center);
    $sheet->getStyle("B".$row.":C".($row))->getAlignment()->setVertical($vertical_bottom);

    $sheet->mergeCells('B'.$row.':C'.$row);
    $sheet->setCellValue('B'.$row, '% Omzet Net per Tahun Total Product Terhadap Total All Product');
    
    $row+=1;
    $sheet->setCellValue('B'.$row, ''.$year_start);
    $sheet->setCellValue('C'.$row, ''.$year_end);
    foreach($dataDivisi as $keyDiv => $valueDiv){
        $divisi = $valueDiv['Divisi'];

        $persentaseAwal = 0;
        if($totalPerDivisiAwal[$divisi]!==0 && $totalPerDivisiAwal[$divisi]!='' && $totalBulanAwal!==0 && $totalBulanAwal!=''){
            $persentaseAwal = ceil(($totalPerDivisiAwal[$divisi]/$totalBulanAwal)*100).'%';
        }

        $persentaseAkhir = 0;
        if($totalPerDivisiAkhir[$divisi]!==0 && $totalPerDivisiAkhir[$divisi]!='' && $totalBulanAkhir!==0 && $totalBulanAkhir!=''){
            $persentaseAkhir = ceil(($totalPerDivisiAkhir[$divisi]/$totalBulanAkhir)*100).'%';
        }
        
        $row+=1;
        $sheet->getStyle("B".$row.":C".($row))->getAlignment()->setHorizontal($horizontal_right);
        $sheet->setCellValue('A'.$row, ''.$divisi);
        $sheet->setCellValue('B'.$row, ''.$persentaseAwal);
        $sheet->setCellValue('C'.$row, ''.$persentaseAkhir);
    }

    $row+=3;
    $sheet = $spreadsheet->createSheet();

}




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