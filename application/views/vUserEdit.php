<script>
	var role=<?php echo json_encode($role); ?>;
	var division=<?php echo json_encode($division); ?>;
	var employeeList=<?php echo json_encode($EmployeeList); ?>;
	
	$(document).ready(function(){
		var GroupCuti = "<?php echo($obj->GroupCuti);?>";

		<?php if(isset($error) && $error != '') echo 'alert("'.$error.'");'; ?>
		
		$("#btnAddRole").click(function(){
			var add = $("#tblSampleRole").find("#rowRole").clone();
			$("#tblDetailRole #tblDetailRoleBody").append(add);
		});

		$(".btnDeleteRole").click(function(){
			var tr=$(this).parent().parent();
			$(tr).find(".RoleStatus").val("delete");
			var RoleId = $(tr).find(".RoleId").val();
			$("#RoleDeleted").val($("#RoleDeleted").val()+RoleId+";");
			//$(tr).addClass("hideMe");
			$(tr).remove();
		});

		$("#tblDetailRole").on('change','.role',function()
		{
			var val=$(this).val();
			var _this=$(this);
			for(var r in role)
			{
				if (role[r].role_id==val)
				{
					var o=role[r];
					var tr=$(this).parent().parent();
					$(tr).find('.RoleId').val(o.role_id);
					$(tr).find('.RoleName').val(o.role_name);
					break;
				}
			}
		});

		$("#btnAddDivision").click(function(){
			var add = $("#tblSampleDivision").find("#rowDivision").clone();
			$("#tblDetailDivision #tblDetailDivisionBody").append(add);
		});

		$(".btnDeleteDivision").click(function(){
			var tr=$(this).parent().parent();

			$(tr).find(".UserDivisionStatus").val("delete");
			var DivId = $(tr).find(".UserDivisionID").val();
			$("#DivisionDeleted").val($("#DivisionDeleted").val()+DivId+";");
			$(tr).remove();
			//$(tr).addClass("hideMe");
		});

		$("#tblDetailDivision").on('change','.division',function()
		{
			var val=$(this).val();
			var _this=$(this);
			for(var d in division)
			{
				if (division[d].user_division_id==val)
				{
					var o=division[d];
					var tr=$(this).parent().parent();
					$(tr).find('.UserDivisionID').val(o.user_division_id);
					$(tr).find('.UserDivisionName').val(o.user_division_name);
					break;
				}
			}
		});

		$('#EmployeeID').on('change', function() {
			var EmployeeID = this.value;
			var BranchID = "";
			var GroupID = "";
			var EmpLevel = "";
			var EmpType = "";
			var Badgenumber = "";

			if (EmployeeID!="") {
				for(var e in employeeList)
				{
					if (employeeList[e].employee_id==EmployeeID)
					{
						BranchID = employeeList[e].BranchID;
						//var BranchName=employeeList[e].BranchName;
						GroupID = employeeList[e].workgroup_id;
						//var GroupName=employeeList[e].workgroup_name;
						Badgenumber = employeeList[e].attendance_id;
						EmpLevel= employeeList[e].employee_level;
						EmpType = employeeList[e].employee_status;
						//alert(Badgenumber);
						$("#Branch").val(BranchID);
						$("#Group").val(GroupID);
						$("#Badgenumber").val(Badgenumber);
						$("#EmpLevel").val(EmpLevel);
						$("#EmpType").val(EmpType);

						break;
					}
				}				
			}

			if (GroupID!="")
			{
				if (GroupID!=GroupCuti && GroupCuti!="") {
					var x = '<option value="">Choose User Division</option>';
					$(".loading").show();
					var csrf_bit = $("input[name=csrf_bit]").val();
					$.post("<?php echo site_url('UserDivision/Gets'); ?>", {
						workgroup:GroupID,
						csrf_bit:csrf_bit
					}, function(data){
						if (data.error != undefined)
						{
							$("#userDivision").html(x);
						}
						else
						{
							for(var i=0; i<data.length; i++)
							{	
								x = x + '<option value="'+data[i].user_division_id+'">'+data[i].user_division_name+'</option>';
							}
							$(".division").html(x);
						}
						$(".loading").hide();
					}
					,'json',errorAjax);	
				}
			}/* else if (BranchID!="" && BranchID!=BranchEmp && BranchEmp!="") {

				var x = '<option value="">Choose User Division</option>';
				$(".loading").show();
				var csrf_bit = $("input[name=csrf_bit]").val();
				$.post("<?php echo site_url('UserDivision/Gets'); ?>", {
					branch:BranchID,
					csrf_bit:csrf_bit
				}, function(data){
					if (data.error != undefined)
					{
						$("#userDivision").html(x);
					}
					else
					{
						for(var i=0; i<data.length; i++)
						{	
							x = x + '<option value="'+data[i].user_division_id+'">'+data[i].user_division_name+'</option>';
						}
						$(".division").html(x);
					}
					$(".loading").hide();
				}
				,'json',errorAjax);		

			} else {
				$("#userDivision").html(x);
			}

			for(var i=0; i<userDivision.length;i++) {
				$("#division["+i+"]").val(userDivision[i].user_division_id);
			}*/
		});

		$("#btnSubmit").click(function(){
			$("#FormUser").submit();
		});

	});	

</script>
<style>
	.col_header {
		background-color: #000099; color: #fff; border:1px dashed #ccc; height:50px; padding:10px;
	}
	.col_header_text {
		line-height:30px; vertical-align: middle;
	}
	.col_detail {
		background-color: #e6e6ff; color: #000; border:1px dashed #ccc; min-height:40px;
	}

@media only screen and (max-width: 599 px) {
	.col_header {
		padding:5px;
	}
	.col_detail {
		height:30px;
		background-color: transparent; color: #000;
	}
}
</style>

<div class="wrapper">
	<?php echo form_open('UserControllers/Edit/'.md5($obj->UserEmail), array("id"=>"FormUser")); ?>

	<div class="form_title">Edit User</div>
	<div class="button-bar-container">
		<a href="<?php echo site_url('UserControllers'); ?>"><div class="btn btnCancel">Batal</div></a>
		<div class="btn btnSubmit" id="btnSubmit" name="btnSubmit">Simpan</div>
	</div>

	<div class="clearfix"></div>
	<div class="manualForm">
		<fieldset>
			<div class="row">
				<div class="col-4 col-m-5">Login MyCompany.id</div>
				<div class="col-8 col-m-7"><?php
				$attr = array(
					'placeholder' => $this->lang->line('UserEmail'),
					'id' => 'UserEmail',
					'readonly'=>true,
					'style'=>"width:50%"
					);
				echo BuildInput('text','UserEmail',$attr,$obj->UserEmail);
				?></div>
			</div>
			<div class="row">
				<div class="col-4 col-m-5"><?php echo($this->lang->line('UserName'));?></div>
				<div class="col-8 col-m-7"><?php
					$attr = array(
						'placeholder' => $this->lang->line('UserName'),
						'id' => 'UserName',
						/*'readonly'=>true,*/
						'style'=>"width:50%"
						);
					echo BuildInput('text','UserName',$attr,$obj->UserName);
				?></div>
			</div>
			<?php 
				$GroupCuti = (($obj->GroupCuti==null)? "":$obj->GroupCuti); 
				//echo($GroupCuti."<br>");
			?>
			<div class="row">
				<div class="col-4 col-m-5">Grup Kerja Cuti</div>
				<div class="col-8 col-m-7">
					<select id="GroupID" name="GroupID" style="width:100%!important;">
						<option value=''>--Pilih Grup Kerja--</option>
						<?php 
							foreach($groups as $g) {
								echo("<option value='".$g->workgroup_id."'".(($obj->GroupCuti==$g->workgroup_id)?" selected":"").">".$g->workgroup_name."</option>");
							}
						?>
					</select>
				</div>
			</div>
			<?php 
				//echo("abc");
				//echo((($obj->IsActive==null)? "Aktif Null" : "Aktif")."<br>");
			?>
			<div class="row">
				<div class="col-4 col-m-5"><?php echo($this->lang->line('Active'));?></div>
				<div class="col-1 col-m-1"><?php
				echo BuildInput('checkbox','IsActive',array('id'=>'IsActive','checked'=>$obj->IsActive == '1'),'1');
				?></div>
				<div class="col-7 col-m-6"></div>
			</div>		
			<div class="row">
				<div class="col-4 col-m-5"><?php echo($this->lang->line('UserBIT'));?></div>
				<div class="col-1 col-m-1"><?php
				echo BuildInput('checkbox','Flag',array('id'=>'Flag','checked'=>$obj->Flag == '1'),'1');
				?></div>
				<div class="col-7 col-m-6"></div>
			</div>
		</fieldset>
		<fieldset>
			<legend><b>Data Berikut adalah Info Karyawan Dari ZenHRS</b></legend>
			<div class="row">
				<div class="col-4 col-m-5">ID Karyawan</div>
				<div class="col-8 col-m-7">
					<select id="EmployeeID" name="EmployeeID" style="width:100%!important;">
						<option value='0'>--Pilih ID--</option>
						<?php foreach($EmployeeList as $el) {
							echo("<option value='".$el->employee_id."'".(($obj->UserEmail==$el->UserEmail)?" selected":"").">".$el->employee_name." [ ".$el->UserEmail." ][".$el->employee_id."]</option>");
						}?>
					</select>
				</div>
			</div>
			<div class="row">
				<div class="col-4 col-m-5">Badgenumber</div>
				<div class="col-8 col-m-7"><?php
					$attr = array(
						'placeholder' => 'Badgenumber',
						'id' => 'Badgenumber',
						'style'=>"width:50%"
						);
					echo BuildInput('text','Badgenumber',$attr,((isset($Employee->attendance_id))?$Employee->attendance_id:""));
				?></div>
			</div>
			<div class="row">
				<div class="col-4 col-m-5">Cabang <i>(readonly)</i></div>
				<div class="col-8 col-m-7"><?php
					$attr = array(
						'placeholder' => 'Cabang (readonly)',
						'id' => 'Branch',
						'readonly'=>true,
						'style'=>"width:50%"
						);
					echo BuildInput('text','Branch',$attr,((isset($Employee->branch_name))?$Employee->branch_name:""));
				?></div>
			</div>
			<div class="row">
				<div class="col-4 col-m-5">Grup Kerja <i>(readonly)</i></div>
				<div class="col-8 col-m-7"><?php
					$attr = array(
						'placeholder' => 'Group Kerja (readonly)',
						'id' => 'Group',
						'readonly'=>true,
						'style'=>"width:50%"
						);
					echo BuildInput('text','Group',$attr,((isset($Employee->workgroup_name))?$Employee->workgroup_name:""));
				?></div>
			</div>
			<div class="row">
				<div class="col-4 col-m-5">Level Karyawan <i>(readonly)</i></div>
				<div class="col-8 col-m-7"><?php
					$attr = array(
						'placeholder' => 'Level Karyawan (readonly)',
						'id' => 'EmpLevel',
						'readonly'=>true,
						'style'=>"width:50%"
						);
					echo BuildInput('text','EmpLevel',$attr,((isset($Employee->employee_level))?$Employee->employee_level:""));
				?></div>
			</div>
			<div class="row">
				<div class="col-4 col-m-5">Status Karyawan <i>(readonly)</i></div>
				<div class="col-8 col-m-7"><?php
					$attr = array(
						'placeholder' => 'Status Karyawan (readonly)',
						'id' => 'EmpType',
						'readonly'=>true,
						'style'=>"width:50%"
						);
					echo BuildInput('text','EmpType',$attr,((isset($Employee->employee_status))?$Employee->employee_status:""));
				?></div>
			</div>
		</fieldset>

		<div class="clearfix"></div>
		<h3><?php echo($this->lang->line('Role'));?></h3>

		<div id="tblDetailRole">
			<div id="tblDetailRoleHead">
				<div class="row">
					<div class="col_header" style="width:20%;float:left;"><button type="button" id="btnAddRole" style="color:black;">Add</button></div>
					<div class="col_header col_header_text"  style="width:30%;float:left;"><?php echo($this->lang->line('Role'));?></div>
					<div class="col_header col_header_text hideOnMobile" style="width:25%;float:left;"><?php echo($this->lang->line('RoleId'));?></div>
					<div class="col_header col_header_text hideOnMobile" style="width:25%;float:left;"><?php echo($this->lang->line('RoleName'));?></div>
				</div>
			</div>
			<div id="tblDetailRoleBody">
				<?php
				if(isset($UserRole))
				{
					foreach($UserRole as $ur)
					{
					?>
						<div class="row">
							<div class="col_detail" style="width:20%;float:left;"><button type="button" class="btnDelete btnDeleteRole"><i class="fa fa-times btnRed"></i></button></div>
							<div class="col_detail" style="width:30%;float:left;">
								<select name="role[]" id="role[]" class="role" style="width:90%;">
									<option value="">Choose Role</option>
							<?php foreach($role as $r) { ?>
									<option value="<?php echo($r->role_id)?>"<?php echo(($r->role_id==$ur->role_id)?" selected":"")?>><?php echo($r->role_name)?></option>
							<?php } ?>
								</select>
							</div>
							<div class="col_detail hideOnMobile" style="width:25%;float:left;"><?php echo BuildInput('text','RoleId[]',array('style'=>"width:90% !important;",'readonly'=>"true", 'class'=>"RoleId"), $ur->role_id);	?></div>	
							<div class="col_detail hideOnMobile" style="width:25%;float:left;"><?php echo BuildInput('text','RoleName[]',array('style'=>"width:90% !important;",'readonly'=>"true", 'class'=>"RoleName"), $ur->role_name);	?></div>
						</div>
				<?php
					}
				}
				?>
			</div>
		</div>

		<div class="clearfix"></div>
		<h3><?php echo($this->lang->line('UserDivision'));?></h3>

		<div id="tblDetailDivision">
			<div id="tblDetailDivisionHead">
				<div class="row">
					<div class="col_header" style="width:20%;float:left;"><button type="button" id="btnAddDivision" style="color:black;">Add</button></div>
					<div class="col_header col_header_text" style="width:30%;float:left;"><?php echo($this->lang->line('Division'));?></div>
					<div class="col_header col_header_text hideOnMobile" style="width:25%;float:left;"><?php echo($this->lang->line('DivisionID'));?></div>
					<div class="col_header col_header_text hideOnMobile" style="width:25%;float:left;"><?php echo($this->lang->line('DivisionName'));?></div>
				</div>
			</div>
			<div id="tblDetailDivisionBody">
				<?php
				if(isset($UserDivision))
				{
					foreach($UserDivision as $ud)
					{
					?>
						<div class="row">
							<div class="col_detail" style="width:20%;float:left;"><button type="button" class="btnDelete btnDeleteDivision"><i class="fa fa-times btnRed"></i></button></div>
							<div class="col_detail" style="width:30%;float:left;">
								<select name="division[]" id="division[]" class="division" style="width:90%;">
									<option value="">Choose Division</option>
							<?php foreach($division as $d) { ?>
									<option value="<?php echo($d->user_division_id)?>"<?php echo(($d->user_division_id==$ud->user_division_id)?" selected":"")?>><?php echo($d->user_division_name)?></option>
							<?php } ?>
								</select>
							</div>
							<div class="col_detail hideOnMobile" style="width:25%;float:left;"><?php echo BuildInput('text','UserDivisionID[]',array('style'=>"width:90% !important;",'readonly'=>"true", 'class'=>"UserDivisionID"), $ud->user_division_id);	?></div>	
							<div class="col_detail hideOnMobile" style="width:25%;float:left;"><?php echo BuildInput('text','UserDivisionName[]',array('style'=>"width:90% !important;",'readonly'=>"true", 'class'=>"UserDivisionName"), $ud->user_division_name); ?></div>
						</div>
					<?php
					}
				}
				?>
			</div>
		</div>

		<div style="display:none;">
			<div class="row">
				<div class="col-6 col-m-6">
					<?php
						$attr = array(
							'readonly' => true,
							'id' => 'RoleDeleted'
							);
						echo BuildInput('text','RoleDeleted',$attr,"");
					?>
				</div>
				<div class="col-6 col-m-6">
					<?php
						$attr = array(
							'readonly' => true,
							'id' => 'DivisionDeleted'
							);
						echo BuildInput('text','DivisionDeleted',$attr,"");
					?>
				</div>
			</div>
		</div>
	</div>
	<?php echo form_close(); ?>
</div>

<div id="tblSampleRole" style="display:none;">
	<div class="row" id="rowRole">
		<div class="col_detail" style="width:20%;float:left;">
			<button type="button" class="btnDelete btnDeleteRole"><i class="fa fa-times btnRed"></i></button>
		</div>
		<div class="col_detail" style="width:30%;float:left;">
			<select name="role[]" id="role[]" class="role" style="width:90%;">
				<option value="">Choose Role</option>
		<?php foreach($role as $r) { ?>
				<option value="<?php echo($r->role_id)?>"><?php echo($r->role_name)?></option>
		<?php } ?>
			</select>
		</div>	
		<div class="col_detail hideOnMobile" style="width:25%;float:left;">
			<?php echo BuildInput('text','RoleId[]',array('style'=>"width:90% !important;",'readonly'=>"true", 'class'=>"RoleId")); ?>
		</div>	
		<div class="col_detail hideOnMobile" style="width:25%;float:left;">
			<?php echo BuildInput('text','RoleName[]',array('style'=>"width:90% !important;",'readonly'=>"true", 'class'=>"RoleName")); ?>
		</div>
	</div>
</div>

<div id="tblSampleDivision" style="display:none;">
	<div class="row" id="rowDivision">
		<div class="col_detail" style="width:20%;float:left;">
			<button type="button" class="btnDelete btnDeleteDivision"><i class="fa fa-times btnRed"></i></button>
		</div>
		<div class="col_detail" style="width:30%;float:left;">
			<select name="division[]" id="division[]" class="division" style="width:90%;">
				<option value="">Choose Division</option>
		<?php foreach($division as $d) { ?>
				<option value="<?php echo($d->user_division_id)?>"><?php echo($d->user_division_name)?></option>
		<?php } ?>
			</select>
		</div>
		<div class="col_detail hideOnMobile" style="width:25%;float:left;">
			<?php
				echo BuildInput('text','UserDivisionID[]',array('style'=>"width:90% !important;",'readonly'=>"true", 'class'=>"UserDivisionID"));
			?>
		</div>
		<div class="col_detail hideOnMobile" style="width:25%;float:left;">
			<?php
				echo BuildInput('text','UserDivisionName[]',array('style'=>"width:90% !important;",'readonly'=>"true", 'class'=>"UserDivisionName"));
			?>
		</div>
	</div>
</div>


