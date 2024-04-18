<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class UnlockDiscTambahan extends NS_Controller 
{
	public function __construct()
	{
		parent::__construct();	
		$this->load->model("UnlockDiscTambahanModel");
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
     	
		$RequestID = $this->input->get("RequestID");
        $wilayah = $this->input->get("Wilayah");
		$action = $this->input->get("action");   
        $UserApproval = $this->input->get("UserApproval"); 

        $CreatedDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));
                
        //cari AlamatWEBService DB BHAKTI cabang yang diinginkan
        $conn = $this->UnlockDiscTambahanModel->get($wilayah);
        if ($conn!=null) {
            $connected = false;

            set_time_limit(3);

            try {
                set_time_limit(60);        
                $TEST_CONN = json_decode(file_get_contents($conn->AlamatWebService.API_BKT."/UnlockDiscTambahan/TestConnection"), true);
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
                $result = json_decode(file_get_contents($conn->AlamatWebService.API_BKT."/UnlockDiscTambahan/Proses?".
                "RequestID=".urlencode($RequestID)."&action=".urlencode($action).
                "&UserApproval=".urlencode($UserApproval).
                "&svr=".urlencode($conn->Server)."&db=".urlencode($conn->Database)."&uid=".urlencode(SQL_UID).
                "&pwd=".urlencode(SQL_PWD)),true);

                if ($result) {                
                    echo "Request Berhasil Diproses ke DB Bhakti";
                } else {
                    //apabila http request error (server cabang yg dituju sedang mati), 
                    echo "Request Tidak Berhasil Diproses ke DB Bhakti";
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