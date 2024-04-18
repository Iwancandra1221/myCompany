<?php
class HelperModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function GetNamaBulan($bl=0)
	{
		$array_bulan = array("Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Des");
		return $array_bulan[$bl];
	}

	function GetNmPeriode($th, $bl, $periode)
	{
		// $NmPeriode = "P".$periode." BL".$bl." TH".substr($th,-2);
		$NmPeriode = "P".$periode." ".$this->GetNamaBulan($bl-1)." ".substr($th,-2);
		return $NmPeriode;
	}

	function GetMonths()
	{
		$qry = "Select * From tb_month_helper order by month";
		$res = $this->db->query($qry);
		if($res->num_rows()>0)
			return $res->result();
		else
			return array();
	}

	function MobileWithCountryCode($mobile)
	{
		$WA = $mobile;
		if ($WA=="" || $WA==null) {
			return "";
		} else if (substr($WA,0,2)=="62") {
			return $WA;
		} else if (substr($WA,0,1)=="0") {
			$len_wa = strlen($WA) * -1;
			$WA = substr($WA, $len_wa);
			$WA = "62".$WA;
			return $WA;
		} else if (substr($WA,0,2)!="62") {
			$WA = "62".$WA;
			return $WA;
		}
	}
}
?>
