<?php
	class WhatsappModel extends CI_Model
	{
		public $SEND_EMAIL_MODE = "OFF";

		function __construct()
		{
			parent::__construct();
		}

		function ReadVariable()
		{
			return $this->SEND_EMAIL_MODE;
		}
		
		function InsertLogWhatsapp($data)
		{
			$q = "INSERT INTO Log_Whatsapp(
					LogDate,
					WhatsappGroupId, WhatsappGroup, ApiInstanceId,
					MsgId, MsgType,
					PhoneNo,
					MsgParam,
					isSent,
					SentDate,
					GatewayUrl,
					UrlResponse,
					StopRetry, BranchId
				)
				VALUES(
					GETDATE(),
					'".$data['WhatsappGroupId']."','".$data['WhatsappGroup']."','".$data['ApiInstanceId']."',
					'".$data['MsgId']."','".$data['MsgType']."',
					'".$data['PhoneNo']."',
					'".$data['MsgParam']."',
					'".$data['isSent']."',
					GETDATE(),
					'".$data['GatewayUrl']."',
					'".$data['UrlResponse']."',
					'".$data['isSent']."', '".$data["Branch"]."'
				)";
				// die($q);

			$res = $this->db->query($q);
		}

		function deleteData($apiInstance){
   			$this->db->where('apiInstance', $apiInstance);
  			$this->db->delete('ms_account_whatsapp_api');
		}

		function addData($data){
	   		$this->db->insert('ms_account_whatsapp_api', $data);
		}

		function updateData($data,$apiInstance){
			$this->db->where('apiInstance', $apiInstance);
	   		$this->db->update('ms_account_whatsapp_api', $data);
		}

		function findDuplicate($apiInstance)
		{
			$str = "SELECT * FROM ms_account_whatsapp_api where apiInstance = '".$apiInstance."' ";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return true;
			} else {
				return false;
			}
		}

		function getAccountCount()
		{
			$str = "SELECT * FROM ms_account_whatsapp_api";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return $res->num_rows();
			} else {
				return 0;
			}
		}
		function GetListWhatsappAccount()
		{
			$str = " SELECT * from ms_account_whatsapp_api order by apiInstance";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return $res->result();
			} else {
				return array();
			}
		}
	}
?>
