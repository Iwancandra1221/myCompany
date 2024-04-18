<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class LaporanPenjualanCampaign extends MY_Controller 
	{
		public $excel_flag = 0;
		public $nama_bulan = array('','JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER');
		public $current_header;
		
		public function __construct()
		{
			parent::__construct();
			$this->load->model('HelperModel');
			$this->load->helper('FormLibrary');
			$this->load->library('excel');
			$this->load->model('MasterReportWilayahModel', 'ReportModel');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		}
		
		public function index()
		{
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN PENJUALAN CAMPAIGN";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN PENJUALAN CAMPAIGN ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$data = array();
			$api = 'APITES';
			
			set_time_limit(60);
			
			$check_campaign = json_decode(file_get_contents($this->API_URL."/LaporanPenjualanCampaign/CheckCampaign?api=".$api));
			
			$data['title'] = 'Laporan Penjualan Campaign | '.WEBTITLE;
			$data['campaign'] = $check_campaign;

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('LaporanPenjualanCampaignFormView',$data);
		}
		
		
		public function GetDivisiByKdBrg($kd_brg)
		{
			foreach($this->current_header as $header => $header_details) {
				foreach($header_details as $header_detail) {
					if($kd_brg == $header_detail->kd_brg) return $header;
				}
			}
			return '';
		}
		
		public function setWarnaDivisi($divisi=''){
			if($divisi=='SHIMIZU'){
				return '00b0f0';
			}
			elseif($divisi=='RINNAI'){
				return 'FF0000';
			}
			elseif($divisi=='MIYAKOKR'){
				return 'ffbf00';
			}
			elseif($divisi=='MIYAKO'){
				return 'ffff00';
			}
			else return 'ffffff';
		}
		
		public function Proses()
		{
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN PENJUALAN CAMPAIGN";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES LAPORAN PENJUALAN CAMPAIGN ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$data = array();
			
			if(isset($_POST["btnPreview"])){
				$this->excel_flag = 0;
			}
			else{
				$this->excel_flag = 1;
			}
			
			if(isset($_POST['campaign']))
			{
				$this->load->library('form_validation');
				$this->form_validation->set_rules('campaign','Campaign','required');
				$campaign = explode('###',$_POST["campaign"]);
				$this->Preview($campaign[0],$campaign[1], $params);
			}
			else
			{
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				redirect("LaporanPenjualanCampaign");
			}
		}
		
		
		public function Preview($p_campaign, $p_nama_campaign, $params){
			
			$api = 'APITES';
			$URL = $this->API_URL."/LaporanPenjualanCampaign/ProsesLaporanCampaign?api=".$api."&p_campaign=".urlencode($p_campaign);
			
			// exit($URL);
			
			$json = json_decode(file_get_contents($URL));
			
			if(count($json->campaign)==0){
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit('Silahkan isi periode di master periode campaign untuk campaign '.$p_nama_campaign);
			}
			
			if(count($json->detail)==0){
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit('Tidak ada data');
			}
			
			$warna_table_header = 'f2f2f2';
			
			$warna_kompensasi = 'bdd7ee';
			$warna_table_green = 'aad08e';
			$warna_qty = 'bdd7ee';
			$warna_omset = 'ffffcc';
			$warna_omset_total = 'ffd966';
			$content_html = "<html>";
			$content_html = "<head>";
			
			$content_html = "<style>
			body{font-family:'Calibri',Arial;}
			table{padding:0;margin:0;border-collapse:collapse}
			td, th { border:1px solid #555; padding:2px!important; text-align:center }
			.td-center { text-align: center; }
			.td-right { text-align:right;}
			.td-bold { font-weight:bold}
			</style>";
			
			
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			$border_style = [
				'borders' => [
					'allBorders' => [
						'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
					],
				],
			];
			
			$font_style = array(
				'font'  => array(
					// 'bold'  => true,
					'color' => array('rgb' => '0000FF'),
				)
			);
			
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
			$alignment_middle = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
			
			$content_html.= "</head>";
			$content_html.= "<body>";
			$content_html.= "<div id='div_header'>";
			$content_html.= "	<div><h2>REKAP ".$p_nama_campaign."</h2></div>";
			$content_html.= "	<div style='clear:both'></div>";
			$content_html.= "</div>";	//close div_header
			
			if($this->excel_flag == 1){
				$sheet->setTitle(substr($p_campaign, 0, 31));
				$sheet->setCellValue('A1', 'REKAP '.$p_nama_campaign);
				$sheet->getStyle('A1')->getFont()->setSize(14);
				$sheet->getStyle('A1')->getFont()->setBold(true);
			}
			
			$currcol = 0;
			$currrow = 2;
			
			$content_html.= "<div class='div_body' style='min-width:10000px;overflow-x:scroll; '>";
			$content_html.= "<div style='clear:both'></div>";
			
			foreach($json->detail as $dt_cabang => $wilayahs) {
			
				foreach($wilayahs as $dt_wilayah => $dealers) {
				
					$content_html.= "<div style='margin-top:20px;'>CABANG: ".$dt_cabang."</div>";
					$content_html.= "<div>WILAYAH: ".$dt_wilayah."</div>";
					$content_html.= "<table style='font-size:10pt!important'>";
					$content_html.= "<tr>";
					
					
					if($this->excel_flag == 1){
						$currrow+=1;
						$sheet->setCellValueByColumnAndRow(1, $currrow, 'CABANG:');
						$sheet->setCellValueByColumnAndRow(2, $currrow, $dt_cabang);
						$currrow+=1;
						$sheet->setCellValueByColumnAndRow(1, $currrow, 'WILAYAH:');
						$sheet->setCellValueByColumnAndRow(2, $currrow, $dt_wilayah);
					}
					
					$content_html.= "	<th rowspan='3' style='min-width:20px'>No</th>";
					$content_html.= "	<th colspan='2'>Dealer</th>";
					$content_html.= "	<th rowspan='3' style='min-width:150px'>Periode<br>Pengembalian</th>";
					
					$currrow += 1;
					$start_row = $currrow;
					if($this->excel_flag == 1){
						$currcol = 0;
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No');
						$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+3);
						$sheet->getColumnDimension('A')->setWidth(10);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Dealer');
						$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+1 , $currrow);
						$currcol += 2;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Periode Pengembalian');
						$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+3);
						$sheet->getColumnDimension('D')->setWidth(25);
					}
					
					$current_header = array();
					$current_header_kode = array();
					$current_header_harga = array();
					foreach($json->header as $header_cabang => $header_divisis) {
						if($header_cabang == $dt_cabang){
							$this->current_header = $header_divisis;
							$current_header = $header_divisis;
							foreach($current_header as $header => $header_details) {
								$content_html.= "<th colspan='".count($header_details)."' style='background-color:#".$warna_table_header."'>".$header."</th>";
								if($this->excel_flag == 1){
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, $header);
									$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+count($header_details)-1, $currrow);
									$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
									$currcol += count($header_details)-1;
								}
								
								foreach($header_details as $header_detail) {
									$current_header_kode[$header_detail->kd_brg] = $header_detail->kd_brg;
									
									if(substr($dt_wilayah,0,2)=='MO'){
										$current_header_harga[$header_detail->kd_brg] = $header_detail->harga_jual_mo;
									}
									else{
										$current_header_harga[$header_detail->kd_brg] = $header_detail->harga_jual;
									}
								}
								
							}
						}
					}
					
					$content_html.= "<th colspan='".(count((array) $current_header)+1)."' style='min-width:100px;background:#".$warna_table_green."'>Omset Per Periode</th>";
					
					if($this->excel_flag == 1){
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Omset Per Periode');
						$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+(count((array)$current_header)), $currrow+1);
						$sheet->getStyle(
						PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow.':'.
						PHPExcel_Cell::stringFromColumnIndex(($currcol+(count((array)$current_header)-1))).($currrow+3))->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_green);
					}					
					
					
					$content_html.= "</tr>";
					$content_html.= "<tr>";
					$content_html.= "<th rowspan='2' style='min-width:60px'>Kode<br>Dealer</th>";
					$content_html.= "<th rowspan='2' style='width:200px'>Nama Dealer</th>";
					
					$currrow += 1;
					$currcol = 1;
					if($this->excel_flag == 1){
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Dealer');
						$sheet->getColumnDimension('B')->setWidth(15);
						$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+2);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama Dealer');
						$sheet->getColumnDimension('C')->setWidth(40);
						$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+2);
					}	
					
					
					
					$currcol += 1;
					foreach($current_header as $header => $header_details) {
						foreach($header_details as $header_detail) {
							$warna = $this->setWarnaDivisi($header_detail->divisi);
							$content_html.= "<th style='width:80px;background:#".$warna."'>".$header_detail->kd_brg."</th>";
							
							if($this->excel_flag == 1){
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $header_detail->kd_brg);
								$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+1);
								$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(12);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setWrapText(true);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna);
							}
						}
					}
					
					$max_col_name_qty = PHPExcel_Cell::stringFromColumnIndex($currcol-1); // nama kolom terakhir (paling kanan)
					
					$currrow += 1;
					foreach($current_header as $header => $header_details) {
						$content_html.= "<th rowspan='2' style='min-width:100px;background:#".$warna_table_green."'>Omset ".$header."</th>";
						if($this->excel_flag == 1){
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Omset '.$header);
							$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
							$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+1);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setWrapText(true);
						}
					}
					
					$content_html.= "<th rowspan='2' style='min-width:100px;background:#".$warna_table_green."'>Total Omset Gabungan</th>";
					if($this->excel_flag == 1){
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Omset Gabungan');
						$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(15);
						$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+1);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setWrapText(true);
						$max_col_name = PHPExcel_Cell::stringFromColumnIndex($currcol-1); // nama kolom terakhir (paling kanan)
					}
					
					$content_html.= "</tr>";
					
					$content_html.= "<tr>";
					$currrow += 1;
					$currcol = 4;
					foreach($current_header_harga as $header_harga) {
						$content_html.= "<th style='background-color:#".$warna_table_header."'>".number_format($header_harga,0)."</th>";
						
						if($this->excel_flag == 1){
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $header_harga);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_header);
						}
					}
					$content_html.= "</tr>";
					
					$grand_total_omset_divisi = array();
					foreach($current_header as $header => $header_details){
						$grand_total_omset_divisi[$header] = 0;
					}
					
					$grand_total_kompensasi = array();
					foreach($current_header as $header => $header_details){
						$grand_total_kompensasi[$header] = 0;
					}
					
					$grand_total_qty = array();
					foreach($current_header_kode as $header_kode) {
						$grand_total_qty[$header_kode] = 0;
					}
					
					$no=0;
					
					foreach($dealers as $dt_dealer => $periodes) {
						$dealer = explode('###',$dt_dealer);
						$no++;
						$content_html.= "
						<tr>
						<td rowspan='".(count($json->campaign)+1)."'>".$no."</td>
						<td rowspan='".(count($json->campaign)+1)."'>".$dealer[0]."</td>
						<td rowspan='".(count($json->campaign)+1)."'>".$dealer[1]."</td>";
						
						$currrow += 1;
						$currcol = 0;
						if($this->excel_flag == 1){
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);
							$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+(count($json->campaign)));
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dealer[0]);
							$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+(count($json->campaign)));
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dealer[1]);
							$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+(count($json->campaign)));
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setWrapText(true); 
						}
						
						$i = 0;
						
						
						$total_campaign = array();
						foreach($current_header_kode as $k) {
							$total_campaign[$k] = 0;
						}
						
						$total_omset_divisi = array();
						foreach($current_header as $header => $header_details) {
							$total_omset_divisi[$header] = 0;
						}
						
						foreach($json->campaign as $c) {
							
							$nama_periode = ($c->nm_periode == 'KOMPENSASI') ? ucwords(strtolower($c->nm_periode)) : $c->nm_periode;
							$color_kompensasi = ($c->nm_periode == 'KOMPENSASI') ? "color:blue;" : "";
							
							if($i > 0){
								$content_html.= "<tr>";
								if($this->excel_flag == 1){
									$currrow += 1;
								}
							}
							
							$content_html.= "<td style='".$color_kompensasi."'>".$nama_periode."</td>";
							
							if($this->excel_flag == 1){
								$currcol = 4;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $nama_periode);
								if($c->nm_periode == 'KOMPENSASI'){
									$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setItalic(true);
									$sheet->getStyle('D'.$currrow.':'.$max_col_name.$currrow)->applyFromArray($font_style);
								}
							}
							
							$omset_divisi = array();
							foreach($current_header as $header => $header_details) {
								$omset_divisi[$header] = 0;
							}
							
							$ada_periode = 0; 
							foreach($periodes as $periode => $details) {
								if($c->nm_periode==$periode){
									foreach($current_header as $header => $header_details) {
										foreach($header_details as $header_detail) {
											$ada = 0;
											foreach($details as $detail) {
												if($detail->kd_brg==$header_detail->kd_brg){
													$divisi = $this->GetDivisiByKdBrg($header_detail->kd_brg);
													$content_html.= "<td style='background:#".$warna_qty.";".$color_kompensasi."'>".$detail->qty."</td>";
													
													if($this->excel_flag == 1){
														$currcol += 1;
														$sheet->setCellValueByColumnAndRow($currcol, $currrow, $detail->qty);
													}
													
													$ada = 1;
													if($periode<>'KOMPENSASI'){
														$total_campaign[$header_detail->kd_brg] +=$detail->qty;
														// $omset_divisi[$divisi] += ($detail->qty) * ($current_header_harga[$header_detail->kd_brg]);
													}
													else{
														$grand_total_kompensasi[$divisi] += ($detail->qty) * ($current_header_harga[$header_detail->kd_brg]);
													}
													$grand_total_qty[$header_detail->kd_brg] += ($detail->qty);
													$omset_divisi[$divisi] += ($detail->qty) * ($current_header_harga[$header_detail->kd_brg]);
													
												}
											}
											if($ada == 0){
												$content_html.= "<td style='background:#".$warna_qty."'></td>";
												$currcol += 1;
											}
										}
									}
									$ada_periode = 1;
								}
							}
							if($ada_periode == 0){
								foreach($current_header_kode as $k){
									$content_html.= "<td style='background:#".$warna_qty."'></td>";
									$currcol += 1;
								}
							}
							
							//kolom omset per divisi
							$omset_gabungan = 0;
							foreach($current_header as $header => $header_details) {
								
								$content_html.= "<td style='".$color_kompensasi.";background:#".(($c->nm_periode == 'KOMPENSASI') ? $warna_qty : $warna_omset)."'>".number_format($omset_divisi[$header],0)."</td>";
								if($this->excel_flag == 1){
									$currcol += 1;
									$sheet->setCellValueByColumnAndRow($currcol, $currrow, $omset_divisi[$header]);
									$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
									$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB((($c->nm_periode == 'KOMPENSASI') ? $warna_qty : $warna_omset));
								}
								
								$omset_gabungan += ($omset_divisi[$header]);
								if($c->nm_periode <> 'KOMPENSASI'){
									$total_omset_divisi[$header] += ($omset_divisi[$header]);
								}
							}
							$content_html.= "<td style='".$color_kompensasi.";background:#".$warna_table_green."'>".number_format($omset_gabungan,0)."</td>";
							
							if($this->excel_flag == 1){
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $omset_gabungan);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_green);
							}
							$content_html.= "</tr>";
							$i++;
						}
						
						// baris sub total qty "Total Campaign" kolom divisi/kode
						$content_html.= "<tr>";
						$content_html.= "<td><em><b>Total Campaign</b></em></td>";
						
						$currrow += 1;
						if($this->excel_flag == 1){
							$currcol = 4;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total Campaign');
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setItalic(true);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFont()->setBold(true);
						}
						
						foreach($total_campaign as $t){
							$content_html.= "<td style='background:#".$warna_qty."'>".$t."</td>";
							if($this->excel_flag == 1){
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $t);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_qty);
							}
						}
						$total_omset_gabungan = 0;
						
						// baris sub total qty "Total Campaign" kolom omset
						foreach($current_header as $header => $header_details) {
							
							$content_html.= "<td style='background:#".$warna_omset_total."'>".number_format($total_omset_divisi[$header],0)."</td>";
							if($this->excel_flag == 1){
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_omset_divisi[$header]);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_omset_total);
							}
							
							$total_omset_gabungan += ($total_omset_divisi[$header]);
							$grand_total_omset_divisi[$header] += ($total_omset_divisi[$header]);
						}
						
						//omset gabungan
						$content_html.= "<td style='background:#".$warna_table_green."'>".number_format($total_omset_gabungan,0)."</td>";
						
						if($this->excel_flag == 1){
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_omset_gabungan);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_green);
						}
						$content_html.= "</tr>";
					}
					
					//baris "TOTAL"
					$content_html.= "<tr>";
					$content_html.= "<td colspan='3' rowspan='2'>TOTAL</td>";
					$content_html.= "<td><b>Actual Campaign</b></td>";
					
					$currrow += 1;
					$currcol = 0;
					if($this->excel_flag == 1){
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
						$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+2, $currrow+1);
						$currcol += 3;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Actual Campaign');
					}
					
					$currcol = 4;
					foreach($current_header_kode as $header_kode) {
						$content_html.= "<td rowspan='2'>".number_format($grand_total_qty[$header_kode])."</td>";
						if($this->excel_flag == 1){
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_qty[$header_kode]);
							$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol, $currrow+1);
						}
					}
					
					// baris sub total qty "Total Campaign" kolom omset
					$grand_total_omset_gabungan = 0;
					foreach($current_header as $header => $header_details) {
						$content_html.= "<td>".number_format($grand_total_omset_divisi[$header],0)."</td>";
						if($this->excel_flag == 1){
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_omset_divisi[$header]);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						}
						$grand_total_omset_gabungan += ($grand_total_omset_divisi[$header]);
					}
					$content_html.= "<td style='background:#".$warna_table_green."'>".number_format($grand_total_omset_gabungan)."</td>";
					if($this->excel_flag == 1){
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_omset_gabungan);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_green);
					}
					
					$content_html.= "</tr>";
					
					$content_html.= "<tr>";
					$content_html.= "<td>Kompensasi</td>";
					
					if($this->excel_flag == 1){
						$currrow += 1;
						$currcol = 4;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kompensasi');
					}
					
					$currcol += count($current_header_kode);
					
					$grand_total_kompensasi_gabungan = 0;
					foreach($current_header as $header => $header_details) {
						$content_html.= "<td style='background:#".$warna_qty.";color:blue'>".number_format($grand_total_kompensasi[$header])."</td>";
						if($this->excel_flag == 1){
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_kompensasi[$header]);
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->applyFromArray($font_style);
						}
						$grand_total_kompensasi_gabungan += ($grand_total_kompensasi[$header]);
					}
					$content_html.= "<td style='color:blue;background:#".$warna_table_green."'>".number_format($grand_total_kompensasi_gabungan)."</td>";
					
					if($this->excel_flag == 1){
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $grand_total_kompensasi_gabungan);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->applyFromArray($font_style);
						$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_table_green);
					
					}
					$content_html.= "</tr>";
					$content_html.= "</table>";
					
					if($this->excel_flag == 1){
						$sheet ->getStyle('A'.$start_row.':'.$max_col_name.$currrow)->applyFromArray($border_style);
						$sheet ->getStyle('A'.$start_row.':'.$max_col_name.$currrow)->getAlignment()->setVertical($alignment_middle);
						$sheet ->getStyle('A'.$start_row.':'.$max_col_name.$currrow)->getAlignment()->setHorizontal($alignment_center);
						$sheet->getStyle('E'.($start_row+4).':'.$max_col_name_qty.($currrow-2))->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_qty);
						$currrow += 2;
					}
				}
			}
			
			$content_html.= "</body>";
			$content_html.= "</html>";
			
			if($this->excel_flag == 1){
				$sheet->setSelectedCell('A1');
				$sheet->getSheetView()->setZoomScale(80);
				$filename=$p_nama_campaign.'['.date('Ymd').']'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');	// download file 

				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				exit();
			}
			
			$content_html.= "</div>";
			$content_html.= "</body></html>";
			
			$data['title'] = $p_nama_campaign;
			$data['content_html'] = $content_html;
			
			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			
			$this->load->view('LaporanResultView',$data);
		}
	}																																												