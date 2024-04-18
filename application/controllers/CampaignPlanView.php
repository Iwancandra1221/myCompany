	<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	
	class CampaignPlanView extends MY_Controller
	{
		
		public function __construct(){
			parent::__construct();
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
			$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
		}
		public function index() 
		{ 
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "RENCANA CAMPAIGN / INTERVENSI";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU RENCANA CAMPAIGN / INTERVENSI ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$CheckAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));

			if(!is_null($CheckAccess)){ 
				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				$this->RenderView('CampaignPlanViewList');
			}
		}

		
		public function view($id='',$type='')
		{				

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "RENCANA CAMPAIGN / INTERVENSI";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." VIEW RENCANA CAMPAIGN / INTERVENSI ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);
			$CheckAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));

			if(!is_null($CheckAccess)){
				$data['id'] = $id;
				$data['type'] = $type;
				$data['mode'] = 'view';
				$data['akses_edit'] = $this->ModuleModel->CheckAccess($this->uri->segment(1), 'edit');
				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				$this->RenderView("CampaignPlanView",$data);
			}

		}

		public function edit($id='',$type='')
		{			
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "RENCANA CAMPAIGN / INTERVENSI";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." EDIT RENCANA CAMPAIGN / INTERVENSI ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);
			$CheckAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));

			if(!is_null($CheckAccess)){
				$data['id'] = $id;
				$data['type'] = $type;
				$data['mode'] = 'edit';
				$data['akses_edit'] = $this->ModuleModel->CheckAccess($this->uri->segment(1), 'edit');
				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				$this->RenderView("CampaignPlanView",$data);
			}
		}

	}
