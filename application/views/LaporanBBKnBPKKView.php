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
	.datepicker{
		z-index: 100000;
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

		$("input[name='opsi']").click(function(){
			var value = $(this).val();
			if(value==1 || value==2){
				$("#no-rekening").css({"display":"none"});
			}
			else if(value==3){
				$("#no-rekening").css({"display":"block"});
				var noRekeningVal = $("select[name='no_rekening']").val();
				$("#no-rekening-value").val(noRekeningVal);
			}
		});
		$("select[name='no_rekening']").change(function(){
			var value = $(this).val();
			$("#no-rekening-value").val(value);
		})
		$("input[name='tipe_report']").click(function(){
			var value = $(this).val();

			$("#no_bbk_parent").css({"display":"none"});
			if(value=='bbk'){
				$("#no_bbk_parent").css({"display":"none"});
			}
			else if(value=='bkk'){
				$("#no_bbk_parent").css({"display":"block"});
				$("#getBbk_Bpkk").css({"display":"none"});
				$("#getBbk_Bkk").css({"display":"block"});
			}
			else if(value=='bpkk'){
				$("#no_bbk_parent").css({"display":"block"});
				$("#getBbk_Bpkk").css({"display":"block"});
				$("#getBbk_Bkk").css({"display":"none"});
			}
		});
	} );
</script>

<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
	<?php 
        echo form_open($formUrl, array("target"=>"_blank"));
	?>
	<div class="form-container">
		<div class="row">
           	<div class="col-3 col-m-3 row-label">Tipe Report</div>
        	<div class="row-input">
              	<input type="radio" name="tipe_report" value="bbk" checked="checked">
              	<label>BBK</label>
              	<input type="radio" name="tipe_report" value="bkk" >
              	<label>BKK</label>				
				<input type="radio" name="tipe_report" value="bpkk">
              	<label>BPKK</label>

              	
			</div>           
        </div>
		<div class="row">
			<div class="col-3 col-m-3 row-label">Tanggal Dari</div>
			<div class="col-3 col-m-3 date">
				<input type="text" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1" value="<?php echo date('m/d/Y'); ?>" autocomplete="off" required>
			</div>
			<div class="col-1 col-m-1">SD</div>
			<div class="col-3 col-m-3 date">
				<input type="text" class="form-control" id="dp2" placeholder="mm/dd/yyyy" name="dp2" value="<?php echo date('m/d/Y'); ?>" autocomplete="off" required>
			</div>
		</div>
		
		<div class="row" id="no_bbk_parent" style="display:none;">
			<div class="col-3 col-m-3 row-label">Kode No BBK</div>
			<div class="row-input">
				<select name="no_bbk_bpkk" id="getBbk_Bpkk" style="display:none;">
					<?php
					echo '<option value="ALL">ALL</option>';
					if($getBbk_Bpkk!=''){
						foreach($getBbk_Bpkk as $value){
							echo '<option value="'.$value['Nomor_Bukti'].'">'.$value['Nomor_Bukti'].'</option>';
						}
					}
					
					?>
					
				</select>
				<select name="no_bbk_bkk" id="getBbk_Bkk" style="display:none;">
					<?php
					echo '<option value="ALL">ALL</option>';
					if($getBbk_Bkk!=''){
						foreach($getBbk_Bkk as $value){
							echo '<option value="'.$value['Nomor_Bukti'].'">'.$value['Nomor_Bukti'].'</option>';
						}
					}
					
					?>
					
				</select>
			</div>
		</div>

		<div class="row">
			<div class="col-3 col-m-3 row-label">Kode Supplier</div>
			<div class="row-input">
				<select name="kd_supplier">
					<?php
					if($supplier!=null){
						echo '<option value="ALL">ALL</option>';
						foreach($supplier as $value){
							echo '<option value="'.$value['Kode_Supplier'].'">'.$value['Nama_Supplier'].'</option>';
						}
					}
					
					?>
					
				</select>
			</div>
		</div>

		<!-- <div class="row">
			<div class="col-3 col-m-3 row-label">Kode Wilayah</div>
			<div class="row-input">
				<select name="kd_wilayah">
					<?php
					// if($wilayah!=null){
					// 	echo '<option value="ALL">ALL</option>';
					// 	foreach($wilayah as $value){
					// 		echo '<option value="'.$value['Kd_Wil'].'">'.$value['wilayah'].' ('.$value['Kd_Wil'].')'.'</option>';
					// 	}
					// }
					
					?>
					
				</select>
			</div>
		</div> -->

		<div class="row">
           	<div class="col-3 col-m-3 row-label">Opsi</div>
        	<div class="row-input">
              	<input type="radio" name="opsi" value="1" checked="checked">
              	<label>Gabungan</label>
				
				<input type="radio" name="opsi" value="2">
              	<label>Grup No Rekening</label>

              	<input type="radio" name="opsi" value="3" >
              	<label>No Rekening</label>
			</div>           
        </div>

		<div class="row" id="no-rekening" style="display: none;">
           	<div class="col-3 col-m-3 row-label">No Rekening</div>
        	<div class="row-input">
        		<select name="no_rekening">
					<?php
					if($rekening!=null){
						foreach($rekening as $value){
							echo '<option value="'.$value['value'].'">'.$value['text'].'</option>';
						}
					}
					
					?>
					
				</select>
				<input type="text"  id="no-rekening-value" disabled>
        	</div>
        </div>

        <div class="row" align="center" style="padding-top:50px;">
			<input type="submit" name="submit" value="EXPORT PDF"/>
			<input type="submit" name="submit" value="EXPORT EXCEL"/>
			<!-- <input type="submit" name="submit" value="EXCEL"/> -->
		</div>
	</div>
	<?php echo form_close(); ?>
</div> <!-- /container -->