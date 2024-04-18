<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DataPicker extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('GzipDecodeModel');
    }

	public function index()
	{
		//$temp['row'] = $this->UserModel->getAllUser();
        //$this->RenderView('UserPickerView',$temp);
	}

	public function salesman()
    {	
        $url = $_SESSION['conn']->AlamatWebService;
        $result = file_get_contents($url.$this->API_BKT."/MasterSalesman/GetListSalesman?api=APITES&cbg=ALL");
        $result = $this->GzipDecodeModel->_decodeGzip_true($result);

        // $data['row'] = json_decode(file_get_contents($url.API_BKT."/MasterSalesman/GetListSalesman?api=APITES&cbg=ALL"));
        $data['row'] = $result;
        $data['title'] = "Pilih Salesman";
        $data["tipe"] = "SALESMAN";
        $this->SetTemplate('template/notemplate');
        $this->RenderView('DataPickerView', $data);
    }

    public function user()
    {   
        $temp = array();
        $this->RenderView('UserPickerView',$temp);
    }

    public function PickDealer(){
        $data = array();
        $url = $_SESSION['conn']->AlamatWebService;
        //die($url.API_BKT."/MasterDealer/GetListDealer?api=APITES");
        // print_r($url.API_BKT."/MasterDealer/GetListDealer?api=APITES&ubranch=".$_SESSION['conn']->BranchId);die();
        $get = file_get_contents($url.API_BKT."/MasterDealer/GetListDealer?api=APITES&ubranch=".$_SESSION["branchID"]);
        $get = $this->GzipDecodeModel->_decodeGzip_true($get);
        print_r($get);
        if($get["result"]=="sukses") {
            $data["row"] = $get["data"];
        } else {
            $data["row"] = array();
        }
        $data['title'] = "Pilih Dealer";
        $data["tipe"] = "DEALER";
        $this->SetTemplate('template/notemplate');
        $this->RenderView('DataPickerView', $data);
    }    

    public function PickEmployee(){
        $data = array();
        $get = json_decode(file_get_contents(API_HRD."/Employee/GetEmployeesAPI?api=APITES&cbg=".urlencode($_SESSION["logged_in"]["branch_id"])),true);
        if($get["result"]=="sukses") {
            $data["row"] = $get["data"];
        } else {
            $data["row"] = array();
        }
        $data['title'] = "Pilih Karyawan";
        $data["tipe"] = "EMPLOYEE";
        $this->SetTemplate('template/notemplate');
        $this->RenderView('DataPickerView', $data);
    }    

    public function PickEmployeeJkt(){
        $data = array();
        //die(API_HRD."/Employee/GetJktEmployeesAPI?api=APITES");
        $get = json_decode(file_get_contents(API_HRD."/Employee/GetJktEmployeesAPI?api=APITES"),true);
        if($get["result"]=="sukses") {
            $data["row"] = $get["data"];
        } else {
            $data["row"] = array();
        }
        //die(json_encode($data["row"]));
        //die(API_HRD."/Employee/GetEmployeesAPI?api=APITES");
        $data['title'] = "Pilih Karyawan";
        $data["tipe"] = "EMPLOYEE";
        $this->SetTemplate('template/notemplate');
        $this->RenderView('DataPickerView', $data);
    }    
}