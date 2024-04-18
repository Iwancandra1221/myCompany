<?php
	Class TargetPenjualanModel extends CI_Model
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





	}
?>