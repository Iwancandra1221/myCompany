
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class Reportjualreturdealerparentdiv extends MY_Controller 
	{
        public function __construct()
		{
			parent::__construct();
			$this->load->model('HelperModel');
			$this->load->helper('FormLibrary');
			$this->load->library('excel');
			$this->load->model('ReportjualreturdealerparentdivModel');
		}

		public function index($error=''){
			$this->ModuleModel->CheckAccess($this->uri->segment(1), ''); 
			if($_SESSION["can_read"]==true){
				$data['divisi'] = $this->ReportjualreturdealerparentdivModel->divisi();
				$data['wilayah'] = $this->ReportjualreturdealerparentdivModel->wilayah();
				$data['tipe_faktur'] = $this->ReportjualreturdealerparentdivModel->tipe_faktur();
				$data['partner_type'] = $this->ReportjualreturdealerparentdivModel->partner_type();
				$this->RenderView('ReportjualreturdealerparentdivView',$data);
			}else{
				redirect('dashboard');
			}
		}


	public function pdf($from='',$until='',$divisi='',$partner_type='',$wilayah='',$kategori='',$tipe_faktur='',$perkategori='',$printper='',$report=''){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

		if($_SESSION["can_read"]==true && !empty($from) && !empty($until) && !empty($printper) && !empty($report)){

			if($printper==1){
				if($report==1){
					$this->pdf_dealer_per_parent_divisi($from,$until,$divisi,$partner_type,$wilayah,$kategori,$tipe_faktur,$perkategori,$printper,$report);
				}else if($report==2){
					$this->pdf_dealer($from,$until,$divisi,$partner_type,$wilayah,$kategori,$tipe_faktur,$perkategori,$printper,$report);
				}else if($report==3){
					$this->pdf_divisi($from,$until,$divisi,$partner_type,$wilayah,$kategori,$tipe_faktur,$perkategori,$printper,$report);
				}
			}else{
				if($report==1){
					$this->pdf_dealer_per_parent_divisi_kota($from,$until,$divisi,$partner_type,$wilayah,$kategori,$tipe_faktur,$perkategori,$printper,$report);
				}else{
					$this->pdf_parentdiv_per_kota($from,$until,$divisi,$partner_type,$wilayah,$kategori,$tipe_faktur,$perkategori,$printper,$report);
				}
			}

		}

	}

	public function excel($from='',$until='',$divisi='',$partner_type='',$wilayah='',$kategori='',$tipe_faktur='',$perkategori='',$printper='',$report=''){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

		if($_SESSION["can_read"]==true && !empty($from) && !empty($until) && !empty($printper) && !empty($report)){

			if($printper==1){
				if($report==1){
					$this->excel_dealer_per_parent_divisi($from,$until,$divisi,$partner_type,$wilayah,$kategori,$tipe_faktur,$perkategori,$printper,$report);
				}else if($report==2){
					$this->excel_dealer($from,$until,$divisi,$partner_type,$wilayah,$kategori,$tipe_faktur,$perkategori,$printper,$report);
				}else if($report==3){
					$this->excel_divisi($from,$until,$divisi,$partner_type,$wilayah,$kategori,$tipe_faktur,$perkategori,$printper,$report);
				}
			}else{
				if($report==1){
					$this->excel_dealer_per_parent_divisi_kota($from,$until,$divisi,$partner_type,$wilayah,$kategori,$tipe_faktur,$perkategori,$printper,$report);
				}else{
					$this->excel_parentdiv_per_kota($from,$until,$divisi,$partner_type,$wilayah,$kategori,$tipe_faktur,$perkategori,$printper,$report);
				}
			}

		}

	}

	public function pdf_dealer_per_parent_divisi($from='',$until='',$divisi='',$partner_type='',$wilayah='',$kategori='',$tipe_faktur='',$perkategori='',$printper='',$report=''){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

		if($_SESSION["can_read"]==true && !empty($from) && !empty($until) && !empty($printper) && !empty($report)){

			$data['from'] = $from;
			$data['until'] = $until;
			$data['divisi'] = $divisi;
			$data['partner_type'] = $partner_type;
			$data['wilayah'] = $wilayah;
			$data['kategori'] = $kategori;
			$data['tipe_faktur'] = $tipe_faktur;
			$data['perkategori'] = $perkategori;
			$data['printper'] = $printper;
			$data['report'] = $report;
			$list = $this->ReportjualreturdealerparentdivModel->GetData($data);

			$list = json_decode($list,true);

			if($list['hasil']=='success'){

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
					'margin_top' => 39,
					'margin_bottom' => 10,
					'margin_header' => 10,
					'margin_footer' => 5,
					'orientation' => 'P'
				));

				if($kategori=='ALL'){
					$kategori='PRODUCT & SPAREPART';
				}

				$header ='<table width="100%">';
				$header .='<tr><td>'.date('Y-m-d H-i-s').'</td><td align="right">Page {PAGENO} of {nbpg}</td></tr>';
				$header .='<tr><td align="center" colspan="2" style="font-size:25px"></td></tr>';				
				$header .='<tr><td align="center" colspan="2" style="font-size:15px"><b>LAPORAN JUAL-RETUR DEALER PER PARENTDIV</b></td></tr>';
				$header .='<tr><td align="center" colspan="2" style="font-size:13.9px">'.$from.' S/D '.$until.'</td></tr>';
				$header .='<tr><td align="center" colspan="2"><table border="0" width="250px"><tr><td style="border:thin solid #000000; padding:3px 3px; font-size:13px">'.$kategori.'</td></tr></table></td></tr>';				
				$header .='</table>';

				$content='';

				$tampung_wilayah='';
				$tampung_pelanggan='';
				$tampung_partner_type='';

				$nm_wilayah='';
				$nm_pelanggan='';

				$total_jual=0;
				$total_retur=0;
				$total_plg=0;


				$grand_total_partner_type_jual=0;
				$grand_total_partner_type_retur=0;
				$grand_total_partner_type_plg=0;

				$grand_total_wilayah_jual=0;
				$grand_total_wilayah_retur=0;
				$grand_total_wilayah_plg=0;


				$grand_total_jual=0;
				$grand_total_retur=0;
				$grand_total_plg=0;

				foreach ($list['data'] as $key => $l) {


					if(!empty($tampung_pelanggan) && $tampung_pelanggan!==rtrim($l['Kd_Plg'])){
						$content .='<tr>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" align="center" valign="top"><b>'.$nm_pelanggan.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$total_jual=0;
						$total_retur=0;
						$total_plg=0;
					}


					if(!empty($tampung_wilayah) && $tampung_wilayah!==rtrim($l['Wilayah'])){
						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$nm_wilayah.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$grand_total_wilayah_jual=0;
						$grand_total_wilayah_retur=0;
						$grand_total_wilayah_plg=0;

					}

					if(!empty($tampung_partner_type) && $tampung_partner_type!==rtrim($l['Partner_Type'])){
						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$tampung_partner_type.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$grand_total_partner_type_jual=0;
						$grand_total_partner_type_retur=0;
						$grand_total_partner_type_plg=0;

					}


					if(!empty($tampung_wilayah) && !empty($tampung_partner_type) && ($tampung_wilayah!==rtrim($l['Wilayah']) || $tampung_partner_type!==rtrim($l['Partner_Type']) || $tampung_pelanggan!==rtrim($l['Kd_Plg']))){
						$content .='</table>';
					}



					if($tampung_wilayah!==rtrim($l['Wilayah']) || $tampung_partner_type!==rtrim($l['Partner_Type'])){


						$content .='<table width="100%" border="0" style="margin-top:10px;">';
						$content .='<tr>';
						$content .='<td valign="top"><b>PARTNER TYPE : '.$l['Partner_Type'].'</b></td>';
						$content .='</tr>';
						$content .='<tr>';
						$content .='<td valign="top"><b>WILAYAH : '.$l['Wilayah'].'</b></td>';
						$content .='</tr>';
						$content .='</table>';

					}

					
					if($tampung_wilayah!==rtrim($l['Wilayah']) || $tampung_partner_type!==rtrim($l['Partner_Type']) || $tampung_pelanggan!==rtrim($l['Kd_Plg'])){

						$tampung_wilayah=rtrim($l['Wilayah']);
						$tampung_partner_type=rtrim($l['Partner_Type']);
						$tampung_pelanggan=rtrim($l['Kd_Plg']);
						$nm_pelanggan=rtrim($l['Nm_Plg']);
						$nm_wilayah=rtrim($l['Wilayah']);


						$content .='<table width="100%" border="0" style="margin-top:5px;">';
						$content .='<tr>';
						$content .='<td valign="top"><b>NAMA TOKO : '.$l['Nm_Plg'].'</b></td>';
						$content .='</tr>';
						$content .='</table>';

						$content .='<table width="100%" border="0" style="border-collapse:collapse;">';
						$content .='<tr>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top"><b>PARENTDIV</b></td>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top" align="right"><b>TOTAL JUAL</b></td>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top" align="right"><b>TOTAL RETUR</b></td>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top" align="right"><b>TOTAL</b></td>';
						$content .='</tr>';
					}

					$content .='<tr>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top">'.$l['ParentDiv'].'</td>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top" align="right">'.number_format($l['Total_Jual'],0,",",".").'</td>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top" align="right">'.number_format($l['Total_Retur'],0,",",".").'</td>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top" align="right">'.number_format($l['Total'],0,",",".").'</td>';
					$content .='</tr>';

					$total_jual=$total_jual+$l['Total_Jual'];
					$total_retur=$total_retur+$l['Total_Retur'];
					$total_plg=$total_plg+$l['Total'];

					$grand_total_wilayah_jual=$grand_total_wilayah_jual+$l['Total_Jual'];
					$grand_total_wilayah_retur=$grand_total_wilayah_retur+$l['Total_Retur'];
					$grand_total_wilayah_plg=$grand_total_wilayah_plg+$l['Total'];

					$grand_total_partner_type_jual=$grand_total_partner_type_jual+$l['Total_Jual'];
					$grand_total_partner_type_retur=$grand_total_partner_type_retur+$l['Total_Retur'];
					$grand_total_partner_type_plg=$grand_total_partner_type_plg+$l['Total'];

					$grand_total_jual=$grand_total_jual+$l['Total_Jual'];
					$grand_total_retur=$grand_total_retur+$l['Total_Retur'];
					$grand_total_plg=$grand_total_plg+$l['Total'];

				}


					if(!empty($nm_pelanggan)){
						$content .='<tr>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" align="center" valign="top"><b>'.$nm_pelanggan.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						

						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$nm_wilayah.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						
						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$tampung_partner_type.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="padding:5px;" align="center" valign="top"><b>GRANDTOTAL</b></td>';
						$content .='<td style="padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_jual,0,",",".").'</b></td>';
						$content .='<td style="padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_retur,0,",",".").'</b></td>';
						$content .='<td style="padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_plg,0,",",".").'</b></td>';
						$content .='</tr>';
					}


					$content .='</table>';


					set_time_limit(0);
					$mpdf->SetHTMLHeader($header,'','1');
					$mpdf->WriteHTML($content);
					$mpdf->Output();

				}


			}else{
				redirect(site_url('Reportjualreturdealerparentdiv/?error=error'));
			}

		}


	public function excel_dealer_per_parent_divisi($from='',$until='',$divisi='',$partner_type='',$wilayah='',$kategori='',$tipe_faktur='',$perkategori='',$printper='',$report=''){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

		if($_SESSION["can_read"]==true && !empty($from) && !empty($until) && !empty($printper) && !empty($report)){

			$data['from'] = $from;
			$data['until'] = $until;
			$data['divisi'] = $divisi;
			$data['partner_type'] = $partner_type;
			$data['wilayah'] = $wilayah;
			$data['kategori'] = $kategori;
			$data['tipe_faktur'] = $tipe_faktur;
			$data['perkategori'] = $perkategori;
			$data['printper'] = $printper;
			$data['report'] = $report;
			$list = $this->ReportjualreturdealerparentdivModel->GetData($data);

			$list = json_decode($list,true);

			if($list['hasil']=='success'){

				$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet(0);

				ini_set('max_execution_time', '1500');
				ini_set("pcre.backtrack_limit", "10000000");
				set_time_limit(0);

				if($kategori=='ALL'){
					$kategori='PRODUCT & SPAREPART';
				}

				$sheet->setTitle('LAPORAN BUKU HARIAN');
				$sheet->setCellValue('A1', 'Printed : '.date('Y-m-d H-i-s'));
				$sheet->setCellValue('A2', 'LAPORAN JUAL-RETUR DEALER PER PARENTDIV');
				$sheet->setCellValue('A3', $from.' S/D '.$until);
				$sheet->setCellValue('A4', $kategori);


				$sheet->mergeCells('A1:D1');
				$sheet->mergeCells('A2:D2');
				$sheet->mergeCells('A3:D3');
				$sheet->mergeCells('A4:D4');

				$sheet->getStyle('A1')->getFont()->setSize(10);
				$sheet->getStyle('A2')->getFont()->setSize(20);
				$sheet->getStyle('A3')->getFont()->setSize(15);
				$sheet->getStyle('A4')->getFont()->setSize(12);

				$sheet->getStyle('A1:D1')->getAlignment()->setHorizontal('right');
				$sheet->getStyle('A2:D2')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A3:D3')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A4:D4')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A2:D2')->getFont()->setBold(true);

				$tampung_wilayah='';
				$tampung_pelanggan='';
				$tampung_partner_type='';

				$nm_wilayah='';
				$nm_pelanggan='';

				$total_jual=0;
				$total_retur=0;
				$total_plg=0;


				$grand_total_partner_type_jual=0;
				$grand_total_partner_type_retur=0;
				$grand_total_partner_type_plg=0;

				$grand_total_wilayah_jual=0;
				$grand_total_wilayah_retur=0;
				$grand_total_wilayah_plg=0;


				$grand_total_jual=0;
				$grand_total_retur=0;
				$grand_total_plg=0;


				$currcol = 1;
				$currrow = 5;

				foreach ($list['data'] as $key => $l) {


					if(!empty($tampung_pelanggan) && $tampung_pelanggan!==rtrim($l['Kd_Plg'])){

						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $nm_pelanggan);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_jual);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_retur);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
						$currrow++;

						$total_jual=0;
						$total_retur=0;
						$total_plg=0;
					}


					if(!empty($tampung_wilayah) && $tampung_wilayah!==rtrim($l['Wilayah'])){
						$currrow++;
						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $nm_wilayah);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_jual);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_retur);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;

						$grand_total_wilayah_jual=0;
						$grand_total_wilayah_retur=0;
						$grand_total_wilayah_plg=0;

					}

					if(!empty($tampung_partner_type) && $tampung_partner_type!==rtrim($l['Partner_Type'])){
						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_partner_type);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_jual);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_retur);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);		
						$currrow++;


						$grand_total_partner_type_jual=0;
						$grand_total_partner_type_retur=0;
						$grand_total_partner_type_plg=0;

					}



					if($tampung_wilayah!==rtrim($l['Wilayah']) || $tampung_partner_type!==rtrim($l['Partner_Type'])){
						$currrow++;
						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARTNER TYPE : '.$l['Partner_Type']);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'WILAYAH : '.$l['Wilayah']);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;
					}

					
					if($tampung_wilayah!==rtrim($l['Wilayah']) || $tampung_partner_type!==rtrim($l['Partner_Type']) || $tampung_pelanggan!==rtrim($l['Kd_Plg'])){

						$tampung_wilayah=rtrim($l['Wilayah']);
						$tampung_partner_type=rtrim($l['Partner_Type']);
						$tampung_pelanggan=rtrim($l['Kd_Plg']);
						$nm_pelanggan=rtrim($l['Nm_Plg']);
						$nm_wilayah=rtrim($l['Wilayah']);

						// $sheet->getStyle()->getFont()->setBold(true);

						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA TOKO : '.$l['Nm_Plg']);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;
						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARENTDIV');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL JUAL');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL RETUR');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);

						$currrow++;
					}

					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['ParentDiv']);
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['Total_Jual']);
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['Total_Retur']);
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['Total']);
					$currrow++;
					

					$total_jual=$total_jual+$l['Total_Jual'];
					$total_retur=$total_retur+$l['Total_Retur'];
					$total_plg=$total_plg+$l['Total'];

					$grand_total_wilayah_jual=$grand_total_wilayah_jual+$l['Total_Jual'];
					$grand_total_wilayah_retur=$grand_total_wilayah_retur+$l['Total_Retur'];
					$grand_total_wilayah_plg=$grand_total_wilayah_plg+$l['Total'];

					$grand_total_partner_type_jual=$grand_total_partner_type_jual+$l['Total_Jual'];
					$grand_total_partner_type_retur=$grand_total_partner_type_retur+$l['Total_Retur'];
					$grand_total_partner_type_plg=$grand_total_partner_type_plg+$l['Total'];

					$grand_total_jual=$grand_total_jual+$l['Total_Jual'];
					$grand_total_retur=$grand_total_retur+$l['Total_Retur'];
					$grand_total_plg=$grand_total_plg+$l['Total'];

				}


					if(!empty($nm_pelanggan)){

						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $nm_pelanggan);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_jual);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_retur);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_plg);
						$currcol ++;
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
						$currrow=$currrow+2;;


						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $nm_wilayah);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_jual);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_retur);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;


						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_partner_type);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_jual);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_retur);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow=$currrow+2;

						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_jual);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_retur);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;

					}



					for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
					    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
					}

					$filename='LAPORAN JUAL-RETUR DEALER PER PARENTDIV ['.date('Y-m-d H-i-s').']'; //save our workbook as this file name
					$writer = new Xlsx($spreadsheet);
					header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
					header('Cache-Control: max-age=0');
					ob_end_clean();
					$writer->save('php://output');
					exit();


				}


			}else{
				redirect(site_url('Reportjualreturdealerparentdiv/?error=error'));
			}

		}


	public function pdf_dealer($from='',$until='',$divisi='',$partner_type='',$wilayah='',$kategori='',$tipe_faktur='',$perkategori='',$printper='',$report=''){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

		if($_SESSION["can_read"]==true && !empty($from) && !empty($until) && !empty($printper) && !empty($report)){

			$data['from'] = $from;
			$data['until'] = $until;
			$data['divisi'] = $divisi;
			$data['partner_type'] = $partner_type;
			$data['wilayah'] = $wilayah;
			$data['kategori'] = $kategori;
			$data['tipe_faktur'] = $tipe_faktur;
			$data['perkategori'] = $perkategori;
			$data['printper'] = $printper;
			$data['report'] = $report;
			$list = $this->ReportjualreturdealerparentdivModel->GetData($data);

			$list = json_decode($list,true);

			if($list['hasil']=='success'){

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
					'margin_top' => 39,
					'margin_bottom' => 10,
					'margin_header' => 10,
					'margin_footer' => 5,
					'orientation' => 'P'
				));

				if($kategori=='ALL'){
					$kategori='PRODUCT & SPAREPART';
				}

				$header ='<table width="100%">';
				$header .='<tr><td>'.date('Y-m-d H-i-s').'</td><td align="right">Page {PAGENO} of {nbpg}</td></tr>';
				$header .='<tr><td align="center" colspan="2" style="font-size:25px"></td></tr>';				
				$header .='<tr><td align="center" colspan="2" style="font-size:15px"><b>LAPORAN JUAL-RETUR PARENTDIV PER DEALER</b></td></tr>';
				$header .='<tr><td align="center" colspan="2" style="font-size:13.9px">'.$from.' S/D '.$until.'</td></tr>';
				$header .='<tr><td align="center" colspan="2"><table border="0" width="250px"><tr><td style="border:thin solid #000000; padding:3px 3px; font-size:13px">'.$kategori.'</td></tr></table></td></tr>';				
				$header .='</table>';

				$content='';

				$tampung_wilayah='';
				$tampung_pelanggan='';
				$tampung_partner_type='';
				$tampung_parentdiv='';

				$nm_wilayah='';
				$nm_pelanggan='';

				$total_jual=0;
				$total_retur=0;
				$total_plg=0;


				$grand_total_partner_type_jual=0;
				$grand_total_partner_type_retur=0;
				$grand_total_partner_type_plg=0;

				$grand_total_wilayah_jual=0;
				$grand_total_wilayah_retur=0;
				$grand_total_wilayah_plg=0;


				$grand_total_jual=0;
				$grand_total_retur=0;
				$grand_total_plg=0;

				foreach ($list['data'] as $key => $l) {


					if(!empty($tampung_parentdiv) && $tampung_parentdiv!==rtrim($l['ParentDiv'])){
						$content .='<tr>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" align="center" valign="top"><b>'.$tampung_parentdiv.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$total_jual=0;
						$total_retur=0;
						$total_plg=0;
					}


					if(!empty($tampung_wilayah) && $tampung_wilayah!==rtrim($l['Wilayah'])){
						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$nm_wilayah.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$grand_total_wilayah_jual=0;
						$grand_total_wilayah_retur=0;
						$grand_total_wilayah_plg=0;

					}

					if(!empty($tampung_partner_type) && $tampung_partner_type!==rtrim($l['Partner_Type'])){
						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$tampung_partner_type.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$grand_total_partner_type_jual=0;
						$grand_total_partner_type_retur=0;
						$grand_total_partner_type_plg=0;

					}


					if(!empty($tampung_wilayah) && !empty($tampung_partner_type) && ($tampung_wilayah!==rtrim($l['Wilayah']) || $tampung_partner_type!==rtrim($l['Partner_Type']) || $tampung_pelanggan!==rtrim($l['Kd_Plg']) || $tampung_parentdiv!==rtrim($l['ParentDiv']))){
						$content .='</table>';
					}



					if($tampung_wilayah!==rtrim($l['Wilayah']) || $tampung_partner_type!==rtrim($l['Partner_Type'])){


						$content .='<table width="100%" border="0" style="margin-top:10px;">';
						$content .='<tr>';
						$content .='<td valign="top"><b>PARTNER TYPE : '.$l['Partner_Type'].'</b></td>';
						$content .='</tr>';
						$content .='<tr>';
						$content .='<td valign="top"><b>WILAYAH : '.$l['Wilayah'].'</b></td>';
						$content .='</tr>';
						$content .='</table>';

					}

					
					if($tampung_wilayah!==rtrim($l['Wilayah']) || $tampung_partner_type!==rtrim($l['Partner_Type']) || $tampung_pelanggan!==rtrim($l['Kd_Plg']) || $tampung_parentdiv!==rtrim($l['ParentDiv'])){

						$tampung_wilayah=rtrim($l['Wilayah']);
						$tampung_partner_type=rtrim($l['Partner_Type']);
						$tampung_pelanggan=rtrim($l['Kd_Plg']);
						$nm_pelanggan=rtrim($l['Nm_Plg']);
						$nm_wilayah=rtrim($l['Wilayah']);
						$tampung_parentdiv=rtrim($l['ParentDiv']);


						$content .='<table width="100%" border="0" style="margin-top:5px;">';
						$content .='<tr>';
						$content .='<td valign="top"><b>PARENTDIV : '.$l['ParentDiv'].'</b></td>';
						$content .='</tr>';
						$content .='</table>';

						$content .='<table width="100%" border="0" style="border-collapse:collapse;">';
						$content .='<tr>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top"><b>NAMA TOKO</b></td>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top" align="right"><b>TOTAL JUAL</b></td>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top" align="right"><b>TOTAL RETUR</b></td>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top" align="right"><b>TOTAL</b></td>';
						$content .='</tr>';
					}

					$content .='<tr>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top">'.$l['Nm_Plg'].'</td>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top" align="right">'.number_format($l['Total_Jual'],0,",",".").'</td>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top" align="right">'.number_format($l['Total_Retur'],0,",",".").'</td>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top" align="right">'.number_format($l['Total'],0,",",".").'</td>';
					$content .='</tr>';

					$total_jual=$total_jual+$l['Total_Jual'];
					$total_retur=$total_retur+$l['Total_Retur'];
					$total_plg=$total_plg+$l['Total'];

					$grand_total_wilayah_jual=$grand_total_wilayah_jual+$l['Total_Jual'];
					$grand_total_wilayah_retur=$grand_total_wilayah_retur+$l['Total_Retur'];
					$grand_total_wilayah_plg=$grand_total_wilayah_plg+$l['Total'];

					$grand_total_partner_type_jual=$grand_total_partner_type_jual+$l['Total_Jual'];
					$grand_total_partner_type_retur=$grand_total_partner_type_retur+$l['Total_Retur'];
					$grand_total_partner_type_plg=$grand_total_partner_type_plg+$l['Total'];

					$grand_total_jual=$grand_total_jual+$l['Total_Jual'];
					$grand_total_retur=$grand_total_retur+$l['Total_Retur'];
					$grand_total_plg=$grand_total_plg+$l['Total'];

				}


					if(!empty($nm_pelanggan)){
						$content .='<tr>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" align="center" valign="top"><b>'.$tampung_parentdiv.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						

						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$nm_wilayah.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						
						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$tampung_partner_type.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="padding:5px;" align="center" valign="top"><b>GRANDTOTAL</b></td>';
						$content .='<td style="padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_jual,0,",",".").'</b></td>';
						$content .='<td style="padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_retur,0,",",".").'</b></td>';
						$content .='<td style="padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_plg,0,",",".").'</b></td>';
						$content .='</tr>';
					}


					$content .='</table>';


					set_time_limit(0);
					$mpdf->SetHTMLHeader($header,'','1');
					$mpdf->WriteHTML($content);
					$mpdf->Output();

				}


			}else{
				redirect(site_url('Reportjualreturdealerparentdiv/?error=error'));
			}

		}

		public function excel_dealer($from='',$until='',$divisi='',$partner_type='',$wilayah='',$kategori='',$tipe_faktur='',$perkategori='',$printper='',$report=''){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

		if($_SESSION["can_read"]==true && !empty($from) && !empty($until) && !empty($printper) && !empty($report)){

			$data['from'] = $from;
			$data['until'] = $until;
			$data['divisi'] = $divisi;
			$data['partner_type'] = $partner_type;
			$data['wilayah'] = $wilayah;
			$data['kategori'] = $kategori;
			$data['tipe_faktur'] = $tipe_faktur;
			$data['perkategori'] = $perkategori;
			$data['printper'] = $printper;
			$data['report'] = $report;
			$list = $this->ReportjualreturdealerparentdivModel->GetData($data);

			$list = json_decode($list,true);

			if($list['hasil']=='success'){

				$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet(0);

				ini_set('max_execution_time', '1500');
				ini_set("pcre.backtrack_limit", "10000000");
				set_time_limit(0);

				if($kategori=='ALL'){
					$kategori='PRODUCT & SPAREPART';
				}

				// $sheet->setTitle('LAPORAN JUAL-RETUR PARENTDIV PER DEALER');
				$sheet->setCellValue('A1', 'Printed : '.date('Y-m-d H-i-s'));
				$sheet->setCellValue('A2', 'LAPORAN JUAL-RETUR PARENTDIV PER DEALER');
				$sheet->setCellValue('A3', $from.' S/D '.$until);
				$sheet->setCellValue('A4', $kategori);


				$sheet->mergeCells('A1:D1');
				$sheet->mergeCells('A2:D2');
				$sheet->mergeCells('A3:D3');
				$sheet->mergeCells('A4:D4');

				$sheet->getStyle('A1')->getFont()->setSize(10);
				$sheet->getStyle('A2')->getFont()->setSize(20);
				$sheet->getStyle('A3')->getFont()->setSize(15);
				$sheet->getStyle('A4')->getFont()->setSize(12);

				$sheet->getStyle('A1:D1')->getAlignment()->setHorizontal('right');
				$sheet->getStyle('A2:D2')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A3:D3')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A4:D4')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A2:D2')->getFont()->setBold(true);

				$tampung_wilayah='';
				$tampung_pelanggan='';
				$tampung_partner_type='';
				$tampung_parentdiv='';

				$nm_wilayah='';
				$nm_pelanggan='';

				$total_jual=0;
				$total_retur=0;
				$total_plg=0;


				$grand_total_partner_type_jual=0;
				$grand_total_partner_type_retur=0;
				$grand_total_partner_type_plg=0;

				$grand_total_wilayah_jual=0;
				$grand_total_wilayah_retur=0;
				$grand_total_wilayah_plg=0;


				$grand_total_jual=0;
				$grand_total_retur=0;
				$grand_total_plg=0;

				$currcol = 1;
				$currrow = 5;

				foreach ($list['data'] as $key => $l) {


					if(!empty($tampung_parentdiv) && $tampung_parentdiv!==rtrim($l['ParentDiv'])){
						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_parentdiv);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_jual);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_retur);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
						$currrow++;

						$total_jual=0;
						$total_retur=0;
						$total_plg=0;
					}


					if(!empty($tampung_wilayah) && $tampung_wilayah!==rtrim($l['Wilayah'])){
						$currrow++;
						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $nm_wilayah);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_jual);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_retur);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;


						$grand_total_wilayah_jual=0;
						$grand_total_wilayah_retur=0;
						$grand_total_wilayah_plg=0;

					}

					if(!empty($tampung_partner_type) && $tampung_partner_type!==rtrim($l['Partner_Type'])){
						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_partner_type);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_jual);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_retur);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;

						$grand_total_partner_type_jual=0;
						$grand_total_partner_type_retur=0;
						$grand_total_partner_type_plg=0;

					}

					if($tampung_wilayah!==rtrim($l['Wilayah']) || $tampung_partner_type!==rtrim($l['Partner_Type'])){
						$currrow++;
						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow,'PARTNER TYPE : '.$l['Partner_Type']);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow,'WILAYAH : '.$l['Wilayah']);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;


					}

					
					if($tampung_wilayah!==rtrim($l['Wilayah']) || $tampung_partner_type!==rtrim($l['Partner_Type']) || $tampung_pelanggan!==rtrim($l['Kd_Plg']) || $tampung_parentdiv!==rtrim($l['ParentDiv'])){

						$tampung_wilayah=rtrim($l['Wilayah']);
						$tampung_partner_type=rtrim($l['Partner_Type']);
						$tampung_pelanggan=rtrim($l['Kd_Plg']);
						$nm_pelanggan=rtrim($l['Nm_Plg']);
						$nm_wilayah=rtrim($l['Wilayah']);
						$tampung_parentdiv=rtrim($l['ParentDiv']);

						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow,'PARENTDIV : '.$l['ParentDiv']);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;

						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow,'NAMA TOKO');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow,'TOTAL JUAL');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow,'TOTAL RETUR');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow,'TOTAL');
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$currrow++;
					}


					$currcol=1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['Nm_Plg']);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['Total_Jual']);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['Total_Retur']);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['Total']);
					$currrow++;


					$total_jual=$total_jual+$l['Total_Jual'];
					$total_retur=$total_retur+$l['Total_Retur'];
					$total_plg=$total_plg+$l['Total'];

					$grand_total_wilayah_jual=$grand_total_wilayah_jual+$l['Total_Jual'];
					$grand_total_wilayah_retur=$grand_total_wilayah_retur+$l['Total_Retur'];
					$grand_total_wilayah_plg=$grand_total_wilayah_plg+$l['Total'];

					$grand_total_partner_type_jual=$grand_total_partner_type_jual+$l['Total_Jual'];
					$grand_total_partner_type_retur=$grand_total_partner_type_retur+$l['Total_Retur'];
					$grand_total_partner_type_plg=$grand_total_partner_type_plg+$l['Total'];

					$grand_total_jual=$grand_total_jual+$l['Total_Jual'];
					$grand_total_retur=$grand_total_retur+$l['Total_Retur'];
					$grand_total_plg=$grand_total_plg+$l['Total'];

				}


					if(!empty($nm_pelanggan)){

						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_parentdiv);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_jual);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_retur);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_plg);
						$currcol ++;
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
						$currrow=$currrow+2;;


						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $nm_wilayah);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_jual);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_retur);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;


						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_partner_type);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_jual);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_retur);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow=$currrow+2;

						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_jual);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_retur);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;
					}


					for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
					    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
					}

					$filename='LAPORAN JUAL-RETUR PARENTDIV PER DEALER ['.date('Y-m-d H-i-s').']'; //save our workbook as this file name
					$writer = new Xlsx($spreadsheet);
					header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
					header('Cache-Control: max-age=0');
					ob_end_clean();
					$writer->save('php://output');
					exit();

				}


			}else{
				redirect(site_url('Reportjualreturdealerparentdiv/?error=error'));
			}

		}

		public function pdf_divisi($from='',$until='',$divisi='',$partner_type='',$wilayah='',$kategori='',$tipe_faktur='',$perkategori='',$printper='',$report=''){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

		if($_SESSION["can_read"]==true && !empty($from) && !empty($until) && !empty($printper) && !empty($report)){

			$data['from'] = $from;
			$data['until'] = $until;
			$data['divisi'] = $divisi;
			$data['partner_type'] = $partner_type;
			$data['wilayah'] = $wilayah;
			$data['kategori'] = $kategori;
			$data['tipe_faktur'] = $tipe_faktur;
			$data['perkategori'] = $perkategori;
			$data['printper'] = $printper;
			$data['report'] = $report;
			$list = $this->ReportjualreturdealerparentdivModel->GetData($data);

			$list = json_decode($list,true);

			if($list['hasil']=='success'){

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
					'margin_top' => 39,
					'margin_bottom' => 10,
					'margin_header' => 10,
					'margin_footer' => 5,
					'orientation' => 'P'
				));

				if($kategori=='ALL'){
					$kategori='PRODUCT & SPAREPART';
				}

				$header ='<table width="100%">';
				$header .='<tr><td>'.date('Y-m-d H-i-s').'</td><td align="right">Page {PAGENO} of {nbpg}</td></tr>';
				$header .='<tr><td align="center" colspan="2" style="font-size:25px"></td></tr>';				
				$header .='<tr><td align="center" colspan="2" style="font-size:15px"><b>LAPORAN JUAL-RETUR PARENTDIV PER DEALER PER DIVISI</b></td></tr>';
				$header .='<tr><td align="center" colspan="2" style="font-size:13.9px">'.$from.' S/D '.$until.'</td></tr>';
				$header .='<tr><td align="center" colspan="2"><table border="0" width="250px"><tr><td style="border:thin solid #000000; padding:3px 3px; font-size:13px">'.$kategori.'</td></tr></table></td></tr>';				
				$header .='</table>';

				$content='';

				$tampung_wilayah='';
				$tampung_pelanggan='';
				$tampung_partner_type='';
				$tampung_parentdiv='';

				$nm_wilayah='';
				$nm_pelanggan='';

				$total_jual=0;
				$total_retur=0;
				$total_plg=0;


				$grand_total_partner_type_jual=0;
				$grand_total_partner_type_retur=0;
				$grand_total_partner_type_plg=0;

				$grand_total_wilayah_jual=0;
				$grand_total_wilayah_retur=0;
				$grand_total_wilayah_plg=0;


				$grand_total_jual=0;
				$grand_total_retur=0;
				$grand_total_plg=0;

				foreach ($list['data'] as $key => $l) {


					if(!empty($tampung_parentdiv) && $tampung_parentdiv!==rtrim($l['ParentDiv'])){
						$content .='<tr>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" align="center" valign="top"><b>'.$nm_pelanggan.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$content .='<tr>';
						$content .='<td align="center" valign="top"><b>'.$tampung_parentdiv.'</b></td>';
						$content .='<td valign="top" align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
						$content .='<td valign="top" align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
						$content .='<td valign="top" align="right"><b>'.number_format($total_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$total_jual=0;
						$total_retur=0;
						$total_plg=0;
					}


					if(!empty($tampung_wilayah) && $tampung_wilayah!==rtrim($l['Wilayah'])){
						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$nm_wilayah.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$grand_total_wilayah_jual=0;
						$grand_total_wilayah_retur=0;
						$grand_total_wilayah_plg=0;

					}

					if(!empty($tampung_partner_type) && $tampung_partner_type!==rtrim($l['Partner_Type'])){
						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$tampung_partner_type.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$grand_total_partner_type_jual=0;
						$grand_total_partner_type_retur=0;
						$grand_total_partner_type_plg=0;

					}


					if(!empty($tampung_wilayah) && !empty($tampung_partner_type) && ($tampung_wilayah!==rtrim($l['Wilayah']) || $tampung_partner_type!==rtrim($l['Partner_Type']) || $tampung_pelanggan!==rtrim($l['Kd_Plg']) || $tampung_parentdiv!==rtrim($l['ParentDiv']))){
						$content .='</table>';
					}



					if($tampung_wilayah!==rtrim($l['Wilayah']) || $tampung_partner_type!==rtrim($l['Partner_Type'])){


						$content .='<table width="100%" border="0" style="margin-top:10px;">';
						$content .='<tr>';
						$content .='<td valign="top"><b>PARTNER TYPE : '.$l['Partner_Type'].'</b></td>';
						$content .='</tr>';
						$content .='<tr>';
						$content .='<td valign="top"><b>WILAYAH : '.$l['Wilayah'].'</b></td>';
						$content .='</tr>';
						$content .='</table>';

					}

					
					if($tampung_wilayah!==rtrim($l['Wilayah']) || $tampung_partner_type!==rtrim($l['Partner_Type']) || $tampung_pelanggan!==rtrim($l['Kd_Plg']) || $tampung_parentdiv!==rtrim($l['ParentDiv'])){

						$tampung_wilayah=rtrim($l['Wilayah']);
						$tampung_partner_type=rtrim($l['Partner_Type']);
						$tampung_pelanggan=rtrim($l['Kd_Plg']);
						$nm_pelanggan=rtrim($l['Nm_Plg']);
						$nm_wilayah=rtrim($l['Wilayah']);
						$tampung_parentdiv=rtrim($l['ParentDiv']);


						$content .='<table width="100%" border="0" style="margin-top:5px;">';
						$content .='<tr>';
						$content .='<td valign="top"><b>NAMA TOKO : '.$l['Nm_Plg'].' '.$l['ParentDiv'].'</b></td>';
						$content .='</tr>';
						$content .='</table>';

						$content .='<table width="100%" border="0" style="border-collapse:collapse;">';
						$content .='<tr>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top"><b>DIVISI</b></td>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top" align="right"><b>TOTAL JUAL</b></td>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top" align="right"><b>TOTAL RETUR</b></td>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top" align="right"><b>TOTAL</b></td>';
						$content .='</tr>';
					}

					$content .='<tr>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top">'.$l['Divisi'].'</td>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top" align="right">'.number_format($l['Total_Jual'],0,",",".").'</td>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top" align="right">'.number_format($l['Total_Retur'],0,",",".").'</td>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top" align="right">'.number_format($l['Total'],0,",",".").'</td>';
					$content .='</tr>';

					$total_jual=$total_jual+$l['Total_Jual'];
					$total_retur=$total_retur+$l['Total_Retur'];
					$total_plg=$total_plg+$l['Total'];

					$grand_total_wilayah_jual=$grand_total_wilayah_jual+$l['Total_Jual'];
					$grand_total_wilayah_retur=$grand_total_wilayah_retur+$l['Total_Retur'];
					$grand_total_wilayah_plg=$grand_total_wilayah_plg+$l['Total'];

					$grand_total_partner_type_jual=$grand_total_partner_type_jual+$l['Total_Jual'];
					$grand_total_partner_type_retur=$grand_total_partner_type_retur+$l['Total_Retur'];
					$grand_total_partner_type_plg=$grand_total_partner_type_plg+$l['Total'];

					$grand_total_jual=$grand_total_jual+$l['Total_Jual'];
					$grand_total_retur=$grand_total_retur+$l['Total_Retur'];
					$grand_total_plg=$grand_total_plg+$l['Total'];

				}


					if(!empty($nm_pelanggan)){
						$content .='<tr>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" align="center" valign="top"><b>'.$nm_pelanggan.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$content .='<tr>';
						$content .='<td align="center" valign="top"><b>'.$tampung_parentdiv.'</b></td>';
						$content .='<td valign="top" align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
						$content .='<td valign="top" align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
						$content .='<td valign="top" align="right"><b>'.number_format($total_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						

						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$nm_wilayah.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						
						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$tampung_partner_type.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="padding:5px;" align="center" valign="top"><b>GRANDTOTAL</b></td>';
						$content .='<td style="padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_jual,0,",",".").'</b></td>';
						$content .='<td style="padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_retur,0,",",".").'</b></td>';
						$content .='<td style="padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_plg,0,",",".").'</b></td>';
						$content .='</tr>';
					}


					$content .='</table>';


					set_time_limit(0);
					$mpdf->SetHTMLHeader($header,'','1');
					$mpdf->WriteHTML($content);
					$mpdf->Output();

				}


			}else{
				redirect(site_url('Reportjualreturdealerparentdiv/?error=error'));
			}

		}

		public function excel_divisi($from='',$until='',$divisi='',$partner_type='',$wilayah='',$kategori='',$tipe_faktur='',$perkategori='',$printper='',$report=''){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

		if($_SESSION["can_read"]==true && !empty($from) && !empty($until) && !empty($printper) && !empty($report)){

			$data['from'] = $from;
			$data['until'] = $until;
			$data['divisi'] = $divisi;
			$data['partner_type'] = $partner_type;
			$data['wilayah'] = $wilayah;
			$data['kategori'] = $kategori;
			$data['tipe_faktur'] = $tipe_faktur;
			$data['perkategori'] = $perkategori;
			$data['printper'] = $printper;
			$data['report'] = $report;
			$list = $this->ReportjualreturdealerparentdivModel->GetData($data);

			$list = json_decode($list,true);

			if($list['hasil']=='success'){

				$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet(0);

				ini_set('max_execution_time', '1500');
				ini_set("pcre.backtrack_limit", "10000000");
				set_time_limit(0);

				if($kategori=='ALL'){
					$kategori='PRODUCT & SPAREPART';
				}

				// $sheet->setTitle('LAPORAN JUAL-RETUR PARENTDIV PER DEALER PER DIVISI');
				$sheet->setCellValue('A1', 'Printed : '.date('Y-m-d H-i-s'));
				$sheet->setCellValue('A2', 'LAPORAN JUAL-RETUR PARENTDIV PER DEALER PER DIVISI');
				$sheet->setCellValue('A3', $from.' S/D '.$until);
				$sheet->setCellValue('A4', $kategori);


				$sheet->mergeCells('A1:D1');
				$sheet->mergeCells('A2:D2');
				$sheet->mergeCells('A3:D3');
				$sheet->mergeCells('A4:D4');

				$sheet->getStyle('A1')->getFont()->setSize(10);
				$sheet->getStyle('A2')->getFont()->setSize(20);
				$sheet->getStyle('A3')->getFont()->setSize(15);
				$sheet->getStyle('A4')->getFont()->setSize(12);

				$sheet->getStyle('A1:D1')->getAlignment()->setHorizontal('right');
				$sheet->getStyle('A2:D2')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A3:D3')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A4:D4')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A2:D2')->getFont()->setBold(true);


				$tampung_wilayah='';
				$tampung_pelanggan='';
				$tampung_partner_type='';
				$tampung_parentdiv='';

				$nm_wilayah='';
				$nm_pelanggan='';

				$total_jual=0;
				$total_retur=0;
				$total_plg=0;


				$grand_total_partner_type_jual=0;
				$grand_total_partner_type_retur=0;
				$grand_total_partner_type_plg=0;

				$grand_total_wilayah_jual=0;
				$grand_total_wilayah_retur=0;
				$grand_total_wilayah_plg=0;


				$grand_total_jual=0;
				$grand_total_retur=0;
				$grand_total_plg=0;

				$currrow=5;
				$currcol=1;

				foreach ($list['data'] as $key => $l) {


					if(!empty($tampung_parentdiv) && $tampung_parentdiv!==rtrim($l['ParentDiv'])){


						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $nm_pelanggan);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_jual);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_retur);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
						$currrow++;

						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_parentdiv);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_jual);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_retur);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
						$currrow++;

						$total_jual=0;
						$total_retur=0;
						$total_plg=0;
					}


					if(!empty($tampung_wilayah) && $tampung_wilayah!==rtrim($l['Wilayah'])){

						$currrow++;
						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $nm_wilayah);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_jual);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_retur);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
						$currrow++;

						$grand_total_wilayah_jual=0;
						$grand_total_wilayah_retur=0;
						$grand_total_wilayah_plg=0;

					}

					if(!empty($tampung_partner_type) && $tampung_partner_type!==rtrim($l['Partner_Type'])){


						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_partner_type);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_jual);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_retur);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
						$currrow++;


						$grand_total_partner_type_jual=0;
						$grand_total_partner_type_retur=0;
						$grand_total_partner_type_plg=0;

					}


					if($tampung_wilayah!==rtrim($l['Wilayah']) || $tampung_partner_type!==rtrim($l['Partner_Type'])){

						$currrow++;
						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARTNER TYPE : '.$l['Partner_Type']);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'WILAYAH : '.$l['Wilayah']);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;


					}

					
					if($tampung_wilayah!==rtrim($l['Wilayah']) || $tampung_partner_type!==rtrim($l['Partner_Type']) || $tampung_pelanggan!==rtrim($l['Kd_Plg']) || $tampung_parentdiv!==rtrim($l['ParentDiv'])){

						$tampung_wilayah=rtrim($l['Wilayah']);
						$tampung_partner_type=rtrim($l['Partner_Type']);
						$tampung_pelanggan=rtrim($l['Kd_Plg']);
						$nm_pelanggan=rtrim($l['Nm_Plg']);
						$nm_wilayah=rtrim($l['Wilayah']);
						$tampung_parentdiv=rtrim($l['ParentDiv']);


						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA TOKO : '.$l['Nm_Plg'].' '.$l['ParentDiv']);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;


						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DIVISI');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL JUAL');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL RETUR');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$currrow++;

					}

					$currcol=1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['Divisi']);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['Total_Jual']);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['Total_Retur']);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['Total']);
					$currrow++;


					$total_jual=$total_jual+$l['Total_Jual'];
					$total_retur=$total_retur+$l['Total_Retur'];
					$total_plg=$total_plg+$l['Total'];

					$grand_total_wilayah_jual=$grand_total_wilayah_jual+$l['Total_Jual'];
					$grand_total_wilayah_retur=$grand_total_wilayah_retur+$l['Total_Retur'];
					$grand_total_wilayah_plg=$grand_total_wilayah_plg+$l['Total'];

					$grand_total_partner_type_jual=$grand_total_partner_type_jual+$l['Total_Jual'];
					$grand_total_partner_type_retur=$grand_total_partner_type_retur+$l['Total_Retur'];
					$grand_total_partner_type_plg=$grand_total_partner_type_plg+$l['Total'];

					$grand_total_jual=$grand_total_jual+$l['Total_Jual'];
					$grand_total_retur=$grand_total_retur+$l['Total_Retur'];
					$grand_total_plg=$grand_total_plg+$l['Total'];

				}


					if(!empty($nm_pelanggan)){

						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $nm_pelanggan);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_jual);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_retur);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$currrow++;


						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_parentdiv);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_jual);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_retur);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
						$currrow=$currrow+2;

						
						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $nm_wilayah);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_jual);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_retur);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;

						
						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_partner_type);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_jual);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_retur);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow=$currrow+2;

						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_jual);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_retur);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;

					}



					for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
					    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
					}

					$filename='LAPORAN JUAL-RETUR PARENTDIV PER DEALER PER DIVISI ['.date('Y-m-d H-i-s').']'; //save our workbook as this file name
					$writer = new Xlsx($spreadsheet);
					header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
					header('Cache-Control: max-age=0');
					ob_end_clean();
					$writer->save('php://output');
					exit();

				}


			}else{
				redirect(site_url('Reportjualreturdealerparentdiv/?error=error'));
			}

		}


		public function pdf_dealer_per_parent_divisi_kota($from='',$until='',$divisi='',$partner_type='',$wilayah='',$kategori='',$tipe_faktur='',$perkategori='',$printper='',$report=''){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

		if($_SESSION["can_read"]==true && !empty($from) && !empty($until) && !empty($printper) && !empty($report)){

			$data['from'] = $from;
			$data['until'] = $until;
			$data['divisi'] = $divisi;
			$data['partner_type'] = $partner_type;
			$data['wilayah'] = $wilayah;
			$data['kategori'] = $kategori;
			$data['tipe_faktur'] = $tipe_faktur;
			$data['perkategori'] = $perkategori;
			$data['printper'] = $printper;
			$data['report'] = $report;
			$list = $this->ReportjualreturdealerparentdivModel->GetData($data);

			$list = json_decode($list,true);

			if($list['hasil']=='success'){

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
					'margin_top' => 39,
					'margin_bottom' => 10,
					'margin_header' => 10,
					'margin_footer' => 5,
					'orientation' => 'P'
				));

				if($kategori=='ALL'){
					$kategori='PRODUCT & SPAREPART';
				}

				$header ='<table width="100%">';
				$header .='<tr><td>'.date('Y-m-d H-i-s').'</td><td align="right">Page {PAGENO} of {nbpg}</td></tr>';
				$header .='<tr><td align="center" colspan="2" style="font-size:25px"></td></tr>';				
				$header .='<tr><td align="center" colspan="2" style="font-size:15px"><b>LAPORAN JUAL-RETUR KOTA PER PARENTDIV</b></td></tr>';
				$header .='<tr><td align="center" colspan="2" style="font-size:13.9px">'.$from.' S/D '.$until.'</td></tr>';
				$header .='<tr><td align="center" colspan="2"><table border="0" width="250px"><tr><td style="border:thin solid #000000; padding:3px 3px; font-size:13px">'.$kategori.'</td></tr></table></td></tr>';				
				$header .='</table>';

				$content='';

				$tampung_wilayah='';
				$tampung_kota='';
				$tampung_partner_type='';

				$total_jual=0;
				$total_retur=0;
				$total_plg=0;


				$grand_total_partner_type_jual=0;
				$grand_total_partner_type_retur=0;
				$grand_total_partner_type_plg=0;

				$grand_total_wilayah_jual=0;
				$grand_total_wilayah_retur=0;
				$grand_total_wilayah_plg=0;


				$grand_total_jual=0;
				$grand_total_retur=0;
				$grand_total_plg=0;

				foreach ($list['data'] as $key => $l) {


					if(!empty($tampung_kota) && $tampung_kota!==rtrim($l['KOTA'])){
						$content .='<tr>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" align="center" valign="top"><b>'.$tampung_kota.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$total_jual=0;
						$total_retur=0;
						$total_plg=0;
					}


					if(!empty($tampung_wilayah) && $tampung_wilayah!==rtrim($l['WILAYAH'])){
						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$tampung_wilayah.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$grand_total_wilayah_jual=0;
						$grand_total_wilayah_retur=0;
						$grand_total_wilayah_plg=0;

					}

					if(!empty($tampung_partner_type) && $tampung_partner_type!==rtrim($l['Partner_Type'])){
						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$tampung_partner_type.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$grand_total_partner_type_jual=0;
						$grand_total_partner_type_retur=0;
						$grand_total_partner_type_plg=0;

					}


					if(!empty($tampung_wilayah) && !empty($tampung_partner_type) && ($tampung_wilayah!==rtrim($l['WILAYAH']) || $tampung_partner_type!==rtrim($l['Partner_Type']))){
						$content .='</table>';
					}



					if($tampung_wilayah!==rtrim($l['WILAYAH']) || $tampung_partner_type!==rtrim($l['Partner_Type'])){


						$content .='<table width="100%" border="0" style="margin-top:10px;">';
						$content .='<tr>';
						$content .='<td valign="top"><b>PARTNER TYPE : '.$l['Partner_Type'].'</b></td>';
						$content .='</tr>';
						$content .='<tr>';
						$content .='<td valign="top"><b>WILAYAH : '.$l['WILAYAH'].'</b></td>';
						$content .='</tr>';
						$content .='</table>';

					}

					
					if($tampung_wilayah!==rtrim($l['WILAYAH']) || $tampung_partner_type!==rtrim($l['Partner_Type'])){

						$tampung_wilayah=rtrim($l['WILAYAH']);
						$tampung_partner_type=rtrim($l['Partner_Type']);
						$tampung_kota=rtrim($l['KOTA']);

						$content .='<table width="100%" border="0" style="margin-top:5px;">';
						$content .='<tr>';
						$content .='<td valign="top"><b>KOTA : '.$l['KOTA'].'</b></td>';
						$content .='</tr>';
						$content .='</table>';

						$content .='<table width="100%" border="0" style="border-collapse:collapse;">';
						$content .='<tr>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top"><b>PARENTDIV</b></td>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top" align="right"><b>TOTAL JUAL</b></td>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top" align="right"><b>TOTAL RETUR</b></td>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top" align="right"><b>TOTAL</b></td>';
						$content .='</tr>';
					}

					$content .='<tr>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top">'.$l['PARENTDIV'].'</td>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top" align="right">'.number_format($l['TOTAL_JUAL'],0,",",".").'</td>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top" align="right">'.number_format($l['TOTAL_RETUR'],0,",",".").'</td>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top" align="right">'.number_format($l['TOTAL'],0,",",".").'</td>';
					$content .='</tr>';

					$total_jual=$total_jual+$l['TOTAL_JUAL'];
					$total_retur=$total_retur+$l['TOTAL_RETUR'];
					$total_plg=$total_plg+$l['TOTAL'];

					$grand_total_wilayah_jual=$grand_total_wilayah_jual+$l['TOTAL_JUAL'];
					$grand_total_wilayah_retur=$grand_total_wilayah_retur+$l['TOTAL_RETUR'];
					$grand_total_wilayah_plg=$grand_total_wilayah_plg+$l['TOTAL'];

					$grand_total_partner_type_jual=$grand_total_partner_type_jual+$l['TOTAL_JUAL'];
					$grand_total_partner_type_retur=$grand_total_partner_type_retur+$l['TOTAL_RETUR'];
					$grand_total_partner_type_plg=$grand_total_partner_type_plg+$l['TOTAL'];

					$grand_total_jual=$grand_total_jual+$l['TOTAL_JUAL'];
					$grand_total_retur=$grand_total_retur+$l['TOTAL_RETUR'];
					$grand_total_plg=$grand_total_plg+$l['TOTAL'];

				}


					if(!empty($tampung_kota)){
						$content .='<tr>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" align="center" valign="top"><b>'.$tampung_kota.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						

						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$tampung_wilayah.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						
						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$tampung_partner_type.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="padding:5px;" align="center" valign="top"><b>GRANDTOTAL</b></td>';
						$content .='<td style="padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_jual,0,",",".").'</b></td>';
						$content .='<td style="padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_retur,0,",",".").'</b></td>';
						$content .='<td style="padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_plg,0,",",".").'</b></td>';
						$content .='</tr>';
					}


					$content .='</table>';


					set_time_limit(0);
					$mpdf->SetHTMLHeader($header,'','1');
					$mpdf->WriteHTML($content);
					$mpdf->Output();

				}


			}else{
				redirect(site_url('Reportjualreturdealerparentdiv/?error=error'));
			}

		}



		public function excel_dealer_per_parent_divisi_kota($from='',$until='',$divisi='',$partner_type='',$wilayah='',$kategori='',$tipe_faktur='',$perkategori='',$printper='',$report=''){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

		if($_SESSION["can_read"]==true && !empty($from) && !empty($until) && !empty($printper) && !empty($report)){

			$data['from'] = $from;
			$data['until'] = $until;
			$data['divisi'] = $divisi;
			$data['partner_type'] = $partner_type;
			$data['wilayah'] = $wilayah;
			$data['kategori'] = $kategori;
			$data['tipe_faktur'] = $tipe_faktur;
			$data['perkategori'] = $perkategori;
			$data['printper'] = $printper;
			$data['report'] = $report;
			$list = $this->ReportjualreturdealerparentdivModel->GetData($data);

			$list = json_decode($list,true);

			if($list['hasil']=='success'){

				$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet(0);

				ini_set('max_execution_time', '1500');
				ini_set("pcre.backtrack_limit", "10000000");
				set_time_limit(0);

				if($kategori=='ALL'){
					$kategori='PRODUCT & SPAREPART';
				}


				// $sheet->setTitle('LAPORAN JUAL-RETUR KOTA PER PARENTDIV');
				$sheet->setCellValue('A1', 'Printed : '.date('Y-m-d H-i-s'));
				$sheet->setCellValue('A2', 'LAPORAN JUAL-RETUR KOTA PER PARENTDIV');
				$sheet->setCellValue('A3', $from.' S/D '.$until);
				$sheet->setCellValue('A4', $kategori);


				$sheet->mergeCells('A1:D1');
				$sheet->mergeCells('A2:D2');
				$sheet->mergeCells('A3:D3');
				$sheet->mergeCells('A4:D4');

				$sheet->getStyle('A1')->getFont()->setSize(10);
				$sheet->getStyle('A2')->getFont()->setSize(20);
				$sheet->getStyle('A3')->getFont()->setSize(15);
				$sheet->getStyle('A4')->getFont()->setSize(12);

				$sheet->getStyle('A1:D1')->getAlignment()->setHorizontal('right');
				$sheet->getStyle('A2:D2')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A3:D3')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A4:D4')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A2:D2')->getFont()->setBold(true);

				$tampung_wilayah='';
				$tampung_kota='';
				$tampung_partner_type='';

				$total_jual=0;
				$total_retur=0;
				$total_plg=0;

				$grand_total_partner_type_jual=0;
				$grand_total_partner_type_retur=0;
				$grand_total_partner_type_plg=0;

				$grand_total_wilayah_jual=0;
				$grand_total_wilayah_retur=0;
				$grand_total_wilayah_plg=0;


				$grand_total_jual=0;
				$grand_total_retur=0;
				$grand_total_plg=0;

				$currrow=5;
				$currcol=1;

				foreach ($list['data'] as $key => $l) {


					if(!empty($tampung_kota) && $tampung_kota!==rtrim($l['KOTA'])){

						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_kota);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_jual);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_retur);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
						$currrow++;


						$total_jual=0;
						$total_retur=0;
						$total_plg=0;
					}


					if(!empty($tampung_wilayah) && $tampung_wilayah!==rtrim($l['WILAYAH'])){
						$currrow++;
						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_wilayah);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_jual);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_retur);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
						$currrow++;

						$grand_total_wilayah_jual=0;
						$grand_total_wilayah_retur=0;
						$grand_total_wilayah_plg=0;

					}

					if(!empty($tampung_partner_type) && $tampung_partner_type!==rtrim($l['Partner_Type'])){
						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_partner_type);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_jual);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_retur);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
						$currrow++;

						$grand_total_partner_type_jual=0;
						$grand_total_partner_type_retur=0;
						$grand_total_partner_type_plg=0;

					}


					if($tampung_wilayah!==rtrim($l['WILAYAH']) || $tampung_partner_type!==rtrim($l['Partner_Type'])){

						$currrow++;
						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARTNER TYPE : '.$l['Partner_Type']);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'WILAYAH : '.$l['WILAYAH']);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;

					}

					
					if($tampung_wilayah!==rtrim($l['WILAYAH']) || $tampung_partner_type!==rtrim($l['Partner_Type'])){

						$tampung_wilayah=rtrim($l['WILAYAH']);
						$tampung_partner_type=rtrim($l['Partner_Type']);
						$tampung_kota=rtrim($l['KOTA']);


						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KOTA : '.$l['KOTA']);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;


						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARENTDIV');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL JUAL');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL RETUR');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$currrow++;
					}


					$currcol=1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['PARENTDIV']);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['TOTAL_JUAL']);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['TOTAL_RETUR']);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['TOTAL']);
					$currrow++;

					$total_jual=$total_jual+$l['TOTAL_JUAL'];
					$total_retur=$total_retur+$l['TOTAL_RETUR'];
					$total_plg=$total_plg+$l['TOTAL'];

					$grand_total_wilayah_jual=$grand_total_wilayah_jual+$l['TOTAL_JUAL'];
					$grand_total_wilayah_retur=$grand_total_wilayah_retur+$l['TOTAL_RETUR'];
					$grand_total_wilayah_plg=$grand_total_wilayah_plg+$l['TOTAL'];

					$grand_total_partner_type_jual=$grand_total_partner_type_jual+$l['TOTAL_JUAL'];
					$grand_total_partner_type_retur=$grand_total_partner_type_retur+$l['TOTAL_RETUR'];
					$grand_total_partner_type_plg=$grand_total_partner_type_plg+$l['TOTAL'];

					$grand_total_jual=$grand_total_jual+$l['TOTAL_JUAL'];
					$grand_total_retur=$grand_total_retur+$l['TOTAL_RETUR'];
					$grand_total_plg=$grand_total_plg+$l['TOTAL'];

				}


					if(!empty($tampung_kota)){


						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_kota);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_jual);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_retur);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$currrow=$currrow+2;

						
						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_wilayah);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_jual);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_retur);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_wilayah_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;

						
						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_partner_type);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_jual);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_retur);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_partner_type_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow=$currrow+2;

						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_jual);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_retur);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_plg);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;

					}


					for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
					    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
					}

					$filename='LAPORAN JUAL-RETUR KOTA PER PARENTDIV ['.date('Y-m-d H-i-s').']'; //save our workbook as this file name
					$writer = new Xlsx($spreadsheet);
					header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
					header('Cache-Control: max-age=0');
					ob_end_clean();
					$writer->save('php://output');
					exit();

				}


			}else{
				redirect(site_url('Reportjualreturdealerparentdiv/?error=error'));
			}

		}




	public function pdf_parentdiv_per_kota($from='',$until='',$divisi='',$partner_type='',$wilayah='',$kategori='',$tipe_faktur='',$perkategori='',$printper='',$report=''){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

		if($_SESSION["can_read"]==true && !empty($from) && !empty($until) && !empty($printper) && !empty($report)){

			$data['from'] = $from;
			$data['until'] = $until;
			$data['divisi'] = $divisi;
			$data['partner_type'] = $partner_type;
			$data['wilayah'] = $wilayah;
			$data['kategori'] = $kategori;
			$data['tipe_faktur'] = $tipe_faktur;
			$data['perkategori'] = $perkategori;
			$data['printper'] = $printper;
			$data['report'] = $report;
			$list = $this->ReportjualreturdealerparentdivModel->GetData($data);

			$list = json_decode($list,true);

			if($list['hasil']=='success'){

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
					'margin_top' => 39,
					'margin_bottom' => 10,
					'margin_header' => 10,
					'margin_footer' => 5,
					'orientation' => 'P'
				));

				if($kategori=='ALL'){
					$kategori='PRODUCT & SPAREPART';
				}

				$header ='<table width="100%">';
				$header .='<tr><td>'.date('Y-m-d H-i-s').'</td><td align="right">Page {PAGENO} of {nbpg}</td></tr>';
				$header .='<tr><td align="center" colspan="2" style="font-size:25px"></td></tr>';				
				$header .='<tr><td align="center" colspan="2" style="font-size:15px"><b>LAPORAN JUAL-RETUR PARENTDIV PER KOTA</b></td></tr>';
				$header .='<tr><td align="center" colspan="2" style="font-size:13.9px">'.$from.' S/D '.$until.'</td></tr>';
				$header .='<tr><td align="center" colspan="2"><table border="0" width="250px"><tr><td style="border:thin solid #000000; padding:3px 3px; font-size:13px">'.$kategori.'</td></tr></table></td></tr>';				
				$header .='</table>';

				$content='';

				$tampung_wilayah='';
				$tampung_kota='';
				$tampung_partner_type='';
				$tampung_parentdiv='';

				$total_jual=0;
				$total_retur=0;
				$total_plg=0;


				$grand_total_partner_type_jual=0;
				$grand_total_partner_type_retur=0;
				$grand_total_partner_type_plg=0;

				$grand_total_wilayah_jual=0;
				$grand_total_wilayah_retur=0;
				$grand_total_wilayah_plg=0;


				$grand_total_jual=0;
				$grand_total_retur=0;
				$grand_total_plg=0;

				foreach ($list['data'] as $key => $l) {


					if(!empty($tampung_parentdiv) && $tampung_parentdiv!==rtrim($l['PARENTDIV'])){
						$content .='<tr>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" align="center" valign="top"><b>'.$tampung_parentdiv.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$total_jual=0;
						$total_retur=0;
						$total_plg=0;
					}


					if(!empty($tampung_wilayah) && $tampung_wilayah!==rtrim($l['WILAYAH'])){
						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$tampung_wilayah.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$grand_total_wilayah_jual=0;
						$grand_total_wilayah_retur=0;
						$grand_total_wilayah_plg=0;

					}

					if(!empty($tampung_partner_type) && $tampung_partner_type!==rtrim($l['Partner_Type'])){
						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$tampung_partner_type.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$grand_total_partner_type_jual=0;
						$grand_total_partner_type_retur=0;
						$grand_total_partner_type_plg=0;

					}


					if(!empty($tampung_wilayah) && !empty($tampung_partner_type) && (($tampung_wilayah!==rtrim($l['WILAYAH']) || $tampung_partner_type!==rtrim($l['Partner_Type'])) || ($tampung_kota!==rtrim($l['KOTA']) || $tampung_parentdiv!==rtrim($l['PARENTDIV'])))){
						$content .='</table>';
					}



					if($tampung_wilayah!==rtrim($l['WILAYAH']) || $tampung_partner_type!==rtrim($l['Partner_Type'])){


						$content .='<table width="100%" border="0" style="margin-top:10px;">';
						$content .='<tr>';
						$content .='<td valign="top"><b>PARTNER TYPE : '.rtrim($l['Partner_Type']).'</b></td>';
						$content .='</tr>';
						$content .='<tr>';
						$content .='<td valign="top"><b>WILAYAH : '.rtrim($l['WILAYAH']).'</b></td>';
						$content .='</tr>';
						$content .='</table>';

					}

					if(($tampung_wilayah!==rtrim($l['WILAYAH']) || $tampung_partner_type!==rtrim($l['Partner_Type'])) || ($tampung_kota!==rtrim($l['KOTA']) || $tampung_parentdiv!==rtrim($l['PARENTDIV']))){

						$tampung_wilayah=rtrim($l['WILAYAH']);
						$tampung_partner_type=rtrim($l['Partner_Type']);
						$tampung_kota=rtrim($l['KOTA']);
						$tampung_parentdiv=rtrim($l['PARENTDIV']);


						$content .='<table width="100%" border="0" style="margin-top:5px;">';
						$content .='<tr>';
						$content .='<td valign="top"><b>PARENTDIV : '.rtrim($l['PARENTDIV']).'</b></td>';
						$content .='</tr>';
						$content .='</table>';

						$content .='<table width="100%" border="0" style="border-collapse:collapse;">';
						$content .='<tr>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top"><b>KOTA</b></td>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top" align="right"><b>TOTAL JUAL</b></td>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top" align="right"><b>TOTAL RETUR</b></td>';
						$content .='<td style="border-bottom:thin solid #000000" width="25%" valign="top" align="right"><b>TOTAL</b></td>';
						$content .='</tr>';
					}

					$content .='<tr>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top">'.$tampung_kota.'</td>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top" align="right">'.number_format($l['TOTAL_JUAL'],0,",",".").'</td>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top" align="right">'.number_format($l['TOTAL_RETUR'],0,",",".").'</td>';
					$content .='<td style="padding-top:5px; padding-bottom:5px;" valign="top" align="right">'.number_format($l['TOTAL'],0,",",".").'</td>';
					$content .='</tr>';

					$total_jual=$total_jual+$l['TOTAL_JUAL'];
					$total_retur=$total_retur+$l['TOTAL_RETUR'];
					$total_plg=$total_plg+$l['TOTAL'];

					$grand_total_wilayah_jual=$grand_total_wilayah_jual+$l['TOTAL_JUAL'];
					$grand_total_wilayah_retur=$grand_total_wilayah_retur+$l['TOTAL_RETUR'];
					$grand_total_wilayah_plg=$grand_total_wilayah_plg+$l['TOTAL'];

					$grand_total_partner_type_jual=$grand_total_partner_type_jual+$l['TOTAL_JUAL'];
					$grand_total_partner_type_retur=$grand_total_partner_type_retur+$l['TOTAL_RETUR'];
					$grand_total_partner_type_plg=$grand_total_partner_type_plg+$l['TOTAL'];

					$grand_total_jual=$grand_total_jual+$l['TOTAL_JUAL'];
					$grand_total_retur=$grand_total_retur+$l['TOTAL_RETUR'];
					$grand_total_plg=$grand_total_plg+$l['TOTAL'];

				}


					if(!empty($tampung_parentdiv)){
						$content .='<tr>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" align="center" valign="top"><b>'.$tampung_parentdiv.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; padding-top:10px;" valign="top" align="right"><b>'.number_format($total_plg,0,",",".").'</b></td>';
						$content .='</tr>';

					
						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$tampung_wilayah.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_wilayah_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						
						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="border-left:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" align="center" valign="top"><b>'.$tampung_partner_type.'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_jual,0,",",".").'</b></td>';
						$content .='<td style="border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_retur,0,",",".").'</b></td>';
						$content .='<td style="border-right:thin solid #000000; border-top:thin solid #000000; border-bottom:thin solid #000000; padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_partner_type_plg,0,",",".").'</b></td>';
						$content .='</tr>';

						$content .='<tr><td colspan="4" style="padding-top:10px"></td></tr>';
						$content .='<tr>';
						$content .='<td style="padding:5px;" align="center" valign="top"><b>GRANDTOTAL</b></td>';
						$content .='<td style="padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_jual,0,",",".").'</b></td>';
						$content .='<td style="padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_retur,0,",",".").'</b></td>';
						$content .='<td style="padding:5px;" valign="top" align="right"><b>'.number_format($grand_total_plg,0,",",".").'</b></td>';
						$content .='</tr>';
					}


					$content .='</table>';

					set_time_limit(0);
					$mpdf->SetHTMLHeader($header,'','1');
					$mpdf->WriteHTML($content);
					$mpdf->Output();

				}


			}else{
				redirect(site_url('Reportjualreturdealerparentdiv/?error=error'));
			}

		}



		public function excel_parentdiv_per_kota($from='',$until='',$divisi='',$partner_type='',$wilayah='',$kategori='',$tipe_faktur='',$perkategori='',$printper='',$report=''){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

		if($_SESSION["can_read"]==true && !empty($from) && !empty($until) && !empty($printper) && !empty($report)){

			$data['from'] = $from;
			$data['until'] = $until;
			$data['divisi'] = $divisi;
			$data['partner_type'] = $partner_type;
			$data['wilayah'] = $wilayah;
			$data['kategori'] = $kategori;
			$data['tipe_faktur'] = $tipe_faktur;
			$data['perkategori'] = $perkategori;
			$data['printper'] = $printper;
			$data['report'] = $report;
			$list = $this->ReportjualreturdealerparentdivModel->GetData($data);

			$list = json_decode($list,true);

			if($list['hasil']=='success'){

				$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet(0);

				ini_set('max_execution_time', '1500');
				ini_set("pcre.backtrack_limit", "10000000");
				set_time_limit(0);

				if($kategori=='ALL'){
					$kategori='PRODUCT & SPAREPART';
				}


				// $sheet->setTitle('LAPORAN JUAL-RETUR PARENTDIV PER KOTA');
				$sheet->setCellValue('A1', 'Printed : '.date('Y-m-d H-i-s'));
				$sheet->setCellValue('A2', 'LAPORAN JUAL-RETUR PARENTDIV PER KOTA');
				$sheet->setCellValue('A3', $from.' S/D '.$until);
				$sheet->setCellValue('A4', $kategori);


				$sheet->mergeCells('A1:D1');
				$sheet->mergeCells('A2:D2');
				$sheet->mergeCells('A3:D3');
				$sheet->mergeCells('A4:D4');

				$sheet->getStyle('A1')->getFont()->setSize(10);
				$sheet->getStyle('A2')->getFont()->setSize(20);
				$sheet->getStyle('A3')->getFont()->setSize(15);
				$sheet->getStyle('A4')->getFont()->setSize(12);

				$sheet->getStyle('A1:D1')->getAlignment()->setHorizontal('right');
				$sheet->getStyle('A2:D2')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A3:D3')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A4:D4')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A2:D2')->getFont()->setBold(true);

				$tampung_wilayah='';
				$tampung_kota='';
				$tampung_partner_type='';
				$tampung_parentdiv='';

				$total_jual=0;
				$total_retur=0;
				$total_plg=0;


				$grand_total_partner_type_jual=0;
				$grand_total_partner_type_retur=0;
				$grand_total_partner_type_plg=0;

				$grand_total_wilayah_jual=0;
				$grand_total_wilayah_retur=0;
				$grand_total_wilayah_plg=0;


				$grand_total_jual=0;
				$grand_total_retur=0;
				$grand_total_plg=0;


				$currrow=5;
				$currcol=1;


				foreach ($list['data'] as $key => $l) {


					if(!empty($tampung_parentdiv) && $tampung_parentdiv!==rtrim($l['PARENTDIV'])){

						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_parentdiv);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_plg,0,",","."));
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
						$currrow++;


						$total_jual=0;
						$total_retur=0;
						$total_plg=0;
					}


					if(!empty($tampung_wilayah) && $tampung_wilayah!==rtrim($l['WILAYAH'])){

						$currrow++;
						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_wilayah);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($grand_total_wilayah_jual,0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($grand_total_wilayah_retur,0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($grand_total_wilayah_plg,0,",","."));
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
						$currrow++;

						$grand_total_wilayah_jual=0;
						$grand_total_wilayah_retur=0;
						$grand_total_wilayah_plg=0;

					}

					if(!empty($tampung_partner_type) && $tampung_partner_type!==rtrim($l['Partner_Type'])){

						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_partner_type);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($grand_total_partner_type_jual,0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($grand_total_partner_type_retur,0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($grand_total_partner_type_plg,0,",","."));
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
						$currrow++;

						$grand_total_partner_type_jual=0;
						$grand_total_partner_type_retur=0;
						$grand_total_partner_type_plg=0;

					}


					if($tampung_wilayah!==rtrim($l['WILAYAH']) || $tampung_partner_type!==rtrim($l['Partner_Type'])){

						$currrow++;
						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARTNER TYPE : '.$l['Partner_Type']);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'WILAYAH : '.$l['WILAYAH']);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;					

					}

					if(($tampung_wilayah!==rtrim($l['WILAYAH']) || $tampung_partner_type!==rtrim($l['Partner_Type'])) || ($tampung_kota!==rtrim($l['KOTA']) || $tampung_parentdiv!==rtrim($l['PARENTDIV']))){

						$tampung_wilayah=rtrim($l['WILAYAH']);
						$tampung_partner_type=rtrim($l['Partner_Type']);
						$tampung_kota=rtrim($l['KOTA']);
						$tampung_parentdiv=rtrim($l['PARENTDIV']);

						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PARENTDIV : '.$l['PARENTDIV']);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;

						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KOTA');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL JUAL');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL RETUR');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$currrow++;
					}


					$currcol=1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_kota);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['TOTAL_JUAL'],0,",","."));
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['TOTAL_RETUR'],0,",","."));
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['TOTAL'],0,",","."));
					$currrow++;


					$total_jual=$total_jual+$l['TOTAL_JUAL'];
					$total_retur=$total_retur+$l['TOTAL_RETUR'];
					$total_plg=$total_plg+$l['TOTAL'];

					$grand_total_wilayah_jual=$grand_total_wilayah_jual+$l['TOTAL_JUAL'];
					$grand_total_wilayah_retur=$grand_total_wilayah_retur+$l['TOTAL_RETUR'];
					$grand_total_wilayah_plg=$grand_total_wilayah_plg+$l['TOTAL'];

					$grand_total_partner_type_jual=$grand_total_partner_type_jual+$l['TOTAL_JUAL'];
					$grand_total_partner_type_retur=$grand_total_partner_type_retur+$l['TOTAL_RETUR'];
					$grand_total_partner_type_plg=$grand_total_partner_type_plg+$l['TOTAL'];

					$grand_total_jual=$grand_total_jual+$l['TOTAL_JUAL'];
					$grand_total_retur=$grand_total_retur+$l['TOTAL_RETUR'];
					$grand_total_plg=$grand_total_plg+$l['TOTAL'];

				}


					if(!empty($tampung_parentdiv)){
						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_kota);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_plg,0,",","."));
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow)->getAlignment()->setHorizontal('center');
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$currrow=$currrow+2;

						
						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_wilayah);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($grand_total_wilayah_jual,0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($grand_total_wilayah_retur,0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($grand_total_wilayah_plg,0,",","."));
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;

						
						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tampung_partner_type);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($grand_total_partner_type_jual,0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($grand_total_partner_type_retur,0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($grand_total_partner_type_plg,0,",","."));
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow=$currrow+2;

						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($grand_total_jual,0,",","."));
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($grand_total_retur,0,",","."));
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($grand_total_plg,0,",","."));
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':D'.$currrow)->getFont()->setBold(true);
						$currrow++;
					}


					for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
					    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
					}

					$filename='LAPORAN JUAL-RETUR PARENTDIV PER KOTA ['.date('Y-m-d H-i-s').']'; //save our workbook as this file name
					$writer = new Xlsx($spreadsheet);
					header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
					header('Cache-Control: max-age=0');
					ob_end_clean();
					$writer->save('php://output');
					exit();

				}


			}else{
				redirect(site_url('Reportjualreturdealerparentdiv/?error=error'));
			}

		}
	}

?>