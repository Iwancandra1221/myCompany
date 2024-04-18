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
<table autosize="1" style="overflow: wrap;width:100%;">
	<?php  
	echo <<<HTML
		<thead>
			<tr style="">
				<td style="border: solid 1px black;width:4%;font-size: 10px;padding: 2mm">NO URUT</td>
				<td style="border: solid 1px black;width:8%;font-size: 10px;padding: 2mm">TGL FAKTUR PAJAK</td>
				<td style="border: solid 1px black;width:13%;font-size: 10px;padding: 2mm">NO FAKTUR PAJAK</td>
				<td style="border: solid 1px black;width:13%;font-size: 10px;padding: 2mm">NPWP PENJUALAN/ PEMBERI JASA</td>
				<td style="border: solid 1px black;width:13%;font-size: 10px;padding: 2mm">NAMA PKP/ PEMBERI JASA</td>
				<td style="border: solid 1px black;width:9%;font-size: 10px;padding: 2mm">NAMA BARANG / JASA</td>
				<td style="border: solid 1px black;width:5%;font-size: 10px;padding: 2mm">QTY</td>
				<td style="border: solid 1px black;width:9%;font-size: 10px;padding: 2mm">HARGA JUAL SATUAN (RP)</td>
				<td style="border: solid 1px black;width:11%;font-size: 10px;padding: 2mm">SUB TOTAL (RP)</td>
				<td style="border: solid 1px black;width:10%;font-size: 10px;padding: 2mm">DASAR PENGENAAN PAJAK (RP)</td>
				<td style="border: solid 1px black;width:10%;font-size: 10px;padding: 2mm">PPN DAPAT DIKREDITKAN (RP)</td>
				<td style="border: solid 1px black;width:11%;font-size: 10px;padding: 2mm">DPP + PPN</td>
				<td style="border: solid 1px black;width:8%;font-size: 10px;padding: 2mm">TGL INVOICE</td>
			</tr>
		</thead>
		<tbody>
HTML;
	$grandTotalSubTotal = 0;
	$grandTotalDpp = 0;
	$grandTotalPpn = 0;
	$grandTotalDppPpn = 0;
	
	$totalHarga = 0;
	$totalSubTotal = 0;
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
		$rowTotal = '';
		if($tmpNo_FakturP!=$value['No_FakturP']){
			$i += 1;
			$col1 = $i;
			$col2 = date('d/m/Y',strtotime($value['Tgl_FakturP']));
			$col3 = $value['No_FakturP'];
			$col4 = $value['NPWP'];
			$col5 = $value['Nm_Supl'];
			$col13 = date('d/m/Y',strtotime($value['Tgl_Invoice']));
			//sebelum di reset simpan dulu 1 row 
			//buat nanti di insert diatasnya sebelum di insert ke nomor faktur yang baru
			if($tmpNo_FakturP!=''){
				$totalDppPppn = number_format(($totalDpp + $totalPpn),2);

				$totalHarga = number_format($totalHarga,2);
				$totalSubTotal = number_format($totalSubTotal,2);
				$totalDpp = number_format($totalDpp,2);
				$totalPpn = number_format($totalPpn,2);
				

				$grandTotalSubTotal += $totalSubTotal;
				$grandTotalDpp += $totalDpp;
				$grandTotalPpn += $totalPpn;
				$grandTotalDppPpn += $totalDppPppn;


				$rowTotal = <<<HTML
				<tr>
					<td style="border-left:solid 1px black; border-right:solid 1px black;"></td>
					<td style="border-left:solid 1px black; border-right:solid 1px black;"></td>
					<td style="border-left:solid 1px black; border-right:solid 1px black;"></td>
					<td style="border-left:solid 1px black; border-right:solid 1px black;"></td>
					<td style="border-left:solid 1px black; border-right:solid 1px black;"></td>
					<td style="border-left:solid 1px black; border-right:solid 1px black;"></td>
					<td style="border-top: solid 1px black;border-left:solid 1px black; border-right:solid 1px black;"></td>
					<td style="border-top: solid 1px black;border-left:solid 1px black; border-right:solid 1px black;text-align: right;">TOTAL</td>
					<td style="text-align: right;border-top: solid 1px black;border-left:solid 1px black; border-right:solid 1px black;">{$totalSubTotal}</td>
					<td style="text-align: right;border-top: solid 1px black;border-left:solid 1px black; border-right:solid 1px black;">{$totalDpp}</td>
					<td style="text-align: right;border-top: solid 1px black;border-left:solid 1px black; border-right:solid 1px black;">{$totalPpn}</td>
					<td style="text-align: right;border-top: solid 1px black;border-left:solid 1px black; border-right:solid 1px black;">{$totalDppPppn}</td>
					<td style="border-top: solid 1px black;border-left:solid 1px black; border-right:solid 1px black;"></td>
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
				</tr>
HTML;
				$totalHarga = 0;
				$totalSubTotal = 0;
				$totalDpp = 0;
				$totalPpn = 0;
			}
			

			$tmpNo_FakturP = $value['No_FakturP'];
			
		}
		
		$col6 = $value['Kd_Brg'];
		$col7 = number_format($value['Qty'],0);
		$col8 = number_format($value['Harga'],2);
		$col9 = number_format($value['Subtotal'],2);
		//$col10 = number_format($value['DPP'],2);
		$col11 = number_format($value['Subtotal'] * $value['TarifPPN']/100,2);
		$col12 = '';//number_format($value['Total_Invoice'],2);
		

		$totalHarga += $value['Harga'];
		$totalSubTotal += $value['Subtotal'];
		$totalDpp += $value['Subtotal'];
		$totalPpn += ($value['Subtotal'] * $value['TarifPPN']/100);
		
	
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
			<td style="border-left:solid 1px black; border-right:solid 1px black;font-size: 11px;padding: 2mm;">{$col13}</td>
		</tr>
HTML;
	}
	?>
		<tr>
			<td colspan="8" style="font-size: 10px;padding: 2mm;text-align:right;">GRAND TOTAL</td>
			<td style="text-align: right;font-size: 10px;padding: 2mm;"><?=number_format($grandTotalSubTotal,2)?></td>
			<td style="text-align: right;font-size: 10px;padding: 2mm;"><?=number_format($grandTotalDpp,2)?></td>
			<td style="text-align: right;font-size: 10px;padding: 2mm;"><?=number_format($grandTotalPpn,2)?></td>
			<td style="text-align: right;font-size: 10px;padding: 2mm;"><?=number_format($grandTotalDppPpn,2)?></td>
			<td style="font-size: 10px;padding: 2mm;"></td>
		</tr>
	</tbody>
</table>
