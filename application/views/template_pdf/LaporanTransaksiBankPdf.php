<?php
$nama_laporan = 'LAPORAN TRANSAKSI BANK';
$html ='';
$html .='
	<style>
	.border{border:1px solid #555;}
	.bold{font-weight:bold;}
	.left{text-align:left;}
	.right{text-align:right;}
	.center{text-align:center;}
	.bigXL{font-size:150%;}
	.big{font-size:120%;}
	.italic{font-style:italic;}
	.w40px{width:40px;}
	</style>
';

$html .='<htmlpageheader name="header_1">
			<div class="right">'.date('d-M-Y H:i:s').'</div>
			<div class="bigXL bold center">'.$nama_laporan.'</div>
			<table width="100%" class="bold">
				<tr><td width="80px">BANK</td>	<td>: '.$Bank.' '.$Cabang.'</td><td></td>
				<tr><td>NO REK</td>	<td>: '.$NoRekening.'</td><td></td>
				<tr><td>A/N</td>	<td>: '.$Nm_Pemilik.'</td><td class="right">Periode '.date('d-M-Y',strtotime($TglAwal)).' s/d '.date('d-M-Y',strtotime($TglAkhir)).'</td>
				</tr>
			</table>
			<table width="100%" class="border bold">
				<tr>
					<td class="left" width="10%">Tgl. Trans</td>
					<td class="left" width="17%">No. Bukti</td>
					<td class="left" width="22%">Keterangan</td>
					<td class="right" width="17%">Debet</td>
					<td class="right" width="17%">Kredit</td>
					<td class="right" width="17%">Saldo</td>
			</table>
		
		</htmlpageheader>';
$html .='<sethtmlpageheader name="header_1" value="on" show-this-page="1" />';
$html .='<table width="100%">';

$total_debet = 0;
$total_kredit = 0;
$saldo = 0;
foreach($res->data as $dt){
	$saldo += $dt->Saldo;
	$html .='<tr>';
	$html .='<td class="left" width="10%">'.date('d-M-Y',strtotime($dt->Tgl_Trans)).'</td>';
	$html .='<td class="left" width="17%">'.$dt->No_Bukti.'</td>';
	$html .='<td class="left" width="22%">'.$dt->Keterangan.'</td>';
	$html .='<td class="right" width="17%">'.number_format($dt->Kredit,2).'</td>';
	$html .='<td class="right" width="17%">'.number_format($dt->Debet,2).'</td>';
	$html .='<td class="right" width="17%">'.number_format($saldo,2).'</td>';
	
	$html .='</tr>';
	$total_debet += $dt->Debet;
	$total_kredit += $dt->Kredit;
}	
$html .='<tr>';
$html .='<td class="left" width="10%">'.date('d-M-Y',strtotime($TglAkhir)).'</td>';
$html .='<td class="left" width="17%"></td>';
$html .='<td class="left" width="22%"><b>Saldo Akhir</b></td>';
$html .='<td class="right" width="17%"></td>';
$html .='<td class="right" width="17%"></td>';
$html .='<td class="right" width="17%"><b>'.number_format($saldo,2).'</b></td>';
$html .='</tr>';

$html .='</table>';
$html .='<table width="100%" class="border bold">';
$html .='<tr>';
$html .='<td class="left" width="10%"></td>';
$html .='<td class="left" width="17%"></td>';
$html .='<td class="right" width="22%">Total</td>';
$html .='<td class="right" width="17%">'.number_format($total_kredit,2).'</td>';
$html .='<td class="right" width="17%">'.number_format($total_debet,2).'</td>';
$html .='<td class="right" width="17%"></td>';
$html .='</tr>';
$html .='</table>';
echo $html;
?>