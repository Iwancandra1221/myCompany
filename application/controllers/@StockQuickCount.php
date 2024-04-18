<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class StockQuickCount extends MY_Controller 
{
	public $excel_flag = 0;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('HelperModel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}
	
	public function index()
	{
		//include_once('/../includes/CheckModule.php');
		$data = array();

		$branches = $this->BranchModel->GetsByUser($_SESSION['logged_in']['useremail']);

		$data['title'] = 'Laporan Stock Quick Count | '.WEBTITLE;
		$data['branches'] = $branches;
		$data['months'] = $this->HelperModel->GetMonths();
		//$this->SetTemplate('template/laporan');

		if($this->uri->segment(2) != '')
	    	$ctrname = $this->uri->segment(1)."/".$this->uri->segment(2);
        else
	       	$ctrname = $this->uri->segment(1);

    	$data['access'] = $this->ModuleModel->getDetail($ctrname,$_SESSION['role']);

    	$api = 'APITES';

		$data['merk'] = json_decode(file_get_contents($this->API_URL."/StockQuickCount/GetListMerk?api=".$api));
		$data['jenis_barang'] = json_decode(file_get_contents($this->API_URL."/StockQuickCount/GetListJenisBarang?api=".$api));
		$data['kode_barang'] = json_decode(file_get_contents($this->API_URL."/StockQuickCount/GetListKodeBarang?api=".$api));
    	$data['wilayah'] = json_decode(file_get_contents($this->API_URL."/StockQuickCount/GetListWilayah?api=".$api));

		$this->RenderView('StockQuickCountFormView',$data);
	}

	public function Preview()
	{	
		$tanggal = $this->input->post('dpDate');
		$merk = rtrim($this->input->post('selMerk'));
		$jenisbarang = rtrim($this->input->post('selJenisBarang'));
		$kodebarang = rtrim($this->input->post('selKodeBarang'));
		$wilayah = rtrim($this->input->post('selWilayah'));
    	
		$api = 'APITES';
    	//print data
    	set_time_limit(60);
        $curl_handle=curl_init();
		curl_setopt($curl_handle, CURLOPT_URL,$this->API_URL."/StockQuickCount/PreviewData?api=".$api."&tgl=".urlencode($tanggal)."&merk=".urlencode($merk)."&jnsbrg=".urlencode($jenisbarang)."&kdbrg=".urlencode($kodebarang)."&wil=".urlencode($wilayah));
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_handle, CURLOPT_USERAGENT, 'MY COMPANY');
		$data['data'] = json_decode(curl_exec($curl_handle));
		// $data['data'] = json_decode(curl_exec($curl_handle));
		curl_close($curl_handle);

		// echo $this->API_URL."StockQuickCount/PreviewData?api=".$api."&tgl=".urlencode($tanggal)."&merk=".urlencode($merk)."&jnsbrg=".urlencode($jenisbarang)."&kdbrg=".urlencode($kodebarang)."&wil=".urlencode($wilayah);
		// print_r($data['barang']);
		$data['tgl'] = $tanggal;
		$data['merk'] = $merk;
		$data['jnsbrg'] = $jenisbarang;
		$data['kodebarang'] = $kodebarang;

		$data['barang'] = json_decode(file_get_contents($this->API_URL."/StockQuickCount/GetListBarang?api=".$api."&tgl=".urlencode($tanggal)."&merk=".urlencode($merk)."&jnsbrg=".urlencode($jenisbarang)."&kdbrg=".urlencode($kodebarang)."&wil=".urlencode($wilayah)));
		$data['wilayah'] = json_decode(file_get_contents($this->API_URL."/StockQuickCount/GetListWilayah?api=".$api."&wil=".urlencode($wilayah)));

		// print_r($data['barang']);
		// echo API_URL."StockQuickCount/GetListBarang?api=".$api."&tgl=".urlencode($tanggal)."&merk=".urlencode($merk)."&jnsbrg=".urlencode($jenisbarang)."&kdbrg=".urlencode($kodebarang)."&wil=".urlencode($wilayah);
		// print_r($data['data']);
		// exit(1);

		$this->RenderView('StockQuickCountResultView',$data);
			
	}
}