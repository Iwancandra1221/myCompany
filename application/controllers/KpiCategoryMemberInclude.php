<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class KpiCategoryMemberInclude extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct(); 
		$this->load->helper('FormLibrary');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		$this->API_ZEN = $this->ConfigSysModel->Get()->zenhrs_url;
	}

	public function index()
	{ 
		$checkAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2)); 
		$post = $this->PopulatePost();		

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
		$listdivision = '';
		if(!ISSET($_SESSION['logged_in']['DivisionListUnderDivHead'])){
			// Ambil List Division user dan divisi di bawah nya
			$URL = $this->API_ZEN."/Zenapi/DivisionListUnderDivHead/".$_SESSION['logged_in']['userid'];
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $URL,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60,
			));
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $response; die;
			if($httpcode!=200){
				echo 'API ZEN sedang tidak bisa diakses! HTTP Code:'.$httpcode; die;
			}
			else{
				$result = json_decode($response, true);
				if($result['result'] =='sukses'){
					// simpan  divisionid ke dalam session supaya tidak hit API ZEN setiap reload halaman
					$_SESSION['logged_in']['DivisionListUnderDivHead'] = $result['data'];
				}
				else{
					echo 'Ambil List Divisi UnderDiv gagal. Error: '.$result->error; die;
				}
			}
		}
		
		$divisionid = array_column($_SESSION['logged_in']['DivisionListUnderDivHead'], 'DivisionID');
		$divisionid = array_values($divisionid);
		$listdivision = implode(';;',$divisionid);
		
		//GET LIST KPI CATEGORY YG SUDAH DIDAFTARKAN DI BHAKTI
		$data["listdivision"] = array();
		$URL = $this->API_URL."/KpiCategoryMemberInclude/Master_KPICategory_AmbilList?api=APITES&jenis=NON-SALESMAN&kpicategory=".urlencode($listdivision);
		// echo $URL;die;
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
		));
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $response; die;
		if($httpcode!=200){
			$params['Remarks']="FAILED - URL ".$URL." sedang tidak bisa diakses! HTTP Code:".$httpcode;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			echo 'API '.$this->API_URL.' sedang tidak bisa diakses! HTTP Code:'.$httpcode; die;
		}
		else{
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			$data["listdivision"] = json_decode($response, true);
		}

		$this->alert  = "";
        $data["alert"] = $this->alert ; 
		$this->RenderView('KpiCategoryMemberIncludeView',$data);		
	}

	public function GetEmployees()
	{ 	
		$URL = $this->API_ZEN."/Zenapi/GetEmployees";
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
			die("API Zen sedang tidak bisa diakses!");
		}
		else{
			$ListEmployee = json_decode($result, true); 
			$hasil = $ListEmployee['data'];
		}
		$result = json_encode($hasil);
		header('HTTP/1.1: 200');
		header('Status: 200');
		header('Content-Length: '.strlen($result));
		exit(json_encode($hasil));  
	}


	public function ListMemberInclude()
	{ 
		$listdivision = '';
		if(($_SESSION['logged_in']['isSalesman']==0) && (ISSET($_SESSION['logged_in']['DivisionListUnderDivHead']))){		
			//Ubah DivisionID menjadi array 1 dimensi
			$divisionid = array_column($_SESSION['logged_in']['DivisionListUnderDivHead'], 'DivisionID');
			$divisionid = array_values($divisionid);
			$listdivision = implode(';;',$divisionid);
		}
		
		//GET MEMBER INCLUDE
		$URL = $this->API_ZEN."/Zenapi/MemberIncludeList?division_id=".$listdivision;
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
			die("API Zen sedang tidak bisa diakses!");
		}
		else{
			$MemberIncludeList = json_decode($result, true); 
			$hasil = $MemberIncludeList['data'];
		}
		
		$result = json_encode($hasil);
		header('HTTP/1.1: 200');
		header('Status: 200');
		header('Content-Length: '.strlen($result));
		exit(json_encode($hasil));  
	}
	
	public function save()
	{ 
		set_time_limit(60);  
		$this->load->library('form_validation');
		$this->form_validation->set_rules('cboDivision','KPI Category','required'); 
		$this->form_validation->set_rules('txtUserID','User','required'); 
		$this->form_validation->set_rules('txtTglAwal','Tanggal Awal','required'); 
		//$this->form_validation->set_rules('txtTglAkhir','Tanggal Akhir','required'); 

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER KPI KATEGORI MEMBER INCLUDE";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." TAMBAH MASTER KPI KATEGORI MEMBER INCLUDE";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);

 		$user_login = $_SESSION["logged_in"]["username"];
		$cboDivision = $this->input->post('cboDivision', true);
		$txtUserID = $this->input->post('txtUserID', true);
		$txtUserName = $this->input->post('txtUserName', true);
		$txtTglAwal = $this->input->post('txtTglAwal', true); 
		$txtTglAkhir = $this->input->post('txtTglAkhir', true);   
		$MemberId = $this->input->post('MemberId', true);   

		$kpi = explode(';;', $cboDivision);
		if ($this->form_validation->run() == false) {
            $response['status'] = 'Error';
            $response['message'] = validation_errors();
        }
        else
        { 
			if ($MemberId=="")
			{
				$URL = $this->API_ZEN."/ZenAPI/MemberIncludeAdd?&KPICategoryID=".urlencode($kpi[0])."&KPICategoryName=".urlencode($kpi[1])."&USERID=".urlencode($txtUserID)."&Name=".urlencode($txtUserName)."&StartDate=".urlencode($txtTglAwal)."&EndDate=".urlencode($txtTglAkhir)."&CreatedBy=".urlencode($user_login);
			}
			else
			{
				$URL = $this->API_ZEN."/ZenAPI/MemberIncludeUpdate?&MemberIncludeID=".urlencode($MemberId)."&KPICategoryID=".urlencode($kpi[0])."&KPICategoryName=".urlencode($kpi[1])."&USERID=".urlencode($txtUserID)."&Name=".urlencode($txtUserName)."&StartDate=".urlencode($txtTglAwal)."&EndDate=".urlencode($txtTglAkhir)."&ModifiedBy=".urlencode($user_login);
			}
			
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
  
}