<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class account extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model("accountModel");
	}

	public function index()
	{	
		$ListAccount = $this->accountModel->GetList();
		die(json_encode($ListAccount));
	}

	

}