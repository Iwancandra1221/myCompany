<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class KpiCategoryMemberIncludeV2 extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct(); 
		$this->load->helper('FormLibrary');
		$this->load->model('ConfigSysModel');
		$this->API_ZEN = $this->ConfigSysModel->Get()->zenhrs_url;
	}

	public function index()
	{ 
		$checkAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER KPI KATEGORI MEMBER INCLUDE";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU MASTER KPI KATEGORI MEMBER INCLUDE";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$data = array(); 
		set_time_limit(60);
		
		$this->RenderView('KpiCategoryMemberIncludeV2View',$data);		
	}

	public function GetEmployees()
	{ 	 
		$param = $_GET;
		$URL = $this->API_ZEN."/Zenapi/datatable_employees";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($param),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		echo($response);
	}

	public function save()
	{ 
		set_time_limit(60);  
		$this->load->library('form_validation');
		$this->form_validation->set_rules('KPICategoryID','KPI Category','required'); 
		$this->form_validation->set_rules('USERID','User','required'); 
		$this->form_validation->set_rules('StartDate','Tanggal Awal','required'); 
		//$this->form_validation->set_rules('txtTglAkhir','Tanggal Akhir','required'); 

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER KPI KATEGORI MEMBER INCLUDE V2";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." TAMBAH MASTER KPI KATEGORI MEMBER INCLUDE V2";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);

 		$user_login = $_SESSION["logged_in"]["username"];
		$KPICategoryID = $this->input->post('KPICategoryID', true);
		$KPICategoryName = $this->input->post('KPICategoryName', true);
		$DivisionID = $this->input->post('DivisionID', true);
		$DivisionName = $this->input->post('DivisionName', true);
		$USERID = $this->input->post('USERID', true);
		$NAME = $this->input->post('NAME', true);
		$StartDate = $this->input->post('StartDate', true); 
		$IsActive = ($this->input->post("IsActive")) ? 1 : 0;
		$MemberId = $this->input->post('MemberId', true);
		
		if ($this->form_validation->run() == false) {
            $response['status'] = 'Error';
            $response['message'] = validation_errors();
        }
        else
        { 
			$URL = $this->API_ZEN."/ZenAPI/MemberIncludeV2Save?&MemberIncludeID=".urlencode($MemberId)."&KPICategoryID=".urlencode($KPICategoryID)."&KPICategoryName=".urlencode($KPICategoryName)."&DivisionID=".urlencode($DivisionID)."&DivisionName=".urlencode($DivisionName)."&USERID=".urlencode($USERID)."&NAME=".urlencode($NAME)."&StartDate=".urlencode($StartDate)."&IsActive=".urlencode($IsActive)."&ModifiedBy=".urlencode($user_login);
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $URL,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60,
			));
			$result = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $result; die;
			$hasil = array();
			if($httpcode!=200){
				$params['Remarks']="FAILED - API Zen sedang tidak bisa diakses! HTTP Code:".$httpcode;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				$response['status'] = 'Error';
	            $response['message'] = "API Zen sedang tidak bisa diakses! HTTP Code:".$httpcode;
			}
			else{
				$res = json_decode($result, true); 
				if ($res["result"]=="sukses")
				{ 
					$params['Remarks']="SUCCESS";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);
					$response['status'] = $res["result"];
				}
				else
				{
					$params['Remarks']="FAILED - ";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);
					$response['status'] = $res["result"];
					$response['message'] = $res["error"];
				}
			}
        }
        echo json_encode($response);
	}
	
	public function delete()
	{ 
		set_time_limit(60);  
		$post = $this->PopulatePost();	
		$MemberIncludeID = $this->input->post('MemberIncludeID');

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER KPI KATEGORI MEMBER INCLUDE";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." HAPUS MASTER KPI KATEGORI MEMBER INCLUDE";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);
		
		$URL = $this->API_ZEN."/ZenAPI/MemberIncludeDelete?&id=".$MemberIncludeID; 
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
		));
		$result = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $result; die;
		$hasil = array();
		if($httpcode!=200){
			$params['Remarks']="FAILED - Gagal delete member include! HTTP Code:".$httpcode;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			die("Gagal delete member include! HTTP Code:".$httpcode);
		}
		else{
			$res = json_decode($result, true); 
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			echo $res["result"];
		}
	}
  
  
	public function datatable_memberinclude()
	{
		$param = $_GET;
		$URL = $this->API_ZEN."/Zenapi/datatable_memberinclude";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($param),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		echo($response);
	}
	
	public function datatable_kpicategorydivision()
	{
		$param = $_GET;
		$URL = API_URL."/KpiCategoryMemberInclude/datatable_kpicategorydivision";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($param),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		echo($response);
	}
	
	
}