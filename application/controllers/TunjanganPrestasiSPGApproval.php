<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	class TunjanganPrestasiSPGApproval extends NS_Controller 
	{
		public $cc = "";
		
		public function __construct()
		{
			parent::__construct();	
			//$this->load->model("TunjanganPrestasiSPGApprovalModel");
			$this->load->library('email');	
			$this->load->model('ConfigSysModel');
			$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		}
		
		public function Approve($kode_import="") {
			$kodeimport=urldecode($this->input->get("kodeimport"));
			$URL = "";
			if ($kode_import!="") {
				$URL = $this->API_URL."/TunjanganPrestasiSPGApproval/Approve?kodeimport=".urlencode($kode_import);
			} else {
				$URL = $this->API_URL."/TunjanganPrestasiSPGApproval/Approve?kodeimport=".urlencode($kodeimport);
			}
			$result = json_decode(file_get_contents($URL));
			
			if ($result=="sukses") {
				echo "<div style='padding:10px;background:#d4edda;border:1px solid #c3e6cb;text-align:center'>Tunjangan Prestasi SPG berhasil di-approve</div>";
			} else {
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid ##f5c6cb;text-align:center'>".$result."</div>";				
			}
		}
		
		public function ApproveNew($kode_import="") {
			$kodeimport=urldecode($this->input->get("kodeimport"));
			$URL = "";
			if ($kode_import!="") {
				$URL = $this->API_URL."/TunjanganPrestasiSPGApproval/ApproveNew?kodeimport=".urlencode($kode_import);
			} else {
				$URL = $this->API_URL."/TunjanganPrestasiSPGApproval/ApproveNew?kodeimport=".urlencode($kodeimport);
			}
			$result = json_decode(file_get_contents($URL));
			if ($result->msg=="sukses") {
				echo "<div style='padding:10px;background:#d4edda;border:1px solid #c3e6cb'><center>Tunjangan Prestasi SPG berhasil di-approve</center><br>".$result->desc."</div>";
			} else {
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><b>".$result->msg."</b></div>";				
			}
		}
		
		public function Reject($kode_import="") {
			$kodeimport=urldecode($this->input->get("kodeimport"));
			$URL = "";
			if ($kode_import!="") {
				$URL = $this->API_URL."/TunjanganPrestasiSPGApproval/Reject?kodeimport=".urlencode($kode_import);
			} else {
				$URL = $this->API_URL."/TunjanganPrestasiSPGApproval/Reject?kodeimport=".urlencode($kodeimport);
			}

			$result = json_decode(file_get_contents($URL));
			
			//$result = $this->TunjanganPrestasiSPGApprovalModel->reject($kode_import);
			if ($result=="sukses") {
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid ##f5c6cb;text-align:center'>Tunjangan Prestasi SPG berhasil di-reject</div>";
			} else { 
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid ##f5c6cb;text-align:center'>".$result."</div>";
			}
		}
		
	}
?>	