<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reportlisting extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->helper('FormLibrary');
	}
	public function index()
	{
		$checkAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2)); 
		$data = array();  
		$this->RenderView('Reportlistingview',$data);  
	}  
	public function loadData()
	{
		$post = $this->PopulatePost();		
		$data = array();  
		$api = 'APITES';  
    	$url = $_SESSION['conn']->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database; 
		//$url = "http://localhost:100/";   
		$Tipe = $this->input->get('Tipe');  
		$Report = $this->input->get('Report');  
		$jumlah_hari = $this->input->get('jumlah_hari');  
		$urlGet = $url.API_BKT."/Reportlisting/GetFaktturListing?api=APITES&svr=10.1.0.92&db=BHAKTI&tipe_check=".$Tipe."&filter_by=".$Report."&hari_terakhir=".$jumlah_hari;
		$GetZen = json_decode(file_get_contents($urlGet), true);  
		if ($GetZen["result"]=="sukses") {  
			$data = $GetZen["data"];  
		}   
		print_r(json_encode($data));
	}
}