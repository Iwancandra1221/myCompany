<?php
class TunjanganPrestasiModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function ListData(){
		$res = $this->db->get('Mst_TunjanganPrestasi_v2');
		if ($res->num_rows()>0){
			return $res->result();
		}else{
			return array();
		}
	}

	function Crud($data) {
		//die(print_r($data));
		$ERR_CODE=0;
		$ERR_MSG="";

		$params = array(
			"EmpPositionID"=> $data->Level_Slsman,
			"BranchCode"=> $data->Wil_Slsman,
			"StartDate" => date("Y-m-d", strtotime($data->Start_Date)),
			"TPOmzet" => $data->TP_Omzet,
			"TPOmzetMethod" => $data->TP_Omzet_Method,
			"TPOmzetMultiplier" => $data->TP_Omzet_Multiplier,
			"TPOmzetBobot" => $data->TP_Omzet_Bobot,
			"TPKPI" => $data->TP_KPI,
			"TPKPIMethod" => $data->TP_KPI_Method,
			"TPKPIMaxPercent" => $data->TP_KPI_Max_Percent,
			"TPKPIMultiplier" => $data->TP_KPI_Multiplier,
			"TPKPIBobot" =>  $data->TP_KPI_Bobot,
			"SkipPelunasan" => $data->SkipPelunasan,
			"PotonganDenda" => $data->PotonganDenda,
			"PembayaranSubsidi" => $data->PembayaranSubsidi
		);

		$where = array(
			"EmpPositionID"=> $data->Level_Slsman,
			"BranchCode"=> $data->Wil_Slsman,
		);

		$this->db->trans_start();

		switch ($data->proses) {
			case "new":
				$this->db->insert("Mst_TunjanganPrestasi_v2",$params);
				break;
			case "edit":
				$this->db->where($where);
				$this->db->update("Mst_TunjanganPrestasi_v2", $params);
				break;
			case "delete":
				$this->db->where($where);
				$this->db->delete("Mst_TunjanganPrestasi_v2");
				break;
		}

		if (($errors = sqlsrv_errors()) != null) {
			foreach ($errors as $error) {
				$ERR_CODE = $error["code"];
				$ERR_MSG .= "code: " . $ERR_CODE . "<br />";
				$ERR_MSG .= "message: " . $error['message'] . "<br />";
				$ERR_MSG .= "lastQuery: " . $this->db->last_query() . "<br />";
			}
		}
		
		if($ERR_MSG==''){
			$this->db->trans_complete();
			return(array("result"=>"success", "errMsg"=>'', "errCode"=>$ERR_CODE));
		}else{
			return (array("result" => "failed", "errMsg" => $ERR_MSG, "errCode" => $ERR_CODE));
		}
	}
}
?>