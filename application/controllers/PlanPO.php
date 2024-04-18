<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	
	class PlanPO extends MY_Controller
	{
		public $approvaltype = 'PLAN PO';
		public $approvedbyfrommsconfig = false;
		public $expirydatefrommsconfig = false;
		public $approvaldefault = 0;
		
		function __construct()
		{
			parent::__construct();
			$this->load->model('SalesManagerModel');
			$this->load->model('PlanPOModel');
			$this->load->model("HelperModel");
			$this->load->model("approvalmodel");
			$this->load->library('email');
			$this->load->library('excel');
			require_once(dirname(__FILE__)."/approval.php"); // the controller route.
			$this->approval = new approval();
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		}
		
		public function index() /*checked*/
		{
			$CheckAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
			if($_SESSION["can_read"]==false) { 
			   redirect("message/index/er_auth"); 
			}

			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "PLAN PO";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU PLAN PO";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);	

			$post = $this->PopulatePost();			
			$this->ViewPlanPOList($post);		
		}

		public function ViewPlanPOList($post) /*checked*/
		{
			$data = array();
	        // $poPlans = $this->PlanPOModel->GetList($post);
	        // // die(json_encode($poPlans));

	        // foreach($poPlans as $p) {
	        // 	$p->Period1 = $this->HelperModel->GetNmPeriode($p->PeriodTh1, $p->PeriodBl1, $p->PeriodP1);
	        // 	$p->Period2 = $this->HelperModel->GetNmPeriode($p->PeriodTh2, $p->PeriodBl2, $p->PeriodP2);
	        // }
	        // $data["poPlans"] = $poPlans;
			$this->RenderView('MsPlanPOList',$data);
		}

		public function GetPlanPOList() /*checked*/
		{
			$post = $this->PopulatePost();
			$poPlans = $this->PlanPOModel->GetList($post);
			// die(json_encode($poPlans));
	        foreach($poPlans as $p) {
	        	$p->Period1 = $this->HelperModel->GetNmPeriode($p->PeriodTh1, $p->PeriodBl1, $p->PeriodP1);
	        	$p->Period2 = $this->HelperModel->GetNmPeriode($p->PeriodTh2, $p->PeriodBl2, $p->PeriodP2);
	        }

			if (count($poPlans)>0) {
				echo(json_encode(array("result"=>"SUCCESS", "list"=>$poPlans)));
			} else {
				echo(json_encode(array("result"=>"FAILED", "list"=>array())));
			}
		}
		
		function view()
		{	
			$PlanId = urldecode($this->input->get("trxid"));
			$planHD = $this->PlanPOModel->GetPlanHD($PlanId);
			$this->FinalStep2("view", $planHD->PlanNo);
		}

		function viewFromDashboard()
		{	
			$PlanId = urldecode($this->input->get("trxid"));
			$planHD = $this->PlanPOModel->GetPlanHD($PlanId);
			$this->FinalStep2FromDashboard("view", $planHD->PlanNo);
		}

		/*function viewPlanPO($trxID) 
		{
			$data = array();
			$resData = array();			
			$planHD = json_decode($this->PlanPOModel->GetTransaksiDetail($trxID));
			//die(json_encode($planHD));
			$wilayahs = json_decode($this->PlanPOModel->GetTransaksiWilayahInclude($trxID));

			// $barang = array();
			// foreach($planHD as $hd) {
			// 	//echo($hd->ProductID."<br>");
			// 	array_push($barang, $hd->ProductID);
			// }

			$data = array();

			$barangs = array();
			foreach($planHD as $hd) {
				array_push($barangs, $hd->ProductID);
			}
			$result["barang"] = $barangs;

			$arrayBarang = array();
			$array_barang = array();

			$previousCampaigns = $this->PlanPOModel->GetPreviousCampaigns($trxID);
			foreach($previousCampaigns as $c) {
				if ($c->IsSelected==1) {
					$breakdowns = array();

					$GetBreakdowns = $this->PlanPOModel->GetBreakdowns($trxID, $c->id);
					foreach($GetBreakdowns as $b) {
						array_push($breakdowns, array("Wilayah"=>trim($b->Kota), "Wil"=>$this->replaceSymbolChars(trim($b->Kota)), 
									"Kd_Lokasi"=>$b->Kd_Lokasi, 
									"AvgJual"=>$b->Avg_Jual, "TotalAvgAll"=>$b->Total_Avg_Jual,
									"Persentase"=>$b->Persentase_Jual, "TotalQtyCampaign"=>$b->Total_Qty_Campaign, 
									"TotalQty"=>$b->Total_Qty, "IsDraft"=>(($b->IsDraft==null)?0:$b->IsDraft), 
									"IsSelected"=>(($b->IsSelected==null)?0:$b->IsSelected)));
					}

					$KdBrg = $this->replaceSymbolChars(trim($c->ProductID));
					$array_barang = array("Kd_Brg"=>trim($c->ProductID), "KdBrg"=>$KdBrg,  
						"Jns_Trx"=>trim($c->JnsTrx), "Nm_Trx"=>trim($c->NmTrx), "Flag"=>$c->Flag, 
						"Total_Hari"=>$c->TotalHari, "Total_Jual"=>$c->TotalJual, "Avg"=>$c->AvgJual, "Total_Avg"=>$c->TotalQty,
						"Total_Hari_Plan"=>$c->TotalHariPlan, "Breakdown_Per_Wilayah"=>$breakdowns, "Id"=>$c->id, 
						"IsDraft"=>(($c->IsDraft==null)?0:$c->IsDraft), 
						"IsSelected"=>(($c->IsSelected==null)?0:$c->IsSelected));
					array_push($arrayBarang, $array_barang);
				}
			}

			$result["status"] = "view";
		
			$result['campaigns'] = $arrayBarang;
			$result['CampaignID'] = $trxID;
			$result['headers'] = $planHD;
			$result["wilayahs"] = $wilayahs;			
			$this->RenderView('MsCampaignPlanView', $result);
		}*/

		public function add() /*checked*/
		{
			$CheckAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
			$post = $this->PopulatePost();
			
			$this->editStep1("add","");
		}

		public function edit()
		{
			$CheckAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
			$post = $this->PopulatePost();
			$trxID = urldecode($this->input->get("trxid"));
			$this->editStep1("edit", $trxID);
		}

		public function editStep1($mode="add", $planId="")
		{
			$CheckAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
			$post = $this->PopulatePost();
			
			$data = array();
			
			$data = [
			'api' => 'APITES'
			];
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $this->API_URL . "/POPlan/GetwilayahInclude",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => http_build_query($data),
			));
			
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// die($response);
			$hasil = json_decode($response);
			$data = array();
			
			if ($hasil->result == "sukses") {
				$data["wilayah"] = json_decode($hasil->data);
			} else {
				$data["wilayah"] = "";
			}
			// die(json_encode($data["wilayah"]));

			$divisi = json_decode(file_get_contents($this->API_URL."/MsBarang/GetDivisiList?api=APITES"), true);
			// die(json_encode($divisi));
			if ($divisi["result"] == "sukses") {
				$data["divisions"] = $divisi["data"];
			} else {
				$data["divisions"] = array();
				$data["alert"] = $divisi["error"];
			}

			$data["list_miyako"] = array();
			$data["list_micook"] = array();
			$data["list_rinnai"] = array();
			$data["list_shimizu"] = array();
			$data["list_cosanitary"] = array();

			$data["miyako"] = array();
			$data["micook"] = array();
			$data["rinnai"] = array();
			$data["shimizu"] = array();
			$data["cosanitary"] = array();

			$DIVISI = "";

			if ($mode=="edit") {
				// die("here");
				$planHD = $this->PlanPOModel->GetPlanHD($planId);
				$DIVISI = $planHD->Division;
			}

			// echo(date("d-M-Y H:i:s")."<br>");
			if ($DIVISI=="" || $DIVISI=="MIYAKO") {			
				$url = $this->API_URL."/MsBarang/BarangListExcludeHadiahGET?api=APITES&divisi=MIYAKO";
				// die($url);
				$listProduk = json_decode(file_get_contents($url),true);
				// die(json_encode($listProduk));
				if ($listProduk["result"]=="sukses") {
					$BRG = $listProduk["data"];
					$data["list_miyako"] = $BRG;
					for($i=0;$i<count($BRG);$i++) {
						array_push($data["miyako"], trim($BRG[$i]["KD_BRG"]));
					}
				}
			}
			// echo(date("d-M-Y H:i:s")."<br>");
			if ($DIVISI=="" || $DIVISI=="MICOOK") {			
				$listProduk = json_decode(file_get_contents($this->API_URL."/MsBarang/BarangListExcludeHadiahGET?api=APITES&divisi=MICOOK"),true);
				// die(json_encode($listProduk));
				if ($listProduk["result"]=="sukses") {
					$BRG = $listProduk["data"];
					$data["list_micook"] = $BRG;
					for($i=0;$i<count($BRG);$i++) {
						array_push($data["micook"], trim($BRG[$i]["KD_BRG"]));
					}
				}
			}
			// echo(date("d-M-Y H:i:s")."<br>");
			if ($DIVISI=="" || $DIVISI=="RINNAI") {			
				$listProduk = json_decode(file_get_contents($this->API_URL."/MsBarang/BarangListExcludeHadiahGET?api=APITES&divisi=RINNAI"),true);
				// die(json_encode($listProduk));
				if ($listProduk["result"]=="sukses") {
					$BRG = $listProduk["data"];
					$data["list_rinnai"] = $BRG;
					for($i=0;$i<count($BRG);$i++) {
						array_push($data["rinnai"], trim($BRG[$i]["KD_BRG"]));
					}
				}
			}
			// echo(date("d-M-Y H:i:s")."<br>");
			if ($DIVISI=="" || $DIVISI=="SHIMIZU") {			
				$listProduk = json_decode(file_get_contents($this->API_URL."/MsBarang/BarangListExcludeHadiahGET?api=APITES&divisi=SHIMIZU"),true);
				// die(json_encode($listProduk));
				if ($listProduk["result"]=="sukses") {
					$BRG = $listProduk["data"];
					$data["list_shimizu"] = $BRG;
					for($i=0;$i<count($BRG);$i++) {
						array_push($data["shimizu"], trim($BRG[$i]["KD_BRG"]));
					}
				}
			}
			// echo(date("d-M-Y H:i:s")."<br>");
			if ($DIVISI=="" || $DIVISI=="CO&SANITARY") {			
				$listProduk = json_decode(file_get_contents($this->API_URL."/MsBarang/BarangListExcludeHadiahGET?api=APITES&divisi=".urlencode("CO&SANITARY")),true);
				// die(json_encode($listProduk));
				if ($listProduk["result"]=="sukses") {
					$BRG = $listProduk["data"];
					$data["list_cosanitary"] = $BRG;
					for($i=0;$i<count($BRG);$i++) {
						array_push($data["cosanitary"], trim($BRG[$i]["KD_BRG"]));
					}
				}
			}
			// echo(date("d-M-Y H:i:s")."<br>");
			// die(json_encode($data["cosanitary"]));

			$data["isDraft"] = 1;
			$data["mode"] = $mode;
			$data["curMonth"] = date("m");
			$data["curYear"] = date("Y");
			$data["curPeriod"] = ((date("d")<16)? 1:2);

			$data["planHD"] = array();
			$data["dtPeriods"] = array();
			$data["dtProducts"] = array();
			$data["dtRegions"] = array();			

			if ($mode=="edit") {
				// die("here");
				$planHD = $this->PlanPOModel->GetPlanHD($planId);
				// die(json_encode($planHD)."<br>");
				$dtPeriods = array();
				$dtProducts = array();
				$dtRegions = array();

				if($planHD!=null){
					$planNo = $planHD->PlanNo;
					$dtRegions = $this->PlanPOModel->GetDetailWilayah($planNo);
					foreach($dtRegions as $w) {
						$w->Wil = $this->ReplaceSymbolChars($w->Region);
					}
					// echo(json_encode($dtRegions)."<br>");
					// $planHD = json_decode($this->PlanPOModel->CheckItemID($planId, $planHD));

					$dtPeriods = $this->PlanPOModel->GetDetailPeriode($planNo);
					$dtProducts= $this->PlanPOModel->GetDetailProduct($planNo);
					// die(json_encode($dtProducts));
				}

				$data["planHD"] = $planHD;
				$data["dtRegions"] = $dtRegions;
				$data["dtPeriods"] = $dtPeriods;
				$data["dtProducts"]= $dtProducts;
			}

			//die(json_encode($data));
			$this->RenderView('MsPlanPOEdit1', $data);
		}

		public function editPlanPO()
		{
			$post = $this->PopulatePost();
			$post['api'] = 'APITES';
			$post["mode"] = "edit";
			// die(json_encode($post));

			$ProcessDraft = $this->PlanPOModel->SavePOPlan($post);
			//$SimpanWilayah = $this->PlanPOModel->ProcessDraftWilayah($post);
			//$this->viewCampaignPlan($post["txtKodeCampaign"]);
			$this->continueStep2($post);
		}

		public function editStep2()
		{
			$post = $this->PopulatePost();
			$post['api'] = 'APITES';
			$post["mode"] = "add";
			// die(json_encode($post));
			
			$ProcessDraft = $this->PlanPOModel->SavePOPlan($post);
			$this->continueStep2($post);
		}

		public function continueStep2($post)
		{
			$planNo = $post["txtPlanCode"];

			$data = array();
			$data = [
				'value' => $post
			];

			// $CheckDraftDT = $this->PlanPOModel->CheckDraftDT($planID);
			// die(json_encode($CheckDraftDT));

			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $this->API_URL . "/POPlan/GetAverageSales",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => http_build_query($data),
			));
			
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo("API RETURN: <br>");
			// die($response."<br>");
			$response = json_decode($response);
			$dataAverage = array();

			// if ($err!="") {
			// 	echo(json_encode($response)."<br>");
			// 	die($err);
			// }

			if ($response->result=="sukses") {
				$dataAverage = $response->data;
				$saveDT = $this->PlanPOModel->SavePOPlanDT($post, $dataAverage);
			}
			$this->FinalStep2("edit", $planNo, $dataAverage);
		}

		public function FinalStep2($mode, $planNo, $dataAverage=array())
		{
			$planHD = $this->PlanPOModel->GetPlanHD2($planNo);
			$planDT = $this->PlanPOModel->GetPlanDT($planNo);
			$productSummary = $this->PlanPOModel->GetPlanDTProductSummary($planNo);
			$dtRegions = $this->PlanPOModel->GetDetailWilayah($planNo);
			$dtProducts= $this->PlanPOModel->GetDetailProduct($planNo);
			$dtPeriods = $this->PlanPOModel->GetDetailPeriode($planNo);
			
			$result = array();
			$result["PlanID"] = $planNo;
			$result['PlanHD'] = $planHD;
			$result['PlanDT'] = $planDT;
			$result["productSummary"] = $productSummary;
			$result['dtProducts'] = $dtProducts;
			$result['dtPeriods'] = $dtPeriods;
			$result["dtRegions"] = $dtRegions;
			$result["dtAverage"] = $dataAverage;
			$result["mode"] = $mode;
			$result['approval'] = $this->approvaldefault;
			// die(json_encode($result));
			$this->RenderView('MsPlanPOEdit2', $result);
		}

		public function FinalStep2FromDashboard($mode, $planNo, $dataAverage=array())
		{
			$planHD = $this->PlanPOModel->GetPlanHD2($planNo);
			$planDT = $this->PlanPOModel->GetPlanDT($planNo);
			$productSummary = $this->PlanPOModel->GetPlanDTProductSummary($planNo);
			$dtRegions = $this->PlanPOModel->GetDetailWilayah($planNo);
			$dtProducts= $this->PlanPOModel->GetDetailProduct($planNo);
			$dtPeriods = $this->PlanPOModel->GetDetailPeriode($planNo);
			
			$result = array();
			$result["PlanID"] = $planNo;
			$result['PlanHD'] = $planHD;
			$result['PlanDT'] = $planDT;
			$result["productSummary"] = $productSummary;
			$result['dtProducts'] = $dtProducts;
			$result['dtPeriods'] = $dtPeriods;
			$result["dtRegions"] = $dtRegions;
			$result["dtAverage"] = $dataAverage;
			$result["mode"] = $mode;
			$result['approval'] = 1;
			// die(json_encode($result));
			$this->RenderView('MsPlanPOEdit2', $result);
		}

		public function FinalSave()
		{				
			$post = $this->PopulatePost();
			//die(json_encode($post));
			$PlanNo = urldecode($this->input->get("trx"));
			$save = $this->PlanPOModel->FinalSave($PlanNo, $post);
			if ($save["result"]=="SUCCESS") {
				$this->FinalStep2("view", $PlanNo);
			} else {

			}
		}

		public function SaveDraftProduct()
		{
			$data = array();
			$post = $this->PopulatePost();
			$simpanPOPlan = $this->PlanPOModel->saveDraftProduct($post);
			echo(json_encode($simpanPOPlan));
		}

		public function SaveDraftWilayah()
		{
			$data = array();
			$post = $this->PopulatePost();
			if ($post["wilayah"]=="ALL") {
				$data = array();
				
				$data = [
				'api' => 'APITES'
				];
				
				$curl = curl_init();
				curl_setopt_array($curl, array(
				CURLOPT_URL => $this->API_URL . "/PerencanaanBarangCampaign/GetwilayahInclude",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => http_build_query($data),
				));
				
				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);
				$hasil = json_decode($response);
				
				$data = array();
				if ($hasil->result == "sukses") {
					//$data["wilayah"] = json_decode($hasil->data);
					$ListWilayah = json_decode($hasil->data);
					foreach($ListWilayah as $w) {
						$post["wilayah"] = $w->Kota;
						$post["kode_lokasi"] = $w->Kd_Lokasi;
						$simpanCampaignPlan = $this->PlanPOModel->saveDraftWilayah($post);
					}

				} else {
					$simpanCampaignPlan = array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>"Gagal Ambil List Wilayah");
				}
			} else {
				$simpanCampaignPlan = $this->PlanPOModel->saveDraftWilayah($post);
			}
			echo(json_encode($simpanCampaignPlan));
		}

		public function RemoveDraft()
		{
			$data = array();
			$post = $this->PopulatePost();
			$removeDraft = $this->PlanPOModel->removeDraft($post);
			echo(json_encode($removeDraft));
		}

		public function RemoveProduct()
		{
			$data = array();
			$post = $this->PopulatePost();
			$removeItem = $this->PlanPOModel->RemoveProduct($post);
			echo(json_encode($removeItem));
		}

		public function RemoveDrafts()
		{
			$data = array();
			$post = $this->PopulatePost();
			$removeCampaignPlan = $this->PlanPOModel->removeDrafts($post["kode_plan"]);
			echo(json_encode($removeCampaignPlan));
		}

		public function CancelPlan()
		{
			$data = array();
			$post = $this->PopulatePost();

			$params = array();
			$params['ApprovalType'] = $this->approvaltype;
			$params["RequestNo"] = $post["PlanNo"];
			$params["CancelledBy"] = $_SESSION["logged_in"]["employeeid"];
			$params["CancelledByName"] = $_SESSION["logged_in"]["username"];
			$params["CancelledDate"] = date('Y-m-d H:i:s');
			$params["CancelledNote"] = $post["Alasan"];
			$params["CancelledByEmail"] = $_SESSION["logged_in"]["useremail"];
			$this->approval->cancelbyajax($params);

			$cancelPlan = $this->PlanPOModel->cancelPlan($post);
			// die(json_encode($cancelPlan));
			echo(json_encode($cancelPlan));
		}

		public function GetPoPeriod()
		{
			$data = array();
			$post = $this->PopulatePost();
			$post["name"] = $this->HelperModel->GetNmPeriode($post["th"], $post["bl"], $post["p"]);
			$GetPeriod = $this->PlanPOModel->GetPeriod($post);
			echo(json_encode($GetPeriod));
		}

		public function SavePoPeriod()
		{
			$data = array();
			$post = $this->PopulatePost();
			$SavePeriod = $this->PlanPOModel->SavePeriod($post);
			echo(json_encode($SavePeriod));
		}

		public function ReplaceSymbolChars($string="") {
			$string = str_replace(" ", "", $string);
			$string = str_replace(",", "_", $string);
			$string = str_replace(".", "_", $string);
			$string = str_replace("/", "_", $string);
			$string = str_replace("-", "_", $string);
			$string = str_replace("'", "_", $string);
			$string = str_replace("&", "_", $string);
			return $string;
		}

		public function CreateRequest()
		{
			$trxID = urldecode($this->input->get("trxid"));
			$planHD = $this->PlanPOModel->GetPlanHD($trxID);
			$Divisi = $planHD->Division;

			$emailContent = $this->CreateEmailContent($planHD->PlanNo, $planHD);
			
			//echo($header);
			//echo($body);
			$BM = $this->SalesManagerModel->GetBrandManagersByDivisi($Divisi);
			// die($Divisi);
			// die(json_encode($BM));
			$footer = "<style> .btn { ";
			$footer.= "		border:1px solid #ccc; border-radius:10px; font-size:14px; text-align:center; padding:10px; float:left; ";
			$footer.= "		margin: 10px 10px 10px 0px; color:white; font-weight:bold; width:140px;";
			$footer.= "} </style>";
			$footer.= "<a href='".site_url("PlanPOApproval/Approved?trxid=".urlencode($planHD->PlanNo)."&approvedby=".urlencode($BM[0]->userid))."'><div class='btn btnApprove' style='background-color:#18400b;'>APPROVE</div></a>";
			$footer.= "<a href='".site_url("PlanPOApproval/Rejected?trxid=".urlencode($planHD->PlanNo)."&approvedby=".urlencode($BM[0]->userid))."'><div class='btn btnReject'  style='background-color:#6b0202;'>REJECT</div></a>";
			$footer.= "<div style='clear:both;'></div>";

			$this->email->clear(true);
			$this->email->from("bhaktiautoemail.noreply@bhakti.co.id", "BHAKTI.CO.ID AUTO-EMAIL");
			$this->email->to($BM[0]->email_address);
			// $this->email->to("indah@bhakti.co.id");
			// $this->email->cc(array("bhaktiautoemail.noreply@bhakti.co.id", "itdev.dist@bhakti.co.id"));

			$header = "<h3>RENCANA INTERVENSI PO BARU</h3>";
			$email_content = $header.$emailContent.$footer;
			//die($email_content);
			//$email_content.= "Periode : ".date("d-M-Y",strtotime($tgl1))." s/d ".date("d-M-Y", strtotime($tgl2));
			$this->email->subject("Req New Plan PO ".$Divisi);
			$this->email->message($email_content);
			$EmailDate = date("Y-m-d H:i:s");
			if ($this->email->send()) {
				$this->PlanPOModel->EmailRequestSent($planHD->PlanNo, $BM[0], true, $EmailDate);

				$params = array();
				$params["ApprovalType"] = $this->approvaltype;
				$params["RequestNo"] = $planHD->PlanNo;
				$params["RequestBy"] = $_SESSION["logged_in"]["useremail"];
				$params["RequestDate"] = $EmailDate;
				$params["RequestByName"] = $_SESSION["logged_in"]["username"];
				$params["RequestByEmail"] = $_SESSION["logged_in"]["useremail"];
				$params["ApprovedBy"] = $BM[0]->userid;
				$params["ApprovedByName"] = $BM[0]->nm_slsman;
				$params["ApprovedByEmail"] = $BM[0]->email_address;
				$params["ApprovedDate"] = NULL;
				$params["ApprovalStatus"] = "UNPROCESSED";
				$params["ApprovalNote"] = NULL;
				$params["AddInfo1"] = "Plan ID";
				$params["AddInfo1Value"] = $planHD->PlanId;
				$params["AddInfo2"] = "Divisi";
				$params["AddInfo2Value"] = $planHD->Division;
				$params["AddInfo3"] = "";
				$params["AddInfo3Value"] = "";
				$params["AddInfo4"] = "";
				$params["AddInfo4Value"] = "";
				$params["AddInfo5"] = "";
				$params["AddInfo5Value"] = "";
				$params["AddInfo6"] = "Periode";
				$params["AddInfo6Value"] = $planHD->Periode;
				$params["AddInfo7"] = "";
				$params["AddInfo7Value"] = "";
				$params["AddInfo8"] = "Catatan";
				$params["AddInfo8Value"] = $planHD->PlanNote;
				$params["AddInfo9"] = "";
				$params["AddInfo9Value"] = "";
				$params["AddInfo10"] = "";
				$params["AddInfo10Value"] = "";
				$params["AddInfo11"] = "";
				$params["AddInfo11Value"] = "";
				$params["AddInfo12"] = "";
				$params["AddInfo12Value"] = "";
				$params["ApprovalNeeded"] = "";
				$params["Priority"] = "1";
				$params["ExpiryDate"] = $planHD->TglPO;
				$params["BhaktiFlag"] = "UNPROCESSED";
				$params["BhaktiProcessDate"] = "";
				$params["IsCancelled"] = 0;
				$params["CancelledBy"] = NULL;
				$params["CancelledByName"] = NULL;
				$params["CancelledDate"] = NULL;
				$params["CancelledNote"] = NULL;
				$params["CancelledByEmail"] = NULL;
				$params["LocationCode"] = "HO";
				$params["IsEmailed"] = 1;
				$params["EmailedDate"] = $EmailDate;
				$params["approvedbyfrommsconfig"] = $this->approvedbyfrommsconfig;
				$params["expirydatefrommsconfig"] = $this->expirydatefrommsconfig;
				$params["amount"] = 0;
				$params["branchid"] = $_SESSION["logged_in"]["branch_id"];
				$x = $this->approval->doaction('insert', $params);

				$data["content_html"] = '<script language="javascript">';
		        $data["content_html"].= 'alert("Plan Saved and Email Sent")';
		        $data["content_html"].= '</script>';
		        $data["content_html"].= "<script>window.close();</script>";
			} else {
				$this->PlanPOModel->EmailRequestSent($planHD->PlanNo, $BM[0], false, $EmailDate);
				$data["content_html"] = '<script language="javascript">';
		        $data["content_html"].= 'alert("Plan Saved; Email Not Sent")';
		        $data["content_html"].= '</script>';
		        $data["content_html"].= "<script>window.close();</script>";
			}
		    $this->RenderView("CustomPageResult", $data);
		}

		public function CreateEmailContent($planNo, $planHD)
		{
			$planDT = $this->PlanPOModel->GetPlanDT($planNo);
			$productSummary = $this->PlanPOModel->GetPlanDTProductSummary($planNo);
			$dtRegions = $this->PlanPOModel->GetDetailWilayah($planNo);
			$dtProducts= $this->PlanPOModel->GetDetailProduct($planNo);
			$dtPeriods = $this->PlanPOModel->GetDetailPeriode($planNo);
			$ProductCount = count($dtProducts);
			$PeriodCount = count($dtPeriods);
			// die(json_encode($planDT));
			$ProductPerTable = floor(6/$PeriodCount);
			// die((string)$ProductPerTable);

			$header = "Diinput Oleh: ".$planHD->CreatedBy."<br>";
			$TGLINPUT = (($planHD->ModifiedDate==null)? $planHD->CreatedDate : $planHD->ModifiedDate);
			$header.= "Waktu Request:".date("Y-m-d H:i:s", strtotime($TGLINPUT))."<br>";
			$header.= "<hr><br>";
			$header.= "Kode Rencana : <b>".$planNo."</b><br>";
			$header.= "Divisi: <b>".$planHD->Division."</b><br>";
			$header.= "Periode: <b>".$planHD->Periode1." s/d ".$planHD->Periode2."</b><br>";
			$header.= "Keterangan: <b>".(($planHD->PlanNote=="")?"-":$planHD->PlanNote)."</b><br>";
			$header.= "Total Barang: <b>".$ProductCount."</b><br>";
			$header.= "<br>";

			
			$body = "<h3>Detail Barang</h3>";
			$body.= "<style>";
			$body.= "	th,td { border:1px solid #ccc; padding:3px; text-align:right; }";
			$body.= "	th { background-color:#ccc; }";
			$body.= "</style>";
			$tables = array();

			$counter = 0;
			$qty = 0;


			$products = array();
			$product = array();
			$ProductCount = 0; 

			$TotalQty = array();

			foreach($dtProducts as $p) {
				array_push($product, $p->ProductId);
				foreach($dtPeriods as $pd) {
					$TotalQty[$p->ProductId][$pd->PeriodId] = 0;
				}

				$ProductCount+=1;

				if ($ProductCount==$ProductPerTable || $ProductCount==count($dtProducts)) {
					array_push($products, $product);
					$ProductCount = 0;
					$product = array();
				}
			}

			// die(json_encode($products));

			for($i=0; $i<count($products); $i++) {

				$product = $products[$i];

				$body.="<table>";
				$body.="	<tr>";
				$body.="		<th width='20%' rowspan='2'>Wilayah</th>";
				for ($x=0; $x<count($product); $x++) {
					$body.="	<th width='20%' colspan='".($PeriodCount+1)."' style='text-align:center!important;'>".$product[$x]."</th>";
				}
				$body.="	</tr>";
				$body.="	<tr>";
				for ($x=0; $x<count($product); $x++) {
					$body.="	<th width='10%'>Avg Sales</th>";
					foreach($dtPeriods as $pd) {
						$body.="<th width='10%'>".$pd->PeriodName."</th>";
					}
				}
				$body.="	</tr>";


				foreach($dtRegions as $r) {
					$body.="<tr>";
					$body.="	<td>".$r->Region."</td>";
					for($x=0;$x<count($product);$x++) {
						$pd = $dtPeriods[0];
						foreach($planDT as $dt) {
							if ($dt->ProductId==$product[$x] && $dt->Region==$r->Region && $dt->PeriodId==$pd->PeriodId) {
								$TotalQty[$product[$x]][0] = $dt->RSalesQtyTotal;
								$qty = $dt->RQtyRegionTotal;
								break 1;
							}
						}
						$body.= "<td style='background-color:#e3ffba;'>".number_format($qty)."</td>";

						foreach($dtPeriods as $pd) {
							foreach($planDT as $dt) {
								if ($dt->ProductId==$product[$x] && $dt->Region==$r->Region && $dt->PeriodId==$pd->PeriodId) {
									$qty = $dt->QtyRegionTotal;
									$TotalQty[$product[$x]][$pd->PeriodId] += $qty;
									break 1;
								}
							}
							$body.= "<td>".number_format($qty)."</td>";
						}
					}
					$body.="</tr>";
				}
				$body.="	<tr>";
				$body.="		<th width='20%'>Total</th>";
				for($x=0;$x<count($product);$x++) {
					$body.="	<th width='10%'>".number_format($TotalQty[$product[$x]][0])."</th>";
					foreach($dtPeriods as $pd) {
						$body.="<th width='10%'>".number_format($TotalQty[$product[$x]][$pd->PeriodId])."</th>";
					}
				}
				$body.="	</tr>";
				$body.="</table>";
				$body.="<div style='height:25px;'></div>";
			}

			return $header.$body;
		}

		public function SaveDraftDT() /*checked*/
		{
			$data = array();
			$post = $this->PopulatePost();
			$simpanPlanDT = $this->PlanPOModel->saveDraftDT($post);
			echo(json_encode($simpanPlanDT));
		}

		public function SaveDraftDTTotalQty() /*checked*/
		{
			$data = array();
			$post = $this->PopulatePost();
			$simpanPlanDT = $this->PlanPOModel->saveDraftDTTotalQty($post);
			echo(json_encode($simpanPlanDT));
		}

	}
