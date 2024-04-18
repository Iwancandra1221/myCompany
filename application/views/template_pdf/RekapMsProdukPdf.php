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
			$Divisi = $value['Divisi'];
			$Merk = $value['Merk'];
			$Jns_Brg = $value['Jns_Brg'];
			$Kd_Brg = $value['Kd_Brg'];
			$Nm_Brg = $value['Nm_Brg'];
			$HS_Code = $value['HS_Code'];
			$HARGA_JUAL = number_format($value['HARGA_JUAL'],0);
			$Disc1 = $value['Disc1'];
			$Disc2 = $value['Disc2'];
			$Disc3 = $value['Disc3'];
			$Tgl_Ganti_Harga2 = $value['Tgl_Ganti_Harga2'];
			$Aktif = $value['Aktif'];
			$User_Name = $value['User_Name'];
			$LastUpdate = $value['LastUpdate'];
			$Div = $value['Div'];
			$Type_Barang = $value['Type_Barang'];

			$body .= <<<HTML
			<tr>
				<td>{$Kd_Brg}</td>
				<td>{$Nm_Brg}</td>
				<td>{$HS_Code}</td>
				<td>{$HARGA_JUAL}</td>
				<td>{$Disc1}</td>
				<td>{$Disc2}</td>
				<td>{$Disc3}</td>
				<td>{$Tgl_Ganti_Harga2}</td>
				<td>{$Aktif}</td>
				<td>{$User_Name}</td>
				<td>{$LastUpdate}</td>
				<td>{$Div}</td>
				<td>{$Type_Barang}</td>
			</tr>
HTML;
		}
	}
	

	echo <<<HTML
	<table style="width:297mm">
		<tr>
			<th style="text-align: left; width:22.8461538462mm;font-size: medium;"></th>
			<th style="text-align: left; width:22.8461538462mm;font-size: medium;"></th>
			<th style="text-align: left; width:22.8461538462mm;font-size: medium;"></th>
			<th style="text-align: left; width:22.8461538462mm;font-size: medium;"></th>
			<th style="text-align: left; width:22.8461538462mm;font-size: medium;"></th>
			<th style="text-align: left; width:22.8461538462mm;font-size: medium;"></th>
			<th style="text-align: left; width:22.8461538462mm;font-size: medium;"></th>
			<th style="text-align: left; width:22.8461538462mm;font-size: medium;"></th>
			<th style="text-align: left; width:22.8461538462mm;font-size: medium;"></th>
			<th style="text-align: left; width:22.8461538462mm;font-size: medium;"></th>
			<th style="text-align: left; width:22.8461538462mm;font-size: medium;"></th>
			<th style="text-align: left; width:22.8461538462mm;font-size: medium;"></th>
			<th style="text-align: left; width:22.8461538462mm;font-size: medium;"></th>
		</tr>
		{$body}
	</table>
HTML;
	
?>