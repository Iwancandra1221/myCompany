<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class UserBranchMapping extends MY_Controller 
{
	public $excel_flag = 0;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('HelperModel');
		$this->load->helper('FormLibrary');
		$this->load->library('email');
		$this->load->library('excel');
	}

	public function Index()
	{
		$data = array();
		$data["branches"] = $this->BranchModel->GetList();
		$data["users"] = $this->UserModel->getAllActiveUser();
		$this->RenderView("UserBranchMappingView", $data);

	}

	public function Add()
	{
		$data = array();	
		$api = "APITES";
		//die(file_get_contents(HRD_URL."Branch/GetBranchesAPI?api=".urlencode($api)."&user=".urlencode($_SESSION["logged_in"]["useremail"])));
		$branches = json_decode(file_get_contents(API_HRD."/Branch/GetBranchesAPI?api=".urlencode($api)."&user=".urlencode($_SESSION["logged_in"]["useremail"])),true);
		$data["branches"] = $branches["data"];
		//$data["users"] = $this->UserModel->getAllActiveUser();
		$this->RenderView("UserBranchMappingAddView", $data);

	}


}