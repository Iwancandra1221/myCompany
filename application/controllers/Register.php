<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Register extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('RegisterModel');
	}
	function history(){
		$obj = array(
			'code' => 0,
			'msg' => '',
			'error' => '',
		);

		$key = 'APITEST';

		$ApiKey = $this->input->post('ApiKey');

		$UserEmail = $this->input->post('UserEmail');
		$AlternateID = $this->input->post('AlternateID');
		$username = $this->input->post('username');
		$userpassword = $this->input->post('userpassword');
		$setpass = $this->input->post('setpass');
		$UserLevel = $this->input->post('UserLevel');
		$IsActive = $this->input->post('IsActive');
		$branch_id = $this->input->post('branch_id');
		$GroupID = $this->input->post('GroupID');
		$BranchName = $this->input->post('BranchName');
		$GroupName = $this->input->post('GroupName');
		$City = $this->input->post('City');
		$SalesmanID = $this->input->post('SalesmanID');
		$RefID = $this->input->post('RefID');
		$DivisionID = $this->input->post('DivisionID');
		$DivisionName = $this->input->post('DivisionName');
		$EmpTypeID = $this->input->post('EmpTypeID');
		$EmpType = $this->input->post('EmpType');
		$EmpLevelID = $this->input->post('EmpLevelID');
		$EmpLevel = $this->input->post('EmpLevel');
		$EmpPositionID = $this->input->post('EmpPositionID');
		$EmpPositionName = $this->input->post('EmpPositionName');
		$HiredDate = $this->input->post('HiredDate');
		$EndDate = $this->input->post('EndDate');
		$Mobile = $this->input->post('Mobile');
		$Whatsapp = $this->input->post('Whatsapp');
		$RoleId = $this->input->post('RoleId');
		$user = $this->input->post('user');

		if($ApiKey=='APITEST'){
			$dataUserHd = array(
				'UserEmail' => $UserEmail,
				'AlternateID' => $AlternateID,
				'UserName' => $username,
				//'UserPassword' => $userpassword,
				'setpass' => $setpass,
				'UserLevel' => $UserLevel,
				'IsActive' => $IsActive,
				//'Flag' => $,
				'branch_id' => $branch_id,
				//'Payroll_Pwd' => $,
				//'Payroll_SetPass' => $,
				//'Payroll_IsActive' => $,
				'Email' => $UserEmail,
				//'CreatedBy' => $user,
				//'CreatedDate' => date('Y-m-d H:i:s'),
				'UpdatedBy' => $user,
				'UpdatedDate' => date('Y-m-d H:i:s'),
				//'role_pm_id' => $,
				//'DefaultDatabaseId' => $,
				'GroupID' => $GroupID,
				'BranchName' => $BranchName,
				'GroupName' => $GroupName,
				'City' => $City,
				'SalesmanID' => $SalesmanID,
				'RefID' => $RefID,
				'DivisionID' => $DivisionID,
				'DivisionName' => $DivisionName,
				'EmpTypeID' => $EmpTypeID,
				'EmpType' => $EmpType,
				'EmpLevelID' => $EmpLevelID,
				'EmpLevel' => $EmpLevel,
				'EmpPositionID' => $EmpPositionID,
				'EmpPositionName' => $EmpPositionName,
				'HiredDate' => $HiredDate,
				//'Pengangkatan' => $,
				'EndDate' => $EndDate,
				'Mobile' => $Mobile,
				//'BankName' => $,
				//'BankAccountNumber' => $,
				//'UserEmailOld' => $,
				//'needSync' => $,
				'Whatsapp' => $Whatsapp,
			);
			$dataUserDt = array(
				'UserEmail' => $UserEmail,
				'RoleId' => $RoleId,
				'USERID' => $AlternateID,
			);
			$dataTbUserDt = array(
				'UserEmail' => $UserEmail,
				'role_id' => $RoleId,
				'UserID' => $AlternateID,
			);

			$getRegister = $this->RegisterModel->getMsUserHd(['UserEmail'=>$UserEmail]);
			if($getRegister!=null){
				//ditemukan - update branch
				$obj['code'] = $this->RegisterModel->update($dataUserHd,$dataUserDt,$dataTbUserDt,['UserEmail'=>$UserEmail]);
				//log_message('error','update');
			}
			else{
				//tdk ditemukan - insert branch
				$dataUserHd['UserEmail'] = $UserEmail;//PRIMARY
				$dataUserHd['userpassword'] = $userpassword;
				$dataUserHd['GroupID'] = $GroupID;
				$dataUserHd['CreatedBy'] = $user;
				$dataUserHd['CreatedDate'] = date('Y-m-d H:i:s');

				$obj['code'] = $this->RegisterModel->insert($dataUserHd,$dataUserDt,$dataTbUserDt);
				//log_message('error','insert');
			}
			//log_message('error','dataRegister '.print_r($dataUserHd,true).' dataUserDt '.print_r($dataUserDt,true).' dataTbUserDt '.print_r($dataTbUserDt,true));
		}
		

		
		$obj['msg'] = $obj['code'] ? 'sukses' : $obj['msg'];
		$obj['code']  = $obj['code'] ? 1 : 0 ;

		$json = json_encode($obj);
		echo $json;
	}
}
