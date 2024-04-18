<?php
	class SyncModel extends CI_Model
	{
		function __construct()
		{
			parent::__construct();
			$CI = &get_instance();
		}
		
		function GetPhoenixURL()
		{
			$query = "SELECT TOP 1 ConfigValue
						from Cof_Sync 
						Where ConfigName='PHOENIX_URL' and IsActive=1";
			$res = $this->db->query($query);
			if ($res->num_rows()>0)
				return $res->row()->ConfigValue;
			else
				return null;
		}
		
		function GetPhoenixVAURL()
		{
			$query = "SELECT TOP 1 ConfigValue
						from Cof_Sync 
						Where ConfigName='PHOENIX_V2_URL' and IsActive=1";
			$res = $this->db->query($query);
			if ($res->num_rows()>0)
				return $res->row()->ConfigValue;
			else
				return null;
		}

		function AddCof_Sync($data){
	   		return $this->db->insert('Cof_Sync', $data);
		}
 
		function UpdateCof_Sync($configName, $branchId, $configValue){  
			return $this->db->query("UPDATE Cof_Sync SET ConfigValue  = '".$configValue."' WHERE BranchId ='".$branchId."' and ConfigName  ='".$configName."'");
		} 

		function DeleteCof_Sync($configName,$branchId)
		{	
			$this->db->where('BranchId',$branchId);
			$this->db->where('ConfigName',$configName); 
			$this->db->delete('Cof_Sync');
		}

		function GetToken($branch_id)
		{
			$query = "SELECT TOP 1 RefreshToken
						from Log_SyncToken 
						Where BranchId='".$branch_id."' 
						AND GETDATE() < TokenExpired and IsActive=1";
						// die($query);
			$res = $this->db->query($query);
			if ($res->num_rows()>0)
				return $res->row()->RefreshToken;
			else
				return null;
		}
		function setTokenExpiredByBranch($branch_id){
			$query = "UPDATE Log_SyncToken
					  SET IsActive=0 
					  WHERE BranchId='".$branch_id."' 
						AND GETDATE()<TokenExpired 
						and IsActive=1";

			log_message('error','setTokenExpiredByBranch '.$this->db->last_query());			
			$this->db->query($query);
		}

		function SetTokenExpired($branch_id)
		{
			$query = "UPDATE Log_SyncToken
					  SET IsActive=0 
					  WHERE BranchId='".$branch_id."' 
						AND GETDATE()<TokenExpired 
						and IsActive=1";
			$this->db->query($query);
			
			$str = "SELECT * FROM Log_SyncToken 
					WHERE BranchId='".$branch_id."' 
						AND IsActive=0 ";
			$res = $this->db->query($str);
			if ($res->num_rows()>0)
				return true;
			else
				return false;
		}
		
		function SaveToken($branch_id, $token, $refresh_token, $token_json, $expires_in)
		{	
			$q = "INSERT INTO Log_SyncToken(LogDate, BranchId, TokenType, Token, RefreshToken, TokenJson, TokenExpired, IsActive)
				  VALUES(GETDATE(), '".$branch_id."','SYNC','".$token."','".$refresh_token."','".$token_json."', DATEADD(second, ".$expires_in.", GETDATE()), 1)";
			
			$res = $this->db->query($q);
			if($res){
				return $token;
			} else {
				
			}
		}
		
		function GetAuth($branch_id){
			// $query = "SELECT * from Cof_Sync 
			// 		  WHERE (BranchId = '".$branch_id."' or BranchId='ALL') 
			// 		  	AND ConfigName LIKE '%TOKEN_%'";
			$query = "
				select a.*
				from (select * from Cof_Sync
					WHERE (BranchId = '".$branch_id."' or BranchId='ALL') 
					AND ConfigName LIKE '%TOKEN_%') a 
					inner join 
					(SELECT ConfigType, ConfigName, ConfigValue, MAX(case when BranchId ='ALL' then 2 else 1 end) as Priority
						from Cof_Sync 
						WHERE (BranchId = '".$branch_id."' or BranchId='ALL') 
						AND ConfigName LIKE '%TOKEN_%'
						GROUP BY ConfigType, ConfigName, ConfigValue
					) b on (case when a.BranchId ='ALL' then 2 else 1 end)=b.[Priority] and a.ConfigType=b.ConfigType and a.ConfigName=b.ConfigName
				";
			// die($query);
			$res = $this->db->query($query);
			if ($res->num_rows()>0){
				$auth = array();
				foreach($res->result() as $row) {
					$auth[$row->ConfigName] = $row->ConfigValue;
				}
				return $auth;
			}
			else
				return null;
		}
		
		function GetSyncUrl($branch_id){
			// $query = "SELECT ConfigName, ConfigValue from Cof_Sync WHERE BranchId = '".$branch_id."' AND ConfigName IN ('SYNC_URL','BRANCH_ID')";
			$query = "
				select a.*
				from (select * from Cof_Sync
					WHERE (BranchId = '".$branch_id."' or BranchId='ALL') 
					AND ConfigName IN ('SYNC_URL','BRANCH_ID')) a 
					inner join 
					(SELECT ConfigType, ConfigName, ConfigValue, MAX(case when BranchId ='ALL' then 2 else 1 end) as Priority
						from Cof_Sync 
						WHERE (BranchId = '".$branch_id."' or BranchId='ALL') 
						AND ConfigName IN ('SYNC_URL','BRANCH_ID')
						GROUP BY ConfigType, ConfigName, ConfigValue
					) b on (case when a.BranchId ='ALL' then 2 else 1 end)=b.[Priority] and a.ConfigType=b.ConfigType and a.ConfigName=b.ConfigName
				";
			$res = $this->db->query($query);
			if ($res->num_rows()>0){
				$data = array();
				foreach($res->result() as $row) {
					$data[$row->ConfigName] = $row->ConfigValue;
				}
				return $data;
			}
			else
				return null;
		}

		function GetConfigSync($ConfigName, $BranchId, $IsActive=1){
			$query = "SELECT ConfigName, ConfigValue, ConfigType, ConfigId 
					  from Cof_Sync 
					  WHERE BranchId IN('".$BranchId."') AND ConfigName = '".$ConfigName."' and IsActive=".$IsActive;
			$res = $this->db->query($query);
			if ($res->num_rows()>0){
				// die(json_encode($res->row()));
				return $res->row()->ConfigValue;
			} else {
				return "";
			}
		}

		function GetTables($BranchId='ALL', $IsActive=1, $Master=""){
			$query = "SELECT ConfigName, ConfigValue, ConfigType, ConfigId 
					  from Cof_Sync 
					  WHERE ConfigType='TABLE' and BranchId IN('ALL') and IsActive=".$IsActive."";
			if ($Master!="") {
				$query.= " and (ConfigName='".$Master."' or Level < (select Level From Cof_Sync Where ConfigType='TABLE' and BranchId IN('ALL') and IsActive=".$IsActive." and ConfigName='".$Master."')) ";
			}
			$query .= " ORDER BY Level";
			$res = $this->db->query($query);
			if ($res->num_rows()>0){
				// die(json_encode($res->row()));
				return $res->result();
			} else {
				return array();
			}
		}
		
		function InsertNotification($data){
			$ERR_MSG = '';
			$this->db->trans_begin();
			$tables = $this->GetTableName($data);
			foreach($tables as $table){
				$branchs  = $this->GetBranchCode($table->TableName);
				foreach($branchs as $branch){
					$query = "SELECT * FROM Trans_SyncPool WHERE BranchCode='".$branch->BranchId."' AND TableName='".$table->TableName."' ";
					$res = $this->db->query($query);
					if ($res->num_rows()>0){
						if($res->row()->IsProcessed == 1){
							$this->db->where('BranchCode',$branch->BranchId);
							$this->db->where('TableName',$table->TableName);
							$this->db->set('NotifiedDate',$this->convertDatetimeLocalToDatetimeX(date('Y-m-d H:i:s'),'UTC'));
							$this->db->set('IsProcessed',0);
							$this->db->set('LastProcessedDate','ProcessedDate', FALSE);
							$this->db->set('ProcessedDate',NULL);
							$this->db->update('Trans_SyncPool');
						}
					}
					else{
						$this->db->set('BranchCode',$branch->BranchId);
						$this->db->set('TableName',$table->TableName);
						$this->db->set('NotifiedDate',$this->convertDatetimeLocalToDatetimeX(date('Y-m-d H:i:s'),'UTC'));
						$this->db->set('IsProcessed',0);
						$this->db->insert('Trans_SyncPool');
					}
					
					if( ($errors = sqlsrv_errors() ) != null) {
						foreach( $errors as $error ) {
							$ERR_MSG.= $error[ 'message']."; ";
						}
					}
				}
			}
			
			$this->db->trans_complete(); //auto rollback jika ada query error
			if ($this->db->trans_status() === FALSE)
			{
				$msg["message"] = "FAILED";
				$msg["description"] = $ERR_MSG;
			}
			else
			{
				$msg["message"] = "SUCCESS";
				$msg["description"] = "";
			}
			return $msg;
		}
		
		function GetTableName($table){
			$table = str_replace(",","','",$table);
			$query = "SELECT DISTINCT ConfigValue as TableName FROM Cof_Sync WHERE ConfigValue IN('".$table."') AND IsActive=1 ORDER BY ConfigValue ";
			$res = $this->db->query($query);
			if($res->num_rows()>0){
				return $res->result();
			}
			else{
				return array();
			}
		}
		
		function GetBranchCode(){
			$query = "SELECT DISTINCT BranchId FROM Cof_Sync ORDER BY BranchId WHERE IsActive=1 ";
			$res = $this->db->query($query);
			if($res->num_rows()>0){
				return $res->result();
			}
			else{
				return array();
			}
		}
		
		function GetRequestSync($branch_id){
			$query = "
			SELECT b.ConfigValue, b.ConfigName, a.NotifiedDate
			FROM Trans_SyncPool a
			INNER JOIN (
				SELECT BranchId as BranchCode, ConfigName, ConfigValue, Level FROM Cof_Sync
				WHERE ConfigType='TABLE' AND IsActive=1 AND BranchId = '".$branch_id."'
			) b ON a.TableName = b.ConfigValue AND a.BranchCode = b.BranchCode
			WHERE IsProcessed=0 
			ORDER BY b.Level ASC
			";
			$res = $this->db->query($query);
			if($res->num_rows()>0){
				return $res->result();
			}
			else{
				return array();
			}
		}
		
		function UpdateRequestSync($branch_id, $tablename){
			$ERR_MSG = '';
			// $this->db->trans_begin();		
			$this->db->trans_start();		
			$this->db->where('BranchCode',$branch_id);
			$this->db->where('TableName',$tablename);
			$this->db->set('IsProcessed',1);
			$this->db->set('LastProcessedDate','ProcessedDate', FALSE);
			$this->db->set('ProcessedDate',$this->convertDatetimeLocalToDatetimeX(date('Y-m-d H:i:s'),'UTC'));
			$this->db->update('Trans_SyncPool');
			
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
			
			$this->db->trans_complete(); //auto rollback jika ada query error
			if ($this->db->trans_status() === FALSE)
			{
				$msg["message"] = "FAILED";
				$msg["description"] = $ERR_MSG;
			}
			else
			{
				$msg["message"] = "SUCCESS";
				$msg["description"] = "";
			}
			return $msg;
		}

		public function convertDatetimeLocalToDatetimeX($dt, $destTimezone="UTC")
		{			
			// get Local Timezone
			$local_tz = date_default_timezone_get(); 
			$dt = new DateTime($dt, new DateTimeZone($local_tz));			
			$dt->setTimezone(new DateTimeZone($destTimezone));

			// format the datetime to local time
			return($dt->format('Y-m-d H:i:s'));
		}
		
		function GetLogs($databaseId=''){
			$query = "SELECT TOP 20 * 
					  FROM Log_Activity 
					  WHERE module='MASTER B2B'";
			if($databaseId!=''){
				$query .= " AND TrxID LIKE '".$databaseId."'";
			}
			
			$query .= " ORDER BY LogDate DESC";
			$res = $this->db->query($query);
			if ($res->num_rows()>0){
				return $res->result();
			} else {
				return array();
			}
		}
		
		
		function InsertLogActivity($conn, $date){
			$ERR_MSG = '';		
			$this->db->trans_start();
			$this->db->set('LogDate',$date);
			$this->db->set('UserID',$_SESSION['logged_in']['username']);
			$this->db->set('Module','MASTER B2B');
			$this->db->set('TrxID',$conn->BranchId);
			$this->db->set('Remarks','ON PROGRESS');
			$this->db->insert('Log_Activity');
			
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
			
			$this->db->trans_complete(); //auto rollback jika ada query error
			if ($this->db->trans_status() === FALSE)
			{
				$msg["result"] = "FAILED";
				$msg["error"] = $ERR_MSG;
			}
			else
			{
				$msg["result"] = "SUCCESS";
				$msg["error"] = "";
			}
			return $msg;
		}
		
		function UpdateLogActivity($conn, $date){
			$ERR_MSG = '';		
			$this->db->trans_start();
			$this->db->where('LogDate',$date);
			$this->db->where('UserID',$_SESSION['logged_in']['username']);
			$this->db->where('Module','MASTER B2B');
			$this->db->where('TrxID',$conn->BranchId);
			$this->db->set('Remarks','FINISHED');
			$this->db->update('Log_Activity');
			
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
			
			$this->db->trans_complete(); //auto rollback jika ada query error
			if ($this->db->trans_status() === FALSE)
			{
				$msg["result"] = "FAILED";
				$msg["error"] = $ERR_MSG;
			}
			else
			{
				$msg["result"] = "SUCCESS";
				$msg["error"] = "";
			}
			return $msg;
		}
		
	}
?>
