<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class InfoEkspedisiSj extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}

	public function index()
	{
        if($this->uri->segment(2) != '')
	    	$ctrname = $this->uri->segment(1)."/".$this->uri->segment(2);
        else
	       	$ctrname = $this->uri->segment(1);

    	$data['access'] = $this->ModuleModel->getDetail($ctrname,$_SESSION['role']);
    	$data['title'] = "INFO EKSPEDISI";
		$this->RenderView('InfoEkspedisiSjView',$data);
	}

	public function PickTujuan(){
		$data['row'] = json_decode(file_get_contents($this->API_URL."/PabrikUpdate/GetListCabang?api=APITES"));
		$data['title'] = "Pilih Cabang";
		$this->SetTemplate('template/notemplate');
		$this->RenderView('CabangPickerView', $data);
	}

	public function UpdateSJ(){
		$tglkirim = $this->input->post('dateTglKirim');
		$namaexp = $this->input->post('txtNamaEks');
		$noexp = $this->input->post('txtNoEks');
		$nocontainer = $this->input->post('txtNoContainer');
		$sealcontainer = $this->input->post('txtContainerSeal');
		$noplatmobil = $this->input->post('txtPlatMobil');
		$supir = $this->input->post('txtNamaSupir');
		$tujuan = $this->input->post('txtTujuan');
		$nmtujuan = $this->input->post('NamaCust');
		$gudang = $this->input->post('AlmCust');

		$jumlahfaktur = $this->input->post('hdnJumlahFaktur');
		$updateby = $_SESSION['logged_in']['useremail'];

		$now = date("m/d/Y h:i:s", time());

		if($jumlahfaktur == 0)
			redirect('InfoEkspedisiSj?updateerror=Surat Jalan tidak ditemukan.');

		$updated = 0;

		for($i=0;$i<$jumlahfaktur;$i++){
			if(isset($_POST['chkNoFaktur_'.$i])){
				$updated ++;
				$tempfaktur = $_POST['noFaktur_'.$i];
				$apikey = 'APITES';

				$data = array (
					'No_Mutasi' => $tempfaktur,
		            'Tgl_Kirim' => $tglkirim,
		            'Tujuan'  => $tujuan,
		            'NmTujuan' => $nmtujuan,
		            'Kd_Gudang'=> $gudang,
		            'ExpName'  => $namaexp,
		            'ExpNO'=> $noexp,
		            'ContainerNO' => $nocontainer,
		            'ContainerSeal' => $sealcontainer,
		            'Sopir' => $supir,
		            'NoPlatMobil' => $noplatmobil,
		            'Flag' => 1,
		            'Updated_By' => $updateby,
		            'Updated_Date' => $now
				);

				$postdata = http_build_query(
			    	array(
				    	'apikey' => $apikey,
				        'data' => $data,
				        'nofaktur' => $tempfaktur
			   	 	)
				);
					
				$opts = array('http' =>
				    array(
				    	'header' => "User-Agent:MyAgent/1.0\r\n",
				        'method'  => 'POST',
				        'header'  => 'Content-type: application/x-www-form-urlencoded',
				        'content' => $postdata
				    )
				);

				$context  = stream_context_create($opts);

				$result = file_get_contents($this->API_URL."/PabrikUpdate/UpdateSJ", false, $context);
				if ($result != 'success'){
					//die($result);
					echo "some error occured";
					exit(1);
				}

			}
		}

		if($updated == 0)
			redirect('InfoEkspedisiSj?updateerror=Pilih minimal 1 faktur utk diupdate.');
		else
			redirect('InfoEkspedisiSj?updatesuccess=1');
	}

}