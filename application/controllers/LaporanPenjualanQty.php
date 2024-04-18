<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LaporanPenjualanQtyCtr extends MY_Controller 
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
		$data = array();

		$branches = $this->BranchModel->GetsByUser($_SESSION['logged_in']['useremail']);

		$data['title'] = WEBTITLE.' | Laporan Penjualan Quantity';
		$data['branches'] = $branches;
		$data['months'] = $this->HelperModel->GetMonths();
		//$this->SetTemplate('template/laporan');

		if($this->uri->segment(2) != '')
	    	$ctrname = $this->uri->segment(1)."/".$this->uri->segment(2);
        else
	       	$ctrname = $this->uri->segment(1);

    	$data['access'] = $this->ModuleModel->getDetail($ctrname,$_SESSION['role']);

    	$api = 'APITES';
		$data['divisi'] = json_decode(file_get_contents($this->API_URL."/LaporanPenjualanQty/GetListDivisi?api=".$api));

		$this->RenderView('LaporanPenjualanQtyFormView',$data);
	}

	public function Preview()
	{
		if($this->input->post('cmdPreviewDataCompare')){
			$this->Preview2();
		}
		$monStart = $this->input->post('monStart');
		$monEnd = $this->input->post('monEnd');
		$divisi = $this->input->post('selDivisi'); 
		$divisi = trim($divisi);

		if($monStart > $monEnd){
			echo "<script>alert('Generate data error! Tanggal awal harus sebelum tanggal akhir.'); window.close();</script>";
		}

		$produk = $_POST['radLokalImport'];
		$jenistrans = $_POST['radJenisTrans'];

		$temp_awal = '01';
		$temp_akhir = date("t", strtotime($monEnd));

		$thn_awal = date("Y", strtotime($monStart));
		$thn_akhir = date("Y", strtotime($monEnd));

		$thn_lalu_awal = $thn_awal - 1;
		$thn_lalu_akhir = $thn_akhir - 1;

		if($thn_awal != $thn_akhir){
			echo "<script>alert('Generate data error! Report hanya support data pada tahun yang sama.'); window.close();</script>";
		}

		$bulan_awal = date("m", strtotime($monStart));
		$bulan_akhir = date("m", strtotime($monEnd));
		$arrbulan = array();

		for($i=$bulan_awal;$i<=$bulan_akhir;$i++){
			array_push($arrbulan, str_pad($i,2,'0',STR_PAD_LEFT));
		}

		$tgl_awal = date("Y", strtotime($monStart)).date("m", strtotime($monStart)).$temp_awal;
		$tgl_akhir = date("Y", strtotime($monEnd)).date("m", strtotime($monEnd)).$temp_akhir;

		$tgl_awal_thn_lalu = $thn_lalu_awal .date("m", strtotime($monStart)).$temp_awal;
		$tgl_akhir_thn_lalu = $thn_lalu_akhir.date("m", strtotime($monEnd)).$temp_akhir;

		$api = 'APITES';

		if (isset($_POST['submitProcess'])) {
			echo "Please wait the data is being processed";
			set_time_limit(60);

	        $curl_handle=curl_init();
			curl_setopt($curl_handle, CURLOPT_URL,$this->API_URL."/LaporanPenjualanQty/ProcessData?api=".$api."&div=".urlencode($divisi)."&start=".urlencode($tgl_awal)."&end=".urlencode($tgl_akhir)."&p=".urlencode($produk)."&jt=".urlencode($jenistrans));
			curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
			curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl_handle, CURLOPT_USERAGENT, 'MY COMPANY');
			$process_result = json_decode(curl_exec($curl_handle));
			curl_close($curl_handle);

			echo "<script>alert('Process Done'); window.close();</script>";
	        
	    }
	    elseif (isset($_POST['submitProcess3Bln'])) {
	    	echo "Please wait the data is being processed";
			set_time_limit(60);

	        $curl_handle=curl_init();
			curl_setopt($curl_handle, CURLOPT_URL,$this->API_URL."/LaporanPenjualanQty/ProcessData3Bulan?api=".$api."&div=".urlencode($divisi)."&start=".urlencode($tgl_awal)."&end=".urlencode($tgl_akhir)."&p=".urlencode($produk)."&jt=".urlencode($jenistrans));
			curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
			curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl_handle, CURLOPT_USERAGENT, 'MY COMPANY');
			$process_result = json_decode(curl_exec($curl_handle));
			curl_close($curl_handle);

			echo "<script>alert('Process (for the past 3 months) Done'); window.close();</script>";
	    }
	    elseif (isset($_POST['cmdPreviewData'])) {
	    	//proses data
	    	set_time_limit(60);
	  		// $curl_handle=curl_init();
			// curl_setopt($curl_handle, CURLOPT_URL,$this->API_URL."LaporanPenjualanQty/ProcessData?api=".$api."&div=".urlencode($divisi)."&start=".urlencode($tgl_awal)."&end=".urlencode($tgl_akhir)."&p=".urlencode($produk)."&jt=".urlencode($jenistrans));
			// curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
			// curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
			// curl_setopt($curl_handle, CURLOPT_USERAGENT, 'MY COMPANY');
			// $process_result = json_decode(curl_exec($curl_handle));
			// curl_close($curl_handle);

			$process_result = $this->curl($this->API_URL."/LaporanPenjualanQty/ProcessData?api=".$api."&div=".urlencode($divisi)."&start=".urlencode($tgl_awal)."&end=".urlencode($tgl_akhir)."&p=".urlencode($produk)."&jt=".urlencode($jenistrans));

			// echo $this->API_URL."LaporanPenjualanQty/ProcessData?api=".$api."&div=".urlencode($divisi)."&start=".urlencode($tgl_awal)."&end=".urlencode($tgl_akhir)."&p=".urlencode($produk)."&jt=".urlencode($jenistrans);

			// echo $process_result;
			
			//ambil data wilayah dan data barang
			$data['wilayah'] = json_decode(file_get_contents($this->API_URL."/LaporanPenjualanQty/GetListWilayah?api=".$api."&div=".urlencode($divisi)."&start=".urlencode($tgl_awal)."&end=".urlencode($tgl_akhir)."&p=".urlencode($produk)."&jt=".urlencode($jenistrans)));
			$data['barang'] = json_decode(file_get_contents($this->API_URL."/LaporanPenjualanQty/GetListBarang?api=".$api."&div=".urlencode($divisi)."&start=".urlencode($tgl_awal)."&end=".urlencode($tgl_akhir)."&p=".urlencode($produk)."&jt=".urlencode($jenistrans)));

	    	//print data
	    	set_time_limit(60);
	  		// $curl_handle=curl_init();
			// curl_setopt($curl_handle, CURLOPT_URL,$this->API_URL."LaporanPenjualanQty/PrintData?api=".$api."&div=".urlencode($divisi)."&start=".urlencode($tgl_awal)."&end=".urlencode($tgl_akhir)."&p=".urlencode($produk)."&jt=".urlencode($jenistrans));
			// curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
			// curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
			// curl_setopt($curl_handle, CURLOPT_USERAGENT, 'MY COMPANY');
			// $data['data'] = json_decode(curl_exec($curl_handle));
			// curl_close($curl_handle);
	    	
	    	$data['data'] = json_decode(file_get_contents($this->API_URL."/LaporanPenjualanQty/PrintData?api=".$api."&div=".urlencode($divisi)."&start=".urlencode($tgl_awal)."&end=".urlencode($tgl_akhir)."&p=".urlencode($produk)."&jt=".urlencode($jenistrans)));

			// echo $this->API_URL."LaporanPenjualanQty/PrintData?api=".$api."&div=".urlencode($divisi)."&start=".urlencode($tgl_awal)."&end=".urlencode($tgl_akhir)."&p=".urlencode($produk)."&jt=".urlencode($jenistrans);
			// ambil data tahun lalu
			// set_time_limit(60);
	  		// $curl_handle=curl_init();
			// curl_setopt($curl_handle, CURLOPT_URL,$this->API_URL."LaporanPenjualanQty/PrintData?api=".$api."&div=".urlencode($divisi)."&start=".urlencode($tgl_awal_thn_lalu)."&end=".urlencode($tgl_akhir_thn_lalu)."&p=".urlencode($produk)."&jt=".urlencode($jenistrans));
			// curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
			// curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
			// curl_setopt($curl_handle, CURLOPT_USERAGENT, 'MY COMPANY');
			// $data['data_thn_lalu'] = json_decode(curl_exec($curl_handle));
			// curl_close($curl_handle);

			//$data['barang_thn_lalu'] = json_decode(file_get_contents($this->API_URL."LaporanPenjualanQty/GetListBarang?api=".$api."&div=".urlencode($divisi)."&start=".urlencode($tgl_awal_thn_lalu)."&end=".urlencode($tgl_akhir_thn_lalu)."&p=".urlencode($produk)."&jt=".urlencode($jenistrans)));

			$data['tglawal'] = $tgl_awal;
			$data['tglakhir'] = $tgl_akhir;
			//$data['tglawal_thnlalu'] = $tgl_awal_thn_lalu;
			//$data['tglakhir_thnlalu'] = $tgl_akhir_thn_lalu;

			$data['bulan'] = $arrbulan;
			$data['divisi'] = $divisi;

			// print_r($data);
			// exit(1);
			$this->RenderView('LaporanPenjualanQtyResultView',$data);
	    }

		
	}

	public function Preview2(){
		$monStart = $this->input->post('monStart');
		$monEnd = $this->input->post('monEnd');
		$divisi = $this->input->post('selDivisi'); 
		$divisi = trim($divisi);

		$data['divisi'] = $divisi;

		if($monStart > $monEnd){
			echo "<script>alert('Generate data error! Tanggal awal harus sebelum tanggal akhir.'); window.close();</script>";
		}

		$produk = $_POST['radLokalImport'];
		$jenistrans = $_POST['radJenisTrans'];

		$temp_awal = '01';
		$temp_akhir = date("t", strtotime($monEnd));

		$thn_awal = date("Y", strtotime($monStart));
		$thn_akhir = date("Y", strtotime($monEnd));

		if($thn_awal != $thn_akhir){
			echo "<script>alert('Generate data error! Report hanya support data pada tahun yang sama.'); window.close();</script>";
		}

		$bulan_awal = date("m", strtotime($monStart));
		$bulan_akhir = date("m", strtotime($monEnd));
		$arrbulan = array();
		

		for($i=$bulan_awal;$i<=$bulan_akhir;$i++){
			array_push($arrbulan, str_pad($i,2,'0',STR_PAD_LEFT));
		}

		$data['bulan'] = $arrbulan;

		$tgl_awal = date("Y", strtotime($monStart)).date("m", strtotime($monStart)).$temp_awal;
		$tgl_akhir = date("Y", strtotime($monEnd)).date("m", strtotime($monEnd)).$temp_akhir;

		$api = 'APITES';

		if($produk == 'LOKIMP')
			$produk = '';	

		set_time_limit(60);
		$data['data'] = json_decode($this->curl($this->API_URL."/LaporanPenjualanQty/ProcessData2?api=".$api."&div=".urlencode($divisi)."&tahun=".urlencode((int)$thn_awal)."&blnawal=".urlencode((int)$bulan_awal)."&blnakhir=".urlencode((int)$bulan_akhir)."&produk=".urlencode($produk)));
	
		$data['wilayah'] = json_decode(file_get_contents($this->API_URL."/LaporanPenjualanQty/GetListWilayah2?api=".$api));
		$data['barang'] = json_decode(file_get_contents($this->API_URL."/LaporanPenjualanQty/GetListBarang2?api=".$api."&hapustemp=1"));

		$data['tglawal'] = $tgl_awal;
		$data['tglakhir'] = $tgl_akhir;
		$data['tahun'] = $thn_awal;
		
		$this->RenderView('LaporanPenjualanQtyResult2View',$data);
		// phpinfo();
		
	}

	function curl($url) {
	    $ch = curl_init();
	    curl_setopt ( $ch, CURLOPT_URL, $url);
	    curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 0 );
	    $data = curl_exec( $ch );

	    if (curl_errno ( $ch )) {
		    echo curl_error ( $ch );
		    curl_close ( $ch );
		    exit ();
		}

	    curl_close( $ch );

	    return $data;
	}
}