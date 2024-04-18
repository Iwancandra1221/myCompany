<?php
	Class EmailConfigModel extends CI_Model
	{
		 function config()
		 {
		    $email_config = array(
	          'protocol' => 'smtp',
			  'smtp_host' => 'mail.bhakti.co.id',
			  'smtp_port' => 587,
			  'smtp_user' => 'bhaktiautoemail.noreply@bhakti.co.id',
			  'smtp_pass' => 'Bhakti2020',
			  'smtp_crypto' => 'tls',
			  'mailtype' => 'html',
			  'charset' => 'iso-8859-1',
			  'wordwrap' => TRUE
	        );
	        return $email_config;
		}
	}
?>