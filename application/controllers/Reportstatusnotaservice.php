<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class Reportstatusnotaservice extends MY_Controller 
	{
		public $excel_flag = 0; 
		public function __construct()
		{
			parent::__construct(); 
			$this->load->model('HelperModel');
			$this->load->helper('FormLibrary');
			$this->load->library('email');
			$this->load->library('excel');
		}
		
		public function proses()
		{
				$api = 'APITES';  

				$kodenota = $_POST['kodenota'];
				$garansi = $_POST['garansi'];
				$selesai = $_POST['selesai'];
				$batal = $_POST['batal'];
				$bayar = $_POST['bayar'];

				$p_start_date = date("d-M-Y", strtotime($_POST['tglawal']));   

				$url = $_SESSION["conn"]->AlamatWebService;
				$svr = $_SESSION["conn"]->Server;
				$db  = $_SESSION["conn"]->Database; 
	        	//$url = "http://localhost:100/";  

				ini_set('max_execution_time', '300');
				ini_set("pcre.backtrack_limit", "5000000"); 
		      	set_time_limit(0);

				$url_cekFakturGantung = $url.API_BKT."/reportstatusnotaservice/GetNotaServiceGantung?api=".$api."&svr=".$svr."&db=".$db."&bln=".$p_start_date;
 				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_URL => $url_cekFakturGantung,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 1000, 
				));
				$result = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl); 
				$result = json_decode($result,true); 

				if (count($result['data'])>0)
				{ 
					echo json_encode($result['data']);  
				}
				else{ 
					$url_ProsesNotaService = $url.API_BKT."/reportstatusnotaservice/ProsesNotaService?api=".$api."&svr=".$svr."&db=".$db."&bln=".$p_start_date."&kodenota=".$kodenota."&garansi=".$garansi."&selesai=".$selesai."&batal=".$batal."&bayar=".$bayar; 
					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_URL => $url_ProsesNotaService,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_TIMEOUT => 1000, 
					));	
					$result = curl_exec($curl);
					$err = curl_error($curl);
					curl_close($curl); 
					$result = json_decode($result,true); 
					$_SESSION["DataReportNotaService"] = $result["data"]; 	
					echo('PROSES SUDAH SELESAI');	
				}
		}

		public function index()
		{  
				$api = 'APITES';   
				$url = $_SESSION["conn"]->AlamatWebService;
				$svr = $_SESSION["conn"]->Server;
				$db  = $_SESSION["conn"]->Database;  
	        	//$url = "http://localhost:100/"; 
				$_SESSION["DataReportNotaService"] = array(); 	

				$data['listnota'] = json_decode(file_get_contents($url.API_BKT."/reportstatusnotaservice/GetListKodeNotaService?api=".$api."&svr=".$svr."&db=".$db));
 
				$data['title'] = 'Report Status Nota Service';
				$data['formDest'] = "Reportstatusnotaservice/Cetak_Report_Status_Nota_Service"; 
				$this->RenderView('Reportstatusnotaserviceview',$data);    

		} 
 
		public function Cetak_Report_Status_Nota_Service()
		{ 
			 
			if (count($_SESSION["DataReportNotaService"])>0)
			{
				if (isset($_POST["btnPdf"])) 
				{
					$this->Print_Pdf();
				}
				else
				if (isset($_POST["btnExcel"])) 
					$this->Download_Excel();   
			}
			else
			{
				echo '<script language="javascript">';
				echo 'alert("MAAF! Anda belum melakukan PROSES DATA apa pun. Silakan klik PROSES & tunggu sebentar hingga tampil pesan '."'PROSES SUDAH SELESAI!'".' ")';
				echo '</script>';

				// echo '<script>location.href = "'.site_url('Reportstatusnotaservice').'"</script>';
			}

		} 

		public function GetListGudangByKdGroupGudang()
		{ 
			$api = 'APITES';   
			$url = $_SESSION["conn"]->AlamatWebService;
			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;  
        	//$url = "http://localhost:100/"; 

			$listgudang = ""; 
			$kdgroupgudang = $this->input->get('kdgroupgudang');
			$GetListGudangByKdGroupGudang = json_decode(file_get_contents($url.API_BKT."/MasterGudang/GetListGudangByKdGroupGudang?api=".$api."&svr=".$svr."&db=".$db."&kdgroup_gudang=".$kdgroupgudang.""));

			if(count($GetListGudangByKdGroupGudang->data)>0){
				$count = 0;
				foreach ($GetListGudangByKdGroupGudang->data as $key => $value) {
					if ($count==0)
						$listgudang = $value->Gudang." | ".$value->NamaGudang;
					else
						$listgudang .= ";;".$value->Gudang." | ".$value->NamaGudang;
					$count++;
				}
			} 	   
			echo json_encode($listgudang);
		} 

 
		//PDF
		public function Print_Pdf()
		{  
			$dpbln = date("M, Y", strtotime($_POST['dpbln']));  
			$tglawal = date("d M Y", strtotime($_POST['tglawal']));    
			$tglakhir = date("d M Y", strtotime($_POST['tglakhir']));     


			$mpdf = new \Mpdf\Mpdf(array(
	          'mode' => '',
	          'format' => 'A4',
	          'default_font_size' => 8,
	          'default_font' => 'arial',
	          'margin_left' => 10,
	          'margin_right' => 10,
	          'margin_top' => 50,
	          'margin_bottom' => 10,
	          'margin_header' => 10,
	          'margin_footer' => 5,
	          'orientation' => 'P'
	        ));


			$header='<table width="100%">';

	        $header.='<tr>'; 
	        $header.='<td>'.date('d-M-Y H:i:s').'</td>';
	        $header.='<td></td>';
	        $header.='<td align="right" width="200px" style="padding:5px">Halaman {PAGENO} / {nbpg}</td>'; 
	        $header.='</tr>';

	        $header.='<tr>';
	        $header.='<td colspan="3" align="center"><h2><b>LAPORAN STATUS NOTA SERVIS</b></h2></td>';
	        $header.='</tr><tr><td colspan="3" style="padding:5px"></td></tr><tr>';
	        $header.='<td><b>Bulan</b> : '.$dpbln.'</td>'; 
	        $header.='<td></td>'; 
	        $header.='<td></td>'; 
	        $header.='</tr>'; 

	        $header.='<<tr>'; 
	        $header.='<td><b>Periode Kontrol</b> : '.$tglawal.' <b>S/D</b> '.$tglakhir.'</td>';
	        $header.='<td></td>'; 
	        $header.='<td></td>'; 
	        $header.='</tr>'; 	    

	        $header.='<<tr>'; 

			if(isset($_POST['chksemuakodenota'])) 
	        	$header.='<td><b>Nota Service</b> : SEMUA KODE NOTA SERVIS</td>'; 
			else 
	        	$header.='<td><b>Nota Service</b> : '.$_POST['cbokodenota'].'</td>'; 

	        $header.='<td></td>'; 
	        $header.='<td></td>'; 
	        $header.='</tr>'; 
 
	        $header.='</table>';

	    	$header .='<table width="100%">'; 
			$header.='<thead><tr>';
			$header.='<td width="10%" ><b>Tanggal</b></td>';
			$header.='<td width="20%" ><b>Nomor Nota Servis</b></td>';
			$header.='<td width="10%" ><b>Status</b></td>';
			$header.='<td width="10%" ><b>Sudah Selesai</b></td>';
			$header.='<td width="10%" ><b>Belum Selesai</b></td>';
			$header.='<td width="20%" ><b>No BBT</b></td>';
			$header.='<td width="10%" ><b>Nilai Setoran</b></td>';
			$header.='<td width="10%" ><b>Keterangan</b></td>';
			$header.='</tr><tr><td colspan="8" style="border-top:thin solid #000;"></td></tr></thead><tbody>';  
        	$header.='</tbody></table>'; 
    
 			$DataReportNotaService = $_SESSION["DataReportNotaService"];

 			$page = 0; 

 			$SubtotalTx = 0;
 			$SubtotalTx_Selesai = 0;
 			$SubtotalTx_Belum_Selesai = 0;
 			$SubTotalSudah_Bayar = 0;  

 			foreach($DataReportNotaService as $datahaader){  
 				$totalTx = 0;
 				$totalTx_Selesai = 0;
 				$totalTx_Belum_Selesai = 0;
 				$TotalSudah_Bayar = 0; 
 				$totalNilai_Setoran = 0;
	        	$content=''; 
 				$content.='<table width="100%">'; 
				$content.='<tbody>'; 
 				foreach($datahaader['data_report'] as $datadetail){ 
	 				$content.='<tr>';
					$content.='<td width="10%" >'.date("d-m-Y",strtotime($datadetail['Tgl_Svc'])).'</td>';
					$content.='<td width="20%" >'.$datadetail['No_Svc'].'</td>';
					$content.='<td width="10%" >'.(($datadetail['Jaminan']=='Y') ? 'Garansi' : 'Non-Garansi').'</td>';
					$content.='<td width="10%" align="center">'.(($datadetail['Kembali']=='Y') ? 'X' : '').'</td>';
					$content.='<td width="10%" align="center">'.(($datadetail['Kembali']=='Y') ? '' : 'X').'</td>';
					$content.='<td width="20%" >'.$datadetail['NoBBT'].'</td>';
					$content.='<td width="10%" >'.number_format($datadetail['Total'],0).'</td>';
					$content.='<td width="10%" >'.(($datadetail['Cancelled']=='Y') ? 'BATAL' : '').'</td>';
					$content.='</tr>';  
 	 
					$totalTx++; 

 					if ($datadetail['Kembali']=='Y')
 						$totalTx_Selesai++; 
 					else
 						$totalTx_Belum_Selesai++;  

 					$totalNilai_Setoran = $totalNilai_Setoran+$datadetail['Total'];

					
 					if ($datadetail['Ongkos_Svc']>0){ 
 						$TotalSudah_Bayar++;
 					}

 				} 
        		$content.='</tbody></table>';

 				$content.='<table width="100%"><tbody>'; 

				$content.='<tr><td colspan="15" style="border-top:thin solid #000;"></td></tr>'; 

 				$content.='<tr>';
				$content.='<td colspan="3"><b>Bulan</b> '.$datahaader['bulan'].'</td>';  
				$content.='<td colspan="1"><b>Transaksi</b></td>'; 
				$content.='<td colspan="2">'.$totalTx.'</td>'; 
				$content.='<td colspan="1"><b>Sdh Selesai</b></td>';  
				$content.='<td colspan="2">'.$totalTx_Selesai.'</td>';  
				$content.='<td colspan="1"><b>Nilai Setoran</b></td>'; 
				$content.='<td colspan="2">'.$totalNilai_Setoran.'</td>'; 
				$content.='<td colspan="1"><b>Bayar</b></td>'; 
				$content.='<td colspan="2">'.$TotalSudah_Bayar.'</td>'; 
				$content.='</tr>';  

				$content.='<tr>';
				$content.='<td colspan="6"><b></b></td>';  
				$content.='<td colspan="1"><b>Blm Selesai</b></td>';  
				$content.='<td colspan="2">'.$totalTx_Belum_Selesai.'</td>';  
				$content.='<td colspan="6"><b></b></td>';  
				$content.='</tr>';  

				$content.='<tr><td colspan="15" style="padding:5px;"></td></tr>'; 

        		$content.='</tbody></table>';

        		$SubtotalTx += $totalTx;
        		$SubtotalTx_Selesai += $totalTx_Selesai;
        		$SubtotalTx_Belum_Selesai += $totalTx_Belum_Selesai;
        		$SubTotalSudah_Bayar += $TotalSudah_Bayar; 

        		if($page>0)
        		{
        			$mpdf->AddPage();
        		}  
		        $page++;

		        if ($page==count($DataReportNotaService))
		        {
		        	$content.='<table width="100%"><tbody>';  
					$content.='<tr><td colspan="15" style="border-top:thin solid #000;"></td></tr>';  
	 				$content.='<tr>';
					$content.='<td colspan="3"><b>Bulan Semuanya</b></td>';  
					$content.='<td colspan="1"><b>Transaksi</b></td>'; 
					$content.='<td colspan="2">'.$SubtotalTx.'</td>'; 
					$content.='<td colspan="1"><b>Sdh Selesai</b></td>';  
					$content.='<td colspan="2">'.$SubtotalTx_Selesai.'</td>';  
					$content.='<td colspan="1"><b>Blm Selesai</b></td>'; 
					$content.='<td colspan="2">'.$SubtotalTx_Belum_Selesai.'</td>'; 
					$content.='<td colspan="1"><b>Bayar</b></td>'; 
					$content.='<td colspan="2">'.$SubTotalSudah_Bayar.'</td>'; 
					$content.='</tr>';    
					$content.='<tr><td colspan="15" style="border-top:thin solid #000;"></td></tr>';  
					$content.='<tr><td colspan="15" style="padding:5px;"></td></tr>';  
	        		$content.='</tbody></table>';

					$content.='<table width="100%"><tbody>';  
	 				$content.='<tr>';
					$content.='<td  width="20%"> 
					<div> 
					<table width="100%">
						<tr> <td ><b> SUMMARY </b></td> </tr>
						<tr> <td style="border-top:thin solid #000;border-bottom:thin solid #000;"></td ></tr> 
						<tr> <td style="padding:5px;"><b> Total Transaksi </b></td> </tr>
						<tr> <td style="padding:5px;"><b> Sudah Selesai </b></td> </tr>
						<tr> <td style="padding:5px;"><b> Belum Selesai </b></td> </tr>
						<tr> <td style="padding:5px;"><b> Bayar </td> </b></tr> 
					</table> 
					</div> 
					</td>';  
					$content.='<td  width="60%">
					<div> 

					<table width="100%" border="1" style=" border-collapse: collapse;">';

					$content_bln1 = '<tr>';
					$content_bln2 = '<tr>';
					$content_bln3 = '<tr>';
					$content_bln4 = '<tr>';
					$content_bln5 = '<tr>';  


					$Total_Transaksi_final = 0;
					$Sudah_Selesai_final = 0;
					$Belum_Selesai_final = 0;
					$Sudah_Bayar_final = 0;


		        	$baris_ke = 0;
					foreach($DataReportNotaService as $datahaader){   
						$content_bln1.='<td width="20%" align="center" style="padding:5px;"><b>'.$datahaader['bulan'].'</b></td>';
						$content_bln2.='<td width="20%" align="center" style="padding:5px;">'.$datahaader['Total_Transaksi'].'</td>';
						$content_bln3.='<td width="20%" align="center" style="padding:5px;">'.$datahaader['Sudah_Selesai'].'</td>';
						$content_bln4.='<td width="20%" align="center" style="padding:5px;">'.$datahaader['Belum_Selesai'].'</td>'; 
						$content_bln5.='<td width="20%" align="center" style="padding:5px;">'.$datahaader['Sudah_Bayar'].'</td>'; 

						$Total_Transaksi_final += $datahaader['Total_Transaksi'];
						$Sudah_Selesai_final += $datahaader['Sudah_Selesai'];
						$Belum_Selesai_final += $datahaader['Belum_Selesai'];
						$Sudah_Bayar_final += $datahaader['Sudah_Bayar'];  

						$baris_ke++;

						if ($baris_ke==count($DataReportNotaService))
						{
							$content_bln1.='<td width="20%" align="center" style="padding:5px;"><b>TOTAL</b></td>';
							$content_bln2.='<td width="20%" align="center" style="padding:5px;">'.$Total_Transaksi_final.'</td>';
							$content_bln3.='<td width="20%" align="center" style="padding:5px;">'.$Sudah_Selesai_final.'</td>';
							$content_bln4.='<td width="20%" align="center" style="padding:5px;">'.$Belum_Selesai_final.'</td>'; 
							$content_bln5.='<td width="20%" align="center" style="padding:5px;">'.$Sudah_Bayar_final.'</td>';  
						}
					} 
					$content_bln1.='</tr>';
					$content_bln2.='</tr>';
					$content_bln3.='</tr>';
					$content_bln4.='</tr>';
					$content_bln5.='</tr>';  

					$content.=$content_bln1.$content_bln2.$content_bln3.$content_bln4.$content_bln5; 

					$content.='</table>

					</div> 
					</td>';  
					$content.='<td  width="20%"></td>';  
					$content.='</tr>';    
	        		$content.='</tbody></table>'; 
		        } 
		        $mpdf->SetHTMLHeader($header,'','1');
		        $mpdf->WriteHTML($content);   
 			}  
	        $mpdf->Output();
		}  
		//PDF
 
 		//EXCEL
		public function Download_Excel()
		{   
			$dpbln = date("M, Y", strtotime($_POST['dpbln']));  
			$tglawal = date("d M Y", strtotime($_POST['tglawal']));    
			$tglakhir = date("d M Y", strtotime($_POST['tglakhir']));   

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID; 
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
			$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
  
			$sheet->setTitle("SERAH_TERIMA_BPB");
 
			$sheet->setCellValue('A1', "LAPORAN STATUS NOTA SERVIS");
			$sheet->mergeCells('A1:K1');
			$sheet->getStyle('A1:K1')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A1')->getFont()->setSize(20); 
			$sheet->setCellValue('A2', 'Print Date : '.date('d-M-Y H:i:s'));
			$sheet->setCellValue('A3', 'Bulan : '.$dpbln); 
			$sheet->setCellValue('A4', 'Periode Kontrol : '.$tglawal.' sd '.$tglakhir); 

			if(isset($_POST['chksemuakodenota'])) 
				$sheet->setCellValue('A5', 'Nota Service : SEMUA KODE NOTA SERVIS'); 
			else 
				$sheet->setCellValue('A5', 'Nota Service : '.$_POST['cbokodenota']); 

			$pos = 7;
			$sheet->getColumnDimension('A')->setWidth(20); 
			$sheet->getColumnDimension('B')->setWidth(20); 
			$sheet->getColumnDimension('C')->setWidth(10); 
			$sheet->getColumnDimension('D')->setWidth(10); 
			$sheet->getColumnDimension('E')->setWidth(20);  
			$sheet->getColumnDimension('F')->setWidth(15);  
			$sheet->getColumnDimension('G')->setWidth(15);
			$sheet->getColumnDimension('H')->setWidth(20);  
			$sheet->getColumnDimension('I')->setWidth(15);  
			$sheet->getColumnDimension('J')->setWidth(10);   
			$sheet->getColumnDimension('K')->setWidth(15);    

			$sheet->setCellValue('A'.$pos, 'Tanggal');
			$sheet->setCellValue('B'.$pos, 'Nomor Nota Servis');
			$sheet->setCellValue('E'.$pos, 'Status');
			$sheet->setCellValue('F'.$pos, 'Sudah Selesai'); 
			$sheet->setCellValue('G'.$pos, 'Belum Selesai'); 
			$sheet->setCellValue('H'.$pos, 'No BBT'); 
			$sheet->setCellValue('I'.$pos, 'Nilai Setoran'); 
			$sheet->setCellValue('K'.$pos, 'Keterangan'); 
			$sheet->getStyle("A".$pos.":K".$pos)->getFont()->setBold(true); 
			$sheet->getStyle("A".$pos.":K".$pos)->getAlignment()->setHorizontal($alignment_center);


			$DataReportNotaService = $_SESSION["DataReportNotaService"];

 			$page = 0; 

 			$SubtotalTx = 0;
 			$SubtotalTx_Selesai = 0;
 			$SubtotalTx_Belum_Selesai = 0;
 			$SubTotalSudah_Bayar = 0;  

 			foreach($DataReportNotaService as $datahaader){  
 				$totalTx = 0;
 				$totalTx_Selesai = 0;
 				$totalTx_Belum_Selesai = 0;
 				$TotalSudah_Bayar = 0; 
 				$totalNilai_Setoran = 0;
	        	$content='';  
 				foreach($datahaader['data_report'] as $datadetail){  
 	 
					$pos++;
		 			$sheet->setCellValue('A'.$pos, date("d-m-Y",strtotime($datadetail['Tgl_Svc'])));
					$sheet->setCellValue('B'.$pos, $datadetail['No_Svc']);
					$sheet->setCellValue('E'.$pos, (($datadetail['Jaminan']=='Y') ? 'Garansi' : 'Non-Garansi'));
					$sheet->setCellValue('F'.$pos, (($datadetail['Kembali']=='Y') ? 'X' : '')); 
					$sheet->setCellValue('G'.$pos, (($datadetail['Kembali']=='Y') ? '' : 'X')); 
					$sheet->setCellValue('H'.$pos, $datadetail['NoBBT']); 
					$sheet->setCellValue('I'.$pos, number_format($datadetail['Total'],0)); 
					$sheet->setCellValue('K'.$pos, (($datadetail['Cancelled']=='Y') ? 'BATAL' : '')); 

					$sheet->getStyle('F'.$pos)->getAlignment()->setHorizontal($alignment_center);
					$sheet->getStyle('G'.$pos)->getAlignment()->setHorizontal($alignment_center);
					$sheet->getStyle('I'.$pos)->getAlignment()->setHorizontal($alignment_right);
					$sheet->getStyle('K'.$pos)->getAlignment()->setHorizontal($alignment_center);

					$totalTx++; 

 					if ($datadetail['Kembali']=='Y')
 						$totalTx_Selesai++; 
 					else
 						$totalTx_Belum_Selesai++;  

 					$totalNilai_Setoran = $totalNilai_Setoran+$datadetail['Total'];

					
 					if ($datadetail['Ongkos_Svc']>0){ 
 						$TotalSudah_Bayar++;
 					}

 				}  
				$pos++;
		 		$sheet->setCellValue('A'.$pos, $datahaader['bulan']); 
				$sheet->getStyle('A'.$pos)->getFont()->setBold(true); 
				$sheet->setCellValue('C'.$pos, 'Transaksi'); 
				$sheet->getStyle('C'.$pos)->getFont()->setBold(true); 
				$sheet->setCellValue('D'.$pos, $totalTx); 
				$sheet->getStyle('D'.$pos)->getAlignment()->setHorizontal($alignment_left);
				$sheet->setCellValue('F'.$pos, 'Sdh Selesai');
				$sheet->getStyle('F'.$pos)->getFont()->setBold(true); 
				$sheet->setCellValue('G'.$pos, $totalTx_Selesai);
				$sheet->getStyle('G'.$pos)->getAlignment()->setHorizontal($alignment_left);
				$sheet->setCellValue('H'.$pos, 'Nilai Setoran');
				$sheet->getStyle('H'.$pos)->getFont()->setBold(true); 
				$sheet->setCellValue('I'.$pos, $totalNilai_Setoran); 
				$sheet->getStyle('I'.$pos)->getAlignment()->setHorizontal($alignment_left);  
				$sheet->setCellValue('J'.$pos, 'Bayar');
				$sheet->getStyle('J'.$pos)->getFont()->setBold(true); 
				$sheet->setCellValue('K'.$pos, $TotalSudah_Bayar);  
				$sheet->getStyle('K'.$pos)->getAlignment()->setHorizontal($alignment_left); 

				$pos++; 
				$sheet->setCellValue('F'.$pos, 'Blm Selesai');
				$sheet->getStyle('F'.$pos)->getFont()->setBold(true); 
				$sheet->setCellValue('G'.$pos, $totalTx_Belum_Selesai);  
				$sheet->getStyle('G'.$pos)->getAlignment()->setHorizontal($alignment_left); 

        		$SubtotalTx += $totalTx;
        		$SubtotalTx_Selesai += $totalTx_Selesai;
        		$SubtotalTx_Belum_Selesai += $totalTx_Belum_Selesai;
        		$SubTotalSudah_Bayar += $TotalSudah_Bayar; 
 
		        $page++;

		        if ($page==count($DataReportNotaService))
		        { 
					$pos++;
					$pos++;	
			 		$sheet->setCellValue('A'.$pos, 'Total Semuanya');
					$sheet->getStyle('A'.$pos)->getFont()->setBold(true); 
					$sheet->setCellValue('C'.$pos, 'Transaksi'); 
					$sheet->getStyle('C'.$pos)->getFont()->setBold(true); 
					$sheet->setCellValue('D'.$pos, $SubtotalTx); 
					$sheet->getStyle('D'.$pos)->getAlignment()->setHorizontal($alignment_left);
					$sheet->setCellValue('F'.$pos, 'Sdh Selesai');
					$sheet->getStyle('F'.$pos)->getFont()->setBold(true); 
					$sheet->setCellValue('G'.$pos, $SubtotalTx_Selesai);
					$sheet->getStyle('G'.$pos)->getAlignment()->setHorizontal($alignment_left);
					$sheet->setCellValue('H'.$pos, 'Blm Selesai');
					$sheet->getStyle('H'.$pos)->getFont()->setBold(true); 
					$sheet->setCellValue('I'.$pos, $SubtotalTx_Belum_Selesai);  
					$sheet->getStyle('I'.$pos)->getAlignment()->setHorizontal($alignment_left); 
					$sheet->setCellValue('J'.$pos, 'Bayar');
					$sheet->getStyle('J'.$pos)->getFont()->setBold(true); 
					$sheet->setCellValue('K'.$pos, $SubTotalSudah_Bayar);  
					$sheet->getStyle('K'.$pos)->getAlignment()->setHorizontal($alignment_left); 
 
					$Total_Transaksi_final = 0;
					$Sudah_Selesai_final = 0;
					$Belum_Selesai_final = 0;
					$Sudah_Bayar_final = 0; 
		        	$baris_ke = 0;
		        	$baris = "A";
					$pos++; 

				 	$sheet->setCellValue($baris.($pos+1), 'SUMMARY');
				 	$sheet->setCellValue($baris.($pos+2), 'Total Transaksi');
				 	$sheet->setCellValue($baris.($pos+3), 'Sudah Selesai');
					$sheet->setCellValue($baris.($pos+4), 'Belum Selesai');
					$sheet->setCellValue($baris.($pos+5), 'Bayar');
					$sheet->getStyle($baris.($pos+1))->getFont()->setBold(true); 

					foreach($DataReportNotaService as $datahaader)
					{     
						if ($baris_ke==0)
							$baris = "E";
						else
						if ($baris_ke==1)
							$baris = "F";
						else
						if ($baris_ke==2)
							$baris = "G";
						else
						if ($baris_ke==3)
							$baris = "H";  

				 		$sheet->setCellValue($baris.($pos+1), $datahaader['bulan']);
						$sheet->getStyle($baris.($pos+1))->getFont()->setBold(true); 
				 		$sheet->setCellValue($baris.($pos+2), $datahaader['Total_Transaksi']);
				 		$sheet->setCellValue($baris.($pos+3), $datahaader['Sudah_Selesai']);
				 		$sheet->setCellValue($baris.($pos+4), $datahaader['Belum_Selesai']);
				 		$sheet->setCellValue($baris.($pos+5), $datahaader['Sudah_Bayar']);
  
						$Total_Transaksi_final += $datahaader['Total_Transaksi'];
						$Sudah_Selesai_final += $datahaader['Sudah_Selesai'];
						$Belum_Selesai_final += $datahaader['Belum_Selesai'];
						$Sudah_Bayar_final += $datahaader['Sudah_Bayar'];  

						$baris_ke++;
					} 
					$baris = "J";
					$sheet->setCellValue($baris.($pos+1), 'TOTAL');
					$sheet->getStyle($baris.($pos+1))->getFont()->setBold(true); 
				 	$sheet->setCellValue($baris.($pos+2), $Total_Transaksi_final);
				 	$sheet->setCellValue($baris.($pos+3), $Sudah_Selesai_final);
				 	$sheet->setCellValue($baris.($pos+4), $Belum_Selesai_final);
				 	$sheet->setCellValue($baris.($pos+5), $Sudah_Bayar_final);
 
					$sheet->getStyle('A'.($pos+1).':J'.($pos+5))->getAlignment()->setHorizontal($alignment_center);
 
		        }  
 			}  
 
 
			$filename='Reportstatusnotaservice['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 
			exit(); 
		}   
 		//EXCEL 
	}							