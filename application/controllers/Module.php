<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Module extends MY_Controller {

	public $alert = "";

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MODULE";
		$params['TrxID'] = date("YmdHis");
		$params['Description'] = $_SESSION["logged_in"]["username"]." BUKA MENU MODULE ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		
		$checkAccess = $this->ModuleModel->CheckAccess($this->uri->segment(1), $this->uri->segment(2));
		$this->alert = "";

		$submit = $this->input->post('submit');

		if($submit==''){
			// ActivityLog
			$params = array();   
			$params['LogDate'] = date("Y-m-d H:i:s");
			$params['UserID'] = $_SESSION["logged_in"]["userid"];
			$params['UserName'] = $_SESSION["logged_in"]["username"];
			$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
			$params['Module'] = "MODULE";
			$params['TrxID'] = date("YmdHis");
			$params['Description'] = $_SESSION["logged_in"]["username"]." VIEW MODULE ";
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->insert_activity($params);

	        $data["parents"] = null;// $this->ModuleModel->GetParentList();
	        $data["alert"] = $this->alert;

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->RenderView('ModuleView',$data);
		}
		else{
			//filter / tampilin data ke datatable
			$orderArray = $this->input->post('order');

			$data = array(
				'draw' => 0,
				'recordsTotal'=> 10,
				'recordsFiltered' => 10,
				'code' => 0,
				'msg' => '',
				'data' => array(),
			);
			$msg = '';
			
			$search = isset($_REQUEST['search']['value']) ? $_REQUEST['search']['value'] : '';
			$data['search'] = $search;


			$orderColumn=$orderArray;
			if(isset($orderColumn[0]['column'])!=''){
				$orderColumn = $orderColumn[0]['column'];
			}
			if($orderColumn==0) $orderColumnName='Jns_Brg';
			else if($orderColumn==1) $orderColumnName='Nm_Kerusakan';
			else if($orderColumn==2) $orderColumnName='Nm_Penyebab';
			else if($orderColumn==3) $orderColumnName='Nm_Perbaikan';
			else if($orderColumn==4) $orderColumnName='is_active';
			else if($orderColumn==5) $orderColumnName='modified_by';
			else if($orderColumn==6) $orderColumnName='modified_date';

			$order = "UserName asc";
			$draw = 1;
			if($orderColumnName!=''){
				$order = $orderColumnName.' '.$orderArray[0]['dir'];
			}
			if(isset($_REQUEST['draw'])){
				$draw = $_REQUEST['draw'];
			}
			if(isset($_REQUEST['start']) && isset($_REQUEST['length'])){
				$start = $_REQUEST['start'];
				$length = $_REQUEST['length'];
				$limit = (object) ['limit'=>$length,'offset'=>$start];
			}
			$where = array();

			if($search!=''){
				$where["(
					cte.module_id LIKE '%".$search."%' OR 
					cte.module_name LIKE '%".$search."%' OR 
					cte.module_type LIKE '%".$search."%' OR 
					cte.description LIKE '%".$search."%'
				)"] = null;
			}

			$top = $limit->limit;
			$offset = $limit->offset;

			$modules = array();
			//
			$result = $this->ModuleModel->getModuleList_v2($where,$order,$top,$offset);
			if($result!=null && $result['data']!=null){
				foreach($result['data'] as $value){
					$value['edit'] = '';
					$value['ganti_kode'] = '';
					$value['hapus'] = '';
					$value['chkbox'] = '';


					
					
					if($_SESSION["can_update"] == 1){
						$checked = ($value['is_active'] == 1 ? 'checked' : '');
						$value['chkbox'] = <<<HTML
						<input type="checkbox" {$checked} onclick="updateIsActive('{$value['module_id']}',event)">
HTML;
						
						$parentModuleId = $value['parent_module_id']=='' ? $value['module_id'] :  $value['parent_module_id'];
						$value['edit'] = <<<HTML
						<a href='#' data-href='#' data-toggle='modal' data-target='#update_modal'
							onclick="loadMapData('{$value['module_id']}','{$value['module_name']}','{$value['module_type']}','{$parentModuleId}','{$value['is_active']}','{$value['controller']}','{$value['description']}','{$value['position']}')">
							<i class='glyphicon glyphicon-pencil'></i>
						</a>
HTML;

						$value['ganti_kode'] = <<<HTML
						<a href='#' data-href='#' data-toggle='modal' data-target='#update_modal_2' onclick="loadMapData2('{$value['module_id']}')">
							<i class='glyphicon glyphicon-pencil'></i>
						</a>
HTML;
					}
					if ($_SESSION["can_delete"]==1) {
						$baseUrl = base_url();
						$value['hapus'] = <<<HTML
						<a href='#' data-href='{$baseUrl}Module/delete?id={$value["module_id"]}' data-toggle='modal' data-target='#confirm-delete' data-record-title='{$value["module_id"]}'><i class='glyphicon glyphicon-trash'></a>
HTML;
					}
					$value['is_active'] = $value['is_active'] == 1 ? 'YA' : 'TIDAK';
					
					
					$data['data'][] = $value;
				}
				
				$data['code'] = 1;
				$data['draw'] = $draw;
				$data['recordsTotal'] = $result['count'];
				$data['recordsFiltered'] = $result['count'];
			}
			else
			{
				
				$data['code'] = 1;
				$data['draw'] = $draw;	
				$data['recordsTotal'] = 0;
				$data['recordsFiltered'] = 0;
			}
	        $data['msg'] = $msg;
			$json = json_encode($data);
			echo $json;
		}
			
	}

	public function getList()
	{
        $data['result'] = $this->MasterDbModel->getList($_SESSION['logged_in']['branch_id']); 
		echo json_encode($data['result']);
	}

	public function get($kodemodul)
	{
        $data['result'] = $this->MasterDbModel->getList($_SESSION['logged_in']['branch_id']); 
		echo json_encode($data['result']);
	}
	public function UpdateIsActive(){
		$data = array(
			'code' => 0,
			'msg' => '',
			'data' => array(),
		);

		$submit = $this->input->post('submit');
		$moduleId = $this->input->post('module_id');
		$isActive =$this->input->post('is_active');

		$msg = "failed";
		if($submit!=''){
			$result = $this->ModuleModel->updateStatus($moduleId,$isActive);
			if($result){
				$msg = "sukses";
				$data['code'] = 1;
			}
		}


		$data['msg'] = $msg;
		$json = json_encode($data);
		echo $json;
	}
	public function DropdownParentList(){ 
		$jenis = $this->input->get('jenis');   
		$data = $this->ModuleModel->GetParentList_v2($jenis);  
		$json = json_encode($data);
		echo $json;
	}
	public function insert()
	{
		
       	$lanjut = true;
       	$post = $this->PopulatePost();

		$kodemodul = $post["txtKodeModule"];
		$namamodule = $post['txtNamaModule'];
		$jenis = $post['selJenis'];
		$controller = $post['txtNamaCtr'];
		$description = $post['txtKeterangan'];
		if(isset($post['chkAktif'])){
			$is_active = $post['chkAktif'];
		}
		else{
			$is_active = 0;
		}
		$parentmodul = $post['selParent'];
		$position = $post['Position'];

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MODULE";
		$params['TrxID'] = $kodemodul;
		$params['Description'] = $_SESSION["logged_in"]["username"]." INSERT MODULE ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		//check data kosong
		if($kodemodul == '' or $namamodule == ''){

			// ActivityLog Update FAILED
			$params['Remarks']="FAILED - Insert Error";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->alert = "Insert Error: Kode dan Nama Module Tidak Boleh Kosong!";
			$lanjut = false;
		}
		if($lanjut) {
			//check kode sudah digunakan / belum
			$temp = $this->ModuleModel->get($kodemodul);
			
			if($temp){
				$this->alert = "Insert Error: Kode Module Sudah Digunakan!";
				$lanjut=false;
			}else if($jenis!=='PARENT' && !empty($parentmodul)){
				$checkparent = $this->ModuleModel->get($parentmodul);
				if($jenis=='CHILD' && $checkparent[0]->module_type!=='PARENT'){
					$this->alert = "Insert Error: Jenis Module Harus PARENT!";
					$lanjut=false;
				}else if($jenis=='GRANDCHILD' && $checkparent[0]->module_type!=='CHILD'){
					$this->alert = "Insert Error: Jenis Module Harus CHILD!";
					$lanjut=false;
				}else if($jenis=='GREAT-GRANDCHILD' && $checkparent[0]->module_type!=='GRANDCHILD'){
					$this->alert = "Insert Error: Jenis Module Harus GRANDCHILD!";
					$lanjut=false;
				}
			}
		}
		if($lanjut) {
			$now = date('Y/m/d h:i:s A');
			$data = array (
	            'module_id' => $kodemodul,
	            'module_name'  => $namamodule,
	            'module_type'=> $jenis,
	            'position' => $position,
	            'controller' => $controller,
	            'description' => $description,
	            'is_active' => $is_active,
				'parent_module_id' => $parentmodul,
				'created_by' => $_SESSION['logged_in']['username'],
				'created_date' => $now
	        );
	        
	        $this->ModuleModel->addData($data);

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->alert = "Insert Module Berhasil";
	    }
		$url = base_url().'Module';
		echo <<<HTML
		<script>
			alert("{$this->alert}");
			window.location.href="{$url}";
		</script>
HTML;
    }

    public function update()
	{
		$post = $this->PopulatePost();
		$kodemodul = $post["utxtKodeModule"];
		$namamodule = $post['utxtNamaModule'];
		$jenis = $post['uselJenis'];
		$parentmodul = $post['uselParent'];
		$position = $post['uPosition'];
		$controller = $post['utxtNamaCtr'];
		$description = $post['utxtKeterangan'];

		if(isset($post['uchkAktif'])){
			$is_active = $post['uchkAktif'];
		}else{
			$is_active = 0;
		}

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MODULE";
		$params['TrxID'] = $kodemodul;
		$params['Description'] = $_SESSION["logged_in"]["username"]." UPDATE MODULE ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		$now = date('Y/m/d h:i:s A');
		$data = array (
            'module_name'  => $namamodule,
            'module_type'=> $jenis,
            'position' => $position,
            'controller' => $controller,
            'description' => $description,
            'is_active' => $is_active,
			'parent_module_id' => $parentmodul,
			'created_by' => $_SESSION['logged_in']['username'],
			'created_date' => $now
        );

        $this->ModuleModel->updateData($data,$kodemodul);

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);
		
		$this->alert = "Update Module Berhasil";
		$url = base_url().'Module';
		echo <<<HTML
		<script>
			alert("{$this->alert}");
			window.location.href="{$url}";
		</script>
HTML;
    }

    public function delete()
	{
		$dataid = $this->input->get('id');
		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MODULE";
		$params['TrxID'] = $dataid;
		$params['Description'] = $_SESSION["logged_in"]["username"]." DELETE MODULE ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

        $this->ModuleModel->deleteData($dataid);

		// ActivityLog Update SUCCESS
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->update_activity($params);

		$this->alert = "Delete Module Berhasil";
		$url = base_url().'Module';
		echo <<<HTML
		<script>
			alert("{$this->alert}");
			window.location.href="{$url}";
		</script>
HTML;
    }

    public function updateKode()
	{
		$post = $this->PopulatePost();
		$kodelama = $post["utxtKodeModuleOld"];
		$kodebaru = $post['utxtNamaModuleNew'];
		$checkKodeBaru = $this->ModuleModel->get("kodebaru");

		// ActivityLog
		$params = array();   
		$params['LogDate'] = date("Y-m-d H:i:s");
		$params['UserID'] = $_SESSION["logged_in"]["userid"];
		$params['UserName'] = $_SESSION["logged_in"]["username"];
		$params['UserEmail'] = $_SESSION["logged_in"]["useremail"];
		$params['Module'] = "MODULE";
		$params['TrxID'] = $kodebaru;
		$params['Description'] = $_SESSION["logged_in"]["username"]." UPDATE KODE MODULE ";
		$params['Remarks']="SUCCESS";
		$params['RemarksDate'] = date("Y-m-d H:i:s");
		$this->ActivityLogModel->insert_activity($params);

		if (count($checkKodeBaru)>0) {
			// ActivityLog Update FAILED
			$params['Remarks']="FAILED - Update Kode Gagal";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->alert = "Update Kode Gagal: Kode Pengganti Sudah Dipakai";
		} else {
	        $this->ModuleModel->updateKode($kodelama, $kodebaru);

			// ActivityLog Update SUCCESS
			$params['Remarks']="SUCCESS";
			$params['RemarksDate'] = date("Y-m-d H:i:s");
			$this->ActivityLogModel->update_activity($params);

			$this->alert = "Update Kode Module Berhasil";
		}
		$url = base_url().'Module';
		echo <<<HTML
		<script>
			alert("{$this->alert}");
			window.location.href="{$url}";
		</script>
HTML;
    }    
}