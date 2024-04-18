<!--link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script-->
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
	font-size:80%;
	}
	
	button:disabled, button:disabled:hover{
	cursor: not-allowed;
	color:#555;
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
	background: #ccc;
	border: 1px solid #ccc;	
	}
	.table-edit td input.justify{
	text-align: left;
	}
	
</style>

<div class="container">
	<div class="form_title">
		<center>
			TARGET KPI KARYAWAN
		</center>
	</div>
	<br>
	<form id="form_request" class="form-horizontal" action="<?php echo site_url('Targetkpikaryawan/sendrequest') ?>" target="_blank" method="POST">
		<div class="row">
			<div class="col-3">
				<label>KPI Category :</label>
				<select name="kpi_kategori" id="kpi_kategori" class="form-control" style="width:100%">
					<?php
						foreach($KPICategory as $r) {
							echo "<option value='".$r->KPICategory."'>".$r->KPICategoryName."</option>";
						}
					?>
				</select>
				<input type="hidden" name="kpi_kategori_name" id="kpi_kategori_name">
				<input type="hidden" name="week" id="week">
			</div>
			<div class="col-3">
				<label>Periode Target :</label>
				
				<div class="input-group">
					<input type="text" name="periode" id="periode" class="form-control monthpicker" style="width:100%" value="<?php echo date('F Y') ?>">
					<span class="input-group-btn">
						<button type="button" class="btn btn-dark" onclick="javascript:load_bawahan()">View</button>
					</span>
				</div>
			</div>
			<div class="col-3">
				<input type="checkbox" id="chk_duplicate" style="margin-top:0"> <label for="chk_duplicate"> Duplicate dari Periode :</label>
				
				<div class="input-group" id="div_duplicate">
					<input type="text" id="periode_duplicate" class="form-control monthpicker" style="width:100%" value="<?php echo date('F Y') ?>">
					<span class="input-group-btn">
						<button type="button" class="btn btn-dark" onclick="javascript:duplicate()">Duplicate</button>
					</span>
				</div>
			</div>
			<div class="col-3">
				<label>Atasan :</label>
				<br>
				<input type="hidden" name="atasan_name" value="<?php echo $atasan['Name'] ?>">
				<input type="hidden" name="atasan_email" value="<?php echo $atasan['UserEmail'] ?>">
				<span id="atasan_name_span"><?php echo $atasan['Name'] ?></span> <em><small id="atasan_email_span"><?php echo $atasan['UserEmail'] ?></small></em>
			</div>
		</div>
		
		<div class="row">
		
			
			<div class="col-12">
				<button type="submit" id="btn_sendrequest" class="btn-primary" disabled>SEND REQUEST</button>
				<table id="table_target" class="table table-bordered">
					<thead>
						<tr>
							<th width="2%" class="no-sort"><input type="checkbox" id="pilih_semua"></th>
							<th width="10%">USERID</th>
							<th width="*">NAMA</th>
							<th width="20%">POSISI</th>
							<th width="5%">TRAINING</th>
							<th width="5%">TOTAL BOBOT</th>
							<th width="10%">STATUS TERAKHIR</th>
							<th width="10%">NO REQUEST</th>
							<th width="18%" class="no-sort">AKSI</th>
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
			<form id="form_save" class="form-horizontal" action="<?php echo site_url('Targetkpikaryawan/save') ?>" method="POST">
				<div class="modal-header form_title">
					<center>
						TARGET KPI KARYAWAN
					</center>
				</div>
				<div class="modal-body p20">
					<input type="hidden" name="atasan" id="atasan">
					<input type="hidden" name="userid" id="userid">
					<input type="hidden" name="nama" id="nama">
					<input type="hidden" name="divisionid" id="divisionid">
					<input type="hidden" name="divisionname" id="divisionname">
					<input type="hidden" name="positionid" id="positionid">
					<input type="hidden" name="positionname" id="positionname">
					<input type="hidden" name="tglawal" id="tglawal">
					<input type="hidden" name="tglakhir" id="tglakhir">
					<input type="hidden" name="kodetarget" id="kodetarget">
					<input type="hidden" name="withtargetkpi" id="withtargetkpi">
					<input type="hidden" name="norequestkpi" id="norequestkpi">
					<input type="hidden" name="targetkpistatus" id="targetkpistatus">
					
					<div class="row">
						<div class="col-6">
							<table class="" style="width:100%">
								<tr><td>USERID </td>		<td><b><span id="span_userid"></span></b></td></tr>
								<tr><td>Nama </td>			<td><b><span id="span_nama"></span></b></td></tr>
								<tr><td>KPI Category</td>	<td><b><span id="span_divisionname"></span></b></td></tr>
								<tr><td>Position </td>		<td><b><span id="span_positionname"></span></b></td></tr>
								<tr><td>Training </td>		<td><input type="checkbox" name="training" id="training" class="form-input"></td></tr>
								<tr><td>Status </td>		<td><b><span id="span_targetkpistatus"></span> <span id="span_norequestkpi"></span></b></td></tr>
							</table>
						</div>
						
						<div class="col-6">
							<table id="table_history" class="table table-bordered table-sm">
								<thead>
									<tr>
										<th width="20%">History Date</th>
										<th width="20%">History Name</th>
										<th width="20%">UserName</th>
										<th width="20%">Email Approval</th>
										<th width="20%">History Note</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div>
					
					<div class="row">
						<div class="col-6">
							TEMPLATE :
							<select id="template_kpi" name="template_id" class="form-input" style="min-width:300px">
								<option value="">MANUAL</option>
							</select>
						</div>
						<div class="col-6" style="text-align:right">
						</div>
						<div class="col-12">
							<input type="hidden" name="kode_target" id="kode_target" value="">
							<input type="hidden" name="no_request" id="no_request" value="">
							<div style="overflow-x:scroll">
								<table id="table_kpi" class="table table-bordered table-sm table-edit">
									<thead>
										<tr>
											<th width="*">KEY PERFORMANCE INDICATOR</th>
											<th width="10%">DESKRIPSI</th>
											<th width="10%" class="target1">TARGET WEEK 1</th>
											<th width="10%" class="target2">TARGET WEEK 2</th>
											<th width="10%" class="target3">TARGET WEEK 3</th>
											<th width="10%" class="target4">TARGET WEEK 4</th>
											<th width="10%" class="target5">TARGET WEEK 5</th>
											<th width="10%" class="target6">TARGET WEEK 6</th>
											<th width="10%">TARGET</th>
											<th width="5%">BOBOT (%)</th>
											<th width="2%" class="form-edit"></th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
							<button type="button" id="btn_tambah_baris" class="btn-primary form-edit nontemplate" onclick="javascript:tambah_baris()">TAMBAH BARIS</button>
							<span style="float:right">
								TOTAL BOBOT <input type="text" id="total_bobot" style="width:60px;text-align:center" readonly>
							</span>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="col-12">
						<small id="LastModified" style="float:left;text-align:left"></small>
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
	let data_bawahan;
	let table_target;
	
	let th;
	let bl;
	
	let master_kpi;
	let idx;
	let cur_template='';
	var w;
	table_target = $('#table_target').DataTable({
		"pageLength"    : 10,
		"searching"     : true,
		"columnDefs": [
		{ targets: 'no-sort', orderable: false },
		{ targets: 'col-hide', visible: false }
		],
		"dom": '<"top">rt<"bottom"ip><"clear">',
		"order": [[1, 'desc']],
	});
	
	$(document).ready(function(){   
		// // $(".monthPicker").datepicker({
			// // dateFormat: 'MM yy',
			// // changeMonth: true,
			// // changeYear: true,
			// // showButtonPanel: true,
			// // immediateUpdates: true,
			// // todayHighlight: false,
			// // onClose: function(dateText, inst) {
				// // var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
				// // var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
				// // $(this).datepicker('setDate', new Date(year, month, 1));
				// // // $(this).val($.datepicker.formatDate('MM yy', new Date(year, month, 1)));
			// // },
		// // });
		
		// // $(".monthPicker").focus(function () {
			// // $(".ui-datepicker-calendar").hide();
			// // $("#ui-datepicker-div").position({
				// // my: "center top",
				// // at: "center bottom",
				// // of: $(this)
			// // });
		// // });
		
		
	$(".monthpicker").datepicker( {
		format: "MM yyyy",
		viewMode: "months", 
		minViewMode: "months",
		autoclose: true,
	});
	
	
		
		$("#template_kpi").change(function () { 
			 
			var selected = $(this).val();
			
			if(cur_template!=selected){

				var totalBobotValue = $('#total_bobot').val(); 
				if (totalBobotValue>0)
				{
					if (!confirm('Ingin ganti template? Target akan direset kembali!')) {
						$(this).val(cur_template);
					} 
					else{
						if(selected!=''){
							master_template_kpi_detail(selected);
						}
						else{
							$('#table_kpi tbody').html('');
							tambah_baris();
						}
						cur_template=selected;
					} 
				}
				else
				{
					if(selected!=''){
						master_template_kpi_detail(selected);
					}
					else{
						$('#table_kpi tbody').html('');
						tambah_baris();
					}
					cur_template=selected; 
				}  
			}
			$('#btn_tambah_baris').prop('disabled', (selected=='')?false:true);
		});
		
		load_bawahan();
		
		$('#div_duplicate').hide();
	});
	
	$(document).on("input", ".numeric", function() {
		this.value = format_currency(this.value);
	});
	
	$(document).on("blur", ".numeric", function() {
		var x  = parseFloat(this.value.replace(',','')) || 0;
		this.value = format_currency(parseFloat(x).toFixed(2));
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
	
	$(document).on("click", "#chk_duplicate", function() {
		var c = this.checked;
		if(c){
			$('#div_duplicate').show();
		}
		else{
			$('#div_duplicate').hide();
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
		hitung_bobot();
	});
	
	$(document).on("click", ".btn-edit" , function() {
		load_target($(this), 'edit');
	});
	
	$(document).on("click", ".btn-view" , function() {
		load_target($(this), 'view');
	});
	
	$(document).on("blur", ".target-input", function() {
		var x = this.id.split('_');
		var id = x[1];
		
		var week1 = $('#week1_'+id).val() || '0';
		var week2 = $('#week2_'+id).val() || '0';
		var week3 = $('#week3_'+id).val() || '0';
		var week4 = $('#week4_'+id).val() || '0';
		var week5 = $('#week5_'+id).val() || '0';
		var week6 = $('#week6_'+id).val() || '0';
		
		week1 = parseFloat(week1.replace(',','')) || 0;
		week2 = parseFloat(week2.replace(',','')) || 0;
		week3 = parseFloat(week3.replace(',','')) || 0;
		week4 = parseFloat(week4.replace(',','')) || 0;
		week5 = parseFloat(week5.replace(',','')) || 0;
		week6 = parseFloat(week6.replace(',','')) || 0;
		
		var target = week1 + week2 + week3 + week4 + week5 + week6;
		$('#target_'+id).val(format_currency(target.toFixed(2)));
	});
	
	$(document).ready(function() {
		$("#form_request").submit(function() {
			if(confirm("Request Target KPI ini?")){
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
						console.log(JSON.stringify(data));
						$('.loading').hide();
						if(data.result=='success'){
							alert('SUCCESS. Request berhasil dikirim!');
							load_bawahan();
						}
						else{
							alert('FAILED. '+data.error);
						}
					},
					error: function (request, status, error) {
						alert('ERROR:\n'+request.responseText);
						$('.loading').hide();
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
			$('.form-input').removeAttr("disabled");
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
					$('.loading').hide();
					if(data.result=='success'){
						alert('SUCCESS. Target KPI berhasil disimpan!');
						load_bawahan();
						$('#modal_edit').modal('hide');
					}
					else{
						alert('FAILED. '+data.error);
					}
				},
				error: function (request, status, error) {
					alert('ERROR:\n'+request.responseText);
					$('.loading').hide();
				}
			});
			event.preventDefault();
		});
	});
	
	function duplicate(){
	
	
		// console.log(data_bawahan);
		// return;
		
		var period =  $('#periode').val().split(' ');
		bl = monthNames.indexOf(period[0]);
		th = period[1];
		var tgl = new Date(th, bl, 1);
		var kategori =  $('#kpi_kategori').val();
		var nama_kategori =  $('#kpi_kategori option:selected').text();
	
		
		var userid_duplicate = [];
		for(i=0;i<data_bawahan.length;i++){		
			// jika kategori  tidak sama
			if( kategori!=data_bawahan[i].DivisionID){
				alert('Kategori di filter ('+nama_kategori+')  dan kategori di list bawahan ('+data_bawahan[i].DivisionName+') tidak sama!\nKlik View untuk load ulang list bawahan!');
				return;
			}
			// jika list dan periode filter tidak sama
			if(th+'-'+((bl<10)?0:'')+bl+'-01'!=data_bawahan[i].Tgl_Awal.substring(0,10)){
				alert('Periode ('+th+'-'+((bl<10)?0:'')+bl+'-01)  dan List bawahan ('+data_bawahan[i].Tgl_Awal+') tidak sama!\nKlik View untuk load ulang list bawahan!');
				return;
			}
			if(data_bawahan[i].TargetKPIStatus!='WAITING FOR APPROVAL' && data_bawahan[i].TargetKPIStatus!='APPROVED'){
				// userid_duplicate.push({userid:data_bawahan[i].USERID.toString(), kodetarget:data_bawahan[i].Kode_Target.toString()});
				userid_duplicate.push(data_bawahan[i].USERID.toString());
			}
		}
		
		if(userid_duplicate.length==0){
			alert('Tidak ada data yang bisa diduplikat!');
			return;
		}
		
		// console.log(userid_duplicate);
		// return;
		
		var period_duplicate =  $('#periode_duplicate').val().split(' ');
		bl_duplicate = monthNames.indexOf(period_duplicate[0]);
		th_duplicate = period_duplicate[1];
		
		var tgl_duplicate = new Date(th_duplicate, bl_duplicate , 1);
		if(tgl_duplicate>=tgl){
			alert('Periode duplicate tidak boleh lebih dari atau sama dengan periode yang dipilih!');
			return;
		}
		else{
			if (confirm('Ingin duplikat target dari periode '+ $('#periode_duplicate').val() + ' ke periode ' + $('#periode').val()+'?\n-Target yang statusnya WAITING FOR APPROVAL dan APPROVED sudah tidak diduplikat \n-Hanya target dari periode sebelumnya yang statusnya APPROVED yang bisa diduplikat')) {
			
			
				
				
				$.ajax({ 
					type: 'POST', 
					url: '<?php echo site_url('Targetkpikaryawan/duplicate') ?>',  
					data: {
						'kategori': kategori,
						'nama_kategori': nama_kategori,
						'bl': bl,
						'th': th,
						'bl_duplicate': bl_duplicate,
						'th_duplicate': th_duplicate,
						'userid': userid_duplicate
					}, 
					dataType: 'json',
					beforeSend: function() {
						$('.loading').show();
					},
					success: function (data) {
						$('.loading').hide();
						if(data.result=='success'){
							alert('SUCCESS. Data berhasil diduplicate!');
							$('#chk_duplicate').prop('checked', false);
							$('#div_duplicate').hide();
							load_bawahan();
						}
						else{
							alert('FAILED. '+data.error);
						}
					},
					error: function (request, status, error) {
						alert('GAGAL DUPLICATE:\n'+request.responseText);
						$('.loading').hide();
					}
				});
			}
		}
	}
	
	function load_bawahan(){
		var kategori =  $('#kpi_kategori').val();
		var nama_kategori =  $('#kpi_kategori option:selected').text();
		$('#kpi_kategori_name').val(nama_kategori);
		
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
			url: '<?php echo site_url('Targetkpikaryawan/ListTargetKPIKaryawan') ?>',  
			data: {
				'kategori': kategori,
				'nama_kategori': nama_kategori,
				'th': th,
				'bl': bl
			}, 
			dataType: 'json',
			success: function (result) {
				// console.log(result);
				$('.loading').hide();
				table_target = $('#table_target').DataTable(); 
				table_target.clear().draw();
				
				data_bawahan = result.bawahan;
				for(i=0;i<result.bawahan.length;i++){
					var data = result.bawahan[i];
					
					var view_disabled = (data.TargetKPIStatus=='UNSAVED') ? 'disabled' : '';
					var edit_disabled = (data.TargetKPIStatus=='APPROVED' || data.TargetKPIStatus=='WAITING FOR APPROVAL') ? 'disabled' : '';
					var cancel_disabled = (data.TargetKPIStatus=='APPROVED' || data.TargetKPIStatus=='WAITING FOR APPROVAL') ? '' : 'disabled';
					
					var dt = '';
					dt+='data-userid="'+data.USERID+'"';
					dt+='data-nama="'+data.Nama+'"';
					dt+='data-DivisionID="'+data.DivisionID+'"';
					dt+='data-DivisionName="'+data.DivisionName+'"';
					dt+='data-PositionID="'+data.PositionID+'"';
					dt+='data-PositionName="'+data.PositionName+'"';
					dt+='data-TglAwal="'+data.Tgl_Awal+'"';
					dt+='data-tglakhir="'+data.Tgl_Akhir+'"';
					dt+='data-kodetarget="'+data.Kode_Target+'"';
					dt+='data-training="'+data.Training+'"';
					dt+='data-withtargetkpi="'+data.WithTargetKPI+'"';
					dt+='data-norequestkpi="'+data.NoRequestKPI+'"';
					dt+='data-targetkpistatus="'+data.TargetKPIStatus+'"';
					dt+='data-templateid="'+data.template_id+'"';
					
					var check_disabled = (data.TargetKPIStatus=='SAVED' || data.TargetKPIStatus=='SAVED(MODIFIED)' || data.TargetKPIStatus=='CANCELLED' || data.TargetKPIStatus=='WAITING FOR APPROVAL') ? '' : 'disabled';
					
					table_target.row.add([
					'<input type="checkbox" name="kode_target[]" value="'+data.Kode_Target+'" class="pilih" '+check_disabled+'>',
					data.USERID,
					data.Nama,
					data.PositionName,
					(data.Training=='1')?'Y':'N',
					data.TotalBobot,
					data.TargetKPIStatus,
					data.NoRequestKPI,
					'<button type="button" class="btn-success btn-view" '+dt+' '+view_disabled+'>VIEW</button> '+
					'<button type="button" class="btn-primary btn-edit" '+dt+' '+edit_disabled+'>EDIT</button> '+
					'<button type="button" class="btn-danger" onclick="javascript:cancel_target(\''+data.Kode_Target+'\',\''+data.NoRequestKPI+'\')" '+cancel_disabled+'>CANCEL</button>'
					]);
					
				}
				table_target.draw();
				master_kpi = result.master_kpi;
				create_select_template_kpi(result.template);
				
				//atasan
				$('#atasan_name').val(result.atasan.Name);	
				$('#atasan_email').val(result.atasan.UserEmail);
				$('#atasan_name_span').text(result.atasan.Name);	
				$('#atasan_email_span').text(result.atasan.UserEmail);	
				
			},
			error: function (request, status, error) {
				alert('ERROR:\n'+request.responseText);
				$('.loading').hide();
			}
		});
	}
	
	function load_target(x, mode){
		var atasan = $('#atasan_userid').text();
		
		var userid = x.attr('data-userid');
		var nama = x.attr('data-nama');
		var divisionid = x.attr('data-divisionid');
		var divisionname = x.attr('data-divisionname');
		var positionid = x.attr('data-positionid');
		var positionname = x.attr('data-positionname');
		var tglawal = x.attr('data-tglawal');
		var tglakhir = x.attr('data-tglakhir');
		var kodetarget = x.attr('data-kodetarget');
		var training = x.attr('data-training');
		var withtargetkpi = x.attr('data-withtargetkpi');
		var norequestkpi = x.attr('data-norequestkpi');
		var targetkpistatus = x.attr('data-targetkpistatus');
		var templateid = x.attr('data-templateid');
		
		var span_norequestkpi = (norequestkpi!='') ? ' ['+norequestkpi+']' : '';
		
		$('#atasan').val(atasan);
		$('#userid').val(userid);
		$('#nama').val(nama);
		$('#divisionid').val(divisionid);
		$('#divisionname').val(divisionname);
		$('#positionid').val(positionid);
		$('#positionname').val(positionname);
		$('#tglawal').val(tglawal);
		$('#tglakhir').val(tglakhir);
		$('#kodetarget').val(kodetarget);
		$('#training').prop('checked',parseInt(training));
		$('#withtargetkpi').val(withtargetkpi);
		$('#norequestkpi').val(norequestkpi);
		$('#targetkpistatus').val(targetkpistatus);
		$('#template_kpi').val(templateid);
		$('#span_userid').html(userid);
		$('#span_nama').html(nama);
		$('#span_divisionname').html(divisionname);
		$('#span_positionname').html(positionname);
		$('#span_targetkpistatus').html(targetkpistatus+span_norequestkpi);
		$('#training').prop('disabled',false);
		cur_template =templateid;
		// var disabled = (templateid!='') ? 'disabled' : '';
		// var xdisabled = '';
		// if(mode=='view'){
			// disabled = 'disabled';
			// xdisabled = 'disabled';
		// }
		
		$('.loading').show();
		idx = 0;
		$('#table_kpi tbody').html('');
		$('#table_history tbody').html('<tr><td colspan="5" align="center">Tidak ada history</td></tr>');
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url('Targetkpikaryawan/TargetKaryawan_KPI_AmbilTargetDetail') ?>',  
			data: {
				'KodeTarget': kodetarget,
				'NoRequestKPI': norequestkpi
			}, 
			dataType: 'json',
			success: function (result) {
				$('.loading').hide();
				for(i=0;i<result.detail.length;i++){
					idx++;
					var data = result.detail[i];
					var tbody = '';
					tbody+='<tr id="baris_'+idx+'">'+
					'<td>'+create_select_master_kpi(master_kpi, data.KPICode)+'</td>'+
					'<td><input type="text" name="deskripsi[]" class="form-input justify" value="'+data.KPINote+'"></td>'+
					'<td class="target1"><input type="text" name="target1[]" id="week1_'+idx+'" value="'+parseFloat(data.TargetWeek1).toFixed(2)+'" class="form-input target-input numeric"></td>'+
					'<td class="target2"><input type="text" name="target2[]" id="week2_'+idx+'" value="'+parseFloat(data.TargetWeek2).toFixed(2)+'" class="form-input target-input numeric"></td>'+
					'<td class="target3"><input type="text" name="target3[]" id="week3_'+idx+'" value="'+parseFloat(data.TargetWeek3).toFixed(2)+'" class="form-input target-input numeric"></td>'+
					'<td class="target4"><input type="text" name="target4[]" id="week4_'+idx+'" value="'+parseFloat(data.TargetWeek4).toFixed(2)+'" class="form-input target-input numeric"></td>'+
					'<td class="target5"><input type="text" name="target5[]" id="week5_'+idx+'" value="'+parseFloat(data.TargetWeek5).toFixed(2)+'" class="form-input target-input numeric"></td>'+
					'<td class="target6"><input type="text" name="target6[]" id="week6_'+idx+'" value="'+parseFloat(data.TargetWeek6).toFixed(2)+'" class="form-input target-input numeric"></td>'+
					'<td><input type="text" name="target[]" value="'+data.KPITarget+'" id="target_'+idx+'" class="form-input target" readonly></td>'+
					'<td><input type="text" name="bobot[]" value="'+data.KPIBobot+'" class="form-input nontemplate numeric bobot" required></td>'+
					'<td class="form-edit"><button type="button" class="btn-danger form-input nontemplate" onclick="hapus_baris('+idx+')">X</button></td>'+
					'</tr>';
					$('#table_kpi tbody').append(tbody);
				}
				
				if(result.history.length>0){
					$('#table_history tbody').html('');
					for(i=0;i<result.history.length;i++){
						idx++;
						var data = result.history[i];
						var tbody = '';
						tbody+='<tr>'+
						'<td>'+data.HistoryDate+'</td>'+
						'<td>'+data.HistoryName+'</td>'+
						'<td>'+data.UserName+'</td>'+
						'<td>'+data.ApproverEmail+'</td>'+
						'<td>'+data.HistoryNote+'</td>'+
						'</tr>';
						$('#table_history tbody').append(tbody);
					}
				}
				
				if(result.detail.length==0){
					tambah_baris();
				}
				
				hitung_bobot();	
				set_kolom_minggu();
				
				
				// $('#btn_tambah_baris').prop('disabled', disabled); 
				// if(mode=='view'){$('.form-edit').hide();}
				// else {$('.form-edit').show();}
				// $('#training').prop('disabled',(mode=='view')?true:false);
				// $('#template_kpi').prop('disabled',(mode=='view')?true:false);
				// $('.target-input').prop('disabled',(mode=='view')?true:false);
				
				
				if(mode=='view'){
					$('.form-input').prop('disabled',true);
					$('.form-edit').hide();
				}
				else {
					$('.form-input').prop('disabled',false);
					$('.form-edit').show();
					$('.nontemplate').prop('disabled',(templateid=='')?false:true);
				}
				
				$('#modal_edit').modal('show');
			},
			error: function (request, status, error) {
				alert('ERROR:\n'+request.responseText);
				$('.loading').hide();
			}
		});
		
	}
	
	function cancel_target(KodeTarget, NoRequestKPI){
		var note = prompt("Alasan cancel request ini?", ""); 
		if (note!='' && note!=null){
			$('.loading').show();
			$.ajax({ 
				type: 'POST', 
				url: '<?php echo site_url('Targetkpikaryawan/cancel') ?>',  
				data: {
					'kode_target': KodeTarget,
					'norequestkpi': NoRequestKPI,
					'note': note,
				}, 
				dataType: 'json',
				success: function (data) {
					$('.loading').hide();
					if(data.result=='success'){
						alert('SUCCESS. Request berhasil dicancel!');
						load_bawahan();
					}
					else{
						alert('FAILED. '+data.error);
					}
				},
				error: function (request, status, error) {
					alert('ERROR:\n'+request.responseText);
					$('.loading').hide();
				}
			});
		}
	}
	
	function master_template_kpi_detail(template_id){
		$('.loading').show();
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url('Targetkpikaryawan/Master_Template_Target_KPI_AmbilList_Detail') ?>',  
			data: {
				'template_id': template_id
			}, 
			dataType: 'json',
			success: function (result) {
				$('.loading').hide();
				$('#table_kpi tbody').html('');
				for(i=0;i<result.length;i++){
					var data = result[i];
					tambah_baris(data.kpi_code, data.kpi_bobot);
				}
				hitung_bobot();
			},
			error: function (request, status, error) {
				alert('ERROR:\n'+request.responseText);
				$('.loading').hide();
			}
		});
	}
	
	function weekCount(year, month_number) {
		// month_number is in the range 1..12
		var firstOfMonth = new Date(year, month_number-1, 1);
		var lastOfMonth = new Date(year, month_number, 0);
		var used = firstOfMonth.getDay() + lastOfMonth.getDate();
		return Math.ceil( used / 7);
	}
	
	function tambah_baris(kpi_code='',bobot=0) {
		var template_id = $('#template_kpi').val();
		var disabled = (template_id!='') ? 'disabled':'';
		var tbody = '';
		idx++;
		tbody+='<tr id="baris_'+idx+'">'+
		'<td>'+create_select_master_kpi(master_kpi,kpi_code)+'</td>'+
		'<td><input type="text" name="deskripsi[]" class="justify"></td>'+
		'<td class="target1"><input type="text" name="target1[]" id="week1_'+idx+'" class="target-input numeric"></td>'+
		'<td class="target2"><input type="text" name="target2[]" id="week2_'+idx+'" class="target-input numeric"></td>'+
		'<td class="target3"><input type="text" name="target3[]" id="week3_'+idx+'" class="target-input numeric"></td>'+
		'<td class="target4"><input type="text" name="target4[]" id="week4_'+idx+'" class="target-input numeric"></td>'+
		'<td class="target5"><input type="text" name="target5[]" id="week5_'+idx+'" class="target-input numeric"></td>'+
		'<td class="target6"><input type="text" name="target6[]" id="week6_'+idx+'" class="target-input numeric"></td>'+
		'<td><input type="text" name="target[]" id="target_'+idx+'" class="target disabled" readonly></td>'+
		'<td><input type="text" name="bobot[]" class="form-input numeric bobot" value="'+bobot+'" required '+disabled+'></td>'+
		'<td><button type="button" class="btn-danger" onclick="hapus_baris('+idx+')" '+disabled+'>X</button></td>'+
		'</tr>';
		$('#table_kpi tbody').append(tbody);
		
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
	
	function create_select_master_kpi(data, selected=''){
		var template_id = $('#template_kpi').val();
		var disabled = (template_id!='') ? 'disabled':'';
		var z = '<select name="KPICode[]" class="form-input nontemplate w100 '+disabled+'" required '+disabled+'>';
		z+='<option value=""></option>';
		for(j=0;j<master_kpi.length;j++){
			var x = master_kpi[j];
			var s = (x.KPICode==selected)?'selected':'';
			z+='<option value="'+x.KPICode+'" '+s+'>'+x.KPIName+'</option>';
		}
		z+='</select>';
		return z;
	}
	
	function create_select_template_kpi(data){
		var html='<option value="">MANUAL</option>';
		for(i=0;i<data.length;i++){
			var x = data[i];
			html+='<option value="'+x.template_id+'">'+x.template_name+'</option>';
		}
		$('#template_kpi').html(html);
	}
	
	function hapus_baris(i){
		if (confirm("Ingin Hapus Baris Ini?")) {
			$('#baris_'+i).remove();
		}
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
			bobot+= parseFloat($(this).val().replace(',','')) || 0;
		});
		if(bobot!=100){
			msg = 'Bobot harus 100%!';
		}
		return msg;
	}
	
	function hitung_bobot(){
		var bobot = 0;
		$('.bobot').each(function(i, obj) {
			bobot+= parseFloat($(this).val().replace(',','')) || 0;
		});
		
		$('#total_bobot').val(parseFloat(bobot).toFixed(0));
		$('#span_total_bobot').text(parseFloat(bobot).toFixed(0));
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
	
	function format_currency(x){
		var str = x.toString().replace(/[^0-9.]/g,'');
		x = str.split('.'); 
		x1 = x[0].length > 0 ? x[0] : '0';
		x2 = x.length > 1 ? '.' + x[1] : '';
		var rgx = /(\d+)(\d{3})/; 
		while (rgx.test(x1)) { 
			x1 = x1.replace(rgx, '$1' + ',' + '$2'); 
		} 
		return x1 + x2;
	}
	

</script>

