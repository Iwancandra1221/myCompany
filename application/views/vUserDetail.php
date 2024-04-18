<script>
	$(document).ready(function(){});
</script>

<div class = "wrapper">
<div class="form_title">Detail User</div>		
<div class="button-bar-container"><a href="<?php echo site_url('UserControllers'); ?>"><div class="btn btnCancel">Kembali</div></a></div>
<div class="clearfix"></div>
<div class="wrapper_dataTable">
	
	<div style="border:1px solid #CCC; border-radius:10px; padding:15px; margin:20px; min-height:150px;">
		<div class="formInput">
			<label class="width-40">User ID</label>
			<label class="width-50"><b><?php echo($obj->UserEmail); ?></b></label>
		</div>
		<div class="formInput">
			<label class="width-40">Nama User</label>
			<label class="width-50"><?php echo($obj->UserName); ?></label>
		</div>
		<div class="formInput">
			<label class="width-40">Aktif</label>
			<label class="width-50"><?php echo(($obj->IsActive==1) ? "YA" : "TIDAK"); ?></label>
		</div>		
		<div class="formInput">
			<label class="width-40">User BIT</label>
			<label class="width-50"><?php echo(($obj->Flag==1) ? "YA" : "TIDAK"); ?></label>
		</div>		
	</div>

	<div class="clearfix"></div>

	<div style="border:1px solid #CCC; border-radius:10px; padding:15px; margin:20px;">
	<table class="dataTable display" id="tblDetailRole">
		<thead>
			<tr>
				<th scope="col" width="30%">ID Role</th>
				<th scope="col" width="50%">Nama Role</th>
				<th scope="col" width="20%">Role HRD</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($UserRoles as $r)
			{
			?>
			<tr>
				<td width="30%" align="center"><?php echo($r->role_id); ?></td>
				<td width="50%" align="center"><?php echo($r->role_name); ?></td>
				<td width="20%" align="center"><?php echo(($r->is_hrd==1)? "YA":"TIDAK"); ?></td>
			</tr>
			<?php
			}
			?>
		</tbody>
	</table>
	</div>
	<div class="clearfix"></div>

	<div style="border:1px solid #CCC; border-radius:10px; padding:15px; margin:20px;">
	<table class="dataTable display" id="tblDetailDivision">
		<thead>
			<tr>
				<th scope="col" width="40%">ID User Division</th>
				<th scope="col" width="60%">Nama User Division</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($UserDivisions as $d)
			{
			?>
			<tr>
				<td width="40%" align="center"><?php echo($d->user_division_id); ?></td>
				<td width="60%" align="center"><?php echo($d->user_division_name); ?></td>
			</tr>
			<?php
			}
			?>
		</tbody>
	</table>
	</div>
</div>
</div>