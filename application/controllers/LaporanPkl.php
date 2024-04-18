<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanPkl extends MY_Controller{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('laporanpklmodel');
	}
	private function _postRequest($url,$data,$jsonDecode=true){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
		curl_setopt($ch, CURLOPT_ENCODING, '');
		//curl_setopt($ch, CURLOPT_POSTFIELDS,"postvar1=value1&postvar2=value2&postvar3=value3");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec($ch);

		$result = json_decode($server_output,$jsonDecode);
		if($result==null){
			// $GLOBALS['bugsnag']->leaveBreadcrumb(
			//     $server_output,
			//     \Bugsnag\Breadcrumbs\Breadcrumb::ERROR_TYPE,
			//     [
			//     	'url' => $url,
			//     	'payload' => $data,
			// 	]
			// );
			// $GLOBALS['bugsnag']->notifyError('ErrorType', 'result kosong - CEK TAB BREADCUMS');
			
		}

		return $result;
	}
	public function index(){
		$url = API_URL.'/LaporanPkl/getDealer?api=APITES';
		$getDealer = $this->_postRequest($url,array());
		$dealer = array();
		if($getDealer!=null && $getDealer['code']==1){
			$dealer = $getDealer['data'];
		}
		$data = array(
			'title' => 'Laporan PKL | '.WEBTITLE,
			'dealer' => $dealer,
		);

		$this->RenderView('LaporanPklView',$data);
	}
	public function LaporanPkl_Proses(){
		$tgl1 = $this->input->post('dp1');
		$tgl2 = $this->input->post('dp2');
		$sortByNoSj = $this->input->post('sort_by_no_sj');
		$kdPlg = $this->input->post('kd_plg');
		$submit = $this->input->post('submit');
		
		$nmDealer = '';
		if($kdPlg!=''){
			//ambil nama plg
			$url = API_URL.'/LaporanPkl/getDealer?api=APITES';
			$getDealer = $this->_postRequest($url,array());
			if($getDealer!=null && $getDealer['code']==1){
				foreach($getDealer['data'] as $key => $value){
					if($kdPlg==$value['Kd_Plg']){
						$nmDealer = $value['Nm_Plg'];
						break;
					}
				}
			}
		}
		

		if($tgl1!=''){
			$date = DateTime::createFromFormat('d-m-Y', $tgl1);
			$newDateString  = $date->format('Y-m-d');
			if($tgl1!=$date->format('d-m-Y')){
				$tgl1 = '';
			}
			else{
				$tgl1 = $newDateString;
			}
		}
		if($tgl2!=''){
			$date = DateTime::createFromFormat('d-m-Y', $tgl2);
			$newDateString  = $date->format('Y-m-d');
			if($tgl2!=$date->format('d-m-Y')){
				$tgl2 = '';
			}
			else{
				$tgl2 = $newDateString;
			}
		}
		if($submit!=''){

			$url = API_URL."/LaporanPkl/LaporanPklProses/";
			$payload = array(
				'api' => 'APITES',
				'tgl1' => $tgl1,
				'tgl2' => $tgl2,
				'sort_by_no_sj' => $sortByNoSj,
				'kd_plg' => $kdPlg,
			);
			$getReport = $this->_postRequest($url,$payload);

			switch($submit){
				case 'PREVIEW':
					$mpdf = new \Mpdf\Mpdf(array(
						'mode' => '',
						'format' => 'Legal',
						'default_font_size' => 8,
						'default_font' => 'arial',
						'margin_left' => 10,
						'margin_right' => 10,
						'margin_top' => 10,
						'margin_bottom' => 10,
						'margin_header' => 10,
						'margin_footer' => 5,
						'orientation' => 'P'
					));
					$body = array(
						'laporan' => $getReport,
						'tgl1' => $tgl1,
						'tgl2' => $tgl2,
						'nmDealer' => $nmDealer,
					);
					$this->load->view('template_pdf/LaporanPklPdf',$body);

					// $content_html = $this->load->view('template_pdf/LaporanPklPdf',$body,true);
					// $mpdf->WriteHTML(utf8_encode($content_html));
					// $mpdf->Output();	
				break;
				case 'EXCEL':
					$body = array(
						'title' => 'Laporan_PKL_',
						'laporan' => $getReport,
						'tgl1' => $tgl1,
						'tgl2' => $tgl2,
						'nmDealer' => $nmDealer,
					);
					$this->load->view('template_xls/LaporanPklXls',$body);
				break;
			}
		}
	}
}