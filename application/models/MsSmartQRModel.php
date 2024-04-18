<?php
class MsSmartQRModel extends CI_Model
{
	public $ERRMSG="";
	public $ERRCODE=0;

	function __construct()
	{
		parent::__construct();		
		$CI = &get_instance();
	}
	function getLog($where){
		$this->db->select("*");
		$this->db->where($where);
		$result =$this->db->get('Log_SmartQR')->result_array();
		return $result;
	}
	function search($id=0, $isgroup=0, $tipe="", $merk="", $lokasi_qr_code="", $ver=1, $url="")
	{
		/*
		tambah 2 kolom
		ALTER TABLE Log_SmartQR ADD url_landing_page varchar(max) CONSTRAINT url_landing_page_col DEFAULT NULL
		ALTER TABLE Log_SmartQR ADD result varchar(50) CONSTRAINT result_col DEFAULT NULL
		*/
		$qry = "
		SELECT *
		FROM Ms_SmartQR
		WHERE ISNULL(is_deleted,0) = 0 AND tipe = '".$tipe."' AND merk ='".$merk."' AND lokasi_qr_code ='".$lokasi_qr_code."' AND isgroup=".(($isgroup=="")?0:$isgroup);
		// die($qry);
		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			$result = $res->row();
			$this->db->trans_start();
			$this->db->set('id',$id);
			$this->db->set('isgroup',$isgroup);
			$this->db->set('tipe',$tipe);
			$this->db->set('merk',$merk);
			$this->db->set('lokasi_qr_code',$lokasi_qr_code);
			$this->db->set('ver',$ver);
			$this->db->set('id',$id);
			$this->db->set('url',$url);
			$this->db->set('LogDate',date('Y-m-d H:i:s'));
			$this->db->set('result','SUCCESS');
			$this->db->set('url_landing_page ',$result->url_redirect);
			$this->db->insert('Log_SmartQR');
			// die($this->db->last_query());
			$ERR_MSG = "";
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
			$this->db->trans_complete();
		
			return $result;
		} else {
			//jika gak ketemu
			$this->db->trans_start();
			$this->db->set('id',$id);
			$this->db->set('isgroup',$isgroup);
			$this->db->set('tipe',$tipe);
			$this->db->set('merk',$merk);
			$this->db->set('lokasi_qr_code',$lokasi_qr_code);
			$this->db->set('ver',$ver);
			$this->db->set('id',$id);
			$this->db->set('url',$url);
			$this->db->set('LogDate',date('Y-m-d H:i:s'));
			$this->db->set('result','FAILED');
			//$this->db->set('url_landing_page ','');
			$this->db->insert('Log_SmartQR');
			return array();
		}
	}

	function GetList($id='%')
	{
		$qry = "
		SELECT a.*,
		CONVERT(varchar, a.created_date, 106) as created_tgl, 
		CONVERT(varchar, a.created_date, 108) as created_jam,
		CONVERT(varchar, a.modified_date, 106) as modified_tgl,
		CONVERT(varchar, a.modified_date, 108) as modified_jam
		FROM Ms_SmartQR a
		WHERE ISNULL(a.is_deleted,0) = 0 AND a.id LIKE '".$id."'
		ORDER BY a.created_date DESC
		";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			if($id=='%'){
				return $res->result();
			}
			else{
				return $res->row();
			}
		} else {
			return array();
		}
	}

	function GetListGroupProduct($merk = array(), $id='%')
	{
		$qry = "
		SELECT *,
		CONVERT(varchar, created_date, 106) as created_tgl, 
		CONVERT(varchar, created_date, 108) as created_jam,
		CONVERT(varchar, modified_date, 106) as modified_tgl,
		CONVERT(varchar, modified_date, 108) as modified_jam
		FROM Ms_SmartQRGroupProduct
		WHERE  ISNULL(is_deleted,0)=0 AND id LIKE '".$id."'";
		if(count($merk)>0){
			$qry .= " AND merk in('".implode("','",$merk)."') ";	
		}
		$qry.=" ORDER BY created_date DESC
		";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetListGroupProductById($id='%')
	{
		$qry = "
		SELECT *,
		CONVERT(varchar, created_date, 106) as created_tgl, 
		CONVERT(varchar, created_date, 108) as created_jam,
		CONVERT(varchar, modified_date, 106) as modified_tgl,
		CONVERT(varchar, modified_date, 108) as modified_jam
		FROM Ms_SmartQRGroupProduct
		WHERE ISNULL(is_deleted,0)=0 AND id LIKE '".$id."'";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->row();
		} else {
			return array();
		}
	}

	function GetListMerk()
	{
		$qry = "
		SELECT DISTINCT merk 
		FROM Ms_SmartQRGroupProduct
		WHERE ISNULL(is_deleted,0)=0
		ORDER BY merk ASC
		";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetListLokasiQRCode()
	{
		$qry = "
		SELECT DISTINCT lokasi_qr_code
		FROM Ms_SmartQR
		WHERE ISNULL(is_deleted,0) = 0
		ORDER BY lokasi_qr_code
		";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetParam($id)
	{
		$qry = "
		SELECT * 
		FROM Ms_SmartQRParam
		WHERE IdLandingPage = '".$id."'
		";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetKdBrg($tipe)
	{
		$qry = "
		SELECT kd_brg	
		FROM Ms_SmartQRGroupProduct
		WHERE ISNULL(is_deleted,0)=0 AND tipe = '".$tipe."'
		";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->row()->kd_brg;
		} else {
			return null;
		}
	}

	function SmartQRInsert($post, $params=array())
	{
		$ERR_MSG='';
		
		$tipe = $post['tipe'];
		if($post['isgroup']=='0'){
			//Ambil kode barang
			$x = explode(' | ',$post['tipe']);
			$tipe = $x[0];
		}
			
		$qry = "
		SELECT *
		FROM Ms_SmartQR
		WHERE tipe = '".$tipe."' AND merk ='".$post['merk']."' AND lokasi_qr_code ='".$post['lokasi_qr_code']."' AND isgroup =".$post['isgroup']." AND ISNULL(is_deleted,0)=0 ";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			$ERR_MSG .= 'Data sudah pernah diinput!\n'.(($post['isgroup']=='0') ? 'Tipe Barang' : 'Group Tipe Barang').': '.$post['tipe'].'\nMerk: '.$post['merk'].'\nLokasi QRCode: '.$post['lokasi_qr_code'].'';
		}
		else{
			
			$this->db->trans_start();
			$this->db->set('isgroup',$post['isgroup']);
			$this->db->set('tipe', $tipe);
			$this->db->set('merk',$post['merk']);
			$this->db->set('lokasi_qr_code',($post['lokasi_qr_code']=='OTHER')?$post['other']:$post['lokasi_qr_code']);
			// $this->db->set('url',$post['url']); // url dicreated setelah dapatkan inserted id 
			$this->db->set('ver',1);
			// $this->db->set('url_redirect',$post['url_redirect']); //isi setelah insert data
			$this->db->set('qty',(ISSET($post['AddInfoParam'])?$post['AddInfoParam']:NULL));
			$this->db->set('created_by',$_SESSION['logged_in']['username']);
			$this->db->set('created_date',date('Y-m-d H:i:s'));
			$this->db->set('modified_by',$_SESSION['logged_in']['username']);
			$this->db->set('modified_date',date('Y-m-d H:i:s'));
			
			$this->db->insert('Ms_SmartQR');
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
			
			$IdSmartQR = $this->db->insert_id();
			
			foreach( $params as $param){
				if($param['value']!=''){
					$this->db->set('IdLandingPage',$IdSmartQR);
					$this->db->set('ParamName',$param['name']);
					$this->db->set('ParamValue',$param['value']);
					$this->db->insert('Ms_SmartQRParam');
				}
			}
				
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
			
			//CREATE URL
			$url = $post['bhakti_url'];
			
			$url .= "?id=".urlencode($IdSmartQR);
			$url .= "&isgroup=".urlencode($post['isgroup']);
			$url .= "&tipe=".urlencode($tipe);
			$url .= "&merk=".urlencode($post['merk']);
			$url .= "&lokasi_qr_code=".urlencode($post['lokasi_qr_code']);
			$url .= "&ver=1";
			
			if($post['AddInfoParamName']!=''){
				$url .= "&".$post['AddInfoParamName']."=".urlencode($post['AddInfoParam']);
			}
			
			foreach($params as $param){
				$url .= "&".$param['name']."=".urlencode($param['value']);
			}
			
			$this->db->where('id',$IdSmartQR);
			$this->db->set('url',$url);
			$this->db->update('Ms_SmartQR');
			
				
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
			$this->db->trans_complete();
		}
		if($ERR_MSG==''){
			return array('result'=>'SUKSES','id'=>$IdSmartQR,'url'=>$url);
		}
		else{
			return array('result'=>'FAILED','message'=>$ERR_MSG);
		}
	}

	function SmartQRUpdate($post)
	{
		$ERR_MSG='';
		$this->db->trans_start();
		
		$this->db->where('id',$post['id']);
		$this->db->set('url_redirect',$post['url_redirect']);
		$this->db->set('modified_by',$_SESSION['logged_in']['username']);
		$this->db->set('modified_date',date('Y-m-d H:i:s'));
		$this->db->update('Ms_SmartQR');
			
		if( ($errors = sqlsrv_errors() ) != null) {
			foreach( $errors as $error ) {
				$ERR_MSG.= $error[ 'message']."; ";
			}
		}
		$this->db->trans_complete();
		if($ERR_MSG==''){
			return 'SUKSES';
		}
		else{
			return $ERR_MSG;
		}
	}

	function SmartQRDelete($post)
	{
		$ERR_MSG='';
		$this->db->trans_start();
		
		$this->db->where('id',$post['id']);
		$this->db->set('is_deleted', true);
		$this->db->set('reason_deleted', $post['reason_deleted']);
		$this->db->set('modified_by',$_SESSION['logged_in']['username']);
		$this->db->set('modified_date',date('Y-m-d H:i:s'));
		$this->db->update('Ms_SmartQR');
			
		if( ($errors = sqlsrv_errors() ) != null) {
			foreach( $errors as $error ) {
				$ERR_MSG.= $error[ 'message']."; ";
			}
		}
		$this->db->trans_complete();
		if($ERR_MSG==''){
			return 'SUKSES';
		}
		else{
			return $ERR_MSG;
		}
	}

	function GroupProductInsert($post)
	{
		$qry = "
		SELECT *
		FROM Ms_SmartQRGroupProduct
		WHERE ISNULL(is_deleted,0)=0 AND merk='".$post['merk']."' AND tipe='".$post['tipe']."'
		";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return "Merk dan Tipe Barang sudah ada!";
		}
		
		$ERR_MSG='';
		$this->db->trans_start();
		
		$this->db->set('merk',$post['merk']);
		$this->db->set('tipe',$post['tipe']);
		$this->db->set('kd_brg',(ISSET($post['kd_brg'])?implode(',',$post['kd_brg']):NULL));
		$this->db->set('created_by',$_SESSION['logged_in']['username']);
		$this->db->set('created_date',date('Y-m-d H:i:s'));
		$this->db->set('modified_by',$_SESSION['logged_in']['username']);
		$this->db->set('modified_date',date('Y-m-d H:i:s'));
		$this->db->insert('Ms_SmartQRGroupProduct');
			
		if( ($errors = sqlsrv_errors() ) != null) {
			foreach( $errors as $error ) {
				$ERR_MSG.= $error[ 'message']."; ";
			}
		}
		$this->db->trans_complete();
		if($ERR_MSG==''){
			return 'SUKSES';
		}
		else{
			return $ERR_MSG;
		}
	}

	function GroupProductUpdate($post)
	{
		$ERR_MSG='';
		$this->db->trans_start();
		
		$this->db->where('id',$post['id']);
		$this->db->set('kd_brg',(ISSET($post['kd_brg'])?implode(',',$post['kd_brg']):NULL));
		$this->db->set('modified_by',$_SESSION['logged_in']['username']);
		$this->db->set('modified_date',date('Y-m-d H:i:s'));
		$this->db->update('Ms_SmartQRGroupProduct');
			
		if( ($errors = sqlsrv_errors() ) != null) {
			foreach( $errors as $error ) {
				$ERR_MSG.= $error[ 'message']."; ";
			}
		}
		$this->db->trans_complete();
		if($ERR_MSG==''){
			return 'SUKSES';
		}
		else{
			return $ERR_MSG;
		}
	}
	
	function GroupProductDelete($post)
	{
		$qry = "
		SELECT *	
		FROM Ms_SmartQR
		WHERE ISNULL(is_deleted,0)=0 AND ISNULL(isgroup,0)=1 AND tipe = '".$post['tipe']."' AND merk='".$post['merk']."'
		";
		$res = $this->db->query($qry);
		if ($res->num_rows()==0) {
			$ERR_MSG='';
			$this->db->trans_start();
			
			$this->db->where('id',$post['id']);
			$this->db->set('is_deleted', true);
			$this->db->set('reason_deleted', $post['reason_deleted']);
			$this->db->set('modified_by',$_SESSION['logged_in']['username']);
			$this->db->set('modified_date',date('Y-m-d H:i:s'));
			$this->db->update('Ms_SmartQRGroupProduct');
				
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
			$this->db->trans_complete();
			if($ERR_MSG==''){
				return 'SUKSES';
			}
			else{
				return $ERR_MSG;
			}
		} else {
			return "Group Tipe Barang '".$post['tipe']."' Merk '".$post['merk']."' sudah dipakai!";
		}
		
		
		
	}

}
?>
