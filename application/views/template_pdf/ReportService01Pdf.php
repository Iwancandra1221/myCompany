<style>
	th{
		text-align: left;
	}
	table,tr,td,th{
		border: 1px solid black;
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
	//Kode	Nama Supplier	Alamat Telepon Fax Keterangan
	$body = '';
	if($report!=null){
		foreach($report as $key => $value){
			$col1 = ($key+=1);
			$col2 = date('d-M-y',strtotime($value->tgl_svc));
			$col3 = $value->no_svc;
			$col4 = $value->Nm_plg;
			$col5 = $value->merk;
			$col6 = $value->Kd_Brg;
			$col7 = $value->No_Seri;
			$col8 = $value->selesai;
			$col9 = $value->Kembali;
			$col10 = $value->Nm_Teknisi;
			$col11 = number_format($value->Ongkos_Svc,0);
			$col12 = number_format($value->Home_Svc,0);
			$col13 = number_format($value->Total_PPN,0);

			$col14 = $value->type_svc;
			$col15 = $value->Pengaduan;
			$col16 = $value->Perbaikan;
			$body .= <<<HTML
			<tr>
				<td rowspan="2">{$col1}</td>
				<td rowspan="2">{$col2}<br>{$col14}</td>
				<td>{$col3}</td>
				<td>{$col4}</td>
				<td>{$col5}</td>
				<td>{$col6}</td>
				<td>{$col7}</td>
				<td>{$col8}</td>
				<td>{$col9}</td>
				<td>{$col10}</td>
				<td>{$col11}</td>
				<td>{$col12}</td>
				<td>{$col13}</td>
			</tr>
			<tr>
				<td colspan="3" style="padding-bottom:10px;">PENGADUAN: {$col15}</td>
				<td colspan="4" style="padding-bottom:10px;">PERBAIKAN: {$col16}<br></td>
				<td colspan="4"></td>
			</tr>
HTML;
		}
	}
	

	echo <<<HTML
	<table style="width:100%">
		<thead>
			<tr>
				<th></th>
				<th>Tgl Srv</th>
				<th>No Srv</th>
				<th>Nama Pelanggan</th>
				<th>Merk</th>
				<th>Kode Brg</th>
				<th>No Seri</th>
				<th>Selesai</th>
				<th>Kembali</th>
				<th>Teknisi</th>
				<th>Ongkos</th>
				<th>Home Svc</th>
				<th>PPN</th>
			</tr>
		</thead>
		{$body}
	</table>
HTML;
	
?>