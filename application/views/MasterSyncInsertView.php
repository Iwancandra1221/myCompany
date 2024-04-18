<style>
</style>

<div class="container">
	<div class="form_title" style="text-align: center;">TAMBAH MASTER SYNC</div>
	<div style="text-align: center;"><small>Config Type: <?php echo $ConfigType ?></div>
	<br>
	
	<?php echo form_open('MasterSync/Insert'); ?>
	<input type="hidden" name="ConfigType" value="<?php echo $ConfigType ?>">
	<?php if($ConfigType=='CONFIG') { ?>
		<div class="row">
			<div class="col-3 col-m-4">Branch</div>
			<div class="col-9 col-m-8">
				<select name="BranchId" class="form-control" id="BranchId" required>
					<option value="">Pilih Cabang</option>
					<option value="ALL">ALL</option>
					<?php foreach($branches as $b) { 
						echo("<option value='".$b['Kd_Lokasi']."'>".$b['Nm_Lokasi']." ".$b['Kd_Lokasi']."</option>");
					}?>
				</select>
			</div>
		</div>
	<?php } ?>
	<div class="row">
        <div class="col-3 col-m-4">Config Name</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="ConfigName" id="ConfigName" placeholder="Config Name" required>
		</div>
	</div>
	<div class="row">
        <div class="col-3 col-m-4">Config Value</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="ConfigValue" id="ConfigValue" placeholder="Config Value" required>
		</div>
	</div>
	<?php if($ConfigType=='TABLE') { ?>
	<div class="row">
        <div class="col-3 col-m-4">Level</div>
        <div class="col-9 col-m-8">
			<input type="number" class="form-control" name="Level" value="1" id="Level" placeholder="Level" required>
		</div>
	</div>
		<input type="hidden" name="BranchId" value="ALL">
		<!--div class="row">
			<div class="col-3 col-m-4">Branch</div>
			<div class="col-9 col-m-8">
			<input type="checkbox" name="BranchId" id="BranchIdCheckbox" checked> <u>PILIH SEMUA</u><br>
				<div>
					<?php //foreach($branches as $b) { ?>
						<div class="col-3 col-m-4">
							<input type="checkbox" name="BranchId[]" class="BranchId" value="<?php //echo $b->branch_code ?>" checked> <?php //echo $b->branch_name." ".$b->branch_code ?><br>
						</div>
					<?php //} ?>
				</div>
			</div>
		</div-->
	<?php } ?>
	
	<?php if($ConfigType=='CONFIG') { ?>
	<input type="hidden" name="Level" value="1"> <!-- default=1 -->
	<?php } ?>
	
	<div class="row">
        <div class="col-3 col-m-4"></div>
        <div class="col-9 col-m-8">
			<input type="checkbox" name="IsActive" id="IsActive" value="1" checked> Aktif
		</div>
	</div>
	
	<div class="row">
        <div class="col-3 col-m-4"></div>
        <div class="col-9 col-m-8">
			<input type="submit" class="btn btn-primary" value="Submit">
			<input type="button" class="btn btn-danger" onclick="location.href = '../MasterSync';" value="Cancel">
		</div>
	</div>
	<?php echo form_close(); ?>
</div>

<script>
	$(document).ready(function() {
		$("#BranchIdCheckbox").change(function() {
			$('.BranchId').prop('checked', this.checked);
		});
	} );
</script>

