<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Masterkpi extends MY_Controller{

	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}
	
	private function _postRequest($url,$data){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
		//curl_setopt($ch, CURLOPT_POSTFIELDS,"postvar1=value1&postvar2=value2&postvar3=value3");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);
		curl_close($ch);
		return $server_output;
	}
	
	public function index(){
		$checkAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2)); 
		//ambil start date
		$url = $this->API_URL.'/Masterkpi/GetKPIStartDate?api=APITES';
		$payload = array();
		$GetKPIStartDate = json_decode($this->_postRequest($url,$payload) ,true);
		if($GetKPIStartDate!='' && $GetKPIStartDate['code']==1){
			$GetKPIStartDate = $GetKPIStartDate['data'];
		}
		else{
			$GetKPIStartDate = array();
		}

		$params = array();	
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER KPI";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU MASTER KPI";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);
		
		$data['title'] = 'Master KPI | '.WEBTITLE;
		$data['start_date'] = $GetKPIStartDate;
		$this->RenderView('MasterkpiView',$data);
	}
	
	public function KPICategoryList(){
		$data = array(
			'code' => 0,
			'msg' => '',
			'data' => array(),
		);
		$msg = 'data tidak ditemukan';
		
        $isSalesman = (int)$this->input->get('is_salesman');
		//SALESMAN | NON-SALESMAN
		$logged_in = $_SESSION['logged_in'];
		$url = '';
		$payload = array();
		if($isSalesman==1){
			//salesman
			$url = $this->API_URL.'/Masterkpi/GetKpiCategory?api=APITES';
			$payload = array(
				'jenis' => 'SALESMAN',
			);
		}
		else{
			//bukan salesman
			$jenis = 'NON-SALESMAN';
			//START>> ambil kpi category
			$kpicategory = "ALL;;";
			$url = API_ZEN.'/ZenAPI/DivisionListUnderDivHead/'.$logged_in['userid'];
			$payload = array(
				'mode_kpi' => $jenis,
			);
			
			$getKpiCategory = json_decode($this->_postRequest($url,$payload),true);
			if($getKpiCategory!=null && $getKpiCategory['data']!=null){
				foreach($getKpiCategory['data'] as $value){
					$kpicategory .= $value['DivisionID'].";;";
				}
				$kpicategory = rtrim($kpicategory,';;');
			}
			//END<< ambil kpi category

			$url = $this->API_URL.'/Masterkpi/GetKpiCategory?api=APITES';
			$payload = array(
				'jenis' => 'NON-SALESMAN',
				'kpi_category' => $kpicategory,
			);
		}
		
		$GetKpiCategory = json_decode($this->_postRequest($url,$payload) ,true);
		if($GetKpiCategory!=null && $GetKpiCategory['code']==1){
			$data['data'] = $GetKpiCategory['data'];
			$data['code'] = 1;
			$msg = '';
		}
		else if($GetKpiCategory!=null && $GetKpiCategory['code']==0){
			$msg = $GetKpiCategory['msg'];
		}
		$data['msg'] = $msg;
		$json = json_encode($data);
		echo $json;
	}
		
	public function kpicategorysalesman(){
		$checkAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2)); 
		$jenis = 'SALESMAN';
		$url = $this->API_URL.'/Masterkpi/GetTargetSalesmanKpiLoadKategori?api=APITES';
		$payload = array(
			'mode_kpi' => $jenis,
		);
		$KPICategory = array();
		$getTargetSalesman = json_decode($this->_postRequest($url,$payload),true);				
		if($getTargetSalesman!='' && $getTargetSalesman['data']!=null){
			foreach($getTargetSalesman['data'] as $value){
				$KPICategory[] = array(
					'id' => $value['Kategori'],
					'name' => $value['Kategori'],
				);
			}
		}
	
		$body = array(
			'title' => 'KPI Category '.$jenis,
			'jenis' => $jenis,
			'is_salesman' => 1,
			'KPICategory' => $KPICategory,
		);

		$params = array();	
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER KPI KATEGORI";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU MASTER KPI KATEGORI";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		// echo json_encode($body); die;
		$this->RenderView('MasterKpiCategoryView',$body);
	}
	
	public function kpicategorykaryawan(){
		$checkAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2)); 
		$logged_in = $_SESSION['logged_in'];
		$KPICategory = array();

			$jenis = 'NON-SALESMAN';
			$url = API_ZEN.'/ZenAPI/DivisionListUnderDivHead/'.$logged_in['userid'];
			$payload = array(
				'mode_kpi' => $jenis,
			);
			// echo API_ZEN;die;
			// echo $this->_postRequest($url,$payload);die;
			$getTargetSalesman = json_decode($this->_postRequest($url,$payload),true);
			if($getTargetSalesman!='' && $getTargetSalesman['data']!=null){
				foreach($getTargetSalesman['data'] as $value){
					$KPICategory[] = array(
						'id' => $value['DivisionID'],
						'name' => $value['DivisionName'],
					);
				}
			}
		
		$body = array(
			'title' => 'KPI Category '.$jenis,
			'jenis' => $jenis,
				'is_salesman' => 0,
			'KPICategory' => $KPICategory,
		);

		$params = array();	
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER KPI KATEGORI KARYAWAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU MASTER KPI KATEGORI KARYAWAN";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		// echo json_encode($body); die;
		$this->RenderView('MasterKpiCategoryView',$body);

	}
	
	public function GetMasterKpiCategory(){
		$data = array(
			'code' => 0,
			'msg' => '',
			'data' => array(),
		);
		$msg = "data tidak ditemukan";

		$startDate = $this->input->post('start_date');
			
		if($startDate !=''){
			$url = $this->API_URL.'/Masterkpi/GetKpiCategory?api=APITES';
			$payload = array(
				'start_date' => $startDate,
				// 'jenis' => ($_SESSION['logged_in']['isSalesman']==1) ? "SALESMAN" : "NON-SALESMAN",
			);
			// echo $this->_postRequest($url,$payload);die;
			$GetKpiCategory = json_decode($this->_postRequest($url,$payload) ,true);
			
			if($GetKpiCategory!='' && $GetKpiCategory['code']==1){
				$GetKpiCategory = $GetKpiCategory['data'];
			}
			else{
				$GetKpiCategory = array();
			}
			//-------
			
			$url = $this->API_URL.'/Masterkpi/GetMasterKpiCategory?api=APITES';
			$payload = array(
				'start_date' => $startDate,
				'is_aktif' => 1
			);
			// echo $this->_postRequest($url,$payload); die;
			
			$result = json_decode($this->_postRequest($url,$payload) ,true);
			if($result!=null && $result['code']==1){
				$data['master_kpi_category'] = $result['data'];
				$data['kpi_category'] = $GetKpiCategory;
				$data['code'] = 1;
				$msg = "";
			}
		}
		$data['msg'] = $msg;
		$json = json_encode($data);
		echo $json;
	}
	
	public function GetKPIStartDate(){
		$data = array(
			'code' => 0,
			'msg' => '',
			'data' => array(),
		);
		$msg = "data tidak ditemukan";

		$startDate = $this->input->post('start_date');
		$date = DateTime::createFromFormat('d-M-Y', $startDate);
		if($date!='' && $date->format('d-M-Y')==$startDate){
			$startDate = $date->format('Y-m-d');
		}
		else{
			$startDate = '';
			$msg = "format tanggal salah";
		}
		if($startDate !=''){
			$url = $this->API_URL.'/Masterkpi/GetKpiCategory?api=APITES';
			$payload = array(
				'start_date' => $startDate,
				// 'jenis' => ($_SESSION['logged_in']['isSalesman']==1) ? "SALESMAN" : "NON-SALESMAN",
			);
			// echo $url;die;
			// echo $this->_postRequest($url,$payload);die;
			
			$GetKpiCategory = json_decode($this->_postRequest($url,$payload) ,true);
			
			
			if($GetKpiCategory!='' && $GetKpiCategory['code']==1){
				$GetKpiCategory = $GetKpiCategory['data'];
			}
			else{
				$GetKpiCategory = array();
			}
			//-------
			
			$url = $this->API_URL.'/Masterkpi/GetKPIStartDate?api=APITES';
			
			$payload = array();
			// echo $url; die;
			$result = json_decode($this->_postRequest($url,$payload) ,true);
			if($result!=null && $result['code']==1){
				$data['master_kpi_category'] = $result['data'];
				$data['kpi_category'] = $GetKpiCategory;
				$data['code'] = 1;
				$msg = "";
			}
		}
		$data['msg'] = $msg;
		$json = json_encode($data);
		echo $json;
	}
	
	public function KPICategoryMemberSave(){
		$url = $this->API_URL.'/Masterkpi/KPICategoryMemberSave';
		$post = $_POST;
		$post['api'] = 'APITES';
		$post['username'] = $_SESSION['logged_in']['username'];
		$result = $this->_postRequest($url, $post);
		echo $result;
	}
	
	public function KPICategoryAdd(){
		$url = $this->API_URL.'/Masterkpi/KPICategoryAdd';
		$post = $_POST;
		$post['api'] = 'APITES';
		$post['ModifiedBy'] = $_SESSION['logged_in']['username'];
		// echo json_encode($post);die;
		$result = $this->_postRequest($url, $post);
		echo $result;
	}
	
	public function KPICategoryUpdate(){
		$url = $this->API_URL.'/Masterkpi/KPICategoryUpdate';
		$post = $_POST;
		$post['api'] = 'APITES';
		$post['ModifiedBy'] = $_SESSION['logged_in']['username'];
		// echo json_encode($post);die;
		$result = $this->_postRequest($url, $post);
		echo $result;
	}
	
}