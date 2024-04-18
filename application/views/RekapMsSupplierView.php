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
</style>


<div class="container">
	<div class="page-title">REKAP SUPPLIER</div>
	<?php 
        echo form_open($formDest, array("target"=>"_blank"));
	?>
	<div class="form-container">
		<div class="row">
			<div class="col-3">
				Kategori
			</div>
			<div class="col-9 col-md-8">
				<input type="radio" name="kategori" id="kategori_1" value="local" checked> 	<label for="kategori_1">Local</label><br>
				<input type="radio" name="kategori" id="kategori_2" value="luar_negeri"> 	<label for="kategori_2">Luar Negeri</label><br>
				<input type="radio" name="kategori" id="kategori_3" value="">				<label for="kategori_3">Keduanya</label>
			</div>
		</div>
		<div class="row" id="div_cabang">
			<div class="col-3">Status</div>
			<div class="col-9 col-md-8">
				<select name="status" class="form-control">
					<option value='1'>Aktif</option>
					<option value='0'>Tidak Aktif</option>
			  		<option value=''>ALL</option>
				  
				</select>
			</div>
		</div>
		
		<div class="row" align="center" style="padding-top:50px;">
			<input type = "submit" name="submit" value="PDF"/>
			<input type = "submit" name="submit" value="EXCEL"/>
		</div>
	 </div>
	<?php echo form_close(); ?>
</div> <!-- /container -->

<script>
	 $(document).ready(function() {
		
	
	});
</script>