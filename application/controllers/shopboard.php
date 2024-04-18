<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Shopboard extends MY_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('shopboardmodel');
		$this->load->model('ConfigSysModel');
		$this->load->model('GzipDecodeModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function index()
	{
		$merk = $this->shopboardmodel->merk();
    	$data['branch'] = $this->branch();
    	$data["supplier"] = $this->supplier();
    	$data["wilayah"] = $this->wilayah();
    	$data["merk"] = $merk;
		// echo json_encode($data);die;
		$this->RenderView('shopboardview',$data);
	}
	
	public function supplier()
	{
		$post['api'] = 'APITES';
		$URL = $this->API_URL."/MsSupplier/GetAllAktifSupplierList?api=APITES";
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_TIMEOUT => 6000
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		
		$supplier = array();
		if($httpcode==200){
			$res = json_decode($response, true);
			foreach($res as $row){
				$nama_supplier = current(explode(' - ',$row['Nama_Supplier']));
				$supplier[] = array('supplier' => trim(strtoupper($nama_supplier)));
			}
		}
		// echo json_encode($supplier);
		return $supplier;
	}

	public function branch()
	{
		$branch = array();
		foreach($this->BranchModel->GetList() as $row){
			$branch[] = array('branch_code' => $row->BranchCode, 'branch_name' => strtoupper($row->BranchName));
		}
		return $branch;
	}
	
	public function wilayah()
	{
		$post['api'] = 'APITES';
		$URL = $this->API_URL."/MsWilayah/GetAllWilayah?api=APITES";
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_TIMEOUT => 6000
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		
		$wilayah = array();
		if($httpcode==200){
			$res = json_decode($response, true);
			$wilayah = $res['data'];
		}
		// echo json_encode($wilayah);
		return $wilayah;
	}

	public function get_email_kajul($wilayah)
	{
		$post['api'] = 'APITES';
		$URL = $this->API_URL."/MsSalesman/GetKajulByWilayah?api=APITES&wilayah=".$wilayah;
		// echo $URL;die;
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_TIMEOUT => 6000
		));
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $response;die;
		
		if($httpcode==200){
			$res = json_decode($response, true);
			return $res['data'];
		}
		return array();
	}
	
	public function get_email_kajul_debug()
	{
		echo json_encode($this->get_email_kajul('OUTLIER'));
	}
	
	public function datatable_shopboard()
	{
		$param = $_GET;
		$res = $this->shopboardmodel->datatable_shopboard($param);
		echo $res;
	}
	
	public function datatable_shopboard_approval()
	{
		$param = $_GET;
		$res = $this->shopboardmodel->datatable_shopboard_approval($param);
		echo $res;
	}
	
	public function datatable_shopboard_approved()
	{
		$param = $_GET;
		$res = $this->shopboardmodel->datatable_shopboard_approved($param);
		echo $res;
	}
	
	public function datatable_shopboard_final()
	{
		$param = $_GET;
		$res = $this->shopboardmodel->datatable_shopboard_final($param);
		echo $res;
	}

	public function save()
	{
        // echo json_encode($_POST);die;
		$res = $this->shopboardmodel->save($_POST);
		if($res=='success'){
			echo json_encode(array('result'=>'success','msg'=>''));
		}
		else{
			echo json_encode(array('result'=>'failed','msg'=>$res));
		}
	}

	public function toko()
	{
        // echo json_encode($_POST);die;
		$res = $this->shopboardmodel->toko($_POST);
		if($res=='success'){
			echo json_encode(array('result'=>'success','msg'=>''));
		}
		else{
			echo json_encode(array('result'=>'failed','msg'=>$res));
		}
	}

	public function excel()
	{
		$res = $this->shopboardmodel->pengajuan_detail($_POST);
        // echo json_encode($res);die;
		if(count($res)>0){
			$nama_laporan = 'DAFTAR SHOPBOARD';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			$sheet->setTitle(substr($nama_laporan, 0, 31));
			
			$alignment_center = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
			$alignment_top = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP;
			$fillcolor = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
			
			$sheet->setCellValue('A1', $nama_laporan);
			$sheet->setCellValue('A2', 'PRINT DATE: '.date('d-M-Y H:i:s'));
			
			$sheet->getStyle('A1')->getFont()->setSize(14);
			$sheet->mergeCells('A1:L1');
			$sheet->mergeCells('A2:L2');
			$sheet->getStyle('A1:A2')->getAlignment()->setHorizontal($alignment_center);
			
			$currow=3;
			$curcol=1;
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'SUPPLIER');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'CABANG');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'WILAYAH');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NAMA TOKO');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'ALAMAT');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'KOTA');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'NO PO');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'MERK');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'UKURAN SHOPBOARD');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'PERIODE PERPANJANGAN');
			$sheet->setCellValueByColumnAndRow($curcol++, $currow, 'OK. PAJAK');
			$sheet->getStyle('A'.$currow.':L'.$currow)->getFill()->setFillType($fillcolor)->getStartColor()->setARGB('C0C0C0');
			$sheet->getStyle('A'.$currow.':L'.$currow)->getFont()->setBold(true);
			
			$no = 0;
			foreach($res as $row){
				$no++;
				$currow++;
				$curcol=1;
				
				$merk = "";
				if($row['merk1']!=""){
					$merk .= $row['merk1'];
				}
				if($row['merk2']!=""){
					$merk .= "\n".$row['merk2'];
				}
				if($row['merk3']!=""){
					$merk .= "\n".$row['merk3'];
				}
				if($row['merk4']!=""){
					$merk .= "\n".$row['merk4'];
				}
				if($row['merk5']!=""){
					$merk .= "\n".$row['merk5'];
				}
				$ukuran = "";
				if($row['ukuran1']!=""){
					$ukuran .= $row['ukuran1'];
				}
				if($row['ukuran2']!=""){
					$ukuran .= "\n".$row['ukuran2'];
				}
				if($row['ukuran3']!=""){
					$ukuran .= "\n".$row['ukuran3'];
				}
				if($row['ukuran4']!=""){
					$ukuran .= "\n".$row['ukuran4'];
				}
				if($row['ukuran5']!=""){
					$ukuran .= "\n".$row['ukuran5'];
				}
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $no);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['supplier']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['BranchName']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['wilayah']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['nama_toko']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['alamat']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['kota']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $row['no_po']);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $merk);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, $ukuran);
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, date('d-M-Y',strtotime($row['periode_start'])).' sd '.date('d-M-Y',strtotime($row['periode_end'])));
				$sheet->setCellValueByColumnAndRow($curcol++, $currow, date('d-M-Y',strtotime($row['final_date'])));
			}
			$sheet->getStyle('H4:'.'J'.$currow)->getAlignment()->setWrapText(true);
			$sheet->getStyle('A3:L'.$currow)->getAlignment()->setVertical($alignment_top);
			foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
				$sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$styleArray = [
				'borders' => [
					'allBorders' => [
						'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
					],
				],
			];
			$sheet ->getStyle('A3:L'.$currow)->applyFromArray($styleArray);
			// $sheet->freezePane('A6');
			$sheet->setSelectedCell('A1');
			$filename=$nama_laporan.' ['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');	// download file 
			exit();
		}
	}
	
	public function pengajuan()
	{
        // echo json_encode($_POST);die;
		$res = $this->shopboardmodel->pengajuan_detail($_POST);
		// echo json_encode($res);die;
		
		// SUSUN DATA KE DALAM MULTI DIMENSION ARRAY
		$email = array();
		foreach($res as $i=>$row){
			$email[$row['wilayah']][] = $row;
		}
		
		$msg = '';
		$error = 0;
		// AMBIL EMAIL KAJUL PER HEADER
		foreach($email as $wilayah=>$row){
			$kajul = $this->get_email_kajul($wilayah);
			// echo json_encode($kajul);die;
			if($kajul){
				$subject = 'Approval Perpanjangan Shopboard Wilayah '.$wilayah;
				$html = $this->create_email($row);
				
				$data = [];
				$data['to'] = $kajul['Email'];
				$data['cc'] = '"'.$_SESSION['logged_in']['useremail'].'","it.maintenance@bhakti.co.id"';
				
				// debug
				// $data['to'] = 'tjambuiliat@gmail.com';
				// $data['cc'] = '"it.aliat@jasakom.com","it.maintenance@bhakti.co.id"';
				
				$data['subject'] = $subject;
				$data['message'] = $html;
				$data['resend'] = 1;
				$result = $this->send_email($data);
				// echo $result; die;
				if(json_decode($result)=='SUCCESS'){
					$this->shopboardmodel->emailed($row, trim($kajul['Email']));
					$msg.=$wilayah.': Email berhasil dikirim ke '.trim($kajul['Email']).' cc ke '.trim($_SESSION['logged_in']['useremail']).'\n';
				}
				else{
					$msg.=$wilayah.': Email gagal dikirim ke '.trim($kajul['Email']).'\n';
				}
			}
			else{
				$error = 1;
				$msg.=$wilayah.': Email kajul tidak ditemukan!\n';
			}
		}
		if($error==0){
			echo json_encode(array('result'=>'success','msg'=>$msg));
		}
		else{
			echo json_encode(array('result'=>'failed','msg'=>$msg));
		}
	}
	
	public function finalize()
	{
        // echo json_encode($_POST);die;
		$res = $this->shopboardmodel->finalize($_POST);
		if($res=='success'){
			echo json_encode(array('result'=>'success','msg'=>''));
		}
		else{
			echo json_encode(array('result'=>'failed','msg'=>$res));
		}
	}
	
	public function send_email($data)
	{
		$url = base_url()."messageGateway/SendEmail";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($data),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// if($httpcode==200){
			return $response;
		// }
		// else{
			// return 'failed';
		// }
	}
	
	public function create_email($data)
	{
		$style="font-family:inherit; border:1px solid #2C458F; padding:5px 10px;background:#2C458F; color:#fff";
		$html="<h2>Approval Perpanjangan Shopboard </h2>";
		$html.="<table style='width:100%; border-collapse:collapse'>";
		$html.="<tr>";
		$html.="<th width='5%' style='".$style."'>No</th>";
		$html.="<th width='*' style='".$style."'>Nama Toko</th>";
		$html.="<th width='20%' style='".$style."'>Tgl. Expired</th>";
		$html.="</tr>";
		
		$style="font-family:inherit; border:1px solid #2C458F; padding:5px 10px";
		$no = 0;
		foreach($data as $row){
			$no++;
			$html.="<tr>";
			$html.="<td style='".$style."'>".$no."</td>";
			$html.="<td style='".$style."'>".$row['nama_toko']."</td>";
			$html.="<td style='".$style."' align='center'>".date('d-M-Y', strtotime($row['periode_end']))."</td>";
			$html.="</tr>";
		}
		$html.="</table>";
		// $html.="<em>Ini adalah email otomatis. Mohon untuk tidak membalas email ini.</em>";
		$html.="<big>Untuk <b>APPROVAL</b> dan <b>REJECT</b> harap masuk ke web myCompany</big>";
		
		return $html;
	}

	public function delete_po()
	{
        // echo json_encode($_POST);die;
		$res = $this->shopboardmodel->delete_po($_POST);
		if($res=='success'){
			echo json_encode(array('result'=>'success','msg'=>''));
		}
		else{
			echo json_encode(array('result'=>'failed','msg'=>$res));
		}
	}

	public function batal_po()
	{
        // echo json_encode($_POST);die;
		$res = $this->shopboardmodel->batal_po($_POST);
		if($res=='success'){
			echo json_encode(array('result'=>'success','msg'=>''));
		}
		else{
			echo json_encode(array('result'=>'failed','msg'=>$res));
		}
	}

	public function deactivate()
	{
        // echo json_encode($_POST);die;
		$res = $this->shopboardmodel->deactivate($_POST);
		if($res=='success'){
			echo json_encode(array('result'=>'success','msg'=>''));
		}
		else{
			echo json_encode(array('result'=>'failed','msg'=>$res));
		}
	}

	public function reactivate()
	{
        // echo json_encode($_POST);die;
		$res = $this->shopboardmodel->reactivate($_POST);
		if($res=='success'){
			echo json_encode(array('result'=>'success','msg'=>''));
		}
		else{
			echo json_encode(array('result'=>'failed','msg'=>$res));
		}
	}

	public function detail($id)
	{
        // echo json_encode($_POST);die;
		$res = $this->shopboardmodel->detail($id);
		echo $res;
	}

	public function detail_po($id)
	{
        // echo json_encode($_POST);die;
		$res = $this->shopboardmodel->detail_po($id);
		echo $res;
	}
	
	public function import()
	{
		if(ISSET($_POST['submit'])){
			$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($_FILES['excel']['tmp_name']);
			$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
			$spreadsheet = $reader->load($_FILES['excel']['tmp_name']);
			$import = $spreadsheet->getActiveSheet()->toArray();
			// echo json_encode($import);die;
			$header = array_shift($import);
			// echo json_encode($header);die;
			
			$max_col = 0;
			foreach($import as $row){
				if(count($row)>$max_col){
					$max_col = count($row);
				}
			}
			// echo $max_col; die;
			
			// jumlah po maksimal yang ada dalam sheet;
			$jum_po = floor(($max_col-7)/6);
			// echo $jum_po; die;
			
			$result = [];
			$error = '';
			$branch = array_column($this->branch(), 'branch_code');
			$cabang = array_column($this->branch(), 'branch_name');
			// echo json_encode($branch);die;
			$supplier = array_column($this->supplier(), 'supplier');
			$wilayah = array_column($this->wilayah(), 'wilayah');
			// echo json_encode($supplier);die;
			
			foreach($import as $i=>$row){
				if($row[0]=='' || $row[1]==''){
					continue;
				}
				
				if(!in_array(strtoupper($row[1]), $cabang)){
					$error = 'Baris '.($i+1).': Cabang '.strtoupper($row[1]).' tidak ditemukan! pastikan tidak ada salah dalam pengetikan.';
					break;
				}
				
				if(!in_array(strtoupper($row[2]), $wilayah)){
					$error = 'Baris '.($i+1).': Wilayah '.strtoupper($row[2]).' tidak ditemukan! pastikan tidak ada salah dalam pengetikan.';
					break;
				}
				
				if(!in_array(strtoupper($row[6]), $supplier)){
					$error = 'Baris '.($i+1).': Supplier '.strtoupper($row[6]).' tidak ditemukan! pastikan tidak ada salah dalam pengetikan.';
					break;
				}
				
				$r = [];
				$r['branchcode'] = $branch[array_search(strtoupper($row[1]),$cabang)];
				$r['cabang'] = strtoupper($row[1]);
				$r['wilayah'] = $row[2];
				$r['nama_toko'] = $row[3];
				$r['alamat'] = $row[4];
				$r['kota'] = $row[5];
				$r['supplier'] = strtoupper($row[6]);
				for($p=0;$p<=$jum_po-1;$p++){
					// echo $p.'<br>';
					$idx = 6 + ($p * 6);
					// echo $idx.'<br>';
					$po = [];
					$po['merk'][] = $row[($idx+1)];
					$po['ukuran'][] = $row[($idx+2)];
					$po['no_po'] = $row[($idx+3)];
					$po['pajak'] = $row[($idx+4)];
					$po['periode_start'] = $row[($idx+5)];
					$po['periode_end'] = $row[($idx+6)];
					
					for($m=$i+1;$m<$i+5;$m++){
						if(ISSET($import[$m])){
							if($import[$m][0]=='' && $import[$m][1]==''){
								if($import[$m][($idx+1)]!='' && $import[$m][($idx+2)]!=''){
									$po['merk'][] = $import[$m][($idx+1)];
									$po['ukuran'][] = $import[$m][($idx+2)];
								}
							}
							else{
								break;
							}
						}
					}
					$r['po'][] = $po;
				}
				$result[] = $r;
			}
			// echo json_encode($result);die;
			
			$data['jum_po'] = $jum_po;
			$data['error'] = $error;
			$data['result'] = html_escape($result);
			$this->RenderView('Shopboardimportview', $data);
		}
		elseif(ISSET($_POST['save_import'])){
			$data = json_decode($_POST['data'], true);
			// echo json_encode($data); die;
			$res = $this->shopboardmodel->import($data);
			echo $res;
		}
		else{
			if($_SESSION['logged_in']['userLevel']!='STAFF') {
				$this->RenderView('Shopboardimportview');
			}
			else{
				echo "<script>";
				echo "alert('Hanya SPV/Kabag yang boleh akses menu import!');";
				echo "window.location.href = '".base_url('shopboard')."'";
				echo "</script>";
			}
		}	
	}



	
}
