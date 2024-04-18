<?php
class MsConfigModel extends CI_Model
{
	public $ERRMSG="";
	public $ERRCODE=0;

	function __construct()
	{
		parent::__construct();		
		$CI = &get_instance();
	}

	function GetConfigAll(){
		$qry = "
		SELECT *
		FROM Ms_Config
		ORDER BY ConfigType, ConfigName, ConfigValue";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetPartnerType()
	{
		$qry = "SELECT ConfigValue from Ms_Config where ConfigType = 'MASTER' and ConfigName = 'PARTNER TYPE'";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetReportOPT()
	{
		$qry = "SELECT ConfigValue from Ms_Config where ConfigType = 'MASTER' and ConfigName = 'SALES REPORT OPTION'";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetDatabaseType()
	{
		$qry = "SELECT ConfigValue from Ms_Config where ConfigType = 'MASTER' and ConfigName = 'DATABASE TYPE'";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetConfigById($id='%'){
		$qry = "
		SELECT a.*
		FROM Ms_Config a
		WHERE ConfigId LIKE '".$id."'
		ORDER BY a.ConfigType, a.ConfigValue";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->row();
		} else {
			return array();
		}
	}

	function GetConfigName($type='%'){
		$qry = "
		SELECT DISTINCT ConfigName
		FROM Ms_Config
		WHERE ConfigType LIKE '".$type."' AND IsActive=1
		ORDER BY ConfigName ASC";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetConfigValue($type='%', $name='%', $group='ALL'){
		$qry = "
		SELECT DISTINCT ConfigType,ConfigName,ConfigValue,AddInfo, AddInfoParam, ";
		if($group=='ALL'){
			$qry .= " 'ALL' as [Group]";
		}else{
			$qry .= " [Group]";
		}
		$qry .= " FROM Ms_Config
		WHERE ConfigType LIKE '".$type."' AND ConfigName LIKE '".$name."' AND IsActive=1";
		
		if($group!='ALL'){
			$qry.=" AND [Group] IN('".$group."')";
		}
		else{
			// if($name=='PARAM')
			// $qry.=" AND [Group] IN('ALL','".$group."')";
		}
		
		$qry.=" ORDER BY ConfigType, ConfigName, ConfigValue ASC";		

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetConfigs($type='%'){
		$qry = "SELECT DISTINCT ConfigType,ConfigName,AddInfo, AddInfoParam, [Group] ";
		$qry.= "FROM Ms_Config
				WHERE ConfigType LIKE '".$type."' AND IsActive=1 ";		
		$qry.=" ORDER BY ConfigType, ConfigName, [Group] ASC";		

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function ConcatStringValue($type='%', $name='%', $group='ALL'){
		$values = $this->GetConfigValue($type, $name, $group);
		$result = "";
		foreach($values as $v) {
			$result .= $v->ConfigValue;
		}
		// die($result);
		return $result;
	}

	function GetGroup(){
		$qry = "
		SELECT DISTINCT [Group] 
		FROM Ms_Config
		ORDER BY [Group] ASC";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			return $res->result();
		} else {
			return array();
		}
	}

	function GetConfigType(){
		$qry = "
		SELECT DISTINCT ConfigType
		FROM Ms_Config
		WHERE IsActive=1
		ORDER BY ConfigType ASC";
		$res = $this->db->query($qry);
		if ($res->num_rows()>0) 
			return $res->result();
		else
			return array();
	}

	function GetConfigMaxIncrement(){
		$qry = " SELECT * from Ms_Config where ConfigType = 'RED' and ConfigName = 'MAX INCREMENT' ";
		$res = $this->db->query($qry);
		if ($res->num_rows()>0) 
			return $res->row()->ConfigValue;
		else
			return array();
	}

	function GetConfigColor($TELAT)
	{
		$qry = " SELECT * from Ms_Config 
				where ConfigType = 
				(select  ConfigType
				from (
				select a.configType, 
				a.ConfigValue as MinPaymentLate, 
				b.ConfigValue as MaxPaymentLate
				from ms_config a 
				inner join ms_config b 
				on a.configtype=b.configtype 
				where 
				a.configname = 'MIN PAYMENT LATE' 
				and 
				b.ConfigName='MAX PAYMENT LATE'
				) x
				where ".$TELAT."
				between 
				cast(MinPaymentLate as int) 
				and 
				cast(maxPaymentLate as int)) and ConfigName = 'COLOR'";
		$res = $this->db->query($qry);
		if ($res->num_rows()>0) 
			return $res->result();
		else
			return array();
	}
 
	function Insert($post)
	{
		$ERR_MSG='';
		
		$qry = "
		SELECT *
		FROM Ms_Config
		WHERE ConfigType = '".(($post['ConfigType']=='OTHER')?$post['ConfigType_Other']:$post['ConfigType'])."'
			AND ConfigName ='".(($post['ConfigName']=='OTHER')?$post['ConfigName_Other']:$post['ConfigName'])."'
			AND ConfigValue ='".$post['ConfigValue']."'
			AND [Group] ='".(($post['Group']=='OTHER')?$post['Group_Other']:$post['Group'])."'
			 ";

		$res = $this->db->query($qry);
		if ($res->num_rows()>0) {
			$ERR_MSG .= 'Data sudah pernah diinput!';
		}
		else{
			$this->db->trans_start();
			
			$this->db->set('ConfigType',($post['ConfigType']=='OTHER')?htmlspecialchars_decode($post['ConfigType_Other']):htmlspecialchars_decode($post['ConfigType']));
			$this->db->set('ConfigName',($post['ConfigName']=='OTHER')?htmlspecialchars_decode($post['ConfigName_Other']):htmlspecialchars_decode($post['ConfigName']));
			$this->db->set('ConfigValue',htmlspecialchars_decode($post['ConfigValue']));
			$this->db->set('[Group]',($post['Group']=='OTHER')?htmlspecialchars_decode($post['Group_Other']):htmlspecialchars_decode($post['Group']));
			$this->db->set('AddInfoParam',(ISSET($post['AddInfoParam'])?htmlspecialchars_decode($post['AddInfoParam']):NULL));
			$this->db->set('AddInfo',(ISSET($post['AddInfo'])?htmlspecialchars_decode($post['AddInfo']):NULL));
			
			$this->db->set('CreatedBy',$_SESSION['logged_in']['username']);
			$this->db->set('CreatedDate',date('Y-m-d H:i:s'));
			$this->db->set('ModifiedBy',$_SESSION['logged_in']['username']);
			$this->db->set('ModifiedDate',date('Y-m-d H:i:s'));
			$this->db->insert('Ms_Config');
			
			// if($post['ConfigType']=='PARAM'){
				// $this->db->query("IF NOT EXISTS ( SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'Ms_LandingPage' AND COLUMN_NAME = '".$post['ConfigName']."')
									// BEGIN
									  // ALTER TABLE Ms_LandingPage ADD ".$post['ConfigName']." VARCHAR(255) NULL
									// END");
			// }
			
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
		$this->db->where('ConfigId',$post['ConfigId']);
		$this->db->set('ConfigValue',htmlspecialchars_decode($post['ConfigValue']));
		$this->db->set('AddInfo',htmlspecialchars_decode($post['AddInfo']));
		$this->db->set('AddInfoParam',htmlspecialchars_decode($post['AddInfoParam']));
		$this->db->set('IsActive',$post['IsActive']);
		$this->db->set('ModifiedBy',$_SESSION['logged_in']['username']);
		$this->db->set('ModifiedDate',date('Y-m-d H:i:s'));
		$this->db->update('Ms_Config');
		
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
