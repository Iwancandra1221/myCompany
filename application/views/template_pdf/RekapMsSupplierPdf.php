<style>
	table{
		width:100%;
		border-collapse:collapse;
		border:0.5px solid #000;
	}
	td, th{
		border:0.5px solid #000;
		padding:5px;
	}
	th{
		text-align: left;
	}
</style>
<?php
	//Kode	Nama Supplier	Alamat Telepon Fax Keterangan
	$body = '';
	if ($rekap != null) {
		foreach($rekap as $value){
			$Kd_Supl = $value['Kd_Supl'];
			$Nm_Supl = $value['Nm_Supl'];
			$Alm_Supl = $value['Alm_Supl'];
			$Npwp = $value['NPWP'];
			$Telp = $value['Telp'];
			$Fax = $value['Fax'];
			$Ket = $value['Ket'];
			$Email = $value['Email'];
			$Bank = $value['Bank'];
			$Nm_Pemilik = $value['Nm_Pemilik'];
			$No_Rekening = $value['No_Rekening'];
			$body .= '
			<tr>
				<td>'.$Kd_Supl.'</td>
				<td>'.$Nm_Supl.'</td>
				<td>'.$Alm_Supl.'</td>
				<td>'.$Npwp.'</td>
				<td>'.$Email.'</td>
				<td>'.$Telp.'</td>
				<td>'.$Fax.'</td>
				<td>'.$Bank.'</td>
				<td>'.$Nm_Pemilik.'</td>
				<td>'.$No_Rekening.'</td>
				<td>'.$Ket.'</td>
			</tr>';
		}
	}


	echo '
	<table>
		<thead>
			<tr>
				<th>Kode</th>
				<th>Nama Supplier</th>
				<th>Alamat</th>
				<th>NPWP</th>
				<th>Email</th>
				<th>Telepon</th>
				<th>Fax</th>
				<th>Bank</th>
				<th>Nama Pemilik</th>
				<th>No Rekening</th>
				<th>Keterangan</th>
			</tr>
		</thead>
		<tbody>
			'.$body.'
		</tbody>
		
	</table>';
	
?>