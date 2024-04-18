<style type="text/css">
	.row {
	    line-height:30px; 
	    vertical-align:middle;
	    clear:both;
	}
	.row-label, .row-input {
    	float:left;
	}
	/* .row-label {
    padding-left: 15px;
    width:180px;
	} */
	.row-input {
   	 	width:420px;
	}
	#no-rekening-value{
		color:black;
	}
	table,th,td{
/*		border: solid 1px black;*/
		border-collapse: collapse;
	}
	th{
		text-align: center;
		font-weight: bold;
	}
	td{
		padding: 0px 5px;
	}
	.table-parent{
		width: 100%;
		height: 500px;
		overflow: auto;
	}

/* 	tr:nth-child(even) {background: white} */
/* 	tr:nth-child(odd) {background: #d5d5d5;} /* selects every odd row */*/
/* 	tr:first-child {
		background: white;
	}
*/	
	th{
		padding: 5px 0px;
		font-weight: bold;
	}
</style>

<?php
$grandGrandTotalDpp = 0;
$grandGrandTotalPpn = 0;
$grandGrandTotalDppPpn = 0;
foreach($rekapTmp as $keyTmp => $valueTmp){
	$katBrg = '';
	if($keyTmp=='P') $katBrg = 'PRODUK';
	else if($keyTmp == 'S') $katBrg = 'SPAREPART';
	else $katBrg = $keyTmp;

	// echo '<pre>';
	// print_r($valueTmp);
	// echo '</pre>';
	// $length = count($value);
	// $data['rekap'] = $value;
	// echo 'katBrg '.$katBrg.'<br>';
	// echo 'length '.$length;
	foreach($valueTmp as $keyRekap => $rekap){
 		
		echo <<<HTML
		<table autosize="1" style="overflow: wrap;width:100%;">
			<thead>
				<tr>
					<td colspan="13" style="font-weight:bold;">{$keyRekap}</td>
					<td style="font-weight:bold;">{$katBrg}</td>
				</tr>
				<tr style="">
					<td style="border: solid 1px black;width:4%;font-size: 10px;padding: 2mm">NO URUT</td>
					<td style="border: solid 1px black;width:8%;font-size: 10px;padding: 2mm">TGL FAKTUR PAJAK</td>
					<td style="border: solid 1px black;width:13%;font-size: 10px;padding: 2mm">NO FAKTUR PAJAK</td>
					<td style="border: solid 1px black;width:13%;font-size: 10px;padding: 2mm">NPWP PENJUALAN/ PEMBERI JASA</td>
					<td style="border: solid 1px black;width:13%;font-size: 10px;padding: 2mm">NAMA PKP/ PEMBERI JASA</td>
					<td style="border: solid 1px black;width:9%;font-size: 10px;padding: 2mm">NAMA BARANG / JASA</td>
					<td style="border: solid 1px black;width:4%;font-size: 10px;padding: 2mm">QTY</td>
					<td style="border: solid 1px black;width:9%;font-size: 10px;padding: 2mm">HARGA JUAL SATUAN (RP)</td>
					<td style="border: solid 1px black;width:11%;font-size: 10px;padding: 2mm">DISC SATUAN (RP)</td>
					<td style="border: solid 1px black;width:11%;font-size: 10px;padding: 2mm">HARGA NETT SATUAN (RP)</td>
					<td style="border: solid 1px black;width:11%;font-size: 10px;padding: 2mm">DASAR PENGENAAN PAJAK (RP)</td>
					<td style="border: solid 1px black;width:10%;font-size: 10px;padding: 2mm">PPN DAPAT DIKREDITKAN (RP)</td>
					<td style="border: solid 1px black;width:11%;font-size: 10px;padding: 2mm">DPP + PPN</td>
					<td style="border: solid 1px black;width:8%;font-size: 10px;padding: 2mm">TGL INVOICE</td>
				</tr>
			</thead>
			<tbody>
HTML;
		$grandTotalDisc = 0;
		$grandTotalDpp = 0;
		$grandTotalPpn = 0;
		$grandTotalDppPpn = 0;
		
		$totalHarga = 0;
		$totalDisc = 0;
		$totalDpp = 0;
		$totalPpn = 0;
		$totalDppPppn = 0;

		$tmpNo_FakturP = '';
		$i=0;
		$rowTotal = '';
		foreach($rekap as $key => $value){

			$col1 = '';
			$col2 = '';
			$col3 = '';
			$col4 = '';
			$col5 = '';
			$col10 = '';
			$col13 = '';
			$col14 = '';
			$rowTotal = '';
			if($tmpNo_FakturP!=$value['No_FakturP']){
				$i += 1;
				$col1 = $i;
				$col2 = date('d/m/Y',strtotime($value['Tgl_FakturP']));
				$col3 = $value['No_FakturP'];
				$col4 = $value['NPWP'];
				$col5 = $value['Nm_Supl'];
				$col14 = date('d/m/Y',strtotime($value['Tgl_Invoice']));
				//sebelum di reset simpan dulu 1 row 
				//buat nanti di insert diatasnya sebelum di insert ke nomor faktur yang baru
				if($tmpNo_FakturP!=''){
					$grandTotalDisc += $totalDisc;
					$grandTotalDpp += $totalDpp;
					$grandTotalPpn += $totalPpn;
					$grandTotalDppPpn += ($totalDpp + $totalPpn);

					$totalDppPppn = number_format(($totalDpp + $totalPpn),2);
					
					$totalHarga = number_format($totalHarga,2);
					$totalDisc = number_format($totalDisc,2);
					$totalDpp = number_format($totalDpp,2);
					$totalPpn = number_format($totalPpn,2);
					

					


					$rowTotal = <<<HTML
					<tr>
						<td style="border-left:solid 1px black; border-right:solid 1px black;"></td>
						<td style="border-left:solid 1px black; border-right:solid 1px black;"></td>
						<td style="border-left:solid 1px black; border-right:solid 1px black;"></td>
						<td style="border-left:solid 1px black; border-right:solid 1px black;"></td>
						<td style="border-left:solid 1px black; border-right:solid 1px black;"></td>
						<td style="border-left:solid 1px black; border-right:solid 1px black;"></td>
						<td style="border-top: solid 1px black;border-left:solid 1px black; border-right:solid 1px black;"></td>
						<td style="border-top: solid 1px black;border-left:solid 1px black; border-right:solid 1px black;"></td>
						<td style="border-top: solid 1px black;border-left:solid 1px black; border-right:solid 1px black;"></td>
						<td style="border-top: solid 1px black;border-left:solid 1px black; border-right:solid 1px black;text-align: right;font-size: 10px;padding: 2mm;">TOTAL</td>
						<td style="text-align: right;border-top: solid 1px black;border-left:solid 1px black; border-right:solid 1px black;font-size: 10px;padding: 2mm;">{$totalDpp}</td>
						<td style="text-align: right;border-top: solid 1px black;border-left:solid 1px black; border-right:solid 1px black;font-size: 10px;padding: 2mm;">{$totalPpn}</td>
						<td style="text-align: right;border-top: solid 1px black;border-left:solid 1px black; border-right:solid 1px black;font-size: 10px;padding: 2mm;">{$totalDppPppn}</td>
						<td style="border-top: solid 1px black;border-left:solid 1px black; border-right:solid 1px black;font-size: 10px;padding: 2mm;"></td>
					</tr>
					<tr>
						<td style="border-left:solid 1px black; border-right:solid 1px black;">&nbsp;&nbsp;</td>
						<td style="border-left:solid 1px black; border-right:solid 1px black;">&nbsp;&nbsp;</td>
						<td style="border-left:solid 1px black; border-right:solid 1px black;">&nbsp;&nbsp;</td>
						<td style="border-left:solid 1px black; border-right:solid 1px black;">&nbsp;&nbsp;</td>
						<td style="border-left:solid 1px black; border-right:solid 1px black;">&nbsp;&nbsp;</td>
						<td style="border-left:solid 1px black; border-right:solid 1px black;">&nbsp;&nbsp;</td>
						<td style="border-left:solid 1px black; border-right:solid 1px black;">&nbsp;&nbsp;</td>
						<td style="border-left:solid 1px black; border-right:solid 1px black;">&nbsp;&nbsp;</td>
						<td style="border-left:solid 1px black; border-right:solid 1px black;">&nbsp;&nbsp;</td>
						<td style="border-left:solid 1px black; border-right:solid 1px black;">&nbsp;&nbsp;</td>
						<td style="border-left:solid 1px black; border-right:solid 1px black;">&nbsp;&nbsp;</td>
						<td style="border-left:solid 1px black; border-right:solid 1px black;">&nbsp;&nbsp;</td>
						<td style="border-left:solid 1px black; border-right:solid 1px black;">&nbsp;&nbsp;</td>
						<td style="border-left:solid 1px black; border-right:solid 1px black;">&nbsp;&nbsp;</td>
					</tr>
HTML;
					$totalHarga = 0;
					$totalDisc = 0;
					$totalDpp = 0;
					$totalPpn = 0;
				}
				

				$tmpNo_FakturP = $value['No_FakturP'];
				
			}
			
			$col6 = $value['Kd_Brg'];
			$col7 = number_format($value['Qty'],0);
			$col8 = number_format($value['Harga'],2);
			$col9 = number_format($value['Disc'],2);
			$col10 = number_format($value['HargaNett'],2);
			$col11 = number_format( round($value['Harga'] * $value['Qty'] * (100 - $value['DiscPersen'])/100) ,2);
			$col12 = number_format( (round($value['Harga'] * $value['Qty'] * (100 - $value['DiscPersen'])/100)) * ($value['TarifPPN']/100),2);
			$col13 = '';//number_format(($value['DPP']+$value['PPN']),2);
			

			$totalHarga += $value['Harga'];
			$totalDisc += $value['Disc'];
			$totalDpp +=  round($value['Harga'] * $value['Qty'] * (100 - $value['DiscPersen'])/100);
			$totalPpn += (round($value['Harga'] * $value['Qty'] * (100 - $value['DiscPersen'])/100)) * ($value['TarifPPN']/100);
			
		
			echo <<<HTML
			{$rowTotal}
			<tr>
				<td style="text-align: right;border-left:solid 1px black; border-right:solid 1px black;font-size: 11px;padding: 2mm;">{$col1}</td>
				<td style="border-left:solid 1px black; border-right:solid 1px black;font-size: 11px;padding: 2mm;">{$col2}</td>
				<td style="border-left:solid 1px black; border-right:solid 1px black;font-size: 11px;padding: 2mm;">{$col3}</td>
				<td style="border-left:solid 1px black; border-right:solid 1px black;font-size: 11px;padding: 2mm;">{$col4}</td>
				<td style="border-left:solid 1px black; border-right:solid 1px black;font-size: 11px;padding: 2mm;">{$col5}</td>
				<td style="border-left:solid 1px black; border-right:solid 1px black;font-size: 11px;padding: 2mm;">{$col6}</td>
				<td style="text-align: right;border-left:solid 1px black; border-right:solid 1px black;font-size: 11px;padding: 2mm;">{$col7}</td>
				<td style="text-align: right;border-left:solid 1px black; border-right:solid 1px black;font-size: 11px;padding: 2mm;">{$col8}</td>
				<td style="text-align: right;border-left:solid 1px black; border-right:solid 1px black;font-size: 11px;padding: 2mm;">{$col9}</td>
				<td style="text-align: right;border-left:solid 1px black; border-right:solid 1px black;font-size: 11px;padding: 2mm;">{$col10}</td>
				<td style="text-align: right;border-left:solid 1px black; border-right:solid 1px black;font-size: 11px;padding: 2mm;">{$col11}</td>
				<td style="text-align: right;border-left:solid 1px black; border-right:solid 1px black;font-size: 11px;padding: 2mm;">{$col12}</td>
				<td style="text-align: right;border-left:solid 1px black; border-right:solid 1px black;font-size: 11px;padding: 2mm;">{$col13}</td>
				<td style="border-left:solid 1px black; border-right:solid 1px black;font-size: 11px;padding: 2mm;">{$col14}</td>
			</tr>
HTML;
		}
		//$grandTotalDisc = number_format($grandTotalDisc,2);
		$grandGrandTotalDpp += $grandTotalDpp;
		$grandGrandTotalPpn += $grandTotalPpn;
		$grandGrandTotalDppPpn += $grandTotalDppPpn;
		$grandTotalDpp = number_format($grandTotalDpp,2);
		$grandTotalPpn = number_format($grandTotalPpn,2);
		$grandTotalDppPpn = number_format($grandTotalDppPpn,2);
		echo <<<HTML
				<tr>
					<td colspan="10" style="border:solid 1px black;font-size: 10px;padding: 2mm;text-align:right;">TOTAL {$keyRekap} - {$katBrg}</td>
					<td style="text-align: right;border:solid 1px black;font-size: 10px;padding: 2mm;">{$grandTotalDpp}</td>
					<td style="text-align: right;border:solid 1px black;font-size: 10px;padding: 2mm;">{$grandTotalPpn}</td>
					<td style="text-align: right;border:solid 1px black;font-size: 10px;padding: 2mm;">{$grandTotalDppPpn}</td>
					<td style="border:solid 1px black;font-size: 10px;padding: 2mm;"></td>
				</tr>
				<tr>
					<td>&nbsp;&nbsp;</td>
					<td>&nbsp;&nbsp;</td>
					<td>&nbsp;&nbsp;</td>
					<td>&nbsp;&nbsp;</td>
					<td>&nbsp;&nbsp;</td>
					<td>&nbsp;&nbsp;</td>
					<td>&nbsp;&nbsp;</td>
					<td>&nbsp;&nbsp;</td>
					<td>&nbsp;&nbsp;</td>
					<td>&nbsp;&nbsp;</td>
					<td>&nbsp;&nbsp;</td>
					<td>&nbsp;&nbsp;</td>
					<td>&nbsp;&nbsp;</td>
					<td>&nbsp;&nbsp;</td>
				</tr>
			</tbody>
		</table>
HTML;
	}
}
//akhir pembuatan table
$grandGrandTotalDpp = number_format($grandGrandTotalDpp,2);
$grandGrandTotalPpn = number_format($grandGrandTotalPpn,2);
$grandGrandTotalDppPpn = number_format($grandGrandTotalDppPpn,2);
echo <<<HTML
<table autosize="1" style="overflow: wrap;width:100%;">
	<tr>
		<td colspan="10" style="font-size: 9px;text-align: right; width: 68%;">Grand Total</td>
		<td style="text-align: right;font-size: 9px;padding: 2mm;width: 9%">{$grandGrandTotalDpp}</td>
		<td style="text-align: right;font-size: 9px;padding: 2mm;width: 9%">{$grandGrandTotalPpn}</td>
		<td style="text-align: right;font-size: 9px;padding: 2mm;width: 9%">{$grandGrandTotalDppPpn}</td>
		<td style="font-size: 9px;padding: 2mm;width: 5%;"></td>
	</tr>
</table>
HTML;
?>
