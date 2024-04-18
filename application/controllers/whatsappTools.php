<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class whatsappTools extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		// URL for request GET /messages
		$url = CHATAPI_URL.'messages?token='.CHATAPI_TOKEN;
		$result = file_get_contents($url); // Send a request
		$data = json_decode($result, 1); // Parse JSON
		foreach($data['messages'] as $message){ // Echo every message
		    echo "Sender:".$message['author']."<br>";
		    echo "Message: ".$message['body']."<br>";
		}
	}

	public function reboot()
	{
		// URL for request GET /messages
		$url = CHATAPI_URL.'reboot?token='.CHATAPI_TOKEN;
		$result = file_get_contents($url); // Send a request
	}

	public function me()
	{
		// URL for request GET /messages
		$url = CHATAPI_URL.'me?token='.CHATAPI_TOKEN;
		$result = file_get_contents($url); // Send a request
		print_r($result);
	}

	public function status()
	{
		// URL for request GET /messages
		$url = CHATAPI_URL.'status?token='.CHATAPI_TOKEN;
		$result = file_get_contents($url); // Send a request
		print_r($result);
	}

	public function messages()
	{
		$src = "OTHER";
		$accounts = $this->accountModel->GetActiveAPI($src);
		$IsSuccess = false;
		$log = array();
		$result = "";

		$WA_GROUP = "";
		$WA_GROUP_ID=0;
		$WA_INSTANCE_ID=0;

		if (count($accounts)>0) {
			foreach($accounts as $a) {
				// $a = $accounts[0];
				if ($IsSuccess==false) {
					$WA_GROUP = $a->whatsappGroup;
					$WA_GROUP_ID=$a->whatsappGroupId;
					$WA_INSTANCE_ID = $a->id;

					$url = $a->apiUrl."messages?token=".$a->apiToken;
					$result = file_get_contents($url);
					$IsSuccess = true;
				}
			}

		}
		print_r($result);
	}
}