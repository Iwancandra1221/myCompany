<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class UserControllers extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->helper('FormLibrary');
		$this->load->model('GzipDecodeModel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		$this->API_ZEN = $this->ConfigSysModel->Get()->zenhrs_url;
	}

	public function index()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "USER";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU USER ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));

		$post = $this->PopulatePost();		
		$data = array();
		$orderArray = $this->input->post('order');


		// set_time_limit(60);
		$data = array();

		if(!isset($_SESSION["branch_ID"])) {
			$_SESSION["branch_ID"] = $_SESSION["branchID"];
		}
		if(!isset($_SESSION["is_active"])) {
			$_SESSION["is_active"] = 1;
		}
		if(!isset($_SESSION["is_non_active"])) {
			$_SESSION["is_non_active"] = 0;
		} 

		$data["branches"] = null;
		$getBranch = json_decode(file_get_contents($this->API_URL.'/Cabang/GetMstCabang?api=APITES'), true);

		if ($getBranch !== null) {
		    if (isset($getBranch['data'])) {
		        $data["branches"] = $getBranch['data'];
		    } 
		}  	

		// $data["ListBranches"] = $this->BranchModel->GetList(); 

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView('UserListView',$data);

		
	}

	public function loadSalesman()
	{
			$url = $this->API_URL."/MsSalesman/GetSalesmanListV2";
            //die($url);
            // open connection
            $curl = curl_init();
            // set the url, number of POST vars, POST data
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_POST => 1,
                CURLOPT_HTTPHEADER => array("Content-type: application/json")
            ));
            // execute post
            $response = curl_exec($curl);
            $err = curl_error($curl);
            // close connection
            curl_close($curl);
 	
 			$statusUpdate = true;

			$hasil = $this->GzipDecodeModel->_decodeGzip($response);
			if ($hasil->result == "sukses") {  
				$stats = $this->UserModel->UpdateSalesmanID($hasil->data); 
				if ($stats["result"] == "SUCCESS")
				{ 
					echo json_encode("Succes Update Salesman"); 
				}
				else
				{
					echo json_encode($stats["errMsg"]); 
				}
			} else { 
				echo json_encode("List Salesman Not Found"); 
			} 
	}

	function data_user(){

		$hasildata = $this->UserModel->getuserlist($this->input->get());

		$data_list=array();
		$data_hasil=array();
		$total=0;
		

		if(!empty($hasildata['data'])){
			foreach ($hasildata['data'] as $key => $r) {
				$action='';

				$list=array();

				$list[] 	= $r->UserID;
				$list[] 	= $r->UserName.'<br>'.$r->UserEmail;
				$list[] 	= $r->EmpPositionName;
				$list[] 	= $r->BranchName;
				$list[] 	= (($r->IsActive==1) ? "AKTIF" : "TIDAK AKTIF");
				// $list[] 	= $r->GroupName;
				// $list[] 	= $r->EmpPositionName;

				if($_SESSION["can_read"] == 1) {
					// if ($r->UserID!=0) {
					// 	$action .= "<a href = '".site_url('UserControllers/View/'.$r->UserID)."'><i class='glyphicon glyphicon-search'></i></a>";
					// } else {
					// 	$action .= "<a href = '".site_url('UserControllers/View2/'.urlencode($r->UserEmail))."'><i class='glyphicon glyphicon-search'></i></a>";
					// }
					$action .= "<a href = '".site_url('UserControllers/View2/'.urlencode($r->UserEmail))."'><i class='glyphicon glyphicon-search'></i></a>";
				}
				if($_SESSION["can_update"] == 1) {
					// if ($r->UserID!=0) {
					// 	$action .= "<a href = '".site_url('UserControllers/View/'.$r->UserID)."/1/1'><i class='glyphicon glyphicon-edit hijau'></i></a>";
					// } else {
					// 	$action .= "<a href = '".site_url('UserControllers/View2/'.urlencode($r->UserEmail))."/1/1'><i class='glyphicon glyphicon-edit hijau'></i></a>";              
					// }
					$action .= "<a href = '".site_url('UserControllers/View2/'.urlencode($r->UserEmail))."/1/1'><i class='glyphicon glyphicon-edit hijau'></i></a>";              
				}
				if($_SESSION["can_delete"] == 1) {
					// if ($r->UserID!=0) {
					// 	$action .= "<a href = '".site_url('UserControllers/Disable/'.$r->UserID)."' data-toggle='modal' data-target='#confirm-delete' data-record-title='".$r->UserName."'><i class='glyphicon glyphicon-trash merah'></i></a>";
					// } else {
					// 	$action .= "<a href = '".site_url('UserControllers/Disable2/'.$r->UserEmail)."' data-toggle='modal' data-target='#confirm-delete' data-record-title='".$r->UserName."'><i class='glyphicon glyphicon-trash merah'></i></a>";              
					// }
					// $action .= "<a href = '".site_url('UserControllers/Disable2/'.$r->UserEmail)."' data-toggle='modal' data-target='#confirm-delete' data-record-title='".$r->UserName."'><i class='glyphicon glyphicon-trash merah'></i></a>";              
				}

				$mobile = '<span style="display:none;" class="item-mobile">'.$r->UserName."<br>".$r->UserID."<br>".$r->EmpPositionName."<br>".$r->BranchName."<br>".(($r->IsActive==1) ? "AKTIF" : "TIDAK AKTIF")."<br></span>".$action;


				$list[] 	= $mobile;

				$data_list[]=$list;
			}

			$total=$hasildata['total'];

		}

		if(!empty($this->input->get('sEcho'))){
			$secho = $this->input->get('sEcho');
		}else{
			$secho = 1;
		}

		$data_hasil['sEcho']=$secho;
		$data_hasil['iTotalRecords']=$total;
		$data_hasil['iTotalDisplayRecords']=$total;
		$data_hasil['aaData']=$data_list;

		print_r(json_encode($data_hasil));
	}

	function View($USERID='', $EDIT=0)
	{	
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "USER";
		$params['TrxID'] = $USERID;
		$params['Description'] = $_SESSION["logged_in"]["username"]." VIEW USER ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$USER = $this->UserModel->Get($USERID);
		// die(json_encode($USER));
		$this->ViewUser($USER, $EDIT, $params);
	}

	function View2($USEREMAIL='', $EDIT=0)
	{		
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "USER";
		$params['TrxID'] = $USEREMAIL;
		$params['Description'] = $_SESSION["logged_in"]["username"]." VIEW2 USER ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$USEREMAIL = urldecode($USEREMAIL);
		$USER = $this->UserModel->getUserDataByEmail($USEREMAIL);
		// die(json_encode($USER));
		$this->ViewUser($USER, $EDIT, $params);
	}

	function ViewUser($USER=null, $EDIT=0, $params=array())
	{
		$isGetUser = (int) $this->input->post('is_get_user');
		$USERID = "";

		if($isGetUser==0){
			// die(json_encode($USER));
			if ($USER!=null) {
				$USERID = $USER->USERID;
				$USEREMAIL = $USER->UserEmail;

				$post = $this->PopulatePost();				
				$data = array();
				$roles = array();

				$data["groups"] = $this->BranchModel->GetListGroup();
				$data["dataUpdated"] = false;
				$data["loginChanged"] = false;
				$data["oldLogin"] = $USEREMAIL;
				$data["newLogin"] = $USEREMAIL;
				// die(json_encode($data));

				if ($USER==null) {
					$data["result"] = "gagal";
					$data["user"] = null;
					$data["error"] = "USER TIDAK DITEMUKAN";

					// ActivityLog Update FAILED
					$params['Remarks']="FAILED - Data Tidak Ditemukan";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

				} else {
					$data["result"] = "sukses";
					$data["user"] = $USER;
					$data["error"] = "";

					if ($USERID==0) {
						//die("here");
						$USERID = $this->UserModel->UpdateUserIDv2($USEREMAIL);
						$USER->USERID = $USERID;
					}

				}

				if ($USERID>0) {
					$data["userRole"] = $this->UserModel->GetRoleByID($USERID);
				} else {
					$data["userRole"] = $this->UserModel->getRoleUser($USEREMAIL);
				}

				// kalo GroupID di $data datalokal kosong baru ambil ke $data user
				if ($USER->GroupID == ""){
					$branch_id=$USER->branch_id;
				} else {
					$GROUP = $this->BranchModel->GetGroup($USER->GroupID);
					$branch_id=$GROUP->BranchID;
				}
						
				if ($branch_id == "JKT") {
					$mainbranch_role=1;
				} else {
					$mainbranch_role=0;
				}
				//die(json_encode($USER));
				// print_r($data['user']);
				// die;
				
		        $employees = array();
				$ListEmployee = array();
				
				$zen = "";

				
				$user_role = $this->RoleModel->getRoleId($_SESSION["logged_in"]["userid"]);

				$data["ListEmployee"] = $ListEmployee;
		        $data["employees"] = $employees; 
				$data["roles"] = $this->RoleModel->getListbyMainBranchwithRole($mainbranch_role,$user_role);
				$data["edit_mode"] = $EDIT;
				
				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				$this->RenderView('UserEdit',$data);
			} else {
				// 
			}
		}
		else{
			//validasi user
			$USERID = (int) $this->input->post('zen_UserID');
			$data = array(
				'code' => 1,
				'msg' => '',
				'data' => array(),
			);
			$msg = "data tidak ditemukan";
			if($USERID>0){
				$zen = $this->API_ZEN."/ZenAPI/GetEmployee?userid=".urlencode($USERID);
				// die($zen);

				$streamContext = stream_context_create(
					array('http'=>
						array(
							'timeout' => 90,  //600 seconds = 10 menit
						)
					)
				);
		        $GetEmployee = json_decode(file_get_contents($zen, false, $streamContext),true);
		        if ($GetEmployee===false) {
					// ActivityLog Update FAILED
					$params['Remarks']="FAILED - Data Tidak Ditemukan";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

		        } else {
		        	//die(json_encode($GetEmployee));

		        	$data['code'] = 1;
		        	/*
		        	$GetEmployee['data'] output:
		        	{"code":1,"msg":"","data":{"USERID":543,"KTP":"7371115307830004","APPLICANTID":"DATA_AWAL_APPLICANT_543","SALESMANID":null,"REFID":null,"USEREMAIL":"a.afni.fitria@office.mks","ISACTIVE":1,"NIK":null,"NAME":"A. AFNI FITRIANI","ALIASNAME":"FITRI","BADGENUMBER":"50406","HIREDDATE":"2007-05-09","PENGANGKATAN":"2016-01-02","PROMOTIONDATE":null,"ENDDATE":null,"GROUPID":"CMKS3","DIVISIONID":"2.3.13.01.01","EMPTYPEID":"EL02","EMPLEVELID":"EML01","EMPPOSITIONID":"JT0187","BIRTHDATE":"1990-01-01","GENDER":"FEMALE","EMAIL":"afnifitriani@gmail.com","MOBILE":"082188340030","WHATSAPP":"082188340030","DATABASEID":"","BRANCHID":"MKS","BRANCHNAME":"MAKASSAR","EMPLEVEL":"STAFF","CITY":"MAKASSAR","GROUPNAME":"Makassar Logistic","EMPTYPE":"TETAP","DIVISIONNAME":"MKS GUDANG PRODUK","EMPLEVELNUMBER":1,"EMPPOSITIONNAME":"Adm Listing","BANKNAME":"BCA","BANKACCOUNTNUMBER":"7990135775","SALARYEMAIL":"afnifitriani@gmail.com"}}
		        	*/
		        	$data['data'] = array(
		        		'USERID' => $GetEmployee['data']['USERID'],
		        		'USEREMAIL' => $GetEmployee['data']['USEREMAIL'],
		        		'NAME' => $GetEmployee['data']['NAME'],
		        		'BRANCHNAME' => $GetEmployee['data']['BRANCHNAME'],
		        		'GROUPNAME' => $GetEmployee['data']['GROUPNAME'],
		        		'EMPLEVEL' => $GetEmployee['data']['EMPLEVEL'],
		        		'EMPTYPE' => $GetEmployee['data']['EMPTYPE'],
		        		//'EMPPOSITIONNAME' => $GetEmployee['data']['EMPPOSITIONNAME'],
		        	);
		        	$msg = "";

					// ActivityLog Update SUCCESS
					$params['Remarks']="SUCCESS";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$params["LogDate"] = date("Y-m-d H:i:s");
					$params['UserID'] = $_SESSION["logged_in"]["userid"];
					$params['UserName'] = $_SESSION["logged_in"]["username"];
					$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
					$params['Module'] = "USER";
					$params['TrxID'] = $USERID;
					$params['Description'] = $_SESSION["logged_in"]["username"]." TARIK USER ZEN UNTUK USERID ".$USERID;
					$this->ActivityLogModel->update_activity($params);

		        }
			}
			
			$data['msg'] = $msg;
			$json =  json_encode($data);
			echo $json;
		}
		
	}

	function ChangeProfile()
	{
		//die(json_encode($_SESSION["logged_in"]["useremail"]));
		$USER = $this->UserModel->GetUserDataByEmail($_SESSION["logged_in"]["useremail"]);
		//die(json_encode($USER));
		$data=array();
		$data["user"] = $USER;
		$this->RenderView('UserEditProfile',$data);
	}

	function ChangePassword()
	{
		//die(json_encode($_SESSION["logged_in"]["useremail"]));
		$USER = $this->UserModel->GetUserDataByEmail($_SESSION["logged_in"]["useremail"]);
		//die(json_encode($USER));
		$data=array();
		$data["user"] = $USER;
		$this->RenderView('UserChangePassword',$data);
	}

	function SaveProfile()
	{	
		$post = $this->PopulatePost();
		$this->UserModel->SaveProfile($post);
        $this->session->set_flashdata('info','Data Berhasil Diubah');

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "USER";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." USER SAVE PROFILE";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		redirect("UserControllers/ChangeProfile");
	}

	function SetUserid()
	{
		$users = array();
		$result = "";

		$URL = $this->API_ZEN."/ZenAPI/GetEmployees";
		$BranchID="";
		$GroupID="";

		$params = array("BranchID"=>$BranchID, "GroupID"=>$GroupID);
		$options = array(
		    'http' => array(
		        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		        'method'  => 'POST',
		        'content' => http_build_query($params)
		    )
		);
		$context  = stream_context_create($options);
		$GetEmployees = json_decode(file_get_contents($URL, false, $context), true);
		if ($GetEmployees === FALSE) {
			echo ("CAN'T GET DATA ZEN");
		} else if ($GetEmployees["result"]=="gagal") {
			echo ("USERS NOT FOUND");
		} else {
			$users = $GetEmployees["data"];
			for ($i=0; $i<count($users); $i++) {
				$USERID = $users[$i]["USERID"];
				$USEREMAIL=$users[$i]["USEREMAIL"];
				$USER = $this->UserModel->getUserDataByUserID($USERID);
				if ($USER==null) {
					$USER = $this->UserModel->getUserDataByUserEmail($USEREMAIL);
					if ($USER!=null) {
						$this->UserModel->UpdateUserID($USEREMAIL, $USERID);
					} else {
						//$add_userid = $this->UserModel->InsertUser($users)
					}
				}

				$USERDT = $this->UserModel->GetRoleByID($USERID);
				if (count($USERDT)==0) {
					$USERDT = $this->UserModel->GetRoleByEmail($USEREMAIL);
					if (count($USERDT)>0) {
						$this->UserModel->UpdateUserIDOnDt($USEREMAIL, $USERID);
					}
				}
			}
		}
	}

	function Disable($USERID)
	{
		$USER = $this->UserModel->Get($USERID);
		if ($USER!=null) {
			if ($this->UserModel->Disable($USER)){
				// ActivityLog
				$params = array();   
				$params['LogDate'] = date("Y-m-d H:i:s");
				$params['UserID'] = $_SESSION["logged_in"]["userid"];
				$params['UserName'] = $_SESSION["logged_in"]["username"];
				$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
				$params['Module'] = "USER";
				$params['TrxID'] = $USER;
				$params['Description'] = $_SESSION["logged_in"]["username"]." USER DISABLE";
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->insert_activity($params);

				redirect("UserControllers");
			}			
		}
	}

	function Disable2($USEREMAIL)
	{
		
		$USER = $this->UserModel->getUserDataByEmail($USEREMAIL);
		if ($USER!=null) {
			if ($this->UserModel->Disable($USER)){
				// ActivityLog
				$params = array();   
				$params['LogDate'] = date("Y-m-d H:i:s");
				$params['UserID'] = $_SESSION["logged_in"]["userid"];
				$params['UserName'] = $_SESSION["logged_in"]["username"];
				$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
				$params['Module'] = "USER";
				$params['TrxID'] = $USER;
				$params['Description'] = $_SESSION["logged_in"]["username"]." USER DISABLE";
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->insert_activity($params);

				redirect("UserControllers");
			}			
		}
	}

	function Edit()
	{
		$streamContext = stream_context_create(
			array('http'=>
				array(
					'timeout' => 60,  //60 seconds = 1 menit
				)
			)
		);
		
		$post = $this->PopulatePost();
		$post["BranchID"] = "";
		$post["BranchName"] = "";
		$post["GroupName"] = "";

		if (!isset($post["IsActive"]) || $post["IsActive"] === "") {
		    $post["IsActive"] = 0;
		}

		//die(json_encode($post));

		//JIKA ADA PILIH USERID ZEN
		if (ISSET($post["zen_status"]) && $post["zen_UserID"]!="") {
			$UserID = $this->UserModel->CekUserID($post["zen_UserID"], $post["UserEmail"] );
			// die(json_encode($AlternateID));

			if(ISSET($UserID)){ //JIKA USERID SUDAH PERNAH DIPAKAI
				$this->session->set_flashdata('error', 'USERID sudah pernah dipakai');
				$this->session->flashdata('error');
				redirect("UserControllers/View2/".urlencode($post["UserEmail"])."/1");
			}
			//AMBIL DATA ZEN
			$zen = $this->API_ZEN."/ZenAPI/GetEmployee?userid=".urlencode($post["zen_UserID"]);
			$GetEmployee = json_decode(file_get_contents($zen, false, $streamContext));
			// die(json_encode($GetEmployee));
			if ($GetEmployee===false) {
				// die("here");
			} else {
				// die("not here");
				$post["Zen"] = $GetEmployee->data;
			}
		}
		// die("x");

		// AMBIL DATA GROUP JIKA USER ADA ISI GROUP
		if ($post["GroupID"]!="") {
			// $URL = API_ZEN."/ZenAPI/GetGroup?group=".urlencode($post["GroupID"]);
			// $GetGroup = json_decode(file_get_contents($URL), true);
			$Branch = $this->UserModel->GetBranch($post["GroupID"]);
			$post["BranchID"] = $Branch->BranchID;
			$post["BranchName"] = $Branch->BranchName;
			$post["GroupName"] = $Branch->GroupName;
		}
		
		// echo json_encode($post);
		// die;
		
		if ($this->UserModel->EditUser($post)){
			if ($post["zen_UserID"]=="0" || $post["zen_UserID"]=="") {
				redirect("UserControllers/View2/".urlencode($post["UserEmail"]));
			} else {
				$userIdTmp = ($post["zen_UserID"] !='' ? $post["zen_UserID"] : $post["UserID"]);
				redirect("UserControllers/View/".$userIdTmp);
			}
		} else {
			if ($post["zen_UserID"]=="0" || $post["zen_UserID"]=="") {
				redirect("UserControllers/View2/".urlencode($post["UserEmail"])."/1");
			} else {
				redirect("UserControllers/View/".$post["UserID"]."/1");
			}
		}
	}

	public function CheckZen($empId=0)
	{
		//include_once(__DIR__.'/../includes/CheckModule.php');			
		$page_title = $this->CheckModuleModel->CheckAccessView();
		$data = array();
		$data['title'] = $page_title;
		$data["USERID"] = $empId;
		$userid = "";
		
		$EMP = $this->EmployeeModel->GetByUserID($empId);
		if ($EMP!=null) {
			$userid = $EMP->UserEmail;			
		}
		$USER = $this->UserModel->GetMD5(md5($userid));

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ROLE";
		$params['TrxID'] = $USER;
		$params['Description'] = $_SESSION["logged_in"]["username"]." CHECKZEN USER ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$data['USER'] = $USER;
		$data['UserRoles'] = $this->UserModel->GetUserRole($userid);
		$data['UserDivisions'] = $this->UserModel->GetUserDivisionCuti($userid);

		$data["EMP"] = $EMP;
		$data["Registrations"] = $this->EmployeeModel->GetRegistration($empId);
		$data["Recruitments"] = $this->EmployeeModel->GetRecruitment($empId);
		$data["Promotions"] = $this->EmployeeModel->GetPromotion($empId);
		$data["Mutations"] = $this->EmployeeModel->GetMutation($empId);

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView('vUserDetailLacak',$data);
	}

	public function UpdateFromZen()
	{
		$post = $this->PopulatePost();
		$UserID = $post["UserID"];

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ROLE";
		$params['TrxID'] = $UserID;
		$params['Description'] = $_SESSION["logged_in"]["username"]." CHECKZEN USER ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		//include_once(__DIR__.'/../includes/CheckModule.php');			
		$this->CheckModuleModel->CheckAccessView();
		$data = array();
		$GetZen = json_decode(file_get_contents(ZEN_API."/ZenAPI/GetEmployee?userid=".$UserID), true);
		//die(json_encode($GetZen));
		if ($GetZen["result"]=="sukses") {
			$EmployeeMC = $this->EmployeeModel->GetByUserID($UserID);
			//$GetEmployeeZen = json_decode(file_get_contents(ZEN_API."/ZenAPI/GetTrans?poolid=0&tbl=USERINFO&key=USERID&trx=".$UserID."&key2=&trx2="), true);
			if ($EmployeeMC==null) {
				$this->EmployeeModel->InsertTrans("USERINFO", "USERID", $UserID, $GetZen["data"]);
			} else {
				$this->EmployeeModel->UpdateTrans("USERINFO", "USERID", $UserID, $GetZen["data"]);
			}

			$Registrations = $GetZen["registration"];
			for($i=0;$i<count($Registrations);$i++) {
				$HistoryID = $Registrations[$i]["HistoryID"];
				$RegistrationMC = $this->EmployeeModel->GetRegistration2($HistoryID);
				if ($RegistrationMC==null) {
					$this->EmployeeModel->InsertTrans("Trans_Registration_History", "HistoryID", $HistoryID, $Registrations[$i]);
				} else {
					$this->EmployeeModel->UpdateTrans("Trans_Registration_History", "HistoryID", $HistoryID, $Registrations[$i]);
				}
			}

			$Recruitments = $GetZen["recruitment"];
			//die(json_encode($Recruitments));
			for($i=0;$i<count($Recruitments);$i++) {
				$HistoryID = $Recruitments[$i]["HistoryID"];
				$RecruitmentMC = $this->EmployeeModel->GetRecruitment2($HistoryID);
				if ($RecruitmentMC==null) {
					//die("Recruitment Not Exists");
					$this->EmployeeModel->InsertTrans("Trans_Recruitment_History", "HistoryID", $HistoryID, $Recruitments[$i]);
				} else {
					//die(json_encode($Recruitments[$i]));
					//die("Recruitment Exists");
					$this->EmployeeModel->UpdateTrans("Trans_Recruitment_History", "HistoryID", $HistoryID, $Recruitments[$i]);
				}				
			}

			$Promotions = $GetZen["promotion"];
			for($i=0;$i<count($Promotions);$i++) {
				$HistoryID = $Promotions[$i]["HistoryID"];
				$PromotionMC = $this->EmployeeModel->GetPromotion2($HistoryID);
				if ($PromotionMC==null) {
					$this->EmployeeModel->InsertTrans("Trans_Promotion_History", "HistoryID", $HistoryID, $Promotions[$i]);
				} else {
					$this->EmployeeModel->UpdateTrans("Trans_Promotion_History", "HistoryID", $HistoryID, $Promotions[$i]);
				}				
			}

			$Mutations = $GetZen["mutation"];
			for($i=0;$i<count($Mutations);$i++) {
				$HistoryID = $Mutations[$i]["HistoryID"];
				$MutationMC = $this->EmployeeModel->GetMutation2($HistoryID);
				if ($MutationMC==null) {
					$this->EmployeeModel->InsertTrans("Trans_Mutation_History", "HistoryID", $HistoryID, $Mutations[$i]);
				} else {
					$this->EmployeeModel->UpdateTrans("Trans_Mutation_History", "HistoryID", $HistoryID, $Mutations[$i]);
				}				
			}

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			echo json_encode(array("result"=>"sukses","error"=>""));
			//redirect("UserControllers/CheckZen/".$UserID);
		} else {
			// ActivityLog Update FAILED
			$params['Remarks']="FAILED - Data Tidak Ditemukan";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			echo json_encode(array("result"=>"gagal","error"=>"Ambil data dari zen.bhakti.co.id Gagal atau Data tidak Ada"));
		}
	}	

	public function ZenUpdate()
	{
		$userid = $this->input->get("userid");
		$user = $this->UserModel->Get($userid);

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ROLE";
		$params['TrxID'] = $userid;
		$params['Description'] = $_SESSION["logged_in"]["username"]." ZENUPDATE USER ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		if ($user!=null) {
			if ($this->UserModel->UpdateColumn($userid, "needSync", "1")) {
				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				echo "SUCCESS";
			} else {
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				echo "FAILED";
			}
		}
	}
}