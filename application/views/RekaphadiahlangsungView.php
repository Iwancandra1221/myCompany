<style type="text/css">
table, tr ,td, th{
	padding: 10px;
}
</style>
<div class="container">
	<div class="page-title">
		REKAP HADIAH LANGSUNG
	</div>
	<div class="form-container" >  

  		<div class="row">
        	<div class="col-12" align="center">
         		<table border="0" width="80%" summary="Table">
         			<tr>
         				<td width="120px">
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
         					Partner Type
         				</td>
         				<td>
         					<select id="partner_type" class="form-control">
         						<option value="ALL">ALL</option>
         						<?php
         							if(!empty($partner_type['data'])){
	         						  	foreach ($partner_type['data'] as $key => $w) {
	         						  		if(!empty($w['partner_type'])){
								?>
												<option value="<?php echo rtrim($w['partner_type']); ?>"><?php echo $w['partner_type']; ?></option>
								<?php
											}
										}
								  	}
								?>
         					</select>
         				</td>

         				<td>
         					Wilayah
         				</td>
         				<td>
         					<select id="wilayah" class="form-control">
         						<option value="ALL">ALL</option>
         						<?php
         							if(!empty($wilayah['data'])){
         						  		foreach ($wilayah['data'] as $key => $w) {
								?>
											<option value="<?php echo rtrim($w['WILAYAH']); ?>"><?php echo $w['WILAYAH']; ?></option>
								<?php
										}
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
		var partner_type = document.getElementById('partner_type').value;
		var wilayah = document.getElementById('wilayah').value;
		window.open('<?php echo site_url('Rekaphadiahlangsung'); ?>/'+e+'/'+from+'/'+until+'/'+partner_type+'/'+wilayah,'_blank');
	}

	<?php
		if(!empty($_GET['error'])){
	?>
			alert('Data form harus diisi semua!!!');
	<?php
		}
	?>
</script>