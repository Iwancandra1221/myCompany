<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserPicker extends MY_Controller {

    function __construct()
    {
        parent::__construct();
    }


	public function index($userid="")
	{
        $temp = array();
        $this->RenderView('UserPickerView',$temp);
	}

    public function GetUsers()
    {
        $post = $this->PopulatePost();
        $branch = ((isset($post['Branch']))? $post["Branch"]:"");
        $nama   = ((isset($post['Nama']))? $post["Nama"]:"");
        $users = $this->UserModel->searchUserByBranch($branch, $nama);

        if($users != null)
            echo json_encode($users);
        else
            echo json_encode(array('error'=>'Invalid Request'));
    }
}