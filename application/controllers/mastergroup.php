<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class mastergroup extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('GroupModel');
		$this->load->model('ZenModel');
		$this->load->helper('FormLibrary');
	}
	
	public function index()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "BRANCH";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU GROUP ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2)); 
		$post = $this->PopulatePost();		
		$data = array(); 
		set_time_limit(60); 
		$GetGroup = $this->GroupModel->GetList();  
		$data["groups"] = $GetGroup; 

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView('MasterGroupView',$data);
	}

	public function MsGroupSync()
	{	
		$user = $_SESSION["logged_in"]["useremail"];
		 
		$url = API_ZEN."/ZenAPI/GetGroups";
		$GetGroups = json_decode(file_get_contents($url),true);
		if ($GetGroups["result"]=="sukses"){
			$Groups = $GetGroups["data"];
			// start sync
			$this->ZenModel->SyncMsGroup($Groups, $user);
			// end sync
		}
		redirect("mastergroup");			
	}

	public function Update()
	{	
		$post = $this->PopulatePost();	 
		$active = 0;
		if ($post['ActiveId']==0) $active = 1; else $active = 0;

		$this->GroupModel->update($post['GroupId'],$active);  

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "BRANCH";
		$params['TrxID'] = $post['GroupId'];
		$params['Description'] = $_SESSION["logged_in"]["username"]." UPDATE GROUP ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$msg = array();
		$msg['result'] = "SUKSES";
		$msg['message'] = "Data Berhasil Di-update";
		echo json_encode($msg);	
	} 

}