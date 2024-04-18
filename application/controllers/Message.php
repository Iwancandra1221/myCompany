<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class message extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('ConfigSysModel');
	}
	
	public function index($message="")
	{
		$post = $this->PopulatePost();

		$this->load->helper('FormLibrary');
		$data['mode'] = "all";
		$data['title'] = $this->ConfigSysModel->GetCompanyName().' - Message';
		$data['titlebar'] = "Message";
		$data['message'] = $message;
		$this->RenderView('message',$data);
	}

	
}
