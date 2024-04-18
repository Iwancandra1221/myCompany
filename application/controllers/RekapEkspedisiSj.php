<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RekapEkspedisiSj extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function index()
	{
        if($this->uri->segment(2) != '')
	    	$ctrname = $this->uri->segment(1)."/".$this->uri->segment(2);
        else
	       	$ctrname = $this->uri->segment(1);

    	$data['access'] = $this->ModuleModel->getDetail($ctrname,$_SESSION['role']);

    	$api = 'APITES';
		$data['divisi'] = json_decode(file_get_contents($this->API_URL."/PabrikUpdate/GetListDivisi?api=".$api));
		$data['title'] = "REKAP EKSPEDISI SJ BHAKTI";

		$this->RenderView('RekapEkspedisiSjView',$data);
	}

	public function PickTujuan(){
		$this->SetTemplate('template/login');
		$this->RenderView('CabangPickerView');
	}

	public function GenerateData(){

		$tanggalawal = $this->input->post('dateTglStart');
		$tanggalakhir = $this->input->post('dateTglEnd');
		$divisi = $this->input->post('selDivisi'); 
		$divisi = trim($divisi);
		$mulaiberlaku = strtotime('2019/02/01');

		if(strtotime($tanggalawal) < $mulaiberlaku or strtotime($tanggalakhir) < $mulaiberlaku){
			echo "<script>alert('Generate data error! Tanggal yang dipilih harus 1 Februari 2019 atau setelahnya.'); window.close();</script>";
		}

		if(strtotime($tanggalawal) > strtotime($tanggalakhir)){
			echo "<script>alert('Generate data error! Tanggal awal harus sebelum tanggal akhir.'); window.close();</script>";
		}

		if(isset($_POST['chkAllTujuan']))
			$tujuan = 'ALL';
		else
			$tujuan = $this->input->post('txtTujuan');

		$status = $_POST['radStat'];

		$api = 'APITES';
		$data['data'] = json_decode(file_get_contents($this->API_URL."/PabrikUpdate/GetListUpdateSJ?api=".$api."&div=".$divisi."&kdtj=".$tujuan."&sd=".$tanggalawal."&ed=".$tanggalakhir."&st=".$status));

		// echo $this->API_URL."PabrikUpdate/GetListUpdate?api=".$api."&div=".$divisi."&kdtj=".$tujuan."&sd=".$tanggalawal."&ed=".$tanggalakhir;
		// print_r($data);

		$this->RenderView('pabrikListUpdateResultView',$data);
	}

	public function GetListSuratJalan()
	{
		$api = 'APITES';
		$kodetujuan = $this->input->get('kodetujuan');
		$res = file_get_contents($this->API_URL."/PabrikUpdate/GetListSuratJalan?api=".$api."&kd=".$kodetujuan);
		echo($res);
	}	

}