<?php 
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class LaporanPembelianUmum extends MY_Controller 
	{
				
		public function __construct()
		{
			parent::__construct();
			$this->load->model('LaporanPembelianUmumModel');
			$this->load->library('excel');
		}

		public function index(){
			$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

			if($_SESSION["can_read"]==true){
				$data['supplier'] = $this->LaporanPembelianUmumModel->GetSupplier();
				$data['cabang'] = $this->LaporanPembelianUmumModel->GetCabang();
				$data['gudang'] = $this->LaporanPembelianUmumModel->GetGudang();
				$this->RenderView('LaporanPembelianUmumView',$data);
			}

		}

		public function pdf($from='',$until='',$supplier='',$cabang='',$gudang=''){
			$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

			if($_SESSION["can_read"]==true && !empty($from) && !empty($until) && !empty($supplier) && !empty($cabang) && !empty($gudang)){

				$data['from'] = $from;
				$data['until'] = $until;
				$data['supplier'] = $supplier;
				$data['cabang'] = $cabang;
				$data['gudang'] = $gudang;
				$list = $this->LaporanPembelianUmumModel->GetData($data);
				$list = json_decode($list,true);
	
				if($list['hasil']=='success'){

					//ini_set('max_execution_time', '30');
					ini_set("pcre.backtrack_limit", "10000000");
					set_time_limit(60);

					$mpdf = new \Mpdf\Mpdf(array(
						'mode' => '',
						'format' => 'Legal',
						'default_font_size' => 8,
						'default_font' => 'arial',
						'margin_left' => 10,
						'margin_right' => 10,
						'margin_top' => 35,
						'margin_bottom' => 10,
						'margin_header' => 10,
						'margin_footer' => 5,
						'orientation' => 'L'
					));

					$header ='<table width="100%">';
					$header .='<tr><td align="right">Printed : '.date('Y-m-d H-i-s').'</td></tr>';
					$header .='<tr><td align="center"><h1>PT. BHAKTI IDOLA TAMA</h1></td></tr>';
					$header .='<tr><td align="center"><h3>LAPORAN PEMBELIAN UMUM</h3></td></tr>';
					$header .='<tr><td align="center"><h5>PERIODE : '.$from.' S/D '.$until.'</h5></td></tr>';
					$header .='<tr><td><h5>Supplier : '.$supplier.'</h5></td></tr>';
					$header .='</table>';

					$content ='<table style="border-collapse:collapse;border-spacing:0;" align="center" border="1" width="100%">';
					$content .='<thead><tr>';
					$content .='<td width="30px" align="center">No</td>';
					$content .='<td width="6.6%" align="center">TGL PO</td>';
					$content .='<td width="6.6%" align="center">NO PO</td>';
					$content .='<td width="6.6%" align="center">NO PU</td>';
					$content .='<td width="6.6%" align="center">TGL PU</td>';
					$content .='<td width="6.6%" align="center">NO. FAKTUR PAJAK</td>';
					$content .='<td width="6.6%" align="center">NAMA BARANG</td>';
					$content .='<td width="6.6%" align="center">KETERANGAN</td>';
					$content .='<td width="6.6%" align="center">QTY</td>';
					$content .='<td width="7.7%" align="center">HARGA JUAL SATUAN(RP)</td>';
					$content .='<td width="7.7%" align="center">DISC SATUAN(RP)</td>';
					$content .='<td width="7.7%" align="center">HARGA NETT SATUAN(RP)</td>';
					$content .='<td width="7.7%" align="center">DASAR PENGENAAN PAJAK (RP)</td>';
					$content .='<td width="7.7%" align="center">PPN DAPAT DIKREDITKAN (RP)</td>';
					$content .='<td width="7.7%" align="center">DPP + PPN</td>';
					$content .='</tr></thead><tbody>';

					$no=1;
					$NoPU='';
					$KdSupplier='';
					$TotalDPP=0;
					$TotalTax=0;
					$TotalPU=0;

					$TotalDPPSupl=0;
					$TotalTaxSupl=0;
					$TotalPUSupl=0;


					$GrandTotalDPP=0;
					$GrandTotalTaxSupl=0;
					$GrandTotalPUSupl=0;

					foreach ($list['data'] as $key => $l) {



						if(!empty($NoPU) && $NoPU!==rtrim($l['No_PU'])){
							$content .='<tr><td colspan="10"></td><td colspan="2"><b>Total<b></td><td><b>'.number_format($TotalDPP,2,",",".").'</b></td><td><b>'.number_format($TotalTax,2,",",".").'</b></td><td><b>'.number_format($TotalPU,2,",",".").'</b></td></tr>';
							$NoPU=rtrim($l['No_PU']);


							$TotalDPP=0;
							$TotalTax=0;
							$TotalPU=0;
						}else{
							$NoPU=rtrim($l['No_PU']);
						}


						if(empty($KdSupplier)){
							$content .='<tr><td colspan="15"><b>'.rtrim($l['Nm_Supl']).'</b></td></tr>';
						}

						if(!empty($KdSupplier) && $KdSupplier!==rtrim($l['Kd_Supl'])){
							
							$content .='<tr><td colspan="10"></td><td colspan="2"><b>Grand Total /Supplier</b></td><td><b>'.number_format($TotalDPPSupl,2,",",".").'</b></td><td><b>'.number_format($TotalTaxSupl,2,",",".").'</b></td><td><b>'.number_format($TotalPUSupl,2,",",".").'</b></td></tr>';
							$KdSupplier=rtrim($l['Kd_Supl']);

							$TotalDPPSupl=0;
							$TotalTaxSupl=0;
							$TotalPUSupl=0;


							$content .='<tr><td colspan="15"><b>'.rtrim($l['Nm_Supl']).'</b></td></tr>';
							
						}else{
							$KdSupplier=rtrim($l['Kd_Supl']);
						}




						$harga_disc = ($l['Harga']*$l['DiscPersen'])/100;
						$harga_net = $l['Harga']-$harga_disc;
						$harga_DPP = ($l['Harga']*(100-$harga_disc)/100)*$l['QTY'];
						$harga_tax = ($harga_DPP*$l['TarifPPN'])/100;
						$total = $harga_DPP+$harga_tax;

						$TotalDPP = round($TotalDPP+$harga_DPP);
						$TotalTax = floor($TotalTax+$harga_tax);
						$TotalPU = $TotalDPP+$TotalTax;

						$TotalDPPSupl=round($TotalDPPSupl+$harga_DPP);
						$TotalTaxSupl=floor($TotalTaxSupl+$harga_tax);
						$TotalPUSupl=$TotalDPPSupl+$TotalTaxSupl;


						$GrandTotalDPP=round($GrandTotalDPP+$harga_DPP);
						$GrandTotalTaxSupl=floor($GrandTotalTaxSupl+$harga_tax);
						$GrandTotalPUSupl=$GrandTotalDPP+$GrandTotalTaxSupl;


						$content .='<tr>';
						$content .='<td align="center">'.$no.'.</td>';
						$content .='<td>'.$l['Tgl_PO'].'</td>';
						$content .='<td>'.rtrim($l['No_PO']).'</td>';
						$content .='<td>'.rtrim($l['No_PU']).'</td>';
						$content .='<td>'.$l['Tgl_PU'].'</td>';
						$content .='<td>'.$l['no_fakturP'].'</td>';
						$content .='<td>'.rtrim($l['Kd_Brg']).'</td>';
						$content .='<td>'.$l['deskripsi_item'].'</td>';
						$content .='<td align="center">'.$l['QTY'].'</td>';
						$content .='<td>'.number_format($l['Harga'],2,",",".").'</td>';
						$content .='<td>'.number_format($harga_disc,2,",",".").'</td>';
						$content .='<td>'.number_format($harga_net,2,",",".").'</td>';
						$content .='<td>'.number_format($harga_DPP,2,",",".").'</td>';
						$content .='<td>'.number_format($harga_tax,2,",",".").'</td>';
						$content .='<td>'.number_format($total,2,",",".").'</td>';
						$content .='</tr>';
						$no++;
					}



					$content .='<tr><td colspan="10"></td><td colspan="2"><b>Total<b></td><td><b>'.number_format($TotalDPP,2,",",".").'</b></td><td><b>'.number_format($TotalTax,2,",",".").'</b></td><td><b>'.number_format($TotalPU,2,",",".").'</b></td></tr>';
					
					$content .='<tr><td colspan="10"></td><td colspan="2"><b>Grand Total /Supplier</b></td><td><b>'.number_format($TotalDPPSupl,2,",",".").'</b></td><td><b>'.number_format($TotalTaxSupl,2,",",".").'</b></td><td><b>'.number_format($TotalPUSupl,2,",",".").'</b></td></tr>';


					$content .='<tr><td colspan="10"></td><td colspan="2"><b>Grand Total</b></td><td><b>'.number_format($GrandTotalDPP,2,",",".").'</b></td><td><b>'.number_format($GrandTotalTaxSupl,2,",",".").'</b></td><td><b>'.number_format($GrandTotalPUSupl,2,",",".").'</b></td></tr>';



					$content .='</tbody></table>';

					set_time_limit(60);
					$mpdf->SetHTMLHeader($header,'','1');
					$mpdf->WriteHTML($content);
					$mpdf->Output();

				}


			}else{
				redirect(site_url('LaporanPembelianUmum/?error=error'));
			}

		}

		public function excel($from='',$until='',$supplier='',$cabang='',$gudang=''){
			$this->ModuleModel->CheckAccess($this->uri->segment(1), '');

			if($_SESSION["can_read"]==true && !empty($from) && !empty($until) && !empty($supplier) && !empty($cabang) && !empty($gudang)){
				$data['from'] = $from;
				$data['until'] = $until;
				$data['supplier'] = $supplier;
				$data['cabang'] = $cabang;
				$data['gudang'] = $gudang;
				$list = $this->LaporanPembelianUmumModel->GetData($data);
				$list = json_decode($list,true);
	
				if($list['hasil']=='success'){

					$spreadsheet = new Spreadsheet();
					$sheet = $spreadsheet->getActiveSheet(0);
					$sheet->setTitle('LAPORAN PEMBELIAN UMUM');
					$sheet->setCellValue('A1', 'Printed : '.date('Y-m-d H-i-s'));
					$sheet->setCellValue('A2', 'PT. BHAKTI IDOLA TAMA');
					$sheet->setCellValue('A3', 'LAPORAN PEMBELIAN UMUM');
					$sheet->setCellValue('A4', 'PERIODE : '.$from.' S/D '.$until);
					$sheet->setCellValue('A6', 'SUPPLIER : '.$supplier);


					$sheet->mergeCells('A1:O1');
					$sheet->mergeCells('A2:O2');
					$sheet->mergeCells('A3:O3');
					$sheet->mergeCells('A4:O4');
					$sheet->mergeCells('A6:C6');
					$sheet->getStyle('A1')->getFont()->setSize(10);
					$sheet->getStyle('A2')->getFont()->setSize(20);
					$sheet->getStyle('A3')->getFont()->setSize(15);
					$sheet->getStyle('A4')->getFont()->setSize(13);
					$sheet->getStyle('A6')->getFont()->setSize(12);
					$sheet->getStyle('A1:O1')->getAlignment()->setHorizontal('right');
					$sheet->getStyle('A2:O2')->getAlignment()->setHorizontal('center');
					$sheet->getStyle('A3:O3')->getAlignment()->setHorizontal('center');
					$sheet->getStyle('A4:O4')->getAlignment()->setHorizontal('center');

					$currcol = 1;
					$currrow = 7;

					$spreadsheet->getActiveSheet()->getStyle('A7:O7')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('eaeaea');

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL PO');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO PO');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO PU');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL PU');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO. FAKTUR PAJAK');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA BARANG');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KETERANGAN');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'HARGA JUAL SATUAN(RP)');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DISC SATUAN(RP)');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'HARGA NETT SATUAN(RP)');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DASAR PENGENAAN PAJAK (RP)');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PPN DAPAT DIKREDITKAN (RP)');
					$currcol ++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DPP + PPN');
					$currcol ++;
					$currrow++;

					$no=1;
					$NoPU='';
					$KdSupplier='';
					$TotalDPP=0;
					$TotalTax=0;
					$TotalPU=0;

					$TotalDPPSupl=0;
					$TotalTaxSupl=0;
					$TotalPUSupl=0;


					$GrandTotalDPP=0;
					$GrandTotalTaxSupl=0;
					$GrandTotalPUSupl=0;

					foreach ($list['data'] as $key => $l) {

						if(!empty($NoPU) && $NoPU!==rtrim($l['No_PU'])){
							$sheet->getStyle('L'.$currrow)->getFont()->setBold(true);
							$sheet->getStyle('M'.$currrow)->getFont()->setBold(true);
							$sheet->getStyle('N'.$currrow)->getFont()->setBold(true);
							$sheet->getStyle('O'.$currrow)->getFont()->setBold(true);
							$sheet->setCellValue('L'.$currrow, 'Total ');
							$sheet->setCellValue('M'.$currrow, number_format($TotalDPP,2,",","."));
							$sheet->setCellValue('N'.$currrow, number_format($TotalTax,2,",","."));
							$sheet->setCellValue('O'.$currrow, number_format($TotalPU,2,",","."));
							$currrow++;
							$NoPU=rtrim($l['No_PU']);


							$TotalDPP=0;
							$TotalTax=0;
							$TotalPU=0;
						}else{
							$NoPU=rtrim($l['No_PU']);
						}


						if(empty($KdSupplier)){
							$currcol=1;
							$sheet->mergeCells('A'.$currrow.':C'.$currrow);
							$sheet->getStyle('A'.$currrow)->getFont()->setBold(true);
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['Nm_Supl']));
							$currrow++;
						}

						if(!empty($KdSupplier) && $KdSupplier!==rtrim($l['Kd_Supl'])){
							$sheet->getStyle('L'.$currrow)->getFont()->setBold(true);
							$sheet->getStyle('M'.$currrow)->getFont()->setBold(true);
							$sheet->getStyle('N'.$currrow)->getFont()->setBold(true);
							$sheet->getStyle('O'.$currrow)->getFont()->setBold(true);
							$sheet->setCellValue('L'.$currrow, 'Grand Total /Supplier ');
							$sheet->setCellValue('M'.$currrow, number_format($TotalDPPSupl,2,",","."));
							$sheet->setCellValue('N'.$currrow, number_format($TotalTaxSupl,2,",","."));
							$sheet->setCellValue('O'.$currrow, number_format($TotalPUSupl,2,",","."));
							$currrow=$currrow+2;
							$KdSupplier=rtrim($l['Kd_Supl']);

							$TotalDPPSupl=0;
							$TotalTaxSupl=0;
							$TotalPUSupl=0;

							$currcol=1;
							$sheet->mergeCells('A'.$currrow.':C'.$currrow);
							$sheet->getStyle('A'.$currrow)->getFont()->setBold(true);
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['Nm_Supl']));
							$currrow++;
						}else{
							$KdSupplier=rtrim($l['Kd_Supl']);
						}




						$harga_disc = ($l['Harga']*$l['DiscPersen'])/100;
						$harga_net = $l['Harga']-$harga_disc;
						$harga_DPP = ($l['Harga']*(100-$harga_disc)/100)*$l['QTY'];
						$harga_tax = ($harga_DPP*$l['TarifPPN'])/100;
						$total = $harga_DPP+$harga_tax;

						$TotalDPP = round($TotalDPP+$harga_DPP);
						$TotalTax = floor($TotalTax+$harga_tax);
						$TotalPU = $TotalDPP+$TotalTax;

						$TotalDPPSupl=round($TotalDPPSupl+$harga_DPP);
						$TotalTaxSupl=floor($TotalTaxSupl+$harga_tax);
						$TotalPUSupl=$TotalDPPSupl+$TotalTaxSupl;


						$GrandTotalDPP=round($GrandTotalDPP+$harga_DPP);
						$GrandTotalTaxSupl=floor($GrandTotalTaxSupl+$harga_tax);
						$GrandTotalPUSupl=$GrandTotalDPP+$GrandTotalTaxSupl;


						$currcol=1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['Tgl_PO']);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['No_PO']));
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['No_PU']));
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['Tgl_PU']);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['no_fakturP']);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['Kd_Brg']));
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['deskripsi_item']);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['QTY']);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['Harga'],2,",","."));
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($harga_disc,2,",","."));
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($harga_net,2,",","."));
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($harga_DPP,2,",","."));
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($harga_tax,2,",","."));
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,2,",","."));
						$currcol ++;
						$currrow++;
					$no++;
					}


					$cell_name = "'L".$currrow.":"."O".$currrow."'";
					$sheet->getStyle('L'.$currrow)->getFont()->setBold(true);
					$sheet->getStyle('M'.$currrow)->getFont()->setBold(true);
					$sheet->getStyle('N'.$currrow)->getFont()->setBold(true);
					$sheet->getStyle('O'.$currrow)->getFont()->setBold(true);
					$sheet->setCellValue('L'.$currrow, 'Total ');
					$sheet->setCellValue('M'.$currrow, number_format($TotalDPP,2,",","."));
					$sheet->setCellValue('N'.$currrow, number_format($TotalTax,2,",","."));
					$sheet->setCellValue('O'.$currrow, number_format($TotalPU,2,",","."));
					$currrow++;
					


					$sheet->getStyle('L'.$currrow)->getFont()->setBold(true);
					$sheet->getStyle('M'.$currrow)->getFont()->setBold(true);
					$sheet->getStyle('N'.$currrow)->getFont()->setBold(true);
					$sheet->getStyle('O'.$currrow)->getFont()->setBold(true);
					$sheet->setCellValue('L'.$currrow, 'Grand Total /Supplier ');
					$sheet->setCellValue('M'.$currrow, number_format($TotalDPPSupl,2,",","."));
					$sheet->setCellValue('N'.$currrow, number_format($TotalTaxSupl,2,",","."));
					$sheet->setCellValue('O'.$currrow, number_format($TotalPUSupl,2,",","."));
					$currrow=$currrow+2;



					$sheet->getStyle('L'.$currrow)->getFont()->setBold(true);
					$sheet->getStyle('M'.$currrow)->getFont()->setBold(true);
					$sheet->getStyle('N'.$currrow)->getFont()->setBold(true);
					$sheet->getStyle('O'.$currrow)->getFont()->setBold(true);
					$sheet->setCellValue('L'.$currrow, 'Grand Total ');
					$sheet->setCellValue('M'.$currrow, number_format($GrandTotalDPP,2,",","."));
					$sheet->setCellValue('N'.$currrow, number_format($GrandTotalTaxSupl,2,",","."));
					$sheet->setCellValue('O'.$currrow, number_format($GrandTotalPUSupl,2,",","."));
					$currrow=$currrow+2;



					for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
					    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
					}

					$filename='LAPORAN PEMBELIAN UMUM ['.date('Y-m-d H-i-s').']'; //save our workbook as this file name
					$writer = new Xlsx($spreadsheet);
					header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
					header('Cache-Control: max-age=0');
					ob_end_clean();
			        $writer->save('php://output');
			        exit();

			    }else{


			    }





			}else{
				redirect(site_url('LaporanPembelianUmum/?error=error'));
			}

		}

	}
?>