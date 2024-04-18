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
</style>

<script>
	 $(document).ready(function() {
		$('#tgl').datepicker({
			format: "M/dd/yyyy",
			autoclose: true
		});
	});
</script>
<div class="">
  <?php echo form_open($formDest, array("target"=>"_blank")) ?>
	 <div class="form-container" style="height:500px!important;">
		<div class="row">
			<div class="col-3">Tipe Laporan</div>
			<div class="col-3">
				<select name="tipe_laporan" class="form-control">
				  <option value='harian'>Harian</option>
				  <option value='bulanan'>Bulanan</option>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="col-3">Tanggal</div>
			<div class="col-3"><input type="text" name="tgl" id="tgl" value="<?=date('M/d/Y')?>"></div>
		</div>
		<div class="row">
			<div class="col-3">Cabang</div>
			<div class="col-9 col-md-8">
				<select name="cabang" class="form-control">
				  <option value='ALL'>ALL</option>
				  <?php 
				  foreach($listCabang as $value){
				  	echo("<option value='".$value["Kd_Lokasi"]."'>".$value["Kd_Lokasi"]." - ".$value["Nm_Lokasi"]."</option>");
				  }
				  ?>
				</select>
			</div>
		</div>
		
		<div class="row" align="center" style="padding-top:50px;">
			<input type = "submit" name="submit" value="PREVIEW"/>
			 <input type = "submit" name="submit" value="EXCEL"/>
		</div>
	 </div>
	<?php echo form_close(); ?>
</div> <!-- /container -->

<script type="text/javascript">
	function showDP(){
		$('#tgl').datepicker('show');
	} 
</script>