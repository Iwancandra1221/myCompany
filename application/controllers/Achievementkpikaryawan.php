<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
class Achievementkpikaryawan extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Achievementkpikaryawanmodel');
		$this->load->model('approvalmodel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		$this->API_ZEN = $this->ConfigSysModel->Get()->zenhrs_url;
	}
	
	public function index()
	{ 
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ACHIEVEMENT KPI KARYAWAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU ACHIEVEMENT KPI KARYAWAN ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		// echo json_encode($_SESSION); die;
		$post['api'] = 'APITES';
		$post['jenis'] = ($_SESSION['logged_in']['isSalesman']==1) ? 'SALESMAN':'NON-SALESMAN';
		//------------------------------------------------
		if(!ISSET($_SESSION['logged_in']['DivisionListUnderDivHead'])){
			// Ambil List Division user dan divisi di bawah nya
			$URL = $this->API_ZEN."/Zenapi/DivisionListUnderDivHead/".$_SESSION['logged_in']['userid'];
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $response; die;
			if($httpcode!=200){

				// ActivityLog Update FAILED
				$params['Remarks']='FAILED - Web ZEN sedang tidak bisa diakses! HTTP Code:'.$httpcode;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				echo 'Web ZEN sedang tidak bisa diakses! HTTP Code:'.$httpcode; die;
			}
			else{
				$result = json_decode($response);
				if($result->result =='sukses'){
					// simpan  divisionid ke dalam session supaya tidak hit API ZEN setiap reload halaman
					$_SESSION['logged_in']['DivisionListUnderDivHead'] = $result->data;
				}
				else{

					// ActivityLog Update FAILED
					$params['Remarks']='FAILED - Ambil List Divisi gagal. Error: '.$result->error;
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

					echo 'Ambil List Divisi gagal. Error: '.$result->error; die;
				}
			}
		}
		$employee = array();
		$member_include = array();
		
		if(ISSET($_SESSION['logged_in']['DivisionListUnderDivHead'])){		
			//Ubah DivisionID menjadi array 1 dimensi
			$divisionid = array_column($_SESSION['logged_in']['DivisionListUnderDivHead'], 'DivisionID');
			$divisionid = array_values($divisionid);
			// echo json_encode($divisionid); die;
			$post['divisionid'] = $divisionid;
		}
		// ------------------------------------------------
		$URL = $this->API_URL."/index.php/Achievementkpikaryawan/Kategori";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $response; die;
		
		if($httpcode!=200){
 
			// ActivityLog Update FAILED
			$params['Remarks']='FAILED - URL "'.$URL.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			echo 'URL "'.$URL.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode; die;
		}
		else{
			$result = json_decode($response);
			if($result->result =='success'){
				$data['KPICategory'] = $result->KPICategory;
			}
			else{
				// ActivityLog Update FAILED
				$params['Remarks']='FAILED - '.$result->error;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				echo $result->error; die;
			}
		}
		
		$data['atasan'] = $this->ZenGetAtasan(date('Y-m-01'));
		$data['title'] = 'ACHIEVEMENT KPI KARYAWAN';
		$data['api_zen'] = $this->API_ZEN;
		// echo json_encode($data);die;

		// ActivityLog Update SUCCESS
		$params['Remarks']='SUCCESS';
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView('AchievementKPIKaryawanView',$data);
	}
	
	public function ListAchievementKPIKaryawan()
	{
		// echo json_encode($_SESSION); die;
		$post['api'] = 'APITES';
		$post['userid'] = $_SESSION['logged_in']['userid'];
		$post['kategori'] = $this->input->post('kategori');
		$post['nama_kategori'] = $this->input->post('nama_kategori');
		$post['th'] = $this->input->post('th');
		$post['bl'] = $this->input->post('bl');	
		
		$tgl_awal = date('Y-m-d', strtotime($post['th'].'-'.$post['bl'].'-01'));
		$tgl_akhir = date('Y-m-d', strtotime("+1 months", strtotime($tgl_awal)));
		$tgl_akhir = date('Y-m-d', strtotime("-1 day", strtotime($tgl_akhir)));
		
		
		$load_bawahan = $this->TargetKaryawan_KPI_LoadBawahan($post['userid'],$post['kategori'], $post['th'], $post['bl']);
		// $master_kpi = $this->Master_KPI_AmbilList($post['kategori']);
		// $template = $this->Master_Template_Target_KPI_AmbilList($post['kategori'], $post['th'], $post['bl']);
		$atasan = $this->ZenGetAtasan($tgl_awal);
		
		echo json_encode(array('bawahan'=>$load_bawahan,'atasan'=>$atasan));
	}
	
	public function TargetKaryawan_KPI_LoadBawahan($userid, $kategori, $th, $bl)
	{
		// echo json_encode($_SESSION); die;
		$post['api'] = 'APITES';
		$post['userid'] = $userid;
		$post['kategori'] = $kategori;
		$post['th'] = $th;
		$post['bl'] = $bl;		
		//------------------------------------------------
		$URL = $this->API_URL."/index.php/Achievementkpikaryawan/TargetKaryawan_KPI_LoadBawahan";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $response; die;
		
		if($httpcode!=200){
			return array();
		}
		else{
			$result = json_decode($response, true);
			
			if($result['result'] =='success'){
				return $result['data'];
			}
			else{
				return array();
			}
		}
	}
	
	public function TargetKaryawan_KPI_AmbilAchievementDetail()
	{
		// echo json_encode($_SESSION); die;
		$post['api'] = 'APITES';
		$post['KodeTarget'] = $this->input->post('KodeTarget');
		$post['NoRequestKPI'] =  $this->input->post('NoRequestKPI');
		$post['NoRequestAcv'] =  $this->input->post('NoRequestAcv');
		//------------------------------------------------
		$URL = $this->API_URL."/index.php/Achievementkpikaryawan/TargetKaryawan_KPI_AmbilAchievementDetail";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		echo $response; die;	
		
		if($httpcode!=200){
			echo json_encode(array('detail'=>array(),'history'=>array()));
		}
		else{
			$result = json_decode($response, true);
			if($result['result'] =='success'){
				echo json_encode(array('detail'=>$result['detail'],'history'=>$result['history']));
			}
			else{
				echo json_encode(array('detail'=>array(),'history'=>array()));
			}
		}
	}
	
	public function Master_Template_Target_KPI_AmbilList($kategori, $th, $bl)
	{
		// echo json_encode($_SESSION); die;
		$post['api'] = 'APITES';
		$post['kategori'] = $kategori;
		$post['th'] = $th;
		$post['bl'] = $bl;
		//------------------------------------------------
		$URL = $this->API_URL."/index.php/Achievementkpikaryawan/Master_Template_Target_KPI_AmbilList";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $response; die;	
		
		if($httpcode!=200){
			return array();
		}
		else{
			$result = json_decode($response, true);
			if($result['result'] =='success'){
				return $result['data'];
			}
			else{
				return array();
			}
		}
	}
	
	public function Master_Template_Target_KPI_AmbilList_Detail()
	{
		// echo json_encode($_SESSION); die;
		$post['api'] = 'APITES';
		$post['template_id'] = $this->input->post('template_id');
		//------------------------------------------------
		$URL = $this->API_URL."/index.php/Achievementkpikaryawan/Master_Template_Target_KPI_AmbilList_Detail";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $response; die;	
		
		if($httpcode!=200){
			echo json_encode(array());
		}
		else{
			$result = json_decode($response, true);
			if($result['result'] =='success'){
				echo json_encode($result['data']);
			}
			else{
				echo json_encode(array());
			}
		}
	}
	
	public function SetKodeTarget($userid, $th, $bl)
	{
		// echo json_encode($_SESSION); die;
		$post['api'] = 'APITES';
		$post['userid'] = $userid;
		$post['th'] = $th;
		$post['bl'] = $bl;		
		//------------------------------------------------
		$URL = $this->API_URL."/index.php/Achievementkpikaryawan/SetKodeTarget";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $response; die;
		
		if($httpcode!=200){
				return '';
		}
		else{
			$result = json_decode($response);
			if($result->result =='success'){
				return $result->data;
			}
			else{
				return '';
			}
		}
	}
	
	public function Master_KPI_AmbilList($kategori)
	{
		// echo json_encode($_SESSION); die;
		$post['api'] = 'APITES';
		$post['kategori'] = $kategori;
		//------------------------------------------------
		$URL = $this->API_URL."/index.php/Achievementkpikaryawan/Master_KPI_AmbilList";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $response; die;
		
		if($httpcode!=200){
			return array();
		}
		else{
			$result = json_decode($response);
			if($result->result =='success'){
				return $result->data;
			}
			else{
				return array();
			}
		}
	}
	
	public function ZenGetAtasan($HistoryDate='2023-01-01')
	{	
		$atasan = array('UserApproval'=>'NOT FOUND','Name'=>'NOT FOUND','UserEmail'=>'NOT FOUND');
		$URL = $this->API_ZEN."/Zenapi/GetEmployeeHead?EventID=LEAVE&HistoryDate=".$HistoryDate."&UserID=".$_SESSION['logged_in']['userid'];
		// echo $URL;die;
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $response; die;
		if($httpcode==200){
			$result = json_decode($response, true);
			$atasan = ISSET($result[0]) ? $result[0] : $atasan;
		}
		return $atasan;
	}	
	
	public function save()
	{

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ACHIEVEMENT KPI KARYAWAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." SIMPAN ACHIEVEMENT KPI KARYAWAN ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$post = $this->PopulatePost();
		$post['api'] = 'APITES';
		$post['spv_userid'] = $_SESSION['logged_in']['userid'];
		$post['spv_name'] = $_SESSION['logged_in']['username'];
		$post['spv_email'] = $_SESSION['logged_in']['useremail'];
		$post['user'] = $_SESSION['logged_in']['username'];
		$post['kode_lokasi'] = $_SESSION['logged_in']['branch_id'];
		// echo json_encode($post); die;
		//------------------------------------------------
		$URL = $this->API_URL."/index.php/Achievementkpikaryawan/save";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		echo $response; die;
		
		if($httpcode!=200){
			// ActivityLog Update FAILED
			$params['Remarks']='FAILED - Save Request Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			echo json_encode(array('result'=>'failed','error'=>'Save Request Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err));
		}
		else{
			// ActivityLog Update SUCCESS
			$params['Remarks']='SUCCESS';
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$result = json_decode($response, true);
			echo json_encode($result);
		}
	}
	
	public function sendrequest()
	{

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ACHIEVEMENT KPI KARYAWAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." SEND REQUEST ACHIEVEMENT KPI KARYAWAN ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		// echo json_encode($_SESSION); die;
		$post = $this->PopulatePost();
		$NoRequestAcv = $_SESSION['logged_in']['userid'].'_A'.date('YmdHis')	;	
		$post['api'] = 'APITES';
		$post['norequestacv'] = $NoRequestAcv;
		$post['kode_lokasi'] = $_SESSION['logged_in']['branch_id'];
		$post['user_name'] = $_SESSION['logged_in']['username'];
		$post['user_email'] = $_SESSION['logged_in']['useremail'];
		// echo json_encode($post); die;
		//------------------------------------------------
		$URL = $this->API_URL."/index.php/Achievementkpikaryawan/sendrequest";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $response; die;	
		
		if($httpcode!=200){

			// ActivityLog Update FAILED
			$params['Remarks']='FAILED - Request Target KPI Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			echo json_encode(array('result'=>'failed','error'=>'Request Target KPI Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err));
		}
		else{
			//{"result":"success","error":"","is_emailed":0}
			$result = json_decode($response, true); 
			if($result['result']=='success'){
				$data['ApprovalType']='KPI KARYAWAN';
				$data['RequestNo']=$post['norequestacv'];
				$data['RequestBy']=$post['user_name'];
				$data['RequestDate']=date('Y-m-d H:i:s');
				$data['RequestByName']=$post['user_name'];
				$data['RequestByEmail']=$post['user_email'];
				$data['ApprovedBy']=$post['atasan_name'];
				$data['ApprovedByName']=$post['atasan_name'];
				$data['ApprovedByEmail']=$post['atasan_email'];
				$data['ApprovalStatus']='UNPROCESSED';
				$data['AddInfo2']='Divisi';
				$data['AddInfo2Value']=$post['kpi_kategori'].' - '.$post['kpi_kategori_name'];
				// $data['AddInfo4']='HTML Content';
				// $data['AddInfo4Value']='';
				$data['AddInfo6']='Periode';
				$data['AddInfo6Value']=$post['periode'];
				$data['AddInfo9']='Wilayah';
				$data['AddInfo9Value']=$_SESSION['logged_in']['branch'];
				$data['AddInfo12']='Week';
				$data['AddInfo12Value']=$post['week'];
				$data['ApprovalNeeded']=1;
				$data['Priority']=1;
				$data['BhaktiFlag']='UNPROCESSED';
				$data['IsCancelled']=0;
				$data['LocationCode']='HO';
				$data['IsEmailed']=$result['is_emailed'];
				$data['EmailedDate']=($result['is_emailed']==1)?date('Y-m-d H:i:s'):NULL;
				// $result = $this->insert_approval($data);
			}

			// ActivityLog Update SUCCESS
			$params['Remarks']='SUCCESS';
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			echo json_encode($result);
		}
	}		
	
	public function cancel()
	{

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ACHIEVEMENT KPI KARYAWAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." CANCEL ACHIEVEMENT KPI KARYAWAN ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		// echo json_encode($_SESSION); die;
		$post = $this->PopulatePost();
		$post['api'] = 'APITES';
		$post['user'] = $_SESSION['logged_in']['username'];
		$post['api_zen'] = $this->API_ZEN;
		// echo json_encode($post); die;
		//------------------------------------------------
		$URL = $this->API_URL."/index.php/Achievementkpikaryawan/cancel";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $response; die;
		
		if($httpcode!=200){

			// ActivityLog Update FAILED
			$params['Remarks']='FAILED - Cancel Request Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			echo json_encode(array('result'=>'failed','error'=>'Cancel Request Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err));
		}
		else{
			$result = json_decode($response, true);
			if(ISSET($result['delete_approval']) && $result['delete_approval']==true){
				$data = array();
				$data['ApprovalType'] = 'KPI KARYAWAN';
				$data['RequestNo'] = $post['norequestacv'];
				$data['CancelledBy'] = $_SESSION['logged_in']['username'];
				$data['CancelledByName'] = $_SESSION['logged_in']['username'];
				$data['CancelledNote'] = $post['note'];
				$data['CancelledByEmail'] = $_SESSION['logged_in']['useremail'];
				// $result = $this->cancel_approval($data);
			}


			// ActivityLog Update SUCCESS
			$params['Remarks']='SUCCESS';
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			echo json_encode($result);
		}
	}
	
	public function insert_approval($data)
	{

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ACHIEVEMENT KPI KARYAWAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." Insert APPROVAL ACHIEVEMENT KPI KARYAWAN ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$result = $this->approvalmodel->doaction('insert', $data);
		if($result['pesan'] == 'Request Ini Berhasil Diinsert'){

			// ActivityLog Update SUCCESS
			$params['Remarks']='SUCCESS';
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			return array('result'=>'success','error'=>'');
		}
		else{

			// ActivityLog Update FAILED
			$params['Remarks']='FAILED';
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			return array('result'=>'failed','error'=>$result['pesan']);
		}
	}
	
	public function cancel_approval($data)
	{
 
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ACHIEVEMENT KPI KARYAWAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." CANCEL APPROVAL ACHIEVEMENT KPI KARYAWAN ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$result = $this->approvalmodel->doaction('cancel', $data);
		if($result['pesan'] == 'Request Ini Berhasil Dicancel'){

			// ActivityLog Update SUCCESS
			$params['Remarks']='SUCCESS';
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			return array('result'=>'success','error'=>'');
		}
		else{

			// ActivityLog Update FAILED
			$params['Remarks']='FAILED - '.$result['pesan'];
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			return array('result'=>'failed','error'=>$result['pesan']);
		}
	}
	
	//--------------APPROVAL--------------------------------------------------------------------------------------
	
	public function ProsesAchievementKPI()
	{
		$noRequest = urldecode($this->input->get("req"));
		$approvedBy = urldecode($this->input->get("app"));
		$totalWeek = urldecode($this->input->get("week"));
		$this->ViewRequestAchievementKPI($noRequest, $approvedBy, $totalWeek);
	}

	public function ViewRequestAchievementKPI($noRequest, $approvedBy, $totalWeek, $msg="") 
	{
		$URL = $this->API_URL."/Achievementkpikaryawan/AmbilAchievementKPI?req=".urlencode($noRequest)."&app=".urldecode($approvedBy);
		// echo($URL);die;
		$GetRequest = json_decode(file_get_contents($URL), true);
		// echo(json_encode($GetRequest));die;

		if($GetRequest["result"]=="SUCCESS") {

			$req = $GetRequest["data"];

			$style = '<style>
				*{
					font-family:"Arial",sans-serif;
					font-size:14px;
				}
				table{
					border-collapse:collapse;
				}
				table th, table td{
					border:1px solid #ddd;
					padding:5px;
				}
				table th {
					text-align:center;
				}
				table tr:hover {
					/*background:#f8f8f8;*/
				}
				table tr:nth-child(even) {
					background:#f8f8f8;
				}
				.target { background:#edf1f2; }
				.achievement { background:#ccf2ff; }
				.final { background:#b3dae8; }
				.modified { font-size:8pt;}
				.closed { background:#faacc9; }
			</style>';
			
			$BL = $req["Bulan"];
			$NM_BL = array("", "JANUARI", "FEBRUARI", "MARET", "APRIL", "MEI", "JUNI", "JULI", "AGUSTUS", "SEPTEMBER", "OKTOBER", "NOVEMBER", "DESEMBER");

			$header = "<h2>REQUEST APPROVAL ACHIEVEMENT KPI</h2><hr>";
			$header.= "No Request: <b>".$req["NoRequestKPI"]."</b><br>";
			$header.= "Tgl Request: <b>".date("d-M-Y H:i:s", strtotime($req["RequestSentDate"]))."</b><br>";
			$header.= "Dikirimkan Oleh: <b style='background:yellow;'>".$req["RequestSentBy"]."</b><br><hr><br>";
			$header.= "Cabang: <b style='background:yellow;'>".$req["Cabang"]."</b><br>";
			$header.= "Periode: <b style='background-color:yellow;'>".$NM_BL[$BL]." ".$req["Tahun"]."</b><br>";  
			$header.= "Banyak Karyawan: <b>".count($req["ListKPI"])."</b><br><br>";

			$detail = "";
			$detailhd = "";
			$detaildt = "";
			$details = $req["ListKPI"];

			$No = 0;

			$detailhd.= "<table>";
			$detailhd.= "<tr>";
			$detailhd.= "   <th width='4%''>No</th>";
			$detailhd.= "   <th width='10%'>Karyawan</th>";
			$detailhd.= "   <th width='12%'>Key Performance Indicator</th>";
			$detailhd.= "   <th width='8%'>Total Target</th>";
			$detailhd.= "   <th width='4%'>%Bobot</th>";
			$detailhd.= "   <th width='8%'>Week1</th>";
			$detailhd.= "   <th width='8%'>Week2</th>";
			$detailhd.= "   <th width='8%'>Week3</th>";
			$detailhd.= "   <th width='8%'>Week4</th>";
			if ($totalWeek>=5) $detailhd.= "   <th width='8%'>Week5</th>";
			if ($totalWeek==6) $detailhd.= "   <th width='8%'>Week6</th>";
			$detailhd.= "   <th width='8%'>TotalAcv</th>";
			$detailhd.= "   <th width='4%'>%</th>";
			$detailhd.= "   <th width='4%'>%Bobot</th>";
			$detailhd.= "   <th width='4%'>%</th>";
			$detailhd.= "   <th width='2%'></th>";
			$detailhd.= "</tr>";

			$TotalWaiting = 0;

			for($i=0; $i<count($details); $i++) {
				$No+= 1;
				$TotalBobot = 0;
				$dt = $details[$i];
				$detaildt = "";
				$ApprovalHistory = $dt["APPROVALHISTORY"];
				$l = count($ApprovalHistory);
				$HISTORY = "";
				if ($l>0) {
					for($j=0; $j<$l; $j++) {
						$HistoryStatus = $ApprovalHistory[$j]["HistoryStatus"];
						$HistoryDate = date("d-M-Y H:i:s", strtotime($ApprovalHistory[$j]["HistoryDate"]));
						$UserName = $ApprovalHistory[$j]["UserName"];
						$HistoryNote = (($ApprovalHistory[$j]["HistoryNote"]==null)?"":$ApprovalHistory[$j]["HistoryNote"]);
						$HISTORY.= $HistoryDate." - ".$UserName." - ".$HistoryStatus."[".$HistoryNote."]<br>"; 
					}
				} else {
					$HISTORY = "-";
				}

				$KPIs = $dt["DETAILS"];
				$k = count($KPIs);
				
				for($j=1; $j<$k;$j++) {
					$detaildt.= "<tr>";
					$detaildt.= "   <td class='target'>".$KPIs[$j]["KPIName"]."</td>";
					$detaildt.= "   <td align='right' class='target'>".number_format($KPIs[$j]["KPITarget"],2)."</td>";
					$detaildt.= "   <td align='right' class='target'>".number_format($KPIs[$j]["KPIBobot"],0)."</td>";
					$detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek1"],2).
									"<br><span class='modified'>/".number_format($KPIs[$j]["TargetWeek1"],2)."<br>".number_format($KPIs[$j]["PersenWeek1"],2)."%</span></td>";
					$detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek2"],2).
									"<br><span class='modified'>/".number_format($KPIs[$j]["TargetWeek2"],2)."<br>".number_format($KPIs[$j]["PersenWeek2"],2)."%</span></td>";
					$detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek3"],2).
									"<br><span class='modified'>/".number_format($KPIs[$j]["TargetWeek3"],2)."<br>".number_format($KPIs[$j]["PersenWeek3"],2)."%</span></td>";
					$detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek4"],2).
									"<br><span class='modified'>/".number_format($KPIs[$j]["TargetWeek4"],2)."<br>".number_format($KPIs[$j]["PersenWeek4"],2)."%</span></td>";
					if ($totalWeek>=5) $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek5"],2).
									"<br><span class='modified'>/".number_format($KPIs[$j]["TargetWeek5"],2)."<br>".number_format($KPIs[$j]["PersenWeek5"],2)."%</span></td>";
					if ($totalWeek==6) $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek6"],2).
									"<br><span class='modified'>/".number_format($KPIs[$j]["TargetWeek6"],2)."<br>".number_format($KPIs[$j]["PersenWeek6"],2)."%</span></td>";
					$detaildt.= "   <td align='right' class='achievement'><b>".number_format($KPIs[$j]["AcvTotal"],2)."</b></td>";
					$detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvPersen"],2)."</td>";
					$detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvBobot"],2)."</td>";
					$detaildt.= "</tr>";
				}
				
				$k= ($k==0) ? 1 : $k;
				
				$detailhd.="<tr>";
				$detailhd.="    <td rowspan='".$k."' class='target'>".$No."</td>";
				$detailhd.="    <td rowspan='".$k."' class='target'>".$dt["NAMA"];   
				if($dt["EXCLUDETUNJANGANPRESTASI"]=='1'){
					$detailhd.="<br><span class='modified' style='color:red'>(Exclude Tunjangan Prestasi)</span>";
				}
				$detailhd.="    </td>";    

				if ($dt["REQUESTSTATUS"]=="CANCELLED") {
					if ($totalWeek==5) {
						$detailhd.= "   <td colspan='11' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CANCELLEDBY"]." ".$dt["CANCELLEDDATE"]." ".$dt["CANCELLEDNOTE"]."</td>";
					} else if ($totalWeek==6) {
						$detailhd.= "   <td colspan='12' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CANCELLEDBY"]." ".$dt["CANCELLEDDATE"]." ".$dt["CANCELLEDNOTE"]."</td>";
					} else {                        
						$detailhd.= "   <td colspan='10' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CANCELLEDBY"]." ".$dt["CANCELLEDDATE"]." ".$dt["CANCELLEDNOTE"]."</td>";
					}
					$detailhd.="    <td align='right' class='final' rowspan='".$k."'><b></b></td>";
				} else if ($dt["REQUESTSTATUS"]=="CLOSED") {
					if ($totalWeek==5) {
						$detailhd.= "   <td colspan='11' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CLOSEDBY"]." ".$dt["CLOSEDDATE"]." ".$dt["CLOSEDNOTE"]."</td>";
					} else if ($totalWeek==6) {
						$detailhd.= "   <td colspan='12' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CLOSEDBY"]." ".$dt["CLOSEDDATE"]." ".$dt["CLOSEDNOTE"]."</td>";
					} else {                        
						$detailhd.= "   <td colspan='10' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CLOSEDBY"]." ".$dt["CLOSEDDATE"]." ".$dt["CLOSEDNOTE"]."</td>";
					}
					$detailhd.="    <td align='right' class='final' rowspan='".$k."'><b></b></td>";
				} else {    
					$detailhd.= "   <td class='target'>".$KPIs[0]["KPIName"]."</td>";
					$detailhd.= "   <td align='right' class='target'>".number_format($KPIs[0]["KPITarget"],2)."</td>";
					$detailhd.= "   <td align='right' class='target'>".number_format($KPIs[0]["KPIBobot"],0)."</td>";
					// $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek1"],2)."<br><br><span class='modified'>".$KPIs[0]["Week1ModifiedBy"]."<br>".$KPIs[0]["Week1ModifiedDate"]."</span></td>";
					// $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek2"],2)."<br><br><span class='modified'>".$KPIs[0]["Week2ModifiedBy"]."<br>".$KPIs[0]["Week2ModifiedDate"]."</span></td>";
					// $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek3"],2)."<br><br><span class='modified'>".$KPIs[0]["Week3ModifiedBy"]."<br>".$KPIs[0]["Week3ModifiedDate"]."</span></td>";
					// $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek4"],2)."<br><br><span class='modified'>".$KPIs[0]["Week4ModifiedBy"]."<br>".$KPIs[0]["Week4ModifiedDate"]."</span></td>";
					// if ($totalWeek>=5) $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek5"],2)."<br><br><span class='modified'>".$KPIs[0]["Week5ModifiedBy"]."<br>".$KPIs[0]["Week5ModifiedDate"]."</span></td>";
					// if ($totalWeek==6) $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek6"],2)."<br><br><span class='modified'>".$KPIs[0]["Week6ModifiedBy"]."<br>".$KPIs[0]["Week6ModifiedDate"]."</span></td>";
					$detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek1"],2).
									"<br><span class='modified'>/".number_format($KPIs[0]["TargetWeek1"],2)."<br>".number_format($KPIs[0]["PersenWeek1"],2)."%</span></td>";
					$detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek2"],2).
									"<br><span class='modified'>/".number_format($KPIs[0]["TargetWeek2"],2)."<br>".number_format($KPIs[0]["PersenWeek2"],2)."%</span></td>";
					$detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek3"],2).
									"<br><span class='modified'>/".number_format($KPIs[0]["TargetWeek3"],2)."<br>".number_format($KPIs[0]["PersenWeek3"],2)."%</span></td>";
					$detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek4"],2).
									"<br><span class='modified'>/".number_format($KPIs[0]["TargetWeek4"],2)."<br>".number_format($KPIs[0]["PersenWeek4"],2)."%</span></td>";
					if ($totalWeek>=5)
					$detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek5"],2).
									"<br><span class='modified'>/".number_format($KPIs[0]["TargetWeek5"],2)."<br>".number_format($KPIs[0]["PersenWeek5"],2)."%</span></td>";
					if ($totalWeek==6)
					$detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek6"],2).
									"<br><span class='modified'>/".number_format($KPIs[0]["TargetWeek6"],2)."<br>".number_format($KPIs[0]["PersenWeek6"],2)."%</span></td>";
									
					$detailhd.= "   <td align='right' class='achievement'><b>".number_format($KPIs[0]["AcvTotal"],2)."</b></td>";
					$detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvPersen"],2)."</td>";
					$detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvBobot"],2)."</td>";
					$detailhd.="    <td align='right' class='final' rowspan='".$k."'><b>".number_format($dt["TOTAL_ACHIEVEMENT"], 0)."</b></td>";
				}
				if ($dt["STATUS"]=="WAITING FOR APPROVAL") {
					$TotalWaiting += 1;
					$detailhd .= "<td rowspan='".$k."'><input type='checkbox' class='cek_pilih' name='karyawan[]' value='".$dt["KODE_TARGET"]."' onchange='cek()' checked></td>";
				} else if ($dt["STATUS"]=="CANCELLED") {
					$detailhd .= "<td rowspan='".$k."'>CANCELLED</td>";    
				} else if ($dt["STATUS"]=="CLOSED") {
					$detailhd .= "<td rowspan='".$k."'>CLOSED</td>"; 
				} else if ($dt["STATUS"]=="REJECTED") {
					$detailhd .= "<td rowspan='".$k."'>REJECTED</td>"; 
				} else if ($dt["STATUS"]=="APPROVED") {
					$detailhd .= "<td rowspan='".$k."'>APPROVED</td>";
				} else {
					$detailhd .= "<td rowspan='".$k."'></td>";
				}
				// $detailhd.="    <td rowspan='".$k."'>".$HISTORY."</td>";
				$detailhd.="</tr>";         
				$detailhd.=$detaildt; 
			}
			$detailhd.="</table>";
			
			$detail = "
			<form action='./ApproveRejectAchievement' method='POST'>
			<div style='padding:10px;background:#FFF;border:1px solid #f5c6cb;'>".$detailhd."
			<input type='hidden' name='no_request' value='".$noRequest."'>
			<input type='hidden' name='app_by' value='".$approvedBy."'>
			<input type='hidden' name='total_week' value='".$totalWeek."'>
			<input type='hidden' name='req_json' value='".json_encode($req)."'>
			<br>";


			if ($TotalWaiting>0) {
				$detail.= "<span style='float:right'><em>(jika tidak pilih = REJECT)</em> Pilih Semua <input type='checkbox' id='cbx_all' onchange='cek_all()' checked></span>
					<br>
					REJECT NOTE (wajib diisi jika reject)<br>
					<input type='input' name='rejectnote' id='rejectnote' style='width:100%;padding:5px;margin-bottom:10px' disabled>
					<center><input type='submit' id='btn_submit' value='APPROVE' style='color:#fff; padding:10px; background:green; border:1px solid #555; text-align:center;' ></center>
					</div>";
				
				$script= '
				<script type="text/javascript">
				function cek_all() {
					var source = document.getElementById("cbx_all");
					var inputElems = document.getElementsByClassName("cek_pilih");
					count = 0;
					for (var i=0; i<inputElems.length; i++) {
						inputElems[i].checked = source.checked;
						if (inputElems[i].checked == true){
							count++;
						}
					}
					if(count>0){
						document.getElementById("btn_submit").value = "APPROVE";
						document.getElementById("btn_submit").style.backgroundColor = "green";
						document.getElementById("rejectnote").required = false;
						document.getElementById("rejectnote").disabled = true;
					}
					else{
						document.getElementById("btn_submit").value = "REJECT";
						document.getElementById("btn_submit").style.backgroundColor = "red";
						document.getElementById("rejectnote").required = true;
						document.getElementById("rejectnote").disabled = false;
					}
				}
				function cek(){
					var inputElems = document.getElementsByClassName("cek_pilih");
					count = 0;
					for (var i=0; i<inputElems.length; i++) {
						if (inputElems[i].checked == true){
							count++;
						}
					}
					if(count>0){
						document.getElementById("btn_submit").value = "APPROVE";
						document.getElementById("btn_submit").style.backgroundColor = "green";
						document.getElementById("cbx_all").checked = true;
						document.getElementById("rejectnote").required = false;
						document.getElementById("rejectnote").disabled = true;
					}
					else{
						document.getElementById("btn_submit").value = "REJECT";
						document.getElementById("btn_submit").style.backgroundColor = "red";
						document.getElementById("cbx_all").checked = false;
						document.getElementById("rejectnote").required = true;
						document.getElementById("rejectnote").disabled = false;
					}
				}
				</script>
				';
				echo $script;
			} else if ($msg!="") {
				echo $msg;
			} else {
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>REQUEST SUDAH DIPROSES!</h2></div>";
			}
			$detail.= "</form>";

			echo ($style);
			echo ($header);
			echo ($detail);
		}
	}

	public function ApproveRejectAchievement() 
	{ 

		$msg = "";
		$data = $this->PopulatePost();
		// echo json_encode($data['karyawan']); die;
		// echo $this->API_URL."/Achievementkpikaryawan/ApproveAchievementKPI";
  		// echo "<br>";
  		// die(json_encode($data));
        
		//APPROVE
		if(ISSET($data['karyawan'])){

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "ACHIEVEMENT KPI KARYAWAN";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." APPROVAL ACHIEVEMENT KPI KARYAWAN ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $this->API_URL."/Achievementkpikaryawan/ApproveAchievementKPI",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => http_build_query($data),
			));
			
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			
			$result = json_decode($result, true);
			
			// echo json_encode($result); die;

			if ($result["result"]=="SUCCESS") {
				$msg = "
				<div style='padding:10px;background:#d4edda;border:1px solid #c3e6cb'>
				<center><h2>REQUEST ACHIEVEMENT KPI BERHASIL DIAPPROVE</h2></center>
				</div>";
				
				$dApproval = array(
					'ApprovalStatus' => 'APPROVED',
					'ApprovedDate' => date('Y-m-d H:i:s'),
				);
				$wApproval = array(
					'RequestNo' => $data['no_request'],
					'ApprovalStatus' => 'UNPROCESSED',
				);
				$resultEdit = $this->Achievementkpikaryawanmodel->editTblApproval($wApproval,$dApproval);
				
				// ActivityLog Update SUCCESS
				$params['Remarks']='SUCCESS';
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			else {
				$msg = "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><center><h2>".$result["error"]."</h2></center></div>";   

				// ActivityLog Update FAILED
				$params['Remarks']='FAILED - '.$result["error"];
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

			}      
		}
		
		//REJECT
		else{


			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "ACHIEVEMENT KPI KARYAWAN";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." REJECT ACHIEVEMENT KPI KARYAWAN ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$url = $this->API_URL."/Achievementkpikaryawan/RejectAchievementKPI";

			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => http_build_query($data),
			));
			
			$result = curl_exec($curl);
			// echo $result;die;
			$err = curl_error($curl);
			curl_close($curl);
									
			$result = json_decode($result, true);

			if ($result["result"]=="SUCCESS") {
				$msg = "<div style='padding:10px;background:#d4edda;border:1px solid #f5c6cb;'><h2><center>REQUEST ACHIEVEMENT KPI BERHASIL DIREJECT</center></h2></div>";
				
				$dApproval = array(
					'ApprovalStatus' => 'REJECTED',
				);
				$wApproval = array(
					'RequestNo' => $data['no_request'],
					'ApprovalStatus' => 'UNPROCESSED',
				);
				$resultEdit = $this->Achievementkpikaryawanmodel->editTblApproval($wApproval,$dApproval);
		

				// ActivityLog Update SUCCESS
				$params['Remarks']='SUCCESS';
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
			else { 
				$msg = "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2><center>".$result["error"]."</center></h2></div>";

				// ActivityLog Update FAILED
				$params['Remarks']='FAILED - '.$result["error"];
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
			}
		}
		$this->ViewRequestAchievementKPI($data["no_request"], $data["app_by"], $data["total_week"], $msg);
	}
	
}																																			