<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Announcement extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model("AnnouncementModel");
	}

	function index(){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');
		if($_SESSION['can_read']==true){
			$data['Announcement'] 	= $this->AnnouncementModel->Get();
			$data['mode']			= 'views';

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "BRANCH";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU ANNOUNCEMENT ";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

			$this->RenderView('AnnouncementView',$data);
		}else{
			redirect('dashboard');
		}
	}

	function add(){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');
		if($_SESSION['can_create']==true){

			$data =array();
			$data['error']	= '';
			$data['data']	= '';

			
			$file = '';
			$post = $this->PopulatePost();
			if(isset($post['proses'])){

				$count = count($_FILES['attachment']['name']);
				if($count<=3){

					$data = $post;
					$file = $_FILES;
					$number = $this->AnnouncementModel->insert($data,$file);

					// ActivityLog
					$params = array();   
					$params['LogDate'] = date("Y-m-d H:i:s");
					$params['UserID'] = $_SESSION["logged_in"]["userid"];
					$params['UserName'] = $_SESSION["logged_in"]["username"];
					$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
					$params['Module'] = "BRANCH";
					$params['TrxID'] = date("YmdHis");
					$params['Description'] = $_SESSION["logged_in"]["username"]." ADD ANNOUNCEMENT ";
					$params['Remarks']="SUCCESS";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->insert_activity($params);

					redirect('Announcement/view/'.$number);

				}else{

					$data['error'] = 'error';

				}
			}

			$data['data']	= $post;
			$data['mode']	= 'add';
						
			$this->RenderView('AnnouncementView',$data);

		}else{
			redirect('dashboard');
		}
	}

	function view($id=''){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');
		if($_SESSION['can_read']==true && !empty($id)){

			$data['error']	= '';
			$data['data']	= $this->AnnouncementModel->Get_Detail($id);
			$data['mode']	= 'view';

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "BRANCH";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." VIEW ANNOUNCEMENT ";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

			$this->RenderView('AnnouncementView',$data);

		}else{
			redirect('dashboard');
		}
	}

	function edit($id=''){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');
		if($_SESSION['can_update']==true){
			$data =array();
			$data['error']	= '';
			$data['data']	= '';

			
			$file = '';
			$post = $this->PopulatePost();
			if(isset($post['proses'])){

				$count = count($_FILES['attachment']['name']);
				if($count<=3){

					$data = $post;
					$file = $_FILES;
					$number = $this->AnnouncementModel->update($data,$file);

					// ActivityLog
					$params = array();   
					$params['LogDate'] = date("Y-m-d H:i:s");
					$params['UserID'] = $_SESSION["logged_in"]["userid"];
					$params['UserName'] = $_SESSION["logged_in"]["username"];
					$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
					$params['Module'] = "BRANCH";
					$params['TrxID'] = date("YmdHis");
					$params['Description'] = $_SESSION["logged_in"]["username"]." UPDATE ANNOUNCEMENT ";
					$params['Remarks']="SUCCESS";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->insert_activity($params);

					redirect('Announcement/view/'.$number);

				}else{

					$data['error'] = 'error';

				}
			}

			$data['data']	= $this->AnnouncementModel->Get_Detail($id);
			$data['mode']	= 'edit';
			$this->RenderView('AnnouncementView',$data);

		}else{
			redirect('dashboard');
		}
	}

	function proses(){

		$post = $this->PopulatePost();
		if(!empty($post)){
			if($post['c']=='delete_img'){
				$this->AnnouncementModel->delete_img($post['a'],$post['b']);
			}else if($post['c']=='delete_announcement'){
				$this->AnnouncementModel->delete_announcement($post['a']);
			}

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "BRANCH";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." DELETE ANNOUNCEMENT ";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

		}

	}

}