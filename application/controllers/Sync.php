<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	class Sync extends NS_Controller 
	{
		public function __construct()
		{
			parent::__construct();	
			$this->load->model("SyncModel");	
			$this->load->model("MasterDbModel");	
			$this->load->library('email');
			$this->PHOENIX_URL  = "";
			$this->branch_id  = "";
		}
		
		public function GetPhoenixURL(){
			$PHOENIX_URL = $this->SyncModel->GetPhoenixURL();
			echo $PHOENIX_URL; // untuk dipakai web lain
		}
		
		public function SetPhoenixURL(){
			$PHOENIX_URL = $this->SyncModel->GetPhoenixURL();
			$this->PHOENIX_URL =  $PHOENIX_URL; // untuk dipakai di controller ini
		}
		
		
		public function GetPhoenixURL2(){
			$PHOENIX_URL = $this->SyncModel->GetPhoenixURL();
			echo json_encode($PHOENIX_URL); // untuk dipakai di Controller zenb2bapi di ZEN
		}
		
		public function GetToken()
		{
			$token = $this->SyncModel->GetToken($this->branch_id); // get token dari tabel Log_SyncToken yg sudah disimpan
			if($token!=''){
				//refresh token
				$refresh_token = $this->RefreshToken($token);
				if($refresh_token['result']=='SUCCESS'){
					$token = $refresh_token['token'];
				}
				else{
					$this->SyncModel->setTokenExpiredByBranch($this->branch_id);
					$token='';
				}
			}
			else{
				$token='';
			}
			
			//jika token tidak ditemukan atau sudah expired. maka ambil token yg baru
			if($token==''){
				$auth = $this->SyncModel->GetAuth($this->branch_id);
				log_message('error','GetToken - token : '.print_r($auth,true));
				if($auth){
					$data["grant_type"] = $auth['TOKEN_GRANT_TYPE'];
					$data["username"] = $auth['TOKEN_USERNAME'];
					$data["password"] = $auth['TOKEN_PASSWORD'];
					$data["user_type"] = $auth['TOKEN_USER_TYPE'];
					
					$curl = curl_init();
					curl_setopt_array($curl, array(
					CURLOPT_URL => $auth['TOKEN_URL'],
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 60,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => http_build_query($data),
					CURLOPT_HTTPHEADER => [
					'Authorization: Basic '.base64_encode($auth['TOKEN_AUTH_USERNAME'] . ':' . $auth['TOKEN_AUTH_PASSWORD'])
					],
					));
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
					
					$result = curl_exec($curl);
					$err = curl_error($curl);
					$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
					curl_close($curl);
					
					//jika berhasil
					if($http_code==200){
						$json = json_decode($result);
						if(isset($json->data->access_token)){
							$token = $this->SyncModel->SaveToken($this->branch_id, $json->data->access_token, $json->data->refresh_token, $result, $json->data->expires_in);
							return array("result"=>"SUCCESS", "token"=>$token);
						}
						else{
							return array("result"=>"FAILED", "token"=>"NOT FOUND");
						}
					}
					else if ($http_code==400) {
						return array("result"=>"FAILED", "token"=>"NOT FOUND");
					} else {
						return array("result"=>"FAILED", "token"=>"NOT FOUND");
					}
				}
				else {
					return array("result"=>"FAILED", "token"=>"NOT FOUND");
				}				
			}
			else {
				// die("existing token : ".$token."<br>");
				return array("result"=>"SUCCESS", "token"=>$token);
			}
		}

		public function GetTokenAPI2()
		{ 
			$branchid = urldecode($this->input->get("branch"));
			$token = $this->SyncModel->GetToken($branchid); // get token dari tabel Log_SyncToken yg sudah disimpan
			if($token!=''){
				//refresh token
				$refresh_token = $this->RefreshToken($token);
				if($refresh_token['result']=='SUCCESS'){
					$token = $refresh_token['token'];
				}
				else{
					$this->SyncModel->setTokenExpiredByBranch($branchid);
					$token='';
				}
			}
			else{
				$token='';
			}
			
			//jika token tidak ditemukan atau sudah expired. maka ambil token yg baru
			if($token==''){
				$auth = $this->SyncModel->GetAuth($branchid); 

				log_message('error','GetToken - token : '.print_r($auth,true));
				if($auth){
					$data["grant_type"] = $auth['TOKEN_GRANT_TYPE'];
					$data["username"] = $auth['TOKEN_USERNAME'];
					$data["password"] = $auth['TOKEN_PASSWORD'];
					$data["user_type"] = $auth['TOKEN_USER_TYPE'];
					
					$curl = curl_init();
					curl_setopt_array($curl, array(
					CURLOPT_URL => $auth['TOKEN_URL'],
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 60,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => http_build_query($data),
					CURLOPT_HTTPHEADER => [
					'Authorization: Basic '.base64_encode($auth['TOKEN_AUTH_USERNAME'] . ':' . $auth['TOKEN_AUTH_PASSWORD'])
					],
					));
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
					
					$result = curl_exec($curl);
					$err = curl_error($curl);
					$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
					curl_close($curl);
					// print_r($http_code);
					// print_r($result);die;
					$json = json_decode($result);
					
					//jika berhasil
					if($http_code==200){	
						if(isset($json->data->access_token)){
							$token = $this->SyncModel->SaveToken($branchid, $json->data->access_token, $json->data->refresh_token, $result, $json->data->expires_in); 
							echo $json->data->access_token;
						}
						else{
							echo "NOT FOUND";
						}
					}
					else if ($http_code==400) { 
						echo "NOT FOUND";
					} else {
						echo "NOT FOUND";
					}
				}
				else {
					echo "NOT FOUND";
				}				
			}
			else { 
				echo $token; 
			}
		}
		
		public function GetTokenAPI()
		{
			$this->branch_id = urldecode($this->input->get("branch"));
			$token = $this->GetToken();
			// die (json_encode($token));
			$result = array();

			if($token["result"]=="SUCCESS"){
				$result['token'] = $token["token"];
				$result['error'] = "";
			} else {
				$result['token'] = "";
				$result['error'] = $token["token"];				
			}
			echo json_encode($result);
		}
		
		//---------------------------TOKEN V2 START-------------------------------------
		public function GetPhoenixVAURL(){
			$PHOENIX_URL = $this->SyncModel->GetPhoenixVAURL();
			echo $PHOENIX_URL; // untuk dipakai web lain
		}

		public function GetTokenv2()
		{
			$auth = $this->SyncModel->GetAuth($this->branch_id);
			log_message('error','GetTokenv2 - token : '.print_r($auth,true));
			if($auth){
				$data["grant_type"] = $auth['TOKEN_V2_GRANT_TYPE'];
				$data["username"] = $auth['TOKEN_USERNAME'];
				$data["password"] = $auth['TOKEN_PASSWORD'];
				$data["user_type"] = 'Internal';
				$curl = curl_init();
				curl_setopt_array($curl, array(
				CURLOPT_URL => $auth['TOKEN_V2_URL'],
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => http_build_query($data)
				));
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
				
				$result = curl_exec($curl);
				$err = curl_error($curl);
				$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
				curl_close($curl);
				// echo $result; die;
				
				//jika berhasil ----------versi sebelumnya------------
				/*
				if($http_code==200){
					$json = json_decode($result);
					if(isset($json->accessToken)){
						$token = $json->accessToken;
						return array("result"=>"SUCCESS", "token"=>$token);
					}
					else{
						return array("result"=>"FAILED", "token"=>"NOT FOUND");
					}
				}
				else if ($http_code==400) {
					return array("result"=>"FAILED", "token"=>"NOT FOUND");
				} else {
					return array("result"=>"FAILED", "token"=>"NOT FOUND");
				}
				*/
				//jika berhasil
				if($http_code==200){
					$json = json_decode($result, true);
					if(isset($json['data']['access_token'])){
						$token = $json['data']['access_token'];
						$refreshToken = $json['data']['refresh_token'];
						$expiresIn = $json['data']['expires_in'];
						return array("result"=>"SUCCESS", "token"=>$token, "refresh_token"=>$refreshToken, "expires_in"=>$expiresIn);
					}
					else{
						return array("result"=>"FAILED", "token"=>"NOT FOUND");
					}
				}
				else if ($http_code==400) {
					return array("result"=>"FAILED", "token"=>"NOT FOUND");
				} else {
					return array("result"=>"FAILED", "token"=>"NOT FOUND");
				}
			}
			else {
				return array("result"=>"FAILED", "token"=>"NOT FOUND");
			}
		}

		public function GetTokenAPI2v2()
		{ 
			$branchid = urldecode($this->input->get("branch"));
			$auth = $this->SyncModel->GetAuth($branchid); 
			// echo json_encode($auth);die;
			log_message('error','GetTokenv2 - token : '.print_r($auth,true));
			if($auth){
				$data["grant_type"] = $auth['TOKEN_V2_GRANT_TYPE'];
				$data["username"] = $auth['TOKEN_USERNAME'];
				$data["password"] = $auth['TOKEN_PASSWORD'];
				$data["user_type"] = 'Internal';
				
				$curl = curl_init();
				curl_setopt_array($curl, array(
				CURLOPT_URL => $auth['TOKEN_V2_URL'],
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => http_build_query($data)
				));
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
				
				$result = curl_exec($curl);
				$err = curl_error($curl);
				$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
				curl_close($curl);
				// print_r($http_code);
				// print_r($result);die;
				
				//--------------versi sebelumnya ------------------------
				/*
				$json = json_decode($result);
				if($http_code==200){
					if(isset($json->accessToken)){
						echo $json->accessToken;
					}
					else{
						echo "NOT FOUND";
					}
				}
				else if ($http_code==400) { 
					echo "NOT FOUND";
				} else {
					echo "NOT FOUND";
				}
				*/
				
				$json = json_decode($result,true);
				if($http_code==200){
					if(isset($json['data']['access_token'])){
						echo $json['data']['access_token'];
					}
					else{
						echo "NOT FOUND";
					}
				}
				else if ($http_code==400) { 
					echo "NOT FOUND";
				} else {
					echo "NOT FOUND";
				}
			}
			else {
				echo "NOT FOUND";
			}
		}
		
		public function GetTokenAPIv2()
		{
			$this->branch_id = urldecode($this->input->get("branch"));
			$token = $this->GetTokenv2();
			// die (json_encode($token));
			$result = array();

			if($token["result"]=="SUCCESS"){
				$result['token'] = $token["token"];
				$result['refresh_token'] = $token["refresh_token"];
				$result['expires_in'] = $token["expires_in"];
				$result['error'] = "";
			} else {
				$result['token'] = "";
				$result['refresh_token'] = "";
				$result['error'] = $token["token"];				
			}
			echo json_encode($result);
		}
		//---------------------------TOKEN V2 END-------------------------------------
		
		
		
		public function myCompanyGetTokenAPI($branch_id) // untuk dipakai internal myCompany
		{
			$this->branch_id = $branch_id;
			$token = $this->GetTokenV2();
			// die (json_encode($token));
			if($token["result"]=="SUCCESS"){
				return $token["token"];
			}
			return '';
		}

		public function SetTokenExpired()
		{
			// echo("ABC");
			// echo(file_get_contents('php://input'));
			// $token = json_decode(file_get_contents('php://input'));
			$this->branch_id = urldecode($this->input->get("branch"));
			if ($this->SyncModel->SetTokenExpired($this->branch_id)) {
				echo ("SUCCESSFUL");
			} else {
				echo ("FAILED");
			}	
		}

		public function GetConfigSync()
		{
			// $data = json_decode(file_get_contents('php://input'));
			// $token = $data->token;
			$this->branch_id = urldecode($this->input->get("branch"));
			$master = (!$this->input->get("master") ? "" : urldecode($this->input->get("master")));

			$result = array();

			if(empty($this->branch_id)){
				$result["result"] = "FAILED";
				$result['username'] = "";
				$result['password'] = "";
				$result["branch_id"] = 0;
				$result["sync_url"] = "";
				$result["email_notif_sync_failed"] = "";
				$result["tables"] = array();
				$result["error"] = "";
			}else{
				$result["result"] = "SUCCESSFUL";
				$result['username'] = SQL_UID;
				$result['password'] = SQL_PWD;
        		$result["branch_code"] = $this->SyncModel->GetConfigSync("BRANCH_CODE", $this->branch_id);
				$result["branch_id"] = $this->SyncModel->GetConfigSync("BRANCH_ID", $this->branch_id);
				$result["sync_url"] = $this->SyncModel->GetConfigSync("SYNC_URL", $this->branch_id);
				$result["email_notif_sync_failed"] = $this->SyncModel->GetConfigSync("EMAIL_NOTIF_SYNC_FAILED", $this->branch_id);
				$result["tables"] = $this->SyncModel->GetTables($this->branch_id, 1, $master);
			}

			echo json_encode($result);	 
		}

		public function GetConfigSyncV2()
		{
			// $data = json_decode(file_get_contents('php://input'));
			// $token = $data->token;
			// $this->branch_id = urldecode($this->input->get("branch"));
			// $master = (!$this->input->get("master") ? "" : urldecode($this->input->get("master")));

			$result = array();
			$result["result"] = "SUCCESSFUL";
			$result['username'] = SQL_UID;
			$result['password'] = SQL_PWD;
			$result["TOKEN_V2_GRANT_TYPE"] = $this->SyncModel->GetConfigSync("TOKEN_V2_GRANT_TYPE", 'ALL');
			$result["PHOENIX_V2_URL"] = $this->SyncModel->GetConfigSync("PHOENIX_V2_URL", 'ALL');

			echo json_encode($result);	 
		}

		public function requestSync()
		{
			$table = $this->input->get('table');
			$data = $this->SyncModel->InsertNotification($table);
			$result = json_encode($data);
			header('HTTP/1.1: 200');
			header('Status: 200');
			header('Content-Length: '.strlen($result));
			exit($result);
		}
		
		public function GetRequestSync()
		{
			$this->branch_id = urldecode($this->input->get("branch"));

			$username = SQL_UID;
			$password = SQL_PWD;
			if(empty($this->branch_id))
			{
				$result = array();
				$result["result"] = "FAILED";
				$result['username'] = "";
				$result['password'] = "";
				$result["branch_id"] = 0;
				$result["sync_url"] = "";
				$result["email_notif_sync_failed"] = "";
				$result["tables"] = array();
				$result["error"] = "";
			}
			else
			{
				$result["result"] = "SUCCESSFUL";
				$result['username'] = $username;
				$result['password'] = $password;
				$result["branch_code"] = $this->SyncModel->GetConfigSync("BRANCH_CODE", $this->branch_id);
				$result["branch_id"] = $this->SyncModel->GetConfigSync("BRANCH_ID", $this->branch_id);
				$result["sync_url"] = $this->SyncModel->GetConfigSync("SYNC_URL", $this->branch_id);
				$result["email_notif_sync_failed"] = $this->SyncModel->GetConfigSync("EMAIL_NOTIF_SYNC_FAILED", $this->branch_id);
				$result["tables"] = $this->SyncModel->GetRequestSync($this->branch_id);
			}
			echo json_encode($result);	 
		}

		public function DeleteCof_Sync()
		{   
			$post = json_decode(file_get_contents('php://input'));
			$data = array();
			$data['branchid'] = $post->branchid; 
			$data['userid'] =  $post->userid; 
			$TokenUserRslt = $this->SyncModel->GetConfigSync("TOKEN_USERNAME", $data['branchid']);
			$rslt = '';
			if ($TokenUserRslt)
			{
				$rslt = $this->SyncModel->DeleteCof_Sync("TOKEN_USERNAME", $data['branchid']); 
			}  

			$TokenPassRslt = $this->SyncModel->GetConfigSync("TOKEN_PASSWORD", $data['branchid']);
			if ($TokenPassRslt)
			{
				$rslt = $this->SyncModel->DeleteCof_Sync("TOKEN_PASSWORD", $data['branchid']); 
			}  

			if ($rslt)
			{ 
				echo json_encode("SUCCESS"); 
			}
			else
			{ 
				echo json_encode("NOT FOUND"); 
			}
	    }


		public function AddCof_Sync()
		{   
	    	$post = json_decode(file_get_contents('php://input'));
			$data = array();
			$data['branchid'] = $post->branchid;
			$data['username'] =  $post->username;
			$data['password'] =  $post->password;
			$data['userid'] =  $post->userid;
 
			$now = date('Y/m/d h:i:s A');

			$rslt = '';
			$TokenUserRslt = $this->SyncModel->GetConfigSync("TOKEN_USERNAME", $data['branchid']);
			if ($TokenUserRslt)
			{
				$rslt = $this->SyncModel->UpdateCof_Sync("TOKEN_USERNAME", $data['branchid'],$data['username']); 
			}
			else
			{
				//ADD TOKEN USERNAME 
				$dataToken_username = array (
						'configType' => "CONFIG",
						'BranchId' => $data['branchid'],
			            'ConfigName'  => "TOKEN_USERNAME",
			            'ConfigValue'=> $data['username'], 
			            'IsActive' => 1,
			            'CreatedBy' => $data['userid'],
			            'CreatedDate' => $now,
			        );
				$rslt = $this->SyncModel->AddCof_Sync($dataToken_username);    
			}

			$TokenPassRslt = $this->SyncModel->GetConfigSync("TOKEN_PASSWORD", $data['branchid']);
			if ($TokenPassRslt)
			{
				$rslt = $this->SyncModel->UpdateCof_Sync("TOKEN_PASSWORD", $data['branchid'],$data['password']); 
			}
			else
			{
			 	//ADD TOKEN PASSWORD
				$dataToken_password = array (
						'configType' => "CONFIG",
						'BranchId' => $data['branchid'],
			            'ConfigName'  => "TOKEN_PASSWORD",
			            'ConfigValue'=> $data['password'], 
			            'IsActive' => 1,
			            'CreatedBy' => $data['userid'],
			            'CreatedDate' => $now,
			        );
				$rslt = $this->SyncModel->AddCof_Sync($dataToken_password);  
			} 

			if ($rslt)
			{ 
				echo json_encode("SUCCESS"); 
			}
			else
			{ 
				echo json_encode("FAILED"); 
			}
		}
		
		public function UpdateRequestSync()
		{
			$branch_id = urldecode($this->input->get("branch"));
			$tablename = urldecode($this->input->get("tablename"));
			$result = $this->SyncModel->UpdateRequestSync($branch_id, $tablename);
			echo json_encode($result);
		}

		public function isTokenValid($token){
			$url = "https://gateway-sit-b2b.bhakti.co.id:9000/api/account/branch/view";
			$data = [];
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => http_build_query($data),
				CURLOPT_HTTPHEADER => [
					'Authorization: Bearer '.$token
				],
			));
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			
			$result = curl_exec($curl);
			$err = curl_error($curl);
			$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);
			// print_r($http_code);
			// print_r($result);die;
			// $json = json_decode($result);
			// echo $http_code;
			$r = true;
			if($http_code==401) $r=false;

			return $r;
		}

		public function RefreshToken($token){
			$auth = $this->SyncModel->GetAuth($this->branch_id);
			if($auth){
				$data["grant_type"] = 'refresh_token';
				$data["refresh_token"] = $token;
				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_URL => $auth['TOKEN_URL'],
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 60,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => http_build_query($data),
					CURLOPT_HTTPHEADER => [
					'Content-Type: application/x-www-form-urlencoded',
					'Authorization: Basic '.base64_encode($auth['TOKEN_AUTH_USERNAME'] . ':' . $auth['TOKEN_AUTH_PASSWORD'])
					]
				));
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
				
				$result = curl_exec($curl);
				$err = curl_error($curl);
				$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
				curl_close($curl);
				// echo($result);die;
				if($http_code==200){	
					$json = json_decode($result);
					if(isset($json->data->access_token)){
						$token =  $this->SyncModel->SaveToken($this->branch_id, $json->data->access_token, $json->data->refresh_token, $result, $json->data->expires_in);
						return array("result"=>"SUCCESS", "token"=>$token);
					}
					else{
						return array("result"=>"FAILED", "token"=>"NOT FOUND");
					}
				}
				else {
					return array("result"=>"FAILED", "token"=>"NOT FOUND");
				}
			}
			else {
				return array("result"=>"FAILED", "token"=>"NOT FOUND");
			}	
		}
		
		public function RefreshTokenV2(){
		
			$this->branch_id = urldecode($this->input->get("branch"));
			$token = urldecode($this->input->get("token"));
			$refresh_token = urldecode($this->input->get("refresh_token"));
			// echo $token;die;
			$auth = $this->SyncModel->GetAuth($this->branch_id);
			// echo json_encode($auth);die;
			if($auth){
				$data["grant_type"] = 'refresh_token';
				$data["token"] = $token;
				$data["refresh_token"] = $refresh_token;
				// echo json_encode($data);die;
				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_URL => 'https://va-sit.bhakti.co.id/api/account/oauth/refresh', //$auth['REFRESH_TOKEN_V2_URL'],
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 60,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => http_build_query($data),
					CURLOPT_HTTPHEADER => [
					'Content-Type: application/x-www-form-urlencoded',
					'Authorization: Bearer '.$token
					]
				));
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
				
				$result = curl_exec($curl);
				$err = curl_error($curl);
				$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
				curl_close($curl);
				// echo($result);die;
				
				if($http_code==200){	
					$json = json_decode($result);
					if(isset($json->data->access_token)){
						$token =  $this->SyncModel->SaveToken($this->branch_id, $json->data->access_token, $json->data->refresh_token, $result, $json->data->expires_in);
						echo json_encode(array("result"=>"SUCCESS", "token"=>$json->data->access_token, "refresh_token"=>$json->data->refresh_token, "token"=>$token, "expires_in"=>$json->data->expires_in));
					}
					else{
						echo json_encode(array("result"=>"FAILED", "token"=>"NOT FOUND"));
					}
				}
				else {
					echo json_encode(array("result"=>"FAILED", "token"=>"NOT FOUND"));
				}
			}
			else {
				echo json_encode(array("result"=>"FAILED", "token"=>"NOT FOUND"));
			}	
		}
		
		public function RefreshTokenDebug(){
			$this->branch_id = 'PTK';
			$token = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJhY3RpdmVCcmFuY2giOnsiaWQiOjE1LCJjb2RlIjoiUFRLIiwibmFtZSI6IlBPTlRJQU5BSyIsImJyYW5jaEhlYWRJZCI6MjY1Mn0sInVzZXJfbmFtZSI6ImJoYWt0aXBvbnRpYW5ha0BiaGFrdGkuY28uaWQiLCJyb2xlSWQiOjMsInBlcm1pc3Npb24iOm51bGwsImJyYW5jaFJlZ2lvbiI6WzEzLDMyLDEsMjAsMTUsMyw2LDI4LDQyLDI3LDM5LDE4LDQ2LDM1LDcsMzYsMjIsMzcsMjQsNDMsMiw0NywyOSwxNCw4LDQ0LDIzLDI1LDQwLDExLDE2LDMzLDQsMTcsMTksMzQsMzEsNDUsNDgsMjEsMTAsNDEsMzgsMjYsNSw5LDMwLDEyXSwicm9sZVR5cGUiOm51bGwsInVzZXJJZCI6NDA0OSwiYnJhbmNoIjpbMSwyLDMsNCw1LDYsNyw4LDksMTAsMTEsMTIsMTMsMTQsMTUsMTYsMTcsMTgsMTldLCJiYXNlU3RhdGlvbklkIjpbMjBdLCJjbGllbnRfaWQiOiJjbGllbnRJZCIsImFjY291bnRJZCI6bnVsbCwiYWN0aXZlUGFydG5lciI6bnVsbCwiem9uZSI6IiswNzowMCIsInNjb3BlIjpbInJlYWQiLCJ3cml0ZSJdLCJhdGkiOiJibDN4bWc3dnZkTjM2Q3BmM1NvbkotUEhuX00iLCJuYW1lIjoiYmhha3RpcG9udGlhbmFrQGJoYWt0aS5jby5pZCIsImFjdGl2ZUJyYW5jaFJlZ2lvbiI6eyJpZCI6NDMsImNvZGUiOiJQWSIsIm5hbWUiOiJQUk9ZRUsiLCJicmFuY2hSZWdpb25IZWFkSWQiOjQ2NX0sImlzRXh0ZXJuYWxPd25lciI6ZmFsc2UsInRhZyI6W10sInVzZXJUeXBlIjoiSW50ZXJuYWwiLCJleHAiOjE2ODQ0NjgwMTEsImp0aSI6Ik55MHFWUW94QmN4Q2pUN25DMEo3V0xFOE8xQSIsImVtYWlsIjoiYmhha3RpcG9udGlhbmFrQGJoYWt0aS5jby5pZCJ9.M7v9t2qTm01h3XjinIIcZxpKtqPe7ZoIaV26R9gJG2e7c3IhNbQ4WPlcLBiF45cGJpMJPjXwrufuG0HoqG3rXcBioWKGzUAx8iERPUgjEdqC0Dzc_6V08L_S6F0vp_krpJqMo7ho_HHoqM4b5sjAoJv96YzQnBZfx08-yG80Zs7frq5W0yuVrw6d-vneLAAOXYK8aTVkLsHAdKZU1Voi79gv2N7M3nFkrAi-LpVRtSugdC63DEUh_SBm3qBmkXNdIodKZd6s2__K6EBM-Si83QTq7pWS0Cq2qOc3_6_AsloiFlo-9LuAI6-muScA_xfy4pVcRGiW9vXhKeW9_nOKbg';
			$res = $this->RefreshToken($token);
		}
		
		public function SyncMaster(){

			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "SYNC MASTER";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU SYNC MASTER";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

			$databases = $this->MasterDbModel->getList();
			// echo json_encode($databases);die;
			$data['databases'] = $databases;
			$this->RenderView('SyncMaster',$data);
		}
		
		public function SyncStart(){

			$params = array();	
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "SYNC MASTER";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES SYNC MASTER";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

			$post = $this->PopulatePost();
    		$conn = $this->MasterDbModel->get($post['databaseId']);
			$date = date('Y-m-d H:i:s');
			$insert = $this->SyncModel->InsertLogActivity($conn, $date);
			$insert['tanggal'] = $date;
			echo json_encode($insert);
			
		}
		
		public function SyncData(){
			$post = $this->PopulatePost();
    		$conn = $this->MasterDbModel->get($post['databaseId']);
			$data['result'] = 'FAILED';
			$data['error'] = '';
					
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $conn->AlamatWebService.API_BKT."/Sync",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 1800,
			// CURLOPT_POST => 1,
			// CURLOPT_POSTFIELDS => json_encode($data),
			// CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			
			if ($response===false) {
				$data['error'] = "API Tujuan OFFLINE";
				} else {
				$data = $this->SyncModel->UpdateLogActivity($conn, $post['tanggal']);
			}
			
			echo json_encode($data);
			
		}
		
		public function GetLogSync(){
			$databaseId = $this->input->get("databaseId");
			if($databaseId!=''){
				$conn = $this->MasterDbModel->get($databaseId);
				$databaseId  = $conn->BranchId;
			}
			$logs = $this->SyncModel->GetLogs($databaseId);
			echo json_encode($logs);
		}

		public function GetAuth(){

			$this->branch_id = urldecode($this->input->get("branch"));

			$Auth = $this->SyncModel->GetAuth($this->branch_id);

			echo json_encode($Auth);	

		}
	}
?>	