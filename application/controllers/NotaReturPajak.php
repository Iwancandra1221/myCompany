<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require 'vendor/autoload.php';
require 'vendor/setasign/fpdi/src/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NotaReturPajak extends MY_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('NotaReturPajakModel');
	}
	
	private function _postRequest($url,$data){

		$options = array(
		    'http' => array(
		   	 	'method' => 'POST',
		    	'content' => http_build_query($data),
		    	'header'  => 'Content-type: application/x-www-form-urlencoded',
			),
		    
		);
		$stream = stream_context_create($options);
		$getContent = file_get_contents($url, false, $stream);
		$result = json_decode($getContent,true);
		return $result;
	}
	public function index(){

			$data['wilayah'] = $this->NotaReturPajakModel->wilayah();
			$data['TipeFaktur'] = $this->NotaReturPajakModel->TipeFaktur();
			
			// print_r($data['TipeFaktur']);
			// die;

			$this->RenderView('NotaReturPajakView',$data);
	}

	public function SearchFaktur(){
		$search = $this->input->post('search');
		$hasil= $this->NotaReturPajakModel->search($search);
		printf($hasil);
	}

	public function pdf(){
			//ini_set('max_execution_time', '300');
			ini_set("pcre.backtrack_limit", "5000000");
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
				'margin_footer' => 0,
				'orientation' => 'L'
			));

			$cetak 			= $this->input->get('cetak');
			$status 		= $this->input->get('status');
			$wilayah 		= $this->input->get('wilayah');
			$periode_dari 	= str_replace("/", "", $this->input->get('periode_dari'));
			$periode_sampai = str_replace("/", "", $this->input->get('periode_sampai'));
			$kategori 		= $this->input->get('kategori');
			$TipeFaktur 	= $this->input->get('TipeFaktur');


			$get = 	'&api=APITES'.
					'&cetak='.$cetak.
					'&status='.$status.
					'&wilayah='.$wilayah.
					'&periode_dari='.$periode_dari.
					'&periode_sampai='.$periode_sampai.
					'&kategori='.$kategori.
					'&TipeFaktur='.$TipeFaktur;

			$url = $_SESSION["conn"]->AlamatWebService.API_BKT."/NotaReturPajak/report?".$get;

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

			// print_r($resultStr);
			// die;

			$curDate = date("d-F-Y H:i:s");
			$header = '<table border="0" style="width:297mm; font-size:15px;">
					<tr>
						<td>
							<b>
								REKAP NOTA RETUR PAJAK
							</b>
						</td>
						<td align="right" style="font-size:12px;">
							'.$curDate.'
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<b>
								PT. BHAKTI IDOLA TAMA
							</b>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<hr>
						</td>
					</tr>
				</table>';

				$content = '
				<table>
					<tr>
						<td colspan="2" style="font-size:13px;">
							PERIODE : '.$this->input->get('periode_dari').' - '.$this->input->get('periode_sampai').'
						</td>
					</tr>
					<tr>
						<td colspan="2" style="font-size:13px;">
							KATEGORI BARANG : '.strtoupper($kategori).'
						</td>
					</tr>
					<tr>
						<td colspan="2" style="font-size:13px;">
							WILAYAH : '.strtoupper($wilayah).'
						</td>
					</tr>
					<tr>
						<td colspan="2" style="font-size:13px;">
							PARTNER TYPE : '.$status.'
						</td>
					</tr>
				</table>';			

				$cust = '';
				$no = 0;
				foreach ($resultStr as $key => $c) {

					if($c->Wilayah!=$cust && $no!=0){
						$content .= '</table>';
						$no = 0;
					}

					if($no==0){
						$cust = $cust=$c->Wilayah;
						$content .= '<table style="width:297mm">
									<tr>
										<td colspan="12">
											<br><br><b>'.$c->Wilayah.'</b>
										</td>
									</tr>
									<tr>
										<th style="text-align: left; width: 8.3%; font-size: 12px;">Tanggal</th>
										<th style="text-align: left; width: 8.3%; font-size: 12px;">No Retur Pajak</th>
										<th style="text-align: left; width: 8.3%; font-size: 12px;">Atas Faktur Pajak</th>
										<th style="text-align: left; width: 8.3%; font-size: 12px;">Tgl Faktur Pajak</th>
										<th style="text-align: left; width: 8.3%; font-size: 12px;">NPWP</th>
										<th style="text-align: left; width: 8.3%; font-size: 12px;">Nama</th>
										<th style="text-align: left; width: 8.3%; font-size: 12px;">DPP</th>
										<th style="text-align: left; width: 8.3%; font-size: 12px;">PPN</th>
										<th style="text-align: left; width: 8.3%; font-size: 12px;">Total</th>
										<th style="text-align: left; width: 8.3%; font-size: 12px;">No BPRPJ</th>
										<th style="text-align: left; width: 8.3%; font-size: 12px;">No BPB</th>
										<th style="text-align: left; width: 8.3%; font-size: 12px;">Kat Brg</th>
									</tr>';
					}

						// if (!empty($c->nm_pajak)){
		                //     $NmPajak = $c->nm_pajak;
						// }
		                // else{
		                //     $NmPajak = $c->Nm_Plg;
		                // }
		                
		                // if (empty($c->NPWP)){
		                //      if (empty($c->NPWP_W)){
		                //         $NPWP = $c->NPWP_D;
		                //     }else{
		                //         $NPWP = $c->NPWP_W;
		                //     }
						// }else{
		                //     $NPWP = $c->NPWP;
		                // }

		                // if(!empty($c->No_FakturP_NR)){
		                // 	$No_FakturP_NR = $c->No_FakturP_NR;
		                // 	$Tgl_FakturP_NR = $c->Tgl_FakturP_NR;
		                // }else{
		                // 	$No_FakturP_NR=$c->No_FakturP;
		                // 	$Tgl_FakturP_NR = $c->Tgl_FakturP;
		                // }

		                if($cetak=='laporan'){
		                	$GrandTotal = $c->GrandTotal;
		                }else{
		                	$GrandTotal = $c->Grandtotal;
		                }

		                // if(empty($c->Total_PPN)){
		                // 	$PPN = $c->PPN;
		                // }else{
		                // 	$PPN = $c->Total_PPN;
		                // }

						// No_FakturP, Tgl_FakturP, Tipe_Faktur, Flag, No_Faktur, Tgl_Faktur, Kd_Trn, 
			// 				Partner_Type, Wilayah, Kd_Plg, Kd_Wil, Alm_Kirim, DPP, PPN, GrandTotal, 
			// 				Kategori_Brg, Tipe_PPN, Nm_Pajak, Alm_Pajak, NPWP, Tgl_Penyerahan, No_Ref,
			// 				No_FakturP_Jual , Tgl_FakturP_Jual

		                $total = $c->DPP + $c->PPN;

						$content .='<tr>
										<td>'.date_format(date_create($c->Tgl_Faktur),"d-m-Y").'</td>
										<td>'.trim($c->No_FakturP).'</td>
										<td>'.trim($c->Nm_Pajak).'</td>
										<td>'.date_format(date_create($c->Tgl_FakturP),"d-m-Y").'</td>
										<td>'.trim($c->NPWP).'</td>
										<td>'.trim($c->Kd_Plg).'</td>
										<td>'.number_format($c->DPP,2,",",".").'</td>
										<td>'.number_format($c->PPN,2,",",".").'</td>
										<td>'.number_format($total,2,",",".").'</td>
										<td>'.trim($c->No_Ref).'</td>
										<td>'.trim(str_replace("BPBKTP", "", str_replace("BPBKTS", "", $c->No_Faktur))).'</td>
										<td>'.$c->Kategori_Brg.'</td>
									</tr>';
					$no++;

				}

				if(count($resultStr)){
					$content .= '</table>';
				}

			$mpdf->SetHTMLHeader($header,'','1');
			$mpdf->WriteHTML($content);
			$mpdf->Output();

	}

	public function excel(){
			//ini_set("max_execution_time", "500");
			ini_set("pcre.backtrack_limit", "1000000");
			set_time_limit(60);



			$cetak 			= $this->input->get('cetak');
			$status 		= $this->input->get('status');
			$wilayah 		= $this->input->get('wilayah');
			$periode_dari 	= str_replace("/", "", $this->input->get('periode_dari'));
			$periode_sampai = str_replace("/", "", $this->input->get('periode_sampai'));
			$kategori 		= $this->input->get('kategori');
			$TipeFaktur 	= $this->input->get('TipeFaktur');


			$get = 	'&api=APITES'.
					'&cetak='.$cetak.
					'&status='.$status.
					'&wilayah='.$wilayah.
					'&periode_dari='.$periode_dari.
					'&periode_sampai='.$periode_sampai.
					'&kategori='.$kategori.
					'&TipeFaktur='.$TipeFaktur;

			$url = $_SESSION["conn"]->AlamatWebService.API_BKT."/NotaReturPajak/report?".$get;
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $get);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Content-Type:application/json',
			));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$resultStr = json_decode(curl_exec($ch));

			$curDate = date("d-F-Y H:i:s");

			  	$page_title = "REKAP NOTA RETUR PAJAK";

				$spreadsheet = new Spreadsheet();
				$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
				$sheet = $spreadsheet->getActiveSheet(0);

				$sheet->setTitle('REKAP NOTA RETUR PAJAK');

				$sheet->mergeCells('A1:J1');
				$sheet->mergeCells('K1:L1');

				$sheet->mergeCells('A2:J2');
				$sheet->mergeCells('A3:J3');
				$sheet->mergeCells('A4:J4');
				$sheet->mergeCells('A5:J5');
				$sheet->mergeCells('A6:J6');

				$sheet->setCellValueByColumnAndRow(1,1, "REKAP NOTA RETUR PAJAK");
				$sheet->setCellValueByColumnAndRow(11,1, "Tanggal : ".$curDate);
				$sheet->getStyle('A1:J1')->getFont()->setSize(12);
				$sheet->getStyle('A1:J1')->getFont()->setBold(true);

				$sheet->setCellValueByColumnAndRow(1,2, "PT. BHAKTI IDOLA TAMA");
				$sheet->getStyle('A2:J2')->getFont()->setSize(12);
				$sheet->getStyle('A2:J2')->getFont()->setBold(true);

				$sheet->setCellValueByColumnAndRow(1,3, "Periode : ".$this->input->get('periode_dari')." - ".$this->input->get('periode_sampai'));
				$sheet->setCellValueByColumnAndRow(1,4, "Kategori Barang : ".strtoupper($kategori));
				$sheet->setCellValueByColumnAndRow(1,5, "Wilayah : ".strtoupper($wilayah));
				$sheet->setCellValueByColumnAndRow(1,6, "Partner Type : ".strtoupper($status));


				$cust = '';
				$no = 0;
				$jum_no=0;
				$currrow = 6;

				$alltotaldpp=0;
				$alltotalppn=0;
				$alltotal=0;

				$jum_data=count($resultStr);
				foreach ($resultStr as $key => $c) {

					$currcol = 1;

					if($c->Wilayah!=$cust && $no!=0){

						$bold_data = 'F'.$currrow.':'.'I'.$currrow;
						$sheet->getStyle($bold_data)->getFont()->setBold(true);

						$sheet->setCellValueByColumnAndRow(6, $currrow, 'TOTAL WILAYAH '.$cust);
						$sheet->setCellValueByColumnAndRow(7, $currrow, number_format($alltotaldpp,2,",","."));
						$sheet->setCellValueByColumnAndRow(8, $currrow, number_format($alltotalppn,2,",","."));
						$sheet->setCellValueByColumnAndRow(9, $currrow, number_format($alltotal,2,",","."));
						$cust=$c->Wilayah;
						$no = 0;
						$alltotaldpp=0;
						$alltotalppn=0;
						$alltotal=0;
						$currrow++;
					}

					if($no==0){

						$currrow=$currrow+2;

						$bold_data = 'A'.$currrow;
						$sheet->getStyle($bold_data)->getFont()->setBold(true);
						$sheet->setCellValueByColumnAndRow(1,$currrow, $c->Wilayah);
						$currrow++;

						$bold_data = 'A'.$currrow.':'.'L'.$currrow;
						$sheet->getStyle($bold_data)->getFont()->setBold(true);

						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tanggal');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Retur Pajak');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Atas Faktur Pajak');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tgl Faktur Pajak');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NPWP');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DPP');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PPN');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No BPRPJ');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No BPB');
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kat Brg');
						$currcol=1;
						$currrow++;
					}

						// if (!empty($c->nm_pajak)){
		                //     $NmPajak = $c->nm_pajak;
						// }
		                // else{
		                //     $NmPajak = $c->Nm_Plg;
		                // }
		                
		                // if (empty($c->NPWP)){
		                //      if (empty($c->NPWP_W)){
		                //         $NPWP = $c->NPWP_D;
		                //     }else{
		                //         $NPWP = $c->NPWP_W;
		                //     }
						// }else{
		                //     $NPWP = $c->NPWP;
		                // }

		                // if(!empty($c->No_FakturP_NR)){
		                // 	$No_FakturP_NR = $c->No_FakturP_NR;
		                // 	$Tgl_FakturP_NR = $c->Tgl_FakturP_NR;
		                // }else{
		                // 	$No_FakturP_NR=$c->No_FakturP;
		                // 	$Tgl_FakturP_NR = $c->Tgl_FakturP;
		                // }

		                if($cetak=='laporan'){
		                	$GrandTotal = $c->GrandTotal;
		                }else{
		                	$GrandTotal = $c->GrandTotal;
		                }

		                // if(empty($c->Total_PPN)){
		                // 	$PPN = $c->PPN;
		                // }else{
		                // 	$PPN = $c->Total_PPN;
		                // }

		                $total = $c->DPP + $c->PPN;

						$alltotaldpp = $alltotaldpp + $c->DPP;
						$alltotalppn = $alltotalppn + $c->PPN;
						$alltotal = $alltotal + $total;;

						// No_FakturP, Tgl_FakturP, Tipe_Faktur, Flag, No_Faktur, Tgl_Faktur, Kd_Trn, 
			// 				Partner_Type, Wilayah, Kd_Plg, Kd_Wil, Alm_Kirim, DPP, PPN, GrandTotal, 
			// 				Kategori_Brg, Tipe_PPN, Nm_Pajak, Alm_Pajak, NPWP, Tgl_Penyerahan, No_Ref,
			// 				No_FakturP_Jual , Tgl_FakturP_Jual

		            	$sheet->setCellValueByColumnAndRow($currcol, $currrow, date_format(date_create($c->Tgl_Faktur),"d-m-Y"));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $c->No_FakturP);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $c->Nm_Pajak);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, date_format(date_create($c->Tgl_FakturP),"d-m-Y"));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($c->NPWP));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($c->Kd_Plg));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($c->DPP,2,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($c->PPN,2,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,2,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim($c->No_Ref));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, trim(str_replace("BPBKTP", "", str_replace("BPBKTS", "", $c->No_Faktur))));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $c->Kategori_Brg);

						$currrow++;
						$no++;

						$jum_no++;


					if($jum_no==$jum_data){

						$bold_data = 'F'.$currrow.':'.'I'.$currrow;
						$sheet->getStyle($bold_data)->getFont()->setBold(true);
						$sheet->setCellValueByColumnAndRow(6, $currrow, 'TOTAL WILAYAH '.$c->Wilayah);
						$sheet->setCellValueByColumnAndRow(7, $currrow, number_format($alltotaldpp,2,",","."));
						$sheet->setCellValueByColumnAndRow(8, $currrow, number_format($alltotalppn,2,",","."));
						$sheet->setCellValueByColumnAndRow(9, $currrow, number_format($alltotal,2,",","."));
						$cust=$c->Wilayah;
						$no = 0;
						$alltotaldpp=0;
						$alltotalppn=0;
						$alltotal=0;
						$currrow++;
					}

				}

				$rand = rand(100,999);

				$filename='Rekap_Nota_Retur_Pajak_'.$rand;
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
		        $writer->save('php://output');
		        exit();
	}
}