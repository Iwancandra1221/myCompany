<div style="position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;">
	<div style="padding: 5px;">
		<?php if ($this->session->flashdata('error')) { ?>
			<div class='alert alert-danger' id='flash-msg' style='float:auto'>
				<?php echo $this->session->flashdata('error') ?>
			</div>
		<?php } ?>
		<?php if ($this->session->flashdata('success')) { ?>
			<div class='alert alert-success' id='flash-msg' style='float:auto'>
				<?php echo $this->session->flashdata('success') ?>
			</div>
		<?php } ?>
	</div>
</div>
<div class="container">
	<?php echo form_open('MasterReportWilayah/insert_data'); ?>
	<div class="row">
		<div class="col-2 col-m-4">Report Opt</div>
		<div class="col-10 col-m-8">
			<select class="form-control" name="reportopt" id="reportopt" novalidate>
				<?php
				foreach ($ListReportOPT as $s) {
					$x = (trim($s->ConfigValue) == trim($ReportOpt)) ? "selected" : "";
					echo ("<option value='" . $s->ConfigValue . "' " . $x . ">" . $s->ConfigValue . "</option>");
				}
				?>
			</select>
		</div>
	</div>
	<div class="row">
		<div class="col-2 col-m-4"></div>
		<div class="col-2 col-m-2" id="hgwilayah">
			<input type="radio" name="grup" id="group_wilayah" value="WILAYAH" <?php echo ($Grup == 'WILAYAH') ? "checked" : "" ?>>
			<label for="group_wilayah"><big>Group Wilayah</big></label>
		</div>
		<div class="col-2 col-m-2" id="hgkota">
			<input type="radio" name="grup" id="group_kota" value="KOTA" <?php echo ($Grup == 'KOTA') ? "checked" : "" ?>>
			<label for="group_kota"><big>Group Kota</big></label>
		</div>
	</div>
	<div class="row">
		<div class="col-2 col-m-4">Nama Group</div>
		<div class="col-10 col-m-8">
			<input type="text" class="form-control" name="wilayahgroup" id="wilayahgroup" placeholder="Edit Wilayah Group" required value="<?php echo $WilayahGroup; ?>">
		</div>
	</div>
	<div class="row">
		<div class="col-2 col-m-4">Partner Type</div>
		<div class="col-10 col-m-8">
			<select class="form-control" name="partnertype" id="partnertype" novalidate>
				<?php
				foreach ($ListPartnerType as $s) {
					$x = (trim($s->ConfigValue) == trim($PartnerType)) ? "selected" : "";
					echo ("<option value='" . $s->ConfigValue . "' " . $x . ">" . $s->ConfigValue . "</option>");
				}
				?>
			</select>
		</div>
	</div>
	<div class="row" id="hwilayah">
		<div class="col-2 col-m-4">Wilayah</div>
		<div class="col-10 col-m-8">
			<select name="wilayah" id="wilayah" class="form-control" required>
				<option value="ALL">Pilih Wilayah</option>
				<?php
				foreach ($wilayah as $w) {
					$s = (trim($w->wilayah) == trim($Wilayah)) ? "selected" : "";
					echo ("<option value='" . $w->wilayah . "' " . $s . ">" . $w->wilayah . "</option>");
				}
				?>
			</select>
		</div>
	</div>
	<div class="row" id="hkota">
		<div class="col-2 col-m-4">Kota</div>
		<div class="col-10 col-m-8">
			<select name="kota" id="kota" class="form-control" required>
				<option value="ALL">Pilih Kota</option>
				<?php
				foreach ($kota as $k) {
					$s = (trim($k->kota) == trim($Kota)) ? "selected" : "";
					echo ("<option value='" . $k->kota . "' " . $s . ">" . $k->kota . "</option>");
				}
				?>
			</select>
		</div>
	</div>
	<div class="row">
		<div class="col-2 col-m-4"></div>
		<div class="col-10 col-m-8">
			<?php
			$s = ($IsActive  == 1) ? "checked" : "";
			echo ("<input type='checkbox' name='IsActive' id='IsActive' value='1' " . $s . "> Aktif");
			?>
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

<script>
	$(document).ready(function() {
		if ($("#group_wilayah").prop("checked")) {
			$("#hkota").hide();
			$("#hwilayah").show();
			setDefaultGroupName('wilayah');
		} else {
			$("#hwilayah").hide();
			$("#hkota").show();
			setDefaultGroupName('kota');
		}

		$("#reportopt").change(function() {
			if ($(this).val() !== "OMZET PER KOTA") {
				$("#hwilayah").show();
				$("#hkota").hide();
				$("#hgkota").hide();
				$("#group_wilayah").prop("checked", true);
				setDefaultGroupName('wilayah');
			} else {
				$("#hwilayah").hide();
				$("#hkota").show();
				$("#hgkota").show();
				$("#group_kota").prop("checked", true);
				setDefaultGroupName('kota');
			}
		});

		$("#group_wilayah").change(function() {
			if ($(this).val() === "WILAYAH") {
				$("#hkota").hide();
				$("#hwilayah").show();
				setDefaultGroupName('wilayah');
			}
		});

		$("#group_kota").change(function() {
			if ($(this).val() === "KOTA") {
				$("#hwilayah").hide();
				$("#hkota").show();
				setDefaultGroupName('kota');
			}
		});

		function setDefaultGroupName(groupType) {
			if (groupType === 'wilayah') {
				var wilayah = $("#wilayah").val();
				$("#wilayahgroup").val(wilayah);
			} else {
				var partnerType = $("#partnertype").val();
				var kota = $("#kota").val();
				var groupName = partnerType + " " + kota;
				$("#wilayahgroup").val('');
			}
		}

		$("#kota, #partnertype, #wilayah").change(function() {
			if ($("#group_wilayah").prop("checked")) {
				setDefaultGroupName('wilayah');
			} else {
				setDefaultGroupName('kota');
			}
		});
	});
</script>