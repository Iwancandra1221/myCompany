<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ZenSync extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model("ZenModel");
		$this->api_zen = $this->ConfigSysModel->Get()->zenhrs_url;
	}

	public function MsGroupSync()
	{	
		$user = $_SESSION["logged_in"]["useremail"];
		

		$url = $this->api_zen."/ZenAPI/GetGroups";
		$GetGroups = json_decode(file_get_contents($url),true);
		if ($GetGroups["result"]=="sukses"){
			$Groups = $GetGroups["data"];
			// start sync
			$this->ZenModel->SyncMsGroup($Groups, $user);
			// end sync

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS GROUP SYNC";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU MS GROUP SYNC ";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

		}
		redirect("Dashboard");			
	}

	public function MsBranchSync()
	{	
		$user = $_SESSION["logged_in"]["useremail"];
		$url = $this->api_zen."/ZenAPI/GetBranches";
		$GetBranches = json_decode(file_get_contents($url),true);
		// die(json_encode($GetBranches));
		
		if ($GetBranches["result"]=="sukses"){
			$branches = $GetBranches["data"];
			// start sync
			$this->ZenModel->SyncMsBranch($branches, $user);
			// end sync

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS BRANCH SYNC";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU MS BRANCH SYNC ";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);
		}

		// redirect("Dashboard");			
	}

	public function MsBranchList(){

		$json = $this->ZenModel->JsonBranch();
		print_r(json_encode($json));

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS BRANCH SYNC";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." MS BRANCH LIST ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);
	}

	public function UpdateStatus(){
		$post = $this->PopulatePost();
		if(!empty($post['BranchID'])){
			$this->ZenModel->UpdateStatus($post['BranchID'],$post['Status']);

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS BRANCH SYNC";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." UPDATE STATUS MS BRANCH SYNC ";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);
		}
	}

}