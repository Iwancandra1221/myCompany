<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PembelianImportApproval extends NS_Controller {

	public $alert = "";

	function __construct()
	{
		parent::__construct();
		$this->load->library("email");
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

        $urlBhakti = $this->API_URL."/PembelianImport/ApproveRequest?api=APITES".
                    "&req=".urlencode($KodeRequest)."&appby=".urlencode($ApprovedBy)."&gm=".urlencode($GM);
    	// die($urlBhakti);
        $GetResults = HttpGetRequest($urlBhakti, $this->API_URL, "APPROVE REQUEST PEMBELIAN IMPORT", 6000);
        $result = json_decode($GetResults, true);
        // die(json_encode($result));
        if ($result["result"]=="sukses") {
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