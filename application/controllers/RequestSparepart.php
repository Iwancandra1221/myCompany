<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class RequestSparepart extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model('RequestSparepartModel');
		$this->load->model('GzipDecodeModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	function index(){
		$checkAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		//if($_SESSION["can_read"]==true){
			$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
			$data['role'] = $this->RequestSparepartModel->role();
			$this->RenderView('RequestSparepartView',$data);
		//}
	}

	function getNoRequest()
	{

		//$url = $this->API_URL. "/RequestSparepart/getNoRequest?api=APITES";
		$url = "http://localhost:90/webAPI/RequestSparepart/getNoRequest?api=APITES";
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

	function getMerk()
	{
	
		//$url = $this->API_URL. "/RequestSparepart/getMerk?api=APITES";
		$url = "http://localhost:90/webAPI/RequestSparepart/getMerk?api=APITES";
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

	function getLokasi()
	{
		//$url = $this->API_URL. "/RequestSparepart/getLokasi?api=APITES";
		$url = "http://localhost:90/webAPI/RequestSparepart/getLokasi?api=APITES";
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

	function getDealer()
	{
		$post['api'] = 'APITES';
		$post['get'] = $this->input->get();
	
		//$url = $this->API_URL. "/RequestSparepart/getDealer";
		$url = "http://localhost:90/webAPI/RequestSparepart/getDealer";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);

		if ($httpcode != 200) {
			echo 'URL "'.$url.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode; die;
		} else {
			//$result = json_decode($response);
			$result = $this->GzipDecodeModel->_decodeGzip($response);
			if($result->result =='sukses'){
				$data['list'] = $result->data;
				$data_list=array();

				if(!empty($data['list'])){
					$listdata=json_decode(json_encode($data['list']->data));
					foreach($listdata as $key => $d) {

						$onclick = "'".str_replace('=','',base64_encode($d->Kode_Pelanggan))."','".str_replace('=','',base64_encode($d->Nama_Pelanggan))."','".str_replace('=','',base64_encode($d->Alamat_Pelanggan))."'";
						//$onclick = "'".$d->Kode_Pelanggan."','".$d->Nama_Pelanggan."','".$d->Alamat_Pelanggan."'";

						$tamp=array();
						$tamp[]='<span onclick="selectDealer('.$onclick.')">'.$d->Kode_Pelanggan.'</span>';
						$tamp[]='<span onclick="selectDealer('.$onclick.')">'.$d->Nama_Pelanggan.'</span>';
						$tamp[]='<span onclick="selectDealer('.$onclick.')">'.$d->Alamat_Pelanggan.'</span>';

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
				echo $result->error; die;
			}
		}
	}

	function getDetailDealerKhusus()
	{
		if (!empty($this->input->post('kd_dealer'))) {
			$kd_dealer = $this->input->post('kd_dealer');
		}
		//$url = $this->API_URL."/RequestSparepart/getDetailDealerKhusus?api=APITES&kd_dealer=" . $kd_dealer;
		$url = "http://localhost:90/webAPI/RequestSparepart/getDetailDealerKhusus?api=APITES&kd_dealer=". $kd_dealer;

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

	function getDetailDealer()
	{
		if (!empty($this->input->post('kd_dealer'))) {
			$kd_dealer = $this->input->post('kd_dealer');
		}
		//$url = $this->API_URL."/RequestSparepart/getDetailDealer?api=APITES&kd_dealer=" . $kd_dealer;
		$url = "http://localhost:90/webAPI/RequestSparepart/getDetailDealer?api=APITES&kd_dealer=" . $kd_dealer;

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

	function getNotaService()
	{

		$post['api'] = 'APITES';
		$post['get'] = $this->input->get();

		//$url = $this->API_URL. "/RequestSparepart/getNotaService?api=APITES";
		$url = "http://localhost:90/webAPI/RequestSparepart/getNotaService?api=APITES";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		if ($httpcode != 200) {
			echo 'URL "'.$url.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode; die;
		} else {
			$result = $this->GzipDecodeModel->_decodeGzip($response);
			if($result->result =='sukses'){
				$data['list'] = $result->data;
				$data_list=array();	

				if(!empty($data['list'])){
					$listdata=json_decode(json_encode($data['list']->data));
					foreach($listdata as $key => $d) {
						$tanggal = date_format(date_create($d->Tgl_Service),'d-m-Y');
						$onclick = "'".str_replace('=','',base64_encode($d->No_Service))."','".str_replace('=','',base64_encode($tanggal))."','".str_replace('=','',base64_encode($d->Nama_Pelanggan)). "','" . str_replace('=', '', base64_encode($d->Kode_Barang)) . "','" . str_replace('=', '', base64_encode($d->Merk)) . "'";
						
						$tamp=array();
						$tamp[]='<span onclick="selectNota('.$onclick.')">'.$d->No_Service.'</span>';
						$tamp[]='<span onclick="selectNota('.$onclick.')">'.$d->Nama_Pelanggan.'</span>';
						$tamp[]='<span onclick="selectNota('.$onclick.')">'.$tanggal.'</span>';
						$tamp[]='<span onclick="selectNota('.$onclick.')">'.$d->Kode_Barang.'</span>';
						$tamp[]='<span onclick="selectNota('.$onclick.')">'.$d->Merk.'</span>';

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
				echo $result->error; die;
			}
		}
	}

	function getRequestSP()
	{
		$post['api'] = 'APITES';
		$post['get'] = $this->input->get();

		//$url = $this->API_URL. "/RequestSparepart/getHeaderRequestSP?api=APITES";
		$url = "http://localhost:90/webAPI/RequestSparepart/getHeaderRequestSP?api=APITES";
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 6000,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($post),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));

		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		if ($httpcode != 200) {
			echo 'URL "'.$url.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode; die;
		} else {
			$result = $this->GzipDecodeModel->_decodeGzip($response);
			if($result->result =='sukses'){
				$data['list'] = $result->data;
				$data_list=array();

				if(!empty($data['list'])){
					$listdata=json_decode(json_encode($data['list']->data));
					foreach($listdata as $key => $d) {
						$tanggal = date_format(date_create($d->Tgl_Request),'d-m-Y');
						$onclick = "'".str_replace('=','',base64_encode($tanggal))."','".str_replace('=','',base64_encode($d->Kd_Request))."',
						'".str_replace('=','',base64_encode($d->Proses)). "','" . str_replace('=', '', base64_encode($d->Keterangan)) . "',
						'" . str_replace('=', '', base64_encode($d->No_Svc)) . "','" . str_replace('=', '', base64_encode($d->Kd_Gudang)) . "',
						'" . str_replace('=', '', base64_encode($d->Kd_Plg)) . "','" . str_replace('=', '', base64_encode($d->Kd_Wil)) . "'";
						
						$tamp=array();
						$tamp[]='<span onclick="selectRequest('.$onclick.')">'.$tanggal.'</span>';
						$tamp[]='<span onclick="selectRequest('.$onclick.')">'.$d->Kd_Request.'</span>';
						$tamp[]='<span onclick="selectRequest('.$onclick.')">'.$d->Proses.'</span>';
						$tamp[]='<span onclick="selectRequest('.$onclick.')">'.$d->Keterangan.'</span>';
						$tamp[]='<span onclick="selectRequest('.$onclick.')">'.$d->No_Svc.'</span>';
						$tamp[]='<span onclick="selectRequest('.$onclick.')">'.$d->Kd_Gudang.'</span>';
						$tamp[]='<span onclick="selectRequest('.$onclick.')">'.$d->Kd_Plg.'</span>';
						$tamp[]='<span onclick="selectRequest('.$onclick.')">'.$d->Kd_Wil.'</span>';
						
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
				echo $result->error; die;
			}
		}
	}

	function getDetailSP()
	{
		if(!empty($this->input->post('kd_request'))){
			$kdRequest = $this->input->post('kd_request');
		}else{
			$kdRequest = '';
		}
		//$url = $this->API_URL. "/RequestSparepart/getDetailRequestSP?api=APITES&kd_request=".$kdRequest;
		$url = "http://localhost:90/webAPI/RequestSparepart/getDetailRequestSP?api=APITES&kd_request=".$kdRequest;

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

	function getMappingGudang($mapping='',$action='')
	{

		$post['api'] = 'APITES';
		$post['get'] = $this->input->get();
		$post['mapping_type'] = $mapping;
		$post['username'] = $_SESSION["logged_in"]["username"];
		$post['action'] = $action;
	
		//$url = $this->API_URL. "/webAPI/RequestSparepart/getMappingGudang";
		$url = "http://localhost:90/webAPI/RequestSparepart/getMappingGudang";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);

		if ($httpcode != 200) {
			echo 'URL "'.$url.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode; die;
		} else {
			$result = $this->GzipDecodeModel->_decodeGzip($response);
			if($result->result =='sukses'){
				$data['list'] = $result->data;
				$data_list=array();

				if(!empty($data['list'])){
					$listdata=json_decode(json_encode($data['list']->data));
					foreach($listdata as $key => $d) {

						$onclick = "'".str_replace('=','',base64_encode($d->Mapping_Name))."','".$action."'";

						$tamp=array();
						$tamp[]='<span onclick="selectMapping('.$onclick.')">'.$d->Mapping_Name.'</span>';
						$tamp[]='<span onclick="selectMapping('.$onclick.')">'.$action.'</span>';

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
				echo $result->error; die;
			}
		}
	}

	function getSparepart()
	{

		$post['api'] = 'APITES';
		$post['merk'] = $this->input->post('merk');
		$post['gudang'] = $this->input->post('gudang');
		$post['kdSparepart'] = $this->input->post('kdSparepart');
		//$url = $this->API_URL. "/RequestSparepart/getSparepart";
		$url = "http://localhost:90/webAPI/RequestSparepart/getSparepart";
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 6000,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($post),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));

		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		$result = json_decode($response, true);
		if ($result["result"]=="sukses") {
			echo json_encode($result["data"]);
		} else {
			echo json_encode(array('error'=>$result["error"]));
		}
	}

	function saveRequest()
	{

		$postx = $this->PopulatePost();
		//if (isset($postx['save'])) { 
			if (isset($postx['kodeSparepart'])) {
				for ($i = 0; $i < count($postx['kodeSparepart']); $i++) {
					$data[] = [
						"Kd_Request" => $postx['nomor_request'],
						"Kd_Sparepart" => $postx["kodeSparepart"][$i],
						"Qty" => $postx["qty"][$i],
						"No_BRP" => $postx["faktur"][$i],
					];
				}
			}

			$datax = array(
				"api" => "APITES",
				"Kd_Request" => $postx['nomor_request'],
				"Tgl_Request" => $postx['tanggal_transaksi'],
				"Keterangan" => $postx['keterangan'],
				"Kd_Plg" => $postx['kode_dealer'],
				"Status" => $postx['option'],
				"Kd_Wil" => isset($postx['alamat_dealer']),
				"No_Svc" => $postx['nota_service'],
				"Kd_Gudang" => substr($postx['gudang_out'], 0, 8),
				"Gudang_Target" => substr($postx['gudang_in'], 0, 8),
				"User_Name" => $_SESSION["logged_in"]["username"],
				"Kd_Lokasi" => substr($postx['nomor_request'], 3, 3),
				"Kd_Dest" => $postx['lokasi'],
				"Detail" => $data
			);
			//echo json_encode($datax);
			//$url = $this->API_URL. "/RequestSparepart/simpanRequest";
			$url = "http://localhost:90/webAPI/RequestSparepart/simpanRequest";
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 6000,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => json_encode($datax),
				CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));

			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			echo $response;
			die;
		//}
	}

}

?>