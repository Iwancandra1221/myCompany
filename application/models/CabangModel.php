<?php
	Class CabangModel extends CI_Model
	{

		public function getConArray($var){
			$this->load->model('masterDb');
	        $temp['row'] = $this->masterDb->get($var);

	        $data['config'] = array( 
	        'hostname' => $temp['row'][0]->Server,
			'username' => 'sa',
			'password' => 'Sprite12345',
			'database' => $temp['row'][0]->Database,
			'dbdriver' => 'sqlsrv',
			'dbprefix' => '',
			'pconnect' => FALSE,
			'db_debug' => (ENVIRONMENT !== 'production')
			);
			return $data;
		}

		function getList($conn){
			$data = $this->getConArray($conn);
			$db2 = $this->load->database($data['config'], TRUE);
	 	 	$db2->select('*');
		    $db2->from('mst_cabang');
		    return $db2->get()->result();
		}

		function get($kdlok = '',$conn){
			$data = $this->getConArray($conn);
			$db2 = $this->load->database($data['config'], TRUE);
	 	 	$db2->select('*');
		    $db2->from('mst_cabang');
		    $db2->where('kd_lokasi', $kdlok);
		    return $db2->get()->row();
		}

	}
?>