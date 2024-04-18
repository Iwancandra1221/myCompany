<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PembelianImportNew extends MY_Controller {

	public $alert = "";

	function __construct()
	{
		parent::__construct();
		$this->load->library("email");
		$this->load->model('approvalmodel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

    public function ReplaceChars($teks) 
    {
    	$teks = str_replace("/","_", $teks);
		$teks = str_replace(",","_", $teks);
		$teks = str_replace(" ","_", $teks);
		$teks = str_replace(".","_", $teks);
		$teks = str_replace("'","_", $teks);
		$teks = str_replace("-","_", $teks);
		$teks = str_replace("__","_",$teks);
		return $teks;		
    }

    public function ApproveRequest()
    {
    	$data = array();
    	$KodeRequest = urldecode($this->input->get("kdreq"));
    	$ApprovedBy = urldecode($this->input->get("empid"));
    	$GM = urldecode($this->input->get("gm"));

    	/*die($this->API_URL."/PembelianImport/ApproveRequest?api=APITES".
        			"&req=".urlencode($KodeRequest)."&appby=".urlencode($ApprovedBy));*/
        $result = json_decode(file_get_contents($this->API_URL."/PembelianImport/ApproveRequest?api=APITES".
        			"&req=".urlencode($KodeRequest)."&appby=".urlencode($ApprovedBy)."&gm=".urlencode($GM)), true);
        if ($result["result"]=="sukses") {
        	$this->approvalmodel->updateapproval($KodeRequest,$ApprovedBy,$GM,'approval');
			$data["content_html"] = '<script language="javascript">';
	        $data["content_html"].= 'alert("Request Berhasil Diapprove")';
	        $data["content_html"].= '</script>';
	        $data["content_html"].= "<script>window.close();</script>";					    	
        } else {
			$data["content_html"] = '<script language="javascript">';
	        $data["content_html"].= 'alert("Request Tidak Berhasil Diapprove : '.$result['error'].'")';
	        $data["content_html"].= '</script>';
	        $data["content_html"].= "<script>window.close();</script>";
        }

        $this->SetTemplate('template/notemplate');
	    $this->RenderView("CustomPageResult", $data);
    }

    public function RejectRequest()
    {
    	$data = array();
    	$KodeRequest = urldecode($this->input->get("kdreq"));
    	$ApprovedBy = urldecode($this->input->get("empid"));
    	$GM = urldecode($this->input->get("gm"));
    	
        $result = json_decode(file_get_contents($this->API_URL."/PembelianImport/RejectRequest?api=APITES".
        			"&req=".urlencode($KodeRequest)."&appby=".urlencode($ApprovedBy)."&gm=".urlencode($GM)), true);
        if ($result["result"]=="sukses") {
        	$this->approvalmodel->updateapproval($KodeRequest,$ApprovedBy,$GM,'cancel');
			$data["content_html"] = '<script language="javascript">';
	        $data["content_html"].= 'alert("Request Berhasil Direject")';
	        $data["content_html"].= '</script>';
	        $data["content_html"].= "<script>window.close();</script>";					    	
        } else {
			$data["content_html"] = '<script language="javascript">';
	        $data["content_html"].= 'alert("Request Tidak Berhasil Direject : '.$result['error'].'")';
	        $data["content_html"].= '</script>';
	        $data["content_html"].= "<script>window.close();</script>";
        }

        $this->SetTemplate('template/notemplate');
	    $this->RenderView("CustomPageResult", $data);
    }

}