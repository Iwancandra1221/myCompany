<?php
	Class GzipDecodeModel extends CI_Model
	{
		public function _decodeGzip($str){
			$decodedData = @gzdecode($str);

			if ($decodedData !== false) {
				return json_decode($decodedData);
			} else {
				return json_decode($str);
			}
		}

		public function _decodeGzip_true($str){
			$decodedData = @gzdecode($str);

			if ($decodedData !== false) {
				return json_decode($decodedData, true);
		
			} else {
				return json_decode($str, true);
			}
		}
	}
?>