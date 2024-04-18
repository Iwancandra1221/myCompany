<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
class TunjanganPrestasi extends MY_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('BranchModel');
		$this->load->model('EmpPositionModel');
		$this->load->model('TunjanganPrestasiModel');
	}
	
	public function index()
	{
		
		//$data['wilayahSalesman'] = [];
		//$data['levelSalesman'] = [];
		$data['server'] = base_url();
		$data['branch'] = $this->BranchModel->GetList();
		$data['empPosition'] = $this->EmpPositionModel->GetDataList();
		$data['omzet'] = $this->GetOmzet();
		$data['kpi'] = $this->GetKPI();
		//echo '<pre>';
		//echo print_r($data);
		//echo '</pre>';
		$this->RenderView('TunjanganPrestasiView',$data);
	}

	public function GetOmzet() {
		return array((object)array('key' => 1, 'value' => 'NORMAL'));
	}

	public function GetKPI() {
		return array((object)array('key' => 1, 'value' => 'NORMAL'),(object)array('key'=> 0, 'value' => 'TPOMZET'));
	}

	public function View()
	{
		$data = $this->TunjanganPrestasiModel->ListData();
		echo json_encode($data);
	}

	public function Save()
	{
		$data = json_decode(trim(file_get_contents("php://input")));
		$result = $this->TunjanganPrestasiModel->Crud($data);
		echo json_encode($result);
	}
}
?>