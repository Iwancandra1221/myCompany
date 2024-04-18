<style>
	input{
		text-transform:uppercase;color:black!important;
	}
</style>


<div class="container">
	<div class="form_title" style="text-align: center;">TAMBAH PESAN BLACKLIST</div>
	<br>
	<?php echo form_open('UniqueCodeGenerator'.$version.'/BlacklistPesanInsert'); ?>
	<div class="row">
        <div class="col-3 col-m-4">Pesan</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="pesan" id="pesan" placeholder="Pesan" required>
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

