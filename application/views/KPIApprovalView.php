<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
</script>
<style>
	.modal-lg{
	width:90%;
	margin: 30px auto;
	}
	
	#modal_edit .modal-body{
	/*font-size:80% !important;*/
	}
	.form-group{
	margin-bottom:0;
	}
	.form-horizontal .control-label{
	text-align:left;
	}
	.float-right{
	float:right;
	}
	.color-red{
	color:red;
	}
	.font-sm{
	font-size:80% !important;
	}
	.disabled:disabled {
	background: #dddddd;
	}
	
	#output-container {
	  column-count: 4;
	  column-gap: 10px;
	}
</style>

<div class="container">
	<div class="form_title">
		<div style="text-align: center;">
			MASTER KPI APPROVAL
		</div>
	</div>
	<div class="row">
		<div class="col-3">
			<label>KPI Category :</label>
			<select id="filter_kpi_kategori" class="form-control" onchange="javascript:filter_kpi_kategori()" style="width:100%">
				<option value="">ALL</option>
				<?php
					foreach($KPICategory as $r) {
						echo "<option value='".$r->KPICategoryName."'>".$r->KPICategoryName."</option>";
					}
				?>
			</select>
		</div>
		<div class="col-3">
			<label>WILAYAH SALESMAN :</label>
			<select id="filter_wilayah_salesman" class="form-control" onchange="javascript:filter_wilayah_salesman()" style="width:100%">
				<option value="">ALL</option>
				<?php
					foreach($KPIApprovalWilayahSalesman as $r) {
						echo "<option value='".$r->wilayah."'>".$r->wilayah."</option>";
					}
				?>
			</select>
		</div>
		<div class="col-3">
			<label>KODE SALESMAN :</label>
			<select id="filter_kode_salesman" class="form-control" onchange="javascript:filter_kode_salesman()" style="width:100%">
				<option value="">ALL</option>
				<?php
					foreach($KPIApprovalSalesman as $r) {
						echo "<option value='".$r->Kd_Slsman."'>".$r->Kd_Slsman." ".$r->Nm_Slsman."</option>";
					}
				?>
			</select>
		</div>
		<?php if($_SESSION["can_create"] == 1) { ?>
		<div class="col-3">
			<button type="button" id="btn-add" class="btn btn-dark" style="float:right">Add New</button>
		</div>
		<?php } ?>
	</div>
	
	<div class="row">
		<div class="col-12">
			<table id="table" class="table table-bordered" summary="table">
				<thead>
					<tr>
						<th scope="col">START DATE</th>
						<th scope="col">KPI CATEGORY</th>
						<th scope="col">WILAYAH SALESMAN</th>
						<th scope="col">KODE SALESMAN</th>
						<th scope="col">NAMA SALESMAN</th>
						<th scope="col">AKTIF</th>
						<?php if($_SESSION["can_update"] == 1) { ?>
						<th scope="col" class="no-sort">EDIT</th>
						<?php } ?>
					</tr>
				</thead>
				<tbody>
					<?php
						$no = 0;
						foreach($KPIApproval as $r){
							$no++;
							$data = '';
							$data .=' data-StartDate="'.date('d-M-Y',strtotime($r->StartDate)).'"';
							$data .=' data-KPICategory="'.$r->KPICategory.'"';
							$data .=' data-WilayahSalesman="'.$r->WilayahSalesman.'"';
							$data .=' data-KodeSalesman="'.$r->KodeSalesman.'"';
							$data .=' data-TargetApprovalNeeded="'.$r->TargetApprovalNeeded.'"';
							$data .=' data-TargetApprovalBy="'.$r->TargetApprovalBy.'"';
							$data .=' data-TargetApprovalNotification="'.$r->TargetApprovalNotification.'"';
							$data .=' data-AchievementApprovalNeeded="'.$r->AchievementApprovalNeeded.'"';
							$data .=' data-AchievementApprovalBy="'.$r->AchievementApprovalBy.'"';
							$data .=' data-AchievementApprovalNotification="'.$r->AchievementApprovalNotification.'"';
							$data .=' data-AchievementMaxPercentage="'.$r->AchievementMaxPercentage.'"';
							$data .=' data-IsActive="'.$r->IsActive.'"';
							$data .=' data-ModifiedBy="'.$r->ModifiedBy.'"';
							$data .=' data-ModifiedDate="'.$r->ModifiedDate.'"';
							$data .=' data-StartTargetInputDate="'.$r->StartTargetInputDate.'"';
							$data .=' data-StartTargetInputMonth="'.$r->StartTargetInputMonth.'"';
							$data .=' data-DeadlineTargetInputDate="'.$r->DeadlineTargetInputDate.'"';
							$data .=' data-DeadlineTargetInputMonth="'.$r->DeadlineTargetInputMonth.'"';
							$data .=' data-DeadlineTargetApprovalDate="'.$r->DeadlineTargetApprovalDate.'"';
							$data .=' data-DeadlineTargetApprovalMonth="'.$r->DeadlineTargetApprovalMonth.'"';
							$data .=' data-DeadlineTargetApprovalBy="'.$r->DeadlineTargetApprovalBy.'"';
							$data .=' data-DeadlineTargetApprovalNotification="'.$r->DeadlineTargetApprovalNotification.'"';
							$data .=' data-DeadlineAchievementInputDate="'.$r->DeadlineAchievementInputDate.'"';
							$data .=' data-DeadlineAchievementInputMonth="'.$r->DeadlineAchievementInputMonth.'"';
							$data .=' data-DeadlineAchievementApprovalDate="'.$r->DeadlineAchievementApprovalDate.'"';
							$data .=' data-DeadlineAchievementApprovalMonth="'.$r->DeadlineAchievementApprovalMonth.'"';
							$data .=' data-DeadlineAchievementApprovalBy="'.$r->DeadlineAchievementApprovalBy.'"';
							$data .=' data-DeadlineAchievementApprovalNotification="'.$r->DeadlineAchievementApprovalNotification.'"';
							$data .=' data-StartAchievementInputDate="'.$r->StartAchievementInputDate.'"';
							$data .=' data-StartAchievementInputMonth="'.$r->StartAchievementInputMonth.'"';
							$checked = ($r->IsActive==1) ? "checked" : "";
						?>
						<tr>
							<td><?php echo date('d-M-Y',strtotime($r->StartDate)) ?></td>
							<td><?php echo $r->KPICategory ?></td>
							<td><?php echo $r->WilayahSalesman ?></td>
							<td><?php echo $r->KodeSalesman ?></td>
							<td><?php echo $r->Nm_Slsman ?></td>
							<td><input type="checkbox" <?php echo $checked ?> onclick="return false"></td>
							<?php if($_SESSION["can_update"] == 1) { ?>
							<td><?php echo '<button class="btn-edit btn btn-sm btn-dark" '.$data.'><i class="glyphicon glyphicon-pencil"></i></button>' ?></td>
							<?php } ?>
						</tr>
						<?php
							
							
						}
					?>
					
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_edit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form id="myform" class="form-horizontal" action="<?php echo site_url('KPI/KPIApprovalSave') ?>" method="POST">
				<div class="modal-header form_title">
					<div style="text-align:center;">
						MASTER KPI APPROVAL
					</div>
				</div>
				<div class="modal-body">
					<div class="col-2">
						<label>Start Date</label>
						<input type="text" name="StartDate" id="StartDate" class="form-control disabled datepicker " autocomplete="off" readonly required>
					</div>
					<div class="col-3">
						<label>KPI Category</label>
						<select name="KPICategory" id="KPICategory" class="form-control disabled" required>
							<option value=""></option>
							<?php
								foreach($KPICategory as $r) {
									echo "<option value='".$r->KPICategoryName."'>".$r->KPICategoryName."</option>";
								}
							?>
						</select>
					</div>
					<div class="col-3">
						<label>Wilayah Salesman</label>
						<select name="WilayahSalesman" id="WilayahSalesman" class="form-control disabled" required>
							<option value="ALL">ALL</option>
							<?php
								foreach($KPIApprovalWilayahSalesman as $r) {
									echo "<option value='".$r->wilayah."'>".$r->wilayah."</option>";
								}
							?>
						</select>
					</div>
					<div class="col-3">
						<label>Nama Salesman</label>
						<select name="KodeSalesman" id="KodeSalesman" class="form-control disabled" required>
							<option value="ALL">ALL</option>
							<?php
								foreach($KPIApprovalSalesman as $r) {
									echo "<option value='".$r->Kd_Slsman."'>".$r->Kd_Slsman." ".$r->Nm_Slsman."</option>";
								}
							?>
						</select>
					</div>
					<div class="col-1">
						<label>Aktif</label>
						<br>
						<input type="checkbox" name="IsActive" id="IsActive" value="1">
					</div>
					
					
					<div class="col-6">
						<div class="form-group">
							<label class="col-6 control-label">Target Approval Needed</label>
							<div class="col-2">
								<input type="number" name="TargetApprovalNeeded" id="TargetApprovalNeeded" class="form-control" required>
							</div>
						</div>
						<div class="form-group">
							<label class="col-6 control-label">Target ApprovalBy <button type="button" class="float-right" onclick="browseJob('TargetApprovalBy')">+</button></label>
							<div class="col-6">
								<textarea name="TargetApprovalBy" id="TargetApprovalBy" class="form-control" required readonly></textarea>
							</div>
						</div>
						
						
						<div class="form-group">
							<label class="col-6 control-label">Target Approval Notification <button type="button" class="float-right" onclick="browseJob('TargetApprovalNotification')">+</button></label>
							<div class="col-6">
								<textarea name="TargetApprovalNotification" id="TargetApprovalNotification" class="form-control" required readonly></textarea>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-7 control-label">Start Target Input ( Date | Month )</label>
							<div class="col-2">
								<input type="number" name="StartTargetInputDate" id="StartTargetInputDate" class="form-control" required>
							</div>
							<div class="col-2">
								<input type="number" name="StartTargetInputMonth" id="StartTargetInputMonth" class="form-control" required>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-7 control-label">Deadline Target Input ( Date | Month )</label>
							<div class="col-2">
								<input type="number" name="DeadlineTargetInputDate" id="DeadlineTargetInputDate" class="form-control" required>
							</div>
							<div class="col-2">
								<input type="number" name="DeadlineTargetInputMonth" id="DeadlineTargetInputMonth" class="form-control" required>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-7 control-label">Deadline Approval ( Date | Month )</label>
							<div class="col-2">
								<input type="number" name="DeadlineTargetApprovalDate" id="DeadlineTargetApprovalDate" class="form-control" required>
							</div>
							<div class="col-2">
								<input type="number" name="DeadlineTargetApprovalMonth" id="DeadlineTargetApprovalMonth" class="form-control" required>
							</div>
						</div>
						
						
						<div class="form-group">
							<label class="col-6 control-label color-red">Target ApprovalBy<br>(Jika Melewati Deadline) <button type="button" class="float-right" onclick="browseJob('DeadlineTargetApprovalBy')">+</button></label>
							<div class="col-6">
								<textarea name="DeadlineTargetApprovalBy" id="DeadlineTargetApprovalBy" class="form-control" required readonly></textarea>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-6 control-label color-red">Target Approval Notification<br>(Jika Melewati Deadline) <button type="button" class="float-right" onclick="browseJob('DeadlineTargetApprovalNotification')">+</button></label>
							<div class="col-6">
								<textarea name="DeadlineTargetApprovalNotification" id="DeadlineTargetApprovalNotification" class="form-control" required readonly></textarea>
							</div>
						</div>
					</div>
					<div class="col-6">
						<div class="form-group">
							<label class="col-6 control-label">Achievement Approval Needed</label>
							<div class="col-2">
								<input type="number" name="AchievementApprovalNeeded" id="AchievementApprovalNeeded" class="form-control">
							</div>
							<label class="col-2 control-label">Acv. Max %</label>
							<div class="col-2">
								<input type="number" name="AchievementMaxPercentage" id="AchievementMaxPercentage" class="form-control" required>
							</div>
						</div>
						<div class="form-group">
							<label class="col-6 control-label">Achievement ApprovalBy <button type="button" class="float-right" onclick="browseJob('AchievementApprovalBy')">+</button></label>
							<div class="col-6">
								<textarea name="AchievementApprovalBy" id="AchievementApprovalBy" class="form-control" required readonly></textarea>
							</div>
						</div>
						
						
						<div class="form-group">
							<label class="col-6 control-label">Achievement Approval Notification <button type="button" class="float-right" onclick="browseJob('AchievementApprovalNotification')">+</button></label>
							<div class="col-6">
								<textarea name="AchievementApprovalNotification" id="AchievementApprovalNotification" class="form-control" required readonly></textarea readonly>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-7 control-label">Start Achievement Input ( Date | Month )</label>
							<div class="col-2">
								<input type="number" name="StartAchievementInputDate" id="StartAchievementInputDate" class="form-control" required>
							</div>
							<div class="col-2">
								<input type="number" name="StartAchievementInputMonth" id="StartAchievementInputMonth" class="form-control" required>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-7 control-label">Deadline Achievement Input ( Date | Month )</label>
							<div class="col-2">
								<input type="number" name="DeadlineAchievementInputDate" id="DeadlineAchievementInputDate" class="form-control" required>
							</div>
							<div class="col-2">
								<input type="number" name="DeadlineAchievementInputMonth" id="DeadlineAchievementInputMonth" class="form-control" required>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-7 control-label">Deadline Approval ( Date | Month )</label>
							<div class="col-2">
								<input type="number" name="DeadlineAchievementApprovalDate" id="DeadlineAchievementApprovalDate" class="form-control" required>
							</div>
							<div class="col-2">
								<input type="number" name="DeadlineAchievementApprovalMonth" id="DeadlineAchievementApprovalMonth" class="form-control" required>
							</div>
						</div>
						<div class="form-group">
							<label class="col-6 control-label color-red">Achievement ApprovalBy<br>(Jika Melewati Deadline) <button type="button" class="float-right" onclick="browseJob('DeadlineAchievementApprovalBy')">+</button></label>
							<div class="col-6">
								<textarea name="DeadlineAchievementApprovalBy" id="DeadlineAchievementApprovalBy" class="form-control" required readonly></textarea>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-6 control-label color-red">Achievement Approval Notification<br>(Jika Melewati Deadline) <button type="button" class="float-right" onclick="browseJob('DeadlineAchievementApprovalNotification')">+</button></label>
							<div class="col-6">
								<textarea name="DeadlineAchievementApprovalNotification" id="DeadlineAchievementApprovalNotification" class="form-control" required readonly></textarea>
							</div>
						</div>
					</div>
					<div style="clear:both"></div>
				</div>
				<div class="modal-footer">
					<div class="col-12">
						<small id="LastModified" style="float:left;text-align:left"></small>
						<button type="button" id="btn_duplicate" class="btn btn-dark btn-ok" onclick="javascript:duplikasi()">DUPLICATE</button>
						<button type="button" id="btn_delete" class="btn btn-danger-dark btn-ok" onclick="javascript:KPIApprovalDelete()">DELETE</button>
						<button type="submit" class="btn btn-dark btn-ok">SAVE</button>
						<button type="button" class="btn btn-dark" data-dismiss="modal">CLOSE</button>
					</div>
				</div>
				
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_job" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form id="myform" class="form-horizontal" action="<?php echo site_url('KPI/Save') ?>" method="POST" target="_blank">
				<input type="hidden" name="KPICode" id="KPICode">
				<div class="modal-header form_title">
					<div style="text-align:center;">
						BROWSE JOB
					</div>
				</div>
				<div class="modal-body">
					<input type="hidden" id="divJob">
					<!--table id="table-job" class="table table-bordered">
						<thead>
							<tr>
								<th>JOB</th>
								<th width="5px">Pilih</th>
							</tr>
						</thead>
						<tbody>
							<?php
								// foreach($KPIApprovalEmailJob as $r) {
									// echo "<tr><td>".$r->Job."</td><td align='center'><input type='checkbox' class='chk_job' value='".$r->Job."'></td></tr>";
								// }
							?>
						</tbody>
					</table-->
					
					<div id="output-container">
					<?php
						foreach($KPIApprovalEmailJob as $r) {
							echo "<input type='checkbox' class='chk_job' value='".$r->Job."'> ".$r->Job."<br>";
						}
					?>
					</div>
					
				</div>
				<div class="modal-footer">
					<div class="col-12">
						<small id="LastModified" style="float:left;text-align:left"></small>
						
						<button type="button" class="btn btn-danger-dark btn-ok" onclick="javascript:pilihJob()">PILIH</button>
						<button type="button" class="btn btn-dark" data-dismiss="modal">CLOSE</button>
						<div id="result"></div>
					</div>
				</div>
				
			</form>
		</div>
	</div>
</div>

<script>
	$(document).ready(function() {
	    t = $('#table').DataTable({
			"pageLength"    : 10,
			"searching"     : true,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false }
			],
			"dom": '<"top">rt<"bottom"ip><"clear">',
			"order": [[1, 'desc']],
		});
		
		$("#myform").submit(function() {
			$('.disabled').removeAttr("disabled");
			$('.loading').show();
			var act = $(this).attr('action');
			var data = new FormData(this);
			$.ajax({
				data      	: data,
				url			: act,
				cache		: false,
				contentType	: false,
				processData	: false,
				type		: 'POST',
    			dataType  : 'json',
				success   : function(msg) {
					// console.log(data);
					$('.loading').hide();
					if(msg.result=='success'){
						alert('SUCCESS. Data berhasil disimpan');
						location.reload();
					}
					else{
						alert('FAILED. '+msg.error);
					}
				}
			});
			event.preventDefault(); //Prevent the default submit
		});
	});
	
	$(document).on("click", ".btn-edit" , function() {
		var StartDate = $(this).attr('data-StartDate');
		var KPICategory = $(this).attr('data-KPICategory');
		var WilayahSalesman = $(this).attr('data-WilayahSalesman');
		var KodeSalesman = $(this).attr('data-KodeSalesman');
		var TargetApprovalNeeded = $(this).attr('data-TargetApprovalNeeded');
		var TargetApprovalBy = $(this).attr('data-TargetApprovalBy');
		var TargetApprovalNotification = $(this).attr('data-TargetApprovalNotification');
		var AchievementApprovalNeeded = $(this).attr('data-AchievementApprovalNeeded');
		var AchievementApprovalBy = $(this).attr('data-AchievementApprovalBy');
		var AchievementApprovalNotification = $(this).attr('data-AchievementApprovalNotification');
		var AchievementMaxPercentage = $(this).attr('data-AchievementMaxPercentage');
		var IsActive = $(this).attr('data-IsActive');
		var ModifiedBy = $(this).attr('data-ModifiedBy');
		var ModifiedDate = $(this).attr('data-ModifiedDate');
		var StartTargetInputDate = $(this).attr('data-StartTargetInputDate');
		var StartTargetInputMonth = $(this).attr('data-StartTargetInputMonth');
		var DeadlineTargetInputDate = $(this).attr('data-DeadlineTargetInputDate');
		var DeadlineTargetInputMonth = $(this).attr('data-DeadlineTargetInputMonth');
		var DeadlineTargetApprovalDate = $(this).attr('data-DeadlineTargetApprovalDate');
		var DeadlineTargetApprovalMonth = $(this).attr('data-DeadlineTargetApprovalMonth');
		var DeadlineTargetApprovalBy = $(this).attr('data-DeadlineTargetApprovalBy');
		var DeadlineTargetApprovalNotification = $(this).attr('data-DeadlineTargetApprovalNotification');
		var DeadlineAchievementInputDate = $(this).attr('data-DeadlineAchievementInputDate');
		var DeadlineAchievementInputMonth = $(this).attr('data-DeadlineAchievementInputMonth');
		var DeadlineAchievementApprovalDate = $(this).attr('data-DeadlineAchievementApprovalDate');
		var DeadlineAchievementApprovalMonth = $(this).attr('data-DeadlineAchievementApprovalMonth');
		var DeadlineAchievementApprovalBy = $(this).attr('data-DeadlineAchievementApprovalBy');
		var DeadlineAchievementApprovalNotification = $(this).attr('data-DeadlineAchievementApprovalNotification');
		var StartAchievementInputDate = $(this).attr('data-StartAchievementInputDate');
		var StartAchievementInputMonth = $(this).attr('data-StartAchievementInputMonth');
		
		$('#StartDate').val(StartDate);
		$('#KPICategory').val(KPICategory);
		$('#WilayahSalesman').val(WilayahSalesman);
		$('#KodeSalesman').val(KodeSalesman);
		$('#TargetApprovalNeeded').val(TargetApprovalNeeded);
		$('#TargetApprovalBy').val(TargetApprovalBy);
		$('#TargetApprovalNotification').val(TargetApprovalNotification);
		$('#AchievementApprovalNeeded').val(AchievementApprovalNeeded);
		$('#AchievementApprovalBy').val(AchievementApprovalBy);
		$('#AchievementApprovalNotification').val(AchievementApprovalNotification);
		$('#AchievementMaxPercentage').val(AchievementMaxPercentage);
		$('#IsActive').prop('checked', (IsActive==1)?true:false);
		$('#ModifiedBy').val(ModifiedBy);
		$('#ModifiedDate').val(ModifiedDate);
		$('#StartTargetInputDate').val(StartTargetInputDate);
		$('#StartTargetInputMonth').val(StartTargetInputMonth);
		$('#DeadlineTargetInputDate').val(DeadlineTargetInputDate);
		$('#DeadlineTargetInputMonth').val(DeadlineTargetInputMonth);
		$('#DeadlineTargetApprovalDate').val(DeadlineTargetApprovalDate);
		$('#DeadlineTargetApprovalMonth').val(DeadlineTargetApprovalMonth);
		$('#DeadlineTargetApprovalBy').val(DeadlineTargetApprovalBy);
		$('#DeadlineTargetApprovalNotification').val(DeadlineTargetApprovalNotification);
		$('#DeadlineAchievementInputDate').val(DeadlineAchievementInputDate);
		$('#DeadlineAchievementInputMonth').val(DeadlineAchievementInputMonth);
		$('#DeadlineAchievementApprovalDate').val(DeadlineAchievementApprovalDate);
		$('#DeadlineAchievementApprovalMonth').val(DeadlineAchievementApprovalMonth);
		$('#DeadlineAchievementApprovalBy').val(DeadlineAchievementApprovalBy);
		$('#DeadlineAchievementApprovalNotification').val(DeadlineAchievementApprovalNotification);
		$('#StartAchievementInputDate').val(StartAchievementInputDate);
		$('#StartAchievementInputMonth').val(StartAchievementInputMonth);
		
		$('.disabled').attr("disabled","disabled");
		
		var Modified = '';
		if(ModifiedDate!=''){
			Modified += '<br>Last Modified on '+ModifiedDate+' By '+ModifiedBy;
		}
		$('#LastModified').html(Modified);
		$('#btn_duplicate').show();
		$('#btn_delete').show();
		
		$('#modal_edit').modal('show');
	});
	
	$(document).on("click", "#btn-add" , function() {	
		$('#StartDate').val('');
		$('#KPICategory').val('');
		$('#WilayahSalesman').val('');
		$('#KodeSalesman').val('');
		$('#TargetApprovalNeeded').val('');
		$('#TargetApprovalBy').val('');
		$('#TargetApprovalNotification').val('');
		$('#AchievementApprovalNeeded').val('');
		$('#AchievementApprovalBy').val('');
		$('#AchievementApprovalNotification').val('');
		$('#AchievementMaxPercentage').val('');
		$('#IsActive').prop('checked', true);
		$('#ModifiedBy').val('');
		$('#ModifiedDate').val('');
		$('#StartTargetInputDate').val('');
		$('#StartTargetInputMonth').val('');
		$('#DeadlineTargetInputDate').val('');
		$('#DeadlineTargetInputMonth').val('');
		$('#DeadlineTargetApprovalDate').val('');
		$('#DeadlineTargetApprovalMonth').val('');
		$('#DeadlineTargetApprovalBy').val('');
		$('#DeadlineTargetApprovalNotification').val('');
		$('#DeadlineAchievementInputDate').val('');
		$('#DeadlineAchievementInputMonth').val('');
		$('#DeadlineAchievementApprovalDate').val('');
		$('#DeadlineAchievementApprovalMonth').val('');
		$('#DeadlineAchievementApprovalBy').val('');
		$('#DeadlineAchievementApprovalNotification').val('');
		$('#StartAchievementInputDate').val('');
		$('#StartAchievementInputMonth').val('');
		
		$('.disabled').removeAttr("disabled");
		$('#btn_duplicate').hide();
		$('#btn_delete').hide();
		
		$('#LastModified').html('');
		$('#modal_edit').modal('show');
	});
	
	function filter_kpi_kategori() {
		var kpi_kategori = $('#filter_kpi_kategori').val();
		if(kpi_kategori==''){
			$("#table").dataTable().fnFilter(kpi_kategori, 1);
		}
		else{
			$("#table").dataTable().fnFilter("^"+kpi_kategori+"$", 1, true);
		}
	}
	
	function filter_wilayah_salesman() {
		var wilayah_salesman = $('#filter_wilayah_salesman').val();
		if(wilayah_salesman==''){
			$("#table").dataTable().fnFilter(wilayah_salesman, 2);
		}
		else{
			$("#table").dataTable().fnFilter("^"+wilayah_salesman+"$", 2, true);
		}
	}
	function filter_kode_salesman() {
		var kode_salesman = $('#filter_kode_salesman').val();
		if(kode_salesman==''){
			$("#table").dataTable().fnFilter(kode_salesman, 3);
		}
		else{
			$("#table").dataTable().fnFilter("^"+kode_salesman+"$", 3, true);
		}
	}
	
	function browseJob(div) {
		$('#divJob').val(div);
		var jobs = $('#'+div).val();
		var job = jobs.split(';');
		
		$('.chk_job').prop('checked', false);
		
		for(i=0;i<job.length;i++){
			$('.chk_job').each(function (index, obj) {
				if ($(this).val() == job[i]) {
					$(this).prop('checked', true);
				}
			});
		}
		$('#modal_job').modal('show');
	}
	
	function pilihJob() {
		var div = $('#divJob').val();
		var jobs = '';
		$('.chk_job').each(function (index, obj) {
			if ($(this).is(':checked')) {
				jobs+= $(this).val() +';';
			}
		});
		$('#'+div).val(jobs);
		$('#modal_job').modal('hide');
	}
	
	function duplikasi() {
		$('.disabled').removeAttr("disabled");
	}
	
	function KPIApprovalDelete(){
		var StartDate = $('#StartDate').val();
		var KPICategory = $('#KPICategory').val();
		var WilayahSalesman = $('#WilayahSalesman').val();
		var KodeSalesman = $('#KodeSalesman').val();
		if (confirm('Ingin hapus data ini?')) {
			$('.loading').show();
			$.ajax({ 
				type: 'POST', 
				url: '<?php echo site_url('KPI/KPIApprovalDelete') ?>',  
				data: {
					'StartDate': StartDate,
					'KPICategory': KPICategory,
					'WilayahSalesman': WilayahSalesman,
					'KodeSalesman': KodeSalesman
				}, 
				dataType: 'json',
				success: function (msg) {
					// console.log(msg);
					$('.loading').hide();
					if(msg.result=='success'){
						alert('SUCCESS. Master KPI Approval Berhasil Dihapus!');
						location.reload();
					}
					else{
						alert('FAILED. '+msg.error);
					}
				}
			});
		}
	}
	
	
</script>

