<?php
	Class masterservicemodel extends CI_Model
	{


	function __construct()
	{
		parent::__construct();
	}
	function GetAutoNumberKerusakan(){
		$id = "0001";
		$result = $this->db->query("select kd_kerusakan from ms_service_kerusakan where isnumeric(kd_kerusakan)=1 order by kd_kerusakan desc")->row();
		if($result!=null){
			$strCounter = $result->kd_kerusakan;
			$counter = (int)$strCounter;
			$counter +=1;
			$prefix = ""; // Optional, you can remove this line if you don't want to use prefix

			$number = str_pad($counter, 4, "0", STR_PAD_LEFT); // Change 4 to the number of digits you want
			$id = $prefix . $number;
		}
		return $id;
	}
	function GetAutoNumberPenyebab(){
		$id = "0001";
		$result = $this->db->query("select kd_penyebab from ms_service_penyebab where isnumeric(kd_penyebab)=1 order by kd_penyebab desc")->row();
		if($result!=null){
			$strCounter = $result->kd_penyebab;
			$counter = (int)$strCounter;
			$counter +=1;
			$prefix = ""; // Optional, you can remove this line if you don't want to use prefix

			$number = str_pad($counter, 4, "0", STR_PAD_LEFT); // Change 4 to the number of digits you want
			$id = $prefix . $number;
		}
		return $id;
	}
	function GetAutoNumberPerbaikan(){
		$id = "0001";
		$result = $this->db->query("select kd_perbaikan from ms_service_perbaikan where isnumeric(kd_perbaikan)=1 order by kd_perbaikan desc")->row();
		if($result!=null){
			$strCounter = $result->kd_perbaikan;
			$counter = (int)$strCounter;
			$counter +=1;
			$prefix = ""; // Optional, you can remove this line if you don't want to use prefix

			$number = str_pad($counter, 4, "0", STR_PAD_LEFT); // Change 4 to the number of digits you want
			$id = $prefix . $number;
		}
		return $id;
	}
	function GetLastIdMsService(){
		$id = 1;
		$result = $this->db->query("select max(id) as last_id from ms_service")->row();
		if($result!=null){
			$id = ($result->last_id) + 1;
		}
		return $id;
	}
	function GetMsServiceCount($where){

		$this->db->select("count(ms_service.id) as c");
		$this->db->where($where);
		$this->db->join("ms_service_jnsbrg","ms_service_jnsbrg.kd_jnsbrg = ms_service.kd_jnsbrg","INNER");
		$this->db->join("ms_service_kerusakan","ms_service_kerusakan.kd_kerusakan = ms_service.kd_kerusakan","INNER");
		$this->db->join("ms_service_penyebab","ms_service_penyebab.kd_penyebab = ms_service.kd_penyebab","INNER");
		$this->db->join("ms_service_perbaikan","ms_service_perbaikan.kd_perbaikan = ms_service.kd_perbaikan","INNER");
		$result = $this->db->get("ms_service with(nolock)")->row();
		
		if($result!=null){
			return $result->c;
		}
		else{
			return 0;
		}

	}


	function GetMsService($data){

		$data=json_decode(json_encode($data));
		$data_list=array();

		$SortCol='b.kd_jnsbrg';
		$SortDir='desc';


		if(!empty($data->iSortCol_0)){
			if($data->iSortCol_0==0){
				$SortCol='b.jns_brg';
			}else if($data->iSortCol_0==1){
				$SortCol='c.Nm_Kerusakan';
			}else if($data->iSortCol_0==2){
				$SortCol='d.Nm_Penyebab';
			}else if($data->iSortCol_0==3){
				$SortCol='e.Nm_Perbaikan';
			}else if($data->iSortCol_0==4){
				$SortCol='a.modified_by';
			}else if($data->iSortCol_0==5){
				$SortCol='a.modified_date';
			} 
		}


		if(!empty($data->sSortDir_0)){
			$SortDir=$data->sSortDir_0;
		}



		$page=0;
		if(!empty($data->iDisplayStart)){
			$page=$data->iDisplayStart;
		}

		$total_data_view=10;
		if(!empty($data->iDisplayLength)){
			$total_data_view=$data->iDisplayLength;
		}



		// $query  =  "SELECT DISTINCT ms_service.id,ms_service_jnsbrg.jns_brg,ms_service_kerusakan.nm_kerusakan,ms_service_penyebab.nm_penyebab,ms_service_perbaikan.nm_perbaikan,
						// ms_service_jnsbrg.kd_jnsbrg,ms_service_kerusakan.kd_kerusakan,ms_service_penyebab.kd_penyebab,ms_service_perbaikan.kd_perbaikan,
						// ms_service.modified_by,ms_service.modified_date,ms_service.is_active 
						// FROM ms_service a
							// INNER JOIN ms_service_jnsbrg ON ms_service_jnsbrg.kd_jnsbrg = ms_service.kd_jnsbrg 
							// INNER JOIN ms_service_kerusakan ON ms_service_kerusakan.kd_kerusakan = ms_service.kd_kerusakan 
							// INNER JOIN ms_service_penyebab ON ms_service_penyebab.kd_penyebab = ms_service.kd_penyebab 
							// INNER JOIN ms_service_perbaikan ON ms_service_perbaikan.kd_perbaikan = ms_service.kd_perbaikan 
						// where 1=1 ";
						
			$query  =  "SELECT DISTINCT a.id, b.Kd_JnsBrg+' - '+b.JnsBrg as jns_brg,  c.Kd_Kerusakan+' - '+c.Nm_Kerusakan as nm_kerusakan, 
				 d.Kd_Penyebab+' - '+d.Nm_Penyebab as nm_penyebab,  e.Kd_Perbaikan+' - '+e.Nm_Perbaikan as nm_perbaikan,
				 b.kd_jnsbrg,c.kd_kerusakan,d.kd_penyebab,e.kd_perbaikan,
				 a.modified_by, a.modified_date, a.is_active
				FROM ms_service	a
				INNER JOIN ms_service_jnsbrg b ON (b.kd_jnsbrg = a.kd_jnsbrg and b.is_active=1)
				INNER JOIN ms_service_kerusakan c ON (c.kd_kerusakan = a.kd_kerusakan and c.is_active=1)
				INNER JOIN ms_service_penyebab d ON (d.kd_penyebab = a.kd_penyebab and d.is_active=1)
				INNER JOIN ms_service_perbaikan e ON (e.kd_perbaikan = a.kd_perbaikan and e.is_active=1)
				where a.is_active=1 ";
				
		// LEFT JOIN (select distinct kd_jnsbrg, jns_brg from ms_service_jnsbrg) as x ON x.kd_jnsbrg = ms_service.kd_jnsbrg 

		if(!empty($data->sSearch)){
			$query  .=  " AND (b.jns_brg LIKE '%".$data->sSearch."%' OR c.nm_kerusakan LIKE '%".$data->sSearch."%' 
								OR d.nm_penyebab LIKE '%".$data->sSearch."%' 
								OR e.nm_perbaikan LIKE '%".$data->sSearch."%' 
								OR b.kd_jnsbrg LIKE '%".$data->sSearch."%' OR c.kd_kerusakan LIKE '%".$data->sSearch."%' OR d.kd_penyebab LIKE '%".$data->sSearch."%' OR e.kd_perbaikan LIKE '%".$data->sSearch."%' OR a.is_active LIKE '%".$data->sSearch."%' OR a.modified_by LIKE '%".$data->sSearch."%' OR a.modified_date LIKE '%".$data->sSearch."%') ";
		}
		
		

		if(!empty($data->sSearch_0) && $data->sSearch_0!='ALL' && $data->sSearch_0!=''){
			$query  .=  " AND a.kd_jnsbrg='".$data->sSearch_0."'";
		}

		if(!empty($data->bRegex_0) && $data->bRegex_0!='ALL' && $data->bRegex_0!='' && $data->bRegex_0!='false'){
			$query  .=  " AND a.kd_kerusakan='".$data->bRegex_0."'";
		}

		if(!empty($data->sSearch_1) && $data->sSearch_1!='ALL' && $data->sSearch_1!=''){
			$query  .=  " AND a.kd_penyebab='".$data->sSearch_1."'";
		}

		if(!empty($data->bRegex_1) && $data->bRegex_1!='ALL' && $data->bRegex_1!='' && $data->bRegex_1!='false'){
			$query  .=  " AND a.kd_perbaikan='".$data->bRegex_1."'";
		}

		$resjum=$this->db->query($query);

		$query .=" order by ".$SortCol." ".$SortDir." OFFSET ".$page." ROWS FETCH NEXT ".$total_data_view." ROWS ONLY";

		// echo $query; die;

		$res=$this->db->query($query);
		if($res->num_rows() > 0){

			$hasildata['result']='success';
			$hasildata['total']=$resjum->num_rows();
			$hasildata['data']=$res->result();
			return json_encode($hasildata);

		}else{
			$hasildata['result']='error';
			$hasildata['total']=0;
			$hasildata['error']='Data tidak ditemukan';
			return json_encode($hasildata);
		}
	}


	function AddMsService($data){
		$this->db->trans_begin();
		$this->db->insert("ms_service",$data);
		$result = $this->db->trans_status();
		if ($result === FALSE){
		    $this->db->trans_rollback();
		}
		else{
		    $this->db->trans_commit();
		}
		return $result;
	}
	function EditMsService($where,$data){
		$this->db->trans_begin();
		$this->db->where($where);
		$this->db->update("ms_service",$data);
		$result = $this->db->trans_status();
		if ($result === FALSE){
		    $this->db->trans_rollback();
		}
		else{
		    $this->db->trans_commit();
		}
		return $result;
	}
	function DeleteMsService($where){
		$this->db->trans_begin();
		$this->db->where($where);
		$this->db->delete("ms_service");
		$result = $this->db->trans_status();
		if ($result === FALSE){
		    $this->db->trans_rollback();
		}
		else{
		    $this->db->trans_commit();
		}
		return $result;
	}
	function GetJenisBarangDistinct($where,$order="jns_brg ASC",$top=null){
		$from = <<<SQL
		(
			SELECT ROW_NUMBER() OVER (ORDER BY {$order}) as RowNum
			,x.*
			FROM (select distinct kd_jnsbrg,jns_brg from ms_service_jnsbrg) as x
		) as x
SQL;
		$this->db->select("*");
		$this->db->from($from);
		$this->db->where($where);
		if($top!=null) $this->db->limit($top);
		
		$result = $this->db->get()->result_array();

		return $result;
	}
	function GetJenisBarang($where,$order="jns_brg ASC",$top=null){
		$from = <<<SQL
		(
			SELECT ROW_NUMBER() OVER (ORDER BY {$order}) as RowNum
			,ms_service_jnsbrg.*
			FROM ms_service_jnsbrg
			 where is_active = 1
		) as x
SQL;
		$this->db->select("*");
		$this->db->from($from);
		$this->db->where($where);
		if($top!=null) $this->db->limit($top);
		
		$result = $this->db->get()->result_array();

		return $result;
	}
	
	//START >> Kerusakan
// 	function GetKerusakan($where,$order="created_date desc",$top=null){
// 		$from = <<<SQL
// 		(
// 			SELECT ROW_NUMBER() OVER (ORDER BY {$order}) as RowNum
// 			,ms_service_kerusakan.*
// 			FROM ms_service_kerusakan
// 		) as x
// SQL;

// 		$this->db->select("*");
// 		$this->db->from($from);
// 		$this->db->where($where);
// 		if($top!=null) $this->db->limit($top);

// 		$result = $this->db->get()->result_array();
// 		return $result;
// 	}

	function GetKerusakan($data){

		$data=json_decode(json_encode($data));
		$data_list=array();

		$SortCol='kd_kerusakan';
		$SortDir='asc';


		if(!empty($data->iSortCol_0)){
			if($data->iSortCol_0==0){
				$SortCol='kd_kerusakan';
			}else if($data->iSortCol_0==1){
				$SortCol='nm_kerusakan';
			}else if($data->iSortCol_0==2){
				$SortCol='is_active';
			}else if($data->iSortCol_0==3){
				$SortCol='modified_by';
			}else if($data->iSortCol_0==4){
				$SortCol='modified_date';
			} 
		}


		if(!empty($data->sSortDir_0)){
			$SortDir=$data->sSortDir_0;
		}



		$page=0;
		if(!empty($data->iDisplayStart)){
			$page=$data->iDisplayStart;
		}

		$total_data_view=10;
		if(!empty($data->iDisplayLength)){
			$total_data_view=$data->iDisplayLength;
		}



		$query  =  "SELECT * from ms_service_kerusakan where 1=1 ";


		if(!empty($data->sSearch)){
			$query  .=  " AND (kd_kerusakan LIKE '%".$data->sSearch."%'
								OR nm_kerusakan LIKE '%".$data->sSearch."%' 
								OR is_active LIKE '%".$data->sSearch."%' OR modified_by LIKE '%".$data->sSearch."%' OR modified_date LIKE '%".$data->sSearch."%') ";
		}
		

		$resjum=$this->db->query($query);

		$query .=" order by ".$SortCol." ".$SortDir." OFFSET ".$page." ROWS FETCH NEXT ".$total_data_view." ROWS ONLY";


		$res=$this->db->query($query);
		if($res->num_rows() > 0){

			$hasildata['result']='success';
			$hasildata['total']=$resjum->num_rows();
			$hasildata['data']=$res->result();
			return json_encode($hasildata);

		}else{
			return array();
		}
	}

	function UpdateKerusakan($where,$data){
		$this->db->trans_begin();

		$this->db->where($where);
		$this->db->update('ms_service_kerusakan',$data);
		$result = $this->db->trans_status();
		if ($result === FALSE){
		    $this->db->trans_rollback();
		}
		else{
		    $this->db->trans_commit();
		}
		return $result;
	}
	function AddKerusakan($data){
		$this->db->trans_begin();

		$this->db->insert('ms_service_kerusakan',$data);

		$result = $this->db->trans_status();
		if ($result === FALSE){
		    $this->db->trans_rollback();
		}
		else{
		    $this->db->trans_commit();
		}
		return $result;
	}
	function DeleteKerusakan($kode){
		// $this->db->trans_begin();
		$kode = base64_decode($kode);
		$this->db->where('kd_kerusakan',$kode);
		$found = $this->db->get("ms_service")->row();
		if($found==null){
			$this->db->where('kd_kerusakan',$kode);
			$this->db->delete('ms_service_kerusakan');
			return 'success';
		}else{
			return 'tidak_bisa_hapus';
		}

		// $result = $this->db->trans_status();
		// if ($result === FALSE){
		//     $this->db->trans_rollback();
		// 	return $result;
		// }
		// else{
		//     $this->db->trans_commit();
		// 	return 'success';
		// }
	}
	// END << Kerusakan

	// START >> Penyebab
// 	function GetPenyebab($where,$order="created_date desc",$top=null){
// 		$from = <<<SQL
// 		(
// 			SELECT ROW_NUMBER() OVER (ORDER BY {$order}) as RowNum
// 			,ms_service_penyebab.*
// 			FROM ms_service_penyebab
// 		) as x
// SQL;
// 		$this->db->select("*");
// 		$this->db->from($from);
// 		$this->db->where($where);
// 		if($top!=null) $this->db->limit($top);

// 		$result = $this->db->get()->result_array();
// 		return $result;
// 	}

	function GetPenyebab($data){

		$data=json_decode(json_encode($data));
		$data_list=array();

		$SortCol='kd_penyebab';
		$SortDir='asc';


		if(!empty($data->iSortCol_0)){
			if($data->iSortCol_0==0){
				$SortCol='kd_penyebab';
			}else if($data->iSortCol_0==1){
				$SortCol='nm_penyebab';
			}else if($data->iSortCol_0==2){
				$SortCol='is_active';
			}else if($data->iSortCol_0==3){
				$SortCol='modified_by';
			}else if($data->iSortCol_0==4){
				$SortCol='modified_date';
			} 
		}


		if(!empty($data->sSortDir_0)){
			$SortDir=$data->sSortDir_0;
		}



		$page=0;
		if(!empty($data->iDisplayStart)){
			$page=$data->iDisplayStart;
		}

		$total_data_view=10;
		if(!empty($data->iDisplayLength)){
			$total_data_view=$data->iDisplayLength;
		}



		$query  =  "SELECT * from ms_service_penyebab where 1=1 ";


		if(!empty($data->sSearch)){
			$query  .=  " AND (kd_penyebab LIKE '%".$data->sSearch."%'
								OR nm_penyebab LIKE '%".$data->sSearch."%' 
								OR is_active LIKE '%".$data->sSearch."%' OR modified_by LIKE '%".$data->sSearch."%' OR modified_date LIKE '%".$data->sSearch."%') ";
		}
		

		$resjum=$this->db->query($query);

		$query .=" order by ".$SortCol." ".$SortDir." OFFSET ".$page." ROWS FETCH NEXT ".$total_data_view." ROWS ONLY";


		$res=$this->db->query($query);
		if($res->num_rows() > 0){

			$hasildata['result']='success';
			$hasildata['total']=$resjum->num_rows();
			$hasildata['data']=$res->result();
			return json_encode($hasildata);

		}else{
			return array();
		}
	}

	function UpdatePenyebab($where,$data){
		$this->db->trans_begin();

		$this->db->where($where);
		$this->db->update('ms_service_penyebab',$data);
		$result = $this->db->trans_status();
		if ($result === FALSE){
		    $this->db->trans_rollback();
		}
		else{
		    $this->db->trans_commit();
		}
		return $result;
	}
	function AddPenyebab($data){
		$this->db->trans_begin();

		$this->db->insert('ms_service_penyebab',$data);

		$result = $this->db->trans_status();
		if ($result === FALSE){
		    $this->db->trans_rollback();
		}
		else{
		    $this->db->trans_commit();
		}
		return $result;
	}
	function DeletePenyebab($kode){
	// 	$this->db->trans_begin();

	// 	$this->db->where('kd_penyebab',$where);
	// 	$found = $this->db->get("ms_service")->row();
	// 	if($found==null){
	// 		//jika gak ketemu delete
	// 		$this->db->where('kd_penyebab',$where);
	// 		$this->db->delete('ms_service_penyebab');
	// 	}
	// 	else return 'Kode Penyebab sudah dipakai di master service!';
	// 	$result = $this->db->trans_status();
	// 	if ($result === FALSE){
	// 	    $this->db->trans_rollback();
	// 		return $result;
	// 	}
	// 	else{
	// 	    $this->db->trans_commit();
	// 		return 'sukses';
	// 	}

		$kode = base64_decode($kode);
		$this->db->where('kd_penyebab',$kode);
		$found = $this->db->get("ms_service")->row();
		if($found==null){
			$this->db->where('kd_penyebab',$kode);
			$this->db->delete('ms_service_penyebab');
			return 'success';
		}else{
			return 'tidak_bisa_hapus';
		}
	}

	// END << Penyebab
	
	// START >> Perbaikan
// 	function GetPerbaikan($where,$order="created_date desc",$top=null){
// 		$from = <<<SQL
// 		(
// 			SELECT ROW_NUMBER() OVER (ORDER BY {$order}) as RowNum
// 			,ms_service_perbaikan.*
// 			FROM ms_service_perbaikan
// 		) as x
// SQL;

// 		$this->db->select("*");
// 		$this->db->from($from);
// 		$this->db->where($where);
// 		if($top!=null) $this->db->limit($top);

// 		$result = $this->db->get()->result_array();
// 		return $result;
// 	}


	function GetPerbaikan($data){

		$data=json_decode(json_encode($data));
		$data_list=array();

		$SortCol='kd_perbaikan';
		$SortDir='asc';


		if(!empty($data->iSortCol_0)){
			if($data->iSortCol_0==0){
				$SortCol='kd_perbaikan';
			}else if($data->iSortCol_0==1){
				$SortCol='nm_perbaikan';
			}else if($data->iSortCol_0==2){
				$SortCol='is_active';
			}else if($data->iSortCol_0==3){
				$SortCol='modified_by';
			}else if($data->iSortCol_0==4){
				$SortCol='modified_date';
			} 
		}


		if(!empty($data->sSortDir_0)){
			$SortDir=$data->sSortDir_0;
		}



		$page=0;
		if(!empty($data->iDisplayStart)){
			$page=$data->iDisplayStart;
		}

		$total_data_view=10;
		if(!empty($data->iDisplayLength)){
			$total_data_view=$data->iDisplayLength;
		}



		$query  =  "SELECT * from ms_service_perbaikan where 1=1 ";


		if(!empty($data->sSearch)){
			$query  .=  " AND (kd_perbaikan LIKE '%".$data->sSearch."%'
								OR nm_perbaikan LIKE '%".$data->sSearch."%' 
								OR is_active LIKE '%".$data->sSearch."%' OR modified_by LIKE '%".$data->sSearch."%' OR modified_date LIKE '%".$data->sSearch."%') ";
		}
		

		$resjum=$this->db->query($query);

		$query .=" order by ".$SortCol." ".$SortDir." OFFSET ".$page." ROWS FETCH NEXT ".$total_data_view." ROWS ONLY";


		$res=$this->db->query($query);
		if($res->num_rows() > 0){

			$hasildata['result']='success';
			$hasildata['total']=$resjum->num_rows();
			$hasildata['data']=$res->result();
			return json_encode($hasildata);

		}else{
			return array();
		}
	}






	function UpdatePerbaikan($where,$data){
		$this->db->trans_begin();

		$this->db->where($where);
		$this->db->update('ms_service_perbaikan',$data);

		$result = $this->db->trans_status();
		if ($result === FALSE){
		    $this->db->trans_rollback();
		}
		else{
		    $this->db->trans_commit();
		}
		return $result;
	}
	function AddPerbaikan($data){
		$this->db->trans_begin();

		$this->db->insert('ms_service_perbaikan',$data);

		$result = $this->db->trans_status();
		if ($result === FALSE){
		    $this->db->trans_rollback();
		}
		else{
		    $this->db->trans_commit();
		}
		return $result;
	}
	function DeletePerbaikan($kode){
		// $this->db->trans_begin();

		// $this->db->where('kd_perbaikan',$where['kd_perbaikan']);
		// $found = $this->db->get("ms_service")->row();
		// if($found==null){
		// 	$this->db->where($where);
		// 	$this->db->delete('ms_service_perbaikan');
		// }
		// else return 'Kode Perbaikan sudah dipakai di master service!';

		// $result = $this->db->trans_status();
		// if ($result === FALSE){
		//     $this->db->trans_rollback();
		// 	return $result;
		// }
		// else{
		//     $this->db->trans_commit();
		// 	return 'sukses';
		// }

		$kode = base64_decode($kode);
		$this->db->where('kd_perbaikan',$kode);
		$found = $this->db->get("ms_service")->row();
		if($found==null){
			$this->db->where('kd_perbaikan',$kode);
			$this->db->delete('ms_service_perbaikan');
			return 'success';
		}else{
			return 'tidak_bisa_hapus';
		}

	}
	// END << Perbaikan

	//----------------------------------------------Yudha-----------------------------------------------//

	
	function GetList(){
		$this->db->select("DISTINCT kd_jnsbrg,jnsbrg,modified_by,modified_date, CASE WHEN is_active=0 THEN 'NON ACTIVE' ELSE 'ACTIVE' END AS active");
		$res = $this->db->get('ms_service_jnsbrg');
		if ($res->num_rows()>0){
			return $res->result();
		}else{
			return array();
		}
	}

	function GetListAll(){
		$this->db->select("kd_jnsbrg,jns_brg,modified_by,modified_date, CASE WHEN is_active=0 THEN 'NON ACTIVE' ELSE 'ACTIVE' END AS active,merk,jnsbrg");
		$res = $this->db->get('ms_service_jnsbrg');
		if ($res->num_rows()>0){
			return $res->result();
		}else{
			return array();
		}
	}

	function add($data=''){

		if(count($data['merk'])){
			$this->db->where('kd_jnsbrg',$data['kode']);
			$res = $this->db->get('ms_service_jnsbrg');

			$success = '';

			if ($res->num_rows()==0){
				$count=count($data['merk']);
				$tamp_error='';
				for($i=0; $i<$count; $i++){


					$this->db->where('merk',$data['merk'][$i]);
					$this->db->where('jnsbrg',$data['jnsbrg'][$i]);
					$res = $this->db->get('ms_service_jnsbrg');

					if ($res->num_rows()==0){
						$this->db->where('kd_jnsbrg',$data['kode']);
						$this->db->where('jns_brg',$data['jns_brg']);
						$this->db->where('merk',$data['merk'][$i]);
						$this->db->where('jnsbrg',$data['jnsbrg'][$i]);
						$res = $this->db->get('ms_service_jnsbrg');

						if ($res->num_rows()>0){
							$tamp_error .=" Kode Barang = ".$data['kode']."<br>";
							$tamp_error .=" Jenis Barang = ".$data['jns_brg']."<br>";
							$tamp_error .=" merk Barang = ".$data['merk'][$i]."<br>";
							$tamp_error .=" Kode Barang = ".$data['jnsbrg'][$i]."<br><hr>";
						}else{
							$this->db->set('kd_jnsbrg',$data['kode']);
							$this->db->set('jns_brg',$data['jns_brg']);
							$this->db->set('merk',$data['merk'][$i]);
							$this->db->set('jnsbrg',$data['jnsbrg'][$i]);
							$this->db->set('is_active',$data['aktif']);
							$this->db->set('created_by',$_SESSION['logged_in']['username']);
							$this->db->set('created_date',date('Y-m-d H:i:s'));
							$this->db->set('modified_by',$_SESSION['logged_in']['username']);
							$this->db->set('modified_date',date('Y-m-d H:i:s'));
							$this->db->insert('ms_service_jnsbrg');
						}

					}else{
						$tamp_error = 'double_merk_jenis_barang';
						return $tamp_error;
						die();
					}

				}
				$success = str_replace("=", "", base64_encode($data['kode']));
			}else{
				$tamp_error = 'double';
			}

			if(empty($tamp_error)){
				return $success;
			}else{
				return $tamp_error;
			}

		}
	}

	function update($data=''){

		if(count($data['merk'])){
			$this->db->where('kd_jnsbrg',$data['kode']);
			$res = $this->db->get('ms_service_jnsbrg');

			$success = '';
			if ($res->num_rows()>0){

				$created_by = $res->row()->created_by;
				$created_date = $res->row()->created_date;

				$count=count($data['merk']);
				$tamp_error='';
				$errorjum=0;

				for($i=0; $i<$count; $i++){


					$this->db->where('kd_jnsbrg!=',$data['kode']);
					$this->db->where('jns_brg!=',$data['jns_brg']);
					$this->db->where('merk',$data['merk'][$i]);
					$this->db->where('jnsbrg',$data['jnsbrg'][$i]);
					$res = $this->db->get('ms_service_jnsbrg');
					if ($res->num_rows()>0){
						$errorjum++;
					}

				}

				if($errorjum==0){

					$this->db->where('kd_jnsbrg',$data['kode']);
					$this->db->delete('ms_service_jnsbrg');
				

					for($i=0; $i<$count; $i++){


						$this->db->where('kd_jnsbrg',$data['kode']);
						$this->db->where('jns_brg',$data['jns_brg']);
						$this->db->where('merk',$data['merk'][$i]);
						$this->db->where('jnsbrg',$data['jnsbrg'][$i]);
						$res = $this->db->get('ms_service_jnsbrg');

						if ($res->num_rows()>0){
							$tamp_error .=" Kode Barang = ".$data['kode']."<br>";
							$tamp_error .=" Jenis Barang = ".$data['jns_brg']."<br>";
							$tamp_error .=" merk Barang = ".$data['merk'][$i]."<br>";
							$tamp_error .=" Kode Barang = ".$data['jnsbrg'][$i]."<br><hr>";
						}else{
							$this->db->set('kd_jnsbrg',$data['kode']);
							$this->db->set('jns_brg',$data['jns_brg']);
							$this->db->set('merk',$data['merk'][$i]);
							$this->db->set('jnsbrg',$data['jnsbrg'][$i]);
							$this->db->set('is_active',$data['aktif']);
							$this->db->set('created_by',$created_by);
							$this->db->set('created_date',$created_date);
							$this->db->set('modified_by',$_SESSION['logged_in']['username']);
							$this->db->set('modified_date',date('Y-m-d H:i:s'));
							$this->db->insert('ms_service_jnsbrg');
						}

					}

					$success = str_replace("=", "", base64_encode($data['kode']));

				}else{
					$tamp_error = 'double_merk_jenis_barang';
				}

			}else{
				$tamp_error = 'error';
			}

			if(empty($tamp_error)){
				return $success;
			}else{
				return $tamp_error;
			}
			

		}
	}

	function GetData($a){
		$kode = base64_decode($a);
		$this->db->where('kd_jnsbrg',$kode);
		$res = $this->db->get('ms_service_jnsbrg');
		if ($res->num_rows()>0){
			return $res->result();
		}else{
			return array();
		}
	}

	function DeleteData($data){
		$kode = base64_decode($data);
		$this->db->where('kd_jnsbrg',$kode);
		$res = $this->db->get('ms_service');

		if ($res->num_rows()==0){
			$this->db->where('kd_jnsbrg',$kode);
			$this->db->delete('ms_service_jnsbrg');
			return 'success';
		}else{
			return 'tidak_bisa_hapus';
		}
	}

	//ICAN
	function GetJnsBrg($where){
		$this->db->select("*");
		$this->db->where($where);
		$this->db->order_by('created_date desc');
		$result = $this->db->get("ms_service_jnsbrg")->result_array();
		return $result;
	}
	function GetService($where){
		$this->db->select("*");
		$this->db->where($where);
		$this->db->order_by('created_date desc');
		$result = $this->db->get("ms_service")->result_array();
		return $result;
	} 
	//ICAN
	
	
	function SelectJenisBarang(){
		$q = "SELECT DISTINCT kd_jnsbrg,jns_brg FROM ms_service_jnsbrg WHERE is_active = 1 order by kd_jnsbrg asc";
		$result = $this->db->query($q)->result_array();
		return $result;
	}
	function SelectKerusakan($jenisbarang){
		$q = "SELECT DISTINCT a.kd_kerusakan,a.nm_kerusakan FROM ms_service_kerusakan a inner join ms_service b on a.kd_kerusakan=b.kd_kerusakan WHERE a.is_active = '1'";

		if($jenisbarang!=='ALL' && $jenisbarang!==''){
			$q .= " and b.kd_jnsbrg='".$jenisbarang."'";
		}

		$q .=" order by a.kd_kerusakan asc";

		$result = $this->db->query($q)->result_array();
		return $result;
	}
	function SelectPenyebab($jenisbarang,$kerusakan){
		$q = "SELECT DISTINCT a.kd_penyebab,a.nm_penyebab FROM ms_service_penyebab a inner join ms_service b on a.kd_penyebab=b.kd_penyebab WHERE a.is_active = '1'";

		if($jenisbarang!=='ALL' && $jenisbarang!==''){
			$q .= " and b.kd_jnsbrg='".$jenisbarang."'";
		}

		if($kerusakan!=='ALL' && $kerusakan!==''){
			$q .= " and b.kd_kerusakan='".$kerusakan."'";
		}


		$q .=" order by a.kd_penyebab asc";
		$result = $this->db->query($q)->result_array();
		return $result;
	}
	function SelectPerbaikan($jenisbarang,$kerusakan,$penyebab){

		$q = "SELECT DISTINCT a.kd_perbaikan,a.nm_perbaikan FROM ms_service_perbaikan a inner join ms_service b on a.kd_perbaikan=b.kd_perbaikan WHERE a.is_active = '1'";

		if($jenisbarang!=='ALL' && $jenisbarang!==''){
			$q .= " and b.kd_jnsbrg='".$jenisbarang."'";
		}

		if($kerusakan!=='ALL' && $kerusakan!==''){
			$q .= " and b.kd_kerusakan='".$kerusakan."'";
		}

		if($penyebab!=='ALL' && $penyebab!==''){
			$q .= " and b.kd_penyebab='".$penyebab."'";
		}


		$q .=" order by a.kd_perbaikan asc";
		$result = $this->db->query($q)->result_array();
		return $result;

	}
	


	function SelectJenisBarangv2(){
		$q = "SELECT DISTINCT kd_jnsbrg,jns_brg FROM ms_service_jnsbrg WHERE is_active = 1 order by kd_jnsbrg asc";
		$result = $this->db->query($q)->result_array();
		return $result;
	}
	function SelectKerusakanv2(){
		$q = "SELECT DISTINCT a.kd_kerusakan,a.nm_kerusakan FROM ms_service_kerusakan a WHERE a.is_active = '1'";
 
		$q .=" order by a.kd_kerusakan asc";

		$result = $this->db->query($q)->result_array();
		return $result;
	}
	function SelectPenyebabv2(){
		$q = "SELECT DISTINCT a.kd_penyebab,a.nm_penyebab FROM ms_service_penyebab a WHERE a.is_active = '1'";
 

		$q .=" order by a.kd_penyebab asc";
		$result = $this->db->query($q)->result_array();
		return $result;
	}
	function SelectPerbaikanv2(){

		$q = "SELECT DISTINCT a.kd_perbaikan,a.nm_perbaikan FROM ms_service_perbaikan a WHERE a.is_active = '1'";
 

		$q .=" order by a.kd_perbaikan asc";
		$result = $this->db->query($q)->result_array();
		return $result;

	}
	
	function getSyncData($table, $page = 1, $page_count = 0)
	{
		$row_per_page = 1000;
		if($page_count==0){
			$count = "SELECT COUNT(*) as total_data FROM ".$table;
			$res = $this->db->query($count);
			$total_data = $res->row()->total_data;
			$page_count = ceil($total_data/$row_per_page);
		}
		// echo $page_count;die;
		
		$start = ($page - 1) * $row_per_page;
		
		$str = "SELECT * FROM ".$table." ORDER BY created_date OFFSET ".$start." ROWS FETCH NEXT ".$row_per_page." ROWS ONLY ";
		 // echo $str."<br>"; //die;
		 if($page_count==0 || $page==$page_count){
			$page = 0;
		 }
		 else{
			$page+=1;
		 }
		 
		$res = $this->db->query($str);
		return ['data'=>$res->result(),'page'=>$page,'page_count'=>$page_count];
		// return ['data'=>[],'page'=>$page,'page_count'=>$page_count];
	}
	
}

?>
