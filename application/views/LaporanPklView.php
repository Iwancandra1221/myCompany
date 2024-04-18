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


<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
	<?php 
		$jum = 0;
        echo form_open("LaporanPkl/LaporanPkl_Proses", array("target"=>"_blank"));
	?>
	<div class="form-container">
		
        <div class="row">
			<div class="col-3 col-m-3 row-label">Periode</div>
			<div class="col-2 col-m-3 ">
				<input type="text" class="form-control" id="dp1" placeholder="dd/mm/yyyy" name="dp1" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" required>
			</div>
			<div class="col-1 col-m-1">-- S/D --</div>
			<div class="col-2 col-m-3 ">
				<input type="text" class="form-control" id="dp2" placeholder="dd/mm/yyyy" name="dp2" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" required>
			</div>
		</div>
		<div class="row">
			<div class="col-3">Dealer</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="kd_plg" id="kd_plg" >
                    <?php
                    if($dealer!=null){
                    	foreach($dealer as $key => $value){
                    		echo '<option value="'.$value['Kd_Plg'].'">'.$value['Kd_Plg'].' - '.$value['Nm_Plg'].'</option>';
                    	}
                    }
            
                    ?>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="col-3"></div>
			<div class="col-8" style="margin-top:-10px " >
				<div class="col-12">
					<input type="checkbox" name="sort_by_no_sj" id="r1" value="1" onclick="frekening('gabungan')"> <label> Sort By No SJ</label> 
					<br>
				</div>
			</div>
		</div>
				

        <div class="row" align="center" style="padding-top:50px;">
			<input type="submit" name="submit" id="btnPreview" value="PREVIEW" />
			<input type="submit" name="submit" id="btnExcel" value="EXCEL"/>
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
	} );


	function frekening(rek) {
        var x = document.getElementById("rekening");

		if (rek == "norekening"){
			x.disabled = false ;
		}  
		else {
			x.disabled = true;
		}
		
		return false;      
    }   

</script>



