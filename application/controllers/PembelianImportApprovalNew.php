<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PembelianImportApprovalNew extends NS_Controller {

	public $alert = "";

	function __construct()
	{
		parent::__construct();
		$this->load->library("email");
		$this->load->model('PembelianImportApprovalModel');
		$this->load->model('approvalmodel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}


    public function index()
    {
		// $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		// if($_SESSION['can_read']==true){
		// 	$this->RenderView('BranchView','');
		// }else{
		
		$this->RenderView('PembelianImportApproval');

    }


    public function view()
    {
    	$data['get'] = urldecode($this->input->get("norequest"));
    	$data['approval'] = $this->PembelianImportApprovalModel->get($this->input->get("norequest"));
    /*
    public function view($number='')
    {
    	$data['get'] = urldecode($number);
    	$data['approval'] = $this->PembelianImportApprovalModel->get($number);
     */
    	$this->RenderView('PembelianImportApproval',$data);
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

        $urlBhakti = $this->API_URL."/PembelianImport/ApproveRequest?api=APITES".
                    "&req=".urlencode($KodeRequest)."&appby=".urlencode($ApprovedBy)."&gm=".urlencode($GM);
    	// die($urlBhakti);
        $GetResults = HttpGetRequest($urlBhakti, $this->API_URL, "APPROVE REQUEST PEMBELIAN IMPORT", 6000);

        $result = json_decode($GetResults, true);

        // die(json_encode($result));
        if ($result["result"]=="sukses") {
        	$this->approvalmodel->updateapproval($KodeRequest,$ApprovedBy,$GM,'approval');
			$data["content_html"] = '<script language="javascript">';
	        $data["content_html"].= 'alert("Request Berhasil Diapprove")';
	        $data["content_html"].= '</script>';
	        $data["content_html"].= "<div style='margin-top:50px;font-size:14pt;width:100%;text-align:center;'>Request Pembelian Import No.<b>".$KodeRequest."</b> Telah Berhasil Diapprove!</div>";					    	
            die($data["content_html"]);
            // $this->SetTemplate('template/notemplate');
            // $this->RenderView("CustomPageResult", $data);

        } else {
			$data["content_html"] = '<script language="javascript">';
	        $data["content_html"].= 'alert("Request Tidak Berhasil Diapprove : '.strtoupper($result['error']).'")';
	        $data["content_html"].= '</script>';
	        // $data["content_html"].= "<script>window.close();</script>";
            $data["content_html"].= "<div style='margin-top:50px;font-size:14pt;width:100%;text-align:center;'>Request Pembelian Import No.<b>".$KodeRequest."</b> Tidak Berhasil Diapprove!<br><b>".strtoupper($result['error'])."</b></div>";                           
            die($data["content_html"]);
            // $this->SetTemplate('template/notemplate');
            // $this->RenderView("CustomPageResult", $data);
        }

    }

    public function RejectRequest()
    {
    	$data = array();
    	$KodeRequest = urldecode($this->input->get("kdreq"));
    	$ApprovedBy = urldecode($this->input->get("empid"));
    	$GM = urldecode($this->input->get("gm"));
    	
        $urlBhakti = $this->API_URL."/PembelianImport/RejectRequest?api=APITES".
        			"&req=".urlencode($KodeRequest)."&appby=".urlencode($ApprovedBy)."&gm=".urlencode($GM);
        $GetResults = HttpGetRequest($urlBhakti, $this->API_URL, "REJECT REQUEST PEMBELIAN IMPORT", 6000);
        $result = json_decode($GetResults, true);

        if ($result["result"]=="sukses") {
        	$this->approvalmodel->updateapproval($KodeRequest,$ApprovedBy,$GM,'cancel');
			$data["content_html"] = '<script language="javascript">';
	        $data["content_html"].= 'alert("Request Berhasil Direject")';
	        $data["content_html"].= '</script>';
	        // $data["content_html"].= "<script>window.close();</script>";					    	
            $data["content_html"].= "<div style='margin-top:50px;font-size:14pt;width:100%;text-align:center;'>Request Pembelian Import No.<b>".$KodeRequest."</b> Berhasil Direject!</div>";                           
            die($data["content_html"]);
        } else {
			$data["content_html"] = '<script language="javascript">';
	        $data["content_html"].= 'alert("Request Tidak Berhasil Direject : '.strtoupper($result['error']).'")';
	        $data["content_html"].= '</script>';
	        // $data["content_html"].= "<script>window.close();</script>";
            $data["content_html"].= "<div style='margin-top:50px;font-size:14pt;width:100%;text-align:center;'>Request Pembelian Import No.<b>".$KodeRequest."</b> Tidak Berhasil Direject!<br><b>".strtoupper($result['error'])."</b></div>";                           
            die($data["content_html"]);
        }

     //    $this->SetTemplate('template/notemplate');
	    // $this->RenderView("CustomPageResult", $data);
    }



}