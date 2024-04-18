<?php
	Class MappingDealerModel extends CI_Model
	{
		function __construct()
		{
			parent::__construct();
			$this->load->model("MasterDbModel");
		}

		function getList($conn){
			$data = $this->MasterDbModel->getConArray($conn);
			$db2 = $this->load->database($data['config'], TRUE);
	 	 	$db2->select('*');
		    $db2->from('mst_mappingdealer');
		    return $db2->get()->result();
		}

		function getbyKdPlg($kdplg = '', $conn){
			$data = $this->MasterDbModel->getConArray($conn);
			$db2 = $this->load->database($data['config'], TRUE);
			$db2->select('*');
		    $db2->from('mst_mappingdealer');
		    $db2->where('Kd_Plg', $kdplg);
		    return $db2->get()->result();
		}
		function getbyKdPlgPjk($kdplg = '', $conn){
			$data = $this->MasterDbModel->getConArray($conn);
			$db2 = $this->load->database($data['config'], TRUE);
			$db2->select('*');
		    $db2->from('mst_mappingdealer');
		    $db2->where('Kd_PlgPjk', $kdplg);
		    return $db2->get()->result();
		}
		
	}
?>