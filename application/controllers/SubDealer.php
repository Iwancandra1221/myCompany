<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class SubDealer extends MY_Controller 
	{
		public $excel_flag = 0;
		public $nama_bulan = array('','JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER');
		public $wilayah_group = array();
		
		// untuk dipakai ketika tampilkan data dan simpan data.
		public $kolom_label = array(
			'NamaMD'=>'Nama MD',
			'CabangMD'=>'Cabang',
			'NamaToko'=>'Nama Toko',
			'TitleToko'=>'Title Toko',
			'FotoTampakDepan'=>'Foto Tampak DEPAN TOKO',
			'NamaPemilik'=>'Nama Pemilik Toko',
			'NamaPanggilan'=>'Nama Panggilan Pemilik Toko',
			'TerdaftarDiMishirin'=>'Sudah terdaftar di Aplikasi Mishirin?',
			'EmailLoginMishirin'=>'Email Login Mishirin',
			'EmailToko'=>'Email Toko',
			'NoHP'=>'No HP',
			'NoWhatsapp'=>'No Whatsapp',
			'NoTelpToko'=>'No Telp Toko',
			'AlamatToko'=>'Alamat Toko',
			'KodePos'=>'Kode Pos',
			'KotamadyaKabupaten'=>'KotaMadya/Kabupaten'
		);
		
		public function __construct()
		{
			parent::__construct();
			$this->load->model('HelperModel');
			$this->load->helper('FormLibrary');
			$this->load->library('excel');
			$this->load->model('SubDealerModel');
			$this->load->model('BranchModel');
		}
		
		public function getBranchName($branchId){
			$cabang = $this->BranchModel->GetList();
			if($_SESSION["logged_in"]['branch_id']!='JKT'){
				foreach($cabang as $row){
					if($row->BranchID==$_SESSION["logged_in"]['branch_id']){
						return $row->BranchName;
					}
				}
			}
			return '';
		}
		
		public function index(){

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS SUBDEALER";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU MS SUBDEALER ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			// $cabang = $this->BranchModel->GetList();
			$cabang = $this->SubDealerModel->GetListCabang();			
			$cabang_nama = "";
			// if($_SESSION["logged_in"]['branch_id']!='JKT'){
				// $find_cabang = array();
				// foreach($cabang as $row){
					// if($row->BranchID==$_SESSION["logged_in"]['branch_id']){
						// $find_cabang[] = $row;
						// $cabang_nama = $row->BranchName;
					// }
				// }
				// $cabang = $find_cabang;
			// }
			
			if($_SESSION["logged_in"]['branch_id']!='JKT'){
				if($cabang){
					$cabang_nama = $cabang[0]->Cabang;
				}
			}
			
			$provinsi = $this->SubDealerModel->GetListProvinsi($cabang_nama); //tambahkan cabang_nama untuk filter provinsi, untuk JKT tampilkan semua.
			$data['cabang'] = $cabang;
			$data['provinsi'] = $provinsi;

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('Spreadsheet',$data);
		}
		
		public function GetListProvinsi(){
			$cabang = $this->input->get('cabang');
			$provinsi = $this->SubDealerModel->GetListProvinsi($cabang);

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS BRANCH SYNC";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." MS SUBDEALER GetListProvinsi";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

			echo json_encode($provinsi);
		}
		
		public function GetListKotamadya(){
			$cabang = $this->input->get('cabang');
			$prov = $this->input->get('prov');
			// $cabang_nama = $this->getBranchName($_SESSION["logged_in"]['branch_id']);
			// $provinsi = urldecode($provinsi);
			$kotamadya = $this->SubDealerModel->GetListKotamadya($cabang, $prov);

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS BRANCH SYNC";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." MS SUBDEALER GetListKotamadya";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

			echo json_encode($kotamadya);
		}
		
		public function GetListNamaMD(){
			$cabang = $this->input->get('cabang');
			$prov = $this->input->get('prov');
			$kota = $this->input->get('kota');
			// $cabang_nama = $this->getBranchName($_SESSION["logged_in"]['branch_id']);
			// $kotamadya = urldecode($kotamadya);
			$namamd = $this->SubDealerModel->GetListNamaMD($cabang, $prov, $kota);

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS BRANCH SYNC";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." MS SUBDEALER GetListNamaMD";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

			echo json_encode($namamd);
		}
		
		public function GetListData(){
			
			$post = $this->PopulatePost();
			//die(json_encode($post));
			$cabang = $post["cabang"];
			$provinsi = $post["provinsi"];
			$kotamadya = $post["kotamadya"];
			$namamd = $post["namamd"];
			$filter = $post["filter"];
			$dp1 = $post["dp1"];
			$dp2 = $post["dp2"];
			$data = $this->SubDealerModel->GetListData($filter,$cabang,$provinsi,$kotamadya,$namamd,$dp1,$dp2);
			
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS BRANCH SYNC";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." MS SUBDEALER GetListData";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

			echo json_encode($data);
		}
		
		public function List(){
			// echo $NamaMD;
			
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS SUBDEALER";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." LIST MS SUBDEALER ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$Cabang = $this->input->get('Cabang');
			$Provinsi = $this->input->get('Provinsi');
			$Kotamadya = $this->input->get('Kotamadya');
			$NamaMD = $this->input->get('NamaMD');
			$sd = $this->input->get('sd');
			$ms = $this->input->get('ms');
			$awal = $this->input->get('awal');
			$akhir = $this->input->get('akhir');
			
			$Cabang = urldecode($Cabang);
			$Provinsi = urldecode($Provinsi);
			$Kotamadya = urldecode($Kotamadya);
			$NamaMD = urldecode($NamaMD);
			$awal = urldecode($awal);
			$akhir = urldecode($akhir);
			
			$sub_dealer = $this->SubDealerModel->GetListSubDealer($Cabang, $Provinsi, $Kotamadya, $NamaMD, $awal, $akhir);
			
			// print_r($sub_dealer);die;
			
			$header = array();
			$header['Cabang'] = $Cabang;
			$header['Provinsi'] = $Provinsi;
			$header['Kotamadya'] = $Kotamadya;
			$header['NamaMD'] = $NamaMD;
			$header['sd'] = $sd;
			$header['ms'] = $ms;
			$header['awal'] = $awal;
			$header['akhir'] = $akhir;
			
			$data['header'] = $header;
			$data['sub_dealer'] = $sub_dealer;

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('SubDealer',$data);
		}
		
		public function edit($SubDealerId){
			$sub_dealer = $this->SubDealerModel->GetSubDealerEdit($SubDealerId);
			// print_r($sub_dealer);
			// die();
			
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS SUBDEALER";
			$params['TrxID'] = $sub_dealer;
			$params['Description'] = $_SESSION["logged_in"]["username"]." EDIT MS SUBDEALER ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			// Validasi: hanya bisa edit data sendiri
			if(trim($sub_dealer->NamaMD)!=$_SESSION["logged_in"]["username"]){
				echo "Anda tidak berhak edit data ini. <a href='".base_url('SubDealer')."'>Kembali</a>";

				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Berhak";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				die;
			}
			
			// $sub_dealer = json_encode($sub_dealer);
			// die($sub_dealer);
			
			$data['sub_dealer'] = $sub_dealer;

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('SubDealerEdit',$data);		
		}
		
		public function Update(){
			$post = $this->PopulatePost();
			$SubDealerId = $post["SubDealerId"];
			// $SubDealer = array_values($post);
			// $cols = array_keys($post);
			unset($post['SubDealerId']);
			
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS SUBDEALER";
			$params['TrxID'] = $SubDealerId;
			$params['Description'] = $_SESSION["logged_in"]["username"]." UPDATE MS SUBDEALER ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$json = json_encode($post);
			
			// die($json);
			$SimpanMarketSurvey = $this->SubDealerModel->RequestUpdateSubDealer($SubDealerId, $json);
			
			$cabang = urlencode($post['CabangMD']);
			$provinsi = urlencode($post['Provinsi']);
			$kotamadya = urlencode($post['KotamadyaKabupaten']);
			$nama_md = urlencode($post['NamaMD']);		
			
			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			redirect('SubDealer/List?Cabang='.$cabang.'&Provinsi='.$provinsi.'&Kotamadya='.$kotamadya.'&NamaMD='.$nama_md);
		}
			
		public function NeedApproval()
		{
			$sub_dealer = $this->SubDealerModel->GetListDataApproval($_SESSION["logged_in"]["userid"]);
			
			// print_r($_SESSION["logged_in"]["userid"]);die;
			
			$data['sub_dealer'] = $sub_dealer;

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS BRANCH SYNC";
			$params['TrxID'] = $sub_dealer;
			$params['Description'] = $_SESSION["logged_in"]["username"]." MS SUBDEALER NeedApproval";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

			$this->RenderView('SubDealerNeedApproval',$data);
		}
		
		public function Approval($SubDealerId){
			$sub_dealer = $this->SubDealerModel->GetSubDealerEdit($SubDealerId);
			// print_r($sub_dealer);die;
			
			$data['sub_dealer'] = $sub_dealer;

			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS BRANCH SYNC";
			$params['TrxID'] = $sub_dealer;
			$params['Description'] = $_SESSION["logged_in"]["username"]." MS SUBDEALER Approval";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

			$this->RenderView('SubDealerApproval',$data);
		}
		
		public function Approve(){
			$post = $this->PopulatePost();
			
			$SubDealerId = $post['SubDealerId'];
			
			
			if(ISSET($post['approve'])){
				// ActivityLog
				$params = array();   
				$params['LogDate'] = date("Y-m-d H:i:s");
				$params['UserID'] = $_SESSION["logged_in"]["userid"];
				$params['UserName'] = $_SESSION["logged_in"]["username"];
				$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
				$params['Module'] = "MS SUBDEALER";
				$params['TrxID'] = $SubDealerId;
				$params['Description'] = $_SESSION["logged_in"]["username"]." MS SUBDEALER Approve";
				$params['Remarks'] = "";
				$params['RemarksDate'] =  'NULL';
				$this->ActivityLogModel->insert_activity($params);


				$sub_dealer = $this->SubDealerModel->GetSubDealerEdit($SubDealerId);
				
				$data = json_decode($sub_dealer->UpdatedJson,true);
				$SubDealer = array_values($data);
				$cols = array_keys($data);
				
				// print_r($SubDealer);
				// echo "<br><br><br>";
				// print_r($cols);
				// die;
				
				$UpdateSubDealer = $this->SubDealerModel->RubahSubDealer($SubDealerId, $SubDealer, $cols);
				
				//Rubah nama kolom ke kolom alias yg ada space
				foreach($cols as $key => $col){
					if(isset($this->kolom_label[$col])) {
						$cols[$key] = $this->kolom_label[$col];
					}
				}
				
				// print_r($cols);
				// die;
				
				$UpdateSubDealer = $this->SubDealerModel->RubahDataMarketSurvey($SubDealerId, $SubDealer, $cols);
				$approve = $this->SubDealerModel->ApprovalUpdate($SubDealerId, 1);

				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

			}
			if(ISSET($post['reject'])){
				$reject = $this->SubDealerModel->ApprovalUpdate($SubDealerId, 0);
				
			}
			redirect('SubDealer/NeedApproval');
		}
		
		public function export(){
			
			$post = $this->PopulatePost();
			//die(json_encode($post));
			$cabang = $post["cabang"];
			$provinsi = $post["provinsi"];
			$kotamadya = $post["kotamadya"];
			$namamd = $post["namamd"];
			
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS SUBDEALER";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." MS SUBDEALER export";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$kolom_alias = array(
			// 'GFormTimeStamp'=>'Timestamp',
			'NamaMD'=>'Nama MD',
			'CabangMD'=>'Cabang',
			'NamaToko'=>'Nama Toko',
			'TitleToko'=>'Title Toko',
			'FotoTampakDepan'=>'Foto Tampak DEPAN TOKO',
			'NamaPemilik'=>'Nama Pemilik Toko',
			'NamaPanggilan'=>'Nama Panggilan Pemilik Toko',
			'TerdaftarDiMishirin'=>'Sudah terdaftar di Aplikasi Mishirin?',
			'EmailLoginMishirin'=>'Email Login Mishirin',
			'EmailToko'=>'Email Toko',
			'NoHP'=>'No HP',
			'NoWhatsapp'=>'No Whatsapp',
			'NoTelpToko'=>'No Telp Toko',
			'AlamatToko'=>'Alamat Toko',
			'KodePos'=>'Kode Pos',
			'KotamadyaKabupaten'=>'KotaMadya/Kabupaten'
			);
			
			$kolom_skip = array('SubDealerId','DataSurveyId','TglMarketSurvey', 'CreatedBy', 'CreatedDate', 'ModifiedBy', 'ModifiedDate', 'IsInvalid', 'SetInvalidBy', 'SetInvalidDate', 'SetInvalidNote', 'Tujuan_Form'); // kolom ini tidak perlu diexport
			
			if(ISSET($post["exportSubDealer"])){
				// echo "exportSubDealer";
				$data = $this->SubDealerModel->exportDataSubDealer($cabang,$provinsi,$kotamadya,$namamd);
				if(!$data){
					// ActivityLog Update FAILED
					$params['Remarks']="FAILED - Data Tidak Ditemukan";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

					die('Data tidak ditemukan');
				}
				$filename = "SubDealer_".date('Ymd');
				$sheet->setTitle('SubDealer');
			}
			
			if(ISSET($post["exportMarketSurvey"])){
				// echo "exportMarketSurvey";
				$data = $this->SubDealerModel->exportDataMarketSurvey($cabang,$provinsi,$kotamadya,$namamd);
				if(!$data){
					// ActivityLog Update FAILED
					$params['Remarks']="FAILED - Data Tidak Ditemukan";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

					die('Data tidak ditemukan');
				}
				$filename = "MarketSurvey_".date('Ymd');
				$sheet->setTitle('MarketSurvey');
			}
			
			// $json =  json_encode($data);
			// echo $json;
			// die;
			
			$create_header = 0;
			$currrow = 1;
			$currcol = 0;
			foreach($data as $key => $val) {
				$currrow+=1;
				$currcol = 0;
				foreach($val as $_key => $_val) {
					if(!in_array($_key,$kolom_skip)){
						$currcol +=1;
						if($create_header==0){
							$col = str_replace("_"," ",$_key); // 1. ganti _ menjadi space
							if(isset($kolom_alias[$col])) {
								$col = $kolom_alias[$col]; // 1. ganti nama kolom
							}
							else{
								// $col = preg_replace('/(?<!\ )[A-Z]/', ' $0', $col);
								$col = ucwords($col);
							}
							$sheet->setCellValueByColumnAndRow($currcol, 1, $col);
						}
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $_val);
					}
				}
				$create_header=1;
			}
			
			for($i=0;$i<$currcol;$i++){
				$column = PHPExcel_Cell::stringFromColumnIndex($i);
				
				$sheet->getColumnDimension($column)->setAutoSize(true);
				
			}
			
			$sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);
			
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
		
		public function import(){
			
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS SUBDEALER";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." MS SUBDEALER import";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$urls = $this->SubDealerModel->GetMDMarketSurveyUrl();
			
			// print_r($urls);die;
			
			foreach($urls as $row){
				$file = file_get_contents($row->GoogleSheetUrl);
				
				$inputFileName = 'tempfile.xlsx';
				file_put_contents($inputFileName, $file);
				$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
				$spreadsheet = $reader->load($inputFileName);
				$sheetData = $spreadsheet->getActiveSheet()->toArray();
				
				// echo "<pre>";
				// print_r($sheetData);
				// echo "</pre>";
				
				$cols = $sheetData[0];
				
				$idx_foto = array_search("Foto Tampak DEPAN TOKO", $cols);
				$idx_tujuanGForm = array_search("Tujuan Penggunaan Google Form", $cols);
				
				for($i=1;$i<count($sheetData);$i++) {
					$SubDealerId = 0;
					$SubDealer = $sheetData[$i];

					$TujuanGForm = strtoupper($SubDealer[$idx_tujuanGForm]);
					if ($TujuanGForm=="FOTO BUKTI SCAN QR CODE" || $TujuanGForm=="FOTO DISPLAY PRODUK") {
						$IsExists = $this->SubDealerModel->CheckDataExists($SubDealer, $cols, $TujuanGForm);
						if ($IsExists==false) {
							$SimpanDataOther = $this->SubDealerModel->TambahDataOther($SubDealer, $cols);
						}
					} else {
						for($j=0;$j<count($SubDealer);$j++){
							//if($j==$idx_foto){ // jika kolom foto, maka jangan dikapital textnya, karena link foto nya case sensitive,
							if (strtoupper(substr($cols[$j],0,4))=="FOTO") {
								$SubDealer[$j] = ($SubDealer[$j]==null)?"":$SubDealer[$j];
							}
							else{
								$SubDealer[$j] = strtoupper(($SubDealer[$j]==null)?"":$SubDealer[$j]);
							}
						}
						
						$DataSubDealer = $this->SubDealerModel->GetSubDealer($SubDealer, $cols);
						if ($DataSubDealer==null) {
							// echo("Tambah SubDealer<br>");
							
							$SimpanSubDealer = $this->SubDealerModel->TambahSubDealer($SubDealer, $cols);
							$DataSubDealer = $this->SubDealerModel->GetSubDealer($SubDealer, $cols);
							$SubDealerId = $DataSubDealer->SubDealerId;
							// echo("SubDealer ID#".$SubDealerId."<br>");
						} else {
							$SimpanSubDealer = $this->SubDealerModel->RubahSubDealer($SubDealer, $cols); // Aliat 7-Sept-2021
							$SubDealerId = $DataSubDealer->SubDealerId;
							// echo("Rubah SubDealer ID#".$SubDealerId."<br>");
						}
						// die("SubDealerId : ".$SubDealerId);	
						
						$DataMarketSurvey = $this->SubDealerModel->GetDataMarketSurvey($SubDealerId, $SubDealer, $cols);
						// echo("MarketSurvey : <br>".json_encode($DataMarketSurvey)."<br>");
						if ($DataMarketSurvey==null) {
							// echo("Tambah Market Survey<br><br>");
							$SimpanMarketSurvey = $this->SubDealerModel->TambahDataMarketSurvey($SubDealerId, $SubDealer, $cols);
						} else {
							// echo("Rubah Market Survey ID#".$DataMarketSurvey->DataSurveyId."<br><br>");
							$SimpanMarketSurvey = $this->SubDealerModel->RubahDataMarketSurvey($SubDealerId, $SubDealer, $cols);
						}
					}
				}
				
				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				unlink($inputFileName);
			}
		}
		
		public function import_old(){
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS SUBDEALER";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." MS SUBDEALER import_old";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$post = $this->PopulatePost();
			$import_count = 0;
			
			$file_mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			if(isset($_FILES['upload_file']['name']) && in_array($_FILES['upload_file']['type'], $file_mimes)) {
				$arr_file = explode('.', $_FILES['upload_file']['name']);
				$extension = end($arr_file);
				if('csv' == $extension){
					$reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
					} else {
					$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
				}
				$spreadsheet = $reader->load($_FILES['upload_file']['tmp_name']);
				$sheetData = $spreadsheet->getActiveSheet()->toArray();
				// echo "<pre>";
				// print_r($sheetData);
				// echo("<br>");
				$cols = $sheetData[0];
				// for($i=0;$i<count($cols);$i++){
				// 	$cols[$i] = strtoupper($cols[$i]);
				// }
				
				
				if($cols[0]!='Timestamp'){ // pastikan excel yg diimport adalah benar dari google form. cek apakah kolom pertama adalah 'Timestamp' 
					echo "Format excel tidak sesuai. mohon periksa kembali data yg diimport.";

					// ActivityLog Update FAILED
					$params['Remarks']="FAILED - gagal diimport";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);

					die;
				}
				
				for($i=1;$i<count($sheetData);$i++) {
					$SubDealerId = 0;
					$SubDealer = $sheetData[$i];
					for($j=0;$j<count($SubDealer);$j++){
						$SubDealer[$j] = strtoupper(($SubDealer[$j]==null)?"":$SubDealer[$j]);
					}
					// echo("SubDealer : <br>".json_encode($SubDealer)."<br>");
					
					$awal = strtotime($post["dp1"]);
					$akhir = strtotime($post["dp2"]);
					$timestamp = strtotime($SubDealer[0]); //timestamp : 8/23/2021 15:46:05
					$new_awal = date('m/d/Y',$awal);
					$new_akhir = date('m/d/Y',$akhir);
					$new_timestamp = date('m/d/Y',$timestamp);
					
					if($new_timestamp>=$new_awal && $new_timestamp<=$new_akhir){
						
						$import_count +=1;
						$DataSubDealer = $this->SubDealerModel->GetSubDealer($SubDealer, $cols);
						if ($DataSubDealer==null) {
							// echo("Tambah SubDealer<br>");
							
							$SimpanSubDealer = $this->SubDealerModel->TambahSubDealer($SubDealer, $cols);
							$DataSubDealer = $this->SubDealerModel->GetSubDealer($SubDealer, $cols);
							$SubDealerId = $DataSubDealer->SubDealerId;
							// echo("SubDealer ID#".$SubDealerId."<br>");
							} else {
							$SubDealerId = $DataSubDealer->SubDealerId;
							// echo("Rubah SubDealer ID#".$SubDealerId."<br>");
						}
						// die("SubDealerId : ".$SubDealerId);	
						
						$DataMarketSurvey = $this->SubDealerModel->GetDataMarketSurvey($SubDealerId, $SubDealer, $cols);
						// echo("MarketSurvey : <br>".json_encode($DataMarketSurvey)."<br>");
						if ($DataMarketSurvey==null) {
							// echo("Tambah Market Survey<br><br>");
							$SimpanMarketSurvey = $this->SubDealerModel->TambahDataMarketSurvey($SubDealerId, $SubDealer, $cols);
							} else {
							// echo("Rubah Market Survey ID#".$DataMarketSurvey->DataSurveyId."<br><br>");
							$SimpanMarketSurvey = $this->SubDealerModel->RubahDataMarketSurvey($SubDealerId, $SubDealer, $cols);
						}
					}
					
				}
			}
			if($import_count>0){
				// ActivityLog Update SUCCESS
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				echo "Data berhasil diimport.";
			}
			else{
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - gagal diimport";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				echo "Data gagal diimport. Silahkan periksa data yang diimport dan tanggal yang dipilih.";
			}
		}
		
		public function FotoDisplay(){
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS SUBDEALER";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." MS SUBDEALER FotoDisplay";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			// $cabang = $this->BranchModel->GetList();
			$cabang = $this->SubDealerModel->GetListCabang();			
			$cabang_nama = "";
			// if($_SESSION["logged_in"]['branch_id']!='JKT'){
				// $find_cabang = array();
				// foreach($cabang as $row){
					// if($row->BranchID==$_SESSION["logged_in"]['branch_id']){
						// $find_cabang[] = $row;
						// $cabang_nama = $row->BranchName;
					// }
				// }
				// $cabang = $find_cabang;
			// }
			
			if($_SESSION["logged_in"]['branch_id']!='JKT'){
				if($cabang){
					$cabang_nama = $cabang[0]->Cabang;
				}
			}
			
			$provinsi = $this->SubDealerModel->GetListProvinsi($cabang_nama); //tambahkan cabang_nama untuk filter provinsi, untuk JKT tampilkan semua.
			$data['cabang'] = $cabang;
			$data['provinsi'] = $provinsi;
			// die(json_encode($data));

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('SubDealerFotoDisplayList',$data);
		}
		
		public function GetListFotoDisplay(){
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS SUBDEALER";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." MS SUBDEALER GetListFotoDisplay";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$post = $this->PopulatePost();
			//die(json_encode($post));
			$dp1 = $post["dp1"];
			$dp2 = $post["dp2"];
			$data = $this->SubDealerModel->GetListFotoDisplay($dp1,$dp2);

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			echo json_encode($data);
		}

		public function ViewFotoDisplay(){
			// die("view");
			$Cabang = $this->input->get('Cabang');
			$NamaMD = $this->input->get('NamaMD');
			// die($NamaMD);
			$fd = $this->input->get('fd');
			$awal = $this->input->get('awal');
			$akhir = $this->input->get('akhir');
			
			$Cabang = urldecode($Cabang);
			$NamaMD = urldecode($NamaMD);
			$awal = urldecode($awal);
			$akhir = urldecode($akhir);
			
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS SUBDEALER";
			$params['TrxID'] = $NamaMD;
			$params['Description'] = $_SESSION["logged_in"]["username"]." MS SUBDEALER ViewFotoDisplay";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$displayCols = $this->SubDealerModel->GetColFotoDisplay();
			$displayPics = $this->SubDealerModel->GetFotoDisplay($Cabang, $NamaMD, $awal, $akhir, $displayCols);
			
			// die(json_encode($displayPics));
			// print_r($sub_dealer);die;
			
			$header = array();
			$header['Cabang'] = $Cabang;
			$header['NamaMD'] = $NamaMD;
			$header['fd'] = $fd;
			$header['awal'] = $awal;
			$header['akhir'] = $akhir;
			
			$data['header'] = $header;
			$data['cols'] = $displayCols;
			$data['data'] = $displayPics;

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('SubDealerFotoDisplay',$data);
		}

		public function ScanQRCode(){
			$cabang = $this->SubDealerModel->GetListCabang();			
			$cabang_nama = "";
			
			if($_SESSION["logged_in"]['branch_id']!='JKT'){
				if($cabang){
					$cabang_nama = $cabang[0]->Cabang;
				}
			}
			
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS SUBDEALER";
			$params['TrxID'] = $cabang_nama;
			$params['Description'] = $_SESSION["logged_in"]["username"]." MS SUBDEALER ScanQRCode";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$provinsi = $this->SubDealerModel->GetListProvinsi($cabang_nama); //tambahkan cabang_nama untuk filter provinsi, untuk JKT tampilkan semua.
			$data['cabang'] = $cabang;
			$data['provinsi'] = $provinsi;

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('SubDealerScanQRCodeList',$data);
		}
		
		public function GetListFotoScanQRCode(){
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS SUBDEALER";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." MS SUBDEALER GetListFotoScanQRCode";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$post = $this->PopulatePost();
			//die(json_encode($post));
			$dp1 = $post["dp1"];
			$dp2 = $post["dp2"];
			$data = $this->SubDealerModel->GetListFotoScanQRCode($dp1,$dp2);

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			
			echo json_encode($data);
		}

		public function ViewFotoScanQRCode(){
			// die("view");
			$Cabang = $this->input->get('Cabang');
			$NamaMD = $this->input->get('NamaMD');
			// die($NamaMD);
			$fd = $this->input->get('fd');
			$awal = $this->input->get('awal');
			$akhir = $this->input->get('akhir');
			
			$Cabang = urldecode($Cabang);
			$NamaMD = urldecode($NamaMD);
			$awal = urldecode($awal);
			$akhir = urldecode($akhir);
			
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MS SUBDEALER";
			$params['TrxID'] = $NamaMD;
			$params['Description'] = $_SESSION["logged_in"]["username"]." MS SUBDEALER GetListFotoScanQRCode";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			//$displayCols = $this->SubDealerModel->GetColFotoDisplay();
			$Pics = $this->SubDealerModel->GetFotoScanQRCode($Cabang, $NamaMD, $awal, $akhir);
			
			// die(json_encode($displayPics));
			// print_r($sub_dealer);die;
			
			$header = array();
			$header['Cabang'] = $Cabang;
			$header['NamaMD'] = $NamaMD;
			$header['fd'] = $fd;
			$header['awal'] = $awal;
			$header['akhir'] = $akhir;
			
			$data['header'] = $header;
			$data['data'] = $Pics;

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			
			$this->RenderView('SubDealerScanQRCode',$data);
		}		
	}																																							