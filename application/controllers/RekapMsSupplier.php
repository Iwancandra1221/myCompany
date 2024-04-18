<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require 'vendor/autoload.php';
require 'vendor/setasign/fpdi/src/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RekapMsSupplier extends MY_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('HelperModel');
		$this->load->model('GzipDecodeModel');
		$this->load->helper('FormLibrary');
		$this->load->library('email');
		$this->load->library('excel');
		$this->load->helper('directory');
		$this->load->helper('file');
		$this->load->model('ConfigSysModel');
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
		// $result = json_decode($getContent,true);
		// return $result;

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
			$params['Module'] = "LAPORAN REKAP MS SUPPLIER";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU REKAP MS SUPPLIER ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$data = array();

			// die(json_encode($GetData["data"]));
			$data['title'] = 'REKAP MASTER SUPPLIER | '.WEBTITLE;
			$data['err'] = '';
			$data['formDest'] = '';

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('RekapMsSupplierView',$data);
		}
		else{
			ini_set('memory_limit', '128M');

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN REKAP MS SUPPLIER";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES REKAP MS SUPPLIER ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$kategori = $this->input->post('kategori');
			$status = $this->input->post('status');
			$this->API_URL = 'http://localhost:90/webAPI';
			$URL = $this->API_URL."/MsSupplier/RekapMsSupplier";

			$data = array(
				'api' => 'APITES',
				'kategori' => $kategori,
				'status' => $status,
			);
			
			$curl = curl_init($URL);
			curl_setopt_array($curl, array(
				CURLOPT_URL => $URL,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '', // Enable automatic decoding
				CURLOPT_POSTFIELDS => $data,
			));
			
			$response = curl_exec($curl);
			$CURLINFO_SIZE_DOWNLOAD = curl_getinfo($curl, CURLINFO_SIZE_DOWNLOAD);
			// echo $CURLINFO_SIZE_DOWNLOAD;die;

			// $data['rekap'] = json_decode($response,true);
			$data['rekap'] = $this->GzipDecodeModel->_decodeGzip_true($response);
			switch($submit){
				case 'EXCEL':
					// ActivityLog Update SUCCESS
					$params['Remarks']="SUCCESS";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

					$this->load->view('template_xls/RekapMsSupplierXls',$data);
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
						'orientation' => 'L'
					));

					$content = $this->load->view('template_pdf/RekapMsSupplierPdf',$data,true);
					$curDate = date("d-F-Y H:i:s");
					$header = '
						<p style="margin:0 0;text-align:right;">'.$curDate.'</p>
						<h1 style="text-align:center">DAFTAR SUPPLIER</h1>';
				
					$mpdf->SetHTMLHeader($header,'','1'); //Yang diulang di setiap awal halaman  (Header)
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