<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	class PreOrderPembelian extends CI_Controller 
	{
		public $cc = "";
		
		public function __construct()
		{
			parent::__construct();
			$this->load->library('email');	
			$this->load->model('PreOrderPembelianModel', 'ReportModel');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		}
		
		public function ApproveOld($no_prepo="") {
			$no_prepo=urldecode($this->input->get("no_prepo"));
			$confirmed_by=urldecode($this->input->get("confirmed_by"));
			$URL = $this->API_URL."/PreOrderPembelian/Approve?no_prepo=".urlencode($no_prepo)."&confirmed_by=".urlencode($confirmed_by);
			// die($URL);
			$result = json_decode(file_get_contents($URL));
			
			if ($result->pesan=='sukses') {
				$this->ReportModel->ApproveCabang($result->kd_lokasi,$no_prepo,$confirmed_by);
				echo "
				<div style='padding:10px;background:#d4edda;border:1px solid #c3e6cb'>
				<center><h2>PRE ORDER PEMBELIAN BERHASIL DIAPPROVE</h2></center>
				</div>";
			}
			else {
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><center><h2>".$result->pesan."</h2></center></div>";				
			}
		}
		
		public function Approve($no_prepo="") {
			$no_prepo=urldecode($this->input->get("no_prepo"));
			$confirmed_by=urldecode($this->input->get("confirmed_by"));
			$URL = $this->API_URL."/PreOrderPembelian/GetDetail?no_prepo=".urlencode($no_prepo);
			// die($URL);
			$prepo = json_decode(file_get_contents($URL));
			
			if(isset($prepo->header)){
				
				$header = json_decode($prepo->header);
				$details = json_decode($prepo->detail);
				
				$style = '<style>
				*{
					font-family:"Arial";
					font-size:14px;
				}
				.table{
					border-collapse:collapse;
					border:1px solid #ddd;
				}
				.table th, .table td{
					border:1px solid #ddd;
					text-align:center;
				}
				.table tr:hover {
					/*background:#f8f8f8;*/
				}
				.table tr:nth-child(even) {
					background:#f8f8f8;
				}
				</style>';
				
				$detail = '';
				$detail .= '<table>';
				$detail .= "<tr><td>No. PrePO </td><td>: <b>".$header->No_PrePO."</b></td></tr>";
				$detail .= "<tr><td>Tgl. PrePO</td><td>: <b>".$header->Tgl_PrePO."</b></td></tr>";
				$detail .= "<tr><td>Divisi</td><td>: <b>".$header->Divisi."</b></td></tr>";
				$detail .= "<tr><td>Nama Group Gudang</td><td>: <b>".$header->Nm_GroupGudang."</b></td></tr>";
				$detail .= "<tr><td>Wilayah</td><td>: <b>".$header->Wilayah."</b></td></tr>";
				$detail .= "<tr><td>Periode</td><td>: <b>".$header->Bulan."/".$header->Tahun." Periode ".$header->Periode."</b></td></tr>";
				$detail .= "<tr><td>Dibuat Oleh</td><td>: <b>".$header->User_Name." <em>".$header->Entry_Time."</em></b> </td></tr>";
				$detail .= "<tr><td>Status</td><td>: <b>".$header->Ket_Status."</em></b> </td></tr>";
				$detail .=  '</table>';
				
				$detail .= '<table class="table">';
				$detail .= "<tr>";
				$detail .= "<th>Kd Brg</th>";
				$detail .= "<th>Stock Saat Ini</th>";
				$detail .= "<th>Stock Dalam Perjalanan</th>";
				$detail .= "<th>Estimasi Sisa Stock</th>";
				$detail .= "<th>Indikator</th>";
				$detail .= "<th>Indikator Buffer Stock</th>";
				$detail .= "<th>PO Otomatis</th>";
				$detail .= "<th>Outstanding PO</th>";
				$detail .= "<th>PO RO</th>";
				$detail .= "<th>Average Per Periode (12 Periode Terakhir)</th>";
				$detail .= "<th>Sales Qty Periode Berjalan</th>";
				$detail .= "<th>Estimasi Jual di Periode Terakhir</th>";
				$detail .= "<th>Keterangan</th>";
				$detail .= "<th>Pilih<br>*</th>";
				$detail .= "</tr>";
				
				foreach($details as $d) {
					$detail .= "<tr>";
					$detail .= "<td>".$d->Kd_Brg. "</td>";
					$detail .= "<td>".number_format($d->R_StockSaatIni). "</td>";
					$detail .= "<td>".number_format($d->R_StockPerjalanan). "</td>";
					$detail .= "<td>".number_format($d->R_EstimasiSisaStock). "</td>";
					$detail .= "<td>".intval($d->R_Indikator)."</td>";
					$detail .= "<td>".$d->R_IndikatorNama. "</td>";
					$detail .= "<td>".number_format($d->R_PO_Otomatis). "</td>";
					$detail .= "<td>".number_format($d->R_OutstandingPO). "</td>";
					$detail .= "<td>".number_format($d->I_TotalBeli). "</td>";
					$detail .= "<td>".number_format($d->I_TotalJual). "</td>";
					$detail .= "<td>".number_format($d->R_SalesQty). "</td>";
					$detail .= "<td>".number_format($d->R_EstimasiJual). "</td>";
					$detail .= "<td>".$d->R_Keterangan. "</td>";
					$detail .= "<td><input type='checkbox' class='cek_pilih' name='kd_brg[]' value='".$d->Kd_Brg."' onchange='cek()' checked></td>";
					$detail .= "</tr>";
				}
				
				$detail .= '</table>';
				
				if ($header->Ket_Status=="WAITING FOR APPROVAL") {
					echo "
					<form action='./Approval' method='POST'>
					<div style='padding:10px;background:#FFF;border:1px solid #f5c6cb;'>".$style.$detail."
					<input type='hidden' name='no_prepo' value='".$no_prepo."'>
					<input type='hidden' name='confirmed_by' value='".$confirmed_by."'>
					<br>
					
					<span style='float:right'><em>(jika tidak pilih = REJECT)</em> Pilih Semua <input type='checkbox' id='cbx_all' onchange='cek_all()' checked></span>
					<br>
					REJECT NOTE (wajib diisi jika reject)<br>
					<input type='input' name='rejectnote' id='rejectnote' style='width:100%;padding:5px;margin-bottom:10px' disabled>
					<center><input type='submit' id='btn_submit' value='APPROVE' style='color:#fff; padding:10px; background:green; border:1px solid #555; text-align:center;' ></center>
					</div>
					</form>
					";
					
					$script= '
					<script type="text/javascript">
					function cek_all() {
						var source = document.getElementById("cbx_all");
						var inputElems = document.getElementsByClassName("cek_pilih");
						count = 0;
						for (var i=0; i<inputElems.length; i++) {
							inputElems[i].checked = source.checked;
							if (inputElems[i].checked == true){
								count++;
							}
						}
						if(count>0){
							document.getElementById("btn_submit").value = "APPROVE";
							document.getElementById("btn_submit").style.backgroundColor = "green";
							document.getElementById("rejectnote").required = false;
							document.getElementById("rejectnote").disabled = true;
						}
						else{
							document.getElementById("btn_submit").value = "REJECT";
							document.getElementById("btn_submit").style.backgroundColor = "red";
							document.getElementById("rejectnote").required = true;
							document.getElementById("rejectnote").disabled = false;
						}
					}
					function cek(){
						var inputElems = document.getElementsByClassName("cek_pilih");
						count = 0;
						for (var i=0; i<inputElems.length; i++) {
							if (inputElems[i].checked == true){
								count++;
							}
						}
						if(count>0){
							document.getElementById("btn_submit").value = "APPROVE";
							document.getElementById("btn_submit").style.backgroundColor = "green";
							document.getElementById("cbx_all").checked = true;
							document.getElementById("rejectnote").required = false;
							document.getElementById("rejectnote").disabled = true;
						}
						else{
							document.getElementById("btn_submit").value = "REJECT";
							document.getElementById("btn_submit").style.backgroundColor = "red";
							document.getElementById("cbx_all").checked = false;
							document.getElementById("rejectnote").required = true;
							document.getElementById("rejectnote").disabled = false;
						}
					}
					</script>
					';
					echo $script;
				}
				else if ($header->Ket_Status=="REJECTED") {
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>PRE PO SUDAH DIREJECT!</h2></div>";
				}
				else if ($header->Ket_Status=="CONFIRMED") {
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>PRE PO SUDAH DIAPPROVE!</h2></div>";
				}
				else if ($header->Ket_Status=="CANCELLED") {
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>PRE PO SUDAH DICANCEL!</h2></div>";	
				}
				else {
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>PRE PO GAGAL DIREJECT!</h2></div>";	
				}
			}else{ 
				echo "No. PrePO tidak ditemukan";
			}
		}
		
		public function Approval() {
			
			$no_prepo=urldecode($this->input->post("no_prepo"));
			$confirmed_by=urldecode($this->input->post("confirmed_by"));
			$rejectnote=urldecode($this->input->post("rejectnote"));
			$data = $this->PopulatePost();
			
			//APPROVE
			if(ISSET($data['kd_brg'])){
			
				// die('approve');
				
				$curl = curl_init();
				curl_setopt_array($curl, array(
				CURLOPT_URL => $this->API_URL."/PreOrderPembelian/Approve?no_prepo=".urlencode($no_prepo)."&confirmed_by=".urlencode($confirmed_by),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 60,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => http_build_query($data),
				));
				
				$result = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);
				
				// $x = array('pesan'=>'sukses', 'kd_lokasi'=>'PTK');//--------------debug
				// $result = json_encode($x); //--------------debug
				
				$result = json_decode($result);
				
				if ($result->pesan=='sukses') {
					
					$response = $this->ReportModel->ApproveCabang($result->kd_lokasi,$no_prepo,$confirmed_by, $data['kd_brg']);
					$result = json_decode($response);
					// print_r($result);
					// die;
					
					if($result=='SUCCESS'){
						echo "
						<div style='padding:10px;background:#d4edda;border:1px solid #c3e6cb'>
						<center><h2>PRE ORDER PEMBELIAN BERHASIL DIAPPROVE</h2></center>
						</div>";
					}
					else{
						echo "
						<div style='padding:10px;background:#d4edda;border:1px solid #c3e6cb'>
						<center><h2>PRE ORDER PEMBELIAN GAGAL DIAPPROVE</h2></center>
						</div>";
					}
				}
				else {
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><center><h2>".$result->pesan."</h2></center></div>";				
				}
				
			}
			
			//REJECT
			else{
				// die('reject');
				
				$URL = $this->API_URL."/PreOrderPembelian/Reject?no_prepo=".urlencode($no_prepo)."&rejectnote=".urlencode($rejectnote);
				$result = json_decode(file_get_contents($URL));
				if ($result->pesan=='sukses') {
					$this->ReportModel->RejectCabang($result->kd_lokasi, $no_prepo,$rejectnote);
					echo "<div style='padding:10px;background:#d4edda;border:1px solid #f5c6cb;'><h2><center>PRE ORDER PEMBELIAN BERHASIL DIREJECT</center></h2></div>";
				}
				else { 
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2><center>".$result->pesan."</center></h2></div>";
				}
			}
		}
		
		public function Reject($no_prepo="") {
		
			$no_prepo=urldecode($this->input->get("no_prepo"));
			$URL = $this->API_URL."/PreOrderPembelian/GetHeader?no_prepo=".urlencode($no_prepo);
			// die($URL);
			$header = json_decode(file_get_contents($URL));
			
			if(isset($header)){
				
				$log = '<table>';
				$log .= "<tr><td>No. PrePO </td><td>: <b>".$header->No_PrePO."</b></td></tr>";
				$log .= "<tr><td>Tgl. PrePO</td><td>: <b>".$header->Tgl_PrePO."</b></td></tr>";
				$log .= "<tr><td>Divisi</td><td>: <b>".$header->Divisi."</b></td></tr>";
				$log .= "<tr><td>Nama Group Gudang</td><td>: <b>".$header->Nm_GroupGudang."</b></td></tr>";
				$log .= "<tr><td>Wilayah</td><td>: <b>".$header->Wilayah."</b></td></tr>";
				$log .= "<tr><td>Periode</td><td>: <b>".$header->Bulan."/".$header->Tahun." Periode ".$header->Periode."</b></td></tr>";
				$log .= "<tr><td>Dibuat Oleh</td><td>: <b>".$header->User_Name." <em>".$header->Entry_Time."</em></b> </td></tr>";
				$log .= "<tr><td>Status</td><td>: <b>".$header->Ket_Status."</em></b> </td></tr>";
				$log .=  '</table>';
				
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'>".$log."</div>";
				
				if ($header->Ket_Status=="WAITING FOR APPROVAL") {
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'>
					<form action='./Rejected' method='POST'>
					<input type='hidden' name='no_prepo' value='".$no_prepo."'>
					<h2>Reject Note</h2>
					<input type='input' name='rejectnote' style='width:100%;padding:5px;margin-bottom:10px' required>
					<input type='submit' value='REJECT' style='color:#fff; padding:10px; background:#dc3545; border:1px solid #dc3545; text-align:center;'>
					</form>
					</div>";
				}
				else if ($header->Ket_Status=="REJECTED") {
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2>PRE PO SUDAH DIREJECT!</h2></div>";
				}
				else if ($header->Ket_Status=="CONFIRMED") {
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2>PRE PO SUDAH DIAPPROVE!</h2></div>";
				}
				else if ($header->Ket_Status=="CANCELLED") {
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2>PRE PO SUDAH DICANCEL!</h2></div>";	
				}
				else {
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2>PRE PO GAGAL DIREJECT!</h2></div>";	
				}
			}else{
				echo "No. PrePO tidak ditemukan";
			}
		}
		
		public function Rejected() {
			$no_prepo=urldecode($this->input->post("no_prepo"));
			$rejectnote=urldecode($this->input->post("rejectnote"));
			$URL = $this->API_URL."/PreOrderPembelian/Reject?no_prepo=".urlencode($no_prepo)."&rejectnote=".urlencode($rejectnote);
			$result = json_decode(file_get_contents($URL));
			if ($result->pesan=='sukses') {
				$this->ReportModel->RejectCabang($result->kd_lokasi, $no_prepo,$rejectnote);
				echo "<div style='padding:10px;background:#d4edda;border:1px solid #f5c6cb;'><h2><center>PRE ORDER PEMBELIAN BERHASIL DIREJECT</center></h2></div>";
			}
			else { 
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2><center>".$result->pesan."</center></h2></div>";
			}
		}
			
	}
?>	