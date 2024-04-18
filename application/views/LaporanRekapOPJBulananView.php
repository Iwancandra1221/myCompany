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
        echo form_open("Reportopj/Report_6N2_OPJ", array("target"=>"_blank"));
	?>
	<div class="form-container">
	
        <form method='POST'>

            <div class="row">
                <div class="col-3">Bulan</div>
                <div class="col-8 col-m-8 date">
                    <select class="form-control" name="bulan" id="bulan">
                        <option value="1" <?php echo((date("m")=="01")?"selected":"")?>>JANUARI</option>
                        <option value="2" <?php echo((date("m")=="02")?"selected":"")?>>FEBRUARI</option>
                        <option value="3" <?php echo((date("m")=="03")?"selected":"")?>>MARET</option>
                        <option value="4" <?php echo((date("m")=="04")?"selected":"")?>>APRIL</option>
                        <option value="5" <?php echo((date("m")=="05")?"selected":"")?>>MEI</option>
                        <option value="6" <?php echo((date("m")=="06")?"selected":"")?>>JUNI</option>
                        <option value="7" <?php echo((date("m")=="07")?"selected":"")?>>JULI</option>
                        <option value="8" <?php echo((date("m")=="08")?"selected":"")?>>AGUSTUS</option>
                        <option value="9" <?php echo((date("m")=="09")?"selected":"")?>>SEPTEMBER</option>
                        <option value="10" <?php echo((date("m")=="10")?"selected":"")?>>OKTOBER</option>
                        <option value="11" <?php echo((date("m")=="11")?"selected":"")?>>NOVEMBER</option>
                        <option value="12" <?php echo((date("m")=="12")?"selected":"")?>>DESEMBER</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-3">Tahun</div>
                <div class="col-8 col-m-8 date">
                    <input class="form-control" type="text" name="tahun" id="tahun" value="<?php echo(date('Y'));?>">
                </div>
            </div>

            <div class="row">
                <div class="col-3">Wilayah</div>
                <div class="col-8 col-m-8 date">
                    <select class="form-control" name="wilayah" id="wilayah" required>
                        <option value='ALL' selected>ALL</option> 
                        <?php 
                            $jum= count($wilayah);
                            for($i=0; $i<$jum; $i++){		
                                echo "<option value='".$wilayah[$i]->WILAYAH."'>".$wilayah[$i]->WILAYAH."</option>";			
                            }	
                        ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-3">Salesman</div>
                <div class="col-8 col-m-8 date">
                    <select  class="form-control" name="salesman" id="salesman" required>
                        <option value='ALL' selected>ALL</option> 
                        <?php 
                            $jum= count($salesman);
                            for($i=0; $i<$jum; $i++){		
                                echo "<option value='".$salesman[$i]->KD_SLSMAN."'>".$salesman[$i]->KD_SLSMAN." - ".$salesman[$i]->NM_SLSMAN."</option>";			
                            }	
                        ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-3">Merk</div>
                <div class="col-8 col-m-8 date">
                    <select  class="form-control" name="merk" id="merk" required>
                        <option value='ALL' selected>ALL</option> 
                        <?php 
                            $jum= count($merk);
                            for($i=0; $i<$jum; $i++){		
                                echo "<option value='".$merk[$i]->Merk."'>".$merk[$i]->Merk."</option>";			
                            }	
                        ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-3">Divisi</div>
                <div class="col-8 col-m-8 date">
                    <select  class="form-control" name="divisi" id="divisi" required>
                        <?php 
                            $jum= count($divisi);
                            for($i=0; $i<$jum; $i++){			
                                echo "<option value='".$divisi[$i]->Kd_Divisi."'>".$divisi[$i]->Kd_Divisi."</option>";			
                            }	
                        ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-3">Partner Type</div>
                <div class="col-8 col-m-8 date">
                    <select  class="form-control" name="partnertype" id="partnertype" required>
                        <option value='ALL' selected>ALL</option> 
                        <?php 
                            $jum= count($partnertype);
                            for($i=0; $i<$jum; $i++){		
                                echo "<option value='".$partnertype[$i]."'>".$partnertype[$i]."</option>";			
                            }	
                        ?>
                    </select>
                </div>
            </div>

            <div class="row" align="center" style="padding-top:50px;">
                <input type="submit" name="btnPDF" value="PDF"/>
                <input type="submit" name="btnHTML" value="PREVIEW"/>
            </div>
        </form>   
    </div>    
    <?php echo form_close(); ?>             
</div> 


