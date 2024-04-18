<?php 
	header('Access-Control-Allow-Origin:*');
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

	ini_set('pcre.backtrack_limit', 10000000); 
	ini_set('memory_limit', '4096M');
	
	class Reportservice extends MY_Controller 
	{
		public function __construct()
		{
			parent::__construct();
			$this->load->model('HelperModel');
			$this->load->helper('FormLibrary');
			$this->load->library('excel');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
			$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
		}
      
		public function index() 
		{
        	//http://localhost:90/myCompany/reportservice/index
			$data = array();
			$api = 'APITES';
			set_time_limit(60);
			//persiapkan bktAPI
			$res = $this->MasterDbModel->getByLocationCode($_SESSION['logged_in']['branch_id']);
			$AlamatBKTAPI = $res->AlamatWebService;
			$ServerBKTAPI = $res->Server;
			$DatabaseBKTAPI = $res->Database;

			//Periode
			//Kode Nota Service (bktAPI)
			//http://localhost:90/bktAPI/Reportservice/GetListKodeNotaService?api=APITES
			$url = $AlamatBKTAPI.API_BKT."/Reportservice/GetListKodeNotaService?api=".$api;
			// die($url);       
			// open connection
			$curl = curl_init();
			// set the url, number of POST vars, POST data
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 1000,
				CURLOPT_POST => 1,
				CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			// execute post
			$response = curl_exec($curl);
			$err = curl_error($curl);
			// close connection
			curl_close($curl);

			if ($response===false) {
				$lanjut = false;
				$data["messages"] = "API Tujuan OFFLINE";
			} else {         
				$hasil = json_decode($response);
				if ($hasil->result == "sukses") {
					$data["kodenotaservices"] = $hasil->data;
				} else {
					$data["kodenotaservices"] = "";
				}
				//die($data["kodenotaservices"]);
			}

			//Merk
			//http://localhost:90/webAPI/MsBarang/GetMerkList2?api=APITES
			$url = API_URL."/MsBarang/GetMerkList2?api=".$api;
			//die($url);
			// open connection
			$curl = curl_init();
			// set the url, number of POST vars, POST data
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 1000,
				CURLOPT_POST => 1,
				CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			// execute post
			$response = curl_exec($curl);
			$err = curl_error($curl);
			// close connection
			curl_close($curl);
			// die($response);

			if ($response===false) {
				$lanjut = false;
				$data["messages"] = "API Tujuan OFFLINE";
			} else {         
				$hasil = json_decode($response);
				$data["merks"] = $hasil;

				//die($data["merks"]);
			}        
			
			//Barang
			//http://localhost:90/webAPI/MsBarang/GetBarangListByMerk?api=APITES&merk=ALL
			$url = API_URL."/MsBarang/GetBarangListByMerk?api=".$api."&merk=ALL";
			//die($url);
			// open connection
			$curl = curl_init();
			// set the url, number of POST vars, POST data
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 1000,
				CURLOPT_POST => 1,
				CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			// execute post
			$response = curl_exec($curl);
			$err = curl_error($curl);
			// close connection
			curl_close($curl);

			if ($response===false) {
				$lanjut = false;
				$data["messages"] = "API Tujuan OFFLINE";
			} else {         
				$hasil = json_decode($response);
				if ($hasil->result == "sukses") {
					$data["barangs"] = $hasil->data;
				} else {
					$data["barangs"] = "";
				}
				//die($data["barangs"]);
			}

			//Teknisi (bktAPI)
			//http://localhost:90/bktAPI/Reportservice/GetListTeknisi?api=APITES
			$url = $AlamatBKTAPI.API_BKT."/Reportservice/GetListTeknisi?api=".$api;
			//die($url);

			// open connection
			$curl = curl_init();
			// set the url, number of POST vars, POST data
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 1000,
				CURLOPT_POST => 1,
				CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			// execute post
			$response = curl_exec($curl);
			$err = curl_error($curl);
			// close connection
			curl_close($curl);

			if ($response===false) {
				$lanjut = false;
				$data["messages"] = "API Tujuan OFFLINE";
			} else {         
				$hasil = json_decode($response);
				if ($hasil->result == "sukses") {
					$data["teknisis"] = $hasil->data;
				} else {
					$data["teknisis"] = "";
				}
				//die($data["teknisis"]);
			}

			$data["DealerSelected"] = "ALL";

			$data['title'] = 'Laporan Service';
			//echo json_encode($data);
			$this->RenderView('reportserviceview',$data);
			//$this->RenderView('LaporanJualReturExcludePPN',$data);
		}  

		/*public function index() 
		{
			//http://localhost:90/myCompany/reportservice/index
			$data = array();
			$api = 'APITES';
			set_time_limit(60);
		    //persiapkan bktAPI
		    $res = $this->MasterDbModel->getByBranchId($_SESSION['logged_in']['branch_id']);
		    // die(json_encode($res));
		    $AlamatBKTAPI = $res->AlamatWebService;
		    $ServerBKTAPI = $res->Server;
		    $DatabaseBKTAPI = $res->Database;

		    //Periode
		    //Kode Nota Service (bktAPI)
		    //http://localhost:90/bktAPI/Reportservice/GetListKodeNotaService?api=APITES
		    $url = $AlamatBKTAPI.$this->API_BKT."/Reportservice/GetListKodeNotaService?api=".$api;
		    // die($url);       
		    // open connection
		    $curl = curl_init();
		    // set the url, number of POST vars, POST data
		    curl_setopt_array($curl, array(
		        CURLOPT_URL => $url,
		        CURLOPT_RETURNTRANSFER => true,
		        CURLOPT_TIMEOUT => 60,
		        CURLOPT_POST => 1,
		        CURLOPT_HTTPHEADER => array("Content-type: application/json")
		    ));
		    // execute post
		    $response = curl_exec($curl);
		    $err = curl_error($curl);
		    // close connection
		    curl_close($curl);

		    if ($response===false) {
		        $lanjut = false;
		        $data["messages"] = "API Tujuan OFFLINE";
		    } else {         
		        $hasil = json_decode($response);
		        if ($hasil->result == "sukses") {
		            $data["kodenotaservices"] = $hasil->data;
		        } else {
		            $data["kodenotaservices"] = "";
		        }
		        //die($data["kodenotaservices"]);
		    }

			//Merk
			//http://localhost:90/webAPI/MsBarang/GetMerkList2?api=APITES
			$url = API_URL."/MsBarang/GetMerkList2?api=".$api;
			//die($url);
			// open connection
			$curl = curl_init();
			// set the url, number of POST vars, POST data
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 1000,
				CURLOPT_POST => 1,
				CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			// execute post
			$response = curl_exec($curl);
			$err = curl_error($curl);
			// close connection
			curl_close($curl);
			// die($response);

		    if ($response===false) {
		        $lanjut = false;
		        $data["messages"] = "API Tujuan OFFLINE";
		    } else {         
		        $hasil = json_decode($response);
		        $data["merks"] = $hasil;

		        //die($data["merks"]);
		    }        
		    
		    //Barang
		    //http://localhost:90/webAPI/MsBarang/GetBarangListByMerk?api=APITES&merk=ALL
		    $url = $this->API_URL."/MsBarang/GetBarangListByMerk?api=".$api."&merk=ALL";
		    //die($url);
		    // open connection
		    $curl = curl_init();
		    // set the url, number of POST vars, POST data
		    curl_setopt_array($curl, array(
		        CURLOPT_URL => $url,
		        CURLOPT_RETURNTRANSFER => true,
		        CURLOPT_TIMEOUT => 60,
		        CURLOPT_POST => 1,
		        CURLOPT_HTTPHEADER => array("Content-type: application/json")
		    ));
		    // execute post
		    $response = curl_exec($curl);
		    $err = curl_error($curl);
		    // close connection
		    curl_close($curl);

		    if ($response===false) {
		        $lanjut = false;
		        $data["messages"] = "API Tujuan OFFLINE";
		    } else {         
		        $hasil = json_decode($response);
		        if ($hasil->result == "sukses") {
		            $data["barangs"] = $hasil->data;
		        } else {
		            $data["barangs"] = "";
		        }
		        //die($data["barangs"]);
		    }

		    //Teknisi (bktAPI)
		    //http://localhost:90/bktAPI/Reportservice/GetListTeknisi?api=APITES
		    $url = $AlamatBKTAPI.$this->API_BKT."/Reportservice/GetListTeknisi?api=".$api;
		    //die($url);

		    // open connection
		    $curl = curl_init();
		    // set the url, number of POST vars, POST data
		    curl_setopt_array($curl, array(
		        CURLOPT_URL => $url,
		        CURLOPT_RETURNTRANSFER => true,
		        CURLOPT_TIMEOUT => 60,
		        CURLOPT_POST => 1,
		        CURLOPT_HTTPHEADER => array("Content-type: application/json")
		    ));
		    // execute post
		    $response = curl_exec($curl);
		    $err = curl_error($curl);
		    // close connection
		    curl_close($curl);

		    if ($response===false) {
		        $lanjut = false;
		        $data["messages"] = "API Tujuan OFFLINE";
		    } else {         
		        $hasil = json_decode($response);
		        if ($hasil->result == "sukses") {
		            $data["teknisis"] = $hasil->data;
		        } else {
		            $data["teknisis"] = "";
		        }
		        //die($data["teknisis"]);
		    }

		    $data["DealerSelected"] = "ALL";

			$data['title'] = 'Laporan Service';
			//echo json_encode($data);
			$this->RenderView('reportserviceview',$data);
			//$this->RenderView('LaporanJualReturExcludePPN',$data);
		} */

		public function carinamabarang() 
		{
			$data = array();
			$api = 'APITES';
			set_time_limit(60);

			$_POST = $this->PopulatePost();	
			$kodebarang = urldecode($_POST["kodebarang"]);

			//http://localhost:90/webAPI/MsBarang/Get?api=APITES&brg=MCM-508
			$url = API_URL."/MsBarang/Get?api=".$api."&brg=".urlencode($kodebarang);
			//die($url);
			// open connection
			$curl = curl_init();
			// set the url, number of POST vars, POST data
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 1000,
				CURLOPT_POST => 1,
				CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			// execute post
			$response = curl_exec($curl);
			$err = curl_error($curl);
			// close connection
			curl_close($curl);

			if ($response===false) {
				$lanjut = false;
				$data["messages"] = "API Tujuan OFFLINE";
			} else {         
				$hasil = json_decode($response);
				if ($hasil->result == "sukses") {
					$data["detailbarang"] = $hasil->data;
					//die($data["detailbarang"]);
					//echo json_encode($data["detailbarang"]->NM_BRG); //hasilnya "MCM-507 MAGIC WARMER PLUS"
					echo ($data["detailbarang"]->NM_BRG); //hasilnya MCM-507 MAGIC WARMER PLUS
				} else {
					$data["detailbarang"] = array();
					echo ("DATA NOT FOUND");
				}
			}
		}    

		/*public function carinamabarang() 
		{
			$data = array();
			$api = 'APITES';
			set_time_limit(60);
		    $_POST = $this->PopulatePost();	
		    $kodebarang = urldecode($_POST["kodebarang"]);

		    //http://localhost:90/webAPI/MsBarang/Get?api=APITES&brg=MCM-508
		    $url = $this->API_URL."/MsBarang/Get?api=".$api."&brg=".urlencode($kodebarang);
		    //die($url);
		    // open connection
		    $curl = curl_init();
		    // set the url, number of POST vars, POST data
		    curl_setopt_array($curl, array(
		        CURLOPT_URL => $url,
		        CURLOPT_RETURNTRANSFER => true,
		        CURLOPT_TIMEOUT => 60,
		        CURLOPT_POST => 1,
		        CURLOPT_HTTPHEADER => array("Content-type: application/json")
		    ));
		    // execute post
		    $response = curl_exec($curl);
		    $err = curl_error($curl);
		    // close connection
		    curl_close($curl);

		    if ($response===false) {
		        $lanjut = false;
		        $data["messages"] = "API Tujuan OFFLINE";
		    } else {         
		        $hasil = json_decode($response);
		        if ($hasil->result == "sukses") {
		            $data["detailbarang"] = $hasil->data;
		            //die($data["detailbarang"]);
		            //echo json_encode($data["detailbarang"]->NM_BRG); //hasilnya "MCM-507 MAGIC WARMER PLUS"
		            echo ($data["detailbarang"]->NM_BRG); //hasilnya MCM-507 MAGIC WARMER PLUS
		        } else {
		            $data["detailbarang"] = array();
		            echo ("DATA NOT FOUND");
		        }
		    }
		}*/
  
		public function listbarangsesuaidivisi() 
		{
			$data = array();
			$api = 'APITES';
			set_time_limit(60);

			$_POST = $this->PopulatePost();	
			$merk = urldecode($_POST["merk"]);

			//http://localhost:90/webAPI/MsBarang/GetBarangListByMerk?api=APITES&merk=MIYAKO
			$url = API_URL."/MsBarang/GetBarangListByMerk?api=".$api."&merk=".$merk;
			//die($url);
			// open connection
			$curl = curl_init();
			// set the url, number of POST vars, POST data
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 1000,
				CURLOPT_POST => 1,
				CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			// execute post
			$response = curl_exec($curl);
			$err = curl_error($curl);
			// close connection
			curl_close($curl);

			if ($response===false) {
				$lanjut = false;
				$data["messages"] = "API Tujuan OFFLINE";
			} else {         
				$hasil = json_decode($response);
				if ($hasil->result == "sukses") {
					$data["detailbarang"] = $hasil->data;
				} else {
					$data["detailbarang"] = "";
				}
				//die($data["detailbarang"]);
				echo json_encode($data["detailbarang"]);
			}
		}  
	
		public function proses_report()
		{
			$keyAPI = 'APITES';
			$api = 'APITES';
			set_time_limit(0);
	
			// // untuk TEST
			// //http://localhost:90/myCompany/Reportopj/Report_6N2_OPJ?api=APITES&wilayah=BANDUNG&salesman=ALL&merk=ALL&bulan=1&tahun=2022&divisi=ALL&partnertype=ALL
			// $params["wilayah"] = urldecode($this->input->get("wilayah"));
			// $params["salesman"] = urldecode($this->input->get("salesman"));
			// $params["merk"] = urldecode($this->input->get("merk"));
			// $params["bulan"] = urldecode($this->input->get("bulan"));
			// $params["tahun"] = urldecode($this->input->get("tahun"));
			// $params["divisi"] = urldecode($this->input->get("divisi"));
			// $params["partnertype"] = urldecode($this->input->get("partnertype"));

			$_POST = $this->PopulatePost();	
			$params["report"] = urldecode($_POST["report"]);
			$params["kodenotaservice"] = urldecode($_POST["kodenotaservice"]);
			$params["tanggal1"] = urldecode($_POST["tanggal1"]);
			$params["dp11"] = urldecode($_POST["dp11"]);
			$params["dp12"] = urldecode($_POST["dp12"]);
			$params["tanggal2"] = urldecode($_POST["tanggal2"]);
			$params["dp21"] = urldecode($_POST["dp21"]);
			$params["dp22"] = urldecode($_POST["dp22"]);
			$params["merk"] = urldecode($_POST["merk"]);
			$params["kodebarang"] = urldecode($_POST["kodebarang"]);
			$params["teknisi"] = urldecode($_POST["teknisi"]);
			$params["garansi"] = urldecode($_POST["garansi"]);

			$metodebayar= "";
			foreach ($_POST['metodebayar'] as $value){
			   $metodebayar .= "'$value'". ",";
			}
			$metodebayar = substr($metodebayar,0,-1);
			$params["metodebayar"] = $metodebayar;

			$params["cetak"] = urldecode($_POST["cetak"]);

			//Checkboxes are not included in the POST data when the form is submitted. You need to call 
			if (isset($_POST['cetakpermerk'])){
				$params["cetakpermerk"]="Y";
			} else {
				$params["cetakpermerk"]="N";
			}

			$params["status"] = urldecode($_POST["status"]);

			//echo json_encode($params);
			// die();

			if($keyAPI==$api) {
				$array_data = array();

				//persiapkan bktAPI
				$res = $this->MasterDbModel->getByLocationCode($_SESSION['logged_in']['branch_id']);
				$AlamatBKTAPI = $res->AlamatWebService;
				$ServerBKTAPI = $res->Server;
				$DatabaseBKTAPI = $res->Database;

				//$page_title Maximum 31 characters allowed in sheet title
				$page_title = "Report Harian Service";
				if ($params["report"]=="01"){
					$excel_title = "Laporan Harian Service tanpa Detail Sparepart";
					$url = $AlamatBKTAPI.API_BKT."/Reportservice/Report_01?api=".$api;
				} elseif ($params["report"]=="02"){
					$excel_title = "Laporan Harian Service dengan Detail Sparepart";
					$url = "";
				} elseif ($params["report"]=="03"){
					$excel_title = "Laporan Ongkos Service";
					$url = "";
				} elseif ($params["report"]=="04"){
					$excel_title = "Laporan Service berdasarkan Teknisi";
					$url = "";
				} elseif ($params["report"]=="05"){
					$excel_title = "Laporan Produk Masuk Service dari Pembeli/Distributor";
					$url = "";
				} elseif ($params["report"]=="06"){
					$excel_title = "Laporan Service Pajak";
					$url = "";
				} elseif ($params["report"]=="07"){
					$excel_title = "Laporan Service Per Kode Barang By QTY";
					$url = "";
				} elseif ($params["report"]=="08"){
					$excel_title = "Laporan Pemasukan Service";
					$url = "";
				} elseif ($params["report"]=="09"){
					$excel_title = "Laporan Summary Service Harian";
					$url = $AlamatBKTAPI.API_BKT."/Reportservice/Report_09_10?api=".$api;
				} elseif ($params["report"]=="10"){
					$excel_title = "Laporan Summary Service Harian Group By Metode Bayar";
					$url = "";
				}

				$url .= "&report=".urlencode($params["report"]).
				"&kodenotaservice=".urlencode($params["kodenotaservice"]).
				"&tanggal1=".urlencode($params["tanggal1"]).
				"&dp11=".urlencode($params["dp11"]).
				"&dp12=".urlencode($params["dp12"]).
				"&tanggal2=".urlencode($params["tanggal2"]).
				"&dp21=".urlencode($params["dp21"]).
				"&dp22=".urlencode($params["dp22"]).
				"&merk=".urlencode($params["merk"]).
				"&kodebarang=".urlencode($params["kodebarang"]).
				"&teknisi=".urlencode($params["teknisi"]).
				"&garansi=".urlencode($params["garansi"]).
				"&metodebayar=".urlencode($params["metodebayar"]).
				"&cetak=".urlencode($params["cetak"]).
				"&cetakpermerk=".urlencode($params["cetakpermerk"]).
				"&status=".urlencode($params["status"]);
				//die($url);      

				// open connection
				$curl = curl_init();
				// set the url, number of POST vars, POST data
				curl_setopt_array($curl, array(
					CURLOPT_URL => $url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 1000,
					CURLOPT_POST => 1,
					CURLOPT_HTTPHEADER => array("Content-type: application/json")
				));
				// execute post
				$response = curl_exec($curl);
				$err = curl_error($curl);
				// close connection
				curl_close($curl);

				// echo ($response);
				// die;

				$hasil = json_decode($response);

				if ($hasil->result == "sukses") {
					$result["result"] = "sukses";
					$result["data"] = $hasil->data;
					$result["error"] = "";
				} else {
					$result["result"] = "gagal";
					$result["data"] = null;
					$result["error"] = "Tidak ada Data";
				}

			} else {
				$result["result"] = "gagal";
				$result["data"] = null;
				$result["error"] = "kode API salah";
			}
			
			$nama_function_pdf = "proses_pdf_html_".$params["report"];
			$name_function_excel = "proses_excel_".$params["report"];
			//echo json_encode($result["data"]); die;
			if(isset($_POST['btnHTML'])) {
				$this->$nama_function_pdf($page_title, $excel_title, $params, $result["data"],'HTML');
			} elseif(isset($_POST['btnPDF'])) {
				$this->$nama_function_pdf($page_title, $excel_title, $params, $result["data"],'PDF');
			} elseif(isset($_POST['btnExcel'])) {
				$this->$name_function_excel($page_title, $excel_title, $params, $result["data"],'SAVE');
			} 

		}

		/*public function listbarangsesuaidivisi() 
		{
			$data = array();
			$api = 'APITES';
			set_time_limit(60);
            $_POST = $this->PopulatePost();	
            $merk = urldecode($_POST["merk"]);

            //http://localhost:90/webAPI/MsBarang/GetBarangListByMerk?api=APITES&merk=MIYAKO
            $url = $this->API_URL."/MsBarang/GetBarangListByMerk?api=".$api."&merk=".$merk;
            //die($url);
            // open connection
            $curl = curl_init();
            // set the url, number of POST vars, POST data
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_POST => 1,
                CURLOPT_HTTPHEADER => array("Content-type: application/json")
            ));
            // execute post
            $response = curl_exec($curl);
            $err = curl_error($curl);
            // close connection
            curl_close($curl);

            if ($response===false) {
                $lanjut = false;
                $data["messages"] = "API Tujuan OFFLINE";
            } else {         
                $hasil = json_decode($response);
                if ($hasil->result == "sukses") {
                    $data["detailbarang"] = $hasil->data;
                } else {
                    $data["detailbarang"] = "";
                }
                //die($data["detailbarang"]);
                echo json_encode($data["detailbarang"]);
            }
        }*/
    
        /*public function proses_report()
        {
            $keyAPI = 'APITES';
            $api = 'APITES';
            set_time_limit(60);
    
            // // untuk TEST
            // //http://localhost:90/myCompany/Reportopj/Report_6N2_OPJ?api=APITES&wilayah=BANDUNG&salesman=ALL&merk=ALL&bulan=1&tahun=2022&divisi=ALL&partnertype=ALL
            // $params["wilayah"] = urldecode($this->input->get("wilayah"));
            // $params["salesman"] = urldecode($this->input->get("salesman"));
            // $params["merk"] = urldecode($this->input->get("merk"));
            // $params["bulan"] = urldecode($this->input->get("bulan"));
            // $params["tahun"] = urldecode($this->input->get("tahun"));
            // $params["divisi"] = urldecode($this->input->get("divisi"));
            // $params["partnertype"] = urldecode($this->input->get("partnertype"));

      			$_POST = $this->PopulatePost();	
            $params["report"] = urldecode($_POST["report"]);
            $params["kodenotaservice"] = urldecode($_POST["kodenotaservice"]);
            $params["tanggal1"] = urldecode($_POST["tanggal1"]);
            $params["dp11"] = urldecode($_POST["dp11"]);
            $params["dp12"] = urldecode($_POST["dp12"]);
            $params["tanggal2"] = urldecode($_POST["tanggal2"]);
            $params["dp21"] = urldecode($_POST["dp21"]);
            $params["dp22"] = urldecode($_POST["dp22"]);
            $params["merk"] = urldecode($_POST["merk"]);
            $params["kodebarang"] = urldecode($_POST["kodebarang"]);
            $params["teknisi"] = urldecode($_POST["teknisi"]);
            $params["garansi"] = urldecode($_POST["garansi"]);

            $metodebayar= "";
            foreach ($_POST['metodebayar'] as $value){
               $metodebayar .= "'$value'". ",";
            }
            $metodebayar = substr($metodebayar,0,-1);
            $params["metodebayar"] = $metodebayar;

            $params["cetak"] = urldecode($_POST["cetak"]);

            //Checkboxes are not included in the POST data when the form is submitted. You need to call 
            if (isset($_POST['cetakpermerk'])){
                $params["cetakpermerk"]="Y";
            } else {
                $params["cetakpermerk"]="N";
            }

            $params["status"] = urldecode($_POST["status"]);

            //echo json_encode($params);
            // die();

            if($keyAPI==$api) {
                $array_data = array();

                //persiapkan bktAPI
                //$res = $this->MasterDbModel->getByLocationCode($_SESSION['logged_in']['branch_id']);
                $res = $this->MasterDbModel->get($_SESSION['conn']->DatabaseId);
                $AlamatBKTAPI = $res->AlamatWebService;
                $ServerBKTAPI = $res->Server;
                $DatabaseBKTAPI = $res->Database;

                //$page_title Maximum 31 characters allowed in sheet title
                $page_title = "Report Harian Service";
                if ($params["report"]=="01"){
                    $excel_title = "Laporan Harian Service tanpa Detail Sparepart";
                    $url = $AlamatBKTAPI.API_BKT."/Reportservice/Report_01?api=".$api;
                } elseif ($params["report"]=="02"){
                    $excel_title = "Laporan Harian Service dengan Detail Sparepart";
                    $url = $AlamatBKTAPI.API_BKT."/Reportservice/Report_02?api=".$api;
                } elseif ($params["report"]=="03"){
                    $excel_title = "Laporan Ongkos Service";
                    $url = $AlamatBKTAPI.$this->API_BKT."/Reportservice/Report_03?api=".$api; 
                } elseif ($params["report"]=="04"){
                    $excel_title = "Laporan Service berdasarkan Teknisi";
                    $url = $AlamatBKTAPI.API_BKT."/Reportservice/Report_04?api=".$api;
                } elseif ($params["report"]=="05"){
                    $excel_title = "Laporan Produk Masuk Service dari Pembeli/Distributor";
                   $url = $AlamatBKTAPI.API_BKT."/Reportservice/Report_05?api=".$api;
                } elseif ($params["report"]=="06"){
                    $excel_title = "Laporan Service Pajak";
                    $url = $AlamatBKTAPI.$this->API_BKT."/Reportservice/Report_06?api=".$api;
                } elseif ($params["report"]=="07"){
                    $excel_title = "Laporan Service Per Kode Barang By QTY";
                    $url = $AlamatBKTAPI.$this->API_BKT."/Reportservice/Report_07?api=".$api;
                } elseif ($params["report"]=="08"){
                    $excel_title = "Laporan Pemasukan Service";
                    $url = $AlamatBKTAPI.API_BKT."/Reportservice/Report_08?api=".$api;
                } elseif ($params["report"]=="09"){
                    $excel_title = "Laporan Summary Service Harian";
                    $url = $AlamatBKTAPI.$this->API_BKT."/Reportservice/Report_09_10?api=".$api;
                } elseif ($params["report"]=="10"){
                    $excel_title = "Laporan Summary Service Harian Group By Metode Bayar";
                    $url = "";
                }

                $url .= "&report=".urlencode($params["report"]).
                "&kodenotaservice=".urlencode($params["kodenotaservice"]).
                "&tanggal1=".urlencode($params["tanggal1"]).
                "&dp11=".urlencode($params["dp11"]).
                "&dp12=".urlencode($params["dp12"]).
                "&tanggal2=".urlencode($params["tanggal2"]).
                "&dp21=".urlencode($params["dp21"]).
                "&dp22=".urlencode($params["dp22"]).
                "&merk=".urlencode($params["merk"]).
                "&kodebarang=".urlencode($params["kodebarang"]).
                "&teknisi=".urlencode($params["teknisi"]).
                "&garansi=".urlencode($params["garansi"]).
                "&metodebayar=".urlencode($params["metodebayar"]).
                "&cetak=".urlencode($params["cetak"]).
                "&cetakpermerk=".urlencode($params["cetakpermerk"]).
                "&status=".urlencode($params["status"]).
                "&svr=".urlencode($ServerBKTAPI).
                "&db=".urlencode($DatabaseBKTAPI);

                // open connection
                $curl = curl_init();
                // set the url, number of POST vars, POST data
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_POST => 1,
                    CURLOPT_HTTPHEADER => array("Content-type: application/json")
                ));
                // execute post
                $response = curl_exec($curl);
                $err = curl_error($curl);
                // close connection
                curl_close($curl);

                // die;

                $hasil = json_decode($response);

                if ($hasil->result == "sukses") {
                    $result["result"] = "sukses";
                    $result["data"] = $hasil->data;
                    $result["error"] = "";
                } else {
                    $result["result"] = "gagal";
                    $result["data"] = null;
                    $result["error"] = "Tidak ada Data";
                }

            } else {
                $result["result"] = "gagal";
                $result["data"] = null;
                $result["error"] = "kode API salah";
            }
            
            $nama_function_pdf = "proses_pdf_html_".$params["report"];
            $name_function_excel = "proses_excel_".$params["report"];
            // echo json_encode($result["data"]); die;
            if(isset($_POST['btnHTML'])) {
                $this->$nama_function_pdf($page_title, $excel_title, $params, $result["data"],'HTML');
            } elseif(isset($_POST['btnPDF'])) {
                $this->$nama_function_pdf($page_title, $excel_title, $params, $result["data"],'PDF');
            } elseif(isset($_POST['btnExcel'])) {
                $this->$name_function_excel($page_title, $excel_title, $params, $result["data"],'SAVE');
            } 

        }*/

		//01 
		public function proses_excel_01 ($page_title, $excel_title, $param, $DataLaporan, $output)
		{
			$body = array(
				'report' => $DataLaporan,
				'dp11' => $param['dp11'],
				'dp12' => $param['dp12'],
			);
			$this->load->view('template_xls/ReportService01Xls',$body);
		}

		//01 
		public function proses_pdf_html_01 ($page_title, $excel_title, $param, $DataLaporan, $output)
		{
			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'margin_left' => 10,
				'margin_right' => 10,
				'margin_top' => 30,
				'margin_bottom' => 10,
				'margin_header' => 10,
				'margin_footer' => 0,
				'orientation' => 'P'
			));
			$body = array(
				'report' => $DataLaporan,
			);
			$content = $this->load->view('template_pdf/ReportService01Pdf',$body,true);
			$curDate = date("d-F-Y H:i:s");
			$header = <<<HTML
				<p style="margin:0 0;text-align:left;">{$curDate}</p>
				<h1 style="margin:10 0mm;margin-top:0px;width:100%;text-align:center;">LAPORAN BUKU HARIAN SERVICE</h1>
				<h5 style="margin:10 0mm;margin-top:0px;width:100%;text-align:center;">{$param['dp11']} S/D {$param['dp12']}</h5>
				<br>
				
HTML;
			$mpdf->SetHTMLHeader($header);
			$mpdf->WriteHTML($content);
			$mpdf->Output();
		}

		/*//02
		public function proses_excel_02 ($page_title, $excel_title, $param, $DataLaporan, $output)
		{

		}

		//02 
		public function proses_pdf_html_02 ($page_title, $excel_title, $param, $DataLaporan, $output)
		{

		}*/

		//04
		public function proses_excel_04 ($page_title, $excel_title, $param, $DataLaporan, $output)
		{
			$dp11 = date('d-M-Y',strtotime($param['dp11']));
			$dp12 = date('d-M-Y',strtotime($param['dp12']));
			$tanggal1 = str_replace('-',' ',$param['tanggal1']);

			$report = array();
			if($DataLaporan !=''){
				foreach($DataLaporan as $value){
					$value->Nm_Teknisi = rtrim($value->Nm_Teknisi,' ');
					$report[$value->Nm_Teknisi][] = $value;
				}
			}

			$body = array(
				'report' => $report,
				'dp11' => $dp11,
				'dp12' => $dp12,
				'tanggal1' => $tanggal1,
			);
			$this->load->view('template_xls/ReportService04Xls',$body);
		}

		//04 
		public function proses_pdf_html_04 ($page_title, $excel_title, $param, $DataLaporan, $output)
		{
			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'margin_left' => 10,
				'margin_right' => 10,
				'margin_top' => 30,
				'margin_bottom' => 10,
				'margin_header' => 10,
				'margin_footer' => 0,
				'orientation' => 'P'
			));
			$report = array();
			if($DataLaporan !=''){
				foreach($DataLaporan as $value){
					$value->Nm_Teknisi = rtrim($value->Nm_Teknisi,' ');
					$report[$value->Nm_Teknisi][] = $value;
				}
			}
			$body = array(
				'report' => $report,
			);

			$content = $this->load->view('template_pdf/ReportService04Pdf',$body,true);
			$curDate = date("d-F-Y H:i:s");
			$dp11 = date('d-M-Y',strtotime($param['dp11']));
			$dp12 = date('d-M-Y',strtotime($param['dp12']));
			$tanggal1 = str_replace('-',' ',$param['tanggal1']);
			$header = <<<HTML
				<p style="margin:0 0;text-align:left;">{$curDate}</p>
				<h1 style="margin:10 0mm;margin-top:0px;width:100%;text-align:left;">LAPORAN SERVICE BY TEKNISI</h1>
				<h5 style="margin:10 0mm;margin-top:0px;width:100%;text-align:left;">{$tanggal1} : {$dp11} S/D {$dp12}</h5>
				<br>
				
HTML;
			$mpdf->SetHTMLHeader($header);
			$mpdf->WriteHTML($content);
			$mpdf->Output();
		}

		//08
		public function proses_excel_08($page_title, $excel_title, $param, $DataLaporan, $output){
			$dp11 = date('d-M-Y',strtotime($param['dp11']));
			$dp12 = date('d-M-Y',strtotime($param['dp12']));
			$tanggal1 = str_replace('-',' ',$param['tanggal1']);
			$body = array(
				'report' => $DataLaporan,
				'dp11' => $dp11,
				'dp12' => $dp12,
				'tanggal1' => $tanggal1,
			);
			$this->load->view('template_xls/ReportService08Xls',$body);
		}

		//08
		public function proses_pdf_html_08($page_title, $excel_title, $param, $DataLaporan, $output){
			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'margin_left' => 10,
				'margin_right' => 10,
				'margin_top' => 30,
				'margin_bottom' => 10,
				'margin_header' => 10,
				'margin_footer' => 0,
				'orientation' => 'P'
			));
			$body = array(
				'report' => $DataLaporan,
			);
			$content = $this->load->view('template_pdf/ReportService08Pdf',$body,true);
			$curDate = date("d-F-Y H:i:s");
			$dp11 = date('d-M-Y',strtotime($param['dp11']));
			$dp12 = date('d-M-Y',strtotime($param['dp12']));
			$tanggal1 = str_replace('-',' ',$param['tanggal1']);
			$header = <<<HTML
				<p style="margin:0 0;text-align:left;">{$curDate}</p>
				<h1 style="margin:10 0mm;margin-top:0px;width:100%;text-align:left;">LAPORAN PEMASUKAN SERVICE</h1>
				<h5 style="margin:10 0mm;margin-top:0px;width:100%;text-align:left;">{$tanggal1} : {$dp11} S/D {$dp12}</h5>
				<br>
				
HTML;
			$mpdf->SetHTMLHeader($header);
			$mpdf->WriteHTML($content);
			$mpdf->Output();
		}

		//06
		public function proses_excel_06 ($page_title, $excel_title, $param, $DataLaporan, $output)
		{
		  $spreadsheet = new Spreadsheet();
		  $sheet = $spreadsheet->getActiveSheet(0);
		        $PrintDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));
		                    
		        $sheet->setTitle($page_title);
		        $sheet->setCellValue('A1', 'Print Date      : '.$PrintDate);
		        $sheet->setCellValue('A2', 'Print By        : '.$_SESSION['logged_in']['username']);
		        $sheet->setCellValue('A3', $page_title);
		        $sheet->getStyle('A3')->getFont()->setSize(20);
		        $sheet->setCellValue('A4', $excel_title);
		        $sheet->setCellValue('A5', $param["tanggal1"].'  : '.date("d-M-Y", strtotime($param["dp11"]))." - ".date("d-M-Y", strtotime($param["dp12"])));
		        $sheet->setCellValue('A6', 'Merk  : '.$param["merk"]);
		        $sheet->setCellValue('A7', 'Barang  : '.$param["kodebarang"]);
		        $sheet->setCellValue('A8', 'Teknisi  : '.$param["teknisi"]);
		        $sheet->setCellValue('A9', 'Garansi  : '.$param["garansi"]);
		        $sheet->setCellValue('A10', 'Metode Bayar  : '.$param["metodebayar"]);
		        $sheet->setCellValue('A11', 'Cetak  : '.$param["cetak"]);
		        $sheet->setCellValue('A12', 'Cetak Per Merk  : '.$param["cetakpermerk"]);
		        $sheet->setCellValue('A13', 'Status  : '.$param["status"]);

		        if($param["tanggal2"]!="TANGGAL-KOSONG"){
		            $sheet->setCellValue('A14', $param["tanggal2"].'  : '.date("d-M-Y", strtotime($param["dp21"]))." - ".date("d-M-Y", strtotime($param["dp22"])));
		            $currrow = 14;  
		        } else {
		            $currrow = 13;  
		        }

		        $currcol = 1;

		        $currrow++;
		        $currrow++;
		        $currcol = 1;
		        $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Tgl Service Pajak");
		        $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
		        $currcol += 1;   
		        $sheet->setCellValueByColumnAndRow($currcol, $currrow, "No Service Pajak");
		        $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
		        $currcol += 1;   
		        $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Keterangan");
		        $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
		        $currcol += 1;   
		        $sheet->setCellValueByColumnAndRow($currcol, $currrow, "DPP");
		        $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
		        $currcol += 1;
		        $sheet->setCellValueByColumnAndRow($currcol, $currrow, "PPN");
		        $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
		        $currcol += 1;
		        $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Nota Service");
		        $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
		        $currcol += 1;
		        $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Tgl Nota Service");
		        $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1); 
		        $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow+1)->getFont()->setBold(true); 
		        $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow+1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);  
		        $currrow++;

		        $SubTotalDPP = 0;
		        $SubTotalPPN = 0; 
		         
		        $jum= count($DataLaporan);
		        $no = 1;
		        for($i=0; $i<$jum; $i++){ 

		                if(!empty($DataLaporan[$i]->Tgl_Svc) && $DataLaporan[$i]->Tgl_Svc!==null){
		                    $Tgl_Svc = date("d-M-Y", strtotime($DataLaporan[$i]->Tgl_Svc));
		                }else{
		                    $Tgl_Svc = '';
		                }


		                if(!empty($DataLaporan[$i]->Tgl_SvcP) && $DataLaporan[$i]->Tgl_SvcP!==null){
		                    $Tgl_SvcP = date("d-M-Y", strtotime($DataLaporan[$i]->Tgl_SvcP));
		                }else{
		                    $Tgl_SvcP = '';
		                }

		            //start isi data - from here
		            $currrow++;
		            $currcol = 1; 
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Tgl_SvcP);   
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->No_SvcP); 
		            $currcol += 1;  
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Jasa Service");  
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Ongkos_Svc);   
		            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');  
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->PPN);   
		            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');    
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->No_Svc);    
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Tgl_Svc);    


		            $SubTotalDPP = $SubTotalDPP + $DataLaporan[$i]->Ongkos_Svc;
		            $SubTotalPPN = $SubTotalPPN + $DataLaporan[$i]->PPN;  

		            $no++;
		            //start isi data - until here

		        } 
		            $currrow++;
		            $currcol = 1; 
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");   
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, ""); 
		            $currcol += 1;  
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "TOTAL");  
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SubTotalDPP);   
		            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');  
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SubTotalPPN);   
		            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');    
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");    
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");    


		        // warna header
		        $max_col = $currcol-1;
		        $max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
		        $fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
		        //$sheet->getStyle('A'.$currrow.':'.$max_col.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');

		        // autosize column
		        foreach(range('A',$max_col) as $columnID) {
		            $spreadsheet->getActiveSheet(0)->getColumnDimension($columnID)
		                ->setWidth(20);
		        }

		        $filename = $excel_title; //save our workbook as this file name
		        $writer = new Xlsx($spreadsheet);
		        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		        header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
		        header('Cache-Control: max-age=0');
		        ob_end_clean();
		        $writer->save('php://output');  // download file
		        exit(); 

		    }

        //06
        public function proses_pdf_html_06($page_title, $excel_title, $param, $DataLaporan, $output)
        {
            ini_set('max_execution_time', '1500');
            ini_set("pcre.backtrack_limit", "10000000");
            set_time_limit(0);

            require_once __DIR__ . '\vendor\autoload.php';
            $mpdf = new \Mpdf\Mpdf(array(
                'mode' => '',
                'format' => 'A4',
                'default_font_size' => 8,
                'default_font' => 'arial',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 45,
                'margin_bottom' => 10,
                'margin_header' => 10,
                'margin_footer' => 5,
                'orientation' => 'P'
            ));

            $PrintDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7)); 


            $header = '<table border="0" style="width:100%; font-size:15px;">
                    <tr>
                        <td align="center">
                            <b>
                                '.$page_title.'
                            </b>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size:12px;">
                            '.$excel_title.'
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size:12px;">
                        '.trim($param["tanggal1"]).' '.date("d-M-Y", strtotime($param["dp11"])).' <b> - </b> '.date("d-M-Y", strtotime($param["dp12"])).'
                        </td>
                    </tr>';

            $filter = 'Merk '.trim($param["merk"]).', Barang '.trim($param["kodebarang"]).', Teknisi '.trim($param["teknisi"]).', Garansi '.trim($param["garansi"]).', Metode Bayar '.trim($param["metodebayar"]).', Cetak '.trim($param["cetak"]).', Cetak Per Merk '.trim($param["cetakpermerk"]).', Status '.trim($param["status"]);
            if($param["tanggal2"]!="TANGGAL-KOSONG"){
                $header .= '<tr>
                                <td align="center" style="font-size:12px;">
                                    Filter Laporan '.$filter.'
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:12px;">
                                '.trim($param["tanggal2"]).' '.date("d-M-Y", strtotime($param["dp21"])).' <b> - </b> '.date("d-M-Y", strtotime($param["dp22"])).'
                                </td>
                            </tr>
                            </table>';
            } else {
                $header .=  '<tr>
                                <td align="center" style="font-size:12px;">
                                    Filter Laporan '.$filter.'
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:12px;">
                                    Print Date '.$PrintDate.'
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:12px;">
                                    Print By '.$_SESSION['logged_in']['username'].'
                                </td>
                            </tr>
                            </table>';
            }

            $header .= '<table width="100%" style="font-weight: bold;">
                        </table>';
    
            $content = '
            <br><table width="100%" border="1" style=" border-collapse: collapse;"> '; 


            $content .='<tr >
                        <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" >Tgl Faktur P</td>
                        <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" >No Faktur P</td> 
                        <td style="text-align: center; width: 10%; font-size: 12px; padding:5px; font-weight: bold;" >Keterangan</td>
                        <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" >DPP</td>
                        <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" >PPN</td>
                        <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" >Nota Svc</td> 
                        <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" >Tgl Nota Svc</td> 
                        </tr>'; 
 

            $SubTotal_DPP = 0;
            $SubTotal_PPN = 0;

            $jum= count($DataLaporan);
            if ($jum!=0){
                $no = 1;
                for($i=0; $i<$jum; $i++){   


                    if(!empty($DataLaporan[$i]->Tgl_Svc) && $DataLaporan[$i]->Tgl_Svc!==null){
                        $Tgl_Svc = date("d-M-Y", strtotime($DataLaporan[$i]->Tgl_Svc));
                    }else{
                        $Tgl_Svc = '';
                    }


                    if(!empty($DataLaporan[$i]->Tgl_SvcP) && $DataLaporan[$i]->Tgl_SvcP!==null){
                        $Tgl_SvcP = date("d-M-Y", strtotime($DataLaporan[$i]->Tgl_SvcP));
                    }else{
                        $Tgl_SvcP = '';
                    }

                    $content .='<tr> 
                        <td style="text-align: left; font-size: 12px; padding:5px;">'.$Tgl_SvcP.'</td> 
                        <td style="text-align: left; font-size: 12px; padding:5px;">'.$DataLaporan[$i]->No_SvcP.'</td> 
                        <td style="text-align: left; font-size: 12px; padding:5px;">Jasa Service</td> 
                        <td style="text-align: right; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->Ongkos_Svc,0,",",".").'</td> 
                        <td style="text-align: right; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->PPN,0,",",".").'</td> 
                        <td style="text-align: left; font-size: 12px; padding:5px;">'.$DataLaporan[$i]->No_Svc.'</td> 
                        <td style="text-align: left; font-size: 12px; padding:5px;">'.$Tgl_Svc.'</td>  
                    </tr>'; 

                    $SubTotal_DPP = $SubTotal_DPP + $DataLaporan[$i]->Ongkos_Svc;
                    $SubTotal_PPN = $SubTotal_PPN + $DataLaporan[$i]->PPN;  

                    $no++; 
                }


                    $content .='<tr> 
                        <td style="text-align: left; font-size: 12px; padding:5px; font-weight: bold;" colspan="2"></td> 
                        <td style="text-align: right; font-size: 12px; padding:5px; font-weight: bold;">TOTAL</td> 
                        <td style="text-align: right; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SubTotal_DPP,0,",",".").'</td> 
                        <td style="text-align: right; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SubTotal_PPN,0,",",".").'</td>  
                        <td style="text-align: left; font-size: 12px; padding:5px; font-weight: bold;" colspan="2"></td> 
                    </tr>'; 
             
                $content .='</table>';  

                set_time_limit(0);
                if($output=='HTML'){
                    echo ($header.$content);
                } else {
                    $mpdf->SetHTMLHeader($header,'','1');
                    $mpdf->WriteHTML($content);
                    $mpdf->Output();
                }
            } else {
                set_time_limit(0);
                if($output=='HTML'){
                    echo ("Tidak Ada Data");
                } else {
                    $mpdf->SetHTMLHeader("Tidak Ada Data",'','1');
                    $mpdf->WriteHTML("");
                    $mpdf->Output();
                }
            }
        }


		/*//09 Summary Service Harian
		public function proses_excel_09 ($page_title, $excel_title, $param, $DataLaporan, $output)
		{
				$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet(0);
		        $PrintDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));
		                    
		        $sheet->setTitle($page_title);
		        $sheet->setCellValue('A1', 'Print Date      : '.$PrintDate);
		        $sheet->setCellValue('A2', 'Print By        : '.$_SESSION['logged_in']['username']);
		        $sheet->setCellValue('A3', $page_title);
		        $sheet->getStyle('A3')->getFont()->setSize(20);
		        $sheet->setCellValue('A4', $excel_title);
		        $sheet->setCellValue('A5', $param["tanggal1"].'  : '.date("d-M-Y", strtotime($param["dp11"]))." - ".date("d-M-Y", strtotime($param["dp12"])));
		        $sheet->setCellValue('A6', 'Merk  : '.$param["merk"]);
		        $sheet->setCellValue('A7', 'Barang  : '.$param["kodebarang"]);
		        $sheet->setCellValue('A8', 'Teknisi  : '.$param["teknisi"]);
		        $sheet->setCellValue('A9', 'Garansi  : '.$param["garansi"]);
		        $sheet->setCellValue('A10', 'Metode Bayar  : '.$param["metodebayar"]);
		        $sheet->setCellValue('A11', 'Cetak  : '.$param["cetak"]);
		        $sheet->setCellValue('A12', 'Cetak Per Merk  : '.$param["cetakpermerk"]);
		        $sheet->setCellValue('A13', 'Status  : '.$param["status"]);

		        if($param["tanggal2"]!="TANGGAL-KOSONG"){
		            $sheet->setCellValue('A14', $param["tanggal2"].'  : '.date("d-M-Y", strtotime($param["dp21"]))." - ".date("d-M-Y", strtotime($param["dp22"])));
		            $currrow = 14;  
		        } else {
		            $currrow = 13;  
		        }

		        $currcol = 1;

		        $currrow++;
		        $currrow++;
		        $currcol = 1;
		        $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Tgl Service Pajak");
		        $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
		        $currcol += 1;   
		        $sheet->setCellValueByColumnAndRow($currcol, $currrow, "No Service Pajak");
		        $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
		        $currcol += 1;   
		        $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Keterangan");
		        $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
		        $currcol += 1;   
		        $sheet->setCellValueByColumnAndRow($currcol, $currrow, "DPP");
		        $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
		        $currcol += 1;
		        $sheet->setCellValueByColumnAndRow($currcol, $currrow, "PPN");
		        $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
		        $currcol += 1;
		        $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Nota Service");
		        $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
		        $currcol += 1;
		        $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Tgl Nota Service");
		        $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1); 
		        $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow+1)->getFont()->setBold(true); 
		        $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow+1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);  
		        $currrow++;

		        $SubTotalDPP = 0;
		        $SubTotalPPN = 0; 
		         
		        $jum= count($DataLaporan);
		        $no = 1;
		        for($i=0; $i<$jum; $i++){ 

		                if(!empty($DataLaporan[$i]->Tgl_Svc) && $DataLaporan[$i]->Tgl_Svc!==null){
		                    $Tgl_Svc = date("d-M-Y", strtotime($DataLaporan[$i]->Tgl_Svc));
		                }else{
		                    $Tgl_Svc = '';
		                }


		                if(!empty($DataLaporan[$i]->Tgl_SvcP) && $DataLaporan[$i]->Tgl_SvcP!==null){
		                    $Tgl_SvcP = date("d-M-Y", strtotime($DataLaporan[$i]->Tgl_SvcP));
		                }else{
		                    $Tgl_SvcP = '';
		                }

		            //start isi data - from here
		            $currrow++;
		            $currcol = 1; 
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Tgl_SvcP);   
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->No_SvcP); 
		            $currcol += 1;  
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Jasa Service");  
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Ongkos_Svc);   
		            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');  
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->PPN);   
		            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');    
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->No_Svc);    
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Tgl_Svc);    


		            $SubTotalDPP = $SubTotalDPP + $DataLaporan[$i]->Ongkos_Svc;
		            $SubTotalPPN = $SubTotalPPN + $DataLaporan[$i]->PPN;  

		            $no++;
		            //start isi data - until here

		        } 
		            $currrow++;
		            $currcol = 1; 
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");   
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, ""); 
		            $currcol += 1;  
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "TOTAL");  
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SubTotalDPP);   
		            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');  
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SubTotalPPN);   
		            $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');    
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");    
		            $currcol += 1;
		            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");    


		        // warna header
		        $max_col = $currcol-1;
		        $max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
		        $fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
		        //$sheet->getStyle('A'.$currrow.':'.$max_col.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');

		        // autosize column
		        foreach(range('A',$max_col) as $columnID) {
		            $spreadsheet->getActiveSheet(0)->getColumnDimension($columnID)
		                ->setWidth(20);
		        }

		        $filename = $excel_title; //save our workbook as this file name
		        $writer = new Xlsx($spreadsheet);
		        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		        header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
		        header('Cache-Control: max-age=0');
		        ob_end_clean();
		        $writer->save('php://output');  // download file
		        exit(); 

        }*/

          
        //03
        public function proses_excel_03 ($page_title, $excel_title, $param, $DataLaporan, $output)
        {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet(0);

            $PrintDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));
                        
            $sheet->setTitle($page_title);
            $sheet->setCellValue('A1', 'Print Date      : '.$PrintDate);
            $sheet->setCellValue('A2', 'Print By        : '.$_SESSION['logged_in']['username']);
            $sheet->setCellValue('A3', $page_title);
            $sheet->getStyle('A3')->getFont()->setSize(20);
            $sheet->setCellValue('A4', $excel_title);
            $sheet->setCellValue('A5', $param["tanggal1"].'  : '.date("d-M-Y", strtotime($param["dp11"]))." - ".date("d-M-Y", strtotime($param["dp12"])));
            $sheet->setCellValue('A6', 'Merk  : '.$param["merk"]);
            $sheet->setCellValue('A7', 'Barang  : '.$param["kodebarang"]);
            $sheet->setCellValue('A8', 'Teknisi  : '.$param["teknisi"]);
            $sheet->setCellValue('A9', 'Garansi  : '.$param["garansi"]);
            $sheet->setCellValue('A10', 'Metode Bayar  : '.$param["metodebayar"]);
            $sheet->setCellValue('A11', 'Cetak  : '.$param["cetak"]);
            $sheet->setCellValue('A12', 'Cetak Per Merk  : '.$param["cetakpermerk"]);
            $sheet->setCellValue('A13', 'Status  : '.$param["status"]);
  
            if($param["tanggal2"]!="TANGGAL-KOSONG"){
                $sheet->setCellValue('A14', $param["tanggal2"].'  : '.date("d-M-Y", strtotime($param["dp21"]))." - ".date("d-M-Y", strtotime($param["dp22"])));
                $currrow = 14;  
            } else {
                $currrow = 13;  
            }

            $currcol = 1;

            $currrow++;
            $currrow++;
            $currcol = 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;  
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "SET REPARASI");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+1 , $currrow);
            $sheet->setCellValueByColumnAndRow($currcol, $currrow+1, "IN");
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow+1, "OUT");
            $currcol += 1; 
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "SPAREPART PENJUALAN");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "SPAREPART JAMINAN");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "ONGKOS KERJA REPARASI");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "ONGKOS TRANSPORT / HOME SERVICE");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1); 
            $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow+1)->getFont()->setBold(true); 
            $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow+1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);  
            $currrow++;

            $Sub_totalIN = 0;
            $Sub_totalOut = 0;
            $Sub_totalFk = 0;
            $Sub_totalSJ = 0;
            $Sub_OngkosSvc = 0;
            $Sub_HomeSvc = 0;
             
            $jum= count($DataLaporan);
            $no = 1;
            for($i=0; $i<$jum; $i++){ 
                //start isi data - from here
                $currrow++;
                $currcol = 1; 
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Merk);   
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->InGaransi); 
                $currcol += 1;  
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->OutGaransi);  
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->totalFK);   
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');  
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->totalSJ);   
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');    
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Ongkos_Svc);   
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');  
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Home_Svc);   
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');   




                    $Sub_totalIN = $Sub_totalIN + $DataLaporan[$i]->InGaransi;
                    $Sub_totalOut = $Sub_totalOut + $DataLaporan[$i]->OutGaransi; 
                    $Sub_totalFk = $Sub_totalFk + $DataLaporan[$i]->totalFK; 
                    $Sub_totalSJ = $Sub_totalSJ + $DataLaporan[$i]->totalSJ;
                    $Sub_OngkosSvc = $Sub_OngkosSvc + $DataLaporan[$i]->Ongkos_Svc;
                    $Sub_HomeSvc = $Sub_HomeSvc + $DataLaporan[$i]->Home_Svc;

                $no++;
                //start isi data - until here

            }


                $currrow++;
                $currcol = 1; 
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, "TOTAL");   
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Sub_totalIN); 
                $currcol += 1;  
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Sub_totalOut);  
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Sub_totalFk);   
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');  
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Sub_totalSJ);   
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');    
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Sub_OngkosSvc);   
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');  
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Sub_HomeSvc);   
                $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');   
 

            // warna header
            $max_col = $currcol-1;
            $max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
            $fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
            //$sheet->getStyle('A'.$currrow.':'.$max_col.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
    
            // autosize column
            foreach(range('D',$max_col) as $columnID) {
                $spreadsheet->getActiveSheet(0)->getColumnDimension($columnID)
                    ->setAutoSize(true);
            }

            $filename = $excel_title; //save our workbook as this file name
            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
            header('Cache-Control: max-age=0');
            ob_end_clean();
            $writer->save('php://output');  // download file
            exit(); 

        }

        //03 
        public function proses_pdf_html_03($page_title, $excel_title, $param, $DataLaporan, $output)
        {
            ini_set('max_execution_time', '1500');
            ini_set("pcre.backtrack_limit", "10000000");
            set_time_limit(0);

            require_once __DIR__ . '\vendor\autoload.php';
            $mpdf = new \Mpdf\Mpdf(array(
                'mode' => '',
                'format' => 'A4',
                'default_font_size' => 8,
                'default_font' => 'arial',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 45,
                'margin_bottom' => 10,
                'margin_header' => 10,
                'margin_footer' => 5,
                'orientation' => 'P'
            ));

            $PrintDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7)); 


            $header = '<table border="0" style="width:100%; font-size:15px;">
                    <tr>
                        <td align="center">
                            <b>
                                '.$page_title.'
                            </b>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size:12px;">
                            '.$excel_title.'
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size:12px;">
                        '.trim($param["tanggal1"]).' '.date("d-M-Y", strtotime($param["dp11"])).' <b> - </b> '.date("d-M-Y", strtotime($param["dp12"])).'
                        </td>
                    </tr>';

            $filter = 'Merk '.trim($param["merk"]).', Barang '.trim($param["kodebarang"]).', Teknisi '.trim($param["teknisi"]).', Garansi '.trim($param["garansi"]).', Metode Bayar '.trim($param["metodebayar"]).', Cetak '.trim($param["cetak"]).', Cetak Per Merk '.trim($param["cetakpermerk"]).', Status '.trim($param["status"]);
            if($param["tanggal2"]!="TANGGAL-KOSONG"){
                $header .= '<tr>
                                <td align="center" style="font-size:12px;">
                                    Filter Laporan '.$filter.'
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:12px;">
                                '.trim($param["tanggal2"]).' '.date("d-M-Y", strtotime($param["dp21"])).' <b> - </b> '.date("d-M-Y", strtotime($param["dp22"])).'
                                </td>
                            </tr>
                            </table>';
            } else {
                $header .=  '<tr>
                                <td align="center" style="font-size:12px;">
                                    Filter Laporan '.$filter.'
                                </td>
                            </tr> 
                            <tr>
                                <td align="center" style="font-size:12px;">
                                    Print Date '.$PrintDate.'
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:12px;">
                                    Print By '.$_SESSION['logged_in']['username'].'
                                </td>
                            </tr>
                            </table>';
            }

            $header .= '<table width="100%" style="font-weight: bold;">
                        </table>';
    
            $content = '
            <br><table width="100%" border="1" style=" border-collapse: collapse;"> '; 


            $content .='<tr >
                        <td style="text-align: center; width: 20%; font-size: 12px; padding:5px; font-weight: bold;" rowspan="2"></td>
                        <td style="text-align: center; width: 20%; font-size: 12px; padding:5px; font-weight: bold;" colspan="2">SET REPARASI</td> 
                        <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" rowspan="2">SPAREPART PENJUALAN</td>
                        <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" rowspan="2">SPAREPART JAMINAN</td>
                        <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" rowspan="2">ONGKOS KERJA REPARASI</td>
                        <td style="text-align: center; width: 15%; font-size: 12px; padding:5px; font-weight: bold;" rowspan="2">ONGKOS TRANSPORT / HOME SERVICE</td> 
                        </tr>';

            $content .='<tr> 
                        <td style="text-align: center; width: 10%;font-size: 12px; padding:5px; font-weight: bold;">IN</td> 
                        <td style="text-align: center; width: 10%;font-size: 12px; padding:5px; font-weight: bold;">OUT</td> 
                        </tr>';

            $Sub_totalIN = 0;
            $Sub_totalOut = 0;
            $Sub_totalFk = 0;
            $Sub_totalSJ = 0;
            $Sub_OngkosSvc = 0;
            $Sub_HomeSvc = 0;

            $jum= count($DataLaporan);
            if ($jum!=0){
                $no = 1;
                for($i=0; $i<$jum; $i++){  
                    $content .='<tr> 
                        <td style="text-align: left; font-size: 12px; padding:5px; font-weight: bold;">'.$DataLaporan[$i]->Merk.'</td> 
                        <td style="text-align: center; font-size: 12px; padding:5px;">'.$DataLaporan[$i]->InGaransi.'</td> 
                        <td style="text-align: center; font-size: 12px; padding:5px;">'.$DataLaporan[$i]->OutGaransi.'</td> 
                        <td style="text-align: right; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->totalFK,0,",",".").'</td> 
                        <td style="text-align: right; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->totalSJ,0,",",".").'</td> 
                        <td style="text-align: right; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->Ongkos_Svc,0,",",".").'</td> 
                        <td style="text-align: right; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->Home_Svc,0,",",".").'</td>  
                    </tr>'; 

                    $Sub_totalIN = $Sub_totalIN + $DataLaporan[$i]->InGaransi;
                    $Sub_totalOut = $Sub_totalOut + $DataLaporan[$i]->OutGaransi; 
                    $Sub_totalFk = $Sub_totalFk + $DataLaporan[$i]->totalFK; 
                    $Sub_totalSJ = $Sub_totalSJ + $DataLaporan[$i]->totalSJ;
                    $Sub_OngkosSvc = $Sub_OngkosSvc + $DataLaporan[$i]->Ongkos_Svc;
                    $Sub_HomeSvc = $Sub_HomeSvc + $DataLaporan[$i]->Home_Svc;

                    $no++; 
                }


                    $content .='<tr> 
                        <td style="text-align: left; font-size: 12px; padding:5px; font-weight: bold;">TOTAL</td> 
                        <td style="text-align: center; font-size: 12px; padding:5px; font-weight: bold;">'.$Sub_totalIN.'</td> 
                        <td style="text-align: center; font-size: 12px; padding:5px; font-weight: bold;">'.$Sub_totalOut.'</td> 
                        <td style="text-align: right; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($Sub_totalFk,0,",",".").'</td> 
                        <td style="text-align: right; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($Sub_totalSJ,0,",",".").'</td> 
                        <td style="text-align: right; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($Sub_OngkosSvc,0,",",".").'</td> 
                        <td style="text-align: right; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($Sub_HomeSvc,0,",",".").'</td>  
                    </tr>'; 
             
                $content .='</table>';  

                set_time_limit(0);
                if($output=='HTML'){
                    echo ($header.$content);
                } else {
                    $mpdf->SetHTMLHeader($header,'','1');
                    $mpdf->WriteHTML($content);
                    $mpdf->Output();
                }
            } else {
                set_time_limit(0);
                if($output=='HTML'){
                    echo ("Tidak Ada Data");
                } else {
                    $mpdf->SetHTMLHeader("Tidak Ada Data",'','1');
                    $mpdf->WriteHTML("");
                    $mpdf->Output();
                }
            }
        }

        //07
        public function proses_excel_07 ($page_title, $excel_title, $param, $DataLaporan, $output)
        {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet(0);

            $PrintDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));
                        
            $sheet->setTitle($page_title);
            $sheet->setCellValue('A1', 'Print Date      : '.$PrintDate);
            $sheet->setCellValue('A2', 'Print By        : '.$_SESSION['logged_in']['username']);
            $sheet->setCellValue('A3', $page_title);
            $sheet->getStyle('A3')->getFont()->setSize(20);
            $sheet->setCellValue('A4', $excel_title);
            $sheet->setCellValue('A5', $param["tanggal1"].'  : '.date("d-M-Y", strtotime($param["dp11"]))." - ".date("d-M-Y", strtotime($param["dp12"])));
            $sheet->setCellValue('A6', 'Merk  : '.$param["merk"]);
            $sheet->setCellValue('A7', 'Barang  : '.$param["kodebarang"]);
            $sheet->setCellValue('A8', 'Teknisi  : '.$param["teknisi"]);
            $sheet->setCellValue('A9', 'Garansi  : '.$param["garansi"]);
            $sheet->setCellValue('A10', 'Metode Bayar  : '.$param["metodebayar"]);
            $sheet->setCellValue('A11', 'Cetak  : '.$param["cetak"]);
            $sheet->setCellValue('A12', 'Cetak Per Merk  : '.$param["cetakpermerk"]);
            $sheet->setCellValue('A13', 'Status  : '.$param["status"]);
  
            if($param["tanggal2"]!="TANGGAL-KOSONG"){
                $sheet->setCellValue('A14', $param["tanggal2"].'  : '.date("d-M-Y", strtotime($param["dp21"]))." - ".date("d-M-Y", strtotime($param["dp22"])));
                $currrow = 14;  
            } else {
                $currrow = 13;  
            }

            $currcol = 1;

            $currrow++;
            $currrow++; 

            $SubTotalOngkos_Svc = 0; 

            $Merk = ""; 
            $Jns_Brg = "";
             
            $jum= count($DataLaporan);
            $no = 1;
            for($i=0; $i<$jum; $i++){ 
                //start isi data - from here

                if ($Merk=="")
                {  
                    $currrow++;
                    $currcol = 1; 
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Merk);
                    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true)->setSize(15); 
                }
                else
                { 
                    if ($Merk!=$DataLaporan[$i]->Merk)
                    { 
                        $currrow++;
                        $currcol = 1; 
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Merk);
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true); 
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SubTotalOngkos_Svc); 
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);  
                        $currrow++; 
 
                        $currrow++;
                        $currcol = 1; 
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Merk);
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true)->setSize(15);  
                        $Jns_Brg = "";
                        $SubTotalOngkos_Svc = 0; 
                    }
                }

                if ($Jns_Brg=="")
                {  
                    $currrow++;
                    $currcol = 1; 
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Jns_Brg);
                    $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);  
                }
                else
                { 
                    if ($Jns_Brg!=$DataLaporan[$i]->Jns_Brg)
                    {  
                        $currrow++;
                        $currrow++;
                        $currcol = 1; 
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Jns_Brg);
                        $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);   
                        $Jns_Brg = "";
                        $SubTotalOngkos_Svc = 0; 
                    }
                }

                $currrow++;
                $currcol = 1; 
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Kd_Brg);   
                $currcol += 1;
                $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Ongkos_Svc);  
  

                $SubTotalOngkos_Svc = $SubTotalOngkos_Svc + $DataLaporan[$i]->Ongkos_Svc; 

                $Merk = $DataLaporan[$i]->Merk;
                $Jns_Brg = $DataLaporan[$i]->Jns_Brg;

                $no++;
                //start isi data - until here 
            }  

            $currrow++;
            $currcol = 1; 
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Merk);
            $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true); 
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SubTotalOngkos_Svc); 
            $sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);   

            // warna header
            $max_col = $currcol-1;
            $max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
            $fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
            //$sheet->getStyle('A'.$currrow.':'.$max_col.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
    
            // autosize column
            foreach(range('A',$max_col) as $columnID) {
                $spreadsheet->getActiveSheet(0)->getColumnDimension($columnID)
                    ->setWidth(20);
            }

            $filename = $excel_title; //save our workbook as this file name
            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
            header('Cache-Control: max-age=0');
            ob_end_clean();
            $writer->save('php://output');  // download file
            exit(); 

        }

        //06
        public function proses_pdf_html_07($page_title, $excel_title, $param, $DataLaporan, $output)
        {
            ini_set('max_execution_time', '1500');
            ini_set("pcre.backtrack_limit", "10000000");
            set_time_limit(0);

            require_once __DIR__ . '\vendor\autoload.php';
            $mpdf = new \Mpdf\Mpdf(array(
                'mode' => '',
                'format' => 'A4',
                'default_font_size' => 8,
                'default_font' => 'arial',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 45,
                'margin_bottom' => 10,
                'margin_header' => 10,
                'margin_footer' => 5,
                'orientation' => 'P'
            ));
            $PrintDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));  

            $header = '<table border="0" style="width:100%; font-size:15px;"> 
                    <tr>
                        <td align="center">
                            <b>
                                '.$page_title.'
                            </b>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size:12px;">
                            '.$excel_title.'
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size:12px;">
                        '.trim($param["tanggal1"]).' '.date("d-M-Y", strtotime($param["dp11"])).' <b> - </b> '.date("d-M-Y", strtotime($param["dp12"])).'
                        </td>
                    </tr>';

            $filter = 'Merk '.trim($param["merk"]).', Barang '.trim($param["kodebarang"]).', Teknisi '.trim($param["teknisi"]).', Garansi '.trim($param["garansi"]).', Metode Bayar '.trim($param["metodebayar"]).', Cetak '.trim($param["cetak"]).', Cetak Per Merk '.trim($param["cetakpermerk"]).', Status '.trim($param["status"]);
            if($param["tanggal2"]!="TANGGAL-KOSONG"){
                $header .= '<tr>
                                <td align="center" style="font-size:12px;">
                                    Filter Laporan '.$filter.'
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:12px;">
                                '.trim($param["tanggal2"]).' '.date("d-M-Y", strtotime($param["dp21"])).' <b> - </b> '.date("d-M-Y", strtotime($param["dp22"])).'
                                </td>
                            </tr>
                            </table>';
            } else {
                $header .=  '<tr>
                                <td align="center" style="font-size:12px;">
                                    Filter Laporan '.$filter.'
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:12px;">
                                    Print Date '.$PrintDate.'
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:12px;">
                                    Print By '.$_SESSION['logged_in']['username'].'
                                </td>
                            </tr>
                            </table>';
            }

            $header .= '<table width="100%" style="font-weight: bold;">
                        </table>'; 
            $SubTotalOngkos_Svc = 0; 

            $Merk = ""; 
            $Jns_Brg = "";


            $jum= count($DataLaporan);
            if ($jum!=0){

                //Button PREVIEW CLICK
                if($output=='HTML'){

                    $content =  '<head>
                        <meta name="viewport" content= "width=device-width, initial-scale=1.0">
                        <style>
                            div {
                                margin: 10px;
                            }
                      
                            .first {
                                width: 20%;
                                display: inline-block; 
                            } 
                        </style>
                    </head> 
                    <body> ';

                    $batas = 0;
                    for($i=0; $i<$jum; $i++){ 

                            if ($batas==0)
                            { 
                                $content .= '<div class="first"> <p>'; 
                                $content .= '<table>';
                            }
                            else
                            {
                                if ($batas>50)
                                {   
                                    $content .= '</table> <br>'; 
                                    $content .= '</p> </div>'; 
                                    $content .= '<div class="first"> <p>'; 
                                    $content .= '<table>'; 
                                    $batas = 0;
                                }
                            }

                            if ($Merk=="") {   
                                $content .= ' <tr> <td><b>'.$DataLaporan[$i]->Merk. '</b></td> </tr>';
                                $batas++; //enter sekali
                            }
                            else{
                                if ($Merk!=$DataLaporan[$i]->Merk)
                                { 
                                    $content .= ' <tr> <td><b>TOTAL</b></td> <td><b>'.$SubTotalOngkos_Svc. '</b></td> </tr>';
                                    $batas++;
                                    $content.=' <tr><td colspan="2" style="padding:10px;"></td></tr>';
                                    $batas++;
                                    $content .= ' <tr> <td><b>'.$DataLaporan[$i]->Merk. '</b></td> </tr>';
                                    $batas++;    //enter sekali
                                }

                            } 

                            if ($Jns_Brg=="") {   
                                $content .= ' <tr> <td><b>'.$DataLaporan[$i]->Jns_Brg. '</b></td> </tr>';
                                $batas++; //enter sekali
                            }
                            else{
                                if ($Jns_Brg!=$DataLaporan[$i]->Jns_Brg)
                                {   
                                    $content .= ' <tr> <td><b>'.$DataLaporan[$i]->Jns_Brg. '</b></td> </tr>';
                                    $batas++;    //enter sekali
                                } 
                            } 

                            $content .= ' <tr> <td>'.$DataLaporan[$i]->Kd_Brg. '</td> <td>'.$DataLaporan[$i]->Ongkos_Svc. '</td> </tr>';
                            $batas++;   

                            $Merk=$DataLaporan[$i]->Merk ;  
                            $Jns_Brg=$DataLaporan[$i]->Jns_Brg ;     
                            $SubTotalOngkos_Svc = $SubTotalOngkos_Svc+$DataLaporan[$i]->Ongkos_Svc;
                    } 

                    $content .= ' <tr> <td><b>TOTAL</b></td> <td><b>'.$SubTotalOngkos_Svc. '</b></td> </tr>'; 
                    $content .= '</table> </p> </div> </body>';

                    echo ($header.$content); 
                } 

                //Button PDF CLICK
                else { 
                    $mpdf->SetHTMLHeader($header,'','1');
                    $mpdf->SetColumns(3);
                    $no = 1;
                    for($i=0; $i<$jum; $i++){   
      
                        if ($Merk=="")
                        {
                            $content = '<table> ';
                            $content .='<tr>
                            <td style="text-align: left; width: 70%;font-size: 14px; font-weight: bold;" >'.$DataLaporan[$i]->Merk.'</td> 
                            </tr>';      
                        }
                        else
                        { 
                            if ($Merk!=$DataLaporan[$i]->Merk)
                            {
                                $content .='<tr> 
                                    <td style="text-align: left; font-size: 11px; font-weight: bold;">'.$Merk.'</td> 
                                    <td style="text-align: left; font-size: 11px; font-weight: bold;">'.$SubTotalOngkos_Svc.'</td>   
                                </tr>'; 
                                $content .= '</table> ';

                                $mpdf->AddPage();
                                $mpdf->keepColumns = true;
                                $mpdf->WriteHTML($content); 

                                $content = '<table> ';
                                $content .='<tr>
                                <td style="text-align: left;font-size: 14px; font-weight: bold;" >'.$DataLaporan[$i]->Merk.'</td> 
                                </tr>'; 
                                $Jns_Brg = "";
                                $SubTotalOngkos_Svc = 0; 
                            }
                        }

                        

                        if ($Jns_Brg=="")
                        {
                            $content .='<tr>
                            <td style="text-align: left;font-size: 11px; font-weight: bold;" >'.$DataLaporan[$i]->Jns_Brg.'</td> 
                            </tr>';      
                        }
                        else
                        { 
                            if ($Jns_Brg!=$DataLaporan[$i]->Jns_Brg)
                            {
                                $content .='<tr><td colspan="2" style="padding:3px;"></td></tr>';
                                $content .='<tr>
                                <td style="text-align: left;font-size: 11px; font-weight: bold;" >'.$DataLaporan[$i]->Jns_Brg.'</td> 
                                </tr>'; 
                            }
                        }
                          

                        $content .='<tr> 
                            <td style="text-align: left; font-size: 11px; ">'.$DataLaporan[$i]->Kd_Brg.'</td> 
                            <td style="text-align: left; font-size: 11px; ">'.$DataLaporan[$i]->Ongkos_Svc.'</td>   
                        </tr>'; 

                        $SubTotalOngkos_Svc = $SubTotalOngkos_Svc + $DataLaporan[$i]->Ongkos_Svc; 
                        $Merk = $DataLaporan[$i]->Merk;
                        $Jns_Brg = $DataLaporan[$i]->Jns_Brg;

                        if ($jum==$no)
                        {
                            $content .='<tr> 
                                    <td style="text-align: left; font-size: 11px; font-weight: bold;">'.$Merk.'</td> 
                                    <td style="text-align: left; font-size: 11px; font-weight: bold;">'.$SubTotalOngkos_Svc.'</td>   
                                    </tr>'; 
                           $content .= '</table> ';

                           $mpdf->AddPage();
                           $mpdf->WriteHTML($content); 
                        }

                        $no++; 

                    } 
                    $mpdf->Output(); 
                } 
               
            } else {
                set_time_limit(0);
                if($output=='HTML'){
                    echo ("Tidak Ada Data");
                } else {
                    $mpdf->SetHTMLHeader("Tidak Ada Data",'','1');
                    $mpdf->WriteHTML("");
                    $mpdf->Output();
                }
            }
        }


        //02
        public function proses_excel_02 ($page_title, $excel_title, $param, $DataLaporan, $output)
        {
            $spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);

            $PrintDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));
						
			$sheet->setTitle($page_title);
            $sheet->setCellValue('A1', 'Print Date      : '.$PrintDate);
            $sheet->setCellValue('A2', 'Print By        : '.$_SESSION['logged_in']['username']);
            $sheet->setCellValue('A3', $page_title);
            $sheet->getStyle('A3')->getFont()->setSize(20);
            $sheet->setCellValue('A4', $excel_title);
            $sheet->setCellValue('A5', $param["tanggal1"].'  : '.date("d-M-Y", strtotime($param["dp11"]))." - ".date("d-M-Y", strtotime($param["dp12"])));
            $sheet->setCellValue('A6', 'Merk  : '.$param["merk"]);
            $sheet->setCellValue('A7', 'Barang  : '.$param["kodebarang"]);
            $sheet->setCellValue('A8', 'Teknisi  : '.$param["teknisi"]);
            $sheet->setCellValue('A9', 'Garansi  : '.$param["garansi"]);
            $sheet->setCellValue('A10', 'Metode Bayar  : '.$param["metodebayar"]);
            $sheet->setCellValue('A11', 'Cetak  : '.$param["cetak"]);
            $sheet->setCellValue('A12', 'Cetak Per Merk  : '.$param["cetakpermerk"]);
            $sheet->setCellValue('A13', 'Status  : '.$param["status"]);

            if($param["tanggal2"]!="TANGGAL-KOSONG"){
                $sheet->setCellValue('A14', $param["tanggal2"].'  : '.date("d-M-Y", strtotime($param["dp21"]))." - ".date("d-M-Y", strtotime($param["dp22"])));
                $currrow = 14;	
            } else {
                $currrow = 13;	
            }

			$currcol = 1;


            // Merk, Kd_Brg, Jns_Brg, No_Svc, Tgl_Svc, PPN, Rate_PPN, 
			// Ongkos_Svc, Home_Svc, Type_Svc, No_Faktur, 
			// Tipe_Faktur, Kerusakan, Perbaikan, Selesai, Kembali, Jaminan, Cancelled,
			// SUBTOTAL, Harga, QTY, kd_sparepart, nm_Sparepart, Disc_Tambahan
            $currrow++;
            $currrow++;
            $currcol = 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Type Svc");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Tgl Svc");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "No Svc");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Merk");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Kd Brg");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Kerusakan");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Perbaikan");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;            
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "No Faktur");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Tipe Faktur");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "kd sparepart");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "nm Sparepart");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "QTY");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Harga");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "SUBTOTAL");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Rate PPN");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Ongkos Svc");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Home Svc");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "PPN");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Disc Tambahan");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Grandtotal");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow+1)->getFont()->setBold(true);	
            $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow+1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);	
            $currrow++;


            $No_Svc = "!@#$%";
            $PPN = 0;
            $Grandtotal = 0;
            $HargaSP = 0;

            $Rate_PPN = 0;
            $Ongkos_Svc = 0;
            $PPN = 0;
            $Home_Svc = 0;
            $Disc_Tambahan = 0;
            $Grandtotal = 0;

            $jum= count($DataLaporan);
            $no = 1;
			for($i=0; $i<$jum; $i++) {

                if ( $No_Svc != $DataLaporan[$i]->No_Svc and $No_Svc != "!@#$%") {

                    // $PPN = $Rate_PPN * $Ongkos_Svc / 100;
                    // $Grandtotal = $HargaSP + $PPN + $Ongkos_Svc + $Home_Svc - $Disc_Tambahan;

                    // $currrow++;                       
                    $currcol = 15;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Rate_PPN);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Ongkos_Svc);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Home_Svc);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $PPN);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Disc_Tambahan);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Grandtotal);
                    $currcol += 1;
                    $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow+1)->getFont()->setBold(true);	
                    $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow+1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);	
                    $currrow++;     
                    
                    $HargaSP = 0;

                    $Rate_PPN = 0;
                    $Ongkos_Svc = 0;
                    $PPN = 0;
                    $Home_Svc = 0;
                    $Disc_Tambahan = 0;
                    $Grandtotal = 0;
                } 

                if ( $No_Svc != $DataLaporan[$i]->No_Svc ) {
                    
                    $currrow++;
                    $currcol = 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Type_Svc);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Tgl_Svc);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->No_Svc);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Merk);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Kd_Brg);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Kerusakan);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Perbaikan);
                    $currcol += 1;                    
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->No_Faktur);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Tipe_Faktur);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Kd_Sparepart);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Nm_Sparepart);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Qty);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Harga);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Subtotal);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;

                    $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow+1)->getFont()->setBold(true);	
                    $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow+1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);	
                    // $currrow++;

                }
                else {
                    $currrow++;
                    $currcol = 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->No_Faktur);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Tipe_Faktur);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Kd_Sparepart);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Nm_Sparepart);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Qty);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Harga);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Subtotal);
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, "");
                    $currcol += 1;
                    $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow+1)->getFont()->setBold(true);	
                    $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow+1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);	
                    // $currrow++;
                }

                $HargaSP += $DataLaporan[$i]->Subtotal;
                $No_Svc = $DataLaporan[$i]->No_Svc;
        
                $Rate_PPN = $DataLaporan[$i]->Rate_PPN;
                $Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
                $PPN = $Rate_PPN * $Ongkos_Svc / 100;

                $Home_Svc = $DataLaporan[$i]->Home_Svc;
                $Disc_Tambahan = $DataLaporan[$i]->Disc_Tambahan;
                $Grandtotal = $HargaSP + $PPN + $Ongkos_Svc + $Home_Svc - $Disc_Tambahan;

                // $currrow++;
            }

            $currcol = 15;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Rate_PPN);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Ongkos_Svc);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Home_Svc);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $PPN);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Disc_Tambahan);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, $Grandtotal);
            $currcol += 1;
            $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow+1)->getFont()->setBold(true);	
            $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow+1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);	
            $currrow++;  


            // warna header
            $max_col = $currcol-1;
            $max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A15:'.$max_col.'16')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
	
            // autosize column
            foreach(range('B',$max_col) as $columnID) {
                $spreadsheet->getActiveSheet(0)->getColumnDimension($columnID)
                    ->setAutoSize(true);
            }

            $filename = $excel_title; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
            ob_end_clean();
            $writer->save('php://output');	// download file
            exit(); 
        }

        //02 
        public function proses_pdf_html_02 ($page_title, $excel_title, $param, $DataLaporan, $output)
        {
            ini_set('max_execution_time', '1500');
			ini_set("pcre.backtrack_limit", "10000000");
			set_time_limit(0);

			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'arial',
				'margin_left' => 5,
				'margin_right' => 5,
				'margin_top' => 40,
				'margin_bottom' => 10,
				'margin_header' => 10,
				'margin_footer' => 5,
				'orientation' => 'P'
			));

            $PrintDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));

            $header = "";
            $content = "";

            $header = '<table border="0" style="width:100%; font-size:10px;">
                    <tr>
                        <td align="center" style="font-size:10px;">
                            Print Date '.$PrintDate.'
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size:10px;">
                            Print By '.$_SESSION['logged_in']['username'].'
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size:10px;">
                            <b>
                                '.$page_title.'
                            </b>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size:10px;">
                            '.$excel_title.'
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size:10px;">
                        '.trim($param["tanggal1"]).' '.date("d-M-Y", strtotime($param["dp11"])).' <b> - </b> '.date("d-M-Y", strtotime($param["dp12"])).'
                        </td>
                    </tr>'
            ;

            $filter = 'Merk '.trim($param["merk"]).', Barang '.trim($param["kodebarang"]).', Teknisi '.trim($param["teknisi"]).', Garansi '.trim($param["garansi"]).', Metode Bayar '.trim($param["metodebayar"]).', Cetak '.trim($param["cetak"]).', Cetak Per Merk '.trim($param["cetakpermerk"]).', Status '.trim($param["status"]);
            if($param["tanggal2"]!="TANGGAL-KOSONG"){
                $header .= '<tr>
                                <td align="center" style="font-size:10px;">
                                    Filter Laporan '.$filter.'
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:10px;">
                                '.trim($param["tanggal2"]).' '.date("d-M-Y", strtotime($param["dp21"])).' <b> - </b> '.date("d-M-Y", strtotime($param["dp22"])).'
                                </td>
                            </tr>
                            </table>'
                ;
            } else {
                $header .= 	'<tr>
                                <td align="center" style="font-size:10px;">
                                    Filter Laporan '.$filter.'
                                </td>
                            </tr></table>'
                ;
            }

            $header .= '<table width="100%" style="font-weight: bold;">
                        </table>'
            ;

            
            
            $content.= "<style> th, td { font-size:8px; } </style>";			
            $content.= "<div class='div_body' style='overflow-x:padding-left:1px;'>";
            $content.= "	<table style='width:100%'><tr>";
            $content.= "		<th style='width:2%; border-bottom:thin solid #333; border-top:thin solid #333;'></th>";
            $content.= "		<th style='width:2%; border-bottom:thin solid #333; border-top:thin solid #333;'>No Faktur</th>";
            $content.= "		<th style='width:1%; border-bottom:thin solid #333; border-top:thin solid #333;'>Tipe</th>";
            $content.= "		<th style='width:5%; border-bottom:thin solid #333; border-top:thin solid #333;'>Kode Sparepart</th>";
            $content.= "		<th style='width:8%; border-bottom:thin solid #333; border-top:thin solid #333;'>Nama Sparepart</th>";
            $content.= "		<th style='width:1%;  border-bottom:thin solid #333; border-top:thin solid #333;'>Qty</th>";
            $content.= "		<th style='width:3%; border-bottom:thin solid #333; border-top:thin solid #333;'>Harga</th>";
            $content.= "		<th style='width:5%; border-bottom:thin solid #333; border-top:thin solid #333;'>SubTotal</th>";
            $content.= "		<th style='width:3%; border-bottom:thin solid #333; border-top:thin solid #333;'>Ongkos Svc</th>";
            $content.= "		<th style='width:3%; border-bottom:thin solid #333; border-top:thin solid #333;'>Home Svc</th>";
            $content.= "		<th style='width:3%; border-bottom:thin solid #333; border-top:thin solid #333;'>PPN</th>";
            $content.= "		<th style='width:2%; border-bottom:thin solid #333; border-top:thin solid #333;'>Disc Tmbhn</th>";
            $content.= "		<th style='width:6%; border-bottom:thin solid #333; border-top:thin solid #333;'>Grand Total</th>";
            $content.= "	</tr>";


            $No_Svc = "!@#$%";
            $PPN = 0;
            $Grandtotal = 0;
            $HargaSP = 0;

            $Rate_PPN = 0;
            $Ongkos_Svc = 0;
            $PPN = 0;
            $Home_Svc = 0;
            $Disc_Tambahan = 0;
            $Grandtotal = 0;

            $jum= count($DataLaporan);
            if ($jum!=0){
                // $no = 1;
                for($i=0; $i<$jum; $i++) {
                    if ( $No_Svc != $DataLaporan[$i]->No_Svc and $No_Svc != "!@#$%" ) {
                        
                        if ( $Grandtotal != 0 ) {
                            $content.= "		<tr><td colspan='6'> </td>";
                            $content.= "			<td align='right' style='border-top:thin solid #333;'>Total</td>";
                            $content.= "			<td align='right' style='border-top:thin solid #333;'>".number_format($HargaSP)."</td>";
                            $content.= "			<td align='right' style='border-top:thin solid #333;'>".number_format($Ongkos_Svc)."</td>";
                            $content.= "			<td align='right' style='border-top:thin solid #333;'>".number_format($Home_Svc)."</td>";
                            $content.= "			<td align='right' style='border-top:thin solid #333;'>".number_format($PPN)."</td>";
                            $content.= "			<td align='right' style='border-top:thin solid #333;'>".number_format($Disc_Tambahan)."</td>";
                            $content.= "			<td align='right' style='border-top:thin solid #333;'>".number_format($Grandtotal)."</td>";
                            $content.= "	    </tr>";
                        }
                        $HargaSP = 0;

                        $Rate_PPN = 0;
                        $Ongkos_Svc = 0;
                        $PPN = 0;
                        $Home_Svc = 0;
                        $Disc_Tambahan = 0;
                        $Grandtotal = 0;

                    }

                    if ( $No_Svc != $DataLaporan[$i]->No_Svc ) {

                        $O = "&nbsp;&nbsp;&nbsp;";
                        if ( $DataLaporan[$i]->Type_Svc == "OUTDOOR" ) {
                            $O = "[O]";
                        }

                        $X = "&nbsp;&nbsp;&nbsp;";
                        if ( $DataLaporan[$i]->Cancelled == "Y" ) {
                            $X = "[X]";
                        }

                        $content.= "		<tr><td colspan='13'></td></tr>
                                            <tr><td colspan='1'><b>".$O."&nbsp;&nbsp;&nbsp;&nbsp;".$X."</b></td>
                                                <td colspan='12'><b>".date("d-M-Y", strtotime($DataLaporan[$i]->Tgl_Svc))."&nbsp;&nbsp;&nbsp;&nbsp;".    
                                                $DataLaporan[$i]->No_Svc."&nbsp;&nbsp;&nbsp;&nbsp;". 
                                                $DataLaporan[$i]->Merk."&nbsp;&nbsp;&nbsp;&nbsp;".
                                                $DataLaporan[$i]->Kd_Brg."&nbsp;&nbsp;&nbsp;&nbsp;".
                                                " Pengaduan=".$DataLaporan[$i]->Kerusakan."&nbsp;&nbsp;&nbsp;&nbsp;".
                                                " Perbaikan=".$DataLaporan[$i]->Perbaikan."&nbsp;&nbsp;&nbsp;&nbsp;".
                                    "       </b></td></tr>
                        ";    

                    }

                    if ( trim($DataLaporan[$i]->No_Faktur) != "" ) {
                        $content.= "		<tr><td></td>";           
                        $content.= "			<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$DataLaporan[$i]->No_Faktur."</td>";
                        $content.= "			<td>".$DataLaporan[$i]->Tipe_Faktur."</td>";
                        $content.= "			<td>".$DataLaporan[$i]->Kd_Sparepart."</td>";
                        $content.= "			<td>".$DataLaporan[$i]->Nm_Sparepart."</td>";
                        $content.= "			<td align='right'>".number_format($DataLaporan[$i]->Qty)."</td>";
                        $content.= "			<td align='right'>".number_format($DataLaporan[$i]->Harga)."</td>";
                        $content.= "			<td align='right'>".number_format($DataLaporan[$i]->Subtotal)."</td>";
                        $content.= "			<td colspan='5'> </td>";
                        $content.= "	    </tr>";
                    }

                    $HargaSP += $DataLaporan[$i]->Subtotal;
                    $No_Svc = $DataLaporan[$i]->No_Svc;
            
                    $Rate_PPN = $DataLaporan[$i]->Rate_PPN;
                    $Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
                    $PPN = $Rate_PPN * $Ongkos_Svc / 100;
    
                    $Home_Svc = $DataLaporan[$i]->Home_Svc;
                    $Disc_Tambahan = $DataLaporan[$i]->Disc_Tambahan;
                    $Grandtotal = $HargaSP + $PPN + $Ongkos_Svc + $Home_Svc - $Disc_Tambahan;

                }

                if ( $Grandtotal != 0 ) {
                    $content.= "		<tr><td colspan='6'> </td>";
                    $content.= "			<td align='right' style='border-top:thin solid #333;'>Total</td>";
                    $content.= "			<td align='right' style='border-top:thin solid #333;'>".number_format($HargaSP)."</td>";
                    $content.= "			<td align='right' style='border-top:thin solid #333;'>".number_format($Ongkos_Svc)."</td>";
                    $content.= "			<td align='right' style='border-top:thin solid #333;'>".number_format($Home_Svc)."</td>";
                    $content.= "			<td align='right' style='border-top:thin solid #333;'>".number_format($PPN)."</td>";
                    $content.= "			<td align='right' style='border-top:thin solid #333;'>".number_format($Disc_Tambahan)."</td>";
                    $content.= "			<td align='right' style='border-top:thin solid #333;'>".number_format($Grandtotal)."</td>";
                    $content.= "	    </tr>";
                }

                $content.= "</table> </div>";

                set_time_limit(0);
                if($output=='HTML'){
                    echo ($header.$content);
                } else {
                    $mpdf->SetHTMLHeader($header,'','1');
                    $mpdf->WriteHTML($content);
                    // $mpdf->SetHTMLHeader($header,'','1');		
		            // $mpdf->WriteHTML(utf8_encode($content));
                    $mpdf->Output();
                }
            } 
            else {
                set_time_limit(0);
                if($output=='HTML'){
                    echo ("Tidak Ada Data");
                } else {
                    $mpdf->SetHTMLHeader("Tidak Ada Data",'','1');
                    $mpdf->WriteHTML("");
                    $mpdf->Output();
                }
            }
        }

        //05 Laporan produk masuk service dari pembeli/distributor 
        public function proses_pdf_html_05  ($page_title, $excel_title, $param, $DataLaporan, $output)
        {
            ini_set('max_execution_time', '1500');
            ini_set("pcre.backtrack_limit", "10000000");
            set_time_limit(0);

            require_once __DIR__ . '\vendor\autoload.php';
            $mpdf = new \Mpdf\Mpdf(array(
                'mode' => '',
                'format' => 'A4',
                'default_font_size' => 8,
                'default_font' => 'arial',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 40,
                'margin_bottom' => 10,
                'margin_header' => 10,
                'margin_footer' => 5,
                'orientation' => 'P'
            ));

            $header='';
            $content='<style>
                .table{
                    border-collapse:collapse;
                    border:1px solid #ddd;
                }
                .table thead .head td{
                    background-color:#ddd;
                }
                .table thead tr td, .table tbody tr td{
                    border:1px solid #ccc;
                }
                </style>';

            if(count($DataLaporan)>0){
                $PrintDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));

                $header='<table width="100%" border="0" style="font-size:15px">';
                $header.='<tr>';
                        $header.='<td align="right" style="font-size:10px;">';
                         $header.='Print Date : '.$PrintDate.'<br>
                                   Print By : '.$_SESSION['logged_in']['username'];  
                        $header.='</td>';
                    $header.='</tr>';
                    $header.='<tr>';
                        $header.='<td align="center">';
                            $header.='<b>LAPORAN PRODUK YANG MASUK SERVICE DARI</b>';  
                        $header.='</td>';
                    $header.='</tr>';
                    $header.='<tr>';
                        $header.='<td align="center">';
                            $header.='<b>PEMBELI / DISTRBUTOR (BULANAN)</b>';  
                        $header.='</td>';
                    $header.='</tr>';  
                    $header.='<tr>';
                        $header.='<td align="center" style="font-size:13px;">'.$param['tanggal1'].'  : '.date('d-M-Y', strtotime($param['dp11'])).' - '.date('d-M-Y', strtotime($param['dp12']));
                        $header.='</td>';
                    $header.='</tr>';  
                $header.='</table>';



                $tamp_bulan='';
                $tamp_cabang='';
                $tamp_merk='';
                $DataLaporan = json_decode(json_encode($DataLaporan));
                $no=1;

                foreach ($DataLaporan as $key => $d) {


                    if((!empty($tamp_bulan) && $tamp_bulan!==date_format(date_create($d->Tgl_SvcP), 'M')) || (!empty($tamp_merk) && $tamp_merk!==$d->MERK)){
                            $content.='</tbody>';
                        $content.='</table>';
                    }

                    if((empty($tamp_cabang) || $tamp_cabang!==$d->Kota)){

                        $content.='<table border="0" width="100%">';
                            $content.='<tr>';
                                if(empty($tamp_cabang) || $tamp_cabang!==$d->Kota){

                                    $content .='<td width="100px">Cabang</td>';
                                    $content .='<td width="5px">:</td>';
                                    $content .='<td>'.$d->Kota.'</td>';

                                }
                            $content .='</tr>';
                        $content.='</table>';
                               

                    }


                    if((empty($tamp_bulan) || $tamp_bulan!==date_format(date_create($d->Tgl_SvcP), 'M')) || (empty($tamp_merk) || $tamp_merk!==$d->MERK)){
                        
                        $no=1;

                        $content.='<table border="0" width="100%">';


                        $content.='<tr>';
                        if($tamp_bulan!==date_format(date_create($d->Tgl_SvcP), 'M')){
                            $content .='<td width="100px">Bulan</td>';
                            $content .='<td width="5px">:</td>';
                            $content .='<td>'.date_format(date_create($d->Tgl_SvcP), 'M Y').'</td>';
                        }else{
                            $content .='<td width="100px"></td>';
                            $content .='<td width="5px"></td>';
                            $content .='<td></td>';
                        }
                           
                        if($tamp_merk!==$d->MERK){
                            $content .='<td width="100px">Merk</td>';
                            $content .='<td width="5px">:</td>';
                            $content .='<td width="100px">'.$tamp_merk.'</td>';
                        }

                        $content .='</tr>';


                        $content.='</table>';

                        $tamp_bulan = date_format(date_create($d->Tgl_SvcP), 'M');


                        $content.='<table border="0" width="100%" class="table" style="font-size:12px; margin-bottom:50px">';

                            $content.='<thead>';
                                $content.='<tr class="head">';
                                    $content.='<td align="center" width="50px" rowspan="2">NO</td>';
                                    $content.='<td>TANGGAL</td>';
                                    $content.='<td align="center">NAMA KONSUMEN</td>';
                                    $content.='<td align="center" rowspan="2">ALAMAT</td>';
                                    $content.='<td align="center" rowspan="2">TYPE / MODEL</td>';
                                    $content.='<td align="center" rowspan="2">NO SERI</td>';
                                    $content.='<td align="center" colspan="2">GARANSI</td>';
                                    $content.='<td align="center" rowspan="2">PENGADUAN</td>';
                                    $content.='<td align="center" rowspan="2">PERBAIKAN</td>';
                                    $content.='<td align="center" rowspan="2">TGL SELESAI</td>';
                                    $content.='<td align="center" colspan="2">PROSES</td>';
                                    $content.='<td align="center" rowspan="2">TEKNISI</td>';
                                $content.='</tr>';
                                $content.='<tr class="head">';
                                    $content.='<td align="right">NO SERVICE</td>';
                                    $content.='<td align="center">TELP/HP</td>';
                                    $content.='<td align="center">IN</td>';
                                    $content.='<td align="center">OUT</td>';

                                    $content.='<td align="center">SELESAI</td>';
                                    $content.='<td align="center">KEMBALI</td>';
                                $content.='</tr>';
                            $content.='</head>';
                            $content.='<tbody>';

                    }


                    $tamp_cabang = $d->Kota;
                    $tamp_merk=$d->MERK;

                    if($d->Jaminan=="Y"){
                        $garansi_y='X';
                    }else{
                        $garansi_y='';
                    }

                    if($d->Jaminan=="N"){
                        $garansi_x='X';
                    }else{
                        $garansi_x='';
                    }

                    if($d->Selesai=="Y"){
                        $selesai_y='X';
                    }else{
                        $selesai_y='';
                    }

                    if($d->Selesai=="N"){
                        $selesai_x='X';
                    }else{
                        $selesai_x='';
                    }



                    $telp='';

                    if(!empty($d->Telp) && !empty($d->HP)){
                        $telp = $d->Telp.'<br>'.$d->HP;
                    }else if(!empty($d->Telp)){
                        $telp = $d->Telp;
                    }else if(!empty($d->HP)){
                        $telp = $d->HP;
                    }


                    $content.='<tr>';
                        $content.='<td align="center" width="50px" rowspan="2">'.$no.'</td>';
                        $content.='<td>'.date_format(date_create($d->Tgl_Svc),'d-M-Y').'</td>';
                        $content.='<td align="center">'.$d->Nm_Plg.'</td>';
                        $content.='<td align="center" rowspan="2">'.$d->Alm_Plg.'</td>';
                        $content.='<td align="center" rowspan="2">'.$d->Kd_Brg.'</td>';
                        $content.='<td align="center" rowspan="2">'.$d->No_Seri.'</td>';
                        $content.='<td align="center" rowspan="2">'.$garansi_y.'</td>';
                        $content.='<td align="center" rowspan="2">'.$garansi_x.'</td>';
                        $content.='<td align="center" rowspan="2">'.$d->Pengaduan.'</td>';
                        $content.='<td align="center" rowspan="2">'.$d->Perbaikan.'</td>';
                        $content.='<td align="center" rowspan="2">'.$d->Tgl_Selesai.'</td>';
                        $content.='<td align="center" rowspan="2">'.$selesai_y.'</td>';
                        $content.='<td align="center" rowspan="2">'.$selesai_x.'</td>';
                        $content.='<td align="center" rowspan="2">'.$d->Nm_Teknisi.'</td>';
                    $content.='</tr>';
                    $content.='<tr>';
                        $content.='<td align="right">'.$d->No_Svc.'</td>';
                        $content.='<td align="center">'.$telp.'</td>';

                    $content.='</tr>';

                    $no++;
                }

                    $content.='</tbody>';

                $content.='</table>';


                set_time_limit(0);
                if($output=='HTML'){
                    echo ($header.$content);
                } else {
                    $mpdf->SetHTMLHeader($header,'','1');
                    $mpdf->WriteHTML($content);
                    $mpdf->Output();
                }
            } else {
                set_time_limit(0);
                if($output=='HTML'){
                    echo ("Tidak Ada Data");
                } else {
                    $mpdf->SetHTMLHeader("Tidak Ada Data",'','1');
                    $mpdf->WriteHTML("");
                    $mpdf->Output();
                }
            }
        }


        //05 Laporan produk masuk service dari pembeli/distributor 
        public function proses_excel_05  ($page_title, $excel_title, $param, $DataLaporan, $output)
        {
            ini_set('max_execution_time', '1500');
            ini_set("pcre.backtrack_limit", "10000000");
            set_time_limit(0);


            if(count($DataLaporan)>0){
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet(0);

                $PrintDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));

                $sheet->setTitle('LAPORAN BUKU HARIAN');
                $sheet->setCellValue('A1', 'Print Date : '.$PrintDate);
                $sheet->setCellValue('A2', 'Print By : '.$_SESSION['logged_in']['username']);
                $sheet->setCellValue('A3', 'LAPORAN PRODUK YANG MASUK SERVICE DARI');
                $sheet->setCellValue('A4', 'PEMBELI / DISTRBUTOR (BULANAN)');
                $sheet->setCellValue('A5', $param["tanggal1"].'  : '.date("d-M-Y", strtotime($param["dp11"]))." - ".date("d-M-Y", strtotime($param["dp12"])));

                $sheet->mergeCells('A1:N1');
                $sheet->mergeCells('A2:N2');
                $sheet->mergeCells('A3:N3');
                $sheet->mergeCells('A4:N4');
                $sheet->mergeCells('A5:N5');

                $sheet->getStyle('A1')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A3')->getFont()->setSize(20);
                $sheet->getStyle('A4')->getFont()->setSize(20);
                $sheet->getStyle('A5')->getFont()->setSize(13);

                $sheet->getStyle('A1:N1')->getAlignment()->setHorizontal('right');
                $sheet->getStyle('A2:N2')->getAlignment()->setHorizontal('right');
                $sheet->getStyle('A3:N3')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A4:N4')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A5:N5')->getAlignment()->setHorizontal('center');

                $tamp_bulan='';
                $tamp_cabang='';
                $tamp_merk='';
                $DataLaporan = json_decode(json_encode($DataLaporan));
                $no=1;
                $currrow=7;
                $currcol=1;

                foreach ($DataLaporan as $key => $d) {

                    if((!empty($tamp_bulan) && $tamp_bulan!==date_format(date_create($d->Tgl_SvcP), 'M')) || (!empty($tamp_merk) && $tamp_merk!==$d->MERK)){
                        $currrow=$currrow+2;
                    }

                    if((empty($tamp_cabang) || $tamp_cabang!==$d->Kota)){

                        $currcol = 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Cabang : '.$d->Kota);
                        $sheet->mergeCells('A'.$currrow.':N'.$currrow);
                        $currrow ++;

                    }


                    if((empty($tamp_bulan) || $tamp_bulan!==date_format(date_create($d->Tgl_SvcP), 'M')) || (empty($tamp_merk) || $tamp_merk!==$d->MERK)){
                        
                        $no=1;

                        if($tamp_bulan!==date_format(date_create($d->Tgl_SvcP), 'M')){
                            $currcol = 1;
                            $sheet->mergeCells('A'.$currrow.':F'.$currrow);
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Bulan : '.date_format(date_create($d->Tgl_SvcP), 'M Y'));
                        }else{
                            $sheet->mergeCells('A'.$currrow.':F'.$currrow);
                        }

                        if($tamp_merk!==$d->MERK){
                            $currcol = 7;
                            $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk : '.$tamp_merk);
                            $sheet->mergeCells('G'.$currrow.':N'.$currrow);
                            $sheet->getStyle('G'.$currrow.':N'.$currrow)->getAlignment()->setHorizontal('right');
                            $currrow ++;
                        }else{
                            $sheet->mergeCells('G'.$currrow.':N'.$currrow);
                            $currrow ++;
                        }


                        $tamp_bulan = date_format(date_create($d->Tgl_SvcP), 'M');

                        $currcol=1;
                        $tambah_row=$currrow+1;
                        $spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':N'.$currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('eaeaea');
                        $spreadsheet->getActiveSheet()->getStyle('A'.$tambah_row.':N'.$tambah_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('eaeaea');

                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No');
                        $sheet->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
                        $sheet->mergeCells('A'.$currrow.':A'.$tambah_row);
                        $currcol++;

                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TANGGAL');
                        $currcol++;

                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA KONSUMEN');
                        $sheet->getStyle('C'.$currrow)->getAlignment()->setHorizontal('center');
                        $currcol++;

                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'ALAMAT');
                        $sheet->mergeCells('D'.$currrow.':D'.$tambah_row);
                        $sheet->getStyle('D'.$currrow)->getAlignment()->setHorizontal('center');
                        $currcol++;

                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TYPE / MODEL');
                        $sheet->mergeCells('E'.$currrow.':E'.$tambah_row);
                        $sheet->getStyle('E'.$currrow)->getAlignment()->setHorizontal('center');
                        $currcol++;

                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO SERI');
                        $sheet->mergeCells('F'.$currrow.':F'.$tambah_row);
                        $sheet->getStyle('F'.$currrow)->getAlignment()->setHorizontal('center');
                        $currcol++;

                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GARANSI');
                        $sheet->mergeCells('G'.$currrow.':H'.$currrow);
                        $sheet->getStyle('G'.$currrow.':H'.$currrow)->getAlignment()->setHorizontal('center');
                        $currcol=$currcol+2;

                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PENGADUAN');
                        $sheet->mergeCells('I'.$currrow.':I'.$tambah_row);
                        $sheet->getStyle('I'.$currrow)->getAlignment()->setHorizontal('center');
                        $currcol++;

                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PERBAIKAN');
                        $sheet->mergeCells('J'.$currrow.':J'.$tambah_row);
                        $sheet->getStyle('J'.$currrow)->getAlignment()->setHorizontal('center');
                        $currcol++;

                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL SELESAI');
                        $sheet->mergeCells('K'.$currrow.':K'.$tambah_row);
                        $sheet->getStyle('K'.$currrow)->getAlignment()->setHorizontal('center');
                        $currcol++;

                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PROSES');
                        $sheet->mergeCells('L'.$currrow.':M'.$currrow);
                        $sheet->getStyle('L'.$currrow.':M'.$currrow)->getAlignment()->setHorizontal('center');
                        $currcol=$currcol+2;

                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TEKNISI');
                        $sheet->mergeCells('N'.$currrow.':N'.$tambah_row);
                        $sheet->getStyle('N'.$currrow)->getAlignment()->setHorizontal('center');
                        $currrow ++;

                        $currcol=2;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO SERVICE');
                        $sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal('right');
                        $currcol++;

                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TELP/HP');
                        $sheet->getStyle('C'.$currrow)->getAlignment()->setHorizontal('center');
                        $currcol++;

                        $currcol=7;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'IN');
                        $sheet->getStyle('G'.$currrow)->getAlignment()->setHorizontal('center');

                        $currcol++;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'OUT');
                        $sheet->getStyle('H'.$currrow)->getAlignment()->setHorizontal('center');

                        $currcol=12;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SELESAI');
                        $sheet->getStyle('L'.$currrow)->getAlignment()->setHorizontal('center');

                        $currcol++;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KEMBALI');
                        $sheet->getStyle('L'.$currrow)->getAlignment()->setHorizontal('center');

                        $currrow ++;

                    }


                    $tamp_cabang = $d->Kota;
                    $tamp_merk=$d->MERK;

                    if($d->Jaminan=="Y"){
                        $garansi_y='X';
                    }else{
                        $garansi_y='';
                    }

                    if($d->Jaminan=="N"){
                        $garansi_x='X';
                    }else{
                        $garansi_x='';
                    }

                    if($d->Selesai=="Y"){
                        $selesai_y='X';
                    }else{
                        $selesai_y='';
                    }

                    if($d->Selesai=="N"){
                        $selesai_x='X';
                    }else{
                        $selesai_x='';
                    }



                    $telp='';

                    if(!empty($d->Telp) && !empty($d->HP)){
                        $telp = $d->Telp.'<br>'.$d->HP;
                    }else if(!empty($d->Telp)){
                        $telp = $d->Telp;
                    }else if(!empty($d->HP)){
                        $telp = $d->HP;
                    }

                    $currcol=1;
                    $tambah_row=$currrow+1;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
                    $sheet->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
                    $sheet->mergeCells('A'.$currrow.':A'.$tambah_row);
                    $currcol++;

                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, date_format(date_create($d->Tgl_Svc),'d-M-Y'));
                    $currcol++;

                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($d->Nm_Plg));
                    $sheet->getStyle('C'.$currrow)->getAlignment()->setHorizontal('center');
                    $currcol++;

                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($d->Alm_Plg));
                    $sheet->mergeCells('D'.$currrow.':D'.$tambah_row);
                    $sheet->getStyle('D'.$currrow)->getAlignment()->setHorizontal('center');
                    $currcol++;

                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($d->Kd_Brg));
                    $sheet->mergeCells('E'.$currrow.':E'.$tambah_row);
                    $sheet->getStyle('E'.$currrow)->getAlignment()->setHorizontal('center');
                    $currcol++;

                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($d->No_Seri));
                    $sheet->mergeCells('F'.$currrow.':F'.$tambah_row);
                    $sheet->getStyle('F'.$currrow)->getAlignment()->setHorizontal('center');
                    $currcol++;

                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $garansi_y);
                    $currcol++;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $garansi_x);
                    $sheet->getStyle('G'.$currrow.':H'.$currrow)->getAlignment()->setHorizontal('center');
                    $currcol++;

                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($d->Pengaduan));
                    $sheet->mergeCells('I'.$currrow.':I'.$tambah_row);
                    $sheet->getStyle('I'.$currrow)->getAlignment()->setHorizontal('center');
                    $currcol++;

                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($d->Perbaikan));
                    $sheet->mergeCells('J'.$currrow.':J'.$tambah_row);
                    $sheet->getStyle('J'.$currrow)->getAlignment()->setHorizontal('center');
                    $currcol++;

                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($d->Tgl_Selesai));
                    $sheet->mergeCells('K'.$currrow.':K'.$tambah_row);
                    $sheet->getStyle('K'.$currrow)->getAlignment()->setHorizontal('center');
                    $currcol++;

                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $selesai_y);
                    $sheet->mergeCells('L'.$currrow.':L'.$tambah_row);
                    $sheet->getStyle('L'.$currrow.':L'.$tambah_row)->getAlignment()->setHorizontal('center');
                    $currcol++;

                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $selesai_x);
                    $sheet->mergeCells('M'.$currrow.':M'.$tambah_row);
                    $sheet->getStyle('M'.$currrow.':M'.$tambah_row)->getAlignment()->setHorizontal('center');
                    $currcol++;

                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($d->Nm_Teknisi));
                    $sheet->mergeCells('N'.$currrow.':N'.$tambah_row);
                    $sheet->getStyle('N'.$currrow)->getAlignment()->setHorizontal('center');
                    $currrow ++;

                    $currcol=2;
                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($d->No_Svc));
                    $sheet->getStyle('B'.$currrow)->getAlignment()->setHorizontal('right');
                    $currcol++;

                    $sheet->setCellValueByColumnAndRow($currcol, $currrow, $telp);
                    $sheet->getStyle('C'.$currrow)->getAlignment()->setHorizontal('center');

                    $no++;
                    $currrow ++;
                }




                for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
                    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
                }

                $filename='LAPORAN PRODUK YANG MASUK SERVICE DARI PEMBELI / DISTRBUTOR (BULANAN) ['.date('Y-m-d H-i-s').']'; //save our workbook as this file name
                $writer = new Xlsx($spreadsheet);
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
                header('Cache-Control: max-age=0');
                ob_end_clean();
                $writer->save('php://output');
                exit();


            } else {
                echo "Data Tidak Ada";
            }
        }


        //09 Summary Service Harian
        public function proses_excel_09 ($page_title, $excel_title, $param, $DataLaporan, $output)
		{
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);

			$PrintDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));
						
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', 'Print Date      : '.$PrintDate);
			$sheet->setCellValue('A2', 'Print By        : '.$_SESSION['logged_in']['username']);
			$sheet->setCellValue('A3', $page_title);
			$sheet->getStyle('A3')->getFont()->setSize(20);
			$sheet->setCellValue('A4', $excel_title);
			$sheet->setCellValue('A5', $param["tanggal1"].'  : '.date("d-M-Y", strtotime($param["dp11"]))." - ".date("d-M-Y", strtotime($param["dp12"])));
			$sheet->setCellValue('A6', 'Merk  : '.$param["merk"]);
			$sheet->setCellValue('A7', 'Barang  : '.$param["kodebarang"]);
			$sheet->setCellValue('A8', 'Teknisi  : '.$param["teknisi"]);
			$sheet->setCellValue('A9', 'Garansi  : '.$param["garansi"]);
			$sheet->setCellValue('A10', 'Metode Bayar  : '.$param["metodebayar"]);
			$sheet->setCellValue('A11', 'Cetak  : '.$param["cetak"]);
			$sheet->setCellValue('A12', 'Cetak Per Merk  : '.$param["cetakpermerk"]);
			$sheet->setCellValue('A13', 'Status  : '.$param["status"]);

			if($param["tanggal2"]!="TANGGAL-KOSONG"){
				$sheet->setCellValue('A14', $param["tanggal2"].'  : '.date("d-M-Y", strtotime($param["dp21"]))." - ".date("d-M-Y", strtotime($param["dp22"])));
				$currrow = 14;	
			} else {
				$currrow = 13;	
			}

			$currcol = 1;

			$currrow++;
			$currrow++;
			$currcol = 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "No");
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Nota Service");
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Tipe Service");
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Tanggal");
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Tanggal Trans");
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Garansi");
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+1 , $currrow);
			$sheet->setCellValueByColumnAndRow($currcol, $currrow+1, "Y");
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow+1, "T");
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Ongkos");
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "PPN");
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Transport");
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "PPH");
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Total");
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Batal / Alasan");
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).($currrow+1))->getFont()->setBold(true);	
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).($currrow+1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);	
			$currrow++;

			$SumByKodeNota_Ongkos_Svc = 0;
			$SumByKodeNota_PPN = 0;
			$SumByKodeNota_Home_Svc = 0;
			$SumByKodeNota_PPH = 0;
			$SumByKodeNota_SubTotal = 0; 

			$SumByCancelled_Ongkos_Svc = 0;
			$SumByCancelled_PPN = 0;
			$SumByCancelled_Home_Svc = 0;
			$SumByCancelled_PPH = 0;
			$SumByCancelled_SubTotal = 0; 

			$SumByTipeSvc_Ongkos_Svc = 0;
			$SumByTipeSvc_PPN = 0;
			$SumByTipeSvc_Home_Svc = 0;
			$SumByTipeSvc_PPH = 0;
			$SumByTipeSvc_SubTotal = 0; 

			$SumByAll_Ongkos_Svc = 0;
			$SumByAll_PPN = 0;
			$SumByAll_Home_Svc = 0;
			$SumByAll_PPH = 0;
			$SumByAll_SubTotal = 0;

			$jum= count($DataLaporan);
			$no = 1;
			for($i=0; $i<$jum; $i++){


				$PPN = ($DataLaporan[$i]->Rate_PPN / 100) * $DataLaporan[$i]->Ongkos_Svc;
				$PPH = ($DataLaporan[$i]->PPH / 100) * $DataLaporan[$i]->Ongkos_Svc;
				$SubTotal = $DataLaporan[$i]->Ongkos_Svc + $DataLaporan[$i]->Home_Svc + $PPN + $PPH;

				if ($i==0){
					$Group1_KodeNota = trim($DataLaporan[$i]->KodeNota);
					$Group2_Cancelled = trim($DataLaporan[$i]->Cancelled);
					$Group3_TipeSvc = trim($DataLaporan[$i]->Type_Svc);

					$SumByKodeNota_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
					$SumByKodeNota_PPN = $PPN;
					$SumByKodeNota_Home_Svc = $DataLaporan[$i]->Home_Svc;
					$SumByKodeNota_PPH = $PPH;
					$SumByKodeNota_SubTotal = $SubTotal;

					$SumByCancelled_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
					$SumByCancelled_PPN = $PPN;
					$SumByCancelled_Home_Svc = $DataLaporan[$i]->Home_Svc;
					$SumByCancelled_PPH = $PPH;
					$SumByCancelled_SubTotal = $SubTotal;

					$SumByTipeSvc_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
					$SumByTipeSvc_PPN = $PPN;
					$SumByTipeSvc_Home_Svc = $DataLaporan[$i]->Home_Svc;
					$SumByTipeSvc_PPH = $PPH;
					$SumByTipeSvc_SubTotal = $SubTotal;

            $currrow++;
            $currrow++;
            $currcol = 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "No");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Nota Service");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Tipe Service");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Tanggal");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Tanggal Trans");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Garansi");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+1 , $currrow);
            $sheet->setCellValueByColumnAndRow($currcol, $currrow+1, "Y");
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow+1, "T");
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Ongkos");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "PPN");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Transport");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "PPH");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Total");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $currcol += 1;
            $sheet->setCellValueByColumnAndRow($currcol, $currrow, "Batal / Alasan");
            $sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
            $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).($currrow+1))->getFont()->setBold(true);	
            $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).($currrow+1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);	
            $currrow++;

					// $currrow++;
					// $currcol = 1;
					// $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GROUP 2 CANCELLED : '.$Group2_Cancelled);
					// $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

					// $currrow++;
					// $currcol = 1;
					// $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GROUP 3 TIPE SERVICE : '.$Group3_TipeSvc);
					// $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	
				} else {

					if (trim($DataLaporan[$i]->Type_Svc)!=$Group3_TipeSvc){
						$currrow++;
						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL TIPE SERVICE : '.$Group3_TipeSvc);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');	
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByTipeSvc_Ongkos_Svc);	
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByTipeSvc_PPN);	
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByTipeSvc_Home_Svc);	
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByTipeSvc_PPH);  
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByTipeSvc_SubTotal);  
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
						$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

						$Group3_TipeSvc = trim($DataLaporan[$i]->Type_Svc);
						$SumByTipeSvc_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
						$SumByTipeSvc_PPN = $PPN;
						$SumByTipeSvc_Home_Svc = $DataLaporan[$i]->Home_Svc;
						$SumByTipeSvc_PPH = $PPH;
						$SumByTipeSvc_SubTotal = $SubTotal;

						// $currrow++;
						// $currcol = 1;
						// $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GROUP 3 : '.trim($DataLaporan[$i]->Type_Svc));
						// $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
					} else {
						$SumByTipeSvc_Ongkos_Svc += $DataLaporan[$i]->Ongkos_Svc;
						$SumByTipeSvc_PPN += $PPN;
						$SumByTipeSvc_Home_Svc += $DataLaporan[$i]->Home_Svc;
						$SumByTipeSvc_PPH += $PPH;
						$SumByTipeSvc_SubTotal += $SubTotal;
					}

					if (trim($DataLaporan[$i]->Cancelled)!=$Group2_Cancelled){
						$currrow++;
						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL CANCELLED : '.$Group2_Cancelled);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');	
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByCancelled_Ongkos_Svc);	
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByCancelled_PPN);	
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByCancelled_Home_Svc);	
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByCancelled_PPH);  
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByCancelled_SubTotal);  
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
						$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

						$Group2_Cancelled = trim($DataLaporan[$i]->Cancelled);
						$SumByCancelled_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
						$SumByCancelled_PPN = $PPN;
						$SumByCancelled_Home_Svc = $DataLaporan[$i]->Home_Svc;
						$SumByCancelled_PPH = $PPH;
						$SumByCancelled_SubTotal = $SubTotal;

						// $currrow++;
						// $currcol = 1;
						// $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GROUP 2 : '.trim($DataLaporan[$i]->Cancelled));
						// $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
					} else {
						$SumByCancelled_Ongkos_Svc += $DataLaporan[$i]->Ongkos_Svc;
						$SumByCancelled_PPN += $PPN;
						$SumByCancelled_Home_Svc += $DataLaporan[$i]->Home_Svc;
						$SumByCancelled_PPH += $PPH;
						$SumByCancelled_SubTotal += $SubTotal;
					}

					if (trim($DataLaporan[$i]->KodeNota)!=$Group1_KodeNota){
						$currrow++;
						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL KODE NOTA : '.$Group1_KodeNota);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');	
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKodeNota_Ongkos_Svc);	
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKodeNota_PPN);	
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKodeNota_Home_Svc);	
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKodeNota_PPH);  
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKodeNota_SubTotal);  
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
						$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

						$Group1_KodeNota = trim($DataLaporan[$i]->KodeNota);
						$SumByKodeNota_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
						$SumByKodeNota_PPN = $PPN;
						$SumByKodeNota_Home_Svc = $DataLaporan[$i]->Home_Svc;
						$SumByKodeNota_PPH = $PPH;
						$SumByKodeNota_SubTotal = $SubTotal;

						// $currrow++;
						// $currcol = 1;
						// $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GROUP 1 : '.trim($DataLaporan[$i]->KodeNota));
						// $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
					} else {
						$SumByKodeNota_Ongkos_Svc += $DataLaporan[$i]->Ongkos_Svc;
						$SumByKodeNota_PPN += $PPN;
						$SumByKodeNota_Home_Svc += $DataLaporan[$i]->Home_Svc;
						$SumByKodeNota_PPH += $PPH;
						$SumByKodeNota_SubTotal += $SubTotal;
					}

				}

				//start isi data - from here
  /*
                $PPN = ($DataLaporan[$i]->Rate_PPN / 100) * $DataLaporan[$i]->Ongkos_Svc;
                $PPH = ($DataLaporan[$i]->PPH / 100) * $DataLaporan[$i]->Ongkos_Svc;
                $SubTotal = $DataLaporan[$i]->Ongkos_Svc + $DataLaporan[$i]->Home_Svc + $PPN + $PPH;

                if ($i==0){
                    $Group1_KodeNota = trim($DataLaporan[$i]->KodeNota);
                    $Group2_Cancelled = trim($DataLaporan[$i]->Cancelled);
                    $Group3_TipeSvc = trim($DataLaporan[$i]->Type_Svc);

                    $SumByKodeNota_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
                    $SumByKodeNota_PPN = $PPN;
                    $SumByKodeNota_Home_Svc = $DataLaporan[$i]->Home_Svc;
                    $SumByKodeNota_PPH = $PPH;
                    $SumByKodeNota_SubTotal = $SubTotal;

                    $SumByCancelled_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
                    $SumByCancelled_PPN = $PPN;
                    $SumByCancelled_Home_Svc = $DataLaporan[$i]->Home_Svc;
                    $SumByCancelled_PPH = $PPH;
                    $SumByCancelled_SubTotal = $SubTotal;

                    $SumByTipeSvc_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
                    $SumByTipeSvc_PPN = $PPN;
                    $SumByTipeSvc_Home_Svc = $DataLaporan[$i]->Home_Svc;
                    $SumByTipeSvc_PPH = $PPH;
                    $SumByTipeSvc_SubTotal = $SubTotal;

                    // $currrow++;
                    // $currcol = 1;
                    // $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GROUP 1 KODE NOTA : '.$Group1_KodeNota);
                    // $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

                    // $currrow++;
                    // $currcol = 1;
                    // $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GROUP 2 CANCELLED : '.$Group2_Cancelled);
                    // $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

                    // $currrow++;
                    // $currcol = 1;
                    // $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GROUP 3 TIPE SERVICE : '.$Group3_TipeSvc);
                    // $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	
                } else {

                    if (trim($DataLaporan[$i]->Type_Svc)!=$Group3_TipeSvc){
                        $currrow++;
                        $currcol = 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL TIPE SERVICE : '.$Group3_TipeSvc);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByTipeSvc_Ongkos_Svc);	
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByTipeSvc_PPN);	
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByTipeSvc_Home_Svc);	
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByTipeSvc_PPH);  
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByTipeSvc_SubTotal);  
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

                        $Group3_TipeSvc = trim($DataLaporan[$i]->Type_Svc);
                        $SumByTipeSvc_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
                        $SumByTipeSvc_PPN = $PPN;
                        $SumByTipeSvc_Home_Svc = $DataLaporan[$i]->Home_Svc;
                        $SumByTipeSvc_PPH = $PPH;
                        $SumByTipeSvc_SubTotal = $SubTotal;

                        // $currrow++;
                        // $currcol = 1;
                        // $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GROUP 3 : '.trim($DataLaporan[$i]->Type_Svc));
                        // $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
                    } else {
                        $SumByTipeSvc_Ongkos_Svc += $DataLaporan[$i]->Ongkos_Svc;
                        $SumByTipeSvc_PPN += $PPN;
                        $SumByTipeSvc_Home_Svc += $DataLaporan[$i]->Home_Svc;
                        $SumByTipeSvc_PPH += $PPH;
                        $SumByTipeSvc_SubTotal += $SubTotal;
                    }

                    if (trim($DataLaporan[$i]->Cancelled)!=$Group2_Cancelled){
                        $currrow++;
                        $currcol = 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL CANCELLED : '.$Group2_Cancelled);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByCancelled_Ongkos_Svc);	
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByCancelled_PPN);	
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByCancelled_Home_Svc);	
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByCancelled_PPH);  
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByCancelled_SubTotal);  
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

                        $Group2_Cancelled = trim($DataLaporan[$i]->Cancelled);
                        $SumByCancelled_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
                        $SumByCancelled_PPN = $PPN;
                        $SumByCancelled_Home_Svc = $DataLaporan[$i]->Home_Svc;
                        $SumByCancelled_PPH = $PPH;
                        $SumByCancelled_SubTotal = $SubTotal;

                        // $currrow++;
                        // $currcol = 1;
                        // $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GROUP 2 : '.trim($DataLaporan[$i]->Cancelled));
                        // $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
                    } else {
                        $SumByCancelled_Ongkos_Svc += $DataLaporan[$i]->Ongkos_Svc;
                        $SumByCancelled_PPN += $PPN;
                        $SumByCancelled_Home_Svc += $DataLaporan[$i]->Home_Svc;
                        $SumByCancelled_PPH += $PPH;
                        $SumByCancelled_SubTotal += $SubTotal;
                    }

                    if (trim($DataLaporan[$i]->KodeNota)!=$Group1_KodeNota){
                        $currrow++;
                        $currcol = 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL KODE NOTA : '.$Group1_KodeNota);
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');	
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKodeNota_Ongkos_Svc);	
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKodeNota_PPN);	
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKodeNota_Home_Svc);	
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKodeNota_PPH);  
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKodeNota_SubTotal);  
                        $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
                        $currcol += 1;
                        $sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
                        $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

                        $Group1_KodeNota = trim($DataLaporan[$i]->KodeNota);
                        $SumByKodeNota_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
                        $SumByKodeNota_PPN = $PPN;
                        $SumByKodeNota_Home_Svc = $DataLaporan[$i]->Home_Svc;
                        $SumByKodeNota_PPH = $PPH;
                        $SumByKodeNota_SubTotal = $SubTotal;

                        // $currrow++;
                        // $currcol = 1;
                        // $sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GROUP 1 : '.trim($DataLaporan[$i]->KodeNota));
                        // $sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
                    } else {
                        $SumByKodeNota_Ongkos_Svc += $DataLaporan[$i]->Ongkos_Svc;
                        $SumByKodeNota_PPN += $PPN;
                        $SumByKodeNota_Home_Svc += $DataLaporan[$i]->Home_Svc;
                        $SumByKodeNota_PPH += $PPH;
                        $SumByKodeNota_SubTotal += $SubTotal;
                    }

                }

                    if(!empty($DataLaporan[$i]->Tgl_Svc) && $DataLaporan[$i]->Tgl_Svc!==null){
                        $Tgl_Svc = date("d-M-Y", strtotime($DataLaporan[$i]->Tgl_Svc));
                    }else{
                        $Tgl_Svc = '';
                    }


                    if(!empty($DataLaporan[$i]->tgl_trans) && $DataLaporan[$i]->tgl_trans!==null){
                        $Tgl_Trans = date("d-M-Y", strtotime($DataLaporan[$i]->tgl_trans));
                    }else{
                        $Tgl_Trans = '';
                    }

                //start isi data - from here
        */

				$currrow++;
				$currcol = 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($DataLaporan[$i]->No_Svc));	
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($DataLaporan[$i]->Type_Svc));	
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $Tgl_Svc);	
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $Tgl_Trans);	

				if ($DataLaporan[$i]->Jaminan=="Y"){
					$garansi = "x";
					$tidakgaransi = "";
				} else {
					$garansi = "";
					$tidakgaransi = "x";
				}
				$currcol += 1;
        
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-M-Y", strtotime($DataLaporan[$i]->Tgl_Trans)));	

                if ($DataLaporan[$i]->Jaminan=="Y"){
                    $garansi = "x";
                    $tidakgaransi = "";
                } else {
                    $garansi = "";
                    $tidakgaransi = "x";
                }
                $currcol += 1;

        		$sheet->setCellValueByColumnAndRow($currcol, $currrow, $garansi);	
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tidakgaransi);	

				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Ongkos_Svc);	
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0'); 
		   
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $PPN);	
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0'); 
		   
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataLaporan[$i]->Home_Svc);	
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0'); 
		   
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $PPH);
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0'); 
			
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SubTotal);	
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0'); 
		   
				$currcol += 1;
				if ($DataLaporan[$i]->Cancelled=="Y"){
					if (strtoupper(trim($DataLaporan[$i]->Kd_Brg))=="JUAL PARTS")
					{
						$alasanbatal = "JUAL PART " + trim($DataLaporan[$i]->Alasan);
					} else {
						$alasanbatal = trim($DataLaporan[$i]->Alasan);
					}
				} else {
					$alasanbatal = "";
				}
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $alasanbatal);	

				$SumByAll_Ongkos_Svc += $DataLaporan[$i]->Ongkos_Svc;
				$SumByAll_PPN += $PPN;
				$SumByAll_Home_Svc += $DataLaporan[$i]->Home_Svc;
				$SumByAll_PPH += $PPH;
				$SumByAll_SubTotal += $SubTotal;

				$no++;
				//start isi data - until here
			}

			$currrow++;
			$currcol = 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL TIPE SERVICE : '.$Group3_TipeSvc);	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByTipeSvc_Ongkos_Svc);
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByTipeSvc_PPN);	
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByTipeSvc_Home_Svc);	
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByTipeSvc_PPH);	
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByTipeSvc_SubTotal);  
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

			$currrow++;
			$currcol = 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL CANCELLED : '.$Group2_Cancelled);	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');		
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByCancelled_Ongkos_Svc);
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByCancelled_PPN);	
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByCancelled_Home_Svc);	
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByCancelled_PPH);	
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByCancelled_SubTotal);  
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

			$currrow++;
			$currcol = 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL KODE NOTA : '.$Group1_KodeNota);	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKodeNota_Ongkos_Svc);
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKodeNota_PPN);	
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKodeNota_Home_Svc);	
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKodeNota_PPH);	
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByKodeNota_SubTotal);  
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

			$currrow++;
			$currcol = 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByAll_Ongkos_Svc);
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByAll_PPN);	
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByAll_Home_Svc);	
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByAll_PPH);	
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SumByAll_SubTotal);  
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');	
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);	

			// warna header
			$max_col = $currcol-1;
			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A'.$currrow.':'.$max_col.$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
	
			// autosize column
			foreach(range('B',$max_col) as $columnID) {
				$spreadsheet->getActiveSheet(0)->getColumnDimension($columnID)
					->setAutoSize(true);
			}

			$filename = $excel_title; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file
			exit(); 

		}

		//09 Summary Service Harian
		public function proses_pdf_html_09 ($page_title, $excel_title, $param, $DataLaporan, $output)
		{	
			//ini_set('max_execution_time', '1500');
			ini_set("pcre.backtrack_limit", "10000000");
			set_time_limit(60);

			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'arial',
				'margin_left' => 10,
				'margin_right' => 10,
				'margin_top' => 35,
				'margin_bottom' => 15,
				'margin_header' => 10,
				'margin_footer' => 5,
				'orientation' => 'P'
			));

			$PrintDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));

			$SumByKodeNota_Ongkos_Svc = 0;
			$SumByKodeNota_PPN = 0;
			$SumByKodeNota_Home_Svc = 0;
			$SumByKodeNota_PPH = 0;
			$SumByKodeNota_SubTotal = 0; 

			$SumByCancelled_Ongkos_Svc = 0;
			$SumByCancelled_PPN = 0;
			$SumByCancelled_Home_Svc = 0;
			$SumByCancelled_PPH = 0;
			$SumByCancelled_SubTotal = 0; 

			$SumByTipeSvc_Ongkos_Svc = 0;
			$SumByTipeSvc_PPN = 0;
			$SumByTipeSvc_Home_Svc = 0;
			$SumByTipeSvc_PPH = 0;
			$SumByTipeSvc_SubTotal = 0; 

			$SumByAll_Ongkos_Svc = 0;
			$SumByAll_PPN = 0;
			$SumByAll_Home_Svc = 0;
			$SumByAll_PPH = 0;
			$SumByAll_SubTotal = 0;

			$header = '<table border="0" style="width:100%; font-size:15px;">
					<tr>
						<td align="center" style="font-size:12px;">
							Print Date '.$PrintDate.'
						</td>
					</tr>
					<tr>
						<td align="center" style="font-size:12px;">
							Print By '.$_SESSION['logged_in']['username'].'
						</td>
					</tr>
					<tr>
						<td align="center">
							<b>
								'.$page_title.'
							</b>
						</td>
					</tr>
					<tr>
						<td align="center" style="font-size:12px;">
							'.$excel_title.'
						</td>
					</tr>
					<tr>
						<td align="center" style="font-size:12px;">
						'.trim($param["tanggal1"]).' '.date("d-M-Y", strtotime($param["dp11"])).' <b> - </b> '.date("d-M-Y", strtotime($param["dp12"])).'
						</td>
					</tr>';

			$filter = 'Merk '.trim($param["merk"]).', Barang '.trim($param["kodebarang"]).', Teknisi '.trim($param["teknisi"]).', Garansi '.trim($param["garansi"]).', Metode Bayar '.trim($param["metodebayar"]).', Cetak '.trim($param["cetak"]).', Cetak Per Merk '.trim($param["cetakpermerk"]).', Status '.trim($param["status"]);
			if($param["tanggal2"]!="TANGGAL-KOSONG"){
				$header .= '<tr>
								<td align="center" style="font-size:12px;">
									Filter Laporan '.$filter.'
								</td>
							</tr>
							<tr>
								<td align="center" style="font-size:12px;">
								'.trim($param["tanggal2"]).' '.date("d-M-Y", strtotime($param["dp21"])).' <b> - </b> '.date("d-M-Y", strtotime($param["dp22"])).'
								</td>
							</tr>
							</table>';
			} else {
				$header .= 	'<tr>
								<td align="center" style="font-size:12px;">
									Filter Laporan '.$filter.'
								</td>
							</tr></table>';
			}

			$header .= '<table width="100%" style="font-weight: bold;">
						</table>';
	
			$content = '
			<br><table width="100%">
				<tr>';

			$content .='<td style="text-align: center; width: 5%; font-size: 12px; padding:5px; font-weight: bold;">No</td>
						<td style="text-align: left; width: 10%; font-size: 12px; padding:5px; font-weight: bold;">Nota Service</td>
						<td style="text-align: left; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">Tipe Service</td>
						<td style="text-align: center; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">Tanggal</td>
						<td style="text-align: center; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">Tanggal Trans</td>
						<td style="text-align: center; width: 5%; font-size: 12px; padding:5px; font-weight: bold;">Garansi Y</td>
						<td style="text-align: center; width: 5%; font-size: 12px; padding:5px; font-weight: bold;">Garansi T</td>
						<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">Ongkos</td>
						<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">PPN</td>
						<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">Transport</td>
						<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">PPH</td>
						<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">Total</td>
						<td style="text-align: left; width: 16%; font-size: 12px; padding:5px; font-weight: bold;">Batal/Alasan</td>
						</tr>';

			$jum= count($DataLaporan);
			if ($jum!=0){
				$no = 1;
				for($i=0; $i<$jum; $i++){

					$PPN = ($DataLaporan[$i]->Rate_PPN / 100) * $DataLaporan[$i]->Ongkos_Svc;
					$PPH = ($DataLaporan[$i]->PPH / 100) * $DataLaporan[$i]->Ongkos_Svc;
					$SubTotal = $DataLaporan[$i]->Ongkos_Svc + $DataLaporan[$i]->Home_Svc + $PPN + $PPH;

					if ($i==0){
						$Group1_KodeNota = trim($DataLaporan[$i]->KodeNota);
						$Group2_Cancelled = trim($DataLaporan[$i]->Cancelled);
						$Group3_TipeSvc = trim($DataLaporan[$i]->Type_Svc);
	
						$SumByKodeNota_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
						$SumByKodeNota_PPN = $PPN;
						$SumByKodeNota_Home_Svc = $DataLaporan[$i]->Home_Svc;
						$SumByKodeNota_PPH = $PPH;
						$SumByKodeNota_SubTotal = $SubTotal;
	
						$SumByCancelled_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
						$SumByCancelled_PPN = $PPN;
						$SumByCancelled_Home_Svc = $DataLaporan[$i]->Home_Svc;
						$SumByCancelled_PPH = $PPH;
						$SumByCancelled_SubTotal = $SubTotal;
	
						$SumByTipeSvc_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
						$SumByTipeSvc_PPN = $PPN;
						$SumByTipeSvc_Home_Svc = $DataLaporan[$i]->Home_Svc;
						$SumByTipeSvc_PPH = $PPH;
						$SumByTipeSvc_SubTotal = $SubTotal;
	
					} else {
	
						if (trim($DataLaporan[$i]->Type_Svc)!=$Group3_TipeSvc){

							$content .='<tr>
											<td style="text-align: left; width: 5%; font-size: 12px; padding:5px; font-weight: bold;"></td>
											<td style="text-align: left; width: 39%; font-size: 12px; padding:5px; font-weight: bold;" colspan="6">TOTAL TIPE SERVICE : '.$Group3_TipeSvc.'</td>
											<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_Ongkos_Svc,0,",",".").'</td>
											<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_PPN,0,",",".").'</td>
											<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_Home_Svc,0,",",".").'</td>
											<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_PPH,0,",",".").'</td>
											<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_SubTotal,0,",",".").'</td>
											<td style="text-align: left; width: 16%; font-size: 12px; padding:5px; font-weight: bold;"></td>
										</tr>';	

							$Group3_TipeSvc = trim($DataLaporan[$i]->Type_Svc);
							$SumByTipeSvc_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
							$SumByTipeSvc_PPN = $PPN;
							$SumByTipeSvc_Home_Svc = $DataLaporan[$i]->Home_Svc;
							$SumByTipeSvc_PPH = $PPH;
							$SumByTipeSvc_SubTotal = $SubTotal;
						} else {
							$SumByTipeSvc_Ongkos_Svc += $DataLaporan[$i]->Ongkos_Svc;
							$SumByTipeSvc_PPN += $PPN;
							$SumByTipeSvc_Home_Svc += $DataLaporan[$i]->Home_Svc;
							$SumByTipeSvc_PPH += $PPH;
							$SumByTipeSvc_SubTotal += $SubTotal;
						}
	
						if (trim($DataLaporan[$i]->Cancelled)!=$Group2_Cancelled){

							$content .='<tr>
											<td style="text-align: left; width: 5%; font-size: 12px; padding:5px; font-weight: bold;"></td>
											<td style="text-align: left; width: 39%; font-size: 12px; padding:5px; font-weight: bold;" colspan="6">TOTAL CANCELLED : '.$Group2_Cancelled.'</td>
											<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_Ongkos_Svc,0,",",".").'</td>
											<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_PPN,0,",",".").'</td>
											<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_Home_Svc,0,",",".").'</td>
											<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_PPH,0,",",".").'</td>
											<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_SubTotal,0,",",".").'</td>
											<td style="text-align: left; width: 16%; font-size: 12px; padding:5px; font-weight: bold;"></td>
										</tr>';	
										
							$Group2_Cancelled = trim($DataLaporan[$i]->Cancelled);
							$SumByCancelled_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
							$SumByCancelled_PPN = $PPN;
							$SumByCancelled_Home_Svc = $DataLaporan[$i]->Home_Svc;
							$SumByCancelled_PPH = $PPH;
							$SumByCancelled_SubTotal = $SubTotal;
					  } else {
							$SumByCancelled_Ongkos_Svc += $DataLaporan[$i]->Ongkos_Svc;
							$SumByCancelled_PPN += $PPN;
							$SumByCancelled_Home_Svc += $DataLaporan[$i]->Home_Svc;
							$SumByCancelled_PPH += $PPH;
							$SumByCancelled_SubTotal += $SubTotal;
						}
	
						if (trim($DataLaporan[$i]->KodeNota)!=$Group1_KodeNota){

							$content .='<tr>
											<td style="text-align: left; width: 5%; font-size: 12px; padding:5px; font-weight: bold;"></td>
											<td style="text-align: left; width: 39%; font-size: 12px; padding:5px; font-weight: bold;" colspan="6">TOTAL KODE NOTA : '.$Group1_KodeNota.'</td>
											<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_Ongkos_Svc,0,",",".").'</td>
											<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_PPN,0,",",".").'</td>
											<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_Home_Svc,0,",",".").'</td>
											<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_PPH,0,",",".").'</td>
											<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_SubTotal,0,",",".").'</td>
											<td style="text-align: left; width: 16%; font-size: 12px; padding:5px; font-weight: bold;"></td>
										</tr>';	

							$Group1_KodeNota = trim($DataLaporan[$i]->KodeNota);
							$SumByKodeNota_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
							$SumByKodeNota_PPN = $PPN;
							$SumByKodeNota_Home_Svc = $DataLaporan[$i]->Home_Svc;
							$SumByKodeNota_PPH = $PPH;
							$SumByKodeNota_SubTotal = $SubTotal;
  
						} else {
							$SumByKodeNota_Ongkos_Svc += $DataLaporan[$i]->Ongkos_Svc;
							$SumByKodeNota_PPN += $PPN;
							$SumByKodeNota_Home_Svc += $DataLaporan[$i]->Home_Svc;
							$SumByKodeNota_PPH += $PPH;
							$SumByKodeNota_SubTotal += $SubTotal;
						}
	
					}    

					//start isi data - from here
					if ($DataLaporan[$i]->Jaminan=="Y"){
						$garansi = "x";
						$tidakgaransi = "";
					} else {
						$garansi = "";
						$tidakgaransi = "x";
					}

					if ($DataLaporan[$i]->Cancelled=="Y"){
						if (strtoupper(trim($DataLaporan[$i]->Kd_Brg))=="JUAL PARTS")
						{
							$alasanbatal = "JUAL PART " + trim($DataLaporan[$i]->Alasan);
						} else {
							$alasanbatal = trim($DataLaporan[$i]->Alasan);
						}
					} else {
						$alasanbatal = "";
					}

					if ($i==$jum-1){
						$content .='<tr>
						<td style="text-align: center; width: 5%; font-size: 12px; padding:5px;">'.$no.'</td>
						<td style="text-align: left; width: 10%; font-size: 12px; padding:5px;">'.trim($DataLaporan[$i]->No_Svc).'</td>
						<td style="text-align: left; width: 8%; font-size: 12px; padding:5px;">'.trim($DataLaporan[$i]->Type_Svc).'</td>
						<td style="text-align: center; width: 8%; font-size: 12px; padding:5px;">'.date("d-M-Y", strtotime($DataLaporan[$i]->Tgl_Svc)).'</td>
						<td style="text-align: center; width: 8%; font-size: 12px; padding:5px;">'.date("d-M-Y", strtotime($DataLaporan[$i]->tgl_trans)).'</td>
						<td style="text-align: center; width: 5%; font-size: 12px; padding:5px;">'.$garansi.'</td>
						<td style="text-align: center; width: 5%; font-size: 12px; padding:5px;">'.$tidakgaransi.'</td>
						<td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->Ongkos_Svc,0,",",".").'</td>
						<td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($PPN,0,",",".").'</td>
						<td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->Home_Svc,0,",",".").'</td>
						<td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($PPH,0,",",".").'</td>
						<td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($SubTotal,0,",",".").'</td>
						<td style="text-align: left; width: 16%; font-size: 12px; padding:5px;">'.$alasanbatal.'</td>
						</tr>';
					} else {
						$content .='<tr>
						<td style="text-align: center; width: 5%; font-size: 12px; padding:5px;">'.$no.'</td>
						<td style="text-align: left; width: 10%; font-size: 12px; padding:5px;">'.trim($DataLaporan[$i]->No_Svc).'</td>
						<td style="text-align: left; width: 8%; font-size: 12px; padding:5px;">'.trim($DataLaporan[$i]->Type_Svc).'</td>
						<td style="text-align: center; width: 8%; font-size: 12px; padding:5px;">'.date("d-M-Y", strtotime($DataLaporan[$i]->Tgl_Svc)).'</td>
						<td style="text-align: center; width: 8%; font-size: 12px; padding:5px;">'.date("d-M-Y", strtotime($DataLaporan[$i]->tgl_trans)).'</td>
						<td style="text-align: center; width: 5%; font-size: 12px; padding:5px;">'.$garansi.'</td>
						<td style="text-align: center; width: 5%; font-size: 12px; padding:5px;">'.$tidakgaransi.'</td>
						<td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->Ongkos_Svc,0,",",".").'</td>
						<td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($PPN,0,",",".").'</td>
						<td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->Home_Svc,0,",",".").'</td>
						<td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($PPH,0,",",".").'</td>
						<td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($SubTotal,0,",",".").'</td>
						<td style="text-align: left; width: 16%; font-size: 12px; padding:5px;">'.$alasanbatal.'</td>
						</tr>';
					}

					$SumByAll_Ongkos_Svc += $DataLaporan[$i]->Ongkos_Svc;
					$SumByAll_PPN += $PPN;
					$SumByAll_Home_Svc += $DataLaporan[$i]->Home_Svc;
					$SumByAll_PPH += $PPH;
					$SumByAll_SubTotal += $SubTotal; 

					$no++;
					//start isi data - until here  
				}
			
				$content .='<tr>
								<td style="text-align: left; width: 5%; font-size: 12px; padding:5px; font-weight: bold;"></td>
								<td style="text-align: left; width: 39%; font-size: 12px; padding:5px; font-weight: bold;" colspan="6">TOTAL TIPE SERVICE : '.$Group3_TipeSvc.'</td>
								<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_Ongkos_Svc,0,",",".").'</td>
								<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_PPN,0,",",".").'</td>
								<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_Home_Svc,0,",",".").'</td>
								<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_PPH,0,",",".").'</td>
								<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_SubTotal,0,",",".").'</td>
								<td style="text-align: left; width: 16%; font-size: 12px; padding:5px; font-weight: bold;"></td>
							</tr>';	

				$content .='<tr>
								<td style="text-align: left; width: 5%; font-size: 12px; padding:5px; font-weight: bold;"></td>
								<td style="text-align: left; width: 39%; font-size: 12px; padding:5px; font-weight: bold;" colspan="6">TOTAL CANCELLED : '.$Group2_Cancelled.'</td>
								<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_Ongkos_Svc,0,",",".").'</td>
								<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_PPN,0,",",".").'</td>
								<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_Home_Svc,0,",",".").'</td>
								<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_PPH,0,",",".").'</td>
								<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_SubTotal,0,",",".").'</td>
								<td style="text-align: left; width: 16%; font-size: 12px; padding:5px; font-weight: bold;"></td>
							</tr>';	

				$content .='<tr>
								<td style="text-align: left; width: 5%; font-size: 12px; padding:5px; font-weight: bold;"></td>
								<td style="text-align: left; width: 39%; font-size: 12px; padding:5px; font-weight: bold;" colspan="6">TOTAL KODE NOTA : '.$Group1_KodeNota.'</td>
								<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_Ongkos_Svc,0,",",".").'</td>
								<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_PPN,0,",",".").'</td>
								<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_Home_Svc,0,",",".").'</td>
								<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_PPH,0,",",".").'</td>
								<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_SubTotal,0,",",".").'</td>
								<td style="text-align: left; width: 16%; font-size: 12px; padding:5px; font-weight: bold;"></td>
							</tr>';	

				$content .='<tr>
							<td style="text-align: left; width: 5%; font-size: 12px; padding:5px; font-weight: bold;"></td>
							<td style="text-align: left; width: 39%; font-size: 12px; padding:5px; font-weight: bold;" colspan="6">GRAND TOTAL</td>
							<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByAll_Ongkos_Svc,0,",",".").'</td>
							<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByAll_PPN,0,",",".").'</td>
							<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByAll_Home_Svc,0,",",".").'</td>
							<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByAll_PPH,0,",",".").'</td>
							<td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByAll_SubTotal,0,",",".").'</td>
							<td style="text-align: left; width: 16%; font-size: 12px; padding:5px; font-weight: bold;"></td>
						</tr></table>';	

				set_time_limit(0);
				if($output=='HTML'){
					echo ($header.$content);
				} else {
					$mpdf->SetHTMLHeader($header,'','1');
					$mpdf->WriteHTML($content);
					$mpdf->Output();
				}
			} else {
				set_time_limit(0);
				if($output=='HTML'){
					echo ("Tidak Ada Data");
				} else {
					$mpdf->SetHTMLHeader("Tidak Ada Data",'','1');
					$mpdf->WriteHTML("");
					$mpdf->Output();
				}
			}

		}
	}

/*
		//09 Summary Service Harian
		public function proses_pdf_html_09 ($page_title, $excel_title, $param, $DataLaporan, $output)
		{	
			//ini_set('max_execution_time', '1500');
			ini_set("pcre.backtrack_limit", "10000000");
			set_time_limit(60);

			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'arial',
				'margin_left' => 10,
				'margin_right' => 10,
				'margin_top' => 35,
				'margin_bottom' => 15,
				'margin_header' => 10,
				'margin_footer' => 5,
				'orientation' => 'P'
			));

            $PrintDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));

            $SumByKodeNota_Ongkos_Svc = 0;
            $SumByKodeNota_PPN = 0;
            $SumByKodeNota_Home_Svc = 0;
            $SumByKodeNota_PPH = 0;
            $SumByKodeNota_SubTotal = 0; 

            $SumByCancelled_Ongkos_Svc = 0;
            $SumByCancelled_PPN = 0;
            $SumByCancelled_Home_Svc = 0;
            $SumByCancelled_PPH = 0;
            $SumByCancelled_SubTotal = 0; 

            $SumByTipeSvc_Ongkos_Svc = 0;
            $SumByTipeSvc_PPN = 0;
            $SumByTipeSvc_Home_Svc = 0;
            $SumByTipeSvc_PPH = 0;
            $SumByTipeSvc_SubTotal = 0; 

            $SumByAll_Ongkos_Svc = 0;
            $SumByAll_PPN = 0;
            $SumByAll_Home_Svc = 0;
            $SumByAll_PPH = 0;
            $SumByAll_SubTotal = 0;

            $header = '<table border="0" style="width:100%;">
                    <tr>
                        <td align="center" style="font-size:10px;">
                                '.$page_title.'
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size:14px;">
                            <b>'.strtoupper($excel_title).'</b>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size:10px;">
                        '.trim($param["tanggal1"]).' '.date("d-M-Y", strtotime($param["dp11"])).' <b> - </b> '.date("d-M-Y", strtotime($param["dp12"])).'
                        </td>
                    </tr>';

            if($param["tanggal2"]!="TANGGAL-KOSONG"){
                $header .= '<tr>
                                <td align="center" style="font-size:10px;">
                                '.trim($param["tanggal2"]).' '.date("d-M-Y", strtotime($param["dp21"])).' <b> - </b> '.date("d-M-Y", strtotime($param["dp22"])).'
                                </td>
                            </tr>
                            </table>';
            }

            $header .= '<table width="100%" style="font-weight: bold;">
                        </table><hr>';

            $filter = 'Merk '.trim($param["merk"]).', Barang '.trim($param["kodebarang"]).', Teknisi '.trim($param["teknisi"]).', Garansi '.trim($param["garansi"]).', Metode Bayar '.trim($param["metodebayar"]).', Cetak '.trim($param["cetak"]).', Cetak Per Merk '.trim($param["cetakpermerk"]).', Status '.trim($param["status"]);
                            
            $footer = '<hr><div style="font-size:7pt">
                       Filter Laporan :'.$filter.'<br>
                       Print By '.$_SESSION['logged_in']['username'].' on '.$PrintDate.'</div>';    
            $content = '
                <br><table width="100%">
                <tr>';

            $content .='<td style="text-align: center; width: 5%; font-size: 12px; padding:5px; font-weight: bold;">No</td>
                        <td style="text-align: left; width: 10%; font-size: 12px; padding:5px; font-weight: bold;">Nota Service</td>
                        <td style="text-align: left; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">Tipe Service</td>
                        <td style="text-align: center; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">Tanggal</td>
                        <td style="text-align: center; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">Tanggal Trans</td>
                        <td style="text-align: center; width: 5%; font-size: 12px; padding:5px; font-weight: bold;">Garansi Y</td>
                        <td style="text-align: center; width: 5%; font-size: 12px; padding:5px; font-weight: bold;">Garansi T</td>
                        <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">Ongkos</td>
                        <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">PPN</td>
                        <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">Transport</td>
                        <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">PPH</td>
                        <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">Total</td>
                        <td style="text-align: left; width: 16%; font-size: 12px; padding:5px; font-weight: bold;">Batal/Alasan</td>
                        </tr>';

            $jum= count($DataLaporan);
            if ($jum!=0){
                $no = 1;
                for($i=0; $i<$jum; $i++){

                    $PPN = ($DataLaporan[$i]->Rate_PPN / 100) * $DataLaporan[$i]->Ongkos_Svc;
                    $PPH = ($DataLaporan[$i]->PPH / 100) * $DataLaporan[$i]->Ongkos_Svc;
                    $SubTotal = $DataLaporan[$i]->Ongkos_Svc + $DataLaporan[$i]->Home_Svc + $PPN + $PPH;

                    if ($i==0){
                        $Group1_KodeNota = trim($DataLaporan[$i]->KodeNota);
                        $Group2_Cancelled = trim($DataLaporan[$i]->Cancelled);
                        $Group3_TipeSvc = trim($DataLaporan[$i]->Type_Svc);
    
                        $SumByKodeNota_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
                        $SumByKodeNota_PPN = $PPN;
                        $SumByKodeNota_Home_Svc = $DataLaporan[$i]->Home_Svc;
                        $SumByKodeNota_PPH = $PPH;
                        $SumByKodeNota_SubTotal = $SubTotal;
    
                        $SumByCancelled_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
                        $SumByCancelled_PPN = $PPN;
                        $SumByCancelled_Home_Svc = $DataLaporan[$i]->Home_Svc;
                        $SumByCancelled_PPH = $PPH;
                        $SumByCancelled_SubTotal = $SubTotal;
    
                        $SumByTipeSvc_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
                        $SumByTipeSvc_PPN = $PPN;
                        $SumByTipeSvc_Home_Svc = $DataLaporan[$i]->Home_Svc;
                        $SumByTipeSvc_PPH = $PPH;
                        $SumByTipeSvc_SubTotal = $SubTotal;
    
                    } else {
    
                        if (trim($DataLaporan[$i]->Type_Svc)!=$Group3_TipeSvc){

                            $content .='<tr>
                                            <td style="text-align: left; width: 5%; font-size: 12px; padding:5px; font-weight: bold;"></td>
                                            <td style="text-align: left; width: 39%; font-size: 12px; padding:5px; font-weight: bold;" colspan="6">TOTAL TIPE SERVICE : '.$Group3_TipeSvc.'</td>
                                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_Ongkos_Svc,0,",",".").'</td>
                                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_PPN,0,",",".").'</td>
                                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_Home_Svc,0,",",".").'</td>
                                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_PPH,0,",",".").'</td>
                                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_SubTotal,0,",",".").'</td>
                                            <td style="text-align: left; width: 16%; font-size: 12px; padding:5px; font-weight: bold;"></td>
                                        </tr>';	

                            $Group3_TipeSvc = trim($DataLaporan[$i]->Type_Svc);
                            $SumByTipeSvc_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
                            $SumByTipeSvc_PPN = $PPN;
                            $SumByTipeSvc_Home_Svc = $DataLaporan[$i]->Home_Svc;
                            $SumByTipeSvc_PPH = $PPH;
                            $SumByTipeSvc_SubTotal = $SubTotal;
                        } else {
                            $SumByTipeSvc_Ongkos_Svc += $DataLaporan[$i]->Ongkos_Svc;
                            $SumByTipeSvc_PPN += $PPN;
                            $SumByTipeSvc_Home_Svc += $DataLaporan[$i]->Home_Svc;
                            $SumByTipeSvc_PPH += $PPH;
                            $SumByTipeSvc_SubTotal += $SubTotal;
                        }
    
                        if (trim($DataLaporan[$i]->Cancelled)!=$Group2_Cancelled){

                            $content .='<tr>
                                            <td style="text-align: left; width: 5%; font-size: 12px; padding:5px; font-weight: bold;"></td>
                                            <td style="text-align: left; width: 39%; font-size: 12px; padding:5px; font-weight: bold;" colspan="6">TOTAL CANCELLED : '.$Group2_Cancelled.'</td>
                                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_Ongkos_Svc,0,",",".").'</td>
                                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_PPN,0,",",".").'</td>
                                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_Home_Svc,0,",",".").'</td>
                                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_PPH,0,",",".").'</td>
                                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_SubTotal,0,",",".").'</td>
                                            <td style="text-align: left; width: 16%; font-size: 12px; padding:5px; font-weight: bold;"></td>
                                        </tr>';	
                                        
                            $Group2_Cancelled = trim($DataLaporan[$i]->Cancelled);
                            $SumByCancelled_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
                            $SumByCancelled_PPN = $PPN;
                            $SumByCancelled_Home_Svc = $DataLaporan[$i]->Home_Svc;
                            $SumByCancelled_PPH = $PPH;
                            $SumByCancelled_SubTotal = $SubTotal;
                      } else {
                            $SumByCancelled_Ongkos_Svc += $DataLaporan[$i]->Ongkos_Svc;
                            $SumByCancelled_PPN += $PPN;
                            $SumByCancelled_Home_Svc += $DataLaporan[$i]->Home_Svc;
                            $SumByCancelled_PPH += $PPH;
                            $SumByCancelled_SubTotal += $SubTotal;
                        }
    
                        if (trim($DataLaporan[$i]->KodeNota)!=$Group1_KodeNota){

                            $content .='<tr>
                                            <td style="text-align: left; width: 5%; font-size: 12px; padding:5px; font-weight: bold;"></td>
                                            <td style="text-align: left; width: 39%; font-size: 12px; padding:5px; font-weight: bold;" colspan="6">TOTAL KODE NOTA : '.$Group1_KodeNota.'</td>
                                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_Ongkos_Svc,0,",",".").'</td>
                                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_PPN,0,",",".").'</td>
                                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_Home_Svc,0,",",".").'</td>
                                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_PPH,0,",",".").'</td>
                                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_SubTotal,0,",",".").'</td>
                                            <td style="text-align: left; width: 16%; font-size: 12px; padding:5px; font-weight: bold;"></td>
                                        </tr>';	

                            $Group1_KodeNota = trim($DataLaporan[$i]->KodeNota);
                            $SumByKodeNota_Ongkos_Svc = $DataLaporan[$i]->Ongkos_Svc;
                            $SumByKodeNota_PPN = $PPN;
                            $SumByKodeNota_Home_Svc = $DataLaporan[$i]->Home_Svc;
                            $SumByKodeNota_PPH = $PPH;
                            $SumByKodeNota_SubTotal = $SubTotal;
  
                        } else {
                            $SumByKodeNota_Ongkos_Svc += $DataLaporan[$i]->Ongkos_Svc;
                            $SumByKodeNota_PPN += $PPN;
                            $SumByKodeNota_Home_Svc += $DataLaporan[$i]->Home_Svc;
                            $SumByKodeNota_PPH += $PPH;
                            $SumByKodeNota_SubTotal += $SubTotal;
                        }
    
                    }    

                    //start isi data - from here
                    if ($DataLaporan[$i]->Jaminan=="Y"){
                        $garansi = "x";
                        $tidakgaransi = "";
                    } else {
                        $garansi = "";
                        $tidakgaransi = "x";
                    }

                    if ($DataLaporan[$i]->Cancelled=="Y"){
                        if (strtoupper(trim($DataLaporan[$i]->Kd_Brg))=="JUAL PARTS")
                        {
                            $alasanbatal = "JUAL PART " + trim($DataLaporan[$i]->Alasan);
                        } else {
                            $alasanbatal = trim($DataLaporan[$i]->Alasan);
                        }
                    } else {
                        $alasanbatal = "";
                    }


                    if(!empty($DataLaporan[$i]->Tgl_Svc) && $DataLaporan[$i]->Tgl_Svc!==null){
                        $Tgl_Svc = date("d-M-Y", strtotime($DataLaporan[$i]->Tgl_Svc));
                    }else{
                        $Tgl_Svc = '';
                    }


                    if(!empty($DataLaporan[$i]->tgl_trans) && $DataLaporan[$i]->tgl_trans!==null){
                        $Tgl_Trans = date("d-M-Y", strtotime($DataLaporan[$i]->tgl_trans));
                    }else{
                        $Tgl_Trans = '';
                    }
                    

                    if ($i==$jum-1){
                        $content .='<tr>
                        <td style="text-align: center; width: 5%; font-size: 12px; padding:5px;">'.$no.'</td>
                        <td style="text-align: left; width: 10%; font-size: 12px; padding:5px;">'.trim($DataLaporan[$i]->No_Svc).'</td>
                        <td style="text-align: left; width: 8%; font-size: 12px; padding:5px;">'.trim($DataLaporan[$i]->Type_Svc).'</td>
                        <td style="text-align: center; width: 8%; font-size: 12px; padding:5px;">'.date("d-M-Y", strtotime($DataLaporan[$i]->Tgl_Svc)).'</td>
                        <td style="text-align: center; width: 8%; font-size: 12px; padding:5px;">'.date("d-M-Y", strtotime($DataLaporan[$i]->Tgl_Trans)).'</td>
                        <td style="text-align: center; width: 5%; font-size: 12px; padding:5px;">'.$garansi.'</td>
                        <td style="text-align: center; width: 5%; font-size: 12px; padding:5px;">'.$tidakgaransi.'</td>
                        <td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->Ongkos_Svc,0,",",".").'</td>
                        <td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($PPN,0,",",".").'</td>
                        <td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->Home_Svc,0,",",".").'</td>
                        <td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($PPH,0,",",".").'</td>
                        <td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($SubTotal,0,",",".").'</td>
                        <td style="text-align: left; width: 16%; font-size: 12px; padding:5px;">'.$alasanbatal.'</td>
                        </tr>';
                    } else {
                        $content .='<tr>
                        <td style="text-align: center; width: 5%; font-size: 12px; padding:5px;">'.$no.'</td>
                        <td style="text-align: left; width: 10%; font-size: 12px; padding:5px;">'.trim($DataLaporan[$i]->No_Svc).'</td>
                        <td style="text-align: left; width: 8%; font-size: 12px; padding:5px;">'.trim($DataLaporan[$i]->Type_Svc).'</td>
                        <td style="text-align: center; width: 8%; font-size: 12px; padding:5px;">'.date("d-M-Y", strtotime($DataLaporan[$i]->Tgl_Svc)).'</td>
                        <td style="text-align: center; width: 8%; font-size: 12px; padding:5px;">'.date("d-M-Y", strtotime($DataLaporan[$i]->Tgl_Trans)).'</td>
                        <td style="text-align: center; width: 5%; font-size: 12px; padding:5px;">'.$garansi.'</td>
                        <td style="text-align: center; width: 5%; font-size: 12px; padding:5px;">'.$tidakgaransi.'</td>
                        <td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->Ongkos_Svc,0,",",".").'</td>
                        <td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($PPN,0,",",".").'</td>
                        <td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($DataLaporan[$i]->Home_Svc,0,",",".").'</td>
                        <td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($PPH,0,",",".").'</td>
                        <td style="text-align: right; width: 8%; font-size: 12px; padding:5px;">'.number_format($SubTotal,0,",",".").'</td>
                        <td style="text-align: left; width: 16%; font-size: 12px; padding:5px;">'.$alasanbatal.'</td>
                        </tr>';
                    }

                    $SumByAll_Ongkos_Svc += $DataLaporan[$i]->Ongkos_Svc;
                    $SumByAll_PPN += $PPN;
                    $SumByAll_Home_Svc += $DataLaporan[$i]->Home_Svc;
                    $SumByAll_PPH += $PPH;
                    $SumByAll_SubTotal += $SubTotal; 

                    $no++;
                    //start isi data - until here  
                }
            
                $content .='<tr>
                                <td style="text-align: left; width: 5%; font-size: 12px; padding:5px; font-weight: bold;"></td>
                                <td style="text-align: left; width: 39%; font-size: 12px; padding:5px; font-weight: bold;" colspan="6">TOTAL TIPE SERVICE : '.$Group3_TipeSvc.'</td>
                                <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_Ongkos_Svc,0,",",".").'</td>
                                <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_PPN,0,",",".").'</td>
                                <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_Home_Svc,0,",",".").'</td>
                                <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_PPH,0,",",".").'</td>
                                <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByTipeSvc_SubTotal,0,",",".").'</td>
                                <td style="text-align: left; width: 16%; font-size: 12px; padding:5px; font-weight: bold;"></td>
                            </tr>';	

                $content .='<tr>
                                <td style="text-align: left; width: 5%; font-size: 12px; padding:5px; font-weight: bold;"></td>
                                <td style="text-align: left; width: 39%; font-size: 12px; padding:5px; font-weight: bold;" colspan="6">TOTAL CANCELLED : '.$Group2_Cancelled.'</td>
                                <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_Ongkos_Svc,0,",",".").'</td>
                                <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_PPN,0,",",".").'</td>
                                <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_Home_Svc,0,",",".").'</td>
                                <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_PPH,0,",",".").'</td>
                                <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByCancelled_SubTotal,0,",",".").'</td>
                                <td style="text-align: left; width: 16%; font-size: 12px; padding:5px; font-weight: bold;"></td>
                            </tr>';	

                $content .='<tr>
                                <td style="text-align: left; width: 5%; font-size: 12px; padding:5px; font-weight: bold;"></td>
                                <td style="text-align: left; width: 39%; font-size: 12px; padding:5px; font-weight: bold;" colspan="6">TOTAL KODE NOTA : '.$Group1_KodeNota.'</td>
                                <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_Ongkos_Svc,0,",",".").'</td>
                                <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_PPN,0,",",".").'</td>
                                <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_Home_Svc,0,",",".").'</td>
                                <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_PPH,0,",",".").'</td>
                                <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByKodeNota_SubTotal,0,",",".").'</td>
                                <td style="text-align: left; width: 16%; font-size: 12px; padding:5px; font-weight: bold;"></td>
                            </tr>';	

                $content .='<tr>
                            <td style="text-align: left; width: 5%; font-size: 12px; padding:5px; font-weight: bold;"></td>
                            <td style="text-align: left; width: 39%; font-size: 12px; padding:5px; font-weight: bold;" colspan="6">GRAND TOTAL</td>
                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByAll_Ongkos_Svc,0,",",".").'</td>
                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByAll_PPN,0,",",".").'</td>
                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByAll_Home_Svc,0,",",".").'</td>
                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByAll_PPH,0,",",".").'</td>
                            <td style="text-align: right; width: 8%; font-size: 12px; padding:5px; font-weight: bold;">'.number_format($SumByAll_SubTotal,0,",",".").'</td>
                            <td style="text-align: left; width: 16%; font-size: 12px; padding:5px; font-weight: bold;"></td>
                        </tr></table>';	

                set_time_limit(60);
                if($output=='HTML'){
                    echo ($header.$content.$footer);
                } else {
                    $mpdf->SetHTMLHeader($header,'','1');
                    $mpdf->SetHTMLFooter($footer);
                    $mpdf->WriteHTML($content);
                    $mpdf->Output();
                }
            } else {
                set_time_limit(60);
                if($output=='HTML'){
                    echo ("Tidak Ada Data");
                } else {
                    $mpdf->SetHTMLHeader("Tidak Ada Data",'','1');
                    $mpdf->WriteHTML("");
                    $mpdf->Output();
                }
            }

        }
    }*/
    ?>
