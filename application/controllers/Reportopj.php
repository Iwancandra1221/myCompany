<?php 
    header('Access-Control-Allow-Origin:*');
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
    class Reportopj extends MY_Controller 
    {
        public function __construct()
        {
            parent::__construct();
			$this->load->model('HelperModel');
			$this->load->helper('FormLibrary');
			$this->load->library('excel');
            $this->load->model('GzipDecodeModel');
            $this->load->model('ConfigSysModel');
            $this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
        }

        public function RekapOpjFaktur() {
            $data = array();
            $api = 'APITES';
            
            set_time_limit(60);

            $params = array();          
            $params['LogDate'] = date("Y-m-d H:i:s");
            $params['UserID'] = $_SESSION["logged_in"]["userid"];
            $params['UserName'] = $_SESSION["logged_in"]["username"];
            $params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
            $params['Module'] = "REPORT OPJ";
            $params['TrxID'] = date("YmdHis");
            $params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU REPORT OPJ";
            $params['Remarks']="SUCCESS";
            $params['RemarksDate'] = date("Y-m-d H:i:s");
            $this->ActivityLogModel->insert_activity($params);
           
            // print_r($this->API_URL."/ReportBass/GetListCabang?api=".$api."&basspusat=".$BassPusat."&kodecabang=".$KodeCabang);
            // die;

            $listpartnertype = json_decode(file_get_contents($this->API_URL."/MsPartnerType/GetListPartnerType?api=".$api));   
            $data["listpartnertype"] = $listpartnertype;

            $listwilayah = json_decode(file_get_contents($this->API_URL."/MsWilayah/GetListWilayah_ReportOPJ?api=".$api)); 
            $data["listwilayah"] = $listwilayah;

            $listsalesman = file_get_contents($this->API_URL."/MsSalesman/GetListSalesman_ReportOPJ?api=".$api); 
            $listsalesman = $this->GzipDecodeModel->_decodeGzip($listsalesman); 
            $data["listsalesman"] = $listsalesman;

            $listdivisi = json_decode(file_get_contents($this->API_URL."/MsDivisi/GetListDivisionProduct?api=".$api)); 
            $data["listdivisi"] = $listdivisi;

            $data['title'] = 'Laporan OPJ | '.WEBTITLE;
            $data['laporan'] = "claim";
            
            $this->RenderView('ReportOPJView',$data);
        }


        public function RekapOpjFaktur_Proses() {
            $page_title = 'Report OPJ';
            $api = 'APITES';

            set_time_limit(60);
                        
            $tgl1 = date_format(date_create($_POST["dp1"]),'m-d-Y');
            $tgl2 = date_format(date_create($_POST["dp2"]),'m-d-Y');
            $partnertype = $_POST["partnertype"];
            $wilayah = $_POST["wilayah"];

            $parts = explode("|", $_POST["salesman"]);
            $salesman = $parts[0];
            $nmsalesman = $parts[1];

            $divisi = $_POST["divisi"];
            $report = $_POST["report"];
                        
            $proses=$_POST["btnExcel"];
            
            // print_r($this->API_URL."/ReportOPJ/RekapOpjFaktur_Proses?api=".$api
            // ."&page_title=".urlencode($page_title)                                                           
            // ."&tgl1=".urlencode($tgl1)
            // ."&tgl2=".urlencode($tgl2)
            // ."&partnertype=".urlencode($partnertype)
            // ."&wilayah=".urlencode($wilayah)
            // ."&divisi=".urlencode($divisi)
            // ."&report=".urlencode($report));
            // die;

            $mainUrl = $_SESSION["conn"]->AlamatWebService. API_BKT;

            $params = array();          
            $params['LogDate'] = date("Y-m-d H:i:s");
            $params['UserID'] = $_SESSION["logged_in"]["userid"];
            $params['UserName'] = $_SESSION["logged_in"]["username"];
            $params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
            $params['Module'] = "REPORT OPJ";
            $params['TrxID'] = date("YmdHis");
            $params['Description'] = $_SESSION["logged_in"]["username"]." PROSES REPORT OPJ PERIODE ".date("d-M-Y", strtotime($_POST["dp1"]))." S/D ".date("d-M-Y", strtotime($_POST["dp2"]));
            $params['Remarks'] = "";
            $params['RemarksDate'] = 'NULL';
            $this->ActivityLogModel->insert_activity($params);
            
            $url = $mainUrl."/ReportOPJ/ReportOpjFaktur_Proses?api=".$api
                        ."&page_title=".urlencode($page_title)                                                          
                        ."&tgl1=".urlencode($tgl1)
                        ."&tgl2=".urlencode($tgl2)
                        ."&partnertype=".urlencode($partnertype)
                        ."&wilayah=".urlencode($wilayah)
                        ."&salesman=".urlencode($salesman)
                        ."&divisi=".urlencode($divisi)
                        ."&report=".urlencode($report);

            // print_r($url);
            // die;

            // $datalaporan = json_decode(file_get_contents($url));

            $ch = curl_init();

            // Set URL yang akan diambil kontennya
            curl_setopt($ch, CURLOPT_URL, $url);

            // Set opsi untuk mengembalikan respons sebagai string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // Lakukan permintaan HTTP
            $output = curl_exec($ch);

            // Cek apakah permintaan berhasil atau tidak
            if(curl_errno($ch)) {
                echo 'Error: ' . curl_error($ch);
                // die;
            }

            // Tutup curl
            curl_close($ch);

            // print_r($output);
            // die;

            $datalaporan = json_decode(str_replace(',"error":"','}',str_replace(',"error":"}','}',$output)));
            // $datalaporan = json_decode($output);

            if (empty($datalaporan)) {
                $datalaporan = json_decode($output);
            }
            
            // print_r($datalaporan);
            // die;

            if (empty($datalaporan)) {
                $params['Remarks']="FAILED - Data Tidak Ditemukan";
                $params['RemarksDate'] = date("Y-m-d H:i:s");
                $this->ActivityLogModel->update_activity($params);

                echo "Tidak Ada Data !!!";
            }
            else {              
                $periode = "Periode " .$tgl1. " S/D " .$tgl2;
                $partnertype = "Partner Type : " .$partnertype;
                $wilayah = "Wilayah : " .$wilayah;
                $salesman = "Salesman : " .$salesman." - ".$nmsalesman;
                $divisi = "Divisi : " .$divisi;
                
                $judul = "Laporan Rekap";
                if ( $report =="detail" ) {
                    $judul .= " OPJ Detail";

                    $this->ReportOPJDetail_Excel ( $page_title, $datalaporan, $periode, $judul, $partnertype, $wilayah, $salesman, $divisi, $params );
                }
                else if ( $report =="summary" ) {
                    $judul .= " OPJ Summary";

                    $this->ReportOPJSummary_Excel ( $page_title, $datalaporan, $periode, $judul, $partnertype, $wilayah, $salesman, $divisi, $params );
                }
                else if ( $report =="sisatoko" ) {
                    $judul .= " Sisa OPJ Per Toko";

                    $this->ReportOPJSisaToko_Excel ( $page_title, $datalaporan, $periode, $judul, $partnertype, $wilayah, $salesman, $divisi, $params );
                }
                else {
                    $judul .= " Sisa OPJ Per Wilayah";

                    $this->ReportOPJSisaWil_Excel ( $page_title, $datalaporan, $periode, $judul, $partnertype, $wilayah, $salesman, $divisi, $params );
                }           

            }
        }


        public function ReportOPJDetail_Excel ($page_title, $datalaporan, $periode, $judul, $partnertype, $wilayah, $salesman, $divisi, $params) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet(0);
                        
            $sheet->setTitle($page_title);
            $sheet->setCellValue('A1', $judul);
            $sheet->getStyle('A1')->getFont()->setSize(20);
            $sheet->setCellValue('A2', $periode);
            $sheet->setCellValue('A3', $partnertype);
            $sheet->setCellValue('A4', $wilayah);
            $sheet->setCellValue('A5', $salesman);
            $sheet->setCellValue('A6', $divisi);            
                                            
            $currcol = 1;
            $currrow = 8;                           
            
            // Header
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARTNER TYPE');
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'WILAYAH');
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NM SLSMAN');
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DIVISI');
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PELANGGAN');
            $sheet->getColumnDimension('E')->setWidth(35);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA WIL');
            $sheet->getColumnDimension('F')->setWidth(35);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL OPJ');
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO OPJ');
            $sheet->getColumnDimension('H')->setWidth(20);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;          
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KD BRG');
            $sheet->getColumnDimension('I')->setWidth(25);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO FAKTUR');
            $sheet->getColumnDimension('J')->setWidth(20);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY');
            $sheet->getColumnDimension('K')->setWidth(10);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY SI');
            $sheet->getColumnDimension('L')->setWidth(10);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY SISA');
            $sheet->getColumnDimension('M')->setWidth(10);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;

            $max_col = $currcol-2;
            
            $fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;          

            $currrow += 1;
                        
            // print_r($datalaporan->data);
            // die;

            // Detail
            $jum= count($datalaporan->data);
            for($i=0; $i<$jum; $i++){
                
            // Wilayah as WILAYAH, Nm_Plg as NM_PLG, Kd_Plg as KD_PLG, Kd_Wil as KD_WIL, Nm_Wil as NM_WIL, 
            // No_OPJ as NO_OPJ, No_Faktur as NO_FAKTUR, Tgl_OPJ as TGL_OPJ, Kd_Brg as KD_BRG, Divisi as DIVISI,
            // Qty as QTY, Qty_SI as QTY_SI, QtySisa as QTYSISA, Kd_Slsman as KD_SLSMAN, 
            // Nm_Slsman as NM_SLSMAN, QtyOPJ as QTYOPJ, Partner_Type as  PARTNER_TYPE

            // ORDER BY PARTNER_TYPE, Wilayah, Kd_Slsman, Divisi, Kd_Plg, Nm_Wil, Tgl_OPJ, No_OPJ, Kd_Brg
                                
                $currrow++;
                $currcol = 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->PARTNER_TYPE);
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->WILAYAH);
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NM_SLSMAN);
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->DIVISI);
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->KD_PLG." - ".$datalaporan->data[$i]->NM_PLG);
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NM_WIL);
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-m-Y",strtotime($datalaporan->data[$i]->TGL_OPJ)));
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_OPJ);
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->KD_BRG);
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_FAKTUR);
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->QTY));
                $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $currcol += 1;              
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->QTY_SI));
                $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $currcol += 1;  
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->QTYSISA));
                $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $currcol += 1;              
            }
            
            // print_r ($judul);

            $max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
            
            // rata tengah header
            $alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
            $sheet->getStyle('A8:'.$max_col.'9')->getAlignment()->setHorizontal($alignment_center);
            
            // warna header
            $fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
            $sheet->getStyle('A8:'.$max_col.'9')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
            
            // border
            $sheet->getStyle("A1:".$max_col."9")->getFont()->setBold(true);
            $styleArray = [
            'borders' => [
            'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            ],
            ],
            ];
            $sheet->getStyle('A8:'.$max_col.$currrow)->applyFromArray($styleArray);
            $sheet->setSelectedCell('A1');
            
                
            $filename= $judul.' ['.date('Ymd').']'; //save our workbook as this file name
            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
            header('Cache-Control: max-age=0');
            ob_end_clean();
            $writer->save('php://output');  // download file 
            exit();
        }

        public function ReportOPJSummary_Excel ($page_title, $datalaporan, $periode, $judul, $partnertype, $wilayah, $salesman, $divisi, $params) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet(0);
                        
            $sheet->setTitle($page_title);
            $sheet->setCellValue('A1', $judul);
            $sheet->getStyle('A1')->getFont()->setSize(20);
            $sheet->setCellValue('A2', $periode);
            $sheet->setCellValue('A3', $partnertype);
            $sheet->setCellValue('A4', $wilayah);
            $sheet->setCellValue('A5', $salesman);
            $sheet->setCellValue('A6', $divisi);            
                                            
            $currcol = 1;
            $currrow = 8;                           
            
            // Header
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARTNER TYPE');
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'WILAYAH');
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NM SLSMAN');
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DIVISI');
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PELANGGAN');
            $sheet->getColumnDimension('E')->setWidth(35);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL OPJ');
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO OPJ');
            $sheet->getColumnDimension('G')->setWidth(20);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;          
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KD BRG');
            $sheet->getColumnDimension('H')->setWidth(25);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY');
            $sheet->getColumnDimension('I')->setWidth(10);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY SI');
            $sheet->getColumnDimension('J')->setWidth(10);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY SISA');
            $sheet->getColumnDimension('K')->setWidth(10);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;

            $max_col = $currcol-2;
            
            $fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;          

            $currrow += 1;
                        
            // print_r($datalaporan->data);
            // die;

            // Detail
            $jum= count($datalaporan->data);
            for($i=0; $i<$jum; $i++){
                
            // Wilayah as WILAYAH, Nm_Plg as NM_PLG, Kd_Plg as KD_PLG, Kd_Wil as KD_WIL, Nm_Wil as NM_WIL, 
            // No_OPJ as NO_OPJ, No_Faktur as NO_FAKTUR, Tgl_OPJ as TGL_OPJ, Kd_Brg as KD_BRG, Divisi as DIVISI,
            // Qty as QTY, Qty_SI as QTY_SI, QtySisa as QTYSISA, Kd_Slsman as KD_SLSMAN, 
            // Nm_Slsman as NM_SLSMAN, QtyOPJ as QTYOPJ, Partner_Type as  PARTNER_TYPE

            // ORDER BY Partner_Type, Wilayah, Kd_Slsman, Divisi, Kd_Plg, Tgl_OPJ, No_OPJ, Kd_Brg,
                                
                $currrow++;
                $currcol = 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->PARTNER_TYPE);
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->WILAYAH);
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NM_SLSMAN);
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->DIVISI);
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->KD_PLG." - ".$datalaporan->data[$i]->NM_PLG);
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-m-Y",strtotime($datalaporan->data[$i]->TGL_OPJ)));
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->NO_OPJ);
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $datalaporan->data[$i]->KD_BRG);
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->QTY));
                $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $currcol += 1;              
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->QTY_SI));
                $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $currcol += 1;  
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($datalaporan->data[$i]->QTYSISA));
                $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $currcol += 1;              
            }
            
            // print_r ($judul);

            $max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
            
            // rata tengah header
            $alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
            $sheet->getStyle('A8:'.$max_col.'9')->getAlignment()->setHorizontal($alignment_center);
            
            // warna header
            $fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
            $sheet->getStyle('A8:'.$max_col.'9')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
            
            // border
            $sheet->getStyle("A1:".$max_col."9")->getFont()->setBold(true);
            $styleArray = [
            'borders' => [
            'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            ],
            ],
            ];
            $sheet->getStyle('A8:'.$max_col.$currrow)->applyFromArray($styleArray);
            $sheet->setSelectedCell('A1');
            
                
            $filename= $judul.' ['.date('Ymd').']'; //save our workbook as this file name
            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
            header('Cache-Control: max-age=0');
            ob_end_clean();
            $writer->save('php://output');  // download file 
            exit();
        }

        public function ReportOPJSisaToko_Excel ($page_title, $datalaporan, $periode, $judul, $partnertype, $wilayah, $salesman, $divisi, $params) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet(0);
                        
            $sheet->setTitle($page_title);
            $sheet->setCellValue('A1', $judul);
            $sheet->getStyle('A1')->getFont()->setSize(20);
            $sheet->setCellValue('A2', $periode);
            $sheet->setCellValue('A3', $partnertype);
            $sheet->setCellValue('A4', $wilayah);
            $sheet->setCellValue('A5', $salesman);
            $sheet->setCellValue('A6', $divisi);            
                                            
            $currcol = 1;
            $currrow = 8;                           
            
            // Header
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARTNER TYPE');
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'WILAYAH');
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NM SLSMAN');
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DIVISI');
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PELANGGAN');
            $sheet->getColumnDimension('E')->setWidth(35);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;  
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KD BRG');
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY OPJ');
            $sheet->getColumnDimension('G')->setWidth(10);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY SISA');
            $sheet->getColumnDimension('H')->setWidth(10);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;

            $max_col = $currcol-2;
            
            $fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;          

            $currrow += 1;
                        
            // print_r($datalaporan->data);
            // die;

            $xPartner_Type = "!@#$%";
            $xWilayah = "!@#$%";
            $xNm_Slsman = "!@#$%";
            $xDivisi = "!@#$%";
            $xKd_Plg = "!@#$%";
            $xKd_Brg = "!@#$%";
            $xQty_OPJ = 0;
            $xQty_Sisa = 0;

            // Detail
            $jum= count($datalaporan->data);
            for($i=0; $i<$jum; $i++){
                
            // Wilayah as WILAYAH, Nm_Plg as NM_PLG, Kd_Plg as KD_PLG, Kd_Wil as KD_WIL, Nm_Wil as NM_WIL, 
            // No_OPJ as NO_OPJ, No_Faktur as NO_FAKTUR, Tgl_OPJ as TGL_OPJ, Kd_Brg as KD_BRG, Divisi as DIVISI,
            // Qty as QTY, Qty_SI as QTY_SI, QtySisa as QTYSISA, Kd_Slsman as KD_SLSMAN, 
            // Nm_Slsman as NM_SLSMAN, QtyOPJ as QTYOPJ, Partner_Type as  PARTNER_TYPE

            // ORDER BY Partner_Type, Wilayah, Kd_Slsman, Divisi, Kd_Plg, Kd_Brg
                                
                if ( $xPartner_Type != $datalaporan->data[$i]->PARTNER_TYPE ) {
                    if ( $xKd_Brg != "!@#$%" && $xKd_Brg != $datalaporan->data[$i]->KD_BRG ) {
                        $currrow++;
                        $currcol = 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xPartner_Type);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xWilayah);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xNm_Slsman);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xDivisi);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Plg);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Brg);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_OPJ));
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $currcol += 1;              
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_Sisa));
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $currcol += 1;  

                        $xQty_Sisa = 0;
                    }
                }
                else if ( $xWilayah != $datalaporan->data[$i]->WILAYAH ) {
                    if ( $xKd_Brg != "!@#$%" && $xKd_Brg != $datalaporan->data[$i]->KD_BRG ) {
                        $currrow++;
                        $currcol = 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xPartner_Type);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xWilayah);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xNm_Slsman);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xDivisi);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Plg);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Brg);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_OPJ));
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $currcol += 1;              
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_Sisa));
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $currcol += 1;  

                        $xQty_Sisa = 0;
                    }
                }
                else if ( $xNm_Slsman != $datalaporan->data[$i]->NM_SLSMAN ) {
                    if ( $xKd_Brg != "!@#$%" && $xKd_Brg != $datalaporan->data[$i]->KD_BRG ) {
                        $currrow++;
                        $currcol = 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xPartner_Type);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xWilayah);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xNm_Slsman);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xDivisi);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Plg);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Brg);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_OPJ));
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $currcol += 1;              
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_Sisa));
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $currcol += 1;  

                        $xQty_Sisa = 0;
                    }
                }
                else if ( $xDivisi != $datalaporan->data[$i]->DIVISI ) {
                    if ( $xKd_Brg != "!@#$%" && $xKd_Brg != $datalaporan->data[$i]->KD_BRG ) {
                        $currrow++;
                        $currcol = 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xPartner_Type);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xWilayah);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xNm_Slsman);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xDivisi);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Plg);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Brg);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_OPJ));
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $currcol += 1;              
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_Sisa));
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $currcol += 1;  

                        $xQty_Sisa = 0;
                    }
                }
                else if ( $xKd_Plg != $datalaporan->data[$i]->KD_PLG." - ".$datalaporan->data[$i]->NM_PLG) {
                    if ( $xKd_Brg != "!@#$%" && $xKd_Brg != $datalaporan->data[$i]->KD_BRG ) {
                        $currrow++;
                        $currcol = 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xPartner_Type);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xWilayah);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xNm_Slsman);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xDivisi);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Plg);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Brg);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_OPJ));
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $currcol += 1;              
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_Sisa));
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $currcol += 1;  

                        $xQty_Sisa = 0;
                    }
                }
                else if ( $xKd_Brg != "!@#$%" ) {
                    $currrow++;
                    $currcol = 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xPartner_Type);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xWilayah);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xNm_Slsman);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xDivisi);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Plg);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Brg);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_OPJ));
                    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $currcol += 1;              
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_Sisa));
                    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $currcol += 1;  

                    $xQty_Sisa = 0;
                }   
                
                $xPartner_Type = $datalaporan->data[$i]->PARTNER_TYPE;
                $xWilayah = $datalaporan->data[$i]->WILAYAH;
                $xNm_Slsman = $datalaporan->data[$i]->NM_SLSMAN;
                $xDivisi = $datalaporan->data[$i]->DIVISI;
                $xKd_Plg = $datalaporan->data[$i]->KD_PLG." - ".$datalaporan->data[$i]->NM_PLG;
                $xKd_Brg = $datalaporan->data[$i]->KD_BRG;
                $xQty_OPJ = $datalaporan->data[$i]->QTYOPJ;
                $xQty_Sisa += $datalaporan->data[$i]->QTYSISA;
            }
            
            $currrow++;
            $currcol = 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xPartner_Type);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xWilayah);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xNm_Slsman);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xDivisi);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Plg);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Brg);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_OPJ));
            $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $currcol += 1;              
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_Sisa));
            $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $currcol += 1;  


            // print_r ($judul);

            $max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
            
            // rata tengah header
            $alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
            $sheet->getStyle('A8:'.$max_col.'9')->getAlignment()->setHorizontal($alignment_center);
            
            // warna header
            $fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
            $sheet->getStyle('A8:'.$max_col.'9')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
            
            // border
            $sheet->getStyle("A1:".$max_col."9")->getFont()->setBold(true);
            $styleArray = [
            'borders' => [
            'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            ],
            ],
            ];
            $sheet->getStyle('A8:'.$max_col.$currrow)->applyFromArray($styleArray);
            $sheet->setSelectedCell('A1');
            
                
            $filename= $judul.' ['.date('Ymd').']'; //save our workbook as this file name
            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
            header('Cache-Control: max-age=0');
            ob_end_clean();
            $writer->save('php://output');  // download file 
            exit();
        }

        public function ReportOPJSisaWil_Excel ($page_title, $datalaporan, $periode, $judul, $partnertype, $wilayah, $salesman, $divisi, $params) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet(0);
                        
            $sheet->setTitle($page_title);
            $sheet->setCellValue('A1', $judul);
            $sheet->getStyle('A1')->getFont()->setSize(20);
            $sheet->setCellValue('A2', $periode);
            $sheet->setCellValue('A3', $partnertype);
            $sheet->setCellValue('A4', $wilayah);
            $sheet->setCellValue('A5', $salesman);
            $sheet->setCellValue('A6', $divisi);            
                                            
            $currcol = 1;
            $currrow = 8;                           
            
            // Header
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARTNER TYPE');
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;          
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NM SLSMAN');
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DIVISI');
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'WILAYAH');
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KD BRG');
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY OPJ');
            $sheet->getColumnDimension('F')->setWidth(10);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY SISA');
            $sheet->getColumnDimension('G')->setWidth(10);
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;

            $max_col = $currcol-2;
            
            $fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;          

            $currrow += 1;
                        
            // print_r($datalaporan->data);
            // die;

            $xPartner_Type = "!@#$%";           
            $xNm_Slsman = "!@#$%";
            $xDivisi = "!@#$%";
            $xWilayah = "!@#$%";
            $xKd_Brg = "!@#$%";
            $xQty_OPJ = 0;
            $xQty_Sisa = 0;

            // Detail
            $jum= count($datalaporan->data);
            for($i=0; $i<$jum; $i++){
                
            // Wilayah as WILAYAH, Nm_Plg as NM_PLG, Kd_Plg as KD_PLG, Kd_Wil as KD_WIL, Nm_Wil as NM_WIL, 
            // No_OPJ as NO_OPJ, No_Faktur as NO_FAKTUR, Tgl_OPJ as TGL_OPJ, Kd_Brg as KD_BRG, Divisi as DIVISI,
            // Qty as QTY, Qty_SI as QTY_SI, QtySisa as QTYSISA, Kd_Slsman as KD_SLSMAN, 
            // Nm_Slsman as NM_SLSMAN, QtyOPJ as QTYOPJ, Partner_Type as  PARTNER_TYPE

            // ORDER BY Partner_Type, Kd_Slsman,  Divisi, Wilayah, Kd_Brg
                                
                if ( $xPartner_Type != $datalaporan->data[$i]->PARTNER_TYPE ) {
                    if ( $xKd_Brg != "!@#$%" && $xKd_Brg != $datalaporan->data[$i]->KD_BRG ) {
                        $currrow++;
                        $currcol = 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xPartner_Type);
                        $currcol += 1;                      
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xNm_Slsman);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xDivisi);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xWilayah);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Brg);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_OPJ));
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $currcol += 1;              
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_Sisa));
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $currcol += 1;  

                        $xQty_Sisa = 0;
                    }
                }               
                else if ( $xNm_Slsman != $datalaporan->data[$i]->NM_SLSMAN ) {
                    if ( $xKd_Brg != "!@#$%" && $xKd_Brg != $datalaporan->data[$i]->KD_BRG ) {
                        $currrow++;
                        $currcol = 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xPartner_Type);
                        $currcol += 1;                      
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xNm_Slsman);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xDivisi);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xWilayah);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Brg);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_OPJ));
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $currcol += 1;              
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_Sisa));
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $currcol += 1;  

                        $xQty_Sisa = 0;
                    }
                }
                else if ( $xDivisi != $datalaporan->data[$i]->DIVISI ) {
                    if ( $xKd_Brg != "!@#$%" && $xKd_Brg != $datalaporan->data[$i]->KD_BRG ) {
                        $currrow++;
                        $currcol = 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xPartner_Type);
                        $currcol += 1;                      
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xNm_Slsman);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xDivisi);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xWilayah);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Brg);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_OPJ));
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $currcol += 1;              
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_Sisa));
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $currcol += 1;  

                        $xQty_Sisa = 0;
                    }
                }
                else if ( $xWilayah != $datalaporan->data[$i]->WILAYAH ) {
                    if ( $xKd_Brg != "!@#$%" && $xKd_Brg != $datalaporan->data[$i]->KD_BRG ) {
                        $currrow++;
                        $currcol = 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xPartner_Type);
                        $currcol += 1;                      
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xNm_Slsman);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xDivisi);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xWilayah);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Brg);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_OPJ));
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $currcol += 1;              
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_Sisa));
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $currcol += 1;  

                        $xQty_Sisa = 0;
                    }
                }               
                else if ( $xKd_Brg != "!@#$%" ) {
                    $currrow++;
                    $currcol = 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xPartner_Type);
                    $currcol += 1;                      
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xNm_Slsman);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xDivisi);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xWilayah);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Brg);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_OPJ));
                    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $currcol += 1;              
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_Sisa));
                    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $currcol += 1;  

                    $xQty_Sisa = 0;
                }   
                
                $xPartner_Type = $datalaporan->data[$i]->PARTNER_TYPE;              
                $xNm_Slsman = $datalaporan->data[$i]->NM_SLSMAN;
                $xDivisi = $datalaporan->data[$i]->DIVISI;
                $xWilayah = $datalaporan->data[$i]->WILAYAH;
                $xKd_Brg = $datalaporan->data[$i]->KD_BRG;
                $xQty_OPJ = $datalaporan->data[$i]->QTYOPJ;
                $xQty_Sisa += $datalaporan->data[$i]->QTYSISA;
            }
            
            $currrow++;
            $currcol = 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xPartner_Type);
            $currcol += 1;          
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xNm_Slsman);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xDivisi);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xWilayah);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $xKd_Brg);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_OPJ));
            $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $currcol += 1;              
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($xQty_Sisa));
            $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $currcol += 1;  


            // print_r ($judul);

            $max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
            
            // rata tengah header
            $alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
            $sheet->getStyle('A8:'.$max_col.'9')->getAlignment()->setHorizontal($alignment_center);
            
            // warna header
            $fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
            $sheet->getStyle('A8:'.$max_col.'9')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
            
            // border
            $sheet->getStyle("A1:".$max_col."9")->getFont()->setBold(true);
            $styleArray = [
            'borders' => [
            'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            ],
            ],
            ];
            $sheet->getStyle('A8:'.$max_col.$currrow)->applyFromArray($styleArray);
            $sheet->setSelectedCell('A1');
            
                
            $filename= $judul.' ['.date('Ymd').']'; //save our workbook as this file name
            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
            header('Cache-Control: max-age=0');
            ob_end_clean();
            $writer->save('php://output');  // download file 

            if ($jum==0){
                $params['Remarks']="FAILED - Data Tidak Ditemukan";
                $params['RemarksDate'] = date("Y-m-d H:i:s");
                $this->ActivityLogModel->update_activity($params);
            } else {
                $params['Remarks']="SUCCESS";
                $params['RemarksDate'] = date("Y-m-d H:i:s");
                $this->ActivityLogModel->update_activity($params);
            } 

            exit();
        }

        public function index() 
        {
            //http://localhost:90/myCompany/Reportopj/index
			$data = array();
			$api = 'APITES';
			set_time_limit(60);

            //Periode


            //Wilayah
            //http://localhost:90/webAPI/MsDealer/GetListWilayah?api=APITES
            $url = $this->API_URL."/MsDealer/GetListWilayah?api=".$api;
            //die($url);
            // open connection
            $curl = curl_init();
            // set the url, number of POST vars, POST data
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_POST => 1,
                CURLOPT_HTTPHEADER => array("Content-type: application/json")
            ));
            // execute post
            $response = curl_exec($curl);
            $err = curl_error($curl);
            // close connection
            curl_close($curl);

			// $hasil = json_decode($response);
            $hasil = $this->GzipDecodeModel->_decodeGzip($response); 
            
			if ($hasil->result == "sukses") {
				$data["wilayah"] = $hasil->data;
			} else {
				$data["wilayah"] = "";
			}
            //die($data["wilayah"]);

            //Salesman
            //http://localhost:90/webAPI/MsSalesman/GetSalesmanList
            $url = $this->API_URL."/MsSalesman/GetSalesmanList";
            //die($url);
            // open connection
            $curl = curl_init();
            // set the url, number of POST vars, POST data
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_POST => 1,
                CURLOPT_HTTPHEADER => array("Content-type: application/json")
            ));
            // execute post
            $response = curl_exec($curl);
            $err = curl_error($curl);
            // close connection
            curl_close($curl);

			// $hasil = json_decode($response);
            $hasil = $this->GzipDecodeModel->_decodeGzip($response);
			if ($hasil->result == "sukses") {
				$data["salesman"] = $hasil->data;
			} else {
				$data["salesman"] = "";
			}
            //die($data["salesman"]);

            //Merk
            //http://localhost:90/webAPI/MasterService/GetsMerek?api=APITES
            $url = $this->API_URL."/MasterService/GetsMerek?api=".$api;
            //die($url);
            // open connection
            $curl = curl_init();
            // set the url, number of POST vars, POST data
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_POST => 1,
                CURLOPT_HTTPHEADER => array("Content-type: application/json")
            ));
            // execute post
            $response = curl_exec($curl);
            $err = curl_error($curl);
            // close connection
            curl_close($curl);

            // $hasil = json_decode($response);
            $hasil = $this->GzipDecodeModel->_decodeGzip($response);
			if ($hasil->result == "sukses") {
				$data["merk"] = $hasil->data;
			} else {
				$data["merk"] = "";
			}
            //die($data["merk"]);

            //Divisi
            //http://localhost:90/webAPI/MsDivisi/GetListDivisi?api=APITES
            $url = $this->API_URL."/MsDivisi/GetListDivisi?api=".$api;
            //die($url);
            // open connection
            $curl = curl_init();
            // set the url, number of POST vars, POST data
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_POST => 1,
                CURLOPT_HTTPHEADER => array("Content-type: application/json")
            ));
            // execute post
            $response = curl_exec($curl);
            $err = curl_error($curl);
            // close connection
            curl_close($curl);

            // $hasil = json_decode($response);
            $hasil = $this->GzipDecodeModel->_decodeGzip($response);
			if ($response!=null) {
				$data["divisi"] = $hasil;
			} else {
				$data["divisi"] = "";
			}
            //die($data["divisi"]);

            //PartnerType
            //http://localhost:90/webAPI/MsDealer/GetListPartnerType?api=APITES
            $url = $this->API_URL."/MsDealer/GetListPartnerType?api=".$api;
            //die($url);
            // open connection
            $curl = curl_init();
            // set the url, number of POST vars, POST data
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_POST => 1,
                CURLOPT_HTTPHEADER => array("Content-type: application/json")
            ));
            // execute post
            $response = curl_exec($curl);
            $err = curl_error($curl);
            // close connection
            curl_close($curl);

            // $hasil = json_decode($response);
            $hasil = $this->GzipDecodeModel->_decodeGzip($response);
			if ($hasil->result == "sukses") {
				$data["partnertype"] = $hasil->data;
			} else {
				$data["partnertype"] = "";
			}
            //die($data["partnertype"]);

            $data["CabangSelected"] = "";

			$data['title'] = 'Laporan Rekap OPJ Bulanan';
			$this->RenderView('LaporanRekapOPJBulananView',$data);
        }        
    
        public function Report_6N2_OPJ() 
        {
            $keyAPI = 'APITES';
            $api = 'APITES';
            set_time_limit(60);
    
            // // untuk TEST
            // //http://localhost:90/myCompany/Reportopj/Report_6N2_OPJ?api=APITES&wilayah=BANDUNG&salesman=ALL&merk=ALL&bulan=1&tahun=2022&divisi=ALL&partnertype=ALL
            // $param["wilayah"] = urldecode($this->input->get("wilayah"));
            // $param["salesman"] = urldecode($this->input->get("salesman"));
            // $param["merk"] = urldecode($this->input->get("merk"));
            // $param["bulan"] = urldecode($this->input->get("bulan"));
            // $param["tahun"] = urldecode($this->input->get("tahun"));
            // $param["divisi"] = urldecode($this->input->get("divisi"));
            // $param["partnertype"] = urldecode($this->input->get("partnertype"));

			$_POST = $this->PopulatePost();	
            $param["wilayah"] = urldecode($_POST["wilayah"]);
            $param["salesman"] = urldecode($_POST["salesman"]);
            $param["merk"] = urldecode($_POST["merk"]);
            $param["bulan"] = urldecode($_POST["bulan"]);
            $param["tahun"] = urldecode($_POST["tahun"]);
            $param["divisi"] = urldecode($_POST["divisi"]);
            $param["partnertype"] = urldecode($_POST["partnertype"]);
            // // echo json_encode($_POST);
            // // die();
    
            if($keyAPI==$api) {
                $array_data = array();
    
                $res = $this->MasterDbModel->getByLocationCode($_SESSION['logged_in']['branch_id']);
                $AlamatWebService = $res->AlamatWebService;
                $url = $AlamatWebService.API_BKT."/Reportopj/Report_6N2_OPJ?api=".$api."&wilayah=".$param["wilayah"]."&salesman=".$param["salesman"]."&merk=".$param["merk"]."&bulan=".$param["bulan"]."&tahun=".$param["tahun"]."&divisi=".$param["divisi"]."&partnertype=".$param["partnertype"];
                // open connection
                $curl = curl_init();
                // set the url, number of POST vars, POST data
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_POST => 1,
                    CURLOPT_HTTPHEADER => array("Content-type: application/json")
                ));
                // execute post
                $response = curl_exec($curl);
                $err = curl_error($curl);
                // close connection
                curl_close($curl);

                $hasil = json_decode($response);
                if ($hasil->result == "sukses") {
                    $result["result"] = "sukses";
                    $result["data"] = $hasil->data;
                    $result["error"] = "";
                } else {
                    $result["result"] = "gagal";
                    $result["data"] = null;
                    $result["error"] = "Tidak ada Data";
                }

            } else {
                $result["result"] = "gagal";
                $result["data"] = null;
                $result["error"] = "kode API salah";
            }
            
            // $hasil = json_encode($result);
            // header('HTTP/1.1: 200');
            // header('Status: 200');
            // header('Content-Length: '.strlen($hasil));
            // exit($hasil);

            
            $page_title = "Laporan Rekap OPJ Bulanan";

            if($param["bulan"]==1){
                $namabulan='JANUARI';
            } elseif($param["bulan"]==2){
                $namabulan='FEBRUARI';
            } elseif($param["bulan"]==3){
                $namabulan='MARET';
            } elseif($param["bulan"]==4){
                $namabulan='APRIL';
            } elseif($param["bulan"]==5){
                $namabulan='MEI';
            } elseif($param["bulan"]==6){
                $namabulan='JUNI';
            } elseif($param["bulan"]==7){
                $namabulan='JULI';
            } elseif($param["bulan"]==8){
                $namabulan='AGUSTUS';
            } elseif($param["bulan"]==9){
                $namabulan='SEPTEMBER';
            } elseif($param["bulan"]==10){
                $namabulan='OKTOBER';
            } elseif($param["bulan"]==11){
                $namabulan='NOVEMBER';
            } elseif($param["bulan"]==12){
                $namabulan='DESEMBER';
            } 

            $tahun = $param["tahun"];

            if(isset($_POST['btnPDF'])) {
                $this->proses_pdf_html($page_title, $namabulan, $tahun, $result["data"],'PDF');
            } elseif(isset($_POST['btnHTML'])) {
                $this->proses_pdf_html($page_title, $namabulan, $tahun, $result["data"],'HTML');
            } 
    
        }

		public function proses_pdf_html ($page_title, $namabulan, $tahun, $DataLaporan, $output)
		{	
			//ini_set('max_execution_time', '1500');
			ini_set("pcre.backtrack_limit", "10000000");
			set_time_limit(60);

			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'arial',
				'margin_left' => 10,
				'margin_right' => 10,
				'margin_top' => 30,
				'margin_bottom' => 10,
				'margin_header' => 50,
				'margin_footer' => 5,
				'orientation' => 'P'
			));

			$header = '<table border="0" style="width:100%; font-size:15px;">
					<tr>
						<td align="center">
							<b>
								'.$page_title.'
							</b>
						</td>
					</tr>
					<tr>
						<td align="center" style="font-size:12px;">
							PERIODE '.$namabulan.' <b> </b> '.$tahun.'
						</td>
					</tr>
				</table>';

            $content = '
            <table width="100%">
                <tr>
                    <td></td>
                </tr>
            </table>';

			$no = 0;

			$Sum_Qty = 0;
			$Sum_Qty_SI = 0;
            $Sum_QtySisa = 0;

			$GrandTotal_Qty = 0;
			$GrandTotal_Qty_SI = 0;
            $GrandTotal_QtySisa = 0;

            $count_salesman = 0;
            $nama_salesman = "";

            $count_partnertype = 0;
            $nama_partnertype = "";

            $count_dealer = 0;
            $nama_dealer = "";

            $count_merk = 0;
            $nama_merk = "";

            $show_total_per_dealer=0;

			$jum= count($DataLaporan);
			for($i=0; $i<$jum; $i++){

                if (
                    ((($nama_salesman!="") && ($nama_salesman==$DataLaporan[$i]->Nm_Slsman))
                    && (($nama_partnertype!="") && ($nama_partnertype==$DataLaporan[$i]->Partner_Type))
                    && (($nama_dealer!="") && ($nama_dealer!=$DataLaporan[$i]->Nm_Plg)))
                ||
                    ((($nama_salesman!="") && ($nama_salesman==$DataLaporan[$i]->Nm_Slsman))
                    && (($nama_partnertype!="") && ($nama_partnertype!=$DataLaporan[$i]->Partner_Type))
                    && (($nama_dealer!="") && ($nama_dealer!=$DataLaporan[$i]->Nm_Plg)))
                ||
                    ((($nama_salesman!="") && ($nama_salesman!=$DataLaporan[$i]->Nm_Slsman))
                    && (($nama_partnertype!="") && ($nama_partnertype!=$DataLaporan[$i]->Partner_Type))
                    && (($nama_dealer!="") && ($nama_dealer!=$DataLaporan[$i]->Nm_Plg)))            
                ) {
                    $show_total_per_dealer=1; 
                }

                if (($nama_salesman=="") || (($nama_salesman!="") && ($nama_salesman!=$DataLaporan[$i]->Nm_Slsman))) {

                    //tampilkan subtotal dealer sebelumnya, from here
                    if ($show_total_per_dealer==1){
                        if ($Sum_Qty == 0){
                            $Sum_Percentase = 100;
                        } else {
                            $Sum_Percentase = ($Sum_Qty_SI / $Sum_Qty) * 100;
                        }

                        $content .='<tr>
                                        <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;"></td>
                                        <td style="text-align: left; width: 10%; font-size: 12px; padding:5px;">SUBTOTAL</td>
                                        <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($Sum_Qty,0,",",".").'</td>
                                        <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($Sum_Qty_SI,0,",",".").'</td>
                                        <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($Sum_QtySisa,0,",",".").'</td>
                                        <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($Sum_Percentase,0,",",".").' % </td>
                                    </tr></table>';	 
                                    
                        $Sum_Qty = 0;
                        $Sum_Qty_SI = 0;
                        $Sum_QtySisa = 0;
                        $show_total_per_dealer=0;
                    }
                    //tampilkan subtotal dealer sebelumnya, until here

                    $content .='<table width="100%"><tr>
                                    <td align="left" style="font-size:12px;">
                                    SALESMAN '.$DataLaporan[$i]->Nm_Slsman.'
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr></table>';

                    $count_salesman += 1;
                    $nama_salesman = $DataLaporan[$i]->Nm_Slsman;
                } 

                if (((($nama_salesman!="") && ($nama_salesman==$DataLaporan[$i]->Nm_Slsman))
                    && (($nama_partnertype=="") || (($nama_partnertype!="") && ($nama_partnertype!=$DataLaporan[$i]->Partner_Type))))
                    ||
                    ((($nama_salesman!="") && ($nama_salesman!=$DataLaporan[$i]->Nm_Slsman))
                    && (($nama_partnertype=="") || (($nama_partnertype!="") && ($nama_partnertype!=$DataLaporan[$i]->Partner_Type))))) {

                    //tampilkan subtotal dealer sebelumnya, from here
                    if ($show_total_per_dealer==1){
                        if ($Sum_Qty == 0){
                            $Sum_Percentase = 100;
                        } else {
                            $Sum_Percentase = ($Sum_Qty_SI / $Sum_Qty) * 100;
                        }

                        $content .='<tr>
                                        <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;"></td>
                                        <td style="text-align: left; width: 10%; font-size: 12px; padding:5px;">SUBTOTAL</td>
                                        <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($Sum_Qty,0,",",".").'</td>
                                        <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($Sum_Qty_SI,0,",",".").'</td>
                                        <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($Sum_QtySisa,0,",",".").'</td>
                                        <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($Sum_Percentase,0,",",".").' % </td>
                                    </tr></table>';	 
                                    
                        $Sum_Qty = 0;
                        $Sum_Qty_SI = 0;
                        $Sum_QtySisa = 0;
                        $show_total_per_dealer=0;
                    }
                    //tampilkan subtotal dealer sebelumnya, until here

                        $content .='<table width="100%"><tr>
                                    <td align="left" style="font-size:12px;">
                                    PARTNER TYPE '.$DataLaporan[$i]->Partner_Type.'
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr></table>';

                    $count_partnertype += 1;
                    $nama_partnertype = $DataLaporan[$i]->Partner_Type;
                } 

                if (((($nama_partnertype!="") && ($nama_partnertype==$DataLaporan[$i]->Partner_Type))
                    && (($nama_dealer=="") || (($nama_dealer!="") && ($nama_dealer!=$DataLaporan[$i]->Nm_Plg))))
                    ||
                    ((($nama_partnertype!="") && ($nama_partnertype!=$DataLaporan[$i]->Partner_Type))
                    && (($nama_dealer=="") || (($nama_dealer!="") && ($nama_dealer!=$DataLaporan[$i]->Nm_Plg))))) {

                        //tampilkan subtotal dealer sebelumnya, from here
                        if ($show_total_per_dealer==1){
                            if ($Sum_Qty == 0){
                                $Sum_Percentase = 100;
                            } else {
                                $Sum_Percentase = ($Sum_Qty_SI / $Sum_Qty) * 100;
                            }

                            $content .='<tr>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;"></td>
                            <td style="text-align: left; width: 10%; font-size: 12px; padding:5px;">SUBTOTAL</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($Sum_Qty,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($Sum_Qty_SI,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($Sum_QtySisa,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($Sum_Percentase,0,",",".").' % </td>
                                        </tr></table>';	 
                                        
                            $Sum_Qty = 0;
                            $Sum_Qty_SI = 0;
                            $Sum_QtySisa = 0;
                            $show_total_per_dealer=0;
                        }
                        //tampilkan subtotal dealer sebelumnya, until here

                        $content .='<table width="100%"><tr><tr>
                                <td align="left" style="font-size:12px;">
                                DEALER '.$DataLaporan[$i]->Nm_Plg.'
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                </tr></table>';

                    $no=0;
                    
                    $content .= '<table style="width:100%; border-collapse: collapse;" border="1">
                                <tr>
                                    <th style="text-align: left; width: 10%; font-size: 12px; padding:5px;">NO.</th>
                                    <th style="text-align: left; width: 10%; font-size: 12px; padding:5px;">KODE BARANG</th>
                                    <th style="text-align: left; width: 10%; font-size: 12px; padding:5px;" align="right">QTY</th>
                                    <th style="text-align: left; width: 10%; font-size: 12px; padding:5px;" align="right">QTY SI</th>
                                    <th style="text-align: left; width: 10%; font-size: 12px; padding:5px;" align="right">QTY Sisa</th>
                                    <th style="text-align: left; width: 10%; font-size: 12px; padding:5px;" align="right">Persentase Terpenuhi</th>
                                </tr></table>';

                    $content .='<table width="100%"><tr>
                                    <td style="text-align: left; width: 10%; font-size: 12px; padding:5px;">
                                    MERK '.$DataLaporan[$i]->Merk.'
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>';

                    $count_merk += 1;
                    $nama_merk = $DataLaporan[$i]->Merk;
                    
                    $count_dealer += 1;
                    $nama_dealer = $DataLaporan[$i]->Nm_Plg;
                }

                // if (((($nama_dealer!="") && ($nama_dealer==$DataLaporan[$i]->Nm_Plg))
                //     && (($nama_merk=="") || (($nama_merk!="") && ($nama_merk!=$DataLaporan[$i]->Merk))))
                //     ||
                //     ((($nama_dealer!="") && ($nama_dealer!=$DataLaporan[$i]->Nm_Plg))
                //     && (($nama_merk=="") || (($nama_merk!="") && ($nama_merk!=$DataLaporan[$i]->Merk))))
                //     ||
                //     ((($nama_partnertype!="") && ($nama_partnertype==$DataLaporan[$i]->Partner_Type))
                //     && (($nama_dealer!="") && ($nama_dealer!=$DataLaporan[$i]->Nm_Plg))
                //     && (($nama_merk=="") || (($nama_merk!="") && ($nama_merk==$DataLaporan[$i]->Merk))))                   
                //     ) {

                if (
                    ((($nama_salesman!="") && ($nama_salesman==$DataLaporan[$i]->Nm_Slsman))
                    && (($nama_partnertype!="") && ($nama_partnertype==$DataLaporan[$i]->Partner_Type))
                    && (($nama_dealer!="") && ($nama_dealer==$DataLaporan[$i]->Nm_Plg))
                    && (($nama_merk=="") || (($nama_merk!="") && ($nama_merk!=$DataLaporan[$i]->Merk))))
                ||
                    ((($nama_salesman!="") && ($nama_salesman==$DataLaporan[$i]->Nm_Slsman))
                    && (($nama_partnertype!="") && ($nama_partnertype==$DataLaporan[$i]->Partner_Type))
                    && (($nama_dealer!="") && ($nama_dealer!=$DataLaporan[$i]->Nm_Plg))
                    && (($nama_merk=="") || (($nama_merk!="") && ($nama_merk!=$DataLaporan[$i]->Merk))))
                ||
                    ((($nama_salesman!="") && ($nama_salesman==$DataLaporan[$i]->Nm_Slsman))
                    && (($nama_partnertype!="") && ($nama_partnertype==$DataLaporan[$i]->Partner_Type))
                    && (($nama_dealer!="") && ($nama_dealer!=$DataLaporan[$i]->Nm_Plg))
                    && (($nama_merk=="") || (($nama_merk!="") && ($nama_merk==$DataLaporan[$i]->Merk))))
                ||
                    ((($nama_salesman!="") && ($nama_salesman==$DataLaporan[$i]->Nm_Slsman))
                    && (($nama_partnertype!="") && ($nama_partnertype!=$DataLaporan[$i]->Partner_Type))
                    && (($nama_dealer!="") && ($nama_dealer!=$DataLaporan[$i]->Nm_Plg))
                    && (($nama_merk=="") || (($nama_merk!="") && ($nama_merk!=$DataLaporan[$i]->Merk))))
                ||
                    ((($nama_salesman!="") && ($nama_salesman==$DataLaporan[$i]->Nm_Slsman))
                    && (($nama_partnertype!="") && ($nama_partnertype!=$DataLaporan[$i]->Partner_Type))
                    && (($nama_dealer!="") && ($nama_dealer!=$DataLaporan[$i]->Nm_Plg))
                    && (($nama_merk=="") || (($nama_merk!="") && ($nama_merk==$DataLaporan[$i]->Merk))))             
                ) {

                    // $content .='</table><table width="100%"><tr>
                    //                 <td style="text-align: left; width: 10%; font-size: 12px; padding:5px;">
                    //                 1 MERK '.$DataLaporan[$i]->Merk.'
                    //                 </td>
                    //                 <td></td>
                    //                 <td></td>
                    //                 <td></td>
                    //                 <td></td>
                    //                 <td></td>
                    //             </tr>';

                    $content .='<tr>
                                    <td style="text-align: left; width: 10%; font-size: 12px; padding:5px;">
                                    MERK '.$DataLaporan[$i]->Merk.'
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>';

                    $count_merk += 1;
                    $nama_merk = $DataLaporan[$i]->Merk;
                } 

				$no++;

                if ($DataLaporan[$i]->Qty == 0){
                    $Percentase = 100;
                } else {
                    $Percentase = ($DataLaporan[$i]->Qty_SI / $DataLaporan[$i]->Qty) * 100;
                }
                
                $content .='<tr>
                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.$no.'</td>
                                <td style="text-align: left; width: 10%; font-size: 12px; padding:5px;">'.$DataLaporan[$i]->Kd_Brg.'</td>
                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->Qty,0,",",".").'</td>
                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->Qty_SI,0,",",".").'</td>
                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->QtySisa,0,",",".").'</td>
                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($Percentase,0,",",".").' % </td>
                            </tr>';

                $Sum_Qty += $DataLaporan[$i]->Qty;
                $Sum_Qty_SI += $DataLaporan[$i]->Qty_SI;
                $Sum_QtySisa += $DataLaporan[$i]->QtySisa; 
                
                $GrandTotal_Qty += $DataLaporan[$i]->Qty;
                $GrandTotal_Qty_SI += $DataLaporan[$i]->Qty_SI;
                $GrandTotal_QtySisa += $DataLaporan[$i]->QtySisa;                 

			}

            if ($GrandTotal_Qty == 0){
                $GrandTotal_Percentase = 100;
            } else {
                $GrandTotal_Percentase = ($GrandTotal_Qty_SI / $GrandTotal_Qty) * 100;
            }

			$content .='<br>';
			$content .='<tr>
							<td style="text-align: right; width: 10%; font-size: 12px; padding:5px;"></td>
							<td style="text-align: left; width: 10%; font-size: 12px; padding:5px;">GRAND TOTAL</td>
							<td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($GrandTotal_Qty,0,",",".").'</td>
							<td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($GrandTotal_Qty_SI,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($GrandTotal_QtySisa,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($GrandTotal_Percentase,0,",",".").' % </td>
						</tr></table>';	

			$content .='<br>';
			$content .='<br>';

			set_time_limit(60);
            if($output=='HTML'){
                echo ($header);
                echo ($content);
            } else {
                $mpdf->SetHTMLHeader($header,'','1');
                $mpdf->WriteHTML($content);
                $mpdf->Output();
            }
		}
    }
    ?>