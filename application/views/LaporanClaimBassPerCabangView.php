<script>
    var CabangSelected = "<?php echo($CabangSelected)?>";
	
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

</script>

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
        echo form_open("Reportbass/proses_reportclaimsummary", array("target"=>"_blank"));
	?>
	<div class="form-container">
	
        <form method='POST'>
            <div class="row">
                <div class="col-3">Cabang</div>
                <div class="col-8 col-m-8 date">
                    <select  class="form-control" name="cabang" id="cabang" required>
                        <!-- <option value='ALL' selected>ALL</option> -->
                        <?php 

                            $jum= count($MS_BASS);
                            for($i=0; $i<$jum; $i++){						
                                echo "<option value='".$MS_BASS[$i]["KODE_BASS"]."'>".$MS_BASS[$i]["KODE_BASS"]." - ".$MS_BASS[$i]["NAMA_BASS"]."</option>";
                            }	

                        ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-3 col-m-3 row-label">Tanggal </div>
                <div class="col-3 col-m-3 date">
                    <input type="text" class="form-control" id="dp1" placeholder="dd-mm-yyyy" name="dp1" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" required>
                </div>
                <div class="col-1 col-m-1">SD</div>
                <div class="col-3 col-m-3 date">
                    <input type="text" class="form-control" id="dp2" placeholder="dd-mm-yyyy" name="dp2" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" required>
                </div>
            </div>

            <div class="row" align="center" style="padding-top:50px;">
                <input type="submit" name="btnPDF" value="PDF"/>
                <input type="submit" name="btnExcel" value="EXCEL"/>
            </div>
        </form>   
    </div>    
    <?php echo form_close(); ?>             
</div> 


