<style>
</style>

<script>
</script>
<div class="container">
	<div class="form_title"><center>MASTER PENGALI LIMIT</center></div>
	<br>
	<?php echo form_open('MsPengaliLimit/Save'); ?>
	
	<div class="row">
        <div class="col-3 col-m-4">Divisi</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="divisi" value="<?php echo $result->Divisi ?>" style="background:lightgray" readonly>
		</div>
	</div>

	<div class="row div_wilayah">
        <div class="col-3 col-m-4">Partner Type</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="partner_type" value="<?php echo $result->Partner_Type ?>" style="background:lightgray" readonly>
		</div>
	</div>

	<?php if(ISSET($result->Wilayah)){ ?>
	<div class="row div_wilayah">
        <div class="col-3 col-m-4">Wilayah</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="wilayah" value="<?php echo $result->Wilayah ?>" style="background:lightgray" readonly>
		</div>
	</div>

	<?php } ?>
	<?php if(ISSET($result->kd_Plg)){ ?>
	<div class="row div_toko">
        <div class="col-3 col-m-4">Kode</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="kd_plg"  value="<?php echo $result->kd_Plg ?>" style="background:lightgray" placeholder="Kode (readonly)" readonly>
		</div>
	</div>
	<div class="row div_toko">
        <div class="col-3 col-m-4">Nama Toko</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="nm_plg"  value="<?php echo $result->nm_plg ?>" style="background:lightgray" placeholder="Nama Toko (readonly)" readonly>
		</div>
	</div>
	<?php } ?>
	
	<div class="row">
        <div class="col-3 col-m-4">Pengali Limit (%)</div>
        <div class="col-9 col-m-8">
			<input type="number" class="form-control" name="pengali" value="<?php echo $result->Pengali ?>" placeholder="0" step=".01" required>
		</div>
	</div>
	
	<?php if(ISSET($result->kd_Plg)){ ?>
	<div class="row div_toko">
        <div class="col-3 col-m-4">Max Limit (Rp)</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control numeric" name="max_limit" value="<?php echo number_format($result->max_limit) ?>" placeholder="0">
		</div>
	</div>
	<?php } ?>
	<div class="row">
        <div class="col-3 col-m-4"></div>
        <div class="col-9 col-m-8">
			<input type="submit" class="btn btn-primary" value="Submit">
			<input type="button" class="btn btn-danger" onclick="location.href = '<?php echo site_url('MsPengaliLimit') ?>';" value="Cancel">
		</div>
	</div>
	<?php echo form_close(); ?>
</div>
<script>
	$(document).on("input", ".numeric", function() {
		this.value = addCommas(this.value.replace(/\D/g,''));
	});
	
	function addCommas(nStr) {
		nStr += '';
		var x = nStr.split('.');
		var x1 = x[0];
		var x2 = x.length > 1 ? '.' + x[1] : '';
		var rgx = /(\d+)(\d{3})/;
		while (rgx.test(x1)) {
			x1 = x1.replace(rgx, '$1' + ',' + '$2');
		}
		return x1 + x2;
	}
</script>