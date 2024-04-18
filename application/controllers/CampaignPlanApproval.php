<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	
	class CampaignPlanApproval extends NS_Controller
	{
		
		function __construct()
		{
			parent::__construct();
			$this->load->model('SalesManagerModel');
			$this->load->model('CampaignPlanModel');
			$this->load->library('email');
			$this->load->library('excel');
			require_once(dirname(__FILE__)."/approval.php"); // the controller route.
			$this->approval = new approval();
		}


		public function CreateEmailContent($trxID, $planHD){

			$header = "Diinput Oleh: ".$planHD[0]->CreatedBy."<br>";
			$TGLINPUT = (($planHD[0]->UpdatedDate==null)? $planHD[0]->CreatedDate:$planHD[0]->UpdatedDate);
			$header.= "Waktu Request:".date("Y-m-d H:i:s", strtotime($TGLINPUT))."<br>";
			$header.= "<hr><br>";
			$header.= "Nama Rencana : <b>".$planHD[0]->CampaignName."</b><br>";
			$header.= "Kode Rencana : <b>".$planHD[0]->CampaignID."</b><br>";
			$header.= "Divisi: <b>".$planHD[0]->Division."</b><br>";
			$header.= "Periode: <b>".date("d-M-Y",strtotime($planHD[0]->CampaignStartHD))." s/d ".date("d-M-Y",strtotime($planHD[0]->CampaignEndHD))."</b><br>";
			$header.= "JumlahHari: <b>".$planHD[0]->JumlahHariHD."</b><br>";
			$header.= "<br>";

			
			$body = "<h3>Detail Barang</h3>";
			$body.= "<style> th,td { border:1px solid #ccc; padding:3px; text-align:right; }</style>";
			$tables = array();
			$products = array();
			$product = array();

			$counter = 0;
			foreach($planHD as $brgs) {
				array_push($product, array("ProductID"=>$brgs->ProductID, "TotalQTY"=>0));
			}

			$col = array();
			$x = 0;
			$ListWilayah = json_decode($this->CampaignPlanModel->GetTransaksiWilayahInclude($trxID));
			//echo(json_encode($ListWilayah)."<br><br>");
			foreach($ListWilayah as $w) {
				$col[$x][0] = $w->Wilayah;
				$x += 1;
			}

			$ProductCount = count($product);
			$CheckItemID = $this->CampaignPlanModel->CheckItemID($trxID, $planHD);

			for($i=0;$i<$ProductCount;$i++) {
				//echo($product[$i]["ProductID"]."<br><br>");
				$TotalQty = 0;
				// $prevCamp = $this->CampaignPlanModel->GetSelectedPreviousCampaigns($trxID, $product[$i]["ProductID"]);
				//echo(json_encode($prevCamp)."<br><br>");
				$breakdowns = $this->CampaignPlanModel->GetBreakdowns($trxID, $product[$i]["ProductID"]);
				//echo(json_encode($breakdowns)."<br><br>");
				$y = $i+1;
				$x = 0;
				$breakdownFound = false;

				foreach($ListWilayah as $w) {
					foreach($breakdowns as $b) {
						if (trim($w->Wilayah)==trim($b->Wilayah)) {
							$col[$x][$y] = $b->Qty;
							$breakdownFound = true;
							$TotalQty+=$b->Qty;
						}
					}
					if ($breakdownFound==false) {
							$col[$x][$y] = 0;
					}
					$x = $x+1;
				}
				$product[$i]["TotalQTY"] = $TotalQty;
			}

			$JmlTable = ceil($ProductCount/8);

			for($t=1;$t<=$JmlTable;$t++) {
				$start = ($t*8) - 8;
				$end = $start + 8;
				if ($end > $ProductCount) {
					$end = $ProductCount;
				}

				$body.= "<table>";
				$body.=	"	<tr>";
				$body.= "		<th width='20%'></th>";
				for ($i=$start;$i<$end;$i++) {
				$body.=	"		<th width='10%'>".$product[$i]["ProductID"]."</th>";
				}
				$body.=	"	</tr>";
				for($i=0;$i<count($col);$i++) {
				$body.=	"	<tr>";
				for($j=$start;$j<=$end;$j++) {
					if ($j==$start) {
						$body.=	"		<td style='text-align:left;'>".$col[$i][0]."</td>";
					//} else if ($j==$start) {
					//	$body.=	"		<td style='text-align:left;'>".$col[$i][0]."</td>";
					//	$body.=	"		<td>".number_format($col[$i][$j])."</td>";
					} else {
						$body.=	"		<td>".number_format($col[$i][$j])."</td>";
					}
				}
				$body.=	"	</tr>";
				}
				$body.= "	<tr>";
				$body.=	"		<td><b>TOTAL</b></td>";
				for ($i=$start;$i<$end;$i++) {
				$body.=	"		<td><b>".number_format($product[$i]["TotalQTY"])."</b></td>";
				}
				$body.= "	</tr>";
				$body.= "</table>";

			}

			return $header.$body;
		}
				

		function Approved()
		{
			$CampaignID = urldecode($this->input->get('campaignid'));
			$ApprovedBy = urldecode($this->input->get('approvedby'));
			$planHD = json_decode($this->CampaignPlanModel->GetTransaksiDetail($CampaignID));
			$htmlContent = $this->CreateEmailContent($CampaignID, $planHD);
			
			$App = $this->CampaignPlanModel->getApproved($CampaignID);
			//die(json_encode($App));
			$data = array(
				'isApproved' => 1,
				'ApprovedBy' => $App[0]->ApprovedByName
				);
				

			if($App[0]->isApproved == 0){
				$data = array(
				'isApproved' => 1,
				'ApprovedBy' => $App[0]->ApprovedByName
				);

				// $params = array();
				// $params['ApprovalType'] = 'CAMPAIGN PLAN';
				// $params['RequestNo'] = $CampaignID;
				// $params['ApprovedBy'] = $ApprovedBy;
				// $params['ApprovalNote'] = '';
				// $x = $this->approval->approve($params);
				
				$this->CampaignPlanModel->Approve($CampaignID, $data);

				// $this->CampaignPlanModel->SendApprovedPlanToBhakti($CampaignID, $data);
				$this->EmailNotifikasi($CampaignID,'APPROVED');

			} else {
				// $data = array(
				// 'isApproved' => 1,
				// 'ApprovedBy' => $ApprovedBy
				// );
				// $this->CampaignPlanModel->Approved($CampaignID, $data);
				// $this->CampaignPlanModel->SendApprovedPlanToBhakti($CampaignID, $data);
				
				$status = ($App[0]->isApproved == 1) ? "approve" : "reject";
				echo "<center><b style='color:".(($status=="reject")?"red":"green").";'>Request Rencana Campaign sudah di-".$status ." tanggal ".$App[0]->ApprovedDate."</b></center>";
				echo "<br>";
				echo $htmlContent;
			}
		}

		function ApprovedPlanUnprocessed()
		{
			$Plans = $this->CampaignPlanModel->GetListApprovedUnprocessed();
			// die(json_encode($Plans));

			foreach ($Plans as $p) {
				$data = array(
					'isApproved' => 1,
					'ApprovedBy' => $p->ApprovedByName
				);
				//echo(json_encode($data)."<br><br>");

				$this->CampaignPlanModel->SendApprovedPlanToBhakti($p->CampaignID, $data);
			}			
			echo(json_encode($Plans));
		}

		function Rejected()
		{
			$CampaignID = urldecode($this->input->get('campaignid'));
			$ApprovedBy = urldecode($this->input->get('approvedby'));
			
			$planHD = json_decode($this->CampaignPlanModel->GetTransaksiDetail($CampaignID));
			$htmlContent = $this->CreateEmailContent($CampaignID, $planHD);
			$App = $this->CampaignPlanModel->getApproved($CampaignID);
			//die(json_encode($App));
			
			if($App[0]->isApproved == 0){

				$data["CampaignID"] = $CampaignID;
				$data["planHD"] = $planHD;
				$data["htmlContent"] = $htmlContent;
				$this->load->view("MsCampaignPlanRejectView", $data);
			}
			else {
				$status = ($App[0]->isApproved == 1) ? "approve" : "reject";
				echo "<center><b style='color:".(($status=="reject")?"red":"green").";'>Request Rencana Campaign sudah di-".$status ." tanggal ".$App[0]->ApprovedDate."</b></center>";
				echo "<br>";
				echo $htmlContent;
			}
		}

		function Reject()
		{
			$post = $this->PopulatePost();
			//die(json_encode($post));
			$CampaignID = $post["txtKodeCampaign"];
			$RejectNote = $post["txtAlasan"];
			$planHD = json_decode($this->CampaignPlanModel->GetTransaksiDetail($CampaignID));
			$ApprovedBy = $planHD[0]->ApprovedBy;
			
			$App = $this->CampaignPlanModel->getApproved($CampaignID);
			//die(json_encode($App));
			
			if($App[0]->isApproved == 0){
				$data = array(
				'isApproved' => 2,
				'ApprovedBy' => $ApprovedBy,
				'CancelNote' => $RejectNote
				);

				// $params = array();
				// $params['ApprovalType'] = 'CAMPAIGN PLAN';
				// $params['RequestNo'] = $CampaignID;
				// $params['ApprovedBy'] = $ApprovedBy;
				// $params['ApprovalNote'] = $RejectNote;
				// $x = $this->approval->reject($params);

				$this->CampaignPlanModel->Reject($CampaignID, $data);
				$this->EmailNotifikasi($CampaignID,'REJECTED');
			}
			else {
				$status = ($App[0]->isApproved == 1) ? "approve" : "reject";
				echo "<center>Request Rencana Campaign sudah di-".$status ." tanggal ".$App[0]->ApprovedDate."</center>";

			}
		}
		
		public function EmailNotifikasi($CampaignID, $status)
		{
						
			$detail =  $this->CampaignPlanModel->GetTransaksiDetail($CampaignID);
			$data = json_decode($detail);
			// $email_data = $this->CampaignPlanModel->getInsertedData($CampaignID);
						
			// var_dump($data);
			// die;
			
			if (!empty($detail)) {
				$badge = ($status=='APPROVED') ? "<span style='background:limegreen; padding:5px;color:#fff'>DISETUJUI</span>" : "<span style='background:RED; padding:5px;color:#fff'>DITOLAK</span>";

				$header = "";
				if ($status=="APPROVED") {
					$header = "<h3>RENCANA CAMPAIGN ".$badge."</h3><br>";
					$header.= "Diapprove Oleh : <b>".$data[0]->ApprovedByName."</b><br>";
					$header.= "Waktu Approve : <b>".date("d-M-Y h:i:s", strtotime($data[0]->ApprovedDate))."</b><br>";
				} else {
					$header = "<h3>RENCANA CAMPAIGN ".$badge."</h3><br>";
					$header.= "Keterangan : <b><font color='red'>".$data[0]->CancelNote."</font></b><br>";
					$header.= "Direject Oleh : <b>".$data[0]->ApprovedByName."</b><br>";
					$header.= "Waktu Reject : <b>".date("d-M-Y h:i:s", strtotime($data[0]->ApprovedDate))."</b><br>";
				}
				$header.="<hr><br>";

				$emailContent = $this->CreateEmailContent($CampaignID, $data);
				
				
				$this->email->clear(true);
				$this->email->from("bhaktiautoemail.noreply@bhakti.co.id", "BHAKTI AUTOEMAIL");
				$this->email->to($data[0]->CreatedBy);
				// $this->email->to("itdev.dist@bhakti.co.id");
				$email_content = $header.$emailContent;
				
				//$email_content .= $badge."<br><br>";
				//$email_content .= "Rencana Campaign ini telah di-<b>".(($status=="APPROVED")?"Approve":"Reject")."</b>";
				//$email_content .= $this->EmailContent($CampaignID);
				
				$this->email->subject("Rencana Campaign ".$data[0]->CampaignName." ".(($status=="APPROVED")?"Disetujui":"Ditolak")." ");
				$this->email->message($email_content);
				
				// print_r($email_content);die;
				
				if ($this->email->Send()) {
					$this->session->set_flashdata([
					'success_message' => 'Berhasil simpan.',
					]);
					// echo 'masuk sini';die;
					// redirect("PersiapanBarangCampaign");
					} else {
					// echo $this->email->print_debugger();die;
					$this->session->set_flashdata([
					'err_message' => 'Email Gagal dikirim.',
					]);
					
					// redirect("PersiapanBarangCampaign");
				}
				} else {
				$this->session->set_flashdata([
				'err_message' => 'Gagal simpan.',
				]);
				
				// redirect("PersiapanBarangCampaign");
			}
		}
		

	}
