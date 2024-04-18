<?php
	Class MasterDbModel extends CI_Model
	{
		function __construct()
		{
			parent::__construct();
		}

		function getList($branchid = '', $orderBy=''){
			$qry = " Select DatabaseId, BranchId, NamaDb, LocationCode, AlamatWebService,AlamatWebServiceJava, [Server], [Database], 
					 Created_Time, Created_By, Updated_Time, Updated_By, isnull(DatabaseType,'OFFICE') as DatabaseType
					 From MsDatabase Where 1=1 ";
			// if ($_SESSION["branchID"]=="JKT") { 
			    // if($branchid != '' && $branchid!= "JKT"){
			    	// $qry.= " and BranchId='".$branchid."' ";
			    // }    
			// } else {
				// $qry.= " and BranchId='".$_SESSION["branchID"]."' ";
			// }
		    // if ($orderBy=="") {
			    // $qry.= " order by BranchId, NamaDb, [Server], [Database]";
			// } else {
				// $qry.= " order by ".$orderBy;
			// }
		    //die($qry);
		    $res = $this->db->query($qry);

		    if ($res->num_rows()>0) 
		    	return $res->result();
		    else
		    	return array();
		}

		function getListByDatabaseType($dbtype = '', $orderBy=''){
			// die (json_encode($_SESSION));
			$qry = " Select DatabaseId, BranchId, NamaDb, LocationCode, AlamatWebService,AlamatWebServiceJava, [Server], [Database], 
					 Created_Time, Created_By, Updated_Time, Updated_By, isnull(DatabaseType,'OFFICE') as DatabaseType
					 From MsDatabase Where 1=1 ";
			if ($_SESSION["logged_in"]["branch_id"]!="JKT") { 
				$qry.= " and BranchId='".$_SESSION["logged_in"]["branch_id"]."' ";
			}
			if ($dbtype!="") { 
				$qry.= " and DatabaseType in ('".$dbtype."') ";
			}
		    if ($orderBy=="") {
			    $qry.= " order by BranchId, NamaDb, [Server], [Database]";
			} else {
				$qry.= " order by ".$orderBy;
			}
		    //die($qry);
		    $res = $this->db->query($qry);

		    if ($res->num_rows()>0) 
		    	return $res->result();
		    else
		    	return array();
		}

		function getListForExport(){
			$qry = " Select DatabaseId, BranchId, NamaDb, AlamatWebService,AlamatWebServiceJava, [Server], [Database], 
					 Created_Time, Created_By, Updated_Time, Updated_By, isnull(DatabaseType,'OFFICE') as DatabaseType
					 From MsDatabase Where 1=1 ";
	    	$qry.= " and (BranchId<>'JKT')";
		    $qry.= " order by BranchId, NamaDb, [Server], [Database]";

		    //die($qry);
		    $res = $this->db->query($qry);

		    if ($res->num_rows()>0) 
		    	return $res->result();
		    else
		    	return array();
		}

		function get($dataid){
			$res = $this->db->query("Select DatabaseId, BranchId, NamaDb, AlamatWebService,AlamatWebServiceJava, [Server], [Database], DatabaseType, LocationCode ,Created_Time, Created_By, Updated_Time, Updated_By
		    						From MsDatabase Where DatabaseId=".$dataid);

		    if ($res->num_rows()>0)
		    	return $res->row();
		    else
		    	return null;
		}

		function getBhaktiPusat(){
			$res = $this->db->query("Select DatabaseId, BranchId, NamaDb, AlamatWebService,AlamatWebServiceJava, [Server], [Database], DatabaseType,
		    						Created_Time, Created_By, Updated_Time, Updated_By
		    						From MsDatabase 
		    						Where BranchId='JKT' and NamaDb='JAKARTA'");

		    if ($res->num_rows()>0)
		    	return $res->row();
		    else
		    	return null;
		}

		function getByBranchId($dataid){
			$str = "";

			$str = "Select DatabaseId, BranchId, NamaDb, AlamatWebService,AlamatWebServiceJava, [Server], [Database], DatabaseType,
				Created_Time, Created_By, Updated_Time, Updated_By
				From MsDatabase Where BranchId='".$dataid."'";
			// die($str);
			$res = $this->db->query($str);
		    if ($res->num_rows()>0)
		    	return $res->row();
		    else
		    	return null;
		}

		function getByLocationCode($dataid){
			$str = "";

			$str = "Select DatabaseId, BranchId, NamaDb, AlamatWebService,AlamatWebServiceJava, [Server], [Database], DatabaseType,
				Created_Time, Created_By, Updated_Time, Updated_By
				From MsDatabase Where LocationCode='".$dataid."' OR BranchId='".$dataid."'";
			// die($str);
			$res = $this->db->query($str);
		    if ($res->num_rows()>0)
		    	return $res->row();
		    else
		    	return null;
		}

		function addData($data){

			$ERR_MSG='';
			
			$qry = " SELECT * from MsDatabase where LocationCode = '".$data["LocationCode"]."' and DatabaseType not in ('WAREHOUSE','OTHER') and DatabaseType = '".$data["DatabaseType"]."'";

			$res = $this->db->query($qry);
			if ($res->num_rows()>0) {
				$ERR_MSG .= 'Data sudah pernah diinput!';
			}
			else{
				$this->db->trans_start();
					 
				$this->db->set("BranchId", $data["BranchId"]);
				$this->db->set('NamaDb', $data["NamaDb"]);
				$this->db->set('AlamatWebService', $data["AlamatWebService"]);
				$this->db->set('AlamatWebServiceJava', $data["AlamatWebServiceJava"]);
				$this->db->set('[Server]', $data["Server"]);
				$this->db->set('[Database]', $data["Database"]);
				$this->db->set('DatabaseType', $data["DatabaseType"]);
				$this->db->set('LocationCode', $data["LocationCode"]);
				$this->db->set('Created_By', $data["Created_By"]);
				$this->db->set('Created_Time', date('Y-m-d H:i:s'));
		   		$this->db->insert('MsDatabase'); 
				
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

		function updateData($data,$id){

			$ERR_MSG='';
			
			$qry = " SELECT * from MsDatabase where LocationCode = '".$data["LocationCode"]."' and DatabaseType not in ('WAREHOUSE','OTHER') and DatabaseType = '".$data["DatabaseType"]."' and DatabaseId not in ('".$id."') ";

			$res = $this->db->query($qry);
			if ($res->num_rows()>0) {
				$ERR_MSG .= 'Data sudah pernah diinput!';
			}
			else{
				$this->db->trans_start(); 

				$this->db->where('DatabaseId', $id);
				$this->db->set("BranchId", $data["BranchId"]);
				$this->db->set('NamaDb', $data["NamaDb"]);
				$this->db->set('AlamatWebService', $data["AlamatWebService"]);
				$this->db->set('AlamatWebServiceJava', $data["AlamatWebServiceJava"]);
				$this->db->set('[Server]', $data["Server"]);
				$this->db->set('[Database]', $data["Database"]);
				$this->db->set('DatabaseType', $data["DatabaseType"]);
				$this->db->set('LocationCode', $data["LocationCode"]);
				$this->db->set('Updated_By', $data["Updated_By"]);
				$this->db->set('Updated_Time', date('Y-m-d H:i:s'));
		   		$this->db->update('MsDatabase');
				
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

		function deleteData($dataid){
   			$this->db->where('DatabaseId', $dataid);
  			$this->db->delete('MsDatabase');
		}

		function getBranches(){
		    $res = $this->db->query("Select BranchID as branch_id, BranchCode as branch_code, BranchName as branch_name, BranchHead as branch_head, IsActive as is_active From Ms_Branch order by BranchName");
		    if ($res->num_rows()>0)
			    return $res->result();
			else
				return array();
		}

		function getConArray($var){
	        $temp['row'] = $this->get($var);

	        $data['config'] = array( 
	        'hostname' => $temp['row'][0]->Server,
			'username' => SQL_UID,
			'password' => SQL_PWD,
			'database' => $temp['row'][0]->Database,
			'dbdriver' => 'sqlsrv',
			'dbprefix' => '',
			'pconnect' => FALSE,
			'db_debug' => (ENVIRONMENT !== 'production')
			);
			return $data;
		}

		function GetConnection($lok="")
		{
			$qry = "Select * From MsDatabase Where LocationCode  = '".$lok."'  and DatabaseType = 'OFFICE'  order by NamaDb "; 
			$res = $this->db->query($qry);
			if ($res->num_rows()>0)
				return $res->result();
			else
				return array();
		}

	}
?>