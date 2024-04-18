<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	class RequestPembayaranApproval extends NS_Controller 
	{
		public $cc = "";
		
		public function __construct()
		{
			parent::__construct();
			$this->load->library('email');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;	
		}
		public function Proses(){
			$norequest = urldecode($this->input->get('norequest'));
			$level = urldecode($this->input->get('level'));
			$header = [];
			$detail = [];

			$urlBhakti = $this->API_URL."/RequestPembayaran/Proses?norequest=".$norequest."&level=".$level;
			
			$json = @file_get_contents($urlBhakti);
			if($json !== false){
				$jsonData = json_decode($json,true);
				
				$data = array(
					'norequest' => $norequest,
					'level' => $level,
					'header' => $jsonData['header'],
					'detail' => $jsonData['detail'],
				);
				$this->_ProsesView($data);
			}
			else die('Halaman tidak ditemukan!');
		}
		
		public function Approve($no_request="") {
			$norequest=urldecode($this->input->get("norequest"));
			$level=urldecode($this->input->get("level"));

			$urlBhakti = $this->API_URL."/index.php/RequestPembayaran/Approve?norequest=".urlencode($norequest)."&level=".urlencode($level);
			// die($urlBhakti);
			// $result = json_decode(file_get_contents($urlBhakti));
			// die(json_encode($result));

			$GetResults = HttpGetRequest($urlBhakti, $this->API_URL, "APPROVE REQUEST PEMBAYARAN", 6000);
			$result = json_decode($GetResults);
			
			$log = '';
			$log .= "No. Request: <b>".$result->history->No_Request."</b><br>";
			$log .= "Tgl. Request: <b>".$result->history->Tgl_Request."</b><br>";
			$log .= "Supplier: <b>".$result->history->Supplier."</b><br>";
			$log .= "Total Bayar: <b>".round($result->history->TotalBayar)."</b><br>";
			$log .= "No. PO: <b>".implode(', ',(array) $result->history->no_po)."</b><br>";
			$log .= "<br>";
			$log .= "Log Request Pembayaran:<b></b> <br>";
			$log .= "Request Oleh: <b>".$result->history->User_Name." <em>".$result->history->Entry_Time."</em></b> <br>";
			
			if($result->history->ApprovalLevel1By!=''){
				$log .= "Approval ".$result->history->RequestApprovalLevel1_JobTitle." Oleh: <b>".$result->history->ApprovalLevel1By." <em>".$result->history->ApprovalLevel1Date."</em></b> <br>";
			}
			if($result->history->ApprovalLevel2By!=''){
				$log .= "Approval ".$result->history->RequestApprovalLevel2_JobTitle." Oleh: <b>".$result->history->ApprovalLevel2By." <em>".$result->history->ApprovalLevel2Date."</em></b> <br>";
			}
			if($result->history->ApprovalLevel3By!=''){
				$log .= "Approval ".$result->history->RequestApprovalLevel3_JobTitle." Oleh: <b>".$result->history->ApprovalLevel3By." <em>".$result->history->ApprovalLevel3Date."</em></b> <br>";
			}
			
			// die(json_encode($result));

			if ($result->pesan=='SUKSES') {
				
				$ApprovedBy = '';
				if($level==1){
					$ApprovedBy = $result->history->RequestApprovalLevel1_JobTitle;
				}
				elseif($level==2){
					$ApprovedBy = $result->history->RequestApprovalLevel2_JobTitle;
				}
				elseif($level==3){
					$ApprovedBy = $result->history->RequestApprovalLevel3_JobTitle;
				}
				
				$this->updateStatusApproval('approve', $result->history->No_Request, $ApprovedBy);
			
				echo "<div style='padding:10px;background:#d4edda;border:1px solid #c3e6cb'>
				<center><h2>REQUEST BERHASIL DIAPPROVE</h2></center><br> ";
				echo $log;
				echo "</div>";
				}
			else {
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><center><h2>".$result->pesan."</h2></center><br>".$log."</div>";				
			}
		}
		
		public function Reject($no_request="") {

			$norequest=urldecode($this->input->get("norequest"));
			$level=urldecode($this->input->get("level"));
			$URL = $this->API_URL."/RequestPembayaran/GetDetail?norequest=".urlencode($norequest);
			// $history = json_decode(file_get_contents($URL));

			$GetResults = HttpGetRequest($URL, $this->API_URL, "AMBIL DETAIL REQUEST PEMBAYARAN", 6000);
			$history = json_decode($GetResults, true);
			
			$log = "";
			//$log .= "No. Request: <b>".$history->No_Request."</b><br>";
			//$log .= "Tgl. Request: <b>".$history->Tgl_Request."</b><br>";
			//$log .= "Supplier: <b>".$history->Supplier."</b><br>";
			//$log .= "Total Bayar: <b>".round($history->TotalBayar)."</b><br>";
			//$log .= "No. PO: <b>".implode(', ',(array) $history->no_po)."</b><br>";
			//$log .= "<br>";
			//$log .= "Log Request Pembayaran:<b></b> <br>";
			//$log .= "Request Oleh: <b>".$history->User_Name." <em>".$history->Entry_Time."</em></b> <br>";
			$log .= "No. Request: <b>".$history["No_Request"]."</b><br>";
			$log .= "Tgl. Request: <b>".$history["Tgl_Request"]."</b><br>";
			$log .= "Supplier: <b>".$history["Supplier"]."</b><br>";
			$log .= "Total Bayar: <b>".round($history["TotalBayar"])."</b><br>";
			$log .= "No. PO: <b>".implode(', ',(array) $history["no_po"])."</b><br>";
			$log .= "<br>";
			$log .= "Log Request Pembayaran:<b></b> <br>";
			$log .= "Request Oleh: <b>".$history["User_Name"]." <em>".$history["Entry_Time"]."</em></b> <br>";
			
			echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'>".$log."</div>";
			//if ($history->Status_ProsesBayar=="CANCELLED") {
			if ($history["Status_ProsesBayar"]=="CANCELLED") {
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2>REQUEST SUDAH DICANCEL!</h2></div>";
			//} else if ($history->RequestApprovalStatus=="APPROVED") {
			} else if ($history["RequestApprovalStatus"]=="APPROVED") {
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2>REQUEST SUDAH DIAPPROVE!</h2></div>";
			//} else if ($history->RequestApprovalStatus=="REJECTED") {
			} else if ($history["RequestApprovalStatus"]=="REJECTED") {
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2>REQUEST SUDAH DIREJECT!</h2></div>";	
			} else {
			
				$approvedby = '';
				if($level==1){
					$approvedby = $history['RequestApprovalLevel1_JobTitle'];
				}
				elseif($level==2){
					$approvedby = $history['RequestApprovalLevel2_JobTitle'];
				}
				elseif($level==3){
					$approvedby = $history['RequestApprovalLevel3_JobTitle'];
				}
				
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'>
				<form action='./Rejected' method='POST'>
				<input type='hidden' name='norequest' value='".$norequest."'>
				<input type='hidden' name='approvedby' value='".$approvedby."'>
				<h2>Reject Note</h2>
				<textarea name='rejectnote' style='width:100%;padding:5px;margin-bottom:10px' required></textarea>
				<input type='submit' value='REJECT' style='color:#fff; padding:10px; background:#dc3545; border:1px solid #dc3545; text-align:center;'>
				</form>
				</div>";
			}
		}
		
		public function Rejected() {
			$norequest=urldecode($this->input->post("norequest"));
			$approvedby=urldecode($this->input->post("approvedby"));
			$rejectnote=urldecode($this->input->post("rejectnote"));
			$URL = $this->API_URL."/RequestPembayaran/Reject?norequest=".urlencode($norequest)."&rejectnote=".urlencode($rejectnote);			
			// $result = json_decode(file_get_contents($URL));

			$GetResults = HttpGetRequest($URL, $this->API_URL, "REJECT REQUEST PEMBAYARAN", 6000);
			
			$result = json_decode($GetResults);
			if ($result->pesan=='sukses') {
				$this->updateStatusApproval('reject', $norequest, $approvedby, $rejectnote);
				echo "<div style='padding:10px;background:#d4edda;border:1px solid #f5c6cb;'><h2><center>REQUEST BERHASIL DIREJECT</center></h2></div>";
				} else { 
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2><center>".$result->pesan."</center></h2></div>";
			}
		}
		private function _ProsesView($data){
			$level = $data['level'];
			$header = $data['header'];
			$detail = $data['detail'];

			$baseUrl = base_url();
			$view =  <<<HTML
			<h2>REQUEST PEMBAYARAN [{$data['norequest']}]</h2>
			<hr>
			<table style="border-collapse:collapse">
				<tbody>
					<tr>
						<td width="30%" colspan="2">Nama Supplier</td>
						<td width="70%" colspan="5"><b>: [{$header['Kd_Supl']}] {$header['Nm_Supl']}</b></td>
					</tr>
					<tr>
						<td colspan="2">Status</td>
						<td colspan="5"><b>: {$header['Status']}</b></td>
					</tr>
					<tr>
						<td colspan="2">Ket. Status</td>
						<td colspan="5"><b>: {$header['Ket_Status']}</b></td>
					</tr>
					<tr>
						<td colspan="2">Invoice</td>
						<td colspan="5"><b>: {$header['Invoice']}</b></td>
					</tr>
					<tr>
						<td colspan="2">Ket. Invoice</td>
						<td colspan="5"><b>: {$header['Ket_Invoice']}</b></td>
					</tr>
					<tr>
						<td colspan="2">Kategori Pembayaran</td>
						<td colspan="5"><b>: {$header['Ket_KategoriBayar']}</b></td>
					</tr>
					<tr>
						<td colspan="2">Catatan</td>
						<td colspan="5"><b>: {$header['Notes']}</b></td>
					</tr>
					<tr>
						<td width="5%" style="border:1px solid #ccc;padding:3px"><b>No</b></td>
						<td width="25%" style="border:1px solid #ccc;padding:3px"><b>No Request</b></td>
						<td width="20%" style="border:1px solid #ccc;padding:3px"><b>No. PO</b></td>
						<td width="15%" style="border:1px solid #ccc;padding:3px"><b>No. PU</b></td>
						<td width="15%" style="border:1px solid #ccc;padding:3px"><b>Sub Total</b></td>
						<td width="10%" style="border:1px solid #ccc;padding:3px"><b>PPn</b></td>
						<td width="10%" style="border:1px solid #ccc;padding:3px"><b>PPh</b></td>
					</tr>
HTML;
			$tglRequest = $header['Tgl_Request'];
			if($tglRequest!='') $tglRequest = date('d M Y H:i:s',strtotime($tglRequest));
			
			$level1Date = $header['Level1Date'];
			if($level1Date!='') $level1Date = date('d M Y H:i:s',strtotime($level1Date));

			$level2Date = $header['Level2Date'];
			if($level2Date!='') $level2Date = date('d M Y H:i:s',strtotime($level2Date));

			$total = 0;
			$totalPpn = 0;
			$totalPph = 0;
			$grandTotal = 0;
			foreach($detail as $key => $value){
					$no = ($key+1);
					$subtotal = number_format($value['SubTotal']);
					$ppn = number_format($value['PPn']);
					$pph = number_format($value['PPh']);

					$total += $value['SubTotal'];
					$totalPpn += $value['PPn'];
					$totalPph += $value['PPh'];

			$view .= <<<HTML
					<tr style="background-color:#99ddff">
						<td style="border:1px solid #ccc;padding:3px">{$no}</td>
						<td style="border:1px solid #ccc;padding:3px">{$value['No_Request']}&nbsp;</td>
						<td style="border:1px solid #ccc;padding:3px">{$value['No_PO']}&nbsp;</td>
						<td style="border:1px solid #ccc;padding:3px">{$value['No_PU']}&nbsp;</td>
						<td style="border:1px solid #ccc;padding:3px" align="right">{$subtotal}</td>
						<td style="border:1px solid #ccc;padding:3px" align="right">{$ppn}</td>
						<td style="border:1px solid #ccc;padding:3px" align="right">{$pph}</td>
					</tr>
HTML;
			}
			$grandTotal = $total + $totalPpn - $totalPph;
			
			$totalCur 	 = number_format($total);
			$totalPpnCur = number_format($totalPpn);
			$totalPphCur = number_format($totalPph);
			$grandTotalCur = number_format($grandTotal);
			$view .= <<<HTML
					<tr>
						<td align="right" colspan="5"><b>Total:</b></td>
						<td align="right" colspan="2">{$totalCur}</td>
					</tr>
					<tr>
						<td align="right" colspan="5"><b>PPN:</b></td>
						<td align="right" colspan="2">{$totalPpnCur}</td>
					</tr>
					<tr>
						<td align="right" colspan="5"><b>PPh:</b></td>
						<td align="right" colspan="2">{$totalPphCur}</td>
					</tr>
					<tr>
						<td align="right" colspan="5"><b>Potongan Lain:</b></td>
						<td align="right" colspan="2">0</td>
					</tr>
					<tr>
						<td align="right" colspan="5"><b>Grand Total:</b></td>
						<td align="right" colspan="2">{$grandTotalCur}</td>
					</tr>
				</tbody>
			</table>
			
				
			
HTML;
			$approvalDate = '';
			switch ($level) {
				case "1":
					$approvalDate = $header['Level1Date'];
					break;
			  	case "2":
					$approvalDate = $header['Level2Date'];
					break;
			  	case "3":
					$approvalDate = $header['Level3Date'];
			}
			if($approvalDate==''){
				//udah di approve
				$view .= <<<HTML
				<div style="margin-top:40px">
					<a href="{$baseUrl}RequestPembayaranApproval/approve?norequest={$data['norequest']}&level={$data['level']}" style="text-decoration:none;color:#fff;padding:10px;background:#28a745;border:1px solid #28a745;text-align:center">Approve</a> &nbsp;&nbsp;&nbsp;
					<a href="{$baseUrl}RequestPembayaranApproval/reject?norequest={$data['norequest']}&level={$data['level']}" style="text-decoration:none;color:#fff;padding:10px;background:#dc3545;border:1px solid #dc3545;text-align:center">Reject</a></div>
				<div style="clear:both;height:40px">
HTML;
			}
			else{

			}

			$view .= <<<HTML
			</div>Log Request Pembayaran
			<br>Request Oleh: <b>{$header['User_Name']} [{$tglRequest}]</b>
HTML;
			
			if($header['Level1By']!=''){
				$view .= <<<HTML
				<br>Approval Kabag MP Oleh: <b>{$header['Level1By']} [{$level1Date}]</b>
HTML;
			}
			if($header['Level2By']!=''){
				$view .= <<<HTML
				<br>Approval Manager MP Oleh: <b>{$header['Level2By']} [{$level2Date}]</b>
HTML;
			}

			echo $view;
		}
		
		private function updateStatusApproval($Status, $RequestNo, $ApprovedBy, $ApprovalNote='')
		{
			$URL = site_url().'/approval/'.$Status;
			$POST = array('ApprovalType'=>'REQUEST PEMBAYARAN','RequestNo'=>$RequestNo,'ApprovedBy'=>$ApprovedBy,'ApprovalNote'=>$ApprovalNote);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$URL);
			curl_setopt($ch, CURLOPT_POST,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $POST);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_VERBOSE,true);
			$result = curl_exec($ch);
			// echo $result; die;
		}
		
		
	}
?>	