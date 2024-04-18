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

 	tr:nth-child(even) {background: white} 
 	tr:first-child {
		background: white;
	}
	th{
		padding: 5px 0px;
		font-weight: bold;
	}
	.border{
		border: solid 1px black;
	}
</style>
<table autosize="1" style="overflow: wrap;width:100%;">
	<?php  
	$currentDate = date('m/d/Y');
	$tgl1 = date('d-m-Y',strtotime( rtrim($tgl1, ' ') ));
	$tgl2 = date('d-m-Y',strtotime( rtrim($tgl2, ' ') ));
	echo <<<HTML
		<thead>
			<tr style="border:none;">
				<td colspan="6" style="border:none;">
					<span style="position: absolute;font-weight: normal;left: 5px;">{$currentDate}</span>
				</td>
			</tr>
			<tr class="header">
				<th colspan="6" style="position: relative;font-size: 12pt;padding-bottom: 10px;">
					REKAP PKL
					<br>
					{$nmDealer}
					<br>
					Periode : {$tgl1} s/d {$tgl2}
					<br>
				</th>
			</tr>
			<tr class="border">
				<th class="" style="text-align: left; width:6%;font-size: 10pt;padding: 2mm">Tgl SJ</th>
				<th class="" style="text-align: left; width:16%;font-size: 10pt;padding: 2mm;">No SJ</th>
				<th class="" style="text-align: left; width:16%;font-size: 10pt;padding: 2mm;">No PBB</th>
				<th class="" style="text-align: left; width:21%;font-size: 10pt;padding: 2mm;">No DO</th>
				<th class="" style="text-align: left; width:21%;font-size: 10pt;padding: 2mm;">No Faktur</th>
				<th class="" style="text-align: left; width:16%;font-size: 10pt;padding: 2mm;">No PO</th>
			</tr>
		</thead>
		<tbody>
HTML;
	if($laporan!='' && $laporan['result']=='SUCCESS'){
		foreach($laporan['data'] as $value){
			$col1 = date('d M Y',strtotime( rtrim($value['Tgl_Faktur'], ' ') )); 
			$col2 = rtrim($value['No_Faktur'], ' ');
			$col3 = rtrim($value['No_PU'], ' ');
			$col4 = rtrim($value['No_DO']);
			$col5 = rtrim($value['No_Faktur_Baru'], ' ');
			$col6 = rtrim($value['No_PO'], ' ');

			echo <<<HTML
			<tr class="border: solid 1px black;">
				<td class="" style="text-align: left; font-size: 10;padding: 1mm 2mm;">{$col1}</td>
				<td class="" style="text-align: left; font-size: 10;padding: 1mm 2mm;">{$col2}</td>
				<td class="" style="text-align: left; font-size: 10;padding: 1mm 2mm;">{$col3}</td>
				<td class="" style="text-align: left; font-size: 10;padding: 1mm 2mm;">{$col4}</td>
				<td class="" style="text-align: left; font-size: 10;padding: 1mm 2mm;">{$col5}</td>
				<td class="" style="text-align: left; font-size: 10;padding: 1mm 2mm;">{$col6}</td>
			</tr>
HTML;
		}
	}

	?>
	</tbody>
</table>
