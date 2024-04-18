<?php
class ReportModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function GetDataMeterai($SN)
	{
		$str = "SELECT * FROM TblMeteraiElektronik WHERE EMeterai_SN='".$SN."' ";
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->row();
		} else {
			return null;
		}
	}

	// [Kode_Lokasi],[EMeterai_ID],[EMeterai_Value],[EMeterai_SN],[EMeterai_ImageBase64]
	// ,[EMeterai_Image],[EMeterai_FullPath],[EMeterai_RequestBy],[EMeterai_RequestDate]
	// ,[Document_Type],[Document_No],[Document_Value],[Document_FileName]
	// ,[Document_UniqueCode],[EMeterai_StampingBy],[EMeterai_StampingDate],[IsCancelled]
	// ,[CancelledBy],[CancelledDate],[ModifiedBy],[ModifiedDate],[IsSigned],[CancelledNote]
	// ,[KodeLawanTransaksi],[NamaLawanTransaksi],[WilayahLawanTransaksi]
	// ,[Document_Date],[Document_Reff],[Document_Remarks]
	// ,[ErrorCode],[ErrorMessage],[Settlement_Status],[Settlement_Desc]
	// ,[Settlement_File],[Settlement_Date],[Settlement_Value],[Settlement_ID]
	// ,[Settlement_Year],[Settlement_Month],[IsLocked],[LockedBy],[LockedDate]

	// WILAYAH, KD_PLG, NM_PLG, KD_LOKASI, SERIAL_NUMBER, CDATE, TYPE_TRANS,
	// NO_BUKTI, TGL_TRANS, KET, NO_REF, TOTAL_BUKTI, NILAI_METERAI, ISSTAMPED, STAMPEDDATE, 
	// NAMA_FILE, ISCANCELLED, CANCELLEDNOTE, CANCELLEDBY, CANCELLEDDATE, ERRORCODE, ERRORMESSAGE,
	// ID_METERAI, IMAGE_BASE64

	function SimpanDataMeterai_Bhakti($data)
	{
		$result = array();
		$result["INSERTED"] = 0;
		$result["ALREADYLOCKED"] = 0;
		$result["MODIFIEDLOCKED"] = 0;
		$result["MODIFIED"] = 0;
		$result["TOTAL"] = count($data);
		
		for($i=0; $i<count($data); $i++) {
			$stamp = $data[$i];
			$ExistingData = $this->GetDataMeterai($stamp["SERIAL_NUMBER"]);
			if ($ExistingData==null) {
				$this->db->set("Kode_Lokasi", $stamp["KD_LOKASI"]);
				$this->db->set("EMeterai_ID", $stamp["ID_METERAI"]);
				$this->db->set("EMeterai_Value", $stamp["NILAI_METERAI"]);
				$this->db->set("EMeterai_SN", $stamp["SERIAL_NUMBER"]);
				$this->db->set("EMeterai_ImageBase64", $stamp["IMAGE_BASE64"]);
				$this->db->set("EMeterai_RequestDate", $stamp["CDATE"]);
				$this->db->set("Document_Type", $stamp["TYPE_TRANS"]);
				$this->db->set("Document_No", $stamp["NO_BUKTI"]);
				$this->db->set("Document_Value", $stamp["TOTAL_BUKTI"]);
				$this->db->set("Document_FileName", $stamp["NAMA_FILE"]);
				// $this->db->set("Emeterai_StampingDate", $stamp["STAMPEDDATE"]);
				$this->db->set("IsSigned", $stamp["ISSTAMPED"]);
				$this->db->set("IsCancelled", $stamp["ISCANCELLED"]);
				$this->db->set("CancelledBy", $stamp["CANCELLEDBY"]);
				$this->db->set("CancelledDate", $stamp["CANCELLEDDATE"]);
				$this->db->set("ModifiedBy", $stamp["MODIFIEDBY"]);
				$this->db->set("ModifiedDate", $stamp["MODIFIEDDATE"]);
				$this->db->set("CancelledNote", $stamp["CANCELLEDNOTE"]);
				$this->db->set("Document_Date", $stamp["TGL_TRANS"]);
				$this->db->set("Document_Reff", $stamp["NO_REF"]);
				$this->db->set("Document_Remarks", $stamp["KET"]);
				$this->db->set("KodeLawanTransaksi", $stamp["KD_PLG"]);
				$this->db->set("NamaLawanTransaksi", $stamp["NM_PLG"]);
				$this->db->set("WilayahLawanTransaksi", $stamp["WILAYAH"]);
				$this->db->set("NPWPLawanTransaksi", $stamp["NPWP"]);
				$this->db->set("NIKLawanTransaksi", $stamp["NIK"]);
				$this->db->set("ErrorCode", $stamp["ERRORCODE"]);
				$this->db->set("ErrorMessage", $stamp["ERRORMESSAGE"]);
				$this->db->set("IsLocked", 0);
				$this->db->insert("TblMeteraiElektronik");
				// $this->db->set("", $stamp[""]);
				// $this->db->set("", $stamp[""]);
				// $this->db->set("", $stamp[""]);
				// $this->db->set("", $stamp[""]);
				// $this->db->set("", $stamp[""]);
				$result["INSERTED"]++;
			} else {
				if ($ExistingData->IsLocked==1) {
					$result["ALREADYLOCKED"]++;
					//Data Meterai Sudah Dilock, Tidak Boleh Dirubah Lagi
				} else {
					$this->db->where("EMeterai_SN", $stamp["SERIAL_NUMBER"]);
					$this->db->set("Kode_Lokasi", $stamp["KD_LOKASI"]);
					$this->db->set("EMeterai_ID", $stamp["ID_METERAI"]);
					$this->db->set("EMeterai_Value", $stamp["NILAI_METERAI"]);
					$this->db->set("EMeterai_ImageBase64", $stamp["IMAGE_BASE64"]);
					$this->db->set("EMeterai_RequestDate", $stamp["CDATE"]);
					$this->db->set("Document_Type", $stamp["TYPE_TRANS"]);
					$this->db->set("Document_No", $stamp["NO_BUKTI"]);
					$this->db->set("Document_Value", $stamp["TOTAL_BUKTI"]);
					$this->db->set("Document_FileName", $stamp["NAMA_FILE"]);
					// $this->db->set("Emeterai_StampingDate", $stamp["STAMPEDDATE"]);
					$this->db->set("IsSigned", $stamp["ISSTAMPED"]);
					$this->db->set("IsCancelled", $stamp["ISCANCELLED"]);
					$this->db->set("CancelledBy", $stamp["CANCELLEDBY"]);
					$this->db->set("CancelledDate", $stamp["CANCELLEDDATE"]);
					$this->db->set("ModifiedBy", $stamp["MODIFIEDBY"]);
					$this->db->set("ModifiedDate", $stamp["MODIFIEDDATE"]);
					$this->db->set("CancelledNote", $stamp["CANCELLEDNOTE"]);
					$this->db->set("Document_Date", $stamp["TGL_TRANS"]);
					$this->db->set("Document_Reff", $stamp["NO_REF"]);
					$this->db->set("Document_Remarks", $stamp["KET"]);
					$this->db->set("KodeLawanTransaksi", $stamp["KD_PLG"]);
					$this->db->set("NamaLawanTransaksi", $stamp["NM_PLG"]);
					$this->db->set("WilayahLawanTransaksi", $stamp["WILAYAH"]);
					$this->db->set("NPWPLawanTransaksi", $stamp["NPWP"]);
					$this->db->set("NIKLawanTransaksi", $stamp["NIK"]);
					$this->db->set("ErrorCode", $stamp["ERRORCODE"]);
					$this->db->set("ErrorMessage", $stamp["ERRORMESSAGE"]);
					if ($stamp["ISSTAMPED"]==1 && $ExistingData->Settlement_Status=="STAMP" && $ExistingData->IsLocked==0) {
						$this->db->set("IsLocked", 1);
						$this->db->set("LockedBy", $_SESSION["logged_in"]["username"]);
						$this->db->set("LockedDate", date("Y-m-d H:i:s"));
					} else {
						$this->db->set("IsLocked", 0);
					}
					$this->db->update("TblMeteraiElektronik");

					if ($stamp["ISSTAMPED"]==1 && $ExistingData->Settlement_Status=="STAMP" && $ExistingData->IsLocked==0) {
						$result["MODIFIEDLOCKED"]++;
					} else {
						$result["MODIFIED"]++;
					}
				}
			}
		}
		return $result;
	}

	function WriteLog($kodeLokasi, $th, $bl, $data)
	{	
		$str = "SELECT * FROM TblMeteraiElektronikLog 
				WHERE Kode_Lokasi='".$kodeLokasi."' and Tahun=".$th."
				and Bulan=".$bl."";
		$res = $this->db->query($str);
		if ($res->num_rows()==0) {
			$this->db->set("Kode_Lokasi", $kodeLokasi);
			$this->db->set("Tahun", $th);
			$this->db->set("Bulan", $bl);
			$this->db->set("CreatedBy", $_SESSION["logged_in"]["username"]);
			$this->db->set("CreatedDate", date("Y-m-d H:i:s"));
			$this->db->set("ModifiedBy", $_SESSION["logged_in"]["username"]);
			$this->db->set("ModifiedDate", date("Y-m-d H:i:s"));
			$this->db->set("Remarks", "");
			$this->db->set("TotalRecord", $data["TOTAL"]);
			$this->db->set("TotalRecordInserted", $data["INSERTED"]);
			$this->db->set("TotalRecordAlreadyLocked", $data["ALREADYLOCKED"]);
			$this->db->set("TotalRecordModifiedAndLocked", $data["MODIFIEDLOCKED"]);
			$this->db->set("TotalRecordModified", $data["MODIFIED"]);
			$this->db->set("TotalStamp", $data["STAMP"]);
			$this->db->set("TotalNotStamp", $data["NOTSTAMP"]);
			$this->db->insert("TblMeteraiElektronikLog");
		} else {
			$this->db->where("Kode_Lokasi", $kodeLokasi);
			$this->db->where("Tahun", $th);
			$this->db->where("Bulan", $bl);
			$this->db->set("ModifiedBy", $_SESSION["logged_in"]["username"]);
			$this->db->set("ModifiedDate", date("Y-m-d H:i:s"));
			$this->db->set("Remarks", "");
			$this->db->set("TotalRecord", $data["TOTAL"]);
			$this->db->set("TotalRecordInserted", $data["INSERTED"]);
			$this->db->set("TotalRecordAlreadyLocked", $data["ALREADYLOCKED"]);
			$this->db->set("TotalRecordModifiedAndLocked", $data["MODIFIEDLOCKED"]);
			$this->db->set("TotalRecordModified", $data["MODIFIED"]);
			$this->db->set("TotalStamp", $data["STAMP"]);
			$this->db->set("TotalNotStamp", $data["NOTSTAMP"]);
			$this->db->update("TblMeteraiElektronikLog");
		}
	}

	function GetImportLogs($sumber, $tahun, $bulan)
	{
		$str = "SELECT * 
				FROM TblMeteraiElektronikLog 
				WHERE Tahun=".$tahun." and Bulan=".$bulan;
		if ($sumber=="SETTLEMENT") {
			$str.= " and Kode_Lokasi='SETTLEMENT' ";
		} else {
			$str.= " and Kode_Lokasi<>'SETTLEMENT' ";
		} 
		$str.= " ORDER BY Kode_Lokasi";
		// die($str);
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function PreSimpanDataSettlement($data)
	{
		$str = "UPDATE TblMeteraiElektronik SET Settlement_Month=0, ProcessNote='PREVIOUS SETTLEMENT MONTH: '+CAST(Settlement_Month as VARCHAR(2)) ";
		$str.= "WHERE Settlement_Year=".$data["TAHUN"]." and Settlement_Month=".$data["BULAN"];
		$run = $this->db->query($str);
	}

	function SimpanDataSettlement($data)
	{
		$result = array();
		$result["INSERTED"] = 0;
		$result["ALREADYLOCKED"] = 0;
		$result["MODIFIEDLOCKED"] = 0;
		$result["MODIFIED"] = 0;

		$ExistingData = $this->GetDataMeterai($data["SN"]);
		if ($ExistingData==null) {
			$this->db->set("Kode_Lokasi", "SETTLEMENT");
			$this->db->set("EMeterai_ID", 0);
			$this->db->set("EMeterai_Value", $data["PRICE"]);
			$this->db->set("EMeterai_SN", $data["SN"]);
			$this->db->set("EMeterai_ImageBase64", "");
			$this->db->set("EMeterai_RequestDate", $data["TGL"]);
			$this->db->set("Document_Type", "");
			$this->db->set("Document_No", "");
			$this->db->set("Document_Value", 0);
			$this->db->set("Document_FileName", $data["FILE"]);
			$this->db->set("IsSigned", 0);	
			$this->db->set("IsCancelled", 0);
			$this->db->set("CancelledBy", "");
			$this->db->set("Document_Reff", "");
			$this->db->set("Document_Remarks", "");
			$this->db->set("KodeLawanTransaksi", "");
			$this->db->set("NamaLawanTransaksi", "");
			$this->db->set("WilayahLawanTransaksi", "");
			$this->db->set("NPWPLawanTransaksi", "");
			$this->db->set("NIKLawanTransaksi", "");
			$this->db->set("ErrorCode", "0");
			$this->db->set("ErrorMessage", "");
			$this->db->set("IsLocked", 0);
			$this->db->set("Settlement_Value", $data["PRICE"]);
			$this->db->set("Settlement_File", $data["FILE"]);
			$this->db->set("Settlement_Date", date("Y-m-d", strtotime($data["TGL"])));	
			$this->db->set("Settlement_Status", $data["STATUS"]);	
			$this->db->set("Settlement_Desc", $data["DESC"]);
			$this->db->set("Settlement_Year", $data["TAHUN"]);
			$this->db->set("Settlement_Month", $data["BULAN"]);
			$this->db->set("Settlement_ID", $data["ID"]);

			$this->db->insert("TblMeteraiElektronik");
			// $this->db->set("", $stamp[""]);
			// $this->db->set("", $stamp[""]);
			// $this->db->set("", $stamp[""]);
			// $this->db->set("", $stamp[""]);
			// $this->db->set("", $stamp[""]);
			$result["INSERTED"]++;
		} else {
			if ($ExistingData->IsLocked==1) {
				$this->db->where("EMeterai_SN", $data["SN"]);
				$this->db->set("Settlement_ID", $data["ID"]);
				$this->db->set("Settlement_Date", date("Y-m-d", strtotime($data["TGL"])));	
				$this->db->set("Settlement_Year", $data["TAHUN"]);
				$this->db->set("Settlement_Month", $data["BULAN"]);
				if ($ExistingData->Kode_Lokasi=="SETTLEMENT") {
					$this->db->set("IsSigned", 0);
					$this->db->set("EMeterai_StampingDate", null);
					$this->db->set("IsLocked", 0);
					$this->db->set("LockedBy", "");
					$this->db->set("LockedDate", null);
				}
				$this->db->update("TblMeteraiElektronik");
				$result["ALREADYLOCKED"]++;
				//Data Meterai Sudah Dilock, Tidak Boleh Dirubah Lagi
			} else {
				$this->db->where("EMeterai_SN", $data["SN"]);
				$this->db->set("Settlement_Value", $data["PRICE"]);
				$this->db->set("Settlement_File", $data["FILE"]);
				$this->db->set("Settlement_Date", date("Y-m-d", strtotime($data["TGL"])));	
				$this->db->set("Settlement_Status", $data["STATUS"]);	
				$this->db->set("Settlement_Desc", $data["DESC"]);
				$this->db->set("Settlement_Year", $data["TAHUN"]);
				$this->db->set("Settlement_Month", $data["BULAN"]);
				$this->db->set("Settlement_ID", $data["ID"]);

				if ($data["STATUS"]=="STAMP" && $ExistingData->IsSigned==1 && $ExistingData->Kode_Lokasi!="SETTLEMENT" ) {
					$this->db->set("IsLocked", 1);
					$this->db->set("LockedBy", $_SESSION["logged_in"]["username"]);
					$this->db->set("LockedDate", date("Y-m-d H:i:s"));
				}
				if ($data["STATUS"]=="NOTSTAMP" && $ExistingData->Kode_Lokasi=="SETTLEMENT" && $ExistingData->IsSigned==1) {
					$this->db->set("IsSigned", 0);
					$this->db->set("EMeterai_StampingDate", null);
				}
				$this->db->update("TblMeteraiElektronik");

				if ($data["STATUS"]=="STAMP" && $ExistingData->IsSigned==1 && $ExistingData->IsLocked==0) {
					$result["MODIFIEDLOCKED"]++;
				} else {
					$result["MODIFIED"]++;
				}
			}
		}
		return $result;
	}

	function HitungSettlementSummary($data)
	{
		$str = "SELECT SUM(CASE WHEN Settlement_Status='STAMP' THEN 1 ELSE 0 END) as TOTAL_STAMP,
					SUM(CASE WHEN Settlement_Status='NOTSTAMP' THEN 1 ELSE 0 END) as TOTAL_NOT_STAMP 
				FROM TblMeteraiElektronik
				WHERE Settlement_ID = '".$data["ID"]."' ";
		$res = $this->db->query($str);
		if ($res->num_rows()>0)
			return $res->row();
		else
			return null;
	}

	function GetDataGabungan($th, $bl, $jns)
	{
		$str = "";
		if ($jns=="SETTLEMENT ONLY STAMP") {
			$str = " SELECT * FROM TblMeteraiElektronik ";
			$str.= " WHERE Settlement_Year=".$th." and Settlement_Month=".$bl;
			$str.= " and Kode_Lokasi='SETTLEMENT' and Settlement_Status='STAMP' ";
			$str.= " and IsDeleted=0 ";
		} else if ($jns=="SETTLEMENT ONLY NOT STAMP") {
			$str = " SELECT * FROM TblMeteraiElektronik ";
			$str.= " WHERE Settlement_Year=".$th." and Settlement_Month=".$bl;
			$str.= " and Kode_Lokasi='SETTLEMENT' and Settlement_Status='NOTSTAMP' ";
			$str.= " and IsDeleted=0 ";
		} else if ($jns=="SETTLEMENT NOT STAMP BHAKTI STAMP") {
			$str = " SELECT * FROM TblMeteraiElektronik ";
			$str.= " WHERE Settlement_Year=".$th." and Settlement_Month=".$bl;
			$str.= " and Settlement_Status='NOTSTAMP' and IsSigned=1";
			$str.= " and Kode_Lokasi<>'SETTLEMENT' and isnull(Settlement_ID,'')<>'' ";
			$str.= " and IsDeleted=0 ";
		} else if ($jns=="SETTLEMENT STAMP BHAKTI NOT STAMP") {
			$str = " SELECT * FROM TblMeteraiElektronik ";
			$str.= " WHERE Settlement_Year=".$th." and Settlement_Month=".$bl;
			$str.= " and Settlement_Status='STAMP' and IsSigned=0";
			$str.= " and Kode_Lokasi<>'SETTLEMENT' and isnull(Settlement_ID,'')<>'' ";
			$str.= " and IsDeleted=0 ";
		} else if ($jns=="BOTH NOT STAMP") {
			$str = " SELECT * FROM TblMeteraiElektronik ";
			$str.= " WHERE Settlement_Year=".$th." and Settlement_Month=".$bl;
			$str.= " and Settlement_Status='NOTSTAMP' and IsSigned=0";
			$str.= " and Kode_Lokasi<>'SETTLEMENT' and isnull(Settlement_ID,'')<>'' ";
			$str.= " and IsDeleted=0 ";
		} else if ($jns=="BOTH STAMP") {
			$str = " SELECT * FROM TblMeteraiElektronik ";
			$str.= " WHERE Settlement_Year=".$th." and Settlement_Month=".$bl;
			$str.= " and Settlement_Status='STAMP' and IsSigned=1";
			$str.= " and Kode_Lokasi<>'SETTLEMENT' and isnull(Settlement_ID,'')<>'' ";
			$str.= " and IsDeleted=0 ";
		} else if ($jns=="BHAKTI ONLY STAMP") {
			$str = " SELECT * FROM TblMeteraiElektronik ";
			$str.= " WHERE year(EMeterai_StampingDate)=".$th." and month(EMeterai_StampingDate)=".$bl;
			$str.= " and Kode_Lokasi<>'SETTLEMENT' and isnull(Settlement_ID,'')='' and IsSigned=1 ";
			$str.= " and IsDeleted=0 ";
		} else if ($jns=="BHAKTI ONLY NOT STAMP") {
			$str = " SELECT * FROM TblMeteraiElektronik ";
			$str.= " WHERE year(EMeterai_RequestDate)=".$th." and month(EMeterai_RequestDate)=".$bl;
			$str.= " and Kode_Lokasi<>'SETTLEMENT' and isnull(Settlement_ID,'')='' and IsSigned=0 ";
			$str.= " and IsDeleted=0 ";
		}
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetDataGabunganPajakku($th, $bl, $jns)
	{
		$str = "";
		if ($jns=="BHAKTI ALL STAMP") {
			$str = "SELECT * FROM TblMeteraiElektronik
					WHERE year(EMeterai_StampingDate)= ".$th." and month(EMeterai_StampingDate)= ".$bl."
					and Kode_Lokasi<>'SETTLEMENT' and isnull(Settlement_ID,'')='' and IsSigned=1
					and IsDeleted=0 
					union all
					SELECT * FROM TblMeteraiElektronik
					WHERE Settlement_Year= ".$th." and Settlement_Month= ".$bl."
					and Settlement_Status='STAMP' and IsSigned=0
					and Kode_Lokasi<>'SETTLEMENT' and isnull(Settlement_ID,'')<>''
					and IsDeleted=0		
					union all
					SELECT * FROM TblMeteraiElektronik
					WHERE Settlement_Year= ".$th." and Settlement_Month= ".$bl."
					and Settlement_Status='STAMP' and IsSigned=1
					and Kode_Lokasi<>'SETTLEMENT' and isnull(Settlement_ID,'')<>''
					and IsDeleted=0 
					";
		} else if ($jns=="BHAKTI ALL NOT STAMP") {
			$str = "SELECT * FROM TblMeteraiElektronik
					WHERE year(EMeterai_RequestDate)= ".$th."  and month(EMeterai_RequestDate)= ".$bl."
					and Kode_Lokasi<>'SETTLEMENT' and isnull(Settlement_ID,'')='' and IsSigned=0
					and IsDeleted=0 
					union all 
					SELECT * FROM TblMeteraiElektronik
					WHERE Settlement_Year = ".$th."  and Settlement_Month= ".$bl."
					and Settlement_Status='NOTSTAMP' and IsSigned=1 
					and Kode_Lokasi<>'SETTLEMENT' and isnull(Settlement_ID,'')<>''
					and IsDeleted=0 
					union all
					SELECT * FROM TblMeteraiElektronik
					WHERE Settlement_Year= ".$th."  and Settlement_Month= ".$bl."
					and Settlement_Status='NOTSTAMP' and IsSigned=0
					and Kode_Lokasi<>'SETTLEMENT' and isnull(Settlement_ID,'')<>''
					and IsDeleted=0 
					";
		} 
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function CancelMeterai($sn, $ket) 
	{
		$ExistingData = $this->GetDataMeterai($sn);
		if ($ExistingData!=null) {
			$this->db->where("EMeterai_SN", $sn);
			$this->db->set("IsCancelled", 1);
			$this->db->set("CancelledBy", $_SESSION["logged_in"]["username"]);
			$this->db->set("CancelledDate", date("Y-m-d H:i:s"));
			$this->db->set("CancelledNote", $ket);
			$this->db->update("TblMeteraiElektronik");
		}
	}

	function ChangeDoc($sn, $ket) 
	{
		$ExistingData = $this->GetDataMeterai($sn);
		if ($ExistingData!=null) {
			$this->db->where("EMeterai_SN", $sn);
			$this->db->set("IsSigned", 0);
			$this->db->set("IsCancelled", 0);
			$this->db->set("CancelledNote", "");
			$this->db->set("CancelledBy", "");
			$this->db->set("CancelledDate", null);
			$this->db->set("Document_Type", "");
			$this->db->set("Document_No", "");
			$this->db->set("ModifiedBy", $_SESSION["logged_in"]["username"]);
			$this->db->set("ModifiedDate", date("Y-m-d H:i:s"));
			$this->db->update("TblMeteraiElektronik");
		}
	}

	function Remove($sn, $ket) 
	{
		$ExistingData = $this->GetDataMeterai($sn);
		if ($ExistingData!=null) {
			$this->db->where("EMeterai_SN", $sn);
			$this->db->set("IsDeleted", 1);
			$this->db->set("DeletedBy", $_SESSION["logged_in"]["username"]);
			$this->db->set("DeletedDate", date("Y-m-d H:i:s"));
			$this->db->set("DeletedNote", $ket);
			$this->db->update("TblMeteraiElektronik");
		}
	}

	function SetStamp($sn, $tgl) 
	{
		$ExistingData = $this->GetDataMeterai($sn);
		if ($ExistingData!=null) {
			$this->db->where("EMeterai_SN", $sn);
			$this->db->set("IsSigned", 1);
			$this->db->set("EMeterai_StampingDate", $tgl);
			$this->db->update("TblMeteraiElektronik");
		}
	}
}
?>
