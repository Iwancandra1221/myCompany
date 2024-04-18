<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CompareTrx extends MY_Controller 
{
	public $excel_flag = 0;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('HelperModel');
		$this->load->helper('FormLibrary');
		$this->load->library('email');
		$this->load->library('excel');
	}

	public function index($err="")
	{
		$data = array();

		$data['title'] = 'BHAKTI.CO.ID | COMPARE TRX';
		//$data['reportOption'] = "STOCK TOTAL";
		//$data['formDest'] = "ReportStock/ProsesStockTotal";
		$dbJKT = $this->MasterDbModel->getByBranchId("DMI");
		
		$dbCBG = array();
		array_push($dbCBG, array("namaDB"=>"BALI", "cabang"=>"BALI"));
		array_push($dbCBG, array("namaDB"=>"BDG", "cabang"=>"BANDUNG"));
		array_push($dbCBG, array("namaDB"=>"BJM", "cabang"=>"BANJARMASIN"));
		array_push($dbCBG, array("namaDB"=>"BGR", "cabang"=>"BOGOR"));
		array_push($dbCBG, array("namaDB"=>"CRB", "cabang"=>"CIREBON"));
		array_push($dbCBG, array("namaDB"=>"JBI", "cabang"=>"JAMBI"));
		array_push($dbCBG, array("namaDB"=>"KRW", "cabang"=>"KARAWANG"));
		array_push($dbCBG, array("namaDB"=>"LPG", "cabang"=>"LAMPUNG"));
		array_push($dbCBG, array("namaDB"=>"MKS", "cabang"=>"MAKASSAR"));
		array_push($dbCBG, array("namaDB"=>"MDN", "cabang"=>"MEDAN"));
		array_push($dbCBG, array("namaDB"=>"PDG", "cabang"=>"PADANG"));
		array_push($dbCBG, array("namaDB"=>"PLB", "cabang"=>"PALEMBANG"));
		array_push($dbCBG, array("namaDB"=>"PKB", "cabang"=>"PEKANBARU"));
		array_push($dbCBG, array("namaDB"=>"PTK", "cabang"=>"PONTIANAK"));
		array_push($dbCBG, array("namaDB"=>"SRD", "cabang"=>"SAMARINDA"));
		array_push($dbCBG, array("namaDB"=>"SMG", "cabang"=>"SEMARANG"));
		array_push($dbCBG, array("namaDB"=>"SURABAYA", "cabang"=>"SURABAYA"));
		array_push($dbCBG, array("namaDB"=>"YGY", "cabang"=>"YOGYAKARTA"));

		$data["dbJKT"] = $dbJKT;
		$data["dbCBG"] = $dbCBG;
		$data["error"] = $err;
		$this->RenderView('CompareTrxForm',$data);
	}

	public function ProsesCompare()
	{
		$data = array();
		$page_title = 'COMPARE TRANSAKSI';
		$api = "APITES";

		if(isset($_POST["btnCompare"])){
		}

		if(isset($_POST['dp1']))
		{
			$dp1 = $_POST["dp1"];
			$dp2 = $_POST["dp2"];
			//$db1 = $_POST["db1"];
			$dbCBG = $_POST["dbCBG"];
			$trxType=$_POST["trxType"];

			$DtBase1 = $this->MasterDbModel->get($dp1);
			$DtBase2 = $this->MasterDbModel->get($dp2);

			/*Select DatabaseId, BranchId, NamaDb, AlamatWebService, [Server], [Database], DatabaseType,
			Created_Time, Created_By, Updated_Time, Updated_By
			From MsDatabase Where DatabaseId=".$dataid);*/
			
			$api1 = $DtBase1->AlamatWebService;
			$server1=$DtBase1->Server;
			$database1=$DtBase1->Database;

			$api1 = "http://localhost/";
			$server1="10.1.48.200";
			$database1="BHAKTI";

			$api2 = $DtBase2->AlamatWebService;
			$server2 = $DtBase2->Server;
			$database2 = $DtBase2->Database;

			$api2 = "http://localhost/";
			$server2 = "10.1.0.6";
			$database2 = $dbCBG;

			$KdLokasi = "";

			$curl = $api2."bktAPI/Konsolidasi/GetTblConfig?api=".$api."&svr=".urlencode($server2)."&db=".urlencode($database2);
			//die($curl);
			$cGetKdLokasi = json_decode(file_get_contents($curl),true);
			if ($cGetKdLokasi["result"]=="sukses") {
				$KdLokasi = $cGetKdLokasi["data"]["KD_LOKASI"];
			}
			//die($KdLokasi);

			$jTrx = array();
			$jurl = $api1."bktAPI/Konsolidasi/GetTransactions?api=".$api."&svr=".urlencode($server1)."&db=".urlencode($database1).
					"&lok=".$KdLokasi."&dp1=".urlencode($dp1)."&dp2=".urlencode($dp2)."&trxtype=".urlencode($trxType);
			$jGetTrx = json_decode(file_get_contents($jurl), true);
			if ($jGetTrx["result"]=="sukses") {
				$jTrx = $jGetTrx["data"];
			}

			$cTrx = array();
	        $curl = $api2."bktAPI/Konsolidasi/GetTransactions?api=".$api."&svr=".urlencode($server2)."&db=".urlencode($database2).
					"&lok=".$KdLokasi."&dp1=".urlencode($dp1)."&dp2=".urlencode($dp2)."&trxtype=".urlencode($trxType);
			$cGetTrx = json_decode(file_get_contents($curl), true);
			if ($cGetTrx["result"]=="sukses") {
				$cTrx = $cGetTrx["data"];
			}

			//die($jurl."<br><br>".$curl);

			$data["trxType"] = $trxType;
			$data["trxJkt"] = $jTrx;
			$data["trxCbg"] = $cTrx;
			$data["dp1"] = $dp1;
			$data["dp2"] = $dp2;

			$this->RenderView("CompareTrxResult", $data);
		}
		else
		{
			//die("no wilayah");
			redirect("CompareTrx");
		}
	}



}