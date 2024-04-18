<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

ini_set("memory_limit", "1G");

class Reportchecklistservice extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->model('HelperModel');
		$this->load->helper('FormLibrary');
		$this->load->library('excel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}
	private function _postRequest($url,$data){

		// $options = array(
		//     'http' => array(
		//    	 	'method' => 'POST',
		//     	'content' => http_build_query($data),
		//     	'header'  => 'Content-type: application/x-www-form-urlencoded',
		// 	),
		    
		// );
		// $stream = stream_context_create($options);
		// $getContent = file_get_contents($url, false, $stream);
		// $result = json_decode($getContent,true);
		// return $result;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
		//curl_setopt($ch, CURLOPT_POSTFIELDS,"postvar1=value1&postvar2=value2&postvar3=value3");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec($ch);
		$result = json_decode($server_output,true);
		return $result;
	}
	public function index(){
		$submit = $this->input->post('submit');
		$btnView = $this->input->get('submit');
		if($btnView!=''){
			$submit = $btnView;
		}
		

		if($submit==''){
			$body = array(
				'title' => 'Laporan Service | '.WEBTITLE,
				'formUrl' => base_url()."Reportchecklistservice",
			);

			$this->RenderView('ReportChecklistServiceView',$body);
		}
		else{
			switch($submit){
				case 'CHECK':
					$kdBrg = $this->input->post('kd_brg');
					$noSeri = $this->input->post('no_seri');

					$orderArray = $this->input->post('order');
					$data = array(
						'draw' => 0,
						'recordsTotal'=> 10,
						'recordsFiltered' => 10,
						'code' => 0,
						'msg' => '',
						'data' => array(),
					);

					$msg = '';

					$search = isset($_REQUEST['search']['value']) ? $_REQUEST['search']['value'] : '';
					$data['search'] = $search;

					$orderColumn=$orderArray;
					if(isset($orderColumn[0]['column'])!=''){
						$orderColumn = $orderColumn[0]['column'];
					}
					$order = "";
					$orderColumnName = "";
					$draw = 1;
					if($orderColumnName!=''){
						$order = $orderColumnName.' '.$orderArray[0]['dir'];
					}
					if(isset($_REQUEST['draw'])){
						$draw = $_REQUEST['draw'];
					}
					if(isset($_REQUEST['start']) && isset($_REQUEST['length'])){
						$start = $_REQUEST['start'];
						$length = $_REQUEST['length'];
						$limit = (object) ['limit'=>$length,'offset'=>$start];
					}

					$url = $this->API_URL.'/Reportchecklistservice/GetHeaderPelanggan?api=APITES';
					$payload = array(
						'kd_brg' => $kdBrg,
						'no_seri'=> $noSeri,
						'top' => $limit->limit,
						'offset' => $limit->offset,
						'search' => $search,
						'order' => $order,
					);
					$getReport = $this->_postRequest($url,$payload);

					if($getReport!='' && $getReport['code']=='success'){
						$data['code'] = 1;
						
						foreach($getReport['data']['report'] as $key => $value){
							$value['no'] = $value['RowNumber'];
							$baseUrl = base_url().'Reportchecklistservice?submit=EXPORT_PDF'.
								'&no_seri='.$value['no_seri'].
								'&kd_brg='.$value['kd_brg'].
								'&nm_plg='.$value['nm_plg'].
								'&hp='.$value['hp'].
								'&kd_lokasi='.$value['kd_lokasi'];
							$aksi = <<<HTML
							<a href="{$baseUrl}" target="_blank">VIEW</a>
HTML;
							$value['aksi'] = $aksi;
							$data['data'][] = $value;
						}
						$data['draw'] = $draw;
						$data['recordsTotal'] = $getReport['data']['total_row'];
						$data['recordsFiltered'] = $getReport['data']['total_row'];
						
					}
					$data['msg'] = $msg;
					$json = json_encode($data);
					echo $json;
				break;
				case 'EXPORT_PDF':
					$noSeri = $this->input->get('no_seri');
					$kdBrg = $this->input->get('kd_brg');
					$nmPlg = $this->input->get('nm_plg');
					$hp = $this->input->get('hp');
					$kdLokasi = $this->input->get('kd_lokasi');

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

					$url = $this->API_URL.'/Reportchecklistservice/GetDetailPelanggan?api=APITES';
					$payload = array(
						'kd_brg' => $kdBrg,
						'no_seri'=> $noSeri,
						'nm_plg' => $nmPlg,
						'hp' => $hp,
						'kd_lokasi' => $kdLokasi,
					);

					$getReport = $this->_postRequest($url,$payload);
					$nmBrg = '';
					$merk = '';
					$nmPlg = '';
					$hp = '';
					$almPlg = '';
					$kdLokasi = '';
					$laporan = array();
					if($getReport!=null && $getReport['code']=='success'){
						$nmBrg = $getReport['data'][0]['nm_brg'];
						$merk = $getReport['data'][0]['merk'];
						$nmPlg = $getReport['data'][0]['nm_plg'];
						$hp = $getReport['data'][0]['hp'];
						$almPlg = $getReport['data'][0]['alm_plg'];
						$kdLokasi = $getReport['data'][0]['kd_lokasi'];

						$laporan = $getReport['data'];
					}
					// echo '<pre>';
					// print_r($payload);
					// echo '</pre>';

					// echo '<pre>';
					// print_r($laporan);
					// echo '</pre>';
					// die();
					$data = array(
						'nm_brg' => $nmBrg,
						'merk' => $merk,
						'no_seri' => $noSeri,
						'nm_plg' => $nmPlg,
						'hp' => $hp,
						'alm_plg' => $almPlg,
						'kd_lokasi' => $kdLokasi,
						'laporan' => $laporan,
					);
					//$this->load->view('template_pdf/ReportchecklistservicePdf',$data);
					$content = $this->load->view('template_pdf/ReportchecklistservicePdf',$data,true);
					$curDate = date("d-F-Y H:i:s");
					$header = <<<HTML
						<p style="margin:0 0;text-align:right;">{$curDate}</p>
						<h1 style="margin:10 0mm;margin-top:0px;width:100%;text-align:center;">CHECKLIST SERVICE</h1>
						<br>
HTML;
					
					$mpdf->SetHTMLHeader($header); //Yang diulang di setiap awal halaman  (Header)
					$mpdf->WriteHTML($content);
					$mpdf->Output();
				break;
			}
		}
		
	}
}