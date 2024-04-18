<?php
class MsConfigRequestApprovalModel extends CI_Model
{
	public $ERRMSG="";
	public $ERRCODE=0;

	function __construct()
	{
		parent::__construct();		
		$CI = &get_instance();
	}
	function GetListEvent(){
		$qry = " SELECT DISTINCT ConfigValue FROM Ms_Config WHERE IsActive=1 and ConfigType = 'MASTER' and ConfigName = 'EVENT' ORDER BY ConfigValue ASC ";
		$res = $this->db->query($qry);
		if ($res->num_rows()>0) 
			return $res->result();
		else
			return array();
	}

	function GetListInfo(){
		$qry = " SELECT DISTINCT ConfigName FROM Ms_Config WHERE IsActive=1 and ConfigType = 'MASTER' ORDER BY ConfigName ASC ";
		$res = $this->db->query($qry);
		if ($res->num_rows()>0) 
			return $res->result();
		else
			return array();
	} 

	function GetListInfoDetail($id=''){
		$qry = " SELECT DISTINCT ConfigValue FROM Ms_Config WHERE IsActive=1 and ConfigType = 'MASTER' AND ConfigName = '".$id."' ORDER BY ConfigValue ASC ";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) 
			return $res->result();
		else
			return array();
	}
 
	function GetLevelSalesman(){
		$qry = " select ConfigValue as level_slsman from Ms_Config where ConfigType = 'USER POSITION' and ConfigName = 'POSITION NAME' ";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) 
			return $res->result();
		else
			return array();
	}
 
	function GetDivision(){
		$qry = " select distinct division from tb_salesman ";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) 
			return $res->result();
		else
			return array();
	}
 
	function GetConfigAll(){
		$qry = "
		SELECT * FROM Ms_ConfigApprovalHD ORDER BY EventId, AddInfo1, AddInfo2, AddInfo3, BranchID ";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}
 
	function GetConfigById($id='%'){
		$qry = "
		SELECT a.*
		FROM Ms_ConfigApprovalHD a
		WHERE ConfigID LIKE '".$id."'
		ORDER BY a.ConfigID ";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->row();
		} else {
			return array();
		}
	}

	function GetConfigDetailById($id=''){
		$qry = " SELECT * FROM Ms_ConfigApprovalDT  
				 WHERE ConfigID = '".$id."' ORDER BY ApprovalLevel, MinAmount ";
 
		$res = $this->db->query($qry);
		if ($res->num_rows()>0) 
			return $res->result();
		else
			return array();
	} 
     
	function Insert($post)
	{
		$ERR_MSG='';
		 
		$qry = " SELECT * FROM Ms_ConfigApprovalHD where ConfigID = 'a' ";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			$ERR_MSG .= 'Data sudah pernah diinput!';
		}
		else{ 
			$ConfigID = $this->getConfigId();

			$this->db->trans_start(); 
			$this->db->set('ConfigID',$ConfigID);
			$this->db->set('BranchID',$post['BranchId']);
			$this->db->set('IsActive', $post['IsActive']);
			$this->db->set('ActiveDate',date("Y-m-d H:i:s", strtotime($post['dp1'])));
			
			$this->db->set('CreatedBy',$_SESSION['logged_in']['username']);
			$this->db->set('CreatedDate',date('Y-m-d H:i:s'));
			$this->db->set('ModifiedBy',$_SESSION['logged_in']['username']);
			$this->db->set('ModifiedDate',date('Y-m-d H:i:s'));

			$this->db->set('EventID',$post['EventId']);
				if ($post['AddInfo1Name']=="")
		  		{ 
					$this->db->set('AddInfo1Name',"");
					$this->db->set('AddInfo1',"");
		  		}
		  		else
		  		{
		  			$this->db->set('AddInfo1Name',$post['AddInfo1Name']);
					$this->db->set('AddInfo1',$post['AddInfo1']);
		  		}
				if ($post['AddInfo2Name']=="")
		  		{ 
					$this->db->set('AddInfo2Name',"");
					$this->db->set('AddInfo2',"");
		  		}
		  		else
		  		{
		  			$this->db->set('AddInfo2Name',$post['AddInfo2Name']);
					$this->db->set('AddInfo2',$post['AddInfo2']);
		  		}
				if ($post['AddInfo3Name']=="")
		  		{ 
					$this->db->set('AddInfo3Name',"");
					$this->db->set('AddInfo3',"");
		  		}
		  		else
		  		{
		  			$this->db->set('AddInfo3Name',$post['AddInfo3Name']);
					$this->db->set('AddInfo3',$post['AddInfo3']);
		  		} 
 

			$this->db->insert('Ms_ConfigApprovalHD'); 

			$jum = count($post['alevel']);
			for($i=0; $i<$jum; $i++){  
				$this->db->set('ConfigID',$ConfigID); 
				$this->db->set('MinAmount',$post['amin'][$i]);
				$this->db->set('MaxAmount',$post['amax'][$i]);
				$this->db->set('ApprovalLevel',$post['alevel'][$i]);
				$this->db->set('ApprovalNeeded',$post['aneeded'][$i]);
				$this->db->set('ApprovalByPosition',$post['aposition'][$i]);
				$this->db->set('ApprovalByDivision',$post['adivision'][$i]);    
				$this->db->insert('Ms_ConfigApprovalDT'); 
			} 



			
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
			$this->db->trans_complete();
		}
		
		if($ERR_MSG==''){
			return 'SUKSES';
		}
		else{
			return $ERR_MSG;
		}
	}
	
	function Update($post)
	{
		$ERR_MSG='';
		$this->db->trans_start();   
		$this->db->where('ConfigID',$post['ConfigID']);
		$this->db->set('EventID',$post['EventId']);
		$this->db->set('BranchID',$post['BranchId']);

				if ($post['AddInfo1Name']=="")
		  		{ 
					$this->db->set('AddInfo1Name',"");
					$this->db->set('AddInfo1',"");
		  		}
		  		else
		  		{
		  			$this->db->set('AddInfo1Name',$post['AddInfo1Name']);
					$this->db->set('AddInfo1',$post['AddInfo1']);
		  		}
				if ($post['AddInfo2Name']=="")
		  		{ 
					$this->db->set('AddInfo2Name',"");
					$this->db->set('AddInfo2',"");
		  		}
		  		else
		  		{
		  			$this->db->set('AddInfo2Name',$post['AddInfo2Name']);
					$this->db->set('AddInfo2',$post['AddInfo2']);
		  		}
				if ($post['AddInfo3Name']=="")
		  		{ 
					$this->db->set('AddInfo3Name',"");
					$this->db->set('AddInfo3',"");
		  		}
		  		else
		  		{
		  			$this->db->set('AddInfo3Name',$post['AddInfo3Name']);
					$this->db->set('AddInfo3',$post['AddInfo3']);
		  		} 
 
		//$this->db->set('IsActive',$post['IsActive']);
		$this->db->set('ActiveDate',date("Y-m-d H:i:s", strtotime($post['dp1'])));
		$this->db->set('ModifiedBy',$_SESSION['logged_in']['username']);
		$this->db->set('ModifiedDate',date('Y-m-d H:i:s'));
		$this->db->update('Ms_ConfigApprovalHD');
		
		if( ($errors = sqlsrv_errors() ) != null) {
			foreach( $errors as $error ) {
				$ERR_MSG.= $error[ 'message']."; ";
			}
		}
		$this->db->trans_complete();
		if($ERR_MSG==''){
			return 'SUKSES';
		}
		else{
			return $ERR_MSG;
		}
	}	
 	 
	function InsertDetail($post)
	{
		$ERR_MSG='';
		$this->db->trans_start();  
				$this->db->set('ConfigID',$post['ConfigID']); 
				$this->db->set('MinAmount',$post['cell1'] );
				$this->db->set('MaxAmount',$post['cell2'] );
				$this->db->set('ApprovalLevel',$post['cell3'] );
				$this->db->set('ApprovalNeeded',$post['cell4'] );
				$this->db->set('ApprovalByPosition',$post['cell5'] );
				$this->db->set('ApprovalByDivision',$post['cell6'] );    
				$this->db->insert('Ms_ConfigApprovalDT'); 
		
		if( ($errors = sqlsrv_errors() ) != null) {
			foreach( $errors as $error ) {
				$ERR_MSG.= $error[ 'message']."; ";
			}
		}
		$this->db->trans_complete();
		if($ERR_MSG==''){
			return 'SUKSES';
		}
		else{
			return $ERR_MSG;
		}
	}	
	
	function DeleteConfig($post)
	{
		$ERR_MSG = '';
		$this->db->trans_start();  
		$this->db->where('ConfigID',$post['ConfigID']);
		$this->db->delete('Ms_ConfigApprovalHD');
		$this->db->where('ConfigID',$post['ConfigID']);
		$this->db->delete('Ms_ConfigApprovalDT');
		
		if( ($errors = sqlsrv_errors() ) != null) {
			foreach( $errors as $error ) {
				$ERR_MSG = $error['message']."; ";
			}
		}
		$this->db->trans_complete();
		if($ERR_MSG == ''){
			return 'SUKSES';
		}
		else{
			return $ERR_MSG;
		}
	}
				
	function DeleteDetail($post)
	{
		$ERR_MSG='';
		$this->db->trans_start();  
		$this->db->where('ConfigID',$post['ConfigID']);
		$this->db->delete('Ms_ConfigApprovalDT');
		
		if( ($errors = sqlsrv_errors() ) != null) {
			foreach( $errors as $error ) {
				$ERR_MSG.= $error[ 'message']."; ";
			}
		}
		$this->db->trans_complete();
		if($ERR_MSG==''){
			return 'SUKSES';
		}
		else{
			return $ERR_MSG;
		}
	}	

 	function getConfigId()
 	{
 		$qry = " DECLARE @Hasil as VARCHAR(50)
				DECLARE @MaxID as VARCHAR(50)
				IF NOT EXISTS(Select * From Ms_ConfigApprovalHD)
					SET @Hasil = 'CONFIG/' +  CONVERT(varchar(10),Year(GETDATE())) + '/' + SUBSTRING(CONVERT(nvarchar(6),getdate(), 112),5,2) + '/' +  '0001'
				ELSE	
				BEGIN
					SELECT @MaxID = MAX(ConfigID) From Ms_ConfigApprovalHD
					SET @MaxID = CAST(CAST(RIGHT(@MaxID,4) as INT)+1 as VARCHAR(10))
					WHILE LEN(@MaxID)<4
					BEGIN
						SET @MaxID = '0'+@MaxID 
					END
					SET @Hasil = 'CONFIG/' +  CONVERT(varchar(10),Year(GETDATE())) + '/' + SUBSTRING(CONVERT(nvarchar(6),getdate(), 112),5,2) + '/' + @MaxID 
				END
				SELECT @Hasil as ConfigID "; 

		$res = $this->db->query($qry); 
		return $res->row()->ConfigID; 
 	} 

 	function GetConfigApprovalPosition($kenaikan_cl,$partnertype,$divisi,$marking,$branchID){
 		$ConfigID = "";

 		$qry = " SELECT top 1 ConfigID, (case when a.BranchID='ALL' then 0 else 1 end) as NoUrut from Ms_ConfigApprovalHD a
				WHERE a.EventID = 'CREDIT LIMIT' 
				AND (a.AddInfo1 = '".$marking."' OR a.AddInfo2 = '".$marking."' OR a.AddInfo3 = '".$marking."')  
				AND (a.AddInfo1 = '".$divisi."' OR a.AddInfo2 = '".$divisi."' OR a.AddInfo3 = '".$divisi."')  
				AND (a.AddInfo1 = '".$partnertype."' OR a.AddInfo2 = '".$partnertype."' OR a.AddInfo3 = '".$partnertype."')  
				AND IsActive = 1 AND  a.BranchID = '".$branchID."' 
				AND getdate()>= ActiveDate  
				ORDER BY ActiveDate DESC, (case when a.BranchID='ALL' then 0 else 1 end) DESC";  
		$res2 = $this->db->query($qry);
		if ($res2->num_rows()>0) {
			$ConfigID = $res2->row()->ConfigID;
		} else {
	 		$qry = " SELECT top 1 ConfigID, (case when a.BranchID='ALL' then 0 else 1 end) as NoUrut from Ms_ConfigApprovalHD a
					WHERE a.EventID = 'CREDIT LIMIT' 
					AND (a.AddInfo1 = '".$marking."' OR a.AddInfo2 = '".$marking."' OR a.AddInfo3 = '".$marking."')  
					AND (a.AddInfo1 = '".$divisi."' OR a.AddInfo2 = '".$divisi."' OR a.AddInfo3 = '".$divisi."')  
					AND (a.AddInfo1 = '".$partnertype."' OR a.AddInfo2 = '".$partnertype."' OR a.AddInfo3 = '".$partnertype."')  
					AND IsActive = 1 AND  a.BranchID='ALL'
					AND getdate()>= ActiveDate  
					ORDER BY ActiveDate DESC, (case when a.BranchID='ALL' then 0 else 1 end) DESC";  
			// die($qry);
			$res = $this->db->query($qry);
			if ($res->num_rows()>0) 
			{
				$ConfigID = $res->row()->ConfigID;
			} else {
				return array();
			}
		} 

		$qry = " SELECT * FROM Ms_ConfigApprovalDT b 
			WHERE ConfigID = '".$ConfigID."' AND (".$kenaikan_cl." >= MinAmount or MinAmount = 0) 
			AND (".$kenaikan_cl." < MaxAmount OR MaxAmount = 0 Or MaxAMount = ".$kenaikan_cl.") "; 
		// die($qry);
		$res = $this->db->query($qry);
		if ($res->num_rows()>0) 
			return $res->result();
		else
			return array();

	}
}
?>
