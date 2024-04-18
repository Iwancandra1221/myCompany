 <?php
	Class UniqueCodeGeneratorModel extends CI_Model
	{
		function __construct()
		{
			parent::__construct();
		}

		function getListSN(){

			$qry = "SELECT * FROM Log_UniqueCode Where ISNULL(isHide,0) = 0  ";
			if ($_SESSION["logged_in"]["isUserPabrik"]==1) {
				$qry .= " and CreatedBy = '".$_SESSION["logged_in"]["useremail"]."' ";
			}
			$qry .= "order by LogDate Desc ";
		    $res = $this->db->query($qry);

		    if ($res->num_rows()>0) 
		    	return $res->result();
		    else
		    	return array();
		}

		function Hide($UCID)
		{
			$qry = "UPDATE Log_UniqueCode SET isHide=1 WHERE LogId=".$UCID;
 			$res = $this->db->query($qry);
 			return $res;
		}

		function saveLog($data, $versiSALT, $ResponseCode, $ResponseText)
		{
			$this->db->set("LogDate", date("Y-m-d h:i:s"));
			$this->db->set("CreatedBy", $data["username"]);
			$this->db->set("SerialNoMin", strtoupper($data["serialnumber-min"]));
			$this->db->set("SerialNoMax", strtoupper($data["serialnumber-max"]));
			$this->db->set("ProductID", strtoupper($data["productcode"]));
			$this->db->set("SALTversion", $versiSALT);
			$this->db->set("ResponseCode", $ResponseCode);
			$this->db->set("Description", $ResponseText);
			$this->db->insert("Log_UniqueCode");
		}

		function checkLog($data)
		{		  
			// $qry = "SELECT * FROM Log_UniqueCode 
					// WHERE ProductID like '".$data["productId"]."%' 
						// and (SerialNoMin between '".$data["serialMin"]."' and '".$data["serialMax"]."' 
						  // or SerialNoMax between '".$data["serialMin"]."' and '".$data["serialMax"]."' 
						  // or '".$data["serialMin"]."' between SerialNoMin and SerialNoMax 
						  // or '".$data["serialMax"]."' between SerialNoMin and SerialNoMax) ";
						  
			$qry = "SELECT * FROM Log_UniqueCode 
					WHERE (ProductID like '".$data["productId"]." | %' OR ProductID='".$data["productId"]."') 
						and (SerialNoMin between '".$data["serialMin"]."' and '".$data["serialMax"]."' 
						  or SerialNoMax between '".$data["serialMin"]."' and '".$data["serialMax"]."' 
						  or '".$data["serialMin"]."' between SerialNoMin and SerialNoMax 
						  or '".$data["serialMax"]."' between SerialNoMin and SerialNoMax) ";
 			$res = $this->db->query($qry);
 			if ($res->num_rows()>0) {
 				return(array("result"=>"FAILED", "logs"=>$res->result()));
 			} else {
 				return(array("result"=>"SUCCESS", "logs"=>array()));
 			}
		}

	}
?>