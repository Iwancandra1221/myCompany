<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MasterGroupItemFokus extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function index($sukses=0)
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER GROUP ITEM FOKUS"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER GROUP ITEM FOKUS";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);
		$data = array();
		
		$url = $this->API_URL."/MasterGroupItemFokus/GetMasterGroupItemFokusList?api=APITES";
		// echo ($url); die;
		$MasterGroupItemFokus = json_decode(file_get_contents($url), true);
		
		//die(json_encode($MasterGroupItemFokus));
		if ($MasterGroupItemFokus["result"]=="sukses") {
			$data["result"] = $MasterGroupItemFokus["data"];
		} else {
			echo json_encode(array('error'=>$MasterGroupItemFokus["error"]));
		}
       	if ($sukses==1) {
       		$data["alert"] = "Simpan Berhasil";
       	}

       	$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);

		$this->RenderView('MasterGroupItemFokusView',$data);
	}

	public function Add($sukses=0)
	{

		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER GROUP ITEM FOKUS"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER GROUP ITEM FOKUS - ADD";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$data = array();
		
		$url = $this->API_URL."/MasterGroupItemFokus/GetWilayahList?api=APITES";
		$wilayah = json_decode(file_get_contents($url), true);
		
		if ($wilayah["result"]=="sukses") {
			$data["wilayah"] = $wilayah["data"];
		} else {
			echo json_encode(array('error'=>$wilayah["error"]));
		}
		
       	if ($sukses==1) {
       		$data["alert"] = "Simpan Berhasil";

       		$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
       	}
       	else{
       		$paramsLog['Remarks']="FAILED Simpan gagal";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
       	}
		$this->RenderView('MasterGroupItemFokusAdd',$data);
	}

	public function Edit()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER GROUP ITEM FOKUS"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER GROUP ITEM FOKUS - EDIT";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$level_code = $this->input->get('level_code');
		$data = array();
		$url = $this->API_URL."/MasterGroupItemFokus/GetMasterGroupItemFokusList?api=APITES&level_code=".urlencode($level_code);
		// echo ($url); die;
		$MasterGroupItemFokus = json_decode(file_get_contents($url), true);	
		// die(json_encode($MasterGroupItemFokus));		
		if ($MasterGroupItemFokus["result"]=="sukses") {
			$data["result"] = $MasterGroupItemFokus["data"];
			$data["wilayah"] = $MasterGroupItemFokus["wilayah"];
			$data["checked"] = $MasterGroupItemFokus["checked"];

			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
		} else {
			$paramsLog['Remarks']="FAILED - EDIT GAGAL";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			echo json_encode(array('error'=>$MasterGroupItemFokus["error"]));
		}
		
		$this->RenderView('MasterGroupItemFokusEdit',$data);
	}

	public function Save()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER GROUP ITEM FOKUS"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER GROUP ITEM FOKUS - SAVE";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$post = $this->PopulatePost();
		$data = [
			"api" => "APITES",
			"level_code" => $post["level_code"],
			"wilayah" => $post["wilayah"],
			"is_active" => $post["is_active"],
			"user" => $_SESSION["logged_in"]["username"]
		];
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->API_URL."/MasterGroupItemFokus/Save",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		// echo json_encode($response);die;
		$result = json_decode($response);
		if($result->result=='sukses'){

			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			redirect('MasterGroupItemFokus?insertsuccess=1');
		}
		else{
			$paramsLog['Remarks']="FAILED - SAVE GAGAL";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			redirect('MasterGroupItemFokus?insertsuccess=0');
		}
	}


    public function Delete()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER GROUP ITEM FOKUS"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER GROUP ITEM FOKUS - DELETE";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$level_code = $this->input->get('level_code');
        $data = [
			"api" => "APITES",
			"level_code" => $level_code,
			"user" => $_SESSION["logged_in"]["username"]
		];
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->API_URL."/MasterGroupItemFokus/Delete",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		// echo json_encode($response);die;
		$result = json_decode($response);
		if($result->result=='sukses'){
			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			redirect('MasterGroupItemFokus?deletesuccess=1');
		}
		else{
			$paramsLog['Remarks']="FAILED - DELETE GAGAL";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			redirect('MasterGroupItemFokus?deletesuccess=0');
		}
		
    }
}