<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class Achievementkpisalesman extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Achievementkpisalesmanmodel');
		$this->load->model('approvalmodel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}
	
	public function index()
	{
		// echo json_encode($_SESSION);die;
		if($_SESSION['logged_in']['isSalesman']==1){
			$data['salesman'] = $this->GetLevelSalesman();
			// echo json_encode($data['salesman']);die;
			if(ISSET($data['salesman'])){
			
				if(!ISSET($_SESSION['logged_in']['level_salesman'])){
					$_SESSION['logged_in']['level_salesman'] = $data['salesman']['Level_Salesman'];
					$_SESSION['logged_in']['wilayah_salesman'] = $data['salesman']['Wilayah'];
				}
			
				$wilayah_salesman = $_SESSION['logged_in']['wilayah_salesman'];
				$level_salesman = $_SESSION['logged_in']['level_salesman'];
					
				// LOGIC DIAMBIL DARI APP TargetSalesman FormInputTargetKPI_Load
				if($level_salesman==0 || $level_salesman==1 || $level_salesman==9){
					$data['wilayah'] = $this->GetWilayah();
				
				}
				else if ($level_salesman>= 70 && $level_salesman < 80){
					$data['wilayah'][] = array('WILAYAH'=>$wilayah_salesman);
					//ambil semua list salesman dengan bawahan dari user yg login (sesuai $_SESSION['logged_in']['salesmanid'])
				}
				else{
					$data['wilayah'][] = array('WILAYAH'=>$wilayah_salesman);
				}
				
				$data['kategori'] = $this->GetKategori();		
				// $data['atasan'] = $this->GetAtasan();
				// echo json_encode($data);die;
				$this->RenderView('Achievementkpisalesmanview',$data);
			}
			else die('Level Salesman belum disetting. mohon di setting terlebih dahulu!');
			
		}
		// else if($this->is_developer()){
		else {
			$data['wilayah'] = $this->GetWilayah();	
			$data['kategori'] = $this->GetKategori();
			$this->RenderView('Achievementkpisalesmanview',$data);
		}
		// else die('Menu ini khusus user yang ada kode salesman nya. mohon di setting terlebih dahulu!');
	}
		
	public function is_developer() {
		$is_developer = false;
		if (array_key_exists("role", $_SESSION)) 
		{
			$jml = count($_SESSION['role']);
			for($i=0;$i<$jml;$i++) {
				if ($_SESSION['role'][$i]=="ROLE01"){
					$is_developer = true;
				}
			}
		}
		return $is_developer;
	}
	
	public function ListTargetKPISalesman()
	{
		// echo json_encode($_SESSION); die;
		$post['api'] = 'APITES';
		$post['kode_salesman'] = $this->input->post('kajul');
		$post['kategori'] = $this->input->post('kategori');
		$post['nama_kategori'] = $this->input->post('nama_kategori');
		$post['th'] = $this->input->post('th');
		$post['bl'] = $this->input->post('bl');	
		
		$tgl_awal = date('Y-m-d', strtotime($post['th'].'-'.$post['bl'].'-01'));
		$tgl_akhir = date('Y-m-d', strtotime("+1 months", strtotime($tgl_awal)));
		$tgl_akhir = date('Y-m-d', strtotime("-1 day", strtotime($tgl_akhir)));
		
		// echo json_encode($post); die;
		
		$load_bawahan = $this->TargetSalesman_KPI_LoadBawahan($post['kode_salesman'],$post['kategori'], $post['th'], $post['bl']);
		$master_kpi = $this->Master_KPI_AmbilList($post['kategori']);
		$template = $this->Master_Template_Target_KPI_AmbilList($post['kategori'], $post['th'], $post['bl']);
		$atasan = $this->GetAtasan($post['kode_salesman']);
		
		echo json_encode(array('bawahan'=>$load_bawahan, 'master_kpi'=>$master_kpi, 'atasan'=>$atasan, 'template'=>$template));
	}
	
	public function TargetSalesman_KPI_LoadBawahan($kode_salesman, $kategori, $th, $bl)
	{
		// echo json_encode($_SESSION); die;
		$post['api'] = 'APITES';
		$post['kode_salesman'] = $kode_salesman;
		$post['kategori'] = $kategori;
		$post['th'] = $th;
		$post['bl'] = $bl;		
		//------------------------------------------------
		$URL = $this->API_URL."/Achievementkpisalesman/TargetSalesman_KPI_LoadBawahan";
		$response = CURLPOSTJSON($URL, $post);
		// echo $response; die;
		$result = json_decode($response, true);
		if($result['result'] =='success'){
			return $result['data'];
		}
		else{
			return array();
		}
	}
	
	public function TargetSalesman_KPI_AmbilAchievementDetail()
	{
		// echo json_encode($_SESSION); die;
		$post['api'] = 'APITES';
		$post['KodeTarget'] = $this->input->post('KodeTarget');
		$post['NoRequestKPI'] =  $this->input->post('NoRequestKPI');
		$post['NoRequestAcv'] =  $this->input->post('NoRequestAcv');
		//------------------------------------------------
		$URL = $this->API_URL."/Achievementkpisalesman/TargetSalesman_KPI_AmbilAchievementDetail";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
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
		$URL = $this->API_URL."/Achievementkpisalesman/Master_Template_Target_KPI_AmbilList";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
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
		$URL = $this->API_URL."/Achievementkpisalesman/Master_Template_Target_KPI_AmbilList_Detail";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
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
		$URL = $this->API_URL."/Achievementkpisalesman/SetKodeTarget";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
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
		$URL = $this->API_URL."/Achievementkpisalesman/Master_KPI_AmbilList";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
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
	
	public function GetLevelSalesman()
	{	
		$post['api'] = 'APITES';
		// $post['svr'] = $_SESSION["conn"]->Server;
		// $post['db'] = $_SESSION["conn"]->Database;
		$post['kode_salesman'] = $_SESSION["logged_in"]['salesmanid'];
		//------------------------------------------------
		// $URL = $_SESSION["conn"]->AlamatWebService.API_BKT."/Achievementkpisalesman/GetLevelSalesman";
		$URL = $this->API_URL."/Targetkpisalesman/GetLevelSalesman";
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
	
	public function GetWilayah()
	{	
		$post['api'] = 'APITES';
		// $post['svr'] = $_SESSION["conn"]->Server;
		// $post['db'] = $_SESSION["conn"]->Database;
		//------------------------------------------------
		// $URL = $_SESSION["conn"]->AlamatWebService.API_BKT."/Achievementkpisalesman/TargetSalesman_AmbilListWilayahSalesman";
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
	
	public function GetKajul()
	{	
		$post['api'] = 'APITES';
		// $post['svr'] = $_SESSION["conn"]->Server;
		// $post['db'] = $_SESSION["conn"]->Database;
		$level_salesman = ISSET($_SESSION['logged_in']['level_salesman']) ? $_SESSION['logged_in']['level_salesman'] : '';
		// if($this->is_developer() || $level_salesman==0 || $level_salesman==1 || $level_salesman==9){ //ambil semua list salesman (tanpa terkecuali)
		if($level_salesman=='' || $level_salesman==0 || $level_salesman==1 || $level_salesman==9){ //ambil semua list salesman (tanpa terkecuali)
			// $level_salesman=='' // dibuka untuk non-salesman; role di atur dari master role;
			$post['wilayah'] = $this->input->post('wilayah_salesman');
			// $URL = $_SESSION["conn"]->AlamatWebService.API_BKT."/Achievementkpisalesman/TargetSalesman_AmbilListSalesman";
			$URL = $this->API_URL."/Targetkpisalesman/TargetSalesman_AmbilListSalesman";
			//------------------------------------------------
			$response = CURLPOSTJSON($URL, $post);
			// echo $response;die; //------------------------------------------------
			$result = json_decode($response, true);
			if($result['result'] =='success'){
				echo $response;
			}
			else{
				echo $result['error']; die;
			}
		}
		else if ($level_salesman>= 70 && $level_salesman < 80){ //ambil semua list salesman dengan bawahan dari user yg login (sesuai $_SESSION['logged_in']['salesmanid'])
			$post['kode_supervisor'] = $_SESSION['logged_in']['salesmanid'];
			// $URL = $_SESSION["conn"]->AlamatWebService.API_BKT."/Achievementkpisalesman/TargetSalesman_AmbilListSalesmanByKodeSupervisor";
			$URL = $this->API_URL."/Targetkpisalesman/TargetSalesman_AmbilListSalesmanByKodeSupervisor";
			//------------------------------------------------
			$response = CURLPOSTJSON($URL, $post);
			// echo $response;die; //------------------------------------------------
			$result = json_decode($response, true);
			if($result['result'] =='success'){
				echo $response;
			}
			else{
				echo $result['error']; die;
			}
		}
		else{
			$result['result'] = 'success';
			$result['data'][] = array('Kd_Slsman'=>$_SESSION['logged_in']['salesmanid'],'Nm_Slsman'=>$_SESSION['logged_in']['username']);
			$result['error'] = '';
			echo json_encode($result);
		}
	}
	
	public function GetKategori()
	{	
		$post['api'] = 'APITES';
		$post['jenis'] = 'SALESMAN';
		//------------------------------------------------
		$URL = $this->API_URL."/Targetkpisalesman/Kategori";
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
	
	public function GetAtasan($kode_salesman)
	{	
		$post['api'] = 'APITES';
		$post['jenis'] = 'SALESMAN';
		$post['Kd_Slsman'] = $kode_salesman;
		//------------------------------------------------
		$URL = $this->API_URL."/Targetkpisalesman/GetAtasan";
		$response = CURLPOSTJSON($URL, $post);
		// echo $result;die;
		$result = json_decode($response, true);
		if($result['result'] =='success'){
			return $result['atasan'];
		}
		else{
			echo $result['error']; die;
		}
	}	
	
	public function save()
	{
		// echo json_encode($_SESSION); die;
		$post = $this->PopulatePost();
		$post['api'] = 'APITES';
		$post['spv_userid'] = $_SESSION['logged_in']['userid'];
		$post['spv_name'] = $_SESSION['logged_in']['username'];
		$post['spv_email'] = $_SESSION['logged_in']['useremail'];
		$post['user'] = $_SESSION['logged_in']['username'];
		// $post['kode_lokasi'] = $_SESSION['logged_in']['branch_id'];
		$post['kode_lokasi'] = $this->GetKdLokasi($post['wilayahsalesman']);
		// echo json_encode($post); die;
		//------------------------------------------------
		$URL = $this->API_URL."/Achievementkpisalesman/save";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
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
			echo json_encode(array('result'=>'failed','error'=>'Simpan Request Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err));
		}
		else{
			$result = json_decode($response, true);
			echo json_encode($result);
		}
	}
	
	public function sendrequest()
	{
		$post = $this->PopulatePost();
		$NoRequestAcv = $post['kajul'].'_A'.date('YmdHis');	
		$post['api'] = 'APITES';
		$post['norequestacv'] = $NoRequestAcv;
		$post['user_name'] = $_SESSION['logged_in']['username'];
		$post['user_email'] = $_SESSION['logged_in']['useremail'];
		// $post['kode_lokasi'] = $_SESSION['logged_in']['branch_id'];
		$post['kode_lokasi'] = $this->GetKdLokasi($post['wilayah']);
		// echo json_encode($post); die;
		//------------------------------------------------
		$URL = $this->API_URL."/Achievementkpisalesman/sendrequest";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		
		// echo $response."<br>"; //die; //-------------------------------------------------
		
		if($httpcode!=200){
			echo json_encode(array('result'=>'failed','error'=>'Request Target KPI Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err));
		}
		else{
			$result = json_decode($response, true);
			if($result['result']=='success'){
				foreach($result['data'] as $res){
					//EMAIL_CODE, EMAIL_TO, IS_SUCCESS, IS_DEADLINE, DEADLINE_APPROVAL_BY, DEADLINE_APPROVAL_EMAIL
						
					$data['ApprovalType']='ACHIEVEMENT KPI SALESMAN';
					$data['RequestNo']=$post['norequestacv'];
					$data['RequestBy']=$post['user_name'];
					$data['RequestDate']=date('Y-m-d H:i:s');
					$data['RequestByName']=$post['user_name'];
					$data['RequestByEmail']=$post['user_email'];
					
					$data['ApprovedBy']=rtrim($res['EMAIL_CODE'],';');
					$data['ApprovedByName']=rtrim($res['EMAIL_CODE'],';');
					$data['ApprovedByEmail']=str_replace('"','',$res['EMAIL_TO']);
					$data['ApprovalStatus']='UNPROCESSED';
					$data['AddInfo2']='Kategori';
					$data['AddInfo2Value']=$post['kpi_kategori'];
					$data['AddInfo5']='Kode Lokasi';
					$data['AddInfo5Value']=$post['kode_lokasi'];
					$data['AddInfo6']='Periode';
					$data['AddInfo6Value']=$post['periode'];
					$data['AddInfo9']='Wilayah';
					$data['AddInfo9Value']=$post['wilayah'];
					$data['AddInfo12']='Week';
					$data['AddInfo12Value']=$post['week'];
					$data['ApprovalNeeded']=1;
					$data['Priority']=1;
					$data['BhaktiFlag']='UNPROCESSED';
					$data['IsCancelled']=0;
					$data['LocationCode']='HO';
					$data['IsEmailed']=$res['IS_SUCCESS'];
					$data['EmailedDate']=($res['IS_SUCCESS']==1)?date('Y-m-d H:i:s'):NULL;
					$result = $this->insert_approval($data);
					if($res['IS_DEADLINE']==1 && str_replace('"','',$res['EMAIL_TO'])!=$res['DEADLINE_APPROVAL_EMAIL']){
					
						$data['ApprovalType']='ACHIEVEMENT KPI SALESMAN';
						$data['RequestNo']=$post['norequestacv'];
						$data['RequestBy']=$post['user_name'];
						$data['RequestDate']=date('Y-m-d H:i:s');
						
						// $data['RequestByName']=rtrim($res['EMAIL_CODE'],';');
						// $data['RequestByEmail']=str_replace('"','',$res['EMAIL_TO']);
						
						$data['RequestByName']=$post['user_name'];
						$data['RequestByEmail']=$post['user_email'];
						
						$data['ApprovedBy']=rtrim($res['DEADLINE_APPROVAL_BY'],';');
						$data['ApprovedByName']=rtrim($res['DEADLINE_APPROVAL_BY'],';');
						$data['ApprovedByEmail']=$res['DEADLINE_APPROVAL_EMAIL'];
						$data['ApprovalStatus']='UNPROCESSED';
						
						$data['AddInfo2']='Kategori';
						$data['AddInfo2Value']=$post['kpi_kategori'];
						
						$data['AddInfo5']='Kode Lokasi';
						$data['AddInfo5Value']=$post['kode_lokasi'];
						
						$data['AddInfo6']='Periode';
						$data['AddInfo6Value']=$post['periode'];
						$data['AddInfo9']='Wilayah';
						$data['AddInfo9Value']=$post['wilayah'];
						$data['AddInfo12']='Week';
						$data['AddInfo12Value']=$post['week'];
						$data['ApprovalNeeded']=1;
						$data['Priority']=2;
						$data['BhaktiFlag']='UNPROCESSED';
						$data['IsCancelled']=0;
						$data['LocationCode']='HO';
						$data['IsEmailed']=0;
						$data['EmailedDate']=NULL;
						$result = $this->insert_approval($data);
					}
				}
			}
			echo json_encode($result);
		}
	}		
	
	public function cancel()
	{
		// echo json_encode($_SESSION); die;
		$post = $this->PopulatePost();
		$post['api'] = 'APITES';
		$post['user'] = $_SESSION['logged_in']['username'];
		// echo json_encode($post); die;
		//------------------------------------------------
		$URL = $this->API_URL."/Achievementkpisalesman/cancel";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		
		// echo json_encode($response); die;
		// echo json_encode($response).'<br><br><br>';
		
		if($httpcode!=200){
			echo json_encode(array('result'=>'failed','error'=>'Cancel Request Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err));
		}
		else{
			$result = json_decode($response, true);
			if($result['result'] =='success'){
				if(ISSET($result['delete_approval']) && $result['delete_approval']==true){
					$data = array();
					$data['ApprovalType'] = 'ACHIEVEMENT KPI SALESMAN';
					$data['RequestNo'] = $post['norequestacv'];
					$data['ApprovedBy'] = $_SESSION['logged_in']['username'];
					$data['CancelledBy'] = $_SESSION['logged_in']['username'];
					$data['CancelledByName'] = $_SESSION['logged_in']['username'];
					$data['CancelledNote'] = $post['note'];
					$data['CancelledByEmail'] = $_SESSION['logged_in']['useremail'];
					$result = $this->cancel_approval($data);
				}
			}
			echo json_encode($result);
		}
	}
	
	
	public function insert_approval($data)
	{
		$result = $this->approvalmodel->doaction('insert', $data);
		if($result['pesan'] == 'Request Ini Berhasil Diinsert'){
			return array('result'=>'success','error'=>'');
		}
		else{
			return array('result'=>'failed','error'=>$result['pesan']);
		}
	}
	
	public function cancel_approval($data)
	{
		$result = $this->approvalmodel->doaction('cancel', $data);
		return array('result'=>'success','error'=>''); // abaikan pesan error, karena ada norequest yg sudah diapprove tidak bisa dicancel, tapi di KPI boleh dicancel
		
		// if($result['pesan'] == 'Request Ini Berhasil Dicancel'){
			// return array('result'=>'success','error'=>'');
		// }
		// else{
			// return array('result'=>'failed','error'=>$result['pesan']);
		// }
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
        $URL = $this->API_URL."/Achievementkpisalesman/AmbilAchievementKPI?req=".urlencode($noRequest)."&app=".urlencode($approvedBy);
        $GetRequest = json_decode(file_get_contents($URL), true);
        if($GetRequest["result"]=="SUCCESS") {

            $req = $GetRequest["data"];

            $style = '<style>
                *{
                    font-family:"Arial";
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
            $header.= "Banyak Salesman: <b>".count($req["ListKPI"])."</b><br><br>";

            $detail = "";
            $detailhd = "";
            $detaildt = "";
            $details = $req["ListKPI"];
            // echo(json_encode($details)."<br>");

            $No = 0;

            $detailhd.= "<table>";
            $detailhd.= "<tr>";
            $detailhd.= "   <th width='4%''>No</th>";
            $detailhd.= "   <th width='10%'>Salesman</th>";
            $detailhd.= "   <th width='8%'>Key Performance Indicator</th>";
            $detailhd.= "   <th width='4%'>Deskripsi</th>";
            $detailhd.= "   <th width='8%'>Target</th>";
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
			$detailhd.= "   <th width='27%'>History/Status</th>";
            $detailhd.= "</tr>";
			
            $TotalWaiting = 0;

            for($i=0; $i<count($details); $i++) {
                $No+= 1;
                $TotalBobot = 0;
                $dt = $details[$i];
                $detaildt = "";
				
                $ApprovalHistory = $dt["APPROVALHISTORY"];
                $l = count($ApprovalHistory);
                // echo("Jumlah History: ".$l."<br>");
                $HISTORY = "";
                if ($l>0) {
                    for($j=0; $j<$l; $j++) {
                        $HistoryStatus = $ApprovalHistory[$j]["HistoryStatus"];
                        $HistoryDate = date("d-M-Y H:i:s", strtotime($ApprovalHistory[$j]["HistoryDate"]));
                        $UserName = $ApprovalHistory[$j]["UserName"];
                        $HistoryNote = (($ApprovalHistory[$j]["HistoryNote"]==null)?"":"[".$ApprovalHistory[$j]["HistoryNote"]."]");
                        $HISTORY.= $HistoryDate." - ".$UserName." - ".$HistoryStatus." ".$HistoryNote."<br>"; 
                    }
                } else {
                    $HISTORY = "-";
                }

                $KPIs = $dt["DETAILS"];     
                $k = count($KPIs);
                // echo("Jumlah KPI : ".$k."<br>");
                for($j=1; $j<$k;$j++) {
                    $detaildt.= "<tr>";
                    $detaildt.= "   <td class='target'>".$KPIs[$j]["KPIName"]."</td>";
                    $detaildt.= "   <td class='target'>".$KPIs[$j]["KPINote"]."</td>";
                    $detaildt.= "   <td align='right' class='target'>".number_format($KPIs[$j]["KPITarget"],2)."</td>";
                    $detaildt.= "   <td align='right' class='target'>".number_format($KPIs[$j]["KPIBobot"],2)."</td>";
                    $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek1"],2)."</td>";
                    $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek2"],2)."</td>";
                    $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek3"],2)."</td>";
                    $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek4"],2)."</td>";
                    if ($totalWeek>=5) $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek5"],2)."</td>";
                    if ($totalWeek==6) $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek6"],2)."</td>";
                    $detaildt.= "   <td align='right' class='achievement'><b>".number_format($KPIs[$j]["AcvTotal"],2)."</b></td>";
                    $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvPersen"],2)."</td>";
                    $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvBobot"],2)."</td>";
                    $detaildt.= "</tr>";
                    // $TotalBobot += $KPIs[$j]["KPIBobot"];
                }

                // $TotalBobot += $KPIs[0]["KPIBobot"];

                if($k==0) {$k=1;} //rowspan tidak boleh 0
                
                $detailhd.="<tr>";
                $detailhd.="    <td rowspan='".$k."' class='target'>".$No."</td>";
                $detailhd.="    <td rowspan='".$k."' class='target'>".$dt["NM_SLSMAN"]."<br>&nbsp;<em>".$dt["CATATAN"]."</em></td>";    

                if ($dt["REQUESTSTATUS"]=="CANCELLED") {
                    if ($totalWeek>=5) {
                        $detailhd.= "   <td colspan='12' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CANCELLEDBY"]." ".$dt["CANCELLEDDATE"]." ".$dt["CANCELLEDNOTE"]."</td>";
                    } else if ($totalWeek==6) {
                        $detailhd.= "   <td colspan='13' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CANCELLEDBY"]." ".$dt["CANCELLEDDATE"]." ".$dt["CANCELLEDNOTE"]."</td>";
                    } else {                        
                        $detailhd.= "   <td colspan='11' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CANCELLEDBY"]." ".$dt["CANCELLEDDATE"]." ".$dt["CANCELLEDNOTE"]."</td>";
                    }
                    $detailhd.="    <td align='right' class='final' rowspan='".$k."'><b></b></td>";
                } else if ($dt["REQUESTSTATUS"]=="CLOSED") {
                    if ($totalWeek>=5) {
                        $detailhd.= "   <td colspan='12' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CLOSEDBY"]." ".$dt["CLOSEDDATE"]." ".$dt["CLOSEDNOTE"]."</td>";
                    } else if ($totalWeek==6) {
                        $detailhd.= "   <td colspan='13' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CLOSEDBY"]." ".$dt["CLOSEDDATE"]." ".$dt["CLOSEDNOTE"]."</td>";
                    } else {                        
                        $detailhd.= "   <td colspan='11' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CLOSEDBY"]." ".$dt["CLOSEDDATE"]." ".$dt["CLOSEDNOTE"]."</td>";
                    }
                    $detailhd.="    <td align='right' class='final' rowspan='".$k."'><b></b></td>";
                } else {    
                    $detailhd.= "   <td class='target'>".$KPIs[0]["KPIName"]."</td>";
                    $detailhd.= "   <td class='target'>".$KPIs[0]["KPINote"]."</td>";
                    $detailhd.= "   <td align='right' class='target'>".number_format($KPIs[0]["KPITarget"],2)."</td>";
                    $detailhd.= "   <td align='right' class='target'>".number_format($KPIs[0]["KPIBobot"],2)."</td>";
                    // $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek1"],2)."<br><br><span class='modified'>".$KPIs[0]["Week1ModifiedBy"]."<br>".$KPIs[0]["Week1ModifiedDate"]."</span></td>";
                    // $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek2"],2)."<br><br><span class='modified'>".$KPIs[0]["Week2ModifiedBy"]."<br>".$KPIs[0]["Week2ModifiedDate"]."</span></td>";
                    // $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek3"],2)."<br><br><span class='modified'>".$KPIs[0]["Week3ModifiedBy"]."<br>".$KPIs[0]["Week3ModifiedDate"]."</span></td>";
                    // $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek4"],2)."<br><br><span class='modified'>".$KPIs[0]["Week4ModifiedBy"]."<br>".$KPIs[0]["Week4ModifiedDate"]."</span></td>";
                    // if ($totalWeek>=5) $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek5"],2)."<br><br><span class='modified'>".$KPIs[0]["Week5ModifiedBy"]."<br>".$KPIs[0]["Week5ModifiedDate"]."</span></td>";
                    // if ($totalWeek==6) $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek6"],2)."<br><br><span class='modified'>".$KPIs[0]["Week6ModifiedBy"]."<br>".$KPIs[0]["Week6ModifiedDate"]."</span></td>";
                    $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek1"],2)."</td>";
                    $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek2"],2)."</td>";
                    $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek3"],2)."</td>";
                    $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek4"],2)."</td>";
                    if ($totalWeek>=5) $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek5"],2)."</td>";
                    if ($totalWeek==6) $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek6"],2)."</td>";
                    $detailhd.= "   <td align='right' class='achievement'><b>".number_format($KPIs[0]["AcvTotal"],2)."</b></td>";
                    $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvPersen"],2)."</td>";
                    $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvBobot"],2)."</td>";
                    $detailhd.="    <td align='right' class='final' rowspan='".$k."'><b>".number_format($dt["TOTAL_ACHIEVEMENT"], 0)."</b></td>";
                }
                //reegan edit sementara, buat aktifin tombol approve dan reject | akses : ApproveRejectAchievement
                //$dt["STATUS"] = "WAITING FOR APPROVAL";
                if ($dt["STATUS"]=="WAITING FOR APPROVAL") {
                    $TotalWaiting += 1;
                    $detailhd .= "<td rowspan='".$k."'><input type='checkbox' class='cek_pilih' name='salesman[]' value='".$dt["KODE_TARGET"]."' onchange='cek()' checked></td>";
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
                // die($detailhd);
            }
            $detailhd.="</table>";

            // $detail = $detailhd.$detaildt;
            
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
                if($req["RequestStatus"]=='EXPIRED')
                    echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>".$req["ApprovalExpiredError"]."</h2></div>";
                else
                    echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>REQUEST SUDAH DIPROSES!</h2></div>";
            }
            $detail.= "</form>";

            echo ($style);
            echo ($header);
            echo ($detail);

        } //else {
        //     else echo "No. PrePO tidak ditemukan";
        // }
    }

    public function ApproveRejectAchievement() 
    {

        $msg = "";
        $data = $this->PopulatePost();
        // echo(json_encode($data)."<br><br>");die;

        //APPROVE
        if(ISSET($data['salesman'])){
            // die("ada Kode Target : <br>".json_encode($data));
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => $this->API_URL."/Achievementkpisalesman/ApproveAchievementKPI",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 1000,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query($data),
            ));
            
            $result = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
                                    
            $result = json_decode($result, true);
            // echo("APPROVE: ".json_encode($result)); die;
            // echo("<br>");

            if ($result["result"]=="SUCCESS") {     

                $msg = "
                <div style='padding:10px;background:#d4edda;border:1px solid #c3e6cb'>
                <center><h2>REQUEST ACHIEVEMENT KPI BERHASIL DIAPPROVE</h2></center>
                </div>";
				
                  $dApproval = array(
                        'ApprovalStatus' => 'APPROVED',
                        'BhaktiFlag' => 'PENDING',
                        'ApprovedDate' => date('Y-m-d H:i:s'),
                    );
                    $wApproval = array(
                        'RequestNo' => $data['no_request'],
                        'ApprovalStatus' => 'UNPROCESSED',
                        'ApprovedByEmail' => $data['app_by'],
                    );
                    $resultEdit = $this->Achievementkpisalesmanmodel->editTblApproval($wApproval,$dApproval);
				
					$getNextPriority = $this->Achievementkpisalesmanmodel->getNextPriority($data);
					if($getNextPriority){
						
						$post['api'] = 'APITES';
						$post['norequestacv'] = $getNextPriority->RequestNo;
						$post['kpi_kategori'] = $getNextPriority->AddInfo2Value;
						$post['user_name'] = $getNextPriority->RequestBy;
						$post['user_email'] = $getNextPriority->RequestByEmail;
						$post['atasan_name'] = $getNextPriority->ApprovedByName;
						$post['atasan_email'] = $getNextPriority->ApprovedByEmail;
						$post['kode_lokasi'] = $getNextPriority->AddInfo5Value;
						// echo json_encode($post);
					
						$URL = $this->API_URL."/index.php/Achievementkpisalesman/sendrequest2";
						$curl = curl_init();
						curl_setopt_array($curl, array(
						CURLOPT_URL => $URL,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_TIMEOUT => 6000,
						CURLOPT_POST => 1,
						CURLOPT_POSTFIELDS => json_encode($post),
						CURLOPT_HTTPHEADER => array("Content-type: application/json")
						));
						
						$response = curl_exec($curl);
						$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
						$err = curl_error($curl);
						curl_close($curl);
						
						// echo $response."<br><br><br>"; //die; //-------------------------------------------------
						
						if($httpcode!=200){
							echo json_encode(array('result'=>'failed','error'=>'Request Target KPI Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err));
						}
						else{
							$result = json_decode($response, true);
							if($result['result']=='success'){
								foreach($result['data'] as $res){
								   $dApproval = array(
										'IsEmailed' => $res['IS_SUCCESS'],
										'EmailedDate' => date('Y-m-d H:i:s'),
									);
									$wApproval = array(
										'RequestNo' => $data['no_request'],
										'ApprovalStatus' => 'UNPROCESSED',
										'ApprovedByEmail' => str_replace('"','',$res['EMAIL_TO'])
									);
									$resultEdit = $this->Achievementkpisalesmanmodel->editTblApproval($wApproval,$dApproval);
								}
							}
							// echo json_encode($result);
						}
					}


            }
            else {
                $msg = "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><center><h2>".$result["error"]."</h2></center></div>";               
            }      
        }
        
        //REJECT
        else{
            // die($data["rejectnote"]);
            // die("Tidak ada Kode Target");
            // die('reject');

            $url = $this->API_URL."/Achievementkpisalesman/RejectAchievementKPI";

            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 1000,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query($data),
            ));
            
            $result = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
                                    
            $result = json_decode($result, true);
            // echo("data: ".json_encode($data)."<br><br>");
            // echo("url webAPI: ".$url."<br><br>");
            // echo("REJECT: ".json_encode($result));
            // echo("<br>");
            // echo("webAPI return :<br>".json_encode($result)."<br><br>");

            if ($result["result"]=="SUCCESS") {
                $msg = "<div style='padding:10px;background:#d4edda;border:1px solid #f5c6cb;'><h2><center>REQUEST ACHIEVEMENT KPI BERHASIL DIREJECT</center></h2></div>";
                
				$dApproval = array(
					'ApprovalStatus' => 'REJECTED',
				);
				$wApproval = array(
					'RequestNo' => $data['no_request'],
					'ApprovalStatus' => 'UNPROCESSED',
				);
				$resultEdit = $this->Achievementkpisalesmanmodel->editTblApproval($wApproval,$dApproval);
                
            }
            else { 
                $msg = "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2><center>".$result["error"]."</center></h2></div>";
            }
        }
        // echo($msg."<br>");
        $this->ViewRequestAchievementKPI($data["no_request"], $data["app_by"], $data["total_week"], $msg);
    }

    public function simpanapproval($parammode, $params, $conn){
        //get list target salesman

        if ($parammode==''){
            $url = $conn->AlamatWebService.API_BKT."/TargetSalesmanApprovalNew/GetListTarget?".
            "parammode=".urlencode($parammode).
            "&mode=".urlencode($params["mode"]).
            "&kategori=".urlencode($params["kategori"]).
            "&userid=".urlencode($params["userid"]).
            "&tanggal=".urlencode($params["tanggal"]).
            "&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID).
            "&pwd=".urlencode(SQL_PWD);
        } else {
            $url = $conn->AlamatWebService.API_BKT."/TargetSalesmanApprovalNew/GetListTarget?".
            "parammode=".urlencode($parammode).
            "&norequest=".urlencode($params["norequest"]).
            "&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID).
            "&pwd=".urlencode(SQL_PWD);
        }

        $result = json_decode(file_get_contents($url),true);

        for ($a=0;$a<count($result);$a++) {
            $params = array();
            $params["ApprovalType"] = $this->approvaltype.' '.$params["kategori"];
            $params["RequestNo"] = $result[$a]["NoRequest"];
            $params["RequestBy"] = $result[$a]["User_Email"];
            $params["RequestDate"] = $result[$a]["EmailedDate"];
            $params["RequestByName"] = $result[$a]["User_Name"];
            $params["RequestByEmail"] = $result[$a]["User_Email"];
            $params["ApprovedBy"] = $result[$a]["UserApproved_ID"];
            $params["ApprovedByName"] = $result[$a]["UserApproved_Name"];
            $params["ApprovedByEmail"] = $result[$a]["UserApproved_Email"];
            $params["ApprovedDate"] = NULL;
            $params["ApprovalStatus"] = "UNPROCESSED";
            $params["ApprovalNote"] = NULL;
            $params["AddInfo1"] = "Kode SPG";
            $params["AddInfo1Value"] = "";
            $params["AddInfo2"] = "Nama SPG";
            $params["AddInfo2Value"] = "";
            $params["AddInfo3"] = "";
            $params["AddInfo3Value"] = "";
            $params["AddInfo4"] = "";
            $params["AddInfo4Value"] = "";
            $params["AddInfo5"] = "";
            $params["AddInfo5Value"] = "";
            $params["AddInfo6"] = "Periode";
            $params["AddInfo6Value"] = $result[$a]["monthyear"];
            $params["AddInfo7"] = "";
            $params["AddInfo7Value"] = "";
            $params["AddInfo8"] = "";
            $params["AddInfo8Value"] = "";
            $params["AddInfo9"] = "Wilayah";
            $params["AddInfo9Value"] = $result[$a]["Wilayah"];
            $params["AddInfo10"] = "";
            $params["AddInfo10Value"] = "";
            $params["AddInfo11"] = "";
            $params["AddInfo11Value"] = "";
            $params["AddInfo12"] = "";
            $params["AddInfo12Value"] = "";
            $params["ApprovalNeeded"] = "";
            $params["Priority"] = "";
            $params["ExpiryDate"] = $result[$a]["Tgl_Akhir"];
            $params["BhaktiFlag"] = "UNPROCESSED";
            $params["BhaktiProcessDate"] = "";
            $params["IsCancelled"] = 0;
            $params["CancelledBy"] = NULL;
            $params["CancelledByName"] = NULL;
            $params["CancelledDate"] = NULL;
            $params["CancelledNote"] = NULL;
            $params["CancelledByEmail"] = NULL;
            $params["LocationCode"] = "HO";
            $params["IsEmailed"] = 1;
            $params["EmailedDate"] = $result[$a]["EmailedDate"];
            $params["approvedbyfrommsconfig"] = $this->approvedbyfrommsconfig;
            $params["expirydatefrommsconfig"] = $this->expirydatefrommsconfig;
            $params["amount"] = 0;
            $params["branchid"] = $result[$a]["kd_lokasi"];
            $x = $this->approval->insert($params);
        }

    }

    public function viewTarget()
	{
        //http://localhost:90/myCompany/TargetSalesmanApproval/viewTarget?norequest=SPSYAT200320200504104406
        $params = array();
        $params["norequest"] = urldecode($this->input->get("norequest"));
        $params["wilayah"] = "JAKARTA";     
               
        //cari AlamatWEBService DB BHAKTI cabang yang diinginkan
        $conn = $this->TargetSalesmanModel->get($params["wilayah"]);
        //die(json_encode($conn));

        if ($conn!=null) {
            $connected = false;

            set_time_limit(3);

            try {
                set_time_limit(0);        
                $TEST_CONN = json_decode(file_get_contents($conn->AlamatWebService.API_BKT."/TargetSalesmanApprovalNew/TestConnection"), true);
                if ($TEST_CONN["result"]=="sukses") {
                    $connected = true;
                }            
            }
            catch (Exception $e) {
                echo $e->getMessage();
            }
            catch (InvalidArgumentException $e) {
                echo $e->getMessage();
            } 

            $url = $conn->AlamatWebService.API_BKT."/TargetSalesmanApprovalNew/ViewDataDiPusatByNoRequest?".
                "norequest=".urlencode($params["norequest"]).
                "&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID).
                "&pwd=".urlencode(SQL_PWD);
            //die($url);

            //die($connected);
            

            if ($connected) {
                set_time_limit(0);
                //$result = json_decode(file_get_contents($url),true);
                $result = file_get_contents($url);
                echo $result;
                //die($result);
                // $x= array();
                // $x["pesan"]=$result["pesan"];
                // echo json_encode($x);

            } else {
                //echo "Cek Function TestConnection";
                echo "Server yang dituju sedang tidak aktif";
            }
        } else {
            echo "Alamat WEB Service Belum Disetting";
        } 
    }

    public function Email_Notifikasi_ByNoRequestNew()
    {
        // http://localhost:90/myCompany/TargetSalesmanApproval/Email_Notifikasi_ByNoRequest?norequest=MDN091230220230202135651
        $params = array();
        $params["norequest"] = urldecode($this->input->get("norequest"));
        $params["wilayah"] = "JAKARTA";     
               
        //cari AlamatWEBService DB BHAKTI cabang yang diinginkan
        $conn = $this->TargetSalesmanModel->get($params["wilayah"]);
        // die(json_encode($conn));

        if ($conn!=null) {
            $connected = false;

            set_time_limit(3);

            try {
                set_time_limit(0);        
                $TEST_CONN = json_decode(file_get_contents($conn->AlamatWebService.API_BKT."/TargetSalesmanApprovalNew/TestConnection"), true);
                if ($TEST_CONN["result"]=="sukses") {
                    $connected = true;
                }            
            }
            catch (Exception $e) {
                echo $e->getMessage();
            }
            catch (InvalidArgumentException $e) {
                echo $e->getMessage();
            } 

            $url = $conn->AlamatWebService.API_BKT."/TargetSalesmanApprovalNew/AmbilDataDiPusatByNoRequest?".
                "norequest=".urlencode($params["norequest"]).
                "&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID).
                "&pwd=".urlencode(SQL_PWD);
            // die($url);
            

            if ($connected) {
                set_time_limit(0);
                $result = json_decode(file_get_contents($url),true);
                
                $this->simpanapproval("NOREQUEST", $params, $conn);

                // $x= array();
                // $x["pesan"]=$result["pesan"];
                // echo json_encode($x);
                echo ($result["pesan"]);

            } else {
                //echo "Cek Function TestConnection";
                echo "Server yang dituju sedang tidak aktif";
            }
        } else {
            echo "Alamat WEB Service Belum Disetting";
        }        
    }
	
	public function GetKdLokasi($wilayah)
	{	
		$post['api'] = 'APITES';
		$post['wilayah'] = $wilayah;
		$URL = $this->API_URL."/Targetkpisalesman/TargetSalesman_KdLokasiByWilayahSalesman";
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


	function weeks($month, $year){
		$num_of_days = date("t", mktime(0,0,0,$month,1,$year)); 
		$lastday = date("t", mktime(0, 0, 0, $month, 1, $year)); 
		$no_of_weeks = 0; 
		$count_weeks = 0; 
		while($no_of_weeks < $lastday){ 
			$no_of_weeks += 7; 
			$count_weeks++; 
		} 
		return $count_weeks;
	} 



	public function Export()
	{
		$wilayah = urldecode($this->input->get("wilayah"));
		$kode_salesman = urldecode($this->input->get("kajul"));
		$kategori = urldecode($this->input->get("kategori"));
		$th = urldecode($this->input->get("th"));
		$bl = urldecode($this->input->get("bl"));
		
		// $wilayah = 'PONTIANAK';
		// $kode_salesman = 'SPS-P01';
		// $kategori = 'TRADISIONAL';
		// $th = 2023;
		// $bl = 1;
		
		$kolom = array("Tahun","Bulan","Cabang","Kode Kategori KPI","Nama Kategori KPI","Kode Salesman","Nama Salesman","Kode KPI","Nama KPI","Unit","Target","Bobot","Acv Week1","Acv Week2","Acv Week3","Acv Week4");
		$week = $this->weeks($bl, $th);		
		if($week>4){
			$kolom[] = "Acv Week5";
		}
		if($week>5){
			$kolom[] = "Acv Week6";
		}
		
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);
		
		$currow = 1;
		$curcol = 1;
		foreach($kolom as $kol){
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $kol);
		}
		$post['api'] = 'APITES';
		$post['kode_salesman'] = $kode_salesman;
		$post['kategori'] = $kategori;
		$post['th'] = $th;
		$post['bl'] = $bl;		
		//------------------------------------------------
		$URL = $this->API_URL."/Achievementkpisalesman/export";
		$response = CURLPOSTJSON($URL, $post);
		// echo $response; die;
		
		$result = json_decode($response, true);
		if($result['result'] =='success'){
			$detail = $result['data'];
			foreach($detail as $data){
				foreach($data['detail'] as $row){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $th);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $bl);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $wilayah);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $kategori);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $kategori);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['EmpId']);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, trim($row['EmpName']));
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['KPICode']);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['KPIName']);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['KPIUnit']);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['KPITarget']);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['KPIBobot']);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['AcvWeek1']);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['AcvWeek2']);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['AcvWeek3']);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['AcvWeek4']);
					if($week>4){
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['AcvWeek5']);
					}
					if($week>5){
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['AcvWeek6']);
					}
				}
			}
			
			
			foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
				$sheet->getColumnDimension($col)->setAutoSize(true);
			} 
			
			$sheet->getStyle("A1:Q1")->getFont()->setBold(true);
			$sheet->getStyle('J2:J'.$currow)->getNumberFormat()->setFormatCode('#,##0');
			$sheet->getStyle('K2:K'.$currow)->getNumberFormat()->setFormatCode('#,##0.00');
			$sheet->getStyle('L2:Q'.$currow)->getNumberFormat()->setFormatCode('#,##0');
			
			$sheet->setSelectedCell('L2');
		
			$filename=$wilayah.' ['.$th.'-'.$bl.']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file
		}
		else{
			die('Error export template achievement KPI! '. $result['error']);
		}
	}
	
	public function import()
	{
		// 0	Tahun
		// 1	Bulan
		// 2	Cabang
		// 3	Kode Kategori KPI
		// 4	Nama Kategori KPI
		// 5	Kode Salesman
		// 6	Nama Salesman
		// 7	Kode KPI
		// 8	Nama KPI
		// 9	Unit
		// 10	Target
		// 11	Bobot
		// 12	AchievementWeek1
		// 13	AchievementWeek2
		// 14	AchievementWeek3
		// 15	AchievementWeek4
		// 16	AchievementWeek5
		// 17	AchievementWeek6
		// 18	TotalAchievement
		// 19	PercentageAchievement
		// 20	BobotAchievement

		if(ISSET($_POST['submit'])){
			
			$kajul = $this->input->post('kajul');
			
			$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($_FILES['excel']['tmp_name']);
			$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
			$spreadsheet = $reader->load($_FILES['excel']['tmp_name']);
			$import = $spreadsheet->getActiveSheet()->toArray();
			// echo json_encode($import);die;
			$header = array_shift($import);
			// echo json_encode($header);die;
			
			$data = array();
			$data['filename'] = $_FILES['excel']['name'];
			$data['kajul'] = $kajul;
			
			// VALIDASI #1 TAHUN DAN BULAN TIDAK BOLEH BEDA
			$tahun = '';
			$bulan = '';
			$wilayah = '';
			$kategori = '';
			$error = '';
			$validasi1 = true;
			foreach($import as $row){
				if($tahun!='' && $tahun != $row[0]){
					$validasi1 = false;
					break;
				}
				if($bulan!='' && $bulan != $row[1]){
					$validasi1 = false;
					break;
				}
				if($wilayah!='' && $wilayah != $row[2]){
					$validasi1 = false;
					break;
				}
				if($kategori!='' && $kategori != $row[3]){
					$validasi1 = false;
					break;
				}
				$tahun = $row[0];
				$bulan = $row[1];
				$wilayah = $row[2];
				$kategori = $row[3];
			}
			
			if($validasi1==false){
				$error = 'Terdapat perbedaan tahun, bulan atau wilayah. Silahkan cek kembali file nya!';
			}
			if($error==''){
				// VALIDASI #2 CEK DATA TARGET 
				$post['api'] = 'APITES';
				$post['kode_salesman'] = $kajul;
				$post['kategori'] = $kategori;
				$post['th'] = $tahun;
				$post['bl'] = $bulan;		
				// echo json_encode($post);die;
				$week = $this->weeks($bulan, $tahun);
				//------------------------------------------------
				$URL = $this->API_URL."/Achievementkpisalesman/export";
				$response = CURLPOSTJSON($URL, $post);
				// echo $response; die;
				
				$result = json_decode($response, true);
				if($result['result'] =='success'){
					$detail = $result['data'];
					$new = array();
					foreach($import as $row){
						foreach($detail as $i => $dt){
							foreach($dt['detail'] as $r){
								if($row[5]==trim($r['EmpId']) && $row[7]== $r['KPICode']){
								
									// Kd_Slsman: "PTK-010",
									// Nm_Slsman: "TEDY HALIM",
									// Wil_Slsman: "PONTIANAK",
									// Level_Slsman: 90,
									// Tgl_Awal: "2023-08-01 00:00:00.000",
									// Tgl_Akhir: "2023-08-31 00:00:00.000",
									// Kode_Target: "PTK-0102308",
									// UserID: "5575",
									
									$new[$i]['kode_salesman']= $dt['Kd_Slsman'];
									$new[$i]['nama_salesman']= $dt['Nm_Slsman'];
									$new[$i]['wilayah_salesman']= $dt['Wil_Slsman'];
									$new[$i]['level_salesman']= $dt['Level_Slsman'];
									$new[$i]['nama_level']= $dt['Nama_Level'];
									$new[$i]['tgl_awal']= $dt['Tgl_Awal'];
									$new[$i]['tgl_akhir']= $dt['Tgl_Akhir'];
									$new[$i]['kode_target']= $dt['Kode_Target'];
									$new[$i]['userid']= $dt['UserID'];
									$new[$i]['kode_target']= $r['KodeTarget'];
									$new[$i]['kode_lokasi']= $r['KodeLokasi'];
									$new[$i]['training']= $r['Training'];
									
									/*
									// hapus tanda koma(,) dalam angka
									$row[10] = str_replace(',','',$row[10]);
									
									$row[12] = str_replace(',','',$row[12]);
									$row[13] = str_replace(',','',$row[13]);
									$row[14] = str_replace(',','',$row[14]);
									$row[15] = str_replace(',','',$row[15]);
									$total_achievement = floatval($row[12]) + floatval($row[13]) + floatval($row[14]) + floatval($row[15]);
									if($week>4){
										$row[16] = str_replace(',','',$row[16]);
										$total_achievement += floatval($row[16]);
									}
									if($week>5){
										$row[17] = str_replace(',','',$row[17]);
										$total_achievement += floatval($row[17]);
									}
									$row[] = $total_achievement;
									*/
									
									// hapus tanda koma(,) dalam angka
									
									$r['AcvWeek1'] = str_replace(',','',$row[12]);
									$r['AcvWeek2'] = str_replace(',','',$row[13]);
									$r['AcvWeek3'] = str_replace(',','',$row[14]);
									$r['AcvWeek4'] = str_replace(',','',$row[15]);
									
									$total_achievement = floatval($r['AcvWeek1']) + floatval($r['AcvWeek2']) + floatval($r['AcvWeek3']) + floatval($r['AcvWeek4']);
									
									if($week>4){
										$r['AcvWeek5'] = str_replace(',','',$row[16]);
										$total_achievement += floatval($r['AcvWeek5']);
									}
									if($week>5){
										$r['AcvWeek6'] = str_replace(',','',$row[17]);
										$total_achievement += floatval($r['AcvWeek6']);
									}
									$r['AcvTotal'] = $total_achievement; //$total_achievement;
									
									$new[$i]['dt'][] = $r;
								}
							}
						}
					}
					// echo json_encode($new); die;
					
					$data['tahun'] = $tahun;
					$data['bulan'] = $bulan;
					$data['week'] = $this->weeks($bulan, $tahun);;
					$data['kategori'] = $kategori;
					$data['wilayah'] = $wilayah;
					$data['header'] = $header;
					$data['data'] = $new;
					// echo json_encode($data); die;
					
				}
				else{
					$error = 'Error import template achievement KPI! '. $result['error'];
				}
			}
			$data['error'] = $error;
			// echo json_encode($data);die;
			$this->RenderView('Achievementkpisalesmanimportview', $data);
		}
		elseif(ISSET($_POST['save_import'])){
			// echo json_encode($_POST); die;
			$post = $this->PopulatePost();
			$post['api'] = 'APITES';
			$post['user'] = $_SESSION['logged_in']['username'];
			// $post['kode_lokasi'] = $_SESSION['logged_in']['branch_id'];
			$post['kode_lokasi'] = $this->GetKdLokasi($post['wilayah']);
			// echo json_encode($post); die;
			//------------------------------------------------
			$URL = $this->API_URL."/Achievementkpisalesman/save_import";
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 6000,
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
				echo json_encode(array('result'=>'failed','error'=>'Simpan Request Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err));
			}
			else{
				$result = json_decode($response, true);
				echo json_encode($result);
			}
			
		}
		else{
			$kajul = $this->input->get('kajul');
			if($kajul==''){
				redirect("Achievementkpisalesman");	
			}
			$data['kajul'] = $kajul;
			$this->RenderView('Achievementkpisalesmanimportview', $data);
		}	
	}
}																																			