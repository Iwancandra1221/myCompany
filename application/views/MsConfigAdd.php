<style>
</style>

<div class="container">
	<div class="form_title"><div style="text-align:center;">CREATE CONFIG</div></div>
	<br>
	<?php echo form_open('MsConfig/Insert'); ?>
	<div class="row">
		<div class="col-3 col-m-4">Config Type</div>
		<div class="col-4 col-m-4">
			<select name="ConfigType" class="form-control" id="ConfigType" required>
				<option value="">Pilih Config Type</option>
				<?php foreach($ConfigType as $type) { 
					echo("<option value='".$type->ConfigType."'>".$type->ConfigType."</option>");
				}?>
				<option value="OTHER">OTHER</option>
			</select>
		</div>
        <div class="col-5 col-m-4">
			<input type="text" class="form-control" name="ConfigType_Other" id="ConfigType_Other" placeholder="OTHER" disabled>
		</div>
	</div>
		
	<div class="row">
        <div class="col-3 col-m-4">Config Name</div>
        <div class="col-4 col-m-4">
			<select name="ConfigName" class="form-control" id="ConfigName" required>
				<option value="">Pilih Config Name</option>
				<?php foreach($ConfigName as $name) { 
					echo("<option value='".$name->ConfigName."'>".$name->ConfigName."</option>");
				}?>
				<option value="OTHER">OTHER</option>
			</select>
		</div>	
        <div class="col-5 col-m-4">
			<input type="text" class="form-control" name="ConfigName_Other" id="ConfigName_Other" placeholder="OTHER" disabled>
		</div>
	</div>
	<div class="row">
        <div class="col-3 col-m-4">Config Value</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="ConfigValue" id="ConfigValue" placeholder="Config Value" required>
		</div>
	</div>
	
	<div class="row">
        <div class="col-3 col-m-4">Group</div>
        <div class="col-4 col-m-4">
			<select name="Group" class="form-control" id="Group" required>
				<option value="">Pilih Group</option>
				<?php foreach($Group as $g) { 
					echo("<option value='".$g->Group."'".(($g->Group=="ALL")?" selected":"").">".$g->Group."</option>");
				}?>
				<option value="OTHER">OTHER</option>
			</select>
		</div>	
        <div class="col-5 col-m-4">
			<input type="text" class="form-control" name="Group_Other" id="Group_Other" placeholder="OTHER" disabled>
		</div>
	</div>
	
	<div class="row">
        <div class="col-3 col-m-4"></div>
        <div class="col-9 col-m-8">
			<input type="checkbox" name="Add" id="Add"> Info Tambahan
		</div>
	</div>
	
	<div class="row">
        <div class="col-3 col-m-4"></div>
        <div class="col-4 col-m-4">
			<input type="text" class="form-control AddInfo" name="AddInfoParam" id="AddInfoParam" placeholder="Nama (tanpa spasi)" onkeydown="return (event.which >= 48 && event.which <= 57) ||(/[a-z]/i.test(event.key)) || event.which == 8 || event.which == 46" disabled>
		</div>	
        <div class="col-5 col-m-4">
			<input type="text" class="form-control AddInfo" name="AddInfo" id="AddInfo" placeholder="Keterangan" disabled>
		</div>
	</div>
	
	
	<div class="row">
        <div class="col-3 col-m-4"></div>
        <div class="col-9 col-m-8">
			<input type="checkbox" name="IsActive" id="IsActive" value="1" checked> Aktif
		</div>
	</div>
	<div class="row">
        <div class="col-3 col-m-4"></div>
        <div class="col-9 col-m-8">
			<input type="submit" class="btn" name="btnSubmit" value="Submit and Close">
			<input type="submit" class="btn" name="btnSubmitAdd" value="Submit and Add Another">
			<input type="button" class="btn" onclick="location.href = '<?php echo site_url('MsConfig') ?>';" value="Close">
		</div>
	</div>
	<?php echo form_close(); ?>
</div>

<script>
	$(document).ready(function() {
		$("#Add").click(function() {
			if($(this).prop("checked") == true){
				$('.AddInfo').prop('disabled',false);
				$("#AddInfoParam").focus();
			}
			else{
				$('.AddInfo').prop('disabled',true);
				$('.AddInfo').val('');
			}
		});
		
		$("#ConfigType").change(function() {
			if($(this).val()=='OTHER') {
				$("#ConfigType_Other").attr('required', true);
				$("#ConfigType_Other").attr('disabled', false);
				$("#ConfigType_Other").focus();
			}
			else{
				$("#ConfigType_Other").val('');
				$("#ConfigType_Other").attr('required', false);
				$("#ConfigType_Other").attr('disabled', true);
			}
		});
		
		$("#ConfigName").change(function() {
			if($(this).val()=='OTHER') {
				$("#ConfigName_Other").attr('required', true);
				$("#ConfigName_Other").attr('disabled', false);
				$("#ConfigName_Other").focus();
			}
			else{
				$("#ConfigName_Other").val('');
				$("#ConfigName_Other").attr('required', false);
				$("#ConfigName_Other").attr('disabled', true);
			}
		});
		
		$("#Group").change(function() {
			if($(this).val()=='OTHER') {
				$("#Group_Other").attr('required', true);
				$("#Group_Other").attr('disabled', false);
				$("#Group_Other").focus();
			}
			else{
				$("#Group_Other").val('');
				$("#Group_Other").attr('required', false);
				$("#Group_Other").attr('disabled', true);
			}
		});
	});
</script>

