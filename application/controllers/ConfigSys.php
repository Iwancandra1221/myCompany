<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ConfigSys extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('ConfigSysModel');
	}

	public function index()
	{
		$update = $this->input->get('update');
		$result = $this->ConfigSysModel->Get();
		$data["result"] = $result;	
		if($update!=''){
		$data["update"] = $update;	
		}
		// echo json_encode($result);die;
		$this->RenderView('ConfigSysView',$data);
	}
	
	
	public function ConfigSysUpdate()
	{
		$post = $this->PopulatePost();
		// echo json_encode($post);die;
		$result = $this->ConfigSysModel->ConfigSysUpdate($post);
		redirect('ConfigSys?update='.$result);
	}
}