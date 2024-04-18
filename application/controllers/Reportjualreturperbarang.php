<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

	class Reportjualreturperbarang extends MY_Controller
	{
		function __construct()
		{
			parent::__construct();
			$this->load->model('ReportjualreturperbarangModel');
		}
 
		public function index($error=''){
			$data['pergroup_item_fokus'] = $this->ReportjualreturperbarangModel->pergroup_item_fokus();
			$data['tipe_faktur'] = $this->ReportjualreturperbarangModel->tipe_faktur();
			$data['wilayah'] = $this->ReportjualreturperbarangModel->wilayah();
			$data['ParentDiv'] = $this->ReportjualreturperbarangModel->ParentDiv();
			$data['Divisi'] = $this->ReportjualreturperbarangModel->Divisi();
			$data['error'] = $error;
 
			$paramsLog = array();   
		 	$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		  	$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		  	$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		  	$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$paramsLog['Module']="REPORT JUAL RETUR PERBARANG"; 
		 	$paramsLog['TrxID'] = date("YmdHis");
			$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU REPORT JUAL RETUR PERBARANG";
		 	$paramsLog['Remarks']="SUCCESS";
		  	$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($paramsLog); 

			$this->RenderView('ReportjualreturperbarangView',$data);
		}

		public function Dealer(){
			$wilayah = urldecode($this->input->post('wilayah'));
			$dealer = $this->ReportjualreturperbarangModel->Dealer($wilayah);
			print_r(json_encode($dealer));
		}

		public function pdf($code_print='615'){
			if($code_print=='615'){
				if($this->input->get('dealer_chk')=='Y'){
					$this->pdf615_dealer($this->input->get());
				}else{
					$this->pdf615($this->input->get());
				}
			}else{
				if($this->input->get('dealer_chk')=='Y'){
					$this->pdf616_dealer($this->input->get());
				}else{
					$this->pdf616($this->input->get());
				}
			}
		}

		public function excel($code_print='615'){
			if($code_print=='615'){
				if($this->input->get('dealer_chk')=='Y'){
					$this->excel615_dealer($this->input->get());
				}else{
					$this->excel615($this->input->get());
				}
			}else{
				if($this->input->get('dealer_chk')=='Y'){
					$this->excel616_dealer($this->input->get());
				}else{
					$this->excel616($this->input->get());
				}
			}
		}

		public function pdf615_dealer($data=''){
			//ini_set('max_execution_time', '1500');
			ini_set("pcre.backtrack_limit", "10000000");
			set_time_limit(60);


			$paramsLog = array();   
		 	$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		  	$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		  	$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		  	$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$paramsLog['Module']="REPORT JUAL RETUR PERBARANG"; 
		 	$paramsLog['TrxID'] = date("YmdHis");
			$paramsLog['Description']=$_SESSION["logged_in"]["username"]." PROSES PDF REPORT 615 Dengan Dealer";
		 	$paramsLog['Remarks']="SUCCESS";
		  	$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($paramsLog); 


			$list = $this->ReportjualreturperbarangModel->list615($data);
			$list = json_decode($list,true);

			if($list['hasil']=='success'){
			
				$paramsLog['Remarks']="SUCCESS";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);

				$mpdf = new \Mpdf\Mpdf(array(
					'mode' => '',
					'format' => 'A4',
					'default_font_size' => 8,
					'default_font' => 'arial',
					'margin_left' => 10,
					'margin_right' => 10,
					'margin_top' => 33,
					'margin_bottom' => 10,
					'margin_header' => 10,
					'margin_footer' => 5,
					'orientation' => 'P'
				));

				if($data['produk']=='ALL'){
					$type_product='PRODUCT & SPAREPART';
				}else{
					$type_product=$data['produk'];
				}

				$header='<table width="100%">';
				$header.='<tr>';
				$header.='<td colspan="3" align="center"><h2><b>JUAL - RETUR PER DEALER PER JENIS BARANG</b></h2></td>';
				$header.='</tr><tr><td colspan="3" style="padding:5px"></td></tr><tr>';
				$header.='<td>PERIODE : '.date_format(date_create($data['from']),'d-M-Y').' S/D '.date_format(date_create($data['until']),'d-M-Y').'</td>';
				$header.='<td></td>';
				$header.='<td align="center" width="200px" style="border:thin solid #000; padding:5px">'.$type_product.'</td>';
				$header.='</tr><tr>';
				$header.='<td colspan="3"><h3>'.$data['wilayah'].'</h3></td>';
				$header.='</tr>';
				$header.='</table>';

				$content='';

				$divisi='';
				$merk='';
				$kdpelanggan='';
				$nmpelanggan='';



				$total_qty_jual=0;
				$total_qty_retur=0;
				$total_qty=0;
				$total_jual=0;
				$total_retur=0;
				$total=0;


				$total_global_qty_jual=0;
				$total_global_qty_retur=0;
				$total_global_qty=0;
				$total_global_jual=0;
				$total_global_retur=0;
				$total_global=0;

				$content.='<table width="100%">';
				foreach ($list['data'] as $key => $l) {
					

					if(($divisi!==rtrim($l['DIVISI']) && $merk!==rtrim($l['MERK'])) || $kdpelanggan!==rtrim($l['KD_PLG'])){


						if($divisi!=='' && $merk!=='' && $kdpelanggan!==''){
							$content.='<tr><td colspan="7" style="border-top:thin solid #000;"></td></tr><tr>';
							$content.='<td><b>Total Merk '.$merk.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty.'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td>';
							$content.='</tr><tr><td colspan="7" style="border-top:thin solid #000;"></td></tr>';
							$content.='<tr><td><b>'.$nmpelanggan.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty.'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td></tr>';
							$content.='<tr></tr>';
							$content.='<tr><td><b>Total Divisi '.$divisi.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty.'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td></tr>';
							$content.='<tr><td colspan="7" style="padding:10px;"></td></tr>';
						}


						$content.='<tr>';
						$content.='<td colspan="4"><b>'.$l['NM_PLG'].'</b></td>';
						$content.='<td colspan="3" align="right"><b>'.rtrim($l['KD_PLG']).'</b></td>';
						$content.='</tr>';

						$content.='<tr>';
						$content.='<td><b><i>Divisi :</i> '.$l['DIVISI'].'</b></td>';
						$content.='<td colspan="6"><b><i>Merk :</i> '.$l['MERK'].'</b></td>';
						$content.='</tr>';

						$total_qty_jual=0;
						$total_qty_retur=0;
						$total_qty=0;
						$total_jual=0;
						$total_retur=0;
						$total=0;

						$divisi=rtrim($l['DIVISI']);
						$merk=rtrim($l['MERK']);
						$kdpelanggan=rtrim($l['KD_PLG']);
						$nmpelanggan=$l['NM_PLG'];

						$content.='<thead><tr>';
						$content.='<td width="25%">JENIS BARANG</td>';
						$content.='<td width="10%" align="right">QTY JUAL</td>';
						$content.='<td width="10%" align="right">QTY RETUR</td>';
						$content.='<td width="10%" align="right">TOTAL QTY</td>';
						$content.='<td width="15%" align="right">TOTAL JUAL</td>';
						$content.='<td width="15%" align="right">TOTAL RETUR</td>';
						$content.='<td width="15%" align="right">TOTAL</td>';
						$content.='</tr><tr><td colspan="7" style="border-top:thin solid #000;"></td></tr></thead><tbody>';

					}


						$total_qty_jual=$total_qty_jual+$l['QTY_JUAL'];
						$total_qty_retur=$total_qty_retur+$l['QTY_RETUR'];
						$total_qty=$total_qty+$l['QTY_TOTAL'];
						$total_jual=$total_jual+$l['TOTAL_JUAL'];
						$total_retur=$total_retur+$l['TOTAL_RETUR'];
						$total=$total+$l['Total'];


						$total_global_qty_jual=$total_global_qty_jual+$l['QTY_JUAL'];
						$total_global_qty_retur=$total_global_qty_retur+$l['QTY_RETUR'];
						$total_global_qty=$total_global_qty+$l['QTY_TOTAL'];
						$total_global_jual=$total_global_jual+$l['TOTAL_JUAL'];
						$total_global_retur=$total_global_retur+$l['TOTAL_RETUR'];
						$total_global=$total_global+$l['Total'];


						$content.='<tr>';
						$content.='<td>'.$l['JNS_BRG'].'</td>';
						$content.='<td align="right">'.$l['QTY_JUAL'].'</td>';
						$content.='<td align="right">'.$l['QTY_RETUR'].'</td>';
						$content.='<td align="right">'.$l['QTY_TOTAL'].'</td>';
						$content.='<td align="right">'.number_format($l['TOTAL_JUAL'],0,",",".").'</td>';
						$content.='<td align="right">'.number_format($l['TOTAL_RETUR'],0,",",".").'</td>';
						$content.='<td align="right">'.number_format($l['Total'],0,",",".").'</td>';
						$content.='</tr>';	

					
				}

				$content.='<tr><td colspan="7" style="border-top:thin solid #000;"></td></tr><tr>';
				$content.='<td><b>Total Merk '.$merk.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty.'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td>';
				$content.='</tr><tr><td colspan="7" style="border-top:thin solid #000;"></td></tr>';
				$content.='<tr><td><b>'.$nmpelanggan.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty.'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td></tr>';
				$content.='<tr></tr>';
				$content.='<tr><td><b>Total Divisi '.$divisi.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty.'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td></tr>';
				$content.='<tr><td colspan="7" style="padding:10px;"></td></tr>';


				$content.='<tr><td colspan="7" style="border-top:thin solid #000;"></td></tr><tr>';
				$content.='<td><b>GRANDTOTAL</b></td>';
				$content.='<td align="right"><b>'.$total_global_qty_jual.'</b></td>';
				$content.='<td align="right"><b>'.$total_global_qty_retur.'</b></td>';
				$content.='<td align="right"><b>'.$total_global_qty.'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_global_jual,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_global_retur,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_global,0,",",".").'</b></td>';
				$content.='</tr>';

				$content.='</tbody></table>';

				set_time_limit(60);

				$mpdf->SetHTMLHeader($header,'','1');
				$mpdf->WriteHTML($content);
				$mpdf->setFooter('<table border="0" width="100%"><tr><td>'.date('d-m-Y H-i-s').'</td><td align="right">Page {PAGENO} of {nb}</td></tr></table>');
				$mpdf->Output();

				

			}else{ 
				$paramsLog['Remarks']="FAILED - DATA TIDAK DITEMUKAN";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);

				$this->index('error');

			}
		} 

		public function pdf615($data=''){
 
			$paramsLog = array();   
		 	$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		  	$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		  	$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		  	$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$paramsLog['Module']="REPORT JUAL RETUR PERBARANG"; 
		 	$paramsLog['TrxID'] = date("YmdHis");
			$paramsLog['Description']=$_SESSION["logged_in"]["username"]." PROSES PDF REPORT 615";
		 	$paramsLog['Remarks']="SUCCESS";
		  	$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($paramsLog); 

			//ini_set('max_execution_time', '1500');
			ini_set("pcre.backtrack_limit", "10000000");
			set_time_limit(60);


			$list = $this->ReportjualreturperbarangModel->list615($data);
			$list = json_decode($list,true);

			if($list['hasil']=='success'){

				$paramsLog['Remarks']="SUCCESS";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);
			
				$mpdf = new \Mpdf\Mpdf(array(
					'mode' => '',
					'format' => 'A4',
					'default_font_size' => 8,
					'default_font' => 'arial',
					'margin_left' => 10,
					'margin_right' => 10,
					'margin_top' => 33,
					'margin_bottom' => 10,
					'margin_header' => 10,
					'margin_footer' => 5,
					'orientation' => 'P'
				));

				if($data['produk']=='ALL'){
					$type_product='PRODUCT & SPAREPART';
				}else{
					$type_product=$data['produk'];
				}

				$header='<table width="100%">';
				$header.='<tr>';
				$header.='<td colspan="3" align="center"><h2><b>JUAL - RETUR PER DIVISI PER JENIS BARANG</b></h2></td>';
				$header.='</tr><tr><td colspan="3" style="padding:5px"></td></tr><tr>';
				$header.='<td>PERIODE : '.date_format(date_create($data['from']),'d-M-Y').' S/D '.date_format(date_create($data['until']),'d-M-Y').'</td>';
				$header.='<td></td>';
				$header.='<td align="center" width="200px" style="border:thin solid #000; padding:5px">'.$type_product.'</td>';
				$header.='</tr><tr>';
				$header.='<td colspan="3"><h3>'.$data['wilayah'].'</h3></td>';
				$header.='</tr>';
				$header.='</table>';

				$content='';

				$divisi='';
				$merk='';



				$total_qty_jual=0;
				$total_qty_retur=0;
				$total_qty=0;
				$total_jual=0;
				$total_retur=0;
				$total=0;


				$total_global_qty_jual=0;
				$total_global_qty_retur=0;
				$total_global_qty=0;
				$total_global_jual=0;
				$total_global_retur=0;
				$total_global=0;

				$content.='<table width="100%">';
				foreach ($list['data'] as $key => $l) {
					

					if($divisi!==rtrim($l['DIVISI']) && $merk!==rtrim($l['MERK'])){


						if($divisi!=='' && $merk!==''){
							$content.='<tr><td colspan="7" style="border-top:thin solid #000;"></td></tr><tr>';
							$content.='<td><b>Total Merk '.$merk.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty.'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td>';
							$content.='</tr><tr><td colspan="7" style="border-top:thin solid #000;"></td></tr>';
							$content.='<tr><td><b>Total Divisi '.$divisi.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty.'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td></tr>';
							$content.='<tr><td colspan="7" style="padding:10px;"></td></tr>';
						}


						$content.='<tr>';
						$content.='<td><b><i>Divisi :</i> '.$l['DIVISI'].'</b></td>';
						$content.='<td colspan="6"><b><i>Merk :</i> '.$l['MERK'].'</b></td>';
						$content.='</tr>';

						$total_qty_jual=0;
						$total_qty_retur=0;
						$total_qty=0;
						$total_jual=0;
						$total_retur=0;
						$total=0;

						$divisi=rtrim($l['DIVISI']);
						$merk=rtrim($l['MERK']);

						$content.='<thead><tr>';
						$content.='<td width="25%">JENIS BARANG</td>';
						$content.='<td width="10%" align="right">QTY JUAL</td>';
						$content.='<td width="10%" align="right">QTY RETUR</td>';
						$content.='<td width="10%" align="right">TOTAL QTY</td>';
						$content.='<td width="15%" align="right">TOTAL JUAL</td>';
						$content.='<td width="15%" align="right">TOTAL RETUR</td>';
						$content.='<td width="15%" align="right">TOTAL</td>';
						$content.='</tr><tr><td colspan="7" style="border-top:thin solid #000;"></td></tr></thead><tbody>';

					}


						$total_qty_jual=$total_qty_jual+$l['QTY_JUAL'];
						$total_qty_retur=$total_qty_retur+$l['QTY_RETUR'];
						$total_qty=$total_qty+$l['QTY_TOTAL'];
						$total_jual=$total_jual+$l['TOTAL_JUAL'];
						$total_retur=$total_retur+$l['TOTAL_RETUR'];
						$total=$total+$l['TOTAL'];


						$total_global_qty_jual=$total_global_qty_jual+$l['QTY_JUAL'];
						$total_global_qty_retur=$total_global_qty_retur+$l['QTY_RETUR'];
						$total_global_qty=$total_global_qty+$l['QTY_TOTAL'];
						$total_global_jual=$total_global_jual+$l['TOTAL_JUAL'];
						$total_global_retur=$total_global_retur+$l['TOTAL_RETUR'];
						$total_global=$total_global+$l['TOTAL'];


						$content.='<tr>';
						$content.='<td>'.$l['JNS_BRG'].'</td>';
						$content.='<td align="right">'.$l['QTY_JUAL'].'</td>';
						$content.='<td align="right">'.$l['QTY_RETUR'].'</td>';
						$content.='<td align="right">'.$l['QTY_TOTAL'].'</td>';
						$content.='<td align="right">'.number_format($l['TOTAL_JUAL'],0,",",".").'</td>';
						$content.='<td align="right">'.number_format($l['TOTAL_RETUR'],0,",",".").'</td>';
						$content.='<td align="right">'.number_format($l['TOTAL'],0,",",".").'</td>';
						$content.='</tr>';	

					
				}

				$content.='<tr><td colspan="7" style="border-top:thin solid #000;"></td></tr><tr>';
				$content.='<td><b>Total Merk '.$merk.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty.'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td>';
				$content.='</tr><tr><td colspan="7" style="border-top:thin solid #000;"></td></tr>';
				$content.='<tr><td><b>Total Divisi '.$divisi.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty.'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td></tr>';
				$content.='<tr><td colspan="7" style="padding:10px;"></td></tr>';


				$content.='<tr><td colspan="7" style="border-top:thin solid #000;"></td></tr><tr>';
				$content.='<td><b>GRANDTOTAL</b></td>';
				$content.='<td align="right"><b>'.$total_global_qty_jual.'</b></td>';
				$content.='<td align="right"><b>'.$total_global_qty_retur.'</b></td>';
				$content.='<td align="right"><b>'.$total_global_qty.'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_global_jual,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_global_retur,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_global,0,",",".").'</b></td>';
				$content.='</tr>';

				$content.='</tbody></table>';

				set_time_limit(60);
				$mpdf->SetHTMLHeader($header,'','1');
				$mpdf->WriteHTML($content);
				$mpdf->setFooter('<table border="0" width="100%"><tr><td>'.date('d-m-Y H-i-s').'</td><td align="right">Page {PAGENO} of {nb}</td></tr></table>');
				$mpdf->Output();

			}else{ 
				$paramsLog['Remarks']="FAILED - DATA TIDAK DITEMUKAN";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);
				$this->index('error');
				
			}
		}
 
		public function pdf616_dealer($data=''){
			//ini_set('max_execution_time', '1500');
			ini_set("pcre.backtrack_limit", "10000000");
			set_time_limit(60);


			$paramsLog = array();   
		 	$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		  	$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		  	$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		  	$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$paramsLog['Module']="REPORT JUAL RETUR PERBARANG"; 
		 	$paramsLog['TrxID'] = date("YmdHis");
			$paramsLog['Description']=$_SESSION["logged_in"]["username"]." PROSES PDF REPORT 616 Dengan Dealer";
		 	$paramsLog['Remarks']="SUCCESS";
		  	$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($paramsLog); 


			$list = $this->ReportjualreturperbarangModel->list616($data);
			$list = json_decode($list,true);

			if($list['hasil']=='success'){
				 
				$paramsLog['Remarks']="SUCCESS";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);

				$mpdf = new \Mpdf\Mpdf(array(
					'mode' => '',
					'format' => 'A4',
					'default_font_size' => 8,
					'default_font' => 'arial',
					'margin_left' => 10,
					'margin_right' => 10,
					'margin_top' => 33,
					'margin_bottom' => 10,
					'margin_header' => 10,
					'margin_footer' => 5,
					'orientation' => 'P'
				));

				if($data['produk']=='ALL'){
					$type_product='PRODUCT & SPAREPART';
				}else{
					$type_product=$data['produk'];
				}

				$header='<table width="100%">';
				$header.='<tr>';
				$header.='<td colspan="3" align="center"><h2><b>JUAL - RETUR PER DIVISI PER DEALER PER KODE BARANG</b></h2></td>';
				$header.='</tr><tr><td colspan="3" style="padding:5px"></td></tr><tr>';
				$header.='<td>PERIODE : '.date_format(date_create($data['from']),'d-M-Y').' S/D '.date_format(date_create($data['until']),'d-M-Y').'</td>';
				$header.='<td></td>';
				$header.='<td align="center" width="200px" style="border:thin solid #000; padding:5px">'.$type_product.'</td>';
				$header.='</tr><tr>';
				$header.='<td colspan="3"><h3>'.$data['wilayah'].'</h3></td>';
				$header.='</tr>';
				$header.='</table>';

				$content='';

				$kdpelanggan='';
				$divisi='';
				$merk='';



				$total_qty_jual=0;
				$total_qty_retur=0;
				$total_qty=0;
				$total_jual=0;
				$total_retur=0;
				$total=0;


				$total_global_qty_jual=0;
				$total_global_qty_retur=0;
				$total_global_qty=0;
				$total_global_jual=0;
				$total_global_retur=0;
				$total_global=0;

				$content.='<table width="100%">';
				foreach ($list['data'] as $key => $l) {
					

					if(($divisi!==rtrim($l['DIVISI']) && $merk!==rtrim($l['MERK'])) || $kdpelanggan!==rtrim($l['KD_PLG'])){


						if($divisi!=='' && $merk!==''){
							$content.='<tr><td colspan="7" style="border-top:thin solid #000;"></td></tr><tr>';
							$content.='<td><b>Total Merk '.$merk.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty.'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td>';
							$content.='</tr><tr><td colspan="7" style="border-top:thin solid #000;"></td></tr>';
							$content.='<tr><td><b>'.$nmpelanggan.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty.'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td></tr>';
							$content.='<tr></tr>';
							$content.='<tr><td><b>Total Divisi '.$divisi.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty.'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td></tr>';
							$content.='<tr><td colspan="7" style="padding:10px;"></td></tr>';
						}


						$content.='<tr>';
						$content.='<td colspan="4"><b>'.$l['NM_PLG'].'</b></td>';
						$content.='<td colspan="3" align="right"><b>'.rtrim($l['KD_PLG']).'</b></td>';
						$content.='</tr>';

						$content.='<tr>';
						$content.='<td><b><i>Divisi :</i> '.$l['DIVISI'].'</b></td>';
						$content.='<td colspan="6"><b><i>Merk :</i> '.$l['MERK'].'</b></td>';
						$content.='</tr>';

						$total_qty_jual=0;
						$total_qty_retur=0;
						$total_qty=0;
						$total_jual=0;
						$total_retur=0;
						$total=0;

						$divisi=rtrim($l['DIVISI']);
						$merk=rtrim($l['MERK']);
						$kdpelanggan=rtrim($l['KD_PLG']);
						$nmpelanggan=$l['NM_PLG'];

						$content.='<thead><tr>';
						$content.='<td width="25%">KODE BARANG</td>';
						$content.='<td width="10%" align="right">QTY JUAL</td>';
						$content.='<td width="10%" align="right">QTY RETUR</td>';
						$content.='<td width="10%" align="right">TOTAL QTY</td>';
						$content.='<td width="15%" align="right">TOTAL JUAL</td>';
						$content.='<td width="15%" align="right">TOTAL RETUR</td>';
						$content.='<td width="15%" align="right">TOTAL</td>';
						$content.='</tr><tr><td colspan="7" style="border-top:thin solid #000;"></td></tr></thead><tbody>';

					}


						$total_qty_jual=$total_qty_jual+$l['QTY_JUAL'];
						$total_qty_retur=$total_qty_retur+$l['QTY_RETUR'];
						$total_qty=$total_qty+$l['QTY_TOTAL'];
						$total_jual=$total_jual+$l['TOTAL_JUAL'];
						$total_retur=$total_retur+$l['TOTAL_RETUR'];
						$total=$total+$l['TOTAL'];


						$total_global_qty_jual=$total_global_qty_jual+$l['QTY_JUAL'];
						$total_global_qty_retur=$total_global_qty_retur+$l['QTY_RETUR'];
						$total_global_qty=$total_global_qty+$l['QTY_TOTAL'];
						$total_global_jual=$total_global_jual+$l['TOTAL_JUAL'];
						$total_global_retur=$total_global_retur+$l['TOTAL_RETUR'];
						$total_global=$total_global+$l['TOTAL'];


						$content.='<tr>';
						$content.='<td>'.$l['KD_BRG'].'</td>';
						$content.='<td align="right">'.$l['QTY_JUAL'].'</td>';
						$content.='<td align="right">'.$l['QTY_RETUR'].'</td>';
						$content.='<td align="right">'.$l['QTY_TOTAL'].'</td>';
						$content.='<td align="right">'.number_format($l['TOTAL_JUAL'],0,",",".").'</td>';
						$content.='<td align="right">'.number_format($l['TOTAL_RETUR'],0,",",".").'</td>';
						$content.='<td align="right">'.number_format($l['TOTAL'],0,",",".").'</td>';
						$content.='</tr>';	

					
				}

				$content.='<tr><td colspan="7" style="border-top:thin solid #000;"></td></tr><tr>';
				$content.='<td><b>Total Merk '.$merk.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty.'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td>';
				$content.='</tr><tr><td colspan="7" style="border-top:thin solid #000;"></td></tr>';
				$content.='<tr><td><b>Total Divisi '.$divisi.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty.'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td></tr>';
				$content.='<tr><td colspan="7" style="padding:10px;"></td></tr>';


				$content.='<tr><td colspan="7" style="border-top:thin solid #000;"></td></tr><tr>';
				$content.='<td><b>GRANDTOTAL</b></td>';
				$content.='<td align="right"><b>'.$total_global_qty_jual.'</b></td>';
				$content.='<td align="right"><b>'.$total_global_qty_retur.'</b></td>';
				$content.='<td align="right"><b>'.$total_global_qty.'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_global_jual,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_global_retur,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_global,0,",",".").'</b></td>';
				$content.='</tr>';

				$content.='</tbody></table>';

				set_time_limit(60);
				$mpdf->SetHTMLHeader($header,'','1');
				$mpdf->WriteHTML($content);
				$mpdf->setFooter('<table border="0" width="100%"><tr><td>'.date('d-m-Y H-i-s').'</td><td align="right">Page {PAGENO} of {nb}</td></tr></table>');
				$mpdf->Output();

			}else{

				$paramsLog['Remarks']="FAILED - DATA TIDAK DITEMUKAN";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);
				$this->index('error');
				
			}
		}

		public function pdf616($data=''){
			//ini_set('max_execution_time', '1500');
			ini_set("pcre.backtrack_limit", "10000000");
			set_time_limit(60);


			$paramsLog = array();   
		 	$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		  	$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		  	$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		  	$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$paramsLog['Module']="REPORT JUAL RETUR PERBARANG"; 
		 	$paramsLog['TrxID'] = date("YmdHis");
			$paramsLog['Description']=$_SESSION["logged_in"]["username"]." PROSES PDF REPORT 616";
		 	$paramsLog['Remarks']="SUCCESS";
		  	$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($paramsLog); 

			$list = $this->ReportjualreturperbarangModel->list616($data);
			$list = json_decode($list,true);

			if($list['hasil']=='success'){
				 
				$paramsLog['Remarks']="SUCCESS";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);

				$mpdf = new \Mpdf\Mpdf(array(
					'mode' => '',
					'format' => 'A4',
					'default_font_size' => 8,
					'default_font' => 'arial',
					'margin_left' => 10,
					'margin_right' => 10,
					'margin_top' => 33,
					'margin_bottom' => 10,
					'margin_header' => 10,
					'margin_footer' => 5,
					'orientation' => 'P'
				));

				if($data['produk']=='ALL'){
					$type_product='PRODUCT & SPAREPART';
				}else{
					$type_product=$data['produk'];
				}

				$header='<table width="100%">';
				$header.='<tr>';
				$header.='<td colspan="3" align="center"><h2><b>JUAL - RETUR PER DIVISI PER KODE BARANG</b></h2></td>';
				$header.='</tr><tr><td colspan="3" style="padding:5px"></td></tr><tr>';
				$header.='<td>PERIODE : '.date_format(date_create($data['from']),'d-M-Y').' S/D '.date_format(date_create($data['until']),'d-M-Y').'</td>';
				$header.='<td></td>';
				$header.='<td align="center" width="200px" style="border:thin solid #000; padding:5px">'.$type_product.'</td>';
				$header.='</tr><tr>';
				$header.='<td colspan="3"><h3>'.$data['wilayah'].'</h3></td>';
				$header.='</tr>';
				$header.='</table>';

				$content='';

				$divisi='';
				$merk='';



				$total_qty_jual=0;
				$total_qty_retur=0;
				$total_qty=0;
				$total_jual=0;
				$total_retur=0;
				$total=0;


				$total_global_qty_jual=0;
				$total_global_qty_retur=0;
				$total_global_qty=0;
				$total_global_jual=0;
				$total_global_retur=0;
				$total_global=0;

				$content.='<table width="100%">';
				foreach ($list['data'] as $key => $l) {
					

					if($divisi!==rtrim($l['DIVISI']) && $merk!==rtrim($l['MERK'])){


						if($divisi!=='' && $merk!==''){
							$content.='<tr><td colspan="7" style="border-top:thin solid #000;"></td></tr><tr>';
							$content.='<td><b>Total Merk '.$merk.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty.'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td>';
							$content.='</tr><tr><td colspan="7" style="border-top:thin solid #000;"></td></tr>';
							$content.='<tr><td><b>Total Divisi '.$divisi.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
							$content.='<td align="right"><b>'.$total_qty.'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
							$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td></tr>';
							$content.='<tr><td colspan="7" style="padding:10px;"></td></tr>';
						}


						$content.='<tr>';
						$content.='<td><b><i>Divisi :</i> '.$l['DIVISI'].'</b></td>';
						$content.='<td colspan="6"><b><i>Merk :</i> '.$l['MERK'].'</b></td>';
						$content.='</tr>';

						$total_qty_jual=0;
						$total_qty_retur=0;
						$total_qty=0;
						$total_jual=0;
						$total_retur=0;
						$total=0;

						$divisi=rtrim($l['DIVISI']);
						$merk=rtrim($l['MERK']);

						$content.='<thead><tr>';
						$content.='<td width="25%">KODE BARANG</td>';
						$content.='<td width="10%" align="right">QTY JUAL</td>';
						$content.='<td width="10%" align="right">QTY RETUR</td>';
						$content.='<td width="10%" align="right">TOTAL QTY</td>';
						$content.='<td width="15%" align="right">TOTAL JUAL</td>';
						$content.='<td width="15%" align="right">TOTAL RETUR</td>';
						$content.='<td width="15%" align="right">TOTAL</td>';
						$content.='</tr><tr><td colspan="7" style="border-top:thin solid #000;"></td></tr></thead><tbody>';

					}


						$total_qty_jual=$total_qty_jual+$l['QTY_JUAL'];
						$total_qty_retur=$total_qty_retur+$l['QTY_RETUR'];
						$total_qty=$total_qty+$l['QTY_TOTAL'];
						$total_jual=$total_jual+$l['TOTAL_JUAL'];
						$total_retur=$total_retur+$l['TOTAL_RETUR'];
						$total=$total+$l['TOTAL'];


						$total_global_qty_jual=$total_global_qty_jual+$l['QTY_JUAL'];
						$total_global_qty_retur=$total_global_qty_retur+$l['QTY_RETUR'];
						$total_global_qty=$total_global_qty+$l['QTY_TOTAL'];
						$total_global_jual=$total_global_jual+$l['TOTAL_JUAL'];
						$total_global_retur=$total_global_retur+$l['TOTAL_RETUR'];
						$total_global=$total_global+$l['TOTAL'];


						$content.='<tr>';
						$content.='<td>'.$l['KD_BRG'].'</td>';
						$content.='<td align="right">'.$l['QTY_JUAL'].'</td>';
						$content.='<td align="right">'.$l['QTY_RETUR'].'</td>';
						$content.='<td align="right">'.$l['QTY_TOTAL'].'</td>';
						$content.='<td align="right">'.number_format($l['TOTAL_JUAL'],0,",",".").'</td>';
						$content.='<td align="right">'.number_format($l['TOTAL_RETUR'],0,",",".").'</td>';
						$content.='<td align="right">'.number_format($l['TOTAL'],0,",",".").'</td>';
						$content.='</tr>';	

					
				}

				$content.='<tr><td colspan="7" style="border-top:thin solid #000;"></td></tr><tr>';
				$content.='<td><b>Total Merk '.$merk.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty.'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td>';
				$content.='</tr><tr><td colspan="7" style="border-top:thin solid #000;"></td></tr>';
				$content.='<tr><td><b>Total Divisi '.$divisi.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty_jual.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty_retur.'</b></td>';
				$content.='<td align="right"><b>'.$total_qty.'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_jual,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_retur,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total,0,",",".").'</b></td></tr>';
				$content.='<tr><td colspan="7" style="padding:10px;"></td></tr>';


				$content.='<tr><td colspan="7" style="border-top:thin solid #000;"></td></tr><tr>';
				$content.='<td><b>GRANDTOTAL</b></td>';
				$content.='<td align="right"><b>'.$total_global_qty_jual.'</b></td>';
				$content.='<td align="right"><b>'.$total_global_qty_retur.'</b></td>';
				$content.='<td align="right"><b>'.$total_global_qty.'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_global_jual,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_global_retur,0,",",".").'</b></td>';
				$content.='<td align="right"><b>'.number_format($total_global,0,",",".").'</b></td>';
				$content.='</tr>';

				$content.='</tbody></table>';

				set_time_limit(60);
				$mpdf->SetHTMLHeader($header,'','1');
				$mpdf->WriteHTML($content);
				$mpdf->setFooter('<table border="0" width="100%"><tr><td>'.date('d-m-Y H-i-s').'</td><td align="right">Page {PAGENO} of {nb}</td></tr></table>');
				$mpdf->Output();

			}else{

				$paramsLog['Remarks']="FAILED - DATA TIDAK DITEMUKAN";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);
				$this->index('error');
				
			}
		}

		public function excel615_dealer($data=''){
			//ini_set('max_execution_time', '1500');
			ini_set("pcre.backtrack_limit", "10000000");
			set_time_limit(60);


			$paramsLog = array();   
		 	$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		  	$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		  	$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		  	$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$paramsLog['Module']="REPORT JUAL RETUR PERBARANG"; 
		 	$paramsLog['TrxID'] = date("YmdHis");
			$paramsLog['Description']=$_SESSION["logged_in"]["username"]." PROSES EXCEL REPORT 615 Dengan Dealer";
		 	$paramsLog['Remarks']="SUCCESS";
		  	$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($paramsLog); 


			$list = $this->ReportjualreturperbarangModel->list615($data);
			$list = json_decode($list,true);

			if($list['hasil']=='success'){
				 
				$paramsLog['Remarks']="SUCCESS";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);
				

				$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet(0);

				//ini_set('max_execution_time', '1500');
				ini_set("pcre.backtrack_limit", "10000000");
				set_time_limit(60);

				if($data['produk']=='ALL'){
					$type_product='PRODUCT & SPAREPART';
				}else{
					$type_product=$data['produk'];
				}

				$sheet->setTitle('REPORT RETUR PERBARANG');
				$sheet->setCellValue('A1', 'Printed : '.date('Y-m-d H-i-s'));
				$sheet->setCellValue('A2', 'JUAL - RETUR PER DEALER PER JENIS BARANG');

				$sheet->setCellValue('A3', date_format(date_create($data['from']),'d-M-Y').' S/D '.date_format(date_create($data['until']),'d-M-Y'));
				$sheet->setCellValue('F3', $type_product);
				$sheet->getStyle('F3:G3')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
				$sheet->getStyle('F3:G3')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
				$sheet->getStyle('F3:G3')->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
				$sheet->getStyle('F3:G3')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

				$sheet->setCellValue('A5', $data['wilayah']);
				$sheet->getStyle('A5:G5')->getFont()->setBold(true);

				$sheet->mergeCells('A1:G1');
				$sheet->getStyle('A1:G1')->getAlignment()->setHorizontal('right');
				$sheet->getStyle('A1')->getFont()->setSize(10);
				

				$sheet->mergeCells('A2:G2');
				$sheet->getStyle('A2:G2')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A2')->getFont()->setSize(20);

				$sheet->mergeCells('A3:C3');

				$sheet->mergeCells('F3:G3');
				$sheet->getStyle('F3:G3')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A3:G3')->getFont()->setSize(10);

				$sheet->mergeCells('A5:G5');



				$divisi='';
				$merk='';
				$nmpelanggan='';
				$kdpelanggan='';


				$total_qty_jual=0;
				$total_qty_retur=0;
				$total_qty=0;
				$total_jual=0;
				$total_retur=0;
				$total=0;


				$total_global_qty_jual=0;
				$total_global_qty_retur=0;
				$total_global_qty=0;
				$total_global_jual=0;
				$total_global_retur=0;
				$total_global=0;


				$currcol = 1;
				$currrow = 6;

				foreach ($list['data'] as $key => $l) {
					

					if(($divisi!==rtrim($l['DIVISI']) && $merk!==rtrim($l['MERK'])) || $kdpelanggan!==rtrim($l['KD_PLG'])){


						if($divisi!=='' && $merk!==''){

							$currcol = 1;
							$sheet->getStyle('A'.$currrow.':G'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Merk '.$merk);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);
							$currrow++;
							$sheet->getStyle('A'.$currrow.':G'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $nmpelanggan);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);
							$currrow++;
							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Divisi '.$divisi);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);

							$currrow=$currrow+2;
						}


						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['NM_PLG']);
						$sheet->mergeCells('A'.$currrow.':E'.$currrow);
						$currcol = 7;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['KD_PLG']));
						$sheet->mergeCells('F'.$currrow.':G'.$currrow);
						$sheet->getStyle('A'.$currrow.':G'.$currrow)->getFont()->setBold(true);
						$sheet->getStyle('F'.$currrow.':G'.$currrow)->getAlignment()->setHorizontal('right');
						$currrow++;		

						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi : '.$l['DIVISI']);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk : '.$l['MERK']);
						$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);
						$currrow++;		

						$total_qty_jual=0;
						$total_qty_retur=0;
						$total_qty=0;
						$total_jual=0;
						$total_retur=0;
						$total=0;

						$divisi=rtrim($l['DIVISI']);
						$merk=rtrim($l['MERK']);
						$kdpelanggan=rtrim($l['KD_PLG']);
						$nmpelanggan=$l['NM_PLG'];

						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'JENIS BARANG');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY JUAL');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY RETUR');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL QTY');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL JUAL');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL RETUR');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
						$currcol ++;
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':G'.$currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('eaeaea');
						$currrow++;					
						$sheet->getStyle('A'.$currrow.':G'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
					}


						$total_qty_jual=$total_qty_jual+$l['QTY_JUAL'];
						$total_qty_retur=$total_qty_retur+$l['QTY_RETUR'];
						$total_qty=$total_qty+$l['QTY_TOTAL'];
						$total_jual=$total_jual+$l['TOTAL_JUAL'];
						$total_retur=$total_retur+$l['TOTAL_RETUR'];
						$total=$total+$l['Total'];


						$total_global_qty_jual=$total_global_qty_jual+$l['QTY_JUAL'];
						$total_global_qty_retur=$total_global_qty_retur+$l['QTY_RETUR'];
						$total_global_qty=$total_global_qty+$l['QTY_TOTAL'];
						$total_global_jual=$total_global_jual+$l['TOTAL_JUAL'];
						$total_global_retur=$total_global_retur+$l['TOTAL_RETUR'];
						$total_global=$total_global+$l['Total'];


						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['JNS_BRG']);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['QTY_JUAL']);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['QTY_RETUR']);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['QTY_TOTAL']);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['TOTAL_JUAL'],0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['TOTAL_RETUR'],0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['Total'],0,",","."));
						$currcol++;

						$currrow++;
				}

							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Merk '.$merk);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);
							$currrow++;
							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $nmpelanggan);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);
							$currrow++;
							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Divisi '.$divisi);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);

							$currrow=$currrow+2;
							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_global_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_global_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_global_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_global_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_global_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_global,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);


				for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
				    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
				}

				$filename='REPORT JUAL RETUR PERBARANG ['.date('Y-m-d H-i-s').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');
				exit();

			}else{

				$paramsLog['Remarks']="FAILED - DATA TIDAK DITEMUKAN";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);
				$this->index('error');
				
			}
		}

		public function excel615($data=''){
			//ini_set('max_execution_time', '1500');
			ini_set("pcre.backtrack_limit", "10000000");
			set_time_limit(60);
 
			$paramsLog = array();   
		 	$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		  	$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		  	$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		  	$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$paramsLog['Module']="REPORT JUAL RETUR PERBARANG"; 
		 	$paramsLog['TrxID'] = date("YmdHis");
			$paramsLog['Description']=$_SESSION["logged_in"]["username"]." PROSES EXCEL REPORT 615";
		 	$paramsLog['Remarks']="SUCCESS";
		  	$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($paramsLog);

			$list = $this->ReportjualreturperbarangModel->list615($data);
			$list = json_decode($list,true);

			if($list['hasil']=='success'){
				 
				$paramsLog['Remarks']="SUCCESS";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);
				

				$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet(0);

				//ini_set('max_execution_time', '1500');
				ini_set("pcre.backtrack_limit", "10000000");
				set_time_limit(60);

				if($data['produk']=='ALL'){
					$type_product='PRODUCT & SPAREPART';
				}else{
					$type_product=$data['produk'];
				}

				$sheet->setTitle('REPORT RETUR PERBARANG');
				$sheet->setCellValue('A1', 'Printed : '.date('Y-m-d H-i-s'));
				$sheet->setCellValue('A2', 'JUAL - RETUR PER DIVISI PER JENIS BARANG');

				$sheet->setCellValue('A3', date_format(date_create($data['from']),'d-M-Y').' S/D '.date_format(date_create($data['until']),'d-M-Y'));
				$sheet->setCellValue('F3', $type_product);
				$sheet->getStyle('F3:G3')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
				$sheet->getStyle('F3:G3')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
				$sheet->getStyle('F3:G3')->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
				$sheet->getStyle('F3:G3')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

				$sheet->setCellValue('A5', $data['wilayah']);
				$sheet->getStyle('A5:G5')->getFont()->setBold(true);

				$sheet->mergeCells('A1:G1');
				$sheet->getStyle('A1:G1')->getAlignment()->setHorizontal('right');
				$sheet->getStyle('A1')->getFont()->setSize(10);
				

				$sheet->mergeCells('A2:G2');
				$sheet->getStyle('A2:G2')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A2')->getFont()->setSize(20);

				$sheet->mergeCells('A3:C3');

				$sheet->mergeCells('F3:G3');
				$sheet->getStyle('F3:G3')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A3:G3')->getFont()->setSize(10);

				$sheet->mergeCells('A5:G5');



				$divisi='';
				$merk='';



				$total_qty_jual=0;
				$total_qty_retur=0;
				$total_qty=0;
				$total_jual=0;
				$total_retur=0;
				$total=0;


				$total_global_qty_jual=0;
				$total_global_qty_retur=0;
				$total_global_qty=0;
				$total_global_jual=0;
				$total_global_retur=0;
				$total_global=0;


				$currcol = 1;
				$currrow = 6;

				foreach ($list['data'] as $key => $l) {
					

					if($divisi!==rtrim($l['DIVISI']) && $merk!==rtrim($l['MERK'])){


						if($divisi!=='' && $merk!==''){

							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Merk '.$merk);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);
							$sheet->getStyle('A'.$currrow.':G'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
							$currrow++;
							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Divisi '.$divisi);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);

							$currrow=$currrow+2;
						}

						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi : '.$l['DIVISI']);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk : '.$l['MERK']);
						$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);
						$currrow++;		

						$total_qty_jual=0;
						$total_qty_retur=0;
						$total_qty=0;
						$total_jual=0;
						$total_retur=0;
						$total=0;

						$divisi=rtrim($l['DIVISI']);
						$merk=rtrim($l['MERK']);

						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'JENIS BARANG');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY JUAL');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY RETUR');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL QTY');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL JUAL');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL RETUR');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
						$currcol ++;
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':G'.$currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('eaeaea');

						$currrow++;					
						$sheet->getStyle('A'.$currrow.':G'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
					}


						$total_qty_jual=$total_qty_jual+$l['QTY_JUAL'];
						$total_qty_retur=$total_qty_retur+$l['QTY_RETUR'];
						$total_qty=$total_qty+$l['QTY_TOTAL'];
						$total_jual=$total_jual+$l['TOTAL_JUAL'];
						$total_retur=$total_retur+$l['TOTAL_RETUR'];
						$total=$total+$l['TOTAL'];


						$total_global_qty_jual=$total_global_qty_jual+$l['QTY_JUAL'];
						$total_global_qty_retur=$total_global_qty_retur+$l['QTY_RETUR'];
						$total_global_qty=$total_global_qty+$l['QTY_TOTAL'];
						$total_global_jual=$total_global_jual+$l['TOTAL_JUAL'];
						$total_global_retur=$total_global_retur+$l['TOTAL_RETUR'];
						$total_global=$total_global+$l['TOTAL'];


						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['JNS_BRG']);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['QTY_JUAL']);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['QTY_RETUR']);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['QTY_TOTAL']);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['TOTAL_JUAL'],0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['TOTAL_RETUR'],0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['TOTAL'],0,",","."));
						$currcol++;

						$currrow++;
				}

							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Merk '.$merk);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);
							$sheet->getStyle('A'.$currrow.':G'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
							$currrow++;
							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Divisi '.$divisi);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);

							$currrow=$currrow+2;
							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_global_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_global_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_global_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_global_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_global_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_global,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);


				for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
				    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
				}

				$filename='REPORT JUAL RETUR PERBARANG ['.date('Y-m-d H-i-s').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');
				exit();

			}else{

				$paramsLog['Remarks']="FAILED - DATA TIDAK DITEMUKAN";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);
				$this->index('error');
				
			}
		}

		public function excel616_dealer($data=''){
			//ini_set('max_execution_time', '1500');
			ini_set("pcre.backtrack_limit", "10000000");
			set_time_limit(60);


			$paramsLog = array();   
		 	$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		  	$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		  	$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		  	$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$paramsLog['Module']="REPORT JUAL RETUR PERBARANG"; 
		 	$paramsLog['TrxID'] = date("YmdHis");
			$paramsLog['Description']=$_SESSION["logged_in"]["username"]." PROSES EXCEL REPORT 616 Dengan Dealer";
		 	$paramsLog['Remarks']="SUCCESS";
		  	$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($paramsLog);


			$list = $this->ReportjualreturperbarangModel->list616($data);
			$list = json_decode($list,true);

			if($list['hasil']=='success'){
				 
				$paramsLog['Remarks']="SUCCESS";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);

				$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet(0);

				//ini_set('max_execution_time', '1500');
				ini_set("pcre.backtrack_limit", "10000000");
				set_time_limit(60);

				if($data['produk']=='ALL'){
					$type_product='PRODUCT & SPAREPART';
				}else{
					$type_product=$data['produk'];
				}

				$sheet->setTitle('REPORT RETUR PERBARANG');
				$sheet->setCellValue('A1', 'Printed : '.date('Y-m-d H-i-s'));
				$sheet->setCellValue('A2', 'JUAL - RETUR PER DIVISI PER DEALER PER KODE BARANG');

				$sheet->setCellValue('A3', date_format(date_create($data['from']),'d-M-Y').' S/D '.date_format(date_create($data['until']),'d-M-Y'));
				$sheet->setCellValue('F3', $type_product);
				$sheet->getStyle('F3:G3')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
				$sheet->getStyle('F3:G3')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
				$sheet->getStyle('F3:G3')->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
				$sheet->getStyle('F3:G3')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

				$sheet->setCellValue('A5', $data['wilayah']);
				$sheet->getStyle('A5:G5')->getFont()->setBold(true);

				$sheet->mergeCells('A1:G1');
				$sheet->getStyle('A1:G1')->getAlignment()->setHorizontal('right');
				$sheet->getStyle('A1')->getFont()->setSize(10);
				

				$sheet->mergeCells('A2:G2');
				$sheet->getStyle('A2:G2')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A2')->getFont()->setSize(20);

				$sheet->mergeCells('A3:C3');

				$sheet->mergeCells('F3:G3');
				$sheet->getStyle('F3:G3')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A3:G3')->getFont()->setSize(10);

				$sheet->mergeCells('A5:G5');



				$divisi='';
				$merk='';
				$nmpelanggan='';
				$kdpelanggan='';


				$total_qty_jual=0;
				$total_qty_retur=0;
				$total_qty=0;
				$total_jual=0;
				$total_retur=0;
				$total=0;


				$total_global_qty_jual=0;
				$total_global_qty_retur=0;
				$total_global_qty=0;
				$total_global_jual=0;
				$total_global_retur=0;
				$total_global=0;


				$currcol = 1;
				$currrow = 6;

				foreach ($list['data'] as $key => $l) {
					

					if(($divisi!==rtrim($l['DIVISI']) && $merk!==rtrim($l['MERK'])) || $kdpelanggan!==rtrim($l['KD_PLG'])){


						if($divisi!=='' && $merk!==''){

							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Merk '.$merk);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);
							$sheet->getStyle('A'.$currrow.':G'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
							$currrow++;
							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $nmpelanggan);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);
							$currrow++;
							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Divisi '.$divisi);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);

							$currrow=$currrow+2;
						}


						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['NM_PLG']);
						$sheet->mergeCells('A'.$currrow.':E'.$currrow);
						$currcol = 7;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['KD_PLG']));
						$sheet->mergeCells('F'.$currrow.':G'.$currrow);
						$sheet->getStyle('A'.$currrow.':G'.$currrow)->getFont()->setBold(true);
						$sheet->getStyle('F'.$currrow.':G'.$currrow)->getAlignment()->setHorizontal('right');
						$currrow++;		

						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi : '.$l['DIVISI']);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk : '.$l['MERK']);
						$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);
						$currrow++;		

						$total_qty_jual=0;
						$total_qty_retur=0;
						$total_qty=0;
						$total_jual=0;
						$total_retur=0;
						$total=0;

						$divisi=rtrim($l['DIVISI']);
						$merk=rtrim($l['MERK']);
						$kdpelanggan=rtrim($l['KD_PLG']);
						$nmpelanggan=$l['NM_PLG'];

						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KODE BARANG');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY JUAL');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY RETUR');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL QTY');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL JUAL');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL RETUR');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
						$currcol ++;
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':G'.$currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('eaeaea');

						$currrow++;					
						$sheet->getStyle('A'.$currrow.':G'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
					}


						$total_qty_jual=$total_qty_jual+$l['QTY_JUAL'];
						$total_qty_retur=$total_qty_retur+$l['QTY_RETUR'];
						$total_qty=$total_qty+$l['QTY_TOTAL'];
						$total_jual=$total_jual+$l['TOTAL_JUAL'];
						$total_retur=$total_retur+$l['TOTAL_RETUR'];
						$total=$total+$l['TOTAL'];


						$total_global_qty_jual=$total_global_qty_jual+$l['QTY_JUAL'];
						$total_global_qty_retur=$total_global_qty_retur+$l['QTY_RETUR'];
						$total_global_qty=$total_global_qty+$l['QTY_TOTAL'];
						$total_global_jual=$total_global_jual+$l['TOTAL_JUAL'];
						$total_global_retur=$total_global_retur+$l['TOTAL_RETUR'];
						$total_global=$total_global+$l['TOTAL'];


						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['KD_BRG']));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['QTY_JUAL']);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['QTY_RETUR']);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['QTY_TOTAL']);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['TOTAL_JUAL'],0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['TOTAL_RETUR'],0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['TOTAL'],0,",","."));
						$currcol++;

						$currrow++;
				}

							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Merk '.$merk);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);
							$sheet->getStyle('A'.$currrow.':G'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
							$currrow++;
							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $nmpelanggan);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);
							$currrow++;
							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Divisi '.$divisi);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);

							$currrow=$currrow+2;
							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_global_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_global_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_global_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_global_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_global_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_global,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);


				for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
				    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
				}

				$filename='REPORT JUAL RETUR PERBARANG ['.date('Y-m-d H-i-s').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');
				exit();

			}else{

				$paramsLog['Remarks']="FAILED - DATA TIDAK DITEMUKAN";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);
				$this->index('error');
				
			}
		}

		public function excel616($data=''){
			//ini_set('max_execution_time', '1500');
			ini_set("pcre.backtrack_limit", "10000000");
			set_time_limit(60);
 
			$paramsLog = array();   
		 	$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		  	$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		  	$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		  	$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$paramsLog['Module']="REPORT JUAL RETUR PERBARANG"; 
		 	$paramsLog['TrxID'] = date("YmdHis");
			$paramsLog['Description']=$_SESSION["logged_in"]["username"]." PROSES EXCEL REPORT 616";
		 	$paramsLog['Remarks']="SUCCESS";
		  	$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($paramsLog);

			$list = $this->ReportjualreturperbarangModel->list616($data);
			$list = json_decode($list,true);

			if($list['hasil']=='success'){
				
				$paramsLog['Remarks']="SUCCESS";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);

				$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet(0);

				//ini_set('max_execution_time', '1500');
				ini_set("pcre.backtrack_limit", "10000000");
				set_time_limit(60);

				if($data['produk']=='ALL'){
					$type_product='PRODUCT & SPAREPART';
				}else{
					$type_product=$data['produk'];
				}

				$sheet->setTitle('REPORT RETUR PERBARANG');
				$sheet->setCellValue('A1', 'Printed : '.date('Y-m-d H-i-s'));
				$sheet->setCellValue('A2', 'JUAL - RETUR PER DIVISI PER KODE BARANG');

				$sheet->setCellValue('A3', date_format(date_create($data['from']),'d-M-Y').' S/D '.date_format(date_create($data['until']),'d-M-Y'));
				$sheet->setCellValue('F3', $type_product);
				$sheet->getStyle('F3:G3')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
				$sheet->getStyle('F3:G3')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
				$sheet->getStyle('F3:G3')->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
				$sheet->getStyle('F3:G3')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);


				$sheet->setCellValue('A5', $data['wilayah']);
				$sheet->getStyle('A5:G5')->getFont()->setBold(true);

				$sheet->mergeCells('A1:G1');
				$sheet->getStyle('A1:G1')->getAlignment()->setHorizontal('right');
				$sheet->getStyle('A1')->getFont()->setSize(10);
				

				$sheet->mergeCells('A2:G2');
				$sheet->getStyle('A2:G2')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A2')->getFont()->setSize(20);

				$sheet->mergeCells('A3:C3');

				$sheet->mergeCells('F3:G3');
				$sheet->getStyle('F3:G3')->getAlignment()->setHorizontal('center');
				$sheet->getStyle('A3:G3')->getFont()->setSize(10);

				$sheet->mergeCells('A5:G5');



				$divisi='';
				$merk='';



				$total_qty_jual=0;
				$total_qty_retur=0;
				$total_qty=0;
				$total_jual=0;
				$total_retur=0;
				$total=0;


				$total_global_qty_jual=0;
				$total_global_qty_retur=0;
				$total_global_qty=0;
				$total_global_jual=0;
				$total_global_retur=0;
				$total_global=0;


				$currcol = 1;
				$currrow = 6;

				foreach ($list['data'] as $key => $l) {
					

					if($divisi!==rtrim($l['DIVISI']) && $merk!==rtrim($l['MERK'])){


						if($divisi!=='' && $merk!==''){

							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Merk '.$merk);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);
							$sheet->getStyle('A'.$currrow.':G'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
							$currrow++;
							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Divisi '.$divisi);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);

							$currrow=$currrow+2;
						}

						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi : '.$l['DIVISI']);
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk : '.$l['MERK']);
						$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);
						$currrow++;		

						$total_qty_jual=0;
						$total_qty_retur=0;
						$total_qty=0;
						$total_jual=0;
						$total_retur=0;
						$total=0;

						$divisi=rtrim($l['DIVISI']);
						$merk=rtrim($l['MERK']);

						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KODE BARANG');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY JUAL');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY RETUR');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL QTY');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL JUAL');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL RETUR');
						$currcol ++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
						$currcol ++;
						$spreadsheet->getActiveSheet()->getStyle('A'.$currrow.':G'.$currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('eaeaea');

						$currrow++;		
						$sheet->getStyle('A'.$currrow.':G'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);			
					}


						$total_qty_jual=$total_qty_jual+$l['QTY_JUAL'];
						$total_qty_retur=$total_qty_retur+$l['QTY_RETUR'];
						$total_qty=$total_qty+$l['QTY_TOTAL'];
						$total_jual=$total_jual+$l['TOTAL_JUAL'];
						$total_retur=$total_retur+$l['TOTAL_RETUR'];
						$total=$total+$l['TOTAL'];


						$total_global_qty_jual=$total_global_qty_jual+$l['QTY_JUAL'];
						$total_global_qty_retur=$total_global_qty_retur+$l['QTY_RETUR'];
						$total_global_qty=$total_global_qty+$l['QTY_TOTAL'];
						$total_global_jual=$total_global_jual+$l['TOTAL_JUAL'];
						$total_global_retur=$total_global_retur+$l['TOTAL_RETUR'];
						$total_global=$total_global+$l['TOTAL'];


						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($l['KD_BRG']));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['QTY_JUAL']);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['QTY_RETUR']);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l['QTY_TOTAL']);
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['TOTAL_JUAL'],0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['TOTAL_RETUR'],0,",","."));
						$currcol++;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($l['TOTAL'],0,",","."));
						$currcol++;

						$currrow++;
				}

							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Merk '.$merk);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);
							$sheet->getStyle('A'.$currrow.':G'.$currrow)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
							$currrow++;
							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Divisi '.$divisi);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);

							$currrow=$currrow+2;
							$currcol = 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GRANDTOTAL');
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_global_qty_jual);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_global_qty_retur);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_global_qty);
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_global_jual,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_global_retur,0,",","."));
							$currcol ++;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($total_global,0,",","."));
							$sheet->getStyle("A".$currrow.":G".$currrow)->getFont()->setBold(true);


				for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
				    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
				}

				$filename='REPORT JUAL RETUR PERBARANG ['.date('Y-m-d H-i-s').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');
				exit();

			}else{

				$paramsLog['Remarks']="FAILED - DATA TIDAK DITEMUKAN";
				$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($paramsLog);
				$this->index('error');
				
			}
		}
 
	}

?>