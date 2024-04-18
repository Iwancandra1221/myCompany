<?php
class PlanPOModel extends CI_Model
{
	function getList($post)
	{
		
		$str = "SELECT PlanId, PlanNo, Division, PlanStatus, PeriodTh1, PeriodBl1, PeriodP1,
					PeriodTh2, PeriodBl2, PeriodP2, CreatedBy, CreatedDate, 
					ModifiedBy, ModifiedDate, IsApproved, ApprovedBy, ApprovedDate, ApprovalNote,
					IsDeleted, DeletedBy, DeletedDate, DeleteNote 
				FROM TblPOPlanHD WHERE PlanStatus in (''";		
		
		if ($post["ChkCancelled"]==1) $str.= ",'CANCELED','DELETED'";
		if ($post["ChkDraft"]==1)   $str .= ",'DRAFT','NEW REQUEST'";
		if ($post["ChkSaved"]==1)   $str .= ",'SAVED'";
		if ($post["ChkWaiting"]==1) $str .= ",'WAITING FOR APPROVAL'";
		if ($post["ChkApproved"]==1) $str.= ",'APPROVED'";
		if ($post["ChkRejected"]==1) $str.= ",'REJECTED'";

		$str .= ") ORDER BY CreatedDate DESC";
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function getAutoNumber($division)
	{
		$period = date('Ym');
		if ($division == 'MIYAKOKR') {
			$div = 'MR';
		} else if ($division == 'MICOOK') {
			$div = 'MC';
		} else {
			$div = substr($division, 0, 2);
		}

		$format = "$div/$period/";
		$this->db->limit(1, 0);
		$this->db->order_by('PlanNo', 'desc');
		$this->db->like('PlanNo', $format);
		$this->db->select('right(PlanNo, 4) PlanNo ', false);
		$res = $this->db->get('TblPOPlanHD');
		if ($res->num_rows() > 0) {
			$number = sprintf("%04d", $res->row()->PlanNo + 1);
			return $format . $number;
		} else
			return $format . '0001';
	}

	function SavePOPlan($post)
	{
		$data = array();
		$data["mode"] = $post["mode"];
		$data["kodePlan"] = $post["txtPlanCode"];
		$data["divisi"] = $post["cboDivision"];
		$data["ketHd"] = $post["txtPlanNote"];
		$data["status"] = "SAVED";
		$data["p1"] = $post["cboPeriodeP1"];
		$data["bl1"] = $post["cboPeriodeBl1"];
		$data["th1"] = $post["cboPeriodeTh1"];
		$data["p2"] = $post["cboPeriodeP2"];
		$data["bl2"] = $post["cboPeriodeBl2"];
		$data["th2"] = $post["cboPeriodeTh2"];

		$dtPeriod 	= $post["dtPeriode"];
		$dtPeriodId = $post["dtPeriodeId"];
		$dtDayCount = $post["jmlHari"];

		$dtRegion = $post["Kota"];
		$dtBranch = $post["KdLokasi"];

		$dtProductId = $post["filterBarang"];
		$dtProductNote = $post["KeteranganDt"];
		
		$lanjut = true;
		$this->db->trans_start();
		//01. Simpan POPlanHD
		if ($data["kodePlan"]=="AUTONUMBER") {
			$insertHD = $this->insertHD($data);
			if ($insertHD["result"]=="SUCCESS") {
				$data["kodePlan"] = $insertHD["planId"];
			} else {
				$lanjut = false;
			}
		} else {
			//PlanNo bukan AUTONUMBER, Cek Dahulu Apakah No Ada Di TblPOPlanHD
			$planHD = $this->GetPlanHD2($data["kodePlan"]);
			if ($planHD==null) {
				//Tidak Ada, INSERT
				$insertHD = $this->insertHD($data);
				if ($insertHD["result"]=="FAILED") {
					$lanjut = false;
				}
			} else {
				//Sudah Ada, UPDATE
				$data["idPlan"] = $planHD->PlanId;
				$updateHD = $this->updateHD($data);
				if ($updateHD["result"]=="FAILED") {
					$lanjut = false;
				}
			}
		}

		//02. Simpan Dt Periods
		$PeriodFound = false;
		if ($lanjut) {
			//02.01. Buang Semua Periode Yang Tersimpan di Database, Namun Tidak ada di lemparan dari Form
			$ExistingDtPeriod = $this->GetDetailPeriode($data["kodePlan"]);
			foreach($ExistingDtPeriod as $dt) {
				$PeriodFound = false;
				$PeriodId = $dt->PeriodId;
				for($i=0; $i<count($dtPeriodId); $i++) {
					if ($dtPeriodId[$i]==$PeriodId) {
						$PeriodFound = true;
					}
				}
				if ($PeriodFound==false) {
					$str = "DELETE FROM TblPOPlanDTPeriods WHERE [Id]=".$dt->Id;
					$this->db->query($str);
				}
			}

			//02.02. Mulai Update/Insert Periode2 Yang Belum Ada
			for($i=0; $i<count($dtPeriodId); $i++) {
				$PeriodFound= false;
				$ExistingDtPeriod = $this->GetDetailPeriode($data["kodePlan"], $dtPeriodId[$i]);
				if (count($ExistingDtPeriod)==0) {
					$str = "INSERT INTO TblPOPlanDTPeriods (PlanNo, PeriodId, PeriodName, DayCount, SavedBy, SavedDate)
							SELECT '".$data["kodePlan"]."', ".$dtPeriodId[$i].", '".$dtPeriod[$i]."', ".$dtDayCount[$i].", '".$_SESSION["logged_in"]["username"]."', GETDATE()";
				} else {
					$str = "UPDATE TblPOPlanDTPeriods 
							SET PeriodName = '".$dtPeriod[$i]."', DayCount = ".$dtDayCount[$i].", 
								SavedBy = '".$_SESSION["logged_in"]["username"]."', SavedDate = GETDATE() 
							WHERE [Id] = ".$ExistingDtPeriod[0]->Id;
				}
				$this->db->query($str);
			}	
		}

		//03. Simpan Dt Products
		$ProductFound = false;
		if ($lanjut) {
			$ExistingDtProduct = $this->GetDetailProduct($data["kodePlan"]);
			foreach($ExistingDtProduct as $dt) {
				$ProductFound = false;
				$ProductId = $dt->ProductId;
				for($i=0; $i<count($dtProductId); $i++) {
					if ($dtProductId[$i]==$ProductId) {
						$ProductFound = true;
					}
				}
				if ($ProductFound==false) {
					$str = "DELETE FROM TblPOPlanDTProducts WHERE [Id]=".$dt->Id;
					$this->db->query($str);
				}
			}

			//02.02. Mulai Update/Insert Periode2 Yang Belum Ada
			for($i=0; $i<count($dtProductId); $i++) {
				$ProductFound= false;
				$ExistingDtProduct = $this->GetDetailProduct($data["kodePlan"], $dtProductId[$i]);
				if (count($ExistingDtProduct)==0) {
					$str = "INSERT INTO TblPOPlanDTProducts (PlanNo, ProductId, ProductNote, IsDraft, SavedBy, SavedDate)
							SELECT '".$data["kodePlan"]."', '".$dtProductId[$i]."', '".$dtProductNote[$i]."', 0, '".$_SESSION["logged_in"]["username"]."', GETDATE()";
				} else {
					$str = "UPDATE TblPOPlanDTProducts 
							SET ProductNote = '".$dtProductNote[$i]."', IsDraft = 0,
								SavedBy = '".$_SESSION["logged_in"]["username"]."', SavedDate = GETDATE() 
							WHERE [Id] = ".$ExistingDtProduct[0]->Id;
				}
				$this->db->query($str);
			}
			
			$str = "DELETE FROM TblPOPlanDT
					WHERE PlanNo='".$data["kodePlan"]."'
					and ProductId NOT IN (Select ProductId From TblPOPlanDTProducts Where PlanNo='".$data["kodePlan"]."')"; 
			$this->db->query($str);
		}

		//04. Simpan Dt Regions
		$RegionFound = false;
		if ($lanjut) {
			$ExistingDtRegion = $this->GetDetailWilayah($data["kodePlan"]);
			foreach($ExistingDtRegion as $dt) {
				$RegionFound = false;
				$Region = $dt->Region;
				for($i=0; $i<count($dtRegion); $i++) {
					if ($dtRegion[$i]==$Region) {
						$RegionFound = true;
					}
				}
				if ($RegionFound==false) {
					$str = "DELETE FROM TblPOPlanDTRegions WHERE [Id]=".$dt->Id;
					$this->db->query($str);
				}
			}

			//04.02. Mulai Update/Insert Periode2 Yang Belum Ada
			for($i=0; $i<count($dtRegion); $i++) {
				$RegionFound= false;
				$ExistingDtRegion = $this->GetDetailWilayah($data["kodePlan"], $dtRegion[$i]);
				if (count($ExistingDtRegion)==0) {
					$str = "INSERT INTO TblPOPlanDTRegions (PlanNo, Region, BranchId, SavedBy, SavedDate)
							SELECT '".$data["kodePlan"]."', '".$dtRegion[$i]."', '".$dtBranch[$i]."', '".$_SESSION["logged_in"]["username"]."', GETDATE()";
				} else {
					$str = "UPDATE TblPOPlanDTRegions 
							SET BranchId = '".$dtBranch[$i]."', 
								SavedBy = '".$_SESSION["logged_in"]["username"]."', SavedDate = GETDATE() 
							WHERE [Id] = ".$ExistingDtRegion[0]->Id;
				}
				$this->db->query($str);
			}
		}

		$this->db->trans_complete();
	}

	function saveDraftProduct($data)
	{
		$lanjut = true;
		$result = array();
		$ERR_MSG = "";

		$isInsert = true;
		if ($data["kodePlan"]=="AUTONUMBER") {
			$isInsert = true;
			$InsertPlan = $this->insertHD($data);
			if ($InsertPlan["result"]=="SUCCESS") {
				$data["kodePlan"] = $InsertPlan["planId"];
			} else {
				$lanjut = false;
			}
		} else {
			$str = "SELECT * FROM TblPOPlanDTProducts WHERE PlanNo='".$data["kodePlan"]."'";
			$res = $this->db->query($str);
			if ($res->num_rows()==0) {
				$isInsert = true;
				$InsertPlan = $this->insertHD($data);
				if ($InsertPlan["result"]=="SUCCESS") {
					$data["kodePlan"] = $InsertPlan["planId"];
				} else {
					$lanjut = false;
				}
			} else {
				$isInsert = false;
			}
		}

		if ($lanjut==true) {
			$str = "SELECT * FROM TblPOPlanDTProducts WHERE PlanNo='".$data["kodePlan"]."' and ProductId='".$data["kodeBarang"]."' ";
			$res = $this->db->query($str);
			if ($res->num_rows()==0){
				$this->db->set("PlanNo", $data["kodePlan"]);
				$this->db->set("ProductId", $data["kodeBarang"]);
				$this->db->set("ProductNote", $data["ketDt"]);
				$this->db->set("IsDraft", 1);
				$this->db->set("SavedBy", $_SESSION["logged_in"]["username"]);
				$this->db->set("SavedDate", date("Y-m-d H:i:s"));
				$this->db->insert("TblPOPlanDTProducts");
			} else {
				$dt = $res->row();
				if ($dt->ProductNote != $data["ketDt"]) {
					$this->db->where("Id", $dt->Id);
					$this->db->set("ProductNote", $data["ketDt"]);
					$this->db->set("IsDraft", 1);
					$this->db->set("SavedBy", $_SESSION["logged_in"]["username"]);
					$this->db->set("SavedDate", date("Y-m-d H:i:s"));
					$this->db->update("TblPOPlanDTProducts");
				}
			}

			if( ($errors = sqlsrv_errors() ) != null) {
		        foreach( $errors as $error ) {
		        	$ERR_CODE = $error["code"];
		            $ERR_MSG.= "message: ".$error[ 'message']." ";
		        }
		        return array("result"=>"FAILED", "planId"=>$data["kodePlan"], "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
		    }
			

			$str = "Select * From TblPOPlanHD h inner join TblPOPlanDTProducts d on h.PlanNo=d.PlanNo Where h.PlanNo='".$data["kodePlan"]."' and d.ProductId='".$data["kodeBarang"]."'";
			$check = $this->db->query($str);
			if ($check->num_rows()>0) {
		        return array("result"=>"SUCCESS", "planId"=>$data["kodePlan"], "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());
			} else {
		        return array("result"=>"FAILED", "planId"=>$data["kodePlan"], "errMsg"=>"SIMPAN GAGAL", "errCode"=>0, "lastQuery"=>$this->db->last_query());
			}
		} else {
			return $InsertPlan;
		}		
	}

	function insertHD($data){
		$ERR_MSG  = "";
		$ERR_CODE = 0;

		if ($data["kodePlan"]=="AUTONUMBER") {
			$data["kodePlan"] = $this->getAutoNumber($data["divisi"]);
		}
		$this->db->set("PlanNo", $data["kodePlan"]);
		$this->db->set("Division", htmlspecialchars_decode($data["divisi"]));
		$this->db->set("PlanNote", $data["ketHd"]);
		$this->db->set("PlanStatus", $data["status"]);
		$this->db->set("PeriodTh1", $data["th1"]);
		$this->db->set("PeriodBl1", $data["bl1"]);
		$this->db->set("PeriodP1", $data["p1"]);
		$this->db->set("PeriodTh2", $data["th2"]);
		$this->db->set("PeriodBl2", $data["bl2"]);
		$this->db->set("PeriodP2", $data["p2"]);
		$this->db->set("CreatedBy", $_SESSION["logged_in"]["username"]);
		$this->db->set("CreatedDate", date("Y-m-d H:i:s"));
		$this->db->set("IsApproved", 0);
		$this->db->insert("TblPOPlanHD");
		if( ($errors = sqlsrv_errors() ) != null) {
	        foreach( $errors as $error ) {
	        	$ERR_CODE = $error["code"];
	            $ERR_MSG.= "message: ".$error[ 'message']." ";
	        }
	        return array("result"=>"FAILED",  "planId"=>$data["kodePlan"], "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
	    } else {
	        return array("result"=>"SUCCESS", "planId"=>$data["kodePlan"], "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());	    	
	    }
	}

	function updateHD($data)
	{
		$ERR_MSG  = "";
		$ERR_CODE = 0;

		$this->db->where("PlanId", $data["idPlan"]);
		//$this->db->set("PlanNo", $data["kodePlan"]);
		$this->db->set("Division", htmlspecialchars_decode($data["divisi"]));
		$this->db->set("PlanNote", $data["ketHd"]);
		$this->db->set("PlanStatus", $data["status"]);
		$this->db->set("PeriodTh1", $data["th1"]);
		$this->db->set("PeriodBl1", $data["bl1"]);
		$this->db->set("PeriodP1", $data["p1"]);
		$this->db->set("PeriodTh2", $data["th2"]);
		$this->db->set("PeriodBl2", $data["bl2"]);
		$this->db->set("PeriodP2", $data["p2"]);
		if ($data["mode"]=="add") {
			$this->db->set("CreatedBy", $_SESSION["logged_in"]["username"]);
			$this->db->set("CreatedDate", date("Y-m-d H:i:s"));
		} else {
			$this->db->set("ModifiedBy", $_SESSION["logged_in"]["username"]);
			$this->db->set("ModifiedDate", date("Y-m-d H:i:s"));
		}
		$this->db->set("IsApproved", 0);
		$this->db->update("TblPOPlanHD");
		if( ($errors = sqlsrv_errors() ) != null) {
	        foreach( $errors as $error ) {
	        	$ERR_CODE = $error["code"];
	            $ERR_MSG.= "message: ".$error[ 'message']." ";
	        }
	        return array("result"=>"FAILED",  "planId"=>$data["kodePlan"], "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
	    } else {
	        return array("result"=>"SUCCESS", "planId"=>$data["kodePlan"], "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());	    	
	    }
	}

	function SavePOPlanDT($post, $dataAverage)
	{
		$data = array();
		$data["mode"] = $post["mode"];
		$data["kodePlan"] = $post["txtPlanCode"];
		$data["divisi"] = $post["cboDivision"];
		$data["ketHd"] = $post["txtPlanNote"];
		$data["status"] = "SAVED";
		$data["p1"] = $post["cboPeriodeP1"];
		$data["bl1"] = $post["cboPeriodeBl1"];
		$data["th1"] = $post["cboPeriodeTh1"];
		$data["p2"] = $post["cboPeriodeP2"];
		$data["th2"] = $post["cboPeriodeBl2"];
		$data["bl2"] = $post["cboPeriodeTh2"];

		$dtPeriod 	= $post["dtPeriode"];
		$dtPeriodId = $post["dtPeriodeId"];
		$dtDayCount = $post["jmlHari"];

		$dtRegion = $post["Kota"];
		$dtBranch = $post["KdLokasi"];

		$dtProductId = $post["filterBarang"];
		$dtProductNote = $post["KeteranganDt"];

		/*	Cek Jumlah Region yang Sudah Tersimpan dengan yang ada di PlanDT.
			Jika beda, berarti ada penambahan/pengurangan Wilayah.
			Persentase Per wilayah yang sudah ada tidak bisa dipakai lagi */
		$str = "SELECT DISTINCT Region From TblPOPlanDT Where PlanNo = '".$data["kodePlan"]."' ";
		$res = $this->db->query($str);
		if  ($res->num_rows()>0) {
			if ($res->num_rows() != count($dtRegion)) {
				$str = "DELETE FROM TblPOPlanDT WHERE PlanNo = '".$data["kodePlan"]."' ";
				$del = $this->db->query($str);
			}
		}
			
		/*[{"ProcessId":"20210713132911","Kd_Brg":"RI-522C","Wilayah":"BALI","AvgQty":764,"TotalQty":24581,"Persentase":"3.11"}]*/
		// die(json_encode($dataAverage));
		for($i=0;$i<count($dataAverage);$i++) {
			$RegionFound = false;
			$BranchId = "";

			for($j=0;$j<count($dtRegion);$j++) {
				if ($dataAverage[$i]->Wilayah == $dtRegion[$j]) {
					$BranchId = $dtBranch[$j];
					$RegionFound = true;
					break 1;
				}
			}

			for($j=0;$j<count($dtPeriodId);$j++) {
				$str = "Select * From TblPOPlanDT Where PlanNo='".$data["kodePlan"]."' and ProductId='".$dataAverage[$i]->Kd_Brg."' and PeriodId=".$dtPeriodId[$j]." and Region='".$dataAverage[$i]->Wilayah."'";
				$res = $this->db->query($str);
				if ($res->num_rows()==0) {
					$SalesQtyAverage = round($dataAverage[$i]->TotalQty / $dtDayCount[$j],2);
					$QtyPerDay = round($dataAverage[$i]->AvgQty / $dtDayCount[$j],2);

					$this->db->set("PlanNo", $data["kodePlan"]);
					$this->db->set("ProductId", $dataAverage[$i]->Kd_Brg);
					$this->db->set("PeriodId", $dtPeriodId[$j]);
					$this->db->set("DayCount", $dtDayCount[$j]);
					$this->db->set("RSalesQtyTotal", $dataAverage[$i]->TotalQty);			//Total Rata2 Penjualan 1 Periode Nasional. Nilainya Tidak Bisa Diupdate User
					$this->db->set("RSalesQtyAverage", $SalesQtyAverage);
					$this->db->set("SalesQtyTotal", $dataAverage[$i]->TotalQty);
					$this->db->set("RSalesQtyAverage", $SalesQtyAverage);

					$this->db->set("Region", $dataAverage[$i]->Wilayah);
					$this->db->set("BranchId", $BranchId);
					$this->db->set("RQtyRegionTotal", $dataAverage[$i]->AvgQty);			//Rata2 Penjualan Region. Nilainya Tidak Bisa Diupdate User
					$this->db->set("RQtyRegionPerDay", $QtyPerDay);							//Rata2 Penjualan Region. Nilainya Tidak Bisa Diupdate User
					$this->db->set("RPercentages", $dataAverage[$i]->Persentase);			//Persentase Rata2 Penjualan terhadap Total Nasional. Nilainya Tidak bisa Diupdate User
					$this->db->set("QtyRegionPerDay", $QtyPerDay);
					$this->db->set("QtyRegionTotal", $dataAverage[$i]->AvgQty);
					$this->db->set("IsDraft", 0);											//IsDraft = 0 berarti data hasil kalkulasi Bhakti, 
					$this->db->set("SavedBy", $_SESSION["logged_in"]["username"]);			//IsDraft = 1,diintervensi oleh Marketing 
					$this->db->set("SavedDate", date("Y-m-d H:i:s"));
					$this->db->insert("TblPOPlanDT");
				} else {

					$this->db->where("PlanNo", $data["kodePlan"]);
					$this->db->where("ProductId", $dataAverage[$i]->Kd_Brg);
					$this->db->where("PeriodId", $dtPeriodId[$j]);
					$this->db->where("Region", $dataAverage[$i]->Wilayah);
					$this->db->set("BranchId", $BranchId);
					$this->db->set("IsDraft", 0);
					$this->db->update("TblPOPlanDT");
				}
			}

			if ($RegionFound==false) {
				$this->db->set("PlanNo", $data["kodePlan"]);
				$this->db->set("Region", $dataAverage[$i]->AvgQty);
				$this->db->set("BranchId", $BranchId);
				$this->db->set("SavedBy", $_SESSION["logged_in"]["username"]);
				$this->db->set("SavedDate", date("Y-m-d H:i:s"));
				$this->db->insert("TblPOPlanDTRegions");
			}

		}

		// die("Insert DT Finished!!");
	}

	function getPeriod($data) {
		$str = "Select a.PeriodId, a.PeriodName, a.DayCount, b.PeriodTh, b.PeriodBl, b.PeriodP, b.StartDate, b.EndDate 
				From TblPOPlanDTPeriods a inner join TblPOPeriods b on a.PeriodId=b.PeriodId 
				Where a.PlanNo='".$data["planNo"]."' 
				and b.PeriodTh=".$data["th"]." and b.PeriodBl=".$data["bl"]." and b.PeriodP=".$data["p"];
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return array("existed"=>true, "data"=>$res->row());
		} else {
			$str = "Select * From TblPOPeriods Where PeriodTh=".$data["th"]." and PeriodBl=".$data["bl"]." and PeriodP=".$data["p"];
			$res = $this->db->query($str);
			if ($res->num_rows()==0) {
				$StartDate = date("Y-m-d", strtotime($data["th"]."-".$data["bl"]."-01"));
				$EndDate = date("Y-m-d", strtotime($data["th"]."-".$data["bl"]."-15"));

				if ($data["p"]==2) {
					$StartDate = date("Y-m-d", strtotime($data["th"]."-".$data["bl"]."-16"));
					$EndDate = date("Y-m-d", strtotime("+1 month",strtotime($data["th"]."-".$data["bl"]."-01")));
				}

				$PeriodId = 1;
				$str = "SELECT MAX(isnull(PeriodId,0)) as MaxPeriodId From TblPOPeriods";
				$res = $this->db->query($str);
				if ($res->num_rows()>0) {
					$PeriodId = $res->row()->MaxPeriodId;
					$PeriodId += 1;
				}

				$str = "INSERT INTO TblPOPeriods (PeriodId, PeriodTh, PeriodBl, PeriodP, PeriodName, StartDate, EndDate, DayCount, ModifiedBy, ModifiedDate) ";
				$str.= "SELECT ".$PeriodId." as pId, ".$data["th"]." as th, ".$data["bl"]." as bl, ".$data["p"]." as p, '".$data["name"]."' as periodName, '".$StartDate."', '".$EndDate."', ";
				$str.= "	datediff(day, '".$StartDate."', '".$EndDate."') as dayCount, '".$_SESSION["logged_in"]["username"]."', GETDATE() ";
				$this->db->query($str);

				$str = "Select * From TblPOPeriods Where PeriodTh=".$data["th"]." and PeriodBl=".$data["bl"]." and PeriodP=".$data["p"];
				$res = $this->db->query($str);
				return array("existed"=>false, "data"=>$res->row());	
			} else {
				return array("existed"=>true, "data"=>$res->row());
			}
		}
	}

	function savePeriod($data) {
		$str = "Select * From TblPOPeriods Where PeriodTh=".$data["th"]." and PeriodBl=".$data["bl"]." and PeriodP=".$data["p"];
		$res = $this->db->query($str);
		if ($res->num_rows()==0) {
			$StartDate = date("Y-m-d", strtotime($data["th"]."-".$data["bl"]."-01"));
			$EndDate = date("Y-m-d", strtotime($data["th"]."-".$data["bl"]."-15"));

			if ($data["p"]==2) {
				$StartDate = date("Y-m-d", strtotime($data["th"]."-".$data["bl"]."-16"));
				$EndDate = date("Y-m-d", strtotime("+1 month",strtotime($data["th"]."-".$data["bl"]."-01")));
			}

			$PeriodId = 1;
			$str = "SELECT MAX(isnull(PeriodId,0)) as MaxPeriodId From TblPOPeriods";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				$PeriodId = $res->row()->MaxPeriodId;
				$PeriodId += 1;
			}

			$str = "INSERT INTO TblPOPeriods (PeriodId, PeriodTh, PeriodBl, PeriodP, PeriodName, StartDate, EndDate, DayCount, ModifiedBy, ModifiedDate) ";
			$str.= "SELECT ".$PeriodId." as pId, ".$data["th"]." as th, ".$data["bl"]." as bl, ".$data["p"]." as p, '".$data["periodName"]."' as periodName, ";
			$str.= "   '".$StartDate."', '".$EndDate."', ".$data["dayCount"]." as datCount, '".$_SESSION["logged_in"]["username"]."', GETDATE() ";
			$this->db->query($str);

			$str = "Select * From TblPOPeriods Where PeriodTh=".$data["th"]." and PeriodBl=".$data["bl"]." and PeriodP=".$data["p"];
			$res = $this->db->query($str);
			return array("success"=>true, "data"=>$res->row());	
		} else {
			$this->db->where("PeriodId", $data["periodId"]);
			$this->db->set("DayCount", $data["dayCount"]);
			$this->db->set("ModifiedBy", $_SESSION["logged_in"]["username"]);
			$this->db->set("ModifiedDate", date("Y-m-d H:i:s"));
			$this->db->update("TblPOPeriods");

			return array("success"=>true, "data"=>$res->row());
		}
	}

	function GetPlanHD($PlanID)
	{
		$PlanHD = null;
		$str = "SELECT *, 
		 	    DATEADD(DAY, -1, CAST(+convert(varchar(max),periodbl1)+'/1/'+convert(varchar(max),periodTH1) AS DATE)) AS TglPO,
				CASE WHEN LEN(convert(varchar(2),PeriodBl1))=1 THEN '0'+ convert(varchar(2),PeriodBl1) ELSE convert(varchar(2),PeriodBl1) END + '/' + convert(varchar(4),PeriodTh1) AS Periode
				FROM TblPOPlanHD
				WHERE PlanId = ".$PlanID;
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			$PlanHD = $res->row();
			$PlanHD = $this->AdditionalProperties($PlanHD);
		}

		return $PlanHD;
	}

	function GetPlanHD2($PlanNo)
	{
		$PlanHD = null;
		$str = "SELECT *
				FROM TblPOPlanHD
				WHERE PlanNo = '".$PlanNo."' ";
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			$PlanHD = $res->row();
			// echo(json_encode($PlanHD));
			$PlanHD = $this->AdditionalProperties($PlanHD);
			// echo(json_encode($PlanHD));
		}

		return $PlanHD;
	}

	function AdditionalProperties($PlanHD)
	{
		$PlanHD->Periode1 = $this->HelperModel->GetNmPeriode($PlanHD->PeriodTh1, $PlanHD->PeriodBl1, $PlanHD->PeriodP1);
		$PlanHD->Periode2 = $this->HelperModel->GetNmPeriode($PlanHD->PeriodTh2, $PlanHD->PeriodBl2, $PlanHD->PeriodP2);

		$StartDate = date("Y-m-d");
		$EndDate = date("Y-m-d");

		if ($PlanHD->PeriodP1==1) {
			$StartDate = date("Y-m-d", strtotime($PlanHD->PeriodTh1."-".$PlanHD->PeriodBl1."-1"));
		} else {
			$StartDate = date("Y-m-d", strtotime($PlanHD->PeriodTh1."-".$PlanHD->PeriodBl1."-16"));
		}

		if ($PlanHD->PeriodP2==1) {
			$EndDate = date("Y-m-d", strtotime($PlanHD->PeriodTh2."-".$PlanHD->PeriodBl2."-15"));
		} else {
			$str = "Select dbo.EOMonth('".$PlanHD->PeriodTh2."-".$PlanHD->PeriodBl2."-01') as EndDate";
			$rs  = $this->db->query($str);
			$EndDate = date("Y-m-d", strtotime($rs->row()->EndDate));
		}

		$PlanHD->StartDate = $StartDate;
		$PlanHD->EndDate = $EndDate;
		return $PlanHD;
	}

	function GetPlanDT($PlanNo)
	{
		$str = "SELECT a.*, b.PeriodTh, b.PeriodBl, b.PeriodP, b.PeriodName, 
					dbo.ReplaceChars(a.Region, '') as Region2, dbo.ReplaceChars(a.ProductId, '') as ProductId2
				FROM TblPOPlanDT a inner join TblPOPeriods b on a.PeriodId=b.PeriodId 
				WHERE a.PlanNo = '".$PlanNo."' 
				ORDER BY a.PlanNo, a.ProductId, a.Region, b.PeriodP, b.PeriodBl, b.PeriodTh";
		//	inner join TblPOPlanDTRegions c on a.PlanNo=c.PlanNo and a.Region=c.Region
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}
	
	function GetPlanDTProductSummary($PlanNo)
	{
		$str = "SELECT DISTINCT a.PlanNo, a.ProductId, a.PeriodId, b.PeriodName, a.DayCount, a.RSalesQtyTotal, a.RSalesQtyAverage, a.SalesQtyTotal, a.SalesQtyAverage,
					b.PeriodTh, b.PeriodBl, b.PeriodP, dbo.ReplaceChars(a.ProductId, '') as ProductId2
				FROM TblPOPlanDT a inner join TblPOPeriods b on a.PeriodId=b.PeriodId
				WHERE a.PlanNo = '".$PlanNo."' 
				ORDER BY b.PeriodTh, b.PeriodBl, b.PeriodP";
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetDetailPeriode($PlanNo, $PeriodId=0)
	{
		$str = " SELECT x.*, y.PeriodName, y.PeriodP, y.PeriodBl, y.PeriodTh
				 FROM TblPOPlanDTPeriods x inner join TblPOPeriods y on x.PeriodId=y.PeriodId 
				 WHERE PlanNo = '".$PlanNo."' ";
		if ($PeriodId!=0) {
			$str.=" AND x.PeriodId = ".$PeriodId."";
		}
		$str.= " ORDER BY y.PeriodTh, y.PeriodBl, y.PeriodP";
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetDetailProduct($PlanNo, $ProductId="")
	{
		$str = " SELECT *, dbo.ReplaceChars(ProductId, '') as ProductId2
				 FROM TblPOPlanDTProducts
				 WHERE PlanNo = '".$PlanNo."'";
		if ($ProductId!="") {
			$str.= " and ProductId='".$ProductId."' ";
		}
		$str.= " ORDER BY ProductId";
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetDetailWilayah($PlanNo, $Region="")
	{
		$str = "Select *, dbo.ReplaceChars(Region, '') as Region2 
				from TblPOPlanDTRegions where PlanNo = '".$PlanNo."' ";
		if ($Region!="") {
			$str.= " and Region='".$Region."' ";
		} 
		$str.= " Order By Region";
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function RemoveProduct($data)
	{
		$lanjut = true;
		$result = array();
		$ERR_MSG = "";
		$ERR_CODE = 0;
		$this->db->trans_begin();
		$checkItem = $this->db->query("Select * From TblPOPlanDTProducts Where PlanNo='".$data["kode_plan"]."' and ProductId='".$data["kode_barang"]."'");
		if ($checkItem->num_rows()>0) {
			if ($checkItem->row()->ProductId==$data["kode_barang"]) {
				$delItem = $this->db->query("Delete From TblPOPlanDTProducts Where PlanNo='".$data["kode_plan"]."' and ProductId='".$data["kode_barang"]."'");
				$delItem = $this->db->query("Delete From TblPOPlanDT Where PlanNo='".$data["kode_plan"]."' and ProductId='".$data["kode_barang"]."'");	
				if( ($errors = sqlsrv_errors() ) != null) {
			        foreach( $errors as $error ) {
			        	$ERR_CODE = $error["code"];
			            $ERR_MSG.= "message: ".$error[ 'message']." ";
			        }
			        $this->db->trans_rollback();
			        return array("result"=>"FAILED", "planId"=>$data["kode_plan"], "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
			    } else {
			    	$str = "Select * From TblPOPlanDTProducts Where PlanNo='".$data["kode_plan"]."'";
			    	$res = $this->db->query($str);
			    	if ($res->num_rows()==0) {
						$delItem = $this->db->query("Delete From TblPOPlanDT Where PlanNo='".$data["kode_plan"]."'");	
						$delItem = $this->db->query("Delete From TblPOPlanDTRegions Where PlanNo='".$data["kode_plan"]."'");	
						$delItem = $this->db->query("Delete From TblPOPlanDTPeriods Where PlanNo='".$data["kode_plan"]."'");	
						$delItem = $this->db->query("Delete From TblPOPlanHD Where PlanNo='".$data["kode_plan"]."'");	
			    	}
			    	$this->db->trans_commit();
			        return array("result"=>"SUCCESS", "planId"=>$data["kode_plan"], "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());
			    }
			} else {
		        return array("result"=>"FAILED", "planId"=>$data["kode_plan"], "errMsg"=>"Product id#".$data["kode_barang"]." Yang Tersimpan Sudah Berbeda. Reload kembali Data Campaign Plan dan Edit Kembali.", "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
			}
		}
	}

	function saveDraftWilayah($data)
	{
		$lanjut = true;
		$result = array();
		$ERR_MSG = "";

		
		$wilayahExisted = false;
		$res = $this->db->query("Select Id, PlanNo, Region, BranchId
								From TblPOPlanDTRegions 
								Where PlanNo='".strtoupper($data["kode_plan"])."' and Region='".strtoupper($data["wilayah"])."'");
		if ($res->num_rows()>0) {
			$wilayahExisted = true;
		}

		$this->db->trans_begin();
		if ($data["is_checked"]==0) {
			//Jika Checkbox DiUncentang
			if ($wilayahExisted==true) {
				//Data Region Ditemukan, Hapus!!
				$this->db->where("Id", $res->row()->Id);
				$this->db->delete("TblPOPlanDTRegions");
			} else {
				//Data Region Belum Ada, SKIPP
			}
		} else {
			//Jika Checkbox Dicentang
			if ($wilayahExisted==true) {
				//Data Region ditemukan
				if ($res->row()->BranchId!=$data["kode_lokasi"]) {
					//Jika BranchId Beda, Update
					$this->db->where("Id", $res->row()->Id);
					$this->db->set("BranchId", $data["kode_lokasi"]);
					$this->db->set("SavedBy", $_SESSION["logged_in"]["username"]);
					$this->db->set("SavedDate", date("Y-m-d H:i:s"));
					$this->db->update("TblPOPlanDTRegions");
				} else {
					//Jika BranchId Sama, SKIPP
				}
			} else {
				//Data Region Belum Ada, Insert ke Tabel
				$this->db->set("PlanNo", $data["kode_plan"]);
				$this->db->set("Region", $data["wilayah"]);
				$this->db->set("BranchId", $data["kode_lokasi"]);
				$this->db->set("SavedBy", $_SESSION["logged_in"]["username"]);
				$this->db->set("SavedDate", date("Y-m-d H:i:s"));
				$this->db->insert("TblPOPlanDTRegions");
			}
		}

		if( ($errors = sqlsrv_errors() ) != null) {
	        foreach( $errors as $error ) {
	        	$ERR_CODE = $error["code"];
	            $ERR_MSG.= "message: ".$error[ 'message']." ";
	        }
	        $this->db->trans_rollback();
	        return array("result"=>"FAILED", "planId"=>$data["kode_plan"], "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
	    } else {
	    	$this->db->trans_commit();
	    	//die($this->db->last_query());
	    }
		

		$str = "Select * From TblPOPlanDTRegions Where PlanNo='".$data["kode_plan"]."' and Region='".$data["wilayah"]."'";
		$check = $this->db->query($str);
		if ($data["is_checked"]==0) {
			if ($check->num_rows()>0) {
		        return array("result"=>"FAILED", "planId"=>$data["kode_plan"], "errMsg"=>"HAPUS GAGAL", "errCode"=>0, "lastQuery"=>$this->db->last_query());
		    } else {
		        return array("result"=>"SUCCESS", "planId"=>$data["kode_plan"], "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());
		    }
		} else {
			if ($check->num_rows()>0) {
		        return array("result"=>"SUCCESS", "planId"=>$data["kode_plan"], "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());
		    } else {
		        return array("result"=>"FAILED", "planId"=>$data["kode_plan"], "errMsg"=>"SIMPAN GAGAL", "errCode"=>0, "lastQuery"=>$this->db->last_query());
		    }
		}
	}

	function saveDraftDT($data)
	{
		$lanjut = true;
		$result = array();
		$ERR_MSG = "";

		$this->db->trans_begin();
		//Update IsSelected=0 untuk Barang Yang akan Disave Draft
		$this->db->where("PlanNo", $data["PlanNo"]);
		$this->db->where("ProductId", $data["ProductId"]);
		$this->db->where("Region", $data["Region"]);
		$this->db->where("PeriodId", $data["PeriodId"]);
		$this->db->set("QtyRegionTotal", $data["Qty"]);
		$this->db->set("IsDraft", 1);
		$this->db->set("SavedBy", $_SESSION["logged_in"]["username"]);
		$this->db->set("SavedDate", date("Y-m-d H:i:s"));
		$this->db->update("TblPOPlanDT");

		$str = "UPDATE TblPOPlanDT 
				SET SalesQtyTotal = TotalQty
				FROM (Select SUM(QtyRegionTotal) as TotalQty  
					  From TblPOPlanDT 
					  Where PlanNo = '".$data["PlanNo"]."' 
					  and ProductId= '".$data["ProductId"]."' 
					  and PeriodId = ".$data["PeriodId"]."
				) S 
				WHERE PlanNo = '".$data["PlanNo"]."' and ProductId='".$data["ProductId"]."' and PeriodId=".$data["PeriodId"];
		$res = $this->db->query($str);
		if( ($errors = sqlsrv_errors() ) != null) {
	        foreach( $errors as $error ) {
	        	$ERR_CODE = $error["code"];
	            $ERR_MSG.= "message: ".$error[ 'message']." ";
	        }
	        $this->db->trans_rollback();
	        return array("result"=>"FAILED", "planNo"=>$data["PlanNo"], "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
	    } else {
	    	$this->db->trans_commit();
	    }
		
		$str = "Select * From TblPOPlanDT Where PlanNo='".$data["PlanNo"]."' and ProductId='".$data["ProductId"]."' and PeriodId=".$data["PeriodId"]." and QtyRegionTotal=".$data["Qty"];
		$check = $this->db->query($str);
		if ($check->num_rows()>0) {
	        return array("result"=>"SUCCESS", "planNo"=>$data["PlanNo"], "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());
		} else {
	        return array("result"=>"FAILED", "planNo"=>$data["PlanNo"], "errMsg"=>"SIMPAN GAGAL", "errCode"=>0, "lastQuery"=>$this->db->last_query());
		}		
	}

	function GetPlanDT2($PlanNo, $ProductId="", $PeriodId=0)
	{
		$str = "SELECT a.*, b.PeriodTh, b.PeriodBl, b.PeriodP, b.PeriodName, 
					dbo.ReplaceChars(a.Region, '') as Region2, dbo.ReplaceChars(a.ProductId, '') as ProductId2
				FROM TblPOPlanDT a inner join TblPOPeriods b on a.PeriodId=b.PeriodId 
				WHERE a.PlanNo = '".$PlanNo."'
				and a.ProductId = '".$ProductId."'
				and a.PeriodId = ".$PeriodId."  
				ORDER BY a.PlanNo, a.ProductId, a.Region, b.PeriodP, b.PeriodBl, b.PeriodTh";
		//	inner join TblPOPlanDTRegions c on a.PlanNo=c.PlanNo and a.Region=c.Region
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}


	function saveDraftDTTotalQty($data)
	{
		$TotalQTYFinal = 0;
		$PlanDT = $this->GetPlanDT2($data["PlanNo"], $data["ProductId"], $data["PeriodId"]);

		$lanjut = true;
		$result = array();
		$ERR_MSG = "";
		$TotalQtyFinal = 0;
		
		$this->db->trans_begin();
		foreach($PlanDT as $dt) {
			$QTY = round(($dt->RPercentages * $data["Qty"])/100);
			$dt->QtyRegionTotal = $QTY;
			$TotalQtyFinal += $QTY;
	
			//Update IsSelected=0 untuk Barang Yang akan Disave Draft
			$this->db->where("Id", $dt->Id);
			$this->db->set("QtyRegionTotal", $QTY);
			$this->db->set("IsDraft", 1);
			$this->db->set("SavedBy", $_SESSION["logged_in"]["username"]);
			$this->db->set("SavedDate", date("Y-m-d H:i:s"));
			$this->db->update("TblPOPlanDT");
		}

		$str = "UPDATE TblPOPlanDT 
				SET SalesQtyTotal = ".$TotalQtyFinal."
				WHERE PlanNo = '".$data["PlanNo"]."' and ProductId='".$data["ProductId"]."' and PeriodId=".$data["PeriodId"];
		$res = $this->db->query($str);
		if( ($errors = sqlsrv_errors() ) != null) {
	        foreach( $errors as $error ) {
	        	$ERR_CODE = $error["code"];
	            $ERR_MSG.= "message: ".$error[ 'message']." ";
	        }
	        $this->db->trans_rollback();
	        return array("result"=>"FAILED", "planNo"=>$data["PlanNo"], "planDT"=>$PlanDT, "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
	    } else {
	    	$this->db->trans_commit();
	    }
		
	    $PlanDT = $this->GetPlanDT2($data["PlanNo"], $data["ProductId"], $data["PeriodId"]);
        return array("result"=>"SUCCESS", "planNo"=>$data["PlanNo"], "planDT"=>$PlanDT, "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());
		// $check = $this->db->query($str);
		// if ($check->num_rows()>0) {
	 //        return array("result"=>"SUCCESS", "planNo"=>$data["PlanNo"], "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());
		// } else {
	 //        return array("result"=>"FAILED", "planNo"=>$data["PlanNo"], "errMsg"=>"SIMPAN GAGAL", "errCode"=>0, "lastQuery"=>$this->db->last_query());
		// }		
	}

	function FinalSave($PlanNo)
	{
		$lanjut = true;
		$result = array();
		$ERR_MSG = "";

		$this->db->trans_begin();
		$str = "UPDATE TblPOPlanDT 
				SET IsDraft = 0
				WHERE PlanNo = '".$PlanNo."'";
		$res = $this->db->query($str);
		if( ($errors = sqlsrv_errors() ) != null) {
	        foreach( $errors as $error ) {
	        	$ERR_CODE = $error["code"];
	            $ERR_MSG.= "message: ".$error[ 'message']." ";
	        }
	        $this->db->trans_rollback();
	        return array("result"=>"FAILED", "planNo"=>$PlanNo, "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
	    } else {
	    	$this->db->trans_commit();
	    }
		
		$str = "Select * From TblPOPlanDT Where PlanNo='".$PlanNo."' and IsDraft=1";
		$check = $this->db->query($str);
		if ($check->num_rows()>0) {
	        return array("result"=>"FAILED", "planNo"=>$PlanNo, "errMsg"=>"SIMPAN GAGAL", "errCode"=>0, "lastQuery"=>$this->db->last_query());
		} else {
	        return array("result"=>"SUCCESS", "planNo"=>$PlanNo, "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());
		}		
	}

	function getEmail($division) {
		$str = "
			select email_address
			from  tb_salesman
			where level_slsman= 'BRAND MANAGER' and Division = '".htmlspecialchars($division)."'
		";

		$res = $this->db->query($str);
		return $res->row();
	}

	function ApproveOnly($PlanNo, $data) {
		set_time_limit(60);

		$this->db->trans_start();
		$this->db->where('PlanNo',$PlanNo);
		$this->db->set('isApproved',$data['isApproved']);
		$this->db->set('ApprovedBy',$data['ApprovedBy']);
		$this->db->set('ApprovedDate',date('Y-m-d H:i:s'));
		$this->db->set('PlanStatus', "APPROVED");
		$this->db->update('TblPOPlanHD');
		$this->db->trans_complete();
		echo '<center>Berhasil Approve, Silahkan tutup halaman ini!</center>';
	}

	function Rejected($PlanNo, $data) {
		set_time_limit(60);

		$this->db->trans_start();
		$this->db->where('PlanNo',$PlanNo);
		$this->db->set('isApproved',$data['isApproved']);
		$this->db->set('ApprovedBy',$data['ApprovedBy']);
		$this->db->set('ApprovedDate',date('Y-m-d H:i:s'));
		$this->db->set("ApprovalNote", $data["ApprovalNote"]);
		$this->db->set('PlanStatus', "REJECTED");
		$this->db->update('TblPOPlanHD');
		$this->db->trans_complete();
		echo '<center>Berhasil Reject, Silahkan tutup halaman ini!</center>';
	}

	function GetListApprovedUnprocessed() {
		$str = "UPDATE TblPOPlanDT 
				SET BhaktiFlag = 'CLOSED', BhaktiProcessDate='".date("Y-m-d H:i:s")."'
				WHERE PlanNo in (Select PlanNo From TblPOPlanHD where IsApproved=1)
				and QtyRegionTotal=0 and BhaktiFlag in ('UNPROCESSED','FAILED')";
		$this->db->query($str);

		$str = "SELECT DISTINCT a.PlanNo, a.Division, a.ApprovedBy, a.ApprovedDate
				 FROM TblPOPlanHD a inner join TblPOPlanDT b on a.PlanNo=b.PlanNo 
				 WHERE a.IsApproved=1 and b.QtyRegionTotal<>0 and b.BhaktiFlag in ('UNPROCESSED','FAILED')
				 order by a.ApprovedDate";
		//die($str);
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function SendApprovedPlanToBhakti($PlanNo)
	{
		$PlanHD = $this->GetPlanHD2($PlanNo);
		$PlanDT = $this->GetPlanDT($PlanNo);
		$dtPeriods = $this->GetDetailPeriode($PlanNo);
		
		// echo(json_encode($PlanDT));

		$lokasi='';
		$wilayah='';
		$connected = false;
		$urlBhakti = "";
		$errMsg = "";
		$errCode = 0;
		$PlanPO = array();

		$NamaDB = "";
		$Server = "";
		$Dtbase = "";

		$str = "select TOP 1 * from MsDatabase where branchid = 'JKT' and NamaDb= 'JAKARTA'";
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			$DB = $res->row();
			$NamaDB = $DB->NamaDb;
			$Server = $DB->Server;
			$Dtbase = $DB->Database;
			// echo("DB FOUND: ".$DB->NamaDb."<br>");
			$urlBhakti = $DB->AlamatWebService;
			// die(json_encode($DB));
		}

		// echo("Check Connection:<br>");
		$cURL = $urlBhakti."bktAPI/Billing/TesVB6";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $cURL);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$response = curl_exec($ch);
		curl_close($ch);

		if ($response === false) {
			$errMsg = "UNABLE TO CONNECT TO BHAKTI API : ".$cURL;
			$this->db->where("Id", $PlanDT->Id);
			$this->db->set("BhaktiFlag", "FAILED");
			$this->db->set("BhaktiProcessDate", date("Y-m-d H:i:s"));
			$this->db->set("BhaktiProcessNote", $errMsg);
			$this->db->update("TblPOPlanDT");
			// echo("DB BHAKTI CBG :<br>".$cURL."<br><br>");
			$connected = false;			
			array_push($resultDT, array("Region"=>"", "BranchId"=>"", "ProductId"=>"", "Period"=>"", "Result"=>"FAILED: TIDAK BISA MENGHUBUNGI API BHAKTI [".$cURL."]"));
		} else {
			//echo("Check Connection's Successful<br><br>");
			$connected = true;
		}
		// echo("<br>");

		$resultDT = array();

		foreach ($PlanDT as $z) {
			// echo(json_encode($z));
			// echo("<br>");

			if ($z->BhaktiFlag=="UNPROCESSED" || $z->BhaktiFlag=="FAILED") {

				if ($z->QtyRegionTotal==0) {
					$this->db->where("Id", $z->Id);
					$this->db->set("BhaktiFlag", "FINISHED");
					$this->db->set("BhaktiProcessDate", date("Y-m-d H:i:s"));
					$this->db->set("BhaktiProcessNote", "QTY 0");
					$this->db->update("TblPOPlanDT");

					array_push($resultDT, array("Region"=>$z->Region, "BranchId"=>$z->BranchId, "ProductId"=>$z->ProductId, "Period"=>$z->PeriodName, "Result"=>"SUCCESSFUL"));

				} else if ($connected) {

					$PlanPO['PlanHD'] = $PlanHD;
					$PlanPO['PlanDT'] = $z;
					$PlanPO['Periods'] = $dtPeriods;
					$PlanPO['Cabang'] = $NamaDB;
					$PlanPO['Server'] = $Server;
					$PlanPO['Database'] = $Dtbase;
					$PlanPO['Uid'] = SQL_UID;
					$PlanPO['Pwd'] = SQL_PWD;
					$PlanPO['api'] = 'APITES';

					$cURL = $urlBhakti. "bktAPI/CampaignPlan/insertPOPlan";
					$ch = curl_init();
					curl_setopt_array($ch, array(
						CURLOPT_URL => $cURL,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_TIMEOUT => 300,
						CURLOPT_POST => 1,
						CURLOPT_POSTFIELDS => json_encode($PlanPO),
						CURLOPT_HTTPHEADER => array("Content-type: application/json")
					));

					$response2 = curl_exec($ch);
					$err = curl_error($ch);
					curl_close($ch);
					
					// echo("Response:<br>");
					// print_r($response2);
					// echo("<br><br>");

					if ($response2!="") {
						$resp=json_decode($response2);

						try {
						    // init bootstrapping phase
						    // if (property_exists("resp", "result")) {
								if (strtoupper($resp->result)=="SUKSES") {

									$this->db->where("Id", $z->Id);
									$this->db->set("BhaktiFlag", "FINISHED");
									$this->db->set("BhaktiProcessDate", date("Y-m-d H:i:s"));
									$this->db->set("BhaktiProcessNote", "SUCCESSFUL");
									$this->db->update("TblPOPlanDT");

									array_push($resultDT, array("Region"=>$z->Region, "BranchId"=>$z->BranchId, "ProductId"=>$z->ProductId, "Period"=>$z->PeriodName, "Result"=>"SUCCESSFUL"));

								} else {

									$this->db->where("Id", $z->Id);
									$this->db->set("BhaktiFlag", "FAILED");
									$this->db->set("BhaktiProcessDate", date("Y-m-d H:i:s"));
									$this->db->set("BhaktiProcessNote", $resp->error);
									$this->db->update("TblPOPlanDT");											

									array_push($resultDT, array("Region"=>$z->Region, "BranchId"=>$z->BranchId, "ProductId"=>$z->ProductId, "Period"=>$z->PeriodName, "Result"=>"FAILED1: ".$resp->error));

								}
							// } else {
							// 	$this->db->where("Id", $z->Id);
							// 	$this->db->set("BhaktiFlag", "FAILED");
							// 	$this->db->set("BhaktiProcessDate", date("Y-m-d H:i:s"));
							// 	$this->db->set("BhaktiProcessNote", $response2);
							// 	$this->db->update("TblPOPlanDT");	
							// }						  
						    // continue execution of the bootstrapping phase
						} catch (Exception $e) {
							$errMsg = $e->getMessage();
						    // echo $e->getMessage()."<br>";

						    if ($errMsg!="") {
								$this->db->where("Id", $z->Id);

								$this->db->set("BhaktiFlag", "FAILED");
								$this->db->set("BhaktiProcessDate", date("Y-m-d H:i:s"));
								$this->db->set("BhaktiProcessNote", $errMsg);
								$this->db->update("TblPOPlanDT");						}

								array_push($resultDT, array("Region"=>$z->Region, "BranchId"=>$z->BranchId, "ProductId"=>$z->ProductId, "Period"=>$z->PeriodName, "Result"=>"FAILED2: ".$errMsg));
							}

					} else {
						//echo("GAGAL<br><br>");
						$this->db->where("Id", $z->Id);
						$this->db->set("BhaktiFlag", "FAILED");
						$this->db->set("BhaktiProcessDate", date("Y-m-d H:i:s"));
						$this->db->set("BhaktiProcessNote", "GAGAL PANGGIL API BHAKTI");
						// $this->db->set("RetryCount","isnull(RetryCount,0)+1",FALSE);
						$this->db->update("TblPOPlanDT");											

						array_push($resultDT, array("Region"=>$z->Region, "BranchId"=>$z->BranchId, "ProductId"=>$z->ProductId, "Period"=>$z->PeriodName, "Result"=>"FAILED3: GAGAL PANGGIL API BHAKTI"));
					}
				}
			}

		}
		
		return $resultDT;
		
	}

	// function getInsertedData($CampaignID)
	// {
		
	// 	$str = "
	// 	DECLARE 
	// 		@columns NVARCHAR(MAX) = '', 
	// 		@sql     NVARCHAR(MAX) = '';
		
	// 	-- select the category names
	// 	SELECT 
	// 		@columns+=QUOTENAME(productid) + ','
	// 	FROM 
	// 		TblCampaignPlanHD
	// 	WHERE
	// 		CampaignID = '" . $CampaignID . "' 
	// 	ORDER BY 
	// 		productid;
		
	// 	-- remove the last comma
	// 	SET @columns = LEFT(@columns, LEN(@columns) - 1);


	// 	-- construct dynamic SQL
	// 	SET @sql ='
	// 	SELECT * FROM   
	// 	(
	// 		SELECT 
	// 			kota as Wilayah, 
	// 			b.productid,
	// 			b.tot_avg
	// 		FROM 
	// 			TblCampaignPlanHD a
	// 			INNER JOIN TblCampaignPlanDT b 
	// 				ON a.campaignid = b.campaignid and a.ProductID=b.ProductID 
	// 		WHERE
	// 		a.CampaignID = ''" . $CampaignID . "'' 
	// 	) t 
	// 	PIVOT(
	// 		sum(tot_avg) 
	// 		FOR productid IN ('+ @columns +')
	// 	) AS pivot_table;';
		
	// 	-- execute the dynamic SQL
	// 	EXECUTE sp_executesql @sql
		 
	// 		";
			
	// 	$res = $this->db->query($str);

	// 	return $res->result_array();
	// }

	function GetPlanHDByItemID($CampaignID, $ItemID=0)
	{
		$str = "Select x.CampaignID, CampaignName, isnull(ItemID,0) as ItemID, x.ProductID, Division, CampaignStartHD, CampaignEndHD, 
					CASE WHEN isnull(JumlahHariHD,0)=0 THEN datediff(day, CampaignStartHD,CampaignEndHD)+1 ELSE isnull(JumlahHariHD,0) END as JumlahHariHD,
					CampaignStart, CampaignEnd, 
					CASE WHEN isnull(JumlahHari,0)=0 THEN datediff(day, CampaignStart, CampaignEnd)+1 ELSE isnull(JumlahHari,0) END as JumlahHari,
					CreatedBy, CreatedDate, UpdatedBy, UpdatedDate, 
					isnull(IsApproved,0) as IsApproved, ApprovedBy, ApprovedByName, ApprovedByEmail, ApprovedDate, 
					isnull(IsDraft,0) as IsDraft, IsEmailed, EmailedBy, EmailedDate,
					isnull(IsCancelled,0) as IsCancelled, CancelledBy, CancelledDate, CancelNote,
					(CASE WHEN IsCancelled=1 THEN 'CANCELLED' 
						  WHEN IsApproved=1 THEN 'APPROVED'
						  WHEN IsApproved=2 THEN 'REJECTED'
						  WHEN IsDraft=1 THEN 'DRAFT'
						  WHEN ISNULL(y.CampaignID,'')='' THEN 'DRAFT' 
						  WHEN IsEmailed=0 THEN 'SAVED' 
						  ELSE 'WAITING FOR APPROVAL' END) as CampaignStatus			
				from TblCampaignPlanHD x LEFT JOIN (SELECT DISTINCT CampaignID, ProductID FROM TblCampaignPlanDTBreakdowns WHERE IsSelected=1) y 
					on x.CampaignID=y.CampaignID and x.ProductID=y.ProductID
				where x.CampaignID = '" . $CampaignID . "' and ItemID=".$ItemID."
				order by x.ProductID ";
		//echo($str);
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->row();
		} else {
			return null;
		}
	}

	function cancelPlan($data)
	{
		$lanjut = true;
		$result = array();
		$ERR_MSG = "";
		$ERR_CODE = 0;
		$this->db->trans_begin();
		
		$checkDT = $this->db->query("Select * From TblPOPlanDT Where PlanNo='".$data["PlanNo"]."' and BhaktiFlag='FINISHED'");
		if ($checkDT->num_rows()>0) {
	        return array("result"=>"FAILED", "planNo"=>$data["PlanNo"], "errMsg"=>"Plan PO #".$data["PlanNo"]." Telah Diupdate Ke Bhakti", "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());				
		} else {
			$checkHD = $this->db->query("Select top 1 * From TblPOPlanHD Where PlanNo='".$data["PlanNo"]."'");
			if ($checkHD->num_rows()>0) {
				if ($checkHD->row()->IsCanceled==1) {
			        return array("result"=>"FAILED", "planNo"=>$data["PlanNo"], "errMsg"=>"Plan PO #".$data["PlanNo"]." Telah Dibatalkan Sebelumnya", "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());				
			    } else if ($checkHD->row()->IsDeleted==1) {
    		        return array("result"=>"FAILED", "planNo"=>$data["PlanNo"], "errMsg"=>"Plan PO #".$data["PlanNo"]." Telah Dibatalkan Sebelumnya", "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());				
			    } else if ($checkHD->row()->IsApproved==2) {
    		        return array("result"=>"FAILED", "planNo"=>$data["PlanNo"], "errMsg"=>"Plan PO #".$data["PlanNo"]." Telah Direject Manager", "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());							    	
			    } else if ($checkHD->row()->IsApproved==1) {
			        return array("result"=>"FAILED", "planNo"=>$data["PlanNo"], "errMsg"=>"Plan PO #".$data["PlanNo"]." Telah Disetujui Manager", "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());				
			    } else {
			    	if ($checkHD->row()->PlanStatus=="WAITING FOR APPROVAL") {
						$str = "UPDATE TblPOPlanHD 
								SET IsCanceled=1, CanceledDate=GETDATE(),
									CanceledBy='".$_SESSION["logged_in"]["username"]."', 
									CancelNote='".$data["Alasan"]."',
									PlanStatus = 'CANCELED'
								WHERE PlanNo='".$data["PlanNo"]."'";
						$cancel = $this->db->query($str);
						if( ($errors = sqlsrv_errors() ) != null) {
					        foreach( $errors as $error ) {
					        	$ERR_CODE = $error["code"];
					            $ERR_MSG.= "message: ".$error[ 'message']." ";
					        }
					        $this->db->trans_rollback();
					        return array("result"=>"FAILED", "planNo"=>$data["PlanNo"], "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
					    } else {
					    	$this->db->trans_commit();
					        return array("result"=>"SUCCESS", "planNo"=>$data["PlanNo"], "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());
					    }
					} else {
						$str = "UPDATE TblPOPlanHD 
								SET IsDeleted=1, DeletedDate=GETDATE(),
									DeletedBy='".$_SESSION["logged_in"]["username"]."', 
									DeleteNote='".$data["Alasan"]."',
									PlanStatus='DELETED' 
								WHERE PlanNo='".$data["PlanNo"]."'";
						$cancel = $this->db->query($str);
						if( ($errors = sqlsrv_errors() ) != null) {
					        foreach( $errors as $error ) {
					        	$ERR_CODE = $error["code"];
					            $ERR_MSG.= "message: ".$error[ 'message']." ";
					        }
					        $this->db->trans_rollback();
					        return array("result"=>"FAILED", "planNo"=>$data["PlanNo"], "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
					    } else {
					    	$this->db->trans_commit();
					        return array("result"=>"SUCCESS", "planNo"=>$data["PlanNo"], "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());
					    }
					}
				}
			} else {
		        return array("result"=>"FAILED", "planNo"=>$data["PlanNo"], "errMsg"=>"Plan PO #".$data["PlanNo"]." Tidak Ditemukan", "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());				
			}
		}
	}


	function EmailRequestSent($PlanNo, $BM, $success=true)
	{
		$this->db->where("PlanNo", $PlanNo);
		$this->db->set("PlanStatus", "WAITING FOR APPROVAL");
		$this->db->set("IsApproved", 0);
		$this->db->set("ApprovedBy", $BM->email_address);
		$this->db->set("IsEmailed", (($success==true)?1:2));
		$this->db->set("EmailedBy", $_SESSION["logged_in"]["username"]);
		$this->db->set("EmailedDate", date("Y-m-d H:i:s"));
		$this->db->update("TblPOPlanHD");
	}
}
