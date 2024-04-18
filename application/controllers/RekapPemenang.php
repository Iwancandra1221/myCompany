<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class RekapPemenang extends MY_Controller 
	{
		public $excel_flag = 0; 
		public function __construct()
		{
			parent::__construct();
			$this->load->model('MasterDbModel');
			// $this->load->model('HelperModel');
			// $this->load->model('GzipDecodeModel');
			// $this->load->helper('FormLibrary');
			// $this->load->library('email');
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
			$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU REKAP PEMENANG ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);


			$api = 'APITES';
			$URL = $this->API_URL."/MsWilayah/GetAllWilayah?api=".$api;
			$curl = curl_init($URL);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_ENCODING, '');
			curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $response; die;
			
			$wilayah = array();
			if($err!=''){
				echo $this->API_URL.' Ambil List Wilayah Gagal. '.$err; die;
			}
			else{
				$res = json_decode($response, true);
				if($res['result']=='sukses'){
					$wilayah = $res['data'];
				}
			}


			// $url = $this->API_URL."/MsWilayah/GetWilayahHO?api=".$api;
			// $getlistWilayahHO = HttpGetRequest($url, $this->API_URL, "Ambil List Wilayah HO");
			// $getlistWilayahHO = json_decode($getlistWilayahHO); 
			
			$data['wilayah'] = $wilayah;  

			$data['title'] = 'REKAP PEMENANG | '.WEBTITLE;
			$data['formDest'] = "RekapPemenang/Excel";
			
			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('RekapPemenangView',$data);
		}
		
		public function Excel()
		{  
			// echo json_encode($_POST); die;
			
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN REKAP PEMENANG";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." PROSES REKAP PEMENANG ";
			$params['Remarks'] = "";
			$params['RemarksDate'] =  'NULL';
			$this->ActivityLogModel->insert_activity($params);

			$data = array();
			$page_title = 'REKAP PEMENANG';
			$data = array();
			$api = 'APITES';	 
			$tgl1 = date('Y-m-d',strtotime($_POST['dp1']));
			$tgl2 = date('Y-m-d',strtotime($_POST['dp2']));
			$wilayah = $_POST['wilayah'];
			
			$URL = $this->API_URL."/RekapPemenang/GetRekapPemenang?api=APITES&wilayah=".$wilayah."&tgl1=".$tgl1."&tgl2=".$tgl2;
			// echo $URL; die;
			
			$curl = curl_init($URL);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_ENCODING, '');
			curl_setopt($curl, CURLOPT_FAILONERROR, '');
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			if ($err) {
				die('Ambil Rekap Pemenang Error: '.$err);
			}
			// echo $response; die;
			
			$res = json_decode($response, true);
			if(count($res['data'])==0){
				// ActivityLog Update FAILED
				$params['Remarks']="FAILED - Data Tidak Ditemukan";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				
				exit('Tidak ada data');
			} 
			
			$spreadsheet = new Spreadsheet(); 
			$sheet = $spreadsheet->getActiveSheet();
			
			$sheet->setCellValue('A1', $page_title);
			$sheet->getStyle('A1')->getFont()->setSize(14);
			$sheet->getStyle('A1')->getFont()->setBold(true);
			
			$sheet->setCellValue('A2', 'Periode');
			$sheet->setCellValue('B2', date('d-M-Y',strtotime($_POST['dp1'])).' sd '.date('d-M-Y',strtotime($_POST['dp2'])));
			$sheet->setCellValue('A3', 'Wilayah');
			$sheet->setCellValue('B3', $_POST['wilayah']);
			$sheet->setCellValue('A4', 'Print Date');
			$sheet->setCellValue('B4', date('d-M-Y H:i:s'));
			$sheet->setCellValue('A5', 'Print By');
			$sheet->setCellValue('B5', $_SESSION['logged_in']['username']);
			
			$currow = 6;
			$curcol = 1;
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'Tanggal');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'No Faktur');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'Kode Pelanggan');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'Nama');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NPWP');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NIK');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'Alamat');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'Email');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'Type');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'Kode Produk');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'Qty');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'Harga Satuan');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'DPP');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'Grossup');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'Tarif');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'Nilai PPh');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'Jenis PPh');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'Keterangan');

			$max_col_name = $sheet->getHighestDataColumn();
			
			$sheet->getStyle("A".$currow.":".$max_col_name.$currow)->getFont()->setBold(true);
			
			$no_faktur='';
			foreach($res['data'] as $row){
				$currow++;
				$curcol = 1;
				if($no_faktur==$row['No_Faktur']){
					$sheet->setCellValueByColumnAndRow(10, $currow, trim($row['Kd_Brg']));
					$sheet->setCellValueByColumnAndRow(11, $currow, $row['Qty']);
					$sheet->setCellValueByColumnAndRow(12, $currow, $row['Harga']);
				}
				else{
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, date('d-M-Y',strtotime($row['Tgl_Faktur'])));
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['No_Faktur']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['Kd_Plg']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['Nama_Pemenang']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['NPWP_Pemenang']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['NIK_Pemenang']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['Alamat_Pemenang']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['Email_Pemenang']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['tipe_faktur']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, trim($row['Kd_Brg']));
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['Qty']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['Harga']);					
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['Total_DPP']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['total_grossup']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, (($row['tarif_pph'])/100));
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['total_pph']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['jenis_pph']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, trim($row['Ket']));
				
				$sheet->getStyle("O".$currow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE);
				}
				
				$no_faktur=$row['No_Faktur'];
			}
			
			$sheet->getStyle('A6:'.$max_col_name.$currow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

			foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
				$sheet->getColumnDimension($col)->setAutoSize(true);
			} 

			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="REKAP_PEMENANG.xlsx"'); 
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