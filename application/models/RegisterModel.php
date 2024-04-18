<?php
class RegisterModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}
	function getMsUserHd($where){
		$this->db->where($where);
		$result = $this->db->get('msuserhd')->row();
		return $result;
	}
	function insert($dataUserHd,$dataUserDt,$dataTbUserDt){

		$this->db->trans_start();

		$this->db->insert('msuserhd',$dataUserHd);
		$this->db->insert('msuserdt',$dataUserDt);
		$this->db->insert('tb_user_dt',$dataTbUserDt);

		$result = $this->db->trans_status();
		if($result) $this->db->trans_complete();
		else $this->db->trans_rollback();

		return $result;
	}
	function update($dataUserHd,$dataUserDt,$dataTbUserDt,$where){
		$this->db->trans_start();

		$this->db->where($where);
		$this->db->update('msuserhd',$dataUserHd);
		$this->db->flush_cache();

		$this->db->where($where);
		$this->db->update('msuserdt',$dataUserDt);
		$this->db->flush_cache();

		$this->db->where($where);
		$this->db->update('tb_user_dt',$dataTbUserDt);
		$this->db->flush_cache();

		$result = $this->db->trans_status();
		if($result) $this->db->trans_complete();
		else $this->db->trans_rollback();

		return $result;
	}
}
