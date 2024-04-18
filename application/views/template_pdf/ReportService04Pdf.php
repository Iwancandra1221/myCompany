<style>
	th{
		text-align: left;
	}
	th{
		border: 1px solid black;
	}
	table,tr,td,th{
/*		border: 1px solid black;*/
		border-collapse: collapse;
	}
	td,th{
		padding-left: 5px;
		padding-right: 5px;
	}
	th{
		text-align: center;
	}
</style>
<?php
	$body = '';
	if($report!=null){
		foreach($report as $key => $report_2){
			$kd_teknisi = '';
			$total_service = 0;
			foreach($report_2 as $key_2 => $value_2){
				$kd_teknisi = $value_2->Kd_Teknisi;
				$total_service+=1;
				$col1 = date('d M Y',strtotime($value_2->tgl_svc));
				$col2 = date('d M Y',strtotime($value_2->Tgl_Trans));
				$col3 = $value_2->no_svc;
				$col4 = $value_2->Kd_Brg;
				$col5 = date('d M Y',strtotime($value_2->Tgl_Selesai));
				$col6 = $value_2->Perbaikan;
				$body .= <<<HTML
				<tr>
					<td>{$col1}</td>
					<td>{$col2}</td>
					<td>{$col3}</td>
					<td>{$col4}</td>
					<td>{$col5}</td>
					<td>{$col6}</td>
				</tr>
HTML;
			}
			echo <<<HTML
			<table style="width:100%">
				<thead>
					<tr>
						<td colspan="6">Nama Teknisi: $key</td>
					</tr>
					<tr>
						<th>Tgl Masuk</th>
						<th>Tgl Trans</th>
						<th>No Svr</th>
						<th>Kode Barang</th>
						<th>Tgl Selesai</th>
						<th>Perbaikan</th>
					</tr>
				</thead>
				{$body}
				<tr>
					<td colspan="4" style="text-align:right;font-weight: bold;">{$kd_teknisi}</td>
					<td style="text-align:right;font-weight: bold;">{$key}</td>
					<td style="text-align:right;font-weight: bold;">Total Banyak Service : {$total_service}</td>
				</tr>
			</table>
			<br>
HTML;
			
		}
		
	}
	else{
		echo <<<HTML
		<table style="width:100%">
			<thead>
				<tr>
					<td>Nama Teknisi: </td>
				</tr>
				<tr>
					<th>Tgl Masuk</th>
					<th>Tgl Trans</th>
					<th>No Svr</th>
					<th>Kode Barang</th>
					<th>Tgl Selesai</th>
					<th>Perbaikan</th>
				</tr>
			</thead>
			{$body}
			
		</table>
HTML;
	}
	

	
	
?>