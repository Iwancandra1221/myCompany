<?php
	Class MsBarangModel extends CI_Model
	{

		function getList(){
	 	 	$this->db->select('*');
		    $this->db->from('tbmc_salesman_mapping');  
		    $this->db->order_by('branch_id', 'ASC');
		    $this->db->order_by('useremail', 'ASC');
		    $this->db->order_by('kd_slsman', 'ASC');
		    return $this->db->get()->result();
		}
		
		function get($kdslsman){
	 	 	$this->db->select('*');
		    $this->db->from('tbmc_salesman_mapping');
		    $this->db->where('kd_slsman', $kdslsman);
		    return $this->db->get()->result();
		 }

		function addData($data){
	   		$this->db->insert('tbmc_salesman_mapping', $data);
		}

		function updateData($data,$kdlsman){
			$this->db->where('kd_slsman', $kdlsman);
	   		$this->db->update('tbmc_salesman_mapping', $data);
		}

		function deleteData($kdslsman){
   			$this->db->where('kd_slsman', $kdslsman);
  			$this->db->delete('tbmc_salesman_mapping');
		}

		
		function getProdukList(){
	 	 	$this->db->select('*');
		    $this->db->from('TblINHeader');
		    return $this->db->get()->result();
		 }
		
		function getSparepartList(){
	 	 	$this->db->select('*');
		    $this->db->from('TblHeaderInSp');
		    return $this->db->get()->result();
		 }
		

	}
?>