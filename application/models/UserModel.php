<?php
	Class UserModel extends CI_Model
	{
		//Catatan 2023 : 
		//msuserhd ditambahkan kolom UserID,
		//penggunaan kolom AlternateID akan digantikan dengan UserID 
		function __construct()
		{
			parent::__construct();
			ini_set("memory_limit","64M");
		}


		function GetLocalUserID($EmpID="0") {
			$str = "SELECT UserEmail FROM msuserhd WHERE UserID='".$EmpID."'";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return $res->row()->UserEmail;
			} else {
				return "";
			}
		}

		function Get($userid) {
			$str = "SELECT a.*, (case when a.UserID is null then '0' else a.UserID end) as USERID, 
					(case when b.[Name] is null then '' else b.Name end) as GroupName, 
					(case when a.branch_id is null then '' else a.branch_id end) as BranchID
					FROM msuserhd a left join Ms_Group b on a.GroupID=b.GroupID 
					WHERE ISNULL(a.UserID,'')<>'' AND a.UserID='".$userid."'
					ORDER BY IsActive DESC
					";

			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return $res->row();
			} else {
				return null;
			}				
		}

		function login2($where){
			$this->db->where($where);
			$result = $this->db->get('msuserhd')->row();
			return $result;
		}

		function login($username, $password)
		{
			$str = "Select * 
					From msuserhd 
					Where UserEmail='".$username."' or UserID='".$username."' AND IsActive=1 ";
			$res = $this->db->query($str);
			if($res !== FALSE && $res -> num_rows() > 0){

				if (strtoupper($res->row()->UserPassword)==strtoupper(md5($password))) {
				 	return array("result"=>"success", "data"=>$res->row());
				} else {
					return array("result"=>"authentication failed", "data"=>array());
				}
			} else {
				return array("result"=>"user not found", "data"=>array());
			}
		}

		function changePassword($username, $newpassword)
		{
			$this->db->where('UserEmail', $username);
			$this->db->set('UserPassword', md5($newpassword));
			$this->db->Update('msuserhd');
		}

		function getAllActiveUser(){

		    $str = "SELECT [UserEmail],
		    			(case when UserID is null then '0' else UserID end) as AlternateID, 
		    			(case when UserID is null then '0' else UserID end) as UserID, 
		    			[UserName],[UserPassword],[setpass],[UserLevel],[IsActive],[Flag], 
		    			(case when branch_id is null then '' else branch_id end) as BranchID,
						[Payroll_Pwd],[Payroll_SetPass],[Payroll_IsActive],[Email],[CreatedBy],[CreatedDate],
						[UpdatedBy],[UpdatedDate],[role_pm_id],[DefaultDatabaseId],
						(case when GroupID is null then '' else GroupID end) as GroupID,
						(case when BranchName is null then '' else BranchName end) as BranchName,
						(case when GroupName is null then '' else GroupName end) as GroupName,
						isnull([City],'') as City, isnull([SalesmanID],'') as SalesmanID,
						isnull([RefID],'') as RefID, isnull([DivisionID],'') as DivisionID, isnull([DivisionName],'') as DivisionName,
						isnull([EmpTypeID],'') as EmpTypeID, isnull([EmpType],'') as EmpType, isnull([EmpLevelID],'') as EmpLevelID,
						isnull([EmpLevel],'') as EmpLevel, isnull([EmpPositionID],'') as EmpPositionID, isnull([EmpPositionName],'') as EmpPositionName,
						[HiredDate],[Pengangkatan],[EndDate],isnull(Whatsapp,'') as Whatsapp, isnull([Mobile],'') as Mobile,
						[BankName],[BankAccountNumber],[UserEmailOld],[needSync]
					FROM msuserhd WHERE IsActive = 1 and isnull([UserID],'0')<> '0' ";
			if ($_SESSION["branchID"]!="JKT") {
				$str.=" AND isnull(msuserhd.branch_id,'')='".$_SESSION["branchID"]."' ";
			}
			$str .= " ORDER BY UserName";
			$res = $this->db->query($str);
			if ($res->num_rows()>0)
			    return $res->result();
			else
				return array();
		}

		function GetListCount($where){
			$this->db->select("count(UserEmail) as c");
			$this->db->where($where);
			$result = $this->db->get("msuserhd with(nolock)")->row();
			if($result!=null){
				return $result->c;
			}
			else{
				return 0;
			}
		}
		function GetList($where,$whereSub,$order,$top){
			$whereSubStr = "";
			if($whereSub!=''){
				$whereSubStr = "WHERE ";
				foreach($whereSub as $key => $value ){
					if($value===null){
						$whereSubStr .= $key . " AND ";
					}
					else{
						$whereSubStr .= $key ." = '".$value."' AND ";
					}
					
				}
				$whereSubStr = rtrim($whereSubStr," AND");
			}

			$from = <<<SQL
				(
					SELECT
					ROW_NUMBER() OVER(ORDER BY {$order}) AS RowNum,
					[UserEmail],
					(case
						when msuserhd.UserID is null then ''
						else msuserhd.UserID
					end) as UserID,
					[UserName],
					[IsActive],
					(case
						when msuserhd.branch_id is null then ''
						else msuserhd.branch_id
					end) as BranchID,
					(case
						when msuserhd.GroupID is null then ''
						else msuserhd.GroupID
					end) as GroupID,
					(case
						when msuserhd.BranchName is null then ''
						else msuserhd.BranchName
					end) as BranchName,
					(case
						when msuserhd.GroupName is null then ''
						else msuserhd.GroupName
					end) as GroupName,
					(case
						when msuserhd.EmpPositionName is null then ''
							else msuserhd.EmpPositionName
						end) as EmpPositionName
					FROM msuserhd with(nolock)
					{$whereSubStr}
				) as x
SQL;
			$this->db->select("top ".$top." *");
			$this->db->from($from);
			$this->db->where($where);
			$res = $this->db->get()->result_array();
			//echo $this->db->last_query(); die();
		    return $res;
		}


		function getuserlist($data){

			$data_list=array();
			
			$page=0;
			if(!empty($data['iDisplayStart'])){
				$page=$data['iDisplayStart'];
			}


			$SortCol='UserID';
			$SortDir='asc';


			if(!empty($data['iSortCol_0'])){
				if($data['iSortCol_0']==0){
					$SortCol='UserID';
				}else if($data['iSortCol_0']==1){
					$SortCol='UserName';
				}else if($data['iSortCol_0']==2){
					$SortCol='EmpPositionName';
				}else if($data['iSortCol_0']==3){
					$SortCol='BranchName';
				}else if($data['iSortCol_0']==4){
					$SortCol='isActive';
				}
			}


			if(!empty($data['sSortDir_0'])){
				$SortDir=$data['sSortDir_0'];
			}



			$page=0;
			if(!empty($data['iDisplayStart'])){
				$page=$data['iDisplayStart'];
			}

			$total_data_view=10;
			if(!empty($data['iDisplayLength'])){
				$total_data_view=$data['iDisplayLength'];
			}



			$query_jum  = "select * from msuserhd where ";

			if($data['sSearch_0'] == 'true' || empty($data['sSearch_0']) || $data['sSearch_0'] == 'false'){
				if($data['sSearch_0']=='true' || $data['sSearch_0']=='false' || $data['sSearch_0']==''){
					$sSearch_0=1;
				}else{
					$sSearch_0=0;
				} 
			}else{
				$sSearch_0=$data['sSearch_0'];
			}

			if($data['bRegex_0'] == 'true' || empty($data['bRegex_0']) || $data['bRegex_0'] == 'false'){
				if($data['bRegex_0']=='true' || $data['bRegex_0']=='false' || $data['bRegex_0']==''){
					$bRegex_0=1;
				}else{
					$bRegex_0=0;
				} 
			}else{
				$bRegex_0=$data['bRegex_0'];
			}

			// if($sSearch_0==1 && $bRegex_0==0){
				// $query_jum  .= "IsActive='1' ";
			// }else if($sSearch_0==0 && $bRegex_0==1){
				// $query_jum  .= "IsActive='0' ";
			// }else if($sSearch_0==0 && $bRegex_0==0){
				// $query_jum  .= "IsActive='2' ";
			// }else{
				// $query_jum  .= "IsActive in ('0','1') ";
			// }
			 
			if ($data['active']=='0' && $data['noactive']=='0'){
				$query_jum  .= " (IsActive ='2')  "; 
			}
			else if ($data['active']=='1' && $data['noactive']=='0'){
				$query_jum  .= " (IsActive ='1')  "; 
			}
			else if ($data['active']=='0' && $data['noactive']=='1'){
				$query_jum  .= " (IsActive ='0')  "; 
			}
			else {
				$query_jum  .= " (IsActive IN ('1','0'))  "; 
			}

			if(!empty($data['sSearch'])){
				$query_jum  .= " and (UserID LIKE '%".$data['sSearch']."%' OR UserName LIKE '%".$data['sSearch']."%' OR UserEmail LIKE '%".$data['sSearch']."%' OR GroupName LIKE '%".$data['sSearch']."%' OR BranchName LIKE '%".$data['sSearch']."%' OR EmpPositionName LIKE '%".$data['sSearch']."%')";
			}

			$resjum=$this->db->query($query_jum);



			$query  = "select * from msuserhd where ";

			if($data['sSearch_0'] == 'true' || empty($data['sSearch_0']) || $data['sSearch_0'] == 'false'){
				if($data['sSearch_0']=='true' || $data['sSearch_0']=='false' || $data['sSearch_0']==''){
					$sSearch_0=1;
				}else{
					$sSearch_0=0;
				} 
			}else{
				$sSearch_0=$data['sSearch_0'];
			}

			if($data['bRegex_0'] == 'true' || empty($data['bRegex_0']) || $data['bRegex_0'] == 'false'){
				if($data['bRegex_0']=='true' || $data['bRegex_0']=='false' || $data['bRegex_0']==''){
					$bRegex_0=1;
				}else{
					$bRegex_0=0;
				} 
			}else{
				$bRegex_0=$data['bRegex_0'];
			}

			// if($sSearch_0==1 && $bRegex_0==0){
				// $query  .= "IsActive='1' ";
			// }else if($sSearch_0==0 && $bRegex_0==1){
				// $query  .= "IsActive='0' ";
			// }else if($sSearch_0==0 && $bRegex_0==0){
				// $query  .= "IsActive='2' ";
			// }else{
				// $query  .= "IsActive in ('0','1') ";
			// }
			 
			if ($data['active']=='0' && $data['noactive']=='0'){
				$query  .= " (IsActive ='2')  "; 
			}
			else if ($data['active']=='1' && $data['noactive']=='0'){
				$query  .= " (IsActive ='1')  "; 
			}
			else if ($data['active']=='0' && $data['noactive']=='1'){
				$query  .= " (IsActive ='0')  "; 
			}
			else {
				$query  .= " (IsActive IN ('1','0'))  "; 
			}


			if(!empty($data['sSearch'])){
				$query  .= " and (UserID LIKE '%".$data['sSearch']."%' OR UserName LIKE '%".$data['sSearch']."%' OR UserEmail LIKE '%".$data['sSearch']."%' OR GroupName LIKE '%".$data['sSearch']."%' OR BranchName LIKE '%".$data['sSearch']."%' OR EmpPositionName LIKE '%".$data['sSearch']."%')";
			}
			
			$query .= " and UserEmail!=''";
			$query .=" order by ".$SortCol." ".$SortDir." OFFSET ".$page." ROWS FETCH NEXT ".$total_data_view." ROWS ONLY";
			$res=$this->db->query($query);
			if($res->num_rows() > 0){

				$hasildata['total']=$resjum->num_rows();
				$hasildata['data']=$res->result();
				return $hasildata;

			}else{
				return array();
			}
		}

		function getAllUser_OLD($data){

			$data_list=array();
			
			$page=0;
			if(!empty($data['iDisplayStart'])){
				$page=$data['iDisplayStart'];
			}


			$SortCol='UserID';
			$SortDir='asc';


			if(!empty($data['iSortCol_0'])){
				if($data['iSortCol_0']==1){
					$SortCol='UserID';
				}else if($data['iSortCol_0']==2){
					$SortCol='UserName';
				}else if($data['iSortCol_0']==3){
					$SortCol='UserEmail';
				}
			}


			if(!empty($data['sSortDir_0'])){
				$SortDir=$data['sSortDir_0'];
			}



			$page=0;
			if(!empty($data['iDisplayStart'])){
				$page=$data['iDisplayStart'];
			}

			$total_data_view=10;
			if(!empty($data['iDisplayLength'])){
				$total_data_view=$data['iDisplayLength'];
			}



			$query_jum  = "SELECT [UserEmail],isnull([AlternateID],'0') as AlternateID,[UserName],[UserPassword],[setpass],[UserLevel],[IsActive],[Flag],
		    			isnull([branch_id],'') as BranchID,
						[Payroll_Pwd],[Payroll_SetPass],[Payroll_IsActive],[Email],[CreatedBy],[CreatedDate],[UpdatedBy],[UpdatedDate],
						[role_pm_id],[DefaultDatabaseId],
						isnull([GroupID],'') as GroupID,isnull([BranchName],'') as BranchName, isnull([GroupName],'') as GroupName,
						isnull([City],'') as City, isnull([SalesmanID],'') as SalesmanID,
						isnull([RefID],'') as RefID, isnull([DivisionID],'') as DivisionID, isnull([DivisionName],'') as DivisionName,
						isnull([EmpTypeID],'') as EmpTypeID, isnull([EmpType],'') as EmpType, isnull([EmpLevelID],'') as EmpLevelID,
						isnull([EmpLevel],'') as EmpLevel, isnull([EmpPositionID],'') as EmpPositionID, isnull([EmpPositionName],'') as EmpPositionName,
						[HiredDate],[Pengangkatan],[EndDate],isnull(Whatsapp,'') as Whatsapp, isnull([Mobile],'') as Mobile,
						[BankName],[BankAccountNumber],[UserEmailOld],[needSync]
						FROM msuserhd where IsActive='1'";

			if(!empty($data['sSearch'])){
				$query_jum  .= " and (UserID LIKE '%".$data['sSearch']."%' OR UserName LIKE '%".$data['sSearch']."%' OR UserEmail LIKE '%".$data['sSearch']."%')";
			}

			$resjum=$this->db->query($query_jum);



			$query  = "SELECT [UserEmail],isnull([AlternateID],'0') as AlternateID,[UserName],[UserPassword],[setpass],[UserLevel],[IsActive],[Flag],
		    			isnull([branch_id],'') as BranchID,
						[Payroll_Pwd],[Payroll_SetPass],[Payroll_IsActive],[Email],[CreatedBy],[CreatedDate],[UpdatedBy],[UpdatedDate],
						[role_pm_id],[DefaultDatabaseId],
						isnull([GroupID],'') as GroupID,isnull([BranchName],'') as BranchName, isnull([GroupName],'') as GroupName,
						isnull([City],'') as City, isnull([SalesmanID],'') as SalesmanID,
						isnull([RefID],'') as RefID, isnull([DivisionID],'') as DivisionID, isnull([DivisionName],'') as DivisionName,
						isnull([EmpTypeID],'') as EmpTypeID, isnull([EmpType],'') as EmpType, isnull([EmpLevelID],'') as EmpLevelID,
						isnull([EmpLevel],'') as EmpLevel, isnull([EmpPositionID],'') as EmpPositionID, isnull([EmpPositionName],'') as EmpPositionName,
						[HiredDate],[Pengangkatan],[EndDate],isnull(Whatsapp,'') as Whatsapp, isnull([Mobile],'') as Mobile,
						[BankName],[BankAccountNumber],[UserEmailOld],[needSync]
						FROM msuserhd where IsActive='1'";



			if(!empty($data['sSearch'])){
				$query  .= " and (UserID LIKE '%".$data['sSearch']."%' OR UserName LIKE '%".$data['sSearch']."%' OR UserEmail LIKE '%".$data['sSearch']."%')";
			}
			
			$query .=" order by ".$SortCol." ".$SortDir." OFFSET ".$page." ROWS FETCH NEXT ".$total_data_view." ROWS ONLY";
			$res=$this->db->query($query);
			if($res->num_rows() > 0){

				$hasildata['total']=$resjum->num_rows();
				$hasildata['data']=$res->result();
				return $hasildata;

			}else{
				return array();
			}

		    // $str = "SELECT [UserEmail],isnull([AlternateID],'0') as AlternateID,[UserName],[UserPassword],[setpass],[UserLevel],[IsActive],[Flag],
		    // 			isnull([branch_id],'') as BranchID,
			// 			[Payroll_Pwd],[Payroll_SetPass],[Payroll_IsActive],[Email],[CreatedBy],[CreatedDate],[UpdatedBy],[UpdatedDate],
			// 			[role_pm_id],[DefaultDatabaseId],
			// 			isnull([GroupID],'') as GroupID,isnull([BranchName],'') as BranchName, isnull([GroupName],'') as GroupName,
			// 			isnull([City],'') as City, isnull([SalesmanID],'') as SalesmanID,
			// 			isnull([RefID],'') as RefID, isnull([DivisionID],'') as DivisionID, isnull([DivisionName],'') as DivisionName,
			// 			isnull([EmpTypeID],'') as EmpTypeID, isnull([EmpType],'') as EmpType, isnull([EmpLevelID],'') as EmpLevelID,
			// 			isnull([EmpLevel],'') as EmpLevel, isnull([EmpPositionID],'') as EmpPositionID, isnull([EmpPositionName],'') as EmpPositionName,
			// 			[HiredDate],[Pengangkatan],[EndDate],isnull(Whatsapp,'') as Whatsapp, isnull([Mobile],'') as Mobile,
			// 			[BankName],[BankAccountNumber],[UserEmailOld],[needSync]
			// 		FROM msuserhd";

			// if ($_SESSION["branchID"]!="JKT") {
			// 	$str.=" WHERE isnull(msuserhd.branch_id,'')='".$_SESSION["branchID"]."' and IsActive='1'";
			// }else{
			// 	$str .=" WHERE IsActive='1'";
			// }

			// $str .= " ORDER BY UserName";
			// $res = $this->db->query($str);
			// if ($res->num_rows()>0)
			//     return $res->result();
			// else
			// 	return array();
		}

		function getAllUser($param){

			//Kolom yg akan diselect (jika tidak ingin difilter, kolom ditaruh di ujung)
			$aColumns = array("AlternateID as UserID","[UserName] AS UserName","[UserEmail] AS UserEmail");
			
			$sTable = " msuserhd ";
			$sWhere = "  IsActive='1' ";
			$query  = DatatableQuery($param, $sTable, $aColumns, $sWhere, 0);
			
			// echo json_encode($query);die;
			
			$res = $this->db->query($query['sQueryFiltered']);
			$iFilteredTotal = $res->num_rows();
				
			$data = array();
			if ($iFilteredTotal>0){
				foreach($res->result_array() as $r){
					$row = array();
					$onclick 	= "'".$r['UserID']."','".$r['UserName']."','".$r['UserEmail']."'";
					$button	= '<button class="btn btn-sm btn-dark" onclick="pilihuser('.$onclick.')">Pilih</button>';
					
					$row[0]=$r['UserID'];
					$row[1]=$r['UserName'];
					$row[2]=$r['UserEmail'];
					$row[3]=$button;
					$data[] = $row;
				}
			}
			
			$res = $this->db->query($query['sQueryTotal']);
			$iTotal = $res->row()->total;

			$output = array(
				"draw" => $param['draw'],
				"recordsTotal" => $iTotal,
				"recordsFiltered" => $iTotal,
				"data" => $data
			);
			echo json_encode($output);

		}

		function getUserDataByEmail($useremail){
			$str = "Select a.*, (case when a.UserID is null then '0' else a.UserID end) as USERID, 
					(case when b.Name is null then '' else b.Name end) as GroupName, 
					(case when a.branch_id is null then '' else a.branch_id end) as BranchID
					From msuserhd a left join Ms_Group b on a.GroupID=b.GroupID 
					Where a.UserEmail = '".$useremail."'";
			// die($str);

			$res = $this->db->query($str);
			if ($res->num_rows()>0)
				return $res->row();
			else
				return null;
		}

		function Get2($useremail){
			$str = "Select isnull(UserID,'0') as USERID, '0' as KTP, a.UserEmail as ApplicantID, '' as SALESMANID, '' as REFID, 
						UserEmail as USEREMAIL, a.IsActive as ISACTIVE, null as NIK, a.UserName as [NAME], null as ALIASNAME, isnull(AlternateID,0) as BADGENUMBER, 
						a.HiredDate as HIREDDATE, a.Pengangkatan as PENGANGKATAN, null as PROMOTIONDATE, a.EndDate as ENDDATE, isnull(a.GroupID,'') as GROUPID, 
						a.DivisionID as DIVISIONID, a.EmpTypeID as EMPTYPEID, a.EmpLevelID as EMPLEVELID, a.EmpPositionID as EMPPOSITIONID,
						null as BIRTHDATE, null as GENDER, a.UserEmail as EMAIL, a.Mobile as MOBILE, a.Whatsapp as WHATSAPP, a.DefaultDatabaseId as DATABASEID, 
						a.branch_id as BRANCHID, '' as BRANCHNAME, '' as EMPLEVEL, '' as CITY, '' as GROUPNAME, '' as EMPTYPE, '' as DIVISIONNAME, 0 as EMPLEVELNUMBER,
						'' as EMPPOSITIONNAME, a.BankName as BANKNAME, a.BankAccountNumber as BANKACCOUNTNUMBER, a.UserEmail as SALARYEMAIL
					From msuserhd a left join Ms_Group b on a.GroupID=b.GroupID 
						left join Ms_Branch c on a.branch_id=c.BranchID
					Where a.UserEmail = '".$useremail."'";
			// die($str);

			$res = $this->db->query($str);
			if ($res->num_rows()>0)
				return $res->row();
			else
				return null;
		}

		function Get3($useremail){
			$str = "Select isnull(UserID,'0') as USERID, '0' as KTP, a.UserEmail as ApplicantID, '' as SALESMANID, '' as REFID, 
						UserEmail as USEREMAIL, a.IsActive as ISACTIVE, null as NIK, a.UserName as [NAME], null as ALIASNAME, isnull(AlternateID,0) as BADGENUMBER, 
						a.HiredDate as HIREDDATE, a.Pengangkatan as PENGANGKATAN, null as PROMOTIONDATE, a.EndDate as ENDDATE, isnull(a.GroupID,'') as GROUPID, 
						a.DivisionID as DIVISIONID, a.EmpTypeID as EMPTYPEID, a.EmpLevelID as EMPLEVELID, a.EmpPositionID as EMPPOSITIONID,
						null as BIRTHDATE, null as GENDER, a.Email as EMAIL, a.Mobile as MOBILE, a.Whatsapp as WHATSAPP, a.DefaultDatabaseId as DATABASEID, 
						a.branch_id as BRANCHID, '' as BRANCHNAME, '' as EMPLEVEL, '' as CITY, '' as GROUPNAME, '' as EMPTYPE, '' as DIVISIONNAME, 0 as EMPLEVELNUMBER,
						'' as EMPPOSITIONNAME, a.BankName as BANKNAME, a.BankAccountNumber as BANKACCOUNTNUMBER, a.UserEmail as SALARYEMAIL
					From msuserhd a left join Ms_Group b on a.GroupID=b.GroupID 
						left join Ms_Branch c on a.branch_id=c.BranchID
					Where a.Email = '".$useremail."' OR a.UserEmail = '".$useremail."'";
			// die($str);

			$res = $this->db->query($str);
			if ($res->num_rows()>0)
				return $res->row();
			else
				return null;
		}

		// {"USERID":3124,"KTP":"3173082710740005","APPLICANTID":"APLC2020020054","SALESMANID":"SPS-HAN","REFID":"","USEREMAIL":"burhan.halim@yahoo.com","ISACTIVE":1,
		// "NIK":null,"NAME":"BURHAN HALIM","ALIASNAME":null,"BADGENUMBER":"9170","HIREDDATE":"2020-02-13","PENGANGKATAN":"2020-06-02","PROMOTIONDATE":"2020-02-18",
		// "ENDDATE":null,"GROUPID":"CJKT1","DIVISIONID":"1.1.04.00.01","EMPTYPEID":"EL02","EMPLEVELID":"EML03","EMPPOSITIONID":"JT0027",
		// "BIRTHDATE":"1990-01-01","GENDER":"MALE","EMAIL":"burhan.halim@yahoo.com","MOBILE":"08179995599","WHATSAPP":"08179995599","DATABASEID":"","BRANCHID":"JKT",
		// "BRANCHNAME":"JAKARTA","EMPLEVEL":"KABAG","CITY":"JAKARTA","GROUPNAME":"Jakarta BKT","EMPTYPE":"TETAP","DIVISIONNAME":"SUBDIV. PENJUALAN MO","EMPLEVELNUMBER":3,
		// "EMPPOSITIONNAME":"Marketing Penjualan","BANKNAME":"BCA","BANKACCOUNTNUMBER":"2871414132","SALARYEMAIL":"burhan.halim@yahoo.com"}


		function getUserDataByUserID($userid){
			$str = "Select a.*, isnull(UserID, '0') as USERID, 
					(case when b.Name is null then '' else b.Name end) as GroupName
					From msuserhd a  left join Ms_Group b on a.GroupID=b.GroupID 
					Where UserID = '".$userid."'";
			//die($str);
			$res = $this->db->query($str);
			if ($res->num_rows()>0)
				return $res->row();
			else
				return null;
		}

		function getUserDataByUserName($username){
	 	 	$this->db->select('*');
		    $this->db->from('msuserhd');
		    $this->db->where('UserName', $username);
		    return $this->db->get()->row();
		}

		function searchUserByBranch($branch="", $nama=""){
			$qry = "Select * from msuserhd where 1=1";
			if ($branch!="") {
				$qry.=" and branch_id='".$branch."' ";
			}
			if ($nama != "") {
				$qry.=" and UserName like '%".$nama."%' ";
			}
			$qry.=" Order By UserName";
			$res = $this->db->query($qry);
			if ($res->num_rows()>0)
				return $res->result();
			else
				return array();
		}	

		function getKdSlsman($useremail,$db){
		 	$this->db->select('*');
		    $this->db->from('TblMappingSalesman');
		    $this->db->where('useremail', $useremail);
		    $this->db->where('nama_db', $db);
		    return $this->db->get()->row();
	 	}

		function GetRoleByEmail($useremail){
		 	$this->db->select('*');
		    $this->db->from('tb_user_dt');
		    $this->db->where('useremail', $useremail);
		    return $this->db->get()->result();
		}

		function GetRoleByID($userid = '')
		{
			$this->db->where("UserID=".$userid."");
			$this->db->join('tb_role_hd b','a.role_id=b.role_id');
			$res = $this->db->get('tb_user_dt a');

			if($res->num_rows() > 0)
				return $res->result();
			else
				return array();
		}

		function UpdateDefaultDb($useremail, $databaseid){
			$str = "update msuserhd set DefaultDatabaseId=".$databaseid." where UserEmail='".$useremail."'";
			$res = $this->db->query($str);
		    return true;
		}

		function UpdateUserID($USEREMAIL, $USERID){
			$str = "update msuserhd set AlternateID=".$USERID." where UserEmail='".$USEREMAIL."'";
			$res = $this->db->query($str);
		    return true;
		}

		function Disable($USER){
			$str = "update msuserhd set IsActive=0 where AlternateID='".$USER->USERID."'";
			$res = $this->db->query($str);
		    return true;
		}


		function UpdateBranchID($USERID, $BRANCHID){
			$str = "update msuserhd set branch_id=".$BRANCHID." where UserEmail='".$USERID."'";
			$res = $this->db->query($str);
		    return true;
		}
 
		function UpdateSalesmanID($data){

			$this->db->trans_start();  
 
			foreach ($data as $key => $value) {  
					$this->db->where("UserID", $value->USERID); 
					$this->db->set("SalesmanID", $value->KD_SLSMAN); 
					$this->db->update('msuserhd');	 
			}      
			if( ($errors = sqlsrv_errors() ) != null) {
		        foreach( $errors as $error ) {
		        	$ERR_CODE = $error["code"];
		            $ERR_MSG.= "message: ".$error[ 'message']." ";
		        }
		        $this->db->trans_rollback();
		        return array("result"=>"FAILED", "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
		    } else {
		    	$this->db->trans_commit();
		    	//die($this->db->last_query());
		    } 
		    $this->db->trans_complete();
		    return array("result"=>"SUCCESS");
		}
 
		function AddUser($user, $password, $currentUser) 
		{
			$USEREMAIL = (($user["USEREMAIL"]=="")? $user["EMAIL"]:$user["USEREMAIL"]);
			
			$this->db->trans_start();
			$this->db->set('UserEmail',$USEREMAIL);
			$this->db->set('AlternateID',$user['USERID']);
			$this->db->set('UserID',$user['USERID']);
			$this->db->set('UserName', $user['NAME']);
			$this->db->set('UserPassword',md5($password));
			$this->db->set('UserLevel','COMMON');
			$this->db->set('IsActive', $user["ISACTIVE"]);
			$this->db->set('Flag',1);
			$this->db->set('branch_id',$user["BRANCHID"]);
			$this->db->set('BranchName',$user['BRANCHNAME']);
			$this->db->set('GroupID',$user['GROUPID']);
			$this->db->set('GroupName',$user['GROUPNAME']);
			$this->db->set('City',$user['CITY']);
			$this->db->set('SalesmanID',$user['SALESMANID']);
			$this->db->set('RefID',$user['REFID']);
			$this->db->set('DivisionID',$user['DIVISIONID']);
			$this->db->set('DivisionName',$user['DIVISIONNAME']);
			$this->db->set('EmpTypeID',$user['EMPTYPEID']);
			$this->db->set('EmpType',$user['EMPTYPE']);
			$this->db->set('EmpLevelID',$user['EMPLEVELID']);
			$this->db->set('EmpLevel',$user['EMPLEVEL']);
			$this->db->set('EmpPositionID',$user['EMPPOSITIONID']);
			$this->db->set('EmpPositionName',$user['EMPPOSITIONNAME']);
			$this->db->set('Mobile',$user['MOBILE']);
			$this->db->set('Whatsapp',$user['WHATSAPP']);
			$this->db->set('Email',$user['EMAIL']);
			$this->db->set('BankName',$user['BANKNAME']);
			$this->db->set('BankAccountNumber',$user['BANKACCOUNTNUMBER']);
			if ($user["HIREDDATE"]!=null) $this->db->set('HiredDate',date("Y-m-d",strtotime($user['HIREDDATE'])));
			if ($user["PENGANGKATAN"]!=null) $this->db->set('Pengangkatan',date("Y-m-d", strtotime($user['PENGANGKATAN'])));
			if ($user["ENDDATE"]!=null) $this->db->set('EndDate', date("Y-m-d", strtotime($user['ENDDATE'])));
			$this->db->set('needSync',0);
			$this->db->set('CreatedBy',$currentUser);
			$this->db->set('CreatedDate',date('Y-m-d H:i:s'));
			$this->db->set('UpdatedBy','');
			$this->db->set('UpdatedDate',date('Y-m-d H:i:s'));
			$this->db->insert('msuserhd');

			$this->load->model('RoleModel');
			$roleDefault = $this->RoleModel->GetRoleDefault()->role_id;

			$this->db->where('UserID', $user["USERID"]);
			$qry = $this->db->get('tb_user_dt');

			if($qry->num_rows() == 0){

				$this->db->set('UserEmail',$USEREMAIL);
				$this->db->set("UserID", $user["USERID"]);
				$this->db->set('role_id', $roleDefault);
				$this->db->set('created_by',$currentUser);
				$this->db->set('created_date',date('Y-m-d H:i:s'));
				$this->db->insert('tb_user_dt');

			}
			
			$this->db->where('UserEmail', $USEREMAIL);
			$qry = $this->db->get('msuserdt');

			if($qry->num_rows() == 0){
			// 	JIRA BKT-650 untuk dipakai oleh UserRequest
				$this->db->set("UserEmail", $USEREMAIL);
				$this->db->set("RoleId", "Role_Staff");
				$this->db->insert("msuserdt");
			}
			
			$this->db->where('UserEmail', $USEREMAIL);
			$qry = $this->db->get('msusershift');

			if($qry->num_rows() == 0){
				//JIRA BKT-650 untuk dipakai oleh UserRequest
				$this->db->set("UserEmail", $USEREMAIL);
				$this->db->set("ShiftId", "Default");
				$this->db->insert("msusershift");
			}

			$this->db->trans_complete();
		}

		function UpdateUser($user, $userid, $currentUser=0) 
		{	
			// die(json_encode($user));
			if ($userid!=0) {

				$currentData = $this->Get($userid);
				if($currentData){
				if ($currentUser==0) $currentUser = $userid;

				$this->db->trans_start();

				$CheckRole = $this->GetRoleByEmail($currentData->UserEmail);
				if (count($CheckRole)>0) {
					$check = $this->db->query("Select * From tb_user_dt Where UserEmail='".$user->EMAIL."' or UserID=".$userid);
					if ($check->num_rows()==0) {
						$this->db->where("UserEmail", $currentData->UserEmail);
						$this->db->set("UserID", $userid);
						$this->db->set("UserEmail", $user->EMAIL);
						$this->db->update("tb_user_dt");
					}
				} else {

				}

				$this->db->where("AlternateID", $userid);
				$this->db->where("IsActive", false);
				// $this->db->set('AlternateID',"");
				$this->db->set('UpdatedBy', ((ISSET($_SESSION["logged_in"]["employeeid"])) ? $_SESSION["logged_in"]["employeeid"] : ""));
				$this->db->set('UpdatedDate',date('Y-m-d H:i:s'));
				$this->db->update('msuserhd');


				$this->db->where('AlternateID',$userid);
				$this->db->set("UserEmailOld", $currentData->UserEmail);
				$this->db->set('UserEmail',$user->EMAIL);
				$this->db->set('UserName', $user->NAME);
				$this->db->set('IsActive', $user->ISACTIVE);
				$this->db->set('branch_id',$user->BRANCHID);
				$this->db->set('BranchName',$user->BRANCHNAME);
				$this->db->set('GroupID',$user->GROUPID);
				$this->db->set('GroupName',$user->GROUPNAME);
				$this->db->set('City',$user->CITY);
				$this->db->set('SalesmanID',$user->SALESMANID);
				$this->db->set('RefID',$user->REFID);
				$this->db->set('DivisionID',$user->DIVISIONID);
				$this->db->set('DivisionName',$user->DIVISIONNAME);
				$this->db->set('EmpTypeID',$user->EMPTYPEID);
				$this->db->set('EmpType',$user->EMPTYPE);
				$this->db->set('EmpLevelID',$user->EMPLEVELID);
				$this->db->set('EmpLevel',$user->EMPLEVEL);
				$this->db->set('EmpPositionID',$user->EMPPOSITIONID);
				$this->db->set('EmpPositionName',$user->EMPPOSITIONNAME);
				$this->db->set('Mobile',$user->MOBILE);
				$this->db->set("Whatsapp", $user->WHATSAPP);
				$this->db->set('Email',$user->EMAIL);
				$this->db->set('BankName',$user->BANKNAME);
				$this->db->set('BankAccountNumber',$user->BANKACCOUNTNUMBER);
				if ($user->HIREDDATE!=null) $this->db->set('HiredDate',date("Y-m-d",strtotime($user->HIREDDATE)));
				if ($user->PENGANGKATAN!=null) $this->db->set('Pengangkatan',date("Y-m-d", strtotime($user->PENGANGKATAN)));
				if ($user->ENDDATE!=null) $this->db->set('EndDate', date("Y-m-d", strtotime($user->ENDDATE)));
				$this->db->set('needSync',0);
				$this->db->set('UpdatedBy',$currentUser);
				$this->db->set('UpdatedDate',date('Y-m-d H:i:s'));
				$this->db->update('msuserhd');

				$ERR_CODE = 0;
				$ERR_MSG = "";

				if( ($errors = sqlsrv_errors() ) != null) {
			        foreach( $errors as $error ) {
			        	$ERR_CODE = $error["code"];
			            $ERR_MSG.= "message: ".$error[ 'message']."";
			            die($ERR_MSG);
			        }
			    } else {
			    	//echo("DELETE ".$TABLE." FINISHED SUCCESSFULLY<br>");
			    }

				$this->db->trans_complete();
				}
			}
		}

		function InsertUserFromDataZen($user)
		{
			$CurUser = ((ISSET($_SESSION["logged_in"]["employeeid"])) ? $_SESSION["logged_in"]["employeeid"] : "");
			$USEREMAIL = (($user["USEREMAIL"]=="")? $user["EMAIL"]:$user["USEREMAIL"]);
			
			$this->db->trans_start();
			$this->db->set('UserEmail',$USEREMAIL);
			$this->db->set('AlternateID',$user['USERID']);
			$this->db->set('UserName', $user['NAME']);
			$this->db->set('UserPassword',md5('12345'));
			$this->db->set('UserLevel','COMMON');
			$this->db->set('IsActive', $user["ISACTIVE"]);
			$this->db->set('Flag',1);
			$this->db->set('branch_id',$user["BRANCHID"]);
			$this->db->set('GroupID',$user['GROUPID']);
			$this->db->set('CreatedBy',$CurUser);
			$this->db->set('CreatedDate',date('Y-m-d H:i:s'));
			$this->db->set('UpdatedBy','');
			$this->db->set('UpdatedDate',date('Y-m-d H:i:s'));
			$this->db->Insert('msuserhd');

			$this->load->model('RoleModel');
			$roleDefault = $this->RoleModel->GetRoleDefault()->role_id;

			$this->db->set('UserEmail',$USEREMAIL);
			$this->db->set("UserID", $user["USERID"]);
			$this->db->set('role_id', $roleDefault);
			$this->db->set('created_by',$CurUser);
			$this->db->set('created_date',date('Y-m-d H:i:s'));
			
			$this->db->insert('tb_user_dt');

			$this->db->trans_complete();
		}

		function EditUser($user)
		{			


			//die(json_encode($user));
			/*{"UserID":"436","UserEmail":"robby.miyako@yahoo.co.id","UserName":"A. ROBBI","Branch":"LAMPUNG","Group":"Lampung ASS","EmpLevel":"KABAG",
			"EmpType":"TETAP","GroupID":"CLPG1","IsActive":"1",
			"role":["ROLE03","ROLE08","ROLE10"],"RoleId":["ROLE03","ROLE08","ROLE10"],
			"RoleName":["STAFF","Head Division","MYCOMPANY"],"RoleDeleted":""}*/
			
			// 1. UPDATE SESUAI INPUTAN USER JIKA ADA
			$this->db->trans_start();
			$this->db->where("UserID", $user["UserID"]);
			$this->db->where("IsActive", false);
			$this->db->set('AlternateID',"");
			$this->db->set('UpdatedBy',((ISSET($_SESSION["logged_in"]["employeeid"])) ? $_SESSION["logged_in"]["employeeid"] : ""));
			$this->db->set('UpdatedDate',date('Y-m-d H:i:s'));
			$this->db->update('msuserhd');
			
			$this->db->where("UserEmail", $user["UserEmail"]);
			$this->db->set('UserID',$user['UserID']);
			$this->db->set('UserName', $user['UserName']);
			$this->db->set('IsActive', $user["IsActive"]);
			$this->db->set('branch_id',$user["BranchID"]);
			$this->db->set('GroupID',$user['GroupID']);
			$this->db->set('BranchName',$user['BranchName']);
			$this->db->set('GroupName',$user['GroupName']);
			$this->db->set('Email',$user["Email"]);
			$this->db->set('Whatsapp',$user["Whatsapp"]);
			$this->db->set('UpdatedBy',((ISSET($_SESSION["logged_in"]["employeeid"])) ? $_SESSION["logged_in"]["employeeid"] : ""));
			$this->db->set('UpdatedDate',date('Y-m-d H:i:s'));
			$this->db->update('msuserhd');
			
			// 2. JIKA ADA PILIH USERID ZEN MAKA UPDATE DATA ZEN
			if(ISSET($user["Zen"])){
				$Zen = $user["Zen"];
				
				$this->db->where("UserEmail", $user["UserEmail"]);
				if($user["UserEmail"]!=$Zen->EMAIL){
					$this->db->set('UserEmail',$Zen->EMAIL);
					$this->db->set('UserEmailOld',$user["UserEmail"]);
				}				
				$this->db->set('UserID',$Zen->USERID);
				$this->db->set('UserName',$Zen->NAME);
				
				// JIKA USER TIDAK ISI GROUP MAKA ISI DENGAN DATA ZEN
				if ($user["GroupID"]=='') {
					$this->db->set('GroupID',$Zen->GROUPID);
					$this->db->set('BranchName',$Zen->BRANCHNAME);
					$this->db->set('GroupName',$Zen->GROUPNAME);
				}
				
				if ($user["Email"]=='') {
					$this->db->set('Email',$Zen->EMAIL);
				}
				
				if ($user["Whatsapp"]=='') {
					$this->db->set('Whatsapp',$Zen->WHATSAPP);
				}
				
				$this->db->set('City',$Zen->CITY);
				$this->db->set('SalesmanID',$Zen->SALESMANID);
				$this->db->set('RefID',$Zen->REFID);
				$this->db->set('DivisionID',$Zen->DIVISIONID);
				$this->db->set('DivisionName',$Zen->DIVISIONNAME);
				$this->db->set('EmpTypeID',$Zen->EMPTYPEID);
				$this->db->set('EmpType',$Zen->EMPTYPE);
				$this->db->set('EmpLevelID',$Zen->EMPLEVELID);
				$this->db->set('EmpLevel',$Zen->EMPLEVEL);
				$this->db->set('EmpPositionID',$Zen->EMPPOSITIONID);
				$this->db->set('EmpPositionName',$Zen->EMPPOSITIONNAME);
				$this->db->set('HiredDate',$Zen->HIREDDATE);
				$this->db->set('Pengangkatan',$Zen->PENGANGKATAN);
				$this->db->set('EndDate',$Zen->ENDDATE);
				$this->db->set('Mobile',$Zen->MOBILE);
				$this->db->set('BankName',$Zen->BANKNAME);
				$this->db->set('BankAccountNumber',$Zen->BANKACCOUNTNUMBER);
				$this->db->update('msuserhd');
				
				// JIKA USEREMAILNYA DIUPDATE DENGAN EMAIL DARI ZEN,
				// MAKA UPDATEKAN JUGA USEREMAIL DI TABEL BERIKUT

				if($user["UserEmail"]!=$Zen->EMAIL){
					// 1. msuserdt
					$check = $this->db->query("Select * From msuserdt Where UserEmail='".$Zen->EMAIL."' or UserID=".$Zen->USERID);
					if ($check->num_rows()==0) {
						$this->db->where("UserEmail", $user["UserEmail"]);
						$this->db->set('UserEmail',$Zen->EMAIL);
						$this->db->set('UserID',$Zen->USERID);
						$this->db->update('msuserdt');
					}

					// 2. tb_user_dt (ada di bawah)
					
					// 3. tb_user_dt_division 
					$check = $this->db->query("Select * From tb_user_dt_division Where UserEmail='".$Zen->EMAIL."' or UserID=".$Zen->USERID);
					if ($check->num_rows()==0) {
						$this->db->where("UserEmail", $user["UserEmail"]);
						$this->db->set('UserEmail',$Zen->EMAIL);
						$this->db->set('UserID',$Zen->USERID);
						$this->db->update('tb_user_dt_division');
					}

					// 4. tb_salesman
					$check = $this->db->query("Select * From tb_salesman Where useremail='".$Zen->EMAIL."' or userid=".$Zen->USERID);
					if ($check->num_rows()==0) {
						$this->db->where("useremail", $user["UserEmail"]);
						$this->db->set('useremail',$Zen->EMAIL);
						$this->db->set('userid',$Zen->USERID);
						$this->db->update('tb_salesman');
					}					
					// Aliat 24-Nov-2021
					
					// transuserrequest
					// RelatedUser
					$this->db->where("RelatedUser", $user["UserEmail"]);
					$this->db->set('RelatedUser',$Zen->EMAIL);
					$this->db->update('transuserrequest');
					// HIC
					$this->db->where("HIC", $user["UserEmail"]);
					$this->db->set('HIC',$Zen->EMAIL);
					$this->db->update('transuserrequest');
					// PIC
					$this->db->where("PIC", $user["UserEmail"]);
					$this->db->set('PIC',$Zen->EMAIL);
					$this->db->update('transuserrequest');
					// WaitConfirmBy
					$this->db->where("WaitConfirmBy", $user["UserEmail"]);
					$this->db->set('WaitConfirmBy',$Zen->EMAIL);
					$this->db->update('transuserrequest');
					// ProcessedBy
					$this->db->where("ProcessedBy", $user["UserEmail"]);
					$this->db->set('ProcessedBy',$Zen->EMAIL);
					$this->db->update('transuserrequest');
					// ClosedBy
					$this->db->where("ClosedBy", $user["UserEmail"]);
					$this->db->set('ClosedBy',$Zen->EMAIL);
					$this->db->update('transuserrequest');
					// CreatedBy
					$this->db->where("CreatedBy", $user["UserEmail"]);
					$this->db->set('CreatedBy',$Zen->EMAIL);
					$this->db->update('transuserrequest');
					// UpdatedBy
					$this->db->where("UpdatedBy", $user["UserEmail"]);
					$this->db->set('UpdatedBy',$Zen->EMAIL);
					$this->db->update('transuserrequest');


					// transuserrequestlog - UserEmail
					$this->db->where("UserEmail", $user["UserEmail"]);
					$this->db->set('UserEmail',$Zen->EMAIL);
					$this->db->update('transuserrequestlog');
		
					// transuserrequestresponse - UserEmail
					$this->db->where("UserEmail", $user["UserEmail"]);
					$this->db->set('UserEmail',$Zen->EMAIL);
					$this->db->update('transuserrequestresponse');
						
					// transuserrequestapproval - ApprovedBy
					$this->db->where("ApprovedBy", $user["UserEmail"]);
					$this->db->set('ApprovedBy',$Zen->EMAIL);
					$this->db->update('transuserrequestapproval');
						
					// transuserrequestresult - UserEmail
					$this->db->where("UserEmail", $user["UserEmail"]);
					$this->db->set('UserEmail',$Zen->EMAIL);
					$this->db->update('transuserrequestresult');
				}
			}
			
			// 3. DELETE ROLE YG LAMA
			$this->db->where("UserEmail", $user["UserEmail"]);
			$this->db->delete("tb_user_dt");
			// 4. ISI TABEL 'tb_user_dt' DENGAN ROLE YG BARU
			if(ISSET($user["RoleId"])){
				$roles = $user["RoleId"];
				for($i=0;$i<count($roles);$i++){
					$check = $this->db->query("Select * From tb_user_dt Where UserEmail='".((ISSET($Zen->EMAIL)) ? $Zen->EMAIL : $user["UserEmail"])."' 
									and role_id='".$roles[$i]."'");
					if ($check->num_rows()==0) {
						$this->db->set('UserEmail',((ISSET($Zen->EMAIL)) ? $Zen->EMAIL : $user["UserEmail"]));
						$this->db->set("UserID", $user["UserID"]);
						$this->db->set('role_id', $roles[$i]);
						$this->db->set('created_by',((ISSET($_SESSION["logged_in"]["employeeid"])) ? $_SESSION["logged_in"]["employeeid"] : ""));
						$this->db->set('created_date',date('Y-m-d H:i:s'));
						$this->db->insert('tb_user_dt');
					}
				}
			}

			//cek apakah emaiil msuserhd tidak sama dengan userinfo
			$this->db->select("m.UserEmail, u.Email");
			$this->db->where("m.AlternateID",$user["UserID"]);
			$this->db->join("USERINFO u","u.USERID = m.AlternateID ","left");
			$checkEmail = $this->db->get("msuserhd m")->row();
			if($checkEmail!=null){
				$oldEmail = $checkEmail->UserEmail;
				$newEmail = $checkEmail->Email;
				if( ($oldEmail!=$newEmail) && ($oldEmail!= $user['UserEmail']) ){
					//update email ApprovedBy dan RequestBy TblApproval

					$whereApproval = array(
						"ApprovedBy" => $oldEmail, 
						"ApprovedDate IS NULL" => null,
						"ApprovalStatus" => 'UNPROCESSED',
						"IsCancelled" => 0,
						"ExpiryDate > getdate()" => null,

					);
					$this->db->select("ApprovedBy");
					$this->db->where($whereApproval);
					$getTblApproval = $this->db->get("TblApproval")->row();
					if($getTblApproval!=null){

						if($getTblApproval->ApprovedBy!=$user['UserEmail']){
							$dataAppproval = array(
								'ApprovedBy' => $user['UserEmail'],
							);
							$this->db->where($whereApproval);
							$this->db->update('TblApproval',$dataAppproval);
						}
					}

					$whereApproval = array(
						"RequestBy" => $oldEmail, 
						"ApprovedDate IS NULL" => null,
						"ApprovalStatus" => 'UNPROCESSED',
						"IsCancelled" => 0,
						"ExpiryDate > getdate()" => null,

					);
					$this->db->select("RequestBy");
					$this->db->where($whereApproval);
					$getTblApproval = $this->db->get("TblApproval")->row();
					if($getTblApproval!=null){
						if($getTblApproval->RequestBy!=$user['UserEmail']){
							$dataAppproval = array(
								'RequestBy' => $user['UserEmail'],
							);
							$this->db->where($whereApproval);
							$this->db->update('TblApproval',$dataAppproval);
						}
					}

					
					//update email tb_salesman
					$this->db->where('userid',$user["UserID"]);
					$this->db->set('useremail',$newEmail);
					$this->db->update('tb_salesman');

				}
			}

			//5 jika zen_UserID tidak kosong update datanya
			//update by UserID
			if($user["UserID"]!='' && $user['zen_UserID']!='' && $user['zen_USEREMAIL']!='' && $user['zen_NAME']!='' ){
				$this->db->where("AlternateID", $user["UserID"]);
				$this->db->set('AlternateID',$user['zen_UserID']);
				$this->db->set('UserEmail',$user['zen_USEREMAIL']);
				$this->db->set('UserName',$user['zen_NAME']);
				$this->db->update('msuserhd');
			}
			$this->db->trans_complete();
			return true;
		}
		
		function GetBranch($GROUPID){
			$str = "
			SELECT DISTINCT Ms_Group.Name AS GroupName, Ms_Group.BranchID, Ms_Branch.BranchName
			FROM Ms_Group
			INNER JOIN Ms_Branch ON Ms_Group.BranchID = Ms_Branch.BranchID
			WHERE (Ms_Group.GroupID = '".$GROUPID."')";
			$res = $this->db->query($str);
		    if($res->num_rows() > 0)
				return $res->row();
			else
				return array();
		}
		
		function CekUserID($USERID, $USEREMAIL ){
			$str = "
				SELECT UserID
				FROM msuserhd
				WHERE (UserID = '".$USERID."') AND (UserEmail <> '".$USEREMAIL."') AND (IsActive=1)";
			// die($str);
			$res = $this->db->query($str);
		    if($res->num_rows() > 0)
				return $res->row();
			else
				return null;
		}

		function SaveProfile($data){
			$this->db->where('UserEmail',$_SESSION["logged_in"]["useremail"]);
			$this->db->set('Email',$data["Email"]);
			$this->db->set('Whatsapp',$data["Whatsapp"]);
			$this->db->Update('msuserhd');
		}

		function resetPassword($email,$newpassword){
			$this->db->where('UserEmail',$email);
			$this->db->set('setpass',1);
			$this->db->set('UserPassword',md5($newpassword));
			$this->db->Update('msuserhd');
		}
		function resetPasswordByUserId($userId,$newpassword){
			$this->db->where('UserID',$userId);
			$this->db->set('setpass',1);
			$this->db->set('UserPassword',md5($newpassword));
			$this->db->Update('msuserhd');
		}

		function setNewPass($email,$newpassword){
			$this->db->where('UserEmail',$email);
			$this->db->set('setpass',0);
			$this->db->set('UserPassword',md5($newpassword));
			$this->db->Update('msuserhd');
		}

		function getTbEmployeeHd($useremail){
			$this->db->select('*');
			$this->db->from('USERINFO');
			$this->db->where('UserEmail', $useremail);
			return $this->db->get()->row();
		}
		
		function getRoleUser($useremail){
			$this->db->select("*");
			$this->db->from("tb_user_dt");
			$this->db->join("msuserhd", "tb_user_dt.UserEmail = msuserhd.UserEmail");
			$this->db->join("tb_role_hd", "tb_user_dt.role_id = tb_role_hd.role_id");
			$this->db->where("tb_user_dt.useremail='".$useremail."' or msuserhd.AlternateID='".$useremail."'");
			//die(json_encode($this->db->get()->result()));
			return $this->db->get()->result();
		}

		function UpdateColumn($userid="0", $nm_col="", $value="")
		{
			$str = "UPDATE msuserhd SET ".$nm_col." = '".$value."' WHERE isnull(AlternateID,'0')='".$userid."'";
			$upd = $this->db->query($str);

			$str = "SELECT * FROM msuserhd WHERE AlternateID='".$userid."' and ".$nm_col."='".$value."'";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return true;
			} else {
				return false;
			}
		}

		function UpdateUserIDv2($useremail="")
		{
			$str = "SELECT * FROM USERINFO WHERE UserEmail='".$useremail."'";
			$res = $this->db->query($str);
			$USERID = 0;

			if ($res->num_rows()>0) {
				$USERID = $res->row()->USERID;
				$upd = "UPDATE msuserhd SET UserID='".$USERID."' WHERE UserEmail='".$useremail."'";
				$this->db->query($upd);
				return $USERID;
			} else {
				return 0;
			}

		} 

		function getuserListByRole($roleId){
			$str = "select R.UserID as user_id,
			U.UserName as user_name ,
			ISNULL(U.GroupName,'') as group_name  from tb_user_dt R
					  inner join msuserhd U 
					  on R.UserID = U.AlternateID 
					  where R.role_id = '".$roleId."'
					  order By u.UserName asc ";
			$res = $this->db->query($str);
			if ($res->num_rows()>0)
				return $res->result();
			else
				return array();
		}
		function getAlluserList($roleId,$search){
			$str = "select Top 10 U.AlternateID as user_id,
					U.UserName as user_name  
					from msuserhd U where U.AlternateID is not null and U.AlternateID != ''
					and U.AlternateID not in (select UserID as userid from tb_user_dt where role_id = 'ROLE01' and UserId Is not null)
					and (AlternateID like '%".$search."%' OR UserName like '%".$search."%')
					order By U.UserName asc "; 
			$res = $this->db->query($str);
			if ($res->num_rows()>0)
				return $res->result();
			else
				return array();
		}

		// function insert_activity($act)
		// {
		// 	$this->db->trans_start();

		// 	$this->db->set('LogDate',$act["LogDate"]);
		// 	$this->db->set('UserID',$_SESSION["logged_in"]["userid"]);
		// 	$this->db->set('UserName',$_SESSION["logged_in"]["username"]);
		// 	$this->db->set('UserEmail',$_SESSION["logged_in"]["useremail"]);
		// 	$this->db->set('TrxID',date("YmdHis", strtotime($act["LogDate"])));
		// 	$this->db->set('Module',$act["Module"]);
		// 	$this->db->set('Description',$act["Description"]);
		// 	$this->db->set('Remarks',$act["Remarks"]);
		// 	$this->db->set('RemarksDate',date("Y-m-d H:i:s"));
		// 	$this->db->insert('Log_Activity');

		// 	$this->db->trans_complete();
		// }

		// function update_activity($act)
		// {
		// 	$this->db->trans_start();

		// 	$this->db->where('LogDate',$act["LogDate"]);
		// 	$this->db->where('TrxID',date("YmdHis", strtotime($act["LogDate"])));
		// 	$this->db->where('Module',$act["Module"]);
		// 	$this->db->set('UserID',$_SESSION["logged_in"]["userid"]);
		// 	$this->db->set('UserName',$_SESSION["logged_in"]["username"]);
		// 	$this->db->set('UserEmail',$_SESSION["logged_in"]["useremail"]);
		// 	$this->db->set('Description',$act["Description"]);
		// 	$this->db->set('Remarks',$act["Remarks"]);
		// 	$this->db->set('RemarksDate',date("Y-m-d H:i:s"));
		// 	$this->db->update('Log_Activity');

		// 	$this->db->trans_complete();
		// }
	}
?>