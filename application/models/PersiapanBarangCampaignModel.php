<?php
class PersiapanBarangCampaignModel extends CI_Model
{
	function getAutoNumber($division)
	{
		$period = date('Ym');
		if ($division == 'MIYAKOKR') {
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
		$str = "Select CampaignID, CampaignName, ProductID, Division, CampaignStart, CampaignEnd, 
					CASE WHEN isnull(JumlahHari,0)=0 THEN datediff(day, CampaignStart, CampaignEnd) ELSE isnull(JumlahHari,0) END as JumlahHari,
					CreatedBy, CreatedDate, UpdatedBy, UpdatedDate, 
					isnull(IsApproved,0) as IsApproved, ApprovedBy, ApprovedDate, 
					isnull(IsDraft,0) as IsDraft, IsEmailed, EmailedBy, EmailedDate,
					isnull(IsCancelled,0) as IsCancelled, CancelledBy, CancelledDate				
				from TblCampaignPlanHD where CampaignID = '" . $CampaignID . "'";

		$res = $this->db->query($str);
		return json_encode($res->result());
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
		$str = "select * from TblCampaignPlanwilayahInclude where CampaignID = '" . $CampaignID . "'";
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
			select isApproved, convert(varchar, MAX(ApprovedDate), 13) as ApprovedDate, ApprovedBy
			from  TblCampaignPlanHD
			where CampaignID = '".$id."'
			Group By isApproved, ApprovedBy
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

	function Approved($CampaignID, $data) {
		$this->db->trans_start();

		$this->db->where('CampaignID',$CampaignID);
		$this->db->set('isApproved',$data['isApproved']);
		$this->db->set('ApprovedBy',$data['ApprovedBy']);
		$this->db->set('ApprovedDate',date('Y-m-d H:i:s'));
		$this->db->update('TblCampaignPlanHD');
		
		// debug start aliat
		// $this->db->trans_complete();
		// echo 'Berhasil approve (tes), Silahkan tutup halaman ini!';
		//debug end

		// $strCabang = "
		// select top 4 * from MsDatabase_test where branchid in ('BLI','CRB')";

		$strLokasi = "select Wilayah, Kd_Lokasi from TblCampaignPlanWilayahInclude WHERE campaignID= '".$CampaignID."'";

		$x = $this->db->query($strLokasi);
		$result = $x->result();
		
		// print_r($result);
		// die;
		
		$lokasi='';
		$wilayah='';
		foreach ($result as $z) {

			$strCabang = "";

			if($z->Kd_Lokasi=='DMI'){
				$strCabang = "select * from MsDatabase where branchid = '".$z->Kd_Lokasi."' and NamaDb= 'JAKARTA'";
			} else if($z->Kd_Lokasi=='BOG'){
				$strCabang = "select * from MsDatabase where branchid = 'JKT' and NamaDb= 'BOGOR'";
			} else if($z->Kd_Lokasi=='KRW'){
				$strCabang = "select * from MsDatabase where branchid = 'JKT' and NamaDb= 'KARAWANG'";
			} else if($z->Kd_Lokasi=='SRY'){
				$strCabang = "select * from MsDatabase where branchid = 'SBY'";
			} else{
				$strCabang = "select * from MsDatabase where branchid = '".$z->Kd_Lokasi."'";
			}

			// $strCabang = "
			// 	select * from MsDatabase_test where branchid = '".$lokasi."' and NamaDb= '". $wilayah ."'";
	
			$res = $this->db->query($strCabang);
			$cabangs = $res->result();
			
			// print_r($res->result());
			// die();
	
			foreach ($cabangs as $cabang) {
				// print_r($cabang->Database);
				$str2 = "
				select
				a.CampaignID,
				CampaignName,
				Kd_GroupGudang,
				a.ProductID AS Kd_Brg,
				b.Division as Divisi,
				CONVERT(varchar,CampaignStart,101) as CampaignStart,
				CONVERT(varchar,CampaignEnd,101) as CampaignEnd,
				b.JumlahHari,
				tot_avg as Qty,
				1 as TotalPeriods,
				CreatedBy,
				CONVERT(varchar,CreatedDate,101) +' '+CONVERT(varchar,CreatedDate,108) as CreatedDate,
				ApprovedBy,
				CONVERT(varchar,ApprovedDate,101) +' '+CONVERT(varchar,ApprovedDate,108) as ApprovedDate
				from TblCampaignPlanDT a left join TblCampaignPlanHD b on a.CampaignID = b.CampaignID   and a.ProductID = b.ProductID

				where a.CampaignID = '".$CampaignID."' and kota ='".$z->Wilayah."'";
				
				// echo $str2;
				// echo '<br/>';
		
				$res2 = $this->db->query($str2);
				
				// print_r($res2->result());
				// die;
				
				$campaignPlan['value'] = $res2->result();
				$campaignPlan['kota'] = $cabang->NamaDb;
				$campaignPlan['Server'] = $cabang->Server;
				$campaignPlan['Database'] = $cabang->Database;
				$campaignPlan['Uid'] = SQL_UID;
				$campaignPlan['Pwd'] = SQL_PWD;
				$campaignPlan['api'] = 'APITES';
	
	
				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_URL => $cabang->AlamatWebService. "bktAPI/PerencanaanBarangCampaign/insertCampaignPlan",
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 300,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => http_build_query($campaignPlan),
				));
	
				$response = curl_exec($curl);
				$err = curl_error($curl);
				
				curl_close($curl);
	
				// print_r($response);
				// print_r($err);
				// die;
	
				if ($err) {
					for ($i = 0; $i < count($campaignPlan['value']); $i++) {
						$dataHeader = array(
							'CampaignID' => $campaignPlan['value'][$i]->CampaignID,
							'CampaignName' => $campaignPlan['value'][$i]->CampaignName,
							'Kd_GroupGudang' => $campaignPlan['value'][$i]->Kd_GroupGudang,
							'Kd_Brg' => $campaignPlan['value'][$i]->Kd_Brg,
							'Divisi' => $campaignPlan['value'][$i]->Divisi,
							'CampaignStart' => $campaignPlan['value'][$i]->CampaignStart,
							'CampaignEnd' => $campaignPlan['value'][$i]->CampaignEnd,
							'JumlahHari' => $campaignPlan['value'][$i]->JumlahHari,
							'Qty' => $campaignPlan['value'][$i]->Qty,
							'TotalPeriods' => $campaignPlan['value'][$i]->TotalPeriods,
							'CreatedBy' => $campaignPlan['value'][$i]->CreatedBy,
							'CreatedDate' => $campaignPlan['value'][$i]->CreatedDate,
							'ApprovedBy' => $campaignPlan['value'][$i]->ApprovedBy,
							'ApprovedDate' => $campaignPlan['value'][$i]->ApprovedDate,
							'Kota' => $cabang->NamaDb
						);
						$this->db->insert('TblCampaignPlanHD_Gagal', $dataHeader);
					}
				}
	
				if(json_decode($response)=='Gagal'){
					for ($i = 0; $i < count($campaignPlan['value']); $i++) {
						$dataHeader = array(
							'CampaignID' => $campaignPlan['value'][$i]->CampaignID,
							'CampaignName' => $campaignPlan['value'][$i]->CampaignName,
							'Kd_GroupGudang' => $campaignPlan['value'][$i]->Kd_GroupGudang,
							'Kd_Brg' => $campaignPlan['value'][$i]->Kd_Brg,
							'Divisi' => $campaignPlan['value'][$i]->Divisi,
							'CampaignStart' => $campaignPlan['value'][$i]->CampaignStart,
							'CampaignEnd' => $campaignPlan['value'][$i]->CampaignEnd,
							'JumlahHari' => $campaignPlan['value'][$i]->JumlahHari,
							'Qty' => $campaignPlan['value'][$i]->Qty,
							'TotalPeriods' => $campaignPlan['value'][$i]->TotalPeriods,
							'CreatedBy' => $campaignPlan['value'][$i]->CreatedBy,
							'CreatedDate' => $campaignPlan['value'][$i]->CreatedDate,
							'ApprovedBy' => $campaignPlan['value'][$i]->ApprovedBy,
							'ApprovedDate' => $campaignPlan['value'][$i]->ApprovedDate,
							'Kota' => $cabang->NamaDb
						);
						$this->db->insert('TblCampaignPlanHD_Gagal', $dataHeader);
					}
				}

			}

		}
		
		$this->db->trans_complete();
		echo '<center>Berhasil approve, Silahkan tutup halaman ini!</center>';
	}

	function Rejected($CampaignID, $data) {
		$this->db->trans_start();

		$this->db->where('CampaignID',$CampaignID);
		$this->db->set('isApproved',$data['isApproved']);
		$this->db->set('ApprovedBy',$data['ApprovedBy']);
		$this->db->set('ApprovedDate',date('Y-m-d H:i:s'));
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

	function getList()
	{
		$str = "SELECT DISTINCT CampaignID, CampaignName, Division, CampaignStart, CampaignEnd, 
					CASE WHEN isnull(JumlahHari,0)=0 THEN datediff(day, CampaignStart, CampaignEnd) ELSE isnull(JumlahHari,0) END as JumlahHari,
					CreatedBy, CreatedDate, UpdatedBy, UpdatedDate, 
					isnull(IsApproved,0) as IsApproved, ApprovedBy, ApprovedDate, 
					isnull(IsDraft,0) as IsDraft, IsEmailed, EmailedBy, EmailedDate,
					isnull(IsCancelled,0) as IsCancelled, CancelledBy, CancelledDate,
					(CASE WHEN IsCancelled=1 THEN 'CANCELLED' WHEN IsDraft=1 THEN 'DRAFT' 
						  WHEN IsEmailed=0 THEN 'SAVED<br>APPROVAL UNSENT' 
						  WHEN IsApproved=2 THEN 'REJECTED'
						  ELSE 'APPROVED' END) as CampaignStatus
				From TblCampaignPlanHD
				ORDER BY  CampaignID DESC";

		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}


	function InsertBhakti($CampaignID, $data) {
		$this->db->trans_start();
		
		// debug start aliat
		// $this->db->trans_complete();
		// echo 'Berhasil approve (tes), Silahkan tutup halaman ini!';
		//debug end

		// $strCabang = "
		// select top 4 * from MsDatabase_test where branchid in ('BLI','CRB')";

		$strLokasi = "select Wilayah, Kd_Lokasi from TblCampaignPlanWilayahInclude WHERE campaignID= '".$CampaignID."'";
		$x = $this->db->query($strLokasi);
		$result = $x->result();
		
		//print_r($result);
		//die;
		
		$lokasi='';
		$wilayah='';
		foreach ($result as $z) {

			$strCabang = "";

			if($z->Kd_Lokasi=='DMI'){
				$strCabang = "select * from MsDatabase where branchid = 'JKT' and NamaDb= 'JAKARTA'";
			} else if($z->Kd_Lokasi=='BOG'){
				$strCabang = "select * from MsDatabase where branchid = 'JKT' and NamaDb= 'BOGOR'";
			} else if($z->Kd_Lokasi=='KRW'){
				$strCabang = "select * from MsDatabase where branchid = 'JKT' and NamaDb= 'KARAWANG'";
			} else if($z->Kd_Lokasi=='SRY'){
				$strCabang = "select * from MsDatabase where branchid = 'SBY'";
			} else{
				$strCabang = "select * from MsDatabase where branchid = '".$z->Kd_Lokasi."'";
			}

			//die($strCabang);
			$res = $this->db->query($strCabang);
			$cabangs = $res->result();
			
			//print_r($cabangs);
			//die();
	
			foreach ($cabangs as $cabang) {
				// print_r($cabang->Database);
				$str2 = "
				select
				a.CampaignID,
				CampaignName,
				Kd_GroupGudang,
				a.ProductID AS Kd_Brg,
				b.Division as Divisi,
				CONVERT(varchar,CampaignStart,101) as CampaignStart,
				CONVERT(varchar,CampaignEnd,101) as CampaignEnd,
				isnull(b.JumlahHari,0) as JumlahHari,
				tot_avg as Qty,
				1 as TotalPeriods,
				CreatedBy,
				CONVERT(varchar,CreatedDate,101) +' '+CONVERT(varchar,CreatedDate,108) as CreatedDate,
				ApprovedBy,
				CONVERT(varchar,ApprovedDate,101) +' '+CONVERT(varchar,ApprovedDate,108) as ApprovedDate
				from TblCampaignPlanDT a left join TblCampaignPlanHD b on a.CampaignID = b.CampaignID   and a.ProductID = b.ProductID
				where a.CampaignID = '".$CampaignID."' and kota ='".$z->Wilayah."'";
				
				// echo $str2;
				// echo '<br/>';
		
				$res2 = $this->db->query($str2);
				
				//print_r($res2->result());
				//die;
				
				$campaignPlan['value'] = $res2->result();
				$campaignPlan['kota'] = $cabang->NamaDb;
				$campaignPlan['Server'] = $cabang->Server;
				$campaignPlan['Database'] = $cabang->Database;
				$campaignPlan['Uid'] = SQL_UID;
				$campaignPlan['Pwd'] = SQL_PWD;
				$campaignPlan['api'] = 'APITES';
	
	
				$curl = curl_init();
				curl_setopt_array($curl, array(
					//CURLOPT_URL => $cabang->AlamatWebService. "bktAPI/PerencanaanBarangCampaign/insertCampaignPlan",
					CURLOPT_URL => "http://localhost/bktAPI/PerencanaanBarangCampaign/insertCampaignPlan",
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 300,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => http_build_query($campaignPlan),
				));
	
				$response = curl_exec($curl);
				$err = curl_error($curl);
				
				curl_close($curl);
	
				//print_r($response);
				//print_r($err);
				// die;
	
				if ($err) {
					for ($i = 0; $i < count($campaignPlan['value']); $i++) {
						$str = "Delete From TblCampaignPlanHD_Gagal Where CampaignID='".$campaignPlan['value'][$i]->CampaignID."' 
									and Kota='".$cabang->NamaDb."' and Kd_GroupGudang='".$campaignPlan['value'][$i]->Kd_GroupGudang."'
									and Kd_Brg='".$campaignPlan['value'][$i]->Kd_Brg."' ";
						$this->db->query($str);

						$dataHeader = array(
							'CampaignID' => $campaignPlan['value'][$i]->CampaignID,
							'CampaignName' => $campaignPlan['value'][$i]->CampaignName,
							'Kd_GroupGudang' => $campaignPlan['value'][$i]->Kd_GroupGudang,
							'Kd_Brg' => $campaignPlan['value'][$i]->Kd_Brg,
							'Divisi' => $campaignPlan['value'][$i]->Divisi,
							'CampaignStart' => $campaignPlan['value'][$i]->CampaignStart,
							'CampaignEnd' => $campaignPlan['value'][$i]->CampaignEnd,
							'JumlahHari' => $campaignPlan['value'][$i]->JumlahHari,
							'Qty' => $campaignPlan['value'][$i]->Qty,
							'TotalPeriods' => $campaignPlan['value'][$i]->TotalPeriods,
							'CreatedBy' => $campaignPlan['value'][$i]->CreatedBy,
							'CreatedDate' => $campaignPlan['value'][$i]->CreatedDate,
							'ApprovedBy' => $campaignPlan['value'][$i]->ApprovedBy,
							'ApprovedDate' => $campaignPlan['value'][$i]->ApprovedDate,
							'Kota' => $cabang->NamaDb
						);
						$this->db->insert('TblCampaignPlanHD_Gagal', $dataHeader);
					}
				} else if(json_decode($response)=='Gagal'){
					for ($i = 0; $i < count($campaignPlan['value']); $i++) {
						$str = "Delete From TblCampaignPlanHD_Gagal Where CampaignID='".$campaignPlan['value'][$i]->CampaignID."' 
									and Kota='".$cabang->NamaDb."' and Kd_GroupGudang='".$campaignPlan['value'][$i]->Kd_GroupGudang."'
									and Kd_Brg='".$campaignPlan['value'][$i]->Kd_Brg."' ";
						$this->db->query($str);

						$dataHeader = array(
							'CampaignID' => $campaignPlan['value'][$i]->CampaignID,
							'CampaignName' => $campaignPlan['value'][$i]->CampaignName,
							'Kd_GroupGudang' => $campaignPlan['value'][$i]->Kd_GroupGudang,
							'Kd_Brg' => $campaignPlan['value'][$i]->Kd_Brg,
							'Divisi' => $campaignPlan['value'][$i]->Divisi,
							'CampaignStart' => $campaignPlan['value'][$i]->CampaignStart,
							'CampaignEnd' => $campaignPlan['value'][$i]->CampaignEnd,
							'JumlahHari' => $campaignPlan['value'][$i]->JumlahHari,
							'Qty' => $campaignPlan['value'][$i]->Qty,
							'TotalPeriods' => $campaignPlan['value'][$i]->TotalPeriods,
							'CreatedBy' => $campaignPlan['value'][$i]->CreatedBy,
							'CreatedDate' => $campaignPlan['value'][$i]->CreatedDate,
							'ApprovedBy' => $campaignPlan['value'][$i]->ApprovedBy,
							'ApprovedDate' => $campaignPlan['value'][$i]->ApprovedDate,
							'Kota' => $cabang->NamaDb
						);
						$this->db->insert('TblCampaignPlanHD_Gagal', $dataHeader);
					}
				} else {
					for ($i = 0; $i < count($campaignPlan['value']); $i++) {
						$str = "Delete From TblCampaignPlanHD_Gagal Where CampaignID='".$campaignPlan['value'][$i]->CampaignID."' 
									and Kota='".$cabang->NamaDb."' and Kd_GroupGudang='".$campaignPlan['value'][$i]->Kd_GroupGudang."'
									and Kd_Brg='".$campaignPlan['value'][$i]->Kd_Brg."' ";
						$this->db->query($str);
					}
				}
			}

		}
		
		$this->db->trans_complete();
		echo '<center>Berhasil approve, Silahkan tutup halaman ini!</center>';
	}
}
