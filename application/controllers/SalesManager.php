<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SalesManager extends MY_Controller {

	public $alert="";
	public function __construct()
	{
		parent::__construct();
        $this->load->model('SalesManagerModel');
        $this->load->model("BranchModel");
        $this->load->model("MsConfigModel");
	}

	public function index()
	{
		

		$CheckAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		$post = $this->PopulatePost();
		if(isset($post['SaveMode'])){
			$simpan =  $this->Simpan($post);
			echo json_encode($simpan);
		}else{
			$this->ViewSalesManager();
		}

	}

	public function ViewSalesManager()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="SALES MANAGER"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU SALES MANAGER";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$data = array();
        // $managers = $this->SalesManagerModel->GetList();
        // $data["managers"] = $managers;





        $branches = array();
		$branches = $this->BranchModel->GetList();
		$data["branches"] = $branches;

        $jabatan = array();
		$jabatan = $this->MsConfigModel->GetConfigValue('USER POSITION','POSITION NAME','ALL');
		$data["jabatan"] = $jabatan;

        // $employees = array();
        // $ListEmployee = array();
		// $ListEmployee = $this->UserModel->getAllUser();
		// // echo json_encode($ListEmployee);die;




		// foreach($ListEmployee as $e){
		// 	if($e->IsActive==1)
		// 	array_push($employees, trim($e->UserName)." - ".trim($e->AlternateID)." - ".trim($e->UserEmail));
		// }

		// // echo json_encode($employees);die;

		// $data["ListEmployee"] = $ListEmployee;
        // $data["employees"] = $employees;

        $data["alerts"] = $this->alert;
		$this->RenderView('SalesManagerView',$data);

		$paramsLog['Remarks']="SUCCESS";
	   	$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
	   	$this->ActivityLogModel->update_activity($paramsLog);

	}
	

	public function ListUsers_OLD(){
	    $str = "";

	    $data_list=array();
	    $total =0;

	    	$data=$this->input->get();
	    	$ListEmployee = $this->UserModel->getAllUser($data);

			$users = $ListEmployee["data"];
			$total = $ListEmployee["total"];
			
			$data_hasil=array();


			if(!empty($users)){
				foreach (json_decode(json_encode($users)) as $key => $r) {
					$action='';
					$req ='';
					$list=array();

					$onclick 	= "'".$r->AlternateID."','".$r->UserName."','".$r->UserEmail."'";
					$list[] 	= '<button class="btn btn-sm btn-dark" onclick="pilihuser('.$onclick.')">Pilih</button>';
					$list[] 	= $r->AlternateID;
					$list[] 	= $r->UserName;
					$list[] 	= $r->UserEmail;
					

					$data_list[]=$list;

				}
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

	public function ListUsers(){
		$param = $_GET;
		$data = $this->UserModel->getAllUser($param);
		echo $data;
	}
	public function GetList(){
        $managers = $this->SalesManagerModel->GetList();
		echo json_encode($managers);
	}

	public function Simpan($post)
	{
		$params = array();
		if(isset($post['DivisionID']) && isset($post["EmployeeID"] )){
			$paramsLog = array();   
			$paramsLog['LogDate'] = date("Y-m-d H:i:s");
			$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
			$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
			$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$paramsLog['Module']="SALES MANAGER"; 
			$paramsLog['TrxID'] = date("YmdHis");
			$paramsLog['Description']=$_SESSION["logged_in"]["username"]." Proses - ".$post["SaveMode"]." SALES MANAGER";
			$paramsLog['Remarks']="";
			$paramsLog['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($paramsLog);


			$params["BranchID"] = $post["BranchID"];
			$params["DivisionID"] = $post["DivisionID"];
			$params["EmployeeName"] = $post["EmployeeName"];
			$params["EmployeeID"] = $post["EmployeeID"];
			$params["UserID"] = $post["UserID"];
			$params["EmailAddress"] = $post["EmailAddress"];
			$params["Mobile"] = $post["Mobile"];
			$params["LevelSalesman"] = $post["LevelSalesman"];

			if ($post["SaveMode"]=="add") {
				$result = $this->SalesManagerModel->Insert($params);

				$paramsLog['Remarks']="SUCCESS";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);

				return $result;
			} else {
				$params["salesman_id"] = $post["salesman_id"];
				$result = $this->SalesManagerModel->Update($params);

				$paramsLog['Remarks']="SUCCESS";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);

				return $result;
			}
		} else {
			$res['result'] = "gagal";
			$res['error'] = "Simpan Gagal! Divisi/Karyawan Belum Diisi";

			$paramsLog['Remarks']="FAILED - Simpan Gagal! Divisi/Karyawan Belum Diisi";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			return $res;
		}
		// redirect("SalesManager");
	}

	public function Hapus()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="SALES MANAGER"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." HAPUS SALES MANAGER";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$post = $this->PopulatePost();
		// echo json_encode($post);die;
		$params = array();

		if(isset($post['salesman_id'])){
			$params["salesman_id"] = $post["salesman_id"];
			$result = $this->SalesManagerModel->Delete($params);

			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			echo json_encode($result);
		} else {
			$result['result'] = 'gagal';
			$result['error'] = 'Data gagal dihapus!';

			$paramsLog['Remarks']="FAILED - DATA GAGAL DIHAPUS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			echo json_encode($result); 
		}
	}
	
	public function GetGeneralManager()
	{
		$result=$this->SalesManagerModel->GetGeneralManager(); 
		echo json_encode($result);
	}	

	public function GetBrandManagers()
	{
		$result=$this->SalesManagerModel->GetBrandManagers(); 
		echo json_encode($result);
	}	
	
	public function GetBrandManagersByDivisi()
	{
		$divisi = urldecode($this->input->get("divisi"));
		$result=$this->SalesManagerModel->GetBrandManagersByDivisi($divisi); 
		echo json_encode($result);
	}			
}