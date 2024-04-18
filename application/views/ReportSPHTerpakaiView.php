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

	.row-input {
		width: 420px;
	}
</style>

<div class="container">
	<div class="page-title"><?php echo (strtoupper($title)); ?></div>
	<?php
	echo form_open("ReportSPHTerpakai/Proses", array("target" => "_blank"));
	?>
	<div class="form-container">
		<div class="row">
			<div class="col-3 col-m-3 row-label">Periode</div>
			<div class="col-3 col-m-3 ">
				<input type="text" class="form-control" id="dp1" placeholder="dd/mm/yyyy" name="dp1" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" required>
			</div>
			<div class="col-1 col-m-1">SD</div>
			<div class="col-3 col-m-3 ">
				<input type="text" class="form-control" id="dp2" placeholder="dd/mm/yyyy" name="dp2" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" required>
			</div>
		</div>
		<div class="row">
			<div class="col-3">Wilayah</div>
			<div class="col-8 col-m-8 ">
				<select class="form-control" name="wilayah" id="wilayah">
					<option value='ALL' selected>ALL</option>
					<?php
					foreach ($wilayah as $s) {
						echo ("<option value='" . $s->wilayah . "'>" . $s->wilayah . "</option>");
					}
					?>
				</select>
			</div>
		</div>

		<div class="row">
			<div class="col-3">Toko</div>
			<div class="col-8 col-m-8 ">
				<select class="form-control" name="toko" id="toko">
					<option value='ALL' selected>ALL</option>
				</select>
			</div>
		</div>

		<div class="row">
			<div class="col-3 col-m-3 row-label">Cari Nama Toko</div>
			<div class="col-3 col-m-3 ">
				<input type="text" class="form-control" id="name_toko" name="name_toko" value="">
			</div>
		</div>

		<div class="row">
			<div class="col-3"></div>
			<div class="col-8">
				<div class="col-4">
					<input type="radio" name="sph" id="sph" value="N" checked> <label for="p1">Semua SPH</label>
				</div>
				<div class="col-4 ">
					<input type="radio" name="sph" id="sph" value="Y"> <label for="p2">Hanya SPH Gantung</label>
				</div>
			</div>
		</div>
		<div class="row" align="center" style="padding-top:50px;">
			<input type="submit" name="btnPDF" name="btnPDF" value="PDF" />
			<input type="submit" name="btnExcel" name="btnExcel" value="EXCEL" />
		</div>
	</div>

	<?php echo form_close(); ?>
</div>


<script>
	$(document).ready(function() {

		$('#dp1').datepicker({
			format: "dd-mm-yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var StartDt = $('#dp1').datepicker('getDate');
			$('#dp2').datepicker("setStartDate", StartDt);
		});

		$('#dp2').datepicker({
			format: "dd-mm-yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var EndDt = $('#dp2').datepicker('getDate');
			$('#dp1').datepicker("setEndDate", EndDt);
		});

		$('#wilayah').on('change', function() {
			getToko();
		});

		$('#name_toko').on('input', function() {
			searchNamaToko();
		});
	});

	function getToko() {
		let wilayah = document.getElementById("wilayah").value;
		let data = '&wilayah=' + wilayah;
		$.ajax({
			type: "POST",
			url: '<?php echo site_url("ReportSPHTerpakai/GetToko"); ?>',
			data: data,
			success: function(data) {
				jsonObject = JSON.parse(data);
				let toko = '<option value="ALL">ALL</option>';
				for (i = 0; i < jsonObject.toko.length; i++) {
					toko += '<option value="' + jsonObject.toko[i].nm_plg + '">' + jsonObject.toko[i].nm_plg + '</option>';
					$("#toko").html(toko);
				}
			}
		});
		return false;
	};

	function searchNamaToko() {
		let namaToko = $('#name_toko').val();
		let wilayah = document.getElementById("wilayah").value;
		let data = {
			wilayah: wilayah,
			nama_toko: namaToko
		};
		$.ajax({
			type: "POST",
			url: '<?php echo site_url("ReportSPHTerpakai/searchNamaToko"); ?>',
			data: data,
			success: function(data) {
				jsonObject = JSON.parse(data);
				let tokoOptions = '';
				for (i = 0; i < jsonObject.toko.length; i++) {
					tokoOptions += '<option value="' + jsonObject.toko[i].nm_plg + '">' + jsonObject.toko[i].nm_plg + '</option>';
				}
				$("#toko").html(tokoOptions);
			}
		});
	}
</script>