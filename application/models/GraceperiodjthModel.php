<?php
	Class GraceperiodjthModel extends CI_Model
	{

		public function __construct(){
			parent::__construct(){
				$this->load->model('GzipDecodeModel');
				$this->load->model('ConfigSysModel');
				$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
			}
		}

		function GetList(){
			$query ="select distinct request_no,Wilayah,Divisi,Kd_Plg,request_status,User_Name,Entry_Time,is_cancelled from TblGracePeriodJTFaktur";
			$res = $this->db->query($query);
			if ($res->num_rows()>0){
				return $res->result();
			}else{
				return array();
			}
		}

		function add($data=''){

			if(count($data['jtl'])){
				$error ='';
				$tahun =date_format(date_create($data['jtl'][0]),'Y');
				for($i=0; $i<count($data['jtl']); $i++){

					$query = "select * from TblGracePeriodJTFaktur where Wilayah='".$data['wilayah']."' AND  Divisi='".$data['divisi']."' AND Kd_Plg='".$data['pelanggan']."' AND  JT_Lama='".$data['jtl'][$i]."' AND JT_Lama='".$data['jtl'][$i]."' AND request_status IN ('UNPROCESSED','APPROVED')";
					$res = $this->db->query($query);

					if ($res->num_rows()>0){
						$error ='Data tidak dapat di simpan ke database, silahkan cek data kembali!!!';
					}else{
						if(date_format(date_create($data['jtl'][$i]),'Y')!==$tahun){
							$error ='Tahun jatuh Tempo lama tidak boleh berbeda!!!';
						}
					}
				}

				if(empty($error)){

					for($i=0; $i<count($data['jtl']); $i++){

						$this->db->set('Wilayah', $data['wilayah']);
						$this->db->set('Divisi', $data['divisi']);
						$this->db->set('Kd_Plg', $data['pelanggan']);
						$this->db->set('JT_Lama', $data['jtl'][$i]);
						$this->db->set('JT_Baru', $data['jtb'][$i]);
						$this->db->set('User_Name', $_SESSION["logged_in"]["username"]);
						$this->db->set('Entry_Time', date('Y-m-d H:i:s'));
						$this->db->set('request_no', $data['number']);
						$this->db->set('request_note', $data['catatan']);
						$this->db->set('request_status', 'UNPROCESSED');
						$this->db->set('is_cancelled', 0);
						$this->db->insert('TblGracePeriodJTFaktur');
					}


					for($i=0; $i<count($data['approval']); $i++){

	        			$query = "insert into TblApproval (ApprovalType, RequestNo, 
								RequestBy, RequestByName, RequestByEmail, RequestDate, 
								ApprovedBy, ApprovedByName, ApprovedByEmail, ApprovedDate, 
								ApprovalStatus,
								AddInfo1, AddInfo1Value, AddInfo2, AddInfo2Value,
								AddInfo3, AddInfo3Value,
								BhaktiFLag) 
								values ('GRACE PERIOD JT','".$data['number']."',
								'".$_SESSION["logged_in"]["useremail"]."','".$_SESSION["logged_in"]["username"]."',
								'".$_SESSION["logged_in"]["useremail"]."','".date('Y-m-d H:i:s')."',								 
								'".$data['approval'][$i]->email_address."','".$data['approval'][$i]->nm_slsman."',
								'".$data['approval'][$i]->email_address."','', 
								'UNPROCESSED',
								'Wilayah','".$data['wilayah']."', 'Kode Pelanggan','".$data['pelanggan']."', 'Divisi','".$data['divisi']."',
								'UNPROCESSED')";
						$this->db->query($query);
					}

					return 'success';
				}else{
					return $error;
				}




			}
		}

		function GetData($data){
			$number = base64_decode($data);
			$this->db->where('request_no',$number);
			$res = $this->db->get('TblGracePeriodJTFaktur');
			if ($res->num_rows()>0){
				return $res->result();
			}else{
				return array();
			}
		}

		function DeleteData($data){
			$number = base64_decode($data['number']);
			$this->db->set('is_cancelled',1);
			$this->db->set('cancelled_by',$_SESSION["logged_in"]["username"]);
			$this->db->set('cancelled_date',date('Y-m-d H:i:s'));
			$this->db->set('cancelled_note',$data['note']);
			$this->db->where('request_no',$number);
			$this->db->update('TblGracePeriodJTFaktur');

			$this->db->set('IsCancelled',1);
			$this->db->set('CancelledBy',$_SESSION["logged_in"]["useremail"]);
			$this->db->set('CancelledByName',$_SESSION["logged_in"]["username"]);
			$this->db->set('CancelledDate',date('Y-m-d H:i:s'));
			$this->db->set('CancelledNote',$data['note']);
			$this->db->set('CancelledByEmail',$_SESSION["logged_in"]["useremail"]);
			$this->db->where('RequestNo',$number);
			$this->db->update('TblApproval');
		}
		
		function GetWilayah(){
			$url =$this->API_URL.'/MsWilayah/GetListWilayahKD/?api=APITES';
			$wilayah = json_decode(file_get_contents($url), true);
			return $wilayah;
		}

		function GetDivisi(){
			$url =$this->API_URL.'/MsDivisi/GetListDivisi/?api=APITES';
			$wilayah = json_decode(file_get_contents($url), true);
			return $wilayah;
		}


		function GetPelanggan($data=''){
			$url =$this->API_URL.'/MasterDealer/GetDealerByNmWil/?api=APITES&wilayah='.$data;
			// $pelanggan = json_decode(file_get_contents($url), true);
			$pelanggan = file_get_contents($url);
			$pelanggan = $this->GzipDecodeModel->_decodeGzip_true($url);
			return $pelanggan;
		}

		function approval($data=''){
			$this->db->where('a.BranchID',$data);
			$this->db->where('a.IsActive','1');
			$this->db->where('c.is_active','1');
			$this->db->where('a.EventID','GRACE PERIOD JT');
			$this->db->join('Ms_ConfigApprovalDT b','a.ConfigID=b.ConfigID');
			$this->db->join('tb_salesman c','b.ApprovalByPosition=c.level_slsman');
			$res = $this->db->get('Ms_ConfigApprovalHD a');
			if ($res->num_rows()>0){
				return $res->result();
			}else{
				$this->db->where('a.BranchID','ALL');
				$this->db->where('a.IsActive','1');
				$this->db->where('c.is_active','1');
				$this->db->where('a.EventID','GRACE PERIOD JT');
				$this->db->join('Ms_ConfigApprovalDT b','a.ConfigID=b.ConfigID');
				$this->db->join('tb_salesman c','b.ApprovalByPosition=c.level_slsman');
				$res = $this->db->get('Ms_ConfigApprovalHD a');
				if ($res->num_rows()>0){
					return $res->result();
				}else{
					return array();
				}
			}
		}

		function cekapproved($data=''){
			$number = base64_decode($data);
			$this->db->where('IsCancelled',0);
			$this->db->where('RequestNo',$number);
			$this->db->where('ApprovedByEmail',$_SESSION["logged_in"]["useremail"]);
			$this->db->where('ApprovalStatus','UNPROCESSED');
			$res = $this->db->get('TblApproval a');
			if ($res->num_rows()>0){
				return 'show';
			}else{
				return 'hide';
			}
		}

		function approved($data){
			$this->db->set('ApprovedDate',date('Y-m-d H:i:s'));
			$this->db->set('ApprovalStatus',$data['status']);
			$this->db->set('BhaktiFlag',$data['status']);
			$this->db->where('RequestNo',$data['number']);
			$this->db->where('IsCancelled',0);
			$this->db->where('ApprovedByEmail',$_SESSION["logged_in"]["useremail"]);
			$this->db->update('TblApproval');

			$this->db->set('request_status',$data['status']);
			$this->db->where('request_no',$data['number']);
			$this->db->update('TblGracePeriodJTFaktur');
		}


		function sync($number){

			$serverdb = $_SESSION['conn']->Server;
			$URLAPI = $_SESSION['conn']->AlamatWebService.'/bktAPI/Graceperiodjt/Sync/?api=APITES&server='.$serverdb;

			$this->db->where('request_no',$number);
			$res = $this->db->get('TblGracePeriodJTFaktur');
			if ($res->num_rows()>0){

				$data = $res->result();
				foreach ($data as $key => $d){

					$curl = curl_init();
					curl_setopt($curl, CURLOPT_URL, $URLAPI);
					curl_setopt($curl, CURLOPT_POST, 1);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $d);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					$response = curl_exec($curl);
					echo $response; 
				}

			}
		}


	}

?>

