<?php
	if (!$this->session->userdata('uid'))
		redirect('Login');

    if($this->uri->segment(2) != '')
    	$ctrname = $this->uri->segment(1)."/".$this->uri->segment(2);
    else
       	$ctrname = $this->uri->segment(1);

	$access = $this->ModuleModel->getDetail($ctrname, $_SESSION['role']);
	$_SESSION["can_read"]=$access->can_read;
	$_SESSION["can_create"]=$access->can_create;
	$_SESSION["can_update"]=$access->can_update;
	$_SESSION["can_delete"]=$access->can_delete;
	$_SESSION["can_print"]=$access->can_print;

?>