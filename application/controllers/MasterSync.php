<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MasterSync extends MY_Controller {

	function __construct()
	{
		parent::__construct();
        $this->load->model('MasterSyncModel');
        $this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function index()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER SYNC"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER SYNC";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$data = array();
		//$data["branches"] = $this->MasterDbModel->getBranches();
		$data["branches"] = null;
        $getBranch = json_decode(file_get_contents($this->API_URL.'/Cabang/GetMstCabang?api=APITES'),true);
		if($getBranch !=null){
			$data["branches"] = $getBranch['data'];
		}
        $data['result'] = $this->MasterSyncModel->getList();
		
        if($this->uri->segment(2) != '')
	    	$ctrname = $this->uri->segment(1)."/".$this->uri->segment(2);
        else
	       	$ctrname = $this->uri->segment(1);

	    // die($ctrname);

    	$data['access'] = $this->ModuleModel->getDetail($ctrname, $_SESSION['role']);
		//die(json_encode($data));
    	// die(json_encode($data["result"]));
		
    	$data["title"] = "";
    	$this->RenderView("MasterSyncView", $data);

    	$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
	}

	public function getListDb($branch="")
	{
		if ($branch=="") $branch = $_SESSION['logged_in']['branch_id'];
        $data['result'] = $this->MasterDbModel->getList($branch); 
		echo json_encode($data['result']);
	}

	public function Add()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER SYNC"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER SYNC - ADD";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$ConfigType = $this->input->get('ConfigType');
		
		$data = array();
		//$data["branches"] = $this->MasterDbModel->getBranches();
		$data["branches"] = null;
        $getBranch = json_decode(file_get_contents($this->API_URL.'/Cabang/GetMstCabang?api=APITES'),true);
		if($getBranch !=null){
			$data["branches"] = $getBranch['data'];
		}
		$data["ConfigType"] = $ConfigType; 
		$this->RenderView("MasterSyncInsertView", $data);

		$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
	}

	public function Edit()
	{

		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER SYNC"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER SYNC - EDIT";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$ConfigType = $this->input->get('ConfigType');
		$ConfigId = $this->input->get('ConfigId');
		$ConfigName = $this->input->get('ConfigName');
		$data["ConfigType"] = $ConfigType;
		$data["ConfigId"] = $ConfigId;
		$data["ConfigName"] = $ConfigName;
        $data['result'] = $this->MasterSyncModel->get($ConfigId,$ConfigName);
        $data["branches"] = null;
        $getBranch = json_decode(file_get_contents($this->API_URL.'/Cabang/GetMstCabang?api=APITES'),true);
		if($getBranch !=null){
			$data["branches"] = $getBranch['data'];
		}
		//$data["branches"] = $this->MasterDbModel->getBranches();
		// echo json_encode($data);die;
		$this->RenderView("MasterSyncUpdateView", $data);

		$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
	}

	public function Insert()
	{

		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER SYNC"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER SYNC - INSERT";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);


		$ConfigName = $this->input->post('ConfigName');
		$ConfigValue = $this->input->post('ConfigValue');
		$Level = $this->input->post('Level');
		$ConfigType = $this->input->post("ConfigType");
		
		if($ConfigType=='CONFIG'){
			$IsActive[0] = $this->input->post('IsActive');
			$BranchId[0] = $this->input->post('BranchId');
		}else{
			// $BranchChecked = array();
			// if($this->input->post('BranchId')!= NULL){
				// $BranchChecked = $this->input->post('BranchId');
			// }			
			// $branches = $this->MasterDbModel->getBranches();
			
			// $BranchId = array();
			// $IsActive = array();
			// foreach($branches as $branch){
				// $BranchId[] = $branch->branch_code;
				// if( in_array($branch->branch_code,$BranchChecked ) )
				// {
					// $IsActive[] = 1;
				// }
				// else  $IsActive[] = 0;
			// }
			$IsActive[0] = $this->input->post('IsActive');
			$BranchId[0] = 'ALL';
		}
		
		$data = array (
			'ConfigType' => $ConfigType,
			'BranchId' => $BranchId,
			'ConfigName'  => $ConfigName,
			'ConfigValue'=> $ConfigValue,
			'Level' => $Level,
			'IsActive' => $IsActive,
			'CreatedBy' => $_SESSION['logged_in']['username']
		);
		$result = $this->MasterSyncModel->addData($data);
		
		if($result){
			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			redirect('MasterSync?insertsuccess=1');
		}else{
			$paramsLog['Remarks']="FAILED - INSERT GAGAL";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			redirect('MasterSync?insertsuccess=0');
		}
    }
    public function UpdateStatus(){
    	$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER SYNC"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER SYNC - UPDATE";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);


    	$data = array(
    		'code' => 'failed',
    		'messages' => array(),
    		'data' => array(),
    	);

    	$ConfigId = (int)$this->input->post('ConfigId');
    	$IsActive = (int)$this->input->post('IsActive');
    	$msg = "Ada data yang belum diisi";
    	if($ConfigId!=='' && $IsActive!==''){
    		$where = array(
    			'ConfigId' => $ConfigId,
    		);
    		$dataMaster = array(
    			'IsActive' => $IsActive,
    			'ModifiedBy' => $_SESSION['logged_in']['username'],
    			'ModifiedDate' => date('Y-m-d H:i:s'),
    		);
    		$result = $this->MasterSyncModel->updatev2($dataMaster,$where);
    		if($result){
    			$data['code'] = 'success';
    			$msg = "Update Master Sync Berhasil";

    			$paramsLog['Remarks']="SUCCESS";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);
    		}
    		else{
    			$paramsLog['Remarks']="FAILED - UPDATE MASTER SYNC GAGAL";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);
    		}
    	}
    	else{
    		$paramsLog['Remarks']="FAILED - ADA DATA YANG BELUM DIISI";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
    	}
    	$data['messages'][0] = $msg;
    	$json = json_encode($data);
    	echo $json;

    }
	public function Update()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER SYNC"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER SYNC - UPDATE";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$ConfigId = $this->input->post('ConfigId');
		$ConfigName = $this->input->post('ConfigName');
		$ConfigValue = $this->input->post('ConfigValue');
		$Level = $this->input->post('Level');
		$ConfigType = $this->input->post("ConfigType");
		
		if($ConfigType=='CONFIG'){
			$IsActive = $this->input->post('IsActive');
			$BranchId = $this->input->post('BranchId');
		}else{
			// $BranchChecked = array();
			// if($this->input->post('BranchId')!= NULL){
				// $BranchChecked = $this->input->post('BranchId');
			// }
			// $branches = $this->MasterDbModel->getBranches();
			
			// $BranchId = array();
			// $IsActive = array();
			// foreach($branches as $branch){
				// $BranchId[] = $branch->branch_code;
				// if( in_array($branch->branch_code, $BranchChecked ) )
				// {
					// $IsActive[] = 1;
				// }
				// else  $IsActive[] = 0;
			// }
			$IsActive = $this->input->post('IsActive');
			$BranchId = 'ALL';
		}
		
		$data = array (
			'ConfigType' => $ConfigType,
			'BranchId' => $BranchId,
			'ConfigName'  => $ConfigName,
			'ConfigValue'=> $ConfigValue,
			'Level' => $Level,
			'IsActive' => $IsActive,
			'ModifiedBy' => $_SESSION['logged_in']['username']
		);
		
		// if($ConfigType=='CONFIG'){
			// $result = $this->MasterSyncModel->update($data, $ConfigId);
		// }
		// else{
			// $result = $this->MasterSyncModel->updateData($data);
		// }
		$result = $this->MasterSyncModel->update($data, $ConfigId);
		
		if($result){
			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			redirect('MasterSync?insertsuccess=1');
		}else{
			$paramsLog['Remarks']="FAILED - UPDATE DATA GAGAL";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			redirect('MasterSync?insertsuccess=0');
		}
    }

    public function update_data()
	{

		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER SYNC"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER SYNC - UPDATE DATA";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$id = $this->input->post('hdnId');
		$branchid = $this->input->post('txtBranchId');
		$namadb = $this->input->post('txtNamaDb');
		$alamatws = $this->input->post('txtAlamatWS');
		$server = $this->input->post('txtServer');
		$database = $this->input->post('txtDb');
		$tipe = $this->input->post("txtDbType");

		$now = date('Y/m/d h:i:s A');

		$data = array (
            'BranchId' => $branchid,
            'NamaDb'  => $namadb,
            'AlamatWebService'=> $alamatws,
            'Server' => $server,
            'Database' => $database,
            'DatabaseType'=>$tipe,
            'Updated_By' => $_SESSION['logged_in']['username'],
            'Updated_Time' => $now
        );

        $this->MasterSyncModel->updateData($data,$id);

        redirect('MasterDb?updatesuccess=1');

        $paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
    }


    public function Delete()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER SYNC"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER SYNC - DELETE";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$ConfigId = $this->input->get('id');
        $result = $this->MasterSyncModel->deleteData($ConfigId);
		$result = json_decode($result);
		if($result){
			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			redirect('MasterSync?deletesuccess=1');

		}else{
			$paramsLog['Remarks']="FAILED - DELETE GAGAL";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			redirect('MasterSync?deletesuccess=0');
		}
		
    }
}