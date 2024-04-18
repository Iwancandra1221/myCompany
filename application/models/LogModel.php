<?php
class LogModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function GetEmailLog($param)
	{
		//Kolom yg akan diselect (jika tidak ingin difilter, kolom ditaruh di ujung)
		$aColumns = array("BranchId", "LogDate", "ParamTo", "ParamCc", "ParamSubject",
		"CONVERT(VARCHAR, LogDate, 106)+' '+CONVERT(VARCHAR, LogDate, 108) as Tanggal",
		"CASE WHEN ISNULL(IsSent,0)=1 THEN 'SUKSES'
		WHEN ISNULL(IsSent,0)=0 AND ISNULL(StopRetry,0)=0 THEN 'PENDING'
		WHEN ISNULL(IsSent,0)=0 AND ISNULL(StopRetry,0)=1 THEN 'GAGAL'
		ELSE 'UNDEFINED' END as Status","LogId");
		
		$sTable = "Log_Email ";
		$sWhere = " (CONVERT(DATE, LogDate) BETWEEN '".date('Y-m-d', strtotime($param['startDate']))."' AND '".date('Y-m-d', strtotime($param['endDate']))."') ";
		$query  = DatatableQuery($param, $sTable, $aColumns, $sWhere);
		
		// echo json_encode($query);die;
		
		$res = $this->db->query($query['sQueryFiltered']);
		$iFilteredTotal = $res->num_rows();
			
		$no = $param['start'];
		$data = array();
		if ($iFilteredTotal>0){
			foreach($res->result_array() as $r){
				$row = array();
				$no++;
				$row[0]=$no;
				$row[1]=$r['BranchId'];
				$row[2]=date('d-M-Y H:i:s',strtotime($r['LogDate']));
				$row[3]=$this->saringtext($r['ParamTo']);
				$row[4]=$this->saringtext($r['ParamCc']);
				$row[5]=$r['ParamSubject'];
				$row[6]=$r['Status'];
				$row[7]='<button type="button" class="btn btn-sm btn-dark" onclick="javascript:viewLog(\''.$r['LogId'].'\')"><i class="glyphicon glyphicon-search"></i></button>';
				$data[] = $row;
			}
		}
		
		$res = $this->db->query($query['sQueryTotal']);
		$iTotal = $res->row()->total;

		$output = array(
			"draw" => $param['draw'],
			"recordsTotal" => $iTotal,
			"recordsFiltered" => $iTotal,
			"data" => $data
		);
		echo json_encode($output);

	}
	
	function GetEmailLogDetail($LogId)
	{
		$qry = "
			SELECT *,
			CONVERT(VARCHAR, LogDate, 106)+' '+CONVERT(VARCHAR, LogDate, 108) as Tanggal,
			CASE WHEN ISNULL(IsSent,0)=1 THEN 'SUKSES'
			WHEN ISNULL(IsSent,0)=0 AND ISNULL(StopRetry,0)=0 THEN 'PENDING'
			WHEN ISNULL(IsSent,0)=0 AND ISNULL(StopRetry,0)=1 THEN 'GAGAL'
			ELSE 'UNDEFINED' END as Status
			FROM Log_Email
			WHERE LogId = ".$LogId."
		";
		$res = $this->db->query($qry);
		if($res->num_rows() > 0)
			return $res->row();
		else
			return array();
	}
	
	function GetWhatsappLog($branch, $dp1, $dp2, $status, $search)
	{
		$qry = "
			SELECT LogId, BranchId, LogDate, MsgType, PhoneNo, SentDate,
			CONVERT(VARCHAR, LogDate, 106)+' '+CONVERT(VARCHAR, LogDate, 108) as Tanggal,
			CONVERT(VARCHAR, SentDate, 106)+' '+CONVERT(VARCHAR, SentDate, 108) as TanggalTerkirim,
			CASE WHEN ISNULL(IsSent,0)=1 THEN 'SUKSES'
			WHEN ISNULL(IsSent,0)=0 AND ISNULL(StopRetry,0)=0 THEN 'PENDING'
			WHEN ISNULL(IsSent,0)=0 AND ISNULL(StopRetry,0)=1 THEN 'GAGAL'
			ELSE 'UNDEFINED' END as Status
			FROM Log_Whatsapp
			WHERE (CONVERT(DATE, LogDate) BETWEEN '".date('Y-m-d', strtotime($dp1))."' AND '".date('Y-m-d', strtotime($dp2))."')
		";
		
		if($branch!=''){
			$qry.=" AND (BranchId='".$branch."')";
		}
		if($status!=''){
			$qry.= " AND (CASE WHEN ISNULL(IsSent,0)=1 THEN 'SUKSES'
			WHEN ISNULL(IsSent,0)=0 AND ISNULL(StopRetry,0)=0 THEN 'PENDING'
			WHEN ISNULL(IsSent,0)=0 AND ISNULL(StopRetry,0)=1 THEN 'GAGAL'
			ELSE 'UNDEFINED' END='".$status."')";
		}
		if($search!=''){
			$qry.= " AND (MsgParam LIKE '%".$search."%')";
		}
		$qry.=" ORDER BY LogDate DESC";
		// echo $qry;die;
		
		$res = $this->db->query($qry);
		if($res->num_rows() > 0)
			return $res->result();
		else
			return array();
	}
	
	function GetWhatsappLogDetail($LogId)
	{
		$qry = "
			SELECT *,
			CONVERT(VARCHAR, LogDate, 106)+' '+CONVERT(VARCHAR, LogDate, 108) as Tanggal,
			CONVERT(VARCHAR, SentDate, 106)+' '+CONVERT(VARCHAR, SentDate, 108) as TanggalTerkirim,
			CASE WHEN ISNULL(IsSent,0)=1 THEN 'SUKSES'
			WHEN ISNULL(IsSent,0)=0 AND ISNULL(StopRetry,0)=0 THEN 'PENDING'
			WHEN ISNULL(IsSent,0)=0 AND ISNULL(StopRetry,0)=1 THEN 'GAGAL'
			ELSE 'UNDEFINED' END as Status
			FROM Log_Whatsapp
			WHERE LogId = ".$LogId."
		";
		$res = $this->db->query($qry);
		if($res->num_rows() > 0)
			return $res->row();
		else
			return array();
	}
	
	function saringtext($txt){
		$txt = str_replace('[','',$txt);
		$txt = str_replace(']','',$txt);
		$txt = str_replace('"','',$txt);
		$txt = str_replace(',','<br>',$txt);
		return $txt;
	}
	
}
?>
