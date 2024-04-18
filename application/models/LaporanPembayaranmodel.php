<?php
	class LaporanPembayaranmodel extends CI_Model
	{
		public function __construct()
		{
			parent::__construct();
			$this->APIKEY = 'APITES';
		}

		function getlist($url, $post) {

			$ch = curl_init($url);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

			$response = curl_exec($ch);

			if (curl_errno($ch)) {
			    echo 'Error: ' . curl_error($ch);
			}

			curl_close($ch);

			return $response;

		}
	}
?>