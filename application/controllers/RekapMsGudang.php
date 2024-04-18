<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require 'vendor/autoload.php';
require 'vendor/setasign/fpdi/src/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RekapMsGudang extends MY_Controller {
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
			$params['Module'] = "LAPORAN REKAP MS GUDANG";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU REKAP MS GUDANG ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$location = array();
			$jenis = array();

			// $getLocation = json_decode(file_get_contents($this->API_URL.'/MsGudang/GetListLocationMsGudang?api=APITES'),TRUE);
			$getLocation = file_get_contents($this->API_URL.'/MsGudang/GetListLocationMsGudang?api=APITES');
			$getLocation = $this->GzipDecodeModel->_decodeGzip_true($getLocation);

			if($getLocation!=null && $getLocation['result']){
				$location = $getLocation['data'];
			}

			// $getJenis = json_decode(file_get_contents($this->API_URL.'/MsGudang/GetListJenisMsGudang?api=APITES'),TRUE);
			$getJenis = file_get_contents($this->API_URL.'/MsGudang/GetListJenisMsGudang?api=APITES');
			$getJenis = $this->GzipDecodeModel->_decodeGzip_true($getJenis);

			if($getJenis!=null && $getJenis['result']){
				$jenis = $getJenis['data'];
			}

			// die(json_encode($GetData["data"]));
			$data = array(
				'title' => 'REKAP MASTER GUDANG | '.WEBTITLE,
				'err' => '',
				'formDest' => '',
				'location' => $location,
				'jenis' => $jenis,
			);

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('RekapMsGudangView',$data);
		}
		else{
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN REKAP MS GUDANG";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES REKAP MS GUDANG ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$location = $this->input->post('location');
			$jenis = $this->input->post('jenis');
			$status = $this->input->post('status');

			$url = $this->API_URL."/MsGudang/RekapMsGudang";

			$payload = array(
				'api' => 'APITES',
				'location' => $location,
				'jenis' => $jenis,
				'status' => $status,
			);

			$data['rekap'] = array();
			$rekap = array();
			$rekapTmp = $this->_postRequest($url,$payload);

			switch($submit){
				case 'EXCEL':
					$body = array(
						'rekapTmp' => $rekapTmp,
					);

					// ActivityLog Update SUCCESS
					$params['Remarks']="SUCCESS";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

					$this->load->view('template_xls/RekapMsGudangXls',$body);
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

					
					
					if($rekapTmp!=null){
						foreach($rekapTmp as $value){
							$rekap[$value['Jenis']][] = $value;
						}
						$iterasi = 0;
						$length = count($rekap);
						//echo $length;
						foreach($rekap as $key => $value){
						//	if($iterasi<=1){
								$data['rekap'] = $value;
								// echo '<pre>';
								// print_r($data['rekap']);
								// echo '</pre>';
								$content = $this->load->view('template_pdf/RekapMsGudangPdf',$data,true);
								$curDate = date("d-F-Y H:i:s");
								$header = <<<HTML
									<p style="margin:0 0;text-align:right;">{$curDate}</p>
									<h1 style="margin:10 0mm;margin-top:0px;width:100%;text-align:center;">DAFTAR MASTER GUDANG</h1>
									<p>JENIS : <b>{$key}</b>
HTML;
								$mpdf->SetHTMLHeader($header,'','1'); //Yang diulang di setiap awal halaman  (Header)
								$mpdf->WriteHTML($content);
								if($iterasi < $length-1){
									$mpdf->AddPage();
								}
								
						//	}
							
							$iterasi+=1;
						}
						$mpdf->Output();

						// ActivityLog Update SUCCESS
						$params['Remarks']="SUCCESS";
						$params['RemarksDate'] = date("Y-m-d H:i:s");
						$this->ActivityLogModel->update_activity($params);

					}
				break;
			}
			
			
		}
		
	}
}
