<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class approvallist extends MY_Controller 
{
	public $approvers = array();
	public $recipients= array();
	public $whatsapps = array();

	public function __construct()
	{
		parent::__construct();
		$this->load->model("approvalmodel");
	}


	public function index()
	{	
		$this->view();
	}

	public function view($id='')
	{
		$data = array();
		$data["title"] = "PERSETUJUAN PERISTIWA";	
		$data["msg"] = "";

		if ($id!="") {

			if ($id==0) {
	            $data["msg"] = "Request Tidak Ada Di Sistem";        
	        } else if ($id==1) {
	            $data["msg"] = "Request Dihapus dari Sistem";
	        } else if ($id==2) {
	            $data["msg"] = "Request Sudah Di-AUTO APPROVAL";
	        } else if ($id==3) {
	            $data["msg"] = "Request Sudah Pernah Direject";
	        } else if ($id==4) {
	            $data["msg"] = "Request Sudah Pernah Diapprove";
	        } else if ($id==5) {
	            $data["msg"] = "Request Berhasil Diapprove";
	        } else if ($id==6) {
	            $data["msg"] = "Request Berhasil Direject";
	        } else if ($id==7) {
	            $data["msg"] = "Something Wrong, Data Tidak Bisa Diproses";
	        } else if ($id==8) {
	            $data["msg"] = "";
	        } else if ($id==9) {
	            $data["msg"] = "";
	        } else if ($id==10) {
	            $data["msg"] = "";
	        } else {
	        	$data["msg"] = "Pesan Error Undefined";
	        }
	        
	        //$_SESSION["info"] = $data["msg"];
	    }
		$this->RenderView('Approvallistview',$data);
	}

	public function list(){
		$_GET ['ApprovalType'] = '';
		$_GET ['ApprovedBy'] = $_SESSION["logged_in"]["userid"];
		$_GET ['ApprovedByEmail'] = $_SESSION["logged_in"]["useremail"];
		$hasildata = $this->approvalmodel->getpendingrequests($_GET);

		$data_list=array();
		$data_hasil=array();
		$total=0;
		$no=1;
		if(!empty($hasildata['data'])){
			foreach ($hasildata['data'] as $key => $r) {

				$list=array();

				$JENIS = $r->RequestType;
				$REQUEST = "";
				$INFO = "";
				$BUTTON = "";

				if ($JENIS=="TARGET SPG") {
					$REQUEST .="No: <b>  ".$r->RequestNo." </b><br>";
					$REQUEST .="Nama: <b>  ".$r->Divisi." </b><br>";
					$REQUEST .="Tgl Request: <b> ". date("d-M-Y H:i:s", strtotime($r->RequestDate))." </b><br>";
					$REQUEST .="Direquest Oleh: <b>  ".$r->RequestByName." </b>";
				} else {
					$REQUEST .="No: <b>  ".$r->RequestNo." </b><br>";
					$REQUEST .="Divisi: <b>  ".$r->Divisi." </b><br>";
					$REQUEST .="Tgl Request: <b>  ".date("d-M-Y H:i:s", strtotime($r->RequestDate))." </b><br>";
					$REQUEST .="Direquest Oleh: <b>  ".$r->RequestByName." </b>";								
				}
			
				if($r->RequestType=='CREDIT LIMIT'){
					$INFO .="Nama: <b> ". $r->NamaPelanggan." </b><br>Wilayah: <b> ".  $r->Wilayah." </b>"; 
				}else if($r->RequestType=='UNLOCK TOKO'){
					$INFO .="Nama: <b> ". $r->NamaPelanggan." </b><br>Wilayah: <b> ".  $r->Wilayah." </b>"; 
				}else if($r->RequestType=='TARGET SPG'){
					$INFO .="Periode: <b> ". $r->NamaPelanggan." </b><br>Wilayah: <b> ".  $r->Wilayah." </b>"; 
				}else if($r->RequestType=='TUNJANGAN PRESTASI SPG'){
					$INFO .="Periode: <b> ". $r->NamaPelanggan." </b><br>Wilayah: <b> ". $r->Wilayah." </b>"; 
				}else if($r->RequestType=='PLAN PO'){
					$INFO .="Keterangan: <b> ". $r->Catatan." </b>"; 
				}else if($r->RequestType=='CAMPAIGN PLAN'){
					$INFO .="Nama: <b> ".$r->NamaPelanggan." </b>"; 
				}else if($r->RequestType=='TARGET KPI KARYAWAN'){
					$INFO .="Periode: <b>".$r->NamaPelanggan." </b><br>Wilayah: <b>".$r->Wilayah."</b>"; 
				}else if($r->RequestType=='TARGET KPI'){
					$INFO .="Kategori KPI: <b> ". $r->KodePelanggan." </b><br>Periode: <b> ". $r->NamaPelanggan." </b><br>Wilayah: <b>  $r->Wilayah </b>"; 
				}else if($r->RequestType=='ACHIEVEMENT KPI KARYAWAN'){
					$INFO .="Periode: <b> ".$r->NamaPelanggan." </b><br>Wilayah: <b> ". $r->Wilayah." </b>"; 
				}else if($r->RequestType=='ACHIEVEMENT KPI'){
					$INFO .="Kategori KPI: <b> ".$r->KodePelanggan." </b><br>Periode: <b> ". $r->NamaPelanggan." </b><br>Wilayah: <b>".$r->Wilayah." </b>"; 
				}else if($r->RequestType=='REQUEST PORO'){
					$INFO .="Periode: <b> ".$r->NamaPelanggan." </b><br>Wilayah: <b> ". $r->Wilayah." </b>";
				}else if($r->RequestType=='TARGET KPI SALESMAN'){
					$INFO .="Periode: <b>".$r->NamaPelanggan." </b><br>Wilayah: <b>".$r->Wilayah."</b>"; 
				}else if($r->RequestType=='ACHIEVEMENT KPI SALESMAN'){
					$INFO .="Periode: <b>".$r->NamaPelanggan." </b><br>Wilayah: <b>".$r->Wilayah."</b>"; 
				}else if($r->RequestType=='TARGET KPI V2'){
					$INFO .="Periode: <b>".$r->NamaPelanggan." </b><br>Wilayah: <b>".$r->Wilayah."</b>"; 
				}else if($r->RequestType=='ACHIEVEMENT KPI V2'){
					$INFO .="Periode: <b> ".$r->NamaPelanggan." </b><br>Wilayah: <b> ". $r->Wilayah." </b>"; 
				}

				$BUTTON = "<a href='".$r->Url."'>VIEW</button>";

				$list[] 	= $no;
				$list[] 	= $JENIS;
				$list[] 	= $REQUEST;
				$list[] 	= $INFO;
				$list[] 	= $BUTTON;

				$data_list[]=$list;
				$no++;
			}

			$total=$hasildata['total'];

		}

		if(!empty($this->input->get('sEcho'))){
			$secho = $this->input->get('sEcho');
		}else{
			$secho = 1;
		}

		$data_hasil['sEcho']=$secho;
		$data_hasil['iTotalRecords']=$total;
		$data_hasil['iTotalDisplayRecords']=$total;
		$data_hasil['aaData']=$data_list;

		print_r(json_encode($data_hasil));
	}


}