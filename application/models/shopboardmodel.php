<?php
class shopboardmodel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function datatable_shopboard_per_toko($param)
	{
		//Kolom yg akan diselect (jika tidak ingin difilter, kolom ditaruh di ujung)
		$aColumns = array(
		"dt.supplier",
		"b.BranchName as cabang",
		"hd.nama_toko",
		"hd.alamat",
		"hd.kota",
		"dt.no_po",
		"CASE WHEN dt.merk1 IS NULL THEN '' ELSE dt.merk1 END +''+
		CASE WHEN dt.merk2 IS NULL THEN '' ELSE '<br>'+dt.merk2 END +''+
		CASE WHEN dt.merk3 IS NULL THEN '' ELSE '<br>'+dt.merk3 END +''+
		CASE WHEN dt.merk4 IS NULL THEN '' ELSE '<br>'+dt.merk4 END +''+
		CASE WHEN dt.merk5 IS NULL THEN '' ELSE '<br>'+dt.merk5 END as merk",
		"CASE WHEN dt.ukuran1 IS NULL THEN '' ELSE dt.ukuran1 END +''+
		CASE WHEN dt.ukuran2 IS NULL THEN '' ELSE '<br>'+dt.ukuran2 END +''+
		CASE WHEN dt.ukuran3 IS NULL THEN '' ELSE '<br>'+dt.ukuran3 END +''+
		CASE WHEN dt.ukuran4 IS NULL THEN '' ELSE '<br>'+dt.ukuran4 END +''+
		CASE WHEN dt.ukuran5 IS NULL THEN '' ELSE '<br>'+dt.ukuran5 END as ukuran",
		"dt.periode_end",
		"dt.id_reklame",
		"dt.id",
		"dt.new",
		"dt.status",
		"dt.approval_status",
		"dt.final_status",
		"dt.rejected_note",
		);
		$sTable = "
		ms_reklameHD hd 
		INNER JOIN ms_reklameDT dt ON hd.id_reklame = dt.id_reklame
		INNER JOIN (
			SELECT id_reklame, max(periode_start) as periode_start
			FROM ms_reklameDT
			WHERE ISNULL(is_deleted,0)=0
			GROUP BY id_reklame
		) as x ON dt.id_reklame = x.id_reklame AND dt.periode_start = x.periode_start
		INNER JOIN ms_branch b ON hd.branchcode = b.BranchCode
		
		";
		
		// $sWhere = " hd.is_active=".$param['is_active']." AND ((dt.new=1 AND ISNULL(dt.approval_status,'')='') OR (dt.final_status='OK' AND dt.approval_status='OK')) ";
		$sWhere = " hd.is_active=".$param['is_active']." ";
		if($param['all_data']==1 || $param['is_active']==0){
		}
		else{
			// $sWhere .= " AND ( (dt.status IN('NEW','ON PROCESS')) OR ( " ;
			$sWhere .= " AND dt.periode_end>='".date('Y-m-d', strtotime($param['periode_start']))."' AND dt.periode_end<='".date('Y-m-d', strtotime($param['periode_end']))."'";
			// $sWhere .= " )) ";
		}
		if($param['cabang']!=''){
			$sWhere .= " AND hd.branchcode='".$param['cabang']."' ";
		}
		$query  = DatatableQuery($param, $sTable, $aColumns, $sWhere, $no=0);
		// echo $query['sQueryFiltered']; die;
		$res = $this->db->query($query['sQueryFiltered']);
		$iFilteredTotal = $res->num_rows();
			
		$data = array();
		if ($iFilteredTotal>0){
			foreach($res->result_array() as $r){
				$row = array();
				$status = '';
				if($r['approval_status']=='REJECTED'){
					$status = '<span class="fs-1 px5" style="background:red;color:#fff"> '.$r['approval_status'].' </span><br><small class="merah"><em>'.$r['rejected_note'].'</em></small>';
				}
				if($r['status']=='CANCELLED'){
					$status = '<span class="fs-1 px5" style="background:red;color:#fff"> '.$r['status'].' </span>';
				}
				$new = '';
				if($r['new']==1 && $r['approval_status']==''){
					$new = '<span class="fs-1 px5" style="background:red;color:#fff"> new </span>';
				}
				$row[]=$r['supplier'];
				$row[]=$r['cabang'];
				$row[]=$r['nama_toko'].'<br>'.$new;
				$row[]=$r['alamat'];
				$row[]=$r['kota'];
				$row[]=$r['no_po'].'<br>'.$status;
				$row[]=$r['merk'];
				$row[]=$r['ukuran'];
				$row[]=date('d-M-Y',strtotime($r['periode_end']));
				
				$row[]='<button type="button" class="btn btn-light p5" onclick="javascript:view_data('.$r['id_reklame'].')"><i class="glyphicon glyphicon-search fs-4 color-primary"></i></button>';
						
				// if($r['status']=='OK' || $r['status']=='NEW' || $r['status']=='REJECTED' || $r['status']=='CANCELLED'){
					// $row[]='<center><input type="checkbox" name="id[]" value="'.$r['id'].'" class="chk_pilih"></center>';
				// }
				// else{
					// $row[]='<center><i class="glyphicon glyphicon-open-file fs-6" title="Dalam Proses PO"></i></center>';
				// }	
				// if(($r['new']==1 && $r['approval_status']!='OK') || ($r['new']==0 && $r['approval_status']!='OK')){
				if(($r['new']==0 && $r['approval_status']=='OK') || ($r['new']==1 && $r['approval_status']=='') || ($r['approval_status']=='REJECTED')){
					$row[]='<center><input type="checkbox" name="id[]" value="'.$r['id'].'" class="chk_pilih"></center>';
				}
				else{
					$row[]='<center><small>Dalam Proses PO</small></center>';
				}
				
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
	
	function datatable_shopboard($param)
	{
		//Kolom yg akan diselect (jika tidak ingin difilter, kolom ditaruh di ujung)
		$aColumns = array(
		"dt.supplier",
		"b.BranchName as cabang",
		"hd.wilayah",
		"hd.nama_toko",
		"hd.alamat",
		"hd.kota",
		"dt.no_po",
		"CASE WHEN dt.merk1 IS NULL THEN '' ELSE dt.merk1 END +''+
		CASE WHEN dt.merk2 IS NULL THEN '' ELSE '<br>'+dt.merk2 END +''+
		CASE WHEN dt.merk3 IS NULL THEN '' ELSE '<br>'+dt.merk3 END +''+
		CASE WHEN dt.merk4 IS NULL THEN '' ELSE '<br>'+dt.merk4 END +''+
		CASE WHEN dt.merk5 IS NULL THEN '' ELSE '<br>'+dt.merk5 END as merk",
		"CASE WHEN dt.ukuran1 IS NULL THEN '' ELSE dt.ukuran1 END +''+
		CASE WHEN dt.ukuran2 IS NULL THEN '' ELSE '<br>'+dt.ukuran2 END +''+
		CASE WHEN dt.ukuran3 IS NULL THEN '' ELSE '<br>'+dt.ukuran3 END +''+
		CASE WHEN dt.ukuran4 IS NULL THEN '' ELSE '<br>'+dt.ukuran4 END +''+
		CASE WHEN dt.ukuran5 IS NULL THEN '' ELSE '<br>'+dt.ukuran5 END as ukuran",
		"dt.periode_end",
		"dt.id_reklame",
		"dt.id",
		"dt.new",
		"dt.status",
		"dt.approval_status",
		"dt.final_status",
		"dt.rejected_note",
		"x.periode_start",
		
		);
		$sTable = "
		ms_reklameHD hd 
		INNER JOIN ms_reklameDT dt ON hd.id_reklame = dt.id_reklame
		INNER JOIN ms_branch b ON hd.branchcode = b.BranchCode
		LEFT JOIN (
			SELECT id_reklame, max(periode_start) as periode_start
			FROM ms_reklameDT
			WHERE ISNULL(is_deleted,0)=0
			GROUP BY id_reklame
		) as x ON dt.id_reklame = x.id_reklame AND dt.periode_start = x.periode_start
		
		";
		
		// $sWhere = " hd.is_active=".$param['is_active']." AND ((dt.new=1 AND ISNULL(dt.approval_status,'')='') OR (dt.final_status='OK' AND dt.approval_status='OK')) ";
		$sWhere = " hd.is_active=".$param['is_active']." ";
		if($param['all_data']==1 || $param['is_active']==0){
		}
		else{
			// $sWhere .= " AND ( (dt.status IN('NEW','ON PROCESS')) OR ( " ;
			$sWhere .= " AND dt.periode_end>='".date('Y-m-d', strtotime($param['periode_start']))."' AND dt.periode_end<='".date('Y-m-d', strtotime($param['periode_end']))."'";
			// $sWhere .= " )) ";
		}
		if($param['cabang']!=''){
			$sWhere .= " AND hd.branchcode='".$param['cabang']."' ";
		}
		$query  = DatatableQuery($param, $sTable, $aColumns, $sWhere, $no=0);
		// echo $query['sQueryFiltered']; die;
		$res = $this->db->query($query['sQueryFiltered']);
		$iFilteredTotal = $res->num_rows();
			
		$data = array();
		if ($iFilteredTotal>0){
			foreach($res->result_array() as $r){
				$row = array();
				$status = '';
				if($r['approval_status']=='REJECTED'){
					$status = '<span class="fs-1 px5" style="background:red;color:#fff"> '.$r['approval_status'].' </span><br><small class="merah"><em>'.$r['rejected_note'].'</em></small>';
				}
				if($r['status']=='CANCELLED'){
					$status = '<span class="fs-1 px5" style="background:red;color:#fff"> '.$r['status'].' </span>';
				}
				$new = '';
				if($r['new']==1 && $r['approval_status']==''){
					$new = '<span class="fs-1 px5" style="background:red;color:#fff"> new </span>';
				}
				$row[]=$r['supplier'];
				$row[]=$r['cabang'];
				$row[]=$r['wilayah'];
				$row[]=$r['nama_toko'].'<br>'.$new;
				$row[]=$r['alamat'];
				$row[]=$r['kota'];
				$row[]=$r['no_po'].'<br>'.$status;
				$row[]=$r['merk'];
				$row[]=$r['ukuran'];
				$row[]=date('d-M-Y',strtotime($r['periode_end']));
				
				$row[]='<button type="button" class="btn btn-light p5" onclick="javascript:view_data('.$r['id_reklame'].')"><i class="glyphicon glyphicon-search fs-4 color-primary"></i></button>';
						
				// if($r['status']=='OK' || $r['status']=='NEW' || $r['status']=='REJECTED' || $r['status']=='CANCELLED'){
					// $row[]='<center><input type="checkbox" name="id[]" value="'.$r['id'].'" class="chk_pilih"></center>';
				// }
				// else{
					// $row[]='<center><i class="glyphicon glyphicon-open-file fs-6" title="Dalam Proses PO"></i></center>';
				// }	
				// if(($r['new']==1 && $r['approval_status']!='OK') || ($r['new']==0 && $r['approval_status']!='OK')){
				if(($r['new']==0 && $r['approval_status']=='OK') || ($r['new']==1 && $r['approval_status']=='') || ($r['approval_status']=='REJECTED')){
					if($r['periode_start']==''){
						$row[]='<center><input type="checkbox" disabled></center>';
					}
					else{
						$row[]='<center><input type="checkbox" name="id[]" value="'.$r['id'].'" class="chk_pilih"></center>';
					}
				}
				else{
					$row[]='<center><small>Dalam Proses PO</small></center>';
				}
				
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
	
	function datatable_shopboard_approval($param)
	{
		//Kolom yg akan diselect (jika tidak ingin difilter, kolom ditaruh di ujung)
		$aColumns = array(
		"dt.supplier",
		"b.BranchName as cabang",
		"hd.wilayah",
		"hd.nama_toko",
		"hd.alamat",
		"hd.kota",
		"dt.no_po",
		"CASE WHEN dt.merk1 IS NULL THEN '' ELSE dt.merk1 END +''+
		CASE WHEN dt.merk2 IS NULL THEN '' ELSE '<br>'+dt.merk2 END +''+
		CASE WHEN dt.merk3 IS NULL THEN '' ELSE '<br>'+dt.merk3 END +''+
		CASE WHEN dt.merk4 IS NULL THEN '' ELSE '<br>'+dt.merk4 END +''+
		CASE WHEN dt.merk5 IS NULL THEN '' ELSE '<br>'+dt.merk5 END as merk",
		"CASE WHEN dt.ukuran1 IS NULL THEN '' ELSE dt.ukuran1 END +''+
		CASE WHEN dt.ukuran2 IS NULL THEN '' ELSE '<br>'+dt.ukuran2 END +''+
		CASE WHEN dt.ukuran3 IS NULL THEN '' ELSE '<br>'+dt.ukuran3 END +''+
		CASE WHEN dt.ukuran4 IS NULL THEN '' ELSE '<br>'+dt.ukuran4 END +''+
		CASE WHEN dt.ukuran5 IS NULL THEN '' ELSE '<br>'+dt.ukuran5 END as ukuran",
		"dt.periode_end",
		"dt.id_reklame",
		"dt.id",
		"dt.new",
		"dt.status",
		"dt.approval_status",
		);
		$sTable = "
		ms_reklameHD hd 
		INNER JOIN ms_reklameDT dt ON hd.id_reklame = dt.id_reklame
		INNER JOIN (
			SELECT id_reklame, max(periode_start) as periode_start
			FROM ms_reklameDT
			WHERE ISNULL(is_deleted,0)=0
			GROUP BY id_reklame
		) as x ON dt.id_reklame = x.id_reklame AND dt.periode_start = x.periode_start
		INNER JOIN ms_branch b ON hd.branchcode = b.BranchCode
		
		";
		
		$sWhere = " hd.branchcode='".$param['cabang']."' ";
		if($param['status']=='APPROVED'){
			$sWhere .= " AND (ISNULL(approval_status,'')='".$param['status']."' OR ISNULL(approval_status,'')='OK') ";
		}
		else{
			$sWhere .= " AND ISNULL(approval_status,'')='".$param['status']."' ";
		}
		// $sWhere = " AND dt.status='".$param['status']."'";
		// if($param['all_data']==0){
			// $sWhere .= " AND dt.periode_end>='".date('Y-m-d', strtotime($param['periode_start']))."' AND dt.periode_end<='".date('Y-m-d', strtotime($param['periode_end']))."'";
		// }
		// if($param['cabang']!=''){
			// $sWhere .= " AND hd.branchcode='".$param['cabang']."' ";
		// }
		
		$query  = DatatableQuery($param, $sTable, $aColumns, $sWhere, $no=0);
		// echo json_encode($query); die;
		$res = $this->db->query($query['sQueryFiltered']);
		$iFilteredTotal = $res->num_rows();
			
		$data = array();
		if ($iFilteredTotal>0){
			foreach($res->result_array() as $r){
				$row = array();
				
				$row[]=$r['supplier'];
				$row[]=$r['cabang'];
				$row[]=$r['wilayah'];
				$row[]=$r['nama_toko'];
				$row[]=$r['alamat'];
				$row[]=$r['kota'];
				$row[]=$r['no_po'];
				$row[]=$r['merk'];
				$row[]=$r['ukuran'];
				$row[]=date('d-M-Y',strtotime($r['periode_end']));
				
				$row[]='<button type="button" class="btn btn-light p5" onclick="javascript:view_data('.$r['id_reklame'].')"><i class="glyphicon glyphicon-search fs-4 color-primary"></i></button>';
						
				if($r['approval_status']=='WAITING FOR APPROVAL'){
					$row[]='<center><input type="checkbox" name="id[]" value="'.$r['id'].'" class="chk_pilih"></center>';
				}
				else if($r['approval_status']=='REJECTED'){
					$row[]='<center><span class="fs-1 color-danger">Rejected <i class="glyphicon glyphicon-remove"></i></span></center>';
				}
				else{
					$row[]='<center><span class="fs-1 color-primary">Approved <i class="glyphicon glyphicon-ok"></i></span></center>';
				}
				
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
	
	function datatable_shopboard_approved($param)
	{
		//Kolom yg akan diselect (jika tidak ingin difilter, kolom ditaruh di ujung)
		$aColumns = array(
		"dt.supplier",
		"b.BranchName as cabang",
		"hd.wilayah",
		"hd.nama_toko",
		"hd.alamat",
		"hd.kota",
		"dt.no_po",
		"CASE WHEN dt.merk1 IS NULL THEN '' ELSE dt.merk1 END +''+
		CASE WHEN dt.merk2 IS NULL THEN '' ELSE '<br>'+dt.merk2 END +''+
		CASE WHEN dt.merk3 IS NULL THEN '' ELSE '<br>'+dt.merk3 END +''+
		CASE WHEN dt.merk4 IS NULL THEN '' ELSE '<br>'+dt.merk4 END +''+
		CASE WHEN dt.merk5 IS NULL THEN '' ELSE '<br>'+dt.merk5 END as merk",
		"CASE WHEN dt.ukuran1 IS NULL THEN '' ELSE dt.ukuran1 END +''+
		CASE WHEN dt.ukuran2 IS NULL THEN '' ELSE '<br>'+dt.ukuran2 END +''+
		CASE WHEN dt.ukuran3 IS NULL THEN '' ELSE '<br>'+dt.ukuran3 END +''+
		CASE WHEN dt.ukuran4 IS NULL THEN '' ELSE '<br>'+dt.ukuran4 END +''+
		CASE WHEN dt.ukuran5 IS NULL THEN '' ELSE '<br>'+dt.ukuran5 END as ukuran",
		"dt.periode_end",
		"dt.id_reklame",
		"dt.id",
		"dt.status",
		"dt.final_status",
		);
		$sTable = "
		ms_reklameHD hd 
		INNER JOIN ms_reklameDT dt ON hd.id_reklame = dt.id_reklame
		INNER JOIN (
			SELECT id_reklame, max(periode_start) as periode_start
			FROM ms_reklameDT
			WHERE ISNULL(is_deleted,0)=0
			GROUP BY id_reklame
		) as x ON dt.id_reklame = x.id_reklame AND dt.periode_start = x.periode_start
		INNER JOIN ms_branch b ON hd.branchcode = b.BranchCode
		";
		
		$sWhere = " dt.approval_status='APPROVED' ";
		// if($param['all_data']==0){
			// $sWhere .= " AND dt.periode_end>='".date('Y-m-d', strtotime($param['periode_start']))."' AND dt.periode_end<='".date('Y-m-d', strtotime($param['periode_end']))."'";
		// }
		// if($param['cabang']!=''){
			// $sWhere .= " AND hd.branchcode='".$param['cabang']."' ";
		// }
		
		$query  = DatatableQuery($param, $sTable, $aColumns, $sWhere, $no=0);
		// echo json_encode($query); die;
		$res = $this->db->query($query['sQueryFiltered']);
		$iFilteredTotal = $res->num_rows();
			
		$data = array();
		if ($iFilteredTotal>0){
			foreach($res->result_array() as $r){
				$row = array();
				$row[]=$r['supplier'];
				$row[]=$r['cabang'];
				$row[]=$r['wilayah'];
				$row[]=$r['nama_toko'];
				$row[]=$r['alamat'];
				$row[]=$r['kota'];
				$row[]=$r['no_po'];
				$row[]=$r['merk'];
				$row[]=$r['ukuran'];
				$row[]=date('d-M-Y',strtotime($r['periode_end']));
				
				$row[]='<button class="btn btn-primary-dark fs-1" onclick="javascript:update_po('.$r['id'].')">
						<i class="glyphicon glyphicon-paste fs-4"></i> Update PO
					</button>';
				
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
	
	function datatable_shopboard_final($param)
	{
		//Kolom yg akan diselect (jika tidak ingin difilter, kolom ditaruh di ujung)
		$aColumns = array(
		"dt.supplier",
		"b.BranchName as cabang",
		"hd.wilayah",
		"hd.nama_toko",
		"hd.alamat",
		"hd.kota",
		"dt.no_po",
		"CASE WHEN dt.merk1 IS NULL THEN '' ELSE dt.merk1 END +''+
		CASE WHEN dt.merk2 IS NULL THEN '' ELSE '<br>'+dt.merk2 END +''+
		CASE WHEN dt.merk3 IS NULL THEN '' ELSE '<br>'+dt.merk3 END +''+
		CASE WHEN dt.merk4 IS NULL THEN '' ELSE '<br>'+dt.merk4 END +''+
		CASE WHEN dt.merk5 IS NULL THEN '' ELSE '<br>'+dt.merk5 END as merk",
		"CASE WHEN dt.ukuran1 IS NULL THEN '' ELSE dt.ukuran1 END +''+
		CASE WHEN dt.ukuran2 IS NULL THEN '' ELSE '<br>'+dt.ukuran2 END +''+
		CASE WHEN dt.ukuran3 IS NULL THEN '' ELSE '<br>'+dt.ukuran3 END +''+
		CASE WHEN dt.ukuran4 IS NULL THEN '' ELSE '<br>'+dt.ukuran4 END +''+
		CASE WHEN dt.ukuran5 IS NULL THEN '' ELSE '<br>'+dt.ukuran5 END as ukuran",
		"dt.periode_start",
		"dt.periode_end",
		"dt.id_reklame",
		"dt.id",
		"dt.status",
		"dt.final_status",
		);
		$sTable = "
		ms_reklameHD hd 
		INNER JOIN ms_reklameDT dt ON hd.id_reklame = dt.id_reklame
		--INNER JOIN (
		--	SELECT id, max(periode_start) as periode_start
		--	FROM ms_reklameDT
		--	WHERE ISNULL(is_deleted,0)=0
		--	GROUP BY id
		--) as x ON dt.id = x.id
		INNER JOIN ms_branch b ON hd.branchcode = b.BranchCode
		
		";
		
		$sWhere = " dt.status='APPROVED' AND dt.approval_status='FINAL' ";
		// if($param['all_data']==0){
			// $sWhere .= " AND dt.periode_end>='".date('Y-m-d', strtotime($param['periode_start']))."' AND dt.periode_end<='".date('Y-m-d', strtotime($param['periode_end']))."'";
		// }
		// if($param['cabang']!=''){
			// $sWhere .= " AND hd.branchcode='".$param['cabang']."' ";
		// }
		
		$query  = DatatableQuery($param, $sTable, $aColumns, $sWhere, $no=0);
		// echo json_encode($query); die;
		$res = $this->db->query($query['sQueryFiltered']);
		$iFilteredTotal = $res->num_rows();
			
		$data = array();
		if ($iFilteredTotal>0){
			foreach($res->result_array() as $r){
				$row = array();
				$row[]=$r['supplier'];
				$row[]=$r['cabang'];
				$row[]=$r['wilayah'];
				$row[]=$r['nama_toko'];
				$row[]=$r['alamat'];
				$row[]=$r['kota'];
				$row[]=$r['no_po'];
				$row[]=$r['merk'];
				$row[]=$r['ukuran'];
				$row[]=date('d-M-Y',strtotime($r['periode_start'])).' sd '.date('d-M-Y',strtotime($r['periode_end']));
				
				$row[]='<button type="button" class="btn btn-light p5" onclick="javascript:final_po('.$r['id'].')"><i class="glyphicon glyphicon-search fs-4 color-primary"></i></button>';
						
						
				$row[]='<center><input type="checkbox" name="id[]" value="'.$r['id'].'" class="chk_pilih_final"></center>';
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
	
	function merk()
	{
		$qry = "
		SELECT DISTINCT merk as id, merk as text FROM (
		SELECT 'MIYAKO' as merk
		UNION ALL
		SELECT 'RINNAI' as merk
		UNION ALL
		SELECT 'SHIMIZU' as merk
		UNION ALL
		SELECT 'SERVICE CENTER' as merk
		UNION ALL
		SELECT DISTINCT merk1
		FROM ms_reklameDT
		UNION ALL
		SELECT DISTINCT merk2
		FROM ms_reklameDT
		UNION ALL
		SELECT DISTINCT merk3
		FROM ms_reklameDT
		UNION ALL
		SELECT DISTINCT merk4
		FROM ms_reklameDT
		UNION ALL
		SELECT DISTINCT merk5
		FROM ms_reklameDT
		) as d
		WHERE merk IS NOT NULL 
		ORDER BY merk
		";
		$res = $this->db->query($qry);
		if($res->num_rows()>0){
			return $res->result();
		}
		else return array();
	}
	
	function get_id_reklame(){
		$str = "SELECT CASE WHEN max(id_reklame) IS NULL THEN 1 ELSE max(id_reklame)+1 END AS id FROM ms_reklameHD";
		$res = $this->db->query($str);
		if($res->num_rows()>0){
			return $res->row()->id;
		}
	}
	
	function validasi_po($id_reklame, $tgl_po){
		$str = "SELECT max(periode_end) AS periode_end FROM ms_reklameDT WHERE final_status='OK' AND id_reklame='".$id_reklame."'";
		$res = $this->db->query($str);
		if($res->num_rows()>0){
			if(date('Y-m-d',strtotime($tgl_po)) > date('Y-m-d',strtotime($res->row()->periode_end))){
				return $tgl_po;
			}
			else return $res->row()->periode_end;
		}
		else return $tgl_po;
	}
	
	function detail($id)
	{
		$qry = "
		SELECT ms_reklameHD.*, ms_branch.BranchName as cabang
		FROM ms_reklameHD 
		INNER JOIN ms_branch ON ms_reklameHD.branchcode = ms_branch.BranchCode WHERE ms_reklameHD.id_reklame=".$id;
		$res = $this->db->query($qry);
		if($res->num_rows()>0){
			$hd = $res->row();
		}
		$qry = "SELECT * FROM ms_reklameDT WHERE ISNULL(is_deleted,0)=0 and id_reklame='".$id."' ORDER BY periode_end DESC";
		$res = $this->db->query($qry);
		if($res->num_rows()>0){
			$dt = $res->result();
		}
		
		echo json_encode(array('header'=>$hd,'detail'=>$dt));
	}
	
	function detail_po($id)
	{
		$qry = "
		SELECT hd.branchcode,hd.wilayah,hd.nama_toko,hd.alamat,hd.kota,hd.is_active,hd.catatan,dt.*
		FROM ms_reklameHD hd
		INNER JOIN ms_reklameDT dt ON hd.id_reklame = dt.id_reklame
		WHERE ISNULL(dt.is_deleted,0)=0 AND dt.id=".$id;
		$res = $this->db->query($qry);
		if($res->num_rows()>0){
			return json_encode($res->row());
		}
		else return json_encode(array());
	}
		
	function save($post)
	{
		$ERR_MSG='';
		$this->db->trans_start();
		switch ($post['act']) {
		case 'revisi_po':
		
			$this->db->where('id_reklame', $post['id_reklame']);
			$this->db->set('branchcode', $post['branchcode']);
			$this->db->set('wilayah', $post['wilayah']);
			$this->db->set('nama_toko', $post['nama_toko']);
			$this->db->set('alamat', $post['alamat']);
			$this->db->set('kota', $post['kota']);
			$this->db->set('modified_by',$_SESSION['logged_in']['username']);
			$this->db->set('modified_date',date('Y-m-d H:i:s'));
			$this->db->update('ms_reklameHD');
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
			
			$qry = "SELECT * FROM ms_reklameDT WHERE id=".$post['id_po'];
			$res = $this->db->query($qry);
			if($res->num_rows()>0){
				$row = $res->row_array();
				// if($post['new']=='1'){
					$this->db->where('id', $post['id_po']);
					$this->db->set('no_po', $post['no_po']);
					$this->db->set('periode_start', $post['periode_start']);
					$this->db->set('periode_end', $post['periode_end']);
					$this->db->set('supplier', $post['supplier']);
					
					for($i=0;$i<=4;$i++){
						$no = $i+1;
						$this->db->set('merk'.$no, (ISSET($post['merk'][$i])) ? $post['merk'][$i] : NULL );
						$this->db->set('ukuran'.$no, (ISSET($post['ukuran'][$i])) ? $post['ukuran'][$i] : NULL );
					}
					$this->db->set('modified_by',$_SESSION['logged_in']['username']);
					$this->db->set('modified_date',date('Y-m-d H:i:s'));
					$this->db->update('ms_reklameDT');
				// }
			}
			
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
		
		break;
		case 'update_po':		
			$this->db->where('id_reklame', $post['id_reklame']);
			$this->db->set('branchcode', $post['branchcode']);
			$this->db->set('wilayah', $post['wilayah']);
			$this->db->set('nama_toko', $post['nama_toko']);
			$this->db->set('alamat', $post['alamat']);
			$this->db->set('kota', $post['kota']);
			$this->db->set('modified_by',$_SESSION['logged_in']['username']);
			$this->db->set('modified_date',date('Y-m-d H:i:s'));
			$this->db->update('ms_reklameHD');
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
			
			$qry = "SELECT * FROM ms_reklameDT WHERE id=".$post['id_po'];
			$res = $this->db->query($qry);
			if($res->num_rows()>0){
				$row = $res->row_array();
				if($post['new']=='1'){
					$this->db->where('id', $post['id_po']);
					$this->db->set('no_po', $post['no_po']);
					$this->db->set('periode_start', $post['periode_start']);
					$this->db->set('periode_end', $post['periode_end']);
					$this->db->set('supplier', $post['supplier']);
					
					$this->db->set('status', 'APPROVED');
					$this->db->set('status_date', $row['approval_date']);
					$this->db->set('approved_by', $row['emailed_to']);
				
					$this->db->set('final_status', 'FINAL');
					$this->db->set('final_date', date('Y-m-d H:i:s'));
					
					$this->db->set('approval_status', 'FINAL');
					
					for($i=0;$i<=4;$i++){
						$no = $i+1;
						$this->db->set('merk'.$no, (ISSET($post['merk'][$i])) ? $post['merk'][$i] : NULL );
						$this->db->set('ukuran'.$no, (ISSET($post['ukuran'][$i])) ? $post['ukuran'][$i] : NULL );
					}
					$this->db->set('modified_by',$_SESSION['logged_in']['username']);
					$this->db->set('modified_date',date('Y-m-d H:i:s'));
					$this->db->update('ms_reklameDT');
				}
				else{
					// VALIDASI TGL PO TIDAK BOLEH KURANG DARI TGL PO SEBELUMNYA
					$validasi_po = $this->validasi_po($post['id_reklame'], $post['periode_start']);
					if($validasi_po != $post['periode_start']){
						$ERR_MSG.= "Periode Pajak Reklame harus di atas tanggal ".date('d-M-Y', strtotime($validasi_po));
					}
					else{
						//UPDATE PO LAMA
						$this->db->where('id', $post['id_po']);
						$this->db->set('approval_status', 'OK');
						$this->db->update('ms_reklameDT');
						
						//INSERT PO BARU
						$this->db->set('id_reklame', $post['id_reklame']);
						$this->db->set('new', 0);
						$this->db->set('no_po', $post['no_po']);
						$this->db->set('periode_start', $post['periode_start']);
						$this->db->set('periode_end', $post['periode_end']);
						$this->db->set('supplier', $post['supplier']);
						
						$this->db->set('status', 'APPROVED');
						$this->db->set('status_date', $row['approval_date']);
						$this->db->set('approved_by', $row['emailed_to']);
						
						$this->db->set('final_status', 'FINAL');
						$this->db->set('final_date', date('Y-m-d H:i:s'));
						
						$this->db->set('emailed_to', $row['emailed_to']);
						$this->db->set('emailed_date', $row['emailed_date']);
						$this->db->set('approval_status', 'FINAL');
						$this->db->set('approval_date', $row['approval_date']);
						
						for($i=0;$i<=4;$i++){
							$no = $i+1;
							$this->db->set('merk'.$no, (ISSET($post['merk'][$i])) ? $post['merk'][$i] : NULL );
							$this->db->set('ukuran'.$no, (ISSET($post['ukuran'][$i])) ? $post['ukuran'][$i] : NULL );
						}
						
						$this->db->set('created_by',$_SESSION['logged_in']['username']);
						$this->db->set('created_date',date('Y-m-d H:i:s'));
						$this->db->insert('ms_reklameDT');
					}
				}
			}
			
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
		
		break;
		case 'finalize':
		
		break;
		default: // toko baru
			$id_reklame = $this->get_id_reklame();
			$this->db->set('id_reklame', $id_reklame);
			$this->db->set('branchcode', $post['branchcode']);
			$this->db->set('wilayah', $post['wilayah']);
			$this->db->set('nama_toko', $post['nama_toko']);
			$this->db->set('alamat', $post['alamat']);
			$this->db->set('kota', $post['kota']);
			$this->db->set('created_by',$_SESSION['logged_in']['username']);
			$this->db->set('created_date',date('Y-m-d H:i:s'));
			$this->db->set('is_active',1);
			$this->db->insert('ms_reklameHD');
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
			
			$this->db->set('id_reklame', $id_reklame);
			$this->db->set('new', 1);
			$this->db->set('no_po', $post['no_po']);
			$this->db->set('periode_start', $post['periode_start']);
			$this->db->set('periode_end', $post['periode_end']);
			$this->db->set('supplier', $post['supplier']);
			$this->db->set('status', 'NEW');
			$this->db->set('status_date', date('Y-m-d H:i:s'));
			
			for($i=0;$i<=4;$i++){
				$no = $i+1;
				$this->db->set('merk'.$no, (ISSET($post['merk'][$i])) ? $post['merk'][$i] : NULL );
				$this->db->set('ukuran'.$no, (ISSET($post['ukuran'][$i])) ? $post['ukuran'][$i] : NULL );
			}
			
			$this->db->set('created_by',$_SESSION['logged_in']['username']);
			$this->db->set('created_date',date('Y-m-d H:i:s'));
			$this->db->insert('ms_reklameDT');
			
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
		}
		
		$this->db->trans_complete();
		if($ERR_MSG==''){
			return 'success';
		}
		else{
			return $ERR_MSG;
		}
	}
	
	function toko($post)
	{
		$ERR_MSG='';
		$this->db->trans_start();
		
		$this->db->where('id_reklame', $post['id_reklame']);
		$this->db->set('branchcode', $post['branchcode']);
		$this->db->set('wilayah', $post['wilayah']);
		$this->db->set('nama_toko', $post['nama_toko']);
		$this->db->set('alamat', $post['alamat']);
		$this->db->set('kota', $post['kota']);
		$this->db->set('modified_by',$_SESSION['logged_in']['username']);
		$this->db->set('modified_date', date('Y-m-d H:i:s'));
		$this->db->update('ms_reklameHD');
		
		if( ($errors = sqlsrv_errors() ) != null) {
			foreach( $errors as $error ) {
				$ERR_MSG.= $error[ 'message']."; ";
			}
		}
		if($ERR_MSG==''){
			$this->db->trans_complete();
			return 'success';
		}
		else{
			return $ERR_MSG;
		}
	}
	
	function pengajuan($post)
	{
		$ERR_MSG='';
		$this->db->trans_start();
		
		for($i=0;$i<count($_POST['id']);$i++){
			$this->db->where('id', $post['id'][$i]);
			$this->db->set('status', 'ON PROCESS');
			$this->db->set('status_date', date('Y-m-d H:i:s'));
			$this->db->update('ms_reklameDT');
			
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
		}
		if($ERR_MSG==''){
			$this->db->trans_complete();
			return 'success';
		}
		else{
			return $ERR_MSG;
		}
	}
	
	function finalize($post)
	{
		$ERR_MSG='';
		$this->db->trans_start();
		
		for($i=0;$i<count($_POST['id']);$i++){
			$this->db->where('id', $post['id'][$i]);
			$this->db->set('new', 0);
			$this->db->set('final_status', 'OK');
			$this->db->set('final_date', date('Y-m-d H:i:s'));
			$this->db->set('approval_status', 'OK');
			$this->db->update('ms_reklameDT');
			
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
		}
		if($ERR_MSG==''){
			$this->db->trans_complete();
			return 'success';
		}
		else{
			return $ERR_MSG;
		}
	}
		
	function pengajuan_detail($post)
	{
		$ids = implode(',',$_POST['id']);
		$qry = "
		SELECT hd.*, dt.*, b.BranchName
		FROM ms_reklameHD hd
		INNER JOIN Ms_Branch b ON hd.branchcode = b.BranchCode
		INNER JOIN ms_reklameDT dt ON hd.id_reklame = dt.id_reklame
		WHERE dt.id IN(".$ids.")
		ORDER BY hd.branchcode
		";
		
		$res = $this->db->query($qry);
		if($res->num_rows()>0){
			return $res->result_array();
			/*
			$data = $res->result_array();
			foreach($data as $i=>$row){
				$periode_start = date('Y-m-d', strtotime($row['periode_end']. ' + 1 days')); 
				$periode_end = date('Y-m-d', strtotime($row['periode_end']. ' + 1 years'));
				$data[$i]['periode_start'] = $periode_start;
				$data[$i]['periode_end'] = $periode_end;
			}
			echo json_encode($data);
			*/
		}
		return array();
	}
		
	function approval($post)
	{
		$ERR_MSG='';
		$this->db->trans_start();
		
		for($i=0;$i<count($_POST['id']);$i++){
			$this->db->where('id', $post['id'][$i]);
			
			// kolom ini akan dipakai lagi untuk pengajuan PO berikutnya
			$this->db->set('approval_status', $_POST['act']);
			$this->db->set('approval_date', date('Y-m-d H:i:s'));
			$this->db->set('rejected_note', $_POST['rejected_note']);
			$this->db->update('ms_reklameDT');
			
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
		}
		if($ERR_MSG==''){
			$this->db->trans_complete();
			return 'success';
		}
		else{
			return $ERR_MSG;
		}
	}
	
	function delete_po($post)
	{
		$ERR_MSG='';
		$this->db->trans_start();
		
		$this->db->where('id', $post['id']);
		$this->db->set('is_deleted', true);
		$this->db->set('deleted_by',$_SESSION['logged_in']['username']);
		$this->db->set('deleted_date', date('Y-m-d H:i:s'));
		$this->db->update('ms_reklameDT');
		
		
		if( ($errors = sqlsrv_errors() ) != null) {
			foreach( $errors as $error ) {
				$ERR_MSG.= $error[ 'message']."; ";
			}
		}
		$this->db->trans_complete();
		if($ERR_MSG==''){
			return 'success';
		}
		else{
			return $ERR_MSG;
		}
	}
	
	function batal_po($post)
	{
		$ERR_MSG='';
		$this->db->trans_start();
		
		$this->db->where('id', $post['id']);
		$this->db->set('status', 'CANCELLED');
		$this->db->set('is_cancelled', 1);
		$this->db->set('cancelled_date', date('Y-m-d H:i:s'));
		$this->db->set('cancelled_note', $post['cancelled_note']);
		$this->db->set('status_date', date('Y-m-d H:i:s'));
		$this->db->update('ms_reklameDT');
		
		
		if( ($errors = sqlsrv_errors() ) != null) {
			foreach( $errors as $error ) {
				$ERR_MSG.= $error[ 'message']."; ";
			}
		}
		$this->db->trans_complete();
		if($ERR_MSG==''){
			return 'success';
		}
		else{
			return $ERR_MSG;
		}
	}
		
	function deactivate($post)
	{
		$ERR_MSG='';
		$this->db->trans_start();
		
		$this->db->where('id_reklame', $post['id']);
		$this->db->set('is_active', false);
		$this->db->set('catatan', $post['catatan']);
		$this->db->set('modified_by',$_SESSION['logged_in']['username']);
		$this->db->set('modified_date', date('Y-m-d H:i:s'));
		$this->db->update('ms_reklameHD');
		
		if( ($errors = sqlsrv_errors() ) != null) {
			foreach( $errors as $error ) {
				$ERR_MSG.= $error[ 'message']."; ";
			}
		}
		$this->db->trans_complete();
		if($ERR_MSG==''){
			return 'success';
		}
		else{
			return $ERR_MSG;
		}
	}
	
	function reactivate($post)
	{
		$ERR_MSG='';
		$this->db->trans_start();
		
		$this->db->where('id_reklame', $post['id']);
		$this->db->set('is_active', true);
		$this->db->set('catatan', '');
		$this->db->set('modified_by',$_SESSION['logged_in']['username']);
		$this->db->set('modified_date', date('Y-m-d H:i:s'));
		$this->db->update('ms_reklameHD');
		
		if( ($errors = sqlsrv_errors() ) != null) {
			foreach( $errors as $error ) {
				$ERR_MSG.= $error[ 'message']."; ";
			}
		}
		$this->db->trans_complete();
		if($ERR_MSG==''){
			return 'success';
		}
		else{
			return $ERR_MSG;
		}
	}
		
	function emailed($row, $email)
	{
		$ERR_MSG='';
		$this->db->trans_start();
		foreach($row as $r){
			$this->db->where('id', $r['id']);
			$this->db->set('approval_status', 'WAITING FOR APPROVAL');
			$this->db->set('emailed_to', $email);
			$this->db->set('emailed_date', date('Y-m-d H:i:s'));
			$this->db->set('emailed_by', $_SESSION['logged_in']['useremail']);
			$this->db->update('ms_reklameDT');
			
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
		}
		$this->db->trans_complete();
		if($ERR_MSG==''){
			return 'success';
		}
		else{
			return $ERR_MSG;
		}
	}
	
	function shopboard_approval_count()
	{
		$qry = "
		SELECT COUNT(*) as jum
		FROM ms_reklameHD hd 
		INNER JOIN ms_reklameDT dt ON hd.id_reklame = dt.id_reklame
		WHERE dt.approval_status='WAITING FOR APPROVAL' AND hd.branchcode='".$_SESSION['logged_in']['branch_id']."'
		";
		$res = $this->db->query($qry);
		return $res->row()->jum;
	}
	
	function import($data)
	{
		$ERR_MSG='';
		$this->db->trans_start();
		foreach($data as $post){
			$id_reklame = $this->get_id_reklame();
			// echo $id_reklame;die;
			$this->db->set('id_reklame', $id_reklame);
			$this->db->set('branchcode', $post['branchcode']);
			$this->db->set('wilayah', $post['wilayah']);
			$this->db->set('nama_toko', $post['nama_toko']);
			$this->db->set('alamat', $post['alamat']);
			$this->db->set('kota', $post['kota']);
			$this->db->set('created_by',$_SESSION['logged_in']['username']);
			$this->db->set('created_date',date('Y-m-d H:i:s'));
			$this->db->set('is_active',1);
			$this->db->insert('ms_reklameHD');
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
			
			foreach($post['po'] as $po){
				$this->db->set('id_reklame', $id_reklame);
				$this->db->set('new', 0);
				$this->db->set('no_po', $po['no_po']);
				$this->db->set('periode_start', $po['periode_start']);
				$this->db->set('periode_end', $po['periode_end']);
				$this->db->set('supplier', $post['supplier']);
				$this->db->set('status', 'OK');
				$this->db->set('status_date', date('Y-m-d', strtotime($po['pajak'])));
				$this->db->set('approved_by', 'IMPORTED BY '.$_SESSION['logged_in']['username']);
				$this->db->set('final_status', 'OK');
				$this->db->set('final_date', date('Y-m-d', strtotime($po['pajak'])));
				$this->db->set('approval_status', 'OK');
				$this->db->set('approval_date', date('Y-m-d', strtotime($po['pajak'])));
				
				for($i=0;$i<=4;$i++){
					$no = $i+1;
					$this->db->set('merk'.$no, (ISSET($po['merk'][$i])) ? $po['merk'][$i] : NULL );
					$this->db->set('ukuran'.$no, (ISSET($po['ukuran'][$i])) ? $po['ukuran'][$i] : NULL );
				}
				
				$this->db->set('created_by',$_SESSION['logged_in']['username']);
				$this->db->set('created_date',date('Y-m-d H:i:s'));
				$this->db->insert('ms_reklameDT');
				
				if( ($errors = sqlsrv_errors() ) != null) {
					foreach( $errors as $error ) {
						$ERR_MSG.= $error[ 'message']."; ";
					}
				}
			}
		}
		if($ERR_MSG==''){
			$this->db->trans_complete();
			return 'success';
		}
		else{
			return $ERR_MSG;
		}
	}
}
?>
