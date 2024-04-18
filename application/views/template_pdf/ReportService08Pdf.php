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
	$total_col5 = 0;
	$total_col6 = 0;
	$total_col7 = 0;
	$total_col8 = 0;
	$total_col9 = 0;
	$total_col10 = 0;
	$total_col11 = 0;
	if($report!=null){
		foreach($report as $key => $value){
			$col1 = ($key+=1);
			$col2 = date('d-M-y',strtotime($value->Tgl_Svc));
			$col3 = date('d-M-y',strtotime($value->Tgl_Trans));
			$col4 = $value->No_Svc;
			$col5 = $value->Ongkos_Svc;
			$col6 = $value->PPN;
			$col7 = '0';
			$col8 = $value->PPH;
			$col9 = ($value->Ongkos_Svc + $value->PPN);
			$col10 = $value->grandtotal;
			$col11 = ($col9 + $col10);
			$col12 = ($value->Metode_Bayar);

			$total_col5 += $col5;
			$total_col6 += $col6;
			$total_col7 += $col7;
			$total_col8 += $col8;
			$total_col9 += $col9;
			$total_col10+= $col10;
			$total_col11 += $col11;

			$col5 =  number_format($col5,0);
			$col6 = number_format($col6,0);

			$col9 =  number_format($col9,0);
			$col10 = number_format($col10,0);
			$col11 = number_format($col11,0);
			$body .= <<<HTML
			<tr>
				<td>{$col1}</td>
				<td>{$col2}</td>
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
			</tr>
HTML;
		}
		$total_col5  = number_format($total_col5,0);
		$total_col6  = number_format($total_col6,0);
		$total_col7  = number_format($total_col7,0);
		$total_col8  = number_format($total_col8,0);
		$total_col9  = number_format($total_col9,0);
		$total_col10  = number_format($total_col10,0);
		$total_col11 = number_format($total_col11,0);
		$body .= <<<HTML
			<tr>
				<td colspan="4">Total CASH</td>
				<td>{$total_col5}</td>
				<td>{$total_col6}</td>
				<td>{$total_col7}</td>
				<td>{$total_col8}</td>
				<td>{$total_col9}</td>
				<td>{$total_col10}</td>
				<td>{$total_col11}</td>
			</tr>
HTML;
	}
	

	echo <<<HTML
	<table style="width:100%">
		<thead>
			<tr>
				<th></th>
				<th>Tgl Srv</th>
				<th>Tgl Trans</th>
				<th>No Srv</th>
				<th>Ongkos Kerja</th>
				<th>PPN</th>
				<th>Transport</th>
				<th>PPH</th>
				<th>Subtotal</th>
				<th>Sparepart</th>
				<th>Total</th>
				<th>Metode Bayar</th>
			</tr>
		</thead>
		{$body}
	</table>
HTML;
	
?>