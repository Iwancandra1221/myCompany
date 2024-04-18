<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class accountemail extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('accountModel'); 
		$this->load->model('WhatsappModel');   
		$this->load->helper('FormLibrary');
	}
	
	public function email()
	{
		$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2)); 
		$this->alert = "";

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ACCOUNT EMAIL";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU ACCOUNT EMAIL ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$this->view("Email");
	} 

	public function whatsapp()
	{
		$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2)); 
		$this->alert = "";

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ACCOUNT WHATSAPP";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU ACCOUNT WHATSAPP ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$this->view("Whatsapp");
	} 

	public function view($tipe)
	{ 
		$post = $this->PopulatePost();		
		$data = array(); 
		set_time_limit(60); 
		if ($tipe=="Email")
		{
			$ListEmailAccount = $this->accountModel->GetListEmailAccount(); 
			$data["ListEmailAccount"] = $ListEmailAccount;  

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "ACCOUNT EMAIL";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." VIEW ACCOUNT EMAIL ";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

		}
		else
		{
			$ListWhatsappAccount = $this->WhatsappModel->GetListWhatsappAccount(); 

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "ACCOUNT WHATSAPP";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." VIEW ACCOUNT WHATSAPP ";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

			$data["ListWhatsappAccount"] = $ListWhatsappAccount;  
		}   
		$data["Tipe"] = $tipe;  
        $data["alert"] = $this->alert;
		$this->RenderView('accountemailview',$data);		
	}

	public function insertemail()
	{
		$mail_user = "";

       	$lanjut = true;
       	$post = $this->PopulatePost(); 
       	$priority = $this->accountModel->getEmailAccountCount();
		$mail_protocol = $post["txtprotocol"];
		$mail_host = $post['txthost'];
		$mail_port = $post['txtport'];
		$mail_user .= $post['txtuser'];
		$mail_pwd = $post['txtpass']; 
		$mail_alias = $post['txtalias'];
		$smtp_crypto = $post['txtsmtpcrypto'];
		$replyto = $post['txtreplyto'];

		//check data kosong
		$findDuplicate = $this->accountModel->findDuplicateEmail($mail_user);

		if($findDuplicate){
			$this->alert = "User Email Duplicate";
			$lanjut = false;
		} 

		if($lanjut) {
			$now = date('Y/m/d h:i:s A');
			$data = array (
	            'priority' => $priority+1,
	            'mail_protocol' => $mail_protocol,
	            'mail_host'  => $mail_host,
	            'mail_port'=> $mail_port,
	            'mail_user' => $mail_user,
	            'mail_pwd' => $mail_pwd ,
	            'mail_alias' => $mail_alias,
	            'smtp_crypto' => $smtp_crypto, 
	            'mail_replyto' => $replyto, 
				'modified_by' => $_SESSION['logged_in']['username'],
				'modified_date' => $now
	        ); 
	        $this->accountModel->addData($data);
			$this->alert = "Insert Module Berhasil";

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "ACCOUNT EMAIL";
			$params['TrxID'] = $mail_user;
			$params['Description'] = $_SESSION["logged_in"]["username"]." INSERT ACCOUNT EMAIL ";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);
	    } 
		$this->view("Email");
    }
 
	public function updateemail()
	{ 
       	$post = $this->PopulatePost();    

		$account_id = $post['edit_txtaccountid']; 
		$mail_user = $post['edit_txtuser']; 
		$priority_new = $post["edit_txtpriority"];

		$mail_protocol = $post["edit_txtprotocol"];
		$mail_host = $post['edit_txthost'];
		$mail_port = $post['edit_txtport'];
		$mail_pwd = $post['edit_txtpass']; 
		$mail_alias = $post['edit_txtalias'];
		$smtp_crypto = $post['edit_txtsmtpcrypto'];
		$replyto = $post['edit_txtreplyto'];

		$test = '';
 
    	$priority_old = $this->accountModel->GetListEmailAccountByEmail($account_id);

    	$listdata = $this->accountModel->GetListEmailAccount();
  
    	if ($priority_new>$priority_old)
    	{ 
    		foreach ($listdata as $value) {
    			if ($value->priority>$priority_old)
    			{	
    				if ($value->priority<=$priority_new)
    				{ 
						$data = array ( 'priority' => $value->priority-1); 
				        $this->accountModel->updateData($data,$value->account_id); 
    				} 
    			}
    		}   
    	}

    	if ($priority_new<$priority_old)
    	{ 
    		foreach ($listdata as $value) {
    			if ($value->priority>=$priority_new)
    			{	
    				if ($value->priority<$priority_old)
    				{
						$data = array ( 'priority' => $value->priority+1); 
				        $this->accountModel->updateData($data,$value->account_id); 
    				}
    			}
    		} 
    	} 

		$now = date('Y/m/d h:i:s A');
    	$data = array ( 'priority' => $priority_new,
							'mail_protocol' => $mail_protocol,
							'mail_host' => $mail_host,
							'mail_port' => $mail_port,
							'mail_pwd' => $mail_pwd,
							'mail_alias' => $mail_alias,
							'smtp_crypto' => $smtp_crypto,
	            			'mail_replyto' => $replyto, 
							'modified_by' => $_SESSION['logged_in']['username'],
							'modified_date' => $now
						); 
    	$this->accountModel->updateData($data,$account_id);
    	$this->alert = "Update Berhasil";  

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ACCOUNT EMAIL";
		$params['TrxID'] = $mail_user;
		$params['Description'] = $_SESSION["logged_in"]["username"]." UPDATE ACCOUNT EMAIL ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$this->view("Email");
    }
 
	public function deleteemail()
	{  
		set_time_limit(60);  
		$post = $this->PopulatePost();	
		$account_id = $this->input->post('account_id');
		$avaiablestatus = $this->accountModel->getEmailAccountCount();
		if ($avaiablestatus>0) {
			$this->accountModel->deleteData($account_id);

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "ACCOUNT EMAIL";
			$params['TrxID'] = date("YmdHis");;
			$params['Description'] = $_SESSION["logged_in"]["username"]." DELETE ACCOUNT EMAIL ";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

			echo "1"; 
		}
	}

	public function tesEmail()
	{ 
		$post = $this->PopulatePost();	
		$email = $post['email'];
    	$result = $this->accountModel->SendEmail($email, $email, "Test Kirim Email", "<b>Hello</b>"); 

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ACCOUNT EMAIL";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." INSERT ACCOUNT EMAIL ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		echo json_encode($result);
	}

	public function tesWA()
	{  
		$post = $this->PopulatePost();	
		$nowa = $post['nowa'];
			$data = array();
			$data["phone"] = $nowa;
			$data["paramType1"] = "Tes Kirim WA";
			$data["param1"] = "Tes Kirim WA"; 
			$data = json_encode($data); 
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => site_url()."api/waba/sendMessage?src=",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $data,
				CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "WHATSAPP";
			$params['TrxID'] = $nowa;
			$params['Description'] = $_SESSION["logged_in"]["username"]." TEST WHATSAPP ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			   
			$result = json_decode($response); 
			if ($result->sent==true) {
				$res = array("result"=>"SUCCESS");

				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

			} else { 
				$res = array("result"=>"FAILED");	

				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

			}
			echo json_encode($res);
	}


	public function insertwhatsapp()
	{
       	$lanjut = true;
       	$post = $this->PopulatePost();  
		$mInstance = $post["txtInstance"];
		$mUrl = $post['txtUrl'];
		$mToken = $post['txtToken'];
		$mVendor = $post['txtVendor'];
		$mNote = $post['txtNote']; 
		$mDaily = $post['txtDaily']; 

		if(isset($post['chkAktif'])){
			$is_active = $post['chkAktif'];
		}
		else{
			$is_active = 0;
		}

		//check data kosong
		$findDuplicate = $this->WhatsappModel->findDuplicate($mInstance);

		if($findDuplicate){
			$this->alert = "Account Whatsapp Duplicate";
			$lanjut = false;
		} 

		if($lanjut) {
			$now = date('Y/m/d h:i:s A');
			$data = array (
	            'apiInstance' => $mInstance,
	            'apiUrl' => $mUrl,
	            'apiToken'  => $mToken,
	            'apiVendor'=> $mVendor,
	            'apiNote' => $mNote,
	            'isActive' => $is_active, 
	            'dailyQuota' => $mDaily ,
				'createdBy' => $_SESSION['logged_in']['username'],
				'createdDate' => $now,
				'modifiedBy' => $_SESSION['logged_in']['username'],
				'modifiedDate' => $now
	        ); 
	        $this->WhatsappModel->addData($data);
			$this->alert = "Insert Account Whatsapp Berhasil";

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "ACCOUNT WHATSAPP";
			$params['TrxID'] = $mInstance;
			$params['Description'] = $_SESSION["logged_in"]["username"]." INSERT ACCOUNT WHATSAPP ";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

	    } 
		$this->view("Wahtsapp");
    }


    public function updatewhatsapp()
	{ 
       	$post = $this->PopulatePost();    

		$mInstance = $post["edit_txtInstance"];
		$mUrl = $post['edit_txtUrl'];
		$mToken = $post['edit_txtToken'];
		$mVendor = $post['edit_txtVendor'];
		$mNote = $post['edit_txtNote']; 
		$mDaily = $post['edit_txtDaily']; 

		if(isset($post['edit_chkAktif'])){
			$is_active = $post['edit_chkAktif'];
		}
		else{
			$is_active = 0;
		}

			$now = date('Y/m/d h:i:s A');
		$data = array ( 
	            'apiUrl' => $mUrl,
	            'apiToken'  => $mToken,
	            'apiVendor'=> $mVendor,
	            'apiNote' => $mNote,
	            'isActive' => $is_active, 
	            'dailyQuota' => $mDaily , 
				'modifiedBy' => $_SESSION['logged_in']['username'],
				'modifiedDate' => $now
	        );  
    	$this->WhatsappModel->updateData($data,$mInstance);

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "ACCOUNT WHATSAPP";
		$params['TrxID'] = $mInstance;
		$params['Description'] = $_SESSION["logged_in"]["username"]." UPDATE ACCOUNT WHATSAPP ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

    	$this->alert = "Update Berhasil";  
		$this->view("Wahtsapp");
    } 

	public function deletewhatsapp()
	{ 
		set_time_limit(60);  
		$post = $this->PopulatePost();	
		$mInstance = $this->input->post('apiInstance');
		$avaiablestatus = $this->WhatsappModel->getAccountCount();
		if ($avaiablestatus>0) {
			$this->WhatsappModel->deleteData($mInstance);

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "ACCOUNT WHATSAPP";
			$params['TrxID'] = $mInstance;
			$params['Description'] = $_SESSION["logged_in"]["username"]." DELETE ACCOUNT WHATSAPP ";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

			echo "1"; 
		}
	} 
}