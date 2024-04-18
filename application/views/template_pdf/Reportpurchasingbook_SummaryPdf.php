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
		border: solid 1px black;
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

 	tr:nth-child(even) {background: white} 
 	tr:nth-child(odd) {background: #d5d5d5;} /* selects every odd row */
 	tr:first-child {
		background: white;
	}
	th{
		padding: 5px 0px;
		font-weight: bold;
	}
</style>
<table autosize="1" style="overflow: wrap;width:100%;">
	<?php  
	echo <<<HTML
		<thead>
			<tr>
				<td style="width:4%;font-size: 10px;padding: 2mm">NO URUT</td>
				<td style="width:8%;font-size: 10px;padding: 2mm">TGL FAKTUR PAJAK</td>
				<td style="width:13%;font-size: 10px;padding: 2mm">NO FAKTUR PAJAK</td>
				<td style="width:13%;font-size: 10px;padding: 2mm">NPWP PENJUALAN/ PEMBERI JASA</td>
				<td style="width:13%;font-size: 10px;padding: 2mm">NAMA PKP/ PEMBERI JASA</td>
				<td style="width:11%;font-size: 10px;padding: 2mm">NO INVOICE</td>
				<td style="width:10%;font-size: 10px;padding: 2mm">DASAR PENGENAAN PAJAK</td>
				<td style="width:10%;font-size: 10px;padding: 2mm">PPN DAPAT DIKREDITKAN (RP)</td>
				<td style="width:10%;font-size: 10px;padding: 2mm">TOTAL (RP)</td>
				<td style="width:8%;font-size: 10px;padding: 2mm">TGL INVOICE</td>
			</tr>
		</thead>
		<tbody>
HTML;
	$grandTotalDpp = 0;
	$grandTotalPpn = 0;
	$grandTotalTotal = 0;
	foreach($rekap as $key => $value){
		$col1 = ($key+1);
		$col2 = date('d/m/Y',strtotime($value['Tgl_FakturP']));
		$col3 = $value['No_FakturP'];
		$col4 = $value['NPWP'];
		$col5 = $value['Nm_Supl'];
		$col6 = $value['No_FakturS'];
		$col7 = number_format($value['DPP'],2);
		$col8 = number_format($value['PPN'],2);
		$col9 = number_format($value['Total_Invoice'],2);
		$col10 = date('d/m/Y',strtotime($value['Tgl_Invoice']));
		
		$grandTotalDpp += $value['DPP'];
		$grandTotalPpn += $value['PPN'];
		$grandTotalTotal += $value['Total_Invoice'];
		echo <<<HTML
		<tr>
			<td style="font-size: 11px;padding: 2mm;text-align: right;">{$col1}</td>
			<td style="font-size: 11px;padding: 2mm;">{$col2}</td>
			<td style="font-size: 11px;padding: 2mm;">{$col3}</td>
			<td style="font-size: 11px;padding: 2mm;">{$col4}</td>
			<td style="font-size: 11px;padding: 2mm;">{$col5}</td>
			<td style="font-size: 11px;padding: 2mm;">{$col6}</td>
			<td style="font-size: 11px;padding: 2mm;text-align: right;">{$col7}</td>
			<td style="font-size: 11px;padding: 2mm;text-align: right;">{$col8}</td>
			<td style="font-size: 11px;padding: 2mm;text-align: right;">{$col9}</td>
			<td style="font-size: 11px;padding: 2mm;">{$col10}</td>
		</tr>
HTML;
	}
	?>
		<tr>
			<td colspan="6" style="font-size: 10px;padding: 2mm;text-align:right;">GRAND TOTAL</td>
			<td style="font-size: 10px;padding: 2mm;text-align: right;"><?=number_format($grandTotalDpp,2)?></td>
			<td style="font-size: 10px;padding: 2mm;text-align: right;"><?=number_format($grandTotalPpn,2)?></td>
			<td style="font-size: 10px;padding: 2mm;text-align: right;"><?=number_format($grandTotalTotal,2)?></td>
			<td style="font-size: 10px;padding: 2mm;"></td>
		</tr>
	</tbody>
</table>
