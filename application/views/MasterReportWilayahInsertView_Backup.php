<div class="container">
	<?php echo form_open('MasterReportWilayah/insert_data'); ?>
	<div class="row">
        <div class="col-2 col-m-4">Report Opt</div>
        <div class="col-10 col-m-8">
        <input type="text" class="form-control" name="reportopt" id="reportopt" placeholder="Input Report Opt" required>
		</div>
	</div>
	<div class="row">
        <div class="col-2 col-m-4"></div>
        <div class="col-2 col-m-2">
		<input type="radio" name="grup" id="group_wilayah" value="WILAYAH" required> <label for="group_wilayah"><big>Group Wilayah</big></label>
		</div>
        <div class="col-2 col-m-2">
		<input type="radio" name="grup" id="group_kota" value="KOTA"> <label for="group_kota"><big>Group Kota</big></label>
		</div>
	</div>
	<div class="row">
        <div class="col-2 col-m-4">Nama Group</div>
        <div class="col-10 col-m-8">
			<input type="text" class="form-control" name="wilayahgroup" id="wilayahgroup" placeholder="Input Wilayah Group" required>
		</div>
	</div>
	<div class="row">
        <div class="col-2 col-m-4">Wilayah</div>
        <div class="col-10 col-m-8">
			<select name="wilayah" id="wilayah" class="form-control" required>
				<option value="">Input Wilayah</option>
				<?php
				foreach($wilayah as $w) { 
					echo("<option value='".$w->wilayah."'>".$w->wilayah."</option>");
				}
				?>
			</select>
		</div>
	</div>
	<div class="row">
        <div class="col-2 col-m-4">Kota</div>
        <div class="col-10 col-m-8">
			<input type="text" class="form-control" name="kota" id="kota" placeholder="Input Kota">
		</div>
	</div>
	<div class="row">
        <div class="col-2 col-m-4"></div>
        <div class="col-10 col-m-8">
			<input type="submit" class="btn btn-primary" name='btnSubmit2' value="Submit + Add Another" style="width:250px;">
			<input type="submit" class="btn btn-primary" name='btnSubmit1' value="Submit">
			<input type="button" class="btn btn-danger" onclick="location.href = '../MasterReportWilayah';" value="Cancel">
		</div>
	</div>
	<?php echo form_close(); ?>
</div>