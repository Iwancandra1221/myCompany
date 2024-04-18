<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 


//$fullUrl : url yang mau dipanggil
//$hostUrl : url yang ditampilkan ke user jika fullUrl gagal dipanggil
//$theAction: kegiatan yang sedang dilakukan
//$returnError: jika false, maka jika gagal langsung di-die, jika true pesan gagal direturn ke pemanggil.
function HttpGetRequest($url, $domainUrl, $theAction, $timeOut=6000)
{
	$msg = "";
	$streamContext = stream_context_create(
		array('http'=>array('timeout' => $timeOut))
	);
	// die($url);

	$HTTPRequest = "";
	$lanjut = true;
	for ($i=0; $i<2; $i++) {
		$HTTPRequest = @file_get_contents($url, false, $streamContext);
		if ($HTTPRequest==null) {
			$lanjut=false;
		} else {
			$lanjut=true;
			break;
		}
	}
	// die($HTTPRequest);

	if ($lanjut==false) {
		if ($theAction!="") {
			$msg .= "<b>".strtoupper($theAction)." GAGAL!!</b><br>"; 
		}
		$msg .= "Koneksi ke ".$domainUrl." Tidak Berhasil.<br>";
		$msg .= "Coba Kembali Beberapa Saat Lagi!<br>";
		$msg .= "Apabila gangguan berlangsung lebih dari 30 menit, Hubungi IT!!<br><br>";
		$msg .= "<a href='".site_url()."/Home'>Kembali ke Dashboard</a>";
		die($msg);
	} else {
		return $HTTPRequest;
	}
}


function HttpGetRequest_Ajax($url, $domainUrl, $theAction, $timeOut=6000)
{
	$msg = "";
	$streamContext = stream_context_create(
		array('http'=>array('timeout' => $timeOut))
	);

	$HTTPRequest = @file_get_contents($url, false, $streamContext);
	if ($HTTPRequest==null) {
		if ($theAction!="") {
			$msg .= "".strtoupper($theAction)." GAGAL!!\n"; 
		}
		$msg .= "Koneksi ke database Bhakti ".$domainUrl." Tidak Berhasil.\n";
		$msg .= "Coba Kembali Beberapa Saat Lagi!\n";
		$msg .= "Apabila gangguan berlangsung lebih dari 30 menit, Hubungi IT!!\n";

		return array("connected"=>false, "err"=>$msg, "result"=>array());
		
	} else {
		return array("connected"=>true, "err"=>"", "result"=>$HTTPRequest);
		// return $HTTPRequest;
	}
}


function HttpGetRequest_Ajax2($url, $domainUrl, $theAction, $timeOut=6000)
{
	$msg = "";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut);
	curl_setopt($ch, CURLOPT_ENCODING, 'gzip');

	$HTTPRequest = curl_exec($ch);

	if ($HTTPRequest === false) {
		$errorMsg = curl_error($ch);
		$errorCode = curl_errno($ch);

		if ($theAction != "") {
			$msg .= strtoupper($theAction) . " GAGAL!!\n";
		}

		$msg .= "Koneksi ke database Bhakti " . $domainUrl . " Tidak Berhasil.\n";
		$msg .= "Coba Kembali Beberapa Saat Lagi!\n";
		$msg .= "Error Code: " . $errorCode . "\n";
		$msg .= "Error Message: " . $errorMsg . "\n";

		return array("connected" => false, "err" => $msg, "result" => array());

	} else {
		curl_close($ch);
		return array("connected" => true, "err" => "", "result" => $HTTPRequest);
	}
}



function CURLPOST($URL='', $data=array())
{
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => http_build_query($data)
	));
	$response = curl_exec($curl);
	$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	$err = curl_error($curl);
	curl_close($curl);
	if($httpcode!=200){
		return array('result'=>'failed','error'=>'URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err);
	}
	else{
		return $response;
	}
}

function CURLPOSTJSON($URL='', $data=array())
{
	$curl = curl_init();
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 6000,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => json_encode($data),
		CURLOPT_HTTPHEADER => array("Content-type: application/json")
	));
	$response = curl_exec($curl);
	$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	$err = curl_error($curl);
	curl_close($curl);
	if($httpcode!=200){
		return json_encode(array('result'=>'failed','error'=>'URL:'.$URL.' HTTP CODE:'.$httpcode.' '.$err));
	}
	else{
		return $response;
	}
}