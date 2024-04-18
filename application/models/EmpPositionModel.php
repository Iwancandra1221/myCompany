<?php
class EmpPositionModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function GetDataList()
	{
		$res = $this->db->query("SELECT PositionID, Name, DivisionID FROM Ms_EmpPosition WHERE IsActive=1 Order BY DivisionID");

		if($res->num_rows() > 0)
			return $res->result();
		else
			return array();
	}	
}
?>
