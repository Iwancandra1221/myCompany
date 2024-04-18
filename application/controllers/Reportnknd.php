<?php 
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Reportnknd extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model("ReportnkndModel");
		$this->load->library('excel');
	}

	public function index(){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), ''); 
		if($_SESSION["can_read"]==true){
			$data['type_nota'] = $this->ReportnkndModel->type_nota();
			$data['kategori_khusus'] = $this->ReportnkndModel->kategori_khusus();
			$data['partner_type'] = $this->ReportnkndModel->partner_type();
			$data['wilayah'] = $this->ReportnkndModel->wilayah();
			$this->RenderView('ReportnkndView',$data);
		}
	}

	public function dealer(){
		set_time_limit(60); 
		if(!empty($_POST)){
			$wilayah = $this->input->post('wilayah');
		}else{
			$wilayah = '';
		}
		
		$dealer = $this->ReportnkndModel->dealer($wilayah);
		print_r(json_encode($dealer));
	}


	public function pdf($from='',$until='',$type_transaksi='',$type_nota='',$kategori_khusus='',$partner_type='',$wilayah='',$dealer='',$alamat='0'){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

		if($_SESSION["can_read"]==true && !empty($from) && !empty($until) && !empty($type_transaksi) && !empty($type_nota) && !empty($kategori_khusus) && !empty($partner_type) && !empty($wilayah) && !empty($dealer)){

			$data['from'] = $from;
			$data['until'] = $until;
			$data['type_transaksi'] = $type_transaksi;
			$data['type_nota'] = $type_nota;
			$data['kategori_khusus'] = $kategori_khusus;
			$data['partner_type'] = $partner_type;
			$data['wilayah'] = $wilayah;
			$data['dealer'] = $dealer;
			$list = $this->ReportnkndModel->GetData($data);
			$list = json_decode($list,true);

			if($list['hasil']=='success'){

				//ini_set('max_execution_time', '1500');
				ini_set("pcre.backtrack_limit", "10000000");
				set_time_limit(60);

				$mpdf = new \Mpdf\Mpdf(array(
					'mode' => '',
					'format' => 'Legal',
					'default_font_size' => 8,
					'default_font' => 'arial',
					'margin_left' => 10,
					'margin_right' => 10,
					'margin_top' => 39,
					'margin_bottom' => 10,
					'margin_header' => 10,
					'margin_footer' => 5,
					'orientation' => 'P'
				));

				$header ='<table width="100%">';
				$header .='<tr><td align="right">Printed : '.date('Y-m-d H-i-s').'</td></tr>';
				$header .='<tr><td align="center"><h1>LAPORAN BUKU HARIAN</h1></td></tr>';
				$header .='<tr><td align="center"><h5>PERIODE : '.$from.' S/D '.$until.'</h5></td></tr>';
				$header .='</table>';
				$header .='<table border="0">';
				$header .='<tr><td width="80px"><h5>Partner Type</h5></td><td width="10px" align="center"><h5>:</h5></td><td><h5>'.$partner_type.'</h5></td></tr>';
				$header .='<tr><td><h5>Wilayah</h5></td><td align="center"><h5>:</h5></td><td><h5>'.$wilayah.'</h5></td></tr>';
				$header .='<tr><td><h5>Type Nota</h5></td><td align="center"><h5>:</h5></td><td><h5>'.$type_nota.'</h5></td></tr>';
				$header .='</table>';


				$content ='<table style="border-collapse:collapse;border-spacing:0;" align="center" border="1" width="100%">';
				$content .='<thead><tr>';
				$content .='<td width="30px" align="center">No</td>';
				$content .='<td width="13.5%" align="center">Tanggal Transaksi</td>';
				$content .='<td width="13.5%" align="center">No Bukti</td>';
				$content .='<td width="13.5%" align="center">NO Distribusi</td>';
				$content .='<td width="13.5%" align="center">Total</td>';
				$content .='<td width="13.5%" align="center">Total Distribusi</td>';
				$content .='<td width="13.5%" align="center">Sisa</td>';
				$content .='<td width="13.5%" align="center">Type Terima</td>';
				if($alamat==1){
					$content .='<td width="13.5%" align="center">Alamat</td>';
				}
				$content .='</tr></thead><tbody>';

				$no=1;
				$tamp_total=0;
				$kodePelanggan='';
				$No_bukti='';
				$No_distribusi='';
				$KdWilayah='';

				$nmplg='';
				$tamp_type_nota='';
				$tamp_Kategori_Lain='';
				$tamp_Kategori_Lain2='';
				$total=0;
				$total_dist=0;

				$tand=0;

				$a=0;
				$b=0;
				$c=0;
				foreach ($list['data'] as $key => $l) {
					

					if(empty($kodePelanggan)){
						if(empty($KdWilayah) && $tand==0){
							$content .='<tr><td colspan="8"><br><b>'.rtrim($l['wilayah']).'</b></td>';
							if($alamat==1){
								$content .='<td></td>';
							}

							$content .='</tr>';

							$KdWilayah=rtrim($l['wilayah']);
							$tand++;
						}
						$content .='<tr><td colspan="7"><b>'.rtrim($l['nm_plg']).'</b></td><td><b>'.rtrim($l['kd_plg']).'</b></td>';
							if($alamat==1){
								$content .='<td></td>';
							}

							$content .='</tr>';

						$kodePelanggan=rtrim($l['kd_plg']);
						$tamp_type_nota=rtrim($l['Type_Nota']);
						$tamp_Kategori_Lain=rtrim($l['Kategori_Lain']);
						$nmplg=rtrim($l['nm_plg']);

					}else{

						if($kodePelanggan!==rtrim($l['kd_plg']) || ($tamp_type_nota!==rtrim($l['Type_Nota']) || $tamp_Kategori_Lain!==rtrim($l['Kategori_Lain']))){
									
							$content .='<tr><td colspan="2"></td><td colspan="2"><b>Total '.rtrim($nmplg).'<b></td><td><b>'.number_format($total,2,",",".").'</b></td><td><b>'.number_format($total_dist,2,",",".").'</b></td><td><b>'.number_format($total-$total_dist,2,",",".").'</b></td><td></td>';
								
							if($alamat==1){
								$content .='<td></td>';
							}

							$content .='</tr>';
							$kodePelanggan=rtrim($l['kd_plg']);

							$nmplg=rtrim($l['nm_plg']);

							if($tamp_type_nota!==rtrim($l['Type_Nota']) || $tamp_Kategori_Lain!==rtrim($l['Kategori_Lain'])){
								$content .='<tr><td colspan="2"></td><td colspan="2"><b>NK '.rtrim($tamp_Kategori_Lain).'<b></td><td><b>'.number_format($a,2,",",".").'</b></td><td><b>'.number_format($b,2,",",".").'</b></td><td><b>'.number_format($a-$b,2,",",".").'</b></td><td></td>';
								
								if($alamat==1){
									$content .='<td></td>';
								}

								$content .='</tr>';

									$content .='<tr><td colspan="2"></td><td colspan="2"><b>NK '.rtrim($l['Type_Nota']).'<b></td><td><b>'.number_format($a,2,",",".").'</b></td><td><b>'.number_format($b,2,",",".").'</b></td><td><b>'.number_format($a-$b,2,",",".").'</b></td><td></td>';
									
								if($alamat==1){
									$content .='<td></td>';
								}

								$content .='</tr>';
								$content .='<tr><td colspan="2"></td><td colspan="2"><b>NK '.rtrim($l['wilayah']).'<b></td><td><b>'.number_format($a,2,",",".").'</b></td><td><b>'.number_format($b,2,",",".").'</b></td><td><b>'.number_format($a-$b,2,",",".").'</b></td><td></td>';
									
								if($alamat==1){
									$content .='<td></td>';
								}

								$content .='</tr>';

								$content .='<tr><td colspan="8"><br><b>'.rtrim($l['wilayah']).'</b></td></tr>';
								$KdWilayah=rtrim($l['wilayah']);
								$tamp_type_nota=rtrim($l['Type_Nota']);
								$tamp_Kategori_Lain=rtrim($l['Kategori_Lain']);
								$a=0;
								$b=0;
							}



							$content .='<tr><td colspan="7"><b>'.rtrim($l['nm_plg']).'</b></td><td><b>'.rtrim($l['kd_plg']).'</b></td>';
							if($alamat==1){
								$content .='<td></td>';
							}

							$content .='</tr>';
							$total=0;
							$total_dist=0;
										
						}else{

						$kodePelanggan=rtrim($l['kd_plg']);

						}
				
					}


					if ($l['Type_terima'] = "PERDANA"){
						$Type_terima='';
					}else{
						$Type_terima=$l['Type_terima'];
					}

					
					$total_dist = $total_dist+$l['total_distribusi'];
					$b=$b+$l['total_distribusi'];
					$content .='<tr>';



					if((!empty($No_bukti) && $No_bukti!==rtrim($l['No_bukti'])) || $no==1){
						$content .='<td align="center">'.$no.'.</td>';
						$content .='<td>'.date_format(date_create($l['tgl_trans']),'d-m-Y').'</td>';
						$content .='<td>'.rtrim($l['No_bukti']).'</td>';
						$content .='<td>'.rtrim($l['no_distribusi']).'</td>';
						$content .='<td>'.number_format($l['Total'],2,",",".").'</td>';
						$No_bukti=rtrim($l['No_bukti']);
						$No_distribusi=rtrim($l['no_distribusi']);
						$tamp_total=$l['Total'];
						$total = $total+$l['Total'];

						$a=$a+$l['Total'];
						
					}else{
						if($No_distribusi!==rtrim($l['no_distribusi'])){
							$content .='<td colspan="3"></td>';
							$content .='<td>'.rtrim($l['no_distribusi']).'</td>';
							$content .='<td></td>';
							$No_distribusi=rtrim($l['no_distribusi']);
						}else{
							$content .='<td colspan="5"></td>';
						}
					}

					$tamp_total=$tamp_total-$l['total_distribusi'];
					$content .='<td>'.number_format($l['total_distribusi'],2,",",".").'</td>';
					$content .='<td>'.number_format($tamp_total,2,",",".").'</td>';
					$content .='<td>'.rtrim($Type_terima).'</td>';
					if($alamat==1){
						if ($l['Kd_Wil']=="NNNN"){
						    $content .='<td>'.rtrim($l['alm_plg']).'</td>';
						}else{
							$content .='<td>'.rtrim($l['Nm_Wil']).'</td>';
						}
					}
					$content .='</tr>';
					$no++;

				}


				$content .='<tr><td colspan="2"></td><td colspan="2"><b>Total '.rtrim($nmplg).'<b></td><td><b>'.number_format($total,2,",",".").'</b></td><td><b>'.number_format($total_dist,2,",",".").'</b></td><td><b>'.number_format($total-$total_dist,2,",",".").'</b></td><td></td>';

				if($alamat==1){
					$content .='<td></td>';
				}

				$content .='</tr>';

				$content .='<tr><td colspan="2"></td><td colspan="2"><b>NK '.rtrim($tamp_Kategori_Lain).'<b></td><td><b>'.number_format($a,2,",",".").'</b></td><td><b>'.number_format($b,2,",",".").'</b></td><td><b>'.number_format($a-$b,2,",",".").'</b></td><td></td>';
				if($alamat==1){
					$content .='<td></td>';
				}

				$content .='</tr>';
				$content .='<tr><td colspan="2"></td><td colspan="2"><b>NK '.rtrim($tamp_type_nota).'<b></td><td><b>'.number_format($a,2,",",".").'</b></td><td><b>'.number_format($b,2,",",".").'</b></td><td><b>'.number_format($a-$b,2,",",".").'</b></td><td></td>';
				if($alamat==1){
					$content .='<td></td>';
				}

				$content .='</tr>';
				$content .='<tr><td colspan="2"></td><td colspan="2"><b>NK '.rtrim($KdWilayah).'<b></td><td><b>'.number_format($a,2,",",".").'</b></td><td><b>'.number_format($b,2,",",".").'</b></td><td><b>'.number_format($a-$b,2,",",".").'</b></td><td></td>';
				if($alamat==1){
					$content .='<td></td>';
				}

				$content .='</tr>';

				$content .='</tbody></table>';


				set_time_limit(60);
				$mpdf->SetHTMLHeader($header,'','1');
				$mpdf->WriteHTML($content);
				$mpdf->Output();

			}


		}else{
			redirect(site_url('Reportnknd/?error=error'));
		}

	}


	public function excel($from='',$until='',$type_transaksi='',$type_nota='',$kategori_khusus='',$partner_type='',$wilayah='',$dealer='',$alamat='0'){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

		if($_SESSION["can_read"]==true && !empty($from) && !empty($until) && !empty($type_transaksi) && !empty($type_nota) && !empty($kategori_khusus) && !empty($partner_type) && !empty($wilayah) && !empty($dealer)){

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);


			$data['from'] = $from;
			$data['until'] = $until;
			$data['type_transaksi'] = $type_transaksi;
			$data['type_nota'] = $type_nota;
			$data['kategori_khusus'] = $kategori_khusus;
			$data['partner_type'] = $partner_type;
			$data['wilayah'] = $wilayah;
			$data['dealer'] = $dealer;
			$list = $this->ReportnkndModel->GetData($data);
			$list = json_decode($list,true);

			if($list['hasil']=='success'){

				//ini_set('max_execution_time', '1500');
				ini_set("pcre.backtrack_limit", "10000000");
				set_time_limit(60);


				$sheet->setTitle('LAPORAN BUKU HARIAN');
				$sheet->setCellValue('A1', 'Printed : '.date('Y-m-d H-i-s'));
				$sheet->setCellValue('A2', 'LAPORAN BUKU HARIAN');
				$sheet->setCellValue('A3', 'PERIODE : '.$from.' S/D '.$until);

				$sheet->setCellValue('A5', 'Partner Type : '.$partner_type);
				$sheet->setCellValue('A6', 'Wilayah : '.$wilayah);
				$sheet->setCellValue('A7', 'Type Nota : '.$type_nota);


				if($alamat==1){
					$merge='I';
					$merge2='H';
					$merge3=9;
				}else{
					$merge='H';
					$merge2='G';
					$merge3=8;
				}

				$sheet->mergeCells('A1:'.$merge.'1');
				$sheet->mergeCells('A2:'.$merge.'2');
				$sheet->mergeCells('A3:'.$merge.'3');
				$sheet->mergeCells('A5:'.$merge.'5');
				$sheet->mergeCells('A6:'.$merge.'6');
				$sheet->mergeCells('A7:'.$merge.'7');
				$sheet->getStyle('A1')->getFont()->setSize(10);
				$sheet->getStyle('A2')->getFont()->setSize(20);
				$sheet->getStyle('A3')->getFont()->setSize(15);
				$sheet->getStyle('A5')->getFont()->setSize(12);
				$sheet->getStyle('A6')->getFont()->setSize(12);
				$sheet->getStyle('A7')->getFont()->setSize(12);
				$sheet->getStyle('A1:'.$merge.'1')->getAlignment()->setHorizontal('right');
				$sheet->getStyle('A2:'.$merge.'2')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A3:'.$merge.'3')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A5:'.$merge.'5')->getAlignment()->setHorizontal('left');
				$sheet->getStyle('A6:'.$merge.'6')->getAlignment()->setHorizontal('left');
				$sheet->getStyle('A7:'.$merge.'7')->getAlignment()->setHorizontal('left');




				$currcol = 1;
				$currrow = 9;

				$spreadsheet->getActiveSheet()->getStyle('A9:'.$merge.'9')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('eaeaea');

				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No');
				$currcol ++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tanggal Transaksi');
				$currcol ++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Bukti');
				$currcol ++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO Distribusi');
				$currcol ++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
				$currcol ++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Distribusi');
				$currcol ++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Sisa');
				$currcol ++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Type Terima');
				$currcol ++;

				if($alamat==1){
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Alamat');
					$currcol ++;
				}
				
				$currrow++;



					$no=1;
					$tamp_total=0;
					$kodePelanggan='';
					$No_bukti='';
					$No_distribusi='';
					$KdWilayah='';

					$nmplg='';
					$tamp_type_nota='';
					$tamp_Kategori_Lain='';
					$tamp_Kategori_Lain2='';
					$total=0;
					$total_dist=0;

					$tand=0;

					$a=0;
					$b=0;
					$c=0;
					foreach ($list['data'] as $key => $l) {
						
						$currcol=1;
						if(empty($kodePelanggan)){
							if(empty($KdWilayah) && $tand==0){
								$sheet->mergeCells('A'.$currrow.':'.$merge.$currrow);
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['wilayah']));
								$currrow ++;

								$KdWilayah=rtrim($l['wilayah']);
								$tand++;
							}

							$sheet->mergeCells('A'.$currrow.':'.$merge2.$currrow);
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['nm_plg']));
							$currcol++;

							if($alamat==1){
								$currcol_alamat=9;
							}else{
								$currcol_alamat=8;
							}

							$sheet->setCellValueByColumnAndRow($merge3, $currrow, rtrim($l['kd_plg']));
							$currrow ++;

							$kodePelanggan=rtrim($l['kd_plg']);
							$tamp_type_nota=rtrim($l['Type_Nota']);
							$tamp_Kategori_Lain=rtrim($l['Kategori_Lain']);
							$nmplg=rtrim($l['nm_plg']);
					
						}else{

							if($kodePelanggan!==rtrim($l['kd_plg']) || ($tamp_type_nota!==rtrim($l['Type_Nota']) || $tamp_Kategori_Lain!==rtrim($l['Kategori_Lain']))){
								

								$sheet->mergeCells('C'.$currrow.':D'.$currrow);
								$sheet->setCellValueByColumnAndRow(3, $currrow, 'Total '.rtrim($nmplg));
								$currcol=5;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,2,",","."));
								$currcol ++;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_dist,2,",","."));
								$currcol ++;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total-$total_dist,2,",","."));
								$currcol ++;
								$currrow++;
								$kodePelanggan=rtrim($l['kd_plg']);

								$nmplg=rtrim($l['nm_plg']);

								if($tamp_type_nota!==rtrim($l['Type_Nota']) || $tamp_Kategori_Lain!==rtrim($l['Kategori_Lain'])){

									$currcol=4;
									$sheet->mergeCells('C'.$currrow.':D'.$currrow);
									$sheet->setCellValueByColumnAndRow(3, $currrow, 'NK '.rtrim($tamp_Kategori_Lain));
									$currcol ++;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($a,2,",","."));
									$currcol ++;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($b,2,",","."));
									$currcol ++;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($a-$b,2,",","."));
									
									$currrow++;
									$currcol=4;
									$sheet->mergeCells('C'.$currrow.':D'.$currrow);
									$sheet->setCellValueByColumnAndRow(3, $currrow, 'NK '.rtrim($l['Type_Nota']));
									$currcol ++;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($a,2,",","."));
									$currcol ++;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($b,2,",","."));
									$currcol ++;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($a-$b,2,",","."));
									
									$currrow++;
									$currcol=4;
									$sheet->mergeCells('C'.$currrow.':D'.$currrow);
									$sheet->setCellValueByColumnAndRow(3, $currrow, 'NK '.rtrim($l['wilayah']));
									$currcol ++;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($a,2,",","."));
									$currcol ++;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($b,2,",","."));
									$currcol ++;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($a-$b,2,",","."));
									$currcol ++;
									$currrow++;


									$sheet->mergeCells('A'.$currrow.':'.$merge2.$currrow);
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['wilayah']));
									$KdWilayah=rtrim($l['wilayah']);
									$tamp_type_nota=rtrim($l['Type_Nota']);
									$tamp_Kategori_Lain=rtrim($l['Kategori_Lain']);
									$a=0;
									$b=0;
								}


								$currcol=1;
								$sheet->mergeCells('A'.$currrow.':'.$merge2.$currrow);
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['nm_plg']));
								$currcol++;

								if($alamat==1){
									$currcol_alamat=9;
								}else{
									$currcol_alamat=8;
								}

								$sheet->setCellValueByColumnAndRow($merge3, $currrow, rtrim($l['kd_plg']));
								$currrow ++;


								$total=0;
								$total_dist=0;
											
							}else{

								$kodePelanggan=rtrim($l['kd_plg']);

							}
					
						}


						if ($l['Type_terima'] = "PERDANA"){
							$Type_terima='';
						}else{
							$Type_terima=$l['Type_terima'];
						}

						
						$total_dist = $total_dist+$l['total_distribusi'];
						$b=$b+$l['total_distribusi'];



						if((!empty($No_bukti) && $No_bukti!==rtrim($l['No_bukti'])) || $no==1){
							$currcol=1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
							$currcol++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, date_format(date_create($l['tgl_trans']),'d-m-Y'));
							$currcol++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['No_bukti']));
							$currcol++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['no_distribusi']));
							$currcol++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['Total'],2,",","."));
							$currcol++;

							$No_bukti=rtrim($l['No_bukti']);
							$No_distribusi=rtrim($l['no_distribusi']);
							$tamp_total=$l['Total'];
							$total = $total+$l['Total'];

							$a=$a+$l['Total'];
							
						}else{
							$currcol=4;
							if($No_distribusi!==rtrim($l['no_distribusi'])){
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['no_distribusi']));
								$currcol++;
								$No_distribusi=rtrim($l['no_distribusi']);
							}
						}

						$tamp_total=$tamp_total-$l['total_distribusi'];
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['total_distribusi'],2,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($tamp_total,2,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['Type_terima']));
						$currcol++;

						if($alamat==1){
							if ($l['Kd_Wil']=="NNNN"){
								$sheet->setCellValueByColumnAndRow(9, $currrow, rtrim($l['alm_plg']));
							}else{
								$sheet->setCellValueByColumnAndRow(9, $currrow, rtrim($l['Nm_Wil']));
							}
						}
						$no++;
						$currrow++;
					}



					$sheet->mergeCells('C'.$currrow.':D'.$currrow);
					$sheet->setCellValueByColumnAndRow(3, $currrow, 'Total '.rtrim($nmplg));
					$currcol=5;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,2,",","."));
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_dist,2,",","."));
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total-$total_dist,2,",","."));
					$currcol ++;
					$currrow++;



					$currcol=4;
					$sheet->mergeCells('C'.$currrow.':D'.$currrow);
					$sheet->setCellValueByColumnAndRow(3, $currrow, 'NK '.rtrim($tamp_Kategori_Lain));
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($a,2,",","."));
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($b,2,",","."));
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($a-$b,2,",","."));
					
					$currrow++;
					$currcol=4;
					$sheet->mergeCells('C'.$currrow.':D'.$currrow);
					$sheet->setCellValueByColumnAndRow(3, $currrow, 'NK '.rtrim($tamp_type_nota));
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($a,2,",","."));
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($b,2,",","."));
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($a-$b,2,",","."));
					
					$currrow++;
					$currcol=4;
					$sheet->mergeCells('C'.$currrow.':D'.$currrow);
					$sheet->setCellValueByColumnAndRow(3, $currrow, 'NK '.rtrim($KdWilayah));
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($a,2,",","."));
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($b,2,",","."));
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($a-$b,2,",","."));
					$currcol ++;
					$currrow++;


				for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
				    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
				}

				$filename='LAPORAN BUKU HARIAN ['.date('Y-m-d H-i-s').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');
				exit();

			}

		}

	}
}
?>