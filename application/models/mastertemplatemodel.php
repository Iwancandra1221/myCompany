<?php
	class mastertemplatemodel extends CI_Model
	{ 
		
		function __construct()
		{
			parent::__construct();
		}
 
		function GetList()
		{
			$str = "	SELECT template_id,template_name ,kpi_category_id,start_date ,is_active ,created_by ,created_date ,modified_by ,modified_date,
							b.KPICategoryName as kpi_category_name
						from Mst_TemplateTargetKPIHD a 
						Inner join Mst_KPICategory b on a.kpi_category_id = b.KPICategory   
						group by template_id ,template_name ,kpi_category_id ,start_date ,is_active ,created_by ,created_date ,modified_by ,modified_date, b.KPICategoryName
						order by a.template_id asc";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return $res->result();
			} else {
				return array();
			}
		}
 
	}
?>
