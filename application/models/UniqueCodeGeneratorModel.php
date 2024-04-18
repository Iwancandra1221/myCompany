 <?php
	Class UniqueCodeGeneratorModel extends CI_Model
	{
		function __construct()
		{
			parent::__construct();
		}

		function getListSN(){
			$qry = "SELECT * FROM Log_UniqueCode Where ISNULL(isHide,0) = 0  ";
			if ($_SESSION["logged_in"]["isUserPabrik"]==1) {
				$qry .= " and CreatedBy = '".$_SESSION["logged_in"]["useremail"]."' ";
			}
			$qry .= "order by LogDate Desc ";
		    $res = $this->db->query($qry);

		    if ($res->num_rows()>0)
		    	return $res->result();
		    else
		    	return array();
		}
		
		function getProductIDByLogId($LogId = '')
		{
			$str = "SELECT SerialNoMin+' | '+SerialNoMax+' | '+ProductID as text FROM Log_UniqueCode WHERE LogId='".$LogId."'";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return $res->row()->text;
			} else {
				return null;
			}
		}
		
		function select2($search = '')
		{
			$str = "SELECT top 10 LogId as id, SerialNoMin+' | '+SerialNoMax+' | '+ProductID as text FROM Log_UniqueCode WHERE (SerialNoMin+' | '+SerialNoMax+' | '+ProductID LIKE '%".$search."%') ORDER BY SerialNoMin+' | '+SerialNoMax+' | '+ProductID";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return $res->result_array();
			} else {
				return array();
			}
		}

		// function GetListSN2($data=''){

		// 	$data_list=array();


		// 	$SortCol='LogDate';
		// 	$SortDir='Desc';


		// 	if(!empty($data['iSortCol_0'])){
		// 		if($data['iSortCol_0']==0){
		// 			$SortCol='LogId';
		// 		}else if($data['iSortCol_0']==1){
		// 			$SortCol='LogDate';
		// 		}else if($data['iSortCol_0']==2){
		// 			$SortCol='CreatedBy';
		// 		}else if($data['iSortCol_0']==3){
		// 			$SortCol='SerialNoMin';
		// 		}else if($data['iSortCol_0']==4){
		// 			$SortCol='SerialNoMax';
		// 		}else if($data['iSortCol_0']==5){
		// 			$SortCol='ProductID';
		// 		}else if($data['iSortCol_0']==6){
		// 			$SortCol='Description';
		// 		}else if($data['iSortCol_0']==7){
		// 			$SortCol='LogId,LogDate,CreatedBy,SerialNoMin,SerialNoMax,ProductID';
		// 		}
		// 	}

		// 	if(!empty($data['sSortDir_0'])){
		// 		$SortDir=$data['sSortDir_0'];
		// 	}


		// 	$page=0;
		// 	if(!empty($data['iDisplayStart'])){
		// 		$page=$data['iDisplayStart'];
		// 	}

		// 	$total_data_view=10;
		// 	if(!empty($data['iDisplayLength'])){
		// 		$total_data_view=$data['iDisplayLength'];
		// 	}

		// 	$query = "SELECT * FROM Log_UniqueCode Where ISNULL(isHide,0) = 0  ";
		// 	if ($_SESSION["logged_in"]["isUserPabrik"]==1) {
		// 		$query .= " and CreatedBy = '".$_SESSION["logged_in"]["useremail"]."' ";
		// 	}

		// 	if(!empty($data['sSearch'])){

		// 		$query  .= " and (LogId LIKE '%".$data['sSearch']."%' OR LogDate LIKE '%".$data['sSearch']."%' OR CreatedBy LIKE '%".$data['sSearch']."%' OR SerialNoMin LIKE '%".$data['sSearch']."%' OR SerialNoMax LIKE '%".$data['sSearch']."%' OR ProductID LIKE '%".$data['sSearch']."%' OR Description LIKE '%".$data['sSearch']."%')";

		// 	}

		// 	$resjum=$this->db->query($query);

			
		// 	$query .=" order by ".$SortCol." ".$SortDir." OFFSET ".$page." ROWS FETCH NEXT ".$total_data_view." ROWS ONLY";
		// 	$res=$this->db->query($query);
		// 	if($res->num_rows() > 0){

		// 		$hasildata['total']=$resjum->num_rows();
		// 		$hasildata['data']=$res->result();
		// 		return $hasildata;

		// 	}else{
		// 		return array();
		// 	}

		// }


		function Hide($UCID,$Ket)
		{
			$qry = "UPDATE Log_UniqueCode SET isHide=1,deleted_by='".$_SESSION["logged_in"]["username"]."',deleted_date='".date('Y-m-d h:i:s')."',reason_deleted='".$Ket."' WHERE LogId=".$UCID;
 			$res = $this->db->query($qry);
 			return $res;
		}

		function getLogUniqueCode($where){
			$this->db->where($where);
			$result = $this->db->get('Log_UniqueCode')->result_array();
			return $result;
		}

		function updateLogUniqueCode($where,$data){
			$this->db->trans_start(); 

			$this->db->where($where);
			$this->db->update('Log_UniqueCode',$data);

			$result = $this->db->trans_complete(); # Completing transaction
			if ($this->db->trans_status() === FALSE) {
			    $this->db->trans_rollback();
			} 
			else {
			    $this->db->trans_commit();
			}
			return $result;
		}

		function DeletedList($data=''){
			// $qry = "select LogId,brand,ProductID,SerialNoMin,SerialNoMax,reason_deleted,deleted_date,deleted_by from Log_UniqueCode where isHide=1";
 			// $res = $this->db->query($qry);
 			// if ($res->num_rows()>0) {
 			// 	return(array("result"=>"SUCCESS", "data"=>$res->result()));
 			// } else {
 			// 	return(array("result"=>"FAILED", "data"=>array()));
 			// }
 			$data_list=array();


			$SortCol='LogId';
			$SortDir='Desc';


			if(!empty($data['iSortCol_0'])){
				if($data['iSortCol_0']==0){
					$SortCol='LogId';
				}else if($data['iSortCol_0']==1){
					$SortCol='brand';
				}else if($data['iSortCol_0']==2){
					$SortCol='ProductID';
				}else if($data['iSortCol_0']==3){
					$SortCol='SerialNoMin';
				}else if($data['iSortCol_0']==4){
					$SortCol='SerialNoMax';
				}else if($data['iSortCol_0']==5){
					$SortCol='reason_deleted';
				}else if($data['iSortCol_0']==6){
					$SortCol='deleted_by';
				}else if($data['iSortCol_0']==7){
					$SortCol='LogId,brand,ProductID,SerialNoMin,SerialNoMax,reason_deleted,deleted_by';
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

			$query = "select LogId,brand,ProductID,SerialNoMin,SerialNoMax,reason_deleted,deleted_date,deleted_by from Log_UniqueCode where isHide=1  ";
			if ($_SESSION["logged_in"]["isUserPabrik"]==1) {
				$query .= " and CreatedBy = '".$_SESSION["logged_in"]["useremail"]."' ";
			}

			if(!empty($data['sSearch'])){

				$query  .= " and (LogId LIKE '%".$data['sSearch']."%' OR deleted_date LIKE '%".$data['sSearch']."%' OR deleted_by LIKE '%".$data['sSearch']."%' OR SerialNoMin LIKE '%".$data['sSearch']."%' OR SerialNoMax LIKE '%".$data['sSearch']."%' OR ProductID LIKE '%".$data['sSearch']."%' OR reason_deleted LIKE '%".$data['sSearch']."%' OR brand LIKE '%".$data['sSearch']."%')";

			}

			$resjum=$this->db->query($query);

			
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
		
		function saveLog($data, $versiSALT, $ResponseCode, $ResponseText)
		{
			$this->db->set("LogDate", date("Y-m-d h:i:s"));
			$this->db->set("CreatedBy", $data["username"]);
			$this->db->set("SerialNoMin", strtoupper($data["serialnumber-min"]));
			$this->db->set("SerialNoMax", strtoupper($data["serialnumber-max"]));
			$this->db->set("ProductID", strtoupper($data["productcode"]));
			$this->db->set("brand", strtoupper($data["productbrand"]));
			$this->db->set("SALTversion", $versiSALT);
			$this->db->set("ResponseCode", $ResponseCode);
			$this->db->set("Description", $ResponseText);
			$this->db->insert("Log_UniqueCode");
		}

		function checkLog($data)
		{		  
			// $qry = "SELECT * FROM Log_UniqueCode 
					// WHERE ProductID like '".$data["productId"]."%' 
						// and (SerialNoMin between '".$data["serialMin"]."' and '".$data["serialMax"]."' 
						  // or SerialNoMax between '".$data["serialMin"]."' and '".$data["serialMax"]."' 
						  // or '".$data["serialMin"]."' between SerialNoMin and SerialNoMax 
						  // or '".$data["serialMax"]."' between SerialNoMin and SerialNoMax) ";
						  
			$qry = "SELECT * FROM Log_UniqueCode 
					WHERE (ProductID like '".$data["productId"]." | %' OR ProductID='".$data["productId"]."') 
						and isHide=0
						and (SerialNoMin between '".$data["serialMin"]."' and '".$data["serialMax"]."' 
						  or SerialNoMax between '".$data["serialMin"]."' and '".$data["serialMax"]."' 
						  or '".$data["serialMin"]."' between SerialNoMin and SerialNoMax 
						  or '".$data["serialMax"]."' between SerialNoMin and SerialNoMax) ";
 			$res = $this->db->query($qry);
 			if ($res->num_rows()>0) {
 				return(array("result"=>"FAILED", "logs"=>$res->result()));
 			} else {
 				return(array("result"=>"SUCCESS", "logs"=>array()));
 			}
		}

		function BlacklistPesanGetList($aktif = '', $id=''){

			$qry = "SELECT * FROM tb_blacklist_pesan WHERE ISNULL(IsDeleted,0) = 0 ";
			if($aktif!=''){
				$qry .= " AND IsActive=".$aktif;
			}
			if($id!=''){
				$qry .= " AND ID='".$id."'";
			}
			$qry .= " ORDER BY CreatedDate ASC ";
		    $res = $this->db->query($qry);

		    if ($res->num_rows()>0){ 
				if($id!='') 
					return $res->row();
				else
					return $res->result();

			}
		    else
		    	return array();
		}


		function BlacklistPesanSave($pesan){
			$this->db->trans_start();
			$this->db->set("Pesan", strtoupper($pesan));
			$this->db->set("CreatedBy", $_SESSION['logged_in']['username']);
			$this->db->set("CreatedDate", date("Y-m-d h:i:s"));
			$this->db->set("IsActive", 1);
			$this->db->insert("tb_blacklist_pesan");
			$this->db->trans_complete();

           if ($this->db->trans_status() === FALSE) {
               return false;
           }
            return true;
		}

		function BlacklistPesanUpdate($post){
			$this->db->trans_start();
			$this->db->where("ID", $post['ID']);
			$this->db->set("Pesan", strtoupper($post['Pesan']));
			$this->db->set("ModifiedBy", $_SESSION['logged_in']['username']);
			$this->db->set("ModifiedDate", date("Y-m-d h:i:s"));
			$this->db->set("IsActive", $post['IsActive']);
			$this->db->update("tb_blacklist_pesan");
			$this->db->trans_complete();

           if ($this->db->trans_status() === FALSE) {
               return false;
           }
            return true;
		}

		function BlacklistPesanDelete($id){
			$this->db->trans_start();
			$this->db->where("ID", $id);
			$this->db->set("ModifiedBy", $_SESSION['logged_in']['username']);
			$this->db->set("ModifiedDate", date("Y-m-d h:i:s"));
			$this->db->set("IsDeleted", 1);
			$this->db->set("IsActive", 0);
			$this->db->update("tb_blacklist_pesan");
			$this->db->trans_complete();

           if ($this->db->trans_status() === FALSE) {
               return false;
           }
            return true;
		}

		function GetListSN2($data=''){

			$data_list=array();


			$SortCol='LogDate';
			$SortDir='Desc';


			if(!empty($data['iSortCol_0'])){
				if($data['iSortCol_0']==0){
					$SortCol='LogId';
				}else if($data['iSortCol_0']==1){
					$SortCol='LogDate';
				}else if($data['iSortCol_0']==2){
					$SortCol='CreatedBy';
				}else if($data['iSortCol_0']==3){
					$SortCol='SerialNoMin';
				}else if($data['iSortCol_0']==4){
					$SortCol='SerialNoMax';
				}else if($data['iSortCol_0']==5){
					$SortCol='ProductID';
				}else if($data['iSortCol_0']==6){
					$SortCol='brand';
				}else if($data['iSortCol_0']==7){
					$SortCol='Description';
				}else if($data['iSortCol_0']==8){
					$SortCol='LogId,LogDate,CreatedBy,SerialNoMin,SerialNoMax,ProductID';
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

			$query = "SELECT * FROM Log_UniqueCode Where ISNULL(isHide,0) = 0  ";
			if ($_SESSION["logged_in"]["isUserPabrik"]==1) {
				$query .= " and CreatedBy = '".$_SESSION["logged_in"]["useremail"]."' ";
			}

			if(!empty($data['sSearch'])){

				$query  .= " and (LogId LIKE '%".$data['sSearch']."%' OR LogDate LIKE '%".$data['sSearch']."%' OR CreatedBy LIKE '%".$data['sSearch']."%' OR SerialNoMin LIKE '%".$data['sSearch']."%' OR SerialNoMax LIKE '%".$data['sSearch']."%' OR ProductID LIKE '%".$data['sSearch']."%' OR Description LIKE '%".$data['sSearch']."%')";

			}

			$resjum=$this->db->query($query);

			
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
	}
?>