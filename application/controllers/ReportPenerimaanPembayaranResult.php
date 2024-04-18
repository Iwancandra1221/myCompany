<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ReportPenerimaanPembayaranResult extends CI_Controller 
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
		$data = array();
		$page_title = 'Laporan PreOrder Pembelian';

		if(isset($_POST["btnPreview"])){
			$this->excel_flag = 0;
		}
		else{
			$this->excel_flag = 1;
		}

		if(isset($_POST['cbg']))
		{

			if(!isset($_POST["cbox1"])){
				$_POST["cbox1"] = 'N';
			}

			$this->load->library('form_validation');
			$this->form_validation->set_rules('yyyy','Tahun','required');
			$this->form_validation->set_rules('cbg','Cabang','required');
			if($this->form_validation->run())
			{

				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle('Testing langsung bunting');
				$this->excel->getActiveSheet()->setCellValue('A1', 'Nilai excelnya');
				$filename='nama_file.xls'; 
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="'.$filename.'"'); 
				header('Cache-Control: max-age=0');
				$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
				$objWriter->save('php://output');
				exit(1);

				if ($_POST["opsi"]=="C01") {
					$this->Preview_DelapanPeriode("GABUNGAN", $page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"], $_POST["cbox1"]);
				} else if ($_POST["opsi"]=="C02") {
					$this->Preview_DelapanPeriode("KOTA", $page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"], $_POST["cbox1"]);
				} else if ($_POST["opsi"]=="C03") {
					$this->Preview_DelapanPeriode("GROUP GUDANG", $page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"]);
				} else if ($_POST["opsi"]=="A01") {
					$this->Preview_SatuPeriodeAllKota($page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"], $_POST["cbox1"]);
				} else if ($_POST["opsi"]=="A02") {
					$this->Preview_SatuPeriodeAllPT($page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"], $_POST["cbox1"]);
				} else if ($_POST["opsi"]=="B01") {
					$this->Preview_JualBeliAllKota("JUAL", $page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"], $_POST["cbox1"]);
				} else if ($_POST["opsi"]=="B02") {
					$this->Preview_JualBeliAllKota("BELI", $page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"], $_POST["cbox1"]);
				} else if ($_POST["opsi"]=="B03") {
					$this->Preview_JualBeliAllPT("JUAL", $page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"], $_POST["cbox1"]);
				} else if ($_POST["opsi"]=="B04") {
					$this->Preview_JualBeliAllPT("BELI", $page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"], $_POST["cbox1"]);
				}
			}
			else
			{
				redirect("LaporanPreOrderPembelian");
			}
		}
		else
		{
			redirect("LaporanPreOrderPembelian");
		}
	}

	public function Preview_SatuPeriodeAllKota($page_title, $p_yy, $p_mm, $p_pp, $p_divisi, $p_cbg, $p_hide_nol='N', $p_email="N")
	{

		$data = array();
		$api = 'APITES';

		$style_col_ganjil = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#ffffcc;";
		$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#fff;";
		$style_col_total = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#f2f2f2;";
		$style_col_brg = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:left;padding-right:5px;padding-left:10px;background-color:#ccffcc;";

		/*
		die($this->API_URL."LaporanPreOrderPembelian/ProsesSatuPeriodeAllKota?api=".$api."&p_yy=".urlencode($p_yy)."&p_mm=".urlencode($p_mm)
									."&p_pp=".urlencode($p_pp)."&p_cbg=".urlencode($p_cbg)."&p_divisi=".urlencode($p_divisi));
		*/
		set_time_limit(60);
		$arrBrg = json_decode(file_get_contents($this->API_URL."/LaporanPreOrderPembelian/GetKodeBarangPO?api=".$api."&p_divisi=".urlencode($p_divisi)));
		set_time_limit(60);
		$PrePo = json_decode(file_get_contents($this->API_URL."/LaporanPreOrderPembelian/ProsesSatuPeriodeAllKota?api=".$api."&p_yy=".urlencode($p_yy)."&p_mm=".urlencode($p_mm)
									."&p_pp=".urlencode($p_pp)."&p_cbg=".urlencode($p_cbg)."&p_divisi=".urlencode($p_divisi)));
		$jmlCbg = count($PrePo);
		$pp_yy = $p_yy;
		$pp_mm = $p_mm;
		$pp_pp = $p_pp;


		$content_html = "<html><body>";
		$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
		$content_html.= "	<div><h2>LAPORAN FORECAST BELI/JUAL/STOCK PER PERIODE ALL KOTA</h2></div>";
		$content_html.= "	<div><b>Divisi : ".$p_divisi."</b></div>";
		$content_html.= "	<div><b>Periode: ".$p_pp."</b></div>";
		$content_html.= "	<div><b>Bln/Thn: ".$p_mm."/".$p_yy."</b></div>";
		$content_html.= "</div>";	//close div_header

		if($this->excel_flag == 1){
			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->setTitle('LaporanForecastBeliJual');
			$this->excel->getActiveSheet()->setCellValue('A1', 'LAPORAN FORECAST BELI/JUAL/STOCK PER PERIODE ALL KOTA');
			$this->excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
			$this->excel->getActiveSheet()->setCellValue('A2', 'Divisi : '.$p_divisi);
			$this->excel->getActiveSheet()->setCellValue('A3', 'Periode : '.$p_pp);
			$this->excel->getActiveSheet()->setCellValue('A4', 'Bln/Thn : '.$p_mm."/".$p_yy);
		}


		$content_html.= "<div class='div_body' style='width:8000px;overflow-x:scroll;'>";
		$content_html.= "<div style='clear:both'></div>";

		$content_html.= "<div id='div_column_header' style='text-align:center;line-height:90px;vertical-align:middle;'>";
		$content_html.= "	<div style='width:250px;".$style_col_brg."'><b><br>Kode Barang</b></div>";

		if($this->excel_flag == 1){
			$this->excel->getActiveSheet()->setCellValue('A6', 'Kode Barang');
		}
		$currcol = 1;
		$currrow = 6;
		for($i=0;$i<count($PrePo);$i++)
		{	
			if ($i%2==1)
				$style_col = $style_col_genap;
			else
				$style_col = $style_col_ganjil;
			$content_html.= "	<div style='width:60px;".$style_col."'><b>".$PrePo[$i]->Kota."<br>S_Awal</b></div>";
			$content_html.= "	<div style='width:60px;".$style_col."'><b>".$PrePo[$i]->Kota."<br>Beli</b></div>";
			$content_html.= "	<div style='width:60px;".$style_col."'><b>".$PrePo[$i]->Kota."<br>Jual</b></div>";
			$content_html.= "	<div style='width:60px;".$style_col."'><b>".$PrePo[$i]->Kota."<br>S_Akhir</b></div>";

			if($this->excel_flag == 1){
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $PrePo[$i]->Kota.' S_Awal');
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $PrePo[$i]->Kota.' Beli');
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $PrePo[$i]->Kota.' Jual');
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $PrePo[$i]->Kota.' S_Akhir');
				$currcol += 1;
			}

		}

		$content_html.= "	<div style='width:80px;".$style_col_total."'><b><br>Total Beli</b></div>";
		$content_html.= "	<div style='width:80px;".$style_col_total."'><b><br>Total Jual</b></div>";
		$content_html.= "</div>";	//close div_column_header
		$content_html.= "<div style='clear:both;></div>";

		if($this->excel_flag == 1){
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Total Beli');
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Total Jual');
			$currcol += 1;
		}

		$currrow = 7;
		for ($j=0; $j<count($arrBrg);$j++)
		{
			$row_brg_html = "<div id='div_column_header' style='text-align:center;line-height:60px;vertical-align:middle;'>";
			$row_brg_html.= "	<div style='width:250px;".$style_col_brg."'>".$arrBrg[$j]."</div>";

			if($this->excel_flag == 1){
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $currrow+$j, $arrBrg[$j]);
			}

			$qty_total_jual = 0;
			$qty_total_beli = 0;

			$currcol = 1;

			for($i=0;$i<count($PrePo);$i++)
			{
				if ($i%2==1)
					$style_col = $style_col_genap;
				else
					$style_col = $style_col_ganjil;

				$qty_s_awal = 0;
				$qty_t_beli = 0;
				$qty_t_jual = 0;
				$qty_s_akhir = 0;
				$arrDetails = $PrePo[$i]->Detail_PrePO;
				for($x=0; $x<count($arrDetails); $x++)
				{
					if ($arrDetails[$x]->Kd_Brg==$arrBrg[$j])
					{
						$qty_s_awal = $arrDetails[$x]->Stock_Awal;		
						$qty_t_beli = $arrDetails[$x]->Total_Beli;		
						$qty_t_jual = $arrDetails[$x]->Total_Jual;		
						$qty_s_akhir = $arrDetails[$x]->Stock_Akhir;		
						break;
					}
				}
				$qty_total_beli = $qty_total_beli + $qty_t_beli;
				$qty_total_jual = $qty_total_jual + $qty_t_jual;
				$row_brg_html.= "	<div style='width:60px;".$style_col."'>".$qty_s_awal."</div>";
				$row_brg_html.= "	<div style='width:60px;".$style_col."'>".$qty_t_beli."</div>";
				$row_brg_html.= "	<div style='width:60px;".$style_col."'>".$qty_t_jual."</div>";
				$row_brg_html.= "	<div style='width:60px;".$style_col."'>".$qty_s_akhir."</div>";

				if($this->excel_flag == 1){
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$i, $currrow+$j, $qty_s_awal);
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$i, $currrow+$j, $qty_t_beli);
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$i, $currrow+$j, $qty_t_jual);
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$i, $currrow+$j, $qty_s_akhir);
				}	

			}
			
			$row_brg_html.= "	<div style='width:80px;".$style_col_total."'><b>".$qty_total_beli."</b></div>";
			$row_brg_html.= "	<div style='width:80px;".$style_col_total."'><b>".$qty_total_jual."</b></div>";
			$row_brg_html.= "</div>";	//close div_column_header
			$row_brg_html.= "<div style='clear:both;></div>";

			if($this->excel_flag == 1){	
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow(count($PrePo)*4+1, $currrow+$j, $qty_total_beli);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow(count($PrePo)*4+2, $currrow+$j, $qty_total_jual);
			}	

			if ($p_hide_nol!="Y" || $qty_total_jual!=0 || $qty_total_beli!=0)
			{
				$content_html.=$row_brg_html;
			}
			else{
				$this->excel->getActiveSheet()->removeRow($currrow+$j);
				$currrow -= 1;
			}
		}
		// exit(1);
		if($this->excel_flag == 1){
			//PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
			$this->excel->getActiveSheet()->mergeCells('A1:J1');
			for ($i = 'A'; $i !=   $this->excel->getActiveSheet()->getHighestColumn(); $i++) {
			    $this->excel->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
			}

			$filename='LaporanForecastBeliJualStockPerPeriodeAllKota['.date('Ymd').'].xls'; //save our workbook as this file name
			header('Content-Type: application/vnd.ms-excel'); //mime type
			header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
			header('Cache-Control: max-age=0'); //no cache
			$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
			//force user to download the Excel file without writing it to server's HD
			$objWriter->save('php://output');
		}

		$content_html.= "</div>";
		$content_html.= "</body></html>";


		$data['title'] = $page_title;
		$data['content_html'] = $content_html;

		$this->RenderView('LaporanResultView',$data);
		// $this->SetTemplate('template/login');
	}

	public function Preview_SatuPeriodeAllPT($page_title, $p_yy, $p_mm, $p_pp, $p_divisi, $p_cbg, $p_hide_nol='N', $p_email="N")
	{

		$data = array();
		$api = 'APITES';

		$style_col_ganjil = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#ffffcc;";
		$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#fff;";
		$style_col_total = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#f2f2f2;";
		$style_col_brg = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:left;padding-right:5px;padding-left:10px;background-color:#ccffcc;";

		/*
		die($this->API_URL."LaporanPreOrderPembelian/ProsesSatuPeriodeAllKota?api=".$api."&p_yy=".urlencode($p_yy)."&p_mm=".urlencode($p_mm)
									."&p_pp=".urlencode($p_pp)."&p_cbg=".urlencode($p_cbg)."&p_divisi=".urlencode($p_divisi));
		*/
		set_time_limit(300);
		$arrBrg = json_decode(file_get_contents($this->API_URL."/LaporanPreOrderPembelian/GetKodeBarangPO?api=".$api."&p_divisi=".urlencode($p_divisi)));
		set_time_limit(300);
		$PrePo = json_decode(file_get_contents($this->API_URL."/LaporanPreOrderPembelian/ProsesSatuPeriodeAllPT?api=".$api."&p_yy=".urlencode($p_yy)."&p_mm=".urlencode($p_mm)
									."&p_pp=".urlencode($p_pp)."&p_cbg=".urlencode($p_cbg)."&p_divisi=".urlencode($p_divisi)));
		$jmlCbg = count($PrePo);
		$pp_yy = $p_yy;
		$pp_mm = $p_mm;
		$pp_pp = $p_pp;


		$content_html = "<html><body>";
		$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
		$content_html.= "	<div><h2>LAPORAN FORECAST BELI/JUAL/STOCK PER PERIODE ALL BIT/PT</h2></div>";
		$content_html.= "	<div><b>Divisi : ".$p_divisi."</b></div>";
		$content_html.= "	<div><b>Periode: ".$p_pp."</b></div>";
		$content_html.= "	<div><b>Bln/Thn: ".$p_mm."/".$p_yy."</b></div>";
		$content_html.= "</div>";	//close div_header

		if($this->excel_flag == 1){
			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->setTitle('LaporanForecastBeliJual');
			$this->excel->getActiveSheet()->setCellValue('A1', 'LAPORAN FORECAST BELI/JUAL/STOCK PER PERIODE ALL BIT/PT');
			$this->excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
			$this->excel->getActiveSheet()->setCellValue('A2', 'Divisi : '.$p_divisi);
			$this->excel->getActiveSheet()->setCellValue('A3', 'Periode : '.$p_pp);
			$this->excel->getActiveSheet()->setCellValue('A4', 'Bln/Thn : '.$p_mm."/".$p_yy);
		}


		$content_html.= "<div class='div_body' style='width:20000px;overflow-x:scroll;'>";
		$content_html.= "<div style='clear:both'></div>";

		$content_html.= "<div id='div_column_header' style='text-align:center;line-height:90px;vertical-align:middle;'>";
		$content_html.= "	<div style='width:250px;".$style_col_brg."height:90px;'><b><br>Kode Barang</b></div>";

		if($this->excel_flag == 1){
			$this->excel->getActiveSheet()->setCellValue('A6', 'Kode Barang');
		}
		$currcol = 1;
		$currrow = 6;
		for($i=0;$i<count($PrePo);$i++)
		{	
			if ($i%2==1)
				$style_col = $style_col_genap;
			else
				$style_col = $style_col_ganjil;
			$content_html.= "	<div style='width:60px;".$style_col."height:90px;'><b>".$PrePo[$i]->Kota."<br>S_Awal</b></div>";
			$content_html.= "	<div style='width:60px;".$style_col."height:90px;'><b>".$PrePo[$i]->Kota."<br>Beli</b></div>";
			$content_html.= "	<div style='width:60px;".$style_col."height:90px;'><b>".$PrePo[$i]->Kota."<br>Jual</b></div>";
			$content_html.= "	<div style='width:60px;".$style_col."height:90px;'><b>".$PrePo[$i]->Kota."<br>S_Akhir</b></div>";

			$str_kota = str_replace("<br>"," ",$PrePo[$i]->Kota);
			if($this->excel_flag == 1){
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $str_kota.' S_Awal');
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $str_kota.' Beli');
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $str_kota.' Jual');
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $str_kota.' S_Akhir');
				$currcol += 1;
			}

		}

		$content_html.= "	<div style='width:80px;".$style_col_total."height:90px;'><b><br>Total<br>Beli</b></div>";
		$content_html.= "	<div style='width:80px;".$style_col_total."height:90px;'><b><br>Total<br>Jual</b></div>";
		$content_html.= "</div>";	//close div_column_header
		$content_html.= "<div style='clear:both;></div>";

		if($this->excel_flag == 1){
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Total Beli');
			$currcol += 1;
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Total Jual');
			$currcol += 1;
		}

		$currrow = 7;
		for ($j=0; $j<count($arrBrg);$j++)
		{
			$row_brg_html = "<div id='div_column_header' style='text-align:center;line-height:60px;vertical-align:middle;'>";
			$row_brg_html.= "	<div style='width:250px;".$style_col_brg."'>".$arrBrg[$j]."</div>";

			if($this->excel_flag == 1){
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $currrow+$j, $arrBrg[$j]);
			}

			$qty_total_jual = 0;
			$qty_total_beli = 0;

			$currcol = 1;

			for($i=0;$i<count($PrePo);$i++)
			{
				if ($i%2==1)
					$style_col = $style_col_genap;
				else
					$style_col = $style_col_ganjil;

				$qty_s_awal = 0;
				$qty_t_beli = 0;
				$qty_t_jual = 0;
				$qty_s_akhir = 0;
				$arrDetails = $PrePo[$i]->Detail_PrePO;
				for($x=0; $x<count($arrDetails); $x++)
				{
					if ($arrDetails[$x]->Kd_Brg==$arrBrg[$j])
					{
						$qty_s_awal = $arrDetails[$x]->Stock_Awal;		
						$qty_t_beli = $arrDetails[$x]->Total_Beli;		
						$qty_t_jual = $arrDetails[$x]->Total_Jual;		
						$qty_s_akhir = $arrDetails[$x]->Stock_Akhir;		
						break;
					}
				}
				$qty_total_beli = $qty_total_beli + $qty_t_beli;
				$qty_total_jual = $qty_total_jual + $qty_t_jual;
				$row_brg_html.= "	<div style='width:60px;".$style_col."'>".$qty_s_awal."</div>";
				$row_brg_html.= "	<div style='width:60px;".$style_col."'>".$qty_t_beli."</div>";
				$row_brg_html.= "	<div style='width:60px;".$style_col."'>".$qty_t_jual."</div>";
				$row_brg_html.= "	<div style='width:60px;".$style_col."'>".$qty_s_akhir."</div>";

				if($this->excel_flag == 1){
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$i, $currrow+$j, $qty_s_awal);
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$i, $currrow+$j, $qty_t_beli);
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$i, $currrow+$j, $qty_t_jual);
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$i, $currrow+$j, $qty_s_akhir);
				}	

				//$row_brg_html.= "	<div style='width:60px;".$style_col."'>".(($qty_s_awal==0)? $qty_s_awal : "<b>".$qty_s_awal."</b>")."</div>";
				//$row_brg_html.= "	<div style='width:60px;".$style_col."'>".(($qty_t_beli==0)? $qty_t_beli : "<b>".$qty_t_beli."</b>")."</div>";
				//$row_brg_html.= "	<div style='width:60px;".$style_col."'>".(($qty_t_jual==0)? $qty_t_jual : "<b>".$qty_t_jual."</b>")."</div>";
				//$row_brg_html.= "	<div style='width:60px;".$style_col."'>".(($qty_s_akhir==0)? $qty_s_akhir : "<b>".$qty_s_akhir."</b>")."</div>";
			}
			
			$row_brg_html.= "	<div style='width:80px;".$style_col_total."'><b>".$qty_total_beli."</b></div>";
			$row_brg_html.= "	<div style='width:80px;".$style_col_total."'><b>".$qty_total_jual."</b></div>";
			$row_brg_html.= "</div>";	//close div_column_header
			$row_brg_html.= "<div style='clear:both;></div>";

			if($this->excel_flag == 1){	
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow(count($PrePo)*4+1, $currrow+$j, $qty_total_beli);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow(count($PrePo)*4+2, $currrow+$j, $qty_total_jual);
			}	

			if ($p_hide_nol!="Y" || $qty_total_jual!=0 || $qty_total_beli!=0)
			{
				$content_html.=$row_brg_html;
			}
			else{
				$this->excel->getActiveSheet()->removeRow($currrow+$j);
				$currrow -= 1;
			}
		}
		// exit(1);
		if($this->excel_flag == 1){
			//PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
			$this->excel->getActiveSheet()->mergeCells('A1:J1');
			for ($i = 'A'; $i !=   $this->excel->getActiveSheet()->getHighestColumn(); $i++) {
			    $this->excel->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
			}

			$filename='LaporanForecastBeliJualStockPerPeriodeAllPT['.date('Ymd').'].xls'; //save our workbook as this file name
			header('Content-Type: application/vnd.ms-excel'); //mime type
			header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
			header('Cache-Control: max-age=0'); //no cache
			$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
			//force user to download the Excel file without writing it to server's HD
			$objWriter->save('php://output');
		}

		$content_html.= "</div>";
		$content_html.= "</body></html>";


		$data['title'] = $page_title;
		$data['content_html'] = $content_html;

		$this->RenderView('LaporanResultView',$data);
		// $this->SetTemplate('template/login');
	}

	public function Preview_JualBeliAllKota($mode, $page_title, $p_yy, $p_mm, $p_pp, $p_divisi, $p_cbg, $p_hide_nol='N', $p_email="N")
	{
		$data = array();
		$api = 'APITES';
		$content_html = "";
		//$style_col = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;";
		//$style_col_brg = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:left;padding-right:5px;padding-left:10px;";

		$style_col_ganjil = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#ffffcc;";
		$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#fff;";
		$style_col_total = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#f2f2f2;";
		$style_col_brg = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:left;padding-right:5px;padding-left:10px;background-color:#ccffcc;";

		$arrBrg = json_decode(file_get_contents($this->API_URL."LaporanPreOrderPembelian/GetKodeBarangPO?api=".$api."&p_divisi=".urlencode($p_divisi)));
		$PrePo = json_decode(file_get_contents($this->API_URL."LaporanPreOrderPembelian/ProsesJualBeliFlatKota?api=".$api."&p_yy=".urlencode($p_yy)."&p_mm=".urlencode($p_mm)
									."&p_pp=".urlencode($p_pp)."&p_cbg=".urlencode($p_cbg)."&p_divisi=".urlencode($p_divisi)));
		$jmlCbg = count($PrePo);
		$pp_yy = $p_yy;
		$pp_mm = $p_mm;
		$pp_pp = $p_pp;


		$content_html = "<html><body>";
		$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
		$content_html.= "	<div><h2>LAPORAN FORECAST ".$mode." ALL CABANG GABUNGAN</h2></div>";
		$content_html.= "	<div><b>Divisi : ".$p_divisi."</b></div>";
		$content_html.= "	<div><b>Periode: ".$p_pp."</b></div>";
		$content_html.= "	<div><b>Bln/Thn: ".$p_mm."/".$p_yy."</b></div>";
		$content_html.= "</div>";	//close div_header

		if($this->excel_flag == 1){
			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->setTitle('LaporanForecastBeliJual');
			$this->excel->getActiveSheet()->setCellValue('A1', 'LAPORAN FORECAST '.$mode.' ALL CABANG GABUNGAN');
			$this->excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
			$this->excel->getActiveSheet()->setCellValue('A2', 'Divisi : '.$p_divisi);
			$this->excel->getActiveSheet()->setCellValue('A3', 'Periode : '.$p_pp);
			$this->excel->getActiveSheet()->setCellValue('A4', 'Bln/Thn : '.$p_mm."/".$p_yy);
		}

		$content_html.= "<div class='div_body' style='width:2000px;overflow-x:scroll;'>";
		$content_html.= "<div style='clear:both'></div>";

		$content_html.= "<div id='div_column_header' style='text-align:center;line-height:90px;vertical-align:middle;'>";
		$content_html.= "	<div style='width:250px;".$style_col_brg."'><b>Kode Barang</b></div>";

		if($this->excel_flag == 1){
			$this->excel->getActiveSheet()->setCellValue('A6', 'Kode Barang');
		}

		$currcol = 1;
		$currrow = 6;
		for($i=0;$i<count($PrePo);$i++)
		{		
			if ($i%2==0)
				$style_col = $style_col_genap;
			else
				$style_col = $style_col_ganjil;

			$content_html.= "	<div style='width:60px;".$style_col."'><b>".$PrePo[$i]->Kota."</b></div>";

			if($this->excel_flag == 1){
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $PrePo[$i]->Kota);
				$currcol += 1;
			}
		}
		$content_html.= "	<div style='width:80px;".$style_col_total."'><b>Total</b></div>";
		$content_html.= "</div>";	//close div_column_header
		$content_html.= "<div style='clear:both;></div>";
		if($this->excel_flag == 1){
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
		}

		$currrow = 7;
		for ($j=0; $j<count($arrBrg);$j++)
		{
			$row_brg_html = "<div id='div_column_header' style='text-align:center;line-height:60px;vertical-align:middle;'>";
			$row_brg_html.= "	<div style='width:250px;".$style_col_brg."'>".$arrBrg[$j]."</div>";

			if($this->excel_flag == 1){
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $currrow+$j, $arrBrg[$j]);
			}

			$qty_total = 0;

			$currcol = 1;
			for($i=0;$i<count($PrePo);$i++)
			{
				if ($i%2==0)
					$style_col = $style_col_genap;
				else
					$style_col = $style_col_ganjil;

				$qty = 0;
				$arrDetails = $PrePo[$i]->Detail_PrePO;
				for($x=0; $x<count($arrDetails); $x++)
				{
					if ($arrDetails[$x]->Kd_Brg==$arrBrg[$j])
					{
						$qty = (($mode=="JUAL") ? $arrDetails[$x]->Total_Jual : $arrDetails[$x]->Total_Beli);		
						break;
					}
				}
				$qty_total = $qty_total + $qty;
				$row_brg_html.= "	<div style='width:60px;".$style_col."'>".(($qty==0)? $qty : "<b>".$qty."</b>")."</div>";

				if($this->excel_flag == 1){
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$i, $currrow+$j, $qty);
				}	
			}
			$row_brg_html.= "	<div style='width:80px;".$style_col_total."'><b>".$qty_total."</b></div>";
			$row_brg_html.= "</div>";	//close div_column_header
			$row_brg_html.= "<div style='clear:both;></div>";

			if($this->excel_flag == 1){
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow(count($PrePo)+1, $currrow+$j, $qty_total);
			}	

			if ($p_hide_nol!="Y" || $qty_total!=0)
			{
				$content_html.=$row_brg_html;
			}
			else{
				$this->excel->getActiveSheet()->removeRow($currrow+$j);
				$currrow -= 1;
			}
		}
		
		if($this->excel_flag == 1){
			//PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
			$this->excel->getActiveSheet()->mergeCells('A1:N1');
			for ($i = 'A'; $i !=   $this->excel->getActiveSheet()->getHighestColumn(); $i++) {
			    $this->excel->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
			}

			$filename='LaporanForecast'.$mode.'AllCabangAllKota['.date('Ymd').'].xls'; //save our workbook as this file name
			header('Content-Type: application/vnd.ms-excel'); //mime type
			header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
			header('Cache-Control: max-age=0'); //no cache
			$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
			//force user to download the Excel file without writing it to server's HD
			$objWriter->save('php://output');
		}

		$content_html.= "</div>";
		$content_html.= "</body></html>";


		$data['title'] = $page_title;
		$data['content_html'] = $content_html;
		
		// $this->SetTemplate('template/login');
		$this->RenderView('LaporanResultView',$data);
	}

	public function Preview_JualBeliAllPT($mode, $page_title, $p_yy, $p_mm, $p_pp, $p_divisi, $p_cbg, $p_hide_nol='N', $p_email="N")
	{
		$data = array();
		$api = 'APITES';

		//$style_col = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;";
		//$style_col_brg = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:left;padding-right:5px;padding-left:10px;";

		$style_col_ganjil = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#ffffcc;";
		$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#fff;";
		$style_col_total = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#f2f2f2;";
		$style_col_brg = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:left;padding-right:5px;padding-left:10px;background-color:#ccffcc;";

		$arrBrg = json_decode(file_get_contents($this->API_URL."/LaporanPreOrderPembelian/GetKodeBarangPO?api=".$api."&p_divisi=".urlencode($p_divisi)));
		$PrePo = json_decode(file_get_contents($this->API_URL."/LaporanPreOrderPembelian/ProsesJualBeliFlat?api=".$api."&p_yy=".urlencode($p_yy)."&p_mm=".urlencode($p_mm)
									."&p_pp=".urlencode($p_pp)."&p_cbg=".urlencode($p_cbg)."&p_divisi=".urlencode($p_divisi)));
		$jmlCbg = count($PrePo);
		$pp_yy = $p_yy;
		$pp_mm = $p_mm;
		$pp_pp = $p_pp;


		$content_html = "<html><body>";
		$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
		$content_html.= "	<div><h2>LAPORAN FORECAST ".$mode." ALL CABANG</h2></div>";
		$content_html.= "	<div><b>Divisi : ".$p_divisi."</b></div>";
		$content_html.= "	<div><b>Periode: ".$p_pp."</b></div>";
		$content_html.= "	<div><b>Bln/Thn: ".$p_mm."/".$p_yy."</b></div>";
		$content_html.= "</div>";	//close div_header

		if($this->excel_flag == 1){
			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->setTitle('LaporanForecast');
			$this->excel->getActiveSheet()->setCellValue('A1', 'LAPORAN FORECAST '.$mode.' ALL CABANG');
			$this->excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
			$this->excel->getActiveSheet()->setCellValue('A2', 'Divisi : '.$p_divisi);
			$this->excel->getActiveSheet()->setCellValue('A3', 'Periode : '.$p_pp);
			$this->excel->getActiveSheet()->setCellValue('A4', 'Bln/Thn : '.$p_mm."/".$p_yy);
		}

		$content_html.= "<div class='div_body' style='width:2000px;overflow-x:scroll;'>";
		$content_html.= "<div style='clear:both'></div>";

		$content_html.= "<div id='div_column_header' style='text-align:center;line-height:90px;vertical-align:middle;'>";
		$content_html.= "	<div style='width:250px;".$style_col_brg."'><b><br>Kode Barang</b></div>";

		if($this->excel_flag == 1){
			$this->excel->getActiveSheet()->setCellValue('A6', 'Kode Barang');
		}
		$currcol = 1;
		$currrow = 6;

		for($i=0;$i<count($PrePo);$i++)
		{		
			if ($i%2==0)
				$style_col = $style_col_genap;
			else
				$style_col = $style_col_ganjil;

			$content_html.= "	<div style='width:60px;".$style_col."'><b>".$PrePo[$i]->Kota."<br>".$PrePo[$i]->Cbg."</b></div>";

			if($this->excel_flag == 1){
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $PrePo[$i]->Kota.' - '.$PrePo[$i]->Cbg);
				$currcol += 1;
			}
		}

		$content_html.= "	<div style='width:80px;".$style_col_total."'><b><br>Total</b></div>";
		$content_html.= "</div>";	//close div_column_header
		$content_html.= "<div style='clear:both;></div>";

		if($this->excel_flag == 1){
			$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
		}

		$currrow = 7;
		for ($j=0; $j<count($arrBrg);$j++)
		{
			$row_brg_html = "<div id='div_column_header' style='text-align:center;line-height:60px;vertical-align:middle;'>";
			$row_brg_html.= "	<div style='width:250px;".$style_col_brg."'>".$arrBrg[$j]."</div>";
			$qty_total = 0;

			if($this->excel_flag == 1){
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $currrow+$j, $arrBrg[$j]);
			}

			$currcol = 1;

			for($i=0;$i<count($PrePo);$i++)
			{
				if ($i%2==0)
					$style_col = $style_col_genap;
				else
					$style_col = $style_col_ganjil;

				$qty = 0;
				$arrDetails = $PrePo[$i]->Detail_PrePO;

				for($x=0; $x<count($arrDetails); $x++)
				{
					if ($arrDetails[$x]->Kd_Brg==$arrBrg[$j])
					{
						$qty = (($mode=="JUAL") ? $arrDetails[$x]->Total_Jual : $arrDetails[$x]->Total_Beli);		
						break;
					}
				}
				$qty_total = $qty_total + $qty;
				$row_brg_html.= "	<div style='width:60px;".$style_col."'>".(($qty==0)? $qty : "<b>".$qty."</b>")."</div>";

				if($this->excel_flag == 1){
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$i, $currrow+$j, $qty);
				}	
			}
			$row_brg_html.= "	<div style='width:80px;".$style_col_total."'><b>".$qty_total."</b></div>";
			$row_brg_html.= "</div>";	//close div_column_header
			$row_brg_html.= "<div style='clear:both;></div>";

			if($this->excel_flag == 1){	
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow(count($PrePo)+1, $currrow+$j, $qty_total);
			}

			if ($p_hide_nol!="Y" || $qty_total!=0)
			{
				$content_html.=$row_brg_html;
			}
			else{
				$this->excel->getActiveSheet()->removeRow($currrow+$j);
				$currrow -= 1;
			}
		}
		
		if($this->excel_flag == 1){
			//PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
			$this->excel->getActiveSheet()->mergeCells('A1:J1');
			for ($i = 'A'; $i !=   $this->excel->getActiveSheet()->getHighestColumn(); $i++) {
			    $this->excel->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
			}

			$filename='LaporanForecast'.$mode.'AllCabangAllPT['.date('Ymd').'].xls'; //save our workbook as this file name
			header('Content-Type: application/vnd.ms-excel'); //mime type
			header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
			header('Cache-Control: max-age=0'); //no cache
			$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
			//force user to download the Excel file without writing it to server's HD
			$objWriter->save('php://output');
		}

		$content_html.= "</div>";
		$content_html.= "</body></html>";


		$data['title'] = $page_title;
		$data['content_html'] = $content_html;
		
		// $this->SetTemplate('template/login');
		$this->RenderView('LaporanResultView',$data);
	}

	public function Preview_DelapanPeriode($mode, $page_title, $p_yy, $p_mm, $p_pp, $p_divisi, $p_cbg="ALL", $p_email="N")
	{
		include APPPATH.'third_party/PHPExcel.php';
		$excel = new PHPExcel();

		$data = array();
		$api = 'APITES';

		$style_col_ganjil = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#ffffcc;";
		$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#fff;";
		$style_col_total = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#f2f2f2;";
		$style_col_brg = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:left;padding-right:5px;padding-left:10px;background-color:#ccffcc;";
		$style_col_fc = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;padding-left:10px;background-color:#ddeeff;";
		$style_col_merah = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#ff1414;";
		$style_col_hijau = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#6fff00;";

		set_time_limit(60);
		ini_set('memory_limit', -1);

		if ($mode=="GROUP GUDANG") {
			/*die($this->API_URL."LaporanPreOrderPembelian/ProsesPerPeriode?api=".$api."&p_yy=".urlencode($p_yy)."&p_mm=".urlencode($p_mm)
									."&p_pp=".urlencode($p_pp)."&p_cbg=".urlencode($p_cbg)."&p_divisi=".urlencode($p_divisi));*/
			$PrePo = json_decode(file_get_contents($this->API_URL."/LaporanPreOrderPembelian/ProsesPerPeriode?api=".$api."&p_yy=".urlencode($p_yy)."&p_mm=".urlencode($p_mm)
									."&p_pp=".urlencode($p_pp)."&p_cbg=".urlencode($p_cbg)."&p_divisi=".urlencode($p_divisi)));
		} else if ($mode=="GABUNGAN") {
			/*die(file_get_contents($this->API_URL."LaporanPreOrderPembelian/ProsesPerPeriodeGabungan?api=".$api."&p_yy=".urlencode($p_yy)."&p_mm=".urlencode($p_mm)
									."&p_pp=".urlencode($p_pp)."&p_cbg=".urlencode($p_cbg)."&p_divisi=".urlencode($p_divisi)."&p_mode=".urlencode($mode)."&p_user=".urlencode($_SESSION['logged_in']['useremail'])));*/
			$PrePo = json_decode(file_get_contents($this->API_URL."/LaporanPreOrderPembelian/ProsesPerPeriodeGabungan?api=".$api."&p_yy=".urlencode($p_yy)."&p_mm=".urlencode($p_mm)
									."&p_pp=".urlencode($p_pp)."&p_cbg=".urlencode($p_cbg)."&p_divisi=".urlencode($p_divisi)."&p_mode=".urlencode($mode)."&p_user=".urlencode($_SESSION['logged_in']['useremail'])));
		} else {
			$PrePo = json_decode(file_get_contents($this->API_URL."/LaporanPreOrderPembelian/ProsesPerPeriodePerKota?api=".$api."&p_yy=".urlencode($p_yy)."&p_mm=".urlencode($p_mm)
									."&p_pp=".urlencode($p_pp)."&p_cbg=".urlencode($p_cbg)."&p_divisi=".urlencode($p_divisi)."&p_mode=".urlencode($mode)."&p_user=".urlencode($_SESSION['logged_in']['useremail'])));
		}
		
		if ($mode=="GROUP GUDANG")
			$judul = "PER KOTA PER GROUP GUDANG";
		else if ($mode=="KOTA")
			$judul = "PER KOTA";
		else if ($mode=="GABUNGAN")
			$judul = "GABUNGAN SEMUA KOTA";
		else
			$judul = "";

		$content_html = "<html><body>";
		$content_html.= "<div id='div_header' style=''>";
		$content_html.= "	<div><h2>LAPORAN FORECAST 8 PERIODE ".$judul."</h2></div>";
		$content_html.= "	<div><b>Divisi : ".$p_divisi."</b></div>";
		$content_html.= "	<div><b>Periode: ".$p_pp."</b></div>";
		$content_html.= "	<div><b>Bln/Thn: ".$p_mm."/".$p_yy."</b></div>";
		$content_html.= "</div>";	//close div_header

		if($this->excel_flag == 1){
			// ini_set('memory_limit', '128M');
			$excel->setActiveSheetIndex(0);
			$excel->getActiveSheet()->setTitle('LaporanForecast8Periode');
			$excel->getActiveSheet()->setCellValue('A1', 'LAPORAN FORECAST 8 PERIODE '.$judul);
			$excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
			$excel->getActiveSheet()->setCellValue('A2', 'Divisi : '.$p_divisi);
			$excel->getActiveSheet()->setCellValue('A3', 'Periode : '.$p_pp);
			$excel->getActiveSheet()->setCellValue('A4', 'Bln/Thn : '.$p_mm."/".$p_yy);
		}

		$currrow = 6;
		$currcol = 0;

		for($i=0;$i<count($PrePo);$i++)
		{		
			$pp_yy = $p_yy;
			$pp_mm = $p_mm;
			$pp_pp = $p_pp;

			$NO_PREPO = $PrePo[$i]->No_PrePo;
			$ADA_PREPO = false;

			$content_html.= "<div class='div_body' style='width:4000px;overflow-x:scroll;'>";
			$content_html.= "<div style='clear:both'></div>";

			if ($mode=="GROUP GUDANG") 
			{
				$content_html.= "<div class='Group_PrePO' style='margin-top:20px;'><b>".$NO_PREPO."</b></div>";			
				$content_html.= "<div class='Group_PrePO' style=''>".$PrePo[$i]->Nm_GroupGudang."</div>";
				if($excel_flag == 1){
					$excel->getActiveSheet()->setCellValueByColumnAndRow(0, $currrow+$i, $NO_PREPO);
					$currrow += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow(0, $currrow+$i, $PrePo[$i]->Nm_GroupGudang);
					$currrow += 1;
				}
			}
			else if ($mode=="KOTA")
			{
				$content_html.= "<div class='Group_PrePO' style='font-size:16pt;margin-top:20px;'><b>".$PrePo[$i]->Nm_GroupGudang."</b></div>";
				if($excel_flag == 1){
					$excel->getActiveSheet()->setCellValueByColumnAndRow(0, $currrow+$i, $PrePo[$i]->Nm_GroupGudang);
					$currrow += 1;
				}
			}
			else if ($mode=="GABUNGAN")
			{}

			$content_html.= "<div id='div_column_header' style='text-align:center;line-height:60px;vertical-align:middle;'>";
			$content_html.= "	<div style='width:220px;height:60px;".$style_col_brg."'><b>Kode Barang</b></div>";
			$content_html.= "	<div style='width:80px;height:60px;".$style_col_total."'><b>Stock Awal</b></div>";

			$currcol = 0;
			if($this->excel_flag == 1){
				$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i, 'Kode Barang');
				$currcol += 1;
				$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i, 'Stock Awal');
				$currcol += 1;
			}

			for ($p=0;$p<8;$p++)
			{
				if ($p%2==0)
					$style_col = $style_col_genap;
				else
					$style_col = $style_col_ganjil;

				$NmPeriode = $this->HelperModel->GetNmPeriode((int)$pp_yy, (int)$pp_mm, (int)$pp_pp);

				//die($NmPeriode);
				$content_html.= "	<div style='width:80px;height:60px;".$style_col."'><b>".$NmPeriode." Beli</b></div>";
				$content_html.= "	<div style='width:80px;height:60px;".$style_col."'><b>".$NmPeriode." Campaign</b></div>";
				$content_html.= "	<div style='width:80px;height:60px;".$style_col."'><b>".$NmPeriode." Jual</b></div>";
				$content_html.= "	<div style='width:80px;height:60px;".$style_col."'><b>".$NmPeriode." (%) Jual</b></div>";
				$content_html.= "	<div style='width:80px;height:60px;".$style_col."'><b>".$NmPeriode." Stock</b></div>";

				if($this->excel_flag == 1){
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$p, $currrow+$i, $NmPeriode.' Beli');
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$p, $currrow+$i, $NmPeriode.' Campaign');
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$p, $currrow+$i, $NmPeriode.' Jual');
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$p, $currrow+$i, $NmPeriode.' (%) Jual');
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol+$p, $currrow+$i, $NmPeriode.' Stock');
				}

				if ($pp_pp==1)
				{
					$pp_pp=2;
				}
				else
				{
					$pp_pp=1;
					$pp_mm=$pp_mm+1;
					if ($pp_mm==13)
					{
						$pp_mm=1;
						$pp_yy=$pp_yy+1;
					}
				}
			}
			$content_html.= "</div>";	//close div_column_header
			$content_html.= "<div style='clear:both;></div>";

			$SUBKATEGORI = "";
			$DT = $PrePo[$i]->Detail;

			$currrow += 1;
		    for($j=0;$j<count($DT);$j++) 
		    {
		    	$currcol = 0;
		    	if ($SUBKATEGORI==$DT[$j]->Jns_Brg)
		    	{
		    	}
		    	else
		    	{
		    		if ($SUBKATEGORI!="") {
		    		//	$content_html.= "</div></div>";
		    		}

		    		$SUBKATEGORI=$DT[$j]->Jns_Brg;
		    		$content_html.= "<div style='padding-left:20px;text-align:left;border:1px solid #000;'><b>".$SUBKATEGORI."</b></div>";

		    		if($this->excel_flag == 1){
						$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $SUBKATEGORI);
						$excel->getActiveSheet()->getStyle('A'.($currrow+$i+$j).':Z'.($currrow+$i+$j))
					    ->getFill()
					    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
					    ->getStartColor()
					    ->setRGB('FFFF00');
						$currrow += 1;
					}
		    	}

		    	if ("create html"=="create html") {
					$content_html.= "<div class='row_barang' style=''>";
					$content_html.= "	<div style='width:220px;".$style_col_brg."'>".$DT[$j]->Kd_Brg."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_total."'>".$DT[$j]->Stock_Awal."</div>";

					$content_html.= "	<div style='width:80px;".$style_col_genap."'>".$DT[$j]->Beli1."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_genap."'>".$DT[$j]->Campaign1."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_genap."'>".$DT[$j]->Jual1."</div>";
					if($p_yy <= 2017 and (12-(int)$p_mm) * 2 >= 1)
						$content_html.= "	<div style='width:80px;".$style_col_genap."'>-</div>";
					else
						$content_html.= "	<div style='width:80px;".($DT[$j]->PersentaseJual1 == 0 ? $style_col_genap : ($DT[$j]->PersentaseJual1 >= 0 ? $style_col_hijau : $style_col_merah))."'>".($DT[$j]->PersentaseJual1 == 0 ? '-' : round($DT[$j]->PersentaseJual1,2))."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_genap."'>".$DT[$j]->Stock1."</div>";

					$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>".$DT[$j]->Beli2."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>".$DT[$j]->Campaign2."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>".$DT[$j]->Jual2."</div>";
					if($p_yy <= 2017 and (12-(int)$p_mm) * 2 >= 2)
						$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>-</div>";
					else
						$content_html.= "	<div style='width:80px;".($DT[$j]->PersentaseJual2 == 0 ? $style_col_ganjil : ($DT[$j]->PersentaseJual2 >= 0 ? $style_col_hijau : $style_col_merah))."'>".($DT[$j]->PersentaseJual2 == 0 ? '-' : round($DT[$j]->PersentaseJual2,2))."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>".$DT[$j]->Stock2."</div>";

					$content_html.= "	<div style='width:80px;".$style_col_genap."'>".$DT[$j]->Beli3."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_genap."'>".$DT[$j]->Campaign3."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_genap."'>".$DT[$j]->Jual3."</div>";
					if($p_yy <= 2017 and (12-(int)$p_mm) * 2 >= 3)
						$content_html.= "	<div style='width:80px;".$style_col_genap."'>-</div>";
					else
						$content_html.= "	<div style='width:80px;".($DT[$j]->PersentaseJual3 == 0 ? $style_col_genap : ($DT[$j]->PersentaseJual3 >= 0 ? $style_col_hijau : $style_col_merah))."'>".($DT[$j]->PersentaseJual3 == 0 ? '-' : round($DT[$j]->PersentaseJual3,2))."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_genap."'>".$DT[$j]->Stock3."</div>";

					$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>".$DT[$j]->Beli4."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>".$DT[$j]->Campaign4."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>".$DT[$j]->Jual4."</div>";
					if($p_yy <= 2017 and (12-(int)$p_mm) * 2 >= 4)
						$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>-</div>";
					else
						$content_html.= "	<div style='width:80px;".($DT[$j]->PersentaseJual4 == 0 ? $style_col_ganjil : ($DT[$j]->PersentaseJual4 >= 0 ? $style_col_hijau : $style_col_merah))."'>".($DT[$j]->PersentaseJual4 == 0 ? '-' : round($DT[$j]->PersentaseJual4,2))."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>".$DT[$j]->Stock4."</div>";

					$content_html.= "	<div style='width:80px;".$style_col_genap."'>".$DT[$j]->Beli5."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_genap."'>".$DT[$j]->Campaign5."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_genap."'>".$DT[$j]->Jual5."</div>";
					if($p_yy <= 2017 and (12-(int)$p_mm) * 2 >= 5)
						$content_html.= "	<div style='width:80px;".$style_col_genap."'>-</div>";
					else
						$content_html.= "	<div style='width:80px;".($DT[$j]->PersentaseJual5 == 0 ? $style_col_genap : ($DT[$j]->PersentaseJual5 >= 0 ? $style_col_hijau : $style_col_merah))."'>".($DT[$j]->PersentaseJual5 == 0 ? '-' : round($DT[$j]->PersentaseJual5,2))."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_genap."'>".$DT[$j]->Stock5."</div>";

					$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>".$DT[$j]->Beli6."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>".$DT[$j]->Campaign6."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>".$DT[$j]->Jual6."</div>";
					if($p_yy <= 2017 and (12-(int)$p_mm) * 2 >= 6)
						$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>-</div>";
					else
						$content_html.= "	<div style='width:80px;".($DT[$j]->PersentaseJual6 == 0 ? $style_col_ganjil : ($DT[$j]->PersentaseJual6 >= 0 ? $style_col_hijau : $style_col_merah))."'>".($DT[$j]->PersentaseJual6 == 0 ? '-' : round($DT[$j]->PersentaseJual6,2))."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>".$DT[$j]->Stock6."</div>";

					$content_html.= "	<div style='width:80px;".$style_col_genap."'>".$DT[$j]->Beli7."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_genap."'>".$DT[$j]->Campaign7."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_genap."'>".$DT[$j]->Jual7."</div>";
					if($p_yy <= 2017 and (12-(int)$p_mm) * 2 >= 7)
						$content_html.= "	<div style='width:80px;".$style_col_genap."'>-</div>";
					else
						$content_html.= "	<div style='width:80px;".($DT[$j]->PersentaseJual7 == 0 ? $style_col_genap : ($DT[$j]->PersentaseJual7 >= 0 ? $style_col_hijau : $style_col_merah))."'>".($DT[$j]->PersentaseJual7 == 0 ? '-' : round($DT[$j]->PersentaseJual7,2))."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_genap."'>".$DT[$j]->Stock7."</div>";

					$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>".$DT[$j]->Beli8."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>".$DT[$j]->Campaign8."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>".$DT[$j]->Jual8."</div>";
					if($p_yy <= 2017 and (12-(int)$p_mm) * 2 >= 8)
						$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>-</div>";
					else
						$content_html.= "	<div style='width:80px;".($DT[$j]->PersentaseJual8 == 0 ? $style_col_ganjil : ($DT[$j]->PersentaseJual8 >= 0 ? $style_col_hijau : $style_col_merah))."'>".($DT[$j]->PersentaseJual8 == 0 ? '-' : round($DT[$j]->PersentaseJual8,2))."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."'>".$DT[$j]->Stock8."</div>";
					$content_html.= "</div>"; //close row_barang
					$content_html.= "<div style='clear:both;></div>";
				}


	    		if($this->excel_flag == 1){
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Kd_Brg);
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Stock_Awal);
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Beli1);
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Campaign1);
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Jual1);
					$currcol += 1;
					if($p_yy <= 2017 and (12-(int)$p_mm) * 2 >= 1)
						$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, '-');
					else
						$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->PersentaseJual1 == 0 ? '-' : round($DT[$j]->PersentaseJual1,2)));
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Stock1);
					$currcol += 1;

					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Beli2);
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Campaign2);
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Jual2);
					$currcol += 1;
					if($p_yy <= 2017 and (12-(int)$p_mm) * 2 >= 2)
						$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, '-');
					else
						$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->PersentaseJual1 == 0 ? '-' : round($DT[$j]->PersentaseJual2,2)));
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Stock2);
					$currcol += 1;

					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Beli3);
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Campaign3);
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Jual3);
					$currcol += 1;
					if($p_yy <= 2017 and (12-(int)$p_mm) * 2 >= 3)
						$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, '-');
					else
						$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->PersentaseJual1 == 0 ? '-' : round($DT[$j]->PersentaseJual3,2)));
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Stock3);
					$currcol += 1;

					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Beli4);
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Campaign4);
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Jual4);
					$currcol += 1;
					if($p_yy <= 2017 and (12-(int)$p_mm) * 2 >= 4)
						$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, '-');
					else
						$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->PersentaseJual1 == 0 ? '-' : round($DT[$j]->PersentaseJual4,2)));
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Stock4);
					$currcol += 1;

					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Beli5);
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Campaign5);
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Jual5);
					$currcol += 1;
					if($p_yy <= 2017 and (12-(int)$p_mm) * 2 >= 5)
						$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, '-');
					else
						$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->PersentaseJual1 == 0 ? '-' : round($DT[$j]->PersentaseJual5,2)));
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Stock5);
					$currcol += 1;
					
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Beli6);
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Campaign6);
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Jual6);
					$currcol += 1;
					if($p_yy <= 2017 and (12-(int)$p_mm) * 2 >= 6)
						$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, '-');
					else
						$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->PersentaseJual1 == 0 ? '-' : round($DT[$j]->PersentaseJual6,2)));
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Stock6);
					$currcol += 1;
					
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Beli7);
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Campaign7);
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Jual7);
					$currcol += 1;
					if($p_yy <= 2017 and (12-(int)$p_mm) * 2 >= 7)
						$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, '-');
					else
						$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->PersentaseJual1 == 0 ? '-' : round($DT[$j]->PersentaseJual7,2)));
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Stock7);
					$currcol += 1;
					
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Beli8);
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Campaign8 );
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Jual8);
					$currcol += 1;
					if($p_yy <= 2017 and (12-(int)$p_mm) * 2 >= 8)
						$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, '-');
					else
						$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->PersentaseJual1 == 0 ? '-' : round($DT[$j]->PersentaseJual8,2)));
					$currcol += 1;
					$excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Stock8);

				}
		    }
		    $currrow += count($DT);
			$content_html.= "</div>";
		}
		$content_html.= "</body></html>";

		if($this->excel_flag == 1){
			//PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
			$excel->getActiveSheet()->mergeCells('A1:J1');
			for ($i = 'A'; $i != $excel->getActiveSheet()->getHighestColumn(); $i++) {
			    $excel->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
			}

			$filename='LaporanForecast8Periode'.$judul.'['.date('Ymd').'].xls'; //save our workbook as this file name
			header('Content-Type: application/vnd.ms-excel'); //mime type
			header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
			header('Cache-Control: max-age=0'); //no cache
			$objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');  
			//force user to download the Excel file without writing it to server's HD
			$objWriter->save('php://output');
			exit(1);
		}


		$data['title'] = $page_title;
		$data['content_html'] = $content_html;
		/*if($p_divisi == 'MIYAKO'){
			echo $content_html;
			exit(1);
		}*/
		// $this->SetTemplate('template/login');
		$this->RenderView('LaporanResultView',$data);
	}

}