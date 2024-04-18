<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
class Targetkpikaryawan extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Targetkpikaryawanmodel');
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
		$params['Module'] = "TARGET KPI KARYAWAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU TARGET KPI KARYAWAN ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		// echo json_encode($_SESSION); die;
		$post['api'] = 'APITES';
		$post['jenis'] = 'NON-SALESMAN';
		//------------------------------------------------
		if(!ISSET($_SESSION['logged_in']['DivisionListUnderDivHead'])){
			// Ambil List Division user dan divisi di bawah nya
			$URL = $this->API_ZEN."/Zenapi/DivisionListUnderDivHead/".$_SESSION['logged_in']['userid'];
			// die($URL);
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
		$URL = $this->API_URL."/Targetkpikaryawan/Kategori";
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
				$params['Remarks']="FAILED - ".$result->error;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				echo $result->error; die;
			}
		}
		
		$data['atasan'] = $this->ZenGetAtasan(date('Y-m-01'));
		$data['title'] = 'Target KPI Karyawan';
		// echo json_encode($data);die;

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView('TargetKPIKaryawanView',$data);
	}
	
	public function ListTargetKPIKaryawan()
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
		
		// echo json_encode($post); die;
		$employee = array();
		$member_include = array();
		
		if(ISSET($_SESSION['logged_in']['DivisionListUnderDivHead'])){		
			//Ubah DivisionID menjadi array 1 dimensi
			$divisionid = array_column($_SESSION['logged_in']['DivisionListUnderDivHead'], 'DivisionID');
			$divisionid = array_values($divisionid);
			// echo json_encode($divisionid); die;
			$post['divisionid'] = $divisionid;
			if(count($divisionid)>0){
				// Ambil List Division user dan divisi di bawah nya				
				$URL = $this->API_ZEN."/Zenapi/GetEmpUnderDivisionIDAndDate?divisionid=".$post['kategori']."&th=".$post['th']."&bl=".$post['bl'];
				// die($URL);

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
					echo 'Zen - Ambil List Employee Under Division gagal ! HTTP Code:'.$httpcode; die;
				}
				else{
					$result = json_decode($response, true);
					if($result['result'] =='sukses'){
						// simpan  divisionid ke dalam session supaya tidak hit API ZEN setiap reload halaman
						if(count($result['data'])>0){
							$employee = $result['data'];
						}
						else{
							echo 'Tidak ada data bawahan'; die;
						}
					}
					else{
						echo 'Zen - Ambil List Employee Under Division gagal. Error: '.$result['error']; die;
					}
				}
			
				// Ambil List Division user dan divisi di bawah nya
				$URL = $this->API_ZEN."/Zenapi/MemberIncludeListByThBl?category=".$post['kategori']."&th=".$post['th']."&bl=".$post['bl'];
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
					echo 'Zen - Ambil List Member Include gagal. Error:'.$httpcode; die;
				}
				else{
					$result = json_decode($response, true);
					if($result['result'] =='sukses'){
						$member_include = $result['data'];
					}
					else{
						echo 'Zen - Ambil List Member Include gagal. Error: '.$result['error']; die;
					}
				}
			}
		}
		// echo json_encode($employee);die;
		
		//#1 jika ada karyawan yg secara zen a/ bawahan, namun terdaftar untuk KPI category beda, maka diexclude 
		foreach($employee as $key => $emp){
			foreach($member_include as $k => $member){
				if($emp['USERID']==$member['USERID']){
					if($post['kategori']!=$member['KPICategoryID']){
						unset($employee[$key]);
					}
				}
			}
		}
		
		//#2 jika ada karyawan yg didaftarkan di member include, namun tidak ada di return dari list employee zen, maka diinclude
		foreach($member_include as $k => $member){
			if($post['kategori']==$member['KPICategoryID']){
				array_push($employee, array(
					'USERID'=>$member['USERID'],
					'NAME'=>$member['Name'],
					'EMPTYPEID'=>'',
					'EMPLEVELID'=>'',
					'EMPPOSITIONID'=>$member['PositionID'],
					'EMPLEVEL'=>'',
					'EMPTYPE'=>'',
					'EMPPOSITIONNAME'=>$member['PositionName']
				));
			}
		}
		
		$employee = array_values($employee);
		// echo json_encode($employee);die;
		
		$load_bawahan = $this->TargetKaryawan_KPI_LoadBawahan($post['userid'],$post['kategori'], $post['th'], $post['bl']);
		$master_kpi = $this->Master_KPI_AmbilList($post['kategori']);
		$template = $this->Master_Template_Target_KPI_AmbilList($post['kategori'], $post['th'], $post['bl']);
		$atasan = $this->ZenGetAtasan($tgl_awal);
		// echo 'load_bawahan='.json_encode($load_bawahan);die;
		
		foreach($employee as $emp){
			$ada = 0;
			foreach($load_bawahan as $bawahan){
				if($bawahan['USERID'] == $emp['USERID']){
					$ada = 1;
				}	
			}
			if($ada==0){
				array_push($load_bawahan, array(
					'USERID'=>$emp['USERID'],
					'Nama'=>$emp['NAME'],
					'DivisionID'=>$post['kategori'],
					'DivisionName'=>$post['nama_kategori'],
					'PositionID'=>$emp['EMPPOSITIONID'],
					'PositionName'=>$emp['EMPPOSITIONNAME'],
					'Tgl_Awal'=>$tgl_awal,
					'Tgl_Akhir'=>$tgl_akhir,
					'Kode_Target'=>$this->SetKodeTarget($emp['USERID'], $post['th'], $post['bl']),
					'Training'=>0,
					'WithTargetKPI'=>1,
					'NoRequestKPI'=>'',
					'TargetKPIStatus'=>'UNSAVED',
					'NoRequestAcv'=>'',
					'AcvKPIStatus'=>'',
					'TotalAchievement'=>0,
					'ExcludeTunjanganPrestasi'=>0,
					'template_id'=>'',
					'TotalBobot'=>0,
				));	
			}
		}
		echo json_encode(array('bawahan'=>$load_bawahan, 'master_kpi'=>$master_kpi, 'atasan'=>$atasan, 'template'=>$template));
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
		$URL = $this->API_URL."/Targetkpikaryawan/TargetKaryawan_KPI_LoadBawahan";
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
		$URL = $this->API_URL."/Targetkpikaryawan/TargetKaryawan_KPI_AmbilTargetDetail";
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
		$URL = $this->API_URL."/Targetkpikaryawan/Master_Template_Target_KPI_AmbilList";
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
		$URL = $this->API_URL."/Targetkpikaryawan/Master_Template_Target_KPI_AmbilList_Detail";
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
		$URL = $this->API_URL."/Targetkpikaryawan/SetKodeTarget";
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
		$URL = $this->API_URL."/Targetkpikaryawan/Master_KPI_AmbilList";
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
		$params['Module'] = "TARGET KPI KARYAWAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." SIMPAN TARGET KPI KARYAWAN ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		// echo json_encode($_SESSION); die;
		$post = $this->PopulatePost();
		$post['api'] = 'APITES';
		$post['atasan'] = $_SESSION['logged_in']['userid'];
		$post['user'] = $_SESSION['logged_in']['username'];
		$post['kode_lokasi'] = $_SESSION['logged_in']['branch_id'];
		// echo json_encode($post); die;
		//------------------------------------------------
		$URL = $this->API_URL."/Targetkpikaryawan/save";
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
		
//			echo json_encode(array('result'=>'failed','error'=>'Simpan Request Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err));
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
		$params['Module'] = "TARGET KPI KARYAWAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." SEND REQUEST TARGET KPI KARYAWAN ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		// echo json_encode($_SESSION); die;
		$post = $this->PopulatePost();
		$NoRequestKPI = $_SESSION['logged_in']['userid'].'_'.date('YmdHis')	;	
		$post['api'] = 'APITES';
		$post['norequestkpi'] = $NoRequestKPI;
		$post['kode_lokasi'] = $_SESSION['logged_in']['branch_id'];
		$post['user_name'] = $_SESSION['logged_in']['username'];
		$post['user_email'] = $_SESSION['logged_in']['useremail'];
		// echo json_encode($post); die;
		//------------------------------------------------
		$URL = $this->API_URL."/Targetkpikaryawan/sendrequest";
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
				$data['ApprovalType']='TARGET KPI KARYAWAN';
				$data['RequestNo']=$post['norequestkpi'];
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
		$params['Module'] = "TARGET KPI KARYAWAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." CANCEL REQUEST TARGET KPI KARYAWAN ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		// echo json_encode($_SESSION); die;
		$post = $this->PopulatePost();
		$post['api'] = 'APITES';
		$post['user'] = $_SESSION['logged_in']['username'];
		// echo json_encode($post); die;
		//------------------------------------------------
		$URL = $this->API_URL."/Targetkpikaryawan/cancel";
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
		// echo json_encode($response); //die;
		
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
				$data['ApprovalType'] = 'TARGET KPI KARYAWAN';
				$data['RequestNo'] = $post['norequestkpi'];
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
			
	
	public function duplicate()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "TARGET KPI KARYAWAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." CANCEL REQUEST TARGET KPI KARYAWAN ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		// echo json_encode($_SESSION); die;
		$post = $this->PopulatePost();
		$post['api'] = 'APITES';
		$post['user'] = $_SESSION['logged_in']['userid'];
		// echo json_encode($post); die;
		//------------------------------------------------
		$URL = $this->API_URL."/Targetkpikaryawan/duplicate";
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
			$params['Remarks']='FAILED - Cancel Request Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			echo json_encode(array('result'=>'failed','error'=>'Cancel Request Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err));
		}
		else{
			$result = json_decode($response, true);
			if(ISSET($result['delete_approval']) && $result['delete_approval']==true){
				$data = array();
				$data['ApprovalType'] = 'TARGET KPI KARYAWAN';
				$data['RequestNo'] = $post['norequestkpi'];
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
		$params['Module'] = "TARGET KPI KARYAWAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." Insert APPROVAL TARGET KPI KARYAWAN ";
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
		$params['Module'] = "TARGET KPI KARYAWAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." CANCEL APPROVAL TARGET KPI KARYAWAN ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$result = $this->approvalmodel->doaction('cancel', $data);
		if($result['pesan'] == 'Request Ini Berhasil Dicancel'){
  
			// ActivityLog Update SUCCESs
			$params['Remarks']='SUCCESs';
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
	
	public function ProsesTargetKPI()
	{
		$noRequest = urldecode($this->input->get("req"));
		$approvedBy = urldecode($this->input->get("app"));
		$totalWeek = urldecode($this->input->get("week"));
		$this->ViewRequestTargetKPI($noRequest, $approvedBy, $totalWeek);
	}

	public function ViewRequestTargetKPI($noRequest, $approvedBy, $totalWeek, $msg="") 
	{
		$URL = $this->API_URL."/Targetkpikaryawan/AmbilTargetKPI?req=".urlencode($noRequest)."&app=".urlencode($approvedBy);
		// echo($URL); die;
		$GetRequest = json_decode(file_get_contents($URL), true);

		if($GetRequest["result"]=="SUCCESS") {

			$req = $GetRequest["data"];

			$style = '<style>
				*{
					font-family:"Arial, sans-serif";
					font-size:14px;
				}
				table{
					border-collapse:collapse;
				}
				table th, table td{
					border:1px solid #ddd;
					text-align:left;
					padding:5px;
				}
				table tr:hover {
					/*background:#f8f8f8;*/
				}
				table tr:nth-child(even) {
					background:#f8f8f8;
				}
			</style>';
			
			$BL = $req["Bulan"];
			$NM_BL = array("", "JANUARI", "FEBRUARI", "MARET", "APRIL", "MEI", "JUNI", "JULI", "AGUSTUS", "SEPTEMBER", "OKTOBER", "NOVEMBER", "DESEMBER");

			$header = "<h2>REQUEST APPROVAL TARGET KPI</h2><hr><br>";
			$header.= "No Request: <b>".$req["NoRequestKPI"]."</b><br>";
			$header.= "Tgl Request: <b>".date("d-M-Y H:i:s", strtotime($req["RequestSentDate"]))."</b><br>";
			$header.= "Dikirimkan Oleh: <b style='background:yellow;'>".$req["RequestSentBy"]."</b><br><hr><br>";
			$header.= "Cabang: <b style='background:yellow;'>".$req["Cabang"]."</b><br>";
			$header.= "Periode: <b style='background:yellow;'>".$NM_BL[$BL]." ".$req["Tahun"]."</b><br>";
			$header.= "Banyak Karyawan: <b>".count($req["ListTargetKPI"])."</b><br><br>";

			$detail = "";
			$detailhd = "";
			$detaildt = "";
			$details = $req["ListTargetKPI"];
			// echo(json_encode($details)."<br>");

			$No = 0;

			$detailhd.= "<table>";
			$detailhd.= "<tr>";
			$detailhd.= "   <th width='5%''>No</th>";
			$detailhd.= "   <th width='15%'>Karyawan</th>";
			$detailhd.= "   <th width='8%'>Periode</th>";
			$detailhd.= "   <th width='20%'>Key Performance Indicator</th>";
			$detailhd.= "   <th width='20%'>Deskripsi</th>";
			$detailhd.= "   <th width='5%'>Week 1</th>";
			$detailhd.= "   <th width='5%'>Week 2</th>";
			$detailhd.= "   <th width='5%'>Week 3</th>";
			$detailhd.= "   <th width='5%'>Week 4</th>";
			if ($totalWeek>=5) $detailhd.= "   <th width='5%'>Week5</th>";
			if ($totalWeek==6) $detailhd.= "   <th width='5%'>Week6</th>";
			$detailhd.= "   <th width='5%'>Total Target</th>";
			$detailhd.= "   <th width='5%'>Bobot</th>";
			$detailhd.= "   <th width='5%'>TotalBobot</th>";
			$detailhd.= "   <th width='10%'></th>";
			$detailhd.= "   <th width='27%'>History/Status</th>";
			$detailhd.= "</tr>";

			$TotalWaiting = 0;
			$bg = "#ccf2ff";

			for($i=0; $i<count($details); $i++) {
				$No+= 1;
				$TotalBobot = 0;
				$dt = $details[$i];
				if ($bg=="#b3dae8") {
					$bg = "#ccf2ff";
				} else {
					$bg = "#b3dae8";
				}
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
					$detaildt.= "<tr style='background:".$bg.";'>";
					$detaildt.= "   <td>".$KPIs[$j]["KPIName"]."</td>";
					$detaildt.= "   <td>".$KPIs[$j]["KPINote"]."</td>";
				
					$detaildt.= "   <td>".number_format($KPIs[$j]["TargetWeek1"],2)."</td>";
					$detaildt.= "   <td>".number_format($KPIs[$j]["TargetWeek2"],2)."</td>";
					$detaildt.= "   <td>".number_format($KPIs[$j]["TargetWeek3"],2)."</td>";
					$detaildt.= "   <td>".number_format($KPIs[$j]["TargetWeek4"],2)."</td>";
					
					if ($totalWeek>=5) $detaildt.= "   <td>".number_format($KPIs[$j]["TargetWeek5"],2)."</td>";
					if ($totalWeek==6) $detaildt.= "   <td>".number_format($KPIs[$j]["TargetWeek6"],2)."</td>";
					$detaildt.= "   <td>".number_format($KPIs[$j]["KPITarget"],2)."</td>";
					$detaildt.= "   <td>".number_format($KPIs[$j]["KPIBobot"],2)."</td>";
					$detaildt.= "</tr>";
					$TotalBobot += $KPIs[$j]["KPIBobot"];
				}

				$TotalBobot += $KPIs[0]["KPIBobot"];


				$detailhd.="<tr style='background:".$bg.";'>";
				$detailhd.="    <td rowspan='".$k."'>".$No."</td>";
				$detailhd.="    <td rowspan='".$k."'>".$dt["NAMA"]."</td>";                
				$detailhd.="    <td rowspan='".$k."'>".$dt["BULAN"]."/".$dt["TAHUN"]."</td>";
				$detailhd.= "   <td>".$KPIs[0]["KPIName"]."</td>";
				$detailhd.= "   <td>".$KPIs[0]["KPINote"]."</td>";
				
				$detailhd.= "   <td>".number_format($KPIs[0]["TargetWeek1"],2)."</td>";
				$detailhd.= "   <td>".number_format($KPIs[0]["TargetWeek2"],2)."</td>";
				$detailhd.= "   <td>".number_format($KPIs[0]["TargetWeek3"],2)."</td>";
				$detailhd.= "   <td>".number_format($KPIs[0]["TargetWeek4"],2)."</td>";
				
				if ($totalWeek>=5) $detailhd.= "   <td>".number_format($KPIs[0]["TargetWeek5"],2)."</td>";
				if ($totalWeek==6) $detailhd.= "   <td>".number_format($KPIs[0]["TargetWeek6"],2)."</td>";
				
				$detailhd.= "   <td>".number_format($KPIs[0]["KPITarget"],2)."</td>";
				$detailhd.= "   <td>".number_format($KPIs[0]["KPIBobot"],2)."</td>";
				$detailhd.="    <td rowspan='".$k."'>".number_format($TotalBobot, 0)."</td>";
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
				$detailhd.="    <td rowspan='".$k."'>".$HISTORY."</td>";
				$detailhd.="</tr>";         
				$detailhd.=$detaildt; 
			}
			$detailhd.="</table>";
			
			$detail = "
			<form action='./ApproveReject' method='POST'>
			<div style='padding:10px;background:#FFF;border:1px solid #f5c6cb;'>".$detailhd."
			<input type='hidden' name='no_request' value='".$noRequest."'>
			<input type='hidden' name='app_by' value='".$approvedBy."'>
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

	public function ApproveReject() {

		$msg = "";
		$data = $this->PopulatePost();
		
		//APPROVE
		if(ISSET($data['karyawan'])){ 
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "TARGET KPI KARYAWAN";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." APPROVE TARGET KPI KARYAWAN ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $this->API_URL."/Targetkpikaryawan/ApproveTargetKPI",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => http_build_query($data),
			));
			
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// die($result);
			$result = json_decode($result, true);

			if ($result["result"]=="SUCCESS") {     

				$msg = "
				<div style='padding:10px;background:#d4edda;border:1px solid #c3e6cb'>
				<center><h2>REQUEST TARGET KPI BERHASIL DIAPPROVE</h2></center>
				</div>";
				
				$dApproval = array(
					'ApprovalStatus' => 'APPROVED',
					'ApprovedDate' => date('Y-m-d H:i:s'),
				);
				$wApproval = array(
					'RequestNo' => $data['no_request'],
					'ApprovalStatus' => 'UNPROCESSED',
				);
				$resultEdit = $this->Targetkpikaryawanmodel->editTblApproval($wApproval,$dApproval);
				
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
			$params['Module'] = "TARGET KPI KARYAWAN";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." REJECT TARGET KPI KARYAWAN ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);

			// die("reject");
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $this->API_URL."/Targetkpikaryawan/RejectTargetKPI",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => http_build_query($data),
			));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $result; die;
			$result = json_decode($result, true);
			if ($result["result"]=="SUCCESS") {
				$msg = "<div style='padding:10px;background:#d4edda;border:1px solid #f5c6cb;'><h2><center>REQUEST TARGET KPI BERHASIL DIREJECT</center></h2></div>";
				
				$dApproval = array(
					'ApprovalStatus' => 'REJECTED',
				);
				$wApproval = array(
					'RequestNo' => $data['no_request'],
					'ApprovalStatus' => 'UNPROCESSED',
				);
				$resultEdit = $this->Targetkpikaryawanmodel->editTblApproval($wApproval,$dApproval);
				
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
		$this->ViewRequestTargetKPI($data["no_request"], $data["app_by"], $msg);
	}
}																																			