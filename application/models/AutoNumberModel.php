<?php
class AutoNumberModel extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	public function getAutoNumber_HistoryID($data)
	{
		if (empty($data['NameTag'])) {
			$data['NameTag'] = 'XXX';
		}
		$format = $data['NameTag']."/".$data['Period']."/";
		$this->db->limit(1, 0);
		$this->db->order_by('HistoryID', 'desc');
		$this->db->like('HistoryID', $format);
		$this->db->select('right(HistoryID,4) HistoryID ', false);
		$res = $this->db->get($data['Table']);

		if ($res->num_rows() > 0) {
			$auto = (sprintf("%04d", $res->row()->HistoryID + 1));
			return $format.$auto;
		} else {
			return $format.'0001';	
		}
	}

	public function getAutoNumber_x($data)
	{

		$format = $data."/";
		$this->db->limit(1, 0);
		$this->db->order_by('request_no', 'desc');
		$this->db->like('request_no', $format);
		$this->db->select('right(request_no,4) request_no ', false);
		$res = $this->db->get('TblGracePeriodJTFaktur');

		if ($res->num_rows() > 0) {
			$auto = (sprintf("%04d", $res->row()->request_no + 1));
			return $format.$auto;
		} else {
			return $format.'0001';	
		}
	}
}
?>