<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class HomeController extends NS_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function index($err='')
	{
		$data['err'] = $err;
		$this->load->view('home',$data);
	}

	public function setSession($useremail="", $userid="", $username="")
	{
		$useremail=urldecode($useremail);
		$DefaultDatabaseID = 0;
		$isUserPabrik = false;

		if (isset($_SESSION["user_pabrik"])) {
			$isUserPabrik = $_SESSION["user_pabrik"];
		}

		$_SESSION["logged_in"]["userid"] = urldecode($userid);
		$_SESSION["logged_in"]["username"] = urldecode($username);
		$_SESSION["logged_in"]["useremail"] = urldecode($useremail);
		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LOGIN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." LOGIN TO MYCOMPANY";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);

		if ($isUserPabrik==false) {		
			$res = $this->UserModel->getUserDataByEmail($useremail);
			// die(json_encode($res));
			$userid=urldecode($userid);
			if ($userid!=0) {
				$res = $this->UserModel->Get($userid);
				if ($res!=null) {
					$Employee = $res;

			     	$sess_array = array(
						'userid' => $Employee->USERID,
						'username' => $Employee->UserName,
						'useremail' => $Employee->UserEmail,
						'branch_id' => $Employee->branch_id,
						'branch' => $Employee->BranchName,
						'city' => $Employee->City,
						'loginPayroll' => 0,
						'employeeid'=> $userid,
						'email' => $Employee->Email,
						'whatsapp' => (($Employee->Whatsapp==NULL || $Employee->Whatsapp=="")? (($Employee->Mobile==NULL)? "":$Employee->Mobile) : $Employee->Whatsapp),
						'isUserPabrik'=>0,
						'isSalesman' => (($Employee->SalesmanID!="" && $Employee->SalesmanID!=null)? 1:0),
						'salesmanid' => $Employee->SalesmanID,
						'userPosition' => $Employee->EmpPositionName,
						'userDivision' => $Employee->DivisionName,
						'userLevel'  => $Employee->EmpLevel,
						'userGroupId'=> $Employee->GroupID,
						'userGroup'  => $Employee->GroupName
   			       	);

			     	$BranchID = "";
			     	$_SESSION["branchID"] = $BranchID;

		     		$DefaultDatabaseID = 0;
			     	if ($Employee->branch_id!="") {
			     		$DB = $this->MasterDbModel->getByBranchId($Employee->branch_id);
			     		if ($DB != null) {
				     		$DefaultDatabaseID = $DB->DatabaseId;
				     	}
			     	}

			       	$_SESSION['databaseID'] = $DefaultDatabaseID;
			       	$_SESSION['flagL'] = 1;		// session utk cek ketika logout redirect ke halaman login loginApp
			        $_SESSION['logged_in'] = $sess_array;
			        $_SESSION["user_role"] = array();
			        
					$db = $this->MasterDbModel->get($_SESSION["databaseID"]);
					$_SESSION['conn'] = $db;
					// die("here");

					$params['Remarks']="SUCCESS";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

					if (WEBTITLE=="REPORT BHAKTI") {
				        redirect("Dashboard");			
					} else {
				        redirect("HomeController");			
					}
				} else {
					$params['Remarks']="FAILED - Data Tidak Ditemukan";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

					$this->logout();
				}
			} else {
				$sess_array = array(
					'username' => $useremail,
					'useremail' => $useremail,
					'userid' => 0,
					'branch_id' => 'JKT',
					'city' => 'PABRIK',
					'loginPayroll' => 0,
					'employeeid'=> $userid,
					'email' => $useremail,
					'whatsapp' => '',
					'isUserPabrik'=> 0,
					'isSalesman' => 0,
					'salesmanid' => '',
					'userPosition' => "",
					'userDivision' => "",
					'userLevel'  => "",
					'userGroupId'=> "",
					'userGroup'  => ""
		       	);

		       	$_SESSION['databaseID'] = (($res->DefaultDatabaseId==null) ? 0 : $res->DefaultDatabaseId);
		       	$_SESSION['flagL'] = 1;		// session utk cek ketika logout redirect ke halaman login loginApp
		        $_SESSION['logged_in'] = $sess_array;
		        $_SESSION["user_role"] = array();
			        
				$db = $this->MasterDbModel->get($_SESSION["databaseID"]);
				$_SESSION['conn'] = $db;

				$employee = $this->UserModel->getUserDataByEmail($useremail);
				if($employee->setpass == 1){
					redirect('resetpassword/setnewpass','refresh');
				} else {
					$params['Remarks']="SUCCESS";
					$this->ActivityLogModel->update_activity($params);

			        redirect("HomeController");
			    }
			}
		} else {

			//die(json_encode($_SESSION["user_role"]));
	        $array = explode(".", $useremail);
	        $GroupId = "PABRIK";
	        if (count($array)>0) {
	          $last = count($array)-1;
	          $GroupId = $array[$last];
	        }

			$sess_array = array(
				'username' => $useremail,
				'useremail' => $useremail,
				'userid' => 0,
				'branch_id' => 'JKT',
				'city' => 'PABRIK',
				'loginPayroll' => 0,
				'employeeid'=> $userid,
				'email' => $useremail,
				'whatsapp' => '',
				'isUserPabrik'=>1,
				'isSalesman' => 0,
				'salesmanid' => "",
				'userPosition' => "",
				'userDivision' => "",
				'userLevel'  => "PABRIK",
				'userGroupId'=> $GroupId,
				'userGroup'  => ""
   	       	);

			$_SESSION["branchID"] = "JKT";
	       	$_SESSION['databaseID'] = 0;
	       	$_SESSION['flagL'] = 1;		// session utk cek ketika logout redirect ke halaman login loginApp
	        $_SESSION['logged_in'] = $sess_array;
	        $_SESSION["user_role"] = array();
			        
			$db = $this->MasterDbModel->get(0);
			$_SESSION['conn'] = $db;

			$params['Remarks']="SUCCESS";
			$this->ActivityLogModel->update_activity($params);

	        redirect("Home");			
		}
	}

	public function logout()
	{
		$this->session->sess_destroy();
		if(session_status() === PHP_SESSION_ACTIVE) {
			$this->session->sess_destroy();
		}
		redirect('MainController','refresh');
	}
 
	public function changePassword(){
		$params = array();

		$username = $_SESSION['logged_in']['useremail'];
   		$oldpassword = $this->input->post('txtOldPassword');
   		$newpassword = $this->input->post('txtNewPassword');

   		$this->load->model('ActivityLogModel');
		$params = array();			
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['LogDate'] =date("Y-m-d H:i:s");
		$params['Module']="PROFILE";
		$params['TrxID'] = $username;
		$params['Description']=$_SESSION["logged_in"]["username"]." CHANGE PASSWORD";
		$params['Remarks']="";
		$params['RemarksDate']=null;
		$this->ActivityLogModel->insert_activity($params);

   		$result = $this->UserModel->login($username, $oldpassword);

   		if($result['result']=='success'){
			$params['Remarks'] = "SUCCESS";
			$params['RemarksDate']= date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

   			$this->UserModel->changePassword($username, $newpassword);
   			$this->session->unset_userdata('logged_in');
   			$this->session->set_flashdata('info','Please relogin using the new password.');
		    redirect('MainController');
   		} else {
			$params['Remarks'] = "FAILED : password lama invalid";
			$params['RemarksDate']= date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

		    $this->index('error Password');
   		}
	}
}

