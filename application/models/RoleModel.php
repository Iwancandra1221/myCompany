<?php
	Class RoleModel extends CI_Model
	{

		function getList(){
	 	 	$this->db->select('*');
		    $this->db->from('tb_role_hd');  
		    $this->db->order_by('role_id', 'ASC');
		    return $this->db->get()->result();
		}
		
		function getListbyMainBranch($mainbranch_role){
			$this->db->select('*');
			$this->db->from('tb_role_hd');  
			if ($mainbranch_role==0) {
				$this->db->where('isnull(mainbranch_role,0)<>1');
			}
			$this->db->order_by('role_id', 'ASC');
			return $this->db->get()->result();
	  }

		function get($kdrole){
	 	 	$this->db->select('*');
		    $this->db->from('tb_role_hd');
		    $this->db->where('role_id', $kdrole);
		    return $this->db->get()->result();
		}
		function getModuleDetail2($kdrole){
	 	 	$this->db->select('*');
		    $this->db->from('tb_modulemycompany_dt');
		    $this->db->where('role_id', $kdrole); 
		    return $this->db->get()->result();
		}
		function getModuleDetail($kdrole,$kdmodule){
	 	 	$this->db->select('*');
		    $this->db->from('tb_modulemycompany_dt');
		    $this->db->where('role_id', $kdrole);
		    $this->db->where('module_id', $kdmodule);
		    return $this->db->get()->row();
		}
		function getAllUserDetail($kdrole){
	 	 	$this->db->select('*');
		    $this->db->from('tb_user_dt');
		    $this->db->where('role_id', $kdrole); 
		    return $this->db->get()->result();
		}
		function getUserDetail($kdrole,$kduser){
	 	 	$this->db->select('*');
		    $this->db->from('tb_user_dt');
		    $this->db->where('role_id', $kdrole);
		    $this->db->where('UserId', $kduser);
		    return $this->db->get()->row();
		}
		function addData($data){
	   		$this->db->insert('tb_role_hd', $data);
		}
		function addDataUser($data){
	   		$this->db->insert('tb_user_dt', $data);
		}
		function updateData($data,$kdrole){
			$this->db->where('role_id', $kdrole);
	   		$this->db->update('tb_role_hd', $data);
		}

		function deleteData($kdrole){
   			$this->db->where('role_id', $kdrole);
  			$this->db->delete('tb_role_hd');
		}
		function deletealluserrole($kdrole){
   			$this->db->where('role_id', $kdrole);
  			$this->db->delete('tb_user_dt');
		}
		function deleteModuleDetail($kdrole){
   			$this->db->where('role_id', $kdrole);
  			$this->db->delete('tb_modulemycompany_dt');
		}
		function deleteuserrole($kdrole,$kduser)
		{	
			$this->db->where('role_id',$kdrole);
			$this->db->where('UserID',$kduser); 
			$this->db->delete('tb_user_dt');
		}

		function updateModuleDetail($data,$kdrole,$kdmodule){
			$this->db->where('role_id', $kdrole);
			$this->db->where('module_id', $kdmodule);
	   		$this->db->update('tb_modulemycompany_dt', $data);
		}

		function insertModuleDetail($data){
			$this->db->insert('tb_modulemycompany_dt', $data);
		}

		function GetRoleDefault()
		{
			$this->db->where('role_default',1);
			$res = $this->db->get('tb_role_hd');
			if($res->num_rows() > 0)
				return $res->row();
			else
				return null;
		}
		function getRoleAutoNumber(){
	 	 	$this->db->select('Top 1 SUBSTRING(role_id, 5, LEN(role_id))+1 AS role_id ');
		    $this->db->from('tb_role_hd'); 
		    $this->db->order_by('role_id', 'desc');
		    return $this->db->get()->row()->role_id;
		} 
		function getuseremail($roleId,$userid){ 
			$this->db->select('UserEmail');
		    $this->db->from('msuserhd'); 
			$this->db->where('AlternateID',$userid); 
		    $this->db->order_by('UserName', 'asc');
		    return $this->db->get()->row()->UserEmail;
		}
		function getRoleId($userid){
	 	 	$this->db->select('role_id');
		    $this->db->from('tb_user_dt');
			$this->db->where('UserID',$userid);  
		    return $this->db->get()->row()->role_id;
		}  

		function getListbyMainBranchwithRole($mainbranch_role,$user_role){
			$this->db->select('*');
			$this->db->from('tb_role_hd');  
			if ($mainbranch_role==0) {
				$this->db->where('isnull(mainbranch_role,0)<>1');
			} 
			if ($user_role <> "ROLE01")
           	{ 
  				$this->db->where_not_in('role_id', "ROLE01");
           	} 
			$this->db->order_by('role_id', 'ASC');
			return $this->db->get()->result();
	  }

		function getModuleListByRoleId($user_role){
			$str = "WITH RecursiveHierarchy AS ( 
					    SELECT
					        module_id,
					        module_name,
					        parent_module_id,
					        CAST(ROW_NUMBER() OVER (ORDER BY position) AS INT) AS position,
					        CAST(CAST(ROW_NUMBER() OVER (ORDER BY position) AS INT) AS VARCHAR(MAX)) AS HierarchyPath
					    FROM
					        tb_modulemycompany_hd
					    WHERE
					        parent_module_id = '' 
					    UNION ALL 
					    SELECT
					        e.module_id,
					        e.module_name,
					        e.parent_module_id,
					        rh.Position,
					        CAST(rh.HierarchyPath + '.' + CAST(CAST(ROW_NUMBER() OVER (ORDER BY e.position) AS INT) AS VARCHAR(MAX)) AS VARCHAR(MAX))
					    FROM
					        tb_modulemycompany_hd e
					    INNER JOIN
					        RecursiveHierarchy rh ON e.parent_module_id = rh.module_id
					)
					SELECT
					    RecursiveHierarchy.module_id,
					    module_name,
						case when  listmodule.can_create is null then 0 else listmodule.can_create end as can_create,
						case when  listmodule.can_read is null then 0 else listmodule.can_create end as can_read,
						case when  listmodule.can_update is null then 0 else listmodule.can_create end as can_update,
						case when  listmodule.can_print is null then 0 else listmodule.can_create end as can_print,
						case when  listmodule.can_delete is null then 0 else listmodule.can_create end as can_delete  
					FROM
					    RecursiveHierarchy
					left join  
					(
					select * from tb_modulemycompany_dt tb_module_mc_dt where role_id = '".$user_role."' 
					) listmodule
					on RecursiveHierarchy.module_id = listmodule.module_id 

					ORDER BY
					 HierarchyPath 
					";
			$res = $this->db->query($str);
			if ($res->num_rows()>0)
				return $res->result();
			else
				return array();
		}

	}
?>