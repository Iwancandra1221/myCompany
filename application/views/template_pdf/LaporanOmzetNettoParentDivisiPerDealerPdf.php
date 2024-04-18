<?php
//LaporanOmzetNettoParentDivisiPerDealerPdf
?>
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
	td{
		border-top: solid 1px black;
		border-bottom: solid 1px black;
	}
	table,th,td{
		border-collapse: collapse;
		font-size: 12px;
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
 	tr:first-child {
		background: white;
	}
	th{
		padding: 5px 0px;
		font-weight: bold;
	}
</style>
<?php
$grandGrandTotal2 = 0;
$grandGrandTotal3 = 0;
$grandGrandTotal4 = 0;
$grandGrandTotal5 = 0;
$grandGrandTotal6 = 0;
foreach($reportTmp as $keyTmp => $valueTmp){
	echo '<p>PARTNER TYPE :'.$keyTmp.'</p>';

	foreach($valueTmp as $keyTmp2 => $valueTmp2){
		echo '<p>WILAYAH :'.$keyTmp2.'</p>';

		$granTotalCol2 = 0;
		$granTotalCol3 = 0;
		$granTotalCol4 = 0;
		$granTotalCol5 = 0;
		$granTotalCol6 = 0;
		foreach($valueTmp2 as $keyParentDiv => $valueParentDiv){
			$tbody = "";
			$totalCol2 = 0;
			$totalCol3 = 0;
			$totalCol4 = 0;
			$totalCol5 = 0;
			$totalCol6 = 0;
			foreach($valueParentDiv as $key => $value){

				$col1 = $value['Nm_Plg'];
				$col2 = $value['Total_Jual'];
				$col3 = $value['Total_RB'];
				$col4 = $value['Total_RC'];
				$col5 = $value['Total_Disc'];
				$col6 = $value['Omzet_Netto'];

				$totalCol2 += $col2;
				$totalCol3 += $col3;
				$totalCol4 += $col4;
				$totalCol5 += $col5;
				$totalCol6 += $col6;

				$col2 = number_format($value['Total_Jual'],0);
				$col3 = number_format($value['Total_RB'],0);
				$col4 = number_format($value['Total_RC'],0);
				$col5 = number_format($value['Total_Disc'],0);
				$col6 = number_format($value['Omzet_Netto'],0);
				$tbody .= <<<HTML
				<tbody>
					<tr>
						<td style="text-align:left;">{$col1}</td>
						<td style="text-align:right;">{$col2}</td>
						<td style="text-align:right;">{$col3}</td>
						<td style="text-align:right;">{$col4}</td>
						<td style="text-align:right;">{$col5}</td>
						<td style="text-align:right;">{$col6}</td>
					</tr>
				</tbody>
HTML;
			}
			$granTotalCol2 += $totalCol2;
			$granTotalCol3 += $totalCol3;
			$granTotalCol4 += $totalCol4;
			$granTotalCol5 += $totalCol5;
			$granTotalCol6 += $totalCol6;

			$totalCol2 = $totalCol2 == 0 ? '0' : number_format($totalCol2,2);
			$totalCol3 = $totalCol3 == 0 ? '0' : number_format($totalCol3,2);
			$totalCol4 = $totalCol4 == 0 ? '0' : number_format($totalCol4,2);
			$totalCol5 = $totalCol5 == 0 ? '0' : number_format($totalCol5,2);
			$totalCol6 = $totalCol6 == 0 ? '0' : number_format($totalCol6,2);
			echo <<<HTML
			<p>PARENTDIV : {$keyParentDiv}</p>
			<table autosize="1" style="overflow: wrap;width:100%;">
				<thead>
					<tr>
						<th style="border:none;width:16.6666666667%;padding: 2mm; text-align: left;">NAMA TOKO</th>
						<th style="border:none;width:16.6666666667%;padding: 2mm; text-align: right;">Total Jual</th>
						<th style="border:none;width:16.6666666667%;padding: 2mm; text-align: right;">Total RB</th>
						<th style="border:none;width:16.6666666667%;padding: 2mm; text-align: right;">Total RC</th>
						<th style="border:none;width:16.6666666667%;padding: 2mm; text-align: right;">Total Disc</th>
						<th style="border:none;width:16.6666666667%;padding: 2mm; text-align: right;">Omzet Netto</th>
					</tr>
				</thead>
				{$tbody}
				<tfoot>
					<tr>
						<th style="text-align:right;">{$keyParentDiv}</th>
						<th style="text-align:right;">{$totalCol2}</th>
						<th style="text-align:right;">{$totalCol3}</th>
						<th style="text-align:right;">{$totalCol4}</th>
						<th style="text-align:right;">{$totalCol5}</th>
						<th style="text-align:right;">{$totalCol6}</th>
					</tr>
				</tfoot>
			</table>
HTML;
		}
		$grandGrandTotal2 += $granTotalCol2;
		$grandGrandTotal3 += $granTotalCol3;
		$grandGrandTotal4 += $granTotalCol4;
		$grandGrandTotal5 += $granTotalCol5;
		$grandGrandTotal6 += $granTotalCol6;

		$granTotalCol2 = $granTotalCol2 == 0 ? '0' : number_format($granTotalCol2,2);
		$granTotalCol3 = $granTotalCol3 == 0 ? '0' : number_format($granTotalCol3,2);
		$granTotalCol4 = $granTotalCol4 == 0 ? '0' : number_format($granTotalCol4,2);
		$granTotalCol5 = $granTotalCol5 == 0 ? '0' : number_format($granTotalCol5,2);
		$granTotalCol6 = $granTotalCol6 == 0 ? '0' : number_format($granTotalCol6,2);
		echo <<<HTML
		<table autosize="1" style="overflow: wrap;width:100%;">
			<tr>
				<th style="border-top: solid 1px black;width:16.6666666667%;text-align: right;">Total PARTNER TYPE:</th>
				<th style="border-top: solid 1px black;width:16.6666666667%;text-align: right;">{$granTotalCol2}</th>
				<th style="border-top: solid 1px black;width:16.6666666667%;text-align: right;">{$granTotalCol3}</th>
				<th style="border-top: solid 1px black;width:16.6666666667%;text-align: right;">{$granTotalCol4}</th>
				<th style="border-top: solid 1px black;width:16.6666666667%;text-align: right;">{$granTotalCol5}</th>
				<th style="border-top: solid 1px black;width:16.6666666667%;text-align: right;">{$granTotalCol6}</th>
			</tr>
			<tr>
				<th style="text-align:right;">{$keyTmp2}</th>
				<th colspan="5"></th>
			</tr>
		</table>
		<br>
		<br>
HTML;
	}
}
$grandGrandTotal2 = $grandGrandTotal2 == 0 ? '0' : number_format($grandGrandTotal2,2);
$grandGrandTotal3 = $grandGrandTotal3 == 0 ? '0' : number_format($grandGrandTotal3,2);
$grandGrandTotal4 = $grandGrandTotal4 == 0 ? '0' : number_format($grandGrandTotal4,2);
$grandGrandTotal5 = $grandGrandTotal5 == 0 ? '0' : number_format($grandGrandTotal5,2);
$grandGrandTotal6 = $grandGrandTotal6 == 0 ? '0' : number_format($grandGrandTotal6,2);
echo <<<HTML
	<table autosize="1" style="overflow: wrap;width:100%;">
		<tr>
			<th style="border-top: solid 1px black;width:16.6666666667%;text-align: right;">GRAND TOTAL</th>
			<th style="border-top: solid 1px black;width:16.6666666667%;text-align: right;">{$grandGrandTotal2}</th>
			<th style="border-top: solid 1px black;width:16.6666666667%;text-align: right;">{$grandGrandTotal3}</th>
			<th style="border-top: solid 1px black;width:16.6666666667%;text-align: right;">{$grandGrandTotal4}</th>
			<th style="border-top: solid 1px black;width:16.6666666667%;text-align: right;">{$grandGrandTotal5}</th>
			<th style="border-top: solid 1px black;width:16.6666666667%;text-align: right;">{$grandGrandTotal6}</th>
		</tr>
	</table>
	<br>
	<br>
HTML;
?>
