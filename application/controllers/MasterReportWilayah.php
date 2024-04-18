<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';

class MasterReportWilayah extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('MasterReportWilayahModel');
		$this->load->model('MsConfigModel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function index()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER REPORT WILAYAH"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER REPORT WILAYAH";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$data = array();
    $data['result'] = $this->MasterReportWilayahModel->getList();
		$issuccess = (!$this->input->get("insertsuccess") ? 2 : $this->input->get("insertsuccess"));
		if ($issuccess==1) {
			$this->session->set_flashdata('success','Data berhasil disimpan!');
		} else if ($issuccess==0) {
			$this->session->set_flashdata('error','Data gagal disimpan!');
		} 
		$data['ListReportOPT'] = $this->MsConfigModel->GetReportOPT();
    // $data['access'] = $this->ModuleModel->getDetail($ctrname, $_SESSION['role']);
    $data["master_opt"] = $this->session->userdata("mastergroupreport_opt");
    $data["title"] = "Config Group Wilayah Report";

    $this->RenderView("MasterReportWilayahView", $data);

    $paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
	}

	public function LoadListByReprotOPT()
	{
		$id = $this->input->get('id');
		$this->session->set_userdata("mastergroupreport_opt", $id);
		$data = $this->MasterReportWilayahModel->getListData($id);
		echo json_encode($data);
	}

	public function ListData(){
		$data_list=array();
		$total =0;

		$id = $this->input->get('id');
		$this->session->set_userdata("mastergroupreport_opt", $id);
		$data = $this->MasterReportWilayahModel->getListData($this->input->get());

		if(count($data)){
			$ConfigReport = $data["data"];

			$data_hasil=array();

			if($this->input->get('iDisplayStart')>0){
				$no=$this->input->get('iDisplayStart')+1;
			}else{
				$no=1;
			}

			if(!empty($ConfigReport)){

				$total = $data["total"];

				foreach (json_decode(json_encode($ConfigReport)) as $key => $r) {
					$action='';
					$req ='';
					$list=array();

					$list[] 	= '<center>'.$no.'</center>';
					$list[] 	= $r->Grup;
					$list[] 	= $r->WilayahGroup;
					$list[] 	= $r->PartnerType;
					$list[] 	= $r->Wilayah;
					$list[] 	= $r->Kota;
					$list[] 	= $r->ModifiedBy;
					$list[] 	= $r->ModifiedDate;
					$list[] 	= '<a href = "MasterReportWilayah/update_page?id='.$r->idconfig.'"><i class="glyphicon glyphicon-pencil"></a>';
					$onclick 	= 'onclick="delete_data('.$r->idconfig.')"';
					$list[] 	= "<a href = '#' ".$onclick."><i class='glyphicon glyphicon-trash'></a>";

					$data_list[]=$list;
					$no++;
				}
			}
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
	}

	public function insert_page()
	{	
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER REPORT WILAYAH"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER REPORT WILAYAH - INSERT NEW";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$data = array();
		$keyAPI = 'APITES';
		$check_wilayah = json_decode(file_get_contents($this->API_URL."/LaporanPenjualanNasional/CheckWilayah?api=".$keyAPI));
		$data["wilayah"] = $check_wilayah;
		$check_kota = json_decode(file_get_contents($this->API_URL."/LaporanPenjualanNasional/CheckKota?api=".$keyAPI));
		$data["kota"] = $check_kota;
		$data["ReportOpt"] = "";
		$data["Grup"] = "WILAYAH";
		$data["WilayahGroup"] = "";
		$data["Wilayah"] = "";
		$data["Kota"] = "";
		$data["id"] = 0;
		$data["PartnerType"] = "";
		$data["IsActive"] = "";
		$data['ListReportOPT'] = $this->MsConfigModel->GetReportOPT();
		$data['ListPartnerType'] = $this->MsConfigModel->GetPartnerType();

		$this->RenderView("MasterReportWilayahInsertView", $data);

		$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
	}

	public function insert_another()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER REPORT WILAYAH"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER REPORT WILAYAH - INSERT ANOTHER";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$dataid = $this->input->get('id');
		$insertsuccess = $this->input->get('insertsuccess');

		$data = array();
		$keyAPI = 'APITES';
		$check_wilayah = json_decode(file_get_contents($this->API_URL."/LaporanPenjualanNasional/CheckWilayah?api=".$keyAPI));
		$data["wilayah"] = $check_wilayah;
		$check_kota = json_decode(file_get_contents($this->API_URL . "/LaporanPenjualanNasional/CheckKota?api=" . $keyAPI));
		$data["kota"] = $check_kota;
		$G = $this->MasterReportWilayahModel->get($dataid);
		$data["ReportOpt"] = $G->ReportOpt;
		$data["Grup"] = $G->Grup;
		$data["WilayahGroup"] = $G->WilayahGroup;
		$data["Wilayah"] = $G->Wilayah;
		$data["Kota"] = $G->Kota;
		$data["id"] = $G->id;
		$data["PartnerType"] = $G->PartnerType;
		$data["IsActive"] = $G->IsActive;
		$data['ListReportOPT'] = $this->MsConfigModel->GetReportOPT();
		$data['ListPartnerType'] = $this->MsConfigModel->GetPartnerType();
		if ($insertsuccess==1) {
			$paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			$this->session->set_flashdata('success','Data berhasil disimpan!');
		} else {
			$paramsLog['Remarks']="FAILED - DATA GAGAL DISIMPAN";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			$this->session->set_flashdata('error','Data gagal disimpan!');
		}
		$this->RenderView("MasterReportWilayahInsertView", $data);
	}

	public function update_page()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER REPORT WILAYAH"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER REPORT WILAYAH - UPDATE PAGE";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$dataid = $this->input->get('id');
		$keyAPI = 'APITES';
		$check_wilayah = json_decode(file_get_contents($this->API_URL."/LaporanPenjualanNasional/CheckWilayah?api=".$keyAPI));
		$data["wilayah"] = $check_wilayah;
		$check_kota = json_decode(file_get_contents($this->API_URL . "/LaporanPenjualanNasional/CheckKota?api=" . $keyAPI));
		$data["kota"] = $check_kota;
    $data['row'] = $this->MasterReportWilayahModel->get($dataid);
		$data['ListReportOPT'] = $this->MsConfigModel->GetReportOPT();
		$data['ListPartnerType'] = $this->MsConfigModel->GetPartnerType();
		$this->RenderView("MasterReportWilayahUpdateView", $data);

		$paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
	}

	public function insert_data()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER REPORT WILAYAH"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER REPORT WILAYAH - INSERT DATA";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$reportopt = $this->input->post('reportopt');
		$wilayahgroup = $this->input->post('wilayahgroup');
		$wilayah = $this->input->post('wilayah');
		$kota = $this->input->post('kota');
		$grup = $this->input->post('grup');
		$partnertype = $this->input->post('partnertype');
		$aktif = $this->input->post('IsActive');

		$data = array (
			'ReportOpt' => $reportopt,
			'WilayahGroup' => $wilayahgroup,
			'Wilayah'  => trim($wilayah),
			'Kota'=> $kota,
			'Grup'=> $grup,
			'PartnerType'=> $partnertype,
			'IsActive'=> $aktif,
			'CreatedBy' => $_SESSION['logged_in']['username'],
			'ModifiedBy' => $_SESSION['logged_in']['username']
		);

    $Check = $this->MasterReportWilayahModel->get2($data);
		if ($Check==null) {
			$id = $this->MasterReportWilayahModel->add($data);
			if ($this->input->post("btnSubmit2")!=null) {
					// redirect('MasterReportWilayah/insert_page');
				redirect('MasterReportWilayah/insert_another?id='.$id."&insertsuccess=1");
			} else {
				redirect('MasterReportWilayah?insertsuccess=1');	    	
			}

		  $paramsLog['Remarks']="SUCCESS";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);
		} else {
			$paramsLog['Remarks']="FAILED - DATA TIDAK DITEMUKAN";
			$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($paramsLog);

			redirect('MasterReportWilayah?insertsuccess=0');
		}
  }

  public function update_data()
	{
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER REPORT WILAYAH"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER REPORT WILAYAH - UPDATE DATA";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

		$id = $this->input->post('id');
		$reportopt = $this->input->post('reportopt');
		$wilayahgroup = $this->input->post('wilayahgroup');
		$wilayah = $this->input->post('wilayah');
		$kota = $this->input->post('kota');
		$grup = $this->input->post('grup');
		$partnertype = $this->input->post('partnertype');
		$aktif = $this->input->post('IsActive');

		$data = array (
			'ReportOpt' => $reportopt,
			'WilayahGroup' => $wilayahgroup,
			'Wilayah'  => trim($wilayah),
			'Kota'=> $kota,
			'Grup'=> $grup,
			'PartnerType'=> $partnertype,
			'IsActive'=> $aktif,
			'CreatedBy' => $_SESSION['logged_in']['username'],
			'ModifiedBy' => $_SESSION['logged_in']['username']
		);
		$this->MasterReportWilayahModel->update($data,$id);

    $paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);

    redirect('MasterReportWilayah?updatesuccess=1');
  }


  public function delete_data()
	{ 
		$paramsLog = array();   
		$paramsLog['LogDate'] = date("Y-m-d H:i:s");
		$paramsLog['UserID'] = $_SESSION["logged_in"]["userid"];
		$paramsLog['UserName'] = $_SESSION["logged_in"]["username"];
		$paramsLog['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$paramsLog['Module']="MASTER REPORT WILAYAH"; 
		$paramsLog['TrxID'] = date("YmdHis");
		$paramsLog['Description']=$_SESSION["logged_in"]["username"]." BUKA MENU MASTER REPORT WILAYAH - UPDATE DATA";
		$paramsLog['Remarks']="";
		$paramsLog['RemarksDate'] = 'NULL';
		$this->ActivityLogModel->insert_activity($paramsLog);

    set_time_limit(0);  
		$post = $this->PopulatePost();	
		$dataid = $this->input->post('id');
		$this->MasterReportWilayahModel->delete($dataid);
		echo "1";  

    $paramsLog['Remarks']="SUCCESS";
		$paramsLog['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($paramsLog);
  }
}
