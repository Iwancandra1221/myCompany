<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CustomerPicker extends MY_Controller {

	public function index()
	{
		$temp['row'] = $this->masterDbModel->get($_SESSION['conn']->DatabaseId);
		$this->SetTemplate('template/notemplate');
        $this->RenderView('CustomerPickerView',$temp);
	}

	public function getData($kd_plg)
    {	
    	$this->load->model('CustomerModel');
        $data = $this->CustomerModel->get_by_kode($kd_plg);
        echo json_encode($data);
    }
}