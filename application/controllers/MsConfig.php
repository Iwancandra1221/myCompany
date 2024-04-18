<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MsConfig extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('MsConfigModel');
	}
	
	public function Index(){
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MS CONFIG"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MSCONFIG";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$data = array();
        if($this->uri->segment(2) != '')
	    	$ctrname = $this->uri->segment(1)."/".$this->uri->segment(2);
        else
	       	$ctrname = $this->uri->segment(1);

    	$data['access'] = $this->ModuleModel->getDetail($ctrname, $_SESSION['role']);
		
		$data['ConfigType'] = $this->MsConfigModel->GetConfigType();
		$data["result"] = $this->MsConfigModel->GetConfigAll();
		$this->RenderView('MsConfig',$data);

		$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
	}
	
	
	public function Add()
	{

		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MS CONFIG"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MSCONFIG - ADD";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$data = array();
		$data['ConfigName'] = $this->MsConfigModel->GetConfigName();
		$data['ConfigType'] = $this->MsConfigModel->GetConfigType();
		$data['Merk'] = $this->MsConfigModel->GetConfigName();
		$data['Group'] = $this->MsConfigModel->GetGroup();
		$this->RenderView('MsConfigAdd',$data);

		$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
	}
	
	public function Edit()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MS CONFIG"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MSCONFIG - EDIT";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$id = $this->input->get('id');
		$data = $this->MsConfigModel->GetConfigById($id);
		echo json_encode($data);

		$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
	}
	
	
	public function GetConfigName(){
		$type = $this->input->get('type');
		$data = $this->MsConfigModel->GetConfigName($type);
		echo json_encode($data);
	}
	
	public function Insert()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MS CONFIG"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MSCONFIG - INSERT";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$post = $this->PopulatePost();	
		$result = $this->MsConfigModel->Insert($post);
		if($result=='SUKSES'){
			$this->session->set_flashdata('success','Data berhasil disimpan!');

			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			if (isset($_POST["btnSubmitAdd"])) {
				redirect('MsConfig/Add');			
			} else {
				redirect('MsConfig');
			}
		}
		else{
			$paramsLog['Remarks']="FAILED - GAGAL INSERT";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			$this->session->set_flashdata('error','Data gagal disimpan! '.$result);
			redirect('MsConfig');
		}
	}
	
	public function AddParam()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MS CONFIG"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MSCONFIG - ADDPARAM";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$post = $this->PopulatePost();	
		$post['ConfigType']='PARAM';
		$post['IsActive'] = 1;
		// echo json_encode($post);die;
		
		$result = $this->MsConfigModel->Insert($post);
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

			$paramsLog['Remarks']="FAILED - GAGAL TAMBAH PARAM";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
		}
		echo json_encode($msg);
	}
	
	public function Update()
	{

		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MS CONFIG"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MSCONFIG - ADDPARAM";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$post = $this->PopulatePost();	
		// echo json_encode($post);die;
		$result = $this->MsConfigModel->Update($post);
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

			$paramsLog['Remarks']="FAILED - GAGAL UPDATE MS CONFIG";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
		}
		echo json_encode($msg);
	}
}