<style>
</style>

<div class="container">
	<div class="form_title" style="text-align: center;">TAMBAH GROUP ITEM FOKUS</div>
	<br>
	<?php echo form_open('MasterGroupItemFokus/Save'); ?>
	<div class="row">
        <div class="col-3 col-m-4">Nama Group Item Fokus</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="level_code" placeholder="Nama Group Item Fokus" required>
		</div>
	</div>
	<div class="row">
        <div class="col-3 col-m-4">Wilayah Exclude</div>
        <div class="col-9 col-m-8">
			<?php
			$i = 0;
			foreach($wilayah as $wil) {
				$i++;
				echo "<input type='checkbox' name='wilayah[]' value='".$wil['Wilayah']."' class='wilayah' id='checkbox_".$i."'> <label for='checkbox_".$i."'>".$wil['Wilayah']."</label><br>";
			}
			?>
		</div>
	</div>
	
	<div class="row">
        <div class="col-3 col-m-4">Aktif</div>
        <div class="col-9 col-m-8">
			<input type="radio" name="is_active" value="1" checked> Y
			<input type="radio" name="is_active" value="0"> N
		</div>
	</div>
	
	
	<div class="row">
        <div class="col-3 col-m-4"></div>
        <div class="col-9 col-m-8">
			<input type="submit" class="btn btn-primary" value="Submit">
			<input type="button" class="btn btn-danger" onclick="location.href = '<?php echo site_url('MasterGroupItemFokus') ?>';" value="Cancel">
		</div>
	</div>
	<?php echo form_close(); ?>
</div>

<script>
	$(document).ready(function() {
		$(".wilayah").click(function(){
			 if ($('.wilayah').filter(':checked').length < 1){
				$('.wilayah').prop('required', true);
			 }else{
				$('.wilayah').prop('required', false);
			 }
		});
	} );
</script>

