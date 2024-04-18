<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportBudgetMP extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model("ReportBudgetMPModel");
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function index(){	
		$data['title'] = 'REKAP BUDGET PO MP';
		$this->RenderView('ReportBudgetMPView',$data);
	}

	public function List(){
		$url = $this->API_URL.'/ReportBudgetMP/ListBudgetMP';
		$data = $_GET;
		$result = $this->ReportBudgetMPModel->ListBudgetMP($url,$data);

		$judul = 'REKAP BUDGET MP';

		if(!empty($_GET) && !empty($result)){
			if($_GET['report']=='po'){
				$this->po($data,$result,$judul);
			}else if($_GET['report']=='merk'){
				$this->merk($data,$result,$judul);

			}else if($_GET['report']=='merksummary'){
				$this->merksummary($data,$result,$judul);
			}
		}else{

		}
	}

	public function po($data, $list, $judul){

	    $spreadsheet = new Spreadsheet();
	    $sheet = $spreadsheet->getActiveSheet(0);

	    $sheet->setCellValue('A1', $judul);
	    $sheet->getStyle('A1')->getFont()->setSize(20);
	    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
	    $sheet->mergeCells('A1:E1');

	    $sheet->setCellValue('A2', 'Periode '.$data['dari'].' s/d '.$data['sampai']);
	    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
	    $sheet->mergeCells('A2:E2');

	    $currcol = 1;
	    $currrow = 3;
	    $no_po = '';
	    $total_budget = 0;
	    $sub_total = 0;

	    foreach ($list as $key => $l) {
	    	if($no_po!=rtrim(strtolower($l['No_PO']))){

				if (!empty($no_po)) {
				    $currcol = 4;

				    $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
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

				    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_budget);

				    $cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
				    $cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');

				    $cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
					$cell->getStyle()->applyFromArray($styleArray);

				    $currrow++;
				    $total_budget = 0;
				}



	    		$currrow++;
		    	$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['No_PO']);
				$sheet->mergeCells('A'.$currrow.':E'.$currrow);
				$sheet->getStyle('A' . $currrow . ':E' . $currrow)->getFont()->setBold(true);
				$no_po = rtrim(strtolower($l['No_PO']));
		        $currrow++;

				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tanggal PO');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Supplier');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama Supplier');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Budget');

				$range = 'A' . $currrow . ':' . $sheet->getHighestColumn() . $currrow;

				$styleArray = [
				    'fill' => [
				        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				        'color' => ['rgb' => 'eaeaea'],
				    ],
				];

				$sheet->getStyle($range)->applyFromArray($styleArray);
				$currrow++;
		    }

		    $currcol = 1;
		    $tgl_po = date_format(date_create($l['Tgl_PO']),'Y-m-d');

			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tgl_po);
			$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
			$cell->getStyle()->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['Kd_Supl']));
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['Nm_Supl']));
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['Merk']));
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['Total_Budget']));
			$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
			$cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');

			$total_budget=$total_budget+$l['Total_Budget'];
			$sub_total=$sub_total+$l['Total_Budget'];
			$currrow++;
	    }

		$currcol = 4;

		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');

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

		$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_budget);

		$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
		$cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');

		$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
		$cell->getStyle()->applyFromArray($styleArray);

		$currrow++;


		for ($column = 'A'; $column <= 'E'; $column++) {
		    $cellCoordinate = $column . $currrow;

		    $sheet->setCellValue($cellCoordinate, '');

		    $cell = $sheet->getCell($cellCoordinate);
		    $style = $cell->getStyle();
		    $borders = $style->getBorders();
		    $borders->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
		}

		$currrow++;

		$currcol = 4;

		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SUB TOTAL');

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

		$sheet->setCellValueByColumnAndRow($currcol, $currrow, $sub_total);

		$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
		$cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');

		$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
		$cell->getStyle()->applyFromArray($styleArray);

		$range = 'A' . $currrow . ':' . $sheet->getHighestColumn() . $currrow;

		foreach (range('A', $sheet->getHighestDataColumn()) as $column) {
		    $sheet->getColumnDimension($column)->setAutoSize(true);
		}

	    $filename = $judul . ' PO [' . date('Ymd') . ']';
	    $writer = new Xlsx($spreadsheet);

	    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
	    header('Cache-Control: max-age=0');
	    ob_end_clean();

	    $writer->save('php://output');
	    exit();

	}


	public function merk($data, $list, $judul){
	    $spreadsheet = new Spreadsheet();
	    $sheet = $spreadsheet->getActiveSheet(0);

	    $sheet->setCellValue('A1', $judul);
	    $sheet->getStyle('A1')->getFont()->setSize(20);
	    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
	    $sheet->mergeCells('A1:E1');

	    $sheet->setCellValue('A2', 'Periode '.$data['dari'].' s/d '.$data['sampai']);
	    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
	    $sheet->mergeCells('A2:E2');

	    $currcol = 1;
	    $currrow = 3;
	    $merk = '';
	    $total_budget = 0;
	    $sub_total = 0;

	    $total_merk=array();

	    foreach ($list as $key => $l) {
	    	if($merk!=rtrim(strtolower($l['Merk']))){

				if (!empty($merk)) {
				    $currcol = 4;

				    $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
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

				    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_budget);

				    $cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
				    $cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');

				    $cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
					$cell->getStyle()->applyFromArray($styleArray);

					$total_merk[$merk] = $total_budget;

				    $currrow++;
				    $total_budget = 0;
				}



	    		$currrow++;
		    	$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim(strtoupper($l['Merk'])));
				$sheet->mergeCells('A'.$currrow.':E'.$currrow);
				$sheet->getStyle('A' . $currrow . ':E' . $currrow)->getFont()->setBold(true);
				$merk = rtrim(strtolower($l['Merk']));
		        $currrow++;

				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nomor PO');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tanggal PO');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Supplier');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama Supplier');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Budget');

				$range = 'A' . $currrow . ':' . $sheet->getHighestColumn() . $currrow;

				$styleArray = [
				    'fill' => [
				        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				        'color' => ['rgb' => 'eaeaea'],
				    ],
				];

				$sheet->getStyle($range)->applyFromArray($styleArray);
				$currrow++;
		    }

		    $currcol = 1;
		    $tgl_po = date_format(date_create($l['Tgl_PO']),'Y-m-d');

		    $sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['No_PO']));
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tgl_po);
			$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
			$cell->getStyle()->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['Kd_Supl']));
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['Nm_Supl']));
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['Total_Budget']));
			$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
			$cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');

			$total_budget=$total_budget+$l['Total_Budget'];
			$sub_total=$sub_total+$l['Total_Budget'];
			$currrow++;
	    }

		$currcol = 4;

		$total_merk[$merk] = $total_budget;

		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
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

		$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_budget);

		$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
		$cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');

		$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
		$cell->getStyle()->applyFromArray($styleArray);

		$currrow++;


		for ($column = 'A'; $column <= 'E'; $column++) {
		    $cellCoordinate = $column . $currrow;

		    $sheet->setCellValue($cellCoordinate, '');

		    $cell = $sheet->getCell($cellCoordinate);
		    $style = $cell->getStyle();
		    $borders = $style->getBorders();
		    $borders->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
		}

		$currrow++;

		foreach ($total_merk as $key => $m) {

			$currcol = 4;

			$sheet->setCellValueByColumnAndRow($currcol, $currrow, strtoupper($key));

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

			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $m);

			$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
			$cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');

			$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
			$cell->getStyle()->applyFromArray($styleArray);

			$currrow++;
		}

		$currcol = 4;

		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SUB TOTAL');

		$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);

		$style = $cell->getStyle();
		$alignment = $style->getAlignment();
		$alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

		$styleArray = [
		    'font' => [
		        'bold' => true,
		    ],
		];

		$cell->getStyle()->applyFromArray($styleArray);

		$range = 'A' . $currrow . ':' . $sheet->getHighestColumn() . $currrow;
		$styleArray = [
		    'borders' => [
		        'top' => [
		            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
		        ],
		    ],
		    'font' => [
		        'bold' => true,
		    ],
		];
		$sheet->getStyle($range)->applyFromArray($styleArray);

		$currcol++;

		$sheet->setCellValueByColumnAndRow($currcol, $currrow, $sub_total);

		$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);

		$cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');

		$cell->getStyle()->applyFromArray($styleArray);


		foreach (range('A', $sheet->getHighestDataColumn()) as $column) {
		    $sheet->getColumnDimension($column)->setAutoSize(true);
		}

	    $filename = $judul . ' Merk [' . date('Ymd') . ']';
	    $writer = new Xlsx($spreadsheet);

	    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
	    header('Cache-Control: max-age=0');
	    ob_end_clean();

	    $writer->save('php://output');
	    exit();
	}

	public function merksummary($data,$list,$judul){
	    $spreadsheet = new Spreadsheet();
	    $sheet = $spreadsheet->getActiveSheet(0);

	    $sheet->setCellValue('A1', $judul);
	    $sheet->getStyle('A1')->getFont()->setSize(20);
	    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
	    $sheet->mergeCells('A1:E1');

	    $sheet->setCellValue('A2', 'Periode '.$data['dari'].' s/d '.$data['sampai']);
	    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
	    $sheet->mergeCells('A2:E2');

	    $currcol = 1;
	    $currrow = 5;
	    $sub_total = 0;

	    $total_merk=array();

		$currcol = 1;
		$sheet->setCellValue('A4', 'Merk');
		$sheet->mergeCells('A4:D4');
		$sheet->setCellValue('E4', 'Total Budget');

		$range = 'A4:' . $sheet->getHighestColumn() .'4';

		$styleArray = [
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'color' => ['rgb' => 'eaeaea'],
			],
		];

		$sheet->getStyle($range)->applyFromArray($styleArray);

	    foreach ($list as $key => $l) {

		    $currcol = 1;
		    $sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['Merk']));
		    $sheet->mergeCells('A'.$currrow.':D'.$currrow);
			$currcol=$currcol+4;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['TotalBudget']));
			$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);
			$cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');

			$sub_total=$sub_total+$l['TotalBudget'];
			$currrow++;
	    }


		$currcol = 4;

		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SUB TOTAL');

		$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);

		$style = $cell->getStyle();
		$alignment = $style->getAlignment();
		$alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

		$styleArray = [
		    'font' => [
		        'bold' => true,
		    ],
		];

		$cell->getStyle()->applyFromArray($styleArray);

		$range = 'A' . $currrow . ':' . $sheet->getHighestColumn() . $currrow;
		$styleArray = [
		    'borders' => [
		        'top' => [
		            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
		        ],
		    ],
		    'font' => [
		        'bold' => true,
		    ],
		];
		$sheet->getStyle($range)->applyFromArray($styleArray);

		$currcol++;

		$sheet->setCellValueByColumnAndRow($currcol, $currrow, $sub_total);

		$cell = $sheet->getCellByColumnAndRow($currcol, $currrow);

		$cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');

		$cell->getStyle()->applyFromArray($styleArray);


		foreach (range('A', $sheet->getHighestDataColumn()) as $column) {
		    $sheet->getColumnDimension($column)->setAutoSize(true);
		}

	    $filename = $judul . ' Summary Merk [' . date('Ymd') . ']';
	    $writer = new Xlsx($spreadsheet);

	    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
	    header('Cache-Control: max-age=0');
	    ob_end_clean();

	    $writer->save('php://output');
	    exit();
	}

	
}