<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class ResetPassword extends CI_Controller {
 
 function __construct()
 {
		 parent::__construct();
		 $this->load->model('UserModel','user',TRUE);
		 $this->load->model("EmailConfigModel","emailconfig");
		 $this->load->model("accountmodel");
 }
 
 function index()
 {
		$resetpassemail = $this->input->post('txtResetPassEmail');
		$this->load->library('email', $this->emailconfig->config());
		//die(json_encode($this->emailconfig->config()));
		// check user email
		// $check = $this->user->getUserDataByEmail($resetpassemail);

		// if($check == ""){
		//   $this->session->set_flashdata('error','Email is not registered in the user database. Please re-check your email address.');
		//   redirect('MainController');
		// }
		//else{
			// generate new email
		$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$string = '';
		for ($i = 0; $i < 8; $i++) {
				$string .= $characters[rand(0, strlen($characters) - 1)];
		}
		
		$temp = null;
		if(is_numeric($resetpassemail)){
			
			$temp = $this->user->getUserDataByUserId($resetpassemail);
			if($temp!=null){
				$this->user->resetPasswordByUserId($resetpassemail,$string);
			}
		}
		else{
			//get email address to send random generated password
			$temp = $this->user->getUserDataByEmail($resetpassemail);
			if($temp!=null){
				$this->user->resetPassword($resetpassemail,$string);
			}
		}

		if($temp!=null){


			if(!is_null($temp->Email) and $temp->Email != ''){
				$toemailaddress = $temp->Email;
			}
			else{
				$toemailaddress = $resetpassemail;
			}
			// die($toemailaddress);
			$message = "Dear User, your new password is <strong>".$string."</strong>";
			$message .= "<br/>After login, you can change your password.";
			// $this->email->set_newline("\r\n");
			// $this->email->from('bhaktiautoemail.noreply@bhakti.co.id');
			// $this->email->to($toemailaddress);
			// $this->email->subject('Bhakti.co.id Reset Password');
			// $this->email->message($message);

			$to = $toemailaddress;
			$cc = '';
			$subject = 'Bhakti.co.id Reset Password';

			$resultSendmail = $this->accountmodel->SendEmail($to, $cc, $subject, $message);

			// echo '<pre>';
			// print_r($resultSendmail);
			// echo '</pre>';

			// echo '<pre>';
			// print_r($temp);
			// echo '</pre>';
			// die();

			if($resultSendmail!=null && $resultSendmail['result']=='SUCCESS'){
				$this->session->set_flashdata('info','Your new password has been sent to '.$toemailaddress.'. Please check your email inbox and re-login with your new password.');
				redirect('HomeController');
			}
			else{
				show_error($this->email->print_debugger());
			}
		}
		else{
			$this->session->set_flashdata('error','Email is not registered in the employee database. Please re-check your email address.');
			redirect('HomeController');
		}
			
		//}
 }

	function setnewpass(){
		$this->load->view('setnewpass');
	}
	
	function postnewpass(){
		$email = $this->input->post('useremail');
		$newpass = $this->input->post('password');
		$renewpass = $this->input->post('repassword');
		if($newpass == $renewpass){
			if(strlen($newpass) < 6){
				$this->session->set_flashdata('error','Password must be minimum 6 length characters.');
				redirect('resetpassword/setnewpass');
			}
			else{
				$this->user->setNewPass($email,$newpass);
				//$this->session->destroy();
				session_destroy();
				$this->session->set_flashdata('info','Your new password has been changed successfully. Please re-login using your new password.');
				redirect('HomeController');
			}

		}
		else{
			$this->session->set_flashdata('error','Password and re-type password do not match.');
			redirect('resetpassword/setnewpass');
		}
	}

}
?>