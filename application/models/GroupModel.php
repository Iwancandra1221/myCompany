<?php
	Class GroupModel extends CI_Model
	{
		function GetList(){

		    $str = "SELECT * FROM Ms_Group";
			$res = $this->db->query($str);
			if ($res->num_rows()>0)
			    return $res->result();
			else
				return array();
		}
		function update($groupid,$IsActive)
		{
			$this->db->where('GroupID', $groupid);
			$this->db->set('IsActive', $IsActive);
			$this->db->set('UpdatedBy',$_SESSION["logged_in"]["useremail"]);
			$this->db->set('UpdatedDate',date('Y-m-d H:i:s'));
			$this->db->Update('Ms_Group');
		}
	}
?>