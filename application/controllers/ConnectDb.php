<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ConnectDB extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model("MasterDbModel");
	}

	public function index()
	{
		$data = array();
       	$temp = $this->UserModel->GetRoleByEmail($_SESSION['logged_in']['useremail']);

       	$role = array();
       	for($i=0;$i<count($temp);$i++){
       		array_push($role, $temp[$i]->role_id);
       	}

       	$_SESSION['role'] = $role;

		$mod = $this->ModuleModel->getListByRole($_SESSION['role']);
       	$_SESSION['module'] = $mod;

		$data["db"] = $this->MasterDbModel->getListByDatabaseType("OFFICE','SERVICE");
		$data["title"] = "";
   		$this->RenderView('ConnectDbView', $data);
	}

	public function logout()
	{
		if(isset($_SESSION['flagL'])){
			session_unset($_SESSION['logged_in']);
			session_destroy();

			echo 	'<script>
			    			window.opener.location = "http://'.$_SERVER['SERVER_NAME'].'/login";
			    			window.close();
					</script>';
		}
		else{
			session_unset($_SESSION['logged_in']);
			session_destroy();
			redirect('Main','refresh');
		}
	}

	public function changeDatabase(){
		unset($_SESSION['conn']);
		redirect("Home");
	}


	public function connect()
	{
		if(isset($_SESSION['conn'])){
			unset($_SESSION['conn']);
		}
		if(isset($_POST['selDb'])){	
			$db_id = $this->input->post('selDb');
		}
		if(isset($_POST["chkDefaultDb"])) {
			if ($this->UserModel->UpdateDefaultDb($_SESSION["logged_in"]["useremail"], $db_id)) {
				//die("here");
				if (isset($_SESSION["databaseID"])) {
					unset($_SESSION["databaseID"]);				
				}				
				$_SESSION["databaseID"] = $db_id;
			}
		}
        try{
    		$db = $this->MasterDbModel->get($db_id);
    		$_SESSION['conn'] = $db;
			redirect("Home");
        }
		catch(Exception $e){
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}		
	}
}