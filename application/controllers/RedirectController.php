<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Redirect extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{

		$target = $_GET['target'];
		// 1 -> Credit Limit
		// 2 -> User Request
		// 3 -> Web HRD

		exit(1);
		if($target == 1){
			$goto = "http://".$_SERVER['HTTP_HOST']."/home";
			$_SESSION['flagL'] = 1;
			redirect($goto);
		}
		else if($target == 2){
			// $this->session->set_userdata('user',$_SESSION['logged_in']['useremail']);
			// $this->session->set_userdata('username',$_SESSION['logged_in']['username']);
			$user = $_SESSION['logged_in']['useremail'];
			$name = $_SESSION['logged_in']['username'];
			$goto = "http://".$_SERVER['HTTP_HOST']."/UserRequest/home";

			$attributes = array('id' => 'myForm');
			echo form_open($goto, $attributes); 
			echo '<input type="hidden" name="useremail" value="'.$user.'">';
			echo '<input type="hidden" name="username" value="'.$name.'">';
			echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'">';
			echo form_close();
			echo "<script>document.getElementById('myForm').submit();</script>";
		}
		else if($target == 3){
			echo "<script>window.close();</script>"; // close current tab
		}
	}
}