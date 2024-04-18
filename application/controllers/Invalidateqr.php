<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Invalidateqr extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('FormLibrary');
		$this->load->model("InvalidateqrModel");
	}

	public function index()
	{	
		$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		if($_SESSION["can_read"]==true){
			$this->RenderView('InvalidateqrView');
		}else{
			
		}
	}

	public function prosesSend() {
		$json_data = file_get_contents("php://input");

	    $data = json_decode($json_data, true);

		if(!empty($data['qr_code'])){

			$endpoint = "https://asia-southeast2-mishirin-726d8.cloudfunctions.net/insertReturScan";

			$data = array(
			    "secretKey" => "bWlzaGlyaW4yMDIx",
			    "qrCode" => $data['qr_code'],
			    "createdBy" => $_SESSION['logged_in']['username'],
			    "method" => $data['method']
			);

			$data_string = json_encode($data);

			$ch = curl_init($endpoint);

			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Content-Type: application/json',
			    'Content-Length: ' . strlen($data_string)
			));

			$result = curl_exec($ch);

			if (curl_errno($ch)) {
			    echo curl_error($ch);
			}

			curl_close($ch);


		}else{
			$result = array();
		}

	    print_r($result);
	}

}