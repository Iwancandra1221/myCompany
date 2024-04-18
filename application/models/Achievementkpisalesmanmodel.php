<?php
Class Achievementkpisalesmanmodel extends CI_Model{

	function editTblApproval($where,$data){
		$this->db->where($where);
		$this->db->update('TblApproval',$data);
		// echo $this->db->last_query()."<br><br>";
		// $result = $this->db->affect_rows() > 0 ? true : false;
		$result=true;
		return $result;
	}
	
	function getNextPriority($data){
		$query = "SELECT TOP 1 * FROM TblApproval WHERE RequestNo='".$data['no_request']."' AND ApprovalStatus='UNPROCESSED' AND IsEmailed=0 ORDER BY Priority ASC";
		// echo $query."<br><br>";
		$res = $this->db->query($query);
		if($res->num_rows()>0){
			$query = "SELECT TOP 1 * FROM TblApproval WHERE RequestNo='".$data['no_request']."' AND ApprovedByEmail='".$data['app_by']."' ";
			// echo $query."<br><br>";
			$res = $this->db->query($query);
			if($res->num_rows()>0){
				return $res->row();
			}
			else{
				return null;
			}
		}
		else{
			return null;
		}
	}
}
?>