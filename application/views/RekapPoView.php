<link href='rekap_po_style.css' rel='stylesheet' type='text/css'>

<?php
date_default_timezone_set('Asia/Jakarta');


$localServer = "10.1.0.2"; //serverName\instanceName, portNumber (default is 1433)
$localConnInfo = array( "Database"=>"BIT", "UID"=>"mishirin", "PWD"=>"br4v01nd14T4n990");
set_time_limit(600);
$local = sqlsrv_connect($localServer, $localConnInfo);
if(!$local) {
     echo "Koneksi ke BIT Gagal.<br />";
     die( print_r( sqlsrv_errors(), true));
}

$p_yy = $_GET["yyyy"];
$p_mm = $_GET["mm"];
$p_pp = $_GET["pp"];
$p_divisi = $_GET["divisi"];

$p_email = strtoupper($_GET["email"]);
if ($p_email==null)
{
	$p_email = "Y";
}

$p_cbg = strtoupper($_GET["cbg"]);
if ($p_cbg==null)
{
	$p_cbg = "%";
} 
else if ($p_cbg="ALL")
{
	$p_cbg = "%";
}

$p_opsi = $_GET["opsi"];

/*
require 'PHPMailerAutoload.php';
$mail = new PHPMailer;
$mail->isSMTP();                            // Set mailer to use SMTP
$mail->Host = "ssl://smtp.googlemail.com";  			// Specify main and backup SMTP servers
$mail->SMTPAuth = true;                     // Enable SMTP authentication
$mail->Username = "bithrd.noreply@gmail.com";        // SMTP username
$mail->Password = "bithrd123";         		// SMTP password
$mail->SMTPSecure = 'ssl';                  // Enable TLS encryption, `ssl` also accepted
$mail->Port = "465";            			// TCP port to connect to
$mail->setFrom("bithrd.noreply@gmail.com", "WEB HRD Auto-Email");
$mail->addReplyTo('bithrd.noreply@gmail.com', 'Information');
$mail->isHTML(true);                        // Set email format to HTML
$mail->Subject = 'Rekap PO';
*/

$sql = "Select DISTINCT Cbg, No_PrePO From dbo.Report_Forecast_JualBeli_Gabungan ('".$p_divisi."','P',".$p_yy.",".$p_mm.",".$p_pp.",'JKTG001') as GAB Order By Cbg, No_PrePO ";
$res_group = sqlsrv_query($local, $sql);
if ($res_group===false)
	die( print_r(sqlsrv_errors(), true));

while($row_group = sqlsrv_fetch_array($res_group)) {

	$CBG = $row_group['Cbg'];
	$NO_PREPO = $row_group['No_PrePO'];

	$ADA_PREPO = false;

	$content_html = "<html><body>";
	$content_html.= "<div style='width:100%;'>";

	$content_html.= "<div id='header' style='width:100%;'>";
	$content_html.= "	<div><h2>LAPORAN INTERVENSI JUAL/BELI</h2></div>";
	//$content_html.= "	<div><h3>TANGGAL ".$kemarin."</h3></div>";
	$content_html.= "</div>";

	$content_html.= "<div style='clear:both'></div>";
	$content_html.= "<div class='Group_PrePO'>".$NO_PREPO."</div>";
	$content_html.= "<div style='width:100%;border-bottom:1px solid #CCC;text-align:center;min-height:30px;'>";
	$content_html.= "	<div class='column_10'><b>KODE BARANG</b></div>";
	$content_html.= "	<div class='column_10'><b>STOCK AWAL</b></div>";
	$content_html.= "	<div class='column_10'><b>BELI (INTERVENSI)</b></div>";
	$content_html.= "	<div class='column_10'><b>JUAL (INTERVENSI)</b></div>";
	$content_html.= "	<div class='column_10'><b>STOCK (INTERVENSI)</b></div>";
	$content_html.= "	<div class='column_10'><b>NAMA BARANG</b></div>";
	$content_html.= "	<div class='column_10'><b>NAMA BARANG</b></div>";
	$content_html.= "	<div class='column_10'><b>NAMA BARANG</b></div>";
	$content_html.= "	<div class='column_10'><b>NAMA BARANG</b></div>";
	$content_html.= "	<div class='column_10'><b>NAMA BARANG</b></div>";
	$content_html.= "</div>";		 
	$content_html.= "<div style='clear:both;></div>";

	$sql = "Select * From dbo.Report_Forecast_JualBeli_Gabungan ('MIYAKO','P',2017,3,1,'JKTG001') as GAB Where No_PrePO = '".$NO_PREPO."' Order By Subkategori, Kd_Brg";
	$res_prepo = sqlsrv_query($local, $sql);

	$SUBKATEGORI = "";

    while($prepo = sqlsrv_fetch_array($res_prepo, SQLSRV_FETCH_ASSOC)) {
    	$ADA_PREPO = true;

    	if ($SUBKATEGORI==$prepo['Subkategori'])
    	{
    	}
    	else
    	{
    		if ($SUBKATEGORI!="") {
				$content_html.= "<div style='width:100%;border-top:1px solid #CCC;text-align:center;min-height:30px'>";
				$content_html.= "	<div style='float:left;width:84%;line-height:30px;vertical-align:middle;'><b>TOTAL ".$SUBKATEGORI."</b></div>";
				$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>123456</b></div>";
				$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>999</b></div>";
				$content_html.= "	<div style='clear:both'></div>";
				$content_html.= "</div></div>";		    		    			
    			$content_html.= "</div></div>";
    		}

    		$SUBKATEGORI=$prepo['Subkategori'];
    		$content_html.= "<div class='another_expedition' style='margin-top:15px;margin-bottom:25px;'>";
    		/*
    		$content_html.= "<div style='font-weight:bold; margin-bottom:30px;'>";
    		$content_html.= "	<div style='width:15%;float:left;font-size:10pt;'>Kode Barang</div>";
    		$content_html.= "	<div style='width:35%;float:left;font-size:10pt;'>: ".$prepo['Exp_Name']."</div>";
    		$content_html.= "	<div style='width:15%;float:left;font-size:10pt;'>No Ekspedisi</div>";
    		$content_html.= "	<div style='width:35%;float:left;font-size:10pt;'>: ".$do['Exp_No']."</div>";
			$content_html.= "	<div style='clear:both'></div>";
    		$content_html.= "	<div style='width:15%;float:left;font-size:10pt;'>No Container</div>";
    		$content_html.= "	<div style='width:35%;float:left;font-size:10pt;'>: ".$do['Container_No']."</div>";
    		$content_html.= "	<div style='width:15%;float:left;font-size:10pt;'>Supir</div>";
    		$content_html.= "	<div style='width:35%;float:left;font-size:10pt;'>: ".$do['Nm_Supir']."</div>";
			$content_html.= "	<div style='clear:both'></div>";
    		$content_html.= "	<div style='width:15%;float:left;font-size:10pt;'>Container Seal</div>";
    		$content_html.= "	<div style='width:35%;float:left;font-size:10pt;'>: ".$do['Container_Seal']."</div>";
    		$content_html.= "	<div style='width:15%;float:left;font-size:10pt;'>No Mobil</div>";
    		$content_html.= "	<div style='width:35%;float:left;font-size:10pt;'>: ".$do['No_PlatMobil']."</div>";
			$content_html.= "</div>";		    		
			*/
    		$content_html.= "<div class='another_shipment' style='margin-top:20px; margin-bottom:15px;'>";
    		$content_html.= "<div style='margin-top:20px; text-align:left; font-weight:bold;'>".$SUBKATEGORI."</div>";
			$content_html.= "<div style='width:100%;border:1px solid #CCC;text-align:center;'>";

    	}

		$content_html.= "<div style='width:100%;text-align:center;min-height:25px;border:1px solid #ccc;'>";
		$content_html.= "	<div class='column_10'>".$prepo['Kd_Brg']."</div>";
		$content_html.= "	<div class='column_10'>".$prepo['Nm_Brg']."</div>";
		$content_html.= "	<div class='column_10'>".$prepo['Stock_Awal']."</div>";
		$content_html.= "	<div class='column_10'>".$prepo['I_TotalBeli']."</div>";
		$content_html.= "	<div class='column_10'>".$prepo['I_TotalJual']."</div>";
		$content_html.= "	<div class='column_10'>".$prepo['I_TotalStock']."</div>";
		$content_html.= "	<div class='column_10'><b>NAMA BARANG</b></div>";
		$content_html.= "	<div class='column_10'><b>NAMA BARANG</b></div>";
		$content_html.= "	<div class='column_10'><b>NAMA BARANG</b></div>";
		$content_html.= "	<div class='column_10'><b>NAMA BARANG</b></div>";
		$content_html.= "</div>";
		$content_html.= "<div style='clear:both;></div>";
		//$TOTAL_QTY += $do['Qty'];
		//$TOTAL_COLI+= $do['Coli'];

    }
	sqlsrv_free_stmt($res_prepo);

	/*if ($ADA_DO)
	{

		$content_html.= "<div style='width:100%;border-top:1px solid #CCC;text-align:center;min-height:30px'>";
		$content_html.= "	<div style='float:left;width:84%;line-height:30px;vertical-align:middle;'><b>TOTAL ".$SHIPMENTID."</b></div>";
		$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>".$TOTAL_QTY."</b></div>";
		$content_html.= "	<div style='float:left;width:8%;line-height:30px;vertical-align:middle;'><b>".$TOTAL_COLI."</b></div>";
		$content_html.= "	<div style='clear:both'></div>";
		$content_html.= "</div></div>";		    		    			
		$content_html.= "</div></div>";
		if ($p_email=="Y")		
		{
			$content_html.= "<div style='font-size:10pt;font-decoration:italic;margin-top:25px;'>";
			$content_html.= "Email ini dikirimkan otomatis oleh sistem. Mohon tidak mereply email ini.";
			$content_html.= "</div>";
		}
		$content_html.= "</div></body></html>";

		echo $content_html;

		//Kirim Emailnya
		$recipients = sqlsrv_query($local, "Select * from Cof_EmailDoPabrikDT_Recipients Where EmailGroupID='".$row_group['EmailGroupID']."' and Aktif='Y'");
		while($recipient = sqlsrv_fetch_array($recipients)) {	
			$mail->addAddress($recipient['EmailAddress'], $recipient['RecipientName']);
		}
		sqlsrv_free_stmt($recipients);

		$mail->Body    = $content_html;
		$mail->AltBody = "Laporan Ekspedisi Pabrik";

		if ($p_email=="Y")
		{
			if(!$mail->send()) {
			    echo 'Message could not be sent.<br>';
			    echo 'Mailer Error: ' . $mail->ErrorInfo;
			    $mail->clearAddresses();
			} else {
			    echo 'Message has been sent<br>';
			    $mail->clearAddresses();
			}
		}		
	}*/
	$content_html.= "</div></body></html>";
	echo ($content_html);
	echo ("<br><br>");

}
sqlsrv_free_stmt($res_group);


//$del_table_content = sqlsrv_query($local, "Truncate Table Trx_DO");
//sqlsrv_free_stmt($del_table_content);

sqlsrv_close($local);

?>