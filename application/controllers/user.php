<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class user extends NS_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model("UserModel");
	}

	public function index()
	{	
		// $ListAccount = $this->accountModel->GetList();
		// die(json_encode($ListAccount));
	}

	public function get()
	{
		$USERID="";
		$USEREMAIL="";

		if (isset($_GET["userid"])) {
			$USERID = str_replace("'","",urldecode($this->input->get("userid")));
		} else if (isset($_GET["useremail"])) {
			$USEREMAIL = str_replace("'","",urldecode($this->input->get("useremail")));
		}
		$EMPLOYEE = array();
		if ($USERID!="0" && $USERID!="") {
			$EMPLOYEE = $this->UserModel->Get($USERID);
		} else {
			$EMPLOYEE = $this->UserModel->getUserDataByEmail($USEREMAIL);
		}

		if ($EMPLOYEE==null) {
			$result["result"] = "gagal";
			$result["data"] = null;
			// $result["registration"] = array();
			// $result["recruitment"] = array();
			// $result["promotion"] = array();
			// $result["mutation"] = array();
			$result["error"]  = "DATA KARYAWAN TIDAK DITEMUKAN";
		} else {
			$result["result"] = "sukses";
			$result["data"] = $EMPLOYEE;
			// $result["registration"] = $this->APIModel->GetTransHistory("Trans_Registration_History", "ApplicantID", $EMPLOYEE->APPLICANTID);
			// $result["recruitment"] = $this->APIModel->GetTransHistory("Trans_Recruitment_History", "UserID", $USERID);
			// $result["promotion"] = $this->APIModel->GetTransHistory("Trans_Promotion_History", "UserID", $USERID);
			// $result["mutation"] = $this->APIModel->GetTransHistory("Trans_Mutation_History", "UserID", $USERID);
			$result["error"]  = "";
		}
		
		$hasil = json_encode($result);
		header('HTTP/1.1: 200');
		header('Status: 200');
		header('Content-Length: '.strlen($hasil));
		exit($hasil);		
	}
	

}