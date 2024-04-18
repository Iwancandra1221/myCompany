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
<script>
    $(document).ready(function() {
		$('#dp1').datepicker({
			format: "dd/mm/yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var StartDt = $('#dp1').datepicker('getDate');
			$('#dp2').datepicker("setStartDate", StartDt);
		});
		
		$('#dp2').datepicker({
			format: "dd/mm/yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var EndDt = $('#dp2').datepicker('getDate');
			$('#dp1').datepicker("setEndDate", EndDt);
		});
	} );
</script>
<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
	<?php 
        echo form_open("LaporanPengeluaranHadiah/Proses", array("target"=>"_blank"));
	?>
	<div class="form-container">
		<div class="row">
			<div class="col-3 col-m-3">Periode</div>
			<div class="col-3 col-m-3 date">
				<input type="text" class="form-control" id="dp1" placeholder="dd/mm/yyyy" name="dp1" autocomplete="off" required>
			</div>
			<div class="col-1 col-m-1">SD</div>
			<div class="col-3 col-m-3 date">
				<input type="text" class="form-control" id="dp2" placeholder="dd/mm/yyyy" name="dp2" autocomplete="off" required>
			</div>
		</div>
		<div class="row">
			<div class="col-3"></div>
			<div class="col-8 col-m-8">
				<input type="radio" name="report" id="p1" value="P1" checked> <label for="p1">LAPORAN PENGELUARAN HADIAH SUMMARY</label>
			</div>
			<div class="col-3"></div>
			<div class="col-8 col-m-8">
				<input type="radio" name="report" id="p2" value="P2"> <label for="p2">LAPORAN PENGELUARAN HADIAH DETAIL</label>
			</div>
		</div>
		<div class="row">
			<div class="col-3"></div>
			<div class="col-8 col-m-8">
			<input type="submit" name="btnExcel" value="EXCEL"/>
			</div>
		</div>
	</div>
	<?php echo form_close(); ?>
</div> <!-- /container -->