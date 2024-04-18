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
        echo form_open("Reportfakturlisting/Proses", array("target"=>"_blank"));
	?>
	<div class="form-container">
		
        <div class="row">
			<div class="col-3">Pilih Database Gudang</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="dbgudang" id="dbgudang" ">
					<?php 
						$jum= count($dbgudang->data);
						for($i=0; $i<$jum; $i++){						
							echo "<option value='".$dbgudang->data[$i]->Kd_Gudang."'
                                >".$dbgudang->data[$i]->Kd_Gudang." --- "
                                .$dbgudang->data[$i]->Server." --- "
                                .$dbgudang->data[$i]->DB."</option>";
						}			  
					?>
				</select>
			</div>
		</div>

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
			<div class="col-3">Merk</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="merk" id="merk">
					<option value='ALL' selected>ALL</option>	
                    
                    <?php
						$jum = count($merks->data);
						for($i=0; $i<$jum; $i++){						
							echo "<option value='".$merks->data[$i]->MERK."'
                                >".$merks->data[$i]->MERK."</option>";
						}			  
					?>
				</select>
			</div>
		</div>

		<div class="row">
			<div class="col-3">Dealer</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="dealer" id="dealer" >
					<option value='ALL --- ALL' selected>ALL</option>
                    
                    <?php 
						$jum = count($dealers->data);
						for($i=0; $i<$jum; $i++){						
							echo "<option value='".$dealers->data[$i]->KD_PLG." --- ".$dealers->data[$i]->NM_PLG."'
                                >".$dealers->data[$i]->NM_PLG." --- ".$dealers->data[$i]->KD_PLG."</option>";
						}			  
					?>
				</select>
			</div>
		</div>


		<div class="row">
			<div class="col-3">Pilihan Laporan</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="pilihanlaporan" id="pilihanlaporan" onclick="fPilihanLaporan()" >
					<option value='A' selected>A. Laporan Faktur yang Sudah DiListing</option>
					<option value='B' >B. Laporan Faktur yang Sudah Dipotong PDA</option>					
				</select>
			</div>
		</div>
			

        <div class="row" align="center" style="padding-top:50px;">
			<input type="submit" name="btnPreview" id="btnPreview" value="PREVIEW" />
			<!-- <input type="submit" name="btnExcel" id="btnExcel" value="EXCEL"/> -->
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




</script>
