<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MsConfigRequestApproval extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('MsConfigRequestApprovalModel');
		$this->load->model('BranchModel');
	}
	
	public function Index(){
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MS CONFIG REQUEST APPROVAL"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MS CONFIG REQUEST APPROVAL";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$data = array();
        if($this->uri->segment(2) != '')
	    	$ctrname = $this->uri->segment(1)."/".$this->uri->segment(2);
        else
	       	$ctrname = $this->uri->segment(1);

    	$data['access'] = $this->ModuleModel->getDetail($ctrname, $_SESSION['role']);
		 
		$data["result"] = $this->MsConfigRequestApprovalModel->GetConfigAll();
		$data['ListEvent'] = $this->MsConfigRequestApprovalModel->GetListEvent();
		$data['ListInfo'] = $this->MsConfigRequestApprovalModel->GetListInfo();
		$data['ListSalesMan'] = $this->MsConfigRequestApprovalModel->GetLevelSalesman();
		$data['ListDivision'] = $this->MsConfigRequestApprovalModel->GetDivision();
		$data["ListBranches"] = $this->BranchModel->GetList(); 
		$this->RenderView('MsConfigRequestApproval',$data);

		$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
	}

	public function GetListInfoDetail()
	{

		$id = $this->input->get('id');
		$data = $this->MsConfigRequestApprovalModel->GetListInfoDetail($id);
		echo json_encode($data);
	} 
	
	public function Add()
	{

		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MS CONFIG REQUEST APPROVAL"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." MENU MS CONFIG REQUEST APPROVAL - ADD";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$id = $this->input->get('id');
		$data = array(); 
		$data['ListEvent'] = $this->MsConfigRequestApprovalModel->GetListEvent();
		$data['ListInfo'] = $this->MsConfigRequestApprovalModel->GetListInfo();
		$data['ListSalesMan'] = $this->MsConfigRequestApprovalModel->GetLevelSalesman();
		$data['ListDivision'] = $this->MsConfigRequestApprovalModel->GetDivision();
		$data['ListBranches'] = $this->BranchModel->GetList();  
		if ($id<>null || $id <> "")
		{
			$data_header = $this->MsConfigRequestApprovalModel->GetConfigById($id);
			$data['data_header'] = $data_header; 
			$data['data_detail'] = $this->MsConfigRequestApprovalModel->GetConfigDetailById($id);  
			$data['info1'] = $this->MsConfigRequestApprovalModel->GetListInfoDetail($data_header->AddInfo1Name);
			$data['info2'] = $this->MsConfigRequestApprovalModel->GetListInfoDetail($data_header->AddInfo2Name);
			$data['info3'] = $this->MsConfigRequestApprovalModel->GetListInfoDetail($data_header->AddInfo3Name);
			$data['idconfig'] = $id;
		}
		else
		{
			$data['idconfig'] = null; 
		}

		$this->RenderView('MsConfigRequestApprovalAdd',$data);

		$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
	}
	
	public function Edit()
	{ 	
		$id = $this->input->get('id');
		$data = $this->MsConfigRequestApprovalModel->GetConfigById($id); 
		echo json_encode($data);
	}
 
	public function EditDetail()
	{
		$id = $this->input->get('id');
		$data = $this->MsConfigRequestApprovalModel->GetConfigDetailById($id);
		echo json_encode($data);
	}
	   
	public function Insert()
	{  
		$post = $this->PopulatePost();
		
		if ($post['datatemp']=="")
		{  
			echo "<script> alert('Data Approval Tidak Boleh Empty');
							window.location.href='ADD';
							</script>";
		}
		else
		{
			$result = $this->MsConfigRequestApprovalModel->Insert($post);
			if($result=='SUKSES'){
				$this->session->set_flashdata('success','Data berhasil disimpan!');
				redirect('MsConfigRequestApproval');
			}
			else{
				$this->session->set_flashdata('error','Data gagal disimpan! '.$result);
				redirect('MsConfigRequestApproval');
			}  
		} 
	}
	 
	public function Update()
	{

		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MS CONFIG REQUEST APPROVAL"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." MENU MS CONFIG REQUEST APPROVAL - UPDATE";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$post = $this->PopulatePost();	
		// echo json_encode($post);die;
		$result = $this->MsConfigRequestApprovalModel->Update($post);
		if($result=='SUKSES'){
			$msg = array();
			$msg['result'] = "SUKSES";
			$msg['message'] = "Data Berhasil Di-update";

			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
		}
		else{
			$msg = array();
			$msg['result'] = "FAILED";
			$msg['message'] = $result;

			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
		}
		echo json_encode($msg);
	}

	public function InsertDetail()
	{

		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MS CONFIG REQUEST APPROVAL"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." MENU MS CONFIG REQUEST APPROVAL - INSERT DETAIL";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);


		$post = $this->PopulatePost();	
		// echo json_encode($post);die;
		$result = $this->MsConfigRequestApprovalModel->InsertDetail($post);
		if($result=='SUKSES'){
			$msg = array();
			$msg['result'] = "SUKSES";
			$msg['message'] = "Data Berhasil Di-update";

			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
		}
		else{
			$msg = array();
			$msg['result'] = "FAILED";
			$msg['message'] = $result;

			$paramsLog['Remarks']="FAILED - GAGAL INSERT DETAIL";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
		}
		echo json_encode($msg);
	}
	
	public function DeleteDetail()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MS CONFIG REQUEST APPROVAL"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." MENU MS CONFIG REQUEST APPROVAL - DELETE DETAIL";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$post = $this->PopulatePost();	
		// echo json_encode($post);die;
		$result = $this->MsConfigRequestApprovalModel->DeleteDetail($post);
		if($result=='SUKSES'){
			$msg = array();
			$msg['result'] = "SUKSES";
			$msg['message'] = "Data Berhasil Di-update";

			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
		}
		else{
			$msg = array();
			$msg['result'] = "FAILED";
			$msg['message'] = $result;

			$paramsLog['Remarks']="FAILED - GAGAL DELETE DETAIL";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
		}
		echo json_encode($msg);
	}
	public function DeleteConfig()
	{

		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MS CONFIG REQUEST APPROVAL"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." MENU MS CONFIG REQUEST APPROVAL - DELETE CONFIG";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$post = $this->PopulatePost();	
		// echo json_encode($post);die;
		$result = $this->MsConfigRequestApprovalModel->DeleteConfig($post);
		if($result=='SUKSES'){
			$msg = array();
			$msg['result'] = "SUKSES";
			$msg['message'] = "Data Berhasil Di-update";

			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
		}
		else{
			$msg = array();
			$msg['result'] = "FAILED";
			$msg['message'] = $result;

			$paramsLog['Remarks']="FAILED - DELETE CONFIG GAGAL";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
		}
		echo json_encode($msg);
	}
}