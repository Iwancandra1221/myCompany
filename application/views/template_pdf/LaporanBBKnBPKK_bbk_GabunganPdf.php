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
		
		border-collapse: collapse;
		font-size: 8pt;
	}
	th,td{
		border: solid 1px black;
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
<table style="width:100%;overflow: auto;" autosize="1">
	<?php  
	$width_col1 = "width:20%;";
	$width_col2 = "width:15%;";
	$width_col3 = "width:15%;";
	$width_col4 = "width:20%;";
	$width_col5 = "width:30%;";
	echo <<<HTML
		<thead>
			<tr>
				<th colspan="13" style="text-align: left;border: 0px solid white">{$printDate}</th>
			</tr>
			<tr>
				<td colspan="13" style="text-align: cemter;font-size: 14pt;border: 0px solid white;border-bottom: 1px solid black;">{$title}<br></td>
			</tr>
			<tr>
				<th style="{$width_col1}text-align: center;"><b>NO BUKTI</b></th>
				<th style="{$width_col2}text-align: center;"><b>TGL TRANS</b></th>
				<th style="{$width_col3}text-align: center;"><b>TOTAL</b></th>
				<th style="{$width_col4}text-align: center;"><b>SUPPLIER</b></th>
				<th style="{$width_col5}text-align: center;"><b>KET</b></th>
			</tr>
		</thead>
		<tbody>
HTML;
	if($laporan!=''){
		// $unikKolom = array();
		// foreach($laporan as $key => $value){
		// 	$md5 = md5($value['No_Bukti_Origin'].$value['Tgl_trans'].$value['Total'].$value['Nm_supl'].$value['Ket']);
		// 	$unikKolom[$md5] = $value;
		// }
		$unikKolom = array();
		foreach($laporan as $key => $value){
			$unikKolom[rtrim($value['No_Bukti_Origin'])] = $value;
		}
		$laporan = array();
		$laporan = $unikKolom;

		$dataNoBukti = array();
		$noBuktiArray = array_column($laporan, 'No_Bukti_Origin');
		$noBuktiRowNum = array_count_values($noBuktiArray);

		$row = 0;
		$NextMergeRow = $row+1;
		$rowNum = 1;
		foreach($laporan as $value){
			$row+=1;
			if($row == $NextMergeRow){
				$rowNum = $noBuktiRowNum[$value['No_Bukti_Origin']];
			}
			$col1 = rtrim($value['No_Bukti_Origin'], ' ');
			$col2 = date('d/m/Y',strtotime( rtrim($value['Tgl_trans'], ' ') ));
			$col3 = (float)rtrim($value['Total'], ' ');
			$col4 = rtrim($value['Nm_supl']);
			$col5 = rtrim($value['Ket'], ' ');

			$col3 = number_format($col3,0);
			echo <<<HTML
			<tr>
HTML;

			if($row == $NextMergeRow){
				echo <<<HTML
				<td style="{$width_col1}text-align: center;" rowspan="{$rowNum}">{$col1}</td>
HTML;
			}
			echo <<<HTML
				<td style="{$width_col2}text-align: center;">{$col2}</td>
HTML;
			
			if($row == $NextMergeRow){
				echo <<<HTML
				<td align="right" style="{$width_col3}" rowspan="{$rowNum}">{$col3}</td>
HTML;
			}
			echo <<<HTML
				<td style="{$width_col4}">{$col4}</td>
				
				<td style="{$width_col5}">{$col5}</td>
			</tr>
HTML;

			if($row == $NextMergeRow){
				$NextMergeRow = ($row+$rowNum);
			}
		}
	}

	?>
	</tbody>
</table>
