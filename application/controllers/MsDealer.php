<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MsDealer extends MY_Controller {
	//unlock toko pinadhin k model
	public $alert = "";
	public $test_mode = false;
	public $test_email = "itdev.dist@bhakti.co.id";
	public $test_whatsapp = "";
	public $whatsapp_account = "SALES";

	public $newRuleDate = "05/01/2022";
	public $approvers = array();
	public $recipients= array();
	public $whatsapps = array();

	function __construct()
	{
		parent::__construct();
		$this->load->model('MsDealerModel');
		$this->load->model('SalesManagerModel');
		$this->load->model('MsConfigRequestApprovalModel');
		$this->load->model('MsConfigModel'); 
		$this->load->model('GzipDecodeModel');
		$this->load->library("email");
		$this->load->helper('url');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		$this->API_BKT = $_SESSION['conn']->AlamatWebService.$this->ConfigSysModel->Get()->bktapi_appname;
		$this->BKTAPI = $this->ConfigSysModel->Get()->bktapi_appname;
		$this->API_BKT_HO = $this->ConfigSysModel->Get()->bktapi_ho_url;
		$this->API_MSG = $this->ConfigSysModel->Get()->messageapi_url;
	}

	public function urlTest()
	{
		die(date("Ymd"));
		/*$URL = "http://10.1.0.92:90/".$this->API_BKT."/MasterDealer/ChangeLimitDealer?svr=".urlencode(trim("10.1.48.200"));
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
			$alert = "";

			$userBranch = $_SESSION["branchID"];
			$userDB = $_SESSION['databaseID'];
			$data["DefaultWilayah"] = $_SESSION["logged_in"]["branch"];
			


			$URL = $this->API_BKT."/MasterDealer/GetListDealer?api=APITES&ubranch=".urlencode($userBranch);
			//die($URL);
			$HTTPRequest = file_get_contents($URL);
			if ($HTTPRequest == null) {
				exit("<b>Koneksi ke Database Bhakti Sedang Ada Gangguan. Coba Kembali Beberapa Saat Lagi</b><br>
					 Apabila Gangguan berlangsung <u>lebih dari</u> 30 menit, <u?Hubungi IT</u>");
			}

	        // $GetDealer = json_decode($HTTPRequest, true);
	        $GetDealer = $this->GzipDecodeModel->_decodeGzip_true($HTTPRequest);
	        if ($GetDealer["result"]=="sukses"){
	        	$ListDealer = $GetDealer["data"];
				for($i=0; $i<count($ListDealer); $i++){
					array_push($dealers, trim($ListDealer[$i]["NM_PLG"])." - ".trim($ListDealer[$i]["KD_PLG"])." - ".trim($ListDealer[$i]["WILAYAH"]));
				}
		    } 

			$URL = $this->API_BKT."/MasterDealer/GetListWilayah?api=APITES&ubranch=".urlencode($userBranch);
			//die($URL);
	        $GetWilayah = file_get_contents($URL);
	        // $GetWilayah = json_decode(file_get_contents($URL),true);
	        $GetWilayah = $this->GzipDecodeModel->_decodeGzip_true($GetWilayah);
	        //die(json_encode($GetWilayah));
	        if ($GetWilayah["result"]=="sukses"){
	        	$ListWilayah = $GetWilayah["data"];
		    }

		    if (count($ListDealer) > 0) {

		    	$data["ListWilayah"] = $ListWilayah;
				$data["ListDealer"] = $ListDealer;
		        $data["Dealers"] = $dealers;
		    	$this->RenderView('MsDealerList', $data);

		    } else {

				$data["content_html"] = '<script language="javascript">';
		        $data["content_html"].= 'alert("GAGAL MENGAMBIL LIST DEALER KE DB BHAKTI")';

		        if($alert!="") {
		        	$data["content_html"].= 'alert("'.urldecode($alert).'")';
		        }
		        $data["content_html"].= '</script>';
		        $this->RenderView('MsDealerList', $data);		    	

		    }
		} else {
			redirect("ConnectDB");
		}      
	}	   

	public function TokoTerkunci($flag=0){
		// die(json_encode($_SESSION));
		if(isset($_SESSION['conn'])) {

			// $params = array();			
			// $params['LogDate'] = date("Y-m-d H:i:s");
			// $params['UserID'] = $_SESSION["logged_in"]["userid"];
			// $params['UserName'] = $_SESSION["logged_in"]["username"];
			// $params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			// $params['Module'] = "TOKO TERKUNCI";
			// $params['TrxID'] = date("YmdHis");
			// $params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU TOKO TERKUNCI";
			// $params['Remarks'] = "";
			// $params['RemarksDate'] = 'NULL';
			// $this->ActivityLogModel->insert_activity($params);

			// $svr = $_SESSION["conn"]->Server;
			// $db = $_SESSION["conn"]->Database;
			// $url = $_SESSION["conn"]->AlamatWebService;
			// $url = $url.$this->API_BKT."/MasterDealer/GetListTokoTerkunci?api=APITES&svr=".urlencode($svr)."&db=".urlencode($db);
			// // die($url);
	        // $result = json_decode(file_get_contents($url), true);
	        // $str = "";
			// if($result["result"]=="sukses"){
			// 	$dealers = $result["data"];
			// 	for ($i=0;$i<count($dealers);$i++) {
			// 		if (isset($dealers[$i])) {
			// 			$req = $this->MsDealerModel->GetRequestUnlockByToko($dealers[$i]["KD_PLG"]);
			// 			if ($req==null) {
			// 				$str.= "Tidak ada request untuk toko ".$dealers[$i]["NM_PLG"]." ".$dealers[$i]["KD_PLG"]."<br/>";
			// 				$dealers[$i]["REQUEST"] = null;
			// 			} else {
			// 				$str.= "Ada request untuk toko ".$dealers[$i]["NM_PLG"]." ".$dealers[$i]["KD_PLG"]."<br/>";
			// 				$dealers[$i]["REQUEST"] = $req;
			// 			}
			// 		} else {
			// 			$dealers[$i]["REQUEST"] = null;
			// 		}
			// 	}
			// 	$data["dealers"] = $dealers;
			// 	$data["alert"] = "";

			// 	$params['Remarks']="SUCCESS";
			// 	$params['RemarksDate'] = date("Y-m-d H:i:s");
			// 	$this->ActivityLogModel->update_activity($params);
			// } else {
			// 	$data["dealers"] = array();
			// 	$data["alert"] = $result["error"];

			// 	$params['Remarks']="FAILED - Data Tidak Ditemukan";
			// 	$params['RemarksDate'] = date("Y-m-d H:i:s");
			// 	$this->ActivityLogModel->update_activity($params);
			// }

			$data["alert"] ='';
			if ($flag==1) {
				$alert = "Request Buka Lock Toko Telah Diemail!";
				// $alert.= "Whatsapp User: ".$_SESSION["logged_in"]["whatsapp"]."\n\n";
				if ($_SESSION["logged_in"]["whatsapp"]=="") {
					$alert.= "No Whatsapp Anda Belum Terdaftar. Daftarkan No Whatsapp Anda untuk menerima notifikasi saat request Anda Ditolak/Disetujui !";
				}
				$data["alert"] = $alert;
			}
			
			$data["MO"] = false;
			$data["menu"] = 'TokoTerkunci';
			// die($_SESSION["branchID"]);
			$this->RenderView('MsDealerTerkunciView',$data);
		}else{
			redirect('Home');
		}
	}


	public function ListTokoTerkunci(){

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "TOKO TERKUNCI";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU TOKO TERKUNCI";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$svr = $_SESSION["conn"]->Server;
		$db = $_SESSION["conn"]->Database;
		$url = $_SESSION["conn"]->AlamatWebService;
		$url = $this->API_BKT."/MasterDealer/GetListTokoTerkunci?api=APITES&svr=".urlencode($svr)."&db=".urlencode($db)."&iDisplayStart=".$this->input->get('iDisplayStart')."&iSortCol_0=".$this->input->get('iSortCol_0')."&sSortDir_0=".$this->input->get('sSortDir_0')."&iDisplayLength=".$this->input->get('iDisplayLength')."&sSearch=".$this->input->get('sSearch');


		// die($url);
	    $result = file_get_contents($url);
	    // $result = json_decode(file_get_contents($url), true);
	    $result = $this->GzipDecodeModel->_decodeGzip_true($result);
	    $str = "";

	    $data_list=array();
	    $total =0;

		if($result["result"]=="sukses"){
			$dealers = $result["data"]['list'];
			$total = $result["data"]['total'];
			for ($i=0;$i<count($dealers);$i++) {
				if (isset($dealers[$i])) {
					$req = $this->MsDealerModel->GetRequestUnlockByToko($dealers[$i]["KD_PLG"]);
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

			
			$data_hasil=array();

			if($this->input->get('iDisplayStart')==0){
				$no=1;
			}else{
				$no=$this->input->get('iDisplayStart')+1;
			}
			
			if(!empty($dealers)){
				$MO = false;
				foreach (json_decode(json_encode($dealers)) as $key => $r) {
					$action='';
					$req ='';
					$list=array();

					$list[] 	= '<center>'.$no.'</center>';
					$list[] 	= $r->KD_PLG;
					$list[] 	= $r->NM_PLG;
					$list[] 	= $r->WILAYAH;
					$list[] 	= 'CL MISHIRIN : '.number_format($r->CL_MISHIRIN).'<br>CL CO&SANITARY : '.number_format($r->CL_COSANITARY);

			        if (($_SESSION["branchID"]=="JKT" && $MO==true) ||
			            ($_SESSION["branchID"]=="JKT" && substr($r->WILAYAH,0,2)=="MO" && trim($r->WILAYAH)!="MODERN OUTLET") ||
						($_SESSION["branchID"]!="JKT" && substr($r->WILAYAH,0,2)=="MO" && trim($r->WILAYAH)!="MODERN OUTLET") ||
						($_SESSION["branchID"]!="JKT" && $r->WILAYAH=="PROYEK")) {
			            if ($r->REQUEST==null) {
			            	$onclick="'".$r->KD_PLG."','".$r->NM_PLG."','".$r->WILAYAH."'";
							$req .= '  <span id="colReq'.$no.'"><div class="btn btnRequest" onclick="btnRequest('.$onclick.')">Buat<br>Request</div></span>';
			            } else {
							$req = "  <span id='colReq".$no."'>";
							  if ($r->REQUEST->IsApproved==0) {
							    $req .= "  <b>Request Unlock Ditemukan<br>[MENUNGGU APPROVAL]</b>";

							    $onclick="'".$r->REQUEST->RequestID."'";
							    $req .= '  <br><button class="btnResend"  onclick="btnResend('.$onclick.')">RESEND EMAIL</button>';

							    $onclick="btnCancelReq('".$r->REQUEST->RequestID."','".$no."')";
							    $req .= '  <button class="btnCancelReq" onclick="'.$onclick.'">CANCEL REQUEST</button>';
							  } else {
							    $req .= "  <b><font color='#015208'>Request Unlock Ditemukan [APPROVED]<br>Unlock S/D Tgl.".date("d-M-Y",strtotime($r->REQUEST->UnlockEnd))."</font></b>";
							  // $req .= "  <br><button class='btnResync' reqid='".$r->REQUEST->RequestID."'>RESYNC KE BHAKTI</button>";
			      
							  }
							$req .= "  </span>";
			            }
					}else{
						$req .= "  <span></span>";
					}

					$list[] 	= $req;

					$onclick="'".$r->KD_PLG."','".$r->NM_PLG."','".$r->WILAYAH."'";
					$list[] 	= '<button class="btn btnFakturGantung" kode="'.$r->KD_PLG.'" nama="'.$r->NM_PLG.'" wil="'.$r->WILAYAH.'" onclick="btnFakturGantung('.$onclick.')">Faktur<br>Gantung</button>';
                  

					$data_list[]=$list;
					$no++;
				}
			}

			$data["dealers"] = $dealers;
			$data["alert"] = "";

			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
		} else {
			$data["dealers"] = array();
			$data["alert"] = $result["error"];
			$params['Remarks']="FAILED - Data Tidak Ditemukan";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
		}

		if(!empty($this->input->get('sEcho'))){
			$secho = $this->input->get('sEcho');
		}else{
			$secho = 1;
		}

		$data_hasil['sEcho']=$secho;
		$data_hasil['iTotalRecords']=$total;
		$data_hasil['iTotalDisplayRecords']=$total;
		$data_hasil['aaData']=$data_list;

		print_r(json_encode($data_hasil));

	}


	public function TokoTerkunci2($flag=0){
		if(isset($_SESSION['conn'])){
			$svr = $_SESSION["conn"]->Server;
			$db = $_SESSION["conn"]->Database;
			$url = $_SESSION["conn"]->AlamatWebService;
			$str = "";
	        
	        $result = file_get_contents($this->API_BKT."/MasterDealer/GetListTokoTerkunci?api=APITES&svr=".urlencode($svr)."&db=".urlencode($db));
	        // $result = json_decode(file_get_contents($url.API_BKT."/MasterDealer/GetListTokoTerkunci?api=APITES"), true);

	        $result = $this->GzipDecodeModel->_decodeGzip_true($result);
			if($result["result"]=="sukses"){
				$dealers = $result["data"];

				/*$Conns = $this->MasterDbModel->getList();
				foreach ($Conns as $cn) {
					$resultMOCBG = json_decode(file_get_contents($cn->AlamatWebService.API_BKT."/MasterDealer/GetListTokoTerkunciMO?api=APITES"), true);

				}*/
				
				$tampDealer = array();
				for ($i=0;$i<count($dealers);$i++) {

					if (isset($dealers[$i])) {
						$req = $this->MsDealerModel->GetRequestUnlockByToko($dealers[$i]["KD_PLG"]);
						if ($req==null) {
							$str.= "Tidak ada request untuk toko ".$dealers[$i]["NM_PLG"]." ".$dealers[$i]["KD_PLG"]."<br/>";
							$tampDealer[$i]["REQUEST"] = null;
						} else {
							$str.= "Ada request untuk toko ".$dealers[$i]["NM_PLG"]." ".$dealers[$i]["KD_PLG"]."<br/>";
							$tampDealer[$i]["REQUEST"] = $req;
						}
					} else {
						$tampDealer[$i]["REQUEST"] = null;
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

			$data["menu"] = 'TokoTerkunci';

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
			// $params = array();
			// $params['LogDate'] = date("Y-m-d H:i:s");
			// $params['UserID'] = $_SESSION["logged_in"]["userid"];
			// $params['UserName'] = $_SESSION["logged_in"]["username"];
			// $params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			// $params['Module'] = "TOKO TERKUNCI MO";
			// $params['TrxID'] = date("YmdHis");
			// $params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU TOKO TERKUNCI MO";
			// $params['Remarks'] = "";
			// $params['RemarksDate'] = 'NULL';
			// $this->ActivityLogModel->insert_activity($params);

			// $svr = $DB->Server;
			// $db = $DB->Database;
			// $url = $DB->AlamatWebService;

			// $str = "";
			// $bUrl = $url.API_BKT."/MasterDealer/GetListTokoTerkunciMO?api=APITES&svr=".urlencode($svr)."&db=".urlencode($db);
			// // die($bUrl);
			// $HTTPRequest = HttpGetRequest($bUrl, $url.API_BKT, "AMBIL LIST TOKO TERKUNCI MO");
			// // die($HTTPRequest);

	        // $result = json_decode($HTTPRequest, true);
			// if($result["result"]=="sukses"){
			// 	$dealers = $result["data"];

			// 	/*$Conns = $this->MasterDbModel->getList();
			// 	foreach ($Conns as $cn) {
			// 		$resultMOCBG = json_decode(file_get_contents($cn->AlamatWebService.API_BKT."/MasterDealer/GetListTokoTerkunciMO?api=APITES"), true);

			// 	}*/				
			// 	for ($i=0;$i<count($dealers);$i++) {
			// 		if (isset($dealers[$i])) {
			// 			$req = $this->MsDealerModel->GetRequestUnlockByToko($dealers[$i]["KD_PLG"]);
			// 			if ($req==null) {
			// 				$str.= "Tidak ada request untuk toko ".$dealers[$i]["NM_PLG"]." ".$dealers[$i]["KD_PLG"]."<br/>";
			// 				$dealers[$i]["REQUEST"] = null;
			// 			} else {
			// 				$str.= "Ada request untuk toko ".$dealers[$i]["NM_PLG"]." ".$dealers[$i]["KD_PLG"]."<br/>";
			// 				$dealers[$i]["REQUEST"] = $req;
			// 			}
			// 		} else {
			// 			$dealers[$i]["REQUEST"] = null;
			// 		}
			// 	}
			// 	//die($str);
			// 	$data["dealers"] = $dealers;
			// 	$data["alert"] = "";

			// 	$params['Remarks']="SUCCESS";
			// 	$params['RemarksDate'] = date("Y-m-d H:i:s");
			// 	$this->ActivityLogModel->update_activity($params);
			// } else {
			// 	$data["dealers"] = array();
			// 	$data["alert"] = $result["error"];

			// 	$params['Remarks']="FAILED - Data Tidak Ditemukan";
			// 	$params['RemarksDate'] = date("Y-m-d H:i:s");
			// 	$this->ActivityLogModel->update_activity($params);
			// }
			$data["alert"] = "";
			if ($flag==1) {
				$data["alert"] = "Request Buka Lock Toko Telah Diemail";
			}
			$data["menu"] = 'TokoTerkunciMO';
			$this->RenderView('MsDealerTerkunciView',$data);
		}else{
			$data["dealers"] = array();
			$data["alert"] = "Master DB Bhakti Jakarta Tidak Ditemukan!";
			$this->RenderView('MsDealerTerkunciView',$data);
		}
	}



	public function ListTokoTerkunciMO(){

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "TOKO TERKUNCI MO";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU TOKO TERKUNCI";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$svr = $_SESSION["conn"]->Server;
		$db = $_SESSION["conn"]->Database;
		$url = $_SESSION["conn"]->AlamatWebService;
		$url = $this->API_BKT."/MasterDealer/GetListTokoTerkunciMO?api=APITES&svr=".urlencode($svr)."&db=".urlencode($db)."&iDisplayStart=".$this->input->get('iDisplayStart')."&iSortCol_0=".$this->input->get('iSortCol_0')."&sSortDir_0=".$this->input->get('sSortDir_0')."&iDisplayLength=".$this->input->get('iDisplayLength')."&sSearch=".$this->input->get('sSearch');

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip');

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
		    echo 'Error: ' . curl_error($ch);
		}

		curl_close($ch);

		$result = json_decode($result, true);

	    $str = "";
	    $data_list=array();
	    $total =0;
		if($result["result"]=="sukses"){
			$dealers = $result["data"]['list'];
			$total = $result["data"]['total'];
			for ($i=0;$i<count($dealers);$i++) {
				if (isset($dealers[$i])) {
					$req = $this->MsDealerModel->GetRequestUnlockByToko($dealers[$i]["KD_PLG"]);
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

			
			$data_hasil=array();

			if($this->input->get('iDisplayStart')==0){
				$no=1;
			}else{
				$no=$this->input->get('iDisplayStart')+1;
			}
			
			if(!empty($dealers)){
				$MO = true;
				foreach (json_decode(json_encode($dealers)) as $key => $r) {
					$action='';
					$req ='';
					$list=array();

					$list[] 	= '<center>'.$no.'</center>';
					$list[] 	= $r->KD_PLG;
					$list[] 	= $r->NM_PLG;
					$list[] 	= $r->WILAYAH;
					$list[] 	= 'CL MISHIRIN : '.number_format($r->CL_MISHIRIN).'<br>CL CO&SANITARY : '.number_format($r->CL_COSANITARY);

			        if (($_SESSION["branchID"]=="JKT" && $MO==true) ||
			            ($_SESSION["branchID"]=="JKT" && substr($r->WILAYAH,0,2)=="MO" && trim($r->WILAYAH)!="MODERN OUTLET") ||
						($_SESSION["branchID"]!="JKT" && substr($r->WILAYAH,0,2)=="MO" && trim($r->WILAYAH)!="MODERN OUTLET") ||
						($_SESSION["branchID"]!="JKT" && $r->WILAYAH=="PROYEK")) {
			            if ($r->REQUEST==null) {
			            	$onclick="'".$r->KD_PLG."','".$r->NM_PLG."','".$r->WILAYAH."'";
							$req .= '  <span id="colReq'.$no.'"><div class="btn btnRequest" onclick="btnRequest('.$onclick.')">Buat<br>Request</div></span>';
			            } else {
							$req = "  <span id='colReq".$no."'>";
							  if ($r->REQUEST->IsApproved==0) {
							    $req .= "  <b>Request Unlock Ditemukan<br>[MENUNGGU APPROVAL]</b>";

							    $onclick="'".$r->REQUEST->RequestID."'";
							    $req .= '  <br><button class="btnResend"  onclick="btnResend('.$onclick.')">RESEND EMAIL</button>';

							    $onclick="btnCancelReq('".$r->REQUEST->RequestID."','".$no."')";
							    $req .= '  <button class="btnCancelReq" onclick="'.$onclick.'">CANCEL REQUEST</button>';
							  } else {
							    $req .= "  <b><font color='#015208'>Request Unlock Ditemukan [APPROVED]<br>Unlock S/D Tgl.".date("d-M-Y",strtotime($r->REQUEST->UnlockEnd))."</font></b>";
							  // $req .= "  <br><button class='btnResync' reqid='".$r->REQUEST->RequestID."'>RESYNC KE BHAKTI</button>";
			      
							  }
							$req .= "  </span>";
			            }
					}else{
						$req .= "  <span></span>";
					}

					$list[] 	= $req;

					$onclick="'".$r->KD_PLG."','".$r->NM_PLG."','".$r->WILAYAH."'";
					$list[] 	= '<button class="btn btnFakturGantung" kode="'.$r->KD_PLG.'" nama="'.$r->NM_PLG.'" wil="'.$r->WILAYAH.'" onclick="btnFakturGantung('.$onclick.')">Faktur<br>Gantung</button>';

					$data_list[]=$list;
					$no++;
				}
			}

			$data["dealers"] = $dealers;
			$data["alert"] = "";

			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
		} else {
			$data["dealers"] = array();
			$data["alert"] = $result["error"];
			$params['Remarks']="FAILED - Data Tidak Ditemukan";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
		}

		if(!empty($this->input->get('sEcho'))){
			$secho = $this->input->get('sEcho');
		}else{
			$secho = 1;
		}

		$data_hasil['sEcho']=$secho;
		$data_hasil['iTotalRecords']=$total;
		$data_hasil['iTotalDisplayRecords']=$total;
		$data_hasil['aaData']=$data_list;

		print_r(json_encode($data_hasil));

	}

	public function TokoTerkunciProyek($flag=0){
		if(isset($_SESSION['conn'])){
			$data["alert"] = "";
			// $params = array();
			// $params['LogDate'] = date("Y-m-d H:i:s");
			// $params['UserID'] = $_SESSION["logged_in"]["userid"];
			// $params['UserName'] = $_SESSION["logged_in"]["username"];
			// $params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			// $params['Module'] = "TOKO TERKUNCI PROYEK";
			// $params['TrxID'] = date("YmdHis");
			// $params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU TOKO TERKUNCI PROYEK";
			// $params['Remarks'] = "";
			// $params['RemarksDate'] = 'NULL';
			// $this->ActivityLogModel->insert_activity($params);

			// $svr = $_SESSION["conn"]->Server;
			// $db = $_SESSION["conn"]->Database;
			// $url = $_SESSION["conn"]->AlamatWebService;
			
			// $str = "";
			// $bUrl = $url.$this->API_BKT."/MasterDealer/GetListTokoTerkunciProyek?api=APITES&svr=".urlencode($svr)."&db=".urlencode($db);
			// //die($bUrl);
	        // $result = json_decode(file_get_contents($bUrl), true);
			// if($result["result"]=="sukses"){
			// 	$dealers = $result["data"];

				
			// 	for ($i=0;$i<count($dealers);$i++) {
			// 		if (isset($dealers[$i])) {
			// 			$req = $this->MsDealerModel->GetRequestUnlockByToko($dealers[$i]["KD_PLG"]);
			// 			if ($req==null) {
			// 				$str.= "Tidak ada request untuk toko ".$dealers[$i]["NM_PLG"]." ".$dealers[$i]["KD_PLG"]."<br/>";
			// 				$dealers[$i]["REQUEST"] = null;
			// 			} else {
			// 				$str.= "Ada request untuk toko ".$dealers[$i]["NM_PLG"]." ".$dealers[$i]["KD_PLG"]."<br/>";
			// 				$dealers[$i]["REQUEST"] = $req;
			// 			}
			// 		} else {
			// 			$dealers[$i]["REQUEST"] = null;
			// 		}
			// 	}
			// 	//die($str);
			// 	$data["dealers"] = $dealers;
			// 	$data["alert"] = "";

			// 	$params['Remarks']="SUCCESS";
			// 	$params['RemarksDate'] = date("Y-m-d H:i:s");
			// 	$this->ActivityLogModel->update_activity($params);
			// } else {
			// 	$data["dealers"] = array();
			// 	$data["alert"] = $result["error"];

			// 	$params['Remarks']="FAILED - Data Tidak Ditemukan";
			// 	$params['RemarksDate'] = date("Y-m-d H:i:s");
			// 	$this->ActivityLogModel->update_activity($params);
			// }
			if ($flag==1) {
				$data["alert"] = "Request Buka Lock Toko Telah Diemail";
			}
			$data["MO"] = true;
			$data["menu"] = 'TokoTerkunciProyek';
			$this->RenderView('MsDealerTerkunciView',$data);
		}
		else{
			redirect('Home');
		}
	}

	public function ListTokoTerkunciProyek(){

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "TOKO TERKUNCI MO";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU TOKO TERKUNCI";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$svr = $_SESSION["conn"]->Server;
		$db = $_SESSION["conn"]->Database;
		$url = $_SESSION["conn"]->AlamatWebService;
		$url = $this->API_BKT."/MasterDealer/GetListTokoTerkunciProyek?api=APITES&svr=".urlencode($svr)."&db=".urlencode($db)."&iDisplayStart=".$this->input->get('iDisplayStart')."&iSortCol_0=".$this->input->get('iSortCol_0')."&sSortDir_0=".$this->input->get('sSortDir_0')."&iDisplayLength=".$this->input->get('iDisplayLength')."&sSearch=".$this->input->get('sSearch');


		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip');

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
		    echo 'Error: ' . curl_error($ch);
		}

		curl_close($ch);

		$result = json_decode($result, true);
		
	    $str = "";

	    $data_list=array();
	    $total =0;
		if($result["result"]=="sukses"){
			$dealers = $result["data"]['list'];
			$total = $result["data"]['total'];
			for ($i=0;$i<count($dealers);$i++) {
				if (isset($dealers[$i])) {
					$req = $this->MsDealerModel->GetRequestUnlockByToko($dealers[$i]["KD_PLG"]);
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

			
			$data_hasil=array();

			if($this->input->get('iDisplayStart')==0){
				$no=1;
			}else{
				$no=$this->input->get('iDisplayStart')+1;
			}
			
			if(!empty($dealers)){
				$MO = true;
				foreach (json_decode(json_encode($dealers)) as $key => $r) {
					$action='';
					$req ='';
					$list=array();

					$list[] 	= '<center>'.$no.'</center>';
					$list[] 	= $r->KD_PLG;
					$list[] 	= $r->NM_PLG;
					$list[] 	= $r->WILAYAH;
					$list[] 	= 'CL MISHIRIN : '.number_format($r->CL_MISHIRIN).'<br>CL CO&SANITARY : '.number_format($r->CL_COSANITARY);

			        if (($_SESSION["branchID"]=="JKT" && $MO==true) ||
			            ($_SESSION["branchID"]=="JKT" && substr($r->WILAYAH,0,2)=="MO" && trim($r->WILAYAH)!="MODERN OUTLET") ||
						($_SESSION["branchID"]!="JKT" && substr($r->WILAYAH,0,2)=="MO" && trim($r->WILAYAH)!="MODERN OUTLET") ||
						($_SESSION["branchID"]!="JKT" && $r->WILAYAH=="PROYEK")) {
			            if ($r->REQUEST==null) {
			            	$onclick="'".$r->KD_PLG."','".$r->NM_PLG."','".$r->WILAYAH."'";
							$req .= '  <span id="colReq'.$no.'"><div class="btn btnRequest" onclick="btnRequest('.$onclick.')">Buat<br>Request</div></span>';
			            } else {
							$req = "  <span id='colReq".$no."'>";
							  if ($r->REQUEST->IsApproved==0) {
							    $req .= "  <b>Request Unlock Ditemukan<br>[MENUNGGU APPROVAL]</b>";

							    $onclick="'".$r->REQUEST->RequestID."'";
							    $req .= '  <br><button class="btnResend"  onclick="btnResend('.$onclick.')">RESEND EMAIL</button>';

							    $onclick="btnCancelReq('".$r->REQUEST->RequestID."','".$no."')";
							    $req .= '  <button class="btnCancelReq" onclick="'.$onclick.'">CANCEL REQUEST</button>';
							  } else {
							    $req .= "  <b><font color='#015208'>Request Unlock Ditemukan [APPROVED]<br>Unlock S/D Tgl.".date("d-M-Y",strtotime($r->REQUEST->UnlockEnd))."</font></b>";
							  // $req .= "  <br><button class='btnResync' reqid='".$r->REQUEST->RequestID."'>RESYNC KE BHAKTI</button>";
			      
							  }
							$req .= "  </span>";
			            }
					}else{
						$req .= "  <span></span>";
					}

					$list[] 	= $req;

					$onclick="'".$r->KD_PLG."','".$r->NM_PLG."','".$r->WILAYAH."'";
					$list[] 	= '<button class="btn btnFakturGantung" kode="'.$r->KD_PLG.'" nama="'.$r->NM_PLG.'" wil="'.$r->WILAYAH.'" onclick="btnFakturGantung('.$onclick.')">Faktur<br>Gantung</button>';

					$data_list[]=$list;
					$no++;
				}
			}

			$data["dealers"] = $dealers;
			$data["alert"] = "";

			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
		} else {
			$data["dealers"] = array();
			$data["alert"] = $result["error"];
			$params['Remarks']="FAILED - Data Tidak Ditemukan";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
		}

		if(!empty($this->input->get('sEcho'))){
			$secho = $this->input->get('sEcho');
		}else{
			$secho = 1;
		}

		$data_hasil['sEcho']=$secho;
		$data_hasil['iTotalRecords']=$total;
		$data_hasil['iTotalDisplayRecords']=$total;
		$data_hasil['aaData']=$data_list;

		print_r(json_encode($data_hasil));

	}

	public function RequestBukaLockToko()
	{
		$post = $this->PopulatePost();
		//die(json_encode($post));
		//{"NMPLG":"PT. BANGUNAN JAYA PERKASA","KDPLG":"DMIB097","WILAYAH":"MODERN OUTLET","KET":"TEST AUTO APPROVED"}

		if (isset($post['KDPLG'])){

			$params = array();			
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "TOKO TERKUNCI ALL";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." REQUEST BUKA LOCK TOKO ".$post['KDPLG']." - ".$post['NMPLG'];
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$REQUEST = $this->MsDealerModel->GetRequestUnlockByToko($post["KDPLG"]);
			if ($REQUEST==null) {

				$isAutoApproved = false;
				$CheckAutoApproved = $this->MsConfigModel->GetConfigValue("REQUEST UNLOCK", "AUTO APPROVED", "ALL");
				if (count($CheckAutoApproved)>0) {
					$isAutoApproved = $CheckAutoApproved[0]->ConfigValue;
				}
				
				$CreateRequest = $this->MsDealerModel->CreateRequestUnlockToko($post, $isAutoApproved);
				if ($CreateRequest["result"]=="SUCCESSFUL") {
					$KodeRequest = $CreateRequest["requestID"];
					$post["REQUESTBY"] = $_SESSION["logged_in"]["username"];

					$params['Remarks']="SUCCESS";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

					$isSendRequest = true;
					if ($isAutoApproved) {
						// die("auto approved");
						$KodeRequestMD5 = md5($KodeRequest);

						// $Url = base_url("MsDealerApproval/autoApproveUnlock/".$KodeRequestMD5);
						// $autoApprove = json_decode($autoApprove, true);
						// echo("Auto Approved");
						$autoApprove = $this->autoApproveUnlock($KodeRequestMD5);
						// die($Url);
						// die(json_encode($autoApprove));
						if ($autoApprove["result"]=="sukses") {
							$isSendRequest = false;
						} else {
							$isSendRequest = true;
						}
					} else {
						// die("not auto approved");
					}

					if ($isSendRequest==true) {
				    	$wCariApproval = array(
				    		'h.AddInfo1Name' => 'PARTNER TYPE',
							'h.EventID' => 'REQUEST UNLOCK',
							'h.AddInfo1' => 'MODERN OUTLET',
				    	);
						$getCariApproval = $this->MsDealerModel->CariApproval($wCariApproval);

						$SendRequest = $this->MsDealerModel->EmailRequestUnlockToko($KodeRequest, $getCariApproval, $isAutoApproved);
						$notifikasiWA = $this->notifikasiWA($KodeRequest, $getCariApproval);
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
				        $data["content_html"].= 'alert("Request Telah Dibuat dan DiAutoApproved")';
				        $data["content_html"].= '</script>';
				        $data["content_html"].= "<script>window.close();</script>";
				        $this->SetTemplate('template/notemplate');
					    $this->RenderView("MsDealerRequestUnlockView", $data);
					}
				} else {

					$params['Remarks']="FAILED - Data Tidak Ditemukan";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

					$data["content_html"] = '<script language="javascript">';
			        $data["content_html"].= 'alert("Request Unlock Ditemukan\nKode Request: '.$CheckRequests->RequestID.'")';
			        $data["content_html"].= '</script>';
			        $data["content_html"].= "<script>window.close();</script>";
			        echo($data["content_html"]);
			        redirect("Dashboard");
				}
			} else {
				$data["REQUEST"] = $REQUEST;
				// die("hi");
				$data["content_html"] = '<script language="javascript">';
		        $data["content_html"].= 'alert("Request Unlock Ditemukan\nKode Request: '.$REQUEST->RequestID.'")';
		        $data["content_html"].= '</script>';
		        $data["content_html"].= "<script>window.close();</script>";
		        echo($data["content_html"]);
		        redirect("MsDealer/TokoTerkunciMO");
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
			$this->MsDealerModel->ApproveUnlock($RequestNo, $UserEmail, $EndDate, $Note, 1, 1);

	        $hasil = array("result"=>"sukses", "error"=>"");
		} else {
	        $hasil = array("result"=>"sukses", "error"=>"request sudah diapprove");
		}
		return $hasil;
	}

	public function notifikasiWA($KodeRequest, $approvers){

		$RQ = $this->MsDealerModel->GetRequestUnlockToko($KodeRequest);
		$src = "SALES";
		
		$response = "";
		for($i=0; $i<count($approvers);$i++){
			$wGetManagers = array(
				'level_slsman' => $approvers[$i]["ApprovalByPosition"],
				'division' => $approvers[$i]["ApprovalByDivision"]
			);
			$GM = $this->SalesManagerModel->GetManagers($wGetManagers['level_slsman'], $wGetManagers['division']);

			$NO_HP = $this->HelperModel->MobileWithCountryCode($GM[0]->mobile);
			if ($this->test_mode==true) {
				$NO_HP = $this->test_whatsapp;
			}
	    	$URL = site_url("MsDealerApproval/ProsesRequestUnlock?kdreq=".urlencode($KodeRequest)."&empid=".urlencode($GM[0]->useremail));
	    	// die($URL);

			$response = "";
			$data = array();
			$data["phone"] = $NO_HP;
			$data["paramType1"] = "text";
			$data["param1"] = "*".$RQ->ReqName."* mengirimkan permintaan unlock dealer terkunci";
			$data["paramType2"] = "text";
			$data["param2"] = "Nama Dealer : *".$RQ->NmPlg."*";
			$data["paramType3"] = "text";
			$data["param3"] = "Kode Dealer : *".$RQ->KdPlg."*";
			$data["paramType4"] = "text";
			$data["param4"] = "Wilayah : *".$RQ->Wilayah."*";
			$data["paramType5"] = "text";
			$data["param5"] = "Alasan : *".$RQ->RequestNote."*";
			$data["paramType6"] = "text";
			$data["param6"] = $URL;
			$data = json_encode($data);
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => site_url()."/waba/template6Params?src=".$src,
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

			// if (property_exists($result, 'sent') && $result->sent === true) {
				// $response = "SUCCESSFUL";
			// }  
			if (ISSET($result->sent) && $result->sent === true) {
				$response = "SUCCESSFUL";
			}  
		}

		return $response;
	}

    public function ResendRequestUnlock($re=0)
    {
    	$data = array();
    	$post = $this->PopulatePost();
    	$KodeRequest = $post['KodeRequest'];

		if(isset($KodeRequest)){

			$params = array();			
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "TOKO TERKUNCI ALL";
			$params['TrxID'] = $post['KodeRequest'];
			$params['Description'] = $_SESSION["logged_in"]["username"]." RESEND EMAIL REQUEST BUKA LOCK TOKO NO ".$post['KodeRequest'];
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			// $this->ActivityLogModel->insert_activity($params);

			// die($post["KodeRequest"]);
			$isAutoApproved = false;
			$CheckAutoApproved = $this->MsConfigModel->GetConfigValue("REQUEST UNLOCK", "AUTO APPROVED", "ALL");
			if (count($CheckAutoApproved)>0) {
				$isAutoApproved = $CheckAutoApproved[0]->ConfigValue;
			}

			$sendRequest = true;
			if ($isAutoApproved) {
				// die("Auto Approved");
				$KodeRequestMD5 = md5($post["KodeRequest"]);
				$autoApprove = $this->autoApproveUnlock($KodeRequestMD5);
				// die(json_encode($autoApprove));
				if ($autoApprove["result"]=="sukses") {
					// echo(json_encode(array("result"=>"sukses","err"=>"")));
					// $sendRequest = false;
			        echo(json_encode(array("result"=>"sukses","err"=>"Request Telah Dibuat dan Di-Auto Approved")));
				} else {
					echo(json_encode(array("result"=>"gagal", "err"=>$autoApprove["error"])));
				}
			} else {
		    	$wCariApproval = array(
		    		'h.AddInfo1Name' => 'PARTNER TYPE',
					'h.EventID' => 'REQUEST UNLOCK',
					'h.AddInfo1' => 'MODERN OUTLET',
		    	);
				$getCariApproval = $this->MsDealerModel->CariApproval($wCariApproval);
				// die($getCariApproval);

				// die("Not Auto Approved");
		    	$Reemail = $this->MsDealerModel->EmailRequestUnlockToko($KodeRequest, $getCariApproval, 0, $re);


	    		// echo(json_encode(array("result"=>"gagal","err"=>"notifikasi WA")));
		    	$NotifikasiWA = $this->notifikasiWA($post["KodeRequest"], $getCariApproval);
		    	// die($NotifikasiWA);

		    	if ($Reemail=="SUCCESSFUL") {

		    		// $params['Remarks']="SUCCESS";
					// $params['RemarksDate'] = date("Y-m-d H:i:s");
					// $this->ActivityLogModel->update_activity($params);

		    		echo(json_encode(array("result"=>"sukses","err"=>"")));
		    	} else {

					// $params['Remarks']="FAILED - Gagal Kirim Email";
					// $params['RemarksDate'] = date("Y-m-d H:i:s");
					// $this->ActivityLogModel->update_activity($params);

		    		echo(json_encode(array("result"=>"gagal", "err"=>$Reemail)));
		    	}				
			}
		}
    }

	public function ResendRequestUnlockTest()
    {
    	$data = array();
    	$post = $this->PopulatePost();
    	$KodeRequest = "UL/DMIH079/2302/01";

		if(isset($KodeRequest)){
			// die($post["KodeRequest"]);
			$isAutoApproved = false;
			$CheckAutoApproved = $this->MsConfigModel->GetConfigValue("REQUEST UNLOCK", "AUTO APPROVED", "ALL");
			if (count($CheckAutoApproved)>0) {
				$isAutoApproved = $CheckAutoApproved[0]->ConfigValue;
			}

			$sendRequest = true;
			if ($isAutoApproved) {
				// die("Auto Approved");
				$KodeRequestMD5 = md5($post["KodeRequest"]);
				$autoApprove = $this->autoApproveUnlock($KodeRequestMD5);
				// die(json_encode($autoApprove));
				if ($autoApprove["result"]=="sukses") {
					// echo(json_encode(array("result"=>"sukses","err"=>"")));
					// $sendRequest = false;
			        echo(json_encode(array("result"=>"sukses","err"=>"Request Telah Dibuat dan Di-Auto Approved")));
				} else {
					echo(json_encode(array("result"=>"gagal", "err"=>$autoApprove["error"])));
				}
			} else {

		    	$wCariApproval = array(
		    		'h.AddInfo1Name' => 'PARTNER TYPE',
					'h.EventID' => 'REQUEST UNLOCK',
					'h.AddInfo1' => 'MODERN OUTLET',
		    	);
				$getCariApproval = $this->MsDealerModel->CariApproval($wCariApproval);
				// die(json_encode($getCariApproval));

				// die("Not Auto Approved");
		    	$Reemail = $this->MsDealerModel->EmailRequestUnlockToko($KodeRequest, $getCariApproval, 0);
	    		// echo(json_encode(array("result"=>"gagal","err"=>"notifikasi WA")));
		    	$Reemail = $this->notifikasiWA($post["KodeRequest"], $getCariApproval);
		    	// die($Reemail);

		    	if ($Reemail=="SUCCESSFUL") {
		    		echo(json_encode(array("result"=>"sukses","err"=>"")));
		    	} else {
		    		echo(json_encode(array("result"=>"gagal", "err"=>$Reemail)));
		    	}				
			}
		}
    }

    public function TestRequest()
    {
    	// 		public function SendEmail($to, $cc, $subject, $body, $branch="MC")
    	$this->accountModel->SendEmail("indah@bhakti.co.id", "indahwati.saliman@gmail.com", "test email", "<b>Hello</b>");
    	// $this->accountModel->SendEmail(array(), array(), "","");
    }

    public function CancelRequestUnlock()
    {
    	$data = array();
    	$post = $this->PopulatePost();
		if(isset($post['KodeRequest']) && isset($post["KetCancel"])){

			$params = array();			
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "TOKO TERKUNCI ALL";
			$params['TrxID'] = $post['KodeRequest'];
			$params['Description'] = $_SESSION["logged_in"]["username"]." CANCEL REQUEST BUKA LOCK TOKO NO ".$post['KodeRequest'];
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);

	    	$REQ = $this->MsDealerModel->GetRequestUnlockToko($post["KodeRequest"]);
	    	if ($REQ->IsApproved==1) {

				$params['Remarks']="FAILED - Request Sudah Diapprove";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

	    		echo(json_encode(array("result"=>"gagal", "request"=>$REQ, "err"=>"Request Sudah Diapprove")));
	    	} else if ($REQ->IsApproved==2) {

	   			$params['Remarks']="FAILED - Request Sudah Direject";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

	    		echo(json_encode(array("result"=>"gagal", "request"=>$REQ, "err"=>"Request Sudah Direject")));
	    	} else if ($REQ->IsCancelled==1) {

				$params['Remarks']="FAILED - Request Sudah Dibatalkan Sebelumnya";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

	    		echo(json_encode(array("result"=>"gagal", "request"=>$REQ, "err"=>"Request Sudah Dibatalkan Sebelumnya")));
	    	} else {
		    	$CancelReq = $this->MsDealerModel->CancelRequestUnlockToko($post["KodeRequest"], $post["KetCancel"]);
		    	if ($CancelReq==true) {

					$params['Remarks']="SUCCESS";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

		    		$this->MsDealerModel->EmailCancelUnlockToko($post["KodeRequest"]);
		    		// $this->NotifikasiCancelUnlockToko($post["KodeRequest"]);
		    		echo(json_encode(array("result"=>"sukses","request"=>$REQ, "err"=>"")));
		    	} else {

					$params['Remarks']="FAILED - Cancel Request Gagal";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

		    		echo(json_encode(array("result"=>"gagal", "request"=>$REQ, "err"=>"Cancel Request Gagal")));
		    	}
		    }
		} else {
    		echo(json_encode(array("result"=>"gagal", "request"=>null, "err"=>"Data Tidak Lengkap")));
		}
    }

    public function NotifikasiCancelUnlockToko($KodeRequest) 
    {
    	$rq = $this->MsDealerModel->GetRequestUnlockToko($KodeRequest);
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

		$url = $this->API_MSG.'/waba/sendMessageWA?src='.$this->whatsapp_account;

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
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
		die(json_encode($result));
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
	        $rq = $this->MsDealerModel->GetRequestUnlockByToko($post["KdPlg"]);
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

				$params = array();			
				$params['LogDate'] = date("Y-m-d H:i:s");
				$params['UserID'] = $_SESSION["logged_in"]["userid"];
				$params['UserName'] = $_SESSION["logged_in"]["username"];
				$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
				$params['Module'] = "TOKO TERKUNCI";
				$params['TrxID'] = date("YmdHis");
				$params['Description'] = $_SESSION["logged_in"]["username"]." KLIK BUTTON FAKTUR GANTUNG";
				$params['Remarks'] = "";
				$params['RemarksDate'] = 'NULL';
				$this->ActivityLogModel->insert_activity($params);

				$svr = $_SESSION["conn"]->Server;
				$db = $_SESSION["conn"]->Database;
				$url = $_SESSION["conn"]->AlamatWebService;
				$str = "";

				$url = $this->API_BKT."/MasterDealer/GetFakturGantung?api=APITES&plg=".urlencode($post["KdPlg"]);
		        $result = file_get_contents($url);
		        $result = $this->GzipDecodeModel->_decodeGzip_true($result);

				if($result["result"]=="sukses"){

					$params['Remarks']="SUCCESS";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

					$faktur = $result["data"];
					echo json_encode(array("result"=>"sukses", "faktur"=>$faktur, "ket"=>""));
				} else if ($result["error"]=="Tidak Ada Faktur Gantung") {		
			        $unlock = json_decode(file_get_contents($this->API_BKT."/MasterDealer/UnlockToko2?api=APITES"."&plg=".urlencode($post["KdPlg"]).
			        			"&svr=".urlencode($svr)."&db=".urlencode($db)."&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD)), true);
			        if ($unlock["result"]=="sukses") {
						echo json_encode(array("result"=>"sukses2", "faktur"=>array(), "ket"=>"Tidak Ada Faktur Gantung. Dealer sudah diaktifkan kembali."));
			        } else {
						echo json_encode(array("result"=>"gagal", "faktur"=>array(), "ket"=>"Tidak Ada Faktur Gantung Namun Dealer Tidak Berhasil Diaktifkan Kembali"));
			        }

					$params['Remarks']="FAILED - Data Tidak Ditemukan";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

				} else {

					$params['Remarks']="FAILED - Data Tidak Ditemukan";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

					echo json_encode(array("result"=>"gagal", "faktur"=>array(), "ket"=>$result["error"]));
				}
			} else {
				echo json_encode(array("result"=>"gagal", "faktur"=>array(), "ket"=>"Pilih Database Dulu"));
			}
		} else {
			echo json_encode(array("result"=>"gagal", "faktur"=>array(), "ket"=>'Kode Pelanggan Belum Diisi'));
		}    	
    }

    public function GetConfigColor()
    { 
		$telat = $this->input->get('telat');
    	$data = $this->MsConfigModel->GetConfigColor($telat); 
		echo json_encode($data);
    }

	public function CreditLimit()
	{
		$alert = urldecode($this->input->get("alert"));

		if(isset($_SESSION['conn'])) {

			$params = array();			
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "CREDIT LIMIT";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU CREDIT LIMIT";
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);

			//$db_id = $_SESSION['databaseID'];
			//die(json_encode($_SESSION["conn"]->NamaDb));
			
			if ($this->test_mode==true) {
				$API = $this->API_BKT_HO;
			} else {
				$API = $this->API_BKT;
			}

			$URL  = $API."/MasterDealer/GetListDealerForCL?api=APITES";
			// die($URL);
			//$HTTPRequest = HttpGetRequest($URL, $API, "AMBIL LIST DEALER");
	    //    $GetDealer = json_decode($HTTPRequest,true);

			$HTTPRequest = HttpGetRequest($URL, $API, "AMBIL LIST DEALER");
	        // $GetDealer = json_decode($HTTPRequest,true);
			$GetDealer = $this->GzipDecodeModel->_decodeGzip_true($HTTPRequest);

	        // die(json_encode($GetDealer));
	        $data=array();
	        $data["alert"] = "";
	        
	        if ($GetDealer["result"]=="sukses"){
	        	$ListDealer = $GetDealer["data"];
				$dealers = array();
				for($i=0; $i<count($ListDealer); $i++){
					array_push($dealers, trim($ListDealer[$i]["NM_PLG"])." - ".trim($ListDealer[$i]["KD_PLG"])." - ".trim($ListDealer[$i]["WILAYAH"])." - ".trim($ListDealer[$i]["PARTNER_TYPE"]));
				}
				// die(json_encode($ListDealer));
				$data["ListDealer"] = $ListDealer;
		        $data["Dealers"] = $dealers;
				$data["kenaikanCL_RED"] = $this->MsConfigModel->GetConfigMaxIncrement();

		        if($alert!="") {
					$data["content_html"] = '<script language="javascript">';
			        $data["content_html"].= 'alert("'.urldecode($alert).'")';
			        $data["content_html"].= '</script>';		        	
		        }

				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

		    	$this->RenderView('MsDealerCreditLimitView', $data);

		    } else {
				$data["content_html"] = '<script language="javascript">';
		        $data["content_html"].= 'alert("GAGAL MENGAMBIL LIST DEALER KE DB BHAKTI")';
		        if($alert!="") {
		        $data["content_html"].= 'alert("'.urldecode($alert).'")';
		        }
		        $data["content_html"].= '</script>';
				
				$data["ListDealer"] = array();
		        $data["Dealers"] = array();
				$data["kenaikanCL_RED"] = $this->MsConfigModel->GetConfigMaxIncrement();

				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
					
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
    	//Function ini dipanggil oleh View setelah Pilih Dealer
		$post = $this->PopulatePost(); 
		if(isset($post['KdPlg']) && isset($post["Divisi"])){
			set_time_limit(60);
	        $domainUrl = $_SESSION['conn']->AlamatWebService;
	        if ($post["PartnerType"]=="MODERN OUTLET") {
	        	$dbHQ = $this->MasterDbModel->getBhaktiPusat();
	        	$domainUrl = $dbHQ->AlamatWebService;
	        }
			if ($this->test_mode==true) $domainUrl = HO;

	        $divisi = "";
	        if ($post["Divisi"]!="SPAREPART") {
	        	$divisi = "MISHIRIN";
	        } else {
	        	$divisi = $post["Divisi"];
	        }

			$domainUrl = $this->API_BKT."/";
			$result = $this->GetCreditLimitDealer($domainUrl, $post["KdPlg"], $divisi, $post["NmPlg"]);
			// die(json_encode($result));
			echo json_encode($result);

		} else {
			echo json_encode(array("result"=>"gagal", "data"=>array(), "ket"=>'Kode Pelanggan/Divisi Belum Diisi'));
		}    	
    }

    public function GetCreditLimitDealer($domainUrl, $kodePelanggan, $divisi, $namaPelanggan="")
    {
    	// $domainUrl = "http://bhaktibali.dnsalias.com:90/bktAPI/";
    	// $kodePelanggan="BLIW024";
    	// $namaPelanggan="WAYAN DJATI KUSUMA";
    	// $divisi = "MISHIRIN";

    	$url = $domainUrl."MasterDealer/CheckLimitDealer?api=APITES&kdplg=".urlencode($kodePelanggan).
    					"&divisi=".urlencode(htmlspecialchars_decode($divisi));
    	// die($url);
    	$HTTPRequest = HttpGetRequest_Ajax2($url, $domainUrl, "Check Credit Limit ".$namaPelanggan, 60000);
    	if ($HTTPRequest["connected"]==true) {
    		// die($HTTPRequest["result"]);
	        // $result = json_decode($HTTPRequest["result"], true);
	        $result = $this->GzipDecodeModel->_decodeGzip_true($HTTPRequest["result"]);
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


			$url = $this->API_BKT."/MasterDealer/CheckLimitDealer?api=APITES&kdplg=".urlencode($post["KdPlg"]).
    					"&divisi=".urlencode(htmlspecialchars_decode($divisi));
    		//die($url);
	        // $get = json_decode(file_get_contents($url),true);
	        $get = file_get_contents($url);

    		$result = $this->GzipDecodeModel->_decodeGzip_true($get);
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

    public function GetCreditLimitGroupDebug()
    {
    	//die("hello");
		$post = array();
		$post["Divisi"] = "MISHIRIN";
		$post["KdPlg"] = "MDNS019";
		$post["WilPlg"] = "MEDAN";
		$post["NmPlg"] = "PT. SENTRAL REJEKI BERSAMA";
		
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
        
        // $domainUrl = "http://localhost:90/";
        // $domainUrl.= $this->API_BKT."/"; 


        $url = $this->API_BKT."/MasterDealer/GetMarkers?api=APITES&kdplg=".urlencode($KdPlg);
        $HTTPRequest = HttpGetRequest_Ajax2($url, $domainUrl, "Ambil Data Marker", 60000);


        // die(json_encode($HTTPRequest));
        if ($HTTPRequest["connected"]==false) {
			return array("result"=>"gagal", "data"=>array(), "ket"=>$HTTPRequest["err"]);
        } else {
			// $GetMarkers = json_decode($HTTPRequest["result"], true);
			$GetMarkers = $this->GzipDecodeModel->_decodeGzip_true($HTTPRequest["result"]);

			// die(json_encode($GetMarkers));
			if ($GetMarkers["result"]=="sukses") {
				$dataMarker = $GetMarkers["data"];

				for ($i=0;$i<count($dataMarker);$i++) {
					if ($dataMarker[$i]["KD_LOKASI"]=="RUK") $dataMarker[$i]["KD_LOKASI"] = "DMI";
					$GetUrl = $this->MasterDbModel->getByLocationCode($dataMarker[$i]["KD_LOKASI"]);
					// die(json_encode($GetUrl));
					if ($GetUrl!=null) {
						set_time_limit(60);
						$domainUrl = $GetUrl->AlamatWebService;
						if ($this->test_mode==true) $domainUrl = HO;
						$domainUrl = $this->API_BKT."/";
						$result = $this->GetCreditLimitDealer($domainUrl, $dataMarker[$i]["KD_PLG"], $Divisi, $dataMarker[$i]["NM_PLG"]);
						// die(json_encode($result));
						if ($result["result"]=="gagal") {
							return $result;
						} else {
							array_push($dealers, $result["data"]);
						}
				    } else {
				    	$GetUrl = $this->MasterDbModel->getByBranchId($dataMarker[$i]["KD_LOKASI"]);
						// die(json_encode($GetUrl));
						if ($GetUrl!=null) {
							set_time_limit(60);
							$domainUrl = $GetUrl->AlamatWebService;
							if ($this->test_mode==true) $domainUrl = HO;
							$domainUrl = $this->API_BKT."/";
							$result = $this->GetCreditLimitDealer($domainUrl, $dataMarker[$i]["KD_PLG"], $Divisi, $dataMarker[$i]["NM_PLG"]);
							// die(json_encode($result));
							if ($result["result"]=="gagal") {
								return $result;
							} else {
								array_push($dealers, $result["data"]);
							}
					    } else {
			    			return array("result"=>"gagal", "data"=>array(), "ket"=>"Tidak Berhasil Mengambil Detail Koneksi Untuk Lokasi ".$dataMarker[$i]["KD_LOKASI"]);
					    }
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

		for($i=0;$i<count($dealers);$i++) {
			if ($dealers[$i]["MAKS_TELAT"]=="") $dealers[$i]["MAKS_TELAT"]=0;
			$configs=$this->MsConfigModel->GetConfigColor($dealers[$i]["MAKS_TELAT"]);
			$config = $configs[0];
			$dealers[$i]["STYLE"] = "background-color:".$config->ConfigValue.";";
			$dealers[$i]["FLAG"] = $config->ConfigType;
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
		$params["PartnerType"] = $this->input->post("txtPartnerTypePlg");

		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "CREDIT LIMIT";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." REQUEST CREDIT LIMIT UNTUK ".$params["KdPlg"]." - ".$params["NmPlg"];
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$GetCLGroup = $this->ProsesCreditLimitGroup($params["KdPlg"], $params["Wilayah"], $params["Divisi"],$params["NmPlg"] );

		if ($GetCLGroup["result"]=="sukses") {
			
			$CLGroup = $GetCLGroup["data"];
			//die("CLGroup : <br>".json_encode($CLGroup));
			$CheckExistingRequest = $this->MsDealerModel->CheckExistingRequest("CREDIT LIMIT", $params);
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


				//$BrandManagers = $this->SalesManagerModel->GetBrandManagers();
				//$SalesManagers = $this->SalesManagerModel->GetSalesManager();
				//$GM = $this->SalesManagerModel->GetGeneralManager();
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

				//Check BASSnya ke webAPI aja
				$url = $this->API_URL."/MasterDealer/GetDealer?api=APITES&plg=".urlencode($params["KdPlg"]);
				// $GetDealer = json_decode(file_get_contents($url),true);
				// $GetDealer = file_get_contents($url);

				$ch = curl_init($url);

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

				curl_setopt($ch, CURLOPT_ENCODING, 'gzip');

				$response = curl_exec($ch);

				if (curl_errno($ch)) {
				    echo 'Curl error: ' . curl_error($ch);
				}

				curl_close($ch);


				$GetDealer = $this->GzipDecodeModel->_decodeGzip_true($response);

				if ($GetDealer["result"]=="sukses") {
					$params["IsBass"] = $GetDealer["data"]["IS_BASS"];
				}


				if ($params["IsBass"]==1 && $params["Divisi"]=="SPAREPART") 
				{
					 $params["PartnerType"] = "BASS";
				}  
				// die(json_encode($params));

				$MsConfigreqApproval = $this->MsConfigRequestApprovalModel->GetConfigApprovalPosition($params["KenaikanCL"],$params["PartnerType"],$params["Divisi"],$params["Marking"],$_SESSION["conn"]->BranchId); 
 				$listlvlsalesman = "";
 				$listkacab = "";
				$i = 0;

				if(count($MsConfigreqApproval)==0){
					redirect("MsDealer/CreditLimit?alert=".urlencode('Config Kenaikan CL tidak ditemukan. Periksa kembali konfigurasi approval kenaikan CL!'));
				}

				$this->approvers = array();
				
				// die(json_encode($MsConfigreqApproval));
				foreach($MsConfigreqApproval as $result) {
					$Priority = $MsConfigreqApproval[$i]->ApprovalLevel;
					$ApprovalNeeded = $MsConfigreqApproval[$i]->ApprovalNeeded;

	 				$listlvlsalesman = "";
	 				$listkacab = "";
					 
					if ($MsConfigreqApproval[$i]->ApprovalByPosition=="BRANCH HEAD")
					{ 
						$listkacab = "'".$MsConfigreqApproval[$i]->ApprovalByPosition."'";
					}
					else
					{ 
						if ($listlvlsalesman!="")
						{
							if ($i>0)
							{
								$listlvlsalesman .= ",";
							}
						}
						$listlvlsalesman .= "'".$MsConfigreqApproval[$i]->ApprovalByPosition."'"; 
					}

					$i++;
				
				
					//die($params["KenaikanCL"].$params["PartnerType"].$params["Divisi"].$params["Marking"].$user["BranchID"]);		
  					// die(json_encode($_SESSION["conn"]->BranchId));

					if ($listkacab!="")
					{
						// die("cari kacab ".$_SESSION["conn"]->BranchId);
						$listapvkacab = $this->SalesManagerModel->GetListKacab($_SESSION["conn"]->BranchId); 
						// $listapvkacab = $this->SalesManagerModel->GetListKacab($user["BranchID"]); 
						$this->addRecipients_apvkacab($listapvkacab, $Priority, $ApprovalNeeded); 
					}
					if ($listlvlsalesman!="")
					{
						// die("cari manager");
						$listapv = $this->SalesManagerModel->GetListApv($listlvlsalesman);
						$this->addRecipients_apv($listapv, $Priority, $ApprovalNeeded);  
					}
				}
				// die(json_encode($this->approvers));

				$REQUESTID = $this->MsDealerModel->RequestCL($params, $email_content, $this->approvers, $user);
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				$params["RequestID"] = $REQUESTID["requestid"];
				$params["ExpiryDate"] = $REQUESTID["expirydate"];
  
				$data = array();
				$data["alert"] = "";
				$data["ListDealer"] = array();
		        $data["Dealers"] = array();
			 	$data["kenaikanCL_RED"] = $this->MsConfigModel->GetConfigMaxIncrement();

				$db_id = $_SESSION['databaseID'];

				if ($this->test_mode==true) $API = HO;
				$URL = $this->API_BKT."/MasterDealer/GetListDealerForCL?api=APITES";
		        // $GetDealer = json_decode(file_get_contents($URL),true);
		        $ch = curl_init($URL);

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

				curl_setopt($ch, CURLOPT_ENCODING, 'gzip');

				$response = curl_exec($ch);

				if (curl_errno($ch)) {
				    echo 'Curl error: ' . curl_error($ch);
				}

				curl_close($ch);


				$GetDealer = $this->GzipDecodeModel->_decodeGzip_true($response);

		        if ($GetDealer["result"]=="sukses"){
		        	$ListDealer = $GetDealer["data"];
					$dealers = array();
					for($i=0; $i<count($ListDealer); $i++){
						array_push($dealers, trim($ListDealer[$i]["NM_PLG"])." - ".trim($ListDealer[$i]["KD_PLG"])." - ".trim($ListDealer[$i]["WILAYAH"])." - ".trim($ListDealer[$i]["PARTNER_TYPE"]));
					}
					$data["ListDealer"] = $ListDealer;
			        $data["Dealers"] = $dealers;

					if ($REQUESTID["result"]=="sukses") { 
				        $data["alert"] = "REQUEST CL SAVED & EMAILED TO KACAB/MANAGER"; 
						$sendEmail = $this->MsDealerModel->EmailRequest("CREDIT LIMIT", $params, $email_content, $this->recipients, $user);
						$sendWA = $this->MsDealerModel->ProsesWhatsapp("CREDIT LIMIT", $params, $this->whatsapps, $user, $this->whatsapp_account);
					} else { 
				        $data["alert"] = strtoupper($REQUESTID["ket"]);
					}
					$this->RenderView('MsDealerCreditLimitView', $data);
			    } else {
			        $data["alert"] = "REQUEST CL SAVED & EMAILED TO KACAB/MANAGER";
			        $data["alert"].= "\nGAGAL MENGAMBIL LIST DEALER KE DB BHAKTI";

			        $this->RenderView('MsDealerCreditLimitView', $data);
			    }

			}
		} else {
			redirect("MsDealer/CreditLimit?alert=".urlencode($GetCLGroup["ket"]));
		}
    }

    public function RequestCLDebug()
    {
    	$dbHQ = $this->MasterDbModel->getBhaktiPusat();

		// $post = $this->PopulatePost();
		$post["selDiv"] = "MO";
		$post["txtKodePlg"] = "DMIC016";
		$post["txtNamaPlg"] = "MITRA 10";
		$post["txtAlamatPlg"] = "JKT";
		$post["txtWilayahPlg"] = "MODERN OUTLET JAKARTA";
		$post["txtMarking"] = "GREEN";
		$post["txtCatatan"] = "-";
		$post["txtLiPerma"] = "1";
		$post["txtLiTemp"] = "1";
		$post["txtLiMaks"] = "100000000000";
		$post["txtRequest"] = "100000000";
		$post["txtPartnerTypePlg"] = "MODERN OUTLET";
		// die(json_encode($post));

		$params = array();
		$params["Divisi"] = $post["selDiv"];
		// if ($post["selDiv"]=="MO") {
		// 	$params["Divisi"] = "MISHIRIN";
		// }
		$params["KdPlg"] = $post["txtKodePlg"];
		$params["NmPlg"] = $post["txtNamaPlg"];
		$params["AlmPlg"] = $post["txtAlamatPlg"];
		$params["Wilayah"] = $post["txtWilayahPlg"];
		$params["Marking"] = $post["txtMarking"];
		$params["Catatan"] = $post["txtCatatan"];
		$params["CLPermanent"] = str_replace(",","",$post["txtLiPerma"]);
		$params["CLTemporary"] = str_replace(",","",$post["txtLiTemp"]);
		$params["CLMaks"] = str_replace(",","",$post["txtLiMaks"]);
		$params["CLNew"] = str_replace(",","",$post["txtRequest"]);
		$params["KenaikanCL"] = $params["CLNew"] - $params["CLPermanent"];
		$params["DatabaseID"] = (($params["Wilayah"]=="MODERN OUTLET")? $dbHQ->DatabaseId : $_SESSION["conn"]->DatabaseId);
		$params["BranchDB"] = $_SESSION["conn"]->BranchId;
		$params["NamaDB"] = $_SESSION["conn"]->NamaDb;
		$params["IsBass"] = false;
		$params["PartnerType"] = $post["txtPartnerTypePlg"];

		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "CREDIT LIMIT";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." REQUEST CREDIT LIMIT UNTUK ".$params["KdPlg"]." - ".$params["NmPlg"];
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);
		// die(json_encode($params));

		$GetCLGroup = $this->ProsesCreditLimitGroup($params["KdPlg"], $params["Wilayah"], $params["Divisi"],$params["NmPlg"] );
		// die(json_encode($GetCLGroup));

		if ($GetCLGroup["result"]=="sukses") {
			$CLGroup = $GetCLGroup["data"];
			//die("CLGroup : <br>".json_encode($CLGroup));
			$CheckExistingRequest = $this->MsDealerModel->CheckExistingRequest("CREDIT LIMIT", $params);
			//$CheckExistingRequest = 0;

			if ($CheckExistingRequest>0) {
				redirect("MsDealer/CreditLimit?alert=".urlencode("REQUEST CREDIT LIMIT SUDAH ADA"));

			} else if ($params["KdPlg"]=="" || $params["NmPlg"]=="" || $params["AlmPlg"]=="" || $params["CLPermanent"]=="" || $params["CLNew"]=="" 
				|| $params["CLNew"]=="0" || $params["CLNew"]==$params["CLPermanent"])  { 
				redirect("MsDealer/CreditLimit?alert=".urlencode("DATA TIDAK LENGKAP"));

			} else {
				// die("123");

				$this->approvers = array();
				$this->recipients= array();
				$this->whatsapps = array();


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
				// die($email_content);

				//Check BASSnya ke webAPI aja
				$url = $this->API_URL."/MasterDealer/GetDealer?api=APITES&plg=".urlencode($params["KdPlg"]);
				$GetDealer = json_decode(file_get_contents($url),true);
				if ($GetDealer["result"]=="sukses") {
					$params["IsBass"] = $GetDealer["data"]["IS_BASS"];
				}

				if ($params["IsBass"]==1 && $params["Divisi"]=="SPAREPART") 
				{
					 $params["PartnerType"] = "BASS";
				}  
				// die(json_encode($params));

				$MsConfigreqApproval = $this->MsConfigRequestApprovalModel->GetConfigApprovalPosition($params["KenaikanCL"],$params["PartnerType"],$params["Divisi"],$params["Marking"],$_SESSION["conn"]->BranchId); 
 				$listlvlsalesman = "";
 				$listkacab = "";
				$i = 0;
				
				$this->approvers = array();
				// die(json_encode($MsConfigreqApproval));

				foreach($MsConfigreqApproval as $result) {
					$Priority = $MsConfigreqApproval[$i]->ApprovalLevel;
					$ApprovalNeeded = $MsConfigreqApproval[$i]->ApprovalNeeded;

	 				$listlvlsalesman = "";
	 				$listkacab = "";
					 
					if ($MsConfigreqApproval[$i]->ApprovalByPosition=="BRANCH HEAD")
					{ 
						$listkacab = "'".$MsConfigreqApproval[$i]->ApprovalByPosition."'";
					}
					else
					{ 
						if ($listlvlsalesman!="")
						{
							if ($i>0)
							{
								$listlvlsalesman .= ",";
							}
						}
						$listlvlsalesman .= "'".$MsConfigreqApproval[$i]->ApprovalByPosition."'"; 
					}

					$i++;
				
				
					//die($params["KenaikanCL"].$params["PartnerType"].$params["Divisi"].$params["Marking"].$user["BranchID"]);		
  					// die(json_encode($_SESSION["conn"]->BranchId));

					if ($listkacab!="")
					{
						// die("cari kacab ".$_SESSION["conn"]->BranchId);
						$listapvkacab = $this->SalesManagerModel->GetListKacab($_SESSION["conn"]->BranchId); 
						// $listapvkacab = $this->SalesManagerModel->GetListKacab($user["BranchID"]); 
						$this->addRecipients_apvkacab($listapvkacab, $Priority, $ApprovalNeeded); 
					}
					if ($listlvlsalesman!="")
					{
						// die("cari manager");
						$listapv = $this->SalesManagerModel->GetListApv($listlvlsalesman);
						$this->addRecipients_apv($listapv, $Priority, $ApprovalNeeded);  
					}
				}
				die(json_encode($this->approvers));

				$REQUESTID = $this->MsDealerModel->RequestCL($params, $email_content, $this->approvers, $user);
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				$params["RequestID"] = $REQUESTID["requestid"];
				$params["ExpiryDate"] = $REQUESTID["expirydate"];
  
				$data = array();
				$data["alert"] = "";
				$data["ListDealer"] = array();
		        $data["Dealers"] = array();
			 	$data["kenaikanCL_RED"] = $this->MsConfigModel->GetConfigMaxIncrement();

				$db_id = $_SESSION['databaseID'];
				
				if ($this->test_mode==true) $API = HO;
				$URL = $this->API_BKT."/MasterDealer/GetListDealerForCL?api=APITES";
		        $GetDealer = json_decode(file_get_contents($URL),true);
		        if ($GetDealer["result"]=="sukses"){
		        	$ListDealer = $GetDealer["data"];
					$dealers = array();
					for($i=0; $i<count($ListDealer); $i++){
						array_push($dealers, trim($ListDealer[$i]["NM_PLG"])." - ".trim($ListDealer[$i]["KD_PLG"])." - ".trim($ListDealer[$i]["WILAYAH"])." - ".trim($ListDealer[$i]["PARTNER_TYPE"]));
					}
					$data["ListDealer"] = $ListDealer;
			        $data["Dealers"] = $dealers;

					if ($REQUESTID["result"]=="sukses") { 
				        $data["alert"] = "REQUEST CL SAVED & EMAILED TO KACAB/MANAGER"; 
						$sendEmail = $this->MsDealerModel->EmailRequest("CREDIT LIMIT", $params, $email_content, $this->recipients, $user);
						$sendWA = $this->MsDealerModel->ProsesWhatsapp("CREDIT LIMIT", $params, $this->whatsapps, $user, $this->whatsapp_account);
					} else { 
				        $data["alert"] = strtoupper($REQUESTID["ket"]);
					}
					$this->RenderView('MsDealerCreditLimitView', $data);
			    } else {
			        $data["alert"] = "REQUEST CL SAVED & EMAILED TO KACAB/MANAGER";
			        $data["alert"].= "\nGAGAL MENGAMBIL LIST DEALER KE DB BHAKTI";

			        $this->RenderView('MsDealerCreditLimitView', $data);
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

    public function ResendRequest($re=0)
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

    	$REQ = $this->MsDealerModel->GetRequest($RequestType, $RequestNo);

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
		$sendEmail = $this->MsDealerModel->EmailRequest($RequestType, $params, $email_content, $recipients, $user, 1, $re);
		$sendWA = $this->MsDealerModel->ProsesWhatsapp($RequestType, $params, $mobiles, $user);
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
		$params = html_escape($params);
		
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
 			
			if ($TELAT=="")
			{
				$data = $this->MsConfigModel->GetConfigColor(0);   
		    	$STYLE = "background-color:".$data[0]->ConfigValue.";";
			}
			else
			{
				$data = $this->MsConfigModel->GetConfigColor($TELAT);   
		    	$STYLE = "background-color:".$data[0]->ConfigValue.";";
			}


  
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
		$GetRequest = $this->MsDealerModel->GetRequestForProcess($RequestType, $RequestNo, "");
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
			$data["approver"]= $this->MsDealerModel->GetRequestApproverList($RequestType, $RequestNo);
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
		$data["Dealers"] = array();
		$data["kenaikanCL_RED"] = $this->MsConfigModel->GetConfigMaxIncrement();
		
		$GetRequest = $this->MsDealerModel->GetRequestForProcess($RequestType, $RequestNo, "");

		// echo(json_encode($GetRequest));
		// die("<br>");

		if ($GetRequest["result"]=="sukses") {
			// die($GetRequest["status"]);
			if ($GetRequest["status"]=="CANCELLED") {
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
				$CancelRequest = $this->MsDealerModel->CancelRequest($RequestType, $RequestNo);
				// echo(json_encode($CancelRequest)."<br><br>");

				if ($CancelRequest["result"]=="sukses") {
					//PANGGIL REQUEST SEKALI LAGI
					// echo("Panggil Request<br>");
					$GetRequest2 = $this->MsDealerModel->GetRequestForProcess($RequestType, $RequestNo, "");
					$REQ = $GetRequest2["data"];
					// echo("Cancelling Request<br>");
					// echo(json_encode($REQ)."<br><br>");
					
					$email_content = $this->CreateCancellationEmailContent($RequestType, $REQ);
					die($email_content."<br><br>");
					$doEmail = $this->MsDealerModel->EmailCancellationRequest($RequestType, $REQ, $email_content);

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
			// die(json_encode($data));
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
		$Requests = $this->MsDealerModel->GetRequest($REQTYPE, $REQ->RequestNo);
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

	//Pindah ke MsDealerApproval Start
	// public function ProcessRequest()
	// {
	// 	$RequestNo = urldecode($this->input->get("id"));
	// 	$Type = urldecode($this->input->get("type"));
	// 	$Alert = urldecode($this->input->get("alert"));
	// 	if ($this->input->get("viewonly") !== "yes") {
	// 		$ViewOnly = false;
	// 	} else {
	// 		$ViewOnly = true;
	// 	}

	// 	$RequestType = "";
	// 	$StrType = "";

	// 	if (strtoupper($Type)=="CL") {

	// 		$RequestType = "CREDIT LIMIT";
	// 		$StrType = "kenaikan Credit Limit";

	// 	} else if (strtoupper($Type)=="CBDOFF") {

	// 		$RequestType = "CBD OFF";
	// 		$StrType = "penonaktifan Status CBD";

	// 	} else if (strtoupper($Type)=="CBDON") {

	// 		$RequestType = "CBD ON";
	// 		$StrType = "pengaktifan Status CBD";

	// 	}

	// 	$data = array();
	// 	$data["RequestNo"] = $RequestNo;
	// 	$data["RequestType"] = $RequestType;
	// 	$data["alert"] = $Alert;

	// 	$GetRequest = $this->MsDealerModel->GetRequestForProcess($RequestType, $RequestNo, "");
	// 	//die(json_encode($GetRequest));
	// 	if ($GetRequest["result"]=="sukses") {
	// 		$REQ = $GetRequest["data"];
	// 		$content_html = $REQ->RequestByName." mengajukan permohonan ".$StrType." sebagai berikut :<br><br>";
	// 		$content_html.=  json_decode($REQ->AddInfo4Value);
	// 		$Requests = $GetRequest["req"];
	// 		$NO = 0;

	// 		$request_table = "<br>";
	// 		$request_table.= "<table>";
	// 		$request_table.= "	<tr>";
	// 		$request_table.= "		<th style='border:1px solid #ccc; padding:3px;'>No</th>";
	// 		$request_table.= "		<th style='border:1px solid #ccc; padding:3px;'>Nama</th>";
	// 		$request_table.= "		<th style='border:1px solid #ccc; padding:3px;'>Status</th>";
	// 		$request_table.= "		<th style='border:1px solid #ccc; padding:3px;'>Tgl Approve/Reject</th>";
	// 		$request_table.= "		<th style='border:1px solid #ccc; padding:3px;'>Catatan</th>";
	// 		$request_table.= "	</tr>";

	// 		foreach($Requests as $r) {
	// 			$NO+=1;
	// 			$request_table.= "	<tr>";
	// 			$request_table.= "		<td style='border:1px solid #ccc; padding:3px;'>".$NO."</td>";
	// 			$request_table.= "		<td style='border:1px solid #ccc; padding:3px;'>".$r->ApprovedByName."</td>";
	// 			$request_table.= "		<td style='border:1px solid #ccc; padding:3px;'>".$r->ApprovalStatus."</td>";
	// 			$request_table.= "		<td style='border:1px solid #ccc; padding:3px;'>".(($r->ApprovalStatus=="UNPROCESSED")? "-" : date("d-M-Y H:i:s",strtotime($r->ApprovedDate)))."</td>";
	// 			$request_table.= "		<td style='border:1px solid #ccc; padding:3px;'>".$r->ApprovalNote."</td>";
	// 			$request_table.= "	</tr>";				
	// 		}
	// 		$request_table.="</table>";

	// 		$request_content = "";
	// 		if ($GetRequest["status"]=="CANCELLED") {
	// 			$content_html .= "<br><font color='red'><b>Request ".$StrType." Ini Telah Dicancel oleh ".$REQ->CancelledByName." Pada ".date("d-M-Y H:i:s", strtotime($REQ->CancelledDate))."</b></font>";
	// 		} else if ($GetRequest["status"]=="PROCESSED") {
	// 			if ($REQ->ApprovalStatus=="APPROVED") {
	// 				$content_html .= "<br><b>Request ".$StrType." Ini Telah DiApprove Pada ".date("d-M-Y H:i:s", strtotime($REQ->ApprovedDate))."</b>";
	// 			} else if ($REQ->ApprovalStatus=="REJECTED") {
	// 				$content_html .= "<br><b>Request ".$StrType." Ini Telah DiReject Pada ".date("d-M-Y H:i:s", strtotime($REQ->ApprovedDate))."</b>";
	// 			} else {
	// 				$content_html .= "<br><b>Request ".$StrType." Ini Sudah Tidak Membutuhkan Respon</b>";
	// 			}
	// 		} else if ($GetRequest["status"]=="APPROVED") {
	// 			$content_html .= "<br><font color='green'><b>Request ".$StrType." Ini Telah Mendapat Approval</b></font>";
	// 		} else if ($GetRequest["status"]=="REJECTED") {
	// 			$content_html .= "<br><font color='red'><b>Request ".$StrType." Ini Telah DiReject</b></font>";
	// 		} else if ($GetRequest["status"]=="EXPIRED") {
	// 			$content_html .= "<br><font color='darkgrey'><b>Request ".$StrType." Ini Telah Kadaluarsa</b></font>";
	// 		} else {

	// 		}
	// 		$data["data"] = $GetRequest;
	// 		$data["viewOnly"] = $ViewOnly;
	// 		$data["approver"]= $this->MsDealerModel->GetRequestApproverList($RequestType, $RequestNo);
	// 		$data["content_html"] = $content_html;
	// 		$data["request_table"] = $request_table;
	//         //$this->load->view("MsDealerRequestCLView", $data);
	//         $this->SetTemplate('template/notemplate');
	//         $this->load->view("MsDealerRequestView", $data);
	// 	} else {
	// 		$data = array();
	// 		$data["content_html"] = '<script language="javascript">';
	// 		if ($GetRequest["status"]=="INVALID REQUEST") {
	// 	        $data["content_html"].= 'alert("INVALID REQUEST: REQUEST '.strtoupper($StrType).' TIDAK DITEMUKAN")';
	// 	    } else {
	// 	    	$data["content_html"].= 'alert("INVALID REQUEST: ANDA TIDAK MEMILIKI AKSES UNTUK REQUEST '.strtoupper($StrType).' INI")';
	// 	    }
	//         $data["content_html"].= '</script>';
	//         $data["content_html"].= "<script>window.close();</script>";
	//         $this->SetTemplate('template/notemplate');
	//         $this->load->view("MsDealerRequestView", $data);		
	// 	}
	// }
	//Pindah ke MsDealerApproval End

	//Pindah ke MsDealerApproval Start
	// public function ApproveRejectRequest(){
	// 	$post = $this->PopulatePost();
	// 	$RequestNo = $post["TxtRequestNo"];
	// 	$RequestType = $post["TxtRequestType"];
	// 	$UserEmail = $post["TxtUserEmail"];
	// 	$UserPwd = $post["TxtUserPwd"];
	// 	$Note = $post["TxtNote"];
	// 	//die($UserPwd);
	// 	$tipe = "";
	// 	if ($RequestType=="CREDIT LIMIT") {
	// 		$tipe = "cl";
	// 	} else if ($RequestType=="CBD OFF") {
	// 		$tipe = "cbdoff";
	// 	} else if ($RequestType=="CBD ON") {
	// 		$tipe = "cbdon";
	// 	}

	// 	if ($UserEmail=="-" || $UserEmail=="") {
	// 		redirect("MsDealer/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&alert=".urlencode("USER BELUM DIPILIH"));
	// 	} else if ($UserPwd!="") {
	// 		$zen = API_ZEN."/ZenAPI/CheckLogin?user=".urlencode($UserEmail)."&pwd=".urlencode(md5($UserPwd));
	// 		$res = json_decode(file_get_contents($zen, true));
	// 		if ($res->result == "SUKSES" || $this->test_mode==true){
	// 			if (isset($_POST["btnApprove"])) {
	// 				$doApprove = $this->MsDealerModel->ApproveRequest($RequestType, $RequestNo, $UserEmail, $Note);
	// 				if ($doApprove["result"]=="sukses") {
	// 					if ($doApprove["complete"]==true) {
	// 						$REQ = $doApprove["req"];
	// 						//die(json_encode($REQ));
	// 						$DatabaseID = $doApprove["databaseID"];
	// 						//die($DatabaseID);
	// 						$DB = $this->MasterDbModel->get($DatabaseID);
	// 						if ($DB!=null) {
	// 							$URL = $DB->AlamatWebService;
	// 							$connected = false;

	// 							$ch = curl_init($URL.API_BKT."/VirtualAccount2/TestConnection");
	// 							curl_setopt($ch, CURLOPT_TIMEOUT, 2);
	// 							curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
	// 							$response = curl_exec($ch);
	// 							if ($response === false) {
	// 								//die(json_encode($response));
	// 							    $info = curl_getinfo($ch);
	// 							    if ($info['http_code'] === 0) {
	// 							        // timeout
	// 							        $connected=false;
	// 							    }
	// 							} else {
	// 								$connected=true;
	// 							}
	// 							//die(($connected)?"connected":"not connected");

	// 							if ($connected) {
	// 								if ($RequestType=="CREDIT LIMIT") {
	// 									$URL = $URL.API_BKT."/MasterDealer/ChangeLimitDealer?svr=".urlencode(trim($DB->Server));
	// 									$URL.= "&kdplg=".urlencode(trim($REQ->AddInfo1Value))."&div=".urlencode(trim($REQ->AddInfo2Value));
	// 									$URL.= "&newcl=".urlencode($REQ->AddInfo3Value)."&db=".urlencode(trim($DB->Database));
	// 									$URL.= "&uid=".urlencode(trim(SQL_UID))."&pwd=".urlencode(trim(SQL_PWD));
	// 									$URL.= "&reqby=".urlencode($REQ->RequestByName)."&reqdate=".urlencode(trim(date("Y-m-d",strtotime($REQ->RequestDate))));
	// 									$URL.= "&appby=".urlencode($REQ->ApprovedByName);


	// 									//die($URL);
	// 									$BhaktiFlag = json_decode(file_get_contents($URL), true);
	// 									//die($URL."<br><br>".json_encode($Bhakti));
	// 								} else if ($RequestType=="CBD OFF" || $RequestType=="CBD ON") {
	// 									/*$URL = $URL.API_BKT."/MasterDealer/ApproveRejectCBD2?api=APITES".
	// 											"&hsid=".urlencode($RequestNo).
	// 											"&kdplg=".urlencode($REQ->AddInfo1Value)."&cbd=".urlencode($RequestType).
	// 											"&tgl=".urlencode(date("Y-m-d",strtotime($REQ->AddInfo6Value))).
	// 											"&sts=APPROVED&exp=".urlencode(date("Y-m-d",strtotime($REQ->ExpiryDate))).
	// 											"&reqby=".urlencode($REQ->RequestByName)."&reqdate=".urlencode(date("Y-m-d H:i:s",strtotime($REQ->RequestDate))).
	// 											"&appby=".urlencode($REQ->ApprovedByName)."&appdate=".urlencode(date("Y-m-d H:i:s",strtotime($REQ->ApprovedDate))).
	// 											"&svr=".urlencode($DB->Server)."&db=".urlencode($DB->Database).
	// 											"&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD);
	// 									//die($URL);
	// 									$BhaktiFlag = json_decode(file_get_contents($URL), true);*/

	// 									$URL = $URL.API_BKT."/MasterDealer/ApproveRejectCBD";
	// 									$params = array("api"=>"APITES", 
	// 													"hsid"=>$RequestNo, "cbd"=>$RequestType,
	// 													"kdplg"=>trim($REQ->AddInfo1Value), 
	// 													"tgl"=>date("Y-m-d", strtotime($REQ->AddInfo6Value)),
	// 													"sts"=>"APPROVED", "exp"=>date("Y-m-d", strtotime($REQ->ExpiryDate)),
	// 													"reqby"=>$REQ->RequestByName,"reqdate"=>date("Y-m-d H:i:s",strtotime($REQ->RequestDate)),
	// 													"appby"=>$REQ->ApprovedByName,"appdate"=>date("Y-m-d H:i:s",strtotime($REQ->ApprovedDate)),
	// 													"svr"=>$DB->Server, "db"=>$DB->Database, 
	// 													"uid"=>SQL_UID, "pwd"=>SQL_PWD);

	// 									$options = array(
	// 									    'http' => array(
	// 									        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
	// 									        'method'  => 'POST',
	// 									        'content' => http_build_query($params)
	// 									    )
	// 									);
	// 									$context  = stream_context_create($options);
	// 									$BhaktiFlag = json_decode(file_get_contents($URL, false, $context), true);
	// 									if ($BhaktiFlag === FALSE) {
	// 										$BhaktiFlag["result"] = "gagal";
	// 									}
	// 								}

	// 								if ($BhaktiFlag["result"]=="sukses") {
	// 									$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"FINISHED");
	// 								} else {
	// 									$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"PENDING");	
	// 								}
	// 							} else {
	// 								$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"PENDING");
	// 							}
	// 						}
	// 						$email_content = $this->CreateResponseEmailContent($RequestType, "APPROVED", $REQ);
	// 						$doEmail = $this->MsDealerModel->EmailResponseRequest($RequestType, $REQ, $email_content);
	// 						redirect("MsDealer/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&user=".urlencode($UserEmail)."&alert=".urlencode("APPROVE BERHASIL"));
	// 					} else {
	// 						redirect("MsDealer/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&user=".urlencode($UserEmail)."&alert=".urlencode("APPROVE BERHASIL"));
	// 					}
	// 				} else {
	// 					redirect("MsDealer/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&alert=".urlencode("APPROVE GAGAL"));
	// 				}
	// 			} else {
	// 				//die($UserEmail);
	// 				$doReject = $this->MsDealerModel->RejectRequest($RequestType, $RequestNo, $UserEmail, $Note);
	// 				if ($doReject["result"]=="sukses") {
	// 					if ($doReject["complete"]==true) {
	// 						$REQ = $doReject["req"];

	// 						if ($RequestType=="CBD OFF" || $RequestType=="CBD ON") {
	// 							$DatabaseID = $doReject["databaseID"];
	// 							$DB = $this->MasterDbModel->get($DatabaseID);
	// 							if ($DB!=null) {
	// 								$URL = $DB->AlamatWebService;
	// 								$connected = false;

	// 								$ch = curl_init($URL.API_BKT."/VirtualAccount2/TestConnection");
	// 								curl_setopt($ch, CURLOPT_TIMEOUT, 2);
	// 								curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
	// 								$response = curl_exec($ch);
	// 								if ($response === false) {
	// 									//die(json_encode($response));
	// 								    $info = curl_getinfo($ch);
	// 								    if ($info['http_code'] === 0) {
	// 								        // timeout
	// 								        $connected=false;
	// 								    }
	// 								} else {
	// 									$connected=true;
	// 								}
	// 								//die(($connected)?"connected":"not connected");

	// 								if ($connected) {
	// 									if ($RequestType=="CBD OFF" || $RequestType=="CBD ON") {
	// 										/*$URL = $URL.API_BKT."/MasterDealer/ApproveRejectCBD2?api=APITES".
	// 												"&hsid=".urlencode($RequestNo).
	// 												"&kdplg=".urlencode($REQ->AddInfo1Value)."&cbd=".urlencode($RequestType).
	// 												"&tgl=".urlencode(date("Y-m-d",strtotime($REQ->AddInfo6Value))).
	// 												"&sts=REJECTED&exp=".urlencode(date("Y-m-d",strtotime($REQ->ExpiryDate))).
	// 												"&reqby=".urlencode($REQ->RequestByName)."&reqdate=".urlencode(date("Y-m-d H:i:s",strtotime($REQ->RequestDate))).
	// 												"&appby=".urlencode($REQ->ApprovedByName)."&appdate=".urlencode(date("Y-m-d H:i:s",strtotime($REQ->ApprovedDate))).
	// 												"&svr=".urlencode($DB->Server)."&db=".urlencode($DB->Database).
	// 												"&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD);
	// 										//die($URL);
	// 										$BhaktiFlag = json_decode(file_get_contents($URL), true);*/
											
	// 										$URL = $URL.API_BKT."/MasterDealer/ApproveRejectCBD";
	// 										$params = array("api"=>"APITES", 
	// 														"hsid"=>$RequestNo, "cbd"=>$RequestType,
	// 														"kdplg"=>trim($REQ->AddInfo1Value), 
	// 														"tgl"=>date("Y-m-d", strtotime($REQ->AddInfo6Value)),
	// 														"sts"=>"REJECTED", "exp"=>date("Y-m-d", strtotime($REQ->ExpiryDate)),
	// 														"reqby"=>$REQ->RequestByName,"reqdate"=>date("Y-m-d H:i:s",strtotime($REQ->RequestDate)),
	// 														"appby"=>$REQ->ApprovedByName,"appdate"=>date("Y-m-d H:i:s",strtotime($REQ->ApprovedDate)),
	// 														"svr"=>$DB->Server, "db"=>$DB->Database, 
	// 														"uid"=>SQL_UID, "pwd"=>SQL_PWD);

	// 										$options = array(
	// 										    'http' => array(
	// 										        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
	// 										        'method'  => 'POST',
	// 										        'content' => http_build_query($params)
	// 										    )
	// 										);
	// 										$context  = stream_context_create($options);
	// 										$BhaktiFlag = json_decode(file_get_contents($URL, false, $context), true);
	// 										if ($BhaktiFlag === FALSE) {
	// 											$BhaktiFlag["result"] = "gagal";
	// 										}
	// 									}

	// 									if ($BhaktiFlag["result"]=="sukses") {
	// 										$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"FINISHED");
	// 									} else {
	// 										$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo,"PENDING");	
	// 									}
	// 								} else {
	// 									$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo, "PENDING");
	// 								}
	// 							}
	// 						} else {
	// 							$BhaktiFlag = $this->MsDealerModel->UpdateBhaktiFlag($RequestType, $REQ->RequestNo, "FINISHED");
	// 						}
	// 					}
	// 					$email_content = $this->CreateResponseEmailContent($RequestType, "REJECTED", $REQ);
	// 					$doEmail = $this->MsDealerModel->EmailResponseRequest($RequestType, $REQ, $email_content);
	// 					redirect("MsDealer/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&alert=".urlencode("REJECT BERHASIL"));
	// 				} else {
	// 					redirect("MsDealer/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&alert=".urlencode("REJECT GAGAL"));
	// 				}
	// 			}
	// 		} else {
	// 			redirect("MsDealer/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&alert=".urlencode("PASSWORD SALAH"));
	// 		}
	// 	} else {
	// 		redirect("MsDealer/ProcessRequest?type=".$tipe."&id=".urlencode($RequestNo)."&alert=".urlencode("PASSWORD BELUM DIISI"));
	// 	}
	// }
	//Pindah ke MsDealerApproval End

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

    public function GetLastTenRequests()
    {
		$post = $this->PopulatePost();
		if(isset($post['KdPlg']) && isset($post["Divisi"])){
			$GetLastTen = $this->MsDealerModel->GetLastTenRequests($post["KdPlg"], $post["Divisi"]);
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

	public function addRecipients_apv($listapv, $priority, $approvalNeeded)
    {
		$USER = array();
		foreach($listapv as $m) {
			$USER["NAMA"] = $m->employee_name;
			$USER["EMAIL"] = (($m->email_address==null)?$m->email:$m->email_address);
			$USER["USEREMAIL"] = $m->useremail;
			$USER["WA"] = $m->mobile;
			$USER["PRIORITY"] = $priority;
			$USER["APPROVALNEEDED"] = $approvalNeeded;
			$this->CheckRecipients($USER);
		}
    }

	public function addRecipients_apvkacab($listapv, $priority, $approvalNeeded)
    {
		$USER = array();
		foreach($listapv as $m) {
			$USER["NAMA"] = $m->employee_name;
			$USER["EMAIL"] =  $m->email ;
			$USER["USEREMAIL"] = $m->useremail;
			$USER["WA"] = $m->mobile;
			$USER["PRIORITY"] = $priority;
			$USER["APPROVALNEEDED"] = $approvalNeeded;
			// die(json_encode($USER));
			$this->CheckRecipients($USER);
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

			$RequestExisted = $this->MsDealerModel->CheckExistingRequest($RequestType, $params);
			if ($RequestExisted==false) {

							
				$URL = $this->API_BKT."/MasterDealer/CreateRequestCBD";
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
				// $doUpdate = json_decode(file_get_contents($URL, false, $context), true);
				$doUpdate = file_get_contents($URL, false, $context);
				$doUpdate = $this->GzipDecodeModel->_decodeGzip_true($doUpdate);

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

				
				$URL = $this->API_BKT."/MasterDealer/GetDealer?api=APITES".
						"&plg=".urlencode($post["KdPlg"]).
						"&svr=".urlencode($_SESSION["conn"]->Server)."&db=".urlencode($_SESSION["conn"]->Database).
						"&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD);
				// $GetDealer = json_decode(file_get_contents($URL), true);
				$GetDealer = file_get_contents($URL);
				$GetDealer = $this->GzipDecodeModel->_decodeGzip_true($GetDealer);
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
						$REQUESTID = $this->MsDealerModel->RequestCBD($params, $email_content, $recipients, $user);
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
						$REQUESTID = $this->MsDealerModel->RequestCBD($params, $email_content, $recipients, $user);
					}

					$sendEmail = $this->MsDealerModel->EmailRequest($RequestType, $params, $email_content, $allRecipients, $user);
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
}