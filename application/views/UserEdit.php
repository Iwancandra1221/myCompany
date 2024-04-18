<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<style>
	@keyframes loading {
	  0% {
	    transform: rotate(0deg);
	  }
	  100% {
	    transform: rotate(360deg);
	  }
	}
</style>
<script>
	var ListEmployee = <?php echo(json_encode($ListEmployee));?>;
	var employees = <?php echo(json_encode($employees));?>;
	
	var userid = <?php echo($user->USERID);?>;
	var role=<?php echo json_encode($roles); ?>;
	var groups = <?php echo json_encode($groups); ?>;

	$(document).ready(function(){
		<?php
		
		if($this->session->flashdata('error')){
			echo("alert('".$this->session->flashdata('error')."');"); 
		}
		
		if(isset($error) && $error != '') {
			echo("alert('".$error."');"); 
		}
		
		?>
		
	    $("#TxtEmployee").autocomplete({
	      source: employees
	    });			

	    $('#TxtEmployee').on('change', function() {
	      var Emp = $("#TxtEmployee").val();
	      var sArray = Emp.split(" - ");
	      $("#EmployeeName").val(sArray[0]);
	      $("#EmployeeID").val(sArray[2]);
	      $("#UserID").val(sArray[1]);
	    });
		
		
		

		$("#btnSubmit").click(function(){
			$("#FormUser").submit();
		});

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

		$("#tblDetailRole").on('change','.role',function(){
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
		$("#rowEmployee").show();

		$("#group-form-zen").hide();
		$('#rowZen input[name="zen_status"]').change(function() {
			var isChecked = $("#rowZen input[name='zen_status']").prop("checked");
			if (isChecked==true) {
				$("#group-form-zen").show();
			} else {
				$("#group-form-zen").hide();
			}
		});
		$("#rowZen input[name='btn_tarik']").click(function(){
			$("#loading-spinner").css({"display":"inline-block"});
			var url = "<?=base_url()?>usercontrollers/ViewUser";
			var idKaryawan = $("#rowZen input[name='zen_UserID']").val();
			var formData = new FormData();
			formData.append("is_get_user",1);
			formData.append("zen_UserID",idKaryawan);
			$.ajax({
				type: "POST",
				url: url,
				data: formData,
				processData: false,
			    contentType: false,
			    cache: false,
				success: function(data){
					$("#loading-spinner").css({"display":"none"});
					var json = JSON.parse(data);
					console.log(json);
					var id = json.data.USERID;
					var email = json.data.USEREMAIL;
					var nama = json.data.NAME;
					var cabang = json.data.BRANCHNAME;
					var grupKerja = json.data.GROUPNAME;
					var levelKaryawan = json.data.EMPLEVEL;
					var statusKaryawan = json.data.EMPTYPE;

					$("#rowZen input[name='zen_USEREMAIL']").val(email);
					$("#rowZen input[name='zen_NAME']").val(nama);
					$("#rowZen input[name='zen_BRANCHNAME']").val(cabang);
					$("#rowZen input[name='zen_GROUPNAME']").val(grupKerja);
					$("#rowZen input[name='zen_EMPLEVEL']").val(levelKaryawan);
					$("#rowZen input[name='zen_EMPTYPE']").val(statusKaryawan);
				},
				error: function(jqXHR, textStatus, errorThrown){
					console.log("error request_get");
					$("#loading-spinner").css({"display":"none"});
				}
			});
		});
	});
	
</script>

<style>
	fieldset { border:1px solid #ccc; padding:15px!important; border-radius:10px; margin-top:10px; margin-bottom:10px; }
	#rowZen { background-color: #e1f25c; }
	#lblkaryawan { color: #3c9902; }
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

<?php echo form_open('UserControllers/Edit'); ?>
<div class="container">
	<?php if ($edit_mode==1) { ?>
	<div class="form_title">EDIT USER</div>
	<?php } else { ?>
	<div class="form_title">VIEW USER</div>
	<?php } ?>
	<div class="clearfix"></div>
	<div class="manualForm">
		<fieldset>
			<?php if ($dataUpdated) { ?>
			<div class="row">
				<div class="col-4 col-m-5"></div>
				<div class="col-8 col-m-7">
					<div style="background-color:yellow;font-size:15px;font-weight:bold;">DATA USER INI BARU SAJA DIUPDATE DENGAN DATA DARI ZEN</div>
				</div>
			</div>
			<?php } ?>
			<?php if ($user->USERID==0) { ?>
			<?php } else { ?>
			<div class="row">
				<div class="col-4 col-m-5">USERID</div>
				<div class="col-8 col-m-7">
					<?php $attr = array('class'=>'user_input', 'id' => 'UserID', 'readonly'=>true);
					echo BuildInput('text','UserID',$attr,$user->USERID); ?>
				</div>
			</div>			
			<?php } ?>
			<div class="row">
				<div class="col-4 col-m-5">USEREMAIL</div>
				<div class="col-8 col-m-7">
					<?php $attr = array('class'=>'user_input', 'id' => 'UserEmail', 'readonly'=>true);
					echo BuildInput('text','UserEmail',$attr,$user->UserEmail); ?>
				</div>
			</div>
			<?php if ($loginChanged) { ?>
			<div class="row">
				<div class="col-4 col-m-5"></div>
				<div class="col-8 col-m-7">
					<font color="blue" style="background-color:yellow;">LOGIN USER INI BERUBAH DARI <b><?php echo($oldLogin);?></b> MENJADI <b><?php echo($newLogin);?></b></font>
				</div>
			</div>
			<?php } ?>
			<div class="row">
				<div class="col-4 col-m-5">NAMA</div>
				<div class="col-8 col-m-7">
					<?php $attr = array('class'=>'user_input', 'id' => 'UserName', 'readonly'=>true);
					echo BuildInput('text','UserName',$attr,$user->UserName); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-4 col-m-5">Cabang</div>
				<div class="col-8 col-m-7">
					<?php $attr = array('class'=>'user_input', 'id'=>'Branch', 'readonly'=>true);
					echo BuildInput('text','Branch',$attr,$user->BranchName); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-4 col-m-5">Grup Kerja</div>
				<div class="col-8 col-m-7">
					<?php $attr = array('class'=>'user_input', 'id'=>'Group', 'readonly'=>true);
					echo BuildInput('text','Group',$attr,$user->GroupName); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-4 col-m-5">Level Karyawan</div>
				<div class="col-8 col-m-7">
					<?php $attr = array('class'=>'user_input', 'id'=>'EmpLevel', 'readonly'=>true);
					echo BuildInput('text','EmpLevel',$attr,$user->EmpLevel); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-4 col-m-5">Status Karyawan</div>
				<div class="col-8 col-m-7">
					<?php $attr = array('class'=>'user_input', 'id'=>'EmpType', 'readonly'=>true);
					echo BuildInput('text','EmpType',$attr,$user->EmpType); ?>
				</div>
			</div>	
		</fieldset>
		
		<fieldset id="fieldsetMapping">		
			<?php //if ($user->USERID==0) {
			$EmployeeName = '';
			$EmployeeID = '';
			$UserID = '';
			$TxtEmployee = '';
			if($user->USERID>0){
				$EmployeeName = $user->UserName;
				$EmployeeID = $user->UserEmail;
				$UserID = $user->UserID;
				$TxtEmployee = $user->UserName.' - '.$user->UserID.' - '.$user->UserEmail;
			}
			
			?>
			<div class="row">
				<div class="col-4 col-m-5"></div> 
				<div class="col-8 col-m-6"></div>
			</div>		
			<div class="row" id="rowEmployee">
				<div class="col-4 col-m-5" id="lblkaryawan">Karyawan</div>
				<div class="col-8 col-m-7">
					<input type="text" class="form-control" name="TxtEmployee" disabled value="<?php echo $TxtEmployee ?>" id="TxtEmployee" placeholder="Ketik Nama Karyawan" style="display:none;">
					<input type="text" class="form-control" name="EmployeeName" value="<?php echo $EmployeeName ?>" id="EmployeeName" placeholder="Nama Karyawan" style="width:30%;">
					<input type="text" class="form-control" name="EmployeeID" value="<?php echo $EmployeeID ?>" id="EmployeeID" placeholder="UserEmail Karyawan" style="width:30%;">
					<input type="text" class="form-control" name="UserID" value="<?php echo $UserID ?>" id="UserID" placeholder="ID Karyawan" style="width:20%;">
				</div>
			</div>						
			<?php //} ?>
		</fieldset>

		<?php 
		if ($edit_mode==1)
		{
			?>
				<fieldset>
					<div class="row" id="rowZen">
						<div class="col-4 col-m-5">Tarik data Zen</div>
						<div class="col-8 col-m-7">
							<input type="checkbox" name="zen_status">
						</div>
						<div id="group-form-zen">
							<div class="col-4 col-m-5">ID Karyawan</div>
							<div class="col-8 col-m-7">
								<input type="text" class="form-control" name="zen_UserID" value="" placeholder="ID Karyawan" style="width:20%;display: inline-block;">
								<input type="button" name="btn_tarik" value="Tarik"><span id="loading-spinner" style="display:none;animation: loading 1s linear infinite;margin-left: 5px;width:20px;"><img style="width: 100%;" src="<?=base_url()?>images/spinner.png"></span>
							</div>
						
							<div class="col-4 col-m-5">USEREMAIL</div>
							<div class="col-8 col-m-7">
								<input type="text" class="form-control" name="zen_USEREMAIL" value=""  placeholder="USEREMAIL" style="width:30%;" onfocus="this.blur();" >
							</div>
							<div class="col-4 col-m-5">NAMA</div>
							<div class="col-8 col-m-7">
								<input type="text" class="form-control" name="zen_NAME" value=""  placeholder="NAMA" style="width:30%;" onfocus="this.blur();" >
							</div>
							<div class="col-4 col-m-5">CABANG</div>
							<div class="col-8 col-m-7">
								<input type="text" class="form-control" name="zen_BRANCHNAME" value=""  placeholder="Cabang" style="width:30%;" onfocus="this.blur();" readonly>
							</div>
							<div class="col-4 col-m-5">Grup Kerja</div>
							<div class="col-8 col-m-7">
								<input type="text" class="form-control" name="zen_GROUPNAME" value=""  placeholder="Grup Kerja" style="width:30%;" onfocus="this.blur();" readonly>
							</div>
							<div class="col-4 col-m-5">Level Karyawan</div>
							<div class="col-8 col-m-7">
								<input type="text" class="form-control" name="zen_EMPLEVEL" value=""  placeholder="Level Karyawan" style="width:30%;" onfocus="this.blur();" readonly>
							</div>
							<div class="col-4 col-m-5">Status Karyawan</div>
							<div class="col-8 col-m-7">
								<input type="text" class="form-control" name="zen_EMPTYPE" value=""  placeholder="Status Karyawan" style="width:30%;" onfocus="this.blur();" readonly>
							</div>
						</div>
					</div>

				</fieldset>
			<?php
		} 
		?>

		<?php $GroupCuti = $user->GroupID; ?>
		<fieldset>		
			<div class="row">
				<div class="col-4 col-m-5"><b>Grup Kerja bhakti.co.id</b></div>
				<div class="col-8 col-m-7">
					<select id="GroupID" name="GroupID" class="user_input" <?php echo(($edit_mode==0)?"disabled":"");?>>
						<option value=''>--Pilih Grup Kerja--</option>
						<?php 
							foreach($groups as $g) {
								echo("<option value='".$g->GroupID."'".(($g->GroupID==$GroupCuti)?" selected":"").">".$g->Name."</option>");
							}
						?>
					</select>
				</div>
			</div>
			<div class="row">
				<div class="col-4 col-m-5"><b>AKTIF</b></div>
				<div class="col-1 col-m-1">
				<?php
					if ($edit_mode==0){
						echo BuildInput('checkbox','IsActive',array('id'=>'IsActive','onclick'=>'return false;','checked'=>$user->IsActive=='1'),'1');	
					}
					else {
						echo BuildInput('checkbox','IsActive',array('id'=>'IsActive','checked'=>$user->IsActive=='1'),'1');
					}					
				?>
				</div>
				<div class="col-7 col-m-6"></div>
			</div>		
			<div class="row">
				<div class="col-4 col-m-5"><b>Email Surat-Menyurat</b></div>
				<div class="col-8 col-m-7">
					<?php $attr = array('class'=>'user_input', 'id'=>'Email');
					echo BuildInput('text','Email', $attr, $user->Email); ?>
				</div>
			</div>			
			<div class="row">
				<div class="col-4 col-m-5"><b>No Whatsapp (Diawali 62)</b></div>
				<div class="col-8 col-m-7">
					<?php $attr = array('class'=>'user_input', 'id'=>'Whatsapp', 'placeholder'=>"6281XXXXXXX [tanpa spasi dan tanda baca]");
					echo BuildInput('text','Whatsapp', $attr, $user->Whatsapp); ?>
				</div>
			</div>			
		</fieldset>

		<fieldset>
			<h3>ROLE USER</h3>

			<?php 
				$btnADD = "";
				if ($edit_mode==1) $btnADD = "<button type='button' id='btnAddRole' style='color:black;'>TAMBAH</button>";
			?>
			<div id="tblDetailRole">
				<div id="tblDetailRoleHead">
					<div class="row">
						<div class="col_header" style="width:10%;float:left;"><?php echo($btnADD);?></div>
						<div class="col_header col_header_text colRole"  style="float:left;">ROLE</div>
						<div class="col_header col_header_text hideOnMobile" style="width:20%;float:left;">KODE ROLE</div>
						<div class="col_header col_header_text hideOnMobile" style="width:30%;float:left;">NAMA ROLE</div>
					</div>
				</div>
				<div id="tblDetailRoleBody">
					<?php
					if(isset($userRole))
					{
						foreach($userRole as $ur)
						{
							$btnDel = "";
							if ($edit_mode==1) $btnDel = "<button type='button' class='btnDelete btnDeleteRole'><i class='glyphicon glyphicon-remove'></i></button>";
						?>
							<div class="row">
								<div class="col_detail" style="width:10%;float:left;"><?php echo($btnDel);?></div>
								<div class="col_detail colRole" style="float:left;">
									<select name="role[]" id="role[]" class="role" style="width:90%;" <?php echo(($edit_mode==0)?"disabled":"");?>>
										<option value="">Choose Role</option>
										<?php foreach($roles as $r) { ?>
										<option value="<?php echo($r->role_id)?>"<?php echo(($r->role_id==$ur->role_id)?" selected":"")?>><?php echo($r->role_name)?></option>
										<?php } ?>
									</select>
								</div>
								<div class="col_detail hideOnMobile" style="width:20%;float:left;"><?php echo BuildInput('text','RoleId[]',array('style'=>"width:90% !important;",'readonly'=>"true", 'class'=>"RoleId"), $ur->role_id);	?></div>	
								<div class="col_detail hideOnMobile" style="width:30%;float:left;"><?php echo BuildInput('text','RoleName[]',array('style'=>"width:90% !important;",'readonly'=>"true", 'class'=>"RoleName"), $ur->role_name);	?></div>
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
				</div>
			</div>
		</fieldset>
	</div>
</div>
</div>
	<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;padding:5px;">
		<?php if ($edit_mode==1) {?><button type="submit" class="btn btnSubmit" id="btnSubmit" name="btnSubmit" align="right">SIMPAN</button><?php } ?>
		<?php if ($edit_mode==1) { ?>
		<a href="<?php echo site_url('UserControllers'); ?>"><button type="button" class="btn btnCancel" align="right">BATAL</button></a>
		<?php } else { ?> 
		<a href="<?php echo site_url('UserControllers'); ?>"><button type="button" class="btn btnCancel" align="right">KEMBALI</button></a>
		<?php } ?>
	</div>
</div>
<?php echo form_close(); ?>

<div id="tblSampleRole" style="display:none;">
	<div class="row" id="rowRole">
		<div class="col_detail" style="width:10%;float:left;">
			<button type="button" class="btnDelete btnDeleteRole"><i class='glyphicon glyphicon-remove'></i></button>
		</div>
		<div class="col_detail colRole" style="float:left;">
			<select name="role[]" id="role[]" class="role" style="width:90%;">
				<option value="">Choose Role</option>
		<?php foreach($roles as $r) { ?>
				<option value="<?php echo($r->role_id)?>"><?php echo($r->role_name)?></option>
		<?php } ?>
			</select>
		</div>	
		<div class="col_detail hideOnMobile" style="width:20%;float:left;">
			<?php echo BuildInput('text','RoleId[]',array('style'=>"width:90% !important;",'readonly'=>"true", 'class'=>"RoleId")); ?>
		</div>	
		<div class="col_detail hideOnMobile" style="width:30%;float:left;">
			<?php echo BuildInput('text','RoleName[]',array('style'=>"width:90% !important;",'readonly'=>"true", 'class'=>"RoleName")); ?>
		</div>
	</div>
</div>

