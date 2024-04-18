<style>
</style>

<div style="position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;">
	<div style="padding: 5px;">
		<?php
			if (isset($_GET['success'])) {
				echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Updated <strong>Successfully !</strong></div>";
			}
		?>
	</div>
</div>

<div class="container">
	<div class="form_title"><center>GRACE PERIOD LOCK TOKO</center></div>
	<br>
	<?php echo form_open('GracePeriodLockToko/Update'); ?>
	<div class="row">
        <div class="col-3 col-m-4" align="right">TRADISIONAL</div>
        <div class="col-9 col-m-8">
			<input type="number" class="form-control" name="GracePeriod_LockToko_Tradisional" placeholder="TRADISIONAL" value="<?php echo $result['GracePeriod_LockToko_Tradisional'] ?>" style="width:20%;" required>
		</div>
	</div>
	<div class="row">
        <div class="col-3 col-m-4" align="right">MODERN OUTLET</div>
        <div class="col-9 col-m-8">
			<input type="number" class="form-control" name="GracePeriod_LockToko_ModernOutlet" placeholder="MODERN OUTLET" value="<?php echo $result['GracePeriod_LockToko_ModernOutlet'] ?>" style="width:20%;" required>
		</div>
	</div>
	<div class="row">
        <div class="col-3 col-m-4" align="right">PROYEK</div>
        <div class="col-9 col-m-8">
			<input type="number" class="form-control" name="GracePeriod_LockToko_Proyek" placeholder="PROYEK" value="<?php echo $result['GracePeriod_LockToko_Proyek'] ?>" style="width:20%;" required>
		</div>
	</div>
	<div class="row">
        <div class="col-3 col-m-4">
		</div>
        <div class="col-9 col-m-8">
        	<em>Dalam Satuan Hari.<br>
				Jika grace periode=2, maka hari ke-3 setelah JT baru toko dikunci (jika faktur belum lunas).<br>
				Contoh: JT 2 Juli, dilock di tgl 5 Juli</em>
		</div>
	</div>
	
	<div class="row">
        <div class="col-3 col-m-4"></div>
        <div class="col-9 col-m-8">
			<input type="submit" class="btn btn-primary" value="UPDATE">
		</div>
	</div>
	<?php echo form_close(); ?>
</div>

