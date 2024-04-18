<?php
class ConfigAutosentModel extends CI_Model
{
	function trx_server($BranchId){
		$this->db->select('AlamatWebService');
		$this->db->where('BranchId',$BranchId);
		$qry = $this->db->get('MsDatabase');
		return $qry->row()->AlamatWebService;
	}

	function get_location(){
		$server = $this->trx_server($_SESSION['logged_in']['branch_id']);
		$URLAPI = $server.'bktAPI/ConfigAutoSent/getCabang';
		$data = array('api' => 'APITES');

		$options = array(
		    'http' => array(
		        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		        'method'  => 'POST',
		        'content' => http_build_query($data)
		    )
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($URLAPI, false, $context);
		if ($result === FALSE) {}
		return $result;
	}
}
?>