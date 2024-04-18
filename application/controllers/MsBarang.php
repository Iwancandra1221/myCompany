<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MsBarang extends MY_Controller {

	function __construct()
	{
		parent::__construct();
        $this->load->model('MsBarangModel');
        $this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function index()
	{
	}

	public function Insentif($sukses=0)
	{
		$CheckAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		$data = array();
		// die($this->API_URL."/MsBarang/GetDivisiList?api=APITES");
        $divisi = json_decode(file_get_contents($this->API_URL."/MsBarang/GetDivisiList?api=APITES"), true);
        if ($divisi["result"]=="sukses") {
        	$data["divisions"] = $divisi["data"];
       	} else {
       		$data["divisions"] = array();
       		$data["alert"] = $divisi["error"];
       	}
       	//die($this->API_URL."/MsBarang/GetMerkList?api=APITES&divisi=".urlencode("all"));
        $merk = json_decode(file_get_contents($this->API_URL."/MsBarang/GetMerkList?api=APITES&divisi=".urlencode("all")), true);
        if ($merk["result"]=="sukses") {
        	$data["merks"] = $merk["data"];
       	} else {
       		$data["merks"] = array();
       		$data["alert"] = $merk["error"];
       	}
       	if ($sukses==1) {
       		$data["alert"] = "Simpan Berhasil";
       	}
		$this->RenderView('MsBarangInsentifView',$data);
	}

	public function GetMerkList()
	{
		$post = $this->PopulatePost();
		if(isset($post['divisi'])){
			//die($this->API_URL."/MsBarang/GetMerkList?api=APITES&divisi=".urlencode($post["divisi"]));
	        $merk = json_decode(file_get_contents($this->API_URL."/MsBarang/GetMerkList?api=APITES&divisi=".urlencode($post["divisi"])), true);
	        if ($merk["result"]=="sukses") {
				echo json_encode($merk["data"]);
	       	} else {
				echo json_encode(array('error'=>$merk["error"]));
	       	}
		} else {
			echo json_encode(array('error'=>'Invalid Request'));
		}
	}

	public function GetJenisList()
	{
		$post = $this->PopulatePost();
		if(isset($post['divisi']) && isset($post["merk"])){
			//die($this->API_URL."/MsBarang/GetMerkList?api=APITES&divisi=".urlencode($post["divisi"]));
	        $result = json_decode(file_get_contents($this->API_URL."/MsBarang/GetJenisList?api=APITES&divisi=".urlencode($post["divisi"]).
	        										"&merk=".urlencode($post["merk"])), true);
	        if ($result["result"]=="sukses") {
				echo json_encode($result["data"]);
	       	} else {
				echo json_encode(array('error'=>$result["error"]));
	       	}
		} else {
			echo json_encode(array('error'=>'Invalid Request'));
		}
	}

	public function GetBarangInsentifList()
	{
		$post = $this->PopulatePost();
		if(isset($post['divisi']) && isset($post["merk"]) && isset($post["jenis"]) & isset($post["kategori"])){
			$url = $this->API_URL."/MsBarang/GetBarangInsentifList?api=APITES&divisi=".urlencode($post["divisi"]).
	        				"&merk=".urlencode($post["merk"]).
							"&jenis=".urlencode($post["jenis"])."&kategori=".urlencode($post["kategori"]).
							"&filter=".urlencode($post["filter"]).
							"&view=".urlencode($post["view"]);
			//die($url);
	        $barang = json_decode(file_get_contents($url), true);
	        if ($barang["result"]=="sukses") {
				echo json_encode($barang["data"]);
	       	} else {
				echo json_encode(array('error'=>$barang["error"]));
	       	}
		} else {
			echo json_encode(array('error'=>'Invalid Request'));
		}
	}
	
	public function SimpanKategoriInsentif()
	{
		$post = $this->PopulatePost();

		if (isset($post['kodebarang'])){
			for($i=0;$i<count($post['kodebarang']);$i++)
			{

				$data = [
					"api" => "APITES",
					"id" => $post["productgroupid"][$i],
					"kdbrg" => $post["kodebarang"][$i],
					"kategori" => $post["kategoriinsentif"][$i],
					"tglawal" => date("Y-m-d", strtotime($post["tglawal"][$i])),
					"user" => $_SESSION["logged_in"]["username"]
				];
				
				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_URL => $this->API_URL."/MsBarang/SimpanKategoriInsentif",
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 60,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => json_encode($data),
					CURLOPT_HTTPHEADER => array("Content-type: application/json")
				));
				
				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);
				//echo($response."<br><br>");
			}
		}

		redirect("MsBarang/Insentif/1");
    }
}