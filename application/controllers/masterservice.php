<?php
defined('BASEPATH') OR exit('No direct script access allowed');

	require 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class masterservice extends My_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('masterservicemodel');
		$this->load->library('excel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function index(){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		$export = $this->input->get('export');
		$submit = $this->input->post('submit');

		if($submit==''){
			if($export==1){

			}else{
				// $body = array(
				// 	'title' => 'MASTER SERVICE',
				// 	'SelectJenisBarang' => $this->SelectJenisBarang(),
				// 	'SelectKerusakan' => $this->SelectKerusakan(),
				// 	'SelectPenyebab' => $this->SelectPenyebab(),
				// 	'SelectPerbaikan' => $this->SelectPerbaikan(),
				// );
				// echo json_encode($body);die;

				$this->RenderView('MasterServiceView');
			}
		}
		else{
			$username = $_SESSION['logged_in']['username'];
			$kd_jnsbrg = $this->input->post('kd_jnsbrg');
			$kd_kerusakan = $this->input->post('kd_kerusakan');
			$kd_penyebab = $this->input->post('kd_penyebab');
			$kd_perbaikan = $this->input->post('kd_perbaikan');
			$is_active = (int)$this->input->post('is_active');
			$ID = $this->input->post('ID');
			$msg = '';

			$obj = array(
				'code' => 'failed',
				'messages' => array(),
				'data' => array(),
			);

			switch($submit){
				case 'Tambah':
					//
					$msg = "Tambah master service gagal";
					if($kd_jnsbrg!='' && $kd_kerusakan !='' && $kd_penyebab!='' &&  $kd_perbaikan!=''){
						$where = array(
							'ms_service.kd_jnsbrg' => $kd_jnsbrg,
							'ms_service.kd_kerusakan' => $kd_kerusakan,
							'ms_service.kd_penyebab' => $kd_penyebab,
							'ms_service.kd_perbaikan' => $kd_perbaikan,
						);
						$found = $this->masterservicemodel->GetMsServiceCount($where);
						if($found==0){
							//jika tidak ketemu boleh insert
							$msg = "Tambah gagal";

							$data = array(
								'id' => $this->masterservicemodel->GetLastIdMsService(),
								'kd_jnsbrg' => $kd_jnsbrg,
								'kd_kerusakan' => $kd_kerusakan,
								'kd_penyebab' => $kd_penyebab,
								'kd_perbaikan' => $kd_perbaikan,
								'is_active' => 1,
								'created_by' => $username,
								'created_date' => date('Y-m-d H:i:s'),
								'modified_by' => $username,
								'modified_date' => date('Y-m-d H:i:s'),
							);
							$result = $this->masterservicemodel->AddMsService($data);
							if($result){
								$obj['code'] = 'success';
								$msg = "Tambah sukses";
							}
						}
						else{
							$msg = "master service sudah pernah dibuat";
						}
					}
				break;
				case 'Edit':
					//
					$obj = array(
						'code' => 'failed',
						'messages' => array(),
						'data' => array(),
					);
					$msg = "Ubah master service gagal";
					if($ID!='' && $kd_jnsbrg!='' && $kd_kerusakan !='' && $kd_penyebab!='' &&  $kd_perbaikan!=''){
						$where = array(
							'ms_service.id <>' => $ID,
							'ms_service.kd_jnsbrg' => $kd_jnsbrg,
							'ms_service.kd_kerusakan' => $kd_kerusakan,
							'ms_service.kd_penyebab' => $kd_penyebab,
							'ms_service.kd_perbaikan' => $kd_perbaikan,
						);
						$found = $this->masterservicemodel->GetMsServiceCount($where);
						
						
						if($found==0){
							//jika tidak ketemu boleh insert
							$msg = "Ubah gagal";

							$where = array(
								'ID' => $ID,
							);
							$data = array(
								'kd_jnsbrg' => $kd_jnsbrg,
								'kd_kerusakan' => $kd_kerusakan,
								'kd_penyebab' => $kd_penyebab,
								'kd_perbaikan' => $kd_perbaikan,
								'is_active' => $is_active,
								//'created_by' => $username,
								//'created_date' => date('Y-m-d H:i:s'),
								'modified_by' => $username,
								'modified_date' => date('Y-m-d H:i:s'),
							);
							$result = $this->masterservicemodel->EditMsService($where,$data);
							if($result){
								$obj['code'] = 'success';
								$msg = "Ubah sukses";
							}
							else 
								$msg = $result;
						}
						else{
							$msg = "master service sudah pernah dibuat";
						}
					}
				break;
				case 'Delete':
					//
					
					$msg = "Hapus master service gagal";
					if($ID!=''){
						
						$msg = "Hapus gagal";
						$where = array(
							'ID' => $ID,
						);
						$result = $this->masterservicemodel->DeleteMsService($where);
						if($result){
							$obj['code'] = 'success';
							$msg = "Hapus sukses";
						}
					}
					
				break;
			}
			$obj['messages'][0] = $msg;
			$json = json_encode($obj);
			echo $json;
// 			$url = base_url()."MasterService";
// 			echo <<<HTML
// 			<script type="text/javascript">
// 				alert("{$msg}");
// 				window.location.href="{$url}";
// 			</script>
// HTML;
		}
	}


// 	public function GetMasterService(){
// 		$kd_jnsbrg = $this->input->get('kd_jnsbrg');
// 		$kd_kerusakan = $this->input->get('kd_kerusakan');
// 		$kd_penyebab = $this->input->get('kd_penyebab');
// 		$kd_perbaikan = $this->input->get('kd_perbaikan');
// 		$orderArray = $this->input->get('order');
// 		$data = array(
// 			'draw' => 0,
// 			'recordsTotal'=> 10,
// 			'recordsFiltered' => 10,
// 			'code' => 0,
// 			'msg' => '',
// 			'data' => array(),
// 		);
// 		$msg = '';
// 		$whereSub = array(
// 			1 => '1',
// 			"IsActive" => 1,
// 		);

// 		if($kd_jnsbrg!='') $whereSub["ms_service.kd_jnsbrg"] = $kd_jnsbrg;
// 		if($kd_kerusakan!='') $whereSub["ms_service.kd_kerusakan"] = $kd_kerusakan;
// 		if($kd_penyebab!='') $whereSub["ms_service.kd_penyebab"] = $kd_penyebab;
// 		if($kd_perbaikan!="") $whereSub["ms_service.kd_perbaikan"] = $kd_perbaikan;

// 		$search = isset($_REQUEST['search']['value']) ? $_REQUEST['search']['value'] : '';
// 		$data['search'] = $search;

// 		if($search!=''){
// 			$whereSub["
// 				(ms_service_jnsbrg.jns_brg LIKE '%".$search."%' OR 
// 				ms_service_kerusakan.nm_kerusakan LIKE '%".$search."%' OR 
// 				ms_service_penyebab.nm_penyebab LIKE '%".$search."%' OR 
// 				ms_service_perbaikan.nm_perbaikan LIKE '%".$search."%' OR
// 				ms_service.kd_jnsbrg LIKE '%".$search."%' OR 
// 				ms_service.kd_kerusakan LIKE '%".$search."%' OR 
// 				ms_service.kd_penyebab LIKE '%".$search."%' OR 
// 				ms_service.kd_perbaikan LIKE '%".$search."%' OR
// 				ms_service.is_active LIKE '%".$search."%' OR
// 				ms_service.modified_by LIKE '%".$search."%' OR
// 				ms_service.modified_date LIKE '%".$search."%')
// 			"] = null;
// 		}

// 		$totalRow = $this->masterservicemodel->GetMsServiceCount($whereSub);


// 		if($search!=''){
// 			$whereSub["
// 				(x.jns_brg LIKE '%".$search."%' OR 
// 				x.nm_kerusakan LIKE '%".$search."%' OR 
// 				x.nm_penyebab LIKE '%".$search."%' OR 
// 				x.nm_perbaikan LIKE '%".$search."%' OR
// 				x.kd_jnsbrg LIKE '%".$search."%' OR
// 				x.kd_kerusakan LIKE '%".$search."%' OR 
// 				x.kd_penyebab LIKE '%".$search."%' OR 
// 				x.kd_perbaikan LIKE '%".$search."%' OR
// 				x.is_active LIKE '%".$search."%' OR
// 				x.modified_by LIKE '%".$search."%' OR
// 				x.modified_date LIKE '%".$search."%')
// 			"] = null;
// 		}
		
// 		if($kd_jnsbrg!='') $whereSub["x.kd_jnsbrg"] = $kd_jnsbrg;
// 		if($kd_kerusakan!='') $whereSub["x.kd_kerusakan"] = $kd_kerusakan;
// 		if($kd_penyebab!='') $whereSub["x.kd_penyebab"] = $kd_penyebab;
// 		if($kd_perbaikan!="") $whereSub["x.kd_perbaikan"] = $kd_perbaikan;

// 		$totalRow = $this->masterservicemodel->GetMsServiceCount($whereSub);

// 		$orderColumn=$orderArray;
// 		if(isset($orderColumn[0]['column'])!=''){
// 			$orderColumn = $orderColumn[0]['column'];
// 		}
// 		if($orderColumn==0) $orderColumnName='jns_brg';
// 		else if($orderColumn==1) $orderColumnName='ms_service_kerusakan.nm_kerusakan';
// 		else if($orderColumn==2) $orderColumnName='ms_service_penyebab.nm_penyebab';
// 		else if($orderColumn==3) $orderColumnName='ms_service_perbaikan.nm_perbaikan';
// 		else if($orderColumn==4) $orderColumnName='ms_service.is_active';
// 		else if($orderColumn==5) $orderColumnName='ms_service.modified_by';
// 		else if($orderColumn==6) $orderColumnName='ms_service.modified_date';

// 		$order = "UserName asc";
// 		$draw = 1;
// 		if($orderColumnName!=''){
// 			$order = $orderColumnName.' '.$orderArray[0]['dir'];
// 		}
// 		if(isset($_REQUEST['draw'])){
// 			$draw = $_REQUEST['draw'];
// 		}
// 		if(isset($_REQUEST['start']) && isset($_REQUEST['length'])){
// 			$start = $_REQUEST['start'];
// 			$length = $_REQUEST['length'];
// 			$limit = (object) ['limit'=>$length,'offset'=>$start];
// 		}
// 		$where = array();

// 		if($search!=''){
// 			$where["
// 				(x.jns_brg LIKE '%".$search."%' OR 
// 				x.nm_kerusakan LIKE '%".$search."%' OR 
// 				x.nm_penyebab LIKE '%".$search."%' OR 
// 				x.nm_perbaikan LIKE '%".$search."%' OR
// 				x.kd_jnsbrg LIKE '%".$search."%' OR
// 				x.kd_kerusakan LIKE '%".$search."%' OR 
// 				x.kd_penyebab LIKE '%".$search."%' OR 
// 				x.kd_perbaikan LIKE '%".$search."%' OR
// 				x.is_active LIKE '%".$search."%' OR
// 				x.modified_by LIKE '%".$search."%' OR
// 				x.modified_date LIKE '%".$search."%')
// 			"] = null;
// 		}
		
// 		if($kd_jnsbrg!='') $where["x.kd_jnsbrg"] = $kd_jnsbrg;
// 		if($kd_kerusakan!='') $where["x.kd_kerusakan"] = $kd_kerusakan;
// 		if($kd_penyebab!='') $where["x.kd_penyebab"] = $kd_penyebab;
// 		if($kd_perbaikan!="") $where["x.kd_perbaikan"] = $kd_perbaikan;

// 		$top = $limit->limit;
// 		$where["RowNum >"] = $limit->offset;
// 		$result = $this->masterservicemodel->GetMsService($where,$order,$top);
// 		if($result!=null){
// 			foreach($result as $value){
// 				$value['jns_brg'] = rtrim($value['jns_brg'] ,' ') . ' - ' .rtrim($value['kd_jnsbrg'] ,' ');
// 				$value['nm_kerusakan'] = rtrim($value['nm_kerusakan'] ,' ') . ' - ' .rtrim($value['kd_kerusakan'] ,' ');
// 				$value['nm_penyebab'] = rtrim($value['nm_penyebab'] ,' ') . ' - ' .rtrim($value['kd_penyebab'] ,' ');
// 				$value['nm_perbaikan'] = rtrim($value['nm_perbaikan'] ,' ') . ' - ' .rtrim($value['kd_perbaikan'] ,' ');
// 				$value['is_active'] = ($value['is_active'] == 1 ? 'Aktif' : 'Tidak Aktif' );
// 				$aksi = <<<HTML
// 					<button class="btn btn-dark" title="Ubah" onClick="btn_edit('{$value['id']}','{$value['jns_brg']}','{$value['nm_kerusakan']}','{$value['nm_penyebab']}','{$value['nm_perbaikan']}','{$value['is_active']}')" type="button"><span class="glyphicon glyphicon-pencil"></span></button>
// HTML;
// 				$value['aksi'] = $aksi;
// 				$data['data'][] = $value;
// 			}
			
// 			$data['draw'] = $draw;
// 			$data['recordsTotal'] = $totalRow;
// 			$data['recordsFiltered'] = $totalRow;
// 			$data['code'] = 1;
// 			$msg = "data ditemukan";
// 		}
// 		$data['msg'] = $msg;
// 		$json = json_encode($data);
// 		echo $json;
// 	}










	public function GetMasterService(){

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "TARIK API MASTER SERVICE ";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." TARIK API MASTER SERVICE ";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';

		$response = $this->masterservicemodel->GetMsService($this->input->get());

			$result = json_decode($response);


			if($result->result =='success'){
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				$data['MasterService'] = $result->data;
				// print_r($data['MasterService']);
				// die();
				$data_list=array();

				if(!empty($data['MasterService'])){
					$listdata=json_decode(json_encode($data['MasterService']));
					// print_r($listdata);
					// die();
					foreach($listdata as $key => $d) {

						$tamp=array();
						$tamp[]=rtrim($d->jns_brg);
						$tamp[]=rtrim($d->nm_kerusakan);
						$tamp[]=rtrim($d->nm_penyebab);
						$tamp[]=rtrim($d->nm_perbaikan);
						$tamp[]= ($d->is_active == 1 ? 'Aktif' : 'Tidak Aktif' );
						$tamp[]= rtrim($d->modified_by);
						$tamp[]= rtrim($d->modified_date);
						$aksi = "'".$d->id."','".$d->jns_brg." - ".$d->kd_jnsbrg."','".$d->nm_kerusakan." - ".$d->kd_kerusakan."','".$d->nm_penyebab." - ".$d->kd_penyebab."','".$d->nm_perbaikan." - ".$d->kd_perbaikan."','".$d->is_active."'";
						$tamp[]='<button class="btn btn-dark" id="btntest" title="Ubah" onClick="btn_edit('.$aksi.')" type="button"><span class="glyphicon glyphicon-pencil"></span></button>';
						

						$data_list[]=$tamp;
					}
					$total=$result->total;
				}else{
					$total=0;
				}


				if(!empty($this->input->get('sEcho'))){
					$secho = $this->input->get('sEcho');
				}else{
					$secho = 1;
				}


				$data_hasil['sEcho']=$secho;
				$data_hasil['iTotalRecords']=$total;
				$data_hasil['iTotalDisplayRecords']=$total;
				$data_hasil['aaData']=$data_list;

				print_r(json_encode($data_hasil));

			}else{

				$tamp[]='Data tidak ditemukan';
				$tamp[]='';
				$tamp[]='';
				$tamp[]='';
				$tamp[]='';
				$tamp[]='';
				$tamp[]='';
				$tamp[]='';

				$data_list[]=$tamp;

				$data_hasil['sEcho']=0;
				$data_hasil['iTotalRecords']=0;
				$data_hasil['iTotalDisplayRecords']=0;
				$data_hasil['aaData']=$data_list;
				print_r(json_encode($data_hasil));

				$params['Remarks']="FAILED - ".$result->error;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				

			}
	}








	public function kerusakan(){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		$username = $_SESSION['logged_in']['username'];
		
		$submit = $this->input->post('submit');
		$btn = $this->input->get('btn');
		if($btn!=''){
			$submit = $btn;
		}
		if($submit==''){
			$export = (int)$this->input->get('export');
			if($export==1){
				//export excel
				$where = array(
					//1 => '1',
					// 'is_active' => 1,
				);
				$rekap = $this->masterservicemodel->GetKerusakan($where);
				$body = array(
					'rekap' => $rekap,
					'title' => 'Rekap Master Service Kerusakan',
				);
				$this->load->view('template_xls/MasterServiceKerusakanXls',$body);
			}
			else{
				// $where = array(
				// 	1 => '1',
				// 	// 'is_active' => 1,
				// );
				// $getKerusakan = $this->masterservicemodel->GetKerusakan($where);
				// $body = array(
				// 	'Kerusakan' => $getKerusakan,
				// );
				$this->RenderView('MasterServiceKerusakanView');
			}
			
		}
		else{
			$kode = $this->input->post('kode');
			$nama = $this->input->post('nama');
			$status = (int)$this->input->post('status');

			$msg = "";
			if($submit=='Tambah'){

					if($nama!=''){

						$data = array(
							'kd_kerusakan' => $this->masterservicemodel->GetAutoNumberKerusakan(),
							'nm_kerusakan' => strtoupper($nama),
							'created_by' => $username,
							'created_date' => date('Y-m-d H:i:s'),
							'modified_by' => $username,
							'modified_date' => date('Y-m-d H:i:s'),
							'is_active' => 1, 
						);
						$result = $this->masterservicemodel->AddKerusakan($data);
						if($result){
							$msg = "success";
						}
					}else{
						$msg = 'error';
					}
			}else if($submit=='Ubah'){
					$msg = "Ubah_data_gagal";
					if($kode!='' && $nama!=''){
						$where = array(
							'kd_kerusakan' => $kode,
						);
						$data = array(
							'nm_kerusakan' => strtoupper($nama),
							'is_active' => $status, 
							'modified_by' => $username,
							'modified_date' => date('Y-m-d H:i:s'),
						);
						$result = $this->masterservicemodel->UpdateKerusakan($where,$data);
						if($result){
							$msg = "success";
						}
					}else{
						$msg = 'error';
					}
			}else if($submit=='Delete'){
					$kode = $this->input->post('kode');
					if($kode!=''){
						// $where = array(
						// 	'kd_kerusakan' => $kode,
						// );
					 	$msg = $this->masterservicemodel->DeleteKerusakan($kode);
					 	
					}else{
						$msg = 'error';
					}
			}

			echo $msg;

		}

			// $url = base_url()."masterservice/kerusakan";
			// echo <<<HTML
			// <script>
			// 	alert('{$msg}');
			// 	window.location.href="{$url}";
			// </script>
		
	}

	// public function listkerusakan(){
	// 	$where = array(
	// 		1 => '1',
	// 		// 'is_active' => 1,
	// 	);
	// 	print_r(json_encode($this->masterservicemodel->GetKerusakan($where)));
	// }

	public function listkerusakan(){

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "TARIK API MASTER SERVICE KERUSAKAN ";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." TARIK API MASTER SERVICE KERUSAKAN";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';

		$response = $this->masterservicemodel->GetKerusakan($this->input->get());
		$result = json_decode($response);
		if($result->result =='success'){
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				$data['Masterkerusakan'] = $result->data;
				// print_r($data['Masterkerusakan']);
				// die();
				$data_list=array();

				if(!empty($data['Masterkerusakan'])){
					$listdata=json_decode(json_encode($data['Masterkerusakan']));
					// print_r($listdata);
					// die();
					foreach($listdata as $key => $d) {


						if($_SESSION["can_update"]==true){

							$edit = "'".$d->kd_kerusakan."','".$d->nm_kerusakan."','".$d->is_active."'";
			                            
							$actions = '<button type="button" class="btn btn-dark" onclick="btn_edit('.$edit.')"><span class="glyphicon glyphicon-pencil"></span></button><button class="btn btn-danger-dark delete-btn" data-encodedelete="'.str_replace("=","",base64_encode($d->kd_kerusakan)).'"><i class="glyphicon glyphicon-trash"></i></button>';
			                            
			            }else{
			                            	
			                $actions = '';
			                            
			            }
			                            

						$tamp=array();
						$tamp[]=rtrim($d->kd_kerusakan ,' ');
						$tamp[]=rtrim($d->nm_kerusakan ,' ');
						$tamp[]= ($d->is_active == 1 ? 'Aktif' : 'Tidak Aktif' );
						$tamp[]= rtrim($d->modified_by);
						$tamp[]= rtrim($d->modified_date);
						$tamp[]=$actions;
						

						$data_list[]=$tamp;
					}
					$total=$result->total;
				}else{
					$total=0;
				}


				if(!empty($this->input->get('sEcho'))){
					$secho = $this->input->get('sEcho');
				}else{
					$secho = 1;
				}


				$data_hasil['sEcho']=$secho;
				$data_hasil['iTotalRecords']=$total;
				$data_hasil['iTotalDisplayRecords']=$total;
				$data_hasil['aaData']=$data_list;

				print_r(json_encode($data_hasil));

			}else{

				$params['Remarks']="FAILED - ".$result->error;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				echo $result->error; die;

			}
	}

	public function penyebab(){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		$username = $_SESSION['logged_in']['username'];
		$submit = $this->input->post('submit');
		$btn = $this->input->get('btn');
		if($btn!=''){
			$submit = $btn;
		}
		if($submit==''){
			$export = (int)$this->input->get('export');
			if($export==1){
				$where = array(
					// 1 => '1',
					// 'is_active' => 1,
				);
				 $rekap = $this->masterservicemodel->GetPenyebab($where);
				$body = array(
					'rekap' => $rekap,
					'title' => 'Rekap Master Service Penyebab',
				);
				$this->load->view('template_xls/MasterServicePenyebabXls',$body);
			}
			else{
				$where = array(
					// 1 => '1',
					// 'is_active' => 1,
				);
				$GetPenyebab = $this->masterservicemodel->GetPenyebab($where);
				$body = array(
					'Penyebab' => $GetPenyebab,
				);
				$this->RenderView('MasterServicePenyebabView',$body);
			}
			
		}else{
			$kode = $this->input->post('kode');
			$nama = $this->input->post('nama');
			$status = (int)$this->input->post('status');
			
			$msg = "";
			if($submit=='Tambah'){
					if($nama!=''){
						$data = array(
							'kd_penyebab' => $this->masterservicemodel->GetAutoNumberPenyebab(),
							'nm_penyebab' => strtoupper($nama),
							'is_active' => 1, 
							'created_by' => $username,
							'created_date' => date('Y-m-d H:i:s'),
							'modified_by' => $username,
							'modified_date' => date('Y-m-d H:i:s'),
						);
						$result = $this->masterservicemodel->AddPenyebab($data);
						if($result){
							$msg = "success";
						}else{
							$msg='error';
						}
					}else{
						$msg='error';
					}
			}else if($submit=='Ubah'){
					$msg = "Ubah data gagal";
					if($kode!='' && $nama!=''){
						$where = array(
							'kd_penyebab' => $kode,
						);
						$data = array(
							'nm_penyebab' => strtoupper($nama),
							'is_active' => $status,
							'modified_by' => $_SESSION['logged_in']['username'],
							'modified_date' => date('Y-m-d H:i:s'),
						);
						$result = $this->masterservicemodel->UpdatePenyebab($where,$data);
						if($result){
							$msg = "success";
						}else{
							$msg='error';
						}
					}else{
						$msg='error';
					}
			}else if($submit=='Delete'){

					$kode = $this->input->post('kode');
					if($kode!=''){
						// $where = array(
						// 	'kd_penyebab' => $kode,
						// );
					 	$msg = $this->masterservicemodel->DeletePenyebab($kode);
					}else{
						$msg='error';
					}
			}

			echo $msg;

// 			$url = base_url()."masterservice/penyebab";
// 			echo <<<HTML
// 			<script>
// 				alert('{$msg}');
// 				window.location.href="{$url}";
// 			</script>
// HTML;
		}
	}

	// public function listpenyebab(){
	// 	$where = array(
	// 		1 => '1',
	// 		// 'is_active' => 1,
	// 	);
	// 	print_r(json_encode($this->masterservicemodel->GetPenyebab($where)));
	// }


	public function listpenyebab(){

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "TARIK API MASTER SERVICE PENYEBAB ";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." TARIK API MASTER SERVICE PENYEBAB";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';

		$response = $this->masterservicemodel->GetPenyebab($this->input->get());
		$result = json_decode($response);
		if($result->result =='success'){
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				$data['Masterpenyebab'] = $result->data;
				// print_r($data['Masterpenyebab']);
				// die();
				$data_list=array();

				if(!empty($data['Masterpenyebab'])){
					$listdata=json_decode(json_encode($data['Masterpenyebab']));
					// print_r($listdata);
					// die();
					foreach($listdata as $key => $d) {


						if($_SESSION["can_update"]==true){

							$edit = "'".$d->kd_penyebab."','".$d->nm_penyebab."','".$d->is_active."'";
			                            
							$actions = '<button type="button" class="btn btn-dark" onclick="btn_edit('.$edit.')"><span class="glyphicon glyphicon-pencil"></span></button><button class="btn btn-danger-dark delete-btn" data-encodedelete="'.str_replace("=","",base64_encode($d->kd_penyebab)).'"><i class="glyphicon glyphicon-trash"></i></button>';
			                            
			            }else{
			                            	
			                $actions = '';
			                            
			            }
			                            

						$tamp=array();
						$tamp[]=rtrim($d->kd_penyebab ,' ');
						$tamp[]=rtrim($d->nm_penyebab ,' ');
						$tamp[]= ($d->is_active == 1 ? 'Aktif' : 'Tidak Aktif' );
						$tamp[]= rtrim($d->modified_by);
						$tamp[]= rtrim($d->modified_date);
						$tamp[]=$actions;
						

						$data_list[]=$tamp;
					}
					$total=$result->total;
				}else{
					$total=0;
				}


				if(!empty($this->input->get('sEcho'))){
					$secho = $this->input->get('sEcho');
				}else{
					$secho = 1;
				}


				$data_hasil['sEcho']=$secho;
				$data_hasil['iTotalRecords']=$total;
				$data_hasil['iTotalDisplayRecords']=$total;
				$data_hasil['aaData']=$data_list;

				print_r(json_encode($data_hasil));

			}else{

				$params['Remarks']="FAILED - ".$result->error;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				echo $result->error; die;

			}
	}



	public function perbaikan(){
		$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		$username = $_SESSION['logged_in']['username'];
		$submit = $this->input->post('submit');
		$btn = $this->input->get('btn');
		if($btn!=''){
			$submit = $btn;
		}
		if($submit==''){
			$export = (int)$this->input->get('export');
			if($export==1){
				$where = array(
					// 1 => '1',
					// 'is_active' => 1,
				);
				$rekap = $this->masterservicemodel->GetPerbaikan($where);
				$body = array(
					'rekap' => $rekap,
					'title' => 'Rekap Master Service Perbaikan',
				);
				$this->load->view('template_xls/MasterServicePerbaikanXls',$body);
			}	
			else{
				$where = array(
					1 => '1',
					// 'is_active' => 1,
				);
				$GetPerbaikan = $this->masterservicemodel->GetPerbaikan($where);
				$body = array(
					'Perbaikan' => $GetPerbaikan,
				);
				$this->RenderView('MasterServicePerbaikanView',$body);
			}

			
		}
		else{
			$kode = $this->input->post('kode');
			$nama = $this->input->post('nama');
			$status = (int)$this->input->post('status');
			
			$msg = "";
			if($submit=='Tambah'){
					if($nama!=''){
						$data = array(
							'kd_perbaikan' => $this->masterservicemodel->GetAutoNumberperbaikan(),
							'nm_perbaikan' => strtoupper($nama),
							'is_active' => 1, 
							'created_by' => $username,
							'created_date' => date('Y-m-d H:i:s'),
							'modified_by' => $username,
							'modified_date' => date('Y-m-d H:i:s'),
						);
						$result = $this->masterservicemodel->Addperbaikan($data);
						if($result){
							$msg = "success";
						}
					}else{
						$msg='error';
					}
			}else if($submit=='Ubah'){
					$msg = "Ubah data gagal";
					if($kode!='' && $nama!=''){
						$where = array(
							'kd_perbaikan' => $kode,
						);
						$data = array(
							'nm_perbaikan' => strtoupper($nama),
							'is_active' => $status,
							'modified_by' => $_SESSION['logged_in']['username'],
							'modified_date' => date('Y-m-d H:i:s'),
						);
						$result = $this->masterservicemodel->Updateperbaikan($where,$data);
						if($result){
							$msg = "success";
						}
					}else{
						$msg='error';
					}
			}else if($submit=='Delete'){

					$kode = $this->input->post('kode');
					if($kode!=''){
						// $where = array(
						// 	'kd_perbaikan' => $kode,
						// );
					 	$msg = $this->masterservicemodel->Deleteperbaikan($kode);
					}else{
						$msg='error';
					}
			}

			echo $msg;

// 			$url = base_url()."masterservice/perbaikan";
// 			echo <<<HTML
// 			<script>
// 				alert('{$msg}');
// 				window.location.href="{$url}";
// 			</script>
// HTML;
		}
	}

	public function listperbaikan(){

		$params = array();			
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "TARIK API MASTER SERVICE PERBAIKAN ";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." TARIK API MASTER SERVICE PERBAIKAN";
		$params['Remarks'] = "";
		$params['RemarksDate'] = 'NULL';

		$response = $this->masterservicemodel->Getperbaikan($this->input->get());
		$result = json_decode($response);
		if($result->result =='success'){
				$params['Remarks']="SUCCESS";
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);

				$data['MasterPerbaikan'] = $result->data;
				// print_r($data['MasterPerbaikan']);
				// die();
				$data_list=array();

				if(!empty($data['MasterPerbaikan'])){
					$listdata=json_decode(json_encode($data['MasterPerbaikan']));
					// print_r($listdata);
					// die();
					foreach($listdata as $key => $d) {


						if($_SESSION["can_update"]==true){

							$edit = "'".$d->kd_perbaikan."','".$d->nm_perbaikan."','".$d->is_active."'";
			                            
							$actions = '<button type="button" class="btn btn-dark" onclick="btn_edit('.$edit.')"><span class="glyphicon glyphicon-pencil"></span></button><button class="btn btn-danger-dark delete-btn" data-encodedelete="'.str_replace("=","",base64_encode($d->kd_perbaikan)).'"><i class="glyphicon glyphicon-trash"></i></button>';
			                            
			            }else{
			                            	
			                $actions = '';
			                            
			            }
			                            

						$tamp=array();
						$tamp[]=rtrim($d->kd_perbaikan ,' ');
						$tamp[]=rtrim($d->nm_perbaikan ,' ');
						$tamp[]= ($d->is_active == 1 ? 'Aktif' : 'Tidak Aktif' );
						$tamp[]= rtrim($d->modified_by);
						$tamp[]= rtrim($d->modified_date);
						$tamp[]=$actions;
						

						$data_list[]=$tamp;
					}
					$total=$result->total;
				}else{
					$total=0;
				}


				if(!empty($this->input->get('sEcho'))){
					$secho = $this->input->get('sEcho');
				}else{
					$secho = 1;
				}


				$data_hasil['sEcho']=$secho;
				$data_hasil['iTotalRecords']=$total;
				$data_hasil['iTotalDisplayRecords']=$total;
				$data_hasil['aaData']=$data_list;

				print_r(json_encode($data_hasil));

			}else{

				$params['Remarks']="FAILED - ".$result->error;
				$params['RemarksDate'] = date("Y-m-d H:i:s");
				$this->ActivityLogModel->update_activity($params);
				echo $result->error; die;

			}
	}

	public function SelectJenisBarang(){
		$result = $this->masterservicemodel->SelectJenisBarang();
		print_r(json_encode($result));
	}
	public function SelectKerusakan(){
		$result = $this->masterservicemodel->SelectKerusakan($this->input->get('jenisbarang'));
		print_r(json_encode($result));
	}
	public function SelectPenyebab(){
		$result = $this->masterservicemodel->SelectPenyebab($this->input->get('jenisbarang'),$this->input->get('kerusakan'));
		print_r(json_encode($result));
	}
	public function SelectPerbaikan(){
		$result = $this->masterservicemodel->SelectPerbaikan($this->input->get('jenisbarang'),$this->input->get('kerusakan'),$this->input->get('penyebab'));
		print_r(json_encode($result));
	}
	

	public function SelectJenisBarangv2(){
		$result = $this->masterservicemodel->SelectJenisBarangv2();
		print_r(json_encode($result));
	}
	public function SelectKerusakanv2(){
		$result = $this->masterservicemodel->SelectKerusakanv2();
		print_r(json_encode($result));
	}
	public function SelectPenyebabv2(){
		$result = $this->masterservicemodel->SelectPenyebabv2();
		print_r(json_encode($result));
	}
	public function SelectPerbaikanv2(){
		$result = $this->masterservicemodel->SelectPerbaikanv2();
		print_r(json_encode($result));
	}

	//----------------------------------------------Yudha-----------------------------------------------//
	
	public function JenisBarang($action='',$get_code=''){
		if($action==''){
			$this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
			if($_SESSION['can_read']==true){
				$data['detail'] = $this->masterservicemodel->GetData($get_code);	
				$data['module'] = 'list';
				$this->RenderView('MasterServiceViewJenisBarang',$data);
			}
		}else if(($action=='edit') && !empty($get_code) && $_SESSION['can_update']==true){

			if(!empty($_POST)){

				if(!empty($this->input->post('aktif'))){ 
					$active = $this->input->post('aktif');
				}else{
					$active = 0;
				}

				$data['kode'] = $this->input->post('kode');
				$data['jns_brg'] = $this->input->post('jns_brg');
				$data['aktif'] = $active;
				$data['merk'] = $this->input->post('merk');
				$data['jnsbrg'] = $this->input->post('jnsbrg');
				$status = $this->masterservicemodel->update($data);
				echo $status;
				die();
			}

			$data['merk'] = json_decode(file_get_contents($this->API_URL.'/MasterService/GetsMerek?api=APITES'), true);
			// die(json_encode($data['merk']));
			// die($get_code);
			$data['detail'] = $this->masterservicemodel->GetData($get_code);
			// die(json_encode($data['detail']));
			$data['module'] = 'edit';
			$this->RenderView('MasterServiceViewJenisBarang',$data);
		}else if(($action=='view') && !empty($get_code) && $_SESSION['can_read']==true){
			$data['merk'] = json_decode(file_get_contents($this->API_URL.'/MasterService/GetsMerek?api=APITES'), true);
			// die(json_encode($data['merk']['data'])."<br>");
			$data['detail'] = $this->masterservicemodel->GetData($get_code);
			// die(json_encode($data['detail']));
			// echo(json_encode($data['detail'])."<br>");
			$data['module'] = 'view';
			$this->RenderView('MasterServiceViewJenisBarang',$data);
		}else if(($action=='add') && $_SESSION['can_create']==true){

			$data['status'] = '';
			if(!empty($_POST)){
				$data['kode'] = $this->input->post('kode');
				$data['jns_brg'] = $this->input->post('jns_brg');
				$data['aktif'] = $this->input->post('aktif');
				$data['merk'] = $this->input->post('merk');
				$data['jnsbrg'] = $this->input->post('jnsbrg');
				$status = $this->masterservicemodel->add($data);
				echo $status;
				die();
			}

			$data['merk'] = json_decode(file_get_contents($this->API_URL.'/MasterService/GetsMerek?api=APITES'), true);
			$data['module'] = 'add';
			$this->RenderView('MasterServiceViewJenisBarang',$data);
		}
	}

	public function listmasterservice(){
		print_r(json_encode($this->masterservicemodel->GetList()));
	}

	public function GetListJenisBarang(){
		$URLAPI = $this->API_URL.'/MasterService/GetListJenisBarang?api=APITES';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $URLAPI);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $this->input->post());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($curl);
		print_r($response);
	}

	public function rekap($rekap=''){
		if($rekap=='JenisBarang'){

			$list = $this->masterservicemodel->GetListAll();

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet(0);
			$sheet->setTitle('Rekap Jenis Barang');
			$sheet->setCellValue('A1', 'REKAP JENIS BARANG');
			$sheet->getStyle('A1')->getFont()->setSize(20);


			$currrow = 1;
			$currcol = 1;


			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'REKAP JENIS BARANG');
			$sheet->mergeCells('A1:G1');
			$sheet->getStyle('A1:G1')->getAlignment()->setHorizontal('center');
			$currcol = 1;
			$currrow = 3;

			$spreadsheet->getActiveSheet()->getStyle('A3:G3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('eaeaea');

			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'KODE JENIS BARANG');
			$currcol ++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'NAMA JENIS BARANG');
			$currcol ++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'MERK');
			$currcol ++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'JENIS BARANG');
			$currcol ++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'ACTIVE');
			$currcol ++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'MODIFIED BY');
			$currcol ++;
			$sheet->setCellValueByColumnAndRow($currcol, $currrow, 'MODIFIED DATE');
			$currcol ++;
			$currrow++;

			foreach ($list as $key => $l) {
				$currcol=1;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l->kd_jnsbrg);
				$currcol ++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l->jns_brg);
				$currcol ++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l->merk);
				$currcol ++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l->jnsbrg);
				$currcol ++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l->active);
				$currcol ++;
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, $l->modified_by);
				 $currcol ++;
				// $sheet->setCellValueByColumnAndRow($currcol, $currrow, $l->modified_date);
				$sheet->setCellValueByColumnAndRow($currcol, $currrow, '');
				$currcol ++;
				$currrow++;
			}


			
			for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
			    $sheet->getColumnDimension($i)->setAutoSize(TRUE);
			}

			$filename='Rekap Jenis Barang ['.date('Y-m-d H-i-s').']'; //save our workbook as this file name
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
			header('Cache-Control: max-age=0');
			ob_end_clean();
	        $writer->save('php://output');	// download file 
	        exit();
	    }


	}

	public function DeleteData(){
		if(!empty($this->input->post('kode')) && $_SESSION["can_delete"]==true){
			$result = $this->masterservicemodel->DeleteData($this->input->post('kode'));
			echo $result;
		}else{
			echo 'error';
		}
	}
}
?>
