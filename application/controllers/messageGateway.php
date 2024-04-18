 <?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class messageGateway extends NS_Controller 
{
	public $user = "";
	public $pass = "";
	public $host = "";
	public $port = "";
	public $crypto = "";

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('directory');
		$this->load->helper('file');
		$this->load->library('email');
		$this->load->library('zip');
		$this->load->model('accountModel');
	}

	public function checkemail(){
		if(!empty($this->input->post())){
			$checkemail = $this->accountModel->checkemail();
			if($checkemail=='SENDGRID'){
				$return = $this->SendEmailSendGrid();
				print_r(json_encode($return));
			}else{
				$this->SendEmail();
			}
		}
	}

	public function SendEmail()
  	{
	    set_time_limit(60);
	    $data = array();
	    
		$obj = json_decode(file_get_contents('php://input'), true);
	     
	    $to = $obj["to"];
		$cc = $obj["cc"];
		//$bcc= $obj["bcc"]; ----> ini lupa dicomment
		$subject = $obj["subject"];
		$message = $obj["message"];
		$account = 1;
		//$account = ISSET($obj["account"]) ? $obj["account"] : 1;
	    
	    $bcc = "";
	    if (isset($obj["bcc"])) {
		    $bcc = $obj["bcc"];
		}

		$branch = "MC";
		if (isset($obj["branch"])) {
			$branch = $obj["branch"];
		}
		$id = 0;
		//if (isset($obj["id"])) {
		//	$id = $obj["id"];
	    //}
	    $from = "";
	    if (isset($obj["from"])) {
	    	$from = $obj["from"];
	    }
	    $savelog = 1;
	    if (isset($obj["savelog"])) {
	    	$savelog = $obj["savelog"];
	    }

	    $resend = 0;
	    if (isset($obj["resend"])) {
	    	$resend = $obj["resend"];
	    }

	    // die($to);
		$this->Send($to, $cc, $subject, $message, $account, $resend, $branch, $id, $from, $savelog, $bcc);
	}


	public function SendEmail2()
	{
		$to = $this->input->post("to");
	    $cc = $this->input->post("cc");
	    $subject = $this->input->post("subject");
	    $message = $this->input->post("message");

	    $bcc = "";
	    if (isset($_POST["bcc"])) {
		    $bcc = $this->input->post("bcc");
		}

	    $account = 1;
	    //if (isset($_POST["account"])) {
		//    $account = $this->input->post("account");
		//}
	    $branch = "MC";
	    if (isset($_POST["branch"])) {
		    $branch = $this->input->post("branch");
		}
	    $id = 0;
	    //if (isset($_POST["id"])) {
		//    $id = $this->input->post("id");
		//}	    
	    $from = "";
	    if (isset($_POST["from"])) {
		    $from = $this->input->post("from");
		}
	    $savelog = 1;
	    if (isset($_POST["savelog"])) {
		    $savelog = $this->input->post("savelog");
		}

	    $resend = 0;
	    if (isset($_POST["resend"])) {
	    	$resend = $this->input->post("resend");
	    }

	    $this->Send($to, $cc, $subject, $message, $account, $resend, $branch, $id, $from, $savelog, $bcc);
	}

	public function SendEmailSendGrid(){

		$keyAPI = 'APITES';
		if($keyAPI!==$this->input->post("key")){
			$sendgridApiKey = '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';
			$sendgridApiUrl = 'https://api.sendgrid.com/v3/mail/send';

			$from = $this->input->post("from");
			$from = explode(",", $from);

			$to = $this->input->post("to");
		    $cc = $this->input->post("cc");
		    $subject = $this->input->post("subject");
		    $message = $this->input->post("message");
		    $titleattachments = $this->input->post("titleattachments");
		    $attachments = $this->input->post("attachments");

		    $datato=[];
		    $recipient = '';
			if (is_array($to)) {
			    foreach ($to as $email) {
			        if (!empty($email)) {
			            $datato[] = ["email" => $email];
			            $recipient .= $email.',';
			        }
			    }
			}else{
				$datato[] = ["email" => $to];
				$recipient .= $to.',';
			}

		    $datacc=[];
			if (is_array($cc)) {
			    foreach ($cc as $email) {
			        if (!empty($email)) {
			            $datacc[] = ["email" => $email];
			        }
			    }
			}else{
				if (!empty($cc)) {
			        $datacc[] = ["email" => $cc];
			    }
			}


			$dataattachment = [];

			if (!empty($attachments)) {
			    if (is_array($attachments)) {
			        $jumattachments = count($attachments);

		            for ($i = 0; $i < $jumattachments; $i++) {
		                if (!empty($attachments[$i])) {
		                	$title = str_replace(" ", "_", $titleattachments[$i]);
		                    $dataattachment[] = [
		                        'content' => $attachments[$i],
		                        'filename' => $title,
		                    ];
		                   
		                }
		            }
			     
			    } else {
			    	$title = str_replace(" ", "_", $titleattachments);
			        
			        
			        $file_url = $attachments;

					$local_path = 'upload/attachment/announcement/'.$title;
					copy($file_url, $local_path);

					$fileContents = file_get_contents($local_path);
					$attachments = base64_encode($fileContents);

					$dataattachment[] = [
			            'content' => $attachments,
			            'filename' => $title,
			        ];
			    }
			}

			$data = [
			    'personalizations' => [
			        [
			            'to' => 
			                $datato
			            ,
			            'cc' => 
			                $datacc
			            ,
			            'subject' => $subject
			        ]
			    ],
			    'from' => [
			        'email' => $from[0]
			    ],
			    'content' => [
			        [
			            'type' => 'text/html',
			            'value' => $message
			        ]
			    ],
			    'attachments' => $dataattachment
			];
			

			if (empty($datacc)) {
			    unset($data['personalizations'][0]['cc']);
			}

			if (empty($dataattachment)) {
			    unset($data['attachments']);
			}

			$headers = [
			    'Authorization: Bearer ' . $sendgridApiKey,
			    'Content-Type: application/json'
			];

			$curl = curl_init();

			curl_setopt_array($curl, array(
			    CURLOPT_URL => $sendgridApiUrl,
			    CURLOPT_RETURNTRANSFER => true,
			    CURLOPT_TIMEOUT => 1000,
			    CURLOPT_POST => 1,
			    CURLOPT_POSTFIELDS => json_encode($data),
			    CURLOPT_HTTPHEADER => $headers
			));

			$response = curl_exec($curl);

			$errorData = json_decode($response, true);

			$recipient =  substr($recipient, 0, -1);

			if (file_exists($local_path)) {
			    unlink($local_path);
			}

			if(!empty($errorData)){
				if (isset($errorData['errors']) && is_array($errorData['errors']) && count($errorData['errors']) > 0) {
					$errorMessage = $errorData['errors'][0]['message'];
					$errorfield = $errorData['errors'][0]['field'];

					return array("result"=>"GAGAL", "recipient"=>"$recipient","errMsg"=>$errorMessage);
				}
			}else{
				return array("result"=>"SUKSES", "recipient"=>"$recipient", "errMsg"=>"");
			}
			curl_close($curl);


		}else{
			return 'Gagal mengirim email!!! Key tidak valid';
		}
	}

	public function SendEmailTest($to)
	{	
			$to=urldecode($to);

			$this->email->clear(true);
			$this->email->to($to); 
			$subject = date("Ymd");
			$e_content = "<b>Hello</b>";
	 		$this->email->from("userrequest@bhakti.co.id", "USER REQUEST AUTO-EMAIL");
   			$this->email->subject($subject);
   			$this->email->message($e_content);	
   			$this->email->send();
   		    $this->email->clear();
	}
	

	public function Send($to, $cc, $subject, $message, $account=1, $resend=0, $branch="TES", $id=0, $from="", $savelog=1, $bcc="", $name_attachment="", $location_attachment="")
	{
		set_time_limit(60);
		$settings = $this->accountModel->EmailAccount($account);
		// die(json_encode($settings));
		$data = array();
		$err = "EMAIL CONFIG NOT FOUND";
		
		$isSuccess = 0;
		$user = "";
		
		error_reporting(0);
		$ci = get_instance();

		foreach($settings as $setting){
			if ($isSuccess==0) {
				// die(json_encode($setting));
				$data = $this->accountModel->Send($to, $cc, $subject, $message, $branch, $setting, $id, $from, $bcc, $name_attachment, $location_attachment, $resend);
				//public function Send($to, $cc, $subject, $message, $branch="TES", $setting, $id=0, $from="", $bcc="", $name_attachment="", $location_attachment="", $re=0)

				if ($data["result"]=="SUCCESS") {
					$user = $setting->mail_user;
					$isSuccess = 1;
					// break;
				}
			}
		}
		// die(json_encode($settings));
				
		$params = array();
		$params["Url"] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$params["To"] = $to;
		$params["Cc"] = $cc;
		$params["Subject"] = $subject;
		$params["Body"] = $message;
		$params["Branch"] = $branch;
		$params["LogId"] = $id;
		$params["Sender"] = $user;

		
		if ($savelog==1) {
			$this->accountModel->WriteLogEmail($params, $isSuccess, json_encode($data));
		}

		$hasil = json_encode($data["result"]);
		if($data["result"]=="SUCCESS"){
			header('HTTP/1.1: 200');
			header('Status: 200');
		}
		else{
			header('HTTP/1.1: 403');
			header('Status: 403');
		}
		header('Content-Length: '.strlen($hasil));
		exit($hasil);
	}


	
	public function SendEmailTestAliat()
	{
		$to = "tjambuiliat@gmail.com";
	    $cc = "ITDEV.DIST@BHAKTI.CO.ID";
	    $subject = "TEST DOANK";
	    $message = "TES KIRIM EMAIL<BR><b>ALIAT</b>";
	    $result = $this->Send($to, $cc, $subject, $message);	
		echo json_encode($result);die;
	}
	
	public function WriteLogEmailTest()
	{
		$data = array(
		  "to"=> "ALGHINZA@GMAIL.COM,nanthadecoco@gmail.com",
		  "cc"=> "EBILLING.BHAKTI.YGY@GMAIL.COM",
		  "subject"=> "PT.Bhakti Idola Tama eBill-JT.24-Aug-2023 [YGYA004]",
		  "message"=> "<div>Kepada Yth,</div><div>Bapak/Ibu PT. ALGHINZA SEJAHTERA</div><div style='height:25px;'></div><div>Terlampir e-billing** dan e-faktur** atas pembelian Anda kepada PT.Bhakti Idola Tama dengan nomor kwitansi <b>0107/YGY/TR/0823</b> JT. <b>24-Aug-2023</b></div><div>**) Lampiran dapat ditampilkan menggunakan Adober Reader yang dapat diunduh melalui situs \t\t<a href='http://get.adobe.com/reader/'>http://get.adobe.com/reader/</a></div><div style='height:25px;'></div><div>Hormat kami,</div><div style='height:25px;'></div><div style='text-decoration:underline;font-weight:bold;'>Finance Division</div><div style='font-weight:bold;'>PT. BHAKTI IDOLA TAMA</div><div style='font-weight:bold;'>JALAN TINOSIDIN NO. 389 B</div><div style='font-weight:bold;'>RT. 012 RW. -</div><div style='font-weight:bold;'>NGESTIHARJO  KASIHAN KAB. BANTUL DI. YOGYAKARTA</div><div style='height:25px;'></div><div>Ini adalah email otomatis. Tolong tidak membalas email ini. Untuk informasi lebih lanjut silahkan menghubungi sales atau divisi Tagihan kami pada  (Hunting)</div>",
		  "is_success"=> 1,
		  "sender"=> "EBILLING.BHAKTI.YGY@GMAIL.COM",
		  "branch"=> "",
		  "response"=> ""
		);

		$url = site_url()."messageGateway/WriteLogEmail";
		// die($url);
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($data),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		
		echo $response;
	
	}
	public function WriteLogEmail()
	{
		$obj = json_decode(file_get_contents('php://input'));

		if(ISSET($obj->to) && ISSET($obj->cc) && ISSET($obj->subject) && ISSET($obj->message)){
			$isSuccess = (ISSET($obj->is_success)) ? $obj->is_success : 0;
			$response = (ISSET($obj->response)) ? $obj->response : '';
			$params = array();
			$params["Url"] = (ISSET($obj->url)) ? $obj->url : '';
			$params["To"] = $obj->to;
			$params["Cc"] = $obj->cc;
			$params["Subject"] = $obj->subject;
			$params["Body"] = $obj->message;
			$params["Branch"] = (ISSET($obj->branch)) ? $obj->branch : '';
			$params["LogId"] = (ISSET($obj->id)) ? $obj->id : 0;
			$params["Sender"] = (ISSET($obj->sender)) ? $obj->sender : '';
			$log = $this->accountModel->WriteLogEmail($params, $isSuccess, $response);
			if($log==''){
				$data['result']='SUCCESS';
				$data['error']='';
			}
			else{
				$data['result']='FAILED';
				$data['error']=$log;
			}
			$hasil = json_encode($data);
			header('HTTP/1.1: 200');
			header('Status: 200');
			header('Content-Length: '.strlen($hasil));
			exit($hasil);
		}
	}

	public function SendEmailWithAttachment(){
	    set_time_limit(60);
	    $error=0;		    
		$name_attachment=array();
		$location_attachment=array();

	   	if(!empty($this->input->post())){

			$to = $this->input->post("to");
		    $cc = $this->input->post("cc");
		    $bcc = $this->input->post("bcc");
		    $subject = $this->input->post("subject");
		    $message = $this->input->post("message");

		    $account = 1;
		    //if (isset($_POST["account"])) {
			//    $account = $this->input->post("account");
			//}
		    $branch = "MC";
		    if (isset($_POST["branch"])) {
			    $branch = $this->input->post("branch");
			}
		    $id = 0;
		    //if (isset($_POST["id"])) {
			//    $id = $this->input->post("id");
			//}	    
		    $from = "";
		    if (isset($_POST["from"])) {
			    $from = $this->input->post("from");
			}
		    $savelog = 1;
		    if (isset($_POST["savelog"])) {
			    $savelog = $this->input->post("savelog");
			}



		    if (!empty($_FILES)) {

		    	$jum=count($_FILES);

		    	for($i=0; $i<$jum; $i++){
			    	$name_attachment[$i]= $_FILES['attachment'.$i]['name'];
					$location_attachment[$i]= $_FILES['attachment'.$i]['tmp_name'];
			    	if($_FILES['attachment'.$i]['size']>10000000){
						$error++;
					}
				}

			}

		}else{

			$obj = json_decode(file_get_contents('php://input'), true);
	    	
		    $to = $obj["to"];		
			$cc = $obj["cc"];
			$bcc = $obj["bcc"];
			$subject = $obj["subject"];
			$message = $obj["message"];
			$account = ISSET($obj["account"]) ? $obj["account"] : 1;
			$branch = "MC";
			if (isset($obj["branch"])) {
				$branch = $obj["branch"];
			}
			$id = 0;
			//if (isset($obj["id"])) {
			//	$id = $obj["id"];
		    //}
		    $from = "";
		    if (isset($obj["from"])) {
		    	$from = $obj["from"];
		    }
		    $savelog = 1;
		    if (isset($obj["savelog"])) {
		    	$savelog = $obj["savelog"];
		    }

		    $attachment = explode(",",$obj['attachment']);

		    if (count($attachment>0)) {

		    	$jum=count($attachment);

		    	for($i=0; $i<$jum; $i++){
					$pdf_decoded = base64_decode ($attachment[$i]);
					$pdf = fopen('upload/attachment/result_'.$i.'.pdf','w');
					fwrite ($pdf, $pdf_decoded);
					fclose ($pdf);
					$name_attachment[$i]="result_".$i.".pdf";

					$cek_size=filesize('upload/attachment/result_'.$i.'.pdf');
					if($cek_size>10000000){
						$error++;
					}
				}

			}

		}

		
		if($error==0){
	    	$this->Send($to, $cc, $subject, $message, $account, $branch, $id, $from, $savelog, $bcc, $name_attachment, $location_attachment);
	    }else{
	    	echo '"LARGE FILES, MAXIMUM 10 MB"';
	    }


	}

}