<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require 'vendor/autoload.php';
//require('./application/third_party/phpoffice/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanEkspedisiPabrik extends MY_Controller 
{

	public $excel_flag = 0;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('HelperModel');
		$this->load->model('ActivityLogModel');
		$this->load->library('Excel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}
	
	public function index()
	{
		//include_once('/../includes/CheckModule.php');
		$data = array();

		$branches = $this->BranchModel->GetsByUser($_SESSION['logged_in']['useremail']);

		$data['title'] = WEBTITLE.' | Laporan Ekspedisi Pabrik';
		$data['branches'] = $branches;
		$data['months'] = $this->HelperModel->GetMonths();
		$this->RenderView('LaporanEkspedisiPabrikFormView',$data);


		$params = array(); 
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN EKSPEDISI PABRIK";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU LAPORAN EKSPEDISI PABRIK";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

	}
	function _postRequest($url,$data,$isJson = false){
		//echo $url.'<br>';
		$ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POST, 1);

	    curl_setopt($ch, CURLOPT_ENCODING, '');
	    
	    if ($isJson) {
	        // Jika data adalah JSON, encode ke JSON dan atur header
	        $strJson = json_encode($data);
	        //echo $strJson;
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $strJson);
	        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

	    } else {
	        // Jika data adalah form data, atur payload dengan http_build_query
	        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	    }

	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	    $result = curl_exec($ch);
	    // $result = @gzdecode($result);
	    if (curl_errno($ch)) {
	        echo 'Curl error: ' . curl_error($ch);
	    }

	    curl_close($ch);

	    return $result;
	}
	function _decodeGzip($str){
	    // Try to decode the data
	    $decodedData = @gzdecode($str);

	    // Check if decoding was successful
	    if ($decodedData !== false) {
	        return $decodedData; // The string is gzip-encoded

	    } else {
	        return $str; // The string is not gzip-encoded
	    }
	}
	public function Preview()
	{
		$LogDate = date("Y-m-d H:i:s");
		$this->Logs_insert($LogDate,'MENAMPILKAN MENU LAPORAN EKSPEDISI PABRIK');

		$khususdepo = 0;
		if(isset($_POST["chkKhususDepo"])){
			$khususdepo = 1;
		}

		if(isset($_POST["btnPreview"])){
			$this->excel_flag = 0;
		}
		else{
			$this->excel_flag = 1;
		}

		$data = array();
		$page_title = 'Preview Laporan Ekspedisi Pabrik';
		if(isset($_POST['cbg']))
		{
			$this->load->library('form_validation');
			$this->form_validation->set_rules('yyyy','Tahun','required');
			$this->form_validation->set_rules('cbg','Cabang','required');


			if ($_POST["cbg"]=="SBY") {
				$_POST["cbg"]="SRY";
			} else if ($_POST["cbg"]=="BGR") {
				$_POST["cbg"]="BOG";
			}
			
			if($this->form_validation->run())
			{
				$this->Logs_Update($LogDate,'SUCCESS','MENAMPILKAN MENU LAPORAN EKSPEDISI PABRIK');
				$this->Preview_Result($page_title, $_POST["yyyy"], $_POST["mm"], $_POST["dd"], $_POST["ddto"], $_POST["email"], $_POST["cbg"], $khususdepo);
			}
			else
			{
				$this->Logs_Update($LogDate,'FAILED - Data Tidak Ditemukan','MENAMPILKAN MENU LAPORAN EKSPEDISI PABRIK');
				redirect("LaporanEkspedisiPabrik");
			}
		}
		else
		{
			$this->Logs_Update($LogDate,'FAILED - Data Tidak Ditemukan','MENAMPILKAN MENU LAPORAN EKSPEDISI PABRIK');
			redirect("LaporanEkspedisiPabrik");
		}
	}

	public function JobHarianCBG()
	{
		$dayname = strtoupper(date("D"));
		if ($dayname=="MON")
		{
			$yesterday = date("m/d/Y", strtotime("-2 day", strtotime(date("Y-m-d"))));
			$kemarin = date("d-M-Y", strtotime("-2 day", strtotime(date("Y-m-d"))));
		}
		elseif ($dayname!="SUN")
		{
			$yesterday = date("m/d/Y", strtotime("-1 day", strtotime(date("Y-m-d"))));
			$kemarin = date("d-M-Y", strtotime("-1 day", strtotime(date("Y-m-d"))));
		}

		$p_yy = date("Y", strtotime($yesterday));
		$p_mm = date("m", strtotime($yesterday));
		$p_dd = date("d", strtotime($yesterday));
		$p_email = "Y";
		$p_cbg = "ALL";

		$this->excel_flag = 0;
		$page_title = 'Preview Laporan Ekspedisi Pabrik';
		$this->Preview_Result($page_title, $p_yy, $p_mm, $p_dd, $p_dd, $p_email, $p_cbg, 0, "Y", "N");

	}

	public function Preview_Result($page_title, $p_yy, $p_mm, $p_dd, $p_ddto, $p_email, $p_cbg, $khususdepo=0, $p_job_cbg="N", $p_job_jkt="N")
	{

		$LogDate = date("Y-m-d H:i:s");
		$this->Logs_insert($LogDate,'MENAMPILKAN MENU LAPORAN EKSPEDISI PABRIK');

		$testing = false;
		$data = array();
		$api = 'APITES';
		set_time_limit(60);
		$content_html = "";
		
		$sampaitanggal = date("m/d/Y", strtotime($p_yy."-".$p_mm."-".$p_ddto));
		$sampaitgl = date("d-M-Y", strtotime($p_yy."-".$p_mm."-".$p_ddto));

		$URL=$this->API_URL."/LaporanEkspedisiPabrik/Proses?api=".$api."&p_yy=".urlencode($p_yy)."&p_mm=".urlencode($p_mm)."&p_dd=".urlencode($p_dd)."&p_ddto=".urlencode($p_ddto)."&p_cbg=".urlencode($p_cbg)."&p_email=".urlencode($p_email);
		// die($URL);
		// $result = json_decode(file_get_contents($URL));

		$response = HttpGetRequest($URL, $this->API_URL, "Proses Laporan Ekspedisi Pabrik");
		$response = $this->_decodeGzip($response);
		$result = json_decode($response);
		$yesterday = $result->yesterday;
		$kemarin = $result->kemarin;
		$disconnected = $result->result;
		$supl_html = "<div>";
		
		for ($i=0;$i<count($disconnected);$i++)
		{
			if ($disconnected[$i]->Koneksi=="GAGAL") {
				$supl_html.= "<div style='color:red;'>KONEKSI KE ".$disconnected[$i]->Nama." <b>GAGAL</b></div>";
			} else {
				$supl_html.= "<div style='color:blue;'>KONEKSI KE ".$disconnected[$i]->Nama." <b>SUKSES</b></div>";
			}
		}
		$supl_html.= "</div>";

		$URL = $this->API_URL."/LaporanEkspedisiPabrik/GetEmailGroup?api=".$api."&p_cbg=".urlencode($p_cbg)."&p_job_cbg=".urlencode($p_job_cbg)."&p_job_jkt=".urlencode($p_job_jkt);
		// die($URL);
		// $row_group = json_decode(file_get_contents($URL));
		$response = HttpGetRequest($URL, $this->API_URL, "Ambil Group Email");
		$response = $this->_decodeGzip($response);
		$row_group = json_decode($response);

		$content_gabungan = "";
		$currrow = 1;

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet(0);

		for($i=0;$i<count($row_group);$i++)
		{
			$EXP_NAME="";
			$EXP_NO = "";
			$CONT_NO="";
			$CONT_SEAL="";
			$SUPIR="";
			$NO_MOBIL="";
			$SHIPMENTID="";
			$ADA_DO = false;

			$content_html = "<html><body>";
			$content_html.= "<div style='width:100%;'>";

			$content_html.= "<div id='header' style='width:100%;'>";
			$content_html.= "	<div><h2>LAPORAN EKSPEDISI DARI PABRIK</h2></div>";
			$content_html.= "	<div><h3>TANGGAL ".$kemarin." s/d ".$sampaitgl."</h3></div>";
			$content_html.= "</div>";

			if ($p_job_cbg=="Y" || $p_job_jkt=="Y") {
				$content_html.= $supl_html;
			}

			$content_html.= "<div style='clear:both'></div>";

			if($this->excel_flag == 1){
				$sheet->setTitle('LaporanEkspedisiPabrik');
				$sheet->setCellValue('A1', 'LAPORAN EKSPEDISI DARI PABRIK');
				$sheet->getStyle('A1')->getFont()->setSize(20);
				$sheet->setCellValue('A2', 'TANGGAL '.$kemarin.' s/d '.$sampaitgl);
				$sheet->getStyle('A2')->getFont()->setSize(20);
			}

			$currcol = 1;
			$currrow += 2;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $row_group[$i]->EmailGroupID);
			$currrow += 1;
			//$sheet->getStyle('A2')->getFont()->setSize(20);

			$URL = $this->API_URL."/LaporanEkspedisiPabrik/GetDO?api=".$api."&p_yesterday=".urlencode($yesterday)."&p_sampaitanggal=".urlencode($sampaitanggal)."&p_emailgroupid=".urlencode($row_group[$i]->EmailGroupID)."&p_depo=".urlencode($khususdepo);
			// die($URL);
			// $do = json_decode(file_get_contents($URL));
			$response = HttpGetRequest($URL, $this->API_URL, "Ambil DO ".$row_group[$i]->EmailGroupID);
			$response = $this->_decodeGzip($response);
			$do = json_decode($response);

			for($j=0;$j<count($do);$j++)
			{
		    	$ADA_DO = true;

		    	if ($EXP_NAME==$do[$j]->Exp_Name && $EXP_NO==$do[$j]->Exp_No && $CONT_NO==$do[$j]->Container_No && $CONT_SEAL==$do[$j]->Container_Seal
		    		&& $SUPIR==$do[$j]->Supir && $NO_MOBIL==$do[$j]->No_Mobil && $SHIPMENTID==$do[$j]->ShipmentID)
		    	{

		    	}
		    	else if ($EXP_NAME==$do[$j]->Exp_Name && $EXP_NO==$do[$j]->Exp_No && $CONT_NO==$do[$j]->Container_No && $CONT_SEAL==$do[$j]->Container_Seal
		    		&& $SUPIR==$do[$j]->Supir && $NO_MOBIL==$do[$j]->No_Mobil)
		    	{
		    		if ($SHIPMENTID!="") {
						$content_html.= "<div style='width:100%;border-top:1px solid #CCC;text-align:center;min-height:30px'>";
						$content_html.= "	<div style='float:left;width:84%;line-height:30px;vertical-align:middle;'><b>TOTAL ".$SHIPMENTID."</b></div>";
						$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>".$TOTAL_QTY."</b></div>";
						$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>".$TOTAL_COLI."</b></div>";
						$content_html.= "	<div style='clear:both'></div>";
						$content_html.= "</div>";		    		    			
						$content_html.= "</div>";		    		    			
		    			$content_html.= "</div>";

		    			if($this->excel_flag == 1){
							$currrow += 1;
							$currcol = 2; 
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$SHIPMENTID);
							$currcol += 3;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL_QTY);
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL_COLI);
							//$currrow += 1;
							//$currcol = 0;
						}

		    		}

		    		$content_html.= "<div class='another_shipment' style='margin-top:20px; margin-bottom:15px;'>";
		    		$content_html.= "<div style='margin-top:20px; text-align:left; font-weight:bold;'>".$do[$j]->ShipmentID."</div>";
					$content_html.= "<div style='width:100%;border:1px solid #CCC;text-align:center;'>";
					$content_html.= "<div style='width:100%;border-bottom:1px solid #CCC;text-align:center;min-height:30px'>";
					$content_html.= "	<div style='float:left;width:10%;line-height:30px;vertical-align:middle;'><b>TGL DO</b></div>";
					$content_html.= "	<div style='float:left;width:20%;line-height:30px;vertical-align:middle;'><b>NO PO</b></div>";
					$content_html.= "	<div style='float:left;width:25%;line-height:30px;vertical-align:middle;'><b>NO DO</b></div>";
					$content_html.= "	<div style='float:left;width:29%;line-height:30px;vertical-align:middle;'><b>KD BRG</b></div>";
					$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>QTY</b></div>";
					$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>COLI</b></div>";
					$content_html.= "	<div style='clear:both'></div>";
					$content_html.= "</div>";		    		

					$SHIPMENTID = $do[$j]->ShipmentID;
					$TOTAL_QTY = 0;
					$TOTAL_COLI = 0;

					if($this->excel_flag == 1){
						$currrow += 1;
						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL DO');
						$currcol += 1; 
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO PO');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO DO');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KD BRG');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'COLI');
						//$currrow += 1;
						//$currcol = 0;
					}
		    	}
		    	else
		    	{
		    		if ($EXP_NAME!="" || $EXP_NO!="" || $CONT_NO!="" || $CONT_SEAL!="" || $SUPIR!="" || $NO_MOBIL!="") {
						$content_html.= "<div style='width:100%;border-top:1px solid #CCC;text-align:center;min-height:30px'>";
						$content_html.= "	<div style='float:left;width:84%;line-height:30px;vertical-align:middle;'><b>TOTAL ".$SHIPMENTID."</b></div>";
						$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>".$TOTAL_QTY."</b></div>";
						$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>".$TOTAL_COLI."</b></div>";
						$content_html.= "	<div style='clear:both'></div>";
						$content_html.= "</div></div>";		    		    			
		    			$content_html.= "</div></div>";
		    			if($this->excel_flag == 1){
		    				$currrow+= 1;
							$currcol = 2; 
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$SHIPMENTID);
							$currcol += 3;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL_QTY);
							$currcol += 1;
							$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL_COLI);
							//$currcol = 0;
							//$currrow+= 1;
						}

		    		}


		    		$content_html.= "<div class='another_expedition' style='margin-top:15px;margin-bottom:25px;'>";
		    		$content_html.= "<div style='font-weight:bold; margin-bottom:30px;'>";
		    		$content_html.= "	<div style='width:15%;float:left;font-size:10pt;'>Nama Ekspedisi</div>";
		    		$content_html.= "	<div style='width:35%;float:left;font-size:10pt;'>: ".$do[$j]->Exp_Name."</div>";
		    		$content_html.= "	<div style='width:15%;float:left;font-size:10pt;'>No Ekspedisi</div>";
		    		$content_html.= "	<div style='width:35%;float:left;font-size:10pt;'>: ".$do[$j]->Exp_No."</div>";
					$content_html.= "	<div style='clear:both'></div>";
		    		$content_html.= "	<div style='width:15%;float:left;font-size:10pt;'>No Container</div>";
		    		$content_html.= "	<div style='width:35%;float:left;font-size:10pt;'>: ".$do[$j]->Container_No."</div>";
		    		$content_html.= "	<div style='width:15%;float:left;font-size:10pt;'>Supir</div>";
		    		$content_html.= "	<div style='width:35%;float:left;font-size:10pt;'>: ".$do[$j]->Supir."</div>";
					$content_html.= "	<div style='clear:both'></div>";
		    		$content_html.= "	<div style='width:15%;float:left;font-size:10pt;'>Container Seal</div>";
		    		$content_html.= "	<div style='width:35%;float:left;font-size:10pt;'>: ".$do[$j]->Container_Seal."</div>";
		    		$content_html.= "	<div style='width:15%;float:left;font-size:10pt;'>No Mobil</div>";
		    		$content_html.= "	<div style='width:35%;float:left;font-size:10pt;'>: ".$do[$j]->No_Mobil."</div>";
					$content_html.= "</div>";		    		
		    		$content_html.= "<div class='another_shipment' style='margin-top:20px; margin-bottom:15px;'>";
		    		$content_html.= "<div style='margin-top:20px; text-align:left; font-weight:bold;'>".$do[$j]->ShipmentID."</div>";

		    		if($this->excel_flag == 1){
		    			$currrow+= 1;
		    			$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama Ekspedisi : '.$do[$j]->Exp_Name);
						$currcol += 2; 
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Ekspedisi : '.$do[$j]->Exp_No);
						$currrow += 1; $currcol -= 2;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Container : '.$do[$j]->Container_No);
						$currcol += 2;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Supir : '.$do[$j]->Supir);
						$currrow += 1; $currcol -= 2;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Container Seal : '.$do[$j]->Container_Seal);
						$currcol += 2;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Mobil : '.$do[$j]->No_Mobil);
						$currrow += 1; $currcol -= 2;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, $do[$j]->ShipmentID);
						//$currrow += 1;
					}


					$content_html.= "<div style='width:100%;border:1px solid #CCC;text-align:center;'>";
					$content_html.= "<div style='width:100%;border-bottom:1px solid #CCC;text-align:center;min-height:30px'>";
					$content_html.= "	<div style='float:left;width:10%;line-height:30px;vertical-align:middle;'><b>TGL DO</b></div>";
					$content_html.= "	<div style='float:left;width:20%;line-height:30px;vertical-align:middle;'><b>NO PO</b></div>";
					$content_html.= "	<div style='float:left;width:25%;line-height:30px;vertical-align:middle;'><b>NO DO</b></div>";
					$content_html.= "	<div style='float:left;width:29%;line-height:30px;vertical-align:middle;'><b>KD BRG</b></div>";
					$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>QTY</b></div>";
					$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>COLI</b></div>";
					$content_html.= "	<div style='clear:both'></div>";
					$content_html.= "</div>";		 

					if($this->excel_flag == 1){
						$currrow += 1;
						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL DO');
						$currcol += 1; 
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO PO');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO DO');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KD BRG');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'QTY');
						$currcol += 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'COLI');
						///$currrow += 1;
						//$currcol = 0;
					}

					$EXP_NAME=$do[$j]->Exp_Name;
					$EXP_NO = $do[$j]->Exp_No;
					$CONT_NO= $do[$j]->Container_No;
					$CONT_SEAL=$do[$j]->Container_Seal;
					$SUPIR=$do[$j]->Supir;
					$NO_MOBIL=$do[$j]->No_Mobil;
					$SHIPMENTID=$do[$j]->ShipmentID;
					$TOTAL_QTY = 0;
					$TOTAL_COLI = 0;

		    	}

				$content_html.= "<div style='width:100%;text-align:center;min-height:25px'>";
				$content_html.= "	<div style='float:left;width:10%;line-height:25px;vertical-align:middle;font-size:9pt;'>".$do[$j]->Tgl_DO."</div>";
				$content_html.= "	<div style='float:left;width:20%;line-height:25px;vertical-align:middle;font-size:9pt;'>".$do[$j]->No_PO."</div>";
				$content_html.= "	<div style='float:left;width:25%;line-height:25px;vertical-align:middle;font-size:9pt;'>".$do[$j]->No_DO."</div>";
				$content_html.= "	<div style='float:left;width:29%;line-height:25px;vertical-align:middle;font-size:9pt;'>".$do[$j]->Kd_Brg."</div>";
				$content_html.= "	<div style='float:left;width:8%;line-height:25px;vertical-align:middle;font-size:9pt;'>".$do[$j]->Qty."</div>";
				$content_html.= "	<div style='float:left;width:8%;line-height:25px;vertical-align:middle;font-size:9pt;'>".$do[$j]->Coli."</div>";
				$content_html.= "	<div style='clear:both'></div>";
				$content_html.= "</div>";

				$TOTAL_QTY += $do[$j]->Qty;
				$TOTAL_COLI+= $do[$j]->Coli;

				if($this->excel_flag == 1){
					$currrow += 1;
					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $do[$j]->Tgl_DO);
					$currcol += 1; 
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $do[$j]->No_PO);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $do[$j]->No_DO);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $do[$j]->Kd_Brg);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $do[$j]->Qty);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $do[$j]->Coli);
					//$currrow += 1;
					//$currcol = 0;
				}
		    }

			if ($ADA_DO)
			{

				$content_html.= "<div style='width:100%;border-top:1px solid #CCC;text-align:center;min-height:30px'>";
				$content_html.= "	<div style='float:left;width:84%;line-height:30px;vertical-align:middle;'><b>TOTAL ".$SHIPMENTID."</b></div>";
				$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>".$TOTAL_QTY."</b></div>";
				$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>".$TOTAL_COLI."</b></div>";
				$content_html.= "	<div style='clear:both'></div>";
				$content_html.= "</div></div>";		    		    			
				$content_html.= "</div></div>";

				if($this->excel_flag == 1){
					$currrow+= 1;
					$currcol = 2; 
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$SHIPMENTID);
					$currcol += 3;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL_QTY);
					$currcol += 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $TOTAL_COLI);
					//$currcol = 0;
				}

				if ($p_email=="Y")		
				{
					$content_html.= "<div style='font-size:10pt;font-style:italic;margin-top:25px;'>";
					$content_html.= "Email ini dikirimkan otomatis oleh sistem. Mohon tidak mereply email ini.";
					$content_html.= "</div>";
				}
				$content_html.= "</div></body></html>";

			} else {
				$content_html.="<div style='font-size:12pt; color:red;'><b>TIDAK ADA DO</b></div>";
				$content_html.= "</div></body></html>";				
			}

			//Kirim Emailnya
			if ($p_email=="Y")
			{
				$recipients = array();
				if ($testing) {
					array_push($recipients, TEST_EMAIL);
				} else {
					$response = file_get_contents($this->API_URL."/LaporanEkspedisiPabrik/GetRecipients?api=".$api."&p_emailgroupid=".urlencode($row_group[$i]->EmailGroupID));
					$response = $this->_decodeGzip($response);
					$recipient = json_decode($response);
					for($k=0;$k<count($recipient);$k++)
					{
						array_push($recipients, $recipient[$k]->EmailAddress);
					}
				}
				//print_r($recipients);
				$this->EmailModel->sendEmailReport($recipients, 'Laporan Ekspedisi Pabrik '.$kemarin, $content_html);
				//echo "success";
			}		

			$content_gabungan = $content_gabungan."<br>".$content_html;
		}

		if($this->excel_flag == 1){
			//PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
			$sheet->mergeCells('A1:J1');
			for ($i = 'A'; $i !=   $sheet->getHighestColumn(); $i++) {
			    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
			}

			$this->Logs_Update($LogDate,'SUCCESS','EXPORT EXCEL LAPORAN EKSPEDISI PABRIK');
			$filename='LaporanEkspedisiPabrik['.date('YmdHis').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
	        $writer->save('php://output');	// download file 
	        exit();

		}

		$data['title'] = $page_title;
		$data['content_html'] = $content_gabungan;
		//$data['content_gabungan'] = $content_gabungan;
		// $this->SetTemplate('template/login');
		if($content_html == ""){

			$this->Logs_Update($LogDate,'FAILED - Data Tidak Ditemukan','MENAMPILKAN LAPORAN EKSPEDISI PABRIK');

			echo "<script>alert('no data found!'); window.close();</script>";
		} else {
			/*if ($this->excel_flag==1) {
				$data["error"]="Excel tersimpan di ".$filename."";
			}*/
			$this->Logs_Update($LogDate,'SUCCESS','MENAMPILKAN LAPORAN EKSPEDISI PABRIK');
			$this->RenderView('LaporanEkspedisiPabrikResultView',$data);

		}
	}


	function Logs_insert($LogDate='',$description=''){
	   $params = array();   
	   $params['LogDate'] = $LogDate;
	   $params['UserID'] = $_SESSION["logged_in"]["userid"];
	   $params['UserName'] = $_SESSION["logged_in"]["username"];
	   $params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
	   $params['Module'] = "LAPORAN EKSPEDISI PABRIK";
	   $params['TrxID'] = date_format(date_create($LogDate),'YmdHis');
	   $params['Description'] = $_SESSION["logged_in"]["username"]." ".$description;
	   $params['Remarks'] = "";
	   $params['RemarksDate'] = 'NULL';
	   $this->ActivityLogModel->insert_activity($params);
	}

	function Logs_Update($LogDate='',$remarks='',$description=''){
		$params = array();   
		$params['LogDate'] = $LogDate;
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "LAPORAN EKSPEDISI PABRIK";
		$params['TrxID'] = date_format(date_create($LogDate),'YmdHis');
		$params['Description'] = $_SESSION["logged_in"]["username"]." ".$description;
		$params['Remarks']=$remarks;
	   	$params['RemarksDate'] = date("Y-m-d H:i:s");
	   	$this->ActivityLogModel->update_activity($params);
	}

    
}