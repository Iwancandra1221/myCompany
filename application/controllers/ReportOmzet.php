<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

ini_set("memory_limit", "1G");
	
class ReportOmzet extends MY_Controller 
{
	public $BE_Java = 0;
	public $BE_Java_Multi_Filter = 0;
	public $pdf_flag = 0;
	public $confirm_flag=0;
	public $excel_flag=0;
	public $email_flag=0;
	public $wilayah_group = array();
	public $moduleID = "";
	public $laporan = array ('LaporanDealerPerDivisi'=>'LAPORAN DEALER PER DIVISI',
							'LaporanDivisiPerDealer'=>'LAPORAN DIVISI PER DEALER',
							'LaporanDealerPerAlmKirimPerDivisi'=>'LAPORAN DEALER PER ALM KIRIM PER DIVISI',
							'LaporanDealerPerKotaPerAlmKirimPerDivisi'=>'LAPORAN DEALER PER KOTA PER ALM KIRIM PER DIVISI',
							'LaporanKotaPerDivisi'=>'LAPORAN KOTA PER DIVISI',
							'LaporanDivisiPerKota'=>'LAPORAN DIVISI PER KOTA'); 
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('HelperModel');
		$this->load->helper('FormLibrary');
		$this->load->library('email');
		$this->load->library('excel');
		$this->load->helper('directory');
		$this->load->helper('file');
		$this->load->model("OmzetModel");
		$this->load->model('MasterReportWilayahModel', 'ReportModel');
		$this->load->model('GzipDecodeModel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}
	
	public function index()
	{
		$data = array();
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}		
		$data['title'] = 'OMZET | REPORT OMZET';
		$data["formURL"] = "";
		$data["btnPDF"] = 0;
		$data["btnExcel"] = 0;
		$data["err"] = "";

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN OMZET NETTO ";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN OMZET NETTO ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$this->RenderView('ReportOmzetNettoForm',$data);
	}
	private function _postRequest($url,$data,$isJson = false){
		$ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POST, 1);

	    if ($isJson) {
	        // Jika data adalah JSON, encode ke JSON dan atur header
	        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	    } else {
	        // Jika data adalah form data, atur payload dengan http_build_query
	        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	    }

	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	    $result = curl_exec($ch);

	    if (curl_errno($ch)) {
	        echo 'Curl error: ' . curl_error($ch);
	    }

	    curl_close($ch);

	    return $result;
	}
	public function LaporanPerbandinganPenjualan(){
		set_time_limit(36000); //tunggu 10 menit
		// ActivityLog
		if(!isset($_POST['submit'])){
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN OMZET CABANG";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN OMZET CABANG ";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

			$data = array();

			//reegankens
			$url = API_URL."/MsPartnerType/GetListPartnerType?api=APITES";
			$payload = [];
			$isJson = false;
			$getPartnerType = $this->_postRequest($url,$payload,$isJson);

			$getPartnerType = json_decode($getPartnerType,true);

			$data['partnerType'] = $getPartnerType;

			$this->moduleID = "REPORT PERBANDINGAN PENJUALAN";
			$data['title'] = $this->moduleID;
			$data["moduleID"] = $this->moduleID;
			$data["configs"] = $this->MsConfigModel->GetConfigs($this->moduleID);

			$data["formURL"] = "ReportOmzet/LaporanPerbandinganPenjualan";
			$data["btnPDF"] = 1;
			$data["btnExcel"] = 0;
			$data["opt"] = "Laporan Perbandingan Penjualan";
			$data["err"] = "";
			$this->RenderView('PerbandinganOmzetView',$data);
		}
		else{
			// echo '<pre>';
			// print_r($_POST);
			// echo '</pre>';
			
			$laporan = (int)$this->input->post('laporan');
			$jenisData = $this->input->post('jenis_data');
			$tipePpn = (int)$this->input->post('tipe_ppn');
			$partnerType = $this->input->post('partner_type');
			$yearStart = (int)$this->input->post('year_start');
			$yearEnd = (int)$this->input->post('year_end');


			if($laporan!=1){
				$month = 00;
			}else{
				$month = (int)$this->input->post('month');
				$date1 = $this->input->post('date1');
				$date2 = $this->input->post('date2');
			}

			switch($_POST['submit']){
				case 'EXCEL':
					$date = array();
					if($laporan==1){
						foreach($date1 as $key => $value){
							$date[] = array(
								$value,
								$date2[$key]
							);
						}
					}

					if($laporan==1 || $laporan==2){
						$url = API_URL."/ReportOmzet/LaporanPerbandinganPenjualan";
					}else{
						$url = API_URL."/ReportOmzet/LaporanPerbandingannasional";
					}
					$payload = array(
						'jenis_data' => $jenisData,
						'tipe_ppn' => $tipePpn,
						'partner_type' => $partnerType,
						'year_start' => $yearStart,
						'year_end' => $yearEnd,
						'month' => $month,
						'date' => $date,
					);
					// echo '<pre>';
					// print_r($payload);
					// echo '</pre>';
					// die();
					$result = $this->_postRequest($url,$payload,true);
					// echo $result;
					// die();
					$dataArray = json_decode($result,true);
					if($dataArray!='' && $dataArray!=null){
						// echo '<pre>';
						// print_r($dataArray);
						// echo '</pre>';
						if($dataArray!=''){
							$namaBulan = [
							    1 => "Januari",
							    2 => "Februari",
							    3 => "Maret",
							    4 => "April",
							    5 => "Mei",
							    6 => "Juni",
							    7 => "Juli",
							    8 => "Agustus",
							    9 => "September",
							    10 => "Oktober",
							    11 => "November",
							    12 => "Desember"
							];
							$judul = '';
							if($jenisData=='') $judul = '';
							else if($jenisData=='1') $judul = 'Omzet jual - retur';
							else if($jenisData=='2') $judul = 'Omzet Netto';
							else if($jenisData=='3') $judul = 'Total Jual';
							else if($jenisData=='4') $judul = 'Total Retur';

							$namaPpn = '';
							if($tipePpn==1) $namaPpn = 'Exclude';
							else if($tipePpn==2) $namaPpn = 'Include';

							$body = array(
								'judul' => $judul,
								'nama_ppn' => $namaPpn,
								'year_start' => $yearStart,
								'year_end' => $yearEnd,
								'month' => $month,
								'report' => $dataArray['data'], 
							);
							
							if($month===0 and $laporan===1){
								//report bulanan
								$body['filenameTambahan'] = 'PerbandinganOmzetTahunan';
								$this->load->view('template_xls/PerbandinganOmzetTahunanXls',$body);
							}else if($month!=0 and $laporan===1){
								$body['nama_bulan'] = $namaBulan[$month];
								$body['filename'] = 'PerbandinganOmzet';
								$this->load->view('template_xls/PerbandinganOmzetXls',$body);
							}else if($laporan===2){
								//report tahunan
								$body['filenameTambahan'] = 'PerbandinganOmzetTahunan';
								$this->load->view('template_xls/PerbandinganOmzetTahunanXls',$body);
							}else if($laporan===3){
								$data['report'] = json_encode($this->OmzetModel->LaporanOmzetNational($body));
								$data['filenameTambahan'] = 'PerbandinganOmzetBulananNasional';
								$this->load->view('template_xls/PerbandinganOmzetBulananNasionalXls',$data);
							}else if($laporan===4){
								$data['report'] = json_encode($this->OmzetModel->LaporanOmzetNational($body));
								$data['filenameTambahan'] = 'PerbandinganOmzetQuartalNasional';
								$this->load->view('template_xls/PerbandinganOmzetQuartalNasionalXls',$data);
							}
							
						}
						
					}
					else{
						echo 'data tidak ditemukan';
					}
				break;
				case 'PDF':

				break;
			}
		}
		
	}
	public function LoadWilayahGroup()
	{
		$this->wilayah_group = $this->ReportModel->getList('OMZET PER KOTA', 'KOTA');
	}
	
	public function GetWilayahGroup($wilayah, $kota)
	{
		// $ketemu = false;
		/* PRIORITAS PERTAMA CARI KOTA YG TERSIMPANNYA BUKAN ALL */
		foreach($this->wilayah_group as $wg){
			if((trim($wg->Wilayah)==$wilayah) && (trim($wg->Kota)==$kota)){
				// $ketemu = true;
				return $wg->WilayahGroup;
			}
		}
		return $kota;
	}


	public function OmzetNettoNational(){

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN OMZET NETTO NASIONAL";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN OMZET NETTO NASIONAL ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);


		$data = array();
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}		
		$data['title'] = 'OMZET | REPORT OMZET Netto National';
		$data["formURL"] = "ReportOmzet/RekapOmzetNettoNasional";
		$data["opt"] = "OMZET NETTO NATIONAL";
		$data["err"] = "";

		$this->RenderView('ReportOmzetNettoNationalForm',$data);
	}

	public function RekapOmzetNettoNasional(){
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN OMZET NETTO NASIONAL";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES LAPORAN OMZET NETTO NASIONAL ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$api = 'APITES';
		$jnsLaporan = $this->input->post('jns_laporan');
		
		$tahun = $this->input->post('tahun');
		$bulan = $this->input->post('bulan');
		//$tglAwal = $this->input->post('periode_start');
		//$tglAkhir = $this->input->post('periode_end');
		$katBrg = $this->input->post('produk_sparepart');
		$partnerType = $this->input->post('partner_type');

		$tglAwal = $tahun.'-'.$bulan.'-01';
		$tglAkhir = date("Y-m-t", strtotime($tglAwal));

		set_time_limit(60);
		if($katBrg=='ALL') $katBrg = '';
		if($partnerType=='ALL') $partnerType = '';
		

		//echo 'jns_laporan '.$jnsLaporan.'<br>';
		//echo 'periode_start '.$tglAwal.'<br>';
		//echo 'peridode_end '.$tglAkhir.'<br>';
		//echo 'produk_sparepart '.$katBrg.'<br>';
		//echo 'partner_type '.$partnerType.'<br>';

		$mainUrl=$this->API_URL;//webAPI
		$url = $mainUrl."/ReportOmzet/RekapOmzetNettoNasional?api=".urlencode($api)
			."&jns_laporan=".urlencode($jnsLaporan)
			."&tgl_awal=".urlencode($tglAwal)
			."&tgl_akhir=".urlencode($tglAkhir)
			."&kat_brg=".urlencode($katBrg)
			."&partner_type=".urlencode($partnerType);

		$HTTPRequest = HttpGetRequest($url, $mainUrl, "Ambil Omzet Bulanan");
		$getOmzet = json_decode($HTTPRequest, true);
		if($getOmzet!=null){
			switch($jnsLaporan){
				case 'A':
					$this->_RekapOmzetNettoNationalExcelA($getOmzet, $tglAwal, $tglAkhir, $partnerType, $params);
					break;
				case 'B':
					$this->_RekapOmzetNettoNationalExcelB($getOmzet, $tglAwal, $tglAkhir, $partnerType, $params);
					break;
				case 'C':
					$this->_RekapOmzetNettoNationalExcelC($getOmzet, $tglAwal, $tglAkhir, $partnerType, $params);
					break;
			}
		}
		else{

			$params['Remarks']="FAILED - Data Tidak Ditemukan";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			echo 'data tidak ditemukan';
		}
		
	}

	private function _RekapOmzetNettoNationalExcelC($getOmzet,$tglAwal,$tglAkhir,$partnerType,$params){
		//Omzet Netto Summary
		$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
		$styleArray = array(
			'borders' => array(
				'allBorders' => array(
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
				),
			),
		);

		$spreadsheet = new Spreadsheet();
		$spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
		$spreadsheet->getDefaultStyle()->getFont()->setSize(10);

		$sheet = $spreadsheet->getActiveSheet(0);
		$sheet->setTitle('Omzet Netto Summary');
		
		
		$currcol = 1;
		$currrow=1;
		$currrowHeader = 5;

		//header awal
		$jmlhField_mo = $getOmzet['JUAL'][0]['mo_jmlh_field'];
		$jmlhField_tr = $getOmzet['JUAL'][0]['tr_jmlh_field'];
		$jmlhField_pr = $getOmzet['JUAL'][0]['pr_jmlh_field'];
		$jmlhField_mo_cabang = $getOmzet['JUAL'][0]['mo_cabang_jmlh_field'];
		$nameAllFields = array_keys($getOmzet['JUAL'][0]);
		$brand_mo = array();
		$brand_tr = array();
		$brand_pr = array();
		$brand_mo_cabang = array();
		$totalField = ($jmlhField_mo+$jmlhField_tr+$jmlhField_pr+$jmlhField_mo_cabang);
		foreach($nameAllFields as $brandKey => $brandValue){
			//if($brandKey>3 && $a<=$jmlhField_mo){
				//$brand_mo[] = ltrim($brandValue,'mo_');
				//$a += 1;
			//}//hapus karaketer p_ dari nama column
			//if($a==$jmlhField_mo) break;
			$indexOf_Mo = stripos($brandValue,'mo_');
			$indexOf_Tr = stripos($brandValue,'tr_');
			$indexOf_Pr = stripos($brandValue,'pr_');
			$indexOf_MoCabang = stripos($brandValue,'mo_cabang_');
			if($brandKey>4 && $indexOf_Mo>-1){
				$brand_mo[] = str_replace('_',' ',ltrim($brandValue,'mo_'));
			}
			if($brandKey>4 && $indexOf_Tr>-1){
				$brand_tr[] = str_replace('_',' ',ltrim($brandValue,'tr_'));
			}
			if($brandKey>4 && $indexOf_Pr>-1){
				$brand_pr[] = str_replace('_',' ',ltrim($brandValue,'pr_'));
			}
			if($brandKey>4 && $indexOf_MoCabang>-1){
				$brand_mo_cabang[] = str_replace('_',' ',ltrim($brandValue,'mo_cabang_'));
			}

			
		}
		//log_message('error','jmlhField '.$jmlhField);
		//log_message('error','brand '.print_r($brand,true));
		//log_message('error','nameAllFields '.print_r($nameAllFields,true));
		
		
		//-----------------
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PT. BHAKTI IDOLA TAMA');
		$currrow+=1;
		
		$sheet->setCellValueByColumnAndRow($currcol, ($currrow), 'OMZET NETTO SUMMARY');
		$currrow+=1;

		$sheet->setCellValueByColumnAndRow($currcol, ($currrow), 'BULAN '.date('F',strtotime($tglAwal)).' '.date('Y',strtotime($tglAwal)));
		$currrow+=1;
		
		$sheet->getStyle('A'.($currcol).':A'.($currrow))->getFont()->setBold(true);


		//$jmlhCol = count($brand);

		$currrowHeader = $currrow;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PRODUCT');
		$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
		
		//if($jmlhField_mo>0){}
		$currcol +=1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'MODERN OUTLET');
		$sheet->mergeCellsByColumnAndRow($currcol, $currrow, ( $currcol + ($jmlhField_mo-1) ) , $currrow);
		
		$currcol +=$jmlhField_mo;//7
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TRADISIONAL');
		$sheet->mergeCellsByColumnAndRow($currcol, $currrow, ( $currcol + ($jmlhField_tr-1) ), $currrow); //6
		
		$currcol +=$jmlhField_tr;//7
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PROYEK');
		$sheet->mergeCellsByColumnAndRow($currcol, $currrow, ( $currcol + ($jmlhField_pr-1) ) , $currrow);

		$currcol +=$jmlhField_pr;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'MO CABANG');
		$sheet->mergeCellsByColumnAndRow($currcol, $currrow, ( $currcol + ($jmlhField_mo_cabang-1) ), $currrow);

		$currcol +=$jmlhField_mo_cabang;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
		$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);

		
		
		
		//group
		/*
		$brand = array(
			'MIYAKO','MICOOK','SHIMIZU','RINNAI',
			'CO& SANITARY WESTBEND','CO& SANITARY RINNAI','CO& SANITARY SHIMIZU'
		);
		*/
		
		//header 7 brand
		//$jmlhCol = count($brand);
		$currcol =2;//B
		$currrow +=1;
		for($a=0;$a<$jmlhField_mo;$a++){
			//penjualan
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $brand_mo[$a]);
			$currcol +=1;
		}
		for($a=0;$a<$jmlhField_tr;$a++){
			//retur
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $brand_tr[$a]);
			$currcol +=1;
		}
		for($a=0;$a<$jmlhField_pr;$a++){
			//disc/biaya TH
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $brand_pr[$a]);
			$currcol +=1;
		}
		for($a=0;$a<$jmlhField_mo_cabang;$a++){
			//omzet netto
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $brand_mo_cabang[$a]);
			$currcol +=1;
		}
		$columnLetter = PHPExcel_Cell::stringFromColumnIndex( ($currcol-1) );//AF
		
		$sheet->getStyle('A'.$currrowHeader.':'.$columnLetter.($currrow))->getAlignment()->setHorizontal($alignment_center);
		$sheet->getStyle('A'.$currrowHeader.':'.$columnLetter.($currrow))->getAlignment()->setVertical($alignment_center);
		//batas akhir table header

		foreach($getOmzet as $tipeOmzet => $getData){
			

			//table body

			$currrow +=1;
			$sheet->setCellValueByColumnAndRow(1, $currrow, str_replace('_',' ',$tipeOmzet) );//JUAL / RETUR / DISC_BIAYA_TH
			$sheet->getStyle('A'.($currrow))->getFont()->setBold(true);
			$currrow +=1;
			$sheet->setCellValueByColumnAndRow(1, $currrow, '' );
			$currrow +=1;
			$bodyRow = $currrow;
			$totalColLetter = 'A';//default
			foreach($getData as $dataKey => $dataValue){
				$sheet->setCellValueByColumnAndRow(1, $currrow, $dataValue['Divisi']);
				$total = 0;
				/*
				0 "Divisi": "CO_SANITARY_",
	            1 "mo_jmlh_field": 1,
	            2 "tr_jmlh_field": 26,
	            3 "pr_jmlh_field": 1,
	            4 "jmlh_field_mo_cabang": 14,
	            5 "mo_MODERN_OUTLET": ".0000",
				*/
				
			
				$b_col = 2;
				$startIndex = 5;
			
				for($b=0;$b<$totalField;$b++){//totalField = 40
					$sheet->setCellValueByColumnAndRow($b_col, $currrow, $dataValue[ $nameAllFields[ ($startIndex) ] ]);
					$total += $dataValue[ $nameAllFields[ ($startIndex) ] ];
					$b_col +=1;
					$startIndex +=1;
				}
				$sheet->setCellValueByColumnAndRow( $b_col  , $currrow, $total);
				$totalColLetter = PHPExcel_Cell::stringFromColumnIndex( $b_col );
				
				
				$currrow +=1;
			}
			//total per colom
			$totalRow = $currrow;
			$sheet->setCellValueByColumnAndRow(1, $currrow, ('TOTAL '.$tipeOmzet) );
			$sheet->getStyle('A'.$totalRow.':'.$totalColLetter.$totalRow)->getFont()->setBold(true);

			$colNumber = 1;//B
			for($a=0;$a<=$totalField;$a++){//tambah 1 kolom total
				$colLetter = PHPExcel_Cell::stringFromColumnIndex( $colNumber );
				$sumRange = $colLetter.$bodyRow.':'.$colLetter.( $currrow );//D7:248
				
				$sheet->setCellValue(($colLetter.''.$currrow) , "=SUM($sumRange)");//D249
				
				$colNumber +=1;
			}
			

			//buat border
			$sheet->getStyle('A'.$currrowHeader.':'.$columnLetter.$currrow)->applyFromArray($styleArray);

			//set autosize col
			$sheet->getColumnDimension('A')->setWidth(5.6); 
			for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
				$sheet->getColumnDimension($i)->setAutoSize(TRUE);
			}

			$currcol = 1;
			$currrow+=1;
			//$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'END'.$currrow);
			$currrowHeader = $currrow;
		}
		//export to excel
		$filename='OmzetNettoNasional['.$tglAwal.']['.$tglAkhir.']'; //save our workbook as this file name
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

	private function _RekapOmzetNettoNationalExcelB($getOmzet,$tglAwal,$tglAkhir,$partnerType,$params){
		//Omzet Netto Per Dealer
		$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$styleArray = array(
				'borders' => array(
					'allBorders' => array(
						'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
					),
				),
			);

			$spreadsheet = new Spreadsheet();
			$spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
			$spreadsheet->getDefaultStyle()->getFont()->setSize(10);

			$sheet = $spreadsheet->getActiveSheet(0);
			$sheet->setTitle('Omzet Netto Per Dealer');
			
			
			$currcol = 1;
			$currrow=1;
			$currrowHeader = 5;
			foreach($getOmzet as $wilayah => $getData){
				$jmlhField = $getData[0]['jmlh_field'];
				$nameAllFields = array_keys($getData[0]);
				$brand = array();
				$a=0;
				foreach($nameAllFields as $brandKey => $brandValue){
					if($brandKey>3 && $a<=$jmlhField){
						$brand[] = ltrim($brandValue,'p_');
						$a += 1;
					}//hapus karaketer p_ dari nama column
					if($a==$jmlhField) break;
					
				}
				//log_message('error','jmlhField '.$jmlhField);
				//log_message('error','brand '.print_r($brand,true));
				//log_message('error','nameAllFields '.print_r($nameAllFields,true));
				
				
				//-----------------
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PT. BHAKTI IDOLA TAMA');
				$currrow+=1;
				
				$sheet->setCellValueByColumnAndRow($currcol, ($currrow), 'OMZET NETTO DEALER '.$partnerType);
				$currrow+=1;

				$sheet->setCellValueByColumnAndRow($currcol, ($currrow), $partnerType.' '.$wilayah);
				$currrow+=1;

				$sheet->setCellValueByColumnAndRow($currcol, ($currrow), 'BULAN '.date('F',strtotime($tglAwal)).' '.date('Y',strtotime($tglAwal)));
				$currrow+=1;
				
				$sheet->getStyle('A'.($currcol).':A'.($currrow))->getFont()->setBold(true);


				$jmlhCol = count($brand);

				$currrowHeader = $currrow;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
				
				$currcol +=1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA DEALER');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
				
				$currcol +=1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PENJUALAN');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, ( $currcol + ($jmlhCol-1) ), $currrow); //6
				
				$currcol +=$jmlhCol;//7
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'RETURN');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, ( $currcol + ($jmlhCol-1) ) , $currrow);

				$currcol +=$jmlhCol;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DISC/BIAYA TH');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, ( $currcol + ($jmlhCol-1) ), $currrow);

				$currcol +=$jmlhCol;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'OMZET NETTO');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, ( $currcol + ($jmlhCol-1) ), $currrow);

				$currcol +=$jmlhCol;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);

				
				//header 7 brand
				//$jmlhCol = count($brand);
				$currcol =3;//C
				$currrow +=1;
				for($a=0;$a<$jmlhCol;$a++){
					//penjualan
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $brand[$a]);
					$currcol +=1;
				}
				for($a=0;$a<$jmlhCol;$a++){
					//retur
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $brand[$a]);
					$currcol +=1;
				}
				for($a=0;$a<$jmlhCol;$a++){
					//disc/biaya TH
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $brand[$a]);
					$currcol +=1;
				}
				for($a=0;$a<$jmlhCol;$a++){
					//omzet netto
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $brand[$a]);
					$currcol +=1;
				}
				$columnLetter = PHPExcel_Cell::stringFromColumnIndex( ($currcol-1) );//AF
				
				$sheet->getStyle('A'.$currrowHeader.':'.$columnLetter.($currrow))->getAlignment()->setHorizontal($alignment_center);
				$sheet->getStyle('A'.$currrowHeader.':'.$columnLetter.($currrow))->getAlignment()->setVertical($alignment_center);
				//batas akhir table header

				//table body
				$currrow +=1;
				$bodyRow = $currrow;
				foreach($getData as $dataKey => $dataValue){
					$sheet->setCellValueByColumnAndRow(1, $currrow, ($dataKey+1) );
					$sheet->setCellValueByColumnAndRow(2, $currrow, $dataValue['Nm_Plg']);
					$total = 0;
					/*
					[0] => Kd_Plg
				    [1] => Nm_Plg
				    [2] => jmlh_field
				    [3] => p_CO_SANITARY_
					*/
					for($b=3;$b<count($nameAllFields);$b++){//mulai dari colom ke 4 
						$sheet->setCellValueByColumnAndRow($b, $currrow, $dataValue[ $nameAllFields[$b] ]);
						$total += $dataValue[ $nameAllFields[$b] ];
					}
					$sheet->setCellValueByColumnAndRow(count($nameAllFields), $currrow, $total);
					$currrow +=1;
				}
				//total per colom
				$sheet->setCellValueByColumnAndRow(2, $currrow, 'Total');
				$colNumber = 2;//D
				for($a=0;$a< (($jmlhCol*4) + 1);$a++){//tambah 1 kolom total
					$colLetter = PHPExcel_Cell::stringFromColumnIndex( $colNumber );
					$sumRange = $colLetter.$bodyRow.':'.$colLetter.( $currrow );//D7:248
					
					$sheet->setCellValue(($colLetter.''.$currrow) , "=SUM($sumRange)");//D249
					$colNumber +=1;
				}
				

				//buat border
				$sheet->getStyle('A'.$currrowHeader.':'.$columnLetter.$currrow)->applyFromArray($styleArray);

				//set autosize col
				$sheet->getColumnDimension('A')->setWidth(5.6); 
				for ($i = 'B'; $i != $sheet->getHighestColumn(); $i++) {
					$sheet->getColumnDimension($i)->setAutoSize(TRUE);
				}

				$currcol = 1;
				$currrow+=5;
				//$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'END'.$currrow);
				$currrowHeader = $currrow;
			}
			//export to excel
			$filename='OmzetNettoNasional['.$tglAwal.']['.$tglAkhir.']'; //save our workbook as this file name
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

	private function _RekapOmzetNettoNationalExcelA($getOmzet,$tglAwal,$tglAkhir,$partnerType,$params){
		//Omzet_Netto_Per_Dealer_Per_Alamat_Kirim
		$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$styleArray = array(
				'borders' => array(
					'allBorders' => array(
						'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
					),
				),
			);

			$spreadsheet = new Spreadsheet();
			$spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
			$spreadsheet->getDefaultStyle()->getFont()->setSize(10);

			$sheet = $spreadsheet->getActiveSheet(0);
			$sheet->setTitle('Omzet Netto Per Dealer Per Alamat Kirim');
			
			
			$currcol = 1;
			$currrow=1;
			$currrowHeader = 5;
			foreach($getOmzet as $wilayah => $getData){
				$jmlhField = $getData[0]['jmlh_field'];
				$nameAllFields = array_keys($getData[0]);
				$brand = array();
				$a=0;
				foreach($nameAllFields as $brandKey => $brandValue){
					if($brandKey>3 && $a<=$jmlhField){
						$brand[] = ltrim($brandValue,'p_');
						$a += 1;
					}//hapus karaketer p_ dari nama column
					if($a==$jmlhField) break;
					
				}
				//log_message('error','jmlhField '.$jmlhField);
				//log_message('error','brand '.print_r($brand,true));
				//log_message('error','nameAllFields '.print_r($nameAllFields,true));
				
				
				//-----------------
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PT. BHAKTI IDOLA TAMA');
				$currrow+=1;
				
				$sheet->setCellValueByColumnAndRow($currcol, ($currrow), 'OMZET NETTO DEALER '.$partnerType);
				$currrow+=1;

				$sheet->setCellValueByColumnAndRow($currcol, ($currrow), $partnerType.' '.$wilayah);
				$currrow+=1;

				$sheet->setCellValueByColumnAndRow($currcol, ($currrow), 'BULAN '.date('F',strtotime($tglAwal)).' '.date('Y',strtotime($tglAwal)));
				$currrow+=1;
				
				$sheet->getStyle('A'.($currcol).':A'.($currrow))->getFont()->setBold(true);


				$jmlhCol = count($brand);

				$currrowHeader = $currrow;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
				
				$currcol +=1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA DEALER');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);

				$currcol +=1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'ALAMAT');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
				
				$currcol +=1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PENJUALAN');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, ( $currcol + ($jmlhCol-1) ), $currrow); //6
				
				$currcol +=$jmlhCol;//7
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'RETURN');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, ( $currcol + ($jmlhCol-1) ) , $currrow);

				$currcol +=$jmlhCol;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DISC/BIAYA TH');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, ( $currcol + ($jmlhCol-1) ), $currrow);

				$currcol +=$jmlhCol;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'OMZET NETTO');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, ( $currcol + ($jmlhCol-1) ), $currrow);

				$currcol +=$jmlhCol;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);

				
				
				
				//group
				/*
				$brand = array(
					'MIYAKO','MICOOK','SHIMIZU','RINNAI',
					'CO& SANITARY WESTBEND','CO& SANITARY RINNAI','CO& SANITARY SHIMIZU'
				);
				*/
				
				//header 7 brand
				//$jmlhCol = count($brand);
				$currcol =4;
				$currrow +=1;
				for($a=0;$a<$jmlhCol;$a++){
					//penjualan
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $brand[$a]);
					$currcol +=1;
				}
				for($a=0;$a<$jmlhCol;$a++){
					//retur
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $brand[$a]);
					$currcol +=1;
				}
				for($a=0;$a<$jmlhCol;$a++){
					//disc/biaya TH
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $brand[$a]);
					$currcol +=1;
				}
				for($a=0;$a<$jmlhCol;$a++){
					//omzet netto
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $brand[$a]);
					$currcol +=1;
				}
				$columnLetter = PHPExcel_Cell::stringFromColumnIndex( ($currcol-1) );//AF
				
				$sheet->getStyle('A'.$currrowHeader.':'.$columnLetter.($currrow))->getAlignment()->setHorizontal($alignment_center);
				$sheet->getStyle('A'.$currrowHeader.':'.$columnLetter.($currrow))->getAlignment()->setVertical($alignment_center);
				//batas akhir table header

				//table body
				$currrow +=1;
				$bodyRow = $currrow;
				foreach($getData as $dataKey => $dataValue){
					$sheet->setCellValueByColumnAndRow(1, $currrow, ($dataKey+1) );
					$sheet->setCellValueByColumnAndRow(2, $currrow, $dataValue['Nm_Plg']);
					$sheet->setCellValueByColumnAndRow(3, $currrow, $dataValue['Sub_NmWil']);
					$total = 0;
					/*
					[0] => Kd_Plg
				    [1] => Nm_Plg
				    [2] => Sub_NmWil
				    [3] => jmlh_field
				    [4] => p_CO_SANITARY_
				    */
					for($b=4;$b<count($nameAllFields);$b++){
						$sheet->setCellValueByColumnAndRow($b, $currrow, $dataValue[ $nameAllFields[$b] ]);
						$total += $dataValue[ $nameAllFields[$b] ];
					}
					$sheet->setCellValueByColumnAndRow(count($nameAllFields), $currrow, $total);
					$currrow +=1;
				}
				//total per colom
				$sheet->setCellValueByColumnAndRow(3, $currrow, 'Total');
				$colNumber = 3;//D
				for($a=0;$a< (($jmlhCol*4) + 1);$a++){//tambah 1 kolom total
					$colLetter = PHPExcel_Cell::stringFromColumnIndex( $colNumber );
					$sumRange = $colLetter.$bodyRow.':'.$colLetter.( $currrow );//D7:248
					
					$sheet->setCellValue(($colLetter.''.$currrow) , "=SUM($sumRange)");//D249
					$colNumber +=1;
				}
				

				//buat border
				$sheet->getStyle('A'.$currrowHeader.':'.$columnLetter.$currrow)->applyFromArray($styleArray);

				//set autosize col
				$sheet->getColumnDimension('A')->setWidth(5.6); 
				for ($i = 'B'; $i != $sheet->getHighestColumn(); $i++) {
					$sheet->getColumnDimension($i)->setAutoSize(TRUE);
				}

				$currcol = 1;
				$currrow+=5;
				//$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'END'.$currrow);
				$currrowHeader = $currrow;
			}
			//export to excel
			$filename='OmzetNettoNasional['.$tglAwal.']['.$tglAkhir.']'; //save our workbook as this file name
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



	public function OmzetCabang()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN OMZET CABANG";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN OMZET CABANG ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);


		$data = array();

		$this->moduleID = "REPORT OMZET CABANG";
		$data['title'] = $this->moduleID;
		$data["moduleID"] = $this->moduleID;
		$data["configs"] = $this->MsConfigModel->GetConfigs($this->moduleID);

		$data["formURL"] = "ReportOmzet/ProsesOmzetCabang";
		$data["btnPDF"] = 1;
		$data["btnExcel"] = 0;
		$data["opt"] = "OMZET CABANG";
		$data["err"] = "";

		$this->RenderView('ReportOmzetNettoForm',$data);
	}
	
	public function ConfirmOmzetCabang()
	{
		$this->moduleID = "REPORT OMZET CABANG";
		$data = array();
		$this->confirm_flag = 1;		
		$th = $this->input->get("th");
		$bl = $this->input->get("bl");
		//die("here");
		$this->Proses_OmzetCabang("", $th, $bl);
	}
	
	public function ProsesOmzetCabang()
	{
		
		$this->moduleID = "REPORT OMZET CABANG";
		$data = array();
		$page_title = 'Report Omzet';
		$this->confirm_flag = 0;
		if (isset($_POST["btnPdf"])) {
			$this->pdf_flag = 1;
			} else {
			$this->pdf_flag = 0;
		}
		
		$this->load->library('form_validation');
		$this->form_validation->set_rules('Tahun','Tahun','required');
		$this->form_validation->set_rules('Bulan','Bulan','required');
		
		if($this->form_validation->run())
		{
			$th = $_POST["Tahun"];
			$bl = $_POST["Bulan"];
			
			$this->Proses_OmzetCabang($page_title, $th, $bl);
		} else  {
			
			redirect("ReportOmzet");
		}
		
	}
	
	public function Proses_OmzetCabang($page_title, $th, $bl)
	{
		
		$params = array();
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['Module']="REPORT OMZET CABANG";
		$params['TrxID'] = date("YmdHis");
		$params['Description']=$_SESSION["logged_in"]["username"]." PROSES OMZET CABANG";
		$params['Remarks']="";
		$params['RemarksDate'] = NULL;
		$this->ActivityLogModel->insert_activity($params);

		$this->moduleID = "REPORT OMZET CABANG";
		$dp1=$th."-".$bl."-"."01";
		$dp =date_create($dp1);
		date_add($dp, date_interval_create_from_date_string("1 month"));
		date_add($dp, date_interval_create_from_date_string("-1 day"));
		$dp2=$dp->format("Y-m-d");
		
		$api = 'APITES';
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}
		$mainUrl = $_SESSION["conn"]->AlamatWebService;
		//MsDatabase - AlamatWebService 
		//$url = "http://10.1.0.92:90/";
		//die($mainUrl);
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;
		//$pwd = SQL_PWD;
		
		set_time_limit(60);
		$mainUrl.=API_BKT;
		$url = $mainUrl."/ReportOmzet/ReportOmzetBulanan?api=".urlencode($api)
			."&tgl1=".urlencode($dp1)."&tgl2=".urlencode($dp2)
			."&svr=".urlencode($svr)."&db=".urlencode($db);
		$HTTPRequest = HttpGetRequest($url, $mainUrl, "Ambil Omzet Bulanan");
		$GetData = json_decode($HTTPRequest, true);
		//die($url);
		// die(json_encode($GetData));

		if ($GetData["result"]=="sukses") {
			$params['Remarks']="SUCCESS";
			$this->ActivityLogModel->update_activity($params);

			$this->Preview_ReportOmzetCabang($th, $bl, $dp1, $dp2, $GetData["data"]);
		} else {
			$params['Remarks']="FAILED: ".$GetData["error"];
			$this->ActivityLogModel->update_activity($params);

			die($GetData["error"]);
		}
	}
	
	public function Preview_ReportOmzetCabang($th, $bl, $dp1, $dp2, $data) 
	{
		//die(json_encode($data));
		$this->moduleID = "REPORT OMZET CABANG";
		
		$style_col_ganjil ="float:left;line-height:15px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#ffffcc;font-size:10px;";
		$style_col_genap = "float:left;line-height:15px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#fff;font-size:10px;";
		$style_header= "float:left;min-height:20px;line-height:15px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#cccccc;font-size:10px;";
		$style_footer= "float:left;min-height:20px;line-height:20px;vertical-align:middle;border-right:1px solid #ccc;border:1px solid #ccc;background-color:#cccccc;font-size:10px;";
		
		$kanan = "text-align:right;padding-right:5px;";
		$kiri  = "text-align:left; padding-left: 5px;";
		$center= "text-align:center;";
		
		//$header_html = "<div style='clear:both;height:25px;'></div>";
		$header_html = "<div id='div_header' style='padding-left:10px;'>";
		$header_html.= "	<div><h2>".$this->moduleID."</h2></div>";
		$header_html.= "	<div><b>Periode : ".date("F Y", strtotime($dp1))."</b></div>";
		$header_html.= "</div>";	//close div_header
		
		$style_col = $style_col_genap;
		
		$group_header = "	<div id='div_column_header' style='width:95%!important;'>";
		$group_header.= "		<div style='width:10%;".$style_header.$kiri."'><b>Wilayah</b></div>";
		$group_header.= "		<div style='width:10%;".$style_header.$kiri."'><b>Divisi</b></div>";
		$group_header.= "		<div style='width:10%;".$style_header.$kiri."'><b>Merk</b></div>";
		$group_header.= "		<div style='width:10%;".$style_header.$kanan."'><b>Jual</b></div>";
		$group_header.= "		<div style='width:9%;".$style_header.$kanan."'><b>ReturBagus</b></div>";
		$group_header.= "		<div style='width:9%;".$style_header.$kanan."'><b>ReturCacat</b></div>";
		$group_header.= "		<div style='width:9%;".$style_header.$kanan."'><b>Disc</b></div>";
		$group_header.= "		<div style='width:10%;".$style_header.$kanan."'><b>OmzetNetto</b></div>";
		$group_header.= "		<div style='width:10%;".$style_header.$kiri."'><b>Status</b></div>";
		$group_header.= "	</div>";	//close div_column_header
		$group_header.= "	<div style='clear:both;'></div>";
		
		$KATEGORI_BRG = "";
		$LOK = "";
		$WILAYAH = "";
		$DIVISI = "";
		$MERK = "";
		$JUAL = 0;
		$RETURB = 0;
		$RETURC = 0;
		$DISC=0;
		$OMZET_NETTO=0;
		
		$user = $_SESSION["logged_in"]["useremail"];
		
		$TOTAL_JUAL = 0;
		$TOTAL_RETURB = 0;
		$TOTAL_RETURC = 0;
		$TOTAL_DISC = 0;
		$TOTAL_OMZET = 0;
		
		$content_html = "<div class='div_body' style='font-size:9pt!important;'>";
		$RESULTS = array();
		//die(json_encode($data));
		for($i=0;$i<count($data);$i++)
		{
			if ($this->confirm_flag==1) {
				// echo("Kategori Barang :".$data[$i]["KATEGORI_BRG"]."; ");
				// echo("Wilayah :".$data[$i]["WILAYAH"]."; ");
				// echo("Divisi :".$data[$i]["DIVISI"]."; ");
				// echo("Merk :".$data[$i]["MERK"].";<br>");
			}
			$n = $i+1;
			
			if ($i%2==1)
			$style_col = $style_col_genap;
			else
			$style_col = $style_col_ganjil;
			
			$data[$i]["STATUS"] = $this->OmzetModel->CheckStatusOmzet($th, $bl, $data[$i]);
			//die($data[$i]["STATUS"]);
			if ($this->confirm_flag==1) {
				if ($data[$i]["STATUS"]=="SAVED") {
					echo("DelDataOmzet ..<br>");
					$this->OmzetModel->DelDataOmzet($data[$i]["KATEGORI_BRG"],$data[$i]["WILAYAH"], $th, $bl, $data[$i]["KD_LOKASI"]);
					echo("DelDataOmzet Done<br>");
				}
				if ($data[$i]["STATUS"]!="SAVED AND LOCKED") {
					echo("Saving Data ...<br>");
					$this->OmzetModel->SaveDataOmzet($th, $bl, $data[$i], $user);
					echo("Saving Data Done<br>");
				}
			}
			
			//die(json_encode($data));
			//die($data[$i]["KATEGORI_BRG"]);
			//echo $KATEGORI_BRG." - ".trim($data[$i]["KATEGORI_BRG"]);
			//kategori_brg di order dari P terlebih dahulu, jika sudah selesai baru proses masuk ke yang else S (sparepart)
			if ($KATEGORI_BRG==trim($data[$i]["KATEGORI_BRG"])) {
				$height = 20;
				
				$content_html.= "	<div id='div_column_header' style='width:95%!important;'>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["WILAYAH"]."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["DIVISI"]."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["MERK"]."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_JUAL"])."</div>";
				$content_html.= "		<div style='width:9%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_RETURB"])."</div>";
				$content_html.= "		<div style='width:9%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_RETURC"])."</div>";
				$content_html.= "		<div style='width:9%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_DISC"])."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["OMZET_NETTO"])."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["STATUS"]."</div>";
				$content_html.= "	</div>";
				$content_html.= "	<div style='clear:both;'></div>";
				
				$TOTAL_JUAL  += $data[$i]["TOTAL_JUAL"];
				$TOTAL_RETURB+= $data[$i]["TOTAL_RETURB"];
				$TOTAL_RETURC+= $data[$i]["TOTAL_RETURC"];
				$TOTAL_DISC  += $data[$i]["TOTAL_DISC"];
				$TOTAL_OMZET += $data[$i]["OMZET_NETTO"];
				
				//die ($content_html); 
				} else {
				
				if ($KATEGORI_BRG!="") {
					
					$group_footer = "	<div id='div_column_header' style='width:95%!important;'>";
					$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>&nbsp;</div>";
					$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>TOTAL</div>";
					$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>&nbsp;</div>";
					$group_footer.= "		<div style='width:10%;".$style_footer.$kanan."'><b>".number_format($TOTAL_JUAL)."</b></div>";
					$group_footer.= "		<div style='width:9%;".$style_footer.$kanan."'><b>".number_format($TOTAL_RETURB)."</b></div>";
					$group_footer.= "		<div style='width:9%;".$style_footer.$kanan."'><b>".number_format($TOTAL_RETURC)."</b></div>";
					$group_footer.= "		<div style='width:9%;".$style_footer.$kanan."'><b>".number_format($TOTAL_DISC)."</b></div>";
					$group_footer.= "		<div style='width:10%;".$style_footer.$kanan."'><b>".number_format($TOTAL_OMZET)."</b></div>";
					$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'><b>&nbsp;</b></div>";
					$group_footer.= "	</div>";	//close div_column_header
					$group_footer.= "	<div style='clear:both;'></div>";
					
					$content_html.= $group_footer;					
				}
				
				
				$height = 40;
				$content_html.= "	<div id='div_column_header' style='width:95%!important;line-height:50px;vertical-align:middle;'>";
				$content_html.= "		<div style='width:15%;height:".$height."px;float:left;".$kiri."'>KATEGORI BARANG :</div>";
				$content_html.= "		<div style='width:75%;height:".$height."px;float:left;".$kiri."'><b>".(($data[$i]["KATEGORI_BRG"]=="P")?"PRODUK":"SPAREPART")."</b></div>";
				$content_html.= "	</div>";
				$content_html.= "	<div style='clear:both;height:5px;'></div>";
				$content_html.= $group_header;
				$content_html.= "	<div id='div_column_header' style='width:95%!important;'>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["WILAYAH"]."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["DIVISI"]."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["MERK"]."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_JUAL"])."</div>";
				$content_html.= "		<div style='width:9%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_RETURB"])."</div>";
				$content_html.= "		<div style='width:9%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_RETURC"])."</div>";
				$content_html.= "		<div style='width:9%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["TOTAL_DISC"])."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kanan."'>".number_format($data[$i]["OMZET_NETTO"])."</div>";
				$content_html.= "		<div style='width:10%;max-height:".$height."px;".$style_col.$kiri."'>".$data[$i]["STATUS"]."</div>";
				$content_html.= "	</div>";
				$content_html.= "	<div style='clear:both;'></div>";
				
				
				
				$KATEGORI_BRG = $data[$i]["KATEGORI_BRG"];
				$LOK = $data[$i]["KD_LOKASI"];
				
				$TOTAL_JUAL  = $data[$i]["TOTAL_JUAL"];
				$TOTAL_RETURB= $data[$i]["TOTAL_RETURB"];
				$TOTAL_RETURC= $data[$i]["TOTAL_RETURC"];
				$TOTAL_DISC  = $data[$i]["TOTAL_DISC"];
				$TOTAL_OMZET = $data[$i]["OMZET_NETTO"];
				
				
			}
			//echo '<br>';
		}
		
		
		$group_footer = "	<div id='div_column_header' style='width:95%!important;'>";
		$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>&nbsp;</div>";
		$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>TOTAL</div>";
		$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>&nbsp;</div>";
		$group_footer.= "		<div style='width:10%;".$style_footer.$kanan."'><b>".number_format($TOTAL_JUAL)."</b></div>";
		$group_footer.= "		<div style='width:9%;".$style_footer.$kanan."'><b>".number_format($TOTAL_RETURB)."</b></div>";
		$group_footer.= "		<div style='width:9%;".$style_footer.$kanan."'><b>".number_format($TOTAL_RETURC)."</b></div>";
		$group_footer.= "		<div style='width:9%;".$style_footer.$kanan."'><b>".number_format($TOTAL_DISC)."</b></div>";
		$group_footer.= "		<div style='width:10%;".$style_footer.$kanan."'><b>".number_format($TOTAL_OMZET)."</b></div>";
		$group_footer.= "		<div style='width:10%;".$style_footer.$kiri."'>&nbsp;</div>";
		$group_footer.= "	</div>";	//close div_column_header
		
		$content_html.= $group_footer;
		$content_html.= "</div>";
		$content_html.= "<div style='clear:both;height:80px;'></div>";
		
		
		
		if ($this->confirm_flag==1) {
			
			$this->Pdf_ReportOmzetCabang($header_html, $content_html, "", $dp1, $dp2);
			
		} else if ($this->pdf_flag==1) {
			
			$this->Pdf_ReportOmzetCabang($header_html, $content_html);
			
		} else {
			$row_btn = "";
			
			$row_btn.= "<form action='ConfirmOmzetCabang'>";
			$row_btn.= "<div style='position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;padding:10px;'>";
			$row_btn.= '	<input type="hidden" class="form-control" id="th" name="th" value="'.$th.'">';
			$row_btn.= '	<input type="hidden" class="form-control" id="bl" name="bl" value="'.$bl.'">';
			$row_btn.= '	<input type="submit" value="SIMPAN">';
			$row_btn.= '</div>';
			$row_btn.= '</form>';
			
			$view['title'] = "Report Omzet Bulanan";
			$view['content_html'] = $row_btn.$header_html.$content_html;
			$this->SetTemplate('template/notemplate');
			$this->RenderView('ReportResultView',$view);
		}
	}
	
	public function Pdf_ReportOmzetCabang($header="", $content="", $footer="", $tgl1="", $tgl2="")
	{
		$this->moduleID = "REPORT OMZET CABANG";
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

		$emailTO = array();
		$emailCC = array();

		$this->load->model("MsConfigModel");
		$getEmails = $this->MsConfigModel->GetConfigValue($this->moduleID, 'EMAIL', 'TO');
		foreach ($getEmails as $e) {
			if ($e->ConfigValue!="") array_push($emailTO, $e->ConfigValue);
		}

		$getEmails = $this->MsConfigModel->GetConfigValue($this->moduleID, 'EMAIL', 'CC');
		foreach ($getEmails as $e) {
			if ($e->ConfigValue!="") array_push($emailCC, $e->ConfigValue);
		}

		if ($this->confirm_flag==1) {
			//die($_SESSION["conn"]->BranchId);
			$lok = $_SESSION["conn"]->BranchId;
			
			$main_dir = "C:/";
			$th = date("Y", strtotime($tgl1));
			$bl = date("m", strtotime($tgl1));
			$pdf_dir = $main_dir."/Report/OmzetCabang/".$th;
			$nm_file = "omzet_".$lok."_".$th."_".$bl.".pdf";
			//Jika folder save belum ada maka create dahulu
			if (!is_dir($pdf_dir)) {
				mkdir($pdf_dir, 0777, TRUE);	
			}
			$mpdf->Output($pdf_dir."/".$nm_file, \Mpdf\Output\Destination::FILE);
			// echo("Pdf_Report Done <br>");
			
			if ($emailTO!="" || $emailCC!="") {
				$this->email->clear(true);
				$this->email->from("bhaktiautoemail.noreply@bhakti.co.id", "BHAKTI.CO.ID AUTO-EMAIL");
				$this->email->to($emailTO);
				$this->email->cc($emailCC);
				$this->email->attach($pdf_dir."/".$nm_file);
				$email_content = $_SESSION["logged_in"]["username"]." mengirimkan Data Omzet Cabang<br>";
				$email_content.= "Periode : ".date("d-M-Y",strtotime($tgl1))." s/d ".date("d-M-Y", strtotime($tgl2));
				$this->email->subject("Data Omzet Cabang [".$lok."][".$th."][".$bl."]");
				$this->email->message($email_content);

				if ($this->email->send(false)) {
					$data["content_html"] = '<script language="javascript">';
					$data["content_html"].= 'alert("Data Confirmed and Email Sent")';
					$data["content_html"].= '</script>';
					$data["content_html"].= "<script>window.close();</script>";
				} else {
					$err = $this->email->print_debugger();
					//die($err);
					$data["content_html"] = '<script language="javascript">';
					$data["content_html"].= 'alert("Email Not Sent")';
					$data["content_html"].= '</script>';
					$data["content_html"].= "<script>window.close();</script>";
				}
			} else {
				$data["content_html"] = '<script language="javascript">';
				$data["content_html"].= 'alert("Data Confirmed\nEmail Tidak Dikirim Karena Penerima Email Tidak Diset")';
				$data["content_html"].= '</script>';
				$data["content_html"].= "<script>window.close();</script>";
			}
			$this->load->view("CustomPageResult", $data);
			//echo("Email Sent <br>");
		} else {
			$mpdf->Output();
		}
	}
	


	public function OmzetNasional()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN OMZET NASIONAL";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN OMZET NASIONAL ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$data = array();

		$this->moduleID = "REPORT OMZET CABANG";
		$data['title'] = $this->moduleID;
		$data["moduleID"] = $this->moduleID;
		$data["configs"] = $this->MsConfigModel->GetConfigs($this->moduleID);
		
		$data["formURL"] = "ReportOmzet/RekapOmzetNasional";
		$data["btnPDF"] = 0;
		$data["btnExcel"] = 0;
		$data["opt"] = "OMZET NASIONAL";
		$data["err"] = "";

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView('ReportOmzetNettoForm',$data);
	}
	
	public function RekapOmzetNasional()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN OMZET NASIONAL";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." LAPORAN OMZET NASIONAL Rekap";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$data = array();
		$this->moduleID = "REPORT OMZET NASIONAL";
		$data['title'] = $this->moduleID;		
		$data["moduleID"] = $this->moduleID;			
		$data["info"] = $this->MsConfigModel->ConcatStringValue($this->moduleID, 'INFO', 'ALL');

		// $page_title = 'Report Omzet';
		$this->confirm_flag = 0;
		
		if (isset($_POST["btnExportExcel"]) || isset($_POST["btnEmailExcel"])) {
			$this->excel_flag = 1;
			} else {
			$this->excel_flag = 0;
		}
		
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);
		
		if (isset($_POST["btnEmailExcel"])) {
			$this->email_flag = 1;
			} else {
			$this->email_flag = 0;
		}
		
		$th = $_POST["Tahun"];
		$bl = $_POST["Bulan"];
		
		$wilayahP = $this->OmzetModel->OmzetBulanan_GetListWilayah("P", $th, $bl);
		$wilayahSP = $this->OmzetModel->OmzetBulanan_GetListWilayah("S", $th, $bl);
		$omzetP = $this->OmzetModel->OmzetBulanan_Gets("P", $th, $bl);
		//die(json_encode($omzetP));
		$omzetSP = $this->OmzetModel->OmzetBulanan_Gets("S", $th, $bl);
		$omzetDivMerkP = $this->OmzetModel->OmzetBulanan_OmzetDivisiMerk("P",$th, $bl);
		$omzetDivMerkSP = $this->OmzetModel->OmzetBulanan_OmzetDivisiMerk("S",$th, $bl);
		$omzetWilayahP = $this->OmzetModel->OmzetBulanan_OmzetWilayah("P",$th, $bl);
		$omzetWilayahSP = $this->OmzetModel->OmzetBulanan_OmzetWilayah("S",$th, $bl);
		
		$periode="";
		if ($bl==0) {
			$periode = $th; 
		} else {
			$tgl = $th."-".$bl."-"."01";
			$periode=date("F Y",strtotime($tgl));
		}

		$header = "<b>PT.BHAKTI IDOLA TAMA</b><br>";
		$header.= "<b>REPORT OMZET NASIONAL</b><br>";
		$header.= "<b>Periode : ".$periode."</b>";
		
		if($this->excel_flag == 1){
			//$this->excel->setActiveSheetIndex(0);
			$sheet->setTitle('ReportOmzetNasional');
			$sheet->setCellValue('A1', 'REPORT OMZET NASIONAL');
			$sheet->getStyle('A1')->getFont()->setSize(20);
			$sheet->setCellValue('A2', 'Periode : '.$periode);
			$sheet->getStyle("A1:A2")->getFont()->setBold(true);
		}
		$currcol = 1;
		$currrow = 5;
		
		
		$body = "<style>";
		$body.= " .td-center { text-align:center;width:500px!important;font-size:9pt;padding:2px 10px 2px 10px; } ";
		$body.= " .td-right { text-align:right;font-size:9pt;width:100px!important;padding:2px 10px 2px 10px; } ";
		$body.= " .td-left { text-align:left;font-size:9pt;width:150px!important;padding:2px 10px 2px 10px; } ";
		$body.= " .cellHd { background-color:black;color:white;font-weight:bold; } ";
		$body.= " .cellFt { background-color:#c2e0ab;color:#000;font-weight:bold;border:1px solid #ccc; } ";
		$body.= "</style>";
		
		$KATEGORI_BRG = "";
		$WILAYAH = "";
		$WilayahP = array();
		$WilayahSP= array();
		$wp = 0;
		$ws = 0;
		$TotalOmzetP = array();
		$TotalOmzetSP= array();
		
		
		if($this->excel_flag == 1){
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Omzet Produk (Dalam Rp.)');
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			
			$currrow+= 2;
			$currcol = 1;
		}
		
		//PRODUK - START
		$tableP = array("header"=>"","body"=>"","footer"=>"");
		$tableP["header"].="  <tr>";
		$tableP["header"].="    <td class='td-left cellHd' rowspan='2'>Divisi</td>";
		$tableP["header"].="    <td class='td-left cellHd' rowspan='2'>Merk</td>";
		foreach($wilayahP as $w)
		{
			$wp+=1;
			$tableP["header"].="    <td colspan='5' class='td-center cellHd'>".$w->Wilayah."</td>";
			array_push($WilayahP, $w->Wilayah);
		}
		$tableP["header"].="    <td rowspan='2' class='td-right cellHd'><b>Total</b></td>";
		$tableP["header"].="  </tr>";
		$tableP["header"].="  <tr>";
		for ($i=0;$i<$wp;$i++) {
			$tableP["header"].="    <td class='td-right cellHd'>Sale</td>";
			$tableP["header"].="    <td class='td-right cellHd'>Retur Bagus</td>";
			$tableP["header"].="    <td class='td-right cellHd'>Retur Cacat</td>";
			$tableP["header"].="    <td class='td-right cellHd'>Disc</td>";
			$tableP["header"].="    <td class='td-right cellHd'>Total</td>";
		}
		$tableP["header"].="  </tr>";
		
		$starttable=$currrow;
		if($this->excel_flag == 1){
			$nextrow = $currrow+1;
			$sheet->mergeCells('A'.(string)$currrow.':'.'A'.(string)$nextrow);
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
			$currcol += 1;
			$sheet->mergeCells('B'.(string)$currrow.':'.'B'.(string)$nextrow);	
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
			$currcol += 1;
			foreach($wilayahP as $w) {			
				$startcol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol);
				$currcol += 4;
				$endcol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol);
				$sheet->mergeCells($startcol.(string)$currrow.':'.$endcol.(string)$currrow);
				$currcol -= 4;
				$sheet->getStyle($startcol.(string)$currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $w->Wilayah);
				$currcol += 5;
			}
			$thiscol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol);
			$sheet->mergeCells($thiscol.(string)$currrow.':'.$thiscol.(string)$nextrow);	
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	
			
			$currrow+= 1;
			$currcol = 1;
			$lastrow = $currrow-1;
			//$sheet->mergeCells('A'.(string)$lastrow.':'.'A'.(string)$currrow);
			//$sheet->mergeCells('B'.(string)$lastrow.':'.'B'.(string)$currrow);			
			$currcol+= 2;
			foreach($wilayahP as $w) {
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Sale');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Retur Bagus');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Retur Cacat');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Disc');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
				$currcol += 1;
			}
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	
			$currrow+= 1;
			$currcol = 1;
		}
		
		foreach($omzetDivMerkP as $odm) {
			$tableP["body"].=" <tr>";
			$tableP["body"].="  <td class='td-left'><b>".$odm->Divisi."</b></td>";
			$tableP["body"].="  <td class='td-left'>".$odm->Merk."</td>";
			for($i=0;$i<count($WilayahP);$i++) {
				$ketemu = false;
				foreach($omzetP as $o) {
					if ($o->Divisi==$odm->Divisi && $o->Merk==$odm->Merk && $o->Wilayah==$WilayahP[$i] && $ketemu==false) {
						$tableP["body"].="  <td class='td-right'>".number_format($o->TotalJual)."</td>";
						$tableP["body"].="  <td class='td-right'>".number_format($o->TotalReturBagus)."</td>";
						$tableP["body"].="  <td class='td-right'>".number_format($o->TotalReturCacat)."</td>";
						$tableP["body"].="  <td class='td-right'>".number_format($o->TotalDisc)."</td>";
						$tableP["body"].="  <td class='td-right'>".number_format($o->OmzetNetto)."</td>";
						$ketemu = true;     
					}
				}
				if ($ketemu==false) {
					$tableP["body"].="  <td class='td-right'>0</td>";
					$tableP["body"].="  <td class='td-right'>0</td>";
					$tableP["body"].="  <td class='td-right'>0</td>";
					$tableP["body"].="  <td class='td-right'>0</td>";
					$tableP["body"].="  <td class='td-right'>0</td>";
				}
			}
			$tableP["body"].="  <td class='td-right'>".number_format($odm->TotalOmzetNetto)."</td>";
			$tableP["body"].="</tr>";
			
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $odm->Divisi);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $odm->Merk);
				$currcol += 1;
				for($i=0;$i<count($WilayahP);$i++) {
					$ketemu=false;
					foreach($omzetP as $o) {
						if ($o->Divisi==$odm->Divisi && $o->Merk==$odm->Merk && $o->Wilayah==$WilayahP[$i] && $ketemu==false) {
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalJual);
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturBagus);
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturCacat);
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalDisc);
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->OmzetNetto);
							$currcol += 1;
							$ketemu=true;
						}
					}
					if ($ketemu==false) {
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;					
					}
				}
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $odm->TotalOmzetNetto);
				
				$currrow+= 1;
				$currcol = 1;
			}
		}
		
		//row total
		$TOTAL = 0;
		$tableP["footer"] =" <tr>";
		$tableP["footer"].="  <td class='td-left cellFt'><b>Total</b></td>";
		$tableP["footer"].="  <td class='td-left cellFt'>&nbsp;</td>";
		for($i=0;$i<count($WilayahP);$i++) {
			$ketemu = false;
			foreach($omzetWilayahP as $o) {
				if ($o->Wilayah==$WilayahP[$i]) {
					$tableP["footer"].="  <td class='td-right cellFt'>".number_format($o->TotalJual)."</td>";
					$tableP["footer"].="  <td class='td-right cellFt'>".number_format($o->TotalReturBagus)."</td>";
					$tableP["footer"].="  <td class='td-right cellFt'>".number_format($o->TotalReturCacat)."</td>";
					$tableP["footer"].="  <td class='td-right cellFt'>".number_format($o->TotalDisc)."</td>";
					$tableP["footer"].="  <td class='td-right cellFt'>".number_format($o->OmzetNetto)."</td>";
					$ketemu = true;     
					$TOTAL+=$o->OmzetNetto;
				}
			}
			if ($ketemu==false) {
				$tableP["footer"].="  <td class='td-right cellFt'>0</td>";
				$tableP["footer"].="  <td class='td-right cellFt'>0</td>";
				$tableP["footer"].="  <td class='td-right cellFt'>0</td>";
				$tableP["footer"].="  <td class='td-right cellFt'>0</td>";
				$tableP["footer"].="  <td class='td-right cellFt'>0</td>";
			}
		}
		$tableP["footer"].="  <td class='td-right cellFt'>".number_format($TOTAL)."</td>";
		$tableP["footer"].="</tr>";
		
		if($this->excel_flag == 1){
			$TOTAL = 0;
			$sheet->mergeCells('A'.(string)$currrow.':'.'B'.(string)$currrow);
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
			$currcol += 2;
			for($i=0;$i<count($WilayahP);$i++) {
				$ketemu = false;
				foreach($omzetWilayahP as $o) {
					if ($o->Wilayah==$WilayahP[$i]) {
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalJual);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturBagus);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturCacat);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalDisc);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->OmzetNetto);
						$currcol += 1;
						$ketemu = true;     
						$TOTAL+=$o->OmzetNetto;
					}
				}
				if ($ketemu==false) {
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
				}
			}
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	
			
		}	    
		//PRODUK - END
		
		$currrow+= 2;
		$currcol = 1;
		
		//SPAREPART - START
		if($this->excel_flag == 1){
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Omzet Sparepart (Dalam Rp.)');
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			
			$currrow+= 2;
			$currcol = 1;
		}
		
		$tableS = array("header"=>"","body"=>"","footer"=>"");
		$tableS["header"].="  <tr>";
		$tableS["header"].="    <td rowspan='2' class='td-left cellHd'><b>Divisi</b></td>";
		$tableS["header"].="    <td rowspan='2' class='td-left cellHd'><b>Merk</b></td>";
		foreach($wilayahP as $w)
		{
			$ws+=1;
			$tableS["header"].="    <td colspan='5' class='td-center cellHd'><b>".$w->Wilayah."</b></td>";
			//array_push($WilayahSP, $w->Wilayah);
		}
		$tableS["header"].="    <td rowspan='2' class='td-right cellHd'><b>Total</b></td>";
		$tableS["header"].="  </tr>";
		$tableS["header"].="  <tr>";
		for ($i=0;$i<$wp;$i++) {
			$tableS["header"].="    <td class='td-right cellHd'><b>Sale</b></td>";
			$tableS["header"].="    <td class='td-right cellHd'><b>Retur Bagus</b></td>";
			$tableS["header"].="    <td class='td-right cellHd'><b>Retur Cacat</b></td>";
			$tableS["header"].="    <td class='td-right cellHd'><b>Disc</b></td>";
			$tableS["header"].="    <td class='td-right cellHd'><b>Total</b></td>";
		}
		$tableS["header"].="  </tr>";
		if($this->excel_flag == 1){
			$nextrow = $currrow+1;
			$sheet->mergeCells('A'.(string)$currrow.':'.'A'.(string)$nextrow);
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
			$currcol += 1;
			$sheet->mergeCells('B'.(string)$currrow.':'.'B'.(string)$nextrow);	
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
			$currcol += 1;
			foreach($wilayahP as $w) {
				$startcol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol);
				$currcol += 4;
				$endcol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol);
				$currcol -= 4;
				$sheet->mergeCells($startcol.(string)$currrow.':'.$endcol.(string)$currrow);
				$sheet->getStyle($startcol.(string)$currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $w->Wilayah);
				$currcol += 5;
			}
			$thiscol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol);
			$sheet->mergeCells($thiscol.(string)$currrow.':'.$thiscol.(string)$nextrow);	
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	
			
			$currrow+= 1;
			$currcol = 1;
			$lastrow = $currrow-1;
			//$sheet->mergeCells('A'.(string)$lastrow.':'.'A'.(string)$currrow);
			//$sheet->mergeCells('B'.(string)$lastrow.':'.'B'.(string)$currrow);			
			$currcol+= 2;
			foreach($wilayahP as $w) {
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Sale');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Retur Bagus');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Retur Cacat');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Disc');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
				$currcol += 1;
			}
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	
			
			$currrow+= 1;
			$currcol = 1;
		}
		
		foreach($omzetDivMerkSP as $odm) {
			$tableS["body"].=" <tr>";
			$tableS["body"].="  <td class='td-left'>".$odm->Divisi."</td>";
			$tableS["body"].="  <td class='td-left'>".$odm->Merk."</td>";
			for($i=0;$i<count($WilayahP);$i++) {
				$ketemu = false;
				foreach($omzetSP as $o) {
					if ($o->Divisi==$odm->Divisi && $o->Merk==$odm->Merk && $o->Wilayah==$WilayahP[$i]) {
						$tableS["body"].="  <td class='td-right'>".number_format($o->TotalJual)."</td>";
						$tableS["body"].="  <td class='td-right'>".number_format($o->TotalReturBagus)."</td>";
						$tableS["body"].="  <td class='td-right'>".number_format($o->TotalReturCacat)."</td>";
						$tableS["body"].="  <td class='td-right'>".number_format($o->TotalDisc)."</td>";
						$tableS["body"].="  <td class='td-right'>".number_format($o->OmzetNetto)."</td>";
						$ketemu = true;            
					}
				}
				if ($ketemu==false) {
					$tableS["body"].="  <td class='td-right'>0</td>";
					$tableS["body"].="  <td class='td-right'>0</td>";
					$tableS["body"].="  <td class='td-right'>0</td>";
					$tableS["body"].="  <td class='td-right'>0</td>";
					$tableS["body"].="  <td class='td-right'>0</td>";
				}
			}
			$tableS["body"].="  <td class='td-right'>".number_format($odm->TotalOmzetNetto)."</td>";
			$tableS["body"].="</tr>";
			
			if($this->excel_flag == 1){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $odm->Divisi);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $odm->Merk);
				$currcol += 1;
				for($i=0;$i<count($WilayahP);$i++) {
					$ketemu=false;
					foreach($omzetSP as $o) {
						if ($o->Divisi==$odm->Divisi && $o->Merk==$odm->Merk && $o->Wilayah==$WilayahP[$i]) {
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalJual);
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturBagus);
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturCacat);
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalDisc);
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->OmzetNetto);
							$currcol += 1;
							$ketemu=true;
						}
					}
					if ($ketemu==false) {
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
						$currcol += 1;					
					}					
				}
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $odm->TotalOmzetNetto);
				
				$currrow+= 1;
				$currcol = 1;
			}	      
		}
		//row total
		$TOTAL = 0;
		$tableS["footer"] =" <tr>";
		$tableS["footer"].="  <td class='td-left cellFt'><b>Total</b></td>";
		$tableS["footer"].="  <td class='td-left cellFt'>&nbsp;</td>";
		for($i=0;$i<count($WilayahP);$i++) {
			$ketemu = false;
			foreach($omzetWilayahSP as $o) {
				if ($o->Wilayah==$WilayahP[$i]) {
					$tableS["footer"].="  <td class='td-right cellFt'>".number_format($o->TotalJual)."</td>";
					$tableS["footer"].="  <td class='td-right cellFt'>".number_format($o->TotalReturBagus)."</td>";
					$tableS["footer"].="  <td class='td-right cellFt'>".number_format($o->TotalReturCacat)."</td>";
					$tableS["footer"].="  <td class='td-right cellFt'>".number_format($o->TotalDisc)."</td>";
					$tableS["footer"].="  <td class='td-right cellFt'>".number_format($o->OmzetNetto)."</td>";
					$ketemu = true;     
					$TOTAL+=$o->OmzetNetto;
				}
			}
			if ($ketemu==false) {
				$tableS["footer"].="  <td class='td-right cellFt'>0</td>";
				$tableS["footer"].="  <td class='td-right cellFt'>0</td>";
				$tableS["footer"].="  <td class='td-right cellFt'>0</td>";
				$tableS["footer"].="  <td class='td-right cellFt'>0</td>";
				$tableS["footer"].="  <td class='td-right cellFt'>0</td>";
			}
		}
		$tableS["footer"].="  <td class='td-right cellFt'>".number_format($TOTAL)."</td>";
		$tableS["footer"].="</tr>";
		
		if($this->excel_flag == 1){
			$TOTAL = 0;
			$sheet->mergeCells('A'.(string)$currrow.':'.'B'.(string)$currrow);
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
			$currcol += 2;
			for($i=0;$i<count($WilayahP);$i++) {
				$ketemu = false;
				foreach($omzetWilayahSP as $o) {
					if ($o->Wilayah==$WilayahP[$i]) {
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalJual);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturBagus);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturCacat);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalDisc);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->OmzetNetto);
						$currcol += 1;
						$ketemu = true;     
						$TOTAL+=$o->OmzetNetto;
					}
				}
				if ($ketemu==false) {
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
				}
			}
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	
		}	    	   
		//SPAREPART - END
		
		$currrow+= 2;
		$currcol = 1;
		
		//GABUNGAN
		if($this->excel_flag == 1){
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'GABUNGAN');
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			
			$currrow+= 2;
			$currcol = 1;
		}
		
		$tableG = array("header"=>"","body"=>"","footer"=>"");
		$tableG["header"].="  <tr>";
		$tableG["header"].="    <td class='td-left cellHd' colspan='2' rowspan='2'>Kategori Barang</td>";
		foreach($wilayahP as $w)
		{
			$tableG["header"].="    <td colspan='5' class='td-center cellHd'>".$w->Wilayah."</td>";
		}
		$tableG["header"].="    <td rowspan='2' class='td-right cellHd'><b>Total</b></td>";
		$tableG["header"].="  </tr>";
		$tableG["header"].="  <tr>";
		for ($i=0;$i<$wp;$i++) {
			$tableG["header"].="    <td class='td-right cellHd'>Sale</td>";
			$tableG["header"].="    <td class='td-right cellHd'>Retur Bagus</td>";
			$tableG["header"].="    <td class='td-right cellHd'>Retur Cacat</td>";
			$tableG["header"].="    <td class='td-right cellHd'>Disc</td>";
			$tableG["header"].="    <td class='td-right cellHd'>Total</td>";
		}
		$tableG["header"].="  </tr>";
		
		if($this->excel_flag == 1){
			$nextrow = $currrow+1;
			$sheet->mergeCells('A'.(string)$currrow.':'.'B'.(string)$nextrow);
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kategori Barang');
			$currcol += 2;
			foreach($wilayahP as $w) {
				$startcol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol);
				$currcol += 4;
				$endcol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol);
				$sheet->mergeCells($startcol.(string)$currrow.':'.$endcol.(string)$currrow);
				$currcol -= 4;
				$sheet->getStyle($startcol.(string)$currrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $w->Wilayah);
				$currcol += 5;
			}
			$thiscol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol);
			$sheet->mergeCells($thiscol.(string)$currrow.':'.$thiscol.(string)$nextrow);	
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	
			
			$currrow+= 1;
			$currcol = 1;
			$lastrow = $currrow-1;
			//$sheet->mergeCells('A'.(string)$lastrow.':'.'A'.(string)$currrow);
			//$sheet->mergeCells('B'.(string)$lastrow.':'.'B'.(string)$currrow);			
			$currcol+= 2;
			foreach($wilayahP as $w) {
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Sale');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Retur Bagus');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Retur Cacat');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Disc');
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
				$currcol += 1;
			}
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	
			
			$currrow+= 1;
			$currcol = 1;
		}
		
		$TOTAL = 0;
		$tableG["body"].=" <tr>";
		$tableG["body"].="  <td class='td-left' colspan='2'><b>PRODUK</b></td>";
		for($i=0;$i<count($WilayahP);$i++) {
			$ketemu = false;
			foreach($omzetWilayahP as $o) {
				if ($o->Wilayah==$WilayahP[$i]) {
					$tableG["body"].="  <td class='td-right'>".number_format($o->TotalJual)."</td>";
					$tableG["body"].="  <td class='td-right'>".number_format($o->TotalReturBagus)."</td>";
					$tableG["body"].="  <td class='td-right'>".number_format($o->TotalReturCacat)."</td>";
					$tableG["body"].="  <td class='td-right'>".number_format($o->TotalDisc)."</td>";
					$tableG["body"].="  <td class='td-right'>".number_format($o->OmzetNetto)."</td>";
					$ketemu = true;     
					$TOTAL+=$o->OmzetNetto;
				}
			}
			if ($ketemu==false) {
				$tableG["body"].="  <td class='td-right'>0</td>";
				$tableG["body"].="  <td class='td-right'>0</td>";
				$tableG["body"].="  <td class='td-right'>0</td>";
				$tableG["body"].="  <td class='td-right'>0</td>";
				$tableG["body"].="  <td class='td-right'>0</td>";
			}
		}
		$tableG["body"].="  <td class='td-right'>".number_format($TOTAL)."</td>";
		$tableG["body"].="</tr>";
		$TOTAL_ALL = $TOTAL;
		
		if($this->excel_flag == 1){
			$sheet->mergeCells('A'.(string)$currrow.':'.'B'.(string)$currrow);
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PRODUK');
			$currcol += 2;
			for($i=0;$i<count($WilayahP);$i++) {
				$ketemu = false;
				foreach($omzetWilayahP as $o) {
					if ($o->Wilayah==$WilayahP[$i]) {
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalJual);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturBagus);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturCacat);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalDisc);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->OmzetNetto);
						$currcol += 1;
						$ketemu = true;     
					}
				}
				if ($ketemu==false) {
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
				}
			}
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL);
			
			$currrow+= 1;
			$currcol = 1;
		}
		
		$TOTAL = 0;
		$tableG["body"].=" <tr>";
		$tableG["body"].="  <td class='td-left' colspan='2'><b>SPAREPART</b></td>";
		for($i=0;$i<count($WilayahP);$i++) {
			$ketemu = false;
			foreach($omzetWilayahSP as $o) {
				if ($o->Wilayah==$WilayahP[$i]) {
					$tableG["body"].="  <td class='td-right'>".number_format($o->TotalJual)."</td>";
					$tableG["body"].="  <td class='td-right'>".number_format($o->TotalReturBagus)."</td>";
					$tableG["body"].="  <td class='td-right'>".number_format($o->TotalReturCacat)."</td>";
					$tableG["body"].="  <td class='td-right'>".number_format($o->TotalDisc)."</td>";
					$tableG["body"].="  <td class='td-right'>".number_format($o->OmzetNetto)."</td>";
					$ketemu = true;     
					$TOTAL+=$o->OmzetNetto;
				}
			}
			if ($ketemu==false) {
				$tableG["body"].="  <td class='td-right'>0</td>";
				$tableG["body"].="  <td class='td-right'>0</td>";
				$tableG["body"].="  <td class='td-right'>0</td>";
				$tableG["body"].="  <td class='td-right'>0</td>";
				$tableG["body"].="  <td class='td-right'>0</td>";
			}
		}
		$tableG["body"].="  <td class='td-right'>".number_format($TOTAL)."</td>";
		$tableG["body"].="</tr>";
		$TOTAL_ALL+= $TOTAL;
		
		if($this->excel_flag == 1){
			$sheet->mergeCells('A'.(string)$currrow.':'.'B'.(string)$currrow);
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SPAREPART');
			$currcol += 2;
			for($i=0;$i<count($WilayahP);$i++) {
				$ketemu = false;
				foreach($omzetWilayahSP as $o) {
					if ($o->Wilayah==$WilayahP[$i]) {
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalJual);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturBagus);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalReturCacat);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->TotalDisc);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $o->OmzetNetto);
						$currcol += 1;
						$ketemu = true;     
					}
				}
				if ($ketemu==false) {
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 0);
					$currcol += 1;
				}
			}
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL);
			
			$currrow+= 1;
			$currcol = 1;
		}
		
		//row total
		$tableG["footer"] =" <tr>";
		$tableG["footer"].="  <td class='td-left cellFt' colspan='2'><b>Total</b></td>";
		
		if ($this->excel_flag==1) {
			$sheet->mergeCells('A'.(string)$currrow.':'.'B'.(string)$currrow);
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
			$currcol += 2;
		}
		
		for($i=0;$i<count($WilayahP);$i++) {
			$TotalJual = 0;
			$TotalReturBagus = 0;
			$TotalReturCacat = 0;
			$TotalDisc = 0;
			$OmzetNetto = 0;
			
			foreach($omzetWilayahP as $o) {
				if ($o->Wilayah==$WilayahP[$i]) {
					$TotalJual+=$o->TotalJual;
					$TotalReturBagus+=$o->TotalReturBagus;
					$TotalReturCacat+=$o->TotalReturCacat;
					$TotalDisc+=$o->TotalDisc;
					$OmzetNetto+=$o->OmzetNetto;
				}
			}
			
			foreach($omzetWilayahSP as $o) {
				if ($o->Wilayah==$WilayahP[$i]) {
					$TotalJual+=$o->TotalJual;
					$TotalReturBagus+=$o->TotalReturBagus;
					$TotalReturCacat+=$o->TotalReturCacat;
					$TotalDisc+=$o->TotalDisc;
					$OmzetNetto+=$o->OmzetNetto;
				}
			}
			$tableG["footer"].="  <td class='td-right cellFt'>".number_format($TotalJual)."</td>";
			$tableG["footer"].="  <td class='td-right cellFt'>".number_format($TotalReturBagus)."</td>";
			$tableG["footer"].="  <td class='td-right cellFt'>".number_format($TotalReturCacat)."</td>";
			$tableG["footer"].="  <td class='td-right cellFt'>".number_format($TotalDisc)."</td>";
			$tableG["footer"].="  <td class='td-right cellFt'>".number_format($OmzetNetto)."</td>";
			
			if ($this->excel_flag==1) {
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalJual);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalReturBagus);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalReturCacat);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TotalDisc);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $OmzetNetto);
				$currcol += 1;	      	
			}
		}
		$tableG["footer"].="  <td class='td-right cellFt'>".number_format($TOTAL_ALL)."</td>";
		$tableG["footer"].="</tr>";
		if ($this->excel_flag==1) {
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL_ALL);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	
			$currrow+= 1;
			$currcol = 1;
		}
		
		
		$body.= "<div><h3>Omzet Produk (Dalam Rp.)</h3></div>";
		$body.= "<table>".$tableP["header"].$tableP["body"].$tableP["footer"]."</table>";
		$body.= "<div style='clear:both;height:20px;'></div>";
		$body.= "<div><h3>Omzet Sparepart (Dalam Rp.)</h3></div>";
		$body.= "<table>".$tableS["header"].$tableS["body"].$tableS["footer"]."</table>";
		$body.= "<div style='clear:both;height:20px;'></div>";
		$body.= "<div><h3>GABUNGAN</h3></div>";
		$body.= "<table>".$tableG["header"].$tableG["body"].$tableG["footer"]."</table>";
		$body.= "<div style='clear:both;height:80px;'></div>";
		
		
		$footer = form_open('ReportOmzet/RekapOmzetNasional', array('target'=>'_blank'));
		$footer.= '<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;">';
		$footer.= '  <input type="text" name="Tahun" id="Tahun" value="'.$th.'" style="display:none;">';
		$footer.= '  <input type="text" name="Bulan" id="Bulan" value="'.$bl.'" style="display:none;">';
		$footer.= '  <div style="clear:both;">';
		$footer.= "    <div style='margin:10px;float:left;'>";
		$footer.= '      <input type = "submit" name="btnExportExcel" value="Export Excel"/>  ';
		$footer.= '    </div>';
		$footer.= "    <div style='margin:10px;float:left;display:none;'>";
		$footer.= '      <input type = "submit" name="btnEmailExcel" value="Email Excel"/>';
		$footer.= '    </div>';
		$footer.= '</div>';
		$footer.= form_close();
		
		if ($this->excel_flag==1) {
			for ($i = 'A'; $i !=   $sheet->getHighestColumn(); $i++) {
				$sheet->getColumnDimension($i)->setAutoSize(TRUE);
			}
		}
		
		
		if ($this->excel_flag==1) {
			/*$filename="C:/OmzetNasional[".$th."][".$bl."].xlsx"; //save our workbook as this file name
				//$filename='C:/OmzetNasional.xlsx'; //save our workbook as this file name
				$writer = new Xlsx($spreadsheet);
			$writer->save($filename); // download file */
			
			$filename='OmzetNasional['.$th.']['.$bl.']'; //save our workbook as this file name
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

		else {
			$data["th"] = $th;
			$data["bl"] = $bl;
			$data["content_html"] = $header."<br>".$body.$footer;

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('ReportOmzetNasionalResult',$data);
		}
	}
	
	public function SummaryOmzetNasional()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN OMZET NASIONAL";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES LAPORAN OMZET NASIONAL SUMMARY";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$post = $this->PopulatePost();
		$summaries = '';
		if(isset($post['th']) && isset($post["bl"]))
		{
			$summaries = $this->OmzetModel->Summary($post['th'],$post['bl']);
			if($summaries != null) {
				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				echo json_encode(array("result"=>"sukses","data"=>$summaries,"error"=>""));
			}
			
			else {
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				echo json_encode(array("result"=>"sukses","data"=>array(),'error'=>'Data Tidak Ada'));
			}
			
		} 
		else {
			// ActivityLog Update FAILED
			$params['Remarks']="FAILED - Data Tidak Ditemukan";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			echo json_encode(array("result"=>"gagal","data"=>$summaries,'error'=>'Parameter Tidak Lengkap'));
		}

	}
	


	public function OmzetHarian()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN OMZET HARIAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN OMZET HARIAN ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$data = array();
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}		
		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;
		
		$this->moduleID = "REPORT OMZET CABANG";
		$data['title'] = $this->moduleID;
		$data["moduleID"] = $this->moduleID;
		$data["configs"] = $this->MsConfigModel->GetConfigs($this->moduleID);

		$data["formURL"] = "ReportOmzet/ProsesOmzetHarian";
		$data["btnPDF"] = 0;
		$data["btnExcel"] = 1;
		$data["opt"] = "OMZET HARIAN";
		$data["err"] = "";
		
		$bUrl = $url.API_BKT."/MasterDealer/GetListAllDealer?api=APITES";
		// $GetAllDealer = json_decode(file_get_contents($bUrl), true);
		$GetAllDealer = file_get_contents($bUrl);
		$GetAllDealer = $this->GzipDecodeModel->_decodeGzip_true($GetAllDealer);
		$dealers = array();
		if ($GetAllDealer["result"]=="sukses") {
			$AllDealers = $GetAllDealer["data"];
			for($i=0;$i<count($AllDealers);$i++) {
				array_push($dealers, trim($AllDealers[$i]["NM_PLG"])." - ".trim($AllDealers[$i]["WILAYAH"])." - ".trim($AllDealers[$i]["KD_PLG"]));
			}        
			$data["ListDealers"] = $AllDealers;
		}
		$data["Dealers"] = $dealers;
		
		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView('ReportOmzetHarianForm',$data);
	}
	
	public function ProsesOmzetHarian()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN OMZET HARIAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES LAPORAN OMZET HARIAN ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$data = array();
		/*
			$this->confirm_flag = 0;
			if (isset($_POST["btnPdf"])) {
			$this->pdf_flag = 1;
			} else {
			$this->pdf_flag = 0;
		}*/
		
		if ($_POST["OptReport"]=="001") {
			$this->load->library('form_validation');
			$this->form_validation->set_rules('dp1','Tanggal Awal','required');
			$this->form_validation->set_rules('dp2','Tanggal Akhir','required');
			
			if($this->form_validation->run())
			{
				$dp1 = $_POST["dp1"];
				$dp2 = $_POST["dp2"];
				$plg = $_POST["KodeDealer"];
				if (isset($_POST["btnExcel"])) {
					$this->excel_flag = 1;
					//die("Excel");
					} else {
					$this->excel_flag = 0;
				}
				
				$this->ProsesOmzetHarian_001_PerDealerPerFaktur($dp1, $dp2, $plg, $params);
			}
			} else  {

				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				
				redirect("ReportOmzet/OmzetHarian");
		}
	}
	
	public function ProsesOmzetHarian_001_PerDealerPerFaktur($dp1, $dp2, $plg, $params)
	{
		$data = array();
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}		
		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;
		$api = "APITES";
		
		$bUrl = $url.API_BKT."/MasterDealer/GetDealer?api=".urlencode($api)."&plg=".urlencode($plg)
		."&svr=".urlencode($svr)."&db=".urlencode($db)."&uid=".urlencode(SQL_UID)."&pwd=".urlencode(SQL_PWD);
		//die($bUrl);
		// $GetDealer = json_decode(file_get_contents($bUrl), true);
		$GetDealer = file_get_contents($bUrl);
		$GetDealer = $this->GzipDecodeModel->_decodeGzip_true($GetDealer);
		$Dealer = array();

		if ($GetDealer["result"]=="sukses") {
			$Dealer = $GetDealer["data"];
			
			$bUrl = $url.API_BKT."/ReportOmzet/OmzetHarianPerDealerPerFaktur?api=".urlencode($api)
			."&dp1=".urlencode($dp1)."&dp2=".urlencode($dp2)."&plg=".urlencode($plg)
			."&svr=".urlencode($svr)."&db=".urlencode($db);
			//die($bUrl);
			$GetData = json_decode(file_get_contents($bUrl), true);
			if ($GetData["result"]=="sukses") {
				$DataOmzet = $GetData["data"];
				
				$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet(0);
				
				$style = "<style>";
				$header = "";
				$content = "";
				$footer = "";
				
				$style.= " 	body { font-size:10pt; }";
				$style.= "	th, td { border:1px solid #ccc; padding:3px; }";
				$style.= "	.colRight { text-align:right; } ";
				
				$header.= "<h3>LAPORAN PENJUALAN HARIAN PER FAKTUR</h3><br>";
				$header.= "Periode: <b>".date("d-M-Y", strtotime($dp1))." - ".date("d-M-Y", strtotime($dp2))."</b><br>";
				$header.= "Dealer: <b>".$Dealer["NM_PLG"]." [".$Dealer["KD_PLG"]."]</b><br>";
				$header.= "Wilayah: <b>".$Dealer["WILAYAH"]."</b>";
				
				if($this->excel_flag == 1){
					$sheet->setTitle('LaporanPenjualanHarianPerFaktur');
					$sheet->setCellValue('A1', 'LAPORAN PENJUALAN HARIAN PER FAKTUR');
					$sheet->getStyle('A1')->getFont()->setSize(20);
					$sheet->setCellValue('A2', 'Periode : '.date("d-M-Y", strtotime($dp1))." - ".date("d-M-Y", strtotime($dp2)));
					$sheet->setCellValue('A3', 'Dealer : '.trim($Dealer["NM_PLG"])." [".$Dealer["KD_PLG"]."]");
					$sheet->setCellValue('A4', 'Wilayah : '.$Dealer["WILAYAH"]);
				}
				
				$content.= "<table>";
				$content.= "	<tr>";
				$content.= "		<th width='5%'>No</th>";
				$content.= "		<th width='12%'>No Faktur</th>";
				$content.= "		<th width='10%'>Tgl Faktur</th>";
				$content.= "		<th width='20%'>Ket</th>";
				$content.= "		<th width='3%'>Jns</th>";
				$content.= "		<th width='10%'>Subtotal</th>";
				$content.= "		<th width='10%'>DiscTambahan</th>";
				$content.= "		<th width='12%'>DPP</th>";
				$content.= "		<th width='10%'>PPN</th>";
				$content.= "		<th width='13%'>Grandtotal</th>";
				$content.= "	</tr>";
				
				if($this->excel_flag == 1){
					$currcol = 1;
					$currrow = 6;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "No");
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "No Faktur");
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Tgl Faktur");
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Ket");
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Jns");
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Subtotal");
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "DiscTambahan");
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "DPP");
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "PPN");
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "Grandtotal");
					
					$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
					$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
					$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	
				}
				
				$NO = 0;
				$TOTALSUBTOTAL=0;
				$TOTALDISC = 0;
				$TOTALDPP = 0;
				$TOTALPPN = 0;
				$TOTALGRANDTOTAL=0;
				
				for($i=0;$i<count($DataOmzet);$i++) {
					$NO+=1;
					$TRX = ((trim($DataOmzet[$i]["NM_TRX"])=="")? "":trim($DataOmzet[$i]["NM_TRX"])." [".$DataOmzet[$i]["JNS_TRX"]."]");
					if ($DataOmzet[$i]["KD_TRN"]=="J") {
						$SUBTOTAL =$DataOmzet[$i]["SUBTOTAL"];
						$DISC =$DataOmzet[$i]["DISC_TAMBAHAN"];
						$DPP =$DataOmzet[$i]["DPP"];
						$PPN =$DataOmzet[$i]["TOTALPPN"];
						$GRANDTOTAL =$DataOmzet[$i]["GRANDTOTAL"];
						} else {
						$SUBTOTAL =-1*$DataOmzet[$i]["SUBTOTAL"];
						$DISC =-1*$DataOmzet[$i]["DISC_TAMBAHAN"];
						$DPP =-1*$DataOmzet[$i]["DPP"];
						$PPN =-1*$DataOmzet[$i]["TOTALPPN"];
						$GRANDTOTAL =-1*$DataOmzet[$i]["GRANDTOTAL"];
					}
					
					$TOTALSUBTOTAL+=$DataOmzet[$i]["SUBTOTAL"];
					$TOTALDISC+=$DataOmzet[$i]["DISC_TAMBAHAN"];
					$TOTALDPP+=$DataOmzet[$i]["DPP"];
					$TOTALPPN+=$DataOmzet[$i]["TOTALPPN"];
					$TOTALGRANDTOTAL+=$DataOmzet[$i]["GRANDTOTAL"];
					
					$content.= "	<tr>";
					$content.= "		<td class='colRight'>".$NO."</td>";
					$content.= "		<td>".$DataOmzet[$i]["NO_FAKTUR"]."</td>";
					$content.= "		<td>".date("d-M-Y",strtotime($DataOmzet[$i]["TGL_FAKTUR"]))."</td>";
					$content.= "		<td>".$TRX."</td>";
					$content.= "		<td>".$DataOmzet[$i]["KD_TRN"]."</td>";
					$content.= "		<td class='colRight'>".number_format($SUBTOTAL)."</td>";
					$content.= "		<td class='colRight'>".number_format($DISC)."</td>";
					$content.= "		<td class='colRight'>".number_format($DPP)."</td>";
					$content.= "		<td class='colRight'>".number_format($PPN)."</td>";
					$content.= "		<td class='colRight'>".number_format($GRANDTOTAL)."</td>";
					$content.= "	</tr>";
					
					if($this->excel_flag == 1){
						$currcol = 1;
						$currrow += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $NO);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataOmzet[$i]["NO_FAKTUR"]);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, date("d-M-Y",strtotime($DataOmzet[$i]["TGL_FAKTUR"])));
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TRX);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DataOmzet[$i]["KD_TRN"]);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SUBTOTAL);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DISC);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $DPP);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $PPN);
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $GRANDTOTAL);
						$currcol += 1;
					}
					
				}
				
				$content.= "	<tr>";
				$content.= "		<td class='colRight' colspan='5'><b>TOTAL</b></td>";
				$content.= "		<td class='colRight'><b>".number_format($TOTALSUBTOTAL)."</b></td>";
				$content.= "		<td class='colRight'><b>".number_format($TOTALDISC)."</b></td>";
				$content.= "		<td class='colRight'><b>".number_format($TOTALDPP)."</b></td>";
				$content.= "		<td class='colRight'><b>".number_format($TOTALPPN)."</b></td>";
				$content.= "		<td class='colRight'><b>".number_format($TOTALGRANDTOTAL)."</b></td>";
				$content.= "	</tr>";
				
				if($this->excel_flag == 1){
					$currcol = 5;
					$currrow += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "TOTAL");
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTALSUBTOTAL);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTALDISC);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTALDPP);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTALPPN);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTALGRANDTOTAL);
					
					$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFont()->setBold(true);
					$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
					$sheet->getStyle("A".$currrow.":".\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currcol).$currrow)->getFill()->getStartColor()->setARGB('FFfcba03');	
				}
				$style.="</style>";
				
				if ($this->excel_flag==1) {
					$sheet->mergeCells('A1:J1');
					$sheet->mergeCells('A2:D2');
					$sheet->mergeCells('A3:D3');
					$sheet->mergeCells('A4:D4');
					for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
						$sheet->getColumnDimension($i)->setAutoSize(TRUE);
					}
					
					$filename='OmzetHarianPerDealerPerFaktur_'.strtolower(trim($Dealer["NM_PLG"])); //save our workbook as this file name
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
					
					} else {
					$data["content_html"] = $style."<br>".$header."<br>".$content."<br>".$footer;

					// ActivityLog Update SUCCESS
					$params['Remarks']="SUCCESS";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

					$this->RenderView('ReportOmzetNasionalResult',$data);
				}				
			}
		}
	}
	


	public function OmzetPerPartnerType()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN PENJUALAN OMZET PER PARTNER TYPE";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN OMZET PER PARTNER TYPE ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$data = array();
		$api = 'APITES';
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}
		
		$url = $this->API_URL."/ReportOmzet/CheckDivisi?api=".$api;
		// echo($url."<br>");
		$check_divisi = json_decode(file_get_contents($url));
		// die(json_encode($check_divisi));
		//$check_wilayah = json_decode(file_get_contents($this->API_URL."/ReportOmzet/CheckWilayah?api=".$api));
		//$check_kota = json_decode(file_get_contents($this->API_URL."/ReportOmzet/CheckKota?api=".$api));
		
		// die($this->API_URL."/ReportOmzet/CheckK?api=".$api);
		
		$this->moduleID = "REPORT OMZET CABANG";
		$data['title'] = $this->moduleID;
		$data["moduleID"] = $this->moduleID;
		$data["configs"] = $this->MsConfigModel->GetConfigs($this->moduleID);

		$data["formURL"] = "ReportOmzet/ProsesOmzetPerPartnerType";
		$data['divisi'] = $check_divisi;
		//$data['wilayah'] = $check_wilayah;
		//$data['partnertype'] = $check_kota;
		$data["opt"] = "OMZET PER PARTNER TYPE";
		$data["err"] = "";
		$data["BE_Java"] = $this->BE_Java;
		$data["BE_Java_Multi_Filter"] = $this->BE_Java_Multi_Filter;
		
		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView('ReportOmzetPerPartnerTypeForm',$data);
	}
	
	public function ProsesOmzetPerPartnerType()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN PENJUALAN OMZET PER PARTNER TYPE";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES LAPORAN OMZET PER PARTNER TYPE ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$data = array();
		$page_title = 'Report Omzet Per Partner Type';
		
		$this->load->library('form_validation');
		$this->form_validation->set_rules('Tahun','Tahun','required');
		$this->form_validation->set_rules('Bulan','Bulan','required');
		$this->form_validation->set_rules('divisi','Divisi','required');
		$this->form_validation->set_rules('PartnerType','PartnerType','required');
		//$this->form_validation->set_rules('wilayah','Wilayah','required');
		//$this->form_validation->set_rules('kota','Kota','required');
		
		if($this->form_validation->run())
		{
			$th = $_POST["Tahun"];
			$bl = $_POST["Bulan"];
			if ($this->BE_Java == 0){	
				$divisi = $_POST["divisi"];
				$partnertype = $_POST["PartnerType"];
			} else {
				if ($this->BE_Java_Multi_Filter == 0){	
					$divisi = $_POST["divisi"];
					$partnertype = $_POST["PartnerType"];
				} else {		
					$divisi= "";
					foreach ($_POST['divisi'] as $value){
					$divisi .= "'$value'". ",";
					}
					$divisi = substr($divisi,0,-1);

					$partnertype= "";
					foreach ($_POST['PartnerType'] as $value){
					$partnertype .= "'$value'". ",";
					}
					$partnertype = substr($partnertype,0,-1);
				}
			}

			//$wilayah = $_POST["wilayah"];
			//$kota = $_POST["kota"];
			
			//$this->Proses_OmzetPerKota($page_title, $th, $bl, $divisi, $wilayah, $kota);
			$this->Proses_OmzetPerPartnerType($page_title, $th, $bl, $divisi, $partnertype, $params);
			} else  {

				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				
				redirect("ReportOmzet/OmzetPerPartnerType");
		}
		
	}
	
	public function Proses_OmzetPerPartnerType($page_title, $th, $bl, $divisi, $partnertype, $params)
	{
		$dp1=$bl."/01/".$th;
		$dp =date_create($dp1);
		date_add($dp, date_interval_create_from_date_string("1 month"));
		date_add($dp, date_interval_create_from_date_string("-1 day"));
		$dp2=$dp->format("m/d/Y");
		
		$api = 'APITES';
		
		set_time_limit(60);
		// die($this->API_URL."/ReportOmzet/ReportOmzetPerKota?api=".urlencode($api)
		// ."&tgl1=".urlencode($dp1)."&tgl2=".urlencode($dp2)
		// ."&divisi=".urlencode($divisi)."&wilayah=".urlencode($wilayah)
		// ."&kota=".urlencode($kota));

		if ($this->BE_Java == 0){	
			// echo "webAPI";
			// echo "<br>";

			$json = json_decode(file_get_contents($this->API_URL."/ReportOmzet/ReportOmzetPerPartnerType?api=".urlencode($api)
			."&tgl1=".urlencode($dp1)."&tgl2=".urlencode($dp2)
			."&divisi=".urlencode(trim($divisi))
			."&partnertype=".urlencode($partnertype)), true);

			// echo $this->API_URL."/ReportOmzet/ReportOmzetPerPartnerType?api=".urlencode($api)
			// ."&tgl1=".urlencode($dp1)."&tgl2=".urlencode($dp2)
			// ."&divisi=".urlencode(trim($divisi))
			// ."&partnertype=".urlencode($partnertype);
			// echo "<br>";
			// echo json_encode($json);
		} else {
			// echo "JAVA";
			// echo "<br>";

			$json = json_decode(file_get_contents(JAVA_API_URL."/ReportOmzet/ProsesOmzetPerPartnerType?api=".urlencode($api)
			."&tgl1=".($dp1)."&tgl2=".($dp2)
			."&divisi=".rawurlencode(str_replace("'","",trim($divisi)))
			."&partnertype=".rawurlencode(str_replace("'","",trim($partnertype)))), true);
			
			// echo JAVA_API_URL."/ReportOmzet/ProsesOmzetPerPartnerType?api=".urlencode($api)
			// ."&tgl1=".($dp1)."&tgl2=".($dp2)
			// ."&divisi=".rawurlencode(str_replace("'","",trim($divisi)))
			// ."&partnertype=".rawurlencode(str_replace("'","",trim($partnertype)));
			// echo "<br>";
			// echo json_encode($json);
			$json = $json["detail"];
		}
		//die;

		if(count($json)>0){
			$this->LoadWilayahGroup();
			// cek group kota
			$data = array();
			$header = array();
			foreach($json as $json_kategori => $json_details) {

				//print_r($json_details);
				
				$details = array();
				foreach($json_details as $json_detail) {		
					
					//print_r($json_details);
					//die;
					//$new_kota = $this->GetWilayahGroup(trim($json_detail['wilayah']),trim($json_detail['kota']));
					// echo $new_kota."<br>";
					$newpartnertype= $json_detail['partnertype'];
					$new_divisi= $json_detail['divisi'];
					$new_merk= $json_detail['merk'];

					//if ($new_kota==trim($json_detail["kota"])) {
					//	$new_header = $json_detail['wilayah']." - ".$new_kota;
					//} else {
						$new_header = $json_detail['wilayah']; 
					//}
					$detail = array(
					'wilayah'=>$new_header,
					'jual'=>$json_detail['jual'],
					'retur_b'=>$json_detail['retur_b'],
					'retur_c'=>$json_detail['retur_c'],
					'biaya'=>$json_detail['biaya'],
					'omzet'=>$json_detail['omzet'],
					);
					$header[] = $new_header;
					
					if(ISSET($details[$new_divisi][$new_merk][$new_header])){
						$details[$new_divisi][$new_merk][$new_header]['jual'] += $json_detail['jual']; 
						$details[$new_divisi][$new_merk][$new_header]['retur_b'] += $json_detail['retur_b']; 
						$details[$new_divisi][$new_merk][$new_header]['retur_c'] += $json_detail['retur_c']; 
						$details[$new_divisi][$new_merk][$new_header]['biaya'] += $json_detail['biaya']; 
						$details[$new_divisi][$new_merk][$new_header]['omzet'] += $json_detail['omzet']; 
					}
					else
					$details[$new_divisi][$new_merk][$new_header] = $detail;
				}					
				$data[$json_kategori] = $details;
			}
			
			
			$header = array_unique($header);
			sort($header);
			// print_r($data);
			// die;

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->Preview_ReportOmzetPerPartnerType($dp1, $divisi, $partnertype, $header, $data);
		}
		else {
			$params['Remarks']="FAILED - Data Tidak Ditemukan";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			die('tidak ada data');

		}
	}
	
	public function Preview_ReportOmzetPerPartnerType($dp1, $divisi, $partnertype, $header, $data) 
	{			
		$warna_jual = 'F0FFF0';
		$warna_retur_b = 'FFF8DC';
		$warna_retur_c = 'FFF5EE';
		$warna_biaya = 'F5F5F5';
		$warna_omzet = 'F0F8FF';
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);
		
		$sheet->setTitle('OmzetPerPartnerType');
		$sheet->setCellValue('A1', 'LAPORAN OMZET PER PARTNER TYPE');
		$sheet->getStyle('A1')->getFont()->setSize(18);
		$sheet->setCellValue('A2', 'Periode');
		$sheet->setCellValue('A3', 'Divisi');
		$sheet->setCellValue('A4', 'Partner Type');
		//$sheet->setCellValue('A5', 'Kota');
		
		$sheet->setCellValue('B2', date("F Y", strtotime($dp1)));
		$sheet->setCellValue('B3', $divisi);
		$sheet->setCellValue('B4', $partnertype);
		//$sheet->setCellValue('B5', $kota);
		$sheet->getStyle('B2:B5')->getFont()->setBold(true);
		
		$currcol = 1;
		$currrow = 5;
		
		$styleArray = [
		'borders' => [
		'allBorders' => [
		'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
		],
		],
		];
		$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
		$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
		
		foreach ($data as $kategori=>$divisis) {
			
			$currcol = 1;
			$currrow +=2;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KATEGORI');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $kategori);
			$sheet->getStyle('B'.$currrow)->getFont()->setBold(true);
			$sheet->getStyle('A'.$currrow.':B'.$currrow)->getFont()->setSize(12);
			
			$currcol = 1;
			$currrow +=1;
			$startrow = $currrow;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$sheet->getColumnDimension('A')->setWidth(20);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$sheet->getColumnDimension('B')->setWidth(20);
			$currcol += 1;
			
			$sheet->getStyle('A'.($currrow).':A'.($currrow+1))->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('B'.($currrow).':B'.($currrow+1))->getAlignment()->setHorizontal($alignment_center);
			
			
			foreach ($header as $hd){
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setHorizontal($alignment_center);
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $hd);
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+4 , $currrow);
				$currcol += 5;
			}
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setHorizontal($alignment_center);
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+4 , $currrow);
			$sheet->getStyle('B'.($currrow).':B'.($currrow+1))->getAlignment()->setHorizontal($alignment_center);
			$currcol += 5;
			
			$currrow += 1;
			$currcol = 3;
			foreach ($header as $hd){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Jual');
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_jual);
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'ReturBagus');
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_b);
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'ReturCacat');
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_c);
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Disc');
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_biaya);
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'OmzetNetto');
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_omzet);
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
				$currcol += 1;
			}
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Jual');
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_jual);
			$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'ReturBagus');
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_b);
			$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'ReturCacat');
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_c);
			$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Disc');
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_biaya);
			$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'OmzetNetto');
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_omzet);
			$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			$currcol += 1;
			$max_col = $currcol-1;
			
			$sheet->getStyle('C'.($currrow).':'.PHPExcel_Cell::stringFromColumnIndex($max_col-1).$currrow)->getAlignment()->setHorizontal($alignment_right);
			$sheet->getStyle('A'.($currrow-1).':'.PHPExcel_Cell::stringFromColumnIndex($max_col-1).$currrow)->getFont()->setBold(true);
			$sheet->getStyle('A'.($currrow-1).':'.PHPExcel_Cell::stringFromColumnIndex($max_col-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('f0e68c');
			
			foreach ($divisis as $divisi=>$merks) {
				foreach ($merks as $merk=>$kotas) {
					
					$currcol = 1;
					$currrow +=1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $divisi);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $merk);
					$currcol += 1;
					
					$jum_jual = 0;
					$jum_retur_b = 0;
					$jum_retur_c = 0;
					$jum_biaya = 0;
					$jum_omzet = 0;
					
					foreach ($header as $hd){
						$ada = 0;
						foreach ($kotas as $kota=>$dt) {
							if($kota==$hd) {
								$ada = 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt['jual']);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_jual);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt['retur_b']);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_b);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt['retur_c']);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_c);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt['biaya']);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_biaya);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt['omzet']);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_omzet);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$currcol += 1;
								
								$jum_jual += ($dt['jual']);
								$jum_retur_b += ($dt['retur_b']);
								$jum_retur_c += ($dt['retur_c']);
								$jum_biaya += ($dt['biaya']);
								$jum_omzet += ($dt['omzet']);
							}
						}
						if($ada==0){
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_jual);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_b);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_c);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_biaya);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_omzet);
							$currcol += 1;
						}
						
					}
					
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jum_jual);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_jual);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jum_retur_b);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_b);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jum_retur_c);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_c);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jum_biaya);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_biaya);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jum_omzet);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_omzet);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$currcol += 1;
					
				}
			}
			$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($max_col-1).$currrow)->applyFromArray($styleArray);
			
		}
		$sheet->setSelectedCell('A1');
		
		$sheet->freezePane('C1');
		
		$filename='OmzetPerPartnerType['.date("FY", strtotime($dp1)).']';
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



	public function OmzetPerKota()
	{
		$data = array();
		$api = 'APITES';
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}
		
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN OMZET PER KOTA";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN OMZET PER KOTA ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);
		
		$check_divisi = json_decode(file_get_contents($this->API_URL."/ReportOmzet/CheckDivisi?api=".$api));
		$check_wilayah = json_decode(file_get_contents($this->API_URL."/ReportOmzet/CheckWilayah?api=".$api));
		$check_kota = json_decode(file_get_contents($this->API_URL."/ReportOmzet/CheckKota?api=".$api));
		
		// die($this->API_URL."/ReportOmzet/CheckK?api=".$api);
		
		$this->moduleID = "REPORT OMZET CABANG";
		$data['title'] = $this->moduleID;
		$data["moduleID"] = $this->moduleID;
		$data["configs"] = $this->MsConfigModel->GetConfigs($this->moduleID);

		$data["formURL"] = "ReportOmzet/ProsesOmzetPerKota";
		$data['divisi'] = $check_divisi;
		$data['wilayah'] = $check_wilayah;
		$data['kota'] = $check_kota;
		$data["opt"] = "OMZET PER KOTA";
		$data["err"] = "";

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView('ReportOmzetPerKotaForm',$data);
	}
	
	public function ProsesOmzetPerKota()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN OMZET PER KOTA";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES LAPORAN OMZET PER KOTA ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$data = array();
		$page_title = 'Report Omzet Per Kota';
		
		$this->load->library('form_validation');
		$this->form_validation->set_rules('Tahun','Tahun','required');
		$this->form_validation->set_rules('Bulan','Bulan','required');
		$this->form_validation->set_rules('divisi','Divisi','required');
		$this->form_validation->set_rules('wilayah','Wilayah','required');
		$this->form_validation->set_rules('kota','Kota','required');
		
		if($this->form_validation->run())
		{
			$th = $_POST["Tahun"];
			$bl = $_POST["Bulan"];
			$divisi = $_POST["divisi"];
			$wilayah = $_POST["wilayah"];
			$kota = $_POST["kota"];
			
			$this->Proses_OmzetPerKota($page_title, $th, $bl, $divisi, $wilayah, $kota, $params);
		} 
		else  {
			// ActivityLog Update FAILED
			$params['Remarks']="FAILED - Data Tidak Ditemukan";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			redirect("ReportOmzet/OmzetPerKota");
		}
		
	}
	
	public function Proses_OmzetPerKota($page_title, $th, $bl, $divisi, $wilayah, $kota, $params)
	{
		$dp1=$bl."/01/".$th;
		$dp =date_create($dp1);
		date_add($dp, date_interval_create_from_date_string("1 month"));
		date_add($dp, date_interval_create_from_date_string("-1 day"));
		$dp2=$dp->format("m/d/Y");
		
		$api = 'APITES';
		
		set_time_limit(60);
		// die($this->API_URL."/ReportOmzet/ReportOmzetPerKota?api=".urlencode($api)
		// ."&tgl1=".urlencode($dp1)."&tgl2=".urlencode($dp2)
		// ."&divisi=".urlencode($divisi)."&wilayah=".urlencode($wilayah)
		// ."&kota=".urlencode($kota));
		
		$json = json_decode(file_get_contents($this->API_URL."/ReportOmzet/ReportOmzetPerKota?api=".urlencode($api)
		."&tgl1=".urlencode($dp1)."&tgl2=".urlencode($dp2)
		."&divisi=".urlencode($divisi)."&wilayah=".urlencode($wilayah)
		."&kota=".urlencode($kota)), true);
		
		// print_r($data);
		// die;
		
		if(count($json)>0){
			$this->LoadWilayahGroup();
			// cek group kota
			$data = array();
			$header = array();
			foreach($json as $json_kategori => $json_details) {
				
				$details = array();
				foreach($json_details as $json_detail) {					
					$new_kota = $this->GetWilayahGroup(trim($json_detail['wilayah']),trim($json_detail['kota']));
					// echo $new_kota."<br>";
					$new_divisi= $json_detail['divisi'];
					$new_merk= $json_detail['merk'];

					if ($new_kota==trim($json_detail["kota"])) {
						$new_header = $json_detail['wilayah']." - ".$new_kota;
					} else {
						$new_header = $new_kota; 
					}
					$detail = array(
					'kota'=>$new_header,
					'jual'=>$json_detail['jual'],
					'retur_b'=>$json_detail['retur_b'],
					'retur_c'=>$json_detail['retur_c'],
					'biaya'=>$json_detail['biaya'],
					'omzet'=>$json_detail['omzet'],
					);
					$header[] = $new_header;
					
					if(ISSET($details[$new_divisi][$new_merk][$new_header])){
						$details[$new_divisi][$new_merk][$new_header]['jual'] += $json_detail['jual']; 
						$details[$new_divisi][$new_merk][$new_header]['retur_b'] += $json_detail['retur_b']; 
						$details[$new_divisi][$new_merk][$new_header]['retur_c'] += $json_detail['retur_c']; 
						$details[$new_divisi][$new_merk][$new_header]['biaya'] += $json_detail['biaya']; 
						$details[$new_divisi][$new_merk][$new_header]['omzet'] += $json_detail['omzet']; 
					}
					else
					$details[$new_divisi][$new_merk][$new_header] = $detail;
				}					
				$data[$json_kategori] = $details;
			}
			
			
			$header = array_unique($header);
			sort($header);
			// print_r($data);
			// die;

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->Preview_ReportOmzetPerKota($dp1, $divisi, $wilayah, $kota, $header, $data);
		}
		else {
			// ActivityLog Update FAILED
			$params['Remarks']="FAILED - Data Tidak Ditemukan";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			die('tidak ada data');
		}
	}
	
	public function Preview_ReportOmzetPerKota($dp1, $divisi, $wilayah, $kota, $header, $data) 
	{			
		$warna_jual = 'F0FFF0';
		$warna_retur_b = 'FFF8DC';
		$warna_retur_c = 'FFF5EE';
		$warna_biaya = 'F5F5F5';
		$warna_omzet = 'F0F8FF';
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);
		
		$sheet->setTitle('OmzetPerKota');
		$sheet->setCellValue('A1', 'LAPORAN OMZET PER KOTA');
		$sheet->getStyle('A1')->getFont()->setSize(18);
		$sheet->setCellValue('A2', 'Periode');
		$sheet->setCellValue('A3', 'Divisi');
		$sheet->setCellValue('A4', 'Wilayah');
		$sheet->setCellValue('A5', 'Kota');
		
		$sheet->setCellValue('B2', date("F Y", strtotime($dp1)));
		$sheet->setCellValue('B3', $divisi);
		$sheet->setCellValue('B4', $wilayah);
		$sheet->setCellValue('B5', $kota);
		$sheet->getStyle('B2:B5')->getFont()->setBold(true);
		
		$currcol = 1;
		$currrow = 5;
		
		$styleArray = [
		'borders' => [
		'allBorders' => [
		'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
		],
		],
		];
		$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
		$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
		
		foreach ($data as $kategori=>$divisis) {
			
			$currcol = 1;
			$currrow +=2;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KATEGORI');
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $kategori);
			$sheet->getStyle('B'.$currrow)->getFont()->setBold(true);
			$sheet->getStyle('A'.$currrow.':B'.$currrow)->getFont()->setSize(12);
			
			$currcol = 1;
			$currrow +=1;
			$startrow = $currrow;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Divisi');
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$sheet->getColumnDimension('A')->setWidth(20);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Merk');
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol , $currrow+1);
			$sheet->getColumnDimension('B')->setWidth(20);
			$currcol += 1;
			
			$sheet->getStyle('A'.($currrow).':A'.($currrow+1))->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('B'.($currrow).':B'.($currrow+1))->getAlignment()->setHorizontal($alignment_center);
			
			
			foreach ($header as $hd){
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setHorizontal($alignment_center);
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $hd);
				$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+4 , $currrow);
				$currcol += 5;
			}
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getAlignment()->setHorizontal($alignment_center);
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
			$sheet->mergeCellsByColumnAndRow($currcol, $currrow, $currcol+4 , $currrow);
			$sheet->getStyle('B'.($currrow).':B'.($currrow+1))->getAlignment()->setHorizontal($alignment_center);
			$currcol += 5;
			
			$currrow += 1;
			$currcol = 3;
			foreach ($header as $hd){
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Jual');
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_jual);
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'ReturBagus');
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_b);
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'ReturCacat');
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_c);
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Disc');
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_biaya);
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
				$currcol += 1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'OmzetNetto');
				$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_omzet);
				$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
				$currcol += 1;
			}
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Jual');
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_jual);
			$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'ReturBagus');
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_b);
			$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'ReturCacat');
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_c);
			$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Disc');
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_biaya);
			$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'OmzetNetto');
			$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_omzet);
			$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($currcol-1))->setWidth(20);
			$currcol += 1;
			$max_col = $currcol-1;
			
			$sheet->getStyle('C'.($currrow).':'.PHPExcel_Cell::stringFromColumnIndex($max_col-1).$currrow)->getAlignment()->setHorizontal($alignment_right);
			$sheet->getStyle('A'.($currrow-1).':'.PHPExcel_Cell::stringFromColumnIndex($max_col-1).$currrow)->getFont()->setBold(true);
			$sheet->getStyle('A'.($currrow-1).':'.PHPExcel_Cell::stringFromColumnIndex($max_col-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('f0e68c');
			
			foreach ($divisis as $divisi=>$merks) {
				foreach ($merks as $merk=>$kotas) {
					
					$currcol = 1;
					$currrow +=1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $divisi);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $merk);
					$currcol += 1;
					
					$jum_jual = 0;
					$jum_retur_b = 0;
					$jum_retur_c = 0;
					$jum_biaya = 0;
					$jum_omzet = 0;
					
					foreach ($header as $hd){
						$ada = 0;
						foreach ($kotas as $kota=>$dt) {
							if($kota==$hd) {
								$ada = 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt['jual']);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_jual);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt['retur_b']);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_b);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt['retur_c']);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_c);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt['biaya']);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_biaya);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$currcol += 1;
								$sheet->setCellValueByColumnAndRow($currcol, $currrow, $dt['omzet']);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_omzet);
								$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
								$currcol += 1;
								
								$jum_jual += ($dt['jual']);
								$jum_retur_b += ($dt['retur_b']);
								$jum_retur_c += ($dt['retur_c']);
								$jum_biaya += ($dt['biaya']);
								$jum_omzet += ($dt['omzet']);
							}
						}
						if($ada==0){
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_jual);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_b);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_c);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_biaya);
							$currcol += 1;
							$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_omzet);
							$currcol += 1;
						}
						
					}
					
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jum_jual);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_jual);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jum_retur_b);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_b);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jum_retur_c);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_retur_c);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jum_biaya);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_biaya);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $jum_omzet);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_omzet);
					$sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($currcol-1).$currrow)->getNumberFormat()->setFormatCode('#,##0');
					$currcol += 1;
					
				}
			}
			$sheet ->getStyle('A'.$startrow.':'.PHPExcel_Cell::stringFromColumnIndex($max_col-1).$currrow)->applyFromArray($styleArray);
			
		}
		$sheet->setSelectedCell('A1');
		
		$sheet->freezePane('C1');
		
		$filename='LaporanOmztPerKota['.date("FY", strtotime($dp1)).']';
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
			


	public function ReportOmzetNettoDealerDivisi()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN OMZET NETTO DEALER DIVISI";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN OMZET NETTO DEALER DIVISI ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$URL = $this->API_URL."/index.php/ReportOmzet/ReportOmzetNettoDealerDivisi?api=APITES";
		// echo $URL;die;
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			// CURLOPT_POST => 1,
			// CURLOPT_POSTFIELDS => http_build_query($data),
		));

		$result = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		
		if($httpcode!=200){
			// ActivityLog Update SUCCESS
			$params['Remarks']="ERROR";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			die('Halaman webAPI sedang tidak bisa diakses! HTTPCode:'.$httpcode);
		}
		else{
			$data = json_decode($result, true);
			$data['title'] = 'REPORT OMZET | REPORT OMZET NETTO DEALER DIVISI';

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('ReportOmzetNettoDealerDivisiView',$data);
		}
	}
	
	public function ProsesReportOmzetNettoDealerDivisi()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN OMZET NETTO DEALER DIVISI";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN OMZET NETTO DEALER DIVISI ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$post['laporan'] = $this->input->post('laporan');
		$post['tgl_awal'] = $this->input->post('dp1');
		$post['tgl_akhir'] = $this->input->post('dp2');
		$post['wilayah'] = ($this->input->post('wilayah')=='ALL') ? '' : $this->input->post('wilayah');
		$post['divisi'] = ($this->input->post('divisi')=='ALL') ? '' : $this->input->post('divisi');
		$post['partner_type'] = ($this->input->post('partner_type')=='ALL') ? '' : $this->input->post('partner_type');
		$post['tipe_faktur'] = ($this->input->post('tipe_faktur')=='SEMUA') ? '' : $this->input->post('tipe_faktur');
		$post['kategori_brg'] = $this->input->post('kategori_brg');
		// $post['x'] = ($this->input->post('x')!=null)?'Y':'N';
		$post['x'] = 'Y';
		$this->excel_flag = ($this->input->post('excel')!=null)?1:0;
		// echo json_encode($post);
		
		$URL = $this->API_URL."/index.php/ReportOmzet/".$post['laporan']."?api=APITES";
		// echo $URL;die;
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => http_build_query($post),
		));

		$result = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $result;die;
		
		if($httpcode!=200){

			// ActivityLog Update FAILED
			$params['Remarks']="FAILED - Data Tidak Ditemukan";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			
			die('Halaman webAPI sedang tidak bisa diakses! HTTPCode:'.$httpcode);
		}
		else{
			$data = json_decode($result);
			switch ($post['laporan']) {
				case 'LaporanDealerPerDivisi':
					$this->PreviewLaporanDealerPerDivisi($post,$data,$params);
					break;
				case 'LaporanDivisiPerDealer':
					$this->PreviewLaporanDivisiPerDealer($post,$data,$params);
					break;
				case 'LaporanDealerPerAlmKirimPerDivisi':
					$this->PreviewLaporanDealerPerAlmKirimPerDivisi($post,$data,$params);
					break;
				case 'LaporanDealerPerKotaPerAlmKirimPerDivisi':
					$this->PreviewLaporanDealerPerKotaPerAlmKirimPerDivisi($post,$data,$params);
					break;
				case 'LaporanKotaPerDivisi':
					$this->PreviewLaporanKotaPerDivisi($post,$data,$params);
					break;
				case 'LaporanDivisiPerKota':
					$this->PreviewLaporanDivisiPerKota($post,$data,$params);
					break;

			}
		}
	}
	
	public function PreviewLaporanDealerPerDivisi($post,$data,$params)
	{			

		$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
		$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
		$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
		$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
		
		$warna_jatuhtempo 	= '909090';
		$warna_wilayah	= 'A9A9A9';
		$warna_toko		= 'C0C0C0';
		$warna_total	= 'D3D3D3';
		
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);
		
		$nama_laporan = $this->laporan[$post['laporan']];
		
		switch ($post['kategori_brg']) {
			case 'P':
				$kategori = 'PRODUCT';
				break;
			case 'S':
				$kategori = 'SPAREPART';
				break;
			default:
				$kategori = 'PRODUCT & SPAREPART';
				break;
		}
		
		$html = "<html>";
		$html .= "<head>";
		$html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
		
		$html .= "<style>
		*, h2{margin:0}
		body{font-family:'Calibri',Arial;}
		.table{padding:0;margin:0;border-collapse:collapse;width;100%}
		.table td, .table th { border:0.5px solid #555; padding:2px!important; }
		.td-center { text-align: center; }
		.td-right { text-align:right;}
		.td-bold { font-weight:bold}
		</style>";
		
		$html.= "</head>";
		$html.= "<body>";
		$html.= "<div id='div_header' style='margin-bottom:10px;text-align:center'>";
		$html.= "<div><h2>".$nama_laporan."</h2></div>";
		$html.= "<center>
		<big>".date('d-M-Y', strtotime($post['tgl_awal']))." s/d ".date('d-M-Y', strtotime($post['tgl_akhir']))."</big><br>
		<big>".$kategori."</big><br>
		<big>PRINT DATE : ".date('d-M-Y H:i:s')."</big>
		</center>
		</div>";
		
		if($this->excel_flag == 1){
			$sheet->setTitle(substr($nama_laporan, 0, 31));
			$sheet->getStyle('A1')->getFont()->setSize(12);
			
			$sheet->setCellValue('A1', $nama_laporan);
			$sheet->setCellValue('A2', date('d-M-Y', strtotime($post['tgl_awal']))." s/d ".date('d-M-Y', strtotime($post['tgl_akhir'])));
			$sheet->setCellValue('A3', $kategori);
			$sheet->setCellValue('A4', 'PRINT DATE : '.date('d-M-Y H:i:s'));
			
			$sheet->mergeCells('A1:G1');
			$sheet->mergeCells('A2:G2');
			$sheet->mergeCells('A3:G3');
			$sheet->mergeCells('A4:G4');
			
			$sheet->getStyle("A1:A3")->getFont()->setBold(true);
			$sheet->getStyle('A1:A3')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A4')->getAlignment()->setHorizontal($alignment_right);
		}
		
		$html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
		$html.= "<div style='clear:both'></div>";
		$currow = 4;
		
		$grandtotal_jual = 0;
		$grandtotal_rb = 0;
		$grandtotal_rc = 0;
		$grandtotal_biaya = 0;
		$grandtotal_omzet = 0;

		foreach($data as $partnertype => $wilayahx){	

			$total_partnertype_jual = 0;
			$total_partnertype_rb = 0;
			$total_partnertype_rc = 0;
			$total_partnertype_biaya = 0;
			$total_partnertype_omzet = 0;

			$html.='<h3>PARTNER TYPE : '.$partnertype.'</h3>';
			if($this->excel_flag == 1){
				$currow++;
				$curcol = 1;
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'PARTNER TYPE : '.$partnertype);
				$sheet->getStyle("A".$currow)->getFont()->setBold(true);
				$sheet->mergeCells("A".$currow.":G".$currow);
			}

			foreach($wilayahx as $wilayah => $dealer){		
				$html.='<h3>WILAYAH : '.$wilayah.'</h3>';
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'WILAYAH : '.$wilayah);
					$sheet->getStyle("A".$currow)->getFont()->setBold(true);
					$sheet->mergeCells("A".$currow.":G".$currow);
				}
				
				$totalwilayah_jual = 0;
				$totalwilayah_rb = 0;
				$totalwilayah_rc = 0;
				$totalwilayah_biaya = 0;
				$totalwilayah_omzet = 0;
				foreach($dealer as $nama_dealer => $data){
					$html.='<b>NAMA TOKO : '.$nama_dealer.'</b><br>';
					if($this->excel_flag == 1){
						$currow++;
						$curcol = 1;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NAMA TOKO : '.$nama_dealer);
						$sheet->getStyle("A".$currow)->getFont()->setBold(true);
						$sheet->mergeCells("A".$currow.":G".$currow);
					}
					
					$html.='<table class="table" style="width:100%">';
					$html.='<tr style="background:#'.$warna_total.'">';
					$html.='<th width="5%">No</th>';
					$html.='<th width="30%">DIVISI</th>';
					$html.='<th width="13%">TOTAL JUAL</th>';
					$html.='<th width="13%">TOTAL RB</th>';
					$html.='<th width="13%">TOTAL RC</th>';
					$html.='<th width="13%">TOTAL BIAYA</th>';
					$html.='<th width="13%">OMZET NETTO</th>';
					$html.='</tr>';
					
					if($this->excel_flag == 1){
						$curcol = 1;
						$currow++;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO');
						$sheet->getColumnDimension('A')->setWidth(5);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'DIVISI');
						$sheet->getColumnDimension('B')->setWidth(30);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL JUAL');
						$sheet->getColumnDimension('C')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL RB');
						$sheet->getColumnDimension('D')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL RC');
						$sheet->getColumnDimension('E')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL BIAYA');
						$sheet->getColumnDimension('F')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'OMZET NETTO');
						$sheet->getColumnDimension('G')->setWidth(20);
						$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
						$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
					}
					$total_jual = 0;
					$total_rb = 0;
					$total_rc = 0;
					$total_biaya = 0;
					$total_omzet = 0;
					$no = 0;
					$startrow = $currow;
					$kd_plg = '';
					foreach($data as $row){
						$kd_plg = $row->Kd_Plg;
						$no++;
						$html.='<tr>';
						$html.='<td class="td-center">'.$no.'</td>';
						$html.='<td class="">'.$row->Divisi.'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_Jual).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_RB).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_RC).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_Disc).'</td>';
						$html.='<td class="td-right">'.number_format($row->Omzet_Netto).'</td>';
						$html.='</tr>';
						
						if($this->excel_flag == 1){
							$currow++;
							$curcol = 1;
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $no);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Divisi);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_Jual);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_RB);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_RC);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_Disc);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Omzet_Netto);
							$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');	
						}
						$total_jual += $row->Total_Jual;
						$total_rb += $row->Total_RB;
						$total_rc += $row->Total_RC;
						$total_biaya += $row->Total_Disc;
						$total_omzet += $row->Omzet_Netto;
					}
					
					$html.='<tr style="background:#'.$warna_total.'">';
					$html.='<td class="td-bold" colspan="2">'.$kd_plg.'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_jual).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_rb).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_rc).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_biaya).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_omzet).'</td>';

					$html.='</tr>';
					$html.='</table><br>';
					
					if($this->excel_flag == 1){
						$curcol = 1;
						$currow++;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO');
						$sheet->getColumnDimension('A')->setWidth(5);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'DIVISI');
						$sheet->getColumnDimension('B')->setWidth(30);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL JUAL');
						$sheet->getColumnDimension('C')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL RB');
						$sheet->getColumnDimension('D')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL RC');
						$sheet->getColumnDimension('E')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL BIAYA');
						$sheet->getColumnDimension('F')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'OMZET NETTO');
						$sheet->getColumnDimension('G')->setWidth(20);
						$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
						$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
					}
					$total_jual = 0;
					$total_rb = 0;
					$total_rc = 0;
					$total_biaya = 0;
					$total_omzet = 0;
					$no = 0;
					$startrow = $currow;
					$kd_plg = '';
					foreach($data as $row){
						$kd_plg = $row->Kd_Plg;
						$no++;
						$html.='<tr>';
						$html.='<td class="td-center">'.$no.'</td>';
						$html.='<td class="">'.$row->Divisi.'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_Jual).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_RB).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_RC).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_Disc).'</td>';
						$html.='<td class="td-right">'.number_format($row->Omzet_Netto).'</td>';
						$html.='</tr>';
						
						if($this->excel_flag == 1){
							$currow++;
							$curcol = 1;
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $no);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Divisi);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_Jual);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_RB);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_RC);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_Disc);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Omzet_Netto);
							$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');	
						}
						$total_jual += $row->Total_Jual;
						$total_rb += $row->Total_RB;
						$total_rc += $row->Total_RC;
						$total_biaya += $row->Total_Disc;
						$total_omzet += $row->Omzet_Netto;
					}
					
					$html.='<tr style="background:#'.$warna_total.'">';
					$html.='<td class="td-bold" colspan="2">'.$kd_plg.'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_jual).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_rb).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_rc).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_biaya).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_omzet).'</td>';
					$html.='</tr>';
					$html.='</table><br>';
					
					if($this->excel_flag == 1){
						$currow++;
						$curcol = 1;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $kd_plg);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_jual);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_rb);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_rc);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_biaya);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_omzet);
						$sheet->mergeCells("A".$currow.":B".$currow);
						$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
						$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
						$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
						
						$styleArray = [
							'borders' => [
								'allBorders' => [
									'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
								],
							],
						];
						$sheet ->getStyle('A'.$startrow.':G'.$currow)->applyFromArray($styleArray);
						$currow++;
					}
					$totalwilayah_jual += $total_jual;
					$totalwilayah_rb += $total_rb;
					$totalwilayah_rc += $total_rc;
					$totalwilayah_biaya += $total_biaya;
					$totalwilayah_omzet += $total_omzet;
				}
				
				$html.='<table class="table" style="width:100%">';
				$html.='<tr style="background:#'.$warna_toko.'">';
				$html.='<td width="35%" class="td-bold" colspan="2">TOTAL WILAYAH '.$wilayah.'</td>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_jual).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_rb).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_rc).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_biaya).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_omzet).'</th>';
				$html.='</tr>';
				$html.='</table><br>';
				
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL WILAYAH '.$wilayah);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_jual);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_rb);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_rc);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_biaya);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_omzet);
					$sheet->mergeCells("A".$currow.":B".$currow);
					// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_right);
					$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
					$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
					$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
					$currow++;
				}
				
				$grandtotal_jual += $totalwilayah_jual;
				$grandtotal_rb += $totalwilayah_rb;
				$grandtotal_rc += $totalwilayah_rc;
				$grandtotal_biaya += $totalwilayah_biaya;
				$grandtotal_omzet += $totalwilayah_omzet;

				$total_partnertype_jual += $totalwilayah_jual;
				$total_partnertype_rb += $totalwilayah_rb;
				$total_partnertype_rc += $totalwilayah_rc;
				$total_partnertype_biaya += $totalwilayah_biaya;
				$total_partnertype_omzet += $totalwilayah_omzet;
			}

			$html.='<table class="table" style="width:100%">';
			$html.='<tr style="background:#'.$warna_toko.'">';
			$html.='<td width="35%" class="td-bold" colspan="2">TOTAL PARTNER TYPE '.$partnertype.'</td>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_jual).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_rb).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_rc).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_biaya).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_omzet).'</th>';
			$html.='</tr>';
			$html.='</table><br>';

			if($this->excel_flag == 1){
				$currow++;
				$curcol = 1;
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL PARTNER TYPE '.$partnertype);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_jual);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_rb);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_rc);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_biaya);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_omzet);
				$sheet->mergeCells("A".$currow.":B".$currow);
				$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
				$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
				$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
				$currow++;
			}

		}
			
		$html.='<table class="table" style="width:100%">';
		$html.='<tr style="background:#'.$warna_wilayah.'">';
		$html.='<td width="35%" class="td-bold" colspan="2">GRAND TOTAL</td>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_jual).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_rb).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_rc).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_biaya).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_omzet).'</th>';
		$html.='</tr>';
		$html.='</table>';
		
		$html.= "</div>";
		$html.= "</body></html>";
		
		if($this->excel_flag == 1){
			$currow++;
			$curcol = 1;
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'GRAND TOTAL');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_jual);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_rb);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_rc);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_biaya);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_omzet);
			$sheet->mergeCells("A".$currow.":B".$currow);
			$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
			$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_wilayah);
			$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
		}
		
		if($this->excel_flag == 1){
			// $sheet->freezePane('A6');
			$sheet->setSelectedCell('A1');
			$filename=$nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
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
		else{
			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'orientation' => 'P'
			));
			$mpdf->defaultfooterline = 1;
			$mpdf->SetFooter('
			<table width="100%">
				<tr>
					<td>'.date('d-M-Y H:i:s').'</td>
					<td class="td-right">Halaman {PAGENO} / {nbpg}</td>
				</tr>
			</table>');
			$mpdf->WriteHTML($html);
			$mpdf->Output();

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

		}
	}
	
	public function PreviewLaporanDivisiPerDealer($post,$data,$params)
	{			
		$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
		$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
		$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
		$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
		
		$warna_jatuhtempo 	= '909090';
		$warna_wilayah	= 'A9A9A9';
		$warna_toko		= 'C0C0C0';
		$warna_total	= 'D3D3D3';
		
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);
		
		$nama_laporan = $this->laporan[$post['laporan']];
		
		switch ($post['kategori_brg']) {
			case 'P':
				$kategori = 'PRODUCT';
				break;
			case 'S':
				$kategori = 'SPAREPART';
				break;
			default:
				$kategori = 'PRODUCT & SPAREPART';
				break;
		}
		
		$html = "<html>";
		$html .= "<head>";
		$html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
		
		$html .= "<style>
		*, h2{margin:0}
		body{font-family:'Calibri',Arial;}
		.table{padding:0;margin:0;border-collapse:collapse;width;100%}
		.table td, .table th { border:0.5px solid #555; padding:2px!important; }
		.td-center { text-align: center; }
		.td-right { text-align:right;}
		.td-bold { font-weight:bold}
		</style>";
		
		$html.= "</head>";
		$html.= "<body>";
		$html.= "<div id='div_header' style='margin-bottom:10px;text-align:center'>";
		$html.= "<div><h2>".$nama_laporan."</h2></div>";
		$html.= "<center>
		<big>".date('d-M-Y', strtotime($post['tgl_awal']))." s/d ".date('d-M-Y', strtotime($post['tgl_akhir']))."</big><br>
		<big>".$kategori."</big><br>
		<big>PRINT DATE : ".date('d-M-Y H:i:s')."</big>
		</center>
		</div>";
		
		if($this->excel_flag == 1){
			$sheet->setTitle(substr($nama_laporan, 0, 31));
			$sheet->getStyle('A1')->getFont()->setSize(12);
			
			$sheet->setCellValue('A1', $nama_laporan);
			$sheet->setCellValue('A2', date('d-M-Y', strtotime($post['tgl_awal']))." s/d ".date('d-M-Y', strtotime($post['tgl_akhir'])));
			$sheet->setCellValue('A3', $kategori);
			$sheet->setCellValue('A4', 'PRINT DATE : '.date('d-M-Y H:i:s'));
			
			$sheet->mergeCells('A1:G1');
			$sheet->mergeCells('A2:G2');
			$sheet->mergeCells('A3:G3');
			$sheet->mergeCells('A4:G4');
			
			$sheet->getStyle("A1:A3")->getFont()->setBold(true);
			$sheet->getStyle('A1:A3')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A4')->getAlignment()->setHorizontal($alignment_right);
		}
		
		$html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
		$html.= "<div style='clear:both'></div>";
		$currow = 4;
		
		$grandtotal_jual = 0;
		$grandtotal_rb = 0;
		$grandtotal_rc = 0;
		$grandtotal_biaya = 0;
		$grandtotal_omzet = 0;
		
		foreach($data as $partnertype => $wilayahx){	

			$total_partnertype_jual = 0;
			$total_partnertype_rb = 0;
			$total_partnertype_rc = 0;
			$total_partnertype_biaya = 0;
			$total_partnertype_omzet = 0;

			$html.='<h3>PARTNER TYPE : '.$partnertype.'</h3>';
			if($this->excel_flag == 1){
				$currow++;
				$curcol = 1;
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'PARTNER TYPE : '.$partnertype);
				$sheet->getStyle("A".$currow)->getFont()->setBold(true);
				$sheet->mergeCells("A".$currow.":G".$currow);
			}

			foreach($wilayahx as $wilayah => $divisi){			
				$html.='<h3>WILAYAH : '.$wilayah.'</h3>';
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'WILAYAH : '.$wilayah);
					$sheet->getStyle("A".$currow)->getFont()->setBold(true);
					$sheet->mergeCells("A".$currow.":G".$currow);
				}
				
				$totalwilayah_jual = 0;
				$totalwilayah_rb = 0;
				$totalwilayah_rc = 0;
				$totalwilayah_biaya = 0;
				$totalwilayah_omzet = 0;
				
				foreach($divisi as $nama_divisi => $data){
					$html.='<b>DIVISI : '.$nama_divisi.'</b><br>';
					if($this->excel_flag == 1){
						$currow++;
						$curcol = 1;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'DIVISI : '.$nama_divisi);
						$sheet->getStyle("A".$currow)->getFont()->setBold(true);
						$sheet->mergeCells("A".$currow.":G".$currow);
					}
					
					$html.='<table class="table" style="width:100%">';
					$html.='<tr style="background:#'.$warna_total.'">';
					$html.='<th width="5%">No</th>';
					$html.='<th width="30%">NAMA TOKO</th>';
					$html.='<th width="13%">TOTAL JUAL</th>';
					$html.='<th width="13%">TOTAL RB</th>';
					$html.='<th width="13%">TOTAL RC</th>';
					$html.='<th width="13%">TOTAL BIAYA</th>';
					$html.='<th width="13%">OMZET NETTO</th>';
					$html.='</tr>';
					
					if($this->excel_flag == 1){
						$curcol = 1;
						$currow++;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO');
						$sheet->getColumnDimension('A')->setWidth(5);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NAMA TOKO');
						$sheet->getColumnDimension('B')->setWidth(30);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL JUAL');
						$sheet->getColumnDimension('C')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL RB');
						$sheet->getColumnDimension('D')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL RC');
						$sheet->getColumnDimension('E')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL BIAYA');
						$sheet->getColumnDimension('F')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'OMZET NETTO');
						$sheet->getColumnDimension('G')->setWidth(20);
						$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
						$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
					}
					$total_jual = 0;
					$total_rb = 0;
					$total_rc = 0;
					$total_biaya = 0;
					$total_omzet = 0;
					$no = 0;
					$startrow = $currow;
					foreach($data as $row){
						$no++;
						$html.='<tr>';
						$html.='<td class="td-center">'.$no.'</td>';
						$html.='<td class="">'.$row->Nm_Plg.'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_Jual).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_RB).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_RC).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_Disc).'</td>';
						$html.='<td class="td-right">'.number_format($row->Omzet_Netto).'</td>';
						$html.='</tr>';
						
						if($this->excel_flag == 1){
							$currow++;
							$curcol = 1;
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $no);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Nm_Plg);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_Jual);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_RB);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_RC);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_Disc);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Omzet_Netto);
							$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');	
						}
						$total_jual += $row->Total_Jual;
						$total_rb += $row->Total_RB;
						$total_rc += $row->Total_RC;
						$total_biaya += $row->Total_Disc;
						$total_omzet += $row->Omzet_Netto;
					}
					
					$html.='<tr style="background:#'.$warna_total.'">';
					$html.='<td class="td-bold" colspan="2">'.$nama_divisi.'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_jual).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_rb).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_rc).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_biaya).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_omzet).'</td>';
					$html.='</tr>';
					$html.='</table><br>';
					
					if($this->excel_flag == 1){
						$curcol = 1;
						$currow++;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO');
						$sheet->getColumnDimension('A')->setWidth(5);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'DIVISI');
						$sheet->getColumnDimension('B')->setWidth(30);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL JUAL');
						$sheet->getColumnDimension('C')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL RB');
						$sheet->getColumnDimension('D')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL RC');
						$sheet->getColumnDimension('E')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL BIAYA');
						$sheet->getColumnDimension('F')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'OMZET NETTO');
						$sheet->getColumnDimension('G')->setWidth(20);
						$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
						$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
					}
					$total_jual = 0;
					$total_rb = 0;
					$total_rc = 0;
					$total_biaya = 0;
					$total_omzet = 0;
					$no = 0;
					$startrow = $currow;
					foreach($data as $row){
						$no++;
						$html.='<tr>';
						$html.='<td class="td-center">'.$no.'</td>';
						$html.='<td class="">'.$row->Nm_Plg.'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_Jual).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_RB).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_RC).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_Disc).'</td>';
						$html.='<td class="td-right">'.number_format($row->Omzet_Netto).'</td>';
						$html.='</tr>';
						
						if($this->excel_flag == 1){
							$currow++;
							$curcol = 1;
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $no);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Nm_Plg);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_Jual);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_RB);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_RC);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_Disc);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Omzet_Netto);
							$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');	
						}
						$total_jual += $row->Total_Jual;
						$total_rb += $row->Total_RB;
						$total_rc += $row->Total_RC;
						$total_biaya += $row->Total_Disc;
						$total_omzet += $row->Omzet_Netto;
					}
					
					$html.='<tr style="background:#'.$warna_total.'">';
					$html.='<td class="td-bold" colspan="2">'.$nama_divisi.'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_jual).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_rb).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_rc).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_biaya).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_omzet).'</td>';
					$html.='</tr>';
					$html.='</table><br>';
					
					if($this->excel_flag == 1){
						$currow++;
						$curcol = 1;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $nama_divisi);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_jual);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_rb);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_rc);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_biaya);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_omzet);
						$sheet->mergeCells("A".$currow.":B".$currow);
						// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_right);
						$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
						$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
						$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
						
						$styleArray = [
							'borders' => [
								'allBorders' => [
									'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
								],
							],
						];
						$sheet ->getStyle('A'.$startrow.':G'.$currow)->applyFromArray($styleArray);
						$currow++;
					}
					
					$totalwilayah_jual += $total_jual;
					$totalwilayah_rb += $total_rb;
					$totalwilayah_rc += $total_rc;
					$totalwilayah_biaya += $total_biaya;
					$totalwilayah_omzet += $total_omzet;
					
				}
				
				$html.='<table class="table" style="width:100%">';
				$html.='<tr style="background:#'.$warna_toko.'">';
				$html.='<td width="35%" class="td-bold" colspan="2">TOTAL WILAYAH '.$wilayah.'</td>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_jual).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_rb).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_rc).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_biaya).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_omzet).'</th>';
				$html.='</tr>';
				$html.='</table><br>';
				
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL WILAYAH '.$wilayah);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_jual);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_rb);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_rc);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_biaya);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_omzet);
					$sheet->mergeCells("A".$currow.":B".$currow);
					// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_right);
					$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
					$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
					$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
					$currow++;
				}
				$grandtotal_jual += $totalwilayah_jual;
				$grandtotal_rb += $totalwilayah_rb;
				$grandtotal_rc += $totalwilayah_rc;
				$grandtotal_biaya += $totalwilayah_biaya;
				$grandtotal_omzet += $totalwilayah_omzet;

				$total_partnertype_jual += $totalwilayah_jual;
				$total_partnertype_rb += $totalwilayah_rb;
				$total_partnertype_rc += $totalwilayah_rc;
				$total_partnertype_biaya += $totalwilayah_biaya;
				$total_partnertype_omzet += $totalwilayah_omzet;
			}

			$html.='<table class="table" style="width:100%">';
			$html.='<tr style="background:#'.$warna_toko.'">';
			$html.='<td width="35%" class="td-bold" colspan="2">TOTAL PARTNER TYPE '.$partnertype.'</td>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_jual).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_rb).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_rc).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_biaya).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_omzet).'</th>';
			$html.='</tr>';
			$html.='</table><br>';

			if($this->excel_flag == 1){
				$currow++;
				$curcol = 1;
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL PARTNER TYPE '.$partnertype);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_jual);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_rb);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_rc);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_biaya);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_omzet);
				$sheet->mergeCells("A".$currow.":B".$currow);
				$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
				$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
				$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
				$currow++;
			}
		}
			
		$html.='<table class="table" style="width:100%">';
		$html.='<tr style="background:#'.$warna_wilayah.'">';
		$html.='<td width="35%" class="td-bold" colspan="2">GRAND TOTAL</td>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_jual).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_rb).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_rc).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_biaya).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_omzet).'</th>';
		$html.='</tr>';
		$html.='</table>';
		
		$html.= "</div>";
		$html.= "</body></html>";
		
		if($this->excel_flag == 1){
			$currow++;
			$curcol = 1;
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'GRAND TOTAL');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_jual);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_rb);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_rc);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_biaya);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_omzet);
			$sheet->mergeCells("A".$currow.":B".$currow);
			$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
			$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_wilayah);
			$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
		}
		
		if($this->excel_flag == 1){
			$sheet->setSelectedCell('A1');
			$filename=$nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
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
		else{
			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'orientation' => 'P'
			));
			$mpdf->defaultfooterline = 1;
			$mpdf->SetFooter('
			<table width="100%">
				<tr>
					<td>'.date('d-M-Y H:i:s').'</td>
					<td class="td-right">Halaman {PAGENO} / {nbpg}</td>
				</tr>
			</table>');
			$mpdf->WriteHTML($html);
			$mpdf->Output();

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

		}
	}

	public function PreviewLaporanDealerPerAlmKirimPerDivisi($post,$data,$params){
		$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
		$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
		$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
		$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
		
		$warna_jatuhtempo 	= '909090';
		$warna_wilayah	= 'A9A9A9';
		$warna_toko		= 'C0C0C0';
		$warna_total	= 'D3D3D3';
		
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);
		
		$nama_laporan = $this->laporan[$post['laporan']];
		
		switch ($post['kategori_brg']) {
			case 'P':
				$kategori = 'PRODUCT';
				break;
			case 'S':
				$kategori = 'SPAREPART';
				break;
			default:
				$kategori = 'PRODUCT & SPAREPART';
				break;
		}
		
		$html = "<html>";
		$html .= "<head>";
		$html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
		
		$html .= "<style>
		*, h2{margin:0}
		body{font-family:'Calibri',Arial;}
		.table{padding:0;margin:0;border-collapse:collapse;width;100%}
		.table td, .table th { border:0.5px solid #555; padding:2px!important; }
		.td-center { text-align: center; }
		.td-right { text-align:right;}
		.td-bold { font-weight:bold}
		</style>";
		
		$html.= "</head>";
		$html.= "<body>";
		$html.= "<div id='div_header' style='margin-bottom:10px;text-align:center'>";
		$html.= "<div><h2>".$nama_laporan."</h2></div>";
		$html.= "<center>
		<big>".date('d-M-Y', strtotime($post['tgl_awal']))." s/d ".date('d-M-Y', strtotime($post['tgl_akhir']))."</big><br>
		<big>".$kategori."</big><br>
		<big>PRINT DATE : ".date('d-M-Y H:i:s')."</big>
		</center>
		</div>";
		
		if($this->excel_flag == 1){
			$sheet->setTitle(substr($nama_laporan, 0, 31));
			$sheet->getStyle('A1')->getFont()->setSize(12);
			
			$sheet->setCellValue('A1', $nama_laporan);
			$sheet->setCellValue('A2', date('d-M-Y', strtotime($post['tgl_awal']))." s/d ".date('d-M-Y', strtotime($post['tgl_akhir'])));
			$sheet->setCellValue('A3', $kategori);
			$sheet->setCellValue('A4', 'PRINT DATE : '.date('d-M-Y H:i:s'));
			
			$sheet->mergeCells('A1:G1');
			$sheet->mergeCells('A2:G2');
			$sheet->mergeCells('A3:G3');
			$sheet->mergeCells('A4:G4');
			
			$sheet->getStyle("A1:A3")->getFont()->setBold(true);
			$sheet->getStyle('A1:A3')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A4')->getAlignment()->setHorizontal($alignment_right);
		}
		
		$html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
		$html.= "<div style='clear:both'></div>";
		$currow = 4;
		
		$grandtotal_jual = 0;
		$grandtotal_rb = 0;
		$grandtotal_rc = 0;
		$grandtotal_biaya = 0;
		$grandtotal_omzet = 0;
		
		foreach($data as $partnertype => $xplg){	

			$total_partnertype_jual = 0;
			$total_partnertype_rb = 0;
			$total_partnertype_rc = 0;
			$total_partnertype_biaya = 0;
			$total_partnertype_omzet = 0;

			$html.='<h3>PARTNER TYPE : '.$partnertype.'</h3>';
			if($this->excel_flag == 1){
				$currow++;
				$curcol = 1;
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'PARTNER TYPE : '.$partnertype);
				$sheet->getStyle("A".$currow)->getFont()->setBold(true);
				$sheet->mergeCells("A".$currow.":G".$currow);
			}

			foreach($xplg as $nm_plg => $alm_plg){			
				$html.='<h3>'.$nm_plg.'</h3>';
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $nm_plg);
					$sheet->getStyle("A".$currow)->getFont()->setBold(true);
					$sheet->mergeCells("A".$currow.":G".$currow);
				}
				
				$totalwilayah_jual = 0;
				$totalwilayah_rb = 0;
				$totalwilayah_rc = 0;
				$totalwilayah_biaya = 0;
				$totalwilayah_omzet = 0;
				
				foreach($alm_plg as $almplg => $data){
					$html.=$almplg.'</b><br>';
					if($this->excel_flag == 1){
						$currow++;
						$curcol = 1;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $almplg);
						$sheet->getStyle("A".$currow)->getFont()->setBold(true);
						$sheet->mergeCells("A".$currow.":G".$currow);
					}
					
					$html.='<table class="table" style="width:100%">';
					$html.='<tr style="background:#'.$warna_total.'">';
					$html.='<th width="5%">No</th>';
					$html.='<th width="30%">DIVISI</th>';
					$html.='<th width="13%">TOTAL JUAL</th>';
					$html.='<th width="13%">TOTAL RB</th>';
					$html.='<th width="13%">TOTAL RC</th>';
					$html.='<th width="13%">TOTAL BIAYA</th>';
					$html.='<th width="13%">OMZET NETTO</th>';
					$html.='</tr>';
					
					if($this->excel_flag == 1){
						$curcol = 1;
						$currow++;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO');
						$sheet->getColumnDimension('A')->setWidth(5);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'DIVISI');
						$sheet->getColumnDimension('B')->setWidth(30);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL JUAL');
						$sheet->getColumnDimension('C')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL RB');
						$sheet->getColumnDimension('D')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL RC');
						$sheet->getColumnDimension('E')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL BIAYA');
						$sheet->getColumnDimension('F')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'OMZET NETTO');
						$sheet->getColumnDimension('G')->setWidth(20);
						$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
						$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
					}
					$total_jual = 0;
					$total_rb = 0;
					$total_rc = 0;
					$total_biaya = 0;
					$total_omzet = 0;
					$no = 0;
					$startrow = $currow;
					foreach($data as $row){
						$no++;
						$html.='<tr>';
						$html.='<td class="td-center">'.$no.'</td>';
						$html.='<td class="">'.$row->Divisi.'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_Jual).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_RB).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_RC).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_Disc).'</td>';
						$html.='<td class="td-right">'.number_format($row->Omzet_Netto).'</td>';
						$html.='</tr>';
						
						if($this->excel_flag == 1){
							$currow++;
							$curcol = 1;
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $no);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Divisi);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_Jual);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_RB);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_RC);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_Disc);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Omzet_Netto);
							$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');	
						}
						$total_jual += $row->Total_Jual;
						$total_rb += $row->Total_RB;
						$total_rc += $row->Total_RC;
						$total_biaya += $row->Total_Disc;
						$total_omzet += $row->Omzet_Netto;
					}
					
					$html.='<tr style="background:#'.$warna_total.'">';
					$html.='<td class="td-bold" colspan="2">Total '.$almplg.'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_jual).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_rb).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_rc).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_biaya).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_omzet).'</td>';
					$html.='</tr>';
					$html.='</table><br>';
					
					if($this->excel_flag == 1){
						$currow++;
						$curcol = 1;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $almplg);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_jual);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_rb);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_rc);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_biaya);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_omzet);
						$sheet->mergeCells("A".$currow.":B".$currow);
						// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_right);
						$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
						$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
						$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
						
						$styleArray = [
							'borders' => [
								'allBorders' => [
									'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
								],
							],
						];
						$sheet ->getStyle('A'.$startrow.':G'.$currow)->applyFromArray($styleArray);
						$currow++;
					}
					
					$totalwilayah_jual += $total_jual;
					$totalwilayah_rb += $total_rb;
					$totalwilayah_rc += $total_rc;
					$totalwilayah_biaya += $total_biaya;
					$totalwilayah_omzet += $total_omzet;
					
				}
				
				$html.='<table class="table" style="width:100%">';
				$html.='<tr style="background:#'.$warna_toko.'">';
				$html.='<td width="35%" class="td-bold" colspan="2">'.$nm_plg.'</td>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_jual).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_rb).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_rc).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_biaya).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_omzet).'</th>';
				$html.='</tr>';
				$html.='</table><br>';
				
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $nm_plg);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_jual);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_rb);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_rc);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_biaya);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_omzet);
					$sheet->mergeCells("A".$currow.":B".$currow);
					// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_right);
					$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
					$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
					$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
					$currow++;
				}
				$grandtotal_jual += $totalwilayah_jual;
				$grandtotal_rb += $totalwilayah_rb;
				$grandtotal_rc += $totalwilayah_rc;
				$grandtotal_biaya += $totalwilayah_biaya;
				$grandtotal_omzet += $totalwilayah_omzet;

				$total_partnertype_jual += $totalwilayah_jual;
				$total_partnertype_rb += $totalwilayah_rb;
				$total_partnertype_rc += $totalwilayah_rc;
				$total_partnertype_biaya += $totalwilayah_biaya;
				$total_partnertype_omzet += $totalwilayah_omzet;
			}

			$html.='<table class="table" style="width:100%">';
			$html.='<tr style="background:#'.$warna_toko.'">';
			$html.='<td width="35%" class="td-bold" colspan="2">TOTAL PARTNER TYPE '.$partnertype.'</td>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_jual).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_rb).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_rc).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_biaya).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_omzet).'</th>';
			$html.='</tr>';
			$html.='</table><br>';

			if($this->excel_flag == 1){
				$currow++;
				$curcol = 1;
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL PARTNER TYPE '.$partnertype);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_jual);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_rb);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_rc);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_biaya);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_omzet);
				$sheet->mergeCells("A".$currow.":B".$currow);
				$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
				$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
				$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
				$currow++;
			}
		}
			
		$html.='<table class="table" style="width:100%">';
		$html.='<tr style="background:#'.$warna_wilayah.'">';
		$html.='<td width="35%" class="td-bold" colspan="2">GRAND TOTAL</td>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_jual).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_rb).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_rc).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_biaya).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_omzet).'</th>';
		$html.='</tr>';
		$html.='</table>';
		
		$html.= "</div>";
		$html.= "</body></html>";
		
		if($this->excel_flag == 1){
			$currow++;
			$curcol = 1;
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'GRAND TOTAL');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_jual);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_rb);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_rc);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_biaya);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_omzet);
			$sheet->mergeCells("A".$currow.":B".$currow);
			$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
			$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_wilayah);
			$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
		}
		
		if($this->excel_flag == 1){
			$sheet->setSelectedCell('A1');
			$filename=$nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
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
		else{
			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'orientation' => 'P'
			));
			$mpdf->defaultfooterline = 1;
			$mpdf->SetFooter('
			<table width="100%">
				<tr>
					<td>'.date('d-M-Y H:i:s').'</td>
					<td class="td-right">Halaman {PAGENO} / {nbpg}</td>
				</tr>
			</table>');
			$mpdf->WriteHTML($html);
			$mpdf->Output();

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

		}
	}

	public function PreviewLaporanDealerPerKotaPerAlmKirimPerDivisi($post,$data,$params){
		$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
		$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
		$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
		$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
		
		$warna_jatuhtempo 	= '909090';
		$warna_wilayah	= 'A9A9A9';
		$warna_toko		= 'C0C0C0';
		$warna_total	= 'D3D3D3';
		
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);
		
		$nama_laporan = $this->laporan[$post['laporan']];
		
		switch ($post['kategori_brg']) {
			case 'P':
				$kategori = 'PRODUCT';
				break;
			case 'S':
				$kategori = 'SPAREPART';
				break;
			default:
				$kategori = 'PRODUCT & SPAREPART';
				break;
		}
		
		$html = "<html>";
		$html .= "<head>";
		$html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
		
		$html .= "<style>
		*, h2{margin:0}
		body{font-family:'Calibri',Arial;}
		.table{padding:0;margin:0;border-collapse:collapse;width;100%}
		.table td, .table th { border:0.5px solid #555; padding:2px!important; }
		.td-center { text-align: center; }
		.td-right { text-align:right;}
		.td-bold { font-weight:bold}
		</style>";
		
		$html.= "</head>";
		$html.= "<body>";
		$html.= "<div id='div_header' style='margin-bottom:10px;text-align:center'>";
		$html.= "<div><h2>".$nama_laporan."</h2></div>";
		$html.= "<center>
		<big>".date('d-M-Y', strtotime($post['tgl_awal']))." s/d ".date('d-M-Y', strtotime($post['tgl_akhir']))."</big><br>
		<big>".$kategori."</big><br>
		<big>PRINT DATE : ".date('d-M-Y H:i:s')."</big>
		</center>
		</div>";
		
		if($this->excel_flag == 1){
			$sheet->setTitle(substr($nama_laporan, 0, 31));
			$sheet->getStyle('A1')->getFont()->setSize(12);
			
			$sheet->setCellValue('A1', $nama_laporan);
			$sheet->setCellValue('A2', date('d-M-Y', strtotime($post['tgl_awal']))." s/d ".date('d-M-Y', strtotime($post['tgl_akhir'])));
			$sheet->setCellValue('A3', $kategori);
			$sheet->setCellValue('A4', 'PRINT DATE : '.date('d-M-Y H:i:s'));
			
			$sheet->mergeCells('A1:G1');
			$sheet->mergeCells('A2:G2');
			$sheet->mergeCells('A3:G3');
			$sheet->mergeCells('A4:G4');
			
			$sheet->getStyle("A1:A3")->getFont()->setBold(true);
			$sheet->getStyle('A1:A3')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A4')->getAlignment()->setHorizontal($alignment_right);
		}
		
		$html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
		$html.= "<div style='clear:both'></div>";
		$currow = 4;
		
		$grandtotal_jual = 0;
		$grandtotal_rb = 0;
		$grandtotal_rc = 0;
		$grandtotal_biaya = 0;
		$grandtotal_omzet = 0;
		
		foreach($data as $partnertype => $wilayahx){	

			$total_partnertype_jual = 0;
			$total_partnertype_rb = 0;
			$total_partnertype_rc = 0;
			$total_partnertype_biaya = 0;
			$total_partnertype_omzet = 0;

			$html.='<h3>PARTNER TYPE : '.$partnertype.'</h3>';
			if($this->excel_flag == 1){
				$currow++;
				$curcol = 1;
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'PARTNER TYPE : '.$partnertype);
				$sheet->getStyle("A".$currow)->getFont()->setBold(true);
				$sheet->mergeCells("A".$currow.":G".$currow);
			}

			foreach($wilayahx as $wilayah => $Nm_Plg){			
				$html.='<h3>KOTA : '.$wilayah.'</h3>';
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'KOTA : '.$wilayah);
					$sheet->getStyle("A".$currow)->getFont()->setBold(true);
					$sheet->mergeCells("A".$currow.":G".$currow);
				}
				
				$totalwilayah_jual = 0;
				$totalwilayah_rb = 0;
				$totalwilayah_rc = 0;
				$totalwilayah_biaya = 0;
				$totalwilayah_omzet = 0;
				
				foreach($Nm_Plg as $nama_plg => $almplg){
					$html.=$nama_plg.'</b><br>';
					if($this->excel_flag == 1){
						$currow++;
						$curcol = 1;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $nama_plg);
						$sheet->getStyle("A".$currow)->getFont()->setBold(true);
						$sheet->mergeCells("A".$currow.":G".$currow);
					}

					foreach($almplg as $almplg => $data){
					$html.=$almplg.'</b><br>';
					if($this->excel_flag == 1){
						$currow++;
						$curcol = 1;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $almplg);
						$sheet->getStyle("A".$currow)->getFont()->setBold(true);
						$sheet->mergeCells("A".$currow.":G".$currow);
					}
					
						$html.='<table class="table" style="width:100%">';
						$html.='<tr style="background:#'.$warna_total.'">';
						$html.='<th width="5%">No</th>';
						$html.='<th width="30%">DIVISI</th>';
						$html.='<th width="13%">TOTAL JUAL</th>';
						$html.='<th width="13%">TOTAL RB</th>';
						$html.='<th width="13%">TOTAL RC</th>';
						$html.='<th width="13%">TOTAL BIAYA</th>';
						$html.='<th width="13%">OMZET NETTO</th>';
						$html.='</tr>';
						
						if($this->excel_flag == 1){
							$curcol = 1;
							$currow++;
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO');
							$sheet->getColumnDimension('A')->setWidth(5);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'DIVISI');
							$sheet->getColumnDimension('B')->setWidth(30);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL JUAL');
							$sheet->getColumnDimension('C')->setWidth(20);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL RB');
							$sheet->getColumnDimension('D')->setWidth(20);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL RC');
							$sheet->getColumnDimension('E')->setWidth(20);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL BIAYA');
							$sheet->getColumnDimension('F')->setWidth(20);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'OMZET NETTO');
							$sheet->getColumnDimension('G')->setWidth(20);
							$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
							$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
						}
						$total_jual = 0;
						$total_rb = 0;
						$total_rc = 0;
						$total_biaya = 0;
						$total_omzet = 0;
						$no = 0;
						$startrow = $currow;
						foreach($data as $row){
							$no++;
							$html.='<tr>';
							$html.='<td class="td-center">'.$no.'</td>';
							$html.='<td class="">'.$row->Divisi.'</td>';
							$html.='<td class="td-right">'.number_format($row->Total_Jual).'</td>';
							$html.='<td class="td-right">'.number_format($row->Total_RB).'</td>';
							$html.='<td class="td-right">'.number_format($row->Total_RC).'</td>';
							$html.='<td class="td-right">'.number_format($row->Total_Disc).'</td>';
							$html.='<td class="td-right">'.number_format($row->Omzet_Netto).'</td>';
							$html.='</tr>';
							
							if($this->excel_flag == 1){
								$currow++;
								$curcol = 1;
								$sheet->setCellValueByColumnAndRow($curcol++, $currow, $no);
								$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Divisi);
								$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_Jual);
								$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_RB);
								$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_RC);
								$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_Disc);
								$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Omzet_Netto);
								$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');	
							}
							$total_jual += $row->Total_Jual;
							$total_rb += $row->Total_RB;
							$total_rc += $row->Total_RC;
							$total_biaya += $row->Total_Disc;
							$total_omzet += $row->Omzet_Netto;
						}
						
						$html.='<tr style="background:#'.$warna_total.'">';
						$html.='<td class="td-bold" colspan="2">'.$nama_plg.'</td>';
						$html.='<td class="td-right td-bold">'.number_format($total_jual).'</td>';
						$html.='<td class="td-right td-bold">'.number_format($total_rb).'</td>';
						$html.='<td class="td-right td-bold">'.number_format($total_rc).'</td>';
						$html.='<td class="td-right td-bold">'.number_format($total_biaya).'</td>';
						$html.='<td class="td-right td-bold">'.number_format($total_omzet).'</td>';
						$html.='</tr>';
						$html.='</table><br>';
						
						if($this->excel_flag == 1){
							$currow++;
							$curcol = 1;
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $nama_plg);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_jual);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_rb);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_rc);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_biaya);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_omzet);
							$sheet->mergeCells("A".$currow.":B".$currow);
							// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_right);
							$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
							$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
							$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
							
							$styleArray = [
								'borders' => [
									'allBorders' => [
										'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
									],
								],
							];
							$sheet ->getStyle('A'.$startrow.':G'.$currow)->applyFromArray($styleArray);
							$currow++;
						}
						
						$totalwilayah_jual += $total_jual;
						$totalwilayah_rb += $total_rb;
						$totalwilayah_rc += $total_rc;
						$totalwilayah_biaya += $total_biaya;
						$totalwilayah_omzet += $total_omzet;
						
					}
				}
				
				$html.='<table class="table" style="width:100%">';
				$html.='<tr style="background:#'.$warna_toko.'">';
				$html.='<td width="35%" class="td-bold" colspan="2">'.$wilayah.'</td>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_jual).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_rb).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_rc).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_biaya).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_omzet).'</th>';
				$html.='</tr>';
				$html.='</table><br>';
				
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $wilayah);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_jual);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_rb);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_rc);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_biaya);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_omzet);
					$sheet->mergeCells("A".$currow.":B".$currow);
					// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_right);
					$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
					$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
					$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
					$currow++;
				}
				$grandtotal_jual += $totalwilayah_jual;
				$grandtotal_rb += $totalwilayah_rb;
				$grandtotal_rc += $totalwilayah_rc;
				$grandtotal_biaya += $totalwilayah_biaya;
				$grandtotal_omzet += $totalwilayah_omzet;

				$total_partnertype_jual += $totalwilayah_jual;
				$total_partnertype_rb += $totalwilayah_rb;
				$total_partnertype_rc += $totalwilayah_rc;
				$total_partnertype_biaya += $totalwilayah_biaya;
				$total_partnertype_omzet += $totalwilayah_omzet;
			}

			$html.='<table class="table" style="width:100%">';
			$html.='<tr style="background:#'.$warna_toko.'">';
			$html.='<td width="35%" class="td-bold" colspan="2">TOTAL PARTNER TYPE '.$partnertype.'</td>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_jual).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_rb).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_rc).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_biaya).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_omzet).'</th>';
			$html.='</tr>';
			$html.='</table><br>';

			if($this->excel_flag == 1){
				$currow++;
				$curcol = 1;
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL PARTNER TYPE '.$partnertype);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_jual);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_rb);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_rc);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_biaya);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_omzet);
				$sheet->mergeCells("A".$currow.":B".$currow);
				$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
				$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
				$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
				$currow++;
			}
		}
			
		$html.='<table class="table" style="width:100%">';
		$html.='<tr style="background:#'.$warna_wilayah.'">';
		$html.='<td width="35%" class="td-bold" colspan="2">GRAND TOTAL</td>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_jual).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_rb).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_rc).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_biaya).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_omzet).'</th>';
		$html.='</tr>';
		$html.='</table>';
		
		$html.= "</div>";
		$html.= "</body></html>";
		
		if($this->excel_flag == 1){
			$currow++;
			$curcol = 1;
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'GRAND TOTAL');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_jual);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_rb);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_rc);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_biaya);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_omzet);
			$sheet->mergeCells("A".$currow.":B".$currow);
			$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
			$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_wilayah);
			$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
		}
		
		if($this->excel_flag == 1){
			$sheet->setSelectedCell('A1');
			$filename=$nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
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
		else{
			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'orientation' => 'P'
			));
			$mpdf->defaultfooterline = 1;
			$mpdf->SetFooter('
			<table width="100%">
				<tr>
					<td>'.date('d-M-Y H:i:s').'</td>
					<td class="td-right">Halaman {PAGENO} / {nbpg}</td>
				</tr>
			</table>');
			$mpdf->WriteHTML($html);
			$mpdf->Output();

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

		}
	}

	public function PreviewLaporanKotaPerDivisi($post,$data,$params){
		$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
		$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
		$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
		$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
		
		$warna_jatuhtempo 	= '909090';
		$warna_wilayah	= 'A9A9A9';
		$warna_toko		= 'C0C0C0';
		$warna_total	= 'D3D3D3';
		
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);
		
		$nama_laporan = $this->laporan[$post['laporan']];
		
		switch ($post['kategori_brg']) {
			case 'P':
				$kategori = 'PRODUCT';
				break;
			case 'S':
				$kategori = 'SPAREPART';
				break;
			default:
				$kategori = 'PRODUCT & SPAREPART';
				break;
		}
		
		$html = "<html>";
		$html .= "<head>";
		$html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
		
		$html .= "<style>
		*, h2{margin:0}
		body{font-family:'Calibri',Arial;}
		.table{padding:0;margin:0;border-collapse:collapse;width;100%}
		.table td, .table th { border:0.5px solid #555; padding:2px!important; }
		.td-center { text-align: center; }
		.td-right { text-align:right;}
		.td-bold { font-weight:bold}
		</style>";
		
		$html.= "</head>";
		$html.= "<body>";
		$html.= "<div id='div_header' style='margin-bottom:10px;text-align:center'>";
		$html.= "<div><h2>".$nama_laporan."</h2></div>";
		$html.= "<center>
		<big>".date('d-M-Y', strtotime($post['tgl_awal']))." s/d ".date('d-M-Y', strtotime($post['tgl_akhir']))."</big><br>
		<big>".$kategori."</big><br>
		<big>PRINT DATE : ".date('d-M-Y H:i:s')."</big>
		</center>
		</div>";
		
		if($this->excel_flag == 1){
			$sheet->setTitle(substr($nama_laporan, 0, 31));
			$sheet->getStyle('A1')->getFont()->setSize(12);
			
			$sheet->setCellValue('A1', $nama_laporan);
			$sheet->setCellValue('A2', date('d-M-Y', strtotime($post['tgl_awal']))." s/d ".date('d-M-Y', strtotime($post['tgl_akhir'])));
			$sheet->setCellValue('A3', $kategori);
			$sheet->setCellValue('A4', 'PRINT DATE : '.date('d-M-Y H:i:s'));
			
			$sheet->mergeCells('A1:G1');
			$sheet->mergeCells('A2:G2');
			$sheet->mergeCells('A3:G3');
			$sheet->mergeCells('A4:G4');
			
			$sheet->getStyle("A1:A3")->getFont()->setBold(true);
			$sheet->getStyle('A1:A3')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A4')->getAlignment()->setHorizontal($alignment_right);
		}
		
		$html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
		$html.= "<div style='clear:both'></div>";
		$currow = 4;
		
		$grandtotal_jual = 0;
		$grandtotal_rb = 0;
		$grandtotal_rc = 0;
		$grandtotal_biaya = 0;
		$grandtotal_omzet = 0;

		foreach($data as $partnertype => $wilayahx){	

			$total_partnertype_jual = 0;
			$total_partnertype_rb = 0;
			$total_partnertype_rc = 0;
			$total_partnertype_biaya = 0;
			$total_partnertype_omzet = 0;

			$html.='<h3>PARTNER TYPE : '.$partnertype.'</h3>';
			if($this->excel_flag == 1){
				$currow++;
				$curcol = 1;
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'PARTNER TYPE : '.$partnertype);
				$sheet->getStyle("A".$currow)->getFont()->setBold(true);
				$sheet->mergeCells("A".$currow.":G".$currow);
			}

			foreach($wilayahx as $wilayah => $xkota){			
				$html.='<h3>WILAYAH : '.$wilayah.'</h3>';
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'WILAYAH : '.$wilayah);
					$sheet->getStyle("A".$currow)->getFont()->setBold(true);
					$sheet->mergeCells("A".$currow.":G".$currow);
				}
				
				$totalwilayah_jual = 0;
				$totalwilayah_rb = 0;
				$totalwilayah_rc = 0;
				$totalwilayah_biaya = 0;
				$totalwilayah_omzet = 0;
				
				foreach($xkota as $nama_kota => $data){
					$html.='<b>KOTA : '.$nama_kota.'</b><br>';
					if($this->excel_flag == 1){
						$currow++;
						$curcol = 1;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'KOTA : '.$nama_kota);
						$sheet->getStyle("A".$currow)->getFont()->setBold(true);
						$sheet->mergeCells("A".$currow.":G".$currow);
					}
					
					$html.='<table class="table" style="width:100%">';
					$html.='<tr style="background:#'.$warna_total.'">';
					$html.='<th width="5%">No</th>';
					$html.='<th width="30%">DIVISI</th>';
					$html.='<th width="13%">TOTAL JUAL</th>';
					$html.='<th width="13%">TOTAL RB</th>';
					$html.='<th width="13%">TOTAL RC</th>';
					$html.='<th width="13%">TOTAL BIAYA</th>';
					$html.='<th width="13%">OMZET NETTO</th>';
					$html.='</tr>';
					
					if($this->excel_flag == 1){
						$curcol = 1;
						$currow++;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO');
						$sheet->getColumnDimension('A')->setWidth(5);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'DIVISI');
						$sheet->getColumnDimension('B')->setWidth(30);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL JUAL');
						$sheet->getColumnDimension('C')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL RB');
						$sheet->getColumnDimension('D')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL RC');
						$sheet->getColumnDimension('E')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL BIAYA');
						$sheet->getColumnDimension('F')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'OMZET NETTO');
						$sheet->getColumnDimension('G')->setWidth(20);
						$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
						$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
					}
					$total_jual = 0;
					$total_rb = 0;
					$total_rc = 0;
					$total_biaya = 0;
					$total_omzet = 0;
					$no = 0;
					$startrow = $currow;
					foreach($data as $row){
						$no++;
						$html.='<tr>';
						$html.='<td class="td-center">'.$no.'</td>';
						$html.='<td class="">'.$row->Divisi.'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_Jual).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_RB).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_RC).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_Disc).'</td>';
						$html.='<td class="td-right">'.number_format($row->Omzet_Netto).'</td>';
						$html.='</tr>';
						
						if($this->excel_flag == 1){
							$currow++;
							$curcol = 1;
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $no);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Nm_Plg);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_Jual);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_RB);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_RC);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_Disc);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Omzet_Netto);
							$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');	
						}
						$total_jual += $row->Total_Jual;
						$total_rb += $row->Total_RB;
						$total_rc += $row->Total_RC;
						$total_biaya += $row->Total_Disc;
						$total_omzet += $row->Omzet_Netto;
					}
					
					$html.='<tr style="background:#'.$warna_total.'">';
					$html.='<td class="td-bold" colspan="2">'.$nama_kota.'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_jual).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_rb).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_rc).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_biaya).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_omzet).'</td>';
					$html.='</tr>';
					$html.='</table><br>';
					
					if($this->excel_flag == 1){
						$currow++;
						$curcol = 1;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $nama_kota);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_jual);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_rb);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_rc);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_biaya);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_omzet);
						$sheet->mergeCells("A".$currow.":B".$currow);
						// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_right);
						$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
						$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
						$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
						
						$styleArray = [
							'borders' => [
								'allBorders' => [
									'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
								],
							],
						];
						$sheet ->getStyle('A'.$startrow.':G'.$currow)->applyFromArray($styleArray);
						$currow++;
					}
					
					$totalwilayah_jual += $total_jual;
					$totalwilayah_rb += $total_rb;
					$totalwilayah_rc += $total_rc;
					$totalwilayah_biaya += $total_biaya;
					$totalwilayah_omzet += $total_omzet;
					
				}
				
				$html.='<table class="table" style="width:100%">';
				$html.='<tr style="background:#'.$warna_toko.'">';
				$html.='<td width="35%" class="td-bold" colspan="2">TOTAL WILAYAH '.$wilayah.'</td>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_jual).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_rb).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_rc).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_biaya).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_omzet).'</th>';
				$html.='</tr>';
				$html.='</table><br>';
				
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL WILAYAH '.$wilayah);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_jual);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_rb);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_rc);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_biaya);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_omzet);
					$sheet->mergeCells("A".$currow.":B".$currow);
					// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_right);
					$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
					$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
					$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
					$currow++;
				}
				$grandtotal_jual += $totalwilayah_jual;
				$grandtotal_rb += $totalwilayah_rb;
				$grandtotal_rc += $totalwilayah_rc;
				$grandtotal_biaya += $totalwilayah_biaya;
				$grandtotal_omzet += $totalwilayah_omzet;

				$total_partnertype_jual += $totalwilayah_jual;
				$total_partnertype_rb += $totalwilayah_rb;
				$total_partnertype_rc += $totalwilayah_rc;
				$total_partnertype_biaya += $totalwilayah_biaya;
				$total_partnertype_omzet += $totalwilayah_omzet;
			}

			$html.='<table class="table" style="width:100%">';
			$html.='<tr style="background:#'.$warna_toko.'">';
			$html.='<td width="35%" class="td-bold" colspan="2">TOTAL PARTNER TYPE '.$partnertype.'</td>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_jual).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_rb).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_rc).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_biaya).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_omzet).'</th>';
			$html.='</tr>';
			$html.='</table><br>';

			if($this->excel_flag == 1){
				$currow++;
				$curcol = 1;
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL PARTNER TYPE '.$partnertype);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_jual);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_rb);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_rc);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_biaya);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_omzet);
				$sheet->mergeCells("A".$currow.":B".$currow);
				$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
				$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
				$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
				$currow++;
			}
		}
			
		$html.='<table class="table" style="width:100%">';
		$html.='<tr style="background:#'.$warna_wilayah.'">';
		$html.='<td width="35%" class="td-bold" colspan="2">GRAND TOTAL</td>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_jual).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_rb).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_rc).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_biaya).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_omzet).'</th>';
		$html.='</tr>';
		$html.='</table>';
		
		$html.= "</div>";
		$html.= "</body></html>";
		
		if($this->excel_flag == 1){
			$currow++;
			$curcol = 1;
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'GRAND TOTAL');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_jual);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_rb);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_rc);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_biaya);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_omzet);
			$sheet->mergeCells("A".$currow.":B".$currow);
			$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
			$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_wilayah);
			$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
		}
		
		if($this->excel_flag == 1){
			$sheet->setSelectedCell('A1');
			$filename=$nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
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
		else{
			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'orientation' => 'P'
			));
			$mpdf->defaultfooterline = 1;
			$mpdf->SetFooter('
			<table width="100%">
				<tr>
					<td>'.date('d-M-Y H:i:s').'</td>
					<td class="td-right">Halaman {PAGENO} / {nbpg}</td>
				</tr>
			</table>');
			$mpdf->WriteHTML($html);
			$mpdf->Output();

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

		}
	}

	public function PreviewLaporanDivisiPerKota($post,$data,$params){
		$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
		$alignment_left = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
		$alignment_right = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
		$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
		
		$warna_jatuhtempo 	= '909090';
		$warna_wilayah	= 'A9A9A9';
		$warna_toko		= 'C0C0C0';
		$warna_total	= 'D3D3D3';
		
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);
		
		$nama_laporan = $this->laporan[$post['laporan']];
		
		switch ($post['kategori_brg']) {
			case 'P':
				$kategori = 'PRODUCT';
				break;
			case 'S':
				$kategori = 'SPAREPART';
				break;
			default:
				$kategori = 'PRODUCT & SPAREPART';
				break;
		}
		
		$html = "<html>";
		$html .= "<head>";
		$html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
		
		$html .= "<style>
		*, h2{margin:0}
		body{font-family:'Calibri',Arial;}
		.table{padding:0;margin:0;border-collapse:collapse;width;100%}
		.table td, .table th { border:0.5px solid #555; padding:2px!important; }
		.td-center { text-align: center; }
		.td-right { text-align:right;}
		.td-bold { font-weight:bold}
		</style>";
		
		$html.= "</head>";
		$html.= "<body>";
		$html.= "<div id='div_header' style='margin-bottom:10px;text-align:center'>";
		$html.= "<div><h2>".$nama_laporan."</h2></div>";
		$html.= "<center>
		<big>".date('d-M-Y', strtotime($post['tgl_awal']))." s/d ".date('d-M-Y', strtotime($post['tgl_akhir']))."</big><br>
		<big>".$kategori."</big><br>
		<big>PRINT DATE : ".date('d-M-Y H:i:s')."</big>
		</center>
		</div>";
		
		if($this->excel_flag == 1){
			$sheet->setTitle(substr($nama_laporan, 0, 31));
			$sheet->getStyle('A1')->getFont()->setSize(12);
			
			$sheet->setCellValue('A1', $nama_laporan);
			$sheet->setCellValue('A2', date('d-M-Y', strtotime($post['tgl_awal']))." s/d ".date('d-M-Y', strtotime($post['tgl_akhir'])));
			$sheet->setCellValue('A3', $kategori);
			$sheet->setCellValue('A4', 'PRINT DATE : '.date('d-M-Y H:i:s'));
			
			$sheet->mergeCells('A1:G1');
			$sheet->mergeCells('A2:G2');
			$sheet->mergeCells('A3:G3');
			$sheet->mergeCells('A4:G4');
			
			$sheet->getStyle("A1:A3")->getFont()->setBold(true);
			$sheet->getStyle('A1:A3')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A4')->getAlignment()->setHorizontal($alignment_right);
		}
		
		$html.= "<div class='div_body' style='width:8000px;overflow-x:scroll; '>";
		$html.= "<div style='clear:both'></div>";
		$currow = 4;
		
		$grandtotal_jual = 0;
		$grandtotal_rb = 0;
		$grandtotal_rc = 0;
		$grandtotal_biaya = 0;
		$grandtotal_omzet = 0;

		foreach($data as $partnertype => $wilayahx){	

			$total_partnertype_jual = 0;
			$total_partnertype_rb = 0;
			$total_partnertype_rc = 0;
			$total_partnertype_biaya = 0;
			$total_partnertype_omzet = 0;

			$html.='<h3>PARTNER TYPE : '.$partnertype.'</h3>';
			if($this->excel_flag == 1){
				$currow++;
				$curcol = 1;
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'PARTNER TYPE : '.$partnertype);
				$sheet->getStyle("A".$currow)->getFont()->setBold(true);
				$sheet->mergeCells("A".$currow.":G".$currow);
			}

			foreach($wilayahx as $wilayah => $xdivisi){			
				$html.='<h3>WILAYAH : '.$wilayah.'</h3>';
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'WILAYAH : '.$wilayah);
					$sheet->getStyle("A".$currow)->getFont()->setBold(true);
					$sheet->mergeCells("A".$currow.":G".$currow);
				}
				
				$totalwilayah_jual = 0;
				$totalwilayah_rb = 0;
				$totalwilayah_rc = 0;
				$totalwilayah_biaya = 0;
				$totalwilayah_omzet = 0;
				
				foreach($xdivisi as $nama_divisi => $data){
					$html.='<b>DIVISI : '.$nama_divisi.'</b><br>';
					if($this->excel_flag == 1){
						$currow++;
						$curcol = 1;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'DIVISI : '.$nama_divisi);
						$sheet->getStyle("A".$currow)->getFont()->setBold(true);
						$sheet->mergeCells("A".$currow.":G".$currow);
					}
					
					$html.='<table class="table" style="width:100%">';
					$html.='<tr style="background:#'.$warna_total.'">';
					$html.='<th width="5%">No</th>';
					$html.='<th width="30%">KOTA</th>';
					$html.='<th width="13%">TOTAL JUAL</th>';
					$html.='<th width="13%">TOTAL RB</th>';
					$html.='<th width="13%">TOTAL RC</th>';
					$html.='<th width="13%">TOTAL BIAYA</th>';
					$html.='<th width="13%">OMZET NETTO</th>';
					$html.='</tr>';
					
					if($this->excel_flag == 1){
						$curcol = 1;
						$currow++;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO');
						$sheet->getColumnDimension('A')->setWidth(5);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'KOTA');
						$sheet->getColumnDimension('B')->setWidth(30);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL JUAL');
						$sheet->getColumnDimension('C')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL RB');
						$sheet->getColumnDimension('D')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL RC');
						$sheet->getColumnDimension('E')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL BIAYA');
						$sheet->getColumnDimension('F')->setWidth(20);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'OMZET NETTO');
						$sheet->getColumnDimension('G')->setWidth(20);
						$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
						$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
					}
					$total_jual = 0;
					$total_rb = 0;
					$total_rc = 0;
					$total_biaya = 0;
					$total_omzet = 0;
					$no = 0;
					$startrow = $currow;
					foreach($data as $row){
						$no++;
						$html.='<tr>';
						$html.='<td class="td-center">'.$no.'</td>';
						$html.='<td class="">'.$row->Divisi.'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_Jual).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_RB).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_RC).'</td>';
						$html.='<td class="td-right">'.number_format($row->Total_Disc).'</td>';
						$html.='<td class="td-right">'.number_format($row->Omzet_Netto).'</td>';
						$html.='</tr>';
						
						if($this->excel_flag == 1){
							$currow++;
							$curcol = 1;
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $no);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Divisi);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_Jual);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_RB);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_RC);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Total_Disc);
							$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row->Omzet_Netto);
							$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');	
						}
						$total_jual += $row->Total_Jual;
						$total_rb += $row->Total_RB;
						$total_rc += $row->Total_RC;
						$total_biaya += $row->Total_Disc;
						$total_omzet += $row->Omzet_Netto;
					}
					
					$html.='<tr style="background:#'.$warna_total.'">';
					$html.='<td class="td-bold" colspan="2">'.$nama_divisi.'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_jual).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_rb).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_rc).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_biaya).'</td>';
					$html.='<td class="td-right td-bold">'.number_format($total_omzet).'</td>';
					$html.='</tr>';
					$html.='</table><br>';
					
					if($this->excel_flag == 1){
						$currow++;
						$curcol = 1;
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $nama_divisi);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_jual);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_rb);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_rc);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_biaya);
						$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_omzet);
						$sheet->mergeCells("A".$currow.":B".$currow);
						// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_right);
						$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
						$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_total);
						$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
						
						$styleArray = [
							'borders' => [
								'allBorders' => [
									'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
								],
							],
						];
						$sheet ->getStyle('A'.$startrow.':G'.$currow)->applyFromArray($styleArray);
						$currow++;
					}
					
					$totalwilayah_jual += $total_jual;
					$totalwilayah_rb += $total_rb;
					$totalwilayah_rc += $total_rc;
					$totalwilayah_biaya += $total_biaya;
					$totalwilayah_omzet += $total_omzet;
					
				}
				
				$html.='<table class="table" style="width:100%">';
				$html.='<tr style="background:#'.$warna_toko.'">';
				$html.='<td width="35%" class="td-bold" colspan="2">TOTAL WILAYAH '.$wilayah.'</td>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_jual).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_rb).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_rc).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_biaya).'</th>';
				$html.='<th width="13%" class="td-right td-bold">'.number_format($totalwilayah_omzet).'</th>';
				$html.='</tr>';
				$html.='</table><br>';
				
				if($this->excel_flag == 1){
					$currow++;
					$curcol = 1;
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL WILAYAH '.$wilayah);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_jual);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_rb);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_rc);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_biaya);
					$sheet->setCellValueByColumnAndRow($curcol++, $currow, $totalwilayah_omzet);
					$sheet->mergeCells("A".$currow.":B".$currow);
					// $sheet->getStyle('A'.$currow)->getAlignment()->setHorizontal($alignment_right);
					$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
					$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
					$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
					$currow++;
				}
				$grandtotal_jual += $totalwilayah_jual;
				$grandtotal_rb += $totalwilayah_rb;
				$grandtotal_rc += $totalwilayah_rc;
				$grandtotal_biaya += $totalwilayah_biaya;
				$grandtotal_omzet += $totalwilayah_omzet;

				$total_partnertype_jual += $totalwilayah_jual;
				$total_partnertype_rb += $totalwilayah_rb;
				$total_partnertype_rc += $totalwilayah_rc;
				$total_partnertype_biaya += $totalwilayah_biaya;
				$total_partnertype_omzet += $totalwilayah_omzet;
			}

			$html.='<table class="table" style="width:100%">';
			$html.='<tr style="background:#'.$warna_toko.'">';
			$html.='<td width="35%" class="td-bold" colspan="2">TOTAL PARTNER TYPE '.$partnertype.'</td>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_jual).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_rb).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_rc).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_biaya).'</th>';
			$html.='<th width="13%" class="td-right td-bold">'.number_format($total_partnertype_omzet).'</th>';
			$html.='</tr>';
			$html.='</table><br>';

			if($this->excel_flag == 1){
				$currow++;
				$curcol = 1;
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'TOTAL PARTNER TYPE '.$partnertype);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_jual);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_rb);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_rc);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_biaya);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $total_partnertype_omzet);
				$sheet->mergeCells("A".$currow.":B".$currow);
				$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
				$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_toko);
				$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
				$currow++;
			}
		}
			
		$html.='<table class="table" style="width:100%">';
		$html.='<tr style="background:#'.$warna_wilayah.'">';
		$html.='<td width="35%" class="td-bold" colspan="2">GRAND TOTAL</td>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_jual).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_rb).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_rc).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_biaya).'</th>';
		$html.='<th width="13%" class="td-right td-bold">'.number_format($grandtotal_omzet).'</th>';
		$html.='</tr>';
		$html.='</table>';
		
		$html.= "</div>";
		$html.= "</body></html>";
		
		if($this->excel_flag == 1){
			$currow++;
			$curcol = 1;
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'GRAND TOTAL');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, '');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_jual);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_rb);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_rc);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_biaya);
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, $grandtotal_omzet);
			$sheet->mergeCells("A".$currow.":B".$currow);
			$sheet->getStyle("A".$currow.":G".$currow)->getFont()->setBold(true);
			$sheet->getStyle("A".$currow.":G".$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB($warna_wilayah);
			$sheet->getStyle('C'.$currow.':G'.$currow)->getNumberFormat()->setFormatCode('#,##0');
		}
		
		if($this->excel_flag == 1){
			$sheet->setSelectedCell('A1');
			$filename=$nama_laporan.'['.date('Ymd').']'; //save our workbook as this file name
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
		else{
			require_once __DIR__ . '\vendor\autoload.php';
			$mpdf = new \Mpdf\Mpdf(array(
				'mode' => '',
				'format' => 'A4',
				'default_font_size' => 8,
				'default_font' => 'tahoma',
				'orientation' => 'P'
			));
			$mpdf->defaultfooterline = 1;
			$mpdf->SetFooter('
			<table width="100%">
				<tr>
					<td>'.date('d-M-Y H:i:s').'</td>
					<td class="td-right">Halaman {PAGENO} / {nbpg}</td>
				</tr>
			</table>');
			$mpdf->WriteHTML($html);
			$mpdf->Output();

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

		}
	}

	
}						
?>