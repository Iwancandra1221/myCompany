<?php
class EmailModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('email');
	}

	public function sendEmailReport($e_recipients=array(), $e_subject='', $e_content='')
	{
		if (SEND_EMAIL=="ON")
		{
			$this->email->from("bithrd.noreply@gmail.com", "WEB HRD Auto-Email");
			if (TEST_MODE == "TRUE")
				$this->email->to(TEST_EMAIL); 
			else
				$this->email->to($e_recipients); 
			$this->email->subject($e_subject);
			$this->email->message($e_content);	

			if ($this->email->send()) 
			{
			    $this->email->clear();
			} 
			else 
			{
			    echo 'Message could not be sent.<br>';
			    $this->email->clear();
			}
		}
	}
}
