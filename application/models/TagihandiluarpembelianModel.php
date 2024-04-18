<?php
	class TagihandiluarpembelianModel extends CI_Model{
		public function __construct(){
			parent::__construct();
		}

		function role(){
			$query = "select LevelNumber from USERINFO a INNER JOIN Ms_EmpLevel b on a.EmpLevelID=b.EmpLevelID WHERE USERID='".$_SESSION['logged_in']['userid']."' AND LevelNumber>1";
			$res = $this->db->query($query);
			if ($res->num_rows()>0){
				return true;
			}else{
				return false;
			}
		}
	}
?>