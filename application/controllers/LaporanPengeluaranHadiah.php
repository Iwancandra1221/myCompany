<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class LaporanPengeluaranHadiah extends MY_Controller 
	{
		public $excel_flag = 0;
		public $maxtimeout = 900;
		
		public function __construct()
		{
			parent::__construct();
			$this->load->model('HelperModel');
			$this->load->helper('FormLibrary');
			$this->load->library('excel');
			ini_set("max_execution_time", 1500);
			ini_set('memory_limit', '1G');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
			$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
		}
		
		public function index()
		{
			$data = array();
			$data['title'] = 'Laporan Pengeluaran Hadiah | '.WEBTITLE;
		
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module']="LAPORAN PENGELUARAN HADIAH"; 
	   	$params['TrxID'] = date("YmdHis");
			$params['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN PENGELUARAN HADIAH";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params); 

			$this->RenderView('LaporanPengeluaranHadiahView',$data);
		}
		
		public function Proses()
		{
			// echo json_encode($_POST);die;
			
			if(isset($_POST["btnExcel"])){

				$params = array();   
				$params['LogDate'] = date("Y-m-d H:i:s");
				$params['UserID'] = $_SESSION["logged_in"]["userid"];
				$params['UserName'] = $_SESSION["logged_in"]["username"];
				$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
				$params['Module']="LAPORAN PENGELUARAN HADIAH"; 
		   	$params['TrxID'] = date("YmdHis");
				$params['Description']=$_SESSION["logged_in"]["username"]." PROSES EXPORT EXCEL ";
		   	$params['Remarks']="";
		   	$params['RemarksDate'] = 'NULL';
		   	$this->ActivityLogModel->insert_activity($params); 
			
				$this->load->library('form_validation');
				$this->form_validation->set_rules('dp1','Tanggal Awal','required');
				$this->form_validation->set_rules('dp2','Tanggal Akhir','required');
				
				if($this->form_validation->run())
				{
					$api = 'APITES';
					$data["dp1"] =  date_format(date_create_from_format("d/m/Y", $_POST["dp1"]),'Y-m-d');
					$data["dp2"] =  date_format(date_create_from_format("d/m/Y", $_POST["dp2"]),'Y-m-d');
					$data['report'] = $_POST["report"];
					$url = $this->API_URL. "/LaporanPengeluaranHadiah/Proses?api=".$api."&p_tgl1=".urlencode($data["dp1"])."&p_tgl2=".urlencode($data['dp2'])."&p_report=".urlencode($data["report"]);
					// die($url);

					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_URL => $url,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_TIMEOUT => $this->maxtimeout,
					));
					$result =  curl_exec($curl);
					curl_close($curl);

					$result = json_decode($result,TRUE);
					
					if(count($result)==0){
						$params['Remarks']="FAILED - Tidak Ada Data";
						$params['RemarksDate'] = date("Y-m-d H:i:s");
						$this->ActivityLogModel->update_activity($params);
						exit('Tidak ada data');
					}

					if($_POST["report"]=='P1'){
						$this->SummaryReport($data, $result, 'Laporan Pengeluaran Hadiah Summary');
					} else {
						$this->DetailReport($data, $result, 'Laporan Pengeluaran Hadiah Detail');
					}
				}
				else
				{
					redirect("LaporanPengeluaranHadiah");
				}
			}

		}

		public function SummaryReport($data, $result, $judul)
		{
			//die(json_encode($result));
			$spreadsheet = new Spreadsheet();
	    $sheet = $spreadsheet->getActiveSheet(0);

			$sheet->setTitle('LaporanPengeluaranHadiahSummary');
	    $sheet->setCellValue('A1', $judul);
	    $sheet->getStyle('A1')->getFont()->setSize(20);
	    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
	    $sheet->mergeCells('A1:F1');

	    $sheet->setCellValue('A2', 'Periode '.date("d-M-Y", strtotime($data['dp1'])).' s/d '.date("d-M-Y", strtotime($data['dp2'])));
	    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
	    $sheet->mergeCells('A2:F2');

			$sheet->setCellValue('A3', 'Tgl Proses '.date("d-m-Y h:i:sa"));
	    $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
	    $sheet->mergeCells('A3:F3');

	    $currcol = 1;
	    $currrow = 5;
		
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Partner Type');
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Wilayah');
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tanggal');
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Barang');
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Qty');
			$currcol++;

			$sheet->getStyle('A' . $currrow . ':F' . $currrow)->getFont()->setBold(true);

			$range = 'A' . $currrow . ':' . $sheet->getHighestColumn() . $currrow;

			$styleArray = [
				'fill' => [
					'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
					'color' => ['rgb' => 'eaeaea'],
				],
			];
			$sheet->getStyle($range)->applyFromArray($styleArray);
			$currrow++;

	    foreach ($result as $key => $val) {
	    	$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($val['Partner_Type']));
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($val['Wilayah']));
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($val['Divisi']));
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($val['Tgl_Faktur']));
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($val['Kd_Brg']));
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($val['Qty']));
				$currrow++;
	    }

			foreach (range('A', $sheet->getHighestDataColumn()) as $column) {
				$sheet->getColumnDimension($column)->setAutoSize(true);
			}

	    $filename = $judul . '[' . date('Ymd') . ']';
	    $writer = new Xlsx($spreadsheet);

	    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
	    header('Cache-Control: max-age=0');
	    ob_end_clean();

	    $writer->save('php://output');
	    exit();
		}

		public function DetailReport($data, $result, $judul)
		{
			//die(json_encode($result));
			$spreadsheet = new Spreadsheet();
	    $sheet = $spreadsheet->getActiveSheet(0);

			$sheet->setTitle('LaporanPengeluaranHadiahDetail');
	    $sheet->setCellValue('A1', $judul);
	    $sheet->getStyle('A1')->getFont()->setSize(20);
	    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
	    $sheet->mergeCells('A1:H1');

	    $sheet->setCellValue('A2', 'Periode '.date("d-M-Y", strtotime($data['dp1'])).' s/d '.date("d-M-Y", strtotime($data['dp2'])));
	    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
	    $sheet->mergeCells('A2:H2');

			$sheet->setCellValue('A3', 'Tgl Proses '.date("d-m-Y h:i:sa"));
	    $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
	    $sheet->mergeCells('A3:H3');

	    $currcol = 1;
	    $currrow = 5;
			$tgl_faktur = '';
			$kd_brg = '';
	    $total = 0;
		
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Partner Type');
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Wilayah');
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tanggal');
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Barang');
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Qty');
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Dealer');
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Faktur');
			$currcol++;

			$sheet->getStyle('A' . $currrow . ':H' . $currrow)->getFont()->setBold(true);
			$range = 'A' . $currrow . ':' . $sheet->getHighestColumn() . $currrow;

			$styleArray = [
				'fill' => [
					'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
					'color' => ['rgb' => 'eaeaea'],
				],
			];
			$sheet->getStyle($range)->applyFromArray($styleArray);
			$currrow++;

	    foreach ($result as $key => $val) {
				if($tgl_faktur !=rtrim($val['Tgl_Faktur'])){
					if (!empty($tgl_faktur)) {
				    $currcol = 5;

				    $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$kd_brg);
						$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
						$style = $cell->getStyle();
						$alignment = $style->getAlignment();
						$alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

				    $styleArray = [
							'font' => [
								'bold' => true,
							],
				    ];

				    $cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
				    $cell->getStyle()->applyFromArray($styleArray);

				    $currcol++;

				    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $total);

				    //$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
				    //$cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');

				    $cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
						$cell->getStyle()->applyFromArray($styleArray);

				    $currrow++;
				    //$total = 0;

						for ($column = 'A'; $column <= 'H'; $column++) {
							$cellCoordinate = $column . $currrow;

							$sheet->setCellValue($cellCoordinate, '');

							$cell = $sheet->getCell($cellCoordinate);
							$style = $cell->getStyle();
							$borders = $style->getBorders();
							$borders->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						}

						$currrow++;

						$currcol = 5;

						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$tgl_faktur);
						$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
						$style = $cell->getStyle();
						$alignment = $style->getAlignment();
						$alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

						$styleArray = [
							'font' => [
								'bold' => true,
							],
						];

						$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
						$cell->getStyle()->applyFromArray($styleArray);

						$currcol++;

						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total);

						//$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
						//$cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');

						$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
						$cell->getStyle()->applyFromArray($styleArray);
						
						$currrow++;
						$currrow++;
				    $total = 0;
					}
				}
				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($val['Partner_Type']));
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($val['Wilayah']));
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($val['Divisi']));
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($val['Tgl_Faktur']));
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($val['Kd_Brg']));
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($val['Qty']));
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($val['Dealer']));
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($val['No_Faktur']));

				$kd_brg = rtrim($val['Kd_Brg']);
				$tgl_faktur = rtrim($val['Tgl_Faktur']);
				$total=$total+$val['Qty'];
				$currrow++;
	    }
			
			$currcol = 5;

			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$kd_brg);

			$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
			$style = $cell->getStyle();
			$alignment = $style->getAlignment();
			$alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

			$styleArray = [
				'font' => [
					'bold' => true,
				],
			];

			$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
			$cell->getStyle()->applyFromArray($styleArray);

			$currcol++;

			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total);

			//$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
			//$cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');

			$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
			$cell->getStyle()->applyFromArray($styleArray);

			$currrow++;

			for ($column = 'A'; $column <= 'H'; $column++) {
				$cellCoordinate = $column . $currrow;

				$sheet->setCellValue($cellCoordinate, '');

				$cell = $sheet->getCell($cellCoordinate);
				$style = $cell->getStyle();
				$borders = $style->getBorders();
				$borders->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
			}

			$currrow++;

			$currcol = 5;

			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$tgl_faktur);
			$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
			$style = $cell->getStyle();
			$alignment = $style->getAlignment();
			$alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

			$styleArray = [
				'font' => [
					'bold' => true,
				],
			];

			$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
			$cell->getStyle()->applyFromArray($styleArray);

			$currcol++;

			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total);

			//$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
			//$cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');

			$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
			$cell->getStyle()->applyFromArray($styleArray);
			
			$currrow++;

			foreach (range('A', $sheet->getHighestDataColumn()) as $column) {
				$sheet->getColumnDimension($column)->setAutoSize(true);
			}

	    $filename = $judul . '[' . date('Ymd') . ']';
	    $writer = new Xlsx($spreadsheet);

	    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
	    header('Cache-Control: max-age=0');
	    ob_end_clean();

	    $writer->save('php://output');
	    exit();
		}
		 
	}		
?>