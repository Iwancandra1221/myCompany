<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TargetPenjualan extends MY_Controller {

	public $alert = "";

	function __construct()
	{
		parent::__construct();
        $this->load->model('MsSalesmanModel');
        $this->load->model('GzipDecodeModel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
	}

	public function index()
	{
		$CheckAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		$post = $this->PopulatePost();
		if(isset($post['SaveMode'])){
			$this->Simpan($post);
		}
		$this->view();
	}

	public function GroupDivisi(){
		$data = array();
        $result = json_decode(file_get_contents($this->API_URL."/TargetPenjualan/GetListGroupDivisi?api=APITES"), true);

		if($result["result"]=="sukses"){
			$data["divisi"] = $result["data"];
		} else {
			$data["divisi"] = array();
			$data["alert"] = $result["error"];
		}
		$this->RenderView('TargetPenjualanGroupDivisiView',$data);
	}

	public function view()
	{
		$data = array();

		if(isset($_SESSION['conn'])){
			$svr = $_SESSION["conn"]->Server;
			$db = $_SESSION["conn"]->Database;
			$url = $_SESSION["conn"]->AlamatWebService;

			//die($url.$this->API_BKT."/MasterSalesman/GetListSalesman?api=APITES");
	        // $result = json_decode(file_get_contents($url.$this->API_BKT."/MasterSalesman/GetListSalesman?api=APITES"), true);

	        $result = file_get_contents($url.$this->API_BKT."/MasterSalesman/GetListSalesman?api=APITES");
	        $result = $this->GzipDecodeModel->_decodeGzip_true($result);

			if($result["result"]=="sukses"){
				$salesman = $result["data"];
				//die(json_encode($salesman));
				for($i=0;$i<count($salesman);$i++) {
					$s = $this->MsSalesmanModel->GetMapping($salesman[$i]["KD_SLSMAN"]);
					if ($s!=null) {
						$salesman[$i]["USEREMAIL"] = $s->USEREMAIL;
					} else {
						$salesman[$i]["USEREMAIL"] = "";
					}
				}
				$data["salesman"] = $salesman;
			} else {
				$data["salesman"] = array();
				$data["alert"] = $result["error"];
			}
			/*if ($flag==1) {
				$data["alert"] = "Request Buka Lock Toko Telah Diemail";
			}*/
			$this->RenderView('MsSalesmanView',$data);
		}
		else{
			redirect('Home');
		}
	}

	public function MappingSalesman()
	{
		$post = $this->PopulatePost();
		if (isset($post['KodeSalesman']) && isset($post["UserEmail"])){
			$res = $this->MsSalesmanModel->GetMapping($post["KodeSalesman"]);
			if ($res==null) {
				$this->MsSalesmanModel->InsertMapping($post);
				echo json_encode(array("result"=>"sukses", "error"=>""));
			} else if ($post["UserEmail"]==$res->USEREMAIL) {
				echo json_encode(array("result"=>"gagal", "error"=>"Salesman Sudah Pernah Dimapping Ke User Yang Sama"));
			} else {
				$this->MsSalesmanModel->UpdateMapping($post);
				echo json_encode(array("result"=>"sukses", "error"=>""));
			}
		}
    }

	/*public function insert()
	{
		$email = $this->input->post('txtUseremail');
		$branchid = $this->input->post('txtBranchId');
		$dbname = $this->input->post('txtDatabase');
		$kdslsman = strtoupper($this->input->post('txtKdSlsman'));
		//check data kosong
		if($email == '' || $branchid == '' || $dbname == '' || $kdslsman == ''){
			redirect('MappingSalesman?inserterror=Tidak Boleh Ada Data yang Kosong.');
			exit(1);
		}

		//check kode sudah digunakan / belum
		$temp = $this->MappingSalesmanModel->get($kdslsman);
		if($temp){
			redirect('MappingSalesman?inserterror=Kode Salesman Sudah Digunakan.');
			exit(1);
		}

		$now = date('Y/m/d h:i:s A');

		$data = array (
            'kd_slsman' => $kdslsman,
            'useremail' => $email,
            'branch_id'	=> $branchid,
            'nama_db' 	=> $dbname,
            'created_by'=> $_SESSION['logged_in']['username'],
            'entry_time'=> $now
        );
        
        $this->MappingSalesmanModel->addData($data);
        redirect('MappingSalesman?insertsuccess=1');
    }

    public function update()
	{
		$oldkdslsman = $this->input->post('txtKdSlsmanOld');
		$newkdslsman = $this->input->post('UpdtxtKdSlsman');
		$email = $this->input->post('UpdtxtUseremail');
		$branchid = $this->input->post('UpdtxtBranchId');
		$namadb = $this->input->post('UpdtxtDatabase');

		$now = date('Y/m/d h:i:s A');

		$data = array (
            'kd_slsman' => $newkdslsman,
            'useremail' => $email,
            'branch_id'	=> $branchid,
            'nama_db' 	=> $namadb,
            'edit_by' 	=> $_SESSION['logged_in']['username'],
            'edit_time' => $now
        );

        $this->MappingSalesmanModel->updateData($data,$oldkdslsman);
        redirect('MappingSalesman?updatesuccess=1');
    }


    public function delete()
	{
		$dataid = $this->input->get('id');
        $this->MappingSalesmanModel->deleteData($dataid);
        redirect('MappingSalesman?deletesuccess=1');
    }*/
}