<?php
defined('BASEPATH') or exit('No direct script access allowed');

require FCPATH.'application/controllers/vendor/autoload.php';
class Bugsnag extends CI_Controller{
	public function __construct(){
		parent::__construct();
		//mycompany-api
		$this->bugsnag = Bugsnag\Client::make("855cd45a6aadeeefbed254062563a39c");
		Bugsnag\Handler::register($this->bugsnag);
		$this->bugsnag->setReleaseStage(BUGSNAG_RELEASE_STAGE);
	}
	public function NotifyException(){
		$json = file_get_contents('php://input');

		$user = array();
		$data = json_decode($json,true);
		$result = "FAILED";
		if($data!=''){
			$msg = $data['msg'];
			if(isset($data['user']) && $data['user']['name']!=''){
				$user = array(
					'id' => $data['user']['id'],
					'name' => $data['user']['name'],
					'email' => $data['user']['email'],
				);
				$this->bugsnag->setMetaData([
				 	'user' => $user
			    ]);
			}
			
			if($msg=='') $msg = 'No Message';

			$this->bugsnag->NotifyException(new RuntimeException($msg));
			//$this->bugsnag->notifyError('ErrorType', 'A wild error appeared!');
			
			$result = "SUKSES";
		}

		echo $result;
	}

}