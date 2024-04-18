<?php 
	header('Access-Control-Allow-Origin:*');
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

	ini_set('pcre.backtrack_limit', 10000000); 
	ini_set('memory_limit', '4096M');

	class ReportSPHTerpakai extends MY_Controller 
	{
		public function __construct()
		{
			parent::__construct();
			$this->load->model('HelperModel');
			$this->load->helper('FormLibrary');
			$this->load->library('excel');
			$this->load->model('ConfigSysModel');
			$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
		}
      
		public function index() 
		{
			$data = array();
			$api = 'APITES';
			set_time_limit(60);
			//persiapkan bktAPI
			$res = $this->MasterDbModel->getByLocationCode($_SESSION['logged_in']['branch_id']);
			$AlamatBKTAPI = $res->AlamatWebService;
			$ServerBKTAPI = $res->Server;
			$DatabaseBKTAPI = $res->Database;

			$url = "http://localhost:90/bktAPI/ReportSPHTerpakai/getWilayah?api=".$api."&srv=".$ServerBKTAPI."&db=".$DatabaseBKTAPI;
			//$url = $AlamatBKTAPI.$this->API_BKT."/ReportSPHTerpakai/getWilayah?api=".$api."&srv=".$ServerBKTAPI."&db=".$DatabaseBKTAPI;
			// die($url);       
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 1000,
				CURLOPT_POST => 1,
				CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
	  
      $hasil = json_decode($response);
      if ($hasil->result == "sukses") {
        $data["wilayah"] = $hasil->data;
      } else {
        $data["wilayah"] = "";
      }

			$data['title'] = 'Laporan SPH Terpakai';
			//echo json_encode($data);
			$this->RenderView('ReportSPHTerpakaiView',$data);
		}  

		public function getToko() 
		{
			$data = array();
			$api = 'APITES';
			set_time_limit(60);
			$res = $this->MasterDbModel->getByLocationCode($_SESSION['logged_in']['branch_id']);
			$AlamatBKTAPI = $res->AlamatWebService;
			$ServerBKTAPI = $res->Server;
			$DatabaseBKTAPI = $res->Database;

			
			$_POST = $this->PopulatePost();	
			$wilayah = urldecode($_POST["wilayah"]);

      $url = "http://localhost:90/bktAPI/ReportSPHTerpakai/getToko?api=".$api."&srv=".$ServerBKTAPI."&db=". $DatabaseBKTAPI . "&wilayah=" . urlencode($wilayah);
			//$url = $AlamatBKTAPI.$this->API_BKT."/ReportSPHTerpakai/getToko?api=".$api."&srv=".$ServerBKTAPI."&wilayah=".urlencode($wilayah);
			
			//die($url);
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 1000,
				CURLOPT_POST => 1,
				CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);

      $hasil = json_decode($response);
      if ($hasil->result == "sukses") {
        $data["toko"] = $hasil->data;
      } else {
        $data["toko"] = array();
      }
      echo json_encode($data);
		}
    
    public function searchNamaToko() 
		{
			$data = array();
			$api = 'APITES';
			set_time_limit(60);
			$res = $this->MasterDbModel->getByLocationCode($_SESSION['logged_in']['branch_id']);
			$AlamatBKTAPI = $res->AlamatWebService;
			$ServerBKTAPI = $res->Server;
			$DatabaseBKTAPI = $res->Database;

			
			$_POST = $this->PopulatePost();	
			$wilayah = urldecode($_POST["wilayah"]);
      $namaToko = urldecode($_POST["nama_toko"]);

      $url = "http://localhost:90/bktAPI/ReportSPHTerpakai/getNamaToko?api=".$api."&srv=".$ServerBKTAPI."&db=". $DatabaseBKTAPI . "&wilayah=" . urlencode($wilayah) . "&nama_toko=" . urlencode($namaToko);
			//$url = $AlamatBKTAPI.$this->API_BKT."/ReportSPHTerpakai/getNamaToko?api=".$api."&srv=".$ServerBKTAPI."&wilayah=".urlencode($wilayah)."&nama_toko=" . urlencode($namaToko);
			
			//die($url);
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 1000,
				CURLOPT_POST => 1,
				CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);

      $hasil = json_decode($response);
      if ($hasil->result == "sukses") {
        $data["toko"] = $hasil->data;
      } else {
        $data["toko"] = array();
      }
      echo json_encode($data);
		}

	
		public function Proses()
		{

			$api = 'APITES';
			set_time_limit(0);

			$_POST = $this->PopulatePost();	
			$params["dp1"] = urldecode($_POST["dp1"]);
			$params["dp2"] = urldecode($_POST["dp2"]);
			$params["wilayah"] = urldecode($_POST["wilayah"]);
			$params["toko"] = urldecode($_POST["toko"]);
			$params["sph"] = urldecode($_POST["sph"]);

			//echo json_encode($params);
			// die();

      //persiapkan bktAPI
      $res = $this->MasterDbModel->getByLocationCode($_SESSION['logged_in']['branch_id']);
      //$AlamatBKTAPI = $res->AlamatWebService;
      $AlamatBKTAPI = 'http://localhost:90/';
      $ServerBKTAPI = $res->Server;
      $DatabaseBKTAPI = $res->Database;

      //$page_title Maximum 31 characters allowed in sheet title
      $page_title = "Laporan SPH Terpakai";
      $excel_title = "Laporan SPH Terpakai";
      $url = $AlamatBKTAPI.$this->API_BKT. "/ReportSPHTerpakai/getReport?api=".$api;
      
      $url .= "&svr=".urlencode($ServerBKTAPI).
      "&db=".urlencode($DatabaseBKTAPI).
      "&dp1=".urlencode(date('Y-M-d', strtotime($params['dp1']))).
      "&dp2=".urlencode(date('Y-M-d', strtotime($params['dp2']))).
      "&wilayah=".urlencode($params["wilayah"]).
      "&toko=".urlencode($params["toko"]).
      "&sph=".urlencode($params["sph"]);
      // die($url);      

      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 1000,
        CURLOPT_POST => 1,
        CURLOPT_ENCODING => '',
        CURLOPT_HTTPHEADER => array("Content-type: application/json")
      ));
      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);

      // echo ($response);
      // die;

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
			
			//echo json_encode($result["data"]); die;
			if(isset($_POST['btnPDF'])) {
				$this->proses_pdf($page_title, $excel_title, $params, $result["data"],'HTML');
			} elseif(isset($_POST['btnPDF'])) {
				$this->proses_pdf($page_title, $excel_title, $params, $result["data"],'PDF');
			} elseif(isset($_POST['btnExcel'])) {
				$this->proses_excel($page_title, $excel_title, $params, $result["data"],'SAVE');
			} 

		}

    public function proses_excel($page_title, $excel_title, $param, $DataLaporan)
    {
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet(0);

      $sheet->setTitle($page_title);
      $sheet->setCellValue('A1', $page_title);
      $sheet->getStyle('A1')->getFont()->setSize(20);
      $sheet->setCellValue('A2', 'Periode  : ' . date("d-M-Y", strtotime($param["dp1"])) . " s/d " . date("d-M-Y", strtotime($param["dp2"])));
      $sheet->setCellValue('A3', 'Wilayah  : ' . $param["wilayah"]);

      $currrow = 2;
      $currcol = 1;

      $currrow++;
      $currrow++;
      $currcol = 1;
      $sheet->setCellValueByColumnAndRow($currcol, $currrow, "TGL SPH");
      $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow + 1);
      $currcol += 1;
      $sheet->setCellValueByColumnAndRow($currcol, $currrow, "No SPH");
      $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow + 1);
      $currcol += 1;
      $sheet->setCellValueByColumnAndRow($currcol, $currrow, "KD PLG");
      $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow + 1);
      $currcol += 1;
      $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Nama Pelanggan");
      $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow + 1);
      $currcol += 1;
      $sheet->setCellValueByColumnAndRow($currcol, $currrow, "No NK");
      $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow + 1);
      $currcol += 1;
      $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Total NK");
      $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow + 1);
      $currcol += 1;
      $sheet->setCellValueByColumnAndRow($currcol, $currrow, "No Kwitansi");
      $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow + 1);
      $currcol += 1;
      $sheet->setCellValueByColumnAndRow($currcol, $currrow, "No Faktur");
      $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow + 1);
      $currcol += 1;
      $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Total Bayar");
      $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow + 1);
      $currcol += 1;
      $sheet->setCellValueByColumnAndRow($currcol, $currrow, "No Distribusi");
      $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow + 1);
      $currcol += 1;
      $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Sisa Nk");
      $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow + 1);
      $sheet->getStyle("A" . $currrow . ":" . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol) . $currrow + 1)->getFont()->setBold(true);
      $sheet->getStyle("A" . $currrow . ":" . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol) . $currrow + 1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
      $currrow++;

      $No_SPH = "";
      $TotalNK = 0;
      $TotalBayar = 0;
      $SisaNK = 0;
      $jum = count($DataLaporan);
      $no = 1;
      for ($i = 0; $i < $jum; $i++) {

        if (!empty($DataLaporan[$i]->Tgl_SPH) && $DataLaporan[$i]->Tgl_SPH !== null) {
          $Tgl_SPH = date("d-M-Y", strtotime($DataLaporan[$i]->Tgl_SPH));
        } else {
          $Tgl_SPH = '';
        }

        if ($No_SPH == $DataLaporan[$i]->No_SPH && count($DataLaporan[$i]->RecordCount) > 1) {
          $currcol = 6;
          $sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalBayar);
          $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol - 1) . $currrow)->getNumberFormat()->setFormatCode('#,##0');
          $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+1);
          $currcol += 1;
          $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
          $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+1);
          $currcol += 1;
          $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->No_Faktur);
          $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+1);
          $currcol += 1;
          $sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalBayar);
          $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol - 1) . $currrow)->getNumberFormat()->setFormatCode('#,##0');
          $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+1);
          $currcol += 1;
          $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
          $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+1);
          $currcol += 1;
          $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
          $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+1);
          $currrow++;

          $TotalBayar = $TotalBayar + $DataLaporan[$i]->Total_Bukti;
        }  else {
          $currrow++;
          $currcol = 1;
          $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Tgl_SPH);
          $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+1);
          $sheet->getStyle("A" . $currrow . ":" . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol) . ($currrow + 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);	
          $currcol += 1;
          $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->No_SPH);
          $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+1);
          $sheet->getStyle("B" . $currrow . ":" . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol) . ($currrow + 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);	

          $currcol += 1;
          $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Kd_plg);
          $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+1);
          $sheet->getStyle("C" . $currrow . ":" . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol) . ($currrow + 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);	

          $currcol += 1;
          $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Nm_Plg);
          $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+1);
          $sheet->getStyle("D" . $currrow . ":" . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol) . ($currrow + 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);	

          $currcol += 1;
          $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->No_NK);
          $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow + 1);
          $sheet->getStyle("E" . $currrow . ":" . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol) . ($currrow + 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);	

          $currcol += 1;
          $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Total_NK);
          $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol - 1) . $currrow)->getNumberFormat()->setFormatCode('#,##0');
          $currcol += 1;
          $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->No_Kwitansi);
          $currcol += 1;
          $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->No_Faktur);
          $currcol += 1;
          $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Total_Bukti);
          $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol - 1) . $currrow)->getNumberFormat()->setFormatCode('#,##0');
          $currcol += 1;
          $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->No_Distribusi);
          $currcol += 1;
          $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
          $currrow++;

          $TotalNK = $TotalNK + $DataLaporan[$i]->Total_NK;
          $TotalBayar = $TotalBayar + $DataLaporan[$i]->Total_Bukti;
          $SisaNK = $TotalNK - $TotalBayar;

          if ($DataLaporan[$i]->RecordCount == 1) {
            $currcol = 6;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalNK);
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol - 1) . $currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalBayar);
            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol - 1) . $currrow)->getNumberFormat()->setFormatCode('#,##0');
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SisaNK);
            $sheet->getStyle("F" . $currrow . ":" . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol) . ($currrow + 1))->getFont()->setBold(true);
            //$sheet->getStyle("F" . $currrow . ":" . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol) . ($currrow + 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);	
          }

          $currrow++;
        }
        $TotalNK = 0;
        $TotalBayar = 0;
        $SisaNK = 0;
        $No_SPH = $DataLaporan[$i]->No_SPH;
        $no++;
      }

      $max_col = $currcol - 1;
      $max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
      $fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
      // autosize column
      foreach (range('A', $max_col) as $columnID) {
        $spreadsheet->getActiveSheet(0)->getColumnDimension($columnID)->setWidth(20);
      }

      $filename = $excel_title; //save our workbook as this file name
      $writer = new Xlsx($spreadsheet);
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
      header('Cache-Control: max-age=0');
      ob_end_clean();
      $writer->save('php://output');  // download file
      exit();
    }

    public function proses_pdf($page_title, $excel_title, $param, $DataLaporan, $output)
    {

      require_once __DIR__ . '\vendor\autoload.php';
      $mpdf = new \Mpdf\Mpdf(array(
          'mode' => '',
          'format' => 'A4',
          'default_font_size' => 8,
          'default_font' => 'arial',
          'margin_left' => 10,
          'margin_right' => 10,
          'margin_top' => 45,
          'margin_bottom' => 10,
          'margin_header' => 10,
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
                  '.date("d-M-Y", strtotime($param["dp1"])).' <b> s/d </b> '.date("d-M-Y", strtotime($param["dp2"])). '
                  </td>
              </tr>
              <tr>
                  <td align="center" style="font-size:12px;">
                    Wilayah  '. trim($param["wilayah"]) .'
                  </td>
              </tr>';

      
      $header .= '<table width="100%" style="font-weight: bold;">
                  </table>';

      $content = '
      <br><table width="100%" border="1" style=" border-collapse: collapse;"> '; 


      $content .= '<tr >
                  <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" >TGL SPH P</td>
                  <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" >No SPH P</td> 
                  <td style="text-align: center; width: 10%; font-size: 12px; padding:5px; font-weight: bold;" >KD PLG</td>
                  <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" >Nama Pelanggan</td>
                  <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" >No NK</td>
                  <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" >Total NK</td>
                  <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" >No Kwitansi</td> 
                  <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" >No Faktur</td>
                  <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" >Total Bayar</td> 
                  <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" >No Distribusi</td> 
                  <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" >Sisa NK</td>  
                  </tr>'; 


      $No_SPH = "";
      $TotalNK = 0;
      $TotalBayar = 0;
      $SisaNK = 0;

      $jum= count($DataLaporan);
      if ($jum!=0){
        $no = 1;
        for($i=0; $i<$jum; $i++){   
          if (!empty($DataLaporan[$i]->Tgl_SPH) && $DataLaporan[$i]->Tgl_SPH !== null) {
            $Tgl_SPH = date("d-M-Y", strtotime($DataLaporan[$i]->Tgl_SPH));
          } else {
            $Tgl_SPH = '';
          }
            $content .= '<tr> 
                <td style="text-align: left; font-size: 12px; padding:5px;" rowspan="2">'. $Tgl_SPH. '</td> 
                <td style="text-align: left; font-size: 12px; padding:5px;" rowspan="2">'.$DataLaporan[$i]->No_SPH. '</td> 
                <td style="text-align: left; font-size: 12px; padding:5px;" rowspan="2">' .$DataLaporan[$i]->Kd_plg . '</td>
                <td style="text-align: left; font-size: 12px; padding:5px;" rowspan="2">' . $DataLaporan[$i]->Nm_Plg . '</td>
                <td style="text-align: left; font-size: 12px; padding:5px;" rowspan="2">' . $DataLaporan[$i]->No_NK . '</td> 
                <td style="text-align: right; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->Total_NK,0,",","."). '</td> 
                <td style="text-align: left; font-size: 12px; padding:5px;">' . $DataLaporan[$i]->No_Kwitansi . '</td> 
                <td style="text-align: left; font-size: 12px; padding:5px;">' . $DataLaporan[$i]->No_Faktur . '</td> 
                <td style="text-align: right; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->Total_Bukti,0,",",".").'</td> 
                <td style="text-align: left; font-size: 12px; padding:5px;">'.$DataLaporan[$i]->No_Distribusi.'</td> 
                <td style="text-align: left; font-size: 12px; padding:5px;"></td>  
            </tr>';

            $TotalNK = $TotalNK + $DataLaporan[$i]->Total_NK;
            $TotalBayar = $TotalBayar + $DataLaporan[$i]->Total_Bukti;
            $SisaNK = $TotalNK - $TotalBayar;
            if ($DataLaporan[$i]->RecordCount == 1) {
              $content .='<tr> 
                <td style="text-align: left; font-size: 12px; padding:5px; font-weight: bold;" colspan="5"></td> 
                <td style="text-align: right; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($TotalNK,0,",","."). '</td> 
                <td style="text-align: left; font-size: 12px; padding:5px; font-weight: bold;"></td> 
                <td style="text-align: left; font-size: 12px; padding:5px; font-weight: bold;"></td> 
                <td style="text-align: right; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($TotalBayar,0,",","."). '</td> 
                <td style="text-align: right; font-size: 12px; padding:5px; font-weight: bold;"></td>
                <td style="text-align: right; font-size: 12px; padding:5px; font-weight: bold;">' . number_format($SisaNK, 0, ",", ".") . '</td>  
              </tr>';
            }

            $no++; 
          }
        
          $content .='</table>';  

          set_time_limit(0);
          if($output=='HTML'){
            echo ($header.$content);
          } else {
            $mpdf->SetHTMLHeader($header,'','1');
            $mpdf->WriteHTML($content);
            $mpdf->Output();
            ob_clean();
          }
      } else {
        set_time_limit(0);
        if($output=='HTML'){
          echo ("Tidak Ada Data");
        } else {
          $mpdf->SetHTMLHeader("Tidak Ada Data",'','1');
          $mpdf->WriteHTML("");
          $mpdf->Output();
          ob_clean();
        }
      }
    } 
	}

?>
