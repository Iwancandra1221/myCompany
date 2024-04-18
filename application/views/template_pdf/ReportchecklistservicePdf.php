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

/* 	tr:nth-child(even) {background: white} */
/* 	tr:nth-child(odd) {background: #d5d5d5;} /* selects every odd row */*/
/* 	tr:first-child {*/
/*		background: white;*/
/*	}*/
	th{
		padding: 5px 0px;
		font-weight: bold;
	}
	.table-1, .table-1 tr, .table-1 td{
		border: unset;
	}
</style>
<table class="table-1" style="width:100%;">
	<tr>
		<td>BARANG</td>
		<td>: <?=$nm_brg?></td>
		<td style="width:15%;"></td>
		<td>NAMA KONSUMEN</td>
		<td>: <?=$nm_plg?></td>
	</tr>
	<tr>
		<td>MERK</td>
		<td>: <?=$merk?></td>
		<td></td>
		<td>NO HP</td>
		<td>: <?=$hp?></td>
	</tr>
	<tr>
		<td>NO SERI</td>
		<td>: <?=$no_seri?></td>
		<td></td>
		<td>ALAMAT</td>
		<td>: <?=$alm_plg?></td>
	</tr>
	<tr>
		<td>PRINTED DATE</td>
		<td>: <?=date('d-M-Y H:i:s')?></td>
		<td></td>
		<td>LOKASI</td>
		<td>: <?=$kd_lokasi?></td>
	</tr>
</table>
<br>
<table autosize="1" style="overflow: wrap;width:297mm;">
	<?php  
	echo <<<HTML
		<thead>
			<tr>
				<th style="width:5.2857142857%;font-size: 12px;padding: 2mm">NO</th>
				<th style="width:14.2857142857%;font-size: 12px;padding: 2mm;">DATA SERVICE</th>
				<th style="width:14.2857142857%;font-size: 12px;padding: 2mm;">PENGADUAN</th>
				<th style="width:14.2857142857%;font-size: 12px;padding: 2mm;">KERUSAKAN</th>
				<th style="width:14.2857142857%;font-size: 12px;padding: 2mm;">PENYEBAB</th>
				<th style="width:14.2857142857%;font-size: 12px;padding: 2mm;">PERBAIKAN</th>
				<th style="width:23.2857142857%;font-size: 12px;padding: 2mm;">PENGGANTIAN PART</th>
			</tr>
		</thead>
		<tbody>
HTML;
	if($laporan!=''){
		foreach($laporan as $key => $value){
			$col1 = ($key+1);
			$col2 = date('d-M-Y',strtotime($value['Tgl_Service'])).'<br>'.$value['No_Svc'].'<br>'.$value['Nama_Teknisi'];
			$col3 = $value['Pengaduan'];
			$col4 = $value['Kerusakan']=='' ? '-' : $value['Kerusakan'];
			$col5 = $value['Penyebab'];
			$col6 = $value['Perbaikan'];
			$col7 = '';
			if($value['penggantian_part']!=''){
				$col7 = str_replace(',','<br>',$value['penggantian_part']);
				
			}
			echo <<<HTML
			<tr>
				<td style="font-size: 12px;padding: 2mm;text-align: left;">{$col1}</td>
				<td style="font-size: 12px;padding: 2mm;text-align: left;">{$col2}</td>
				<td style="font-size: 12px;padding: 2mm;text-align: left;">{$col3}</td>
				<td style="font-size: 12px;padding: 2mm;">{$col4}</td>
				<td style="font-size: 12px;padding: 2mm;">{$col5}</td>
				<td style="font-size: 12px;padding: 2mm;">{$col6}</td>
				<td style="font-size: 12px;padding: 2mm;">{$col7}</td>
			</tr>
HTML;
		}
	}

	?>
	</tbody>
</table>
