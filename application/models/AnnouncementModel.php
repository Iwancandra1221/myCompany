<?php
class AnnouncementModel extends CI_Model{
	public function __construct(){
		parent::__construct();
	}
	function getTransAnnouncement($where,$order=null,$fetch='row'){
		$this->db->select("*");
		$this->db->where($where);
		
		if($order!=null) $this->db->order_by($order);

		$res = $this->db->get("trans_announcement");
		if($fetch=='row'){
			return $res->row();
		}
		else{
			return $res->result_array();
		}
	} 

	function Get()
	{
		$qry = $this->db->order_by('announcement_id','DESC');
		$qry = $this->db->get('trans_announcement');

		if($qry->num_rows() > 0){
			return $qry->result();
		}else{
			return array();
		}

	}

	function Get_Detail($id='')
	{
		$this->db->where('announcement_id', base64_decode($id));
		$qry = $this->db->get('trans_announcement');

		if($qry->num_rows() > 0){
			return $qry->result();
		}else{
			return array();
		}

	}

	function insert($data='',$file=''){

		$now = date('Y/m/d h:i:s A');

		if(!empty($file)){

			$count = count($file['attachment']['name']);
			$upload_location = "upload/attachment/announcement/";

			$files_arr = array();

			$file1='';
			$file2='';
			$file3='';

			if($count>0){
				for($i = 0;$i < $count;$i++){
					$path = "";
					$nama_file = "";

					if(isset($_FILES['attachment']['name'][$i]) && $_FILES['attachment']['name'][$i] != ''){
						$filename = $_FILES['attachment']['name'][$i];

						$acak = mt_rand(1000, 9999);
						$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
						$valid_ext = array("png","jpeg","jpg");
						if(in_array($ext, $valid_ext)){
							$name_file=$acak.'_'.$filename;
							$path = $upload_location.$name_file;
						}
						move_uploaded_file($_FILES['attachment']['tmp_name'][$i],$path);

						if($i==0){
							$this->db->set('attachment_1',$name_file);
						}else if($i==1){
							$this->db->set('attachment_2',$name_file);
						}else if($i==2){
							$this->db->set('attachment_3',$name_file);
						}

					}

				}
			}
		}

		if(!empty($data['active'])){
			$this->db->set('is_active','1');
		}else{
			$this->db->set('is_active','0');
		}

		$this->db->set('announcement',htmlspecialchars($data['announcement']));
		$this->db->set('start_published_date',date_format(date_create($data['start_published_date']),'Y-m-d'));
		$this->db->set('end_published_date',date_format(date_create($data['end_published_date']),'Y-m-d'));
		$this->db->set('created_by',$_SESSION['logged_in']['username']);
		$this->db->set('created_date',$now);
		$this->db->insert('trans_announcement');

		$this->db->where('created_by',$_SESSION['logged_in']['username']);
		$this->db->order_by('announcement_id','desc');
		$qry = $this->db->get('trans_announcement');

		if($qry->num_rows() > 0){
			$hasil = $qry->row()->announcement_id;
			return str_replace("=", "", base64_encode($hasil));
		}else{
			return 0;
		}

	}

	function update($data='',$file=''){

		$id = base64_decode(base64_decode($data['asd']));
		$now = date('Y/m/d h:i:s A');

		$this->db->where('announcement_id',$id);
		$qry = $this->db->get('trans_announcement');
		if($qry->num_rows() > 0){

			if(!empty($file)){
				$count = count($file['attachment']['name']);
				$upload_location = "attachment/announcement/";

				$files_arr = array();

				$file1='';
				$file2='';
				$file3='';

				if($count>0){

					if (!empty($qry->row()->attachment_1) && is_file('attachment/announcement/'.$qry->row()->attachment_1) && $_FILES['attachment']['size'][0]>0) {
				        unlink('attachment/announcement/'.$qry->row()->attachment_1);
				    }
				    if (!empty($qry->row()->attachment_2) && is_file('attachment/announcement/'.$qry->row()->attachment_2) && $_FILES['attachment']['size'][1]>0) {
				        unlink('attachment/announcement/'.$qry->row()->attachment_2);
				    }
				    if (!empty($qry->row()->attachment_3) && is_file('attachment/announcement/'.$qry->row()->attachment_3) && $_FILES['attachment']['size'][2]>0) {
				        unlink('attachment/announcement/'.$qry->row()->attachment_3);
				    }

					for($i = 0;$i < $count;$i++){

						if($_FILES['attachment']['size'][$i]>0){
							if(isset($_FILES['attachment']['name'][$i]) && $_FILES['attachment']['name'][$i] != ''){
								$filename = $_FILES['attachment']['name'][$i];

								$acak = mt_rand(1000, 9999);
								$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
								$valid_ext = array("png","jpeg","jpg");
								if(in_array($ext, $valid_ext)){
									$name_file=$acak.'_'.$filename;
									$path = $upload_location.$name_file;
								}
							}

							move_uploaded_file($_FILES['attachment']['tmp_name'][$i],$path);

							if($i==0){
								$this->db->set('attachment_1',$name_file);
							}else if($i==1){
								$this->db->set('attachment_2',$name_file);
							}else if($i==2){
								$this->db->set('attachment_3',$name_file);
							}
						}
					}
				}
			}

			if(!empty($data['active'])){
				$this->db->set('is_active','1');
			}else{
				$this->db->set('is_active','0');
			}

			$this->db->set('announcement',htmlspecialchars($data['announcement']));
			$this->db->set('start_published_date',date_format(date_create($data['start_published_date']),'Y-m-d'));
			$this->db->set('end_published_date',date_format(date_create($data['end_published_date']),'Y-m-d'));
			$this->db->set('modified_by',$_SESSION['logged_in']['username']);
			$this->db->set('modified_date',$now);
			$this->db->where('announcement_id',$id);
			$this->db->update('trans_announcement');

			return str_replace("=", "", base64_encode($id));

		}else{
			return 'error';
		}
	}

	function delete_img($a,$b){

		$this->db->where('announcement_id',base64_decode($b));
		$qry = $this->db->get('trans_announcement');
		if($qry->num_rows() > 0){

			if ($qry->row()->attachment_1==$a) {
				if(is_file('attachment/announcement/'.$qry->row()->attachment_1)){
			   		unlink('attachment/announcement/'.$qry->row()->attachment_1);
			   	}
			    $this->db->set('attachment_1','');
			}else if ($qry->row()->attachment_2==$a) {
				if(is_file('attachment/announcement/'.$qry->row()->attachment_2)){
			    	unlink('attachment/announcement/'.$qry->row()->attachment_2);
			    }
			    $this->db->set('attachment_2','');
			}else if ($qry->row()->attachment_3==$a) {
				if(is_file('attachment/announcement/'.$qry->row()->attachment_3)){
			    	unlink('attachment/announcement/'.$qry->row()->attachment_3);
			    }
			    $this->db->set('attachment_3','');
			}

			$this->db->where('announcement_id',$qry->row()->announcement_id);
			$this->db->update('trans_announcement');

		}
	}

	function delete_announcement($a){
		$this->db->where('announcement_id',base64_decode($a));
		$qry = $this->db->get('trans_announcement');
		if($qry->num_rows() > 0){

			if (!empty($qry->row()->attachment_1) && is_file('attachment/announcement/'.$qry->row()->attachment_1)){
			    unlink('attachment/announcement/'.$qry->row()->attachment_1);
			    $this->db->set('attachment_1','');
			}
			if (!empty($qry->row()->attachment_2) && is_file('attachment/announcement/'.$qry->row()->attachment_2)){
			    unlink('attachment/announcement/'.$qry->row()->attachment_2);
			    $this->db->set('attachment_2','');
			}
			if (!empty($qry->row()->attachment_3) && is_file('attachment/announcement/'.$qry->row()->attachment_3)){
			    unlink('attachment/announcement/'.$qry->row()->attachment_3);
			    $this->db->set('attachment_3','');
			}

		}

		$this->db->where('announcement_id',base64_decode($a));
		$this->db->delete('trans_announcement');

	}
}
?>
