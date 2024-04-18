<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class Reportserahterimabpb extends MY_Controller 
	{
		public $excel_flag = 0; 
		public function __construct()
		{
			parent::__construct(); 
			$this->load->model('HelperModel');
			$this->load->model('GzipDecodeModel');
			$this->load->helper('FormLibrary');
			$this->load->library('email');
			$this->load->library('excel');
		}
		
		public function index()
		{ 
			$api = 'APITES';   
			$url = $_SESSION["conn"]->AlamatWebService;
			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;  
        	//$url = "http://localhost:100/"; 

			$listgudang = file_get_contents($url.API_BKT."/MasterGudang/GetListGudang3?api=".$api."&svr=".$svr."&db=".$db);
        	$listgudang = $this->GzipDecodeModel->_decodeGzip($listgudang);
			$data['listgudang'] = $gudang;

			$listgroupgudang = file_get_contents($url.API_BKT."/MasterGudang/GetListGroupGudang?api=".$api."&svr=".$svr."&db=".$db);
        	$listgroupgudang = $this->GzipDecodeModel->_decodeGzip($listgroupgudang);
			$data['listgroupgudang'] = $listgroupgudang;

			$data['title'] = 'Report Serah Terima BPB/BPRPJ';
			$data['formDest'] = "Reportserahterimabpb/Proses";
			
			$this->RenderView('Reportserahterimabpbview',$data);
		} 
		public function GetListGudangByKdGroupGudang()
		{ 
			$api = 'APITES';   
			$url = $_SESSION["conn"]->AlamatWebService;
			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database;  
        	//$url = "http://localhost:100/"; 

			$listgudang = ""; 
			$kdgroupgudang = $this->input->get('kdgroupgudang');
			$GetListGudangByKdGroupGudang = json_decode(file_get_contents($url.API_BKT."/MasterGudang/GetListGudangByKdGroupGudang?api=".$api."&svr=".$svr."&db=".$db."&kdgroup_gudang=".$kdgroupgudang.""));

			if(count($GetListGudangByKdGroupGudang->data)>0){
				$count = 0;
				foreach ($GetListGudangByKdGroupGudang->data as $key => $value) {
					if ($count==0)
						$listgudang = $value->Gudang." | ".$value->NamaGudang;
					else
						$listgudang .= ";;".$value->Gudang." | ".$value->NamaGudang;
					$count++;
				}
			} 	   
			echo json_encode($listgudang);
		} 

		public function Proses()
		{  
			//ini_set('max_execution_time', '300');
			ini_set("pcre.backtrack_limit", "5000000"); 
	      	set_time_limit(60);

			$data = array(); 
			   
			$dp1 = $_POST['dp1']; 
			$dp2 = $_POST['dp2']; 
 	
 			$cboGudang ="";
 			if(isset($_POST['cboGudang'])){
				$cboGudang = $_POST['cboGudang'];
			} 
 			$cboGroupGudang ="";
 			if(isset($_POST['cboGroupGudang'])){
				$cboGroupGudang = $_POST['cboGroupGudang'];
			}   

			$listgd = $_POST['listgd'];  
  
			if (isset($_POST["btnPdf"])) 
				$this->excel_flag = 0; 
			else 
				$this->excel_flag = 1;  
 
			$api = 'APITES';  

			$p_start_date = date("d-M-Y", strtotime($dp1)); 
			$p_end_date = date("d-M-Y", strtotime($dp2));   

			$url = $_SESSION["conn"]->AlamatWebService;
			$svr = $_SESSION["conn"]->Server;
			$db  = $_SESSION["conn"]->Database; 
        	//$url = "http://localhost:100/"; 
  
			$url = $url.API_BKT."/Reportserahterimabpb/GetReportSerahTerimaBPB_BPRPJ?api=".$api."&svr=".$svr."&db=".$db."&p_start=".$p_start_date."&p_end=".$p_end_date."&kd_gudang=".$cboGudang."&kd_group_gudang=".$cboGroupGudang;
			 

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60, 
			));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl); 
			$result = json_decode($result,true); 
			if(count($result["data"])==0){
				exit('Tidak ada data');
			}
			else
			{  
				if($this->excel_flag == 1)
					$this->Report_603_A_Excel($p_start_date,$p_end_date,$result["data"],$listgd); 
				else
					$this->Report_603_A_Pdf($p_start_date,$p_end_date,$result["data"],$listgd);    

			}			 

		}
 
		//PDF
		public function Report_603_A_Pdf($p_start_date,$p_end_date,$result,$listgd)
		{ 
			$header='<table width="100%">';

	        $header.='<tr>'; 
	        $header.='<td>'.date('d-M-Y H:i:s').'</td>';
	        $header.='<td></td>';
	        $header.='<td align="right" width="200px" style="padding:5px">Halaman {PAGENO} / {nbpg}</td>'; 
	        $header.='</tr>';

	        $header.='<tr>';
	        $header.='<td colspan="3" align="center"><h2><b>LAPORAN SERAH TERIMA BPB</b></h2></td>';
	        $header.='</tr><tr><td colspan="3" style="padding:5px"></td></tr><tr>';
	        $header.='<td>PERIODE : '.$p_start_date.' S/D '.$p_end_date.'</td>';
	        $header.='<td></td>'; 
	        $header.='</tr>'; 
	        $header.='</table>';

	        $content='';
 
			$ptype = "";
			$group1 = "data_awal";
   
        	$content.='<table width="100%">';


				$content.='<thead><tr>';
			    $content.='<td width="20%" ><b>Gudang</b></td>';
			    $content.='<td width="20%" ><b></b></td>'; 
			    $content.='<td width="20%" ><b>Tanggal</b></td>';
			    $content.='<td width="20%" ><b>No BPB</b></td>';  
			    $content.='<td width="20%" ><b>No BPRPJ</b></td>';  
			    $content.='</tr><tr><td colspan="5" style="border-top:thin solid #000;"></td></tr></thead><tbody>'; 


			$kd_groupgd = "";
			$nm_groupgd = "";
			$count_group_gudang = 0;
			foreach($result as $hd){   

				if ($kd_groupgd=="")
				{
					$count_group_gudang = 0;
					$content.='<tr>';
			        $content.='<td colspan="5"><b>Group Gudang : '.$hd['Kd_GroupGudang'].' - '.$hd['Nm_GroupGudang'].'</b></td>'; 
			        $content.='</tr>'; 
				}
				else
				{ 
					if ($kd_groupgd != $hd['Kd_GroupGudang'])
					{   
						$content.='<tr><td colspan="5" style="border-top:thin solid #000;"></td></tr>';
						$content.='<tr>';
			            $content.='<td colspan="4"><b>TOTAL BPB/BPRPJ ('.$kd_groupgd.' - '.$nm_groupgd.')</b></td>';
			            $content.='<td ><b>'.$count_group_gudang.'</b></td>'; 
			            $content.='</tr>';  

						$count_group_gudang = 0;
						$content.='<tr><td colspan="5" style="padding:10px;"></td></tr>';
						$content.='<tr>';
			            $content.='<td colspan="5"><b>Group Gudang : '.$hd['Kd_GroupGudang'].' - '.$hd['Nm_GroupGudang'].'</b></td>'; 
			            $content.='</tr>'; 
					}
				}

				$content.='<tr>';
	            $content.='<td colspan="2" >'.$hd['Kd_Gudang'].' - '.$hd['Nm_Gudang'].'</td>'; 
	            $content.='<td>'.date("d-M-Y",strtotime($hd['Tgl_BPB'])).'</td>'; 
	            $content.='<td>'.$hd['No_BPB'].'</td>'; 
	            $content.='<td>'.$hd['No_BPRPJ'].'</td>';  
	            $content.='</tr>'; 

				$kd_groupgd = $hd['Kd_GroupGudang'];
				$nm_groupgd = $hd['Nm_GroupGudang'];
				$count_group_gudang++; 
			}  

			$content.='<tr><td colspan="5" style="border-top:thin solid #000;"></td></tr>';
			$content.='<tr>';
			$content.='<td colspan="4"><b>TOTAL BPB/BPRPJ ('.$kd_groupgd.' - '.$nm_groupgd.')</b></td>';
			$content.='<td ><b>'.$count_group_gudang.'</b></td>'; 
			$content.='</tr>'; 

			if ($listgd!="")
			{ 
				$content.='<tr><td colspan="5" style="padding:10px;"></td></tr>';
				$content.='<tr>';
				$content.='<td colspan="5"><b>'.$listgd.'</b></td>'; 
				$content.='</tr>'; 
			}

			$content.='<tr><td colspan="5" style="padding:10px;"></td></tr>';
			$content.='<tr><td colspan="5" style="border-top:thin solid #000;"></td></tr>';
			$content.='<tr><td colspan="5" style="padding:10px;"></td></tr>';


			$content.='<tr>';
	  		$content.='<td></td>'; 
	   		$content.='<td colspan="2" >Tanggal Cetak : '.date('d-M-Y H:i:s').'</td>';  
	     	$content.='<td>Tanggal Cetak : </td>'; 
	      	$content.='<td></td>';  
	      	$content.='</tr>'; 

			$content.='<tr><td colspan="5" style="padding:5px;"></td></tr>';

			$content.='<tr >';
	  		$content.='<td></td>'; 
	   		$content.='<td valign="top" style="border:1px solid #000;height:150px"> Yg Menyerahkan</td>'; 
	  		$content.='<td></td>'; 
	     	$content.='<td valign="top" style="border:1px solid #000;height:150px"> Yg Menerima</td>'; 
	      	$content.='<td></td>';  
	      	$content.='</tr>';


        	$content.='</tbody></table>';

	        $mpdf = new \Mpdf\Mpdf(array(
	          'mode' => '',
	          'format' => 'A4',
	          'default_font_size' => 8,
	          'default_font' => 'arial',
	          'margin_left' => 10,
	          'margin_right' => 10,
	          'margin_top' => 33,
	          'margin_bottom' => 10,
	          'margin_header' => 10,
	          'margin_footer' => 5,
	          'orientation' => 'P'
	        ));
	        $mpdf->SetHTMLHeader($header,'','1');
	        $mpdf->WriteHTML($content);
	        $mpdf->Output();
		}  
		//PDF
 
 		//EXCEL
		public function Report_603_A_Excel($p_start_date,$p_end_date,$result,$listgd)
		{  
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID; 
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
  
			$sheet->setTitle("SERAH_TERIMA_BPB");
 
			$sheet->setCellValue('A1', "LAPORAN SERAH TERIMA BPB");
			$sheet->mergeCells('A1:D1');
			$sheet->getStyle('A1:D1')->getAlignment()->setHorizontal($alignment_center);
			$sheet->getStyle('A1')->getFont()->setSize(20); 
			$sheet->setCellValue('A2', 'Print Date : '.date('d-M-Y H:i:s'));
			$sheet->setCellValue('A3', 'Periode : '.$p_start_date.' sd '.$p_end_date); 
			$pos = 5;
			$sheet->getColumnDimension('A')->setWidth(40); 
			$sheet->getColumnDimension('B')->setWidth(20); 
			$sheet->getColumnDimension('C')->setWidth(20); 
			$sheet->getColumnDimension('D')->setWidth(20);  
			$sheet->getStyle("A".$pos.":D".$pos)->getFont()->setBold(true); 
			$sheet->setCellValue('A'.$pos, 'Gudang');
			$sheet->setCellValue('B'.$pos, 'Tanggal');
			$sheet->setCellValue('C'.$pos, 'No BPB');
			$sheet->setCellValue('D'.$pos, 'No BPRPJ'); 
			$sheet->getStyle('A'.$pos.':D'.$pos)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('FFFACD');
 
			$i = 0;   


			$kd_groupgd = "";
			$nm_groupgd = "";
			$count_group_gudang = 0;
			foreach($result as $hd){   

				if ($kd_groupgd=="")
				{
					$count_group_gudang = 0;
					$pos++;
					$sheet->setCellValue('A'.$pos, 'Group Gudang : '.$hd['Kd_GroupGudang'].' - '.$hd['Nm_GroupGudang']);
					$sheet->getStyle("A".$pos.":D".$pos)->getFont()->setBold(true); 
					$sheet->mergeCells("A".$pos.":D".$pos);
				}
				else
				{ 
					if ($kd_groupgd != $hd['Kd_GroupGudang'])
					{    
						$pos++;
						$sheet->setCellValue('A'.$pos, 'TOTAL BPB/BPRPJ ('.$kd_groupgd.' - '.$nm_groupgd.')');
						$sheet->setCellValue('D'.$pos, $count_group_gudang); 
						$sheet->mergeCells("A".$pos.":C".$pos);
						$sheet->getStyle("A".$pos.":D".$pos)->getFont()->setBold(true); 

						$count_group_gudang = 0;  
						$pos= $pos+2;
						$sheet->setCellValue('A'.$pos, 'Group Gudang : '.$hd['Kd_GroupGudang'].' - '.$hd['Nm_GroupGudang']);
						$sheet->getStyle("A".$pos.":D".$pos)->getFont()->setBold(true); 
						$sheet->mergeCells("A".$pos.":D".$pos);
					}
				}

				$pos++;
				$sheet->setCellValue('A'.$pos, $hd['Kd_Gudang'].' - '.$hd['Nm_Gudang']);
				$sheet->setCellValue('B'.$pos, date("d-M-Y",strtotime($hd['Tgl_BPB'])));
				$sheet->setCellValue('C'.$pos, $hd['No_BPB']);
				$sheet->setCellValue('D'.$pos, $hd['No_BPRPJ']);  

				$kd_groupgd = $hd['Kd_GroupGudang'];
				$nm_groupgd = $hd['Nm_GroupGudang'];
				$count_group_gudang++; 
			}
			$pos++;
			$sheet->setCellValue('A'.$pos, 'TOTAL BPB/BPRPJ ('.$kd_groupgd.' - '.$nm_groupgd.')');
			$sheet->setCellValue('D'.$pos, $count_group_gudang);
			$sheet->mergeCells("A".$pos.":C".$pos);
			$sheet->getStyle("A".$pos.":D".$pos)->getFont()->setBold(true); 

			if ($listgd!="")
			{ 
				$pos= $pos+2;
				$sheet->setCellValue('A'.$pos, $listgd);
				$sheet->getStyle("A".$pos.":D".$pos)->getFont()->setBold(true); 
				$sheet->mergeCells("A".$pos.":D".$pos); 
			}
 
			$filename='Reportserahterimabpb['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 
			exit(); 
		}   
 		//EXCEL 
	}							