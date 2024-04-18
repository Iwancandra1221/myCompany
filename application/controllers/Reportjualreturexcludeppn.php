<?php 
    header('Access-Control-Allow-Origin:*');
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
    class Reportjualreturexcludeppn extends MY_Controller 
    {
        public function __construct()
        {
            parent::__construct();
			$this->load->model('HelperModel');
			$this->load->helper('FormLibrary');
			$this->load->library('excel');
            $this->load->model('ConfigSysModel');
            $this->load->model('GzipDecodeModel');
            $this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
        }

        public function index() 
        {
            //http://localhost:90/myCompany/Reportomzetnettobarang/index
			$data = array();
			$api = 'APITES';
			set_time_limit(60);

            //Periode

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

            $hasil = $this->GzipDecodeModel->_decodeGzip($response);
			// $hasil = json_decode($response);
			if ($hasil->result == "sukses") {
				$data["wilayah"] = $hasil->data;
			} else {
				$data["wilayah"] = "";
			}
            //die($data["wilayah"]);

            //ParentDiv
            //http://localhost:90/webAPI/MsDivisi/GetListParentDiv?api=APITES
            $url = $this->API_URL."/MsDivisi/GetListParentDiv?api=".$api;
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

            $hasil = $this->GzipDecodeModel->_decodeGzip_true($response);
            // print_r($hasil);die();
            // $hasil = json_decode($response);

			if (!empty($hasil)) {
				$data["parentdiv"] = $hasil;
			} else {
				$data["parentdiv"] = "";
			}
            //die($data["parentdiv"]);

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

            $hasil = json_decode($response);
			if ($response!=null) {
				$data["divisi"] = $hasil;
			} else {
				$data["divisi"] = "";
			}
            //die($data["divisi"]);

            //TipeFaktur
            //http://localhost:90/webAPI/MsTipeFaktur/GetList?api=APITES
            $url = $this->API_URL."/MsTipeFaktur/GetList?api=".$api;
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

            $hasil = json_decode($response);
			if ($response!=null) {
				$data["tipefaktur"] = $hasil;
			} else {
				$data["tipefaktur"] = "";
			}
            //die($data["tipefaktur"]);

            //Dealer
            //http://localhost:90/webAPI/MasterDealer/GetListDealer?api=APITES
            $url = $this->API_URL."/MasterDealer/GetListDealer?api=".$api;
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
				$data["dealer"] = $hasil->data;
			} else {
				$data["dealer"] = "";
			}
            //die($data["dealer"]);

            // //WilayahKhusus
            // //http://localhost:90/webAPI/MsDealer/GetListWilayahKhusus?api=APITES&kdplg=DMIC049
            // //$_POST = $this->PopulatePost();	
            // //die(urldecode($_POST['dealer']));
            // // if(isset(urldecode($_POST['dealer']))) {
            // //     $kd_plg = '';
            // // } else {
            // //     $kd_plg = urldecode($_POST['dealer']);
            // // } 
            // $kd_plg = 'ALL';       
            // $url = $this->API_URL."/MsDealer/GetListWilayahKhusus?api=".$api."&kdplg=".$kd_plg;
            // //die($url);
            // // open connection
            // $curl = curl_init();
            // // set the url, number of POST vars, POST data
            // curl_setopt_array($curl, array(
            //     CURLOPT_URL => $url,
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_TIMEOUT => 60,
            //     CURLOPT_POST => 1,
            //     CURLOPT_HTTPHEADER => array("Content-type: application/json")
            // ));
            // // execute post
            // $response = curl_exec($curl);
            // $err = curl_error($curl);
            // // close connection
            // curl_close($curl);

            // $hasil = json_decode($response);
			// if ($response!=null) {
			// 	$data["wilayahkhusus"] = $hasil->data;
			// } else {
			// 	$data["wilayahkhusus"] = "";
			// }
            // //die($data["wilayahkhusus"]);

            $data["DealerSelected"] = "ALL";

			$data['title'] = 'Laporan Jual Retur Exclude PPN';
			$this->RenderView('LaporanJualReturExcludePPN',$data);
        }  
    
        public function LaporanJualReturExcludePPN() 
        {
            $keyAPI = 'APITES';
            $api = 'APITES';
            set_time_limit(0);
    
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
            $param["dp1"] = urldecode($_POST["dp1"]);
            $param["dp2"] = urldecode($_POST["dp2"]);
            $param["partnertype"] = urldecode($_POST["partnertype"]);
            $param["wilayah"] = urldecode($_POST["wilayah"]);
            $param["parentdiv"] = urldecode($_POST["parentdiv"]);
            $param["divisi"] = urldecode($_POST["divisi"]);
            $param["tipefaktur"] = urldecode($_POST["tipefaktur"]);
            $param["netto"] = "N";
            $param["kategoribarang"] = urldecode($_POST["kategoribarang"]);
            $param["dealer"] = urldecode($_POST["dealer"]);

            //Checkboxes are not included in the POST data when the form is submitted. You need to call 
            if (isset($_POST['filterberdasarkankodedealer'])){
                $param["filterberdasarkankodedealer"]="Y";
            } else {
                $param["filterberdasarkankodedealer"]="N";
            }

            $param["report"] = urldecode($_POST["report"]);
            $param["isSalesman"] = $_SESSION['logged_in']['isSalesman'];
            $param["salesmanid"] = $_SESSION['logged_in']['salesmanid'];

            if ($param["isSalesman"]==1) {
                //Cari Kode Salesman
                //http://localhost:90/webAPI/MsSalesman/GetSalesmanByUserID?salesman=&userid=2004
                $url = $this->API_URL."/MsSalesman/GetSalesmanByUserID?salesman=&userid=". $_SESSION['logged_in']['salesmanid'];
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
                    $salesman = $hasil->data;
                } else {
                    $salesman = "";
                }
                $param["kodesalesman"] = $salesman->KD_SLSMAN;
            } else {
                $param["kodesalesman"] = "";
            }
            // echo json_encode($param);
            // die();
    
            if($keyAPI==$api) {
                $array_data = array();
    
                $res = $this->MasterDbModel->getByLocationCode($_SESSION['logged_in']['branch_id']);
                $AlamatWebService = $res->AlamatWebService;
                $url = $AlamatWebService.API_BKT."/Reportjualreturexcludeppn/ReportJualReturExcludePPN?api=".$api.
                "&dp1=".urlencode($param["dp1"]).
                "&dp2=".urlencode($param["dp2"]).
                "&partnertype=".urlencode($param["partnertype"]).
                "&wilayah=".urlencode($param["wilayah"]).
                "&parentdiv=".urlencode($param["parentdiv"]).
                "&divisi=".urlencode($param["divisi"]).
                "&tipefaktur=".urlencode($param["tipefaktur"]).
                "&kategoribarang=".urlencode($param["kategoribarang"]).
                "&filterberdasarkankodedealer=".urlencode($param["filterberdasarkankodedealer"]).
                "&dealer=".urlencode($param["dealer"]).
                "&report=".urlencode($param["report"]).
                "&isSalesman=".urlencode($param["isSalesman"]).
                "&salesmanid=".urlencode($param["salesmanid"]).
                "&kodesalesman=".urlencode($param["kodesalesman"]).
                "&svr=".urlencode($res->Server)."&db=".urlencode($res->Database);

                //untuk test
                // $url = 'http://localhost:90/bktAPI/Reportjualreturexcludeppn/ReportJualReturExcludePPN?api=APITES&dp1=18-01-2022&dp2=23-04-2023&partnertype=ALL&wilayah=ALL&parentdiv=ALL&divisi=ALL&tipefaktur=ALL&kategoribarang=P&filterberdasarkankodedealer=N&dealer=ALL&report=JENISBARANG&isSalesman=0&salesmanid=&kodesalesman=&svr=10.1.0.99&db=BHAKTI';
                //$url = 'http://localhost:90/bktAPI/Reportjualreturexcludeppn/ReportJualReturExcludePPN?api=APITES&dp1=18-01-2022&dp2=23-04-2023&partnertype=ALL&wilayah=ALL&parentdiv=ALL&divisi=ALL&tipefaktur=ALL&kategoribarang=P&filterberdasarkankodedealer=Y&dealer=DMIC049&report=JENISBARANG&isSalesman=0&salesmanid=&kodesalesman=&svr=10.1.0.99&db=BHAKTI';
                //echo($url);

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

                //echo ($response);
                //die;

                // $hasil = json_decode($response);
                $hasil = $this->GzipDecodeModel->_decodeGzip_true($response);

                if ($hasil['result'] == "sukses") {
                    $result["result"] = "sukses";
                    $result["data"] = $hasil['data'];
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
            
            //$page_title Maximum 31 characters allowed in sheet title
            //Jual Retur Per Jenis Barang (Exclude PPN) = 41
            //Jual Retur Per Kode Barang (Exclude PPN) = 40
            $excel_title = "Jual Retur";

            if ($param["filterberdasarkankodedealer"]=="Y"){

                if ($param["dealer"]=='ALL'){
                    $excel_title .= " All Dealer";
                } else {
                    $excel_title .= " Per Dealer ".trim($param["dealer"]);               
                }
            }

            if($param["report"]=='JENISBARANG'){
                $page_title = "Jual Retur Per Jenis Barang";
                $excel_title .= " per Jenis Barang (Exclude PPN)";
            } else if($param["report"]=='KODEBARANG'){ 
                $page_title = "Jual Retur Per Kode Barang";
                $excel_title .= " per Kode Barang (Exclude PPN)";
            }

            if ($param["partnertype"]!="ALL") {
                $excel_title .= " Partner Type ".trim($param["partnertype"]);
            }

            if ($param["wilayah"]!="ALL") {
                $excel_title .= " Wilayah ".trim($param["wilayah"]);
            }

            if ($param["parentdiv"]!="ALL") {
                $excel_title .= " Parent Div ".trim($param["parentdiv"]);
            }

            if ($param["divisi"]!="ALL") {
                $excel_title .= " Divisi ".trim($param["divisi"]);
            }

            if ($param["tipefaktur"]!="ALL") {
                $excel_title .= " Tipe Faktur ".trim($param["tipefaktur"]);
            }

            if ($param["netto"]!="ALL") {
                $excel_title .= " Netto ".trim($param["netto"]);
            }

            if ($param["kategoribarang"]!="ALL") {
                $excel_title .= " Kategori Barang ".trim($param["kategoribarang"]);
            }

            //echo json_encode($result["data"]);
            if(isset($_POST['btnHTML'])) {
                $this->proses_pdf_html($page_title, $excel_title, $param, $result["data"],'HTML');
            } elseif(isset($_POST['btnPDF'])) {
                $this->proses_pdf_html($page_title, $excel_title, $param, $result["data"],'PDF');
            } elseif(isset($_POST['btnExcel'])) {
                $this->proses_excel($page_title, $excel_title, $param, $result["data"],'SAVE');
            } 
        }

        public function proses_excel ($page_title, $excel_title, $param, $DataLaporan, $output)
		{
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);

            $PrintDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));
						
			$sheet->setTitle($page_title);
            $sheet->setCellValue('A1', 'Print Date      : '.$PrintDate);
            $sheet->setCellValue('A2', 'Print By        : '.$_SESSION['logged_in']['username']);
            $sheet->setCellValue('A3', $page_title);
            $sheet->getStyle('A3')->getFont()->setSize(20);
            $sheet->setCellValue('A4', $excel_title);
            $sheet->setCellValue('A5', 'Periode         : '.date("d-M-Y", strtotime($param["dp1"]))." - ".date("d-M-Y", strtotime($param["dp2"])));
            $sheet->setCellValue('A6', 'Kategori Barang : '.trim($param["kategoribarang"]));
            $sheet->setCellValue('A7', 'Status Netto    : '.$param["netto"]);

			$currcol = 1;
			$currrow = 7;	

            $currrow++;
            $currrow++;
            $currcol = 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Wilayah");
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Partner Type");
            if ($param["dealer"]!='ALL'){
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Kode Dealer");	
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Nama Dealer");	
            }

            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Divisi");
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Merk");
            if($param["report"]=='JENISBARANG'){
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Jenis Barang");
            } elseif($param["report"]=='KODEBARANG'){
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Kode Barang");
            }
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Qty Jual");
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Qty Retur");
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Total Qty");
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Total Jual");
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Total Retur");
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Total Rp");
            $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

			// warna header
            $max_col = $currcol-1;
            $max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A'.$currrow.':'.$max_col.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
            $Group1_Partner_Type = '';
            $Group2_Kd_Plg = '';
            $Group3_Alm_Plg = '';
            $Group4_Divisi = '';
            $Group5_Merk = '';

            $SumByMerk_Qty_Jual = 0;
            $SumByMerk_Qty_Retur = 0;
            $SumByMerk_Qty_Total = 0;
            $SumByMerk_Total_Jual = 0;
            $SumByMerk_Total_Retur = 0;
            $SumByMerk_Total = 0;

            $SumByDivisi_Qty_Jual = 0;
            $SumByDivisi_Qty_Retur = 0;
            $SumByDivisi_Qty_Total = 0;
            $SumByDivisi_Total_Jual = 0;
            $SumByDivisi_Total_Retur = 0;
            $SumByDivisi_Total = 0;

            $SumByKd_Plg_Qty_Jual = 0;
            $SumByKd_Plg_Qty_Retur = 0;
            $SumByKd_Plg_Qty_Total  = 0;
            $SumByKd_Plg_Total_Jual  = 0;
            $SumByKd_Plg_Total_Retur  = 0;
            $SumByKd_Plg_Total = 0;
   
            $SumByPartner_Type_Qty_Jual = 0;
            $SumByPartner_Type_Qty_Retur = 0;
            $SumByPartner_Type_Qty_Total = 0;
            $SumByPartner_Type_Total_Jual = 0;
            $SumByPartner_Type_Total_Retur = 0;
            $SumByPartner_Type_Total = 0;
 
            $SumByAll_Qty_Jual = 0;
            $SumByAll_Qty_Retur = 0;
            $SumByAll_Qty_Total = 0;
            $SumByAll_Total_Jual = 0;
            $SumByAll_Total_Retur = 0;
            $SumByAll_Total = 0;

			$jum= count($DataLaporan);
			for($i=0; $i<$jum; $i++){
                if ($i==0){
                    $Group1_Partner_Type = trim($DataLaporan[$i]['Partner_Type']);
                    $Group4_Divisi = trim($DataLaporan[$i]['Divisi']);
                    $Group5_Merk = trim($DataLaporan[$i]['Merk']);

                    $SumByMerk_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                    $SumByMerk_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                    $SumByMerk_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                    $SumByMerk_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                    $SumByMerk_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                    $SumByMerk_Total = $DataLaporan[$i]['Total'];

                    $SumByDivisi_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                    $SumByDivisi_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                    $SumByDivisi_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                    $SumByDivisi_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                    $SumByDivisi_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                    $SumByDivisi_Total = $DataLaporan[$i]['Total'];
            
                    $SumByPartner_Type_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                    $SumByPartner_Type_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                    $SumByPartner_Type_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                    $SumByPartner_Type_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                    $SumByPartner_Type_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                    $SumByPartner_Type_Total = $DataLaporan[$i]['Total'];

                    if ($param["dealer"]!='ALL'){
                        $Group2_Kd_Plg = trim($DataLaporan[$i]['Kd_Plg']);
                        $SumByKd_Plg_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                        $SumByKd_Plg_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                        $SumByKd_Plg_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                        $SumByKd_Plg_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                        $SumByKd_Plg_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                        $SumByKd_Plg_Total = $DataLaporan[$i]['Total'];
                    }

                } else {

                    if (trim($DataLaporan[$i]['Merk'])!=$Group5_Merk){

                        $currrow++;
                        $currcol = 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL MERK');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        if ($param["dealer"]!='ALL'){
                            $currcol += 1;
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                            $currcol += 1;
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        }	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByMerk_Qty_Jual);
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByMerk_Qty_Retur);	
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByMerk_Qty_Total);	
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByMerk_Total_Jual);	
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByMerk_Total_Retur);  
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByMerk_Total);  
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

                        $Group5_Merk = trim($DataLaporan[$i]['Merk']);
                        $SumByMerk_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                        $SumByMerk_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                        $SumByMerk_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                        $SumByMerk_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                        $SumByMerk_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                        $SumByMerk_Total = $DataLaporan[$i]['Total'];
                    } else {
                        $SumByMerk_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                        $SumByMerk_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                        $SumByMerk_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                        $SumByMerk_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                        $SumByMerk_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                        $SumByMerk_Total += $DataLaporan[$i]['Total'];
                    }

                    if (trim($DataLaporan[$i]['Divisi'])!=$Group4_Divisi){

                        $currrow++;
                        $currcol = 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL DIVISI');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        if ($param["dealer"]!='ALL'){
                            $currcol += 1;
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                            $currcol += 1;
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        }	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByDivisi_Qty_Jual);
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByDivisi_Qty_Retur);	
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByDivisi_Qty_Total);	
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByDivisi_Total_Jual);	
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByDivisi_Total_Retur);  
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByDivisi_Total);  
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

                        $Group4_Divisi = trim($DataLaporan[$i]['Divisi']);
                        $SumByDivisi_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                        $SumByDivisi_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                        $SumByDivisi_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                        $SumByDivisi_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                        $SumByDivisi_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                        $SumByDivisi_Total = $DataLaporan[$i]['Total'];
                    } else {
                        $SumByDivisi_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                        $SumByDivisi_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                        $SumByDivisi_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                        $SumByDivisi_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                        $SumByDivisi_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                        $SumByDivisi_Total += $DataLaporan[$i]['Total'];
                    }

                    if ($param["dealer"]!='ALL'){
                        if (trim($DataLaporan[$i]['Kd_Plg'])!=$Group2_Kd_Plg){

                            $currrow++;
                            $currcol = 1;
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL DEALER');
                            $currcol += 1;
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                            if ($param["dealer"]!='ALL'){
                                $currcol += 1;
                                $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                                $currcol += 1;
                                $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                            }	
                            $currcol += 1;
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                            $currcol += 1;
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                            $currcol += 1;
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                            $currcol += 1;
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKd_Plg_Qty_Jual);
                            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');	
                            $currcol += 1;
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKd_Plg_Qty_Retur);	
                            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                            $currcol += 1;
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKd_Plg_Qty_Total);	
                            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                            $currcol += 1;
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKd_Plg_Total_Jual);	
                            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                            $currcol += 1;
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKd_Plg_Total_Retur);  
                            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                            $currcol += 1;
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKd_Plg_Total);  
                            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                            $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	
                            
                            $Group2_Kd_Plg = trim($DataLaporan[$i]['Kd_Plg']);
                            $SumByKd_Plg_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                            $SumByKd_Plg_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                            $SumByKd_Plg_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                            $SumByKd_Plg_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                            $SumByKd_Plg_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                            $SumByKd_Plg_Total = $DataLaporan[$i]['Total'];
                        } else {
                            $SumByKd_Plg_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                            $SumByKd_Plg_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                            $SumByKd_Plg_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                            $SumByKd_Plg_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                            $SumByKd_Plg_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                            $SumByKd_Plg_Total += $DataLaporan[$i]['Total'];
                        }
                    }

                    if (trim($DataLaporan[$i]['Partner_Type'])!=$Group1_Partner_Type){

                        $currrow++;
                        $currcol = 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL PARTNER TYPE');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        if ($param["dealer"]!='ALL'){
                            $currcol += 1;
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                            $currcol += 1;
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        }		
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByPartner_Type_Qty_Jual);
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByPartner_Type_Qty_Retur);	
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByPartner_Type_Qty_Total);	
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByPartner_Type_Total_Jual);	
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByPartner_Type_Total_Retur);  
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByPartner_Type_Total);  
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

                        $Group1_Partner_Type = trim($DataLaporan[$i]['Partner_Type']);
                        $SumByPartner_Type_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                        $SumByPartner_Type_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                        $SumByPartner_Type_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                        $SumByPartner_Type_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                        $SumByPartner_Type_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                        $SumByPartner_Type_Total = $DataLaporan[$i]['Total'];
                    } else {
                        $SumByPartner_Type_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                        $SumByPartner_Type_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                        $SumByPartner_Type_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                        $SumByPartner_Type_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                        $SumByPartner_Type_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                        $SumByPartner_Type_Total += $DataLaporan[$i]['Total'];
                    }

                }

                //echo json_encode($DataLaporan[$i]['Wilayah']);
                //start isi data - from here
				$currrow++;
				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($DataLaporan[$i]['Wilayah']));
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($DataLaporan[$i]['Partner_Type']));	

                if ($param["dealer"]!='ALL'){
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($DataLaporan[$i]['Kd_Plg']));	
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($DataLaporan[$i]['Nm_Plg']));	
                }

				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($DataLaporan[$i]['Divisi']));	
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($DataLaporan[$i]['Merk']));	

                if($param["report"]=='JENISBARANG'){
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($DataLaporan[$i]['Jns_Brg']));
                } elseif($param["report"]=='KODEBARANG'){
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($DataLaporan[$i]['Kd_Brg']));
                }

				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]['Qty_Jual']);
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');	
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]['Qty_Retur']);	
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]['Qty_Total']);	
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]['Total_Jual']);
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]['Total_Retur']); 
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0'); 
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]['Total']); 
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0'); 
           
                $SumByAll_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                $SumByAll_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                $SumByAll_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                $SumByAll_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                $SumByAll_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                $SumByAll_Total += $DataLaporan[$i]['Total'];

                //start isi data - until here
            }

            $currrow++;
            $currcol = 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL MERK');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            if ($param["dealer"]!='ALL'){
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            }	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByMerk_Qty_Jual);
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByMerk_Qty_Retur);	
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByMerk_Qty_Total);	
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByMerk_Total_Jual);	
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByMerk_Total_Retur);  
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByMerk_Total);  
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

            $currrow++;
            $currcol = 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL DIVISI');	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            if ($param["dealer"]!='ALL'){
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            }
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByDivisi_Qty_Jual);
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByDivisi_Qty_Retur);	
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByDivisi_Qty_Total);	
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByDivisi_Total_Jual);	
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByDivisi_Total_Retur);  
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByDivisi_Total);  
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	
 
            if ($param["dealer"]!='ALL'){
                $currrow++;
                $currcol = 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL DEALER');	
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKd_Plg_Qty_Jual);
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');	
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKd_Plg_Qty_Retur);	
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKd_Plg_Qty_Total);	
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKd_Plg_Total_Jual);	
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKd_Plg_Total_Retur);  
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKd_Plg_Total);  
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	
    
            }

            $currrow++;
            $currcol = 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL PARTNER TYPE');	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            if ($param["dealer"]!='ALL'){
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            }
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByPartner_Type_Qty_Jual);
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByPartner_Type_Qty_Retur);	
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByPartner_Type_Qty_Total);	
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByPartner_Type_Total_Jual);	
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByPartner_Type_Total_Retur);  
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByPartner_Type_Total);  
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

            $currrow++;
            $currcol = 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            if ($param["dealer"]!='ALL'){
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            }
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByAll_Qty_Jual);
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByAll_Qty_Retur);	
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByAll_Qty_Total);	
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByAll_Total_Jual);	
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByAll_Total_Retur);  
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByAll_Total);  
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

            $max_col = $currcol;
            $max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
            for ($i = 'A'; $i != $max_col; $i++) {
                $sheet->getColumnDimension($i)->setAutoSize(TRUE);
            }

            $filename = $excel_title; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
            ob_end_clean();
            $writer->save('php://output');	// download file
            exit(); 
        }

        public function proses_pdf_html ($page_title, $excel_title, $param, $DataLaporan, $output)
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
				'margin_top' => 50,
				'margin_bottom' => 10,
				'margin_header' => 10,
				'margin_footer' => 5,
				'orientation' => 'P'
			));

            $PrintDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));

            $Group0_Wilayah = '';
            $Group1_Partner_Type = '';
            $Group2_Kd_Plg = '';
            $Group2_Nm_Plg = '';
            $Group3_Alm_Plg = '';
            $Group4_Divisi = '';
            $Group5_Merk = '';

            $GantiJudulMerk = 'N';
            $GantiJudulDivisi = 'N';
            $GantiJudulKd_Plg = 'N';
            $GantiJudulPartner_Type = 'N';
            $GantiJudulWilayah = 'N';

            $SumByMerk_Qty_Jual = 0;
            $SumByMerk_Qty_Retur = 0;
            $SumByMerk_Qty_Total = 0;
            $SumByMerk_Total_Jual = 0;
            $SumByMerk_Total_Retur = 0;
            $SumByMerk_Total = 0;

            $SumByDivisi_Qty_Jual = 0;
            $SumByDivisi_Qty_Retur = 0;
            $SumByDivisi_Qty_Total = 0;
            $SumByDivisi_Total_Jual = 0;
            $SumByDivisi_Total_Retur = 0;
            $SumByDivisi_Total = 0;

            $SumByKd_Plg_Qty_Jual = 0;
            $SumByKd_Plg_Qty_Retur = 0;
            $SumByKd_Plg_Qty_Total  = 0;
            $SumByKd_Plg_Total_Jual  = 0;
            $SumByKd_Plg_Total_Retur  = 0;
            $SumByKd_Plg_Total = 0;
   
            $SumByPartner_Type_Qty_Jual = 0;
            $SumByPartner_Type_Qty_Retur = 0;
            $SumByPartner_Type_Qty_Total = 0;
            $SumByPartner_Type_Total_Jual = 0;
            $SumByPartner_Type_Total_Retur = 0;
            $SumByPartner_Type_Total = 0;

            $SumByWilayah_Qty_Jual = 0;
            $SumByWilayah_Qty_Retur = 0;
            $SumByWilayah_Qty_Total = 0;
            $SumByWilayah_Total_Jual = 0;
            $SumByWilayah_Total_Retur = 0;
            $SumByWilayah_Total = 0;
 
            $SumByAll_Qty_Jual = 0;
            $SumByAll_Qty_Retur = 0;
            $SumByAll_Qty_Total = 0;
            $SumByAll_Total_Jual = 0;
            $SumByAll_Total_Retur = 0;
            $SumByAll_Total = 0;

			$header = '<table border="0" style="width:100%; font-size:15px;">
                    <tr>
                        <td align="center" style="font-size:12px;">
                            Print Date '.$PrintDate.'
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size:12px;">
                            Print By '.$_SESSION['logged_in']['username'].'
                        </td>
                    </tr>
					<tr>
						<td align="center">
							<b>
								'.$page_title.'
							</b>
						</td>
					</tr>
					<tr>
						<td align="center" style="font-size:12px;">
                            '.$excel_title.'
						</td>
					</tr>
					<tr>
						<td align="center" style="font-size:12px;">
							Periode '.date("d-M-Y", strtotime($param["dp1"])).' <b> - </b> '.date("d-M-Y", strtotime($param["dp2"])).'
						</td>
					</tr>
					<tr>
						<td align="center" style="font-size:12px;">
							Kategori Barang '.trim($param["kategoribarang"]).'
						</td>
					</tr>
					<tr>
						<td align="center" style="font-size:12px;">
							Status Netto '.$param["netto"].'
						</td>
					</tr>
				</table>';

            $content = '
            <br><table width="100%" style="font-weight: bold;">
                <tr>';

            if($param["report"]=='JENISBARANG'){
                $content .='<td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">Jenis Barang</td>';
            } elseif($param["report"]=='KODEBARANG'){
                $content .='<td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">Kode Barang</td>';
            }

            $content .='<td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">Qty Jual</td>
                        <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">Qty Retur</td>
                        <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">Total Qty</td>
                        <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">Total Jual</td>
                        <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">Total Retur</td>
                        <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">Total Rp</td>
                        </tr></table>';

			$jum= count($DataLaporan);
            if ($jum!=0){
                //$content .='<table width="100%">';
                for($i=0; $i<$jum; $i++){

                    if ($i==0){

                        $Group0_Wilayah = trim($DataLaporan[$i]['Wilayah']);
                        $Group1_Partner_Type = trim($DataLaporan[$i]['Partner_Type']);
                        $Group4_Divisi = trim($DataLaporan[$i]['Divisi']);
                        $Group5_Merk = trim($DataLaporan[$i]['Merk']);
    
                        $SumByMerk_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                        $SumByMerk_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                        $SumByMerk_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                        $SumByMerk_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                        $SumByMerk_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                        $SumByMerk_Total = $DataLaporan[$i]['Total'];
    
                        $SumByDivisi_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                        $SumByDivisi_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                        $SumByDivisi_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                        $SumByDivisi_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                        $SumByDivisi_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                        $SumByDivisi_Total = $DataLaporan[$i]['Total'];
                
                        $SumByPartner_Type_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                        $SumByPartner_Type_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                        $SumByPartner_Type_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                        $SumByPartner_Type_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                        $SumByPartner_Type_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                        $SumByPartner_Type_Total = $DataLaporan[$i]['Total'];

                        $SumByWilayah_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                        $SumByWilayah_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                        $SumByWilayah_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                        $SumByWilayah_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                        $SumByWilayah_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                        $SumByWilayah_Total = $DataLaporan[$i]['Total'];
    
                        if ($param["dealer"]!='ALL'){
                            $Group2_Kd_Plg = trim($DataLaporan[$i]['Kd_Plg']);
                            $Group2_Nm_Plg = trim($DataLaporan[$i]['Nm_Plg']);
                            $SumByKd_Plg_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                            $SumByKd_Plg_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                            $SumByKd_Plg_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                            $SumByKd_Plg_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                            $SumByKd_Plg_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                            $SumByKd_Plg_Total = $DataLaporan[$i]['Total'];
                        }

                        $content .='<table width="100%" style="font-weight: bold;"><tr>
                                        <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">WILAYAH '.trim($Group0_Wilayah).'</td>
                                    </tr></table>';

                        $content .='<table width="100%" style="font-weight: bold;"><tr>
                                        <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">PARTNER TYPE '.trim($Group1_Partner_Type).'</td>
                                    </tr></table>';

                        if ($param["dealer"]!='ALL'){
                            $content .='<table width="100%" style="font-weight: bold;"><tr>
                                            <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">DEALER '.trim($Group2_Kd_Plg).' - '.trim($Group2_Nm_Plg).'</td>
                                        </tr></table>';
                        }

                        $content .='<table width="100%" style="font-weight: bold;"><tr>
                                        <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">DIVISI '.trim($Group4_Divisi).'</td>
                                    </tr></table>';

                        $content .='<table width="100%" style="font-weight: bold;"><tr>
                                        <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">MERK '.trim($Group5_Merk).'</td>
                                    </tr></table>';
    
                    } else {
    
                        if (trim($DataLaporan[$i]['Merk'])!=$Group5_Merk){

                            $content .='<table width="100%" style="font-weight: bold;"><tr>
                                            <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL MERK '.$Group5_Merk.'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Qty_Jual,0,",",".").'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Qty_Retur,0,",",".").'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Qty_Total,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Total_Jual,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Total_Retur,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Total,0,",",".").'</td>
                                        </tr></table>';
    
                            $Group5_Merk = trim($DataLaporan[$i]['Merk']);
                            $SumByMerk_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                            $SumByMerk_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                            $SumByMerk_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                            $SumByMerk_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                            $SumByMerk_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                            $SumByMerk_Total = $DataLaporan[$i]['Total'];
                            $GantiJudulMerk = 'Y';
                        } else {

                            if ((trim($DataLaporan[$i]['Divisi'])==$Group4_Divisi) 
                            //&& (($param["dealer"]!='ALL') && (trim($DataLaporan[$i]['Kd_Plg'])==$Group2_Kd_Plg))
                            && (trim($DataLaporan[$i]['Partner_Type'])==$Group1_Partner_Type)
                            && (trim($DataLaporan[$i]['Wilayah'])==$Group0_Wilayah)
                            ){
                                $SumByMerk_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                                $SumByMerk_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                                $SumByMerk_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                                $SumByMerk_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                                $SumByMerk_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                                $SumByMerk_Total += $DataLaporan[$i]['Total'];
                                $GantiJudulMerk = 'N';
                            } else {
                                $content .='<table width="100%" style="font-weight: bold;"><tr>
                                                <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">0TOTAL MERK '.$Group5_Merk.'</td>
                                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Qty_Jual,0,",",".").'</td>
                                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Qty_Retur,0,",",".").'</td>
                                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Qty_Total,0,",",".").'</td>
                                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Total_Jual,0,",",".").'</td>
                                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Total_Retur,0,",",".").'</td>
                                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Total,0,",",".").'</td>
                                            </tr></table>';

                                $Group5_Merk = trim($DataLaporan[$i]['Merk']);
                                $SumByMerk_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                                $SumByMerk_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                                $SumByMerk_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                                $SumByMerk_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                                $SumByMerk_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                                $SumByMerk_Total = $DataLaporan[$i]['Total'];
                            }
                        }
    
                        if (trim($DataLaporan[$i]['Divisi'])!=$Group4_Divisi){
    
                            $content .='<table width="100%" style="font-weight: bold;"><tr>
                                            <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL DIVISI '.$Group4_Divisi.'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Jual,0,",",".").'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Retur,0,",",".").'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Total,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total_Jual,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total_Retur,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total,0,",",".").'</td>
                                        </tr></table>';

                            $Group4_Divisi = trim($DataLaporan[$i]['Divisi']);
                            $SumByDivisi_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                            $SumByDivisi_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                            $SumByDivisi_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                            $SumByDivisi_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                            $SumByDivisi_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                            $SumByDivisi_Total = $DataLaporan[$i]['Total'];
                            $GantiJudulDivisi = 'Y';
                        } else {
                            if ($param["dealer"]!='ALL'){
                                if (trim($DataLaporan[$i]['Kd_Plg'])==$Group2_Kd_Plg){
                                    $SumByDivisi_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                                    $SumByDivisi_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                                    $SumByDivisi_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                                    $SumByDivisi_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                                    $SumByDivisi_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                                    $SumByDivisi_Total += $DataLaporan[$i]['Total'];
                                    $GantiJudulDivisi = 'N';
                                } else {
                                    $content .='<table width="100%" style="font-weight: bold;"><tr>
                                                    <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL DIVISI '.$Group4_Divisi.'</td>
                                                    <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Jual,0,",",".").'</td>
                                                    <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Retur,0,",",".").'</td>
                                                    <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Total,0,",",".").'</td>
                                                    <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total_Jual,0,",",".").'</td>
                                                    <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total_Retur,0,",",".").'</td>
                                                    <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total,0,",",".").'</td>
                                                </tr></table>';

                                    $Group4_Divisi = trim($DataLaporan[$i]['Divisi']);
                                    $SumByDivisi_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                                    $SumByDivisi_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                                    $SumByDivisi_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                                    $SumByDivisi_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                                    $SumByDivisi_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                                    $SumByDivisi_Total = $DataLaporan[$i]['Total'];
                                } 
                            } else {
                                if (//(($param["dealer"]!='ALL') && (trim($DataLaporan[$i]['Kd_Plg'])==$Group2_Kd_Plg))
                                //&& 
                                (trim($DataLaporan[$i]['Partner_Type'])==$Group1_Partner_Type)
                                //&& (trim($DataLaporan[$i]['Wilayah'])==$Group0_Wilayah)
                                ){
                                    $SumByDivisi_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                                    $SumByDivisi_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                                    $SumByDivisi_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                                    $SumByDivisi_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                                    $SumByDivisi_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                                    $SumByDivisi_Total += $DataLaporan[$i]['Total'];
                                    $GantiJudulDivisi = 'N';
                                } else {
                                    $content .='<table width="100%" style="font-weight: bold;"><tr>
                                                    <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL DIVISI '.$Group4_Divisi.'</td>
                                                    <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Jual,0,",",".").'</td>
                                                    <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Retur,0,",",".").'</td>
                                                    <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Total,0,",",".").'</td>
                                                    <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total_Jual,0,",",".").'</td>
                                                    <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total_Retur,0,",",".").'</td>
                                                    <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total,0,",",".").'</td>
                                                </tr></table>';

                                    $Group4_Divisi = trim($DataLaporan[$i]['Divisi']);
                                    $SumByDivisi_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                                    $SumByDivisi_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                                    $SumByDivisi_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                                    $SumByDivisi_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                                    $SumByDivisi_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                                    $SumByDivisi_Total = $DataLaporan[$i]['Total'];
                                }
                            }
                        }
    
                        if ($param["dealer"]!='ALL'){
                            if (trim($DataLaporan[$i]['Kd_Plg'])!=$Group2_Kd_Plg){
    
                                $content .='<table width="100%" style="font-weight: bold;"><tr>
                                                <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL DEALER '.$Group2_Kd_Plg.'</td>
                                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Qty_Jual,0,",",".").'</td>
                                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Qty_Retur,0,",",".").'</td>
                                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Qty_Total,0,",",".").'</td>
                                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Total_Jual,0,",",".").'</td>
                                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Total_Retur,0,",",".").'</td>
                                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Total,0,",",".").'</td>
                                            </tr></table>';	

                                $Group2_Kd_Plg = trim($DataLaporan[$i]['Kd_Plg']);
                                $Group2_Nm_Plg = trim($DataLaporan[$i]['Nm_Plg']);
                                $SumByKd_Plg_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                                $SumByKd_Plg_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                                $SumByKd_Plg_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                                $SumByKd_Plg_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                                $SumByKd_Plg_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                                $SumByKd_Plg_Total = $DataLaporan[$i]['Total'];
                                $GantiJudulKd_Plg = 'Y';
                            } else {
                                if (//(trim($DataLaporan[$i]['Partner_Type'])==$Group1_Partner_Type)
                                //&& 
                                (trim($DataLaporan[$i]['Wilayah'])==$Group0_Wilayah)
                                ){
                                    $SumByKd_Plg_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                                    $SumByKd_Plg_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                                    $SumByKd_Plg_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                                    $SumByKd_Plg_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                                    $SumByKd_Plg_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                                    $SumByKd_Plg_Total += $DataLaporan[$i]['Total'];
                                    $GantiJudulKd_Plg = 'N';
                                } else {
                                    $content .='<table width="100%" style="font-weight: bold;"><tr>
                                                    <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL DEALER '.$Group2_Kd_Plg.'</td>
                                                    <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Qty_Jual,0,",",".").'</td>
                                                    <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Qty_Retur,0,",",".").'</td>
                                                    <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Qty_Total,0,",",".").'</td>
                                                    <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Total_Jual,0,",",".").'</td>
                                                    <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Total_Retur,0,",",".").'</td>
                                                    <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Total,0,",",".").'</td>
                                                </tr></table>';

                                    $Group4_Divisi = trim($DataLaporan[$i]['Divisi']);
                                    $SumByKd_Plg_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                                    $SumByKd_Plg_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                                    $SumByKd_Plg_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                                    $SumByKd_Plg_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                                    $SumByKd_Plg_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                                    $SumByKd_Plg_Total = $DataLaporan[$i]['Total'];
                                }
                            }
                        }
    
                        if (trim($DataLaporan[$i]['Partner_Type'])!=$Group1_Partner_Type){

                            $content .='<table width="100%" style="font-weight: bold;"><tr>
                                            <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL PARTNER TYPE '.$Group1_Partner_Type.'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Qty_Jual,0,",",".").'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Qty_Retur,0,",",".").'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Qty_Total,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Total_Jual,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Total_Retur,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Total,0,",",".").'</td>
                                        </tr></table>';	
                        
                            $Group1_Partner_Type = trim($DataLaporan[$i]['Partner_Type']);
                            $SumByPartner_Type_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                            $SumByPartner_Type_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                            $SumByPartner_Type_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                            $SumByPartner_Type_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                            $SumByPartner_Type_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                            $SumByPartner_Type_Total = $DataLaporan[$i]['Total'];
                            $GantiJudulPartner_Type = 'Y';
                        } else {
                            if ((trim($DataLaporan[$i]['Wilayah'])==$Group0_Wilayah)
                            ){
                                $SumByPartner_Type_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                                $SumByPartner_Type_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                                $SumByPartner_Type_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                                $SumByPartner_Type_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                                $SumByPartner_Type_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                                $SumByPartner_Type_Total += $DataLaporan[$i]['Total'];
                                $GantiJudulPartner_Type = 'N';
                            } else {
                                $content .='<table width="100%" style="font-weight: bold;"><tr>
                                                <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL PARTNER TYPE '.$Group1_Partner_Type.'</td>
                                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Qty_Jual,0,",",".").'</td>
                                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Qty_Retur,0,",",".").'</td>
                                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Qty_Total,0,",",".").'</td>
                                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Total_Jual,0,",",".").'</td>
                                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Total_Retur,0,",",".").'</td>
                                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Total,0,",",".").'</td>
                                            </tr></table>';	
                            
                                $Group1_Partner_Type = trim($DataLaporan[$i]['Partner_Type']);
                                $SumByPartner_Type_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                                $SumByPartner_Type_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                                $SumByPartner_Type_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                                $SumByPartner_Type_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                                $SumByPartner_Type_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                                $SumByPartner_Type_Total = $DataLaporan[$i]['Total'];
                            }
                        }

                        if (trim($DataLaporan[$i]['Wilayah'])!=$Group0_Wilayah){

                            $content .='<table width="100%" style="font-weight: bold;"><tr>
                                            <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL WILAYAH '.$Group0_Wilayah.'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByWilayah_Qty_Jual,0,",",".").'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByWilayah_Qty_Retur,0,",",".").'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByWilayah_Qty_Total,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByWilayah_Total_Jual,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByWilayah_Total_Retur,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByWilayah_Total,0,",",".").'</td>
                                        </tr></table>';	

                            $Group0_Wilayah = trim($DataLaporan[$i]['Wilayah']);
                            $SumByWilayah_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                            $SumByWilayah_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                            $SumByWilayah_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                            $SumByWilayah_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                            $SumByWilayah_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                            $SumByWilayah_Total = $DataLaporan[$i]['Total'];
                            $GantiJudulWilayah = 'Y';
                        } else {
                            $SumByWilayah_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                            $SumByWilayah_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                            $SumByWilayah_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                            $SumByWilayah_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                            $SumByWilayah_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                            $SumByWilayah_Total += $DataLaporan[$i]['Total'];
                            $GantiJudulWilayah = 'N';
                        }

                        if ($GantiJudulWilayah == 'Y'){
                            $content .='<table width="100%" style="font-weight: bold;"><tr>
                                            <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">WILAYAH '.trim($Group0_Wilayah).'</td>
                                        </tr></table>';
                        }

                        if ($GantiJudulPartner_Type == 'Y'){
                            $content .='<table width="100%" style="font-weight: bold;"><tr>
                                            <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">PARTNER TYPE '.trim($Group1_Partner_Type).'</td>
                                        </tr></table>';
                        }

                        if ($GantiJudulKd_Plg == 'Y'){
                            if ($param["dealer"]!='ALL'){
                                $content .='<table width="100%" style="font-weight: bold;"><tr>
                                                <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">DEALER '.trim($Group2_Kd_Plg).' - '.trim($Group2_Nm_Plg).'</td>
                                            </tr></table>';
                            }
                        }

                        if ($GantiJudulDivisi == 'Y'){
                            $content .='<table width="100%" style="font-weight: bold;"><tr>
                                            <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">DIVISI '.trim($Group4_Divisi).'</td>
                                        </tr></table>';
                        }

                        if ($GantiJudulMerk == 'Y'){
                            $content .='<table width="100%" style="font-weight: bold;"><tr>
                                            <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">MERK '.trim($Group5_Merk).'</td>
                                        </tr></table>';
                        }

    
                    }

                    //start isi data - from here
                    $content .='<table width="100%"><tr>';
                    if($param["report"]=='JENISBARANG'){
                        $content .='<td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">'.$DataLaporan[$i]['Jns_Brg'].'</td>';
                    } elseif($param["report"]=='KODEBARANG'){
                        $content .='<td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">'.$DataLaporan[$i]['Kd_Brg'].'</td>';
                    }
                    $content .='
                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]['Qty_Jual'],0,",",".").'</td>
                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]['Qty_Retur'],0,",",".").'</td>
                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]['Qty_Total'],0,",",".").'</td>
                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]['Total_Jual'],0,",",".").'</td>
                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]['Total_Retur'],0,",",".").'</td>
                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]['Total'],0,",",".").'</td>
                                </tr></table>';

                    $SumByAll_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                    $SumByAll_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                    $SumByAll_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                    $SumByAll_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                    $SumByAll_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                    $SumByAll_Total += $DataLaporan[$i]['Total'];  
                    //start isi data - until here           
                }
                //$content .='</table';
            }

            $content .='<table width="100%" style="font-weight: bold;"><tr>
                            <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL MERK '.$Group5_Merk.'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Qty_Jual,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Qty_Retur,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Qty_Total,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Total_Jual,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Total_Retur,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Total,0,",",".").'</td>
                        </tr></table>';

            $content .='<table width="100%" style="font-weight: bold;"><tr>
                            <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL DIVISI '.$Group4_Divisi.'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Jual,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Retur,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Total,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total_Jual,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total_Retur,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total,0,",",".").'</td>
                        </tr></table>';

            if ($param["dealer"]!='ALL'){
                $content .='<table width="100%" style="font-weight: bold;"><tr>
                                <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL DEALER '.$Group2_Kd_Plg.'</td>
                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Qty_Jual,0,",",".").'</td>
                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Qty_Retur,0,",",".").'</td>
                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Qty_Total,0,",",".").'</td>
                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Total_Jual,0,",",".").'</td>
                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Total_Retur,0,",",".").'</td>
                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Total,0,",",".").'</td>
                            </tr></table>';	
            }

            $content .='<table width="100%" style="font-weight: bold;"><tr>
                            <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL PARTNER TYPE '.$Group1_Partner_Type.'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Qty_Jual,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Qty_Retur,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Qty_Total,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Total_Jual,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Total_Retur,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Total,0,",",".").'</td>
                        </tr></table>';	
			
			$content .='<table width="100%" style="font-weight: bold;"><tr>
							<td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">GRAND TOTAL</td>
							<td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByAll_Qty_Jual,0,",",".").'</td>
							<td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByAll_Qty_Retur,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByAll_Qty_Total,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByAll_Total_Jual,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByAll_Total_Retur,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByAll_Total,0,",",".").'</td>
						</tr></table>';	

			set_time_limit(60);
            if($output=='HTML'){
                echo ($header.$content);
            } else {
                $mpdf->SetHTMLHeader($header,'','1');
                $mpdf->WriteHTML($content);
                $mpdf->Output();
            }
		}

		public function proses_pdf_html_old ($page_title, $excel_title, $param, $DataLaporan, $output)
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
				'margin_top' => 50,
				'margin_bottom' => 10,
				'margin_header' => 10,
				'margin_footer' => 5,
				'orientation' => 'P'
			));

            $PrintDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));

            $Group0_Wilayah = '';
            $Group1_Partner_Type = '';
            $Group2_Kd_Plg = '';
            $Group2_Nm_Plg = '';
            $Group3_Alm_Plg = '';
            $Group4_Divisi = '';
            $Group5_Merk = '';

            $GantiJudulMerk = 'N';
            $GantiJudulDivisi = 'N';
            $GantiJudulKd_Plg = 'N';
            $GantiJudulPartner_Type = 'N';
            $GantiJudulWilayah = 'N';

            $SumByMerk_Qty_Jual = 0;
            $SumByMerk_Qty_Retur = 0;
            $SumByMerk_Qty_Total = 0;
            $SumByMerk_Total_Jual = 0;
            $SumByMerk_Total_Retur = 0;
            $SumByMerk_Total = 0;

            $SumByDivisi_Qty_Jual = 0;
            $SumByDivisi_Qty_Retur = 0;
            $SumByDivisi_Qty_Total = 0;
            $SumByDivisi_Total_Jual = 0;
            $SumByDivisi_Total_Retur = 0;
            $SumByDivisi_Total = 0;

            $SumByKd_Plg_Qty_Jual = 0;
            $SumByKd_Plg_Qty_Retur = 0;
            $SumByKd_Plg_Qty_Total  = 0;
            $SumByKd_Plg_Total_Jual  = 0;
            $SumByKd_Plg_Total_Retur  = 0;
            $SumByKd_Plg_Total = 0;
   
            $SumByPartner_Type_Qty_Jual = 0;
            $SumByPartner_Type_Qty_Retur = 0;
            $SumByPartner_Type_Qty_Total = 0;
            $SumByPartner_Type_Total_Jual = 0;
            $SumByPartner_Type_Total_Retur = 0;
            $SumByPartner_Type_Total = 0;

            $SumByWilayah_Qty_Jual = 0;
            $SumByWilayah_Qty_Retur = 0;
            $SumByWilayah_Qty_Total = 0;
            $SumByWilayah_Total_Jual = 0;
            $SumByWilayah_Total_Retur = 0;
            $SumByWilayah_Total = 0;
 
            $SumByAll_Qty_Jual = 0;
            $SumByAll_Qty_Retur = 0;
            $SumByAll_Qty_Total = 0;
            $SumByAll_Total_Jual = 0;
            $SumByAll_Total_Retur = 0;
            $SumByAll_Total = 0;

			$header = '<table border="0" style="width:100%; font-size:15px;">
                    <tr>
                        <td align="center" style="font-size:12px;">
                            Print Date '.$PrintDate.'
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size:12px;">
                            Print By '.$_SESSION['logged_in']['username'].'
                        </td>
                    </tr>
					<tr>
						<td align="center">
							<b>
								'.$page_title.'
							</b>
						</td>
					</tr>
					<tr>
						<td align="center" style="font-size:12px;">
                            '.$excel_title.'
						</td>
					</tr>
					<tr>
						<td align="center" style="font-size:12px;">
							Periode '.date("d-M-Y", strtotime($param["dp1"])).' <b> - </b> '.date("d-M-Y", strtotime($param["dp2"])).'
						</td>
					</tr>
					<tr>
						<td align="center" style="font-size:12px;">
							Kategori Barang '.trim($param["kategoribarang"]).'
						</td>
					</tr>
					<tr>
						<td align="center" style="font-size:12px;">
							Status Netto '.$param["netto"].'
						</td>
					</tr>
				</table>';

            $content = '
            <br><table width="100%" style="font-weight: bold;">
                <tr>';

            if($param["report"]=='JENISBARANG'){
                $content .='<td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">Jenis Barang</td>';
            } elseif($param["report"]=='KODEBARANG'){
                $content .='<td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">Kode Barang</td>';
            }

            $content .='<td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">Qty Jual</td>
                        <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">Qty Retur</td>
                        <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">Total Qty</td>
                        <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">Total Jual</td>
                        <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">Total Retur</td>
                        <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">Total Rp</td>
                        </tr></table>';

			$jum= count($DataLaporan);
            if ($jum!=0){
                //$content .='<table width="100%">';
                for($i=0; $i<$jum; $i++){

                    if ($i==0){

                        $Group0_Wilayah = trim($DataLaporan[$i]['Wilayah']);
                        $Group1_Partner_Type = trim($DataLaporan[$i]['Partner_Type']);
                        $Group4_Divisi = trim($DataLaporan[$i]['Divisi']);
                        $Group5_Merk = trim($DataLaporan[$i]['Merk']);
    
                        $SumByMerk_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                        $SumByMerk_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                        $SumByMerk_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                        $SumByMerk_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                        $SumByMerk_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                        $SumByMerk_Total = $DataLaporan[$i]['Total'];
    
                        $SumByDivisi_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                        $SumByDivisi_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                        $SumByDivisi_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                        $SumByDivisi_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                        $SumByDivisi_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                        $SumByDivisi_Total = $DataLaporan[$i]['Total'];
                
                        $SumByPartner_Type_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                        $SumByPartner_Type_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                        $SumByPartner_Type_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                        $SumByPartner_Type_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                        $SumByPartner_Type_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                        $SumByPartner_Type_Total = $DataLaporan[$i]['Total'];

                        $SumByWilayah_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                        $SumByWilayah_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                        $SumByWilayah_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                        $SumByWilayah_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                        $SumByWilayah_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                        $SumByWilayah_Total = $DataLaporan[$i]['Total'];
    
                        if ($param["dealer"]!='ALL'){
                            $Group2_Kd_Plg = trim($DataLaporan[$i]['Kd_Plg']);
                            $Group2_Nm_Plg = trim($DataLaporan[$i]['Nm_Plg']);
                            $SumByKd_Plg_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                            $SumByKd_Plg_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                            $SumByKd_Plg_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                            $SumByKd_Plg_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                            $SumByKd_Plg_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                            $SumByKd_Plg_Total = $DataLaporan[$i]['Total'];
                        }

                        $content .='<table width="100%" style="font-weight: bold;"><tr>
                                        <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">WILAYAH '.trim($Group0_Wilayah).'</td>
                                    </tr></table>';

                        $content .='<table width="100%" style="font-weight: bold;"><tr>
                                        <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">PARTNER TYPE '.trim($Group1_Partner_Type).'</td>
                                    </tr></table>';

                        if ($param["dealer"]!='ALL'){
                            $content .='<table width="100%" style="font-weight: bold;"><tr>
                                            <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">DEALER '.trim($Group2_Kd_Plg).' - '.trim($Group2_Nm_Plg).'</td>
                                        </tr></table>';
                        }

                        $content .='<table width="100%" style="font-weight: bold;"><tr>
                                        <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">DIVISI '.trim($Group4_Divisi).'</td>
                                    </tr></table>';

                        $content .='<table width="100%" style="font-weight: bold;"><tr>
                                        <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">MERK '.trim($Group5_Merk).'</td>
                                    </tr></table>';
    
                    } else {
    
                        if (trim($DataLaporan[$i]['Merk'])!=$Group5_Merk){

                            $content .='<table width="100%" style="font-weight: bold;"><tr>
                                            <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL MERK '.$Group5_Merk.'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Qty_Jual,0,",",".").'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Qty_Retur,0,",",".").'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Qty_Total,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Total_Jual,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Total_Retur,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Total,0,",",".").'</td>
                                        </tr></table>';
    
                            $Group5_Merk = trim($DataLaporan[$i]['Merk']);
                            $SumByMerk_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                            $SumByMerk_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                            $SumByMerk_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                            $SumByMerk_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                            $SumByMerk_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                            $SumByMerk_Total = $DataLaporan[$i]['Total'];
                            $GantiJudulMerk = 'Y';
                        } else {

                            if (trim($DataLaporan[$i]['Divisi'])==$Group4_Divisi){
                                $SumByMerk_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                                $SumByMerk_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                                $SumByMerk_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                                $SumByMerk_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                                $SumByMerk_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                                $SumByMerk_Total += $DataLaporan[$i]['Total'];
                                $GantiJudulMerk = 'N';
                            } else {
                                $content .='<table width="100%" style="font-weight: bold;"><tr>
                                                <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL MERK '.$Group5_Merk.'</td>
                                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Qty_Jual,0,",",".").'</td>
                                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Qty_Retur,0,",",".").'</td>
                                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Qty_Total,0,",",".").'</td>
                                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Total_Jual,0,",",".").'</td>
                                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Total_Retur,0,",",".").'</td>
                                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Total,0,",",".").'</td>
                                            </tr></table>';

                                $Group5_Merk = trim($DataLaporan[$i]['Merk']);
                                $SumByMerk_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                                $SumByMerk_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                                $SumByMerk_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                                $SumByMerk_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                                $SumByMerk_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                                $SumByMerk_Total = $DataLaporan[$i]['Total'];
                            }
                        }
    
                        if (trim($DataLaporan[$i]['Divisi'])!=$Group4_Divisi){
    
                            $content .='<table width="100%" style="font-weight: bold;"><tr>
                                            <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL DIVISI '.$Group4_Divisi.'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Jual,0,",",".").'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Retur,0,",",".").'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Total,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total_Jual,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total_Retur,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total,0,",",".").'</td>
                                        </tr></table>';

                            $Group4_Divisi = trim($DataLaporan[$i]['Divisi']);
                            $SumByDivisi_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                            $SumByDivisi_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                            $SumByDivisi_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                            $SumByDivisi_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                            $SumByDivisi_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                            $SumByDivisi_Total = $DataLaporan[$i]['Total'];
                            $GantiJudulDivisi = 'Y';
                        } else {
                            if ($param["dealer"]!='ALL'){
                                if (trim($DataLaporan[$i]['Kd_Plg'])==$Group2_Kd_Plg){
                                    $SumByDivisi_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                                    $SumByDivisi_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                                    $SumByDivisi_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                                    $SumByDivisi_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                                    $SumByDivisi_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                                    $SumByDivisi_Total += $DataLaporan[$i]['Total'];
                                    $GantiJudulDivisi = 'N';
                                } else {
                                    $content .='<table width="100%" style="font-weight: bold;"><tr>
                                                    <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL DIVISI '.$Group4_Divisi.'</td>
                                                    <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Jual,0,",",".").'</td>
                                                    <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Retur,0,",",".").'</td>
                                                    <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Total,0,",",".").'</td>
                                                    <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total_Jual,0,",",".").'</td>
                                                    <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total_Retur,0,",",".").'</td>
                                                    <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total,0,",",".").'</td>
                                                </tr></table>';

                                    $Group4_Divisi = trim($DataLaporan[$i]['Divisi']);
                                    $SumByDivisi_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                                    $SumByDivisi_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                                    $SumByDivisi_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                                    $SumByDivisi_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                                    $SumByDivisi_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                                    $SumByDivisi_Total = $DataLaporan[$i]['Total'];
                                } 
                            } else {
                                if (trim($DataLaporan[$i]['Partner_Type'])==$Group1_Partner_Type){
                                    $SumByDivisi_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                                    $SumByDivisi_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                                    $SumByDivisi_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                                    $SumByDivisi_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                                    $SumByDivisi_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                                    $SumByDivisi_Total += $DataLaporan[$i]['Total'];
                                    $GantiJudulDivisi = 'N';
                                } else {
                                    $content .='<table width="100%" style="font-weight: bold;"><tr>
                                                    <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL DIVISI '.$Group4_Divisi.'</td>
                                                    <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Jual,0,",",".").'</td>
                                                    <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Retur,0,",",".").'</td>
                                                    <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Total,0,",",".").'</td>
                                                    <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total_Jual,0,",",".").'</td>
                                                    <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total_Retur,0,",",".").'</td>
                                                    <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total,0,",",".").'</td>
                                                </tr></table>';

                                    $Group4_Divisi = trim($DataLaporan[$i]['Divisi']);
                                    $SumByDivisi_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                                    $SumByDivisi_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                                    $SumByDivisi_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                                    $SumByDivisi_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                                    $SumByDivisi_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                                    $SumByDivisi_Total = $DataLaporan[$i]['Total'];
                                }
                            }
                        }
    
                        if ($param["dealer"]!='ALL'){
                            if (trim($DataLaporan[$i]['Kd_Plg'])!=$Group2_Kd_Plg){
    
                                $content .='<table width="100%" style="font-weight: bold;"><tr>
                                                <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL DEALER '.$Group2_Kd_Plg.'</td>
                                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Qty_Jual,0,",",".").'</td>
                                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Qty_Retur,0,",",".").'</td>
                                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Qty_Total,0,",",".").'</td>
                                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Total_Jual,0,",",".").'</td>
                                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Total_Retur,0,",",".").'</td>
                                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Total,0,",",".").'</td>
                                            </tr></table>';	

                                $Group2_Kd_Plg = trim($DataLaporan[$i]['Kd_Plg']);
                                $Group2_Nm_Plg = trim($DataLaporan[$i]['Nm_Plg']);
                                $SumByKd_Plg_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                                $SumByKd_Plg_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                                $SumByKd_Plg_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                                $SumByKd_Plg_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                                $SumByKd_Plg_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                                $SumByKd_Plg_Total = $DataLaporan[$i]['Total'];
                                $GantiJudulKd_Plg = 'Y';
                            } else {
                                if (trim($DataLaporan[$i]['Partner_Type'])==$Group1_Partner_Type){
                                    $SumByKd_Plg_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                                    $SumByKd_Plg_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                                    $SumByKd_Plg_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                                    $SumByKd_Plg_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                                    $SumByKd_Plg_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                                    $SumByKd_Plg_Total += $DataLaporan[$i]['Total'];
                                    $GantiJudulKd_Plg = 'N';
                                } else {
                                    $content .='<table width="100%" style="font-weight: bold;"><tr>
                                                    <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL DEALER '.$Group2_Kd_Plg.'</td>
                                                    <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Qty_Jual,0,",",".").'</td>
                                                    <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Qty_Retur,0,",",".").'</td>
                                                    <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Qty_Total,0,",",".").'</td>
                                                    <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Total_Jual,0,",",".").'</td>
                                                    <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Total_Retur,0,",",".").'</td>
                                                    <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Total,0,",",".").'</td>
                                                </tr></table>';

                                    $Group4_Divisi = trim($DataLaporan[$i]['Divisi']);
                                    $SumByKd_Plg_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                                    $SumByKd_Plg_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                                    $SumByKd_Plg_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                                    $SumByKd_Plg_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                                    $SumByKd_Plg_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                                    $SumByKd_Plg_Total = $DataLaporan[$i]['Total'];
                                }
                            }
                        }
    
                        if (trim($DataLaporan[$i]['Partner_Type'])!=$Group1_Partner_Type){

                            $content .='<table width="100%" style="font-weight: bold;"><tr>
                                            <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL PARTNER TYPE '.$Group1_Partner_Type.'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Qty_Jual,0,",",".").'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Qty_Retur,0,",",".").'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Qty_Total,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Total_Jual,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Total_Retur,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Total,0,",",".").'</td>
                                        </tr></table>';	
                        
                            $Group1_Partner_Type = trim($DataLaporan[$i]['Partner_Type']);
                            $SumByPartner_Type_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                            $SumByPartner_Type_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                            $SumByPartner_Type_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                            $SumByPartner_Type_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                            $SumByPartner_Type_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                            $SumByPartner_Type_Total = $DataLaporan[$i]['Total'];
                            $GantiJudulPartner_Type = 'Y';
                        } else {
                            if (trim($DataLaporan[$i]['Wilayah'])==$Group0_Wilayah){
                                $SumByPartner_Type_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                                $SumByPartner_Type_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                                $SumByPartner_Type_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                                $SumByPartner_Type_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                                $SumByPartner_Type_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                                $SumByPartner_Type_Total += $DataLaporan[$i]['Total'];
                                $GantiJudulPartner_Type = 'N';
                            } else {
                                $content .='<table width="100%" style="font-weight: bold;"><tr>
                                                <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL PARTNER TYPE '.$Group1_Partner_Type.'</td>
                                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Qty_Jual,0,",",".").'</td>
                                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Qty_Retur,0,",",".").'</td>
                                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Qty_Total,0,",",".").'</td>
                                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Total_Jual,0,",",".").'</td>
                                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Total_Retur,0,",",".").'</td>
                                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Total,0,",",".").'</td>
                                            </tr></table>';	
                            
                                $Group1_Partner_Type = trim($DataLaporan[$i]['Partner_Type']);
                                $SumByPartner_Type_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                                $SumByPartner_Type_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                                $SumByPartner_Type_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                                $SumByPartner_Type_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                                $SumByPartner_Type_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                                $SumByPartner_Type_Total = $DataLaporan[$i]['Total'];
                            }
                        }

                        if (trim($DataLaporan[$i]['Wilayah'])!=$Group0_Wilayah){

                            $content .='<table width="100%" style="font-weight: bold;"><tr>
                                            <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL WILAYAH '.$Group0_Wilayah.'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByWilayah_Qty_Jual,0,",",".").'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByWilayah_Qty_Retur,0,",",".").'</td>
                                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByWilayah_Qty_Total,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByWilayah_Total_Jual,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByWilayah_Total_Retur,0,",",".").'</td>
                                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByWilayah_Total,0,",",".").'</td>
                                        </tr></table>';	

                            $Group0_Wilayah = trim($DataLaporan[$i]['Wilayah']);
                            $SumByWilayah_Qty_Jual = $DataLaporan[$i]['Qty_Jual'];
                            $SumByWilayah_Qty_Retur = $DataLaporan[$i]['Qty_Retur'];
                            $SumByWilayah_Qty_Total = $DataLaporan[$i]['Qty_Total'];
                            $SumByWilayah_Total_Jual = $DataLaporan[$i]['Total_Jual'];
                            $SumByWilayah_Total_Retur = $DataLaporan[$i]['Total_Retur'];
                            $SumByWilayah_Total = $DataLaporan[$i]['Total'];
                            $GantiJudulWilayah = 'Y';
                        } else {
                            $SumByWilayah_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                            $SumByWilayah_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                            $SumByWilayah_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                            $SumByWilayah_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                            $SumByWilayah_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                            $SumByWilayah_Total += $DataLaporan[$i]['Total'];
                            $GantiJudulWilayah = 'N';
                        }

                        if ($GantiJudulWilayah == 'Y'){
                            $content .='<table width="100%" style="font-weight: bold;"><tr>
                                            <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">WILAYAH '.trim($Group0_Wilayah).'</td>
                                        </tr></table>';
                        }

                        if ($GantiJudulPartner_Type == 'Y'){
                            $content .='<table width="100%" style="font-weight: bold;"><tr>
                                            <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">PARTNER TYPE '.trim($Group1_Partner_Type).'</td>
                                        </tr></table>';
                        }

                        if ($GantiJudulKd_Plg == 'Y'){
                            if ($param["dealer"]!='ALL'){
                                $content .='<table width="100%" style="font-weight: bold;"><tr>
                                                <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">DEALER '.trim($Group2_Kd_Plg).' - '.trim($Group2_Nm_Plg).'</td>
                                            </tr></table>';
                            }
                        }

                        if ($GantiJudulDivisi == 'Y'){
                            $content .='<table width="100%" style="font-weight: bold;"><tr>
                                            <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">DIVISI '.trim($Group4_Divisi).'</td>
                                        </tr></table>';
                        }

                        if ($GantiJudulMerk == 'Y'){
                            $content .='<table width="100%" style="font-weight: bold;"><tr>
                                            <td style="text-align: left; width: 100%; font-size: 12px; padding:5px;">MERK '.trim($Group5_Merk).'</td>
                                        </tr></table>';
                        }

    
                    }

                    //start isi data - from here
                    $content .='<table width="100%"><tr>';
                    if($param["report"]=='JENISBARANG'){
                        $content .='<td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">'.$DataLaporan[$i]['Jns_Brg'].'</td>';
                    } elseif($param["report"]=='KODEBARANG'){
                        $content .='<td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">'.$DataLaporan[$i]['Kd_Brg'].'</td>';
                    }
                    $content .='
                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]['Qty_Jual'],0,",",".").'</td>
                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]['Qty_Retur'],0,",",".").'</td>
                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]['Qty_Total'],0,",",".").'</td>
                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]['Total_Jual'],0,",",".").'</td>
                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]['Total_Retur'],0,",",".").'</td>
                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]['Total'],0,",",".").'</td>
                                </tr></table>';

                    $SumByAll_Qty_Jual += $DataLaporan[$i]['Qty_Jual'];
                    $SumByAll_Qty_Retur += $DataLaporan[$i]['Qty_Retur'];
                    $SumByAll_Qty_Total += $DataLaporan[$i]['Qty_Total'];
                    $SumByAll_Total_Jual += $DataLaporan[$i]['Total_Jual'];
                    $SumByAll_Total_Retur += $DataLaporan[$i]['Total_Retur'];
                    $SumByAll_Total += $DataLaporan[$i]['Total'];  
                    //start isi data - until here           
                }
                //$content .='</table';
            }

            $content .='<table width="100%" style="font-weight: bold;"><tr>
                            <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL MERK '.$Group5_Merk.'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Qty_Jual,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Qty_Retur,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Qty_Total,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Total_Jual,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Total_Retur,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByMerk_Total,0,",",".").'</td>
                        </tr></table>';

            $content .='<table width="100%" style="font-weight: bold;"><tr>
                            <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL DIVISI '.$Group4_Divisi.'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Jual,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Retur,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Qty_Total,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total_Jual,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total_Retur,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByDivisi_Total,0,",",".").'</td>
                        </tr></table>';

            if ($param["dealer"]!='ALL'){
                $content .='<table width="100%" style="font-weight: bold;"><tr>
                                <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL DEALER '.$Group2_Kd_Plg.'</td>
                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Qty_Jual,0,",",".").'</td>
                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Qty_Retur,0,",",".").'</td>
                                <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Qty_Total,0,",",".").'</td>
                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Total_Jual,0,",",".").'</td>
                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Total_Retur,0,",",".").'</td>
                                <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByKd_Plg_Total,0,",",".").'</td>
                            </tr></table>';	
            }

            $content .='<table width="100%" style="font-weight: bold;"><tr>
                            <td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">TOTAL PARTNER TYPE '.$Group1_Partner_Type.'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Qty_Jual,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Qty_Retur,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Qty_Total,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Total_Jual,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Total_Retur,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByPartner_Type_Total,0,",",".").'</td>
                        </tr></table>';	
			
			$content .='<table width="100%" style="font-weight: bold;"><tr>
							<td style="text-align: left; width: 25%; font-size: 12px; padding:5px;">GRAND TOTAL</td>
							<td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByAll_Qty_Jual,0,",",".").'</td>
							<td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByAll_Qty_Retur,0,",",".").'</td>
                            <td style="text-align: right; width: 10%; font-size: 12px; padding:5px;">'.number_format($SumByAll_Qty_Total,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByAll_Total_Jual,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByAll_Total_Retur,0,",",".").'</td>
                            <td style="text-align: right; width: 15%; font-size: 12px; padding:5px;">'.number_format($SumByAll_Total,0,",",".").'</td>
						</tr></table>';	

			set_time_limit(60);
            if($output=='HTML'){
                echo ($header.$content);
            } else {
                $mpdf->SetHTMLHeader($header,'','1');
                $mpdf->WriteHTML($content);
                $mpdf->Output();
            }
		}

    }
    ?>