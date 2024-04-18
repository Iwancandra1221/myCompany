<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ActivityLog extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model("ActivityLogModel");
	}

	function index(){
		$data['module'] = $this->ActivityLogModel->module();
		// $data['datalist'] = $this->DataList($this->input->post());

		if(!empty($this->input->post())){ 
			$from = $this->input->post('from'); 
			$until = $this->input->post('until'); 
			$module = $this->input->post('module'); 
		}else{
			$from='';
			$until='';
			$module='';
		}

		$data['from'] = $from;
		$data['until'] = $until;
		$data['moduleselect'] = $module;
		$this->RenderView('ActivityLogView',$data);
	}

	function DataList(){


		$hasildata = $this->ActivityLogModel->dataactivitylog($this->input->get());

		$data_list=array();
		$data_hasil=array();
		$total=0;

		if(!empty($hasildata['data'])){
			foreach ($hasildata['data'] as $key => $r) {

				$list=array();

				$list[] 	= $r->LogDate;
				$list[] 	= $r->Module;
				$list[] 	= $r->Description;
				$list[] 	= $r->Remarks;
				$list[] 	= $r->RemarksDate;

				$hasil 	= '';

				if(!empty($r->RemarksDate)){
					$Logdate = date_format(date_create($r->LogDate), 'Y-m-d h:i:sa');
					$RemarksDate = date_format(date_create($r->RemarksDate), 'Y-m-d h:i:sa');
					$from  = new DateTime($Logdate);
					$until = new DateTime($RemarksDate);
					$jarak = $until->diff($from);


					$hasil = $jarak->h.':'.$jarak->i.':'.$jarak->s.':000';
				}

				$list[] 	= $hasil;

				$data_list[]=$list;
			}

			$total=$hasildata['total'];

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

	}
}
