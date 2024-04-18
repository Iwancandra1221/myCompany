<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class wabaViewer extends MY_Controller 
{
	public function __construct()
	{
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");

		parent::__construct();
		$this->load->model("WhatsappModel");
		$this->load->model("accountModel");
	}

	public function index()
	{
		$src = $this->input->get("src");
		$resend = $this->input->get('resend');

	    // $data = file_get_contents('php://input');
		$data = '{"template":"payment",
			      "language":{"policy":"deterministic","code":"in"},
				  "namespace":"1da6f982_eb03_429d_ac5e_eed964aff479",
				  "params":[
  				  		{"type":"body",
  				  		 "parameters":[
  				  			{"type":"text","text":"1,234,567"},
  				  			{"type":"text","text":"14-Jun-2022"},
  				  			{"type":"text","text":"14:00 WIB"},
  				  			{"type":"text","text":"PT.ABCD"},
  				  			{"type":"text","text":"KWITANSI/ABC/2022/06"},
  				  			{"type":"text","text":"10-Jun-2022"}
  				  		 ]
  				  		}
				  	],
				  "phone":"6281222345235"
				}';

		$options = stream_context_create(['http' => [
				'method'  => 'POST',
				'header'  => 'Content-type: application/json',
				'content' => $data
			]
		]);
		// Send a request

		$accounts = $this->accountModel->GetActiveAPI($src);
		$IsSuccess = false;
		$log = array();
		$result = "";
		$res = array();
		$res["sent"] = false;
		$res["id"] = "";

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

					$url = $a->apiUrl."sendTemplate?token=".$a->apiToken;
					$result = file_get_contents($url, false, $options);
					$res = json_decode($result,true);

					if ($res["sent"]==true) {
						$IsSuccess = true;
					}
				}
			}

			// simpan ke table Log_Whatsapp
			if($resend=="" && $res["sent"]==true){
				$phone = json_decode($data);
				$log["WhatsappGroupId"] = $WA_GROUP_ID;
				$log["WhatsappGroup"] = $WA_GROUP;
				$log["ApiInstanceId"] = $WA_INSTANCE_ID; 
				$log["MsgId"] = $res["id"];

				$log['MsgType'] = "LINK";
				$log['PhoneNo'] = $phone->phone;
				$log['MsgParam'] = $data;
				$log['isSent'] = $res["sent"];
				$log['GatewayUrl'] = site_url("sendLinkWA");
				$log['UrlResponse'] = $result;
				$this->WhatsappModel->InsertLogWhatsapp($log);
			}
		}

		exit($result);
	}

	public function messages($last=10,$chatId="")
	{
		$src = "OTHER";
		$resend = $this->input->get('resend');

		// https://api.chat-api.com/instance210962/message?token=4sq9i4ysv5bnbfpf&last=1
		// https://api.chat-api.com/instance210962/messages?token=4sq9i4ysv5bnbfpf&last=10
		// lastMessageNumber
		// firstMessageNumber
		// limit
		// chatId
		// min_time
		// max_time
		// msgId

		$params= "&last=".$last;
		if($chatId!="") $params .= "&chatId=".$chatId;
		
		// {
		//   "messages": [
		//     {
		//       "messageNumber": 106291,
		//       "id": "ABGHYoElIkQQTwIQ9-QfJvcHwjfsaNoaz-l26A",
		//       "body": "https://s3.eu-central-1.wasabisys.com/incoming-chat-api/2022/7/29/210962/0bc3912c-add3-4345-96ef-9d5e75a4a048.jpeg",
		//       "fromMe": 0,
		//       "self": 0,
		//       "isForwarded": 0,
		//       "author": "6281252244104@c.us",
		//       "time": 1659063205,
		//       "chatId": "6281252244104@c.us",
		//       "type": "image",
		//       "senderName": "Andreas Prabudi",
		//       "chatName": "6281252244104",
		//       "caption": null,
		//       "quotedMsgId": null,
		//       "meta": null
		//     },
				// {
			 //      "messageNumber": 106288,
			 //      "id": "gBGHYoEiI0UjXwIJbGl8Q6hl3S9S",
			 //      "body": "http://zen.bhakti.co.id/",
			 //      "fromMe": 1,
			 //      "self": 1,
			 //      "isForwarded": 0,
			 //      "author": "62215323808@c.us",
			 //      "time": 1659061014,
			 //      "chatId": "6281222345235@c.us",
			 //      "type": "chat",
			 //      "senderName": "62215323808@c.us",
			 //      "chatName": "6281222345235",
			 //      "caption": null,
			 //      "quotedMsgId": null,
			 //      "meta": null
			 //    }
		//    ]
		// }		   

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

					$url = $a->apiUrl."messages?token=".$a->apiToken.$params;
					// die($url);
					$result = file_get_contents($url, false);
					// die($result);

					$res = json_decode($result);
					/*
						result successful 
						{
						  "sent": true,
						  "message": "Sent to 6281222345235@c.us",
						  "description": "Message has been sent to the provider",
						  "id": "gBGHYoEiI0UjXwIJzDg_CJcHTY7F"
						}
						result failed
						{
						  "message": "body: Param text: Slip Tunjangan Prestasi atas nama  karyawan abcdefghijkl mnopqrstuvw xyz\ndengan UserID 123456 cannot have new-line/tab characters or more than 4 consecutive spaces"
						}
					*/
					if (isset($res->sent)) {
						if ($res->sent==true) {
							$IsSuccess = true;
						}
					} else {
						$res->sent = false;
						$res->id = "";
						$res->description = "";
						$IsSuccess = false;
					}

					$result = json_encode($res);
				}
			}

			// // simpan ke table Log_Whatsapp
			// if($resend=="" && $res["sent"]==true){
			// 	$phone = json_decode($data);
			// 	$log["WhatsappGroupId"] = $WA_GROUP_ID;
			// 	$log["WhatsappGroup"] = $WA_GROUP;
			// 	$log["ApiInstanceId"] = $WA_INSTANCE_ID; 
			// 	$log["MsgId"] = $res["id"];

			// 	$log['MsgType'] = "LINK";
			// 	$log['PhoneNo'] = $phone->phone;
			// 	$log['MsgParam'] = $data;
			// 	$log['isSent'] = $res["sent"];
			// 	$log['GatewayUrl'] = site_url("sendLinkWA");
			// 	$log['UrlResponse'] = $result;
			// 	$this->WhatsappModel->InsertLogWhatsapp($log);
			// }
		}

		
		exit($result);
	}


}