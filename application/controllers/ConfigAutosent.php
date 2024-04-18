<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ConfigAutosent extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('ConfigAutosentModel');
	}

	public function index()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="JOBS"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." MENU JOBS";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		$data['server']	= $this->ConfigAutosentModel->trx_server($_SESSION['logged_in']['branch_id']);
		$data['location']	= $this->ConfigAutosentModel->get_location();
		$this->RenderView('ConfigAutosent',$data);

		$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
	}

	function getList()
	{
		$server = $this->ConfigAutosentModel->trx_server($_SESSION['logged_in']['branch_id']);
		$URLAPI = $server . 'bktAPI/ConfigAutoSent/getList';
		$result = $this->_getRequest($URLAPI);
		echo json_encode($result);
	}


	function getCabang()
	{
		$server = $this->ConfigAutosentModel->trx_server($_SESSION['logged_in']['branch_id']);
		$URLAPI = $server . 'bktAPI/ConfigAutoSent/getCabang?api=APITES';
		$result = $this->_getRequest($URLAPI);
		echo json_encode($result);
	}

	function sendConfig()
	{
		$server = $this->ConfigAutosentModel->trx_server($_SESSION['logged_in']['branch_id']);
		$URLAPI = $server . 'bktAPI/ConfigAutoSent/sendConfig';
		$data = array(
			'api' => 'APITES',
			'proses' => $this->input->post('proses'),
			'Kd_Lokasi' => $this->input->post('Kd_Lokasi'),
			'Nm_Lokasi' => $this->input->post('Nm_Lokasi'),
			'Jumlah_Record' => $this->input->post('Jumlah_Record'),
			'Initial' => $this->input->post('Initial'),
			'Aktif' => $this->input->post('Aktif'),
			'Db_Server' => $this->input->post('Db_Server'),
			'Db_Database' => $this->input->post('Db_Database'),
			'Db_API' => $this->input->post('Db_API'),
		);
		$result = $this->_postRequest($URLAPI, $data);
		return $result;
	}


	private function _getRequest($url) {

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec($ch);

    $result = json_decode($server_output, true);

    curl_close($ch);

    return $result;
	}

	private function _postRequest($url, $data) {
 
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec($ch);

    $result = json_decode($server_output, true);

    curl_close($ch);

    return $result;
	}
	
	
}