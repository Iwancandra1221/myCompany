<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class whatsappTest extends NS_Controller 
{
	public function __construct()
	{
		parent::__construct();
		// $this->load->helper("FileBase64");
		// $this->load->helper("FileBase64_helper");
	}

	public function index()
	{
		// die("whatsapp test");
	}

	public function RequestCL() 
	{	
		$src  = "OTHER";
		$phone=$this->input->get("phone");
		if ($phone=="") {
			$phone = "6281222345235";
		}

		// Divisi : *{{1}}* Dealer : *{{2}}* *{{3}}* Wilayah : *{{4}}* 
		// CL Permanent : *{{5}}* CL Temporary : *{{6}}* CL Direquest : * {{7}}* Kenaikan CL : *{{8}}* Catatan : *{{9}}* 
		// Diajukan Oleh : *{{10}}* Request CL berlaku s/d tgl *{{11}}* Proses melalui link berikut : {{12}} 

		$data = array();
		$data["phone"] = $phone;
		$data["paramType1"] = "text";
		$data["param1"] = "MISHIRIN";
		$data["paramType2"] = "text";
		$data["param2"] = "PT.CATUR SUKSES INTERNASIONAL";
		$data["paramType3"] = "text";
		$data["param3"] = "DMIC049";
		$data["paramType4"] = "text";
		$data["param4"] = "JAKARTA";
		$data["paramType5"] = "text";
		$data["param5"] = "1,234,567,890";
		$data["paramType6"] = "text";
		$data["param6"] = "0";
		$data["paramType7"] = "text";
		$data["param7"] = "1,334,567,890";
		$data["paramType8"] = "text";
		$data["param8"] = "100,000,000";
		$data["paramType9"] = "text";
		$data["param9"] = "urgent";
		$data["paramType10"] = "text";
		$data["param10"] = "INDAH";
		$data["paramType11"] = "text";
		$data["param11"] = "16-Jun-2022";
		$data["paramType12"] = "text";
		$data["param12"] = "http://mon.bhakti.co.id:90/myCompany/MsDealerApproval/";
		$data = json_encode($data);

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => site_url("api/waba/requestCL")."?src=".$src,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		echo($response);
	}

	public function PayrollTest() 			/*successful*/
	{	
		$src  = "OTHER";
		$phone=$this->input->get("phone");
		if ($phone=="") {
			$phone = "6281222345235";
		}

		// BODY
		// SLIP *{{1}}* Periode *{{2}}* Nama Karyawan : *{{3}}* UserID : *{{4}}*
		// FOOTER
		// Auto Whatsapp PT. Bhakti Idola Tama
		// BUTTONS
		// download
		// url: http://zen.bhakti.co.id/ReportSalary/downloadFile?id={{1}}

		$data = array();
		$data["phone"] = $phone;
		$data["paramType1"] = "text";
		$data["param1"] = "Gaji";
		$data["paramType2"] = "text";
		$data["param2"] = "Mei 2021";
		$data["paramType3"] = "text";
		$data["param3"] = "CANDRA PRAMITA WIJAYA PUTRA TEJA";
		$data["paramType4"] = "text";
		$data["param4"] = "0000";
		$data["buttomParamType1"] = "text";
		$data["buttonParam1"] = "12345";
		$data = json_encode($data);


		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => site_url()."api/waba/template4Params?src=".$src,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		echo($response);
	}

	public function PayrollTest2() 			/*successful*/
	{	
		$src  = "OTHER";
		// die($src);
		$phone=$this->input->get("phone");
		if ($phone=="") {
			$phone = "6281222345235";
		}

		// BODY
		// SLIP *{{1}}* Periode *{{2}}* Nama Karyawan : *{{3}}* UserID : *{{4}}*
		// FOOTER
		// Auto Whatsapp PT. Bhakti Idola Tama
		// BUTTONS
		// download
		// url: http://zen.bhakti.co.id/ReportSalary/downloadFile?id={{1}}

		$data = array();
		$data["phone"] = $phone;
		$data["paramType1"] = "text";
		$data["param1"] = "SLIP *GAJI*";
		$data["paramType2"] = "text";
		$data["param2"] = "Periode *AGUSTUS 2022*";
		$data["paramType3"] = "text";
		$data["param3"] = "Nama Karyawan : *CANDRA PRAMITA WIJAYA PUTRA TEJA*";
		$data["paramType4"] = "text";
		$data["param4"] = "UserID : *0000*";
		$data["paramType5"] = "text";
		$data["param5"] = "......";
		$data["paramType6"] = "text";
		$data["param6"] = "Download File Slip Dari Link Berikut:";
		$data["paramType7"] = "text";
		$data["param7"] = "http://zen.bhakti.co.id/uploads/salary/2022/08/1234.pdf";
		$data = json_encode($data);


		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => site_url()."api/waba/template7Params?src=".$src,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		echo($response);
	}

	public function EbillingTest() 			/*successful*/
	{	
		$src  = "OTHER";
		$phone=$this->input->get("phone");
		if ($phone=="") {
			$phone = "6281222345235";
		}

		// Dealer Yth, Berikut ringkasan e-Billing dan e-Faktur Pajak yang telah Kami kirim melalui email pada *{{1}}* 
		// Total Tagihan : *{{2}}* Jatuh Tempo : *{{3}}* No Kwitansi : *{{4}}* 
		// Pembayaran ditujukan ke Virtual Account BCA berikut ini : 
		// No Rekening : *{{5}}* Nama : *{{6}}* Nama Produk : *BHAKTIIDOLATAMA* 
		// Hormat Kami, *PT. Bhakti Idola Tama* 

		$data = array();
		$data["phone"] = $phone;
		$data["paramType1"] = "text";
		$data["param1"] = "06 Jun 2022";
		$data["paramType2"] = "text";
		$data["param2"] = "17,030,314";
		$data["paramType3"] = "text";
		$data["param3"] = "17-Jun-2022";
		$data["paramType4"] = "text";
		$data["param4"] = "0064/SRY/TR/0622";
		$data["paramType5"] = "text";
		$data["param5"] = "75111-00003-12089";
		$data["paramType6"] = "text";
		$data["param6"] = "KUNING LANGGENG SENTOSA, CV";
		$data = json_encode($data);

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => site_url("waba/ebilling")."?src=".$src,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		echo($response);
	}

	public function PaymentVATest() 		/*successful*/
	{	
		$src  = "OTHER";
		$phone=$this->input->get("phone");
		if ($phone=="") {
			$phone = "6281222345235";
		}

		// Dealer Yth, Pembayaran Anda sejumalah *Rp. {{1}}* telah kami terima pada tanggal *{{2}}* Pk. *{{3}}* untuk pembayaran atas tagihan berikut: 
		// Nama Dealer : *{{4}}* No. Kwitansi : *{{5}}* Jatuh Tempo : *{{6}}* 
		// Terima kasih atas kerjasamanya yang baik. *PT. Bhakti Idola Tama* 

		$data = array();
		$data["phone"] = $phone;
		$data["paramType1"] = "text";
		$data["param1"] = "17,000,000";
		$data["paramType2"] = "text";
		$data["param2"] = "30-May-2022";
		$data["paramType3"] = "text";
		$data["param3"] = "16:35:25 WIB";
		$data["paramType4"] = "text";
		$data["param4"] = "CV. BERKAT NIAGA MAKMUR";
		$data["paramType5"] = "text";
		$data["param5"] = "0076/BJM/TR/0522";
		$data["paramType6"] = "text";
		$data["param6"] = "02-JUN-2022";
		// $data["paramType7"] = "text";
		// $data["param7"] = "";
		// $data["paramType8"] = "text";
		// $data["param8"] = "";
		// $data["paramType9"] = "text";
		// $data["param9"] = "";
		// $data["paramType10"] = "text";
		// $data["param10"] = "";
		// $data["paramType11"] = "text";
		// $data["param11"] = "";
		// $data["paramType12"] = "text";
		// $data["param12"] = "";
		$data = json_encode($data);

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => site_url("waba/paymentVA")."?src=".$src,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		echo($response);
	}


	public function SendTemplateTest()
	{	
		$src  = "DEV";
		$phone=$this->input->get("phone");
		if ($phone=="") {
			$phone = "6285158285882";
		}

		$data = array();
		$data["phone"] = $phone;
		$data["paramType1"] = "text";
		$data["param1"] = "RAMA ABDILLAH mengajukan permohonan kenaikan Credit Limit sebagai berikut:";
		$data["paramType2"] = "text";
		$data["param2"] = "Divisi: *MISHIRIN*\nDealer: *PT.CATUR SUKSES INTERNASIONAL*\n*DMIC049*\nWilayah: *JAKARTA*\nCatatan:\n*URGENT*\n\n";
		$data["paramType3"] = "text";
		$data["param3"] = "CL Permanent: *1,234,567*\nCL Temporary: *0*\n\n";
		$data["paramType4"] = "text";
		$data["param4"] = "Request Credit Limit ini berlaku s/d Tgl 15-Jun-2022 23:59:59\n\n";
		$data["paramType5"] = "text";
		$data["param5"] = "Proses Request Melalui Link Berikut:\n\n";
		$data["paramType6"] = "text";
		$data["param6"] = "http://mon.bhakti.co.id:90/myCompany/MsDealerApproval/";
		$data["paramType7"] = "text";
		$data["param7"] = "";
		$data = json_encode($data);

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => site_url("waba/template6Params")."?src=".$src,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		echo($response);
	}

	public function SendMessageTest()
	{	
		$src = $this->input->get("src");
		$phone=$this->input->get("phone");
		if ($phone=="") {
			$phone = "6281284316750";
			// $phone = "6285245229492";
		}

		$data = [
			"chatId" => "",
			"phone" => $phone,
			"body" => "dikirim via & chat-api",
			"quotedMsgId" => "",
			"mentionedPhones" => ""
		];
		// die(site_url("sendMessageWA"));
		
		$data = json_encode($data);
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => site_url("sendMessageWA")."?src=".$src,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		echo($response);
	}
	

	public function SendFileTest()
	{
		$src = $this->input->get("src");
		$phone=$this->input->get("phone");
		if ($phone=="") {
			$phone = "6281222345235";
		}

		// $file = "C:\ebilling\pdf_billing\2022\02\20220217_PT_BANGUNAN_JAYA_PRIMA_BDGB014_BILLING.pdf";
		// die($type);
		$file = "C:\\ebilling\\pdf_billing\\2022\\02\\20220217_PT_BANGUNAN_JAYA_PRIMA_BDGB014_BILLING.pdf";
		$type = pathinfo($file, PATHINFO_EXTENSION);
		$data = file_get_contents($file);
		$filetype = "application";

		$base64 = 'data:'.$filetype.'/' . $type . ';base64,' . base64_encode($data);
		// $base64 = encodeBase64($file);

		$data = [
			"phone" => $phone,
			"body" => $base64,
			"filename" => "slip_tunj_prestasi_jul_2022.pdf",
			"caption" => "slip tunjangan prestasi karyawan abcd [12345] periode juli 2022"
		];
		
		$URL = site_url("waba/sendFile")."?src=".$src;
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		echo($response);

	}

	public function SendLinkTest()
	{
		$src = $this->input->get("src");
		$phone=$this->input->get("phone");
		if ($phone=="") {
			$phone = "6281222345235";
		}

		//$b64Doc = chunk_split(base64_encode(file_get_contents("https://img.icons8.com/cute-clipart/2x/google-logo.png")));
		//echo($b64Doc."<br><br>");

		$data = [
			"chatId" => "",
			"phone" => $phone,
			"body" => "http://mon.bhakti.co.id:90/",
			//"previewBase64" => "data:image/jpeg;base64,".$b64Doc,
			"previewBase64" => "",
			"title" => "Portal Log Bhakti VA",
			"description" => "minion",
			"text" => "Kunjungi http://mon.bhakti.co.id:90/ untuk Pemantauan..",
			"quotedMsgId" => "",
			"mentionedPhones" => ""
		];
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => site_url("sendLinkWA")."?src=".$src,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POST => 1,
			// CURLOPT_POSTFIELDS => http_build_query($data),
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array("Content-type: application/json")
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		echo($response);
	}

	
}