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
	.table-footer{
		border-top: solid 1px black;
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
				<th style="width: 15%;" class="table-header">Harga</th>
				<th style="width: 5%;" class="table-header">S Awal</th>
				<th style="width: 5%;" class="table-header">Beli</th>
				<th style="width: 5%;" class="table-header">Jual</th>
				<th style="width: 5%;" class="table-header">Return</th>
				<th style="width: 8%;" class="table-header">Mutasi T</th>
				<th style="width: 8%;" class="table-header">Mutasi K</th>
				<th style="width: 8%;" class="table-header">Terima</th>
				<th style="width: 5%;" class="table-header">Keluar</th>
				<th style="width: 10%;" class="table-header">S Akhir</th>
				<th style="width: 15%;" class="table-header">GranTotal</th>
			</tr>	
HTML;
		foreach($divisiValue as $merkKey => $merkValue){
			echo <<<HTML
			<tr>
				<td colspan="12" style="font-weight: bold;">{$merkKey}</td>
			</tr>	
HTML;
			$harga = 0;
			$saldoAwal = 0;
			$beli = 0;
			$jual = 0;
			$retur = 0;
			$mutasiTerima = 0;
			$mutasiKeluar = 0;
			$terima = 0;
			$keluar = 0;
			$saldoAkhir = 0;
			$totalGrandTotal = 0;

			//echo "Merk".$merkKey;
			foreach($merkValue as $key => $value){
				$harga += $value['Harga'];
				$saldoAwal += $value['Saldo_Awal'];
				$beli += $value['Beli'];
				$jual += $value['Jual'];
				$retur += $value['Retur'];
				$mutasiTerima += $value['Mutasi_Terima'];
				$mutasiKeluar += $value['Mutasi_Keluar'];
				$terima += $value['Terima'];
				$keluar += $value['Keluar'];
				$saldoAkhir += $value['Saldo_Akhir'];

				$grandTotal = $harga + $saldoAwal + $beli + $jual + $retur + $mutasiTerima + $mutasiKeluar + $terima + $keluar + $saldoAkhir;
				$totalGrandTotal += $grandTotal;
				
				echo '
				<tr>
					<td>'.$value['Kd_Sparepart'].'</td>
					<td>'.$value['Nm_Sparepart'].'</td>
					<td>'.( $value['Harga'] == 0 ? '-' : number_format($value['Harga'] ,0)).'</td>
					<td>'.( $value['Saldo_Awal'] == 0 ? '-' : number_format($value['Saldo_Awal'],0) ).'</td>
					<td>'.( $value['Beli'] == 0 ? '-' : number_format($value['Beli'],0) ).'</td>
					<td>'.( $value['Jual'] == 0 ? '-' : number_format($value['Jual'],0) ).'</td>
					<td>'.( $value['Retur'] == 0 ? '-' : number_format($value['Retur'],0) ).'</td>
					<td>'.( $value['Mutasi_Terima'] == 0 ? '-' : number_format($value['Mutasi_Terima'],0) ).'</td>
					<td>'.( $value['Mutasi_Keluar'] == 0 ? '-' : number_format($value['Mutasi_Keluar'],0) ).'</td>
					<td>'.( $value['Terima'] == 0 ? '-' : number_format($value['Terima'],0) ).'</td>
					<td>'.( $value['Keluar'] == 0 ? '-' : number_format($value['Keluar'],0) ).'</td>
					<td>'.( $value['Saldo_Akhir'] == 0 ? '-' : number_format($value['Saldo_Akhir'],0) ).'</td>
					<td>'.( $grandTotal == 0 ? '-' : number_format($grandTotal,0) ).'</td>
				</tr>';
			}
			$harga = '';
			echo '
			<tr>
				<td class="table-footer" style=" font-weight: bold;text-align: right;">TOTAL &nbsp;&nbsp;</td>
				<td class="table-footer" style=" font-weight: bold;" >'.$merkKey.'</td>
				<td class="table-footer">'.( $harga == 0 ? '' : $harga ).'</td>
				<td class="table-footer">'.( $saldoAwal == 0 ? '-' : $saldoAwal ).'</td>
				<td class="table-footer">'.( $beli == 0 ? '-' : $beli ).'</td>
				<td class="table-footer">'.( $jual == 0 ? '-' : $jual ).'</td>
				<td class="table-footer">'.( $retur == 0 ? '-' : $retur ).'</td>
				<td class="table-footer">'.( $mutasiTerima == 0 ? '-' : $mutasiTerima ).'</td>
				<td class="table-footer">'.( $mutasiKeluar == 0 ? '-' : $mutasiKeluar ).'</td>
				<td class="table-footer">'.( $terima == 0 ? '-' : $terima ).'</td>
				<td class="table-footer">'.( $keluar == 0 ? '-' : $keluar ).'</td>
				<td class="table-footer">'.( $saldoAkhir == 0 ? '-' : $saldoAkhir ).'</td>
				<td class="table-footer">'.( $totalGrandTotal == 0 ? '-' : number_format($totalGrandTotal,0) ).'</td>
			</tr>
			<tr>
				<td colspan="12">&nbsp;</td>
			</tr>';
			
		}
		echo <<<HTML
		</table>
HTML;
	}

}
?>
