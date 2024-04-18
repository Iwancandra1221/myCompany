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
	
	.table-edit td input.disabled, .table-edit td input[disabled]{
		background: #fff;
		border: 1px solid #fff;
	}
	
	.table-edit td input[readonly]{
		background: none;
		border: 0;	
	}
	.table-edit td input.justify{
	text-align: left;
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
  
	select.form-control{
		padding:4px 12px;
	}	
</style>

<div class="container">
	<div class="form_title">
		<div style="text-align:center;">
			TARGET KPI V2
		</div>
	</div>
	<br>
	<form id="form_request" class="form-horizontal" action="<?php echo site_url('Targetkpiv2/sendrequest') ?>" method="POST" target="_blank">
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
				</select>
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
				<input type="hidden" name="kpicategory" id="kpi_category">
				<input type="text" name="kpicategoryname" id="kpi_category_name" class="form-control" readonly>
			</div>
			<div class="col-3">
				<label>Jenis KPI :</label>
				<input type="text" name="jeniskpi" id="jeniskpi" class="form-control" readonly>
			</div>
		</div>
		<div class="row">
			<div class="col-12">
				<table id="table_target" class="table table-bordered">
					<thead>
						<tr>
							<th width="2%" class="no-sort"><input type="checkbox" id="pilih_semua"></th>
							<th width="10%">USERID</th>
							<th width="*">NAMA</th>
							<th width="20%">POSISI</th>
							<th width="10%">LEVEL</th>
							<th width="5%">TOTAL BOBOT</th>
							<th width="10%">STATUS TERAKHIR</th>
							<th width="10%">NO REQUEST</th>
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
			<form id="form_save" class="form-horizontal" action="<?php echo site_url('Targetkpiv2/save') ?>" method="POST">
				<div class="modal-header form_title">
					<center>
						TARGET KPI V2
					</center>
				</div>
				<div class="modal-body p20">
					<input type="hidden" name="kode_lokasi" id="kode_lokasi">
					<input type="hidden" name="cabang" id="cabang">
					<input type="hidden" name="atasan" id="atasan">
					<input type="hidden" name="userid" id="userid">
					<input type="hidden" name="nama" id="nama">
					<input type="hidden" name="kpicategory" id="kpicategory">
					<input type="hidden" name="kpicategoryname" id="kpicategoryname">
					<input type="hidden" name="divisionid" id="divisionid">
					<input type="hidden" name="divisionname" id="divisionname">
					<input type="hidden" name="positionid" id="positionid">
					<input type="hidden" name="positionname" id="positionname">
					<input type="hidden" name="emplevelid" id="emplevelid">
					<input type="hidden" name="emplevel" id="emplevel">
					<input type="hidden" name="tglawal" id="tglawal">
					<input type="hidden" name="tglakhir" id="tglakhir">
					<input type="hidden" name="kodetarget" id="kodetarget">
					<input type="hidden" name="withtargetkpi" id="withtargetkpi">
					<input type="hidden" name="norequestkpi" id="norequestkpi">
					<input type="hidden" name="targetkpistatus" id="targetkpistatus">
					
					<div class="row">
						<div class="col-6">
							<table class="w100">
								<tr><td width="150px">USERID / Nama </td><td><b><span id="span_nama"></span> [<span id="span_userid"></span>]</b></td></tr>
								<tr><td>Position </td>		<td><b><span id="span_positionname"></span></b></td></tr>
								<tr><td>Level </td>		<td><b><span id="span_level"></span></b></td></tr>
							</table>
						</div>
						
						<div class="col-6">
							<table class="w100">
								<tr><td width="150px">KPI Category</td>	<td><b><span id="span_kpicategoryname"></span></b></td></tr>
								<tr><td>Division</td>	<td><b><span id="span_divisionname"></span></b></td></tr>
								<tr><td>Status </td><td><b><span id="span_targetkpistatus"></span> <span id="span_norequestkpi"></span></b></td></tr>
							</table>
						</div>
					</div>
					
					<div class="row">
						<div class="col-6">
							<table class="w100">
								<tr>
									<td width="150px">Catatan </td>
									<td><input type="text" name="catatan" id="catatan" class="form-control form-input"></td>
								</tr>
							</table>
						</div>
						<div class="col-6">
							<table class="w100">
								<tr>
								<td width="150px">Template </td>
								<td>
									<select id="template_kpi" name="template_id" class="form-control form-input">
										<option value="">MANUAL</option>
									</select>
								</td>
								</tr>
							</table>
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
											<th width="10%" class="target1">TARGET<br>WEEK 1</th>
											<th width="10%" class="target2">TARGET<br>WEEK 2</th>
											<th width="10%" class="target3">TARGET<br>WEEK 3</th>
											<th width="10%" class="target4">TARGET<br>WEEK 4</th>
											<th width="10%" class="target5">TARGET<br>WEEK 5</th>
											<th width="10%" class="target6">TARGET<br>WEEK 6</th>
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
		"language": {
			"emptyTable": "Data EMPTY / Klik VIEW untuk load data"
		}
	});
	
	$(document).ready(function(){  
		$("#form_request").submit(function() {
			event.preventDefault();
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
					}
				});
			}
		});
		
		$("#form_save").submit(function() {
			event.preventDefault();
			
			var check = validasi();
			if(check!=''){
				alert(check);
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
				}
			});
		});
		
		$("#template_kpi").change(function () {
			var selected = $(this).val();
			if(cur_template!=selected){
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
			$('#btn_tambah_baris').prop('disabled', (cur_template=='')?false:true);
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
		var x  = parseFloat(this.value.replace(/,/g,'')) || 0;
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
		
		week1 = parseFloat(week1.replace(/,/g,'')) || 0;
		week2 = parseFloat(week2.replace(/,/g,'')) || 0;
		week3 = parseFloat(week3.replace(/,/g,'')) || 0;
		week4 = parseFloat(week4.replace(/,/g,'')) || 0;
		week5 = parseFloat(week5.replace(/,/g,'')) || 0;
		week6 = parseFloat(week6.replace(/,/g,'')) || 0;
		
		var target = week1 + week2 + week3 + week4 + week5 + week6;
		$('#target_'+id).val(format_currency(target.toFixed(2)));
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
		// var kategori =  $('#kpi_category').val();
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
			url: '<?php echo site_url('Targetkpiv2/ListTargetkpiv2') ?>',  
			data: {
				'userid': spvuserid,
				// 'kategori': kategori,
				'divisionid': divisionid,
				'divisionname': divisionname,
				'th': th,
				'bl': bl
			}, 
			dataType: 'json',
			success: function (res) {
				// console.log(res);
				console.log(JSON.stringify(res));
				$('.loading').hide();
				table_target = $('#table_target').DataTable(); 
				table_target.clear().draw();
				if(res.result=='success'){
					$('#kpi_category').val(res.kpicategory.KPICategory);
					$('#kpi_category_name').val(res.kpicategory.KPICategoryName);
					$('#jeniskpi').val(res.kpicategory.Jenis);
				
					for(i=0;i<res.bawahan.length;i++){
						var data = res.bawahan[i];
						
						var view_disabled = (data.TARGETKPISTATUS=='UNSAVED') ? 'disabled' : '';
						var edit_disabled = (data.TARGETKPISTATUS=='APPROVED' || data.TARGETKPISTATUS=='WAITING FOR APPROVAL') ? 'disabled' : '';
						var cancel_disabled = (data.TARGETKPISTATUS=='APPROVED' || data.TARGETKPISTATUS=='WAITING FOR APPROVAL') ? '' : 'disabled';
						
						var dt = '';
						dt+='data-userid="'+data.USERID+'"';
						dt+='data-nama="'+data.NAME+'"';
						dt+='data-divisionid="'+data.DIVISIONID+'"';
						dt+='data-divisionname="'+data.DIVISIONNAME+'"';
						dt+='data-positionid="'+data.POSITIONID+'"';
						dt+='data-positionname="'+data.POSITIONNAME+'"';
						dt+='data-emplevelid="'+data.EMPLEVELID+'"';
						dt+='data-emplevel="'+data.EMPLEVEL+'"';
						dt+='data-tglawal="'+data.TGL_AWAL+'"';
						dt+='data-tglakhir="'+data.TGL_AKHIR+'"';
						dt+='data-kodetarget="'+data.KODE_TARGET+'"';
						dt+='data-withtargetkpi="'+data.WITHTARGETKPI+'"';
						dt+='data-norequestkpi="'+data.NOREQUESTKPI+'"';
						dt+='data-targetkpistatus="'+data.TARGETKPISTATUS+'"';
						dt+='data-templateid="'+data.TEMPLATE_ID+'"';
						dt+='data-catatan="'+data.CATATAN+'"';
						
						var check_disabled = (data.TARGETKPISTATUS=='SAVED' || data.TARGETKPISTATUS=='SAVED(MODIFIED)' || data.TARGETKPISTATUS=='CANCELLED' || data.TARGETKPISTATUS=='REJECTED') ? '' : 'disabled';
						
						table_target.row.add([
						'<input type="checkbox" name="kode_target[]" value="'+data.KODE_TARGET+'" class="pilih" '+check_disabled+'>',
						data.USERID,
						data.NAME,
						data.POSITIONNAME,
						data.EMPLEVEL,
						data.TOTALBOBOT,
						data.TARGETKPISTATUS,
						data.NOREQUESTKPI,
						'<button type="button" class="btn-success btn-view" '+dt+' '+view_disabled+'>VIEW</button> '+
						'<button type="button" class="btn-primary btn-edit" '+dt+' '+edit_disabled+'>EDIT</button> '+
						'<button type="button" class="btn-danger" onclick="javascript:cancel_target(\''+data.KODE_TARGET+'\',\''+data.NOREQUESTKPI+'\')" '+cancel_disabled+'>CANCEL</button>'
						]);
						
					}
					table_target.draw();
					master_kpi = res.master_kpi;
					create_select_template_kpi(res.template);
					
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
	
	function load_target(x, mode){
		var period =  $('#periode').val().split('-');
		var bl = period[1] - 1;
		var th = period[0];
		
		var kodelokasi = $('#branch_id').val();
		var cabang = $('#branch_id option:selected').text();
	
		var atasan = $('#spvuserid').val();
		var kpicategory = $('#kpi_category').val();
		var kpicategoryname = $('#kpi_category_name').val();
		
		var userid = x.attr('data-userid');
		var nama = x.attr('data-nama');
		var divisionid = x.attr('data-divisionid');
		var divisionname = x.attr('data-divisionname');
		var positionid = x.attr('data-positionid');
		var positionname = x.attr('data-positionname');
		var emplevelid = x.attr('data-emplevelid');
		var emplevel = x.attr('data-emplevel');
		var tglawal = x.attr('data-tglawal');
		var tglakhir = x.attr('data-tglakhir');
		var kodetarget = x.attr('data-kodetarget');
		// var training = x.attr('data-training');
		var withtargetkpi = x.attr('data-withtargetkpi');
		var norequestkpi = x.attr('data-norequestkpi');
		var targetkpistatus = x.attr('data-targetkpistatus');
		var templateid = x.attr('data-templateid');
		var catatan = x.attr('data-catatan');
		
		var span_norequestkpi = (norequestkpi!='') ? ' ['+norequestkpi+']' : '';
		
		$('#kodelokasi').val(kodelokasi);
		$('#cabang').val(cabang);
		$('#atasan').val(atasan);
		$('#userid').val(userid);
		$('#nama').val(nama);
		$('#kpicategory').val(kpicategory);
		$('#kpicategoryname').val(kpicategoryname);
		$('#divisionid').val(divisionid);
		$('#divisionname').val(divisionname);
		$('#positionid').val(positionid);
		$('#positionname').val(positionname);
		$('#emplevelid').val(emplevelid);
		$('#emplevel').val(emplevel);
		$('#tglawal').val(tglawal);
		$('#tglakhir').val(tglakhir);
		$('#kodetarget').val(kodetarget);
		// $('#training').prop('checked',parseInt(training));
		$('#withtargetkpi').val(withtargetkpi);
		$('#norequestkpi').val(norequestkpi);
		$('#targetkpistatus').val(targetkpistatus);
		$('#template_kpi').val(templateid);
		$('#catatan').val(catatan);
		
		$('#span_userid').html(userid);
		$('#span_nama').html(nama);
		$('#span_divisionname').html(divisionname);
		$('#span_kpicategoryname').html(kpicategoryname);
		$('#span_positionname').html(positionname);
		$('#span_level').html(emplevel);
		$('#span_targetkpistatus').html(targetkpistatus+span_norequestkpi);
		
		cur_template =templateid;
		
		$('.loading').show();
		idx = 0;
		$('#table_kpi tbody').html('');
		$('#history').html('');
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url('Targetkpiv2/TargetKaryawan_KPI_AmbilTargetDetail') ?>',  
			data: {
				'KodeTarget': kodetarget,
				'NoRequestKPI': norequestkpi
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
					'<td>'+create_select_master_kpi(master_kpi, data.KPICode)+'</td>'+
					'<td><input type="text" name="kpinote[]" class="form-input justify" value="'+data.KPINote+'"></td>'+
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
				
				var log = '';
				if(result.history.length>0){
					for(i=0;i<result.history.length;i++){
						var data = result.history[i];
						log+='<b>'+result.history[i].HistoryName+'</b>: '+result.history[i].UserName+' ('+result.history[i].Email+') '+result.history[i].HistoryDate+'<br>';
					}
				}
				$('#history').html(log);
				
				if(result.detail.length==0){
					tambah_baris();
				}
				
				hitung_bobot();	
				set_kolom_minggu();
				
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
			}
		});
	}
	
	function cancel_target(KodeTarget, NoRequestKPI){
		var note = prompt("Alasan cancel request ini?", ""); 
		if (note!='' && note!=null){
			$('.loading').show();
			$.ajax({ 
				type: 'POST', 
				url: '<?php echo site_url('Targetkpiv2/cancel') ?>',  
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
				}
			});
		}
	}
	
	function master_template_kpi_detail(template_id){
		$('.loading').show();
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url('Targetkpiv2/Master_Template_Target_KPI_AmbilList_Detail') ?>',  
			data: {
				'template_id': template_id
			}, 
			dataType: 'json',
			success: function (result) {
				console.log(result);
				$('.loading').hide();
				$('#table_kpi tbody').html('');
				for(i=0;i<result.length;i++){
					var data = result[i];
					tambah_baris(data.kpi_code, data.kpi_bobot);
				}
				hitung_bobot();
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
		'<td><input type="text" name="kpinote[]" class="justify"></td>'+
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
		var z = '<select name="kpicode[]" class="form-input kpicode nontemplate w100 '+disabled+'" required '+disabled+'>';
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
		// validasi kpicode tidak boleh sama
		$('.kpicode').each(function(i, obj) {
			var kpicode = $(obj).val();
			var kpiname = $(obj).find("option:selected").text();
			// alert('1.'+kpicode+'='+kpiname);
			var count = 0;
			$('.kpicode').each(function(j, objx) {
				if($(objx).val()==kpicode){
					// alert('2.'+$(objx).val()+'='+kpicode);
					count+=1;
				}
			});
			
			// alert(count);
			if(count>1){
				msg = 'Tidak boleh ada KPI yang sama ('+kpiname+')!';
			}
		});
		
		// validasi target tidak boleh kosong
		if(msg==''){
			$('.target').each(function(i, obj) {
				if($(this).val()=='' || $(this).val()==0){
					msg = 'Target tidak boleh kosong!';
				}
			});
		}
		
		// validasi total bobot harus 100%
		if(msg==''){
			var bobot = 0;
			$('.bobot').each(function(i, obj) {
				bobot+= parseFloat($(this).val().replace(/,/g,'')) || 0;
			});
			if(bobot!=100){
				msg = 'Bobot harus 100%!';
			}
		}
		return msg;
	}
	
	function hitung_bobot(){
		var bobot = 0;
		$('.bobot').each(function(i, obj) {
			bobot+= parseFloat($(this).val().replace(/,/g,'')) || 0;
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
		x = x.toString().replace(/,/g,"") || 0;
		var str = x.replace(/[^0-9.]/g,'');
		console.log('format_currency='+x);
		x = str.split('.'); 
		x1 = x[0].length > 0 ? x[0] : '0';
		x2 = x.length > 1 ? '.' + x[1] : '';
		var rgx = /(\d+)(\d{3})/; 
		while (rgx.test(x1)) { 
			x1 = x1.replace(rgx, '$1' + ',' + '$2'); 
		} 
		// console.log('format_currency return='+x);
		return x1 + x2;
	}
	
	$(".monthpicker").datepicker( {
		format: "MM yyyy",
		viewMode: "months", 
		minViewMode: "months",
		autoclose: true,
	});
		
	</script>

