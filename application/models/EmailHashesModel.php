<?php
class EmailHashesModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();		
		$CI = &get_instance();
	}
	function addHashes($data){
		$this->db->trans_start();

		$this->db->insert('email_hashes',$data);

		$result = $this->db->trans_status();
		if ($result === FALSE) {
			$this->db->trans_rollback();
		}
		else{
			$this->db->trans_commit();
		}
		return $result;
	}
	function editHashes($data,$where){
		$this->db->trans_start();

		$this->db->where($where);
		$this->db->update('email_hashes',$data);

		$result = $this->db->trans_status();
		if ($result === FALSE) {
			$this->db->trans_rollback();
		}
		else{
			$this->db->trans_commit();
		}
		return $result;
	}
	function getHashes($where){
		$this->db->where($where);
		$result = $this->db->get('email_hashes')->row();
		return $result;
	}
}
?>
