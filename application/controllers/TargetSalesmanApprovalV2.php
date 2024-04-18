<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TargetSalesmanApprovalV2 extends MY_Controller 
{

    public $approvaltype = 'TARGET';
    public $approvedbyfrommsconfig = false;
    public $expirydatefrommsconfig = false;
    public $approvaldefault = 0;
    public $maxtimeout = 150;
    public $cc = "";

    public function __construct()
    {
        parent::__construct();  
        $this->load->model("SalesManagerModel");
        $this->load->model("TargetSalesmanModel");
        $this->load->model("approvalmodel");
        require_once(dirname(__FILE__)."/approval.php"); // the controller route.
        $this->approval = new approval();
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
                set_time_limit($this->maxtimeout);        
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
                set_time_limit($this->maxtimeout);
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
                    set_time_limit($this->maxtimeout);        
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
                    set_time_limit($this->maxtimeout);
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
                set_time_limit($this->maxtimeout);        
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
                set_time_limit($this->maxtimeout);
                $result = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApproval/ProsesNew?norequest=".urlencode($params["norequest"])."&kode_target=".urlencode($params["kode_target"])."&user=".urlencode($params["ApprovedByEmail"])."&action=".$params["action"]."&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD)),true);

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
                   set_time_limit($this->maxtimeout);        
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
                    set_time_limit($this->maxtimeout);
                    $result = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApproval/ProsesNew?norequest=".urlencode($params["norequest"])."&kode_target=".urlencode($params["kode_target"])."&user=".urlencode($params["ApprovedByEmail"])."&action=".$params["action"]."&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD)),true);                    

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
                set_time_limit($this->maxtimeout);        
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
                set_time_limit($this->maxtimeout);
                $result = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApprovalNew/ProsesNew?norequest=".urlencode($params["norequest"])."&kode_target=".urlencode($params["kode_target"])."&user=".urlencode($params["user"])."&action=".$params["action"]."&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD)),false);

                if ($result) {                
                    // $result = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApproval/Proses?".
                    //             "kode_target=".$kode_target."&user=".$user."&action=".$action), true);
                    $sudahada=$this->TargetSalesmanModel->cari_pendinganNew($params);
                    if ($sudahada){
                        $this->TargetSalesmanModel->updateRequestApprovalNew($params);
                        echo "Request Berhasil Di ".$params["action"];
                    } else {
                        echo "Request Berhasil Di ".$params["action"];
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

    public function ProsesNew3()
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
                set_time_limit($this->maxtimeout);        
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

            

            $url=str_replace("amp;", "",$conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApprovalNew/ProsesNew?norequest=".urlencode($params["norequest"])."&kode_target=".urlencode($params["kode_target"])."&user=".urlencode($params["user"])."&action=".$params["action"]."&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD));

            if ($connected) {
                set_time_limit($this->maxtimeout);
                $result = json_decode(file_get_contents($url),true);

                if ($result) {                
                    // $result = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApproval/Proses?".
                    //             "kode_target=".$kode_target."&user=".$user."&action=".$action), true);

                    if ($params["action"]=='APPROVE'){
                        $_POST = array();
                        $_POST ['ApprovalType'] = 'TARGET SPG'; //sementara hardcode dulu
                        $RequestNo = $this->ReplaceChars($params["norequest"]);
                        $_POST ['RequestNo'] = $RequestNo;
                        $_POST ['ApprovedBy'] = $_SESSION["logged_in"]["userid"];
                        $_POST ['ApprovedByEmail'] = $_SESSION["logged_in"]["useremail"];
                        $_POST ['ApprovalNote'] = '';
                        $approve_approval=$this->approval->approve();
                        //echo json_encode($approve_approval);                          
                    } else {
                        $_POST = array();
                        $_POST ['ApprovalType'] = 'TARGET SPG'; //sementara hardcode dulu
                        $RequestNo = $this->ReplaceChars($params["norequest"]);
                        $_POST ['RequestNo'] = $RequestNo;
                        $_POST ['ApprovedBy'] = $_SESSION["logged_in"]["userid"];
                        $_POST ['ApprovedByEmail'] = $_SESSION["logged_in"]["useremail"];
                        $_POST ['ApprovalNote'] = '';
                        $reject_approval=$this->approval->reject();
                        //echo json_encode($reject_approval);   
                    }

            
                    if ($params["action"]=='APPROVE'){
                        $_POST = array();
                        $_POST ['ApprovalType'] = 'TARGET SPG'; //sementara hardcode dulu
                        $RequestNo = $this->ReplaceChars($params["norequest"]);
                        $_POST ['RequestNo'] = $RequestNo;
                        $_POST ['ApprovedBy'] = $_SESSION["logged_in"]["userid"];
                        $_POST ['ApprovedByEmail'] = $_SESSION["logged_in"]["useremail"];
                        $_POST ['ApprovalNote'] = '';
                        $approve_approval=$this->approval->approve();
                        //echo json_encode($approve_approval);                          
                    } else {
                        $_POST = array();
                        $_POST ['ApprovalType'] = 'TARGET SPG'; //sementara hardcode dulu
                        $RequestNo = $this->ReplaceChars($params["norequest"]);
                        $_POST ['RequestNo'] = $RequestNo;
                        $_POST ['ApprovedBy'] = $_SESSION["logged_in"]["userid"];
                        $_POST ['ApprovedByEmail'] = $_SESSION["logged_in"]["useremail"];
                        $_POST ['ApprovalNote'] = '';
                        $reject_approval=$this->approval->reject();
                        //echo json_encode($reject_approval);   
                    }   

                    //$norequest, $svr, $db, $uid, $pwd, $kodetarget, $user, $action
                    $this->ProsesNew_Substitute_bktAPI(urlencode($params["norequest"]), urlencode($conn->Server), urlencode($conn->Database), urlencode(SQL_UID), urlencode(SQL_PWD), urlencode($params["kode_target"]), urlencode($params["user"]), $params["action"]);           

                } else {
                    //echo "Cek Function TestConnection";
                    echo "Server yang dituju sedang tidak aktif";
                }
            } else {
                echo "Alamat WEB Service Belum Disetting";
            }   
        }
        redirect("Approvallist/view/".$result["result"]);
    }

    function ReplaceChars($teks) 
    {
        $teks = str_replace("/","", $teks);
        $teks = str_replace(",","", $teks);
        $teks = str_replace(" ","", $teks);
        $teks = str_replace(".","", $teks);
        $teks = str_replace("'","", $teks);
        $teks = str_replace("-","", $teks);
        $teks = str_replace("__","",$teks);
        $teks = str_replace("_","",$teks);
        return $teks;       
    }


    // Dari NewBKT -> myCompany -> bktAPI JKT untuk Approval TS
    public function Email_Notifikasi()
    {
        $params = array();
        $params["mode"] = urldecode($this->input->get("mode"));
        $params["kategori"] = urldecode($this->input->get("kategori"));
        $params["userid"] = urldecode($this->input->get("userid"));
        $params["tanggal"] = urldecode($this->input->get("tanggal"));   
        $params["wilayah"] = "JAKARTA";      
               
        //cari AlamatWEBService DB BHAKTI cabang yang diinginkan
        $conn = $this->TargetSalesmanModel->get($params["wilayah"]);
        if ($conn!=null) {
            $connected = false;

            set_time_limit(3);

            try {
                set_time_limit($this->maxtimeout);        
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
                set_time_limit($this->maxtimeout);
                $result = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApprovalNew/AmbilDataDiPusatByTglInput?mode=".urlencode($params["mode"])."&kategori=".urlencode($params["kategori"])."&userid=".urlencode($params["userid"])."&tanggal=".urlencode($params["tanggal"])."&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD)),true);

                $this->simpanapproval("", $params, $conn);

                // ECHO $CONN->ALAMATWEBSERVICE.$this->API_BKT."/TARGETSALESMANAPPROVALNEW/AMBILDATADIPUSATBYTGLINPUT?".
                // "MODE=".URLENCODE($PARAMS["MODE"]).
                // "&KATEGORI=".URLENCODE($PARAMS["KATEGORI"]).
                // "&USERID=".URLENCODE($PARAMS["USERID"]).
                // "&TANGGAL=".URLENCODE($PARAMS["TANGGAL"]).
                // "&SVR=".URLENCODE($CONN->SERVER)."&DB=".URLENCODE($CONN->DATABASE)."&UID=".URLENCODE(SQL_UID).
                // "&PWD=".URLENCODE(SQL_PWD);

                // $x= array();
                // $x["pesan"]=$result["pesan"];
                // echo json_encode($x);
                echo ($result["pesan"]);              
            } else {
                //echo "Cek Function TestConnection";
                echo "Server yang dituju sedang tidak aktif";
            }
        } else {
            echo "Alamat WEB Service Belum Disetting";
        }        
    }

    public function Email_Notifikasi_ResendEmail()
    {
        $params = array();
        $params["mode"] = urldecode($this->input->get("mode"));
        $params["kategori"] = urldecode($this->input->get("kategori"));
        $params["userid"] = urldecode($this->input->get("userid"));
        $params["tanggal"] = urldecode($this->input->get("tanggal"));   
        $params["wilayah"] = "JAKARTA";      
                
        $conn = $this->TargetSalesmanModel->get($params["wilayah"]);
        if ($conn!=null) {
            $connected = false;

            set_time_limit(3);

            try {
                set_time_limit($this->maxtimeout);        
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
                set_time_limit($this->maxtimeout);
                $result = json_decode(file_get_contents($conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApprovalNew/AmbilDataDiPusatByTglInputResendEmail?mode=".urlencode($params["mode"])."&kategori=".urlencode($params["kategori"])."&userid=".urlencode($params["userid"])."&tanggal=".urlencode($params["tanggal"])."&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD)),true); 

                echo ($result["pesan"]);              
            } else { 
                echo "Server yang dituju sedang tidak aktif";
            }
        } else {
            echo "Alamat WEB Service Belum Disetting";
        }        
    }


    public function Email_Notifikasi_ByNoRequest()
    {
        // http://localhost:90/myCompany/TargetSalesmanApproval/Email_Notifikasi_ByNoRequest?norequest=MDN091230220230202135651
        $params = array();
        $params["norequest"] = urldecode($this->input->get("norequest"));
        $params["wilayah"] = "JAKARTA";     
               
        //cari AlamatWEBService DB BHAKTI cabang yang diinginkan
        $conn = $this->TargetSalesmanModel->get($params["wilayah"]);
        // die(json_encode($conn));

        if ($conn!=null) {
            $connected = false;

            set_time_limit(3);

            try {
                set_time_limit($this->maxtimeout);        
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

            $url = $conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApprovalNew/AmbilDataDiPusatByNoRequest?norequest=".urlencode($params["norequest"])."&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD);
            // die($url);
            

            if ($connected) {
                set_time_limit($this->maxtimeout);
                $result = json_decode(file_get_contents($url),true);

                $this->simpanapproval("NOREQUEST", $params, $conn);

                // $x= array();
                // $x["pesan"]=$result["pesan"];
                // echo json_encode($x);
                echo ($result["pesan"]);

            } else {
                //echo "Cek Function TestConnection";
                echo "Server yang dituju sedang tidak aktif";
            }
        } else {
            echo "Alamat WEB Service Belum Disetting";
        }        
    }

    public function ProsesTargetKPI()
    {
        $noRequest = urldecode($this->input->get("req"));
        $approvedBy = urldecode($this->input->get("app"));
        $this->ViewRequestTargetKPI($noRequest, $approvedBy);
    }

    public function ViewRequestTargetKPI($noRequest, $approvedBy, $msg="") 
    {
        // echo("No Request :".$noRequest."<br>");
        $URL = $this->API_URL."/TargetSalesman/AmbilTargetKPI?req=".urlencode($noRequest)."&app=".urlencode($approvedBy);
        // echo($URL);
        // echo("<br>");
        $GetRequest = json_decode(file_get_contents($URL), true);
        // echo(json_encode($GetRequest));die;
        // echo("<br><br>");

        if($GetRequest["result"]=="SUCCESS") {

            $req = $GetRequest["data"];

            $style = '<style>
                *{
                    font-family: Arial,sans-serif;
                    font-size:14px;
                }
                table{
                    border-collapse:collapse;
                }
                table th, table td{
                    border:1px solid #ddd;
                    text-align:left;
                    padding:5px;
                }
                table tr:hover {
                    /*background:#f8f8f8;*/
                }
                table tr:nth-child(even) {
                    background:#f8f8f8;
                }
            </style>';
            
            /*
            {"result":"SUCCESS","data":{"NoRequestKPI":"DMI-RUD_20210908114208","RequestSentDate":"2021-09-08 11:43:51.767","RequestSentBy":null,
            "RequestSentByEmail":null,"KajulEmailAddress":null,"KacabEmailAddress":null,"RequestSentCounter":1,"RequestStatus":"CLOSED",
            "ApproverCode":"BM1","ApproverName":"INDAH1","ApproverEmail":"INDAH@BHAKTI.CO.ID","IsApproved":0,"ApprovedDate":null,"ApprovedNote":null,
            "IsCancelled":0,"CancelledBy":null,"CancelledNote":null,"IsClosed":1,"ClosedBy":"JKTIT01","ClosedDate":"2021-09-08 11:48:01.817",
            "ClosedNote":"DMI-RUD_20210908114208", "KodeLokasi":"DMI",
            "ListTargetKPI":[
                {"KD_SLSMAN":"RUK-RLD","NM_SLSMAN":"ROLANDO","KODE_TARGET":"RUK-RLD2101","LEVEL_SLSMAN":"90","NAMA_LEVEL":"SALESMAN","USER_NAME":"INDAH",
                "WITHTARGETKPI":1,"APPROVALKPINEEDED":2,"KD_LOKASI":"DMI","NOREQUESTKPI":"DMI-RUD_20210908114208","STATUS":"CLOSED",
                "DETAILS":[
                    {"KodeTarget":"RUK-RLD2101","PeriodeTH":2021,"PeriodeBL":1,"KPICode":"KPI000001","KPIName":"Data Subdealer dan Market Survey","KPIUnit":"PCS",
                    "KPITarget":"1.00","KPIBobot":"100.00","CreatedBy":"JKTIT01","CreatedDate":"2021-09-07 11:36:52.440","ModifiedBy":null,"ModifiedDate":null,
                    "NoRequestKPI":"DMI-RUD_20210908114208"}
                ]}
            ]},"error":""}           
            */
            $BL = $req["Bulan"];
            $NM_BL = array("", "JANUARI", "FEBRUARI", "MARET", "APRIL", "MEI", "JUNI", "JULI", "AGUSTUS", "SEPTEMBER", "OKTOBER", "NOVEMBER", "DESEMBER");

            $header = "<h2>REQUEST APPROVAL TARGET KPI</h2><hr><br>";
            $header.= "No Request: <b>".$req["NoRequestKPI"]."</b><br>";
            $header.= "Tgl Request: <b>".date("d-M-Y H:i:s", strtotime($req["RequestSentDate"]))."</b><br>";
            $header.= "Dikirimkan Oleh: <b style='background:yellow;'>".$req["RequestSentBy"]."</b><br><hr><br>";
            $header.= "Cabang: <b style='background:yellow;'>".$req["Cabang"]."</b><br>";
            $header.= "Periode: <b style='background:yellow;'>".$NM_BL[$BL]." ".$req["Tahun"]."</b><br>";
            $header.= "Banyak Salesman: <b>".count($req["ListTargetKPI"])."</b><br><br>";

            $detail = "";
            $detailhd = "";
            $detaildt = "";
            $details = $req["ListTargetKPI"];
            // echo(json_encode($details)."<br>");

            $No = 0;

            $detailhd.= "<table>";
            $detailhd.= "<tr>";
            $detailhd.= "   <th width='5%''>No</th>";
            $detailhd.= "   <th width='15%'>Salesman</th>";
            $detailhd.= "   <th width='8%'>Periode</th>";
            $detailhd.= "   <th width='15%'>Key Performance Indicator</th>";
            $detailhd.= "   <th width='5%'>Deskripsi</th>";
            $detailhd.= "   <th width='5%'>Target</th>";
            $detailhd.= "   <th width='5%'>Bobot</th>";
            $detailhd.= "   <th width='5%'>TotalBobot</th>";
            $detailhd.= "   <th width='10%'></th>";
            $detailhd.= "   <th width='27%'>History/Status</th>";
            $detailhd.= "</tr>";

            /*"ListTargetKPI":[
                {"KD_SLSMAN":"RUK-RLD","NM_SLSMAN":"ROLANDO","KODE_TARGET":"RUK-RLD2101","LEVEL_SLSMAN":"90","NAMA_LEVEL":"SALESMAN","USER_NAME":"INDAH",
                "WITHTARGETKPI":1,"APPROVALKPINEEDED":2,"KD_LOKASI":"DMI","NOREQUESTKPI":"DMI-RUD_20210908114208","STATUS":"CLOSED",
                "DETAILS":[
                    {"KodeTarget":"RUK-RLD2101","PeriodeTH":2021,"PeriodeBL":1,"KPICode":"KPI000001","KPIName":"Data Subdealer dan Market Survey","KPIUnit":"PCS",
                    "KPITarget":"1.00","KPIBobot":"100.00","CreatedBy":"JKTIT01","CreatedDate":"2021-09-07 11:36:52.440","ModifiedBy":null,"ModifiedDate":null,
                    "NoRequestKPI":"DMI-RUD_20210908114208"}
                ]}
            ]*/

            $TotalWaiting = 0;
            $bg = "#ccf2ff";

            for($i=0; $i<count($details); $i++) {
                $No+= 1;
                $TotalBobot = 0;
                $dt = $details[$i];
                if ($bg=="#b3dae8") {
                    $bg = "#ccf2ff";
                } else {
                    $bg = "#b3dae8";
                }
                $detaildt = "";

                // echo("APPROVAL HISTORY<br>");
                // echo(json_encode($dt["APPROVALHISTORY"])."<br><br>");
                $ApprovalHistory = $dt["APPROVALHISTORY"];
                $l = count($ApprovalHistory);
                // echo("Jumlah History: ".$l."<br>");
                $HISTORY = "";
                if ($l>0) {
                    for($j=0; $j<$l; $j++) {
                        $HistoryStatus = $ApprovalHistory[$j]["HistoryStatus"];
                        $HistoryDate = date("d-M-Y H:i:s", strtotime($ApprovalHistory[$j]["HistoryDate"]));
                        $UserName = $ApprovalHistory[$j]["UserName"];
                        $HistoryNote = (($ApprovalHistory[$j]["HistoryNote"]==null)?"":$ApprovalHistory[$j]["HistoryNote"]);
                        $HISTORY.= $HistoryDate." - ".$UserName." - ".$HistoryStatus."[".$HistoryNote."]<br>"; 
                    }
                } else {
                    $HISTORY = "-";
                }
                // die($HISTORY);

                $KPIs = $dt["DETAILS"];     
                $k = count($KPIs);

                for($j=1; $j<$k;$j++) {
                    $detaildt.= "<tr style='background:".$bg.";'>";
                    $detaildt.= "   <td>".$KPIs[$j]["KPIName"]."</td>";
                    $detaildt.= "   <td>".$KPIs[$j]["KPINote"]."</td>";
                    $detaildt.= "   <td>".number_format($KPIs[$j]["KPITarget"],2)."</td>";
                    $detaildt.= "   <td>".number_format($KPIs[$j]["KPIBobot"],2)."</td>";
                    $detaildt.= "</tr>";
                    $TotalBobot += $KPIs[$j]["KPIBobot"];
                }

                $TotalBobot += $KPIs[0]["KPIBobot"];

                if($k==0) {$k=1;} //rowspan tidak boleh 0   
                
                $detailhd.="<tr style='background:".$bg.";'>";
                $detailhd.="    <td rowspan='".$k."'>".$No."</td>";
                $detailhd.="    <td rowspan='".$k."'>".$dt["NM_SLSMAN"]."<br><em> &nbsp; ".$dt["CATATAN"]."</em></td>";                
                $detailhd.="    <td rowspan='".$k."'>".$dt["BULAN"]."/".$dt["TAHUN"]."</td>";
                $detailhd.= "   <td>".$KPIs[0]["KPIName"]."</td>";
                $detailhd.= "   <td>".$KPIs[0]["KPINote"]."</td>";
                $detailhd.= "   <td>".number_format($KPIs[0]["KPITarget"],2)."</td>";
                $detailhd.= "   <td>".number_format($KPIs[0]["KPIBobot"],2)."</td>";
                $detailhd.="    <td rowspan='".$k."'>".number_format($TotalBobot, 0)."</td>";
                if ($dt["STATUS"]=="WAITING FOR APPROVAL") {
                    $TotalWaiting += 1;
                    $detailhd .= "<td rowspan='".$k."'><input type='checkbox' class='cek_pilih' name='salesman[]' value='".$dt["KODE_TARGET"]."' onchange='cek()' checked></td>";
                } else if ($dt["STATUS"]=="CANCELLED") {
                    $detailhd .= "<td rowspan='".$k."'>CANCELLED</td>";    
                } else if ($dt["STATUS"]=="CLOSED") {
                    $detailhd .= "<td rowspan='".$k."'>CLOSED</td>"; 
                } else if ($dt["STATUS"]=="REJECTED") {
                    $detailhd .= "<td rowspan='".$k."'>REJECTED</td>"; 
                } else if ($dt["STATUS"]=="APPROVED") {
                    $detailhd .= "<td rowspan='".$k."'>APPROVED</td>";
                } else {
                    $detailhd .= "<td rowspan='".$k."'></td>";
                }
                $detailhd.="    <td rowspan='".$k."'>".$HISTORY."</td>";
                $detailhd.="</tr>";         
                $detailhd.=$detaildt;       
                // die($detailhd);
            }
            $detailhd.="</table>";

            // $detail = $detailhd.$detaildt;
            
            $detail = "
            <form action='./ApproveReject' method='POST'>
            <div style='padding:10px;background:#FFF;border:1px solid #f5c6cb;'>".$detailhd."
            <input type='hidden' name='no_request' value='".$noRequest."'>
            <input type='hidden' name='app_by' value='".$approvedBy."'>
            <input type='hidden' name='req_json' value='".json_encode($req)."'>
            <br>";


            if ($TotalWaiting>0) {
                $detail.= "<span style='float:right'><em>(jika tidak pilih = REJECT)</em> Pilih Semua <input type='checkbox' id='cbx_all' onchange='cek_all()' checked></span>
                    <br>
                    REJECT NOTE (wajib diisi jika reject)<br>
                    <input type='input' name='rejectnote' id='rejectnote' style='width:100%;padding:5px;margin-bottom:10px' disabled>
                    <center><input type='submit' id='btn_submit' value='APPROVE' style='color:#fff; padding:10px; background:green; border:1px solid #555; text-align:center;' ></center>
                    </div>";
                
                $script= '
                <script type="text/javascript">
                function cek_all() {
                    var source = document.getElementById("cbx_all");
                    var inputElems = document.getElementsByClassName("cek_pilih");
                    count = 0;
                    for (var i=0; i<inputElems.length; i++) {
                        inputElems[i].checked = source.checked;
                        if (inputElems[i].checked == true){
                            count++;
                        }
                    }
                    if(count>0){
                        document.getElementById("btn_submit").value = "APPROVE";
                        document.getElementById("btn_submit").style.backgroundColor = "green";
                        document.getElementById("rejectnote").required = false;
                        document.getElementById("rejectnote").disabled = true;
                    }
                    else{
                        document.getElementById("btn_submit").value = "REJECT";
                        document.getElementById("btn_submit").style.backgroundColor = "red";
                        document.getElementById("rejectnote").required = true;
                        document.getElementById("rejectnote").disabled = false;
                    }
                }
                function cek(){
                    var inputElems = document.getElementsByClassName("cek_pilih");
                    count = 0;
                    for (var i=0; i<inputElems.length; i++) {
                        if (inputElems[i].checked == true){
                            count++;
                        }
                    }
                    if(count>0){
                        document.getElementById("btn_submit").value = "APPROVE";
                        document.getElementById("btn_submit").style.backgroundColor = "green";
                        document.getElementById("cbx_all").checked = true;
                        document.getElementById("rejectnote").required = false;
                        document.getElementById("rejectnote").disabled = true;
                    }
                    else{
                        document.getElementById("btn_submit").value = "REJECT";
                        document.getElementById("btn_submit").style.backgroundColor = "red";
                        document.getElementById("cbx_all").checked = false;
                        document.getElementById("rejectnote").required = true;
                        document.getElementById("rejectnote").disabled = false;
                    }
                }
                </script>
                ';
                echo $script;
            } else if ($msg!="") {
                echo $msg;
            } else {
                if($req["RequestStatus"]=='EXPIRED')
                    echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>".$req["ApprovalExpiredError"]."</h2></div>";
                else
                    echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>REQUEST SUDAH DIPROSES!</h2></div>";
            }
            $detail.= "</form>";

            echo ($style);
            echo ($header);
            echo ($detail);

        } //else {
        //     else echo "No. PrePO tidak ditemukan";
        // }
    }

    public function ApproveReject() {

        $msg = "";
        $data = $this->PopulatePost();
        // echo($data["app_by"]."<br>");
        // echo(json_encode($data)."<br><br>");


        //APPROVE
        if(ISSET($data['salesman'])){
            // die("ada Kode Target");
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => $this->API_URL."/TargetSalesman/ApproveTargetKPI",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->maxtimeout,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query($data),
            ));
            
            $result = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
                                    
            $result = json_decode($result, true);
            // echo("APPROVE: ".json_encode($result));
            // echo("<br>");

            if ($result["result"]=="SUCCESS") {     

                $msg = "
                <div style='padding:10px;background:#d4edda;border:1px solid #c3e6cb'>
                <center><h2>REQUEST TARGET KPI BERHASIL DIAPPROVE</h2></center>
                </div>";
                //reegan
                // foreach($data['salesman'] as $kodeTarget){
                    //reegan approve
                    $dApproval = array(
                        'ApprovalStatus' => 'APPROVED',
                        'ApprovedDate' => date('Y-m-d H:i:s'),
                    );
                    $wApproval = array(
                        // 'RequestNo' => $kodeTarget,
						'RequestNo' => $data['no_request'],
                        'ApprovalStatus' => 'UNPROCESSED',
                    );
                    $resultEdit = $this->TargetSalesmanModel->editTblApproval($wApproval,$dApproval);
                // }
            }
            else {
                $msg = "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><center><h2>".$result["error"]."</h2></center></div>";               
            }      
        }
        
        //REJECT
        else{
            // die($data["rejectnote"]);
            // die("Tidak ada Kode Target");
            // die('reject');
            // $URL = $this->API_URL."/TargetSalesman/RejectTargetKPI?req=".urlencode($noRequest)."&app_by=".urlencode($app_by);
            //$result = json_decode(file_get_contents($URL), true);
            $curl = curl_init();
            curl_setopt_array($curl, array(
            //CURLOPT_URL => $this->API_URL."/TargetSalesman/ApproveTargetKPI?req=".urlencode($noRequest)."&app_by=".urlencode($app_by),
            CURLOPT_URL => $this->API_URL."/TargetSalesman/RejectTargetKPI",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->maxtimeout,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query($data),
            ));
            
            $result = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
                                    
            $result = json_decode($result, true);
            // echo("REJECT: ".json_encode($result));
            // echo("<br>");
            // die("webAPI return :<br>".json_encode($result)."<br><br>");

            if ($result["result"]=="SUCCESS") {
                $msg = "<div style='padding:10px;background:#d4edda;border:1px solid #f5c6cb;'><h2><center>REQUEST TARGET KPI BERHASIL DIREJECT</center></h2></div>";
                //reegan
                // foreach($data['salesman'] as $kodeTarget){
                    //reegan rejected
                    $dApproval = array(
                        'ApprovalStatus' => 'REJECTED',
                    );
                    $wApproval = array(
                        // 'RequestNo' => $kodeTarget,
						'RequestNo' => $data['no_request'],
                        'ApprovalStatus' => 'UNPROCESSED',
                    );
                    $resultEdit = $this->TargetSalesmanModel->editTblApproval($wApproval,$dApproval);
                // }

            }
            else { 
                $msg = "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2><center>".$result["error"]."</center></h2></div>";
            }
        }
        // echo($msg."<br>");
        $this->ViewRequestTargetKPI($data["no_request"], $data["app_by"], $msg);
    }

    public function ProsesAchievementKPI()
    {
        $noRequest = urldecode($this->input->get("req"));
        $approvedBy = urldecode($this->input->get("app"));
        $totalWeek = urldecode($this->input->get("week"));
        $this->ViewRequestAchievementKPI($noRequest, $approvedBy, $totalWeek);
    }

    public function ViewRequestAchievementKPI($noRequest, $approvedBy, $totalWeek, $msg="") 
    {
        // echo("No Request :".$noRequest."<br>");
        $URL = $this->API_URL."/TargetSalesman/AmbilAchievementKPI?req=".urlencode($noRequest)."&app=".urlencode($approvedBy);
        // echo($URL);
        // echo("<br>");
        $GetRequest = json_decode(file_get_contents($URL), true);
        // echo(json_encode($GetRequest));die;
        // echo("<br><br>");

        if($GetRequest["result"]=="SUCCESS") {

            $req = $GetRequest["data"];

            $style = '<style>
                *{
                    font-family:Arial,sans-serif;
                    font-size:14px;
                }
                table{
                    border-collapse:collapse;
                }
                table th, table td{
                    border:1px solid #ddd;
                    padding:5px;
                }
                table th {
                    text-align:center;
                }
                table tr:hover {
                    /*background:#f8f8f8;*/
                }
                table tr:nth-child(even) {
                    background:#f8f8f8;
                }
                .target { background:#edf1f2; }
                .achievement { background:#ccf2ff; }
                .final { background:#b3dae8; }
                .modified { font-size:8pt;}
                .closed { background:#faacc9; }
            </style>';
            
            /*
            {"result":"SUCCESS","data":{"Cabang":"JAKARTA","Tahun":2021,"Bulan":9,"NoRequestKPI":"RUK-ATN_A20210922112109","RequestSentDate":"2021-09-22 11:33:33.350","RequestSentBy":"X",
                "RequestSentByEmail":"INDAHWATI.SALIMAN@GMAIL.COM","KajulEmailAddress":"INDAH@BHAKTI.CO.ID","KacabEmailAddress":"INDAH@BHAKTI.CO.ID",
                "RequestSentCounter":1,"RequestStatus":"WAITING FOR APPROVAL","ApproverCode":"KACAB","ApproverName":"Z","ApproverEmail":"INDAH@BHAKTI.CO.ID",
                "IsApproved":0,"ApprovedDate":null,"ApprovedNote":null,"IsCancelled":0,"CancelledBy":null,"CancelledNote":null,"IsClosed":0,
                "ClosedBy":null,"ClosedDate":null,"ClosedNote":null,"KodeLokasi":"DMI","ListKPI":[
                    {"KD_SLSMAN":"RUK-ABD","NM_SLSMAN":"ABDULLOH","KODE_TARGET":"RUK-ABD2109","LEVEL_SLSMAN":"90","NAMA_LEVEL":"SALESMAN","USER_NAME":"ANTON",
                    "APPROVALKPINEEDED":1,"REQUESTSTATUS":"WAITING FOR APPROVAL","KD_LOKASI":"DMI","NOREQUESTKPI":"RUK-ATN_A20210922112109","TAHUN":2021,"BULAN":9,
                    "STATUS":"WAITING FOR APPROVAL","DETAILS":[
                        {"EmpId":"RUK-ABD","EmpName":"ABDULLOH","EmpCategory":"SALESMAN","EmpLevelId":90,"EmpLevel":"SALESMAN","EmpUserId":0,"EmpEmail":"",
                        "Tahun":2021,"Bulan":9,"Training":0,"KodeLokasi":"DMI","KodeTarget":"RUK-ABD2109","TotalAchievement":"80.00",
                        "KPICode":"KPI000001","KPIName":"Data Subdealer dan Market Survey","KPIUnit":"PCS","KPITarget":"100.00","KPIBobot":"100.00",
                        "AcvWeek1":"8.00","AcvWeek2":"17.00","AcvWeek3":"18.00","AcvWeek4":"16.00","AcvWeek5":"10.00","AcvWeek6":".00","AcvTotal":"80.00",
                        "AcvPersen":"80.00","AcvBobot":"80.00","CreatedBy":"ANTON","CreatedDate":"2021-09-20 09:24:19.720",
                        "ModifiedBy":"ANTON","ModifiedDate":"2021-09-20 09:25:16.983","NoRequestKPI":"RUK-ATN_A20210922112109",
                        "RequestStatus":"WAITING FOR APPROVAL","RequestNote":null,"SuperiorId":"RUK-ATN","SuperiorName":"ANTON",
                        "SuperiorEmail":"ITDEV.DIST@BHAKTI.CO.ID","Week1ModifiedBy":"ANTON","Week1ModifiedDate":"2021-09-20 09:25:16.983",
                        "Week2ModifiedBy":"ANTON","Week2ModifiedDate":"2021-09-20 09:25:16.983","Week3ModifiedBy":"ANTON","Week3ModifiedDate":"2021-09-20 09:25:16.983",
                        "Week4ModifiedBy":"ANTON","Week4ModifiedDate":"2021-09-20 09:25:16.983","Week5ModifiedBy":null,"Week5ModifiedDate":null,
                        "Week6Modifiedby":null,"Week6ModifiedDate":null}
                    ],"APPROVALHISTORY":[]}
                ]},"error":""}
            */
            $BL = $req["Bulan"];
            $NM_BL = array("", "JANUARI", "FEBRUARI", "MARET", "APRIL", "MEI", "JUNI", "JULI", "AGUSTUS", "SEPTEMBER", "OKTOBER", "NOVEMBER", "DESEMBER");

            $header = "<h2>REQUEST APPROVAL ACHIEVEMENT KPI</h2><hr>";
            $header.= "No Request: <b>".$req["NoRequestKPI"]."</b><br>";
            $header.= "Tgl Request: <b>".date("d-M-Y H:i:s", strtotime($req["RequestSentDate"]))."</b><br>";
            $header.= "Dikirimkan Oleh: <b style='background:yellow;'>".$req["RequestSentBy"]."</b><br><hr><br>";
            $header.= "Cabang: <b style='background:yellow;'>".$req["Cabang"]."</b><br>";
            $header.= "Periode: <b style='background-color:yellow;'>".$NM_BL[$BL]." ".$req["Tahun"]."</b><br>";  
            $header.= "Banyak Salesman: <b>".count($req["ListKPI"])."</b><br><br>";

            $detail = "";
            $detailhd = "";
            $detaildt = "";
            $details = $req["ListKPI"];
            // echo(json_encode($details)."<br>");

            $No = 0;

            $detailhd.= "<table>";
            $detailhd.= "<tr>";
            $detailhd.= "   <th width='4%''>No</th>";
            $detailhd.= "   <th width='10%'>Salesman</th>";
            $detailhd.= "   <th width='8%'>Key Performance Indicator</th>";
            $detailhd.= "   <th width='4%'>Deskripsi</th>";
            $detailhd.= "   <th width='8%'>Target</th>";
            $detailhd.= "   <th width='4%'>%Bobot</th>";
            $detailhd.= "   <th width='8%'>Week1</th>";
            $detailhd.= "   <th width='8%'>Week2</th>";
            $detailhd.= "   <th width='8%'>Week3</th>";
            $detailhd.= "   <th width='8%'>Week4</th>";
            if ($totalWeek>=5) $detailhd.= "   <th width='8%'>Week5</th>";
            if ($totalWeek==6) $detailhd.= "   <th width='8%'>Week6</th>";
            $detailhd.= "   <th width='8%'>TotalAcv</th>";
            $detailhd.= "   <th width='4%'>%</th>";
            $detailhd.= "   <th width='4%'>%Bobot</th>";
            $detailhd.= "   <th width='4%'>%</th>";
            $detailhd.= "   <th width='2%'></th>";
            $detailhd.= "</tr>";

            /*"ListTargetKPI":[
                {"KD_SLSMAN":"RUK-RLD","NM_SLSMAN":"ROLANDO","KODE_TARGET":"RUK-RLD2101","LEVEL_SLSMAN":"90","NAMA_LEVEL":"SALESMAN","USER_NAME":"INDAH",
                "WITHTARGETKPI":1,"APPROVALKPINEEDED":2,"KD_LOKASI":"DMI","NOREQUESTKPI":"DMI-RUD_20210908114208","STATUS":"CLOSED",
                "DETAILS":[
                    {"KodeTarget":"RUK-RLD2101","PeriodeTH":2021,"PeriodeBL":1,"KPICode":"KPI000001","KPIName":"Data Subdealer dan Market Survey","KPIUnit":"PCS",
                    "KPITarget":"1.00","KPIBobot":"100.00","CreatedBy":"JKTIT01","CreatedDate":"2021-09-07 11:36:52.440","ModifiedBy":null,"ModifiedDate":null,
                    "NoRequestKPI":"DMI-RUD_20210908114208"}
                ]}
            ]*/

            $TotalWaiting = 0;

            for($i=0; $i<count($details); $i++) {
                $No+= 1;
                $TotalBobot = 0;
                $dt = $details[$i];
                $detaildt = "";

                // echo("APPROVAL HISTORY<br>");
                // echo(json_encode($dt["APPROVALHISTORY"])."<br><br>");
                $ApprovalHistory = $dt["APPROVALHISTORY"];
                $l = count($ApprovalHistory);
                // echo("Jumlah History: ".$l."<br>");
                $HISTORY = "";
                if ($l>0) {
                    for($j=0; $j<$l; $j++) {
                        $HistoryStatus = $ApprovalHistory[$j]["HistoryStatus"];
                        $HistoryDate = date("d-M-Y H:i:s", strtotime($ApprovalHistory[$j]["HistoryDate"]));
                        $UserName = $ApprovalHistory[$j]["UserName"];
                        $HistoryNote = (($ApprovalHistory[$j]["HistoryNote"]==null)?"":$ApprovalHistory[$j]["HistoryNote"]);
                        $HISTORY.= $HistoryDate." - ".$UserName." - ".$HistoryStatus."[".$HistoryNote."]<br>"; 
                    }
                } else {
                    $HISTORY = "-";
                }
                // die($HISTORY);

                $KPIs = $dt["DETAILS"];     
                $k = count($KPIs);
                // echo("Jumlah KPI : ".$k."<br>");
                for($j=1; $j<$k;$j++) {
                    $detaildt.= "<tr>";
                    $detaildt.= "   <td class='target'>".$KPIs[$j]["KPIName"]."</td>";
                    $detaildt.= "   <td class='target'>".$KPIs[$j]["KPINote"]."</td>";
                    $detaildt.= "   <td align='right' class='target'>".number_format($KPIs[$j]["KPITarget"],2)."</td>";
                    $detaildt.= "   <td align='right' class='target'>".number_format($KPIs[$j]["KPIBobot"],2)."</td>";
                    $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek1"],2)."</td>";
                    $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek2"],2)."</td>";
                    $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek3"],2)."</td>";
                    $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek4"],2)."</td>";
                    if ($totalWeek>=5) $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek5"],2)."</td>";
                    if ($totalWeek==6) $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek6"],2)."</td>";
                    $detaildt.= "   <td align='right' class='achievement'><b>".number_format($KPIs[$j]["AcvTotal"],2)."</b></td>";
                    $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvPersen"],2)."</td>";
                    $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvBobot"],2)."</td>";
                    $detaildt.= "</tr>";
                    // $TotalBobot += $KPIs[$j]["KPIBobot"];
                }

                // $TotalBobot += $KPIs[0]["KPIBobot"];

                if($k==0) {$k=1;} //rowspan tidak boleh 0
                
                $detailhd.="<tr>";
                $detailhd.="    <td rowspan='".$k."' class='target'>".$No."</td>";
                $detailhd.="    <td rowspan='".$k."' class='target'>".$dt["NM_SLSMAN"]."<br>&nbsp;<em>".$dt["CATATAN"]."</em></td>";    

                if ($dt["REQUESTSTATUS"]=="CANCELLED") {
                    if ($totalWeek>=5) {
                        $detailhd.= "   <td colspan='12' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CANCELLEDBY"]." ".$dt["CANCELLEDDATE"]." ".$dt["CANCELLEDNOTE"]."</td>";
                    } else if ($totalWeek==6) {
                        $detailhd.= "   <td colspan='13' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CANCELLEDBY"]." ".$dt["CANCELLEDDATE"]." ".$dt["CANCELLEDNOTE"]."</td>";
                    } else {                        
                        $detailhd.= "   <td colspan='11' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CANCELLEDBY"]." ".$dt["CANCELLEDDATE"]." ".$dt["CANCELLEDNOTE"]."</td>";
                    }
                    $detailhd.="    <td align='right' class='final' rowspan='".$k."'><b></b></td>";
                } else if ($dt["REQUESTSTATUS"]=="CLOSED") {
                    if ($totalWeek>=5) {
                        $detailhd.= "   <td colspan='12' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CLOSEDBY"]." ".$dt["CLOSEDDATE"]." ".$dt["CLOSEDNOTE"]."</td>";
                    } else if ($totalWeek==6) {
                        $detailhd.= "   <td colspan='13' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CLOSEDBY"]." ".$dt["CLOSEDDATE"]." ".$dt["CLOSEDNOTE"]."</td>";
                    } else {                        
                        $detailhd.= "   <td colspan='11' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CLOSEDBY"]." ".$dt["CLOSEDDATE"]." ".$dt["CLOSEDNOTE"]."</td>";
                    }
                    $detailhd.="    <td align='right' class='final' rowspan='".$k."'><b></b></td>";
                } else {    
                    $detailhd.= "   <td class='target'>".$KPIs[0]["KPIName"]."</td>";
                    $detailhd.= "   <td class='target'>".$KPIs[0]["KPINote"]."</td>";
                    $detailhd.= "   <td align='right' class='target'>".number_format($KPIs[0]["KPITarget"],2)."</td>";
                    $detailhd.= "   <td align='right' class='target'>".number_format($KPIs[0]["KPIBobot"],2)."</td>";
                    // $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek1"],2)."<br><br><span class='modified'>".$KPIs[0]["Week1ModifiedBy"]."<br>".$KPIs[0]["Week1ModifiedDate"]."</span></td>";
                    // $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek2"],2)."<br><br><span class='modified'>".$KPIs[0]["Week2ModifiedBy"]."<br>".$KPIs[0]["Week2ModifiedDate"]."</span></td>";
                    // $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek3"],2)."<br><br><span class='modified'>".$KPIs[0]["Week3ModifiedBy"]."<br>".$KPIs[0]["Week3ModifiedDate"]."</span></td>";
                    // $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek4"],2)."<br><br><span class='modified'>".$KPIs[0]["Week4ModifiedBy"]."<br>".$KPIs[0]["Week4ModifiedDate"]."</span></td>";
                    // if ($totalWeek>=5) $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek5"],2)."<br><br><span class='modified'>".$KPIs[0]["Week5ModifiedBy"]."<br>".$KPIs[0]["Week5ModifiedDate"]."</span></td>";
                    // if ($totalWeek==6) $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek6"],2)."<br><br><span class='modified'>".$KPIs[0]["Week6ModifiedBy"]."<br>".$KPIs[0]["Week6ModifiedDate"]."</span></td>";
                    $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek1"],2)."</td>";
                    $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek2"],2)."</td>";
                    $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek3"],2)."</td>";
                    $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek4"],2)."</td>";
                    if ($totalWeek>=5) $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek5"],2)."</td>";
                    if ($totalWeek==6) $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek6"],2)."</td>";
                    $detailhd.= "   <td align='right' class='achievement'><b>".number_format($KPIs[0]["AcvTotal"],2)."</b></td>";
                    $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvPersen"],2)."</td>";
                    $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvBobot"],2)."</td>";
                    $detailhd.="    <td align='right' class='final' rowspan='".$k."'><b>".number_format($dt["TOTAL_ACHIEVEMENT"], 0)."</b></td>";
                }
                //reegan edit sementara, buat aktifin tombol approve dan reject | akses : ApproveRejectAchievement
                //$dt["STATUS"] = "WAITING FOR APPROVAL";
                if ($dt["STATUS"]=="WAITING FOR APPROVAL") {
                    $TotalWaiting += 1;
                    $detailhd .= "<td rowspan='".$k."'><input type='checkbox' class='cek_pilih' name='salesman[]' value='".$dt["KODE_TARGET"]."' onchange='cek()' checked></td>";
                } else if ($dt["STATUS"]=="CANCELLED") {
                    $detailhd .= "<td rowspan='".$k."'>CANCELLED</td>";    
                } else if ($dt["STATUS"]=="CLOSED") {
                    $detailhd .= "<td rowspan='".$k."'>CLOSED</td>"; 
                } else if ($dt["STATUS"]=="REJECTED") {
                    $detailhd .= "<td rowspan='".$k."'>REJECTED</td>"; 
                } else if ($dt["STATUS"]=="APPROVED") {
                    $detailhd .= "<td rowspan='".$k."'>APPROVED</td>";
                } else {
                    $detailhd .= "<td rowspan='".$k."'></td>";
                }
                // $detailhd.="    <td rowspan='".$k."'>".$HISTORY."</td>";
                $detailhd.="</tr>";         
                $detailhd.=$detaildt;       
                // die($detailhd);
            }
            $detailhd.="</table>";

            // $detail = $detailhd.$detaildt;
            
            $detail = "
            <form action='./ApproveRejectAchievement' method='POST'>
            <div style='padding:10px;background:#FFF;border:1px solid #f5c6cb;'>".$detailhd."
            <input type='hidden' name='no_request' value='".$noRequest."'>
            <input type='hidden' name='app_by' value='".$approvedBy."'>
            <input type='hidden' name='total_week' value='".$totalWeek."'>
            <input type='hidden' name='req_json' value='".json_encode($req)."'>
            <br>";


            if ($TotalWaiting>0) {
                $detail.= "<span style='float:right'><em>(jika tidak pilih = REJECT)</em> Pilih Semua <input type='checkbox' id='cbx_all' onchange='cek_all()' checked></span>
                    <br>
                    REJECT NOTE (wajib diisi jika reject)<br>
                    <input type='input' name='rejectnote' id='rejectnote' style='width:100%;padding:5px;margin-bottom:10px' disabled>
                    <center><input type='submit' id='btn_submit' value='APPROVE' style='color:#fff; padding:10px; background:green; border:1px solid #555; text-align:center;' ></center>
                    </div>";
                
                $script= '
                <script type="text/javascript">
                function cek_all() {
                    var source = document.getElementById("cbx_all");
                    var inputElems = document.getElementsByClassName("cek_pilih");
                    count = 0;
                    for (var i=0; i<inputElems.length; i++) {
                        inputElems[i].checked = source.checked;
                        if (inputElems[i].checked == true){
                            count++;
                        }
                    }
                    if(count>0){
                        document.getElementById("btn_submit").value = "APPROVE";
                        document.getElementById("btn_submit").style.backgroundColor = "green";
                        document.getElementById("rejectnote").required = false;
                        document.getElementById("rejectnote").disabled = true;
                    }
                    else{
                        document.getElementById("btn_submit").value = "REJECT";
                        document.getElementById("btn_submit").style.backgroundColor = "red";
                        document.getElementById("rejectnote").required = true;
                        document.getElementById("rejectnote").disabled = false;
                    }
                }
                function cek(){
                    var inputElems = document.getElementsByClassName("cek_pilih");
                    count = 0;
                    for (var i=0; i<inputElems.length; i++) {
                        if (inputElems[i].checked == true){
                            count++;
                        }
                    }
                    if(count>0){
                        document.getElementById("btn_submit").value = "APPROVE";
                        document.getElementById("btn_submit").style.backgroundColor = "green";
                        document.getElementById("cbx_all").checked = true;
                        document.getElementById("rejectnote").required = false;
                        document.getElementById("rejectnote").disabled = true;
                    }
                    else{
                        document.getElementById("btn_submit").value = "REJECT";
                        document.getElementById("btn_submit").style.backgroundColor = "red";
                        document.getElementById("cbx_all").checked = false;
                        document.getElementById("rejectnote").required = true;
                        document.getElementById("rejectnote").disabled = false;
                    }
                }
                </script>
                ';
                echo $script;
            } else if ($msg!="") {
                echo $msg;
            } else {
                if($req["RequestStatus"]=='EXPIRED')
                    echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>".$req["ApprovalExpiredError"]."</h2></div>";
                else
                    echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>REQUEST SUDAH DIPROSES!</h2></div>";
            }
            $detail.= "</form>";

            echo ($style);
            echo ($header);
            echo ($detail);

        } //else {
        //     else echo "No. PrePO tidak ditemukan";
        // }
    }

    public function ApproveRejectAchievement() 
    {

        $msg = "";
        $data = $this->PopulatePost();
        // echo(json_encode($data)."<br><br>");


        //APPROVE
        if(ISSET($data['salesman'])){
            // die("ada Kode Target : <br>".json_encode($data));
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => $this->API_URL."/TargetSalesman/ApproveAchievementKPI",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->maxtimeout,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query($data),
            ));
            
            $result = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
                                    
            $result = json_decode($result, true);
            // echo("APPROVE: ".json_encode($result));
            // echo("<br>");

            if ($result["result"]=="SUCCESS") {     

                $msg = "
                <div style='padding:10px;background:#d4edda;border:1px solid #c3e6cb'>
                <center><h2>REQUEST ACHIEVEMENT KPI BERHASIL DIAPPROVE</h2></center>
                </div>";
                // foreach($data['salesman'] as $kodeTarget){
                    //reegan approve
                    $dApproval = array(
                        'ApprovalStatus' => 'APPROVED',
                        'ApprovedDate' => date('Y-m-d H:i:s'),
                    );
                    $wApproval = array(
                        // 'RequestNo' => $kodeTarget,
						'RequestNo' => $data['no_request'],
                        'ApprovalStatus' => 'UNPROCESSED',
                    );
                    $resultEdit = $this->TargetSalesmanModel->editTblApproval($wApproval,$dApproval);
                // }
                


            }
            else {
                $msg = "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><center><h2>".$result["error"]."</h2></center></div>";               
            }      
        }
        
        //REJECT
        else{
            // die($data["rejectnote"]);
            // die("Tidak ada Kode Target");
            // die('reject');

            $url = $this->API_URL."/TargetSalesman/RejectAchievementKPI";

            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->maxtimeout,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query($data),
            ));
            
            $result = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
                                    
            $result = json_decode($result, true);
            // echo("data: ".json_encode($data)."<br><br>");
            // echo("url webAPI: ".$url."<br><br>");
            // echo("REJECT: ".json_encode($result));
            // echo("<br>");
            // echo("webAPI return :<br>".json_encode($result)."<br><br>");

            if ($result["result"]=="SUCCESS") {
                $msg = "<div style='padding:10px;background:#d4edda;border:1px solid #f5c6cb;'><h2><center>REQUEST ACHIEVEMENT KPI BERHASIL DIREJECT</center></h2></div>";
                //reegan reject
                // foreach($data['salesman'] as $kodeTarget){
                    //reegan approve
                    $dApproval = array(
                        'ApprovalStatus' => 'REJECTED',
                    );
                    $wApproval = array(
                        // 'RequestNo' => $kodeTarget,
						'RequestNo' => $data['no_request'],
                        'ApprovalStatus' => 'UNPROCESSED',
                    );
                    $resultEdit = $this->TargetSalesmanModel->editTblApproval($wApproval,$dApproval);
                // }
            }
            else { 
                $msg = "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2><center>".$result["error"]."</center></h2></div>";
            }
        }
        // echo($msg."<br>");
        $this->ViewRequestAchievementKPI($data["no_request"], $data["app_by"], $data["total_week"], $msg);
    }

    public function simpanapproval($parammode, $params, $conn){
        //get list target salesman

        if ($parammode==''){
            $url = $conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApprovalNew/GetListTarget?parammode=".urlencode($parammode)."&mode=".urlencode($params["mode"])."&kategori=".urlencode($params["kategori"])."&userid=".urlencode($params["userid"])."&tanggal=".urlencode($params["tanggal"])."&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD);
        } else {
            $url = $conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApprovalNew/GetListTarget?parammode=".urlencode($parammode)."&norequest=".urlencode($params["norequest"])."&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD);
        }

        $result = json_decode(file_get_contents($url),true);

        for ($a=0;$a<count($result);$a++) {
            $params = array();
            $params["ApprovalType"] = $this->approvaltype.' '.$params["kategori"];
            $params["RequestNo"] = $result[$a]["NoRequest"];
            $params["RequestBy"] = $result[$a]["User_Email"];
            $params["RequestDate"] = $result[$a]["EmailedDate"];
            $params["RequestByName"] = $result[$a]["User_Name"];
            $params["RequestByEmail"] = $result[$a]["User_Email"];
            $params["ApprovedBy"] = $result[$a]["UserApproved_ID"];
            $params["ApprovedByName"] = $result[$a]["UserApproved_Name"];
            $params["ApprovedByEmail"] = $result[$a]["UserApproved_Email"];
            $params["ApprovedDate"] = NULL;
            $params["ApprovalStatus"] = "UNPROCESSED";
            $params["ApprovalNote"] = NULL;
            $params["AddInfo1"] = "Kode SPG";
            $params["AddInfo1Value"] = "";
            $params["AddInfo2"] = "Nama SPG";
            $params["AddInfo2Value"] = "";
            $params["AddInfo3"] = "";
            $params["AddInfo3Value"] = "";
            $params["AddInfo4"] = "";
            $params["AddInfo4Value"] = "";
            $params["AddInfo5"] = "";
            $params["AddInfo5Value"] = "";
            $params["AddInfo6"] = "Periode";
            $params["AddInfo6Value"] = $result[$a]["monthyear"];
            $params["AddInfo7"] = "";
            $params["AddInfo7Value"] = "";
            $params["AddInfo8"] = "";
            $params["AddInfo8Value"] = "";
            $params["AddInfo9"] = "Wilayah";
            $params["AddInfo9Value"] = $result[$a]["Wilayah"];
            $params["AddInfo10"] = "";
            $params["AddInfo10Value"] = "";
            $params["AddInfo11"] = "";
            $params["AddInfo11Value"] = "";
            $params["AddInfo12"] = "";
            $params["AddInfo12Value"] = "";
            $params["ApprovalNeeded"] = "";
            $params["Priority"] = "";
            $params["ExpiryDate"] = $result[$a]["Tgl_Akhir"];
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
            $params["EmailedDate"] = $result[$a]["EmailedDate"];
            $params["approvedbyfrommsconfig"] = $this->approvedbyfrommsconfig;
            $params["expirydatefrommsconfig"] = $this->expirydatefrommsconfig;
            $params["amount"] = 0;
            $params["branchid"] = $result[$a]["kd_lokasi"];
            $x = $this->approval->insert($params);
        }

    }

    public function viewTarget(){
        //http://localhost:90/myCompany/TargetSalesmanApproval/viewTarget?norequest=SPSYAT200320200504104406
        $params = array();
        $params["norequest"] = urldecode($this->input->get("norequest"));
        $params["wilayah"] = "JAKARTA";     
               
        //cari AlamatWEBService DB BHAKTI cabang yang diinginkan
        $conn = $this->TargetSalesmanModel->get($params["wilayah"]);
        //die(json_encode($conn));

        if ($conn!=null) {
            $connected = false;

            set_time_limit(3);

            try {
                set_time_limit($this->maxtimeout);        
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

            // die("here");

            $this->ViewDataTargetSPG(urlencode($params["norequest"]), urlencode($conn->Server), urlencode($conn->Database), urlencode(SQL_UID), urlencode(SQL_PWD));


        } else {
            echo "Alamat WEB Service Belum Disetting";
        } 
    }


    public function Email_Notifikasi_ByNoRequestNew()
    {
        // http://localhost:90/myCompany/TargetSalesmanApproval/Email_Notifikasi_ByNoRequest?norequest=MDN091230220230202135651
        $params = array();
        $params["norequest"] = urldecode($this->input->get("norequest"));
        $params["wilayah"] = "JAKARTA";     
               
        //cari AlamatWEBService DB BHAKTI cabang yang diinginkan
        $conn = $this->TargetSalesmanModel->get($params["wilayah"]);
        // die(json_encode($conn));

        if ($conn!=null) {
            $connected = false;

            set_time_limit(3);

            try {
                set_time_limit($this->maxtimeout);        
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

            $url = $conn->AlamatWebService.$this->API_BKT."/TargetSalesmanApprovalNew/AmbilDataDiPusatByNoRequest?norequest=".urlencode($params["norequest"])."&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD);
            // die($url);
            

            if ($connected) {
                set_time_limit(60);
                $result = json_decode(file_get_contents($url),true);
                
                $this->simpanapproval("NOREQUEST", $params, $conn);

                // $x= array();
                // $x["pesan"]=$result["pesan"];
                // echo json_encode($x);
                echo ($result["pesan"]);

            } else {
                //echo "Cek Function TestConnection";
                echo "Server yang dituju sedang tidak aktif";
            }
        } else {
            echo "Alamat WEB Service Belum Disetting";
        }        
    }

    //Substitute bktAPI From Here
    public function ProsesNew_Substitute_bktAPI($norequest, $svr, $db, $uid, $pwd, $kodetarget, $user, $action)
    {
        $server_source = urldecode($svr);
        $database_name  = urldecode($db);
        $sql_uid = urldecode($uid);     
        $sql_pwd = urldecode($pwd);     
        include_once APPPATH."/includes/Connections.php";   
        
        $params = array();
        $params["norequest"] = urldecode($norequest);
        $params["kode_target"] = urldecode($kodetarget);
        $params["user"] = urldecode($user);     
        $params["action"] = urldecode($action);
        $params["emailmode"] = "VIEW";          

        $data = array();            
                                
        $CheckTargetAvailable = $this->TargetSalesmanModel->CheckTargetAvailableNew($params, $config);
        if($CheckTargetAvailable==false){           
            $data = array("result"=> "0",
                          "pesan" => "Request Tidak Ada Di Sistem");        
        }
        else
        {
            $CheckTargetYangAkanApprove = $this->TargetSalesmanModel->CheckTargetYangAkanApproveNew($params, $config);
            if($CheckTargetYangAkanApprove->IsApproved==1){
                //Status IsApproved = 1
                if ($CheckTargetYangAkanApprove->ApprovedBy == 'AUTO APPROVAL'){
                    $data = array("result"=> "2",
                                    "pesan" => "Request Sudah Di-AUTO APPROVAL");                                                        
                } else {
                    $data = array("result"=> "4", 
                                    "pesan" => "Request Sudah Pernah Diapprove Sebelumnya Oleh ".$CheckTargetYangAkanApprove->ApprovedBy." Pada ".$CheckTargetYangAkanApprove->ApprovedDate);                           
                }                   
            }
            else if($CheckTargetYangAkanApprove->IsApproved==0)
            {   //Status IsApproved = 0         
                if($CheckTargetYangAkanApprove->IsRejected==1) {
                    $data = array("result"=> "3",
                                    "pesan" => "Request Sudah Pernah Direject Sebelumnya"); 
                }
                elseif($CheckTargetYangAkanApprove->IsDeleted==1) {
                    $data = array("result"=> "1",
                                    "pesan" => "Setelah Diajukan, Request Dihapus dari Sistem");
                } 
                else //if ($CheckTargetYangAkanApprove->IsRejected==0) && ($CheckTargetYangAkanApprove->IsDeleted==0) 
                {
                    //$this->Notif_Email_KeUserInput($params, $config);   
                    //die;
                    if ($sukses=$this->TargetSalesmanModel->MoveTargetSalesmanLogToTargetSalesmanNew($params, $config))
                    {               
                        if($sukses == true){    
                            if ($params["action"]=='APPROVE'){
                                                            
                                //$data = array();    
                                $KodeLokasi=$this->TargetSalesmanModel->GetTblConfig($config);
                                $this->Notif_Email_KeUserInput_EmailCaraBaru($params, $config);
                                $data = array("result"=> "5", "pesan" => "Request Berhasil Diappove");    

                            } else {
                                $this->Notif_Email_KeUserInput_EmailCaraBaru($params, $config);
                                $data = array("result"=> "6", "pesan" => "Request Berhasil Direject");
                            }
                        } else { 
                            $data = array("result"=> "7",
                                        "pesan" => "Something Wrong, Data Tidak Bisa Diproses");                                
                        }   
                    }   
                }           
            }
        }
        echo json_encode($data);                    
    }   

    public function ViewDataTargetSPG($norequest, $svr, $db, $uid, $pwd) {
        // die("x");
        $server_source = urldecode($svr);
        $database_name  = urldecode($db);
        $sql_uid = urldecode($uid);     
        $sql_pwd = urldecode($pwd);     
        include_once APPPATH."/includes/Connections.php";
        // die(json_encode($config));

        $params = array();
        $params["norequest"] = urldecode($norequest);
        $params["parammode"] = "NOREQUEST";

        if ($server_source == $this->db->hostname)
        {
            $params["lokasiAPIdanDB"] = 'SAMA';
        } else {
            $params["lokasiAPIdanDB"] = 'BEDA';
        }

        $x = $this->TargetSalesmanModel->get_list_bynorequest2($params, $config);


        // die(json_encode($x));
        $params["mode"]=$x[0]["mode"];
        $params["kategori"]=$x[0]["kategori"];
        $params["userid"]=$x[0]["userid"];
        $params["tanggal"]=$x[0]["tanggal"];
        $params["monthyear"]=$x[0]["monthyear"];
        $params["month"]=$x[0]["month"];
        $params["year"]=$x[0]["year"];
        $params["emailmode"] = "VIEW";
        $params["tanggalserver"] = date("d");
        $params["bulanserver"] = date("m"); 
        $params["tahunserver"] = date("Y"); 
        // die(json_encode($params));
        $data = array();
        $data = $this->CreateRequestContent($params, $config);
        $data["isForm"] = false;
        $data["formURL"] = "";
        $data["RequestNo"] = "";
        $data["RequestType"] = "";
        // die(json_encode($data));

        $this->RenderView("requestview", $data);
        // echo json_encode($result);
    }   

    public function CreateRequestContent($params, $config) {

        $bktAPI = $this->TargetSalesmanModel->GetLokasibktAPI($config);
        $MonthYear=$this->TargetSalesmanModel-> GetMonthYear_ByEntryTime2($params, $config);    
        // die(json_encode($MonthYear));
        $KotaPusat = "JAKARTA";

        $DetailUser=$this->TargetSalesmanModel->GetLevelUserInput($params, $config);
        // die(json_encode($DetailUser));
        $NamaUser=$DetailUser->NamaUser;
        $LevelUser=$DetailUser->LevelUser;
        $Level_Salesman=$DetailUser->Level_Salesman;
        $Wilayah_Salesman=$DetailUser->Wilayah;

        $x = $this->SalesManagerModel->GetGeneralManager(); 
        // die(json_encode($x));
        $Kode_GM='';
        $Nama_GM='';
        $Email_GM='';
        $Level_GM='';

        if(count($x)>0){
            $Kode_GM=$x[0]->kd_slsman;
            $Nama_GM=$x[0]->employee_name;
            $Email_GM=$x[0]->email_address;     
            $Level_GM=$x[0]->level_slsman;
        }else{
            echo '<h2>Data "<b>GENERAL MANAGER</b>" tidak ditemukan</h2>';
            die();
        }
        // die(json_encode($x));
        // die($Email_GM);
        //echo json_encode($config);die;
    
        //Jika Tanggal Server MAX. 12, Input, Re-input dan Edit Target Bulan Berjalan Dan Sebelumnya Harus Approval Ke GM
        if ($params["kategori"]=='SALESMAN'){
            $TargetSalesman_LastModified = $this->TargetSalesmanModel->Get_TargetSalesman_LastModified($config);
        } else if ($params["kategori"]=='SPG'){
            $TargetSalesman_LastModified = $this->TargetSalesmanModel->Get_TargetSPG_LastModified($config);
        }

        // die(json_encode($MonthYear));
        // die(json_encode($TargetSalesman_LastModified));

        if (
            (//Kondisi Pertama, Target Mei 2020 Diinput Juni 2020 (Seharusnya Max 12 Mei 2020)
            ($params["bulanserver"]>$MonthYear->Month) && ($params["tahunserver"]==$MonthYear->Year))
            ||
            (//Kondisi Kedua, Target Tahun yang sudah Lewat
            ($params["tahunserver"]>$MonthYear->Year))
            ||
            (//Kondisi Ketiga, Target Mei 2020 Diinput 13 Mei 2020 (Seharusnya Max 12 Mei 2020)
            ($params["bulanserver"]==$MonthYear->Month) && ($params["tahunserver"]==$MonthYear->Year) && ($params["tanggalserver"]>$TargetSalesman_LastModified->Tanggal))
            )
        {
            // die($params["kategori"]);
            if ($Wilayah_Salesman=='JAKARTA') { 
                if ($params["kategori"]=='SALESMAN'){
                    if ($Level_Salesman==1){ 
                        $MaxDate=1;
                        $Kode_Atasan=$Kode_GM;
                        $Nama_Atasan=$Nama_GM;
                        $Email_Atasan=$Email_GM;        
                        $Level_Atasan=$Level_GM;
                        $GM=1;                      
                        //Jika GM yang modif maka langsung auto approval dan email pemberitahuan ke dirinya sendiri
                        $Need_Approval=0;
                    } else if ($Level_Salesman==70){ 
                        $x=$this->TargetSalesmanModel->GetAtasanSales_ByEntryTime2($params, $config);
                        $Kode_Manager=$x[0]["Kode_Atasan"];
                        $Nama_Manager=$x[0]["Nama_Atasan"];
                        $Email_Manager=$x[0]["Email_Atasan"];
                        $Level_Manager=$x[0]["Level_Atasan"];

                        $MaxDate=1;                     
                        $Kode_Atasan=$Kode_Manager;
                        $Nama_Atasan=$Nama_Manager;
                        $Email_Atasan=$Email_Manager;
                        $Level_Atasan=$Level_Manager;               
                        $GM=0;              
                        $Need_Approval=1;                               
                    } else {
                        //Per 22 Jan 2021, Approval Dirubah Ke Atasan Masing Masing

                        // $MaxDate=1;
                        // $Kode_Atasan=$Kode_GM;
                        // $Nama_Atasan=$Nama_GM;
                        // $Email_Atasan=$Email_GM;     
                        // $Level_Atasan=$Level_GM;
                        // $GM=1;                       
                        // $Need_Approval=1;

                        $x=$this->TargetSalesmanModel->GetAtasanSales_ByEntryTime2($params, $config);
                        $Kode_Manager=$x[0]["Kode_Atasan"];
                        $Nama_Manager=$x[0]["Nama_Atasan"];
                        $Email_Manager=$x[0]["Email_Atasan"];
                        $Level_Manager=$x[0]["Level_Atasan"];
            
                        if ($params["userid"]==$Kode_Manager){ 
                            //Jika Manager yang modif maka langsung auto approval dan email pemberitahuan ke dirinya sendiri
                            $MaxDate=0;             
                            $Kode_Atasan=$Kode_Manager;
                            $Nama_Atasan=$Nama_Manager;
                            $Email_Atasan=$Email_Manager;
                            $Level_Atasan=$Level_Manager;               
                            $Need_Approval=0; //Auto_Approval
                            $GM=0;
                        } else {
                            //Jika Admin yg modif maka email ke manager 
                            //untuk Fandy Bogor, Kode_Atasan = 'GM' walaupun diedit dirinya sendiri
                            if($Kode_Manager=='GM'){
                                $MaxDate=0;                 
                                $Kode_Atasan=$Kode_GM;
                                $Nama_Atasan=$Nama_GM;
                                $Email_Atasan=$Email_GM;        
                                $Level_Atasan=$Level_GM;
                                $Need_Approval=1;
                                $GM=1; //Aproval By GM
                            } else {
                                $MaxDate=0;                     
                                $Kode_Atasan=$Kode_Manager;
                                $Nama_Atasan=$Nama_Manager;
                                $Email_Atasan=$Email_Manager;
                                $Level_Atasan=$Level_Manager;               
                                $Need_Approval=1;
                                $GM=0; //Aproval By Manager         
                            }
                        }   

                    }
                } else { //SPG JAKARTA
                    $x=$this->TargetSalesmanModel->GetAtasanSales_ByEntryTime2($params, $config);
                    $Kode_Manager=$x[0]["Kode_Atasan"];
                    $Nama_Manager=$x[0]["Nama_Atasan"];
                    $Email_Manager=$x[0]["Email_Atasan"];
                    $Level_Manager=$x[0]["Level_Atasan"];
        
                    if ($params["userid"]==$Kode_Manager){ 
                        //Jika Manager yang modif maka langsung auto approval dan email pemberitahuan ke dirinya sendiri
                        $MaxDate=0;             
                        $Kode_Atasan=$Kode_Manager;
                        $Nama_Atasan=$Nama_Manager;
                        $Email_Atasan=$Email_Manager;
                        $Level_Atasan=$Level_Manager;               
                        $Need_Approval=0; //Auto_Approval
                        $GM=0;
                    } else {
                        //Jika Admin yg modif maka email ke manager 
                        //untuk Fandy Bogor, Kode_Atasan = 'GM' walaupun diedit dirinya sendiri
                        if($Kode_Manager=='GM'){
                            $MaxDate=0;                 
                            $Kode_Atasan=$Kode_GM;
                            $Nama_Atasan=$Nama_GM;
                            $Email_Atasan=$Email_GM;        
                            $Level_Atasan=$Level_GM;
                            $Need_Approval=1;
                            $GM=1; //Aproval By GM
                        } else {
                            $MaxDate=0;                     
                            $Kode_Atasan=$Kode_Manager;
                            $Nama_Atasan=$Nama_Manager;
                            $Email_Atasan=$Email_Manager;
                            $Level_Atasan=$Level_Manager;               
                            $Need_Approval=1;
                            $GM=0; //Aproval By Manager         
                        }
                    }           
                }
            } else { //SELAIN JKT, SALESMAN+SPG
                $x=$this->TargetSalesmanModel->GetAtasanSales_ByEntryTime2($params, $config);
                // die(json_encode($x));
                $Kode_Manager=$x[0]["Kode_Atasan"];
                $Nama_Manager=$x[0]["Nama_Atasan"];
                $Email_Manager=$x[0]["Email_Atasan"];
                $Level_Manager=$x[0]["Level_Atasan"];
    
                if ($params["userid"]==$Kode_Manager){ 
                    //Jika Manager yang modif maka langsung auto approval dan email pemberitahuan ke dirinya sendiri
                    $MaxDate=0;             
                    $Kode_Atasan=$Kode_Manager;
                    $Nama_Atasan=$Nama_Manager;
                    $Email_Atasan=$Email_Manager;
                    $Level_Atasan=$Level_Manager;               
                    $Need_Approval=0; //Auto_Approval
                    $GM=0;
                } else {
                    //Jika Admin yg modif maka email ke manager 
                    //untuk Fandy Bogor, Kode_Atasan = 'GM' walaupun diedit dirinya sendiri
                    if($Kode_Manager=='GM'){
                        $MaxDate=0;                 
                        $Kode_Atasan=$Kode_GM;
                        $Nama_Atasan=$Nama_GM;
                        $Email_Atasan=$Email_GM;        
                        $Level_Atasan=$Level_GM;
                        $Need_Approval=1;
                        $GM=1; //Aproval By GM
                    } else {
                        $MaxDate=0;                     
                        $Kode_Atasan=$Kode_Manager;
                        $Nama_Atasan=$Nama_Manager;
                        $Email_Atasan=$Email_Manager;
                        $Level_Atasan=$Level_Manager;               
                        $Need_Approval=1;
                        $GM=0; //Aproval By Manager         
                    }
                }               
            }   
        } else {
            // die("here");
            //Jika Dimodif Sebelum Tgl 12
            //Email Atasan Sales
            $x=$this->TargetSalesmanModel->GetAtasanSales_ByEntryTime2($params, $config);
            $Kode_Manager=$x[0]["Kode_Atasan"];
            $Nama_Manager=$x[0]["Nama_Atasan"];
            $Email_Manager=$x[0]["Email_Atasan"];
            $Level_Manager=$x[0]["Level_Atasan"];

            if ($params["userid"]==$Kode_Manager){ 
                //Jika Manager yang modif maka langsung auto approval dan email pemberitahuan ke dirinya sendiri
                $MaxDate=0;             
                $Kode_Atasan=$Kode_Manager;
                $Nama_Atasan=$Nama_Manager;
                $Email_Atasan=$Email_Manager;
                $Level_Atasan=$Level_Manager;               
                $Need_Approval=0; //Auto_Approval
                $GM=0;
            } else {
                //Jika Admin yg modif maka email ke manager 
                //untuk Fandy Bogor, Kode_Atasan = 'GM' walaupun diedit dirinya sendiri
                if($Kode_Manager=='GM'){
                    $MaxDate=0;                 
                    $Kode_Atasan=$Kode_GM;
                    $Nama_Atasan=$Nama_GM;
                    $Email_Atasan=$Email_GM;        
                    $Level_Atasan=$Level_GM;
                    $Need_Approval=1;
                    $GM=1; //Aproval By GM
                } else {
                    $MaxDate=0;                     
                    $Kode_Atasan=$Kode_Manager;
                    $Nama_Atasan=$Nama_Manager;
                    $Email_Atasan=$Email_Manager;
                    $Level_Atasan=$Level_Manager;               
                    $Need_Approval=1;
                    $GM=0; //Aproval By Manager         
                }
            }               
        }           

        $email_content = "";   
        $button_approve = "";
        $button_reject = "";

        // die("here");
        if ($params["parammode"]=='NOREQUEST'){
            // die("1");
            $HD=$this->TargetSalesmanModel->GetTargetHDLog_ByNoRequest2($params, $config);
        } else if ($params["parammode"]=='EMAIL'){
            // die("2");
            $HD=$this->TargetSalesmanModel->GetTargetHDLog_ByEntryTimeResendEmail($params, $config);
        }else {
            // die("3");
            $HD=$this->TargetSalesmanModel->GetTargetHDLog_ByEntryTime2($params, $config);
        }

        if (substr($HD,0,5)=="ERROR") {
            $rq = array();
            $rq["ApprovalType"] = "TARGET SPG";
            $rq["RequestNo"] = $params["norequest"];
            $rq["CancelledBy"] = "N/A";
            $rq["CancelledByName"] = "N/A";
            $rq["CancelledNote"] = "Request Tidak Ditemukan";
            $rq["CancelledByEmail"] = "N/A";
            $this->approvalmodel->cancel($rq);

            return array("content_html"=>$HD, "button_approve"=>"", "button_reject"=>"");            
        } else {
            $HD = json_decode($HD,true);        
            for ($a=0;$a<count($HD);$a++) {

                $email_content.= "<table border='1'>";
                $email_content.= "<tr>";
                $email_content.= "<td width='100px'> Kode Target </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["kode_target"]. "</td>";
                $email_content.= "</tr><tr>";
                $email_content.= "<tr>";
                $email_content.= "<td width='100px'> No Request </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["NoRequest"]. "</td>";
                $email_content.= "</tr><tr>";           
                $email_content.= "<td width='100px'> Salesman </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["Kd_Slsman"]. " - " .$HD[$a]["Nm_Slsman"]. " </td>";
                $email_content.= "</tr><tr>";
                $email_content.= "<td width='100px'> Periode </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["Tgl_Awal"]. " - " .$HD[$a]["Tgl_Akhir"]. " </td>";
                $email_content.= "</tr><tr>";   
                $email_content.= "<td width='100px'> Training </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["Training"]. " </td>";
                $email_content.= "</tr><tr>";               
                $email_content.= "<td width='100px'> Total Target </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["Total_Target"]. " </td>";
                $email_content.= "</tr><tr>";
                $email_content.= "<td width='100px'> Diinput Oleh </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["User_Name"]. " - " .$HD[$a]["Entry_Time"]. " </td>";                       
                $email_content.= "</tr><tr>";
                $email_content.= "<td width='100px'> Email </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["User_Email"]. " </td>";            

                // die($email_content);

                if ($params["mode"]=='CANCELREQ'){
                    $email_content.= "</tr><tr>";
                    $email_content.= "<td width='100px'> Dibatalkan Oleh </td>";
                    $email_content.= "<td width='300px''>" .$HD[$a]["UserDelete_Name"]. " - " .$HD[$a]["DeletedDate"]. " </td></tr>";
                }
                else if ($params["mode"]=='HAPUS'){
                    $email_content.= "</tr><tr>";
                    $email_content.= "<td width='100px'> Dihapus Oleh </td>";
                    $email_content.= "<td width='300px''>" .$HD[$a]["UserDelete_Name"]. " - " .$HD[$a]["DeletedDate"]. " </td></tr>";
                }
                else if ($params["mode"]=='REMINDER'){
                    $email_content.= "</tr><tr>";
                    $email_content.= "<td width='100px'> Last Email </td>";
                    $email_content.= "<td width='300px''>" .$HD[$a]["EmailedDate"]. " </td></tr>";
                }           
                else
                {
                    $email_content.= "</tr>";
                }       

                $email_content.= "</table>";
                $email_content.= "<br>";
                $email_content.= "<table border='1' width='375 px'>";
                $email_content.= "<tr>";        
                $email_content.= "<th width='100px'> Divisi </th>";     
                $email_content.= "<th width='200px'> Total Target </th>";           
                $email_content.= "</tr>";       
                $DT=$this->TargetSalesmanModel->GetTargetDTLog_ByNoRequest2($HD[$a]["NoRequest"], $params["lokasiAPIdanDB"], $config);
                for ($i=0;$i<count($DT);$i++) {
                    
                        $email_content.= "<tr>";        
                        $email_content.= "<td> " .$DT[$i]["Divisi"]. " </td>";      
                        $email_content.= "<td align='right'> " .$DT[$i]["Total_Target"]. " </td>";                  
                        $email_content.= "</tr>";
                    
                }
                $email_content.= "</table>";


                if ($Need_Approval==1){
                    if ($params["mode"]=='BARU'){
                        if ($HD[$a]["tipe"]=='BARU'){                       
                            $email_content.= "Target diatas adalah Input Target Pertama Kali <br>";
                        } else if ($HD[$a]["tipe"]=='REINPUT') {
                            $email_content.= "Target diatas memerlukan Approval karena berupa Re-Input Target yang sebelumnya sudah dihapus <br>";  
                        }
                    }
                    else if ($params["mode"]=='UBAH'){
                        $email_content.= "Target diatas adalah Perubahan Target <br>";
                    }
                    else if ($params["mode"]=='REMINDER'){
                        $email_content.= "Target diatas adalah Reminder dari Email yang dikirimkan 7 hari sebelumnya <br>";
                    }
            
                    $button_approve = site_url("TargetSalesmanApprovalV2/ProsesNew3?norequest=".urlencode($HD[$a]["NoRequest"]).
                                            "&kode_target=".urlencode($HD[$a]["kode_target"]).
                                            "&user=".urlencode($Email_Atasan).
                                            "&wilayah=".urlencode($KotaPusat)."&action=APPROVE");

                    $button_reject = site_url("TargetSalesmanApprovalV2/ProsesNew3?norequest=".urlencode($HD[$a]["NoRequest"]).
                                            "&kode_target=".urlencode($HD[$a]["kode_target"]).
                                            "&user=".urlencode($Email_Atasan).
                                            "&wilayah=".urlencode($KotaPusat)."&action=REJECT");  

                }//close need_approval
            }//close $HD
        }

        return array("content_html"=>$email_content, "button_approve"=>$button_approve, "button_reject"=>$button_reject);
    }  

    public function Notif_Email_KeUserInput($params, $config) {
        $HD=$this->TargetSalesmanModel->GetMasterHD2($params, $config);
        if (count($HD)==1){
            for ($a=0;$a<count($HD);$a++) {

                //$EmailFrom = "bitautoemail.noreply@gmail.com";
                $EmailFrom = $this->email->smtp_user;
                $EmailFromName = "BHAKTI AUTO-EMAIL";
                $EmailTo = $HD[$a]["User_Email"];

                //echo "<br> Email Berhasil Dikirim Ke ";
                //echo json_encode($EmailTo);
                
                $this->email->clear(true);
                $this->email->from($EmailFrom, $EmailFromName);
                $this->email->to($EmailTo); 
                if ($this->cc!=""){
                    $this->email->cc($this->cc);    
                }           

                if ($HD[$a]["IsApproved"]==1){
                    if (($HD[$a]["Level_Salesman"]=='SALESMAN') || ($HD[$a]["Level_Salesman"]=='SPG')) {
                        $subject = "Target ".$HD[$a]["Level_Salesman"]." ".$HD[$a]["Nm_Slsman"]." ".$HD[$a]["MonthYear"]." Diapprove";
                    } else {
                        $subject = "Target ".$HD[$a]["Nm_Slsman"]." Periode ".$HD[$a]["MonthYear"]." Diapprove";
                    }
                    $this->email->subject($subject);
                    $email_content= "<html><head></head><body>";
                    $email_content.= "Melalui email ini, kami menginfokan Target ".$HD[$a]["Level_Salesman"]." Periode ".$HD[$a]["MonthYear"]." telah disetujui dengan rincian sbb";                                    
                } else if ($HD[$a]["IsRejected"]==1){
                    if (($HD[$a]["Level_Salesman"]=='SALESMAN') || ($HD[$a]["Level_Salesman"]=='SPG')) {
                        $subject = "Target ".$HD[$a]["Level_Salesman"]." ".$HD[$a]["Nm_Slsman"]." ".$HD[$a]["MonthYear"]." Direject";
                    } else {
                        $subject = "Target ".$HD[$a]["Nm_Slsman"]." Periode ".$HD[$a]["MonthYear"]." Direject";
                    }                   
                    $this->email->subject($subject);
                    $email_content= "<html><head></head><body>";
                    $email_content.= "Melalui email ini, kami menginfokan Target ".$HD[$a]["Level_Salesman"]." Periode ".$HD[$a]["MonthYear"]." direject dengan rincian sbb";               
                } else if ($HD[$a]["IsApproved"]==0 && $HD[$a]["IsRejected"]==0){
                    if (($HD[$a]["Level_Salesman"]=='SALESMAN') || ($HD[$a]["Level_Salesman"]=='SPG')) {
                        $subject = "Target ".$HD[$a]["Level_Salesman"]." ".$HD[$a]["Nm_Slsman"]." ".$HD[$a]["MonthYear"]." Menunggu Approval";
                    } else {
                        $subject = "Target ".$HD[$a]["Nm_Slsman"]." Periode ".$HD[$a]["MonthYear"]." Menunggu Approval";
                    }                       
                    $this->email->subject($subject);
                    $email_content= "<html><head></head><body>";
                    $email_content.= "Melalui email ini, kami menginfokan Target ".$HD[$a]["Level_Salesman"]." Periode ".$HD[$a]["MonthYear"]." masih menunggu approval dengan rincian sbb";                
                }

                $email_content.= "<br><br>";
                $email_content.= "<table border='1'>";
                $email_content.= "<tr>";
                $email_content.= "<td width='100px'> Kode Target </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["kode_target"]. "</td>";
                $email_content.= "</tr><tr>";
                $email_content.= "<tr>";
                $email_content.= "<td width='100px'> No Request </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["NoRequest"]. "</td>";
                $email_content.= "</tr><tr>";           
                $email_content.= "<td width='100px'> Salesman </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["Kd_Slsman"]. " - " .$HD[$a]["Nm_Slsman"]. " </td>";
                $email_content.= "</tr><tr>";
                $email_content.= "<td width='100px'> Periode </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["Tgl_Awal"]. " - " .$HD[$a]["Tgl_Akhir"]. " </td>";
                $email_content.= "</tr><tr>";   
                $email_content.= "<td width='100px'> Training </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["Training"]. " </td>";
                $email_content.= "</tr><tr>";               
                $email_content.= "<td width='100px'> Total Target </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["Total_Target"]. " </td>";
                $email_content.= "</tr><tr>";
                $email_content.= "<td width='100px'> Diinput Oleh </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["User_Name"]. " - " .$HD[$a]["Entry_Time"]. " </td>";                       

                if ($HD[$a]["IsApproved"]==1){
                    $email_content.= "</tr><tr>";
                    $email_content.= "<td width='100px'> Diapprove Oleh </td>";
                    $email_content.= "<td width='300px''>" .$HD[$a]["UserApproved_Name"]. " - " .$HD[$a]["ApprovedDate"]. " </td></tr>";
                } else if ($HD[$a]["IsRejected"]==1){
                    $email_content.= "</tr><tr>";
                    $email_content.= "<td width='100px'> Direject Oleh </td>";
                    $email_content.= "<td width='300px''>" .$HD[$a]["UserRejected_Name"]. " - " .$HD[$a]["RejectedDate"]. " </td></tr>";
                } else if ($HD[$a]["IsApproved"]==0 && $HD[$a]["IsRejected"]==0){
                    $email_content.= "</tr><tr>";
                    $email_content.= "<td width='100px'> Diapprove Oleh </td>";
                    $email_content.= "<td width='300px''>" .$HD[$a]["UserApproved_Name"]. " - " .$HD[$a]["ApprovedDate"]. " </td></tr>";
                }       

                $email_content.= "</table>";
                $email_content.= "<br>";
                $email_content.= "<table border='1' width='375 px'>";
                $email_content.= "<tr>";        
                $email_content.= "<th width='100px'> Divisi </th>";     
                $email_content.= "<th width='75px'> Kategori_Insentif </th>";   
                $email_content.= "<th width='200px'> Total_Target </th>";           
                $email_content.= "</tr>";       
                $DT=$this->TargetSalesmanModel->GetMasterDT2($params, $config);
                for ($i=0;$i<count($DT);$i++) {
                    
                        $email_content.= "<tr>";        
                        $email_content.= "<td> " .$DT[$i]["Divisi"]. " </td>";      
                        $email_content.= "<td> " .$DT[$i]["Kategori_Insentif"]. " </td>";   
                        $email_content.= "<td align='right'> " .$DT[$i]["Total_Target"]. " </td>";                  
                        $email_content.= "</tr>";
                    
                }
                $email_content.= "</table>";
                
                if ($HD[$a]["IsApproved"]==1){
                    $email_content.= "<br> Data Di Program Target Salesman Telah Diperbaharui";
                } else if ($HD[$a]["IsRejected"]==1){
                    $email_content.= "<br> Data Di Program Target Salesman Tidak Ada Perubahan";
                } else if ($HD[$a]["IsApproved"]==0 && $HD[$a]["IsRejected"]==0){
                    $email_content.= "<br> Data Di Program Target Salesman Masih Menunggu Approval";
                }

                $email_content.= "<br>";
                $email_content.= "<br> Email ini dikirimkan otomatis oleh sistem, mohon tidak membalas email ini</body></html>";
                return $email_content;
                // if ($params["emailmode"]=='SENT'){
                //     $this->email->message($email_content);
                //     if (!$this->email->send()) {  
                //         show_error($this->email->print_debugger()); 
                //         //return("GAGAL");
                //     }else{   
                //         //return("SUKSES");
                //     }                   
                // } 
                // else {          
                //     echo "<h2><b>$subject</b></h2>";
                //     echo "<br>";
                //     echo $email_content;    
                // }
            }
        }
    }

    public function Notif_Email_KeUserInput_EmailCaraBaru($params, $config) {

        $this->load->library('email');
        $account = 1;
        $settings = $this->accountModel->EmailAccount($account);
        
        $HD=$this->TargetSalesmanModel->GetMasterHD2($params, $config);
        if (count($HD)==1){
            for ($a=0;$a<count($HD);$a++) {

                $EmailTo = $HD[$a]["User_Email"];
      
                if ($HD[$a]["IsApproved"]==1){
                    if (($HD[$a]["Level_Salesman"]=='SALESMAN') || ($HD[$a]["Level_Salesman"]=='SPG')) {
                        $subject = "Target ".$HD[$a]["Level_Salesman"]." ".$HD[$a]["Nm_Slsman"]." ".$HD[$a]["MonthYear"]." Diapprove";
                    } else {
                        $subject = "Target ".$HD[$a]["Nm_Slsman"]." Periode ".$HD[$a]["MonthYear"]." Diapprove";
                    }
                    $this->email->subject($subject);
                    $email_content= "<html><head></head><body>";
                    $email_content.= "Melalui email ini, kami menginfokan Target ".$HD[$a]["Level_Salesman"]." Periode ".$HD[$a]["MonthYear"]." telah disetujui dengan rincian sbb";                                    
                } else if ($HD[$a]["IsRejected"]==1){
                    if (($HD[$a]["Level_Salesman"]=='SALESMAN') || ($HD[$a]["Level_Salesman"]=='SPG')) {
                        $subject = "Target ".$HD[$a]["Level_Salesman"]." ".$HD[$a]["Nm_Slsman"]." ".$HD[$a]["MonthYear"]." Direject";
                    } else {
                        $subject = "Target ".$HD[$a]["Nm_Slsman"]." Periode ".$HD[$a]["MonthYear"]." Direject";
                    }                   
                    $this->email->subject($subject);
                    $email_content= "<html><head></head><body>";
                    $email_content.= "Melalui email ini, kami menginfokan Target ".$HD[$a]["Level_Salesman"]." Periode ".$HD[$a]["MonthYear"]." direject dengan rincian sbb";               
                } else if ($HD[$a]["IsApproved"]==0 && $HD[$a]["IsRejected"]==0){
                    if (($HD[$a]["Level_Salesman"]=='SALESMAN') || ($HD[$a]["Level_Salesman"]=='SPG')) {
                        $subject = "Target ".$HD[$a]["Level_Salesman"]." ".$HD[$a]["Nm_Slsman"]." ".$HD[$a]["MonthYear"]." Menunggu Approval";
                    } else {
                        $subject = "Target ".$HD[$a]["Nm_Slsman"]." Periode ".$HD[$a]["MonthYear"]." Menunggu Approval";
                    }                       
                    $this->email->subject($subject);
                    $email_content= "<html><head></head><body>";
                    $email_content.= "Melalui email ini, kami menginfokan Target ".$HD[$a]["Level_Salesman"]." Periode ".$HD[$a]["MonthYear"]." masih menunggu approval dengan rincian sbb";                
                }

                $email_content.= "<br><br>";
                $email_content.= "<table border='1'>";
                $email_content.= "<tr>";
                $email_content.= "<td width='100px'> Kode Target </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["kode_target"]. "</td>";
                $email_content.= "</tr><tr>";
                $email_content.= "<tr>";
                $email_content.= "<td width='100px'> No Request </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["NoRequest"]. "</td>";
                $email_content.= "</tr><tr>";           
                $email_content.= "<td width='100px'> Salesman </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["Kd_Slsman"]. " - " .$HD[$a]["Nm_Slsman"]. " </td>";
                $email_content.= "</tr><tr>";
                $email_content.= "<td width='100px'> Periode </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["Tgl_Awal"]. " - " .$HD[$a]["Tgl_Akhir"]. " </td>";
                $email_content.= "</tr><tr>";   
                $email_content.= "<td width='100px'> Training </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["Training"]. " </td>";
                $email_content.= "</tr><tr>";               
                $email_content.= "<td width='100px'> Total Target </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["Total_Target"]. " </td>";
                $email_content.= "</tr><tr>";
                $email_content.= "<td width='100px'> Diinput Oleh </td>";
                $email_content.= "<td width='300px''>" .$HD[$a]["User_Name"]. " - " .$HD[$a]["Entry_Time"]. " </td>";                       

                if ($HD[$a]["IsApproved"]==1){
                    $email_content.= "</tr><tr>";
                    $email_content.= "<td width='100px'> Diapprove Oleh </td>";
                    $email_content.= "<td width='300px''>" .$HD[$a]["UserApproved_Name"]. " - " .$HD[$a]["ApprovedDate"]. " </td></tr>";
                } else if ($HD[$a]["IsRejected"]==1){
                    $email_content.= "</tr><tr>";
                    $email_content.= "<td width='100px'> Direject Oleh </td>";
                    $email_content.= "<td width='300px''>" .$HD[$a]["UserRejected_Name"]. " - " .$HD[$a]["RejectedDate"]. " </td></tr>";
                } else if ($HD[$a]["IsApproved"]==0 && $HD[$a]["IsRejected"]==0){
                    $email_content.= "</tr><tr>";
                    $email_content.= "<td width='100px'> Diapprove Oleh </td>";
                    $email_content.= "<td width='300px''>" .$HD[$a]["UserApproved_Name"]. " - " .$HD[$a]["ApprovedDate"]. " </td></tr>";
                }       

                $email_content.= "</table>";
                $email_content.= "<br>";
                $email_content.= "<table border='1' width='375 px'>";
                $email_content.= "<tr>";        
                $email_content.= "<th width='100px'> Divisi </th>";     
                $email_content.= "<th width='75px'> Kategori_Insentif </th>";   
                $email_content.= "<th width='200px'> Total_Target </th>";           
                $email_content.= "</tr>";       
                $DT=$this->TargetSalesmanModel->GetMasterDT2($params, $config);
                for ($i=0;$i<count($DT);$i++) {
                    
                        $email_content.= "<tr>";        
                        $email_content.= "<td> " .$DT[$i]["Divisi"]. " </td>";      
                        $email_content.= "<td> " .$DT[$i]["Kategori_Insentif"]. " </td>";   
                        $email_content.= "<td align='right'> " .$DT[$i]["Total_Target"]. " </td>";                  
                        $email_content.= "</tr>";
                    
                }
                $email_content.= "</table>";
                
                if ($HD[$a]["IsApproved"]==1){
                    $email_content.= "<br> Data Di Program Target Salesman Telah Diperbaharui";
                } else if ($HD[$a]["IsRejected"]==1){
                    $email_content.= "<br> Data Di Program Target Salesman Tidak Ada Perubahan";
                } else if ($HD[$a]["IsApproved"]==0 && $HD[$a]["IsRejected"]==0){
                    $email_content.= "<br> Data Di Program Target Salesman Masih Menunggu Approval";
                }

                $email_content.= "<br>";
                $email_content.= "<br> Email ini dikirimkan otomatis oleh sistem, mohon tidak membalas email ini</body></html>";
                
                $emailResult =$this->accountModel->SendEmail($EmailTo, "bhaktiautoemail.noreply@bhakti.co.id", $subject, $email_content);
            }
        }
    }
    //Substitute bktAPI Until Here
    
}
?>