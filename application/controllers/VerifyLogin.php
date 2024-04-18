<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class VerifyLogin extends CI_Controller {
 
 function __construct()
 {
   parent::__construct();
   $this->load->model('UserModel');
 }
 
 function index()
 {
   $username = $this->input->post('username');
   $password = $this->input->post('password');
   //query the database
   $result = $this->UserModel->login($username, $password);
 
   if($result)
   {
     $sess_array = array();
     foreach($result as $row)
     {
       $sess_array = array(
         'username' => $row->UserName,
         'useremail' => $row->UserEmail,
         'branch_id' => $row->branch_id
       );
       $this->session->set_userdata('logged_in', $sess_array);
     }
     redirect('Home','refresh');
   }
   else
   {
     $this->session->set_flashdata('error','Wrong email and password combination.');
     // $this->load->helper('form');
     redirect('Main');
   }
 }
}
?>