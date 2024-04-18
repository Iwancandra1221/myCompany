<?php
class LaporanPenjualanNasionalModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$CI = &get_instance();
	}

	// get AlamatWebService utk baca DB BHAKTI cabang yang diinginkan
	// function GetList($report_opt=''){
		// $where = ($report_opt=='') ? "ReportOpt" : "'".$report_opt."'";
		// $res = $this->db->query("SELECT WilayahGroup, RTRIM(Wilayah) as Wilayah, RTRIM(Kota) as Kota From TblConfigReport_Wilayah WHERE ReportOpt=".$where."");
		// if ($res->num_rows()>0)
			// return $res->result();
		// else
			// return null;
	// }
	
	// get AlamatWebService utk baca DB BHAKTI cabang yang diinginkan
	function edit($id){
		$res = $this->db->query("Select * From TblConfigReport_Wilayah WHERE urut = '".$id."'");
		if ($res->num_rows()>0)
			return $res->result();
		else
			return null;
	}
	
	// get AlamatWebService utk baca DB BHAKTI cabang yang diinginkan
	// function save($id){
	function save($data){
		$this->db->set('branch_id',$data['BranchID']);
		$this->db->set('branch_name',$data['BranchName']);
		$this->db->set('branch_code',$data['BranchCode']);
		if($data['IsActive'] == 'true')
			$this->db->set('is_active',1);
		else
			$this->db->set('is_active',0);
		$this->db->set('created_by',$this->session->userdata('user'));
		$this->db->set('created_date',date('Y-m-d H:i:s'));
		$this->db->set('updated_by',$this->session->userdata('user'));
		$this->db->set('updated_date',date('Y-m-d H:i:s'));
		$this->db->insert('tb_branch');
	}
	// get AlamatWebService utk baca DB BHAKTI cabang yang diinginkan
	function update($data){
		$this->db->trans_start();
		$this->db->where('branch_id',$data['BranchID']);
		$this->db->set('branch_name',$data['BranchName']);
		$this->db->set('branch_code',$data['BranchCode']);
		if($data['IsActive'] == 'true')
			$this->db->set('is_active',1);
		else
			$this->db->set('is_active',0);
		$this->db->set('branch_head',$data['BranchHead']);
		$this->db->set('updated_by',$this->session->userdata('user'));
		$this->db->set('updated_date',date('Y-m-d H:i:s'));
		$this->db->update('tb_branch');
		$this->db->trans_complete();
	}
	// get AlamatWebService utk baca DB BHAKTI cabang yang diinginkan
	function delete($id){
		$this->db->where('branch_id',$data);
		return $this->db->delete('tb_branch');
	}
}
?>
	    