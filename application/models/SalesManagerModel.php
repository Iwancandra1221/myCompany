<?php
	Class SalesManagerModel extends CI_Model
	{
		function GetList(){
			$str = "Select a.salesman_id, a.userid, a.[useremail],a.[kd_slsman],a.[nm_slsman],a.[level_slsman],
						isnull(a.[branch_id],'ALL') as branch_id,isnull(a.[database_id],0) as database_id, 
						a.[division],a.[email_address],isnull(a.[mobile],'') as mobile,a.[create_date],a.[create_by],a.[edit_date],a.[edit_by],
						isnull(b.UserName, isnull(a.nm_slsman,'')) as employee_name
					From tb_salesman a left join msuserhd b on a.userid=b.AlternateID
					where a.level_slsman in ('MARKETING', 'BRAND MANAGER', 'GENERAL MANAGER', 'SALES HEAD','SALES MANAGER', 'SERVICE MANAGER')
					order by a.division";
		    $res = $this->db->query($str);
		    if ($res->num_rows()>0) 
		    	return $res->result();
		    else
		    	return array();
		}
		
		function Insert($params) {
			// $res = $this->db->query($str);
			
			$ERR_MSG = '';
			$str = "select ISNULL(max(salesman_id),0)+1 as salesman_id from tb_salesman";
			$res = $this->db->query($str);
			$salesman_id = $res->row()->salesman_id;
			
			// $str = "insert into tb_salesman (branch_id, userid, division, useremail, 
						// email_address, mobile, nm_slsman, level_slsman, 
						// create_by, create_date, salesman_id) 
					// values('".$params["BranchID"]."', '".$params["UserID"]."', '".htmlspecialchars_decode($params["DivisionID"])."','".$params["EmployeeID"]."',
						// '".$params["EmailAddress"]."', '".$params["Mobile"]."', '".$params["EmployeeName"]."', '".$params["LevelSalesman"]."', 
						// '".$_SESSION["logged_in"]["useremail"]."', '".date('Y-m-d H:i:s')."',".$salesman_id.")";
			// die($str);
			
			$this->db->trans_begin();
			$this->db->set('branch_id',$params["BranchID"]);
			$this->db->set('userid',$params["UserID"]);
			$this->db->set('division',htmlspecialchars_decode($params["DivisionID"]));
			$this->db->set('useremail',$params["EmployeeID"]);
			$this->db->set('email_address',$params["EmailAddress"]);
			$this->db->set('mobile',$params["Mobile"]);
			$this->db->set('nm_slsman',$params["EmployeeName"]);
			$this->db->set('level_slsman',$params["LevelSalesman"]);
			$this->db->set('create_by',$_SESSION["logged_in"]["useremail"]);
			$this->db->set('create_date',date('Y-m-d H:i:s'));
			$this->db->set('salesman_id',$salesman_id);
			$this->db->set('is_active',1);
			$this->db->insert('tb_salesman');
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
			$this->db->trans_complete();
			
			if($ERR_MSG==''){
				$result['result'] = 'sukses';
				$result['error'] = '';
				$result['salesman_id'] = $salesman_id;
				return $result;
			}
			else{
				$result['result'] = 'gagal';
				$result['error'] = $ERR_MSG;
				return $result;
			}
		}

		function Update($params) {
			// $str = "update tb_salesman
					// set userid='".$params["UserID"]."', nm_slsman = '".$params["EmployeeName"]."',
						// useremail='".$params["EmployeeID"]."', email_address='".$params["EmailAddress"]."', mobile='".$params["Mobile"]."',
						// edit_by = '".$_SESSION["logged_in"]["useremail"]."', edit_date='".date('Y-m-d H:i:s')."',
						// branch_id='".$params["BranchID"]."'
					// where salesman_id='".$params["salesman_id"]."'";
			// // die($str);				
			
			$ERR_MSG = '';
			$this->db->trans_start();
			$this->db->where('salesman_id',$params["salesman_id"]);
			$this->db->set('userid',$params["UserID"]);
			$this->db->set('division',htmlspecialchars_decode($params["DivisionID"]));
			$this->db->set('nm_slsman',$params["EmployeeName"]);
			$this->db->set('level_slsman',$params["LevelSalesman"]);
			$this->db->set('useremail',$params["EmployeeID"]);
			$this->db->set('email_address',$params["EmailAddress"]);
			$this->db->set('mobile',$params["Mobile"]);
			$this->db->set('edit_by',$_SESSION["logged_in"]["useremail"]);
			$this->db->set('edit_date',date('Y-m-d H:i:s'));
			$this->db->set('branch_id',$params["BranchID"]);
			$this->db->update('tb_salesman');
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
			$this->db->trans_complete();
			
			if($ERR_MSG==''){
				$result['result'] = 'sukses';
				$result['error'] = '';
				return $result;
			}
			else{
				$result['result'] = 'gagal';
				$result['error'] = $ERR_MSG;
				return $result;
			}

		}

		function Delete($params) {
			// $str = "delete from tb_salesman
					// where salesman_id='".$params["salesman_id"]."'";
			// $res = $this->db->query($str);
			$ERR_MSG = '';
			$this->db->trans_start();
			$this->db->where('salesman_id',$params["salesman_id"]);
			$this->db->delete('tb_salesman');
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
			// echo $this->db->last_query(); die;
			$this->db->trans_complete();
			
			if($ERR_MSG==''){
				$result['result'] = 'sukses';
				$result['error'] = '';
				return $result;
			}
			else{
				$result['result'] = 'gagal';
				$result['error'] = $ERR_MSG;
				return $result;
			}				
		}

		/*function GetSalesManagers($where){
			$this->db->select("a.*, b.UserName as employee_name, b.Email as email");
			$this->db->join("msuserhd b","a.useremail=b.UserEmail","inner");
			$this->db->where($where);
			$res = $this->db->get('tb_salesman a');
		    if ($res->num_rows()>0) 
		    	return $res->row();
		    else
		    	return null;
		}*/

		function GetGM(){
			$str = "Select a.*, b.UserName as employee_name, b.Email as email 
					From tb_salesman a inner join msuserhd b on a.useremail=b.UserEmail
					where a.level_slsman='GENERAL MANAGER'";
		    $res = $this->db->query($str);
		    if ($res->num_rows()>0) 
		    	return $res->row();
		    else
		    	return null;
		}

		function GetManagers($posisi="", $divisi=""){
			$str = "Select a.*, b.UserName as employee_name, b.Email as email 
					From tb_salesman a inner join msuserhd b on a.useremail=b.UserEmail
					where (a.level_slsman in ('".$posisi."') or ('".$divisi."'='ALL' or a.division='".$divisi."'))
					order by a.division";
		    $res = $this->db->query($str);
		    if ($res->num_rows()>0) 
		    	return $res->result();
		    else
		    	return array();
		}

		function GetBrandManagers(){
			$str = "Select a.*, b.UserName as employee_name, b.Email as email 
					From tb_salesman a inner join msuserhd b on a.useremail=b.UserEmail
					where a.level_slsman in ('BRAND MANAGER')
					order by a.division";
		    $res = $this->db->query($str);
		    if ($res->num_rows()>0) 
		    	return $res->result();
		    else
		    	return array();
		}

		function GetSalesManager(){
			$str = "Select a.*, b.UserName as employee_name, b.Email as email 
					From tb_salesman a inner join msuserhd b on a.useremail=b.UserEmail
					where a.level_slsman in ('SALES MANAGER')
					order by a.division";
		    $res = $this->db->query($str);
		    if ($res->num_rows()>0) 
		    	return $res->result();
		    else
		    	return array();
		}

		function GetBrandManagersByDivisi($divisi=""){
			$str = "Select a.*, b.UserName as employee_name, b.Email as email 
					From tb_salesman a with(nolock) inner join msuserhd b with(nolock) on a.useremail=b.UserEmail
					where a.level_slsman in ('BRAND MANAGER')
					and replace(a.division,'&','') = '".str_replace('&','', $divisi)."'
					order by a.division";
			// echo($str."<br>");
		    $res = $this->db->query($str);
		    if ($res->num_rows()>0) {
		    	// die(json_encode($res->result()));
		    	return $res->result();
		    } else {
		    	// die("ga nemu");
		    	return array();
		    }
		}		

		function GetGeneralManager(){
			$str = "Select a.*, b.UserName as employee_name, b.Email as email 
					From tb_salesman a inner join msuserhd b on a.useremail=b.UserEmail
					where a.level_slsman in ('GENERAL MANAGER') AND a.division = 'SALES'
					order by a.division";
		    $res = $this->db->query($str);
		    if ($res->num_rows()>0) 
		    	return $res->result();
		    else
		    	return array();
		}

		function GetKajul($wil=""){
			$str = "Select a.*, b.UserName as employee_name, b.UserEmail as email 
					From tb_salesman a inner join msuserhd b on a.useremail=b.UserEmail or (a.UserID=b.AlternateID and isnull(b.AlternateID,'0')<>'0')
					where a.level_slsman in ('SALES HEAD') and a.division='".$wil."'";
			//die($str);
		    $res = $this->db->query($str);
		    if ($res->num_rows()>0) 
		    	return $res->row();
		    else
		    	return null;
		}

		function GetListApv($listapv){
			$str = "Select a.*, b.UserName as employee_name, b.Email as email 
					From tb_salesman a inner join msuserhd b on a.useremail=b.UserEmail
					where a.level_slsman in (".$listapv.")
					order by a.division";
		    $res = $this->db->query($str);
		    if ($res->num_rows()>0) 
		    	return $res->result();
		    else
		    	return array();
		}

		function GetListKacab($branchid){
			$str = "select b.UserName as employee_name,b.Email as email,b.UserEmail as useremail , b.Whatsapp as mobile
					from Ms_Branch a
					Left join msuserhd b
					on a.BranchHead = b.AlternateID
					where 
					BranchID = '".$branchid."'";
		    $res = $this->db->query($str);
		    if ($res->num_rows()>0) 
		    	return $res->result();
		    else
		    	return array();
		}

	}
?>