<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class approval extends NS_Controller 
{

	//MY_Controller with Session Checker
	//NS_Controller without Session Checker
	public $approvers = array();
	public $recipients= array();
	public $whatsapps = array();

	public function __construct()
	{
		parent::__construct();
		$this->load->model("approvalmodel");
	}

	public function TestConnection()
	{

		$data = array("result"=> "sukses",
					  "jam" => (int)date("H"));								

		echo json_encode($data);
	}    

	public function index()
	{	
		echo '<pre>';
		print_r($_POST);
		echo '</pre>';
	}

	public function insert()
	{
		if(!empty($this->input->get())){
			$post = $this->input->get();
		}else{
       		$post = $this->PopulatePost();
       	}

        //$post = $this->PopulatePost();
        //echo 'reegan';
        //echo '<pre>';
		//print_r($post);
		//echo '</pre>';
		//$insert = $this->approvalmodel->insert($post);
       	$this->doaction('insert', $post);
    }

	public function get(){
        $post = $this->PopulatePost();
		$get = $this->approvalmodel->get($post);
		echo json_encode($get);
	}

    public function delete()
	{	
		$post = $this->PopulatePost();
		$this->doaction('delete', $post);
	}

    public function approve()
	{	
		$post = $this->PopulatePost();
		$this->doaction('approve', $post);
	}

    public function reject()
	{	
		$post = $this->PopulatePost();
		$this->doaction('reject', $post);
	}

    public function cancel()
	{	
		$post = $this->PopulatePost();
		$this->doaction('cancel', $post);
	}

    public function cancelbyajax()
	{	
        $post = $this->PopulatePost();
		$CountApproved = 0;
		$CountRejected = 0;
		$CountCancelled = 0;
		$get = $this->approvalmodel->get($post);
		if (count($get)>0){
			for($i=0;$i<count($get);$i++) {
				//echo json_encode($get[$i]->ApprovalStatus);
				if ($get[$i]->ApprovalStatus=='APPROVED'){
					$CountApproved++;
				} else if ($get[$i]->ApprovalStatus=='REJECTED'){
					$CountRejected++;
				} else if ($get[$i]->ApprovalStatus=='CANCELLED'){
					$CountCancelled++;
				}
			}
			if ($CountApproved==0 && $CountRejected==0 && $CountCancelled==0) {
				$this->approvalmodel->cancel($post);
			}
		}
	}

    public function doaction($action, $post)
	{	

		$CountApproved = 0;
		$CountRejected = 0;
		$CountCancelled = 0;
        $CountUnprocessed = 0;
		$ApprovalNeeded = 0;

		$lanjut = true;
		//Jika INSERT, Cek Dahulu UserEmail Approver Terdaftar di msuserhd 
		if ($action=="insert") {
			$approver1 = $this->UserModel->Get($post['ApprovedBy']);		//Cek by UserID
			$approver2 = $this->UserModel->Get2($post['ApprovedByEmail']);		//Cek by UserEmail
			if ($approver1==null && $approver2==null) {
				$lanjut = false;
				$x["pesan"] = "Useremail Approver ".$post['ApprovedByEmail']." Tidak Terdaftar di MyCompany. Request Gagal Disimpan di MyCompany";
			} else {

			}
		}

		if ($lanjut==true) {
			$get = $this->approvalmodel->get($post);
			if (count($get)>0){
				for($i=0;$i<count($get);$i++) {
					//echo json_encode($get[$i]->ApprovalStatus);
					if ($get[$i]->ApprovalStatus=='APPROVED'){
						$CountApproved++;
					} else if ($get[$i]->ApprovalStatus=='REJECTED'){
						$CountRejected++;
					} else if ($get[$i]->ApprovalStatus=='CANCELLED'){
						$CountCancelled++;
					} else if ($get[$i]->ApprovalStatus=='UNPROCESSED'){
						$CountUnprocessed++;
					}
					$ApprovalNeeded = $get[$i]->ApprovalNeeded;
				}

				if ($action=='insert'){
					if ($CountApproved>0){
						$x["pesan"] = "Request Ini Sudah Diapprove, Tidak Dapat Di".$action." Lagi";
					} elseif ($CountRejected>0){
						$x["pesan"] = "Request Ini Sudah Direject, Tidak Dapat Di".$action." Lagi";
					} elseif ($CountCancelled>0){
						$x["pesan"] = "Request Ini Sudah Dicancel, Tidak Dapat Di".$action." Lagi";
					} elseif ($CountApproved==0 && $CountRejected==0 && $CountCancelled==0) {
						$delete = $this->approvalmodel->delete($post);
						if ($delete==true){
							$doaction = $this->approvalmodel->insert($post);
							if ($doaction==true) {
								$x["pesan"] = "Request Ini Berhasil Di".$action." Ulang";	
							} else {
								$x["pesan"] = "Request Ini Gagal Di".$action." Ulang";	
							}
						} else {
							$x["pesan"] = "Request Ini Gagal Di".$action."";	
						}
					}
				} elseif ($action=='approve'){
					if ($CountApproved>0 && $CountApproved==$ApprovalNeeded){
						$x["pesan"] = "Request Ini Sudah Diapprove Semua, Tidak Dapat Di".$action." Lagi";
					} elseif ($CountRejected>0){
						$x["pesan"] = "Request Ini Sudah Direject, Tidak Dapat Di".$action." Lagi";
					} elseif ($CountCancelled>0){
						$x["pesan"] = "Request Ini Sudah Dicancel, Tidak Dapat Di".$action." Lagi";
					} elseif (($CountApproved==0 && $CountRejected==0 && $CountCancelled==0) || ($CountApproved>0 && $CountApproved<$ApprovalNeeded)) {
						$doaction = $this->approvalmodel->approve($post);
						if ($doaction==true) {
							$x["pesan"] = "Request Ini Berhasil Di".$action."";	
						} else {
							$x["pesan"] = "Request Ini Gagal Di".$action."";	
						}
						$CountApproved++;
						if ($CountApproved==$ApprovalNeeded){
							$this->approvalmodel->updatebhaktiflag($post);
						}
					}
				} elseif ($action=='reject'){
					if ($CountApproved>0 && $CountApproved==$ApprovalNeeded){
						$x["pesan"] = "Request Ini Sudah Diapprove, Tidak Dapat Di".$action." Lagi";
					} elseif ($CountRejected>0){
						$x["pesan"] = "Request Ini Sudah Direject, Tidak Dapat Di".$action." Lagi";
					} elseif ($CountCancelled>0){
						$x["pesan"] = "Request Ini Sudah Dicancel, Tidak Dapat Di".$action." Lagi";
					} elseif (($CountApproved==0 && $CountRejected==0 && $CountCancelled==0) ||
								($CountApproved>0 && $CountApproved<$ApprovalNeeded)){ //jika level 1 sudah approved, tapi level 2 mau reject
						$doaction = $this->approvalmodel->reject($post);
						if ($doaction==true) {
							$x["pesan"] = "Request Ini Berhasil Di".$action."";	
						} else {
							$x["pesan"] = "Request Ini Gagal Di".$action."";	
						}
					}
				} elseif ($action=='cancel'){
					if ($CountApproved>0){
						$x["pesan"] = "Request Ini Sudah Diapprove, Tidak Dapat Di".$action." Lagi";
					} elseif ($CountRejected>0){
						$x["pesan"] = "Request Ini Sudah Direject, Tidak Dapat Di".$action." Lagi";
					} elseif ($CountCancelled>0){
						$x["pesan"] = "Request Ini Sudah Dicancel, Tidak Dapat Di".$action." Lagi";
					} elseif ($CountApproved==0 && $CountRejected==0 && $CountCancelled==0) {
						$doaction = $this->approvalmodel->cancel($post);
						if ($doaction==true) {
							$x["pesan"] = "Request Ini Berhasil Di".$action."";	
						} else {
							$x["pesan"] = "Request Ini Gagal Di".$action."";	
						}
					}
				} elseif ($action=='delete'){
					if ($CountApproved>0){
						$x["pesan"] = "Request Ini Sudah Diapprove, Tidak Dapat Di".$action." Lagi";
					} elseif ($CountRejected>0){
						$x["pesan"] = "Request Ini Sudah Direject, Tidak Dapat Di".$action." Lagi";
					} elseif ($CountCancelled>0){
						$x["pesan"] = "Request Ini Sudah Dicancel, Tidak Dapat Di".$action." Lagi";
					} elseif ($CountApproved==0 && $CountRejected==0 && $CountCancelled==0) {
						$doaction = $this->approvalmodel->delete($post);
						if ($doaction==true) {
							$x["pesan"] = "Request Ini Berhasil Di".$action."";	
						} else {
							$x["pesan"] = "Request Ini Gagal Di".$action."";	
						}
					}
				}

			} else {
				if ($action=='insert'){
					$doaction = $this->approvalmodel->insert($post);
					if ($doaction==true) {
						$x["pesan"] = "Request Ini Berhasil Di".$action."";	
					} else {
						$x["pesan"] = "Request Ini Gagal Di".$action."";	
					}
				} else {
					$x["pesan"] = "Request Ini Tidak Ditemukan, Tidak Dapat Di".$action."";
				}
			}
		}
		echo json_encode($x);
	}
	
	
    public function emailed()
	{
        $post = $this->PopulatePost();
		$doaction = $this->approvalmodel->emailed($post);
		if ($doaction==true) {
			echo 'sukses';
		} else {
			echo 'gagal';
		}
	}
	
	   public function updateisemailednextpriority($post)
	{
		$doaction = $this->approvalmodel->updateisemailednextpriority($post);
		if ($doaction==true) {
			echo 'sukses';
		} else {
			echo 'gagal';
		}
	}

	public function CancelTblApprovalBecauseTrxIsDeleted($post)
	{
		$doaction = $this->approvalmodel->CancelTblApprovalBecauseTrxIsDeleted($post);
		if ($doaction==true) {
			echo 'sukses';
		} else {
			echo 'gagal';
		}
	}

}