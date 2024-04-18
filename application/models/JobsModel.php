<?php

class JobsModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}
  
	function GetListJobs()
	{
		$res = $this->db->query("select * from ms_jobs Where is_active = 1 ");

		if($res->num_rows() > 0)
			return $res->result();
		else
			return array();
	} 

	function GetListBranch()
	{
		$res = $this->db->query("select * from Ms_Branch a inner join MsDatabase b on a.BranchID = b.BranchId Where a.IsActive = 1 Order by a.BranchName");

		if($res->num_rows() > 0)
			return $res->result();
		else
			return array();
	}

	function GetList(){
		$this->db->select("*,case when is_active=0 then 'Not Active' else 'Active' end as is_active");
	    $res = $this->db->get('ms_jobs');
	    if ($res->num_rows()>0){
	    	return $res->result();
	    }else{
	    	return array();
	    }
	}
		
	function proses($data=''){
		
		$proses 			= $data['proses'];

		if($proses=='schedule'){

			$post = $data['post'];
			$jum_dbid 				= count($post['dbid']);
			$jum_jobs_schedule_type = count($post['jobs_schedule_type']);
			$jum_prioritas 			= count($post['prioritas']);
			$jum_server 			= count($post['server']);
			$jum_database 			= count($post['database']);

			$idjobs					= $post['idjobs'];
			$job_description		= $post['job_description'];
			$job_function			= $post['job_function'];
			if($jum_dbid==$jum_jobs_schedule_type && $jum_jobs_schedule_type==$jum_prioritas && $jum_prioritas==$jum_server && $jum_server==$jum_database){
					

				for($x=0; $x<$jum_jobs_schedule_type; $x++){
					$dbid 				= $post['dbid'][$x];
					$jobs_schedule_type = $post['jobs_schedule_type'][$x];

					if(!empty($post['jobs_schedule_day_'.$dbid])){
						$jobs_schedule_day = '';

						for($jsd=0; $jsd<count($post['jobs_schedule_day_'.$dbid]); $jsd++){
							$jobs_schedule_day 	.= $post['jobs_schedule_day_'.$dbid][$jsd].',';
						}
						for($y=0; $y<$jobs_schedule_day; $y++){
							
						}

						$jobs_schedule_day=substr($jobs_schedule_day, 0, -1);

					}else{
						$jobs_schedule_day	= '';
					}

					$prioritas 			= $post['prioritas'][$x];
					$server 			= $post['server'][$x];
					$database 			= $post['database'][$x];

					if(!empty($post['job_custom_query'][$x])){
						$job_custom_query	= $post['job_custom_query'][$x];
					}else{
						$job_custom_query	= '';
					}
					
					if(!empty($post['active'][$x])){
						$active = 1;
					}else{
						$active = 0;
					}

					$this->db->where('job_id',$idjobs);
					$this->db->where('DatabaseId',base64_decode($dbid));
					$res = $this->db->get('Ms_JobsDT');

					$this->db->set('job_id',$idjobs);
					$this->db->set('DatabaseId',base64_decode($dbid));
					$this->db->set('job_schedule_type',$jobs_schedule_type);
					$this->db->set('job_schedule_day',$jobs_schedule_day);
					$this->db->set('job_priority',$prioritas);
					$this->db->set('[server]',$server);
					$this->db->set('[database]',$database);
					$this->db->set('is_active',$active);
					$this->db->set('job_custom_query',$job_custom_query);


	    			if ($res->num_rows()>0){

	    				$this->db->where('job_id',$idjobs);
						$this->db->where('DatabaseId',base64_decode($dbid));
	    				$this->db->update('Ms_JobsDT');

	    			}else{

	    				$this->db->insert('Ms_JobsDT');

	    			}


					$this->db->where('DatabaseId',base64_decode($dbid));
					$trx_db = $this->db->get('MsDatabase');


					if ($trx_db->num_rows()>0){
						$URLAPI = $trx_db->row()->AlamatWebService.'bktAPI/Jobs/SendJobsCabang';
						// $URLAPI = 'http://localhost:90/bktAPI/Jobs/SendJobsCabang';
						$serverdb = $trx_db->row()->Server;

						$data = array('api' => 'APITES',
									  'proses' => 'save',
									  'job_description' => $job_description,
									  'job_function' => $job_function,
									  'server_db' => $serverdb, 
									  'job_id' => $idjobs, 
									  'job_schedule_type' => $jobs_schedule_type, 
									  'job_schedule_day' => $jobs_schedule_day, 
									  'job_priority' => $prioritas, 
									  'server' => $server, 
									  'database' => $database, 
									  'is_active' => $active, 
									  'job_custom_query' => $job_custom_query, 
									  'username' => $_SESSION['logged_in']['username']);

						$options = array(
						    'http' => array(
						        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
						        'method'  => 'POST',
						        'content' => http_build_query($data)
						    )
						);
						$context  = stream_context_create($options);
						$result = file_get_contents($URLAPI, false, $context);
						if ($result === FALSE) {}
						echo $result;
						
					}
				}

				
			}else{

				return 'error';

			}

		}else{

			$jobsid 			= strtoupper($data['jobsid']);
			$job_description 	= $data['job_description'];
			$function_jobs 		= $data['function_jobs'];
			$schedule_type 		= $data['schedule_type'];
			$job_priority 		= $data['job_priority'];
			$server 			= $data['server'];
			$database 			= $data['database'];
			$active 			= $data['active']*1;
			$custom_query 		= $data['custom_query'];

			if($proses=='add' && $_SESSION['can_create']==true){


				$this->db->where('job_id',$jobsid);
				$res = $this->db->get('ms_jobs');
				if ($res->num_rows()==0){ 

					$this->db->order_by('id','DESC');
					$this->db->select('id');
					$queryid = $this->db->get('ms_jobs');
					if ($queryid->num_rows()>0){ 
						$id = $queryid->row()->id+1;
					}else{
						$id=1;
					}

					$this->db->set('id', $id);
					$this->db->set('job_id', $jobsid);
					$this->db->set('job_description', $job_description);
					$this->db->set('job_function', $function_jobs);
					$this->db->set('job_schedule_type', $schedule_type);
					$this->db->set('job_priority', $job_priority);
					$this->db->set('server', $server);
					$this->db->set('[database]', $database);
					$this->db->set('is_active', $active);
					$this->db->set('is_custom_query', $custom_query);
					$this->db->set('created_by',$_SESSION['logged_in']['username']);
					$this->db->set('created_date',date('Y-m-d H:i:s'));
					$this->db->set('modified_by','');
					$this->db->set('modified_date','');
					$this->db->insert('ms_jobs');

					$data['proses'] = 'CREATE';
					$data['jobsid'] = $jobsid;
					return $this->email($data);
					//return 'success';

				}else{

					return 'sama';

				}

			}else if($proses=='edit' && $_SESSION['can_update']==true){

				$cek=0;
				if($active==0){

					$this->db->where('is_active','1');
					$this->db->where('job_id',$jobsid);
					$res = $this->db->get('Ms_JobsDT');
					if ($res->num_rows()>0){
						$cek = $res->num_rows();
					}

				}

				if($cek==0){

					$this->db->set('job_description', $job_description);
					$this->db->set('job_function', $function_jobs);
					$this->db->set('job_schedule_type', $schedule_type);
					$this->db->set('job_priority', $job_priority);
					$this->db->set('server', $server);
					$this->db->set('[database]', $database);
					$this->db->set('is_active', $active);
					$this->db->set('is_custom_query', $custom_query);
					$this->db->set('modified_by',$_SESSION['logged_in']['username']);
					$this->db->set('modified_date',date('Y-m-d H:i:s'));
					$this->db->where('job_id', $jobsid);
					$this->db->update('ms_jobs');

					$data['proses'] = 'EDIT';
					$data['jobsid'] = $jobsid;
					$this->email($data);

					return 'success';

				}else{
					return 'active';
				}

			}else if($proses=='delete' && $_SESSION['can_delete']==true){

				$cek=0;
				if($active==0){

					$this->db->where('is_active','1');
					$this->db->where('job_id',$jobsid);
					$res = $this->db->get('Ms_JobsDT');
					if ($res->num_rows()>0){
						$cek=1;
					}

				}

				if($cek==0){

					$this->db->where('job_id', $jobsid);
					$this->db->delete('ms_jobs');

					$this->db->where('job_id', $jobsid);
					$this->db->delete('Ms_JobsDT');

					$this->db->where('DatabaseId',$_SESSION['databaseID']);
					$trx_db = $this->db->get('MsDatabase');
					
						$URLAPI = $trx_db->row()->AlamatWebService.'/bktAPI/Jobs/SendJobsCabang';

						$data = array('api' => 'APITES','proses' => 'delete','job_id' => $jobsid);

						$options = array(
						    'http' => array(
						        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
						        'method'  => 'POST',
						        'content' => http_build_query($data)
						    )
						);
						$context  = stream_context_create($options);
						$result = file_get_contents($URLAPI, false, $context);
						if ($result === FALSE) {}

					echo 'success';
					$data['proses'] = 'DELETE';
					$data['jobsid'] = $jobsid;
					// return $this->email($data);
					$this->email($data);
					
					return 'success';

				}else{

					return 'active';

				}

			}else{

				return 'error';

			}

		}


	}

	function email($data){

		$configsys = $this->ConfigSysModel->Get();
		$smtp['protocol'] = $configsys->mail_protocol;
		$smtp['smtp_host'] = $configsys->mail_host;
		$smtp['smtp_port'] = $configsys->mail_port;
		$smtp['smtp_user'] = $configsys->mail_user;
		$smtp['smtp_pass'] = $configsys->mail_pwd;
		if ($configsys->smtp_crypto=="tls") {
			$smtp['smtp_crypto'] = $configsys->smtp_crypto;
		}
		$smtp['charset'] = "utf-8";
		$smtp['mailtype'] = "html";
		$smtp['newline'] = "\r\n";

		$this->load->library('email');
		$ci = get_instance();

		$this->db->where('ConfigType','MASTER JOBS');
		$qry = $this->db->get('Ms_Config');
		$query = $qry->result();

		$cc = array();
		$to = '';
		foreach ($query as $key => $q) {
			if($q->Group=='CC'){

				$cc[] = $q->ConfigValue;

			}else if($q->Group=='TO'){

				$to = $q->ConfigValue;

			}
		}
	


		$email_content = '<style>.header{ width:100%; float:left;} .body{ width:100%; float:left;}</style><div class="header" align="center"><h1>MASTER JOBS - '.$data['proses'].'</h1></div><div class="body" align="center"><h3>ID JOBS : '.$data['jobsid'].'</h3></div>';

		$ci->email->clear(true);
		$ci->email->initialize($smtp);
		$ci->email->from("bhaktiautoemail.noreply@bhakti.co.id", "BHAKTI.CO.ID AUTO-EMAIL");
		$ci->email->to($to);
		$ci->email->cc($cc);

		$ci->email->subject("MASTER JOBS ".$data['proses']." - ".$data['jobsid']);
		$ci->email->message($email_content);
		$ci->email->send();
					
	}

	function Get_Jobs($data=''){
	    $this->db->where('job_id',$data);
	    $res = $this->db->get('ms_jobs');
	    if ($res->num_rows()>0){
	    	return $res->result();
	    }else{
	    	return array();
	    }
	}

	function Get_Schedule($data=''){
		$query = "select a.DatabaseId as dbid,a.NamaDb,b.*,case when b.server is null then a.Server else b.server end as srs, case when b.[database] is null then a.[Database] else a.[Database] end as db from MsDatabase a left join (select * from Ms_JobsDT where job_id='".$data."') as b on a.DatabaseId=b.DatabaseId";
	    $res = $this->db->query($query);
	    if ($res->num_rows()>0) 
			return $res->result();
	    else
	    	return array();
	}
	
}
?>

