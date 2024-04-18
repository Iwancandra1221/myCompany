<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class KPI extends MY_Controller {

	function __construct()
	{
		parent::__construct();  
		$this->load->model('JobsModel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function Salesman()
	{
		$checkAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2)); 
		$post['api'] = 'APITES';
		$post['jenis'] = 'SALESMAN';
		$post['trx'] = 'select';

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER KPI SALESMAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU MASTER KPI SALESMAN";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);

		// echo json_encode($post);die;
		// //------------------------------------------------
		$URL = $this->API_URL."/Masterkpi/MasterKPIList";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $response; die;
		
		if($httpcode!=200){
			$params['Remarks']="FAILED - URL ".$URL." sedang tidak bisa diakses! HTTP Code:".$httpcode;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			echo 'URL "'.$URL.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode; die;
		}
		else{
			$result = json_decode($response);

			if($result->result =='success'){
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				// $data['MasterKPI'] = $result->MasterKPI;
				$data['unit'] = $result->KPIUnit;
				$data['KPICategory'] = $result->KPICategory;
				$data['Divisi'] = $result->Divisi;
				$data['SellingDifficultyLevel'] = $result->SellingDifficultyLevel;
			}
			else{
				$params['Remarks']="FAILED - ".$result->error;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				echo $result->error; die;
			}
		}
		
		// $unit = array_column($data['MasterKPI'], 'KPIUnit');
		// $unit = array_unique($unit);
		// $unit = array_values($unit);
		// $data['unit'] = $unit;
		// echo json_encode($data);die;
		$this->RenderView("KPIView",$data);
	}


	public function ListKPI($trx='SALESMAN'){
		if($trx=='Salesman'){
			$trx='SALESMAN';
			$a='SALESMAN';
		}else{
			$trx='NON-SALESMAN';
			$a='KARYAWAN';
		}

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER KPI ".$a;
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU MASTER KPI ".$a;
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';

		$post['api'] = 'APITES';
		$post['jenis'] = $trx;
		$post['trx'] = 'list';
		$post['get'] = $this->input->get();

		$URL = $this->API_URL."/Masterkpi/MasterKPIList";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);


		if($httpcode!=200){

			$params['Remarks']="FAILED - URL ".$URL." sedang tidak bisa diakses! HTTP Code:".$httpcode;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			echo 'URL "'.$URL.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode; die;
	
		}else{

			$result = json_decode($response);
			// print_r($result);
			// die();
			if($result->result =='success'){
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				$data['MasterKPI'] = $result->MasterKPI;
				// print_r($data['MasterKPI']);
				// die();
				$data_list=array();

				if(!empty($data['MasterKPI'])){
					$listdata=json_decode(json_encode($data['MasterKPI']->data));

					foreach($listdata as $key => $d) {
						$checked = ($d->IsActive==1) ? "checked" : "";

						$tamp=array();
						$tamp[]=$d->KPIName;
						$tamp[]=$d->KPIDescription;
						$tamp[]=$d->KPICategoryName;
						$tamp[]=$d->KPIUnit;
						$tamp[]=$d->TargetPenjualan;
						$tamp[]=$d->Divisi;
						$tamp[]=$d->ItemFokus;

						if($_SESSION["can_update"] == 1) {
							$tamp[]='<input type="checkbox" '.$checked.' id="aktif_nonaktif" data-code="'.$d->KPICode.'" onclick="return true">';
						}else{
							$tamp[]='<input type="checkbox" '.$checked.' onclick="return false">';
						}

						$data_click = '';
						$data_click .=' data-code="'.$d->KPICode.'"';
						$data_click .=' data-name="'.$d->KPIName.'"';
						$data_click .=' data-desc="'.$d->KPIDescription.'"';
						$data_click .=' data-category="'.$d->KPICategory.'"';
						$data_click .=' data-unit="'.$d->KPIUnit.'"';
						$data_click .=' data-target="'.$d->TargetPenjualan.'"';
						$data_click .=' data-divisi="'.$d->Divisi.'"';
						$data_click .=' data-item="'.$d->ItemFokus.'"';
						$data_click .=' data-active="'.$d->IsActive.'"';
						$data_click .=' data-createdby="'.$d->CreatedBy.'"';
						$data_click .=' data-createddate="'.$d->CreatedDate.'"';
						$data_click .=' data-modifiedby="'.$d->ModifiedBy.'"';
						$data_click .=' data-modifieddate="'.$d->ModifiedDate.'"';
						
						$action='';
						if($_SESSION["can_update"] == 1) {
							$tamp[]= '<button class="btn-edit btn btn-dark" '.$data_click.'><i class="glyphicon glyphicon-pencil"></i></button>';
						}
						
						if($_SESSION["can_delete"] == 1) {
							$tamp[]= '<button class="btn-delete btn btn-danger-dark" '.$data_click.'><i class="glyphicon glyphicon-trash"></i></button>';
						} 

						$data_list[]=$tamp;
					}
					$total=$data['MasterKPI']->total;
				}else{
					$total=0;
				}


				if(!empty($this->input->get('sEcho'))){
					$secho = $this->input->get('sEcho');
				}else{
					$secho = 1;
				}


				$data_hasil['sEcho']=$secho;
				$data_hasil['iTotalRecords']=$total;
				$data_hasil['iTotalDisplayRecords']=$total;
				$data_hasil['aaData']=$data_list;

				print_r(json_encode($data_hasil));

			}else{

				$params['Remarks']="FAILED - ".$result->error;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				echo $result->error; die;

			}
		}


	}



	public function Karyawan()
	{
		$checkAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2)); 
		$post['api'] = 'APITES';
		$post['jenis'] = 'NON-SALESMAN';
		$post['trx'] = 'select';

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER KPI KARYAWAN";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU MASTER KPI KARYAWAN";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);

		//------------------------------------------------
		if(!ISSET($_SESSION['logged_in']['DivisionListUnderDivHead'])){
			// Ambil List Division user dan divisi di bawah nya
			$URL = API_ZEN."/Zenapi/DivisionListUnderDivHead/".$_SESSION['logged_in']['userid']; //debug pakai userid ci indah
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
			));
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);
			// echo $response; die;
			if($httpcode!=200){
				$params['Remarks']="FAILED - Web ZEN sedang tidak bisa diakses! HTTP Code:".$httpcode;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				echo 'Web ZEN sedang tidak bisa diakses! HTTP Code:'.$httpcode; die;
			}
			else{
				$result = json_decode($response);
				if($result->result =='sukses'){
					$params['Remarks']="SUCCESS";
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);
					// simpan  divisionid ke dalam session supaya tidak hit API ZEN setiap reload halaman
					$_SESSION['logged_in']['DivisionListUnderDivHead'] = $result->data;
				}
				else{
					$params['Remarks']="FAILED - Ambil List Divisi gagal. Error:".$result->error;
					$params['RemarksDate'] = date("Y-m-d H:i:s");
					$this->ActivityLogModel->update_activity($params);
					echo 'Ambil List Divisi gagal. Error: '.$result->error; die;
				}
			}
		}
		if(ISSET($_SESSION['logged_in']['DivisionListUnderDivHead'])){		
			//Ubah DivisionID menjadi array 1 dimensi
			$divisionid = array_column($_SESSION['logged_in']['DivisionListUnderDivHead'], 'DivisionID');
			$divisionid = array_values($divisionid);
			// echo json_encode($divisionid); die;
			$post['divisionid'] = $divisionid;
		}
		// echo json_encode($post);die;
		//------------------------------------------------
		$URL = $this->API_URL."/Masterkpi/MasterKPIList";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $response; die;
		
		if($httpcode!=200){
			$params['Remarks']="FAILED - URL ".$URL." sedang tidak bisa diakses! HTTP Code:".$httpcode;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			echo 'URL "'.$URL.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode; die;
		}
		else{
			$result = json_decode($response);

			if($result->result =='success'){
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				// $data['MasterKPI'] = $result->MasterKPI;
				$data['unit'] = $result->KPIUnit;
				$data['KPICategory'] = $result->KPICategory;
				$data['Divisi'] = $result->Divisi;
				$data['SellingDifficultyLevel'] = $result->SellingDifficultyLevel;
			}
			else{
				$params['Remarks']="FAILED - ".$result->error;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				echo $result->error; die;
			}
		}
		
		// $unit = array_column($data['MasterKPI'], 'KPIUnit');
		// $unit = array_unique($unit);
		// $unit = array_values($unit);
		// $data['unit'] = $unit;
		// echo json_encode($data);die;
		$this->RenderView("KPIView", $data);
	}

	public function Save()
	{
		// echo $this->input->post('Divisi');die;
		$post = $this->PopulatePost();
		// echo json_encode($post['Divisi']);die;
		$post['api'] = 'APITES';
		$post['user'] = $_SESSION['logged_in']['username'];
		
		$res['result']='failed';
		$res['error']='';

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER KPI";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." TAMBAH MASTER KPI";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);
		
		$URL = $this->API_URL."/Masterkpi/MasterKPISave";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => htmlspecialchars_decode(json_encode($post)),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		
		if($httpcode!=200){
			$params['Remarks']="FAILED - URL ".$URL." sedang tidak bisa diakses! HTTP Code:".$httpcode;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			$res['error'] = 'URL "'.$URL.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode;
		}
		else{
			if($response=='success'){
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				$res['result']=$response;
			}
			else{
				$params['Remarks']="FAILED - ".$URL;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				$res['error']=$response;
			}
		}
		echo json_encode($res);
	}

	public function Delete()
	{
		$KPICode = $this->input->get('KPICode');
		// echo json_encode($post);
		$post['api'] = 'APITES';
		$post['user'] = $_SESSION['logged_in']['username'];
		$post['KPICode'] = $KPICode;
		
		$res['result']='failed';
		$res['error']='';

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER KPI";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." HAPUS MASTER KPI";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);
		
		$URL = $this->API_URL."/Masterkpi/MasterKPIDelete";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		
		if($httpcode!=200){
			$params['Remarks']="FAILED - URL ".$URL." sedang tidak bisa diakses! HTTP Code:".$httpcode;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			$res['error'] = 'URL "'.$URL.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode;
		}
		else{
			if($response=='success'){
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				$res['result']=$response;
			}
			else{
				$params['Remarks']="FAILED - ".$URL;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				$res['error']=$response;
			}
		}
		echo json_encode($res);
	}
 
 	public function AktifNonaktif()
	{
		$KPICode = $this->input->get('KPICode');
		$Aktif = $this->input->get('Aktif');
		// echo json_encode($post);
		$post['api'] = 'APITES';
		$post['user'] = $_SESSION['logged_in']['username'];
		$post['KPICode'] = $KPICode;
		$post['Aktif'] = $Aktif;
		
		$res['result']='failed';
		$res['error']='';

		if($Aktif==1){
			$description = 'MENGAKTIFKAN';
		}else{
			$description = 'MENONAKTIFKAN';
		}

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER KPI";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." ".$description." MASTER KPI";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);
		
		$URL = $this->API_URL."/Masterkpi/MasterKPIAktifNonaktif";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		
		if($httpcode!=200){
			$params['Remarks']="FAILED - URL ".$URL." sedang tidak bisa diakses! HTTP Code:".$httpcode;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			$res['error'] = 'URL "'.$URL.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode;
		}
		else{
			if($response=='success'){
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				$res['result']=$response;
			}
			else{
				$params['Remarks']="FAILED - ".$URL;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				$res['error']=$response;
			}
		}
		echo json_encode($res);
	}

	public function KPIApproval()
	{
		$checkAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2)); 
		$post['api'] = 'APITES';

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER KPI APPROVAL";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU MASTER KPI APPROVAL";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);

		$URL = $this->API_URL."/Masterkpi/KPIApprovalList";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $response; die;
		
		if($httpcode!=200){
			$params['Remarks']="FAILED - URL ".$URL." sedang tidak bisa diakses! HTTP Code:".$httpcode;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			echo 'URL "'.$URL.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode; die;
		}
		else{
			$result = json_decode($response);
			if($result->result =='success'){
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				$data['KPIApproval'] = $result->KPIApproval;
				$data['KPIApprovalEmailJob'] = $result->KPIApprovalEmailJob;
				$data['KPICategory'] = $result->KPICategory;
				$data['KPIApprovalSalesman'] = $result->KPIApprovalSalesman;
				$data['KPIApprovalWilayahSalesman'] = $result->KPIApprovalWilayahSalesman;
			}
			else{
				$params['Remarks']="FAILED - ".$URL;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				echo $result->error; die;
			}
		}
		
		$this->RenderView("KPIApprovalView", $data);
	}

	public function KPIApprovalSave()
	{
		$post = $this->PopulatePost();
		// echo json_encode($post);die;
		$post['api'] = 'APITES';
		$post['user'] = $_SESSION['logged_in']['username'];
		
		$res['result']='failed';
		$res['error']='';

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER KPI APPROVAL";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." TAMBAH MASTER KPI APPROVAL";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);
		
		$URL = $this->API_URL."/Masterkpi/KPIApprovalSave";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		// echo $response;die;
		
		if($httpcode!=200){
			$params['Remarks']="FAILED - URL ".$URL." sedang tidak bisa diakses! HTTP Code:".$httpcode;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			$res['error'] = 'URL "'.$URL.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode;
		}
		else{
			if($response=='success'){
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				$res['result']=$response;
			}
			else{
				$params['Remarks']="FAILED - ".$URL;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				$res['error']=$response;
			}
		}
		echo json_encode($res);
	}

	public function KPIApprovalDelete()
	{
		$post = $this->PopulatePost();
		$post['api'] = 'APITES';
		$post['user'] = $_SESSION['logged_in']['username'];
		
		$res['result']='failed';
		$res['error']='';

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MASTER KPI APPROVAL";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." HAPUS MASTER KPI APPROVAL";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($params);
		
		$URL = $this->API_URL."/Masterkpi/KPIApprovalDelete";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($post),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);
		
		if($httpcode!=200){
			$params['Remarks']="FAILED - URL ".$URL." sedang tidak bisa diakses! HTTP Code:".$httpcode;
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			$res['error'] = 'URL "'.$URL.'" sedang tidak bisa diakses! HTTP Code:'.$httpcode;
		}
		else{
			if($response=='success'){
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				$res['result']=$response;
			}
			else{
				$params['Remarks']="FAILED - ".$URL;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				$res['error']=$response;
			}
		}
		echo json_encode($res);
	}

}
