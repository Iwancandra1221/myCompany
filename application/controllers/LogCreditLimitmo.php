<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LogCreditLimitmo extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct(); 
		$this->load->helper('FormLibrary');
	}
	
	public function index()
	{
		$api = 'APITES';
		$urlService = $_SESSION["conn"]->AlamatWebService;
		$checkAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));  	
		$data = array(); 
		set_time_limit(60); 
		$Gets = json_decode(file_get_contents($urlService.API_BKT."/LogCreditLimitmo/GetLogCreditLimitmo?api=".$api)); 
		$data["result"] = $Gets; 
		$data["title"] = "Log Credit Limit MO";
		$this->RenderView('LogCreditLimitmoView',$data);
	}
 
	public function Update()
	{  
		$post = $this->PopulatePost();	 
		$api = 'APITES';
		$urlService = $_SESSION["conn"]->AlamatWebService;
		$URL = $urlService.API_BKT."/LogCreditLimitmo/CheckLimitMODM?api=APITES&kdplg=".$post["kdplg"]."&divisi=".$post["divisi"];
		$json = json_decode(file_get_contents($URL));
		if(count($json)>0){
			$msg = array();
			$msg['result'] = "SUKSES";
			$msg['message'] = "Data Berhasil Di-update";
			echo json_encode($msg);	
		}
		else
		{
			$msg = array();
			$msg['result'] = "Gagal";
			$msg['message'] = "Data Tidak Di Temukan";
			echo json_encode($msg);	
		} 
	}     
}