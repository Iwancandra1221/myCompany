<?php
	class LaporanTagihanModel extends CI_Model
	{
		public function __construct()
		{
			parent::__construct();
		}

		public function getList($data){
			$api_url = $data['API_BKT'] . "/LaporanTagihan/List";

		    $data = array(
		        'svr' => $_SESSION['conn']->Server,
		        'db' => $_SESSION['conn']->Database,
		        'api' => $data['APIKEY'],
		        'awal' => $data['awal'],
				'akhir' => $data['akhir'],
				'jenis_tagihan' => $data['jenis_tagihan'],
				'no_tagihan' => $data['no_tagihan'],
				'wilayah' => $data['wilayah'],
				'dealer' => $data['dealer']
		    );

		    $data = http_build_query($data);

		    $ch = curl_init();

		    curl_setopt($ch, CURLOPT_URL, $api_url);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		    $response = curl_exec($ch);
		    curl_close($ch);

			return json_decode($response);
		    
		}

		public function getListDetail($no_penerimaan,$data){
			$api_url = $data['API_BKT'] . "/LaporanTagihan/ListDetail";

		    $data = array(
		        'svr' => $_SESSION['conn']->Server,
		        'db' => $_SESSION['conn']->Database,
		        'api' => $data['APIKEY'],
		        'no_penerimaan' => $no_penerimaan
		    );

		    $data = http_build_query($data);

		    $ch = curl_init();

		    curl_setopt($ch, CURLOPT_URL, $api_url);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		    $response = curl_exec($ch);
		    curl_close($ch);

			return json_decode($response);
		}

		public function getListbbt($data){
			$api_url = $data['API_BKT'] . "/LaporanTagihan/ListBBT";

		    $data = array(
		        'svr' => $_SESSION['conn']->Server,
		        'db' => $_SESSION['conn']->Database,
		        'api' => $data['APIKEY'],
		        'awal' => $data['awal'],
				'akhir' => $data['akhir'],
				'jenis_tagihan' => $data['jenis_tagihan'],
				'no_tagihan' => $data['no_tagihan'],
				'wilayah' => $data['wilayah'],
				'dealer' => $data['dealer']
		    );

		    $data = http_build_query($data);

		    $ch = curl_init();

		    curl_setopt($ch, CURLOPT_URL, $api_url);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		    $response = curl_exec($ch);
		    curl_close($ch);

			return json_decode($response);
		    
		}

		public function getListDetailBBT($no_bbt,$data){
			$api_url = $data['API_BKT'] . "/LaporanTagihan/ListDetailBBT";

		    $data = array(
		        'svr' => $_SESSION['conn']->Server,
		        'db' => $_SESSION['conn']->Database,
		        'api' => $data['APIKEY'],
		        'no_bbt' => $no_bbt
		    );

		    $data = http_build_query($data);

		    $ch = curl_init();

		    curl_setopt($ch, CURLOPT_URL, $api_url);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		    $response = curl_exec($ch);
		    curl_close($ch);

			return json_decode($response);
		}
	}
?>
