<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TargetFokus extends MY_Controller {

	function __construct()
	{
		parent::__construct();
        // $this->load->model('TargetFokusModel');
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
		$paramsLog['Module']="TARGET FOKUS"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU TARGET FOKUS";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$data = array();
		// $CheckAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		// echo json_encode($CheckAccess);die;
			// $data["access"] = $CheckAccess;	
		// $url = $this->API_URL."/TargetFokus/GetAllTargetFokus?api=APITES";
		// echo json_encode($url);die;
		// $targetfokus = json_decode(file_get_contents($url), true);
		// if ($targetfokus["result"]=="sukses") {
			// $data["result"] = $targetfokus["data"];
		// echo json_encode($targetfokus["data"]);die;
		// } else {
       		// $data["result"] = array();
       		// $data["alert"] = $targetfokus["error"];
		// }
       // die($this->API_URL."/TargetFokus/GetDivisiList?api=APITES");
        $divisi = json_decode(file_get_contents($this->API_URL."/TargetFokus/GetDivisiList?api=APITES"), true);
        if ($divisi["result"]=="sukses") {
        	$data["divisions"] = $divisi["data"];
       	} else {
       		$data["divisions"] = array();
       		$data["alert"] = $divisi["error"];
       	}
       	//die($this->API_URL."/TargetFokus/GetMerkList?api=APITES&divisi=".urlencode("all"));
        $merk = json_decode(file_get_contents($this->API_URL."/TargetFokus/GetMerkList?api=APITES&divisi=".urlencode("all")), true);
        if ($merk["result"]=="sukses") {
        	$data["merks"] = $merk["data"];
       	} else {
       		$data["merks"] = array();
       		$data["alert"] = $merk["error"];
       	}
       	//die($this->API_URL."/TargetFokus/GetKategoriList?api=APITES&divisi=".urlencode("all"));
        $kategori = json_decode(file_get_contents($this->API_URL."/TargetFokus/GetKategoriList?api=APITES"), true);
        if ($merk["result"]=="sukses") {
        	$data["kategoris"] = $kategori["data"];
			} else {
       		$data["kategoris"] = array();
       		$data["alert"] = $kategori["error"];
       	}
        $startdate = json_decode(file_get_contents($this->API_URL."/TargetFokus/GetStartDate?api=APITES"), true);
        if ($startdate["result"]=="sukses") {
        	$data["startdates"] = $startdate["data"];
			} else {
       		$data["startdates"] = array();
       		$data["alert"] = $startdate["error"];
       	}
       	if ($sukses==1) {
       		$data["alert"] = "Simpan Berhasil";
       	}
		$this->RenderView('TargetFokusView',$data);

		$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
	}
	
	
	public function GetAllTargetFokus(){
		$param = $_GET;
        $data = file_get_contents($this->API_URL."/TargetFokus/GetAllTargetFokus?".http_build_query($param));
		echo $data;
	}
		
		
		

	public function Add($sukses=0)
	{

		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="TARGET FOKUS"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU TARGET FOKUS - ADD";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);


		$CheckAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		$data = array();
		// die($this->API_URL."/TargetFokus/GetDivisiList?api=APITES");
        $divisi = json_decode(file_get_contents($this->API_URL."/TargetFokus/GetDivisiList?api=APITES"), true);
        if ($divisi["result"]=="sukses") {
        	$data["divisions"] = $divisi["data"];
       	} else {
       		$data["divisions"] = array();
       		$data["alert"] = $divisi["error"];
       	}
       	//die($this->API_URL."/TargetFokus/GetMerkList?api=APITES&divisi=".urlencode("all"));
        $merk = json_decode(file_get_contents($this->API_URL."/TargetFokus/GetMerkList?api=APITES&divisi=".urlencode("all")), true);
        if ($merk["result"]=="sukses") {
        	$data["merks"] = $merk["data"];
       	} else {
       		$data["merks"] = array();
       		$data["alert"] = $merk["error"];
       	}
       	//die($this->API_URL."/TargetFokus/GetKategoriList?api=APITES&divisi=".urlencode("all"));
        $kategori = json_decode(file_get_contents($this->API_URL."/TargetFokus/GetKategoriList?api=APITES"), true);
        if ($merk["result"]=="sukses") {
        	$data["kategoris"] = $kategori["data"];
			} else {
       		$data["kategoris"] = array();
       		$data["alert"] = $kategori["error"];
       	}
		
       	if ($sukses==1) {
       		$data["alert"] = "Simpan Berhasil";
       	}
		$this->RenderView('TargetFokusAdd',$data);

		$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
	}

	public function GetMerkList()
	{
		$post = $this->PopulatePost();
		if(isset($post['divisi'])){
			$post['divisi'] = str_replace("&","",htmlspecialchars_decode($post['divisi'])); // hilangkan & pada CO&SANITARY
	        $merk = json_decode(file_get_contents($this->API_URL."/TargetFokus/GetMerkList?api=APITES&divisi=".$post["divisi"]), true);
	        if ($merk["result"]=="sukses") {
				echo json_encode($merk["data"]);
	       	} else {
				echo json_encode(array('error'=>$merk["error"]));
	       	}
		} else {
			echo json_encode(array('error'=>'Invalid Request'));
		}
	}

	public function GetJenisList()
	{
		$post = $this->PopulatePost();
		if(isset($post['divisi']) && isset($post["merk"])){
			//die($this->API_URL."/TargetFokus/GetMerkList?api=APITES&divisi=".urlencode($post["divisi"]));
	        $result = json_decode(file_get_contents($this->API_URL."/TargetFokus/GetJenisList?api=APITES&divisi=".urlencode($post["divisi"]).
	        										"&merk=".urlencode($post["merk"])), true);
	        if ($result["result"]=="sukses") {
				echo json_encode($result["data"]);
	       	} else {
				echo json_encode(array('error'=>$result["error"]));
	       	}
		} else {
			echo json_encode(array('error'=>'Invalid Request'));
		}
	}

	public function GetTargetFokusList()
	{
		$post = $this->PopulatePost();
		if(isset($post['divisi']) && isset($post["merk"]) && isset($post["jenis"])){
			$url = $this->API_URL."/TargetFokus/GetTargetFokusList?api=APITES&divisi=".urlencode($post["divisi"]).
	        				"&merk=".urlencode($post["merk"]).
							"&jenis=".urlencode($post["jenis"]);
			// echo json_encode($url);die;
	        $barang = json_decode(file_get_contents($url), true);
	        if ($barang["result"]=="sukses") {
				echo json_encode($barang["data"]);
	       	} else {
				echo json_encode(array('error'=>$barang["error"]));
	       	}
		} else {
			echo json_encode(array('error'=>'Invalid Request'));
		}
	}

	public function GetBarangList()
	{
		$post = $this->PopulatePost();
		if(isset($post['divisi']) && isset($post["merk"]) && isset($post["jenis"])){
			$url = $this->API_URL."/TargetFokus/GetBarangList?api=APITES&divisi=".urlencode($post["divisi"]).
	        				"&merk=".urlencode($post["merk"]).
							"&jenis=".urlencode($post["jenis"]);
			// echo json_encode($url);die;
	        $barang = json_decode(file_get_contents($url), true);
	        if ($barang["result"]=="sukses") {
				echo json_encode($barang["data"]);
	       	} else {
				echo json_encode(array('error'=>$barang["error"]));
	       	}
		} else {
			echo json_encode(array('error'=>'Invalid Request'));
		}
	}
	
	public function SimpanTargetFokus()
	{

		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="TARGET FOKUS"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU TARGET FOKUS - SIMPAN TARGET FOKUS";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$post = $this->PopulatePost();
		// echo json_encode($post);die;
		if(ISSET($post['save'])){ //--------------simpan------------
			if (isset($post['kodebarang'])){
				for($i=0;$i<count($post['kodebarang']);$i++)
				{
					$data = [
						"api" => "APITES",
						"kdbrg" => $post["kodebarang"][$i],
						"kategori" => $post["kategoriinsentif"][$i],
						"tglawal" => date("Y-m-d", strtotime($post["tglawal"][$i])),
						"tglakhir" => date("Y-m-d", strtotime($post["tglakhir"][$i])),
						"user" => $_SESSION["logged_in"]["username"]
					];
					
					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_URL => $this->API_URL."/TargetFokus/SimpanTargetFokus",
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_TIMEOUT => 60,
						CURLOPT_POST => 1,
						CURLOPT_POSTFIELDS => json_encode($data),
						CURLOPT_HTTPHEADER => array("Content-type: application/json")
					));
					
					$response = curl_exec($curl);
					$err = curl_error($curl);
					curl_close($curl);
					echo($response."<br><br>");
				}
				
				$data = array(
					'api'=>'APITES',
					'divisi'=>$post['filterDivisi'],
					'item_fokus'=>$post['filterKategori'],
					'tgl_awal'=>$post['dp1'],
					'tgl_akhir'=>$post['dp2'],
					'user'=>$_SESSION["logged_in"]["username"]
				);
				$this->SendEmailNotification($data);
				
			}

			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			redirect("TargetFokus/index/1");
		}
		else{ //--------------export------------
			$data = array();
			$data["mode"] = "TARGET FOKUS";
			$data["divisi"] = $post['filterDivisi'];
			$data["awal"] = date("Y-m-d",strtotime($post['filterStartDate']));
			$data["databases"] = $this->MasterDbModel->getListForExport("", "NamaDb");
			$this->RenderView('ExportDataTargetFokusView',$data);	
		}
    }
	
	
	public function SendEmailNotification($data = array()){
	
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->API_URL."/TargetFokus/SendEmailNotification",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		// echo($response."<br><br>");
	}

	Public function ExportTargetFokus()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="TARGET FOKUS"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU TARGET FOKUS - EXPORT TARGET FOKUS";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$err = "";
		$post = $this->PopulatePost();
		
		// echo json_encode($post); die;

		if(isset($post['db'])){
			$dbId = $post["db"];
			$db = $this->MasterDbModel->get($dbId);
			//die(json_encode($db));

			$URL = $this->API_URL;
			// $URL.= "/TargetFokus/GetTargetFokusList?api=APITES&divisi=".urlencode($post['divisi']).
    				// "&merk=".urlencode("all")."&jenis=".urlencode("all")."&kategori=".urlencode("all")."&awal=".urlencode($post['awal'])."&filter=".urlencode("EXPORT").
    				// "&view=1";
			$URL.= "/TargetFokus/GetTargetFokusList?api=APITES&divisi=".urlencode($post['divisi'])."&awal=".urlencode($post['awal']);
    		// die(json_encode($URL));
			
	        $list = json_decode(file_get_contents($URL), true);
	        // die(json_encode($list));
	        
	        if ($list["result"]=="sukses") {
	        	$PG = $list["data"];
	        	$lanjut = true;
		        if ($lanjut) {

					$data = [
						"api" => "APITES",
						"list" => $PG,
						"user" => $_SESSION["logged_in"]["username"],
						"svr"=>$db->Server,
						"db" => $db->Database,
						"uid" => SQL_UID,
						"pwd" => SQL_PWD
					];
					// die(json_encode($data));
					$urlBhakti = HO;

					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_URL => $urlBhakti.API_BKT."/TargetFokus/UpdateTargetFokus",
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_TIMEOUT => 600,
						CURLOPT_POST => 1,
						CURLOPT_POSTFIELDS => json_encode($data),
						CURLOPT_HTTPHEADER => array("Content-type: application/json")
					));
					
					// die(json_encode($urlBhakti));
					$response = curl_exec($curl);
					$err = curl_error($curl);
					curl_close($curl);
					// die(json_encode($response));

					if ($response===false) {
						$lanjut = false;
						$err = "API Tujuan OFFLINE";
					} else {
				        if (json_decode($response)=="sukses") {
				        	$lanjut=true;
				       	} else {
				       		$lanjut=false;
				       		$err=json_decode($response);
				       	}
			        }
		        }

		        $paramsLog['Remarks']="SUCCESS";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);

	        	if ($lanjut) {
		        	echo json_encode(array("result"=>"sukses", "error"=>"Sukses Update List Barang Target Fokus", "list"=>$PG));
	        	} else {
					echo json_encode(array("result"=>"gagal", "error"=>$err, "list"=>$PG)); //"Error Update Kategori Barang"));
	        	}
	        } else {
	        	echo json_encode(array("result"=>$list["result"], "error"=>$list["error"], "list"=>array()));
	        }
		} else {
			echo json_encode(array("result"=>"gagal", 'error'=>'Param CBG Belum Diberikan', "list"=>array()));
		}
	}

    public function Delete()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="TARGET FOKUS"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU TARGET FOKUS - EXPORT TARGET FOKUS";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$dataid = $this->input->get('id');
        $data = [
			"api" => "APITES",
			"id" => $dataid,
			"user" => $_SESSION["logged_in"]["username"]
		];
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->API_URL."/TargetFokus/Delete",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		
		$result = json_decode($response);
		if($result->result=='sukses'){
			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			$this->SendEmailNotification($data);
			redirect('TargetFokus?deletesuccess=1');
		}
		else{
			$paramsLog['Remarks']="FAILED - DELETE GAGAL";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			redirect('TargetFokus?deletesuccess=0');
		}
		
    }
}