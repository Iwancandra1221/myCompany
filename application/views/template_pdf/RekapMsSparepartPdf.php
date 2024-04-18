<style>
	th{
		text-align: left;
	}
</style>
<?php
	//Kode	Nama Supplier	Alamat Telepon Fax Keterangan
	$body = '';
	if($rekap!=null){
		foreach($rekap as $value){
			$Kd_sparepart = $value['Kd_sparepart'];
			$Merk = $value['Merk'];
			$Nm_sparepart = $value['Nm_sparepart'];
			$Aktif = $value['Aktif'];
			$Jns_sparepart = $value['Jns_sparepart'];
			$Tgl_Trans = $value['Tgl_Trans'];
			$Harga_Jual = number_format($value['Harga_Jual'],0);
			$User_Name = $value['User_Name'];
			$Disc1 = $value['Disc1'];
			$Disc2 = $value['Disc2'];
			$Disc3 = $value['Disc3'];
			$LastUpdate = $value['LastUpdate'];

			$body .= <<<HTML
			<tr>
				<td>{$Kd_sparepart}</td>
				<td>{$Nm_sparepart}</td>
				<td>{$Harga_Jual}</td>
				<td>{$Disc1}</td>
				<td>{$Disc2}</td>
				<td>{$Disc3}</td>
				<td>{$Aktif}</td>
				<td>{$User_Name}</td>
				<td>{$LastUpdate}</td>
			</tr>
HTML;
		}
	}
	

	echo <<<HTML
	<table style="width:297mm">
		<tr>
			<th style="text-align: left; width:33mm;font-size: medium;"></th>
			<th style="text-align: left; width:33mm;font-size: medium;"></th>
			<th style="text-align: left; width:33mm;font-size: medium;"></th>
			<th style="text-align: left; width:33mm;font-size: medium;"></th>
			<th style="text-align: left; width:33mm;font-size: medium;"></th>
			<th style="text-align: left; width:33mm;font-size: medium;"></th>
			<th style="text-align: left; width:33mm;font-size: medium;"></th>
			<th style="text-align: left; width:33mm;font-size: medium;"></th>
			<th style="text-align: left; width:33mm;font-size: medium;"></th>
		</tr>
		{$body}
	</table>
HTML;
	
?>