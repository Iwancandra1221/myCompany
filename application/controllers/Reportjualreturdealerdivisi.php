<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class Reportjualreturdealerdivisi extends MY_Controller 
	{
		public $excel_flag = 0; 
		public function __construct()
		{
			parent::__construct();
			$this->load->model('BranchModel');
			$this->load->model('HelperModel');
			$this->load->helper('FormLibrary');
			$this->load->library('email');
			$this->load->library('excel');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		}
		
		public function index()
		{ 
			$api = 'APITES'; 

			$data['divisi'] = json_decode(file_get_contents($this->API_URL."/MsDivisi/GetListParentDiv?api=".$api));
			$data['wilayah'] = json_decode(file_get_contents($this->API_URL."/MsWilayah/GetWilayahHO?api=".$api));
			$data['partnertype'] = json_decode(file_get_contents($this->API_URL."/MsPartnerType/GetListPartnerTypev2?api=".$api));
			$data['tipefaktur'] = json_decode(file_get_contents($this->API_URL."/MsTipeFaktur/GetListTipeFaktur?api=".$api));
			$data['title'] = 'Laporan Jual - Retur Dealer (Divisi)';
			$data['formDest'] = "Reportjualreturdealerdivisi/Proses";
			
			$this->RenderView('ReportjualreturdealerdivisiView',$data);
		}
		
		public function Proses()
		{      
			//ini_set('max_execution_time', '1500');
	      	ini_set("pcre.backtrack_limit", "10000000");
	      	set_time_limit(60);

			$report = $_POST['report']; 
			$dp1 = $_POST['dp1']; 
			$dp2 = $_POST['dp2']; 
			$cboDivisi = $_POST['cboDivisi']; 
			$cboWilayah = $_POST['cboWilayah']; 
			$cboKategoriBarang = $_POST['cboKategoriBarang']; 
			$partnertype = $_POST['cboPartnerType']; 
			$cboTipeFaktur = $_POST['cboTipeFaktur']; 

			$p_start_date = date("d-M-Y", strtotime($dp1)); 
			$p_end_date = date("d-M-Y", strtotime($dp2));  

			$Kategori_brg = "";
			if ($cboKategoriBarang=="ALL") 
				$Kategori_brg = "PRODUK & SPAREPART"; 
			else if ($cboKategoriBarang=="P") 
				$Kategori_brg = "PRODUK"; 
			else 
				$Kategori_brg = "SPAREPART"; 
 
			if ($cboWilayah == "ALL") 
				$wilayah = "";
			else
				$wilayah = $cboWilayah;
			if ($cboDivisi == "ALL") 
				$divisi = "";
			else
				$divisi = rtrim($cboDivisi);
			if ($cboTipeFaktur == "ALL") 
				$tipe = "";
			else
				$tipe = $cboTipeFaktur;
			if ($cboKategoriBarang == "ALL") 
				$kategori = "";
			else
				$kategori = $cboKategoriBarang;  

	      	$api = 'APITES';  
        	$url = $_SESSION['conn']->AlamatWebService;
			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;

			// $svr = "10.1.0.99";
        	// $url = "http://localhost/"; 

			if (isset($_POST["btnPdf"])) 
				$this->excel_flag = 0; 
			else 
				$this->excel_flag = 1;  
 
			if ($report==1) 
				$url = $url.API_BKT."/Reportjualreturdealerdivisi/Report_611?api=APITES&kode_report=611_A&wilayah=".$wilayah."&divisi=".$divisi."&tipe=".$tipe."&kategori=".$kategori."&partnertype=".$partnertype."&tipe=".$tipe."&dp1=".$dp1."&dp2=".$dp2."&svr=".urlencode($svr)."&db=".urlencode($db);
			else if ($report==2) 
				$url = $url.API_BKT."/Reportjualreturdealerdivisi/Report_611?api=APITES&kode_report=611_B&wilayah=".$wilayah."&divisi=".$divisi."&tipe=".$tipe."&kategori=".$kategori."&partnertype=".$partnertype."&tipe=".$tipe."&dp1=".$dp1."&dp2=".$dp2."&svr=".urlencode($svr)."&db=".urlencode($db); 
			else if ($report==3) 
				$url = $url.API_BKT."/Reportjualreturdealerdivisi/Report_611?api=APITES&kode_report=611_C&wilayah=".$wilayah."&divisi=".$divisi."&tipe=".$tipe."&kategori=".$kategori."&partnertype=".$partnertype."&tipe=".$tipe."&dp1=".$dp1."&dp2=".$dp2."&svr=".urlencode($svr)."&db=".urlencode($db); 
			else if ($report==4) 
				$url = $url.API_BKT."/Reportjualreturdealerdivisi/Report_611?api=APITES&kode_report=611_D&wilayah=".$wilayah."&divisi=".$divisi."&tipe=".$tipe."&kategori=".$kategori."&partnertype=".$partnertype."&tipe=".$tipe."&dp1=".$dp1."&dp2=".$dp2."&svr=".urlencode($svr)."&db=".urlencode($db);
			else 
				$url = $url.API_BKT."/Reportjualreturdealerdivisi/Report_611?api=APITES&kode_report=611_E&wilayah=".$wilayah."&divisi=".$divisi."&tipe=".$tipe."&kategori=".$kategori."&partnertype=".$partnertype."&tipe=".$tipe."&dp1=".$dp1."&dp2=".$dp2."&svr=".urlencode($svr)."&db=".urlencode($db);
  
			$curl = curl_init();
			curl_setopt_array($curl, array(CURLOPT_URL => $url,CURLOPT_RETURNTRANSFER => true,CURLOPT_TIMEOUT => 1000));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl); 
			$result = json_decode($result,true); 
			if(count($result)==0){
				exit('Tidak ada data');
			} 		
			else{
				if ($report==1) 
				{
					if($this->excel_flag == 1)
						$this->Report_661_A_Excel($p_start_date,$p_end_date,$Kategori_brg,$result); 
					else
						$this->Report_661_A_Pdf($p_start_date,$p_end_date,$Kategori_brg,$result); 
				}
				else if ($report==2) 
				{
					if($this->excel_flag == 1)
						$this->Report_661_B_Excel($p_start_date,$p_end_date,$Kategori_brg,$result); 
					else
						$this->Report_661_B_Pdf($p_start_date,$p_end_date,$Kategori_brg,$result); 
				}
				else if ($report==3) 
				{
					if($this->excel_flag == 1)
						$this->Report_661_C_Excel($p_start_date,$p_end_date,$Kategori_brg,$result); 
					else
						$this->Report_661_C_Pdf($p_start_date,$p_end_date,$Kategori_brg,$result); 
				}
				else if ($report==4) 
				{
					if($this->excel_flag == 1)
						$this->Report_661_D_Excel($p_start_date,$p_end_date,$Kategori_brg,$result); 
					else
						$this->Report_661_D_Pdf($p_start_date,$p_end_date,$Kategori_brg,$result); 
				}
				else 
				{
					if($this->excel_flag == 1)
						$this->Report_661_E_Excel($p_start_date,$p_end_date,$Kategori_brg,$result); 
					else
						$this->Report_661_E_Pdf($p_start_date,$p_end_date,$Kategori_brg,$result); 
				}
	      	}

		}  

		//PDF
		public function Report_661_A_Pdf($p_start_date,$p_end_date,$Kategori_brg,$result)
		{ 
			$header='<table width="100%">';

	        $header.='<tr>'; 
	        $header.='<td>'.date('d-M-Y H:i:s').'</td>';
	        $header.='<td></td>';
	        $header.='<td align="right" width="200px" style="padding:5px">Halaman {PAGENO} / {nbpg}</td>'; 
	        $header.='</tr>';

	        $header.='<tr>';
	        $header.='<td colspan="3" align="center"><h2><b>LAPORAN PER PARENT DIVISI PER WILAYAH</b></h2></td>';
	        $header.='</tr><tr><td colspan="3" style="padding:5px"></td></tr><tr>';
	        $header.='<td>PERIODE : '.$p_start_date.' S/D '.$p_end_date.'</td>';
	        $header.='<td></td>';
	        $header.='<td align="center" width="200px" style="border:thin solid #000; padding:5px">'.$Kategori_brg.'</td>';
	        $header.='</tr>'; 
	        $header.='</table>';

	        $content='';
 
			$ptype = "";
			$group1 = "data_awal";

			$sum_total_jual_group1 = 0;
			$sum_total_retur_group1 = 0;
			$sum_total_group1 = 0; 

			$sum_total_jual_group2 = 0;
			$sum_total_retur_group2 = 0;
			$sum_total_group2 = 0; 

			$sum_total_jual_grandtotal = 0;
			$sum_total_retur_grandtotal = 0;
			$sum_total_grandtotal = 0; 
 
        	$content.='<table width="100%">';
			foreach($result as $hd){ 
				if ($ptype=="")
				{ 
					$ptype = $hd["Partner_Type"];   

					$content.='<tr>';
		            $content.='<td><b><i>Partner Type :</i> '.$hd["Partner_Type"].'</b></td>'; 
		            $content.='</tr>';

					$sum_total_jual_group1 = 0;
					$sum_total_retur_group1 = 0;
					$sum_total_group1 = 0; 

					$sum_total_jual_group2 = 0;
					$sum_total_retur_group2 = 0;
					$sum_total_group2 = 0; 
				}
				else
				{
					if ($ptype!=$hd["Partner_Type"])
					{  
						//hitung total Divisi
		            	$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr>'; 
		              	$content.='<tr><td><b>Total '.$group1.'</b></td>'; 
		              	$content.='<td align="right"><b>'.number_format($sum_total_jual_group1,0,",",".").'</b></td>';
		              	$content.='<td align="right"><b>'.number_format($sum_total_retur_group1,0,",",".").'</b></td>';
		              	$content.='<td align="right"><b>'.number_format($sum_total_group1,0,",",".").'</b></td></tr>';
 
						//hitung total Partner Type
						$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr>';
					 	$content.='<tr><td><b>Total '.$ptype.'</b></td>'; 
						$content.='<td align="right"><b>'.number_format($sum_total_jual_group2,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_retur_group2,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_group2,0,",",".").'</b></td></tr>';

              			$content.='<tr><td colspan="4" style="padding:10px;"></td></tr>';//enter sekali

						$ptype = $hd["Partner_Type"];

						$content.='<tr>';
			            $content.='<td><b><i>Partner Type :</i> '.$hd["Partner_Type"].'</b></td>'; 
			            $content.='</tr>';
						$group1 = "data_awal";

						$sum_total_jual_group1 = 0;
						$sum_total_retur_group1 = 0;
						$sum_total_group1 = 0; 

						$sum_total_jual_group2 = 0;
						$sum_total_retur_group2 = 0;
						$sum_total_group2 = 0; 
					}
				} 

				if ($group1=="data_awal")
				{
					$group1 = $hd["PARENTDIV"]; 

					$content.='<tr>';
			    	$content.='<td><b><i>Parent Divisi :</i> '.$hd["PARENTDIV"].'</b></td>'; 
			    	$content.='</tr>';

			    	$content.='<thead><tr>';
		            $content.='<td width="25%">WILAYAH</td>';  
		            $content.='<td width="15%" align="right">TOTAL JUAL</td>';
		            $content.='<td width="15%" align="right">TOTAL RETUR</td>';
		            $content.='<td width="15%" align="right">TOTAL</td>';
		            $content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr></thead><tbody>';

					$sum_total_jual_group1 = 0;
					$sum_total_retur_group1 = 0;
					$sum_total_group1 = 0; 
				}
				else
				{
					if (rtrim($group1)!=rtrim($hd["PARENTDIV"]))
					{  
						//hitung total Divisi
		            	$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr>';
		              	$content.='<tr><td><b>Total '.$group1.'</b></td>'; 
		              	$content.='<td align="right"><b>'.number_format($sum_total_jual_group1,0,",",".").'</b></td>';
		              	$content.='<td align="right"><b>'.number_format($sum_total_retur_group1,0,",",".").'</b></td>';
		              	$content.='<td align="right"><b>'.number_format($sum_total_group1,0,",",".").'</b></td></tr>';
              			$content.='<tr><td colspan="4" style="padding:10px;"></td></tr>';//enter sekali

						$group1 = $hd["PARENTDIV"]; 
						$content.='<tr>';
				    	$content.='<td><b><i>Parent Divisi :</i> '.$hd["PARENTDIV"].'</b></td>'; 
				    	$content.='</tr>';

				    	$content.='<thead><tr>';
			            $content.='<td width="25%">WILAYAH</td>';  
			            $content.='<td width="15%" align="right">TOTAL JUAL</td>';
			            $content.='<td width="15%" align="right">TOTAL RETUR</td>';
			            $content.='<td width="15%" align="right">TOTAL</td>';
			            $content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr></thead><tbody>';

						$sum_total_jual_group1 = 0;
						$sum_total_retur_group1 = 0;
						$sum_total_group1 = 0; 
					}
				}  

				$content.='<tr>';
	            $content.='<td>'.$hd['WILAYAH'].'</td>'; 
	            $content.='<td align="right">'.number_format($hd['TOTAL_JUAL'],0,",",".").'</td>';
	            $content.='<td align="right">'.number_format($hd['TOTAL_RETUR'],0,",",".").'</td>';
	            $content.='<td align="right">'.number_format($hd['TOTAL'],0,",",".").'</td>';
	            $content.='</tr>'; 

				$sum_total_jual_group1 += $hd["TOTAL_JUAL"];
				$sum_total_retur_group1 += $hd["TOTAL_RETUR"];
				$sum_total_group1 += $hd["TOTAL"]; 

				$sum_total_jual_group2 += $hd["TOTAL_JUAL"];
				$sum_total_retur_group2 += $hd["TOTAL_RETUR"];
				$sum_total_group2 += $hd["TOTAL"]; 

				$sum_total_jual_grandtotal += $hd["TOTAL_JUAL"];
				$sum_total_retur_grandtotal += $hd["TOTAL_RETUR"];
				$sum_total_grandtotal += $hd["TOTAL"]; 

 
			} 

			//hitung total Divisi
			$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td><b>Total '.$group1.'</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_jual_group1,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_retur_group1,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_group1,0,",",".").'</b></td></tr>';

			//hitung total Partner Type
			$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td><b>Total '.$ptype.'</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_jual_group2,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_retur_group2,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_group2,0,",",".").'</b></td></tr>';
				
			$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td><b>GRAND TOTAL</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_jual_grandtotal,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_retur_grandtotal,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_grandtotal,0,",",".").'</b></td></tr>';

        	$content.='</tbody></table>';

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
	        $mpdf->SetHTMLHeader($header,'','1');
	        $mpdf->WriteHTML($content);
	        $mpdf->Output();
		}  

		public function Report_661_B_Pdf($p_start_date,$p_end_date,$Kategori_brg,$result)
		{ 
			$header='<table width="100%">';

	        $header.='<tr>'; 
	        $header.='<td>'.date('d-M-Y H:i:s').'</td>';
	        $header.='<td></td>';
	        $header.='<td align="right" width="200px" style="padding:5px">Halaman {PAGENO} / {nbpg}</td>'; 
	        $header.='</tr>';

	        $header.='<tr>';
	        $header.='<td colspan="3" align="center"><h2><b>LAPORAN PER WILAYAH PER PARENT DIVISI</b></h2></td>';
	        $header.='</tr><tr><td colspan="3" style="padding:5px"></td></tr><tr>';
	        $header.='<td>PERIODE : '.$p_start_date.' S/D '.$p_end_date.'</td>';
	        $header.='<td></td>';
	        $header.='<td align="center" width="200px" style="border:thin solid #000; padding:5px">'.$Kategori_brg.'</td>';
	        $header.='</tr>'; 
	        $header.='</table>';

	        $content='';
 
			$ptype = "";
			$group1 = "data_awal";

			$sum_total_jual_group1 = 0;
			$sum_total_retur_group1 = 0;
			$sum_total_group1 = 0; 

			$sum_total_jual_group2 = 0;
			$sum_total_retur_group2 = 0;
			$sum_total_group2 = 0; 

			$sum_total_jual_grandtotal = 0;
			$sum_total_retur_grandtotal = 0;
			$sum_total_grandtotal = 0; 
 
        	$content.='<table width="100%">';
			foreach($result as $hd){ 
				if ($ptype=="")
				{ 
					$ptype = $hd["Partner_Type"];   

					$content.='<tr>';
		            $content.='<td><b><i>Partner Type :</i> '.$hd["Partner_Type"].'</b></td>'; 
		            $content.='</tr>';

					$sum_total_jual_group1 = 0;
					$sum_total_retur_group1 = 0;
					$sum_total_group1 = 0; 

					$sum_total_jual_group2 = 0;
					$sum_total_retur_group2 = 0;
					$sum_total_group2 = 0; 
				}
				else
				{
					if ($ptype!=$hd["Partner_Type"])
					{  
						//hitung total Divisi
		            	$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr>'; 
		 				$content.='<tr><td><b>Total '.$group1.'</b></td>'; 
		              	$content.='<td align="right"><b>'.number_format($sum_total_jual_group1,0,",",".").'</b></td>';
		              	$content.='<td align="right"><b>'.number_format($sum_total_retur_group1,0,",",".").'</b></td>';
		              	$content.='<td align="right"><b>'.number_format($sum_total_group1,0,",",".").'</b></td></tr>';
 
						//hitung total Partner Type
						$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr>';
					 	$content.='<tr><td><b>Total '.$ptype.'</b></td>'; 
						$content.='<td align="right"><b>'.number_format($sum_total_jual_group2,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_retur_group2,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_group2,0,",",".").'</b></td></tr>';

              			$content.='<tr><td colspan="4" style="padding:10px;"></td></tr>';//enter sekali

						$ptype = $hd["Partner_Type"];

						$content.='<tr>';
			            $content.='<td><b><i>Partner Type :</i> '.$hd["Partner_Type"].'</b></td>'; 
			            $content.='</tr>';
						$group1 = "data_awal";

						$sum_total_jual_group1 = 0;
						$sum_total_retur_group1 = 0;
						$sum_total_group1 = 0; 

						$sum_total_jual_group2 = 0;
						$sum_total_retur_group2 = 0;
						$sum_total_group2 = 0; 
					}
				} 

				if ($group1=="data_awal")
				{
					$group1 = $hd["WILAYAH"]; 

					$content.='<tr>';
			    	$content.='<td><b><i>Wilayah :</i> '.$hd["WILAYAH"].'</b></td>'; 
			    	$content.='</tr>';

			    	$content.='<thead><tr>';
		            $content.='<td width="25%">PARENT DIVISI</td>';  
		            $content.='<td width="15%" align="right">TOTAL JUAL</td>';
		            $content.='<td width="15%" align="right">TOTAL RETUR</td>';
		            $content.='<td width="15%" align="right">TOTAL</td>';
		            $content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr></thead><tbody>';

					$sum_total_jual_group1 = 0;
					$sum_total_retur_group1 = 0;
					$sum_total_group1 = 0; 
				}
				else
				{
					if (rtrim($group1)!=rtrim($hd["WILAYAH"]))
					{  
						//hitung total Divisi
		            	$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr>';
		 				$content.='<tr><td><b>Total '.$group1.'</b></td>'; 
		              	$content.='<td align="right"><b>'.number_format($sum_total_jual_group1,0,",",".").'</b></td>';
		              	$content.='<td align="right"><b>'.number_format($sum_total_retur_group1,0,",",".").'</b></td>';
		              	$content.='<td align="right"><b>'.number_format($sum_total_group1,0,",",".").'</b></td></tr>';
              			$content.='<tr><td colspan="4" style="padding:10px;"></td></tr>';//enter sekali

						$group1 = $hd["WILAYAH"]; 
						$content.='<tr>';
				    	$content.='<td><b><i>Wilayah :</i> '.$hd["WILAYAH"].'</b></td>'; 
				    	$content.='</tr>';

				    	$content.='<thead><tr>';
			            $content.='<td width="25%">PARENT DIVISI</td>';  
			            $content.='<td width="15%" align="right">TOTAL JUAL</td>';
			            $content.='<td width="15%" align="right">TOTAL RETUR</td>';
			            $content.='<td width="15%" align="right">TOTAL</td>';
			            $content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr></thead><tbody>';

						$sum_total_jual_group1 = 0;
						$sum_total_retur_group1 = 0;
						$sum_total_group1 = 0; 
					}
				}  

				$content.='<tr>';
	            $content.='<td>'.$hd['PARENTDIV'].'</td>'; 
	            $content.='<td align="right">'.number_format($hd['TOTAL_JUAL'],0,",",".").'</td>';
	            $content.='<td align="right">'.number_format($hd['TOTAL_RETUR'],0,",",".").'</td>';
	            $content.='<td align="right">'.number_format($hd['TOTAL'],0,",",".").'</td>';
	            $content.='</tr>'; 

				$sum_total_jual_group1 += $hd["TOTAL_JUAL"];
				$sum_total_retur_group1 += $hd["TOTAL_RETUR"];
				$sum_total_group1 += $hd["TOTAL"]; 

				$sum_total_jual_group2 += $hd["TOTAL_JUAL"];
				$sum_total_retur_group2 += $hd["TOTAL_RETUR"];
				$sum_total_group2 += $hd["TOTAL"]; 

				$sum_total_jual_grandtotal += $hd["TOTAL_JUAL"];
				$sum_total_retur_grandtotal += $hd["TOTAL_RETUR"];
				$sum_total_grandtotal += $hd["TOTAL"]; 

 
			} 

			//hitung total Divisi
			$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td><b>Total '.$group1.'</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_jual_group1,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_retur_group1,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_group1,0,",",".").'</b></td></tr>';

			//hitung total Partner Type
			$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td><b>Total '.$ptype.'</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_jual_group2,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_retur_group2,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_group2,0,",",".").'</b></td></tr>';
				
			$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td><b>GRAND TOTAL</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_jual_grandtotal,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_retur_grandtotal,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_grandtotal,0,",",".").'</b></td></tr>';

        	$content.='</tbody></table>';

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
	        $mpdf->SetHTMLHeader($header,'','1');
	        $mpdf->WriteHTML($content);
	        $mpdf->Output();
		}  

		public function Report_661_C_Pdf($p_start_date,$p_end_date,$Kategori_brg,$result)
		{ 
			$header='<table width="100%">';

	        $header.='<tr>'; 
	        $header.='<td>'.date('d-M-Y H:i:s').'</td>';
	        $header.='<td></td>';
	        $header.='<td align="right" width="200px" style="padding:5px">Halaman {PAGENO} / {nbpg}</td>'; 
	        $header.='</tr>';

	        $header.='<tr>';
	        $header.='<td colspan="3" align="center"><h2><b>LAPORAN PER DEALER SUMMARY</b></h2></td>';
	        $header.='</tr><tr><td colspan="3" style="padding:5px"></td></tr><tr>';
	        $header.='<td>PERIODE : '.$p_start_date.' S/D '.$p_end_date.'</td>';
	        $header.='<td></td>';
	        $header.='<td align="center" width="200px" style="border:thin solid #000; padding:5px">'.$Kategori_brg.'</td>';
	        $header.='</tr>'; 
	        $header.='</table>';

	        $content='';
 
			$ptype = "";
			$group1 = "data_awal";

			$sum_total_jual_group1 = 0;
			$sum_total_retur_group1 = 0;
			$sum_total_group1 = 0; 

			$sum_total_jual_group2 = 0;
			$sum_total_retur_group2 = 0;
			$sum_total_group2 = 0; 

			$sum_total_jual_grandtotal = 0;
			$sum_total_retur_grandtotal = 0;
			$sum_total_grandtotal = 0; 
 
        	$content.='<table width="100%">';
			foreach($result as $hd){ 
				if ($ptype=="")
				{ 
					$ptype = $hd["Partner_Type"];   

					$content.='<tr>';
		            $content.='<td  colspan="2"><b><i>Partner Type :</i> '.$hd["Partner_Type"].'</b></td>'; 
		            $content.='</tr>';

					$sum_total_jual_group1 = 0;
					$sum_total_retur_group1 = 0;
					$sum_total_group1 = 0; 

					$sum_total_jual_group2 = 0;
					$sum_total_retur_group2 = 0;
					$sum_total_group2 = 0; 
				}
				else
				{
					if ($ptype!=$hd["Partner_Type"])
					{  
						//hitung total Divisi
		            	$content.='</tr><tr><td colspan="5" style="border-top:thin solid #000;"></td></tr>'; 
		 				$content.='<tr><td colspan="2"><b>Total '.$group1.'</b></td>'; 
		              	$content.='<td align="right"><b>'.number_format($sum_total_jual_group1,0,",",".").'</b></td>';
		              	$content.='<td align="right"><b>'.number_format($sum_total_retur_group1,0,",",".").'</b></td>';
		              	$content.='<td align="right"><b>'.number_format($sum_total_group1,0,",",".").'</b></td></tr>';
 
						//hitung total Partner Type
						$content.='</tr><tr><td colspan="5" style="border-top:thin solid #000;"></td></tr>';
					 	$content.='<tr><td colspan="2"><b>Total '.$ptype.'</b></td>'; 
						$content.='<td align="right"><b>'.number_format($sum_total_jual_group2,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_retur_group2,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_group2,0,",",".").'</b></td></tr>';

              			$content.='<tr><td colspan="5" style="padding:10px;"></td></tr>';//enter sekali

						$ptype = $hd["Partner_Type"];

						$content.='<tr>';
			            $content.='<td colspan="2"><b><i>Partner Type :</i> '.$hd["Partner_Type"].'</b></td>'; 
			            $content.='</tr>';
						$group1 = "data_awal";

						$sum_total_jual_group1 = 0;
						$sum_total_retur_group1 = 0;
						$sum_total_group1 = 0; 

						$sum_total_jual_group2 = 0;
						$sum_total_retur_group2 = 0;
						$sum_total_group2 = 0; 
					}
				} 

				if ($group1=="data_awal")
				{
					$group1 = $hd["WILAYAH"]; 

					$content.='<tr>';
			    	$content.='<td colspan="2"><b><i>Wilayah :</i> '.$hd["WILAYAH"].'</b></td>'; 
			    	$content.='</tr>';

			    	$content.='<thead><tr>';
		        	$content.='<td width="10%">KD PLG</td>';  
		        	$content.='<td width="45%">Nama PLG</td>';   
		        	$content.='<td width="15%" align="right">TOTAL JUAL</td>';
		    		$content.='<td width="15%" align="right">TOTAL RETUR</td>';
		    		$content.='<td width="15%" align="right">TOTAL</td>';
		            $content.='</tr><tr><td colspan="5" style="border-top:thin solid #000;"></td></tr></thead><tbody>';

					$sum_total_jual_group1 = 0;
					$sum_total_retur_group1 = 0;
					$sum_total_group1 = 0; 
				}
				else
				{
					if (rtrim($group1)!=rtrim($hd["WILAYAH"]))
					{  
						//hitung total Divisi
		            	$content.='</tr><tr><td colspan="5" style="border-top:thin solid #000;"></td></tr>';
		 				$content.='<tr><td colspan="2"><b>Total '.$group1.'</b></td>'; 
		              	$content.='<td align="right"><b>'.number_format($sum_total_jual_group1,0,",",".").'</b></td>';
		              	$content.='<td align="right"><b>'.number_format($sum_total_retur_group1,0,",",".").'</b></td>';
		              	$content.='<td align="right"><b>'.number_format($sum_total_group1,0,",",".").'</b></td></tr>';
              			$content.='<tr><td colspan="5" style="padding:10px;"></td></tr>';//enter sekali

						$group1 = $hd["WILAYAH"]; 
						$content.='<tr>';
				    	$content.='<td colspan="2"><b><i>Wilayah :</i> '.$hd["WILAYAH"].'</b></td>'; 
				    	$content.='</tr>';

				    	$content.='<thead><tr>';
		            	$content.='<td width="10%">KD PLG</td>';  
		            	$content.='<td width="45%">Nama PLG</td>';   
		            	$content.='<td width="15%" align="right">TOTAL JUAL</td>';
		           	 	$content.='<td width="15%" align="right">TOTAL RETUR</td>';
		            	$content.='<td width="15%" align="right">TOTAL</td>';
			            $content.='</tr><tr><td colspan="5" style="border-top:thin solid #000;"></td></tr></thead><tbody>';

						$sum_total_jual_group1 = 0;
						$sum_total_retur_group1 = 0;
						$sum_total_group1 = 0; 
					}
				}  

				$content.='<tr>';
	            $content.='<td>'.$hd['KD_PLG'].'</td>'; 
	            $content.='<td>'.$hd['NM_PLG'].'</td>'; 
	            $content.='<td align="right">'.number_format($hd['TOTAL_JUAL'],0,",",".").'</td>';
	            $content.='<td align="right">'.number_format($hd['TOTAL_RETUR'],0,",",".").'</td>';
	            $content.='<td align="right">'.number_format($hd['TOTAL'],0,",",".").'</td>';
	            $content.='</tr>'; 

				$sum_total_jual_group1 += $hd["TOTAL_JUAL"];
				$sum_total_retur_group1 += $hd["TOTAL_RETUR"];
				$sum_total_group1 += $hd["TOTAL"]; 

				$sum_total_jual_group2 += $hd["TOTAL_JUAL"];
				$sum_total_retur_group2 += $hd["TOTAL_RETUR"];
				$sum_total_group2 += $hd["TOTAL"]; 

				$sum_total_jual_grandtotal += $hd["TOTAL_JUAL"];
				$sum_total_retur_grandtotal += $hd["TOTAL_RETUR"];
				$sum_total_grandtotal += $hd["TOTAL"]; 

 
			} 

			//hitung total Divisi
			$content.='</tr><tr><td colspan="5" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td colspan="2"><b>Total '.$group1.'</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_jual_group1,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_retur_group1,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_group1,0,",",".").'</b></td></tr>';

			//hitung total Partner Type
			$content.='</tr><tr><td colspan="5" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td colspan="2"><b>Total '.$ptype.'</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_jual_group2,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_retur_group2,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_group2,0,",",".").'</b></td></tr>';
				
			$content.='</tr><tr><td colspan="5" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td colspan="2"><b>GRAND TOTAL</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_jual_grandtotal,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_retur_grandtotal,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_grandtotal,0,",",".").'</b></td></tr>';

        	$content.='</tbody></table>';

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
	        $mpdf->SetHTMLHeader($header,'','1');
	        $mpdf->WriteHTML($content);
	        $mpdf->Output();
		} 

		public function Report_661_D_Pdf($p_start_date,$p_end_date,$Kategori_brg,$result)
		{ 
			$header='<table width="100%">';

	        $header.='<tr>'; 
	        $header.='<td>'.date('d-M-Y H:i:s').'</td>';
	        $header.='<td></td>';
	        $header.='<td align="right" width="200px" style="padding:5px">Halaman {PAGENO} / {nbpg}</td>'; 
	        $header.='</tr>';

	        $header.='<tr>';
	        $header.='<td colspan="3" align="center"><h2><b>LAPORAN PER PARENT DIVISI PER PARTNER TYPE</b></h2></td>';
	        $header.='</tr><tr><td colspan="3" style="padding:5px"></td></tr><tr>';
	        $header.='<td>PERIODE : '.$p_start_date.' S/D '.$p_end_date.'</td>';
	        $header.='<td></td>';
	        $header.='<td align="center" width="200px" style="border:thin solid #000; padding:5px">'.$Kategori_brg.'</td>';
	        $header.='</tr>'; 
	        $header.='</table>';

	        $content='';
 
			$div = "data_awal";  

			$sum_total_jual_group2 = 0;
			$sum_total_retur_group2 = 0;
			$sum_total_group2 = 0; 

			$sum_total_jual_grandtotal = 0;
			$sum_total_retur_grandtotal = 0;
			$sum_total_grandtotal = 0; 
 
        	$content.='<table width="100%">';
			foreach($result as $hd){ 
				if ($div=="data_awal")
				{ 
					$div = $hd["PARENTDIV"];   

					$content.='<tr>';
		            $content.='<td><b><i>Parent Divisi :</i> '.$hd["PARENTDIV"].'</b></td>'; 
		            $content.='</tr>'; 

					$content.='<thead><tr>';
			    	$content.='<td width="25%">PARTNER TYPE</td>';  
			    	$content.='<td width="15%" align="right">TOTAL JUAL</td>';
			    	$content.='<td width="15%" align="right">TOTAL RETUR</td>';
			    	$content.='<td width="15%" align="right">TOTAL</td>';
			   		$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr></thead><tbody>';

					$sum_total_jual_group2 = 0;
					$sum_total_retur_group2 = 0;
					$sum_total_group2 = 0; 
				}
				else
				{
					if ($div!=$hd["PARENTDIV"])
					{   
						//hitung total Partner Type
						$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr>';
					 	$content.='<tr><td><b>Total '.$div.'</b></td>'; 
						$content.='<td align="right"><b>'.number_format($sum_total_jual_group2,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_retur_group2,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_group2,0,",",".").'</b></td></tr>';

              			$content.='<tr><td colspan="4" style="padding:10px;"></td></tr>';//enter sekali

						$div = $hd["PARENTDIV"];

						$content.='<tr>';
			            $content.='<td><b><i>Parent Divisi :</i> '.$hd["PARENTDIV"].'</b></td>'; 
			            $content.='</tr>';
						$group1 = "data_awal"; 

				    	$content.='<thead><tr>';
			            $content.='<td width="25%">PARTNER TYPE</td>';  
			            $content.='<td width="15%" align="right">TOTAL JUAL</td>';
			            $content.='<td width="15%" align="right">TOTAL RETUR</td>';
			            $content.='<td width="15%" align="right">TOTAL</td>';
			            $content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr></thead><tbody>';

						$sum_total_jual_group2 = 0;
						$sum_total_retur_group2 = 0;
						$sum_total_group2 = 0; 
					}
				}  

				$content.='<tr>';
	            $content.='<td>'.$hd['Partner_Type'].'</td>'; 
	            $content.='<td align="right">'.number_format($hd['TOTAL_JUAL'],0,",",".").'</td>';
	            $content.='<td align="right">'.number_format($hd['TOTAL_RETUR'],0,",",".").'</td>';
	            $content.='<td align="right">'.number_format($hd['TOTAL'],0,",",".").'</td>';
	            $content.='</tr>';  

				$sum_total_jual_group2 += $hd["TOTAL_JUAL"];
				$sum_total_retur_group2 += $hd["TOTAL_RETUR"];
				$sum_total_group2 += $hd["TOTAL"]; 

				$sum_total_jual_grandtotal += $hd["TOTAL_JUAL"];
				$sum_total_retur_grandtotal += $hd["TOTAL_RETUR"];
				$sum_total_grandtotal += $hd["TOTAL"]; 

 
			}  
			//hitung total Partner Type
			$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td><b>Total '.$div.'</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_jual_group2,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_retur_group2,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_group2,0,",",".").'</b></td></tr>';
				
			$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td><b>GRAND TOTAL</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_jual_grandtotal,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_retur_grandtotal,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_grandtotal,0,",",".").'</b></td></tr>';

        	$content.='</tbody></table>';

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
	        $mpdf->SetHTMLHeader($header,'','1');
	        $mpdf->WriteHTML($content);
	        $mpdf->Output();
		}  

		public function Report_661_E_Pdf($p_start_date,$p_end_date,$Kategori_brg,$result)
		{ 
			$header='<table width="100%">';

	        $header.='<tr>'; 
	        $header.='<td>'.date('d-M-Y H:i:s').'</td>';
	        $header.='<td></td>';
	        $header.='<td align="right" width="200px" style="padding:5px">Halaman {PAGENO} / {nbpg}</td>'; 
	        $header.='</tr>';

	        $header.='<tr>';
	        $header.='<td colspan="3" align="center"><h2><b>LAPORAN PER PARTNER TYPE PER PARENT DIVISI</b></h2></td>';
	        $header.='</tr><tr><td colspan="3" style="padding:5px"></td></tr><tr>';
	        $header.='<td>PERIODE : '.$p_start_date.' S/D '.$p_end_date.'</td>';
	        $header.='<td></td>';
	        $header.='<td align="center" width="200px" style="border:thin solid #000; padding:5px">'.$Kategori_brg.'</td>';
	        $header.='</tr>'; 
	        $header.='</table>';

	        $content='';
 
			$ptype = "";  

			$sum_total_jual_group2 = 0;
			$sum_total_retur_group2 = 0;
			$sum_total_group2 = 0; 

			$sum_total_jual_grandtotal = 0;
			$sum_total_retur_grandtotal = 0;
			$sum_total_grandtotal = 0; 
 
        	$content.='<table width="100%">';
			foreach($result as $hd){ 
				if ($ptype=="")
				{ 
					$ptype = $hd["Partner_Type"];   

					$content.='<tr>';
		            $content.='<td><b><i>Partner Type :</i> '.$hd["Partner_Type"].'</b></td>'; 
		            $content.='</tr>'; 

					$content.='<thead><tr>';
			    	$content.='<td width="25%">PARENT DIVISI</td>';  
			    	$content.='<td width="15%" align="right">TOTAL JUAL</td>';
			    	$content.='<td width="15%" align="right">TOTAL RETUR</td>';
			    	$content.='<td width="15%" align="right">TOTAL</td>';
			   		$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr></thead><tbody>';
					$sum_total_jual_group2 = 0;
					$sum_total_retur_group2 = 0;
					$sum_total_group2 = 0; 
				}
				else
				{
					if ($ptype!=$hd["Partner_Type"])
					{   
						//hitung total Partner Type
						$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr>';
					 	$content.='<tr><td><b>Total '.$ptype.'</b></td>'; 
						$content.='<td align="right"><b>'.number_format($sum_total_jual_group2,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_retur_group2,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_group2,0,",",".").'</b></td></tr>';

              			$content.='<tr><td colspan="4" style="padding:10px;"></td></tr>';//enter sekali

						$ptype = $hd["Partner_Type"];

						$content.='<tr>';
			            $content.='<td><b><i>Partner Type :</i> '.$hd["Partner_Type"].'</b></td>'; 
			            $content.='</tr>';
						$group1 = "data_awal"; 

				    	$content.='<thead><tr>';
			            $content.='<td width="25%">PARENT DIVISI</td>';  
			            $content.='<td width="15%" align="right">TOTAL JUAL</td>';
			            $content.='<td width="15%" align="right">TOTAL RETUR</td>';
			            $content.='<td width="15%" align="right">TOTAL</td>';
			            $content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr></thead><tbody>';

						$sum_total_jual_group2 = 0;
						$sum_total_retur_group2 = 0;
						$sum_total_group2 = 0; 
					}
				}  

				$content.='<tr>';
	            $content.='<td>'.$hd['PARENTDIV'].'</td>'; 
	            $content.='<td align="right">'.number_format($hd['TOTAL_JUAL'],0,",",".").'</td>';
	            $content.='<td align="right">'.number_format($hd['TOTAL_RETUR'],0,",",".").'</td>';
	            $content.='<td align="right">'.number_format($hd['TOTAL'],0,",",".").'</td>';
	            $content.='</tr>';  

				$sum_total_jual_group2 += $hd["TOTAL_JUAL"];
				$sum_total_retur_group2 += $hd["TOTAL_RETUR"];
				$sum_total_group2 += $hd["TOTAL"]; 

				$sum_total_jual_grandtotal += $hd["TOTAL_JUAL"];
				$sum_total_retur_grandtotal += $hd["TOTAL_RETUR"];
				$sum_total_grandtotal += $hd["TOTAL"]; 

 
			}  
			//hitung total Partner Type
			$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td><b>Total '.$ptype.'</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_jual_group2,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_retur_group2,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_group2,0,",",".").'</b></td></tr>';
				
			$content.='</tr><tr><td colspan="4" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td><b>GRAND TOTAL</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_jual_grandtotal,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_retur_grandtotal,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_grandtotal,0,",",".").'</b></td></tr>';

        	$content.='</tbody></table>';

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
	        $mpdf->SetHTMLHeader($header,'','1');
	        $mpdf->WriteHTML($content);
	        $mpdf->Output();
		}
		//PDF


		//EXCEL

		public function Report_661_A_Excel($p_start_date,$p_end_date,$Kategori_brg,$result)
		{   
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID; 
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
  
			$sheet->setTitle("Jual_Return_Dealer");
 
			$sheet->setCellValue('A1', 'LAPORAN PER PARENT DIVISI PER WILAYAH');
			$sheet->mergeCells('A1:F1');
			$sheet->getStyle('A1:F1')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A1')->getFont()->setSize(20); 
			$sheet->setCellValue('A2', 'Print Date : '.date('d-M-Y H:i:s'));
			$sheet->setCellValue('A3', 'Periode : '.$p_start_date.' sd '.$p_end_date);
			$sheet->setCellValue('A4', 'Kategori : '.$Kategori_brg);

			$pos = 6;
			$sheet->getColumnDimension('A')->setWidth(15); 
			$sheet->getColumnDimension('B')->setWidth(15); 
			$sheet->getColumnDimension('C')->setWidth(15); 
			$sheet->getColumnDimension('D')->setWidth(15); 
			$sheet->getColumnDimension('E')->setWidth(15); 
			$sheet->getColumnDimension('F')->setWidth(15);  
			$sheet->getStyle("A".$pos.":F".$pos)->getFont()->setBold(true); 

			$sheet->setCellValue('A'.$pos, 'Partner Type');
			$sheet->setCellValue('B'.$pos, 'Parent Divisi');
			$sheet->setCellValue('C'.$pos, 'Wilayah');
			$sheet->setCellValue('D'.$pos, 'Total Jual');
			$sheet->setCellValue('E'.$pos, 'Total Retur');
			$sheet->setCellValue('F'.$pos, 'Total');  
			$sheet->getStyle('A'.$pos.':F'.$pos)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('FFFACD');

			$i = 0;   

			$ptype = "";
			$divisi = "data_awal";

			$sum_total_jual_group1 = 0;
			$sum_total_retur_group1 = 0;
			$sum_total_group1 = 0; 

			$sum_total_jual_group2 = 0;
			$sum_total_retur_group2 = 0;
			$sum_total_group2 = 0; 

			$sum_total_jual_grandtotal = 0;
			$sum_total_retur_grandtotal = 0;
			$sum_total_grandtotal = 0; 


			foreach($result as $hd){ 
				if ($ptype=="")
				{ 
					$ptype = $hd["Partner_Type"]; 

					$sum_total_jual_group1 = 0;
					$sum_total_retur_group1 = 0;
					$sum_total_group1 = 0; 

					$sum_total_jual_group2 = 0;
					$sum_total_retur_group2 = 0;
					$sum_total_group2 = 0; 
				}
				else
				{
					if ($ptype!=$hd["Partner_Type"])
					{  
							$pos++;
							$sheet->setCellValue('A'.$pos, 'TOTAL '.$divisi); 
							$sheet->setCellValue('D'.$pos, $sum_total_jual_group1);
							$sheet->setCellValue('E'.$pos, $sum_total_retur_group1);
							$sheet->setCellValue('F'.$pos, $sum_total_group1); 
							$sheet->getStyle("A".$pos.":F".$pos)->getFont()->setBold(true);
							$pos++;
							$sheet->setCellValue('A'.$pos, 'TOTAL '.$ptype); 
							$sheet->setCellValue('D'.$pos, $sum_total_jual_group2);
							$sheet->setCellValue('E'.$pos, $sum_total_retur_group2);
							$sheet->setCellValue('F'.$pos, $sum_total_group2); 
							$sheet->getStyle("A".$pos.":F".$pos)->getFont()->setBold(true);  
						$divisi = "data_awal";
						$ptype = $hd["Partner_Type"];  
						$sum_total_jual_group1 = 0;
						$sum_total_retur_group1 = 0;
						$sum_total_group1 = 0; 

						$sum_total_jual_group2 = 0;
						$sum_total_retur_group2 = 0;
						$sum_total_group2 = 0; 
					}
				} 

				if ($divisi=="data_awal")
				{
					$divisi = $hd["PARENTDIV"]; 

					$sum_total_jual_group1 = 0;
					$sum_total_retur_group1 = 0;
					$sum_total_group1 = 0; 
				}
				else
				{
					if (rtrim($divisi)!=rtrim($hd["PARENTDIV"]))
					{
						 
							$pos++;
							$sheet->setCellValue('A'.$pos, 'TOTAL '.$divisi); 
							$sheet->setCellValue('D'.$pos, $sum_total_jual_group1);
							$sheet->setCellValue('E'.$pos, $sum_total_retur_group1);
							$sheet->setCellValue('F'.$pos, $sum_total_group1); 
							$sheet->getStyle("A".$pos.":F".$pos)->getFont()->setBold(true);  
						$divisi = $hd["PARENTDIV"]; 
						$sum_total_jual_group1 = 0;
						$sum_total_retur_group1 = 0;
						$sum_total_group1 = 0; 
					}
				}  
				$sum_total_jual_group1 += $hd["TOTAL_JUAL"];
				$sum_total_retur_group1 += $hd["TOTAL_RETUR"];
				$sum_total_group1 += $hd["TOTAL"]; 

				$sum_total_jual_group2 += $hd["TOTAL_JUAL"];
				$sum_total_retur_group2 += $hd["TOTAL_RETUR"];
				$sum_total_group2 += $hd["TOTAL"]; 

				$sum_total_jual_grandtotal += $hd["TOTAL_JUAL"];
				$sum_total_retur_grandtotal += $hd["TOTAL_RETUR"];
				$sum_total_grandtotal += $hd["TOTAL"];   
					$pos++;
					$sheet->setCellValue('A'.$pos, $hd["Partner_Type"]);
					$sheet->setCellValue('B'.$pos, $hd["PARENTDIV"]);
					$sheet->setCellValue('C'.$pos, $hd["WILAYAH"]);
					$sheet->setCellValue('D'.$pos, $hd["TOTAL_JUAL"]);
					$sheet->setCellValue('E'.$pos, $hd["TOTAL_RETUR"]);
					$sheet->setCellValue('F'.$pos, $hd["TOTAL"]);  

			} 
						 
							$pos++;
							$sheet->setCellValue('A'.$pos, 'TOTAL '.$divisi); 
							$sheet->setCellValue('D'.$pos, $sum_total_jual_group1);
							$sheet->setCellValue('E'.$pos, $sum_total_retur_group1);
							$sheet->setCellValue('F'.$pos, $sum_total_group1); 
							$sheet->getStyle("A".$pos.":F".$pos)->getFont()->setBold(true);
							$pos++;
							$sheet->setCellValue('A'.$pos, 'TOTAL '.$ptype); 
							$sheet->setCellValue('D'.$pos, $sum_total_jual_group2);
							$sheet->setCellValue('E'.$pos, $sum_total_retur_group2);
							$sheet->setCellValue('F'.$pos, $sum_total_group2); 
							$sheet->getStyle("A".$pos.":F".$pos)->getFont()->setBold(true);
							$pos++;
							$sheet->setCellValue('A'.$pos, 'GRAND TOTAL'); 
							$sheet->setCellValue('D'.$pos, $sum_total_jual_grandtotal);
							$sheet->setCellValue('E'.$pos, $sum_total_retur_grandtotal);
							$sheet->setCellValue('F'.$pos, $sum_total_grandtotal); 
							$sheet->getStyle("A".$pos.":F".$pos)->getFont()->setBold(true); 
				$filename='LaporanJualReturDealerDivisi['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 
				exit(); 
		} 

		public function Report_661_B_Excel($p_start_date,$p_end_date,$Kategori_brg,$result)
		{   
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID; 
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
  
			$sheet->setTitle("Jual_Return_Dealer");

			$sheet->setCellValue('A1', 'LAPORAN PER WILAYAH PER PARENT DIVISI');
			$sheet->mergeCells('A1:F1');
			$sheet->getStyle('A1:F1')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A1')->getFont()->setSize(20); 
			$sheet->setCellValue('A2', 'Print Date : '.date('d-M-Y H:i:s'));
			$sheet->setCellValue('A3', 'Periode : '.$p_start_date.' sd '.$p_end_date);
			$sheet->setCellValue('A4', 'Kategori : '.$Kategori_brg);

			$pos = 6;
			$sheet->getColumnDimension('A')->setWidth(15); 
			$sheet->getColumnDimension('B')->setWidth(15); 
			$sheet->getColumnDimension('C')->setWidth(15); 
			$sheet->getColumnDimension('D')->setWidth(15); 
			$sheet->getColumnDimension('E')->setWidth(15); 
			$sheet->getColumnDimension('F')->setWidth(15);  
			$sheet->getStyle("A".$pos.":F".$pos)->getFont()->setBold(true); 

			$sheet->setCellValue('A'.$pos, 'Partner Type');
			$sheet->setCellValue('B'.$pos, 'Wilayah');
			$sheet->setCellValue('C'.$pos, 'Parent Divisi'); 
			$sheet->setCellValue('D'.$pos, 'Total Jual');
			$sheet->setCellValue('E'.$pos, 'Total Retur');
			$sheet->setCellValue('F'.$pos, 'Total');  
			$sheet->getStyle('A'.$pos.':F'.$pos)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('FFFACD');

			$i = 0;   

			$ptype = "";
			$will = "data_awal";

			$sum_total_jual_group1 = 0;
			$sum_total_retur_group1 = 0;
			$sum_total_group1 = 0; 

			$sum_total_jual_group2 = 0;
			$sum_total_retur_group2 = 0;
			$sum_total_group2 = 0; 

			$sum_total_jual_grandtotal = 0;
			$sum_total_retur_grandtotal = 0;
			$sum_total_grandtotal = 0; 


			foreach($result as $hd){ 
				if ($ptype=="")
				{ 
					$ptype = $hd["Partner_Type"]; 

					$sum_total_jual_group1 = 0;
					$sum_total_retur_group1 = 0;
					$sum_total_group1 = 0; 

					$sum_total_jual_group2 = 0;
					$sum_total_retur_group2 = 0;
					$sum_total_group2 = 0; 
				}
				else
				{
					if ($ptype!=$hd["Partner_Type"])
					{  
							$pos++;
							$sheet->setCellValue('A'.$pos, 'TOTAL '.$will); 
							$sheet->setCellValue('D'.$pos, $sum_total_jual_group1);
							$sheet->setCellValue('E'.$pos, $sum_total_retur_group1);
							$sheet->setCellValue('F'.$pos, $sum_total_group1); 
							$sheet->getStyle("A".$pos.":F".$pos)->getFont()->setBold(true);
							$pos++;
							$sheet->setCellValue('A'.$pos, 'TOTAL '.$ptype); 
							$sheet->setCellValue('D'.$pos, $sum_total_jual_group2);
							$sheet->setCellValue('E'.$pos, $sum_total_retur_group2);
							$sheet->setCellValue('F'.$pos, $sum_total_group2); 
							$sheet->getStyle("A".$pos.":F".$pos)->getFont()->setBold(true);  
						$will = "data_awal";
						$ptype = $hd["Partner_Type"];  
						$sum_total_jual_group1 = 0;
						$sum_total_retur_group1 = 0;
						$sum_total_group1 = 0; 

						$sum_total_jual_group2 = 0;
						$sum_total_retur_group2 = 0;
						$sum_total_group2 = 0; 
					}
				} 

				if ($will=="data_awal")
				{
					$will = $hd["WILAYAH"]; 

					$sum_total_jual_group1 = 0;
					$sum_total_retur_group1 = 0;
					$sum_total_group1 = 0; 
				}
				else
				{
					if (rtrim($will)!=rtrim($hd["WILAYAH"]))
					{
						 
							$pos++;
							$sheet->setCellValue('A'.$pos, 'TOTAL '.$will); 
							$sheet->setCellValue('D'.$pos, $sum_total_jual_group1);
							$sheet->setCellValue('E'.$pos, $sum_total_retur_group1);
							$sheet->setCellValue('F'.$pos, $sum_total_group1); 
							$sheet->getStyle("A".$pos.":F".$pos)->getFont()->setBold(true);  
						$will = $hd["WILAYAH"]; 
						$sum_total_jual_group1 = 0;
						$sum_total_retur_group1 = 0;
						$sum_total_group1 = 0; 
					}
				}  
				$sum_total_jual_group1 += $hd["TOTAL_JUAL"];
				$sum_total_retur_group1 += $hd["TOTAL_RETUR"];
				$sum_total_group1 += $hd["TOTAL"]; 

				$sum_total_jual_group2 += $hd["TOTAL_JUAL"];
				$sum_total_retur_group2 += $hd["TOTAL_RETUR"];
				$sum_total_group2 += $hd["TOTAL"]; 

				$sum_total_jual_grandtotal += $hd["TOTAL_JUAL"];
				$sum_total_retur_grandtotal += $hd["TOTAL_RETUR"];
				$sum_total_grandtotal += $hd["TOTAL"];   
					$pos++;
					$sheet->setCellValue('A'.$pos, $hd["Partner_Type"]);
					$sheet->setCellValue('B'.$pos, $hd["WILAYAH"]);
					$sheet->setCellValue('C'.$pos, $hd["PARENTDIV"]);
					$sheet->setCellValue('D'.$pos, $hd["TOTAL_JUAL"]);
					$sheet->setCellValue('E'.$pos, $hd["TOTAL_RETUR"]);
					$sheet->setCellValue('F'.$pos, $hd["TOTAL"]);  

			} 
						 
							$pos++;
							$sheet->setCellValue('A'.$pos, 'TOTAL '.$will); 
							$sheet->setCellValue('D'.$pos, $sum_total_jual_group1);
							$sheet->setCellValue('E'.$pos, $sum_total_retur_group1);
							$sheet->setCellValue('F'.$pos, $sum_total_group1); 
							$sheet->getStyle("A".$pos.":F".$pos)->getFont()->setBold(true);
							$pos++;
							$sheet->setCellValue('A'.$pos, 'TOTAL '.$ptype); 
							$sheet->setCellValue('D'.$pos, $sum_total_jual_group2);
							$sheet->setCellValue('E'.$pos, $sum_total_retur_group2);
							$sheet->setCellValue('F'.$pos, $sum_total_group2); 
							$sheet->getStyle("A".$pos.":F".$pos)->getFont()->setBold(true);
							$pos++;
							$sheet->setCellValue('A'.$pos, 'GRAND TOTAL'); 
							$sheet->setCellValue('D'.$pos, $sum_total_jual_grandtotal);
							$sheet->setCellValue('E'.$pos, $sum_total_retur_grandtotal);
							$sheet->setCellValue('F'.$pos, $sum_total_grandtotal); 
							$sheet->getStyle("A".$pos.":F".$pos)->getFont()->setBold(true); 
				$filename='LaporanJualReturDealerDivisi['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 
				exit(); 
		} 
 
		public function Report_661_C_Excel($p_start_date,$p_end_date,$Kategori_brg,$result)
		{   
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID; 
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
  
			$sheet->setTitle("Jual_Return_Dealer");
 			$sheet->setCellValue('A1', 'LAPORAN PER DEALER SUMMARY');
			$sheet->mergeCells('A1:G1');
			$sheet->getStyle('A1:G1')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A1')->getFont()->setSize(20); 
			$sheet->setCellValue('A2', 'Print Date : '.date('d-M-Y H:i:s'));
			$sheet->setCellValue('A3', 'Periode : '.$p_start_date.' sd '.$p_end_date);
			$sheet->setCellValue('A4', 'Kategori : '.$Kategori_brg);

			$pos = 6;
			$sheet->getColumnDimension('A')->setWidth(30); 
			$sheet->getColumnDimension('B')->setWidth(30); 
			$sheet->getColumnDimension('C')->setWidth(15); 
			$sheet->getColumnDimension('D')->setWidth(40); 
			$sheet->getColumnDimension('E')->setWidth(15); 
			$sheet->getColumnDimension('F')->setWidth(15);  
			$sheet->getColumnDimension('G')->setWidth(15);  
			$sheet->getStyle("A".$pos.":G".$pos)->getFont()->setBold(true); 

			$sheet->setCellValue('A'.$pos, 'Partner Type');
			$sheet->setCellValue('B'.$pos, 'Wilayah');
			$sheet->setCellValue('C'.$pos, 'Kd Plg'); 
			$sheet->setCellValue('D'.$pos, 'Nama Plg'); 
			$sheet->setCellValue('E'.$pos, 'Total Jual');
			$sheet->setCellValue('F'.$pos, 'Total Retur');
			$sheet->setCellValue('G'.$pos, 'Total');  
			$sheet->getStyle('A'.$pos.':G'.$pos)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('FFFACD');

			$i = 0;   

			$ptype = "";
			$will = "data_awal";

			$sum_total_jual_group1 = 0;
			$sum_total_retur_group1 = 0;
			$sum_total_group1 = 0; 

			$sum_total_jual_group2 = 0;
			$sum_total_retur_group2 = 0;
			$sum_total_group2 = 0; 

			$sum_total_jual_grandtotal = 0;
			$sum_total_retur_grandtotal = 0;
			$sum_total_grandtotal = 0; 


			foreach($result as $hd){ 
				if ($ptype=="")
				{ 
					$ptype = $hd["Partner_Type"]; 

					$sum_total_jual_group1 = 0;
					$sum_total_retur_group1 = 0;
					$sum_total_group1 = 0; 

					$sum_total_jual_group2 = 0;
					$sum_total_retur_group2 = 0;
					$sum_total_group2 = 0; 
				}
				else
				{
					if ($ptype!=$hd["Partner_Type"])
					{  
							$pos++;
							$sheet->setCellValue('A'.$pos, 'TOTAL '.$will); 
							$sheet->setCellValue('E'.$pos, $sum_total_jual_group1);
							$sheet->setCellValue('F'.$pos, $sum_total_retur_group1);
							$sheet->setCellValue('G'.$pos, $sum_total_group1); 
							$sheet->getStyle("A".$pos.":G".$pos)->getFont()->setBold(true);
							$pos++;
							$sheet->setCellValue('A'.$pos, 'TOTAL '.$ptype); 
							$sheet->setCellValue('E'.$pos, $sum_total_jual_group2);
							$sheet->setCellValue('F'.$pos, $sum_total_retur_group2);
							$sheet->setCellValue('G'.$pos, $sum_total_group2); 
							$sheet->getStyle("A".$pos.":G".$pos)->getFont()->setBold(true);  
						$will = "data_awal";
						$ptype = $hd["Partner_Type"];  
						$sum_total_jual_group1 = 0;
						$sum_total_retur_group1 = 0;
						$sum_total_group1 = 0; 

						$sum_total_jual_group2 = 0;
						$sum_total_retur_group2 = 0;
						$sum_total_group2 = 0; 
					}
				} 

				if ($will=="data_awal")
				{
					$will = $hd["WILAYAH"]; 

					$sum_total_jual_group1 = 0;
					$sum_total_retur_group1 = 0;
					$sum_total_group1 = 0; 
				}
				else
				{
					if (rtrim($will)!=rtrim($hd["WILAYAH"]))
					{ 
							$pos++;
							$sheet->setCellValue('A'.$pos, 'TOTAL '.$will); 
							$sheet->setCellValue('E'.$pos, $sum_total_jual_group1);
							$sheet->setCellValue('F'.$pos, $sum_total_retur_group1);
							$sheet->setCellValue('G'.$pos, $sum_total_group1); 
							$sheet->getStyle("A".$pos.":G".$pos)->getFont()->setBold(true);  
						$will = $hd["WILAYAH"]; 
						$sum_total_jual_group1 = 0;
						$sum_total_retur_group1 = 0;
						$sum_total_group1 = 0; 
					}
				}  
				$sum_total_jual_group1 += $hd["TOTAL_JUAL"];
				$sum_total_retur_group1 += $hd["TOTAL_RETUR"];
				$sum_total_group1 += $hd["TOTAL"]; 

				$sum_total_jual_group2 += $hd["TOTAL_JUAL"];
				$sum_total_retur_group2 += $hd["TOTAL_RETUR"];
				$sum_total_group2 += $hd["TOTAL"]; 

				$sum_total_jual_grandtotal += $hd["TOTAL_JUAL"];
				$sum_total_retur_grandtotal += $hd["TOTAL_RETUR"];
				$sum_total_grandtotal += $hd["TOTAL"];   
					$pos++;
					$sheet->setCellValue('A'.$pos, $hd["Partner_Type"]);
					$sheet->setCellValue('B'.$pos, $hd["WILAYAH"]);
					$sheet->setCellValue('C'.$pos, $hd["KD_PLG"]);
					$sheet->setCellValue('D'.$pos, $hd["NM_PLG"]);  
					$sheet->setCellValue('E'.$pos, $hd["TOTAL_JUAL"]);
					$sheet->setCellValue('F'.$pos, $hd["TOTAL_RETUR"]);
					$sheet->setCellValue('G'.$pos, $hd["TOTAL"]);  

			} 
						 
							$pos++;
							$sheet->setCellValue('A'.$pos, 'TOTAL '.$will); 
							$sheet->setCellValue('E'.$pos, $sum_total_jual_group1);
							$sheet->setCellValue('F'.$pos, $sum_total_retur_group1);
							$sheet->setCellValue('G'.$pos, $sum_total_group1); 
							$sheet->getStyle("A".$pos.":G".$pos)->getFont()->setBold(true);
							$pos++;
							$sheet->setCellValue('A'.$pos, 'TOTAL '.$ptype); 
							$sheet->setCellValue('E'.$pos, $sum_total_jual_group2);
							$sheet->setCellValue('F'.$pos, $sum_total_retur_group2);
							$sheet->setCellValue('G'.$pos, $sum_total_group2); 
							$sheet->getStyle("A".$pos.":G".$pos)->getFont()->setBold(true);
							$pos++;
							$sheet->setCellValue('A'.$pos, 'GRAND TOTAL'); 
							$sheet->setCellValue('E'.$pos, $sum_total_jual_grandtotal);
							$sheet->setCellValue('F'.$pos, $sum_total_retur_grandtotal);
							$sheet->setCellValue('G'.$pos, $sum_total_grandtotal); 
							$sheet->getStyle("A".$pos.":G".$pos)->getFont()->setBold(true); 
				$filename='LaporanJualReturDealerDivisi['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 
				exit(); 
		} 

		public function Report_661_D_Excel($p_start_date,$p_end_date,$Kategori_brg,$result)
		{   
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID; 
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
  
			$sheet->setTitle("Jual_Return_Dealer");
 
			$sheet->setCellValue('A1', 'LAPORAN PER PARENT DIVISI PER PARTNER TYPE');
			$sheet->mergeCells('A1:E1');
			$sheet->getStyle('A1:E1')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A1')->getFont()->setSize(20); 
			$sheet->setCellValue('A2', 'Print Date : '.date('d-M-Y H:i:s'));
			$sheet->setCellValue('A3', 'Periode : '.$p_start_date.' sd '.$p_end_date);
			$sheet->setCellValue('A4', 'Kategori : '.$Kategori_brg);

			$pos = 6;
			$sheet->getColumnDimension('A')->setWidth(15); 
			$sheet->getColumnDimension('B')->setWidth(15); 
			$sheet->getColumnDimension('C')->setWidth(15); 
			$sheet->getColumnDimension('D')->setWidth(15); 
			$sheet->getColumnDimension('E')->setWidth(15);  
			$sheet->getStyle("A".$pos.":E".$pos)->getFont()->setBold(true); 

			$sheet->setCellValue('A'.$pos, 'Parent Divisi'); 
			$sheet->setCellValue('B'.$pos, 'Partner Type'); 
			$sheet->setCellValue('C'.$pos, 'Total Jual');
			$sheet->setCellValue('D'.$pos, 'Total Retur');
			$sheet->setCellValue('E'.$pos, 'Total');  
			$sheet->getStyle('A'.$pos.':E'.$pos)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('FFFACD');

			$i = 0;   

			$ptype = "";
			$divisi = "data_awal";

			$sum_total_jual_group1 = 0;
			$sum_total_retur_group1 = 0;
			$sum_total_group1 = 0; 
  
			$sum_total_jual_grandtotal = 0;
			$sum_total_retur_grandtotal = 0;
			$sum_total_grandtotal = 0; 


			foreach($result as $hd){  

				if ($divisi=="data_awal")
				{
					$divisi = $hd["PARENTDIV"]; 

					$sum_total_jual_group1 = 0;
					$sum_total_retur_group1 = 0;
					$sum_total_group1 = 0; 
				}
				else
				{
					if (rtrim($divisi)!=rtrim($hd["PARENTDIV"]))
					{
						 
						$pos++;
						$sheet->setCellValue('A'.$pos, 'TOTAL '.$divisi); 
						$sheet->setCellValue('C'.$pos, $sum_total_jual_group1);
						$sheet->setCellValue('D'.$pos, $sum_total_retur_group1);
						$sheet->setCellValue('E'.$pos, $sum_total_group1); 
						$sheet->getStyle("A".$pos.":E".$pos)->getFont()->setBold(true);  
						$divisi = $hd["PARENTDIV"]; 
						$sum_total_jual_group1 = 0;
						$sum_total_retur_group1 = 0;
						$sum_total_group1 = 0; 
					}
				}  
				$sum_total_jual_group1 += $hd["TOTAL_JUAL"];
				$sum_total_retur_group1 += $hd["TOTAL_RETUR"];
				$sum_total_group1 += $hd["TOTAL"];  

				$sum_total_jual_grandtotal += $hd["TOTAL_JUAL"];
				$sum_total_retur_grandtotal += $hd["TOTAL_RETUR"];
				$sum_total_grandtotal += $hd["TOTAL"];   
				$pos++;
				$sheet->setCellValue('A'.$pos, $hd["PARENTDIV"]);
				$sheet->setCellValue('B'.$pos, $hd["Partner_Type"]); 
				$sheet->setCellValue('C'.$pos, $hd["TOTAL_JUAL"]);
				$sheet->setCellValue('D'.$pos, $hd["TOTAL_RETUR"]);
				$sheet->setCellValue('E'.$pos, $hd["TOTAL"]);  

			}  
			$pos++;
			$sheet->setCellValue('A'.$pos, 'TOTAL '.$divisi); 
			$sheet->setCellValue('C'.$pos, $sum_total_jual_group1);
			$sheet->setCellValue('D'.$pos, $sum_total_retur_group1);
			$sheet->setCellValue('E'.$pos, $sum_total_group1); 
			$sheet->getStyle("A".$pos.":E".$pos)->getFont()->setBold(true);
			$pos++; 
			$sheet->setCellValue('A'.$pos, 'GRAND TOTAL'); 
			$sheet->setCellValue('C'.$pos, $sum_total_jual_grandtotal);
			$sheet->setCellValue('D'.$pos, $sum_total_retur_grandtotal);
			$sheet->setCellValue('E'.$pos, $sum_total_grandtotal); 
			$sheet->getStyle("A".$pos.":E".$pos)->getFont()->setBold(true); 
			$filename='LaporanJualReturDealerDivisi['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 
			exit(); 
		} 

		public function Report_661_E_Excel($p_start_date,$p_end_date,$Kategori_brg,$result)
		{   
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID; 
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
  
			$sheet->setTitle("Jual_Return_Dealer");
 
			$sheet->setCellValue('A1', 'LAPORAN PER PARTNER TYPE PER PARENT DIVISI');
			$sheet->mergeCells('A1:E1');
			$sheet->getStyle('A1:E1')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A1')->getFont()->setSize(20); 
			$sheet->setCellValue('A2', 'Print Date : '.date('d-M-Y H:i:s'));
			$sheet->setCellValue('A3', 'Periode : '.$p_start_date.' sd '.$p_end_date);
			$sheet->setCellValue('A4', 'Kategori : '.$Kategori_brg);

			$pos = 6;
			$sheet->getColumnDimension('A')->setWidth(15); 
			$sheet->getColumnDimension('B')->setWidth(15); 
			$sheet->getColumnDimension('C')->setWidth(15); 
			$sheet->getColumnDimension('D')->setWidth(15); 
			$sheet->getColumnDimension('E')->setWidth(15);  
			$sheet->getStyle("A".$pos.":E".$pos)->getFont()->setBold(true); 

			$sheet->setCellValue('A'.$pos, 'Partner Type'); 
			$sheet->setCellValue('B'.$pos, 'Parent Divisi'); 
			$sheet->setCellValue('C'.$pos, 'Total Jual');
			$sheet->setCellValue('D'.$pos, 'Total Retur');
			$sheet->setCellValue('E'.$pos, 'Total');  
			$sheet->getStyle('A'.$pos.':E'.$pos)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('FFFACD');

			$i = 0;   

			$ptype = "";
			$will = "data_awal";

			$sum_total_jual_group1 = 0;
			$sum_total_retur_group1 = 0;
			$sum_total_group1 = 0; 
  
			$sum_total_jual_grandtotal = 0;
			$sum_total_retur_grandtotal = 0;
			$sum_total_grandtotal = 0; 


			foreach($result as $hd){  

				if ($will=="data_awal")
				{
					$will = $hd["Partner_Type"]; 

					$sum_total_jual_group1 = 0;
					$sum_total_retur_group1 = 0;
					$sum_total_group1 = 0; 
				}
				else
				{
					if (rtrim($will)!=rtrim($hd["Partner_Type"]))
					{
						 
						$pos++;
						$sheet->setCellValue('A'.$pos, 'TOTAL '.$will); 
						$sheet->setCellValue('C'.$pos, $sum_total_jual_group1);
						$sheet->setCellValue('D'.$pos, $sum_total_retur_group1);
						$sheet->setCellValue('E'.$pos, $sum_total_group1); 
						$sheet->getStyle("A".$pos.":E".$pos)->getFont()->setBold(true);  
						$will = $hd["Partner_Type"]; 
						$sum_total_jual_group1 = 0;
						$sum_total_retur_group1 = 0;
						$sum_total_group1 = 0; 
					}
				}  
				$sum_total_jual_group1 += $hd["TOTAL_JUAL"];
				$sum_total_retur_group1 += $hd["TOTAL_RETUR"];
				$sum_total_group1 += $hd["TOTAL"];  

				$sum_total_jual_grandtotal += $hd["TOTAL_JUAL"];
				$sum_total_retur_grandtotal += $hd["TOTAL_RETUR"];
				$sum_total_grandtotal += $hd["TOTAL"];   
				$pos++;
				$sheet->setCellValue('A'.$pos, $hd["Partner_Type"]);
				$sheet->setCellValue('B'.$pos, $hd["PARENTDIV"]); 
				$sheet->setCellValue('C'.$pos, $hd["TOTAL_JUAL"]);
				$sheet->setCellValue('D'.$pos, $hd["TOTAL_RETUR"]);
				$sheet->setCellValue('E'.$pos, $hd["TOTAL"]);  

			}  
			$pos++;
			$sheet->setCellValue('A'.$pos, 'TOTAL '.$will); 
			$sheet->setCellValue('C'.$pos, $sum_total_jual_group1);
			$sheet->setCellValue('D'.$pos, $sum_total_retur_group1);
			$sheet->setCellValue('E'.$pos, $sum_total_group1); 
			$sheet->getStyle("A".$pos.":E".$pos)->getFont()->setBold(true);
			$pos++; 
			$sheet->setCellValue('A'.$pos, 'GRAND TOTAL'); 
			$sheet->setCellValue('C'.$pos, $sum_total_jual_grandtotal);
			$sheet->setCellValue('D'.$pos, $sum_total_retur_grandtotal);
			$sheet->setCellValue('E'.$pos, $sum_total_grandtotal); 
			$sheet->getStyle("A".$pos.":E".$pos)->getFont()->setBold(true); 
			$filename='LaporanJualReturDealerDivisi['.date('Ymd').']'; //save our workbook as this file name
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