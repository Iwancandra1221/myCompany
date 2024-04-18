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
<table autosize="1" style="overflow: wrap;width:297mm;">
	<?php  
	echo <<<HTML
		<thead>
			<tr>
				<th style="width:52.4285714286mm;font-size: 18px;padding: 2mm">NO BUKTI</th>
				<th style="width:32.4285714286mm;font-size: 18px;padding: 2mm;">TGL TRANS</th>
				<th style="width:42.4285714286mm;font-size: 18px;padding: 2mm;">TOTAL</th>
				<th style="width:42.4285714286mm;font-size: 18px;padding: 2mm;">SUPPLIER</th>
				<th style="width:32.4285714286mm;font-size: 18px;padding: 2mm;">BANK</th>
				<th style="width:30.4285714286mm;font-size: 18px;padding: 2mm;">KET</th>
			</tr>
		</thead>
		<tbody>
HTML;
	if($laporan!=''){
		foreach($laporan as $value){
			$col1 = rtrim($value['No_bukti'], ' ');
			$col2 = date('d/m/Y',strtotime( rtrim($value['Tgl_trans'], ' ') ));
			$col3 = number_format( rtrim($value['Total'], ' ') , 2);
			$col4 = rtrim($value['Nm_supl']);
			$col5 = rtrim($value['Bank'], ' ');

			$col6 = rtrim($value['No_giro'], ' ');

			$col7 = date('m/d/Y',strtotime( rtrim($value['Tgl_jatuhTempo'], ' ') ));
			$col8 = rtrim($value['Ket'], ' ').($col6 == '' ? '' :  (' - '.$col6) );
			echo <<<HTML
			<tr>
				<td style="font-size: 18px;padding: 2mm;text-align: center;">{$col1}</td>
				<td style="font-size: 18px;padding: 2mm;text-align: center;">{$col2}</td>
				<td style="font-size: 18px;padding: 2mm;text-align: right;">{$col3}</td>
				<td style="font-size: 18px;padding: 2mm;">{$col4}</td>
				<td style="font-size: 18px;padding: 2mm;">{$col5}</td>
				<td style="font-size: 18px;padding: 2mm;">{$col8}</td>
			</tr>
HTML;
		}
	}

	?>
	</tbody>
</table>
