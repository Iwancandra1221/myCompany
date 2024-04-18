<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Tagihandiluarpembelian extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model('TagihandiluarpembelianModel');
		$this->load->model('GzipDecodeModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		$this->NPWPBHAKTI = '018598896038000';
	}

	function index(){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		if($_SESSION["can_read"]==true){
			$data['role'] = $this->TagihandiluarpembelianModel->role();
			$this->RenderView('Tagihandiluarpembelian',$data);
		}
	}

	function getListTagihan(){
		$url = $this->API_URL."/TagihanPajakMasukan/GetListTagihan?api=APITES";
		// $url = "http://localhost:90/webAPI/TagihanPajakMasukan/GetListTagihan?api=APITES";

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		echo $response; 
		die;	

	}

	function getPemberijasa($pemberijasa=''){

		if(!empty($pemberijasa) && $pemberijasa=='Supplier'){
			$url = $this->API_URL."/MsSupplier/GetListAllSupplier?api=APITES";
			// $url = "http://localhost:90/webAPI/MsSupplier/GetListAllSupplier?api=APITES";
		}else{
			$url = $this->API_URL."/MsDealer/GetListDealerAll?api=APITES";
			// $url = "http://localhost:90/webAPI/MsDealer/GetListDealerAll?api=APITES";
		}

		$post['api'] = 'APITES';
		$post['get'] = $this->input->get();

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);

		if($httpcode!=200){

			$params['Remarks']="FAILED - URL ".$url." sedang tidak bisa diakses! HTTP Code:".$httpcode;
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['Module'] = 'Tagihandiluarpembelian';
			$params['TrxID'] = 'Pemberijasa';
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			echo 'URL "'.$url.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode; die;
	
		}else{

			// $result = json_decode($response);
			$result = $this->GzipDecodeModel->_decodeGzip($response);

			if($result->result =='success'){
				$data['list'] = $result->data;
				$data_list=array();

				if(!empty($data['list'])){
					$listdata=json_decode(json_encode($data['list']->data));

					if($pemberijasa=='Supplier'){
						foreach($listdata as $key => $d) {

							$onclick = "'".str_replace('=','',base64_encode($d->Kd_Supl))."','".str_replace('=','',base64_encode($d->Nm_Supl))."','".str_replace('=','',base64_encode($d->NPWP))."','".str_replace('=','',base64_encode($d->Alm_Supl))."','".str_replace('=','',base64_encode($d->Cabang))."','supplier'";

							$tamp=array();
							$tamp[]='<span onclick="selectpemberijasa('.$onclick.')">'.$d->Kd_Supl.'</span>';
							$tamp[]='<span onclick="selectpemberijasa('.$onclick.')">'.$d->Nm_Supl.'</span>';

							$data_list[]=$tamp;
						}
					}else{
						foreach($listdata as $key => $d) {

							$onclick = "'".str_replace('=','',base64_encode($d->kd_plg))."','".str_replace('=','',base64_encode($d->nm_plg))."','".str_replace('=','',base64_encode($d->NPWP))."','".str_replace('=','',base64_encode($d->alm_plg))."','','customer'";

							$tamp=array();
							$tamp[]='<span onclick="selectpemberijasa('.$onclick.')">'.$d->kd_plg.'</span>';
							$tamp[]='<span onclick="selectpemberijasa('.$onclick.')">'.$d->nm_plg.'</span>';


							$data_list[]=$tamp;
						}
					}


					$total=$data['list']->total;

				}else{
					$total=0;
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
			}else{
				$params['Remarks']="FAILED - ".$result->error;
				$params['LogDate'] = date("Y-m-d H:i:s");
				$params['Module'] = 'Tagihandiluarpembelian';
				$params['TrxID'] = 'Pemberijasa';
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				echo $result->error; die;
			}

		}		
	}

	function getObjekpajak(){

		$url = $this->API_URL."/Msobjekpajak/GetList";
		// $url = "http://localhost:90/webAPI/Msobjekpajak/GetList";

		$post['api'] = 'APITES';
		$post['get'] = $this->input->get();

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);

		if($httpcode!=200){

			$params['Remarks']="FAILED - URL ".$url." sedang tidak bisa diakses! HTTP Code:".$httpcode;
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['Module'] = 'Tagihandiluarpembelian';
			$params['TrxID'] = 'Objekpajak';
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			echo 'URL "'.$url.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode; die;
	
		}else{

			$result = json_decode($response);

			if($result->result =='success'){
				$data['list'] = $result->data;
				$data_list=array();

				if(!empty($data['list'])){
					$listdata=json_decode(json_encode($data['list']->data));

					foreach($listdata as $key => $d) {

						$onclick = "'".$d->kode_objek_pajak."','".$d->nama_objek_pajak."','".$d->pasal_pph."'";

						$tamp=array();
						$tamp[]='<span onclick="selectkodepajak('.$onclick.')">'.$d->kode_objek_pajak.'</span>';
						$tamp[]='<span onclick="selectkodepajak('.$onclick.')">'.$d->nama_objek_pajak.'</span>';
						$tamp[]='<span onclick="selectkodepajak('.$onclick.')">'.$d->pasal_pph.'</span>';

						$data_list[]=$tamp;
					}

					$total=$data['list']->total;

				}else{
					$total=0;
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
			}else{
				$params['Remarks']="FAILED - ".$result->error;
				$params['LogDate'] = date("Y-m-d H:i:s");
				$params['Module'] = 'Tagihandiluarpembelian';
				$params['TrxID'] = 'Objekpajak';
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				echo $result->error; die;
			}

		}	

	}

	function getKategori(){
		if(!empty($this->input->post('pemberijasa'))){
			$pemberijasa = $this->input->post('pemberijasa');
		}else{
			$pemberijasa = 'Supplier';
		}
		$url = $this->API_URL."/MsKategori/GetList?api=APITES&pemberijasa=".$pemberijasa;
		// $url = "http://localhost:90/webAPI/MsKategori/GetList?api=APITES&pemberijasa=".$pemberijasa;

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		echo $response; 
		die;	

	}

	function getDivisi(){

		$url = $this->API_URL."/MsDivisi/GetListDivisionName?api=APITES";
		// $url = "http://localhost:90/webAPI/MsDivisi/GetListDivisionName?api=APITES";

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		echo $response; 
		die;	

	}

	function getTarifPPN(){

		$TglTransaksi = $this->input->post('TglTransaksi');

		$url = $this->API_URL."/TagihanPajakMasukan/GetListTarifPPN?api=APITES&TglTransaksi=".$TglTransaksi;
		// $url = "http://localhost:90/webAPI/TagihanPajakMasukan/GetListTarifPPN?api=APITES&TglTransaksi=".$TglTransaksi;

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		echo $response; 
		die;	

	}

	function getFakturPajakMasukan($bulanfp='',$tahunfp=''){

		$url = $this->API_URL."/TagihanPajakMasukan/GetListFakturPajakMasukan";
		// $url = "http://localhost:90/webAPI/TagihanPajakMasukan/GetListFakturPajakMasukan";

		$post['api'] = 'APITES';
		$post['bulan'] = $bulanfp;
		$post['tahun'] = $tahunfp;
		$post['get'] = $this->input->get();

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);

		if($httpcode!=200){

			$params['Remarks']="FAILED - URL ".$url." sedang tidak bisa diakses! HTTP Code:".$httpcode;
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['Module'] = 'Tagihandiluarpembelian';
			$params['TrxID'] = 'FakturPajakMasukan';
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			echo 'URL "'.$url.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode; die;
	
		}else{

			$result = json_decode($response);
			if($result->result =='success'){
				$data['list'] = $result->data;
				$data_list=array();

				if(!empty($data['list'])){
					$listdata=json_decode(json_encode($data['list']->data));

					foreach($listdata as $key => $d) {
						$tanggalFormatted = date_format(date_create($d->tanggal),'d-m-Y');
						$bln = date_format(date_create($d->tanggal),'m');
						$thn = date_format(date_create($d->tanggal),'Y');

						$onclick = "'".$d->noFp."','".$tanggalFormatted."','".$bln."','".$thn."','".$d->BulanMasaPajak."','".$d->TahunMasaPajak."','".$d->DPP."','".$d->PPN."','".$d->Total."'";

						$tamp=array();
						$tamp[]='<span onclick="getNoFP('.$onclick.')">'.$d->noFp.'</span>';
						$tamp[]='<span onclick="getNoFP('.$onclick.')">'.$tanggalFormatted.'</span>';
						$tamp[]='<span onclick="getNoFP('.$onclick.')">'.$d->Nama.'</span>';
						$tamp[]='<span onclick="getNoFP('.$onclick.')">'.$d->DPP.'</span>';
						$tamp[]='<span onclick="getNoFP('.$onclick.')">'.$d->PPN.'</span>';
						$tamp[]='<span onclick="getNoFP('.$onclick.')">'.$d->Total.'</span>';

						$data_list[]=$tamp;
					}

					$total=$data['list']->total;

				}else{
					$total=0;
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
			}else{
				$params['Remarks']="FAILED - ".$result->error;
				$params['LogDate'] = date("Y-m-d H:i:s");
				$params['Module'] = 'Tagihandiluarpembelian';
				$params['TrxID'] = 'FakturPajakMasukan';
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				echo $result->error; die;
			}

		}	
	}

	function getTagihan(){
		$url = $this->API_URL."/TagihanPajakMasukan/GetTagihan";
		// $url = "http://localhost:90/webAPI/TagihanPajakMasukan/GetTagihan";

		$post['api'] = 'APITES';
		$post['get'] = $this->input->get();

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);

		if($httpcode!=200){

			$params['Remarks']="FAILED - URL ".$url." sedang tidak bisa diakses! HTTP Code:".$httpcode;
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['Module'] = 'Tagihandiluarpembelian';
			$params['TrxID'] = 'ListTagihan';
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			echo 'URL "'.$url.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode; die;
	
		}else{
			$result = json_decode($response);
			if($result->result =='success'){
				$data['list'] = $result->data;
				$data_list=array();

				if(!empty($data['list'])){
					$listdata=json_decode(json_encode($data['list']->data));

					foreach($listdata as $key => $d) {
						$tgl_transaksi = date_format(date_create($d->tgl_transaksi),'d-m-Y');
						$tgl_tagihan = date_format(date_create($d->tgl_tagihan),'d-m-Y');

						$onclick = "'".$d->no_transaksi."','".$d->no_tagihan."','".$tgl_transaksi."','".$tgl_tagihan."','".$d->NPWP."','".$d->NIK."','".$d->Alm_suplcust."'";

						$tamp=array();
						$tamp[]='<span onclick="getTagihan('.$onclick.')">'.$d->no_transaksi.'</span>';
						$tamp[]='<span onclick="getTagihan('.$onclick.')">'.$tgl_transaksi.'</span>';
						$tamp[]='<span onclick="getTagihan('.$onclick.')">'.$d->no_tagihan.'</span>';
						$tamp[]='<span onclick="getTagihan('.$onclick.')">'.$d->NoFP.'</span>';
						$tamp[]='<span onclick="getTagihan('.$onclick.')">'.$d->nama_pemberi_jasa.'</span>';
						$tamp[]='<span onclick="getTagihan('.$onclick.')">'.$d->DPP.'</span>';
						$tamp[]='<span onclick="getTagihan('.$onclick.')">'.$d->PPN.'</span>';
						$tamp[]='<span onclick="getTagihan('.$onclick.')">'.$d->Total.'</span>';

						$data_list[]=$tamp;
					}

					$total=$data['list']->total;

				}else{
					$total=0;
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
			}else{
				$params['Remarks']="FAILED - ".$result->error;
				$params['LogDate'] = date("Y-m-d H:i:s");
				$params['Module'] = 'Tagihandiluarpembelian';
				$params['TrxID'] = 'ListTagihan';
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				echo $result->error; die;
			}
		}	
	}

	function proses(){
		$url = $this->API_URL."/TagihanPajakMasukan/prosestagihan";
		// $url = "http://localhost:90/webAPI/TagihanPajakMasukan/prosestagihan";

		$post['api'] 		= 'APITES';
		$post['UserInput'] 	= $_SESSION['logged_in']['username'];
		$post['data'] 		= $this->input->post();

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		echo $response; 
		die;
	}

	function getTagihanView(){
		$url = $this->API_URL."/TagihanPajakMasukan/getTagihanView";
		// $url = "http://localhost:90/webAPI/TagihanPajakMasukan/getTagihanView";

		$post['api'] 		= 'APITES';
		$post['data'] 		= $this->input->post();

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		echo $response; 
		die;
	}

	function getDataExcel($dld){

		$namabulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

		$url = $this->API_URL."/TagihanPajakMasukan/getDataExcel";
		// $url = "http://localhost:90/webAPI/TagihanPajakMasukan/getDataExcel";

		$post['api'] 		= 'APITES';
		$post['bulan'] 		= $this->input->get('bulan');
		$post['tahun'] 		= $this->input->get('tahun');
		$post['proses'] 	= $dld;

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);

		$result = json_decode($response);
		if($result->result=='sukses'){

			
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
						
			

			if($dld=='buku_ppn'){
				$filename = 'Buku PPN Masukan.xlsx';
				$title = 'Buku PPN Masukan';
				$sheet->setTitle($title);

				$sheet->setTitle($filename);
				$sheet->setCellValue('A1', 'PAJAK MASUKAN ATAS BIAYA');
				
				$sheet->getStyle('A1')->getFont()->setSize(13);
				$sheet->setCellValue('A2', 'MASA PAJAK : '.$namabulan[$post['bulan']].' '.$post['tahun']);

				$currcol = 1;
				$currrow = 4;
						
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO INPUT');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL FP');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO FP');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO TAGIHAN');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NPWP');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA PKP');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KETERANGAN');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DPP');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PPN');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TOTAL');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO NK');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL NK');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KATEGORI');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KATEGORI BIAYA');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;

				$sheet->getStyle('A4:N4')->getFont()->setBold(true);


				$currrow++;
			
				foreach ($result->data as $key => $r) {

					$currcol = 1;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->no_transaksi);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, date_format(date_create($r->Tanggal),'d/m/Y'));
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('dd/mm/yyyy');

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->NoFP);
					$sheet->getStyle($currcol, $currrow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->no_tagihan);
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($r->NPWP));
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($r->Nm_Supl));

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($r->NamaJasaKenaPajak));

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->DPP);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->PPn);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->Total);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->NoNK);
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, date_format(date_create($r->TglNK),'d/m/Y'));
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('dd/mm/yyyy');

					$currcol++;

					if($r->statusdikreditkan==0){
						$kategori = 'Dikreditkan';
					}else{
						$kategori = 'Tidak Dikreditkan';
					}

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $kategori);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->Kategori);

					$currrow++;
				}

			}else if($dld=='import_pajak'){		

				$attachmentDirectory = FCPATH . 'attachment/';

				$filePath = $attachmentDirectory.md5($_SESSION['logged_in']['userid']).'.csv';

				if (file_exists($filePath)) {

					unlink($filePath);
				}

				file_put_contents($filePath,'');


				$data = array(
				    array('FM', 'KD_JENIS_TRANSAKSI', 'FG_PENGGANTI', 'NOMOR_FAKTUR', 'MASA_PAJAK', 'TAHUN_PAJAK', 'TANGGAL_FAKTUR', 'NPWP', 'NAMA', 'ALAMAT_LENGKAP', 'JUMLAH_DPP', 'JUMLAH_PPN', 'JUMLAH_PPNBM', 'IS_CREDITABLE')
				);

				$csvFilename = $filePath;
				$csvFile = fopen($csvFilename, 'w');

				foreach ($data as $row) {
				    fputcsv($csvFile, $row);
				}

				$datatest2 = array();
				
				foreach ($result->data as $key => $r) {
					$datatest = '';
					$datatest[] = 'FM';
					$datatest[] = substr($r->NoFP, 0, 2);
					$datatest[] = substr($r->NoFP, 2, 1);

					$part1 = substr($r->NoFP, 4, 3);
					$part2 = substr($r->NoFP, 8, 2);
					$part3 = substr($r->NoFP, 11, 8);

					$finalResult = $part1 . $part2 . $part3;

					$datatest[] = $r->NoFP;

					$datatest[] = $r->BulanMasaPajak;
					$datatest[] = $r->TahunMasaPajak;
					$datatest[] = date_format(date_create($r->Tanggal),'d/m/Y');

					$part1 = substr($r->NPWP, 0, 2);
					$part2 = substr($r->NPWP, 3, 3);
					$part3 = substr($r->NPWP, 7, 3);
					$part4 = substr($r->NPWP, 11, 1);
					$part5 = substr($r->NPWP, 13, 3);
					$part6 = substr($r->NPWP, 17, 3);

					$result = $part1 . $part2 . $part3 . $part4 . $part5 . $part6;

					$datatest[] = $r->NPWP;

					$string = trim($r->Nm_Supl);
					$result = substr($string, 0, 50);

					$datatest[] = $result;

					$string = trim($r->Alm_Supl);
					$string = str_replace(chr(13), ' ', $string);
					$string = str_replace(chr(10), ' ', $string);
					$string = str_replace(chr(9), ' ', $string);
					$datatest[] = $string;

					$datatest[] = $r->DPP;
					$datatest[] =  $r->PPn;
					$datatest[] = '0';
					$datatest[] = '1';

					$datatest2[]=$datatest;
				}

				foreach ($datatest2 as $row) {
				    fputcsv($csvFile, $row);
				}

				$currrow=2;
				for($x=0; $x<count($datatest2); $x++){
					$sheet->getStyleByColumnAndRow(4, $currrow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
					$currrow++;
				}

				fclose($csvFile);

				header('Content-Type: application/csv');
		        header('Content-Disposition: attachment; filename="import pajak masukan efaktur.csv"');
		        readfile($filePath);

				if (file_exists($filePath)) {
					unlink($filePath);
				}

			}else if($dld=='unifikasi'){

				$filename = 'rekap eunifikasi.xlsx';
				$title = 'rekap eunifikasi';
				$sheet->setTitle($title);

				$sheet->setTitle($filename);
				$sheet->setCellValue('A1', 'REKAP eUNIFIKASI');
				
				$sheet->getStyle('A1')->getFont()->setSize(13);
				$sheet->setCellValue('A2', 'MASA PAJAK : '.$namabulan[$post['bulan']].' '.$post['tahun']);


				$currcol = 1;
				$currrow = 4;
						
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, ' NO URUT');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO INPUT');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');

				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'MASA');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;

				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL  INPUT');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO TAGIHAN');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TGL TAGIHAN');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NPWP');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NIK');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA PKP');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DPP YANG DIPOTONG');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'TARIF PPH');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PPH 23/26');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;

				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PPH 4 AYAT 2');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PPH 15');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO SUKET');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NO SKB');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KODE OBJEK PAJAK');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;

				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'CABANG');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currrow++;

				$sheet->getStyle('A4:Q4')->getFont()->setBold(true);

				$no=1;
				foreach ($result->data as $key => $r) {
					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->no_transaksi);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->bulanpotongpph.'-'.$r->tahunpotongpph);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, date_format(date_create($r->TglInput),'d/m/Y'));
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('dd/mm/yyyy');

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->no_tagihan);
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, date_format(date_create($r->tgl_tagihan),'d/m/Y'));
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode('dd/mm/yyyy');

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($r->NPWP));
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, "000000000000000");
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($r->Nm_Supl));

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->DPPPemotongan);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->TarifPPH.'%');

					$currcol++;

					if($r->isPPH23==true){
						$hasil = $r->DPPPemotongan*$r->TarifPPH/100;
						$suket = $r->no_skb;
					}else{
						$hasil = '';
						$suket = '';
					}

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $hasil);

					if($r->isPPH4==true){
						$hasil 	= $r->DPPPemotongan*$r->TarifPPH/100;
						$skb 	= $r->no_skb;
					}else{
						$hasil 	= '';
						$skb 	= '';
					}

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $hasil);

					if($r->isPPH15==true){
						$hasil = $r->DPPPemotongan*$r->TarifPPH/100;
					}else{
						$hasil = '';
					}

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $hasil);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $suket);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $skb);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->kode_objek_pajak);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->cabang);

					$currcol++;

					$currrow++;

					$no++;
				}

			}else if($dld=='pph21'){
				
				$filename = 'REKAP PPh 21.xlsx';
				$title = 'REKAP PPh 21';
				$sheet->setTitle($title);

				$sheet->setTitle($filename);
				$sheet->setCellValue('A1', 'REKAP PPH 21');
				
				$sheet->getStyle('A1')->getFont()->setSize(13);
				$sheet->setCellValue('A2', 'MASA PAJAK : '.$namabulan[$post['bulan']].' '.$post['tahun']);


				$currcol = 1;
				$currrow = 4;
						
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, ' No Urut');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tanggal Input');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;

				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No  Input');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Masa Pajak');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tahun Pajak');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Pembetulan');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nomor Bukti Potong');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NPWP');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NIK');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Alamat');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;

				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'WP Luar Negeri');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Negara');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Pajak');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Jumlah Bruto');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;
				
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'DPP Amount');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currcol++;

				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tanpa NPWP');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');

				$currcol++;

				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tarif');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');

				$currcol++;

				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Jumlah PPh');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');

				$currcol++;

				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NPWP Pemotong');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');

				$currcol++;

				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama Pemotong');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');

				$currcol++;

				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tanggal Bukti Potong');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');

				$currcol++;

				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Referensi');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');

				$currcol++;

				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Cabang');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFill()->getStartColor()->setARGB('eaeaea');
				
				$currrow++;

				$sheet->getStyle('A4:X4')->getFont()->setBold(true);


				$no=1;
				foreach ($result->data as $key => $r) {
					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $no);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, date_format(date_create($r->tgl_transaksi),'d/m/Y'));

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->no_transaksi);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->BulanMasaPajak);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->TahunMasaPajak);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, '0');
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($r->NPWP));
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, '000000000000000');
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($r->Nm_Supl));

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, rtrim($r->Alm_Supl));

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'N');

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->kode_objek_pajak);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->DPPPemotongan);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');

					$currcol++;

					if(empty($r->NPWP) || $r->NPWP=='0' || $r->NPWP==null){
						$tpnpwp = 'Y';
					}else{
						$tpnpwp = 'N';
					}

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $tpnpwp);

					$currcol++;

					$hasilpersen = $r->JumlahPotong/$r->DPPPemotongan*100;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $hasilpersen.'%');

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->JumlahPotong);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $this->NPWPBHAKTI);
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'PT. BHAKTI IDOLA TAMA');

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->no_tagihan);
					$sheet->getStyleByColumnAndRow($currcol, $currrow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

					$currcol++;

					$sheet->setCellValueByColumnAndRow($currcol, $currrow, $r->cabang);

					$currrow++;

					$no++;
				}
			}	

			if($dld=='buku_ppn' || $dld=='unifikasi' || $dld=='pph21'){

					for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
					    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
					}

				$writer = new Xlsx($spreadsheet);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename='.$filename); 
				header('Cache-Control: max-age=0');
				ob_end_clean();
				$writer->save('php://output');
			}

		}else{
			echo $result->error;
		}
		die;
	}

	function delete(){
		$url = $this->API_URL."/TagihanPajakMasukan/deletetagihan";
		$post['api'] = 'APITES';
		$post['number'] = $this->input->post('number');
		$post['keterangan'] = $this->input->post('keterangan');
		$post['username'] = $_SESSION['logged_in']['username'];

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);

		$data = json_decode($response);
		print_r($data->result);
	}

}

?>