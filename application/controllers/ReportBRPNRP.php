<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class ReportBRPNRP extends MY_Controller 
	{
		public $excel_flag = 0; 
		public function __construct()
		{
			parent::__construct();
			$this->load->model('BranchModel');
			$this->load->model('HelperModel');
			$this->load->model('GzipDecodeModel');
			$this->load->helper('FormLibrary');
			$this->load->library('email');
			$this->load->library('excel');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		}
		
		public function index()
		{ 
			$api = 'APITES'; 
			
			$url = $this->API_URL."/MsSupplier/GetSupplierList?api=".$api;
			$getListSupplier = HttpGetRequest($url, $this->API_URL, "Ambil List Supplier");
			$getListSupplier = json_decode($getListSupplier);  
			
			$url = $this->API_URL."/MsGudang/GetListGudangTargetNRP?api=".$api;
			$getListGudangTarget = HttpGetRequest($url, $this->API_URL, "Ambil List Gudang Target");  
			// $getListGudangTarget = json_decode($getListGudangTarget);	
			$getListGudangTarget = $this->GzipDecodeModel->_decodeGzip($getListGudangTarget);  
			// die(json_encode($getListGudangTarget));

			$url = $this->API_URL."/MsGudang/GetListGudangSumberNRP?api=".$api;
			$getListGudangSumber = HttpGetRequest($url, $this->API_URL, "Ambil List Gudang Sumber");  
			// $getListGudangSumber = json_decode($getListGudangSumber);
			$getListGudangSumber = $this->GzipDecodeModel->_decodeGzip($getListGudangSumber);    
			// die(json_encode($getListGudangSumber));

			// $getListGudangTarget = json_decode(file_get_contents($this->API_URL."/MsGudang/GetListGudangTargetNRP?api=".$api));  
			// die(json_encode($getListGudangTarget));
			// $getListGudangSumber = json_decode(file_get_contents($this->API_URL."/MsGudang/GetListGudangSumberNRP?api=".$api));  
			// die(json_encode($getListGudangSumber));
 
			$data['listsupplier'] = $getListSupplier; 
			$data['gudang_sumber'] = $getListGudangSumber; 
			$data['gudang_target'] = $getListGudangTarget; 

			$data['title'] = 'LAPORAN NRP / SURAT JALAN RETUR | '.WEBTITLE;
			$data['formDest'] = "ReportBRPNRP/ProsesNRP";
			
			$this->RenderView('ReportBRPNRPView',$data);
		}
		
		public function ProsesNRP()
		{ 
			die("ProsesNRP");
			$data = array();
			$page_title = 'EXPORT BRP NRP';
			   
			$dp1 = $_POST['dp1']; 
			$dp2 = $_POST['dp2']; 
			$kategori = $_POST['kategori']; 
			$laporan = $_POST['laporan']; 


			$this->confirm_flag = 0;

			if(isset($_POST["btnExcel"])){
				$this->excel_flag = 1;
			}
			else{
				$this->excel_flag = 0;
			} 


			if (isset($_POST["btnPdf"])) {
				$this->pdf_flag = 1;
			} 
			else {
				$this->pdf_flag = 0;
			}

			if ($laporan==1)
			{	
				$gudang_sumber = explode("#",$_POST["gudang_sumber"]);
				$gudang_target = explode("#",$_POST["gudang_target"]); 
				$this->Export_Excel_BRP_NRP($page_title,$_POST['dp1'],$_POST['dp2'],$kategori,$gudang_sumber[0],$gudang_target[0]); 
			} 
			else if ($laporan==2)
			{ 
				$supplier = $_POST['supplier'];  
				if (isset($_POST["btnPdf"])) {
					$this->Export_Pdf_NRP($page_title,$_POST['dp1'],$_POST['dp2'],$kategori,$supplier); 
				} 
				else {
					$this->Export_Excel_NRP($page_title,$_POST['dp1'],$_POST['dp2'],$kategori,$supplier);
				}
			} 
			else if ($laporan==3)
			{  
				$this->Export_Excel_E_FAKTUR($page_title,$_POST['dp1'],$_POST['dp2'],$kategori); 
			}
			else if ($laporan==4)
			{ 
				$supplier = $_POST['supplier']; 
				if (isset($_POST["btnPdf"])) {
					$this->Export_Pdf_SJ_RETUR_DENGAN_NO_BRP($page_title,$_POST['dp1'],$_POST['dp2'],$kategori,$supplier); 
				} 
				else { 
					$this->Export_Excel_SJ_RETUR_DENGAN_NO_BRP($page_title,$_POST['dp1'],$_POST['dp2'],$kategori,$supplier); 
				}  
			} 
		}

		public function Export_Excel_SJ_RETUR_DENGAN_NO_BRP($page_title, $dp1, $dp2, $p_kategori, $p_supplier)
		{ 
			$p_start_date = date("d-M-Y", strtotime($dp1)); 
			$p_end_date = date("d-M-Y", strtotime($dp2)); 
			$data = array();
			$api = 'APITES';
			
			$ReportSJ_Retur_With_No_BRP = json_decode(file_get_contents($this->API_URL."/LaporanNRP/Export_SJ_Retur_With_No_BRP?api=".$api."&p_start_date=".urlencode($dp1)."&p_end_date=".urlencode($dp2)."&p_kategori=".$p_kategori."&p_supplier=".$p_supplier)); 
			if(count($ReportSJ_Retur_With_No_BRP->detail)==0)
			{
				exit('Tidak ada data');
			} 
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0); 
			$content_html = "<html>";
			$content_html = "<head>";
			$content_html = "<style>
							body{font-family:'Calibri',Arial;}
							table{padding:0;margin:0;border-collapse:collapse}
							td, th { border:1px solid #555; padding:2px!important; }
							.td-center { text-align: center; }
							.td-right { text-align:right;}
							</style>";
			$content_html.= "</head>"; //OPEN div_header
			$content_html.= "<body>"; //OPEN BODY div_header
			$content_html.= "	<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "		<div><h2>REKAP SURAT JALAN RETUR DENGAN BRP</h2></div>"; 
			$content_html.= "		<div><b>PERIODE : ".$p_start_date." S/D ".$p_end_date."</b></div>";  
			$content_html.= "	</div>";	//close div_header
			$content_html.= "	<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$content_html.= "	<div style='clear:both'></div>";  

 			$currcol = 1;
			$currrow = 4; 
			if($this->excel_flag == 1){
				$sheet->setTitle('SURAT JALAN RETUR DENGAN BRPN');
				$sheet->setCellValue('A1', 'REKAP SURAT JALAN RETUR DENGAN BRP');   
				$sheet->setCellValue('A2', 'PERIODE : '.$p_start_date.' S/D '.$p_end_date); 
			} 
			$Total_DPP = 0;
			$Total_NNP = 0;
			$Total_Total = 0; 
			$SubTotal_DPP = 0;
			$SubTotal_NNP = 0;
			$SubTotal_Total = 0; 
			$GrandSubTotal_DPP = 0;
			$GrandSubTotal_NNP = 0;
			$GrandSubTotal_Total = 0;  
			$tglsama = "";
			foreach($ReportSJ_Retur_With_No_BRP->detail as $hd){ 
				$tglsama = ""; 
				if ($GrandSubTotal_DPP>0)
				{ 
					//isi total dan sub total preview 
					$content_html.= "<tr>"; 
					$content_html.= "<td colspan='5'></td>";   
					$content_html.= "<td colspan='3'><b>TOTAL</b></td>";  
					$content_html.= "<td class='td-right'><b>".number_format($Total_DPP,2)."</b></td>";  
					$content_html.= "<td class='td-right'><b>".number_format($Total_NNP,2)."</b></td>";   
					$content_html.= "<td class='td-right'><b>".number_format($Total_Total,2)."</b></td>";  
					$content_html.= "<td colspan='2'></td>";  
					$content_html.= "</tr>"; 

					$content_html.= "<tr>"; 
					$content_html.= "<td colspan='5'></td>";   
					$content_html.= "<td colspan='3'><b>SUB TOTAL</b></td>";  
					$content_html.= "<td class='td-right'><b>".number_format($SubTotal_DPP,2)."</b></td>";  
					$content_html.= "<td class='td-right'><b>".number_format($SubTotal_NNP,2)."</b></td>";   
					$content_html.= "<td class='td-right'><b>".number_format($SubTotal_Total,2)."</b></td>";  
					$content_html.= "<td colspan='2'></td>";  
					$content_html.= "</tr>";  
					if($this->excel_flag == 1){  
						//isi total dan sub total
						$sheet->setCellValueByColumnAndRow(6, $currrow,  'TOTAL');
						$sheet->getColumnDimension('F')->setWidth(12); 
						$currcol += 1; 
						$sheet->setCellValueByColumnAndRow(9, $currrow,  number_format($Total_DPP));
						$sheet->getColumnDimension('I')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow(10, $currrow,  number_format($Total_NNP));
						$sheet->getColumnDimension('J')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow(11, $currrow,  number_format($Total_Total));
						$sheet->getColumnDimension('K')->setWidth(12);
						$currcol = 1;
						$currrow ++;  
						$sheet->setCellValueByColumnAndRow(6, $currrow,  'SUB TOTAL');
						$sheet->getColumnDimension('F')->setWidth(12); 
						$currcol += 1; 
						$sheet->setCellValueByColumnAndRow(9, $currrow,  number_format($SubTotal_DPP));
						$sheet->getColumnDimension('I')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow(10, $currrow,  number_format($SubTotal_NNP));
						$sheet->getColumnDimension('J')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow(11, $currrow,  number_format($SubTotal_Total));
						$sheet->getColumnDimension('K')->setWidth(12);
						$currcol = 1;
						$currrow++; 
					}
				}  
				$SubTotal_DPP = 0;
				$SubTotal_NNP = 0;
				$SubTotal_Total = 0;  
				// buat alamat header Preview 
				$content_html.= "<div style='margin-bottom:20px;'>";
				$content_html.= "<table style='font-size:80%; border-collapse: collapse; border: none;'>";
				$content_html.= "<tr style='border: none;'>";
				$content_html.= "<td style='border: none;'><b>PEMBELI</b></td>";
				$content_html.= "<td style='border: none;'></td>";
				$content_html.= "<td style='border: none;'></td>";
				$content_html.= "<td style='border: none;'><b>PENJUAL</b></td>";
				$content_html.= "<td style='border: none;'></td>";
				$content_html.= "<td style='border: none;'></td>";
				$content_html.= "</tr>";
				$content_html.= "<tr style='border: none;'>";
				$content_html.= "<td style='border: none;'><b>Nama </b></td>";
				$content_html.= "<td style='border: none;'><b>       :</b></td>";
				$content_html.= "<td style='border: none;'><b>".$ReportSJ_Retur_With_No_BRP->header[0]->Nm_PKP."</b></td>";
				$content_html.= "<td style='border: none;'><b>Nama </b></td>";
				$content_html.= "<td style='border: none;'><b>       :</b></td>";
				$content_html.= "<td style='border: none;'><b>".$hd->Nm_Supl."</b></td>";
				$content_html.= "</tr>";
				$content_html.= "<tr style='border: none;'>";
				$content_html.= "<td style='border: none;'><b>Alamat </b></td>";
				$content_html.= "<td style='border: none;'><b>       :</b></td>";
				$content_html.= "<td style='border: none;'><b>".$ReportSJ_Retur_With_No_BRP->header[0]->Alm_PKP."</b></td>";
				$content_html.= "<td style='border: none;'><b>Alamat </b></td>";
				$content_html.= "<td style='border: none;'><b>       :</b></td>";
				$content_html.= "<td style='border: none;'><b>".$hd->Alm_Supl."</b></td>";
				$content_html.= "</tr>";
				$content_html.= "<tr style='border: none;'>";
				$content_html.= "<td style='border: none;'><b>NPWP </b></td>";
				$content_html.= "<td style='border: none;'><b>       :</b></td>";
				$content_html.= "<td style='border: none;'><b>".$ReportSJ_Retur_With_No_BRP->header[0]->NPWP_PKP."</b></td>";
				$content_html.= "<td style='border: none;'><b>NPWP </b></td>";
				$content_html.= "<td style='border: none;'><b>       :</b></td>";
				$content_html.= "<td style='border: none;'><b>".$hd->NPWP."</b></td>";
				$content_html.= "</tr>";
				$content_html.= "<table>";
				if($this->excel_flag == 1)
				{
					// buat alamat header Excel
						$currcol = 1; 
						$sheet->setCellValueByColumnAndRow(1, $currrow, 'PEMBELI');
						$sheet->getColumnDimension('A')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(0).$currrow)->getFont()->setBold(true);				
						$sheet->setCellValueByColumnAndRow(7, $currrow, 'PENJUAL');
						$sheet->getColumnDimension('F')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(6).$currrow)->getFont()->setBold(true);
						$currrow++;
						$sheet->setCellValueByColumnAndRow(1, $currrow, 'Nama : ');
						$sheet->getColumnDimension('A')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(0).$currrow)->getFont()->setBold(true);						
						$sheet->setCellValueByColumnAndRow(2, $currrow, $ReportSJ_Retur_With_No_BRP->header[0]->Nm_PKP);
						$sheet->getColumnDimension('B')->setWidth(7); 	
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(1).$currrow)->getFont()->setBold(true);				
						$sheet->setCellValueByColumnAndRow(7, $currrow, 'Nama : ');
						$sheet->getColumnDimension('F')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(6).$currrow)->getFont()->setBold(true);				
						$sheet->setCellValueByColumnAndRow(8, $currrow, $hd->Nm_Supl);
						$sheet->getColumnDimension('G')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(7).$currrow)->getFont()->setBold(true);
						$currrow++;
						$sheet->setCellValueByColumnAndRow(1, $currrow, 'Alamat : ');
						$sheet->getColumnDimension('A')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(0).$currrow)->getFont()->setBold(true);						
						$sheet->setCellValueByColumnAndRow(2, $currrow, $ReportSJ_Retur_With_No_BRP->header[0]->Alm_PKP);
						$sheet->getColumnDimension('B')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(1).$currrow)->getFont()->setBold(true);					
						$sheet->setCellValueByColumnAndRow(7, $currrow, 'Alamat : ');
						$sheet->getColumnDimension('F')->setWidth(7);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(6).$currrow)->getFont()->setBold(true); 				
						$sheet->setCellValueByColumnAndRow(8, $currrow, $hd->Alm_Supl);
						$sheet->getColumnDimension('G')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(7).$currrow)->getFont()->setBold(true);
						$currrow++;
						$sheet->setCellValueByColumnAndRow(1, $currrow, 'NPWP : ');
						$sheet->getColumnDimension('A')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(0).$currrow)->getFont()->setBold(true);						
						$sheet->setCellValueByColumnAndRow(2, $currrow, $ReportSJ_Retur_With_No_BRP->header[0]->NPWP_PKP);
						$sheet->getColumnDimension('B')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(1).$currrow)->getFont()->setBold(true);					
						$sheet->setCellValueByColumnAndRow(7, $currrow, 'NPWP : ');
						$sheet->getColumnDimension('F')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(6).$currrow)->getFont()->setBold(true);				
						$sheet->setCellValueByColumnAndRow(8, $currrow, $hd->NPWP);
						$sheet->getColumnDimension('G')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(7).$currrow)->getFont()->setBold(true);
						$currrow += 2;


						$sheet->setCellValueByColumnAndRow(1, $currrow, 'NRP'); 
						$sheet->getColumnDimension('A')->setWidth(12);  
						$sheet->setCellValueByColumnAndRow(12, $currrow, 'Faktur Pajak'); 
						$sheet->getColumnDimension('L')->setWidth(12);  
						$currrow++;  

						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NOMOR');
						$sheet->getColumnDimension('A')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KODE BARANG');
						$sheet->getColumnDimension('B')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA BARANG');
						$sheet->getColumnDimension('C')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO BRP');
						$sheet->getColumnDimension('D')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'CABANG');
						$sheet->getColumnDimension('E')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'HARGA SATUAN');
						$sheet->getColumnDimension('F')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY');
						$sheet->getColumnDimension('G')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SUBTOTAL');
						$sheet->getColumnDimension('H')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DPP');
						$sheet->getColumnDimension('I')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PPN');
						$sheet->getColumnDimension('J')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
						$sheet->getColumnDimension('K')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NOMOR');
						$sheet->getColumnDimension('L')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TANGGAL');
						$sheet->getColumnDimension('M')->setWidth(12); 
						$currcol += 1;
						$currrow++;  
				} 

				// buat judul table
				$content_html.= "<table style='font-size:9pt!important'>"; 
				$content_html.= "<tr style='background-color:#33ccff;'>"; 
				$content_html.= "<th style='width:80px' colspan='11'>NRP</th>";  
				$content_html.= "<th style='width:100px' colspan='2'>Faktur Pajak</th>";   
				$content_html.= "</tr>"; 
				$content_html.= "<tr style='background-color:#33ccff;'>"; 
				$content_html.= "<th style='width:120px'>NOMOR</th>";
				$content_html.= "<th style='width:120px'>KODE BARANG</th>";
				$content_html.= "<th style='width:380px'>NAMA BARANG</th>"; 
				$content_html.= "<th style='width:80px'>NO BRP</th>";  
				$content_html.= "<th style='width:80px'>CABANG</th>";
				$content_html.= "<th style='width:100px'>HARGA SATUAN</th>";  
				$content_html.= "<th style='width:30px'>QTY</th>";  
				$content_html.= "<th style='width:80px'>SUBTOTAL</th>";  
				$content_html.= "<th style='width:80px'>DPP</th>";  
				$content_html.= "<th style='width:80px'>PPN</th>";  
				$content_html.= "<th style='width:80px'>TOTAL</th>";  
				$content_html.= "<th style='width:120px'>NOMOR</th>";  
				$content_html.= "<th style='width:70px'>TANGGAL</th>";   
				$content_html.= "</tr>";   

				foreach($hd->data as $hdt){ 
 
						if ($tglsama <> date("d-M-Y", strtotime($hdt->Tgl_SJR)))
						{  
							if ($SubTotal_DPP>0)
							{   	
								//isi total 
								$content_html.= "<tr>"; 
								$content_html.= "<td colspan='5'></td>";   
								$content_html.= "<td colspan='3'><b>TOTAL</b></td>";  
								$content_html.= "<td class='td-right'><b>".number_format($Total_DPP,2)."</b></td>";  
								$content_html.= "<td class='td-right'><b>".number_format($Total_NNP,2)."</b></td>";   
								$content_html.= "<td class='td-right'><b>".number_format($Total_Total,2)."</b></td>";  
								$content_html.= "<td colspan='2'></td>";  
								$content_html.= "</tr>"; 
								if($this->excel_flag == 1){
									//isi total
									$sheet->setCellValueByColumnAndRow(6, $currrow,  'TOTAL');
									$sheet->getColumnDimension('F')->setWidth(12); 
									$currcol += 1; 
									$sheet->setCellValueByColumnAndRow(9, $currrow,  number_format($Total_DPP));
									$sheet->getColumnDimension('I')->setWidth(12); 
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow(10, $currrow,  number_format($Total_NNP));
									$sheet->getColumnDimension('J')->setWidth(12); 
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow(11, $currrow,  number_format($Total_Total));
									$sheet->getColumnDimension('K')->setWidth(12);
									$currcol = 1; 
									$currrow++;
								}
							} 

							//isi tanggal transaksi  
							$content_html.= "<tr >";
							$content_html.= "<td colspan='13'><b>Tanggal : ".date("d-M-Y",strtotime($hdt->Tgl_SJR))."</b></td>";
							$content_html.= "</tr>";
							$tglsama = date("d-M-Y", strtotime($hdt->Tgl_SJR));
							if($this->excel_flag == 1){
								//isi tanggal transaksi 
								$sheet->setCellValueByColumnAndRow(1, $currrow, 'Tanggal : '.date("d-M-Y",strtotime($hdt->Tgl_SJR)));
								$sheet->getColumnDimension('A')->setWidth(7); 
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(0).$currrow)->getFont()->setBold(true);	 
								$currrow++;
							}

							$Total_DPP =0;
							$Total_NNP =0;
							$Total_Total =0; 
						}
						$Total_DPP += $hdt->Subtotal;
						$Total_NNP += $hdt->PPN;
						$Total_Total += $hdt->Grandtotal; 

						$SubTotal_DPP += $hdt->Subtotal;
						$SubTotal_NNP += $hdt->PPN;
						$SubTotal_Total += $hdt->Grandtotal;   

						// isi data preview 
						$content_html.= "<tr>"; 
						$content_html.= "<td class='td-left'>".$hdt->No_NRPu."</td>";
						$content_html.= "<td class='td-left'>".$hdt->Kd_Brg."</td>";
						$content_html.= "<td class='td-left'>".$hdt->Nm_Brg."</td>";
						$content_html.= "<td class='td-left'>".$hdt->BRP."</td>";  
						$content_html.= "<td class='td-left'>".$hdt->kota."</td>";  
						$content_html.= "<td class='td-right'>".number_format($hdt->Harga,2)."</td>";  
						$content_html.= "<td class='td-right'>".$hdt->Qty."</td>";  
						$content_html.= "<td class='td-right'>".number_format($hdt->Qty*$hdt->Harga,2)."</td>";  
						$content_html.= "<td class='td-right'>".number_format($hdt->Subtotal,2)."</td>";  
						$content_html.= "<td class='td-right'>".number_format($hdt->PPN,2)."</td>";  
						$content_html.= "<td class='td-right'>".number_format($hdt->Grandtotal,2)."</td>";  
						$content_html.= "<td class='td-left'>".$hdt->No_FakturP."</td>";  
						$content_html.= "<td class='td-left'>".date("d-M-Y", strtotime($hdt->Tgl_FakturP))."</td>";  
						$content_html.= "</tr>";     

						if($this->excel_flag == 1)
						{ 
							//isi data looping
							$currcol = 1;   
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $hdt->No_NRPu);
							$sheet->getColumnDimension('A')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $hdt->Kd_Brg);
							$sheet->getColumnDimension('B')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  $hdt->Nm_Brg);
							$sheet->getColumnDimension('C')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  $hdt->BRP);
							$sheet->getColumnDimension('D')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  $hdt->kota);
							$sheet->getColumnDimension('E')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  number_format($hdt->Harga));
							$sheet->getColumnDimension('F')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  $hdt->Qty);
							$sheet->getColumnDimension('G')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  number_format($hdt->Qty*$hdt->Harga));
							$sheet->getColumnDimension('H')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  number_format($hdt->Subtotal));
							$sheet->getColumnDimension('I')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  number_format($hdt->PPN));
							$sheet->getColumnDimension('J')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  number_format($hdt->Grandtotal));
							$sheet->getColumnDimension('K')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  $hdt->No_FakturP);
							$sheet->getColumnDimension('L')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  date("d-M-Y", strtotime($hdt->Tgl_FakturP)));
							$sheet->getColumnDimension('M')->setWidth(12); 
							$currrow++; 
						}
				}  

				$GrandSubTotal_DPP += $SubTotal_DPP;
				$GrandSubTotal_NNP += $SubTotal_NNP;
				$GrandSubTotal_Total += $SubTotal_Total;
			} 
			//isi total subtotal grandtotal
			$content_html.= "<tr>"; 
			$content_html.= "<td colspan='5'></td>";   
			$content_html.= "<td colspan='3'><b>TOTAL</b></td>";  
			$content_html.= "<td class='td-right'><b>".number_format($Total_DPP,2)."</b></td>";  
			$content_html.= "<td class='td-right'><b>".number_format($Total_NNP,2)."</b></td>";   
			$content_html.= "<td class='td-right'><b>".number_format($Total_Total,2)."</b></td>";  
			$content_html.= "<td colspan='2'></td>";  
			$content_html.= "</tr>"; 

			$content_html.= "<tr>"; 
			$content_html.= "<td colspan='5'></td>";   
			$content_html.= "<td colspan='3'><b>SUB TOTAL</b></td>";  
			$content_html.= "<td class='td-right'><b>".number_format($SubTotal_DPP,2)."</b></td>";  
			$content_html.= "<td class='td-right'><b>".number_format($SubTotal_NNP,2)."</b></td>";   
			$content_html.= "<td class='td-right'><b>".number_format($SubTotal_Total,2)."</b></td>";  
			$content_html.= "<td colspan='2'></td>";  
			$content_html.= "</tr>"; 
 
			$content_html.= "<tr>"; 
			$content_html.= "<td colspan='5'></td>";   
			$content_html.= "<td colspan='3'><b>GRAND TOTAL</b></td>";  
			$content_html.= "<td class='td-right'><b>".number_format($GrandSubTotal_DPP,2)."</b></td>";  
			$content_html.= "<td class='td-right'><b>".number_format($GrandSubTotal_NNP,2)."</b></td>";   
			$content_html.= "<td class='td-right'><b>".number_format($GrandSubTotal_Total,2)."</b></td>";  
			$content_html.= "<td colspan='2'></td>";  
			$content_html.= "</tr>";
			$content_html.= "</table>";
			$content_html.= "</div>"; 
			if($this->excel_flag == 1)
			{  
				$sheet->setCellValueByColumnAndRow(6, $currrow,  'TOTAL');
				$sheet->getColumnDimension('F')->setWidth(12); 
				$currcol += 1; 
				$sheet->setCellValueByColumnAndRow(9, $currrow,  number_format($Total_DPP));
				$sheet->getColumnDimension('I')->setWidth(12); 
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow(10, $currrow,  number_format($Total_NNP));
				$sheet->getColumnDimension('J')->setWidth(12); 
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow(11, $currrow,  number_format($Total_Total));
				$sheet->getColumnDimension('K')->setWidth(12);
				$currcol = 1;
				$currrow ++;  
				$sheet->setCellValueByColumnAndRow(6, $currrow,  'SUB TOTAL');
				$sheet->getColumnDimension('F')->setWidth(12); 
				$currcol += 1; 
				$sheet->setCellValueByColumnAndRow(9, $currrow,  number_format($SubTotal_DPP));
				$sheet->getColumnDimension('I')->setWidth(12); 
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow(10, $currrow,  number_format($SubTotal_NNP));
				$sheet->getColumnDimension('J')->setWidth(12); 
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow(11, $currrow,  number_format($SubTotal_Total));
				$sheet->getColumnDimension('K')->setWidth(12);
				$currcol = 1; 

				$currrow+=2; 
				$sheet->setCellValueByColumnAndRow(6, $currrow,  'GRAND TOTAL');
				$sheet->getColumnDimension('F')->setWidth(12); 
				$currcol += 1; 
				$sheet->setCellValueByColumnAndRow(9, $currrow,  number_format($GrandSubTotal_DPP));
				$sheet->getColumnDimension('I')->setWidth(12); 
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow(10, $currrow,  number_format($GrandSubTotal_NNP));
				$sheet->getColumnDimension('J')->setWidth(12); 
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow(11, $currrow,  number_format($GrandSubTotal_Total));
				$sheet->getColumnDimension('K')->setWidth(12); 
				$currcol = 1; 
			} 

			if($this->excel_flag == 1){ 
				$filename='REKAP_SURAT_JALAN_RETUR['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 
				exit();
			}

			$content_html.= "</div>"; //CLOSE div_header
			$content_html.= "</body></html>"; //CLOSE BODY div_header
			
			if ($this->pdf_flag == 1)
			{ 
				//echo $content_html;
				$this->Pdf_Report("", $content_html);
				exit();
			}

			$data['title'] = $page_title;
			$data['content_html'] = $content_html;
			

			$this->load->view('LaporanResultView',$data);

		}

		public function Export_Pdf_SJ_RETUR_DENGAN_NO_BRP($page_title, $dp1, $dp2, $p_kategori, $p_supplier)
		{
			die("Export_Pdf_SJ_Retur_Dengan_No_BRP");
			$p_start_date = date("d-M-Y", strtotime($dp1)); 
			$p_end_date = date("d-M-Y", strtotime($dp2)); 
			$data = array();
			$api = 'APITES';
			
			$ReportSJ_Retur_With_No_BRP = json_decode(file_get_contents($this->API_URL."/LaporanNRP/Export_SJ_Retur_With_No_BRP?api=".$api."&p_start_date=".urlencode($dp1)."&p_end_date=".urlencode($dp2)."&p_kategori=".$p_kategori."&p_supplier=".$p_supplier)); 
			if(count($ReportSJ_Retur_With_No_BRP->detail)==0)
			{
				exit('Tidak ada data');
			} 
			$style_col_ganjil ="float:left;line-height:15px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#ffffcc;font-size:10px;";
			$style_col_genap = "float:left;line-height:15px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#fff;font-size:8px;";
			$style_header= "float:left;min-height:20px;line-height:15px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#cccccc;font-size:8px;";
			$style_footer= "float:left;min-height:20px;line-height:20px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#cccccc;font-size:10px;";
			$style_col_no_border = "float:left;line-height:15px;vertical-align:middle;border-right:0px solid #ccc;border:0x solid #ccc;background-color:#fff;font-size:10px;";
			
			$style_col = $style_col_genap;
			$kanan = "text-align:right;padding-right:5px;";
			$kiri  = "text-align:left; padding-left: 5px;";
			$center= "text-align:center;";



			$content_html= "<div>";  
			$header_html = "<div style='clear:both;height:25px;'></div>";
			$header_html.= "<div id='div_header' style='padding-left:10px;'>";
			$header_html.= "<div><h2>REKAP SURAT JALAN RETUR DENGAN BRP</h2></div>";
			$header_html.= "<div><b>Periode : ".$p_start_date." S/D ".$p_end_date."</b></div>"; 
			$header_html.= "</div>";	

 
			$Total_DPP = 0;
			$Total_NNP = 0;
			$Total_Total = 0; 
			$SubTotal_DPP = 0;
			$SubTotal_NNP = 0;
			$SubTotal_Total = 0; 
			$GrandSubTotal_DPP = 0;
			$GrandSubTotal_NNP = 0;
			$GrandSubTotal_Total = 0;  
			$tglsama = "";

			$height = 20; 
			$width = 6;
				$group_header = "	<div id='div_column_header' style='width:100%!important;padding-left:5px;'>";
				$group_header.= "		<div style='width:8%;".$style_header.$kiri."'><b>NOMOR</b></div>";
				$group_header.= "		<div style='width:".$width."%;".$style_header.$kiri."'><b>KODE BARANG</b></div>";
				$group_header.= "		<div style='width:15%;".$style_header.$kiri."'><b>NAMA BARANG</b></div>";
				$group_header.= "		<div style='width:".$width."%;".$style_header.$kiri."'><b>BRP</b></div>";
				$group_header.= "		<div style='width:".$width."%;".$style_header.$kiri."'><b>CABANG</b></div>"; 
				$group_header.= "		<div style='width:7%;".$style_header.$kanan."'><b>HARGA SATUAN</b></div>";
				$group_header.= "		<div style='width:3%;".$style_header.$kanan."'><b>QTY</b></div>";
				$group_header.= "		<div style='width:".$width."%;".$style_header.$kanan."'><b>SUBTOTAL</b></div>";
				$group_header.= "		<div style='width:".$width."%;".$style_header.$kanan."'><b>DPP</b></div>";
				$group_header.= "		<div style='width:".$width."%;".$style_header.$kanan."'><b>PPN</b></div>";
				$group_header.= "		<div style='width:".$width."%;".$style_header.$kanan."'><b>TOTAL</b></div>";
				$group_header.= "		<div style='width:8%;".$style_header.$kiri."'><b>NOMOR</b></div>";
				$group_header.= "		<div style='width:8%;".$style_header.$kiri."'><b>TANGGAL</b></div>";
				$group_header.= "	</div>";	//close div_column_header 

			foreach($ReportSJ_Retur_With_No_BRP->detail as $hd){  
				$tglsama = ""; 
				if ($GrandSubTotal_DPP>0)
				{ 			
					$content_html.= "	<div id='div_column_header' style='width:100%!important;padding-left:5px;'>"; 
					$content_html.= "<div style='width:62%;max-height:".$height."px;".$style_col.$kanan."'><b>TOTAL</b></div>"; 
					$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($Total_DPP,2)."</b></div>";
					$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($Total_NNP,2)."</b></div>";
					$content_html.= "<div style='width:23%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($Total_Total,2)."</b></div>"; 
					$content_html.= "	</div>";
					$content_html.= "	<div style='clear:both;'></div>"; 

					$content_html.= "	<div id='div_column_header' style='width:100%!important;padding-left:5px;'>";
					$content_html.= "<div style='width:62%;max-height:".$height."px;".$style_col.$kanan."'><b>SUB TOTAL</b></div>"; 
					$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($SubTotal_DPP,2)."</b></div>";
					$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($SubTotal_NNP,2)."</b></div>";
					$content_html.= "<div style='width:23%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($SubTotal_Total,2)."</b></div>";  
								$content_html.= "	</div>";
					$content_html.= "	<div style='clear:both;'></div>";
					//isi total dan sub total preview  
				}  
				$SubTotal_DPP = 0;
				$SubTotal_NNP = 0;
				$SubTotal_Total = 0;  
				// buat alamat header Preview 
				$content_html.= "<table>"; 
				$content_html.= "<tr>";
				$content_html.= "<td><b>PEMBELI</b></td>";
				$content_html.= "<td></td>";
				$content_html.= "<td></td>";
				$content_html.= "<td><b>PENJUAL</b></td>";
				$content_html.= "<td></td>";
				$content_html.= "<td></td>";
				$content_html.= "</tr>";
				$content_html.= "<tr>";
				$content_html.= "<td><b>Nama </b></td>";
				$content_html.= "<td><b>       :</b></td>";
				$content_html.= "<td><b>".$ReportSJ_Retur_With_No_BRP->header[0]->Nm_PKP."</b></td>";
				$content_html.= "<td><b>Nama </b></td>";
				$content_html.= "<td><b>       :</b></td>";
				$content_html.= "<td><b>".$hd->Nm_Supl."</b></td>";
				$content_html.= "</tr>";
				$content_html.= "<tr>";
				$content_html.= "<td><b>Alamat </b></td>";
				$content_html.= "<td><b>       :</b></td>";
				$content_html.= "<td><b>".$ReportSJ_Retur_With_No_BRP->header[0]->Alm_PKP."</b></td>";
				$content_html.= "<td><b>Alamat </b></td>";
				$content_html.= "<td><b>       :</b></td>";
				$content_html.= "<td><b>".$hd->Alm_Supl."</b></td>";
				$content_html.= "</tr>";
				$content_html.= "<tr>";
				$content_html.= "<td><b>NPWP </b></td>";
				$content_html.= "<td><b>       :</b></td>";
				$content_html.= "<td><b>".$ReportSJ_Retur_With_No_BRP->header[0]->NPWP_PKP."</b></td>";
				$content_html.= "<td><b>NPWP </b></td>";
				$content_html.= "<td><b>       :</b></td>";
				$content_html.= "<td><b>".$hd->NPWP."</b></td>";
				$content_html.= "</tr>";
				$content_html.= "</table>";
				// buat judul table   

				$content_html.= $group_header; 
				foreach($hd->data as $hdt){ 
 
						if ($tglsama <> date("d-M-Y", strtotime($hdt->Tgl_SJR)))
						{  
							if ($SubTotal_DPP>0)
							{   	
								//isi total 
								$content_html.= "	<div id='div_column_header' style='width:100%!important;padding-left:5px;'>"; 
								$content_html.= "<div style='width:62%;max-height:".$height."px;".$style_col.$kanan."'><b>TOTAL</b></div>"; 
								$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($Total_DPP,2)."</b></div>";
								$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($Total_NNP,2)."</b></div>";
								$content_html.= "<div style='width:23%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($Total_Total,2)."</b></div>"; 
								$content_html.= "	</div>";
								$content_html.= "	<div style='clear:both;'></div>"; 
							} 

							//isi tanggal transaksi   
							$content_html.= "<div id='div_column_header' style='width:100%!important;padding-left:5px;'>";
							$content_html.= "<div style='width:99%;max-height:".$height."px;".$style_col.$kiri."'><b>Tanggal : ".date("d-M-Y",strtotime($hdt->Tgl_NRPu))."</b></div>"; 
							$content_html.= "</div>";
							$content_html.= "<div style='clear:both;'></div>"; 



							$tglsama = date("d-M-Y", strtotime($hdt->Tgl_SJR)); 
							$Total_DPP =0;
							$Total_NNP =0;
							$Total_Total =0; 
						}
						$Total_DPP += $hdt->Subtotal;
						$Total_NNP += $hdt->PPN;
						$Total_Total += $hdt->Grandtotal; 

						$SubTotal_DPP += $hdt->Subtotal;
						$SubTotal_NNP += $hdt->PPN;
						$SubTotal_Total += $hdt->Grandtotal;   

						$content_html.= "<div id='div_column_header' style='width:100%!important;padding-left:5px;'>";
						$content_html.= "<div style='width:8%;max-height:".$height."px;".$style_col.$kiri."'>".$hdt->No_NRPu."</div>";
						$content_html.= "<div style='width:".$width."%;max-height:".$height."px;".$style_col.$kiri."'>".$hdt->Kd_Brg."</div>";
						$content_html.= "<div style='width:15%;max-height:".$height."px;".$style_col.$kiri."'>".$hdt->Nm_Brg."</div>";
						$content_html.= "<div style='width:".$width."%;max-height:".$height."px;".$style_col.$kiri."'>".$hdt->BRP."</div>";
						$content_html.= "<div style='width:".$width."%;max-height:".$height."px;".$style_col.$kiri."'>".$hdt->kota."</div>";
						$content_html.= "<div style='width:7%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($hdt->Harga,2)."</div>";
						$content_html.= "<div style='width:3%;max-height:".$height."px;".$style_col.$kanan."'>".$hdt->Qty."</div>";
						$content_html.= "<div style='width:".$width."%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($hdt->Qty*$hdt->Harga,2)."</div>";
						$content_html.= "<div style='width:".$width."%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($hdt->Subtotal,2)."</div>";
						$content_html.= "<div style='width:".$width."%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($hdt->PPN,2)."</div>";
						$content_html.= "<div style='width:".$width."%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($hdt->Grandtotal,2)."</div>";
						$content_html.= "<div style='width:8%;max-height:".$height."px;".$style_col.$kiri."'>".$hdt->No_FakturP."</div>";
						$content_html.= "<div style='width:8%;max-height:".$height."px;".$style_col.$kiri."'>".date("d-M-Y", strtotime($hdt->Tgl_FakturP))."</div>";
						$content_html.= "</div>";
						$content_html.= "<div style='clear:both;'></div>";
						// isi data preview 
						   
				}  

				$GrandSubTotal_DPP += $SubTotal_DPP;
				$GrandSubTotal_NNP += $SubTotal_NNP;
				$GrandSubTotal_Total += $SubTotal_Total;
			}
			$content_html.= "</div>";   
			//isi total subtotal grandtotal
			$content_html.= "	<div id='div_column_header' style='width:100%!important;padding-left:5px;'>"; 
			$content_html.= "<div style='width:62%;max-height:".$height."px;".$style_col.$kanan."'><b>TOTAL</b></div>"; 
			$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($Total_DPP,2)."</b></div>";
			$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($Total_NNP,2)."</b></div>";
			$content_html.= "<div style='width:23%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($Total_Total,2)."</b></div>"; 
			$content_html.= "	</div>";
			$content_html.= "	<div style='clear:both;'></div>"; 

			$content_html.= "	<div id='div_column_header' style='width:100%!important;padding-left:5px;'>";
			$content_html.= "<div style='width:62%;max-height:".$height."px;".$style_col.$kanan."'><b>SUB TOTAL</b></div>"; 
			$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($SubTotal_DPP,2)."</b></div>";
			$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($SubTotal_NNP,2)."</b></div>";
			$content_html.= "<div style='width:23%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($SubTotal_Total,2)."</b></div>";  
						$content_html.= "	</div>";
			$content_html.= "	<div style='clear:both;'></div>";

			$content_html.= "	<div id='div_column_header' style='width:100%!important;padding-left:5px;'>";
			$content_html.= "<div style='width:62%;max-height:".$height."px;".$style_col.$kanan."'><b>GRAND TOTAL</b></div>"; 
			$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($GrandSubTotal_DPP,2)."</b></div>";
			$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($GrandSubTotal_NNP,2)."</b></div>";
			$content_html.= "<div style='width:23%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($GrandSubTotal_Total,2)."</b></div>";  
			$content_html.= "	</div>";
			$content_html.= "	<div style='clear:both;'></div>";  
 
			$this->Pdf_Report($header_html, $content_html);
			 
		}
		public function Export_Pdf_NRP($page_title, $dp1, $dp2, $p_kategori, $p_supplier)
		{
			die("Export_Pdf_NRP");
			$p_start_date = date("d-M-Y", strtotime($dp1)); 
			$p_end_date = date("d-M-Y", strtotime($dp2)); 
			$data = array();
			$api = 'APITES';
			
			$ReportPdfNRP = json_decode(file_get_contents($this->API_URL."/LaporanNRP/ExportNRP?api=".$api."&p_start_date=".urlencode($dp1)."&p_end_date=".urlencode($dp2)."&p_kategori=".$p_kategori."&p_supplier=".$p_supplier));
			//echo count($ReportBRPNRP); 

			if(count($ReportPdfNRP->detail)==0){
				exit('Tidak ada data');
			}

			$style_col_ganjil ="float:left;line-height:15px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#ffffcc;font-size:10px;";
			$style_col_genap = "float:left;line-height:15px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#fff;font-size:8px;";
			$style_header= "float:left;min-height:20px;line-height:15px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#cccccc;font-size:8px;";
			$style_footer= "float:left;min-height:20px;line-height:20px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#cccccc;font-size:10px;";
			$style_col_no_border = "float:left;line-height:15px;vertical-align:middle;border-right:0px solid #ccc;border:0x solid #ccc;background-color:#fff;font-size:10px;";
			
			$style_col = $style_col_genap;
			$kanan = "text-align:right;padding-right:5px;";
			$kiri  = "text-align:left; padding-left: 5px;";
			$center= "text-align:center;";
			
			$header_html = "<div style='clear:both;height:25px;'></div>";
			$header_html.= "<div id='div_header' style='padding-left:10px;'>";
			$header_html.= "<div><h2>REKAP NOTA RETUR PEMBELIAN</h2></div>";
			$header_html.= "<div><b>Periode : ".$p_start_date." S/D ".$p_end_date."</b></div>"; 
			$header_html.= "</div>";	//close div_header
			
 
			$Total_DPP = 0;
			$Total_NNP = 0;
			$Total_Total = 0; 
			$SubTotal_DPP = 0;
			$SubTotal_NNP = 0;
			$SubTotal_Total = 0; 
			$GrandSubTotal_DPP = 0;
			$GrandSubTotal_NNP = 0;
			$GrandSubTotal_Total = 0; 
			$tglsama = "";   

			$height = 20; 
			$width = 6;
				$group_header = "	<div id='div_column_header' style='width:100%!important;padding-left:5px;'>";
				$group_header.= "		<div style='width:8%;".$style_header.$kiri."'><b>NOMOR</b></div>";
				$group_header.= "		<div style='width:".$width."%;".$style_header.$kiri."'><b>KODE BARANG</b></div>";
				$group_header.= "		<div style='width:15%;".$style_header.$kiri."'><b>NAMA BARANG</b></div>";
				$group_header.= "		<div style='width:7%;".$style_header.$kanan."'><b>HARGA SATUAN</b></div>";
				$group_header.= "		<div style='width:3%;".$style_header.$kanan."'><b>QTY</b></div>";
				$group_header.= "		<div style='width:".$width."%;".$style_header.$kanan."'><b>SUBTOTAL</b></div>";
				$group_header.= "		<div style='width:".$width."%;".$style_header.$kanan."'><b>DPP</b></div>";
				$group_header.= "		<div style='width:".$width."%;".$style_header.$kanan."'><b>PPN</b></div>";
				$group_header.= "		<div style='width:".$width."%;".$style_header.$kanan."'><b>TOTAL</b></div>";
				$group_header.= "		<div style='width:8%;".$style_header.$kiri."'><b>NOMOR</b></div>";
				$group_header.= "		<div style='width:5%;".$style_header.$kiri."'><b>TANGGAL</b></div>";
				$group_header.= "		<div style='width:".$width."%;".$style_header.$kiri."'><b>BRP</b></div>";
				$group_header.= "		<div style='width:".$width."%;".$style_header.$kiri."'><b>CABANG</b></div>"; 
				$group_header.= "	</div>";	//close div_column_header 
				//buat judul table  
				$group_footer = "	<div id='div_column_header' style='width:100%!important;'>";
 
				$group_footer.= "	</div>";
 
				$content_html = "<div class='div_body' style='font-size:9pt!important;'>"; 
				foreach($ReportPdfNRP->detail as $hd)
				{ 

					$tglsama = ""; 
					if ($GrandSubTotal_DPP>0)
					{	
						$content_html.= "	<div id='div_column_header' style='width:100%!important;padding-left:5px;'>"; 
						$content_html.= "<div style='width:48%;max-height:".$height."px;".$style_col.$kanan."'><b>TOTAL</b></div>"; 
						$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($Total_DPP,2)."</b></div>";
						$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($Total_NNP,2)."</b></div>";
						$content_html.= "<div style='width:34%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($Total_Total,2)."</b></div>"; 
						$content_html.= "</div>";
						$content_html.= "<div style='clear:both;'></div>"; 

						$content_html.= "<div id='div_column_header' style='width:100%!important;padding-left:5px;'>";
						$content_html.= "<div style='width:48%;max-height:".$height."px;".$style_col.$kanan."'><b>SUB TOTAL</b></div>"; 
						$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($SubTotal_DPP,2)."</b></div>";
						$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($SubTotal_NNP,2)."</b></div>";
						$content_html.= "<div style='width:34%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($SubTotal_Total,2)."</b></div>";  
						$content_html.= "</div>";
						$content_html.= "<div style='clear:both;'></div>";
					} 
						
					$SubTotal_DPP = 0;
					$SubTotal_NNP = 0;
					$SubTotal_Total = 0; 
					//isi data table
					// buat header  

					$content_html.= "<div style='width:100%!important;padding-left:5px;'>"; 
					$content_html.= "<div style='width:50%;max-height:".$height."px;".$style_col_no_border.$kiri."'><b>PEMBELI</b></div>"; 
					$content_html.= "<div style='width:49%;max-height:".$height."px;".$style_col_no_border.$kiri."'><b>PENJUAL</b></div>"; 
					$content_html.= "</div>";
					$content_html.= "<div style='clear:both;'></div>"; 

					$content_html.= "<div style='width:100%!important;padding-left:5px;'>"; 
					$content_html.= "<div style='width:4%;max-height:".$height."px;".$style_col_no_border.$kiri."'><b>Nama</b></div>";  
					$content_html.= "<div style='width:45%;max-height:".$height."px;".$style_col_no_border.$kiri."'><b> : ".$ReportPdfNRP->header[0]->Nm_PKP."</b></div>"; 
					$content_html.= "<div style='width:4%;max-height:".$height."px;".$style_col_no_border.$kiri."'><b>Nama</b></div>";  
					$content_html.= "<div style='width:45%;max-height:".$height."px;".$style_col_no_border.$kiri."'><b> : ".$hd->Nm_Supl."</b></div>"; 
					$content_html.= "</div>";
					$content_html.= "<div style='clear:both;'></div>";

					$content_html.= "<div style='width:100%!important;padding-left:5px;'>"; 
					$content_html.= "<div style='width:4%;max-height:".$height."px;".$style_col_no_border.$kiri."'><b>Alamat</b></div>";  
					$content_html.= "<div style='width:45%;max-height:".$height."px;".$style_col_no_border.$kiri."'><b> : ".$ReportPdfNRP->header[0]->Alm_PKP."</b></div>"; 
					$content_html.= "<div style='width:4%;max-height:".$height."px;".$style_col_no_border.$kiri."'><b>Alamat</b></div>";  
					$content_html.= "<div style='width:45%;max-height:".$height."px;".$style_col_no_border.$kiri."'><b> : ".$hd->Alm_Supl."</b></div>"; 
					$content_html.= "</div>";
					$content_html.= "<div style='clear:both;'></div>";

					$content_html.= "<div style='width:100%!important;padding-left:5px;'>"; 
					$content_html.= "<div style='width:4%;max-height:".$height."px;".$style_col_no_border.$kiri."'><b>NPWP</b></div>";  
					$content_html.= "<div style='width:45%;max-height:".$height."px;".$style_col_no_border.$kiri."'><b> : ".$ReportPdfNRP->header[0]->Nm_PKP."</b></div>"; 
					$content_html.= "<div style='width:4%;max-height:".$height."px;".$style_col_no_border.$kiri."'><b>NPWP</b></div>";  
					$content_html.= "<div style='width:45%;max-height:".$height."px;".$style_col_no_border.$kiri."'><b> : ".$hd->NPWP."</b></div>"; 
					$content_html.= "</div>";
					$content_html.= "<div style='clear:both;'></div>";
				 	$content_html.= "<div style='clear:both;'></div>"; 

					$content_html.= $group_header; 

					//looping data 
					$content_html.= "	<div style='clear:both;'></div>";
					foreach($hd->data as $hdt)
					{ 
						if ($tglsama <> date("d-M-Y", strtotime($hdt->Tgl_NRPu)))
						{  

							if ($SubTotal_DPP>0)
							{ 
								$content_html.= "	<div id='div_column_header' style='width:100%!important;padding-left:5px;'>"; 
								$content_html.= "<div style='width:48%;max-height:".$height."px;".$style_col.$kanan."'><b>TOTAL</b></div>"; 
								$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($Total_DPP,2)."</b></div>";
								$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($Total_NNP,2)."</b></div>";
								$content_html.= "<div style='width:34%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($Total_Total,2)."</b></div>"; 
								$content_html.= "	</div>";
								$content_html.= "	<div style='clear:both;'></div>"; 
							}


							$content_html.= "	<div id='div_column_header' style='width:100%!important;padding-left:5px;'>";
							$content_html.= "<div style='width:96%;max-height:".$height."px;".$style_col.$kiri."'><b>Tanggal : ".date("d-M-Y",strtotime($hdt->Tgl_NRPu))."</b></div>"; 
							$content_html.= "	</div>";
							$content_html.= "	<div style='clear:both;'></div>"; 
							$Total_DPP =0;
							$Total_NNP =0;
							$Total_Total =0; 
						} 

						$content_html.= "	<div id='div_column_header' style='width:100%!important;padding-left:5px;'>";
						$content_html.= "<div style='width:8%;max-height:".$height."px;".$style_col.$kiri."'>".$hdt->No_NRPu."</div>";
						$content_html.= "<div style='width:".$width."%;max-height:".$height."px;".$style_col.$kiri."'>".$hdt->Kd_Brg."</div>";
						$content_html.= "<div style='width:15%;max-height:".$height."px;".$style_col.$kiri."'>".$hdt->Nm_Brg."</div>";
						$content_html.= "<div style='width:7%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($hdt->Harga,2)."</div>";
						$content_html.= "<div style='width:3%;max-height:".$height."px;".$style_col.$kanan."'>".$hdt->Qty."</div>";
						$content_html.= "<div style='width:".$width."%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($hdt->Qty*$hdt->Harga,2)."</div>";
						$content_html.= "<div style='width:".$width."%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($hdt->Subtotal,2)."</div>";
						$content_html.= "<div style='width:".$width."%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($hdt->PPN,2)."</div>";
						$content_html.= "<div style='width:".$width."%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($hdt->Grandtotal,2)."</div>";
						$content_html.= "<div style='width:8%;max-height:".$height."px;".$style_col.$kiri."'>".$hdt->No_FakturP."</div>";
						$content_html.= "<div style='width:5%;max-height:".$height."px;".$style_col.$kiri."'>".date("d-M-Y", strtotime($hdt->Tgl_FakturP))."</div>";
						$content_html.= "<div style='width:".$width."%;max-height:".$height."px;".$style_col.$kiri."'>".$hdt->BRP."</div>";
						$content_html.= "<div style='width:".$width."%;max-height:".$height."px;".$style_col.$kiri."'>".$hdt->kota."</div>";
						$content_html.= "	</div>";
						$content_html.= "	<div style='clear:both;'></div>";
 
						$tglsama = date("d-M-Y", strtotime($hdt->Tgl_NRPu));

						$Total_DPP += $hdt->Subtotal;
						$Total_NNP += $hdt->PPN;
						$Total_Total += $hdt->Grandtotal; 

						$SubTotal_DPP += $hdt->Subtotal;
						$SubTotal_NNP += $hdt->PPN;
						$SubTotal_Total += $hdt->Grandtotal;
					}  
					$GrandSubTotal_DPP += $SubTotal_DPP;
					$GrandSubTotal_NNP += $SubTotal_NNP;
					$GrandSubTotal_Total += $SubTotal_Total;
 
					//$content_html.= $group_footer;  
				}
						$content_html.= "	<div id='div_column_header' style='width:100%!important;padding-left:5px;'>"; 
						$content_html.= "<div style='width:48%;max-height:".$height."px;".$style_col.$kanan."'><b>TOTAL</b></div>"; 
						$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($Total_DPP,2)."</b></div>";
						$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($Total_NNP,2)."</b></div>";
						$content_html.= "<div style='width:34%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($Total_Total,2)."</b></div>"; 
						$content_html.= "	</div>";
						$content_html.= "	<div style='clear:both;'></div>"; 

						$content_html.= "	<div id='div_column_header' style='width:100%!important;padding-left:5px;'>";
						$content_html.= "<div style='width:48%;max-height:".$height."px;".$style_col.$kanan."'><b>SUB TOTAL</b></div>"; 
						$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($SubTotal_DPP,2)."</b></div>";
						$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($SubTotal_NNP,2)."</b></div>";
						$content_html.= "<div style='width:34%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($SubTotal_Total,2)."</b></div>";  
						$content_html.= "	</div>";
						$content_html.= "	<div style='clear:both;'></div>";

						$content_html.= "	<div id='div_column_header' style='width:100%!important;padding-left:5px;'>";
						$content_html.= "<div style='width:48%;max-height:".$height."px;".$style_col.$kanan."'><b>GRAND TOTAL</b></div>"; 
						$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($GrandSubTotal_DPP,2)."</b></div>";
						$content_html.= "<div style='width:6%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($GrandSubTotal_NNP,2)."</b></div>";
						$content_html.= "<div style='width:34%;max-height:".$height."px;".$style_col.$kiri."'><b>".number_format($GrandSubTotal_Total,2)."</b></div>";  
						$content_html.= "	</div>";
						$content_html.= "	<div style='clear:both;'></div>"; 
 
			$content_html.= "</div>";

			$content_html.= "<div style='clear:both;height:80px;'></div>"; 
			if ($this->confirm_flag==1) {
				
				$this->Pdf_Report($header_html, $content_html, "", $dp1, $dp2);
				
			} else if ($this->pdf_flag==1) {
				
				$this->Pdf_Report($header_html, $content_html);
				
			} else {
				$row_btn = "";
				
				$row_btn.= "<form action='ConfirmNRP'>";
				$row_btn.= "<div style='position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;padding:10px;'>";
				$row_btn.= '	<input type="hidden" class="form-control" id="dp1" name="dp1" value="'.$dp1.'">';
				$row_btn.= '	<input type="hidden" class="form-control" id="dp2" name="dp2" value="'.$dp2.'">';
				$row_btn.= '	<input type="hidden" class="form-control" id="p_kategori" name="p_kategori" value="'.$p_kategori.'">';
				$row_btn.= '	<input type="hidden" class="form-control" id="p_supplier" name="p_supplier" value="'.$p_supplier.'">';
				$row_btn.= '	<input type="submit" value="SIMPAN">';
				$row_btn.= '</div>';
				$row_btn.= '</form>';
				
				$view['title'] = "REKAP NOTA RETUR PEMBELIAN";
				$view['content_html'] = $row_btn.$header_html.$content_html;
				$this->SetTemplate('template/notemplate');
				$this->RenderView('ReportResultView',$view);
			}
		}

		public function Export_Excel_E_FAKTUR($page_title, $dp1, $dp2, $p_kategori)
		{
			die("Export_Excel_E_Faktur");
			$p_start_date = date("d-M-Y", strtotime($dp1)); 
			$p_end_date = date("d-M-Y", strtotime($dp2)); 
			$data = array();
			$api = 'APITES';
			
			$ReportEFaktur = json_decode(file_get_contents($this->API_URL."/LaporanNRP/ExportE_FAKTUR?api=".$api."&p_start_date=".urlencode($dp1)."&p_end_date=".urlencode($dp2)."&p_kategori=".$p_kategori));
			//echo count($ReportBRPNRP); 

			if(count($ReportEFaktur)==0){
				exit('Tidak ada data');
			}

			$style_col_ganjil = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#00ffff;";
			$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#ff00ff;"; 

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);

			$content_html = "<html>";
			$content_html = "<head>";
			$content_html = "<style>
			body{font-family:'Calibri',Arial;}
			table{padding:0;margin:0;border-collapse:collapse}
			td, th { border:1px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			</style>";
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "<div><h2>NRP E-FAKTUR</h2></div>"; 
			$content_html.= "<div><b>PERIODE: ".$p_start_date." S/D ".$p_end_date."</b></div>";  
			$content_html.= "</div>";	//close div_header
			$content_html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";

			$currcol = 1;
			$currrow = 4;

			if($this->excel_flag == 1){
				$sheet->setTitle('NRP E-FAKTUR');
				$sheet->setCellValue('A1', 'NRP E-FAKTUR');
				///$sheet->getStyle('A1')->getFont()->setSize(20); untuk ukuran font excel
				$sheet->setCellValue('A2', 'PERIODE : '.$p_start_date.' S/D '.$p_end_date); 
			}
	 			 
			$namatarget = "" ;
			$startid = 0;
			$content_html.= "<table style='font-size:10pt!important'>";
					$content_html.= "<tr style='background-color:#33ccff;'>"; 
					$content_html.= "<th style='width:30px'>RM</th>"; 
					$content_html.= "<th style='width:80px'>NPWP</th>";
					$content_html.= "<th style='width:300px'>NAMA</th>";
					$content_html.= "<th style='width:30px'>KD JENIS TRANSAKSI</th>";
					$content_html.= "<th style='width:30px'>FG PENGGANTI</th>";
					$content_html.= "<th style='width:80px'>NOMOR FAKTUR</th>";
					$content_html.= "<th style='width:80px'>TANGGAL FAKTUR</th>";
					$content_html.= "<th style='width:30px'>IS CREDITABLE</th>";
					$content_html.= "<th style='width:30px'>NOMOR DOKUMEN RETUR</th>";
					$content_html.= "<th style='width:80px'>TANGGAL ETUR</th>";
					$content_html.= "<th style='width:70px'>MASA PAJAK RETUR</th>";
					$content_html.= "<th style='width:70px'>TAHUN PAJAK RETUR</th>";
					$content_html.= "<th style='width:80px'>NILAI RETUR DPP</th>";
					$content_html.= "<th style='width:80px'>NILAI RETUR PPN</th>";
					$content_html.= "<th style='width:80px'>NILAI RETUR PPNBM</th>";
					$content_html.= "</tr>";  

					if($this->excel_flag == 1){  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'RM');
						$sheet->getColumnDimension('A')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'NPWP');
						$sheet->getColumnDimension('B')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'NAMA');
						$sheet->getColumnDimension('C')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'KD_JENIS_TRANSAKSI');
						$sheet->getColumnDimension('D')->setWidth(8); 
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'FG_PENGGANTI');
						$sheet->getColumnDimension('E')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'NOMOR_FAKTUR');
						$sheet->getColumnDimension('F')->setWidth(20);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'TANGGAL_FAKTUR');
						$sheet->getColumnDimension('G')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'IS_CREDITABLE');
						$sheet->getColumnDimension('H')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'NOMOR_DOKUMEN_RETUR');
						$sheet->getColumnDimension('I')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'TANGGAL_RETUR');
						$sheet->getColumnDimension('J')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'MASA_PAJAK_RETUR');
						$sheet->getColumnDimension('K')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'TAHUN_PAJAK_RETUR');
						$sheet->getColumnDimension('L')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'NILAI_RETUR_DPP');
						$sheet->getColumnDimension('M')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'NILAI_RETUR_PPN');
						$sheet->getColumnDimension('N')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'NILAI_RETUR_PPNBM');
						$sheet->getColumnDimension('O')->setWidth(8);  
					}
			foreach($ReportEFaktur as $dt){  
					$content_html.= "<tr>";
					$content_html.= "<td>RM</td>"; 
					$content_html.= "<td>".$dt->NPWP."</td>"; 
					$content_html.= "<td>".$dt->Nm_Supl."</td>"; 
					$content_html.= "<td>".substr($dt->No_Faktur_Pajak,0,2)."</td>"; 
					$content_html.= "<td>".substr($dt->No_Faktur_Pajak,2,1)."</td>"; 
					$content_html.= "<td>".substr($dt->No_Faktur_Pajak,3)."</td>"; 
					$content_html.= "<td>".date("d/m/Y", strtotime($dt->Tgl_FakturP))."</td>"; 
					$content_html.= "<td class='td-right'>1</td>"; 
					$content_html.= "<td>".$dt->No_NRPu."</td>"; 
					$content_html.= "<td>".date("d/m/Y", strtotime($dt->Tgl_NRPu))."</td>"; 
					$content_html.= "<td class='td-right'>".date("m",strtotime($dt->Tgl_NRPu))."</td>"; 
					$content_html.= "<td class='td-right'>".date("Y",strtotime($dt->Tgl_NRPu))."</td>"; 
					$content_html.= "<td class='td-right'>".number_format($dt->Subtotal,2)."</td>"; 
					$content_html.= "<td class='td-right'>".number_format($dt->PPN,2)."</td>";  
					$content_html.= "<td class='td-right'>0</td>";  
					//$content_html.= "<td class='td-right'>".number_format($ReportBRPNRP[$startid]->Total,2)."</td>"; 
					$content_html.= "</tr>";  

					if($this->excel_flag == 1){ 
						$currcol=1; 
						$currrow++;
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, 'RM');
						$sheet->getColumnDimension('A')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $dt->NPWP);
						$sheet->getColumnDimension('B')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $dt->Nm_Supl);
						$sheet->getColumnDimension('C')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, substr($dt->No_Faktur_Pajak,0));
						$sheet->getColumnDimension('D')->setWidth(8); 
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, substr($dt->No_Faktur_Pajak,2,1));
						$sheet->getColumnDimension('E')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, substr($dt->No_Faktur_Pajak,3));
						$sheet->getColumnDimension('F')->setWidth(20);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, date("d/m/Y", strtotime($dt->Tgl_FakturP)));
						$sheet->getColumnDimension('G')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, '1');
						$sheet->getColumnDimension('H')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, $dt->No_NRPu);
						$sheet->getColumnDimension('I')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, date("d/m/Y", strtotime($dt->Tgl_NRPu)));
						$sheet->getColumnDimension('J')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, date("m",strtotime($dt->Tgl_NRPu)));
						$sheet->getColumnDimension('K')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, date("Y",strtotime($dt->Tgl_NRPu)));
						$sheet->getColumnDimension('L')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, number_format($dt->Subtotal));
						$sheet->getColumnDimension('M')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, number_format($dt->PPN));	
						$sheet->getColumnDimension('N')->setWidth(8);  
						$sheet->setCellValueByColumnAndRow($currcol++, $currrow, '0');
						$sheet->getColumnDimension('O')->setWidth(8);  
					}
			}  
 			$content_html.= "</table>";
			if($this->excel_flag == 1){ 
				$filename='LaporanEFAKTUR['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 
				exit();
			}
 
			$content_html.= "</div>";
			$content_html.= "</body></html>";
			
			$data['title'] = $page_title;
			$data['content_html'] = $content_html;
			
			$this->load->view('LaporanResultView',$data); 
		}

		public function Export_Excel_NRP($page_title, $dp1, $dp2, $p_kategori, $p_supplier)
		{
			die("Export_Excel_NRP");
			$p_start_date = date("d-M-Y", strtotime($dp1)); 
			$p_end_date = date("d-M-Y", strtotime($dp2)); 
			$data = array();
			$api = 'APITES';
			
			$ReportNRP = json_decode(file_get_contents($this->API_URL."/LaporanNRP/ExportNRP?api=".$api."&p_start_date=".urlencode($dp1)."&p_end_date=".urlencode($dp2)."&p_kategori=".$p_kategori."&p_supplier=".$p_supplier));
			//echo count($ReportBRPNRP); 

			if(count($ReportNRP->detail)==0){
				exit('Tidak ada data');
			}

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);

			$content_html = "<html>";
			$content_html = "<head>";
			$content_html = "<style>
			body{font-family:'Calibri',Arial;}
			table{padding:0;margin:0;border-collapse:collapse}
			td, th { border:1px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			</style>";
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "<div><h2>REKAP NOTA RETUR PEMBELIAN</h2></div>"; 
			$content_html.= "<div><b>PERIODE : ".$p_start_date." S/D ".$p_end_date."</b></div>";  
			$content_html.= "</div>";	//close div_header
			$content_html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
 
 			$currcol = 1;
			$currrow = 4;

			if($this->excel_flag == 1){
				$sheet->setTitle('REKAP NOTA RETUR PEMBELIAN');
				$sheet->setCellValue('A1', 'REKAP NOTA RETUR PEMBELIAN');
				///$sheet->getStyle('A1')->getFont()->setSize(20); untuk ukuran font excel
				$sheet->setCellValue('A2', 'PERIODE : '.$p_start_date.' S/D '.$p_end_date); 
			}

			$Total_DPP = 0;
			$Total_NNP = 0;
			$Total_Total = 0; 
			$SubTotal_DPP = 0;
			$SubTotal_NNP = 0;
			$SubTotal_Total = 0; 
			$GrandSubTotal_DPP = 0;
			$GrandSubTotal_NNP = 0;
			$GrandSubTotal_Total = 0; 

			$tglsama = "";
			foreach($ReportNRP->detail as $hd){ 
				$tglsama = ""; 
				if ($GrandSubTotal_DPP>0)
						{ 
							$content_html.= "<tr>"; 
							$content_html.= "<td colspan='3'></td>";  
							$content_html.= "<td colspan='3'><b>TOTAL</b></td>";  
							$content_html.= "<td class='td-right'><b>".number_format($Total_DPP,2)."</b></td>";  
							$content_html.= "<td class='td-right'><b>".number_format($Total_NNP,2)."</b></td>";   
							$content_html.= "<td class='td-right'><b>".number_format($Total_Total,2)."</b></td>";  
							$content_html.= "<td colspan='4'></td>";  
							$content_html.= "</tr>"; 

							$content_html.= "<tr>"; 
							$content_html.= "<td colspan='3'></td>";  
							$content_html.= "<td colspan='3'><b>SUB TOTAL</b></td>";  
							$content_html.= "<td class='td-right'><b>".number_format($SubTotal_DPP,2)."</b></td>";  
							$content_html.= "<td class='td-right'><b>".number_format($SubTotal_NNP,2)."</b></td>";   
							$content_html.= "<td class='td-right'><b>".number_format($SubTotal_Total,2)."</b></td>";  
							$content_html.= "<td colspan='4'></td>";  
							$content_html.= "</tr>"; 	 
							$content_html.= "</table>";   

							if($this->excel_flag == 1){  
								$sheet->setCellValueByColumnAndRow(4, $currrow,  'TOTAL');
								$sheet->getColumnDimension('D')->setWidth(12); 
								$currcol += 1; 
								$sheet->setCellValueByColumnAndRow(7, $currrow,  number_format($Total_DPP));
								$sheet->getColumnDimension('G')->setWidth(12); 
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow(8, $currrow,  number_format($Total_NNP));
								$sheet->getColumnDimension('H')->setWidth(12); 
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow(9, $currrow,  number_format($Total_Total));
								$sheet->getColumnDimension('I')->setWidth(12); 
								$currcol = 1;
								$currrow ++;  

								$sheet->setCellValueByColumnAndRow(4, $currrow,  'SUB TOTAL');
								$sheet->getColumnDimension('D')->setWidth(12); 
								$currcol += 1; 
								$sheet->setCellValueByColumnAndRow(7, $currrow,  number_format($SubTotal_DPP));
								$sheet->getColumnDimension('G')->setWidth(12); 
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow(8, $currrow,  number_format($SubTotal_NNP));
								$sheet->getColumnDimension('H')->setWidth(12); 
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow(9, $currrow,  number_format($SubTotal_Total));
								$sheet->getColumnDimension('I')->setWidth(12); 
								$currcol = 1;
								$currrow +=2;   
							}
						} 
						
				$SubTotal_DPP = 0;
				$SubTotal_NNP = 0;
				$SubTotal_Total = 0; 
				//isi data table
				// buat header 
				$content_html.= "<div style='margin-bottom:20px;'>";
				$content_html.= "<table style='font-size:80%; border-collapse: collapse; border: none;'>";
				$content_html.= "<tr style='border: none;'>";
				$content_html.= "<td style='border: none;'><b>PEMBELI</b></td>";
				$content_html.= "<td style='border: none;'></td>";
				$content_html.= "<td style='border: none;'></td>";
				$content_html.= "<td style='border: none;'><b>PENJUAL</b></td>";
				$content_html.= "<td style='border: none;'></td>";
				$content_html.= "<td style='border: none;'></td>";
				$content_html.= "</tr>";
				$content_html.= "<tr style='border: none;'>";
				$content_html.= "<td style='border: none;'><b>Nama </b></td>";
				$content_html.= "<td style='border: none;'><b>       :</b></td>";
				$content_html.= "<td style='border: none;'><b>".$ReportNRP->header[0]->Nm_PKP."</b></td>";
				$content_html.= "<td style='border: none;'><b>Nama </b></td>";
				$content_html.= "<td style='border: none;'><b>       :</b></td>";
				$content_html.= "<td style='border: none;'><b>".$hd->Nm_Supl."</b></td>";
				$content_html.= "</tr>";
				$content_html.= "<tr style='border: none;'>";
				$content_html.= "<td style='border: none;'><b>Alamat </b></td>";
				$content_html.= "<td style='border: none;'><b>       :</b></td>";
				$content_html.= "<td style='border: none;'><b>".$ReportNRP->header[0]->Alm_PKP."</b></td>";
				$content_html.= "<td style='border: none;'><b>Alamat </b></td>";
				$content_html.= "<td style='border: none;'><b>       :</b></td>";
				$content_html.= "<td style='border: none;'><b>".$hd->Alm_Supl."</b></td>";
				$content_html.= "</tr>";
				$content_html.= "<tr style='border: none;'>";
				$content_html.= "<td style='border: none;'><b>NPWP </b></td>";
				$content_html.= "<td style='border: none;'><b>       :</b></td>";
				$content_html.= "<td style='border: none;'><b>".$ReportNRP->header[0]->NPWP_PKP."</b></td>";
				$content_html.= "<td style='border: none;'><b>NPWP </b></td>";
				$content_html.= "<td style='border: none;'><b>       :</b></td>";
				$content_html.= "<td style='border: none;'><b>".$hd->NPWP."</b></td>";
				$content_html.= "</tr>";
				$content_html.= "<table>";
				
				if($this->excel_flag == 1){

						$currcol = 1; 
						$sheet->setCellValueByColumnAndRow(1, $currrow, 'PEMBELI');
						$sheet->getColumnDimension('A')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(0).$currrow)->getFont()->setBold(true);				
						$sheet->setCellValueByColumnAndRow(7, $currrow, 'PENJUAL');
						$sheet->getColumnDimension('F')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(6).$currrow)->getFont()->setBold(true);
						$currrow++;
						$sheet->setCellValueByColumnAndRow(1, $currrow, 'Nama : ');
						$sheet->getColumnDimension('A')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(0).$currrow)->getFont()->setBold(true);						
						$sheet->setCellValueByColumnAndRow(2, $currrow, $ReportNRP->header[0]->Nm_PKP);
						$sheet->getColumnDimension('B')->setWidth(7); 	
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(1).$currrow)->getFont()->setBold(true);				
						$sheet->setCellValueByColumnAndRow(7, $currrow, 'Nama : ');
						$sheet->getColumnDimension('F')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(6).$currrow)->getFont()->setBold(true);				
						$sheet->setCellValueByColumnAndRow(8, $currrow, $hd->Nm_Supl);
						$sheet->getColumnDimension('G')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(7).$currrow)->getFont()->setBold(true);
						$currrow++;
						$sheet->setCellValueByColumnAndRow(1, $currrow, 'Alamat : ');
						$sheet->getColumnDimension('A')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(0).$currrow)->getFont()->setBold(true);						
						$sheet->setCellValueByColumnAndRow(2, $currrow, $ReportNRP->header[0]->Alm_PKP);
						$sheet->getColumnDimension('B')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(1).$currrow)->getFont()->setBold(true);					
						$sheet->setCellValueByColumnAndRow(7, $currrow, 'Alamat : ');
						$sheet->getColumnDimension('F')->setWidth(7);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(6).$currrow)->getFont()->setBold(true); 				
						$sheet->setCellValueByColumnAndRow(8, $currrow, $hd->Alm_Supl);
						$sheet->getColumnDimension('G')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(7).$currrow)->getFont()->setBold(true);
						$currrow++;
						$sheet->setCellValueByColumnAndRow(1, $currrow, 'NPWP : ');
						$sheet->getColumnDimension('A')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(0).$currrow)->getFont()->setBold(true);						
						$sheet->setCellValueByColumnAndRow(2, $currrow, $ReportNRP->header[0]->NPWP_PKP);
						$sheet->getColumnDimension('B')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(1).$currrow)->getFont()->setBold(true);					
						$sheet->setCellValueByColumnAndRow(7, $currrow, 'NPWP : ');
						$sheet->getColumnDimension('F')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(6).$currrow)->getFont()->setBold(true);				
						$sheet->setCellValueByColumnAndRow(8, $currrow, $hd->NPWP);
						$sheet->getColumnDimension('G')->setWidth(7); 
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(7).$currrow)->getFont()->setBold(true);
						$currrow += 2;


						$sheet->setCellValueByColumnAndRow(1, $currrow, 'NRP'); 
						$sheet->getColumnDimension('A')->setWidth(12);  
						$sheet->setCellValueByColumnAndRow(10, $currrow, 'Faktur Pajak'); 
						$sheet->getColumnDimension('J')->setWidth(12);  
						$currrow++;  

						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NOMOR');
						$sheet->getColumnDimension('A')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KODE BARANG');
						$sheet->getColumnDimension('B')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA BARANG');
						$sheet->getColumnDimension('C')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'HARGA SATUAN');
						$sheet->getColumnDimension('D')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY');
						$sheet->getColumnDimension('E')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SUBTOTAL');
						$sheet->getColumnDimension('F')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DPP');
						$sheet->getColumnDimension('G')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PPN');
						$sheet->getColumnDimension('H')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
						$sheet->getColumnDimension('I')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NOMOR');
						$sheet->getColumnDimension('J')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TANGGAL');
						$sheet->getColumnDimension('K')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'BRP');
						$sheet->getColumnDimension('L')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'CABANG');
						$sheet->getColumnDimension('M')->setWidth(12); 
						$currcol = 1;
						$currrow++;  
				} 
				//buat judul table
				$content_html.= "<table style='font-size:9pt!important'>"; 

						$content_html.= "<tr style='background-color:#33ccff;'>"; 
						$content_html.= "<th style='width:80px' colspan='9'>NRP</th>";  
						$content_html.= "<th style='width:100px' colspan='4'>Faktur Pajak</th>";   
						$content_html.= "</tr>"; 
						$content_html.= "<tr style='background-color:#33ccff;'>"; 
						$content_html.= "<th style='width:120px'>NOMOR</th>";
						$content_html.= "<th style='width:120px'>KODE BARANG</th>";
						$content_html.= "<th style='width:380px'>NAMA BARANG</th>";
						$content_html.= "<th style='width:100px'>HARGA SATUAN</th>";  
						$content_html.= "<th style='width:30px'>QTY</th>";  
						$content_html.= "<th style='width:80px'>SUBTOTAL</th>";  
						$content_html.= "<th style='width:80px'>DPP</th>";  
						$content_html.= "<th style='width:80px'>PPN</th>";  
						$content_html.= "<th style='width:80px'>TOTAL</th>";  
						$content_html.= "<th style='width:120px'>NOMOR</th>";  
						$content_html.= "<th style='width:70px'>TANGGAL</th>";  
						$content_html.= "<th style='width:80px'>BRP</th>";  
						$content_html.= "<th style='width:80px'>CABANG</th>";  
						$content_html.= "</tr>";   
				foreach($hd->data as $hdt){ 
 
						if ($tglsama <> date("d-M-Y", strtotime($hdt->Tgl_NRPu)))
						{  
							if ($SubTotal_DPP>0)
							{   	
								$content_html.= "<tr>"; 
								$content_html.= "<td colspan='3'></td>";  
								$content_html.= "<td colspan='3'><b>TOTAL</b></td>";  
								$content_html.= "<td class='td-right'><b>".number_format($Total_DPP,2)."</b></td>";  
								$content_html.= "<td class='td-right'><b>".number_format($Total_NNP,2)."</b></td>";   
								$content_html.= "<td class='td-right'><b>".number_format($Total_Total,2)."</b></td>";  
								$content_html.= "<td colspan='4'></td>";  
								$content_html.= "</tr>"; 

								if($this->excel_flag == 1){
									$sheet->setCellValueByColumnAndRow(4, $currrow,  'TOTAL');
									$sheet->getColumnDimension('D')->setWidth(12); 
									$currcol += 1; 
									$sheet->setCellValueByColumnAndRow(7, $currrow,  number_format($Total_DPP));
									$sheet->getColumnDimension('G')->setWidth(12); 
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow(8, $currrow,  number_format($Total_NNP));
									$sheet->getColumnDimension('H')->setWidth(12); 
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow(9, $currrow,  number_format($Total_Total));
									$sheet->getColumnDimension('I')->setWidth(12); 
									$currcol = 1;
									$currrow ++;  
								}
							}

							$content_html.= "<tr >";
							$content_html.= "<td colspan='13'><b>Tanggal : ".date("d-M-Y",strtotime($hdt->Tgl_NRPu))."</b></td>";
							$content_html.= "</tr>";
							$tglsama = date("d-M-Y", strtotime($hdt->Tgl_NRPu));


							if($this->excel_flag == 1){
								$sheet->setCellValueByColumnAndRow(1, $currrow, 'Tanggal : '.date("d-M-Y",strtotime($hdt->Tgl_NRPu)));
								$sheet->getColumnDimension('A')->setWidth(7); 
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(0).$currrow)->getFont()->setBold(true);	 
								$currrow++;
							}

							$Total_DPP =0;
							$Total_NNP =0;
							$Total_Total =0; 
						}
						$Total_DPP += $hdt->Subtotal;
						$Total_NNP += $hdt->PPN;
						$Total_Total += $hdt->Grandtotal; 

						$SubTotal_DPP += $hdt->Subtotal;
						$SubTotal_NNP += $hdt->PPN;
						$SubTotal_Total += $hdt->Grandtotal;   

						$content_html.= "<tr>"; 
						$content_html.= "<td class='td-left'>".$hdt->No_NRPu."</td>";
						$content_html.= "<td class='td-left'>".$hdt->Kd_Brg."</td>";
						$content_html.= "<td class='td-left'>".$hdt->Nm_Brg."</td>";
						$content_html.= "<td class='td-right'>".number_format($hdt->Harga,2)."</td>";  
						$content_html.= "<td class='td-right'>".$hdt->Qty."</td>";  
						$content_html.= "<td class='td-right'>".number_format($hdt->Qty*$hdt->Harga,2)."</td>";  
						$content_html.= "<td class='td-right'>".number_format($hdt->Subtotal,2)."</td>";  
						$content_html.= "<td class='td-right'>".number_format($hdt->PPN,2)."</td>";  
						$content_html.= "<td class='td-right'>".number_format($hdt->Grandtotal,2)."</td>";  
						$content_html.= "<td class='td-left'>".$hdt->No_FakturP."</td>";  
						$content_html.= "<td class='td-left'>".date("d-M-Y", strtotime($hdt->Tgl_FakturP))."</td>";  
						$content_html.= "<td class='td-left'>".$hdt->BRP."</td>";  
						$content_html.= "<td class='td-left'>".$hdt->kota."</td>";  
						$content_html.= "</tr>"; 

						if($this->excel_flag == 1){ 

							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $hdt->No_NRPu);
							$sheet->getColumnDimension('A')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $hdt->Kd_Brg);
							$sheet->getColumnDimension('B')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  $hdt->Nm_Brg);
							$sheet->getColumnDimension('C')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  number_format($hdt->Harga));
							$sheet->getColumnDimension('D')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  $hdt->Qty);
							$sheet->getColumnDimension('E')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  number_format($hdt->Qty*$hdt->Harga));
							$sheet->getColumnDimension('F')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  number_format($hdt->Subtotal));
							$sheet->getColumnDimension('G')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  number_format($hdt->PPN));
							$sheet->getColumnDimension('H')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  number_format($hdt->Grandtotal));
							$sheet->getColumnDimension('I')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  $hdt->No_FakturP);
							$sheet->getColumnDimension('J')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  date("d-M-Y", strtotime($hdt->Tgl_FakturP)));
							$sheet->getColumnDimension('K')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  $hdt->BRP);
							$sheet->getColumnDimension('L')->setWidth(12); 
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow,  $hdt->kota);
							$sheet->getColumnDimension('M')->setWidth(12); 
							$currcol = 1;  
							$currrow++; 
						}
				}  
				$GrandSubTotal_DPP += $SubTotal_DPP;
				$GrandSubTotal_NNP += $SubTotal_NNP;
				$GrandSubTotal_Total += $SubTotal_Total;
			} 

				$content_html.= "<tr>"; 
				$content_html.= "<td colspan='3'></td>";  
				$content_html.= "<td colspan='3'><b>TOTAL</b></td>";  
				$content_html.= "<td class='td-right'><b>".number_format($Total_DPP,2)."</b></td>";  
				$content_html.= "<td class='td-right'><b>".number_format($Total_NNP,2)."</b></td>";   
				$content_html.= "<td class='td-right'><b>".number_format($Total_Total,2)."</b></td>";  
				$content_html.= "<td colspan='4'></td>";  
				$content_html.= "</tr>"; 

				$content_html.= "<tr>"; 
				$content_html.= "<td colspan='3'></td>";  
				$content_html.= "<td colspan='3'><b>SUB TOTAL</b></td>"; 
				$content_html.= "<td class='td-right'><b>".number_format($SubTotal_DPP,2)."</b></td>";  
				$content_html.= "<td class='td-right'><b>".number_format($SubTotal_NNP,2)."</b></td>";   
				$content_html.= "<td class='td-right'><b>".number_format($SubTotal_Total,2)."</b></td>";  
				$content_html.= "<td colspan='4'></td>";  
				$content_html.= "</tr>"; 
 
				$content_html.= "<tr>"; 
				$content_html.= "<td colspan='3'></td>";  
				$content_html.= "<td colspan='3'><b>GRAND TOTAL</b></td>";  
				$content_html.= "<td class='td-right'><b>".number_format($GrandSubTotal_DPP,2)."</b></td>";  
				$content_html.= "<td class='td-right'><b>".number_format($GrandSubTotal_NNP,2)."</b></td>";   
				$content_html.= "<td class='td-right'><b>".number_format($GrandSubTotal_Total,2)."</b></td>";  
				$content_html.= "<td colspan='4'></td>";  
				$content_html.= "</tr>";

				if($this->excel_flag == 1){  
 
								$sheet->setCellValueByColumnAndRow(4, $currrow,  'TOTAL');
								$sheet->getColumnDimension('D')->setWidth(12); 
								$currcol += 1; 
								$sheet->setCellValueByColumnAndRow(7, $currrow,  number_format($Total_DPP));
								$sheet->getColumnDimension('G')->setWidth(12); 
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow(8, $currrow,  number_format($Total_NNP));
								$sheet->getColumnDimension('H')->setWidth(12); 
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow(9, $currrow,  number_format($Total_Total));
								$sheet->getColumnDimension('I')->setWidth(12); 
								$currcol = 1;
								$currrow ++;  

								$sheet->setCellValueByColumnAndRow(4, $currrow,  'SUB TOTAL');
								$sheet->getColumnDimension('D')->setWidth(12); 
								$currcol += 1; 
								$sheet->setCellValueByColumnAndRow(7, $currrow,  number_format($SubTotal_DPP));
								$sheet->getColumnDimension('G')->setWidth(12); 
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow(8, $currrow,  number_format($SubTotal_NNP));
								$sheet->getColumnDimension('H')->setWidth(12); 
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow(9, $currrow,  number_format($SubTotal_Total));
								$sheet->getColumnDimension('I')->setWidth(12);  
								$currcol = 1; 
								$currrow+=2; 

								$sheet->setCellValueByColumnAndRow(4, $currrow,  'GRAND TOTAL');
								$sheet->getColumnDimension('D')->setWidth(12); 
								$currcol += 1; 
								$sheet->setCellValueByColumnAndRow(7, $currrow,  number_format($GrandSubTotal_DPP));
								$sheet->getColumnDimension('G')->setWidth(12); 
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow(8, $currrow,  number_format($GrandSubTotal_NNP));
								$sheet->getColumnDimension('H')->setWidth(12); 
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow(9, $currrow,  number_format($GrandSubTotal_Total));
								$sheet->getColumnDimension('I')->setWidth(12);  
								$currcol = 1; 
							} 


				$content_html.= "</table>";  
				$content_html.= "</div>"; 

			if($this->excel_flag == 1){ 
				$filename='LaporanNRP['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 
				exit();
			}

			$content_html.= "</div>";
			$content_html.= "</body></html>";
			
			$data['title'] = $page_title;
			$data['content_html'] = $content_html;
			
			$this->load->view('LaporanResultView',$data);
		}

		public function Export_Excel_BRP_NRP($page_title, $dp1, $dp2, $p_kategori, $p_gudang_sumber, $p_gudang_target)
		{
			die("Export_Excel_BRP_NRP");
			$p_start_date = date("d-M-Y", strtotime($dp1)); 
			$p_end_date = date("d-M-Y", strtotime($dp2)); 
			$data = array();
			$api = 'APITES';	 
			$ReportBRPNRP = json_decode(file_get_contents($this->API_URL."/LaporanNRP/ExportBRPNRP?api=".$api."&p_start_date=".urlencode($dp1)."&p_end_date=".urlencode($dp2)."&p_kategori=".$p_kategori."&p_gudang_sumber=".$p_gudang_sumber."&p_gudang_target=".$p_gudang_target.""));
	 
			if(count($ReportBRPNRP)==0){
				exit('Tidak ada data');
			}

			$style_col_ganjil = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#00ffff;";
			$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#ff00ff;"; 

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);

			$content_html = "<html>";
			$content_html = "<head>";
			$content_html = "<style>
			body{font-family:'Calibri',Arial;}
			table{padding:0;margin:0;border-collapse:collapse}
			td, th { border:1px solid #555; padding:2px!important; }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			</style>";
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header' style='margin-bottom:20px;'>";
			$content_html.= "<div><h2>REKAP BUKTI RETUR PEMBELIAN</h2></div>"; 
			$content_html.= "<div><b>PERIODE: ".$p_start_date." S/D ".$p_end_date."</b></div>";  
			$content_html.= "</div>";	//close div_header
			$content_html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";

			$currcol = 1;
			$currrow = 4;

			if($this->excel_flag == 1){
				$sheet->setTitle('REKAP_BUKTI_RETUR_PEMBELIAN');
				$sheet->setCellValue('A1', 'REKAP BUKTI RETUR PEMBELIAN');
				///$sheet->getStyle('A1')->getFont()->setSize(20); untuk ukuran font excel
				$sheet->setCellValue('A2', 'PERIODE : '.$p_start_date.' S/D '.$p_end_date); 
			}
	 			
			

			$namatarget = "" ;
			$startid = 0; 
			foreach($ReportBRPNRP as $dt){ 
				$tgl_mutasi = date("d-M-Y", strtotime($ReportBRPNRP[$startid]->tgl_mutasi)); 
				$tgl_faktur = date("d-M-Y", strtotime($ReportBRPNRP[$startid]->Tgl_FakturP)); 
				if ($namatarget<>$ReportBRPNRP[$startid]->Nm_Gudang)
				{	 
					if ($startid>0)
					{ 
						$content_html.= "</table>";
					}
					$namatarget = $ReportBRPNRP[$startid]->Nm_Gudang;
					$content_html.= "<div><h2>".$namatarget."</h2></div>"; 

					$content_html.= "<table style='font-size:10pt!important'>";
					$content_html.= "<tr style='background-color:#33ccff;'>"; 
					$content_html.= "<th style='width:80px'>TGL BRP</th>";
					$content_html.= "<th style='width:130px'>NO BRP</th>";
					$content_html.= "<th style='width:100px'>BARANG BRP</th>";
					$content_html.= "<th style='width:60px'>QTY BRP</th>";
					$content_html.= "<th style='width:130px'>NO NRP</th>";
					$content_html.= "<th style='width:130px'>NO PEMBELIAN</th>";
					$content_html.= "<th style='width:100px'>TGL PEMBELIAN</th>";
					$content_html.= "<th style='width:60px'>QTY NRP</th>";
					$content_html.= "<th style='width:100px'>HARGA</th>";
					$content_html.= "<th style='width:100px'>TOTAL</th>";
					$content_html.= "</tr>"; 
 
					$content_html.= "<tr>";
					$content_html.= "<td>".$tgl_mutasi."</td>";
					$content_html.= "<td>".$ReportBRPNRP[$startid]->No_Mutasi."</td>";
					$content_html.= "<td>".$ReportBRPNRP[$startid]->Kd_Brg."</td>";
					$content_html.= "<td class='td-right'>".$ReportBRPNRP[$startid]->Qty."</td>"; 
					$content_html.= "<td>".$ReportBRPNRP[$startid]->No_NRPu."</td>"; 
					$content_html.= "<td>".$ReportBRPNRP[$startid]->No_FakturP."</td>"; 
					$content_html.= "<td>".$tgl_faktur."</td>"; 
					$content_html.= "<td class='td-right'>".$ReportBRPNRP[$startid]->Qty_NRP."</td>"; 
					$content_html.= "<td class='td-right'>".number_format($ReportBRPNRP[$startid]->Harga,2)."</td>"; 
					$content_html.= "<td class='td-right'>".number_format($ReportBRPNRP[$startid]->Total,2)."</td>"; 
					$content_html.= "</tr>";

					if($this->excel_flag == 1){
						if ($startid>0)
						{
							$currcol = 1;
							$currrow += 2; 
						}


						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'RETUR KE :');
						$sheet->getColumnDimension('A')->setWidth(7);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $namatarget);
						$sheet->getColumnDimension('B')->setWidth(15);
						$currrow += 2; 
						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL BRP');
						$sheet->getColumnDimension('A')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO BRP');
						$sheet->getColumnDimension('B')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'BARANG BRP');
						$sheet->getColumnDimension('C')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY BRP');
						$sheet->getColumnDimension('D')->setWidth(8); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO BRP');
						$sheet->getColumnDimension('E')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO BPEMBELIAN');
						$sheet->getColumnDimension('F')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL BPEMBELIAN');
						$sheet->getColumnDimension('G')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY NRP');
						$sheet->getColumnDimension('H')->setWidth(8); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'HARGA');
						$sheet->getColumnDimension('I')->setWidth(10); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
						$sheet->getColumnDimension('J')->setWidth(10); 
						$currcol += 1; 
						$currrow += 2;  
						$currcol = 1; 
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tgl_mutasi);
						$sheet->getColumnDimension('A')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $ReportBRPNRP[$startid]->No_Mutasi);
						$sheet->getColumnDimension('B')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $ReportBRPNRP[$startid]->Kd_Brg);
						$sheet->getColumnDimension('C')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $ReportBRPNRP[$startid]->Qty);
						$sheet->getColumnDimension('D')->setWidth(8); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $ReportBRPNRP[$startid]->No_NRPu);
						$sheet->getColumnDimension('E')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $ReportBRPNRP[$startid]->No_FakturP);
						$sheet->getColumnDimension('F')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tgl_faktur);
						$sheet->getColumnDimension('G')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $ReportBRPNRP[$startid]->Qty_NRP);
						$sheet->getColumnDimension('H')->setWidth(8); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($ReportBRPNRP[$startid]->Harga));
						$sheet->getColumnDimension('I')->setWidth(10); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($ReportBRPNRP[$startid]->Total));
						$sheet->getColumnDimension('J')->setWidth(10); 
						$currcol += 1;
 						 
					}
				}
				else
				{   
					$content_html.= "<tr>";
					$content_html.= "<td>".$tgl_mutasi."</td>";
					$content_html.= "<td>".$ReportBRPNRP[$startid]->No_Mutasi."</td>";
					$content_html.= "<td>".$ReportBRPNRP[$startid]->Kd_Brg."</td>";
					$content_html.= "<td class='td-right'>".$ReportBRPNRP[$startid]->Qty."</td>"; 
					$content_html.= "<td>".$ReportBRPNRP[$startid]->No_NRPu."</td>"; 
					$content_html.= "<td>".$ReportBRPNRP[$startid]->No_FakturP."</td>"; 
					$content_html.= "<td>".$tgl_faktur."</td>";
					$content_html.= "<td class='td-right'>".$ReportBRPNRP[$startid]->Qty_NRP."</td>"; 
					$content_html.= "<td class='td-right'>".number_format($ReportBRPNRP[$startid]->Harga,2)."</td>"; 
					$content_html.= "<td class='td-right'>".number_format($ReportBRPNRP[$startid]->Total,2)."</td>"; 
					$content_html.= "</tr>";


					if ($this->excel_flag == 1)
					{
						$currrow += 1; 
						$currcol = 1; 
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tgl_mutasi);
						$sheet->getColumnDimension('A')->setWidth(12); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $ReportBRPNRP[$startid]->No_Mutasi);
						$sheet->getColumnDimension('B')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $ReportBRPNRP[$startid]->Kd_Brg);
						$sheet->getColumnDimension('C')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $ReportBRPNRP[$startid]->Qty);
						$sheet->getColumnDimension('D')->setWidth(8); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $ReportBRPNRP[$startid]->No_NRPu);
						$sheet->getColumnDimension('E')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $ReportBRPNRP[$startid]->No_FakturP);
						$sheet->getColumnDimension('F')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tgl_faktur);
						$sheet->getColumnDimension('G')->setWidth(15); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $ReportBRPNRP[$startid]->Qty_NRP);
						$sheet->getColumnDimension('H')->setWidth(8); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($ReportBRPNRP[$startid]->Harga));
						$sheet->getColumnDimension('I')->setWidth(10); 
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, number_format($ReportBRPNRP[$startid]->Total));
						$sheet->getColumnDimension('J')->setWidth(10); 
						$currcol += 1;
					}
				}
				$startid ++;
			}  
			$content_html.= "</table>";
 
			if($this->excel_flag == 1){
				//PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
				// $sheet->mergeCells('A1:J1');
				// for ($i = 'A'; $i !=   $sheet->getHighestColumn(); $i++) {
				// $sheet->getColumnDimension($i)->setAutoSize(TRUE);
				// }
				
				$filename='LaporanBRPNRP['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 
				exit();
			}


			$content_html.= "</div>";
			$content_html.= "</body></html>";
			
			$data['title'] = $page_title;
			$data['content_html'] = $content_html;
			
			$this->load->view('LaporanResultView',$data);
		}

		public function Pdf_Report($header="", $content="", $footer="", $tgl1="", $tgl2="")
		{
			// echo("Create Pdf_Report <br>");
			$data = array();
			set_time_limit(60);
			
			$mpdf = new \Mpdf\Mpdf(array(
			'mode' => '',
			'format' => 'A4',
			/*'default_font_size' => 8,*/
			'default_font' => 'tahoma',
			'margin_left' => 10,
			'margin_right' => 10,
			'margin_top' => 30,
			'margin_bottom' => 10,
			'margin_header' => 0,
			'margin_footer' => 0,
			'orientation' => 'L'
			));
			
			$mpdf->SetHTMLHeader($header);				//Yang diulang di setiap awal halaman  (Header)
			$mpdf->WriteHTML(utf8_encode($content));
 
			if ($this->confirm_flag==1) {
				//die($_SESSION["conn"]->BranchId);
				$lok = $_SESSION["conn"]->BranchId;
				
				$main_dir = "C:/";
				$th = date("Y", strtotime($tgl1));
				$bl = date("m", strtotime($tgl1));
				$pdf_dir = $main_dir."/Report/ReportBRPNRP/".$th;
				$nm_file = "omzet_".$lok."_".$th."_".$bl.".pdf";
				//Jika folder save belum ada maka create dahulu
				if (!is_dir($pdf_dir)) {
					mkdir($pdf_dir, 0777, TRUE);	
				}
				$mpdf->Output($pdf_dir."/".$nm_file, \Mpdf\Output\Destination::FILE);
				// echo("Pdf_Report Done <br>");
				 
				$data["content_html"] = "<script>window.close();</script>"; 
				$this->load->view("CustomPageResult", $data);
				//echo("Email Sent <br>");
			} else {
				$mpdf->Output();
			}
		}

		public function ConfirmNRP()
		{
			$data = array();
			$this->confirm_flag = 1;		
			$dp1 = $this->input->get("dp1");
			$dp2 = $this->input->get("dp2");
			$p_kategori = $this->input->get("p_kategori");
			$p_supplier = $this->input->get("p_supplier");
			//die("here");
			$this->Export_Pdf_NRP("", $dp1, $dp2, $p_kategori, $p_supplier);
		} 
 

	}							