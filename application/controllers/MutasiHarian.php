<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MutasiHarian extends MY_Controller 
{

	public $excel_flag = 0;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('ModuleModel');
		$this->load->model('MutasiHarianModel');
		$this->load->model('HelperModel');
		$this->load->model('GzipDecodeModel');
	}
	
	public function index()
	{
		// $this->ModuleModel->CheckAccess($this->uri->segment(1), '');
		// if($_SESSION["can_read"]==true){
			$data['title'] = 'LAPORAN BUKU MUTASI HARIAN | '.WEBTITLE;
			$data['branches'] = '';
			$data['months'] = $this->HelperModel->GetMonths();

			$gudang = file_get_contents($_SESSION['conn']->AlamatWebService.API_BKT."/MasterGudang/GetList?api=APITES");
			$gudang = $this->GzipDecodeModel->_decodeGzip($gudang);
			$data['gudang'] = $gudang;
			$this->RenderView('MutasiHarianView',$data);
		// }else{
		// 	redirect("Dashboard");
		// }
	}

	public function proses()
	{	
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

		if(!empty($_POST) && $_SESSION["can_read"]==true){

			$awal = urldecode($this->input->post("awal"));
			$akhir = urldecode($this->input->post("akhir"));
			$kode_transaksi= urldecode($this->input->post("kode_transaksi"));
			$type_cetak = urldecode($this->input->post("type_cetak"));
			$kategori_barang = urldecode($this->input->post("kategori_barang"));
			$gudang = urldecode($this->input->post("gudang"));


			$data = [
			"awal" => $awal,
			"akhir" => $akhir,
			"kode_transaksi"=> $kode_transaksi,
			"type_cetak" => $type_cetak,
			"kategori_barang" => $kategori_barang,
			"gudang" => $gudang
			];
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $_SESSION["conn"]->AlamatWebService.API_BKT.'/MutasiHarian/GetData?api=APITES');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			$response = curl_exec($ch);
			$data = json_decode($response);

			if($data->result=='sukses'){
				
				//ini_set('max_execution_time', '30');
				ini_set("pcre.backtrack_limit", "10000000");
				set_time_limit(60);

				require_once __DIR__ . '\vendor\autoload.php';
				$mpdf = new \Mpdf\Mpdf(array(
					'mode' => '',
					'format' => 'A4',
					'default_font_size' => 8,
					'default_font' => 'arial',
					'margin_left' => 10,
					'margin_right' => 10,
					'margin_top' => 30,
					'margin_bottom' => 10,
					'margin_header' => 10,
					'margin_footer' => 5,
					'orientation' => 'P'
				));

				
				$header  ='<table border="0" width="100%">';
				$header .='<tr><td align="right">Page {PAGENO} of {nb}</td></tr>'; 
				$header .='</table>';
				$header .= '<table border="0" style="width:100%; font-size:15px;">
						<tr>
							<td valign="top" rowspan="2">
								<b>
									LAPORAN BUKU MUTASI HARIAN
								</b>
							</td>
							<td style="font-size: 10px;" align="right">Tanggal Awal : '.$awal.'</td>
						</tr>
						<tr>
							<td style="font-size: 10px;" align="right">Tanggal Akhir : '.$akhir.'</td>
						</tr>
					</table>';

					$content='';

					$tam_Gudang_Sumber='';
					$tam_nomor_mutasi='';

					for($i=0; $i<count($data->data); $i++){


						if($tam_nomor_mutasi!=$data->data[$i]->No_Mutasi && $tam_nomor_mutasi!==''){				

							$content .= '<tr>
											<td colspan="2" valign="top">'.$data->data[$i]->Kd_Trn.' '.$data->data[$i]->Type_Cetak.' '.$data->data[$i]->Ket.'</td>
											<td colspan="2" valign="top">Keluar ke : '.$data->data[$i]->Nm_GdgTarget.'</td>
										</tr>';
							$content .= '</table>';
						}



						if($tam_Gudang_Sumber!=$data->data[$i]->Gudang_Sumber && $tam_Gudang_Sumber!==''){
							
							$content .= '</div>';
						}



						if($tam_Gudang_Sumber!=$data->data[$i]->Gudang_Sumber){
							$tam_Gudang_Sumber=$data->data[$i]->Gudang_Sumber;
							
							$content .= '<div style="page-break-after:always">
										<table width="100%">
											<tr>
												<td>
													'.$data->data[$i]->Nm_GdgSumber.'
												</td>
											</tr>
										</table>';

						}



						if($tam_nomor_mutasi!=$data->data[$i]->No_Mutasi){
							$content .= ' <table width="100%">
												<tr>
													<td>
														'.$data->data[$i]->Merk.'
													</td>
												</tr>
											</table>';

							$content .= '<table style="width:100%; border-collapse: collapse; font-size: 9px;" border="1">
											<tr>
												<td style="text-align: left; width: 120px; padding:5px;">Tanggal</td>
												<td style="text-align: left; width: 120px; padding:5px;">No Mutasi</td>
												<td style="text-align: left; padding:5px;">Nama Barang</td>
												<td style="text-align: left; width: 100px; padding:5px;" align="right">Qty</td>
											</tr>';
						}		

							if($tam_nomor_mutasi!=$data->data[$i]->No_Mutasi){ 
								$Tgl_Mutasi = date_format(date_create($data->data[$i]->Tgl_Mutasi),'d-M-Y'); 
							}else{
								$Tgl_Mutasi = '';
							}

							if($tam_nomor_mutasi!=$data->data[$i]->No_Mutasi){ 
								$No_Mutasi = $data->data[$i]->No_Mutasi; 
							}else{
								$No_Mutasi = '';
							}

							$content .= '<tr>';

							if($Tgl_Mutasi!==''){
												$content .= '<td style="text-align: left; padding:5px;" valign="top">
													'.$Tgl_Mutasi.'
													</td>
													<td style="text-align: left; padding:5px;" valign="top">
														'.$No_Mutasi.'
													</td>';
											}else{
												$content .= '<td colspan="2"></td>';
											}

							$content .= '<td style="text-align: left; padding:5px;" valign="top">
												'.$data->data[$i]->Kd_Brg.' '.$data->data[$i]->Nm_GdgSumber.'
											</td>
											<td style="text-align: left; padding:5px;" align="right" valign="top">
												'.$data->data[$i]->Qty.'
											</td>
										</tr>';

							if($i==count($data->data)-1){
								$content .= '<tr>
											<td colspan="2" valign="top">'.$data->data[$i]->Kd_Trn.' '.$data->data[$i]->Type_Cetak.' '.$data->data[$i]->Ket.'</td>
											<td colspan="2" valign="top">Keluar ke : '.$data->data[$i]->Nm_GdgTarget.'</td>
										</tr></table></div>';
							}

						if($tam_nomor_mutasi!=$data->data[$i]->No_Mutasi){
							$tam_nomor_mutasi=$data->data[$i]->No_Mutasi;
						}

					}
						


				set_time_limit(60);
				$mpdf->SetHTMLHeader($header,'','1');
				$mpdf->WriteHTML($content);
				$mpdf->Output();

			}else{
				redirect("MutasiHarian");
			}
		}else{
			redirect("MutasiHarian");
		}

	}
}
?>