<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	class PreOrderPembelianApprovalV2 extends NS_Controller 
	{
		public $cc = "";
		public $approvaltype = 'REQUEST PORO';
		public $approvedbyfrommsconfig = false;
		public $expirydatefrommsconfig = false; 
		
		public function __construct()
		{
			parent::__construct();
			$this->load->library('email');	
			$this->load->model('PreOrderPembelianModel', 'ReportModel');
			$this->load->model('SalesManagerModel');
			$this->load->model('approvalmodel');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		}
		
		public function Approve($no_prepo="") {
			if(!empty($this->input->get("no_prepo"))){
				$no_prepo=urldecode($this->input->get("no_prepo"));
			}
			$confirmed_by=urldecode($this->input->get("confirmed_by"));
			$URL = $this->API_URL."/PreOrderPembelian/GetDetail?no_prepo=".urlencode($no_prepo);
			// die($URL);
			$prepo = json_decode(file_get_contents($URL));
			
			// print_r($prepo->header);
			// echo("<br>");
			if(isset($prepo->header)){
				
				$header = json_decode($prepo->header);
				$details = json_decode($prepo->detail);

				$style = '<style>
				*{
					font-family:Arial;
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
				$detail .= "<th>PO Otomatis</th>";
				$detail .= "<th>Outstanding PO</th>";
				$detail .= "<th>PO RO</th>";
				$detail .= "<th>Average Per Periode (12 Periode Terakhir)</th>";
				$detail .= "<th>Sales Qty Periode Berjalan</th>";
				$detail .= "<th>Estimasi Jual di Periode Terakhir</th>";
				$detail .= "<th>Estimasi Sisa Stock</th>";
				$detail .= "<th>Indikator</th>";
				$detail .= "<th>Indikator Buffer Stock</th>";
				$detail .= "<th>Keterangan</th>";
				if ($header->Ket_Status=="WAITING FOR APPROVAL") {
				$detail .= "<th>Pilih<br>*</th>";
				} else {
				$detail .= "<th>Status</th>";	
				}
				$detail .= "</tr>";
				
				foreach($details as $d) {
					$detail .= "<tr>";
					$detail .= "<td>".$d->Kd_Brg. "</td>";
					$detail .= "<td>".number_format($d->R_StockSaatIni). "</td>";
					$detail .= "<td>".number_format($d->R_StockPerjalanan). "</td>";
					$detail .= "<td>".number_format($d->R_PO_Otomatis). "</td>";
					$detail .= "<td>".number_format($d->R_OutstandingPO). "</td>";
					$detail .= "<td>".number_format($d->I_TotalBeli). "</td>";
					$detail .= "<td>".number_format($d->I_TotalJual). "</td>";
					$detail .= "<td>".number_format($d->R_SalesQty). "</td>";
					$detail .= "<td>".number_format($d->R_EstimasiJual). "</td>";
					$detail .= "<td>".number_format($d->R_EstimasiSisaStock). "</td>";
					$detail .= "<td>".number_format($d->R_Indikator, 2)."</td>";
					$detail .= "<td>".$d->R_IndikatorNama. "</td>";
					$detail .= "<td>".$d->R_Keterangan. "</td>";
					if ($header->Ket_Status=="WAITING FOR APPROVAL") {
					$detail .= "<td><input type='checkbox' class='cek_pilih' name='kd_brg[]' value='".$d->Kd_Brg."' onchange='cek()' checked></td>";
					} else if ($d->Status_Order=="CANCELLED") {
					$detail .= "<td>CANCELLED</td>";	
					} else if ($d->Status_Order=="CLOSED") {
					$detail .= "<td>CLOSED BY DIV.PURCHASING</td>";	
					} else if ($d->Status_Order=="REJECTED") {
					$detail .= "<td>REJECTED</td>";	
					} else if ($header->Ket_Status!="REJECTED") {
						if ($d->No_Order!="" && $d->No_Order!="-") {
							$detail .= "<td>".$d->No_Order."</td>";
						} else {
							$detail .= "<td>Menunggu Div.Purchasing</td>";
						}	
					} else {
						$detail .= "<td></td>";
					}
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
				else if($header->Ket_Status=="REJECTED") {
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>PRE PO SUDAH DIREJECT!</h2></div>";
					echo "Alasan Reject : <b>".$header->Reject_Note."</b><br>";
					echo $style."<br>";
					echo $detail;
					echo "<br>";
					echo "Diproses Oleh : <b>".$header->Confirmed_By."</b><br>";
					echo "Diproses Tgl : <b>".date("d-M-Y h:i:s", strtotime($header->Confirmed_Date))."</b>";
				}
				else if ($header->Ket_Status=="CONFIRMED") {
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>PRE PO SUDAH DIAPPROVE!</h2></div>";
					echo $style."<br>";
					echo $detail;
					echo "<br>";
					echo "Diproses Oleh : <b>".$header->Confirmed_By."</b><br>";
					echo "Diproses Tgl : <b>".date("d-M-Y h:i:s", strtotime($header->Confirmed_Date))."</b>";

				}
				else if ($header->Ket_Status=="FINISHED") {
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>PRE PO SUDAH DIPROSES DIV.PURCHASING!</h2></div>";
					echo $style."<br>";
					echo $detail;
					echo "<br>";
					echo "Diproses Oleh : <b>".$header->Confirmed_By."</b><br>";
					echo "Diproses Tgl : <b>".date("d-M-Y h:i:s", strtotime($header->Confirmed_Date))."</b>";

				}
				else if ($header->Ket_Status=="CANCELLED") {
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>PRE PO SUDAH DICANCEL OLEH DIV.PURCHASING!</h2></div>";	
					echo $style."<br>";
					echo $detail;
					echo "<br>";
					// echo "Diproses Oleh : <b>".$header->Confirmed_By."</b><br>";
					// echo "Diproses Tgl : <b>".date("d-M-Y h:i:s", strtotime($header->Confirmed_Date))."</b>";
				}
				else if ($header->Ket_Status=="CLOSED") {
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>PRE PO SUDAH DICLOSE OLEH DIV.PURCHASING!</h2></div>";	
					echo $style."<br>";
					echo $detail;
					echo "<br>";
					// echo "Diproses Oleh : <b>".$header->Confirmed_By."</b><br>";
					// echo "Diproses Tgl : <b>".date("d-M-Y h:i:s", strtotime($header->Confirmed_Date))."</b>";
				}
				else if ($header->Ket_Status=="WAITING FOR APPROVAL KACAB") {
					echo "<div style='width:100%; float:left; padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>PRE PO BELUM DIAPPROVE OLEH KACAB!</h2></div>";	
					echo $style."<br>";
					echo $detail;
					echo "<br>";
					// echo "Diproses Oleh : <b>".$header->Confirmed_By."</b><br>";
					// echo "Diproses Tgl : <b>".date("d-M-Y h:i:s", strtotime($header->Confirmed_Date))."</b>";
				}
		  
				else {
					//echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>PRE PO GAGAL DIREJECT!</h2></div>";	
				}
			}
			else echo "No. PrePO tidak ditemukan";
		}
		
		public function Approval() {
			
			$no_prepo=urldecode($this->input->post("no_prepo"));
			$confirmed_by=urldecode($this->input->post("confirmed_by"));
			$rejectnote=urldecode($this->input->post("rejectnote"));
			$data = $this->PopulatePost();
			//die(json_encode($data));

			//APPROVE
			if(ISSET($data['kd_brg'])){
			
				// die('approve');
				
				$curl = curl_init();
				curl_setopt_array($curl, array(
				CURLOPT_URL => $this->API_URL."/PreOrderPembelian/Approve?no_prepo=".urlencode($no_prepo)."&confirmed_by=".urlencode($confirmed_by),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 1000,
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
			if(!empty($this->input->get("no_prepo"))){
				$no_prepo=urldecode($this->input->get("no_prepo"));
			}
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
			}
			else echo "No. PrePO tidak ditemukan";
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
			
		public function ApproveNew() {

			$no_prepo=urldecode($this->input->get("no_prepo"));
			$acc_by=urldecode($this->input->get("acc_by"));
			$confirmed_by=urldecode($this->input->get("confirmed_by"));
			$URL = $this->API_URL."/PreOrderPembelian/GetDetail?no_prepo=".urlencode($no_prepo);
			$prepo = json_decode(file_get_contents($URL));

			$data = array();
			$data["content_html"] = "";
			$data["isForm"] = false;
			$data["formURL"] = "";
			$data["button_approve"] = "";
			$data["button_reject"] = "";

			$style="";
			$detail="";
			$log="";
			$script="";
			
			if(isset($prepo->header)){
				
				$header = json_decode($prepo->header);
				$details = json_decode($prepo->detail);

				$style = '<style>
				*{
					font-family:Arial;
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
				$detail .= "<tr><td>Status</td><td>: <b>".$header->Ket_Status."</em></b> </td></tr>";
				$detail .=  '</table>';
				
				
				$detail .= '<table class="table">';
				$detail .= "<tr>";
				$detail .= "<th width='10%'>Kd Brg</th>";
				$detail .= "<th width='6%'>Stock Saat Ini</th>";
				$detail .= "<th width='6%'>Stock Dalam Perjalanan</th>";
				$detail .= "<th width='6%'>PO Otomatis</th>";
				$detail .= "<th width='6%'>Outstanding PO</th>";
				$detail .= "<th width='6%'>Sesuatu</th>";
				$detail .= "<th width='6%'>PO RO</th>";
				$detail .= "<th width='6%'>Average Per Periode (12 Periode Terakhir)</th>";
				$detail .= "<th width='6%'>Sales Qty Periode Berjalan</th>";
				$detail .= "<th width='6%'>Estimasi Jual di Periode Terakhir</th>";
				$detail .= "<th width='6%'>Estimasi Sisa Stock</th>";
				$detail .= "<th width='6%'>Indikator</th>";
				$detail .= "<th width='6%'>Indikator Buffer Stock</th>";
				$detail .= "<th width='*'>Keterangan</th>";
				if ($header->Ket_Status=="WAITING FOR APPROVAL" || $header->Ket_Status=="WAITING FOR APPROVAL KACAB") {
				$detail .= "<th width='3%'>Pilih<br>*</th>";
				} else {
					$detail .= "<th width='3%'>Status</th>";
					$this->approvalmodel->close("REQUEST PORO", $no_prepo, $header->Ket_Status);
				}
				$detail .= "</tr>";
				
				foreach($details as $d) {
				
					// TIDAK TAMPILKAN DETAIL YG REJECTED JIKA POSISI APPROVAL
					if ($header->Ket_Status=="WAITING FOR APPROVAL" || $header->Ket_Status=="WAITING FOR APPROVAL KACAB") {
						if ($d->Status_Order=="REJECTED") {
							CONTINUE;
						}
					}
					
					$detail .= "<tr>";
					$detail .= "<td>".$d->Kd_Brg. "</td>";
					$detail .= "<td>".number_format($d->R_StockSaatIni). "</td>";
					$detail .= "<td>".number_format($d->R_StockPerjalanan). "</td>";
					$detail .= "<td>".number_format($d->R_PO_Otomatis). "</td>";
					$detail .= "<td>".number_format($d->R_OutstandingPO). "</td>";
					$detail .= "<td>".number_format($d->F_Campaign1). "</td>";
					$detail .= "<td>".number_format($d->I_TotalBeli). "</td>";
					$detail .= "<td>".number_format($d->R_Average). "</td>";
					$detail .= "<td>".number_format($d->R_SalesQty). "</td>";
					$detail .= "<td>".number_format($d->R_EstimasiJual). "</td>";
					$detail .= "<td>".number_format($d->R_EstimasiSisaStock). "</td>";
					$detail .= "<td>".number_format($d->R_Indikator, 2)."</td>";
					$detail .= "<td>".$d->R_IndikatorNama. "</td>";
					$detail .= "<td>".$d->R_Keterangan. "</td>";
					if ($header->Ket_Status=="WAITING FOR APPROVAL" || $header->Ket_Status=="WAITING FOR APPROVAL KACAB") {
						$detail .= "<td><input type='checkbox' class='cek_pilih' name='kd_brg[]' value='".$d->Kd_Brg."' onchange='cek()' checked></td>";
					} else if ($d->Status_Order=="CANCELLED") {
						$detail .= "<td>CANCELLED</td>";	
					} else if ($d->Status_Order=="CLOSED") {
						$detail .= "<td>CLOSED BY DIV.PURCHASING</td>";	
					} else if ($d->Status_Order=="REJECTED") {
						$detail .= "<td>REJECTED</td>";	
					} else if ($header->Ket_Status!="REJECTED") {
						if ($d->No_Order!="" && $d->No_Order!="-") {
							$detail .= "<td>".$d->No_Order."</td>";
						} else {
							$detail .= "<td>Menunggu Div.Purchasing</td>";
						}	
					} else {
						$detail .= "<td>".$header->Ket_Status."</td>";
					}
					$detail .= "</tr>";
				}
				
				$detail .= '</table>';


							  			
				if ($header->Ket_Status=="WAITING FOR APPROVAL" || $header->Ket_Status=="WAITING FOR APPROVAL KACAB" || 1==0) {
					
					$detail = "
					<form action='".site_url('PreOrderPembelianApprovalV2/ApprovalNewAndUpdateTblApprovalV2')."' method='POST'>
						<div style='padding:10px;background:#FFF;border:1px solid #f5c6cb;'>".$style.$detail."
							<input type='hidden' name='no_prepo' value='".trim($no_prepo)."'>
							<input type='hidden' name='acc_by' value='".trim($acc_by)."'>
							<input type='hidden' name='confirmed_by' value='".$confirmed_by."'>
							<input type='hidden' name='user_email' value='".trim($header->User_Email)."'>
							<input type='hidden' name='division' value='".trim($header->Divisi)."'>
							
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
					// echo $script;


					$Log = 'LOG<br>';
					$Log .= "Diajukan Oleh : <b>".$header->User_Name." (".$header->User_Email.") [".date("d M Y h:i:s", strtotime($header->Entry_Time))."]</b><br>";
					if ($header->Acc_By!="") {
						$Log.= 'Diapprove Kacab : <b>'.$header->Acc_By.' ['.date("d M Y h:i:s",strtotime($header->Acc_Date)).']</b><br>';		
					}
					if ($header->Confirmed_By!="") {
						$Log.= 'Diapprove BM : <b>'.$header->Confirmed_By.' ['.date("d M Y h:i:s",strtotime($header->Confirmed_Date)).']</b><br>';
					}
					// echo($Log);
					$data["content_html"] = $detail.$log.$script;
				}
				else if($header->Ket_Status=="REJECTED") {
					$data["content_html"] = "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>PRE PO SUDAH DIREJECT!</h2></div>";
					$data["content_html"].= "Alasan Reject : <b>".$header->Reject_Note."</b><br>";
					$data["content_html"].= $style."<br>";
					$data["content_html"].= $detail;
					$data["content_html"].= "<br>";
					$data["content_html"].= "Diproses Oleh : <b>".$header->Confirmed_By."</b><br>";
					$data["content_html"].= "Diproses Tgl : <b>".date("d-M-Y h:i:s", strtotime($header->Confirmed_Date))."</b>";
				}
				else if ($header->Ket_Status=="CONFIRMED") {
					$data["content_html"] = "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>PRE PO SUDAH DIAPPROVE!</h2></div>";
					$data["content_html"].= $style."<br>";
					$data["content_html"].= $detail;
					$data["content_html"].= "<br>";
					$data["content_html"].= "Diproses Oleh : <b>".$header->Confirmed_By."</b><br>";
					$data["content_html"].= "Diproses Tgl : <b>".date("d-M-Y h:i:s", strtotime($header->Confirmed_Date))."</b>";

				}
				else if ($header->Ket_Status=="FINISHED") {
					$data["content_html"].= "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>PRE PO SUDAH DIPROSES DIV.PURCHASING!</h2></div>";
					$data["content_html"].= $style."<br>";
					$data["content_html"].= $detail;
					$data["content_html"].= "<br>";
					$data["content_html"].= "Diproses Oleh : <b>".$header->Confirmed_By."</b><br>";
					$data["content_html"].= "Diproses Tgl : <b>".date("d-M-Y h:i:s", strtotime($header->Confirmed_Date))."</b>";

				}
				else if ($header->Ket_Status=="CANCELLED") {
					$data["content_html"].= "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>PRE PO SUDAH DICANCEL OLEH DIV.PURCHASING!</h2></div>";	
					$data["content_html"].= $style."<br>";
					$data["content_html"].= $detail;
					$data["content_html"].= "<br>";
				}
				else if ($header->Ket_Status=="CLOSED") {
					$data["content_html"].= "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>PRE PO SUDAH DICLOSE OLEH DIV.PURCHASING!</h2></div>";	
					$data["content_html"].= $style."<br>";
					$data["content_html"].= $detail;
					$data["content_html"].= "<br>";
				}
				else {
					//echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>PRE PO GAGAL DIREJECT!</h2></div>";	
				}
			} else {
				$data["content_html"].= "No. PrePO tidak ditemukan";
			}
			$this->RenderView("requestview", $data);

		}
		
		public function ApprovalNew() {
			
			$no_prepo=urldecode($this->input->post("no_prepo"));
			$acc_by=urldecode($this->input->post("acc_by"));
			$confirmed_by=urldecode($this->input->post("confirmed_by"));
			$user_email=urldecode($this->input->post("user_email"));
			$rejectnote=urldecode($this->input->post("rejectnote"));
			$division = urldecode($this->input->post("division"));
			$data = $this->PopulatePost();
			// die(json_encode($data));

			$email_bm = "";
			//APPROVE
			if(ISSET($data['kd_brg'])){

				// die('approve');
				if($acc_by!=''){
					// die(json_encode($data));
					// die($this->API_URL."/PreOrderPembelian/ApproveNew?no_prepo=".urlencode($no_prepo)."&acc_by=".urlencode($acc_by));
					// approval kacab
					$curl = curl_init();
					curl_setopt_array($curl, array(
					CURLOPT_URL => $this->API_URL."/PreOrderPembelian/ApproveNew?no_prepo=".urlencode($no_prepo)."&acc_by=".urlencode($acc_by),
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 1000,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => http_build_query($data),
					));
					
					$result = curl_exec($curl);
					$err = curl_error($curl);
					curl_close($curl);

					$bms = $this->SalesManagerModel->GetBrandManagersByDivisi($division);
					$email_bm = (($bms[0]->email_address=="")? $bms[0]->email : $bms[0]->email_address);
					$user_email = "";

				} else{
					// approval branch manager
					$curl = curl_init();
					curl_setopt_array($curl, array(
					CURLOPT_URL => $this->API_URL."/PreOrderPembelian/ConfirmNew?no_prepo=".urlencode($no_prepo)."&confirmed_by=".urlencode($confirmed_by)."&user_email=".urlencode($user_email),
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 1000,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => http_build_query($data),
					));
					
					$result = curl_exec($curl);
					$err = curl_error($curl);
					curl_close($curl);
				}
				
				
				$result = json_decode($result);
				// die(json_encode($result));
				
				if ($result->pesan=='sukses') {
					$url = $this->API_URL."/PreOrderPembelian/Email?no_prepo=".urlencode($no_prepo)."&bm=".urlencode($email_bm)."&user=".urlencode($user_email);
					$curl = curl_init();
					curl_setopt_array($curl, array(
					CURLOPT_URL => $url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 1000,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => http_build_query(array()),
					));
					
					$result = curl_exec($curl);
					$err = curl_error($curl);
					curl_close($curl);

					// $this->PreOrderPembelianModel->email($no_prepo,'',$email_brand_manager,''); // kirim email approval ke brand manager					
					
					echo "
					<div style='padding:10px;background:#d4edda;border:1px solid #c3e6cb'>
					<center><h2>PRE ORDER PEMBELIAN BERHASIL DIAPPROVE</h2></center>
					</div>";
				}
				else {
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><center><h2>".$result->pesan."</h2></center></div>";				
				}
				
			}
			//REJECT
			else{
				// die('reject');
				$URL = $this->API_URL."/PreOrderPembelian/RejectNew?no_prepo=".urlencode($no_prepo)."&rejectnote=".urlencode($rejectnote)."&user_email=".urlencode($user_email);
				$result = json_decode(file_get_contents($URL));
				if ($result->pesan=='sukses') {
					$url = $this->API_URL."/PreOrderPembelian/Email?no_prepo=".urlencode($no_prepo)."&bm=&user=".urlencode($user_email);
					$curl = curl_init();
					curl_setopt_array($curl, array(
					CURLOPT_URL => $url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 1000,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => http_build_query(array()),
					));
					
					$result = curl_exec($curl);
					$err = curl_error($curl);
					curl_close($curl);

					echo "<div style='padding:10px;background:#d4edda;border:1px solid #f5c6cb;'><h2><center>PRE ORDER PEMBELIAN BERHASIL DIREJECT</center></h2></div>";
				}
				else { 
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2><center>".$result->pesan."</center></h2></div>";
				}
			}
		}

		public function ApprovalNewAndUpdateTblApprovalV2() {
			// die("v2");
			$res = $this->ApprovalNewAndUpdateTblApproval();
			redirect("approvallist/view/".$res);
		}

		public function ApprovalNewAndUpdateTblApproval() {
			
			$data = $this->PopulatePost();
			$no_prepo=urldecode($this->input->post("no_prepo"));
			$acc_by=urldecode($this->input->post("acc_by"));
			$confirmed_by=urldecode($this->input->post("confirmed_by"));
			$user_email=urldecode($this->input->post("user_email"));
			$rejectnote=urldecode($this->input->post("rejectnote"));
			$division = urldecode($this->input->post("division"));
			// die(json_encode($data));

			// $approvedbyid = ""; 
			$approvedby = (($acc_by!="")? $acc_by : $confirmed_by);
			// // echo $approvedby;die;
			// $user = $this->UserModel->getUserDataByEmail($approvedby);
			// // echo json_encode($user);die;
			// if ($user != null) {
				// $approvedbyid = $user->USERID;
			// } else {
				// $user = $this->UserModel->Get($approvedby);
				// if ($user!=null) {
					// $approvedbyid = $user->USERID;
				// }
			// }


			$err='';
			$email_bm = "";
			//APPROVE
			if(ISSET($data['kd_brg'])){
				// die($acc_by);
				// die('approve');
				if($acc_by!=''){
					// die(json_encode($data));
					$url = $this->API_URL."/PreOrderPembelian/ApproveNew?no_prepo=".urlencode($no_prepo)."&acc_by=".urlencode($acc_by);
					// die($url);

					// approval kacab
					$curl = curl_init();
					curl_setopt_array($curl, array(
					CURLOPT_URL => $url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 1000,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => http_build_query($data),
					));
					
					$result = curl_exec($curl);
					$err = curl_error($curl);
					curl_close($curl);

					// die(json_encode($curl));


					$bms = $this->SalesManagerModel->GetBrandManagersByDivisi($division);
					$email_bm = (($bms[0]->email_address=="")? $bms[0]->email : $bms[0]->email_address);
					$user_email = "";

				} else{
					// approval branch manager
					$curl = curl_init();
					curl_setopt_array($curl, array(
					CURLOPT_URL => $this->API_URL."/PreOrderPembelian/ConfirmNew?no_prepo=".urlencode($no_prepo)."&confirmed_by=".urlencode($confirmed_by)."&user_email=".urlencode($user_email),
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 1000,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => http_build_query($data),
					));
					
					$result = curl_exec($curl);
					$err = curl_error($curl);
					curl_close($curl);
				}
				$result = json_decode($result);
				// die(json_encode($result));

				if ($result->pesan=='sukses'){

					// #1 Email dahulu supaya bisa insert 2 baris. karena jika update approve duluan, tidak bisa insert lg dgn RequestNo yg sama
					if($acc_by!=''){
						$url = $this->API_URL."/PreOrderPembelian/Email?no_prepo=".urlencode($no_prepo)."&bm=".urlencode($email_bm)."&user=".urlencode($user_email);
						$curl = curl_init();
						curl_setopt_array($curl, array(
						CURLOPT_URL => $url,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_TIMEOUT => 1000,
						CURLOPT_POST => 1,
						CURLOPT_POSTFIELDS => http_build_query(array()),
						));
						
						$result = curl_exec($curl);
						$err = curl_error($curl);
						curl_close($curl);
					}
					// $this->PreOrderPembelianModel->email($no_prepo,'',$email_brand_manager,''); // kirim email approval ke brand manager
					
					// #2 Update Approve
					$params = array();
					$params['ApprovalType'] = $this->approvaltype;
					$params['RequestNo'] = $no_prepo;
					$params['ApprovedBy'] = $approvedby;
					$params['ApprovalNote'] = '';
					if($acc_by!=''){
						$params['Priority'] = 1;
					} else {
						$params['Priority'] = 2;
					}
					$this->approvalmodel->doaction('approve',$params);


					$this->approvalmodel->updateisemailednextpriority($params);
					
					echo "
					<div style='padding:10px;background:#d4edda;border:1px solid #c3e6cb'>
					<center><h2>PRE ORDER PEMBELIAN BERHASIL DIAPPROVE</h2></center>
					</div>";
					return '5'; // approve
				}
				else {
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><center><h2>".$result->pesan."</h2></center></div>";		
					return '7'; // something wrong
				}
				
			}
			//REJECT
			else{
				$result ='';
				// die('reject');
				$URL = $this->API_URL."/PreOrderPembelian/RejectNew?no_prepo=".urlencode($no_prepo)."&rejectnote=".urlencode($rejectnote)."&user_email=".urlencode($user_email);
				$result = json_decode(file_get_contents($URL));
				if ($result->pesan=='sukses') {

					//Update TblApproval, From Here

					$params = array();
					$params['ApprovalType'] = $this->approvaltype;
					$params['RequestNo'] = $no_prepo;
					$params['ApprovedBy'] = $approvedby;
					$params['ApprovalNote'] = urlencode($rejectnote);
					$this->approvalmodel->doaction('reject',$params);

					//Update TblApproval, Until Here

					$url = $this->API_URL."/PreOrderPembelian/Email?no_prepo=".urlencode($no_prepo)."&bm=&user=".urlencode($user_email);
					$curl = curl_init();
					curl_setopt_array($curl, array(
					CURLOPT_URL => $url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 1000,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => http_build_query(array()),
					));
					
					$result = curl_exec($curl);
					$err = curl_error($curl);
					curl_close($curl);

					echo "<div style='padding:10px;background:#d4edda;border:1px solid #f5c6cb;'><h2><center>PRE ORDER PEMBELIAN BERHASIL DIREJECT</center></h2></div>";
					return '6'; // reject
				}
				else { 
					echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2><center>".$result->pesan."</center></h2></div>";		
					return '7'; // something wrong
				}
			}
		}

		public function InsertToTblApproval()
		{   
			// $data = '{      
			// 			"RequestNo": "PPOP/DMI/2306/072" ,      
			// 			"RequestBy": "1960" ,       
			// 			"RequestByName": "INDAH",      
			// 			"RequestByEmail": "indah@bhakti.co.id" ,      
			// 			"ApprovedBy": "2850" ,      
			// 			"ApprovedByName": "ALIAT" ,      
			// 			"ApprovedByEmail": "tjambuiliat@gmail.com" ,      
			// 			"AddInfo2Value": "RINNAI",      
			// 			"AddInfo6Value": "JUN 2023 PERIODE 2" ,      
			// 			"AddInfo9Value": "JAKARTA"      
			// 		}'; 
		    $data = file_get_contents('php://input');
		    $trx = json_decode($data);


			$EmailDate = date("Y-m-d H:i:s"); 
			$ExpiryDate = date('Y-m-d H:i:s', strtotime("+15 days", strtotime($EmailDate)));

			$division = $trx->AddInfo2Value;
			$bms = $this->SalesManagerModel->GetBrandManagersByDivisi($division);
			if (count($bms)==0) {
				echo "Brand Manager untuk Divisi ".$division." Belum Diset di mycompany";
			} else {

				//Kacab
				$ApprovedBy = $trx->ApprovedBy;
				$ApprovedByName = $trx->ApprovedByName;
				$ApprovedByEmail = $trx->ApprovedByEmail;
				$url="no_prepo=".urlencode($trx->RequestNo)."&acc_by=".urlencode($ApprovedByEmail);
		    	$post = array(); 
				$post["ApprovalType"] = $this->approvaltype;
				$post["RequestNo"] = $trx->RequestNo; 
				$post["RequestDate"] = $EmailDate;
				$post["RequestBy"] = $trx->RequestBy;
				$post["RequestByName"] = $trx->RequestByName;
				$post["RequestByEmail"] = $trx->RequestByEmail;
				$post["ApprovedBy"] = $ApprovedBy;
				$post["ApprovedByName"] = $ApprovedByName;
				$post["ApprovedByEmail"] = $ApprovedByEmail;
				$post["ApprovedDate"] = NULL;
				$post["ApprovalStatus"] = "UNPROCESSED";
				$post["ApprovalNote"] = NULL;
				$post["AddInfo1"] = "";
				$post["AddInfo1Value"] = "";
				$post["AddInfo2"] = "Divisi";
				$post["AddInfo2Value"] = $trx->AddInfo2Value;
				$post["AddInfo3"] = "";
				$post["AddInfo3Value"] = "";
				$post["AddInfo4"] = "URL";
				$post["AddInfo4Value"] = $url;
				$post["AddInfo5"] = "";
				$post["AddInfo5Value"] = "";
				$post["AddInfo6"] = "Periode";
				$post["AddInfo6Value"] = $trx->AddInfo6Value;
				$post["AddInfo7"] = "";
				$post["AddInfo7Value"] = "";
				$post["AddInfo8"] = "";
				$post["AddInfo8Value"] = "";
				$post["AddInfo9"] = "Wilayah";
				$post["AddInfo9Value"] = $trx->AddInfo9Value;
				$post["AddInfo10"] = "";
				$post["AddInfo10Value"] = "";
				$post["AddInfo11"] = "";
				$post["AddInfo11Value"] = "";
				$post["AddInfo12"] = "";
				$post["AddInfo12Value"] = "";
				$post["ApprovalNeeded"] = 1;
				$post["Priority"] = 1;
				$post["ExpiryDate"] = $ExpiryDate;
				$post["BhaktiFlag"] = "UNPROCESSED";
				$post["BhaktiProcessDate"] = "";
				$post["IsCancelled"] = 0;
				$post["CancelledBy"] = NULL;
				$post["CancelledByName"] = NULL;
				$post["CancelledDate"] = NULL;
				$post["CancelledNote"] = NULL;
				$post["CancelledByEmail"] = NULL;
				$post["LocationCode"] = "HO";
				$post["IsEmailed"] = 1;
				$post["EmailedDate"] = $EmailDate;
				$post["approvedbyfrommsconfig"] = $this->approvedbyfrommsconfig;
				$post["expirydatefrommsconfig"] = $this->expirydatefrommsconfig; 
				$post["amount"] = 0; 
				$post["branchID"] = "";
				$this->approvalmodel->doaction('insert', $post);
				// echo(json_encode($x));

				//saat Request PORO, hit myCompany untuk insert ke TblApproval 2 level
				//BM
				$ApprovedBy = $bms[0]->userid;
				$ApprovedByName = $bms[0]->nm_slsman;
				$ApprovedByEmail = (($bms[0]->email_address=="")? $bms[0]->email : $bms[0]->email_address);
				$url="no_prepo=".urlencode($trx->RequestNo)."&confirmed_by=".urlencode($ApprovedByEmail);
		    	$post = array(); 
				$post["ApprovalType"] = $this->approvaltype;
				$post["RequestNo"] = $trx->RequestNo; 
				$post["RequestDate"] = $EmailDate;
				$post["RequestBy"] = $trx->RequestBy;
				$post["RequestByName"] = $trx->RequestByName;
				$post["RequestByEmail"] = $trx->RequestByEmail;
				$post["ApprovedBy"] = $ApprovedBy;
				$post["ApprovedByName"] = $ApprovedByName;
				$post["ApprovedByEmail"] = $ApprovedByEmail;
				$post["ApprovedDate"] = NULL;
				$post["ApprovalStatus"] = "UNPROCESSED";
				$post["ApprovalNote"] = NULL;
				$post["AddInfo1"] = "";
				$post["AddInfo1Value"] = "";
				$post["AddInfo2"] = "Divisi";
				$post["AddInfo2Value"] = $trx->AddInfo2Value;
				$post["AddInfo3"] = "";
				$post["AddInfo3Value"] = "";
				$post["AddInfo4"] = "URL";
				$post["AddInfo4Value"] = $url;
				$post["AddInfo5"] = "";
				$post["AddInfo5Value"] = "";
				$post["AddInfo6"] = "Periode";
				$post["AddInfo6Value"] = $trx->AddInfo6Value;
				$post["AddInfo7"] = "";
				$post["AddInfo7Value"] = "";
				$post["AddInfo8"] = "";
				$post["AddInfo8Value"] = "";
				$post["AddInfo9"] = "Wilayah";
				$post["AddInfo9Value"] = $trx->AddInfo9Value;
				$post["AddInfo10"] = "";
				$post["AddInfo10Value"] = "";
				$post["AddInfo11"] = "";
				$post["AddInfo11Value"] = "";
				$post["AddInfo12"] = "";
				$post["AddInfo12Value"] = "";
				$post["ApprovalNeeded"] = 1;
				$post["Priority"] = 2;
				$post["ExpiryDate"] = $ExpiryDate;
				$post["BhaktiFlag"] = "UNPROCESSED";
				$post["BhaktiProcessDate"] = "";
				$post["IsCancelled"] = 0;
				$post["CancelledBy"] = NULL;
				$post["CancelledByName"] = NULL;
				$post["CancelledDate"] = NULL;
				$post["CancelledNote"] = NULL;
				$post["CancelledByEmail"] = NULL;
				$post["LocationCode"] = "HO";
				$post["IsEmailed"] = 0;
				$post["EmailedDate"] = NULL;
				$post["approvedbyfrommsconfig"] = $this->approvedbyfrommsconfig;
				$post["expirydatefrommsconfig"] = $this->expirydatefrommsconfig; 
				$post["amount"] = 0; 
				$post["branchID"] = "";
				$this->approvalmodel->doaction('insert', $post);
				// echo("<br>");
				// echo(json_encode($x));

			}
		}

		public function CancelTblApprovalBecauseTrxIsDeleted()
		{    
		    $data = file_get_contents('php://input');
		    $trx = json_decode($data);

			$post = array(); 
			$post["ApprovalType"] = $this->approvaltype;
			$post["RequestNo"] = $trx->RequestNo; 
			$post["CancelledBy"] = $trx->CancelledBy;
			$post["CancelledByName"] = $trx->CancelledByName;
			$post["CancelledByEmail"] = $trx->CancelledByEmail;
			$post["CancelledNote"] = $trx->CancelledNote;
			$this->approvalmodel->CancelTblApprovalBecauseTrxIsDeleted($post);
		}
			
	}
?>	