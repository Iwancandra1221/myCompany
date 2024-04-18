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
        echo form_open("ReportPenerimaan/ReportPenerimaan_Proses", array("target"=>"_blank"));
	?>
	<div class="form-container">
		
        <div class="row">
			<div class="col-3 col-m-3 row-label">Periode</div>
			<div class="col-2 col-m-3 ">
				<input type="text" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1" value="<?php echo date('m-d-Y'); ?>" autocomplete="off" required>
			</div>
			<div class="col-1 col-m-1">-- S/D --</div>
			<div class="col-2 col-m-3 ">
				<input type="text" class="form-control" id="dp2" placeholder="mm/dd/yyyy" name="dp2" value="<?php echo date('m-d-Y'); ?>" autocomplete="off" required>
			</div>
		</div>

        <div class="row">
			<div class="col-3">Tipe Terima</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="typeterima" id="typeterima" >
                    <option value="ALL" selected>ALL</option>		
					<?php 
						$jum= count($listtypeterima->data);
						for($i=0; $i<$jum; $i++){						
							echo "<option value='".$listtypeterima->data[$i]->TYPE_TERIMA."'
                                >".$listtypeterima->data[$i]->TYPE_TERIMA."</option>";
						}			  
					?>
				</select>
			</div>
		</div>

        <div class="row">
			<div class="col-3">Bank</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="bank" id="bank" >
                    <option value="ALL" selected>ALL</option>		
					<?php 
						$jum= count($listbank->data);
						for($i=0; $i<$jum; $i++){						
							echo "<option value='".$listbank->data[$i]->BANK."'
                                >".$listbank->data[$i]->BANK."</option>";
						}			  
					?>
				</select>
			</div>
		</div>

        <div class="row">
			<div class="col-3">Wilayah</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="wilayah" id="wilayah" onchange="fwilayah()">
                    <option value="ALL" selected>ALL</option>		
					<?php 
						$jum= count($listwilayah->data);
						for($i=0; $i<$jum; $i++){						
							echo "<option value='".$listwilayah->data[$i]->WILAYAH."'
                                >".$listwilayah->data[$i]->WILAYAH."</option>";
						}			  
					?>
				</select>
			</div>
		</div>
	
        <div class="row">
			<div class="col-3 col-m-3 row-label"></div>
			<div class="col-8 col-m-3 ">
				<textarea id="wil" name="wil" class="form-control" rows="2" readonly value=""></textarea>
				<input type="hidden" id="wil_list" name="wil_list">
			</div>			
		</div>

		<div class="row">
			<div class="col-3">Status</div>
			<div class="col-8" style="margin-top:-10px " >
				<div class="col-12">
					<input type="radio" name="radstatus" id="r1" value="semua" checked> <label>Semua Penerimaan</label> 
					<br>
					<input type="radio" name="radstatus" id="r2" value="gantung"> <label>Penerimaan Gantung (Belum ada BBT)</label>
					<br>
					<input type="radio" name="radstatus" id="r3" value="bbt"> <label>Penerimaan yang sudah dijadikan BBT</label>
					<br>					
				</div>
			</div>
		</div>
		
		<div class="row">
		<div class="col-3"> </div>
			<div class="col-8">
				<input type="checkbox" id="cetak_detail" name="cetak_detail" value="Y" >
				<label for="cetak_detail">Tampilkan Detail Faktur</label>
			</div>	
		</div>	

        <div class="row" align="center" style="padding-top:50px;">
			<input type="submit" name="btnPreview" id="btnPreview" value="PREVIEW" />
			<input type="submit" name="btnExcel" id="btnExcel" value="EXCEL"/>
		</div>
	</div>

	<?php echo form_close(); ?>
</div>


<script>
    $(document).ready(function() {

		$('#dp1').datepicker({
			format: "mm-dd-yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var StartDt = $('#dp1').datepicker('getDate');
			$('#dp2').datepicker("setStartDate", StartDt);
		});
		
		$('#dp2').datepicker({
			format: "mm-dd-yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var EndDt = $('#dp2').datepicker('getDate');
			$('#dp1').datepicker("setEndDate", EndDt);
		});
	} );


	function fwilayah() {
		var cbo = document.getElementById("wilayah");
		var txt = document.getElementById("wil");
		var txt_value = document.getElementById("wil_list");

		if (txt_value.value.indexOf("'" + cbo.value + "'") == -1) {
		if (cbo.value == "ALL") {
			txt.value = "";
			txt_value.value = "";
		} else {
			if (txt.value == "") {
				txt.value = cbo.value;
				txt_value.value = "'" + cbo.value + "'";
				
			} else {
				txt.value += ", " + cbo.value;
				txt_value.value += ", '" + cbo.value + "'";
			}
		}
		}
	}

</script>



