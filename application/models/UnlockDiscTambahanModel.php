<?php
class UnlockDiscTambahanModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$CI = &get_instance();
	}

    // get AlamatWebService utk baca DB BHAKTI cabang yang diinginkan
	function get($wilayah){
		$res = $this->db->query("Select AlamatWebService, Server, [Database]
								From MsDatabase Where NamaDb='".$wilayah."'");
		if ($res->num_rows()>0)
			return $res->row();
		else
			return null;
	}

}
?>