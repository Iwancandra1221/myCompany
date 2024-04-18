<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TargetKhusus extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('FormLibrary');
		$this->load->model('TargetKhususModel');
	}
	
	public function index()
	{
		$post = $this->PopulatePost();

		$data = array();
		$api = 'APITES';
		set_time_limit(60);

		$data["List"] = json_decode(file_get_contents(TES_URL."/IncentiveSalesman/GetListTargetKhusus?api=".$api));
		$this->RenderView('TargetKhususListView',$data);
	}

}