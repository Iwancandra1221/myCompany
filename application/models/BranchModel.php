<?php
class BranchModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function Get($data)
	{
		
		$this->db->where('BranchID',$data);
		$res = $this->db->get('Ms_Branch');
		if($res->num_rows() > 0)
			return $res->row();
		else
			return null;
	}
	function insert($data){
		$this->db->trans_start();

		$this->db->insert('Ms_Branch',$data);
		
		$result = $this->db->trans_status();
		if($result) $this->db->trans_complete();
		else $this->db->trans_rollback();

		return $result;
		
	}
	function update($data,$where){
		$this->db->trans_start();

		$this->db->where($where);
		$this->db->update('Ms_Branch',$data);

		$result = $this->db->trans_status();
		if($result) $this->db->trans_complete();
		else $this->db->trans_rollback();

		return $result;
	}

	function GetList()
	{
		$res = $this->db->query("select * from Ms_Branch Where IsActive=1 Order by BranchName");

		if($res->num_rows() > 0)
			return $res->result();
		else
			return array();
	}

	function Gets()
	{
		$res = $this->db->query("select * from Ms_Branch 
			where (BranchID = (select branch_id from msuserhd where UserEmail = '".$this->session->userdata('user')."')
		 	 	or BranchID in (select branch_id from tb_user_city_mapping x inner join tb_city y on x.city_id=y.city_id 
		 	 		where x.UserEmail='".$this->session->userdata('user')."')) 
			Order by BranchName");

		if($res->num_rows() > 0)
			return $res->result();
		else
			return array();
	}

	function GetsByUser($useremail)
	{
		$q = "Select branch_id, branch_name From 
			(
			  Select a.branch_id, b.BranchName as branch_name 
			  From msuserhd a inner join Ms_Branch b on a.branch_id=b.BranchID
			  where a.UserEmail = '".$useremail."' 
			  union 
			  Select y.BranchID, z.BranchName
			  from tb_user_workgroup_mapping x inner join Ms_Group y on x.workgroup_id=y.GroupID
			  inner join Ms_Branch z on y.BranchID=z.BranchID 
			  where x.UserEmail = '".$useremail."'
			) Gab order by [branch_name]";
		$res = $this->db->query($q);
		if($res->num_rows() > 0)
			return $res->result();
		else
			return array();
	}

	function CheckDelete($data)
	{

		$res2 = $this->db->query("select * from msuserhd where branch_id = '".$data."'");
		if($res2->num_rows()>0) return false;
		
	 	return true;	

	}

	function GetListGroup()
	{
		$res = $this->db->query("select * from Ms_Group Where IsActive=1 Order by [Name]");

		if($res->num_rows() > 0)
			return $res->result();
		else
			return array();
	}

	function GetGroup($GroupID="")
	{
		$res = $this->db->query("select * from Ms_Group Where GroupID='".$GroupID."' Order by [Name]");

		if($res->num_rows() > 0)
			return $res->row();
		else
			return null;
	}

	function GetBranchHead($branch)
	{
		$q = "select TOP 1 b.* 
			from ms_branch a inner join msuserhd b on a.BranchHead=b.AlternateID 
			where a.BranchID='".$branch."'";
		$res = $this->db->query($q);
		if($res->num_rows() > 0)
			return $res->row();
		else
			return null;
	}
}
?>
