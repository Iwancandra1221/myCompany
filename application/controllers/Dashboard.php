<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require 'vendor/autoload.php';
require 'vendor/setasign/fpdi/src/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class Dashboard extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('MsDealerModel');
		$this->load->model('AnnouncementModel');
		$this->load->model("approvalmodel");
		$this->load->model("shopboardmodel");
		require_once(dirname(__FILE__)."/approval.php"); // the controller route.
		$this->approval = new approval();
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}
	function _postRequest($url,$data,$isJson = false){
		//echo $url.'<br>';
		$ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POST, 1);

	    curl_setopt($ch, CURLOPT_ENCODING, '');
	    
	    if ($isJson) {
	        // Jika data adalah JSON, encode ke JSON dan atur header
	        $strJson = json_encode($data);
	        //echo $strJson;
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $strJson);
	        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

	    } else {
	        // Jika data adalah form data, atur payload dengan http_build_query
	        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	    }

	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	    $result = curl_exec($ch);
	    // $result = @gzdecode($result);
	    if (curl_errno($ch)) {
	        echo 'Curl error: ' . curl_error($ch);
	    }

	    curl_close($ch);

	    return $result;
	}
	public function checkImageFile($filename){
		$isImage = false;
		$exp = explode('.',$filename);
		if(count($exp)>1){
			$ext = strtolower($exp[count($exp)-1]);
			if($ext=='jpg' || $ext=='png' || $ext=='jpeg'){
				$isImage = true;
			}
		}
		$result = '';
		$url = base_url().'upload/attachment/announcement/'.$filename;
		if($isImage){
			$result = <<<HTML
			<div style="width: 200px;height: 150px; display: inline-block;"  href="">
				<a class="image-popup" href="{$url}">
					<img src="{$url}" style="width:100%;height:100%;object-fit: contain;" >
				</a>
				</div>
HTML;
		}
		else{
			if($filename!=''){
				$result = <<<HTML
				<a href="{$url}" target="_blank">{$filename}</a>
HTML;
			}

		}
		return $result;
	}


	public function index()
	{

		$data = array();

		$wAnnouncement = array(
			'is_active' => 1,
			'start_published_date <=' => date('Y-m-d'),
			'end_published_date >=' => date('Y-m-d'),

		);
		$fAnnouncement = 'array';
		$oAnnouncement = null;
		// $getPengumuman = $this->AnnouncementModel->getTransAnnouncement($wAnnouncement, $oAnnouncement, $fAnnouncement);
		$getPengumuman = null;
		$Pengumuman = array();
		if($getPengumuman!=null){
			foreach($getPengumuman as $key => $value){
				$value['attachment_1'] = $this->checkImageFile($value['attachment_1']);
				$value['attachment_2'] = $this->checkImageFile($value['attachment_2']);
				$value['attachment_3'] = $this->checkImageFile($value['attachment_3']);
				$Pengumuman[] = $value;
			}
		}


       	if (isset($_SESSION['role'])) {
			$mod = $this->ModuleModel->getListByRole($_SESSION['role']);
	       	$_SESSION['module'] = $mod;
	    } else {
	      	redirect('MainController');
	    }


		$_POST = array();
		$_POST ['ApprovalType'] = '';
		$_POST ['ApprovedBy'] = $_SESSION["logged_in"]["userid"];
		$_POST ['ApprovedByEmail'] = $_SESSION["logged_in"]["useremail"];
		$PendingCL = $this->approvalmodel->getpendingrequestcount($_POST);

		$data["Pending"] = $PendingCL;
		// $data["RequestPending"] = $PendingCL;
		$data["Pengumuman"] = $Pengumuman;
		
		$data["shopboardapproval"] = 0;
		if(($_SESSION['logged_in']['isSalesman']==1) && ($_SESSION['logged_in']['userLevel']=='ASS. MANAGER' || $_SESSION['logged_in']['userLevel']=='KABAG')){
			$data["shopboardapproval"] = $this->shopboardmodel->shopboard_approval_count();
		}

		$this->RenderView('DashboardViewV2',$data);
	}

	function test(){
		$_POST = array();
		$_POST ['ApprovalType'] = 'CAMPAIGN PLAN';
		$_POST ['ApprovedBy'] = $_SESSION["logged_in"]["userid"];
		$_POST ['ApprovedByEmail'] = $_SESSION["logged_in"]["useremail"];
		$PendingCL = $this->approval->getpendingrequests();
		die($PendingCL);
	}


	function ViewReqApproval(){	
		$data['ReqApproval']=$_SESSION['ReqApproval'];		
		$this->RenderView('ReqApprovalListView',$data);		
	}

	function sync_data(){
		if ($_SESSION["logged_in"]["branch_id"]=="JKT" && $_SESSION["logged_in"]["city"]=="JAKARTA") { 
			$URL = $this->API_URL."/DataSyncReceiver/GetLastSyncDataCabang?api=APITES";
			
			$LastSync = json_decode(@$this->_postRequest($URL,[]), true);
			$refresh = 0;
			if (!is_array($LastSync)) {
				$refresh = 1;
				$LastSync = array();
			}
				if($refresh==1){
					$linkRefresh = base_url().'Dashboard';
					echo <<<HTML
					<p style="text-align:center;">Terjadi Masalah dalam penarikan Data Cabang <a href="{$linkRefresh}">Refresh</a></p>
					<br><br>
HTML;
				}
				if (isset($LastSync)) {
?>	
					<h4>SYNC DATA CABANG</h4>
					<table id="tbl-sync" class="table table-bordered" style="width: 500px;">
						<tr>
							<th id="th-sync-no" width="50px">No</th>
							<th id="th-sync-cabang" width="*">Cabang</th>
							<th id="th-sync-time" width="200px">Last Success Pool Time</th>
						</tr>
						<?php
							$no=0;
							foreach($LastSync as $row){
								$no++;
							?>
							<?php if ($row["HourDifference"]>24) { ?>
							<td style='color:red;'><?php echo $no ?></td>
							<td style='color:red;'><?php echo $row['Nm_Lokasi'] ?></td>
							<td style='color:red;'><?php echo $row['LastSuccessPoolTime'] ?></td>
							<?php } else { ?>
							<td style='color:black;'><?php echo $no ?></td>
							<td style='color:black;'><?php echo $row['Nm_Lokasi'] ?></td>
							<td style='color:black;'><?php echo $row['LastSuccessPoolTime'] ?></td>
							<?php } ?>
						</tr>
					<?php
						}
					}
					?>
					</table>
	<?php } 	
	}


	function pending_request(){
		$_POST = array();
		$_POST ['ApprovalType'] = '';
		$_POST ['ApprovedBy'] = $_SESSION["logged_in"]["userid"];
		$_POST ['ApprovedByEmail'] = $_SESSION["logged_in"]["useremail"];
		$PendingCL = $this->approvalmodel->getpendingrequests($_POST);
		if(count($PendingCL)>0){
		$html = '';
		$html .='<div>
		<table id="tblrequests" class="table table-bordered" style="width: 700px;">
		<tr>
		<th id="th-requests-no" width="50px">NO</th>
		<th id="th-requests-jenis" width="500px">JENIS REQUEST</th>
		<th id="th-requests-request" width="500px">REQUEST</th>
		<th id="th-requests-info" width="500px">INFO</th>
		<th id="th-requests-proses" width="100px">PROSES</th>
		</tr>';
		
		$no=0;
		foreach($PendingCL as $row){
			$no++;
			
			$html .="<td>".$no."</td>";
			$html .= "<td>".$row->RequestType."</td>";
			
			if($row->RequestType=='TARGET SPG'){  
				$html .="<td>No: <b>  (".$row->RequestNo.") </b><br>";
				$html .="Nama: <b>  (".$row->Divisi.") </b><br>";
				$html .="Tgl Request: <b> ". date("d-M-Y H:i:s", strtotime($row->RequestDate))." </b><br>";
				$html .="Direquest Oleh: <b>  (".$row->RequestByName.") </b>";
				$html .="</td>";
				} else if($row->RequestType!='TARGET SPG'){  
				$html .="<td>No: <b>  ($row->RequestNo) </b><br>";
				$html .="Divisi: <b>  ($row->Divisi) </b><br>";
				$html .="Tgl Request: <b>  ".date("d-M-Y H:i:s", strtotime($row->RequestDate))." </b><br>";
				$html .="Direquest Oleh: <b>  (".$row->RequestByName.") </b>";
				$html .="</td>";
			} 
			$html .="<td>";
				if($row->RequestType=='CREDIT LIMIT'){
					$html .="Nama: <b> ". $row->NamaPelanggan." </b><br>Wilayah: <b> ".  $row->Wilayah." </b>"; 
				}else if($row->RequestType=='UNLOCK TOKO'){
					$html .="Nama: <b> ". $row->NamaPelanggan." </b><br>Wilayah: <b> ".  $row->Wilayah." </b>"; 
				}else if($row->RequestType=='TARGET SPG'){
					$html .="Periode: <b> ". $row->NamaPelanggan." </b><br>Wilayah: <b> ".  $row->Wilayah." </b>"; 
				}else if($row->RequestType=='TUNJANGAN PRESTASI SPG'){
					$html .="Periode: <b> ". $row->NamaPelanggan." </b><br>Wilayah: <b> ". $row->Wilayah." </b>"; 
				}else if($row->RequestType=='PLAN PO'){
					$html .="Keterangan: <b> ". $row->Catatan." </b>"; 
				}else if($row->RequestType=='CAMPAIGN PLAN'){
					$html .="Nama: <b> ".$row->NamaPelanggan." </b>"; 
				}else if($row->RequestType=='TARGET KPI KARYAWAN'){
					$html .="Periode: <b>".$row->NamaPelanggan." </b><br>Wilayah: <b>".$row->Wilayah."</b>"; 
				}else if($row->RequestType=='TARGET KPI'){
					$html .="Kategori KPI: <b> ". $row->KodePelanggan." </b><br>Periode: <b> ". $row->NamaPelanggan." </b><br>Wilayah: <b>  $row->Wilayah </b>"; 
				}else if($row->RequestType=='ACHIEVEMENT KPI KARYAWAN'){
					$html .="Periode: <b> ".$row->NamaPelanggan." </b><br>Wilayah: <b> ". $row->Wilayah." </b>"; 
				}else if($row->RequestType=='ACHIEVEMENT KPI'){
					$html .="Kategori KPI: <b> ".$row->KodePelanggan." </b><br>Periode: <b> ". $row->NamaPelanggan." </b><br>Wilayah: <b>".$row->Wilayah." </b>"; 
				}
			$html .="</td>";
			$url_data="'".$row->Url."','".$row->RequestNo."'";
			$html .='<td><a href="#" onclick="oper_link('.$url_data.')"><i class="glyphicon glyphicon-eye-open"></a></td>';
			$html .="</tr>";
		}
		$html .='</table></div>';
		}
		else{
			$html = 'Tidak ada data';
		}
		echo $html;
	}




	function achievement_kpi_dashboard()
	{
		$param['api'] = 'APITES';
		$param['userid'] = $this->input->post('userid');
		$param['tahun'] = $this->input->post('tahun');
		$param['bulan'] = $this->input->post('bulan');	
		
		$URL = $this->API_URL."/Achievementkpisalesman/achievement_kpi_dashboard";
		// echo $URL; die;
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($param),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		echo($response);
	}
	
	


	function achievement_kpi()
	{
		$param = $_GET;
		$URL = $this->API_URL."/Achievementkpisalesman/datatable_dashboard";
		// echo $URL; die;
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($param),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		echo($response);
	}
	
	
}

?>