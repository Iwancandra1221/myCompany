<?php

class LaporanPajakModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('GzipDecodeModel');
		$this->load->model('ConfigSysModel');
		$this->API_URL = $this->ConfigSysModel->Get()->webapi_url;
	}
  
	function TipeFaktur()
	{
		$TipeFaktur = json_decode(file_get_contents($this->API_URL."/TipeFaktur/GetsList?api=APITES"),true);
		return $TipeFaktur;
	} 

	function Dealer($partner_type='',$wilayah='')
	{
		set_time_limit(60);

		$data  = '&partner_type='.$partner_type;
		$data .= '&wilayah='.$wilayah;

		$url = str_replace(' ','%20',$this->API_URL."/MsDealer/GetListDealers?api=APITES".$data);

		$ch = curl_init();

	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_URL, $url);

	    $data = curl_exec($ch);
	    curl_close($ch);

	    $data = $this->GzipDecodeModel->_decodeGzip($data);

	    return $data;

	} 

	function Gudang($wilayah)
	{

		set_time_limit(60);

		$data = '&wilayah='.$wilayah;

		$url = str_replace(' ','%20',$this->API_URL."/MsWilayah/GudangList?api=APITES".$data);
		
		$ch = curl_init();

	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_URL, $url);

	    $data = curl_exec($ch);
	    curl_close($ch);

	    return $data;

	} 

	function Wilayah()
	{
		$wilayah = json_decode(file_get_contents($this->API_URL."/MsWilayah/GetWilayahLaporanPajak?api=APITES"),true);
		return $wilayah;
	} 

	function database($db=''){
		$this->db->where('DatabaseId',$db);
		$this->db->select('AlamatWebService');
		$res = $this->db->get('MsDatabase');
		if ($res->num_rows() > 0) {
			return $res->row()->AlamatWebService;
		} else{
			return null;
		}
	}
}
?>