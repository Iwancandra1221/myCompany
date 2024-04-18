<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

	class CampaignPlan extends MY_Controller
	{
		public $excel_flag = 0;
		public $approvaltype = 'CAMPAIGN PLAN';
		public $approvedbyfrommsconfig = false;
		public $expirydatefrommsconfig = false;
		public $approvaldefault = 0;

		function __construct()
		{
			parent::__construct();
			$this->load->model('SalesManagerModel');
			$this->load->model('CampaignPlanModel');
			$this->load->model('HelperModel');
			$this->load->library('email');
			$this->load->library('excel');
			$this->load->library('../controllers/approval');
			$this->approval = new approval();
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		}
		
		public function index()
		{
			// $this->RenderView("UnderMaintenanceView");
			//die($this->uri->segment(2));

			$CheckAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
			if($_SESSION["can_read"]==false) { 
				redirect("message/index/er_auth"); 
			}

			$post = $this->PopulatePost();
			$this->ViewCampaignPlanList();		
			$params = array(); 
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "ENAMPILKAN MENU PERENCANAAN CAMPAIGN";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU ENAMPILKAN MENU PERENCANAAN CAMPAIGN";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

		}

		public function ViewCampaignPlanList()
		{
			$data = array();
	        //$campaignPlans = $this->CampaignPlanModel->GetList();
	        //$data["CampaignPlans"] = $campaignPlans;
			$this->RenderView('MsCampaignPlanList',$data);
		}

		public function GetCampaignPlanList()
		{
			$post = $this->PopulatePost();

			$CampaignPlanList = $this->CampaignPlanModel->GetList($post);
			if (count($CampaignPlanList)>0) {
				echo(json_encode(array("result"=>"SUCCESS", "list"=>$CampaignPlanList)));
			} else {
				echo(json_encode(array("result"=>"FAILED", "list"=>array())));
			}
		}

		
		function view()
		{	
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'MENAMPILKAN MENU PERENCANAAN CAMPAIGN - VIEW');
			$trxID = urldecode($this->input->get("trxid"));
			$this->viewCampaignPlan($trxID);
			$this->Logs_Update($LogDate,'SUCCESS','MENAMPILKAN MENU PERENCANAAN CAMPAIGN - VIEW');
		}

		function viewFromDashboard()
		{	
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'MENAMPILKAN MENU PERENCANAAN CAMPAIGN - VIEW FROM DASHBOARD');
			$trxID = urldecode($this->input->get("trxid"));
			$this->viewCampaignPlanFromDashboard($trxID);
			$this->Logs_Update($LogDate,'SUCCESS','MENAMPILKAN MENU PERENCANAAN CAMPAIGN - VIEW FROM DASHBOARD');
		}

		function viewCampaignPlan($trxID) 
		{
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'MENAMPILKAN MENU PERENCANAAN CAMPAIGN - VIEW CAMPAIGN PLAN');

			$data = array();
			$resData = array();			
			$planHD = json_decode($this->CampaignPlanModel->GetTransaksiDetail($trxID));
			//die(json_encode($planHD));
			$wilayahs = json_decode($this->CampaignPlanModel->GetTransaksiWilayahInclude($trxID));
			$ProductBreakdowns = array();

			$data = array();
			$arrayBarang = array();
			$array_barang = array();
			$barangs = array();

			foreach($planHD as $hd) {
				array_push($barangs, $hd->ProductID);

				$breakdowns = array();
				$GetBreakdowns = $this->CampaignPlanModel->GetBreakdowns($trxID, $hd->ProductID);
				foreach($GetBreakdowns as $b) {
					array_push($breakdowns, array("KodeBarang"=>$hd->ProductID, 
								"Wilayah"=>trim($b->Wilayah), "Wil"=>$this->replaceSymbolChars(trim($b->Wilayah)), 
								"Kd_Lokasi"=>$b->Kd_Lokasi,  "IdxBreakdown"=>0, 
								"AvgJual"=>$b->AvgJual, "TotalAvgAll"=>$b->TotalAvgAll,
								"Persentase"=>$b->Persentase, "TotalQtyCampaign"=>0, 
								"TotalQty"=>(($b->Qty==null)?0:$b->Qty), "IsDraft"=>0, 
								"IsSelected"=>1
					));
				}
				array_push($ProductBreakdowns, array("KodeBarang"=>$hd->ProductID, "KdBrg"=>$this->replaceSymbolChars(trim($hd->ProductID)),
													 "QtyAverage"=>$hd->QtyAverage, "TotalQty"=>$hd->TotalQty, "JumlahHari"=>$hd->JumlahHari,
													 "Breakdown_Per_Wilayah"=>$breakdowns));

				$KdBrg = $this->replaceSymbolChars(trim($hd->ProductID));
				$array_barang = array("Kd_Brg"=>trim($hd->ProductID), "KdBrg"=>$KdBrg,  
					"Jns_Trx"=>"", "Nm_Trx"=>"", "Flag"=>"", 
					"Total_Hari"=>0, "Total_Jual"=>0, "Avg"=>$hd->QtyAverage, "Total_Avg"=>$hd->TotalQty,
					"Total_Hari_Plan"=>$hd->JumlahHari, "Breakdown_Per_Wilayah"=>$breakdowns, "Id"=>0, 
					"IsDraft"=>0, "IsSelected"=>1);
				array_push($arrayBarang, $array_barang);
			}

			$result["barang"] = $barangs;


			// $previousCampaigns = $this->CampaignPlanModel->GetPreviousCampaigns($trxID);
			// foreach($previousCampaigns as $c) {
			// 	if ($c->IsSelected==1) {
			// 		// $breakdowns = array();
			// 		// $GetBreakdowns = $this->CampaignPlanModel->GetBreakdowns($trxID, $c->id);
			// 		// foreach($GetBreakdowns as $b) {
			// 		// 	array_push($breakdowns, array("Wilayah"=>trim($b->Kota), "Wil"=>$this->replaceSymbolChars(trim($b->Kota)), 
			// 		// 				"Kd_Lokasi"=>$b->Kd_Lokasi, 
			// 		// 				"AvgJual"=>$b->Avg_Jual, "TotalAvgAll"=>$b->Total_Avg_Jual,
			// 		// 				"Persentase"=>$b->Persentase_Jual, "TotalQtyCampaign"=>$b->Total_Qty_Campaign, 
			// 		// 				"TotalQty"=>$b->Total_Qty, "IsDraft"=>(($b->IsDraft==null)?0:$b->IsDraft), 
			// 		// 				"IsSelected"=>(($b->IsSelected==null)?0:$b->IsSelected)));
			// 		// }

			// 		$KdBrg = $this->replaceSymbolChars(trim($c->ProductID));
			// 		$array_barang = array("Kd_Brg"=>trim($c->ProductID), "KdBrg"=>$KdBrg,  
			// 			"Jns_Trx"=>trim($c->JnsTrx), "Nm_Trx"=>trim($c->NmTrx), "Flag"=>$c->Flag, 
			// 			"Total_Hari"=>$c->TotalHari, "Total_Jual"=>$c->TotalJual, "Avg"=>$c->AvgJual, "Total_Avg"=>$c->TotalQty,
			// 			"Total_Hari_Plan"=>$c->TotalHariPlan, "Breakdown_Per_Wilayah"=>$breakdowns, "Id"=>$c->id, 
			// 			"IsDraft"=>(($c->IsDraft==null)?0:$c->IsDraft), 
			// 			"IsSelected"=>(($c->IsSelected==null)?0:$c->IsSelected));
			// 		array_push($arrayBarang, $array_barang);
			// 	}
			// }

			$result["status"] = "view";
			$result['campaigns'] = $arrayBarang;
			$result['CampaignID'] = $trxID;
			$result["ProductBreakdowns"] = $ProductBreakdowns;
			$result['headers'] = $planHD;
			$result["wilayahs"] = $wilayahs;	
			$result['approval'] = $this->approvaldefault;		

			$this->Logs_Update($LogDate,'SUCCESS','MENAMPILKAN MENU PERENCANAAN CAMPAIGN - VIEW CAMPAIGN PLAN');

			$this->RenderView('MsCampaignPlanView', $result);
		}

		function viewCampaignPlanFromDashboard($trxID) 
		{
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'MENAMPILKAN MENU PERENCANAAN CAMPAIGN - VIEW CAMPAIGN PLAN FROM DASHBOARD');
			$data = array();
			$resData = array();			
			$planHD = json_decode($this->CampaignPlanModel->GetTransaksiDetail($trxID));
			//die(json_encode($planHD));
			$wilayahs = json_decode($this->CampaignPlanModel->GetTransaksiWilayahInclude($trxID));
			$ProductBreakdowns = array();

			$data = array();
			$arrayBarang = array();
			$array_barang = array();
			$barangs = array();

			foreach($planHD as $hd) {
				array_push($barangs, $hd->ProductID);

				$breakdowns = array();
				$GetBreakdowns = $this->CampaignPlanModel->GetBreakdowns($trxID, $hd->ProductID);
				foreach($GetBreakdowns as $b) {
					array_push($breakdowns, array("KodeBarang"=>$hd->ProductID, 
								"Wilayah"=>trim($b->Wilayah), "Wil"=>$this->replaceSymbolChars(trim($b->Wilayah)), 
								"Kd_Lokasi"=>$b->Kd_Lokasi,  "IdxBreakdown"=>0, 
								"AvgJual"=>$b->AvgJual, "TotalAvgAll"=>$b->TotalAvgAll,
								"Persentase"=>$b->Persentase, "TotalQtyCampaign"=>0, 
								"TotalQty"=>(($b->Qty==null)?0:$b->Qty), "IsDraft"=>0, 
								"IsSelected"=>1
					));
				}
				array_push($ProductBreakdowns, array("KodeBarang"=>$hd->ProductID, "KdBrg"=>$this->replaceSymbolChars(trim($hd->ProductID)),
													 "QtyAverage"=>$hd->QtyAverage, "TotalQty"=>$hd->TotalQty, "JumlahHari"=>$hd->JumlahHari,
													 "Breakdown_Per_Wilayah"=>$breakdowns));

				$KdBrg = $this->replaceSymbolChars(trim($hd->ProductID));
				$array_barang = array("Kd_Brg"=>trim($hd->ProductID), "KdBrg"=>$KdBrg,  
					"Jns_Trx"=>"", "Nm_Trx"=>"", "Flag"=>"", 
					"Total_Hari"=>0, "Total_Jual"=>0, "Avg"=>$hd->QtyAverage, "Total_Avg"=>$hd->TotalQty,
					"Total_Hari_Plan"=>$hd->JumlahHari, "Breakdown_Per_Wilayah"=>$breakdowns, "Id"=>0, 
					"IsDraft"=>0, "IsSelected"=>1);
				array_push($arrayBarang, $array_barang);
			}

			$result["barang"] = $barangs;


			// $previousCampaigns = $this->CampaignPlanModel->GetPreviousCampaigns($trxID);
			// foreach($previousCampaigns as $c) {
			// 	if ($c->IsSelected==1) {
			// 		// $breakdowns = array();
			// 		// $GetBreakdowns = $this->CampaignPlanModel->GetBreakdowns($trxID, $c->id);
			// 		// foreach($GetBreakdowns as $b) {
			// 		// 	array_push($breakdowns, array("Wilayah"=>trim($b->Kota), "Wil"=>$this->replaceSymbolChars(trim($b->Kota)), 
			// 		// 				"Kd_Lokasi"=>$b->Kd_Lokasi, 
			// 		// 				"AvgJual"=>$b->Avg_Jual, "TotalAvgAll"=>$b->Total_Avg_Jual,
			// 		// 				"Persentase"=>$b->Persentase_Jual, "TotalQtyCampaign"=>$b->Total_Qty_Campaign, 
			// 		// 				"TotalQty"=>$b->Total_Qty, "IsDraft"=>(($b->IsDraft==null)?0:$b->IsDraft), 
			// 		// 				"IsSelected"=>(($b->IsSelected==null)?0:$b->IsSelected)));
			// 		// }

			// 		$KdBrg = $this->replaceSymbolChars(trim($c->ProductID));
			// 		$array_barang = array("Kd_Brg"=>trim($c->ProductID), "KdBrg"=>$KdBrg,  
			// 			"Jns_Trx"=>trim($c->JnsTrx), "Nm_Trx"=>trim($c->NmTrx), "Flag"=>$c->Flag, 
			// 			"Total_Hari"=>$c->TotalHari, "Total_Jual"=>$c->TotalJual, "Avg"=>$c->AvgJual, "Total_Avg"=>$c->TotalQty,
			// 			"Total_Hari_Plan"=>$c->TotalHariPlan, "Breakdown_Per_Wilayah"=>$breakdowns, "Id"=>$c->id, 
			// 			"IsDraft"=>(($c->IsDraft==null)?0:$c->IsDraft), 
			// 			"IsSelected"=>(($c->IsSelected==null)?0:$c->IsSelected));
			// 		array_push($arrayBarang, $array_barang);
			// 	}
			// }

			$result["status"] = "view";
			$result['campaigns'] = $arrayBarang;
			$result['CampaignID'] = $trxID;
			$result["ProductBreakdowns"] = $ProductBreakdowns;
			$result['headers'] = $planHD;
			$result["wilayahs"] = $wilayahs;	
			$result['approval'] = 1;		

			$this->Logs_Update($LogDate,'SUCCESS','MENAMPILKAN MENU PERENCANAAN CAMPAIGN - VIEW CAMPAIGN PLAN FROM DASHBOARD');
			$this->RenderView('MsCampaignPlanView', $result);
		}

		public function add()
		{

			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'PROSES PERENCANAAN CAMPAIGN - PROCESS ADD');

			$CheckAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
			$post = $this->PopulatePost();
			
			$data = array();
			
			$data = [
			'api' => 'APITES'
			];
			
			//die($this->API_URL . "/CampaignPlan/GetwilayahInclude");
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $this->API_URL . "/CampaignPlan/GetwilayahInclude",
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
				//$data["wilayah"] = json_decode($hasil->data);
				$ListWilayah = json_decode($hasil->data);
				foreach($ListWilayah as $w) {
					$w->Wil = $this->ReplaceSymbolChars($w->Kota);
				}
				//die(json_encode($ListWilayah));
				$data["wilayah"] = $ListWilayah;

			} else {
				$data["wilayah"] = "";
			}
			
			$divisi = json_decode(file_get_contents($this->API_URL . "/MsBarang/GetDivisiList?api=APITES"), true);
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

			//die($this->API_URL."/MsBarang/BarangListExcludeHadiahGET?api=APITES&divisi=MIYAKO");
			$listProduk = json_decode(file_get_contents($this->API_URL."/MsBarang/BarangListExcludeHadiahGET?api=APITES&divisi=MIYAKO"),true);
			//die(json_encode($listProduk));
			if ($listProduk["result"]=="sukses") {
				$BRG = $listProduk["data"];
				$data["list_miyako"] = $BRG;
				for($i=0;$i<count($BRG);$i++) {
					array_push($data["miyako"], trim($BRG[$i]["KD_BRG"]));
				}
			}
			//die($this->API_URL."/MsBarang/BarangListExcludeHadiahGET?api=APITES&divisi=MIYAKOKR");
			$listProduk = json_decode(file_get_contents($this->API_URL."/MsBarang/BarangListExcludeHadiahGET?api=APITES&divisi=MICOOK"),true);
			if ($listProduk["result"]=="sukses") {
				$BRG = $listProduk["data"];
				$data["list_micook"] = $BRG;
				for($i=0;$i<count($BRG);$i++) {
					array_push($data["micook"], trim($BRG[$i]["KD_BRG"]));
				}
			}
			$listProduk = json_decode(file_get_contents($this->API_URL."/MsBarang/BarangListExcludeHadiahGET?api=APITES&divisi=RINNAI"),true);
			if ($listProduk["result"]=="sukses") {
				$BRG = $listProduk["data"];
				$data["list_rinnai"] = $BRG;
				for($i=0;$i<count($BRG);$i++) {
					array_push($data["rinnai"], trim($BRG[$i]["KD_BRG"]));
				}
			}
			$listProduk = json_decode(file_get_contents($this->API_URL."/MsBarang/BarangListExcludeHadiahGET?api=APITES&divisi=SHIMIZU"),true);
			if ($listProduk["result"]=="sukses") {
				$BRG = $listProduk["data"];
				$data["list_shimizu"] = $BRG;
				for($i=0;$i<count($BRG);$i++) {
					array_push($data["shimizu"], trim($BRG[$i]["KD_BRG"]));
				}
			}
			$listProduk = json_decode(file_get_contents($this->API_URL."/MsBarang/BarangListExcludeHadiahGET?api=APITES&divisi=".urlencode("CO&SANITARY")),true);
			if ($listProduk["result"]=="sukses") {
				$BRG = $listProduk["data"];
				$data["list_cosanitary"] = $BRG;
				for($i=0;$i<count($BRG);$i++) {
					array_push($data["cosanitary"], trim($BRG[$i]["KD_BRG"]));
				}
			}

			$data["isDraft"] = 1;
			$data["mode"] = "add";
			$data["planHD"] = array();
			$data["wilayahInclude"] = array();

			//die(json_encode($data));
			$this->Logs_Update($LogDate,'SUCCESS','PROSES PERENCANAAN CAMPAIGN - PROCESS ADD');
			$this->RenderView('MsCampaignPlanView1', $data);
		}

		public function edit()
		{
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'PROSES PERENCANAAN CAMPAIGN - PROCESS EDIT');

			$CheckAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
			$post = $this->PopulatePost();
			
			$trxID = urldecode($this->input->get("trxid"));

			$resData = array();			
			$planHD = json_decode($this->CampaignPlanModel->GetTransaksiDetail($trxID));
			// die(json_encode($planHD));

			if(count($planHD)>0){
				$wilayahInclude = json_decode($this->CampaignPlanModel->GetTransaksiWilayahInclude($trxID));
				foreach($wilayahInclude as $w) {
					$w->Wil = $this->ReplaceSymbolChars($w->Wilayah);
				}
				$planHD = json_decode($this->CampaignPlanModel->CheckItemID($trxID, $planHD));


				//die(json_encode($planHD));

				$data = [
				'api' => 'APITES'
				];
				
				$curl = curl_init();
				curl_setopt_array($curl, array(
				CURLOPT_URL => $this->API_URL . "/CampaignPlan/GetListWilayah",
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
				//die($planHD);
				$data["planHD"] = $planHD;
				$data["wilayahInclude"] = $wilayahInclude;


				if ($hasil->result == "sukses") {
					$ListWilayah = json_decode($hasil->data);
					foreach($ListWilayah as $w) {
						$w->Wil = $this->ReplaceSymbolChars($w->Kota);
					}
					//die(json_encode($ListWilayah));
					$data["wilayah"] = $ListWilayah;
				} else {
					$data["wilayah"] = "";
				}
				
				$divisi = json_decode(file_get_contents($this->API_URL . "/MsBarang/GetDivisiList?api=APITES"), true);
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

				//die($this->API_URL."/MsBarang/BarangListExcludeHadiahGET?api=APITES&divisi=MIYAKO");
				$listProduk = json_decode(file_get_contents($this->API_URL."/MsBarang/BarangListExcludeHadiahGET?api=APITES&divisi=MIYAKO"),true);
				//die(json_encode($listProduk));
				if ($listProduk["result"]=="sukses") {
					$BRG = $listProduk["data"];
					$data["list_miyako"] = $BRG;
					for($i=0;$i<count($BRG);$i++) {
						array_push($data["miyako"], trim($BRG[$i]["KD_BRG"]));
					}
				}
				$listProduk = json_decode(file_get_contents($this->API_URL."/MsBarang/BarangListExcludeHadiahGET?api=APITES&divisi=MICOOK"),true);
				if ($listProduk["result"]=="sukses") {
					$BRG = $listProduk["data"];
					$data["list_micook"] = $BRG;
					for($i=0;$i<count($BRG);$i++) {
						array_push($data["micook"], trim($BRG[$i]["KD_BRG"]));
					}
				}
				$listProduk = json_decode(file_get_contents($this->API_URL."/MsBarang/BarangListExcludeHadiahGET?api=APITES&divisi=RINNAI"),true);
				if ($listProduk["result"]=="sukses") {
					$BRG = $listProduk["data"];
					$data["list_rinnai"] = $BRG;
					for($i=0;$i<count($BRG);$i++) {
						array_push($data["rinnai"], trim($BRG[$i]["KD_BRG"]));
					}
				}
				$listProduk = json_decode(file_get_contents($this->API_URL."/MsBarang/BarangListExcludeHadiahGET?api=APITES&divisi=SHIMIZU"),true);
				if ($listProduk["result"]=="sukses") {
					$BRG = $listProduk["data"];
					$data["list_shimizu"] = $BRG;
					for($i=0;$i<count($BRG);$i++) {
						array_push($data["shimizu"], trim($BRG[$i]["KD_BRG"]));
					}
				}
				$listProduk = json_decode(file_get_contents($this->API_URL."/MsBarang/BarangListExcludeHadiahGET?api=APITES&divisi=".urlencode("CO&SANITARY")),true);
				if ($listProduk["result"]=="sukses") {
					$BRG = $listProduk["data"];
					$data["list_cosanitary"] = $BRG;
					for($i=0;$i<count($BRG);$i++) {
						array_push($data["cosanitary"], trim($BRG[$i]["KD_BRG"]));
					}
				}

				$data["isDraft"] = $this->CampaignPlanModel->checkDraft($trxID);
				$data["mode"] = "edit";
			}
			
			//die(json_encode($data));
			$this->Logs_Update($LogDate,'SUCCESS','PROSES PERENCANAAN CAMPAIGN - PROCESS EDIT');
			$this->RenderView('MsCampaignPlanView1', $data);
		}

		public function SaveDraft()
		{
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'PROSES PERENCANAAN CAMPAIGN - PROCESS SAVE DRAFT');
			$data = array();
			$post = $this->PopulatePost();
			$simpanCampaignPlan = $this->CampaignPlanModel->saveDraft($post);
			echo(json_encode($simpanCampaignPlan));
			$this->Logs_Update($LogDate,'SUCCESS','PROSES PERENCANAAN CAMPAIGN - PROCESS SAVE DRAFT');
		}

		public function SaveDraftWilayah()
		{
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'PROSES PERENCANAAN CAMPAIGN - PROCESS SAVE DRAFT WILAYAH');

			$data = array();
			$post = $this->PopulatePost();
			if ($post["wilayah"]=="ALL") {
				$data = array();
				
				$data = [
				'api' => 'APITES'
				];
				
				$curl = curl_init();
				curl_setopt_array($curl, array(
				CURLOPT_URL => $this->API_URL . "/CampaignPlan/GetwilayahInclude",
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
						$simpanCampaignPlan = $this->CampaignPlanModel->saveDraftWilayah($post);
					}
					$this->Logs_Update($LogDate,'SUCCESS','PROSES PERENCANAAN CAMPAIGN - PROCESS SAVE DRAFT WILAYAH');
				} else {
					$this->Logs_Update($LogDate,'FAILED - '.'PROSES PERENCANAAN CAMPAIGN - PROCESS SAVE DRAFT WILAYAH');
					$simpanCampaignPlan = array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>'', "errCode"=>$ERR_CODE, "lastQuery"=>"Gagal Ambil List Wilayah");
				}
			} else {
				$simpanCampaignPlan = $this->CampaignPlanModel->saveDraftWilayah($post);
				$this->Logs_Update($LogDate,'SUCCESS','PROSES PERENCANAAN CAMPAIGN - PROCESS SAVE DRAFT WILAYAH');
			}
			echo(json_encode($simpanCampaignPlan));
		}

		public function RemoveDraft()
		{
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'PROSES PERENCANAAN CAMPAIGN - PROCESS REMOVE DRAFT');
			$data = array();
			$post = $this->PopulatePost();
			$this->Logs_Update($LogDate,'SUCCESS','PROSES PERENCANAAN CAMPAIGN - PROCESS REMOVE DRAFT');
			$removeDraft = $this->CampaignPlanModel->removeDraft($post);
			echo(json_encode($removeDraft));
		}

		public function RemoveItem()
		{
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'PROSES PERENCANAAN CAMPAIGN - PROCESS REMOVE ITEM');
			$data = array();
			$post = $this->PopulatePost();
			$this->Logs_Update($LogDate,'SUCCESS','PROSES PERENCANAAN CAMPAIGN - PROCESS REMOVE ITEM');
			$removeItem = $this->CampaignPlanModel->removeItem($post);
			echo(json_encode($removeItem));
		}

		public function RemoveDrafts()
		{
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'PROSES PERENCANAAN CAMPAIGN - PROCESS REMOVE DRAFTS');

			$data = array();
			$post = $this->PopulatePost();
			$this->Logs_Update($LogDate,'SUCCESS','PROSES PERENCANAAN CAMPAIGN - PROCESS REMOVE DRAFTS');
			$removeCampaignPlan = $this->CampaignPlanModel->removeDrafts($post["kode_plan"]);
			echo(json_encode($removeCampaignPlan));
		}

		public function CancelPlan()
		{
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'PROSES PERENCANAAN CAMPAIGN - PROCESS CANCEL PLAN');

			$data = array();
			$post = $this->PopulatePost();

			$params = array();
			$params['ApprovalType'] = $this->approvaltype;
			$params["RequestNo"] = $post["kode_plan"];
			$params["CancelledBy"] = $_SESSION["logged_in"]["employeeid"];
			$params["CancelledByName"] = $_SESSION["logged_in"]["username"];
			$params["CancelledDate"] = date('Y-m-d H:i:s');
			$params["CancelledNote"] = $post["alasan"];
			$params["CancelledByEmail"] = $_SESSION["logged_in"]["useremail"];

			$this->Logs_Update($LogDate,'SUCCESS','PROSES PERENCANAAN CAMPAIGN - PROCESS REMOVE PLAN');

			$this->approval->cancelbyajax($params);

			$cancelPlan = $this->CampaignPlanModel->cancelPlan($post);
			echo(json_encode($cancelPlan));

		}

		public function addStep2()
		{
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'PROSES PERENCANAAN CAMPAIGN - PROCESS CANCEL ADD STEP 2');

			$post = $this->PopulatePost();
			$post['api'] = 'APITES';
			
			$ProcessDraft = $this->CampaignPlanModel->ProcessDraft($post);
			$SimpanWilayah = $this->CampaignPlanModel->ProcessDraftWilayah($post);
			$this->continueStep2($post);
			$this->Logs_Update($LogDate,'SUCCESS','PROSES PERENCANAAN CAMPAIGN - PROCESS REMOVE PLAN 2');
		}

		public function continueStep2($post)
		{
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'PROSES PERENCANAAN CAMPAIGN - PROCESS CONTINUE STEP 2');

			$trxID = $post["txtKodeCampaign"];
			$planHD = json_decode($this->CampaignPlanModel->GetTransaksiDetail($trxID));
			$wilayahs = json_decode($this->CampaignPlanModel->GetTransaksiWilayahInclude($trxID));
			// echo $trxID."<br>";
			// echo(json_encode($planHD)."<br><br>");
			// die(json_encode($wilayahs)."<br><br>");

			if (!isset($post["filterBarang"])) {
				$barang = array();
				foreach($planHD as $hd) {
					array_push($barang, strtoupper($hd->ProductID));
				}
				$post["filterBarang"] = $barang;
			}

			$data = array();
			$data = [
				'value' => $post
			];
			// die(http_build_query($data));
			// die(json_encode($data));
			$ProductBreakdowns = array();


			// Cek apakah Ada Selisih Jumlah Wilayah/Produk Antara Yang Sudah Tersimpan dengan Yang Baru Dilempar dari Page View1
			// Jika ada Selisih, CheckDraftDT akan return False
			// Maka Sistem akan request Perhitungan Ulang ke Bhakti
			$CheckDraftDT = $this->CampaignPlanModel->CheckDraftDT($trxID);
			// die(json_encode($CheckDraftDT));

			if ($CheckDraftDT==false) {
				// echo("DraftDT False<br><br>");
				//echo(json_encode($post)."<br><br>");

				//Request Perhitungan PreviousCampaign dan Persentase Per Wilayah Ulang ke Bhakti (WebAPI)
				$curl = curl_init();
				curl_setopt_array($curl, array(
				CURLOPT_URL => $this->API_URL . "/CampaignPlan/GetAverageCampaign",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => http_build_query($data),
				));
				
				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);

				// die($response."<br>");
				$response = json_decode($response);

				$ress = array();
				$ressHeader = array();

				//die($response);
				foreach ($response->data as $res) {
					$ress[] = json_decode($res);
				}
				
				
				foreach ($response->header as $res) {
					$ressHeader[] = $res;
				}
				
				if ($err) {
					echo "cURL Error #:" . $err;
				} else {

					//$result['data'] = $ress;
					//$result['bedahari'] = $response->bedahari;
					$barangs = $response->barang;
					$result['barang'] = $barangs;
					//echo(json_encode($barangs));
					
					$campaigns = $response->campaign;	//$campaigns menampung kombinasi barang-campaign unik 
					$avgsales = $response->avgsales;
					// echo(json_encode($avgsales)."<br><br>");die;
					$save_avgSales = $this->CampaignPlanModel->SaveAverageSales($trxID, $avgsales);

					//die(json_encode($campaigns));

					$arrayBarang = array();
					foreach($planHD as $hd) {
						// echo(json_encode($hd)."<br><br>");
						// die(json_encode($hd));
						$breakdowns = array();
						$GetBreakdowns = $this->CampaignPlanModel->GetBreakdowns($trxID, $hd->ProductID);
						foreach($GetBreakdowns as $b) {
							// echo(json_encode(array("KodeBarang"=>$hd->ProductID, 
							// 			"Wilayah"=>trim($b->Wilayah), "Wil"=>$this->replaceSymbolChars(trim($b->Wilayah)), 
							// 			"Kd_Lokasi"=>$b->Kd_Lokasi,  "IdxBreakdown"=>0, 
							// 			"AvgJual"=>$b->AvgJual, "TotalAvgAll"=>$b->TotalAvgAll,
							// 			"Persentase"=>$b->Persentase, "TotalQtyCampaign"=>0, 
							// 			"TotalQty"=>(($b->Qty==null)?0:$b->Qty), "IsDraft"=>0, 
							// 			"IsSelected"=>0 
							// ))."<br><br>");

							array_push($breakdowns, array("KodeBarang"=>$hd->ProductID, 
										"Wilayah"=>trim($b->Wilayah), "Wil"=>$this->replaceSymbolChars(trim($b->Wilayah)), 
										"Kd_Lokasi"=>$b->Kd_Lokasi,  "IdxBreakdown"=>0, 
										"AvgJual"=>$b->AvgJual, "TotalAvgAll"=>$b->TotalAvgAll,
										"Persentase"=>$b->Persentase, "TotalQtyCampaign"=>0, 
										"TotalQty"=>(($b->Qty==null)?0:$b->Qty), "IsDraft"=>0, 
										"IsSelected"=>0 
							));

							
						}
						
						// echo(json_encode(array("KodeBarang"=>$hd->ProductID, "KdBrg"=>$this->replaceSymbolChars(trim($hd->ProductID)),
						// 									 "QtyAverage"=>$hd->QtyAverage, "TotalQty"=>$hd->TotalQty, "JumlahHari"=>$hd->JumlahHari,
						// 									 "Breakdown_Per_Wilayah"=>$breakdowns))."<br><br>");

						array_push($ProductBreakdowns, array("KodeBarang"=>$hd->ProductID, "KdBrg"=>$this->replaceSymbolChars(trim($hd->ProductID)),
															 "QtyAverage"=>$hd->QtyAverage, "TotalQty"=>$hd->TotalQty, "JumlahHari"=>$hd->JumlahHari,
															 "Breakdown_Per_Wilayah"=>$breakdowns));

						foreach($campaigns as $camp) {
							foreach(json_decode($camp) as $c) {
								//die(json_encode($c));
								if (strtoupper($hd->ProductID)==strtoupper($c->Kd_Brg)) {
									$TotalJual = 0;
									foreach($ress as $rs) {
									 	foreach($rs as $r) {
									 		if (strtoupper(trim($r->Kd_Brg))==strtoupper(trim($c->Kd_Brg)) && 
									 			strtoupper(trim($r->Jns_Trx))==strtoupper(trim($c->Jns_Trx))) {
									 			$TotalJual = $TotalJual + (($r->Total_Jual==null)? 0: $r->Total_Jual); 
									 		}
									 	}
									}

									if ($c->TotalHari==0 || $c->TotalHari==null) {
										$c->TotalHari = 1;
										$Avg = $TotalJual;
									} else {
										$Avg = ROUND($TotalJual/$c->TotalHari);
									}

									$TotalAvg = $hd->JumlahHari * $Avg;

									$breakdowns = array();
												

									$KdBrg = $this->replaceSymbolChars(trim($c->Kd_Brg));

									$array_barang = array("Kd_Brg"=>trim($c->Kd_Brg), "KdBrg"=>$KdBrg,  
													"Jns_Trx"=>trim($c->Jns_Trx), "Nm_Trx"=>trim($c->Nm_Trx), "Flag"=>$c->Flag, 
													"Total_Hari"=>$c->TotalHari, "Total_Jual"=>$TotalJual, "Avg"=>$Avg, "Total_Avg"=>$TotalAvg,
													"Total_Hari_Plan"=>$hd->JumlahHari, "Breakdown_Per_Wilayah"=>array(),
													"IsDraft"=>0, "IsSelected"=>0);
									//echo(json_encode($array_barang)."<br>");
									$save_campaign = $this->CampaignPlanModel->SavePreviousCampaigns($trxID, $array_barang);
									$array_barang["Id"] = $save_campaign;
									array_push($arrayBarang, $array_barang);
								}
							}
						}
					}

					$result['status'] = 'baru';					
				}
			} else {
				// echo("DraftDT True<br><br>");
				// die(json_encode($planHD));
				//echo("here");
				$barangs = array();
				foreach($planHD as $hd) {
					array_push($barangs, $hd->ProductID);
		
					$breakdowns = array();
					$GetBreakdowns = $this->CampaignPlanModel->GetBreakdowns($trxID, $hd->ProductID);
					foreach($GetBreakdowns as $b) {
						array_push($breakdowns, array("KodeBarang"=>$hd->ProductID, 
									"Wilayah"=>trim($b->Wilayah), "Wil"=>$this->replaceSymbolChars(trim($b->Wilayah)), 
									"Kd_Lokasi"=>$b->Kd_Lokasi,  "IdxBreakdown"=>0, 
									"AvgJual"=>$b->AvgJual, "TotalAvgAll"=>$b->TotalAvgAll,
									"Persentase"=>$b->Persentase, "TotalQtyCampaign"=>0, 
									"TotalQty"=>(($b->Qty==null)?0:$b->Qty), "IsDraft"=>0, 
									"IsSelected"=>0 
						));
					}
					array_push($ProductBreakdowns, array("KodeBarang"=>$hd->ProductID, "KdBrg"=>$this->replaceSymbolChars(trim($hd->ProductID)),
														 "QtyAverage"=>$hd->QtyAverage, "TotalQty"=>$hd->TotalQty, "JumlahHari"=>$hd->JumlahHari,
														 "Breakdown_Per_Wilayah"=>$breakdowns));
				}

				$result["barang"] = $barangs;
				$arrayBarang = array();
				$array_barang = array();

				$previousCampaigns = $this->CampaignPlanModel->GetPreviousCampaigns($trxID);
				foreach($previousCampaigns as $c) {
					$KdBrg = $this->replaceSymbolChars(trim($c->ProductID));
					$array_barang = array("Kd_Brg"=>trim($c->ProductID), "KdBrg"=>$KdBrg,  
						"Jns_Trx"=>trim($c->JnsTrx), "Nm_Trx"=>trim($c->NmTrx), "Flag"=>$c->Flag, 
						"Total_Hari"=>$c->TotalHari, "Total_Jual"=>$c->TotalJual, "Avg"=>$c->AvgJual, "Total_Avg"=>$c->TotalQty,
						"Total_Hari_Plan"=>$c->JumlahHari, "Breakdown_Per_Wilayah"=>array(), "Id"=>$c->id, 
						"IsDraft"=>(($c->IsDraft==null)?0:$c->IsDraft), 
						"IsSelected"=>(($c->IsSelected==null)?0:$c->IsSelected));
					array_push($arrayBarang, $array_barang);
				}

				$result["status"] = "draft";
			}
			// die(json_encode($ProductBreakdowns));

			$result['campaigns'] = $arrayBarang;
			$result['CampaignID'] = $trxID;
			$result["ProductBreakdowns"] = $ProductBreakdowns;
			$result['headers'] = $planHD;
			$result["wilayahs"] = $wilayahs;
			// die(json_encode($result));
			$this->Logs_Update($LogDate,'SUCCESS','PROSES PERENCANAAN CAMPAIGN - PROCESS CONTINUE STEP 2');
			$this->RenderView('MsCampaignPlanView2', $result);
		}

		public function ReplaceSymbolChars($string="") {
			$string = str_replace(" ", "", $string);
			$string = str_replace(",", "_", $string);
			$string = str_replace(".", "_", $string);
			$string = str_replace(":", "_", $string);
			$string = str_replace(";", "_", $string);
			$string = str_replace("/", "_", $string);
			$string = str_replace("+", "_", $string);
			$string = str_replace("-", "_", $string);
			$string = str_replace("'", "_", $string);
			$string = str_replace("&", "_", $string);
			$string = str_replace(")", "_", $string);
			$string = str_replace("(", "_", $string);
			$string = str_replace("[", "_", $string);
			$string = str_replace("<", "_", $string);
			$string = str_replace(">", "_", $string);
			$string = str_replace("!", "_", $string);
			$string = str_replace("?", "_", $string);
			$string = str_replace("$", "_", $string);
			$string = str_replace("%", "_", $string);
			$string = str_replace("@", "_", $string);
			$string = str_replace("#", "_", $string);
			$string = str_replace("*", "_", $string);
			return $string;
		}


		public function SimpanCampaignPlan()
		{
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'PROSES PERENCANAAN CAMPAIGN - PROCESS SIMPAN CAMPAIGN PLAN');

			$trxID = urldecode($this->input->get("trxid"));
			$data = array();
			$post = $this->PopulatePost();
			//die(json_encode($post));

			$proses = $this->CampaignPlanModel->ProcessDraftDT($trxID, $post);
			$this->Logs_Update($LogDate,'SUCCESS','PROSES PERENCANAAN CAMPAIGN - PROCESS SIMPAN CAMPAIGN PLAN');
			redirect("CampaignPlan/view?trxid=".urlencode($trxID));
		}

		public function CreateRequestOld()
		{

			$trxID = urldecode($this->input->get("trxid"));
			$planHD = json_decode($this->CampaignPlanModel->GetTransaksiDetail($trxID));
			$Divisi = $planHD[0]->Division;

			$emailContent = $this->CreateEmailContent($trxID,$planHD);
			
			//echo($header);
			//echo($body);
			$BM = $this->SalesManagerModel->GetBrandManagersByDivisi($Divisi);
			// die($Divisi);
			// die(json_encode($BM));
			$footer = "<style> .btn { ";
			$footer.= "		border:1px solid #ccc; border-radius:10px; font-size:14px; text-align:center; padding:10px; float:left; ";
			$footer.= "		margin: 10px 10px 10px 0px; color:white; font-weight:bold; width:140px;";
			$footer.= "} </style>";
			$footer.= "<a href='".site_url("CampaignPlanApproval/Approved?campaignid=".urlencode($trxID)."&approvedby=".urlencode($BM[0]->userid))."'><div class='btn btnApprove' style='background-color:#18400b;'>APPROVE</div></a>";
			$footer.= "<a href='".site_url("CampaignPlanApproval/Rejected?campaignid=".urlencode($trxID)."&approvedby=".urlencode($BM[0]->userid))."'><div class='btn btnReject'  style='background-color:#6b0202;'>REJECT</div></a>";
			$footer.= "<div style='clear:both;'></div>";

			$this->email->clear(true);
			$this->email->from("bhaktiautoemail.noreply@bhakti.co.id", "BHAKTI.CO.ID AUTO-EMAIL");
			// $this->email->to($BM[0]->email_address);
			$this->email->to("indah@bhakti.co.id");
			$this->email->cc(array("bhaktiautoemail.noreply@bhakti.co.id", "itdev.dist@bhakti.co.id"));

			$header = "<h3>RENCANA CAMPAIGN BARU</h3>";
			$email_content = $header.$emailContent.$footer;
			//die($email_content);
			//$email_content.= "Periode : ".date("d-M-Y",strtotime($tgl1))." s/d ".date("d-M-Y", strtotime($tgl2));
			$this->email->subject("Req New Plan ".substr($planHD[0]->CampaignName,0,35));
			$this->email->message($email_content);
			$EmailDate = date("Y-m-d H:i:s");
			if ($this->email->send()) {
				$this->CampaignPlanModel->EmailRequestSent($trxID, $BM[0], true, $EmailDate);
				$data["content_html"] = '<script language="javascript">';
		        $data["content_html"].= 'alert("Plan Saved and Email Sent")';
		        $data["content_html"].= '</script>';
		        $data["content_html"].= "<script>window.close();</script>";
			} else {
				$this->CampaignPlanModel->EmailRequestSent($trxID, $BM[0], false, $EmailDate);
				$data["content_html"] = '<script language="javascript">';
		        $data["content_html"].= 'alert("Plan Saved; Email Not Sent")';
		        $data["content_html"].= '</script>';
		        $data["content_html"].= "<script>window.close();</script>";
			}
		    $this->RenderView("CustomPageResult", $data);
		}

		public function CreateRequest()
		{

			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'REQUEST PERENCANAAN CAMPAIGN');

			$trxID = urldecode($this->input->get("trxid"));
			$planHD = json_decode($this->CampaignPlanModel->GetTransaksiDetail($trxID));
			$Divisi = $planHD[0]->Division;

			$header = "<h3>RENCANA CAMPAIGN BARU</h3>";
			$emailContent = $this->CreateEmailContent($trxID,$planHD);
			// die($header.$emailContent);
			//echo($header);
			//echo($body);
			$BM = $this->SalesManagerModel->GetBrandManagersByDivisi($Divisi);
			// die($Divisi);
			// die(json_encode($BM));
			$footer = "<style> .btn { ";
			$footer.= "		border:1px solid #ccc; border-radius:10px; font-size:14px; text-align:center; padding:10px; float:left; ";
			$footer.= "		margin: 10px 10px 10px 0px; color:white; font-weight:bold; width:140px;";
			$footer.= "} </style>";
			$footer.= "<a href='".site_url("CampaignPlanApproval/Approved?campaignid=".urlencode($trxID)."&approvedby=".urlencode($BM[0]->userid))."'><div class='btn btnApprove' style='background-color:#18400b;'>APPROVE</div></a>";
			$footer.= "<a href='".site_url("CampaignPlanApproval/Rejected?campaignid=".urlencode($trxID)."&approvedby=".urlencode($BM[0]->userid))."'><div class='btn btnReject'  style='background-color:#6b0202;'>REJECT</div></a>";
			$footer.= "<div style='clear:both;'></div>";

			$this->email->clear(true);
			$this->email->from("bhaktiautoemail.noreply@bhakti.co.id", "BHAKTI.CO.ID AUTO-EMAIL");
			$this->email->to($BM[0]->email_address);
			// $this->email->to("indah@bhakti.co.id");
			$this->email->cc(array("bhaktiautoemail.noreply@bhakti.co.id", "itdev.dist@bhakti.co.id"));

			$email_content = $header.$emailContent.$footer;
			//die($email_content);
			//$email_content.= "Periode : ".date("d-M-Y",strtotime($tgl1))." s/d ".date("d-M-Y", strtotime($tgl2));
			$this->email->subject("Req New Plan ".substr($planHD[0]->CampaignName,0,35));
			$this->email->message($email_content);
			$EmailDate = date("Y-m-d H:i:s");
			if ($this->email->send()) {
				$this->CampaignPlanModel->EmailRequestSent($trxID, $BM[0], true, $EmailDate);

				$params = array();
				$params["ApprovalType"] = $this->approvaltype;
				$params["RequestNo"] = $trxID;
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
				$params["AddInfo1"] = "Kode Campaign";
				$params["AddInfo1Value"] = $planHD[0]->CampaignID;
				$params["AddInfo2"] = "Divisi";
				$params["AddInfo2Value"] = $planHD[0]->Division;
				$params["AddInfo3"] = "";
				$params["AddInfo3Value"] = "";
				$params["AddInfo4"] = "";
				$params["AddInfo4Value"] = "";
				$params["AddInfo5"] = "";
				$params["AddInfo5Value"] = "";
				$params["AddInfo6"] = "Nama Campaign";
				$params["AddInfo6Value"] = $planHD[0]->CampaignName;
				$params["AddInfo7"] = "";
				$params["AddInfo7Value"] = "";
				$params["AddInfo8"] = "";
				$params["AddInfo8Value"] = "";
				$params["AddInfo9"] = "";
				$params["AddInfo9Value"] = "";
				$params["AddInfo10"] = "";
				$params["AddInfo10Value"] = "";
				$params["AddInfo11"] = "";
				$params["AddInfo11Value"] = "";
				$params["AddInfo12"] = "";
				$params["AddInfo12Value"] = "";
				$params["ApprovalNeeded"] = "";
				$params["Priority"] = "";
				$params["ExpiryDate"] = $planHD[0]->CampaignEnd;
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
				$x = $this->approval->insert($params);

				$this->Logs_Update($LogDate,'SUCCESS','PLAN SAVED EMAIL AND EMAIL, REQUEST PERENCANAAN CAMPAIGN');
				$data["content_html"] = '<script language="javascript">';
		        $data["content_html"].= 'alert("Plan Saved and Email Sent")';
		        $data["content_html"].= '</script>';
		        $data["content_html"].= "<script>window.close();</script>";
			} else {
				$this->CampaignPlanModel->EmailRequestSent($trxID, $BM[0], false, $EmailDate);
				$this->Logs_Update($LogDate,'FAILED - PLAN SAVED EMAIL NOT SENT','CREATE REQUEST PERENCANAAN CAMPAIGN - PROCESS ADD');
				$data["content_html"] = '<script language="javascript">';
		        $data["content_html"].= 'alert("Plan Saved; Email Not Sent")';
		        $data["content_html"].= '</script>';
		        $data["content_html"].= "<script>window.close();</script>";
			}

		    $this->RenderView("CustomPageResult", $data);
		}

		public function ConfirmRequest()
		{

			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'CONFIRM REQUEST PERENCANAAN CAMPAIGN');

			$trxID = urldecode($this->input->get("trxid"));
			$planHD = json_decode($this->CampaignPlanModel->GetTransaksiDetail($trxID));
			$Divisi = $planHD[0]->Division;

			$emailContent = $this->CreateEmailContent($trxID,$planHD);			
			//echo($header);
			//echo($body);
			$BM = $this->SalesManagerModel->GetBrandManagersByDivisi($Divisi);
			// die($Divisi);
			// die(json_encode($BM));
			$style = "<style> .btn { ";
			$style.= "		border:1px solid #ccc; border-radius:10px; font-size:14px; text-align:center; padding:10px; float:left; ";
			$style.= "		margin: 10px 10px 10px 0px; color:white; font-weight:bold; width:140px;";
			$style.= "} </style>";
			
			$footer = "<a href='".site_url("CampaignPlanApproval/Approved?campaignid=".urlencode($trxID)."&approvedby=".urlencode($BM[0]->userid))."'><div class='btn btnApprove' style='background-color:#18400b;'>APPROVE</div></a>";
			$footer.= "<a href='".site_url("CampaignPlanApproval/Rejected?campaignid=".urlencode($trxID)."&approvedby=".urlencode($BM[0]->userid))."'><div class='btn btnReject'  style='background-color:#6b0202;'>REJECT</div></a>";
			$footer.= "<div style='clear:both;'></div>";

			$header = "<h3>RENCANA CAMPAIGN BARU</h3>";
			$email_content = $header.$emailContent.$style;
			$data["content_html"] = $email_content;
			$data["footer"] = $footer;
		    $this->RenderView("CustomPageResult", $data);
		    $this->Logs_Update($LogDate,'SUCCESS','CONFIRM REQUEST PERENCANAAN CAMPAIGN');
		}

		public function CreateEmailContent($trxID, $planHD){
			
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'PROSES PERENCANAAN CAMPAIGN - PROCESS CREATE EMAIL CONTENT');

			$header = "Diinput Oleh: ".$planHD[0]->CreatedBy."<br>";
			$TGLINPUT = (($planHD[0]->UpdatedDate==null)? $planHD[0]->CreatedDate:$planHD[0]->UpdatedDate);
			$header.= "Waktu Request:".date("Y-m-d H:i:s", strtotime($TGLINPUT))."<br>";
			$header.= "<hr><br>";
			$header.= "Nama Rencana : <b>".$planHD[0]->CampaignName."</b><br>";
			$header.= "Kode Rencana : <b>".$planHD[0]->CampaignID."</b><br>";
			$header.= "Divisi: <b>".$planHD[0]->Division."</b><br>";
			$header.= "Periode: <b>".date("d-M-Y",strtotime($planHD[0]->CampaignStartHD))." s/d ".date("d-M-Y",strtotime($planHD[0]->CampaignEndHD))."</b><br>";
			$header.= "JumlahHari: <b>".$planHD[0]->JumlahHariHD."</b><br>";
			$header.= "<br>";

			
			$body = "<h3>Detail Barang</h3>";
			$body.= "<style> th,td { border:1px solid #ccc; padding:3px; text-align:right; }</style>";
			$tables = array();
			$products = array();
			$product = array();

			$counter = 0;
			foreach($planHD as $p) {
				array_push($product, array("ProductID"=>$p->ProductID, "QtyAverage"=>$p->QtyAverage, "TotalQTY"=>$p->TotalQty));
			}

			$col = array();
			$x = 0;
			$ListWilayah = json_decode($this->CampaignPlanModel->GetTransaksiWilayahInclude($trxID));
			//echo(json_encode($ListWilayah)."<br><br>");
			foreach($ListWilayah as $w) {
				$col[$x][0] = $w->Wilayah;
				$x += 1;
			}

			$ProductCount = count($product);
			$CheckItemID = $this->CampaignPlanModel->CheckItemID($trxID, $planHD);

			for($i=0;$i<$ProductCount;$i++) {
			//foreach($planHD as $p) {
				//echo($product[$i]["ProductID"]."<br><br>");
				$TotalQty = 0;
				//$prevCamp = $this->CampaignPlanModel->GetSelectedPreviousCampaigns($trxID, $product[$i]["ProductID"]);
				//echo(json_encode($prevCamp)."<br><br>");
				$breakdowns = $this->CampaignPlanModel->GetBreakdowns($trxID, $product[$i]["ProductID"]);
				//echo(json_encode($breakdowns)."<br><br>");
				$y = $i+1;
				$x = 0;
				$breakdownFound = false;

				foreach($ListWilayah as $w) {
					foreach($breakdowns as $b) {
						if (trim($w->Wilayah)==trim($b->Wilayah)) {
							$col[$x][$y] = $b->Qty;
							$breakdownFound = true;
							$TotalQty += $b->Qty;
						}
					}
					if ($breakdownFound==false) {
							$col[$x][$y] = 0;
					}
					$x = $x+1;
				}
				$product[$i]["TotalQTY"] = $TotalQty;
			}

			$JmlTable = ceil($ProductCount/8);

			for($t=1;$t<=$JmlTable;$t++) {
				$start = ($t*8) - 8;
				$end = $start + 8;
				if ($end > $ProductCount) {
					$end = $ProductCount;
				}

				$body.= "<table>";
				$body.=	"	<tr>";
				$body.= "		<th width='20%'></th>";
				for ($i=$start;$i<$end;$i++) {
				$body.=	"		<th width='10%'>".$product[$i]["ProductID"]."</th>";
				}
				$body.=	"	</tr>";
				for($i=0;$i<count($col);$i++) {
				$body.=	"	<tr>";
				for($j=$start;$j<=$end;$j++) {
					if ($j==$start) {
						$body.=	"		<td style='text-align:left;'>".$col[$i][0]."</td>";
					//} else if ($j==$start) {
					//	$body.=	"		<td style='text-align:left;'>".$col[$i][0]."</td>";
					//	$body.=	"		<td>".number_format($col[$i][$j])."</td>";
					} else {
						$body.=	"		<td>".number_format($col[$i][$j])."</td>";
					}
				}
				$body.=	"	</tr>";
				}
				$body.= "	<tr>";
				$body.=	"		<td><b>TOTAL</b></td>";
				for ($i=$start;$i<$end;$i++) {
				$body.=	"		<td><b>".number_format($product[$i]["TotalQTY"])."</b></td>";
				}
				$body.= "	</tr>";
				$body.= "</table>";

			}

			$this->Logs_Update($LogDate,'SUCCESS','PROSES PERENCANAAN CAMPAIGN - PROCESS CREATE EMAIL CONTENT');
			return $header.$body;
		}
		
		public function GetBarangList()
		{
			$columnIndex = $_POST['order'][0]['column'];
			
			$post = [
			'row' => $_POST['start'],
			'rowperpage' => $_POST['length'],
			'columnName' => $_POST['columns'][1]['data'],
			'columnSortOrder' => $_POST['order'][0]['dir'],
			'searchValue' =>  $_POST['search']['value'],
			'api' => $_POST['api'],
			'divisi' => $_POST['divisi'],
			'index' => $_POST['index'],
			'draw' => $_POST['draw']
			];
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $this->API_URL . "/CampaignPlan/GetPerencanaanBarangList",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $post,
			));
			
			$response = curl_exec($curl);
			$err = curl_error($curl);
			
			curl_close($curl);
			
			if ($err) {
				echo "cURL Error #:" . $err;
				} else {
				echo $response;
			}
		}

		public function CampaignIDEncoded()
		{
			die(urlencode("ALEX.ROMY@BHAKTI.CO.ID"));
			//Approved?campaignid=SH%2F202101%2F0002&approvedby=ALEX.ROMY%40BHAKTI.CO.ID
		}
		
		
		function EmailUlang(){
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'PROSES PERENCANAAN CAMPAIGN - PROCESS EMAIL');

			$CampaignID = $this->input->post('CampaignID');
			$divisi = $this->input->post('Divisi');
			$status =  $this->EmailApproval($CampaignID, $divisi);
			
			if($status=='SUCCESS'){
				echo "Email berhasil dikirim";
				$this->Logs_Update($LogDate,'SUCCESS','PROSES PERENCANAAN CAMPAIGN - PROCESS EMAIL');
			}
			else{
				echo "Email gagal dikirim";
				$this->Logs_Update($LogDate,'FAILED - EMAIL GAGAL DIKIRIM','PROSES PERENCANAAN CAMPAIGN - PROCESS EMAIL');
			}			
		}

		public function SaveDraftDT()
		{
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'PROSES PERENCANAAN CAMPAIGN - PROCESS SAVE DRAFTDT');
			$data = array();
			$post = $this->PopulatePost();
			$simpanCampaignPlan = $this->CampaignPlanModel->saveDraftDT($post);
			echo(json_encode($simpanCampaignPlan));
			$this->Logs_Update($LogDate,'SUCCESS','PROSES PERENCANAAN CAMPAIGN - PROCESS SAVE DRAFTDT');
		}

		public function SaveDraftBreakdown()
		{
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'PROSES PERENCANAAN CAMPAIGN - PROCESS DRAFT BREAKDOWN');
			$data = array();
			$post = $this->PopulatePost();
			$simpanCampaignPlan = $this->CampaignPlanModel->saveDraftBreakdown($post);
			echo(json_encode($simpanCampaignPlan));
			$this->Logs_Update($LogDate,'SUCCESS','PROSES PERENCANAAN CAMPAIGN - PROCESS DRAFT BREAKDOWN');
		}

		public function RemoveDraftDT()
		{
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'PROSES PERENCANAAN CAMPAIGN - PROCESS REMOVE DRAFTDT');
			$data = array();
			$post = $this->PopulatePost();
			$removeDraft = $this->CampaignPlanModel->removeDraft($post);
			echo(json_encode($removeDraft));
			$this->Logs_Update($LogDate,'SUCCESS','PROSES PERENCANAAN CAMPAIGN - PROCESS REMOVE DRAFTDT');
		}		

		function num2alpha($n)
		{
			$n = $n - 1;
		    for($r = ""; $n >= 0; $n = intval($n / 26) - 1)
		        $r = chr($n%26 + 0x41) . $r;
		    return $r;
		}

		public function Excel()
		{
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'EXPORT EXCEL PERENCANAAN CAMPAIGN');

			$trxID = urldecode($this->input->get("trxid"));

			$testing = false;
			$data = array();
			$api = 'APITES';
			set_time_limit(60);
			$content_html = "";
			

			$URL=$this->API_URL."/CampaignPlan/GetDetailPlan?api=".$api."&trxid=".urlencode($trxID);
			//die($URL);
			
			$data = json_decode(file_get_contents($URL), true);
			// die(json_encode($data));

			$HD = $this->CampaignPlanModel->GetPlanHD($trxID);

			$spreadsheet = new Spreadsheet();
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet = $spreadsheet->getActiveSheet(0);

			$sheet->setTitle('BreakdownRencanaCampaign');
			$sheet->setCellValue("A1", "BREAKDOWN CAMPAIGN PLAN");
			$sheet->mergeCells("A1:C1");
			$sheet->getStyle('A1:B4')->getFont()->setSize(20);
			$sheet->setCellValue("A2", "Kode Campaign: ");
			$sheet->setCellValue("B2", $HD[0]->CampaignID);
			$sheet->setCellValue("A3", "Nama Campaign: ");
			$sheet->setCellValue("B3", $HD[0]->CampaignName);
			$sheet->setCellValue("A4", "Divisi: ");
			$sheet->setCellValue("B4", $HD[0]->Division);
			$sheet->setCellValue("A5", "Periode Plan: ");
			$sheet->setCellValue("B5", date("d-M-Y", strtotime($HD[0]->CampaignStart))." - ".date("d-M-Y", strtotime($HD[0]->CampaignEnd)));
			$sheet->getStyle('A2:B5')->getFont()->setSize(10);
			$sheet->getStyle('B2:B5')->getFont()->setBold(true);

			$Periods = $data["Periods"];
			$Periode = "";
			$col1 = "";
			$col2 = "";

			$currrow = 7;	
			$currcol = 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "KODE BARANG");
			$sheet->mergeCells($this->num2alpha($currcol).$currrow.":".$this->num2alpha($currcol).($currrow+1));
			
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "WILAYAH");
			$sheet->mergeCells($this->num2alpha($currcol).$currrow.":".$this->num2alpha($currcol).($currrow+1));

			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "QTY PLAN");
			$sheet->mergeCells($this->num2alpha($currcol).$currrow.":".$this->num2alpha($currcol).($currrow+1));
			$colQtyPlan = $this->num2alpha($currcol);

			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "GROUP GUDANG");
			$sheet->mergeCells($this->num2alpha($currcol).$currrow.":".$this->num2alpha($currcol).($currrow+1));

			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "NAMA GROUP GUDANG");
			$sheet->mergeCells($this->num2alpha($currcol).$currrow.":".$this->num2alpha($currcol).($currrow+1));

			$nextrow = $currrow+1;
				
			// die(json_encode($Periods));
			for($i=0; $i<count($Periods); $i++) {
				$Periode = $this->HelperModel->GetNmPeriode($Periods[$i]["Tahun"], $Periods[$i]["Bulan"], $Periods[$i]["Periode"]);

				$currcol += 1;
				$col1 = $this->num2alpha($currcol);
				$Periods[$i]["NamaPeriode"] = $Periode;

				$Periods[$i]["ColQtyIndex"] = $currcol;
				$Periods[$i]["ColQty"] = $col1;
				$col2 = $this->num2alpha($currcol+1);
				$Periods[$i]["ColJns"] = $col2; 
				$col2 = $this->num2alpha($currcol+2);
				$Periods[$i]["ColPrePO"] = $col2;

				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $Periode);
				$sheet->mergeCells($col1.$currrow.":".$col2.$currrow);
				$nextrow = $currrow+1;
				$sheet->setCellValueByColumnAndRow($currcol, $nextrow, "QTY");				
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $nextrow, "JNS");
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $nextrow, "NO PREPO");
			}

			$currcol += 1;
			$colTotalQty = $currcol;
			$lastcol = $this->num2alpha($colTotalQty);
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "TOTAL QTY");
			$sheet->mergeCells($lastcol.$currrow.":".$lastcol.($currrow+1));

			$headerRow = 'A'.$currrow.':'.$this->num2alpha($currcol).$nextrow;
			$sheet->getStyle($headerRow)->getFont()->setBold(true);
			$sheet->getStyle($headerRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
			$sheet->getStyle($headerRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
			// $sheet->getStyle($headerRow)->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
			// $sheet->getStyle($headerRow)->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
			// $sheet->getStyle($headerRow)->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
			// $sheet->getStyle($headerRow)->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
			// $spreadsheet->getActiveSheet()->getStyle('B2')
			//     ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
			// $spreadsheet->getActiveSheet()->getStyle('B2')
			//     ->getFill()->getStartColor()->setARGB('FFFF0000');

			$currrow += 1;
			$startrow = $currrow + 1;
			$lastrow = 0;
			
			$Plans = array();
			$Details = $data["Details"];
			for($i=0; $i<count($Details); $i++) {
				$RowFound = false;
				$Brs = 0;

				for($x=0; $x<count($Plans); $x++) {
					if (($Details[$i]["Kd_Brg"]==$Plans[$x]["Kd_Brg"]) && ($Details[$i]["Wilayah"]==$Plans[$x]["Wilayah"])
						&& ($Details[$i]["Kd_GroupGudang"]==$Plans[$x]["Kd_GroupGudang"])) {
						$Brs = $Plans[$x]["RowIndex"];
						$RowFound = true;
					}
				}
				if($RowFound==false) {
					$currrow += 1;
					$Brs = $currrow;
					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $Details[$i]["Kd_Brg"]);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $Details[$i]["Wilayah"]);
					$currcol += 1;
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $Details[$i]["Kd_GroupGudang"]);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $Details[$i]["Nm_GroupGudang"]);

					array_push($Plans, array("Kd_Brg"=>$Details[$i]["Kd_Brg"], "Wilayah"=>$Details[$i]["Wilayah"], "Kd_GroupGudang"=>$Details[$i]["Kd_GroupGudang"], "RowIndex"=>$currrow));
				}

				// die(json_encode($Periods));
				$Periode = $this->HelperModel->GetNmPeriode($Details[$i]["Tahun"], $Details[$i]["Bulan"], $Details[$i]["Periode"]);	
				if ($Details[$i]["Qty"]>0) {
				
					for($x=0; $x<count($Periods); $x++) {
						
						if($sheet->getCellByColumnAndRow($Periods[$x]["ColQtyIndex"], $Brs)->getValue()==''){
							$sheet->getStyle($Periods[$x]["ColQty"].$Brs.':'.$Periods[$x]["ColPrePO"].$Brs)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('4d4d4d');
						}
						
						if ($Periods[$x]["NamaPeriode"]==$Periode) {
							$sheet->getStyle($Periods[$x]["ColQty"].$Brs.':'.$Periods[$x]["ColPrePO"].$Brs)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffffff');
							$sheet->setCellValue($Periods[$x]["ColQty"].$Brs, $Details[$i]["Qty"]);
							$sheet->setCellValue($Periods[$x]["ColJns"].$Brs, (($Details[$i]["Tipe_PO"]=="PO MAJOR")?"M":"R"));
							$sheet->setCellValue($Periods[$x]["ColPrePO"].$Brs, $Details[$i]["No_PrePO"]);
							
							if($Details[$i]["No_PrePO"]=='CANCELLED'){
								$sheet->getStyle($Periods[$x]["ColQty"].$Brs.':'.$Periods[$x]["ColPrePO"].$Brs)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('f08080');
							}
						}
						
					}
				}

				$lastrow = (($lastrow < $Brs)? $Brs : $lastrow);
				
			}
			
			

			$PlanDT = $this->CampaignPlanModel->GetPlanDT($trxID);
			// die(json_encode($PlanDT));
			for($i = $startrow; $i <= $currrow; $i++) {
				$TotalQty = 0;
				$KdBrg = $sheet->getCellByColumnAndRow(1, $i)->getValue();
				$Wilayah = $sheet->getCellByColumnAndRow(2, $i)->getValue();
				
				foreach($PlanDT as $d) {
					if (strtoupper(trim($d->ProductID))==strtoupper(trim($KdBrg)) && strtoupper(trim($d->Kota))==strtoupper(trim($Wilayah))) {
						$sheet->setCellValueByColumnAndRow(3, $i, $d->TotalQty);
					}
				}
				// echo($KdBrg."<br>".$Wilayah."<br>");
				for($x=0; $x<count($Periods); $x++) {
					$Qty = (int)$sheet->getCellByColumnAndRow($Periods[$x]["ColQtyIndex"], $i)->getValue();
					// echo("QTY ".$Periods[$x]["NamaPeriode"]." : ".$Qty."<br>");
					$Qty = (($Qty=="")? 0: (int)$Qty);
					$TotalQty += $Qty;
				}
				// echo("Total Qty : ".$TotalQty."<br>");

				$sheet->setCellValueByColumnAndRow($colTotalQty, $i, $TotalQty);
				$sheet->getStyle($colTotalQty.$startrow.":".$colTotalQty.$lastrow)->getNumberFormat()->setFormatCode('#,##0');
			}
			// die("--");

			$sheet->getStyle($colQtyPlan.$startrow.":".$colQtyPlan.$lastrow)->getNumberFormat()->setFormatCode('#,##0');
			for($x=0; $x<count($Periods); $x++) {
				$sheet->getStyle($Periods[$x]["ColQty"].$startrow.":".$Periods[$x]["ColQty"].$lastrow)->getNumberFormat()->setFormatCode('#,##0');
			}

			$rows = 'A'.$startrow.':'.$lastcol.$lastrow;
			// $sheet->getStyle($headerRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
			$sheet->getStyle($rows)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

			$styleArray = [
			    'borders' => [
			  		'allBorders' => [
			            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			        ]
			   	 ]
			];  
			
			// echo $this->num2alpha($lastcol).$lastrow;die;
			
			$sheet->getStyle('A7:'.$lastcol.$lastrow)->applyFromArray($styleArray);
			
			// for ($i = 'A'; $i != $this->num2alpha($lastcol); $i++) {
				// for($j=7;$j<=$lastrow;$j++) {
					// $cell = $i.$j;
					// $sheet->getStyle($cell)
					    // ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
					// $sheet->getStyle($cell)
					    // ->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
					// $sheet->getStyle($cell)
					    // ->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
					// $sheet->getStyle($cell)
					    // ->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
				// }
			// }

			// $sheet->mergeCells('A1:J1');
			for ($i = 'A'; $i !=   $sheet->getHighestColumn(); $i++) {
			    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
			}

			$this->Logs_Update($LogDate,'SUCCESS','EXPORT EXCEL PERENCANAAN CAMPAIGN');
			$filename='BreakdownCampaignPlan['.date('YmdHis').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
	        $writer->save('php://output');	// download file 
	        exit();

		}

		public function md5()
		{
			$brg = "SPG30-321 BIT";
			$sn = "200804002";
			$salt = "GARAM";
			$x = md5($brg.$salt.$sn);
			die($x);
		}


		function Logs_insert($LogDate='',$description=''){
			$params = array();   
			$params['LogDate'] = $LogDate;
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "PERENCANAAN CAMPAIGN";
			$params['TrxID'] = date_format(date_create($LogDate),'YmdHis');
			$params['Description'] = $_SESSION["logged_in"]["username"]." ".$description;
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);
		}

		function Logs_Update($LogDate='',$remarks='',$description=''){
			$params = array();   
			$params['LogDate'] = $LogDate;
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "PERENCANAAN CAMPAIGN";
			$params['TrxID'] = date_format(date_create($LogDate),'YmdHis');
			$params['Description'] = $_SESSION["logged_in"]["username"]." ".$description;
			$params['Remarks']=$remarks;
		   	$params['RemarksDate'] = date("Y-m-d H:i:s");
		   	$this->ActivityLogModel->update_activity($params);
		}

		function listhead($number, $type) {
		    $url = $this->API_URL . "/CampaignPlanView/Get?api=APITES&number=" . $number . "&type=" . $type;

		    $ch = curl_init($url);

		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		    $response = curl_exec($ch);

		    if (curl_errno($ch)) {
		        echo 'Error: ' . curl_error($ch);
		    }

		    curl_close($ch);

		    print_r($response);
		}

		function Get_priod($number, $type) {
		    $url = $this->API_URL."/CampaignPlanView/Get_priod?api=APITES&number=".$number."&type=".$type;

		    $ch = curl_init($url);

		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		    $response = curl_exec($ch);

		    if (curl_errno($ch)) {
		        echo 'Error: ' . curl_error($ch);
		    }

		    curl_close($ch);

		    print_r($response);
		}

		function Get_Barang($number, $type, $tahun='', $bulan='', $priod='') {
		    $url = $this->API_URL."/CampaignPlanView/Get_Barang?api=APITES&number=".$number."&type=".$type."&tahun=".$tahun."&bulan=".$bulan."&priod=".$priod;

		    $ch = curl_init($url);

		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		    $response = curl_exec($ch);

		    if (curl_errno($ch)) {
		        echo 'Error: ' . curl_error($ch);
		    }

		    curl_close($ch);

		    print_r($response);
		}

		function Get_Wilayah() {

		    $url = $this->API_URL."/CampaignPlanView/Get_Wilayah?api=APITES&number=".$this->input->get('number')."&type=".$this->input->get('type')."&tahun=".$this->input->get('tahun')."&bulan=".$this->input->get('bulan')."&priod=".$this->input->get('priod')."&barang=".$this->input->get('barang')."&wilayah=".$this->input->get('wilayah')."&status=".$this->input->get('status');

		    $ch = curl_init($url);

		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		    $response = curl_exec($ch);

		    if (curl_errno($ch)) {
		        echo 'Error: ' . curl_error($ch);
		    }

		    curl_close($ch);

		    print_r($response);
		}

		function Get_list() {

		    $url = $this->API_URL."/CampaignPlanView/Get_list?api=APITES&number=".$this->input->get('number')."&type=".$this->input->get('type')."&tahun=".$this->input->get('tahun')."&bulan=".$this->input->get('bulan')."&priod=".$this->input->get('priod')."&barang=".$this->input->get('barang')."&wilayah=".$this->input->get('wilayah')."&status=".$this->input->get('status');

		    $ch = curl_init($url);

		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		    $response = curl_exec($ch);

		    if (curl_errno($ch)) {
		        echo 'Error: ' . curl_error($ch);
		    }

		    curl_close($ch);

		    print_r($response);
		}

		function GetIn() {

		    $url = $this->API_URL."/CampaignPlanView/GetIn?api=APITES&number=".$this->input->get('number')."&type=".$this->input->get('type')."&kdbrg=".$this->input->get('kdbrg')."&tahun=".$this->input->get('tahun')."&bulan=".$this->input->get('bulan')."&priod=".$this->input->get('priod')."&kdgudang=".$this->input->get('kdgudang');

		    $ch = curl_init($url);

		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		    $response = curl_exec($ch);

		    if (curl_errno($ch)) {
		        echo 'Error: ' . curl_error($ch);
		    }

		    curl_close($ch);

		    print_r($response);
		}



	}
