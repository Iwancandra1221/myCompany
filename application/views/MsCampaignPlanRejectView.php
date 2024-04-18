<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>
	.row { padding:5px; }
	.campaign-plan-card { font-size:20px;}
</style>
<div class="container">
	<div class="alert">
		<?php if ($this->session->flashdata('success_message') != '') { ?>
			<div class="e-message success"><?php echo $this->session->flashdata('success_message'); ?></div>
		<?php
		}
		if ($this->session->flashdata('err_message') != '') {
		?>
			<div class="e-message error"><?php echo $this->session->flashdata('err_message'); ?></div>
		<?php } ?>
	</div>

	<div class="title"><h3>TOLAK RENCANA CAMPAIGN ?</h3></div>
	<?php echo form_open('CampaignPlanApproval/Reject', array("id" => "FormCampaignPlan")); ?>
	<div class="form">
		<div class="campaign-plan-card">
			<div class="row">
				<div class="col-3 col-m-4 header-row-label">Kode Rencana Campaign</div>
				<div class="col-5 col-m-4 header-row">
					<input type="text" class="form-control" name="txtKodeCampaign" id="txtKodeCampaign" value="<?php echo($CampaignID);?>" style="width:200px;" readonly>
				</div>
				<div class="col-4 col-m-4 header-row"></div>
			</div>
			<div class="row">
				<div class="col-3 col-m-4 header-row-label">Alasan</div>
				<div class="col-9 col-m-8 header-row">
					<input type="text" class="form-control" name="txtAlasan" id="txtAlasan" placeholder="Alasan" style="width:200px;" required>
				</div>
			</div>
			<div class="row">
				<div class="col-3 col-m-4 header-row-label"></div>
				<div class="col-9 col-m-8 header-row">
					<button id="btnSubmit" name="btnSubmit">REJECT</button>
				</div>
			</div>
		</div>
	</div>
	<?php echo form_close(); ?>

	<div style="height:50px;"></div>
	<?php echo($htmlContent);?>
</div>