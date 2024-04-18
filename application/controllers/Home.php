<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	}

	public function callHome($useremail="", $userid="")
	{
		$useremail=urldecode($useremail);
		$DefaultDatabaseID = 0;
		$isUserPabrik = false;

		if (isset($_SESSION["user_pabrik"])) {
			$isUserPabrik = $_SESSION["user_pabrik"];
		}

		if ($isUserPabrik==false) {		
			$res = $this->UserModel->getUserDataByEmail($useremail);

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
		     		$BranchID = $Employee->branch_id;
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

					$db = $this->MasterDbModel->get($_SESSION["databaseID"]);
					$_SESSION['conn'] = $db;

			        redirect("Home");			
				} else {
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

				$db = $this->MasterDbModel->get($_SESSION["databaseID"]);
				$_SESSION['conn'] = $db;

		        redirect("Home");
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

			$db = $this->MasterDbModel->get(0);
			$_SESSION['conn'] = $db;

	        redirect("Home");			
		}
	}

	public function index()
	{
		//die(json_encode($_SESSION));

		if (isset($_SESSION["logged_in"])==false) {
			$this->logout();
		}

		$user = $_SESSION["logged_in"];
       	$temp = $this->UserModel->GetRoleByEmail($user['useremail']);

       	$role = array();
       	for($i=0;$i<count($temp);$i++){
       		array_push($role, $temp[$i]->role_id);
       	}
       	if ($user["isSalesman"]==1) {
       		array_push($role, "ROLE15");
       	}
       	if ($user["isUserPabrik"]==1) {
       		$role = array();
       		array_push($role, "ROLE11");
       		if (strtoupper($user['useremail'])=="QR@PABRIK.PTRI" || strtoupper($user['useremail'])=="QR@PABRIK.KG" || strtoupper($user['useremail'])=="QR@PABRIK.TIN") {
       			array_push($role, "ROLE18");
       		}
       	}
       	$_SESSION['role'] = $role;

       	// die(json_encode($_SESSION['role']));
		$mod = $this->ModuleModel->getListByRole($_SESSION['role']);
		// die(json_encode($mod));
       	$_SESSION['module'] = $mod;

       	if (!isset($_SESSION["logged_in"]["isUserPabrik"])) {
       		$_SESSION["logged_in"]["isUserPabrik"] = 0;
       	}

       	// die(json_encode($_SESSION));
		if(isset($_SESSION['flagL']) and $_SESSION['flagL']==1){
			//die((string)$user["databaseid"]);
   			if ($_SESSION["databaseID"]==0 && $_SESSION["logged_in"]["isUserPabrik"]==0) {
				redirect("ConnectDb");
			} else {
				redirect("Dashboard");
			}
		} else {
			//die("no flagL");
			redirect("Home/logout");
       	}
	}

	public function logout()
	{
		session_destroy();
		redirect("HomeController");

	}

	public function changeDatabase(){
		unset($_SESSION['conn']);
		redirect("Home");
	}
}