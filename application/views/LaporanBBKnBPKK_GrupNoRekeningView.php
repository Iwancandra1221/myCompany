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
	table,th{
		border: solid 1px black;
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

/* 	tr:nth-child(even) {background: white} */
/* 	tr:nth-child(odd) {background: #d5d5d5;} /* selects every odd row */*/
/* 	tr:first-child {*/
/*		background: white;*/
/*	}*/
	th{
		padding: 5px 0px;
		font-weight: bold;
	}
</style>
<div class="container">
	<div class="page-title" style="margin-bottom: 0px"><?php echo(strtoupper($title));?></div>
	<div class="" style="text-align:center;">Periode : <?=date('d-M-Y',strtotime($tgl1))?> s/d <?=date('d-M-Y',strtotime($tgl2))?></div>
	<br>
	<div class="table-parent">
		<table style="width:1500px;">
		<tr>
			<th scope="col" style="width:10%;">NO BUKTI</th>
			<th scope="col" style="width:10%;">TGL TRANS</th>
			<th scope="col" style="width:10%;">TOTAL</th>
			<th scope="col" style="width:10%;">SUPPLIER</th>
			<th scope="col" style="width:5%;">BANK</th>
			<th scope="col" style="width:5%;">NO GIRO</th>
			<th scope="col" style="width:10%;">TGL JT</th>
			<th scope="col" style="width:30%;">KET</th>
		</tr>
		<?php  
		$tmpNoRekening = 0;
		$total = 0;
		//hitung jumlah no rekening yang beda

		$iterasi = 0;
		if($laporan!=null){
			foreach($laporan as $key => $value){
				$col0 = 'NO. REKENING : '.rtrim($value['No_Rekening'],' ').' - '.$value['Bank'].' - PT.BHAKTI IDOLA TAMA';
				$col1 = rtrim($value['No_bukti'], ' ');
				$col2 = date('d/m/Y',strtotime( rtrim($value['Tgl_trans'], ' ') ));
				$col3 = number_format( rtrim($value['Total'], ' ') , 2);
				$col4 = '';
				$col5 = rtrim($value['Bank'], ' ');
				$col6 = rtrim($value['No_giro'], ' ');
				$col7 = date('m/d/Y',strtotime( rtrim($value['Tgl_jatuhTempo'], ' ') ));
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
							<td colspan="2" style="border-top: solid 1px black; text-align: center;">Ttl Rek {$noRekening_Sebelumnya}</td>
							<td style="border-top: solid 1px black;">{$total}</td>
							<td colspan="5" style="border-top: solid 1px black;"></td>
						</tr>
						<tr><td style="padding-bottom: 10px;"></td></tr>
HTML;
						$total = 0;				
					}


					
					echo <<<HTML
					<tr>
						<td colspan="8"><u>{$col0}</u></td>
					</tr>
HTML;
					$iterasi+=1;
				}

				$total += $value['Total'];
				echo <<<HTML
				<tr>
					<td>{$col1}</td>
					<td>{$col2}</td>
					<td>{$col3}</td>
					<td>{$col4}</td>
					<td>{$col5}</td>
					<td>{$col6}</td>
					<td>{$col7}</td>
					<td>{$col8}</td>
				</tr>
HTML;
				
			}
			$total = number_format($total,2);
			$noRekening_Sebelumnya = $laporan[(count($laporan)-1)]['No_Rekening'];
			echo <<<HTML
				<tr>
					<td colspan="2" style="border-top: solid 1px black; text-align: center;">Ttl Rek {$noRekening_Sebelumnya}</td>
					<td style="border-top: solid 1px black;">{$total}</td>
					<td colspan="5" style="border-top: solid 1px black;"></td>
				</tr>
				<tr><td style="padding-bottom: 10px;"></td></tr>
HTML;
		}
		?>
		
		</table>
	</div>
</div>
