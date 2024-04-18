<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SalesManagerAPI extends NS_Controller 
{

	public $alert="";
	public function __construct()
	{
		parent::__construct();
        $this->load->model('SalesManagerModel');
        $this->load->model("BranchModel");
	}

	//GetGeneralManager
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