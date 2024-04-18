<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class WebHrd extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	}

	public function GetBranches()
	{
		$api = 'APITES';
		$user = $this->session->userdata["logged_in"]["useremail"];

		if(!empty($api)) {
			//die(HRD_URL."APIIncoming/GetBranches?user=".urlencode($user)."&api=".$api);
			$url = file_get_contents(API_HRD."/Branch/GetBranches".
				"?user=".urlencode($user).
				"&api=".$api);
			echo $url;			
		} else {
			echo "Tidak ada Data";
		}
	}

}