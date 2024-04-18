<?php
Class RekaphadiahlangsungModule extends CI_Model{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('GzipDecodeModel');
        $this->load->model('ConfigSysModel');
        $this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
        $this->API = 'APITES';
    }

    public function wilayah(){
        $svr = $_SESSION["conn"]->Server;
        $db  = $_SESSION["conn"]->Database;

        $url = $_SESSION["conn"]->AlamatWebService;

    	$url = $url . $this->API_BKT . "/MasterWilayah/GetListAllWilayah?api=" . $this->API . "&svr=" . $svr . "&db=" . $db;

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);

		if (curl_errno($ch)) {
			echo 'Curl error: ' . curl_error($ch);
		}

		curl_close($ch);

		return $this->GzipDecodeModel->_decodeGzip_true($response);
    }

	public function partner_type(){
	    $svr = $_SESSION["conn"]->Server;
	    $db  = $_SESSION["conn"]->Database;

	    $url = $_SESSION["conn"]->AlamatWebService;

	    $postFields = array(
	        'api' => $this->API,
	        'svr' => $svr,
	        'db' => $db
	    );

	    $url = $url . $this->API_BKT . "/MasterDealer/GetListPartnerType";

	    $ch = curl_init($url);

	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	    $response = curl_exec($ch);

	    if (curl_errno($ch)) {
	        echo 'Curl error: ' . curl_error($ch);
	    }

	    curl_close($ch);

	    return $this->GzipDecodeModel->_decodeGzip_true($response);
	}

	public function list($data){

	    $svr = $_SESSION["conn"]->Server;
	    $db  = $_SESSION["conn"]->Database;

	    $url = $_SESSION["conn"]->AlamatWebService;

	    $postFields = array(
	        'api' => $this->API,
	        'svr' => $svr,
	        'db' => $db,
	        'awal' => $data['awal'],
	        'akhir' => $data['akhir'],
	        'partner_type' => $data['partner_type'],
	        'wilayah' => $data['wilayah']
	    );

	    $url = $url . $this->API_BKT . "/Rekaphadiahlangsung/GetList";

	    $ch = curl_init($url);

	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	    $response = curl_exec($ch);

	    if (curl_errno($ch)) {
	        echo 'Curl error: ' . curl_error($ch);
	    }

	    curl_close($ch);

	    return $this->GzipDecodeModel->_decodeGzip_true($response);
	}


}
?>