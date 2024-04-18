<?php
class ReportFinanceModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function GetListPartnerType($exclude_bass=1)
	{
		$str = "SELECT ConfigValue as partner_type FROM ms_config WHERE ConfigType='MASTER' AND ConfigName='PARTNER TYPE'";
		if($exclude_bass==1){
			$str .= " AND ConfigValue <>'BASS'";
		}
		$str .= " ORDER BY ConfigName ";
		
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

}
?>
