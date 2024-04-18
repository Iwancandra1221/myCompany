<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>
</style>

<div class="container">
	<div class="form_title" style="text-align: center;">  
			LAPORAN SISA FAKTUR PER JATUH TEMPO 
	</div>
	<?php echo form_open('ReportFinance/PreviewSisaFaktur',array('id' => 'myform','target'=>'_blank')); ?>
	<div class="form-container border20 p20 mb20">
		
		<div class="row">
			<div class="col-3">Report</div>
			<div class="col-8">
				<select name="laporan" id="laporan" class="form-control form-control-dark" onchange="javascript:chooseReport()" required>
					<?php
						foreach($report as $key => $rpt){
							echo "<option value='".$key."'>".$rpt."</option>";
						}
					?>
				</select>
			</div>
		</div>
		
		<div class="row">
			<div class="col-3">Cabang</div>
			<div class="col-8">
				<select name="cabang" class="form-control form-control-dark" required>
					<option value="ALL#ALL">ALL</option>
					<?php
						foreach($cabang as $c){
							echo "<option value='".$c->Kd_Lokasi."#".$c->Nm_Lokasi."'>".$c->Nm_Lokasi." - ".$c->Kd_Lokasi."</option>";
						}
					?>
				</select>
			</div>
		</div>
		
		<div class="row">
			<div class="col-3">Partner Type</div>
			<div class="col-8">
				<?php
					foreach($partner_type as $c){
						echo "<input type='checkbox' name='partner_type[]' value='".$c->partner_type."' checked> ".$c->partner_type."<br>";
					}
				?>
			</div>
		</div>
		
		<div class="row">
			<div class="col-3">Per Tanggal</div>
			<div class="col-3">
				<input type="text" name="tanggal" value="<?php echo date('d-M-Y') ?>" class="form-control form-control-dark datepicker" autocomplete="off" readonly required>
			</div>
		</div>
		
		<div class="row report_aging">
			<div class="col-3">Range Umur Faktur (Hari)</div>
			<div class="col-3">
				<select name="range" class="form-control form-control-dark" required>
					<option value="30">30</option>
					<option value="45">45</option>
					<option value="60">60</option>
					<option value="90">90</option>
				</select>
			</div>
		</div>
		
		<div class="row">
			<div class="col-3"></div>
			<div class="col-8"><input type="submit" name="btnPreview" value="Preview" class="btn" > <input type="submit" name="btnExcel" value="Excel" class="btn"></div>
		</div>
	</div>
	<?php echo form_close(); ?>
</div>

<script>
	$(document).ready(function() {
		$("#myform").submit(function(e){
			var chk = $('input:checkbox:checked').length;
			if(chk==0){
				alert('Partner Type wajib dipilih!');
				e.preventDefault();
			}
		});
		chooseReport();
	});
	
	function chooseReport(){
		var rpt = $('#laporan').val();
		if(rpt=='5' || rpt=='6' || rpt=='7'){
			$('.report_aging').show();
		}
		else{
			$('.report_aging').hide();
		}
	}
</script>

