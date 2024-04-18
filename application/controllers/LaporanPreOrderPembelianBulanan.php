<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
header('Access-Control-Allow-Origin: *');

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanPreOrderPembelianBulanan extends MY_Controller 
{
	public $excel_flag = 0;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('HelperModel');
		$this->load->library("Excel");
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}
	
	public function index()
	{ 
		$params = array();   
   		$params['LogDate'] = date("Y-m-d H:i:s");
   		$params['UserID'] = $_SESSION["logged_in"]["userid"];
   		$params['UserName'] = $_SESSION["logged_in"]["username"];
   		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module']="LAPORAN PREORDER PEMBELIAN BULANAN"; 
   		$params['TrxID'] = date("YmdHis");
		$params['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN PREORDER PEMBELIAN BULANAN";
   		$params['Remarks']="SUCCESS";
   		$params['RemarksDate'] = date("Y-m-d H:i:s");
   		$this->ActivityLogModel->insert_activity($params); 

		$data = array();
		$api = 'APITES';

		$branches = $this->BranchModel->GetsByUser($_SESSION['logged_in']['useremail']);
		$data['title'] = 'Laporan PreOrder Pembelian Bulanan | '.WEBTITLE;
		$data['months'] = $this->HelperModel->GetMonths();
		$check_divisi = json_decode(file_get_contents($this->API_URL."/LaporanPreOrderPembelian/CheckDivisi?api=".$api."&p_user=".urlencode($_SESSION['logged_in']['useremail'])));
		$data['divisi'] = $check_divisi;
		$this->RenderView('LaporanPreOrderPembelianBulananFormView',$data);
	}

	public function Preview()
	{ 
		$data = array();
		$page_title = 'Laporan PreOrder Pembelian Bulanan';

		if(isset($_POST["btnPreview"])){
			$this->excel_flag = 0;
			$params = array();   
	   		$params['LogDate'] = date("Y-m-d H:i:s");
	   		$params['UserID'] = $_SESSION["logged_in"]["userid"];
	   		$params['UserName'] = $_SESSION["logged_in"]["username"];
	   		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module']="LAPORAN PREORDER PEMBELIAN BULANAN"; 
	   		$params['TrxID'] = date("YmdHis");
			$params['Description']=$_SESSION["logged_in"]["username"]." PROSES PREVIEW REPORT";
	   		$params['Remarks']="";
	   		$params['RemarksDate'] = 'NULL';
	   		$this->ActivityLogModel->insert_activity($params); 
		}
		else{
			$this->excel_flag = 1;
			$params = array();   
	   		$params['LogDate'] = date("Y-m-d H:i:s");
	   		$params['UserID'] = $_SESSION["logged_in"]["userid"];
	   		$params['UserName'] = $_SESSION["logged_in"]["username"];
	   		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module']="LAPORAN PREORDER PEMBELIAN BULANAN"; 
	   		$params['TrxID'] = date("YmdHis");
			$params['Description']=$_SESSION["logged_in"]["username"]." PROSES EXPORT KE EXCEL";
	   		$params['Remarks']="";
	   		$params['RemarksDate'] = 'NULL';
	   		$this->ActivityLogModel->insert_activity($params); 
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
				
				$this->Preview_EnamPeriode("GABUNGAN", $page_title, $_POST["yyyy"], $_POST["mm"], $_POST["pp"], $_POST["divisi"], $_POST["cbg"], $_POST["cbox1"],$params);
				
			}
			else
			{
				redirect("LaporanPreOrderPembelianBulanan");
			}
		}
		else
		{
			redirect("LaporanPreOrderPembelianBulanan");
		}
	}

	public function Preview_EnamPeriode($mode, $page_title, $p_yy, $p_mm, $p_pp, $p_divisi, $p_cbg="ALL", $p_email="N",$params)
	{

		$auth = $this->ModuleModel->getDetail('LaporanPreOrderPembelianBulanan',$_SESSION['role']); 
		$bolehinsert = $auth->can_create;
		$bolehupdate = $auth->can_update;
		$userlogin = $_SESSION['logged_in']['useremail'];

		$data = array();
		$api = 'APITES';
		$username = str_replace(' ', '', (string)$_SESSION['logged_in']['username']);

		$style_col_ganjil = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#ffffcc;";
		$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#fff;";
		$style_col_total = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#f2f2f2;";
		$style_col_brg = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:left;padding-right:5px;padding-left:10px;background-color:#ccffcc;";

		set_time_limit(60);

		//die($this->API_URL."LaporanPreOrderPembelianBulanan/AmbilListData?api=".$api."&p_yy=".urlencode($p_yy)."&p_mm=".urlencode($p_mm)."&p_pp=".urlencode($p_pp)."&p_cbg=".urlencode($p_cbg)."&p_divisi=".urlencode($p_divisi)."&p_mode=".urlencode($mode)."&p_user=".urlencode($userlogin));
		$PrePo = json_decode(file_get_contents($this->API_URL."/LaporanPreOrderPembelianBulanan/AmbilListData?api=".$api."&p_yy=".urlencode($p_yy)."&p_mm=".urlencode($p_mm)."&p_pp=".urlencode($p_pp)."&p_cbg=".urlencode($p_cbg)."&p_divisi=".urlencode($p_divisi)."&p_mode=".urlencode($mode)."&p_user=".urlencode($userlogin)));
		$judul = "GABUNGAN SEMUA KOTA";

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);

		if(count($PrePo) > 0){
			$content_html = "<html><body>";
			if($bolehupdate == 1){
				$content_html.= "<div id='disablingDiv' class='disablingDiv'></div>";
				$content_html.= "<div id='loading' class='loader'></div>";
			}
			$content_html.= "<div id='div_header' style=''>";
			$content_html.= "	<div><h2>LAPORAN PRE ORDER PEMBELIAN BULANAN</h2></div>";
			$content_html.= "	<div><b>Divisi : ".$p_divisi."</b></div>";
			// $content_html.= "	<div><b>Periode: ".$p_pp."</b></div>";
			$content_html.= "	<div><b>Bln/Thn: ".$p_mm."/".$p_yy."</b></div>";
			$content_html.= "</div>";	//close div_header
			$bnykdata = count($PrePo);

			if($bolehupdate == 1)
				$content_html.= "<br><div style='text-align:left'><input type='button' value='SIMPAN DATA' style='width:180px' onclick=SimpanData(".$bnykdata.",'".$p_divisi."',$p_mm,$p_yy,'".$username."')></div>";

			if($this->excel_flag == 1){
				$sheet->setTitle('LaporanPreOrderPembelianBulanan');
				$sheet->setCellValue('A1', 'LAPORAN PRE ORDER PEMBELIAN BULANAN '.$judul);
				$sheet->getStyle('A1')->getFont()->setSize(20);
				$sheet->setCellValue('A2', 'Divisi : '.$p_divisi);
				$sheet->setCellValue('A3', 'Bln/Thn : '.$p_mm."/".$p_yy);
			}

			$currrow = 5;
			$currcol = 0;

			$pp_yy = $p_yy;
			$pp_mm = $p_mm;
			$pp_pp = $p_pp;
			//content
			$content_html.= "<div class='div_body' style='width:2000px;overflow-x:scroll;'>";
			$content_html.= "<div style='clear:both'></div>";
			$content_html.= "<div id='div_column_header' style='text-align:center;line-height:60px;vertical-align:middle;'>";
			$content_html.= "	<div style='width:300px;".$style_col_brg."'><b><br>Jenis Barang</b></div>";
			$content_html.= "	<div style='width:220px;".$style_col_brg."'><b><br>Kode Barang</b></div>";
			$content_html.= "	<div style='width:80px;".$style_col_total."'><b><br>Stock Awal</b></div>";

			$currcol = 1;
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Jenis Barang');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Barang');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Stock Awal');
				$currcol += 1;
			}

			for ($p=0;$p<4;$p++)
			{
				if ($p%2==0)
					$style_col = $style_col_genap;
				else
					$style_col = $style_col_ganjil;

				$NmPeriode = $this->HelperModel->GetNamaBulan((int)$pp_mm-1);
				//die($NmPeriode);
				$content_html.= "	<div style='width:80px;".$style_col."'><b>".$NmPeriode."<br>Beli</b></div>";
				$content_html.= "	<div style='width:80px;".$style_col."'><b>".$NmPeriode."<br>Jual</b></div>";
				$content_html.= "	<div style='width:80px;".$style_col."'><b>".$NmPeriode."<br>Stock</b></div>";

				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($currcol+$p, $currrow, $NmPeriode.' Beli');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol+$p, $currrow, $NmPeriode.' Jual');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol+$p, $currrow, $NmPeriode.' Stock');
				}

				$pp_pp=1;
				$pp_mm=$pp_mm+1;
				if ($pp_mm==13)
				{
					$pp_mm=1;
					$pp_yy=$pp_yy+1;
				}
				
			}
			$content_html.= "</div>";	//close div_column_header
			$content_html.= "<div style='clear:both;></div>";

			$totalstokawal = 0;

			$totalbeli1 = 0;
			$totaljual1 = 0;
			$totalstok1 = 0;

			$totalbeli2 = 0;
			$totaljual2 = 0;
			$totalstok2 = 0;

			$totalbeli3 = 0;
			$totaljual3 = 0;
			$totalstok3 = 0;

			$totalbeli4 = 0;
			$totaljual4 = 0;
			$totalstok4 = 0;

			$currrow += 1;
		    for($j=0;$j<count($PrePo);$j++) 
		    {
		    	$currcol = 1;

				$content_html.= "<div class='row_barang' style=''>";
				$content_html.= "	<div style='width:300px;".$style_col_brg."' id = 'JenisBarang".$j."'>".$PrePo[$j]->Jns_Brg."</div>";
				$content_html.= "	<div style='width:220px;".$style_col_brg."' id = 'KodeBarang".$j."'>".$PrePo[$j]->Kd_Brg."</div>";
				$content_html.= "	<div style='width:80px;".$style_col_total."' id = 'StokAwal".$j."'>".$PrePo[$j]->Stock_Awal."</div>";
				$totalstokawal += $PrePo[$j]->Stock_Awal;

				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$j, $PrePo[$j]->Jns_Brg);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$j, $PrePo[$j]->Kd_Brg);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$j, $PrePo[$j]->Stock_Awal);
					$currcol += 1;
				}

				//--> BELI JUAL STOCK
				$content_html.= "	<div style='width:80px;".$style_col_genap."' id = 'Beli1".$j."'>".($PrePo[$j]->Beli1)."</div>";
				if($bolehupdate == 1)
					$content_html.= "	<div style='width:80px;".$style_col_genap."'><input type='number' name='Jual1".$j."' id = 'Jual1".$j."' value='".($PrePo[$j]->Jual1)."' style='width:82px; height:32px; text-align:right' min=0 onchange='recalcStock(1,$j)' onfocus='setPrevValue(this.value)'></div>";
				else
					$content_html.= "	<div style='width:80px;".$style_col_genap."' id = 'Jual1".$j."'>".$PrePo[$j]->Jual1."</div>";
				$content_html.= "	<div style='width:80px;".$style_col_genap."' id = 'Stok1".$j."'>".$PrePo[$j]->Stock1."</div>";
				$totalbeli1 += ($PrePo[$j]->Beli1);
				$totaljual1 += ($PrePo[$j]->Jual1);
				$totalstok1 += $PrePo[$j]->Stock1;

				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$j, $PrePo[$j]->Beli1);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$j, $PrePo[$j]->Jual1);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$j, $PrePo[$j]->Stock1);
					$currcol += 1;
				}

				$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id = 'Beli2".$j."'>".($PrePo[$j]->Beli2)."</div>";
				if($bolehupdate == 1)
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."'><input type='number' name='Jual2".$j."' id = 'Jual2".$j."' value='".($PrePo[$j]->Jual2)."' style='width:82px; height:32px; text-align:right' min=0 onchange='recalcStock(2,$j)' onfocus='setPrevValue(this.value)'></div>";
				else
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id = 'Jual2".$j."'>".$PrePo[$j]->Jual2."</div>";
				$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id = 'Stok2".$j."'>".$PrePo[$j]->Stock2."</div>";
				$totalbeli2 += ($PrePo[$j]->Beli2);
				$totaljual2 += ($PrePo[$j]->Jual2);
				$totalstok2 += $PrePo[$j]->Stock2;

				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$j, $PrePo[$j]->Beli2);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$j, $PrePo[$j]->Jual2);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$j, $PrePo[$j]->Stock2);
					$currcol += 1;
				}

				$content_html.= "	<div style='width:80px;".$style_col_genap."' id = 'Beli3".$j."'>".($PrePo[$j]->Beli3)."</div>";
				if($bolehupdate == 1)
					$content_html.= "	<div style='width:80px;".$style_col_genap."'><input type='number' name='Jual3".$j."' id = 'Jual3".$j."' value='".($PrePo[$j]->Jual3)."' style='width:82px; height:32px; text-align:right' min=0 onchange='recalcStock(3,$j)' onfocus='setPrevValue(this.value)'></div>";
				else
					$content_html.= "	<div style='width:80px;".$style_col_genap."' id = 'Jual3".$j."'>".$PrePo[$j]->Jual3."</div>";
				$content_html.= "	<div style='width:80px;".$style_col_genap."' id = 'Stok3".$j."'>".$PrePo[$j]->Stock3."</div>";
				$totalbeli3 += ($PrePo[$j]->Beli3);
				$totaljual3 += ($PrePo[$j]->Jual3);
				$totalstok3 += $PrePo[$j]->Stock3;

				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$j, $PrePo[$j]->Beli3);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$j, $PrePo[$j]->Jual3);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$j, $PrePo[$j]->Stock3);
					$currcol += 1;
				}

				$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id = 'Beli4".$j."'>".($PrePo[$j]->Beli4)."</div>";
				if($bolehupdate == 1)
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."'><input type='number' name='Jual4".$j."' id = 'Jual4".$j."' value='".($PrePo[$j]->Jual4)."' style='width:82px; height:32px; text-align:right' min=0 onchange='recalcStock(4,$j)' onfocus='setPrevValue(this.value)'></div>";
				else
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id = 'Jual4".$j."'>".$PrePo[$j]->Jual4."</div>";
				$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id = 'Stok4".$j."'>".$PrePo[$j]->Stock4."</div>";
				$totalbeli4 += ($PrePo[$j]->Beli4);
				$totaljual4 += ($PrePo[$j]->Jual4);
				$totalstok4 += $PrePo[$j]->Stock4;

				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$j, $PrePo[$j]->Beli4);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$j, $PrePo[$j]->Jual4);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$j, $PrePo[$j]->Stock4);
					$currcol += 1;
				}

				$content_html.= "<input type='hidden' id='merk$j' value='".$PrePo[$j]->Merk."'>";
				$content_html.= "<input type='hidden' id='subkategori$j' value='".$PrePo[$j]->SubKategori."'>";

				$content_html.= "</div>"; //close row_barang
				$content_html.= "<div style='clear:both;></div>";
			}

			$currcol = 1;
			$currrow += count($PrePo)+1;
			// baris total
		    $content_html.= "<div class='row_barang' style=''>";
		    $content_html.= "	<div style='width:300px;".$style_col_brg."'><b>TOTAL</b></div>";
			$content_html.= "	<div style='width:220px;".$style_col_brg."'>&nbsp;</div>";
			$content_html.= "	<div style='width:80px;".$style_col_total."' id='totalstokawal'>".($totalstokawal)."</div>";
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, ':');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $totalstokawal);
				$currcol += 1;
			}
			$content_html.= "	<div style='width:80px;".$style_col_genap."' id='totalbeli1'>".($totalbeli1)."</div>";
			$content_html.= "	<div style='width:80px;".$style_col_genap."' id='totaljual1'>".($totaljual1)."</div>";
			$content_html.= "	<div style='width:80px;".$style_col_genap."' id='totalstok1'>".($totalstok1)."</div>";
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $totalbeli1);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $totaljual1);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $totalstok1);
				$currcol += 1;
			}

			$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id='totalbeli2'>".($totalbeli2)."</div>";
			$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id='totaljual2'>".($totaljual2)."</div>";
			$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id='totalstok2'>".($totalstok2)."</div>";
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $totalbeli2);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $totaljual2);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $totalstok2);
				$currcol += 1;;
			}

			$content_html.= "	<div style='width:80px;".$style_col_genap."' id='totalbeli3'>".($totalbeli3)."</div>";
			$content_html.= "	<div style='width:80px;".$style_col_genap."' id='totaljual3'>".($totaljual3)."</div>";
			$content_html.= "	<div style='width:80px;".$style_col_genap."' id='totalstok3'>".($totalstok3)."</div>";
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $totalbeli3);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $totaljual3);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $totalstok3);
				$currcol += 1;
			}
			$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id='totalbeli4'>".($totalbeli4)."</div>";
			$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id='totaljual4'>".($totaljual4)."</div>";
			$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id='totalstok4'>".($totalstok4)."</div>";
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $totalbeli4);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $totaljual4);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $totalstok4);
				$currcol += 1;
			}

			$content_html.= "</div>"; //close row_barang
			$content_html.= "<div style='clear:both;></div>";
			//end baris total
			$content_html.= "</div>"; //close content

			if($bolehupdate == 1)
				$content_html.= "<br><div style='text-align:left'><input type='button' value='SIMPAN DATA' style='width:180px' onclick=SimpanData(".$bnykdata.",'".$p_divisi."',$p_mm,$p_yy,'".$username."')></div>";
			$content_html.= "</body></html>";
		}
		else{
			$url = $this->API_URL."/LaporanPreOrderPembelian/ProsesPerPeriodeGabungan?api=".$api."&p_yy=".urlencode($p_yy)."&p_mm=".urlencode($p_mm)."&p_pp=".urlencode($p_pp)."&p_cbg=".urlencode($p_cbg)."&p_divisi=".urlencode($p_divisi)."&p_mode=".urlencode($mode)."&p_user=".urlencode($userlogin);
			// die($url);
			$PrePo = json_decode(file_get_contents($url));

			$content_html = "<html><body>";

			if($bolehupdate == 1){
				$content_html.= "<div id='disablingDiv' class='disablingDiv'></div>";
				$content_html.= "<div id='loading' class='loader'></div>";
			}
			$content_html.= "<div id='div_header' style=''>";
			$content_html.= "	<div><h2>LAPORAN PRE ORDER PEMBELIAN BULANAN</h2></div>";
			$content_html.= "	<div><b>Divisi : ".$p_divisi."</b></div>";
			$content_html.= "	<div><b>Bln/Thn: ".$p_mm."/".$p_yy."</b></div>";
			$content_html.= "</div>";	//close div_header

			if($this->excel_flag == 1){
				//$this->excel->setActiveSheetIndex(0);
				$sheet->setTitle('LaporanPreOrderPembelianBulanan');
				$sheet->setCellValue('A1', 'LAPORAN PRE ORDER PEMBELIAN BULANAN '.$judul);
				$sheet->getStyle('A1')->getFont()->setSize(20);
				$sheet->setCellValue('A2', 'Divisi : '.$p_divisi);
				$sheet->setCellValue('A3', 'Bln/Thn : '.$p_mm."/".$p_yy);
			}

			$currrow = 5;
			$currcol = 1;

			$bnykdata = 0;
			for($i=0;$i<count($PrePo);$i++){
				$bnykdata += count($PrePo[$i]->Detail);
			}
			if($bolehinsert == 1)
				$content_html.= "<br><div style='text-align:left'><input type='button' value='SIMPAN DATA' style='width:180px' onclick=SimpanData(".$bnykdata.",'".$p_divisi."',$p_mm,$p_yy,'".$username."')></div>";

			for($i=0;$i<count($PrePo);$i++)
			{
				$pp_yy = $p_yy;
				$pp_mm = $p_mm;
				$pp_pp = $p_pp;

				$NO_PREPO = $PrePo[$i]->No_PrePo;
				$ADA_PREPO = false;

				$content_html.= "<div class='div_body' style='width:2500px;overflow-x:scroll;'>";
				$content_html.= "<div style='clear:both'></div>";

				if ($mode=="GROUP GUDANG") 
				{
					$content_html.= "<div class='Group_PrePO' style='margin-top:20px;'><b>".$NO_PREPO."</b></div>";			
					$content_html.= "<div class='Group_PrePO' style=''>".$PrePo[$i]->Nm_GroupGudang."</div>";
					if($this->excel_flag == 1){
						$sheet->setCellValueByColumnAndRow(1, $currrow+$i, $NO_PREPO);
						$currrow += 1;
						$sheet->setCellValueByColumnAndRow(1, $currrow+$i, $PrePo[$i]->Nm_GroupGudang);
						$currrow += 1;
					}
				}
				else if ($mode=="KOTA")
				{
					$content_html.= "<div class='Group_PrePO' style='font-size:16pt;margin-top:20px;'><b>".$PrePo[$i]->Nm_GroupGudang."</b></div>";
					if($this->excel_flag == 1){
						$sheet->setCellValueByColumnAndRow(1, $currrow+$i, $PrePo[$i]->Nm_GroupGudang);
						$currrow += 1;
					}
				}
				else if ($mode=="GABUNGAN")
				{}

				$content_html.= "<div id='div_column_header' style='text-align:center;line-height:60px;vertical-align:middle;'>";
				$content_html.= "	<div style='width:300px;".$style_col_brg."'><b><br>Jenis Barang</b></div>";
				$content_html.= "	<div style='width:220px;".$style_col_brg."'><b><br>Kode Barang</b></div>";
				$content_html.= "	<div style='width:80px;".$style_col_total."'><b><br>Stock Awal</b></div>";

				$currcol = 1;
				if($this->excel_flag == 1){
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i, 'Jenis Barang');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i, 'Kode Barang');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i, 'Stock Awal');
					$currcol += 1;
				}

				for ($p=0;$p<4;$p++)
				{
					if ($p%2==0)
						$style_col = $style_col_genap;
					else
						$style_col = $style_col_ganjil;

					$NmPeriode = $this->HelperModel->GetNamaBulan((int)$pp_mm-1);
					//die($NmPeriode);
					$content_html.= "	<div style='width:80px;".$style_col."'><b>".$NmPeriode."<br>Beli</b></div>";
					$content_html.= "	<div style='width:80px;".$style_col."'><b>".$NmPeriode."<br>Campaign</b></div>";
					$content_html.= "	<div style='width:80px;".$style_col."'><b>".$NmPeriode."<br>Jual</b></div>";
					$content_html.= "	<div style='width:80px;".$style_col."'><b>".$NmPeriode."<br>Stock</b></div>";

					if($this->excel_flag == 1){
						$sheet->setCellValueByColumnAndRow($currcol+$p, $currrow+$i, $NmPeriode.' Beli');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol+$p, $currrow+$i, $NmPeriode.' Campaign');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol+$p, $currrow+$i, $NmPeriode.' Jual');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol+$p, $currrow+$i, $NmPeriode.' Stock');
					}

					$pp_pp=1;
					$pp_mm=$pp_mm+1;
					if ($pp_mm==13)
					{
						$pp_mm=1;
						$pp_yy=$pp_yy+1;
					}
					
				}
				$content_html.= "</div>";	//close div_column_header
				$content_html.= "<div style='clear:both;></div>";

				$SUBKATEGORI = "";
				$DT = $PrePo[$i]->Detail;

				$currrow += 1;

				$totalstokawal = 0;

				$totalbeli1 = 0;
				$totaljual1 = 0;
				$totalstok1 = 0;
				$totalcamp1 = 0;

				$totalbeli2 = 0;
				$totaljual2 = 0;
				$totalstok2 = 0;
				$totalcamp2 = 0;

				$totalbeli3 = 0;
				$totaljual3 = 0;
				$totalstok3 = 0;
				$totalcamp3 = 0;

				$totalbeli4 = 0;
				$totaljual4 = 0;
				$totalstok4 = 0;
				$totalcamp4 = 0;

			    for($j=0;$j<count($DT);$j++) 
			    {
			    	$currcol = 1;

					$content_html.= "<div class='row_barang' style=''>";
					$content_html.= "	<div style='width:300px;".$style_col_brg."' id = 'JenisBarang".$j."'>".$DT[$j]->Jns_Brg."</div>";
					$content_html.= "	<div style='width:220px;".$style_col_brg."' id = 'KodeBarang".$j."'>".$DT[$j]->Kd_Brg."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_total."' id = 'StokAwal".$j."'>".$DT[$j]->Stock_Awal."</div>";
					$totalstokawal += $DT[$j]->Stock_Awal;

					//--> BELI JUAL STOCK
					$content_html.= "	<div style='width:80px;".$style_col_genap."' id = 'Beli1".$j."'>".($DT[$j]->Beli1+$DT[$j]->Beli2)."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_genap."' id = 'Camp1".$j."'>".($DT[$j]->Campaign1+$DT[$j]->Campaign2)."</div>";
					if($bolehinsert == 1)
						$content_html.= "	<div style='width:80px;".$style_col_genap."'><input type='number' name='Jual1".$j."' id = 'Jual1".$j."' value='".($DT[$j]->Jual1+$DT[$j]->Jual2)."' style='width:82px; height:32px; text-align:right' min=0 onchange='recalcStock(1,$j)' onfocus='setPrevValue(this.value)'></div>";
					else
						$content_html.= "	<div style='width:80px;".$style_col_genap."' id = 'Jual1".$j."'>".($DT[$j]->Jual1+$DT[$j]->Jual2)."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_genap."' id = 'Stok1".$j."'>".$DT[$j]->Stock2."</div>";
					$totalbeli1 += ($DT[$j]->Beli1+$DT[$j]->Beli2);
					$totalcamp1 += ($DT[$j]->Campaign1+$DT[$j]->Campaign2);
					$totaljual1 += ($DT[$j]->Jual1+$DT[$j]->Jual2);
					$totalstok1 += $DT[$j]->Stock2;

					$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id = 'Beli2".$j."'>".($DT[$j]->Beli3+$DT[$j]->Beli4)."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id = 'Camp2".$j."'>".($DT[$j]->Campaign3+$DT[$j]->Campaign4)."</div>";
					if($bolehinsert == 1)
						$content_html.= "	<div style='width:80px;".$style_col_ganjil."'><input type='number' name='Jual2".$j."' id = 'Jual2".$j."' value='".($DT[$j]->Jual3+$DT[$j]->Jual4)."' style='width:82px; height:32px; text-align:right' min=0 onchange='recalcStock(2,$j)' onfocus='setPrevValue(this.value)'></div>";
					else
						$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id = 'Jual2".$j."'>".($DT[$j]->Jual3+$DT[$j]->Jual4)."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id = 'Stok2".$j."'>".$DT[$j]->Stock4."</div>";
					$totalbeli2 += ($DT[$j]->Beli3+$DT[$j]->Beli4);
					$totaljual2 += ($DT[$j]->Jual3+$DT[$j]->Jual4);
					$totalstok2 += $DT[$j]->Stock4;
					$totalcamp2 += ($DT[$j]->Campaign3+$DT[$j]->Campaign4);

					$content_html.= "	<div style='width:80px;".$style_col_genap."' id = 'Beli3".$j."'>".($DT[$j]->Beli5+$DT[$j]->Beli6)."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_genap."' id = 'Camp3".$j."'>".($DT[$j]->Campaign5+$DT[$j]->Campaign6)."</div>";
					if($bolehinsert == 1)
						$content_html.= "	<div style='width:80px;".$style_col_genap."'><input type='number' name='Jual3".$j."' id = 'Jual3".$j."' value='".($DT[$j]->Jual5+$DT[$j]->Jual6)."' style='width:82px; height:32px; text-align:right' min=0 onchange='recalcStock(3,$j)' onfocus='setPrevValue(this.value)'></div>";
					else
						$content_html.= "	<div style='width:80px;".$style_col_genap."' id = 'Jual3".$j."'>".($DT[$j]->Jual5+$DT[$j]->Jual6)."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_genap."' id = 'Stok3".$j."'>".$DT[$j]->Stock6."</div>";
					$totalbeli3 += ($DT[$j]->Beli5+$DT[$j]->Beli6);
					$totaljual3 += ($DT[$j]->Jual5+$DT[$j]->Jual6);
					$totalstok3 += $DT[$j]->Stock6;
					$totalcamp3 += ($DT[$j]->Campaign5+$DT[$j]->Campaign6);

					$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id = 'Beli4".$j."'>".($DT[$j]->Beli7+$DT[$j]->Beli8)."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id = 'Camp4".$j."'>".($DT[$j]->Campaign7+$DT[$j]->Campaign8)."</div>";
					if($bolehinsert == 1)
						$content_html.= "	<div style='width:80px;".$style_col_ganjil."'><input type='number' name='Jual4".$j."' id = 'Jual4".$j."' value='".($DT[$j]->Jual7+$DT[$j]->Jual8)."' style='width:82px; height:32px; text-align:right' min=0 onchange='recalcStock(4,$j)' onfocus='setPrevValue(this.value)'></div>";
					else
						$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id = 'Jual4".$j."'>".($DT[$j]->Jual7+$DT[$j]->Jual8)."</div>";
					$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id = 'Stok4".$j."'>".$DT[$j]->Stock8."</div>";
					$totalbeli4 += ($DT[$j]->Beli7+$DT[$j]->Beli8);
					$totaljual4 += ($DT[$j]->Jual8+$DT[$j]->Jual8);
					$totalstok4 += $DT[$j]->Stock8;
					$totalcamp4 += ($DT[$j]->Campaign7+$DT[$j]->Campaign8);

					$content_html.= "<input type='hidden' id='merk$j' value='".$DT[$j]->Merk."'>";
					$content_html.= "<input type='hidden' id='subkategori$j' value='".$DT[$j]->SubKategori."'>";

					$content_html.= "</div>"; //close row_barang
					$content_html.= "<div style='clear:both;></div>";

					$currcol = 1;
		    		if($this->excel_flag == 1){
		    			$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Jns_Brg);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Kd_Brg);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Stock_Awal);
						$currcol += 1;

						$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->Beli1+$DT[$j]->Beli2));
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->Campaign1+$DT[$j]->Campaign2));
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->Jual1+$DT[$j]->Jual2));
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Stock2);
						$currcol += 1;
						
						$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->Beli3+$DT[$j]->Beli4));
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->Campaign3+$DT[$j]->Campaign4));
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->Jual3+$DT[$j]->Jual4));
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Stock4);
						$currcol += 1;

						$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->Beli5+$DT[$j]->Beli6));
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->Campaign5+$DT[$j]->Campaign6));
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->Jual5+$DT[$j]->Jual6));
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Stock6);
						$currcol += 1;

						$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->Beli7+$DT[$j]->Beli8));
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->Campaign7+$DT[$j]->Campaign8));
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, ($DT[$j]->Jual7+$DT[$j]->Jual8));
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+$j, $DT[$j]->Stock8);
						$currcol += 1;
					}
			    }

			    // baris total
			    $content_html.= "<div class='row_barang' style=''>";
			    $content_html.= "	<div style='width:300px;".$style_col_brg."'><b>TOTAL</b></div>";
				$content_html.= "	<div style='width:220px;".$style_col_brg."'>&nbsp;</div>";
				$content_html.= "	<div style='width:80px;".$style_col_total."' id='totalstokawal'>".($totalstokawal)."</div>";

				$content_html.= "	<div style='width:80px;".$style_col_genap."' id='totalbeli1'>".($totalbeli1)."</div>";
				$content_html.= "	<div style='width:80px;".$style_col_genap."' id='totalcamp1'>".($totalcamp1)."</div>";
				$content_html.= "	<div style='width:80px;".$style_col_genap."' id='totaljual1'>".($totaljual1)."</div>";
				$content_html.= "	<div style='width:80px;".$style_col_genap."' id='totalstok1'>".($totalstok1)."</div>";

				$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id='totalbeli2'>".($totalbeli2)."</div>";
				$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id='totalcamp2'>".($totalcamp2)."</div>";
				$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id='totaljual2'>".($totaljual2)."</div>";
				$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id='totalstok2'>".($totalstok2)."</div>";

				$content_html.= "	<div style='width:80px;".$style_col_genap."' id='totalbeli3'>".($totalbeli3)."</div>";
				$content_html.= "	<div style='width:80px;".$style_col_genap."' id='totalcamp3'>".($totalcamp3)."</div>";
				$content_html.= "	<div style='width:80px;".$style_col_genap."' id='totaljual3'>".($totaljual3)."</div>";
				$content_html.= "	<div style='width:80px;".$style_col_genap."' id='totalstok3'>".($totalstok3)."</div>";

				$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id='totalbeli4'>".($totalbeli4)."</div>";
				$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id='totalcamp4'>".($totalcamp4)."</div>";
				$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id='totaljual4'>".($totaljual4)."</div>";
				$content_html.= "	<div style='width:80px;".$style_col_ganjil."' id='totalstok4'>".($totalstok4)."</div>";

				$content_html.= "</div>"; //close row_barang
				$content_html.= "<div style='clear:both;></div>";
				//end baris total
			    $currrow += count($DT);

				$currcol = 1;
	    		if($this->excel_flag == 1){
	    			$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, 'TOTAL');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, ':');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, ($totalstokawal));
					$currcol += 1;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, $totalbeli1);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, $totalcamp1);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, $totaljual1);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, $totalstok1);
					$currcol += 1;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, $totalbeli2);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, $totalcamp2);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, $totaljual2);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, $totalstok2);
					$currcol += 1;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, $totalbeli3);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, $totalcamp3);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, $totaljual3);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, $totalstok3);
					$currcol += 1;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, $totalbeli4);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, $totalcamp4);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, $totaljual4);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow+$i+1, $totalstok4);
					$currcol += 1;
				}

				$content_html.= "</div>";
			}
			if($bolehinsert == 1)
				$content_html.= "<br><div style='text-align:left'><input type='button' value='SIMPAN DATA' style='width:180px' onclick=SimpanData(".$bnykdata.",'".$p_divisi."',$p_mm,$p_yy,'".$username."')></div>";

			$content_html.= "</body></html>";
		}


		if($this->excel_flag == 1){
			//PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
			$sheet->mergeCells('A1:J1');
			for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
			    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
			}

			/*$filename='C:/LaporanPreOrderPembelianBulanan['.date('YmdHis').'].xlsx'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
	        //header('Content-Type: application/vnd.ms-excel');
	        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	        header('Content-Disposition: attachment;filename="'. $filename .'"'); 
	        header('Cache-Control: max-age=0');
	        $writer->save('php://output'); // download file 
	        exit(1);*/
	        
			$filename='LaporanPreOrderPembelianBulanan['.date('YmdHis').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
	        $writer->save('php://output');	// download file 


			$params['Remarks']="SUCCESS";
		   	$params['RemarksDate'] = date("Y-m-d H:i:s");
		   	$this->ActivityLogModel->update_activity($params);
	        exit();

		}
		

		$data['title'] = $page_title;
		$data['content_html'] = $content_html;
	        
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);
		
		$this->SetTemplate('template/notemplate');
		$this->RenderView('LaporanResultView',$data);
	}

	function Simpan(){
		$postdata = $_POST['data'];
		$api = $_POST['api'];

		$opts = array('http' =>
		    array(
		    	'header' => "User-Agent:MyAgent/1.0\r\n",
		        'method'  => 'POST',
		        'header'  => 'Content-type: application/x-www-form-urlencoded',
		        'content' => $postdata
		    )
		);

		$context  = stream_context_create($opts);

		$result = file_get_contents($this->API_URL."/LaporanPreOrderPembelianBulanan/Simpan?api=".$api, false, $context);
		if($result != 'success'){
			echo "some error occured";
			exit(1);
		}
		// echo json_encode($data);
	}

}