<?php
class ReportBudgetMPModel extends CI_Model{
	public function __construct(){
		parent::__construct();
		$this->load->model('GzipDecodeModel');
	}

public function ListBudgetMP($url,$data){
    $post['api'] 	= 'APITES';
    $post['dari'] 	= urldecode($data['dari']);
    $post['sampai'] = urldecode($data['sampai']);
    $post['merk'] 	= urldecode($data['merk']);
    $post['report'] = urldecode($data['report']);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 6000,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => json_encode($post),
        CURLOPT_HTTPHEADER => array("Content-type: application/json")
    ));

    $response = curl_exec($curl);

    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $err = curl_error($curl);

    curl_close($curl);

    if($httpcode != 200){
        return array();
    } else {
        $result = $this->GzipDecodeModel->_decodeGzip_true($response);  

        if($result['result'] == 'sukses'){
            return $result['data'];
        } else {
            return array();
        }
    }
}


}
?>