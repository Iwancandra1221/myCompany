<style>
.message{
	text-align: center;
	width: calc(100% - 10px);
	font-size: 18px;
}
</style>	

<script>
$(document).ready(function()
{
	<?php 
		if(isset($error) && $error != '')
		{
			echo 'alert("'.$error.'");';
		}
		?>

});
</script>



<div class="manualForm">

	<?php echo form_open(); ?>
	<fieldset>
		<div class="clearfix" style="height:25px;"></div>
		<div class="message">
			<?php if($message=="er_auth"){?>
				<img src="<?php echo site_url('images/forbidden.png');?>" height="100px">
				<div class="clearfix" style="height:25px;"></div>
				<?php echo("You don't have permission to perform this action.<br>Please contact [ADMINISTRATOR]!");?>
				<div class="clearfix" style="height:25px;"></div>
				<a href="javascript:history.back()"><input type="button" value="Back to Previous Page"  class="btnCancel"></a>
			<?php }?>

			<?php if($message=="su_profile"){?>
				<img src="<?php echo site_url('images/success.png');?>" height="100px">
				<div class="clearfix" style="height:25px;"></div>
				<?php echo("Succesfully changed password!");?>
				<div class="clearfix" style="height:25px;"></div>
				<a href="<?php echo site_url('logout');?>"><input type="button" value="Sign Out to Verify"  class="btnCancel"></a>
			<?php }?>
			<?php if($message=="su_config"){?>
				<img src="<?php echo site_url('images/success.png');?>" height="100px">
				<div class="clearfix" style="height:25px;"></div>
				<?php echo("Succesfully changed configuration!");?>
				<div class="clearfix" style="height:25px;"></div>
				<a href="<?php echo site_url('configuration');?>"><input type="button" value="Back to Configuration"  class="btnCancel"></a>
			<?php }?>
			<?php if($message=="su_emp_import"){?>
				<img src="<?php echo site_url('images/success.png');?>" height="100px">
				<div class="clearfix" style="height:25px;"></div>
				<?php echo("Succesfully import employees!");?>
				<div class="clearfix" style="height:25px;"></div>
				<a href="<?php echo site_url('employee');?>"><input type="button" value="Back to Employee"  class="btnCancel"></a>
			<?php }?>
			<?php if($message=="su_dep_import"){?>
				<img src="<?php echo site_url('images/success.png');?>" height="100px">
				<div class="clearfix" style="height:25px;"></div>
				<?php echo("Succesfully import divisions!");?>
				<div class="clearfix" style="height:25px;"></div>
				<a href="<?php echo site_url('division');?>"><input type="button" value="Back to Division"  class="btnCancel"></a>
			<?php }?>
			<?php if($message=="su_att_import"){?>
				<img src="<?php echo site_url('images/success.png');?>" height="100px">
				<div class="clearfix" style="height:25px;"></div>
				<?php echo("Succesfully import attendance!");?>
				<div class="clearfix" style="height:25px;"></div>
				<a href="<?php echo site_url('attendance');?>"><input type="button" value="Back to Attendance"  class="btnCancel"></a>
			<?php }?>
			<?php if($message=="su_man_att"){?>
				<img src="<?php echo site_url('images/success.png');?>" height="100px">
				<div class="clearfix" style="height:25px;"></div>
				<?php echo("Succesfully add manual attendance!");?>
				<div class="clearfix" style="height:25px;"></div>
				<a href="<?php echo site_url('manual_att');?>"><input type="button" value="Back to Manual Attendance"  class="btnCancel"></a>
			<?php }?>
			<?php if($message=="su_status_att"){?>
				<img src="<?php echo site_url('images/success.png');?>" height="100px">
				<div class="clearfix" style="height:25px;"></div>
				<?php echo("Succesfully update attendance status!");?>
				<div class="clearfix" style="height:25px;"></div>
				<a href="<?php echo site_url('status_att/index/'.$_GET['date'].'?GroupID='.$_GET['GroupID'].'&DivisionID='.$_GET['DivisionID'].'&EmpTypeID='.$_GET['EmpTypeID'].'&EmpLevelID='.$_GET['EmpLevelID']);?>"><input type="button" value="Back to Attendance Status"  class="btnCancel"></a>
			<?php }?>
			<?php if($message=="su_overtime"){?>
				<img src="<?php echo site_url('images/success.png');?>" height="100px">
				<div class="clearfix" style="height:25px;"></div>
				<?php echo("Succesfully add overtime ".date("d-M-Y",strtotime($_GET["date"]))."!");?>
				<div class="clearfix" style="height:25px;"></div>
				<a href="<?php echo site_url('overtime/index/'.$_GET['date']);?>"><input type="button" value="Back to Overtime"  class="btnCancel"></a>
			<?php }?>

			<?php if($message=="su_benefit"){?>
				<img src="<?php echo site_url('images/success.png');?>" height="100px">
				<div class="clearfix" style="height:25px;"></div>
				<?php echo("Succesfully add benefit !");?>
				<div class="clearfix" style="height:25px;"></div>
				<a href="<?php echo site_url('benefit/index/'.date("Ymd",strtotime($_GET["date1"])).date("Ymd",strtotime($_GET["date2"])));?>"><input type="button" value="Back to Benefit"  class="btnCancel"></a>
			<?php }?>
		</div>

		<div class="clearfix" style="height:25px;"></div>
	</fieldset>
	<?php echo form_close(); ?> 
</div>
