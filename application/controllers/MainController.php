<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MainController extends NS_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		$this->version = "1.10.15";
	}

	public function index()
	{
		$this->load->view('main', array("version"=>$this->version));
		// $this->load->view('UnderMaintenanceView');
	}

	function changelog(){
		$api = 'APITANDATERIMA';
		$data['proses']=0;
		$data['url']='';
		$data['ConfigName']='';
		$data['ConfigType']='';
		$data['ConfigDesc']='';
		$url = $this->API_URL."/Viewchangelogbhakti/ChangeLog?api=".$api."&log=MyCompany&type=ChangeLog";
		$changelog = json_decode(file_get_contents($url));
		if(count($changelog) == 1){
			$data['proses']=1;
			$data['url']=$changelog[0]->ConfigValue;
			$data['ConfigName']=$changelog[0]->ConfigName;
			$data['ConfigType']=$changelog[0]->ConfigType;
			$data['ConfigDesc']=$changelog[0]->ConfigDesc;
		}
		$this->load->view('changelog',$data);
	}
}
