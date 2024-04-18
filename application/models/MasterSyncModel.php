 <?php
	Class MasterSyncModel extends CI_Model
	{
		function __construct()
		{
			parent::__construct();
		}

		function getList($branchid = '', $orderBy=''){
			$qry = " Select * From Cof_Sync Where 1=1 ";
			// if ($_SESSION["branchID"]=="JKT") { 
			    // if($branchid != '' && $branchid!= "JKT"){
			    	// $qry.= " and BranchId='".$branchid."' ";
			    // }    
			// } else {
				// $qry.= " and BranchId='".$_SESSION["branchID"]."' ";
			// }
		    if ($orderBy=="") {
			    $qry.= " order by ConfigType,BranchId, ConfigName";
			} else {
				$qry.= " order by ".$orderBy;
			}
		    // die($qry);
		    $res = $this->db->query($qry);

		    if ($res->num_rows()>0) 
		    	return $res->result();
		    else
		    	return array();
		}

		function get($ConfigId='%',$ConfigName='%'){
		
			$ConfigId = ($ConfigId=='') ? '%': $ConfigId;
			$ConfigName = ($ConfigName=='') ? '%': $ConfigName;
			
			$q = "Select * From Cof_Sync Where ConfigId LIKE '".$ConfigId."' AND ConfigName LIKE '".$ConfigName."'";
			// die($q);
			$res = $this->db->query($q);
		    if ($res->num_rows()>0)
		    	return $res->result();
		    else
		    	return null;
		}
		
		function addData($data){
			$this->db->trans_start();
			for($i=0;$i<count($data['BranchId']);$i++){
				$this->db->set('ConfigType', $data["ConfigType"]);
				$this->db->set("BranchId", $data['BranchId'][$i]);
				$this->db->set('ConfigName', $data["ConfigName"]);
				$this->db->set('ConfigValue', $data["ConfigValue"]);
				$this->db->set('Level', $data["Level"]);
				$this->db->set('IsActive', $data["IsActive"][$i]);
				$this->db->set('CreatedBy', $data["CreatedBy"]);
				$this->db->set('CreatedDate', date('Y-m-d H:i:s'));
				$this->db->insert('Cof_Sync');
			}
			$this->db->trans_complete();
			if ($this->db->trans_status() === TRUE)
			{
				return true;
			}
			else return false;
		}

		function updateData($data){
		
			$this->db->trans_start();
			for($i=0;$i<count($data['BranchId']);$i++){
				$query = "SELECT * FROM Cof_Sync WHERE ConfigName='".$data["ConfigName"]."' AND BranchId='".$data['BranchId'][$i]."'";
				$res = $this->db->query($query);
				if ($res->num_rows()>0){
					$this->db->where('ConfigName', $data["ConfigName"]);
					$this->db->where("BranchId", $data['BranchId'][$i]);
					$this->db->set('ConfigValue', $data["ConfigValue"]);
					$this->db->set('Level', $data["Level"]);
					$this->db->set('IsActive', $data["IsActive"][$i]);
					$this->db->set('ModifiedBy', $data["ModifiedBy"]);
					$this->db->set('ModifiedDate', date('Y-m-d H:i:s'));
					$this->db->update('Cof_Sync');
				}
				else{
					$this->db->set('ConfigType', $data["ConfigType"]);
					$this->db->set("BranchId", $data['BranchId'][$i]);
					$this->db->set('ConfigName', $data["ConfigName"]);
					$this->db->set('ConfigValue', $data["ConfigValue"]);
					$this->db->set('Level', $data["Level"]);
					$this->db->set('IsActive', $data["IsActive"][$i]);
					$this->db->set('CreatedBy', $data["ModifiedBy"]);
					$this->db->set('CreatedDate', date('Y-m-d H:i:s'));
					$this->db->insert('Cof_Sync');
				}
			}
			
			$this->db->trans_complete();
			if ($this->db->trans_status() === TRUE)
			{
				return true;
			}
			else return false;
		}
		function updatev2($data,$where){
			$this->db->trans_start();
			$this->db->where($where);
			$this->db->update("Cof_Sync",$data);

			$result = $this->db->trans_status();
			if ( $result === TRUE){
				$this->db->trans_commit();
			}
			else{
				$this->db->trans_rollback();
			}
			return $result;
		}
		function update($data,$ConfigId){
			$this->db->trans_start();
			
			if($data["ConfigType"]=='CONFIG' && $data["BranchId"]=='ALL'){
				$this->db->where('ConfigName', $data["ConfigName"]);
			}
			else{
				$this->db->where('ConfigId', $ConfigId);
				$this->db->set("BranchId", $data["BranchId"]);
				$this->db->set('ConfigName', $data["ConfigName"]);
			}
			
			$this->db->set('ConfigValue', $data["ConfigValue"]);
			$this->db->set('Level', $data["Level"]);
			$this->db->set('IsActive', $data["IsActive"]);
			$this->db->set('ModifiedBy', $data["ModifiedBy"]);
			$this->db->set('ModifiedDate', date('Y-m-d H:i:s'));
	   		$this->db->update('Cof_Sync');
			// die($this->db->last_query());
			$this->db->trans_complete();
			
			if ($this->db->trans_status() === TRUE)
			{
				return true;
			}
			else return false;
		}

		function deleteData($ConfigId){
			$this->db->trans_start();
   			$this->db->where('ConfigId', $ConfigId);
  			$this->db->delete('Cof_Sync');
			$this->db->trans_complete(); 
				
			if ($this->db->trans_status() === TRUE)
			{
				return true;
			}
			else return false;
		}

	}
?>