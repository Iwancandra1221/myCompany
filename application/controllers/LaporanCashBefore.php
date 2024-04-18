<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class LaporanCashBefore extends MY_Controller 
	{
		public $excel_flag = 0;
		public $maxtimeout = 900;
		public $memorylimit = '256m';
		
		public function __construct()
		{
			parent::__construct();
			$this->load->model('BranchModel');
			$this->load->library('form_validation');
		}
		
		public function index()
		{
			$data = array();
			set_time_limit($this->maxtimeout);
			$branches = $this->BranchModel->GetsByUser($_SESSION['logged_in']['useremail']);
			$data['title'] = 'Laporan Cash Before| '.WEBTITLE;
			$data['branches'] = $branches;
			$this->RenderView('LaporanCashBeforeFormView',$data);
			
			$LogDate = date("Y-m-d H:i:s");
			$this->Logs_insert($LogDate,'MENAMPILKAN MENU CASH BEFORE');
		}

		private function _postRequest($url,$data){

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$server_output = curl_exec($ch);
			$result = json_decode($server_output, true);
			return $result;
		}

		private function Logs_insert($LogDate='',$description=''){
			$params = array();   
			$params['LogDate'] = $LogDate;
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN CASH BEFORE";
			$params['TrxID'] = date_format(date_create($LogDate),'YmdHis');
			$params['Description'] = $_SESSION["logged_in"]["username"]." ".$description;
			$params['Remarks'] = "";
			$params['RemarksDate'] = 'NULL';
			$this->ActivityLogModel->insert_activity($params);
		}

		private function Logs_Update($LogDate='',$remarks='',$description=''){
			$params = array();   
			$params['LogDate'] = $LogDate;
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "LAPORAN CASH BEFORE";
			$params['TrxID'] = date_format(date_create($LogDate),'YmdHis');
			$params['Description'] = $_SESSION["logged_in"]["username"]." ".$description;
			$params['Remarks']=$remarks;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
		}
		
		
		public function Proses()
		{
			$submit = $this->input->post('submit');
			if ($submit != '') 
			{
				$LogDate = date("Y-m-d H:i:s");
				$this->Logs_insert($LogDate, 'PROSES LAPORAN CASH BEFORE');
				$this->form_validation->set_rules('date1','Tanggal Awal Bayar','required');
				$this->form_validation->set_rules('date2','Tanggal Akhir Bayar','required');
				$this->form_validation->set_rules('cabang','Cabang','required');
				if($this->form_validation->run())
				{
					$data = array();
		
					$data["date1"] =  date_format(date_create_from_format("d/m/Y", $_POST["date1"]),'Y-m-d');
					$data["date2"] =  date_format(date_create_from_format("d/m/Y", $_POST["date2"]),'Y-m-d');
					$cabang = explode('-',$_POST['cabang']);
					$data["cabang"] = $cabang[0];
					$data["cabang_name"] = $cabang[1];

					$dataReport['rekap'] = $this->getDataReport($data);
					$dataReport['data'] = $data;
					//die(json_encode($dataReport['rekap']['Sales']));
					if (count($dataReport['rekap']['Sales']) > 0 || count($dataReport['rekap']['Service']) > 0 || count($dataReport['rekap']['Dealer']) > 0 ) {
						switch($submit){
							case 'EXCEL':
							
								$this->Logs_Update($LogDate,'SUCCESS', 'MENAMPILKAN DATA LAPORAN CASH BEFORE');

								$this->load->view('template_xls/LaporanCashBeforeXls', $dataReport);
								
								break;	
						}
					} else {
						redirect("LaporanCashBefore");
					}
				} else {
	
					$this->Logs_Update($LogDate, 'FAILED - Invalid Input"', 'MENAMPILKAN DATA LAPORAN CASH BEFORE');

					$_SESSION["error"] = "Invalid Input";
					redirect("LaporanCashBefore");
				}
			} else {
				redirect("LaporanCashBefore");
			}
		}

		public function getDataReport($params)
		{

			$params['api'] = 'APITES';
			if (!isset($_SESSION["conn"])) {
				redirect("ConnectDB");
			}
			$url = $_SESSION["conn"]->AlamatWebService;
			$params['svr'] = $_SESSION["conn"]->Server;
			$params['db']  = $_SESSION["conn"]->Database;
			$data = json_encode($params);

			$url .= API_BKT . '/LaporanCashBefore/GetReport';

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => $this->maxtimeout,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $data,
				CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));		
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			// die($response."<br><br>");

			return json_decode($response, true);
			
		}

	}																																			