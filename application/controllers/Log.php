<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Log extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('LogModel');
		$this->load->model('BranchModel');
	}

	public function LogWhatsapp()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LOG WHATSAPP";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LOG WHATSAPP ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);


		$data = array();
		$data["branches"] = $this->BranchModel->GetList();
		// $data["result"] = $this->LogModel->GetWhatsappLog();
		// echo json_encode($data["branches"]);die;
		$this->RenderView('LogWhatsappView',$data);
	}
	
	public function GetEmailLog()
	{

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LOG EMAIL";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES LOG EMAIL ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		//$branch = ($this->input->post('branch')) ? $this->input->post('branch') : "";
		//$dp1 = $this->input->post('dp1');
		//$dp2 = $this->input->post('dp2');
		//$status = ($this->input->post('status')) ? $this->input->post('status') : "";
		//$search = ($this->input->post('search')) ? $this->input->post('search') : "";
	
		$param = $_GET;
		//$result = $this->LogModel->GetEmailLog($branch, $dp1, $dp2, $status, $search);
    $data = $this->LogModel->GetEmailLog($param);
    
    
		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);
    
    echo $data;
	}

	public function GetEmailLogDetail()
	{
		
		$id = $this->input->get('id');
		if($id){
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LOG EMAIL";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES LOG EMAIL ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$result = $this->LogModel->GetEmailLogDetail($id);

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			echo json_encode($result);
		}
	}
	
	public function LogEmail()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LOG EMAIL";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LOG EMAIL ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$data = array();
		$data["branches"] = $this->BranchModel->GetList();
		$this->RenderView('LogEmailView',$data);
	}
	
	public function GetWhatsappLog()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LOG WHATSAPP";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES LOG WHATSAPP ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$branch = ($this->input->post('branch')) ? $this->input->post('branch') : "";
		$dp1 = $this->input->post('dp1');
		$dp2 = $this->input->post('dp2');
		$status = ($this->input->post('status')) ? $this->input->post('status') : "";
		$search = ($this->input->post('search')) ? $this->input->post('search') : "";
	
		$result = $this->LogModel->GetWhatsappLog($branch, $dp1, $dp2, $status, $search);

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		echo json_encode($result);
	}
	
	public function GetWhatsappLogDetail()
	{
		
		$id = $this->input->get('id');
		if($id){
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LOG WHATSAPP";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES LOG WHATSAPP ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$result = $this->LogModel->GetWhatsappLogDetail($id);

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			echo json_encode($result);
		}
	}
	
}