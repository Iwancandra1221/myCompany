<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Branch extends NS_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
	}

	public function index(){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		if($_SESSION['can_read']==true){
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "BRANCH";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU BRANCH ";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);
		
			$this->RenderView('BranchView','');
		}else{
			redirect('dashboard');
		}
	}

	public function ms_branch(){
		$obj = array(
			'code' => 0,
			'msg' => '',
			'error' => '',
		);

		$this->load->library('form_validation');
		$this->form_validation->set_rules('BranchName','Branch Name','required');
		$this->form_validation->set_rules('BranchHead','Branch Head','required');
		$this->form_validation->set_rules('BranchAddress','Branch Address','required');
		//$this->form_validation->set_rules('CompanyNPWP','Company NPWP','required');
		//$this->form_validation->set_rules('UserNPWP','User NPWP','required');

		if($this->form_validation->run()){

			$BranchID = $this->input->post('BranchID');
			$BranchName = $this->input->post('BranchName');
			$BranchHead = $this->input->post('BranchHead');
			//$BranchViceHead = $this->input->post('BranchViceHead');
			$BranchAddress = $this->input->post('BranchAddress');
			$IsActive = $this->input->post('IsActive');
			//$CompanyNPWP = $this->input->post('CompanyNPWP');
			//$UserNPWP = $this->input->post('UserNPWP');
			//$WebAPI = $this->input->post('WebAPI');
			//$OvertimeLimit = $this->input->post('OvertimeLimit');
			//$BranchPhoneAreaCode = $this->input->post('BranchPhoneAreaCode');
			//$BranchPhoneNumber = $this->input->post('BranchPhoneNumber');
			$User = $this->input->post('User');

			$BranchHead = explode(' - ', $BranchHead);
			//$BranchViceHead = explode(' - ', $BranchViceHead);
			//$UserNPWP = explode(' - ', $UserNPWP);

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "BRANCH";
			$params['TrxID'] = $BranchID;
			$params['Description'] = $_SESSION["logged_in"]["username"]." SIMPAN BRANCH ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$dataBranch = array(
				'BranchName' => $BranchName,
				'BranchHead' => $BranchHead[0],
				//'BranchViceHead' => $BranchViceHead[0],
				'BranchAddress' => $BranchAddress,
				'IsActive' => $IsActive,
				//'CompanyNPWP' => $CompanyNPWP,
				//'UserNPWP' => $UserNPWP[0],
				//'WebAPI' => $WebAPI,
				//'OvertimeLimit' => $OvertimeLimit,
				//'BranchPhoneAreaCode' => $BranchPhoneAreaCode,
				//'BranchPhoneNumber' => $BranchPhoneNumber,
				
			);

			

			$getBranch = $this->BranchModel->Get($BranchID);
			if($getBranch!=null){
				//ditemukan - update branch
				$dataBranch['UpdatedBy'] = $User;
				$dataBranch['UpdatedDate'] = date("Y-m-d H:i:s");
				$obj['code'] = $this->BranchModel->update($dataBranch,['BranchID'=>$BranchID]);
				
				log_message('error','update');
			}
			else{
				//tdk ditemukan - insert branch
				$dataBranch['BranchID'] = $BranchID;
				$dataBranch['CreatedBy'] = $User;
				$dataBranch['CreatedDate'] = date("Y-m-d H:i:s");

				$obj['code'] = $this->BranchModel->insert($dataBranch);
				
				log_message('error','insert');
			}

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			log_message('error','dataBranch '.print_r($dataBranch,true));
		}
		else{
			// echo form_error('BranchName');
 			$obj['msg'] = "Ada data yang belum diisi";
		}

		
		$obj['msg'] = $obj['code'] ? 'sukses' : $obj['msg'];
		$obj['code']  = $obj['code'] ? 1 : 0 ;

		$json = json_encode($obj);
		echo $json;
	}
}
?>