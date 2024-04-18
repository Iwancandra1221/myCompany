<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	class RequestPembayaran extends MY_Controller 
	{
		public $cc = "";
		
		public function __construct()
		{
			parent::__construct();
			$this->load->library('email');
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		}
		
		public function Approve($no_request="") {
			$norequest=urldecode($this->input->get("norequest"));
			$level=urldecode($this->input->get("level"));
			$URL = $this->API_URL."/RequestPembayaran/Approve?norequest=".urlencode($norequest)."&level=".urlencode($level);
			//die($URL);
			$result = json_decode(file_get_contents($URL));
			
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
			
			if ($result->pesan=='sukses') {
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
			$history = json_decode(file_get_contents($URL));
			
			
			$log = '';
			$log .= "No. Request: <b>".$history->No_Request."</b><br>";
			$log .= "Tgl. Request: <b>".$history->Tgl_Request."</b><br>";
			$log .= "Supplier: <b>".$history->Supplier."</b><br>";
			$log .= "Total Bayar: <b>".round($history->TotalBayar)."</b><br>";
			$log .= "No. PO: <b>".implode(', ',(array) $history->no_po)."</b><br>";
			$log .= "<br>";
			$log .= "Log Request Pembayaran:<b></b> <br>";
			$log .= "Request Oleh: <b>".$history->User_Name." <em>".$history->Entry_Time."</em></b> <br>";
			
			echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'>".$log."</div>";
			if ($history->Status_ProsesBayar=="CANCELLED") {
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2>REQUEST SUDAH DICANCEL!</h2></div>";
			} else if ($history->RequestApprovalStatus=="APPROVED") {
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2>REQUEST SUDAH DIAPPROVE!</h2></div>";
			} else if ($history->RequestApprovalStatus=="REJECTED") {
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2>REQUEST SUDAH DIREJECT!</h2></div>";	
			} else {
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'>
				<form action='./Rejected' method='POST'>
				<input type='hidden' name='norequest' value='".$norequest."'>
				<h2>Reject Note</h2>
				<textarea name='rejectnote' style='width:100%;padding:5px;margin-bottom:10px' required></textarea>
				<input type='submit' value='REJECT' style='color:#fff; padding:10px; background:#dc3545; border:1px solid #dc3545; text-align:center;'>
				</form>
				</div>";
			}
		}
		
		public function Rejected() {
			$norequest=urldecode($this->input->post("norequest"));
			$rejectnote=urldecode($this->input->post("rejectnote"));
			$URL = $this->API_URL."/RequestPembayaran/Reject?norequest=".urlencode($norequest)."&rejectnote=".urlencode($rejectnote);
			
			$result = json_decode(file_get_contents($URL));
			if ($result->pesan=='sukses') {
				echo "<div style='padding:10px;background:#d4edda;border:1px solid #f5c6cb;'><h2><center>REQUEST BERHASIL DIREJECT</center></h2></div>";
				} else { 
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2><center>".$result->pesan."</center></h2></div>";
			}
		}
		
	}
?>	