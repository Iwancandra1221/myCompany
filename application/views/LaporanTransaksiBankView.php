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
			format: "yyyy-mm-dd",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var StartDt = $('#dp1').datepicker('getDate');
			$('#dp2').datepicker("setStartDate", StartDt);
		});
		
		$('#dp2').datepicker({
			format: "yyyy-mm-dd",
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
        echo form_open($formUrl, array("target"=>"_blank"));
	?>
	<div class="form-container">
		<div class="row">
			<div class="col-3 col-m-3 row-label">Tanggal Dari</div>
			<div class="col-3 col-m-3 date">
				<input type="text" class="form-control" id="dp1" placeholder="yyyy-mm-dd" name="dp1" value="<?php echo date('Y-m-d'); ?>" autocomplete="off" onchange="ceksaldoawal()" required>
			</div>
			<div class="col-1 col-m-1">SD</div>
			<div class="col-3 col-m-3 date">
				<input type="text" class="form-control" id="dp2" placeholder="yyyy-mm-dd" name="dp2" value="<?php echo date('Y-m-d'); ?>" autocomplete="off" onchange="ceksaldoawal()" required>
			</div>
		</div>
		<div class="row" id="no-rekening">
           	<div class="col-3 col-m-3 row-label">No Rekening</div>
        	<div class="col-9 col-m-8">
        		<select name="no_rekening" id="no_rekening" class="form-control" width="100%" onchange="ceksaldoawal()">
					<?php
					if($rekening!=null){
						foreach($rekening as $value){
						echo '<option value="'.$value['value'].' | '.date('Ymd',strtotime($value['Tgl_Saldo_Awal'])).' | '.$value['Bank'].' | '.$value['Cabang'].' | '.$value['Nm_Pemilik'].'">'.$value['text'].' | '.$value['value'].'</option>';
						}
					}
					?>
					
				</select>
        	</div>
        </div>
		<div class="row" id="no-rekening">
           	<div class="col-3 col-m-3 row-label"></div>
        	<div class="col-9 col-m-8">
				<input type="submit" name="submit" value="EXPORT PDF" id="pdfid" />
				<input type="submit" name="submit" value="EXPORT EXCEL" id="excelid" />
        	</div>
        </div>
	</div>
	<?php echo form_close(); ?>
</div> <!-- /container -->

<script>
	function ceksaldoawal(){
		var tglawal = document.getElementById('dp1').value;
		tglawal=tglawal.replace("-", "");
		tglawal=tglawal.replace("-", "");
		var rek = document.getElementById('no_rekening').value;
		const arr = rek.split(" | ");
		var h = arr[1];
		
		if(tglawal<h){
			var tgl = h.substring(6, 8);
			var bln = h.substring(4, 6);
			var thn = h.substring(0, 4);

			alert("Minimal Tanggal awal laporan : "+tgl+'-'+bln+'-'+thn);
			document.getElementById('pdfid').disabled=true;
			document.getElementById('excelid').disabled=true;
		}else{
			document.getElementById('pdfid').disabled=false;
			document.getElementById('excelid').disabled=false;
		}
	}
</script>