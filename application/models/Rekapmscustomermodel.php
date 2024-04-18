<?php
	Class Rekapmscustomermodel extends CI_Model{
		function datalistcustomer($data,$proses=''){

			$url = 'http://10.1.0.8:90/bktAPI/MsCustomer/list'.$proses;

			$ch = curl_init($url);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

			$response = curl_exec($ch);

			if (curl_errno($ch)) {
			    echo 'Error: ' . curl_error($ch);
			}

			curl_close($ch);

			return $response;

		}
	}
?>