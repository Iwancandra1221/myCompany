<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
class Achievementkpiv2 extends MY_Controller 
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
		// echo json_encode($_SESSION);die;
		
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ACHIEVEMENT KPI V2";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU ACHIEVEMENT KPI V2 ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);
		
		$data['branch'] = $this->GetBranches();
		$data['title'] = 'Achievement KPI V2';
		$data['api_zen'] = $this->API_ZEN;

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);
		$this->RenderView('AchievementKPIV2View',$data);
	}
		
	public function GetBranches()
	{	
		$URL = $this->API_ZEN."/Zenapi/GetBranches";
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
		
	public function GetKPICategoryByDivisionID($divisionid)
	{	
		$post['api'] = 'APITES';
		$post['jenis'] = 'SALESMAN';
		$post['divisionid'] = $divisionid;
		//------------------------------------------------
		$URL = API_URL."/Targetkpiv2/GetKPICategoryByDivisionID";
		$response = CURLPOSTJSON($URL, $post);
		// echo $response;die;
		$result = json_decode($response, true);
		if($result['result'] =='success'){
			return $result['KPICategory'];
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
	
	public function ListAchievementKPIV2()
	{
		// echo json_encode($_SESSION); die;
		$post['api'] = 'APITES';
		$post['userid'] = $this->input->post('userid');
		$post['kategori'] = $this->input->post('kategori');
		$post['divisionid'] = $this->input->post('divisionid');
		$post['divisionname'] = $this->input->post('divisionname');
		$post['nama_kategori'] = $this->input->post('nama_kategori');
		$post['th'] = $this->input->post('th');
		$post['bl'] = $this->input->post('bl');	
		
		$tgl_awal = date('Y-m-d', strtotime($post['th'].'-'.$post['bl'].'-01'));
		$tgl_akhir = date('Y-m-d', strtotime("+1 months", strtotime($tgl_awal)));
		$tgl_akhir = date('Y-m-d', strtotime("-1 day", strtotime($tgl_akhir)));
		
		$error = '';
		if($error==''){
			//------------------------------------------------
			$URL = API_URL."/Targetkpiv2/GetKPICategoryByDivisionID";
			$response = CURLPOSTJSON($URL, $post);
			// echo $response;die;
			$result = json_decode($response, true);
			if($result['result'] =='success'){
				$kpicategory = $result['KPICategory'];
			}
			else{
				$error = $result['error'];
			}
		}
		
		if($error==''){
			$load_bawahan = $this->TargetKaryawan_KPI_LoadBawahan($post['userid'],$post['divisionid'], $post['th'], $post['bl']);
			$atasan = $this->ZenGetAtasan($post['userid'], $tgl_awal);
		}
		
		if($error==''){
			$res = [];
			$res['result'] = 'success';
			$res['bawahan'] = $load_bawahan;
			$res['atasan'] = $atasan;
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
		$URL = $this->API_URL."/Achievementkpiv2/TargetKaryawan_KPI_LoadBawahan";
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
		$URL = $this->API_URL."/Achievementkpiv2/TargetKaryawan_KPI_AmbilAchievementDetail";
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
		$params['Module'] = "ACHIEVEMENT KPI V2";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." SIMPAN ACHIEVEMENT KPI V2 ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$post = $_POST;
		$post['api'] = 'APITES';
		$post['spv_userid'] = $_SESSION['logged_in']['userid'];
		$post['spv_name'] = $_SESSION['logged_in']['username'];
		$post['spv_email'] = $_SESSION['logged_in']['useremail'];
		
		$post['user'] = $_SESSION['logged_in']['username'];
		$post['kode_lokasi'] = $_SESSION['logged_in']['branch_id'];
		// echo json_encode($post); die;
		//------------------------------------------------
		$URL = $this->API_URL."/Achievementkpiv2/save";
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
		$params['Module'] = "ACHIEVEMENT KPI V2";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." SEND REQUEST ACHIEVEMENT KPI V2 ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		// echo json_encode($_SESSION); die;
		$post = $_POST;
		$NoRequestAcv = $_SESSION['logged_in']['userid'].'_A'.date('YmdHis')	;	
		$post['api'] = 'APITES';
		$post['norequestacv'] = $NoRequestAcv;
		$post['kode_lokasi'] = $_SESSION['logged_in']['branch_id'];
		$post['user_name'] = $_SESSION['logged_in']['username'];
		$post['user_email'] = $_SESSION['logged_in']['useremail'];
		// echo json_encode($post); die;
		//------------------------------------------------
		$URL = $this->API_URL."/Achievementkpiv2/sendrequest";
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
			$result = json_decode($response, true); 
			if($result['result']=='success'){
				foreach($result['approval'] as $approval){
					$data['ApprovalType']='ACHIEVEMENT KPI V2';
					$data['RequestNo']=$post['norequestacv'];
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
				}
				foreach($result['deadline'] as $deadline){
					$data['ApprovalType']='ACHIEVEMENT KPI V2';
					$data['RequestNo']=$post['norequestacv'];
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
				}
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
		$params['Module'] = "ACHIEVEMENT KPI V2";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." CANCEL ACHIEVEMENT KPI V2 ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		// echo json_encode($_SESSION); die;
		$post = $_POST;
		$post['api'] = 'APITES';
		$post['user'] = $_SESSION['logged_in']['username'];
		$post['api_zen'] = $this->API_ZEN;
		// echo json_encode($post); die;
		//------------------------------------------------
		$URL = $this->API_URL."/Achievementkpiv2/cancel";
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
		
		// echo $response; // die;
		
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
				$data['ApprovalType'] = 'ACHIEVEMENT KPI V2';
				$data['RequestNo'] = $post['norequestacv'];
				$data['CancelledBy'] = $_SESSION['logged_in']['username'];
				$data['CancelledByName'] = $_SESSION['logged_in']['username'];
				$data['CancelledNote'] = $post['note'];
				$data['CancelledByEmail'] = $_SESSION['logged_in']['useremail'];
				// echo json_encode($data);die;
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
		$params['Module'] = "ACHIEVEMENT KPI V2";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." Insert APPROVAL ACHIEVEMENT KPI V2 ";
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
		$params['Module'] = "ACHIEVEMENT KPI V2";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." CANCEL APPROVAL ACHIEVEMENT KPI V2 ";
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