<script>
	$(document).ready(function(){
		<?php 
		if(isset($error) && $error != '') {
			echo("alert('".$error."');"); 
		}
		
		/*if($edit_mode==1 && $success==0) {
			echo("alert('Rubah User Gagal, Ada Error!');");
		} else if ($edit_mode==0 && $success==1) {
			echo("alert('Rubah User Berhasil!');");
		}*/

		?>

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
	});
</script>

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
	<div class="form_title">CHANGE PROFILE</div>
	<div class="clearfix"></div>
	<div class="manualForm">
		<fieldset>
			<div class="row">
				<div class="col-4 col-m-5">USERID</div>
				<div class="col-8 col-m-7">
					<?php $attr = array('class'=>'user_input', 'id' => 'UserID', 'readonly'=>true);
					echo BuildInput('text','UserID',$attr,$user->USERID); ?>
				</div>
			</div>			
			<div class="row">
				<div class="col-4 col-m-5">USEREMAIL</div>
				<div class="col-8 col-m-7">
					<?php $attr = array('class'=>'user_input', 'id' => 'UserEmail', 'readonly'=>true);
					echo BuildInput('text','UserEmail',$attr,$user->UserEmail); ?>
				</div>
			</div>
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
<!-- 			<div class="row">
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
 -->			
 			<?php 
				$GroupCuti = $user->GroupID;
			?>
			<div class="row">
				<div class="col-4 col-m-5"><b>Grup Kerja bhakti.co.id</b></div>
				<div class="col-8 col-m-7">
					<?php $attr = array('class'=>'user_input', 'id'=>'GroupID', 'readonly'=>true);
					echo BuildInput('text','GroupID', $attr,$GroupCuti); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-4 col-m-5"><b>AKTIF</b></div>
				<div class="col-1 col-m-1">
				<?php
					echo BuildInput('checkbox','IsActive',array('id'=>'IsActive','onclick'=>'return false;','checked'=>$user->IsActive=='1'),'1');	
				?>
				</div>
				<div class="col-7 col-m-6"></div>
			</div>		
		</fieldset>
		<fieldset>
			<?php echo form_open('UserControllers/SaveProfile'); ?>
			<div class="row">
				<div class="col-4 col-m-5">Email Surat-Menyurat</div>
				<div class="col-8 col-m-7">
					<?php $attr = array('class'=>'user_input', 'id'=>'Email');
					echo BuildInput('text','Email', $attr, $user->Email); ?>
				</div>
			</div>			
			<div class="row">
				<div class="col-4 col-m-5">No Whatsapp (Diawali 62)</div>
				<div class="col-8 col-m-7">
					<?php $attr = array('class'=>'user_input', 'id'=>'Whatsapp', 'placeholder'=>"6281XXXXXXX [tanpa spasi dan tanda baca]");
					echo BuildInput('text','Whatsapp', $attr, $user->Whatsapp); ?>
				</div>
			</div>			
			<div class="row">
				<div class="col-4 col-m-5"></div>
				<div class="col-8 col-m-7">
					<button type="submit" id="btnSubmit" name="btnSubmit">SUBMIT</button>
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
