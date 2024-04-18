<style>
	fieldset { border:1px solid #ccc; padding:15px!important; border-radius:10px; margin-top:10px; margin-bottom:10px; }

	.btnSubmit { background-color: #0d7d07; color:#fff!important;}

	.col_header {
		background-color: #000099; color: #fff; border:1px dashed #ccc; height:50px; padding:10px;
	}
	.col_header_text {
		line-height:30px; vertical-align: middle;
	}
	.col_detail {
		background-color: #e6e6ff; color: #000; border:1px dashed #ccc; min-height:40px;
		padding:5px;
	}

	@media only screen and (max-width: 599 px) {
		.col_header {
			padding:5px;
		}
		.col_detail {
			padding:1px;
			height:30px;
			background-color: transparent; color: #000;
		}
	}
</style>

<h4 style="display:<?php if(!$this->session->flashdata('info')) echo 'none';?>; position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;" id="info-alert" class="alert alert-success col-sm-12">
  <!-- error msg here -->
  <?php 
    echo $this->session->flashdata('info'); 
    if(isset($_SESSION['info'])){
        unset($_SESSION['info']);
    }
  ?>
</h4>


<div class="container">
	<div class="form_title">CHANGE PASSWORD</div>
	<div class="clearfix"></div>
	<div class="manualForm">
		<fieldset>
			<?php echo form_open('HomeController/changePassword'); ?>
			<div class="row">
				<div class="col-4 col-m-5">Old Password</div>
				<div class="col-8 col-m-7">					
				<input type="password" class="user_input" name="txtOldPassword" id="txtOldPassword" placeholder="Please type your old password here" onkeyup="checkPassword()">
				</div>
			</div>			
			<div class="row">
				<div class="col-4 col-m-5">New Password</div>
				<div class="col-8 col-m-7">
                <input type="password" class="user_input" name="txtNewPassword" id="txtNewPassword" placeholder="Please type your new password here" onkeyup="checkPassword()">
				</div>
			</div>	
			<div class="row">
				<div class="col-4 col-m-5">Re-type New Password</div>
				<div class="col-8 col-m-7">
                <input type="password" class="user_input" name="txtReNewPassword" id="txtReNewPassword" placeholder="Please re-type your new password here" required onkeyup="checkPassword()">
				</div>
			</div>			
			<div class="row">
					<center><span style="color:red;" id="errormsg"></span></center>
				</div>
			</div>			
			<div class="row">
				<div class="col-4 col-m-5"></div>
				<div class="col-8 col-m-7">
					<button type="submit" id="btnSubmit" name="btnSubmit" disabled>SUBMIT</button>
				</div>
			</div>			
			<?php echo form_close(); ?>
		</fieldset>

		<div class="clearfix"></div>
	</div>
</div>
</div>
	<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;padding:5px;">
		<a href="<?php echo site_url('UserControllers'); ?>"><button type="button" class="btn btnCancel" align="right">KEMBALI</button></a>
	</div>
</div>

<script>
  function checkPassword(){
	if ($('#txtNewPassword').val().length < 6){
	  $('#errormsg').show();
	  $('#errormsg').html("Your New Password is too short");
	  $('#btnSubmit').prop('disabled', true);
	  $('#txtReNewPassword').prop('disabled', true);
	}
	else{
		$('#txtReNewPassword').prop('disabled', false);
	   if ($('#txtNewPassword').val() != $('#txtReNewPassword').val()) {
		  $('#errormsg').show();
		  $('#errormsg').html("Your New Passwords do not match!");
		  $('#btnSubmit').prop('disabled', true);
		}
		else{
		  $('#errormsg').hide();
		  $('#btnSubmit').prop('disabled', false);
		}
	}
  }
</script>


