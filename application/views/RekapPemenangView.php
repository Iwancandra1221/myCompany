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
	#no-rekening-value{
		color:black;
	}
</style>

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
	});
</script>
<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
	<?php 
        echo form_open($formDest, array("target"=>"_blank"));
	?>
	<div class="form-container">
		<div class="row">
			<div class="col-3 col-m-3 row-label">Periode</div>
			<div class="col-3 col-m-3 date">
				<input type="text" class="form-control" id="dp1" placeholder="yyyy-mm-dd" name="dp1" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" required>
			</div>
			<div class="col-1 col-m-1">SD</div>
			<div class="col-3 col-m-3 date">
				<input type="text" class="form-control" id="dp2" placeholder="yyyy-mm-dd" name="dp2" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" required>
			</div>
		</div>
		<div class="row">
           	<div class="col-3 col-m-3 row-label">Wilayah</div>
        	<div class="col-9 col-m-8">
        		<select name="wilayah" class="form-control" width="100%">
					<?php
					foreach($wilayah as $row){
						echo '<option value="'.$row['wilayah'].'">'.$row['wilayah'].'</option>';
					}
					?>
				</select>
        	</div>
        </div>
		<div class="row" id="no-rekening">
           	<div class="col-3 col-m-3 row-label"></div>
        	<div class="col-9 col-m-8">
				<input type="submit" name="submit" value="EXPORT EXCEL">
        	</div>
        </div>
	</div>
	<?php echo form_close(); ?>
</div> <!-- /container -->
