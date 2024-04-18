<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
class Targetkpiv2 extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Targetkpiv2model');
		$this->load->model('approvalmodel');
		$this->version = 2;
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
		$this->API_ZEN = $this->ConfigSysModel->Get()->zenhrs_url;
	}
	
	public function index()
	{
		// echo json_encode($_SESSION);die;
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "TARGET KPI V2";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU TARGET KPI V2 ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);
		
		$data['branch'] = $this->GetBranches();
		$data['title'] = 'Target KPI V2';

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView('TargetKPIV2View',$data);
	}
		
	public function GetBranches()
	{	
		$URL = $this->API_ZEN."/Zenapi/GetBranches";
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
		if($httpcode!=200){
			echo 'Web ZEN sedang tidak bisa diakses! HTTP Code:'.$httpcode; die;
		}
		else{
			$result = json_decode($response, true);
			if($result['result'] =='sukses'){
				// echo json_encode($result['data']);
				return $result['data'];
			}
			else{
				echo $result['error']; die;
			}
		}
	}
	
	public function GetDivHeadByBranchIDAndUserID($BranchID='')
	{	
		$URL = $this->API_ZEN."/Zenapi/GetDivHeadByBranchIDAndUserID?BranchID=".$BranchID.'&UserID='.$_SESSION['logged_in']['userid'];
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
		if($httpcode!=200){
			$res['result'] = 'gagal';
			$res['data'] = array();
			$res['error'] = 'Web ZEN sedang tidak bisa diakses! HTTP Code:'.$httpcode;
			echo json_encode($res);
		}
		else{
			echo $response;
		}
	}
	
	public function GetManagers()
	{	
		$post['api'] = 'APITES';
		// $post['svr'] = $_SESSION["conn"]->Server;
		// $post['db'] = $_SESSION["conn"]->Database;
		//------------------------------------------------
		// $URL = $_SESSION["conn"]->AlamatWebService.API_BKT."/Targetkpisalesman/TargetSalesman_AmbilListWilayahSalesman";
		$URL = $this->API_URL."/Targetkpisalesman/TargetSalesman_AmbilListWilayahSalesman";
		$response = CURLPOSTJSON($URL, $post);
		// echo $response;die;
		$result = json_decode($response, true);
		if($result['result'] =='success'){
			return $result['data'];
		}
		else{
			echo $result['error']; die;
		}
	}
	
	public function GetDivision($userid)
	{	
		// #1 LIST DIVISION DARI ZEN
		$URL = $this->API_ZEN."/Zenapi/DivisionListUnderDivHead/".$userid;
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
			$res['result'] = 'gagal';
			$res['data'] = array();
			$res['error'] = 'Web ZEN sedang tidak bisa diakses! HTTP Code:'.$httpcode;
			echo json_encode($res);
		}
		else{
			echo $response;
		}
	}
	
	public function ListTargetKPIV2()
	{
		$post['api'] = 'APITES';
		$post['userid'] = $this->input->post('userid');
		// $post['kategori'] = $this->input->post('kategori');
		$post['divisionid'] = $this->input->post('divisionid');
		$post['divisionname'] = $this->input->post('divisionname');
		$post['th'] = $this->input->post('th');
		$post['bl'] = $this->input->post('bl');	
		// echo json_encode($post); die;
		
		$tgl_awal = date('Y-m-d', strtotime($post['th'].'-'.$post['bl'].'-01'));
		$tgl_akhir = date('Y-m-d', strtotime("+1 months", strtotime($tgl_awal)));
		$tgl_akhir = date('Y-m-d', strtotime("-1 day", strtotime($tgl_akhir)));
		
		$employee = array();
		$member_include = array();
		$error = '';
		
		if($error==''){
			//------------------------------------------------
			$URL = $this->API_URL."/Targetkpiv2/GetKPICategoryByDivisionID";
			$response = CURLPOSTJSON($URL, $post);
			// echo $response;die;
			$result = json_decode($response, true);
			if($result['result'] =='success'){
				$kpicategory =  $result['KPICategory'];
			}
			else{
				$error =  $result['error'];
			}
		}
		
		if($error==''){
			$URL = $this->API_ZEN."/Zenapi/GetEmpUnderDivisionIDAndDate?divisionid=".urlencode($post['divisionid'])."&th=".$post['th']."&bl=".$post['bl'];
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
			
			if($httpcode!=200){
				$error = 'Web ZEN sedang tidak bisa diakses! HTTP Code:'.$httpcode;
			}
			else{
				$result = json_decode($response, true);
				if($result['result'] =='sukses'){
					// simpan  divisionid ke dalam session supaya tidak hit API ZEN setiap reload halaman
					$employee = $result['data'];
				}
				else{
					$error = 'Ambil List Employee Under Division gagal. Error: '.$result['error'];
				}
			}
		}
	
		if($error==''){
			// Ambil List Division user dan divisi di bawah nya
			$URL = $this->API_ZEN."/Zenapi/MemberIncludeV2ListByThBl?th=".$post['th']."&bl=".$post['bl'];
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
			if($httpcode!=200){
				$error= 'Web ZEN sedang tidak bisa diakses! HTTP Code:'.$httpcode;
			}
			else{
				$result = json_decode($response, true);
				if($result['result'] =='sukses'){
					$member_include = $result['data'];
				}
				else{
					$error = 'Ambil List Member Include gagal. Error: '.$result['error'];
				}
			}
		}
		// echo json_encode($employee);die;
		// echo json_encode($member_include);die;
		
		if($error==''){
			//#1 jika ada karyawan yg secara zen a/ bawahan, namun terdaftar untuk KPI category beda, maka diexclude 
			foreach($employee as $key => $emp){
				foreach($member_include as $k => $member){
					if($emp['USERID']==$member['USERID']){
						if($post['divisionid']!=$member['DivisionID']){
							unset($employee[$key]);
						}
					}
				}
			}
			
			//#2 jika ada karyawan yg didaftarkan di member include, namun tidak ada di return dari list employee zen, maka diinclude
			foreach($member_include as $k => $member){
				if($post['divisionid']==$member['DivisionID']){
					array_push($employee, array(
						'USERID'=>$member['USERID'],
						'NAME'=>$member['Name'],
						'EMPTYPEID'=>'',
						'EMPLEVELID'=>$member['EmpLevelID'],
						'EMPPOSITIONID'=>$member['PositionID'],
						'EMPLEVEL'=>$member['EmpLevel'],
						'EMPTYPE'=>'',
						'EMPPOSITIONNAME'=>$member['PositionName']
					));
				}
			}
			
			// $employee = array_values($employee);
			
			// susun ke dalam format yg ditentukan
			$new_employee = array();
			foreach($employee as $emp){
				array_push($new_employee, array(
					'USERID'=>$emp['USERID'],
					'NAME'=>$emp['NAME'],
					'DIVISIONID'=>$post['divisionid'],
					'DIVISIONNAME'=>$post['divisionname'],
					'POSITIONID'=>$emp['EMPPOSITIONID'],
					'POSITIONNAME'=>$emp['EMPPOSITIONNAME'],
					'EMPLEVELID'=>$emp['EMPLEVELID'],
					'EMPLEVEL'=>$emp['EMPLEVEL'],
					'TGL_AWAL'=>$tgl_awal,
					'TGL_AKHIR'=>$tgl_akhir,
					'KODE_TARGET'=>$this->SetKodeTarget($emp['USERID'], $post['th'], $post['bl']),
					'WITHTARGETKPI'=>1,
					'NOREQUESTKPI'=>'',
					'TARGETKPISTATUS'=>'UNSAVED',
					'NOREQUESTACV'=>'',
					'ACVKPISTATUS'=>'',
					'TOTALACHIEVEMENT'=>0,
					'EXCLUDETUNJANGANPRESTASI'=>0,
					'TEMPLATE_ID'=>'',
					'CATATAN'=>'',
					'TOTALBOBOT'=>0,
				));	
			}
		}
		// echo json_encode($new_employee);die;
		
		if($error==''){
			$load_bawahan = $this->TargetKaryawan_KPI_LoadBawahan($post['userid'],$post['divisionid'], $post['th'], $post['bl']);
			// echo json_encode($load_bawahan);die;
			
			foreach($new_employee as $i=>$emp){
				$ada = 0;
				foreach($load_bawahan as $bawahan){
					if($bawahan['USERID'] == $emp['USERID']){
						$new_employee[$i]['KODE_TARGET'] = $bawahan['Kode_Target'];
						$new_employee[$i]['WITHTARGETKPI'] = $bawahan['WithTargetKPI'];
						$new_employee[$i]['NOREQUESTKPI'] = $bawahan['NoRequestKPI'];
						$new_employee[$i]['TARGETKPISTATUS'] =$bawahan['TargetKPIStatus'];
						$new_employee[$i]['NOREQUESTACV'] = $bawahan['NoRequestAcv'];
						$new_employee[$i]['ACVKPISTATUS'] = $bawahan['AcvKPIStatus'];
						$new_employee[$i]['TOTALACHIEVEMENT'] = $bawahan['TotalAchievement'];
						$new_employee[$i]['EXCLUDETUNJANGANPRESTASI'] = $bawahan['ExcludeTunjanganPrestasi'];
						$new_employee[$i]['TEMPLATE_ID'] = $bawahan['template_id'];
						$new_employee[$i]['CATATAN'] = $bawahan['catatan'];
						$new_employee[$i]['TOTALBOBOT'] = $bawahan['TotalBobot'];
					}	
				}
			}
			
			if(ISSET($kpicategory['KPICategory'])){
				$master_kpi = $this->Master_KPI_AmbilList($kpicategory['KPICategory']);
				$template = $this->Master_Template_Target_KPI_AmbilList($kpicategory['KPICategory'], $post['th'], $post['bl']);
				$atasan = $this->ZenGetAtasan($post['userid'], $tgl_awal);
			}
			else{
				$error = 'KPI Category tidak ditemukan!';
			}
		}
		
		if($error==''){
			$res = [];
			$res['result'] = 'success';
			$res['bawahan'] = $new_employee;
			$res['master_kpi'] = $master_kpi;
			$res['atasan'] = $atasan;
			$res['template'] = $template;
			$res['kpicategory'] = $kpicategory;
			echo json_encode($res);
		}
		else{
			$res = [];
			$res['result'] = 'failed';
			$res['error'] = $error;
			echo json_encode($res);
		}
	}
	
	public function TargetKaryawan_KPI_LoadBawahan($userid, $divisionid, $th, $bl)
	{
		// echo json_encode($_SESSION); die;
		$post['api'] = 'APITES';
		$post['userid'] = $userid;
		$post['divisionid'] = $divisionid;
		$post['th'] = $th;
		$post['bl'] = $bl;		
		//------------------------------------------------
		$URL = $this->API_URL."/Targetkpiv2/TargetKaryawan_KPI_LoadBawahan";
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
	
	public function TargetKaryawan_KPI_AmbilTargetDetail()
	{
		// echo json_encode($_SESSION); die;
		$post['api'] = 'APITES';
		$post['KodeTarget'] = $this->input->post('KodeTarget');
		$post['NoRequestKPI'] =  $this->input->post('NoRequestKPI');
		//------------------------------------------------
		$URL = $this->API_URL."/Targetkpiv2/TargetKaryawan_KPI_AmbilTargetDetail";
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
		$URL = $this->API_URL."/Targetkpiv2/Master_Template_Target_KPI_AmbilList";
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
		$URL = $this->API_URL."/Targetkpiv2/Master_Template_Target_KPI_AmbilList_Detail";
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
		$URL = $this->API_URL."/Targetkpiv2/SetKodeTarget";
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
		$URL = $this->API_URL."/Targetkpiv2/Master_KPI_AmbilList";
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
	
	public function ZenGetAtasan($UserID, $HistoryDate='2023-01-01')
	{	
		$atasan = array('UserApproval'=>'NOT FOUND','Name'=>'NOT FOUND','UserEmail'=>'NOT FOUND');
		$URL = $this->API_ZEN."/Zenapi/GetEmployeeHead?EventID=LEAVE&HistoryDate=".$HistoryDate."&UserID=".$UserID;
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
		$params['Module'] = "TARGET KPI V2";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." SIMPAN TARGET KPI V2 ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);
		
		$post = $_POST;
		$post['api'] = 'APITES';
		// $post['atasan'] = $_SESSION['logged_in']['userid'];
		$post['user'] = $_SESSION['logged_in']['username'];
		$post['kode_lokasi'] = $_SESSION['logged_in']['branch_id'];
		// echo json_encode($post); die;
		//------------------------------------------------
		$URL = $this->API_URL."/Targetkpiv2/save";
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
			$params['Remarks']='FAILED - Save Request Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			echo json_encode(array('result'=>'failed','error'=>'Save Request Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err));
		}
		else{
			$result = json_decode($response, true); 
			// ActivityLog Update SUCCESS
			$params['Remarks']='SUCCESS';
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
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
		$params['Module'] = "TARGET KPI V2";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." SEND REQUEST TARGET KPI V2 ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		// echo json_encode($_SESSION); die;
		$post = $_POST;
		$NoRequestKPI = $_SESSION['logged_in']['userid'].'_'.date('YmdHis')	;	
		$post['api'] = 'APITES';
		$post['norequestkpi'] = $NoRequestKPI;
		$post['user_name'] = $_SESSION['logged_in']['username'];
		$post['user_email'] = $_SESSION['logged_in']['useremail'];
		// echo json_encode($post); die;
		
		//------------------------------------------------
		$URL = $this->API_URL."/Targetkpiv2/sendrequest";
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
		// echo $response; //die;
		// echo "<br>";
		// echo "<br>";
		
		if($httpcode!=200){
			// ActivityLog Update FAILED
			$params['Remarks']='FAILED - Request Target KPI Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			echo json_encode(array('result'=>'failed','error'=>'Request Target KPI Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err));
		}
		else{
			$error = '';
			$result = json_decode($response, true); 
			if($result['result']=='success'){
				foreach($result['approval'] as $approval){
					$data['ApprovalType']='TARGET KPI V2';
					$data['RequestNo']=$post['norequestkpi'];
					$data['RequestBy']=$post['user_name'];
					$data['RequestDate']=date('Y-m-d H:i:s');
					$data['RequestByName']=$post['user_name'];
					$data['RequestByEmail']=$post['user_email'];
					
					$data['ApprovedBy']=$approval['job'];
					$data['ApprovedByName']=$approval['nama'];
					$data['ApprovedByEmail']=$approval['email'];
					
					$data['ApprovalStatus']='UNPROCESSED';
					$data['AddInfo1']='KPI Category ID';
					$data['AddInfo1Value']=$post['kpicategory'];
					$data['AddInfo2']='KPI Category';
					$data['AddInfo2Value']=$post['kpicategoryname'];
					$data['AddInfo5']='Kode Lokasi';
					$data['AddInfo5Value']=$_SESSION['logged_in']['branch_id'];
					$data['AddInfo6']='Periode';
					$data['AddInfo6Value']=$post['periode'];
					$data['AddInfo9']='Wilayah';
					$data['AddInfo9Value']=$_SESSION['logged_in']['branch'];
					$data['AddInfo12']='Week';
					$data['AddInfo12Value']=$post['week'];
					$data['ApprovalNeeded']=$result['approval_needed'];
					$data['Priority']=1;
					$data['BhaktiFlag']='UNPROCESSED';
					$data['IsCancelled']=0;
					$data['LocationCode']='HO';
					$data['IsEmailed']=($result['is_emailed']==1)?1:0;
					$data['EmailedDate']=($result['is_emailed']==1)?date('Y-m-d H:i:s'):NULL;
					$insert_approval = $this->insert_approval($data);
					if($insert_approval['result']=='failed'){
						$error =(ISSET($insert_approval['error'])) ? $insert_approval['error'] : 'Error insert approval!';
						break;
					}
				}
				
				if($error==''){
					foreach($result['deadline'] as $deadline){
						$data['ApprovalType']='TARGET KPI V2';
						$data['RequestNo']=$post['norequestkpi'];
						$data['RequestBy']=$post['user_name'];
						$data['RequestDate']=date('Y-m-d H:i:s');
						$data['RequestByName']=$post['user_name'];
						$data['RequestByEmail']=$post['user_email'];
						
						$data['ApprovedBy']=$deadline['job'];
						$data['ApprovedByName']=$deadline['nama'];
						$data['ApprovedByEmail']=$deadline['email'];
						
						$data['ApprovalStatus']='UNPROCESSED';
						$data['AddInfo1']='KPI Category ID';
						$data['AddInfo1Value']=$post['kpicategory'];
						$data['AddInfo2']='KPI Category';
						$data['AddInfo2Value']=$post['kpicategoryname'];
						$data['AddInfo5']='Kode Lokasi';
						$data['AddInfo5Value']=$_SESSION['logged_in']['branch_id'];
						$data['AddInfo6']='Periode';
						$data['AddInfo6Value']=$post['periode'];
						$data['AddInfo9']='Wilayah';
						$data['AddInfo9Value']=$_SESSION['logged_in']['branch'];
						$data['AddInfo12']='Week';
						$data['AddInfo12Value']=$post['week'];
						$data['ApprovalNeeded']=$result['approval_needed'];
						$data['Priority']=2;
						$data['BhaktiFlag']='UNPROCESSED';
						$data['IsCancelled']=0;
						$data['LocationCode']='HO';
						$data['IsEmailed']=0;
						$data['EmailedDate']=NULL;
						$insert_approval = $this->insert_approval($data);
						if($insert_approval['result']=='failed'){
							$error = (ISSET($insert_approval['error'])) ? $insert_approval['error'] : 'Error insert approval!';
							break;
						}
					}				
				}				
			}

			// ActivityLog Update SUCCESS
			
			if($error==''){
				$params['Remarks']='SUCCESS';
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				
				$res['result']='success';
				$res['error']='';
				echo json_encode($res);
			}
			else{
				$params['Remarks']='FAILED '.$error;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				
				$res['result']='failed';
				$res['error']=$error;
				echo json_encode($res);
			}
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
		$params['Module'] = "TARGET KPI V2";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." CANCEL REQUEST TARGET KPI V2 ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		// echo json_encode($_SESSION); die;
		$post = $_POST;
		$post['api'] = 'APITES';
		$post['user'] = $_SESSION['logged_in']['username'];
		// echo json_encode($post); die;
		//------------------------------------------------
		$URL = $this->API_URL."/Targetkpiv2/cancel";
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
				$data['ApprovalType'] = 'TARGET KPI V2';
				$data['RequestNo'] = $post['norequestkpi'];
				$data['CancelledBy'] = $_SESSION['logged_in']['username'];
				$data['CancelledByName'] = $_SESSION['logged_in']['username'];
				$data['CancelledNote'] = $post['note'];
				$data['CancelledByEmail'] = $_SESSION['logged_in']['useremail'];
				$result = $this->cancel_approval($data);
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
		$params['Module'] = "TARGET KPI V2";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." Insert APPROVAL TARGET KPI V2 ";
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
			$params['Remarks']='FAILED - '.$result['pesan'];
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
		$params['Module'] = "TARGET KPI V2";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." CANCEL APPROVAL TARGET KPI V2 ";
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
  
}																																			