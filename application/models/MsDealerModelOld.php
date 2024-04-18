<?php
	Class MsDealerModelOld extends CI_Model
	{
		//credit limit
		public $test_mode = false;
		public $test_email = "";
		public $test_wa_mode = false;
		public $test_wa_phone = "";

	    function ReplaceChars($teks) 
	    {
	    	$teks = str_replace("/","_", $teks);
			$teks = str_replace(",","_", $teks);
			$teks = str_replace(" ","_", $teks);
			$teks = str_replace(".","_", $teks);
			$teks = str_replace("'","_", $teks);
			$teks = str_replace("-","_", $teks);
			$teks = str_replace("__","_",$teks);
			return $teks;		
	    }

		function getJmlReqApproval($email){
			$qry = "SELECT *
					FROM [TblApproval] a inner join
						(select RequestNo, [priority] as RequestGroup, COUNT(ApprovedBy) as JmlApproval
						From TblApproval
						Group by RequestNo, [priority], ApprovalNeeded) b on a.RequestNo=b.RequestNo 
							and a.[Priority]=b.RequestGroup
					where BhaktiFlag='UNPROCESSED' and IsCancelled = 0
						and ApprovalType='CREDIT LIMIT'";
						//and ApprovedBy='".$email."'
			$qry.= "	and ApprovalStatus = 'UNPROCESSED'
						and a.ApprovalNeeded<>b.JmlApproval
						and convert(varchar(8),a.ExpiryDate,112)>=convert(varchar(8),GETDATE(),112)";

			$res = $this->db->query($qry);			
			return $res->num_rows();			
		}

		function getDataReqApproval($email){
			$qry = "SELECT *
					FROM [TblApproval] a inner join
						(select RequestNo, [priority] as RequestGroup, COUNT(ApprovedBy) as JmlApproval
						From TblApproval
						Group by RequestNo, [priority], ApprovalNeeded) b on a.RequestNo=b.RequestNo 
							and a.[Priority]=b.RequestGroup
					where BhaktiFlag='UNPROCESSED' and IsCancelled = 0
						and ApprovalType='CREDIT LIMIT'";
						//and ApprovedBy='".$email."'
			$qry.= "	and ApprovalStatus = 'UNPROCESSED'
						and a.ApprovalNeeded<>b.JmlApproval
						and convert(varchar(8),a.ExpiryDate,112)>=convert(varchar(8),GETDATE(),112)";

			$res = $this->db->query($qry);			
			if ($res->num_rows()>0)
		    	return $res->result();
		    else
		    	return null;			
		}

		function CheckExistingRequest($RequestType="CREDIT LIMIT", $params)
		{
			$str = "";
			if ($RequestType=="CREDIT LIMIT") {
				/*
				$str = "Select * From TblApproval Where ApprovalType='CREDIT LIMIT' 
						and AddInfo1Value='".$params["KdPlg"]."' and AddInfo2Value='".$params["Divisi"]."'
						and AddInfo3Value='".$params["CLNew"]."' and convert(varchar(8),ExpiryDate,112)>=convert(varchar(8),getdate(),112) 
						and year(isnull(ApprovedDate,getdate()))=".date("Y")." and month(isnull(ApprovedDate,getdate()))=".date("m")."
						and RequestNo not in (Select RequestNo From TblApproval Where ApprovalStatus='REJECTED')
						and IsCancelled=0";
				*/
				$str = "Select * From TblApproval Where ApprovalType='CREDIT LIMIT' 
						and AddInfo1Value='".$params["KdPlg"]."' and AddInfo2Value='".$params["Divisi"]."'
						and AddInfo3Value='".$params["CLNew"]."' and convert(varchar(8),ExpiryDate,112)>=convert(varchar(8),getdate(),112) 
						and RequestNo ='UNPROCESSED'
						and IsCancelled=0";
			} else if ($RequestType=="CBD ON" || $RequestType=="CBD OFF") {
				$str = "Select * From TblApproval 
						Where ApprovalType='".$RequestType."' 
							and AddInfo1Value='".$params["KdPlg"]."' 
							and AddInfo6Value='".date("Y-m-d", strtotime($params["HistoryDate"]))."' 
							and RequestNo not in (Select RequestNo From TblApproval Where ApprovalStatus='REJECTED')
							and IsCancelled=0";
			}
			$GetRequests = $this->db->query($str);
			if ($GetRequests->num_rows()==0) {
				return false;
			} else {
				return true;
			}
		}

		function RequestCL($params, $html_content, $recipients, $user, $RequestID="")
		{
			$ReqID = "CL_".trim($params["KdPlg"])."_".date("Ymdhis");
			if ($RequestID!="") $ReqID = $RequestID;

			$ExpiryDate = null;
			$MsConfig = $this->db->query("select * from Ms_Config WHERE ConfigType='REQUEST CL'")->row();
			$configValue = $MsConfig->ConfigValue;
			
			$this->db->flush_cache();
			
			if($configValue!='UNLIMITED'){
				$getExpiryDate = $this->db->query(" ".$configValue." as ExpiryDate ")->row();
				$ExpiryDate = $getExpiryDate->ExpiryDate;
				
				$this->db->flush_cache();

				if($ExpiryDate!='') $ExpiryDate = date("Y-m-d",strtotime($ExpiryDate));

			}
			
			// $ExpiryDate = date("Y-m-d", strtotime("+1 day"));
			// if (date("w")==5) {
			// 	$ExpiryDate = date("Y-m-d", strtotime("+4 day"));
			// } else if (date("w")==6) {
			// 	$ExpiryDate = date("Y-m-d", strtotime("+3 day"));
			// } else if (date("w")==0) {
			// 	$ExpiryDate = date("Y-m-d", strtotime("+2 day"));
			// }	/*Jumat,Sabtu,Minggu ExpiryDate di Hari Senin*/
			
			$params["RequestID"] = $ReqID;
			$params["ExpiryDate"] = $ExpiryDate;
			// die(json_encode($params));
			// die(json_encode($recipients));

			//if ($SuksesCount>0) {
			$this->db->trans_start();
			for($i=0; $i<count($recipients); $i++) {
				$this->db->set("ApprovalType", "CREDIT LIMIT");
				$this->db->set("RequestNo",$ReqID);
				$this->db->set("RequestBy", $user["UserEmail"]);
				$this->db->set("RequestDate", date("Y-m-d H:i:s"));
				$this->db->set("RequestByName", $user["Name"]);
				$this->db->set("RequestByEmail", $user["Email"]);
				$this->db->set("ApprovedBy", $recipients[$i]["USEREMAIL"]);
				$this->db->set("ApprovedByName", $recipients[$i]["NAMA"]);
				$this->db->set("ApprovedByEmail", $recipients[$i]["EMAIL"]);
				$this->db->set("ApprovedDate", null);
				$this->db->set("ApprovalStatus", "UNPROCESSED");
				$this->db->set("ApprovalNeeded", $params["ApprovalNeeded"]);
				$this->db->set("Priority", $params["Priority"]);
				$this->db->set("AddInfo1", "Kode Pelanggan");
				$this->db->set("AddInfo1Value", $params["KdPlg"]);
				$this->db->set("AddInfo2", "Divisi");
				$this->db->set("AddInfo2Value", $params["Divisi"]);
				$this->db->set("AddInfo3", "Credit Limit Baru");
				$this->db->set("AddInfo3Value", $params["CLNew"]);
				$this->db->set("AddInfo4", "HTML Content");
				$this->db->set("AddInfo4Value", json_encode($html_content));
				$this->db->set("AddInfo5", "Database ID");
				$this->db->set("AddInfo5Value", $params["DatabaseID"]);
				$this->db->set("AddInfo6", "Nama Pelanggan");
				$this->db->set("AddInfo6Value", $params["NmPlg"]);
				$this->db->set("AddInfo7", "CL Permanent");
				$this->db->set("AddInfo7Value", $params["CLPermanent"]);
				$this->db->set("AddInfo8", "Catatan");
				$this->db->set("AddInfo8Value", $params["Catatan"]);
				$this->db->set("AddInfo9", "Wilayah");
				$this->db->set("AddInfo9Value", $params["Wilayah"]);
				$this->db->set("AddInfo11", "CL Temporary");
				$this->db->set("AddInfo11Value", $params["CLTemporary"]);
				$this->db->set("AddInfo12", "Kenaikan CL");
				$this->db->set("AddInfo12Value", $params["KenaikanCL"]);
				$this->db->set("ExpiryDate", $ExpiryDate);
				$this->db->set("BhaktiFlag", "UNPROCESSED");
				$this->db->set("BhaktiProcessDate", null);
				$this->db->insert("TblApproval");

				$recipients[$i]["LASTQUERY"] = $this->db->last_query();
			}
			$this->db->trans_complete();
			
			$str = "Select * From TblApproval Where RequestNo='".$ReqID."'"; 
			$res = $this->db->query($str);
			if ($res->num_rows()>0){
				return(array("result"=>"sukses","requestid"=>$ReqID, "expirydate"=>$ExpiryDate, "recipients"=>$recipients, "ket"=>"Simpan Request Sukses"));
			} else {
				return(array("result"=>"gagal", "requestid"=>"", "expirydate"=>"", "recipients"=>$recipients, "ket"=>"Simpan Request Gagal"));
			}

			//} else {
			//	return(array("result"=>"gagal", "requestid"=>"", "ket"=>"Email Gagal; Simpan Request Diskip"));
			//}
		}

		function RequestCBD($params, $html_content, $recipients, $user, $RequestID="")
		{
			$DEALER = $params["Dealer"];

			//if ($SuksesCount>0) {
			$this->db->trans_start();
			for($i=0; $i<count($recipients); $i++) {
				$this->db->set("ApprovalType", $params["RequestType"]);
				$this->db->set("RequestNo",$params["HistoryID"]);
				$this->db->set("RequestBy", $user["UserEmail"]);
				$this->db->set("RequestDate", date("Y-m-d H:i:s"));
				$this->db->set("RequestByName", $user["Name"]);
				$this->db->set("RequestByEmail", $user["Email"]);
				$this->db->set("ApprovedBy", $recipients[$i]["USEREMAIL"]);
				$this->db->set("ApprovedByName", $recipients[$i]["NAMA"]);
				$this->db->set("ApprovedByEmail", $recipients[$i]["EMAIL"]);
				$this->db->set("ApprovedDate", null);
				$this->db->set("ApprovalStatus", "UNPROCESSED");
				$this->db->set("ApprovalNeeded", $params["ApprovalNeeded"]);
				$this->db->set("Priority", $params["Priority"]);
				$this->db->set("AddInfo1", "Kode Pelanggan");
				$this->db->set("AddInfo1Value", $DEALER["KD_PLG"]);
				$this->db->set("AddInfo2", "Nama Pelanggan");
				$this->db->set("AddInfo2Value", $DEALER["NM_PLG"]);
				$this->db->set("AddInfo3", "Wilayah");
				$this->db->set("AddInfo3Value", $DEALER["WILAYAH"]);
				$this->db->set("AddInfo4", "HTML Content");
				$this->db->set("AddInfo4Value", json_encode($html_content));
				$this->db->set("AddInfo5", "Database ID");
				$this->db->set("AddInfo5Value", $params["DatabaseID"]);
				$this->db->set("AddInfo6", "Tanggal Start");
				$this->db->set("AddInfo6Value", date("Y-m-d", strtotime($params["Tgl"])));
				$this->db->set("ExpiryDate", date("Y-m-d",strtotime($params["ExpiryDate"])));
				$this->db->set("BhaktiFlag", "UNPROCESSED");
				$this->db->set("BhaktiProcessDate", null);
				$this->db->insert("TblApproval");
			}
			$this->db->trans_complete();

			$str = "Select * From TblApproval Where RequestNo='".$params["HistoryID"]."'"; 
			$res = $this->db->query($str);
			if ($res->num_rows()>0){
				return(array("result"=>"sukses","requestid"=>$params["HistoryID"], "expirydate"=>$params["ExpiryDate"], "ket"=>"Email Sukses; Simpan Request Sukses"));
			} else {
				return(array("result"=>"gagal", "requestid"=>"", "expirydate"=>"", "ket"=>"Email Sukses; Simpan Request Gagal"));
			}
		}

		function EmailRequest($RequestType="CREDIT LIMIT", $params, $html_content, $recipients, $user, $resend=0)
		{
			//echo(json_encode($params)."<br><br>");
			//echo(json_encode($recipients)."<br><br>");
			$SuksesCount = 0;		

			$configsys = $this->ConfigSysModel->Get();
			$smtp['protocol'] = $configsys->mail_protocol;
			$smtp['smtp_host'] = $configsys->mail_host;
			$smtp['smtp_port'] = $configsys->mail_port;
			$smtp['smtp_user'] = $configsys->mail_user;
			$smtp['smtp_pass'] = $configsys->mail_pwd;
			if ($configsys->smtp_crypto=="tls") {
				$smtp['smtp_crypto'] = $configsys->smtp_crypto;
			}
			$smtp['charset'] = "utf-8";
			$smtp['mailtype'] = "html";
			$smtp['newline'] = "\r\n";

			$this->load->library('email');
			$ci = get_instance();

			$REQUESTID = $params["RequestID"];

			$email_subject = "";
			if ($RequestType=="CREDIT LIMIT") {

				$email_content = $user["Name"]." mengajukan permohonan kenaikan Credit Limit sebagai berikut :<br><br>";

				$request_content = "";
				if($params['ExpiryDate']!=''){
					$request_content = "<br><br>Request Credit Limit ini Berlaku S/D Tanggal <b>".date("d-M-Y", strtotime($params["ExpiryDate"]))." 23:59:59</b><br><br>";
				}	

				$request_content.= "<a href='".BHAKTICOID."MsDealerApprovalOld/ProcessRequest?type=cl&id=".$REQUESTID."'>
									<button id='btnProcess' style='background-color:#ccd3de;color:#000;padding:5px;'>PROSES</button>
										</a>";		

				$email_subject = substr("REQ CL ".date("dMY")." ".$this->ReplaceChars($params["NmPlg"]),0,48);

			} else if ($RequestType=="CBD ON") {

				$DEALER = $params["Dealer"];
				$email_content = $user["Name"]." mengajukan permohonan pengaktifan status CBD untuk dealer berikut :<br><br>";

				$request_content = "<a href='".BHAKTICOID."MsDealerApprovalOld/ProcessRequest?type=cbdon&id=".$REQUESTID."'>
									<button id='btnProcess' style='background-color:#ccd3de;color:#000;padding:5px;'>PROSES</button>
									</a>";

				$email_subject = substr("REQ CBD ON ".date("dMY")." ".$DEALER["NM_PLG"],0,48);

			} else if ($RequestType=="CBD OFF") {

				$DEALER = $params["Dealer"];
				$email_content = $user["Name"]." mengajukan permohonan pe-nonaktif-an status CBD untuk dealer berikut :<br><br>";

				$request_content = "<a href='".BHAKTICOID."MsDealerApprovalOld/ProcessRequest?type=cbdoff&id=".$REQUESTID."'>
									<button id='btnProcess' style='background-color:#ccd3de;color:#000;padding:5px;'>PROSES</button>
									</a>";

				$email_subject = substr("REQ CBD OFF ".date("dMY")." ".$DEALER["NM_PLG"],0,48);
			}

			$EmailRecipients = array();	
			if ($this->test_mode==true) {
				array_push($EmailRecipients, $this->test_email);
			} else {
				for($i=0; $i<count($recipients); $i++) {
					array_push($EmailRecipients, $recipients[$i]["EMAIL"]);
				}
				if ($this->test_email!="") {
					array_push($EmailRecipients, $this->test_email);
				}
			}
			$email_content = $email_content.$html_content.$request_content;

			//echo("Email Content:<br><br>".$email_content."<br><br>");

	        $ci->email->clear(true);
			$ci->email->initialize($smtp);
			$ci->email->from($configsys->mail_user, $configsys->mail_alias);			
			$ci->email->to($EmailRecipients);
			$ci->email->cc("bhaktiautoemail.noreply@bhakti.co.id");
			$ci->email->subject($email_subject);
			$ci->email->message($email_content);
			//$ci->email->attach($attachment);
			
			if ($ci->email->send()) {
	        	$ci->email->clear(true);
	        	$SuksesCount+=1;

				if ($resend==0) {
					$this->db->where("RequestNo", $REQUESTID);
					$this->db->set("AddInfo10", "PENERIMA EMAIL");
					$this->db->set("AddInfo10Value", json_encode($EmailRecipients));
					$this->db->update("TblApproval");
				}
	        	//echo("SUKSES<br>");
			} else {
	        	$err = $ci->email->print_debugger();
	        	$ci->email->clear(true);
	        	// echo("EMAIL TIDAK TERKIRIM : ".$err."<br>");
			}
		
			return $SuksesCount;
		}

		function ProsesWhatsapp($RequestType="CREDIT LIMIT", $params, $mobiles, $user, $waAccount="OTHER")
		{
			if (count($mobiles)==0 && $this->test_wa_mode==false) return "GAGAL";
			
			$SuksesCount = 0;
			$REQUESTID = $params["RequestID"];

			$email_subject = "";
			$URL = "";
			$wa_content = "";

			if ($RequestType=="CREDIT LIMIT") {
				$URL = site_url()."MsDealerApprovalOld/ProcessRequest?type=cl&id=".$REQUESTID;

			} else if ($RequestType=="CBD ON") {

				$DEALER = $params["Dealer"];
				$email_content = $user["Name"]." mengajukan permohonan pengaktifan status CBD untuk dealer berikut :<br><br>";

				$request_content = "<a href='".site_url()."MsDealerApprovalOld/ProcessRequest?type=cbdon&id=".$REQUESTID."'>
									<button id='btnProcess' style='background-color:#ccd3de;color:#000;padding:5px;'>PROSES</button>
									</a>";

				$email_subject = substr("REQ CBD ON ".date("dMY")." ".$DEALER["NM_PLG"],0,48);

			} else if ($RequestType=="CBD OFF") {

				$DEALER = $params["Dealer"];
				$email_content = $user["Name"]." mengajukan permohonan pe-nonaktif-an status CBD untuk dealer berikut :<br><br>";

				$request_content = "<a href='".site_url()."MsDealerApprovalOld/ProcessRequest?type=cbdoff&id=".$REQUESTID."'>
									<button id='btnProcess' style='background-color:#ccd3de;color:#000;padding:5px;'>PROSES</button>
									</a>";

				$email_subject = substr("REQ CBD OFF ".date("dMY")." ".$DEALER["NM_PLG"],0,48);
			}

			set_time_limit(60);

			if ($this->test_wa_mode==true) {
				$mobiles = array();
				array_push($mobiles, $this->test_wa_phone);
			} else if ($this->test_wa_phone!="") {
				array_push($mobiles, $this->test_wa_phone);
			}
			
			for ($i=0;$i<count($mobiles);$i++) {
				// Divisi : *{{1}}* Dealer : *{{2}}* *{{3}}* Wilayah : *{{4}}* CL Permanent : *{{5}}* CL Temporary : *{{6}}* 
				// CL Direquest : * {{7}}* Kenaikan CL : *{{8}}* Catatan : *{{9}}* Diajukan Oleh : *{{10}}* 
				// Request CL berlaku s/d tgl *{{11}}* Proses melalui link berikut : {{12}} 

				$data = [
					"phone" => $mobiles[$i],
					"paramType1"=>"text",
					"param1"=>htmlspecialchars($params["Divisi"]),
					"paramType2"=>"text",
					"param2"=>htmlspecialchars($params["NmPlg"]),
					"paramType3"=>"text",
					"param3"=>htmlspecialchars($params["KdPlg"]),
					"paramType4"=>"text",
					"param4"=>htmlspecialchars($params["Wilayah"]),
					"paramType5"=>"text",
					"param5"=>htmlspecialchars(number_format($params["CLPermanent"])),
					"paramType6"=>"text",
					"param6"=>(($params["CLTemporary"]!=null)?htmlspecialchars(number_format($params["CLTemporary"])):"N/A"),
					"paramType7"=>"text",
					"param7"=>htmlspecialchars(number_format($params["CLNew"])),
					"paramType8"=>"text",
					"param8"=>(($params["CLTemporary"]!=null)?htmlspecialchars(number_format($params["KenaikanCL"])):"N/A"),
					"paramType9"=>"text",
					"param9"=>htmlspecialchars($params["Catatan"]),
					"paramType10"=>"text",
					"param10"=>htmlspecialchars($user["Name"]),
					"paramType11"=>"text",
					"param11"=>htmlspecialchars(date("d-M-Y", strtotime($params["ExpiryDate"]))." 23:59:59"),
					"paramType12"=>"text",
					"param12"=>htmlspecialchars($URL)
				];
				
				$APIurl = API_MSG."/waba/requestCL?src=".$waAccount;

				$curl2 = curl_init();
				curl_setopt_array($curl2, array(
					CURLOPT_URL => $APIurl,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 60,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => json_encode($data),
					CURLOPT_HTTPHEADER => array("Content-type: application/json")
				));
				
				$response = curl_exec($curl2);
				$err = curl_error($curl2);
				curl_close($curl2);
			}

			return "SUKSES";
		}

		function GetRequestForProcess($RequestType="CREDIT LIMIT", $RequestID, $UserEmail="") 
		{
			$approval_needed = 0;
			$approved_count = 0;
			$str = "Select * From TblApproval Where ApprovalType='".$RequestType."' and RequestNo='".$RequestID."'";
			$GetRequests = $this->db->query($str);
			if ($GetRequests->num_rows()==0) {
				//die("invalid request");
				$result = array("result"=>"gagal", "status"=>"INVALID REQUEST", "data"=>array(), "req"=>array(), "approval_needed"=>$approval_needed, "approved_count"=>$approved_count);
			} else {
				$REQS = $GetRequests->result();
				//echo(json_encode($REQS)."<br><br>");
				$JumlahUnprocessed = 0;
				$JumlahApproved = 0;
				$JumlahRejected = 0;
				$approval_needed = 0;
				$approved_count = 0;

				$str = "Select Priority, ApprovalNeeded, sum(case when ApprovalStatus='UNPROCESSED' then 1 else 0 end) as UnprocessedCount, 
							(case when sum(case when ApprovalStatus='APPROVED' then 1 else 0 end)>ApprovalNeeded then ApprovalNeeded else sum(case when ApprovalStatus='APPROVED' then 1 else 0 end) end) as ApprovedCount,
							sum(case when ApprovalStatus='REJECTED' then 1 else 0 end) as RejectedCount
						From TblApproval
						Where ApprovalType='".$RequestType."' and RequestNo='".$RequestID."'
						Group By Priority, ApprovalNeeded";
				$CheckApproval = $this->db->query($str);
				foreach($CheckApproval->result() as $a) {
					$approval_needed += $a->ApprovalNeeded;
					$approved_count += $a->ApprovedCount;
				
					$str = "Select RequestNo, ApprovalStatus, count(ApprovedBy) as StatusCount From TblApproval 
							where ApprovalType='".$RequestType."' and RequestNo='".$RequestID."' and Priority=".$a->Priority."
							Group By RequestNo, ApprovalStatus";

					$CheckStatus = $this->db->query($str);
					if ($CheckStatus->num_rows()>0) {
						$ApprovalStatus = $CheckStatus->Result();
						foreach($ApprovalStatus as $as) {
							if ($as->ApprovalStatus=="UNPROCESSED") {
								$JumlahUnprocessed += $as->StatusCount;
							} else if ($as->ApprovalStatus=="APPROVED") {
								$JumlahApproved += (($as->StatusCount>$a->ApprovalNeeded)?$a->ApprovalNeeded:$as->StatusCount);
							} else if ($as->ApprovalStatus=="REJECTED") {
								$JumlahRejected += $as->StatusCount;
							}
						}
					}
				}

				if ($REQS[0]->IsCancelled==1) {
					$result = array("result"=>"sukses", "status"=>"CANCELLED", "data"=>$REQS[0], "req"=>$REQS, "approval_needed"=>$approval_needed, "approved_count"=>$approved_count);
				} else if ($JumlahRejected>0) {
					$result = array("result"=>"sukses", "status"=>"REJECTED", "data"=>$REQS[0], "req"=>$REQS, "approval_needed"=>$approval_needed, "approved_count"=>$approved_count);
				} else if ($JumlahUnprocessed==0) {
					$result = array("result"=>"sukses", "status"=>"PROCESSED", "data"=>$REQS[0], "req"=>$REQS, "approval_needed"=>$approval_needed, "approved_count"=>$approved_count);
				} else if ($JumlahApproved>=$approval_needed) {
					$result = array("result"=>"sukses", "status"=>"APPROVED", "data"=>$REQS[0], "req"=>$REQS, "approval_needed"=>$approval_needed, "approved_count"=>$approved_count);
				} else {
					$result = array("result"=>"sukses", "status"=>"UNPROCESSED", "data"=>$REQS[0], "req"=>$REQS, "approved"=>$JumlahApproved, "approval_needed"=>$approval_needed, "approved_count"=>$approved_count);
				}

			}
			//echo(json_encode($result)."<br><br>");
			return $result;
		}

		function GetRequestApproverList($RequestType="CREDIT LIMIT", $RequestID) 
		{
			$approval_needed = 0;
			$approved_count = 0;
			$str = "Select x.*
					From TblApproval x left join (Select RequestNo, Priority, count(ApprovedBy) as ApprovedCount  
						From TblApproval Where ApprovalType='".$RequestType."' and RequestNo='".$RequestID."'  and ApprovalStatus='APPROVED'
						Group By RequestNo, Priority) y on x.RequestNo=y.RequestNo and x.Priority=y.Priority
					Where x.ApprovalType='".$RequestType."' and x.RequestNo='".$RequestID."' 
					and x.ApprovalStatus='UNPROCESSED'
					and x.ApprovalNeeded>isnull(y.ApprovedCount,0)";
			$GetRequests = $this->db->query($str);
			if ($GetRequests->num_rows()==0) {
				//die("invalid request");
				return array();
			} else {
				return $GetRequests->result();
			}
		}

		function UpdateBhaktiFlag($TYPE="CREDIT LIMIT", $REQ, $STATUS)
		{
			$str = "Update TblApproval Set BhaktiFlag='".$STATUS."', BhaktiProcessDate=GETDATE()
					Where ApprovalType='".$TYPE."' and RequestNo='".$REQ."'";
			$Updt= $this->db->query($str);

			$str = "Select * From TblApproval Where ApprovalType='".$TYPE."' 
					and RequestNo='".$REQ."' and BhaktiFlag='".$STATUS."'";
			$res = $this->db->query($str);
			if ($res->num_rows()>0)
				return true;
			else
				return false;
		}

		function ApproveRequest($RequestType="CREDIT LIMIT", $RequestID, $UserEmail="", $Note="") 
		{
			$result = array();
			$str = "Select *, AddInfo5Value as DATABASEID 
					From TblApproval Where ApprovalType='".$RequestType."' and RequestNo='".$RequestID."' 
					and ApprovedBy='".$UserEmail."' ";
			$GetRequest = $this->db->query($str);
			if ($GetRequest->num_rows()==0) {
				$result = array("result"=>"gagal", "status"=>"INVALID REQUEST", "databaseID"=>0, "complete"=>false, "req"=>null);
			} else {

				$str = "Update TblApproval Set ApprovalStatus='APPROVED', ApprovedDate=GETDATE(), ApprovalNote='".$Note."'
						Where ApprovalType='".$RequestType."' and RequestNo='".$RequestID."' and ApprovedBy='".$UserEmail."' ";
				$Updt = $this->db->query($str);

				$str = "Select *, AddInfo5Value as DATABASEID 
						From TblApproval 
						Where ApprovalType='".$RequestType."' and RequestNo='".$RequestID."' and ApprovedBy='".$UserEmail."' 
						and ApprovalStatus='APPROVED'";
				$CheckLagi = $this->db->query($str);
				if ($CheckLagi->num_rows()>0) {
					$str = "Select Priority, ApprovalNeeded, sum(case when ApprovalStatus='UNPROCESSED' then 1 else 0 end) as UnprocessedCount, 
								sum(case when ApprovalStatus='APPROVED' then 1 else 0 end) as ApprovedCount,
								sum(case when ApprovalStatus='REJECTED' then 1 else 0 end) as RejectedCount
							From TblApproval
							Where ApprovalType='".$RequestType."' and RequestNo='".$RequestID."'
							Group By Priority, ApprovalNeeded";
					$CheckApproval = $this->db->query($str);
					if ($CheckApproval->num_rows()==0) {
						$result = array("result"=>"gagal", "status"=>"APPROVE GAGAL", "databaseID"=>0, "complete"=>false, "req"=>$CheckLagi->row());
					} else {
						$approved = true;
						//die(json_encode($CheckApproval->result()));
						foreach($CheckApproval->result() as $a) {
							if ($approved==true && $a->ApprovedCount<$a->ApprovalNeeded) {
								$approved = false;
							}
						}

						// $DatabaseID = $CheckLagi->row()->DatabaseID;
						// $this->load->model()
						if ($approved) {
							$ApprovalComplete = $this->UpdateBhaktiFlag($RequestType, $RequestID, "PENDING");
							$result = array("result"=>"sukses", "status"=>"APPROVE SUKSES", "databaseID"=>$CheckLagi->row()->DATABASEID, "complete"=>true, "req"=>$CheckLagi->row());
						} else {
							$result = array("result"=>"sukses", "status"=>"APPROVE SUKSES", "databaseID"=>$CheckLagi->row()->DATABASEID, "complete"=>false, "req"=>$CheckLagi->row());
						}
					}
				} else {
					$result = array("result"=>"gagal", "status"=>"APPROVE GAGAL", "databaseID"=>0, "complete"=>false, "req"=>$GetRequest->row());
				}
			}
			//die(json_encode($result));
			return $result;
		}

		function RejectRequest($RequestType="CREDIT LIMIT", $RequestID, $UserEmail="", $Note="") 
		{
			$result = array();
			$str = "Select *, AddInfo5Value as DATABASEID  
					From TblApproval Where ApprovalType='".$RequestType."' and RequestNo='".$RequestID."' and ApprovedBy='".$UserEmail."' ";
			$GetRequest = $this->db->query($str);
			if ($GetRequest->num_rows()==0) {
				$result = array("result"=>"gagal", "status"=>"INVALID REQUEST", "databaseID"=>0, "complete"=>false, "req"=>null);
			} else {
				$str = "Update TblApproval Set ApprovalStatus='REJECTED', ApprovedDate=GETDATE(), ApprovalNote='".$Note."'
						Where ApprovalType='".$RequestType."' and RequestNo='".$RequestID."' and ApprovalStatus='UNPROCESSED' and ApprovedBy='".$UserEmail."'";
				$Updt = $this->db->query($str);

				$str = "Select *, AddInfo5Value as DATABASEID  
						From TblApproval 
						Where ApprovalType='".$RequestType."' and RequestNo='".$RequestID."' and ApprovedBy='".$UserEmail."' 
						and ApprovalStatus='REJECTED'";
				$CheckLagi = $this->db->query($str);
				if ($CheckLagi->num_rows()>0) {
					$result = array("result"=>"sukses", "status"=>"REJECT SUKSES", "databaseID"=>$CheckLagi->row()->DATABASEID, "complete"=>true, "req"=>$CheckLagi->row());
				} else {
					$result = array("result"=>"gagal", "status"=>"REJECT GAGAL", "databaseID"=>0, "complete"=>false, "req"=>$GetRequest->row());
				}
			}
			//die(json_encode($result));
			return($result);
		}

		function CancelRequest($RequestType="CREDIT LIMIT", $RequestID) 
		{
			$result = array();
			$str = "Select *, AddInfo5Value as DATABASEID  
					From TblApproval 
					Where ApprovalType='".$RequestType."' and RequestNo='".$RequestID."'";
			$GetRequest = $this->db->query($str);
			if ($GetRequest->num_rows()==0) {
				$result = array("result"=>"gagal", "status"=>"INVALID REQUEST", "databaseID"=>0, "complete"=>false, "req"=>null);
			} else {
				$str = "Update TblApproval 
						Set IsCancelled=1, CancelledDate=GETDATE(), CancelledNote='', 
							CancelledByEmail='".$_SESSION['logged_in']["email"]."',
							CancelledBy='".$_SESSION['logged_in']["useremail"]."', 
							CancelledByName='".$_SESSION['logged_in']["username"]."'
						Where ApprovalType='".$RequestType."' and RequestNo='".$RequestID."'";
				$Updt = $this->db->query($str);

				$str = "Select *, AddInfo5Value as DATABASEID  
						From TblApproval 
						Where ApprovalType='".$RequestType."' and RequestNo='".$RequestID."' and IsCancelled=1";
				$CheckLagi = $this->db->query($str);
				if ($CheckLagi->num_rows()>0) {
					$result = array("result"=>"sukses", "status"=>"CANCEL SUKSES", "databaseID"=>$CheckLagi->row()->DATABASEID, "complete"=>true, "req"=>$CheckLagi->row());
				} else {
					$result = array("result"=>"gagal", "status"=>"CANCEL GAGAL", "databaseID"=>0, "complete"=>false, "req"=>$GetRequest->row());
				}
			}
			//die(json_encode($result));
			return($result);
		}

		function GetRequest($RequestType="CREDIT LIMIT", $RequestID)
		{
			$str = "";

			if ($RequestType=="CREDIT LIMIT") {

				$str = "Select *, AddInfo1Value as KD_PLG, isnull(AddInfo6Value,'') as NM_PLG, isnull(AddInfo9Value,'') as WILAYAH, AddInfo5Value as DATABASEID
						From TblApproval Where ApprovalType='CREDIT LIMIT' 
						and RequestNo='".$RequestID."'";

			} else if ($RequestType=="CBD ON") {

				$str = "Select *, AddInfo1Value as KD_PLG, isnull(AddInfo2Value,'') as NM_PLG, isnull(AddInfo3Value,'') as WILAYAH, AddInfo5Value as DATABASEID
						From TblApproval Where ApprovalType='CBD ON' 
						and RequestNo='".$RequestID."'";

			} else if ($RequestType=="CBD OFF") {

				$str = "Select *, AddInfo1Value as KD_PLG, isnull(AddInfo2Value,'') as NM_PLG, isnull(AddInfo3Value,'') as WILAYAH, AddInfo5Value as DATABASEID
						From TblApproval Where ApprovalType='CBD OFF' 
						and RequestNo='".$RequestID."'";

			}

			$GetRequests = $this->db->query($str);
			if ($GetRequests->num_rows()==0) {
				return array();
			} else {
				return $GetRequests->result();
			}
		}

		function EmailResponseRequest($RequestType="CREDIT LIMIT", $Req, $html_content)
		{
			$configsys = $this->ConfigSysModel->Get();
			$smtp['protocol'] = $configsys->mail_protocol;
			$smtp['smtp_host'] = $configsys->mail_host;
			$smtp['smtp_port'] = $configsys->mail_port;
			$smtp['smtp_user'] = $configsys->mail_user;
			$smtp['smtp_pass'] = $configsys->mail_pwd;
			$smtp['smtp_crypto'] = $configsys->smtp_crypto;
			$smtp['charset'] = "utf-8";
			$smtp['mailtype'] = "html";
			$smtp['newline'] = "\r\n";

			$this->load->library('email');
			$ci = get_instance();

	        $ci->email->clear(true);
			$ci->email->initialize($smtp);
			$ci->email->from($configsys->mail_user, $configsys->mail_alias);
			if ($this->test_mode==true) {
				$ci->email->to($this->test_email);
			} else {
				$ci->email->to($Req->RequestByEmail);
			}
			$ci->email->cc("bhaktiautoemail.noreply@bhakti.co.id");

			if ($this->test_mode==false) {
				$this->load->model("SalesManagerModel");
				$WILAYAH = (($Req->AddInfo9Value==null)? "" : $Req->AddInfo9Value);
				if ($WILAYAH!="" && $WILAYAH!="JAKARTA") {
					$Kajul = $this->SalesManagerModel->GetKajul($WILAYAH);
					if ($Kajul!=null) {
						$ci->email->cc($Kajul->email_address);
					}
				}
			}

			$email_subject = "";
			if ($RequestType=="CREDIT LIMIT") {
				$email_subject = substr("REQ CL ".date("dMY",strtotime($Req->RequestDate))." ".$this->ReplaceChars($Req->AddInfo6Value),0,48);
			} else if ($RequestType=="CBD ON") {
				$email_subject = substr("REQ CBD ON ".date("dMY",strtotime($Req->RequestDate))." ".$this->ReplaceChars($Req->AddInfo2Value),0,48);
			} else if ($RequestType=="CBD OFF") {
				$email_subject = substr("REQ CBD OFF ".date("dMY",strtotime($Req->RequestDate))." ".$this->ReplaceChars($Req->AddInfo2Value),0,48);
			}

			$ci->email->subject($email_subject);
			$ci->email->message($html_content);
			//$ci->email->attach($attachment);
			
			if ($ci->email->send()) {
	        	$ci->email->clear(true);
	        	return "SUCCESS";
	        	//return("SUKSES");
			} else {
	        	$err = $ci->email->print_debugger();
	        	$ci->email->clear(true);
	        	return "FAILED : ".$err;
	        	//return("EMAIL TIDAK TERKIRIM : ".$err);
			}
		}		

		function EmailCancellationRequest($RequestType="CREDIT LIMIT", $Req, $html_content)
		{
			//die(json_encode($Req));

			$configsys = $this->ConfigSysModel->Get();
			$smtp['protocol'] = $configsys->mail_protocol;
			$smtp['smtp_host'] = $configsys->mail_host;
			$smtp['smtp_port'] = $configsys->mail_port;
			$smtp['smtp_user'] = $configsys->mail_user;
			$smtp['smtp_pass'] = $configsys->mail_pwd;
			$smtp['smtp_crypto'] = $configsys->smtp_crypto;
			$smtp['charset'] = "utf-8";
			$smtp['mailtype'] = "html";
			$smtp['newline'] = "\r\n";

			$this->load->library('email');
			$ci = get_instance();

	        $ci->email->clear(true);
			$ci->email->initialize($smtp);
			$ci->email->from($configsys->mail_user, $configsys->mail_alias);
			if ($this->test_mode==true) {
				$ci->email->to($this->test_email);
			} else {
				$ci->email->to($Req->RequestByEmail);
			}
			$ci->email->cc(array("bhaktiautoemail.noreply@bhakti.co.id","itdev.dist@bhakti.co.id"));

			$ListCC = array();

			if ($this->test_mode==false) {
				$this->load->model("SalesManagerModel");
				if ($RequestType=="CREDIT LIMIT") {
					$WILAYAH = (($Req->AddInfo9Value==null)? "" : $Req->AddInfo9Value);
				}
				if ($WILAYAH!="" && $WILAYAH!="JAKARTA") {
					$Kajul = $this->SalesManagerModel->GetKajul($WILAYAH);
					if ($Kajul!=null) {
						array_push($ListCC, $Kajul->email_address);
					}
				}
			}

			$REQ = $this->GetRequest($RequestType, $Req->RequestNo);
			foreach($REQ as $r) {
				array_push($ListCC, $r->ApprovedByEmail);
			}
			

			if ($Req->RequestByEmail!=$Req->CancelledByEmail) {
				array_push($ListCC, $r->CancelledByEmail);	
			}
			$ci->email->cc($ListCC);
			
			$email_subject = "REQ";
			if ($RequestType=="CREDIT LIMIT") {
				$email_subject = substr("REQ CL BATAL ".date("dMY",strtotime($Req->RequestDate))." ".$this->ReplaceChars($Req->AddInfo6Value),0,48);
			} else if ($RequestType=="CBD ON") {
				$email_subject = substr("REQ CBD ON BATAL ".date("dMY",strtotime($Req->RequestDate))." ".$this->ReplaceChars($Req->AddInfo2Value),0,48);
			} else if ($RequestType=="CBD OFF") {
				$email_subject = substr("REQ CBD OFF BATAL ".date("dMY",strtotime($Req->RequestDate))." ".$this->ReplaceChars($Req->AddInfo2Value),0,48);
			}

			$ci->email->subject($email_subject);
			$ci->email->message($html_content);
			//$ci->email->attach($attachment);
			
			if ($ci->email->send()) {
	        	$ci->email->clear(true);
	        	return "SUCCESS";
	        	//return("SUKSES");
			} else {
	        	$err = $ci->email->print_debugger();
	        	$ci->email->clear(true);
	        	return "FAILED : ".$err;
	        	//return("EMAIL TIDAK TERKIRIM : ".$err);
			}
		}
		public function EmailRequestUnlockToko($KodeRequest) 
	    {
			$RQ = $this->MsDealerModel->GetRequestUnlockToko($KodeRequest);
	    	$GM = $this->SalesManagerModel->GetGM();
			$edit=$this->MsDealerModel->UpdateApproverUnlockToko($KodeRequest, $GM);
			
			$err = "SUCCESSFUL";

			if (SEND_EMAIL=="ON")
			{
				$e_content = "<b>".$RQ->ReqName."</b> mengirimkan permintaan unlock dealer terkunci<br>";
				$e_content.= "Nama Dealer : <b>".$RQ->NmPlg."</b><br>";
				$e_content.= "Kode Dealer : <b>".$RQ->KdPlg."</b><br>";
				$e_content.= "Wilayah : <b>".$RQ->Wilayah."</b><br>";
				$e_content.= "Alasan : <b>".$RQ->RequestNote."</b><br>";
				$e_content.= "No Ref : <b>".$KodeRequest."</b><br>";
				$e_content.= "<br/>";

				$e_content.= "<style>";
				$e_content.= "	.btn {width:200px;text-align:center;cursor:pointer;border-radius:10px;";
				$e_content.= "		line-height:40px;vertical-align:middle;font-weight:bold;font-size:15pt;}";
				$e_content.= "	.btn:hover { background-color:#ffff00; color:#000; }";
				$e_content.= "</style>";

				$e_content.= "<div style='float:left;margin-right:20px;'><a href='".site_url("MsDealerApprovalOld/ProsesRequestUnlock?kdreq=".urlencode($KodeRequest)."&empid=".urlencode($GM->useremail))."'>";
				$e_content.= "<div class='btn' style='background-color:#73a9f5;color:#000;'>PROSES</div>";
				$e_content.= "</a></div>";

				//$e_content.= "<div style='float:left;margin-right:20px;'><a href='".site_url("MsDealer/ApproveUnlock?kdreq=".urlencode($KodeRequest)."&empid=".urlencode($GM->useremail))."'>";
				//$e_content.= "<div class='btn' style='background-color:#33ff33;color:#000;'>APPROVE</div>";
				//$e_content.= "</a></div>";

				//$e_content.= "<div style='float:left;'><a href='".site_url("MsDealer/RejectUnlock?kdreq=".urlencode($KodeRequest)."&empid=".urlencode($GM->useremail))."'>";
				//$e_content.= "<div class='btn' style='background-color:#800000;color:#fff;'>REJECT</div>";
				//$e_content.= "</a></div>";

				$e_content.= "<div style='clear:both;'></div>";
				$this->email->from("bhaktiautoemail.noreply@bhakti.co.id", "BHAKTI.CO.ID AUTO-EMAIL");

				if ($this->test_mode == true)
					$this->email->to($this->test_email); 
				else
					$this->email->to(($GM->email_address!="")?$GM->email_address:$GM->useremail); 

				$plg = $this->MsDealerModel->ReplaceChars($RQ->NmPlg);
				$subject = substr("Request Unlock Toko ".$plg,0,40)." ".date("Ymd");
				$this->email->subject($subject);
				$this->email->message($e_content);	
				//$this->email->send();
				if ($this->email->send()) {
				} else {
		        	$err = $this->email->print_debugger();
				}			
			    $this->email->clear(true);
			}
			return $err;
	    }
		public function EmailCancelUnlockToko($KodeRequest) 
	    {
	    	$rq = $this->MsDealerModel->GetRequestUnlockToko($KodeRequest);
			if (SEND_EMAIL=="ON")
			{
				$e_content = "<b>".$rq->ReqName."</b> <b>MEMBATALKAN</b> PERMINTAAN UNLOCK DEALER TERKUNCI<br>";
				$e_content.= "Alasan : <b>".$rq->CancelledNote."</b><br>";
				$e_content.= "-----------------------------------<br>";
				$e_content.= "Nama Dealer : <b>".$rq->NmPlg."</b><br>";
				$e_content.= "Kode Dealer : <b>".$rq->KdPlg."</b><br>";
				$e_content.= "Wilayah : <b>".$rq->Wilayah."</b><br>";
				$e_content.= "No Ref : <b>".$KodeRequest."</b><br>";
				$e_content.= "<br/>";

				$this->email->from("bitautoemail.noreply@gmail.com", "BHAKTI.CO.ID AUTO-EMAIL");
				$CCs = (($rq->ReqEmail!="") ? $rq->ReqEmail : $rq->RequestBy);
		    	$GM = $this->SalesManagerModel->GetGM();
				$TOs = (($GM->email_address!="")?$GM->email_address:$GM->useremail); 

				if ($this->test_mode == true) {
					$this->email->to($this->test_email); 
					$this->email->cc($this->test_email); 
				} else {
					$this->email->to($TOs); 
					$this->email->cc($CCs); 
				}

				$plg = $this->MsDealerModel->ReplaceChars($rq->NmPlg);
				$subject = substr("Request Unlock Toko ".$plg,0,40)." ".date("Ymd");
				$this->email->subject($subject);
				$this->email->message($e_content);	
				$this->email->send();
			    $this->email->clear();
			}
			return true;
	    }

		function GetLastTenRequests($KdPlg, $Divisi)
		{
			$str = "Select Top 10 RequestNo, REPLACE(RequestNo,'/','_') as RequestID, CLAwal, CLBaru, sum(ApprovalNeeded) as ApprovalNeeded,sum(ApprovedCount) as ApprovedCount, ExpiryDate, ";
			$str.= "	convert(varchar(20), ExpiryDate, 106) as StrExpiryDate, IsCancelled, MAX(ApprovedDate) as ApprovedDate, convert(varchar(20),MAX(ApprovedDate),113) as ApprovedDateStr, ";
			$str.= " 	RequestBy, RequestByName, convert(varchar(20),min(RequestDate),113) as RequestDateStr, MIN(RequestDate) as RequestDate,  ";
			$str.= " 	(case when IsCancelled=1 then 'CANCELLED' else (case when sum(RejectedCount)>0 then 'REJECTED' when sum(ApprovedCount)>=sum(ApprovalNeeded) then 'APPROVED' ";
			$str.= " 		when convert(varchar(8),ExpiryDate,112)<convert(varchar(8),Getdate(),112) then 'EXPIRED' else 'WAITING FOR APPROVAL' end) ";
			$str.= " 	end) as RequestStatus, BhaktiFlag, convert(varchar(20),min(BhaktiProcessDate),113) as BhaktiProcessDate";
			$str.= " From (";
			$str.= " 	Select RequestNo, AddInfo7Value as CLAwal, AddInfo3Value as CLBaru, Priority, ApprovalNeeded, ExpiryDate, MAX(ApprovedDate) as ApprovedDate, ";
			$str.= " 		RequestBy, RequestByName, BhaktiFlag, BhaktiProcessDate, MIN(RequestDate) as RequestDate, isnull(IsCancelled,0) as IsCancelled, ";
			$str.= " 		SUM(case when ApprovalStatus='UNPROCESSED' then 1 else 0 end) as UnprocessedCount,";
			$str.= " 		SUM(case when ApprovalStatus='APPROVED' then 1 else 0 end) as ApprovedCount,";
			$str.= " 		SUM(case when ApprovalStatus='REJECTED' then 1 else 0 end) as RejectedCount";
			$str.= " 	From TblApproval ";
			$str.= " 	Where ApprovalType='CREDIT LIMIT' and AddInfo1Value='".$KdPlg."' and AddInfo2Value='".htmlspecialchars_decode($Divisi)."'";
			$str.= " 	Group By RequestNo, AddInfo7Value, AddInfo3Value, Priority, ApprovalNeeded, ExpiryDate, RequestBy, RequestByName, BhaktiFlag, BhaktiProcessDate, isnull(IsCancelled,0)";
			$str.= " ) GAB ";
			$str.= " Group By RequestNo, REPLACE(RequestNo,'/','_'), CLAwal, CLBaru, ExpiryDate, RequestBy, RequestByName, IsCancelled, BhaktiFlag, BhaktiProcessDate ";
			$str.= " Order By RequestDate Desc";
			//die($str)
			$GetRequests = $this->db->query($str);
			if ($GetRequests->num_rows()==0) {
				return array();
			} else {
				return $GetRequests->result();
			}
		}

		function GetPendingCL()
		{
			$str = "Select REQ.RequestNo, REPLACE(REQ.RequestNo,'/','_') as RequestID, REQ.CLAwal, REQ.CLBaru, REQ.KdPlg as Kd_Plg, REQ.KdPlg, REQ.Divisi, ";
			$str.= " 	REQ.RequestBy, REQ.RequestByName, convert(varchar(20),REQ.RequestDate,113) as RequestDateStr, REQ.RequestDate, REQ.ApprovedBy, ";
			$str.= " 	REQ.DatabaseID, dbs.BranchId, dbs.NamaDb, dbs.AlamatWebService, dbs.[Server], dbs.[Database]  ";
			$str.= " From (";
			$str.= " 	Select RequestNo, CAST(isnull(AddInfo7Value,'0') as MONEY) as CLAwal, CAST(isnull(AddInfo3Value,'0') as MONEY) as CLBaru, ";
			$str.= "		RequestBy, RequestByName, RequestDate, MAX(convert(varchar(8),ApprovedDate,112)+' '+ApprovedByName) as ApprovedBy, 
							CAST(AddInfo5Value as INT) as DatabaseID, AddInfo1Value as KdPlg, AddInfo2Value as Divisi ";
			$str.= " 	From TblApproval ";
			$str.= " 	Where ApprovalType='CREDIT LIMIT' and BhaktiFlag in ('PENDING') and ApprovalStatus='APPROVED'
							and year(ApprovedDate)=".date("Y")." and month(ApprovedDate)=".date("m")." 
						Group By RequestNo, CAST(isnull(AddInfo7Value,'0') as MONEY), CAST(isnull(AddInfo3Value,'0') as MONEY), ";
			$str.= "		RequestBy, RequestByName, RequestDate, CAST(AddInfo5Value as INT), AddInfo1Value, AddInfo2Value";
			$str.= " ) REQ inner Join MsDatabase DBS on REQ.DatabaseID=DBS.DatabaseId ";
			$str.= " Order By RequestDate";
			//die($str);
			$GetRequests = $this->db->query($str);
			if ($GetRequests->num_rows()==0) {
				return array();
			} else {
				return $GetRequests->result();
			}			
		}

		function GetPendingCBD()
		{
			$str = "Select Req.ApprovalType, REQ.RequestNo, REPLACE(REQ.RequestNo,'/','_') as RequestID, REQ.KdPlg, Req.NmPlg, ";
			$str.= " 	REQ.RequestBy, REQ.RequestByName, convert(varchar(20),REQ.RequestDate,113) as RequestDateStr, REQ.RequestDate, 
						REQ.ApprovedBy, REQ.ApprovedDate, ";
			$str.= " 	REQ.DatabaseID, dbs.BranchId, dbs.NamaDb, dbs.AlamatWebService, dbs.[Server], dbs.[Database]  ";
			$str.= " From (";
			$str.= " 	Select ApprovalType, RequestNo, RequestBy, RequestByName, RequestDate, 
							MAX(convert(varchar(8),ApprovedDate,112)+' '+ApprovedByName) as ApprovedBy, MAX(ApprovedDate) as ApprovedDate,
							CAST(AddInfo5Value as INT) as DatabaseID, AddInfo1Value as KdPlg, AddInfo2Value as NmPlg, AddInfo6Value as TglOff ";
			$str.= " 	From TblApproval ";
			$str.= " 	Where ApprovalType in ('CBD ON','CBD OFF') and BhaktiFlag='PENDING' and ApprovalStatus='APPROVED' and AddInfo6Value<='".date("Y-m-d")."'
						Group By ApprovalType, RequestNo, AddInfo2Value, AddInfo6Value, ";
			$str.= "		RequestBy, RequestByName, RequestDate, CAST(AddInfo5Value as INT), AddInfo1Value";
			$str.= " ) REQ inner Join MsDatabase DBS on REQ.DatabaseID=DBS.DatabaseId ";
			$str.= " Order By RequestDate";
			//die($str);
			$GetRequests = $this->db->query($str);
			if ($GetRequests->num_rows()==0) {
				return array();
			} else {
				return $GetRequests->result();
			}			
		}

		function CreateRequestUnlockToko($data) 
		{
			$ERR_MSG = "";
			$requestId = $this->CreateKodeRequestUnlockToko($data);

			$ExpiryDate = null;
			$MsConfig = $this->db->query("select * from Ms_Config WHERE ConfigType='REQUEST UNLOCK'")->row();
			$configValue = $MsConfig->ConfigValue;

			$this->db->flush_cache();

			if($configValue!='UNLIMITED'){
				$getExpiryDate = $this->db->query(" ".$configValue." as ExpiryDate ")->row();
				$ExpiryDate = $getExpiryDate->ExpiryDate;
				
				$this->db->flush_cache();

				if($ExpiryDate!='') $ExpiryDate = date("Y-m-d",strtotime($ExpiryDate));

			}

			$this->db->set("RequestID", $requestId);
			$this->db->set("RequestBy", $_SESSION["logged_in"]["useremail"]);
			$this->db->set("RequestDate", date('Y-m-d H:i:s'));
			$this->db->set("KdPlg", $data["KDPLG"]);
			$this->db->set("NmPlg", $data["NMPLG"]);
			$this->db->set("Wilayah", $data["WILAYAH"]);
			$this->db->set("DatabaseID", $_SESSION["databaseID"]);
			$this->db->set("UnlockBegin", date("Y-m-d"));
			$this->db->set("RequestNote", strtoupper($data["KET"]));
			$this->db->set("IsApproved", 0);
			$this->db->set("ExpiryDate", $ExpiryDate);
			$this->db->insert("TblMsDealerUnlock");

			if( ($errors = sqlsrv_errors() ) != null) {
		        foreach( $errors as $error ) {
		            //echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
		            $ERR_CODE = $error["code"];
		            $ERR_MSG = "code: ".$ERR_CODE."<br />";
		            $ERR_MSG.= "message: ".$error['message']."<br />";
		        }

		    } else {
		    	$ERR_MSG = "SUCCESSFUL";
		    }

			return array("requestID"=>$requestId, "result"=>"$ERR_MSG");
		}

		function UpdateApproverUnlockToko($requestId, $GM) 
		{
			$ERR_MSG = "";

			$this->db->where("RequestID", $requestId);
			$this->db->set("RequestBy", $_SESSION["logged_in"]["useremail"]);
			$this->db->set("RequestDate", date('Y-m-d H:i:s'));
			$this->db->set("ApprovedBy", (($GM->email_address!="")?$GM->email_address:$GM->useremail));
			// $this->db->set("NmPlg", $data["NMPLG"]);
			// $this->db->set("Wilayah", $data["WILAYAH"]);
			// $this->db->set("DatabaseID", $_SESSION["databaseID"]);
			// $this->db->set("UnlockBegin", date("Y-m-d"));
			// $this->db->set("RequestNote", strtoupper($data["KET"]));
			// $this->db->set("IsApproved", 0);
			$this->db->update("TblMsDealerUnlock");

			if( ($errors = sqlsrv_errors() ) != null) {
		        foreach( $errors as $error ) {
		            //echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
		            $ERR_CODE = $error["code"];
		            $ERR_MSG = "code: ".$ERR_CODE."<br />";
		            $ERR_MSG.= "message: ".$error['message']."<br />";
		        }

		    } else {
		    	$ERR_MSG = "SUCCESSFUL";
		    }

			return array("requestID"=>$requestId, "result"=>"$ERR_MSG");
		}

		function CreateKodeRequestUnlockToko($data)
		{
			$KodeRequest = "";
			$prefiks = "UL/".$data["KDPLG"]."/".date("ym")."/";
			$str = "Select CAST(Right(MAX(RequestID),2) as INT) as RequestIDMax From TblMsDealerUnlock 
					Where left(RequestID,".(string)strlen($prefiks).")='".$prefiks."'";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				$NoMax = $res->row()->RequestIDMax;
				$NoMax = $NoMax + 1;
				if ($NoMax<10) 
					$KodeRequest = $prefiks."0".$NoMax;
				else
					$KodeRequest = $prefiks.$NoMax;
			} else {
				$KodeRequest = $prefiks."01";
			}
			return $KodeRequest;
		}

		function GetRequestUnlockToko($Kode)
		{
			$str = "Select a.*, b.[UserName] as ReqName, isnull(b.Email,'') as ReqEmail,
						isnull(c.[UserName],'') as AppName, isnull(c.Email,'') as  AppEmail,
						isnull(d.[UserName],'') as CanName, isnull(d.Email,'') as CanEmail,
						dateadd(day,8,getdate()) as DefaultEndDate,
						convert(varchar(50),dateadd(day,8,getdate()),106) as DefaultEndDateStr,
						convert(varchar(50),dateadd(day,8,getdate()),101) as DefaultEndDateStr2
					From TblMsDealerUnlock a inner join msuserhd b on a.RequestBy=b.UserEmail 
						left join msuserhd c on a.ApprovedBy=c.UserEmail 
						left join msuserhd d on a.CancelledBy=d.UserEmail
					Where a.RequestID = '".$Kode."'";
			$res = $this->db->query($str);
			if($res->num_rows()>0)
				return $res->row();
			else
				return null;
		}

		function GetRequestUnlockByToko($Kode)
		{
			$str = "Select a.*, b.[UserName] as ReqName, isnull(b.Email,'') as ReqEmail,
						isnull(c.[UserName],'') as AppName, isnull(c.Email,'') as  AppEmail
					From TblMsDealerUnlock a inner join msuserhd b on a.RequestBy=b.UserEmail 
						left join msuserhd c on a.ApprovedBy=c.UserEmail 
					Where a.KdPlg = '".$Kode."' 
					and isnull(a.IsCancelled,0) = 0
					and ((a.IsApproved=0 and a.RequestDate>='2022-09-09') 
						or (a.IsApproved=1 and '".date("Ymd")."' between convert(varchar(8),a.UnlockBegin,112) and convert(varchar(8),a.UnlockEnd,112)))";
			
			$res = $this->db->query($str);
			if($res->num_rows()>0)
				return $res->row();
			else
				return null;
		}

		function ApproveUnlockToko($KodeRequest, $ApprovedBy, $EndDate, $Note) 
		{
			$this->db->where("RequestID", $KodeRequest);
			$this->db->set("IsApproved", 1);
			$this->db->set("ApprovedBy", $ApprovedBy);
			$this->db->set("ApprovedDate", date('Y-m-d H:i:s'));
			$this->db->set("ApprovalNote", strtoupper($Note));
			$this->db->set("UnlockBegin", date("Y-m-d"));
			$this->db->set("UnlockEnd", date("Y-m-d", strtotime($EndDate)));
			$this->db->update("TblMsDealerUnlock");

			return true;
		}		

		function RejectUnlockToko($KodeRequest, $ApprovedBy, $Note) 
		{
			$this->db->where("RequestID", $KodeRequest);
			$this->db->set("IsApproved", 2);
			$this->db->set("ApprovedBy", $ApprovedBy);
			$this->db->set("ApprovedDate", date('Y-m-d H:i:s'));
			$this->db->set("ApprovalNote", strtoupper($Note));
			$this->db->update("TblMsDealerUnlock");

			return true;
		}		

		function CancelRequestUnlockToko($KodeRequest, $Note) 
		{
			$this->db->where("RequestID", $KodeRequest);
			$this->db->set("IsCancelled", 1);
			$this->db->set("CancelledBy", $_SESSION["logged_in"]["useremail"]);
			$this->db->set("CancelledDate", date('Y-m-d H:i:s'));
			$this->db->set("CancelledNote", strtoupper($Note));
			$this->db->update("TblMsDealerUnlock");
			return true;
		}

		function GetUnlockedDealer()
		{
			$str = "Select a.*
					From TblMsDealerUnlock a inner join msuserhd b on a.RequestBy=b.UserEmail 
						left join msuserhd c on a.ApprovedBy=c.UserEmail 
						left join msuserhd d on a.CancelledBy=d.UserEmail
					Where IsApproved=1
					and cast(convert(varchar(50),GETDATE(),101) as DATETIME) between UnlockBegin and UnlockEnd ";
			//die($str);
			$GetRequests = $this->db->query($str);
			if ($GetRequests->num_rows()==0) {
				return array();
			} else {
				return $GetRequests->result();
			}			
		}

		function GetPendingRequests($RequestType="CREDIT LIMIT", $user="", $userid="0")
		{
			$str = "";
			$str.= "Select '".$RequestType."' as RequestType, RequestNo, RequestByName, MIN(RequestDate) as RequestDate, 
						AddInfo1Value as KodePelanggan, AddInfo2Value as Divisi, CAST(AddInfo3Value as MONEY) as CreditLimitBaru,
						AddInfo6Value as NamaPelanggan, CAST(AddInfo7Value as MONEY) as CreditLimitPermanent, AddInfo9Value as Wilayah,
						CAST(AddInfo11Value as MONEY) as CreditLimitTemporary, CAST(AddInfo12Value as MONEY) as KenaikanCL,  
						[Priority], ExpiryDate, ApprovalNeeded, sum(case when ApprovalStatus='UNPROCESSED' then 1 else 0 end) as UnprocessedCount, 
						(case when sum(case when ApprovalStatus='APPROVED' then 1 else 0 end)>ApprovalNeeded then ApprovalNeeded else sum(case when ApprovalStatus='APPROVED' then 1 else 0 end) end) as ApprovedCount,
						sum(case when ApprovalStatus='REJECTED' then 1 else 0 end) as RejectedCount,
						'".site_url()."MsDealerApprovalOld/ProcessRequest?id='+RequestNo+'&type=cl&mc=1' as Url 
					From TblApproval
					Where ApprovalType='CREDIT LIMIT'
					and (IsCancelled=0 or IsCancelled is null)
					and ExpiryDate>=cast(convert(varchar(50),getdate(),101) as datetime)
					and RequestNo+'-'+cast(Priority as varchar(2)) in (Select RequestNo+'-'+cast(Priority as varchar(2)) From TblApproval where ApprovalType='CREDIT LIMIT' 
						and (approvedby='".$user."' or approvedby='".$userid."') 
						and approvalstatus='UNPROCESSED')
					Group By RequestNo, RequestByName,
						AddInfo1Value, AddInfo2Value, CAST(AddInfo3Value as MONEY),
						AddInfo6Value, CAST(AddInfo7Value as MONEY), AddInfo9Value,
						CAST(AddInfo11Value as MONEY), CAST(AddInfo12Value as MONEY), 
						[Priority], ExpiryDate, ApprovalNeeded
					Having sum(case when ApprovalStatus='REJECTED' then 1 else 0 end)=0
					and (case when sum(case when ApprovalStatus='APPROVED' then 1 else 0 end)>ApprovalNeeded then ApprovalNeeded else sum(case when ApprovalStatus='APPROVED' then 1 else 0 end) end)<ApprovalNeeded";
			$str.= " UNION ALL ";
			$str.= "Select 'UNLOCK TOKO' as RequestType, RequestId as RequestNo, RequestBy as RequestByName, RequestDate, 
						KdPlg, '' as Divisi, 0 as CreditLimitBaru, 
						NmPlg, 0 as CLPermanent, Wilayah, 0 as CLTemporary, 0 as KenaikanCL, 
						1 as [Priority], cast(convert(varchar(50),dateadd(day,1,RequestDate),101) as datetime) as ExpiryDate,
						1 as ApprovalNeeded, 0 as UnprocessedCount, 0 as ApprovalCount, 0 as RejectedCount,
						'".site_url()."MsDealerApprovalOld/ProsesRequestUnlock?kdreq='+RequestId+'&type=cl&mc=1' as Url
					From TblMsDealerUnlock
					WHERE (IsApproved=0) 
						and (IsCancelled=0 or IsCancelled is null)
						and (Approvedby='".$user."' or Approvedby='".$userid."') 
						and cast(convert(varchar(50),dateadd(day,1,RequestDate),101) as datetime)>=cast(convert(varchar(50),getdate(),101) as datetime)
					";
			$str.= " ORDER BY RequestDate";
			// die($str);
			$GetRequests = $this->db->query($str);
			if ($GetRequests->num_rows()>0) {
				// die(json_encode($GetRequests->result()));
				return $GetRequests->result();
			} else {
				return array();
			}
		}



	}
?>