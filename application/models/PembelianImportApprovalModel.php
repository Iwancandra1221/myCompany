<?php
	Class PembelianImportApprovalModel extends CI_Model
	{
		function get($number){

			$query = "select * from TblApproval where RequestNo='".$number."' and ApprovedBy='".$_SESSION["logged_in"]["useremail"]."' and ApprovalType='PURCHASE IMPORT'";
			//$query = "select * from TblApproval where RequestNo='".base64_decode($number)."' and ApprovedBy='".$_SESSION["logged_in"]["useremail"]."' and ApprovalType='PURCHASE IMPORT'";
			$res = $this->db->query($query);
			if ($res->num_rows()>0) {
				return $res->result();
			} else {
				return null;
			}
		}
	}
?>