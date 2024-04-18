<style>
</style>

<div class="container">
	<div class="form_title"><center>Tambah Master Landing Page</center></div>
	<br>
	<?php echo form_open('MsLandingPage/Update'); ?>
			<input type="hidden" name="id" value="<?php echo $result->id ?>">
	<div class="row">
        <div class="col-3 col-m-4">Type</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="type" value="<?php echo $result->type ?>" placeholder="Type">
		</div>
	</div>
	<div class="row">
        <div class="col-3 col-m-4">Brand</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="brand" value="<?php echo $result->brand ?>" placeholder="Brand">
		</div>
	</div>
	<div class="row">
        <div class="col-3 col-m-4">Action</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="action" value="<?php echo $result->action ?>" placeholder="Action">
		</div>
	</div>
	<div class="row">
        <div class="col-3 col-m-4">URL Redirect</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="url_redirect" value="<?php echo $result->url_redirect ?>" placeholder="URL Redirect">
		</div>
	</div>
	
	
	<div class="row">
        <div class="col-3 col-m-4"></div>
        <div class="col-9 col-m-8">
			<input type="submit" class="btn btn-primary" value="Submit">
			<input type="button" class="btn btn-danger" onclick="location.href = '<?php echo site_url('MsLandingPage') ?>';" value="Cancel">
		</div>
	</div>
	<?php echo form_close(); ?>
</div>

