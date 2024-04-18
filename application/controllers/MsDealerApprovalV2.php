<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MsDealerApprovalV2 extends MY_Controller {

	public $alert = "";
	public $test_mode = false;

	function __construct()
	{
		parent::__construct();
		$this->load->model('MsDealerModel');
		$this->load->model('SalesManagerModel');
		$this->load->model('GzipDecodeModel');
		$this->load->model('MsConfigModel');
		$this->load->model('UserModel');
		$this->load->library("email");
		$this->load->helper('url');
	}

	//Unlock Toko Terkunci 
    public function ProsesRequestUnlock()
    {
    	$data = array();
    	$alert = urldecode($this->input->get("alert"));
    	$KodeRequest = urldecode($this->input->get("kdreq"));
    	$Approver = urldecode($this->input->get("empid"));
    	$End = urldecode($this->input->get("end"));
    	$Note = urldecode($this->input->get("note"));
    	$mc = 0;
		$mc = (!$this->input->get("mc") ? 0 : $this->input->get("mc"));

		$isAutoApproved = false;
		$CheckAutoApproved = $this->MsConfigModel->GetConfigValue("REQUEST UNLOCK", "AUTO APPROVED", "ALL");
		// die(json_encode($CheckAutoApproved));

		if (count($CheckAutoApproved)>0) {
			// die("Auto Approved");
			$isAutoApproved = $CheckAutoApproved[0]->ConfigValue;
		}

		$REQUEST = $this->MsDealerModel->GetRequestUnlockToko($KodeRequest);
		if ($REQUEST!=null) {
			if ($REQUEST->IsApproved==0 && $isAutoApproved) {
				$RequestNo = $KodeRequest;
				$UserEmail = $Approver;
				$Note = "";
				$EndDate = $End;
				// die("approve unlock");
																		  
				$this->MsDealerModel->ApproveUnlock($RequestNo, $UserEmail, $EndDate, $Note, $mc);
			} else {
		    	// die(json_encode($REQUEST));
		    	$data["APPROVER"] = $Approver;
		    	$data["REQUEST"] = $REQUEST;
		    	//$data["REQBY"] = '';

		        // $user = $this->UserModel->getUserDataByEmail($REQUEST->RequestBy);
			    // $zen = API_ZEN."/ZenAPI/GetEmployee?userid=".urlencode($user->AlternateID);
			    // $GetEmployee = json_decode(file_get_contents($zen, true));
				// if ($GetEmployee->result=="sukses") {
				// 	// die(json_encode($GetEmployee->data));
				// 	$data["REQBY"] = $GetEmployee->data;
				// }
		    	$user = $this->UserModel->Get2($REQUEST->RequestBy);
				$data["REQBY"] = $user;
				// die(json_encode($user));
		    	$data["ALERT"] = $alert;

		    	if ($REQUEST->RequestDate==null) {
		    		$data["EXPIRED"] = false;
		    	} else if (date("Ymd",strtotime($REQUEST->RequestDate))!=date("Ymd")) {
					$data["EXPIRED"] = true;
			    } else {
			    	$data["EXPIRED"] = false;	    	
			    }
			    $data["ENDDATE"] = $End;
			    $data["NOTE"] = $Note;
			    $data["mc"] = $mc;

			    if ($mc==0) {
		        	//$this->SetTemplate('template/notemplate');
			    	$this->load->view("MsDealerRequestUnlockView", $data);
			    } else {
			    	$this->SetTemplate('template/template');
			    	$this->RenderView("MsDealerRequestUnlockView", $data);
			    }
			}
		} else {
			die("REQUEST UNLOCK TIDAK DITEMUKAN");
		}
    }

	public function ApproveRejectUnlock()
	{
		$post = $this->PopulatePost();
		$RequestNo = $post["TxtRequestNo"];
		$UserEmail = $post["TxtUserEmail"];
		$UserPwd = (ISSET($post["TxtUserPwd"])) ? $post["TxtUserPwd"] : "";
		$Note = $post["TxtNote"];
		$EndDate = $post["EndDate"];
		//die($EndDate);
    	$mc = 0;
		$mc = (!$this->input->get("mc") ? 0 : $this->input->get("mc"));

		// if ($this->input->get("mc")!==1) {
		// 	$mc = 1;
		// }

		$logged_in = false;
		if((ISSET($_SESSION['logged_in'])) && ($_SESSION['logged_in']['useremail']==$UserEmail)){
			$logged_in = true;
		}

		if ($UserPwd=="" && $logged_in==false) {
			redirect("MsDealerApproval/ProsesRequestUnlock?kdreq=".urlencode($RequestNo)."&empid=".urlencode($UserEmail).
					 "&alert=".urlencode("PASSWORD BELUM DIINPUT")."&note=".urlencode($Note)."&end=".urlencode($EndDate)."&mc=".$mc);
		} else {
			//$zen = API_ZEN."/ZenAPI/CheckLogin?user=".urlencode($UserEmail)."&pwd=".urlencode(md5($UserPwd));
			//$res = json_decode(file_get_contents($zen, true));

			if($logged_in==false){
				$wUser = array(
					'UserEmail' => $UserEmail,
					'UserPassword' => md5($UserPwd),
				);
				$getUser = $this->UserModel->login2($wUser);
				if($getUser!=null || "SKIP"=="NOT SKIP"){
					$logged_in = true;
				}
			}
			//if ($res->result == "SUKSES" || 1==1){
			//if ($res->result == "SUKSES" || "SKIP"=="NOT SKIP"){
			// if($getUser!=null || "SKIP"=="NOT SKIP"){
			if($logged_in == true) {
				if (isset($_POST["btnApprove"])) {
					$this->MsDealerModel->ApproveUnlock($RequestNo, $UserEmail, $EndDate, $Note, $mc);
				} else {
					$this->MsDealerModel->RejectUnlock($RequestNo, $UserEmail, $EndDate, $Note, $mc);
				}
				// $this->NotifikasiApprovalUnlockToko($RequestNo);
			} else {
				redirect("MsDealerApproval/ProsesRequestUnlock?kdreq=".urlencode($RequestNo)."&empid=".urlencode($UserEmail).
						 "&alert=".urlencode("PASSWORD SALAH")."&note=".urlencode($Note)."&end=".urlencode($EndDate)."&mc=".$mc);
			}
		}
	}

	public function autoApproveUnlock($KodeRequestMD5)
	{
		$REQUEST = $this->MsDealerModel->GetRequestUnlockTokoMD5($KodeRequestMD5);
		// die(json_encode($REQUEST));
		if ($REQUEST->IsApproved==0) {
			$RequestNo = $REQUEST->RequestID;
			$UserEmail = "AUTO APPROVED";
			$Note="AUTO APPROVED";
			$EndDate = date("Y-m-d", strtotime("+7 day"));
			// die("approve unlock");
			$this->MsDealerModel->ApproveUnlock($RequestNo, $UserEmail, $EndDate, $Note, 0, 1);

	        $hasil = json_encode(array("result"=>"sukses", "error"=>""));
	        header('HTTP/1.1: 200');
	        header('Status: 200');
	        header('Content-Length: '.strlen($hasil));
	        exit($hasil);
		} else {
	        $hasil = json_encode(array("result"=>"sukses", "error"=>"request sudah diapprove"));
	        header('HTTP/1.1: 200');
	        header('Status: 200');
	        header('Content-Length: '.strlen($hasil));
	        exit($hasil);

		}
	}


    public function EmailApprovalUnlock($KodeRequest) 
    {
    	$rq = $this->MsDealerModel->GetRequestUnlockToko($KodeRequest);
		if (SEND_EMAIL=="ON")
		{
			$e_content = "<b>".$rq->AppName."</b> <b>".(($rq->IsApproved==1)?"<font color='#004d00'>MENYETUJUI</font>":"<font color='#b30000'>MENOLAK</font>")."</b> PERMINTAAN UNLOCK DEALER TERKUNCI<br>";
			$e_content.= "Nama Dealer : <b>".$rq->NmPlg."</b><br>";
			$e_content.= "Kode Dealer : <b>".$rq->KdPlg."</b><br>";
			$e_content.= "Wilayah : <b>".$rq->Wilayah."</b><br>";
			$e_content.= "No Ref : <b>".$KodeRequest."</b><br>";
			if ($rq->IsApproved==1) {
				$e_content.= "Unlock Toko Berlaku S/D Tgl ".date("d-M-Y", strtotime($rq->UnlockEnd));
			}
			$e_content.= "<br/>";

			$this->email->from("bitautoemail.noreply@gmail.com", "BHAKTI.CO.ID AUTO-EMAIL");
			$recipients = (($rq->ReqEmail!="") ? $rq->ReqEmail : $rq->RequestBy);

			if (TEST_MODE == "TRUE")
				$this->email->to(TEST_EMAIL); 
			else
				$this->email->to($recipients); 

			$plg = $this->MsDealerModel->ReplaceChars($rq->NmPlg);
			$subject = substr("Request Unlock Toko ".$plg,0,40)." ".date("Ymd");
			$this->email->subject($subject);
			$this->email->message($e_content);	
			$this->email->send();
		    $this->email->clear();
		}
		return true;
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
		$mc = 0;
		$mc = (!$this->input->get("mc") ? 0 : $this->input->get("mc"));
		// if ($this->input->get("mc")==null) {
		// 	$mc = $this->input->get("mc");
		// }
		// die("&mc=".$mc);

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
		$data["content_html"] = "";

		$GetRequest = $this->MsDealerModel->GetRequestForProcess($RequestType, $RequestNo, "");
		// die(json_encode($GetRequest));
		if ($GetRequest["result"]=="sukses") {
			$REQ = $GetRequest["data"];

			$content_html = "<b>".$REQ->RequestByName."</b> mengajukan permohonan ".$StrType." sebagai berikut :<br><br>";
			$content_html.= "No Request : <b>".$RequestNo."</b><br>";
			$content_html.= "Waktu Request : <b>".date("d-M-Y H:i:s", strtotime($REQ->RequestDate))."</b><br><br>";
			
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
			

			$request_content = "";
			// die(date("Ymd",strtotime($REQ->ExpiryDate)));

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
			} else if ($GetRequest["status"]=="EXPIRED" || date("Ymd",strtotime($REQ->ExpiryDate))<date("Ymd")) {
				$GetRequest["status"]="EXPIRED";
				$content_html .= "<br><font color='red' size='+1'><b>".strtoupper("Request ".$StrType." Ini Telah Kadaluarsa")."</b></font><br>";
			} else {

			}

			$data["data"] = $GetRequest;
			$data["viewOnly"] = $ViewOnly;
			$data["mc"] = $mc;

			$data["approver"]= $this->MsDealerModel->GetRequestApproverList($RequestType, $RequestNo);
			$data["content_html"] = $content_html;
			$data["request_table"] = $request_table;
	        //$this->load->view("MsDealerRequestCLView", $data);

	        // die(json_encode($data));
	        // die("mc:".$mc);
	        if ($mc==0) {
		        $this->SetTemplate('template/notemplate');
		        $this->load->view("MsDealerRequestView", $data);
		    } else {
		    	$this->SetTemplate('template/template');
		        $this->RenderView("MsDealerRequestView", $data);
		    }
		} else {
			// die("here");
			$data = array();
			$data["RequestNo"] = $RequestNo;
			$data["RequestType"] = $RequestType;
			$data["alert"] = $Alert;

			$data["content_html"] = '<script language="javascript">';
			if ($GetRequest["status"]=="INVALID REQUEST") {
		        $data["content_html"].= 'alert("INVALID REQUEST: REQUEST '.strtoupper($StrType).' TIDAK DITEMUKAN")';
		    } else {
		    	$data["content_html"].= 'alert("INVALID REQUEST: ANDA TIDAK MEMILIKI AKSES UNTUK REQUEST '.strtoupper($StrType).' INI")';
		    }
	        $data["content_html"].= '</script>';
	        $data["alert"] = 'INVALID REQUEST: REQUEST '.strtoupper($StrType).' TIDAK DITEMUKAN';
	        // $data["content_html"].= "<script>window.close();</script>";
	        // die(json_encode($data));

	        if ($mc==0) {
		        $this->SetTemplate('template/notemplate');
		        $this->load->view("MsDealerRequestView", $data);		
		    } else {
		    	$this->SetTemplate('template/template');
		        $this->RenderView("MsDealerRequestView", $data);
		    }
		}
	}

	public function ProcessRequestV2()
	{
		$RequestNo = urldecode($this->input->get("id"));
		$Type = urldecode($this->input->get("type"));
		$Alert = urldecode($this->input->get("alert"));
		if ($this->input->get("viewonly") !== "yes") {
			$ViewOnly = false;
		} else {
			$ViewOnly = true;
		}
		$mc = 0;
		$mc = (!$this->input->get("mc") ? 0 : $this->input->get("mc"));

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
		$data["content_html"] = "";

		$GetRequest = $this->MsDealerModel->GetRequestForProcess($RequestType, $RequestNo, "");
		// die(json_encode($GetRequest));
		if ($GetRequest["result"]=="sukses") {
			$REQ = $GetRequest["data"];

			$content_html = "<b>".$REQ->RequestByName."</b> mengajukan permohonan ".$StrType." sebagai berikut :<br><br>";
			$content_html.= "No Request : <b>".$RequestNo."</b><br>";
			$content_html.= "Waktu Request : <b>".date("d-M-Y H:i:s", strtotime($REQ->RequestDate))."</b><br><br>";
			
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
			

			$request_content = "";
			// die(date("Ymd",strtotime($REQ->ExpiryDate)));

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
			} else if ($GetRequest["status"]=="EXPIRED" || date("Ymd",strtotime($REQ->ExpiryDate))<date("Ymd")) {
				$GetRequest["status"]="EXPIRED";
				$content_html .= "<br><font color='red' size='+1'><b>".strtoupper("Request ".$StrType." Ini Telah Kadaluarsa")."</b></font><br>";
			} else {

			}

			$data["data"] = $GetRequest;
			$data["viewOnly"] = $ViewOnly;
			$data["mc"] = $mc;

			$data["approver"]= $this->MsDealerModel->GetRequestApproverList($RequestType, $RequestNo);
			$data["content_html"] = $content_html;
			$data["request_table"] = $request_table;

			$data["button_approve"] = "";
			$data["button_reject"] = "";
	        $this->RenderView("MsDealerRequestViewV2", $data);
		} else {
			// die("here");
			$data = array();
			$data["RequestNo"] = $RequestNo;
			$data["RequestType"] = $RequestType;
			$data["alert"] = $Alert;

			$data["content_html"] = '<script language="javascript">';
			if ($GetRequest["status"]=="INVALID REQUEST") {
		        $data["content_html"].= 'alert("INVALID REQUEST: REQUEST '.strtoupper($StrType).' TIDAK DITEMUKAN")';
		    } else {
		    	$data["content_html"].= 'alert("INVALID REQUEST: ANDA TIDAK MEMILIKI AKSES UNTUK REQUEST '.strtoupper($StrType).' INI")';
		    }
	        $data["content_html"].= '</script>';
	        $data["alert"] = 'INVALID REQUEST: REQUEST '.strtoupper($StrType).' TIDAK DITEMUKAN';
	        // $data["content_html"].= "<script>window.close();</script>";
	        // die(json_encode($data));

	    	$this->SetTemplate('template/template');
	        $this->RenderView("MsDealerRequestView", $data);
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
		$mc = 0;
		$mc = (!$this->input->get("mc") ? 0 : $this->input->get("mc"));
		// die("&mc=".$mc);

		$tipe = "";
		if ($RequestType=="CREDIT LIMIT") {
			$tipe = "cl";
		} else if ($RequestType=="CBD OFF") {
			$tipe = "cbdoff";
		} else if ($RequestType=="CBD ON") {
			$tipe = "cbdon";
		}
		// die($UserEmail);
		
		if ($UserEmail=="-" || $UserEmail=="") {
			redirect("MsDealerApproval/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&alert=".urlencode("USER BELUM DIPILIH")."&mc=".$mc);
		} else if ($UserPwd!="") {
			//$zen = API_ZEN."/ZenAPI/CheckLogin?user=".urlencode($UserEmail)."&pwd=".urlencode(md5($UserPwd));
			//$res = json_decode(file_get_contents($zen, true));

			$result = $this->UserModel->login($UserEmail,$UserPwd); 
	        if($result["result"]=="success" || $this->test_mode==true) {  
			//if ($res->result == "SUKSES" || $this->test_mode==true){
				if (isset($_POST["btnApprove"])) {
					$doApprove = $this->MsDealerModel->ApproveRequest($RequestType, $RequestNo, $UserEmail, $Note);
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
										// $BhaktiFlag = json_decode(file_get_contents($URL), true);
										$BhaktiFlag = file_get_contents($URL);
										$BhaktiFlag = $this->GzipDecodeModel->_decodeGzip_true($BhaktiFlag);
										//die($URL."<br><br>".json_encode($Bhakti));
									} else if ($RequestType=="CBD OFF" || $RequestType=="CBD ON") {
										$URL = $URL.API_BKT."/MasterDealer/ApproveRejectCBD2?api=APITES".
												"&hsid=".urlencode($RequestNo).
												"&kdplg=".urlencode($REQ->AddInfo1Value)."&cbd=".urlencode($RequestType).
												"&tgl=".urlencode(date("Y-m-d",strtotime($REQ->AddInfo6Value))).
												"&sts=APPROVED&exp=".urlencode(date("Y-m-d",strtotime($REQ->ExpiryDate))).
												"&reqby=".urlencode($REQ->RequestByName)."&reqdate=".urlencode(date("Y-m-d H:i:s",strtotime($REQ->RequestDate))).
												"&appby=".urlencode($REQ->ApprovedByName)."&appdate=".urlencode(date("Y-m-d H:i:s",strtotime($REQ->ApprovedDate))).
												"&svr=".urlencode($DB->Server)."&db=".urlencode($DB->Database).
												"&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD);
										//die($URL);
										// $BhaktiFlag = json_decode(file_get_contents($URL), true);
										$BhaktiFlag = file_get_contents($URL);
										$BhaktiFlag = $this->GzipDecodeModel->_decodeGzip_true($BhaktiFlag);


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
										// $BhaktiFlag = json_decode(file_get_contents($URL, false, $context), true);
										$BhaktiFlag = file_get_contents($URL, false , $context);
										$BhaktiFlag = $this->GzipDecodeModel->_decodeGzip_true($BhaktiFlag);
										if ($BhaktiFlag === FALSE) {
											$BhaktiFlag["result"] = "gagal";
										}
									}

									if ($BhaktiFlag["result"]=="sukses") {
										$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"FINISHED");
									} else {
										$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"PENDING");	
									}
								} else {
									$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"PENDING");
								}
							}
							$email_content = $this->CreateResponseEmailContent($RequestType, "APPROVED", $REQ);
							$doEmail = $this->MsDealerModel->EmailResponseRequest($RequestType, $REQ, $email_content);
							if ($mc==0) {
								redirect("MsDealerApproval/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&user=".urlencode($UserEmail)."&alert=".urlencode("APPROVE BERHASIL")."&mc=".$mc);
							} else {
								redirect("Dashboard");
							}
						} else {
							if ($mc==0) {
								redirect("MsDealerApproval/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&user=".urlencode($UserEmail)."&alert=".urlencode("APPROVE BERHASIL")."&mc=".$mc);
							} else {
								redirect("Dashboard");
							}
						}
					} else {
						redirect("MsDealerApproval/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&alert=".urlencode("APPROVE GAGAL")."&mc=".$mc);
					}

				} else {
					//die($UserEmail);
					$doReject = $this->MsDealerModel->RejectRequest($RequestType, $RequestNo, $UserEmail, $Note);
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
											// $BhaktiFlag = json_decode(file_get_contents($URL, false, $context), true);
											$BhaktiFlag = file_get_contents($URL, false, $context);
											$BhaktiFlag = $this->GzipDecodeModel->_decodeGzip_true($BhaktiFlag);
											if ($BhaktiFlag === FALSE) {
												$BhaktiFlag["result"] = "gagal";
											}
										}

										if ($BhaktiFlag["result"]=="sukses") {
											$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"FINISHED");
										} else {
											$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"PENDING");	
										}
									} else {
										$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo, "PENDING");
									}
								}
							} else {
								$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo, "FINISHED");
							}
						}
						$email_content = $this->CreateResponseEmailContent($RequestType, "REJECTED", $REQ);
						$doEmail = $this->MsDealerModel->EmailResponseRequest($RequestType, $REQ, $email_content);
						if ($mc==0) {
							redirect("MsDealerApproval/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&alert=".urlencode("REJECT BERHASIL")."&mc=".$mc);
						} else {
							redirect("Dashboard");
						}
					} else {
						redirect("MsDealerApproval/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&alert=".urlencode("REJECT GAGAL")."&mc=".$mc);
					}
				}
			} else {
				redirect("MsDealerApproval/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&alert=".urlencode("PASSWORD SALAH")."&mc=".$mc);
			}
		} else {
			redirect("MsDealerApproval/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&alert=".urlencode("PASSWORD BELUM DIISI")."&mc=".$mc);
		}
	}

	public function ApproveRejectRequestV2(){
		$post = $this->PopulatePost();
		$RequestNo = $post["TxtRequestNo"];
		$RequestType = $post["TxtRequestType"];
		// $UserEmail = $post["TxtUserEmail"];
		// $UserPwd = $post["TxtUserPwd"];
		// $Note = $post["TxtNote"];
		$UserEmail = $_SESSION["logged_in"]["useremail"];
		$UserPwd  ="";
		$Note = "";
		//die($UserPwd);
		$mc = 0;
		$mc = (!$this->input->get("mc") ? 0 : $this->input->get("mc"));
		// die("&mc=".$mc);

		$tipe = "";
		if ($RequestType=="CREDIT LIMIT") {
			$tipe = "cl";
		} else if ($RequestType=="CBD OFF") {
			$tipe = "cbdoff";
		} else if ($RequestType=="CBD ON") {
			$tipe = "cbdon";
		}
		// die($UserEmail);
		
		if (isset($_POST["btnApprove"])) {
			$doApprove = $this->MsDealerModel->ApproveRequest($RequestType, $RequestNo, $UserEmail, $Note);

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
								// $BhaktiFlag = json_decode(file_get_contents($URL), true);
								$BhaktiFlag = file_get_contents($URL);
								$BhaktiFlag = $this->GzipDecodeModel->_decodeGzip_true($BhaktiFlag);
								//die($URL."<br><br>".json_encode($Bhakti));
							} else if ($RequestType=="CBD OFF" || $RequestType=="CBD ON") {
								$URL = $URL.API_BKT."/MasterDealer/ApproveRejectCBD2?api=APITES".
										"&hsid=".urlencode($RequestNo).
										"&kdplg=".urlencode($REQ->AddInfo1Value)."&cbd=".urlencode($RequestType).
										"&tgl=".urlencode(date("Y-m-d",strtotime($REQ->AddInfo6Value))).
										"&sts=APPROVED&exp=".urlencode(date("Y-m-d",strtotime($REQ->ExpiryDate))).
										"&reqby=".urlencode($REQ->RequestByName)."&reqdate=".urlencode(date("Y-m-d H:i:s",strtotime($REQ->RequestDate))).
										"&appby=".urlencode($REQ->ApprovedByName)."&appdate=".urlencode(date("Y-m-d H:i:s",strtotime($REQ->ApprovedDate))).
										"&svr=".urlencode($DB->Server)."&db=".urlencode($DB->Database).
										"&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD);
								//die($URL);
								// $BhaktiFlag = json_decode(file_get_contents($URL), true);
								$BhaktiFlag = file_get_contents($URL);
								$BhaktiFlag = $this->GzipDecodeModel->_decodeGzip_true($BhaktiFlag);

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
								// $BhaktiFlag = json_decode(file_get_contents($URL, false, $context), true);
								$BhaktiFlag = file_get_contents($URL, false, $context);
								$BhaktiFlag = $this->GzipDecodeModel->_decodeGzip_true($BhaktiFlag);
								if ($BhaktiFlag === FALSE) {
									$BhaktiFlag["result"] = "gagal";
								}
							}

							if ($BhaktiFlag["result"]=="sukses") {
								$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"FINISHED");
							} else {
								$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"PENDING");	
							}
						} else {
							$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"PENDING");
						}
					}
					$email_content = $this->CreateResponseEmailContent($RequestType, "APPROVED", $REQ);
					$doEmail = $this->MsDealerModel->EmailResponseRequest($RequestType, $REQ, $email_content);
					
					redirect("approvallist/view/5");
				} else {
					redirect("approvallist/view/5");
				}
			} else {
				redirect("approvallist/view/7");
			}

		} else {
			// die($UserEmail);
			$doReject = $this->MsDealerModel->RejectRequest($RequestType, $RequestNo, $UserEmail, $Note);
			// die(json_encode($doReject));
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
									// $BhaktiFlag = json_decode(file_get_contents($URL, false, $context), true);
									$BhaktiFlag = file_get_contents($URL, false, $context);
									$BhaktiFlag = $this->GzipDecodeModel->_decodeGzip_true($BhaktiFlag);
									if ($BhaktiFlag === FALSE) {
										$BhaktiFlag["result"] = "gagal";
									}
								}

								if ($BhaktiFlag["result"]=="sukses") {
									$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"FINISHED");
								} else {
									$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"PENDING");	
								}
							} else {
								$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo, "PENDING");
							}
						}
					} else {
						$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo, "FINISHED");
					}
				}
				$email_content = $this->CreateResponseEmailContent($RequestType, "REJECTED", $REQ);
				$doEmail = $this->MsDealerModel->EmailResponseRequest($RequestType, $REQ, $email_content);
				redirect("approvallist/view/6");
			} else {
				redirect("approvallist/view/7");
			}
		}
	}

	public function CreateResponseEmailContent($RequestType="CREDIT LIMIT", $Response, $REQ)
	{
		$Requests = $this->MsDealerModel->GetRequest($RequestType, $REQ->RequestNo);
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

    public function UpdateBhaktiFlag()
	{
		$keyAPI = 'APITES';
		$api = urldecode($this->input->get('api'));
		$req = urldecode($this->input->get('req'));
		$sts = urldecode($this->input->get('sts'));
		$type = urldecode($this->input->get('type'));

		if($keyAPI==$api) {
			set_time_limit(60);
			$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($type, $req, strtoupper($sts));
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

    public function SyncUnlockedDealer()
	{
		$keyAPI = 'APITES';
		$api = urldecode($this->input->get('api'));

		if($keyAPI==$api) {
			set_time_limit(60);
			$requests = $this->MsDealerModel->GetUnlockedDealer();	
			if (count($requests)>0) 
			{
				foreach($requests as $rq) {
					$conn = $this->MasterDbModel->get($rq->DatabaseID);
    				$url = $conn->AlamatWebService.API_BKT."/MasterDealer/UnlockToko?api=APITES".
		        			"&req=".urlencode($rq->RequestID)."&reqby=".urlencode($rq->RequestBy)."&reqdate=".urlencode(date('Y-m-d H:i:s',strtotime($rq->RequestDate))).
		        			"&plg=".urlencode($rq->KdPlg)."&ket=".urlencode($rq->RequestNote)."&appby=".urlencode($rq->ApprovedBy).
		        			"&begin=".urlencode(date("Y-m-d", strtotime($rq->UnlockBegin)))."&end=".urlencode(date("Y-m-d", strtotime($rq->UnlockEnd))).
		        			"&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID).
		        			"&pwd=".urlencode(SQL_PWD);
			        // echo($url);
			        // echo("<br>");
			        // $result = json_decode(file_get_contents($url), true);
			        $result = file_get_contents($URL);
					$result = $this->GzipDecodeModel->_decodeGzip_true($result);
			        $rq->result = $result;
			    }

				$data["result"] = "sukses";
				$data["data"] = $requests;
				$data["error"] = "";
			}
			else
			{
				$data["result"] = "gagal";
				$data["data"] = array();
				$data["error"] = "Tidak Ada Dealer Unlocked";
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

    public function GetPendingCL()
	{
		$keyAPI = 'APITES';
		$api = urldecode($this->input->get('api'));

		if($keyAPI==$api) {
			set_time_limit(60);
			$requests = $this->MsDealerModel->GetPendingCL();
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
			$requests = $this->MsDealerModel->GetPendingCBD();
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

    public function UpdateIsLocked()
	{
		$kdplg = urldecode($this->input->get('kdplg'));
		$branch = urldecode($this->input->get('branch'));
		
		$param = array("message" => "", "data" => array("partnerCodes"=> array($kdplg), "isLocked" => false));
		$data = json_encode($param);
		
		$result="FAILED";
		$token = '';
		$err = '';
		$response = json_decode(file_get_contents(base_url().'Sync/GetTokenAPIv2?branch='.$branch));
		
		if($response!==false){
			if(ISSET($response->token)){
				$token = $response->token;
			}
			else{
				$err="Token tidak ditemukan!";
			}
		}
		else{
			$err="URL Ambil Token Tidak Valid!";
		}
		
		$PHOENIXURL = '';
		$response = @file_get_contents(site_url().'/Sync/GetPhoenixURL');
		if($response!==false){
			if($response!=''){
				$PHOENIXURL = $response;
			}
			else{
				$err="Phoenix URL tidak ditemukan!";
			}
		}
		else{
			$err="URL Ambil Phoenix URL Tidak Valid!";
		}
		
		if($token!='' && $PHOENIXURL!=''){
			$URL = trim($PHOENIXURL).'account/update-is-locked';
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer ' . $token), 
			));			
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			
			$response = curl_exec($curl);
			$err = curl_error($curl);
			$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);
			
			if($http_code==200){
				$res = json_decode($response);
				if($res->message=='Sukses Edit')
				$result = 'SUCCESS';
			}
			else{
				$err = 'HTTPCODE:'.$http_code.' '.$err;
			}
		}	
		echo json_encode(array('result'=>$result, 'message'=>$err));
	}
	
}