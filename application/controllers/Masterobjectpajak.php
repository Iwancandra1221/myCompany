<?php
require FCPATH.'application/controllers/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
defined('BASEPATH') or exit('No direct script access allowed');

class Masterobjectpajak extends MY_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}
	
	public function index(){
		// $url = API_URL.'/Masterobjectpajak/export_master_object_pajak';
		// $result = $this->_postRequest($url,array(),false);
		// if($result!=''){
		// 	$result = json_decode($result,true);
		// 	if(count($result)>0 && count($result['data'])>0){
		// 		$report =  $result['data'];
		// 	}
		// }
		
		$data = array(
			'urlImport' => base_url().'Masterobjectpajak/import_master_object_pajak',
			'urlExport' => base_url().'Masterobjectpajak/export_master_object_pajak',
			'getMasterObjekPajak' => base_url().'Masterobjectpajak/get_master_object_pajak',
			//'report' => $report,
		);
		$this->RenderView('MasterObjectPajakView',$data);
	}
	private function _postRequest($url,$data,$isJson = false){
		//echo $url.'<br>';
		$ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POST, 1);

	    if ($isJson) {
	        // Jika data adalah JSON, encode ke JSON dan atur header
	        $strJson = json_encode($data);
	        //echo $strJson;
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $strJson);
	        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	    } else {
	        // Jika data adalah form data, atur payload dengan http_build_query
	        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	    }

	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	    $result = curl_exec($ch);

	    if (curl_errno($ch)) {
	        echo 'Curl error: ' . curl_error($ch);
	    }

	    curl_close($ch);

	    return $result;
	}
	public function get_master_object_pajak(){
		// echo '<pre>';
		// print_r($_POST);
		// echo '</pre>';
		$obj = array(
			'draw' => 1,
			'recordsTotal'=> 0,
			'recordsFiltered' => 0,
			'code' => 0,
			'msg' => '',
			'data' => array(),
		);

		// $post = json_encode($_POST);
		// echo $post;
		$url = $this->API_URL.'/Masterobjectpajak/get_master_object_pajak';
		$result = $this->_postRequest($url,$_POST,true);
		if($result!=''){
			$resultData = json_decode($result);
			if(count($resultData->data) > 0 ){
				$obj = $resultData;
			}
		}
		
		$json = json_encode($obj);
		echo $json;
	}
	public function export_master_object_pajak(){
		$url = $this->API_URL.'/Masterobjectpajak/export_master_object_pajak';
		$result = $this->_postRequest($url,array(),false);

		$result = json_decode($result,true);
		if($result!=''){
			$data = array(
				'report' => $result['data'],
			);
			$this->load->view('template_xls/MasterObjekPajakXls',$data);
		}
	}
	public function import_master_object_pajak(){
		$obj = array(
			'code' => 0,
			'msg' => 'Terjadi kesalahan saat mengunggah file',
			'data' => array(),
		);
		if (isset($_POST['submit'])) {
		    // Cek apakah file Excel diunggah tanpa error
		    if ($_FILES['excelFile']['error'] == 0) {
		        $tempFilePath = $_FILES['excelFile']['tmp_name']; // Lokasi file sementara

		        $dir = 'upload/master_objek_pajak/';
		        if (!file_exists($dir)) {
        			mkdir($dir, 0777, true); // 
        		}
		        // Pindahkan file Excel ke lokasi yang diinginkan
		        $targetFilePath = $dir . $_FILES['excelFile']['name'].'_'.date('YmdHis');
		        move_uploaded_file($tempFilePath, $targetFilePath);

		        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
		        $reader->setReadDataOnly(true);
				$spreadsheet = $reader->load($targetFilePath);
				$sheet = $spreadsheet->getActiveSheet();
				$data = [];
				$rowNumber = 0;
		        foreach ($sheet->getRowIterator() as $row) {
		        	if($rowNumber>0){
		        		$rowData = [];
			            $cellNumber=0;
			            foreach ($row->getCellIterator() as $cell) {
			            	if($cellNumber<4){
			            		$rowData[] = $cell->getValue();
			            	}
			                $cellNumber+=1;
			            }
			            $data[] = $rowData;
		        	}
		            
		            $rowNumber+=1;
		        }
		        unlink($targetFilePath);
		        if(count($data)>0){
		        	// Hapus file Excel setelah selesai membaca
		        	
		        	$dataTmp = [];
		        	foreach($data as $value){
		        		if($value[0]!=null){
		        			$dataTmp[] = array(
			        			'kode_objek_pajak' => $value[0],
				        		'nama_objek_pajak' => $value[1],
				    			'pasal_pph' => $value[2],
				    			'is_active' => ($value[3] == 'aktif' ? 1 : 0),
				    			'modified_by' => $_SESSION['logged_in']['username'],
			        		);
		        		}
		        		
		        	}
		        	$url = $this->API_URL.'/Masterobjectpajak/import_master_object_pajak';
		        	$result = $this->_postRequest($url,$dataTmp,true);
		        	if($result!=''){
		        		$resultData = json_decode($result,true);
		        		if($resultData!=null){
		        			$obj = array(
			        			'code' => $resultData['code'],
			        			'msg' => $resultData['msg'],
			        			'data' => $resultData['data'],
			        		);
		        		}
		        		
		        	}
		        	
		        }

		       
		    }
		}
		$json = json_encode($obj);
		echo $json;

	}
}
?>