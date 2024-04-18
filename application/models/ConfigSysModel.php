<?php
	class ConfigSysModel extends CI_Model
	{
		function __construct()
		{
			parent::__construct();
		}

		function Get()
		{
			$res = $this->db->query("Select * From tb_config_sys");
		
			if($res->num_rows() > 0)
				return $res->row();
			else
				return null;
		}

		function GetCompanyName()
		{
			$res = $this->db->query("Select company_name From tb_config_sys");
			if($res->num_rows() > 0)
				return $res->row()->company_name;
			else
				return "";
		}

		function update($post)
		{
			$this->db->trans_start();
			$this->db->set('company_name',$post['company_name']);
			$this->db->set('mail_protocol',$post['mail_protocol']);
			$this->db->set('mail_host',$post['smtp_host']);
			$this->db->set('mail_port',$post['smtp_port']);
			$this->db->set('mail_user',$post['smtp_user']);
			if(isset($post['smtp_pwd']))
				$this->db->set('mail_pwd',$post['smtp_pwd']);
			$this->db->set('mail_alias',$post['alias']);
			$this->db->set('hrd_email',$post['email_hrd']);
			if(isset($post['notification_new']))
				$this->db->set('request_new_notification',1);
			else
				$this->db->set('request_new_notification',0);
			if(isset($post['notification_wait']))
				$this->db->set('request_wait_notification',1);
			else
				$this->db->set('request_wait_notification',0);
			if(isset($post['notification_approved']))
				$this->db->set('request_approved_notification',1);
			else
				$this->db->set('request_approved_notification',0);
			if(isset($post['notification_rejected']))
				$this->db->set('request_rejected_notification',1);
			else
				$this->db->set('request_rejected_notification',0);
			if(isset($post['notification_cancelled']))
				$this->db->set('request_cancelled_notification',1);
			else
				$this->db->set('request_cancelled_notification',0);

			$this->db->set('bugsnag_environment',$post['bugsnag_environment']);
			$this->db->set('bktapi_appname',$post['bktapi_appname']);
			$this->db->set('webapi_url',$post['webapi_url']);
			$this->db->set('messageapi_url',$post['messageapi_url']);
			$this->db->set('zenhrs_url',$post['zenhrs_url']);
			$this->db->set('bktapi_ho_url',$post['bktapi_ho_url']);
			$this->db->set('mishirin_url',$post['mishirin_url']);
			if(isset($post['mishirin_key']))
				$this->db->set('mishirin_key',$post['mishirin_key']);
			$this->db->set('webapi_java_url',$post['webapi_java_url']);
			
			$this->db->Update('tb_config_sys');
			$this->db->trans_complete();
		}
		
		
		function ConfigSysUpdate($post)
		{
			$this->db->trans_start();
			$this->db->where('1=1');
			$this->db->set('company_name',$post['company_name']);
			$this->db->set('mail_protocol',$post['mail_protocol']);
			$this->db->set('mail_host',$post['mail_host']);
			$this->db->set('mail_port',$post['mail_port']);
			$this->db->set('mail_user',$post['mail_user']);
			if(isset($post['mail_pwd']))
				$this->db->set('mail_pwd',$post['mail_pwd']);
			$this->db->set('mail_alias',$post['mail_alias']);
			$this->db->set('smtp_crypto',$post['smtp_crypto']);
			$this->db->set('modified_by',$_SESSION['logged_in']['username']);
			$this->db->set('modified_date',date('Y-m-d H:i:s'));

			$this->db->set('bugsnag_environment',$post['bugsnag_environment']);
			$this->db->set('bktapi_appname',$post['bktapi_appname']);
			$this->db->set('webapi_url',$post['webapi_url']);
			$this->db->set('messageapi_url',$post['messageapi_url']);
			$this->db->set('zenhrs_url',$post['zenhrs_url']);
			$this->db->set('bktapi_ho_url',$post['bktapi_ho_url']);
			$this->db->set('mishirin_url',$post['mishirin_url']);
			if(isset($post['mishirin_key']))
				$this->db->set('mishirin_key',$post['mishirin_key']);
			$this->db->set('webapi_java_url',$post['webapi_java_url']);
			
			$this->db->Update('tb_config_sys');
			
			if( ($errors = sqlsrv_errors() ) != null) {
				return 'failed';
			}
			
			$this->db->trans_complete();
			return 'success';
		}

	}
?>