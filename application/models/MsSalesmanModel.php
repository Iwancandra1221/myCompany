<?php
	Class MsSalesmanModel extends CI_Model
	{

		function GetMapping($kdslsman){
			$str = "Select kd_slsman as KD_SLSMAN, isnull(nm_slsman,'') as NM_SLSMAN, 
					isnull(level_slsman,'SALESMAN') as LEVEL_SLSMAN, isnull(useremail,'') as USEREMAIL, 
					isnull(branch_id,'') AS BRANCH_ID, isnull(database_id,0) AS DATABASE_ID
					From tb_salesman Where kd_slsman = '".$kdslsman."'";
			//die($str);
		    $res = $this->db->query($str);
		    if ($res->num_rows()>0)
			    return $res->row();
			else
				return null;
		 }

		 function InsertMapping($data)
		 {
		 	$this->db->set("kd_slsman", $data["KodeSalesman"]);
		 	$this->db->set("nm_slsman", $data["NamaSalesman"]);
		 	$this->db->set("level_slsman", $data["LevelSalesman"]);
		 	$this->db->set("useremail", $data["UserEmail"]);
		 	$this->db->set("branch_id", $_SESSION["logged_in"]["branch_id"]);
		 	$this->db->set("database_id", $_SESSION["databaseID"]);
		 	$this->db->insert("tb_salesman");
		 }

		 function UpdateMapping($data)
		 {
		 	$this->db->where("kd_slsman", $data["KodeSalesman"]);
		 	$this->db->set("nm_slsman", $data["NamaSalesman"]);
		 	$this->db->set("level_slsman", $data["LevelSalesman"]);
		 	$this->db->set("useremail", $data["UserEmail"]);
		 	$this->db->set("branch_id", $_SESSION["logged_in"]["branch_id"]);
		 	$this->db->set("database_id", $_SESSION["databaseID"]);
		 	$this->db->insert("tb_salesman");
		 }




	}
?>