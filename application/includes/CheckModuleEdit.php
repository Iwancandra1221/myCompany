<?php
	if (!$this->session->userdata('uid'))
		redirect('Login');

	$controller_name="";
	if (isset($controller)==false)
	{
		$url =  "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$url = str_replace(XAMPP_PORT,'', $url);
		$url = str_replace(base_url(),'', $url);
		$cari = strpos($url,'/');
		
		if (!$cari)
			$controller_name = $url;
		else
			$controller_name = strstr($url,'/',true);
	} else {
		$controller_name=$controller;
	}

	$ModuleID = $this->modulemodel->GetModuleIdByControllerName($controller_name);
	$allow_view=$this->modulemodel->AllowDetailModule($ModuleID,'VIEW');
	$allow_add=$this->modulemodel->AllowDetailModule($ModuleID,'ADD');
	$allow_edit=$this->modulemodel->AllowDetailModule($ModuleID,'EDIT');
	$allow_delete=$this->modulemodel->AllowDetailModule($ModuleID,'DEL');
	$allow_import=$this->modulemodel->AllowDetailModule($ModuleID,'IMPORT');

	$this->session->set_userdata('active_module_payroll',$ModuleID);
	$this->session->set_userdata('allow_view',$allow_view);
	$this->session->set_userdata('allow_add',$allow_add);
	$this->session->set_userdata('allow_edit',$allow_edit);
	$this->session->set_userdata('allow_delete',$allow_delete);
	$this->session->set_userdata('allow_import',$allow_import);
	
	if(!$this->session->userdata('allow_edit'))
		redirect('Forbidden');
?>