<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

	class LaporanTagihan extends MY_Controller {

		function __construct()
		{
			parent::__construct();
			$this->load->model('LaporanTagihanModel');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
			$this->API_BKT = $_SESSION['conn']->AlamatWebService.$this->ConfigSysModel->Get()->bktapi_appname;
			$this->API_URL = 'http://localhost:90/webAPI';
			$this->APIKEY = 'APITES';
		}

		function index(){
			if(!empty($this->input->get())){
				$data['awal'] 			= $this->input->get('awal');
				$data['akhir'] 			= $this->input->get('akhir');
				$data['jenis_tagihan'] 	= $this->input->get('jenis_tagihan');
				$data['no_tagihan'] 	= $this->input->get('no_tagihan');
				$data['wilayah'] 		= $this->input->get('wilayah');
				$data['dealer'] 		= $this->input->get('dealer');
				$this->exportexcel($data);
			}else{
				$this->RenderView('LaporanTagihanView');
			}
		}



		function exportexcel($data){
			$data['API_BKT'] = $this->API_BKT;
			$data['APIKEY'] = $this->APIKEY;

			$header = $this->LaporanTagihanModel->getList($data);
			$header_BBT = $this->LaporanTagihanModel->getListbbt($data);

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
								

			$page_title = 'Laporan Perbandingan';
			$sheet->setTitle($page_title);
			$sheet->setCellValue('A1', 'Laporan Perbandingan Detail Pembayaran Tagihan');
			$sheet->getStyle('A1')->getFont()->setSize(15);
			$sheet->mergeCells('A1:M1');

			$sheet->setCellValue('A2', 'Periode : '.base64_decode($data['awal']).' s/d '.base64_decode($data['akhir']));
			$sheet->getStyle('A2')->getFont()->setSize(10);
			$sheet->mergeCells('A2:G2');

			$sheet->setCellValue('A3', 'Jenis Tagihan : '.base64_decode($data['jenis_tagihan']));
			$sheet->getStyle('A3')->getFont()->setSize(10);
			$sheet->mergeCells('A3:G3');

			$sheet->setCellValue('A4', 'Wilayah : '.base64_decode($data['wilayah']));
			$sheet->getStyle('A4')->getFont()->setSize(10);
			$sheet->mergeCells('A4:G4');

			$sheet->setCellValue('A5', 'Dealer : '.base64_decode($data['dealer']));
			$sheet->getStyle('A5')->getFont()->setSize(10);
			$sheet->mergeCells('A5:G5');

			$currcol = 1;
			$currrow = 7;


				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No');
				$sheet->getColumnDimension('A')->setWidth(3.5);
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nomor Tagihan');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Tanggal Jatuh Tempo');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Nama Dealer');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Kode Dealer');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No Penerimaan');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Penerimaan (Tagihan)');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Selisih Penerimaan');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'No BBT');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'BBT (Tagihan)');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
				$currcol++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Selisih BBT');
				$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);

				$sheet->getStyle('A7:M7')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('EAEAEA');
				
				$total_selisih_tagihan=0;
				$total_selisih_penerimaan=0;

			$currrow_penerimaan = 8; 
			if($header->result=='sukses'){

				$no=1;
				foreach ($header->data as $key => $d) {
					
					$detail1 = $this->LaporanTagihanModel->getListDetail(str_replace('=', '', base64_encode($d->No_Penerimaan)),$data);
					$selisih1=$d->Jumlah - $detail1->data;

					$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow_penerimaan, $no);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow_penerimaan, $d->No_Tagihan);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow_penerimaan, date_format(date_create($d->Tgl_jatuhTempo),'Y-m-d'));
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow_penerimaan, $d->nm_plg);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow_penerimaan, $d->Kd_plg);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow_penerimaan, $d->No_Penerimaan);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow_penerimaan, $d->Jumlah);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow_penerimaan, $detail1->data);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow_penerimaan, $selisih1);

					$total_selisih_penerimaan = $total_selisih_penerimaan+$selisih1;

					$no++;
					$currrow_penerimaan++; 
				}


			}else{
				$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow_penerimaan, 'Data Tidak Ada');
					$sheet->mergeCells('A8:I8');
			}

			$currrow_tagihan = 8; 
			if($header_BBT->result=='sukses'){

				$no=1;
				foreach ($header_BBT->data as $key => $d) {
					
					$detail2 = $this->LaporanTagihanModel->getListDetailBBT(str_replace('=', '', base64_encode($d->No_bukti)),$data);
					$selisih2=$d->Total - $detail2->data;

					if($currrow_tagihan>$currrow_penerimaan){
						$currcol = 1;
						$sheet->setCellValueByColumnAndRow($currcol, $currrow_penerimaan, $no);
					}

					$currcol = 10;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow_tagihan, $d->No_bukti);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow_tagihan, $d->Total);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow_tagihan, $detail2->data);
					$currcol++;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow_tagihan, $selisih2);

					$total_selisih_tagihan = $total_selisih_tagihan+$selisih2;

					$no++;
					$currrow_tagihan++; 
				}


			}else{
				$currcol = 1;
					$sheet->setCellValueByColumnAndRow($currcol, $currrow_tagihan, 'Data Tidak Ada');
					$sheet->mergeCells('J8:M8');
			}


			if($currrow_penerimaan>$currrow_tagihan){
				$currrow = $currrow_penerimaan;
			}else{
				$currrow = $currrow_tagihan;
			}

			$currcol = 8;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_selisih_penerimaan);
			$currcol=$currcol+3;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'Total');
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
			$currcol++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, $total_selisih_tagihan);
			$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);



			$sheet->getStyleByColumnAndRow($currcol, $currrow)->getFont()->setBold(true);



			$filename= $page_title.' ['.date('Ymd').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
			$writer->save('php://output');

		}



		function jenis_tagihan() {
		    $api_url = $this->API_BKT . "/JenisTagihan/listJenisTagihan";

		    $data = array(
		        'svr' => $_SESSION['conn']->Server,
		        'db' => $_SESSION['conn']->Database,
		        'api' => $this->APIKEY
		    );

		    $data = http_build_query($data);

		    $ch = curl_init();

		    curl_setopt($ch, CURLOPT_URL, $api_url);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		    $response = curl_exec($ch);
		    curl_close($ch);

		    if ($response === false) {
		        echo "Gagal mengambil data JSON: " . curl_error($ch);
		    } else {
		        $data = json_decode($response);
		        if ($data === null) {
		            echo "Gagal mengurai data JSON";
		        } else {
		            print_r($response);
		        }
		    }
		}

		function wilayah(){
			$api_url = $this->API_BKT . "/MasterWilayah/GetListWilayah";

		    $data = array(
		        // 'svr' => $_SESSION['conn']->Server,
		        'svr' => '10.1.48.200',
		        'db' => $_SESSION['conn']->Database,
		        'api' => $this->APIKEY,
		        'branch' => $_SESSION['conn']->BranchId
		    );

		    $data = http_build_query($data);

		    $ch = curl_init();

		    curl_setopt($ch, CURLOPT_URL, $api_url);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		    $response = curl_exec($ch);
		    curl_close($ch);

		    if ($response === false) {
		        echo "Gagal mengambil data JSON: " . curl_error($ch);
		    } else {
		        $data = json_decode($response);
		        if ($data === null) {
		            echo "Gagal mengurai data JSON";
		        } else {
		            print_r($response);
		        }
		    }
		}

		function dealer($kdwilayah,$nm_wilayah){
			if($kdwilayah!==''){
				$api_url = $this->API_BKT . "/MasterDealer/GetDealerByNmWil";

			    $data = array(
			        'kdwil' => $kdwilayah,
			        'nmwilayah' => $nm_wilayah,
			        // 'svr' => $_SESSION['conn']->Server,
			        'svr' => '10.1.48.200',
			        'db' => $_SESSION['conn']->Database,
			        'api' => $this->APIKEY,
			        'branch' => $_SESSION['conn']->BranchId
			    );

			    $data = http_build_query($data);

			    $ch = curl_init();

			    curl_setopt($ch, CURLOPT_URL, $api_url);
			    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			    curl_setopt($ch, CURLOPT_POST, 1);
			    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

			    $response = curl_exec($ch);
			    curl_close($ch);

			    if ($response === false) {
			        echo "Gagal mengambil data JSON: " . curl_error($ch);
			    } else {
			        $data = json_decode($response);
			        if ($data === null) {
			            echo "Gagal mengurai data JSON";
			        } else {
			            print_r($response);
			        }
			    }
			}
		}

	}
?>

