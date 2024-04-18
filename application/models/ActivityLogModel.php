<?php
class ActivityLogModel extends CI_Model{
	public function __construct(){
		parent::__construct();
	}

	function insert_activity($act)
	{
		$this->db->trans_start();

		$this->db->set('LogDate',date("Y-m-d H:i:s", strtotime($act["LogDate"])));
		$this->db->set('UserID',$act["UserID"]);
		$this->db->set('UserName',$act["UserName"]);
		$this->db->set('UserEmail',$act["UserEmail"]);
		$this->db->set('Module',$act["Module"]);
		$this->db->set('TrxID',$act["TrxID"]);
		$this->db->set('Description',$act["Description"]);
		$this->db->set('Remarks',$act["Remarks"]);
		$this->db->set('RemarksDate',date("Y-m-d H:i:s", strtotime($act["RemarksDate"])));
		$this->db->set('URL',$_SERVER['HTTP_HOST']);
		$this->db->insert('Log_Activity');

		$this->db->trans_complete();
    }
  
    public function update_activity($act)
    {
    	$this->db->where('LogDate',date("Y-m-d H:i:s", strtotime($act["LogDate"])));
		$this->db->where('Module',$act["Module"]);
		$this->db->where('TrxID',$act["TrxID"]);
		$this->db->set('Remarks',$act["Remarks"]);
		$this->db->set('RemarksDate',date("Y-m-d H:i:s", strtotime($act["RemarksDate"])));
		$this->db->update('Log_Activity');
  	}

  	public function module(){
		$this->db->select('distinct Module');
		$res = $this->db->get('Log_Activity');
		if($res->num_rows() > 0){
			return $res->result();
		}else{
			return array();
		}
	}

	public function dataactivitylog($data=''){
		$data_list=array();
		
		$page=0;
		if(!empty($data['iDisplayStart'])){
			$page=$data['iDisplayStart'];
		}


		$SortCol='LogDate';
		$SortDir='desc';


		if(!empty($data['iSortCol_0'])){
			if($data['iSortCol_0']==0){
				$SortCol='LogDate';
			}else if($data['iSortCol_0']==1){
				$SortCol='Module';
			}else if($data['iSortCol_0']==2){
				$SortCol='Description';
			}else if($data['iSortCol_0']==3){
				$SortCol='Remarks';
			}else if($data['iSortCol_0']==4){
				$SortCol='RemarksDate';
			}else if($data['iSortCol_0']==5){
				$SortCol='selisih';
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

		$query_jum = "select * from Log_Activity where ";
		if(!empty($data['sSearch_0']) && !empty($data['bRegex_0'])){
			$query_jum  .= "LogDate>='".$data['sSearch_0']." 00:00:00' and LogDate<='".$data['bRegex_0']." 23:59:59'";
		}else{
			$query_jum  .= "LogDate>'".date('Y-m-d 00:00:00')."' and LogDate<'".date('Y-m-d H:i:s')."'";
		}

		if(!empty($data['sSearch_1'])){
			$query_jum  .= " and Module='".$data['sSearch_1']."'";
		}

		if(!empty($data['sSearch'])){
			$query_jum  .= " and (LogDate LIKE '%".$data['sSearch']."%' OR Module LIKE '%".$data['sSearch']."%' OR Description LIKE '%".$data['sSearch']."%' OR Remarks LIKE '%".$data['sSearch']."%' OR RemarksDate LIKE '%".$data['sSearch']."%')";
		}


		$resjum=$this->db->query($query_jum);



		$query  = "select LogDate,Module,Description,Remarks,RemarksDate, DATEDIFF (minute,LogDate,RemarksDate) as selisih from Log_Activity where ";


		if(!empty($data['sSearch_0']) && !empty($data['bRegex_0'])){
			$query  .= "LogDate>='".$data['sSearch_0']." 00:00:00' and LogDate<='".$data['bRegex_0']." 23:59:59'";
		}else{
			$query  .= "LogDate>'".date('Y-m-d 00:00:00')."' and LogDate<'".date('Y-m-d H:i:s')."'";
		}

		if(!empty($data['sSearch_1'])){
			$query  .= " and Module='".$data['sSearch_1']."'";
		}

		if(!empty($data['sSearch'])){
			$query  .= " and (LogDate LIKE '%".$data['sSearch']."%' OR Module LIKE '%".$data['sSearch']."%' OR Description LIKE '%".$data['sSearch']."%' OR Remarks LIKE '%".$data['sSearch']."%' OR RemarksDate LIKE '%".$data['sSearch']."%')";
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
  
}

?>