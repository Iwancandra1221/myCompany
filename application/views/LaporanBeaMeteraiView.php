<style type="text/css">
	.row {
    line-height:30px; 
    vertical-align:middle;
    clear:both;
	}
	.row-label, .row-input {
    float:left;
	}
	/* .row-label {
    padding-left: 15px;
    width:180px;
	} */
	.row-input {
    width:420px;
	}
</style>

<script>
    $(document).ready(function() {
		$('#dp1').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var StartDt = $('#dp1').datepicker('getDate');
			$('#dp2').datepicker("setStartDate", StartDt);
		});
		
		$('#dp2').datepicker({
			format: "mm/dd/yyyy",
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
        echo form_open("LaporanBeaMeterai/Proses", array("target"=>"_blank"));
	?>
	<div class="form-container">
		
		<div class="row">
			<div class="col-3 col-m-3 row-label">Tanggal Dokumen</div>
			<div class="col-3 col-m-3 date">
				<input type="text" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1" value="<?php echo date('m-d-Y'); ?>" autocomplete="off" required>
			</div>
			<div class="col-1 col-m-1">SD</div>
			<div class="col-3 col-m-3 date">
				<input type="text" class="form-control" id="dp2" placeholder="mm/dd/yyyy" name="dp2" value="<?php echo date('m-d-Y'); ?>" autocomplete="off" required>
			</div>
		</div>
				
		<div class="row">
           	<div class="col-3 col-m-3 row-label">Opsi</div>
        	<div class="row-input">
              	<input type="checkbox" id="cbox1" name="cbox1" value="Y" checked="checked">
              	<label for="cbox1">Meterai = 0</label>
			</div>
			<div class="row-input">
				<input type="checkbox" id="cbox2" name="cbox2" value="Y" checked="checked">
              	<label for="cbox2">Meterai > 0</label>
			</div>
           
        </div>

        <div class="row" align="center" style="padding-top:50px;">
			<!-- <input type="submit" name="btnPreview" value="PREVIEW" style="display:none;"/> -->
			<input type="submit" name="btnExcel" value="EXCEL"/>
		</div>
	</div>
	<?php echo form_close(); ?>
</div> <!-- /container -->