<?php
class MsLandingPageModel extends CI_Model
{
	public $ERRMSG="";
	public $ERRCODE=0;

	function __construct()
	{
		parent::__construct();		
		$CI = &get_instance();
	}

	function search($type="", $merk="", $lokasi_qr_code="")
	{
		$qry = "
		SELECT *
		FROM Ms_LandingPage
		WHERE tipe = '".$type."' AND merk ='".$merk."' AND lokasi_qr_code ='".$lokasi_qr_code."'";
		// die($qry);
		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->row();
		} else {
			return array();
		}
	}

	function GetList($id='%')
	{
		$qry = "
		SELECT a.*
		FROM Ms_LandingPage a
		WHERE a.id LIKE '".$id."'
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

	function GetListTipeBarang($merk = array(), $id='%')
	{
		$qry = "
		SELECT a.*
		FROM Ms_LandingPageTipeBarang a
		WHERE a.id LIKE '".$id."'";
		if(count($merk)>0){
			$qry .= " AND merk in('".implode("','",$merk)."') ";	
		}
		$qry.=" ORDER BY a.created_date DESC
		";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetListTipeBarangById($id='%')
	{
		$qry = "
		SELECT a.*
		FROM Ms_LandingPageTipeBarang a
		WHERE a.id LIKE '".$id."'";

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
		FROM Ms_LandingPageTipeBarang
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
		FROM Ms_LandingPage
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
		FROM Ms_LandingPageParam
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
		FROM Ms_LandingPageTipeBarang
		WHERE tipe = '".$tipe."'
		";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->row()->kd_brg;
		} else {
			return null;
		}
	}

	function Insert($post, $params=array())
	{
		$ERR_MSG='';
		
		$qry = "
		SELECT *
		FROM Ms_LandingPage
		WHERE tipe = '".$post['tipe']."' AND merk ='".$post['merk']."' AND lokasi_qr_code ='".$post['lokasi_qr_code']."'";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			$ERR_MSG .= 'Data sudah pernah diinput!\nTipe: '.$post['tipe'].'\nMerk: '.$post['merk'].'\nLokasi QRCode: '.$post['lokasi_qr_code'].'';
		}
		else{
			$this->db->trans_start();
			$this->db->set('tipe',$post['tipe']);
			$this->db->set('merk',$post['merk']);
			$this->db->set('lokasi_qr_code',($post['lokasi_qr_code']=='OTHER')?$post['other']:$post['lokasi_qr_code']);
			$this->db->set('url',$post['url']);
			$this->db->set('url_redirect',$post['url_redirect']);
			$this->db->set('qty',(ISSET($post['qty'])?$post['qty']:NULL));
			$this->db->set('created_by',$_SESSION['logged_in']['username']);
			$this->db->set('created_date',date('Y-m-d H:i:s'));
			$this->db->set('modified_by',$_SESSION['logged_in']['username']);
			$this->db->set('modified_date',date('Y-m-d H:i:s'));
			
			$this->db->insert('Ms_LandingPage');
			
			$IdLandingPage = $this->db->insert_id();
			
			foreach( $params as $param){
				$this->db->set('IdLandingPage',$IdLandingPage);
				$this->db->set('ParamName',$param['name']);
				$this->db->set('ParamValue',$param['value']);
				$this->db->insert('Ms_LandingPageParam');
			}
				
			if( ($errors = sqlsrv_errors() ) != null) {
				foreach( $errors as $error ) {
					$ERR_MSG.= $error[ 'message']."; ";
				}
			}
			$this->db->trans_complete();
		}
		if($ERR_MSG==''){
			return 'SUKSES';
		}
		else{
			return $ERR_MSG;
		}
	}


	function Update($post)
	{
		$ERR_MSG='';
		$this->db->trans_start();
		
		$this->db->where('id',$post['id']);
		$this->db->set('url_redirect',$post['url_redirect']);
		$this->db->set('modified_by',$_SESSION['logged_in']['username']);
		$this->db->set('modified_date',date('Y-m-d H:i:s'));
		$this->db->update('Ms_LandingPage');
			
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

	function TipeBarangInsert($post)
	{
		$qry = "
		SELECT *
		FROM Ms_LandingPageTipeBarang
		WHERE merk='".$post['merk']."' AND tipe='".$post['tipe']."'
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
		$this->db->insert('Ms_LandingPageTipeBarang');
			
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

	function TipeBarangUpdate($post)
	{
		$ERR_MSG='';
		$this->db->trans_start();
		
		$this->db->where('id',$post['id']);
		$this->db->set('kd_brg',(ISSET($post['kd_brg'])?implode(',',$post['kd_brg']):NULL));
		$this->db->set('modified_by',$_SESSION['logged_in']['username']);
		$this->db->set('modified_date',date('Y-m-d H:i:s'));
		$this->db->update('Ms_LandingPageTipeBarang');
			
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

}
?>
