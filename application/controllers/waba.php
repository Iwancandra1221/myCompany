<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class waba extends NS_Controller 
{
	public $Branch = "MC";

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

		$accounts = $this->accountModel->GetActiveWhatsappAPI($src);
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
				$log["Branch"] = $this->Branch;
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

	public function templateList()
	{
		$src = "OTHER";
		$accounts = $this->accountModel->GetActiveWhatsappAPI($src);
		$IsSuccess = false;
		$log = array();
		$response = "";

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

					$url = $a->apiUrl."templates?token=".$a->apiToken;
					$response = file_get_contents($url);
					$IsSuccess = true;
				}
			}

		}
		exit($response);
	}

	public function sendFile()
	{
		$src = "OTHER";
		$resend = $this->input->get('resend');
	    $data = file_get_contents('php://input');
		
		// $data = '{
		// 		  "body": "'.$body.'",
		// 		  "phone": "'.$phone.'"
		// 		}';

		$options = stream_context_create(['http' => [
				'method'  => 'POST',
				'header'  => 'Content-type: application/json',
				'content' => $data
			]
		]);
		// Send a request

		$accounts = $this->accountModel->GetActiveWhatsappAPI($src);
		$IsSuccess = false;
		$log = array();
		$this->result = "";

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

					$url = $a->apiUrl."sendMessage?token=".$a->apiToken;
					$result = file_get_contents($url, false, $options);
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

			// simpan ke table Log_Whatsapp
			if($resend=="" && $res["sent"]==true){
				$phone = json_decode($data);
				$log["Branch"] = $this->Branch;
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

	public function sendWhatsapp($subUrl, $data_array, $phone, $resend=false)
	{
		$options = stream_context_create([
		    'http' => [
		        'method' => 'PUT',
		        'header' => "Content-type: application/json\r\n" .
		                    "Accept: application/json\r\n" .
		                    "Connection: close\r\n" .
		                    "Content-length: " . strlen($data_array) . "\r\n",
		        'protocol_version' => 1.1,
		        'content' => $data_array
		    ],
		    'ssl' => [
		        'verify_peer' => false,
		        'verify_peer_name' => false
		    ]
		]);

		$src = "OTHER";
		$accounts = $this->accountModel->GetActiveWhatsappAPI($src);
		$IsSuccess = false;
		$log = array();

		$this->response = "";
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

					// $url = $a->apiUrl."sendMessage?token=".$a->apiToken;
					$url = $a->apiUrl.$subUrl."?token=".$a->apiToken;
					$result = file_get_contents($url, false, $options);
					$res = json_decode($result);

					if (isset($res->sent)) {
						if ($res->sent==true) {
							$IsSuccess = true;
						}
					} else {
						$res->sent = false;
						$res->id = "";
						$res->description = "";
					}
					$this->response = json_encode($res);
				}
			}

			// simpan ke table Log_Whatsapp
			if($resend==false && $res->sent==true){

				if(!empty($this->Branch)){
					$log["Branch"] = $this->Branch;
				}else{
					$log["Branch"] = "MC";
				}
				
				$log["WhatsappGroupId"] = $WA_GROUP_ID;
				$log["WhatsappGroup"] = $WA_GROUP;
				$log["ApiInstanceId"] = $WA_INSTANCE_ID; 
				$log["MsgId"] = $res->id;

				$log['MsgType'] = "TEXT";
				$log['PhoneNo'] = $phone;
				$log['MsgParam'] = $data_array;
				$log['isSent'] = $res->sent;
				$log['GatewayUrl'] = $url;
				$log['UrlResponse'] = $result;
				$this->WhatsappModel->InsertLogWhatsapp($log);
			}
		}	
		return $this->response;	
	}


	//sendMessage ok
	public function sendMessage()
	{
		$resend = $this->input->get('resend');
		$data = json_decode(file_get_contents('php://input'),true);
		$Phones = explode(",", $data["phone"]);
		$Body = $data["body"];

		for ($i=0; $i < count($Phones); $i++) { 
			$data_array = '{
								"body": "'.$Body.'",
							  	"phone":"'.$Phones[$i].'"
						}';


			$this->response = $this->sendWhatsapp("sendMessage", $data_array, $Phones[$i], (($resend=="")?false:true));
		}

		exit($this->response);
	}

	//postman ok
	public function requestCL()
	{
		$resend = $this->input->get('resend');

		$data = json_decode(file_get_contents('php://input'),true);
		$Phones = explode(",", $data["phone"]);
		$ParamType1 = $data["paramType1"];
		$Param1 = htmlspecialchars_decode($data["param1"]);
		$ParamType2 = $data["paramType2"];
		$Param2 = htmlspecialchars_decode($data["param2"]);
		$ParamType3 = $data["paramType3"];
		$Param3 = htmlspecialchars_decode($data["param3"]);
		$ParamType4 = $data["paramType4"];
		$Param4 = htmlspecialchars_decode($data["param4"]);
		$ParamType5 = $data["paramType5"];
		$Param5 = htmlspecialchars_decode($data["param5"]);
		$ParamType6 = $data["paramType6"];
		$Param6 = htmlspecialchars_decode($data["param6"]);
		$ParamType7 = $data["paramType7"];
		$Param7 = htmlspecialchars_decode($data["param7"]);
		$ParamType8 = $data["paramType8"];
		$Param8 = htmlspecialchars_decode($data["param8"]);
		$ParamType9 = $data["paramType9"];
		$Param9 = htmlspecialchars_decode($data["param9"]);
		$ParamType10 = $data["paramType10"];
		$Param10 = htmlspecialchars_decode($data["param10"]);
		$ParamType11 = $data["paramType11"];
		$Param11 = htmlspecialchars_decode($data["param11"]);
		$ParamType12 = $data["paramType12"];
		$Param12 = htmlspecialchars_decode($data["param12"]);

		
		for($i=0; $i<count($Phones);$i++) {
			$data_array = '{"template":"request_cl_new",
			      "language":{"policy":"deterministic","code":"id"},
				  "namespace":"1da6f982_eb03_429d_ac5e_eed964aff479",
				  "params":[
  				  		{"type":"body",
  				  		 "parameters":[
  				  			{"type":"'.$ParamType1.'","text":"'.$Param1.'"},		
  				  			{"type":"'.$ParamType2.'","text":"'.$Param2.'"},
  				  			{"type":"'.$ParamType3.'","text":"'.$Param3.'"},
  				  			{"type":"'.$ParamType4.'","text":"'.$Param4.'"},
  				  			{"type":"'.$ParamType5.'","text":"'.$Param5.'"},
  				  			{"type":"'.$ParamType6.'","text":"'.$Param6.'"},
  				  			{"type":"'.$ParamType7.'","text":"'.$Param7.'"},
  				  			{"type":"'.$ParamType8.'","text":"'.$Param8.'"},		
  				  			{"type":"'.$ParamType9.'","text":"'.$Param9.'"},
  				  			{"type":"'.$ParamType10.'","text":"'.$Param10.'"},
  				  			{"type":"'.$ParamType11.'","text":"'.$Param11.'"},
  				  			{"type":"'.$ParamType12.'","text":"'.$Param12.'"}
  				  		 ]
  				  		}
				  	],
				  "phone":"'.$Phones[$i].'"
				}';

			$this->response = $this->sendWhatsapp("sendTemplate", $data_array, $Phones[$i], (($resend=="")?false:true));
		}

		exit($this->response);
	}

	//payrollEslip Failed
	public function payrollEslip()
	{
		$src = "OTHER";
		$resend = $this->input->get('resend');

		
		// BODY
		// SLIP *{{1}}* Periode *{{2}}* Nama Karyawan : *{{3}}* UserID : *{{4}}*
		// FOOTER
		// Auto Whatsapp PT. Bhakti Idola Tama
		// BUTTONS
		// download
		// url: http://zen.bhakti.co.id/ReportSalary/downloadFile?id={{1}}
 
		$data = json_decode(file_get_contents('php://input'),true);

		// $data = $_POST;

		$Phones = explode(",", $data["phone"]);

		$ParamType1 = $data["paramType1"];
		$Param1 = htmlspecialchars_decode($data["param1"]);
		$ParamType2 = $data["paramType2"];
		$Param2 = htmlspecialchars_decode($data["param2"]);
		$ParamType3 = $data["paramType3"];
		$Param3 = htmlspecialchars_decode($data["param3"]);
		$ParamType4 = $data["paramType4"];
		$Param4 = htmlspecialchars_decode($data["param4"]);
		$ButtonParamType1 = $data["buttonParamType1"];
		$ButtonParam1 = htmlspecialchars_decode($data["buttonParam1"]);
		

		for ($i=0; $i < count($Phones); $i++) { 

		    // $data = file_get_contents('php://input');
			$data_array = '{"template":"payroll_eslip",
				      "language":{"policy":"deterministic","code":"id"},
					  "namespace":"1da6f982_eb03_429d_ac5e_eed964aff479",
					  "params":[{"type":"body",
  				  		 		"parameters":[
	  				  			{"type":"'.$ParamType1.'","text":"'.$Param1.'"},		
	  				  			{"type":"'.$ParamType2.'","text":"'.$Param2.'"},
	  				  			{"type":"'.$ParamType3.'","text":"'.$Param3.'"},
	  				  			{"type":"'.$ParamType4.'","text":"'.$Param4.'"}]

	  				  		},{
	  				  			"type":"button",
								"sub_type":"url",
								"parameters":[{"type":"'.$ButtonParamType1.'","text":"'.$ButtonParam1.'"}]
	  				  		}],
					  "phone":"'.$Phones[$i].'"
					}';
			$this->response = $this->sendWhatsapp("sendTemplate", $data_array, $Phones[$i], (($resend=="")?false:true));
		}

		exit($this->response);
	}

	//check ok
	public function custom1ParamNoHeader()
	{
		$resend = $this->input->get('resend');
		$data = json_decode(file_get_contents('php://input'),true);
		
		$Phones = explode(",",$data["phone"]);
		$ParamType1 = $data["paramType1"];
		$Param1 = htmlspecialchars_decode($data["param1"]);
		// die($Param1);
		

		for($i=0; $i<count($Phones); $i++){

		    // $data = file_get_contents('php://input');
			$data_array = '{"template":"custom_1_param_no_header",
				      "language":{"policy":"deterministic","code":"en"},
					  "namespace":"1da6f982_eb03_429d_ac5e_eed964aff479",
					  "params":[
	  				  		{"type":"body",
	  				  		 "parameters":[
	  				  			{"type":"'.$ParamType1.'","text":"'.$Param1.'"}
	  				  		 ]
	  				  		}
					  	],
					  "phone":"'.$Phones[$i].'"
					}';

			$this->response = $this->sendWhatsapp("sendTemplate", $data_array, $Phones[$i], (($resend=="")?false:true));
		}

		exit($this->response);

	}

	//ok
	public function custom2ParamNoHeader()
	{
		$resend = $this->input->get('resend');
		$src = strtoupper($this->input->get("src"));
		if ($src=="SALES") $src = "B2B";

		$data = json_decode(file_get_contents('php://input'),true);

		$Phones = explode(",", $data["phone"]);
		// $Phones = $data["phone"];
		$ParamType1 = $data["paramType1"];
		$Param1 = $data["param1"];
		$ParamType2 = $data["paramType2"];
		$Param2 = $data["param2"];
		if ($src=="SALES") {
			$this->Branch = "B2B";
		} else if (isset($data["branch"])) {
			$this->Branch = $data["branch"];
		}
		// die($Param1);
		
	    for($i=0; $i<count($Phones);$i++) {

			$data_array = '{"template":"custom_2_params_no_header",
				      "language":{"policy":"deterministic","code":"en"},
					  "namespace":"1da6f982_eb03_429d_ac5e_eed964aff479",
					  "params":[
	  				  		{"type":"body",
	  				  		 "parameters":[
	  				  			{"type":"'.$ParamType1.'","text":"'.$Param1.'"},
	  				  			{"type":"'.$ParamType2.'","text":"'.$Param2.'"}
	  				  		 ]
	  				  		}
					  	],
					  "phone":"'.$Phones[$i].'"
					}';

			$this->response = $this->sendWhatsapp("sendTemplate", $data_array, $Phones[$i], (($resend=="")?false:true));
		}

		exit($this->response);
	}

	//ok
	public function template4Params()
	{
		$resend = $this->input->get('resend');
		$data = json_decode(file_get_contents('php://input'),true);
		// die(json_encode($data));

		$Phones = explode(",",$data["phone"]);
		$ParamType1 = $data["paramType1"];
		$Param1 = $data["param1"];
		$ParamType2 = $data["paramType2"];
		$Param2 = $data["param2"];
		$ParamType3 = $data["paramType3"];
		$Param3 = $data["param3"];
		$ParamType4 = $data["paramType4"];
		$Param4 = $data["param4"];

	    for($i=0; $i<count($Phones);$i++) {
			$data_array = '{"template":"start_template_4_xc8rtck",
				      "language":{"policy":"deterministic","code":"en"},
					  "namespace":"1da6f982_eb03_429d_ac5e_eed964aff479",
					  "params":[
	  				  		{"type":"body",
	  				  		 "parameters":[
	  				  			{"type":"'.$ParamType1.'","text":"'.$Param1.'"},		
	  				  			{"type":"'.$ParamType2.'","text":"'.$Param2.'"},
	  				  			{"type":"'.$ParamType3.'","text":"'.$Param3.'"},
	  				  			{"type":"'.$ParamType4.'","text":"'.$Param4.'"}
	  				  		 ]
	  				  		}
					  	],
					  "phone":"'.$Phones[$i].'"
					}';

			$this->response = $this->sendWhatsapp("sendTemplate", $data_array, $Phones[$i], (($resend=="")?false:true));
		}

		exit($this->response);
	}

	//ok
	public function template5Params()
	{
		$resend = $this->input->get('resend');
		$src = $this->input->get("src");
		// die(file_get_contents('php://input'));
		$data = json_decode(file_get_contents('php://input'),true);
		// die(json_encode($data));

		$Phones = explode(",",$data["phone"]);
		$ParamType1 = $data["paramType1"];
		$Param1 = $data["param1"];
		$ParamType2 = $data["paramType2"];
		$Param2 = $data["param2"];
		$ParamType3 = $data["paramType3"];
		$Param3 = $data["param3"];
		$ParamType4 = $data["paramType4"];
		$Param4 = $data["param4"];
		$ParamType5 = $data["paramType5"];
		$Param5 = $data["param5"];

		// die($Param6);
	    // 

	    for($i=0; $i<count($Phones);$i++) {
			$data_array = '{"template":"start_template_5_pcmj5p5g",
				      "language":{"policy":"deterministic","code":"en"},
					  "namespace":"1da6f982_eb03_429d_ac5e_eed964aff479",
					  "params":[
	  				  		{"type":"body",
	  				  		 "parameters":[
	  				  			{"type":"'.$ParamType1.'","text":"'.$Param1.'"},		
	  				  			{"type":"'.$ParamType2.'","text":"'.$Param2.'"},
	  				  			{"type":"'.$ParamType3.'","text":"'.$Param3.'"},
	  				  			{"type":"'.$ParamType4.'","text":"'.$Param4.'"},
	  				  			{"type":"'.$ParamType5.'","text":"'.$Param5.'"}
	  				  		 ]
	  				  		}
					  	],
					  "phone":"'.$Phones[$i].'"
					}';

			$this->response = $this->sendWhatsapp("sendTemplate", $data_array, $Phones[$i], (($resend=="")?false:true));
		}

		exit($this->response);
	}

	//ok
	public function template6Params()
	{
		$resend = $this->input->get('resend');
		$src = $this->input->get("src");
		// die(file_get_contents('php://input'));
		$data = json_decode(file_get_contents('php://input'),true);
		// die(json_encode($data));

		$Phones = explode(",",$data["phone"]);
		$ParamType1 = $data["paramType1"];
		$Param1 = $data["param1"];
		$ParamType2 = $data["paramType2"];
		$Param2 = $data["param2"];
		$ParamType3 = $data["paramType3"];
		$Param3 = $data["param3"];
		$ParamType4 = $data["paramType4"];
		$Param4 = $data["param4"];
		$ParamType5 = $data["paramType5"];
		$Param5 = $data["param5"];
		$ParamType6 = $data["paramType6"];
		$Param6 = $data["param6"];

		// die($Param6);
	    // 

	    for($i=0; $i<count($Phones);$i++) {
			$data_array = '{"template":"start_template_6_hgailwn",
				      "language":{"policy":"deterministic","code":"en"},
					  "namespace":"1da6f982_eb03_429d_ac5e_eed964aff479",
					  "params":[
	  				  		{"type":"body",
	  				  		 "parameters":[
	  				  			{"type":"'.$ParamType1.'","text":"'.$Param1.'"},		
	  				  			{"type":"'.$ParamType2.'","text":"'.$Param2.'"},
	  				  			{"type":"'.$ParamType3.'","text":"'.$Param3.'"},
	  				  			{"type":"'.$ParamType4.'","text":"'.$Param4.'"},
	  				  			{"type":"'.$ParamType5.'","text":"'.$Param5.'"},
	  				  			{"type":"'.$ParamType6.'","text":"'.$Param6.'"}
	  				  		 ]
	  				  		}
					  	],
					  "phone":"'.$Phones[$i].'"
					}';

			$this->response = $this->sendWhatsapp("sendTemplate", $data_array, $Phones[$i], (($resend=="")?false:true));
		}

		exit($this->response);
	}


	//ok
	public function template7Params()
	{
		$resend = $this->input->get('resend');
		$data = json_decode(file_get_contents('php://input'),true);
		// die(json_encode($data));

		$Phones = explode(",",$data["phone"]);
		$ParamType1 = $data["paramType1"];
		$Param1 = $data["param1"];
		$ParamType2 = $data["paramType2"];
		$Param2 = $data["param2"];
		$ParamType3 = $data["paramType3"];
		$Param3 = $data["param3"];
		$ParamType4 = $data["paramType4"];
		$Param4 = $data["param4"];
		$ParamType5 = $data["paramType5"];
		$Param5 = $data["param5"];
		$ParamType6 = $data["paramType6"];
		$Param6 = $data["param6"];
		$ParamType7 = $data["paramType7"];
		$Param7 = $data["param7"];

		// die($Param6);
	    // 
	   	
	   	for($i=0; $i<count($Phones);$i++) {
		    $data_array = '{"template":"start_template_7_copfl4yi",
				      "language":{"policy":"deterministic","code":"en"},
					  "namespace":"1da6f982_eb03_429d_ac5e_eed964aff479",
					  "params":[
	  				  		{"type":"body",
	  				  		 "parameters":[
	  				  			{"type":"'.$ParamType1.'","text":"'.$Param1.'"},		
	  				  			{"type":"'.$ParamType2.'","text":"'.$Param2.'"},
	  				  			{"type":"'.$ParamType3.'","text":"'.$Param3.'"},
	  				  			{"type":"'.$ParamType4.'","text":"'.$Param4.'"},
	  				  			{"type":"'.$ParamType5.'","text":"'.$Param5.'"},
	  				  			{"type":"'.$ParamType6.'","text":"'.$Param6.'"},
	  				  			{"type":"'.$ParamType7.'","text":"'.$Param7.'"}
	  				  		 ]
	  				  		}
					  	],
					  "phone":"'.$Phones[$i].'"
					}';
				
			
			$this->response = $this->sendWhatsapp("sendTemplate", $data_array, $Phones[$i], (($resend=="")?false:true));
		}

		exit($this->response);
	}

//-----------------------------------------------------------------------------------start custom1ParamNoHeader + custom2ParamNoHeader-----------------------------------------------------------------------------------//
	public function customParamNoHeader_v2()
	{

		$datapost = file_get_contents('php://input');
		$dataArray = json_decode($datapost, true);

		if ($dataArray !== null) {

			if(!empty($dataArray['src'])){

				$src = $dataArray['src'];

				if ($src=="SALES"){
					$src = "B2B";
				}
				
				if ($src=="SALES") {
					$this->Branch = "B2B";
				} else if (isset($data["branch"])) {
					$this->Branch = $data["branch"];
				}

			}


			$resultparameters = [];

			$count_data_param = count($dataArray['data_param']);

			if($count_data_param>1){
				if($count_data_param==2){
					$template='custom_2_params_no_header';
				}else if($count_data_param==4){
					$template='start_template_4_xc8rtck';
				}else if($count_data_param==5){
					$template='start_template_5_pcmj5p5g';
				}else if($count_data_param==6){
					$template='start_template_6_hgailwn';
				}else if($count_data_param==7){
					$template='start_template_7_copfl4yi';
				}else{
					$template='custom_1_param_no_header';
				}
			}else{
				$template='custom_1_param_no_header';
			}
			
			$code = ['en','id'];

			foreach ($dataArray['data_param'] as $key => $dp) {
				$parameters = array();

				$parameters = [
					'type' => $dp['ParamType'],
					'text' => $dp['Param']
				];
				$resultparameters[] = $parameters;
			}
			$data = array();

			foreach ($dataArray['phone'] as $key => $p) {
			    $data = '{
			        "template":"'.$template.'",
			        "language":{"policy":"deterministic","code":"en"},
			        "namespace":"1da6f982_eb03_429d_ac5e_eed964aff479",
			        "params":[
			                    {
			                        "type":"body",
			                        "parameters": '.json_encode($resultparameters).'  
			                    }
			                ],

			        "phone": "'.$p.'"
			    }';
			    
			    $this->response = $this->sendWhatsapp("sendTemplate", $data, $p, (($dataArray['resend'] == "") ? false : true));

			    $responseData = json_decode($this->response, true);

			    if ($responseData['sent'] === false) {
			        $x = 0;
			        $continueLoop = true;
			        for ($i = 1; $i < count($code); $i++) {
			            $data = str_replace('"code":"' . $code[$x] . '"', '"code":"' . $code[$i] . '"', $data);
			            $this->response = $this->sendWhatsapp("sendTemplate", $data, $p, (($dataArray['resend'] == "") ? false : true));
			            $responseData = json_decode($this->response, true);
			            $x++;

			            if ($responseData['sent'] === true) {
			                break;
			            }

			        }
			    }
			}

		}else{
			$err = ["sent" => false, "message" => "Data JSON tidak valid."];
			$this->response = json_encode($err);
		}


		exit($this->response);

	}
//-----------------------------------------------------------------------------------end custom1ParamNoHeader + custom2ParamNoHeader-----------------------------------------------------------------------------------//

	public function requestPembayaran()
	{
		$resend = $this->input->get('resend');
		$src = $this->input->get("src");
		// die(file_get_contents('php://input'));
		$data = json_decode(file_get_contents('php://input'),true);
		// die(json_encode($data));

		$Phones = explode(",",$data["phone"]);
		$ParamType1 = $data["paramType1"];
		$Param1 = $data["param1"];
		$ParamType2 = $data["paramType2"];
		$Param2 = $data["param2"];
		$ParamType3 = $data["paramType3"];
		$Param3 = $data["param3"];
		$ParamType4 = $data["paramType4"];
		$Param4 = $data["param4"];
		$ParamType5 = $data["paramType5"];
		$Param5 = $data["param5"];
		$ParamType6 = $data["paramType6"];
		$Param6 = $data["param6"];
		$ParamType7 = $data["paramType7"];
		$Param7 = $data["param7"];
		$ParamType8 = $data["paramType8"];
		$Param8 = $data["param8"];
		$ParamType9 = $data["paramType9"];
		$Param9 = $data["param9"];
		$ParamType10 = $data["paramType10"];
		$Param10 = $data["param10"];
		$ParamType11 = $data["paramType11"];
		$Param11 = $data["param11"];
		$ParamType12 = $data["paramType12"];
		$Param12 = $data["param12"];
		$ParamType13 = $data["paramType13"];
		$Param13 = $data["param13"];
		$ParamType14 = $data["paramType14"];
		$Param14 = $data["param14"];
		$ParamType15 = $data["paramType15"];
		$Param15 = $data["param15"];

		// die($Param6);
	    // 

	    for($i=0; $i<count($Phones);$i++) {

			$data_array = '{"template":"request_pembayaran",
				      "language":{"policy":"deterministic","code":"id"},
					  "namespace":"1da6f982_eb03_429d_ac5e_eed964aff479",
					  "params":[
	  				  		{"type":"body",
	  				  		 "parameters":[
	  				  			{"type":"'.$ParamType1.'","text":"'.$Param1.'"},		
	  				  			{"type":"'.$ParamType2.'","text":"'.$Param2.'"},
	  				  			{"type":"'.$ParamType3.'","text":"'.$Param3.'"},
	  				  			{"type":"'.$ParamType4.'","text":"'.$Param4.'"},
	  				  			{"type":"'.$ParamType5.'","text":"'.$Param5.'"},
	  				  			{"type":"'.$ParamType6.'","text":"'.$Param6.'"},
	  				  			{"type":"'.$ParamType7.'","text":"'.$Param7.'"},
	  				  			{"type":"'.$ParamType8.'","text":"'.$Param8.'"},
	  				  			{"type":"'.$ParamType9.'","text":"'.$Param9.'"},
	  				  			{"type":"'.$ParamType10.'","text":"'.$Param10.'"},
	  				  			{"type":"'.$ParamType11.'","text":"'.$Param11.'"},
	  				  			{"type":"'.$ParamType12.'","text":"'.$Param12.'"},
	  				  			{"type":"'.$ParamType13.'","text":"'.$Param13.'"},
	  				  			{"type":"'.$ParamType14.'","text":"'.$Param14.'"},
	  				  			{"type":"'.$ParamType15.'","text":"'.$Param15.'"}
	  				  		 ]
	  				  		}
					  	],
					  "phone":"'.$Phones[$i].'"
					}';
			$this->response = $this->sendWhatsapp("sendTemplate", $data_array, $Phones[$i], (($resend=="")?false:true));
		}

		exit($this->response);

	}

	public function ebilling()
	{
		$resend = $this->input->get('resend');

		$data = json_decode(file_get_contents('php://input'),true);

		$Phones = explode(",",$data["phone"]);
		$ParamType1 = $data["paramType1"];
		$Param1 = $data["param1"];
		$ParamType2 = $data["paramType2"];
		$Param2 = $data["param2"];
		$ParamType3 = $data["paramType3"];
		$Param3 = $data["param3"];
		$ParamType4 = $data["paramType4"];
		$Param4 = $data["param4"];
		$ParamType5 = $data["paramType5"];
		$Param5 = $data["param5"];
		$ParamType6 = $data["paramType6"];
		$Param6 = $data["param6"];

		for($i=0; $i<count($Phones);$i++) {
		    // $data = file_get_contents('php://input');
			$data_array = '{"template":"billing",
				      "language":{"policy":"deterministic","code":"id"},
					  "namespace":"1da6f982_eb03_429d_ac5e_eed964aff479",
					  "params":[
	  				  		{"type":"body",
	  				  		 "parameters":[
	  				  			{"type":"'.$ParamType1.'","text":"'.$Param1.'"},		
	  				  			{"type":"'.$ParamType2.'","text":"'.$Param2.'"},
	  				  			{"type":"'.$ParamType3.'","text":"'.$Param3.'"},
	  				  			{"type":"'.$ParamType4.'","text":"'.$Param4.'"},
	  				  			{"type":"'.$ParamType5.'","text":"'.$Param5.'"},
	  				  			{"type":"'.$ParamType6.'","text":"'.$Param6.'"}
	  				  		 ]
	  				  		}
					  	],
					  "phone":"'.$Phones[$i].'"
					}';
			$this->response = $this->sendWhatsapp("sendTemplate", $data_array, $Phones[$i], (($resend=="")?false:true));
		}

		exit($this->response);
	}

	public function paymentVA()
	{
		$resend = $this->input->get('resend');

		$data = json_decode(file_get_contents('php://input'),true);

		$Phones = explode(",",$data["phone"]);
		$ParamType1 = $data["paramType1"];
		$Param1 = $data["param1"];
		$ParamType2 = $data["paramType2"];
		$Param2 = $data["param2"];
		$ParamType3 = $data["paramType3"];
		$Param3 = $data["param3"];
		$ParamType4 = $data["paramType4"];
		$Param4 = $data["param4"];
		$ParamType5 = $data["paramType5"];
		$Param5 = $data["param5"];
		$ParamType6 = $data["paramType6"];
		$Param6 = $data["param6"];

	    // $data = file_get_contents('php://input');

	    for($i=0; $i<count($Phones);$i++) {

			$data_array = '{"template":"payment",
				      "language":{"policy":"deterministic","code":"id"},
					  "namespace":"1da6f982_eb03_429d_ac5e_eed964aff479",
					  "params":[
	  				  		{"type":"body",
	  				  		 "parameters":[
	  				  			{"type":"'.$ParamType1.'","text":"'.$Param1.'"},		
	  				  			{"type":"'.$ParamType2.'","text":"'.$Param2.'"},
	  				  			{"type":"'.$ParamType3.'","text":"'.$Param3.'"},
	  				  			{"type":"'.$ParamType4.'","text":"'.$Param4.'"},
	  				  			{"type":"'.$ParamType5.'","text":"'.$Param5.'"},
	  				  			{"type":"'.$ParamType6.'","text":"'.$Param6.'"}
	  				  		 ]
	  				  		}
					  	],
					  "phone":"'.$Phones[$i].'"
					}';

			$this->response = $this->sendWhatsapp("sendTemplate", $data_array, $Phones[$i], (($resend=="")?false:true));
		}

		exit($this->response);
	}

	public function paymentProcessed()
	{
		$resend = $this->input->get('resend');

		$data = json_decode(file_get_contents('php://input'),true);

		$Phones = explode(",",$data["phone"]);
		$ParamType1 = $data["paramType1"];
		$Param1 = $data["param1"];
		$ParamType2 = $data["paramType2"];
		$Param2 = $data["param2"];
		$ParamType3 = $data["paramType3"];
		$Param3 = $data["param3"];
		$ParamType4 = $data["paramType4"];
		$Param4 = $data["param4"];
		$ParamType5 = $data["paramType5"];
		$Param5 = $data["param5"];
		$ParamType6 = $data["paramType6"];
		$Param6 = $data["param6"];

	    // $data = file_get_contents('php://input');

	    for($i=0; $i<count($Phones);$i++) {

			$data_array = '{"template":"payment_processed",
				      "language":{"policy":"deterministic","code":"id"},
					  "namespace":"1da6f982_eb03_429d_ac5e_eed964aff479",
					  "params":[
	  				  		{"type":"body",
	  				  		 "parameters":[
	  				  			{"type":"'.$ParamType1.'","text":"'.$Param1.'"},		
	  				  			{"type":"'.$ParamType2.'","text":"'.$Param2.'"},
	  				  			{"type":"'.$ParamType3.'","text":"'.$Param3.'"},
	  				  			{"type":"'.$ParamType4.'","text":"'.$Param4.'"},
	  				  			{"type":"'.$ParamType5.'","text":"'.$Param5.'"},
	  				  			{"type":"'.$ParamType6.'","text":"'.$Param6.'"}
	  				  		 ]
	  				  		}
					  	],
					  "phone":"'.$Phones[$i].'"
					}';

			$this->response = $this->sendWhatsapp("sendTemplate", $data_array, $Phones[$i], (($resend=="")?false:true));
		}

		exit($this->response);
	}

			
	public function WriteLogWhatsapp()
	{
		$obj = json_decode(file_get_contents('php://input'));
		
		$params["Branch"] = (ISSET($obj->Branch)) ? $obj->Branch : '';
		$params["WhatsappGroupId"] = (ISSET($obj->WhatsappGroupId)) ? $obj->WhatsappGroupId : '';
		$params["WhatsappGroup"] = (ISSET($obj->WhatsappGroup)) ? $obj->WhatsappGroup : '';
		$params["ApiInstanceId"] = (ISSET($obj->ApiInstanceId)) ? $obj->ApiInstanceId : '';
		$params["MsgId"] = (ISSET($obj->MsgId)) ? $obj->MsgId : '';
		$params['MsgType'] = (ISSET($obj->MsgType)) ? $obj->MsgType : '';
		$params['PhoneNo'] = (ISSET($obj->PhoneNo)) ? $obj->PhoneNo : '';
		$params['MsgParam'] = (ISSET($obj->MsgParam)) ? $obj->MsgParam : '';
		$params['isSent'] = (ISSET($obj->isSent)) ? $obj->isSent : '';
		$params['GatewayUrl'] = (ISSET($obj->GatewayUrl)) ? $obj->GatewayUrl : '';
		$params['UrlResponse'] = (ISSET($obj->UrlResponse)) ? $obj->UrlResponse : '';
		
		$log = $this->WhatsappModel->InsertLogWhatsapp($params);
		
		$data['result']='SUCCESS';
		$data['error']='';
		
		$hasil = json_encode($data);
		header('HTTP/1.1: 200');
		header('Status: 200');
		header('Content-Length: '.strlen($hasil));
		exit($hasil);
	}
	
	public function WriteLogWhatsappTest()
	{
		$data = array(
			'Branch'=>'PTK',
			'WhatsappGroupId'=>123,
			'WhatsappGroup'=>'WhatsappGroupxxx',
			'ApiInstanceId'=>123,
			'MsgId'=> "123",
			'MsgType'=> 'MsgTypexx',
			'PhoneNo'=> '6285245229499',
			'MsgParam'=> 'MsgParamxxx',
			'isSent'=> 1,
			'GatewayUrl'=> 'GatewayUrlxxx',
			'UrlResponse'=> 'UrlResponsexxx',
		);
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => site_url()."/api/waba/WriteLogWhatsapp",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($data),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		
		echo $response;
	}


	//sendOneLineMessage is replaced by sendMessage
	public function sendOneLineMessage()
	{
		$src = "OTHER";
		$resend = $this->input->get('resend');
	    $data = file_get_contents('php://input');

		$Phones = explode(",",$data["phone"]);
		$ParamType1 = $data["paramType1"];
		$Param1 = $data["param1"];
		$ParamType2 = $data["paramType2"];
		$Param2 = $data["param2"];
		$ParamType3 = $data["paramType3"];
		$Param3 = $data["param3"];
		$ParamType4 = $data["paramType4"];
		$Param4 = $data["param4"];
		$ParamType5 = $data["paramType5"];
		$Param5 = $data["param5"];
		$ParamType6 = $data["paramType6"];
		$Param6 = $data["param6"];
		$ParamType7 = $data["paramType7"];
		$Param7 = $data["param7"];


		for ($i=0; $i < count($Phones); $i++) { 

			$data_array = '{"template":"custom_1_param_no_header",
			      	"language":{"policy":"deterministic","code":"en"},
				  	"namespace":"1da6f982_eb03_429d_ac5e_eed964aff479",
				  	"params":[
  				  		{"type":"body","parameters":[{"type":"text","text":"tes"}]}
				  	],
				  	"phone":"'.$Phones[$i].'"
				}';

			$options = stream_context_create([
			    'http' => [
			        'method' => 'PUT',
			        'header' => "Content-type: application/json\r\n" .
			                    "Accept: application/json\r\n" .
			                    "Connection: close\r\n" .
			                    "Content-length: " . strlen($data_array) . "\r\n",
			        'protocol_version' => 1.1,
			        'content' => $data_array
			    ],
			    'ssl' => [
			        'verify_peer' => false,
			        'verify_peer_name' => false
			    ]
			]);


			// Send a request

			$accounts = $this->accountModel->GetActiveWhatsappAPI($src);
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

						$url = $a->apiUrl."sendMessage?token=".$a->apiToken;

						$result = file_get_contents($url, false, $options);
						$res = json_decode($result);

						if (isset($res->sent)) {
							if ($res->sent==true) {
								$IsSuccess = true;
							}
						} else {
							$res->sent = false;
							$res->id = "";
							$res->description = "";
						}
						$result = json_encode($res);
					}
				}

				// simpan ke table Log_Whatsapp
				if($resend=="" && $res->sent==true){

					if(!empty($this->Branch)){
						$log["Branch"] = $this->Branch;
					}else{
						$log["Branch"] = "MC";
					}
					
					$log["WhatsappGroupId"] = $WA_GROUP_ID;
					$log["WhatsappGroup"] = $WA_GROUP;
					$log["ApiInstanceId"] = $WA_INSTANCE_ID; 
					$log["MsgId"] = $res->id;

					$log['MsgType'] = "LINK";
					$log['PhoneNo'] = $Phones[$i];
					$log['MsgParam'] = $data_array;
					$log['isSent'] = $res->sent;
					$log['GatewayUrl'] = site_url("sendLinkWA");
					$log['UrlResponse'] = $result;
					$this->WhatsappModel->InsertLogWhatsapp($log);
				}
			}

		}

		exit($result);
	}

	
}