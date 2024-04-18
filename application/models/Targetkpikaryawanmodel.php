<?php
Class Targetkpikaryawanmodel extends CI_Model{

	function editTblApproval($where,$data){
		$this->db->where($where);
		$this->db->update('TblApproval',$data);
		// $result = $this->db->affect_rows() > 0 ? true : false;
		$result=true;
		return $result;
	}
}
?>