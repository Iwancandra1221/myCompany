<?php
class SubDealerModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$CI = &get_instance();
	}
	
	function GetMDMarketSurveyUrl($zone=0, $branch=""){
		$query = "SELECT * 
				  FROM Cof_MarketSurvey 
				  WHERE Active=1";
		if ($zone==0) {
			$query .= " and branch_id = '".$branch."'";
		} else if ($zone==1) {
			$query .= " and branch_id = 'JKT'";
		} else {			
			// $query .= " and branch_id = 'SMG'";
			$query .= " and branch_id <> 'JKT'";
		}
		$query .= "ORDER BY LastUpdate";
		// die($query);
		$res = $this->db->query($query);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	// function GetMDMarketSurveyUrl($zone=0){
	// 	$query = "SELECT * FROM Cof_MarketSurvey WHERE Active=1";
	// 	if ($zone>0) {
	// 		if ($zone==1) {
	// 			$query .= " and branch_id in (JKT','CRB','BDG','YGY')";
	// 		} else if ($zone==2) {
	// 			$query .= " and branch_id in ('BLI','LOM','MKS,'BOG','SRD')";
	// 		} else if ($zone==3) {
	// 			$query .= " and branch_id in ('MLG','SMG','SRY','OTL')";
	// 		} else if ($zone==4) {
	// 			$query .= " and branch_id in ('JBI','PLB','LPG')";
	// 		} else if ($zone==5) {
	// 			$query .= " and branch_id in ('PKB','PDG','PTK')";
	// 		} else if ($zone==6) {
	// 			$query .= " and branch_id in ('KRW','BJM','MDN')";
	// 		} else if ($zone==7) {
	// 			$query .= " and branch_id in ('SMG')";
	// 		}
	// 	}
	// 	// die($query);
	// 	$res = $this->db->query($query);
	// 	if ($res->num_rows()>0) {
	// 		return $res->result();
	// 	} else {
	// 		return array();
	// 	}
	// }

	function RubahLastUpdate($data, $tgl, $row){
		$query = "UPDATE Cof_MarketSurvey 
				  SET LastUpdate='".date("Y-m-d H:i:s", strtotime($tgl))."',
				  	LastRowNumber = ".$row." 
				  WHERE branch_id='".$data->branch_id."' ";
		$res = $this->db->query($query);
		return true;
		// if ($res->num_rows()>0) {
		// 	return $res->result();
		// } else {
		// 	return array();
		// }
	}

	function GetListCabang(){
		$query = "SELECT branch_id, Cabang 
					FROM Cof_MarketSurvey ";
		if($_SESSION["logged_in"]['branch_id']!='JKT'){
			$query .= " WHERE BranchID='".$_SESSION["logged_in"]['branch_id']."' ";
		}
		$query .= " ORDER BY Cabang";
		$res = $this->db->query($query);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}
	
	function GetListProvinsi($cabang){
		$query = "SELECT DISTINCT Provinsi FROM Ms_SubDealer WHERE CabangMD LIKE'%".$cabang."%' ORDER BY Provinsi";
		$res = $this->db->query($query);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}
	
	function GetListKotamadya($cabang,$provinsi){	
		$query = "SELECT DISTINCT KotamadyaKabupaten FROM Ms_SubDealer WHERE CabangMD LIKE'%".$cabang."%' AND Provinsi LIKE'%".$provinsi."%' ORDER BY KotamadyaKabupaten";
		$res = $this->db->query($query);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}
	
	function GetListNamaMD($cabang,$provinsi,$kotamadya){
		$query = "
		SELECT DISTINCT NamaMD
		FROM (SELECT NamaMD, KotamadyaKabupaten, CabangMD, Provinsi
			  FROM TblMDMarketSurvey  WHERE CabangMD LIKE'%".$cabang."%'
			  UNION ALL
			  SELECT NamaMD, KotamadyaKabupaten, CabangMD, Provinsi
			  FROM Ms_SubDealer  WHERE CabangMD LIKE'%".$cabang."%'
			  ) AS Dt
		WHERE (CabangMD LIKE '%".$cabang."%') AND (Provinsi LIKE '%".$provinsi."%') AND (KotamadyaKabupaten LIKE '%".$kotamadya."%')
		ORDER BY NamaMD";
		
		$res = $this->db->query($query);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}
	
	function GetListData($filter,$cabang,$provinsi,$kotamadya,$namamd, $dp1, $dp2){
		$filter_tanggal = "";
		if($dp1!=''){
			$filter_tanggal = " AND (a.GFormTimeStamp BETWEEN CONVERT(DATE,'".$dp1."',101) AND CONVERT(DATE,'".$dp2."',101)) ";
		}
	
		if($filter=='kotamadya'){
		$query = "
			SELECT
				a.CabangMD,
				a.Provinsi,
				a.KotamadyaKabupaten,
				a.NamaMD,
				COUNT(a.SubDealerId) AS JumlahSubDealer,
				COUNT(b.DataSurveyId) AS JumlahMarketSurvey,
				MAX(b.GFormTimeStamp) as LastUpdate,
				'".$dp1."' AS TglAwal,
				'".$dp2."' AS TglAkhir
			FROM TblMDMarketSurvey AS b INNER JOIN
			Ms_SubDealer AS a ON b.SubDealerId = a.SubDealerId
			WHERE 1=1 
				".$filter_tanggal."
				AND [Tujuan_Penggunaan_Google_Form] in ('', 'MARKET SURVEY')
			GROUP BY a.CabangMD, a.Provinsi, a.KotamadyaKabupaten, a.NamaMD
			HAVING (a.NamaMD LIKE '%".$namamd."%')
				AND (a.KotamadyaKabupaten LIKE '%".$kotamadya."%')
				AND (a.Provinsi LIKE '%".$provinsi."%')
				AND (a.CabangMD LIKE '%".$cabang."%')
			ORDER BY a.CabangMD, a.Provinsi, a.KotamadyaKabupaten, a.NamaMD
			";
		}
		else{
			$query = "
			SELECT
				a.CabangMD,
				'ALL' as Provinsi,
				'ALL' as KotamadyaKabupaten,
				a.NamaMD,
				COUNT(a.SubDealerId) AS JumlahSubDealer,
				COUNT(b.DataSurveyId) AS JumlahMarketSurvey,
				MAX(b.GFormTimeStamp) as LastUpdate,
				'".$dp1."' AS TglAwal,
				'".$dp2."' AS TglAkhir
			FROM TblMDMarketSurvey AS b INNER JOIN
			Ms_SubDealer AS a ON b.SubDealerId = a.SubDealerId
			WHERE 1=1
				".$filter_tanggal."
				AND [Tujuan_Penggunaan_Google_Form] in ('', 'MARKET SURVEY')
			GROUP BY a.CabangMD, a.NamaMD
			HAVING (a.NamaMD LIKE '%".$namamd."%')
				AND (a.CabangMD LIKE '%".$cabang."%')
			ORDER BY a.CabangMD, a.NamaMD
			";
		}
			$res = $this->db->query($query);
			if ($res->num_rows()>0) {
				return $res->result();
			} else {
				return array();
			}
	}
	
	

	function GetListSubDealer($Cabang, $Provinsi, $Kotamadya, $NamaMD, $dp1, $dp2)
	{
		$filter_tanggal = "";
		if($dp1!=''){
			$filter_tanggal = " AND (a.GFormTimeStamp BETWEEN CONVERT(DATE,'".$dp1."',101) AND CONVERT(DATE,'".$dp2."',101)) ";
		}
		$Provinsi = ($Provinsi=='ALL') ? '%' : $Provinsi;
		$Kotamadya = ($Kotamadya=='ALL') ? '%' : $Kotamadya;
		$str = "SELECT
		SubDealerId,
		CabangMD,
		NamaMD,
		NamaToko,
		TitleToko,
		NamaPemilik,
		NamaPanggilan,
		CASE WHEN TerdaftarDiMishirin=1 THEN 'SUDAH' ELSE 'BELUM' END as TerdaftarDiMishirin,
		EmailLoginMishirin,
		NoHP,
		EmailToko,
		NoWhatsapp,
		NoTelpToko,
		AlamatToko,
		Kelurahan,
		Kecamatan,
		KodePos,
		Provinsi,
		KotamadyaKabupaten,
		GeoStamp,
		GeoCode,
		GeoAddress,
		GFormTimeStamp,
		FotoTampakDepan,
		CASE WHEN DATEDIFF(MONTH , GFormTimeStamp , GETDATE()) = 0 THEN 1 WHEN DATEDIFF(MONTH , GFormTimeStamp , GETDATE()) = 1 AND DAY(GETDATE()) <= 10 THEN 1 ELSE 0 END as AllowEdit
		FROM Ms_SubDealer AS a
		WHERE
			CabangMD='".$Cabang."' AND
			Provinsi LIKE '".$Provinsi."' AND
			KotamadyaKabupaten LIKE '".$Kotamadya."' AND
			ISNULL(NamaMD,'')='".$NamaMD."'
			".$filter_tanggal."
		ORDER BY NamaToko
		";
			
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			// die(json_encode($res->row()));
			return $res->result();
		} else {
			// die("null");
			return null;
		}
	}
	
	function GetListDataApproval($atasan_userid){
		$query = "
			SELECT Ms_SubDealer.*
			FROM tb_salesman INNER JOIN
			Ms_SubDealer ON tb_salesman.nm_slsman = Ms_SubDealer.NamaMD
			WHERE (Ms_SubDealer.NeedApproval = 1) AND (tb_salesman.atasan_userid = '".$atasan_userid."')
			ORDER BY Ms_SubDealer.NamaMD
			";
		$res = $this->db->query($query);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}
	

	function GetSubDealer($SubDealer, $cols) 
	{
		$NamaMD = "";
		$NamaToko = "";
		$Timestamp = "";
		for($i=0;$i<count($cols);$i++){
			if (strtoupper($cols[$i])=="NAMA MD" && $SubDealer[$i]!="") {
				$NamaMD = $SubDealer[$i];
			}
			if (strtoupper($cols[$i])=="NAMA TOKO" && $SubDealer[$i]!="") {
				$NamaToko = $SubDealer[$i];
			}
			if (strtoupper($cols[$i])=="TIMESTAMP" && $SubDealer[$i]!="") {
				$Timestamp = $SubDealer[$i];
			}
		}

		// $NamaToko = $SubDealer[array_search("Nama Toko", $cols)];
		// $Timestamp = $SubDealer[array_search("Timestamp", $cols)];
		// die($NamaToko);
		$str = "Select * From Ms_SubDealer Where isnull(NamaMD,'')='".$NamaMD."' and isnull(NamaToko,'')='".$NamaToko."' and GFormTimeStamp='".$Timestamp."'";
		// die($str."<br>");
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			// die(json_encode($res->row()));
			$SubDealers = $res->result();
			return $SubDealers[0];
		} else {
			// die("null");
			return null;
		}
	}
	
	function GetSubDealerEdit($SubDealerId) 
	{
		$str = "
		SELECT
		Ms_SubDealer.NamaMD,
		Ms_SubDealer.CabangMD,
		Ms_SubDealer.NamaToko,
		Ms_SubDealer.TitleToko,
		Ms_SubDealer.NamaPemilik,
		Ms_SubDealer.NamaPanggilan,
		CASE WHEN Ms_SubDealer.TerdaftarDiMishirin = 1 THEN 'SUDAH' ELSE 'BELUM' END AS TerdaftarDiMishirin,
		Ms_SubDealer.EmailLoginMishirin,
		Ms_SubDealer.EmailToko,
		Ms_SubDealer.NoHP,
		Ms_SubDealer.NoWhatsapp,
		Ms_SubDealer.NoTelpToko,
		Ms_SubDealer.AlamatToko,
		Ms_SubDealer.Kelurahan,
		Ms_SubDealer.Kecamatan, 
		Ms_SubDealer.KotamadyaKabupaten,
		Ms_SubDealer.Provinsi,
		Ms_SubDealer.KodePos,
		Ms_SubDealer.GFormTimeStamp,
		Ms_SubDealer.UpdatedJson,
		TblMDMarketSurvey.*
		FROM Ms_SubDealer
		INNER JOIN TblMDMarketSurvey ON Ms_SubDealer.SubDealerId = TblMDMarketSurvey.SubDealerId AND Ms_SubDealer.GFormTimeStamp = TblMDMarketSurvey.GFormTimeStamp
		WHERE Ms_SubDealer.SubDealerId='".$SubDealerId."'";
		// die($str);
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			// die(json_encode($res->row()));
			$SubDealers = $res->result();
			return $SubDealers[0];
		} else {
			// die("null");
			return null;
		}
	}

	function RequestUpdateSubDealer($SubDealerId, $Json) 
	{ 
		$str = "UPDATE Ms_SubDealer SET NeedApproval=1, UpdatedDate=GETDATE(), UpdatedJson='".$Json."'
		WHERE SubDealerId='".$SubDealerId."'";
		$res = $this->db->query($str);
		return $res;
	}

	function TambahSubDealer($SubDealer, $cols, $CreatedBy) 
	{
		$NamaMD = "";
		$CabangMD = "";
		$NamaToko = "";
		$GFormTimeStamp = "";

		for($i=0;$i<count($cols);$i++){
			if (strtoupper($cols[$i])=="CABANG" && $SubDealer[$i]!="") {
				$CabangMD = $SubDealer[$i];
			}
			if (strtoupper($cols[$i])=="NAMA MD" && $SubDealer[$i]!="") {
				$NamaMD = $SubDealer[$i];
			}
			if (strtoupper($cols[$i])=="NAMA TOKO" && $SubDealer[$i]!="") {
				$NamaToko = $SubDealer[$i];
			}
			if (strtoupper($cols[$i])=="TIMESTAMP" && $SubDealer[$i]!="") {
				$GFormTimeStamp = $SubDealer[$i];
			}
		}


		$this->db->set("NamaMD",$NamaMD);				
		$this->db->set("CabangMD",$CabangMD);
		$this->db->set("NamaToko",$NamaToko);
		$this->db->set("TitleToko",$SubDealer[array_search("Title Toko", $cols)]);
		$this->db->set("NamaPemilik",$SubDealer[array_search("Nama Pemilik Toko", $cols)]);
		$this->db->set("NamaPanggilan",$SubDealer[array_search("Nama Panggilan Pemilik Toko", $cols)]);
		$this->db->set("TerdaftarDiMishirin",(($SubDealer[array_search("Sudah terdaftar di Aplikasi Mishirin?", $cols)]=='SUDAH')?1:0));
		$this->db->set("EmailLoginMishirin",$SubDealer[array_search("Email Login Mishirin", $cols)]);
		$this->db->set("EmailToko",$SubDealer[array_search("Email Toko", $cols)]);
		$this->db->set("NoHP",$SubDealer[array_search("No HP", $cols)]);
		$this->db->set("NoWhatsapp",$SubDealer[array_search("No Whatsapp", $cols)]);
		$this->db->set("NoTelpToko",$SubDealer[array_search("No Telp Toko", $cols)]);
		$this->db->set("AlamatToko",$SubDealer[array_search("Alamat Toko", $cols)]);
		$this->db->set("Kelurahan",$SubDealer[array_search("Kelurahan", $cols)]);
		$this->db->set("Kecamatan",$SubDealer[array_search("Kecamatan", $cols)]);
		for($i=0;$i<count($cols);$i++){
			if (strtoupper($cols[$i])=="KOTAMADYA/KABUPATEN" && $SubDealer[$i]!="") {
				$this->db->set("KotamadyaKabupaten",$SubDealer[$i]);				
			}
		}
		for($i=0;$i<count($cols);$i++){
			if (strtoupper($cols[$i])=="PROVINSI" && $SubDealer[$i]!="") {
				$this->db->set("Provinsi",$SubDealer[$i]);				
			}
		}
		$this->db->set("KodePos",$SubDealer[array_search("Kode Pos", $cols)]);
		$this->db->set("FotoTampakDepan",$SubDealer[array_search("Foto Tampak DEPAN TOKO", $cols)]);
		// $this->db->set("GeoStamp",$SubDealer[array_search("GeoStamp", $cols)]);
		// $this->db->set("GeoCode",$SubDealer[array_search("GeoCode", $cols)]);
		// $this->db->set("GeoAddress",$SubDealer[array_search("GeoAddress", $cols)]);
		for($i=0;$i<count($cols);$i++){
			if (strtoupper($cols[$i])=="GEOSTAMP" && $SubDealer[$i]!="") {
				$this->db->set("GeoStamp",$SubDealer[$i]);				
			}
		}
		for($i=0;$i<count($cols);$i++){
			if (strtoupper($cols[$i])=="GEOCODE" && $SubDealer[$i]!="") {
				$this->db->set("GeoCode",$SubDealer[$i]);				
			}
		}
		for($i=0;$i<count($cols);$i++){
			if (strtoupper($cols[$i])=="GEOADDRESS" && $SubDealer[$i]!="") {
				$this->db->set("GeoAddress",$SubDealer[$i]);				
			}
		}
		$this->db->set("GFormTimeStamp",$GFormTimeStamp);
		$this->db->set("CreatedBy",$CreatedBy);
		$this->db->set("CreatedDate",date("Y-m-d H:i:s"));
		$this->db->insert("Ms_SubDealer");
		// $this->db->trans_complete();
		  
		$str = "Select * From Ms_SubDealer Where CabangMD='".$CabangMD."'
				and NamaMD='".$NamaMD."' and NamaToko='".$NamaToko."' 
				and GFormTimeStamp='".$GFormTimeStamp."' ";
		$res = $this->db->query($str);
		if ($res->num_rows()>0)
			return true;
		else
			return false;
	}

	function RubahSubDealer($SubDealerId, $SubDealer, $cols) 
	{
		
		// $this->db->trans_start();
		$this->db->set("CabangMD",$SubDealer[array_search("CabangMD", $cols)]);
		$this->db->set("NamaToko",$SubDealer[array_search("NamaToko", $cols)]);
		$this->db->set("TitleToko",$SubDealer[array_search("TitleToko", $cols)]);
		$this->db->set("NamaPemilik",$SubDealer[array_search("NamaPemilik", $cols)]);
		$this->db->set("NamaPanggilan",$SubDealer[array_search("NamaPanggilan", $cols)]);
		$this->db->set("TerdaftarDiMishirin",(($SubDealer[array_search("TerdaftarDiMishirin", $cols)]=='SUDAH')?1:0));
		$this->db->set("EmailLoginMishirin",$SubDealer[array_search("EmailLoginMishirin", $cols)]);
		$this->db->set("EmailToko",$SubDealer[array_search("EmailToko", $cols)]);
		$this->db->set("NoHP",$SubDealer[array_search("NoHP", $cols)]);
		$this->db->set("NoWhatsapp",$SubDealer[array_search("NoWhatsapp", $cols)]);
		$this->db->set("NoTelpToko",$SubDealer[array_search("NoTelpToko", $cols)]);
		$this->db->set("AlamatToko",$SubDealer[array_search("AlamatToko", $cols)]);
		$this->db->set("Kelurahan",$SubDealer[array_search("Kelurahan", $cols)]);
		$this->db->set("Kecamatan",$SubDealer[array_search("Kecamatan", $cols)]);
		$this->db->set("KotamadyaKabupaten",$SubDealer[array_search("KotamadyaKabupaten", $cols)]);
		$this->db->set("KodePos",$SubDealer[array_search("KodePos", $cols)]);
		
		$this->db->set("ModifiedBy",$_SESSION["logged_in"]["username"]);
		$this->db->set("ModifiedDate",date("Y-m-d H:i:s"));
		
		$this->db->where("SubDealerId", $SubDealerId);
		$this->db->update("Ms_SubDealer");
		// $this->db->trans_complete();
		return true;
	}

	function ApprovalUpdate($SubDealerId, $Approval) 
	{
		$this->db->trans_start();
		$this->db->set("NeedApproval",0);
		$this->db->set("Approval",$Approval);
		$this->db->set("ApprovalBy",$_SESSION["logged_in"]["username"]);
		$this->db->set("ApprovalDate",date("Y-m-d H:i:s"));
		$this->db->where("SubDealerId", $SubDealerId);
		$this->db->update("Ms_SubDealer");
		$this->db->trans_complete();
		return true;
	}

	function GetDataMarketSurvey($SubDealerId, $SubDealer, $cols)
	{
		//1 SubDealer Mungkin Saja diSurvey beberapa kali (dalam event berbeda).
		//Jadi untuk mencari Data sebaiknya includekan TimeStamp-nya
		$NamaMD = $SubDealer[array_search("Nama MD", $cols)];
		$Timestamp = $SubDealer[array_search("Timestamp", $cols)];
		$str = "Select * From TblMDMarketSurvey Where isnull(NamaMD,'')='".$NamaMD."' and GFormTimeStamp='".$Timestamp."' and SubDealerId=".$SubDealerId;
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			// die(json_encode($res->row()));
			return $res->row();
		} else {
			// die("null");
			return null;
		}
	}

	function exportDataSubDealer($cabang,$provinsi,$kotamadya,$namamd)
	{
		
		$str = " 
		SELECT
		a.CabangMD,
		a.NamaMD,
		a.NamaToko,
		a.TitleToko,
		a.NamaPemilik,
		a.NamaPanggilan,
		CASE WHEN a.TerdaftarDiMishirin=1 THEN 'Sudah' ELSE 'Belum' END as TerdaftarDiMishirin,
		a.EmailLoginMishirin,
		a.NoHP,
		a.EmailToko,
		a.NoWhatsapp,
		a.NoTelpToko,
		a.AlamatToko,
		a.Kelurahan,
		a.Kecamatan,
		a.KodePos,
		a.Provinsi,
		a.KotamadyaKabupaten,
		a.GeoStamp,
		a.GeoCode,
		a.GeoAddress,
		a.GFormTimeStamp,
		a.FotoTampakDepan
		FROM Ms_SubDealer AS a
		Where 
			a.CabangMD LIKE '%".$cabang."%' AND
			a.Provinsi LIKE '%".$provinsi."%' AND
			a.KotamadyaKabupaten LIKE '%".$kotamadya."%' AND
			a.NamaMD LIKE '%".$namamd."%'";
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return null;
		}
	}
	
	function exportDataMarketSurvey($cabang,$provinsi,$kotamadya,$namamd)
	{
		
		$str = " 
		SELECT
		b.*
		FROM TblMDMarketSurvey AS b 
		Where 
			b.CabangMD LIKE '%".$cabang."%' AND
			b.Provinsi LIKE '%".$provinsi."%' AND
			b.KotamadyaKabupaten LIKE '%".$kotamadya."%' AND
			b.NamaMD LIKE '%".$namamd."%' AND 
			b.Tujuan_Penggunaan_Google_Form in ('', 'MARKET SURVEY')";
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return null;
		}
	}

	function TambahDataMarketSurvey($SubDealerId, $SubDealer, $cols, $CreatedBy) 
	{
		$array_subdealer = array("NAMA MD", "CABANG", "NAMA TOKO", "TITLE TOKO", "NAMA PEMILIK TOKO", "NAMA PANGGILAN PEMILIK TOKO",
								"SUDAH TERDAFTAR DI APLIKASI MISHIRIN?", "EMAIL LOGIN MISHIRIN", "EMAIL TOKO", "NO HP", "NO WHATSAPP",
								"NO TELP TOKO", "ALAMAT TOKO", "KELURAHAN", "KECAMATAN", "KOTAMADYA/KABUPATEN", "PROVINSI", "KODE POS",
								"FOTO TAMPAK DEPAN TOKO", "TIMESTAMP");

		$idx_tujuanGForm = array_search("Tujuan Penggunaan Google Form", $cols);

		if (strtoupper($SubDealer[$idx_tujuanGForm])=="FOTO BUKTI SCAN QR CODE") {
			return true;
		}
	
		// $this->db->trans_start();
		$this->db->set("NamaMD",$SubDealer[array_search("Nama MD", $cols)]);
		$this->db->set("CabangMD",$SubDealer[array_search("Cabang", $cols)]);
		$this->db->set("SubDealerId",$SubDealerId);
		$this->db->set("NamaToko",$SubDealer[array_search("Nama Toko", $cols)]);
		for($i=0;$i<count($cols);$i++){
			if (strtoupper($cols[$i])=="KOTAMADYA/KABUPATEN" && $SubDealer[$i]!="") {
				$this->db->set("KotamadyaKabupaten",$SubDealer[$i]);				
			}
		}
		for($i=0;$i<count($cols);$i++){
			if (strtoupper($cols[$i])=="PROVINSI" && $SubDealer[$i]!="") {
				$this->db->set("Provinsi",$SubDealer[$i]);				
			}
		}
		$this->db->set("TglMarketSurvey", date("Y-m-d", strtotime($SubDealer[array_search("Timestamp", $cols)])));
		$this->db->set("GFormTimeStamp",$SubDealer[array_search("Timestamp", $cols)]);
		$this->db->set("CreatedBy",$CreatedBy);
		$this->db->set("CreatedDate",date("Y-m-d H:i:s"));
		$this->db->insert("TblMDMarketSurvey");
	
		for($i=0;$i<count($cols);$i++) {
			$col = $cols[$i];
			if ((in_array(strtoupper($col),$array_subdealer)==false) && ($col!="")) {
				$str = "SELECT dbo.ReplaceChars('".$col."','_') as ColName";
				$rsCol = $this->db->query($str);
				$namaCol = $rsCol->row()->ColName;

				$str = "Select * From sys.tables t inner join sys.columns c on t.object_id = c.object_id 
							where t.name = 'TblMDMarketSurvey' and c.name = '".$namaCol."' ";
				$cariCol = $this->db->query($str);
				if ($cariCol->num_rows()==0) {
					$str = "ALTER TABLE TblMDMarketSurvey ADD ".$namaCol." varchar(1000) Null";
					$addCol = $this->db->query($str);
				} 	

				$this->db->set($namaCol, $SubDealer[$i]);
				$this->db->where("SubDealerId", $SubDealerId);
				$this->db->update("TblMDMarketSurvey");
			}
		}

		// $this->db->trans_complete();
		return true;
	}

	function RubahDataMarketSurvey($SubDealerId, $SubDealer, $cols) 
	{
		$array_subdealer = array("NAMA MD", "CABANG", "NAMA TOKO", "TITLE TOKO", "NAMA PEMILIK TOKO", "NAMA PANGGILAN PEMILIK TOKO",
								"SUDAH TERDAFTAR DI APLIKASI MISHIRIN?", "EMAIL LOGIN MISHIRIN", "EMAIL TOKO", "NO HP", "NO WHATSAPP",
								"NO TELP TOKO", "ALAMAT TOKO", "KELURAHAN", "KECAMATAN", "KOTAMADYA/KABUPATEN", "PROVINSI", "KODE POS",
								"FOTO TAMPAK DEPAN TOKO", "TIMESTAMP");
		// $this->db->trans_start();
		$this->db->set("TglMarketSurvey", date("Y-m-d", strtotime($SubDealer[array_search("Timestamp", $cols)])));
		
		$this->db->set("NamaToko",$SubDealer[array_search("Nama Toko", $cols)]);
		
		$this->db->where("GFormTimeStamp",$SubDealer[array_search("Timestamp", $cols)]);
		$this->db->where("SubDealerId", $SubDealerId);
		$this->db->update("TblMDMarketSurvey");

		for($i=0;$i<count($cols);$i++) {
			$col = $cols[$i];
			if ((in_array(strtoupper($col),$array_subdealer)==false) && ($col!="")) {
				$str = "SELECT dbo.ReplaceChars('".$col."','_') as ColName";
				$rsCol = $this->db->query($str);
				$namaCol = $rsCol->row()->ColName;

				$str = "Select * From sys.tables t inner join sys.columns c on t.object_id = c.object_id 
							where t.name = 'TblMDMarketSurvey' and c.name = '".$namaCol."' ";
				$cariCol = $this->db->query($str);
				if ($cariCol->num_rows()==0) {
					$str = "ALTER TABLE TblMDMarketSurvey ADD ".$namaCol." varchar(1000) Null";
					$addCol = $this->db->query($str);
				} 	

				$this->db->set($namaCol, $SubDealer[$i]);
				$this->db->where("SubDealerId", $SubDealerId);
				$this->db->update("TblMDMarketSurvey");				
			}
		}

		// $this->db->trans_complete();
		return true;
	}

	function CheckDataExists($SubDealer, $cols, $TujuanGForm="")
	{
		//1 SubDealer Mungkin Saja diSurvey beberapa kali (dalam event berbeda).
		//Jadi untuk mencari Data sebaiknya includekan TimeStamp-nya
		$NamaMD = $SubDealer[array_search("Nama MD", $cols)];
		$Timestamp = $SubDealer[array_search("Timestamp", $cols)];
		$str = "Select * From TblMDMarketSurvey 
				Where isnull(NamaMD,'')='".$NamaMD."' 
				and GFormTimeStamp='".$Timestamp."' 
				and upper(Tujuan_Penggunaan_Google_Form)='".$TujuanGForm."'";

		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return true;
		} else {
			return false;
		}
	}

	function TambahDataOther($SubDealer, $cols, $CreatedBy) 
	{
		$array_subdealer = array("NAMA MD", "CABANG", "NAMA TOKO", "TITLE TOKO", "NAMA PEMILIK TOKO", "NAMA PANGGILAN PEMILIK TOKO",
								"SUDAH TERDAFTAR DI APLIKASI MISHIRIN?", "EMAIL LOGIN MISHIRIN", "EMAIL TOKO", "NO HP", "NO WHATSAPP",
								"NO TELP TOKO", "ALAMAT TOKO", "KELURAHAN", "KECAMATAN", "KOTAMADYA/KABUPATEN", "PROVINSI", "KODE POS",
								"TIMESTAMP", "TUJUAN PENGGUNAAN GOOGLE FORM");

		$idx_tujuanGForm = array_search("Tujuan Penggunaan Google Form", $cols);
		$TujuanGForm = strtoupper($SubDealer[$idx_tujuanGForm]); 

		$NamaMD = "";
		$CabangMD = "";
		$NamaToko = "";
		$GFormTimeStamp = "";

		for($i=0;$i<count($cols);$i++){
			if (strtoupper($cols[$i])=="CABANG" && $SubDealer[$i]!="") {
				$CabangMD = $SubDealer[$i];
			}
			if (strtoupper($cols[$i])=="NAMA MD" && $SubDealer[$i]!="") {
				$NamaMD = $SubDealer[$i];
			}
			if (strtoupper($cols[$i])=="NAMA TOKO" && $SubDealer[$i]!="") {
				$NamaToko = $SubDealer[$i];
			}
			if (strtoupper($cols[$i])=="TIMESTAMP" && $SubDealer[$i]!="") {
				$GFormTimeStamp = $SubDealer[$i];
			}
		}
		// $this->db->trans_start();
		$this->db->set("NamaMD",$NamaMD);
		$this->db->set("CabangMD",$CabangMD);
		$this->db->set("NamaToko",$NamaToko);
		$this->db->set("SubDealerId","");
		$this->db->set("KotamadyaKabupaten","");				
		$this->db->set("Provinsi","");				
		$this->db->set("Tujuan_Penggunaan_Google_Form", $TujuanGForm);
		$this->db->set("TglMarketSurvey", date("Y-m-d", strtotime($GFormTimeStamp)));
		$this->db->set("GFormTimeStamp",$GFormTimeStamp);
		$this->db->set("CreatedBy",$CreatedBy);
		$this->db->set("CreatedDate",date("Y-m-d H:i:s"));
		$this->db->insert("TblMDMarketSurvey");
	
		for($i=0;$i<count($cols);$i++) {
			$col = $cols[$i];
			if ((in_array(strtoupper($col),$array_subdealer)==false) && ($col!="") && ($SubDealer[$i]!="")) {
				$str = "SELECT dbo.ReplaceChars('".$col."','_') as ColName";
				$rsCol = $this->db->query($str);
				if ($rsCol->num_rows()>0) {
					$namaCol = $rsCol->row()->ColName;

					$str = "Select * From sys.tables t inner join sys.columns c on t.object_id = c.object_id 
								where t.name = 'TblMDMarketSurvey' and c.name = '".$namaCol."' ";
					$cariCol = $this->db->query($str);
					if ($cariCol->num_rows()==0) {
						$str = "ALTER TABLE TblMDMarketSurvey ADD ".$namaCol." varchar(1000) Null";
						$addCol = $this->db->query($str);
					} 	

					$this->db->set($namaCol, $SubDealer[$i]);
					$this->db->where("NamaMD",$NamaMD);
					$this->db->where("CabangMD",$CabangMD);
					$this->db->where("Tujuan_Penggunaan_Google_Form", $TujuanGForm);
					$this->db->where("GFormTimeStamp",$SubDealer[array_search("Timestamp", $cols)]);
					$this->db->update("TblMDMarketSurvey");
				}
			}
		}

		// $this->db->trans_complete();
		return true;
	}

	function RubahDataOther($SubDealer, $cols, $CreatedBy) 
	{
		$array_subdealer = array("NAMA MD", "CABANG", "NAMA TOKO", "TITLE TOKO", "NAMA PEMILIK TOKO", "NAMA PANGGILAN PEMILIK TOKO",
								"SUDAH TERDAFTAR DI APLIKASI MISHIRIN?", "EMAIL LOGIN MISHIRIN", "EMAIL TOKO", "NO HP", "NO WHATSAPP",
								"NO TELP TOKO", "ALAMAT TOKO", "KELURAHAN", "KECAMATAN", "KOTAMADYA/KABUPATEN", "PROVINSI", "KODE POS",
								"TIMESTAMP", "TUJUAN PENGGUNAAN GOOGLE FORM");

		$idx_tujuanGForm = array_search("Tujuan Penggunaan Google Form", $cols);
		$TujuanGForm = strtoupper($SubDealer[$idx_tujuanGForm]); 
		$NamaMD = strtoupper($SubDealer[array_search("Nama MD", $cols)]);
		$CabangMD = strtoupper($SubDealer[array_search("Cabang", $cols)]);
		$Timestamp = $SubDealer[array_search("Timestamp", $cols)];

		// $this->db->trans_start();

		for($i=0;$i<count($cols);$i++){
			if (strtoupper($cols[$i])=="NAMA TOKO" && $SubDealer[$i]!="") {
				echo("Update NamaToko <b>".$SubDealer[$i]."</b><br>");
				$this->db->where("NamaMD",$NamaMD);
				$this->db->where("CabangMD",$CabangMD);
				$this->db->where("GFormTimeStamp",$Timestamp);
				$this->db->set("NamaToko",$SubDealer[$i]);				
				$this->db->update("TblMDMarketSurvey");
			}
		}
	
		for($i=0;$i<count($cols);$i++) {
			$col = $cols[$i];
			if ((in_array(strtoupper($col),$array_subdealer)==false) && ($col!="")) {
				$str = "SELECT dbo.ReplaceChars('".$col."','_') as ColName";
				$rsCol = $this->db->query($str);
				$namaCol = $rsCol->row()->ColName;

				$str = "Select * From sys.tables t inner join sys.columns c on t.object_id = c.object_id 
							where t.name = 'TblMDMarketSurvey' and c.name = '".$namaCol."' ";
				$cariCol = $this->db->query($str);
				if ($cariCol->num_rows()==0) {
					$str = "ALTER TABLE TblMDMarketSurvey ADD ".$namaCol." varchar(1000) Null";
					$addCol = $this->db->query($str);
				} 	

				$this->db->set($namaCol, $SubDealer[$i]);
				$this->db->where("NamaMD",$NamaMD);
				$this->db->where("CabangMD",$CabangMD);
				$this->db->where("Tujuan_Penggunaan_Google_Form", $TujuanGForm);
				$this->db->where("GFormTimeStamp",$Timestamp);
				$this->db->update("TblMDMarketSurvey");
			}
		}

		// $this->db->trans_complete();
		return true;
	}

	function GetListFotoDisplay($dp1,$dp2){
		$filter_tanggal = "";
		if($dp1!=''){
			$filter_tanggal = " AND (a.GFormTimeStamp BETWEEN CONVERT(DATE,'".$dp1."',101) AND CONVERT(DATE,'".$dp2."',101)) ";
		}
		$filter_branch = "";
		if ($_SESSION["branchID"]!="JKT") {
			$filter_branch =  " AND (a.CabangMD in (Select Cabang From Cof_MarketSurvey Where BranchID='".$_SESSION["branchID"]."')) ";
		}
	
		$query = "
		SELECT
			a.CabangMD,
			a.NamaMD,
			COUNT(a.DataSurveyId) AS JumlahFotoDisplay,
			CONVERT(VARCHAR(50),MAX(CAST(a.GFormTimeStamp as DATETIME)),113) as LastUpdate,
			'".$dp1."' AS TglAwal,
			'".$dp2."' AS TglAkhir
		FROM TblMDMarketSurvey AS a
		WHERE 1=1 ".$filter_tanggal.
			$filter_branch."
			AND Tujuan_Penggunaan_Google_Form in ('FOTO DISPLAY BARANG','FOTO DISPLAY PRODUK')
		GROUP BY a.CabangMD, a.NamaMD
		ORDER BY a.CabangMD, a.NamaMD
		";
		// die($query);

		$res = $this->db->query($query);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetFotoDisplay($Cabang, $NamaMD, $dp1, $dp2, $cols)
	{
		$filter_tanggal = "";
		if($dp1!=''){
			$filter_tanggal = " AND (a.GFormTimeStamp BETWEEN CONVERT(DATE,'".$dp1."',101) AND CONVERT(DATE,'".$dp2."',101)) ";
		}

		$strcol = "";
		foreach($cols as $c) {
			$strcol .= $c->COLNAME.",";
		}

		$str = "SELECT a.NamaMD, a.CabangMD, a.NamaToko, a.GeoCode, a.GFormTimeStamp, a.Brand_display_produk,
					".$strcol." a.Foto_Tampak_DEPAN_TOKO as FotoTampakDepan
				FROM TblMDMarketSurvey AS a
				WHERE Tujuan_Penggunaan_Google_Form in ('FOTO DISPLAY BARANG', 'FOTO DISPLAY PRODUK') AND 
					CabangMD='".$Cabang."' AND
					NamaMD='".$NamaMD."'
					".$filter_tanggal."
				ORDER BY NamaToko
		";
			
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			// die(json_encode($res->row()));
			return $res->result();
		} else {
			// die("null");
			return null;
		}
	}

	function GetColFotoDisplay()
	{
		$str = "Select c.name as COLNAME
				From sys.tables t inner join sys.columns c on t.object_id = c.object_id 
				where t.name='TblMDMarketSurvey'
				and upper(c.name) like 'FOTO_DISPLAY_%'";
			
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}	

	function GetListFotoScanQRCode($dp1,$dp2){
		$filter_tanggal = "";
		if($dp1!=''){
			$filter_tanggal = " AND (a.GFormTimeStamp BETWEEN CONVERT(DATE,'".$dp1."',101) AND CONVERT(DATE,'".$dp2."',101)) ";
		}
		$filter_branch = "";
		if ($_SESSION["branchID"]!="JKT") {
			$filter_branch =  " AND (a.CabangMD in (Select Cabang From Cof_MarketSurvey Where BranchID='".$_SESSION["branchID"]."')) ";
		}
	
		$query = "
		SELECT
			a.CabangMD,
			a.NamaMD,
			COUNT(a.DataSurveyId) AS JumlahFotoScan,
			CONVERT(VARCHAR(50),CAST(MAX(a.GFormTimeStamp) as DATETIME),113) as LastUpdate,
			'".$dp1."' AS TglAwal,
			'".$dp2."' AS TglAkhir
		FROM TblMDMarketSurvey AS a
		WHERE 1=1 ".$filter_tanggal.
			$filter_branch."
			AND GFormTimeStamp>='2022-01-01'
			AND upper(Tujuan_Penggunaan_Google_Form) in ('FOTO BUKTI SCAN QR CODE')
		GROUP BY a.CabangMD, a.NamaMD
		ORDER BY a.CabangMD, a.NamaMD
		";
		// die($query);

		$res = $this->db->query($query);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetFotoScanQRCode($Cabang, $NamaMD, $dp1, $dp2)
	{
		$filter_tanggal = "";
		if($dp1!=''){
			$filter_tanggal = " AND (a.GFormTimeStamp BETWEEN CONVERT(DATE,'".$dp1."',101) AND CONVERT(DATE,'".$dp2."',101)) ";
		}

		$str = "SELECT a.NamaMD, a.CabangMD, a.NamaToko, a.GeoCode, a.GFormTimeStamp, a.Foto_Bukti_Scan_QR_Code_pada_Aplikasi_MSR_Toko as FotoScan,
					a.Foto_Tampak_DEPAN_TOKO as FotoTampakDepan
				FROM TblMDMarketSurvey AS a
				WHERE upper(Tujuan_Penggunaan_Google_Form) in ('FOTO BUKTI SCAN QR CODE')  
					AND CabangMD='".$Cabang."' 
					AND NamaMD='".$NamaMD."'
					AND GFormTimeStamp>='2022-01-01'
					".$filter_tanggal."
				ORDER BY NamaToko
		";
			
		$res = $this->db->query($str);
		if ($res->num_rows()>0) {
			// die(json_encode($res->row()));
			return $res->result();
		} else {
			// die("null");
			return null;
		}
	}

}
?>
	    