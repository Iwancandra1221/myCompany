<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UniqueCodeGeneratorV2 extends MY_Controller {

	// public $SALT = "GARAM";
	// public $SALTv = "1";
	// public $HashLength = 3;
	// // public $MishirinURL = 'https://asia-southeast2-mishirin-726d8.cloudfunctions.net'; // DEVELOPMENT  
	// public $MishirinURL = 'https://australia-southeast1-bhakti-mobile-27ba4.cloudfunctions.net'; // PRODUCTION

	
	function __construct()
	{
		parent::__construct();
		$this->load->model('HelperModel');
		$this->load->helper('FormLibrary');
		$this->load->model('UniqueCodeGeneratorModel', 'UCGModel');
		$this->load->library("email");
		$this->load->helper('url');
		$this->load->library('excel');
		$this->secretKey = base64_encode(MISHIRIN_KEY);
		$this->controller = 'UniqueCodeGeneratorV2';
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	private function _postRequestJson($url,$data){

		$json = json_encode($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

		$headers = array(
		    'Content-Type:application/json',
       	 	'Content-Length: ' . strlen($json)
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);

		curl_close($ch);

		$server_output = json_decode($server_output,true);
		return $server_output;
	}

	public function index()
	{
        if($this->uri->segment(2) != '')
	    	$ctrname = $this->uri->segment(1)."/".$this->uri->segment(2);
        else
	       	$ctrname = $this->uri->segment(1);

    	$data['access'] = $this->ModuleModel->getDetail($ctrname,$_SESSION['role']);
    	$data["version"] = "V2";
    	// $data["SNList"] = $this->UCGModel->GetListSN();
    	// $Blacklist = $this->getBlacklistMishirin();
    	// // die(json_encode($Blacklist));
    	// if (!isset($Blacklist->data)) {
    	// 	// die("tidak ada");
    	// 	$data["Blacklist"] = array();
    	// } else {
    	// 	// die("ada");
    	// 	$data["Blacklist"] = $Blacklist->data;
    	// }
    	// die(json_encode($data["Blacklist"]));
	    // $this->RenderView("ReportResultView", $data);
    	$data['controller'] = $this->controller;
		$this->RenderView('UniqueCodeGenerateList',$data);
	}
	
	public function getBlacklistMishirin()
	{
		$data = array("secretKey"=>$this->secretKey);
		// echo json_encode($data);die;

		$json_data = json_encode($data);

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => MISHIRIN_URL.'/getBlacklist',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 1000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => $json_data,
		CURLOPT_HTTPHEADER => array('Content-Type: application/json'), 
		));

		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

		$result = curl_exec($curl);
		$err = curl_error($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		print_r(json_encode(json_decode($result)));
	}


	
	public function Hide($UCID, $ket='')
	{	
		$username = $_SESSION['logged_in']['username'];
		if (UNIQUECODE_SALTV > "1") {

			$where = array(
				'LogId' => $UCID,
			);
			$getLog = $this->UCGModel->getLogUniqueCode($where);
			if($getLog!=null){

				$getLog = $getLog[0];

				$url = MISHIRIN_URL."/getFactoryKartuNoSeri";
				$payload = array(
					"secretKey"=> $this->secretKey,
				);
				$payload['filters'][] = array(
					'field' => 'kode_brg',
					'value' => $getLog['ProductID']
				);
				$payload['filters'][] = array(
					'field' => 'range_awal',
					'value' => $getLog['SerialNoMin']
				);
				$payload['filters'][] = array(
					'field' => 'range_akhir',
					'value' => $getLog['SerialNoMax']
				);
				$getApi = $this->_postRequestJson($url,$payload);

				$factory_kns_id = '';
				if($getApi!=null && isset($getApi['data']) && $getApi['data']!=null){
					$factory_kns_id = $getApi['data'][0]['factory_kns_id'];
					// foreach($getApi['data'] as $value){
					// 	if($value['kode_brg']==$getLog['ProductID'] && $value['range_awal']==$getLog['SerialNoMin'] && $value['range_akhir']==$getLog['SerialNoMax']){
					// 		$factory_kns_id = $value['factory_kns_id'];
					// 		break;
					// 	}
					// }
					if($factory_kns_id!=''){
						// echo 'data ditemukan '.$factory_kns_id;
						//delete factory kartu no seri
						$url = MISHIRIN_URL."/deleteFactoryKartuNoSeri";
						$payload = array(
							"secretKey" => $this->secretKey,
						    "factory_kns_id" => $factory_kns_id,
						    "created_by"=> $username
						);
						$getApi = $this->_postRequestJson($url,$payload);
						//print_r($getApi);
						if($getApi!=null && isset($getApi['message']) && $getApi['message']=='Kartu No Seri Berhasil Dihapus!'){
							//sukses delete, update ke Log_UniqueCode
							$where = array(
								'ProductID' => $getLog['ProductID'],
								'SerialNoMin' => $getLog['SerialNoMin'],
								'SerialNoMax' => $getLog['SerialNoMax'],
							);
							$data = array(
								'send_api_mishirin' => 0,
							);
							$result = $this->UCGModel->updateLogUniqueCode($where,$data);
							if($result){
								//echo 'sukses';
							}
						}
					}
				}
			}
		}
        $hide= $this->UCGModel->Hide($UCID,$ket);
		echo $hide;
	}

	public function GenerateForm()
	{
        if($this->uri->segment(2) != '')
	    	$ctrname = $this->uri->segment(1)."/".$this->uri->segment(2);
        else
	       	$ctrname = $this->uri->segment(1);

    	$data['access'] = $this->ModuleModel->getDetail($ctrname,$_SESSION['role']);
    	$data["products"] = array();

    	$url = $this->API_URL."/MsBarang/GetPurchaseProductList?api=APITES&mishirin=1";
    	$barangs = json_decode(file_get_contents($url), true);
    	// die(json_encode($barangs));
    	if ($barangs!=null && $barangs["result"]=="sukses") {
    		$BRGS = $barangs["data"];	
			for($i=0;$i<count($BRGS);$i++) {
				array_push($data["products"], trim($BRGS[$i]["KD_BRG"])." | ".trim($BRGS[$i]["MERK"])." | ".trim($BRGS[$i]["JNS_BRG"]));
			}
    	}
    	// die (json_encode($data["products"]));
		$this->RenderView('UniqueCodeGenerateForm',$data);
	}

	public function CheckProductId()
	{
		$post = $this->PopulatePost();
		$url = $this->API_URL."/MsBarang/Get?api=APITES&brg=".urlencode($post["productId"]);
		$check = json_decode(file_get_contents($url), true);
		if ($check!=null && $check["result"]=="sukses") {
			echo(json_encode(array("result"=>"SUCCESS", "product"=>$check["data"])));
		} else {
			echo(json_encode(array("result"=>"FAILED", "product"=>array())));
		}
	}

	public function CheckLog()
	{
		$post = $this->PopulatePost();		
		$check = $this->UCGModel->checkLog($post);
		// die(json_encode($check));
		echo(json_encode($check));
	}

	public function Generate()
	{
    	$post = $this->PopulatePost();
    	$USERNAME = $post["username"];
    	$PRODUCTID = trim(strtoupper($post["productcode"]));
    	$SERIALNOMIN = trim(strtoupper($post["serialnumber-min"]));
    	$SERIALNOMAX = trim(strtoupper($post["serialnumber-max"]));
    	$SERIALNO = "";
    	$SERIALNOLEN = 0;

    	$TGL = $this->encryptDate(date("d"));
    	$BL = $this->encryptMonth(date("m"));
    	$TH = $this->encryptYear(date("Y"));

    	$NO = 0;

        $data = array();
		
		$warna_table_header = 'f2f2f2';
		$warna_table_footer = 'b7dee8';
		$warna_table_grandtotal = '7CFC00';

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);		
		
		// die("here");

		$WORKBOOK = "SERIAL NUMBER - UNIQUE KEY";
		$sheet->setTitle($WORKBOOK);
		$sheet->setCellValue('A1', $WORKBOOK);
		$sheet->getStyle('A1')->getFont()->setSize(12);
		$sheet->getStyle("A1")->getFont()->setBold(true);
		$sheet->setCellValue('A2', 'GENERATED ON : ');
		$sheet->setCellValue('B2', date("d-M-Y h:i:s"));
		$sheet->setCellValue('A3', 'GENERATED BY : ');
		$sheet->setCellValue('B3', $USERNAME);
		$sheet->getStyle("B2:B3")->getFont()->setBold(true);
		
		$currcol = 1;
		$currrow = 5;
		$startrow = $currrow;
		
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No');
		$sheet->getColumnDimension('A')->setWidth(15);
		$currcol += 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'ProductID');
		$sheet->getColumnDimension('B')->setWidth(30);
		$currcol += 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SerialNo');
		$sheet->getColumnDimension('C')->setWidth(15);
		$currcol += 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'UniqueCode');
		$sheet->getColumnDimension('D')->setWidth(40);
		$currcol += 1;
		$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QRCode');
		$sheet->getColumnDimension('E')->setWidth(100);
		$sheet->getStyle("A".$startrow.":E".$startrow)->getFont()->setBold(true);
		
		$currrow += 1;
		$SERIALNOLEN = strlen($SERIALNOMIN);
		// die("here");

		for($i=$SERIALNOMIN; $i<=$SERIALNOMAX; $i++){
			$NO += 1;
			$SERIALNO = (string)$i;
			while (strlen($SERIALNO) < $SERIALNOLEN) {
				$SERIALNO = "0".$SERIALNO;
			}
			if(UNIQUECODE_SALTV==1){
				$MD5 = md5($PRODUCTID.UNIQUECODE_SALT.$SERIALNO);
				$MD5 = strtoupper(substr($MD5,1,UNIQUECODE_HASHLENGTH));
				$MD5 = UNIQUECODE_SALTV.$MD5.$BL.$TH;
			}
			else{

				$MD5 = md5($PRODUCTID.UNIQUECODE_SALT.$SERIALNO);
				$MD5 = strtoupper(substr($MD5,1,UNIQUECODE_HASHLENGTH));
				$MD5 = UNIQUECODE_SALTV.$MD5.$BL.$TH.$TGL;


			}
			

			$QRCODE = $PRODUCTID." | ".$SERIALNO." | ".$MD5;
			$currcol = 1;

			log_message('error','QRCODE '.$QRCODE);

			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $NO);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $PRODUCTID);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SERIALNO);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $MD5);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $QRCODE);
			$currcol += 1;

			$currrow += 1;
		}
		//kirim yang di input dari form ke team ko aming
		$url = MISHIRIN_URL."/insertFactoryKartuNoSeri";
		$payload = array(
			"secretKey"=> $this->secretKey,
			'kode_brg' => $PRODUCTID,
			'range_awal' => $SERIALNOMIN,
			'range_akhir' => $SERIALNOMAX,
			'message' => '',
			'message_internal' => '',
			'created_by' => $USERNAME,
		);
		$result = $this->_postRequestJson($url,$payload);
		if($result!=null && isset($result['message']) && $result['message']=='Insert Berhasil!'){
			//insert sukses
			$where = array(
				'ProductID' => $PRODUCTID,
				'SerialNoMin' => $SERIALNOMIN,
				'SerialNoMax' => $SERIALNOMAX,
			);
			$data = array(
				'send_api_mishirin' => 1,
			);
			$this->UCGModel->updateLogUniqueCode($where,$data);
		}

		$lastrow = $currrow - 1;		
		// die("here");
		
		// border
		$styleArray = [
		'borders' => [
		'allBorders' => [
		'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
		],
		],
		];
		$sheet ->getStyle("A".$startrow.':E'.$lastrow)->applyFromArray($styleArray);
		
		$sheet->setSelectedCell('A1');
		$filename='UniqueCode ['.date('Ymdhis').']'; //save our workbook as this file name
		$ResponseCode = 0;
		$ResponseText = "SUCCESSFUL";

		try {
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 
		} catch(Exception $e) {
			$ResponseCode = 1;
		  	$ResponseText = $e->getMessage();
		}

		$this->UCGModel->saveLog($post, UNIQUECODE_SALTV, $ResponseCode, $ResponseText);
		exit();		
	}

	public function importExcel()
	{
    	$post = $this->PopulatePost();
    	$USERNAME = $post["username2"];
    	$IDXSHEET = $post["idx-sheet"];
    	$IDXROW = $post["idx-start-row"];
    	$IDXCOLSN = $post["idx-col-sn"];
    	$IDXCOLPRODUCT = $post["idx-col-product"];

    	$IDXSHEET = $IDXSHEET - 1;
    	$IDXCOLSN = $IDXCOLSN - 1;
    	$IDXCOLPRODUCT = $IDXCOLPRODUCT - 1;

    	$TGL = $this->encryptDate(date("d"));
    	$BL = $this->encryptMonth(date("m"));
    	$TH = $this->encryptYear(date("Y"));

    	$PRODUCTID = "";
    	$SERIALNOMIN = "";
    	$SERIALNOMAX = "";
    	$SERIALNO = "";
    	$SERIALNOLEN = 0;

		$FILENAME = $_FILES['file']['name'];
        $SAVED_FILENAME = date("Ymdhis")."_".$FILENAME;

        $inputFileName = './assets/'.$SAVED_FILENAME;
        $sheetData = array();

		$file_mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		if(isset($_FILES['file']['name']) && in_array($_FILES['file']['type'], $file_mimes)) {
			$arr_file = explode('.', $_FILES['file']['name']);
			$extension = end($arr_file);

			if('csv' == $extension){
				$reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
			} else {
				$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
			}
			
			$x = $reader->load($_FILES['file']['tmp_name']);
			$sourceSheet = $x->getSheet($IDXSHEET);
			$sheetData = $sourceSheet->toArray();
		} 

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);				
		$WORKBOOK = "SERIAL NUMBER - UNIQUE KEY";
		$sheet->setTitle($WORKBOOK);
		$sheet->setCellValue('A1', $WORKBOOK);
		$sheet->getStyle('A1')->getFont()->setSize(12);
		$sheet->getStyle("A1")->getFont()->setBold(true);
		$sheet->setCellValue('A2', 'GENERATED ON : ');
		$sheet->setCellValue('B2', date("d-M-Y h:i:s"));
		$sheet->setCellValue('A3', 'GENERATED BY : ');
		$sheet->setCellValue('B3', $USERNAME);
		$sheet->getStyle("B2:B3")->getFont()->setBold(true);
 		
    	$NO = 0;
        $data = array();

    	$currrow = 5;
    	$currcol = 1;
    	$startrow = $currrow;

        $highestRow = $sourceSheet->getHighestRow();
        $highestColumn = $sourceSheet->getHighestColumn();

        // echo("Start Row: ".$IDXROW."<br>");
        // echo("Highest Row: ".$highestRow."<br>");
        // echo("Highest Col: ".$highestColumn."<br>");
        // die("-");

        if ($highestRow >= $IDXROW) {

			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No');
			$sheet->getColumnDimension('A')->setWidth(15);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'ProductID');
			$sheet->getColumnDimension('B')->setWidth(30);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'SerialNo');
			$sheet->getColumnDimension('C')->setWidth(15);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'UniqueCode');
			$sheet->getColumnDimension('D')->setWidth(40);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QRCode');
			$sheet->getColumnDimension('E')->setWidth(100);

			$sheet->getStyle("A".$startrow.":E".$startrow)->getFont()->setBold(true);

			$currrow += 1;
        }

        $IDXROW = $IDXROW - 1;
        $SERIALNOLEN = 0;

        for ($row = $IDXROW; $row < $highestRow; $row++){                  //  Read a row of data into an array                 
            $rowData = $sheetData[$row];
            
            $SERIALNO = trim(strtoupper($rowData[$IDXCOLSN]));
            $PRODUCTID = "";
            if ($IDXCOLPRODUCT>=0) {
            	$PRODUCTID = trim(strtoupper($rowData[$IDXCOLPRODUCT]));
            }
            if ($row==$IDXROW) {
            	$SERIALNOMIN = $SERIALNO;
    			$SERIALNOLEN = strlen($SERIALNOMIN);
            }
            if ($row==$highestRow) {
            	$SERIALNOMAX = $SERIALNO;
            }

			$NO += 1;

			// $MD5 = md5($PRODUCTID.UNIQUECODE_SALT.$SERIALNO);
			// $MD5 = strtoupper(substr($MD5,1,UNIQUECODE_HASHLENGTH));
			// $MD5 = UNIQUECODE_SALTV.$MD5.$BL.$TH;
			// $QRCODE = $PRODUCTID." | ".$SERIALNO." | ".$MD5;
			if(UNIQUECODE_SALTV==1){
				$MD5 = md5($PRODUCTID.UNIQUECODE_SALT.$SERIALNO);
				$MD5 = strtoupper(substr($MD5,1,UNIQUECODE_HASHLENGTH));
				$MD5 = UNIQUECODE_SALTV.$MD5.$BL.$TH;
			}
			else{

				$MD5 = md5($PRODUCTID.UNIQUECODE_SALT.$SERIALNO);
				$MD5 = strtoupper(substr($MD5,1,UNIQUECODE_HASHLENGTH));
				$MD5 = UNIQUECODE_SALTV.$MD5.$BL.$TH.$TGL;
			}

			// echo("<br>");
			// echo("No :".$NO."<br>");
			// echo("ProductID :".$PRODUCTID."<br>");
			// echo("SerialNo :".$SERIALNO."<br>");
			// echo("UniqueCode :".$MD5."<br>");
			// echo("QRCode :".$QRCODE."<br>");
			// echo("<hr>");

			$currcol = 1;

			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $NO);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $PRODUCTID);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $SERIALNO);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $MD5);
			$currcol += 1;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $QRCODE);

			$currrow += 1;
		}
		
		$lastrow = $currrow - 1;		
		
		
		// border
		$styleArray = [
						'borders' => [
							'allBorders' => [
								'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
							],
						],
					  ];
		$sheet ->getStyle("A".$startrow.':E'.$lastrow)->applyFromArray($styleArray);
		
		$sheet->setSelectedCell('A1');
		$filename='UniqueCode ['.date('Ymdhis').']'; //save our workbook as this file name
		$ResponseCode = 0;
		$ResponseText = "SUCCESSFUL";

		try {
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 
		} catch(Exception $e) {
			$ResponseCode = 1;
		  	$ResponseText = $e->getMessage();
		}

		$this->UCGModel->saveLog($post, UNIQUECODE_SALTV, $ResponseCode, $ResponseText);
		exit();		
	}
	public function encryptDate($D=1){
		//1 - 1 = A
		//26 - 1 = Z
		$letter = '';
		if($D>=1 && $D<=26){
			$i = $D - 1;
			$letter = chr($i+65);
		}
		else if($D>=27 && $D<=31){
			$letter = $D-26;
		}
		return $letter;
	}
	public function encryptMonth($M=1)
	{
		$M = $M-1;
		$M = ord("J")+$M;
		return chr($M);
	}

	public function encryptYear($Y=1)
	{
		$Y = $Y - 2021;
		if ($Y > 36) {
			$Y = $Y % 36;
		}


		if ($Y > 9) {
			$Y = $Y + 55;
			$YY = chr($Y);
		} else {
			$YY = (string)$Y;
		}

		return $YY;
	}

	public function DebugGenerate()
	{
    	// $post = $this->PopulatePost();
    	$USERNAME = "";
    	$PRODUCTID = "RI-522S";
    	$SERIALNOMIN = "2109049931";
    	$SERIALNOMAX = "2109038591";
    	$SERIALNO = "";
    	$SERIALNOLEN = 0;

    	$TGL = $this->encryptDate(date("d"));
    	$BL = $this->encryptMonth(date("m"));
    	$TH = $this->encryptYear(date("Y"));

    	$NO = 0;
        $data = array();
		if(UNIQUECODE_SALTV==1){
			echo 'versi 1 <br>';
			$MD5 = md5($PRODUCTID.UNIQUECODE_SALT.$SERIALNOMIN);
			$MD5 = strtoupper(substr($MD5,1,UNIQUECODE_HASHLENGTH));
			$MD5 = UNIQUECODE_SALTV.$MD5.$BL.$TH;
			$QRCODE = $PRODUCTID." | ".$SERIALNOMIN." | ".$MD5;
			echo($QRCODE);
			echo("<br>");
			$MD5 = md5($PRODUCTID.UNIQUECODE_SALT.$SERIALNOMAX);
			$MD5 = strtoupper(substr($MD5,1,UNIQUECODE_HASHLENGTH));
			$MD5 = UNIQUECODE_SALTV.$MD5.$BL.$TH;
			$QRCODE = $PRODUCTID." | ".$SERIALNOMAX." | ".$MD5;
			echo($QRCODE);
			echo("<br>");
		}
		else{
			echo 'versi 2 <br>';
			$MD5 = md5($PRODUCTID.UNIQUECODE_SALT.$SERIALNOMIN);
			$MD5 = strtoupper(substr($MD5,1,UNIQUECODE_HASHLENGTH));
			$MD5 = UNIQUECODE_SALTV.$MD5.$BL.$TH.$TGL;
			$QRCODE = $PRODUCTID." | ".$SERIALNOMIN." | ".$MD5;
			echo($QRCODE);
			echo("<br>");
			$MD5 = md5($PRODUCTID.UNIQUECODE_SALT.$SERIALNOMAX);
			$MD5 = strtoupper(substr($MD5,1,UNIQUECODE_HASHLENGTH));
			$MD5 = UNIQUECODE_SALTV.$MD5.$BL.$TH.$TGL;
			$QRCODE = $PRODUCTID." | ".$SERIALNOMAX." | ".$MD5;
			echo($QRCODE);
			echo("<br>");
		}
		$url = MISHIRIN_URL."/getFactoryKartuNoSeri";
		$payload = array(
			"secretKey"=> $this->secretKey,
		);
		$result = $this->_postRequestJson($url,$payload);
		echo '<pre>';
		print_r($result);
		echo '</pre>';
	}

	public function BlacklistPesan()
	{
		$data['controller'] = $this->controller;
    	$data["result"] = $this->UCGModel->BlacklistPesanGetList();
    	$data["version"] = "V2";

		$this->RenderView('MsBlacklistPesanView',$data);
	}


	public function BlacklistPesanAdd()
	{
		$data['controller'] = $this->controller;
		$this->RenderView('MsBlacklistPesanAdd');
    //$data["version"] = "V2";
		//$this->RenderView('MsBlacklistPesanAdd', $data);
	}

	public function BlacklistPesanEdit($id)
	{

    	$data["result"] = $this->UCGModel->BlacklistPesanGetList('',$id);
    	$data["version"] = "V2";
		$this->RenderView('MsBlacklistPesanEdit', $data);
	}

	public function BlacklistPesanInsert()
	{
		$pesan = $this->input->post('pesan');
    	$insert = $this->UCGModel->BlacklistPesanSave($pesan);
		if($insert){
			redirect($this->controller.'/BlacklistPesan?insertsuccess=1');
		}
		else{
			redirect($this->controller.'/BlacklistPesan?insertsuccess=0');
			//redirect('UniqueCodeGeneratorV2/BlacklistPesan?insertsuccess=1');
		//}
		//else{
		//	redirect('UniqueCodeGeneratorV2/BlacklistPesan?insertsuccess=0');
		}
	}

	public function BlacklistPesanUpdate()
	{

		$post = $this->PopulatePost();
    	$update = $this->UCGModel->BlacklistPesanUpdate($post);
		if($update){
			redirect($this->controller.'/BlacklistPesan?updatesuccess=1');
		}
		else{
			redirect($this->controller.'/BlacklistPesan?updatesuccess=0');
			//redirect('UniqueCodeGeneratorV2/BlacklistPesan?updatesuccess=1');
		//}
		//else{
			//redirect('UniqueCodeGeneratorV2/BlacklistPesan?updatesuccess=0');
		}
	}

	public function BlacklistPesanDelete($id)
	{
    	$delete = $this->UCGModel->BlacklistPesanDelete($id);
		if($delete){
			redirect($this->controller.'/BlacklistPesan?deletesuccess=1');
		}
		else{
			redirect($this->controller.'/BlacklistPesan?deletesuccess=0');
			//redirect('UniqueCodeGeneratorV2/BlacklistPesan?deletesuccess=1');
		//}
		//else{
			//redirect('UniqueCodeGeneratorV2/BlacklistPesan?deletesuccess=0');
		}
	}

	public function BlacklistInsert()
	{
		$post = $this->PopulatePost();

		$data = array("secretKey"=>$this->secretKey,
						"kode_brg"=>$post['kode_brg'],
						"range_awal"=>$post['range_awal'],
						"range_akhir"=>$post['range_akhir'],
						"message_external"=>$post['message'],
						"message_internal"=>$post['message_internal'],
						"created_by"=>$_SESSION['logged_in']['username']);

		$json_data = json_encode($data);

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => MISHIRIN_URL.'/insertBlacklistKartuNoSeri',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 1000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => $json_data,
		CURLOPT_HTTPHEADER => array('Content-Type: application/json'), 
		));

		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

		$result = curl_exec($curl);
		$err = curl_error($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		// die($result);
		// return json_decode($result);
		$res =  json_decode($result);
		if(isset($res->data) && $res->data!=null){
			redirect($this->controller.'?insertblacklistsuccess=1&message='.$res->message);
		}
		else if(isset($res->data)) {
			redirect($this->controller.'?insertblacklistsuccess=0&message='.$res->message);
		} else {
			redirect($this->controller.'?insertblacklistsuccess=0&message='.json_encode("Tidak terhubung dengan API Mishirin. Coba Kembali Nanti."));
			//redirect('UniqueCodeGeneratorV2?insertblacklistsuccess=1&message='.$res->message);
		//}
		//else if(isset($res->data)) {
		//	redirect('UniqueCodeGeneratorV2?insertblacklistsuccess=0&message='.$res->message);
		//} else {
		//	redirect('UniqueCodeGeneratorV2?insertblacklistsuccess=0&message='.json_encode("Tidak terhubung dengan API Mishirin. Coba Kembali Nanti."));
		}
	}

	public function BlacklistDelete($blacklist_id)
	{
		$data = array("secretKey"=>$this->secretKey, 
						"blacklist_id"=>$blacklist_id, 
						"created_by"=>$_SESSION['logged_in']['username']);

		$json_data = json_encode($data);

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => MISHIRIN_URL.'/deleteBlacklistKartuNoSeri',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 1000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => $json_data,
		CURLOPT_HTTPHEADER => array('Content-Type: application/json'), 
		));

		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

		$result = curl_exec($curl);
		$err = curl_error($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		// return json_decode($result);
		$res =  json_decode($result);
		if(isset($res->data) && $res->data!=null){
			redirect($this->controller.'?deleteblacklistsuccess=1&message='.$res->message);
		}
		else{
			redirect($this->controller.'?deleteblacklistsuccess=0&message='.$res->message);
			//redirect('UniqueCodeGeneratorV2?deleteblacklistsuccess=1&message='.$res->message);
		//}
		//else{
			//redirect('UniqueCodeGeneratorV2?deleteblacklistsuccess=0&message='.$res->message);
		}
	}

	public function BlacklistAdd($logid='')
	{
		if($this->uri->segment(2) != '')
			$ctrname = $this->uri->segment(1)."/".$this->uri->segment(2);
		else
			$ctrname = $this->uri->segment(1);

		$data['access'] = $this->ModuleModel->getDetail($ctrname,$_SESSION['role']);

		// $data["SNList"] = $this->UCGModel->GetListSN();
		// $SNList = $this->UCGModel->GetListSN();
		// $data["SelectedSN"] = '';
		$data["SNList"] = array();
		 // foreach($SNList as $SN) {
			// array_push($data["SNList"], trim($SN->SerialNoMin)." | ".trim($SN->SerialNoMax)." | ".trim($SN->ProductID));
			// if($logid!=''){
				// if($SN->LogId == $logid){
					// $data["SelectedSN"] = trim($SN->SerialNoMin)." | ".trim($SN->SerialNoMax)." | ".trim($SN->ProductID);
				// }
			// }
		// }


		$data["LogId"] = $logid;
		$data["SelectedSN"] = $this->UCGModel->getProductIDByLogId($logid);
		$data["pesan"] = $this->UCGModel->BlacklistPesanGetList(1);
		$data["controller"] = $this->controller;
    //$data["version"] = "V2";

		$this->RenderView('BlacklistAdd',$data);
	}

	public function GetListSN(){
	    $data_list=array();
	    $total =0;

			$data = $this->UCGModel->GetListSN2($this->input->get());
			// die($data);

			if(count($data)){
				$ConfigReport = $data["data"];
				

				$data_hasil=array();

				if(!empty($ConfigReport)){

					$total = $data["total"];

					foreach (json_decode(json_encode($ConfigReport)) as $key => $r) {
						$action='';
						$req ='';
						$list=array();

						$MOBILE = "#".$r->LogId."<br>".$r->LogDate."<br>".$r->CreatedBy."<br>".$r->SerialNoMin."<br>".$r->SerialNoMax."<br>".$r->ProductID."<br>".$r->Description;

						$list[] 	= $r->LogId;
						$list[] 	= $r->LogDate;
						$list[] 	= $r->CreatedBy;
						$list[] 	= $r->SerialNoMin;
						$list[] 	= $r->SerialNoMax;
						$list[] 	= $r->ProductID;
						$list[] 	= $r->brand;
						$list[] 	= $r->Description;

						$list[] 	= $MOBILE;

						// if($r->CreatedBy==$_SESSION["logged_in"]["useremail"]){
							$list[] 	= "<a href='javascript:HideCode(".$r->LogId.")'><i class='glyphicon glyphicon-trash'></a>";
						// }else{
						// 	$list[] 	= '';
						// }
						
						$list[] 	= "<a href='UniqueCodegeneratorV2/BlacklistAdd/".$r->LogId."' target='_blank'><i class='glyphicon glyphicon-ban-circle'></a>";


						$data_list[] = $list;

					}
				}
			}


		if(!empty($this->input->get('sEcho'))){
			$secho = $this->input->get('sEcho');
		}else{
			$secho = 1;
		}

		$data_hasil['sEcho']=$secho;
		$data_hasil['iTotalRecords']=$total;
		$data_hasil['iTotalDisplayRecords']=$total;
		$data_hasil['aaData']=$data_list;

		print_r(json_encode($data_hasil));
	}

	public function getDeletedList(){
	    $data_list=array();
	    $total =0;

	    	$data = $this->UCGModel->DeletedList($this->input->get());
			// die($data);

			if(count($data)){
				$ConfigReport = $data["data"];
				

				$data_hasil=array();

				if(!empty($ConfigReport)){

					$total = $data["total"];

					foreach (json_decode(json_encode($ConfigReport)) as $key => $r) {
						$action='';
						$req ='';
						$list=array();

						$MOBILE = "#".$r->LogId."<br>".$r->brand."<br>".$r->ProductID."<br>".$r->SerialNoMin."<br>".$r->reason_deleted."<br>".$r->deleted_date."<br>".$r->deleted_by;

						$list[] 	= $r->LogId;
						$list[] 	= $r->brand;
						$list[] 	= $r->ProductID;
						$list[] 	= $r->SerialNoMin;
						$list[] 	= $r->SerialNoMax;
						$list[] 	= $r->reason_deleted;
						$list[] 	= $r->deleted_date;
						$list[] 	= $r->deleted_by;

						$list[] 	= $MOBILE;


						$data_list[] = $list;

					}
				}
			}


		if(!empty($this->input->get('sEcho'))){
			$secho = $this->input->get('sEcho');
		}else{
			$secho = 1;
		}

		$data_hasil['sEcho']=$secho;
		$data_hasil['iTotalRecords']=$total;
		$data_hasil['iTotalDisplayRecords']=$total;
		$data_hasil['aaData']=$data_list;

		print_r(json_encode($data_hasil));
	}
	
	public function GetListSNSelect(){
		$search = $this->input->get('search');
		$data = $this->UCGModel->select2($search);
		echo json_encode($data);
	}
	
	
}