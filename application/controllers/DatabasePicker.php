<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DatabasePicker extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$temp = array();
		$temp['row'] = $this->MasterDbModel->getList();
        $this->RenderView('DatabasePickerView',$temp);
	}

}