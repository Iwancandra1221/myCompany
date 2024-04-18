<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MsDealerOld extends MY_Controller {
	//unlock toko pinadhin k model
	public $alert = "";
	public $test_mode = false;
	public $test_email = "";//"itdev.dist@bhakti.co.id";
	public $test_whatsapp = "";
	public $whatsapp_account = "SALES";

	public $newRuleDate = "05/01/2022";
	public $approvers = array();
	public $recipients= array();
	public $whatsapps = array();

	function __construct()
	{
		parent::__construct();
		$this->load->model('MsDealerModelOld');
		$this->load->model('SalesManagerModel');
		$this->load->library("email");
		$this->load->helper('url');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function urlTest()
	{
		die(date("Ymd"));
		/*$URL = "http://10.1.0.92:90/".API_BKT."/MasterDealer/ChangeLimitDealer?svr=".urlencode(trim("10.1.48.200"));
		$URL.= "&kdplg=".urlencode(trim("DMIB063"))."&div=".urlencode(trim("MO"));
		$URL.= "&newcl=".urlencode("237000000")."&db=".urlencode(trim("BHAKTI"));
		$URL.= "&uid=".urlencode(trim(SQL_UID))."&pwd=".urlencode(trim(SQL_PWD));
		$URL.= "&reqby=".urlencode("LEROY AKIRA GEOVANI")."&reqdate=".urlencode(trim("2020-08-04"));
		$URL.= "&appby=".urlencode("HERBY PERSOLIMA");
		die($URL);*/
	}
	
	public function index()
	{
		//$alert = urldecode($this->input->get("alert"));

		if(isset($_SESSION['conn'])) {

			$data = array();
			$dealers = array();
			$ListDealer = array();
			$ListWilayah = array();

			$userBranch = $_SESSION["branchID"];
			$userDB = $_SESSION['databaseID'];
			$data["DefaultWilayah"] = $_SESSION["logged_in"]["branch"];
			$API = $_SESSION["conn"]->AlamatWebService;


			$URL = $API.API_BKT."/MasterDealer/GetListDealer?api=APITES&ubranch=".urlencode($userBranch);
			//die($URL);
			$HTTPRequest = @file_get_contents($URL);
			if ($HTTPRequest == null) {
				exit("<b>Koneksi ke Database Bhakti Sedang Ada Gangguan. Coba Kembali Beberapa Saat Lagi</b><br>
					 Apabila Gangguan berlangsung <u>lebih dari</u> 30 menit, <u?Hubungi IT</u>");
			}

	        $GetDealer = json_decode($HTTPRequest, true);
	        if ($GetDealer["result"]=="sukses"){
	        	$ListDealer = $GetDealer["data"];
				for($i=0; $i<count($ListDealer); $i++){
					array_push($dealers, trim($ListDealer[$i]["NM_PLG"])." - ".trim($ListDealer[$i]["KD_PLG"])." - ".trim($ListDealer[$i]["WILAYAH"]));
				}
		    } 

			$URL = $API.API_BKT."/MasterDealer/GetListWilayah?api=APITES&ubranch=".urlencode($userBranch);
			//die($URL);
	        $GetWilayah = json_decode(file_get_contents($URL),true);
	        //die(json_encode($GetWilayah));
	        if ($GetWilayah["result"]=="sukses"){
	        	$ListWilayah = $GetWilayah["data"];
		    }

		    if (count($ListDealer)>0) {

		    	$data["ListWilayah"] = $ListWilayah;
				$data["ListDealer"] = $ListDealer;
		        $data["Dealers"] = $dealers;
		    	$this->RenderView('MsDealerList', $data);

		    } else {

				$data["content_html"] = '<script language="javascript">';
		        $data["content_html"].= 'alert("GAGAL MENGAMBIL LIST DEALER KE DB BHAKTI")';
		        // if($alert!="") {
		        // $data["content_html"].= 'alert("'.urldecode($alert).'")';
		        // }
		        $data["content_html"].= '</script>';
		        $this->RenderView('MsDealerList', $data);		    	

		    }
		} else {
			redirect("ConnectDB");
		}      
	}	

    public function CreateRequestCBD()
    {
    	$data = array();
    	$post = $this->PopulatePost();
		$RequestType ="";
		if ($post["CBD"]==1) {
			$RequestType = "CBD ON";
		} else {
			$RequestType = "CBD OFF";
		}

		$HistoryID = "CBD_".$post["KdPlg"]."_".date("YmdHis");
		$ExpiryDate = date("Y-m-d", strtotime("+1 day"));
		if (date("w")==5) {
			$ExpiryDate = date("Y-m-d", strtotime("+4 day"));
		} else if (date("w")==6) {
			$ExpiryDate = date("Y-m-d", strtotime("+3 day"));
		} else if (date("w")==0) {
			$ExpiryDate = date("Y-m-d", strtotime("+2 day"));
		}	/*Jumat,Sabtu,Minggu ExpiryDate di Hari Senin*/

		$params = array();
		$params["DatabaseID"] = $_SESSION["conn"]->DatabaseId;
		$params["BranchDB"] = $_SESSION["conn"]->BranchId;
		$params["NamaDB"] = $_SESSION["conn"]->NamaDb;			
		$params["RequestID"] = $HistoryID;
		$params["HistoryID"] = $HistoryID;
		$params["HistoryDate"] = date("Y-m-d", strtotime($post["Tgl"]));
		$params["ExpiryDate"] = $ExpiryDate;
		$params["RequestType"] = $RequestType;
		$params["KdPlg"] = $post["KdPlg"];

		if(isset($_SESSION['conn'])) {

			$RequestExisted = $this->MsDealerModelOld->CheckExistingRequest($RequestType, $params);
			if ($RequestExisted==false) {

				$API = $_SESSION["conn"]->AlamatWebService;			
				$URL = $API.API_BKT."/MasterDealer/CreateRequestCBD";
				$urlParams = array("api"=>"APITES", "kdplg"=>$params["KdPlg"], "cbd"=>$RequestType,
								"tgl"=>date("Y-m-d",strtotime($params["HistoryDate"])), "hsid"=>$HistoryID, 
								"exp"=>$ExpiryDate, 
								"user"=>$_SESSION["logged_in"]["username"],
								"svr"=>$_SESSION["conn"]->Server, "db"=>$_SESSION["conn"]->Database, 
								"uid"=>SQL_UID, "pwd"=>SQL_PWD);

				$options = array(
				    'http' => array(
				        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				        'method'  => 'POST',
				        'content' => http_build_query($urlParams)
				    )
				);
				$context  = stream_context_create($options);
				$doUpdate = json_decode(file_get_contents($URL, false, $context), true);

				if ($doUpdate === FALSE) {
					echo(json_encode(array("result"=>"gagal", "error"=>"Tidak Terhubung ke Database Bhakti")));
				} else if ($doUpdate["result"]!="sukses") {
					echo(json_encode(array("result"=>"gagal", "error"=>$doUpdate["error"])));
				}


				$recipients = array();
				$allRecipients = array();

				$this->load->model("SalesManagerModel");
				$BrandManagers = $this->SalesManagerModel->GetBrandManagers();
				$GM = $this->SalesManagerModel->GetGeneralManager();
				$EmpFound = false;
				$Priority = 0;

				$user = array();
				$user["Name"] = $_SESSION["logged_in"]["username"];
				$user["UserID"] = $_SESSION["logged_in"]["employeeid"];
				$user["UserEmail"] = $_SESSION["logged_in"]["useremail"];
				$user["Email"] = $_SESSION["logged_in"]["email"];
				$user["BranchID"] = $_SESSION["logged_in"]["branch_id"];
				$user["City"] = $_SESSION["logged_in"]["city"];

				$API = $_SESSION["conn"]->AlamatWebService;
				$URL = $API.API_BKT."/MasterDealer/GetDealer?api=APITES".
						"&plg=".urlencode($post["KdPlg"]).
						"&svr=".urlencode($_SESSION["conn"]->Server)."&db=".urlencode($_SESSION["conn"]->Database).
						"&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD);
				$GetDealer = json_decode(file_get_contents($URL), true);
				
				if ($GetDealer["result"]=="sukses") {
					$DEALER = $GetDealer["data"];				
					$params["Dealer"] = $DEALER;
					$params["Tgl"] = $post["Tgl"];
					$email_content = $this->CreateEmailContent_CBD($RequestType, $DEALER, $user, $post["Tgl"]);

					$Priority = 1;
					$lanjutBM = true;
					$lanjutGM = false;
					$recipients = array();
					$allRecipients = array();
					$params["ApprovalNeeded"] = 1;
					$params["Priority"] = $Priority;

					if ($lanjutBM==true) {
						$Priority = 1;
						$recipients = array();
						$params["ApprovalNeeded"] = 1;
						$params["Priority"] = $Priority;
						foreach($BrandManagers as $m) {
							$EmpFound = false;
							for($i=0;$i<count($recipients);$i++) {
								if ($recipients[$i]["NAMA"]==$m->employee_name && $recipients[$i]["EMAIL"]==(($m->email_address==null)?$m->email:$m->email_address)) {
									$EmpFound = true;
								}
							}
							if ($EmpFound==false) {
								array_push($recipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
								array_push($allRecipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
							}
						}
						$REQUESTID = $this->MsDealerModelOld->RequestCBD($params, $email_content, $recipients, $user);
					}

					if ($lanjutGM==true) {
						$Priority+=1;
						$recipients = array();
						$params["ApprovalNeeded"] = 1;
						$params["Priority"] = $Priority;
						foreach($GM as $m) {
							$EmpFound = false;
							for($i=0;$i<count($recipients);$i++) {
								if ($recipients[$i]["NAMA"]==$m->employee_name && $recipients[$i]["EMAIL"]==(($m->email_address==null)?$m->email:$m->email_address)) {
									$EmpFound = true;
								}
							}
							if ($EmpFound==false) {
								array_push($recipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
								array_push($allRecipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
							}
						}
						//die(json_encode($recipients));
						$REQUESTID = $this->MsDealerModelOld->RequestCBD($params, $email_content, $recipients, $user);
					}

					$sendEmail = $this->MsDealerModelOld->EmailRequest($RequestType, $params, $email_content, $allRecipients, $user);
					if ($sendEmail>0) {
						echo(json_encode(array("result"=>"sukses", "err"=>"")));
					} else {
						echo(json_encode(array("result"=>"gagal", "err"=>$sendEmail)));
					}

				} else {
					echo(json_encode(array("result"=>"gagal", "err"=>"Gagal Ambil Detail Data Dealer ke DB Bhakti")));
				}
			} else {
				echo(json_encode(array("result"=>"gagal", "err"=>"Request ".$RequestType." untuk Dealer dan Tanggal yang Dipilih Sudah Ada")));
			}
		} else {
			redirect("ConnectDB");
		}
    }

	public function CreateEmailContent_CBD($REQUESTTYPE="", $DEALER, $USER, $DATE)
	{
		$email_content = "Kode Dealer : <b>".$DEALER["KD_PLG"]."</b><br>";
		$email_content.= "Nama Dealer : <b>".$DEALER["NM_PLG"]."</b><br>";
		$email_content.= "Wilayah : <b>".$DEALER["WILAYAH"]."</b><br>";
		$email_content.= "=========================================<br>";
		if ($REQUESTTYPE == "CBD OFF") {
			$email_content.= "Status CBD ingin dinonaktifkan per Tgl. <b>".date("d-M-Y", strtotime($DATE))."<br>";
		} else {
			$email_content.= "Status CBD ingin diaktifkan per Tgl. <b>".date("d-M-Y", strtotime($DATE))."<br>";
		}
		return $email_content;
	}    

	public function TokoTerkunci($flag=0){
		// die(json_encode($_SESSION));
		if(isset($_SESSION['conn'])) {
			$svr = $_SESSION["conn"]->Server;
			$db = $_SESSION["conn"]->Database;
			$url = $_SESSION["conn"]->AlamatWebService;
			$url = $url.API_BKT."/MasterDealer/GetListTokoTerkunci?api=APITES&svr=".urlencode($svr)."&db=".urlencode($db);
			// die($url);
	        $result = json_decode(file_get_contents($url), true);
	        $str = "";
			if($result["result"]=="sukses"){
				$dealers = $result["data"];
				for ($i=0;$i<count($dealers);$i++) {
					if (isset($dealers[$i])) {
						$req = $this->MsDealerModelOld->GetRequestUnlockByToko($dealers[$i]["KD_PLG"]);
						if ($req==null) {
							$str.= "Tidak ada request untuk toko ".$dealers[$i]["NM_PLG"]." ".$dealers[$i]["KD_PLG"]."<br/>";
							$dealers[$i]["REQUEST"] = null;
						} else {
							$str.= "Ada request untuk toko ".$dealers[$i]["NM_PLG"]." ".$dealers[$i]["KD_PLG"]."<br/>";
							$dealers[$i]["REQUEST"] = $req;
						}
					} else {
						$dealers[$i]["REQUEST"] = null;
					}
				}
				$data["dealers"] = $dealers;
				$data["alert"] = "";
			} else {
				$data["dealers"] = array();
				$data["alert"] = $result["error"];
			}
			if ($flag==1) {
				$alert = "Request Buka Lock Toko Telah Diemail!\n";
				// $alert.= "Whatsapp User: ".$_SESSION["logged_in"]["whatsapp"]."\n\n";
				if ($_SESSION["logged_in"]["whatsapp"]=="") {
					$alert.= "No Whatsapp Anda Belum Terdaftar. Daftarkan No Whatsapp Anda untuk menerima notifikasi saat request Anda Ditolak/Disetujui !";
				}
				$data["alert"] = $alert;
			}
			$data["MO"] = false;
			// die($_SESSION["branchID"]);
			$this->RenderView('MsDealerTerkunciView',$data);
		}
		else{
			redirect('Home');
		}
	}

	public function TokoTerkunci2($flag=0){
		if(isset($_SESSION['conn'])){
			$svr = $_SESSION["conn"]->Server;
			$db = $_SESSION["conn"]->Database;
			$url = $_SESSION["conn"]->AlamatWebService;
			$str = "";
	        
	        $result = json_decode(file_get_contents($url.API_BKT."/MasterDealer/GetListTokoTerkunci?api=APITES"), true);
			if($result["result"]=="sukses"){
				$dealers = $result["data"];

				/*$Conns = $this->MasterDbModel->getList();
				foreach ($Conns as $cn) {
					$resultMOCBG = json_decode(file_get_contents($cn->AlamatWebService.API_BKT."/MasterDealer/GetListTokoTerkunciMO?api=APITES"), true);

				}*/
				
				for ($i=0;$i<count($dealers);$i++) {
					if (isset($dealers[$i])) {
						$req = $this->MsDealerModelOld->GetRequestUnlockByToko($dealers[$i]["KD_PLG"]);
						if ($req==null) {
							$str.= "Tidak ada request untuk toko ".$dealers[$i]["NM_PLG"]." ".$dealers[$i]["KD_PLG"]."<br/>";
							$dealers[$i]["REQUEST"] = null;
						} else {
							$str.= "Ada request untuk toko ".$dealers[$i]["NM_PLG"]." ".$dealers[$i]["KD_PLG"]."<br/>";
							$dealers[$i]["REQUEST"] = $req;
						}
					} else {
						$dealers[$i]["REQUEST"] = null;
					}
				}

				$data["dealers"] = $dealers;
				$data["alert"] = "";
			} else {
				$data["dealers"] = array();
				$data["alert"] = $result["error"];
			}
			if ($flag==1) {
				$data["alert"] = "Request Buka Lock Toko Telah Diemail";
			}
			$data["MO"] = true;
			$this->RenderView('MsDealerTerkunciView',$data);
		} else{
			redirect('Home');
		}
	}

	public function TokoTerkunciMO($flag=0){
		$data = array();
		$data["MO"] = true;
		$DB = $this->MasterDbModel->getBhaktiPusat();

		if($DB!=null){
			$svr = $DB->Server;
			$db = $DB->Database;
			$url = $DB->AlamatWebService;

			$str = "";
			$bUrl = $url.API_BKT."/MasterDealer/GetListTokoTerkunciMO?api=APITES&svr=".urlencode($svr)."&db=".urlencode($db);
			$HTTPRequest = HttpGetRequest($bUrl, $url.API_BKT, "AMBIL LIST TOKO TERKUNCI MO");
			// die($HTTPRequest);

	        $result = json_decode($HTTPRequest, true);
			if($result["result"]=="sukses"){
				$dealers = $result["data"];

				/*$Conns = $this->MasterDbModel->getList();
				foreach ($Conns as $cn) {
					$resultMOCBG = json_decode(file_get_contents($cn->AlamatWebService.API_BKT."/MasterDealer/GetListTokoTerkunciMO?api=APITES"), true);

				}*/				
				for ($i=0;$i<count($dealers);$i++) {
					if (isset($dealers[$i])) {
						$req = $this->MsDealerModelOld->GetRequestUnlockByToko($dealers[$i]["KD_PLG"]);
						if ($req==null) {
							$str.= "Tidak ada request untuk toko ".$dealers[$i]["NM_PLG"]." ".$dealers[$i]["KD_PLG"]."<br/>";
							$dealers[$i]["REQUEST"] = null;
						} else {
							$str.= "Ada request untuk toko ".$dealers[$i]["NM_PLG"]." ".$dealers[$i]["KD_PLG"]."<br/>";
							$dealers[$i]["REQUEST"] = $req;
						}
					} else {
						$dealers[$i]["REQUEST"] = null;
					}
				}
				//die($str);
				$data["dealers"] = $dealers;
				$data["alert"] = "";
			} else {
				$data["dealers"] = array();
				$data["alert"] = $result["error"];
			}
			if ($flag==1) {
				$data["alert"] = "Request Buka Lock Toko Telah Diemail";
			}
			$this->RenderView('MsDealerTerkunciView',$data);
		}
		else{
			$data["dealers"] = array();
			$data["alert"] = "Master DB Bhakti Jakarta Tidak Ditemukan!";
			$this->RenderView('MsDealerTerkunciView',$data);
		}
	}

	public function TokoTerkunciProyek($flag=0){
		if(isset($_SESSION['conn'])){
			$svr = $_SESSION["conn"]->Server;
			$db = $_SESSION["conn"]->Database;
			$url = $_SESSION["conn"]->AlamatWebService;
			
			$str = "";
			$bUrl = $url.API_BKT."/MasterDealer/GetListTokoTerkunciProyek?api=APITES&svr=".urlencode($svr)."&db=".urlencode($db);
			//die($bUrl);
	        $result = json_decode(file_get_contents($bUrl), true);
			if($result["result"]=="sukses"){
				$dealers = $result["data"];

				
				for ($i=0;$i<count($dealers);$i++) {
					if (isset($dealers[$i])) {
						$req = $this->MsDealerModelOld->GetRequestUnlockByToko($dealers[$i]["KD_PLG"]);
						if ($req==null) {
							$str.= "Tidak ada request untuk toko ".$dealers[$i]["NM_PLG"]." ".$dealers[$i]["KD_PLG"]."<br/>";
							$dealers[$i]["REQUEST"] = null;
						} else {
							$str.= "Ada request untuk toko ".$dealers[$i]["NM_PLG"]." ".$dealers[$i]["KD_PLG"]."<br/>";
							$dealers[$i]["REQUEST"] = $req;
						}
					} else {
						$dealers[$i]["REQUEST"] = null;
					}
				}
				//die($str);
				$data["dealers"] = $dealers;
				$data["alert"] = "";
			} else {
				$data["dealers"] = array();
				$data["alert"] = $result["error"];
			}
			if ($flag==1) {
				$data["alert"] = "Request Buka Lock Toko Telah Diemail";
			}
			$data["MO"] = true;
			$this->RenderView('MsDealerTerkunciView',$data);
		}
		else{
			redirect('Home');
		}
	}

	public function RequestBukaLockToko()
	{
		$post = $this->PopulatePost();
		if (isset($post['KDPLG'])){
			$CreateRequest = $this->MsDealerModelOld->CreateRequestUnlockToko($post);
			if ($CreateRequest["result"]=="SUCCESSFUL") {
				$KodeRequest = $CreateRequest["requestID"];
				$post["REQUESTBY"] = $_SESSION["logged_in"]["username"];

				$SendRequest = $this->MsDealerModelOld->EmailRequestUnlockToko($KodeRequest);
				// $SendRequest = $this->notifikasiWA($KodeRequest);
				if ($SendRequest!="SUCCESSFUL") {
					$data["content_html"] = '<script language="javascript">';
			        $data["content_html"].= 'alert("Request Tidak Berhasil Dikirim:'.$SendRequest.'")';
			        $data["content_html"].= '</script>';
			        $data["content_html"].= "<script>window.close();</script>";
			        $this->SetTemplate('template/notemplate');
				    $this->RenderView("MsDealerRequestUnlockView", $data);
				} else {
					if ($post["WILAYAH"]=="SPECIAL SALES" || $post["WILAYAH"]=="DM" || $post["WILAYAH"]=="MODERN OUTLET") {
						redirect("MsDealer/TokoTerkunciMO/1");
					} else if ($post["WILAYAH"]=="PROYEK") {
						redirect("MsDealer/TokoTerkunciProyek/1");
					} else {
						redirect("MsDealer/TokoTerkunci/1");			
					}
				}
			} else {
				$data["content_html"] = '<script language="javascript">';
		        $data["content_html"].= 'alert("Request Tidak Berhasil Dibuat:'.$CreateRequest["result"].'")';
		        $data["content_html"].= '</script>';
		        $data["content_html"].= "<script>window.close();</script>";
		        $this->SetTemplate('template/notemplate');
			    $this->RenderView("MsDealerRequestUnlockView", $data);
			}
		} else {
			if ($post["WILAYAH"]=="SPECIAL SALES" || $post["WILAYAH"]=="DM" || $post["WILAYAH"]=="MODERN OUTLET") {
				redirect("MsDealer/TokoTerkunciMO/1");
			} else if ($post["WILAYAH"]=="PROYEK") {
				redirect("MsDealer/TokoTerkunciProyek/1");
			} else {
				redirect("MsDealer/TokoTerkunci/1");			
			}
		}
    }

	public function notifikasiWA($KodeRequest){

		$RQ = $this->MsDealerModelOld->GetRequestUnlockToko($KodeRequest);
    	$GM = $this->SalesManagerModel->GetGM();
		$NO_HP = $this->HelperModel->MobileWithCountryCode($GM->mobile);
		if ($this->test_mode==true) {
			$NO_HP = $this->test_whatsapp;
		}
    	$URL = site_url("MsDealerApproval/ProsesRequestUnlock?kdreq=".urlencode($KodeRequest)."&empid=".urlencode($GM->useremail));

		$response = "SUCCESSFUL";

		$e_content = "*".$RQ->ReqName."* mengirimkan permintaan unlock dealer terkunci\n";
		$e_content.= "Nama Dealer : *".$RQ->NmPlg."*\n";
		$e_content.= "Kode Dealer : *".$RQ->KdPlg."*\n";
		$e_content.= "Wilayah : *".$RQ->Wilayah."*\n";
		$e_content.= "Alasan : *".$RQ->RequestNote."*\n";
		$e_content.= "No Ref : *".$KodeRequest."*\n";
		$e_content.= "\n";

		$e_content.= "".$URL."\n";

		$data = [
			"chatId" => "",
			"phone" => $NO_HP,
			"body" => $URL,
			"previewBase64" => "",
			"title" => "REQUEST UNLOCK TOKO",
			"description" => "",
			"text" => $e_content,
			"quotedMsgId" => "",
			"mentionedPhones" => ""
		];
		$data = json_encode($data);

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => API_MSG.'/sendLinkWA?src='.$this->whatsapp_account,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		$result = json_decode($response);
		if ($result->sent==true) {
			$response = "SUCCESSFUL";
		}

		return $response;
	}

    public function ResendRequestUnlock()
    {
    	$data = array();
    	$post = $this->PopulatePost();
		if(isset($post['KodeRequest'])){
	    	$Reemail = $this->MsDealerModelOld->EmailRequestUnlockToko($post["KodeRequest"]);
    		// echo(json_encode(array("result"=>"gagal","err"=>"notifikasi WA")));

	    	$Reemail = $this->notifikasiWA($post["KodeRequest"]);
	    	// die($Reemail);
	    	if ($Reemail=="SUCCESSFUL") {
	    		echo(json_encode(array("result"=>"sukses","err"=>"")));
	    	} else {
	    		echo(json_encode(array("result"=>"gagal", "err"=>$Reemail)));
	    	}
		}
    }

	

    public function CancelRequestUnlock()
    {
    	$data = array();
    	$post = $this->PopulatePost();
		if(isset($post['KodeRequest']) && isset($post["KetCancel"])){
	    	$REQ = $this->MsDealerModelOld->GetRequestUnlockToko($post["KodeRequest"]);
	    	if ($REQ->IsApproved==1) {
	    		echo(json_encode(array("result"=>"gagal", "request"=>$REQ, "err"=>"Request Sudah Diapprove")));
	    	} else if ($REQ->IsApproved==2) {
	    		echo(json_encode(array("result"=>"gagal", "request"=>$REQ, "err"=>"Request Sudah Direject")));
	    	} else if ($REQ->IsCancelled==1) {
	    		echo(json_encode(array("result"=>"gagal", "request"=>$REQ, "err"=>"Request Sudah Dibatalkan Sebelumnya")));
	    	} else {
		    	$CancelReq = $this->MsDealerModelOld->CancelRequestUnlockToko($post["KodeRequest"], $post["KetCancel"]);
		    	if ($CancelReq==true) {
		    		$this->MsDealerModelOld->EmailCancelUnlockToko($post["KodeRequest"]);
		    		// $this->NotifikasiCancelUnlockToko($post["KodeRequest"]);
		    		echo(json_encode(array("result"=>"sukses","request"=>$REQ, "err"=>"")));
		    	} else {
		    		echo(json_encode(array("result"=>"gagal", "request"=>$REQ, "err"=>"Cancel Request Gagal")));
		    	}
		    }
		} else {
    		echo(json_encode(array("result"=>"gagal", "request"=>null, "err"=>"Data Tidak Lengkap")));
		}
    }

    public function NotifikasiCancelUnlockToko($KodeRequest) 
    {
    	$rq = $this->MsDealerModelOld->GetRequestUnlockToko($KodeRequest);
    	$GM = $this->SalesManagerModel->GetGM();
		$NO_HP = $this->HelperModel->MobileWithCountryCode($GM->mobile);
		if ($this->test_mode==true) {
			$NO_HP = $this->test_whatsapp;
		}

		$e_content = "*".$rq->ReqName."* *MEMBATALKAN* PERMINTAAN UNLOCK DEALER TERKUNCI\n";
		$e_content.= "Alasan : *".$rq->CancelledNote."*\n";
		$e_content.= "-----------------------------------\n";
		$e_content.= "Nama Dealer : *".$rq->NmPlg."*\n";
		$e_content.= "Kode Dealer : *".$rq->KdPlg."*\n";
		$e_content.= "Wilayah : *".$rq->Wilayah."*\n";
		$e_content.= "No Ref : *".$KodeRequest."*\n";
		$e_content.= "\n";

		$data = [
			"chatId" => "",
			"phone" => $NO_HP,
			"body" => $e_content,
			"quotedMsgId" => "",
			"mentionedPhones" => ""
		];
		$data = json_encode($data);

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => API_MSG.'/sendMessageWA?src='.$this->whatsapp_account,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		$result = json_decode($response);
		if ($result->sent==true) {
			return true;
		} else {
			return false;
		}
    }

    

    public function GetRequestUnlockByToko()
    {
		$post = $this->PopulatePost();
		if(isset($post['KdPlg'])){
	        $rq = $this->MsDealerModelOld->GetRequestUnlockByToko($post["KdPlg"]);
	        if ($rq==null) {
				echo json_encode(array("result"=>"sukses", "ket"=>""));
	       	} else if ($rq->IsApproved==1) {
				echo json_encode(array("result"=>"gagal", 'ket'=>"Request Sudah Diajukan [".$rq->ReqName."] dan Sudah Diapprove [".$rq->AppName."]. Unlock Berlaku s/d Tgl ".date("d-m-Y", strtotime($rq->UnlockEnd))));
	       	} else {
	       		echo json_encode(array("result"=>"gagal", 'ket'=>"Request Sudah Diajukan [".$rq->ReqName."] dan Menunggu Approval"));
	       	}
		} else {
			echo json_encode(array("result"=>"gagal", "ket"=>'Kode Pelanggan Belum Diisi'));
		}    	
    }

	public function GetFakturGantung()
    {
		$post = $this->PopulatePost();
		if(isset($post['KdPlg'])){

			if(isset($_SESSION['conn'])){
				$svr = $_SESSION["conn"]->Server;
				$db = $_SESSION["conn"]->Database;
				$url = $_SESSION["conn"]->AlamatWebService;
				$str = "";

				$url = $url.API_BKT."/MasterDealer/GetFakturGantung?api=APITES&plg=".urlencode($post["KdPlg"]);
		        $result = json_decode(file_get_contents($url), true);
				if($result["result"]=="sukses"){
					$faktur = $result["data"];
					echo json_encode(array("result"=>"sukses", "faktur"=>$faktur, "ket"=>""));
				} else if ($result["error"]=="Tidak Ada Faktur Gantung") {
			        $unlock = json_decode(file_get_contents($url.API_BKT."/MasterDealer/UnlockToko2?api=APITES"."&plg=".urlencode($post["KdPlg"]).
			        			"&svr=".urlencode($svr)."&db=".urlencode($db)."&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD)), true);
			        if ($unlock["result"]=="sukses") {
						echo json_encode(array("result"=>"sukses2", "faktur"=>array(), "ket"=>"Tidak Ada Faktur Gantung. Dealer sudah diaktifkan kembali."));
			        } else {
						echo json_encode(array("result"=>"gagal", "faktur"=>array(), "ket"=>"Tidak Ada Faktur Gantung Namun Dealer Tidak Berhasil Diaktifkan Kembali"));
			        }
				} else {
					echo json_encode(array("result"=>"gagal", "faktur"=>array(), "ket"=>$result["error"]));
				}
			} else {
				echo json_encode(array("result"=>"gagal", "faktur"=>array(), "ket"=>"Pilih Database Dulu"));
			}
		} else {
			echo json_encode(array("result"=>"gagal", "faktur"=>array(), "ket"=>'Kode Pelanggan Belum Diisi'));
		}    	
    }

	public function CreditLimit()
	{
		$alert = urldecode($this->input->get("alert"));

		if(isset($_SESSION['conn'])) {
			//$db_id = $_SESSION['databaseID'];
			//die(json_encode($_SESSION["conn"]->NamaDb));
			$API = $_SESSION["conn"]->AlamatWebService;
			if ($this->test_mode==true) $API = HO;
			// $API = HO;
			$URL = $API.API_BKT."/MasterDealer/GetListDealerForCL?api=APITES";
			// die($URL);
			$HTTPRequest = HttpGetRequest($URL, $API.API_BKT, "AMBIL LIST DEALER");
	        $GetDealer = json_decode($HTTPRequest,true);
	        // die(json_encode($GetDealer));
	        $data=array();
	        $data["alert"] = "";
	        
	        if ($GetDealer["result"]=="sukses"){
	        	$ListDealer = $GetDealer["data"];
				$dealers = array();
				for($i=0; $i<count($ListDealer); $i++){
					array_push($dealers, trim($ListDealer[$i]["NM_PLG"])." - ".trim($ListDealer[$i]["KD_PLG"])." - ".trim($ListDealer[$i]["WILAYAH"]));
				}
				//die(json_encode($ListDealer));
				$data["ListDealer"] = $ListDealer;
		        $data["Dealers"] = $dealers;
				$data["kenaikanCL_RED"] = 100000000;

		        if($alert!="") {
					$data["content_html"] = '<script language="javascript">';
			        $data["content_html"].= 'alert("'.urldecode($alert).'")';
			        $data["content_html"].= '</script>';		        	
		        }
		        if (date("Ymd")>=date("Ymd", strtotime($this->newRuleDate))) {
			    	$this->RenderView('MsDealerCreditLimitView', $data);
			    } else {
			        $this->RenderView('MsDealerCreditLimitViewOld', $data);			    	
			    }
		    } else {
				$data = array();
				$data["content_html"] = '<script language="javascript">';
		        $data["content_html"].= 'alert("GAGAL MENGAMBIL LIST DEALER KE DB BHAKTI")';
		        if($alert!="") {
		        $data["content_html"].= 'alert("'.urldecode($alert).'")';
		        }
		        $data["content_html"].= '</script>';
				
				$data["ListDealer"] = array();
		        $data["Dealers"] = array();
				$data["kenaikanCL_RED"] = 100000000;
				
		        $this->RenderView('MsDealerCreditLimitView', $data);

		        /*if (date("Ymd")>=date("Ymd", strtotime($this->newRuleDate))) {
			        $this->RenderView('MsDealerCreditLimitView', $data);
			    } else {
			        $this->RenderView('MsDealerCreditLimitViewOld', $data);			    	
			    }*/
		    }
		} else {
			redirect("ConnectDB");
		}	        
	}	

    public function GetCreditLimit()
    {
		$post = $this->PopulatePost();
		if(isset($post['KdPlg']) && isset($post["Divisi"])){
			set_time_limit(60);
	        $domainUrl = $_SESSION['conn']->AlamatWebService;
	        if ($post["WilPlg"]=="MODERN OUTLET" || $post["WilPlg"]=="DM") {
	        	$dbHQ = $this->MasterDbModel->getBhaktiPusat();
	        	$domainUrl = $dbHQ->AlamatWebService;
	        }
			if ($this->test_mode==true) $domainUrl = HO;

	        $divisi = "";
	        if ($post["Divisi"]=="MO" || $post["Divisi"]=="DM") {
	        	$divisi = "MISHIRIN";
	        } else {
	        	$divisi = $post["Divisi"];
	        }

			$domainUrl.= API_BKT."/";
			$result = $this->GetCreditLimitDealer($domainUrl, $post["KdPlg"], $divisi, $post["NmPlg"]);
			echo json_encode($result);
		} else {
			echo json_encode(array("result"=>"gagal", "data"=>array(), "ket"=>'Kode Pelanggan/Divisi Belum Diisi'));
		}    	
    }

    public function GetCreditLimitDealer($domainUrl, $kodePelanggan, $divisi, $namaPelanggan="")
    {
    	$url = $domainUrl."MasterDealer/CheckLimitDealer?api=APITES&kdplg=".urlencode($kodePelanggan).
    					"&divisi=".urlencode(htmlspecialchars_decode($divisi));
    	// die($url);
    	$HTTPRequest = HttpGetRequest_Ajax($url, $domainUrl, "Check Credit Limit ".$namaPelanggan, 30000);
    	if ($HTTPRequest["connected"]==true) {
    		// die($HTTPRequest["result"]);
	        $result = json_decode($HTTPRequest["result"], true);
	        // die(json_encode($result));
	        if ($result["result"]=="sukses") {
				return array("result"=>"sukses", "data"=>$result["data"], "ket"=>"");
	       	} else {
	       		return array("result"=>"gagal",  "data"=>array(), "ket"=>$result["error"]);
	       	}
	    } else {
	    	return array("result"=>"gagal", "data"=>array(), "ket"=>$HTTPRequest["err"]);
	    }
    }

    public function GetCreditLimit2()
    {
		$post = array();
		$post["KdPlg"] = $this->input->get("kdplg");
		$post["WilPlg"] = $this->input->get("wilplg");
		$post["Divisi"] = $this->input->get("divisi");

		if(isset($post['KdPlg']) && isset($post["Divisi"])){
			set_time_limit(60);
	        $url = $_SESSION['conn']->AlamatWebService;
	        if ($post["WilPlg"]=="MODERN OUTLET" || $post["WilPlg"]=="DM") {
	        	$dbHQ = $this->MasterDbModel->getBhaktiPusat();
	        	$url = $dbHQ->AlamatWebService;
	        }

	        $divisi = "";
	        if ($post["Divisi"]=="MO" || $post["Divisi"]=="DM") {
	        	$divisi = "MISHIRIN";
	        } else {
	        	$divisi = $post["Divisi"];
	        }


			$url = $url.API_BKT."/MasterDealer/CheckLimitDealer?api=APITES&kdplg=".urlencode($post["KdPlg"]).
    					"&divisi=".urlencode(htmlspecialchars_decode($divisi));
    		//die($url);
	        $get = json_decode(file_get_contents($url),true);
	        //die(json_encode($get));
	        if ($get["result"]=="sukses") {
				echo json_encode(array("result"=>"sukses", "data"=>$get["data"]));
	       	} else {
	       		echo json_encode(array("result"=>"gagal", "data"=>array(), "ket"=>$get["error"]));
	       	}
		} else {
			echo json_encode(array("result"=>"gagal", "data"=>array(), "ket"=>'Kode Pelanggan/Divisi Belum Diisi'));
		}    	
    }

    public function GetCreditLimitGroup()
    {
    	//die("hello");
		$post = $this->PopulatePost();
		if(isset($post['KdPlg']) && isset($post["Divisi"])){
			$dealers = array();
			$GetDealers = $this->ProsesCreditLimitGroup($post["KdPlg"], $post["WilPlg"], $post["Divisi"], $post["NmPlg"]);
			if ($GetDealers["result"]=="sukses") {
				$dealers = $GetDealers["data"];
				if (count($dealers)>0){
					echo json_encode(array("result"=>"sukses", "data"=>$dealers, "ket"=>""));	
				} else {
					echo json_encode(array("result"=>"gagal", "data"=>array(), "ket"=>"Tidak Berhasil Mengambil Data Credit Limit Group"));	
				}
			} else {
				echo json_encode($GetDealers);
			}
		} else {
			echo json_encode(array("result"=>"gagal", "data"=>array(), "ket"=>'Kode Pelanggan/Divisi Belum Diisi'));
		}    	
    }

    public function ProsesCreditLimitGroup($KdPlg, $WilPlg, $Divisi, $NmPlg)
    {
    	$dealers = array();
        $domainUrl = $_SESSION['conn']->AlamatWebService;
        if ($WilPlg=="MODERN OUTLET" || $WilPlg=="DM") {
        	$dbHQ = $this->MasterDbModel->getBhaktiPusat();
        	$domainUrl = $dbHQ->AlamatWebService;
        }
        if ($this->test_mode==true) $domainUrl = HO;
        
        $domainUrl.= API_BKT."/"; 
        $url = $domainUrl."MasterDealer/GetMarkers?api=APITES&kdplg=".urlencode($KdPlg);
        $HTTPRequest = HttpGetRequest_Ajax($url, $domainUrl, "Ambil Data Marker", 60000);
        // die($url);
        if ($HTTPRequest["connected"]==false) {
			return array("result"=>"gagal", "data"=>array(), "ket"=>$HTTPRequest["err"]);
        } else {
			$GetMarkers = json_decode($HTTPRequest["result"], true);
			// die(json_encode($GetMarkers));
			if ($GetMarkers["result"]=="sukses") {
				$dataMarker = $GetMarkers["data"];

				for ($i=0;$i<count($dataMarker);$i++) {
					if ($dataMarker[$i]["KD_LOKASI"]=="RUK") $dataMarker[$i]["KD_LOKASI"] = "DMI";
					$GetUrl = $this->MasterDbModel->getByBranchId($dataMarker[$i]["KD_LOKASI"]);
					//die(json_encode($GetUrl));
					if ($GetUrl!=null) {
						set_time_limit(60);
						$domainUrl = $GetUrl->AlamatWebService;
						if ($this->test_mode==true) $domainUrl = HO;
						$domainUrl.= API_BKT."/";
						$result = $this->GetCreditLimitDealer($domainUrl, $dataMarker[$i]["KD_PLG"], $Divisi, $dataMarker[$i]["NM_PLG"]);
						if ($result["result"]=="gagal") {
							return $result;
						} else {
							array_push($dealers, $result["data"]);
						}
				    } else {
		    			return array("result"=>"gagal", "data"=>array(), "ket"=>"Tidak Berhasil Mengambil Detail Koneksi Untuk Lokasi ".$dataMarker[$i]["KD_LOKASI"]);
				    }
				}
			} else {
				$result = $this->GetCreditLimitDealer($domainUrl, $KdPlg, $Divisi, $NmPlg);
				if ($result["result"]=="sukses") {
					array_push($dealers, $result["data"]);
				} else {
					return $result;
				}
		    }
		}
		// die(json_encode($dealers));
		return array("result"=>"sukses", "data"=>$dealers, "ket"=>"");
	    // return $dealers;
    }

    public function RequestCL()
    {
    	$dbHQ = $this->MasterDbModel->getBhaktiPusat();

		$post = $this->PopulatePost();
		$params = array();
		$params["Divisi"] = $this->input->post("selDiv");
		$params["KdPlg"] = $this->input->post("txtKodePlg");
		$params["NmPlg"] = $this->input->post("txtNamaPlg");
		$params["AlmPlg"] = $this->input->post("txtAlamatPlg");
		$params["Wilayah"] = $this->input->post("txtWilayahPlg");
		$params["Marking"] = $this->input->post("txtMarking");
		$params["Catatan"] = $this->input->post("txtCatatan");
		$params["CLPermanent"] = str_replace(",","",$this->input->post("txtLiPerma"));
		$params["CLTemporary"] = str_replace(",","",$this->input->post("txtLiTemp"));
		$params["CLMaks"] = str_replace(",","",$this->input->post("txtLiMaks"));
		$params["CLNew"] = str_replace(",","",$this->input->post("txtRequest"));
		$params["KenaikanCL"] = $params["CLNew"] - $params["CLPermanent"];
		$params["DatabaseID"] = (($params["Wilayah"]=="MODERN OUTLET")? $dbHQ->DatabaseId : $_SESSION["conn"]->DatabaseId);
		$params["BranchDB"] = $_SESSION["conn"]->BranchId;
		$params["NamaDB"] = $_SESSION["conn"]->NamaDb;
		$params["IsBass"] = false;

		$GetCLGroup = $this->ProsesCreditLimitGroup($params["KdPlg"], $params["Wilayah"], $params["Divisi"],$params["NmPlg"] );
		
		if ($GetCLGroup["result"]=="sukses") {
			$CLGroup = $GetCLGroup["data"];
			//die("CLGroup : <br>".json_encode($CLGroup));
			$CheckExistingRequest = $this->MsDealerModelOld->CheckExistingRequest("CREDIT LIMIT", $params);
			//$CheckExistingRequest = 0;

			if ($CheckExistingRequest>0) {
				redirect("MsDealer/CreditLimit?alert=".urlencode("REQUEST CREDIT LIMIT SUDAH ADA"));

			} else if ($params["KdPlg"]=="" || $params["NmPlg"]=="" || $params["AlmPlg"]=="" || $params["CLPermanent"]=="" || $params["CLNew"]=="" 
				|| $params["CLNew"]=="0" || $params["CLNew"]==$params["CLPermanent"])  {

				redirect("MsDealer/CreditLimit?alert=".urlencode("DATA TIDAK LENGKAP"));

			} else {

				// $recipients = array();
				// $allRecipients = array();
				// $mobiles = array();

				$this->approvers = array();
				$this->recipients= array();
				$this->whatsapps = array();
				
				$BrandManagers = $this->SalesManagerModel->GetBrandManagers();
				$SalesManagers = $this->SalesManagerModel->GetSalesManager();
				$GM = $this->SalesManagerModel->GetGeneralManager();
				$EmpFound = false;
				$Priority = 0;

				$user = array();
				$user["Name"] = $_SESSION["logged_in"]["username"];
				$user["UserID"] = $_SESSION["logged_in"]["employeeid"];
				$user["UserEmail"] = $_SESSION["logged_in"]["useremail"];
				$user["Email"] = $_SESSION["logged_in"]["email"];
				$user["BranchID"] = $_SESSION["logged_in"]["branch_id"];
				$user["City"] = $_SESSION["logged_in"]["city"];
				$email_content = $this->CreateEmailContent($params, $CLGroup);
				
				if ($params["Divisi"]=="SPAREPART") {

					//BEFORE 16JUN2020: CL SPAREPART<=20JT APPROVAL KACAB/KO HENGKY. >20JT APPROVAL KACAB/KO HENGKY + GM
					//16JUN2020: SPAREPART ALL CBG KIRIM ke HENGKY WIJAYA 2115
					//11NOV2020: PINDAH KE ANDRE HILMAN 3651

					//14DES2020: 
					//BASS  : JKT ke Manager Service, CBG <=100juta Kacab, >100juta Kacab+Manager Service
					//DEALER: JKT <=100juta Manager Service, > 100juta Manager Service + 1 BM
					//		  Cabang <=100juta ke Kacab, >100juta Kacab + 1 BM

					//Check BASSnya ke webAPI aja
					$url = $this->API_URL."/MasterDealer/GetDealer?api=APITES&plg=".urlencode($params["KdPlg"]);
					$GetDealer = json_decode(file_get_contents($url),true);
					if ($GetDealer["result"]=="sukses") {
						$params["IsBass"] = $GetDealer["data"]["IS_BASS"];
					}

					$Priority = 1;
					$params["ApprovalNeeded"] = 1;
					$params["Priority"] = $Priority;
					$this->approvers = array();

					if ($params["Wilayah"]=="OUTLIER" || ($params["BranchDB"]=="JKT" && $params["NamaDB"]=="JAKARTA")) {
						$this->addRecipients_Mgr("SERVICE");
					} else {
						$this->addRecipients_Kacab($params["BranchDB"], $params["NamaDB"], $user["BranchID"]);
					}
					$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $this->approvers, $user);
					$params["RequestID"] = $REQUESTID["requestid"];
					$params["ExpiryDate"] = $REQUESTID["expirydate"];

					if ($params["KenaikanCL"]>100000000) {
						$Priority += 1;
						$params["ApprovalNeeded"] = 1;
						$params["Priority"] = $Priority;
						$this->approvers = array();

						if ($params["IsBass"]==1) {
							if ($params["Wilayah"]=="OUTLIER" || ($params["BranchDB"]=="JKT" && $params["NamaDB"]=="JAKARTA")) {
							} else {
								$this->addRecipients_Mgr("SERVICE");
								$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $this->approvers, $user, $REQUESTID["requestid"]);
							}
						} else {
							$this->addRecipients_BM($params["Divisi"], $BrandManagers);
							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $this->approvers, $user, $REQUESTID["requestid"]);
						}
					}

					$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $this->recipients, $user);
					$sendWA = $this->MsDealerModelOld->ProsesWhatsapp("CREDIT LIMIT", $params, $this->whatsapps, $user, $this->whatsapp_account);

				} else if ($params["Divisi"]=="MO") {

					$Priority = 1;
					$lanjutBM = false;
					$params["ApprovalNeeded"] = 1;
					$params["Priority"] = $Priority;

					if ($params["Wilayah"]=="MODERN OUTLET" || $params["Wilayah"]=="DM") {
						$this->addRecipients_Mgr("MO");
					} else {
						$this->addRecipients_Kacab($params["BranchDB"], $params["NamaDB"], $user["BranchID"]);
					}
					$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $this->approvers, $user);
					$params["RequestID"] = $REQUESTID["requestid"];
					$params["ExpiryDate"] = $REQUESTID["expirydate"];

					// 1,000,000,000 - 2,000,000,000 KACAB + 1 BM
					if ($params["KenaikanCL"]>=1000000000 or strtoupper($params["Marking"])=="RED") {
						$Priority += 1;
						$this->approvers = array();
						$params["ApprovalNeeded"] = 1;
						$params["Priority"] = $Priority;

						$this->addRecipients_BM($params["Divisi"], $BrandManagers);
						$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $this->approvers, $user, $REQUESTID["requestid"]);
					}

					// > 2,000,000,000 KACAB + 1 BM + GM
					if ($params["KenaikanCL"]>2000000000) {
						$Priority+=1;
						$this->approvers = array();
						$params["ApprovalNeeded"] = 1;
						$params["Priority"] = $Priority;

						$this->addRecipients_GM($GM);
						$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $this->approvers, $user, $REQUESTID["requestid"]);
					}

					$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $this->recipients, $user);
					$sendWA = $this->MsDealerModelOld->ProsesWhatsapp("CREDIT LIMIT", $params, $this->whatsapps, $user, $this->whatsapp_account);

				} else {

					//07 Mar 2021: Per 1 Juli 2021, DM balik ke Prosedur Approval MO
					//Tradisional 
					// if (date("Ymd")<"20220301")
					$Priority = 1;
					$lanjutBM = false;
					$params["ApprovalNeeded"] = 1;
					$params["Priority"] = $Priority;
					$this->approvers = array();

					// 1 - 999,999,999 KACAB
					$this->addRecipients_Kacab($params["BranchDB"], $params["NamaDB"], $user["BranchID"]);
					$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $this->approvers, $user);
					// die(json_encode($REQUESTID));

					$params["RequestID"] = $REQUESTID["requestid"];
					$params["ExpiryDate"] = $REQUESTID["expirydate"];

					// 1,000,000,000 - 2,000,000,000 KACAB + 1 BM
					if ($params["KenaikanCL"]>=1000000000 or strtoupper($params["Marking"])=="RED") {
						$Priority += 1;
						$this->approvers = array();
						$params["ApprovalNeeded"] = 1;
						$params["Priority"] = $Priority;

						$this->addRecipients_BM($params["Divisi"], $BrandManagers);
						$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $this->approvers, $user, $REQUESTID["requestid"]);
					}

					// > 2,000,000,000 KACAB + 1 BM + GM
					if ($params["KenaikanCL"]>2000000000) {
						$Priority+=1;
						$this->approvers = array();
						$params["ApprovalNeeded"] = 1;
						$params["Priority"] = $Priority;

						$this->addRecipients_GM($GM);
						$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $this->approvers, $user, $REQUESTID["requestid"]);
					}

					$this->addRecipients_Kajul($params["Wilayah"]);					
					$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $this->recipients, $user);
					$sendWA = $this->MsDealerModelOld->ProsesWhatsapp("CREDIT LIMIT", $params, $this->whatsapps, $user, $this->whatsapp_account);
				}
				// die(json_encode($recipients));
				//Simpan Ke TblApproval

				$data = array();
				$db_id = $_SESSION['databaseID'];
				$API = $_SESSION["conn"]->AlamatWebService;
				if ($this->test_mode==true) $API = HO;
				$URL = $API.API_BKT."/MasterDealer/GetListDealerForCL?api=APITES";
		        $GetDealer = json_decode(file_get_contents($URL),true);
		        if ($GetDealer["result"]=="sukses"){
		        	$ListDealer = $GetDealer["data"];
					$dealers = array();
					for($i=0; $i<count($ListDealer); $i++){
						array_push($dealers, trim($ListDealer[$i]["NM_PLG"])." - ".trim($ListDealer[$i]["KD_PLG"])." - ".trim($ListDealer[$i]["WILAYAH"]));
					}
					$data["ListDealer"] = $ListDealer;
			        $data["Dealers"] = $dealers;
					if ($REQUESTID["result"]=="sukses") {
						//$data["content_html"] = '<script language="javascript">';
				        //$data["content_html"].= 'alert("REQUEST CL SAVED & EMAILED TO KACAB/MANAGER")';
				        //$data["content_html"].= '</script>';
				        $data["alert"] = "REQUEST CL SAVED & EMAILED TO KACAB/MANAGER";
					} else {
						//$data["content_html"] = '<script language="javascript">';
				        //$data["content_html"].= 'alert("'.strtoupper($REQUESTID["ket"]).'")';
				        //$data["content_html"].= '</script>';
				        $data["alert"] = strtoupper($REQUESTID["ket"]);
					}
					$this->RenderView('MsDealerCreditLimitView', $data);
			        //if (date("Ymd")>=date("Ymd", strtotime($this->newRuleDate))) {
			        //	//CL Produk Sudah Gabung
				    //    $this->RenderView('MsDealerCreditLimitView', $data);
				    //} else {
				    //    $this->RenderView('MsDealerCreditLimitViewOld', $data);			    	
				    //}
			    } else {
					$data = array();
			        $data["alert"] = "REQUEST CL SAVED & EMAILED TO KACAB/MANAGER";
			        $data["alert"].= "\nGAGAL MENGAMBIL LIST DEALER KE DB BHAKTI";
			        $this->RenderView('MsDealerCreditLimitView', $data);
			        //if (date("Ymd")>=date("Ymd", strtotime($this->newRuleDate))) {
				    //    $this->RenderView('MsDealerCreditLimitView', $data);
				    //} else {
				    //    $this->RenderView('MsDealerCreditLimitViewOld', $data);			    	
				    //}
			    }

			}
		} else {
			redirect("MsDealer/CreditLimit?alert=".urlencode($GetCLGroup["ket"]));
		}
    }

    public function CheckRecipients($data, $add_approvers=1, $add_recipients=1, $add_whatsapp=1) 
    {
    	$RECIPIENTS = $this->recipients;

		$EmpFound = false;
		for($i=0;$i<count($RECIPIENTS);$i++) {
			if ($RECIPIENTS[$i]["NAMA"]==$data["NAMA"] && $RECIPIENTS[$i]["EMAIL"]==$data["EMAIL"]) {
				$EmpFound = true;
			}
		}

		if ($EmpFound==false) {
			if ($add_approvers==1) array_push($this->approvers, $data);
			if ($add_recipients==1) array_push($this->recipients, $data);

			$MobileFound = false;
			if ($add_whatsapp==1) {
				$WA = $this->HelperModel->MobileWithCountryCode($data["WA"]);
				if ($WA!="") {
					$mobiles = $this->whatsapps;
					for($i=0;$i<count($mobiles);$i++) {
						if ($mobiles[$i]==$WA) {
							$MobileFound = true;
						}
					}
					if ($MobileFound==false) {
						array_push($this->whatsapps, $WA);
					}
				}
			}
		} 
    }

    public function addRecipients_Kajul($Wilayah="")
    {
		$Kajul = $this->SalesManagerModel->GetKajul($Wilayah);
		if ($Kajul!=null) {
			$USER = array();
			$USER["NAMA"] = $Kajul->employee_name;
			$USER["EMAIL"] = (($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address);
			$USER["USEREMAIL"] = $Kajul->useremail;
			$USER["WA"] = "";
			$this->CheckRecipients($USER, 0, 1, 0);				
		} 			
    }


    public function addRecipients_Kacab($BranchDB="", $NamaDB="", $BranchID="")
    {
		$USER = array();
    	if ($BranchDB=="JKT" && $NamaDB=="JAKARTA") {
			$SalesManagers = $this->SalesManagerModel->GetSalesManager();
			
			foreach($SalesManagers as $m) {
				$USER["NAMA"] = $m->employee_name;
				$USER["EMAIL"] = (($m->email_address==null)?$m->email:$m->email_address);
				$USER["USEREMAIL"] = $m->useremail;
				$USER["WA"] = $m->mobile;
				$this->CheckRecipients($USER);
			}
		} else if ($BranchDB=="JKT" && ($NamaDB=="BOGOR" || $NamaDB=="KARAWANG")) {
			//Khusus BOGOR dan KARAWANG under JAKARTA dihardcode kirim request ke FANDY
			$zen = API_ZEN."/ZenAPI/GetEmployee?userid=2661";
			$GetKacab = json_decode(file_get_contents($zen), true);
			if ($GetKacab["result"]=="sukses") {
				$Employee = $GetKacab["data"];

				$USER["NAMA"] = $Employee["NAME"];
				$USER["EMAIL"] = $Employee["EMAIL"];
				$USER["USEREMAIL"] = $Employee["EMAIL"];
				$USER["WA"] = $Employee["WHATSAPP"];
				$this->CheckRecipients($USER);				
			} 			
		} else {
			$zen = API_ZEN."/ZenAPI/GetBranch?branch=".urlencode($BranchID);
			$GetBranch = json_decode(file_get_contents($zen), true);
			if ($GetBranch["result"]=="sukses") {
				$BranchHead = $GetBranch["data"]["BRANCHHEAD"];
				if ($BranchHead!="") {
					$zen = API_ZEN."/ZenAPI/GetEmployee?userid=".urlencode($BranchHead);
					$GetKacab = json_decode(file_get_contents($zen), true);
					if ($GetKacab["result"]=="sukses") {
						$Employee = $GetKacab["data"];
						$USER["NAMA"] = $Employee["NAME"];
						$USER["EMAIL"] = $Employee["EMAIL"];
						$USER["USEREMAIL"] = $Employee["EMAIL"];
						$USER["WA"] = $Employee["WHATSAPP"];
						$this->CheckRecipients($USER);
					}
				}
			}
		}
    }

    public function addRecipients_BM($Divisi="", $BrandManagers)
    {
		$USER = array();

		if ($Divisi=="CO&SANITARY") {
			foreach($BrandManagers as $m) {
				if (htmlspecialchars_decode($m->division)=="CO&SANITARY") {
					$USER["NAMA"] = $m->employee_name;
					$USER["EMAIL"] = (($m->email_address==null)?$m->email:$m->email_address);
					$USER["USEREMAIL"] = $m->useremail;
					$USER["WA"] = $m->mobile;
					$this->CheckRecipients($USER);
				}
			}
		} else {				
			foreach($BrandManagers as $m) {
				$USER["NAMA"] = $m->employee_name;
				$USER["EMAIL"] = (($m->email_address==null)?$m->email:$m->email_address);
				$USER["USEREMAIL"] = $m->useremail;
				$USER["WA"] = $m->mobile;
				$this->CheckRecipients($USER);
			}
		}
    }

    public function addRecipients_GM($GM)
    {
		$USER = array();
		foreach($GM as $m) {
			$USER["NAMA"] = $m->employee_name;
			$USER["EMAIL"] = (($m->email_address==null)?$m->email:$m->email_address);
			$USER["USEREMAIL"] = $m->useremail;
			$USER["WA"] = $m->mobile;
			$this->CheckRecipients($USER);
		}
    }

    public function addRecipients_Mgr($x="")
    {
    	$USER = array();
		
		if ($x=="SERVICE") {
			//3651 : Andre Hilman
			$zen = API_ZEN."/ZenAPI/GetEmployee?userid=3651";
			$GetKacab = json_decode(file_get_contents($zen), true);
			if ($GetKacab["result"]=="sukses") {
				$Employee = $GetKacab["data"];
				
				$USER["NAMA"] = $Employee["NAME"];
				$USER["EMAIL"] = $Employee["EMAIL"];
				$USER["USEREMAIL"] = $Employee["EMAIL"];
				$USER["WA"] = $Employee["WHATSAPP"];
				$this->CheckRecipients($USER);
			}
		} else if ($x=="MO") {
			$Kajul = $this->SalesManagerModel->GetKajul("MODERN OUTLET");
			if ($Kajul!=null) {
				$USER = array();
				$USER["NAMA"] = $Kajul->employee_name;
				$USER["EMAIL"] = (($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address);
				$USER["USEREMAIL"] = $Kajul->useremail;
				$USER["WA"] = "";
				$this->CheckRecipients($USER);	
			}
		}
    }						

    public function ResendRequest()
    {
		$RequestNo = urldecode($this->input->get("id"));
		$Type = urldecode($this->input->get("type"));
		$RequestType="";
		$StrType = "";

		if (strtoupper($Type)=="CL") {

			$RequestType = "CREDIT LIMIT";
			$StrType = "REQUEST CREDIT LIMIT";

		} else if (strtoupper($Type)=="CBDOFF") {

			$RequestType = "CBD OFF";
			$StrType = "REQUEST PENONAKTIFAN STATUS CBD";

		}

    	$REQ = $this->MsDealerModelOld->GetRequest($RequestType, $RequestNo);

		// ApprovalType RequestNo RequestBy RequestDate
		// RequestByName RequestByEmail ApprovedBy ApprovedByName
		// ApprovedByEmail ApprovedDate ApprovalStatus ApprovalNote
		// "Kode Pelanggan" AddInfo1 AddInfo1Value "Divisi" AddInfo2 AddInfo2Value
		// "CL Baru" AddInfo3 AddInfo3Value "HTML Content" AddInfo4 AddInfo4Value
		// "Database ID" AddInfo5 AddInfo5Value "Nama Pelanggan" AddInfo6 AddInfo6Value
		// ApprovalNeeded Priority ExpiryDate BhaktiFlag BhaktiProcessDate  
		// "CL Lama" AddInfo7 AddInfo7Value "Catatan" AddInfo8 AddInfo8Value
		// "Wilayah" AddInfo9 AddInfo9Value "Penerima Email (termasuk yg ga bisa approve)" AddInfo10 AddInfo10Value
		// IsCancelled CancelledBy CancelledByName CancelledDate CancelledNote CancelledByEmail
		
		$params = array();
		$params["RequestID"] = $RequestNo;
		$params["ExpiryDate"] = date("Y-m-d", strtotime($REQ[0]->ExpiryDate));
		$params["Divisi"] = $REQ[0]->AddInfo2Value;
		$params["KdPlg"] = $REQ[0]->AddInfo1Value;
		$params["NmPlg"] = $REQ[0]->AddInfo6Value;
		$params["AlmPlg"] = "";
		$params["Wilayah"] = $REQ[0]->AddInfo9Value;
		$params["Catatan"] = $REQ[0]->AddInfo8Value;
		$params["CLPermanent"] = $REQ[0]->AddInfo7Value;
		$params["CLTemporary"] = $REQ[0]->AddInfo11Value;
		$params["CLMaks"] = 0;
		$params["CLNew"] = $REQ[0]->AddInfo3Value;
		$params["KenaikanCL"] = $REQ[0]->AddInfo12Value;
		$params["DatabaseID"] = $REQ[0]->AddInfo5Value;
		$params["BranchDB"] = 0;
		$params["NamaDB"] = "";
		$params["IsBass"] = false;

		$email_content = json_decode($REQ[0]->AddInfo4Value);

		$user = array();
		$user["Name"] = $_SESSION["logged_in"]["username"];
		$user["UserID"] = $_SESSION["logged_in"]["employeeid"];
		$user["UserEmail"] = $_SESSION["logged_in"]["useremail"];
		$user["Email"] = $_SESSION["logged_in"]["email"];
		$user["BranchID"] = $_SESSION["logged_in"]["branch_id"];
		$user["City"] = $_SESSION["logged_in"]["city"];

		$recipients = array();
		$allRecipients = array();
		$mobiles = array();

		foreach($REQ as $r) {
			if ($r->ApprovalStatus=="UNPROCESSED") {
				array_push($recipients, array("NAMA"=>$r->ApprovedByName, "EMAIL"=>$r->ApprovedByEmail, "USEREMAIL"=>$r->ApprovedBy));
			}
		}

		$sendEmail=0;
		// $sendEmail = $this->MsDealerModelOld->EmailRequest($RequestType, $params, $email_content, $recipients, $user, 1);
		$sendWA = $this->MsDealerModelOld->ProsesWhatsapp($RequestType, $params, $mobiles, $user);
		// function ProsesWhatsapp($RequestType="CREDIT LIMIT", $params, $mobiles, $user, $waAccount="OTHER")
		// echo((string)$sendEmail);
		// echo("<br>");

		$data = array();

  		if ($sendEmail > 0 || $sendWA=="SUKSES") {
  			$data["alert"] = "REQUEST CL TELAH DIKIRIM ULANG";
  		} else {
  			$data["alert"] = "REQUEST CL GAGAL DIKIRIM ULANG";
  		}
  		$this->RenderView("AlertView", $data);
    }

	public function CreateEmailContent($params, $CLGroup)
	{
		$DIVISI = (($params["Divisi"]=="MO")?"MODERN OUTLET":$params["Divisi"]);

		$email_content = "Divisi : <b>".$DIVISI."</b><br>";
		$email_content.= "Nama : <b>".$params["NmPlg"]." - ".$params["KdPlg"]."</b><br>";
		$email_content.= "Alamat : ".$params["AlmPlg"]."<br>";
		$email_content.= "CL Permanent : <b>".number_format($params["CLPermanent"])."</b><br>";
		$email_content.= "CL Temporary : ".number_format($params["CLTemporary"])."<br>";
		if ($params["CLMaks"]!="UNLIMITED")  {
		$email_content.= "CL Maks Yang Diperbolehkan : ".number_format($params["CLMaks"])."<br>";
		}
		$email_content.= "CL Direquest : <b>".number_format($params["CLNew"])."</b><br>";
		$email_content.= "Kenaikan CL : <b>".number_format($params["KenaikanCL"])."</b><br>";
		$email_content.= "Catatan : <br><b>".(($params["Catatan"]=="")?"-":$params["Catatan"])."</b><br><br>";

		$email_content.= "<u>Data Toko Lengkap Satu Pemilik</u><br>";
		$tbl = "";
		if (count($CLGroup)>0) {
			$tbl.= "<table>";
			$tbl.= "	<tr>";
			$tbl.= "		<th width='5%' style='border:1px solid #ccc; padding:3px;'>NO</th>";
			//$tbl.= "		<th class='hideOnMobile' style='border:1px solid #ccc; padding:3px;'>DEALER</th>";
			//$tbl.= "		<th class='hideOnMobile' style='border:1px solid #ccc; padding:3px;'>CREDIT LIMIT</th>";
			//$tbl.= "		<th class='hideOnMobile' style='border:1px solid #ccc; padding:3px;'>PIUTANG</th>";
			//$tbl.= "		<th class='hideOnMobile' style='border:1px solid #ccc; padding:3px;'>FAKTUR TERLAMA</th>";
			//$tbl.= "		<th class='hideOnMobile' style='border:1px solid #ccc; padding:3px;'>JT FAKTUR TERLAMA</th>";
			$tbl.= "		<th width='90%' style='border:1px solid #ccc; padding:3px;'>DATA DEALER</th>";
			$tbl.= "		<th width='5%' style='border:1px solid #ccc; padding:3px;'></th>";
			$tbl.= "	</tr>";
		}
		$NO = 0;
		for($i=0;$i<count($CLGroup);$i++) {
			//die(json_encode($CLGroup[$i]));
			$NO+=1;
			$PLG = $CLGroup[$i]["NM_PLG"]."<br>".$CLGroup[$i]["KD_PLG"]."<br>".$CLGroup[$i]["KD_LOKASI"];
			$CL = "Permanent: ".number_format($CLGroup[$i]["CL_PERMANENT"])."<br>Temporary: ".number_format($CLGroup[$i]["CL_TEMPORARY"])."";
			$PIUTANG = number_format($CLGroup[$i]["PIUTANG"])."";
			$FK_TERLAMA = $CLGroup[$i]["TGL_FAKTUR_TERLAMA"]."";
			$JT_TERLAMA = $CLGroup[$i]["JT_FAKTUR_TERLAMA"]."";
			$TELAT = $CLGroup[$i]["MAKS_TELAT"];

			$MOBILE = $PLG."<br>";
			$MOBILE.= $CL."<br>";
			$MOBILE.= "PIUTANG: ".$PIUTANG."<br>";
			$MOBILE.= "FK TERLAMA: ".$FK_TERLAMA."<br>";
			$MOBILE.= "JT TERLAMA: ".$JT_TERLAMA."<br>&nbsp;";
			$STYLE = "background-color:".(($TELAT>7)?"yellow":"green").";";

			$tbl.= "	<tr>";
			$tbl.= "		<td style='border:1px solid #ccc; padding:3px;'>".$NO."</td>";
			//$tbl.= "		<td class='hideOnMobile' style='border:1px solid #ccc; padding:3px;'>".$PLG."</td>";
			//$tbl.= "		<td class='hideOnMobile' style='border:1px solid #ccc; padding:3px;'>".$CL."</td>";
			//$tbl.= "		<td class='hideOnMobile' style='border:1px solid #ccc; padding:3px;'>".$PIUTANG."</td>";
			//$tbl.= "		<td class='hideOnMobile' style='border:1px solid #ccc; padding:3px;'>".$FK_TERLAMA."</td>";
			//$tbl.= "		<td class='hideOnMobile' style='border:1px solid #ccc; padding:3px;'>".$JT_TERLAMA."</td>";
			$tbl.= "		<td style='border:1px solid #ccc; padding:3px;'>".$MOBILE."</td>";
			$tbl.= "		<td style='border:1px solid #ccc; padding:3px;".$STYLE."'></td>";
			$tbl.= "	</tr>";
		}
		if ($tbl!="") $tbl.= "</table>";

		$email_content.= $tbl;
		return $email_content;
	}

	public function ProcessRequestCL()
	{
		$RequestNo = urldecode($this->input->get("id"));
		$RequestType = "CREDIT LIMIT";
		$Alert = urldecode($this->input->get("alert"));

		if ($this->input->get("viewonly") !== "yes") {
			$ViewOnly = false;
		} else {
			$ViewOnly = true;
		}

		$data = array();
		$data["RequestNo"] = $RequestNo;
		$data["RequestType"] = $RequestType;
		$data["alert"] = $Alert;

		$StrType = "kenaikan Credit Limit";
		$GetRequest = $this->MsDealerModelOld->GetRequestForProcess($RequestType, $RequestNo, "");
		//die(json_encode($GetRequest));
		if ($GetRequest["result"]=="sukses") {
			$REQ = $GetRequest["data"];
			$content_html = $REQ->RequestByName." mengajukan permohonan ".$StrType." sebagai berikut :<br><br>";
			$content_html.=  json_decode($REQ->AddInfo4Value);
			$Requests = $GetRequest["req"];
			$NO = 0;

			$request_table = "<br>";
			$request_table.= "<table>";
			$request_table.= "	<tr>";
			$request_table.= "		<th style='border:1px solid #ccc; padding:3px;'>No</th>";
			$request_table.= "		<th style='border:1px solid #ccc; padding:3px;'>Nama</th>";
			$request_table.= "		<th style='border:1px solid #ccc; padding:3px;'>Status</th>";
			$request_table.= "		<th style='border:1px solid #ccc; padding:3px;'>Tgl Approve/Reject</th>";
			$request_table.= "		<th style='border:1px solid #ccc; padding:3px;'>Catatan</th>";
			$request_table.= "	</tr>";

			foreach($Requests as $r) {
				$NO+=1;
				$request_table.= "	<tr>";
				$request_table.= "		<td style='border:1px solid #ccc; padding:3px;'>".$NO."</td>";
				$request_table.= "		<td style='border:1px solid #ccc; padding:3px;'>".$r->ApprovedByName."</td>";
				$request_table.= "		<td style='border:1px solid #ccc; padding:3px;'>".$r->ApprovalStatus."</td>";
				$request_table.= "		<td style='border:1px solid #ccc; padding:3px;'>".(($r->ApprovalStatus=="UNPROCESSED")? "-" : date("d-M-Y H:i:s",strtotime($r->ApprovedDate)))."</td>";
				$request_table.= "		<td style='border:1px solid #ccc; padding:3px;'>".$r->ApprovalNote."</td>";
				$request_table.= "	</tr>";				
			}
			$request_table.="</table>";

			$request_content = "";
			if ($GetRequest["status"]=="CANCELLED") {
				$content_html .= "<br><font color='red'><b>Request ".$StrType." Ini Telah Dicancel oleh ".$REQ->CancelledByName." Pada ".date("d-M-Y H:i:s", strtotime($REQ->CancelledDate))."</b></font>";
			} else if ($GetRequest["status"]=="PROCESSED") {
				if ($REQ->ApprovalStatus=="APPROVED") {
					$content_html .= "<br><b>Request ".$StrType." Ini Telah DiApprove Pada ".date("d-M-Y H:i:s", strtotime($REQ->ApprovedDate))."</b>";
				} else if ($REQ->ApprovalStatus=="REJECTED") {
					$content_html .= "<br><b>Request ".$StrType." Ini Telah DiReject Pada ".date("d-M-Y H:i:s", strtotime($REQ->ApprovedDate))."</b>";
				} else {
					$content_html .= "<br><b>Request ".$StrType." Ini Sudah Tidak Membutuhkan Respon</b>";
				}
			} else if ($GetRequest["status"]=="APPROVED") {
				$content_html .= "<br><font color='green'><b>Request ".$StrType." Ini Telah Mendapat Approval</b></font>";
			} else if ($GetRequest["status"]=="REJECTED") {
				$content_html .= "<br><font color='red'><b>Request ".$StrType." Ini Telah DiReject</b></font>";
			} else if ($GetRequest["status"]=="EXPIRED") {
				$content_html .= "<br><font color='darkgrey'><b>Request ".$StrType." Ini Telah Kadaluarsa</b></font>";
			} else {

			}
			$data["data"] = $GetRequest;
			$data["viewOnly"] = $ViewOnly;
			$data["approver"]= $this->MsDealerModelOld->GetRequestApproverList($RequestType, $RequestNo);
			$data["content_html"] = $content_html;
			$data["request_table"] = $request_table;

	        $this->SetTemplate('template/notemplate');
	        $this->load->view("MsDealerRequestView", $data);
		} else {
			$data = array();
			$data["content_html"] = '<script language="javascript">';
			if ($GetRequest["status"]=="INVALID REQUEST") {
		        $data["content_html"].= 'alert("INVALID REQUEST: REQUEST '.strtoupper($StrType).' TIDAK DITEMUKAN")';
		    } else {
		    	$data["content_html"].= 'alert("INVALID REQUEST: ANDA TIDAK MEMILIKI AKSES UNTUK REQUEST '.strtoupper($StrType).' INI")';
		    }
	        $data["content_html"].= '</script>';
	        $data["content_html"].= "<script>window.close();</script>";

	        $this->SetTemplate('template/notemplate');
	        $this->load->view("MsDealerRequestView", $data);		
		}
	}

	public function CancelRequest()
	{
		$RequestNo = urldecode($this->input->get("id"));
		$Type = urldecode($this->input->get("type"));
		$Alert = urldecode($this->input->get("alert"));
		if ($this->input->get("viewonly") !== "yes") {
			$ViewOnly = false;
		} else {
			$ViewOnly = true;
		}

		$RequestType = "";
		$StrType = "";

		if (strtoupper($Type)=="CL") {

			$RequestType = "CREDIT LIMIT";
			$StrType = "REQUEST CREDIT LIMIT";

		} else if (strtoupper($Type)=="CBDOFF") {

			$RequestType = "CBD OFF";
			$StrType = "REQUEST PENONAKTIFAN STATUS CBD";

		}

		
		$data = array();
		$data["RequestNo"] = $RequestNo;
		$data["RequestType"] = $RequestType;
		$data["alert"] = $Alert;

		$GetRequest = $this->MsDealerModelOld->GetRequestForProcess($RequestType, $RequestNo, "");

		//echo(json_encode($GetRequest));
		//echo("<br>");

		if ($GetRequest["result"]=="sukses") {
			if ($GetRequest["status"]=="CANCELLED") {
				//echo("CANCELLED<br>");
				//echo($StrType."<br>");	
				$data["content_html"] = '<script language="javascript">';
		        $data["content_html"].= 'alert("'.$StrType.' SUDAH PERNAH DICANCEL")';
		        $data["content_html"].= '</script>';
		        $data["content_html"].= "<script>window.close();</script>";
		        //echo($data["content_html"]."<br>");
			} else if ($GetRequest["status"]=="APPROVED") {
				$data["content_html"] = '<script language="javascript">';
		        $data["content_html"].= 'alert("'.$StrType.' SUDAH SELESAI DIAPPROVE")';
		        $data["content_html"].= '</script>';
		        $data["content_html"].= "<script>window.close();</script>";
			} else if ($GetRequest["status"]=="REJECTED") {
				$data["content_html"] = '<script language="javascript">';
		        $data["content_html"].= 'alert("'.$StrType.' SUDAH DIREJECT")';
		        $data["content_html"].= '</script>';
		        $data["content_html"].= "<script>window.close();</script>";
			} else if ($GetRequest["status"]=="EXPIRED") {
				$data["content_html"] = '<script language="javascript">';
		        $data["content_html"].= 'alert("'.$StrType.' SUDAH EXPIRED")';
		        $data["content_html"].= '</script>';
		        $data["content_html"].= "<script>window.close();</script>";
			} else {
				$CancelRequest = $this->MsDealerModelOld->CancelRequest($RequestType, $RequestNo);
				// echo(json_encode($CancelRequest)."<br><br>");

				if ($CancelRequest["result"]=="sukses") {
					//PANGGIL REQUEST SEKALI LAGI
					// echo("Panggil Request<br>");
					$GetRequest2 = $this->MsDealerModelOld->GetRequestForProcess($RequestType, $RequestNo, "");
					$REQ = $GetRequest2["data"];
					// echo("Cancelling Request<br>");
					// echo(json_encode($REQ)."<br><br>");
					
					$email_content = $this->CreateCancellationEmailContent($RequestType, $REQ);
					// echo($email_content."<br><br>");
					$doEmail = $this->MsDealerModelOld->EmailCancellationRequest($RequestType, $REQ, $email_content);

					$data["content_html"] = '<script language="javascript">';
			        $data["content_html"].= 'alert("'.$StrType.' BERHASIL DICANCEL")';
			        $data["content_html"].= '</script>';
			        $data["content_html"].= "<script>window.close();</script>";					
				} else if ($CancelRequest["status"]=="INVALID REQUEST") {
					$data["content_html"] = '<script language="javascript">';
			        $data["content_html"].= 'alert("'.$StrType.' TIDAK DITEMUKAN")';
			        $data["content_html"].= '</script>';
			        $data["content_html"].= "<script>window.close();</script>";					
				} else {
					$data["content_html"] = '<script language="javascript">';
			        $data["content_html"].= 'alert("'.$StrType.' TIDAK BERHASIL DICANCEL")';
			        $data["content_html"].= '</script>';
			        $data["content_html"].= "<script>window.close();</script>";					
				}
			}
			//die(json_encode($data));
	        $this->SetTemplate('template/notemplate');
	        $this->load->view("MsDealerRequestView", $data);
		} else {
			$data = array();
			$data["content_html"] = '<script language="javascript">';
			if ($GetRequest["status"]=="INVALID REQUEST") {
		        $data["content_html"].= 'alert("'.$StrType.' TIDAK DITEMUKAN")';
		    } else {
		    	$data["content_html"].= 'alert("ANDA TIDAK MEMILIKI AKSES UNTUK '.$StrType.' INI")';
		    }
	        $data["content_html"].= '</script>';
	        $data["content_html"].= "<script>window.close();</script>";
	        $this->SetTemplate('template/notemplate');
	        $this->load->view("MsDealerRequestView", $data);		
		}
	}

	public function CreateCancellationEmailContent($REQTYPE="CREDIT LIMIT", $REQ)
	{
		$Requests = $this->MsDealerModelOld->GetRequest($REQTYPE, $REQ->RequestNo);
		$NO = 0;

		$TITLE = "";
		if ($REQTYPE=="CREDIT LIMIT") {
			$TITLE = "Kenaikan Credit Limit";
		} else {
			$TITLE = "Penonaktifan Status CBD";
		}

		$email_content = "Request ".$TITLE." berikut telah <b>DIBATALKAN</b><br><br>";
		$email_content.= "Oleh : <b>".$REQ->CancelledByName."</b><br>";
		$email_content.= "Pada : <b>".date("d-M-Y H:i:s",strtotime($REQ->CancelledDate))."</b><br>";
		$email_content.= "=========================================================<br><br>";
		$email_content.= json_decode($REQ->AddInfo4Value);
		
		$stylesheet = "<style>";
		$stylesheet.= "	td, th { border:1px solid #ccc; padding:3px; } ";
		$stylesheet.= "</style>";

		$email_content.= $stylesheet;
		return $email_content;
	}

	public function ProcessRequest()
	{
		$RequestNo = urldecode($this->input->get("id"));
		$Type = urldecode($this->input->get("type"));
		$Alert = urldecode($this->input->get("alert"));
		if ($this->input->get("viewonly") !== "yes") {
			$ViewOnly = false;
		} else {
			$ViewOnly = true;
		}

		$RequestType = "";
		$StrType = "";

		if (strtoupper($Type)=="CL") {

			$RequestType = "CREDIT LIMIT";
			$StrType = "kenaikan Credit Limit";

		} else if (strtoupper($Type)=="CBDOFF") {

			$RequestType = "CBD OFF";
			$StrType = "penonaktifan Status CBD";

		} else if (strtoupper($Type)=="CBDON") {

			$RequestType = "CBD ON";
			$StrType = "pengaktifan Status CBD";

		}

		$data = array();
		$data["RequestNo"] = $RequestNo;
		$data["RequestType"] = $RequestType;
		$data["alert"] = $Alert;

		$GetRequest = $this->MsDealerModelOld->GetRequestForProcess($RequestType, $RequestNo, "");
		//die(json_encode($GetRequest));
		if ($GetRequest["result"]=="sukses") {
			$REQ = $GetRequest["data"];
			$content_html = $REQ->RequestByName." mengajukan permohonan ".$StrType." sebagai berikut :<br><br>";
			$content_html.=  json_decode($REQ->AddInfo4Value);
			$Requests = $GetRequest["req"];
			$NO = 0;

			$request_table = "<br>";
			$request_table.= "<table>";
			$request_table.= "	<tr>";
			$request_table.= "		<th style='border:1px solid #ccc; padding:3px;'>No</th>";
			$request_table.= "		<th style='border:1px solid #ccc; padding:3px;'>Nama</th>";
			$request_table.= "		<th style='border:1px solid #ccc; padding:3px;'>Status</th>";
			$request_table.= "		<th style='border:1px solid #ccc; padding:3px;'>Tgl Approve/Reject</th>";
			$request_table.= "		<th style='border:1px solid #ccc; padding:3px;'>Catatan</th>";
			$request_table.= "	</tr>";

			foreach($Requests as $r) {
				$NO+=1;
				$request_table.= "	<tr>";
				$request_table.= "		<td style='border:1px solid #ccc; padding:3px;'>".$NO."</td>";
				$request_table.= "		<td style='border:1px solid #ccc; padding:3px;'>".$r->ApprovedByName."</td>";
				$request_table.= "		<td style='border:1px solid #ccc; padding:3px;'>".$r->ApprovalStatus."</td>";
				$request_table.= "		<td style='border:1px solid #ccc; padding:3px;'>".(($r->ApprovalStatus=="UNPROCESSED")? "-" : date("d-M-Y H:i:s",strtotime($r->ApprovedDate)))."</td>";
				$request_table.= "		<td style='border:1px solid #ccc; padding:3px;'>".$r->ApprovalNote."</td>";
				$request_table.= "	</tr>";				
			}
			$request_table.="</table>";

			$request_content = "";
			if ($GetRequest["status"]=="CANCELLED") {
				$content_html .= "<br><font color='red'><b>Request ".$StrType." Ini Telah Dicancel oleh ".$REQ->CancelledByName." Pada ".date("d-M-Y H:i:s", strtotime($REQ->CancelledDate))."</b></font>";
			} else if ($GetRequest["status"]=="PROCESSED") {
				if ($REQ->ApprovalStatus=="APPROVED") {
					$content_html .= "<br><b>Request ".$StrType." Ini Telah DiApprove Pada ".date("d-M-Y H:i:s", strtotime($REQ->ApprovedDate))."</b>";
				} else if ($REQ->ApprovalStatus=="REJECTED") {
					$content_html .= "<br><b>Request ".$StrType." Ini Telah DiReject Pada ".date("d-M-Y H:i:s", strtotime($REQ->ApprovedDate))."</b>";
				} else {
					$content_html .= "<br><b>Request ".$StrType." Ini Sudah Tidak Membutuhkan Respon</b>";
				}
			} else if ($GetRequest["status"]=="APPROVED") {
				$content_html .= "<br><font color='green'><b>Request ".$StrType." Ini Telah Mendapat Approval</b></font>";
			} else if ($GetRequest["status"]=="REJECTED") {
				$content_html .= "<br><font color='red'><b>Request ".$StrType." Ini Telah DiReject</b></font>";
			} else if ($GetRequest["status"]=="EXPIRED") {
				$content_html .= "<br><font color='darkgrey'><b>Request ".$StrType." Ini Telah Kadaluarsa</b></font>";
			} else {

			}
			$data["data"] = $GetRequest;
			$data["viewOnly"] = $ViewOnly;
			$data["approver"]= $this->MsDealerModelOld->GetRequestApproverList($RequestType, $RequestNo);
			$data["content_html"] = $content_html;
			$data["request_table"] = $request_table;
	        //$this->load->view("MsDealerRequestCLView", $data);
	        $this->SetTemplate('template/notemplate');
	        $this->load->view("MsDealerRequestView", $data);
		} else {
			$data = array();
			$data["content_html"] = '<script language="javascript">';
			if ($GetRequest["status"]=="INVALID REQUEST") {
		        $data["content_html"].= 'alert("INVALID REQUEST: REQUEST '.strtoupper($StrType).' TIDAK DITEMUKAN")';
		    } else {
		    	$data["content_html"].= 'alert("INVALID REQUEST: ANDA TIDAK MEMILIKI AKSES UNTUK REQUEST '.strtoupper($StrType).' INI")';
		    }
	        $data["content_html"].= '</script>';
	        $data["content_html"].= "<script>window.close();</script>";
	        $this->SetTemplate('template/notemplate');
	        $this->load->view("MsDealerRequestView", $data);		
		}
	}

	public function ApproveRejectRequest(){
		$post = $this->PopulatePost();
		$RequestNo = $post["TxtRequestNo"];
		$RequestType = $post["TxtRequestType"];
		$UserEmail = $post["TxtUserEmail"];
		$UserPwd = $post["TxtUserPwd"];
		$Note = $post["TxtNote"];
		//die($UserPwd);
		$tipe = "";
		if ($RequestType=="CREDIT LIMIT") {
			$tipe = "cl";
		} else if ($RequestType=="CBD OFF") {
			$tipe = "cbdoff";
		} else if ($RequestType=="CBD ON") {
			$tipe = "cbdon";
		}

		if ($UserEmail=="-" || $UserEmail=="") {
			redirect("MsDealer/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&alert=".urlencode("USER BELUM DIPILIH"));
		} else if ($UserPwd!="") {
			$zen = API_ZEN."/ZenAPI/CheckLogin?user=".urlencode($UserEmail)."&pwd=".urlencode(md5($UserPwd));
			$res = json_decode(file_get_contents($zen, true));
			if ($res->result == "SUKSES" || $this->test_mode==true){
				if (isset($_POST["btnApprove"])) {
					$doApprove = $this->MsDealerModelOld->ApproveRequest($RequestType, $RequestNo, $UserEmail, $Note);
					if ($doApprove["result"]=="sukses") {
						if ($doApprove["complete"]==true) {
							$REQ = $doApprove["req"];
							//die(json_encode($REQ));
							$DatabaseID = $doApprove["databaseID"];
							//die($DatabaseID);
							$DB = $this->MasterDbModel->get($DatabaseID);
							if ($DB!=null) {
								$URL = $DB->AlamatWebService;
								$connected = false;

								$ch = curl_init($URL.API_BKT."/VirtualAccount2/TestConnection");
								curl_setopt($ch, CURLOPT_TIMEOUT, 2);
								curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
								$response = curl_exec($ch);
								if ($response === false) {
									//die(json_encode($response));
								    $info = curl_getinfo($ch);
								    if ($info['http_code'] === 0) {
								        // timeout
								        $connected=false;
								    }
								} else {
									$connected=true;
								}
								//die(($connected)?"connected":"not connected");

								if ($connected) {
									if ($RequestType=="CREDIT LIMIT") {
										$URL = $URL.API_BKT."/MasterDealer/ChangeLimitDealer?svr=".urlencode(trim($DB->Server));
										$URL.= "&kdplg=".urlencode(trim($REQ->AddInfo1Value))."&div=".urlencode(trim($REQ->AddInfo2Value));
										$URL.= "&newcl=".urlencode($REQ->AddInfo3Value)."&db=".urlencode(trim($DB->Database));
										$URL.= "&uid=".urlencode(trim(SQL_UID))."&pwd=".urlencode(trim(SQL_PWD));
										$URL.= "&reqby=".urlencode($REQ->RequestByName)."&reqdate=".urlencode(trim(date("Y-m-d",strtotime($REQ->RequestDate))));
										$URL.= "&appby=".urlencode($REQ->ApprovedByName);


										//die($URL);
										$BhaktiFlag = json_decode(file_get_contents($URL), true);
										//die($URL."<br><br>".json_encode($Bhakti));
									} else if ($RequestType=="CBD OFF" || $RequestType=="CBD ON") {
										/*$URL = $URL.API_BKT."/MasterDealer/ApproveRejectCBD2?api=APITES".
												"&hsid=".urlencode($RequestNo).
												"&kdplg=".urlencode($REQ->AddInfo1Value)."&cbd=".urlencode($RequestType).
												"&tgl=".urlencode(date("Y-m-d",strtotime($REQ->AddInfo6Value))).
												"&sts=APPROVED&exp=".urlencode(date("Y-m-d",strtotime($REQ->ExpiryDate))).
												"&reqby=".urlencode($REQ->RequestByName)."&reqdate=".urlencode(date("Y-m-d H:i:s",strtotime($REQ->RequestDate))).
												"&appby=".urlencode($REQ->ApprovedByName)."&appdate=".urlencode(date("Y-m-d H:i:s",strtotime($REQ->ApprovedDate))).
												"&svr=".urlencode($DB->Server)."&db=".urlencode($DB->Database).
												"&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD);
										//die($URL);
										$BhaktiFlag = json_decode(file_get_contents($URL), true);*/

										$URL = $URL.API_BKT."/MasterDealer/ApproveRejectCBD";
										$params = array("api"=>"APITES", 
														"hsid"=>$RequestNo, "cbd"=>$RequestType,
														"kdplg"=>trim($REQ->AddInfo1Value), 
														"tgl"=>date("Y-m-d", strtotime($REQ->AddInfo6Value)),
														"sts"=>"APPROVED", "exp"=>date("Y-m-d", strtotime($REQ->ExpiryDate)),
														"reqby"=>$REQ->RequestByName,"reqdate"=>date("Y-m-d H:i:s",strtotime($REQ->RequestDate)),
														"appby"=>$REQ->ApprovedByName,"appdate"=>date("Y-m-d H:i:s",strtotime($REQ->ApprovedDate)),
														"svr"=>$DB->Server, "db"=>$DB->Database, 
														"uid"=>SQL_UID, "pwd"=>SQL_PWD);

										$options = array(
										    'http' => array(
										        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
										        'method'  => 'POST',
										        'content' => http_build_query($params)
										    )
										);
										$context  = stream_context_create($options);
										$BhaktiFlag = json_decode(file_get_contents($URL, false, $context), true);
										if ($BhaktiFlag === FALSE) {
											$BhaktiFlag["result"] = "gagal";
										}
									}

									if ($BhaktiFlag["result"]=="sukses") {
										$BhaktiFlag = $this->MsDealerModelOld->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"FINISHED");
									} else {
										$BhaktiFlag = $this->MsDealerModelOld->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"PENDING");	
									}
								} else {
									$BhaktiFlag = $this->MsDealerModelOld->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"PENDING");
								}
							}
							$email_content = $this->CreateResponseEmailContent($RequestType, "APPROVED", $REQ);
							$doEmail = $this->MsDealerModelOld->EmailResponseRequest($RequestType, $REQ, $email_content);
							redirect("MsDealer/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&user=".urlencode($UserEmail)."&alert=".urlencode("APPROVE BERHASIL"));
						} else {
							redirect("MsDealer/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&user=".urlencode($UserEmail)."&alert=".urlencode("APPROVE BERHASIL"));
						}
					} else {
						redirect("MsDealer/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&alert=".urlencode("APPROVE GAGAL"));
					}
				} else {
					//die($UserEmail);
					$doReject = $this->MsDealerModelOld->RejectRequest($RequestType, $RequestNo, $UserEmail, $Note);
					if ($doReject["result"]=="sukses") {
						if ($doReject["complete"]==true) {
							$REQ = $doReject["req"];

							if ($RequestType=="CBD OFF" || $RequestType=="CBD ON") {
								$DatabaseID = $doReject["databaseID"];
								$DB = $this->MasterDbModel->get($DatabaseID);
								if ($DB!=null) {
									$URL = $DB->AlamatWebService;
									$connected = false;

									$ch = curl_init($URL.API_BKT."/VirtualAccount2/TestConnection");
									curl_setopt($ch, CURLOPT_TIMEOUT, 2);
									curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
									$response = curl_exec($ch);
									if ($response === false) {
										//die(json_encode($response));
									    $info = curl_getinfo($ch);
									    if ($info['http_code'] === 0) {
									        // timeout
									        $connected=false;
									    }
									} else {
										$connected=true;
									}
									//die(($connected)?"connected":"not connected");

									if ($connected) {
										if ($RequestType=="CBD OFF" || $RequestType=="CBD ON") {
											/*$URL = $URL.API_BKT."/MasterDealer/ApproveRejectCBD2?api=APITES".
													"&hsid=".urlencode($RequestNo).
													"&kdplg=".urlencode($REQ->AddInfo1Value)."&cbd=".urlencode($RequestType).
													"&tgl=".urlencode(date("Y-m-d",strtotime($REQ->AddInfo6Value))).
													"&sts=REJECTED&exp=".urlencode(date("Y-m-d",strtotime($REQ->ExpiryDate))).
													"&reqby=".urlencode($REQ->RequestByName)."&reqdate=".urlencode(date("Y-m-d H:i:s",strtotime($REQ->RequestDate))).
													"&appby=".urlencode($REQ->ApprovedByName)."&appdate=".urlencode(date("Y-m-d H:i:s",strtotime($REQ->ApprovedDate))).
													"&svr=".urlencode($DB->Server)."&db=".urlencode($DB->Database).
													"&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD);
											//die($URL);
											$BhaktiFlag = json_decode(file_get_contents($URL), true);*/
											
											$URL = $URL.API_BKT."/MasterDealer/ApproveRejectCBD";
											$params = array("api"=>"APITES", 
															"hsid"=>$RequestNo, "cbd"=>$RequestType,
															"kdplg"=>trim($REQ->AddInfo1Value), 
															"tgl"=>date("Y-m-d", strtotime($REQ->AddInfo6Value)),
															"sts"=>"REJECTED", "exp"=>date("Y-m-d", strtotime($REQ->ExpiryDate)),
															"reqby"=>$REQ->RequestByName,"reqdate"=>date("Y-m-d H:i:s",strtotime($REQ->RequestDate)),
															"appby"=>$REQ->ApprovedByName,"appdate"=>date("Y-m-d H:i:s",strtotime($REQ->ApprovedDate)),
															"svr"=>$DB->Server, "db"=>$DB->Database, 
															"uid"=>SQL_UID, "pwd"=>SQL_PWD);

											$options = array(
											    'http' => array(
											        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
											        'method'  => 'POST',
											        'content' => http_build_query($params)
											    )
											);
											$context  = stream_context_create($options);
											$BhaktiFlag = json_decode(file_get_contents($URL, false, $context), true);
											if ($BhaktiFlag === FALSE) {
												$BhaktiFlag["result"] = "gagal";
											}
										}

										if ($BhaktiFlag["result"]=="sukses") {
											$BhaktiFlag = $this->MsDealerModelOld->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"FINISHED");
										} else {
											$BhaktiFlag = $this->MsDealerModelOld->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"PENDING");	
										}
									} else {
										$BhaktiFlag = $this->MsDealerModelOld->UpdateBhaktiFlag($RequestType, $REQ->RequestNo, "PENDING");
									}
								}
							} else {
								$BhaktiFlag = $this->MsDealerModelOld->UpdateBhaktiFlag($RequestType, $REQ->RequestNo, "FINISHED");
							}
						}
						$email_content = $this->CreateResponseEmailContent($RequestType, "REJECTED", $REQ);
						$doEmail = $this->MsDealerModelOld->EmailResponseRequest($RequestType, $REQ, $email_content);
						redirect("MsDealer/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&alert=".urlencode("REJECT BERHASIL"));
					} else {
						redirect("MsDealer/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&alert=".urlencode("REJECT GAGAL"));
					}
				}
			} else {
				redirect("MsDealer/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&alert=".urlencode("PASSWORD SALAH"));
			}
		} else {
			redirect("MsDealer/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&alert=".urlencode("PASSWORD BELUM DIISI"));
		}
	}

	public function CreateResponseEmailContent($RequestType="CREDIT LIMIT", $Response, $REQ)
	{
		$Requests = $this->MsDealerModelOld->GetRequest($RequestType, $REQ->RequestNo);
		$NO = 0;

		$request_table = "<br>";
		$request_table.= "<table>";
		$request_table.= "	<tr>";
		$request_table.= "		<th width='5%' style='border:1px dashed #ccc;padding:3px;'>No</th>";
		$request_table.= "		<th width='20%' style='border:1px dashed #ccc;padding:3px;'>Ket</th>";
		$request_table.= "	</tr>";

		foreach($Requests as $r) {
			$NO+=1;
			$MOBILE = $r->ApprovedByName."<br>";
			$MOBILE.= "STATUS: ".$r->ApprovalStatus."<br>";
			$MOBILE.= ($r->ApprovalStatus=="UNPROCESSED")? "" : date("d-M-Y H:i:s",strtotime($r->ApprovedDate))."<br>";
			$MOBILE.= ($r->ApprovalNote=="")?"<br>":"KET: ".$r->ApprovalNote."<br>";

			$request_table.= "	<tr>";
			$request_table.= "		<td style='border:1px dashed #ccc;padding:3px;'>".$NO."</td>";
			$request_table.= "		<td style='border:1px dashed #ccc;padding:3px;'>".$MOBILE."</td>";
			$request_table.= "	</tr>";				
		}
		$request_table.="</table>";

		$email_content = "";
		if ($RequestType=="CREDIT LIMIT") {
			$email_content = "Permohonan Kenaikan Credit Limit berikut telah <b>".(($Response=="APPROVED")?"Disetujui":"Ditolak")."</b><br><br>";
		} else {
			$email_content = "Permohonan Penonaktifan Status CBD berikut telah <b>".(($Response=="APPROVED")?"Disetujui":"Ditolak")."</b><br><br>";
		}
		$email_content.= json_decode($REQ->AddInfo4Value);
		
		$stylesheet = "<style>";
		$stylesheet.= "	td, th { border:1px solid #ccc; padding:3px; } ";
		$stylesheet.= "</style>";

		$email_content.= $stylesheet.$request_table;
		return $email_content;
	}

    public function GetLastTenRequests()
    {
		$post = $this->PopulatePost();
		if(isset($post['KdPlg']) && isset($post["Divisi"])){
			$GetLastTen = $this->MsDealerModelOld->GetLastTenRequests($post["KdPlg"], $post["Divisi"]);
	        if (count($GetLastTen)>0) {
				echo json_encode(array("result"=>"sukses", "data"=>$GetLastTen, "ket"=>""));
	       	} else {
	       		echo json_encode(array("result"=>"gagal", "data"=>array(), "ket"=>"Belum Ada Request CL"));
	       	}
		} else {
			echo json_encode(array("result"=>"gagal", "data"=>array(), "ket"=>'Kode Pelanggan/Divisi Belum Diisi'));
		}    	
    }

    public function UpdateBhaktiFlag()
	{
		$keyAPI = 'APITES';
		$api = urldecode($this->input->get('api'));
		$req = urldecode($this->input->get('req'));
		$sts = urldecode($this->input->get('sts'));
		$type = urldecode($this->input->get('type'));

		if($keyAPI==$api) {
			set_time_limit(60);
			$BhaktiFlag = $this->MsDealerModelOld->UpdateBhaktiFlag($type, $req, strtoupper($sts));
			if ($BhaktiFlag==true) {
				$data["result"] = "sukses";
			}
			else
			{
				$data["result"] = "gagal";
			}
		} else {
			$data["result"] = "gagal";
		}

		$hasil = json_encode($data);
		header('HTTP/1.1: 200');
		header('Status: 200');
		header('Content-Length: '.strlen($hasil));
		exit($hasil);	
	}

    public function GetPendingCL()
	{
		$keyAPI = 'APITES';
		$api = urldecode($this->input->get('api'));

		if($keyAPI==$api) {
			set_time_limit(60);
			$requests = $this->MsDealerModelOld->GetPendingCL();
			if (count($requests)>0) 
			{
				$data["result"] = "sukses";
				$data["data"] = $requests;
				$data["error"] = "";
			}
			else
			{
				$data["result"] = "gagal";
				$data["data"] = array();
				$data["error"] = "Tidak Ada Request Pending";
			}
		} else {
			$data["result"] = "gagal";
			$data["data"] = array();
			$data["error"] = "Kode API Salah";
		}

		$hasil = json_encode($data);
		header('HTTP/1.1: 200');
		header('Status: 200');
		header('Content-Length: '.strlen($hasil));
		exit($hasil);	
	}

    public function GetPendingCBD()
	{
		$keyAPI = 'APITES';
		$api = urldecode($this->input->get('api'));

		if($keyAPI==$api) {
			set_time_limit(60);
			$requests = $this->MsDealerModelOld->GetPendingCBD();
			if (count($requests)>0) 
			{
				$data["result"] = "sukses";
				$data["data"] = $requests;
				$data["error"] = "";
			}
			else
			{
				$data["result"] = "gagal";
				$data["data"] = array();
				$data["error"] = "Tidak Ada Request Pending";
			}
		} else {
			$data["result"] = "gagal";
			$data["data"] = array();
			$data["error"] = "Kode API Salah";
		}

		$hasil = json_encode($data);
		header('HTTP/1.1: 200');
		header('Status: 200');
		header('Content-Length: '.strlen($hasil));
		exit($hasil);	
	}

	public function viewRekapFakturJT(){
		if(isset($_SESSION['conn'])){
			$result = $this->UserModel->getKdSlsman($_SESSION['logged_in']['useremail'],$_SESSION['conn']->Database);
			if($result){
				$this->RenderView('MsDealerFakturJatuhTempoView',$result);
			}
			else{
				$this->RenderView('MsDealerFakturJatuhTempoView');
			}
		}
		else{
			redirect('Home');
		}
	}

	public function PreviewFakturJT(){
		$this->RenderView('MsDealerPreviewFakturJatuhTempoView');
	}

    public function RequestCLOld()
    {
    	$dbHQ = $this->MasterDbModel->getBhaktiPusat();

		$post = $this->PopulatePost();
		$params = array();
		$params["Divisi"] = $this->input->post("selDiv");
		$params["KdPlg"] = $this->input->post("txtKodePlg");
		$params["NmPlg"] = $this->input->post("txtNamaPlg");
		$params["AlmPlg"] = $this->input->post("txtAlamatPlg");
		$params["Wilayah"] = $this->input->post("txtWilayahPlg");
		$params["Marking"] = $this->input->post("txtMarking");
		$params["Catatan"] = $this->input->post("txtCatatan");
		$params["CLPermanent"] = str_replace(",","",$this->input->post("txtLiPerma"));
		$params["CLTemporary"] = str_replace(",","",$this->input->post("txtLiTemp"));
		$params["CLMaks"] = str_replace(",","",$this->input->post("txtLiMaks"));
		$params["CLNew"] = str_replace(",","",$this->input->post("txtRequest"));
		$params["KenaikanCL"] = $params["CLNew"] - $params["CLPermanent"];
		$params["DatabaseID"] = (($params["Wilayah"]=="MODERN OUTLET")? $dbHQ->DatabaseId : $_SESSION["conn"]->DatabaseId);
		$params["BranchDB"] = $_SESSION["conn"]->BranchId;
		$params["NamaDB"] = $_SESSION["conn"]->NamaDb;
		$params["IsBass"] = false;
		$params["RequestID"] = "";
		$params["ExpiryDate"] = date("Y-m-d H:i:s");

		$GetCLGroup = $this->ProsesCreditLimitGroup($params["KdPlg"], $params["Wilayah"], $params["Divisi"], $params["NmPlg"] );
		if ($GetCLGroup["result"]=="sukses") {
			$CLGroup = $GetCLGroup["data"];
			//die("CLGroup : <br>".json_encode($CLGroup));
			$CheckExistingRequest = $this->MsDealerModelOld->CheckExistingRequest("CREDIT LIMIT", $params);
			//$CheckExistingRequest = 0;

			if ($CheckExistingRequest>0) {
				redirect("MsDealer/CreditLimit?alert=".urlencode("REQUEST CREDIT LIMIT SUDAH ADA"));

			} else if ($params["KdPlg"]=="" || $params["NmPlg"]=="" || $params["AlmPlg"]=="" || $params["CLPermanent"]=="" || $params["CLNew"]=="" 
				|| $params["CLNew"]=="0" || $params["CLNew"]==$params["CLPermanent"])  {

				redirect("MsDealer/CreditLimit?alert=".urlencode("DATA TIDAK LENGKAP"));

			} else {

				$recipients = array();
				$allRecipients = array();
				$mobiles = array();
				$REQUESTID = array();
				$IsSuccess = "SUKSES";

				$this->load->model("SalesManagerModel");
				$BrandManagers = $this->SalesManagerModel->GetBrandManagers();
				$SalesManagers = $this->SalesManagerModel->GetSalesManager();
				$GM = $this->SalesManagerModel->GetGeneralManager();
				$EmpFound = false;
				$Priority = 0;

				$user = array();
				$user["Name"] = $_SESSION["logged_in"]["username"];
				$user["UserID"] = $_SESSION["logged_in"]["employeeid"];
				$user["UserEmail"] = $_SESSION["logged_in"]["useremail"];
				$user["Email"] = $_SESSION["logged_in"]["email"];
				$user["BranchID"] = $_SESSION["logged_in"]["branch_id"];
				$user["City"] = $_SESSION["logged_in"]["city"];

				$email_content = $this->CreateEmailContent($params, $CLGroup);
				
				if ($params["Divisi"]=="SPAREPART") {

					//BEFORE 16JUN2020: CL SPAREPART<=20JT APPROVAL KACAB/KO HENGKY. >20JT APPROVAL KACAB/KO HENGKY + GM
					//16JUN2020: SPAREPART ALL CBG KIRIM ke HENGKY WIJAYA 2115
					//11NOV2020: PINDAH KE ANDRE HILMAN 3651

					//14DES2020: 
					//BASS  : JKT ke Manager Service, CBG <=100juta Kacab, >100juta Kacab+Manager Service
					//DEALER: JKT <=100juta Manager Service, > 100juta Manager Service + 1 BM
					//		  Cabang <=100juta ke Kacab, >100juta Kacab + 1 BM

					//Check BASSnya ke webAPI aja
					$url = $this->API_URL."/MasterDealer/GetDealer?api=APITES&plg=".urlencode($params["KdPlg"]);
					$GetDealer = json_decode(file_get_contents($url),true);
					if ($GetDealer["result"]=="sukses") {
						$params["IsBass"] = $GetDealer["data"]["IS_BASS"];
					}
					//echo((($params["IsBass"])?"BASS":"NOT BASS")."<br>");

					if ($params["KenaikanCL"]<=100000000) {

						$Priority = 1;
						$params["ApprovalNeeded"] = 1;
						$params["Priority"] = $Priority;
						$recipients = array();
						if ($params["Wilayah"]=="OUTLIER" || ($params["BranchDB"]=="JKT" && $params["NamaDB"]=="JAKARTA")) {
							//3651 : Andre Hilman
							$zen = API_ZEN."/ZenAPI/GetEmployee?userid=3651";
							$GetKacab = json_decode(file_get_contents($zen), true);
							if ($GetKacab["result"]=="sukses") {
								$Employee = $GetKacab["data"];
								array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
								array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

								$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
								if ($WA != "") array_push($mobiles, $WA);
							}
							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];

						} else if ($params["BranchDB"]=="JKT" && ($params["NamaDB"]=="BOGOR" || $params["NamaDB"]=="KARAWANG")) {
							//Khusus BOGOR dan KARAWANG under JAKARTA dihardcode kirim request ke FANDY
							$zen = API_ZEN."/ZenAPI/GetEmployee?userid=2661";
							$GetKacab = json_decode(file_get_contents($zen), true);
							if ($GetKacab["result"]=="sukses") {
								$Employee = $GetKacab["data"];
								array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
								array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

								$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
								if ($WA != "") array_push($mobiles, $WA);
							}
							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];
						} else {
							//$user["BranchID"] = "BJM";

							$zen = API_ZEN."/ZenAPI/GetBranch?branch=".urlencode($user["BranchID"]);
							$GetBranch = json_decode(file_get_contents($zen), true);
							if ($GetBranch["result"]=="sukses") {
								$BranchHead = $GetBranch["data"]["BRANCHHEAD"];
								if ($BranchHead!="") {
									$zen = API_ZEN."/ZenAPI/GetEmployee?userid=".urlencode($BranchHead);
									$GetKacab = json_decode(file_get_contents($zen), true);

									if ($GetKacab["result"]=="sukses") {
										$Employee = $GetKacab["data"];
										array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
										array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

										$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
										if ($WA != "") array_push($mobiles, $WA);
									}
								}
							}
							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];
						}

						$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);
						$sendWA = $this->MsDealerModelOld->ProsesWhatsapp("CREDIT LIMIT", $params, $mobiles, $user);

					} else if ($params["KenaikanCL"]>100000000) {
						$Priority = 1;
						$params["ApprovalNeeded"] = 1;
						$params["Priority"] = $Priority;
						$recipients = array();

						if ($params["IsBass"]==1) {
							$zen = API_ZEN."/ZenAPI/GetEmployee?userid=3651";
							$GetKacab = json_decode(file_get_contents($zen), true);
							if ($GetKacab["result"]=="sukses") {
								$Employee = $GetKacab["data"];
								array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
								array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

								$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
								if ($WA != "") array_push($mobiles, $WA);
							}
							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];

							if ($params["BranchDB"]=="JKT" && ($params["NamaDB"]=="BOGOR" || $params["NamaDB"]=="KARAWANG")) {
								$Priority += 1;
								$params["ApprovalNeeded"] = 1;
								$params["Priority"] = $Priority;
								$recipients = array();
								//Khusus BOGOR dan KARAWANG under JAKARTA dihardcode kirim request ke FANDY
								$zen = API_ZEN."/ZenAPI/GetEmployee?userid=2661";
								$GetKacab = json_decode(file_get_contents($zen), true);
								if ($GetKacab["result"]=="sukses") {
									$Employee = $GetKacab["data"];
									array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
									array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

									$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
									if ($WA != "") array_push($mobiles, $WA);
								}
								$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user, $REQUESTID["requestid"]);
								$IsSuccess = strtoupper($REQUESTID["result"]);
							} else if ($params["BranchDB"]!="JKT") {							
								$Priority += 1;
								$params["ApprovalNeeded"] = 1;
								$params["Priority"] = $Priority;
								$recipients = array();

								$zen = API_ZEN."/ZenAPI/GetBranch?branch=".urlencode($user["BranchID"]);
								$GetBranch = json_decode(file_get_contents($zen), true);
								if ($GetBranch["result"]=="sukses") {
									$BranchHead = $GetBranch["data"]["BRANCHHEAD"];
									if ($BranchHead!="") {
										$zen = API_ZEN."/ZenAPI/GetEmployee?userid=".urlencode($BranchHead);
										$GetKacab = json_decode(file_get_contents($zen), true);

										if ($GetKacab["result"]=="sukses") {
											$Employee = $GetKacab["data"];
											array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
											array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

											$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
											if ($WA != "") array_push($mobiles, $WA);
										}
									}
								}
								$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user, $REQUESTID["requestid"]);
								$IsSuccess = strtoupper($REQUESTID["result"]);
							}
						} else {
							//14 DES 2020: KENAIKAN > 100JUTA,
							//DEALER/BUKAN BASS => JKT : 1 BM + MGR SVR, CBG : 1 BM + KACAB
							//BM DULU ..
							foreach($BrandManagers as $m) {
								$EmpFound = false;
								for($i=0;$i<count($recipients);$i++) {
									if ($recipients[$i]["NAMA"]==$m->employee_name && $recipients[$i]["EMAIL"]==(($m->email_address==null)?$m->email:$m->email_address)) {
										$EmpFound = true;
									}
								}
								if ($EmpFound==false) {
									array_push($recipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
									array_push($allRecipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
								}

								$MobileFound = false;
								$WA = $this->HelperModel->MobileWithCountryCode($m->mobile);
								if ($WA!="") {
									for($i=0;$i<count($mobiles);$i++) {
										if ($mobiles[$i]==$WA) {
											$MobileFound = true;
										}
									}
									if ($MobileFound==false) {
										array_push($mobiles, $WA);
									}
								}
							}
							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];

							//SKRG MGR SVC / KACAB
							if ($params["Wilayah"]=="OUTLIER" || ($params["BranchDB"]=="JKT" && $params["NamaDB"]=="JAKARTA")) {
								$Priority += 1;
								$params["ApprovalNeeded"] = 1;
								$params["Priority"] = $Priority;
								$recipients = array();
								$zen = API_ZEN."/ZenAPI/GetEmployee?userid=3651";
								$GetKacab = json_decode(file_get_contents($zen), true);
								if ($GetKacab["result"]=="sukses") {
									$Employee = $GetKacab["data"];
									array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
									array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

									$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
									if ($WA != "") array_push($mobiles, $WA);
								}
								$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user, $REQUESTID["requestid"]);
								$IsSuccess = strtoupper($REQUESTID["result"]);

							} else if ($params["BranchDB"]=="JKT" && ($params["NamaDB"]=="BOGOR" || $params["NamaDB"]=="KARAWANG")) {
								$Priority += 1;
								$params["ApprovalNeeded"] = 1;
								$params["Priority"] = $Priority;
								$recipients = array();
								//Khusus BOGOR dan KARAWANG under JAKARTA dihardcode kirim request ke FANDY
								$zen = API_ZEN."/ZenAPI/GetEmployee?userid=2661";
								$GetKacab = json_decode(file_get_contents($zen), true);
								if ($GetKacab["result"]=="sukses") {
									$Employee = $GetKacab["data"];
									array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
									array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

									$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
									if ($WA != "") array_push($mobiles, $WA);
								}
								$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user, $REQUESTID["requestid"]);
								$IsSuccess = strtoupper($REQUESTID["result"]);

							} else if ($params["BranchDB"]!="JKT") {							
								$Priority += 1;
								$params["ApprovalNeeded"] = 1;
								$params["Priority"] = $Priority;
								$recipients = array();

								$zen = API_ZEN."/ZenAPI/GetBranch?branch=".urlencode($user["BranchID"]);
								$GetBranch = json_decode(file_get_contents($zen), true);
								if ($GetBranch["result"]=="sukses") {
									$BranchHead = $GetBranch["data"]["BRANCHHEAD"];
									if ($BranchHead!="") {
										$zen = API_ZEN."/ZenAPI/GetEmployee?userid=".urlencode($BranchHead);
										$GetKacab = json_decode(file_get_contents($zen), true);

										if ($GetKacab["result"]=="sukses") {
											$Employee = $GetKacab["data"];
											array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
											array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

											$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
											if ($WA != "") array_push($mobiles, $WA);
										}
									}
								}
								$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user, $REQUESTID["requestid"]);
								$IsSuccess = strtoupper($REQUESTID["result"]);
							}
						}

						$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);
						$sendWA = $this->MsDealerModelOld->ProsesWhatsapp("CREDIT LIMIT", $params, $mobiles, $user);
					}

				} else if ($params["Divisi"]=="MO") {
					if ($params["KenaikanCL"]<=100000000) {

						$Priority = 1;
						$lanjutBM = false;
						//$recipients = array();
						$params["ApprovalNeeded"] = 1;
						$params["Priority"] = $Priority;

						if ($params["Wilayah"]=="MODERN OUTLET" || $params["Wilayah"]=="DM") {
							$Kajul = $this->SalesManagerModel->GetKajul("MODERN OUTLET");
							if ($Kajul!=null) {
								array_push($recipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
								array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
								
								$WA = $this->HelperModel->MobileWithCountryCode($Kajul->mobile);
								if ($WA!="")  array_push($mobiles, $Kajul->mobile);
							}

							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];
							$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);
							$sendWA = $this->MsDealerModelOld->ProsesWhatsapp("CREDIT LIMIT", $params, $mobiles, $user);

						} else {
							if ($params["BranchDB"]=="JKT" && ($params["NamaDB"]=="BOGOR" || $params["NamaDB"]=="KARAWANG")) {
								//Khusus BOGOR dan KARAWANG under JAKARTA dihardcode kirim request ke FANDY
								$zen = API_ZEN."/ZenAPI/GetEmployee?userid=2661";
								$GetKacab = json_decode(file_get_contents($zen), true);
								if ($GetKacab["result"]=="sukses") {
									$Employee = $GetKacab["data"];
									array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
									array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

									$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
									if ($WA != "") array_push($mobiles, $WA);
								}
								$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
								$IsSuccess = strtoupper($REQUESTID["result"]);
								$params["RequestID"] = $REQUESTID["requestid"];
								$params["ExpiryDate"] = $REQUESTID["expirydate"];

								$Kajul = $this->SalesManagerModel->GetKajul($params["Wilayah"]);
								if ($Kajul!=null) array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
								$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);
								$sendWA = $this->MsDealerModelOld->ProsesWhatsapp("CREDIT LIMIT", $params, $mobiles, $user);

							} else {
								$zen = API_ZEN."/ZenAPI/GetBranch?branch=".urlencode($user["BranchID"]);
								$GetBranch = json_decode(file_get_contents($zen), true);
								if ($GetBranch["result"]=="sukses") {
									$BranchHead = $GetBranch["data"]["BRANCHHEAD"];
									if ($BranchHead!="") {
										$zen = API_ZEN."/ZenAPI/GetEmployee?userid=".urlencode($BranchHead);
										$GetKacab = json_decode(file_get_contents($zen), true);

										if ($GetKacab["result"]=="sukses") {
											$Employee = $GetKacab["data"];
											array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
											array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

											$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
											if ($WA != "") array_push($mobiles, $WA);
										}
									}
								}
								$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
								$IsSuccess = strtoupper($REQUESTID["result"]);
								$params["RequestID"] = $REQUESTID["requestid"];
								$params["ExpiryDate"] = $REQUESTID["expirydate"];

								$Kajul = $this->SalesManagerModel->GetKajul($params["Wilayah"]);
								if ($Kajul!=null) array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
								$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);			
								$sendWA = $this->MsDealerModelOld->ProsesWhatsapp("CREDIT LIMIT", $params, $mobiles, $user);
							}
						}
					} else if ($params["KenaikanCL"]<=300000000) {
						$Priority = 1;
						$lanjutBM = true;
						$lanjutSM = false;
						//$recipients = array();
						$params["ApprovalNeeded"] = 1;
						$params["Priority"] = $Priority;

						if ($params["Wilayah"]=="MODERN OUTLET" || $params["Wilayah"]=="DM") {
							$Kajul = $this->SalesManagerModel->GetKajul("MODERN OUTLET");
							if ($Kajul!=null) {
								array_push($recipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
								array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
								
								$WA = $this->HelperModel->MobileWithCountryCode($Kajul->mobile);
								if ($WA!="")  array_push($mobiles, $Kajul->mobile);
							}

							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];
							//$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);
							$lanjutBM = false;
							$lanjutSM = true;

						} else {
							
							if ($params["BranchDB"]=="JKT" && ($params["NamaDB"]=="BOGOR" || $params["NamaDB"]=="KARAWANG")) {
								//Khusus BOGOR dan KARAWANG under JAKARTA dihardcode kirim request ke FANDY
								$zen = API_ZEN."/ZenAPI/GetEmployee?userid=2661";
								$GetKacab = json_decode(file_get_contents($zen), true);
								if ($GetKacab["result"]=="sukses") {
									$Employee = $GetKacab["data"];
									array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
									array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

									$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
									if ($WA != "") array_push($mobiles, $WA);
								}
								$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
								$IsSuccess = strtoupper($REQUESTID["result"]);
								$params["RequestID"] = $REQUESTID["requestid"];
								$params["ExpiryDate"] = $REQUESTID["expirydate"];

								$Kajul = $this->SalesManagerModel->GetKajul($params["Wilayah"]);
								if ($Kajul!=null) array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
								//$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);

							} else {
								$zen = API_ZEN."/ZenAPI/GetBranch?branch=".urlencode($user["BranchID"]);
								$GetBranch = json_decode(file_get_contents($zen), true);
								if ($GetBranch["result"]=="sukses") {
									$BranchHead = $GetBranch["data"]["BRANCHHEAD"];
									if ($BranchHead!="") {
										$zen = API_ZEN."/ZenAPI/GetEmployee?userid=".urlencode($BranchHead);
										$GetKacab = json_decode(file_get_contents($zen), true);

										if ($GetKacab["result"]=="sukses") {
											$Employee = $GetKacab["data"];
											array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
											array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

											$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
											if ($WA != "") array_push($mobiles, $WA);
										}
									}
								}
								$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
								$IsSuccess = strtoupper($REQUESTID["result"]);
								$params["RequestID"] = $REQUESTID["requestid"];
								$params["ExpiryDate"] = $REQUESTID["expirydate"];

								$Kajul = $this->SalesManagerModel->GetKajul($params["Wilayah"]);
								if ($Kajul!=null) array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
								//$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);						
							}
						}

						if ($lanjutSM==true) {
							$Priority += 1;
							$recipients = array();
							$params["ApprovalNeeded"] = 1;
							$params["Priority"] = $Priority;
					
							foreach($SalesManagers as $m) {
								$EmpFound = false;
								for($i=0;$i<count($recipients);$i++) {
									if ($recipients[$i]["NAMA"]==$m->employee_name && $recipients[$i]["EMAIL"]==(($m->email_address==null)?$m->email:$m->email_address)) {
										$EmpFound = true;
									}
								}
								if ($EmpFound==false) {
									array_push($recipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
									array_push($allRecipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
								}

								$MobileFound = false;
								$WA = $this->HelperModel->MobileWithCountryCode($m->mobile);
								if ($WA!="") {
									for($i=0;$i<count($mobiles);$i++) {
										if ($mobiles[$i]==$WA) {
											$MobileFound = true;
										}
									}
									if ($MobileFound==false) {
										array_push($mobiles, $WA);
									}
								}
							}
						}

						if ($lanjutBM==true) {
							$Priority += 1;
							$recipients = array();
							$params["ApprovalNeeded"] = 1;
							$params["Priority"] = $Priority;
					
							foreach($BrandManagers as $m) {
								$EmpFound = false;
								for($i=0;$i<count($recipients);$i++) {
									if ($recipients[$i]["NAMA"]==$m->employee_name && $recipients[$i]["EMAIL"]==(($m->email_address==null)?$m->email:$m->email_address)) {
										$EmpFound = true;
									}
								}
								if ($EmpFound==false) {
									array_push($recipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
									array_push($allRecipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
								}

								$MobileFound = false;
								$WA = $this->HelperModel->MobileWithCountryCode($m->mobile);
								if ($WA!="") {
									for($i=0;$i<count($mobiles);$i++) {
										if ($mobiles[$i]==$WA) {
											$MobileFound = true;
										}
									}
									if ($MobileFound==false) {
										array_push($mobiles, $WA);
									}
								}
							}

							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user, $REQUESTID["requestid"]);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];
						}
						// echo("Start Send Email/WA");
						$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);
						$sendWA = $this->MsDealerModelOld->ProsesWhatsapp("CREDIT LIMIT", $params, $mobiles, $user);

					} else if ($params["KenaikanCL"]<=500000000) {
						$Priority = 1;
						$lanjutBM = true;
						$lanjutSM = false;
						//$recipients = array();
						$params["ApprovalNeeded"] = 1;
						$params["Priority"] = $Priority;

						if ($params["Wilayah"]=="MODERN OUTLET" || $params["Wilayah"]=="DM") {
							$Kajul = $this->SalesManagerModel->GetKajul("MODERN OUTLET");
							if ($Kajul!=null) {
								array_push($recipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
								array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
								$WA = $this->HelperModel->MobileWithCountryCode($Kajul->mobile);
								if ($WA!="")  array_push($mobiles, $Kajul->mobile);
							}

							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];
							//$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);
							$lanjutSM = true;
						} else {
							if ($params["BranchDB"]=="JKT" && ($params["NamaDB"]=="BOGOR" || $params["NamaDB"]=="KARAWANG")) {
								//Khusus BOGOR dan KARAWANG under JAKARTA dihardcode kirim request ke FANDY
								$zen = API_ZEN."/ZenAPI/GetEmployee?userid=2661";
								$GetKacab = json_decode(file_get_contents($zen), true);
								if ($GetKacab["result"]=="sukses") {
									$Employee = $GetKacab["data"];
									array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
									array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

									$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
									if ($WA != "") array_push($mobiles, $WA);
								}
								$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
								$IsSuccess = strtoupper($REQUESTID["result"]);
								$params["RequestID"] = $REQUESTID["requestid"];
								$params["ExpiryDate"] = $REQUESTID["expirydate"];

								$Kajul = $this->SalesManagerModel->GetKajul($params["Wilayah"]);
								if ($Kajul!=null) array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
								//$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);

							} else {
								$zen = API_ZEN."/ZenAPI/GetBranch?branch=".urlencode($user["BranchID"]);
								$GetBranch = json_decode(file_get_contents($zen), true);
								if ($GetBranch["result"]=="sukses") {
									$BranchHead = $GetBranch["data"]["BRANCHHEAD"];
									if ($BranchHead!="") {
										$zen = API_ZEN."/ZenAPI/GetEmployee?userid=".urlencode($BranchHead);
										$GetKacab = json_decode(file_get_contents($zen), true);

										if ($GetKacab["result"]=="sukses") {
											$Employee = $GetKacab["data"];
											array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
											array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

											$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
											if ($WA != "") array_push($mobiles, $WA);
										}
									}
								}
								$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
								$IsSuccess = strtoupper($REQUESTID["result"]);
								$params["RequestID"] = $REQUESTID["requestid"];
								$params["ExpiryDate"] = $REQUESTID["expirydate"];

								$Kajul = $this->SalesManagerModel->GetKajul($params["Wilayah"]);
								if ($Kajul!=null) array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
								//$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);						
							}
						}

						if ($lanjutSM==true) {
							$Priority += 1;
							$recipients = array();
							$params["ApprovalNeeded"] = 1;
							$params["Priority"] = $Priority;
					
							foreach($SalesManagers as $m) {
								$EmpFound = false;
								for($i=0;$i<count($recipients);$i++) {
									if ($recipients[$i]["NAMA"]==$m->employee_name && $recipients[$i]["EMAIL"]==(($m->email_address==null)?$m->email:$m->email_address)) {
										$EmpFound = true;
									}
								}
								if ($EmpFound==false) {
									array_push($recipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
									array_push($allRecipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
								}

								$MobileFound = false;
								$WA = $this->HelperModel->MobileWithCountryCode($m->mobile);
								if ($WA!="") {
									for($i=0;$i<count($mobiles);$i++) {
										if ($mobiles[$i]==$WA) {
											$MobileFound = true;
										}
									}
									if ($MobileFound==false) {
										array_push($mobiles, $WA);
									}
								}
							}
						}

						if ($lanjutBM==true) {
							$Priority += 1;
							$recipients = array();
							$params["ApprovalNeeded"] = (($lanjutSM==true)?1:2);
							$params["Priority"] = $Priority;
					
							foreach($BrandManagers as $m) {				
								$EmpFound = false;
								for($i=0;$i<count($recipients);$i++) {
									if ($recipients[$i]["NAMA"]==$m->employee_name && $recipients[$i]["EMAIL"]==(($m->email_address==null)?$m->email:$m->email_address)) {
										$EmpFound = true;
									}
								}
								if ($EmpFound==false) {
									array_push($recipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
									array_push($allRecipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
								}

								$MobileFound = false;
								$WA = $this->HelperModel->MobileWithCountryCode($m->mobile);
								if ($WA!="") {
									for($i=0;$i<count($mobiles);$i++) {
										if ($mobiles[$i]==$WA) {
											$MobileFound = true;
										}
									}
									if ($MobileFound==false) {
										array_push($mobiles, $WA);
									}
								}

							}

							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user, $REQUESTID["requestid"]);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];
						}

						$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);
						$sendWA = $this->MsDealerModelOld->ProsesWhatsapp("CREDIT LIMIT", $params, $mobiles, $user);

					} else  {				
						$Priority = 1;
						$lanjutBM = true;
						$lanjutSM = false;
						$lanjutGM = true;
						$recipients = array();
						$allRecipients = array();
						$params["ApprovalNeeded"] = 1;
						$params["Priority"] = $Priority;
						
						if ($params["Wilayah"]=="MODERN OUTLET" || $params["Wilayah"]=="DM") {
							$Kajul = $this->SalesManagerModel->GetKajul("MODERN OUTLET");
							if ($Kajul!=null) {
								array_push($recipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
								array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
								$WA = $this->HelperModel->MobileWithCountryCode($Kajul->mobile);
								if ($WA!="")  array_push($mobiles, $Kajul->mobile);
							}

							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];
							//$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);
							$lanjutSM = true;

						} else {
							if ($params["BranchDB"]=="JKT" && ($params["NamaDB"]=="BOGOR" || $params["NamaDB"]=="KARAWANG")) {
								//Khusus BOGOR dan KARAWANG under JAKARTA dihardcode kirim request ke FANDY
								$zen = API_ZEN."/ZenAPI/GetEmployee?userid=2661";
								$GetKacab = json_decode(file_get_contents($zen), true);
								if ($GetKacab["result"]=="sukses") {
									$Employee = $GetKacab["data"];
									array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
									array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

									$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
									if ($WA != "") array_push($mobiles, $WA);
								}
								$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
								$IsSuccess = strtoupper($REQUESTID["result"]);
								$params["RequestID"] = $REQUESTID["requestid"];
								$params["ExpiryDate"] = $REQUESTID["expirydate"];

								$Kajul = $this->SalesManagerModel->GetKajul($params["Wilayah"]);
								if ($Kajul!=null) array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
								//$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);

							} else {
								$zen = API_ZEN."/ZenAPI/GetBranch?branch=".urlencode($user["BranchID"]);
								$GetBranch = json_decode(file_get_contents($zen), true);
								if ($GetBranch["result"]=="sukses") {
									$BranchHead = $GetBranch["data"]["BRANCHHEAD"];
									if ($BranchHead!="") {
										$zen = API_ZEN."/ZenAPI/GetEmployee?userid=".urlencode($BranchHead);
										$GetKacab = json_decode(file_get_contents($zen), true);

										if ($GetKacab["result"]=="sukses") {
											$Employee = $GetKacab["data"];
											array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
											array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

											$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
											if ($WA != "") array_push($mobiles, $WA);
										}
									}
								}
								$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
								$IsSuccess = strtoupper($REQUESTID["result"]);
								$params["RequestID"] = $REQUESTID["requestid"];
								$params["ExpiryDate"] = $REQUESTID["expirydate"];

								$Kajul = $this->SalesManagerModel->GetKajul($params["Wilayah"]);
								if ($Kajul!=null) array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
								//$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);						
							}
						}

						if ($lanjutSM==true) {
							$Priority += 1;
							$recipients = array();
							$params["ApprovalNeeded"] = 1;
							$params["Priority"] = $Priority;
					
							foreach($SalesManagers as $m) {
								$EmpFound = false;
								for($i=0;$i<count($recipients);$i++) {
									if ($recipients[$i]["NAMA"]==$m->employee_name && $recipients[$i]["EMAIL"]==(($m->email_address==null)?$m->email:$m->email_address)) {
										$EmpFound = true;
									}
								}
								if ($EmpFound==false) {
									array_push($recipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
									array_push($allRecipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
								}

								$MobileFound = false;
								$WA = $this->HelperModel->MobileWithCountryCode($m->mobile);
								if ($WA!="") {
									for($i=0;$i<count($mobiles);$i++) {
										if ($mobiles[$i]==$WA) {
											$MobileFound = true;
										}
									}
									if ($MobileFound==false) {
										array_push($mobiles, $WA);
									}
								}
							}
						}

						if ($lanjutBM==true) {
							$Priority += 1;
							$recipients = array();
							$params["ApprovalNeeded"] = (($lanjutSM==true)?1:2);
							$params["Priority"] = $Priority;
					
							foreach($BrandManagers as $m) {
								$EmpFound = false;
								for($i=0;$i<count($recipients);$i++) {
									if ($recipients[$i]["NAMA"]==$m->employee_name && $recipients[$i]["EMAIL"]==(($m->email_address==null)?$m->email:$m->email_address)) {
										$EmpFound = true;
									}
								}
								if ($EmpFound==false) {
									array_push($recipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
									array_push($allRecipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
								}

								$MobileFound = false;
								$WA = $this->HelperModel->MobileWithCountryCode($m->mobile);
								if ($WA!="") {
									for($i=0;$i<count($mobiles);$i++) {
										if ($mobiles[$i]==$WA) {
											$MobileFound = true;
										}
									}
									if ($MobileFound==false) {
										array_push($mobiles, $WA);
									}
								}
							}

							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user, $REQUESTID["requestid"]);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];
						}

						if ($lanjutGM==true) {
							$Priority+=1;
							$recipients = array();
							$params["ApprovalNeeded"] = 1;
							$params["Priority"] = $Priority;
							foreach($GM as $m) {
								$EmpFound = false;
								for($i=0;$i<count($recipients);$i++) {
									if ($recipients[$i]["NAMA"]==$m->employee_name && $recipients[$i]["EMAIL"]==(($m->email_address==null)?$m->email:$m->email_address)) {
										$EmpFound = true;
									}
								}
								if ($EmpFound==false) {
									array_push($recipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
									array_push($allRecipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
								}

								$MobileFound = false;
								$WA = $this->HelperModel->MobileWithCountryCode($m->mobile);
								if ($WA!="") {
									for($i=0;$i<count($mobiles);$i++) {
										if ($mobiles[$i]==$WA) {
											$MobileFound = true;
										}
									}
									if ($MobileFound==false) {
										array_push($mobiles, $WA);
									}
								}

							}
							//die(json_encode($recipients));
							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user, $REQUESTID["requestid"]);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];
						}

						$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);
						$sendWA = $this->MsDealerModelOld->ProsesWhatsapp("CREDIT LIMIT", $params, $mobiles, $user);
					}
					//Akhir Coding untuk MO
				} else {
					//30 Juni 2021: Per 1 Juli 2021, DM balik ke Prosedur Approval MO
					//Tradisional 
					if ($params["KenaikanCL"]<300000000) {
						$Priority = 1;
						$lanjutBM = false;
						//$recipients = array();
						$params["ApprovalNeeded"] = 1;
						$params["Priority"] = $Priority;
						// die("Naik CL di bawah 300jt");

						if ($params["BranchDB"]=="JKT" && $params["NamaDB"]=="JAKARTA") {
							// die("Sales Managers:<br>".json_encode($SalesManagers));
							foreach($SalesManagers as $m) {
								$EmpFound = false;
								for($i=0;$i<count($recipients);$i++) {
									if ($recipients[$i]["NAMA"]==$m->employee_name && $recipients[$i]["EMAIL"]==(($m->email_address==null)?$m->email:$m->email_address)) {
										$EmpFound = true;
									}
								}
								if ($EmpFound==false) {
									array_push($recipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
									array_push($allRecipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
								}

								$MobileFound = false;
								$WA = $this->HelperModel->MobileWithCountryCode($m->mobile);
								if ($WA!="") {
									for($i=0;$i<count($mobiles);$i++) {
										if ($mobiles[$i]==$WA) {
											$MobileFound = true;
										}
									}
									if ($MobileFound==false) {
										array_push($mobiles, $WA);
									}
								}
							}

							// die(json_encode($recipients));
							// die(json_encode($allRecipients));
							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							if ($IsSuccess=="SUKSES") {
								$params["RequestID"] = $REQUESTID["requestid"];
								$params["ExpiryDate"] = $REQUESTID["expirydate"];

								//Khusus wilayah OUTLER dihardcode kirim request ke DENI PRIHATIN
								$Kajul = $this->SalesManagerModel->GetKajul($params["Wilayah"]);
								if ($Kajul!=null) array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));

								$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);
								$sendWA = $this->MsDealerModelOld->ProsesWhatsapp("CREDIT LIMIT", $params, $mobiles, $user);
							} else {
								die(json_encode($REQUESTID));
							}
						} else if ($params["BranchDB"]=="JKT" && ($params["NamaDB"]=="BOGOR" || $params["NamaDB"]=="KARAWANG")) {
							//Khusus BOGOR dan KARAWANG under JAKARTA dihardcode kirim request ke FANDY
							$zen = API_ZEN."/ZenAPI/GetEmployee?userid=2661";
							$GetKacab = json_decode(file_get_contents($zen), true);
							if ($GetKacab["result"]=="sukses") {
								$Employee = $GetKacab["data"];
								array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
								array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

								$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
								if ($WA != "") array_push($mobiles, $WA);
							}
							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];

							$Kajul = $this->SalesManagerModel->GetKajul($params["Wilayah"]);
							if ($Kajul!=null) array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
							$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);
							$sendWA = $this->MsDealerModelOld->ProsesWhatsapp("CREDIT LIMIT", $params, $mobiles, $user);

						} else {
							$zen = API_ZEN."/ZenAPI/GetBranch?branch=".urlencode($user["BranchID"]);
							$GetBranch = json_decode(file_get_contents($zen), true);
							if ($GetBranch["result"]=="sukses") {
								$BranchHead = $GetBranch["data"]["BRANCHHEAD"];
								if ($BranchHead!="") {
									$zen = API_ZEN."/ZenAPI/GetEmployee?userid=".urlencode($BranchHead);
									$GetKacab = json_decode(file_get_contents($zen), true);

									if ($GetKacab["result"]=="sukses") {
										$Employee = $GetKacab["data"];
										array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
										array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

										$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
										if ($WA != "") array_push($mobiles, $WA);
									}
								}
							}
							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];

							$Kajul = $this->SalesManagerModel->GetKajul($params["Wilayah"]);
							if ($Kajul!=null) array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
							$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);						
							$sendWA = $this->MsDealerModelOld->ProsesWhatsapp("CREDIT LIMIT", $params, $mobiles, $user);
						}

					} else if ($params["KenaikanCL"]<=1000000000) {
						$Priority = 1;
						$lanjutBM = true;
						//$recipients = array();
						$params["ApprovalNeeded"] = 1;
						$params["Priority"] = $Priority;

						if ($params["BranchDB"]=="JKT" && $params["NamaDB"]=="JAKARTA") {

							$lanjutBM = true;
							$params["ApprovalNeeded"] = 1;

							foreach($SalesManagers as $m) {
								$EmpFound = false;
								for($i=0;$i<count($recipients);$i++) {
									if ($recipients[$i]["NAMA"]==$m->employee_name && $recipients[$i]["EMAIL"]==(($m->email_address==null)?$m->email:$m->email_address)) {
										$EmpFound = true;
									}
								}
								if ($EmpFound==false) {
									array_push($recipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
									array_push($allRecipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
								}

								$MobileFound = false;
								$WA = $this->HelperModel->MobileWithCountryCode($m->mobile);
								if ($WA!="") {
									for($i=0;$i<count($mobiles);$i++) {
										if ($mobiles[$i]==$WA) {
											$MobileFound = true;
										}
									}
									if ($MobileFound==false) {
										array_push($mobiles, $WA);
									}
								}
							}

							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];

							//Khusus wilayah OUTLER dihardcode kirim request ke DENI PRIHATIN
							$Kajul = $this->SalesManagerModel->GetKajul($params["Wilayah"]);
							if ($Kajul!=null) array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));

							// $sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);
							// $sendWA = $this->MsDealerModelOld->ProsesWhatsapp("CREDIT LIMIT", $params, $mobiles, $user);

						} else if ($params["BranchDB"]=="JKT" && ($params["NamaDB"]=="BOGOR" || $params["NamaDB"]=="KARAWANG")) {
							//Khusus BOGOR dan KARAWANG under JAKARTA dihardcode kirim request ke FANDY
							$zen = API_ZEN."/ZenAPI/GetEmployee?userid=2661";
							$GetKacab = json_decode(file_get_contents($zen), true);
							if ($GetKacab["result"]=="sukses") {
								$Employee = $GetKacab["data"];
								array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
								array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

								$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
								if ($WA != "") array_push($mobiles, $WA);
							}
							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];

							$Kajul = $this->SalesManagerModel->GetKajul($params["Wilayah"]);
							if ($Kajul!=null) array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));

						} else {
							$zen = API_ZEN."/ZenAPI/GetBranch?branch=".urlencode($user["BranchID"]);
							$GetBranch = json_decode(file_get_contents($zen), true);
							if ($GetBranch["result"]=="sukses") {
								$BranchHead = $GetBranch["data"]["BRANCHHEAD"];
								if ($BranchHead!="") {
									$zen = API_ZEN."/ZenAPI/GetEmployee?userid=".urlencode($BranchHead);
									$GetKacab = json_decode(file_get_contents($zen), true);

									if ($GetKacab["result"]=="sukses") {
										$Employee = $GetKacab["data"];
										array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
										array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

										$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
										if ($WA != "") array_push($mobiles, $WA);
									}
								}
							}
							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];

							$Kajul = $this->SalesManagerModel->GetKajul($params["Wilayah"]);
							if ($Kajul!=null) array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
						}

						if ($lanjutBM==true) {
							$Priority += 1;
							$recipients = array();
							$params["ApprovalNeeded"] = 1;
							$params["Priority"] = $Priority;
							if ($params["Divisi"]=="CO&SANITARY") {
								foreach($BrandManagers as $m) {
									if (htmlspecialchars_decode($m->division)=="CO&SANITARY") {
										array_push($recipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
										array_push($allRecipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
										
										$MobileFound = false;
										$WA = $this->HelperModel->MobileWithCountryCode($m->mobile);
										if ($WA!="") {
											array_push($mobiles, $m->mobile);
										}
									}
								}
							} else {				
								foreach($BrandManagers as $m) {
									$EmpFound = false;
									for($i=0;$i<count($recipients);$i++) {
										if ($recipients[$i]["NAMA"]==$m->employee_name && $recipients[$i]["EMAIL"]==(($m->email_address==null)?$m->email:$m->email_address)) {
											$EmpFound = true;
										}
									}
									if ($EmpFound==false) {
										array_push($recipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
										array_push($allRecipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
									}
		
									$MobileFound = false;
									$WA = $this->HelperModel->MobileWithCountryCode($m->mobile);
									if ($WA!="") {
										for($i=0;$i<count($mobiles);$i++) {
											if ($mobiles[$i]==$WA) {
												$MobileFound = true;
											}
										}
										if ($MobileFound==false) {
											array_push($mobiles, $WA);
										}
									}

								}
							}

							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user, $REQUESTID["requestid"]);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];
							$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);
							$sendWA = $this->MsDealerModelOld->ProsesWhatsapp("CREDIT LIMIT", $params, $mobiles, $user);
						}
					} else  {				
						$Priority = 1;
						$lanjutBM = true;
						$lanjutGM = true;
						$recipients = array();
						$allRecipients = array();
						$params["ApprovalNeeded"] = 1;
						$params["Priority"] = $Priority;
						
						if ($params["BranchDB"]=="JKT" && $params["NamaDB"]=="JAKARTA") {
							$lanjutBM = true;
							$params["ApprovalNeeded"] = 1;

							foreach($SalesManagers as $m) {
								$EmpFound = false;
								for($i=0;$i<count($recipients);$i++) {
									if ($recipients[$i]["NAMA"]==$m->employee_name && $recipients[$i]["EMAIL"]==(($m->email_address==null)?$m->email:$m->email_address)) {
										$EmpFound = true;
									}
								}
								if ($EmpFound==false) {
									array_push($recipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
									array_push($allRecipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
								}

								$MobileFound = false;
								$WA = $this->HelperModel->MobileWithCountryCode($m->mobile);
								if ($WA!="") {
									for($i=0;$i<count($mobiles);$i++) {
										if ($mobiles[$i]==$WA) {
											$MobileFound = true;
										}
									}
									if ($MobileFound==false) {
										array_push($mobiles, $WA);
									}
								}
							}
							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];

							$Kajul = $this->SalesManagerModel->GetKajul($params["Wilayah"]);
							if ($Kajul!=null) array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));

							// echo(json_encode($recipients));
							// echo("<br>");

						} else if ($params["BranchDB"]=="JKT" && ($params["NamaDB"]=="BOGOR" || $params["NamaDB"]=="KARAWANG")) {
							//Khusus BOGOR dan KARAWANG under JAKARTA dihardcode kirim request ke FANDY
							$zen = API_ZEN."/ZenAPI/GetEmployee?userid=2661";
							$GetKacab = json_decode(file_get_contents($zen), true);
							if ($GetKacab["result"]=="sukses") {
								$Employee = $GetKacab["data"];
								array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
								array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

								$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
								if ($WA != "") array_push($mobiles, $WA);
							}
							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];

							$Kajul = $this->SalesManagerModel->GetKajul($params["Wilayah"]);
							if ($Kajul!=null) array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));

						} else {
							$zen = API_ZEN."/ZenAPI/GetBranch?branch=".urlencode($user["BranchID"]);
							$GetBranch = json_decode(file_get_contents($zen), true);
							if ($GetBranch["result"]=="sukses") {
								$BranchHead = $GetBranch["data"]["BRANCHHEAD"];
								if ($BranchHead!="") {
									$zen = API_ZEN."/ZenAPI/GetEmployee?userid=".urlencode($BranchHead);
									$GetKacab = json_decode(file_get_contents($zen), true);

									if ($GetKacab["result"]=="sukses") {
										$Employee = $GetKacab["data"];
										array_push($recipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));
										array_push($allRecipients, array("NAMA"=>$Employee["NAME"], "EMAIL"=>$Employee["EMAIL"], "USEREMAIL"=>$Employee["EMAIL"]));

										$WA = $this->HelperModel->MobileWithCountryCode($Employee["WHATSAPP"]);
										if ($WA != "") array_push($mobiles, $WA);
									}
								}
							}
							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];

							$Kajul = $this->SalesManagerModel->GetKajul($params["Wilayah"]);
							if ($Kajul!=null) array_push($allRecipients, array("NAMA"=>$Kajul->employee_name, "EMAIL"=>(($Kajul->email_address==null)?$Kajul->email:$Kajul->email_address), "USEREMAIL"=>$Kajul->useremail));
						}

						if ($lanjutBM==true) {
							$Priority += 1;
							$recipients = array();
							$params["ApprovalNeeded"] = 1;
							$params["Priority"] = $Priority;
							foreach($BrandManagers as $m) {
								$EmpFound = false;
								for($i=0;$i<count($recipients);$i++) {
									if ($recipients[$i]["NAMA"]==$m->employee_name && $recipients[$i]["EMAIL"]==(($m->email_address==null)?$m->email:$m->email_address)) {
										$EmpFound = true;
									}
								}
								if ($EmpFound==false) {
									array_push($recipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
									array_push($allRecipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
								}

								$MobileFound = false;
								$WA = $this->HelperModel->MobileWithCountryCode($m->mobile);
								if ($WA!="") {
									for($i=0;$i<count($mobiles);$i++) {
										if ($mobiles[$i]==$WA) {
											$MobileFound = true;
										}
									}
									if ($MobileFound==false) {
										array_push($mobiles, $WA);
									}
								}
							}
							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user, $REQUESTID["requestid"]);
							$IsSuccess = strtoupper($REQUESTID["result"]);

						}

						if ($lanjutGM==true) {
							$Priority+=1;
							$recipients = array();
							$params["ApprovalNeeded"] = 1;
							$params["Priority"] = $Priority;
							foreach($GM as $m) {
								$EmpFound = false;
								for($i=0;$i<count($recipients);$i++) {
									if ($recipients[$i]["NAMA"]==$m->employee_name && $recipients[$i]["EMAIL"]==(($m->email_address==null)?$m->email:$m->email_address)) {
										$EmpFound = true;
									}
								}
								if ($EmpFound==false) {
									array_push($recipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
									array_push($allRecipients, array("NAMA"=>$m->employee_name, "EMAIL"=>(($m->email_address==null)?$m->email:$m->email_address), "USEREMAIL"=>$m->useremail));
								}

								$MobileFound = false;
								$WA = $this->HelperModel->MobileWithCountryCode($m->mobile);
								if ($WA!="") {
									for($i=0;$i<count($mobiles);$i++) {
										if ($mobiles[$i]==$WA) {
											$MobileFound = true;
										}
									}
									if ($MobileFound==false) {
										array_push($mobiles, $WA);
									}
								}
							}
							//die(json_encode($recipients));
							$REQUESTID = $this->MsDealerModelOld->RequestCL($params, $email_content, $recipients, $user, $REQUESTID["requestid"]);
							$IsSuccess = strtoupper($REQUESTID["result"]);
							$params["RequestID"] = $REQUESTID["requestid"];
							$params["ExpiryDate"] = $REQUESTID["expirydate"];

							$sendEmail = $this->MsDealerModelOld->EmailRequest("CREDIT LIMIT", $params, $email_content, $allRecipients, $user);
							$sendWA = $this->MsDealerModelOld->ProsesWhatsapp("CREDIT LIMIT", $params, $mobiles, $user);
							// echo(json_encode($recipients));
							// echo("<br>");
						}

					}
				}
				// die(json_encode($recipients));
				$data = array();
		        $data["alert"] = "";

				$db_id = $_SESSION['databaseID'];
				$API = $_SESSION["conn"]->AlamatWebService;
				if ($this->test_mode==true) $API = HO;

				$URL = $API.API_BKT."/MasterDealer/GetListDealerForCL?api=APITES";
				$HTTPRequest = HttpGetRequest($URL, $API.API_BKT, "AMBIL LIST DEALER");
				// die($URL);
		        $GetDealer = json_decode($HTTPRequest,true);
		        // die(json_encode($GetDealer));
		        if ($GetDealer["result"]=="sukses"){
		        	$ListDealer = $GetDealer["data"];
					$dealers = array();
					for($i=0; $i<count($ListDealer); $i++){
						array_push($dealers, trim($ListDealer[$i]["NM_PLG"])." - ".trim($ListDealer[$i]["KD_PLG"])." - ".trim($ListDealer[$i]["WILAYAH"]));
					}

					$data["ListDealer"] = $ListDealer;
			        $data["Dealers"] = $dealers;
					if (strtoupper($IsSuccess)=="SUKSES") {
				        $data["alert"] = "REQUEST CL SAVED & EMAILED TO KACAB/MANAGER";
					} else {
				        $data["alert"] = strtoupper($REQUESTID["ket"]);
					}

			    } else {
					$data = array();
			        $data["alert"] = "REQUEST CL SAVED & EMAILED TO KACAB/MANAGER";
			        $data["alert"].= "\nGAGAL MENGAMBIL LIST DEALER KE DB BHAKTI";
			    }

		        if (date("Ymd")>=date("Ymd", strtotime($this->newRuleDate))) {
			        $this->RenderView('MsDealerCreditLimitView', $data);
			    } else {
			        $this->RenderView('MsDealerCreditLimitViewOld', $data);			    	
			    }

			}
		} else {
			redirect("MsDealer/CreditLimit?alert=".urlencode($GetCLGroup["ket"]));
		}
    }
}