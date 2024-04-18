<style type="text/css">
table, tr ,td, th{
	padding: 10px;
}
</style>
<div class="container">
	<div class="page-title">
		LAPORAN PEMBELIAN UMUM
	</div>
	<div class="form-container" >  

  		<div class="row">
        	<div class="col-12" align="center">
         		<table border="0" width="80%" summary="Table">
         			<tr>
         				<td width="80px">
         					Periode
         				</td>
         				<td>
         					<input type="text" id="from" class="form-control" value="<?php echo date('Y-m-d'); ?>">
         				</td>
         				<td align="center" width="50px">
         					S/D
         				</td>
         				<td>
         					<input type="text" id="until" class="form-control" value="<?php echo date('Y-m-d'); ?>">
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Supplier
         				</td>
         				<td colspan="3">
         					<select id="supplier" class="form-control">
         						<?php
								  	foreach ($supplier as $key => $s) {
								?>
										<option value="<?php echo rtrim($s['Kode_Supplier']); ?>"><?php echo $s['Nama_Supplier']; ?></option>
								<?php
								  	}
								?>
         					</select>
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Cabang
         				</td>
         				<td colspan="3">
         					<select id="cabang" class="form-control">
         						<?php
								  	foreach ($cabang as $key => $c) {
								?>
										<option value="<?php echo rtrim($c['Kode_Lokasi']); ?>"><?php if($c['Kode_Lokasi']!=='ALL'){ echo $c['Kode_Lokasi'].' - '.$c['Nama_Lokasi']; }else{ echo $c['Nama_Lokasi']; } ?></option>
								<?php
								  	}
								?>
         					</select>
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Gudang
         				</td>
         				<td colspan="3">
         					<select id="gudang" class="form-control">
         						<?php
								  	foreach ($gudang as $key => $g) {
								?>
										<option value="<?php echo rtrim($g['Kode_Gudang']); ?>"><?php echo $g['Nama_Gudang']; ?></option>
								<?php
								  	}
								?>
         					</select>
         				</td>
         			</tr>
         			<tr>
         				<td colspan="4" align="center">
         					<button class="btn btn-default" onclick="print_data('pdf');">
         						PDF
         					</button>
         					<button class="btn btn-default" onclick="print_data('excel');">
         						Excel
         					</button>
         				</td>
         			</tr>
         		</table>
        	</div> 
      </div> 
  </div>

</div>
<script type="text/javascript">
	$('#from,#until').datepicker({
		format: "yyyy-mm-dd",
		autoclose: true
	});

	function print_data(e){
		var from = document.getElementById('from').value;
		var until = document.getElementById('until').value;
		var supplier = document.getElementById('supplier').value;
		var cabang = document.getElementById('cabang').value;
		var gudang = document.getElementById('gudang').value;
		window.open('<?php echo site_url('LaporanPembelianUmum'); ?>/'+e+'/'+from+'/'+until+'/'+supplier+'/'+cabang+'/'+gudang,'_blank');
	}

	<?php
		if(!empty($_GET['error'])){
	?>
			alert('Data form harus diisi semua!!!');
	<?php
		}
	?>
</script>