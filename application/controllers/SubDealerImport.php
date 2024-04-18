<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class SubDealerImport extends NS_Controller 
	{
		public function __construct()
		{
			parent::__construct();
			$this->load->model('HelperModel');
			$this->load->helper('FormLibrary');
			$this->load->library('excel');
			$this->load->model('SubDealerModel');
		}
		
		
		public function cekTanggal($array, $cols, $config){
			// return true;
			$CreateDate = date_create($array[array_search("Timestamp", $cols)]);
			$Create_Thn = date_format($CreateDate,"Y");
			$Create_Bln = date_format($CreateDate,"m");
			// echo (date('Y').date('m'))."<br>";
			// echo ($Create_Thn.$Create_Bln)."<br>";

			// $selisih_bulan = (date('Y').date('m'))-($Create_Thn.$Create_Bln);
			// // echo $selisih_bulan;
			// // echo date('d');
			// if( $selisih_bulan == 0){
			// 	return true;
			// }
			// elseif($selisih_bulan == 1 && date('d')<=10){
			// 	return true;
			// }
			// else return false;
			// echo ("Tgl Data:".date("Ymd", strtotime($array[array_search("Timestamp", $cols)]))."<br>".
			// 	  "Tgl Config:".date("Ymd", strtotime($config->LastUpdate))."<br>");
			if (date("Ymd", strtotime($array[array_search("Timestamp", $cols)])) >= date("Ymd", strtotime($config->LastUpdate))) {
				// echo("Lanjut Update Data<br>");
				return true;
			} else {
				// echo("Skip Update Data<br>");
				return false;
			}
		}
		
		public function index(){
			
			$CreatedBy = (ISSET($_SESSION["logged_in"]["username"])) ? $_SESSION["logged_in"]["username"] : 'JOB';
			
			$urls = $this->SubDealerModel->GetMDMarketSurveyUrl();
			
			// print_r($urls);die;
			
			foreach($urls as $row){
				// echo json_encode($row)."<br>";
				
				try {
					$file = file_get_contents($row->GoogleSheetUrl);
					
					
					$inputFileName = 'tempfile.xlsx';
					file_put_contents($inputFileName, $file);
					$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
					$spreadsheet = $reader->load($inputFileName);
					$sheetData = $spreadsheet->getActiveSheet()->toArray();
					
					$cols = $sheetData[0];
					
					// echo(json_encode($cols));
					// echo("<br>");
					// die;
					
					// trim nama kolom untuk hilangkan spasi di ujung text
					foreach($cols as $key => $val){
						$cols[$key] = trim($val);
					}
					
					$idx_foto = array_search("Foto Tampak DEPAN TOKO", $cols);
					$idx_scan_qr_code_pada_aplikasi_msr_toko = array_search("Foto Bukti Scan QR Code pada Aplikasi MSR Toko", $cols);
					
					for($i=1;$i<count($sheetData);$i++){
						
						$SubDealerId = 0;
						$SubDealer = $sheetData[$i];
						// echo(json_encode($SubDealer));
						// echo("<br>");
						
						// skip simpan jika ada Scan QR Code pada Aplikasi MSR Toko
						if($SubDealer[$idx_scan_qr_code_pada_aplikasi_msr_toko]==''){
							if($this->cekTanggal($SubDealer, $cols)){
								
								
								for($j=0;$j<count($SubDealer);$j++){
									if($j==$idx_foto){ // jika kolom foto, maka jangan dikapital textnya, karena link foto nya case sensitive,
										$SubDealer[$j] = ($SubDealer[$j]==null)?"":$SubDealer[$j];
									}
									else{
										$SubDealer[$j] = strtoupper(($SubDealer[$j]==null)?"":$SubDealer[$j]);
									}
									
									
									$SubDealer[$j] = str_replace("'",'"',$SubDealer[$j]); // ubah tanda ' menjadi " supaya bisa tersimpan di database
									
								}
								
								
								$DataSubDealer = $this->SubDealerModel->GetSubDealer($SubDealer, $cols);
								
								if ($DataSubDealer==null) {
									// echo("Tambah SubDealer<br>");
									
									$SimpanSubDealer = $this->SubDealerModel->TambahSubDealer($SubDealer, $cols, $CreatedBy);
									$DataSubDealer = $this->SubDealerModel->GetSubDealer($SubDealer, $cols);
									$SubDealerId = $DataSubDealer->SubDealerId;
									// echo("SubDealer ID#".$SubDealerId."<br>");
									} else {
									// $SimpanSubDealer = $this->SubDealerModel->RubahSubDealer($SubDealer, $cols); // tidak perlu edit lg
									$SubDealerId = $DataSubDealer->SubDealerId;
									// echo("Rubah SubDealer ID#".$SubDealerId."<br>");
								}
								// die("SubDealerId : ".$SubDealerId);	
								
								$DataMarketSurvey = $this->SubDealerModel->GetDataMarketSurvey($SubDealerId, $SubDealer, $cols);
								// echo("MarketSurvey : <br>".json_encode($DataMarketSurvey)."<br>");
								if ($DataMarketSurvey==null) {
									// echo("Tambah Market Survey<br><br>");
									$SimpanMarketSurvey = $this->SubDealerModel->TambahDataMarketSurvey($SubDealerId, $SubDealer, $cols, $CreatedBy);
									} else {
									// echo("Rubah Market Survey ID#".$DataMarketSurvey->DataSurveyId."<br><br>");
									// $SimpanMarketSurvey = $this->SubDealerModel->RubahDataMarketSurvey($SubDealerId, $SubDealer, $cols); // tidak perlu edit lg
								}
								
							}
						}
						
					}
					
					unlink($inputFileName);
					
				}
				catch (Exception $e) {
					echo 'Caught exception: ',  $e->getMessage(), "\n";
				}
			}
		}
		
		public function import($zone=1, $branch=""){
			set_time_limit(60);
			$CreatedBy = (ISSET($_SESSION["logged_in"]["username"])) ? $_SESSION["logged_in"]["username"] : 'JOB';
			
			$urls = $this->SubDealerModel->GetMDMarketSurveyUrl($zone, $branch);
			// die(json_encode($urls));
			// print_r($urls);die;
			
			foreach($urls as $row){
				echo json_encode($row)."<br>";
				
				try {
					$PreviousLastRow = $row->LastRowNumber;
					// die("Previous: ".$PreviousLastRow);
					// die($row->GoogleSheetUrl);

					$start = date("d-M-Y H:i:s");
					$file = file_get_contents($row->GoogleSheetUrl);
					// die(json_encode($file));
					
					$inputFileName = 'tempfile.xlsx';
					file_put_contents($inputFileName, $file);
					$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
					$spreadsheet = $reader->load($inputFileName);
					$sheetData = $spreadsheet->getActiveSheet()->toArray();
					
					$cols = $sheetData[0];
					// die(json_encode($cols));
					// echo(json_encode($cols));
					// echo("<br>");
					
					// trim nama kolom untuk hilangkan spasi di ujung text
					foreach($cols as $key => $val){
						$cols[$key] = trim($val);
					}
					echo(json_encode($cols)."<br><br>");
					
					$idx_NamaMD = array_search("Nama MD", $cols);
					$idx_foto = array_search("Foto Tampak DEPAN TOKO", $cols);
					$idx_scan_qr_code_pada_aplikasi_msr_toko = array_search("Foto Bukti Scan QR Code pada Aplikasi MSR Toko", $cols);
					$idx_tujuanGForm = array_search("Tujuan Penggunaan Google Form", $cols);
					
					echo("Jumlah Data: ".count($sheetData)."<br>");
					$TglMax = date("Y-m-d");

					echo("Start #".$PreviousLastRow." - End #".count($sheetData)."<br>");

					for($i=$PreviousLastRow;$i<count($sheetData);$i++){
						$SubDealerId = 0;
						$SubDealer = $sheetData[$i];
						echo("<b>[".$i."]</b><br>".json_encode($SubDealer)."<br>");

						// echo("Data #".$i."<br>");
						// skip simpan jika ada Scan QR Code pada Aplikasi MSR Toko
						// if($SubDealer[$idx_scan_qr_code_pada_aplikasi_msr_toko]==''){
						if($this->cekTanggal($SubDealer, $cols, $row)){

							for($j=0;$j<count($SubDealer);$j++){
								// if($j==$idx_foto){ // jika kolom foto, maka jangan dikapital textnya, karena link foto nya case sensitive,
								if (strtoupper(substr($cols[$j],0,4))=="FOTO") {
									$SubDealer[$j] = ($SubDealer[$j]==null)?"":$SubDealer[$j];
								}
								else{
									$SubDealer[$j] = strtoupper(($SubDealer[$j]==null)?"":$SubDealer[$j]);
								}
								
								$SubDealer[$j] = str_replace("'",'"',$SubDealer[$j]); // ubah tanda ' menjadi " supaya bisa tersimpan di database
							}

							$NamaMD = strtoupper($SubDealer[$idx_NamaMD]);
							echo("Nama MD : ".$NamaMD."<br>");
							$TujuanGForm = strtoupper($SubDealer[$idx_tujuanGForm]);
							echo("Tujuan GForm : ".$TujuanGForm."<br>");
							$Tgl = $SubDealer[array_search("Timestamp", $cols)];
							echo("Timestamp : ".$Tgl."<br>");

							if ($TujuanGForm=="FOTO BUKTI SCAN QR CODE" || $TujuanGForm=="FOTO DISPLAY PRODUK" || $TujuanGForm=="FOTO DISPLAY BARANG") {
								echo("#".$i." Check Data Sudah Ada/Belum<br>");
								$IsExists = $this->SubDealerModel->CheckDataExists($SubDealer, $cols, $TujuanGForm);
								if ($IsExists==false) {
									echo("#".$i." Tambah Other<br>");
									$SimpanDataOther = $this->SubDealerModel->TambahDataOther($SubDealer, $cols, $CreatedBy);
								} else {
									echo("#".$i." Rubah Other<br>");
									$RubahDataOther =$this->SubDealerModel->RubahDataOther($SubDealer,$cols, $CreatedBy);
								}

							} else {							
								echo("#".$i." Check SubDealer Sudah Ada/Belum<br>");
								// die(json_encode($SubDealer));
								$DataSubDealer = $this->SubDealerModel->GetSubDealer($SubDealer, $cols);								
								if ($DataSubDealer==null) {
									// die("null");
									echo("#".$i." Tambah SubDealer<br>");
									$SimpanSubDealer = $this->SubDealerModel->TambahSubDealer($SubDealer, $cols, $CreatedBy);
									if ($SimpanSubDealer==true) {
										echo("#".$i." Tambah SubDealer Berhasil<br>");
										$DataSubDealer = $this->SubDealerModel->GetSubDealer($SubDealer, $cols);
										// die(json_encode($DataSubDealer));
										$SubDealerId = $DataSubDealer->SubDealerId;
										// echo("SubDealer ID#".$SubDealerId."<br>");
									} else {
										echo("#".$i." Tambah SubDealer Gagal<br>");
									}
								} else {
									echo("#".$i." SubDealer Sudah Ada<br>");
									// die("not null");
									// $SimpanSubDealer = $this->SubDealerModel->RubahSubDealer($SubDealer, $cols); // tidak perlu edit lg
									$SubDealerId = $DataSubDealer->SubDealerId;
									// echo("Rubah SubDealer ID#".$SubDealerId."<br>");
								}
								// die("SubDealerId : ".$SubDealerId);	
								
								echo("#".$i." Cek Data Market Survey Sudah Ada/Belum<br>");
								$DataMarketSurvey = $this->SubDealerModel->GetDataMarketSurvey($SubDealerId, $SubDealer, $cols);
								// echo("MarketSurvey : <br>".json_encode($DataMarketSurvey)."<br>");
								if ($DataMarketSurvey==null) {
									echo("#".$i." Tambah Market Survey<br>");
									$SimpanMarketSurvey = $this->SubDealerModel->TambahDataMarketSurvey($SubDealerId, $SubDealer, $cols, $CreatedBy);
								} else {
									echo("#".$i." Data Market Survey Sudah Ada<br>");
									// echo("Rubah Market Survey ID#".$DataMarketSurvey->DataSurveyId."<br><br>");
									// $SimpanMarketSurvey = $this->SubDealerModel->RubahDataMarketSurvey($SubDealerId, $SubDealer, $cols); // tidak perlu edit lg
								}
							}


							if ($i==1) {
								$TglMax = $Tgl;
								$updConfig = $this->SubDealerModel->RubahLastUpdate($row, $TglMax, $i);
							} else if (date("Ymd", strtotime($Tgl))>date("Ymd", strtotime($TglMax))) {
								$TglMax = $Tgl;
								$updConfig = $this->SubDealerModel->RubahLastUpdate($row, $TglMax, $i);
							}
						} else {
							echo("[".$i."] SKIP INSERT/UPDATE<br><br>");
						}
					}
					
					$updConfig = $this->SubDealerModel->RubahLastUpdate($row, $TglMax, $i);
					unlink($inputFileName);
					echo("Import Selesai<br>");
					echo("Start: ".$start."<br>End: ".date("d-M-Y H:i:s"));
				} catch (Exception $e) {
					echo 'Caught exception: ',  $e->getMessage(), "\n";
				}
			}
		}
		
		
		}																																														