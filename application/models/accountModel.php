<?php
	class accountModel extends CI_Model
	{
		public $user = "bhaktiautoemail.noreply@bhakti.co.id";
		
		function __construct()
		{
			parent::__construct();
			$this->load->model('EmailHashesModel');
		}

		/* WHATSAPP WHATSAPP */
		function GetList()
		{
			$str = "SELECT * FROM ms_account_whatsapp_api";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return $res->result();
			} else {
				return array();
			}
		}

		function GetActiveWhatsappAPI($src="") 
		{
			$str = "SELECT * 
					FROM ms_account_whatsapp_group g  
					WHERE g.whatsappGroup='".$src."' and g.isActive=1";
			$res = $this->db->query($str);
			if ($res->num_rows()==0) {
				$src = "OTHER";
			}

			$str = "SELECT g.[id] as whatsappGroupId, g.whatsappGroup, a.* 
					FROM ms_account_whatsapp_group g inner join ms_account_whatsapp_api a on g.apiInstanceId=a.id 
					WHERE g.whatsappGroup='".$src."' 
					and a.isActive=1 and g.isActive=1 ";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return $res->result();
			} else {
				return array();
			}			
		}
		/* WHATSAPP WHATSAPP */

		public function checkemail(){
			return 'SENDGRID';
		}

		public function EmailAccount($account=0)
		{
			
			$str = "SELECT * FROM ms_account_email ";
			// if ($account!=0) {
			// 	$str.=" WHERE account_id=".$account."";
			// }
			$str.= " ORDER BY priority ASC ";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return $res->result();
			} else {
				return array();
			}	
			
		}
		
		function WriteLogEmail($params, $isSuccess=1, $responseText="")
		{
			$LogDate = date("Y-m-d H:i:s");
			$str = "";

			if ($params['LogId']==0) {
				$str = "INSERT INTO Log_Email
						(LogDate, BranchId, GatewayUrl, Params, ParamTo, ParamCc, ParamSubject, ParamBody,
						IsSent, SentDate, RetryCount, UrlResponse, EmailSender)
					  SELECT '".date("Y-m-d H:i:s", strtotime($LogDate))."', '".$params["Branch"]."', '".$params["Url"]."', 
						'', '".json_encode($params["To"])."', '".json_encode($params["Cc"])."', '".($params["Subject"])."', 
						'".str_replace("'","''",$params["Body"])."', $isSuccess, (case when $isSuccess=1 then GETDATE() else null end),  
						0,  '".json_encode($responseText)."', '".$params["Sender"]."'";

			} else {
				if ($isSuccess==1) {
				  $str = "UPDATE Log_Email
					SET IsSent=1, SentDate=GETDATE(), RetryCount=ISNULL(RetryCount,0)+1,
					  UrlResponse = '".json_encode($responseText)."', GatewayUrl = '".$params["Url"]."',
					  BranchId = '".$params["Branch"]."', EmailSender = '".$params["Sender"]."', 
					  Params = '', ParamTo = '".json_encode($params["To"])."', ParamCc = '".json_encode($params["Cc"])."',
					  ParamSubject= '".($params["Subject"])."', ParamBody='".str_replace("'","''",$params["Body"])."'  
					WHERE LogId = ".$params["LogId"];
				} else {
				  $str = "UPDATE Log_Email
					SET IsSent=0, RetryCount=ISNULL(RetryCount,0)+1,
					  UrlResponse = '".json_encode($responseText)."', GatewayUrl = '".$params["Url"]."',
					  BranchId = '".$params["Branch"]."', EmailSender = '".$params["Sender"]."', 
					  Params = '', ParamTo = '".json_encode($params["To"])."', ParamCc = '".json_encode($params["Cc"])."',
					  ParamSubject= '".($params["Subject"])."', ParamBody='".str_replace("'","''",$params["Body"])."'  
					WHERE LogId = ".$params["LogId"];
				}
			}
			// die($str);
			$this->db->query($str);
			// die("here");
			// return "";
		}

		public function SendEmail($to, $cc, $subject, $body, $branch="MC", $logId=0, $from="", $bcc="", $re=0)
		{
			
			set_time_limit(60);
			$this->load->library('email');
			$account = 1;
			$settings = $this->EmailAccount($account);
			$result = "";
			// die(json_encode($setting));
			foreach($settings as $setting) {
				if ($result!="SUCCESS") {
					$res = $this->Send($to, $cc, $subject, $body, $branch, $setting, $logId, $from, $bcc,'','', $re);

					// die(json_encode($res));
					$result = $res["result"];
					$params = array();
					$params["Url"] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
					$params["To"] = $to;
					$params["Cc"] = $cc;
					$params["Subject"] = $subject;
					$params["Body"] = $body;
					$params["Branch"] = $branch;
					$params["LogId"] = $logId;
					$params["Sender"] = $setting->mail_user;
				}
			}
			
			$this->WriteLogEmail($params, (($res["result"]=="SUCCESS")?1:0), json_encode($res));
			return ($res);
		}

		public function Send($to, $cc, $subject, $message, $branch="TES", $setting, $id=0, $from="", $bcc="", $name_attachment="", $location_attachment="", $re=0)
		{		

			$result = "FAILED";
			$err = "";
			$this->load->library('email');

			$alias = $setting->mail_alias;
			if ($from != "") $alias = $from;

			$replyto = (($setting->mail_replyto == null)?"":$setting->mail_replyto);

			$user = $setting->mail_user;
			$smtp['protocol'] = $setting->mail_protocol;
			$smtp['smtp_host'] = $setting->mail_host;
			$smtp['smtp_port'] = $setting->mail_port;
			$smtp['smtp_user'] = $setting->mail_user;
			$smtp['smtp_pass'] = $setting->mail_pwd;
			
			if ($setting->smtp_crypto=="tls") {
				$smtp['smtp_crypto'] = $setting->smtp_crypto; 
			}
			$smtp['mailtype'] = 'html';
			$smtp['charset'] = 'iso-8859-1';
			$smtp['wordwrap'] = TRUE;
			$smtp['newline'] = "\r\n";
			$smtp['validate'] = FALSE;

			$ci = get_instance();			

			$ci->email->initialize($smtp);
			$ci->email->clear(true);
			if ($replyto != "") {
				$ci->email->from($user, $alias, $replyto);
			} else {
				$ci->email->from($user, $alias);
			}
			$ci->email->to($to);
			$ci->email->cc($cc);
			$ci->email->bcc($bcc);
			$ci->email->subject($subject);
			$ci->email->message($message);


			// if(count($name_attachment)>0 && !empty($location_attachment)){
			// 	for ($i=0; $i < count($name_attachment); $i++) { 
			// 		move_uploaded_file($location_attachment[$i], 'upload/attachment/'.$name_attachment[$i]);
			// 	}
			// }

			// if(count($name_attachment)>0){
			// 	for ($i=0; $i < count($name_attachment); $i++) { 
			// 		$this->email->attach('upload/attachment/'.$name_attachment[$i]);
			// 	}
			// }
			$saveHashMail = '';
			
			$dataMail = print_r($to,true).print_r($cc,true).print_r($bcc,true).print_r($subject,true).print_r($message,true);
        	$hashMail = hash('sha256', strtolower(trim($dataMail)));
        	// log_message('error',"\nto ".print_r($to,true)."\ncc ".print_r($cc,true)."\nbcc ".print_r($bcc,true)."\nsubject ".print_r($subject,true)."\nmessage ".print_r($message,true));
        	// log_message('error','hashMail '.$hashMail);
			$whereHashes = array(
	        	'hash' => $hashMail,
	        );
	        $getHashes = $this->EmailHashesModel->getHashes($whereHashes);
	        if($getHashes==null OR $re=='1'){

				if ($ci->email->send(FALSE)) {
					$dataHashes = array(
			        	'hash' => $hashMail,
			        	'created_date' => date('Y-m-d H:i:s'),
			        );

			        if($getHashes==null){
			        	$resultSimpan = $this->EmailHashesModel->addHashes($dataHashes);
			        }

					$result = "SUCCESS";

				}else{
					$err = $ci->email->print_debugger();
					$ci->email->clear(true);
				}
			}
			else{
				//log_message('error','email sudah pernah dikirim '.print_r($getHashes,true));
			}

			// if(count($name_attachment)>0){
			// 	for ($i=0; $i < count($name_attachment); $i++) { 
			// 		unlink('upload/attachment/'.$name_attachment[$i]);
			// 	}
			// }

			return array("result"=>$result, "message"=>$err, "user"=>$user);
		}

		function deleteData($account_id){
   			$this->db->where('account_id', $account_id);
  			$this->db->delete('ms_account_email');
		}

		function addData($data){
	   		$this->db->insert('ms_account_email', $data);
		}

		function updateData($data,$account_id){
			$this->db->where('account_id', $account_id);
	   		$this->db->update('ms_account_email', $data);
		}

		function getEmailAccountCount()
		{
			$str = "SELECT * FROM ms_account_email";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return $res->num_rows();
			} else {
				return 0;
			}
		}

		function GetListEmailAccountByEmail($account_id)
		{
			$str = "SELECT priority FROM ms_account_email where account_id = '".$account_id."' ";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return $res->row()->priority;
			} else {
				return 0;
			}
		}

		function GetListEmailAccount()
		{
			$str = " SELECT * from ms_account_email order by priority";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return $res->result();
			} else {
				return array();
			}
		}

		function findDuplicateEmail($email)
		{
			$str = "SELECT * FROM ms_account_email where mail_user = '".$email."' ";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return true;
			} else {
				return false;
			}
		}


		function SendEmails($to, $cc, $subject, $message, $account=1, $branch="TES", $id=0){
			$result = "FAILED";
			$err = "";
			set_time_limit(60);
			error_reporting(0);
			$this->load->library('email');

			$this->SendEmail($to, $cc, $subject, $message); 
				
			
		}

	}
?>
