<?php 
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	class Masterkpicategoryv2 extends MY_Controller 
	{
		public function __construct()
		{
			parent::__construct();
			$this->load->model('Achievementkpikaryawanmodel');
			$this->load->model('approvalmodel');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
			$this->API_ZEN = $this->ConfigSysModel->Get()->zenhrs_url;
			$this->API_ZEN = 'http://localhost:90/ZenHRD';
		}

		private function _postRequest($url,$data=''){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$return = curl_exec($ch);
			curl_close($ch);
			return $return;
		}

	function index(){
		$checkAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2)); 
		$logged_in = $_SESSION['logged_in'];

		// $jenis = 'KARYAWAN';
		$jenis = '';

		$body = array(
			'title' => 'KPI Category '.$jenis,
			'jenis' => $jenis,
			'is_salesman' => 0,
			'proses' => 'list',
		);

		$params = array();	
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		// $params['Module'] = "MASTER KPI KATEGORI KARYAWAN";
		$params['Module'] = "MASTER KPI KATEGORI";
		$params['TrxID'] = date("YmdHis");
		// $params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU MASTER KPI KATEGORI KARYAWAN";
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU MASTER KPI KATEGORI ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		// echo json_encode($body); die;
		$this->RenderView('MasterKpiCategoryViewv2',$body);
	}

		function add(){
			$checkAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2)); 
			$KPICategory = array();

				$listjenis = ['SALESMAN','NON-SALESMAN'];
				// $url = $this->API_ZEN.'/ZenAPI/DivisionListUnderDivHead/';
				$url = $this->API_ZEN.'/ZenAPI/DivisionListHead/';

				$getTargetSalesman = json_decode($this->_postRequest($url),true);
				if($getTargetSalesman!='' && $getTargetSalesman['data']!=null){
					foreach($getTargetSalesman['data'] as $value){
						$KPICategory[] = array(
							'id' => $value['DivisionID'],
							'name' => $value['DivisionName'],
							'userid' => $value['DivHead'],
							'empname' => $value['EmpName'],
						);
					}
				}
			
			$body = array(
				'title' => 'KPI Category',
				'listjenis' => $listjenis,
				'is_salesman' => 0,
				'KPICategory' => $KPICategory,
				'proses' => 'add',
			);

			$this->RenderView('MasterKpiCategoryViewv2',$body);
		}

		function edit($KPIID){

			$listjenis = ['SALESMAN','NON-SALESMAN'];

			$KPICategory = array();

			$urlheader = $this->API_URL.'/Masterkpi/listKPICategori/';

			// $urldivision = $this->API_ZEN.'/ZenAPI/DivisionListUnderDivHead/';
			$urldivision = $this->API_ZEN.'/ZenAPI/DivisionListHead/';

			$getdivisi = json_decode($this->_postRequest($urldivision),true);

			if($getdivisi!='' && $getdivisi['data']!=null){
				foreach($getdivisi['data'] as $value){
					$KPICategory[] = array(
						'id' => $value['DivisionID'],
						'name' => $value['DivisionName'],
						'userid' => $value['DivHead'],
						'empname' => $value['EmpName'],
					);
				}
			}

			$data['api'] = 'APITES';
			$data['KPICategoryID'] = base64_decode($KPIID);

			$ListKPICategoryHeader=array();
			$ListKPICategory=array();

			$getdatadivisi = json_decode($this->_postRequest($urlheader,$data),true);

			if($getdatadivisi!=null){
				$no=0;
				foreach($getdatadivisi as $a){

					if($no==0){
						$ListKPICategoryHeader[] = array(

							'KPICategoryID' => $a['KPICategoryID'],
							'KPICategory' => $a['KPICategory'],
							'KPICategoryName' => $a['KPICategoryName'],
							'IsActive' => $a['IsActive'],
							'jenis' => $a['Jenis']
						);
						$no++;
					}

					if(!empty($a['DivisionID'])){
						$ListKPICategory[] = array(

							'StartDate' => $a['StartDate'],
							'DivisionID' => $a['DivisionID'],
							'DivisionName' => $a['DivisionName'],
							'aktif' => $a['aktif']
						);
					}
				}
			}else{
				redirect(site_url('Masterkpicategoryv2'));
			}

			$KPICategoryHeader='';
			
			$body = array(
				'title' => 'KPI Category ',
				'listjenis' => $listjenis,
				'is_salesman' => 0,
				'header' => $ListKPICategoryHeader,
				'body' => $ListKPICategory,
				'KPICategory' => $KPICategory,
				'proses' => 'edit',
			);			
			$this->RenderView('MasterKpiCategoryViewv2',$body);
		}

		function view($KPIID){

			$listjenis = ['SALESMAN','NON-SALESMAN'];

			$KPICategory = array();

			$jenis = '';

			$urlheader = $this->API_URL.'/Masterkpi/listKPICategori/';

			$urldivision = $this->API_ZEN.'/ZenAPI/DivisionListHead/';

			$getdivisi = json_decode($this->_postRequest($urldivision),true);
			if($getdivisi!='' && $getdivisi['data']!=null){
				foreach($getdivisi['data'] as $value){
					$KPICategory[] = array(
						'id' => $value['DivisionID'],
						'name' => $value['DivisionName'],
						'userid' => $value['DivHead'],
						'empname' => $value['EmpName'],
					);
				}
			}

			$data['api'] = 'APITES';
			$data['KPICategoryID'] = base64_decode($KPIID);

			$ListKPICategoryHeader=array();
			$ListKPICategory=array();

			$getdatadivisi = json_decode($this->_postRequest($urlheader,$data),true);
			if($getdatadivisi!=null){
				$no=0;
				foreach($getdatadivisi as $a){

					if($no==0){
						$ListKPICategoryHeader[] = array(

							'KPICategoryID' => $a['KPICategoryID'],
							'KPICategory' => $a['KPICategory'],
							'KPICategoryName' => $a['KPICategoryName'],
							'IsActive' => $a['IsActive'],
							'jenis' => $a['Jenis']

						);
						$no++;
					}

					$ListKPICategory[] = array(

						'StartDate' => $a['StartDate'],
						'DivisionID' => $a['DivisionID'],
						'DivisionName' => $a['DivisionName'],
						'aktif' => $a['aktif']
					);

					$jenis = $a['Jenis'];
				}
			}else{
				redirect(site_url('Masterkpicategoryv2'));
			}

			$KPICategoryHeader='';
			
			$body = array(
				'title' => 'KPI Category ',
				'listjenis' => $listjenis,
				'is_salesman' => 0,
				'header' => $ListKPICategoryHeader,
				'body' => $ListKPICategory,
				'KPICategory' => $KPICategory,
				'proses' => 'view',
			);			
			$this->RenderView('MasterKpiCategoryViewv2',$body);
		}

		public function prosesadd(){
			$url = $this->API_URL.'/Masterkpi/KPICategoryAdd2';
			$post = $_POST;
			$post['api'] = 'APITES';
			$post['jenis']='KARYAWAN';
			$post['ModifiedBy']=$_SESSION['logged_in']['userid'];
			$result = $this->_postRequest($url, $post);
			echo $result;
		}
		
		public function prosesupdate(){
			$url = $this->API_URL.'/Masterkpi/KPICategoryUpdate2';
			$post = $_POST;
			$post['api'] = 'APITES';
			$post['ModifiedBy'] = $_SESSION['logged_in']['userid'];
			$result = $this->_postRequest($url, $post);
			echo $result;
		}

		public function KPICategoryList(){
			$data = array(
				'code' => 0,
				'msg' => '',
				'data' => array(),
			);
			$msg = 'data tidak ditemukan';
			
	        $isSalesman = (int)$this->input->get('is_salesman');
			$logged_in = $_SESSION['logged_in'];
			$url = '';
			$payload = array();

			$url = $this->API_URL.'/Masterkpi/GetKpiCategory?api=APITES';
				$payload = array(
					'jenis' => 'KARYAWAN',
				);

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

	}
?>
