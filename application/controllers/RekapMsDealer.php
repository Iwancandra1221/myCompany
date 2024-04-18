<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class RekapMsDealer extends MY_Controller 
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
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN REKAP MS DEALER";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU REKAP MS DEALER ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$api = 'APITES'; 
			
			$url = $this->API_URL."/CetakDaftarDealer/GetPartnerType?api=".$api;
			$getListPartnerType = HttpGetRequest($url, $this->API_URL, "Ambil List Partner Type");
			// $getListPartnerType = json_decode($getListPartnerType); 
			$getListPartnerType = $this->GzipDecodeModel->_decodeGzip($getListPartnerType);

			$url = $this->API_URL."/MsWilayah/GetWilayahHO?api=".$api;
			$getlistWilayahHO = HttpGetRequest($url, $this->API_URL, "Ambil List Wilayah HO");
			$getlistWilayahHO = json_decode($getlistWilayahHO);  

			$url = $this->API_URL."/MsWilayah/GetWpShipment?api=".$api;
			$getlistwpshipment = HttpGetRequest($url, $this->API_URL, "Ambil List W.P. Shipment");
			$getlistwpshipment = json_decode($getlistwpshipment);  
 
			$data['listpartnertype'] = $getListPartnerType;  
			$data['listwilayahho'] = $getlistWilayahHO;  
			$data['listwpshipment'] = $getlistwpshipment;  

			$data['title'] = 'REKAP DAFTAR PELANGGAN | '.WEBTITLE;
			$data['formDest'] = "RekapMsDealer/Proses";
			
			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('RekapMsDealerView',$data);
		}
		
		public function Proses()
		{  
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN REKAP MS DEALER";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES REKAP MS DEALER ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$data = array();
			$page_title = 'REKAP DAFTAR DEALER';
			    
			$partnertype = $_POST['partnertype']; 
			$cbox1 = $_POST['statuscbox1']; 
			$cbox2 = $_POST['statuscbox2']; 

			if($cbox1=="Y")
			{
				$wilayahho = "ALL";
			}
			else
			{
				$wilayahho = $_POST['wilayahho']; 
			}
			if($cbox2=="Y")
			{
				$wpshipment = "ALL"; 
			}
			else
			{
				$wpshipment = $_POST['wpshipment']; 
			}
			$status = $_POST['status'];  

			$this->Export_Excel_CETAK_DAFTAR_PELANGGAN($page_title,$wilayahho,$wpshipment,$partnertype,$status,$params); 
			 
		} 

		public function Export_Excel_CETAK_DAFTAR_PELANGGAN($page_title, $wilayahho,$wpshipment,$partnertype,$status,$params)
		{  
			$data = array();
			$api = 'APITES';	 
			// $CetakDaftarDealer = json_decode(file_get_contents($this->API_URL."/CetakDaftarDealer/Export_Excel_CETAK_DAFTAR_PELANGGAN?api=".$api."&wilayahho=".urlencode($wilayahho)."&wpshipment=".urlencode($wpshipment)."&partnertype=".urlencode($partnertype)."&status=".urlencode($status).""));
			
			$CetakDaftarDealer = file_get_contents($this->API_URL."/CetakDaftarDealer/Export_Excel_CETAK_DAFTAR_PELANGGAN?api=".$api."&wilayahho=".urlencode($wilayahho)."&wpshipment=".urlencode($wpshipment)."&partnertype=".urlencode($partnertype)."&status=".urlencode($status)."");
	 		$CetakDaftarDealer = $this->GzipDecodeModel->_decodeGzip($CetakDaftarDealer);

			if(count($CetakDaftarDealer)==0){
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				
				exit('Tidak ada data');
			} 
			//die($CetakDaftarDealer);
			//exit($CetakDaftarDealer);

			$style_col_ganjil = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#00ffff;";
			$style_col_genap = "float:left;line-height:30px;vertical-align:middle;border:1px solid #ccc;text-align:right;padding-right:5px;background-color:#ff00ff;"; 

			$spreadsheet = new Spreadsheet(); 
 			$activesheet;
 			$sheetindex = 0; 
			$currcol = 1;
			$currrow = 0;
			$startid = 0; 
			$currentWilayah = "";
			// foreach($CetakDaftarDealer as $dt){ 
			foreach ($CetakDaftarDealer as $key => $dt) {
				if ($currentWilayah != $dt->wilayah)
				{
					if ($sheetindex>0)
					{ 
						$spreadsheet->createSheet(); 
					}
						$spreadsheet->setActiveSheetIndex($sheetindex);
						$activesheet = $spreadsheet->getActiveSheet();
						$activesheet->setTitle($dt->wilayah);
						$activesheet->setCellValue('A1', 'DAFTAR PELANGGAN'); 
						$activesheet->getStyle('A1')->getFont()->setBold(true);	
						$activesheet->setCellValue('A2', 'Wilayah : '.$dt->wilayah);  
						$activesheet->getStyle('A2')->getFont()->setBold(true);	
						$activesheet->setCellValue('A3', 'Tgl Proses : '.date("d-M-Y"));    	
						$activesheet->getStyle('A3')->getFont()->setBold(true);	
  
						$currrow = 5; 
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Pelanggan');
						$activesheet->getColumnDimension('A')->setWidth(15); 
						$activesheet->getStyle('A'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama Pelanggan');
						$activesheet->getColumnDimension('B')->setWidth(15); 
						$activesheet->getStyle('B'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Alamat Pelanggan');
						$activesheet->getColumnDimension('C')->setWidth(15); 
						$activesheet->getStyle('C'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kota');
						$activesheet->getColumnDimension('D')->setWidth(15); 
						$activesheet->getStyle('D'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Partner Type');
						$activesheet->getColumnDimension('E')->setWidth(15); 
						$activesheet->getStyle('E'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Wilayah');
						$activesheet->getColumnDimension('F')->setWidth(15); 
						$activesheet->getStyle('F'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'NPWP');
						$activesheet->getColumnDimension('G')->setWidth(15); 
						$activesheet->getStyle('G'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Telp');
						$activesheet->getColumnDimension('H')->setWidth(15); 
						$activesheet->getStyle('H'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Fax');
						$activesheet->getColumnDimension('I')->setWidth(15); 
						$activesheet->getStyle('I'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kontak Person');
						$activesheet->getColumnDimension('J')->setWidth(15); 
						$activesheet->getStyle('J'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Email Address');
						$activesheet->getColumnDimension('K')->setWidth(15); 
						$activesheet->getStyle('K'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'No HP');
						$activesheet->getColumnDimension('L')->setWidth(15); 
						$activesheet->getStyle('L'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama Toko');
						$activesheet->getColumnDimension('M')->setWidth(15); 
						$activesheet->getStyle('M'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Lokasi');
						$activesheet->getColumnDimension('N')->setWidth(15); 
						$activesheet->getStyle('N'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama Bank');
						$activesheet->getColumnDimension('O')->setWidth(15); 
						$activesheet->getStyle('O'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Cabang Bank');
						$activesheet->getColumnDimension('P')->setWidth(15); 
						$activesheet->getStyle('P'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Rekening');
						$activesheet->getColumnDimension('Q')->setWidth(15); 
						$activesheet->getStyle('Q'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama Pemilik Rekening');
						$activesheet->getColumnDimension('R')->setWidth(15); 
						$activesheet->getStyle('R'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'No VA');
						$activesheet->getColumnDimension('S')->setWidth(15); 
						$activesheet->getStyle('S'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Aktif');
						$activesheet->getColumnDimension('T')->setWidth(15); 
						$activesheet->getStyle('T'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Jenie PPh');
						$activesheet->getColumnDimension('U')->setWidth(15); 
						$activesheet->getStyle('U'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Keterangan');
						$activesheet->getColumnDimension('V')->setWidth(15); 
						$activesheet->getStyle('V'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kelompok PKP');
						$activesheet->getColumnDimension('W')->setWidth(15); 
						$activesheet->getStyle('W'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'CL Mishirin');
						$activesheet->getColumnDimension('X')->setWidth(15); 
						$activesheet->getStyle('X'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'CL SPAREPART');
						$activesheet->getColumnDimension('Y')->setWidth(15); 
						$activesheet->getStyle('Y'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Marker');
						$activesheet->getColumnDimension('Z')->setWidth(15); 
						$activesheet->getStyle('Z'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Faktur 1st');
						$activesheet->getColumnDimension('AA')->setWidth(15); 
						$activesheet->getStyle('AA'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'User Name');
						$activesheet->getColumnDimension('AB')->setWidth(15); 
						$activesheet->getStyle('AB'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Entry Time');
						$activesheet->getColumnDimension('AC')->setWidth(15); 
						$activesheet->getStyle('AC'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, '#Kode Wil');
						$activesheet->getColumnDimension('AD')->setWidth(15); 
						$activesheet->getStyle('AD'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama Wilayah');
						$activesheet->getColumnDimension('AE')->setWidth(15); 
						$activesheet->getStyle('AE'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, '#Wilayah');
						$activesheet->getColumnDimension('AF')->setWidth(15); 
						$activesheet->getStyle('AF'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Type shipment name');
						$activesheet->getColumnDimension('AG')->setWidth(15); 
						$activesheet->getStyle('AG'.$currrow)->getFont()->setBold(true);	
						$currcol += 1;
						$activesheet->setCellValueByColumnAndRow($currcol, $currrow, 'Branch');
						$activesheet->getColumnDimension('AH')->setWidth(15); 
						$activesheet->getStyle('AH'.$currrow)->getFont()->setBold(true); 

						$sheetindex++;  
						$currcol = 1;
						$currrow += 1;
				}  
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->kd_plg); 
				$activesheet->getColumnDimension('A')->setWidth(15);  
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->nm_plg); 
				$activesheet->getColumnDimension('B')->setWidth(15);   
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->alm_plg); 
				$activesheet->getColumnDimension('C')->setWidth(15);  
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->kota); 
				$activesheet->getColumnDimension('D')->setWidth(15);  
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->partner_type); 
				$activesheet->getColumnDimension('E')->setWidth(15);  
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->wilayah); 
				$activesheet->getColumnDimension('F')->setWidth(15);  
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->npwp); 
				$activesheet->getColumnDimension('G')->setWidth(15);  
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->telp); 
				$activesheet->getColumnDimension('H')->setWidth(15);  
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->fax); 
				$activesheet->getColumnDimension('I')->setWidth(15);  
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->kontak_person); 
				$activesheet->getColumnDimension('J')->setWidth(15);  
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->Email_Address); 
				$activesheet->getColumnDimension('K')->setWidth(15);  
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->No_HP); 
				$activesheet->getColumnDimension('L')->setWidth(15);  
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->Nm_Toko); 
				$activesheet->getColumnDimension('M')->setWidth(15);  
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->Kd_Lokasi); 
				$activesheet->getColumnDimension('N')->setWidth(15);  
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->Nama_Bank); 
				$activesheet->getColumnDimension('O')->setWidth(15);  
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->Cabang_Bank); 
				$activesheet->getColumnDimension('P')->setWidth(15);  
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->No_Rekening); 
				$activesheet->getColumnDimension('Q')->setWidth(15);  
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->NamaPemilik_Rekening); 
				$activesheet->getColumnDimension('R')->setWidth(15);  
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->No_VA); 
				$activesheet->getColumnDimension('S')->setWidth(15); 
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->Aktif); 
				$activesheet->getColumnDimension('T')->setWidth(15); 
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->Jenis_PPh); 
				$activesheet->getColumnDimension('U')->setWidth(15); 
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->ket); 
				$activesheet->getColumnDimension('V')->setWidth(15); 
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->Kelompok_PKP); 
				$activesheet->getColumnDimension('W')->setWidth(15); 
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->CL_Mishirin); 
				$activesheet->getColumnDimension('X')->setWidth(15); 
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->CL_SPAREPART); 
				$activesheet->getColumnDimension('Y')->setWidth(15); 
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->Marker); 
				$activesheet->getColumnDimension('Z')->setWidth(15); 
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->Faktur1st); 
				$activesheet->getColumnDimension('AA')->setWidth(15); 
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->User_Name); 
				$activesheet->getColumnDimension('AB')->setWidth(15); 
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->Entry_time); 
				$activesheet->getColumnDimension('AC')->setWidth(15); 
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->Kd_Wil); 
				$activesheet->getColumnDimension('AD')->setWidth(15); 
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->Nm_Wil); 
				$activesheet->getColumnDimension('AE')->setWidth(15); 
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->Wilayah2); 
				$activesheet->getColumnDimension('AF')->setWidth(15); 
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->type_shipment_name); 
				$activesheet->getColumnDimension('AG')->setWidth(15); 
				$currcol += 1;
				$activesheet->setCellValueByColumnAndRow($currcol, $currrow, $dt->branch_name); 
				$activesheet->getColumnDimension('AH')->setWidth(15); 
			    $currrow++;
				$currcol = 1;
				$currentWilayah = $dt->wilayah;
			}  
			$filename='CetakDaftarDealer['.date('Ymd').']'; //save our workbook as this file name
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

	}							