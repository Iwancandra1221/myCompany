<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MasterDb extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		
	}

	public function index()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS DATABASE";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU MS DATABASE ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$data = array();
        $data['result'] = $this->MasterDbModel->getList();

        if($this->uri->segment(2) != '')
	    	$ctrname = $this->uri->segment(1)."/".$this->uri->segment(2);
        else
	       	$ctrname = $this->uri->segment(1);

    	$data['access'] = $this->ModuleModel->getDetail($ctrname, $_SESSION['role']);
    	//die(json_encode($data["access"]));
    	$data["title"] = "";

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

    	$this->RenderView("MasterDbView", $data);
	}

	public function getListDb($branch="")
	{
		if ($branch=="") $branch = $_SESSION['logged_in']['branch_id'];

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS DATABASE";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." GET LIST MS DATABASE ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

        $data['result'] = $this->MasterDbModel->getList($branch); 

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		echo json_encode($data['result']);
	}

	public function insert_page()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS DATABASE";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." INSERT PAGE MS DATABASE ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$data = array();
		$data["branches"] = $this->MasterDbModel->getBranches();
		$data["databasetype"] = $this->MsConfigModel->GetDatabaseType();

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView("MasterDbInsertView", $data);
	}

	public function update_page()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS DATABASE";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." UPDATE PAGE MS DATABASE ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$dataid = $this->input->get('id');
		
        $data['row'] = $this->MasterDbModel->get($dataid);

		$data["branches"] = $this->MasterDbModel->getBranches();
		$data["databasetype"] = $this->MsConfigModel->GetDatabaseType();

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView("MasterDbUpdateView", $data);
	}

	public function detail_page()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS DATABASE";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." VIEW PAGE MS DATABASE ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$dataid = $this->input->get('id');
		
        $data['row'] = $this->MasterDbModel->get($dataid);

		$data["branches"] = $this->MasterDbModel->getBranches();
		$data["databasetype"] = $this->MsConfigModel->GetDatabaseType();

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView("MasterDbDetailView", $data);
	}

	public function insert_data()
	{ 
		
		$branchid = $this->input->post('txtBranchId');
		$namadb = $this->input->post('txtNamaDb');
		$alamatws = $this->input->post('txtAlamatWS');
		$alamatwsJava = $this->input->post('txtAlamatWSJava');
		$server = $this->input->post('txtServer');
		$database = $this->input->post('txtDb');
		$tipe = $this->input->post("txtDbType");
		$loc = $this->input->post("txtLoc");
		
		$now = date('Y/m/d h:i:s A');

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS DATABASE";
		$params['TrxID'] = $branchid;
		$params['Description'] = $_SESSION["logged_in"]["username"]." INSERT DATA MS DATABASE ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$data = array (
            'BranchId' => $branchid,
            'NamaDb'  => $namadb,
            'AlamatWebService'=> $alamatws,
            'AlamatWebServiceJava'=> $alamatwsJava,
            'Server' => $server,
            'Database' => $database,
            'DatabaseType'=>$tipe,
            'LocationCode'=>$loc,
            'Created_By' => $_SESSION['logged_in']['username'],
            'Created_Time' => $now
        );
		
        $result = $this->MasterDbModel->addData($data); 

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

	 	echo '<script type="text/javascript">
			 	alert("'.$result.'");
			 	window.location.href = "'.base_url().'MasterDb";
			 	</script>';  
    }

    public function update_data()
	{
		$id = $this->input->post('hdnId');
		$branchid = $this->input->post('txtBranchId');
		$namadb = $this->input->post('txtNamaDb');
		$alamatws = $this->input->post('txtAlamatWS');
		$alamatwsJava = $this->input->post('txtAlamatWSJava');
		$server = $this->input->post('txtServer');
		$database = $this->input->post('txtDb');
		$tipe = $this->input->post("txtDbType");
		$loc = $this->input->post("txtLoc");

		$now = date('Y/m/d h:i:s A');

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS DATABASE";
		$params['TrxID'] = $branchid;
		$params['Description'] = $_SESSION["logged_in"]["username"]." UPDATE DATA MS DATABASE ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$data = array (
            'BranchId' => $branchid,
            'NamaDb'  => $namadb,
            'AlamatWebService'=> $alamatws,
            'AlamatWebServiceJava'=> $alamatwsJava,
            'Server' => $server,
            'Database' => $database,
            'DatabaseType'=>$tipe,
            'LocationCode'=>$loc,
            'Updated_By' => $_SESSION['logged_in']['username'],
            'Updated_Time' => $now
        );

        $this->MasterDbModel->updateData($data,$id);

    	$result = $this->MasterDbModel->updateData($data,$id); 

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

	 	echo '<script type="text/javascript">
			 	alert("'.$result.'");
			 	window.location.href = "'.base_url().'MasterDb";
			 	</script>';  
    }


    public function delete_data()
	{
		$dataid = $this->input->get('id');

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MS DATABASE";
		$params['TrxID'] = $dataid;
		$params['Description'] = $_SESSION["logged_in"]["username"]." DELETE DATA MS DATABASE ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

        $this->MasterDbModel->deleteData($dataid);

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

        redirect('MasterDb?deletesuccess=1');
    }
}