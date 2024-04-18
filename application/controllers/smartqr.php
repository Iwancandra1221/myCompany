<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class smartqr extends NS_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('MsSmartQRModel');
	}

	public function index()
	{
		$id= urldecode($this->input->get('id'));
		$isgroup= urldecode($this->input->get('isgroup'));
		$tipe= urldecode($this->input->get('tipe'));
		$merk= urldecode($this->input->get('merk'));
		$lokasi_qr_code= urldecode($this->input->get('lokasi_qr_code'));
		$ver= urldecode($this->input->get('ver'));
		$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$result = $this->MsSmartQRModel->search($id, $isgroup, $tipe, $merk, $lokasi_qr_code, $ver, $url);
		// echo json_encode($result);die;
		$result = json_decode(json_encode($result), true); // convert stdClass -> array
		
		if($result){
			if($result['url_redirect'] !=''){
				redirect($result['url_redirect']);
			}
			else{
				// // $data = array();
				// // //fixed column
				// // $data['id'] 			= $result['id'];
				// // $data['isgroup'] 		= $result['isgroup'];
				// // $data['merk'] 			= $result['merk'];
				// // $data['tipe'] 			= $result['tipe'];
				// // $data['lokasi_qr_code'] = $result['lokasi_qr_code'];
				// // $data['ver'] 			= $result['ver'];
				// // $data['url'] 			= $result['url'];
				// // $data['url_redirect'] 	= $result['url_redirect'];
				// // $data['qty'] 			= $result['qty'];
				// // $data['created_by'] 	= $result['created_by'];
				// // $data['created_date']	= $result['created_date'];
				// // $data['modified_by'] 	= $result['modified_by'];
				// // $data['modified_date'] 	= $result['modified_date'];
				
				// // //added column/param
				// // $params = $this->MsSmartQRModel->GetParam($result['id']);
				// // for($i=0;$i<count($params);$i++) {
					// // $data[trim($params[$i]->ParamName)] = $params[$i]->ParamValue;
				// // }
				
				// // $barangs = $this->MsSmartQRModel->GetKdBrg($result['tipe']);
				// // $data['kd_brg'] = explode(',',$barangs);

				// // echo json_encode(array("result"=>"success","data"=>$data));
				die('<h1>Error 404<br><small>Halaman tidak ditemukan</small></h1>');
			}
		}
		// else echo json_encode(array("result"=>"failed","error"=>"Halaman tidak ditemukan"));
		else die('<h1>Error 404<br><small>Halaman tidak ditemukan</small></h1>');
	}
}