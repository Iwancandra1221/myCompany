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
		padding: 2px 5px;
	}
	.table-parent{
		width: 100%;
		height: 500px;
		overflow: auto;
	}
	/*tr:not(:first-child) {
	  color: white;
	}*/

 	tr:nth-child(even) {background: white} 
 	tr:nth-child(odd) {background: #d5d5d5;} /* selects every odd row */*/
 	tr:first-child {
		background: white;
	}
	th{
		padding: 5px 0px;
		font-weight: bold;
	}
</style>
<table autosize="1" style="overflow: wrap; width:297mm;">
	<?php  
	$tmpNoRekening = 0;
	$total = 0;
	//hitung jumlah no rekening yang beda

	$iterasi = 0;
	echo <<<HTML
	<thead>
		<tr>
			<th style="width:55mm;font-size: 18px;padding: 2mm;">NO BUKTI</th>
			<th style="width:35mm;font-size: 18px;padding: 2mm;">TGL TRANS</th>
			<th style="width:50mm;font-size: 18px;padding: 2mm;">TOTAL</th>
			<th style="width:50mm;font-size: 18px;padding: 2mm;">SUPPLIER</th>
			<th style="width:107mm;font-size: 18px;padding: 2mm;">KET</th>
		</tr>
	</thead>
	<tbody>
HTML;
	if($laporan!=null){
		foreach($laporan as $key => $value){
			$col0 = 'NO. REKENING : '.rtrim($value['No_Rekening'],' ').' - '.$value['Bank'].' - PT.BHAKTI IDOLA TAMA';
			$col1 = rtrim($value['No_bukti'], ' ');
			$col2 = date('d/m/Y',strtotime( rtrim($value['Tgl_trans'], ' ') ));
			$col3 = number_format( rtrim($value['Total'], ' ') , 2);
			$col4 = rtrim($value['Nm_supl']);
			//$col5 = rtrim($value['Bank'], ' ');
			//$col6 = rtrim($value['No_giro'], ' ');
			//$col7 = date('m/d/Y',strtotime( rtrim($value['Tgl_jatuhTempo'], ' ') ));
			$col8 = rtrim($value['Ket'], ' ');

			
			//ini disimpen biar tau apakah iterasi sudah berubah
			$tmpIterasi = $iterasi;
			if($tmpNoRekening!=$value['No_Rekening']){
				$tmpNoRekening = $value['No_Rekening'];
				//jika iterasi ke 2 maka sisipin ini diatas no rekening untuk total rekening di atasnya lagi
				if($iterasi>0){
					$total = number_format($total,2);
					$noRekening_Sebelumnya = $laporan[($key-1)]['No_Rekening'];
					echo <<<HTML
					<tr>
						<td colspan="2" style="border-top: solid 1px black; text-align: center;font-size: 18px;padding: 2mm;background-color: yellow;">Ttl Rek {$noRekening_Sebelumnya}</td>
						<td style="border-top: solid 1px black;font-size: 18px;padding: 2mm;text-align: right;background-color: yellow;">{$total}</td>
						<td colspan="2" style="border-top: solid 1px black;font-size: 18px;padding: 2mm;background-color: yellow;"></td>
					</tr>
					<tr><td colspan="5" style="font-size: 18px;padding: 2mm;"></td></tr>
HTML;
					$total = 0;				
				}


				
				echo <<<HTML
				<tr>
					<td colspan="5" style="font-size: 18px;padding: 2mm;"><u>{$col0}</u></td>
				</tr>
HTML;
				$iterasi+=1;
			}

			$total += $value['Total'];
			echo <<<HTML
			<tr>
				<td style="font-size: 18px;">{$col1}</td>
				<td style="font-size: 18px;">{$col2}</td>
				<td style="font-size: 18px;text-align: right;">{$col3}</td>
				<td style="font-size: 18px;">{$col4}</td>
				<td style="font-size: 18px;">{$col8}</td>
			</tr>
HTML;
			
		}
		$total = number_format($total,2);
		$noRekening_Sebelumnya = $laporan[(count($laporan)-1)]['No_Rekening'];
		echo <<<HTML
			<tr>
				<td colspan="2" style="border-top: solid 1px black; text-align: center;font-size: 18px;padding: 2mm;background-color: yellow;">Ttl Rek {$noRekening_Sebelumnya}</td>
				<td style="border-top: solid 1px black;text-align: right;font-size: 18px;padding: 2mm;text-align: right;background-color: yellow;">{$total}</td>
				<td colspan="2" style="border-top: solid 1px black;font-size: 18px;padding: 2mm;background-color: yellow;"></td>
			</tr>
			<tr><td colspan="5" style="font-size: 18px;padding: 2mm;"></td></tr>
HTML;
	}
	?>
	</tbody>
</table>
