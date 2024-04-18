<?php 
class Datasource extends NS_Controller
{
	
	function __construct()
	{
		parent::__construct();		
		$this->load->model('UserModel');
	}

	public function GetCredential()
	{
		$data = array();
		$data["result"] = "SUCCESS";
		$data["uid"] = SQL_UID;
		$data["pwd"] = SQL_PWD;
		$data["error"] = "";

		$result = json_encode($data);
		header('HTTP/1.1: 200');
		header('Status: 200');
		header('Content-Length: '.strlen($result));
		exit($result);		
	}

	public function LoadDataTable($option, $cols, $firstcol, $idx, $tbl, $edit_by_link=0, $del_by_link=0, $namecol="")
	{
		$_module_id = $this->session->userdata('active_module');
		$_controller = $this->ModuleModel->Get($_module_id)->controller_name;

		$whr = '';
		$data = $this->GetData($cols,$idx,$tbl,$whr);
		$output = $data['output'];
		$datares = $data['datares'];
		if(!empty($datares))
		{
			$no_urut = 1;
			foreach($datares->result_array() as $row)
			{
				$r = array();
				foreach($cols as $c)
				{
					if ($edit_by_link == 0)
						$button_edit = ($this->session->userdata('can_edit'))? 
								"<button class='btnEdit' title='Edit' data='".$row[$idx]."' 
								nama='".(($namecol!="")? $row[$namecol] : $row[$idx])."'>
								<i class='fa fa-pencil'></i></button>":"";
					else if ($edit_by_link == 1)
						$button_edit = ($this->session->userdata('can_edit'))? 
								'<a href="'.site_url($_controller.'/Edit/'.md5($row[$idx])).'">
								<button class="btnEdit" title="Edit"><i class="fa fa-pencil"></i></button></a>' : '';
					else
						$button_edit = "";

					if ($del_by_link == 0)
						$button_del = ($this->session->userdata('can_delete'))? 
								'<button class="btnDelete" title="Delete" data="'.$row[$idx].'" 
								nama="'.(($namecol!="")? $row[$namecol] : $row[$idx]).'">
								<i class="fa fa-times"></i></button>':'';
					// else if ($del_by_link == 0)
					// 	$button_del = ($this->session->userdata('can_delete'))? '<a href="'.site_url($_controller.'/Delete/'.$row[$idx]).'"><button class="btnDelete" title="Delete"><i class="fa fa-times"></i></button></a>' : '';
					else
						$button_del = "";

					if($c == $firstcol)
					{
						$r[] = $button_edit.$button_del;
						
						if($c == 'no_urut')
							$r[] = $no_urut;
						else if($c=='server_source')
							$r[] = urldecode($row[$c]);
						else if (strtoupper($option) == "USERDIVISION" && $c=='UserDivisionID')
							$r[] = ($this->session->userdata('can_view'))? '<a href="'.site_url('UserDivision/View/'.$row[$idx]).
									 '" style="width:100%; display:inline-block;" >'.$row[$c].'</a>' : $row[$c];
						else if ($c != "hide_this_col")
							$r[] = $row[$c];
					}
					else if ($c == 'is_active')
						if($row[$c]=='1') $r[]='Y'; else $r[]='N';
					else if($c=='server_source')
						$r[] = urldecode($row[$c]);
					else if (strtoupper($c) == "CREATED_DATE")
						$r[] = date("d-M-Y H:i:s", strtotime($row[$c]));
					else if ($c != "hide_this_col")
						$r[] = $row[$c];
				}
				$output['aaData'][] = $r;
				$no_urut = $no_urut+1;
			}
		}

		echo json_encode($output);
	}
	
	public function Master($option, $edit_by_link=0, $del_by_link=0)
	{
		$namecol = "";

		if (strtoupper($option) == "BRANCH") {
			$cols = array("no_urut", "branch_id","branch_name","branch_code","is_active");
			$firstcol = "no_urut";
			$idx = "branch_id";
			$namecol = "branch_name";
			$tbl = "(select *, 0 as no_urut from tb_branch) as src";

		/*} else if (strtoupper($option) == "CITY") {
			$cols = array("no_urut", "city_id","city_name", "city_name_2", "branch_name", "is_active");
			$firstcol = "no_urut";
			$idx = "city_id";
			$namecol = "city_name_2";
			$tbl = "(select a.*, branch_name, 0 as no_urut from tb_city a inner join tb_branch b on a.branch_id=b.branch_id) as src";
		*/
		} else if (strtoupper($option) == "WORKGROUP") {
			$cols = array("no_urut", "workgroup_id", "workgroup_name", "city", "branch_name", "is_active");
			$firstcol = "no_urut";
			$idx = "workgroup_id";
			$namecol = "workgroup_name";
			$tbl = "(select a.*, branch_name, 0 as no_urut from tb_workgroup a inner join tb_branch b on a.branch_id=b.branch_id) as src";

		} else if (strtoupper($option) == "COMPANY") {
			$cols = array("CompanyID","CompanyName","is_active");
			$firstcol = "CompanyID";
			$idx = "CompanyID";
			$namecol = "CompanyName";
			$tbl = "(select company_id as CompanyID, company_name as CompanyName, is_active from tb_company) as src";

		} else if (strtoupper($option) == "DIVISION") {
			$cols = array("DivisionID","DivisionName","is_active");
			$firstcol = "DivisionID";
			$idx = "DivisionID";
			$namecol = "DivisionName";
			$tbl = "(select division_id as DivisionID, division_name as DivisionName, is_active from tb_division) src";

		} else if (strtoupper($option) == "GOLONGAN") {
			$cols = array("golongan_id","golongan_name","is_active");
			$firstcol = "golongan_id";
			$idx = "golongan_id";
			$namecol = "golongan_name";
			$tbl = "(select golongan_id, golongan_name, is_active from tb_golongan) src";

		} else if (strtoupper($option) == "DEPARTMENT") {
			$cols = array("DepartmentID","DepartmentName","IsActive");
			$firstcol = "DepartmentID";
			$idx = "DepartmentID";
			$tbl = "(select DepartmenID, Name as DepartmentName, IsActive from Ms_Department) src";

		} else if (strtoupper($option) == "MARITALSTATUS") {
			$cols = array("MaritalStatusID","MaritalStatus","is_active");
			$firstcol = "MaritalStatusID";
			$idx = "MaritalStatusID";
			$namecol = "MaritalStatus";
			$tbl = "(select marital_status_id as MaritalStatusID, marital_status as MaritalStatus, is_active from tb_marital_status) src";

		} else if (strtoupper($option) == "EDUCATIONLEVEL") {
			$cols = array("EducationLevelID","EducationLevel","is_active");
			$firstcol = "EducationLevelID";
			$idx = "EducationLevelID";
			$namecol = "EducationLevel";
			$tbl = "(select education_level_id as EducationLevelID, education_level as EducationLevel, is_active from tb_education_level) src";

		} else if (strtoupper($option) == "EMPLOYEELEVEL") {
			$cols = array("employee_level_id","employee_level","is_active");
			$firstcol = "employee_level_id";
			$idx = "employee_level_id";
			$namecol = "employee_level";
			$tbl = "(select employee_level_id, employee_level, is_active from tb_employee_level) src";

		} else if (strtoupper($option) == "EMPLOYEESTATUS") {
			$cols = array("EmployeeStatusID","EmployeeStatus","is_active");
			$firstcol = "EmployeeStatusID";
			$idx = "EmployeeStatusID";
			$tbl = "(select employee_status_id as EmployeeStatusID, employee_status as EmployeeStatus, is_active from tb_employee_status) src";

		} else if (strtoupper($option) == "RESIGNSTATUS") {
			$cols = array("ResignStatusID","ResignStatus","is_active");
			$firstcol = "ResignStatusID";
			$idx = "ResignStatusID";
			$tbl = "(select resign_status_id as ResignStatusID, resign_status as ResignStatus, is_active from tb_resign_status) src";

		} else if (strtoupper($option) == "JOBTITLE") {
			$cols = array("JobID","Job","is_active");
			$firstcol = "JobID";
			$idx = "JobID";
			$tbl = "(select job_title_id as JobID, job_title_name as Job, is_active from tb_job_title) src";

		} else if (strtoupper($option) == "DIVISIONHEAD") {
			$cols = array("DivisionHeadID","CompanyName","BranchName","DivisionName","HeadEmail","is_active");
			$firstcol = "DivisionHeadID";
			$idx = "DivisionHeadID";
			$tbl = "(select a.division_head_id as DivisionHeadID, b.company_name as CompanyName, c.branch_name as BranchName, 
						d.division_name as DivisionName, a.division_head as HeadEmail, a.is_active
					From tb_division_head a inner join tb_company b on a.company_id=b.company_id
						inner join tb_branch c on a.branch_id=c.branch_id
						inner join tb_division d on a.division_id=d.division_id
					) as src";

		} else if (strtoupper($option) == "DEPARTMENTHEAD") {
			$cols = array("DepartmentHeadID","CompanyName","BranchName","DivisionName","DepartmentName","HeadEmail","is_active");
			$firstcol = "DepartmentHeadID";
			$idx = "DepartmentHeadID";
			$tbl = "(select a.department_head_id as DepartmentHeadID, b.company_name as CompanyName, c.branch_name as BranchName,
						d.division_name as DivisionName, e.department_name as DepartmentName, a.department_head as HeadEmail, a.is_active
					From tb_department_head a inner join tb_company b on a.company_id=b.company_id
						inner join tb_branch c on a.branch_id=c.branch_id
						inner join tb_division d on a.division_id=d.division_id
						inner join tb_department e on a.department_id=e.department_id
					) as src";

		} else if (strtoupper($option) == "USERDIVISION") {
			$cols = array("UserDivisionID","UserDivisionName","WorkgroupName","Head", "request_approver", "is_active");
			$firstcol = "UserDivisionID";
			$idx = "UserDivisionID";

			$tbl = "(select UserDivisionID , UserDivisionName, Head, WorkgroupName, request_approver, is_active
						 From (
							 select a.user_division_id as UserDivisionID, a.user_division_name as UserDivisionName, 
								c.employee_name as Head, b.workgroup_name as WorkgroupName, d.employee_name as request_approver, a.is_active
							 From tb_user_division a inner join tb_workgroup b on a.workgroup_id=b.workgroup_id
							 left join tb_employee_hd c on a.user_division_head=c.UserEmail and isnull(a.user_division_head,'')<>''
							 left join tb_employee_hd d on a.request_approver=d.UserEmail and isnull(a.request_approver,'')<>''
							 where b.branch_id = (select branch_id from msuserhd where UserEmail = '".$this->session->userdata("user")."')
							 union
							 select a.user_division_id as UserDivisionID, a.user_division_name as UserDivisionName, 
								c.employee_name as Head, b.workgroup_name as WorkgroupName, d.employee_name as request_approver, a.is_active
							 From tb_user_division a inner join tb_workgroup b on a.workgroup_id=b.workgroup_id
							 left join tb_employee_hd c on a.user_division_head=c.UserEmail and isnull(a.user_division_head,'')<>''
							 left join tb_employee_hd d on a.request_approver=d.UserEmail and isnull(a.request_approver,'')<>''
							 where b.workgroup_id in (select workgroup_id from tb_user_workgroup_mapping where UserEmail = '".$this->session->userdata("user")."')
						 ) as GAB Order by WorkgroupName, UserDivisionName) src";
			
		} else if (strtoupper($option) == "BRANCHGROUP") {
			$cols = array("BranchGroupID","BranchGroupName","is_active");
			$firstcol = "BranchGroupID";
			$idx = "BranchGroupID";
			$tbl = "(select branch_group_id as BranchGroupID, branch_group_name as BranchGroupName, is_active as IsActive from tb_branch_group_hd) src";

		} else if (strtoupper($option) == "CONFIGCUTI") {
			$cols = array("LeaveConfigID","StartYear","ApproverLevel", "is_active");
			$firstcol = "LeaveConfigID";
			$idx = "LeaveConfigID";
			$tbl = "(SELECT a.config_cuti_id as LeaveConfigID, a.config_start_year as StartYear, b.employee_level as ApproverLevel, a.is_active
						FROM tb_config_cuti a inner join tb_employee_level b ON a.min_approver_level = b.employee_level_id
					) src";

		} else if (strtoupper($option) == "LATEPENALTY") {
			$cols = array("PenaltyID","StartYear","is_active");
			$firstcol = "PenaltyID";
			$idx = "PenaltyID";
			$tbl = "(select penalty_id as PenaltyID, start_year as StartYear, is_active from tb_penalty_hd) src";

		} else if (strtoupper($option) == "USERATTENDANCECONNECTION") {
			$cols = array("connection_id", "connection_name", "branch_name","server_source","database_name","is_active");
			$firstcol = "connection_id";
			$idx = "connection_id";

			$tbl = "(select connection_id, a.connection_name, b.branch_name, server_source, database_name, a.is_active 
					from tb_user_attendance_connection a inner join tb_branch b on a.branch_id=b.branch_id
					where (a.branch_id = (select branch_id from msuserhd where UserEmail = '".$this->session->userdata("user")."')
			 	 	or a.branch_id in (select branch_id from tb_user_workgroup_mapping x inner join tb_workgroup y on x.workgroup_id=y.workgroup_id 
			 	 	where x.UserEmail='".$this->session->userdata('user')."'))
					) src";

		} else if (strtoupper($option) == "MSCUTI") {
			$cols = array("cuti_id", "cuti_name", "days_given", "is_active");
			$firstcol = "cuti_id";
			$idx = "cuti_id";
			$tbl = "tb_ms_cuti";

		} else if (strtoupper($option) == "CUTIADJUSTMENT") {
			$cols = array("hide_this_col","employee_name", "branch_name", "year", "total_adjustment", "timestamp", "adjustment_note");
			$firstcol = "hide_this_col";

			$idx = "hide_this_col";

			$tbl = "(select adjustment_id as hide_this_col, b.employee_name, c.branch_name, a.year, 
					(case when a.total_adjustment>0 then '<font color=''red''>' else '<font color=''green''>' end)+'<b>'+cast(a.total_adjustment as varchar(3))+'</b></font>' as total_adjustment, 
					(a.created_by+'<br>'+convert(varchar(100),a.created_date,106)) as timestamp, a.adjustment_note
					from tb_cuti_adjustment a inner join tb_employee_hd b on a.UserEmail=b.UserEmail
					inner join tb_branch c on b.branch_id=c.branch_id
					where (b.branch_id = (select branch_id from msuserhd where UserEmail = '".$this->session->userdata("user")."')
			 	 	or b.branch_id in (select branch_id from tb_user_workgroup_mapping x inner join tb_workgroup y on x.workgroup_id=y.workgroup_id 
		 	 		where x.UserEmail='".$this->session->userdata("user")."'))
					) src";				
		}

		$this->LoadDataTable($option, $cols, $firstcol, $idx, $tbl, $edit_by_link, $del_by_link, $namecol);
	}


}
?>