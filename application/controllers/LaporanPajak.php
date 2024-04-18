<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/setasign/fpdi/src/autoload.php';

require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanPajak extends MY_Controller{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('LaporanPajakModel');
	}


	public function index(){

		$data['Wilayah'] = $this->LaporanPajakModel->Wilayah();
		$data['TipeFaktur'] = $this->LaporanPajakModel->TipeFaktur();

		$paramsLog = array();   
	 	$paramsLog['LogDate'] = date("Y-m-d H:i:s");
	  	$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
	  	$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
	  	$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="LAPORAN PAJAK"; 
	 	$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN PAJAK";
	 	$paramsLog['Remarks']="SUCCESS";
	  	$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($paramsLog); 

		$this->RenderView('LaporanPajakView',$data);
	}

	public function dealer(){
		$partner_type 	= str_replace('_',' ',$this->input->get('partner_type'));
		$wilayah 		= str_replace('_',' ',$this->input->get('wilayah'));
		$dealer 		= $this->LaporanPajakModel->Dealer($partner_type,$wilayah);
		print_r(json_encode($dealer));
	}

	public function getGudang(){

		if(!empty($this->input->get('wilayah'))){
			$wilayah 		= str_replace('_',' ',$this->input->get('wilayah'));
		}else{
			$wilayah 		= 'ACEH';
		}

		$gudang 		= $this->LaporanPajakModel->Gudang($wilayah);
		print_r($gudang);
	}

	public function pdf_laporan_pajak()
	{
			// echo json_encode($_GET);die;
			$paramsLog = array();   
		 	$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		  	$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		  	$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		  	$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$paramsLog['Module']="LAPORAN PAJAK"; 
		 	$paramsLog['TrxID'] = date("YmdHis");
			$paramsLog['Description']=$_SESSION["logged_in"]["username"]." PROSES PDF LAPORAN PKP";
		 	$paramsLog['Remarks']="";
		  	$paramsLog['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($paramsLog); 

			ini_set('max_execution_time', '0');
			ini_set("pcre.backtrack_limit", "1000000000");
			set_time_limit(0);

			// require_once __DIR__ . '\vendor\autoload.php';
			// $mpdf = new \Mpdf\Mpdf(array(
			// 	'mode' => '',
			// 	'format' => 'A4',
			// 	'default_font_size' => 8,
			// 	'default_font' => 'arial',
			// 	'margin_left' => 10,
			// 	'margin_right' => 10,
			// 	'margin_top' => 30,
			// 	'margin_bottom' => 10,
			// 	'margin_header' => 10,
			// 	'margin_footer' => 5,
			// 	'orientation' => 'L'
			// ));


			// $periode_dari 	= str_replace("/", "", $this->input->get('periode_dari'));
			// $periode_sampai = str_replace("/", "", $this->input->get('periode_sampai'));
			// $tgl_cetak		= $this->input->get('tgl_cetak');

			$periode_dari 	= date("Y-m-d", strtotime($this->input->get('periode_dari')));
			$periode_sampai = date("Y-m-d", strtotime($this->input->get('periode_sampai')));
			$printdate 		= "Print Date : " . date("d-m-Y h:i:sa");
			$tgl_cetak		= date("Y-m-d", strtotime($this->input->get('tgl_cetak')));
			$kd_cabang		= str_replace('_',' ', $this->input->get('kd_cabang'));
			$product		= str_replace('_',' ', $this->input->get('product'));
			$sparepart		= str_replace('_',' ', $this->input->get('sparepart'));
			$service		= str_replace('_',' ', $this->input->get('service'));
			$urut			= str_replace('_',' ', $this->input->get('urut'));
			$partner_type	= str_replace('_',' ', $this->input->get('partner_type'));
			$wilayah		= str_replace('_',' ', $this->input->get('wilayah'));
			
			if(!empty(str_replace('_',' ', $this->input->get('dealer'))) && str_replace('_',' ', $this->input->get('dealer'))!=='ALL'){
				$dealer = explode(" | ",str_replace('_',' ', $this->input->get('dealer')));
				$dealer = $dealer[1];
			}else{
				$dealer = 'ALL';
			}
			
			if(!empty(str_replace('_',' ', $this->input->get('gudang'))) && str_replace('_',' ', $this->input->get('gudang'))!=='ALL'){
				$gudang = explode(" | ",str_replace('_',' ', $this->input->get('gudang')));
				$gudang = $gudang[1];
			}else{
				$gudang = 'ALL';
			}

			$keterangan		= str_replace('_',' ', $this->input->get('keterangan'));
			$tipefaktur		= str_replace('_',' ', $this->input->get('tipefaktur'));



			$get = 	'api=APITES'.
					'&periode_dari='.$periode_dari.
					'&periode_sampai='.$periode_sampai.
					'&tgl_cetak='.$tgl_cetak.
					'&kd_cabang='.$kd_cabang.
					'&product='	.$product.
					'&sparepart='.$sparepart.
					'&service='.$service.
					'&urut='.$urut.
					'&partner_type='.$partner_type.
					'&wilayah='.$wilayah.
					'&dealer='.$dealer.
					'&gudang='.$gudang.
					'&keterangan='.$keterangan.
					'&tipefaktur='.$tipefaktur;

			$url_db	= $this->LaporanPajakModel->database($_SESSION['conn']->DatabaseId);
			$url = $url_db."bktAPI/LaporanPajak/LaporanPajak_a?".str_replace(' ', '%20', $get);
			// $url = "http://localhost:90/bktAPI/LaporanPajak/LaporanPajak_a?".$get;
// echo $url;
// die();
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $get);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Content-Type:application/json',
			));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$resultStr = json_decode(curl_exec($ch));

			$content = '<div style="width:90%; margin:auto">
            <table border="0" style="width:100%;">
					<tr>
						<td align="right" style="font-size:12px;">							
								'.$printdate.'							
						</td>
					</tr>

					<tr>
						<td align="center" style="font-size:15px;">
							<b>
								LAPORAN PKP
							</b>
						</td>
					</tr>
					<tr>
						<td align="center" style="font-size:12px;">
							PERIODE '.date_format(date_create($periode_dari),'d-M-Y').' <b>S/D</b> '.date_format(date_create($periode_sampai),'d-M-Y').'
						</td>
					</tr>
				</table>';

				$content .= '
				<table width="100%">
					<tr>
						<td></td>
					</tr>
				</table>';


				$footer  ='<table border="0" width="100%">';
				$footer .='<tr><td>Print : '.$tgl_cetak.'</td><td align="right">Page {PAGENO} of {nb}</td></tr>'; 
				$footer .='</table>';

						
						$content .= '<table style="width:100%; border-collapse: collapse;" border="2>
									<tr>
										<td colspan="12" style="padding:10px; font-size:13px;">
											<b>Partner Type : '.$partner_type.'</b>
										</td>
									</tr>
									<tr>
										<th valign="top" style="text-align: left; width: 9%; font-size: 12px; padding:5px; border:2px solid #333;">Tgl FakturP</th>
										<th valign="top" style="text-align: left; width: 9%; font-size: 12px; padding:5px; border:2px solid #333;">No FakturP</th>
										<th valign="top" style="text-align: left; width: 10%; font-size: 12px; padding:5px; border:2px solid #333;">Nama Pelanggan</th>
										<th valign="top" style="text-align: left; width: 9%; font-size: 12px; padding:5px; border:2px solid #333;">Kode Item</th>
										<th valign="top" style="text-align: left; width: 9%; font-size: 12px; padding:5px; border:2px solid #333;" align="right">Qty</th>
										<th valign="top" style="text-align: left; width: 9%; font-size: 12px; padding:5px; border:2px solid #333;" align="right">DPP (Rp)</th>
										<th valign="top" style="text-align: left; width: 9%; font-size: 12px; padding:5px; border:2px solid #333;" align="right">PPN (Rp)</th>
										<th valign="top" style="text-align: left; width: 9%; font-size: 12px; padding:5px; border:2px solid #333;" align="right">Total (Rp)</th>
										<th valign="top" style="text-align: left; width: 9%; font-size: 12px; padding:5px; border:2px solid #333;">No Faktur</th>
										<th valign="top" style="text-align: left; width: 9%; font-size: 12px; padding:5px; border:2px solid #333;">Tgl Faktur</th>
										<th valign="top" style="text-align: left; width: 9%; font-size: 12px; padding:5px; border:2px solid #333;">Tgl Trans</th>
									</tr>';
				$total_service = 0;
				$total_transportasi = 0;
				$dpp = 0;
				$ppn = 0;
				$grand_total=0;
				if(count($resultStr)){
					$tampno='';
					$simpanfaktur='';
					foreach ($resultStr as $key => $c) {
						if(empty($c->tgl_trans) || date("d-m-Y",strtotime($c->tgl_trans))=='01-01-1970'){
							$tgl_trans='';
						}else{
							$tgl_trans=date("d-m-Y",strtotime($c->tgl_trans));
						}
							$content .='<tr>';
							if($simpanfaktur!==$c->No_FakturP){
								$content .=	'<td valign="top" style="padding:5px; border:2px solid #333;">'.date_format(date_create($c->Tgl_FakturP),"d-m-Y").'</td>
											<td valign="top" style="padding:5px; border:2px solid #333;">'.$c->No_FakturP.'</td>
											<td valign="top" style="padding:5px; border:2px solid #333;">'.$c->Nm_Plg.'</td>';
							}else{
								$content .='<td></td><td></td><td></td>';
							}


								$content .=	'<td valign="top" style="padding:5px; border:2px solid #333;">'.$c->Kd_Brg.'</td>
											<td valign="top" style="padding:5px; border:2px solid #333;" align="right">'.$c->Qty.'</td>';
											
								if($c->Kd_Brg=='BIAYA TRANSPORT'){		
								
									$dpp=$dpp+$c->DPP;
									$ppn=$ppn+$c->PPN; 
									$total_transportasi += $c->DPP;
								
									$content .=	'<td valign="top" style="padding:5px; border:2px solid #333;" align="right">'.number_format($c->DPP,2,",",".").'</td>
												<td valign="top" style="padding:5px; border:2px solid #333;" align="right">'.number_format($c->PPN,2,",",".").'</td>';
											
								}
											
											
							if($simpanfaktur!==$c->No_FakturP){
								$content .='	<td valign="top" style="padding:5px; border:2px solid #333;" align="right">'.number_format($c->DPP,2,",",".").'</td>
												<td valign="top" style="padding:5px; border:2px solid #333;" align="right">'.number_format($c->PPN,2,",",".").'</td>
												<td valign="top" style="padding:5px; border:2px solid #333;" align="right">'.number_format($c->Grandtotal,2,",",".").'</td>
												<td valign="top" style="padding:5px; border:2px solid #333;">'.trim($c->No_Faktur).'</td>
												<td valign="top" style="padding:5px; border:2px solid #333;">'.date_format(date_create($c->Tgl_Faktur),"d-m-Y").'</td>
												<td valign="top" style="padding:5px; border:2px solid #333;">'.$tgl_trans.'</td>
											</tr>';
								$simpanfaktur = $c->No_FakturP;
								
								$total_service += (($c->Kd_Brg=='JASA SERVICE') ? $c->DPP : 0);
				
				
								$dpp=$dpp+$c->DPP;
								$ppn=$ppn+$c->PPN; 
								$grand_total=$grand_total+$c->Grandtotal; 
							}else{
								if($c->Kd_Brg!='BIAYA TRANSPORT'){	
									$content .='	<td valign="top" style="padding:5px; border:2px solid #333;" align="right"></td>
									<td valign="top" style="padding:5px; border:2px solid #333;" align="right"></td>';
								}			
									$content .='<td valign="top" style="padding:5px; border:2px solid #333;" align="right"></td>
												<td valign="top" style="padding:5px; border:2px solid #333;"></td>
												<td valign="top" style="padding:5px; border:2px solid #333;"></td>
												<td valign="top" style="padding:5px; border:2px solid #333;"></td>
											</tr>';
							}


						// $mpdf->setFooter($footer);

					}
				}

				$content .= '<tr>
								<td valign="top" colspan="5" rowspan="5"></td>
								<td valign="top" style="font-size:12px; padding:5px; border:2px solid #333;" align="right"><b>Total Service</b></td>
								<td valign="top" style="font-size:12px; padding:5px; border:2px solid #333;" colspan="2" align="right"><b>'.number_format($total_service,2,",",".").'</b></td>
							</tr>';
							
				$content .= '<tr>
								<td valign="top" style="font-size:12px; padding:5px; border:2px solid #333;" align="right"><b>Total Transportasi</b></td>
								<td valign="top" style="font-size:12px; padding:5px; border:2px solid #333;" colspan="2" align="right"><b>'.number_format($total_transportasi,2,",",".").'</b></td>
							</tr>';
				$content .= '<tr>
								<td valign="top" style="font-size:12px; padding:5px; border:2px solid #333;" align="right"><b>Total DPP</b></td>
								<td valign="top" style="font-size:12px; padding:5px; border:2px solid #333;" colspan="2" align="right"><b>'.number_format(($dpp),2,",",".").'</b></td>
							</tr>';
				$content .= '<tr>
								<td valign="top" style="font-size:12px; padding:5px; border:2px solid #333;" align="right"><b>Total PPN</b></td>
								<td valign="top" style="font-size:12px; padding:5px; border:2px solid #333;" colspan="2" align="right"><b>'.number_format($ppn,2,",",".").'</b></td>
							</tr>';
				$content .= '<tr>
								<td valign="top" style="font-size:12px; padding:5px; border:2px solid #333;" align="right"><b>Total</b></td>
								<td valign="top" style="font-size:12px; padding:5px; border:2px solid #333;" colspan="2" align="right"><b>'.number_format($grand_total,2,",",".").'</b></td>
							</tr>';
				$content .= '</table></div>';
				


			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			echo $content;

			// $mpdf->SetHTMLHeader($header,'','1');
			// $mpdf->WriteHTML($content);
			// $mpdf->Output();

	}

	public function excel_laporan_pajak() {

			$paramsLog = array();   
		 	$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		  	$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		  	$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		  	$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$paramsLog['Module']="LAPORAN PAJAK"; 
		 	$paramsLog['TrxID'] = date("YmdHis");
			$paramsLog['Description']=$_SESSION["logged_in"]["username"]." PROSES EXCEL LAPORAN PKP";
		 	$paramsLog['Remarks']="";
		  	$paramsLog['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($paramsLog); 

			// ini_set('max_execution_time', '0');
			ini_set("pcre.backtrack_limit", "5000000");
			set_time_limit(0);

			// $periode_dari 	= str_replace("/", "", $this->input->get('periode_dari'));
			// $periode_sampai = str_replace("/", "", $this->input->get('periode_sampai'));
			// $tgl_cetak		= $this->input->get('tgl_cetak');

			$periode_dari 	= date("Y-m-d", strtotime($this->input->get('periode_dari')));
			$periode_sampai = date("Y-m-d", strtotime($this->input->get('periode_sampai')));
			$periode 		= "Periode " .date_format(date_create($periode_dari),'d-M-Y'). " S/D " .date_format(date_create($periode_sampai),'d-M-Y')	;
			$printdate 		= "Print Date : " . date("d-m-Y h:i:sa");
			$judul 			= "LAPORAN PKP";
			$tgl_cetak		= date("Y-m-d", strtotime($this->input->get('tgl_cetak')));
			$kd_cabang		= str_replace('_',' ', $this->input->get('kd_cabang'));
			$product		= str_replace('_',' ', $this->input->get('product'));
			$sparepart		= str_replace('_',' ', $this->input->get('sparepart'));
			$service		= str_replace('_',' ', $this->input->get('service'));
			$urut			= str_replace('_',' ', $this->input->get('urut'));
			$partner_type	= str_replace('_',' ', $this->input->get('partner_type'));
			$wilayah		= str_replace('_',' ', $this->input->get('wilayah'));
			
			if(!empty(str_replace('_',' ', $this->input->get('dealer'))) && str_replace('_',' ', $this->input->get('dealer'))!=='ALL'){
				$dealer = explode(" | ",str_replace('_',' ', $this->input->get('dealer')));
				$dealer = $dealer[1];
			}else{
				$dealer = 'ALL';
			}
			
			if(!empty(str_replace('_',' ', $this->input->get('gudang'))) && str_replace('_',' ', $this->input->get('gudang'))!=='ALL'){
				$gudang = explode(" | ",str_replace('_',' ', $this->input->get('gudang')));
				$gudang = $gudang[1];
			}else{
				$gudang = 'ALL';
			}

			$keterangan		= str_replace('_',' ', $this->input->get('keterangan'));
			$tipefaktur		= str_replace('_',' ', $this->input->get('tipefaktur'));


			$get = 	'api=APITES'.
					'&periode_dari='.$periode_dari.
					'&periode_sampai='.$periode_sampai.
					'&tgl_cetak='.$tgl_cetak.
					'&kd_cabang='.$kd_cabang.
					'&product='	.$product.
					'&sparepart='.$sparepart.
					'&service='.$service.
					'&urut='.$urut.
					'&partner_type='.$partner_type.
					'&wilayah='.$wilayah.
					'&dealer='.$dealer.
					'&gudang='.$gudang.
					'&keterangan='.$keterangan.
					'&tipefaktur='.$tipefaktur;

			$url_db	= $this->LaporanPajakModel->database($_SESSION['conn']->DatabaseId);
			$url = $url_db."bktAPI/LaporanPajak/LaporanPajak_a?".str_replace(' ', '%20', $get);
			// die($url);

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $get);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Content-Type:application/json',
			));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$resultStr = json_decode(curl_exec($ch));

			// print_r($resultStr);
			// die;

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$page_title = $judul;
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', $judul);
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', $periode);
			            								
			$currcol = 1;
			$currrow = 4;
					
			// die("!");

			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Partner Type');
			$sheet->getColumnDimension('A')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tgl FakturP');
			$sheet->getColumnDimension('B')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No FakturP');
			$sheet->getColumnDimension('C')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama Pelanggan');
			$sheet->getColumnDimension('D')->setWidth(25);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Item');
			$sheet->getColumnDimension('E')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Qty');
			$sheet->getColumnDimension('F')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DPP (Rp)');
			$sheet->getColumnDimension('G')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PPN (Rp)');
			$sheet->getColumnDimension('H')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total (Rp)');
			$sheet->getColumnDimension('I')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Faktur');
			$sheet->getColumnDimension('J')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tgl Faktur');
			$sheet->getColumnDimension('K')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tgl Trans');
			$sheet->getColumnDimension('L')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Keterangan');
			$sheet->getColumnDimension('M')->setWidth(30);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
														
			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
						
			$no_bukti_temp = "";
			$no_faktur_temp = ""; 
			
			// $x = 0;
			// Detail
			$simpanfaktur='';
			$totaldpp = 0;
			$totalppn = 0;
			$grand_total = 0;
			$total_service = 0;
			$total_transportasi = 0;
			if(count($resultStr)){

				foreach ($resultStr as $key => $c) {
					// $x += 1;
					$currrow++; 

					if($simpanfaktur!==$c->No_Faktur){
						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $partner_type);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-m-Y",strtotime($c->Tgl_FakturP)));
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $c->No_FakturP);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $c->Nm_Plg);
						$currcol += 1;
					}else{
						$currcol = 5;
					}

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $c->Kd_Brg);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $c->Qty);
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$currcol += 1;
					
					if($c->Kd_Brg=='BIAYA TRANSPORT'){
						$totaldpp = $totaldpp+$c->DPP;
						$totalppn = $totalppn+$c->PPN;
						$total_transportasi = $total_transportasi+$c->DPP;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $c->DPP);
						$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $c->PPN);
						$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
						$currcol += 1;
					}
					
					if($simpanfaktur!==$c->No_Faktur){
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $c->DPP);
						$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $c->PPN);
						$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $c->Grandtotal);
						$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $c->No_Faktur);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-m-Y",strtotime($c->Tgl_Faktur)));
						$currcol += 1;

						if(empty($c->tgl_trans) || date("d-m-Y",strtotime($c->tgl_trans))=='01-01-1970'){
							$tgl_trans='';
						}else{
							$tgl_trans=date("d-m-Y",strtotime($c->tgl_trans));
						}
						
						$sheet->getStyle('E'.$currrow.':I'.$currrow)->getNumberFormat()->setFormatCode('#,##0');

						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tgl_trans);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $c->Ket);
						
						$totaldpp = $totaldpp+$c->DPP;
						$totalppn = $totalppn+$c->PPN;
						$grand_total = $grand_total+$c->Grandtotal;
						
						$total_service = $total_service + (($c->Kd_Brg=='JASA SERVICE') ? $c->DPP : 0);
						
						$simpanfaktur = $c->No_Faktur;
					}
				}
			}

			$currrow++;
			$currcol = 6;
			$boldFontStyle = [
			    'font' => [
			        'bold' => true,
			    ],
			];

			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);
			$currcol++;

			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $totaldpp);
			$sheet->getStyle('G'.$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);
			$currcol++;

			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $totalppn);
			$sheet->getStyle('H'.$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);
			$currcol++;

			$sheet->setCellValueByColumnAndRow($currcol, $currrow, ($grand_total));
			$sheet->getStyle('I'.$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);

			$currrow++;
			$currrow++;
			$currcol = 8;
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);
			$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'TOTAL SERVICE');
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);
			$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_service);
			$sheet->getStyle('I'.$currrow)->getNumberFormat()->setFormatCode('#,##0');

			$currrow++;
			$currcol = 8;
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);
			$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'TOTAL TRANSPORTASI');
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);
			$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_transportasi);
			$sheet->getStyle('I'.$currrow)->getNumberFormat()->setFormatCode('#,##0');

			$currrow++;
			$currcol = 8;
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);
			$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'TOTAL DPP');
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);
			$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $totaldpp);
			$sheet->getStyle('I'.$currrow)->getNumberFormat()->setFormatCode('#,##0');

			$currrow++;
			$currcol = 8;
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);
			$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'TOTAL PPN');
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);
			$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $totalppn);
			$sheet->getStyle('I'.$currrow)->getNumberFormat()->setFormatCode('#,##0');

			$currrow++;
			$currcol = 8;
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);
			$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'GRAND TOTAL');
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, ($grand_total));
			$sheet->getStyle('I'.$currrow)->getNumberFormat()->setFormatCode('#,##0');
			
			

			// print_r ($x);
			// die;

			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A4:'.$max_col.'5')->getAlignment()->setHorizontal($alignment_center);
			
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A4:'.$max_col.'5')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
			// border
			$sheet->getStyle("A1:".$max_col."5")->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet->getStyle('A4:'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');
			
				
			$filename= $judul.' ['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 

			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
			exit();
	}

	public function pdf_laporan_pajak_a1(){
 
			$paramsLog = array();   
		 	$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		  	$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		  	$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		  	$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$paramsLog['Module']="LAPORAN PAJAK"; 
		 	$paramsLog['TrxID'] = date("YmdHis");
			$paramsLog['Description']=$_SESSION["logged_in"]["username"]." PROSES PDF LAPORAN PAJAK A1";
		 	$paramsLog['Remarks']="";
		  	$paramsLog['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($paramsLog); 

			//ini_set('max_execution_time', '30');
			ini_set("pcre.backtrack_limit", "5000000");
			set_time_limit(0);

			// require_once __DIR__ . '\vendor\autoload.php';
			// $mpdf = new \Mpdf\Mpdf(array(
			// 	'mode' => '',
			// 	'format' => 'A4',
			// 	'default_font_size' => 8,
			// 	'default_font' => 'arial',
			// 	'margin_left' => 10,
			// 	'margin_right' => 10,
			// 	'margin_top' => 30,
			// 	'margin_bottom' => 10,
			// 	'margin_header' => 10,
			// 	'margin_footer' => 5,
			// 	'orientation' => 'P'
			// ));


			$periode_dari 	= date("Y-m-d", strtotime($this->input->get('periode_dari')));
			$periode_sampai = date("Y-m-d", strtotime($this->input->get('periode_sampai')));
			$printdate = "Print Date : " . date("d-m-Y h:i:sa");
			$tgl_cetak		= $this->input->get('tgl_cetak');
			$kd_cabang		= str_replace('_',' ', $this->input->get('kd_cabang'));
			$product		= str_replace('_',' ', $this->input->get('product'));
			$sparepart		= str_replace('_',' ', $this->input->get('sparepart'));
			$service		= str_replace('_',' ', $this->input->get('service'));
			$urut			= str_replace('_',' ', $this->input->get('urut'));
			$partner_type	= str_replace('_',' ', $this->input->get('partner_type'));
			$wilayah		= str_replace('_',' ', $this->input->get('wilayah'));
			
			if(!empty(str_replace('_',' ', $this->input->get('dealer'))) && str_replace('_',' ', $this->input->get('dealer'))!=='ALL'){
				$dealer = explode(" | ",str_replace('_',' ', $this->input->get('dealer')));
				$dealer = $dealer[1];
			}else{
				$dealer = 'ALL';
			}
			
			if(!empty(str_replace('_',' ', $this->input->get('gudang'))) && str_replace('_',' ', $this->input->get('gudang'))!=='ALL'){
				$gudang = explode(" | ",str_replace('_',' ', $this->input->get('gudang')));
				$gudang = $gudang[1];
			}else{
				$gudang = 'ALL';
			}

			$keterangan		= str_replace('_',' ', $this->input->get('keterangan'));
			$tipefaktur		= str_replace('_',' ', $this->input->get('tipefaktur'));



			$get = 	'api=APITES'.
					'&periode_dari='.$periode_dari.
					'&periode_sampai='.$periode_sampai.
					'&tgl_cetak='.$tgl_cetak.
					'&kd_cabang='.$kd_cabang.
					'&product='	.$product.
					'&sparepart='.$sparepart.
					'&service='.$service.
					'&urut='.$urut.
					'&partner_type='.$partner_type.
					'&wilayah='.$wilayah.
					'&dealer='.$dealer.
					'&gudang='.$gudang.
					'&keterangan='.$keterangan.
					'&tipefaktur='.$tipefaktur;

			$url_db	= $this->LaporanPajakModel->database($_SESSION['conn']->DatabaseId);
			$url = $url_db."bktAPI/LaporanPajak/LaporanPajak_b?".str_replace(' ', '%20', $get);

			
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $get);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Content-Type:application/json',
			));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$resultStr = json_decode(curl_exec($ch));

			// print_r($resultStr);
			// die;

			$content = '<div style="width:90%; margin:auto"><table border="0" style="width:100%; ">
					<tr>
						<td align="right" style="font-size:12px;">							
								'.$printdate.'							
						</td>
					</tr>

					<tr>
						<td align="center" style="font-size:15px;">
							<b>
								LAPORAN PAJAK A1
							</b>
						</td>
					</tr>
					<tr>
						<td align="center" style="font-size:12px;">
							PERIODE '.date_format(date_create($periode_dari),'d-M-Y').' <b>S/D</b> '.date_format(date_create($periode_sampai),'d-M-Y').'
						</td>
					</tr>
				</table>';

				$content .= '
				<table width="100%">
					<tr>
						<td></td>
					</tr>
				</table>';


				$footer  ='<table border="0" width="100%">';
				$footer .='<tr><td>Print : '.$tgl_cetak.'</td><td align="right">Page {PAGENO} of {nb}</td></tr>'; 
				$footer .='</table>';

						$content .= '<table style="width:100%; border-collapse: collapse;" border="1">
									<tr>
										<td colspan="6" style="padding:10px; font-size:13px;">
											<b>Partner Type : '.$partner_type.'</b>
										</td>
									</tr>
									<tr>
										<th valign="top" style="text-align: left; width: 30px; font-size: 12px; padding:5px; border:2px solid #333;" align="center">No</th>
										<th valign="top" style="text-align: left; width: 20%; font-size: 12px; padding:5px; border:2px solid #333;">Nama Pelanggan</th>
										<th valign="top" style="text-align: left; width: 20%; font-size: 12px; padding:5px; border:2px solid #333;">NPWP</th>
										<th valign="top" style="text-align: left; width: 20%; font-size: 12px; padding:5px; border:2px solid #333;">No Faktur Pajak</th>
										<th valign="top" style="text-align: left; width: 20%; font-size: 12px; padding:5px; border:2px solid #333;">Tanggal</th>
										<th valign="top" style="text-align: left; width: 20%; font-size: 12px; padding:5px; border:2px solid #333;" align="right">Total (Rp)</th>
									</tr>';
				$total_service = 0;
				$total_transportasi = 0;
				$no=1;
				if(count($resultStr)){
					$tampno='';
					foreach ($resultStr as $key => $c) {

							if(empty($tampno) || $tampno!==$c->No_FakturP){
								
								$total = $c->Grandtotal;
								
								if($c->Ket=='JASA SERVICE'){
									$total_service += ($c->DPP) + ($c->PPN);
								}
								
								if($c->Ket=='BIAYA TRANSPORT'){
									$total_transportasi += ($c->DPP) + ($c->PPN);
									$total_service = $total_service - (($c->DPP) + ($c->PPN));
								}
								
								if($c->Ket!='BIAYA TRANSPORT'){
								$content .='<tr>
												<td valign="top" style="padding:5px; border:2px solid #333;" align="center">'.$no.'.</td>
												<td valign="top" style="padding:5px; border:2px solid #333;">'.$c->Nm_Plg.'</td>
												<td valign="top" style="padding:5px; border:2px solid #333;">'.$c->NPWP.'</td>
												<td valign="top" style="padding:5px; border:2px solid #333;">'.$c->No_FakturP.'</td>
												<td valign="top" style="padding:5px; border:2px solid #333;">'.date_format(date_create($c->Tgl_FakturP),"d-m-Y").'</td>
												<td valign="top" style="padding:5px; border:2px solid #333;" align="right">'.number_format($total,2,",",".").'</td>
											</tr>';
								}
								
								// $mpdf->setFooter($footer);
								$no++;
								$tampno=$c->No_FakturP;
							}
					}

				}

				$content .= '<tr>
								<td valign="top" style="font-size:12px; padding:5px; border:2px solid #333;" align="right" colspan="5"><b>Total Service: </b></td>
								<td valign="top" style="font-size:12px; padding:5px; border:2px solid #333;" align="right"><b>'.number_format($total_service,2,",",".").'</b></td>
							</tr>';
				$content .= '<tr>
								<td valign="top" style="font-size:12px; padding:5px; border:2px solid #333;" align="right" colspan="5"><b>Total Transportasi: </b></td>
								<td valign="top" style="font-size:12px; padding:5px; border:2px solid #333;" align="right"><b>'.number_format($total_transportasi,2,",",".").'</b></td>
							</tr>';
				$content .= '<tr>
								<td valign="top" style="font-size:12px; padding:5px; border:2px solid #333;" align="right" colspan="5"><b>Grand Total: </b></td>
								<td valign="top" style="font-size:12px; padding:5px; border:2px solid #333;" align="right"><b>'.number_format(($total_service+$total_transportasi),2,",",".").'</b></td>
							</tr>';
				$content .= '</table></div>';

			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			echo $content;

			// $mpdf->SetHTMLHeader($header,'','1');
			// $mpdf->WriteHTML($content);
			// $mpdf->Output();


	}

	public function excel_laporan_pajak_a1() {

			$paramsLog = array();   
		 	$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		  	$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		  	$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		  	$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$paramsLog['Module']="LAPORAN PAJAK"; 
		 	$paramsLog['TrxID'] = date("YmdHis");
			$paramsLog['Description']=$_SESSION["logged_in"]["username"]." PROSES EXCEL LAPORAN PAJAK A1";
		 	$paramsLog['Remarks']="";
		  	$paramsLog['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($paramsLog); 

			//ini_set('max_execution_time', '30');
			ini_set("pcre.backtrack_limit", "5000000");
			set_time_limit(0);

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


			$periode_dari 	= date("Y-m-d", strtotime($this->input->get('periode_dari')));
			$periode_sampai = date("Y-m-d", strtotime($this->input->get('periode_sampai')));
			$periode 		= "Periode " .date_format(date_create($periode_dari),'d-M-Y'). " S/D " .date_format(date_create($periode_sampai),'d-M-Y')	;
			$printdate 		= "Print Date : " . date("d-m-Y h:i:sa");
			$judul 			= "LAPORAN PAJAK A1";
			$tgl_cetak		= $this->input->get('tgl_cetak');
			$kd_cabang		= str_replace('_',' ', $this->input->get('kd_cabang'));
			$product		= str_replace('_',' ', $this->input->get('product'));
			$sparepart		= str_replace('_',' ', $this->input->get('sparepart'));
			$service		= str_replace('_',' ', $this->input->get('service'));
			$urut			= str_replace('_',' ', $this->input->get('urut'));
			$partner_type	= str_replace('_',' ', $this->input->get('partner_type'));
			$wilayah		= str_replace('_',' ', $this->input->get('wilayah'));
			
			if(!empty(str_replace('_',' ', $this->input->get('dealer'))) && str_replace('_',' ', $this->input->get('dealer'))!=='ALL'){
				$dealer = explode(" | ",str_replace('_',' ', $this->input->get('dealer')));
				$dealer = $dealer[1];
			}else{
				$dealer = 'ALL';
			}
			
			if(!empty(str_replace('_',' ', $this->input->get('gudang'))) && str_replace('_',' ', $this->input->get('gudang'))!=='ALL'){
				$gudang = explode(" | ",str_replace('_',' ', $this->input->get('gudang')));
				$gudang = $gudang[1];
			}else{
				$gudang = 'ALL';
			}

			$keterangan		= str_replace('_',' ', $this->input->get('keterangan'));
			$tipefaktur		= str_replace('_',' ', $this->input->get('tipefaktur'));



			$get = 	'api=APITES'.
					'&periode_dari='.$periode_dari.
					'&periode_sampai='.$periode_sampai.
					'&tgl_cetak='.$tgl_cetak.
					'&kd_cabang='.$kd_cabang.
					'&product='	.$product.
					'&sparepart='.$sparepart.
					'&service='.$service.
					'&urut='.$urut.
					'&partner_type='.$partner_type.
					'&wilayah='.$wilayah.
					'&dealer='.$dealer.
					'&gudang='.$gudang.
					'&keterangan='.$keterangan.
					'&tipefaktur='.$tipefaktur;

			$url_db	= $this->LaporanPajakModel->database($_SESSION['conn']->DatabaseId);
			$url = $url_db."bktAPI/LaporanPajak/LaporanPajak_b?".str_replace(' ', '%20', $get);
			// echo $url;
			// die();
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $get);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type:application/json',
			));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$resultStr = json_decode(curl_exec($ch));


			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			$page_title = $judul;
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', $judul);
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', $periode);
			            								
			$currcol = 1;
			$currrow = 4;
					
			// Header
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Partner Type');
			$sheet->getColumnDimension('A')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;			
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama Pelanggan');
			$sheet->getColumnDimension('B')->setWidth(25);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NPWP');
			$sheet->getColumnDimension('C')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Faktur Pajak');
			$sheet->getColumnDimension('D')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tanggal');
			$sheet->getColumnDimension('E')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total (Rp)');
			$sheet->getColumnDimension('F')->setWidth(15);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Keterangan');
			$sheet->getColumnDimension('G')->setWidth(30);
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$currcol += 1;
														
			$max_col = $currcol-2;
			
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;			

			$currrow += 1;
						
			$no_bukti_temp = "";
			$no_faktur_temp = ""; 
			

			// Detail
			if(count($resultStr)){
				$tampno='';
				$total_service=0;
				$total_transportasi=0;
				foreach ($resultStr as $key => $c) {
					if(empty($tampno) || $tampno!==$c->No_FakturP){
					
						$total = $c->Grandtotal;
						
						if($c->Ket=='JASA SERVICE'){
							$total_service += ($c->DPP) + ($c->PPN);
						}
						
						if($c->Ket=='BIAYA TRANSPORT'){
							$total_transportasi += ($c->DPP) + ($c->PPN);
							$total_service = $total_service - (($c->DPP) + ($c->PPN));
						}
						
						if($c->Ket!='BIAYA TRANSPORT'){
					
							$currrow++; 

							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $partner_type);
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $c->Nm_Plg);
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $c->No_FakturP);
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $c->NPWP);
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-m-Y",strtotime($c->Tgl_FakturP)));
							$currcol += 1;					
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total);
							$sheet->getStyle('F'.$currrow)->getNumberFormat()->setFormatCode('#,##0');
							// $sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($c->Ket));
							$currcol += 1;
							
							
							$tampno=$c->No_FakturP;
						}
					}
				}
			}

			$currcol = 5;
			$boldFontStyle = [
			    'font' => [
			        'bold' => true,
			    ],
			];

			$currrow++;
			$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'TOTAL SERVICE');
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);
			

			$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_service);
			$sheet->getStyle('F'.$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);
			
			$currrow++;
			$currcol = 5;
			$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'TOTAL TRANSPORTASI');
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);
			

			$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $total_transportasi);
			$sheet->getStyle('F'.$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);
			
			$currrow++;
			$currcol = 5;
			$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'GRANDTOTAL');
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);
	
	

			$sheet->setCellValueByColumnAndRow($currcol++, $currrow, ($total_service + $total_transportasi) );
			$sheet->getStyle('F'.$currrow)->getNumberFormat()->setFormatCode('#,##0');
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->applyFromArray($boldFontStyle);

			// print_r ($judul);

			$max_col = PHPExcel_Cell::stringFromColumnIndex($max_col); // index kolom terakhir (paling kanan)
			
			// rata tengah header
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$sheet->getStyle('A4:'.$max_col.'5')->getAlignment()->setHorizontal($alignment_center);
			
			// warna header
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			$sheet->getStyle('A4:'.$max_col.'5')->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('ffe53b');
			
			// border
			$sheet->getStyle("A1:".$max_col."5")->getFont()->setBold(true);
			$styleArray = [
			'borders' => [
			'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			],
			],
			];
			$sheet->getStyle('A4:'.$max_col.$currrow)->applyFromArray($styleArray);
			$sheet->setSelectedCell('A1');
			
				
			$filename= $judul.' ['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 

			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
			exit();


	}

	public function pdf_laporan_pajak_edit(){

			$paramsLog = array();   
		 	$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		  	$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		  	$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		  	$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$paramsLog['Module']="LAPORAN PAJAK"; 
		 	$paramsLog['TrxID'] = date("YmdHis");
			$paramsLog['Description']=$_SESSION["logged_in"]["username"]." PROSES PDF FAKTUR BELUM DI EDIT PAJAK";
		 	$paramsLog['Remarks']="";
		  	$paramsLog['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($paramsLog); 

			//ini_set('max_execution_time', '30');
			ini_set("pcre.backtrack_limit", "5000000");
			set_time_limit(0);

			// require_once __DIR__ . '\vendor\autoload.php';
			// $mpdf = new \Mpdf\Mpdf(array(
			// 	'mode' => '',
			// 	'format' => 'A4',
			// 	'default_font_size' => 8,
			// 	'default_font' => 'arial',
			// 	'margin_left' => 10,
			// 	'margin_right' => 10,
			// 	'margin_top' => 30,
			// 	'margin_bottom' => 10,
			// 	'margin_header' => 10,
			// 	'margin_footer' => 5,
			// 	'orientation' => 'P'
			// ));


			$periode_dari 	= str_replace("/", "", $this->input->get('periode_dari'));
			$periode_sampai = str_replace("/", "", $this->input->get('periode_sampai'));
			$printdate = "Print Date : " . date("d-m-Y h:i:sa");
			$tgl_cetak		= $this->input->get('tgl_cetak');
			$kd_cabang		= str_replace('_',' ', $this->input->get('kd_cabang'));
			$product		= str_replace('_',' ', $this->input->get('product'));
			$sparepart		= str_replace('_',' ', $this->input->get('sparepart'));
			$service		= str_replace('_',' ', $this->input->get('service'));
			$urut			= str_replace('_',' ', $this->input->get('urut'));
			$partner_type	= str_replace('_',' ', $this->input->get('partner_type'));
			$wilayah		= str_replace('_',' ', $this->input->get('wilayah'));
			
			if(!empty(str_replace('_',' ', $this->input->get('dealer'))) && str_replace('_',' ', $this->input->get('dealer'))!=='ALL'){
				$dealer = explode(" | ",str_replace('_',' ', $this->input->get('dealer')));
				$dealer = $dealer[1];
			}else{
				$dealer = 'ALL';
			}
			
			if(!empty(str_replace('_',' ', $this->input->get('gudang'))) && str_replace('_',' ', $this->input->get('gudang'))!=='ALL'){
				$gudang = explode(" | ",str_replace('_',' ', $this->input->get('gudang')));
				$gudang = $gudang[1];
			}else{
				$gudang = 'ALL';
			}

			$keterangan		= str_replace('_',' ', $this->input->get('keterangan'));
			$tipefaktur		= str_replace('_',' ', $this->input->get('tipefaktur'));



			$get = 	'api=APITES'.
					'&periode_dari='.$periode_dari.
					'&periode_sampai='.$periode_sampai.
					'&tgl_cetak='.$tgl_cetak.
					'&kd_cabang='.$kd_cabang.
					'&product='	.$product.
					'&sparepart='.$sparepart.
					'&service='.$service.
					'&urut='.$urut.
					'&partner_type='.$partner_type.
					'&wilayah='.$wilayah.
					'&dealer='.$dealer.
					'&gudang='.$gudang.
					'&keterangan='.$keterangan.
					'&tipefaktur='.$tipefaktur;

			$url_db	= $this->LaporanPajakModel->database($_SESSION['conn']->DatabaseId);

			$url = $url_db."bktAPI/LaporanPajak/LaporanPajak_c?".str_replace(' ', '%20', $get);

			// print_r($url);
			// die;


			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $get);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Content-Type:application/json',
			));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$resultStr = json_decode(curl_exec($ch));

			$content = '<div style="width:90%; margin:auto"><table border="0" style="width:100%; ">
			<table border="0" style="width:100%;">
					<tr>
						<td align="right" style="font-size:12px;">							
								'.$printdate.'							
						</td>
					</tr>
					<tr>
						<td align="center" style="font-size:15px;">
							<b>
								FAKTUR-FAKTUR BELUM DIEDIT PAJAK
							</b>
						</td>
					</tr>
					<tr>
						<td align="center" style="font-size:12px;">
							PERIODE '.date_format(date_create($periode_dari),'d-M-Y').' <b>S/D</b> '.date_format(date_create($periode_sampai),'d-M-Y').'<br>'.date('Y-m-d').' '. date('h:i:s').'
						</td>
					</tr>
				</table>';

				$content .= '
				<table width="100%">
					<tr>
						<td></td>
					</tr>
				</table>';


				$footer  ='<table border="0" width="100%">';
				$footer .='<tr><td>Print : '.$tgl_cetak.'</td><td align="right">Page {PAGENO} of {nb}</td></tr>'; 
				$footer .='</table>';

						$content .= '<table style="width:100%; border-collapse: collapse;" border="1">
									<tr>
										<td colspan="6" style="padding:10px; font-size:13px;">
											<b>Partner Type : '.$partner_type.'</b>
										</td>
									</tr>
									<tr>
										<th valign="top" style="text-align: left; width: 30px; font-size: 12px; padding:5px; border:2px solid #333;" align="center">No</th>
										<th valign="top" style="text-align: left; width: 20%; font-size: 12px; padding:5px; border:2px solid #333;">Nomor Faktur</th>
										<th valign="top" style="text-align: left; width: 20%; font-size: 12px; padding:5px; border:2px solid #333;">Tanggal</th>
										<th valign="top" style="text-align: left; width: 20%; font-size: 12px; padding:5px; border:2px solid #333;">Nominal</th>
										<th valign="top" style="text-align: left; width: 20%; font-size: 12px; padding:5px; border:2px solid #333;">Nama Pelanggan</th>
										<th valign="top" style="text-align: left; width: 20%; font-size: 12px; padding:5px; border:2px solid #333;">Alamat</th>
									</tr>';
				$Grandtotal = 0;
				$no=1;
				if(count($resultStr)){
					$simpanfaktur='';
					foreach ($resultStr as $key => $c) {
						if($simpanfaktur!==$c->No_Faktur){
						
							$content .='<tr>
											<td valign="top" style="padding:5px; border:2px solid #333;" align="center">'.$no.'.</td>
											<td valign="top" style="padding:5px; border:2px solid #333;">'.$c->No_Faktur.'</td>
											<td valign="top" style="padding:5px; border:2px solid #333;">'.date_format(date_create($c->Tgl_Faktur),"d-m-Y").'</td>
											<td valign="top" style="padding:5px; border:2px solid #333;" align="right">'.number_format($c->Grandtotal,2,",",".").'</td>
											<td valign="top" style="padding:5px; border:2px solid #333;">'.$c->Nm_Plg.'</td>
											<td valign="top" style="padding:5px; border:2px solid #333;">'.$c->Alm_Plg.'</td>
										</tr>';

							$Grandtotal += ($c->Grandtotal);
							$simpanfaktur=$c->No_Faktur;
							// $mpdf->setFooter($footer);
							$no++;
						}
					}

				}

				$content .= '<tr>
								<td valign="top" style="font-size:12px; padding:5px; border:2px solid #333;" align="right" colspan="3"><b>Total : </b></td>
								<td valign="top" style="font-size:12px; padding:5px; border:2px solid #333;" align="right"><b>'.number_format($Grandtotal,2,",",".").'</b></td>
								<td valign="top" colspan="2"></td>
							</tr>';
				$content .= '</table></div>';

			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			echo $content;

			// $mpdf->SetHTMLHeader($header,'','1');
			// $mpdf->WriteHTML($content);
			// $mpdf->Output();

	}


}