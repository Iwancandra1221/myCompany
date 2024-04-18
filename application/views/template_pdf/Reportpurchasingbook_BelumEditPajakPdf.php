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

echo <<<HTML
<table>
	<tr>
		<td style="border-bottom: solid 1px black;font-weight: bold;">No</td>
		<td style="border-bottom: solid 1px black;font-weight: bold;">No BPB/PU</td>
		<td style="border-bottom: solid 1px black;font-weight: bold;">Tgl BPB/PU</td>
		<td style="border-bottom: solid 1px black;font-weight: bold;"></td>
		<td style="border-bottom: solid 1px black;font-weight: bold;">Supplier</td>
		<td style="border-bottom: solid 1px black;font-weight: bold;">Gudang</td>
	</tr>
HTML;
	foreach($rekapTmp as $key => $value){
		$col0 = $key+1;
		$col1 = $value['No_PU'];
		$col2 = date('d-m-Y',strtotime($value['Tgl_PU']));
		$col3 = $value['Kategori_Brg'];
		$col4 = $value['Nm_Supl'];
		$col5 = '';
		if($key==0){
			$col5 = $value['Nm_Gudang'];
		}
		echo <<<HTML
		<tr>
			<td>{$col0}</td>
			<td>{$col1}</td>
			<td>{$col2}</td>
			<td>{$col3}</td>
			<td>{$col4}</td>
			<td>{$col5}</td>
		</tr>
HTML;
	}
	echo <<<HTML
	</table>
	<br>
HTML;
?>
