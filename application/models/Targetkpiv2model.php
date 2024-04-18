<?php
Class Targetkpiv2model extends CI_Model{

	function editTblApproval($where,$data){
		$this->db->where($where);
		$this->db->update('TblApproval',$data);
		// echo $this->db->last_query().'<br><br>';
		$result=true;
		return $result;
	}
	
	function getNextPriority($data){
		$query = "SELECT TOP 1 * FROM TblApproval WHERE RequestNo='".$data['no_request']."' AND ApprovalStatus='UNPROCESSED' AND IsEmailed=0 ORDER BY Priority ASC";
		// echo $query."<br><br>";
		$res = $this->db->query($query);
		if($res->num_rows()>0){
			return $res->row();
			/*
			$query = "SELECT TOP 1 * FROM TblApproval WHERE RequestNo='".$data['no_request']."' AND ApprovedByEmail='".$data['app_by']."' ";
			// echo $query."<br><br>";
			$res = $this->db->query($query);
			if($res->num_rows()>0){
				return $res->row();
			}
			else{
				return null;
			}
			*/
		}
		else{
			return null;
		}
	}
	
	function UpdateApproval($no_request, $app_by) //$deadline_emailed = array()
	{
		$this->db->where('RequestNo', $no_request);
		$this->db->where('ApprovedByEmail', $app_by);
		$this->db->where('ApprovalStatus', 'UNPROCESSED');
		$this->db->set('ApprovalStatus', 'APPROVED');
		$this->db->set('ApprovedDate', date('Y-m-d H:i:s'));
		$this->db->update('TblApproval');
		
		$approval_needed = 1;
		$priority = 1;
		$query = "SELECT TOP 1 * FROM TblApproval WHERE RequestNo='".$no_request."' AND ApprovedByEmail='".$app_by."' ";
		// echo $query."<br><br>";
		$res = $this->db->query($query);
		if($res->num_rows()>0){
			$approval_needed = $res->row()->ApprovalNeeded;
			$priority = $res->row()->Priority;
		}
		
		$query = "SELECT SUM(CASE WHEN ApprovalStatus='APPROVED' THEN 1 ELSE 0 END) as ApprovedCount FROM TblApproval WHERE RequestNo='".$no_request."' AND Priority=".$priority." ";
		// echo $query."<br><br>";
		$res = $this->db->query($query);
		if($res->num_rows()>0){
			$approved_count = $res->row()->ApprovedCount;
		}
		
		if($approved_count>=$approval_needed){
			$this->db->where('RequestNo', $no_request);
			$this->db->where('Priority', $priority);
			$this->db->where('ApprovalStatus', 'UNPROCESSED');
			$this->db->set('ApprovalStatus', 'APPROVED');
			// $this->db->set('ApprovedDate', date('Y-m-d H:i:s'));
			$this->db->update('TblApproval');
			
			
			$this->db->where('RequestNo', $no_request);
			$this->db->where('Priority', ($priority+1));
			$this->db->where('ApprovalStatus', 'UNPROCESSED');
			$this->db->set('IsEmailed', 1);
			$this->db->set('EmailedDate', date('Y-m-d H:i:s'));
			$this->db->update('TblApproval');
			
			/*
			if(ISSET($deadline_emailed)){
				foreach($deadline_emailed as $deadline){
					foreach($deadline['approval'] as $approver){
						$this->db->where('RequestNo', $no_request);
						$this->db->where('ApprovedByEmail', $approver['email']);
						$this->db->where('ApprovalStatus', 'UNPROCESSED');
						$this->db->set('IsEmailed', ($deadline['result']=='failed') ? 1 : 0 );
						if($deadline['result']=='failed'){
							$this->db->set('EmailedDate', date('Y-m-d H:i:s'));
						}
						$this->db->update('TblApproval');
					}
			
				}
			}
			*/
		}
	}
}
?>