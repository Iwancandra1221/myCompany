<style type="text/css">
	.row {
		line-height: 30px;
		vertical-align: middle;
		clear: both;
	}

	.row-label,
	.row-input {
		float: left;
	}

	.row-label {
		padding-left: 15px;
		width: 180px;
	}

	.row-input {
		width: 420px;
	}
</style>

<script>
	$(document).ready(function() {
		$('#dr1').datepicker({
			format: "dd/mm/yyyy",
			autoclose: true
		});
		$('#dr2').datepicker({
			format: "dd/mm/yyyy",
			autoclose: true
		});
		$('#db1').datepicker({
			format: "dd/mm/yyyy",
			autoclose: true
		});
		$('#db2').datepicker({
			format: "dd/mm/yyyy",
			autoclose: true
		});
		$('#in_nr').prop('checked', false);
	});
</script>
<div style="display:<?php if (!$this->session->flashdata('error')) echo 'none'; ?>; position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;" id="login-alert" class="alert alert-danger col-sm-12">
	<!-- error msg here -->
	<?php
	echo $this->session->flashdata('error');
	if (isset($_SESSION['error'])) {
		unset($_SESSION['error']);
	}
	?>
</div>
<div style="display:<?php if (!$this->session->flashdata('info')) echo 'none'; ?>; position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;" id="info-alert" class="alert alert-success col-sm-12">
	<?php
	echo $this->session->flashdata('info');
	if (isset($_SESSION['info'])) {
		unset($_SESSION['info']);
	}
	?>
</div>
<div class="container">
	<div class="page-title"><?php echo (strtoupper($title)); ?></div>
	<?php
	echo form_open("LaporanBarangDalamPerjalanan/Proses", array("target" => "_blank"));
	?>
	<div class="form-container">
		<div class="row">
			<div class="col-3 col-m-3">TANGGAL NR</div>
			<div class="col-3 col-m-3">
				<input type="text" class="form-control" id="dr1" placeholder="dd/mm/yyyy" name="dr1" value="<?php echo date('d/m/Y'); ?>" autocomplete="off" required>
			</div>
			<div class="col-1 col-m-1">SD</div>
			<div class="col-3 col-m-3">
				<input type="text" class="form-control" id="dr2" placeholder="dd/mm/yyyy" name="dr2" value="<?php echo date('d/m/Y'); ?>" autocomplete="off" required>
			</div>
		</div>
		<div class="row">
			<div class="col-3 col-m-3">TANGGAL BPB</div>
			<div class="col-3 col-m-3">
				<input type="text" class="form-control" id="db1" placeholder="dd/mm/yyyy" name="db1" value="<?php echo date('d/m/Y'); ?>" autocomplete="off" required>
			</div>
			<div class="col-1 col-m-1">SD</div>
			<div class="col-3 col-m-3">
				<input type="text" class="form-control" id="db2" placeholder="dd/mm/yyyy" name="db2" value="<?php echo date('d/m/Y'); ?>" autocomplete="off" required>
			</div>
		</div>
		<div class="row">
			<div class="col-3"></div>
			<div class="col-9 col-m-8">
				<input type="checkbox" name="in_nr" id="in_nr" class="chk_exclude" value="0"><label class="chk_exclude" for="in_nr">INCLUDE NOTA RETUR TANPA BPB</label>
			</div>
		</div>
		<div class="row" align="center" style="padding-top:50px;">
			<!--<input type="submit" name="submit" value="PDF" />-->
			<input type="submit" name="submit" value="EXCEL" />
		</div>
	</div>
	<?php echo form_close(); ?>
</div> <!-- /container -->