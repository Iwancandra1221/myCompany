<?php 
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

	class Rekapmscustomer extends MY_Controller {
		public function __construct(){
			parent::__construct();
			$this->load->model('Rekapmscustomermodel');
			$this->load->model("MasterDbModel");
			$this->load->model('GzipDecodeModel');
		}
		
		public function index(){
			$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2)); 
			if($_SESSION["can_read"]==true){
				$this->RenderView("RekapmscustomerView");			
			}
		}

		public function list(){

			$data['api']	= 'APITES';
			$data['sSearch']	= $this->input->get('sSearch');
			$data['sSortDir_0']	= $this->input->get('sSortDir_0');
			$data['iSortingCols']	= $this->input->get('iSortingCols');
			$data['iDisplayStart']	= $this->input->get('iDisplayStart');
			$data['iDisplayLength']	= $this->input->get('iDisplayLength');
			$data['url']	= $this->MasterDbModel->get($_SESSION['conn']->DatabaseId)->AlamatWebService;
			$data['svr']	= $_SESSION['conn']->Server;
			$data['db']		= $_SESSION['conn']->Database;

			// $hasildata = json_decode($this->Rekapmscustomermodel->datalistcustomer($data),true);
			$hasildata = $this->Rekapmscustomermodel->datalistcustomer($data);
			$hasildata = $this->GzipDecodeModel->_decodeGzip_true($hasildata);

			$data_list=array();
			$data_hasil=array();
			$total=0;

			if(!empty($hasildata['result']) && $hasildata['result']=='success'){

				foreach ($hasildata['data']['list'] as $key => $r) {

					$list=array();

					$list[] 	= $r['Kd_Cust'];
					$list[] 	= $r['Nama_Cust'];
					$list[] 	= $r['Telp_Cust'];
					$list[] 	= $r['Almt_Cust'];
					$list[] 	= $r['Email'];

					$data_list[]=$list;
				}

				$total=$hasildata['data']['total'];

			}

				if(!empty($this->input->get('sEcho'))){
					$secho = $this->input->get('sEcho');
				}else{
					$secho = 1;
				}

				$data_hasil['sEcho']=$secho;
				$data_hasil['iTotalRecords']=count($data_list);
				$data_hasil['iTotalDisplayRecords']=$total;
				$data_hasil['aaData']=$data_list;

			print_r(json_encode($data_hasil));
		}

		public function ExportExcel()
		{

			$testing = false;
			$api = 'APITES';
			set_time_limit(60);
			$content_html = "";
			
			$data['api']	= 'APITES';
			$data['url']	= $this->MasterDbModel->get($_SESSION['conn']->DatabaseId)->AlamatWebService;
			$data['svr']	= $_SESSION['conn']->Server;
			$data['db']		= $_SESSION['conn']->Database;
			
			if(!empty($this->input->get('search'))){
				$data['search']	= $this->input->get('search');
			}else{
				$data['search']	= '';
			}

			$proses='/all';
			// $hasildata = json_decode($this->Rekapmscustomermodel->datalistcustomer($data,$proses),true);

			$hasildata = $this->Rekapmscustomermodel->datalistcustomer($data,$proses);
			$hasildata = $this->GzipDecodeModel->_decodeGzip_true($hasildata);
			
			if(!empty($hasildata['result']) && $hasildata['result']=='success'){
				$spreadsheet = new Spreadsheet();
				$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
				$sheet = $spreadsheet->getActiveSheet(0);

				$sheet->getStyle('A1:I1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
				$sheet->getStyle('A1:I1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

				$sheet->setTitle('MasterCustomer');
				$sheet->setCellValue("A1", "MASTER CUSTOMER");
				$sheet->getStyle('A1:I1')->getFont()->setSize(20);
				$sheet->mergeCells("A1:I1");

				$col1 = "";
				$col2 = "";

				$currrow = 3;	
				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "KODE CUSTOMER");

				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "NAMA CUSTOMER");

				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "TELP");

				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "ALAMAT");

				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "NPWP");

				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "NIK");

				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "PASSPORT");

				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "EMAIL");

				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, "TANGGAL INPUT");

				$sheet->getStyle('A3:I3')->getFont()->setBold(true);

				$sheet->getStyle('A3:I3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyle('A3:I3')->getFill()->getStartColor()->setARGB('EAEAEA'); 

				foreach ($hasildata['data'] as $key => $h) {

					$currrow += 1;
					$Brs = $currrow;
					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $h["Kd_Cust"]);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $h["Nama_Cust"]);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $h["Telp_Cust"]);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $h["Almt_Cust"]);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $h["NPWP"]);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $h["NIK"]);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $h["Passport"]);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $h["Email"]);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, date_format(date_create($h["Entry_Time"]),'d-M-Y'));

				}


				for ($i = 'A'; $i !=   $sheet->getHighestColumn(); $i++) {
				    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
				}

				$filename='MasterCustomer['.date('YmdHis').']';
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
		        $writer->save('php://output');
		        exit();
		    }else{
		    	echo $hasildata['message'];
		    }

		}

	}
?>