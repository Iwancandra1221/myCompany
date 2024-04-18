<style>
	input{
		text-transform:uppercase;color:black!important;
	}
</style>

<div class="container">
	<div class="form_title" style="text-align: center;">EDIT PESAN BLACKLIST</div>
	<br>
	<?php echo form_open('UniqueCodeGenerator'.$version.'/BlacklistPesanUpdate'); ?>
			<input type="hidden" name="ID" value="<?php echo $result->ID ?>">
	<div class="row">
        <div class="col-3 col-m-4">Pesan</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="Pesan" placeholder="Pesan" value="<?php echo $result->Pesan ?>">
		</div>
	</div>
	<div class="row">
        <div class="col-3 col-m-4">Aktif</div>
        <div class="col-9 col-m-8">
			<input type="radio" name="IsActive" value="1" <?php echo ($result->IsActive==1) ? "checked" : ""; ?>> Y
			<input type="radio" name="IsActive" value="0" <?php echo ($result->IsActive==0) ? "checked" : ""; ?>> N
		</div>
	</div>
	
	
	<div class="row">
        <div class="col-3 col-m-4"></div>
        <div class="col-9 col-m-8">
			<input type="submit" class="btn btn-primary" value="Submit">
			<input type="button" class="btn btn-danger" onclick="location.href = '<?php echo site_url('UniqueCodeGenerator'.$version.'/BlacklistPesan'); ?>';" value="Cancel">
		</div>
	</div>
	<?php echo form_close(); ?>
</div>

