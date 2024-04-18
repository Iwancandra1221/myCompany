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
	
	.nav-tabs, .nav-tabs li{
	background:#fff !important;
	}
	
	.tab-content {
	border-top: 1px solid #555;
	}
	
	.nav-tabs>li.active>a, .nav-tabs>li.active>a:focus, .nav-tabs>li.active>a:hover {
	cursor: default;
	border: 1px solid #555;
	border-bottom-color: transparent;
	font-weight:bold;
	background-color: #303331;
	color: #fff !important;
	}
	.nav-tabs>li>a {
	margin-right: 2px;
	line-height: 1.42857143;
	border-radius: 4px 4px 0 0;
	font-weight:bold;
	border: 1px solid #555;
	border-bottom-color: transparent;
	color: #000 !important;
	}
	
	
	.table-sm{
		font-size:90%;
		overflow-x: scroll;
	}
	
	button:disabled, button:disabled:hover{
	cursor: not-allowed;
	color:#555;
	}
	
	.table-edit{
		/*
		min-width:100%;
		width:auto;
		max-width:200% !important;
		*/
	}
	
	.table-edit tr{
	border:0 !important;
	}
	.table-edit td{
	padding:3px !important;
	/*
	border-left:0 !important;
	border-right:0 !important;
	*/
	}
	.table-edit td input{
	border:1px solid #555;
	}
	.table-edit input[type=text]{
		width:100%;
		text-align:right;
	}
	
	.table-edit td input.disabled{
		background: #fff;
		border: 1px solid #fff;	
	}
	
	.table-edit td input[readonly]{
		background: #fff;
		border: 1px solid #fff;	
	}
	
	.select2-container ul li {
		background-color: #fff !important;
		color: #000  !important;
	}
	.select2-container ul li {
		background-color: #fff !important;
		color: #000  !important;
	}

	.select2-results__option--highlighted.select2-results__option--selectable {
		background-color: #5897fb !important;
		color: white !important;
	}
	
	.form-control, .btn{
		height:28px;
		line-height:28px;
	}
	.btn{
		padding-top:3px;
	}
</style>

<div class="container">
	<div class="form_title">
		<div style="text-align:center;">
			ACHIEVEMENT KPI V2
		</div>
	</div>
	<br>
	<form id="form_request" class="form-horizontal" action="<?php echo site_url('Achievementkpiv2/sendrequest') ?>" target="_blank" method="POST">
		<div class="row">
			<div class="col-3">
				<label>Cabang :</label>
				<select name="branch_id" id="branch_id" class="form-control select2" style="width:100%">
					<?php
						foreach($branch as $b) {
							$selected = ($b['BRANCHID']==$_SESSION['logged_in']['branch_id']) ? 'selected' : '';
							echo "<option value='".$b['BRANCHID']."' ".$selected.">".$b['BRANCHNAME']."</option>";
						}
					?>
				</select>
			</div>
			<div class="col-3">
				<label>Manager :</label>
				<select name="spvuserid" id="spvuserid" class="form-control select2" style="width:100%">
				</select>
			</div>
			<div class="col-3">
				<label>KPI Division :</label>
				<select name="kpidivision" id="kpidivision" class="form-control select2" style="width:100%">
					<?php
						// foreach($KPICategory as $r) {
							// echo "<option value='".$r->KPICategory."'>".$r->KPICategoryName."</option>";
						// }
					?>
				</select>
				<!--input type="hidden" name="kpidivision_name" id="kpidivision_name"-->
				<input type="hidden" name="week" id="week">
			</div>
			<div class="col-2">
				<label>Periode Target :</label>
				<input type="text" name="periode" id="periode" class="form-control monthpicker" style="width:100%" value="<?php echo date('F Y') ?>">
			</div>
			
			<div class="col-1">
				<label>&nbsp;</label>
				<br>
				<button type="button" class="btn btn-dark w100" onclick="javascript:load_bawahan()">View</button>
			</div>
		</div>
		<div class="row">
			<div class="col-3">
				<label></label>
				<br>
				<button type="submit" id="btn_sendrequest" class="btn-primary" disabled>SEND REQUEST</button>
			</div>
			<div class="col-3">
				<label>Atasan [Zen]:</label>
				<br>
				<input type="hidden" name="atasan_name" id="atasan_name" value="">
				<input type="hidden" name="atasan_email" id="atasan_email" value="">
				<!--span id="atasan_name_span"><?php //echo $atasan['Name'] ?></span> [<span id="atasan_email_span"><?php //echo $atasan['UserEmail'] ?></span>] -->
				<input type="text" name="atasan_name_span" id="atasan_name_span" class="form-control" readonly>
			</div>
			<div class="col-3">
				<label>KPI Category :</label>
				<input type="hidden" name="kpicategory" id="kpicategory">
				<input type="text" name="kpicategoryname" id="kpicategoryname" class="form-control" readonly>
			</div>
			<div class="col-3">
				<label>Jenis KPI :</label>
				<input type="text" name="jeniskpi" id="jeniskpi" class="form-control" readonly>
			</div>
			
			
		</div>
		<div class="row">
			<div class="col-12">
				<table id="table_achievement" class="table table-bordered" summary="table">
					<thead>
						<tr>
							<th width="2%" class="no-sort"><input type="checkbox" id="pilih_semua"></th>
							<th width="5%">USERID</th>
							<th width="*">NAMA</th>
							<th width="20%">POSISI</th>
							<th width="5%">TOTAL ACHIEVEMENT</th>
							<th width="10%">STATUS TERAKHIR</th>
							<th width="10%">NO REQUEST</th>
							<th width="10%">EXCLUDE TUNJANGAN PRESTASI</th>
							<th width="200px" class="no-sort">AKSI</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</form>
</div>

<div class="modal fade" id="modal_edit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form id="form_save" class="form-horizontal" action="<?php echo site_url('Achievementkpiv2/save') ?>" method="POST">
				<div class="modal-header form_title">
					<div style="text-align:center;">
						ACHIEVEMENT KPI V2
					</div>
				</div>
				<div class="modal-body p20">
					<input type="hidden" name="spv_userid" id="spv_userid">
					<input type="hidden" name="spv_name" id="spv_name">
					<input type="hidden" name="spv_email" id="spv_email">
					<input type="hidden" name="userid" id="userid">
					<input type="hidden" name="nama" id="nama">
					<input type="hidden" name="divisionid" id="divisionid">
					<input type="hidden" name="divisionname" id="divisionname">
					<input type="hidden" name="positionid" id="positionid">
					<input type="hidden" name="positionname" id="positionname">
					<input type="hidden" name="th" id="th">
					<input type="hidden" name="bl" id="bl">
					<input type="hidden" name="kodetarget" id="kodetarget">
					<input type="hidden" name="withtargetkpi" id="withtargetkpi">
					<input type="hidden" name="norequestkpi" id="norequestkpi">
					<input type="hidden" name="norequestacv" id="norequestacv">
					<input type="hidden" name="acvkpistatus" id="acvkpistatus">
					
					<div class="row">
						<div class="col-6">
							<table class="w100">
								<tr><td width="150px">USERID / Nama </td>		<td><strong><span id="span_nama"></span> [<span id="span_userid"></span>] </strong></td></tr>
								<tr><td>Position </td>		<td><strong><span id="span_positionname"></span></strong></td></tr>
								<tr><td>Level </td>		<td><strong><span id="span_level"></span></strong></td></tr>
								<tr><td>Catatan </td><td><span id="catatan"></span></td></tr>
							</table>
						</div>
						<div class="col-6">
							<table class="w100">
								<tr><td width="150px">KPI Category</td>	<td><strong><span id="span_kpicategoryname"></span></strong></td></tr>
								<tr><td>Division</td>	<td><strong><span id="span_divisionname"></span></strong></td></tr>
								<tr><td>Status </td><td><strong><span id="span_targetkpistatus"></span> <span id="span_norequestkpi"></span></strong></td></tr>
								<tr><td></td><td><input type="checkbox" name="exclude" id="exclude"> Exclude Tunjangan Prestasi </td></tr>
							</table>
						</div>
					</div>
					
					<div class="row">
						<div class="col-12">
							<input type="hidden" name="kode_target" id="kode_target" value="">
							<input type="hidden" name="no_request" id="no_request" value="">
							<div style="overflow-x:scroll">
								<table id="table_kpi" class="table table-bordered table-sm table-edit" summary="table">
									<thead>
										<tr>
											<th style="min-width:250px" rowspan="2">KEY PERFORMANCE INDICATOR</th>
											<th style="min-width:150px" rowspan="2">DESKRIPSI</th>
											<th style="min-width:120px" rowspan="2">TARGET</th>
											<th style="min-width:50px" rowspan="2">BOBOT (%)</th>
											
											<th colspan="3" class="text-center target_1">WEEK 1</th>
											<th colspan="3" class="text-center target_2">WEEK 2</th>
											<th colspan="3" class="text-center target_3">WEEK 3</th>
											<th colspan="3" class="text-center target_4">WEEK 4</th>
											<th colspan="3" class="text-center target_5">WEEK 5</th>
											<th colspan="3" class="text-center target_6">WEEK 6</th>
											
											
											<th style="min-width:120px" rowspan="2">TOTAL</th>
											<th style="min-width:50px" rowspan="2">% ACV</th>
											<th style="min-width:50px" rowspan="2">BOBOT ACV</th>
										</tr>
										<tr>
											<th style="min-width:120px" class="text-center target_1">TARGET</th>
											<th style="min-width:120px" class="text-center target_1">ACV</th>
											<th style="min-width:50px" class="text-center target_1">%</th>
											
											<th style="min-width:120px" class="text-center target_2">TARGET</th>
											<th style="min-width:120px" class="text-center target_2">ACV</th>
											<th style="min-width:50px" class="text-center target_2">%</th>
											
											<th style="min-width:120px" class="text-center target_3">TARGET</th>
											<th style="min-width:120px" class="text-center target_3">ACV</th>
											<th style="min-width:50px" class="text-center target_3">%</th>
											
											<th style="min-width:120px" class="text-center target_4">TARGET</th>
											<th style="min-width:120px" class="text-center target_4">ACV</th>
											<th style="min-width:50px" class="text-center target_4">%</th>
											
											<th style="min-width:120px" class="text-center target_5">TARGET</th>
											<th style="min-width:120px" class="text-center target_5">ACV</th>
											<th style="min-width:50px" class="text-center target_5">%</th>
											
											<th style="min-width:120px" class="text-center target_6">TARGET</th>
											<th style="min-width:120px" class="text-center target_6">ACV</th>
											<th style="min-width:50px" class="text-center target_6">%</th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
							
							
							<span style="float:right">
								TOTAL ACHIEVEMENT <input type="text" name="total_achievement" id="total_achievement" style="width:60px;text-align:center" readonly>
							</span>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="col-9" style="text-align:left">
						<div id="history"></div>
					</div>
					<div class="col-3">
						<button type="submit" class="btn-success form-edit">SAVE</button>
						<button type="button" class="btn-danger" data-dismiss="modal">CLOSE</button>
						<div id="result"></div>
					</div>
				</div>
				
			</form>
		</div>
	</div>
</div>

<script>
	const monthNames = ["", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];		
	let table_achievement;
	
	let th;
	let bl;
	
	let master_kpi;
	let idx;
	let cur_template='';
	var w;
	table_achievement = $('#table_achievement').DataTable({
		"pageLength"    : 10,
		"searching"     : true,
		"columnDefs": [
		{ targets: 'no-sort', orderable: false },
		{ targets: 'col-hide', visible: false }
		],
		"dom": '<"top">rt<"bottom"ip><"clear">',
		"order": [[1, 'desc']],
		"language": {
			"emptyTable": "Data EMPTY / Klik VIEW untuk load data"
		}
	});
	
	$(document).ready(function(){   
		$(".monthPicker").datepicker({
			dateFormat: 'MM yy',
			changeMonth: true,
			changeYear: true,
			showButtonPanel: true,
			immediateUpdates: true,
			todayHighlight: false,
			onClose: function(dateText, inst) {
				var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
				var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
				$(this).datepicker('setDate', new Date(year, month, 1));
				// $(this).val($.datepicker.formatDate('MM yy', new Date(year, month, 1)));
			},
		});
		
		$(".monthPicker").focus(function () {
			$(".ui-datepicker-calendar").hide();
			$("#ui-datepicker-div").position({
				my: "center top",
				at: "center bottom",
				of: $(this)
			});
		});
		
		$(".select2").select2();
		$("#branch_id").change(function () {
			load_userid();
		});
		$("#spvuserid").change(function () {
			load_kpidivision();
		});
		
		load_userid();
		
	});
	
	$(document).on("input", ".numeric", function() {
		this.value = format_currency(this.value);
	});

	$(document).on("blur", ".numeric", function() {
		if(this.value!=''){
			var x  = parseFloat(this.value.replace(/,/g,"")) || 0;
			x = format_currency(parseFloat(x).toFixed(2));
			this.value = format_currency(x);
		}
	});

	
	$(document).on("click", "#pilih_semua", function() {
		var c = this.checked;
		$('.pilih').each(function(i, obj) {
			if($(this).is(':disabled')){
			}
			else{
				$(this).prop("checked", c);
			}
		});
		
		var c = $('input.pilih:checked').length;
		if(c>0){
			$('#btn_sendrequest').prop('disabled',false);
		}
		else{
			$('#btn_sendrequest').prop('disabled',true);
		}
	});
	
	$(document).on("change", ".pilih", function(){
		var c = $('input.pilih:checked').length;
		if(c>0){
			$('#btn_sendrequest').prop('disabled',false);
		}
		else{
			$('#btn_sendrequest').prop('disabled',true);
		}
	});
	
	$(document).on("blur", ".bobot", function() {
		hitung_achievement();
	});
	
	$(document).on("click", ".btn-edit" , function() {
		load_achievement($(this), 'edit');
	});
	
	$(document).on("click", ".btn-view" , function() {
		load_achievement($(this), 'view');
	});
	
	$(document).on("blur", ".acv-input", function() {
		var x = this.id.split('_');
		var id = x[1];
		
		var target = $('#target_'+id).val() || '0';
		var bobot = $('#bobot_'+id).val() || '0';
		
		var target1 = $('#target1_'+id).val() || '0';
		var target2 = $('#target2_'+id).val() || '0';
		var target3 = $('#target3_'+id).val() || '0';
		var target4 = $('#target4_'+id).val() || '0';
		var target5 = $('#target5_'+id).val() || '0';
		var target6 = $('#target6_'+id).val() || '0';
		
		var acv1 = $('#acv1_'+id).val() || '0';
		var acv2 = $('#acv2_'+id).val() || '0';
		var acv3 = $('#acv3_'+id).val() || '0';
		var acv4 = $('#acv4_'+id).val() || '0';
		var acv5 = $('#acv5_'+id).val() || '0';
		var acv6 = $('#acv6_'+id).val() || '0';
		
		target1 = parseFloat(target1.replace(/,/g,'')) || 0;
		target2 = parseFloat(target2.replace(/,/g,'')) || 0;
		target3 = parseFloat(target3.replace(/,/g,'')) || 0;
		target4 = parseFloat(target4.replace(/,/g,'')) || 0;
		target5 = parseFloat(target5.replace(/,/g,'')) || 0;
		target6 = parseFloat(target6.replace(/,/g,'')) || 0;
		
		acv1 = parseFloat(acv1.replace(/,/g,'')) || 0;
		acv2 = parseFloat(acv2.replace(/,/g,'')) || 0;
		acv3 = parseFloat(acv3.replace(/,/g,'')) || 0;
		acv4 = parseFloat(acv4.replace(/,/g,'')) || 0;
		acv5 = parseFloat(acv5.replace(/,/g,'')) || 0;
		acv6 = parseFloat(acv6.replace(/,/g,'')) || 0;
		
		var persen1 = acv1/target1*100 || 0;
		var persen2 = acv2/target2*100 || 0;
		var persen3 = acv3/target3*100 || 0;
		var persen4 = acv4/target4*100 || 0;
		var persen5 = acv5/target5*100 || 0;
		var persen6 = acv6/target6*100 || 0;
		
		$('#persen1_'+id).val(format_currency(persen1.toFixed(2)));
		$('#persen2_'+id).val(format_currency(persen2.toFixed(2)));
		$('#persen3_'+id).val(format_currency(persen3.toFixed(2)));
		$('#persen4_'+id).val(format_currency(persen4.toFixed(2)));
		$('#persen5_'+id).val(format_currency(persen5.toFixed(2)));
		$('#persen6_'+id).val(format_currency(persen6.toFixed(2)));
		
		var acvtotal = acv1 + acv2 + acv3 + acv4 + acv5 + acv6;
		var acvpersen = acvtotal*100/target;
		if(acvpersen>100){
			acvpersen=100;
		}
		
		var acvbobot = bobot * acvpersen / 100;
		
		$('#acvtotal_'+id).val(format_currency(acvtotal.toFixed(2)));
		$('#acvpersen_'+id).val(format_currency(acvpersen.toFixed(2)));
		$('#acvbobot_'+id).val(format_currency(acvbobot.toFixed(2)));
		
		hitung_achievement();
	});
	
	$(document).ready(function() {
		$("#form_request").submit(function() {
			if(confirm("Send Request Achievement KPI ini?")){
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
					success   : function(data){
						// console.log(JSON.stringify(data));
						$('.loading').hide();
						if(data.result=='success'){
							alert('SUCCESS. Request berhasil dikirim!');
							load_bawahan();
						}
						else{
							alert('FAILED. '+data.error);
							$('#modal_edit').modal('hide');	
						}
					}
				});
			}
			event.preventDefault();
		});
		
		$("#form_save").submit(function() {
			if(validasi()!=''){
				alert(validasi());
				return false;
			}
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
				success   : function(data){
					// console.log(JSON.stringify(data));
					$('.loading').hide();
					if(data.result=='success'){
						$('#modal_edit').modal('hide');
						load_bawahan();
						alert('SUCCESS. Achievement KPI berhasil disimpan!');
					}
					else{
						alert('FAILED. '+data.error);
					}
				}
			});
			event.preventDefault();
		});
	});
	
	function load_userid(){
		$('.loading').show();
		var branch_id =  $('#branch_id').val();
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo base_url() ?>Targetkpiv2/GetDivHeadByBranchIDAndUserID/'+branch_id,
			dataType: 'json',
			success: function (res) {
				console.log(res);
				$('.loading').hide();
				var opt = '';
				if(res.result=='sukses'){
					for(i=0;i<res.data.length;i++){
						opt+='<option value="'+res.data[i].USERID+'">'+res.data[i].NAME+'</option>';
					}
				}
				else{
					alert(res.error);
				}
				$('#spvuserid').html(opt);
				load_kpidivision();
			}
		});
	}
	
	function load_kpidivision(){
		var spvuserid =  $('#spvuserid').val();
		$('#kpidivision').html('');
		if(spvuserid){
		$('.loading').show();
			$.ajax({ 
				type: 'GET', 
				url: '<?php echo base_url() ?>Targetkpiv2/GetDivision/'+spvuserid,
				dataType: 'json',
				success: function (res) {
					// console.log(res);
					$('.loading').hide();
					var opt = '';
					if(res.result=='sukses'){
						for(i=0;i<res.data.length;i++){
							opt+='<option value="'+res.data[i].DivisionID+'">'+res.data[i].DivisionName+'</option>';
						}
					}
					else{
						alert(res.error);
					}
					$('#kpidivision').html(opt);
				}
			});
		}
	}
	
	function load_bawahan(){
		var spvuserid =  $('#spvuserid').val();
		var kategori =  $('#kpicategory').val();
		var divisionid =  $('#kpidivision').val();
		var divisionname =  $('#kpidivision option:selected').text();
		
		var period =  $('#periode').val().split(' ');
		bl = monthNames.indexOf(period[0]);
		th = period[1];
		
		w = weekCount(th,bl);
		$('#week').val(w);
		
		$('#btn_sendrequest').prop('disabled',true);
		$('#pilih_semua').prop('checked',false);
		
		$('.loading').show();
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo base_url('Achievementkpiv2/ListAchievementKPIV2') ?>',  
			data: {
				'userid': spvuserid,
				'kategori': kategori,
				'divisionid': divisionid,
				'divisionname': divisionname,
				'th': th,
				'bl': bl
			}, 
			dataType: 'json',
			success: function (res) {
				// console.log(JSON.stringify(result));
				// console.log(result);
				$('.loading').hide();
				table_achievement = $('#table_achievement').DataTable(); 
				table_achievement.clear().draw();
				if(res.result=='success'){
					$('#kpicategory').val(res.kpicategory.KPICategory);
					$('#kpicategoryname').val(res.kpicategory.KPICategoryName);
					$('#jeniskpi').val(res.kpicategory.Jenis);
					
					for(i=0;i<res.bawahan.length;i++){
						var data = res.bawahan[i];
						
						var edit_disabled = (data.AcvKPIStatus=='APPROVED' || data.AcvKPIStatus=='WAITING FOR APPROVAL') ? 'disabled' : '';
						var cancel_disabled = (data.AcvKPIStatus=='APPROVED' || data.AcvKPIStatus=='WAITING FOR APPROVAL') ? '' : 'disabled';
						var report_disabled = (data.AcvKPIStatus=='APPROVED') ? '' : 'disabled';
						var check_disabled = (data.AcvKPIStatus=='SAVED' || data.AcvKPIStatus=='SAVED(MODIFIED)' || data.AcvKPIStatus=='CANCELLED' || data.AcvKPIStatus=='REJECTED') ? '' : 'disabled';
						
						var dt = '';
						dt+='data-userid="'+data.USERID+'"';
						dt+='data-nama="'+data.Nama+'"';
						dt+='data-DivisionID="'+data.DivisionID+'"';
						dt+='data-DivisionName="'+data.DivisionName+'"';
						dt+='data-PositionID="'+data.PositionID+'"';
						dt+='data-PositionName="'+data.PositionName+'"';
						dt+='data-emplevelid="'+data.EmpLevelID+'"';
						dt+='data-emplevel="'+data.EmpLevel+'"';
						
						dt+='data-kodetarget="'+data.Kode_Target+'"';
						dt+='data-training="'+data.Training+'"';
						dt+='data-withtargetkpi="'+data.WithTargetKPI+'"';
						dt+='data-norequestkpi="'+data.NoRequestKPI+'"';
						dt+='data-norequestacv="'+data.NoRequestAcv+'"';
						dt+='data-acvkpistatus="'+data.AcvKPIStatus+'"';
						dt+='data-exclude="'+data.ExcludeTunjanganPrestasi+'"';
						dt+='data-catatan="'+data.catatan+'"';
						
						table_achievement.row.add([
						'<input type="checkbox" name="kode_target[]" value="'+data.Kode_Target+'" class="pilih" '+check_disabled+'>',
						data.USERID,
						data.Nama,
						data.PositionName,
						parseFloat(data.TotalAchievement).toFixed(0),
						(data.AcvKPIStatus=='')?'NEW':data.AcvKPIStatus,
						data.NoRequestAcv,
						(data.ExcludeTunjanganPrestasi=='1')?'Y':'N',
						'<button type="button" class="btn-success btn-view" '+dt+'>VIEW</button> '+
						'<button type="button" class="btn-primary btn-edit" '+dt+' '+edit_disabled+'>EDIT</button> '+
						'<!--button type="button" class="btn-warning" onclick="javascript:open_report(\''+data.USERID+'\')" '+report_disabled+'><i class="glyphicon glyphicon-file"></i></button--> '+
						'<button type="button" class="btn-danger" onclick="javascript:cancel_request(\''+data.USERID+'\',\''+data.Kode_Target+'\',\''+data.NoRequestAcv+'\')"'+cancel_disabled+'>CANCEL</button>'
						]);
						
					}
					table_achievement.draw();
					// master_kpi = result.master_kpi;
					// create_select_template_kpi(result.template);
					
					//atasan
					$('#atasan_name').val(res.atasan.Name);	
					$('#atasan_email').val(res.atasan.UserEmail);
					$('#atasan_name_span').val(res.atasan.Name+' ['+res.atasan.UserEmail+']');
				}
				else{
					alert(res.error);
				}
			}
		});
	}
	
	function load_achievement(x, mode){
		var disabled = (mode=='view')? 'disabled' : '';
		
		
		var spv_userid = $('#spvuserid').val();
		var spv_name = $('#atasan_name').val();
		var spv_email = $('#atasan_email').val();
		var kpicategoryname = $('#kpicategoryname').val();
		
		var userid = x.attr('data-userid');
		var nama = x.attr('data-nama');
		var divisionid = x.attr('data-divisionid');
		var divisionname = x.attr('data-divisionname');
		var positionid = x.attr('data-positionid');
		var positionname = x.attr('data-positionname');
		var levelid = x.attr('data-emplevelid');
		var level = x.attr('data-emplevel');
		
		var kodetarget = x.attr('data-kodetarget');
		var training = x.attr('data-training');
		var withtargetkpi = x.attr('data-withtargetkpi');
		var norequestkpi = x.attr('data-norequestkpi');
		var norequestacv = x.attr('data-norequestacv');
		var acvkpistatus = x.attr('data-acvkpistatus');
		var exclude = x.attr('data-exclude');
		var catatan = x.attr('data-catatan');
		
		var span_norequestacv = (norequestacv!='') ? ' ['+norequestacv+']' : 'NEW';
		
		$('#spv_userid').val(spv_userid);
		$('#spv_name').val(spv_name);
		$('#spv_email').val(spv_email);
		
		$('#userid').val(userid);
		$('#nama').val(nama);
		$('#divisionid').val(divisionid);
		$('#divisionname').val(divisionname);
		$('#positionid').val(positionid);
		$('#positionname').val(positionname);
		
		$('#th').val(th);
		$('#bl').val(bl);
		
		$('#kodetarget').val(kodetarget);
		$('#training').prop('checked',parseInt(training));
		$('#withtargetkpi').val(withtargetkpi);
		$('#norequestkpi').val(norequestkpi);
		$('#norequestacv').val(norequestacv);
		$('#acvkpistatus').val(acvkpistatus);
		$('#exclude').prop('checked',parseInt(exclude));
		
		$('#span_userid').html(userid);
		$('#span_nama').html(nama);
		$('#span_kpicategoryname').html(kpicategoryname);
		$('#span_divisionname').html(divisionname);
		$('#span_positionname').html(positionname);
		$('#span_level').html(level);
		$('#span_targetkpistatus').html(acvkpistatus+span_norequestacv);
		$('#catatan').html(catatan);
		
		$('#exclude').prop('disabled',(mode=='view')? true : false);
		
		$('.loading').show();
		idx = 0;
		$('#table_kpi tbody').html('');
		$('#table_history tbody').html('<tr><td colspan="5" align="center">Tidak ada history</td></tr>');
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url('Achievementkpiv2/TargetKaryawan_KPI_AmbilAchievementDetail') ?>',  
			data: {
				'KodeTarget': kodetarget,
				'NoRequestKPI': norequestkpi,
				'NoRequestAcv': norequestacv
			}, 
			dataType: 'json',
			success: function (result) {
				console.log(JSON.stringify(result));
				$('.loading').hide();
				for(i=0;i<result.detail.length;i++){
					idx++;
					var data = result.detail[i];
					var tbody = '';
					tbody+='<tr id="baris_'+idx+'">'+
					'<td><input type="hidden" name="kpicode[]" value="'+data.KPICode+'"><input type="hidden" name="kpiname[]" value="'+data.KPIName+'">'+data.KPIName+'</td>'+
					'<td><input type="hidden" name="kpinote[]" value="'+data.KPINote+'"><input type="hidden" name="kpiunit[]" value="'+data.KPIUnit+'">'+data.KPINote+'</td>'+
					'<td><input type="text" name="target[]" value="'+data.KPITarget+'" id="target_'+idx+'" class="target disabled" readonly></td>'+
					'<td><input type="text" name="bobot[]" value="'+data.KPIBobot+'" id="bobot_'+idx+'" class="bobot disabled" readonly ></td>'+
					'<td class="text-center target_1"><input type="text" name="target1[]" id="target1_'+idx+'" value="'+format_currency(data.TargetWeek1)+'" class="target1 disabled" readonly></td>'+
					'<td class="text-center target_1"><input type="text" name="acv1[]" id="acv1_'+idx+'" value="'+format_currency(data.AcvWeek1)+'" class="acv-input numeric '+disabled+'"></td>'+
					'<td class="text-center target_1"><input type="text" name="persen1[]" id="persen1_'+idx+'" value="'+format_currency(data.PersenWeek1)+'" class="disabled" readonly></td>'+
					
					'<td class="text-center target_2"><input type="text" name="target2[]" id="target2_'+idx+'" value="'+format_currency(data.TargetWeek2)+'" class="target2 disabled" readonly></td>'+
					'<td class="text-center target_2"><input type="text" name="acv2[]" id="acv2_'+idx+'" value="'+format_currency(data.AcvWeek2)+'" class="acv-input numeric '+disabled+'"></td>'+
					'<td class="text-center target_2"><input type="text" name="persen2[]" id="persen2_'+idx+'" value="'+format_currency(data.PersenWeek2)+'" class="disabled" readonly></td>'+
					
					'<td class="text-center target_3"><input type="text" name="target3[]" id="target3_'+idx+'" value="'+format_currency(data.TargetWeek3)+'" class="target3 disabled" readonly></td>'+
					'<td class="text-center target_3"><input type="text" name="acv3[]" id="acv3_'+idx+'" value="'+format_currency(data.AcvWeek3)+'" class="acv-input numeric '+disabled+'"></td>'+
					'<td class="text-center target_3"><input type="text" name="persen3[]" id="persen3_'+idx+'" value="'+format_currency(data.PersenWeek3)+'" class="disabled" readonly></td>'+
					
					'<td class="text-center target_4"><input type="text" name="target4[]" id="target4_'+idx+'" value="'+format_currency(data.TargetWeek4)+'" class="target4 disabled" readonly></td>'+
					'<td class="text-center target_4"><input type="text" name="acv4[]" id="acv4_'+idx+'" value="'+format_currency(data.AcvWeek4)+'" class="acv-input numeric '+disabled+'"></td>'+
					'<td class="text-center target_4"><input type="text" name="persen4[]" id="persen4_'+idx+'" value="'+format_currency(data.PersenWeek4)+'" class="disabled" readonly></td>'+
					
					'<td class="text-center target_5"><input type="text" name="target5[]" id="target5_'+idx+'" value="'+format_currency(data.TargetWeek5)+'" class="target5 disabled" readonly></td>'+
					'<td class="text-center target_5"><input type="text" name="acv5[]" id="acv5_'+idx+'" value="'+format_currency(data.AcvWeek5)+'" class="acv-input numeric '+disabled+'"></td>'+
					'<td class="text-center target_5"><input type="text" name="persen5[]" id="persen5_'+idx+'" value="'+format_currency(data.PersenWeek5)+'" class="disabled" readonly></td>'+
					
					'<td class="text-center target_6"><input type="text" name="target6[]" id="target6_'+idx+'" value="'+format_currency(data.TargetWeek6)+'" class="target6 disabled" readonly></td>'+
					'<td class="text-center target_6"><input type="text" name="acv6[]" id="acv6_'+idx+'" value="'+format_currency(data.AcvWeek6)+'" class="acv-input numeric '+disabled+'"></td>'+
					'<td class="text-center target_6"><input type="text" name="persen6[]" id="persen6_'+idx+'" value="'+format_currency(data.PersenWeek6)+'" class="disabled" readonly></td>'+
					
					'<td><input type="text" name="acvtotal[]" value="'+format_currency(data.AcvTotal)+'" id="acvtotal_'+idx+'" class="disabled" readonly ></td>'+
					'<td><input type="text" name="acvpersen[]" value="'+format_currency(data.AcvPersen)+'" id="acvpersen_'+idx+'" class="disabled" readonly ></td>'+
					'<td><input type="text" name="acvbobot[]" value="'+format_currency(data.AcvBobot)+'" id="acvbobot_'+idx+'" class="acvbobot disabled" readonly ></td>'+
					'</tr>';
					$('#table_kpi tbody').append(tbody);
				}
				
				var log = '';
				if(result.history.length>0){
					for(i=0;i<result.history.length;i++){
						var data = result.history[i];
						log+='<b>'+result.history[i].HistoryName+'</b>: '+result.history[i].UserName+' ('+result.history[i].Email+') '+result.history[i].HistoryDate+'<br>';
					}
				}
				$('#history').html(log);
				
				hitung_achievement();	
				set_kolom_minggu();
				set_kolom_target();
				
				if(mode=='view')
				{$('.form-edit').hide();}
				else
				{$('.form-edit').show();}
				
				// $('#template_kpi').prop('disabled',false);
				
				$('.acv-input').prop('disabled',(mode=='view')? true : false);
				
				$('#modal_edit').modal('show');	
			}
		});	
	}
	
	function cancel_request(USERID, KodeTarget, NoRequestAcv){
		var note = prompt("Alasan cancel request ini?", ""); 
		if (note!='' && note!=null) {
			
			$('.loading').show();
			$.ajax({ 
				type: 'POST', 
				url: '<?php echo site_url('Achievementkpiv2/cancel') ?>',  
				data: {
					'userid': USERID,
					'kode_target': KodeTarget,
					'norequestacv': NoRequestAcv,
					'note': note,
					'th': th,
					'bl': bl,
				}, 
				dataType: 'json',
				success: function (data) {
					// console.log(data);
					$('.loading').hide();
					if(data.result=='success'){
						alert('SUCCESS. Request berhasil dicancel!');
						load_bawahan();
					}
					else{
						alert('FAILED. '+data.error);
					}
				}
			});
		}
	}
	
	function weekCount(year, month_number) {
		// month_number is in the range 1..12
		var firstOfMonth = new Date(year, month_number-1, 1);
		var lastOfMonth = new Date(year, month_number, 0);
		var used = firstOfMonth.getDay() + lastOfMonth.getDate();
		return Math.ceil( used / 7);
	}
	
	function validasi(){
		var msg = '';
		$('.target').each(function(i, obj) {
			if($(this).val()=='' || $(this).val()==0){
				msg = 'Target tidak boleh kosong!';
			}
		});
		var bobot = 0;
		$('.bobot').each(function(i, obj) {
			bobot+= parseFloat($(this).val().replace(/,/g,'')) || 0;
		});
		if(bobot!=100){
			msg = 'Bobot harus 100%!';
		}
		return msg;
	}
	
	function hitung_achievement(){
		var acvbobot = 0;
		$('.acvbobot').each(function(i, obj) {
			acvbobot+= parseFloat($(this).val().replace(/,/g,'')) || 0;
		});
		
		$('#total_achievement').val(parseFloat(acvbobot).toFixed(0));
		$('#span_total_achievement').text(parseFloat(acvbobot).toFixed(0));
	}
	
	function set_kolom_minggu(){
		if(w<5){
			$('.target5').hide();
			$('.target6').hide();
			}else if(w<6){
			$('.target6').hide();
		}
		else{
			$('.target5').show();
			$('.target6').show();
		}	
	}
	
	function set_kolom_target(){
		for(i=1;i<=6;i++){
			var jum = 0;
			$('.target'+i).each(function(i, obj) {
				jum+= parseFloat($(this).val().replace(/,/g,'')) || 0;
			});
			if(jum==0){
				$('.target_'+i).hide();
			}
			else{
				$('.target_'+i).show();
			}
		}
	}
	
	function format_currency(x){
		x = x.toString().replace(/,/g,"") || 0;
		var str = x.replace(/[^0-9.]/g,'');
		x = str.split('.'); 
		x1 = x[0].length > 0 ? x[0] : '0';
		x2 = x.length > 1 ? '.' + x[1] : '';
		var rgx = /(\d+)(\d{3})/; 
		while (rgx.test(x1)) { 
			x1 = x1.replace(rgx, '$1' + ',' + '$2'); 
		}
		return x1 + x2;
	}
	
	function open_report(userid){
		window.open('<?php echo $api_zen ?>/EmpTunjanganPrestasi/ViewDetail/'+userid+'/'+th+'/'+bl, '_blank');
	}
	
	$(".monthpicker").datepicker( {
		format: "MM yyyy",
		viewMode: "months", 
		minViewMode: "months",
		autoclose: true,
	});
	
</script>

