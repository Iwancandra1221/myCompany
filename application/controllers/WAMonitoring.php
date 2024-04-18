<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class WAMonitoring extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('FormLibrary');
		$this->load->model('ConfigSysModel');
		$this->API_MSG = $this->ConfigSysModel->Get()->messageapi_url;
	}
	
	public function index()
	{
		$post = $this->PopulatePost();
		$data = array();
		$api = 'APITES';
		set_time_limit(60);

		$url = $this->API_MSG."/waba/wabaViewer/messages/10";
		$getMessages = file_get_contents($url);
		$messages = json_decode($getMessages, true);
		// die(json_encode($messages["messages"]));
		$data["messages"] = $messages["messages"];
		// die(json_encode($data["messages"]));
		// die($getMessages);

		$this->RenderView('WAMonitoringList',$data);
	}
	public function message($chatId=""){
		if($chatId==""){
			//redirect
		}
		else{
			$url = $this->API_MSG."/waba/wabaViewer/messages/1/".$chatId;
			$getMessages = file_get_contents($url);
			$messages = json_decode($getMessages, true);
			$data["messages"] = $messages["messages"];
			$data['chatId'] = $chatId;
			$data['formAction'] = base_url().'WAMonitoring/sendMessage?chatId='.urlencode($chatId);
			$this->RenderView('WAMessage',$data);
		}
		
	}
	public function sendMessage(){
		$data = array(
			'code' => 0,
			'msg' => '',
			'data' => array(),
		);
		$chatId = $this->input->get('chatId');
		$body = $this->input->post('body');
		if($chatId!="" && $body!=""){
			$url = $this->API_MSG."/waba/sendMessageWA?src=OTHERS";
			$content = json_encode(array(
				'chatId' => urldecode($chatId),
				'body' => $body,
			));
			$options = stream_context_create(['http' => [
					'method'  => 'POST',
					'header'  => 'Content-type: application/json',
					'content' => $content,
				]
			]);
			$result = file_get_contents($url, false, $options);
			$messages = json_decode($result, true);
			if($messages['sent']==true){
				/*
				{
				  "sent": true,
				  "id": "gBGGeRhGZTEfAgkJCh2wAz4ZH-8",
				  "message": "Sent to 78005553535@c.us",
				  "description": "Message has been sent to the provider"
				}
				*/
				$data['code'] = 1;
				$data['msg'] = "Sukses";
			}
			else{
				$data['msg'] = $messages['message'];
			}
			$this->output->set_status_header(200);
		}
		else{
			$this->output->set_status_header(500);
		}
		return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($data));
	}

}
