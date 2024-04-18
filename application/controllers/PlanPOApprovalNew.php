<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	
	class PlanPOApprovalNew extends NS_Controller
	{
		
		function __construct()
		{
			parent::__construct();
			$this->load->model('SalesManagerModel');
			$this->load->model('PlanPOModel');
			$this->load->library('email');
			$this->load->library('excel');
			require_once(dirname(__FILE__)."/approval.php"); // the controller route.
			$this->approval = new approval();
		}


		function Approved()
		{
			$PlanNo = urldecode($this->input->get('trxid'));
			$ApprovedBy = urldecode($this->input->get('approvedby'));
			$PlanHD = $this->PlanPOModel->GetPlanHD2($PlanNo);

			$htmlContent = $this->CreateEmailContent($PlanNo, $PlanHD);
			
			$data = array(
				'isApproved' => 1,
				'ApprovedBy' => $PlanHD->ApprovedBy
				);
				

			if($PlanHD->IsApproved == 0){
				$data = array(
				'isApproved' => 1,
				'ApprovedBy' => $PlanHD->ApprovedBy
				);
				
				$params = array();
				$params['ApprovalType'] = 'PLAN PO';
				$params['RequestNo'] = $PlanNo;
				$params['ApprovedBy'] = $ApprovedBy;
				$params['ApprovalNote'] = '';
				$x = $this->approval->approve($params);

				$this->PlanPOModel->ApproveOnly($PlanNo, $data);
				$this->EmailNotifikasi($PlanNo,'APPROVED');

			} else {
				
				$status = ($PlanHD->IsApproved == 1) ? "approve" : "reject";
				echo "<center><b style='color:".(($status=="reject")?"red":"green").";'>Request Rencana PO sudah di-".$status ." tanggal ".$PlanHD->ApprovedDate."</b></center>";
				echo "<br>";
				echo $htmlContent;
			}

		}

		function Rejected()
		{
			$PlanNo = urldecode($this->input->get('trxid'));
			$ApprovedBy = urldecode($this->input->get('approvedby'));			
			$PlanHD = $this->PlanPOModel->GetPlanHD2($PlanNo);
			$htmlContent = $this->CreateEmailContent($PlanNo, $PlanHD);

			if($PlanHD->IsApproved == 0){

				$data["PlanNo"] = $PlanNo;
				$data["PlanHD"] = $PlanHD;
				$data["htmlContent"] = $htmlContent;
				$this->load->view("MsPlanPORejectView", $data);
			}
			else {
				$status = ($PlanHD->IsApproved == 1) ? "approve" : "reject";
				echo "<center><b style='color:".(($status=="reject")?"red":"green").";'>Request Rencana PO sudah di-".$status ." tanggal ".$PlanPO->ApprovedDate."</b></center>";
				echo "<br>";
				echo $htmlContent;
			}
		}

		function Reject()
		{
			$post = $this->PopulatePost();
			$PlanNo = $post["txtKodePlan"];
			$Alasan = $post["txtAlasan"];
			$PlanHD = $this->PlanPOModel->GetPlanHD2($PlanNo);
			$ApprovedBy = $PlanHD->ApprovedBy;
						
			if($PlanHD->IsApproved == 0){
				$data = array(
				'isApproved' => 2,
				'ApprovedBy' => $ApprovedBy,
				'ApprovalNote' => $Alasan
				);

				$params = array();
				$params['ApprovalType'] = 'PLAN PO';
				$params['RequestNo'] = $PlanNo;
				$params['ApprovedBy'] = $ApprovedBy;
				$params['ApprovalNote'] = $Alasan;
				$x = $this->approval->reject($params);

				$this->PlanPOModel->Rejected($PlanNo, $data);
				$this->EmailNotifikasi($PlanNo,'REJECTED');
			}
			else {
				$status = ($PlanHD->IsApproved == 1) ? "approve" : "reject";
				echo "<center>Request Rencana PO sudah di-".$status ." tanggal ".$PlanHD->ApprovedDate."</center>";

			}
		}
		
		public function EmailNotifikasi($PlanNo, $status)
		{						
			$PlanHD =  $this->PlanPOModel->GetPlanHD2($PlanNo);
						
			
			if ($PlanHD!=null) {
				$badge = ($status=='APPROVED') ? "<span style='background:limegreen; padding:5px;color:#fff'>APPROVED</span>" : "<span style='background:RED; padding:5px;color:#fff'>REJECTED</span>";

				$header = "";
				if ($status=="APPROVED") {
					$header = "<h3>RENCANA INTERVENSI PO ".$badge."</h3><br>";
					$header.= "Diapprove Oleh : <b>".$PlanHD->ApprovedBy."</b><br>";
					$header.= "Waktu Approve : <b>".date("d-M-Y h:i:s", strtotime($PlanHD->ApprovedDate))."</b><br>";
				} else {
					$header = "<h3>RENCANA INTERVENSI PO ".$badge."</h3><br>";
					$header.= "Keterangan : <b><font color='red'>".$PlanHD->CancelNote."</font></b><br>";
					$header.= "Direject Oleh : <b>".$PlanHD->ApprovedBy."</b><br>";
					$header.= "Waktu Reject : <b>".date("d-M-Y h:i:s", strtotime($PlanHD->ApprovedDate))."</b><br>";
				}
				$header."<hr><br>";

				$emailContent = $this->CreateEmailContent($PlanNo, $PlanHD);
				
				$this->email->clear(true);
				$this->email->from("bhaktiautoemail.noreply@bhakti.co.id", "BHAKTI AUTOEMAIL");
				$this->email->to($PlanHD->CreatedBy);

				$email_content = $header.$emailContent;
				
				$this->email->subject("Rencana Intervensi PO ".$PlanHD->Division." [".$status."] ");
				$this->email->message($email_content);
				
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
		
		public function CreateEmailContent($planNo, $planHD)
		{
			$planDT = $this->PlanPOModel->GetPlanDT($planNo);
			$productSummary = $this->PlanPOModel->GetPlanDTProductSummary($planNo);
			$dtRegions = $this->PlanPOModel->GetDetailWilayah($planNo);
			$dtProducts= $this->PlanPOModel->GetDetailProduct($planNo);
			$dtPeriods = $this->PlanPOModel->GetDetailPeriode($planNo);
			$ProductCount = count($dtProducts);
			$PeriodCount = count($dtPeriods);
			// die(json_encode($planDT));
			$ProductPerTable = floor(6/$PeriodCount);
			// die((string)$ProductPerTable);

			$header = "Diinput Oleh: ".$planHD->CreatedBy."<br>";
			$TGLINPUT = (($planHD->ModifiedDate==null)? $planHD->CreatedDate : $planHD->ModifiedDate);
			$header.= "Waktu Request:".date("Y-m-d H:i:s", strtotime($TGLINPUT))."<br>";
			$header.= "<hr><br>";
			$header.= "Kode Rencana : <b>".$planNo."</b><br>";
			$header.= "Divisi: <b>".$planHD->Division."</b><br>";
			$header.= "Periode: <b>".$planHD->Periode1." s/d ".$planHD->Periode2."</b><br>";
			$header.= "Keterangan: <b>".(($planHD->PlanNote=="")?"-":$planHD->PlanNote)."</b><br>";
			$header.= "Total Barang: <b>".$ProductCount."</b><br>";
			$header.= "<br>";

			
			$body = "<h3>Detail Barang</h3>";
			$body.= "<style>";
			$body.= "	th,td { border:1px solid #ccc; padding:3px; text-align:right; }";
			$body.= "	th { background-color:#ccc; }";
			$body.= "</style>";
			$tables = array();

			$counter = 0;
			$qty = 0;


			$products = array();
			$product = array();
			$ProductCount = 0; 

			$TotalQty = array();

			foreach($dtProducts as $p) {
				array_push($product, $p->ProductId);
				foreach($dtPeriods as $pd) {
					$TotalQty[$p->ProductId][$pd->PeriodId] = 0;
				}

				$ProductCount+=1;

				if ($ProductCount==$ProductPerTable || $ProductCount==count($dtProducts)) {
					array_push($products, $product);
					$ProductCount = 0;
					$product = array();
				}
			}

			// die(json_encode($products));

			for($i=0; $i<count($products); $i++) {

				$product = $products[$i];

				$body.="<table>";
				$body.="	<tr>";
				$body.="		<th width='20%' rowspan='2'>Wilayah</th>";
				for ($x=0; $x<count($product); $x++) {
					$body.="	<th width='20%' colspan='".($PeriodCount+1)."' style='text-align:center!important;'>".$product[$x]."</th>";
				}
				$body.="	</tr>";
				$body.="	<tr>";
				for ($x=0; $x<count($product); $x++) {
					$body.="	<th width='10%'>Avg Sales</th>";
					foreach($dtPeriods as $pd) {
						$body.="<th width='10%'>".$pd->PeriodName."</th>";
					}
				}
				$body.="	</tr>";


				foreach($dtRegions as $r) {
					$body.="<tr>";
					$body.="	<td>".$r->Region."</td>";
					for($x=0;$x<count($product);$x++) {
						$pd = $dtPeriods[0];
						foreach($planDT as $dt) {
							if ($dt->ProductId==$product[$x] && $dt->Region==$r->Region && $dt->PeriodId==$pd->PeriodId) {
								$qty = $dt->RQtyRegionTotal;
								break 1;
							}
						}
						$body.= "<td style='background-color:#e3ffbal;'>".number_format($qty)."</td>";

						foreach($dtPeriods as $pd) {
							foreach($planDT as $dt) {
								if ($dt->ProductId==$product[$x] && $dt->Region==$r->Region && $dt->PeriodId==$pd->PeriodId) {
									$qty = $dt->QtyRegionTotal;
									$TotalQty[$product[$x]][$pd->PeriodId] += $qty;
									break 1;
								}
							}
							$body.= "<td>".number_format($qty)."</td>";
						}
					}
					$body.="</tr>";
				}
				$body.="	<tr>";
				$body.="		<th width='20%'>Total</th>";
				for($x=0;$x<count($product);$x++) {
					$body.="	<th width='10%'></th>";
					foreach($dtPeriods as $pd) {
						$body.="<th width='10%'>".number_format($TotalQty[$product[$x]][$pd->PeriodId])."</th>";
					}
				}
				$body.="	</tr>";
				$body.="</table>";
				$body.="<div style='height:25px;'></div>";
			}

			return $header.$body;
		}

		function ApprovedPlanUnprocessed()
		{
			//Function ini dipanggil oleh Job Windows
			$Plans = $this->PlanPOModel->GetListApprovedUnprocessed();
			// die(json_encode($Plans));

			foreach ($Plans as $p) {
				$result = $this->PlanPOModel->SendApprovedPlanToBhakti($p->PlanNo);
				$p->Result = $result;
			}			
			echo(json_encode($Plans));
		}


	}
