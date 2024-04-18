<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MsSalesman extends MY_Controller {

	public $alert = "";

	function __construct()
	{
		parent::__construct();
        $this->load->model('MsSalesmanModel');
        $this->load->model('GzipDecodeModel');
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

	public function view()
	{
		$data = array();

		if(isset($_SESSION['conn'])){
			$svr = $_SESSION["conn"]->Server;
			$db = $_SESSION["conn"]->Database;
			$url = $_SESSION["conn"]->AlamatWebService;

	       	$result = file_get_contents($url.API_BKT."/MasterSalesman/GetListSalesman?api=APITES");
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

}