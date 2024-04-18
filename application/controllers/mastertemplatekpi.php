<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mastertemplatekpi extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('mastertemplatemodel');  
		$this->load->model('accountModel');  
		$this->load->helper('FormLibrary');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}
	
	public function Salesman()
	{
		$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2)); 
		$this->alert = ""; 
		set_time_limit(60);  
		$post = $this->PopulatePost();		
		$data = array();  

   		$url = $this->API_URL."/MasterTemplateKPI/Master_Template_Target_KPI_AmbilList?api=APITES&jenis=1"; 
		$list = HttpGetRequest($url, $this->API_URL, "Ambil List Template KPI Salesman"); 
		$data["title"] = "Master Template KPI Salesman"; 
		$data["tipe"] = "Salesman"; 
		$data["list"] = json_decode($list);     
        $data["alert"] = $this->alert;

		$params = array();	
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER TEMPLATE TARGET KPI SALESMAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU MASTER TEMPLATE TARGET KPI SALESMAN";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$this->RenderView('mastertemplatekpiview',$data);
	}  
	public function Karyawan()
	{
		$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2)); 
		$this->alert = ""; 
		set_time_limit(60);  
		$post = $this->PopulatePost();		
		$data = array();   
   		$url = $this->API_URL."/MasterTemplateKPI/Master_Template_Target_KPI_AmbilList?api=APITES&jenis=0"; 
		$list = HttpGetRequest($url, $this->API_URL, "Ambil List Template KPI Karyawan");  
		$data["title"] = "Master Template KPI Karyawan"; 
		$data["tipe"] = "Karyawan"; 
		$data["list"] = json_decode($list);     
        $data["alert"] = $this->alert;

		$params = array();	
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER TEMPLATE TARGET KPI KARYAWAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU MASTER TEMPLATE TARGET KPI KARYAWAN";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$this->RenderView('mastertemplatekpiview',$data);
	}  
    public function insert()
    { 
		set_time_limit(60);  
		$this->load->library('form_validation');
		$this->form_validation->set_rules('txt_template_id','Template ID','required'); 
		$this->form_validation->set_rules('txt_template_name','Template Name','required'); 
		$this->form_validation->set_rules('cboKpiCategory','KPi Category','required'); 
		$this->form_validation->set_rules('dtStartDate','Start Date','required');  


		$templateId = $this->input->post('txt_template_id', true);
		$templateName = $this->input->post('txt_template_name', true);
		$kpiCategory = $this->input->post('cboKpiCategory', true);
		$startDate = $this->input->post('dtStartDate', true); 
		$active = $this->input->post('IsActive', true);
		$texens = $this->input->post('texens', true);
		$listposition = $this->input->post('listposition', true); 
		$listbobot = $this->input->post('listbobot', true); 
		$listkpi = $this->input->post('listkpi', true); 
 		$userlogin = $_SESSION['logged_in']['username'];
		$event = $this->input->post('event_submit', true);

        if ($this->form_validation->run() == false) {
            $response = array(
                'status' => 'Error',
                'message' => validation_errors()
            );
        }
        else {  
        	if ($texens=="1")
			{
				$response = array(
	                'status' => 'Error',
	                'message' => "Total Bobot Harus 100"
	            );
			}
			else if ($texens=="2")
			{
				$response = array(
	                'status' => 'Error',
	                'message' => "KPI Position Tidak Boleh Ada Yang Sama"
	            );
			}
			else if ($texens=="3")
			{
				$response = array(
	                'status' => 'Error',
	                'message' => "KPI Tidak Boleh Ada Yang Sama"
	            );
			}
			else 
			{ 	  
				if($event==1) { 
					$url = $this->API_URL."/MasterTemplateKPI/Master_Template_Target_KPI_Insert?api=APITES&template_id=".urlencode($templateId)."&template_name=".urlencode($templateName)."&kpi_category_id=".urlencode($kpiCategory)."&start_date=".urlencode($startDate)."&is_active=".urlencode($active)."&listID=".urlencode($listposition)."&listKPI=".urlencode($listkpi)."&listBobot=".urlencode($listbobot)."&iUser=".urlencode($userlogin);  
					$list = HttpGetRequest($url, $this->API_URL,  "Insert Template KPI"); 
					$hasil =json_decode($list);
					$response = array(
		                'status' => $hasil,
		                'message' => $hasil
		            );   
					}
				else
				{  
					$url = $this->API_URL."/MasterTemplateKPI/Master_Template_Target_KPI_Update?api=APITES&template_id=".urlencode($templateId)."&template_name=".urlencode($templateName)."&kpi_category_id=".urlencode($kpiCategory)."&start_date=".urlencode($startDate)."&is_active=".urlencode($active)."&listID=".urlencode($listposition)."&listKPI=".urlencode($listkpi)."&listBobot=".urlencode($listbobot)."&iUser=".urlencode($userlogin);  
					$list = HttpGetRequest($url, $this->API_URL,  "Update Template KPI"); 
					$hasil =json_decode($list);
					$response = array(
		                'status' => $hasil,
		                'message' => $hasil
		            );  
	            } 
			}
        }

		$params = array();	
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER TEMPLATE TARGET KPI";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." TAMBAH MASTER TEMPLATE TARGET KPI";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function deletetemplate()
	{ 
		set_time_limit(60);  
		$post = $this->PopulatePost();	
		$templateid = $this->input->post('templateid')	;
		$data = array();  
   		$url = $this->API_URL."/MasterTemplateKPI/Master_Template_Target_KPI_Delete?api=APITES&template_id=".urlencode($templateid); 
		HttpGetRequest($url, $this->API_URL, "Delete Template KPI Karyawan"); 

		$params = array();	
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER TEMPLATE TARGET KPI";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." HAPUS MASTER TEMPLATE TARGET KPI";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		echo "1"; 
	}
}