<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->helper("FormLibrary");
	}

	public function index()
	{ 
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ROLE";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU ROLE ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


        $data['result'] = $this->RoleModel->getList();
        $data['rolecode'] =$this->RoleModel->getRoleAutoNumber(); 
        $data['user_role'] =$this->RoleModel->getRoleId($_SESSION["logged_in"]["userid"]);  

		$checkAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		//die(json_encode($checkAccess));

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView('RoleView',$data);
		
	}


	public function getList()
	{
        $data['result'] = $this->MasterDbModel->getList($_SESSION['logged_in']['branch_id']); 
		echo json_encode($data['result']);
	}

	public function get($koderole)
	{
        $data['result'] = $this->MasterDbModel->getList($_SESSION['logged_in']['branch_id']); 
		echo json_encode($data['result']);
	}

	public function getRoleAllUser(){
		$post = $this->PopulatePost();
		$koderole = $post['akdrole']; 
		$modules = array();
		$parents = $this->UserModel->getAlluserList($koderole);
		foreach($parents as $p) {
			array_push($modules, array("user_id"=>$p->user_id, "user_name"=>$p->user_name)); 
		} 
		echo json_encode($modules);
	}

	public function getRoleAllUser2(){ 
		$search = $this->input->get('q'); 
		$koderole = ""; 
		 
		$data = array(
			'data' => array(),
		);

		$parents = $this->UserModel->getAlluserList($koderole,$search);
		foreach($parents as $p) {
			$data['data'][] = array(
					'id' => rtrim($p->user_id ," "),
					'text' => rtrim($p->user_name ," "),
				);
		}
		$json = json_encode($data);
		echo $json;
	}
 

	public function getRoleUser(){
		$post = $this->PopulatePost();
		$koderole = $post['akdrole'];

		$modules = array();
		$parents = $this->UserModel->getuserListByRole($koderole);
		foreach($parents as $p) {
			array_push($modules, array("user_id"=>$p->user_id, "user_name"=>$p->user_name, 
				"group_name"=>$p->group_name)); 
		}
		 
		echo json_encode($modules);
	}


	public function getRoleModule(){
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ROLE";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." GET ROLE MODEULE";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$post = $this->PopulatePost();
		$koderole = $post['akdrole'];

		$modules = array();
		$listmodules = $this->RoleModel->getModuleListByRoleId($koderole);

		// print_r($listmodules);
		// die;
		// foreach($parents as $p) {
		// 	array_push($modules, array("module_id"=>$p->module_id, "module_name"=>$p->module_name, "module_type"=>$p->module_type,
		// 		"position"=>$p->position, "controller"=>$p->controller, "description"=>$p->description,
		// 		"parent_module_id"=>$p->parent_module_id, "is_active"=>$p->is_active));
		// 	$children = $this->ModuleModel->GetModuleList("CHILD", $p->module_id);
		// 	foreach($children as $c) {
		// 		array_push($modules, array("module_id"=>$c->module_id, "module_name"=>$c->module_name, "module_type"=>$c->module_type,
		// 			"position"=>$c->position, "controller"=>$c->controller, "description"=>$c->description,
		// 			"parent_module_id"=>$c->parent_module_id, "is_active"=>$c->is_active));
		// 		$grandchildren = $this->ModuleModel->GetModuleList("GRANDCHILD", $c->module_id);
		// 		foreach($grandchildren as $g) {
		// 			array_push($modules, array("module_id"=>$g->module_id, "module_name"=>$g->module_name, "module_type"=>$g->module_type,
		// 				"position"=>$g->position, "controller"=>$g->controller, "description"=>$g->description,
		// 				"parent_module_id"=>$g->parent_module_id, "is_active"=>$g->is_active));
		// 			$greatgrandchildren = $this->ModuleModel->GetModuleList("GREAT-GRANDCHILD", $g->module_id);
		// 			foreach($greatgrandchildren as $gc) {
		// 				array_push($modules, array("module_id"=>$gc->module_id, "module_name"=>$gc->module_name, "module_type"=>$gc->module_type,
		// 					"position"=>$gc->position, "controller"=>$gc->controller, "description"=>$gc->description,
		// 					"parent_module_id"=>$gc->parent_module_id, "is_active"=>$gc->is_active));
		// 			}
		// 		}
		// 	}
		// }
		// for($i=0;$i<count($modules);$i++) {
        // 	$res = $this->RoleModel->getModuleDetail($koderole, $modules[$i]["module_id"]);
        // 	if($res){
		// 	    $modules[$i]["can_read"] = $res->can_read;
		// 	    $modules[$i]["can_create"] = $res->can_create;
		// 	    $modules[$i]["can_update"] = $res->can_update;
		// 	    $modules[$i]["can_delete"] = $res->can_delete;
		// 	    $modules[$i]["can_print"] = $res->can_print;
		// 	}			
		// }

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		echo json_encode($listmodules);
	}


	public function insert()
	{
		$koderole = $this->input->post('txtKodeRole');
		$namarole = $this->input->post('txtNamaRole');

		// if(isset($_POST['chkDefault'])){
		// 	$is_default = $this->input->post('chkDefault');
		// }
		// else{
		// 	$is_default = 0;
		// }

		$is_default = 0;

		//if(isset($_POST['chkHRD'])){
		//	$is_hrd = $this->input->post('chkHRD');
		//}
		//else{
		//	$is_hrd = 0;
		//}

		// if(isset($_POST['chkNotif'])){
		// 	$is_notif = $this->input->post('chkNotif');
		// }
		// else{
		// 	$is_notif = 0;
		// }

		$is_notif = 0;
		
		if(isset($_POST['chkMainBranch'])){
			$is_mainbranch = $this->input->post('chkMainBranch');
		}
		else{
			$is_mainbranch = 0;
		}

		//$importance = $this->input->post('selImpor');

		//check data kosong
		if($koderole == '' or $namarole == ''){
			redirect('Role?inserterror=Tidak Boleh Ada Data yang Kosong.');
			exit(1);
		}

		//check kode sudah digunakan / belum
		$temp = $this->RoleModel->get($koderole);
		if($temp){
			redirect('Role?inserterror=Kode Role Sudah Digunakan.');
			exit(1);
		}

		$now = date('Y/m/d h:i:s A');

		$data = array (
            'role_id' => $koderole,
            'role_name'  => $namarole,
            'role_default'=> $is_default,
            //'is_hrd' => $is_hrd,
			'report_cuti_notification' => $is_notif,
			'mainbranch_role' => $is_mainbranch,
            //'role_importance' => $importance,
			'created_by' => $_SESSION['logged_in']['username'],
			'created_date' => $now,
			'updated_by' => $_SESSION['logged_in']['username'],
			'updated_date' => $now
        );
        
        $this->RoleModel->addData($data);

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ROLE";
		$params['TrxID'] = $koderole;
		$params['Description'] = $_SESSION["logged_in"]["username"]." INSERT ROLE ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

        redirect('Role?insertsuccess=1');
    }


	public function duplicaterole()
	{
		$roleid = $this->input->post('txtroleidhidden');
		$koderole = $this->input->post('txtDupKodeRole');
		$namarole = $this->input->post('txtDupNamaRole'); 
		$is_default = 0; 
		$is_notif = 0; 
		if(isset($_POST['chDupMainBranch'])){
			$is_mainbranch = $this->input->post('chDupMainBranch');
		}
		else{
			$is_mainbranch = 0;
		}
  
		//check data kosong
		if($roleid == ''){
			redirect('Role?duplicateerror=Tidak Boleh Ada Data yang Kosong.');
			exit(1);
		}

		//check data kosong
		if($koderole == '' or $namarole == ''){
			redirect('Role?duplicateerror=Tidak Boleh Ada Data yang Kosong.');
			exit(1);
		}

		//check kode sudah digunakan / belum
		$temp = $this->RoleModel->get($koderole);
		if($temp){
			redirect('Role?duplicateerror=Kode Role Sudah Digunakan.');
			exit(1);
		}

		$now = date('Y/m/d h:i:s A');

		// Tambah Role Sesuai Duplicate
		$data = array (
            'role_id' => $koderole,
            'role_name'  => $namarole,
            'role_default'=> $is_default, 
			'report_cuti_notification' => $is_notif,
			'mainbranch_role' => $is_mainbranch, 
			'created_by' => $_SESSION['logged_in']['username'],
			'created_date' => $now,
			'updated_by' => $_SESSION['logged_in']['username'],
			'updated_date' => $now
        ); 
        $this->RoleModel->addData($data); 
		// Tambah Role Sesuai Duplicate


		// Duplicate Semua Module Accesc Sesuai Role Terpilih
		$temp = $this->RoleModel->getModuleDetail2($roleid);

		foreach($temp as $p) { 
			$data = array (
					'module_id' => $p->module_id,
					'role_id' => $koderole,
		            'can_read'  => $p->can_read,
		            'can_create'=> $p->can_create,
		            'can_update' => $p->can_update,
		            'can_delete' => $p->can_delete,
		            'can_print' => $p->can_print,
		        );
			$this->RoleModel->insertModuleDetail($data);  
		}  
		// Duplicate Semua Module Accesc Sesuai Role Terpilih

		// Duplicate Semua User Sesuai Role Terpilih
		//$temp = $this->RoleModel->getAllUserDetail($roleid);

		//foreach($temp as $p) { 
		//	$data = array (	
	    //        'UserEmail' => $p->UserEmail,
	    //        'role_id'  => $koderole,
	    //        'created_by'=> $_SESSION['logged_in']['username'],
		//		'created_date' => $now,  
		//		'UserID' => $p->UserID, 
	    //    ); 
	    //    $this->RoleModel->addDataUser($data);	
		//}  
		// Duplicate Semua User Sesuai Role Terpilih

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ROLE";
		$params['TrxID'] = $koderole;
		$params['Description'] = $_SESSION["logged_in"]["username"]." DUPLICATE ROLE ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

        redirect('Role?duplicatesuccess=1');
    }


    public function update()
	{
		$koderole = $this->input->post('utxtKodeRole');
		$namarole = $this->input->post('utxtNamaRole');

		// if(isset($_POST['uchkDefault'])){
		// 	$is_default = $this->input->post('uchkDefault');
		// }
		// else{
		// 	$is_default = 0;
		// }

		//if(isset($_POST['uchkHRD'])){
		//	$is_hrd = $this->input->post('uchkHRD');
		//}
		//else{
		//	$is_hrd = 0;
		//}

		// if(isset($_POST['uchkNotif'])){
		// 	$is_notif = $this->input->post('uchkNotif');
		// }
		// else{
		// 	$is_notif = 0;
		// }

		if(isset($_POST['uchkMainBranch'])){
			$is_mainbranch = $this->input->post('uchkMainBranch');
		}
		else{
			$is_mainbranch = 0;
		}


		//$importance = $this->input->post('uselImpor');

		$now = date('Y/m/d h:i:s A');

		$data = array (
            'role_name'  => $namarole,
            // 'role_default'=> $is_default,
            //'is_hrd' => $is_hrd,
			// 'report_cuti_notification' => $is_notif,
			'mainbranch_role' => $is_mainbranch,
            //'role_importance' => $importance,
			'updated_by' => $_SESSION['logged_in']['username'],
			'updated_date' => $now
        );

        $this->RoleModel->updateData($data,$koderole);

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ROLE";
		$params['TrxID'] = $koderole;
		$params['Description'] = $_SESSION["logged_in"]["username"]." UPDATE ROLE ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

        redirect('Role?updatesuccess=1');
    }

    public function updateuserbyrole(){
    	$koderole = $_POST['txtKodeUserByRole'];
    	$Jmlhuser = $_POST['hdnJmlhUser']; 
    	for($i=0;$i<$Jmlhuser;$i++){   
    		if (isset($_POST['chkdelete'.$i])==1) 
    		{  
    		$this->RoleModel->deleteuserrole($koderole,$_POST['hdnkduser'.$i]); 
    		}  
    	} 
        $userlist = $this->UserModel->getuserListByRole($koderole);
       	$_SESSION['userlist'] = $userlist;
    	redirect('Role?deleteusersuccess=1');
    }

    public function adduser(){ 
		//$roleid = $this->input->post('txtroleidhidden2');
    	$koderole = $_POST['txtroleidhidden2'];
    	$listuseradd = $_POST['listuserid'];
		$now = date('Y/m/d h:i:s A'); 
		$arrays = explode(',', $listuseradd);  
		for ($i = 0; $i< count($arrays); $i++){  
			$dataEmail['user_email'] =$this->RoleModel->getuseremail($koderole, $arrays[$i]); 
			$data = array (	
	            'UserEmail' => $dataEmail['user_email'],
	            'role_id'  => $koderole,
	            'created_by'=> $_SESSION['logged_in']['username'],
				'created_date' => $now,  
				'UserID' => $arrays[$i], 
	        ); 
	        $this->RoleModel->addDataUser($data);	 
		}       

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ROLE";
		$params['TrxID'] = $koderole;
		$params['Description'] = $_SESSION["logged_in"]["username"]." ROLE ADD USER ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

    	redirect('Role?addusersuccess=1');
    }
 
    public function updateModule(){
    	$koderole = $_POST['mtxtKodeRole'];
    	$jmlhmodule = $_POST['hdnJmlhModule'];

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ROLE";
		$params['TrxID'] = $koderole;
		$params['Description'] = $_SESSION["logged_in"]["username"]." ROLE UPDATE MODULE";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

    	for($i=0;$i<$jmlhmodule;$i++){
    		$kdmodule = 'hdnKdModule'.$i;
    		if(!empty($_POST[$kdmodule])){
	    		$temp = $this->RoleModel->getModuleDetail($koderole, $_POST[$kdmodule]);
				if($temp){
					$data = array (
			            'can_read'  => (isset($_POST['chkread'.$i])) ? "1" : "0",
			            'can_create'=> (isset($_POST['chkcreate'.$i])) ? "1" : "0",
			            'can_update' => (isset($_POST['chkupdate'.$i])) ? "1" : "0",
			            'can_delete' => (isset($_POST['chkdelete'.$i])) ? "1" : "0",
			            'can_print' => (isset($_POST['chkprint'.$i])) ? "1" : "0",
			        );
			        $this->RoleModel->updateModuleDetail($data,$koderole,$_POST[$kdmodule]);
				}
				else{
					$data = array (
						'module_id' => $_POST[$kdmodule],
						'role_id' => $koderole,
			            'can_read'  => (isset($_POST['chkread'.$i])) ? "1" : "0",
			            'can_create'=> (isset($_POST['chkcreate'.$i])) ? "1" : "0",
			            'can_update' => (isset($_POST['chkupdate'.$i])) ? "1" : "0",
			            'can_delete' => (isset($_POST['chkdelete'.$i])) ? "1" : "0",
			            'can_print' => (isset($_POST['chkprint'.$i])) ? "1" : "0",
			        );
			        $this->RoleModel->insertModuleDetail($data);
				}
			}
    	}

        $mod = $this->ModuleModel->getListByRole($_SESSION['role']);
       	$_SESSION['module'] = $mod;

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

    	redirect('Role?updatesuccess=1');
    }

    public function delete()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ROLE";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." ROLE DELETE";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$dataid = $this->input->get('id');
        $this->RoleModel->deleteData($dataid);

		$roleid = $this->input->get('id');
        $this->RoleModel->deleteModuleDetail($dataid);

		$roleid = $this->input->get('id');
        $this->RoleModel->deletealluserrole($dataid);

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);
		
        redirect('Role?deletesuccess=1');
    } 
}