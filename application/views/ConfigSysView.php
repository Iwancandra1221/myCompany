<style>
	.fontBig {
		font-size: 17px;
	}
</style>
<script>
</script>

<div id="notifikasi">
	<?php
	if (isset($update)) {
		if ($update == 'success') {
			echo '
				<div class="msg msg-success">
					<i class="glyphicon glyphicon-ok-sign"></i>
					Config Sys Berhasil di-update
				</div>';
		}
		if ($update == 'failed') {
			echo '
				<div class="msg msg-danger">
					<i class="glyphicon glyphicon-remove-circle"></i>
					Config Sys Gagal di-update
				</div>';
		}
	}
	?>
</div>


<div class="container">
	<div class="form_title" style="text-align: center;">
		CONFIG SYS
	</div>
	<br>
	<?php echo form_open('ConfigSys/ConfigSysUpdate', array('id' => 'myformXXX')); ?>
	<div class="border20 p20">

		<div class="row">
			<div class="col-3 col-m-4"><span class="fontBig">Company Name</span><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="text" name="company_name" value="<?php echo $result->company_name ?>" class="form-control form-control-dark" placeholder="Company Name" required>
			</div>
		</div>

		<hr class="hr-dark">

		<div class="row">
			<div class="col-3 col-m-4"><span class="fontBig">Mail Protocol</span><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="text" name="mail_protocol" value="<?php echo $result->mail_protocol ?>" class="form-control form-control-dark" maxlength="5" placeholder="Mail Protokol" required>
			</div>
		</div>
		<div class="row">
			<div class="col-3 col-m-4"><span class="fontBig">Mail Host</span><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="text" name="mail_host" value="<?php echo $result->mail_host ?>" class="form-control form-control-dark" placeholder="Mail Host" required>
			</div>
		</div>
		<div class="row">
			<div class="col-3 col-m-4"><span class="fontBig">Mail Port</span><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="text" name="mail_port" value="<?php echo $result->mail_port ?>" class="form-control form-control-dark" placeholder="Mail Port" required>
			</div>
		</div>
		<div class="row">
			<div class="col-3 col-m-4"><span class="fontBig">Mail User</span><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="text" name="mail_user" value="<?php echo $result->mail_user ?>" class="form-control form-control-dark" placeholder="Mail User" required>
			</div>
		</div>
		<div class="row">
			<div class="col-3 col-m-4"><span class="fontBig">Mail Password</span><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="password" name="mail_pwd" value="<?php echo $result->mail_pwd ?>" class="form-control form-control-dark" placeholder="Mail Password" required>
			</div>
		</div>
		<div class="row">
			<div class="col-3 col-m-4"><span class="fontBig">Mail Alias</span><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="text" name="mail_alias" value="<?php echo $result->mail_alias ?>" class="form-control form-control-dark" placeholder="Mail Alias" required>
			</div>
		</div>
		<div class="row">
			<div class="col-3 col-m-4"><span class="fontBig">SMTP Crypto</span><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="text" name="smtp_crypto" value="<?php echo $result->smtp_crypto ?>" class="form-control form-control-dark" placeholder="SMTP Crypto" required>
			</div>
		</div>

		<div class="row">
			<div class="col-3 col-m-4"><span class="fontBig">Bugsnag Environment</span><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="text" name="bugsnag_environment" value="<?php echo $result->bugsnag_environment ?>" class="form-control form-control-dark" placeholder="Bugsnag Environment" required>
			</div>
		</div>

		<div class="row">
			<div class="col-3 col-m-4"><span class="fontBig">bktAPI AppName</span><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="text" name="bktapi_appname" value="<?php echo $result->bktapi_appname ?>" class="form-control form-control-dark" placeholder="bktAPI AppName" required>
			</div>
		</div>

		<div class="row">
			<div class="col-3 col-m-4"><span class="fontBig">webAPI URL</span><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="text" name="webapi_url" value="<?php echo $result->webapi_url ?>" class="form-control form-control-dark" placeholder="webAPI URL" required>
			</div>
		</div>

		<div class="row">
			<div class="col-3 col-m-4"><span class="fontBig">messageAPI URL</span><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="text" name="messageapi_url" value="<?php echo $result->messageapi_url ?>" class="form-control form-control-dark" placeholder="messageAPI URL" required>
			</div>
		</div>

		<div class="row">
			<div class="col-3 col-m-4"><span class="fontBig">ZenHRS URL</span><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="text" name="zenhrs_url" value="<?php echo $result->zenhrs_url ?>" class="form-control form-control-dark" placeholder="ZenHRS URL" required>
			</div>
		</div>

		<div class="row">
			<div class="col-3 col-m-4"><span class="fontBig">bktAPI_HO URL</span><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="text" name="bktapi_ho_url" value="<?php echo $result->bktapi_ho_url ?>" class="form-control form-control-dark" placeholder="bktAPI_HO URL" required>
			</div>
		</div>

		<div class="row">
			<div class="col-3 col-m-4"><span class="fontBig">Mishirin URL</span><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="text" name="mishirin_url" value="<?php echo $result->mishirin_url ?>" class="form-control form-control-dark" placeholder="Mishirin URL" required>
			</div>
		</div>

		<div class="row">
			<div class="col-3 col-m-4"><span class="fontBig">Mishirin KEY</span><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="password" name="mishirin_key" value="<?php echo $result->mishirin_key ?>" class="form-control form-control-dark" placeholder="Mishirin KEY" required>
			</div>
		</div>

		<div class="row">
			<div class="col-3 col-m-4"><span class="fontBig">webAPI JAVA URL</span><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="text" name="webapi_java_url" value="<?php echo $result->webapi_java_url ?>" class="form-control form-control-dark" placeholder="webAPI JAVA URL" required>
			</div>
		</div>

		<div class="row">
			<div class="col-3 col-m-4"></div>
			<div class="col-9 col-m-8">
				<div class="mb20">Last Modified on <?php echo date('d M Y', strtotime($result->modified_date)) ?> | <?php echo date('H:i:s', strtotime($result->modified_date)) ?> | <?php echo $result->modified_by ?>
				</div>
				<input type="submit" name="save" id="btnSubmit" class="btn btn-dark btnSubmit" value="UPDATE CONFIG">
			</div>
		</div>
		<div class="row">
			<center>
			</center>
		</div>
	</div>

	<?php echo form_close(); ?>
</div>


<script type="text/javascript">
	$(document).ready(function() {
		$(".msg").delay(3000).fadeOut("slow");
	});
</script>