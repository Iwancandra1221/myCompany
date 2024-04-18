<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TargetSalesman extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();	
		$this->load->model("TargetSalesmanModel");
        $this->load->model('ConfigSysModel');
        $this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
        $this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
    }

	public function TestConnection()
	{

		$data = array("result"=> "sukses",
					  "jam" => (int)date("H"));								

		echo json_encode($data);
		// header('HTTP/1.1: 200');
		// header('Status: 200');
		// header('Content-Length: '.strlen($hasil));
		// exit($hasil);

	}    

    public function Proses()
    {
     	
		$kode_target = $this->input->get("kode_target");	
        $user = $this->input->get("user");	
        $wilayah = $this->input->get("wilayah");
		$action = $this->input->get("action");        
        
        $ApprovedTime = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));
                
        //cari AlamatWEBService DB BHAKTI cabang yang diinginkan
        $conn = $this->TargetSalesmanModel->get($wilayah);
        if ($conn!=null) {
            $connected = false;

            set_time_limit(3);

            try {
                set_time_limit(60);        
                $TEST_CONN = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApproval/TestConnection"), true);
                if ($TEST_CONN["result"]=="sukses") {
                    $connected = true;
                }            
            }
            catch (Exception $e) {
                echo $e->getMessage();
            }
            catch (InvalidArgumentException $e) {
                echo $e->getMessage();
            } 

            if ($connected) {
                set_time_limit(60);
                $result = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApproval/Proses?".
                "kode_target=".$kode_target."&user=".$user."&action=".$action),true);

                if ($result) {                
                    // $result = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApproval/Proses?".
                    //             "kode_target=".$kode_target."&user=".$user."&action=".$action), true);

                    echo $result['pesan']; 
                } else {
                    //apabila http request error (server cabang yg dituju sedang mati), 
                    //maka approval/reject disimpan di table lokal HRDMC dahulu            
                    $sudahada=$this->TargetSalesmanModel->cari_pendingan($kode_target);
                    if ($sudahada){
                        echo "Request Tidak Berhasil Disimpan ke DB Bhakti, Sudah Ditampung Di Tabel Antrian";
                    } else {
                        $this->TargetSalesmanModel->insertRequestApproval($kode_target,$user,$wilayah,$action,$ApprovedTime);
                        echo "Request Tidak Berhasil Disimpan ke DB Bhakti, Akan Ditampung Dulu Di Tabel Antrian";
                    }
                }

            } else {
                //echo "Cek Function TestConnection";
                echo "Server yang dituju sedang tidak aktif";
            }
        } else {
            echo "Alamat WEB Service Belum Disetting";
        }        
    }

    public function JobRunPendingApproval()
    {
        $data= $this->TargetSalesmanModel->get_list_pendingan();
        //echo json_encode($data);

        for ($i=0;$i<count($data);$i++) {
           
            $kode_target=$data[$i]['kode_target'];
            $user=$data[$i]['user'];
            $wilayah=$data[$i]['wilayah'];
            $action=$data[$i]['action'];
            $ApprovedTime=$data[$i]['ApprovedTime'];

            //cari AlamatWEBService DB BHAKTI cabang yang diinginkan
            $conn = $this->TargetSalesmanModel->get($wilayah);
            if ($conn!=null) {
                $connected = false;

                set_time_limit(3);

                try {
                    set_time_limit(60);        
                    $TEST_CONN = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApproval/TestConnection"), true);
                    if ($TEST_CONN["result"]=="sukses") {
                        $connected = true;
                    }            
                }
                catch (Exception $e) {
                    echo $e->getMessage();
                }
                catch (InvalidArgumentException $e) {
                    echo $e->getMessage();
                } 

                if ($connected) {
                    set_time_limit(60);
                    $result = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApproval/Proses?".
                    "kode_target=".$kode_target."&user=".$user."&action=".$action),true);

                    if ($result) {    
                        $this->TargetSalesmanModel->updateRequestApproval($kode_target,$user,$wilayah,$action,$ApprovedTime);
                    }
                } 
            }                    
        }
    }    


    public function ProsesNew()
    {
         //http://localhost:8080/mycompany/TargetSalesman/ProsesNew?norequest=DEL-DEL2004_20200407150644
         //&kode_target=DEL-DEL2004&RequestBy=DEL-DEL&RequestByName=DELINA&RequestByEmail=d5autumn2008@yahoo.com
         //&RequestDate=07%20Apr%202020%2015:06:44:973&ApprovedBy=DEL-DEL&
         //ApprovedByName=DELINA&ApprovedByEmail=d5autumn2008@yahoo.com&wilayah=JAKARTA&action=APPROVE
		$params = array();
        $params["norequest"] = urldecode($this->input->get("norequest"));
        $params["kode_target"] = urldecode($this->input->get("kode_target"));
		$params["RequestBy"] = urldecode($this->input->get("RequestBy"));
		$params["RequestByName"] = urldecode($this->input->get("RequestByName"));
        $params["RequestByEmail"] = urldecode($this->input->get("RequestByEmail"));
        $params["RequestDate"] = urldecode($this->input->get("RequestDate"));
		$params["ApprovedBy"] = urldecode($this->input->get("ApprovedBy"));
		$params["ApprovedByName"] = urldecode($this->input->get("ApprovedByName"));
        $params["ApprovedByEmail"] = urldecode($this->input->get("ApprovedByEmail"));	
        $params["wilayah"] = urldecode($this->input->get("wilayah"));  	
		$params["action"] = $this->input->get("action");  
        $params["ApprovedTime"] = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));
                
        //cari AlamatWEBService DB BHAKTI cabang yang diinginkan
        $conn = $this->TargetSalesmanModel->get($params["wilayah"]);
        if ($conn!=null) {
            $connected = false;

            set_time_limit(3);

            try {
                set_time_limit(60);        
                $TEST_CONN = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApproval/TestConnection"), true);
                if ($TEST_CONN["result"]=="sukses") {
                    $connected = true;
                }            
            }
            catch (Exception $e) {
                echo $e->getMessage();
            }
            catch (InvalidArgumentException $e) {
                echo $e->getMessage();
            } 

            if ($connected) {
                set_time_limit(60);
                $result = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApproval/ProsesNew?".
                "norequest=".urlencode($params["norequest"]).
                "&kode_target=".urlencode($params["kode_target"]).
                "&user=".urlencode($params["ApprovedByEmail"]).
                "&action=".$params["action"].
                "&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID).
                "&pwd=".urlencode(SQL_PWD)),true);

                // echo $conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApproval/ProsesNew?".
                // "norequest=".$params["norequest"].
                // "&kode_target=".$params["kode_target"].
                // "&user=".$params["ApprovedByEmail"].
                // "&action=".$params["action"].
                // "&svr=".$conn->Server."&db=".$conn->Database."&uid=".SQL_UID.
                // "&pwd=".SQL_PWD;

                //http://localhost:8080/bktAPI/TargetSalesmanApproval/ProsesNew?norequest=DMI-DED2004_20200402100903&user=d5autumn2008@yahoo.com&action=APPROVE&svr=localhost&db=BHAKTI&uid=sa&pwd=Sprite12345

                if ($result) {                
                    // $result = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApproval/Proses?".
                    //             "kode_target=".$kode_target."&user=".$user."&action=".$action), true);
                    $sudahada=$this->TargetSalesmanModel->cari_pendinganNew($params);
                    if ($sudahada){
                        $this->TargetSalesmanModel->updateRequestApprovalNew($params);
                    } else {
                        echo $result['pesan']; 
                    }                    
                } else {
                    //apabila http request error (server cabang yg dituju sedang mati), 
                    //maka approval/reject disimpan di table lokal HRDMC dahulu            
                    $sudahada=$this->TargetSalesmanModel->cari_pendinganNew($params);
                    if ($sudahada){
                        echo "Request Tidak Berhasil Disimpan ke DB Bhakti, Sudah Ditampung Di Tabel Antrian Approval";
                    } else {
                        $this->TargetSalesmanModel->insertRequestApprovalNew($params);
                        echo "Request Tidak Berhasil Disimpan ke DB Bhakti, Akan Ditampung Dulu Di Tabel Antrian Approval";
                    }
                }

            } else {
                //echo "Cek Function TestConnection";
                echo "Server yang dituju sedang tidak aktif";
            }
        } else {
            echo "Alamat WEB Service Belum Disetting";
        }        
    }

    public function CallWebAPI()
    {
        $data = urldecode($this->input->get("data"));      
        $url = $this->API_URL."/TargetSalesman/UpdateKeDBPusat?data=".urlencode(($data));
        //echo $url;       
        $result = json_decode(file_get_contents($url),false); 
        if ($result) {   
            //$x = array("pesan" => "Target Salesman Berhasil Diupdate");	                         
            $x["pesan"]=$result->pesan;
        } else {
            $x["pesan"]="Tidak Connect Ke WebAPI";
        }        
        echo json_encode($x);
    }    
    
    public function JobRunPendingApprovalNew()
    {
        $data= $this->TargetSalesmanModel->get_list_pendinganNew();
        //echo json_encode($data);
        $sukses=0;
        for ($i=0;$i<count($data);$i++) {
           
            $params = array();
            $params["norequest"] = $data[$i]["norequest"];
            $params["kode_target"] = $data[$i]["kode_target"];
            $params["RequestBy"] = $data[$i]["RequestBy"];
            $params["RequestByName"] = $data[$i]["RequestByName"];
            $params["RequestByEmail"] = $data[$i]["RequestByEmail"];
            $params["RequestDate"] = $data[$i]["RequestDate"];
            $params["ApprovedBy"] = $data[$i]["ApprovedBy"];
            $params["ApprovedByName"] = $data[$i]["ApprovedByName"];
            $params["ApprovedByEmail"] = $data[$i]["ApprovedByEmail"];	
            $params["wilayah"] = $data[$i]["wilayah"];	
            $params["action"] = $data[$i]["action"];
            $params["ApprovedTime"] = $data[$i]["ApprovedTime"];

            $wilayah = $data[$i]["wilayah"];

           //cari AlamatWEBService DB BHAKTI cabang yang diinginkan
           $conn = $this->TargetSalesmanModel->get($wilayah);
           if ($conn!=null) 
           {
               $connected = false;

               set_time_limit(3);

               try {
                   set_time_limit(60);        
                   $TEST_CONN = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApproval/TestConnection"), true);
                   if ($TEST_CONN["result"]=="sukses") {
                       $connected = true;
                   }            
               }
               catch (Exception $e) {
                   echo $e->getMessage();
               }
               catch (InvalidArgumentException $e) {
                   echo $e->getMessage();
               } 

               if ($connected) {
                    set_time_limit(60);
                    $result = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApproval/ProsesNew?".
                    "norequest=".urlencode($params["norequest"]).
                    "&kode_target=".urlencode($params["kode_target"]).
                    "&user=".urlencode($params["ApprovedByEmail"]).
                    "&action=".$params["action"].
                    "&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID).
                    "&pwd=".urlencode(SQL_PWD)),true);                    

                    if ($result) 
                    {                

                        $this->TargetSalesmanModel->updateRequestApprovalNew($params);
                        $sukses++;
                    } 
               } 
           }                             
           echo $sukses." Target Salesman Berhasil DiApprove";                   
        }
    }    
    
    public function ProsesNew2()
    {
		$params = array();
        $params["norequest"] = urldecode($this->input->get("norequest"));
        $params["kode_target"] = urldecode($this->input->get("kode_target"));
		$params["user"] = urldecode($this->input->get("user"));
        $params["wilayah"] = urldecode($this->input->get("wilayah"));  	
		$params["action"] = $this->input->get("action");  
        $params["ApprovedTime"] = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));
                
        //cari AlamatWEBService DB BHAKTI cabang yang diinginkan
        $conn = $this->TargetSalesmanModel->get($params["wilayah"]);
        if ($conn!=null) {
            $connected = false;

            set_time_limit(3);

            try {
                set_time_limit(60);        
                $TEST_CONN = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApprovalNew/TestConnection"), true);
                if ($TEST_CONN["result"]=="sukses") {
                    $connected = true;
                }            
            }
            catch (Exception $e) {
                echo $e->getMessage();
            }
            catch (InvalidArgumentException $e) {
                echo $e->getMessage();
            } 

            if ($connected) {
                set_time_limit(60);
                $result = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApprovalNew/ProsesNew?".
                "norequest=".urlencode($params["norequest"]).
                "&kode_target=".urlencode($params["kode_target"]).
                "&user=".urlencode($params["user"]).
                "&action=".$params["action"].
                "&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID).
                "&pwd=".urlencode(SQL_PWD)),true);

                if ($result) {                
                    // $result = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApproval/Proses?".
                    //             "kode_target=".$kode_target."&user=".$user."&action=".$action), true);
                    $sudahada=$this->TargetSalesmanModel->cari_pendinganNew($params);
                    if ($sudahada){
                        $this->TargetSalesmanModel->updateRequestApprovalNew($params);
                    } else {
                        echo $result['pesan']; 
                    }                    
                } else {
                    //apabila http request error (server cabang yg dituju sedang mati), 
                    //maka approval/reject disimpan di table lokal HRDMC dahulu            
                    $sudahada=$this->TargetSalesmanModel->cari_pendinganNew($params);
                    if ($sudahada){
                        echo "Request Tidak Berhasil Disimpan ke DB Bhakti, Sudah Ditampung Di Tabel Antrian Approval";
                    } else {
                        $this->TargetSalesmanModel->insertRequestApprovalNew($params);
                        echo "Request Tidak Berhasil Disimpan ke DB Bhakti, Akan Ditampung Dulu Di Tabel Antrian Approval";
                    }
                }

            } else {
                //echo "Cek Function TestConnection";
                echo "Server yang dituju sedang tidak aktif";
            }
        } else {
            echo "Alamat WEB Service Belum Disetting";
        }        
    }

}
?>