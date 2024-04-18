<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Graceperiodjt extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('GraceperiodjthModel');
	}
	
	public function index(){
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="GRAJE PERIOD JT"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU GRACE PERIOD JT";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');
		if($_SESSION['can_read']==true){
			$data['list'] = $this->GraceperiodjthModel->GetList();
			$data['module'] = 'list';
			$this->RenderView('GraceperiodjtView',$data);
		}

		$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
	}

	public function add(){
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="GRAJE PERIOD JT"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU GRACE PERIOD JT - ADD";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$this->load->model('AutoNumberModel');
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');
		if($_SESSION['can_create']==true){
			if(!empty($_POST)){
				$data['approval'] = $this->GraceperiodjthModel->approval(urldecode($this->input->post("wilayah")));
				$data['number'] = $this->AutoNumberModel->getAutoNumber_x(date_format(date_create($this->input->post("jtl")[0]),'Y'));
				$data['wilayah'] = urldecode($this->input->post("wilayah"));
				$data['divisi'] = urldecode($this->input->post("divisi"));
				$data['pelanggan'] = urldecode($this->input->post("pelanggan"));
				$data['catatan'] = urldecode($this->input->post("catatan"));
				$data['jtl'] = $this->input->post("jtl");
				$data['jtb'] = $this->input->post("jtb");

				$status['status'] = $this->GraceperiodjthModel->add($data);
				$status['number'] = str_replace("=", "", base64_encode($data['number']));
				if($status['status']=='success'){

					$message = '<table>';
					$message .='<tr><td width="100px">Number</td><td width="1">:</td><td><b>'.$data['number'].'</b></td></tr>';
					$message .='<tr><td>Wilayah</td><td width="1">:</td><td><b>'.$data['wilayah'].'</b></td></tr>';
					$message .='<tr><td>Divisi</td><td width="1">:</td><td><b>'.$data['divisi'].'</b></td></tr>';
					$message .='<tr><td>Pelanggan</td><td width="1">:</td><td><b>'.$data['pelanggan'].'</b></td></tr>';
					$message .='<tr><td>Catatan</td><td width="1">:</td><td><b>'.$data['catatan'].'</b></td></tr>';
					$message .='<tr><td>Request By</td><td width="1">:</td><td><b>'.$_SESSION["logged_in"]["username"].'</b></td></tr>';
					$message .= '</table>';
					$subject = "GRACE PERIOD JT ". $data['number'];

					for($i=0; $i<count($data['approval']); $i++){
						$this->SendEmail($data['approval'][$i]->email_address,$_SESSION["logged_in"]["useremail"],$subject, $message);
					}

					$paramsLog['Remarks']="SUCCESS";
					$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($paramsLog);

					print_r(json_encode($status));

				}else{
					$paramsLog['Remarks']="FAILED - TAMBAH DATA GAGAL";
					$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($paramsLog);

					print_r(json_encode($status));
				}
				die();
				
			}
			$data['wilayah'] = $this->GraceperiodjthModel->GetWilayah();
			$data['divisi'] = $this->GraceperiodjthModel->GetDivisi();
			$data['module'] = 'add';
			$this->RenderView('GraceperiodjtView',$data);
		}
	}	

	public function view($number){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');
		if($_SESSION['can_read']==true){
			$data['cekapproved'] = $this->GraceperiodjthModel->cekapproved($number);
			$data['detail'] = $this->GraceperiodjthModel->GetData($number);
			$data['wilayah'] = $this->GraceperiodjthModel->GetWilayah();
			$data['divisi'] = $this->GraceperiodjthModel->GetDivisi();
			$data['module'] = 'view';
			$this->RenderView('GraceperiodjtView',$data);
		}
	}

	public function GetPelanggan(){
		if(!empty($this->input->post("wilayah"))){
			$wilayah = urldecode($this->input->post("wilayah"));
		}else{
			$wilayah = 'ALL';
		}
		$wilayah = $this->GraceperiodjthModel->GetPelanggan($wilayah);
		print_r(json_encode($wilayah));
		
	}

	public function DeleteData(){
		if(!empty($this->input->post('number')) && $_SESSION["can_delete"]==true){
			$paramsLog = array();   
			$paramsLog['LogDate'] = date("Y-m-d H:i:s");
			$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
			$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
			$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$paramsLog['Module']="GRAJE PERIOD JT"; 
			$paramsLog['TrxID'] = date("YmdHis");
			$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU GRACE PERIOD JT - DELETE DATA";
			$paramsLog['Remarks']="";
			$paramsLog['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($paramsLog);


			$data['number'] = $this->input->post('number');
			$data['note'] = $this->input->post('note');
			$this->GraceperiodjthModel->DeleteData($data);
			$result['hasil']='success';

			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

		}else{
			$result['hasil']='error';

			$paramsLog['Remarks']="FAILED - DELETE DATA GAGAL";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
		}

		print_r(json_encode($result));
	}

	public function Approved(){
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="GRAJE PERIOD JT"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU GRACE PERIOD JT - APPROVED";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		if(!empty($this->input->post('number'))){
			

			$data['number'] = $this->input->post('number');
			$data['status'] = $this->input->post('status');
			$this->GraceperiodjthModel->approved($data);

			$message = '<table>';
			$message .='<tr><td width="100px">Number</td><td width="1">:</td><td><b>'.$data['number'].'</b></td></tr>';
			$message .='<tr><td width="100px">Status</td><td width="1">:</td><td><b>'.$data['status'].'</b></td></tr>';
			$message .= '</table>';
			$subject = "GRACE PERIOD JT ". $data['number']." - ".$data['status'];

			$this->SendEmail($_SESSION["logged_in"]["useremail"],'',$subject, $message);

			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

		}
		else{
			$paramsLog['Remarks']="FAILED - APPROVED GAGAL";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
		}
	}

	public function SendEmail($to,$cc,$title,$message)
	{	
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="GRAJE PERIOD JT"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU GRACE PERIOD JT - SEND EMAIL";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$this->load->model('accountModel');
		$this->accountModel->SendEmails($to, $cc, $title, $message); 

		$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);

	}

	public function Sync(){
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="GRAJE PERIOD JT"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU GRACE PERIOD JT - SYNC";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$number = $this->input->post('number');
		echo $this->GraceperiodjthModel->sync($number);

		$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
	}
}
?>