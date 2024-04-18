<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jobs extends MY_Controller {

	function __construct()
	{
		parent::__construct();  
		$this->load->model('JobsModel');
	}
	 
	public function JobLogs()
	{
		$jobsid = $this->input->get('id');

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "JOBS";
		$params['TrxID'] = $jobsid;
		$params['Description'] = $_SESSION["logged_in"]["username"]." JOBS LOG ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$data = array();
		$data["JobsID"] = $jobsid;
		$data["ListBranches"] = $this->JobsModel->GetListBranch(); 
		$data["ListJobs"] = $this->JobsModel->GetListJobs(); 

		if ($jobsid!="" || $jobsid!=null)
		{
			$data["ListLog"] = $this->LogsList($jobsid);
			$data["BranchId"] = $_SESSION["conn"]->BranchId;
		}
		else
		{ 
			$data["ListLog"] = null;
			$data["BranchId"] = null;
		}

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView("JobLogsView", $data);
	}

	public function LogsList($jobsid)
	{  
		$api = 'APITES'; 
		$dp1 = date('Y-m-d', strtotime("-7 day", strtotime(date('Y-m-d'))));  
		$dp2 = date('Y-m-d');

		// $dp1 = date('2022-12-1');
		// $dp2 = date('2022-12-31');  

		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database; 

		$result = json_decode(file_get_contents($url.API_BKT."/Jobs/JobsLogs?api=".urlencode($api)."&svr=".urlencode($svr)."&db=".urlencode($db)."&dp1=".urlencode($dp1)."&dp2=".urlencode($dp2)."&jobsid=".urlencode($jobsid)),true);  

    	if(count($result)==0){
    		return null; 
		} 
		else
		{
			return $result; 
		} 
	}
 
	public function ViewLogsByBranch()
	{  
				
		$api = 'APITES';

		$url = $this->input->get('url');
		$svr = $this->input->get('svr');
		$db = $this->input->get('db'); 
		$dp1 = $this->input->get('dp1'); 
		$dp2 = $this->input->get('dp2'); 
		$jobsid = $this->input->get('jobsid'); 
		// $url = $_SESSION["conn"]->AlamatWebService;
		// $svr = $_SESSION["conn"]->Server;
		// $db  = $_SESSION["conn"]->Database; 
		
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "JOBS";
		$params['TrxID'] = $jobsid;
		$params['Description'] = $_SESSION["logged_in"]["username"]." JOBS LOG ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$result = json_decode(file_get_contents($url.API_BKT."/Jobs/JobsLogs?api=".urlencode($api)."&svr=".urlencode($svr)."&db=".urlencode($db)."&dp1=".urlencode($dp1)."&dp2=".urlencode($dp2)."&jobsid=".urlencode($jobsid)),true);  

    	if(count($result)==0){
			// ActivityLog Update FAILED
			$params['Remarks']="FAILED - Data Tidak Ditemukan";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

    		echo "Tidak ada data";
    		die;
		} 
		else
		{
			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			echo json_encode($result); 
		} 
	}
	
	public function index()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "JOBS";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU JOBS ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);


		$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		$this->RenderView('JobsView');

	}

	public function Schedule($data='')
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "JOBS";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." SCHEDULE JOBS ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		if(!empty($data)){
			$this->ModuleModel->CheckAccess($this->uri->segment(1), '');
			$data_cabang['id'] = $data;
			$data_cabang['jobs'] = $this->JobsModel->Get_Jobs($data);
			$data_cabang['cabang'] = $this->JobsModel->Get_Schedule($data);

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('JobsView',$data_cabang);
		}else{
			// ActivityLog Update FAILED
			$params['Remarks']="FAILED - Data Tidak Ditemukan";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			redirect(site_url('Jobs'));
		}

	}

	function GetList(){
		$managers = $this->JobsModel->GetList();
		echo json_encode($managers);
	}

	function Proses($schedule=''){
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "JOBS";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES JOBS ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		if(!empty($schedule)){
			$data['proses'] = 'schedule';
			$data['post'] 	= $this->input->post();
			$hasil =  str_replace(" ", "", $this->JobsModel->proses($data));

			if($hasil=='success'){
				echo '1';
			}else if($hasil=='active'){
				echo '2';
			}else{
				echo $hasil;
			}
			
			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

		}else{

			if(!empty($this->input->post('proses'))){
				if(!empty($this->input->post('jobsid'))){
					$data['proses'] 			=	$this->input->post('proses');
					$data['jobsid'] 			=	$this->input->post('jobsid');
					$data['job_description'] 	=	$this->input->post('job_description');
					$data['function_jobs'] 		=	$this->input->post('function_jobs');
					$data['schedule_type'] 		=	$this->input->post('schedule_type');
					$data['job_priority'] 		=	$this->input->post('job_priority');
					$data['server'] 			=	$this->input->post('server');
					$data['database'] 			=	$this->input->post('database');
					$data['active'] 			=	$this->input->post('active');
					$data['custom_query'] 		=	$this->input->post('custom_query');

					$hasil =  str_replace(" ", "", $this->JobsModel->proses($data));

					if($hasil=='success'){
						echo '1';
					}else if($hasil=='active'){
						echo '2';
					}else{
						echo $hasil;
					}

					// ActivityLog Update SUCCESS
					$params['Remarks']="SUCCESS";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

				}else{
					// ActivityLog Update FAILED
					$params['Remarks']="FAILED - Data Tidak Ditemukan";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

					echo 'kosong';
				}
			}else{
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				redirect(site_url('Jobs'));
			}

		}
	}

}
