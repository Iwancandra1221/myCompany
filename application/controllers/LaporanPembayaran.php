<?php 
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class LaporanPembayaran extends MY_Controller {
		public function __construct()
		{
			parent::__construct();
			$this->load->model('LaporanPembayaranmodel');
			$this->load->model('ActivityLogModel');
			$this->load->model('ConfigSysModel');
			$this->API_BKT = $this->ConfigSysModel->Get()->webapi_url;
			$this->APIKEY = 'APITES';
		}
		
		public function index(){

			 $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
			 $data['title'] = 'REKAP OTORISASI BAYAR'; 
			 $this->RenderView('LaporanPembayaranView',$data);

		}

		public function ListTanggalPembayaran(){
			$url = $this->API_BKT.'/LaporanPembayaran/ListTanggalPembayaran';

			if(!empty($this->input->post('supplier'))){
				$datapost = $this->input->post('supplier');
			}else{
				$datapost = 'ALL';
			}

			$post = array(
		        'api' => $this->APIKEY,
			    'supplier' => $datapost
			);

			$result = $this->LaporanPembayaranmodel->getlist($url,$post);
			print_r($result);

		}

		public function ListSupplierPembayaran(){
			$url = $this->API_BKT.'/LaporanPembayaran/ListSupplierPembayaran';

			if(!empty($this->input->post('tanggal'))){
				$datapost = $this->input->post('tanggal');
			}else{
				$datapost = 'ALL';
			}

			$post = array(
		        'api' => $this->APIKEY,
			    'tanggal' => $datapost
			);

			$result = $this->LaporanPembayaranmodel->getlist($url,$post);
			print_r($result);

		}

		public function export($tanggal='QUxM',$supplier='QUxM'){

			$urlnorek = $this->API_BKT.'/LaporanPembayaran/NoRek';
			$arr = array('api' => $this->APIKEY);
			$no_rek = $this->LaporanPembayaranmodel->getlist($urlnorek,$arr);
			$no_rek = json_decode($no_rek,true);

			$url = $this->API_BKT.'/LaporanPembayaran/ListPembayaran';

			$post = array(
		        'api' => $this->APIKEY,
			    'tanggal' => str_replace('=', '', $tanggal),
			    'supplier' => str_replace('=', '', $supplier)
			);

			$result = $this->LaporanPembayaranmodel->getlist($url,$post);
			$data = json_decode($result,true);

			if($data['result']==='sukses'){

				$page_title = 'LAPORAN PEMBAYARAN';

				$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet(0);

				$currcol = 1;
				$currrow = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Trx ID');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Transfer Type');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Debited Account');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Credited Account');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Amount');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Currency');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Charges Type');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Charges Account');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Remark 1');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Remark 2');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Rcv Bank Code');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Rcv Bank Name');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Rcv Name');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Cust Type');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Cust Residence');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Trx Code');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Email');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Supplier Name');
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Region');
				$currrow++;

				$no=1;
				$tamp_nominal=0;
				$tamp_CA='';
				$tamp_Jum=0;

				for($i=0; $i<count($data['data']); $i++){
					
					$bank = strtoupper($data['data'][$i]['partner_bank']);
					if($data['data'][$i]['Total']>1000000000){
						if (stripos($bank, 'BCA') !== false) {
							$nama_bank = 'BCA';
						}else{
							$nama_bank = 'RTS';
						}
					}else{
						if (stripos($bank, 'BCA') !== false) {
							$nama_bank = 'BCA';
						}else{
							$nama_bank = 'LLG';
						}
					}

					$tampno = '';
					$jum = strlen($no);

					for($x=4; $x>$jum; $x--){
						$tampno .= '0';
					}

					$tampno=$tampno.$no;

					if($tamp_CA!=$data['data'][$i]['partner_bank_account'] && $tamp_CA!='' && $tamp_Jum>0){

						$currcol = 4;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');

						$style = $sheet->getStyleByColumnAndRow($currcol, $currrow);
						$font = $style->getFont();
						$font->setBold(true);

						$alignment = $style->getAlignment();
						$alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);


						$currcol = 5;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tamp_nominal);
						$style = $sheet->getStyleByColumnAndRow($currcol, $currrow);
						$style->getNumberFormat()->setFormatCode('#,##0.00');
						$font = $style->getFont();
						$font->setBold(true);

						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':S'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$currrow++;

						$tamp_nominal = 0;
						$tamp_Jum = 0;

					}

					if ($tamp_CA != $data['data'][$i]['partner_bank_account']) {
					    $tamp_nominal = 0;
					    $tamp_Jum = 0;

					    $spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':S'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
					}

					if($tamp_CA==$data['data'][$i]['partner_bank_account']){
						$tamp_Jum++;
					}

					$tamp_CA = $data['data'][$i]['partner_bank_account'];

					if($data['data'][$i]['partner_bank_account']!=''){
						$tamp_nominal = $tamp_nominal+$data['data'][$i]['Total'];
					}

					$currcol=1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, date_format(date_create($data['data'][$i]['Tgl_Pembayaran']),'Ymd').$tampno);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $nama_bank);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow,  $no_rek['data']);
					$style = $sheet->getStyleByColumnAndRow($currcol, $currrow);
					$style->getNumberFormat()->setFormatCode('0');

					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, (string)$data['data'][$i]['partner_bank_account']);

					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $data['data'][$i]['Total']);
					$style = $sheet->getStyleByColumnAndRow($currcol, $currrow);
					$style->getNumberFormat()->setFormatCode('#,##0.00');
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($data['data'][$i]['MataUang']));
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'OUR');
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($no_rek['data']));
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($data['data'][$i]['swift_code']));
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($data['data'][$i]['Nm_Bank_Supplier']));
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($data['data'][$i]['partner_bank_account_owner']));
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, '1');
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, '1');
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, '88');
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($data['data'][$i]['Email']));
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($data['data'][$i]['Nm_Supl']));
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($data['data'][$i]['Wilayah']));
					$currrow++;

					$no++;
				}

				if($tamp_Jum>0 && $tamp_CA!=''){

						$currcol = 4;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');

						$style = $sheet->getStyleByColumnAndRow($currcol, $currrow);
						$font = $style->getFont();
						$font->setBold(true);

						$alignment = $style->getAlignment();
						$alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);


						$currcol = 5;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tamp_nominal);
						$style = $sheet->getStyleByColumnAndRow($currcol, $currrow);
						$style->getNumberFormat()->setFormatCode('#,##0.00');
						$font = $style->getFont();
						$font->setBold(true);

						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':S'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

						$currrow++;

						$tamp_nominal = 0;
						$tamp_Jum = 0;

					}

				for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
					$sheet->getColumnDimension($i)->setAutoSize(TRUE);
				}

				$filename='REKAP OTORISASI BAYAR ['.date('Y-m-d H-i-s').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');
				exit();

			}else{

			}
		}

		function numberic($a){
			$cleanedString = preg_replace('/[^0-9]/', '', $a);
			return intval($cleanedString);
		}
	}
?>