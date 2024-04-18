<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ShopboardApproval extends MY_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('shopboardmodel');
		// $this->load->helper('FormLibrary');
		// $this->load->library("email");
		// $this->load->helper('url');
		// $this->load->library('excel');
	}

	public function index()
	{
		$data = [];
		$this->RenderView('shopboardkajulview',$data);
	}
	
	public function datatable_shopboard_approval()
	{
		$param = $_GET;
		$res = $this->shopboardmodel->datatable_shopboard_approval($param);
		echo $res;
	}

	public function approval()
	{
		$res = $this->shopboardmodel->pengajuan_detail($_POST);
		
		// SUSUN DATA PER SUPPLIER
		$email = array();
		foreach($res as $i=>$row){
			$email[$row['supplier']][] = $row;
		}
		// echo json_encode($email);die;
		
		$error = 0;
		$msg = '';
		foreach($email as $supplier=>$row){
			$subject = 'Perpanjangan Shopboard '.$_SESSION['logged_in']['branch_id'].' ['.$_POST['act'].']';
			$html = $this->create_email_approval($row,$_POST);
			
			$data = [];
			$data['to'] = $_SESSION['logged_in']['useremail'];
			$data['cc'] = '"'.$row[0]['emailed_by'].'","it.maintenance@bhakti.co.id"';
			
			// debug
			// $data['to'] = 'tjambuiliat@gmail.com';
			// $data['cc'] = '"it.aliat@jasakom.com","it.maintenance@bhakti.co.id"';
			
			$data['subject'] = $subject;
			$data['message'] = $html;
			$result = $this->send_email($data);
			if(json_decode($result)=='SUCCESS'){
				$res = $this->shopboardmodel->approval($_POST);
				if($res=='success'){
				}
				else{
					$error = 1;
					$msg.=$res.'\n';
				}
			}
			else{
				$error = 1;
				$msg.='Email gagal dikirim ke '.$data['to'].'\n';
			}
		}
		if($error==0){
			echo json_encode(array('result'=>'success','msg'=>'Email sukses dikirim ke '.$_SESSION['logged_in']['useremail']));
		}
		else{
			echo json_encode(array('result'=>'failed','msg'=>$msg));
		}
	}
	
	
	public function create_email_approval($data, $post)
	{
		$style="font-family:inherit; border:1px solid #2C458F; padding:5px 10px;background:#2C458F; color:#fff";
		$html="<h2>Perpanjangan Shopboard ".$_SESSION['logged_in']['branch_id']." - ".$data[0]['supplier']."</h2>";
		$html.="Status Approval: ".$post['act']."<br>";
		if($post['rejected_note']!=''){
			$html.="Rejected Note: ".$post['rejected_note']."<br>";
		}
		$html.="Di".strtolower($post['act'])." Oleh: ".$_SESSION['logged_in']['username']." (".$_SESSION['logged_in']['useremail'].")<br>";
		$html.="<table style='width:100%; border-collapse:collapse'>";
		$html.="<tr><th style='".$style."'>No</th><th style='".$style."'>Nama Toko</th><th style='".$style."'>Ukuran<br>Shopboard</th><th style='".$style."'>Tgl. Expired</th></tr>";
		
		$style="font-family:inherit; border:1px solid #2C458F; padding:5px 10px";
		$no = 0;
		foreach($data as $row){
			$no++;
			$ukuran = $row['merk1'].": ".$row['ukuran1']."<br>";
			$ukuran .= ($row['merk2']!='') ? $row['merk2'].": ".$row['ukuran2']."<br>" : "";
			$ukuran .= ($row['merk3']!='') ? $row['merk3'].": ".$row['ukuran3']."<br>" : "";
			$ukuran .= ($row['merk4']!='') ? $row['merk4'].": ".$row['ukuran4']."<br>" : "";
			$ukuran .= ($row['merk5']!='') ? $row['merk5'].": ".$row['ukuran5']."<br>" : "";
			
			$html.="<tr>";
			$html.="<td style='".$style."'>".$no."</td>";
			$html.="<td style='".$style."'>Nama Toko: <b>".$row['nama_toko']."</b><br>Alamat: <b>".$row['alamat']."</b><br>Kota: <b>".$row['kota']."</b><br>Supplier: <b>".$row['supplier']."</b></td>";
			$html.="<td style='".$style."'>".$ukuran."</td>";
			$html.="<td style='".$style."'>".date('d-M-Y', strtotime($row['periode_end']))."</td>";
			$html.="</tr>";
		}
		$html.="</table>";
		$html.="<em>Ini adalah email otomatis. Mohon untuk tidak membalas email ini.</em>";
		
		return $html;
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
}
