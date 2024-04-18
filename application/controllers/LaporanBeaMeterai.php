<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class LaporanBeaMeterai extends MY_Controller 
	{
				
		public function __construct()
		{
			parent::__construct();
			$this->load->model('HelperModel');
			$this->load->helper('FormLibrary');
			$this->load->library('excel');
			$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
		}
		
		public function index()
		{
			$data = array();
			$api = 'APITES';
			
			set_time_limit(60);
			
			$data['title'] = 'Laporan Bea Meterai | '.WEBTITLE;

			$params = array();   
	   		$params['LogDate'] = date("Y-m-d H:i:s");
	   		$params['UserID'] = $_SESSION["logged_in"]["userid"];
	   		$params['UserName'] = $_SESSION["logged_in"]["username"];
	   		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module']="LAPORAN BEA METERAI"; 
	   		$params['TrxID'] = date("YmdHis");
			$params['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN BEA METERAI";
	   		$params['Remarks']="SUCCESS";
	   		$params['RemarksDate'] = date("Y-m-d H:i:s");
	   		$this->ActivityLogModel->insert_activity($params); 
			
			// print_r($data);
			// die;
			$this->RenderView('LaporanBeaMeteraiView',$data);
		}
		
		public function Proses()
		{
			// $data = array();
			// $data["dp1"]=$_POST['dp1'];
			
			$page_title = 'Laporan Bea Meterai';
						
			if (empty($_POST["cbox1"]) ){ 
				$_POST["cbox1"]="N";
			}
			if (empty($_POST["cbox2"]) ){ 
				$_POST["cbox2"]="N";
			}

			// print_r($_POST["cbox1"]);
			// die;

			// $this->load->library('form_validation');
			// $this->form_validation->set_rules('laporan','Nama Laporan','required');
			// $this->form_validation->set_rules('dp1','Tanggal Awal','required');
			// $this->form_validation->set_rules('dp2','Tanggal Akhir','required');
				
			// if($this->form_validation->run())
			// {
				$this->Preview($page_title, $_POST["dp1"], $_POST["dp2"], $_POST["cbox1"], $_POST["cbox2"]);
			// }
			// else
			// {
			// 	redirect("Dashboard");
			// }
			
		}
					
		
		public function Preview($page_title, $p_tgl1, $p_tgl2, $p_cbox1='', $p_cbox2='')
		{				

 
			$params = array();   
	   		$params['LogDate'] = date("Y-m-d H:i:s");
	   		$params['UserID'] = $_SESSION["logged_in"]["userid"];
	   		$params['UserName'] = $_SESSION["logged_in"]["username"];
	   		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module']="LAPORAN BEA METERAI"; 
	   		$params['TrxID'] = date("YmdHis");
			$params['Description']=$_SESSION["logged_in"]["username"]." PROSES EXCEL LAPORAN BEA METERAI";
	   		$params['Remarks']="";
	   		$params['RemarksDate'] = 'NULL';
	   		$this->ActivityLogModel->insert_activity($params); 

			$api = 'APITES';
			
			// print_r($p_tgl2);
			// die;
			
			$mainUrl = $_SESSION["conn"]->AlamatWebService;
			//MsDatabase - AlamatWebService 
			//$url = "http://10.1.0.92:8080/";
			//die($mainUrl);
			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;
						
			set_time_limit(60);
			// $mainUrl.=$this->API_BKT;
			$mainUrl='http://localhost:90/bktAPI';

			$BeaMeterai = json_decode(file_get_contents($mainUrl."/LaporanBeaMeterai/Proses?api=".$api."&p_tgl1=".urlencode($p_tgl1)."&p_tgl2=".urlencode($p_tgl2)."&p_cbox1=".urlencode($p_cbox1)."&p_cbox2=".urlencode($p_cbox2)));
			
			// die($mainUrl."/LaporanBeaMeterai/Proses?api=".$api."&p_tgl1=".urlencode($p_tgl1)."&p_tgl2=".urlencode($p_tgl2)."&p_cbox1=".urlencode($p_cbox1)."&p_cbox2=".urlencode($p_cbox2));
			
			// print_r(count($BeaMeterai));
			// die;
			
			if(count($BeaMeterai)==0){
				$params['Remarks']="FAILED - Tidak Ada Data";
			   	$params['RemarksDate'] = date("Y-m-d H:i:s");
			   	$this->ActivityLogModel->update_activity($params);
				exit('Tidak ada data');
			}

			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$sheet->setTitle('LaporanBeaMeterai');
			$sheet->setCellValue('A1', 'LAPORAN BEA METERAI');
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', 'Periode : '.$p_tgl1.' sd '.$p_tgl2);
            // $sheet->setCellValue('A3', 'Meterai=0 : '.$p_cbox1);
			// $sheet->setCellValue('A4', 'Meterai>0 : '.$p_cbox2);
								
			$currcol = 1;
			$currrow = 6;
						
			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No');
			$sheet->getColumnDimension('A')->setWidth(5);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Jenis Dokumen');
			$sheet->getColumnDimension('B')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nomor Dokumen');
			$sheet->getColumnDimension('C')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tanggal Dokumen');
			$sheet->getColumnDimension('D')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nilai Dokumen');
			$sheet->getColumnDimension('E')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Jenis Identitas');
			$sheet->getColumnDimension('F')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nomor Identitas');
			$sheet->getColumnDimension('G')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama Terutang');
			$sheet->getColumnDimension('H')->setWidth(45);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Bea Meterai Dipungut');
			$sheet->getColumnDimension('I')->setWidth(20);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Emeterai SN');
			$sheet->getColumnDimension('J')->setWidth(30);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
											
			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
						
			
			// Detail
			foreach($BeaMeterai as $BM) {
				
				$currrow++;
				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->No);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Jenis_Dokumen);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Nomor_Dokumen);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("m-d-Y",strtotime($BM->Tanggal_Dokumen)));
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($BM->Nilai_Dokumen,2,",","."));
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Jenis_Identitas);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Nomor_Identitas);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->Nama_Terutang);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($BM->Bea_Meterai_Dipungut,2,",","."));
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $BM->EMeteraiSN);
				$currcol += 1;
			}
			
			
			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A6:'.$max_col.'7')->getAlignment()->setHorizontal($alignment_center);
			
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A6:'.$max_col.'7')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
			// border
			$sheet->getStyle("A1:".$max_col."7")->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet->getStyle('A6:'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');
			
				
			$filename='LaporanBeaMeterai['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 
			exit();
		
		}
		
		
	}												