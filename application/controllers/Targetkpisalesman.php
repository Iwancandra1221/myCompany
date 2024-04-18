<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
class Targetkpisalesman extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Targetkpisalesmanmodel');
		$this->load->model('approvalmodel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
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
			
				$wilayah_salesman = $_SESSION['logged_in']['branch_id'].' | '.$_SESSION['logged_in']['wilayah_salesman'];
				$level_salesman = $_SESSION['logged_in']['level_salesman'];
					
				// LOGIC DIAMBIL DARI APP TargetSalesman FormInputTargetKPI_Load
				if($level_salesman==0 || $level_salesman==1 || $level_salesman==9){
					$data['wilayah'] = $this->GetWilayah();	
					//ambil semua list salesman (tanpa terkecuali)
				
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
				$this->RenderView('TargetKPISalesmanView',$data);
			}
			else die('Level Salesman belum disettting. mohon di setting terlebih dahulu!');
		}
		// else if($this->is_developer()){
		else{
			$data['wilayah'] = $this->GetWilayah();
			$data['kategori'] = $this->GetKategori();		
			// $data['atasan'] = $this->GetAtasan();
			$this->RenderView('TargetKPISalesmanView',$data);
		}
		// else die('Menu ini khusus user yang ada kode salesman nya. mohon di setting terlebih dahulu!');
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
		$URL = $this->API_URL."/index.php/Targetkpisalesman/TargetSalesman_KPI_LoadBawahan";
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
	
	public function TargetSalesman_KPI_AmbilTargetDetail()
	{
		// echo json_encode($_SESSION); die;
		$post['api'] = 'APITES';
		$post['KodeTarget'] = $this->input->post('KodeTarget');
		$post['NoRequestKPI'] =  $this->input->post('NoRequestKPI');
		//------------------------------------------------
		$URL = $this->API_URL."/index.php/Targetkpisalesman/TargetSalesman_KPI_AmbilTargetDetail";
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
		$URL = $this->API_URL."/index.php/Targetkpisalesman/Master_Template_Target_KPI_AmbilList";
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
		$URL = $this->API_URL."/index.php/Targetkpisalesman/Master_Template_Target_KPI_AmbilList_Detail";
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
		$URL = $this->API_URL."/index.php/Targetkpisalesman/SetKodeTarget";
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
		$URL = $this->API_URL."/index.php/Targetkpisalesman/Master_KPI_AmbilList";
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
		// $URL = $_SESSION["conn"]->AlamatWebService.$this->API_BKT."/Targetkpisalesman/GetLevelSalesman";
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
		// $URL = $_SESSION["conn"]->AlamatWebService.$this->API_BKT."/Targetkpisalesman/TargetSalesman_AmbilListWilayahSalesman";
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
			// $URL = $_SESSION["conn"]->AlamatWebService.$this->API_BKT."/Targetkpisalesman/TargetSalesman_AmbilListSalesman";
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
			// $URL = $_SESSION["conn"]->AlamatWebService.$this->API_BKT."/Targetkpisalesman/TargetSalesman_AmbilListSalesmanByKodeSupervisor";
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
		$URL = $this->API_URL."/index.php/Targetkpisalesman/Kategori";
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
		$URL = $this->API_URL."/index.php/Targetkpisalesman/GetAtasan";
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
		$post['atasan'] = $post['atasan']; //$_SESSION['logged_in']['userid'];
		$post['user'] = $_SESSION['logged_in']['username'];
		// $post['kode_lokasi'] = $_SESSION['logged_in']['branch_id'];
		$post['kode_lokasi'] = $this->GetKdLokasi($post['wilayahsalesman']);
		// echo json_encode($post); die;
		//------------------------------------------------
		$URL = $this->API_URL."/index.php/Targetkpisalesman/save";
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
		$NoRequestKPI = $post['kajul'].'_'.date('YmdHis');	
		$post['api'] = 'APITES';
		$post['norequestkpi'] = $NoRequestKPI;
		$post['kode_lokasi'] = $this->GetKdLokasi($post['wilayah']);
		$post['user_name'] = $_SESSION['logged_in']['username'];
		$post['user_email'] = $_SESSION['logged_in']['useremail'];
		// echo json_encode($post); die;
		//------------------------------------------------
		$URL = $this->API_URL."/index.php/Targetkpisalesman/sendrequest";
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
		
		// $response = '{"result":"success","error":"","data":[{"EMAIL_CODE":"ERROR AUTOSENT;","EMAIL_TO":"\"fototampung123@gmail.com\"","IS_SUCCESS":1,"IS_DEADLINE":0,"DEADLINE_APPROVAL_BY":"MARKOM;","DEADLINE_APPROVAL_EMAIL":"datatampung001@gmail.com"}]}';
		if($httpcode!=200){
			echo json_encode(array('result'=>'failed','error'=>'Request Target KPI Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err));
		}
		else{
			$result = json_decode($response, true);
			if($result['result']=='success'){
				foreach($result['data'] as $res){
					//EMAIL_CODE, EMAIL_TO, IS_SUCCESS, IS_DEADLINE, DEADLINE_APPROVAL_BY, DEADLINE_APPROVAL_EMAIL
					
					$data['ApprovalType']='TARGET KPI SALESMAN';
					$data['RequestNo']=$post['norequestkpi'];
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
					// $data['AddInfo12']='Week';
					// $data['AddInfo12Value']=$post['week'];
					$data['ApprovalNeeded']=1;
					$data['Priority']=1;
					$data['BhaktiFlag']='UNPROCESSED';
					$data['IsCancelled']=0;
					$data['LocationCode']='HO';
					$data['IsEmailed']=$res['IS_SUCCESS'];
					$data['EmailedDate']=($res['IS_SUCCESS']==1)?date('Y-m-d H:i:s'):NULL;
					$result = $this->insert_approval($data);
					if($res['IS_DEADLINE']==1 && str_replace('"','',$res['EMAIL_TO'])!=$res['DEADLINE_APPROVAL_EMAIL']){
						$data['ApprovalType']='TARGET KPI SALESMAN';
						$data['RequestNo']=$post['norequestkpi'];
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
						// $data['AddInfo12']='Week';
						// $data['AddInfo12Value']=$post['week'];
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
		$URL = $this->API_URL."/index.php/Targetkpisalesman/cancel";
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
		// echo json_encode($response); //die;
		
		if($httpcode!=200){
			echo json_encode(array('result'=>'failed','error'=>'Cancel Request Gagal! URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err));
		}
		else{
			$result = json_decode($response, true);
			if($result['result'] =='success'){
				if(ISSET($result['delete_approval']) && $result['delete_approval']==true){
					$data = array();
					$data['ApprovalType'] = 'TARGET KPI SALESMAN';
					$data['RequestNo'] = $post['norequestkpi'];
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
	
    public function ProsesTargetKPI()
    {
        $noRequest = urldecode($this->input->get("req"));
        $approvedBy = urldecode($this->input->get("app"));
        $this->ViewRequestTargetKPI($noRequest, $approvedBy);
    }

    public function ViewRequestTargetKPI($noRequest, $approvedBy, $msg="") 
    {
        $URL = $this->API_URL."/targetkpisalesman/AmbilTargetKPI?req=".urlencode($noRequest)."&app=".urlencode($approvedBy);
        $GetRequest = json_decode(file_get_contents($URL), true);
        if($GetRequest["result"]=="SUCCESS") {

            $req = $GetRequest["data"];

            $style = '<style>
                *{
					font-family: Arial, sans-serif;
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
            $header.= "Banyak Salesman: <b>".count($req["ListTargetKPI"])."</b><br><br>";

            $detail = "";
            $detailhd = "";
            $detaildt = "";
            $details = $req["ListTargetKPI"];
            // echo(json_encode($details)."<br>");

            $No = 0;

            $detailhd.= "<table>";
            $detailhd.= "<tr>";
            $detailhd.= "   <th width='5%''>No</th>";
            $detailhd.= "   <th width='15%'>Salesman</th>";
            $detailhd.= "   <th width='8%'>Periode</th>";
            $detailhd.= "   <th width='15%'>Key Performance Indicator</th>";
            $detailhd.= "   <th width='5%'>Deskripsi</th>";
            $detailhd.= "   <th width='5%'>Target</th>";
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
                        $HistoryNote = (($ApprovalHistory[$j]["HistoryNote"]==null)?"":"[".$ApprovalHistory[$j]["HistoryNote"]."]");
                        $HISTORY.= $HistoryDate." - ".$UserName." - ".$HistoryStatus.$HistoryNote."<br>"; 
                    }
                } else {
                    $HISTORY = "-";
                }
                // die($HISTORY);

                $KPIs = $dt["DETAILS"];     
                $k = count($KPIs);

                for($j=1; $j<$k;$j++) {
                    $detaildt.= "<tr style='background:".$bg.";'>";
                    $detaildt.= "   <td>".$KPIs[$j]["KPIName"]."</td>";
                    $detaildt.= "   <td>".$KPIs[$j]["KPINote"]."</td>";
                    $detaildt.= "   <td>".number_format($KPIs[$j]["KPITarget"],2)."</td>";
                    $detaildt.= "   <td>".number_format($KPIs[$j]["KPIBobot"],2)."</td>";
                    $detaildt.= "</tr>";
                    $TotalBobot += $KPIs[$j]["KPIBobot"];
                }

                $TotalBobot += $KPIs[0]["KPIBobot"];

                if($k==0) {$k=1;} //rowspan tidak boleh 0   
                
                $detailhd.="<tr style='background:".$bg.";'>";
                $detailhd.="    <td rowspan='".$k."'>".$No."</td>";
                $detailhd.="    <td rowspan='".$k."'>".$dt["NM_SLSMAN"]."<br><em> &nbsp; ".$dt["CATATAN"]."</em></td>";                
                $detailhd.="    <td rowspan='".$k."'>".$dt["BULAN"]."/".$dt["TAHUN"]."</td>";
                $detailhd.= "   <td>".$KPIs[0]["KPIName"]."</td>";
                $detailhd.= "   <td>".$KPIs[0]["KPINote"]."</td>";
                $detailhd.= "   <td>".number_format($KPIs[0]["KPITarget"],2)."</td>";
                $detailhd.= "   <td>".number_format($KPIs[0]["KPIBobot"],2)."</td>";
                $detailhd.="    <td rowspan='".$k."'>".number_format($TotalBobot, 0)."</td>";
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
                if($req["RequestStatus"]=='EXPIRED'){
                    echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>".$req["ApprovalExpiredError"]."</h2></div>";
                }else{
                    echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>REQUEST SUDAH DIPROSES!</h2></div>";
                }
            }
            $detail.= "</form>";

            echo ($style);
            echo ($header);
            echo ($detail);

        } //else {
        //     else echo "No. PrePO tidak ditemukan";
        // }
    }

    public function ApproveReject() {

        $msg = "";
        $data = $this->PopulatePost();
        // echo json_encode($data);die;
		
        //APPROVE
        if(ISSET($data['salesman'])){
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => $this->API_URL."/Targetkpisalesman/ApproveTargetKPI",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 1000,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query($data),
            ));
            
            $result = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            // echo $result;die;
                                    
            $result = json_decode($result, true);

            if ($result["result"]=="SUCCESS") {     

                $msg = "
                <div style='padding:10px;background:#d4edda;border:1px solid #c3e6cb'>
                <center><h2>REQUEST TARGET KPI BERHASIL DIAPPROVE</h2></center>
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
                    $resultEdit = $this->Targetkpisalesmanmodel->editTblApproval($wApproval,$dApproval);
					
					$getNextPriority = $this->Targetkpisalesmanmodel->getNextPriority($data);
					if($getNextPriority){
						
						$post['api'] = 'APITES';
						$post['norequestkpi'] = $getNextPriority->RequestNo;
						$post['kpi_kategori'] = $getNextPriority->AddInfo2Value;
						$post['user_name'] = $getNextPriority->RequestBy;
						$post['user_email'] = $getNextPriority->RequestByEmail;
						$post['atasan_name'] = $getNextPriority->ApprovedByName;
						$post['atasan_email'] = $getNextPriority->ApprovedByEmail;
						$post['kode_lokasi'] = $getNextPriority->AddInfo5Value;
						// echo json_encode($post);
					
						$URL = $this->API_URL."/Targetkpisalesman/sendrequest2";
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
									$resultEdit = $this->Targetkpisalesmanmodel->editTblApproval($wApproval,$dApproval);
								}
							}
						}
					}
            }
            else {
                $msg = "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><center><h2>".$result["error"]."</h2></center></div>";               
            }      
        }
        
        //REJECT
        else{
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => $this->API_URL."/Targetkpisalesman/RejectTargetKPI",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 1000,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query($data),
            ));
            
            $result = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
                                    
            $result = json_decode($result, true);
            // echo("REJECT: ".json_encode($result));
            // echo("<br>");
            // die("webAPI return :<br>".json_encode($result)."<br><br>");

            if ($result["result"]=="SUCCESS") {
                $msg = "<div style='padding:10px;background:#d4edda;border:1px solid #f5c6cb;'><h2><center>REQUEST TARGET KPI BERHASIL DIREJECT</center></h2></div>";
				$dApproval = array(
					'ApprovalStatus' => 'REJECTED',
				);
				$wApproval = array(
					'RequestNo' => $data['no_request'],
					'ApprovalStatus' => 'UNPROCESSED',
					'ApprovedByEmail' => $data['app_by']
				);
				$resultEdit = $this->Targetkpisalesmanmodel->editTblApproval($wApproval,$dApproval);
            }
            else { 
                $msg = "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2><center>".$result["error"]."</center></h2></div>";
            }
        }
        // echo($msg."<br>");
        $this->ViewRequestTargetKPI($data["no_request"], $data["app_by"], $msg);
    }

    public function simpanapproval($parammode, $params, $conn){
        //get list target salesman

        if ($parammode==''){
            $url = $conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApprovalNew/GetListTarget?".
            "parammode=".urlencode($parammode).
            "&mode=".urlencode($params["mode"]).
            "&kategori=".urlencode($params["kategori"]).
            "&userid=".urlencode($params["userid"]).
            "&tanggal=".urlencode($params["tanggal"]).
            "&svr=".urlencode($conn->Server).
            "&db=".urlencode($conn->Database).
            "&uid=".urlencode(SQL_UID).
            "&pwd=".urlencode(SQL_PWD);
        } else {
            $url = $conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApprovalNew/GetListTarget?".
            "parammode=".urlencode($parammode).
            "&norequest=".urlencode($params["norequest"]).
            "&svr=".urlencode($conn->Server).
            "&db=".urlencode($conn->Database).
            "&uid=".urlencode(SQL_UID).
            "&pwd=".urlencode(SQL_PWD);
        }

        $HD = json_decode(file_get_contents($url),true);

        for ($a=0;$a<count($HD);$a++) {
            $params = array();
            $params["ApprovalType"] = $this->approvaltype.' '.$params["kategori"];
            $params["RequestNo"] = $HD[$a]["NoRequest"];
            $params["RequestBy"] = $HD[$a]["User_Email"];
            $params["RequestDate"] = $HD[$a]["EmailedDate"];
            $params["RequestByName"] = $HD[$a]["User_Name"];
            $params["RequestByEmail"] = $HD[$a]["User_Email"];
            $params["ApprovedBy"] = $HD[$a]["UserApproved_ID"];
            $params["ApprovedByName"] = $HD[$a]["UserApproved_Name"];
            $params["ApprovedByEmail"] = $HD[$a]["UserApproved_Email"];
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
            $params["AddInfo6Value"] = $HD[$a]["monthyear"];
            $params["AddInfo7"] = "";
            $params["AddInfo7Value"] = "";
            $params["AddInfo8"] = "";
            $params["AddInfo8Value"] = "";
            $params["AddInfo9"] = "Wilayah";
            $params["AddInfo9Value"] = $HD[$a]["Wilayah"];
            $params["AddInfo10"] = "";
            $params["AddInfo10Value"] = "";
            $params["AddInfo11"] = "";
            $params["AddInfo11Value"] = "";
            $params["AddInfo12"] = "";
            $params["AddInfo12Value"] = "";
            $params["ApprovalNeeded"] = "";
            $params["Priority"] = "";
            $params["ExpiryDate"] = $HD[$a]["Tgl_Akhir"];
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
            $params["EmailedDate"] = $HD[$a]["EmailedDate"];
            $params["approvedbyfrommsconfig"] = $this->approvedbyfrommsconfig;
            $params["expirydatefrommsconfig"] = $this->expirydatefrommsconfig;
            $params["amount"] = 0;
            $params["branchid"] = $HD[$a]["kd_lokasi"];
            $x = $this->approval->insert($params);
        }

    }

    public function viewTarget(){
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
                $TEST_CONN = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApprovalNew/TestConnection"), true);
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

            $url = $conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApprovalNew/ViewDataDiPusatByNoRequest?".
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

    public function Email_Notifikasi_ByNoRequestNew(){
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
                $TEST_CONN = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApprovalNew/TestConnection"), true);
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

            $url = $conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApprovalNew/AmbilDataDiPusatByNoRequest?".
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
                echo $result["pesan"];

            } else {
                //echo "Cek Function TestConnection";
                echo "Server yang dituju sedang tidak aktif";
            }
        } else {
            echo "Alamat WEB Service Belum Disetting";
        }        
    }

	public function is_developer(){
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
}																																			