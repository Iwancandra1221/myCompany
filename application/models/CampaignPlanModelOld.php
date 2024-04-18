<?php
class CampaignPlanModel extends CI_Model
{
	function getAutoNumber($division)
	{
		$period = date('Ym');
		if ($division == 'MIYAKOKR' || $division == 'MICOOK') {
			$div = 'MR';
		} else {
			$div = substr($division, 0, 2);
		}

		$format = "$div/$period/";
		$this->db->limit(1, 0);
		$this->db->order_by('CampaignID', 'desc');
		$this->db->like('CampaignID', $format);
		$this->db->select('right(CampaignID, 4) CampaignID ', false);
		$res = $this->db->get('TblCampaignPlanHD');
		if ($res->num_rows() > 0) {
			$number = sprintf("%04d", $res->row()->CampaignID + 1);
			return $format . $number;
		} else
			return $format . '0001';
	}

	function GetTransaksiPerencanaan($CampaignID)
	{
		$str = "Select a.*, b.Kd_Lokasi 
				From TblCampaignPlanDT a inner join TblCampaignPlanWilayahInclude b on a.Kota=b.Wilayah and a.CampaignID=b.CampaignID
				Where a.CampaignID = '" . $CampaignID . "'";
		//die($str);
		$res = $this->db->query($str);
		return json_encode($res->result());
	}

	function GetTransaksiDetail($CampaignID)
	{
		$str = "Select x.CampaignID, CampaignName, isnull(ItemID,0) as ItemID, 
					upper(x.ProductID) as ProductID, Division, CampaignStartHD, CampaignEndHD, 
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
					on x.CampaignID=y.CampaignID and upper(x.ProductID)=upper(y.ProductID)
					where x.CampaignID = '" . $CampaignID . "'
				order by upper(x.ProductID) ";

		$res = $this->db->query($str);
		return json_encode($res->result());
	}
	
	function CheckItemID($trxID, $data) 
	{
		//die(json_encode($data));	
		foreach($data as $d) {
			if ($d->ItemID==null || $d->ItemID==0) {
				$NewItemID = $this->GetMaxItemID($trxID);
				$upd = $this->db->query("Update TblCampaignPlanHD Set ItemID=".$NewItemID." Where CampaignID='".$trxID."' and ProductID='".$d->ProductID."'");
			}
		}
		$planHD = $this->GetTransaksiDetail($trxID);
		return $planHD;
	}

	function GetMaxItemID($trxID)
	{
		$str = "Select MAX(isnull(ItemID,0)) as ItemIDMax From TblCampaignPlanHD Where CampaignID='".$trxID."'";
		$res = $this->db->query($str);
		if ($res->num_rows()==0) {
			return 1;
		} else {
			$max = $res->row()->ItemIDMax;
			$max = $max+1;
			return $max;
		}

	}

	function GetInsertedDT($CampaignID)
	{
		$str = "select DISTINCT CampaignID from TblCampaignPlanDT where CampaignID = '" . $CampaignID . "'";

		$res = $this->db->query($str);
		if($res->num_rows()>0)
			return true;
		else 
			return false;
	}
	
	function GetTransaksiWilayahInclude($CampaignID)
	{
		$str = "select * from TblCampaignPlanwilayahInclude where CampaignID = '" . $CampaignID . "' Order By Wilayah";
		$res = $this->db->query($str);
		return json_encode($res->result());
	}

	function GetPerencanaanSummary($CampaignID)
	{
		$str = " select distinct a.Kd_Lokasi, a.ProductID as Kd_Brg, a.Kota, a.Jns_Trx, a.Nm_Trx, a.Total_Jual, a.TotalHari, 
		case when ISNULL(a.Jns_Trx,'') = b.Jns_Trx and a.Kota = b.Kota and a.ProductID = b.ProductID  
				then isnull (a.avg_rounded,b.avg_rounded) else ISNULL(a.avg_rounded,0) end as avg_rounded,
		case when ISNULL(a.Jns_Trx,'') = b.Jns_Trx and a.Kota = b.Kota and a.ProductID = b.ProductID  
				  then isnull (a.tot_avg,b.tot_avg) else ISNULL(a.tot_avg,0) end as tot_avg,
		case when ISNULL(a.Jns_Trx,'') = b.Jns_Trx and a.Kota = b.Kota and a.ProductID = b.ProductID  
				  then 
				  1
				  else 
				  0 
				  end as flag ,
		a.avg_harian_perthn 
		from TblCampaignPlanDTSummary a 
		left join TblCampaignPlanDT b on  a.CampaignID=b.CampaignID and a.ProductID = b.ProductID and a.Kota=b.Kota
		Where a.CampaignID = '" . $CampaignID . "'
		group by a.Kd_Lokasi, a.ProductID, b.ProductID, a.Kota, b.Kota, a.Jns_Trx, a.Nm_Trx, a.Total_Jual, a.TotalHari, a.avg_rounded,
		a.tot_avg , b.Jns_Trx, b.tot_avg , a.avg_harian_perthn, b.avg_rounded
		order by a.ProductID, Kota, Total_Jual Desc";
		

		
		$res = $this->db->query($str);
		
		return json_encode($res->result());
	}

	function GetTransaksiList($post)
	{
		//## Read value
		$index = $post['index'];
		$row = $post['row'];
		$rowperpage = $post['rowperpage']; // Rows display per page
		$columnName = $post['columnName']; // Column name
		$columnSortOrder = $post['columnSortOrder']; // asc or desc
		$searchValue = $post['searchValue']; // Search value
		$endrow = $row + $rowperpage;

		## Search 
		$searchQuery = " ";
		if ($searchValue != '') {
			$searchQuery = " (CampaignID like '%" . $searchValue . "%' or 
							CampaignName like '%" . $searchValue . "%' or
							Division like'%" . $searchValue . "%' ) ";
		}

		## Total number of records without filtering
		$str = "select COUNT(*) as allcount
			from (
			select distinct CampaignID, CampaignName, Division from TblCampaignPlanHD )a";

		$res = $this->db->query($str);
		
		$totalRecords = $res->row()->allcount;

		## Total number of record with filtering
		if ($searchValue != '') {
			$str = "select COUNT(*) as allcount 
				from (
				select distinct CampaignID, CampaignName, Division from TblCampaignPlanHD )a where " . $searchQuery . "";
			$res = $this->db->query($str);
			$totalRecordwithFilter = $res->row()->allcount;
		} else {
			$str = "select COUNT(*)  as allcount
				from (
				select distinct CampaignID, CampaignName, Division from TblCampaignPlanHD )a ";
			$res = $this->db->query($str);
			$totalRecordwithFilter = $res->row()->allcount;
		}

		## Fetch records
		if ($searchValue != '') {
			// $empQuery = "
				// SELECT *
				// FROM TblCampaignPlanHD
				// WHERE " . $searchQuery;
			$empQuery = "
				
				WITH Results AS(
					select * , DATEDIFF(DAY, a.CampaignStart, a.CampaignEnd)+1 as JumlahHari, ROW_NUMBER() OVER (ORDER BY CampaignID ASC) AS RowNum 
					from (
						select distinct CampaignID, CampaignName, Division, MIN(CampaignStart) AS CampaignStart , MAX(CampaignEnd) AS CampaignEnd, IsApproved ,  ISNULL(Jum,0) as Jum
						from TblCampaignPlanHD 
						LEFT JOIN (
						SELECT COUNT(DISTINCT CampaignID) AS Jum, CampaignID as CampID
							FROM TblCampaignPlanDT
							GROUP BY CampaignID
						) dt ON dt.CampID = TblCampaignPlanHD.CampaignID
						WHERE " . $searchQuery."
						GROUP BY  CampaignID, CampaignName, Division, IsApproved ,Jum
						 )a
					) SELECT * FROM Results WHERE RowNum >" . $row . "
						AND RowNum <= " . $endrow . " order by " . $columnName . " " . $columnSortOrder;
						
						
		} else {
			$empQuery = "WITH Results AS(
					select * , DATEDIFF(DAY, a.CampaignStart, a.CampaignEnd)+1 as JumlahHari, ROW_NUMBER() OVER (ORDER BY CampaignID ASC) AS RowNum 
					from (
						select distinct CampaignID, CampaignName, Division, MIN(CampaignStart) AS CampaignStart , MAX(CampaignEnd) AS CampaignEnd, IsApproved , ISNULL(Jum,0) as Jum
						from TblCampaignPlanHD 
						
						LEFT JOIN (
						SELECT COUNT(DISTINCT CampaignID) AS Jum, CampaignID as CampID
							FROM TblCampaignPlanDT
							GROUP BY CampaignID
						) dt ON dt.CampID = TblCampaignPlanHD.CampaignID
						
						GROUP BY  CampaignID, CampaignName, Division, IsApproved ,Jum
						 )a
					) SELECT * FROM Results WHERE RowNum >" . $row . "
						AND RowNum <= " . $endrow . " order by " . $columnName . " " . $columnSortOrder;
		}
		
		// echo $empQuery;

		$empRecords = $this->db->query($empQuery);
		
		$data = array();
		$dt = array();

		for ($x = 0; $x <= count($empRecords->result()) - 1; $x++) {
			$dt[] = array(
				"btn" => "<button type='button' onclick='TrxTerpilih(\"" . $empRecords->result()[$x]->CampaignID . "\",\"" . $empRecords->result()[$x]->IsApproved . "\",\"" . $empRecords->result()[$x]->CampaignName . "\",\"" . $empRecords->result()[$x]->Division . "\",\"" . date('d-M-Y',strtotime($empRecords->result()[$x]->CampaignStart)) . "\",\"" . date('d-M-Y',strtotime($empRecords->result()[$x]->CampaignEnd)) . "\",\"" . $empRecords->result()[$x]->JumlahHari . "\",\"" . $index . "\",\"" . $empRecords->result()[$x]->Jum . "\")' id='terpilih'>Pilih</button>",
				"CampaignID" => $empRecords->result()[$x]->CampaignID,
				"CampaignName" => $empRecords->result()[$x]->CampaignName,
				"Divisi" => $empRecords->result()[$x]->Division
			);
		}

		$data["dt"] = $dt;
		$data["totalRecordwithFilter"] = $totalRecordwithFilter;
		$data["totalRecords"] = $totalRecordwithFilter; //$totalRecords;
		return $data;
	}

	function insertSummary($campaignId,$data)
	{
		$this->db->trans_start();
		// print_r($data);die();
		$this->db->where('CampaignID', $campaignId);
		$this->db->delete('TblCampaignPlanDTSummary');

		// echo 'aaa';die;
		for ($i = 0; $i < count($data); $i++) {
			for ($x = 0; $x < count($data[$i]); $x++) {
				// print_r($data[$i][$x]);
				// die;
				$dataHeader = array(
					'CampaignId' => $campaignId,
					'Kota' => $data[$i][$x]->Kota,
					'ProductID' => $data[$i][$x]->Kd_Brg,
					'Jns_Trx' =>  $data[$i][$x]->Jns_Trx,
					'Nm_Trx' =>  $data[$i][$x]->Nm_Trx,
					'Total_Jual' =>  $data[$i][$x]->Total_Jual,
					'TotalHari' =>  $data[$i][$x]->TotalHari,
					'avg_rounded' =>  $data[$i][$x]->avg_rounded,
					'tot_avg' =>  $data[$i][$x]->tot_avg,
					'Kd_Lokasi' =>  $data[$i][$x]->Kd_Lokasi,
					'avg_harian_perthn' => $data[$i][$x]->avg_harian_perthn
				);
				$result['header'][] = $dataHeader;
				// print_r($result['header']);
				$this->db->insert('TblCampaignPlanDTSummary', $dataHeader);
			}
		}
		
		$this->db->trans_complete();

		return $result;
	}

	function insertHD($data){
		
		$this->db->trans_start();

		$hd = $data->header;
		
		$CampaignID = $this->getAutoNumber($hd[0]->Division);
		
		foreach ($hd as $h) {
			
			$dataHeader = array(
			'CampaignID' => $CampaignID,
			'CampaignName' => $h->CampaignName,
			'ProductID' => $h->ProductID,
			'Division' => $h->Division,
			'CampaignStart' => $h->CampaignStart,
			'CampaignEnd' => $h->CampaignEnd,
			'JumlahHari' => $h->JumlahHari,
			'CreatedBy' => $_SESSION["logged_in"]["useremail"], 
			'CreatedDate' => date('Y-m-d H:i:s'),
			'IsApproved' => 0
		);
		$result['header'][] = $dataHeader;
		
		$this->db->insert('TblCampaignPlanHD', $dataHeader);
			
		}
			
		if (isset($data->wilayahInclude)) {
			
			$wil = $data->wilayahInclude;
			$lokasi = $data->wilayahIncludeKdLokasi;
			
			for ($i = 0; $i < count($wil); $i++) {
				$dataWilayah = array(
					'CampaignID' => $CampaignID,
					'Wilayah' => $wil[$i],
					'Kd_Lokasi' => $lokasi[$i]
				);
				$this->db->insert('TblCampaignPlanwilayahInclude', $dataWilayah);
			}
		}
		
		$this->db->trans_complete();
		return $CampaignID;	
	}
	
	function insertDT($CampaignID,$dataDetail){
		
		$this->db->trans_start();
		
		$this->db->where('CampaignID', $CampaignID);
		$this->db->delete('TblCampaignPlanDT');
		
		foreach ($dataDetail['data'] as $p) {
			$detail = array(
				'CampaignID' => $CampaignID,
				'ProductID' => $p->Kd_Brg,
				'Kota' => $p->Kota,
				'Jns_Trx' => $p->Jns_Trx,
				'Nm_Trx' => $p->Nm_Trx,
				'Total_Jual' => $p->Total_Jual,
				'TotalHari' => $p->TotalHari,
				'avg_rounded' => $p->avg_rounded,
				'tot_avg' => $p->tot_avg,
				'Kd_GroupGudang' => $p->Kd_GroupGudang,
				'avg_harian_perthn' => $p->avg_harian_perthn
			);
			$result['detail'][] = $detail;
			
			$this->db->insert('TblCampaignPlanDT', $detail);
			
		}
		
		$this->db->trans_complete();
		return $CampaignID;
	}

	function updateHD($data){
	
		$this->db->trans_start();
		$hd = $data->header;
		$CampaignID = $hd[0]->CampaignID;
		$str = "select distinct CreatedBy, CreatedDate from TblCampaignPlanHD where CampaignID = '" . $CampaignID . "'";
		$res = $this->db->query($str);		
		
		if(!isset($res->row(0)->CreatedBy)){
			$CreatedBy = $_SESSION["logged_in"]["useremail"];
		}else{
			$CreatedBy =$res->row(0)->CreatedBy;
		}
		if(!isset($res->row(0)->CreatedDate)){
			$CreatedDate = date('Y-m-d H:i:s');
		}else{
			$CreatedDate =$res->row(0)->CreatedDate;
		}
		
		$this->db->where('CampaignID', $CampaignID);
		$this->db->delete('TblCampaignPlanHD');
		$this->db->where('CampaignID', $CampaignID);
		$this->db->delete('TblCampaignPlanwilayahInclude');

		foreach ($hd as $h) {
			
			$dataHeader = array(
			'CampaignID' => $CampaignID,
			'CampaignName' => $h->CampaignName,
			'ProductID' => $h->ProductID,
			'Division' => $h->Division,
			'CampaignStart' => $h->CampaignStart,
			'CampaignEnd' => $h->CampaignEnd,
			'JumlahHari' => $h->JumlahHari,
			'CreatedBy' => $CreatedBy, 
			'CreatedDate' => $CreatedDate,
			'UpdatedBy' => $_SESSION["logged_in"]["useremail"], 
			'UpdatedDate' => date('Y-m-d H:i:s'),
			'IsApproved' => 0
		);
			$result['header'][] = $dataHeader;
			   
			$this->db->insert('TblCampaignPlanHD', $dataHeader);
		}
		
		if (isset($data->wilayahInclude)) {
			
			// print_r($data->wilayahInclude)
			// die;
			
			$wil = $data->wilayahInclude;
			$lokasi = $data->wilayahIncludeKdLokasi;
			
			for ($i = 0; $i < count($wil); $i++) {
				$dataWilayah = array(
					'CampaignID' => $CampaignID,
					'Wilayah' => $wil[$i],
					'Kd_Lokasi' => $lokasi[$i]
				);
				$this->db->insert('TblCampaignPlanwilayahInclude', $dataWilayah);
			}
		}



		$this->db->trans_complete();

		return $CampaignID;
	}

	function getApproved($id) {
		$str = "
			select distinct isApproved, convert(varchar, ApprovedDate, 13) as ApprovedDate, ApprovedByName
			from  TblCampaignPlanHD
			where CampaignID = '".$id."'
		";

		$res = $this->db->query($str);
		return $res->result();
	}

	function getEmail($division) {
		$str = "
			select email_address
			from  tb_salesman
			where level_slsman= 'BRAND MANAGER' and Division = '".htmlspecialchars($division)."'
		";

		// $str = "
		// 	select email_address
		// 	from  tb_salesman_test
		// 	where level_slsman= 'BRAND MANAGER' and Division = '".htmlspecialchars($division)."'
		// ";
		
		$res = $this->db->query($str);
		return $res->row();
	}


	function ApproveOnly($CampaignID, $data) {
		set_time_limit(60);

		$this->db->trans_start();
		$this->db->where('CampaignID',$CampaignID);
		$this->db->set('isApproved',$data['isApproved']);
		$this->db->set('ApprovedBy',$data['ApprovedBy']);
		$this->db->set('ApprovedDate',date('Y-m-d H:i:s'));
		$this->db->update('TblCampaignPlanHD');

		$this->db->where("CampaignID", $CampaignID);
		$this->db->where("IsSelected",0);
		$this->db->set("BhaktiFlag", "-");
		$this->db->set("BhaktiProcessDate", date("Y-m-d H:i:s"));
		$this->db->update("TblCampaignPlanDTBreakdowns");

		$this->db->where("CampaignID", $CampaignID);
		$this->db->where("IsSelected",1);
		$this->db->set("BhaktiFlag", "UNPROCESSED");
		$this->db->set("BhaktiProcessDate", date("Y-m-d H:i:s"));
		$this->db->update("TblCampaignPlanDTBreakdowns");

		$this->db->trans_complete();
		echo '<center>Berhasil approve, Silahkan tutup halaman ini!</center>';
	}

	function GetListApprovedUnprocessed()
	{
		$str = "UPDATE TblCampaignPlanDTBreakdowns SET BhaktiFlag = 'CLOSED', BhaktiProcessDate='".date("Y-m-d H:i:s")."'
				 WHERE CampaignID in (Select CampaignID From TblCampaignPlanHD where ApprovedDate<='2021-03-15' and IsApproved=1)
				 and IsSelected=1 and Total_Qty<>0 and BhaktiFlag='UNPROCESSED'";
		$this->db->query($str);

		$str = "SELECT DISTINCT a.CampaignID, a.CampaignName, a.ApprovedBy, a.ApprovedByName, a.ApprovedDate
						 FROM TblCampaignPlanHD a inner join TblCampaignPlanDTBreakdowns b on a.CampaignID=b.CampaignID and a.ProductID=b.ProductID
						 WHERE a.ApprovedDate>='2021-01-14' and a.IsApproved=1 and b.IsSelected=1 and b.Total_Qty<>0 and b.BhaktiFlag='UNPROCESSED'
						 order by a.ApprovedDate";
		//die($str);
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function SendApprovedPlanToBhakti($CampaignID, $data)
	{
		// die($CampaignID);
		$strLokasi = "select Distinct Wilayah, Kd_Lokasi from TblCampaignPlanWilayahInclude WHERE campaignID= '".$CampaignID."'";
		// die($strLokasi);
		$x = $this->db->query($strLokasi);
		if ($x->num_rows()==0) {
			// Jika Data Wilayah Include Kosong, maka diisi ulang dari detail Breakdowns yang IsSelected=1
			$insWilayah = "insert into TblCampaignPlanWilayahInclude (CampaignID, Wilayah, Kd_Lokasi, CreatedBy, CreatedDate, DraftAdd, DraftRemove, DraftBy, DraftDate)
						Select CampaignID, Kota, Kd_Lokasi, CreatedBy, MIN(CreatedDate) as CreatedDate, 0,0, NULL, NULL 
						from TblCampaignPlanDTBreakdowns 
						where CampaignID='".$CampaignID."' and IsSelected=1 
						Group By CampaignID, Kota, Kd_Lokasi, CreatedBy";
			$this->db->query($insWilayah);

			$x = $this->db->query($strLokasi);
		} 

		$result = $x->result();
		// die(json_encode($result));		
		// print_r($result);
		// print_r("<br>");
		// die;
		
		$lokasi='';
		$wilayah='';
		$connected = false;

		foreach ($result as $z) {
			$connected = true;
			//echo(json_encode($z)."<br>");
			$strCabang = "";

			$strCabang = "select TOP 1 * from MsDatabase where branchid = 'JKT' and NamaDb= 'JAKARTA'";
			/*if($z->Kd_Lokasi=='DMI'){
				$strCabang = "select * from MsDatabase where branchid = 'JKT' and NamaDb= 'JAKARTA'";
			} else if($z->Kd_Lokasi=='BOG'){
				$strCabang = "select * from MsDatabase where branchid = 'JKT' and NamaDb= 'BOGOR'";
			} else if($z->Kd_Lokasi=='KRW'){
				$strCabang = "select * from MsDatabase where branchid = 'JKT' and NamaDb= 'KARAWANG'";
			} else if($z->Kd_Lokasi=='SRY'){
				$strCabang = "select * from MsDatabase where branchid = 'SBY'";
			} else{
				$strCabang = "select * from MsDatabase where branchid = '".$z->Kd_Lokasi."'";
			}*/

			$res = $this->db->query($strCabang);
			if ($res->num_rows()>0) {
				$cabang = $res->row();
				// die(json_encode($cabang));
				echo($cabang->NamaDb);
				echo("<br><br>Check Connection:<br>");

				/*Baris Berikut Hanya Untuk Test*/
				$cabang->AlamatWebService = "http://localhost/";
				/********************************/

				$cURL = $cabang->AlamatWebService."bktAPI/Billing/TesVb6";
				$ch = curl_init($cURL);
				curl_setopt($ch, CURLOPT_TIMEOUT, 3);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
				$response = curl_exec($ch);
				// die(json_encode($ch));
				// die("<br><br>");

				if ($response === false) {
					echo("THIS JOB CAN'T CALL API DB BHAKTI CBG :<br>".$cURL."<br><br>");
					$connected = false;			

				} else { 
					echo("Check Connection's Successful<br><br>");
					$connected = true;

					$str2 = "SELECT a.CampaignID, a.CampaignName, a.Division as Divisi, a.ProductID as Kd_Brg, b.Kota as Wilayah, b.Kd_Lokasi,
								CONVERT(varchar,CampaignStart,101) as CampaignStart,
								CONVERT(varchar,CampaignEnd,101) as CampaignEnd,
								a.JumlahHari, b.Total_Qty as Qty, a.CreatedBy, 
								CONVERT(varchar,a.CreatedDate,101) +' '+CONVERT(varchar,a.CreatedDate,108) as CreatedDate,
								a.ApprovedBy,
								CONVERT(varchar,a.ApprovedDate,101) +' '+CONVERT(varchar,a.ApprovedDate,108) as ApprovedDate						
							 FROM TblCampaignPlanHD a inner join TblCampaignPlanDTBreakdowns b on a.CampaignID=b.CampaignID and a.ProductID=b.ProductID
							 WHERE a.CampaignID='".$CampaignID."' and b.Kota='".$z->Wilayah."' and b.IsSelected=1 and b.Total_Qty<>0 
							 		and b.BhaktiFlag='UNPROCESSED'";
						
					// echo $str2;
					// echo '<br><br>';
			
					$res2 = $this->db->query($str2);
					$details = $res2->result();
					
					// echo(json_encode($res2->result())."<br><br>");

					foreach($details as $dt) {
						if ($dt->Qty==0) {
							$this->db->where("CampaignID", $CampaignID);
							$this->db->where("ProductID", $dt->Kd_Brg);
							$this->db->where("Kota", $dt->Wilayah);
							$this->db->where("IsSelected",1);
							$this->db->set("BhaktiFlag", "FINISHED");
							$this->db->set("BhaktiProcessDate", date("Y-m-d H:i:s"));
							$this->db->set("BhaktiProcessNote", "SUCCESSFUL");
							$this->db->set("RetryCount", "CASE WHEN isnull(RetryCount,0)=0 THEN 0 ELSE isnull(RetryCount,0)+1 END", FALSE);
							$this->db->update("TblCampaignPlanDTBreakdowns");

						} else if ($connected) {

							$campaignPlan['value'] = $dt;
							$campaignPlan['kota'] = $cabang->NamaDb;
							$campaignPlan['Server'] = $cabang->Server;
							$campaignPlan['Database'] = $cabang->Database;
							$campaignPlan['Uid'] = SQL_UID;
							$campaignPlan['Pwd'] = SQL_PWD;
							$campaignPlan['api'] = 'APITES';

							
							//if ($cabang->NamaDb=="JAKARTA") {
							//$cabang->AlamatWebService = "http://localhost/";
							//}
							//echo(json_encode($campaignPlan)."<br><br>");
							//echo($cabang->AlamatWebService. "bktAPI/CampaignPlan/insertCampaignPlan<br><br>");

							$curl = curl_init();
							curl_setopt_array($curl, array(
								CURLOPT_URL => $cabang->AlamatWebService. "bktAPI/CampaignPlan/insertCampaignPlan",
								CURLOPT_RETURNTRANSFER => true,
								CURLOPT_TIMEOUT => 300,
								CURLOPT_POST => 1,
								CURLOPT_POSTFIELDS => json_encode($campaignPlan),
								CURLOPT_HTTPHEADER => array("Content-type: application/json")
							));

							$response2 = curl_exec($curl);
							$err = curl_error($curl);
							curl_close($curl);

							print_r($response2."<br>");
							echo("<br>");
							//print_r($err);
							// die;

							if ($response2!="") {
								$resp=json_decode($response2);
								//echo("SUKSES<br><br>");
								if (strtoupper($resp->result)=="SUKSES") {

									$this->db->where("CampaignID", $CampaignID);
									$this->db->where("ProductID", $dt->Kd_Brg);
									$this->db->where("Kota", $dt->Wilayah);
									$this->db->where("IsSelected",1);
									$this->db->set("BhaktiFlag", "FINISHED");
									$this->db->set("BhaktiProcessDate", date("Y-m-d H:i:s"));
									$this->db->set("BhaktiProcessNote", "SUCCESSFUL");
									$this->db->set("RetryCount", "CASE WHEN isnull(RetryCount,0)=0 THEN 0 ELSE isnull(RetryCount,0)+1 END", FALSE);
									$this->db->update("TblCampaignPlanDTBreakdowns");

								} else {

									$this->db->where("CampaignID", $CampaignID);
									$this->db->where("ProductID", $dt->Kd_Brg);
									$this->db->where("Kota", $dt->Wilayah);
									$this->db->where("IsSelected",1);
									$this->db->set("BhaktiFlag", "UNPROCESSED");
									$this->db->set("BhaktiProcessDate", date("Y-m-d H:i:s"));
									$this->db->set("BhaktiProcessNote", $resp->error);
									$this->db->set("RetryCount","isnull(RetryCount,0)+1",FALSE);
									$this->db->update("TblCampaignPlanDTBreakdowns");											

								}
							} else {
								//echo("GAGAL<br><br>");
								$this->db->where("CampaignID", $CampaignID);
								$this->db->where("ProductID", $dt->Kd_Brg);
								$this->db->where("Kota", $dt->Wilayah);
								$this->db->where("IsSelected",1);
								$this->db->set("BhaktiFlag", "UNPROCESSED");
								$this->db->set("BhaktiProcessDate", date("Y-m-d H:i:s"));
								$this->db->set("BhaktiProcessNote", "GAGAL PANGGIL API BHAKTI");
								$this->db->set("RetryCount","isnull(RetryCount,0)+1",FALSE);
								$this->db->update("TblCampaignPlanDTBreakdowns");											
							}
						} else {

							$this->db->where("CampaignID", $CampaignID);
							$this->db->where("ProductID", $dt->Kd_Brg);
							$this->db->where("Kota", $dt->Wilayah);
							$this->db->where("IsSelected",1);
							$this->db->set("BhaktiFlag", "UNPROCESSED");
							$this->db->set("BhaktiProcessDate", date("Y-m-d H:i:s"));
							$this->db->set("BhaktiProcessNote", "GAGAL PANGGIL API BHAKTI");
							$this->db->set("RetryCount","isnull(RetryCount,0)+1",FALSE);
							$this->db->update("TblCampaignPlanDTBreakdowns");																	

						}
					}
				}
				
			} else {
				$this->db->where("CampaignID", $CampaignID);
				$this->db->where("Kota", $z->Wilayah);
				$this->db->where("IsSelected",1);
				$this->db->set("BhaktiFlag", "UNPROCESSED");
				$this->db->set("BhaktiProcessDate", date("Y-m-d H:i:s"));
				$this->db->set("BhaktiProcessNote", "MS DATABASE TIDAK DITEMUKAN");
				$this->db->set("RetryCount","isnull(RetryCount,0)+1",FALSE);
				$this->db->update("TblCampaignPlanDTBreakdowns");											

			}
		}
		
		// echo '<center>Berhasil approve, Silahkan tutup halaman ini!</center>';
	}

	function Rejected($CampaignID, $data) {
		$this->db->trans_start();

		$this->db->where('CampaignID',$CampaignID);
		$this->db->set('isApproved',$data['isApproved']);
		$this->db->set('ApprovedBy',$data['ApprovedBy']);
		$this->db->set('ApprovedDate',date('Y-m-d H:i:s'));
		$this->db->set('CancelNote',$data['CancelNote']);
		$this->db->update('TblCampaignPlanHD');

		$this->db->trans_complete();
		echo '<center>Berhasil reject, Silahkan tutup halaman ini!</center>';
	}

	function getInsertedData($CampaignID)
	{
		
		$str = "
		DECLARE 
			@columns NVARCHAR(MAX) = '', 
			@sql     NVARCHAR(MAX) = '';
		
		-- select the category names
		SELECT 
			@columns+=QUOTENAME(productid) + ','
		FROM 
			TblCampaignPlanHD
		WHERE
			CampaignID = '" . $CampaignID . "' 
		ORDER BY 
			productid;
		
		-- remove the last comma
		SET @columns = LEFT(@columns, LEN(@columns) - 1);


		-- construct dynamic SQL
		SET @sql ='
		SELECT * FROM   
		(
			SELECT 
				kota as Wilayah, 
				b.productid,
				b.tot_avg
			FROM 
				TblCampaignPlanHD a
				INNER JOIN TblCampaignPlanDT b 
					ON a.campaignid = b.campaignid and a.ProductID=b.ProductID 
			WHERE
			a.CampaignID = ''" . $CampaignID . "'' 
		) t 
		PIVOT(
			sum(tot_avg) 
			FOR productid IN ('+ @columns +')
		) AS pivot_table;';
		
		-- execute the dynamic SQL
		EXECUTE sp_executesql @sql
		 
			";
			
		$res = $this->db->query($str);

		return $res->result_array();
	}

	function getList($post)
	{
		
		$str = "SELECT CampaignID, CampaignName, Division, CampaignStart, CampaignEnd, JumlahHari,
					CreatedBy, CreatedDate, UpdatedBy, UpdatedDate, IsApproved, ApprovedBy, ApprovedByName, ApprovedByEmail, ApprovedDate,
					IsDraft, IsEmailed, EmailedBy, EmailedDate, IsCancelled, CancelledBy, CancelledDate,
					case CampaignStatus when 0 then 'CANCELLED' when 1 then 'DRAFT' when 2 then 'SAVED' when 3 then 'WAITING FOR APPROVAL'
					when 4 then 'APPROVED' when 5 then 'REJECTED' else 'SAVED' end as CampaignStatus 
				FROM (
					SELECT x.CampaignID, MAX(CampaignName) as CampaignName, Division, MAX(CampaignStartHD) as CampaignStart, MAX(CampaignEndHD) as CampaignEnd,
						MAX(CASE WHEN isnull(JumlahHariHD,0)=0 THEN (datediff(day, CampaignStartHD, CampaignEndHD)+1) ELSE isnull(JumlahHariHD,0) END) as JumlahHari,
						MAX(CreatedBy) as CreatedBy, MAX(CreatedDate) as CreatedDate, MAX(UpdatedBy) as UpdatedBy, MAX(UpdatedDate) as UpdatedDate, 
						isnull(IsApproved,0) as IsApproved, ApprovedBy, 
						ApprovedByName, ApprovedByEmail, MAX(ApprovedDate) as ApprovedDate, 
						isnull(IsDraft,0) as IsDraft, IsEmailed, EmailedBy, MAX(EmailedDate) as EmailedDate,
						isnull(IsCancelled,0) as IsCancelled, CancelledBy, MAX(CancelledDate) as CancelledDate,
						MIN(CASE WHEN (IsCancelled)=1 THEN 0 
							  WHEN (IsApproved)=1 THEN 4
							  WHEN (IsApproved)=2 THEN 5
							  WHEN IsDraft=1 THEN 1
							  WHEN ISNULL(y.CampaignID,'')='' THEN 1 
							  WHEN (IsEmailed)=0 THEN 2
							  ELSE 3 END) as CampaignStatus
					From TblCampaignPlanHD x LEFT JOIN (SELECT DISTINCT CampaignID, ProductID FROM TblCampaignPlanDTBreakdowns WHERE IsSelected=1) y 
						on x.CampaignID=y.CampaignID and x.ProductID=y.ProductID 
					Where 1=1 ";		
		$str .= "	GROUP BY x.CampaignID, Division, 
						isnull(IsApproved,0), ApprovedBy, ApprovedByName, ApprovedByEmail,
						isnull(IsDraft,0), IsEmailed, EmailedBy, isnull(IsCancelled,0), CancelledBy
				) CampaignPlan 
				WHERE CampaignStatus in (10";
		if ($post["ChkCancelled"]==1) $str.= ",0";
		if ($post["ChkDraft"]==1) $str.= ",1";
		if ($post["ChkSaved"]==1) $str.= ",2";
		if ($post["ChkWaiting"]==1) $str.= ",3";
		if ($post["ChkApproved"]==1) $str.= ",4";
		if ($post["ChkRejected"]==1) $str.= ",5";

		$str .= ") ";
		if ($post["ChkActive"]==1 && $post["ChkInActive"]==1) {

		} else if ($post["ChkActive"]==1) {
			$str .= " and CampaignEnd>='".date("Y-m-d")."' ";
		} else {
			$str .= " and CampaignEnd <'".date("Y-m-d")."' ";
		}
		$str .= " ORDER BY CampaignID DESC";
		// die($str);
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetPlanHD($CampaignID)
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
				where x.CampaignID = '" . $CampaignID . "'
				order by x.ProductID ";
		//echo($str);
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetPlanDT($CampaignID)
	{
		$str = "Select CampaignID, ProductID, Kota, Total_Qty as TotalQty
				from TblCampaignPlanDTBreakdowns
				where CampaignID = '" . $CampaignID . "' and IsSelected=1
				order by ProductID, Kota";
		//echo($str);
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}


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

	function saveDraft($data)
	{
		$lanjut = true;
		$result = array();
		$ERR_MSG = "";
		//$data["divisi"] = $data["divisi"];
		//die(json_encode($data));
		$isInsert = true; 

		$this->db->trans_begin();

		if ($data["kode_plan"]=="AUTONUMBER") {
			$data["kode_plan"] = $this->getAutoNumber($data["divisi"]);
		} else {
			$hd = $this->GetPlanHDByItemID($data["kode_plan"], $data["item_id"]);
			//die(json_encode($hd));
			if ($hd!=null) {
				if ($hd->IsDraft==1) {
					//Hapus Jika Menemukan Item Yang Sama dan Status IsDraft=1
					$delItem = $this->db->query("Delete From TblCampaignPlanHD Where CampaignID='".$data["kode_plan"]."' 
												 and ItemID='".$data["item_id"]."' and IsDraft=1");	
				} else {
					$isInsert=false;
				}
			}
		}		

		if ($isInsert) {

			$this->db->set("ItemID", $data["item_id"]);
			$this->db->set("CampaignID", strtoupper($data["kode_plan"]));
			$this->db->set("CampaignName", strtoupper(htmlspecialchars_decode($data["nama_plan"])));
			$this->db->set("ProductID", strtoupper(htmlspecialchars_decode($data["kd_brg"])));
			$this->db->set("Division", strtoupper(htmlspecialchars_decode($data["divisi"])));
			$this->db->set("CampaignStartHD", date("Y-m-d", strtotime($data["start_hd"])));
			$this->db->set("CampaignEndHD", date("Y-m-d", strtotime($data["end_hd"])));
			$this->db->set("JumlahHariHD", $data["jumlah_hari_hd"]);
			$this->db->set("CampaignStart", date("Y-m-d", strtotime($data["start_campaign"])));
			$this->db->set("CampaignEnd", date("Y-m-d", strtotime($data["end_campaign"])));
			$this->db->set("JumlahHari", $data["jumlah_hari"]);
			$this->db->set("CreatedBy", $_SESSION["logged_in"]["useremail"]);
			$this->db->set("CreatedDate", date("Y-m-d H:i:s"));
			$this->db->set("IsDraft", 1);
			$this->db->set("IsEmailed",0);
			$this->db->set("IsApproved", 0);				
			$this->db->set("IsCancelled", 0);
			$this->db->set("ItemID", $this->GetMaxItemID($data["kode_plan"]));
			$this->db->insert('TblCampaignPlanHD');

		} else {

			$this->db->where("ItemID", $data["item_id"]);
			$this->db->where("CampaignID", $data["kode_plan"]);
			$this->db->set("CampaignName", strtoupper(htmlspecialchars_decode($data["nama_plan"])));
			$this->db->set("ProductID", strtoupper(htmlspecialchars_decode($data["kd_brg"])));
			$this->db->set("Division", strtoupper(htmlspecialchars_decode($data["divisi"])));
			$this->db->set("CampaignStartHD", date("Y-m-d", strtotime($data["start_hd"])));
			$this->db->set("CampaignEndHD", date("Y-m-d", strtotime($data["end_hd"])));
			$this->db->set("JumlahHariHD", $data["jumlah_hari_hd"]);
			$this->db->set("CampaignStart", date("Y-m-d", strtotime($data["start_campaign"])));
			$this->db->set("CampaignEnd", date("Y-m-d", strtotime($data["end_campaign"])));
			$this->db->set("JumlahHari", $data["jumlah_hari"]);
			$this->db->set("CreatedBy", $_SESSION["logged_in"]["useremail"]);
			$this->db->set("CreatedDate", date("Y-m-d H:i:s"));
			$this->db->set("IsDraft", 1);
			$this->db->set("IsEmailed",0);
			$this->db->set("IsApproved", 0);				
			$this->db->set("IsCancelled", 0);
			$this->db->set("ItemID", $this->GetMaxItemID($data["kode_plan"]));
			$this->db->update('TblCampaignPlanHD');
			
		}

		if( ($errors = sqlsrv_errors() ) != null) {
	        foreach( $errors as $error ) {
	        	$ERR_CODE = $error["code"];
	            $ERR_MSG.= "message: ".$error[ 'message']." ";
	        }
	        $this->db->trans_rollback();
	        return array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
	    } else {
	    	$this->db->trans_commit();
	    	//die($this->db->last_query());
	    }
		

		$str = "Select * From TblCampaignPlanHD Where CampaignID='".$data["kode_plan"]."' and ProductID='".$data["kd_brg"]."'";
		$check = $this->db->query($str);
		if ($check->num_rows()>0) {
	        return array("result"=>"SUCCESS", "campaignId"=>$data["kode_plan"], "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());
		} else {
	        return array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>"SIMPAN GAGAL", "errCode"=>0, "lastQuery"=>$this->db->last_query());
		}		
	}

	function removeDraft($data)
	{
		$lanjut = true;
		$result = array();
		$ERR_MSG = "";
		$ERR_CODE = 0;
		$this->db->trans_begin();
		$checkItem = $this->db->query("Select * From TblCampaignPlanHD Where CampaignID='".$data["kode_plan"]."' and ProductID='".$data["kd_brg"]."'");
		if ($checkItem->num_rows()>0) {
			if ($checkItem->row()->ProductID==$data["kd_brg"]) {
				if ($checkItem->row()->IsDraft==1) {
					$delItem = $this->db->query("Delete From TblCampaignPlanHD Where CampaignID='".$data["kode_plan"]."' and ProductID='".$data["kd_brg"]."'");			
					if( ($errors = sqlsrv_errors() ) != null) {
				        foreach( $errors as $error ) {
				        	$ERR_CODE = $error["code"];
				            $ERR_MSG.= "message: ".$error[ 'message']." ";
				        }
				        $this->db->trans_rollback();
				        return array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
				    } else {
				    	$this->db->trans_commit();
				        return array("result"=>"SUCCESS", "campaignId"=>$data["kode_plan"], "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());
				    }
				} else {
			        return array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>"Item ".$data["kd_brg"]." Sudah Tidak Berstatus DRAFT", "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
				}
			} else {
		        return array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>"Item #".$data["kd_brg"]." Yang Tersimpan Sudah Berbeda. Reload kembali Data Campaign Plan dan Edit Kembali.", "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
			}
		}
	}

	function removeItem($data)
	{
		$lanjut = true;
		$result = array();
		$ERR_MSG = "";
		$ERR_CODE = 0;
		$this->db->trans_begin();
		$checkItem = $this->db->query("Select * From TblCampaignPlanHD Where CampaignID='".$data["kode_plan"]."' and ProductID='".$data["kd_brg"]."'");
		if ($checkItem->num_rows()>0) {
			if ($checkItem->row()->ProductID==$data["kd_brg"]) {
				$delItem = $this->db->query("Delete From TblCampaignPlanDTBreakdowns Where CampaignID='".$data["kode_plan"]."' and ProductID='".$data["kd_brg"]."'");			
				$delItem = $this->db->query("Delete From TblCampaignPlanDTSummary Where CampaignID='".$data["kode_plan"]."' and ProductID='".$data["kd_brg"]."'");			
				$delItem = $this->db->query("Delete From TblCampaignPlanPreviousCampaigns Where CampaignID='".$data["kode_plan"]."' and ProductID='".$data["kd_brg"]."'");			
				$delItem = $this->db->query("Delete From TblCampaignPlanPersentasePerWilayah Where CampaignID='".$data["kode_plan"]."' and ProductID='".$data["kd_brg"]."'");			
				
				$delItem = $this->db->query("Delete From TblCampaignPlanHD Where CampaignID='".$data["kode_plan"]."' and ProductID='".$data["kd_brg"]."'");			
				if( ($errors = sqlsrv_errors() ) != null) {
			        foreach( $errors as $error ) {
			        	$ERR_CODE = $error["code"];
			            $ERR_MSG.= "message: ".$error[ 'message']." ";
			        }
			        $this->db->trans_rollback();
			        return array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
			    } else {
			    	$this->db->trans_commit();
			        return array("result"=>"SUCCESS", "campaignId"=>$data["kode_plan"], "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());
			    }
			} else {
		        return array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>"Product id#".$data["kd_brg"]." Yang Tersimpan Sudah Berbeda. Reload kembali Data Campaign Plan dan Edit Kembali.", "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
			}
		}
	}

	function removeDrafts($campaignPlanId)
	{
		$lanjut = true;
		$result = array();
		$ERR_MSG = "";
		//die($data["divisi"]);

		$this->db->trans_begin();

		$delItem = $this->db->query("Delete From TblCampaignPlanHD Where CampaignID='".$campaignPlanId."' and IsDraft=1");			
		if( ($errors = sqlsrv_errors() ) != null) {
	        foreach( $errors as $error ) {
	        	$ERR_CODE = $error["code"];
	            $ERR_MSG.= "message: ".$error[ 'message']." ";
	        }
	        $this->db->trans_rollback();
	        return array("result"=>"FAILED", "campaignId"=>$campaignPlanId, "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
	    } else {
	    	$this->db->trans_commit();
	    }
		
        return array("result"=>"SUCCESS", "campaignId"=>$campaignPlanId, "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());
	}

	function saveDraftWilayah($data)
	{
		$lanjut = true;
		$result = array();
		$ERR_MSG = "";

		
		$wilayahExisted = false;
		$res = $this->db->query("Select CampaignID, Wilayah, Kd_Lokasi, isnull(DraftAdd,0) as DraftAdd, isnull(DraftRemove,0) as DraftRemove
								From TblCampaignPlanWilayahInclude 
								Where CampaignID='".strtoupper($data["kode_plan"])."' and Wilayah='".strtoupper($data["wilayah"])."'
									and Kd_Lokasi='".strtoupper($data["kode_lokasi"])."'");
		if ($res->num_rows()>0) {
			$wilayahExisted = true;
		}

		$this->db->trans_begin();

		if ($data["is_checked"]==0) {
			if ($wilayahExisted==true) {
				if ($res->row()->DraftAdd==1) {
					$this->db->where("CampaignID", $data["kode_plan"]);
					$this->db->where("Wilayah", $data["wilayah"]);
					$this->db->delete("TblCampaignPlanWilayahInclude");
				} else if ($res->row()->DraftRemove==0) {
					$this->db->where("CampaignID", $data["kode_plan"]);
					$this->db->where("Wilayah", $data["wilayah"]);
					$this->db->set("DraftAdd", 0);
					$this->db->set("DraftRemove",1);
					$this->db->set("DraftBy", $_SESSION["logged_in"]["username"]);
					$this->db->set("DraftDate", date("Y-m-d H:i:s"));
					$this->db->update("TblCampaignPlanWilayahInclude");
				} else {
					//do nothing
				}
			} else {
				//do nothing
			}
		} else {
			if ($wilayahExisted==true) {
				if ($res->row()->DraftRemove==1) {
					//remove draft
					$this->db->where("CampaignID", $data["kode_plan"]);
					$this->db->where("Wilayah", $data["wilayah"]);
					$this->db->set("DraftAdd", 0);
					$this->db->set("DraftRemove",0);
					$this->db->set("DraftBy", $_SESSION["logged_in"]["username"]);
					$this->db->set("DraftDate", date("Y-m-d H:i:s"));
					$this->db->update("TblCampaignPlanWilayahInclude");
				} else {
					$this->db->where("CampaignID", $data["kode_plan"]);
					$this->db->where("Wilayah", $data["wilayah"]);
					$this->db->set("DraftAdd", 1);
					$this->db->set("DraftRemove",0);
					$this->db->set("DraftBy", $_SESSION["logged_in"]["username"]);
					$this->db->set("DraftDate", date("Y-m-d H:i:s"));
					$this->db->update("TblCampaignPlanWilayahInclude");
				}
			} else {
				$this->db->set("CampaignID", $data["kode_plan"]);
				$this->db->set("Wilayah", $data["wilayah"]);
				$this->db->set("Kd_Lokasi", $data["kode_lokasi"]);
				$this->db->set("CreatedBy", $_SESSION["logged_in"]["username"]);
				$this->db->set("CreatedDate", date("Y-m-d H:i:s"));
				$this->db->set("DraftAdd", 1);
				$this->db->set("DraftRemove",0);
				$this->db->set("DraftBy", $_SESSION["logged_in"]["username"]);
				$this->db->set("DraftDate", date("Y-m-d H:i:s"));
				$this->db->insert("TblCampaignPlanWilayahInclude");
			}
		}

		if( ($errors = sqlsrv_errors() ) != null) {
	        foreach( $errors as $error ) {
	        	$ERR_CODE = $error["code"];
	            $ERR_MSG.= "message: ".$error[ 'message']." ";
	        }
	        $this->db->trans_rollback();
	        return array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
	    } else {
	    	$this->db->trans_commit();
	    	//die($this->db->last_query());
	    }
		

		$str = "Select CampaignID, Wilayah, Kd_Lokasi, isnull(DraftAdd,0) as DraftAdd, isnull(DraftRemove,0) as DraftRemove 
				From TblCampaignPlanWilayahInclude Where CampaignID='".$data["kode_plan"]."' and Wilayah='".$data["wilayah"]."'";
		$check = $this->db->query($str);
		if ($check->num_rows()>0) {
			if ($data["is_checked"]==0) {
				if($check->row()->DraftRemove==1) {
			        return array("result"=>"SUCCESS", "campaignId"=>$data["kode_plan"], "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());
			    } else {
    		        return array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>"SIMPAN GAGAL", "errCode"=>0, "lastQuery"=>$this->db->last_query());
			    }
			} else {
				if ($check->row()->DraftRemove==0) {
			        return array("result"=>"SUCCESS", "campaignId"=>$data["kode_plan"], "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());					
				} else {
    		        return array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>"SIMPAN GAGAL", "errCode"=>0, "lastQuery"=>$this->db->last_query());					
				}
			}
		} else {
			if ($data["is_checked"]==0) {
		        return array("result"=>"SUCCESS", "campaignId"=>$data["kode_plan"], "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());					
			} else {
		        return array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>"SIMPAN GAGAL", "errCode"=>0, "lastQuery"=>$this->db->last_query());
		    }
		}		
	}

	
	function checkDraft($trxId)
	{
		$str = "Select * From TblCampaignPlanHD Where CampaignID='".$trxId."' and IsDraft=1";
		$res = $this->db->query($str);
		if ($res->num_rows()>0) 
			return 1;
		else
			return 0;
	}

	function processDraft($data)
	{
		//Hapus Dahulu Data Tersimpan Yang Diflag Akan Dihapus
		$str = "DELETE FROM TblCampaignPlanHD WHERE CampaignID = '".$data["txtKodeCampaign"]."' and IsCancelled=2";
		$process = $this->db->query($str);
		$str = "UPDATE TblCampaignPlanHD SET IsDraft=0 WHERE CampaignID='".$data["txtKodeCampaign"]."'";
		$process = $this->db->query($str);
	}

	function ProcessDraftWilayah($data) 
	{
		$str = "DELETE FROM TblCampaignPlanWilayahInclude WHERE CampaignID = '".$data["txtKodeCampaign"]."' and DraftRemove=1";
		$process = $this->db->query($str);

		$str = "UPDATE TblCampaignPlanWilayahInclude SET DraftAdd=0 WHERE CampaignID = '".$data["txtKodeCampaign"]."' and DraftAdd=1";
		$process = $this->db->query($str);		
	}

	function cancelPlan($data)
	{
		$lanjut = true;
		$result = array();
		$ERR_MSG = "";
		$ERR_CODE = 0;
		$this->db->trans_begin();
		
		$checkItem = $this->db->query("Select top 1 * From TblCampaignPlanHD Where CampaignID='".$data["kode_plan"]."'");
		if ($checkItem->num_rows()>0) {
		//$HD = $this->GetTransaksiDetail($data["kode_plan"]);
		//if (count($HD)>0) {
			//if ($HD->CampaignStatus!="CANCELLED") {
			if ($checkItem->row()->IsCancelled==0) {
				$str = "UPDATE TblCampaignPlanHD 
						SET IsCancelled=1, CancelledDate=GETDATE(),
							CancelledBy='".$_SESSION["logged_in"]["username"]."', 
							CancelNote='".$data["alasan"]."' 
						WHERE CampaignID='".$data["kode_plan"]."'";
				$cancel = $this->db->query($str);
				if( ($errors = sqlsrv_errors() ) != null) {
			        foreach( $errors as $error ) {
			        	$ERR_CODE = $error["code"];
			            $ERR_MSG.= "message: ".$error[ 'message']." ";
			        }
			        $this->db->trans_rollback();
			        return array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
			    } else {
			    	$this->db->trans_commit();
			        return array("result"=>"SUCCESS", "campaignId"=>$data["kode_plan"], "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());
			    }
			} else {
		        return array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>"Campaign Plan #".$data["kode_plan"]." Sudah Berstatus Batal", "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
			}
		} else {
	        return array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>"Campaign Plan #".$data["kode_plan"]." Tidak Ditemukan", "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
		}
	}

	function CheckDraftDT($trxID)
	{
		$str = "Select * From TblCampaignPlanPreviousCampaigns Where CampaignID='".$trxID."' and IsDraft=1";
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			// echo("Previous Campaign Found");
			$str = "SELECT SUM(x) as x FROM (
						Select count(Wilayah) as x
						From TblCampaignPlanWilayahInclude
						Where CampaignID='".$trxID."' and upper(Wilayah) not in 
							(Select upper(Kota) From TblCampaignPlanDTBreakdowns Where CampaignID='".$trxID."')
						Union all
						Select count(ProductID) as x 
						From TblCampaignPlanHD 
						Where CampaignID='".$trxID."' and upper(ProductID) not in 
							(Select upper(ProductID) From TblCampaignPlanDTBreakdowns Where CampaignID='".$trxID."')
					) GAB";
			$res2 = $this->db->query($str);

			//Jika x = 0, artinya semua Wilayah dari Tabel Wilayah Include sudah terdaftar dalam Tabel Breakdowns
			//				dan semua Product di Tabel CampaignPlanHD sudah masuk dalam Tabel Breakdowns
			
			if ($res2->num_rows()>0) {
				$QtyCount = $res2->row();
				if ($QtyCount->x==0) {
					//Jika Tidak Ada Wilayah Baru
					$str = "SELECT ProductID, ROUND(SUM(Persentase),0) as TotalPersentase 
							FROM TblCampaignPlanPersentasePerWilayah 
							WHERE CampaignID='".$trxID."'
							GROUP BY ProductID";
					$res3 = $this->db->query($str);
					if ($res3->num_rows()==0) {
						// echo ("Persentase Per Wilayah Belum Tersimpan");
						return false;
					} else {
						$isHundred = true;
						foreach($res3->result() as $rs) {
							if ($rs->TotalPersentase<>100) {
								$isHundred=false;
							}
						}
						if ($isHundred==false) {
							// echo("Total Persentase ".$res3->row()->TotalPersentase." Belum 100");
							return false;
						} else {
							// echo("Semua Wilayah dan Barang ada dalam TblCampaignPlanDTBreakdowns");
							return true;
						}
					}
				} else {
					// echo("Ada Wilayah atau Kode Barang yang Belum ada Dalam TblCampaignPlanDTBreakdowns");
					return false;
				}
			} else {
				$str = "SELECT ProductID, ROUND(SUM(Persentase),0) as TotalPersentase 
						FROM TblCampaignPlanPersentasePerWilayah 
						WHERE CampaignID='".$trxID."'
						GROUP BY ProductID";
				$res3 = $this->db->query($str);
				if ($res3->num_rows()==0) {
					// echo ("Persentase Per Wilayah Belum Tersimpan");
					return false;
				} else {
					$isHundred = true;
					foreach($res3->result() as $rs) {
						if ($rs->TotalPersentase<>100) {
							$isHundred=false;
						}
					}
					if ($isHundred==false) {
						//echo("Total Persentase Belum 100");
						return false;
					} else {
						//echo("Semua Wilayah dan Barang ada dalam TblCampaignPlanDTBreakdowns");
						return true;
					}
				}
			}
		} else {
			// echo("Previous Campaign Not Found");
			return false;
		}
	}

	function GetPreviousCampaigns($trxID)
	{
		$str = "Select a.*, isnull(a.TotalHariPlan, b.JumlahHari) as JumlahHari
				From TblCampaignPlanPreviousCampaigns a inner join TblCampaignPlanHD b on a.CampaignID=b.CampaignID and a.ProductID=b.ProductID
				Where a.CampaignID='".$trxID."'
				Order By a.ProductID, a.[id]";
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetBreakdowns($trxID, $prevTrxID)
	{
		$str = "SELECT * FROM TblCampaignPlanDTBreakdowns 
				WHERE CampaignID='".$trxID."' 
					and PreviousCampaignId=".$prevTrxID." ORDER BY Kota";
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetSelectedPreviousCampaigns($trxID, $productID)
	{
		$str = "Select * From TblCampaignPlanPreviousCampaigns 
				Where CampaignID='".$trxID."' and ProductID='".$productID."' and IsSelected=1
				Order By ProductID, [id]";
		//die($str);
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->row();
		} else {
			return null;
		}
	}

	function SavePreviousCampaigns($trxID, $data)
	{
		/*
		$array_barang = array("Kd_Brg"=>$c->Kd_Brg, "KdBrg"=>$KdBrg,  
		"Jns_Trx"=>$c->Jns_Trx, "Nm_Trx"=>$c->Nm_Trx, "Flag"=>$c->Flag, 
		"Total_Hari"=>$c->TotalHari, "Total_Jual"=>$TotalJual, "Avg"=>$Avg, "Total_Avg"=>$TotalAvg,
		"Total_Hari_Plan"=>$hd->JumlahHari, "Breakdown_Per_Wilayah"=>$breakdowns);
		*/
		$dtId = 0;

		$this->db->trans_begin();
		$str = "Select * From TblCampaignPlanPreviousCampaigns Where CampaignID='".$trxID."' 
				and ProductID = '".$data["Kd_Brg"]."' and JnsTrx='".$data["Jns_Trx"]."' ";
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			$dtId = $res->row()->id;
			$this->db->where("id", $res->row()->id);
			$this->db->set("CampaignID", $trxID);
			$this->db->set("ProductID", $data["Kd_Brg"]);
			$this->db->set("JnsTrx", (($data["Jns_Trx"]==null)?"":$data["Jns_Trx"]));
			$this->db->set("NmTrx", (($data["Nm_Trx"]==null)?"":$data["Nm_Trx"]));
			$this->db->set("TotalHari", (($data["Total_Hari"]==null)? 0 : $data["Total_Hari"]));
			$this->db->set("TotalJual", (($data["Total_Jual"]==null)? 0 : $data["Total_Jual"]));
			$this->db->set("AvgJual", (($data["Avg"]==null)? 0 : $data["Avg"]));
			$this->db->set("TotalHariPlan", (($data["Total_Hari_Plan"]==null)? 0 : $data["Total_Hari_Plan"]));
			$this->db->set("TotalQty", (($data["Total_Avg"]==null)? 0 : $data["Total_Avg"]));
			$this->db->set("Flag", $data["Flag"]);
			$this->db->set("CreatedBy", $_SESSION["logged_in"]["username"]);
			$this->db->set("CreatedDate", date("Y-m-d H:i:s"));
			$this->db->update("TblCampaignPlanPreviousCampaigns");
			if( ($errors = sqlsrv_errors() ) != null) {
	        	$this->db->trans_rollback();
	    		return 0;
			}
		} else {
			$this->db->set("CampaignID", $trxID);
			$this->db->set("ProductID", $data["Kd_Brg"]);
			$this->db->set("JnsTrx", (($data["Jns_Trx"]==null)?"":$data["Jns_Trx"]));
			$this->db->set("NmTrx", (($data["Nm_Trx"]==null)?"":$data["Nm_Trx"]));
			$this->db->set("TotalHari", (($data["Total_Hari"]==null)? 0 : $data["Total_Hari"]));
			$this->db->set("TotalJual", (($data["Total_Jual"]==null)? 0 : $data["Total_Jual"]));
			$this->db->set("AvgJual", (($data["Avg"]==null)? 0 : $data["Avg"]));
			$this->db->set("TotalHariPlan", (($data["Total_Hari_Plan"]==null)? 0 : $data["Total_Hari_Plan"]));
			$this->db->set("TotalQty", (($data["Total_Avg"]==null)? 0 : $data["Total_Avg"]));
			$this->db->set("Flag", $data["Flag"]);
			$this->db->set("CreatedBy", $_SESSION["logged_in"]["username"]);
			$this->db->set("CreatedDate", date("Y-m-d H:i:s"));
			$this->db->insert("TblCampaignPlanPreviousCampaigns");
			if( ($errors = sqlsrv_errors() ) != null) {
	        	$this->db->trans_rollback();
	    		return 0;
			}
			$str = "Select * From TblCampaignPlanPreviousCampaigns Where CampaignID='".$trxID."' 
					and ProductID = '".$data["Kd_Brg"]."' and JnsTrx='".$data["Jns_Trx"]."' ";
			$res2 = $this->db->query($str);
			if ($res2->num_rows()>0) {
				$dtId = $res2->row()->id;
			} else {
				return 0;
			}
		}
		
		$this->db->where("CampaignID", $trxID);
		$this->db->where("ProductID", $data["Kd_Brg"]);
		$this->db->where("Jns_Trx", (($data["Jns_Trx"]==null)?"":$data["Jns_Trx"]));
		$this->db->delete("TblCampaignPlanDTBreakdowns");

      	$breakdowns = $data["Breakdown_Per_Wilayah"];
      	/*
			array_push($breakdowns, array("Wilayah"=>$s->Wilayah, "Kd_Lokasi"=>$s->Kd_Lokasi, "AvgJual"=>$s->AvgJual, "TotalAvgAll"=>$s->TotalAvgAll,
			"Persentase"=>$s->Persentase,"TotalQtyCampaign"=>$TotalAvg, "TotalQty"=>$qty_per_wilayah));
		*/

      	for ($i=0; $i<count($breakdowns); $i++) {
			$this->db->set("CampaignID", $trxID);
			$this->db->set("ProductID", $data["Kd_Brg"]);
			$this->db->set("Kota", $breakdowns[$i]["Wilayah"]);
			$this->db->set("PreviousCampaignId", $dtId);
			$this->db->set("Jns_Trx", $data["Jns_Trx"]);
			$this->db->set("Nm_Trx", $data["Nm_Trx"]);
			$this->db->set("Avg_Jual", $breakdowns[$i]["AvgJual"]);
			$this->db->set("Total_Avg_Jual", $breakdowns[$i]["TotalAvgAll"]);
			$this->db->set("Persentase_Jual", $breakdowns[$i]["Persentase"]);
			$this->db->set("Total_Qty_Campaign", $breakdowns[$i]["TotalQtyCampaign"]);
			$this->db->set("Total_Qty", $breakdowns[$i]["TotalQty"]);
			$this->db->set("Kd_Lokasi", $breakdowns[$i]["Kd_Lokasi"]);
			$this->db->set("CreatedBy", $_SESSION["logged_in"]["username"]);
			$this->db->set("CreatedDate", date("Y-m-d H:i:s"));
			$this->db->insert("TblCampaignPlanDTBreakdowns");			
		}
		$this->db->trans_commit();

		return $dtId;
	}

	/*[[{"Wilayah":"CIREBON ","Kd_Lokasi":"CRB","Kd_Brg":"MCM-306","TotalJual":"0","AvgJual":".000000","TotalAvgAll":0,"Persentase":20},
	*/
	function SaveAverageSales($trxID, $data)
	{
		/*
			[CampaignID],[Wilayah],[ProductID]
			,[TotalJual],[AvgJual],[TotalAvgAll],[Persentase]
			,[CreatedBy],[CreatedDate]
		*/
		$dtId = 0;

		$this->db->trans_begin();
		$this->db->where("CampaignID", $trxID);
		$this->db->delete("TblCampaignPlanPersentasePerWilayah");

		foreach($data as $dt) {
			foreach($dt as $d) {
				$this->db->set("CampaignID", $trxID);
				$this->db->set("Wilayah", $d->Wilayah);
				$this->db->set("ProductID", $d->Kd_Brg);
				$this->db->set("TotalJual", (float)$d->TotalJual);
				$this->db->set("AvgJual", (float)$d->AvgJual);
				$this->db->set("TotalAvgAll", (float)$d->TotalAvgAll);
				$this->db->set("Persentase", (float)$d->Persentase);
				$this->db->set("CreatedBy", $_SESSION["logged_in"]["username"]);
				$this->db->set("CreatedDate", date("Y-m-d H:i:s"));
				$this->db->insert("TblCampaignPlanPersentasePerWilayah");
			}
		}
		$this->db->trans_commit();

		return true;
	}

	function ProcessDraftDT($trxID)
	{
		$this->db->trans_begin();
		$str = "SELECT * FROM TblCampaignPlanPreviousCampaigns Where IsDraft=1 and IsSelected=1 and CampaignID='".$trxID."'";
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			foreach($res->result() as $r) {
				$str = "UPDATE TblCampaignPlanPreviousCampaigns SET IsSelected=0, IsDraft=0, UpdatedBy='".$_SESSION["logged_in"]["username"]."', UpdatedDate=GETDATE() 
						WHERE CampaignID='".$trxID."' and ProductID='".$r->ProductID."' and [id]<>".$r->id;
				$upd = $this->db->query($str);
			}
			$str = "UPDATE TblCampaignPlanPreviousCampaigns SET IsDraft=0, UpdatedBy='".$_SESSION["logged_in"]["username"]."', UpdatedDate=GETDATE() 
					WHERE CampaignID='".$trxID."' and ProductID='".$r->ProductID."' and [id]=".$r->id;
			$upd = $this->db->query($str);		
		}

		$HD = json_decode($this->GetTransaksiDetail($trxID));

		foreach($HD as $d) {
			$KodeBarang = $d->ProductID;
			$str = "SELECT * FROM TblCampaignPlanPreviousCampaigns WHERE CampaignID='".$trxID."' and ProductID='".$KodeBarang."' and IsSelected=1";
			$res2 = $this->db->query($str);
			if ($res2->num_rows()>0) {
				$PreviousID = $res2->row()->id;
				$str = "UPDATE TblCampaignPlanDTBreakdowns SET IsSelected=0, IsDraft=0 WHERE CampaignID='".$trxID."' and ProductID='".$KodeBarang."' and PreviousCampaignID<>".$PreviousID;
				$res3 = $this->db->query($str);

				$str = "UPDATE TblCampaignPlanDTBreakdowns SET IsDraft=0 WHERE CampaignID='".$trxID."' and ProductID='".$KodeBarang."' and PreviousCampaignID=".$PreviousID;
				$res4 = $this->db->query($str);
			}
		}

		$this->db->trans_commit();
	}

	function saveDraftDT($data)
	{
		$lanjut = true;
		$result = array();
		$ERR_MSG = "";

		$this->db->trans_begin();
		//Update IsSelected=0 untuk Barang Yang akan Disave Draft
		$this->db->where("CampaignID", $data["kode_plan"]);
		$this->db->where("ProductID", $data["kode_barang"]);
		$this->db->set("IsSelected", 0);
		$this->db->set("UpdatedBy", $_SESSION["logged_in"]["username"]);
		$this->db->set("UpdatedDate", date("Y-m-d H:i:s"));
		$this->db->update("TblCampaignPlanPreviousCampaigns");

		$this->db->where("CampaignID", $data["kode_plan"]);
		$this->db->where("ProductID", $data["kode_barang"]);
		$this->db->set("IsSelected", 0);
		$this->db->set("UpdatedBy", $_SESSION["logged_in"]["username"]);
		$this->db->set("UpdatedDate", date("Y-m-d H:i:s"));
		$this->db->update("TblCampaignPlanDTBreakdowns");

		//Update IsSelected=1 untuk ID Campaign Yang Dikirim dari View
		$this->db->where("CampaignID", $data["kode_plan"]);
		$this->db->where("id", $data["previous_campaign_id"]);
		$this->db->set("AvgJual", round($data["avg_jual"],0));
		$this->db->set("TotalQty", round($data["total_avg_jual"],0));
		$this->db->set("IsDraft", 1);
		$this->db->set("IsSelected", 1);
		$this->db->set("UpdatedBy", $_SESSION["logged_in"]["username"]);
		$this->db->set("UpdatedDate", date("Y-m-d H:i:s"));
		$this->db->update("TblCampaignPlanPreviousCampaigns");
		if( ($errors = sqlsrv_errors() ) != null) {
	        foreach( $errors as $error ) {
	        	$ERR_CODE = $error["code"];
	            $ERR_MSG.= "message: ".$error[ 'message']." ";
	        }
	        $this->db->trans_rollback();
	        return array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
	    } else {
	    	$this->db->trans_commit();
	    	//die($this->db->last_query());
	    }
		
		$str = "Select * From TblCampaignPlanPreviousCampaigns Where CampaignID='".$data["kode_plan"]."' and [id]='".$data["previous_campaign_id"]."' and IsSelected=1";
		$check = $this->db->query($str);
		if ($check->num_rows()>0) {
	        return array("result"=>"SUCCESS", "campaignId"=>$data["kode_plan"], "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());
		} else {
	        return array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>"SIMPAN GAGAL", "errCode"=>0, "lastQuery"=>$this->db->last_query());
		}		
	}

	function saveDraftBreakdown($data)
	{
		$lanjut = true;
		$result = array();
		$ERR_MSG = "";

		$this->db->trans_begin();

		$this->db->where("CampaignID", $data["kode_plan"]);
		$this->db->where("ProductID", $data["kode_barang"]);
		$this->db->where("Kota", $data["wilayah"]);
		$this->db->where("PreviousCampaignId", $data["previous_campaign_id"]);
		$this->db->set("Kd_Lokasi", $data["kode_lokasi"]);
		$this->db->set("Total_Qty_Campaign", $data["total_qty_campaign"]);
		$this->db->set("Total_Qty", $data["total_qty"]);
		$this->db->set("IsDraft", 1);
		$this->db->set("IsSelected", 1);
		$this->db->set("UpdatedBy", $_SESSION["logged_in"]["username"]);
		$this->db->set("UpdatedDate", date("Y-m-d H:i:s"));
		$this->db->update("TblCampaignPlanDTBreakdowns");
		if( ($errors = sqlsrv_errors() ) != null) {
	        foreach( $errors as $error ) {
	        	$ERR_CODE = $error["code"];
	            $ERR_MSG.= "message: ".$error[ 'message']." ";
	        }
	        $this->db->trans_rollback();
	        return array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>$ERR_MSG, "errCode"=>$ERR_CODE, "lastQuery"=>$this->db->last_query());
	    } else {
	    	$this->db->trans_commit();
	    	//die($this->db->last_query());
	    }
		

		$str = "Select * From TblCampaignPlanDTBreakdowns Where CampaignID='".$data["kode_plan"]."' and ProductID='".$data["kode_barang"]."' and Kota='".$data["wilayah"]."' and IsDraft=1";
		$check = $this->db->query($str);
		if ($check->num_rows()>0) {
	        return array("result"=>"SUCCESS", "campaignId"=>$data["kode_plan"], "errMsg"=>"", "errCode"=>0, "lastQuery"=>$this->db->last_query());
		} else {
	        return array("result"=>"FAILED", "campaignId"=>$data["kode_plan"], "errMsg"=>"SIMPAN GAGAL", "errCode"=>0, "lastQuery"=>$this->db->last_query());
		}		
	}

	function EmailRequestSent($trxID, $BM, $success=true)
	{
		$this->db->where("CampaignID", $trxID);
		$this->db->set("IsApproved", 0);
		$this->db->set("ApprovedBy", $BM->userid);
		$this->db->set("ApprovedByName", $BM->nm_slsman);
		$this->db->set("ApprovedByEmail", $BM->email_address);
		$this->db->set("IsEmailed", (($success==true)?1:2));
		$this->db->set("EmailedBy", $_SESSION["logged_in"]["username"]);
		$this->db->set("EmailedDate", date("Y-m-d H:i:s"));
		$this->db->update("TblCampaignPlanHD");
	}
}
