<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

	function GetKodeSupplier()
	{
		$supl = "x";
		if ($_SESSION["logged_in"]["isUserPabrik"]==1) {
			if (strtoupper($_SESSION["logged_in"]["useremail"])=="USER@PABRIK.KG" || strtoupper($_SESSION["logged_in"]["useremail"])=="QR@PABRIK.KG") {
				$supl = "JKTK001";
			} else if (strtoupper($_SESSION["logged_in"]["useremail"])=="USER@PABRIK.PTRI" || strtoupper($_SESSION["logged_in"]["useremail"])=="QR@PABRIK.PTRI") {
				$supl = "JKTR001";
			} else if (strtoupper($_SESSION["logged_in"]["useremail"])=="USER@PABRIK.TIN" || strtoupper($_SESSION["logged_in"]["useremail"])=="QR@PABRIK.TIN") {
				$supl = "JKTT003";
			}
		}
		return $supl;
	}