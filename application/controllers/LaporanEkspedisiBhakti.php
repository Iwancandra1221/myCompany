<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LaporanEkspedisiBhakti extends MY_Controller 
{
	public $excel_flag = 0;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('HelperModel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}
	
	public function index()
	{
		//include_once('/../includes/CheckModule.php');
		$data = array();

		$branches = $this->BranchModel->GetsByUser($_SESSION['logged_in']['useremail']);

		$data['title'] = 'LAPORAN EKSPEDISI BHAKTI | '.WEBTITLE;
		$data['branches'] = $branches;
		$data['months'] = $this->HelperModel->GetMonths();
		//$this->SetTemplate('template/laporan');
		$this->RenderView('LaporanEkspedisiBhaktiFormView',$data);
	}

	public function Preview()
	{
		//include_once('/../includes/CheckModuleAdd.php');
		// $post = $this->PopulatePost();

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

			if($this->form_validation->run())
			{
				$this->Preview_Result($page_title, $_POST["yyyy"], $_POST["mm"], $_POST["dd"], $_POST["email"], $_POST["cbg"]);
			}
			else
			{
				redirect("LaporanEkspedisiBhakti");
			}
		}
		else
		{
			redirect("LaporanEkspedisiBhakti");
		}
	}

	public function Preview_Result($page_title, $p_yy, $p_mm, $p_dd, $p_email, $p_cbg)
	{
		$data = array();
		$api = 'APITES';
		set_time_limit(60);
		$content_html = "";

		// $p_dd = 9;
		// $p_mm = 5;
		// $p_yy = 2017;
		// $p_cbg = "BANDUNG";

		$result = json_decode(file_get_contents($this->API_URL."/LaporanEkspedisiBhakti/Proses?api=".$api."&p_yy=".urlencode($p_yy)."&p_mm=".urlencode($p_mm)."&p_dd=".urlencode($p_dd)."&p_cbg=".urlencode($p_cbg)."&p_email=".urlencode($p_email)));

		if(count($result) == 0){
			echo "<script>
				alert('no data found!');
				window.close();
			</script>";
			// redirect("LaporanEkspedisiBhaktiCtr");
			exit(1);
		}

		$row_group = json_decode(file_get_contents($this->API_URL."/LaporanEkspedisiBhakti/GetEmailGroup?api=".$api."&p_cbg=".urlencode($p_cbg)));

		// echo $this->API_URL."LaporanEkspedisiBhakti/GetEmailGroup?api=".$api."&p_cbg=".urlencode($p_cbg);
		// print_r($row_group);
		// exit(1);
		$tgl_full = $p_dd."-".$p_mm."-".$p_yy;

		$content_html = "<html><body>";
		$content_html.= "<div style='width:100%;'>";

		$content_html.= "<div id='header' style='width:100%;'>";
		$content_html.= "	<div><h2>LAPORAN EKSPEDISI BHAKTI</h2></div>";
		$content_html.= "	<div><h3>TANGGAL ".date("d-M-Y", strtotime($tgl_full))."</h3></div>";
		$content_html.= "</div>";

		$content_html.= "<div style='clear:both'></div>";

		if($this->excel_flag == 1){
			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->setTitle('LaporanEkspedisiPabrik');
			$this->excel->getActiveSheet()->setCellValue('A1', 'LAPORAN EKSPEDISI BHAKTI');
			$this->excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
			$this->excel->getActiveSheet()->setCellValue('A2', 'TANGGAL '.date("d-M-Y", strtotime($tgl_full)));
			$this->excel->getActiveSheet()->getStyle('A2')->getFont()->setSize(20);
		}

		$totalqty = 0;

		$currcol = 0;
		$currrow = 4;

		for($i=0;$i<count($result);$i++)
		{

			//die($this->API_URL."LaporanEkspedisiPabrik/GetDO?api=".$api."&p_yesterday=".urlencode($yesterday)."&p_emailgroupid=".urlencode($row_group[$i]->EmailGroupID));
			if($i==0 ){
				$content_html.= "<div style='margin-top:20px; text-align:left; font-weight:bold;'>KATEGORI : ".($result[$i]->Kategori_Brg == 'P'?'PRODUK':'SPAREPART')."</div>";
				$content_html.= "<div style='margin-top:20px; text-align:left; font-weight:bold;'>NAMA : ".$result[$i]->Nama."</div>";
				$content_html.= "<div style='margin-top:20px; text-align:left; font-weight:bold;'>DIVISI : ".$result[$i]->Divisi."</div>";
				$content_html.= "<div style='width:100%;border:1px solid #CCC;text-align:center;'>";

				$content_html.= "<div style='width:100%;border-bottom:1px solid #CCC;text-align:center;min-height:30px'>";
				$content_html.= "	<div style='float:left;width:10%;line-height:30px;vertical-align:middle;'><b>TGL FAKTUR</b></div>";
				$content_html.= "	<div style='float:left;width:20%;line-height:30px;vertical-align:middle;'><b>NO FAKTUR</b></div>";
				$content_html.= "	<div style='float:left;width:25%;line-height:30px;vertical-align:middle;'><b>NO PO</b></div>";
				$content_html.= "	<div style='float:left;width:29%;line-height:30px;vertical-align:middle;'><b>KD BRG</b></div>";
				$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>QTY</b></div>";
				$content_html.= "	<div style='clear:both'></div>";
				$content_html.= "</div>";


				if($this->excel_flag == 1){
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'KATEGORI : ');
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, ($result[$i]->Kategori_Brg == 'P'?'PRODUK':'SPAREPART'));
					$currrow += 1;
					$currcol = 0;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA : ');
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $result[$i]->Nama);
					$currrow += 1;
					$currcol = 0;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'DIVISI : ');
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $result[$i]->Divisi);
					$currrow += 1;
					$currcol = 0;

					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'TGL FAKTUR');
					$currcol += 1; 
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'NO FAKTUR');
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'NO PO');
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'KD BRG');
					$currcol += 1;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'QTY');

					$currrow += 1;
					$currcol = 0;
				}
			}
			else{
				if($result[$i-1]->Kategori_Brg != $result[$i]->Kategori_Brg){
					$content_html.= "<div style='width:100%;border-top:1px solid #CCC;text-align:center;min-height:30px'>";
					$content_html.= "	<div style='float:left;width:84%;line-height:30px;vertical-align:middle;'><b>TOTAL ".$result[$i-1]->Divisi."</b></div>";
					$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>".$totalqty."</b></div>";
					$content_html.= "	<div style='clear:both'></div>";
					$content_html.= "</div></div>";
					if($this->excel_flag == 1){
						$currcol = 0; 
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$result[$i-1]->Divisi);
						$currcol += 4;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $totalqty);
						$currrow += 1;
						$currcol = 0; 
					}
					$totalqty = 0;

					$content_html.= "<div style='width:100%;text-align:center;min-height:25px'>";
					$content_html.= "<div style='margin-top:20px; text-align:left; font-weight:bold;'>KATEGORI : ".($result[$i]->Kategori_Brg == 'P'?'PRODUK':'SPAREPART')."</div>";
					$content_html.= "</div>";
					$content_html.= "<div style='width:100%;text-align:center;min-height:25px'>";
					$content_html.= "<div style='margin-top:20px; text-align:left; font-weight:bold;'>NAMA : ".$result[$i]->Nama."</div>";
					$content_html.= "<div style='width:100%;text-align:center;min-height:25px'>";
					$content_html.= "<div style='margin-top:20px; text-align:left; font-weight:bold;'>DIVISI : ".$result[$i]->Divisi."</div>";

					$content_html.= "<div style='width:100%;border:1px solid #CCC;text-align:center;'>";

					$content_html.= "<div style='width:100%;border-bottom:1px solid #CCC;text-align:center;min-height:30px'>";
					$content_html.= "	<div style='float:left;width:10%;line-height:30px;vertical-align:middle;'><b>TGL FAKTUR</b></div>";
					$content_html.= "	<div style='float:left;width:20%;line-height:30px;vertical-align:middle;'><b>NO FAKTUR</b></div>";
					$content_html.= "	<div style='float:left;width:25%;line-height:30px;vertical-align:middle;'><b>NO PO</b></div>";
					$content_html.= "	<div style='float:left;width:29%;line-height:30px;vertical-align:middle;'><b>KD BRG</b></div>";
					$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>QTY</b></div>";
					$content_html.= "	<div style='clear:both'></div>";
					$content_html.= "</div>";

					if($this->excel_flag == 1){
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'KATEGORI : ');
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, ($result[$i]->Kategori_Brg == 'P'?'PRODUK':'SPAREPART'));
						$currrow += 1;
						$currcol = 0;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA : ');
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $result[$i]->Nama);
						$currrow += 1;
						$currcol = 0;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'DIVISI : ');
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $result[$i]->Divisi);
						$currrow += 1;
						$currcol = 0;

						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'TGL FAKTUR');
						$currcol += 1; 
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'NO FAKTUR');
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'NO PO');
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'KD BRG');
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'QTY');

						$currrow += 1;
						$currcol = 0;
					}
				}

				if($result[$i-1]->Nama != $result[$i]->Nama){
					$content_html.= "<div style='width:100%;border-top:1px solid #CCC;text-align:center;min-height:30px'>";
					$content_html.= "	<div style='float:left;width:84%;line-height:30px;vertical-align:middle;'><b>TOTAL ".$result[$i-1]->Divisi."</b></div>";
					$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>".$totalqty."</b></div>";
					$content_html.= "	<div style='clear:both'></div>";
					$content_html.= "</div></div>";
					if($this->excel_flag == 1){
						$currcol = 0; 
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$result[$i-1]->Divisi);
						$currcol += 4;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $totalqty);
						$currrow += 1;
						$currcol = 0; 
					}
					$totalqty = 0;

					$content_html.= "<div style='width:100%;text-align:center;min-height:25px'>";
					$content_html.= "<div style='margin-top:20px; text-align:left; font-weight:bold;'>NAMA :".$result[$i]->Nama."</div>";

					$content_html.= "<div style='width:100%;text-align:center;min-height:25px'>";
					$content_html.= "<div style='margin-top:20px; text-align:left; font-weight:bold;'>DIVISI :".$result[$i]->Divisi."</div>";

					$content_html.= "<div style='width:100%;border:1px solid #CCC;text-align:center;'>";

					$content_html.= "<div style='width:100%;border-bottom:1px solid #CCC;text-align:center;min-height:30px'>";
					$content_html.= "	<div style='float:left;width:10%;line-height:30px;vertical-align:middle;'><b>TGL FAKTUR</b></div>";
					$content_html.= "	<div style='float:left;width:20%;line-height:30px;vertical-align:middle;'><b>NO FAKTUR</b></div>";
					$content_html.= "	<div style='float:left;width:25%;line-height:30px;vertical-align:middle;'><b>NO PO</b></div>";
					$content_html.= "	<div style='float:left;width:29%;line-height:30px;vertical-align:middle;'><b>KD BRG</b></div>";
					$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>QTY</b></div>";
					$content_html.= "	<div style='clear:both'></div>";
					$content_html.= "</div>";

					if($this->excel_flag == 1){
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA : ');
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $result[$i]->Nama);
						$currrow += 1;
						$currcol = 0;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'DIVISI : ');
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $result[$i]->Divisi);
						$currrow += 1;
						$currcol = 0;

						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'TGL FAKTUR');
						$currcol += 1; 
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'NO FAKTUR');
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'NO PO');
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'KD BRG');
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'QTY');

						$currrow += 1;
						$currcol = 0;
					}
				}

				if($result[$i-1]->Divisi != $result[$i]->Divisi){
					$content_html.= "<div style='width:100%;border-top:1px solid #CCC;text-align:center;min-height:30px'>";
					$content_html.= "	<div style='float:left;width:84%;line-height:30px;vertical-align:middle;'><b>TOTAL ".$result[$i-1]->Divisi."</b></div>";
					$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>".$totalqty."</b></div>";
					$content_html.= "	<div style='clear:both'></div>";
					$content_html.= "</div></div>";
					if($this->excel_flag == 1){
						$currcol = 0; 
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$result[$i-1]->Divisi);
						$currcol += 4;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $totalqty);
						$currrow += 1;
						$currcol = 0; 
					}
					$totalqty = 0;


					$content_html.= "<div style='width:100%;text-align:center;min-height:25px'>";
					$content_html.= "<div style='margin-top:20px; text-align:left; font-weight:bold;'>DIVISI : ".$result[$i]->Divisi."</div>";

					$content_html.= "<div style='width:100%;border:1px solid #CCC;text-align:center;'>";

					$content_html.= "<div style='width:100%;border-bottom:1px solid #CCC;text-align:center;min-height:30px'>";
					$content_html.= "	<div style='float:left;width:10%;line-height:30px;vertical-align:middle;'><b>TGL FAKTUR</b></div>";
					$content_html.= "	<div style='float:left;width:20%;line-height:30px;vertical-align:middle;'><b>NO FAKTUR</b></div>";
					$content_html.= "	<div style='float:left;width:25%;line-height:30px;vertical-align:middle;'><b>NO PO</b></div>";
					$content_html.= "	<div style='float:left;width:29%;line-height:30px;vertical-align:middle;'><b>KD BRG</b></div>";
					$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>QTY</b></div>";
					$content_html.= "	<div style='clear:both'></div>";
					$content_html.= "</div>";

					if($this->excel_flag == 1){
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'DIVISI : ');
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $result[$i]->Divisi);
						$currrow += 1;
						$currcol = 0;

						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'TGL FAKTUR');
						$currcol += 1; 
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'NO FAKTUR');
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'NO PO');
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'KD BRG');
						$currcol += 1;
						$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'QTY');

						$currrow += 1;
						$currcol = 0;
					}

				}
					
			}

			
			$content_html.= "<div style='width:100%;text-align:center;min-height:25px'>";
			$content_html.= "	<div style='float:left;width:10%;line-height:25px;vertical-align:middle;font-size:9pt;'>".date("d-M-Y", strtotime($result[$i]->Tgl_Faktur))."</div>";
			$content_html.= "	<div style='float:left;width:20%;line-height:25px;vertical-align:middle;font-size:9pt;'>".$result[$i]->No_Faktur."</div>";
			$content_html.= "	<div style='float:left;width:25%;line-height:25px;vertical-align:middle;font-size:9pt;'>".$result[$i]->No_OPJ."</div>";
			$content_html.= "	<div style='float:left;width:29%;line-height:25px;vertical-align:middle;font-size:9pt;'>".$result[$i]->Kd_Brg."</div>";
			$content_html.= "	<div style='float:left;width:8%;line-height:25px;vertical-align:middle;font-size:9pt;'>".$result[$i]->Qty."</div>";
			$content_html.= "	<div style='clear:both'></div>";
			$content_html.= "</div>";

			if($this->excel_flag == 1){
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, date("d-M-Y", strtotime($result[$i]->Tgl_Faktur)));
				$currcol += 1; 
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $result[$i]->No_Faktur);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $result[$i]->No_OPJ);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $result[$i]->Kd_Brg);
				$currcol += 1;
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $result[$i]->Qty);

				$currrow += 1;
				$currcol = 0;
			}

			$totalqty += $result[$i]->Qty;

			if($i == count($result)-1){
				$content_html.= "<div style='width:100%;border-top:1px solid #CCC;text-align:center;min-height:30px'>";
				$content_html.= "	<div style='float:left;width:84%;line-height:30px;vertical-align:middle;'><b>TOTAL ".$result[$i]->Divisi."</b></div>";
				$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>".$totalqty."</b></div>";
				$content_html.= "	<div style='clear:both'></div>";
				$content_html.= "</div></div>";

				if($this->excel_flag == 1){
					$currcol = 0; 
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL '.$result[$i]->Divisi);
					$currcol += 4;
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($currcol, $currrow, $totalqty);
					$currrow += 1;
					$currcol = 0;
				}
				$totalqty = 0;
			}
			
		}   

				

		if ($p_email=="Y")		
		{
			$content_html.= "<div style='font-size:10pt;font-style:italic;margin-top:25px;'>";
			$content_html.= "Email ini dikirimkan otomatis oleh sistem. Mohon tidak mereply email ini.";
			$content_html.= "</div>";
		}
		$content_html.= "</div></body></html>";

		//Kirim Emailnya
		if ($p_email=="Y")
		{
			$recipients = array();
			$recipient = json_decode(file_get_contents($this->API_URL."/LaporanEkspedisiPabrik/GetRecipients?api=".$api."&p_emailgroupid=".urlencode($row_group[0]->EmailGroupID)));
			for($k=0;$k<count($recipient);$k++)
			{
				array_push($recipients, $recipient[$k]->EmailAddress);
			}
			array_push($recipients, 'itdev.dist@bhakti.co.id');
			// print_r($recipients);
			// exit(1);
			$this->EmailModel->sendEmailReport($recipients, 'Laporan Ekspedisi Bhakti '.date("d-M-Y", strtotime($tgl_full)), $content_html);
			//echo "success";
		}

		if($this->excel_flag == 1){
			//PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
			$this->excel->getActiveSheet()->mergeCells('A1:J1');
			for ($i = 'A'; $i !=   $this->excel->getActiveSheet()->getHighestColumn(); $i++) {
			    $this->excel->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
			}

			$filename='LaporanEkspedisiBhakti['.date('Ymd').'].xls'; //save our workbook as this file name
			header('Content-Type: application/vnd.ms-excel'); //mime type
			header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
			header('Cache-Control: max-age=0'); //no cache
			$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
			//force user to download the Excel file without writing it to server's HD
			$objWriter->save('php://output');
		}

		$data['title'] = $page_title;
		$data['content_html'] = $content_html;		
		// $this->SetTemplate('template/login');
		if($content_html == ""){
			echo "<script>alert('no data found!'); window.close();</script>";
		}
		else
			$this->RenderView('LaporanEkspedisiBhaktiResultView',$data);
	}
}