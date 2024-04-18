<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class Reportomzetnettodivisi extends MY_Controller 
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

			$data['divisi'] = json_decode(file_get_contents($this->API_URL."/MsDivisi/GetListDivisi?api=".$api));
			$data['wilayah'] = json_decode(file_get_contents($this->API_URL."/MsWilayah/GetWilayahHO?api=".$api));
			$data['partnertype'] = json_decode(file_get_contents($this->API_URL."/MsPartnerType/GetListPartnerTypev2?api=".$api));
			$data['tipefaktur'] = json_decode(file_get_contents($this->API_URL."/MsTipeFaktur/GetListTipeFaktur?api=".$api));
			$data['title'] = 'Laporan Omzet Netto Divisi Summary';
			$data['formDest'] = "Reportomzetnettodivisi/Proses";
			
			$this->RenderView('ReportomzetnettodivisiView',$data);
		}
		
		public function Proses()
		{  
			//ini_set('max_execution_time', '300');
			ini_set("pcre.backtrack_limit", "5000000"); 
	      	set_time_limit(60);

			$data = array(); 
			   
			$dp1 = $_POST['dp1']; 
			$dp2 = $_POST['dp2']; 
			$cboDivisi = $_POST['cboDivisi']; 
			$cboWilayah = $_POST['cboWilayah']; 
			$cboKategoriBarang = $_POST['cboKategoriBarang']; 
			$cboPartnerType = $_POST['cboPartnerType']; 
			$cboTipeFaktur = $_POST['cboTipeFaktur']; 
			$report = $_POST['report']; 
  
			if (isset($_POST["btnPdf"])) 
				$this->excel_flag = 0; 
			else 
				$this->excel_flag = 1;  
 
			$api = 'APITES';  
			$p_start_date = date("d-M-Y", strtotime($dp1)); 
			$p_end_date = date("d-M-Y", strtotime($dp2));   
			$url = $_SESSION["conn"]->AlamatWebService;
			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;
			// $svr = "10.1.0.99";
        	// $url = "http://localhost/"; 

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
			if ($cboPartnerType == "ALL") 
				$pertnertype = "" ;
			else
				$pertnertype = $cboPartnerType;
 
			if ($report==1) 
				$url = $url.API_BKT."/Reportomzetnettodivisi/PROSES_OMZET_NETTO_PER_DIVISI?api=APITES&wilayah=".$wilayah."&divisi=".$divisi."&tipe=".$tipe."&kategori=".$kategori."&pertnertype=".$pertnertype."&dp1=".$dp1."&dp2=".$dp2."&svr=".urlencode($svr)."&db=".urlencode($db);
			else 
				$url = $url.API_BKT."/Reportomzetnettodivisi/PROSES_OMZET_NETTO_PER_WILAYAH?api=APITES&wilayah=".$wilayah."&divisi=".$divisi."&tipe=".$tipe."&kategori=".$kategori."&pertnertype=".$pertnertype."&dp1=".$dp1."&dp2=".$dp2."&svr=".urlencode($svr)."&db=".urlencode($db);

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60, 
			));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl); 
			$result = json_decode($result,true); 
			if(count($result)==0){
				exit('Tidak ada data');
			}
			else
			{
				if ($report==1) 
				{
					if($this->excel_flag == 1)
						$this->Report_603_A_Excel($p_start_date,$p_end_date,$Kategori_brg,$result); 
					else
						$this->Report_603_A_Pdf($p_start_date,$p_end_date,$Kategori_brg,$result);  
				}	
				else
				{
					if($this->excel_flag == 1)
						$this->Report_603_B_Excel($p_start_date,$p_end_date,$Kategori_brg,$result); 
					else
						$this->Report_603_B_Pdf($p_start_date,$p_end_date,$Kategori_brg,$result);  
				}

			}			 

		}
 
		//PDF
		public function Report_603_A_Pdf($p_start_date,$p_end_date,$Kategori_brg,$result)
		{ 
			$header='<table width="100%">';

	        $header.='<tr>'; 
	        $header.='<td>'.date('d-M-Y H:i:s').'</td>';
	        $header.='<td></td>';
	        $header.='<td align="right" width="200px" style="padding:5px">Halaman {PAGENO} / {nbpg}</td>'; 
	        $header.='</tr>';

	        $header.='<tr>';
	        $header.='<td colspan="3" align="center"><h2><b>LAPORAN OMZET NETTO PER DIVISI PER WILAYAH</b></h2></td>';
	        $header.='</tr><tr><td colspan="3" style="padding:5px"></td></tr><tr>';
	        $header.='<td>PERIODE : '.$p_start_date.' S/D '.$p_end_date.'</td>';
	        $header.='<td></td>';
	        $header.='<td align="center" width="200px" style="border:thin solid #000; padding:5px">'.$Kategori_brg.'</td>';
	        $header.='</tr>'; 
	        $header.='</table>';

	        $content='';
 
			$ptype = "";
			$group1 = "data_awal";
 
			$sum_total_Jual_per_divisi = 0;
			$sum_total_Rb_per_divisi = 0;
			$sum_total_Rc_per_divisi = 0;
			$sum_total_Disc_per_divisi = 0;
			$sum_total_Nettto_per_divisi = 0;

			$sum_total_Jual_per_partner_type = 0;
			$sum_total_Rb_per_partner_type = 0;
			$sum_total_Rc_per_partner_type = 0;
			$sum_total_Disc_per_partner_type = 0;
			$sum_total_Nettto_per_partner_type = 0;

			$sum_total_Jual_per_GrandTotal = 0;
			$sum_total_Rb_per_GrandTotal = 0;
			$sum_total_Rc_per_GrandTotal = 0;
			$sum_total_Disc_per_GrandTotal = 0;
			$sum_total_Nettto_per_GrandTotal = 0;
 
        	$content.='<table width="100%">';
			foreach($result as $hd){ 
				if ($ptype=="")
				{ 
					$ptype = $hd["Partner_Type"];   

					$content.='<tr>';
		            $content.='<td><b><i>Partner Type :</i> '.$hd["Partner_Type"].'</b></td>'; 
		            $content.='</tr>';

					$sum_total_Jual_per_divisi = 0;
					$sum_total_Rb_per_divisi = 0;
					$sum_total_Rc_per_divisi = 0;
					$sum_total_Disc_per_divisi = 0;
					$sum_total_Nettto_per_divisi = 0;

					$sum_total_Jual_per_partner_type = 0;
					$sum_total_Rb_per_partner_type = 0;
					$sum_total_Rc_per_partner_type = 0;
					$sum_total_Disc_per_partner_type = 0;
					$sum_total_Nettto_per_partner_type = 0;
				}
				else
				{
					if ($ptype!=$hd["Partner_Type"])
					{   
						//hitung total Divisi
						$content.='</tr><tr><td colspan="6" style="border-top:thin solid #000;"></td></tr>';
					 	$content.='<tr><td><b>Total '.$group1.'</b></td>'; 
						$content.='<td align="right"><b>'.number_format($sum_total_Jual_per_divisi,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_Rb_per_divisi,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_Rc_per_divisi,0,",",".").'</b></td></tr>';
						$content.='<td align="right"><b>'.number_format($sum_total_Disc_per_divisi,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_Nettto_per_divisi,0,",",".").'</b></td></tr>';

						//hitung total Partner Type
						$content.='</tr><tr><td colspan="6" style="border-top:thin solid #000;"></td></tr>';
					 	$content.='<tr><td><b>Total '.$ptype.'</b></td>'; 
						$content.='<td align="right"><b>'.number_format($sum_total_Jual_per_partner_type,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_Rb_per_partner_type,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_Rc_per_partner_type,0,",",".").'</b></td></tr>';
						$content.='<td align="right"><b>'.number_format($sum_total_Disc_per_partner_type,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_Nettto_per_partner_type,0,",",".").'</b></td></tr>';

              			$content.='<tr><td colspan="6" style="padding:10px;"></td></tr>';//enter sekali

						$ptype = $hd["Partner_Type"];

						$content.='<tr>';
			            $content.='<td><b><i>Partner Type :</i> '.$hd["Partner_Type"].'</b></td>'; 
			            $content.='</tr>';
						$group1 = "data_awal";

						$sum_total_Jual_per_divisi = 0;
						$sum_total_Rb_per_divisi = 0;
						$sum_total_Rc_per_divisi = 0;
						$sum_total_Disc_per_divisi = 0;
						$sum_total_Nettto_per_divisi = 0;

						$sum_total_Jual_per_partner_type = 0;
						$sum_total_Rb_per_partner_type = 0;
						$sum_total_Rc_per_partner_type = 0;
						$sum_total_Disc_per_partner_type = 0;
						$sum_total_Nettto_per_partner_type = 0;
					}
				} 

				if ($group1=="data_awal")
				{
					$group1 = $hd["Divisi"]; 

					$content.='<tr>';
			    	$content.='<td><b><i>Divisi :</i> '.$hd["Divisi"].'</b></td>'; 
			    	$content.='</tr>';

			    	$content.='<thead><tr>';
		            $content.='<td width="25%">Wilayah</td>';  
			    	$content.='<td width="15%" align="right">Total Jual</td>';
			    	$content.='<td width="15%" align="right">Total RB</td>';
			   		$content.='<td width="15%" align="right">Total RC</td>';
			    	$content.='<td width="15%" align="right">Total Disc</td>';
			    	$content.='<td width="15%" align="right">Omzet Netto</td>';
		            $content.='</tr><tr><td colspan="6" style="border-top:thin solid #000;"></td></tr></thead><tbody>';

					$sum_total_Jual_per_divisi = 0;
					$sum_total_Rb_per_divisi = 0;
					$sum_total_Rc_per_divisi = 0;
					$sum_total_Disc_per_divisi = 0;
					$sum_total_Nettto_per_divisi = 0; 
				}
				else
				{
					if (rtrim($group1)!=rtrim($hd["Divisi"]))
					{   
						//hitung total Divisi
						$content.='</tr><tr><td colspan="6" style="border-top:thin solid #000;"></td></tr>';
					 	$content.='<tr><td><b>Total '.$group1.'</b></td>'; 
						$content.='<td align="right"><b>'.number_format($sum_total_Jual_per_divisi,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_Rb_per_divisi,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_Rc_per_divisi,0,",",".").'</b></td></tr>';
						$content.='<td align="right"><b>'.number_format($sum_total_Disc_per_divisi,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_Nettto_per_divisi,0,",",".").'</b></td></tr>';
              			$content.='<tr><td colspan="6" style="padding:10px;"></td></tr>';//enter sekali

						$group1 = $hd["Divisi"]; 
						$content.='<tr>';
				    	$content.='<td><b><i>Divisi :</i> '.$hd["Divisi"].'</b></td>'; 
				    	$content.='</tr>';

				    	$content.='<thead><tr>';
			            $content.='<td width="25%">Wilayah</td>';  
			            $content.='<td width="15%" align="right">Total Jual</td>';
			            $content.='<td width="15%" align="right">Total RB</td>';
			            $content.='<td width="15%" align="right">Total RC</td>';
			            $content.='<td width="15%" align="right">Total Disc</td>';
			            $content.='<td width="15%" align="right">Omzet Netto</td>';
			            $content.='</tr><tr><td colspan="6" style="border-top:thin solid #000;"></td></tr></thead><tbody>';

						$sum_total_Jual_per_divisi = 0;
						$sum_total_Rb_per_divisi = 0;
						$sum_total_Rc_per_divisi = 0;
						$sum_total_Disc_per_divisi = 0;
						$sum_total_Nettto_per_divisi = 0; 
					}
				}  

				$content.='<tr>';
	            $content.='<td>'.$hd['Wilayah'].'</td>'; 
	            $content.='<td align="right">'.number_format($hd['Total_Jual'],0,",",".").'</td>';
	            $content.='<td align="right">'.number_format($hd['Total_RB'],0,",",".").'</td>';
	            $content.='<td align="right">'.number_format($hd['Total_RC'],0,",",".").'</td>';
	            $content.='<td align="right">'.number_format($hd['Total_Disc'],0,",",".").'</td>';
	            $content.='<td align="right">'.number_format($hd['Omzet_Netto'],0,",",".").'</td>';
	            $content.='</tr>'; 

				$sum_total_Jual_per_divisi += $hd["Total_Jual"];
				$sum_total_Rb_per_divisi += $hd["Total_RB"];
				$sum_total_Rc_per_divisi += $hd["Total_RC"];
				$sum_total_Disc_per_divisi += $hd["Total_Disc"];
				$sum_total_Nettto_per_divisi += $hd["Omzet_Netto"];

				$sum_total_Jual_per_partner_type += $hd["Total_Jual"];
				$sum_total_Rb_per_partner_type += $hd["Total_RB"];
				$sum_total_Rc_per_partner_type += $hd["Total_RC"];
				$sum_total_Disc_per_partner_type += $hd["Total_Disc"];
				$sum_total_Nettto_per_partner_type += $hd["Omzet_Netto"];

				$sum_total_Jual_per_GrandTotal += $hd["Total_Jual"];
				$sum_total_Rb_per_GrandTotal += $hd["Total_RB"];
				$sum_total_Rc_per_GrandTotal += $hd["Total_RC"];
				$sum_total_Disc_per_GrandTotal += $hd["Total_Disc"];
				$sum_total_Nettto_per_GrandTotal += $hd["Omzet_Netto"];

 
			} 

			//hitung total Divisi
			$content.='</tr><tr><td colspan="6" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td><b>Total '.$group1.'</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_Jual_per_divisi,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_Rb_per_divisi,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_Rc_per_divisi,0,",",".").'</b></td></tr>';
			$content.='<td align="right"><b>'.number_format($sum_total_Disc_per_divisi,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_Nettto_per_divisi,0,",",".").'</b></td></tr>';

			//hitung total Partner Type
			$content.='</tr><tr><td colspan="6" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td><b>Total '.$ptype.'</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_Jual_per_partner_type,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_Rb_per_partner_type,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_Rc_per_partner_type,0,",",".").'</b></td></tr>';
			$content.='<td align="right"><b>'.number_format($sum_total_Disc_per_partner_type,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_Nettto_per_partner_type,0,",",".").'</b></td></tr>';
				
			$content.='</tr><tr><td colspan="6" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td><b>GRAND TOTAL</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_Jual_per_GrandTotal,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_Rb_per_GrandTotal,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_Rc_per_GrandTotal,0,",",".").'</b></td></tr>';
			$content.='<td align="right"><b>'.number_format($sum_total_Disc_per_GrandTotal,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_Nettto_per_GrandTotal,0,",",".").'</b></td></tr>';

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
 
		public function Report_603_B_Pdf($p_start_date,$p_end_date,$Kategori_brg,$result)
		{ 
			$header='<table width="100%">';

	        $header.='<tr>'; 
	        $header.='<td>'.date('d-M-Y H:i:s').'</td>';
	        $header.='<td></td>';
	        $header.='<td align="right" width="200px" style="padding:5px">Halaman {PAGENO} / {nbpg}</td>'; 
	        $header.='</tr>';

	        $header.='<tr>';
	        $header.='<td colspan="3" align="center"><h2><b>LAPORAN OMZET NETTO PER WILAYAH PER DIVISI</b></h2></td>';
	        $header.='</tr><tr><td colspan="3" style="padding:5px"></td></tr><tr>';
	        $header.='<td>PERIODE : '.$p_start_date.' S/D '.$p_end_date.'</td>';
	        $header.='<td></td>';
	        $header.='<td align="center" width="200px" style="border:thin solid #000; padding:5px">'.$Kategori_brg.'</td>';
	        $header.='</tr>'; 
	        $header.='</table>';

	        $content='';
 
			$ptype = "";
			$group1 = "data_awal";
 
			$sum_total_Jual_per_divisi = 0;
			$sum_total_Rb_per_divisi = 0;
			$sum_total_Rc_per_divisi = 0;
			$sum_total_Disc_per_divisi = 0;
			$sum_total_Nettto_per_divisi = 0;

			$sum_total_Jual_per_partner_type = 0;
			$sum_total_Rb_per_partner_type = 0;
			$sum_total_Rc_per_partner_type = 0;
			$sum_total_Disc_per_partner_type = 0;
			$sum_total_Nettto_per_partner_type = 0;

			$sum_total_Jual_per_GrandTotal = 0;
			$sum_total_Rb_per_GrandTotal = 0;
			$sum_total_Rc_per_GrandTotal = 0;
			$sum_total_Disc_per_GrandTotal = 0;
			$sum_total_Nettto_per_GrandTotal = 0;
 
        	$content.='<table width="100%">';
			foreach($result as $hd){ 
				if ($ptype=="")
				{ 
					$ptype = $hd["Partner_Type"];   

					$content.='<tr>';
		            $content.='<td><b><i>Partner Type :</i> '.$hd["Partner_Type"].'</b></td>'; 
		            $content.='</tr>';

					$sum_total_Jual_per_divisi = 0;
					$sum_total_Rb_per_divisi = 0;
					$sum_total_Rc_per_divisi = 0;
					$sum_total_Disc_per_divisi = 0;
					$sum_total_Nettto_per_divisi = 0;

					$sum_total_Jual_per_partner_type = 0;
					$sum_total_Rb_per_partner_type = 0;
					$sum_total_Rc_per_partner_type = 0;
					$sum_total_Disc_per_partner_type = 0;
					$sum_total_Nettto_per_partner_type = 0;
				}
				else
				{
					if ($ptype!=$hd["Partner_Type"])
					{   
						//hitung total Divisi
						$content.='</tr><tr><td colspan="6" style="border-top:thin solid #000;"></td></tr>';
					 	$content.='<tr><td><b>Total '.$group1.'</b></td>'; 
						$content.='<td align="right"><b>'.number_format($sum_total_Jual_per_divisi,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_Rb_per_divisi,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_Rc_per_divisi,0,",",".").'</b></td></tr>';
						$content.='<td align="right"><b>'.number_format($sum_total_Disc_per_divisi,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_Nettto_per_divisi,0,",",".").'</b></td></tr>';

						//hitung total Partner Type
						$content.='</tr><tr><td colspan="6" style="border-top:thin solid #000;"></td></tr>';
					 	$content.='<tr><td><b>Total '.$ptype.'</b></td>'; 
						$content.='<td align="right"><b>'.number_format($sum_total_Jual_per_partner_type,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_Rb_per_partner_type,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_Rc_per_partner_type,0,",",".").'</b></td></tr>';
						$content.='<td align="right"><b>'.number_format($sum_total_Disc_per_partner_type,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_Nettto_per_partner_type,0,",",".").'</b></td></tr>';

              			$content.='<tr><td colspan="6" style="padding:10px;"></td></tr>';//enter sekali

						$ptype = $hd["Partner_Type"];

						$content.='<tr>';
			            $content.='<td><b><i>Partner Type :</i> '.$hd["Partner_Type"].'</b></td>'; 
			            $content.='</tr>';
						$group1 = "data_awal";

						$sum_total_Jual_per_divisi = 0;
						$sum_total_Rb_per_divisi = 0;
						$sum_total_Rc_per_divisi = 0;
						$sum_total_Disc_per_divisi = 0;
						$sum_total_Nettto_per_divisi = 0;

						$sum_total_Jual_per_partner_type = 0;
						$sum_total_Rb_per_partner_type = 0;
						$sum_total_Rc_per_partner_type = 0;
						$sum_total_Disc_per_partner_type = 0;
						$sum_total_Nettto_per_partner_type = 0;
					}
				} 

				if ($group1=="data_awal")
				{
					$group1 = $hd["Wilayah"]; 

					$content.='<tr>';
			    	$content.='<td><b><i>Wilayah :</i> '.$hd["Wilayah"].'</b></td>'; 
			    	$content.='</tr>';

			    	$content.='<thead><tr>';
		            $content.='<td width="25%">Divisi</td>';  
			    	$content.='<td width="15%" align="right">Total Jual</td>';
			    	$content.='<td width="15%" align="right">Total RB</td>';
			   		$content.='<td width="15%" align="right">Total RC</td>';
			    	$content.='<td width="15%" align="right">Total Disc</td>';
			    	$content.='<td width="15%" align="right">Omzet Netto</td>';
		            $content.='</tr><tr><td colspan="6" style="border-top:thin solid #000;"></td></tr></thead><tbody>';

					$sum_total_Jual_per_divisi = 0;
					$sum_total_Rb_per_divisi = 0;
					$sum_total_Rc_per_divisi = 0;
					$sum_total_Disc_per_divisi = 0;
					$sum_total_Nettto_per_divisi = 0; 
				}
				else
				{
					if (rtrim($group1)!=rtrim($hd["Wilayah"]))
					{   
						//hitung total Divisi
						$content.='</tr><tr><td colspan="6" style="border-top:thin solid #000;"></td></tr>';
					 	$content.='<tr><td><b>Total '.$group1.'</b></td>'; 
						$content.='<td align="right"><b>'.number_format($sum_total_Jual_per_divisi,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_Rb_per_divisi,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_Rc_per_divisi,0,",",".").'</b></td></tr>';
						$content.='<td align="right"><b>'.number_format($sum_total_Disc_per_divisi,0,",",".").'</b></td>';
						$content.='<td align="right"><b>'.number_format($sum_total_Nettto_per_divisi,0,",",".").'</b></td></tr>';
              			$content.='<tr><td colspan="6" style="padding:10px;"></td></tr>';//enter sekali

						$group1 = $hd["Wilayah"]; 
						$content.='<tr>';
				    	$content.='<td><b><i>Wilayah :</i> '.$hd["Wilayah"].'</b></td>'; 
				    	$content.='</tr>';

				    	$content.='<thead><tr>';
			            $content.='<td width="25%">Divisi</td>';  
			            $content.='<td width="15%" align="right">Total Jual</td>';
			            $content.='<td width="15%" align="right">Total RB</td>';
			            $content.='<td width="15%" align="right">Total RC</td>';
			            $content.='<td width="15%" align="right">Total Disc</td>';
			            $content.='<td width="15%" align="right">Omzet Netto</td>';
			            $content.='</tr><tr><td colspan="6" style="border-top:thin solid #000;"></td></tr></thead><tbody>';

						$sum_total_Jual_per_divisi = 0;
						$sum_total_Rb_per_divisi = 0;
						$sum_total_Rc_per_divisi = 0;
						$sum_total_Disc_per_divisi = 0;
						$sum_total_Nettto_per_divisi = 0; 
					}
				}  

				$content.='<tr>';
	            $content.='<td>'.$hd['Divisi'].'</td>'; 
	            $content.='<td align="right">'.number_format($hd['Total_Jual'],0,",",".").'</td>';
	            $content.='<td align="right">'.number_format($hd['Total_RB'],0,",",".").'</td>';
	            $content.='<td align="right">'.number_format($hd['Total_RC'],0,",",".").'</td>';
	            $content.='<td align="right">'.number_format($hd['Total_Disc'],0,",",".").'</td>';
	            $content.='<td align="right">'.number_format($hd['Omzet_Netto'],0,",",".").'</td>';
	            $content.='</tr>'; 

				$sum_total_Jual_per_divisi += $hd["Total_Jual"];
				$sum_total_Rb_per_divisi += $hd["Total_RB"];
				$sum_total_Rc_per_divisi += $hd["Total_RC"];
				$sum_total_Disc_per_divisi += $hd["Total_Disc"];
				$sum_total_Nettto_per_divisi += $hd["Omzet_Netto"];

				$sum_total_Jual_per_partner_type += $hd["Total_Jual"];
				$sum_total_Rb_per_partner_type += $hd["Total_RB"];
				$sum_total_Rc_per_partner_type += $hd["Total_RC"];
				$sum_total_Disc_per_partner_type += $hd["Total_Disc"];
				$sum_total_Nettto_per_partner_type += $hd["Omzet_Netto"];

				$sum_total_Jual_per_GrandTotal += $hd["Total_Jual"];
				$sum_total_Rb_per_GrandTotal += $hd["Total_RB"];
				$sum_total_Rc_per_GrandTotal += $hd["Total_RC"];
				$sum_total_Disc_per_GrandTotal += $hd["Total_Disc"];
				$sum_total_Nettto_per_GrandTotal += $hd["Omzet_Netto"];

 
			} 

			//hitung total Divisi
			$content.='</tr><tr><td colspan="6" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td><b>Total '.$group1.'</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_Jual_per_divisi,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_Rb_per_divisi,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_Rc_per_divisi,0,",",".").'</b></td></tr>';
			$content.='<td align="right"><b>'.number_format($sum_total_Disc_per_divisi,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_Nettto_per_divisi,0,",",".").'</b></td></tr>';

			//hitung total Partner Type
			$content.='</tr><tr><td colspan="6" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td><b>Total '.$ptype.'</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_Jual_per_partner_type,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_Rb_per_partner_type,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_Rc_per_partner_type,0,",",".").'</b></td></tr>';
			$content.='<td align="right"><b>'.number_format($sum_total_Disc_per_partner_type,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_Nettto_per_partner_type,0,",",".").'</b></td></tr>';
				
			$content.='</tr><tr><td colspan="6" style="border-top:thin solid #000;"></td></tr>';
		 	$content.='<tr><td><b>GRAND TOTAL</b></td>'; 
			$content.='<td align="right"><b>'.number_format($sum_total_Jual_per_GrandTotal,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_Rb_per_GrandTotal,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_Rc_per_GrandTotal,0,",",".").'</b></td></tr>';
			$content.='<td align="right"><b>'.number_format($sum_total_Disc_per_GrandTotal,0,",",".").'</b></td>';
			$content.='<td align="right"><b>'.number_format($sum_total_Nettto_per_GrandTotal,0,",",".").'</b></td></tr>';

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
		public function Report_603_A_Excel($p_start_date,$p_end_date,$Kategori_brg,$result)
		{  
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID; 
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
  
			$sheet->setTitle("OMZET_NETTO_PERDIVISI");
 
			$sheet->setCellValue('A1', "LAPORAN OMZET NETTO PER DIVISI PER WILAYAH");
			$sheet->mergeCells('A1:H1');
			$sheet->getStyle('A1:H1')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A1')->getFont()->setSize(20); 
			$sheet->setCellValue('A2', 'Print Date : '.date('d-M-Y H:i:s'));
			$sheet->setCellValue('A3', 'Periode : '.$p_start_date.' sd '.$p_end_date);
			$sheet->setCellValue('A4', 'Kategori : '.$Kategori_brg);
			$pos = 6;
			$sheet->getColumnDimension('A')->setWidth(20); 
			$sheet->getColumnDimension('B')->setWidth(15); 
			$sheet->getColumnDimension('C')->setWidth(30); 
			$sheet->getColumnDimension('D')->setWidth(15); 
			$sheet->getColumnDimension('E')->setWidth(15); 
			$sheet->getColumnDimension('F')->setWidth(15); 
			$sheet->getColumnDimension('G')->setWidth(15); 
			$sheet->getColumnDimension('H')->setWidth(15); 
			$sheet->getStyle("A".$pos.":H".$pos)->getFont()->setBold(true); 
			$sheet->setCellValue('A'.$pos, 'Partner Type');
			$sheet->setCellValue('B'.$pos, 'Divisi');
			$sheet->setCellValue('C'.$pos, 'Wilayah');
			$sheet->setCellValue('D'.$pos, 'Total Jual');
			$sheet->setCellValue('E'.$pos, 'Total RB');
			$sheet->setCellValue('F'.$pos, 'Total RC');
			$sheet->setCellValue('G'.$pos, 'Total Disc');
			$sheet->setCellValue('H'.$pos, 'Omzet Netto'); 
			$sheet->getStyle('A'.$pos.':H'.$pos)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('FFFACD');
 
			$i = 0;  
  
			$ptype = "";
			$divisi = "";

			$sum_total_Jual_per_divisi = 0;
			$sum_total_Rb_per_divisi = 0;
			$sum_total_Rc_per_divisi = 0;
			$sum_total_Disc_per_divisi = 0;
			$sum_total_Nettto_per_divisi = 0;

			$sum_total_Jual_per_partner_type = 0;
			$sum_total_Rb_per_partner_type = 0;
			$sum_total_Rc_per_partner_type = 0;
			$sum_total_Disc_per_partner_type = 0;
			$sum_total_Nettto_per_partner_type = 0;

			$sum_total_Jual_per_GrandTotal = 0;
			$sum_total_Rb_per_GrandTotal = 0;
			$sum_total_Rc_per_GrandTotal = 0;
			$sum_total_Disc_per_GrandTotal = 0;
			$sum_total_Nettto_per_GrandTotal = 0;


			foreach($result as $hd)
			{ 
				if ($ptype=="")
				{ 
					$ptype = $hd["Partner_Type"]; 

					$sum_total_Jual_per_divisi = 0;
					$sum_total_Rb_per_divisi = 0;
					$sum_total_Rc_per_divisi = 0;
					$sum_total_Disc_per_divisi = 0;
					$sum_total_Nettto_per_divisi = 0;

					$sum_total_Jual_per_partner_type = 0;
					$sum_total_Rb_per_partner_type = 0;
					$sum_total_Rc_per_partner_type = 0;
					$sum_total_Disc_per_partner_type = 0;
					$sum_total_Nettto_per_partner_type = 0;
				}
				else
				{
					if ($ptype!=$hd["Partner_Type"])
					{
						 
						$pos++;
						$sheet->setCellValue('A'.$pos, 'TOTAL '.$divisi); 
						$sheet->setCellValue('D'.$pos, $sum_total_Jual_per_divisi);
						$sheet->setCellValue('E'.$pos, $sum_total_Rb_per_divisi);
						$sheet->setCellValue('F'.$pos, $sum_total_Rc_per_divisi);
						$sheet->setCellValue('G'.$pos, $sum_total_Disc_per_divisi);
						$sheet->setCellValue('H'.$pos, $sum_total_Nettto_per_divisi);
						$sheet->getStyle("A".$pos.":H".$pos)->getFont()->setBold(true);
						$pos++;
						$sheet->setCellValue('A'.$pos, 'TOTAL '.$ptype); 
						$sheet->setCellValue('D'.$pos, $sum_total_Jual_per_partner_type);
						$sheet->setCellValue('E'.$pos, $sum_total_Rb_per_partner_type);
						$sheet->setCellValue('F'.$pos, $sum_total_Rc_per_partner_type);
						$sheet->setCellValue('G'.$pos, $sum_total_Disc_per_partner_type);
						$sheet->setCellValue('H'.$pos, $sum_total_Nettto_per_partner_type);
						$sheet->getStyle("A".$pos.":H".$pos)->getFont()->setBold(true); 
 
						$divisi = "";
						$ptype = $hd["Partner_Type"];  

						$sum_total_Jual_per_divisi = 0;
						$sum_total_Rb_per_divisi = 0;
						$sum_total_Rc_per_divisi = 0;
						$sum_total_Disc_per_divisi = 0;
						$sum_total_Nettto_per_divisi = 0;

						$sum_total_Jual_per_partner_type = 0;
						$sum_total_Rb_per_partner_type = 0;
						$sum_total_Rc_per_partner_type = 0;
						$sum_total_Disc_per_partner_type = 0;
						$sum_total_Nettto_per_partner_type = 0;
					}
				} 

				if ($divisi=="")
				{
					$divisi = $hd["Divisi"]; 

					$sum_total_Jual_per_divisi = 0;
					$sum_total_Rb_per_divisi = 0;
					$sum_total_Rc_per_divisi = 0;
					$sum_total_Disc_per_divisi = 0;
					$sum_total_Nettto_per_divisi = 0;
				}
				else
				{
					if (rtrim($divisi)!=rtrim($hd["Divisi"]))
					{
						 
						$pos++;
						$sheet->setCellValue('A'.$pos, 'TOTAL '.$divisi); 
						$sheet->setCellValue('D'.$pos, $sum_total_Jual_per_divisi);
						$sheet->setCellValue('E'.$pos, $sum_total_Rb_per_divisi);
						$sheet->setCellValue('F'.$pos, $sum_total_Rc_per_divisi);
						$sheet->setCellValue('G'.$pos, $sum_total_Disc_per_divisi);
						$sheet->setCellValue('H'.$pos, $sum_total_Nettto_per_divisi);
						$sheet->getStyle("A".$pos.":H".$pos)->getFont()->setBold(true); 
						$divisi = $hd["Divisi"]; 
		
						$sum_total_Jual_per_divisi = 0;
						$sum_total_Rb_per_divisi = 0;
						$sum_total_Rc_per_divisi = 0;
						$sum_total_Disc_per_divisi = 0;
						$sum_total_Nettto_per_divisi = 0;
					}
				}  

				$sum_total_Jual_per_divisi += $hd["Total_Jual"];
				$sum_total_Rb_per_divisi += $hd["Total_RB"];
				$sum_total_Rc_per_divisi += $hd["Total_RC"];
				$sum_total_Disc_per_divisi += $hd["Total_Disc"];
				$sum_total_Nettto_per_divisi += $hd["Omzet_Netto"];

				$sum_total_Jual_per_partner_type += $hd["Total_Jual"];
				$sum_total_Rb_per_partner_type += $hd["Total_RB"];
				$sum_total_Rc_per_partner_type += $hd["Total_RC"];
				$sum_total_Disc_per_partner_type += $hd["Total_Disc"];
				$sum_total_Nettto_per_partner_type += $hd["Omzet_Netto"];

				$sum_total_Jual_per_GrandTotal += $hd["Total_Jual"];
				$sum_total_Rb_per_GrandTotal += $hd["Total_RB"];
				$sum_total_Rc_per_GrandTotal += $hd["Total_RC"];
				$sum_total_Disc_per_GrandTotal += $hd["Total_Disc"];
				$sum_total_Nettto_per_GrandTotal += $hd["Omzet_Netto"];
 
				$pos++;
				$sheet->setCellValue('A'.$pos, $hd["Partner_Type"]);
				$sheet->setCellValue('B'.$pos, $hd["Divisi"]);
				$sheet->setCellValue('C'.$pos, $hd["Wilayah"]);
				$sheet->setCellValue('D'.$pos, $hd["Total_Jual"]);
				$sheet->setCellValue('E'.$pos, $hd["Total_RB"]);
				$sheet->setCellValue('F'.$pos, $hd["Total_RC"]);
				$sheet->setCellValue('G'.$pos, $hd["Total_Disc"]);
				$sheet->setCellValue('H'.$pos, $hd["Omzet_Netto"]); 
			} 
					 
			$pos++;
			$sheet->setCellValue('A'.$pos, 'TOTAL '.$divisi); 
			$sheet->setCellValue('D'.$pos, $sum_total_Jual_per_divisi);
			$sheet->setCellValue('E'.$pos, $sum_total_Rb_per_divisi);
			$sheet->setCellValue('F'.$pos, $sum_total_Rc_per_divisi);
			$sheet->setCellValue('G'.$pos, $sum_total_Disc_per_divisi);
			$sheet->setCellValue('H'.$pos, $sum_total_Nettto_per_divisi); 
			$sheet->getStyle("A".$pos.":H".$pos)->getFont()->setBold(true);
			$pos++;
			$sheet->setCellValue('A'.$pos, 'TOTAL '.$ptype); 
			$sheet->setCellValue('D'.$pos, $sum_total_Jual_per_partner_type);
			$sheet->setCellValue('E'.$pos, $sum_total_Rb_per_partner_type);
			$sheet->setCellValue('F'.$pos, $sum_total_Rc_per_partner_type);
			$sheet->setCellValue('G'.$pos, $sum_total_Disc_per_partner_type);
			$sheet->setCellValue('H'.$pos, $sum_total_Nettto_per_partner_type);
			$sheet->getStyle("A".$pos.":H".$pos)->getFont()->setBold(true);
			$pos++;
			$sheet->setCellValue('A'.$pos, 'GRAND TOTAL'); 
			$sheet->setCellValue('D'.$pos, $sum_total_Jual_per_GrandTotal);
			$sheet->setCellValue('E'.$pos, $sum_total_Rb_per_GrandTotal);
			$sheet->setCellValue('F'.$pos, $sum_total_Rc_per_GrandTotal);
			$sheet->setCellValue('G'.$pos, $sum_total_Disc_per_GrandTotal);
			$sheet->setCellValue('H'.$pos, $sum_total_Nettto_per_GrandTotal);
			$sheet->getStyle("A".$pos.":H".$pos)->getFont()->setBold(true); 
			$filename='ReportOmzetNettoDivisi['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 
			exit(); 
		}  

		public function Report_603_B_Excel($p_start_date,$p_end_date,$Kategori_brg,$result)
		{   
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID; 
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;

			$sheet->setTitle("OMZET_NETTO_PER_WILAYAH");

			$sheet->setCellValue('A1', "LAPORAN OMZET NETTO PER WILAYAH PER DIVISI");
			$sheet->mergeCells('A1:H1');
			$sheet->getStyle('A1:H1')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A1')->getFont()->setSize(20); 
			$sheet->setCellValue('A2', 'Print Date : '.date('d-M-Y H:i:s'));
			$sheet->setCellValue('A3', 'Periode : '.$p_start_date.' sd '.$p_end_date);
			$sheet->setCellValue('A4', 'Kategori : '.$Kategori_brg);
			$pos = 6;
			$sheet->getColumnDimension('A')->setWidth(20); 
			$sheet->getColumnDimension('B')->setWidth(20); 
			$sheet->getColumnDimension('C')->setWidth(15); 
			$sheet->getColumnDimension('D')->setWidth(15); 
			$sheet->getColumnDimension('E')->setWidth(15); 
			$sheet->getColumnDimension('F')->setWidth(15); 
			$sheet->getColumnDimension('G')->setWidth(15); 
			$sheet->getColumnDimension('H')->setWidth(15); 
			$sheet->getStyle("A".$pos.":H".$pos)->getFont()->setBold(true); 

			$sheet->setCellValue('A'.$pos, 'Partner Type');
			$sheet->setCellValue('B'.$pos, 'Wilayah');
			$sheet->setCellValue('C'.$pos, 'Divisi');
			$sheet->setCellValue('D'.$pos, 'Total Jual');
			$sheet->setCellValue('E'.$pos, 'Total RB');
			$sheet->setCellValue('F'.$pos, 'Total RC');
			$sheet->setCellValue('G'.$pos, 'Total Disc');
			$sheet->setCellValue('H'.$pos, 'Omzet Netto');
			$sheet->getStyle('A'.$pos.':H'.$pos)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('FFFACD');

			$i = 0;  
			$ptype = "";
			$will = "";

			$sum_total_Jual_per_will = 0;
			$sum_total_Rb_per_will = 0;
			$sum_total_Rc_per_will = 0;
			$sum_total_Disc_per_will = 0;
			$sum_total_Nettto_per_will = 0;

			$sum_total_Jual_per_partner_type = 0;
			$sum_total_Rb_per_partner_type = 0;
			$sum_total_Rc_per_partner_type = 0;
			$sum_total_Disc_per_partner_type = 0;
			$sum_total_Nettto_per_partner_type = 0;

			$sum_total_Jual_per_GrandTotal = 0;
			$sum_total_Rb_per_GrandTotal = 0;
			$sum_total_Rc_per_GrandTotal = 0;
			$sum_total_Disc_per_GrandTotal = 0;
			$sum_total_Nettto_per_GrandTotal = 0;


			foreach($result as $hd){ 
				if ($ptype=="")
				{ 
					$ptype = $hd["Partner_Type"]; 

					$sum_total_Jual_per_will = 0;
					$sum_total_Rb_per_will = 0;
					$sum_total_Rc_per_will = 0;
					$sum_total_Disc_per_will = 0;
					$sum_total_Nettto_per_will = 0;

					$sum_total_Jual_per_partner_type = 0;
					$sum_total_Rb_per_partner_type = 0;
					$sum_total_Rc_per_partner_type = 0;
					$sum_total_Disc_per_partner_type = 0;
					$sum_total_Nettto_per_partner_type = 0;
				}
				else
				{
					if ($ptype!=$hd["Partner_Type"])
					{
						 
						$pos++;
						$sheet->setCellValue('A'.$pos, 'TOTAL '.$will); 
						$sheet->setCellValue('D'.$pos, $sum_total_Jual_per_will);
						$sheet->setCellValue('E'.$pos, $sum_total_Rb_per_will);
						$sheet->setCellValue('F'.$pos, $sum_total_Rc_per_will);
						$sheet->setCellValue('G'.$pos, $sum_total_Disc_per_will);
						$sheet->setCellValue('H'.$pos, $sum_total_Nettto_per_will);
						$sheet->getStyle("A".$pos.":H".$pos)->getFont()->setBold(true);
						$pos++;
						$sheet->setCellValue('A'.$pos, 'TOTAL '.$ptype); 
						$sheet->setCellValue('D'.$pos, $sum_total_Jual_per_partner_type);
						$sheet->setCellValue('E'.$pos, $sum_total_Rb_per_partner_type);
						$sheet->setCellValue('F'.$pos, $sum_total_Rc_per_partner_type);
						$sheet->setCellValue('G'.$pos, $sum_total_Disc_per_partner_type);
						$sheet->setCellValue('H'.$pos, $sum_total_Nettto_per_partner_type);
						$sheet->getStyle("A".$pos.":H".$pos)->getFont()->setBold(true); 
						 
						$will = "";
						$ptype = $hd["Partner_Type"];
						 
						$sum_total_Jual_per_will = 0;
						$sum_total_Rb_per_will = 0;
						$sum_total_Rc_per_will = 0;
						$sum_total_Disc_per_will = 0;
						$sum_total_Nettto_per_will = 0;

						$sum_total_Jual_per_partner_type = 0;
						$sum_total_Rb_per_partner_type = 0;
						$sum_total_Rc_per_partner_type = 0;
						$sum_total_Disc_per_partner_type = 0;
						$sum_total_Nettto_per_partner_type = 0;
					}
				} 

				if ($will=="")
				{
					$will = $hd["Wilayah"]; 
					$sum_total_Jual_per_will = 0;
					$sum_total_Rb_per_will = 0;
					$sum_total_Rc_per_will = 0;
					$sum_total_Disc_per_will = 0;
					$sum_total_Nettto_per_will = 0;
				}
				else
				{
					if (rtrim($will)!=rtrim($hd["Wilayah"]))
					{ 
						$pos++;
						$sheet->setCellValue('A'.$pos, 'TOTAL '.$will); 
						$sheet->setCellValue('D'.$pos, $sum_total_Jual_per_will);
						$sheet->setCellValue('E'.$pos, $sum_total_Rb_per_will);
						$sheet->setCellValue('F'.$pos, $sum_total_Rc_per_will);
						$sheet->setCellValue('G'.$pos, $sum_total_Disc_per_will);
						$sheet->setCellValue('H'.$pos, $sum_total_Nettto_per_will);
						$sheet->getStyle("A".$pos.":H".$pos)->getFont()->setBold(true); 
 
						$will = $hd["Wilayah"];
						 
						$sum_total_Jual_per_willi = 0;
						$sum_total_Rb_per_will = 0;
						$sum_total_Rc_per_will = 0;
						$sum_total_Disc_per_will = 0;
						$sum_total_Nettto_per_will = 0;
					}
				}  
				$sum_total_Jual_per_will += $hd["Total_Jual"];
				$sum_total_Rb_per_will += $hd["Total_RB"];
				$sum_total_Rc_per_will += $hd["Total_RC"];
				$sum_total_Disc_per_will += $hd["Total_Disc"];
				$sum_total_Nettto_per_will += $hd["Omzet_Netto"];

				$sum_total_Jual_per_partner_type += $hd["Total_Jual"];
				$sum_total_Rb_per_partner_type += $hd["Total_RB"];
				$sum_total_Rc_per_partner_type += $hd["Total_RC"];
				$sum_total_Disc_per_partner_type += $hd["Total_Disc"];
				$sum_total_Nettto_per_partner_type += $hd["Omzet_Netto"];

				$sum_total_Jual_per_GrandTotal += $hd["Total_Jual"];
				$sum_total_Rb_per_GrandTotal += $hd["Total_RB"];
				$sum_total_Rc_per_GrandTotal += $hd["Total_RC"];
				$sum_total_Disc_per_GrandTotal += $hd["Total_Disc"];
				$sum_total_Nettto_per_GrandTotal += $hd["Omzet_Netto"];
 
				$pos++;
				$sheet->setCellValue('A'.$pos, $hd["Partner_Type"]);
				$sheet->setCellValue('B'.$pos, $hd["Wilayah"]);
				$sheet->setCellValue('C'.$pos, $hd["Divisi"]);
				$sheet->setCellValue('D'.$pos, $hd["Total_Jual"]);
				$sheet->setCellValue('E'.$pos, $hd["Total_RB"]);
				$sheet->setCellValue('F'.$pos, $hd["Total_RC"]);
				$sheet->setCellValue('G'.$pos, $hd["Total_Disc"]);
				$sheet->setCellValue('H'.$pos, $hd["Omzet_Netto"]); 

			}  
			$pos++;
			$sheet->setCellValue('A'.$pos, 'TOTAL '.$will); 
			$sheet->setCellValue('D'.$pos, $sum_total_Jual_per_will);
			$sheet->setCellValue('E'.$pos, $sum_total_Rb_per_will);
			$sheet->setCellValue('F'.$pos, $sum_total_Rc_per_will);
			$sheet->setCellValue('G'.$pos, $sum_total_Disc_per_will);
			$sheet->setCellValue('H'.$pos, $sum_total_Nettto_per_will); 
			$sheet->getStyle("A".$pos.":H".$pos)->getFont()->setBold(true);
			$pos++;
			$sheet->setCellValue('A'.$pos, 'TOTAL '.$ptype); 
			$sheet->setCellValue('D'.$pos, $sum_total_Jual_per_partner_type);
			$sheet->setCellValue('E'.$pos, $sum_total_Rb_per_partner_type);
			$sheet->setCellValue('F'.$pos, $sum_total_Rc_per_partner_type);
			$sheet->setCellValue('G'.$pos, $sum_total_Disc_per_partner_type);
			$sheet->setCellValue('H'.$pos, $sum_total_Nettto_per_partner_type);
			$sheet->getStyle("A".$pos.":H".$pos)->getFont()->setBold(true);
			$pos++;
			$sheet->setCellValue('A'.$pos, 'GRAND TOTAL'); 
			$sheet->setCellValue('D'.$pos, $sum_total_Jual_per_GrandTotal);
			$sheet->setCellValue('E'.$pos, $sum_total_Rb_per_GrandTotal);
			$sheet->setCellValue('F'.$pos, $sum_total_Rc_per_GrandTotal);
			$sheet->setCellValue('G'.$pos, $sum_total_Disc_per_GrandTotal);
			$sheet->setCellValue('H'.$pos, $sum_total_Nettto_per_GrandTotal);
			$sheet->getStyle("A".$pos.":H".$pos)->getFont()->setBold(true);  
			$filename='ReportOmzetNettoDivisi['.date('Ymd').']'; //save our workbook as this file name
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