 <?php
	Class MasterReportWilayahModel extends CI_Model
	{
		function __construct()
		{
			parent::__construct();
		}

		function getList($report_opt='', $grup=''){
			$report_opt = ($report_opt=='') ? "%" : $report_opt;
			$grup 		= ($grup=='') ? "%" : $grup;
			
			$qry = "SELECT * FROM TblConfigReport_Wilayah WHERE ReportOpt LIKE '".$report_opt."' AND Grup LIKE '".$grup."' ORDER BY ReportOpt, WilayahGroup, PartnerType, Wilayah, Kota";
			// die($qry);
		    $res = $this->db->query($qry);

		    if ($res->num_rows()>0){
		    	return $res->result();
		    }
		    else{
		    	return array();
		    }
		}

		function getListData($data){

			$data_list=array();
			
			$page=0;
			if(!empty($data['iDisplayStart'])){
				$page=$data['iDisplayStart'];
			}


			$SortCol='ReportOpt';
			$SortDir='asc';


			if(!empty($data['iSortCol_0'])){
				if($data['iSortCol_0']==1){
					$SortCol='ReportOpt';
				}else if($data['iSortCol_0']==2){
					$SortCol='WilayahGroup';
				}else if($data['iSortCol_0']==3){
					$SortCol='PartnerType';
				}else if($data['iSortCol_0']==4){
					$SortCol='Wilayah';
				}else if($data['iSortCol_0']==5){
					$SortCol='Kota';
				}else if($data['iSortCol_0']==6){
					$SortCol='ModifiedBy';
				}else if($data['iSortCol_0']==7){
					$SortCol='ModifiedDate';
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



			$query_jum  = "SELECT * FROM TblConfigReport_Wilayah";

			if(!empty($data['sSearch'])){

				$query_jum  .= " WHERE (Grup LIKE '%".$data['sSearch']."%' OR WilayahGroup LIKE '%".$data['sSearch']."%' OR PartnerType LIKE '%".$data['sSearch']."%' OR Wilayah LIKE '%".$data['sSearch']."%' OR Kota LIKE '%".$data['sSearch']."%' OR ReportOpt LIKE '%".$data['sSearch']."%')";

				if(!empty($data['sSearch_0'])){
					$query_jum  .= " and ReportOpt LIKE '%".$data['sSearch_0']."%'";
				}
			}else if(!empty($data['sSearch_0'])){
				$query_jum  .= " WHERE ReportOpt LIKE '%".$data['sSearch_0']."%'";
			}


			$resjum=$this->db->query($query_jum);



			$query  = "SELECT *,id as idconfig FROM TblConfigReport_Wilayah";

			if(!empty($data['sSearch'])){

				$query  .= " WHERE (Grup LIKE '%".$data['sSearch']."%' OR WilayahGroup LIKE '%".$data['sSearch']."%' OR PartnerType LIKE '%".$data['sSearch']."%' OR Wilayah LIKE '%".$data['sSearch']."%' OR Kota LIKE '%".$data['sSearch']."%' OR ReportOpt LIKE '%".$data['sSearch']."%')";

				if(!empty($data['sSearch_0'])){
					$query  .= " and ReportOpt LIKE '%".$data['sSearch_0']."%'";
				}
			}else if(!empty($data['sSearch_0'])){
				$query  .= " WHERE ReportOpt LIKE '%".$data['sSearch_0']."%'";
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
		}

		function getOptWilayahGroup($opt='PENJUALAN NASIONAL'){
			$qry = "SELECT WilayahGroup FROM TblConfigReport_Wilayah WHERE (ReportOpt = '".$opt."') GROUP BY WilayahGroup ORDER BY WilayahGroup";
			// die($qry);
		    $res = $this->db->query($qry);

		    if ($res->num_rows()>0) 
		    	return $res->result();
		    else
		    	return array();
		}

		function get($id){
			$res = $this->db->query("SELECT * FROM TblConfigReport_Wilayah WHERE id=".$id);
		    if ($res->num_rows()>0)
		    	return $res->row();
		    else
		    	return null;
		}

		function get2($data){
			$str = "SELECT * FROM TblConfigReport_Wilayah 
					WHERE ReportOpt='".$data["ReportOpt"]."'
					and Grup='".$data["Grup"]."'
					and WilayahGroup='".$data["WilayahGroup"]."'
					and Wilayah='".$data["Wilayah"]."'
					and Kota='".(($data["Kota"]=="")?"ALL":$data["Kota"])."' "; 

			$res = $this->db->query($str);
		    if ($res->num_rows()>0)
		    	return $res->row();
		    else
		    	return null;
		}

		function add($data){
		
			// print_r($data);die;
		
			$this->db->set("ReportOpt", $data["ReportOpt"]);
			$this->db->set('Grup', $data["Grup"]);
			$this->db->set('WilayahGroup', $data["WilayahGroup"]);
			$this->db->set('Wilayah', $data["Wilayah"]);
			$this->db->set('Kota', (($data["Kota"]=="")?"ALL":$data["Kota"]));
			$this->db->set('PartnerType', $data["PartnerType"]);
			$this->db->set('IsActive', $data["IsActive"]);
			$this->db->set('CreatedBy', $data["CreatedBy"]);
			$this->db->set('ModifiedBy', $data["ModifiedBy"]);
			$this->db->set('ModifiedDate',date('Y-m-d H:i:s'));
	   		$this->db->insert('TblConfigReport_Wilayah');

	   		$G = $this->get2($data);
	   		if ($G!=null) {
	   			return $G->id;
	   		} else {
	   			return 0;
	   		}
		}

		function update($data,$id){
			$this->db->where('id', $id);
			$this->db->set("ReportOpt", $data["ReportOpt"]);
			$this->db->set('Grup', $data["Grup"]);
			$this->db->set('WilayahGroup', $data["WilayahGroup"]);
			$this->db->set('Wilayah', $data["Wilayah"]);
			$this->db->set('Kota', (($data["Kota"]=="")?"ALL":$data["Kota"]));
			$this->db->set('PartnerType', $data["PartnerType"]);
			$this->db->set('IsActive', $data["IsActive"]); 
			$this->db->set('ModifiedBy', $data["ModifiedBy"]);
			$this->db->set('ModifiedDate',date('Y-m-d H:i:s'));
	   		$this->db->update('TblConfigReport_Wilayah');
		}

		function delete($id){
   			$this->db->where('id', $id);
  			$this->db->delete('TblConfigReport_Wilayah');
		}
	}
?>