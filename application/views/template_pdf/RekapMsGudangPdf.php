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
			$Kd_Gudang = $value['Kd_Gudang'];
			$Nm_Gudang = $value['Nm_Gudang'];
			$Alm_Gudang = $value['Alm_Gudang'];
			$Jenis = $value['Jenis'];
			$location = $value['location'];
			$Kategori = substr($value['Kategori'],0,3);
			$Aktif = $value['Aktif'];
			$TipeGudang = $value['TipeGudang'];
			$Kd_LokasiWH = $value['Kd_LokasiWH'];
			$Entry_Time = $value['Entry_Time'];
			$modified_date = $value['modified_date'];

			$body .= <<<HTML
			<tr>
				<td>{$Kd_Gudang}</td>
				<td>{$Nm_Gudang}</td>
				<td>{$Alm_Gudang}</td>
				<td>{$location}</td>
				<td>{$Kategori}</td>
				<td>{$Aktif}</td>
				<td>{$TipeGudang}</td>
				<td>{$Kd_LokasiWH}</td>
				<td>{$Entry_Time}</td>
				<td>{$modified_date}</td>
			</tr>
HTML;
		}
	}
	

	echo <<<HTML
	<table style="width:100%">
		<tr>
			<th style="text-align: left; width:10%;font-size: medium;">Kode</th>
			<th style="text-align: left; width:15%;font-size: medium;">Nama Gudang</th>
			<th style="text-align: left; width:10%;font-size: medium;">Alamat</th>
			<th style="text-align: left; width:5%;font-size: medium;">Loc</th>
			<th style="text-align: left; width:5%;font-size: medium;">Kat</th>
			<th style="text-align: left; width:5%;font-size: medium;">Aktif</th>
			<th style="text-align: left; width:10%;font-size: medium;">Tipe Gud</th>
			<th style="text-align: left; width:10%;font-size: medium;">Kd Lok WH</th>
			<th style="text-align: left; width:20%;font-size: medium;">Entry Time</th>
			<th style="text-align: left; width:10%;font-size: medium;">Modified Date</th>
		</tr>	
		<tr>
			<td colspan="10"><hr></td>
		</tr>
		

		{$body}
	</table>
HTML;
	
?>
