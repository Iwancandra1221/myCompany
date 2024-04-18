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
<table autosize="1" style="overflow: wrap; width:100%">
	<?php  
	$tmpNoRekening = 0;
	$total = 0;
	//hitung jumlah no rekening yang beda
	$width_col1 = "width:20%;";
	$width_col2 = "width:15%;";
	$width_col3 = "width:15%;";
	$width_col4 = "width:20%;";
	$width_col5 = "width:30%;";
	$iterasi = 0;
	echo <<<HTML
	<thead>
		<tr>
			<th colspan="13" style="text-align: left;border: 0px solid white">{$printDate}</th>
		</tr>
		<tr>
			<td colspan="13" style="text-align: cemter;font-size: 14pt;border: 0px solid white;border-bottom: 1px solid black;">{$title}<br></td>
		</tr>
		<tr>
			<th style="{$width_col1}text-align: center;font-size: 7pt;padding: 2mm;"><b>NO BUKTI</b></th>
			<th style="{$width_col2}text-align: center;font-size: 7pt;padding: 2mm;"><b>TGL TRANS</b></th>
			<th style="{$width_col3}text-align: center;font-size: 7pt;padding: 2mm;"><b>TOTAL</b></th>
			<th style="{$width_col4}text-align: center;font-size: 7pt;padding: 2mm;"><b>SUPPLIER</b></th>
			<th style="{$width_col5}text-align: center;font-size: 7pt;padding: 2mm;"><b>KET</b></th>
		</tr>
	</thead>
	<tbody>
HTML;
	
	if($laporan!=null){
		//distinct kolom
		$unikKolom = array();
		foreach($laporan as $key => $value){
			$md5 = md5($value['No_bukti'].$value['Tgl_trans'].$value['Total'].$value['Nm_supl'].$value['Ket']);
			$unikKolom[$md5] = $value;
		}
		$laporan = array();
		foreach($unikKolom as $value){
			$laporan[] = $value;
		}
		
		$dataNoBukti = array();
		$noBuktiArray = array_column($laporan, 'No_bukti');
		$noBuktiRowNum = array_count_values($noBuktiArray);
		$row = 0;
		$NextMergeRow = $row+1;
		$rowNum = 1;

		foreach($laporan as $key => $value){
			$row+=1;
			$col0 = 'NO. REKENING : '.rtrim($value['No_Rekening'],' ').' - '.$value['Bank'].' - PT.BHAKTI IDOLA TAMA';
			$col1 = rtrim($value['No_Bukti_Origin'], ' ');
			$col2 = date('d/m/Y',strtotime( rtrim($value['Tgl_trans'], ' ') ));

			$col3 = rtrim($value['Total'], ' ');

			$col4 = rtrim($value['Nm_supl']);
			$col5 = rtrim($value['Ket'], ' ');

			$col3 = number_format($col3,0);
			//ini disimpen biar tau apakah iterasi sudah berubah
			$tmpIterasi = $iterasi;
			if($tmpNoRekening!=$value['No_Rekening']){
				$tmpNoRekening = $value['No_Rekening'];
				//jika iterasi ke 2 maka sisipin ini diatas no rekening untuk total rekening di atasnya lagi
				if($iterasi>0){
					$total = number_format($total,2);
					$noRekening_Sebelumnya = $laporan[($key-1)]['No_Rekening'];
					$row+=1;
					echo <<<HTML
					<tr>
						<td colspan="2" style="border-top: solid 1px black; text-align: center;font-size: 8pt;padding: 2mm;background-color: yellow;">Ttl Rek {$noRekening_Sebelumnya}</td>
						<td style="border-top: solid 1px black;font-size: 8pt;padding: 2mm;text-align: right;background-color: yellow;">{$total}</td>
						<td colspan="2" style="border-top: solid 1px black;font-size: 8pt;padding: 2mm;background-color: yellow;"></td>
					</tr>
					<tr><td colspan="5" style="font-size: 8pt;padding: 2mm;"></td></tr>
HTML;
					$total = 0;				
				}

				echo <<<HTML
				<tr>
					<td colspan="5" style="width:100%;font-size: 8pt;padding: 2mm;"><u>{$col0}</u></td>
				</tr>
HTML;
				$iterasi+=1;

				$rowNum = $noBuktiRowNum[$value['No_bukti']];
				$NextMergeRow = ($row+$rowNum -1);
				$row = $NextMergeRow;
			}


			if($row == $NextMergeRow){
				$rowNum = $noBuktiRowNum[$value['No_bukti']];
			}
			echo <<<HTML
			<tr>
HTML;
			if($row == $NextMergeRow){
				echo <<<HTML
				<td style="{$width_col1}font-size: 8pt;" rowspan="{$rowNum}">{$col1}</td>
HTML;
			}

			echo <<<HTML
				<td style="{$width_col2}font-size: 8pt;">{$col2}</td>
HTML;
			
			
			if($row == $NextMergeRow){
				echo <<<HTML
				<td align="right" style="{$width_col3}font-size: 8pt;" rowspan="{$rowNum}">{$col3}</td>
HTML;
			}
			echo <<<HTML
				<td style="{$width_col4}font-size: 8pt;">{$col4}</td>
				<td style="{$width_col5}font-size: 8pt;">{$col5}</td>
			</tr>
HTML;
			
			
			if($row == $NextMergeRow){
				$NextMergeRow = ($row+$rowNum);
				$total += $value['Total'];
			}
		}
		$total = number_format($total);
		$noRekening_Sebelumnya = $laporan[(count($laporan)-1)]['No_Rekening'];
		echo <<<HTML
			<tr>
				<td colspan="2" style="border-top: solid 1px black; text-align: center;font-size: 8pt;padding: 2mm;background-color: yellow;">Ttl Rek {$noRekening_Sebelumnya}</td>
				<td style="border-top: solid 1px black;text-align: right;font-size: 8pt;padding: 2mm;text-align: right;background-color: yellow;">{$total}</td>
				<td colspan="2" style="border-top: solid 1px black;font-size: 8pt;padding: 2mm;background-color: yellow;"></td>
			</tr>
			<tr><td colspan="5" style="font-size: 8pt;padding: 2mm;"></td></tr>
HTML;
	}
	?>
	</tbody>
</table>
