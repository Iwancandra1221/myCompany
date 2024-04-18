<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TargetKaryawanApproval extends NS_Controller 
{
	public function __construct()
	{
		parent::__construct();	
		$this->load->model("TargetKaryawanModel");
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function TestConnection()
	{

		$data = array("result"=> "sukses",
					  "jam" => (int)date("H"));
		echo json_encode($data);
		// header('HTTP/1.1: 200');
		// header('Status: 200');
		// header('Content-Length: '.strlen($hasil));
		// exit($hasil);
	} 

	public function ProsesTargetKPI()
	{
		$noRequest = urldecode($this->input->get("req"));
		$approvedBy = urldecode($this->input->get("app"));
		$totalWeek = urldecode($this->input->get("week"));
		$this->ViewRequestTargetKPI($noRequest, $approvedBy, $totalWeek);
	}

	public function ViewRequestTargetKPI($noRequest, $approvedBy, $totalWeek, $msg="") 
	{
		$URL = $this->API_URL."/TargetKaryawan/AmbilTargetKPI?req=".urlencode($noRequest)."&app=".urlencode($approvedBy);
		// echo($URL); die;
		$GetRequest = json_decode(file_get_contents($URL), true);

		if($GetRequest["result"]=="SUCCESS") {

			$req = $GetRequest["data"];

			$style = '<style>
				*{
					font-family:Arial, sans-serif;
					font-size:14px;
				}
				table{
					border-collapse:collapse;
				}
				table th, table td{
					border:1px solid #ddd;
					text-align:left;
					padding:5px;
				}
				table tr:hover {
					/*background:#f8f8f8;*/
				}
				table tr:nth-child(even) {
					background:#f8f8f8;
				}
			</style>';
			
			$BL = $req["Bulan"];
			$NM_BL = array("", "JANUARI", "FEBRUARI", "MARET", "APRIL", "MEI", "JUNI", "JULI", "AGUSTUS", "SEPTEMBER", "OKTOBER", "NOVEMBER", "DESEMBER");

			$header = "<h2>REQUEST APPROVAL TARGET KPI</h2><hr><br>";
			$header.= "No Request: <b>".$req["NoRequestKPI"]."</b><br>";
			$header.= "Tgl Request: <b>".date("d-M-Y H:i:s", strtotime($req["RequestSentDate"]))."</b><br>";
			$header.= "Dikirimkan Oleh: <b style='background:yellow;'>".$req["RequestSentBy"]."</b><br><hr><br>";
			$header.= "Cabang: <b style='background:yellow;'>".$req["Cabang"]."</b><br>";
			$header.= "Periode: <b style='background:yellow;'>".$NM_BL[$BL]." ".$req["Tahun"]."</b><br>";
			$header.= "Banyak Karyawan: <b>".count($req["ListTargetKPI"])."</b><br><br>";

			$detail = "";
			$detailhd = "";
			$detaildt = "";
			$details = $req["ListTargetKPI"];
			// echo(json_encode($details)."<br>");

			$No = 0;

			$detailhd.= "<table>";
			$detailhd.= "<tr>";
			$detailhd.= "   <th width='5%''>No</th>";
			$detailhd.= "   <th width='15%'>Karyawan</th>";
			$detailhd.= "   <th width='8%'>Periode</th>";
			$detailhd.= "   <th width='20%'>Key Performance Indicator</th>";
			$detailhd.= "   <th width='20%'>Deskripsi</th>";
			$detailhd.= "   <th width='5%'>Week 1</th>";
			$detailhd.= "   <th width='5%'>Week 2</th>";
			$detailhd.= "   <th width='5%'>Week 3</th>";
			$detailhd.= "   <th width='5%'>Week 4</th>";
			if ($totalWeek>=5) $detailhd.= "   <th width='5%'>Week5</th>";
			if ($totalWeek==6) $detailhd.= "   <th width='5%'>Week6</th>";
			$detailhd.= "   <th width='5%'>Total Target</th>";
			$detailhd.= "   <th width='5%'>Bobot</th>";
			$detailhd.= "   <th width='5%'>TotalBobot</th>";
			$detailhd.= "   <th width='10%'></th>";
			$detailhd.= "   <th width='27%'>History/Status</th>";
			$detailhd.= "</tr>";

			$TotalWaiting = 0;
			$bg = "#ccf2ff";

			for($i=0; $i<count($details); $i++) {
				$No+= 1;
				$TotalBobot = 0;
				$dt = $details[$i];
				if ($bg=="#b3dae8") {
					$bg = "#ccf2ff";
				} else {
					$bg = "#b3dae8";
				}
				$detaildt = "";
				
				$ApprovalHistory = $dt["APPROVALHISTORY"];
				$l = count($ApprovalHistory);
				
				$HISTORY = "";
				if ($l>0) {
					for($j=0; $j<$l; $j++) {
						$HistoryStatus = $ApprovalHistory[$j]["HistoryStatus"];
						$HistoryDate = date("d-M-Y H:i:s", strtotime($ApprovalHistory[$j]["HistoryDate"]));
						$UserName = $ApprovalHistory[$j]["UserName"];
						$HistoryNote = (($ApprovalHistory[$j]["HistoryNote"]==null)?"":"[".$ApprovalHistory[$j]["HistoryNote"]."]");
						$HISTORY.= $HistoryDate." - ".$UserName." - ".$HistoryStatus." ".$HistoryNote."<br>"; 
					}
				} else {
					$HISTORY = "-";
				}

				$KPIs = $dt["DETAILS"];     
				$k = count($KPIs);

				for($j=1; $j<$k;$j++) {
					$detaildt.= "<tr style='background:".$bg.";'>";
					$detaildt.= "   <td>".$KPIs[$j]["KPIName"]."</td>";
					$detaildt.= "   <td>".$KPIs[$j]["KPINote"]."</td>";
				
					$detaildt.= "   <td>".number_format($KPIs[0]["TargetWeek1"],2)."</td>";
					$detaildt.= "   <td>".number_format($KPIs[0]["TargetWeek2"],2)."</td>";
					$detaildt.= "   <td>".number_format($KPIs[0]["TargetWeek3"],2)."</td>";
					$detaildt.= "   <td>".number_format($KPIs[0]["TargetWeek4"],2)."</td>";
					
					if ($totalWeek>=5) $detaildt.= "   <td>".number_format($KPIs[0]["TargetWeek5"],2)."</td>";
					if ($totalWeek==6) $detaildt.= "   <td>".number_format($KPIs[0]["TargetWeek6"],2)."</td>";
					$detaildt.= "   <td>".number_format($KPIs[$j]["KPITarget"],2)."</td>";
					$detaildt.= "   <td>".number_format($KPIs[$j]["KPIBobot"],2)."</td>";
					$detaildt.= "</tr>";
					$TotalBobot += $KPIs[$j]["KPIBobot"];
				}

				$TotalBobot += $KPIs[0]["KPIBobot"];


				$detailhd.="<tr style='background:".$bg.";'>";
				$detailhd.="    <td rowspan='".$k."'>".$No."</td>";
				$detailhd.="    <td rowspan='".$k."'>".$dt["NAMA"]."</td>";                
				$detailhd.="    <td rowspan='".$k."'>".$dt["BULAN"]."/".$dt["TAHUN"]."</td>";
				$detailhd.= "   <td>".$KPIs[0]["KPIName"]."</td>";
				$detailhd.= "   <td>".$KPIs[0]["KPINote"]."</td>";
				
				$detailhd.= "   <td>".number_format($KPIs[0]["TargetWeek1"],2)."</td>";
				$detailhd.= "   <td>".number_format($KPIs[0]["TargetWeek2"],2)."</td>";
				$detailhd.= "   <td>".number_format($KPIs[0]["TargetWeek3"],2)."</td>";
				$detailhd.= "   <td>".number_format($KPIs[0]["TargetWeek4"],2)."</td>";
				
				if ($totalWeek>=5) $detailhd.= "   <td>".number_format($KPIs[0]["TargetWeek5"],2)."</td>";
				if ($totalWeek==6) $detailhd.= "   <td>".number_format($KPIs[0]["TargetWeek6"],2)."</td>";
				
				$detailhd.= "   <td>".number_format($KPIs[0]["KPITarget"],2)."</td>";
				$detailhd.= "   <td>".number_format($KPIs[0]["KPIBobot"],2)."</td>";
				$detailhd.="    <td rowspan='".$k."'>".number_format($TotalBobot, 0)."</td>";
				if ($dt["STATUS"]=="WAITING FOR APPROVAL") {
					$TotalWaiting += 1;
					$detailhd .= "<td rowspan='".$k."'><input type='checkbox' class='cek_pilih' name='karyawan[]' value='".$dt["KODE_TARGET"]."' onchange='cek()' checked></td>";
				} else if ($dt["STATUS"]=="CANCELLED") {
					$detailhd .= "<td rowspan='".$k."'>CANCELLED</td>";    
				} else if ($dt["STATUS"]=="CLOSED") {
					$detailhd .= "<td rowspan='".$k."'>CLOSED</td>"; 
				} else if ($dt["STATUS"]=="REJECTED") {
					$detailhd .= "<td rowspan='".$k."'>REJECTED</td>"; 
				} else if ($dt["STATUS"]=="APPROVED") {
					$detailhd .= "<td rowspan='".$k."'>APPROVED</td>";
				} else {
					$detailhd .= "<td rowspan='".$k."'></td>";
				}
				$detailhd.="    <td rowspan='".$k."'>".$HISTORY."</td>";
				$detailhd.="</tr>";         
				$detailhd.=$detaildt; 
			}
			$detailhd.="</table>";
			
			$detail = "
			<form action='./ApproveReject' method='POST'>
			<div style='padding:10px;background:#FFF;border:1px solid #f5c6cb;'>".$detailhd."
			<input type='hidden' name='no_request' value='".$noRequest."'>
			<input type='hidden' name='app_by' value='".$approvedBy."'>
			<input type='hidden' name='req_json' value='".json_encode($req)."'>
			<br>";


			if ($TotalWaiting>0) {
				$detail.= "<span style='float:right'><em>(jika tidak pilih = REJECT)</em> Pilih Semua <input type='checkbox' id='cbx_all' onchange='cek_all()' checked></span>
					<br>
					REJECT NOTE (wajib diisi jika reject)<br>
					<input type='input' name='rejectnote' id='rejectnote' style='width:100%;padding:5px;margin-bottom:10px' disabled>
					<center><input type='submit' id='btn_submit' value='APPROVE' style='color:#fff; padding:10px; background:green; border:1px solid #555; text-align:center;' ></center>
					</div>";
				
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
			} else if ($msg!="") {
				echo $msg;
			} else {
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>REQUEST SUDAH DIPROSES!</h2></div>";
			}
			$detail.= "</form>";

			echo ($style);
			echo ($header);
			echo ($detail);
		}
	}

	public function ApproveReject() {

		$msg = "";
		$data = $this->PopulatePost();
		
		//APPROVE
		if(ISSET($data['karyawan'])){
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $this->API_URL."/TargetKaryawan/ApproveTargetKPI",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => http_build_query($data),
			));
			
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// die($result);
			$result = json_decode($result, true);

			if ($result["result"]=="SUCCESS") {     

				$msg = "
				<div style='padding:10px;background:#d4edda;border:1px solid #c3e6cb'>
				<center><h2>REQUEST TARGET KPI BERHASIL DIAPPROVE</h2></center>
				</div>";
				//reegan
				foreach($data['karyawan'] as $kodeTarget){
					//reegan approve
					$dApproval = array(
						'ApprovalStatus' => 'APPROVED',
						'ApprovedDate' => date('Y-m-d H:i:s'),
					);
					$wApproval = array(
						'RequestNo' => $kodeTarget,
						'ApprovalStatus' => 'UNPROCESSED',
					);
					// $resultEdit = $this->TargetKaryawanModel->editTblApproval($wApproval,$dApproval);
				}
			}
			else {
				$msg = "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><center><h2>".$result["error"]."</h2></center></div>";               
			}      
		}
		
		//REJECT
		else{
			// die("reject");
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $this->API_URL."/TargetKaryawan/RejectTargetKPI",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => http_build_query($data),
			));
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $result; die;
			$result = json_decode($result, true);
			if ($result["result"]=="SUCCESS") {
				$msg = "<div style='padding:10px;background:#d4edda;border:1px solid #f5c6cb;'><h2><center>REQUEST TARGET KPI BERHASIL DIREJECT</center></h2></div>";
				//reegan
				foreach($data['karyawan'] as $kodeTarget){
					//reegan approve
					$dApproval = array(
						'ApprovalStatus' => 'REJECTED',
					);
					$wApproval = array(
						'RequestNo' => $kodeTarget,
						'ApprovalStatus' => 'UNPROCESSED',
					);
					// $resultEdit = $this->TargetKaryawanModel->editTblApproval($wApproval,$dApproval);
				}
			}
			else { 
				$msg = "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2><center>".$result["error"]."</center></h2></div>";
			}
		}
		$this->ViewRequestTargetKPI($data["no_request"], $data["app_by"], $msg);
	}

	public function ProsesAchievementKPI()
	{
		$noRequest = urldecode($this->input->get("req"));
		$approvedBy = urldecode($this->input->get("app"));
		$totalWeek = urldecode($this->input->get("week"));
		$this->ViewRequestAchievementKPI($noRequest, $approvedBy, $totalWeek);
	}

	public function ViewRequestAchievementKPI($noRequest, $approvedBy, $totalWeek, $msg="") 
	{
		$URL = $this->API_URL."/TargetKaryawan/AmbilAchievementKPI?req=".urlencode($noRequest)."&app=".urldecode($approvedBy);
		// echo($URL);die;
		$GetRequest = json_decode(file_get_contents($URL), true);
		// echo(json_encode($GetRequest));die;

		if($GetRequest["result"]=="SUCCESS") {

			$req = $GetRequest["data"];

			$style = '<style>
				*{
					font-family:Arial, sans-serif;
					font-size:14px;
				}
				table{
					border-collapse:collapse;
				}
				table th, table td{
					border:1px solid #ddd;
					padding:5px;
				}
				table th {
					text-align:center;
				}
				table tr:hover {
					/*background:#f8f8f8;*/
				}
				table tr:nth-child(even) {
					background:#f8f8f8;
				}
				.target { background:#edf1f2; }
				.achievement { background:#ccf2ff; }
				.final { background:#b3dae8; }
				.modified { font-size:8pt;}
				.closed { background:#faacc9; }
			</style>';
			
			$BL = $req["Bulan"];
			$NM_BL = array("", "JANUARI", "FEBRUARI", "MARET", "APRIL", "MEI", "JUNI", "JULI", "AGUSTUS", "SEPTEMBER", "OKTOBER", "NOVEMBER", "DESEMBER");

			$header = "<h2>REQUEST APPROVAL ACHIEVEMENT KPI</h2><hr>";
			$header.= "No Request: <b>".$req["NoRequestKPI"]."</b><br>";
			$header.= "Tgl Request: <b>".date("d-M-Y H:i:s", strtotime($req["RequestSentDate"]))."</b><br>";
			$header.= "Dikirimkan Oleh: <b style='background:yellow;'>".$req["RequestSentBy"]."</b><br><hr><br>";
			$header.= "Cabang: <b style='background:yellow;'>".$req["Cabang"]."</b><br>";
			$header.= "Periode: <b style='background-color:yellow;'>".$NM_BL[$BL]." ".$req["Tahun"]."</b><br>";  
			$header.= "Banyak Karyawan: <b>".count($req["ListKPI"])."</b><br><br>";

			$detail = "";
			$detailhd = "";
			$detaildt = "";
			$details = $req["ListKPI"];

			$No = 0;

			$detailhd.= "<table>";
			$detailhd.= "<tr>";
			$detailhd.= "   <th width='4%''>No</th>";
			$detailhd.= "   <th width='10%'>Karyawan</th>";
			$detailhd.= "   <th width='12%'>Key Performance Indicator</th>";
			$detailhd.= "   <th width='8%'>Total Target</th>";
			$detailhd.= "   <th width='4%'>%Bobot</th>";
			$detailhd.= "   <th width='8%'>Week1</th>";
			$detailhd.= "   <th width='8%'>Week2</th>";
			$detailhd.= "   <th width='8%'>Week3</th>";
			$detailhd.= "   <th width='8%'>Week4</th>";
			if ($totalWeek>=5) $detailhd.= "   <th width='8%'>Week5</th>";
			if ($totalWeek==6) $detailhd.= "   <th width='8%'>Week6</th>";
			$detailhd.= "   <th width='8%'>TotalAcv</th>";
			$detailhd.= "   <th width='4%'>%</th>";
			$detailhd.= "   <th width='4%'>%Bobot</th>";
			$detailhd.= "   <th width='4%'>%</th>";
			$detailhd.= "   <th width='2%'></th>";
			$detailhd.= "</tr>";

			$TotalWaiting = 0;

			for($i=0; $i<count($details); $i++) {
				$No+= 1;
				$TotalBobot = 0;
				$dt = $details[$i];
				$detaildt = "";
				$ApprovalHistory = $dt["APPROVALHISTORY"];
				$l = count($ApprovalHistory);
				$HISTORY = "";
				if ($l>0) {
					for($j=0; $j<$l; $j++) {
						$HistoryStatus = $ApprovalHistory[$j]["HistoryStatus"];
						$HistoryDate = date("d-M-Y H:i:s", strtotime($ApprovalHistory[$j]["HistoryDate"]));
						$UserName = $ApprovalHistory[$j]["UserName"];
						$HistoryNote = (($ApprovalHistory[$j]["HistoryNote"]==null)?"":$ApprovalHistory[$j]["HistoryNote"]);
						$HISTORY.= $HistoryDate." - ".$UserName." - ".$HistoryStatus."[".$HistoryNote."]<br>"; 
					}
				} else {
					$HISTORY = "-";
				}

				$KPIs = $dt["DETAILS"];
				$k = count($KPIs);
				
				for($j=1; $j<$k;$j++) {
					$detaildt.= "<tr>";
					$detaildt.= "   <td class='target'>".$KPIs[$j]["KPIName"]."</td>";
					$detaildt.= "   <td align='right' class='target'>".number_format($KPIs[$j]["KPITarget"],2)."</td>";
					$detaildt.= "   <td align='right' class='target'>".number_format($KPIs[$j]["KPIBobot"],0)."</td>";
					$detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek1"],2).
									"<br><span class='modified'>/".number_format($KPIs[$j]["TargetWeek1"],2)."<br>".number_format($KPIs[$j]["PersenWeek1"],2)."%</span></td>";
					$detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek2"],2).
									"<br><span class='modified'>/".number_format($KPIs[$j]["TargetWeek2"],2)."<br>".number_format($KPIs[$j]["PersenWeek2"],2)."%</span></td>";
					$detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek3"],2).
									"<br><span class='modified'>/".number_format($KPIs[$j]["TargetWeek3"],2)."<br>".number_format($KPIs[$j]["PersenWeek3"],2)."%</span></td>";
					$detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek4"],2).
									"<br><span class='modified'>/".number_format($KPIs[$j]["TargetWeek4"],2)."<br>".number_format($KPIs[$j]["PersenWeek4"],2)."%</span></td>";
					if ($totalWeek>=5) $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek5"],2).
									"<br><span class='modified'>/".number_format($KPIs[$j]["TargetWeek5"],2)."<br>".number_format($KPIs[$j]["PersenWeek5"],2)."%</span></td>";
					if ($totalWeek==6) $detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvWeek6"],2).
									"<br><span class='modified'>/".number_format($KPIs[$j]["TargetWeek6"],2)."<br>".number_format($KPIs[$j]["PersenWeek6"],2)."%</span></td>";
					$detaildt.= "   <td align='right' class='achievement'><b>".number_format($KPIs[$j]["AcvTotal"],2)."</b></td>";
					$detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvPersen"],2)."</td>";
					$detaildt.= "   <td align='right' class='achievement'>".number_format($KPIs[$j]["AcvBobot"],2)."</td>";
					$detaildt.= "</tr>";
				}
				
				$k= ($k==0) ? 1 : $k;
				
				$detailhd.="<tr>";
				$detailhd.="    <td rowspan='".$k."' class='target'>".$No."</td>";
				$detailhd.="    <td rowspan='".$k."' class='target'>".$dt["NAMA"];   
				if($dt["EXCLUDETUNJANGANPRESTASI"]=='1'){
					$detailhd.="<br><span class='modified' style='color:red'>(Exclude Tunjangan Prestasi)</span>";
				}
				$detailhd.="    </td>";    

				if ($dt["REQUESTSTATUS"]=="CANCELLED") {
					if ($totalWeek>=5) {
						$detailhd.= "   <td colspan='11' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CANCELLEDBY"]." ".$dt["CANCELLEDDATE"]." ".$dt["CANCELLEDNOTE"]."</td>";
					} else if ($totalWeek==6) {
						$detailhd.= "   <td colspan='12' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CANCELLEDBY"]." ".$dt["CANCELLEDDATE"]." ".$dt["CANCELLEDNOTE"]."</td>";
					} else {                        
						$detailhd.= "   <td colspan='10' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CANCELLEDBY"]." ".$dt["CANCELLEDDATE"]." ".$dt["CANCELLEDNOTE"]."</td>";
					}
					$detailhd.="    <td align='right' class='final' rowspan='".$k."'><b></b></td>";
				} else if ($dt["REQUESTSTATUS"]=="CLOSED") {
					if ($totalWeek>=5) {
						$detailhd.= "   <td colspan='11' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CLOSEDBY"]." ".$dt["CLOSEDDATE"]." ".$dt["CLOSEDNOTE"]."</td>";
					} else if ($totalWeek==6) {
						$detailhd.= "   <td colspan='12' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CLOSEDBY"]." ".$dt["CLOSEDDATE"]." ".$dt["CLOSEDNOTE"]."</td>";
					} else {                        
						$detailhd.= "   <td colspan='10' align='center' class='closed'>Request Dibatalkan Oleh ".$dt["CLOSEDBY"]." ".$dt["CLOSEDDATE"]." ".$dt["CLOSEDNOTE"]."</td>";
					}
					$detailhd.="    <td align='right' class='final' rowspan='".$k."'><b></b></td>";
				} else {    
					$detailhd.= "   <td class='target'>".$KPIs[0]["KPIName"]."</td>";
					$detailhd.= "   <td align='right' class='target'>".number_format($KPIs[0]["KPITarget"],2)."</td>";
					$detailhd.= "   <td align='right' class='target'>".number_format($KPIs[0]["KPIBobot"],0)."</td>";
					// $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek1"],2)."<br><br><span class='modified'>".$KPIs[0]["Week1ModifiedBy"]."<br>".$KPIs[0]["Week1ModifiedDate"]."</span></td>";
					// $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek2"],2)."<br><br><span class='modified'>".$KPIs[0]["Week2ModifiedBy"]."<br>".$KPIs[0]["Week2ModifiedDate"]."</span></td>";
					// $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek3"],2)."<br><br><span class='modified'>".$KPIs[0]["Week3ModifiedBy"]."<br>".$KPIs[0]["Week3ModifiedDate"]."</span></td>";
					// $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek4"],2)."<br><br><span class='modified'>".$KPIs[0]["Week4ModifiedBy"]."<br>".$KPIs[0]["Week4ModifiedDate"]."</span></td>";
					// if ($totalWeek>=5) $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek5"],2)."<br><br><span class='modified'>".$KPIs[0]["Week5ModifiedBy"]."<br>".$KPIs[0]["Week5ModifiedDate"]."</span></td>";
					// if ($totalWeek==6) $detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek6"],2)."<br><br><span class='modified'>".$KPIs[0]["Week6ModifiedBy"]."<br>".$KPIs[0]["Week6ModifiedDate"]."</span></td>";
					$detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek1"],2).
									"<br><span class='modified'>/".number_format($KPIs[0]["TargetWeek1"],2)."<br>".number_format($KPIs[0]["PersenWeek1"],2)."%</span></td>";
					$detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek2"],2).
									"<br><span class='modified'>/".number_format($KPIs[0]["TargetWeek2"],2)."<br>".number_format($KPIs[0]["PersenWeek2"],2)."%</span></td>";
					$detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek3"],2).
									"<br><span class='modified'>/".number_format($KPIs[0]["TargetWeek3"],2)."<br>".number_format($KPIs[0]["PersenWeek3"],2)."%</span></td>";
					$detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek4"],2).
									"<br><span class='modified'>/".number_format($KPIs[0]["TargetWeek4"],2)."<br>".number_format($KPIs[0]["PersenWeek4"],2)."%</span></td>";
					if ($totalWeek>=5)
					$detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek5"],2).
									"<br><span class='modified'>/".number_format($KPIs[0]["TargetWeek5"],2)."<br>".number_format($KPIs[0]["PersenWeek5"],2)."%</span></td>";
					if ($totalWeek==6)
					$detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvWeek6"],2).
									"<br><span class='modified'>/".number_format($KPIs[0]["TargetWeek6"],2)."<br>".number_format($KPIs[0]["PersenWeek6"],2)."%</span></td>";
									
					$detailhd.= "   <td align='right' class='achievement'><b>".number_format($KPIs[0]["AcvTotal"],2)."</b></td>";
					$detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvPersen"],2)."</td>";
					$detailhd.= "   <td align='right' class='achievement'>".number_format($KPIs[0]["AcvBobot"],2)."</td>";
					$detailhd.="    <td align='right' class='final' rowspan='".$k."'><b>".number_format($dt["TOTAL_ACHIEVEMENT"], 0)."</b></td>";
				}
				if ($dt["STATUS"]=="WAITING FOR APPROVAL") {
					$TotalWaiting += 1;
					$detailhd .= "<td rowspan='".$k."'><input type='checkbox' class='cek_pilih' name='karyawan[]' value='".$dt["KODE_TARGET"]."' onchange='cek()' checked></td>";
				} else if ($dt["STATUS"]=="CANCELLED") {
					$detailhd .= "<td rowspan='".$k."'>CANCELLED</td>";    
				} else if ($dt["STATUS"]=="CLOSED") {
					$detailhd .= "<td rowspan='".$k."'>CLOSED</td>"; 
				} else if ($dt["STATUS"]=="REJECTED") {
					$detailhd .= "<td rowspan='".$k."'>REJECTED</td>"; 
				} else if ($dt["STATUS"]=="APPROVED") {
					$detailhd .= "<td rowspan='".$k."'>APPROVED</td>";
				} else {
					$detailhd .= "<td rowspan='".$k."'></td>";
				}
				// $detailhd.="    <td rowspan='".$k."'>".$HISTORY."</td>";
				$detailhd.="</tr>";         
				$detailhd.=$detaildt; 
			}
			$detailhd.="</table>";
			
			$detail = "
			<form action='./ApproveRejectAchievement' method='POST'>
			<div style='padding:10px;background:#FFF;border:1px solid #f5c6cb;'>".$detailhd."
			<input type='hidden' name='no_request' value='".$noRequest."'>
			<input type='hidden' name='app_by' value='".$approvedBy."'>
			<input type='hidden' name='total_week' value='".$totalWeek."'>
			<input type='hidden' name='req_json' value='".json_encode($req)."'>
			<br>";


			if ($TotalWaiting>0) {
				$detail.= "<span style='float:right'><em>(jika tidak pilih = REJECT)</em> Pilih Semua <input type='checkbox' id='cbx_all' onchange='cek_all()' checked></span>
					<br>
					REJECT NOTE (wajib diisi jika reject)<br>
					<input type='input' name='rejectnote' id='rejectnote' style='width:100%;padding:5px;margin-bottom:10px' disabled>
					<center><input type='submit' id='btn_submit' value='APPROVE' style='color:#fff; padding:10px; background:green; border:1px solid #555; text-align:center;' ></center>
					</div>";
				
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
			} else if ($msg!="") {
				echo $msg;
			} else {
				echo "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;text-align:center'><h2>REQUEST SUDAH DIPROSES!</h2></div>";
			}
			$detail.= "</form>";

			echo ($style);
			echo ($header);
			echo ($detail);
		}
	}

	public function ApproveRejectAchievement() 
	{

		$msg = "";
		$data = $this->PopulatePost();
		// echo json_encode($data['karyawan']); die;
		// echo $this->API_URL."/TargetKaryawan/ApproveAchievementKPI";
  //       echo "<br>";
  //       die(json_encode($data));
        
		//APPROVE
		if(ISSET($data['karyawan'])){
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $this->API_URL."/TargetKaryawan/ApproveAchievementKPI",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => http_build_query($data),
			));
			
			$result = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			
			$result = json_decode($result, true);
			
			// echo json_encode($result); die;

			if ($result["result"]=="SUCCESS") {
				$msg = "
				<div style='padding:10px;background:#d4edda;border:1px solid #c3e6cb'>
				<center><h2>REQUEST ACHIEVEMENT KPI BERHASIL DIAPPROVE</h2></center>
				</div>";
				//reegan
				foreach($data['karyawan'] as $kodeTarget){
					//reegan approve
					$dApproval = array(
						'ApprovalStatus' => 'APPROVED',
						'ApprovedDate' => date('Y-m-d H:i:s'),
					);
					$wApproval = array(
						'RequestNo' => $kodeTarget,
						'ApprovalStatus' => 'UNPROCESSED',
					);
					$resultEdit = $this->TargetKaryawanModel->editTblApproval($wApproval,$dApproval);
				}
			}
			else {
				$msg = "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><center><h2>".$result["error"]."</h2></center></div>";               
			}      
		}
		
		//REJECT
		else{
			$url = $this->API_URL."/TargetKaryawan/RejectAchievementKPI";

			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => http_build_query($data),
			));
			
			$result = curl_exec($curl);
			// echo $result;die;
			$err = curl_error($curl);
			curl_close($curl);
									
			$result = json_decode($result, true);

			if ($result["result"]=="SUCCESS") {
				$msg = "<div style='padding:10px;background:#d4edda;border:1px solid #f5c6cb;'><h2><center>REQUEST ACHIEVEMENT KPI BERHASIL DIREJECT</center></h2></div>";
				//reegan
				foreach($data['karyawan'] as $kodeTarget){
					//reegan approve
					$dApproval = array(
						'ApprovalStatus' => 'REJECTED',
					);
					$wApproval = array(
						'RequestNo' => $kodeTarget,
						'ApprovalStatus' => 'UNPROCESSED',
					);
					$resultEdit = $this->TargetKaryawanModel->editTblApproval($wApproval,$dApproval);
				}
			}
			else { 
				$msg = "<div style='padding:10px;background:#f8d7da;border:1px solid #f5c6cb;'><h2><center>".$result["error"]."</center></h2></div>";
			}
		}
		$this->ViewRequestAchievementKPI($data["no_request"], $data["app_by"], $data["total_week"], $msg);
	}

}
?>