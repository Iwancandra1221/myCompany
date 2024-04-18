<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ExportData extends MY_Controller {

	function __construct()
	{
		parent::__construct();
        $this->load->model('MsBarangModel');
        $this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function index()
	{

	}

	public function KategoriInsentif()
	{
		$data = array();
		$data["mode"] = "KATEGORI INSENTIF";
		$data["databases"] = $this->MasterDbModel->getListForExport("", "NamaDb");
		$this->RenderView('ExportDataView',$data);
	}

	Public function ExportKategoriInsentif()
	{
		$err = "";
		$post = $this->PopulatePost();

		if(isset($post['db'])){
			$dbId = $post["db"];
			$db = $this->MasterDbModel->get($dbId);
			//die(json_encode($db));

			$URL = $this->API_URL;
			$URL.= "/MsBarang/GetBarangInsentifList?api=APITES&divisi=".urlencode("all").
    				"&merk=".urlencode("all")."&jenis=".urlencode("all")."&kategori=".urlencode("all")."&filter=".urlencode("EXPORT").
    				"&view=1";
    		// die($URL);
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
					//die(json_encode($data));
					
					// $urlBhakti = $db->AlamatWebService;
					$urlBhakti = HO;

					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_URL => $urlBhakti.API_BKT."/MasterBarang/UpdateProductGroup2",
						//CURLOPT_URL => "http://localhost/bktAPI/MasterBarang/UpdateProductGroup",
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_TIMEOUT => 60,
						CURLOPT_POST => 1,
						CURLOPT_POSTFIELDS => json_encode($data),
						CURLOPT_HTTPHEADER => array("Content-type: application/json")
					));
					
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

	        	if ($lanjut) {
		        	echo json_encode(array("result"=>"sukses", "error"=>"Sukses Update List Barang Silver&Gold", "list"=>$PG));
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

	Public function ExportKategoriInsentifOld()
	{
		$post = $this->PopulatePost();
		if(isset($post['db'])){
			$dbId = $post["db"];
			$db = $this->MasterDbModel->get($dbId);
			//die(json_encode($db));
			$URL = $this->API_URL;
			$URL.= "/MsBarang/GetBarangInsentifList?api=APITES&divisi=".urlencode("all").
    				"&merk=".urlencode("all")."&jenis=".urlencode("all")."&kategori=".urlencode("GOLD")."&filter=".urlencode("EXPORT").
    				"&view=0";
    		// die($URL);
	        $list = json_decode(file_get_contents($URL), true);
	        // die(json_encode($list));
	        if ($list["result"]=="sukses") {
	        	$PG = $list["data"];
	        	$lanjut = true;
		        if ($lanjut) {
		        	for($i=0;$i<count($PG);$i++) {
		        		$kdbrg = $PG[$i]["KD_BRG"];
		        		$kategori = $PG[$i]["KATEGORI_INSENTIF"];
		        		$start = $PG["$i"]["STARTDATE"];

		        		if($lanjut) {
							$data = [
								"api" => "APITES",
								"brg" => $kdbrg,
								"kategori" => $kategori,
								"start" => date("Y-m-d", strtotime($start)),
								"user" => $_SESSION["logged_in"]["username"],
								"svr"=>$db->Server,
								"db" => $db->Database,
								"uid" => SQL_UID,
								"pwd" => SQL_PWD
							];
							//die(json_encode($data));
							
							$urlBhakti = $db->AlamatWebService;
							$urlBhakti = "http://localhost/";

							$curl = curl_init();
							curl_setopt_array($curl, array(
								CURLOPT_URL => $urlBhakti.API_BKT."/MasterBarang/UpdateProductGroup",
								//CURLOPT_URL => "http://localhost/bktAPI/MasterBarang/UpdateProductGroup",
								CURLOPT_RETURNTRANSFER => true,
								CURLOPT_TIMEOUT => 60,
								CURLOPT_POST => 1,
								CURLOPT_POSTFIELDS => json_encode($data),
								CURLOPT_HTTPHEADER => array("Content-type: application/json")
							));
							
							$response = curl_exec($curl);
							$err = curl_error($curl);
							curl_close($curl);
							// echo(json_encode($response));
							// print_r($response);

					        if (json_decode($response)=="sukses") {
					        	$lanjut=true;
					       	} else {
					       		$lanjut=false;
					       	}

					       	$PG[$i]["SUCCESS"] = $lanjut;

		        			/*$cURL = $db->AlamatWebService.API_BKT."/MasterBarang/UpdateProductGroup?api=APITES".
		        					"&brg=".urlencode($kdbrg)."&kategori=".urlencode($kategori)."&start=".urlencode(date("Y-m-d", strtotime($start))).
		        					"&user=".urlencode($_SESSION["logged_in"]["username"]).
		        					"&svr=".urlencode($db->Server)."&db=".urlencode($db->Database).
		        					"&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD);
				        	$updtPG = json_decode(file_get_contents($cURL));

					        if ($updtPG=="sukses") {
					        	$lanjut=true;
					       	} else {
					       		//die($cURL."<br>".$updtPG);
					       		$lanjut=false;
					       	}
					       	*/
					    } else {
					       	$PG[$i]["SUCCESS"] = false;
					    }
		        	}
		        }

	        	if ($lanjut) {
		        	echo json_encode(array("result"=>"sukses", "error"=>"Sukses Update List Barang Silver&Gold", "list"=>$PG));
	        	} else {
					echo json_encode(array("result"=>"gagal", "error"=>json_decode($response), "list"=>$PG)); //"Error Update Kategori Barang"));
	        	}
	        } else {
	        	echo json_encode(array("result"=>$list["result"], "error"=>$list["error"], "list"=>array()));
	        }
		} else {
			echo json_encode(array("result"=>"gagal", 'error'=>'Param CBG Belum Diberikan', "list"=>array()));
		}
	}

	Public function ExportKategoriInsentif2()
	{
		$post = $this->PopulatePost();
		if(isset($post['db'])){
			$dbId = $post["db"];
			$db = $this->MasterDbModel->get($dbId);
			//die(json_encode($db));

			$lanjut=true;
			$data = [
				"api" => "APITES",
				"user" => $_SESSION["logged_in"]["username"],
				"svr"=>$db->Server,
				"db" => $db->Database,
				"uid" => SQL_UID,
				"pwd" => SQL_PWD
			];
			//die(json_encode($data));
			
			$urlBhakti = $db->AlamatWebService;
			$urlBhakti = "http://localhost/";

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $urlBhakti.API_BKT."/MasterBarang/UpdateProductGroup3",
				//CURLOPT_URL => "http://localhost/bktAPI/MasterBarang/UpdateProductGroup",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => json_encode($data),
				CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo(json_encode($response));
			// print_r($response);

	        if (json_decode($response)=="sukses") {
	        	$lanjut=true;
	       	} else {
	       		$lanjut=false;
	       	}

        	if ($lanjut) {
	        	echo json_encode(array("result"=>"sukses", "error"=>"Sukses Update List Barang Silver&Gold", "list"=>array()));
        	} else {
				echo json_encode(array("result"=>"gagal", "error"=>json_decode($response), "list"=>array())); //"Error Update Kategori Barang"));
        	}
		} else {
			echo json_encode(array("result"=>"gagal", 'error'=>'Param CBG Belum Diberikan', "list"=>array()));
		}
	}
}