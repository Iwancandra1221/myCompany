<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ReportLog extends MY_Controller{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('ModuleModel');
		$this->load->model('UserModel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
		$this->API_BKT = $this->ConfigSysModel->Get()->bktapi_appname;
	}
	private function _postRequest($url,$data){

		// $options = array(
		//     'http' => array(
		//    	 	'method' => 'POST',
		//     	'content' => http_build_query($data),
		//     	'header'  => 'Content-type: application/x-www-form-urlencoded',
		// 	),
		    
		// );
		// $stream = stream_context_create($options);
		// $getContent = file_get_contents($url, false, $stream);
		// $result = json_decode($getContent,true);
		// return $result;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
		//curl_setopt($ch, CURLOPT_POSTFIELDS,"postvar1=value1&postvar2=value2&postvar3=value3");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);
		return $result;
	}
	public function ActivityLog(){
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "REPORT LOG";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU REPORT LOG ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$module = $this->_getModule();
		$action = $this->_getAction();
		$user = $this->_getUser();
		$data = array(
			'formURL' => base_url().'ReportLog/LoadActivityLog',
			'module' => $module,
			'action' => $action,
			'user' => $user,
		);
		// die(json_encode($data));

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->RenderView('ReportActivityLog',$data);		
	}

	public function LoadActivityLog(){
		//--

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "REPORT LOG";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]."PROSES REPORT LOG ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);


		$api = 'APITES';
		set_time_limit(60);
		
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}

		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;

		$module = urldecode($this->input->post('module'));
		$action = urldecode($this->input->post('action'));
		$user = urldecode($this->input->post('user'));
		$trxId = urldecode($this->input->post('trx_id'));
		$startDate  = urldecode($this->input->post("start_date"));
		$endDate  = urldecode($this->input->post("end_date"));

		if($startDate!='') $startDate = date('Y-m-d',strtotime($startDate));
		if($endDate!='') $endDate = date('Y-m-d',strtotime($endDate));

		$draw = 1;
		$limit = 0;
		$offset = 0;
		if(isset($_REQUEST['draw'])){
			$draw = $_REQUEST['draw'];
		}
		$search = isset($_REQUEST['search']['value']) ? $_REQUEST['search']['value'] : '';
		if($search!=''){
			$module = '';
			$action = '';
			$user = '';
			$trxId = '';
			$startDate = '';
			$endDate = '';
		}
        $payload = array(
			'api' => urlencode($api),
			'svr' => urlencode($svr),
			'db' => urlencode($db),

			'module' => $module,
			'action' => $action,
			'user' => $user,
			'trx_id' => $trxId,
			'start_date' => $startDate,
			'end_date' => $endDate,

			'draw' => $_REQUEST['draw'],
			'columns' => $_REQUEST['columns'],
			'start' => $_REQUEST['start'],
			'length' => $_REQUEST['length'],
			'search' => $_REQUEST['search'],
		);

		$postdata = http_build_query($payload);

		$opts = array('http' =>
			array(
				'method'  => 'POST',
				'header'  => 'Content-Type: application/x-www-form-urlencoded',
				'content' => $postdata
			)
		);
		$context  = stream_context_create($opts);
		$hasil = file_get_contents($url.$this->API_BKT.'/ReportLog/ActivityLog', false, $context);
		
		$result = json_decode($hasil,true);
		if($result!=''){
			$result['draw'] = $draw;
			$json = json_encode($result);

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);


			echo $json;
			// header('HTTP/1.1: 200');
			// header('Status: 200');
			// header('Content-Length: '.strlen($result));
			// exit($result);
		}
		else{
			// ActivityLog Update FAILED
			$params['Remarks']="FAILED - Data Tidak Ditemukan";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			echo 'error <br>'.$hasil;
		}	
	}

	public function _getModule(){
		$api = 'APITES';

		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}

		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;

		$payload = array(
			'api' => urlencode($api),
			'svr' => urlencode($svr),
			'db' => urlencode($db),
		);
		$postdata = http_build_query($payload);

		$opts = array('http' =>
			array(
				'method'  => 'POST',
				'header'  => 'Content-Type: application/x-www-form-urlencoded',
				'content' => $postdata
			)
		);
		$context  = stream_context_create($opts);
		$hasil = file_get_contents($url.$this->API_BKT.'/ReportLog/Module', false, $context);
		
		$result = json_decode($hasil,true);
		return $result;
	}
	public function _getAction(){
		$api = 'APITES';

		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}

		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;

		$payload = array(
			'api' => urlencode($api),
			'svr' => urlencode($svr),
			'db' => urlencode($db),
		);
		$postdata = http_build_query($payload);

		$opts = array('http' =>
			array(
				'method'  => 'POST',
				'header'  => 'Content-Type: application/x-www-form-urlencoded',
				'content' => $postdata
			)
		);
		$context  = stream_context_create($opts);
		$hasil = file_get_contents($url.$this->API_BKT.'/ReportLog/Action', false, $context);
		
		$result = json_decode($hasil,true);

		return $result;
	}
	public function _getUser(){
		$api = 'APITES';

		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}

		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;

		$payload = array(
			'api' => urlencode($api),
			'svr' => urlencode($svr),
			'db' => urlencode($db),
		);
		$postdata = http_build_query($payload);

		$opts = array('http' =>
			array(
				'method'  => 'POST',
				'header'  => 'Content-Type: application/x-www-form-urlencoded',
				'content' => $postdata
			)
		);
		$context  = stream_context_create($opts);
		$hasil = file_get_contents($url.$this->API_BKT.'/ReportLog/User', false, $context);
		
		$result = json_decode($hasil,true);

		return $result;
	}
	
	public function SyncLog()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "SYNC LOG";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU SYNC LOG ";
		$params['Remarks'] = "";
		$params['RemarksDate'] =  'NULL';
		$this->ActivityLogModel->insert_activity($params);



		$api = 'APITES';
		set_time_limit(60);
		
		if(!isset($_SESSION["conn"])) {
			redirect("ConnectDB");
		}
		$ModifiedBy = $_SESSION['logged_in']['username'];

		$url = $_SESSION["conn"]->AlamatWebService;
		$svr = $_SESSION["conn"]->Server;
		$db  = $_SESSION["conn"]->Database;


		if(!empty($_POST['type_of_config'])){
			$ConfigType=str_replace("=", "", base64_encode($_POST['type_of_config']));
		}else{
			$ConfigType='';
		}

		if(!empty($_POST['config_name'])){
			$ConfigName=str_replace("=", "", base64_encode($_POST['config_name']));
		}else{
			$ConfigName='';
		}

		if(!empty($_POST['Cabang'])){
			$filter_cabang = $_POST['Cabang'];
		}else{
			$filter_cabang = $_SESSION['logged_in']['branch_id'];
		}
		$CariCabang=str_replace("=", "", base64_encode($filter_cabang));
		$TarikCabang=str_replace("=", "", base64_encode(base64_encode(base64_encode($_SESSION["branchID"]))));
		$submit = $this->input->post('submit');


		if($submit==''){
			$data = array(
				'DataBranch' => array(),
				'typeconfigall' => array(),
			);

			

			// $GetBranch = json_decode(file_get_contents($url.$this->API_BKT."/MasterTypeConfig/GetBranch?api=".urlencode($api)."&svr=".urlencode($svr)."&db=".urlencode($db)),true);
			// if ($GetBranch["result"]=="sukses") {
			// 	$data["DataBranch"] = $GetBranch["data"];
			// } else {
			// 	$data["DataBranch"] = array();
			// }

			$data["branches"] = null;

			


			$payloadUrl = $url.$this->API_BKT."/MasterTypeConfig/GetListTypeConfig?api=".urlencode($api)."&svr=".urlencode($svr)."&db=".urlencode($db);
			$GetTypeConfig = json_decode($this->_postRequest($payloadUrl,array()) ,true);
			
			if ($GetTypeConfig["result"]=="sukses") {
				$data["typeconfig"] = $GetTypeConfig["data"];
			} else {
				$data["typeconfig"] = array();
			}

			$payloadUrl = $this->API_URL.'/Cabang/GetMstCabang?api=APITES';
	        $GetBranch = json_decode($this->_postRequest($payloadUrl,array()) ,true);
			if($GetBranch !=null){
				$data["DataBranch"] = $GetBranch['data'];
			}

			$from='';
			$until='';
			$ConfigType='';
			$Cabang='';
			$ConfigName='';
			$CariCabang='';

			// if(!empty($_POST['dp1'])){
				// $from=str_replace("=", "", base64_encode($_POST['dp1']));
			// }else{
				// $from='';
			// }

			// if(!empty($_POST['dp2'])){
				// $until=str_replace("=", "", base64_encode($_POST['dp2']));
			// }else{
				// $until='';
			// }

			// if(!empty($_POST['dp1'])){
				// $from=str_replace("=", "", base64_encode($_POST['dp1']));
			// }else{
				// $from='';
			// }


			

			$data['title'] = 'LOG SYNC | '.WEBTITLE;
			$data['filter_cabang'] = $filter_cabang;
			$data['ModifiedBy'] = $ModifiedBy;

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);
			
			$this->RenderView('ReportSyncLog',$data);
		}
		else{
			$draw = 1;
			$top = 10;
			$offset = 0;
			$data = array(
				'draw' => 0,
				'recordsTotal'=> 10,
				'recordsFiltered' => 10,
				'code' => 0,
				'msg' => '',
				'data' => array(),
			);
			
			if(isset($_REQUEST['draw'])){
				$draw = $_REQUEST['draw'];
			}
			if(isset($_REQUEST['length'])){
				$top = $_REQUEST['length'];
			}
			if(isset($_REQUEST['start'])){
				$offset = $_REQUEST['start'];
			}
			$payload = array(
				'api' =>urlencode($api),
				'svr' => urlencode($svr),
				'db' => urlencode($db),
				'ConfigType' => urlencode($ConfigType),
				'Cabang' => urlencode($TarikCabang),
				'ConfigName' => urlencode($ConfigName),
				'CariCabang' => urlencode($CariCabang),
				'top' => $top,
				'offset' => $offset,
			);
			$strPayload = http_build_query($payload);
			$payloadUrl = $url.$this->API_BKT."/MasterTypeConfig/GetListTypeConfigAll?".$strPayload;
	        $GetTypeConfigAll = json_decode($this->_postRequest($payloadUrl,array()) ,true);

	        $data["typeconfigall"] = array();
			if ($GetTypeConfigAll["result"]=="sukses") {
				$dataTable = array();
				foreach($GetTypeConfigAll['data'] as $value){
					$aksi = '';
					if($value["ConfigType"]=='LAST_SYNC'){
						$aksi = <<<HTML
						<button type="button" onclick="edit('{$value['ConfigName']}')"><span class="glyphicon glyphicon-pencil"></span></button>
HTML;
					}
					$value['aksi'] = $aksi;
					$dataTable[] = $value;
				}

				$data['data'] = $dataTable;
				$data['draw'] = $draw;
				$data['recordsTotal'] = $GetTypeConfigAll["recordsTotal"];
				$data['recordsFiltered'] = $GetTypeConfigAll["recordsTotal"];
				$data['code'] = 1;
				$msg = "data ditemukan";
			}

			$json = json_encode($data);
			echo $json;
		}
		
	}
}
?>