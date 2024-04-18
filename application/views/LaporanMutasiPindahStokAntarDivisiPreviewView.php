<style type="text/css">
	.row {
	 line-height:30px; 
	 vertical-align:middle;
	 clear:both;
	}
	.row-label, .row-input {
	 float:left;
	}
	.row-label {
	 padding-left: 15px;
	 width:180px;
	}
	.row-input {
	 width:420px;
	}
	input{
		color: black;
	}
	table, tr ,td, th{
		border:solid black 1px;
		color: black;
	}
	td, th{
		background: white;
	}
	body{
		background: #303331;
		color: white;
	}
</style>

<script>
</script>
<div class="" style="margin:15px;">
	<div>PT BHAKTI IDOLA TAMA 
		<span style="float: right;">Tanggal : <?=$tgl?></span>
	</div>
	<div>Laporan Mutasi Pindah Stock Dalam 1 Lokasi 
		<span style="float: right;">Cabang : <?=$cabang?></span>
	</div>
	<table class="table" style="width:100%;">
		<tr>
			<th scope="col" rowspan="2">Lokasi WH</th>
			<th scope="col" colspan="3">Gudang Sumber</th>
			<th scope="col" colspan="3">Gudang Target</th>
			<th scope="col" colspan="3">Item Yang Dimutasi</th>
			<th scope="col" rowspan="2">Ket</th>
		</tr>
		<tr>
			<th scope="col">Kode Gudang</th>
			<th scope="col">Nama Gudang</th>
			<th scope="col">No Mutasi 'K'</th>
			<th scope="col">Kode Gudang</th>
			<th scope="col">Nama Gudang</th>
			<th scope="col">No Mutasi 'T'</th>
			<th scope="col">Kode Produk</th>
			<th scope="col">Nama Produk</th>
			<th scope="col">QTY</th>
		</tr>
		<?php
		foreach($report as $value){
			$row1 = rtrim($value['Kd_LokasiWH'],' ');
			$row2 = rtrim($value['Gudang_Sumber'],' ');
			$row3 = rtrim($value['Nm_Gudang_Sumber'],' ');
			$row4 = rtrim($value['No_Mutasi'],' ');
			$row5 = rtrim($value['Gudang_Target'],' ');
			$row6 = rtrim($value['Nm_GUdang_Target'],' ');
			$row7 = rtrim($value['No_Ref'],' ');
			$row8 = rtrim($value['Kd_Brg'],' ');
			$row9 = rtrim($value['NM_BRG'],' ');
			$row10 = rtrim($value['Qty'],' ');
			$row11 = rtrim($value['Ket'],' ');
			echo <<<HTML
			<tr>
				<td>{$row1}</td>
				<td>{$row2}</td>
				<td>{$row3}</td>
				<td>{$row4}</td>
				<td>{$row5}</td>
				<td>{$row6}</td>
				<td>{$row7}</td>
				<td>{$row8}</td>
				<td>{$row9}</td>
				<td>{$row10}</td>
				<td>{$row11}</td>
			</tr>
HTML;
		}
		?>
	</table>
</div> <!-- /container -->

<script type="text/javascript">
	function showDP(){
		$('#tgl').datepicker('show');
	} 
</script>