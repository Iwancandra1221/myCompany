<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RekapMsProdukSparepart extends MY_Controller 
{
	public $excel_flag = 0; 
	public function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('HelperModel');
		$this->load->helper('FormLibrary');
		$this->load->library('email');
		$this->load->library('excel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}
	private function _postRequest($url,$data){
		// $options = array(
		//     'http' => array(
		//    	 	'method' => 'POST',
		//     	'content' => http_build_query($data),
		//     	'header'  => "Content-type: application/x-www-form-urlencoded\r\n".
		//     		"User-Agent:MyAgent/1.0\r\n".
		//     		'Cookie: ' . $_SERVER['HTTP_COOKIE']."\r\n",
		// 	),
		    
		// );
		// $stream = stream_context_create($options);
		// $getContent = file_get_contents($url, false, $stream);
		// $result = json_decode($getContent,true);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
		//curl_setopt($ch, CURLOPT_POSTFIELDS,"postvar1=value1&postvar2=value2&postvar3=value3");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec($ch);

		$result = json_decode($server_output,true);
		return $result;
	}
	public function index()
	{ 	
		$submit = $this->input->post('submit');
		// echo '<pre>';
		// print_r($_POST);
		// echo '</pre>';
		$api = 'APITES'; 
		if($submit==null){
			$url = $this->API_URL."/MsDivisi/GetListDivisi?api=".$api;
			$getDivisi = HttpGetRequest($url, $this->API_URL, "Ambil List Partner Type");
			$getDivisi = json_decode($getDivisi,true); 

			$url = $this->API_URL."/MsBarang/GetMerkList2?api=".$api;
			$getMerk = HttpGetRequest($url, $this->API_URL, "Ambil List Partner Type");
			$getMerk = json_decode($getMerk,true); 

			$url = $this->API_URL."/MsBarang/GetMsJenisBrg?api=".$api;
			$getJnsBrg = HttpGetRequest($url, $this->API_URL, "Ambil List Partner Type");
			$getJnsBrg = json_decode($getJnsBrg,true);
			if($getJnsBrg!=null && $getJnsBrg['result']=='sukses'){
				$getJnsBrg = $getJnsBrg['data'];
			}	
			$data = array(
				'title' =>  'REKAP MASTER PRODUK SPAREPART | '.WEBTITLE,
				'formDest' => 'RekapMsProdukSparepart',
				'divisi' => $getDivisi,
				'merk' => $getMerk,
				'jnsBrg' => $getJnsBrg,
			);
			
			$this->RenderView('RekapMsProdukSparepartView',$data);
		}
		else{
			$kategori = $this->input->post('kategori');
			$group = $this->input->post('group');
			$jnsBrg = $this->input->post('jns_brg');
			$merk = $this->input->post('merk');
			$divisi = $this->input->post('divisi');
			$status = $this->input->post('status');
			$tgl = $this->input->post('tgl');

			$payload = array(
				'api' => $api,
				'kategori' => $kategori,
				'group' => $group,
				'jns_brg' => $jnsBrg,
				'merk' => $merk,
				'divisi' => $divisi,
				'status' => $status,
			);
			$url = $this->API_URL."/MsProdukSparepart/RekapMsProdukSparepart";
			$data = array();
			$rekap = array();
			$rekapTmp = $this->_postRequest($url,$payload);
			// echo '<pre>';
			// print_r($rekapTmp);
			// echo '</pre>';
			switch($submit){
				case 'EXPORT EXCEL':
					if($kategori=='produk'){
						//PRODUK=====================
						if($rekapTmp!=''){
							foreach ($rekapTmp as $key => $value) {
								$rekap[$value['Jns_Brg']][] = $value;
							}
						}
						// echo '<pre>';
						// print_r($rekap);
						// echo '</pre>';
						$data['rekap'] = $rekap;
						$this->load->view('template_xls/RekapMsProdukXls.php',$data);
					}
					else{
						//SPAREPART============================
						if($rekapTmp!=''){
							foreach ($rekapTmp as $key => $value) {
								$rekap[$value['Jns_sparepart']][] = $value;
							}
						}
						$data['rekap'] = $rekap;
						$this->load->view('template_xls/RekapMsSparepartXls.php',$data);
					}
				break;
				case 'EXPORT PDF':
					require_once __DIR__ . '\vendor\autoload.php';
					$mpdf = new \Mpdf\Mpdf(array(
						'mode' => '',
						'format' => 'A4',
						'default_font_size' => 8,
						'default_font' => 'tahoma',
						'margin_left' => 10,
						'margin_right' => 10,
						'margin_top' => 40,
						'margin_bottom' => 10,
						'margin_header' => 10,
						'margin_footer' => 0,
						'orientation' => 'L',
					));
					$content = null;
					$header = '';
					if($rekapTmp!=null){
						if($kategori=='produk'){
							//PRODUK=====================
							foreach ($rekapTmp as $key => $value) {
								$rekap[$value['Jns_Brg']][] = $value;
							}
							// echo '<pre>';
							// print_r($rekap);
							// echo '</pre>';
							$iterasi = 0;
							foreach($rekap as $key => $value){
								$data['rekap'] = $value;

								$content = $this->load->view('template_pdf/RekapMsProdukPdf',$data,true);
								$header = <<<HTML
									<p style="margin:0 0;text-align:right;">Daftar Produk</p>
									<p style="margin:0 0;text-align:right;">Tanggal = {$tgl}</p>
									<h1 style="margin:10 0mm;margin-top:0px;width:100%;text-align:center;">DAFTAR PRODUK</h1>
									
									<table style="width:297mm">
										<tr>
											<th style="text-align: left; width:22.8461538462mm;font-size: medium;">Kode Barang</th>
											<th style="text-align: left; width:22.8461538462mm;font-size: medium;">Nama Barang</th>
											<th style="text-align: left; width:22.8461538462mm;font-size: medium;">HS Code</th>
											<th style="text-align: left; width:22.8461538462mm;font-size: medium;">Harga Jual</th>
											<th style="text-align: left; width:22.8461538462mm;font-size: medium;">Disc 1</th>
											<th style="text-align: left; width:22.8461538462mm;font-size: medium;">Disc 2</th>
											<th style="text-align: left; width:22.8461538462mm;font-size: medium;">Disc 3</th>
											<th style="text-align: left; width:22.8461538462mm;font-size: medium;">Tgl Ganti Harga</th>
											<th style="text-align: left; width:22.8461538462mm;font-size: medium;">Aktif</th>
											<th style="text-align: left; width:22.8461538462mm;font-size: medium;">User Name</th>
											<th style="text-align: left; width:22.8461538462mm;font-size: medium;">Last Update</th>
											<th style="text-align: left; width:22.8461538462mm;font-size: medium;">Divisi</th>
											<th style="text-align: left; width:22.8461538462mm;font-size: medium;">Tipe Brg</th>
										</tr>
									</table>
HTML;
								
								$mpdf->SetHTMLHeader($header,'','1'); //Yang diulang di setiap awal halaman  (Header)
								$mpdf->WriteHTML($content);
								if($iterasi>$key)
									$mpdf->AddPage();

								$iterasi+=1;
							}
							
						}

						else{ 
							//SPAREPART============================
							foreach ($rekapTmp as $key => $value) {
								$rekap[$value['Jns_sparepart']][] = $value;
							}
							$iterasi = 0;
							foreach($rekap as $value){
								$data['rekap'] = $value;

								$content = $this->load->view('template_pdf/RekapMsSparepartPdf',$data,true);
								$header = <<<HTML
									<p style="margin:0 0;text-align:right;">Daftar Sparepart</p>
									<p style="margin:0 0;text-align:right;">Tanggal = {$tgl}</p>
									<h1 style="margin:10 0mm;margin-top:0px;width:100%;text-align:center;">DAFTAR SPAREPART</h1>
									
									<table style="width:297mm">
										<tr>
											<th style="text-align: left; width:33mm;font-size: medium;">Kode Sparepart</th>
											<th style="text-align: left; width:33mm;font-size: medium;">Nama Sparepart</th>
											<th style="text-align: left; width:33mm;font-size: medium;">Harga Jual</th>
											<th style="text-align: left; width:33mm;font-size: medium;">Disc 1</th>
											<th style="text-align: left; width:33mm;font-size: medium;">Disc 2</th>
											<th style="text-align: left; width:33mm;font-size: medium;">Disc 3</th>
											<th style="text-align: left; width:33mm;font-size: medium;">Aktif</th>
											<th style="text-align: left; width:33mm;font-size: medium;">User Name</th>
											<th style="text-align: left; width:33mm;font-size: medium;">Last Update</th>
										</tr>
									</table>
HTML;
								
								$mpdf->SetHTMLHeader($header,'','1'); //Yang diulang di setiap awal halaman  (Header)
								$mpdf->WriteHTML($content);
								if($iterasi>$key)
									$mpdf->AddPage();

								$iterasi+=1;
							}
						}
					}

					$mpdf->Output();
				break;
			}
		}
		
	}
}
