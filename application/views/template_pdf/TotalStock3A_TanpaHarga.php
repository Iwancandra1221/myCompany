<style type="text/css">
	h5{
		margin: 2px 0;
		font-size: 8pt;
	}
	table, tr, td{
		/*border: solid 1px black;*/
		font-size: 8pt;
	}
	.table-header{
		border-bottom: solid 1px black;border-top: solid 1px black;
	}
</style>
<?php
foreach ($getStock as $gudangKey => $gudangValue) {
	echo <<<HTML
	<h5>{$gudangKey}&emsp;{$getGudang[$gudangKey]}</h5>
HTML;
	foreach($gudangValue as $divisiKey => $divisiValue){
		echo <<<HTML
		<br>
		<h5 style="border: solid 1px black; color:black;width: 30%">{$divisiKey}</h5>
		<table style="width: 100%;">
			<tr>
				<th style="width: 15%;" class="table-header">Kode Sparepart</th>
				<th style="width: 20%;" class="table-header">Nama Sparepart</th>
				<th style="width: 5%;" class="table-header">S Awal</th>
				<th style="width: 5%;" class="table-header">Beli</th>
				<th style="width: 5%;" class="table-header">Jual</th>
				<th style="width: 5%;" class="table-header">Return</th>
				<th style="width: 8%;" class="table-header">Mutasi T</th>
				<th style="width: 8%;" class="table-header">Mutasi K</th>
				<th style="width: 8%;" class="table-header">Terima</th>
				<th style="width: 5%;" class="table-header">Keluar</th>
				<th style="width: 15%;" class="table-header">S Akhir</th>
			</tr>	
HTML;
		foreach($divisiValue as $merkKey => $merkValue){
			echo <<<HTML
			<tr>
				<td colspan="11" style="font-weight: bold;">{$merkKey}</td>
			</tr>	
HTML;
			$saldoAwal = 0;
			$beli = 0;
			$jual = 0;
			$retur = 0;
			$mutasiTerima = 0;
			$mutasiKeluar = 0;
			$terima = 0;
			$keluar = 0;
			$saldoAkhir = 0;
			//echo "Merk".$merkKey;
			foreach($merkValue as $key => $value){
				$saldoAwal += $value['Saldo_Awal'];
				$beli += $value['Beli'];
				$jual += $value['Jual'];
				$retur += $value['Retur'];
				$mutasiTerima += $value['Mutasi_Terima'];
				$mutasiKeluar += $value['Mutasi_Keluar'];
				$terima += $value['Terima'];
				$keluar += $value['Keluar'];
				$saldoAkhir += $value['Saldo_Akhir'];
				echo "
				<tr>
					<td>".$value['Kd_Sparepart']."</td>
					<td>".$value['Nm_Sparepart']."</td>
					<td>".( $value['Saldo_Awal'] == 0 ? '-' : $value['Saldo_Awal'] )."</td>
					<td>".( $value['Beli'] == 0 ? '-' : $value['Beli'] )."</td>
					<td>".( $value['Jual'] == 0 ? '-' : $value['Jual'] )."</td>
					<td>".( $value['Retur'] == 0 ? '-' : $value['Retur'] )."</td>
					<td>".( $value['Mutasi_Terima'] == 0 ? '-' : $value['Mutasi_Terima'] )."</td>
					<td>".( $value['Mutasi_Keluar'] == 0 ? '-' : $value['Mutasi_Keluar'] )."</td>
					<td>".( $value['Terima'] == 0 ? '-' : $value['Terima'] )."</td>
					<td>".( $value['Keluar'] == 0 ? '-' : $value['Keluar'] )."</td>
					<td>".( $value['Saldo_Akhir'] == 0 ? '-' : $value['Saldo_Akhir'] )."</td>
				</tr>";
			}
			// <td>".( $saldoAwal == 0 ? '-' : $saldoAwal)."</td>
			// <td>".( $beli == 0 ? '-' : $beli)."</td>
			// <td>".( $jual == 0 ? '-' : $jual)."</td>
			// <td>".( $retur == 0 ? '-' : $retur)."</td>
			// <td>".( $mutasiTerima == 0 ? '-' : $mutasiTerima)."</td>
			// <td>".( $mutasiKeluar == 0 ? '-' : $mutasiKeluar)."</td>
			// <td>".( $terima == 0 ? '-' : $terima)."</td>
			// <td>".( $keluar == 0 ? '-' : $keluar)."</td>
			echo "
			<tr>
				<td style='border-top:solid black 1px; font-weight: bold;text-align: right;'>TOTAL &nbsp;&nbsp;</td>
				<td style='border-top:solid black 1px; font-weight: bold;'>".$merkKey."</td>
				<td colspan='8' style='border-top:solid black 1px; '></td>
				<td style='border-top:solid black 1px; '>".( $saldoAkhir == 0 ? '-' : number_format($saldoAkhir,0) )."</td>
			</tr>
			<tr>
				<td colspan='11'>&nbsp;</td>
			</tr>";
			
		}
		echo <<<HTML
		</table>
HTML;
	}

}
?>
