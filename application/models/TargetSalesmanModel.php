<?php
class TargetSalesmanModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$CI = &get_instance();
	}

	// get AlamatWebService utk baca DB BHAKTI cabang yang diinginkan
	function get($wilayah){
		$res = $this->db->query("Select AlamatWebService, Server, [Database]
								From MsDatabase Where NamaDb='".$wilayah."'");
		if ($res->num_rows()>0)
			return $res->row();
		else
			return null;
	}

	// apabila http request error (server cabang yg dituju sedang mati), 
	// maka approval/reject disimpan di table lokal HRDMC dahulu
	function insertRequestApproval($kode_target,$user,$wilayah,$action,$ApprovedTime){
        $hasil=$this->db->query("INSERT INTO TblApproval (ApprovalType, RequestNo, 
							     ApprovedBy, ApprovedDate, ApprovalStatus, ApprovalLevel, 
								 AddInfo1, AddInfo1Value, AddInfo2, AddInfo2Value,
								 BhaktiFLag) 
								 VALUES ('TARGET SALESMAN','".$kode_target."',
								 '".$user."','".$ApprovedTime."', 0,1,
								 'WILAYAH','".$wilayah."', 'ACTION','".$action."',
								 'UNPROCESSED')");
        return $hasil;		
	}

    function get_list_pendingan(){
    	$hasil=array();

        $query=$this->db->query("SELECT RequestNo, ApprovedBy, AddInfo1Value, AddInfo2Value, ApprovedDate
		                        FROM TblApproval WITH(NOLOCK)
								WHERE ApprovalType = 'TARGET SALESMAN' AND BhaktiFLag ='UNPROCESSED'");
		if ($query->num_rows()>0)	{							
			foreach ($query->result_array() as $row) {

				$hasil[] = ['kode_target' => $row['RequestNo'],
					'user' => $row['ApprovedBy'],
					'wilayah' => $row['AddInfo1Value'],	
					'action' => $row['AddInfo2Value'],		
					'ApprovedTime' => $row['ApprovedDate']];	
			
			}
		} else {

			$hasil[] = ['kode_target' => '',
			'user' => '',
			'wilayah' => '',	
			'action' => '',		
			'ApprovedTime' => ''];			

		}

		return $hasil;
		//return $query->result();
	}		

	function updateRequestApproval($kode_target,$user,$wilayah,$action,$ApprovedTime){
		$BhaktiProcessDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));

		if ($action=='APPROVE'){							
			$hasil=$this->db->query("UPDATE TblApproval 
									SET	ApprovalStatus = 1,
									BhaktiFlag = 'PROCESSED',
									BhaktiProcessDate = '".$BhaktiProcessDate."'
									WHERE RequestNo = '".$kode_target."'");
		} else {
			$hasil=$this->db->query("UPDATE TblApproval 
									SET	ApprovalStatus = 2	
									BhaktiFlag = 'PROCESSED',
									BhaktiProcessDate = '".$BhaktiProcessDate."'
									WHERE RequestNo = '".$kode_target."'");			
		}

        return $hasil;	
    }
	
	function cari_pendingan($kode_target)
	{
        $qry="SELECT * FROM TblApproval WITH(NOLOCK)
			  WHERE ApprovalType = 'TARGET SALESMAN' AND BhaktiFLag ='UNPROCESSED'
			  AND RequestNo = '".$kode_target."'";
		$SQL = $this->db->query($qry);
		if ($SQL->num_rows()>0)
			return true;
		else
			return false;			
	}	

	function insertRequestApprovalNew($params){
		//ApprovalStatus UNPROCESSED / APPROVED / REJECTED
        $hasil=$this->db->query("INSERT INTO TblApproval (ApprovalType, RequestNo, 
								 RequestBy, RequestByName, RequestByEmail, RequestDate, 
							     ApprovedBy, ApprovedByName, ApprovedByEmail, ApprovedDate, 
								 ApprovalStatus, ApprovalLevel, 
								 AddInfo1, AddInfo1Value, AddInfo2, AddInfo2Value,
								 AddInfo3, AddInfo3Value,
								 BhaktiFLag) 
								 VALUES ('TARGET SALESMAN','".$params["norequest"]."',
								 '".$params["RequestBy"]."','".$params["RequestByName"]."',
								 '".$params["RequestByEmail"]."','".$params["RequestDate"]."',								 
								 '".$params["ApprovedBy"]."','".$params["ApprovedByName"]."',
								 '".$params["ApprovedByEmail"]."','".$params["ApprovedTime"]."', 
								 'UNPROCESSED',1,
								 'WILAYAH','".$params["wilayah"]."', 'ACTION','".$params["action"]."',
								 'KODE_TARGET','".$params["kode_target"]."',
								 'UNPROCESSED')");
        return $hasil;		
	}

    function get_list_pendinganNew(){
		
        $query=$this->db->query("SELECT norequest=RequestNo,
								RequestBy, RequestByName, RequestByEmail, RequestDate,
								ApprovedBy, ApprovedByName, ApprovedByEmail, ApprovedTime=ApprovedDate,
								wilayah=AddInfo1Value, [action]=AddInfo2Value, kode_target=AddInfo3Value
		                        FROM TblApproval WITH(NOLOCK)
								WHERE ApprovalType = 'TARGET SALESMAN' AND BhaktiFLag ='UNPROCESSED'");
		if ($query->num_rows()>0)	{							
			foreach ($query->result_array() as $row) {

				$hasil[] = ['norequest' => $row['norequest'],
					'kode_target' => $row['kode_target'],
					'RequestBy' => $row['RequestBy'],
					'RequestByName' => $row['RequestByName'],
					'RequestByEmail' => $row['RequestByEmail'],
					'RequestDate' => $row['RequestDate'],
					'ApprovedBy' => $row['ApprovedBy'],
					'ApprovedByName' => $row['ApprovedByName'],
					'ApprovedByEmail' => $row['ApprovedByEmail'],
					'ApprovedTime' => $row['ApprovedTime'],
					'wilayah' => $row['wilayah'],	
					'action' => $row['action']];	
			
			}
		} else {

			$hasil[] = ['norequest' => '',
			'kode_target' => '',
			'RequestBy' => '',
			'RequestByName' => '',
			'RequestByEmail' => '',
			'RequestDate' => '',
			'ApprovedBy' => '',
			'ApprovedByName' => '',
			'ApprovedByEmail' => '',
			'ApprovedTime' => '',
			'wilayah' => '',	
			'action' => ''];
		}

		return $hasil;
		//return $query->result();
	}	
	
	function updateRequestApprovalNew($params){
		$BhaktiProcessDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));
		//ApprovalStatus UNPROCESSED / APPROVED / REJECTED
		if ($params["action"]=='APPROVE'){							
			$hasil=$this->db->query("UPDATE TblApproval 
									SET	ApprovalStatus = 'APPROVED',
									BhaktiFlag = 'PROCESSED',
									BhaktiProcessDate = '".$BhaktiProcessDate."'
									WHERE REPLACE(REPLACE(RequestNo,'-',''),'_','') = '".str_replace(['-','_'],'',$params["norequest"])."'");
		} else {
			$hasil=$this->db->query("UPDATE TblApproval 
									SET	ApprovalStatus = 'REJECTED',
									BhaktiFlag = 'PROCESSED',
									BhaktiProcessDate = '".$BhaktiProcessDate."'
									WHERE REPLACE(REPLACE(RequestNo,'-',''),'_','') = '".str_replace(['-','_'],'',$params["norequest"])."'");			
		}

        return $hasil;	
    }		

	function cari_pendinganNew($params)
	{
        $qry="SELECT * FROM TblApproval WITH(NOLOCK)
			  WHERE ApprovalType in ('TARGET SALESMAN','TARGET SPG') AND BhaktiFLag ='UNPROCESSED'
			  AND REPLACE(REPLACE(RequestNo,'-',''),'_','') = '".str_replace(['-','_'],'',$params["norequest"])."'";			  
		$SQL = $this->db->query($qry);
		if ($SQL->num_rows()>0)
			return true;
		else
			return false;			
	}	
	function editTblApproval($where,$data){
		$this->db->where($where);
		$this->db->update('TblApproval',$data);
		// $result = $this->db->affect_rows() > 0 ? true : false;
		$result = true;
		return $result;
	}


	//Substitute bktAPI From Here
	function GetLokasibktAPI($configDB)
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);
		$qry = "select bktAPI=WebAPI_URL
				from TblConfig";	
		$res = $this->bkt->query($qry);
		if ($res->num_rows()>0)
			return $res->row();
		else
			return null;			
	}	

	function GetTblConfig($configDB)
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);
		$qry = "select KodeLokasi=TblConfig.kd_lokasi, Kota=RTRIM(TblCabang.nm_Cab)
				from TblConfig left join TblCabang on TblConfig.kd_lokasi = TblCabang.Kd_Wil";	
		$res = $this->bkt->query($qry);
		if ($res->num_rows()>0)
			return $res->row();
		else
			return null;			
	}	

	
	function GetLevelUserInput($params, $configDB){
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);
		$qry = "SELECT RTRIM(A.Nm_Slsman) AS NamaUser, RTRIM(B.Nama_Level) AS LevelUser, A.Level_Salesman, RTRIM(A.Wilayah) AS Wilayah
								 FROM TblMsSalesman A LEFT JOIN Mst_LevelSalesman B
								 ON A.Level_Salesman=B.Level_Salesman WHERE A.Kd_Slsman='".$params["userid"]."'";
		$res = $this->bkt->query($qry);								 
		if ($res->num_rows()>0)
			return $res->row();
		else
			return null;		
	}	
	
	function GetAtasanSales_ByEntryTime($params, $configDB) 
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);
		$Level = 61;
		$LevelCabang = 20;		

		$hasil=array();

		if (($params["mode"]=='CANCELREQ') || ($params["mode"]=='HAPUS')) {
			$qry = "SELECT 	Kode_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Kd_Slsman 
													ELSE D.Kd_Slsman END 
												ELSE D.Kd_Slsman END
											ELSE C.Kd_Slsman END
										ELSE C.Kd_Slsman END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Kd_Slsman ELSE '' END
									END,
							Nama_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Nm_Slsman 
													ELSE D.Nm_Slsman END 
												ELSE D.Nm_Slsman END
											ELSE C.Nm_Slsman END
										ELSE C.Nm_Slsman END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Nm_Slsman ELSE '' END
									END,
							Email_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Email 
													ELSE D.Email END 
												ELSE D.Email END
											ELSE C.Email END
										ELSE C.Email END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Email ELSE '' END
									END,
							Kode_Level_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Level_Salesman 
													ELSE D.Level_Salesman END 
												ELSE D.Level_Salesman END
											ELSE C.Level_Salesman END
										ELSE C.Level_Salesman END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Level_Salesman ELSE '' END
									END,
							Level_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														EL.Nama_Level 
													ELSE DL.Nama_Level END 
												ELSE DL.Nama_Level END
											ELSE CL.Nama_Level END
										ELSE CL.Nama_Level END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN BL.Nama_Level ELSE '' END
									END																																			
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON B.Kd_Supervisor = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON C.Kd_Supervisor = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON D.Kd_Supervisor = E.Kd_Slsman
					LEFT JOIN Mst_LevelSalesman BL WITH(NOLOCK) ON B.Level_Salesman = BL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman CL WITH(NOLOCK) ON C.Level_Salesman = CL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman DL WITH(NOLOCK) ON D.Level_Salesman = DL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman EL WITH(NOLOCK) ON E.Level_Salesman = EL.Level_Salesman
					WHERE A.NoRequest IN (SELECT NoRequest FROM Mst_TargetSlsman_Log WITH(NOLOCK)
					WHERE DeletedBy ='".$params["userid"]."'
					AND CONVERT(varchar(max),DeletedDate,120) ='".$params["tanggal"]."')
					AND ISNULL(B.Wilayah,'')='JAKARTA'
					GROUP BY 
					B.Kd_Slsman, B.Nm_Slsman, B.Email, B.Level_Salesman, B.Kd_Supervisor,
					C.Kd_Slsman, C.Nm_Slsman, C.Email, C.Level_Salesman, C.Kd_Supervisor,
					D.Kd_Slsman, D.Nm_Slsman, D.Email, D.Level_Salesman, D.Kd_Supervisor,
					E.Kd_Slsman, E.Nm_Slsman, E.Email, E.Level_Salesman,
					BL.Nama_Level, CL.Nama_Level, DL.Nama_Level, EL.Nama_Level
					UNION ALL
					SELECT	Kode_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Kd_Slsman 
													ELSE D.Kd_Slsman END 
												ELSE D.Kd_Slsman END
											ELSE C.Kd_Slsman END
										ELSE C.Kd_Slsman END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 'GM' ELSE '' END
									END,
							Nama_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Nm_Slsman 
													ELSE D.Nm_Slsman END 
												ELSE D.Nm_Slsman END
											ELSE C.Nm_Slsman END
										ELSE C.Nm_Slsman END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 'GM' ELSE '' END
									END,
							Email_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Email 
													ELSE D.Email END 
												ELSE D.Email END
											ELSE C.Email END
										ELSE C.Email END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 'GM' ELSE '' END
									END,
							Kode_Level_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Level_Salesman 
													ELSE D.Level_Salesman END 
												ELSE D.Level_Salesman END
											ELSE C.Level_Salesman END
										ELSE C.Level_Salesman END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 1 ELSE '' END
									END,
							Level_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														EL.Nama_Level 
													ELSE DL.Nama_Level END 
												ELSE DL.Nama_Level END
											ELSE CL.Nama_Level END
										ELSE CL.Nama_Level END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 'GM' ELSE '' END
									END																																			
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON B.Kd_Supervisor = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON C.Kd_Supervisor = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON D.Kd_Supervisor = E.Kd_Slsman
					LEFT JOIN Mst_LevelSalesman BL WITH(NOLOCK) ON B.Level_Salesman = BL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman CL WITH(NOLOCK) ON C.Level_Salesman = CL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman DL WITH(NOLOCK) ON D.Level_Salesman = DL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman EL WITH(NOLOCK) ON E.Level_Salesman = EL.Level_Salesman
					WHERE A.NoRequest IN (SELECT NoRequest FROM Mst_TargetSlsman_Log WITH(NOLOCK)
					WHERE DeletedBy ='".$params["userid"]."'
					AND CONVERT(varchar(max),DeletedDate,120) ='".$params["tanggal"]."')
					AND ISNULL(B.Wilayah,'')<>'JAKARTA'
					GROUP BY 
					B.Kd_Slsman, B.Nm_Slsman, B.Email, B.Level_Salesman, B.Kd_Supervisor,
					C.Kd_Slsman, C.Nm_Slsman, C.Email, C.Level_Salesman, C.Kd_Supervisor,
					D.Kd_Slsman, D.Nm_Slsman, D.Email, D.Level_Salesman, D.Kd_Supervisor,
					E.Kd_Slsman, E.Nm_Slsman, E.Email, E.Level_Salesman,
					BL.Nama_Level, CL.Nama_Level, DL.Nama_Level, EL.Nama_Level";			
		}
		else {
			$qry = "SELECT 	Kode_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Kd_Slsman 
													ELSE D.Kd_Slsman END 
												ELSE D.Kd_Slsman END
											ELSE C.Kd_Slsman END
										ELSE C.Kd_Slsman END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Kd_Slsman ELSE '' END
									END,
							Nama_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Nm_Slsman 
													ELSE D.Nm_Slsman END 
												ELSE D.Nm_Slsman END
											ELSE C.Nm_Slsman END
										ELSE C.Nm_Slsman END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Nm_Slsman ELSE '' END
									END,
							Email_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Email 
													ELSE D.Email END 
												ELSE D.Email END
											ELSE C.Email END
										ELSE C.Email END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Email ELSE '' END
									END,
							Kode_Level_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Level_Salesman 
													ELSE D.Level_Salesman END 
												ELSE D.Level_Salesman END
											ELSE C.Level_Salesman END
										ELSE C.Level_Salesman END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Level_Salesman ELSE '' END
									END,
							Level_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														EL.Nama_Level 
													ELSE DL.Nama_Level END 
												ELSE DL.Nama_Level END
											ELSE CL.Nama_Level END
										ELSE CL.Nama_Level END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN BL.Nama_Level ELSE '' END
									END																																			
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON B.Kd_Supervisor = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON C.Kd_Supervisor = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON D.Kd_Supervisor = E.Kd_Slsman
					LEFT JOIN Mst_LevelSalesman BL WITH(NOLOCK) ON B.Level_Salesman = BL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman CL WITH(NOLOCK) ON C.Level_Salesman = CL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman DL WITH(NOLOCK) ON D.Level_Salesman = DL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman EL WITH(NOLOCK) ON E.Level_Salesman = EL.Level_Salesman
					WHERE A.NoRequest IN (SELECT NoRequest FROM Mst_TargetSlsman_Log WITH(NOLOCK)
					WHERE User_Name ='".$params["userid"]."'
					AND CONVERT(varchar(max),entry_time,120) ='".$params["tanggal"]."')
					AND ISNULL(B.Wilayah,'')='JAKARTA'
					GROUP BY 
					B.Kd_Slsman, B.Nm_Slsman, B.Email, B.Level_Salesman, B.Kd_Supervisor,
					C.Kd_Slsman, C.Nm_Slsman, C.Email, C.Level_Salesman, C.Kd_Supervisor,
					D.Kd_Slsman, D.Nm_Slsman, D.Email, D.Level_Salesman, D.Kd_Supervisor,
					E.Kd_Slsman, E.Nm_Slsman, E.Email, E.Level_Salesman,
					BL.Nama_Level, CL.Nama_Level, DL.Nama_Level, EL.Nama_Level
					UNION ALL
					SELECT 	Kode_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Kd_Slsman 
													ELSE D.Kd_Slsman END 
												ELSE D.Kd_Slsman END
											ELSE C.Kd_Slsman END
										ELSE C.Kd_Slsman END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 'GM' ELSE '' END
									END,
							Nama_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Nm_Slsman 
													ELSE D.Nm_Slsman END 
												ELSE D.Nm_Slsman END
											ELSE C.Nm_Slsman END
										ELSE C.Nm_Slsman END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 'GM' ELSE '' END
									END,
							Email_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Email 
													ELSE D.Email END 
												ELSE D.Email END
											ELSE C.Email END
										ELSE C.Email END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 'GM' ELSE '' END
									END,
							Kode_Level_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Level_Salesman 
													ELSE D.Level_Salesman END 
												ELSE D.Level_Salesman END
											ELSE C.Level_Salesman END
										ELSE C.Level_Salesman END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 1 ELSE '' END
									END,
							Level_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														EL.Nama_Level 
													ELSE DL.Nama_Level END 
												ELSE DL.Nama_Level END
											ELSE CL.Nama_Level END
										ELSE CL.Nama_Level END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 'GM' ELSE '' END
									END																																			
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON B.Kd_Supervisor = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON C.Kd_Supervisor = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON D.Kd_Supervisor = E.Kd_Slsman
					LEFT JOIN Mst_LevelSalesman BL WITH(NOLOCK) ON B.Level_Salesman = BL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman CL WITH(NOLOCK) ON C.Level_Salesman = CL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman DL WITH(NOLOCK) ON D.Level_Salesman = DL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman EL WITH(NOLOCK) ON E.Level_Salesman = EL.Level_Salesman
					WHERE A.NoRequest IN (SELECT NoRequest FROM Mst_TargetSlsman_Log WITH(NOLOCK)
					WHERE User_Name ='".$params["userid"]."'
					AND CONVERT(varchar(max),entry_time,120) ='".$params["tanggal"]."')
					AND ISNULL(B.Wilayah,'')<>'JAKARTA'
					GROUP BY 
					B.Kd_Slsman, B.Nm_Slsman, B.Email, B.Level_Salesman, B.Kd_Supervisor,
					C.Kd_Slsman, C.Nm_Slsman, C.Email, C.Level_Salesman, C.Kd_Supervisor,
					D.Kd_Slsman, D.Nm_Slsman, D.Email, D.Level_Salesman, D.Kd_Supervisor,
					E.Kd_Slsman, E.Nm_Slsman, E.Email, E.Level_Salesman,
					BL.Nama_Level, CL.Nama_Level, DL.Nama_Level, EL.Nama_Level";
		}

		$res = $this->bkt->query($qry);
		//if ($res->num_rows()>0)
			//return $res->row();	
		//else
		//	return null;		
		foreach ($res->result_array() as $row)
		{					
			$hasil[] = ['Kode_Atasan' => TRIM($row['Kode_Atasan']),
						'Nama_Atasan' => TRIM($row['Nama_Atasan']),
						'Email_Atasan' => TRIM($row['Email_Atasan']),
						'Kode_Level_Atasan' => TRIM($row['Kode_Level_Atasan']),
						'Level_Atasan' => TRIM($row['Level_Atasan'])];						
		}	
		return $hasil;				
	}		

	function GetAtasanSalesByKodeSupervisorList($configDB){
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);
		$Level = 61;
		$LevelCabang = 20;

		$hasil=array();

		$qry = "SELECT Wilayah=B.Wilayah,Kode_Salesman=B.Kd_Slsman, Nama_Salesman=B.Nm_Slsman,
				Kode_Level_Salesman=B.Level_Salesman, Level_Salesman=BL.Nama_Level,
								Jumlah_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$Level."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$Level."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																3 
															ELSE 2 END 
														ELSE 2 END
													ELSE 1 END
												ELSE 1 END
											ELSE
												CASE WHEN B.Level_Salesman > '".$Level."' THEN 0 ELSE 0 END
											END,
								   Kode_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$Level."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$Level."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																E.Kd_Slsman 
															ELSE D.Kd_Slsman END 
														ELSE D.Kd_Slsman END
													ELSE C.Kd_Slsman END
												ELSE C.Kd_Slsman END
											ELSE
												CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Kd_Slsman ELSE '' END
											END,
									Nama_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$Level."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$Level."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																E.Nm_Slsman 
															ELSE D.Nm_Slsman END 
														ELSE D.Nm_Slsman END
													ELSE C.Nm_Slsman END
												ELSE C.Nm_Slsman END
											ELSE
												CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Nm_Slsman ELSE '' END
											END,
									Email_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$Level."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$Level."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																E.Email 
															ELSE D.Email END 
														ELSE D.Email END
													ELSE C.Email END
												ELSE C.Email END
											ELSE
												CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Email ELSE '' END
											END,
									Kode_Level_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$Level."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$Level."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																E.Level_Salesman 
															ELSE D.Level_Salesman END 
														ELSE D.Level_Salesman END
													ELSE C.Level_Salesman END
												ELSE C.Level_Salesman END
											ELSE
												CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Level_Salesman ELSE '' END
											END,
									Level_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$Level."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$Level."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																EL.Nama_Level 
															ELSE DL.Nama_Level END 
														ELSE DL.Nama_Level END
													ELSE CL.Nama_Level END
												ELSE CL.Nama_Level END
											ELSE
												CASE WHEN B.Level_Salesman > '".$Level."' THEN BL.Nama_Level ELSE '' END
											END									
									FROM TblMsSalesman B WITH(NOLOCK)
									LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON B.Kd_Supervisor = C.Kd_Slsman
									LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON C.Kd_Supervisor = D.Kd_Slsman
									LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON D.Kd_Supervisor = E.Kd_Slsman
									LEFT JOIN Mst_LevelSalesman BL WITH(NOLOCK) ON B.Level_Salesman = BL.Level_Salesman
									LEFT JOIN Mst_LevelSalesman CL WITH(NOLOCK) ON C.Level_Salesman = CL.Level_Salesman
									LEFT JOIN Mst_LevelSalesman DL WITH(NOLOCK) ON D.Level_Salesman = DL.Level_Salesman
									LEFT JOIN Mst_LevelSalesman EL WITH(NOLOCK) ON E.Level_Salesman = EL.Level_Salesman	
									WHERE B.AKTIF = 'Y'
									AND ISNULL(B.Wilayah,'')='JAKARTA'
									GROUP BY B.Wilayah,
									B.Kd_Slsman, B.Nm_Slsman, B.Email, B.Level_Salesman, B.Kd_Supervisor,
									C.Kd_Slsman, C.Nm_Slsman, C.Email, C.Level_Salesman, C.Kd_Supervisor,
									D.Kd_Slsman, D.Nm_Slsman, D.Email, D.Level_Salesman, D.Kd_Supervisor,
									E.Kd_Slsman, E.Nm_Slsman, E.Email, E.Level_Salesman,
									BL.Nama_Level, CL.Nama_Level, DL.Nama_Level, EL.Nama_Level
									UNION ALL
									SELECT Wilayah=B.Wilayah,Kode_Salesman=B.Kd_Slsman, Nama_Salesman=B.Nm_Slsman,
									Kode_Level_Salesman=B.Level_Salesman, Level_Salesman=BL.Nama_Level,
								Jumlah_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																3 
															ELSE 2 END 
														ELSE 2 END
													ELSE 1 END
												ELSE 1 END
											ELSE
												CASE WHEN B.Level_Salesman > '".$LevelCabang."' THEN 0 ELSE 0 END
											END,
								   Kode_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																E.Kd_Slsman 
															ELSE D.Kd_Slsman END 
														ELSE D.Kd_Slsman END
													ELSE C.Kd_Slsman END
												ELSE C.Kd_Slsman END
											ELSE
												CASE WHEN B.Level_Salesman > '".$LevelCabang."' THEN 'GM' ELSE '' END
											END,
									Nama_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																E.Nm_Slsman 
															ELSE D.Nm_Slsman END 
														ELSE D.Nm_Slsman END
													ELSE C.Nm_Slsman END
												ELSE C.Nm_Slsman END
											ELSE
												CASE WHEN B.Level_Salesman > '".$LevelCabang."' THEN 'GM' ELSE '' END
											END,
									Email_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																E.Email 
															ELSE D.Email END 
														ELSE D.Email END
													ELSE C.Email END
												ELSE C.Email END
											ELSE
												CASE WHEN B.Level_Salesman > '".$LevelCabang."' THEN 'GM' ELSE '' END
											END,
									Kode_Level_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																E.Level_Salesman 
															ELSE D.Level_Salesman END 
														ELSE D.Level_Salesman END
													ELSE C.Level_Salesman END
												ELSE C.Level_Salesman END
											ELSE
												CASE WHEN B.Level_Salesman > '".$LevelCabang."' THEN 1 ELSE '' END
											END,
									Level_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																EL.Nama_Level 
															ELSE DL.Nama_Level END 
														ELSE DL.Nama_Level END
													ELSE CL.Nama_Level END
												ELSE CL.Nama_Level END
											ELSE
												CASE WHEN B.Level_Salesman > '".$LevelCabang."' THEN 'GM' ELSE '' END
											END									
									FROM TblMsSalesman B WITH(NOLOCK)
									LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON B.Kd_Supervisor = C.Kd_Slsman
									LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON C.Kd_Supervisor = D.Kd_Slsman
									LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON D.Kd_Supervisor = E.Kd_Slsman
									LEFT JOIN Mst_LevelSalesman BL WITH(NOLOCK) ON B.Level_Salesman = BL.Level_Salesman
									LEFT JOIN Mst_LevelSalesman CL WITH(NOLOCK) ON C.Level_Salesman = CL.Level_Salesman
									LEFT JOIN Mst_LevelSalesman DL WITH(NOLOCK) ON D.Level_Salesman = DL.Level_Salesman
									LEFT JOIN Mst_LevelSalesman EL WITH(NOLOCK) ON E.Level_Salesman = EL.Level_Salesman	
									WHERE B.AKTIF = 'Y'
									AND ISNULL(B.Wilayah,'')<>'JAKARTA'
									GROUP BY B.Wilayah,
									B.Kd_Slsman, B.Nm_Slsman, B.Email, B.Level_Salesman, B.Kd_Supervisor,
									C.Kd_Slsman, C.Nm_Slsman, C.Email, C.Level_Salesman, C.Kd_Supervisor,
									D.Kd_Slsman, D.Nm_Slsman, D.Email, D.Level_Salesman, D.Kd_Supervisor,
									E.Kd_Slsman, E.Nm_Slsman, E.Email, E.Level_Salesman,
									BL.Nama_Level, CL.Nama_Level, DL.Nama_Level, EL.Nama_Level									
									ORDER BY B.Wilayah, Kode_Level_Atasan, Nama_Atasan, Kode_Level_Salesman, Nama_Salesman";

		$res = $this->bkt->query($qry);		
		foreach ($res->result_array() as $row)
		{					
			$hasil[] = ['Wilayah' => $row['Wilayah'],
						'Kode_Salesman' => $row['Kode_Salesman'],
						'Nama_Salesman' => $row['Nama_Salesman'],
						'Kode_Level_Salesman' => $row['Kode_Level_Salesman'],
						'Level_Salesman' => $row['Level_Salesman'],
						'Jumlah_Atasan' => $row['Jumlah_Atasan'],
						'Kode_Atasan' => $row['Kode_Atasan'],
						'Nama_Atasan' => $row['Nama_Atasan'],
						'Email_Atasan' => $row['Email_Atasan'],
						'Kode_Level_Atasan' => $row['Kode_Level_Atasan'],
						'Level_Atasan' => $row['Level_Atasan']];
		}	
		return $hasil;
	}

	function GetAtasanSalesByKodeSupervisor($KodeSalesman, $configDB){
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);
		$Level = 61;
		$LevelCabang = 20;

		$hasil=array();

		$qry = "SELECT Wilayah=B.Wilayah,Kode_Salesman=B.Kd_Slsman, Nama_Salesman=B.Nm_Slsman,
				Kode_Level_Salesman=B.Level_Salesman, Level_Salesman=BL.Nama_Level,
								Jumlah_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$Level."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$Level."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																3 
															ELSE 2 END 
														ELSE 2 END
													ELSE 1 END
												ELSE 1 END
											ELSE
												CASE WHEN B.Level_Salesman > '".$Level."' THEN 0 ELSE 0 END
											END,
								   Kode_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$Level."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$Level."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																E.Kd_Slsman 
															ELSE D.Kd_Slsman END 
														ELSE D.Kd_Slsman END
													ELSE C.Kd_Slsman END
												ELSE C.Kd_Slsman END
											ELSE
												CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Kd_Slsman ELSE '' END
											END,
									Nama_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$Level."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$Level."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																E.Nm_Slsman 
															ELSE D.Nm_Slsman END 
														ELSE D.Nm_Slsman END
													ELSE C.Nm_Slsman END
												ELSE C.Nm_Slsman END
											ELSE
												CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Nm_Slsman ELSE '' END
											END,
									Email_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$Level."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$Level."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																E.Email 
															ELSE D.Email END 
														ELSE D.Email END
													ELSE C.Email END
												ELSE C.Email END
											ELSE
												CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Email ELSE '' END
											END,
									Kode_Level_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$Level."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$Level."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																E.Level_Salesman 
															ELSE D.Level_Salesman END 
														ELSE D.Level_Salesman END
													ELSE C.Level_Salesman END
												ELSE C.Level_Salesman END
											ELSE
												CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Level_Salesman ELSE '' END
											END,
									Level_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$Level."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$Level."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																EL.Nama_Level 
															ELSE DL.Nama_Level END 
														ELSE DL.Nama_Level END
													ELSE CL.Nama_Level END
												ELSE CL.Nama_Level END
											ELSE
												CASE WHEN B.Level_Salesman > '".$Level."' THEN BL.Nama_Level ELSE '' END
											END									
									FROM TblMsSalesman B WITH(NOLOCK)
									LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON B.Kd_Supervisor = C.Kd_Slsman
									LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON C.Kd_Supervisor = D.Kd_Slsman
									LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON D.Kd_Supervisor = E.Kd_Slsman
									LEFT JOIN Mst_LevelSalesman BL WITH(NOLOCK) ON B.Level_Salesman = BL.Level_Salesman
									LEFT JOIN Mst_LevelSalesman CL WITH(NOLOCK) ON C.Level_Salesman = CL.Level_Salesman
									LEFT JOIN Mst_LevelSalesman DL WITH(NOLOCK) ON D.Level_Salesman = DL.Level_Salesman
									LEFT JOIN Mst_LevelSalesman EL WITH(NOLOCK) ON E.Level_Salesman = EL.Level_Salesman	
									WHERE B.AKTIF = 'Y'
									AND ISNULL(B.Wilayah,'')='JAKARTA'
									AND B.Kd_Slsman = '".$KodeSalesman."'
									GROUP BY B.Wilayah,
									B.Kd_Slsman, B.Nm_Slsman, B.Email, B.Level_Salesman, B.Kd_Supervisor,
									C.Kd_Slsman, C.Nm_Slsman, C.Email, C.Level_Salesman, C.Kd_Supervisor,
									D.Kd_Slsman, D.Nm_Slsman, D.Email, D.Level_Salesman, D.Kd_Supervisor,
									E.Kd_Slsman, E.Nm_Slsman, E.Email, E.Level_Salesman,
									BL.Nama_Level, CL.Nama_Level, DL.Nama_Level, EL.Nama_Level
									UNION ALL
									SELECT Wilayah=B.Wilayah,Kode_Salesman=B.Kd_Slsman, Nama_Salesman=B.Nm_Slsman,
									Kode_Level_Salesman=B.Level_Salesman, Level_Salesman=BL.Nama_Level,
								Jumlah_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																3 
															ELSE 2 END 
														ELSE 2 END
													ELSE 1 END
												ELSE 1 END
											ELSE
												CASE WHEN B.Level_Salesman > '".$LevelCabang."' THEN 0 ELSE 0 END
											END,
								   Kode_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																E.Kd_Slsman 
															ELSE D.Kd_Slsman END 
														ELSE D.Kd_Slsman END
													ELSE C.Kd_Slsman END
												ELSE C.Kd_Slsman END
											ELSE
												CASE WHEN B.Level_Salesman > '".$LevelCabang."' THEN B.Kd_Slsman ELSE '' END
											END,
									Nama_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																E.Nm_Slsman 
															ELSE D.Nm_Slsman END 
														ELSE D.Nm_Slsman END
													ELSE C.Nm_Slsman END
												ELSE C.Nm_Slsman END
											ELSE
												CASE WHEN B.Level_Salesman > '".$LevelCabang."' THEN B.Nm_Slsman ELSE '' END
											END,
									Email_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																E.Email 
															ELSE D.Email END 
														ELSE D.Email END
													ELSE C.Email END
												ELSE C.Email END
											ELSE
												CASE WHEN B.Level_Salesman > '".$LevelCabang."' THEN B.Email ELSE '' END
											END,
									Kode_Level_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																E.Level_Salesman 
															ELSE D.Level_Salesman END 
														ELSE D.Level_Salesman END
													ELSE C.Level_Salesman END
												ELSE C.Level_Salesman END
											ELSE
												CASE WHEN B.Level_Salesman > '".$LevelCabang."' THEN B.Level_Salesman ELSE '' END
											END,
									Level_Atasan=
											CASE WHEN B.Kd_Supervisor <> '' THEN
												CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN C.Kd_Supervisor <> '' THEN
														CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
															CASE WHEN D.Kd_Supervisor <> '' THEN
																EL.Nama_Level 
															ELSE DL.Nama_Level END 
														ELSE DL.Nama_Level END
													ELSE CL.Nama_Level END
												ELSE CL.Nama_Level END
											ELSE
												CASE WHEN B.Level_Salesman > '".$LevelCabang."' THEN BL.Nama_Level ELSE '' END
											END									
									FROM TblMsSalesman B WITH(NOLOCK)
									LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON B.Kd_Supervisor = C.Kd_Slsman
									LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON C.Kd_Supervisor = D.Kd_Slsman
									LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON D.Kd_Supervisor = E.Kd_Slsman
									LEFT JOIN Mst_LevelSalesman BL WITH(NOLOCK) ON B.Level_Salesman = BL.Level_Salesman
									LEFT JOIN Mst_LevelSalesman CL WITH(NOLOCK) ON C.Level_Salesman = CL.Level_Salesman
									LEFT JOIN Mst_LevelSalesman DL WITH(NOLOCK) ON D.Level_Salesman = DL.Level_Salesman
									LEFT JOIN Mst_LevelSalesman EL WITH(NOLOCK) ON E.Level_Salesman = EL.Level_Salesman	
									WHERE B.AKTIF = 'Y'
									AND ISNULL(B.Wilayah,'')<>'JAKARTA'
									AND B.Kd_Slsman = '".$KodeSalesman."'
									GROUP BY B.Wilayah,
									B.Kd_Slsman, B.Nm_Slsman, B.Email, B.Level_Salesman, B.Kd_Supervisor,
									C.Kd_Slsman, C.Nm_Slsman, C.Email, C.Level_Salesman, C.Kd_Supervisor,
									D.Kd_Slsman, D.Nm_Slsman, D.Email, D.Level_Salesman, D.Kd_Supervisor,
									E.Kd_Slsman, E.Nm_Slsman, E.Email, E.Level_Salesman,
									BL.Nama_Level, CL.Nama_Level, DL.Nama_Level, EL.Nama_Level									
									ORDER BY B.Wilayah, Kode_Level_Atasan, Nama_Atasan, Kode_Level_Salesman, Nama_Salesman";

		$res = $this->bkt->query($qry);		
		foreach ($res->result_array() as $row)
		{					
			$hasil[] = ['Wilayah' => $row['Wilayah'],
						'Kode_Salesman' => $row['Kode_Salesman'],
						'Nama_Salesman' => $row['Nama_Salesman'],
						'Kode_Level_Salesman' => $row['Kode_Level_Salesman'],
						'Level_Salesman' => $row['Level_Salesman'],
						'Jumlah_Atasan' => $row['Jumlah_Atasan'],
						'Kode_Atasan' => $row['Kode_Atasan'],
						'Nama_Atasan' => $row['Nama_Atasan'],
						'Email_Atasan' => $row['Email_Atasan'],
						'Kode_Level_Atasan' => $row['Kode_Level_Atasan'],
						'Level_Atasan' => $row['Level_Atasan']];
		}	
		return $hasil;
	}	

	function get_list_blm_email($configDB){
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);

		$hasil=array();

		$query=$this->bkt->query("SELECT mode='BARU', kategori='SALESMAN',
		    userid=A.User_Name, tanggal=CONVERT(VARCHAR(MAX),A.Entry_Time,120)
			FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
			LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
			LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
			WHERE A.IsApproved = 0
			AND ISNULL(A.IsRejected,0) = 0 
			AND ISNULL(A.IsDeleted,0) = 0
			AND ISNULL(A.IsEmailed,0) = 0
			AND ISNULL(A.IsEmailedPembatalan,0) = 0
			AND A.User_Name NOT LIKE '%(AUTO)%'
			AND B.Level_Salesman NOT IN ('97','98')
			AND A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman)
			GROUP BY A.User_Name, A.Entry_Time
			UNION ALL
			SELECT mode='UBAH', kategori='SALESMAN',
			userid=A.User_Name, tanggal=CONVERT(VARCHAR(MAX),A.Entry_Time,120)
			FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
			LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
			LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
			WHERE A.IsApproved = 0 
			AND ISNULL(A.IsRejected,0) = 0 
			AND ISNULL(A.IsDeleted,0) = 0
			AND ISNULL(A.IsEmailed,0) = 0
			AND ISNULL(A.IsEmailedPembatalan,0) = 0
			AND A.User_Name NOT LIKE '%(AUTO)%'
			AND B.Level_Salesman NOT IN ('97','98')
			AND A.Kode_Target IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))
			GROUP BY A.User_Name, A.Entry_Time
			UNION ALL
			SELECT mode='CANCELREQ', kategori='SALESMAN',
			userid=A.DeletedBy, tanggal=CONVERT(VARCHAR(MAX),A.DeletedDate,120)
			FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
			LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
			LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
			WHERE A.IsApproved = 0 
			AND ISNULL(A.IsRejected,0) = 0 
			AND ISNULL(A.IsDeleted,0) = 1
			AND ISNULL(A.IsEmailedPembatalan,0) = 0		
			AND A.User_Name NOT LIKE '%(AUTO)%'
			AND B.Level_Salesman NOT IN ('97','98')
			GROUP BY A.DeletedBy, A.DeletedDate
			UNION ALL
			SELECT mode='HAPUS', kategori='SALESMAN',
			userid=A.DeletedBy, tanggal=CONVERT(VARCHAR(MAX),A.DeletedDate,120)
			FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
			LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
			LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
			WHERE A.IsApproved = 1 
			AND ISNULL(A.IsRejected,0) = 0 
			AND ISNULL(A.IsDeleted,0) = 1
			AND ISNULL(A.IsEmailedPembatalan,0) = 0		
			AND A.User_Name NOT LIKE '%(AUTO)%'
			AND B.Level_Salesman NOT IN ('97','98')
			AND A.DeletedBy IN (SELECT Kd_Slsman FROM TblMsSalesman)
			AND CONVERT(VARCHAR(MAX),A.DeletedDate,120) >= '2020-09-01 00:00:01'
			GROUP BY A.DeletedBy, A.DeletedDate	
			UNION ALL
			SELECT mode='REMINDER', kategori='SALESMAN',
			userid=A.User_Name, tanggal=CONVERT(VARCHAR(MAX),A.Entry_Time,120)
			FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
			LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
			LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
			WHERE A.IsApproved = 0
			AND ISNULL(A.IsRejected,0) = 0 
			AND ISNULL(A.IsDeleted,0) = 0
			AND ISNULL(A.IsEmailed,0) = 1
			AND ISNULL(A.IsEmailedPembatalan,0) = 0
			AND A.EmailedDate IS NOT NULL
			AND A.User_Name NOT LIKE '%(AUTO)%'
			AND B.Level_Salesman NOT IN ('97','98')
			AND A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman)
			AND DATEDIFF(d,CONVERT(VARCHAR(MAX),A.EmailedDate,101),CONVERT(VARCHAR(MAX),GETDATE(),101)) >= 7
			GROUP BY A.User_Name, A.Entry_Time					
			UNION ALL
			SELECT mode='BARU', kategori='SPG',
		    userid=A.User_Name, tanggal=CONVERT(VARCHAR(MAX),A.Entry_Time,120)
			FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
			LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
			LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
			WHERE A.IsApproved = 0
			AND ISNULL(A.IsRejected,0) = 0 
			AND ISNULL(A.IsDeleted,0) = 0
			AND ISNULL(A.IsEmailed,0) = 0
			AND ISNULL(A.IsEmailedPembatalan,0) = 0
			AND A.User_Name NOT LIKE '%(AUTO)%'
			AND B.Level_Salesman IN ('97','98')
			AND A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman)
			GROUP BY A.User_Name, A.Entry_Time
			UNION ALL
			SELECT mode='UBAH', kategori='SPG',
			userid=A.User_Name, tanggal=CONVERT(VARCHAR(MAX),A.Entry_Time,120)
			FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
			LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
			LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
			WHERE A.IsApproved = 0 
			AND ISNULL(A.IsRejected,0) = 0 
			AND ISNULL(A.IsDeleted,0) = 0
			AND ISNULL(A.IsEmailed,0) = 0
			AND ISNULL(A.IsEmailedPembatalan,0) = 0
			AND A.User_Name NOT LIKE '%(AUTO)%'
			AND B.Level_Salesman IN ('97','98')
			AND A.Kode_Target IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))
			GROUP BY A.User_Name, A.Entry_Time
			UNION ALL
			SELECT mode='CANCELREQ', kategori='SPG',
			userid=A.DeletedBy, tanggal=CONVERT(VARCHAR(MAX),A.DeletedDate,120)
			FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
			LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
			LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
			WHERE A.IsApproved = 0 
			AND ISNULL(A.IsRejected,0) = 0 
			AND ISNULL(A.IsDeleted,0) = 1
			AND ISNULL(A.IsEmailedPembatalan,0) = 0		
			AND A.User_Name NOT LIKE '%(AUTO)%'
			AND B.Level_Salesman IN ('97','98')
			GROUP BY A.DeletedBy, A.DeletedDate
			UNION ALL
			SELECT mode='HAPUS', kategori='SPG',
			userid=A.DeletedBy, tanggal=CONVERT(VARCHAR(MAX),A.DeletedDate,120)
			FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
			LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
			LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
			WHERE A.IsApproved = 1 
			AND ISNULL(A.IsRejected,0) = 0 
			AND ISNULL(A.IsDeleted,0) = 1
			AND ISNULL(A.IsEmailedPembatalan,0) = 0		
			AND A.User_Name NOT LIKE '%(AUTO)%'
			AND B.Level_Salesman IN ('97','98')
			AND A.DeletedBy IN (SELECT Kd_Slsman FROM TblMsSalesman)
			AND CONVERT(VARCHAR(MAX),A.DeletedDate,120) >= '2020-09-01 00:00:01'
			GROUP BY A.DeletedBy, A.DeletedDate
			UNION ALL
			SELECT mode='REMINDER', kategori='SPG',
			userid=A.User_Name, tanggal=CONVERT(VARCHAR(MAX),A.Entry_Time,120)
			FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
			LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
			LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
			WHERE A.IsApproved = 0
			AND ISNULL(A.IsRejected,0) = 0 
			AND ISNULL(A.IsDeleted,0) = 0
			AND ISNULL(A.IsEmailed,0) = 1
			AND ISNULL(A.IsEmailedPembatalan,0) = 0
			AND A.EmailedDate IS NOT NULL
			AND A.User_Name NOT LIKE '%(AUTO)%'
			AND B.Level_Salesman IN ('97','98')
			AND A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman)
			AND DATEDIFF(d,CONVERT(VARCHAR(MAX),A.EmailedDate,101),CONVERT(VARCHAR(MAX),GETDATE(),101)) >= 7
			GROUP BY A.User_Name, A.Entry_Time");

		if ($query->num_rows()>0)	{							
			foreach ($query->result_array() as $row) {

				$hasil[] = [
				'mode' => $row['mode'],
				'kategori' => $row['kategori'],
				'userid' => $row['userid'],
				'tanggal' => $row['tanggal']];	
			
			}
		} else {

			$hasil[] = [
					  'mode' => '',
					  'kategori' => '',
					  'userid' => '',
					  'tanggal' => ''];				
	
		}

		return $hasil;
		//return $query->result();
	}		

	function CheckTargetAvailableNew($params, $configDB)
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);

		$qry = "SELECT Kode_Target, kd_slsman FROM Mst_TargetSlsman_Log 
				WHERE NoRequest ='".$params["norequest"]."'
				GROUP BY Kode_Target, kd_slsman";		
		$res = $this->bkt->query($qry);
		if ($res->num_rows()>0)
			return true;
		else
			return false;
	}		

	function CheckTargetYangAkanApproveNew($params, $configDB)
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);

		$qry = "SELECT A.Kode_Target, A.kd_slsman, User_Name=B.Nm_Slsman, A.Entry_Time,
				IsApproved=ISNULL(A.IsApproved,0), ApprovedBy=ISNULL(C.Nm_Slsman,''), A.ApprovedDate, 
				IsRejected=ISNULL(A.IsRejected,0), RejectedBy=ISNULL(D.Nm_Slsman,''), A.RejectedDate, 
				IsDeleted=ISNULL(A.IsDeleted,0), DeletedBy=ISNULL(E.Nm_Slsman,''), A.DeletedDate
				FROM Mst_TargetSlsman_Log A WITH(NOLOCK)
				LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.User_Name = B.Kd_Slsman
				LEFT JOIN (SELECT Nm_Slsman=RTRIM(Nm_Slsman), Email=LTRIM(RTRIM(Email)) FROM TblMsSalesman WITH(NOLOCK) WHERE ISNULL(Email,'')<>'') C ON A.ApprovedBy = C.Email
				LEFT JOIN (SELECT Nm_Slsman=RTRIM(Nm_Slsman), Email=LTRIM(RTRIM(Email)) FROM TblMsSalesman WITH(NOLOCK) WHERE ISNULL(Email,'')<>'') D ON A.RejectedBy = D.Email
				LEFT JOIN (SELECT Nm_Slsman=RTRIM(Nm_Slsman), Email=LTRIM(RTRIM(Email)) FROM TblMsSalesman WITH(NOLOCK) WHERE ISNULL(Email,'')<>'') E ON A.DeletedBy = E.Email
				WHERE NoRequest ='".$params["norequest"]."'";		
		$res = $this->bkt->query($qry);
		if ($res->num_rows()>0)
			return $res->row();
		else
			return false;
	}	

	function MoveTargetSalesmanLogToTargetSalesmanNew($params, $configDB)
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);		
		//$this->bkt->trans_start();

		$DateStamp = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));

		if ($params["action"]=='APPROVE'){
			//Jika request diapprove oleh Atasan, maka target lama di Log diupdate IsDeleted = 1
			$qry = "Update Mst_TargetSlsman_Log
					Set IsDeleted = 1, DeletedBy ='".$params["user"]."', DeletedDate = '".$DateStamp."'
					Where NoRequest in (
					Select NoRequest From Mst_TargetSlsman Where Kode_Target in (
					Select Kode_Target From Mst_TargetSlsman_Log
					Where NoRequest = '".$params["norequest"]."'))";
			$SQL1 = $this->bkt->query($qry);	
			
			$qry = "Update Mst_TargetSlsmanDivisi_Log
					Set IsDeleted = 1, DeletedBy ='".$params["user"]."', DeletedDate = '".$DateStamp."'
					Where NoRequest in (
					Select NoRequest From Mst_TargetSlsmanDivisi Where Kode_Target in (
					Select Kode_Target From Mst_TargetSlsmanDivisi_Log
					Where NoRequest = '".$params["norequest"]."'))";
			$SQL2 = $this->bkt->query($qry);			

			$qry = "Update Mst_TargetSlsman_Log 
					Set IsApproved=1,
					ApprovedBy='".$params["user"]."',
					ApprovedDate='".$DateStamp."'
					WHERE NoRequest ='".$params["norequest"]."'";
			$SQL3 = $this->bkt->query($qry);

			$qry = "Update Mst_TargetSlsmandivisi_Log 
					Set IsApproved=1,
					ApprovedBy='".$params["user"]."',
					ApprovedDate='".$DateStamp."'
					WHERE NoRequest ='".$params["norequest"]."'";
			$SQL4 = $this->bkt->query($qry);	
			
			$qry = "DELETE FROM Mst_TargetSlsman
					WHERE Kode_Target = '".$params["kode_target"]."'";			
			$SQL5 = $this->bkt->query($qry);				

			$qry = "INSERT INTO Mst_TargetSlsman (Kd_Slsman, Tgl_Awal, Tgl_Akhir, Total_Target, User_Name, Entry_Time, 
					Training, Kode_Target, AmbilSubsidi, Tgl_AmbilSubsidi, Total_Subsidi, ProcessedBy, ReadKategoriInsentif, 
					ApprovedBy, ApprovedDate, NoRequest, Kd_Lokasi,
					LevelSalesman, WithTargetKPI, NoRequestKPI, ApprovalKPINeeded, TargetKPIStatus)
					SELECT Kd_Slsman, Tgl_Awal, Tgl_Akhir, Total_Target, User_Name, Entry_Time, 
					Training, Kode_Target, AmbilSubsidi, Tgl_AmbilSubsidi, Total_Subsidi, ProcessedBy, ReadKategoriInsentif, 
                    ApprovedBy, ApprovedDate, NoRequest, Kd_Lokasi,
					LevelSalesman, WithTargetKPI, NoRequestKPI, ApprovalKPINeeded, TargetKPIStatus
					FROM Mst_TargetSlsman_Log WHERE NoRequest = '".$params["norequest"]."'";				
			$SQL6 = $this->bkt->query($qry);	

			$qry = "DELETE FROM Mst_TargetSlsmandivisi
					WHERE Kode_Target = '".$params["kode_target"]."'";			
			$SQL7 = $this->bkt->query($qry);			

			$qry = "INSERT INTO Mst_TargetSlsmandivisi (Kode_Target, ID_GroupDivisi, Total_Target, User_Name, Entry_Time, Kategori_Insentif, NoRequest)
				SELECT Kode_Target, ID_GroupDivisi, Total_Target, User_Name, Entry_Time, Kategori_Insentif, NoRequest
				FROM Mst_TargetSlsmandivisi_Log WHERE NoRequest = '".$params["norequest"]."'";	
			$SQL8 = $this->bkt->query($qry);	

		} 
		else 
		{
			$qry = "Update Mst_TargetSlsman_Log 
					Set IsRejected=1,
					RejectedBy='".$params["user"]."',
					RejectedDate='".$DateStamp."'
					WHERE NoRequest ='".$params["norequest"]."'";
			$SQL9 = $this->bkt->query($qry);

			$qry = "Update Mst_TargetSlsmandivisi_Log 
					Set IsRejected=1,
					RejectedBy='".$params["user"]."',
					RejectedDate='".$DateStamp."'
					WHERE NoRequest ='".$params["norequest"]."'";
			$SQL10 = $this->bkt->query($qry);				
		}
		//$this->bkt->trans_complete();
		return true;
	}	

	function GetMasterHD($params, $configDB)
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);

		$hasil=array();

		$query=$this->bkt->query("SELECT Kd_Slsman, Tgl_Awal, Tgl_Akhir, Total_Target, User_Name= User_Name+'(AUTO)', Entry_Time,
								Training, Kode_Target, AmbilSubsidi, Tgl_AmbilSubsidi, Total_Subsidi, 
								ProcessedBy, ReadKategoriInsentif, ApprovedBy, ApprovedDate, NoRequest
								FROM Mst_TargetSlsman_Log
								WHERE NoRequest = '".$params["norequest"]."'");		
											
		foreach ($query->result_array() as $row)
		{					
			$hasil[] = ['Kd_Slsman' => $row['Kd_Slsman'],
					  'Tgl_Awal' => $row['Tgl_Awal'],
					  'Tgl_Akhir' => $row['Tgl_Akhir'],	
					  'Total_Target' => $row['Total_Target'],
					  'User_Name' => $row['User_Name'],
					  'Entry_Time' => $row['Entry_Time'],	
					  'Training' => $row['Training'],	
					  'Kode_Target' => $row['Kode_Target'],				
					  'AmbilSubsidi' => $row['AmbilSubsidi'],
					  'Tgl_AmbilSubsidi' => $row['Tgl_AmbilSubsidi'],
					  'Total_Subsidi' => $row['Total_Subsidi'],
					  'ProcessedBy' => $row['ProcessedBy'],
					  'ReadKategoriInsentif' => $row['ReadKategoriInsentif'],				
					  'ApprovedBy' => $row['ApprovedBy'],
					  'ApprovedDate' => $row['ApprovedDate'],
					  'NoRequest' => $row['NoRequest']];						  
		}
		return $hasil;	
	}	

	function GetMasterHD2($params, $configDB) 
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);		

		$hasil=array();

		$query=$this->bkt->query("SELECT A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
				Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
				left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
				Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
				Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=SUBSTRING(RTRIM(B.Nm_Slsman),1,20), 
				Level_Salesman=RTRIM(G.Nama_Level),
				Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
				User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
				IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),A.ApprovedBy), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
				IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
				IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
				User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman
				FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
				LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
				LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
				LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON RTRIM(A.ApprovedBy) = RTRIM(D.Email)
				LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON RTRIM(A.RejectedBy) = RTRIM(E.Email)
				LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
				LEFT JOIN Mst_LevelSalesman G WITH(NOLOCK) ON B.Level_Salesman = G.Level_Salesman
				WHERE A.NoRequest ='".$params["norequest"]."'");			
				
		foreach ($query->result_array() as $row)
		{						
			$hasil[] = ['kode_target' => $row['kode_target'],
					  'Tgl_Awal' => $row['Tgl_Awal'],
					  'Tgl_Akhir' => $row['Tgl_Akhir'],	
					  'MonthYear' => $row['MonthYear'],
					  'Training' => $row['Training'],
					  'Kd_Slsman' => $row['Kd_Slsman'],
					  'Nm_Slsman' => $row['Nm_Slsman'],	
					  'Level_Salesman' => $row['Level_Salesman'],						
					  'Total_Target' => $row['Total_Target'],	
					  'User_ID' => $row['User_ID'],				
					  'User_Name' => $row['User_Name'],
					  'User_Email' => $row['User_Email'],
					  'Entry_Time' => $row['Entry_Time'],
					  'NoRequest' => $row['NoRequest'],

					  'IsApproved' => $row['IsApproved'],
					  'UserApproved_ID' => $row['UserApproved_ID'],
					  'UserApproved_Name' => $row['UserApproved_Name'],
					  'UserApproved_Email' => $row['UserApproved_Email'],
					  'ApprovedDate' => $row['ApprovedDate'],

					  'IsRejected' => $row['IsRejected'],
					  'UserRejected_ID' => $row['UserRejected_ID'],
					  'UserRejected_Name' => $row['UserRejected_Name'],
					  'UserRejected_Email' => $row['UserRejected_Email'],
					  'RejectedDate' => $row['RejectedDate'],	

					  'IsDeleted' => $row['IsDeleted'],
					  'UserDelete_ID' => $row['UserDelete_ID'],
					  'UserDelete_Name' => $row['UserDelete_Name'],
					  'UserDelete_Email' => $row['UserDelete_Email'],
					  'DeletedDate' => $row['DeletedDate'],
					
					  'User_Level' => $row['User_Level'],
					  'UserApproved_Level' => $row['UserApproved_Level'],
					  'UserRejected_Level' => $row['UserRejected_Level'],
					  'UserDelete_Level' => $row['UserDelete_Level']					  
					];	
		}
		return $hasil;			
	}

	function GetMasterDT($params, $configDB)
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);

		$hasil=array();

		$query=$this->bkt->query("SELECT Kode_Target, ID_GroupDivisi, Total_Target, 
								User_Name= User_Name+'(AUTO)', Entry_Time, Kategori_Insentif, NoRequest
								FROM Mst_TargetSlsmanDivisi_Log WITH(NOLOCK)
								WHERE NoRequest = '".$params["norequest"]."'");		
							
		foreach ($query->result_array() as $row)
		{					
			$hasil[] = ['Kode_Target' => $row['Kode_Target'],
					  'ID_GroupDivisi' => $row['ID_GroupDivisi'],
					  'Total_Target' => $row['Total_Target'],	
					  'User_Name' => $row['User_Name'],
					  'Entry_Time' => $row['Entry_Time'],
					  'Kategori_Insentif' => $row['Kategori_Insentif'],	
					  'NoRequest' => $row['NoRequest']];						  
		}
		return $hasil;	
	}	

	function GetMasterDT2($params, $configDB) 
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);

		$hasil=array();

		$query=$this->bkt->query("SELECT A.ID_GroupDivisi, Divisi=RTRIM(B.nama_subgroupdivisi), 
								A.Kategori_Insentif, Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1) 
								FROM Mst_TargetSlsmanDivisi_Log A WITH(NOLOCK) 
								Left JOIN 
								(SELECT DISTINCT ID_GroupDivisi,ID_SubGroupDivisi,nama_groupdivisi, nama_subgroupdivisi 
								FROM dbo.Mst_TargetSlsman_GroupDivisi WITH(NOLOCK)
								GROUP BY ID_GroupDivisi,ID_SubGroupDivisi,nama_groupdivisi, nama_subgroupdivisi)
								B ON A.ID_groupdivisi = b.ID_GroupDivisi+b.ID_SubGroupDivisi
								WHERE A.NoRequest ='".$params["norequest"]."'
								ORDER BY A.ID_GroupDivisi, B.nama_subgroupdivisi, A.Kategori_Insentif, A.Total_Target");								
							
		foreach ($query->result_array() as $row)
		{					
			$hasil[] = ['ID_GroupDivisi' => $row['ID_GroupDivisi'],
					  'Divisi' => $row['Divisi'],
					  'Kategori_Insentif' => $row['Kategori_Insentif'],	
					  'Total_Target' => $row['Total_Target']];	
		}
		return $hasil;	
	}	

	function Get_TargetSalesman_LastModified($configDB)
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);
		$qry = "SELECT Config_Value AS Tanggal 
				FROM Cof_Penjualan WHERE Config_Name = 'TargetSalesman_LastModified'";	
		$res = $this->bkt->query($qry);
		if ($res->num_rows()>0)
			return $res->row();
		else
			return null;			
	}

	function Get_TargetSPG_LastModified($configDB)
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);
		$qry = "SELECT Config_Value AS Tanggal 
				FROM Cof_Penjualan WHERE Config_Name = 'TargetSPG_LastModified'";	
		$res = $this->bkt->query($qry);
		if ($res->num_rows()>0)
			return $res->row();
		else
			return null;			
	}	

	function get_list_bynorequest2($params, $configDB){
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);
		// die(json_encode($params));
		
		$hasil=array();

		if ($params["parammode"]==""){
			if ($params["mode"]=='CANCELREQ'){	
				$query = $this->bkt->query("select DISTINCT A.User_Name as userid, CONVERT(VARCHAR(MAX),A.DeletedDate,120) as tanggal,
						'CANCELREQ' as [mode],
						case when dbo.F_GetLevelSalesman(A.Kd_Slsman, YEAR(A.Tgl_Awal), MONTH(A.Tgl_Awal)) in (97,98) then 'SPG' else 'SALESMAN' end as [kategori],
						left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
						month(A.Tgl_Awal) as Month, year(A.Tgl_Awal) as Year, 
						B.Wilayah
						From Mst_TargetSlsman_Log A
						INNER JOIN TblMsSalesman B ON A.Kd_Slsman = B.Kd_Slsman
						WHERE A.DeletedBy ='".$params["userid"]."' 
						AND (CONVERT(varchar(MAX),A.DeletedDate,120) ='".$params["tanggal"]."'
						OR CONVERT(varchar(10),A.DeletedDate,120) ='".$params["tanggal"]."')");
			} else {
				$query = $this->bkt->query("select DISTINCT A.User_Name as userid, CONVERT(VARCHAR(MAX),A.Entry_Time,120) as tanggal,
						case when not exists(Select * From Mst_TargetSlsman Where Kode_Target=A.Kode_Target) then 'BARU' else 'UBAH' end as [mode],
						case when dbo.F_GetLevelSalesman(A.Kd_Slsman, YEAR(A.Tgl_Awal), MONTH(A.Tgl_Awal)) in (97,98) then 'SPG' else 'SALESMAN' end as [kategori],
						left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
						month(A.Tgl_Awal) as Month, year(A.Tgl_Awal) as Year,
						B.Wilayah
						From Mst_TargetSlsman_Log A
						INNER JOIN TblMsSalesman B ON A.Kd_Slsman = B.Kd_Slsman
						WHERE A.User_Name ='".$params["userid"]."' 
						AND (CONVERT(varchar(MAX),A.entry_time,120) ='".$params["tanggal"]."'
						OR CONVERT(varchar(10),A.entry_time,120) ='".$params["tanggal"]."')");
			}
			// die($this->bkt->last_query());
		} 
		else if ($params["parammode"]=="EMAIL"){
			$query = $this->bkt->query("select DISTINCT A.User_Name as userid, CONVERT(VARCHAR(MAX),A.Entry_Time,120) as tanggal,
						case when not exists(Select * From Mst_TargetSlsman Where Kode_Target=A.Kode_Target) then 'BARU' else 'UBAH' end as [mode],
						case when dbo.F_GetLevelSalesman(A.Kd_Slsman, YEAR(A.Tgl_Awal), MONTH(A.Tgl_Awal)) in (97,98) then 'SPG' else 'SALESMAN' end as [kategori],
						left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
						month(A.Tgl_Awal) as Month, year(A.Tgl_Awal) as Year,
						B.Wilayah
						From Mst_TargetSlsman_Log A
						INNER JOIN TblMsSalesman B ON A.Kd_Slsman = B.Kd_Slsman
						WHERE A.User_Name ='".$params["userid"]."' 
						AND (CONVERT(varchar(MAX),A.entry_time,120) ='".$params["tanggal"]."'
						OR CONVERT(varchar(10),A.entry_time,120) = CONVERT(varchar(10),'".$params["tanggal"]."',120))");
		} 
		else {
			$query = $this->bkt->query("select A.User_Name as userid, CONVERT(VARCHAR(MAX),A.Entry_Time,120) as tanggal,
						case when not exists(Select * From Mst_TargetSlsman Where Kode_Target=A.Kode_Target) then 'BARU' else 'UBAH' end as [mode],
						case when dbo.F_GetLevelSalesman(A.Kd_Slsman, YEAR(A.Tgl_Awal), MONTH(A.Tgl_Awal)) in (97,98) then 'SPG' else 'SALESMAN' end as [kategori],
						left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
						month(A.Tgl_Awal) as Month, year(A.Tgl_Awal) as Year,
						B.Wilayah
					From Mst_TargetSlsman_Log A
					INNER JOIN TblMsSalesman B ON A.Kd_Slsman = B.Kd_Slsman
					where REPLACE(REPLACE(A.NoRequest,'-',''),'_','') = '".$params["norequest"]."'");
		}

		if ($query->num_rows()>0)	{
			// die(json_encode($query->result()));
			foreach ($query->result_array() as $row) {

				$hasil[] = [
				'query' => $this->bkt->last_query(),
				'mode' => $row['mode'],
				'kategori' => $row['kategori'],
				'userid' => $row['userid'],
				'tanggal' => $row['tanggal'],
				'monthyear' => $row['MonthYear'],
				'month' => $row['Month'],
				'year' => $row['Year']];	
			
			}
		} else {

			$hasil[] = [
					  'query' => $this->bkt->last_query(),
					  'mode' => '',
					  'kategori' => '',
					  'userid' => '',
					  'tanggal' => '',
					  'monthyear' => '',
					  'month' => 0,
					  'year' => 0];			
	
		}
		// die(json_encode($hasil));
		return $hasil;
		//return $query->result();
	}	

	function GetMonthYear_ByEntryTime2($params, $configDB) 
	{
		// die("here");
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);

		if (($params["mode"]=='CANCELREQ') || ($params["mode"]=='HAPUS')){
			$qry = "SELECT DISTINCT RTRIM(B.nm_wil) AS Kota, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
					Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
					left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
					month(A.Tgl_Awal) as Month, year(A.Tgl_Awal) as Year
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblCabang B WITH(NOLOCK) ON A.Kd_Lokasi = B.Kd_Wil 
					WHERE A.DeletedBy ='".$params["userid"]."' 
					AND (CONVERT(varchar(max),A.DeletedDate,120) ='".$params["tanggal"]."' or 
						CONVERT(varchar(10),A.DeletedDate,120) ='".$params["tanggal"]."')";				
		}	
		else {
			$qry = "SELECT DISTINCT RTRIM(B.nm_wil) AS Kota, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
					Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
					left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
					month(A.Tgl_Awal) as Month, year(A.Tgl_Awal) as Year
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblCabang B WITH(NOLOCK) ON A.Kd_Lokasi = B.Kd_Wil 
					WHERE A.User_Name ='".$params["userid"]."' 
					AND (CONVERT(varchar(max),A.entry_time,120) ='".$params["tanggal"]."' or 
						CONVERT(varchar(10),A.entry_time,120) ='".$params["tanggal"]."')";	
		}
		// die($qry);

		// if ($params["lokasiAPIdanDB"] == 'BEDA')
		// {
		// 	$res = $this->bkt->query($qry);
		// }
		// else {
		// 	$res = $this->bkt->query($qry);
		// }


		$res = $this->bkt->query($qry);


		return $res->row();
	}
	
	function GetAtasanSales_ByEntryTime2($params, $configDB) 
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);

		$Level = 61;
		$LevelCabang = 20;	

		$hasil=array();

		if (($params["mode"]=='CANCELREQ') || ($params["mode"]=='HAPUS')) {
			$qry = "SELECT 	Kode_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Kd_Slsman 
													ELSE D.Kd_Slsman END 
												ELSE D.Kd_Slsman END
											ELSE C.Kd_Slsman END
										ELSE C.Kd_Slsman END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Kd_Slsman ELSE '' END
									END,
							Nama_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Nm_Slsman 
													ELSE D.Nm_Slsman END 
												ELSE D.Nm_Slsman END
											ELSE C.Nm_Slsman END
										ELSE C.Nm_Slsman END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Nm_Slsman ELSE '' END
									END,
							Email_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Email 
													ELSE D.Email END 
												ELSE D.Email END
											ELSE C.Email END
										ELSE C.Email END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Email ELSE '' END
									END,
							Kode_Level_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Level_Salesman 
													ELSE D.Level_Salesman END 
												ELSE D.Level_Salesman END
											ELSE C.Level_Salesman END
										ELSE C.Level_Salesman END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Level_Salesman ELSE '' END
									END,
							Level_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														EL.Nama_Level 
													ELSE DL.Nama_Level END 
												ELSE DL.Nama_Level END
											ELSE CL.Nama_Level END
										ELSE CL.Nama_Level END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN BL.Nama_Level ELSE '' END
									END																																			
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON B.Kd_Supervisor = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON C.Kd_Supervisor = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON D.Kd_Supervisor = E.Kd_Slsman
					LEFT JOIN Mst_LevelSalesman BL WITH(NOLOCK) ON B.Level_Salesman = BL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman CL WITH(NOLOCK) ON C.Level_Salesman = CL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman DL WITH(NOLOCK) ON D.Level_Salesman = DL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman EL WITH(NOLOCK) ON E.Level_Salesman = EL.Level_Salesman
					WHERE A.NoRequest IN (SELECT NoRequest FROM Mst_TargetSlsman_Log WITH(NOLOCK)
					WHERE DeletedBy ='".$params["userid"]."'
					AND CONVERT(varchar(max),DeletedDate,120) ='".$params["tanggal"]."')
					AND ISNULL(B.Wilayah,'')='JAKARTA'
					GROUP BY 
					B.Kd_Slsman, B.Nm_Slsman, B.Email, B.Level_Salesman, B.Kd_Supervisor,
					C.Kd_Slsman, C.Nm_Slsman, C.Email, C.Level_Salesman, C.Kd_Supervisor,
					D.Kd_Slsman, D.Nm_Slsman, D.Email, D.Level_Salesman, D.Kd_Supervisor,
					E.Kd_Slsman, E.Nm_Slsman, E.Email, E.Level_Salesman,
					BL.Nama_Level, CL.Nama_Level, DL.Nama_Level, EL.Nama_Level
					UNION ALL
					SELECT	Kode_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Kd_Slsman 
													ELSE D.Kd_Slsman END 
												ELSE D.Kd_Slsman END
											ELSE C.Kd_Slsman END
										ELSE C.Kd_Slsman END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 'GM' ELSE '' END
									END,
							Nama_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Nm_Slsman 
													ELSE D.Nm_Slsman END 
												ELSE D.Nm_Slsman END
											ELSE C.Nm_Slsman END
										ELSE C.Nm_Slsman END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 'GM' ELSE '' END
									END,
							Email_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Email 
													ELSE D.Email END 
												ELSE D.Email END
											ELSE C.Email END
										ELSE C.Email END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 'GM' ELSE '' END
									END,
							Kode_Level_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Level_Salesman 
													ELSE D.Level_Salesman END 
												ELSE D.Level_Salesman END
											ELSE C.Level_Salesman END
										ELSE C.Level_Salesman END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 1 ELSE '' END
									END,
							Level_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														EL.Nama_Level 
													ELSE DL.Nama_Level END 
												ELSE DL.Nama_Level END
											ELSE CL.Nama_Level END
										ELSE CL.Nama_Level END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 'GM' ELSE '' END
									END																																			
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON B.Kd_Supervisor = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON C.Kd_Supervisor = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON D.Kd_Supervisor = E.Kd_Slsman
					LEFT JOIN Mst_LevelSalesman BL WITH(NOLOCK) ON B.Level_Salesman = BL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman CL WITH(NOLOCK) ON C.Level_Salesman = CL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman DL WITH(NOLOCK) ON D.Level_Salesman = DL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman EL WITH(NOLOCK) ON E.Level_Salesman = EL.Level_Salesman
					WHERE A.NoRequest IN (SELECT NoRequest FROM Mst_TargetSlsman_Log WITH(NOLOCK)
					WHERE DeletedBy ='".$params["userid"]."'
					AND CONVERT(varchar(max),DeletedDate,120) ='".$params["tanggal"]."')
					AND ISNULL(B.Wilayah,'')<>'JAKARTA'
					GROUP BY 
					B.Kd_Slsman, B.Nm_Slsman, B.Email, B.Level_Salesman, B.Kd_Supervisor,
					C.Kd_Slsman, C.Nm_Slsman, C.Email, C.Level_Salesman, C.Kd_Supervisor,
					D.Kd_Slsman, D.Nm_Slsman, D.Email, D.Level_Salesman, D.Kd_Supervisor,
					E.Kd_Slsman, E.Nm_Slsman, E.Email, E.Level_Salesman,
					BL.Nama_Level, CL.Nama_Level, DL.Nama_Level, EL.Nama_Level";			
		}
		else {
			$qry = "SELECT 	Kode_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Kd_Slsman 
													ELSE D.Kd_Slsman END 
												ELSE D.Kd_Slsman END
											ELSE C.Kd_Slsman END
										ELSE C.Kd_Slsman END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Kd_Slsman ELSE '' END
									END,
							Nama_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Nm_Slsman 
													ELSE D.Nm_Slsman END 
												ELSE D.Nm_Slsman END
											ELSE C.Nm_Slsman END
										ELSE C.Nm_Slsman END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Nm_Slsman ELSE '' END
									END,
							Email_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Email 
													ELSE D.Email END 
												ELSE D.Email END
											ELSE C.Email END
										ELSE C.Email END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Email ELSE '' END
									END,
							Kode_Level_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Level_Salesman 
													ELSE D.Level_Salesman END 
												ELSE D.Level_Salesman END
											ELSE C.Level_Salesman END
										ELSE C.Level_Salesman END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN B.Level_Salesman ELSE '' END
									END,
							Level_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$Level."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$Level."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														EL.Nama_Level 
													ELSE DL.Nama_Level END 
												ELSE DL.Nama_Level END
											ELSE CL.Nama_Level END
										ELSE CL.Nama_Level END
									ELSE
										CASE WHEN B.Level_Salesman > '".$Level."' THEN BL.Nama_Level ELSE '' END
									END																																			
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON B.Kd_Supervisor = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON C.Kd_Supervisor = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON D.Kd_Supervisor = E.Kd_Slsman
					LEFT JOIN Mst_LevelSalesman BL WITH(NOLOCK) ON B.Level_Salesman = BL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman CL WITH(NOLOCK) ON C.Level_Salesman = CL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman DL WITH(NOLOCK) ON D.Level_Salesman = DL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman EL WITH(NOLOCK) ON E.Level_Salesman = EL.Level_Salesman
					WHERE A.NoRequest IN (SELECT NoRequest FROM Mst_TargetSlsman_Log WITH(NOLOCK)
					WHERE User_Name ='".$params["userid"]."'
					AND (CONVERT(varchar(10),entry_time,120) ='".$params["tanggal"]."' 
					OR CONVERT(varchar(MAX),entry_time,120) ='".$params["tanggal"]."'))
					AND ISNULL(B.Wilayah,'')='JAKARTA'
					GROUP BY 
					B.Kd_Slsman, B.Nm_Slsman, B.Email, B.Level_Salesman, B.Kd_Supervisor,
					C.Kd_Slsman, C.Nm_Slsman, C.Email, C.Level_Salesman, C.Kd_Supervisor,
					D.Kd_Slsman, D.Nm_Slsman, D.Email, D.Level_Salesman, D.Kd_Supervisor,
					E.Kd_Slsman, E.Nm_Slsman, E.Email, E.Level_Salesman,
					BL.Nama_Level, CL.Nama_Level, DL.Nama_Level, EL.Nama_Level
				UNION ALL
					SELECT 	Kode_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Kd_Slsman 
													ELSE D.Kd_Slsman END 
												ELSE D.Kd_Slsman END
											ELSE C.Kd_Slsman END
										ELSE C.Kd_Slsman END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 'GM' ELSE '' END
									END,
							Nama_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Nm_Slsman 
													ELSE D.Nm_Slsman END 
												ELSE D.Nm_Slsman END
											ELSE C.Nm_Slsman END
										ELSE C.Nm_Slsman END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 'GM' ELSE '' END
									END,
							Email_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Email 
													ELSE D.Email END 
												ELSE D.Email END
											ELSE C.Email END
										ELSE C.Email END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 'GM' ELSE '' END
									END,
							Kode_Level_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														E.Level_Salesman 
													ELSE D.Level_Salesman END 
												ELSE D.Level_Salesman END
											ELSE C.Level_Salesman END
										ELSE C.Level_Salesman END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 1 ELSE '' END
									END,
							Level_Atasan=
									CASE WHEN B.Kd_Supervisor <> '' THEN
										CASE WHEN C.Level_Salesman > '".$LevelCabang."' THEN 
											CASE WHEN C.Kd_Supervisor <> '' THEN
												CASE WHEN D.Level_Salesman > '".$LevelCabang."' THEN 
													CASE WHEN D.Kd_Supervisor <> '' THEN
														EL.Nama_Level 
													ELSE DL.Nama_Level END 
												ELSE DL.Nama_Level END
											ELSE CL.Nama_Level END
										ELSE CL.Nama_Level END
									ELSE
										CASE WHEN B.Level_Salesman = '".$LevelCabang."' THEN 'GM' ELSE '' END
									END																																			
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON B.Kd_Supervisor = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON C.Kd_Supervisor = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON D.Kd_Supervisor = E.Kd_Slsman
					LEFT JOIN Mst_LevelSalesman BL WITH(NOLOCK) ON B.Level_Salesman = BL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman CL WITH(NOLOCK) ON C.Level_Salesman = CL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman DL WITH(NOLOCK) ON D.Level_Salesman = DL.Level_Salesman
					LEFT JOIN Mst_LevelSalesman EL WITH(NOLOCK) ON E.Level_Salesman = EL.Level_Salesman
					WHERE A.NoRequest IN (SELECT NoRequest FROM Mst_TargetSlsman_Log WITH(NOLOCK)
					WHERE User_Name ='".$params["userid"]."'
					AND (CONVERT(varchar(10),entry_time,120) ='".$params["tanggal"]."'
					OR CONVERT(varchar(MAX),entry_time,120) ='".$params["tanggal"]."'))
					AND ISNULL(B.Wilayah,'')<>'JAKARTA'
					GROUP BY 
					B.Kd_Slsman, B.Nm_Slsman, B.Email, B.Level_Salesman, B.Kd_Supervisor,
					C.Kd_Slsman, C.Nm_Slsman, C.Email, C.Level_Salesman, C.Kd_Supervisor,
					D.Kd_Slsman, D.Nm_Slsman, D.Email, D.Level_Salesman, D.Kd_Supervisor,
					E.Kd_Slsman, E.Nm_Slsman, E.Email, E.Level_Salesman,
					BL.Nama_Level, CL.Nama_Level, DL.Nama_Level, EL.Nama_Level";
		}

		//die($qry);

		// if ($params["lokasiAPIdanDB"] == 'BEDA')
		// {
		// 	$res = $this->bkt->query($qry);
		// }
		// else {
		// 	$res = $this->bkt->query($qry);
		// }	

		$res = $this->bkt->query($qry);

		foreach ($res->result_array() as $row)
		{					
			$hasil[] = ['Kode_Atasan' => TRIM($row['Kode_Atasan']),
						'Nama_Atasan' => TRIM($row['Nama_Atasan']),
						'Email_Atasan' => TRIM($row['Email_Atasan']),
						'Kode_Level_Atasan' => TRIM($row['Kode_Level_Atasan']),
						'Level_Atasan' => TRIM($row['Level_Atasan'])];						
		}	
		return $hasil;				
	}
	
	function GetTargetHDLog_ByEntryTime2($params, $configDB)
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);

		$hasil=array();

		$str = "";

		if ($params["kategori"]=='SALESMAN'){	
			if ($params["mode"]=='BARU'){		

				$str = "SELECT tipe='BARU', A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
					Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
					left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
					Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
					Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
					User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
					IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
					IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
					IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
					User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
					kd_lokasi=isnull(A.kd_lokasi,'')
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
					LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
					WHERE A.User_Name ='".$params["userid"]."'
					AND CONVERT(varchar(max),A.entry_time,120) ='".$params["tanggal"]."'
					AND A.IsApproved = 0
					AND ISNULL(A.IsRejected,0) = 0 
					AND ISNULL(A.IsDeleted,0) = 0
					AND ISNULL(A.IsEmailed,0) = 0
					AND ISNULL(A.IsEmailedPembatalan,0) = 0
					AND A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))
					AND (A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman_Log WITH(NOLOCK)
					WHERE CONVERT(varchar(max),entry_time,120) <'".$params["tanggal"]."')
					)					
					AND B.Level_Salesman NOT IN ('97','98')
					UNION ALL
					SELECT tipe='REINPUT', A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
					Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
					left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
					Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
					Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
					User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
					IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
					IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
					IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
					User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
					kd_lokasi=isnull(A.kd_lokasi,'')
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
					LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
					WHERE A.User_Name ='".$params["userid"]."'
					AND CONVERT(varchar(max),A.entry_time,120) ='".$params["tanggal"]."'
					AND A.IsApproved = 0
					AND ISNULL(A.IsRejected,0) = 0 
					AND ISNULL(A.IsDeleted,0) = 0
					AND ISNULL(A.IsEmailed,0) = 0
					AND ISNULL(A.IsEmailedPembatalan,0) = 0
					AND A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))
					AND A.Kode_Target IN (SELECT Kode_Target FROM Mst_TargetSlsman_Log WITH(NOLOCK) 
					WHERE CONVERT(varchar(max),entry_time,120) <'".$params["tanggal"]."' AND (ISNULL(IsDeleted,0) = 1 OR ISNULL(IsRejected,0) = 1))
					AND B.Level_Salesman NOT IN ('97','98')";
			}
			else if ($params["mode"]=='UBAH'){	
				$str="SELECT tipe='UBAH',A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
					Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
					left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
					Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
					Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
					User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
					IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
					IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
					IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
					User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
					kd_lokasi=isnull(A.kd_lokasi,'')
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
					LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
					WHERE A.User_Name ='".$params["userid"]."'
					AND CONVERT(varchar(max),A.entry_time,120) ='".$params["tanggal"]."'
					AND A.IsApproved = 0 
					AND ISNULL(A.IsRejected,0) = 0 
					AND ISNULL(A.IsDeleted,0) = 0
					AND ISNULL(A.IsEmailed,0) = 0
					AND ISNULL(A.IsEmailedPembatalan,0) = 0
					AND A.Kode_Target IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))
					AND B.Level_Salesman NOT IN ('97','98')";
			}
			else if ($params["mode"]=='CANCELREQ'){	
				$str="SELECT tipe='CANCELREQ',A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
					Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
					left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
					Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
					Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
					User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
					IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
					IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
					IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
					User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
					kd_lokasi=isnull(A.kd_lokasi,'')
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman 
					LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
					WHERE A.DeletedBy ='".$params["userid"]."'
					AND CONVERT(varchar(max),A.DeletedDate,120) ='".$params["tanggal"]."'
					AND A.IsApproved = 0 
					AND ISNULL(A.IsRejected,0) = 0 
					AND ISNULL(A.IsDeleted,0) = 1
					AND ISNULL(A.IsEmailedPembatalan,0) = 0
					AND B.Level_Salesman NOT IN ('97','98')";	
			}
			else if ($params["mode"]=='HAPUS'){	
				$str="SELECT tipe='HAPUS',A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
					Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
					left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
					Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
					Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
					User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
					IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
					IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
					IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
					User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
					kd_lokasi=isnull(A.kd_lokasi,'')
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman 
					LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
					WHERE A.DeletedBy ='".$params["userid"]."'
					AND CONVERT(varchar(max),A.DeletedDate,120) ='".$params["tanggal"]."'
					AND A.IsApproved = 1 
					AND ISNULL(A.IsRejected,0) = 0 
					AND ISNULL(A.IsDeleted,0) = 1
					AND ISNULL(A.IsEmailedPembatalan,0) = 0
					AND B.Level_Salesman NOT IN ('97','98')";	
			}
			else if ($params["mode"]=='REMINDER'){	
				$str="SELECT tipe='REMINDER', A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
				Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
				left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
				Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
				Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
				User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
				IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
				IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
				IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
				User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
				kd_lokasi=isnull(A.kd_lokasi,'')
				FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
				LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
				LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
				LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
				LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
				LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
				WHERE A.User_Name ='".$params["userid"]."'
				AND CONVERT(varchar(max),A.Entry_Time,120) ='".$params["tanggal"]."'
				AND A.IsApproved = 0
				AND ISNULL(A.IsRejected,0) = 0 
				AND ISNULL(A.IsDeleted,0) = 0
				AND ISNULL(A.IsEmailed,0) = 1
				AND ISNULL(A.IsEmailedPembatalan,0) = 0
				AND A.EmailedDate IS NOT NULL
				AND A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))				
				AND B.Level_Salesman NOT IN ('97','98')";					
			}					
		} else if ($params["kategori"]=='SPG'){	
			if ($params["mode"]=='BARU'){		
				$str="SELECT tipe='BARU', A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
				Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
				left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
				Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
				Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
				User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
				IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
				IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
				IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
				User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
				kd_lokasi=isnull(A.kd_lokasi,'')
				FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
				LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
				LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
				LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
				LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
				LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
				WHERE A.User_Name ='".$params["userid"]."'
				AND CONVERT(varchar(max),A.entry_time,120) ='".$params["tanggal"]."'
				AND A.IsApproved = 0
				AND ISNULL(A.IsRejected,0) = 0 
				AND ISNULL(A.IsDeleted,0) = 0
				AND ISNULL(A.IsEmailed,0) = 0
				AND ISNULL(A.IsEmailedPembatalan,0) = 0
				AND A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))
				AND (A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman_Log WITH(NOLOCK)
				WHERE CONVERT(varchar(max),entry_time,120) <'".$params["tanggal"]."')
				)					
				AND B.Level_Salesman IN ('97','98')
				UNION ALL
				SELECT tipe='REINPUT', A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
				Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
				left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
				Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
				Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
				User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
				IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
				IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
				IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
				User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
				kd_lokasi=isnull(A.kd_lokasi,'')
				FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
				LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
				LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
				LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
				LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
				LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
				WHERE A.User_Name ='".$params["userid"]."'
				AND CONVERT(varchar(max),A.entry_time,120) ='".$params["tanggal"]."'
				AND A.IsApproved = 0
				AND ISNULL(A.IsRejected,0) = 0 
				AND ISNULL(A.IsDeleted,0) = 0
				AND ISNULL(A.IsEmailed,0) = 0
				AND ISNULL(A.IsEmailedPembatalan,0) = 0
				AND A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))
				AND A.Kode_Target IN (SELECT Kode_Target FROM Mst_TargetSlsman_Log WITH(NOLOCK) 
				WHERE CONVERT(varchar(max),entry_time,120) <'".$params["tanggal"]."' AND (ISNULL(IsDeleted,0) = 1 OR ISNULL(IsRejected,0) = 1))
				AND B.Level_Salesman IN ('97','98')";
			}
			else if ($params["mode"]=='UBAH'){	
				$str="SELECT tipe='UBAH',A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
					Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
					left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
					Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
					Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
					User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
					IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
					IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
					IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
					User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
					kd_lokasi=isnull(A.kd_lokasi,'')
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
					LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
					WHERE A.User_Name ='".$params["userid"]."'
					AND CONVERT(varchar(max),A.entry_time,120) ='".$params["tanggal"]."'
					AND A.IsApproved = 0 
					AND ISNULL(A.IsRejected,0) = 0 
					AND ISNULL(A.IsDeleted,0) = 0
					AND ISNULL(A.IsEmailed,0) = 0
					AND ISNULL(A.IsEmailedPembatalan,0) = 0
					AND A.Kode_Target IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))
					AND B.Level_Salesman IN ('97','98')";
			}
			else if ($params["mode"]=='CANCELREQ'){	
				$str="SELECT tipe='CANCELREQ',A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
					Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
					left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
					Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
					Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
					User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
					IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
					IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
					IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
					User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
					kd_lokasi=isnull(A.kd_lokasi,'')
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
					LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
					WHERE A.DeletedBy ='".$params["userid"]."'
					AND CONVERT(varchar(max),A.DeletedDate,120) ='".$params["tanggal"]."'
					AND A.IsApproved = 0 
					AND ISNULL(A.IsRejected,0) = 0 
					AND ISNULL(A.IsDeleted,0) = 1
					AND ISNULL(A.IsEmailedPembatalan,0) = 0
					AND B.Level_Salesman IN ('97','98')";	
			}	
			else if ($params["mode"]=='HAPUS'){	
				$str="SELECT tipe='HAPUS',A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
					Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
					left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
					Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
					Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
					User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
					IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
					IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
					IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
					User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
					kd_lokasi=isnull(A.kd_lokasi,'')
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
					LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
					WHERE A.DeletedBy ='".$params["userid"]."'
					AND CONVERT(varchar(max),A.DeletedDate,120) ='".$params["tanggal"]."'
					AND A.IsApproved = 1
					AND ISNULL(A.IsRejected,0) = 0 
					AND ISNULL(A.IsDeleted,0) = 1
					AND ISNULL(A.IsEmailedPembatalan,0) = 0
					AND B.Level_Salesman IN ('97','98')";	
			}
			else if ($params["mode"]=='REMINDER'){	
				$str="SELECT tipe='REMINDER', A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
				Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
				left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
				Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
				Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
				User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
				IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
				IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
				IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
				User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
				kd_lokasi=isnull(A.kd_lokasi,'')
				FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
				LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
				LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
				LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
				LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
				LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
				WHERE A.User_Name ='".$params["userid"]."'
				AND CONVERT(varchar(max),A.Entry_Time,120) ='".$params["tanggal"]."'
				AND A.IsApproved = 0
				AND ISNULL(A.IsRejected,0) = 0 
				AND ISNULL(A.IsDeleted,0) = 0
				AND ISNULL(A.IsEmailed,0) = 1
				AND ISNULL(A.IsEmailedPembatalan,0) = 0
				AND A.EmailedDate IS NOT NULL
				AND A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))				
				AND B.Level_Salesman IN ('97','98')";					
			}									
		}	
		
		$query = $this->bkt->query($str);

		// if ($params["lokasiAPIdanDB"] == 'BEDA')
		// {
		// 	$query = $this->bkt->query($str);
		// }
		// else {
		// 	$query = $this->bkt->query($str);
		// }	

		if( ($errors = sqlsrv_errors() ) != null) {
			$ERR_MSG = "";
	        foreach( $errors as $error ) {
	        	$ERR_CODE = $error["code"];
			    $ERR_MSG.= "code: ".$ERR_CODE."<br />";
	            $ERR_MSG.= "message: ".$error[ 'message']."<br />";
	        }
	        return("ERROR: ".$ERR_MSG);
	    }
		
		if ($query->num_rows()==0)	 {
			return("ERROR: Target Tidak Ditemukan");
		}
		
		foreach ($query->result_array() as $row)
		{					
			$hasil[] = ['tipe' => $row['tipe'],
				      'kode_target' => $row['kode_target'],
					  'Tgl_Awal' => $row['Tgl_Awal'],
					  'Tgl_Akhir' => $row['Tgl_Akhir'],	
					  'MonthYear' => $row['MonthYear'],
					  'Training' => $row['Training'],
					  'Kd_Slsman' => $row['Kd_Slsman'],
					  'Nm_Slsman' => $row['Nm_Slsman'],	
					  'Total_Target' => $row['Total_Target'],	
					  'User_ID' => $row['User_ID'],				
					  'User_Name' => $row['User_Name'],
					  'User_Email' => $row['User_Email'],
					  'Entry_Time' => $row['Entry_Time'],
					  'NoRequest' => $row['NoRequest'],

					  'IsApproved' => $row['IsApproved'],
					  'UserApproved_ID' => $row['UserApproved_ID'],
					  'UserApproved_Name' => $row['UserApproved_Name'],
					  'UserApproved_Email' => $row['UserApproved_Email'],
					  'ApprovedDate' => $row['ApprovedDate'],

					  'IsRejected' => $row['IsRejected'],
					  'UserRejected_ID' => $row['UserRejected_ID'],
					  'UserRejected_Name' => $row['UserRejected_Name'],
					  'UserRejected_Email' => $row['UserRejected_Email'],
					  'RejectedDate' => $row['RejectedDate'],	

					  'IsDeleted' => $row['IsDeleted'],
					  'UserDelete_ID' => $row['UserDelete_ID'],
					  'UserDelete_Name' => $row['UserDelete_Name'],
					  'UserDelete_Email' => $row['UserDelete_Email'],
					  'DeletedDate' => $row['DeletedDate'],
					
					  'User_Level' => $row['User_Level'],
					  'UserApproved_Level' => $row['UserApproved_Level'],
					  'UserRejected_Level' => $row['UserRejected_Level'],
					  'UserDelete_Level' => $row['UserDelete_Level'],
					  
					  'EmailedDate' => $row['EmailedDate'],
					  'kd_lokasi' => $row['kd_lokasi']
					];	
					
			return json_encode($hasil);
		}
		return $hasil;	
	}
	
	function GetTargetHDLog_ByEntryTimeResendEmail($params, $configDB)
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);

		$hasil=array();

		$str = "SELECT tipe='BARU', A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
				Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
				left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
				Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
				Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
				User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
				IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
				IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
				IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
				User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
				kd_lokasi=isnull(A.kd_lokasi,'')
				FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
				LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
				LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
				LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
				LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
				LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
				WHERE A.User_Name = '".$params["userid"]."'
				AND 				
				(
				CONVERT(varchar(MAX),A.entry_time,120) = '".$params["tanggal"]."' 
				OR 
				CONVERT(varchar(10),A.entry_time,120) = CONVERT(varchar(10),'".$params["tanggal"]."',120)
				)
				AND A.IsApproved = 0
				AND ISNULL(A.IsRejected,0) = 0  
				AND ISNULL(A.IsDeleted,0) = 0 
				AND ISNULL(A.IsEmailedPembatalan,0) = 0
				AND A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))
				AND (A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman_Log WITH(NOLOCK)
				WHERE  
				CONVERT(varchar(10),A.entry_time,120) < CONVERT(varchar(10),'".$params["tanggal"]."',120) 
				)
				)					
				AND B.Level_Salesman IN ('97','98')
				UNION ALL
				SELECT tipe='REINPUT', A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
				Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
				left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
				Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
				Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
				User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
				IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
				IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
				IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
				User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
				kd_lokasi=isnull(A.kd_lokasi,'')
				FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
				LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
				LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
				LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
				LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
				LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
				WHERE A.User_Name ='".$params["userid"]."'
				AND 				
				(
				CONVERT(varchar(MAX),A.entry_time,120) = '".$params["tanggal"]."' 
				OR 
				CONVERT(varchar(10),A.entry_time,120) = CONVERT(varchar(10),'".$params["tanggal"]."',120)
				)
				AND A.IsApproved = 0
				AND ISNULL(A.IsRejected,0) = 0 
				AND ISNULL(A.IsDeleted,0) = 0 
				AND ISNULL(A.IsEmailedPembatalan,0) = 0
				AND A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))
				AND A.Kode_Target IN (SELECT Kode_Target FROM Mst_TargetSlsman_Log WITH(NOLOCK) 
				WHERE CONVERT(varchar(10),A.entry_time,120) < CONVERT(varchar(10),'".$params["tanggal"]."',120)  AND (ISNULL(IsDeleted,0) = 1 OR ISNULL(IsRejected,0) = 1))
				AND B.Level_Salesman IN ('97','98')"; 

		$query = $this->bkt->query($str);

		if( ($errors = sqlsrv_errors() ) != null) {
			$ERR_MSG = "";
	        foreach( $errors as $error ) {
	        	$ERR_CODE = $error["code"];
			    $ERR_MSG.= "code: ".$ERR_CODE."<br />";
	            $ERR_MSG.= "message: ".$error[ 'message']."<br />";
	        }
	        return("ERROR: ".$ERR_MSG);
	    }
		
		if ($query->num_rows()==0)	 {
			//die($str);
			return("ERROR: Target Tidak Ditemukan");
		} 
		
		foreach ($query->result_array() as $row)
		{					
			$hasil[] = ['tipe' => $row['tipe'],
				      'kode_target' => $row['kode_target'],
					  'Tgl_Awal' => $row['Tgl_Awal'],
					  'Tgl_Akhir' => $row['Tgl_Akhir'],	
					  'MonthYear' => $row['MonthYear'],
					  'Training' => $row['Training'],
					  'Kd_Slsman' => $row['Kd_Slsman'],
					  'Nm_Slsman' => $row['Nm_Slsman'],	
					  'Total_Target' => $row['Total_Target'],	
					  'User_ID' => $row['User_ID'],				
					  'User_Name' => $row['User_Name'],
					  'User_Email' => $row['User_Email'],
					  'Entry_Time' => $row['Entry_Time'],
					  'NoRequest' => $row['NoRequest'],

					  'IsApproved' => $row['IsApproved'],
					  'UserApproved_ID' => $row['UserApproved_ID'],
					  'UserApproved_Name' => $row['UserApproved_Name'],
					  'UserApproved_Email' => $row['UserApproved_Email'],
					  'ApprovedDate' => $row['ApprovedDate'],

					  'IsRejected' => $row['IsRejected'],
					  'UserRejected_ID' => $row['UserRejected_ID'],
					  'UserRejected_Name' => $row['UserRejected_Name'],
					  'UserRejected_Email' => $row['UserRejected_Email'],
					  'RejectedDate' => $row['RejectedDate'],	

					  'IsDeleted' => $row['IsDeleted'],
					  'UserDelete_ID' => $row['UserDelete_ID'],
					  'UserDelete_Name' => $row['UserDelete_Name'],
					  'UserDelete_Email' => $row['UserDelete_Email'],
					  'DeletedDate' => $row['DeletedDate'],
					
					  'User_Level' => $row['User_Level'],
					  'UserApproved_Level' => $row['UserApproved_Level'],
					  'UserRejected_Level' => $row['UserRejected_Level'],
					  'UserDelete_Level' => $row['UserDelete_Level'],
					  
					  'EmailedDate' => $row['EmailedDate'],
					  'kd_lokasi' => $row['kd_lokasi']
					];	

			return json_encode($hasil);
		}
		return $hasil;	
	}		

	function GetTargetHDLog_ByNoRequest2($params, $configDB) 
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);

		$str = "";
		//Buang Filter Berikut Supaya Bisa Email Ulang Request Target Salesman/SPG
		//AND ISNULL(A.IsEmailed,0) = 0

		$hasil=array();

		if ($params["kategori"]=='SALESMAN'){	
			if ($params["mode"]=='BARU'){		

				$str = "SELECT tipe='BARU', A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
					Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
					left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
					Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
					Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
					User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
					IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
					IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
					IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
					User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
					kd_lokasi=isnull(A.kd_lokasi,'')
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
					LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
					WHERE REPLACE(REPLACE(A.NoRequest,'-',''),'_','') ='".$params["norequest"]."'
					AND A.IsApproved = 0
					AND ISNULL(A.IsRejected,0) = 0 
					AND ISNULL(A.IsDeleted,0) = 0
					AND ISNULL(A.IsEmailedPembatalan,0) = 0
					AND A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))
					AND (A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman_Log WITH(NOLOCK)
					WHERE CONVERT(varchar(max),entry_time,120) <'".$params["tanggal"]."')
					OR EXISTS (Select * From Mst_TargetSlsman_Log 
					WHERE Mst_TargetSlsman_Log.Kode_Target = A.Kode_Target 
					AND CONVERT(varchar(max),entry_time,120) <'".$params["tanggal"]."'))					
					AND B.Level_Salesman NOT IN ('97','98')
					UNION ALL
					SELECT tipe='REINPUT', A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
					Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
					left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
					Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
					Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
					User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
					IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
					IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
					IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
					User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
					kd_lokasi=isnull(A.kd_lokasi,'')
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
					LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
					WHERE REPLACE(REPLACE(A.NoRequest,'-',''),'_','') ='".$params["norequest"]."'
					AND A.IsApproved = 0
					AND ISNULL(A.IsRejected,0) = 0 
					AND ISNULL(A.IsDeleted,0) = 0
					AND ISNULL(A.IsEmailedPembatalan,0) = 0
					AND A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))
					AND A.Kode_Target IN (SELECT Kode_Target FROM Mst_TargetSlsman_Log WITH(NOLOCK) 
					WHERE CONVERT(varchar(max),entry_time,120) <'".$params["tanggal"]."' AND ISNULL(IsDeleted,0) = 1)
					AND B.Level_Salesman NOT IN ('97','98')";
			}
			else if ($params["mode"]=='UBAH'){	
				$str="SELECT tipe='UBAH',A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
					Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
					left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
					Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
					Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
					User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
					IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
					IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
					IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
					User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
					kd_lokasi=isnull(A.kd_lokasi,'')
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
					LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
					WHERE REPLACE(REPLACE(A.NoRequest,'-',''),'_','') ='".$params["norequest"]."'
					AND A.IsApproved = 0 
					AND ISNULL(A.IsRejected,0) = 0 
					AND ISNULL(A.IsDeleted,0) = 0
					AND ISNULL(A.IsEmailedPembatalan,0) = 0
					AND A.Kode_Target IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))
					AND B.Level_Salesman NOT IN ('97','98')";
			}
			else if ($params["mode"]=='CANCELREQ'){	
				$str="SELECT tipe='CANCELREQ',A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
					Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
					left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
					Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
					Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
					User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
					IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
					IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
					IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
					User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
					kd_lokasi=isnull(A.kd_lokasi,'')
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman 
					LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
					WHERE REPLACE(REPLACE(A.NoRequest,'-',''),'_','') ='".$params["norequest"]."'
					AND A.IsApproved = 0 
					AND ISNULL(A.IsRejected,0) = 0 
					AND ISNULL(A.IsDeleted,0) = 1
					AND ISNULL(A.IsEmailedPembatalan,0) = 0
					AND B.Level_Salesman NOT IN ('97','98')";	
			}
			else if ($params["mode"]=='HAPUS'){	
				$str="SELECT tipe='HAPUS',A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
					Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
					left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
					Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
					Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
					User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
					IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
					IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
					IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
					User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
					kd_lokasi=isnull(A.kd_lokasi,'')
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman 
					LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
					WHERE REPLACE(REPLACE(A.NoRequest,'-',''),'_','') ='".$params["norequest"]."'
					AND A.IsApproved = 1 
					AND ISNULL(A.IsRejected,0) = 0 
					AND ISNULL(A.IsDeleted,0) = 1
					AND ISNULL(A.IsEmailedPembatalan,0) = 0
					AND B.Level_Salesman NOT IN ('97','98')";	
			}
			else if ($params["mode"]=='REMINDER'){	
				$str="SELECT tipe='REMINDER', A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
				Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
				left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
				Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
				Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
				User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
				IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
				IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
				IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
				User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
				kd_lokasi=isnull(A.kd_lokasi,'')
				FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
				LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
				LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
				LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
				LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
				LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
				WHERE REPLACE(REPLACE(A.NoRequest,'-',''),'_','') ='".$params["norequest"]."'
				AND A.IsApproved = 0
				AND ISNULL(A.IsRejected,0) = 0 
				AND ISNULL(A.IsDeleted,0) = 0
				AND ISNULL(A.IsEmailed,0) in (0,1)
				AND ISNULL(A.IsEmailedPembatalan,0) = 0
				AND A.EmailedDate IS NOT NULL
				AND A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))				
				AND B.Level_Salesman NOT IN ('97','98')";					
			}					
		} else if ($params["kategori"]=='SPG'){	
			if ($params["mode"]=='BARU'){		
				$str="SELECT tipe='BARU', A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
				Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
				left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
				Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
				Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
				User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
				IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
				IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
				IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
				User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
				kd_lokasi=isnull(A.kd_lokasi,'')
				FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
				LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
				LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
				LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
				LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
				LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
				WHERE REPLACE(REPLACE(A.NoRequest,'-',''),'_','') ='".$params["norequest"]."'
				AND A.IsApproved = 0
				AND ISNULL(A.IsRejected,0) = 0 
				AND ISNULL(A.IsDeleted,0) = 0
				AND ISNULL(A.IsEmailedPembatalan,0) = 0
				AND A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))
				AND (A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman_Log WITH(NOLOCK)
				WHERE CONVERT(varchar(max),entry_time,120) <'".$params["tanggal"]."')
				OR EXISTS (Select * From Mst_TargetSlsman_Log 
				WHERE Mst_TargetSlsman_Log.Kode_Target = A.Kode_Target 
				AND CONVERT(varchar(max),entry_time,120) <'".$params["tanggal"]."'))					
				AND B.Level_Salesman IN ('97','98')
				UNION ALL
				SELECT tipe='REINPUT', A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
				Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
				left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
				Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
				Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
				User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
				IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
				IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
				IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
				User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
				kd_lokasi=isnull(A.kd_lokasi,'')
				FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
				LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
				LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
				LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
				LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
				LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
				WHERE REPLACE(REPLACE(A.NoRequest,'-',''),'_','') ='".$params["norequest"]."'
				AND A.IsApproved = 0
				AND ISNULL(A.IsRejected,0) = 0 
				AND ISNULL(A.IsDeleted,0) = 0
				AND ISNULL(A.IsEmailedPembatalan,0) = 0
				AND A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))
				AND A.Kode_Target IN (SELECT Kode_Target FROM Mst_TargetSlsman_Log WITH(NOLOCK) 
				WHERE CONVERT(varchar(max),entry_time,120) <'".$params["tanggal"]."' AND ISNULL(IsDeleted,0) = 1)
				AND B.Level_Salesman IN ('97','98')";
			}
			else if ($params["mode"]=='UBAH'){	
				$str="SELECT tipe='UBAH',A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
					Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
					left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
					Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
					Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
					User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
					IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
					IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
					IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
					User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
					kd_lokasi=isnull(A.kd_lokasi,'')
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
					LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
					WHERE REPLACE(REPLACE(A.NoRequest,'-',''),'_','') ='".$params["norequest"]."'
					AND A.IsApproved = 0 
					AND ISNULL(A.IsRejected,0) = 0 
					AND ISNULL(A.IsDeleted,0) = 0
					AND ISNULL(A.IsEmailedPembatalan,0) = 0
					AND A.Kode_Target IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))
					AND B.Level_Salesman IN ('97','98')";
			}
			else if ($params["mode"]=='CANCELREQ'){	
				$str="SELECT tipe='CANCELREQ',A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
					Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
					left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
					Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
					Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
					User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
					IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
					IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
					IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
					User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
					kd_lokasi=isnull(A.kd_lokasi,'')
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
					LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
					WHERE REPLACE(REPLACE(A.NoRequest,'-',''),'_','') ='".$params["norequest"]."'
					AND A.IsApproved = 0 
					AND ISNULL(A.IsRejected,0) = 0 
					AND ISNULL(A.IsDeleted,0) = 1
					AND ISNULL(A.IsEmailedPembatalan,0) = 0
					AND B.Level_Salesman IN ('97','98')";	
			}	
			else if ($params["mode"]=='HAPUS'){	
				$str="SELECT tipe='HAPUS',A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
					Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
					left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
					Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
					Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
					User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
					IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
					IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
					IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
					User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
					kd_lokasi=isnull(A.kd_lokasi,'')
					FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
					LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
					LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
					LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
					LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
					LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
					WHERE REPLACE(REPLACE(A.NoRequest,'-',''),'_','') ='".$params["norequest"]."'
					AND A.IsApproved = 1
					AND ISNULL(A.IsRejected,0) = 0 
					AND ISNULL(A.IsDeleted,0) = 1
					AND ISNULL(A.IsEmailedPembatalan,0) = 0
					AND B.Level_Salesman IN ('97','98')";	
			}
			else if ($params["mode"]=='REMINDER'){	
				$str="SELECT tipe='REMINDER', A.kode_target, Tgl_Awal=CONVERT(VARCHAR(MAX),A.Tgl_Awal,106), 
				Tgl_Akhir=CONVERT(VARCHAR(MAX),A.Tgl_Akhir,106),
				left(datename(m,A.Tgl_Awal),3)+' '+cast(datepart(yyyy,A.Tgl_Awal) as varchar) as MonthYear,
				Training=CASE A.Training WHEN 1 THEN 'YA' ELSE 'TIDAK' END,
				Kd_Slsman=RTRIM(A.Kd_Slsman), Nm_Slsman=RTRIM(B.Nm_Slsman), Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1),
				User_ID=A.User_Name, User_Name=LTRIM(RTRIM(C.Nm_Slsman)), User_Email=LTRIM(RTRIM(C.Email)), Entry_Time=CONVERT(VARCHAR(MAX),A.Entry_Time,120), A.NoRequest,
				IsApproved, UserApproved_ID = ISNULL(A.ApprovedBy,''), UserApproved_Name=ISNULL(LTRIM(RTRIM(D.Nm_Slsman)),''), UserApproved_Email=ISNULL(LTRIM(RTRIM(D.Email)),''), ApprovedDate=CONVERT(VARCHAR(MAX),A.ApprovedDate,120),
				IsRejected=ISNULL(A.IsRejected,0), UserRejected_ID = ISNULL(A.RejectedBy,''), UserRejected_Name=ISNULL(LTRIM(RTRIM(E.Nm_Slsman)),''), UserRejected_Email=ISNULL(LTRIM(RTRIM(E.Email)),''), RejectedDate=CONVERT(VARCHAR(MAX),A.RejectedDate,120),
				IsDeleted=ISNULL(A.IsDeleted,0),UserDelete_ID = ISNULL(A.DeletedBy,''), UserDelete_Name=ISNULL(LTRIM(RTRIM(F.Nm_Slsman)),''), UserDelete_Email=ISNULL(LTRIM(RTRIM(F.Email)),''), DeletedDate=CONVERT(VARCHAR(MAX),A.DeletedDate,120),
				User_Level=C.Level_Salesman, UserApproved_Level=D.Level_Salesman, UserRejected_Level=E.Level_Salesman, UserDelete_Level=F.Level_Salesman, EmailedDate=CONVERT(VARCHAR(MAX),A.EmailedDate,120),
				kd_lokasi=isnull(A.kd_lokasi,'')
				FROM Mst_TargetSlsman_Log A WITH(NOLOCK) 
				LEFT JOIN TblMsSalesman B WITH(NOLOCK) ON A.Kd_Slsman = B.Kd_Slsman
				LEFT JOIN TblMsSalesman C WITH(NOLOCK) ON A.User_Name = C.Kd_Slsman
				LEFT JOIN TblMsSalesman D WITH(NOLOCK) ON A.ApprovedBy = D.Kd_Slsman
				LEFT JOIN TblMsSalesman E WITH(NOLOCK) ON A.RejectedBy = E.Kd_Slsman
				LEFT JOIN TblMsSalesman F WITH(NOLOCK) ON A.DeletedBy = F.Kd_Slsman
				WHERE REPLACE(REPLACE(A.NoRequest,'-',''),'_','') ='".$params["norequest"]."'
				AND A.IsApproved = 0
				AND ISNULL(A.IsRejected,0) = 0 
				AND ISNULL(A.IsDeleted,0) = 0
				AND ISNULL(A.IsEmailed,0) in (0,1)
				AND ISNULL(A.IsEmailedPembatalan,0) = 0
				AND A.EmailedDate IS NOT NULL
				AND A.Kode_Target NOT IN (SELECT Kode_Target FROM Mst_TargetSlsman WITH(NOLOCK))				
				AND B.Level_Salesman IN ('97','98')";					
			}									
		}	
		
		// die($str);

		$query = $this->bkt->query($str);

		if( ($errors = sqlsrv_errors() ) != null) {
			$ERR_MSG = "";
	        foreach( $errors as $error ) {
	        	$ERR_CODE = $error["code"];
			    $ERR_MSG.= "code: ".$ERR_CODE."<br />";
	            $ERR_MSG.= "message: ".$error[ 'message']."<br />";
	        }
	        return ("ERROR: ".$ERR_MSG);
	    }
		
		if ($query->num_rows()==0)	 {
			//die($str);
			return ("ERROR: Data Tidak Ditemukan");
		} else {
		
			foreach ($query->result_array() as $row)
			{					
				$hasil[] = ['tipe' => $row['tipe'],
					      'kode_target' => $row['kode_target'],
						  'Tgl_Awal' => $row['Tgl_Awal'],
						  'Tgl_Akhir' => $row['Tgl_Akhir'],	
						  'MonthYear' => $row['MonthYear'],
						  'Training' => $row['Training'],
						  'Kd_Slsman' => $row['Kd_Slsman'],
						  'Nm_Slsman' => $row['Nm_Slsman'],	
						  'Total_Target' => $row['Total_Target'],	
						  'User_ID' => $row['User_ID'],				
						  'User_Name' => $row['User_Name'],
						  'User_Email' => $row['User_Email'],
						  'Entry_Time' => $row['Entry_Time'],
						  'NoRequest' => $row['NoRequest'],

						  'IsApproved' => $row['IsApproved'],
						  'UserApproved_ID' => $row['UserApproved_ID'],
						  'UserApproved_Name' => $row['UserApproved_Name'],
						  'UserApproved_Email' => $row['UserApproved_Email'],
						  'ApprovedDate' => $row['ApprovedDate'],

						  'IsRejected' => $row['IsRejected'],
						  'UserRejected_ID' => $row['UserRejected_ID'],
						  'UserRejected_Name' => $row['UserRejected_Name'],
						  'UserRejected_Email' => $row['UserRejected_Email'],
						  'RejectedDate' => $row['RejectedDate'],	

						  'IsDeleted' => $row['IsDeleted'],
						  'UserDelete_ID' => $row['UserDelete_ID'],
						  'UserDelete_Name' => $row['UserDelete_Name'],
						  'UserDelete_Email' => $row['UserDelete_Email'],
						  'DeletedDate' => $row['DeletedDate'],
						
						  'User_Level' => $row['User_Level'],
						  'UserApproved_Level' => $row['UserApproved_Level'],
						  'UserRejected_Level' => $row['UserRejected_Level'],
						  'UserDelete_Level' => $row['UserDelete_Level'],
						  
						  'EmailedDate' => $row['EmailedDate'],
						  'kd_lokasi' => $row['kd_lokasi']
						];	
			}

			return json_encode($hasil);	
		}
	}	
	
	function GetTargetDTLog_ByNoRequest2($norequest="",$lokasiAPIdanDB, $configDB) 
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);

		$hasil =array();
		
		$str = "SELECT A.ID_GroupDivisi, Divisi=RTRIM(B.nama_subgroupdivisi), 
				A.Kategori_Insentif, Total_Target=CONVERT(varchar, CAST(A.Total_Target AS money), 1) 
				FROM Mst_TargetSlsmanDivisi_Log A WITH(NOLOCK) 
				Left JOIN 
				(SELECT DISTINCT ID_GroupDivisi,ID_SubGroupDivisi,nama_groupdivisi, nama_subgroupdivisi 
				FROM dbo.Mst_TargetSlsman_GroupDivisi WITH(NOLOCK)
				GROUP BY ID_GroupDivisi,ID_SubGroupDivisi,nama_groupdivisi, nama_subgroupdivisi)
				B ON A.ID_groupdivisi = b.ID_GroupDivisi+b.ID_SubGroupDivisi
				WHERE A.NoRequest ='".$norequest."'
				ORDER BY A.ID_GroupDivisi, B.nama_subgroupdivisi, A.Kategori_Insentif, A.Total_Target";								
							
		if ($lokasiAPIdanDB == 'BEDA')
		{
			$query = $this->bkt->query($str);
		}
		else {
			$query = $this->bkt->query($str);
		}			

		foreach ($query->result_array() as $row)
		{					
			$hasil[] = ['ID_GroupDivisi' => $row['ID_GroupDivisi'],
					  'Divisi' => $row['Divisi'],
					  'Kategori_Insentif' => $row['Kategori_Insentif'],	
					  'Total_Target' => $row['Total_Target']];	
		}
		return $hasil;	
	}

	function FlagSudahEmail_ByEntryTime2($params, $configDB)
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);

		$DateStamp = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));

		if ($params["parammode"]=="NOREQUEST"){
			if ($params["mode"]=='CANCELREQ')
			{
				$qry = "Update Mst_TargetSlsman_Log 
						Set IsEmailedPembatalan=1,
						EmailedPembatalanDate='".$DateStamp."'
						WHERE REPLACE(REPLACE(NoRequest,'-',''),'_','') = '".$params["norequest"]."'";
				$SQL1 = $this->bkt->query($qry);

				$qry = "Update Mst_TargetSlsmanDivisi_Log 
						Set IsEmailedPembatalan=1,
						EmailedPembatalanDate='".$DateStamp."'
						WHERE REPLACE(REPLACE(NoRequest,'-',''),'_','') = '".$params["norequest"]."'";
				$SQL2 = $this->bkt->query($qry);
			} else if ($params["mode"]=='HAPUS')
			{
				$qry = "Update Mst_TargetSlsman_Log 
						Set IsEmailedPembatalan=1,
						EmailedPembatalanDate='".$DateStamp."'
						WHERE REPLACE(REPLACE(NoRequest,'-',''),'_','') = '".$params["norequest"]."'";
				$SQL1 = $this->bkt->query($qry);

				$qry = "Update Mst_TargetSlsmanDivisi_Log 
						Set IsEmailedPembatalan=1,
						EmailedPembatalanDate='".$DateStamp."'
						WHERE REPLACE(REPLACE(NoRequest,'-',''),'_','') = '".$params["norequest"]."'";
				$SQL2 = $this->bkt->query($qry);
			}
			else
			{
				$qry = "Update Mst_TargetSlsman_Log 
						Set IsEmailed=1,
						EmailedDate='".$DateStamp."'
						WHERE REPLACE(REPLACE(NoRequest,'-',''),'_','') = '".$params["norequest"]."'";
				$SQL1 = $this->bkt->query($qry);

				$qry = "Update Mst_TargetSlsmanDivisi_Log 
						Set IsEmailed=1,
						EmailedDate='".$DateStamp."'
						WHERE REPLACE(REPLACE(NoRequest,'-',''),'_','') = '".$params["norequest"]."'";
				$SQL2 = $this->bkt->query($qry);
			}
		} else {
			if ($params["mode"]=='CANCELREQ')
			{
				$qry = "Update Mst_TargetSlsman_Log 
						Set IsEmailedPembatalan=1,
						EmailedPembatalanDate='".$DateStamp."'
						WHERE DeletedBy ='".$params["userid"]."'
						AND CONVERT(varchar(max),DeletedDate,120) ='".$params["tanggal"]."'";
				$SQL1 = $this->bkt->query($qry);

				$qry = "Update Mst_TargetSlsmanDivisi_Log 
						Set IsEmailedPembatalan=1,
						EmailedPembatalanDate='".$DateStamp."'
						WHERE DeletedBy ='".$params["userid"]."'
						AND CONVERT(varchar(max),DeletedDate,120) ='".$params["tanggal"]."'";
				$SQL2 = $this->bkt->query($qry);
			} else if ($params["mode"]=='HAPUS')
			{
				$qry = "Update Mst_TargetSlsman_Log 
						Set IsEmailedPembatalan=1,
						EmailedPembatalanDate='".$DateStamp."'
						WHERE DeletedBy ='".$params["userid"]."'
						AND CONVERT(varchar(max),DeletedDate,120) ='".$params["tanggal"]."'";
				$SQL1 = $this->bkt->query($qry);

				$qry = "Update Mst_TargetSlsmanDivisi_Log 
						Set IsEmailedPembatalan=1,
						EmailedPembatalanDate='".$DateStamp."'
						WHERE DeletedBy ='".$params["userid"]."'
						AND CONVERT(varchar(max),DeletedDate,120) ='".$params["tanggal"]."'";
				$SQL2 = $this->bkt->query($qry);
			}
			else
			{
				$qry = "Update Mst_TargetSlsman_Log 
						Set IsEmailed=1,
						EmailedDate='".$DateStamp."'
						WHERE User_Name ='".$params["userid"]."'
						AND CONVERT(varchar(max),entry_time,120) ='".$params["tanggal"]."'";
				$SQL1 = $this->bkt->query($qry);

				$qry = "Update Mst_TargetSlsmanDivisi_Log 
						Set IsEmailed=1,
						EmailedDate='".$DateStamp."'
						WHERE User_Name ='".$params["userid"]."'
						AND CONVERT(varchar(max),entry_time,120) ='".$params["tanggal"]."'";
				$SQL2 = $this->bkt->query($qry);
			}
		}
			

		$ERR_MSG = "";
		$ERR_CODE = 0;
		if( ($errors = sqlsrv_errors() ) != null) {

			foreach( $errors as $error ) {
				$ERR_CODE = $error["code"];
				//echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
				$ERR_MSG.= "code: ".$ERR_CODE."<br />";
				$ERR_MSG.= "message: ".$error[ 'message']."<br />";
				$ERR_MSG.= "sql: ".$qry."<br />";
			}

			return(array("result"=>"gagal", "err"=>$ERR_MSG, "errCode"=>$ERR_CODE));
		} else {
			return(array("result"=>"sukses", "err"=>$qry, "errCode"=>0));
		}	
	}	

	function UpdateKodeAtasan($params, $configDB)
	{
		$CI = &get_instance();
		$this->bkt = $this->load->database($configDB, TRUE);

		$str = "Update Mst_TargetSlsman_Log 
				Set Kd_Supervisor = '".$params["KodeAtasan"]."' 
				WHERE REPLACE(REPLACE(A.NoRequest,'-',''),'_','') ='".$params["norequest"]."'";

		$query = $this->bkt->query($str);
		
		// if ($params["lokasiAPIdanDB"] == 'BEDA')
		// {
		// 	$query = $this->bkt->query($str);
		// }
		// else {
		// 	$query = $this->bkt->query($str);
		// }	
		return true;
	}
	//Substitute bktAPI Until Here

}
?>
	    