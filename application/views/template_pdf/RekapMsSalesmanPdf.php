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
			$x = $value['Cabang'];
			$Kd_Slsman = $value['Kd_Slsman'];
			$Nm_Slsman = $value['Nm_Slsman'];
			$User_Id = $value['UserID'];
			$Nm_Lvl_Slsman = $value['Nm_Lvl_Slsman'];
			$Kd_Supervisor = $value['Kd_Supervisor'];
			$Nm_Spv = $value['Nm_Spv'];
			$AKTIF = $value['AKTIF'];
			$Kd_Lokasi = $value['Kd_Lokasi'];
			$body .= <<<HTML
			<tr>
				<td>{$x}</td>
				<td>{$Kd_Slsman}</td>
				<td>{$Nm_Slsman}</td>
				<td>{$User_Id}</td>
				<td>{$Nm_Lvl_Slsman}</td>
				<td>{$Kd_Supervisor}&nbsp;&nbsp;&nbsp;&nbsp;{$Nm_Spv}</td>
				<td>{$AKTIF}</td>
				<td>{$Kd_Lokasi}</td>
			</tr>
HTML;
		}
	}
	

	echo <<<HTML
	<table style="width:210mm">
		<thead>
			<tr>
				<th style="text-align: left; width:35mm;font-size: medium;">Cabang</th>
				<th style="text-align: left; width:35mm;font-size: medium;">Kode</th>
				<th style="text-align: left; width:35mm;font-size: medium;">Nama Salesman</th>
				<th style="text-align: left; width:10mm;font-size: medium;">User ID</th>
				<th style="text-align: left; width:35mm;font-size: medium;">Level Saleman</th>
				<th style="text-align: left; width:60mm;font-size: medium;">Atasan</th>
				<th style="text-align: left; width:10mm;font-size: medium;">Aktif</th>
				<th style="text-align: left; width:10mm;font-size: medium;">Kode Lokasi</th>
			</tr>
			<tr>
				<td colspan="8"><hr></td>
			</tr>
		</thead>
		<tbody>
			{$body}
		</tbody>
	</table>
	
HTML;
	
?>