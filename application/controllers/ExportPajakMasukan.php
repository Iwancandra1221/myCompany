<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportPajakMasukan extends MY_Controller {


	public function __construct()
	{
		parent::__construct();
		$this->load->library('excel');
		$this->load->model('ActivityLogModel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function index()
	{
		$checkAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		if($_SESSION["can_read"]==true){
			$post = $this->PopulatePost();

			if(!empty($post['dari']) && !empty($post['sampai'])){
				
				$LogDate = date("Y-m-d H:i:s");
				$this->Logs_insert($LogDate,'MENAMPILKAN PAJAK MASUKAN');

				$dari 		= $post['dari'];
				$sampai 	= $post['sampai'];
				$product	= $post['product'];
				$sparepart	= $post['sparepart'];

				$url = $this->API_URL.'/ExportPajakMasukan/getExport?api=APITES&dari='.$dari.'&sampai='.$sampai.'&product='.$product.'&sparepart='.$sparepart;
				$result = json_decode(file_get_contents($url));

			  	$page_title = "EXPORT PAJAK MASUKAN";

				$spreadsheet = new Spreadsheet();
				$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
				$sheet = $spreadsheet->getActiveSheet(0);

				$sheet->setTitle('ExportPajakMasukan');
				$sheet->setCellValue("A1", "FM");
				$sheet->setCellValue("B1", "KD_JENIS_TRANSAKSI");
				$sheet->setCellValue("C1", "FG_PENGGANTI");
				$sheet->setCellValue("D1", "NOMOR_FAKTUR");
				$sheet->setCellValue("E1", "MASA_PAJAK");
				$sheet->setCellValue("F1", "TAHUN_PAJAK");
				$sheet->setCellValue("G1", "TANGGAL_FAKTUR");
				$sheet->setCellValue("H1", "NPWP");
				$sheet->setCellValue("I1", "NAMA");
				$sheet->setCellValue("J1", "ALAMAT_LENGKAP");
				$sheet->setCellValue("K1", "JUMLAH_DPP");
				$sheet->setCellValue("L1", "JUMLAH_PPN");
				$sheet->setCellValue("M1", "JUMLAH_PPNBM");
				$sheet->setCellValue("N1", "IS_CREDITABLE");
				$sheet->setCellValue("O1", "FIELD_TAMBAHAN_1");
				$sheet->setCellValue("P1", "FIELD_TAMBAHAN_2");
				$sheet->setCellValue("Q1", "FIELD_TAMBAHAN_3");
				$sheet->setCellValue("R1", "FIELD_TAMBAHAN_4");
				$sheet->setCellValue("S1", "FIELD_TAMBAHAN_5");



				$sheet->getStyle('A1:S1')->getFont()->setSize(12);
				$sheet->getStyle('A1:S1')->getFont()->setBold(true);

				$currrow = 2;	
				

				foreach ($result as $key => $r) {

					$currcol = 1;
					
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->FM);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->KD_JENIS_TRANSAKSI);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->FG_PENGGANTI);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->NOMOR_FAKTUR);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->MASA_PAJAK);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->TAHUN_PAJAK);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->TANGGAL_FAKTUR);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->NPWP);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->NAMA);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->ALAMAT_LENGKAP);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->JUMLAH_DPP);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->JUMLAH_PPN);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->JUMLAH_PPNBM);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->IS_CREDITABLE);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');

					$currrow++;
				}



			    if(!empty($product) || !empty($sparepart)){
				    if($product==1 && $sparepart==1){
					    $name_file = 'All';
					}else if($product==1){
						$name_file = 'Product';
					}else{
						$name_file = 'Sparepart';
					}
				}else{
					$name_file = 'All';
				}


				$rand = rand(100,999);

				$this->Logs_Update($LogDate,'SUCCESS','MENAMPILKAN EXPORT PAJAK MASUKAN');
				
				$filename='PajakMasukan_'.$name_file.'_'.$rand;
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
		        $writer->save('php://output');
		        exit();
			}

			$this->RenderView('ExportPajakMasukanView');	

			$params = array(); 
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "EXPORT PAJAK MASUKAN";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU EXPORT PAJAK MASUKAN";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

		}else{
			redirect("Dashboard");
		}
	}


	function Logs_insert($LogDate='',$description=''){
		$params = array();   
		$params['LogDate'] = $LogDate;
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
	   	$params['Module'] = "EXPORT PAJAK MASUKAN";
		$params['TrxID'] = date_format(date_create($LogDate),'YmdHis');
		$params['Description'] = $_SESSION["logged_in"]["username"]." ".$description;
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);
	}

	function Logs_Update($LogDate='',$remarks='',$description=''){
		$params = array();   
		$params['LogDate'] = $LogDate;
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
	   	$params['Module'] = "EXPORT PAJAK MASUKAN";
		$params['TrxID'] = date_format(date_create($LogDate),'YmdHis');
		$params['Description'] = $_SESSION["logged_in"]["username"]." ".$description;
		$params['Remarks']=$remarks;
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);
	}

}