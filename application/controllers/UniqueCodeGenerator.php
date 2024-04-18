<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UniqueCodeGenerator extends MY_Controller {

	// public $SALT = "GARAM";
	// public $SALTv = "1";
	// public $HashLength = 3;
	// // public $MishirinURL = 'https://asia-southeast2-mishirin-726d8.cloudfunctions.net'; // DEVELOPMENT  
	// public $MishirinURL = 'https://australia-southeast1-bhakti-mobile-27ba4.cloudfunctions.net'; // PRODUCTION
	// public $MishirinKey = 'mishirin2021'; 

	function __construct()
	{
		parent::__construct();
		$this->load->model('HelperModel');
		$this->load->helper('FormLibrary');
		$this->load->model('UniqueCodeGeneratorModel', 'UCGModel');
		$this->load->library("email");
		$this->load->helper('url');
		$this->load->library('excel');
		$this->controller = 'UniqueCodeGenerator';
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function index()
	{
        if(!empty($this->uri->segment(2))){
	    	$ctrname = $this->uri->segment(1)."/".$this->uri->segment(2);
        }else{
	       	$ctrname = $this->uri->segment(1);
        }

    	$data['access'] = $this->ModuleModel->getDetail($ctrname,$_SESSION['role']);
    	// $data["SNList"] = $this->UCGModel->GetListSN();
		
		/*
    	$Blacklist = $this->getBlacklistMishirin();
    	// die(json_encode($Blacklist));
    	if (!isset($Blacklist->data)) {
    		// die("tidak ada");
    		$data["Blacklist"] = array();
    	} else {
    		// die("ada");
    		$data["Blacklist"] = $Blacklist->data;
    	}
		*/
    	//$data["version"] = "";
    	// die(json_encode($data["Blacklist"]));
	    // $this->RenderView("ReportResultView", $data);

		$params = array();	
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = 'UNIQUE CODE GENERATOR';
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU UNIQUE CODE GENERATOR";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);
		
    	$data['controller'] = $this->controller;
		// echo json_encode($data);die;
		$this->RenderView('UniqueCodeGenerateList',$data);
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

						$list[] 	= "<a href='".$this->controller."/BlacklistAdd/".$r->LogId."' target='_blank'><i class='glyphicon glyphicon-ban-circle'></a>";


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
	
	public function getBlacklistMishirin()
	{
		$secretKey = base64_encode(MISHIRIN_KEY);
		$data = array("secretKey"=>$secretKey);
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

		echo $result;
	}


	
	public function Hide($UCID,$Ket)
	{
        $hide= $this->UCGModel->Hide($UCID,$Ket);
		echo $hide;
	}

	public function GenerateForm()
	{
        if($this->uri->segment(2) != ''){
	    	$ctrname = $this->uri->segment(1)."/".$this->uri->segment(2);
        }else{
	       	$ctrname = $this->uri->segment(1);
        }

    	$data['access'] = $this->ModuleModel->getDetail($ctrname,$_SESSION['role']);
    	$data["products"] = array();

    	$url = $this->API_URL."/MsBarang/GetBarangListGET?api=APITES&mishirin=1";
    	$barangs = json_decode(file_get_contents($url), true);
    	// die(json_encode($barangs));
    	if ($barangs["result"]=="sukses") {
    		$BRGS = $barangs["data"];	
			for($i=0;$i<count($BRGS);$i++) {
				array_push($data["products"], trim($BRGS[$i]["KD_BRG"])." | ".trim($BRGS[$i]["MERK"])." | ".trim($BRGS[$i]["JNS_BRG"]));
			}
    	}
    	// die (json_encode($data["products"]));

 		$params = array();	
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = 'UNIQUE CODE GENERATOR';
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA FORM UNIQUE CODE GENERATOR";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$this->RenderView('UniqueCodeGenerateForm',$data);
	}

	public function CheckProductId()
	{
		$post = $this->PopulatePost();
		$url = $this->API_URL."/MsBarang/Get?api=APITES&brg=".urlencode($post["productId"]);
		$check = json_decode(file_get_contents($url), true);
		if ($check["result"]=="sukses") {
			echo json_encode(array("result"=>"SUCCESS", "product"=>$check["data"]));
		} else {
			echo json_encode(array("result"=>"FAILED", "product"=>array()));
		}
	}

	public function CheckLog()
	{
		$post = $this->PopulatePost();		
		$check = $this->UCGModel->checkLog($post);
		// die(json_encode($check));
		echo json_encode($check);
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

		$params = array();	
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = 'UNIQUE CODE GENERATOR';
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." GENERATE UNIQUE CODE GENERATOR";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);


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

			$MD5 = md5($PRODUCTID.UNIQUECODE_SALT.$SERIALNO);
			$MD5 = strtoupper(substr($MD5,1,UNIQUECODE_HASHLENGTH));
			$MD5 = UNIQUECODE_SALTV.$MD5.$BL.$TH;

			$QRCODE = $PRODUCTID." | ".$SERIALNO." | ".$MD5;
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
			$currcol += 1;

			$currrow += 1;
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

			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

		} catch(Exception $e) {
			$ResponseCode = 1;
		  	$ResponseText = $e->getMessage();

			$params['Remarks']='FAILED - '.$e->getMessage();
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

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

			$MD5 = md5($PRODUCTID.UNIQUECODE_SALT.$SERIALNO);
			$MD5 = strtoupper(substr($MD5,1,UNIQUECODE_HASHLENGTH));
			$MD5 = UNIQUECODE_SALTV.$MD5.$BL.$TH;
			$QRCODE = $PRODUCTID." | ".$SERIALNO." | ".$MD5;

			// echo "<br>";
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

    	$BL = $this->encryptMonth(date("m"));
    	$TH = $this->encryptYear(date("Y"));

    	$NO = 0;
        $data = array();
		
		$MD5 = md5($PRODUCTID.UNIQUECODE_SALT.$SERIALNOMIN);
		$MD5 = strtoupper(substr($MD5,1,UNIQUECODE_HASHLENGTH));
		$MD5 = UNIQUECODE_SALTV.$MD5.$BL.$TH;
		$QRCODE = $PRODUCTID." | ".$SERIALNOMIN." | ".$MD5;
		echo $QRCODE;
		echo "<br>";
		$MD5 = md5($PRODUCTID.UNIQUECODE_SALT.$SERIALNOMAX);
		$MD5 = strtoupper(substr($MD5,1,UNIQUECODE_HASHLENGTH));
		$MD5 = UNIQUECODE_SALTV.$MD5.$BL.$TH;
		$QRCODE = $PRODUCTID." | ".$SERIALNOMAX." | ".$MD5;
		echo $QRCODE;
		echo "<br>";
	}

	public function BlacklistPesan()
	{
    	$data["result"] = $this->UCGModel->BlacklistPesanGetList();
    	$data["version"] = "";

		$this->RenderView('MsBlacklistPesanView',$data);
	}


	public function BlacklistPesanAdd()
	{
    	$data["version"] = "";
		$this->RenderView('MsBlacklistPesanAdd', $data);
	}

	public function BlacklistPesanEdit($id)
	{

    	$data["result"] = $this->UCGModel->BlacklistPesanGetList('',$id);
    	$data["version"] = "";
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
		}
	}

	public function BlacklistInsert()
	{
		$post = $this->PopulatePost();

		$secretKey = base64_encode(MISHIRIN_KEY);
		$data = array("secretKey"=>$secretKey,
						"kode_brg"=>$post['kode_brg'],
						"range_awal"=>$post['range_awal'],
						"range_akhir"=>$post['range_akhir'],
						"message"=>$post['message'],
						"message_internal"=>$post['message_internal'],
						"created_by"=>$_SESSION['logged_in']['username']);

		$json_data = json_encode($data);

		$params = array();	
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = 'UNIQUE CODE GENERATOR';
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." TAMBAH BLACKLIST NO SERI";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => MISHIRIN_URL.'/insertBlacklistKartuNoSeri',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
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
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			redirect($this->controller.'?insertblacklistsuccess=1&message='.$res->message);
		}
		else if(isset($res->data)) {
			$params['Remarks']="FAILED - ".$res->message;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			redirect($this->controller.'?insertblacklistsuccess=0&message='.$res->message);
		} else {
			$params['Remarks']="FAILED - Tidak terhubung dengan API Mishirin. Coba Kembali Nanti.";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			redirect($this->controller.'?insertblacklistsuccess=0&message='.json_encode("Tidak terhubung dengan API Mishirin. Coba Kembali Nanti."));
		}
	}

	public function BlacklistDelete($blacklist_id)
	{
		$secretKey = base64_encode(MISHIRIN_KEY);
		$data = array("secretKey"=>$secretKey, "blacklist_id"=>$blacklist_id, "created_by"=>$_SESSION['logged_in']['username']);

		$json_data = json_encode($data);

		$params = array();	
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = 'UNIQUE CODE GENERATOR';
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." DELETE BLACKLIST NO SERI";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => MISHIRIN_URL.'/deleteBlacklistKartuNoSeri',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
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
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			redirect($this->controller.'?deleteblacklistsuccess=1&message='.$res->message);
		}
		else{
			$params['Remarks']="FAILED - ".$res->message;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			redirect($this->controller.'?deleteblacklistsuccess=0&message='.$res->message);
		}
	}

	public function BlacklistAdd($logid='')
	{
		if($this->uri->segment(2) != '')
			$ctrname = $this->uri->segment(1)."/".$this->uri->segment(2);
		else
			$ctrname = $this->uri->segment(1);

		$data['access'] = $this->ModuleModel->getDetail($ctrname,$_SESSION['role']);

		// $data["products"] = array();
		// $url = $this->API_URL."/MsBarang/GetBarangListGET?api=APITES&mishirin=1";
		// $barangs = json_decode(file_get_contents($url), true);
		// if ($barangs["result"]=="sukses") {
			// $BRGS = $barangs["data"];	
			// for($i=0;$i<count($BRGS);$i++) {
				// array_push($data["products"], trim($BRGS[$i]["KD_BRG"])." | ".trim($BRGS[$i]["MERK"])." | ".trim($BRGS[$i]["JNS_BRG"]));
			// }
		// }

		// $data["SNList"] = $this->UCGModel->GetListSN();
		// $SNList = $this->UCGModel->GetListSN();
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
    //$data["version"] = "";
		// echo json_encode($data);die;
		$this->RenderView('BlacklistAdd',$data);
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
	
}