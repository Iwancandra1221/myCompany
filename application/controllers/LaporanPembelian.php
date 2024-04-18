<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class LaporanPembelian extends MY_Controller 
	{
		public $excel_flag = 0; 
		public function __construct()
		{
			parent::__construct();
			$this->load->model('BranchModel');
			$this->load->model('HelperModel');
			$this->load->model('ActivityLogModel');
			$this->load->model('GzipDecodeModel');
			$this->load->helper('FormLibrary');
			$this->load->library('email');
			$this->load->library('excel');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		}
		
		public function index()
		{ 
			$api = 'APITES'; 

			$url = $this->API_URL."/MsSupplier/getListSupplierFor7A?api=".$api;
			$getListSupplier = HttpGetRequest($url, $this->API_URL, "Ambil List Supplier");
			$getListSupplier = $this->GzipDecodeModel->_decodeGzip($getListSupplier);
			// $getListSupplier = json_decode($getListSupplier); 

			$data['listsupplier'] = $getListSupplier; 

			$url = $this->API_URL."/MsGudang/GetListGudang7A?api=".$api;
			$getlistgudang = HttpGetRequest($url, $this->API_URL, "Ambil List Gudang");
			$getlistgudang = $this->GzipDecodeModel->_decodeGzip($getlistgudang);
			// $getlistgudang = json_decode($getlistgudang); 

			$data['listgudang'] = $getlistgudang; 


			// $url = $this->API_URL."/MsBarang/GetBarangList7A?api=".$api;
			// $getListBarang = HttpGetRequest($url, $this->API_URL, "Ambil List Barang"); 
			// $getListBarang = json_decode($getListBarang); 

			// $data['listbarang'] = $getListBarang; 


			// $url = $this->API_URL."/MsBarang/GetSparepartList7A?api=".$api;
			// $getListSparepart = HttpGetRequest($url, $this->API_URL, "Ambil List Sparepart"); 
			// $getListSparepart = json_decode($getListSparepart); 

			// $data['listsparepart'] = $getListSparepart; 
			 

			$data['title'] = 'CETAK DAFTAR PELANGGAN | '.WEBTITLE;
			$data['formDest'] = "LaporanPembelian/Proses";
			
			$this->RenderView('LaporanPembelianView',$data);

			$params = array(); 
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN PEMBELIAN";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN PEMBELIAN";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

		}
		
		public function GetListSupplier()
		{
			$api = 'APITES'; 
			$status = $this->input->get('status');
			$url = $this->API_URL."/MsSupplier/getListSupplierFor7A?api=".$api."&status=".$status;
			$data = HttpGetRequest($url, $this->API_URL, "Ambil List Supplier");
			$data = json_decode($data);   
			echo json_encode($data);
		} 
		 
		public function GetListBarang()
		{
			// $api = 'APITES';  
			// $url = $this->API_URL."/MsBarang/GetBarangList7A?api=".$api;
			// $data = HttpGetRequest($url, $this->API_URL, "Ambil List Barang"); 
			// $data = json_decode($data);   
			// echo json_encode($data);
			
			
		
			$param = $_GET;
			$api = 'APITES';  
			$url = $this->API_URL."/MsBarang/GetBarangList7A?";
			$data = file_get_contents($url.http_build_query($param));
			echo $data;
			
			
		} 
		
		public function GetListSparepart()
		{
			$api = 'APITES';  
			$url = $this->API_URL."/MsBarang/GetSparepartList7A?api=".$api;
			$data = HttpGetRequest($url, $this->API_URL, "Ambil List Sparepart"); 
			$data = json_decode($data);   
			echo json_encode($data);
		} 

		public function Proses()
		{   
			$cetaklaporan = $_POST['cetaklaporan'];  
			$list_kode_gudang = $_POST['list_kode_gudang']; 

			$dp1 = $_POST["dp1"];
			$dp2 = $_POST["dp2"];

			$radioSupplier = $_POST["radioSupplier"];
			$supplier = $_POST["supplier"]; 

			$Kategori_Barang = $_POST["Kategori_Barang"];

			$kdbrg = $_POST["kdbrg"];

			//die($cetaklaporan." ".$dp1." ".$dp2." ".$radioSupplier." ".$supplier." ".$Kategori_Barang." ".$kdbrg." ".$list_kode_gudang);
 			
 			if ($cetaklaporan=="rp1")
 			{ 
 				$this->Export_Pdf_RP1($cetaklaporan,$dp1,$dp2,$radioSupplier,$supplier,$Kategori_Barang,$kdbrg,$list_kode_gudang); 
 			}
 			else if ($cetaklaporan=="rp2") 
 			{
 				$this->Export_Pdf_RP2($cetaklaporan,$dp1,$dp2,$radioSupplier,$supplier,$Kategori_Barang,$kdbrg,$list_kode_gudang); 
 			}
 			else if ($cetaklaporan=="rp3") 
 			{
 				$this->Export_Pdf_RP3($cetaklaporan,$dp1,$dp2,$radioSupplier,$supplier,$Kategori_Barang,$kdbrg,$list_kode_gudang); 
 			}
 			else if ($cetaklaporan=="rp4") 
 			{
 				$this->Export_Pdf_RP4($cetaklaporan,$dp1,$dp2,$radioSupplier,$supplier,$Kategori_Barang,$kdbrg,$list_kode_gudang); 
 			}
			 
		} 
 
		public function Export_Pdf_RP1_pdf($tgl1, $tgl2, $ket, $supplier, $kategori_barang,$kdbrg,$list_kode_gudang)
		{ 

			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'MENAMPILKAN LAPORAN PEMBELIAN');

			//ini_set('max_execution_time', '30');
			ini_set("pcre.backtrack_limit", "5000000");
			$api = 'APITES';


			$url = $_SESSION["conn"]->AlamatWebService;

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->API_URL."/LaporanPembelian/Export_7A_Laporan_Analisa_Pembelian?api=".$api."&tgl1=".urlencode($tgl1)."&tgl2=".urlencode($tgl2)."&ket=".urlencode($ket)."&supplier=".urlencode($supplier)."&kategori_barang=".urlencode($kategori_barang)."&kdbrg=".urlencode($kdbrg)."&list_kode_gudang=".urlencode($list_kode_gudang),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60,
				// CURLOPT_IGNORE_CONTENT_LENGTH => 1,
			));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $result;die;
			$result = json_decode($result,true);

      		if(count($result)==0){
				echo "Tidak ada data";
				$this->Logs_Update($LogDate,'FAILED - Data Tidak Ditemukan','MENAMPILKAN LAPORAN PEMBELIAN');
				die;
			}

			$nama_laporan = 'Analisa Pembelian Per Kode Barang'; 
			$html = '';


			$html .='
				<style>
				.table-bordered {border-collapse:collapse}
				.table-bordered th, .table-bordered td{border-left:0.5px solid #555;border-bottom:0.5px solid #555;padding:2px}
				.border{border-top:0.5px solid #555;border-right:0.5px solid #555;}
				.bold{font-weight:bold;}
				.right{text-align:right;}
				.center{text-align:center;}
				.bigXL{font-size:180%;}
				.big{font-size:150%;}
				.italic{font-style:italic;}
				.w100{width:100%;}
				.mb-10{margin-bottom:10px}
				td{vertical-align:top}
				.group{page-break-inside:avoid;}
				</style>
			'; 

			$totalgudang = 0;
			$totalsupplier = 0;
			$grandtotal = 0;

			$nm_sup = "empty";
			$nm_wil = "empty";
			$kd_gud = "empty";
			$nm_gud = "empty";
			$i = 0;
			$html .='<div class="group">';
			foreach($result['detail'] as $data){

				if ($nm_sup <> trim($data['Nm_Supl']))
				{ 
					if ($nm_sup <> "empty")
					{ 	 
						$html .=' 
							<tr>
								<td colspan="6" class="right">  
									<b> '.$kd_gud.'</b> 
								</td>
								<td class="right">
									<b>'.number_format($totalgudang,2).'</b>
								</td>
							</tr>
							<tr>
								<td colspan="6" class="right">  
									<b> '.$nm_sup.'</b> 
								</td>
								<td class="right">
									<b>'.number_format($totalsupplier,2).'</b>
								</td>
							</tr>
						';  
						$totalgudang = 0;
						$totalsupplier = 0;
						$html .='</table>'; 
						$kd_gud = "empty";
					}

					$html .='<br>
					<table>
					<tr>
					<td><b>'.trim($data['Nm_Supl']).'</b>
					</td>
					</tr>
					</table>
					'; 
				}	 
				if ($kd_gud<>trim($data['Nm_Gudang']))
				{
					if ($kd_gud <> "empty")
					{ 	  
						$html .=' 
							<tr>
								<td colspan="6" class="right">  
									<b> '.$kd_gud.'</b> 
								</td>
								<td class="right">
									<b>'.number_format($totalgudang,2).'</b>
								</td>
							</tr>
						';  
						$totalgudang = 0; 
						$html .='</table>'; 
					}

					$html .='
					<table>
					<tr>
					<td><b>'.trim($data['Nm_Gudang']).'</b>
					</td>
					</tr>
					</table>
					'; 

					$html .='
					<table class="table-bordered w100 border">  
					'; 
					$html .=' 
					<tr>
					<td width="10%" class="center">
					<b>Kode Barang</b>
					</td>
					<td width="20%" class="center">
					<b>Nama</b>
					</td>
					<td width="10%" class="center">
					<b>Qty</b>
					</td>
					<td width="15%" class="center">
					<b>Harga</b>
					</td>
					<td width="15%" class="center">
					<b>DPP</b>
					</td>
					<td width="15%" class="center">
					<b>PPN</b>
					</td>
					<td width="15%" class="center">
					<b>Jumlah</b>
					</td>
					</tr>
					';  
				}		 

					$html .=' 
					<tr>
					<td >
					'.trim($data['Kd_Brg']).' 
					</td>
					<td >
					'.trim($data['Nm_Brg']).'
					</td>
					<td class="right">
					'.trim($data['Qty']).'
					</td>
					<td class="right">
					'.number_format($data['Harga'],2).'
					</td>
					<td class="right">
					'.number_format($data['DPP'],2).'
					</td>
					<td class="right">
					'.number_format($data['PPN'],2).'
					</td>
					<td class="right">
					'.number_format($data['Total'],2).'
					</td>
					</tr>
					';  

				$nm_sup = trim($data['Nm_Supl']);
				$kd_gud = trim($data['Nm_Gudang']);
 
				$i++;
				$totalgudang = $totalgudang + $data['Total'];
				$totalsupplier = $totalsupplier + $data['Total'];
				$grandtotal = $grandtotal + $data['Total'];
			}

					$html .=' 
					<tr>
						<td colspan="6" class="right">  
							<b> '.$kd_gud.'</b> 
						</td>
						<td class="right">
							<b>'.number_format($totalgudang,2).'</b>
						</td>
					</tr>
					<tr>
						<td colspan="6" class="right">  
							<b> '.$nm_sup.'</b> 
						</td>
						<td class="right">
							<b>'.number_format($totalsupplier,2).'</b>
						</td>
					</tr>
					<tr>
						<td colspan="6" class="right">  
							<b>GRAND TOTAL</b> 
						</td>
						<td class="right">
							<b>'.number_format($grandtotal,2).'</b>
						</td>
					</tr>
					';  

			$html .=' </table>'; 
			$html .='</div>'; 
			

			// echo $html;die;
			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'margin_left' => 10,
				'margin_right' => 10,
				'margin_top' => 25.4,
				'margin_bottom' => 20,
				'margin_header' => 10,
				'margin_footer' => 10,
				'orientation' => 'P'
			)); 
			$mpdf->SetHTMLHeader('
			<table width="100%">
				<tr>
					<td>
						'.date('d-M-Y H:i:s').'
					</td>
					<td class="right">
						Halaman {PAGENO} / {nbpg}
					</td>
				</tr>
			</table>
			<div class="big bold center">'.$nama_laporan.'</div>
			<div class="center mb-10">Periode '.date('d-M-Y',strtotime($tgl1)).' s/d '.date('d-M-Y',strtotime($tgl2)).'</div>
			');

			$this->Logs_Update($LogDate,'SUCCESS','MENAMPILKAN LAPORAN PEMBELIAN - ANALISA PEMBELIAN PER KODE BARANG');
			$mpdf->WriteHTML($html);
			$mpdf->Output();
		} 

		public function Export_Pdf_RP1($cetaklaporan,$tgl1, $tgl2, $ket, $supplier, $kategori_barang,$kdbrg,$list_kode_gudang)
		{
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'MENAMPILKAN LAPORAN PEMBELIAN');

			//ini_set('max_execution_time', '30');
			ini_set("pcre.backtrack_limit", "5000000");
			$api = 'APITES';


			$url = $_SESSION["conn"]->AlamatWebService;

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->API_URL."/LaporanPembelian/Export_7A_Laporan?api=".$api."&cetaklaporan=".urlencode($cetaklaporan)."&tgl1=".urlencode($tgl1)."&tgl2=".urlencode($tgl2)."&ket=".urlencode($ket)."&supplier=".urlencode($supplier)."&kategori_barang=".urlencode($kategori_barang)."&kdbrg=".urlencode($kdbrg)."&list_kode_gudang=".urlencode($list_kode_gudang),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60,
				// CURLOPT_IGNORE_CONTENT_LENGTH => 1,
			));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $result;die;
			$result = json_decode($result,true);

      		if(count($result['detail'])==0){
				echo "Tidak ada data";
				$this->Logs_Update($LogDate,'FAILED - Data Tidak Ditemukan','MENAMPILKAN LAPORAN PEMBELIAN');
				die;
			}   

			$html = '';


			$html .='
				<style>
				.table-bordered {border-collapse:collapse}
				.table-bordered th, .table-bordered td{border-left:0.5px solid #555;border-bottom:0.5px solid #555;padding:2px}
				.border{border-top:0.5px solid #555;border-right:0.5px solid #555;}
				.bold{font-weight:bold;}
				.right{text-align:right;}
				.center{text-align:center;}
				.bigXL{font-size:180%;}
				.big{font-size:150%;}
				.italic{font-style:italic;}
				.w100{width:100%;}
				.mb-10{margin-bottom:10px}
				td{vertical-align:top}
				.group{page-break-inside:avoid;}
				</style>
			'; 

			$totalgudang = 0;
			$totalsupplier = 0;
			$grandtotal = 0;

			$nm_sup = "empty";
			$nm_wil = "empty";
			$kd_gud = "empty";
			$nm_gud = "empty";
			$i = 0;
			$html .='<div class="group" style="margin-left:20px;margin-right:20px;margin-bottom:40px;" >';
			foreach($result['detail'] as $data){

				if ($nm_sup <> trim($data['Nm_Supl']))
				{ 
					if ($nm_sup <> "empty")
					{ 	 
						$html .=' 
							<tr>
								<td colspan="6" class="right">  
									<b> '.$kd_gud.'</b> 
								</td>
								<td class="right">
									<b>'.number_format($totalgudang,2).'</b>
								</td>
							</tr>
							<tr>
								<td colspan="6" class="right">  
									<b> '.$nm_sup.'</b> 
								</td>
								<td class="right">
									<b>'.number_format($totalsupplier,2).'</b>
								</td>
							</tr>
						';  
						$totalgudang = 0;
						$totalsupplier = 0;
						$html .='</table>'; 
						$kd_gud = "empty";
					}

					$html .='<br>
					<table>
					<tr>
					<td><b>'.trim($data['Nm_Supl']).'</b>
					</td>
					</tr>
					</table>
					'; 
				}	 
				if ($kd_gud<>trim($data['Nm_Gudang']))
				{
					if ($kd_gud <> "empty")
					{ 	  
						$html .=' 
							<tr>
								<td colspan="6" class="right">  
									<b> '.$kd_gud.'</b> 
								</td>
								<td class="right">
									<b>'.number_format($totalgudang,2).'</b>
								</td>
							</tr>
						';  
						$totalgudang = 0; 
						$html .='</table>'; 
					}

					$html .='
					<table>
					<tr>
					<td><b>'.trim($data['Nm_Gudang']).'</b>
					</td>
					</tr>
					</table>
					'; 

					$html .='
					<table class="table-bordered w100 border">  
					'; 
					$html .=' 
					<tr>
					<td width="10%" class="center">
					<b>Kode Barang</b>
					</td>
					<td width="45%" class="center">
					<b>Nama</b>
					</td>
					<td width="5%" class="center">
					<b>Qty</b>
					</td>
					<td width="10%" class="center">
					<b>Harga</b>
					</td>
					<td width="10%" class="center">
					<b>DPP</b>
					</td>
					<td width="10%" class="center">
					<b>PPN</b>
					</td>
					<td width="10%" class="center">
					<b>Jumlah</b>
					</td>
					</tr>
					';  
				}		 

					$html .=' 
					<tr>
					<td >
					'.trim($data['Kd_Brg']).' 
					</td>
					<td >
					'.trim($data['Nm_Brg']).'
					</td>
					<td class="right">
					'.trim($data['Qty']).'
					</td>
					<td class="right">
					'.number_format($data['Harga'],2).'
					</td>
					<td class="right">
					'.number_format($data['DPP'],2).'
					</td>
					<td class="right">
					'.number_format($data['PPN'],2).'
					</td>
					<td class="right">
					'.number_format($data['Total'],2).'
					</td>
					</tr>
					';  

				$nm_sup = trim($data['Nm_Supl']);
				$kd_gud = trim($data['Nm_Gudang']);
 
				$i++;
				$totalgudang = $totalgudang + $data['Total'];
				$totalsupplier = $totalsupplier + $data['Total'];
				$grandtotal = $grandtotal + $data['Total'];
			}

					$html .=' 
					<tr>
						<td colspan="6" class="right">  
							<b> '.$kd_gud.'</b> 
						</td>
						<td class="right">
							<b>'.number_format($totalgudang,2).'</b>
						</td>
					</tr>
					<tr>
						<td colspan="6" class="right">  
							<b> '.$nm_sup.'</b> 
						</td>
						<td class="right">
							<b>'.number_format($totalsupplier,2).'</b>
						</td>
					</tr>
					<tr>
						<td colspan="6" class="right">  
							<b>GRAND TOTAL</b> 
						</td>
						<td class="right">
							<b>'.number_format($grandtotal,2).'</b>
						</td>
					</tr>
					';  

			$html .=' </table>'; 
			$html .='</div>'; 

			$page_title = 'Analisa Pembelian Per Kode Barang'; 
			$content_html = "<html>";
			$content_html = "<head>";
			 
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "	<div><h1 style='font-size:2vw; text-align:center;'>".$page_title."</h1></div>"; 
			$content_html.= "	<div><h1 style='font-size:1vw; text-align:center;'>Periode: ".date('d-M-Y',strtotime($tgl1))." sd ".date('d-M-Y',strtotime($tgl2))."</h1></div>"; 
			$content_html.= "</div>";	//close div_header
			 
			$content_html.= "<div class='div_body' style='width:100%;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
			  
			$content_html.= $html;

			$content_html.= "</div>";
			$content_html.= "</body></html>";
			// die($content_html);
			$data['title'] = $page_title;
			$data['content_html'] = $content_html;
			// die(json_encode($data));
			// $this->load->view('LaporanResultView',$data);
			$this->Logs_Update($LogDate,'SUCCESS','MENAMPILKAN LAPORAN PEMBELIAN - ANALISA PEMBELIAN PER KODE BARANG');
			$this->RenderView('LaporanResultView',$data);

		} 

		public function Export_Pdf_RP2($cetaklaporan,$tgl1, $tgl2, $ket, $supplier, $kategori_barang,$kdbrg,$list_kode_gudang)
		{
			
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'MENAMPILKAN LAPORAN PEMBELIAN');

			//ini_set('max_execution_time', '30');
			ini_set("pcre.backtrack_limit", "5000000");
			$api = 'APITES';


			$url = $_SESSION["conn"]->AlamatWebService;

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->API_URL."/LaporanPembelian/Export_7A_Laporan?api=".$api."&cetaklaporan=".urlencode($cetaklaporan)."&tgl1=".urlencode($tgl1)."&tgl2=".urlencode($tgl2)."&ket=".urlencode($ket)."&supplier=".urlencode($supplier)."&kategori_barang=".urlencode($kategori_barang)."&kdbrg=".urlencode($kdbrg)."&list_kode_gudang=".urlencode($list_kode_gudang),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60,
				// CURLOPT_IGNORE_CONTENT_LENGTH => 1,
			));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $result;die;
			$result = json_decode($result,true);

      		if(count($result['detail'])==0){
				echo "Tidak ada data";
				$this->Logs_Update($LogDate,'FAILED - Data Tidak Ditemukan','MENAMPILKAN LAPORAN PEMBELIAN');
				die;
			}   

			$html = '';


			$html .='
				<style>
				.table-bordered {border-collapse:collapse}
				.table-bordered th, .table-bordered td{border-left:0.5px solid #555;border-bottom:0.5px solid #555;padding:2px}
				.border{border-top:0.5px solid #555;border-right:0.5px solid #555;}
				.bold{font-weight:bold;}
				.right{text-align:right;}
				.center{text-align:center;}
				.bigXL{font-size:180%;}
				.big{font-size:150%;}
				.italic{font-style:italic;}
				.w100{width:100%;}
				.mb-10{margin-bottom:10px}
				td{vertical-align:top}
				.group{page-break-inside:avoid;}
				</style>
			'; 

			$totalgudang = 0;
			$totalsupplier = 0;
			$grandtotal = 0;

			$nm_sup = "empty";
			$no_pu = "empty";
			$kd_gud = "empty";
			$no_do = "empty";
			$i = 0;
			$html .='<div class="group" style="margin-left:20px;margin-right:20px;margin-bottom:40px;" >';
			foreach($result['detail'] as $data){

				if ($nm_sup <> trim($data['Nm_Supl']))
				{ 
					if ($nm_sup <> "empty")
					{ 	 
						$html .=' 
							<tr>
								<td colspan="7" class="right">  
									<b> '.$kd_gud.'</b> 
								</td>
								<td class="right">
									<b>'.number_format($totalgudang,2).'</b>
								</td>
							</tr>
							<tr>
								<td colspan="7" class="right">  
									<b> '.$nm_sup.'</b> 
								</td>
								<td class="right">
									<b>'.number_format($totalsupplier,2).'</b>
								</td>
							</tr>
						';  
						$totalgudang = 0;
						$totalsupplier = 0;
						$html .='</table>'; 
						$kd_gud = "empty";
					}

					$html .='<br>
					<table>
					<tr>
					<td><b>'.trim($data['Nm_Supl']).'</b>
					</td>
					</tr>
					</table>
					'; 
				}	 
				if ($kd_gud<>trim($data['Nm_Gudang']))
				{
					if ($kd_gud <> "empty")
					{ 	  
						$html .=' 
							<tr>
								<td colspan="7" class="right">  
									<b> '.$kd_gud.'</b> 
								</td>
								<td class="right">
									<b>'.number_format($totalgudang,2).'</b>
								</td>
							</tr>
						';  
						$totalgudang = 0; 
						$html .='</table>'; 
					}

					$html .='
					<table>
					<tr>
					<td><b>'.trim($data['Nm_Gudang']).'</b>
					</td>
					</tr>
					</table>
					'; 

					$html .='
					<table class="table-bordered w100 border">  
					'; 
					$html .=' 
					<tr>
					<td width="10%" class="center">
					<b>No PU</b>
					</td> 
					<td width="10%" class="center">
					<b>No DO</b>
					</td> 
					<td width="10%" class="center">
					<b>Kode Barang</b>
					</td> 
					<td width="5%" class="center">
					<b>Qty</b>
					</td>
					<td width="10%" class="center">
					<b>Harga</b>
					</td>
					<td width="10%" class="center">
					<b>DPP</b>
					</td>
					<td width="10%" class="center">
					<b>PPN</b>
					</td>
					<td width="10%" class="center">
					<b>Jumlah</b>
					</td>
					</tr>
					';  
				}		 

					$html .=' 
					<tr>';
					if ($no_do <> trim($data['No_SJ']))
					{
 						$html .= '<td >
						'.trim($data['No_PU']).' 
						</td> 
						<td >
						'.trim($data['No_SJ']).' 
						</td>';
					}
					else
					{
 						$html .= '<td >  
						</td> 
						<td > 
						</td>';
					}

					$html .='
					<td >
					'.trim($data['Kd_Brg']).' 
					</td> 
					<td class="right">
					'.trim($data['Qty']).'
					</td>
					<td class="right">
					'.number_format($data['Harga'],2).'
					</td>
					<td class="right">
					'.number_format($data['DPP'],2).'
					</td>
					<td class="right">
					'.number_format($data['PPN'],2).'
					</td>
					<td class="right">
					'.number_format($data['Total'],2).'
					</td>
					</tr>
					';  

				$nm_sup = trim($data['Nm_Supl']);
				$kd_gud = trim($data['Nm_Gudang']);
				$no_do = trim($data['No_SJ']);
 
				$i++;
				$totalgudang = $totalgudang + $data['Total'];
				$totalsupplier = $totalsupplier + $data['Total'];
				$grandtotal = $grandtotal + $data['Total'];
			}

					$html .=' 
					<tr>
						<td colspan="7" class="right">  
							<b> '.$kd_gud.'</b> 
						</td>
						<td class="right">
							<b>'.number_format($totalgudang,2).'</b>
						</td>
					</tr>
					<tr>
						<td colspan="7" class="right">  
							<b> '.$nm_sup.'</b> 
						</td>
						<td class="right">
							<b>'.number_format($totalsupplier,2).'</b>
						</td>
					</tr>
					<tr>
						<td colspan="7" class="right">  
							<b>GRAND TOTAL</b> 
						</td>
						<td class="right">
							<b>'.number_format($grandtotal,2).'</b>
						</td>
					</tr>
					';  

			$html .=' </table>'; 
			$html .='</div>'; 

			$page_title = 'Pembelian Per Nomor Purchase'; 
			$content_html = "<html>";
			$content_html = "<head>";
			 
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "	<div><h1 style='font-size:2vw; text-align:center;'>".$page_title."</h1></div>"; 
			$content_html.= "	<div><h1 style='font-size:1vw; text-align:center;'>Periode: ".date('d-M-Y',strtotime($tgl1))." sd ".date('d-M-Y',strtotime($tgl2))."</h1></div>"; 
			$content_html.= "</div>";	//close div_header
			 
			$content_html.= "<div class='div_body' style='width:100%;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
			  
			$content_html.= $html;

			$content_html.= "</div>";
			$content_html.= "</body></html>";
			// die($content_html);
			$data['title'] = $page_title;
			$data['content_html'] = $content_html;
			// die(json_encode($data));
			// $this->load->view('LaporanResultView',$data);
			$this->Logs_Update($LogDate,'SUCCESS','MENAMPILKAN LAPORAN PEMBELIAN - PEMBELIAN PER NOMOR PURCHASE');
			$this->RenderView('LaporanResultView',$data);


		} 

		public function Export_Pdf_RP3($cetaklaporan,$tgl1, $tgl2, $ket, $supplier, $kategori_barang,$kdbrg,$list_kode_gudang)
		{
			
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'MENAMPILKAN LAPORAN PEMBELIAN');

			//ini_set('max_execution_time', '30');
			ini_set("pcre.backtrack_limit", "5000000");
			$api = 'APITES';


			$url = $_SESSION["conn"]->AlamatWebService;

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->API_URL."/LaporanPembelian/Export_7A_Laporan?api=".$api."&cetaklaporan=".urlencode($cetaklaporan)."&tgl1=".urlencode($tgl1)."&tgl2=".urlencode($tgl2)."&ket=".urlencode($ket)."&supplier=".urlencode($supplier)."&kategori_barang=".urlencode($kategori_barang)."&kdbrg=".urlencode($kdbrg)."&list_kode_gudang=".urlencode($list_kode_gudang),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60,
				// CURLOPT_IGNORE_CONTENT_LENGTH => 1,
			));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $result;die;
			$result = json_decode($result,true);
 
      		if(count($result['detail'])==0){
				echo "Tidak ada data";
				$this->Logs_Update($LogDate,'FAILED - Data Tidak Ditemukan','MENAMPILKAN LAPORAN PEMBELIAN');
				die;
			} 

			$html = '';


			$html .='
				<style>
				.table-bordered {border-collapse:collapse}
				.table-bordered th, .table-bordered td{border-left:0.5px solid #555;border-bottom:0.5px solid #555;padding:2px}
				.border{border-top:0.5px solid #555;border-right:0.5px solid #555;}
				.bold{font-weight:bold;}
				.right{text-align:right;}
				.center{text-align:center;}
				.bigXL{font-size:180%;}
				.big{font-size:150%;}
				.italic{font-style:italic;}
				.w100{width:100%;}
				.mb-10{margin-bottom:10px}
				td{vertical-align:top}
				.group{page-break-inside:avoid;}
				</style>
			'; 

			$totalgudang = 0;
			$totalsupplier = 0;
			$grandtotal = 0;

			$nm_sup = "empty";
			$nm_wil = "empty";
			$kd_gud = "empty";
			$nm_gud = "empty";
			$i = 0;
			$html .='<div class="group" style="margin-left:20px;margin-right:20px;margin-bottom:40px;" >';
			foreach($result['detail'] as $data){

				if ($nm_sup <> trim($data['Nm_Supl']))
				{ 
					if ($nm_sup <> "empty")
					{ 	 
						$html .=' 
							<tr>
								<td colspan="6" class="right">  
									<b> '.$kd_gud.'</b> 
								</td>
								<td class="right">
									<b>'.number_format($totalgudang,2).'</b>
								</td>
							</tr>
							<tr>
								<td colspan="6" class="right">  
									<b> '.$nm_sup.'</b> 
								</td>
								<td class="right">
									<b>'.number_format($totalsupplier,2).'</b>
								</td>
							</tr>
						';  
						$totalgudang = 0;
						$totalsupplier = 0;
						$html .='</table>'; 
						$kd_gud = "empty";
					}

					$html .='<br>
					<table>
					<tr>
					<td><b>'.trim($data['Nm_Supl']).'</b>
					</td>
					</tr>
					</table>
					'; 
				}	 
				if ($kd_gud<>trim($data['Nm_Gudang']))
				{
					if ($kd_gud <> "empty")
					{ 	  
						$html .=' 
							<tr>
								<td colspan="6" class="right">  
									<b> '.$kd_gud.'</b> 
								</td>
								<td class="right">
									<b>'.number_format($totalgudang,2).'</b>
								</td>
							</tr>
						';  
						$totalgudang = 0; 
						$html .='</table>'; 
					}

					$html .='
					<table>
					<tr>
					<td><b>'.trim($data['Nm_Gudang']).'</b>
					</td>
					</tr>
					</table>
					'; 

					$html .='
					<table class="table-bordered w100 border">  
					'; 
					$html .=' 
					<tr>
					<td width="10%" class="center">
					<b>Kode Barang</b>
					</td>
					<td width="45%" class="center">
					<b>Nama</b>
					</td>
					<td width="5%" class="center">
					<b>Qty</b>
					</td>
					<td width="10%" class="center">
					<b>Harga</b>
					</td>
					<td width="10%" class="center">
					<b>DPP</b>
					</td>
					<td width="10%" class="center">
					<b>PPN</b>
					</td>
					<td width="10%" class="center">
					<b>Jumlah</b>
					</td>
					</tr>
					';  
				}		 

					$html .=' 
					<tr>
					<td >
					'.trim($data['Kd_Brg']).' 
					</td>
					<td >
					'.trim($data['Nm_Brg']).'
					</td>
					<td class="right">
					'.trim($data['Qty']).'
					</td>
					<td class="right">
					'.number_format($data['Harga'],2).'
					</td>
					<td class="right">
					'.number_format($data['DPP'],2).'
					</td>
					<td class="right">
					'.number_format($data['PPN'],2).'
					</td>
					<td class="right">
					'.number_format($data['Total'],2).'
					</td>
					</tr>
					';  

				$nm_sup = trim($data['Nm_Supl']);
				$kd_gud = trim($data['Nm_Gudang']);
 
				$i++;
				$totalgudang = $totalgudang + $data['Total'];
				$totalsupplier = $totalsupplier + $data['Total'];
				$grandtotal = $grandtotal + $data['Total'];
			}

					$html .=' 
					<tr>
						<td colspan="6" class="right">  
							<b> '.$kd_gud.'</b> 
						</td>
						<td class="right">
							<b>'.number_format($totalgudang,2).'</b>
						</td>
					</tr>
					<tr>
						<td colspan="6" class="right">  
							<b> '.$nm_sup.'</b> 
						</td>
						<td class="right">
							<b>'.number_format($totalsupplier,2).'</b>
						</td>
					</tr>
					<tr>
						<td colspan="6" class="right">  
							<b>GRAND TOTAL</b> 
						</td>
						<td class="right">
							<b>'.number_format($grandtotal,2).'</b>
						</td>
					</tr>
					';  

			$html .=' </table>'; 
			$html .='</div>'; 

			$page_title = 'Summary Pembelian Per Gudang'; 
			$content_html = "<html>";
			$content_html = "<head>";
			 
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "	<div><h1 style='font-size:2vw; text-align:center;'>".$page_title."</h1></div>"; 
			$content_html.= "	<div><h1 style='font-size:1vw; text-align:center;'>Periode: ".date('d-M-Y',strtotime($tgl1))." sd ".date('d-M-Y',strtotime($tgl2))."</h1></div>"; 
			$content_html.= "</div>";	//close div_header
			 
			$content_html.= "<div class='div_body' style='width:100%;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
			  
			$content_html.= $html;

			$content_html.= "</div>";
			$content_html.= "</body></html>";
			// die($content_html);
			$data['title'] = $page_title;
			$data['content_html'] = $content_html;
			// die(json_encode($data));
			// $this->load->view('LaporanResultView',$data);
			$this->Logs_Update($LogDate,'SUCCESS','MENAMPILKAN LAPORAN PEMBELIAN - SUMMARY PEMBELIAN PER GUDANG');
			$this->RenderView('LaporanResultView',$data);
		} 

		public function Export_Pdf_RP4($cetaklaporan,$tgl1, $tgl2, $ket, $supplier, $kategori_barang,$kdbrg,$list_kode_gudang)
		{
			
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'MENAMPILKAN LAPORAN PEMBELIAN');

			//ini_set('max_execution_time', '30');
			ini_set("pcre.backtrack_limit", "5000000");
			$api = 'APITES';


			$url = $_SESSION["conn"]->AlamatWebService;

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->API_URL."/LaporanPembelian/Export_7A_Laporan?api=".$api."&cetaklaporan=".urlencode($cetaklaporan)."&tgl1=".urlencode($tgl1)."&tgl2=".urlencode($tgl2)."&ket=".urlencode($ket)."&supplier=".urlencode($supplier)."&kategori_barang=".urlencode($kategori_barang)."&kdbrg=".urlencode($kdbrg)."&list_kode_gudang=".urlencode($list_kode_gudang),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60,
				// CURLOPT_IGNORE_CONTENT_LENGTH => 1,
			));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $result;die;
			$result = json_decode($result,true);

      		if(count($result['detail'])==0){
				echo "Tidak ada data";
				$this->Logs_Update($LogDate,'FAILED - Data Tidak Ditemukan','MENAMPILKAN LAPORAN PEMBELIAN');
				die;
			} 

			$html = '';


			$html .='
				<style>
				.table-bordered {border-collapse:collapse}
				.table-bordered th, .table-bordered td{border-left:0.5px solid #555;border-bottom:0.5px solid #555;padding:2px}
				.border{border-top:0.5px solid #555;border-right:0.5px solid #555;}
				.bold{font-weight:bold;}
				.right{text-align:right;}
				.center{text-align:center;}
				.bigXL{font-size:180%;}
				.big{font-size:150%;}
				.italic{font-style:italic;}
				.w100{width:100%;}
				.mb-10{margin-bottom:10px}
				td{vertical-align:top}
				.group{page-break-inside:avoid;}
				</style>
			'; 

			$totalgudang = 0;
			$totalsupplier = 0;
			$grandtotal = 0;

			$nm_sup = "empty";
			$nm_wil = "empty";
			$kd_gud = "empty";
			$nm_gud = "empty";
			$i = 0;
			$html .='<div class="group" style="margin-left:20px;margin-right:20px;margin-bottom:40px;" >';
			foreach($result['detail'] as $data){

				if ($nm_sup <> trim($data['Nm_Supl']))
				{ 
					if ($nm_sup <> "empty")
					{ 	 
						$html .=' 
							<tr>
								<td colspan="5" class="right">  
									<b> '.$kd_gud.'</b> 
								</td>
								<td class="right">
									<b>'.number_format($totalgudang,2).'</b>
								</td>
							</tr>
							<tr>
								<td colspan="5" class="right">  
									<b> '.$nm_sup.'</b> 
								</td>
								<td class="right">
									<b>'.number_format($totalsupplier,2).'</b>
								</td>
							</tr>
						';  
						$totalgudang = 0;
						$totalsupplier = 0;
						$html .='</table>'; 
						$kd_gud = "empty";
					}

					$html .='<br>
					<table>
					<tr>
					<td><b>'.trim($data['Nm_Supl']).'</b>
					</td>
					</tr>
					</table>
					'; 
				}	 
				if ($kd_gud<>trim($data['Nm_Gudang']))
				{
					if ($kd_gud <> "empty")
					{ 	  
						$html .=' 
							<tr>
								<td colspan="5" class="right">  
									<b> '.$kd_gud.'</b> 
								</td>
								<td class="right">
									<b>'.number_format($totalgudang,2).'</b>
								</td>
							</tr>
						';  
						$totalgudang = 0; 
						$html .='</table>'; 
					}

					$html .='
					<table>
					<tr>
					<td><b>'.trim($data['Nm_Gudang']).'</b>
					</td>
					</tr>
					</table>
					'; 

					$html .='
					<table class="table-bordered w100 border">  
					'; 
					$html .=' 
					<tr>
					<td width="10%" class="center">
					<b>Kode Barang</b>
					</td>
					<td width="55%" class="center">
					<b>Nama</b>
					</td>
					<td width="5%" class="center">
					<b>Qty</b>
					</td>
					<td width="10%" class="center">
					<b>Harga</b>
					</td>
					<td width="10%" class="center">
					<b>DPP</b>
					</td> 
					<td width="10%" class="center">
					<b>Jumlah</b>
					</td>
					</tr>
					';  
				}		 

					$html .=' 
					<tr>
					<td >
					'.trim($data['Kd_Brg']).' 
					</td>
					<td >
					'.trim($data['Nm_Brg']).'
					</td>
					<td class="right">
					'.trim($data['Qty']).'
					</td>
					<td class="right">
					'.number_format($data['Harga'],2).'
					</td>
					<td class="right">
					'.number_format($data['DPP'],2).'
					</td> 
					<td class="right">
					'.number_format($data['Total'],2).'
					</td>
					</tr>
					';  

				$nm_sup = trim($data['Nm_Supl']);
				$kd_gud = trim($data['Nm_Gudang']);
 
				$i++;
				$totalgudang = $totalgudang + $data['Total'];
				$totalsupplier = $totalsupplier + $data['Total'];
				$grandtotal = $grandtotal + $data['Total'];
			}

					$html .=' 
					<tr>
						<td colspan="5" class="right">  
							<b> '.$kd_gud.'</b> 
						</td>
						<td class="right">
							<b>'.number_format($totalgudang,2).'</b>
						</td>
					</tr>
					<tr>
						<td colspan="5" class="right">  
							<b> '.$nm_sup.'</b> 
						</td>
						<td class="right">
							<b>'.number_format($totalsupplier,2).'</b>
						</td>
					</tr>
					<tr>
						<td colspan="5" class="right">  
							<b>GRAND TOTAL</b> 
						</td>
						<td class="right">
							<b>'.number_format($grandtotal,2).'</b>
						</td>
					</tr>
					';  

			$html .=' </table>'; 
			$html .='</div>'; 

			$page_title = 'Analisa Pembelian Per Kode Barang Exclude PPN'; 
			$content_html = "<html>";
			$content_html = "<head>";
			 
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "	<div><h1 style='font-size:2vw; text-align:center;'>".$page_title."</h1></div>"; 
			$content_html.= "	<div><h1 style='font-size:1vw; text-align:center;'>Periode: ".date('d-M-Y',strtotime($tgl1))." sd ".date('d-M-Y',strtotime($tgl2))."</h1></div>"; 
			$content_html.= "</div>";	//close div_header
			 
			$content_html.= "<div class='div_body' style='width:100%;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
			  
			$content_html.= $html;

			$content_html.= "</div>";
			$content_html.= "</body></html>";
			// die($content_html);
			$data['title'] = $page_title;
			$data['content_html'] = $content_html;
			// die(json_encode($data));
			// $this->load->view('LaporanResultView',$data);
			$this->Logs_Update($LogDate,'SUCCESS','MENAMPILKAN LAPORAN PEMBELIAN- ANALISA PEMBELIAN PER KODE BARANG EXCLUDE PPN');
			$this->RenderView('LaporanResultView',$data);
		} 

		function Logs_insert($LogDate='',$description=''){
		   $params = array();   
		   $params['LogDate'] = $LogDate;
		   $params['UserID'] = $_SESSION["logged_in"]["userid"];
		   $params['UserName'] = $_SESSION["logged_in"]["username"];
		   $params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		   $params['Module'] = "LAPORAN PEMBELIAN";
		   $params['TrxID'] = date_format(date_create($LogDate),'YmdHis');
		   $params['Description'] = $_SESSION["logged_in"]["username"]." ".$description;
		   $params['Remarks'] = "";
		   $params['RemarksDate'] = 'NULL';
		   $this->ActivityLogModel->insert_activity($params);
		}

		function Logs_Update($LogDate='',$remarks='',$description=''){
			$params = array();   
			$params['LogDate'] = $LogDate;
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN PEMBELIAN";
			$params['TrxID'] = date_format(date_create($LogDate),'YmdHis');
			$params['Description'] = $_SESSION["logged_in"]["username"]." ".$description;
			$params['Remarks']=$remarks;
		   	$params['RemarksDate'] = date("Y-m-d H:i:s");
		   	$this->ActivityLogModel->update_activity($params);
		}

	}							