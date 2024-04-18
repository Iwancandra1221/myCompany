<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require 'vendor/autoload.php';
require 'vendor/setasign/fpdi/src/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RekapMsSalesman extends MY_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('HelperModel');
		$this->load->helper('FormLibrary');
		$this->load->library('email');
		$this->load->library('excel');
		$this->load->helper('directory');
		$this->load->helper('file');
		$this->load->model('ConfigSysModel');
		$this->load->model('GzipDecodeModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	private function _postRequest($url,$data){

		$options = array(
		    'http' => array(
		   	 	'method' => 'POST',
		    	'content' => http_build_query($data),
		    	'header'  => 'Content-type: application/x-www-form-urlencoded',
			),
		    
		);
		$stream = stream_context_create($options);
		$getContent = file_get_contents($url, false, $stream);
		$result = $this->GzipDecodeModel->_decodeGzip_true($getContent);
		return $result;
	}


	public function index(){
		$submit = $this->input->post('submit');
		if($submit==''){
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN REKAP MS SALESMAN";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU REKAP MS SALESMAN ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$data = array();

			$data['title'] = 'REKAP MASTER SALESMAN | '.WEBTITLE;
			$data['err'] = '';
			$data['formDest'] = '';

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('RekapMsSalesmanView',$data);
		}
		else{
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN REKAP MS SALESMAN";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES REKAP MS SALESMAN ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$kategori = $this->input->post('kategori');
			$status = $this->input->post('status');

			$url = $this->API_URL."/MsSalesman/RekapMsSalesman";
			// die($url);
			$payload = array(
				'api' => 'APITES',
				'kategori' => $kategori,
				'status' => $status,
			);

			$data['rekap'] = $this->_postRequest($url,$payload);
			
			switch($submit){
				case 'EXCEL':
					// ActivityLog Update SUCCESS
					$params['Remarks']="SUCCESS";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

					$this->load->view('template_xls/RekapMsSalesmanXls',$data);
					
					break;
				case 'PDF':
					require_once __DIR__ . '\vendor\autoload.php';
					$mpdf = new \Mpdf\Mpdf(array(
						'mode' => '',
						'format' => 'A4',
						'default_font_size' => 8,
						'default_font' => 'tahoma',
						'margin_left' => 10,
						'margin_right' => 10,
						'margin_top' => 30,
						'margin_bottom' => 10,
						'margin_header' => 10,
						'margin_footer' => 0,
						'orientation' => 'P'
					));
					
					$content = $this->load->view('template_pdf/RekapMsSalesmanPdf',$data,true);
					$curDate = date("d-F-Y H:i:s");
					$header = <<<HTML
						<p style="margin:0 0;text-align:right;">{$curDate}</p>
						<h1 style="margin:10 0mm;margin-top:0px;width:100%;text-align:center;">DAFTAR SALESMAN</h1>
						<br>
						
HTML;
					
					$mpdf->SetHTMLHeader($header); //Yang diulang di setiap awal halaman  (Header)
					$mpdf->WriteHTML($content);
					$mpdf->Output();

					// ActivityLog Update SUCCESS
					$params['Remarks']="SUCCESS";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

				break;	
			}	
		}
	}
}