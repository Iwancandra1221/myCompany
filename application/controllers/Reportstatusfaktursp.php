<?php 
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Reportstatusfaktursp extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model("ReportstatusfakturspModel");
		$this->load->library('excel');
	}

	function index(){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

		if($_SESSION["can_read"]==true){
			$data['no_faktur'] = $this->ReportstatusfakturspModel->Getnofaktur();
			$data['gudang'] = $this->ReportstatusfakturspModel->Getgudang();
			$this->RenderView('ReportstatusfakturspView',$data);
		}
	}

	function proses_data(){

		print_r($this->ReportstatusfakturspModel->proses_data($this->input->post()));

	}

	function pdf($proses='',$acak='',$DTPAwal='',$DTPAkhir='',$CboNoFaktur='',$CboTpCtk='',$CboKdGdg='',$ChkSelesai='',$ChkSmSelesai='',$OptSemua='',$OptBayar='',$OptGratis='',$ChkBatal='',$ChkSmBatal=''){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

		if($_SESSION["can_read"]==true && !empty($acak)){

			ini_set('max_execution_time', '1500');
			ini_set("pcre.backtrack_limit", "10000000");
			set_time_limit(0);


				$mpdf = new \Mpdf\Mpdf(array(
					'mode' => '',
					'format' => 'Legal',
					'default_font_size' => 8,
					'default_font' => 'arial',
					'margin_left' => 10,
					'margin_right' => 10,
					'margin_top' => 45,
					'margin_bottom' => 10,
					'margin_header' => 10,
					'margin_footer' => 5,
					'orientation' => 'P'
				));

			$header='';
			$content='';

			if($proses=='ReportGantung'){



				$data = json_decode($this->ReportstatusfakturspModel->ProsesApiData($proses,$acak));

				$header = '<table border="0" style="width:100%; font-size:15px;">
								<tr>
									<td align="right" style="font-size:10px;">
										Page : {PAGENO} of {nb}
									</td>
								</tr>
								<tr>
									<td align="right" style="font-size:10px;">
										Print : '.date('d-m-Y').'
									</td>
								</tr>
								<tr>
									<td align="center">
										<b>
											FAKTUR SPAREPART YANG MASIH GANTUNG
										</b>
									</td>
								</tr>
							</table>';

				$header .= '<table border="0" style="width:100%; font-size:10px; margin-top:20px;">
								<tr>
									<td width="80px">
										<b>
											Bulan
										</b>
									</td>
									<td width="1px"><b>:</b></td>
									<td>'.date_format(date_create($DTPAwal),'M Y').'</td>
								</tr>
								<tr>
									<td>
										<b>Periode</b>
									</td>
									<td><b>:</b></td>
									<td>'.date_format(date_create($DTPAwal),'d M Y').' s/d '.date_format(date_create($DTPAkhir),'d M Y').'</td>
								</tr>
								<tr>
									<td>
										<b>Gudang</b>
									</td>
									<td><b>:</b></td>
									<td>'.str_replace("-"," - ",str_replace("%20", " ", $CboKdGdg)).'</td>
								</tr>
							</table>';

							$content .= '<style>
											.table {
											    font-family: sans-serif;
											    color: #232323;
											    border-collapse: collapse;
											}
											 
											.table, .td {
											    border: 1px solid #999;
											    padding: 5px 20px;
											}
										</style>
										<table class="table" border="o" style="width:100%; font-size:10px; margin-top:20px;">
											<thead>
												<tr>
													<td class="td" style="width:16%" align="center">
														<b>
															No Faktur
														</b>
													</td>
													<td class="td" style="width:16%" align="center">
														<b>
															Tanggal
														</b>
													</td>
													<td class="td" style="width:16%" align="center">
														<b>
															Tipe
														</b>
													</td>
													<td class="td" style="width:16%" align="center">
														<b>
															Tanggal Bayar
														</b>
													</td>
													<td class="td" style="width:16%" align="center">
														<b>
															No Nota Service
														</b>
													</td>
													<td class="td" style="width:16%" align="center">
														<b>
															Jaminan
														</b>
													</td>
												</tr>
											</thead>';

							$content .= '<tbody>';

							if(count($data)>0){
								foreach ($data as $key => $d) {

									if(!empty($d->Tgl_Bayar)){ 
										$Tgl_Pembayaran = date_format(date_create($d->Tgl_Bayar),'d M Y'); 
									}else{
										$Tgl_Pembayaran = '';
									}

									if(!empty($d->No_Svc)){ 
										$No_Svc = $d->No_Svc; 
									}else{
										$No_Svc = '';
									}

									$content .= '<tr>';
									$content .= '<td class="td" align="center">'.$d->No_FSP.'</td>';
									$content .= '<td class="td" align="center">'.date_format(date_create($d->Tgl_FSP),'d M Y').'</td>';
									$content .= '<td class="td" align="center">'.$d->Type_Cetak.'</td>';
									$content .= '<td class="td" align="center">'.$Tgl_Pembayaran.'</td>';
									$content .= '<td class="td" align="center">'.$No_Svc.'</td>';
									$content .= '<td class="td" align="center">'.$d->Jaminan.'</td>';
									$content .= '</tr>';
								}
							}

							$content .='<tr><td class="td" colspan="6"><b>Total Transaksi : </b> '.number_format(count($data)).'</td></tr>';
							$content .='</tbody>';

							$content .= '</table>';

			}else{

				$data = json_decode($this->ReportstatusfakturspModel->ProsesApiData($proses,$acak));

				$header = '<table border="0" style="width:100%; font-size:15px;">
								<tr>
									<td align="right" style="font-size:10px;">
										Page : {PAGENO} of {nb}
									</td>
								</tr>
								<tr>
									<td align="right" style="font-size:10px;">
										Print : '.date('d-m-Y').'
									</td>
								</tr>
								<tr>
									<td align="center">
										<b>
											LAPORAN STATUS FAKTUR SPAREPART DIVISI SERVICE
										</b>
									</td>
								</tr>
							</table>';

				$header .= '<table border="0" style="width:100%; font-size:10px; margin-top:20px;">
								<tr>
									<td width="100px">
										<b>
											Bulan
										</b>
									</td>
									<td width="1px"><b>:</b></td>
									<td>'.date_format(date_create($DTPAkhir),'M Y').'</td>
								</tr>
								<tr>
									<td>
										<b>Periode Kontrol</b>
									</td>
									<td><b>:</b></td>
									<td>'.date_format(date_create($DTPAwal),'d M Y').' s/d '.date_format(date_create($DTPAkhir),'d M Y').'</td>
								</tr>
								<tr>
									<td>
										<b>Gudang</b>
									</td>
									<td><b>:</b></td>
									<td>'.str_replace("-"," - ",str_replace("%20", " ", $CboKdGdg)).'</td>
								</tr>
							</table>';

							$content .= '<style>
											.table {
											    font-family: sans-serif;
											    color: #232323;
											    border-collapse: collapse;
											}
											 
											.table, .td {
											    border-bottom: 1px solid #999;
											    padding: 5px 1px;
											}

											.table, .tdt {
												border-top: 1px solid #999;
											    padding: 3px 1px;
											}

											.table, .tdn {
											    padding: 3px 1px;
											}

											


											.table_bawah {
											    font-family: sans-serif;
											    color: #232323;
											    border-collapse: collapse;
											}

											.table_bawah .tdfull{
												border: 1px solid #999;
											    padding: 5px 1px;
											}

										</style>';

							
							if(count($data)>0){

								$bulan=0;
								
								$total=0;
								$total_semua=0;
								$jum_sudah_selesai=0;
								$jum_belum_selesai=0;	
								$total_jum_sudah_selesai=0;
								$total_jum_belum_selesai=0;
								$total_jum=0;


								$awal_bln = date('d-m-Y', strtotime('-1 month', strtotime( $DTPAwal )));

								for($i=0; $i<=3; $i++){
																				
									$awal_bln = date('d-m-Y', strtotime('+1 month', strtotime( $awal_bln )));
									$bln = date_format(date_create($awal_bln),'m')*1;

									$ttt[$bln]=0;
									$ssb[$bln]=0;
									$ssg[$bln]=0;
									$bs[$bln]=0;

								}

								$jum=0;



								$tssb=0;
								$tbs=0;
								$tssg=0;


								foreach ($data as $key => $d) {

									if(!empty($bulan) && $bulan!==date_format(date_create($d->Tgl_FSP),'M')){

										$content .='</tbody>';
										$content .='<tr>';
											$content .='<td class="tdt" align="center"><b>Bulan</b> '.$bulan.'</td>';
											$content .='<td class="tdt" align="center"><b>Transaksi</b> '.$jum.'</td>';
											$content .='<td class="tdt"></td>';
											$content .='<td class="tdt"><b>Sudah Selesai</b></td>';
											$content .='<td class="tdt" align="center">'.$jum_sudah_selesai.'</td>';
											$content .='<td class="tdt" align="center"><b>Total Nilai Setoran</b></td>';
											$content .='<td class="tdt" align="center">'.number_format($total).'</td>';
											$content .='<td class="tdt"></td>';
										$content .='</tr>';

										$content .='<tr>';
											$content .='<td></td>';
											$content .='<td></td>';
											$content .='<td></td>';
											$content .='<td><b>Belum Selesai</b></td>';
											$content .='<td align="center">'.$jum_belum_selesai.'</td>';
											$content .='<td></td>';
											$content .='<td></td>';
											$content .='<td></td>';
										$content .='</tr>';

										$content .='<tr>';
											$content .='<td colspan="8"><br></td>';
										$content .='</tr>';
									
										$content .= '</table>';

									}

									if($bulan!==date_format(date_create($d->Tgl_FSP),'M')){

										$bulan=date_format(date_create($d->Tgl_FSP),'M');

										$content .= '<table class="table" border="0" style="width:100%; font-size:10px; margin-top:20px;">
													<thead>
														<tr>
															<td class="td" style="width:12,5%" align="center">
																<b>
																	Tanggal
																</b>
															</td>
															<td class="td" style="width:12,5%" align="center">
																<b>
																	No Faktur
																</b>
															</td>
															<td class="td" style="width:12,5%" align="center">
																<b>
																	Status
																</b>
															</td>
															<td class="td" style="width:12,5%" align="center">
																<b>
																	Sudah Selesai
																</b>
															</td>
															<td class="td" style="width:12,5%" align="center">
																<b>
																	Belum Selesai
																</b>
															</td>
															<td class="td" style="width:12,5%" align="center">
																<b>
																	No BBT
																</b>
															</td>
															<td class="td" style="width:12,5%" align="center">
																<b>
																	Nilai Setoran
																</b>
															</td>
															<td class="td" style="width:12,5%" align="center">
																<b>
																	Keterangan
																</b>
															</td>
														</tr>
													</thead>';

										$jum=0;
										$total=0;
										$jum_sudah_selesai=0;
										$jum_belum_selesai=0;

										$tssb=0;
										$tssg=0;
										$tbs=0;

										$content .= '<tbody>';
										
									}


									$sudah_selesai = '';
									$belum_selesai = '';

									if(rtrim($d->Kembali)=='Y'){
										$sudah_selesai = 'X';
										$belum_selesai = '';
										$jum_sudah_selesai++;
										$total_jum_sudah_selesai++;
									}else if(rtrim($d->Kembali)=='N'){
										$sudah_selesai = '';
										$belum_selesai = 'X';
										$jum_belum_selesai++;
										$total_jum_belum_selesai++;
									}

										$content .= '<tr>';
											$content .= '<td class="tdn" align="center">'.date_format(date_create($d->Tgl_FSP),'d M Y').'</td>';
											$content .= '<td class="tdn" align="center">'.$d->No_FSP.'</td>';
											$content .= '<td class="tdn" align="center">'.rtrim($d->Kembali).'</td>';
											$content .= '<td class="tdn" align="center">'.$sudah_selesai.'</td>';
											$content .= '<td class="tdn" align="center">'.$belum_selesai.'</td>';
											$content .= '<td class="tdn" align="center">'.$d->No_Bukti.'</td>';
											$content .= '<td class="tdn" align="center">'.number_format($d->Setoran).'</td>';
											$content .= '<td class="tdn" align="center"></td>';
										$content .= '</tr>';
															

									$jum++;
									$total_jum++;

									$bln = date_format(date_create($d->Tgl_FSP),'m')*1;
									$ttt[$bln]=$jum;

									$total=$total+$d->Setoran;
									$total_semua=$total_semua+$d->Setoran;
									

									if($d->Jaminan=='Y'){
										$tssg++;
										$ssg[$bln]=$tssg;
									}else{
										$tssb++;
										$ssb[$bln]=$tssb;
									}


									if(rtrim($d->Kembali)=='N'){
										$tbs++;
										$bs[$bln]=$tbs;
									}


								}

								$content .='</tbody>';

								$content .='<tr>';
									$content .='<td class="tdt" align="center"><b>Bulan</b> '.$bulan.'</td>';
									$content .='<td class="tdt" align="center"><b>Transaksi</b> '.$jum.'</td>';
									$content .='<td class="tdt"></td>';
									$content .='<td class="tdt"><b>Sudah Selesai</b></td>';
									$content .='<td class="tdt" align="center">'.$jum_sudah_selesai.'</td>';
									$content .='<td class="tdt" align="center"><b>Total Nilai Setoran</b></td>';
									$content .='<td class="tdt" align="center">'.number_format($total).'</td>';
									$content .='<td class="tdt"></td>';
								$content .='</tr>';

								$content .='<tr>';
									$content .='<td></td>';
									$content .='<td></td>';
									$content .='<td></td>';
									$content .='<td><b>Belum Selesai</b></td>';
									$content .='<td align="center">'.$jum_belum_selesai.'</td>';
									$content .='<td></td>';
									$content .='<td></td>';
									$content .='<td></td>';
								$content .='</tr>';

								$content .='<tr>';
									$content .='<td colspan="8"><br></td>';
								$content .='</tr>';
							

							$content .= '</table>';


							$content .= '<table class="table_bawah" border="0" style="width:100%; font-size:10px;">';
								$content .='<tr>';
									$content .='<td class="td" align="center"><b>Total Semuanya</b></td>';
									$content .='<td class="td" align="center"><b>Transaksi</b> '.$total_jum.'</td>';
									$content .='<td class="td" align="center"><b>Sudah Selesai</b> '.$total_jum_sudah_selesai.'</td>';
									$content .='<td class="td" align="center"><b>Belum Selesai</b> '.$total_jum_belum_selesai.'</td>';
									$content .='<td class="td" align="center"><b>Total Nilai Setoran</b></td>';
									$content .='<td class="td" align="center">'.number_format($total_semua).'</td>';
								$content .='</tr>';
							$content .= '</table>';

							$content .='<table class="table_bawah" border="0" style="width:100%; font-size:10px; margin-top:20px;">';

								
								$content .='<tr>';
									$content .='<td width="120px"><b>SUMMARY</b><hr style="padding:0px; margin:2px"><hr style="padding:0px; margin:2px"></td>';
									$content .='<td width="30px"></td>';

									$ambil_bulan = date('d-M-Y', strtotime('-1 month', strtotime( $DTPAwal )));
									for($i=0; $i<=3; $i++){
										$ambil_bulan = date('d-M-Y', strtotime('+1 month', strtotime( $ambil_bulan )));
										$content .='<td class="tdfull" align="center"><b>'.date_format(date_create($ambil_bulan),'M').'</b></td>';
									}

									$content .='<td class="tdfull" align="center"><b>TOTAL</b></td>';
								$content .='</tr>';
								$content .='<tr>';
									$content .='<td><b>Total Transaksi</b></td>';
									$content .='<td></td>';

									$total=0;

									$awal_bln = date('d-m-Y', strtotime('-1 month', strtotime( $DTPAwal )));

									for($i=0; $i<=3; $i++){
																				
										$awal_bln = date('d-m-Y', strtotime('+1 month', strtotime( $awal_bln )));
										$bln = date_format(date_create($awal_bln),'m')*1;

										$content .='<td class="tdfull" align="center"><b>'.$ttt[$bln].'</b></td>';
										$total=$total+$ttt[$bln];

									}

									$content .='<td class="tdfull" align="center"><b>'.$total.'</b></td>';

								$content .='</tr>';
								$content .='<tr>';
									$content .='<td><b>Sudah Selesai (Bayar)</b></td>';
									$content .='<td></td>';

									$total=0;


									$awal_bln = date('d-m-Y', strtotime('-1 month', strtotime( $DTPAwal )));

									for($i=0; $i<=3; $i++){
																				
										$awal_bln = date('d-m-Y', strtotime('+1 month', strtotime( $awal_bln )));
										$bln = date_format(date_create($awal_bln),'m')*1;

										$content .='<td class="tdfull" align="center">'.$ssb[$bln].'</td>';
										$total=$total+$ssb[$bln];

									}


										$content .='<td class="tdfull" align="center">'.$total.'</td>';

								$content .='</tr>';
								$content .='<tr>';
									$content .='<td><b>Sudah Selesai (Gratis)</b></td>';
									$content .='<td></td>';

									$total=0;

									$awal_bln = date('d-m-Y', strtotime('-1 month', strtotime( $DTPAwal )));

									for($i=0; $i<=3; $i++){
																				
										$awal_bln = date('d-m-Y', strtotime('+1 month', strtotime( $awal_bln )));
										$bln = date_format(date_create($awal_bln),'m')*1;

										$content .='<td class="tdfull" align="center">'.$ssg[$bln].'</td>';
										$total=$total+$ssg[$bln];

									}


										$content .='<td class="tdfull" align="center">'.$total.'</td>';

								$content .='</tr>';
								$content .='<tr>';
									$content .='<td><b>Belum Selesai</b></td>';
									$content .='<td></td>';

									$total=0;

									$awal_bln = date('d-m-Y', strtotime('-1 month', strtotime( $DTPAwal )));

									for($i=0; $i<=3; $i++){
																				
										$awal_bln = date('d-m-Y', strtotime('+1 month', strtotime( $awal_bln )));
										$bln = date_format(date_create($awal_bln),'m')*1;

										$content .='<td class="tdfull" align="center">'.$bs[$bln].'</td>';
										$total=$total+$bs[$bln];

									}
										$content .='<td class="tdfull" align="center">'.$total.'</td>';

								$content .='</tr>';
							$content .='</table>';

						}


			}

			$mpdf->SetHTMLHeader($header,'','1');
			$mpdf->WriteHTML($content);
			$mpdf->Output();
		}else{
			redirect(site_url('Reportstatusfaktursp/?error=error'));
		}
	}

	function excel($proses='',$acak='',$DTPAwal='',$DTPAkhir='',$CboNoFaktur='',$CboTpCtk='',$CboKdGdg='',$ChkSelesai='',$ChkSmSelesai='',$OptSemua='',$OptBayar='',$OptGratis='',$ChkBatal='',$ChkSmBatal=''){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');


		if($_SESSION["can_read"]==true && !empty($acak)){

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);

			ini_set('max_execution_time', '1500');
			ini_set("pcre.backtrack_limit", "10000000");
			set_time_limit(0);


			$data = json_decode($this->ReportstatusfakturspModel->ProsesApiData($proses,$acak));

				$PrintDate = gmdate('Y-m-d H:i:s', time() + (60 * 60 * 7));

	            $sheet->setCellValue('A1', 'Print Date      : '.$PrintDate);
	            $sheet->setCellValue('A2', 'Print By        : '.$_SESSION['logged_in']['username']);
	            $sheet->setCellValue('A3', 'LAPORAN STATUS FAKTUR SPAREPART DIVISI SERVICE');
	            $sheet->getStyle('A3')->getFont()->setSize(20);

	            $sheet->setCellValue('A5', 'Bulan  : '.date_format(date_create($DTPAkhir),'M Y'));
	            $sheet->setCellValue('A6', 'Periode Kontrol  : '.date_format(date_create($DTPAwal),'d M Y').' s/d '.date_format(date_create($DTPAkhir),'d M Y'));
	            $sheet->setCellValue('A7', 'Gudang  : '.str_replace("-"," - ",str_replace("%20", " ", $CboKdGdg)));


				$sheet->mergeCells('A1:H1');
				$sheet->mergeCells('A2:H2');
				$sheet->mergeCells('A3:H3');
				$sheet->mergeCells('A5:H5');
				$sheet->mergeCells('A6:H6');
				$sheet->mergeCells('A7:H7');
				$sheet->getStyle('A1')->getFont()->setSize(10);
				$sheet->getStyle('A2')->getFont()->setSize(10);
				$sheet->getStyle('A3')->getFont()->setSize(15);
				$sheet->getStyle('A5')->getFont()->setSize(12);
				$sheet->getStyle('A6')->getFont()->setSize(12);
				$sheet->getStyle('A7')->getFont()->setSize(12);
				$sheet->getStyle('A1:H1')->getAlignment()->setHorizontal('right');
				$sheet->getStyle('A2:H2')->getAlignment()->setHorizontal('right');
				$sheet->getStyle('A3:H3')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A5:H5')->getAlignment()->setHorizontal('left');
				$sheet->getStyle('A6:H6')->getAlignment()->setHorizontal('left');
				$sheet->getStyle('A7:H7')->getAlignment()->setHorizontal('left');

				$spreadsheet->getActiveSheet()->getStyle('A3:H3')->getFont()->setBold( true );



				if(count($data)>0){

					$bulan=0;
					
					$total=0;
					$total_semua=0;
					$jum_sudah_selesai=0;
					$jum_belum_selesai=0;	
					$total_jum_sudah_selesai=0;
					$total_jum_belum_selesai=0;
					$total_jum=0;


					$awal_bln = date('d-m-Y', strtotime('-1 month', strtotime( $DTPAwal )));

					for($i=0; $i<=3; $i++){
																				
						$awal_bln = date('d-m-Y', strtotime('+1 month', strtotime( $awal_bln )));
						$bln = date_format(date_create($awal_bln),'m')*1;

						$ttt[$bln]=0;
						$ssb[$bln]=0;
						$ssg[$bln]=0;
						$bs[$bln]=0;

					}

					$jum=0;

					$tssb=0;
					$tbs=0;
					$tssg=0;

					$currcol = 1;
					$currrow = 9;

					foreach ($data as $key => $d) {

						if(!empty($bulan) && $bulan!==date_format(date_create($d->Tgl_FSP),'M')){

							$currcol=1;

							$sheet->getStyle('A'.$currrow.':'.'H'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Bulan '.$bulan);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Transaksi '.$jum);
							$currcol=$currcol+2;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Sudah Selesai');
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jum_sudah_selesai);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Nilai Setoran');
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total));
							$currrow++;

							$currcol=4;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Belum Selesai');
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jum_belum_selesai);

							$currrow=$currrow+2;

						}

						if($bulan!==date_format(date_create($d->Tgl_FSP),'M')){

							$bulan=date_format(date_create($d->Tgl_FSP),'M');


							$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':H'.$currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('eaeaea');
							$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':H'.$currrow)->getFont()->setBold( true );
							$sheet->getStyle('A'.$currrow.':H9'.$currrow)->getAlignment()->setHorizontal('center');

							$currcol=1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tanggal');
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Faktur Transaksi');
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Status');
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Sudah Selesa');
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Belum Selesai');
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No BBT');
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nilai Setoran ');
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Keterangan');
							$currcol=1;
							$currrow++;
							

							$jum=0;
							$total=0;
							$jum_sudah_selesai=0;
							$jum_belum_selesai=0;

							$tssb=0;
							$tssg=0;
							$tbs=0;

										
						}


						$sudah_selesai = '';
						$belum_selesai = '';

						if(rtrim($d->Kembali)=='Y'){
							$sudah_selesai = 'X';
							$belum_selesai = '';
							$jum_sudah_selesai++;
							$total_jum_sudah_selesai++;
						}else if(rtrim($d->Kembali)=='N'){
							$sudah_selesai = '';
							$belum_selesai = 'X';
							$jum_belum_selesai++;
							$total_jum_belum_selesai++;
						}

						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, date_format(date_create($d->Tgl_FSP),'d M Y'));
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($d->No_FSP));
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($d->Kembali));
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $sudah_selesai);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $belum_selesai);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($d->No_Bukti));
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim(number_format($d->Setoran)));
						$currrow ++;
														

						$jum++;
						$total_jum++;

						$bln = date_format(date_create($d->Tgl_FSP),'m')*1;
						$ttt[$bln]=$jum;

						$total=$total+$d->Setoran;
						$total_semua=$total_semua+$d->Setoran;
						

						if($d->Jaminan=='Y'){
							$tssg++;
							$ssg[$bln]=$tssg;
						}else{
							$tssb++;
							$ssb[$bln]=$tssb;
						}


						if(rtrim($d->Kembali)=='N'){
							$tbs++;
							$bs[$bln]=$tbs;
						}


					}


					$currcol=1;
					$sheet->getStyle('A'.$currrow.':'.'H'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Bulan '.$bulan);
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Transaksi '.$jum);
					$currcol=$currcol+2;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Sudah Selesai');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jum_sudah_selesai);
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Nilai Setoran');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total));
					$currrow++;

					$currcol=4;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Belum Selesai');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jum_belum_selesai);
					$currrow=$currrow+3;

					$sheet->getStyle('A'.$currrow.':'.'H'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
					$sheet->getStyle('A'.$currrow.':'.'H'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
					$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':H'.$currrow)->getFont()->setBold( true );
					$currcol=1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Semuanya');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Transaksi '.$total_jum);
					$currcol=$currcol+2;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Sudah Selesai '.$total_jum_sudah_selesai);
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Belum Selesai '.$total_jum_belum_selesai);
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Nilai Setoran');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_semua));
					$currrow=$currrow+3;



					$currcol=2;
					$sheet->getStyle('B'.$currrow.':'.'B'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE);
					$spreadsheet->getActiveSheet()->getStyle('B'.$currrow)->getFont()->setBold( true );
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SUMMARY');
					$currcol=$currcol+2;
					$ambil_bulan = date('d-M-Y', strtotime('-1 month', strtotime( $DTPAwal )));

					for($i=0; $i<=3; $i++){
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('D'.$currrow.':H'.$currrow)->getFont()->setBold( true );
						$ambil_bulan = date('d-M-Y', strtotime('+1 month', strtotime( $ambil_bulan )));
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, date_format(date_create($ambil_bulan),'M'));
						$currcol ++;
					}
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');

					$currrow++;

					$currcol=2;
					$spreadsheet->getActiveSheet()->getStyle('B'.$currrow)->getFont()->setBold( true );
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Transaksi');
					$currcol=$currcol+2;

					$total=0;

					$awal_bln = date('d-m-Y', strtotime('-1 month', strtotime( $DTPAwal )));
					for($i=0; $i<=3; $i++){
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('D'.$currrow.':H'.$currrow)->getFont()->setBold( true );

						$awal_bln = date('d-m-Y', strtotime('+1 month', strtotime( $awal_bln )));
						$bln = date_format(date_create($awal_bln),'m')*1;

						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $ttt[$bln]);
						$total=$total+$ttt[$bln];
						$currcol ++;

					}
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total);

					$currrow++;

					$currcol=2;
					$spreadsheet->getActiveSheet()->getStyle('B'.$currrow)->getFont()->setBold( true );
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Sudah Selesai (Bayar)');
					$currcol=$currcol+2;

					$total=0;

					$awal_bln = date('d-m-Y', strtotime('-1 month', strtotime( $DTPAwal )));
					for($i=0; $i<=3; $i++){
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

						$awal_bln = date('d-m-Y', strtotime('+1 month', strtotime( $awal_bln )));
						$bln = date_format(date_create($awal_bln),'m')*1;

						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $ssb[$bln]);
						$total=$total+$ssb[$bln];
						$currcol ++;

					}
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total);

					$currrow++;

					$currcol=2;
					$spreadsheet->getActiveSheet()->getStyle('B'.$currrow)->getFont()->setBold( true );
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Sudah Selesai (Bayar)');
					$currcol=$currcol+2;

					$total=0;

					$awal_bln = date('d-m-Y', strtotime('-1 month', strtotime( $DTPAwal )));
					for($i=0; $i<=3; $i++){
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

						$awal_bln = date('d-m-Y', strtotime('+1 month', strtotime( $awal_bln )));
						$bln = date_format(date_create($awal_bln),'m')*1;

						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $ssg[$bln]);
						$total=$total+$ssg[$bln];
						$currcol ++;

					}
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total);

					$currrow++;

					$currcol=2;
					$spreadsheet->getActiveSheet()->getStyle('B'.$currrow)->getFont()->setBold( true );
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Belum Selesai');
					$currcol=$currcol+2;

					$total=0;

					$awal_bln = date('d-m-Y', strtotime('-1 month', strtotime( $DTPAwal )));
					for($i=0; $i<=3; $i++){
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$sheet->getStyle('D'.$currrow.':'.'H'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

						$awal_bln = date('d-m-Y', strtotime('+1 month', strtotime( $awal_bln )));
						$bln = date_format(date_create($awal_bln),'m')*1;

						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $bs[$bln]);
						$total=$total+$bs[$bln];
						$currcol ++;

					}
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total);

			}


			for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
			    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
			}

            $filename = 'LAPORAN STATUS FAKTUR SPAREPART DIVISI SERVIS';
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
            ob_end_clean();
            $writer->save('php://output');	// download file
            exit(); 

			
		}else{
			redirect(site_url('Reportstatusfaktursp/?error=error'));
		}
	}


}