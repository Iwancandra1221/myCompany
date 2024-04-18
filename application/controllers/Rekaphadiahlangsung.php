<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
	require 'vendor/setasign/fpdi/src/autoload.php';

	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class Rekaphadiahlangsung extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('RekaphadiahlangsungModule');
    }

    public function index()
    {

        $this->ModuleModel->CheckAccess($this->uri->segment(1), '');
        if ($_SESSION['can_read'] == true) {
            $data['partner_type'] = $this->RekaphadiahlangsungModule->partner_type();
            $data['wilayah'] = $this->RekaphadiahlangsungModule->wilayah();
            $this->RenderView('RekaphadiahlangsungView', $data);
        } else {
            redirect('dashboard');
        }
    }

    public function pdf($awal='',$akhir='',$partner_type='',$wilayah=''){
    	$this->ModuleModel->CheckAccess($this->uri->segment(1), '');
    	if ($_SESSION['can_read'] == true) {

    		$data['awal'] = $awal;
    		$data['akhir'] = $akhir;
    		$data['partner_type'] = $partner_type;
    		$data['wilayah'] = $wilayah;

    		ini_set('memory_limit', '128M');
    		set_time_limit(0);

    		$list = $this->RekaphadiahlangsungModule->list($data);

		    $LogDate = date("Y-m-d H:i:s");
		    $this->Logs_insert($LogDate, 'LAPORAN HADIAH LANGSUNG');

		    ini_set("pcre.backtrack_limit", "50000000");
		    // set_time_limit(0);



		    require_once __DIR__ . '/vendor/autoload.php'; // Use forward slash instead of backslash
		    $mpdf = new \Mpdf\Mpdf(array(
		        'mode'              => '',
		        'format'            => 'A4',
		        'default_font_size' => 10,
		        'default_font'      => 'arial',
		        'margin_left'       => 10,
		        'margin_right'      => 10,
		        'margin_top'        => 35,
		        'margin_bottom'     => 35,
		        'margin_header'     => 10,
		        'margin_footer'     => 5,
		        'orientation'       => 'L'
		    ));

			$header = '<table width="100%">';
			$header .= '<tr>';
			$header .= '<td colspan="2">' . date('d-m-Y H-i-s') . '</td>';
			$header .= '</tr>';
			$header .= '<tr>';
			$header .= '<td align="center" colspan="2"><h2>LAPORAN HADIAH LANGSUNG</h2></td>';
			$header .= '</tr>';
			$header .= '<tr>';
			$header .= '<td align="center" colspan="2">';
			$header .= 'Tanggal : ' . date_format(date_create($awal), 'd M Y') . ' s/d ' . date_format(date_create($akhir), 'd M Y');
			$header .= '</td>';
			$header .= '</tr>';
			$header .= '</table>';

			$no=1;
			$jns_trx = '';
			$nm_trx = '';

			$total_faktur_wilayah=0;
			$total_wilayah = 0;

			$Kd_Lokasi = '';
			$content = '';

			$hadiah = array();
			$total_hadiah = array();

			if(!empty($list['data'])){
	    		foreach ($list['data'] as $key => $l) {
	    			
	    			if($jns_trx!=$l['Jns_Trx'] || $l['Kd_Lokasi']!=$Kd_Lokasi){

	    				if($Kd_Lokasi!=''){
	    					$content .= '<tr><td colspan="5"></td><td align="right">Total</td><td align="right">'.number_format($total_faktur_wilayah, 0, ',', '.').'</td><td></td><td align="right">'.number_format($total_wilayah, 0, ',', '.').'</td><td colspan="3"></td></tr></tbody></table><br>';

							$jnstrx = strtolower(str_replace(' ','',$jns_trx));

			    			$hadiah[$jnstrx]['total_faktur'] = $total_faktur_wilayah;
			    			
			    			$hadiah[$jnstrx]['nama_hadiah'] = $nm_trx;

			    			$hadiah[$jnstrx]['total']=$total_wilayah;

			    			if(empty($total_hadiah[$jnstrx]['total_faktur'])){
			    				$total_hadiah[$jnstrx]['total_faktur'] = 0;
			    				$total_hadiah[$jnstrx]['total'] = 0;
			    			}

			    			$total_hadiah[$jnstrx]['total_faktur'] = $total_hadiah[$jnstrx]['total_faktur']+$total_faktur_wilayah;
			    			$total_hadiah[$jnstrx]['nama_hadiah'] = $nm_trx;
			    			$total_hadiah[$jnstrx]['total'] = $total_hadiah[$jnstrx]['total_faktur']+$total_wilayah;

	    					$total_faktur_wilayah=0;
							$total_wilayah = 0;

	    					if($l['Kd_Lokasi']!=$Kd_Lokasi){
	    						$content .='<table width="100%"><tr><td></td><td width="200px"></td><td width="150px" align="center"><b>Total Faktur<br>Hadiah (Rp)</b></td><td width="150px" align="center"><b>Total NK<br>Hadiah (Rp)</b></td><td width="100px"></td></tr>';

	    						$total_faktur = 0;
	    						$total = 0;

	    						foreach ($hadiah as $key => $h) {
	    							$total_faktur = $total_faktur+$h['total_faktur'];
	    							$total = $total+$h['total'];
	    							$content .= '<tr><td></td><td>'.$h['nama_hadiah'].'</td><td align="right"><b>'.number_format($h['total_faktur'], 0, ',', '.').'</b></td><td align="right"><b>'.number_format($h['total'], 0, ',', '.').'</b></td><td></td></tr>';
	    						}
	    						
	    						$content .= '<tr><td></td><td width="250px" style="border-top:thin solid"><b>Total</b></td><td width="100px" align="right" style="border-top:thin solid"><b>'.number_format($total_faktur, 0, ',', '.').'</b></td><td width="100px" align="right" style="border-top:thin solid"><b>'.number_format($total, 0, ',', '.').'</b></td></tr>';
	    						
	    						$content .='</table><br>';

	    						$hadiah = array();	
	    					}
	    				}

	    				if(!empty($l['Jns_Trx'])){
	    					$trx = $l['Nm_Trx'];
	    				}else{
	    					$trx = '-';
	    				}

	    				$no=1;

	    				if($l['Kd_Lokasi']!=$Kd_Lokasi){
	    					$content .= '<table><tr><td><h3>'.$l['wilayah'].'</h3></td></tr></table>';
	    					$Kd_Lokasi = $l['Kd_Lokasi'];
	    				}

						$content .= '<table><tr><td><h3>HADIAH : '.$trx.'</h3></td></tr></table>';
						$content .= '<table style="width:100%; border-collapse: collapse; border: 1px solid;"><thead>';
						$content .= '<tr style="border: 1px solid">';
							$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px;" width="30px"><b>No</b></td>';
							$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px; width:10%;"><b>Faktur Campaign</b></td>';
							$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px; width:10%;"><b>Faktur Hadiah</b></td>';
							$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px; width:10%;"><b>Kode Pelanggan</b></td>';
							$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px; width:10%;"><b>Nama Pelanggan</b></td>';
							$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px; width:10%;"><b>Divisi</b></td>';
							$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px; width:10%;"><b>Total Faktur<br>Hadiah (Rp)</b></td>';
							$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px; width:10%;"><b>No Bukti</b></td>';
							$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px; width:10%;"><b>Total NK<br>Hadiah (Rp)</b></td>';
							$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px; width:10%;"><b>Tgl Jth Tempo</b></td>';
							$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px; width:10%;"><b>No Kwitansi</b></td>';
							$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px; width:10%;"><b>No Distribusi</b></td>';
						$content .= '</tr></thead><tbody>';

	    				$jns_trx = $l['Jns_Trx'];
	    				$nm_trx = $l['Nm_Trx'];
	    			}

					$content .= '<tr style="border: 1px solid;">';
						$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px">'.$no.'.</td>';
						$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px">'.$l['faktur_campaign'].'</td>';
						$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px">'.$l['no_hadiah'].'</td>';
						$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px">'.$l['Kd_Plg'].'</td>';
						$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px">'.$l['nm_plg'].'</td>';
						$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px">'.$l['Divisi'].'</td>';
						$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px" align="right">'.number_format($l['Total_Faktur'], 0, ',', '.').'</td>';
						$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px">'.$l['No_bukti'].'</td>';
						$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px" align="right">'.number_format($l['Total'], 0, ',', '.').'</td>';
						$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px">'.date_format(date_create($l['Tgl_JatuhTempo']),'Y-m-d').'</td>';
						$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px">'.$l['No_Kwitansi'].'</td>';
						$content .= '<td valign="top" align="center" style="border: 1px solid; padding:5px">'.$l['No_Distribusi'].'</td>';
					$content .= '</tr>';

					$total_faktur_wilayah = $total_faktur_wilayah+$l['Total_Faktur'];
					$total_wilayah = $total_wilayah+$l['Total'];

					$no++;
	    		}

				$content .= '<tr><td colspan="5"></td><td align="right">Total</td><td align="right">'.number_format($total_faktur_wilayah, 0, ',', '.').'</td><td></td><td align="right">'.number_format($total_wilayah, 0, ',', '.').'</td><td colspan="3"></td></tr></tbody></table><br>';

				$jnstrx = strtolower(str_replace(' ','',$jns_trx));

			    $hadiah[$jnstrx]['total_faktur'] = $total_faktur_wilayah;
			    
			    $hadiah[$jnstrx]['nama_hadiah'] = $nm_trx;

			    $hadiah[$jnstrx]['total']=$total_wilayah;

				if(empty($total_hadiah[$jnstrx]['total_faktur'])){
			    	$total_hadiah[$jnstrx]['total_faktur'] = 0;
			    	$total_hadiah[$jnstrx]['total'] = 0;
			    }

				$total_hadiah[$jnstrx]['total_faktur'] = $total_hadiah[$jnstrx]['total_faktur']+$total_faktur_wilayah;
			    $total_hadiah[$jnstrx]['nama_hadiah'] = $nm_trx;
			    $total_hadiah[$jnstrx]['total'] = $total_hadiah[$jnstrx]['total_faktur']+$total_wilayah;

	    		$total_faktur_wilayah=0;
				$total_wilayah = 0;

	    	
	    		$content .='<table width="100%"><tr><td></td><td width="200px"></td><td width="150px" align="center"><b>Total Faktur<br>Hadiah (Rp)</b></td><td width="150px" align="center"><b>Total NK<br>Hadiah (Rp)</b></td><td width="100px"></td></tr>';

	    		$total_faktur = 0;
	    		$total = 0;

	    		foreach ($hadiah as $key => $h) {
	    			$total_faktur = $total_faktur+$h['total_faktur'];
	    			$total = $total+$h['total'];
	    			$content .= '<tr><td></td><td>'.$h['nama_hadiah'].'</td><td align="right"><b>'.number_format($h['total_faktur'], 0, ',', '.').'</b></td><td align="right"><b>'.number_format($h['total'], 0, ',', '.').'</b></td><td></td></tr>';
	    		}
	    						
	    		$content .= '<tr><td></td><td width="250px" style="border-top:thin solid"><b>Total</b></td><td width="100px" align="right" style="border-top:thin solid"><b>'.number_format($total_faktur, 0, ',', '.').'</b></td><td width="100px" align="right" style="border-top:thin solid"><b>'.number_format($total, 0, ',', '.').'</b></td></tr>';
	    		
	    		$content .='</table><br>';


			$content .='<table width="600px" style="border-collapse: collapse; border: 1px solid;"><tr><td colspan="3" align="center"><b>Total Semua Hadiah</b></td></tr><tr><td width="200px" style="border-top:thin solid"></td><td width="150px" align="center" style="border-top:thin solid"><b>Total Faktur<br>Hadiah (Rp)</b></td><td width="150px" align="center" style="border-top:thin solid"><b>Total NK<br>Hadiah (Rp)</b></td></tr>';

	    		$total_faktur = 0;
	    		$total = 0;

	    		foreach ($total_hadiah as $key => $h) {
	    			$total_faktur = $total_faktur+$h['total_faktur'];
	    			$total = $total+$h['total'];
	    			$content .= '<tr><td style="border-top:thin solid">'.$h['nama_hadiah'].'</td><td align="right" style="border-top:thin solid">'.number_format($h['total_faktur'], 0, ',', '.').'</td><td align="right" style="border-top:thin solid">'.number_format($h['total'], 0, ',', '.').'</td></tr>';
	    		}
	    						
	    		$content .= '<tr><td width="250px" style="border-top:thin solid"><b>Total</b></td><td width="100px" align="right" style="border-top:thin solid"><b>'.number_format($total_faktur, 0, ',', '.').'</b></td><td width="100px" align="right" style="border-top:thin solid"><b>'.number_format($total, 0, ',', '.').'</b></td></tr>';
	    		
	    		$content .='</table><br>';



	    		// $content .= '</tbody></table>';
	    	}else{
	    		$content .= 'Data tidak ditemukan!!!';
	    	}

	    	// echo $content;die();


		    $this->Logs_Update($LogDate, 'SUCCESS', 'LAPORAN HADIAH LANGSUNG');

		    $mpdf->SetHTMLHeader($header, '', '1');
		    $mpdf->WriteHTML($content);
		    $mpdf->Output();

    	} else {
            redirect('dashboard');
        }
    }

    public function excel($awal='',$akhir='',$partner_type='',$wilayah=''){
    	$this->ModuleModel->CheckAccess($this->uri->segment(1), '');
    	if ($_SESSION['can_read'] == true) {

    		$data['awal'] = $awal;
    		$data['akhir'] = $akhir;
    		$data['partner_type'] = $partner_type;
    		$data['wilayah'] = $wilayah;

    		$list = $this->RekaphadiahlangsungModule->list($data);
    		
		    $LogDate = date("Y-m-d H:i:s");
		    $this->Logs_insert($LogDate, 'LAPORAN HADIAH LANGSUNG');

			ini_set("pcre.backtrack_limit", "50000000");
		    set_time_limit(0);

		    $spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);

			$sheet->setTitle('LAPORAN HADIAH LANGSUNG');

			$sheet->setCellValue('B1', date('d-m-Y H-i-s'));
			$sheet->mergeCells('A1:L1');

			$sheet->setCellValue('A2', 'LAPORAN HADIAH LANGSUNG');
			$sheet->getStyle('A2')->getFont()->setSize(20);
			$sheet->getStyle("A2:L2")->getFont()->setBold(true);
			$sheet->mergeCells('A2:L2');
			$sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

			$sheet->setCellValue('A3', 'Periode '.date_format(date_create($awal), 'd M Y') . ' s/d ' . date_format(date_create($akhir), 'd M Y'));
			$sheet->mergeCells('A3:L3');
			$sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);


			$no=1;
			$jns_trx = '';
			$nm_trx = '';

			$total_faktur_wilayah=0;
			$total_wilayah = 0;

			$Kd_Lokasi = '';
			$content = '';

			$hadiah = array();
			$total_hadiah = array();

			$currrow = 5;
			$currcol = 1;

			if(!empty($list['data'])){
	    		foreach ($list['data'] as $key => $l) {
	    			
	    			if($jns_trx!=$l['Jns_Trx'] || $l['Kd_Lokasi']!=$Kd_Lokasi){

	    				if($Kd_Lokasi!=''){
	    					$currcol = 6;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
							$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

							$currcol++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_faktur_wilayah, 0, '.', ','));
							$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
							$currcol=$currcol+2;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_wilayah, 0, '.', ','));
							$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
							$currrow++;

							$jnstrx = strtolower(str_replace(' ','',$jns_trx));

			    			$hadiah[$jnstrx]['total_faktur'] = $total_faktur_wilayah;
			    			
			    			$hadiah[$jnstrx]['nama_hadiah'] = $nm_trx;

			    			$hadiah[$jnstrx]['total']=$total_wilayah;

			    			if(empty($total_hadiah[$jnstrx]['total_faktur'])){
			    				$total_hadiah[$jnstrx]['total_faktur'] = 0;
			    				$total_hadiah[$jnstrx]['total'] = 0;
			    			}

			    			$total_hadiah[$jnstrx]['total_faktur'] = $total_hadiah[$jnstrx]['total_faktur']+$total_faktur_wilayah;
			    			$total_hadiah[$jnstrx]['nama_hadiah'] = $nm_trx;
			    			$total_hadiah[$jnstrx]['total'] = $total_hadiah[$jnstrx]['total_faktur']+$total_wilayah;

	    					$total_faktur_wilayah=0;
							$total_wilayah = 0;

	    					if($l['Kd_Lokasi']!=$Kd_Lokasi){
	    						$currrow=$currrow+2;;
	    						$currcol = 7;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Faktur Hadiah (Rp)');
								$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
								$sheet->getStyle("G".$currrow.":I".$currrow)->getFont()->setBold(true);
								$currcol = 9;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total NK Hadiah (Rp)');
								$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
								$sheet->getStyle("G".$currrow.":I".$currrow)->getFont()->setBold(true);
								$currrow++;

	    						$total_faktur = 0;
	    						$total = 0;

	    						foreach ($hadiah as $key => $h) {
	    							$sheet->getStyle("F".$currrow.":I".$currrow)->getFont()->setBold(true);
	    							$currcol = 6;
	    							$total_faktur = $total_faktur+$h['total_faktur'];
	    							$total = $total+$h['total'];
	    							$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($h['nama_hadiah']));
	    							$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
	    							$currcol++;
	    							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($h['total_faktur'], 0, '.', ','));
	    							$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
	    							$currcol=$currcol+2;
	    							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($h['total'], 0, '.', ','));
	    							$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
	    							$currrow++;
	    						}

	    						$currcol = 6;
	    						$sheet->getStyle("F".$currrow.":I".$currrow)->getFont()->setBold(true);
	    						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
	    						$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
	    						$currcol++;
	    						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_faktur, 0, '.', ','));
	    						$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
	    						$currcol=$currcol+2;
	    						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total, 0, '.', ','));
	    						$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
	    						$currrow++;

	    						$hadiah = array();	
	    					}
	    				}


	    				if(!empty($l['Jns_Trx'])){
	    					$trx = $l['Nm_Trx'];
	    				}else{
	    					$trx = '-';
	    				}

	    				if($jns_trx!='' && $no!='1'){
	    					$currrow++;
	    				}

	    				$no=1;

	    				if($l['Kd_Lokasi']!=$Kd_Lokasi){
	    					$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['wilayah']);
							$sheet->getStyle("A".$currrow.":L".$currrow)->getFont()->setBold(true);
							$sheet->mergeCells("A".$currrow.":L".$currrow);
	    					$Kd_Lokasi = $l['Kd_Lokasi'];
							$currrow++;
	    				}

						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'HADIAH : '.$trx);
						$sheet->getStyle("A".$currrow.":L".$currrow)->getFont()->setBold(true);
						$sheet->mergeCells("A".$currrow.":L".$currrow);

						$currrow++;
						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No');
						$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Faktur Campaign');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Faktur Hadiah');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Pelanggan');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama Pelanggan');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Faktur Hadiah (Rp)');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Bukti');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total NK Hadiah (Rp)');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tgl Jth Tempo');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Kwitansi');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Distribusi');

						$sheet->getStyle("A".$currrow.":L".$currrow)->getFont()->setBold(true);

						$sheet->getStyle("A".$currrow.":L".$currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
						$sheet->getStyle("A".$currrow.":L".$currrow)->getFill()->getStartColor()->setARGB('eaeaeaea');


						$currrow++;

	    				$jns_trx = $l['Jns_Trx'];
	    				$nm_trx = $l['Nm_Trx'];
	    			}

					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['faktur_campaign']);
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['no_hadiah']);
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['Kd_Plg']);
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['nm_plg']);
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['Divisi']);
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['Total_Faktur'], 0, '.', ','));
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['No_bukti']);
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['Total'], 0, '.', ','));
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, date_format(date_create($l['Tgl_JatuhTempo']),'Y-m-d'));
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['No_Kwitansi']);
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['No_Distribusi']);
					$currrow++;

					$total_faktur_wilayah = $total_faktur_wilayah+$l['Total_Faktur'];
					$total_wilayah = $total_wilayah+$l['Total'];

					$no++;
	    		}

	    		$currcol = 6;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_faktur_wilayah, 0, '.', ','));
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
				$currcol=$currcol+2;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_wilayah, 0, '.', ','));
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
				$currrow++;

				$jnstrx = strtolower(str_replace(' ','',$jns_trx));

			    $hadiah[$jnstrx]['total_faktur'] = $total_faktur_wilayah;
			    
			    $hadiah[$jnstrx]['nama_hadiah'] = $nm_trx;

			    $hadiah[$jnstrx]['total']=$total_wilayah;

			    if(empty($total_hadiah[$jnstrx]['total_faktur'])){
			    	$total_hadiah[$jnstrx]['total_faktur'] = 0;
			    	$total_hadiah[$jnstrx]['total'] = 0;
			    }

			    $total_hadiah[$jnstrx]['total_faktur'] = $total_hadiah[$jnstrx]['total_faktur']+$total_faktur_wilayah;
			    $total_hadiah[$jnstrx]['nama_hadiah'] = $nm_trx;
			    $total_hadiah[$jnstrx]['total'] = $total_hadiah[$jnstrx]['total_faktur']+$total_wilayah;

	    		$total_faktur_wilayah=0;
				$total_wilayah = 0;

	    		$currrow=$currrow+2;;
	    		$currcol = 7;
	    		$sheet->getStyle("F".$currrow.":I".$currrow)->getFont()->setBold(true);
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Faktur Hadiah (Rp)');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
				$currcol = 9;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total NK Hadiah (Rp)');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
				$currrow++;

	    		$total_faktur = 0;
	    		$total = 0;

	    		foreach ($hadiah as $key => $h) {
	    			$sheet->getStyle("F".$currrow.":I".$currrow)->getFont()->setBold(true);
	    			$currcol = 6;
	    			$total_faktur = $total_faktur+$h['total_faktur'];
	    			$total = $total+$h['total'];
	    			$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($h['nama_hadiah']));
	    			$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
	    			$currcol++;
	    			$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($h['total_faktur'], 0, '.', ','));
	    			$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
	    			$currcol=$currcol+2;
	    			$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($h['total'], 0, '.', ','));
	    			$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
	    			$currrow++;
	    		}

	    		$currcol = 6;
	    		$sheet->getStyle("F".$currrow.":I".$currrow)->getFont()->setBold(true);
	    		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
	    		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
	    		$currcol++;
	    		$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_faktur, 0, '.', ','));
	    		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
	    		$currcol=$currcol+2;
	    		$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total, 0, '.', ','));
	    		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
	    		$currrow=$currrow+2;


	    		$currcol=1;
	    		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Semua Hadiah');
	    		$sheet->mergeCells("A".$currrow.":B".$currrow);
	    		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
	    		$sheet->getStyle("A".$currrow.":D".$currrow)->getFont()->setBold(true);
	    		$currcol=$currcol+2;
	    		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Faktur Hadiah (Rp)');
	    		$currcol++;
	    		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total NK Hadiah (Rp)');
	    		$currrow++;


	    		$total_faktur = 0;
	    		$total = 0;

	    		foreach ($total_hadiah as $key => $h) {
	    			$total_faktur = $total_faktur+$h['total_faktur'];
	    			$total = $total+$h['total'];

		    		$sheet->getStyle("A".$currrow.":D".$currrow)->getFont()->setBold(true);
	    			$currcol=1;
		    		$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($h['nama_hadiah']));
		    		$sheet->mergeCells("A".$currrow.":B".$currrow);
		    		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
		    		$currcol=$currcol+2;
		    		$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($h['total_faktur'], 0, '.', ','));
		    		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
		    		$currcol++;
		    		$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($h['total'], 0, '.', ','));
		    		$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
		    		$currrow++;
	    		}
	    						
		    	$sheet->getStyle("A".$currrow.":D".$currrow)->getFont()->setBold(true);
	    		$currcol=1;
		    	$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
		    	$sheet->mergeCells("A".$currrow.":B".$currrow);
		    	$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
		    	$currcol=$currcol+2;
		    	$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_faktur, 0, '.', ','));
		    	$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
		    	$currcol++;
		    	$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total, 0, '.', ','));
		    	$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
		    	$currrow++;

	    		$hadiah = array();	
	    					

	    	}else{
	    		$currcol = 1;
	    		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Data tidak ditemukan!!!');
	    	}


		    $this->Logs_Update($LogDate, 'SUCCESS', 'LAPORAN HADIAH LANGSUNG');

			for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
				$sheet->getColumnDimension($i)->setAutoSize(TRUE);
			}

			$filename='LAPORAN HADIAH LANGSUNG ['.date('Y-m-d H-i-s').']';
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');
			exit();

    	} else {
            redirect('dashboard');
        }
    }
	
	function Logs_insert($LogDate='',$description=''){
		$params = array();   
		$params['LogDate'] = $LogDate;
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "PERENCANAAN CAMPAIGN";
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
		$params['Module'] = "PERENCANAAN CAMPAIGN";
		$params['TrxID'] = date_format(date_create($LogDate),'YmdHis');
		$params['Description'] = $_SESSION["logged_in"]["username"]." ".$description;
		$params['Remarks']=$remarks;
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);
	}

}
?>
