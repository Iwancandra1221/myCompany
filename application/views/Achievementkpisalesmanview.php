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
	  font-size:90%;
	}
	
	button:disabled, button:disabled:hover{
	cursor: not-allowed;
	color:#555;
	}
	
	.table-edit tr{
		border:0 !important;
	}
	.table-edit th{
		/* font-weight:bold; */
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
	.table-edit td input[type=text]{
		width:100%;
		text-align:right;
	}
	
	.table-edit td input:disabled{
		background: #fff;
		border: 1px solid #fff;	
	}
	.table-edit td input.justify{
	text-align: left;
	}
	
	@media only screen and (max-width: 460px){
		.colMobile{
			padding:10px !important;
			display: block;
		}
	}
</style>

<div class="container">
	<div class="form_title">
		<div style="text-align:center;">
			ACHIEVEMENT KPI SALESMAN
		</div>
	</div>
	<br>
	<form id="form_request" class="form-horizontal" action="<?php echo site_url('Achievementkpisalesman/sendrequest') ?>" method="POST">
		<div class="row">
			<div class="col-3">
				<label>Wilayah :</label>
				<select name="wilayah" id="wilayah" class="form-control" style="width:100%">
					<?php
						foreach($wilayah as $w) {
							echo "<option value='".trim($w['WILAYAH'])."'>".$w['WILAYAH']."</option>";
						}
					?>
				</select>
			</div>
			<div class="col-3">
				<label>KAJUL/MANAGER :</label>
				<select name="kajul" id="kajul" class="form-control" style="width:100%">
					
				</select>
			</div>
			<div class="col-3">
				<label>KPI Category :</label>
				<select name="kpi_kategori" id="kpi_kategori" class="form-control" style="width:100%">
					<?php
						foreach($kategori as $r) {
							echo "<option value='".$r['KPICategory']."'>".$r['KPICategoryName']."</option>";
						}
					?>
				</select>
				<input type="hidden" name="kpi_kategori_name" id="kpi_kategori_name">
				<input type="hidden" name="week" id="week">
			</div>
			<div class="col-2">
				<label>Periode Target :</label>
				<input type="text" name="periode" id="periode" class="form-control monthpicker" style="width:100%" value="<?php echo date('F Y') ?>" autocomplete="off">
			</div>
			
			<div class="col-1">
				<label>&nbsp;</label>
				<br>
				<button type="button" class="btn btn-dark" onclick="javascript:load_bawahan()">View</button>
			</div>
		</div>
		
		<div class="row">
			<div class="col-12">
				<div>
				<button type="submit" id="btn_sendrequest" class="btn-primary" disabled>SEND REQUEST</button>
				<label>EMAIL ATASAN :</label>
				<input type="hidden" name="atasan_name" id="atasan_name" value="<?php echo ISSET($atasan['ATASAN_NAMA']) ? $atasan['ATASAN_NAMA'] : "" ?>">
				<input type="hidden" name="atasan_email" id="atasan_email" value="<?php echo ISSET($atasan['ATASAN_NAMA']) ? $atasan['ATASAN_EMAIL'] : "" ?>">
				<span id="atasan_name_span"><?php echo ISSET($atasan['ATASAN_NAMA']) ? $atasan['ATASAN_NAMA'] : "" ?></span> [<span id="atasan_email_span"><?php echo ISSET($atasan['ATASAN_NAMA']) ? $atasan['ATASAN_EMAIL'] : "" ?></span>]
				
				<button type="button" class="btn-success" style="float:right" onclick="javascript:achievement_import()">IMPORT</button>
				<button type="button" class="btn-warning" style="float:right" onclick="javascript:achievement_export()">EXPORT</button>
			
				</div>
			
				<table id="table_target" class="table table-bordered" summary="table">
					<thead>
						<tr>
							<th scope="col" width="2%" class="no-sort"><input type="checkbox" id="pilih_semua"></th>
							<th scope="col" class="hideOnMobile">KODE SALESMAN</th>
							<th scope="col" class="hideOnMobile">NAMA SALESMAN</th>
							<th scope="col" class="hideOnMobile">POSISI</th>
							<th scope="col" class="hideOnMobile">USERID</th>
							<th scope="col" class="hideOnMobile">TOTAL ACHIEVEMENT</th>
							<th scope="col" class="hideOnMobile">STATUS TERAKHIR</th>
							<th scope="col" class="hideOnMobile">NO REQUEST</th>
							<th scope="col" class="hideOnMobile no-sort" width="18%">AKSI</th>
							<th scope="col" class="colMobile">SALESMAN</th>
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
			<form id="form_save" class="form-horizontal" action="<?php echo site_url('Achievementkpisalesman/save') ?>" method="POST" onkeydown="return event.keyCode != 13">
				<div class="modal-header form_title">
					<div style="text-align:center;">
						ACHIEVEMENT KPI SALESMAN
					</div>
				</div>
				<div class="modal-body p20">
					<input type="hidden" name="atasan" id="atasan">
					<input type="hidden" name="kodesalesman" id="kodesalesman">
					<input type="hidden" name="namasalesman" id="namasalesman">
					<input type="hidden" name="levelsalesman" id="levelsalesman">
					<input type="hidden" name="namalevel" id="namalevel">
					<input type="hidden" name="wilayahsalesman" id="wilayahsalesman">
					<input type="hidden" name="kategori" id="kategori">
					<input type="hidden" name="th" id="th">
					<input type="hidden" name="bl" id="bl">
					<input type="hidden" name="kodetarget" id="kodetarget">
					<input type="hidden" name="withtargetkpi" id="withtargetkpi">
					<input type="hidden" name="norequestkpi" id="norequestkpi">
					<input type="hidden" name="norequestacv" id="norequestacv">
					<input type="hidden" name="acvkpistatus" id="acvkpistatus">
					
					<div class="row">
						<div class="col-6">
							<table class="" style="width:100%" summary="table">
								<tr><th scope="col">Kode Salesman </th>		<th scope="col"><strong><span id="span_kodesalesman"></span></strong></th></tr>
								<tr><th scope="col">Nama </th>			<th scope="col"><strong><span id="span_namasalesman"></span></strong></th></tr>
								<tr><th scope="col">Wilayah </th>		<th scope="col"><strong><span id="span_wilayahsalesman"></span></strong></th></tr>
								<tr><th scope="col">Kategori </th>		<th scope="col"><strong><span id="span_namalevel"></span></strong></th></tr>
								<tr><th scope="col">USERID </th>		<th scope="col"><strong><span id="span_userid"></span></strong></th></tr>
								<tr><th scope="col">Catatan </th>		<th scope="col"><strong><span id="catatan"></span></strong></th></tr>
								<tr><th scope="col">Status </th>		<th scope="col"><strong><span id="span_acvkpistatus"></span> <span id="span_norequestacv"></span></strong></th></tr>
								<!--tr><th scope="col">Template </th>		<th scope="col"><strong><span id="template_name"></span></strong></th></tr-->
							</table>
						</div>
						
						<div class="col-6">
							<table id="table_history" class="table table-bordered table-sm" summary="table">
								<thead>
									<tr>
										<th scope="col" width="20%">History Date</th>
										<th scope="col" width="20%">History Name</th>
										<th scope="col" width="20%">UserName</th>
										<th scope="col" width="20%">Email Approval</th>
										<th scope="col" width="20%">History Note</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div>
					
					<div class="row">
						<div class="col-12">
							<input type="hidden" name="kode_target" id="kode_target" value="">
							<input type="hidden" name="no_request" id="no_request" value="">
							<div style="overflow-x:scroll">
								<table id="table_kpi" class="table table-bordered table-sm table-edit" style="margin-bottom:0">
									<thead>
										<tr>
											<th style="min-width:280px">KEY PERFORMANCE INDICATOR</th>
											<th style="min-width:200px">DESKRIPSI</th>
											<th style="min-width:180px">TARGET</th>
											<th style="min-width:80px">BOBOT (%)</th>
											
											<th style="min-width:180px" class="text-center target_1">WEEK 1</th>
											<th style="min-width:180px" class="text-center target_2">WEEK 2</th>
											<th style="min-width:180px" class="text-center target_3">WEEK 3</th>
											<th style="min-width:180px" class="text-center target_4">WEEK 4</th>
											<th style="min-width:180px" class="text-center target_5">WEEK 5</th>
											<th style="min-width:180px" class="text-center target_6">WEEK 6</th>
											
											
											<th style="min-width:180px">TOTAL</th>
											<th style="min-width:80px">% ACV</th>
											<th style="min-width:80px">BOBOT ACV</th>
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
	let table_target;
	
	let th;
	let bl;
	
	let master_kpi;
	let template_kpi;
	let idx;
	let cur_template='';
	var w;
	table_target = $('#table_target').DataTable({
		"pageLength"    : 10,
		"searching"     : true,
		"columnDefs": [
		{ targets: [ 1,2,3,4,5,6,7,8 ],className: 'hideOnMobile' },
		{ targets: [ 9 ],className: 'colMobile' },
		{ targets: 'no-sort', orderable: false },
		{ targets: 'col-hide', visible: false }
		],
		"dom": '<"top">rt<"bottom"ip><"clear">',
		"order": [[1, 'desc']],
	});
	
	$(document).ready(function(){
		$("#wilayah").change(function () {
			load_kajul();
		});
		
		load_kajul();
	});
	
	$(document).on("input", ".numeric", function() {
		this.value = format_currency(this.value);
	});
	
	$(document).on("blur", ".numeric", function() {
		var x  = parseFloat(this.value.replace(/\,/g,'')) || 0;
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
	

	$(document).on("blur", ".acv-input", function() {
		var x = this.id.split('_');
		var id = x[1];
		
		var target = $('#target_'+id).val() || '0';
		var bobot = $('#bobot_'+id).val() || '0';
		
		
		var acv1 = $('#acv1_'+id).val() || '0';
		var acv2 = $('#acv2_'+id).val() || '0';
		var acv3 = $('#acv3_'+id).val() || '0';
		var acv4 = $('#acv4_'+id).val() || '0';
		var acv5 = $('#acv5_'+id).val() || '0';
		var acv6 = $('#acv6_'+id).val() || '0';
		
		target = parseFloat(target.replace(/\,/g,'')) || 0;
		bobot = parseFloat(bobot.replace(/\,/g,'')) || 0;
		
		acv1 = parseFloat(acv1.replace(/\,/g,'')) || 0;
		acv2 = parseFloat(acv2.replace(/\,/g,'')) || 0;
		acv3 = parseFloat(acv3.replace(/\,/g,'')) || 0;
		acv4 = parseFloat(acv4.replace(/\,/g,'')) || 0;
		acv5 = parseFloat(acv5.replace(/\,/g,'')) || 0;
		acv6 = parseFloat(acv6.replace(/\,/g,'')) || 0;
		
		
		
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
	
	
	$(document).on("click", ".btn-edit" , function() {
		load_achievement($(this), 'edit');
	});
	
	$(document).on("click", ".btn-view" , function() {
		load_achievement($(this), 'view');
	});
	
	$(document).ready(function() {
		$("#form_request").submit(function() {
			if(confirm("Request Achievement KPI ini?")){
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
						// console.log(data);
						// console.log(JSON.stringify(data));
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
			event.preventDefault();
		});
		
		$("#form_save").submit(function() {
			if(validasi()!=''){
				alert(validasi());
				return false;
			}
			$('.form-input').prop("disabled", false);
			
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
					// console.log(JSON.stringify(data));
					// console.log(data);
					if(data.result=='success'){
						alert('SUCCESS. Achievement KPI berhasil disimpan!');
						load_bawahan();
						$('#modal_edit').modal('hide');
					}
					else{
						alert('FAILED. '+data.error);
					}
				}
			});
			event.preventDefault();
		});
	});
	
	function load_kajul(){
		$('.loading').show();
		var wilayah_salesman =  $('#wilayah').val();
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url('Achievementkpisalesman/GetKajul') ?>',  
			data: {
				'wilayah_salesman': wilayah_salesman
			}, 
			dataType: 'json',
			success: function (result) {
				// console.log(result);
				$('.loading').hide();
				var opt = '';
				for(i=0;i<result.data.length;i++){
					opt+='<option value="'+result.data[i].Kd_Slsman+'">'+result.data[i].Nm_Slsman+'</option>';
				}
				$('#kajul').html(opt);
				// load_bawahan();
			}
		});
	}
	
	function load_bawahan(){
		var kajul =  $('#kajul').val();
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
			url: '<?php echo site_url('Achievementkpisalesman/ListTargetkpisalesman') ?>',  
			data: {
				'kajul': kajul,
				'kategori': kategori,
				'nama_kategori': nama_kategori,
				'th': th,
				'bl': bl
			}, 
			dataType: 'json',
			success: function (result) {
				// console.log(JSON.stringify(result));
				$('.loading').hide();
				// console.log(result);
				table_target = $('#table_target').DataTable(); 
				table_target.clear().draw();
				for(i=0;i<result.bawahan.length;i++){
					var data = result.bawahan[i];
					
					var view_disabled = (data.AcvKPIStatus=='UNSAVED' || data.AcvKPIStatus=='') ? 'disabled' : '';
					var edit_disabled = (data.AcvKPIStatus=='APPROVED' || data.AcvKPIStatus=='WAITING FOR APPROVAL') ? 'disabled' : '';
					var cancel_disabled = (data.AcvKPIStatus=='APPROVED' || data.AcvKPIStatus=='WAITING FOR APPROVAL') ? '' : 'disabled';
					var check_disabled = (data.AcvKPIStatus=='SAVED' || data.AcvKPIStatus=='CANCELLED') ? '' : 'disabled';
					
					var dt = '';
					dt+='data-kodesalesman="'+data.Kd_Slsman+'"';
					dt+='data-namasalesman="'+data.Nm_Slsman+'"';
					dt+='data-wilayahsalesman="'+data.Wil_Slsman+'"';
					dt+='data-levelsalesman="'+data.Level_Slsman+'"';
					dt+='data-namalevel="'+data.Nama_Level+'"';
					dt+='data-kategori="'+kategori+'"';
					dt+='data-kodetarget="'+data.Kode_Target+'"';
					dt+='data-userid="'+data.UserID+'"';
					dt+='data-withtargetkpi="'+data.WithTargetKPI+'"';
					dt+='data-norequestkpi="'+data.NoRequestKPI+'"';
					dt+='data-norequestacv="'+data.NoRequestAcv+'"';
					dt+='data-targetkpistatus="'+data.TargetKPIStatus+'"';
					dt+='data-acvkpistatus="'+((data.AcvKPIStatus=='')?'NEW':data.AcvKPIStatus)+'"';
					dt+='data-catatan="'+data.Catatan+'"';
					dt+='data-templateid="'+data.template_id+'"';
					
					
					var col0 = '<input type="checkbox" name="kode_target[]" value="'+data.Kode_Target+'" class="pilih" '+check_disabled+'>';
					var col1 = data.Kd_Slsman;
					var col2 = data.Nm_Slsman;
					var col3 = data.Nama_Level;
					var col4 = data.UserID;
					var col5 = parseFloat(data.TotalAchievement).toFixed(0);
					var col6 = (data.AcvKPIStatus=='')?'NEW':data.AcvKPIStatus;
					var col7 = data.NoRequestAcv;
					var col8 = '<button type="button" class="btn-success btn-view" '+dt+' '+view_disabled+'>VIEW</button> '+
							'<button type="button" class="btn-primary btn-edit" '+dt+' '+edit_disabled+'>EDIT</button> '+
							'<button type="button" class="btn-danger" onclick="javascript:cancel_achievement(\''+data.Kode_Target+'\',\''+data.NoRequestAcv+'\')" '+cancel_disabled+'>CANCEL</button>';
					var col9 = ''+
							'Kode Salesman: '+col1+'<br>'+
							'Nama Salesman: '+col2+'<br>'+
							'Level Salesman: '+col3+'<br>'+
							'USERID: '+col4+'<br>'+
							'Bobot: '+col5+'<br>'+
							'Status: '+col6+'<br>'+
							'No Request KPI: '+col7+'<br>'+
							col8;
					table_target.row.add([
					col0,
					col1,
					col2,
					col3,
					col4,
					col5,
					col6,
					col7,
					col8,
					col9
					]);
					
				}
				table_target.draw();
				master_kpi = result.master_kpi;
				template_kpi = result.template;
				
				//atasan
				$('#atasan_name').val(result.atasan.ATASAN_NAMA);	
				$('#atasan_email').val(result.atasan.ATASAN_EMAIL);
				$('#atasan_name_span').text(result.atasan.ATASAN_NAMA);	
				$('#atasan_email_span').text(result.atasan.ATASAN_EMAIL);
				
			}
		});
	}
	
	function load_achievement(x, mode){
		var atasan = $('#atasan_userid').text();
		
		var kodesalesman = x.attr('data-kodesalesman');
		var namasalesman = x.attr('data-namasalesman');
		var levelsalesman = x.attr('data-levelsalesman');
		var namalevel = x.attr('data-namalevel');
		var wilayahsalesman = x.attr('data-wilayahsalesman');
		var kategori = x.attr('data-kategori');
		var kodetarget = x.attr('data-kodetarget');
		var userid = x.attr('data-userid');
		var withtargetkpi = x.attr('data-withtargetkpi');
		var norequestkpi = x.attr('data-norequestkpi');
		var catatan = x.attr('data-catatan');
		var norequestacv = x.attr('data-norequestacv');
		var acvkpistatus = x.attr('data-acvkpistatus');
		var templateid = x.attr('data-templateid');
		cur_template =templateid;
		
		var span_norequestacv = (norequestacv!='') ? ' ['+norequestacv+']' : '';
		$('#atasan').val(atasan);
		$('#kodesalesman').val(kodesalesman);
		$('#namasalesman').val(namasalesman);
		$('#levelsalesman').val(levelsalesman);
		$('#namalevel').val(namalevel);
		$('#wilayahsalesman').val(wilayahsalesman);
		$('#kategori').val(kategori);
		
		$('#th').val(th);
		$('#bl').val(bl);
		
		$('#kodetarget').val(kodetarget);
		$('#withtargetkpi').val(withtargetkpi);
		$('#norequestkpi').val(norequestkpi);
		$('#norequestacv').val(norequestacv);
		$('#acvkpistatus').val(acvkpistatus);
		$('#span_kodesalesman').html(kodesalesman);
		$('#span_namasalesman').html(namasalesman);
		$('#span_wilayahsalesman').html(wilayahsalesman);
		$('#span_namalevel').html(namalevel);
		$('#span_userid').html(userid);
		$('#template_name').html(template_name());
		$('#span_acvkpistatus').html(acvkpistatus+span_norequestacv);
		$('#catatan').html(catatan);
		
		$('.loading').show();
		idx = 0;
		$('#table_kpi tbody').html('');
		$('#table_history tbody').html('<tr><td colspan="5" align="center">Tidak ada history</td></tr>');
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url('Achievementkpisalesman/TargetSalesman_KPI_AmbilAchievementDetail') ?>',  
			data: {
				'KodeTarget': kodetarget,
				'NoRequestKPI': norequestkpi,
				'NoRequestAcv': norequestacv
			}, 
			dataType: 'json',
			success: function (result) {
				// console.log(JSON.stringify(result));
				// console.log(result);
				
				$('.loading').hide();
				for(i=0;i<result.detail.length;i++){
					idx++;
					var data = result.detail[i];
					var tbody = '';
					tbody+='<tr id="baris_'+idx+'">'+
					'<td><input type="hidden" name="kpicode[]" value="'+data.KPICode+'"><input type="hidden" name="kpiname[]" value="'+data.KPIName+'">'+data.KPIName+'</td>'+
					'<td><input type="hidden" name="kpinote[]" value="'+data.KPINote+'"><input type="hidden" name="kpiunit[]" value="'+data.KPIUnit+'">'+data.KPINote+'</td>'+
					'<td><input type="text" name="target[]" value="'+format_currency(data.KPITarget)+'" id="target_'+idx+'" class="form-input target" disabled></td>'+
					'<td><input type="text" name="bobot[]" value="'+format_currency(data.KPIBobot)+'" id="bobot_'+idx+'" class="form-input bobot" disabled></td>'+
				
					'<td class="text-center target_1"><input type="text" name="acv1[]" id="acv1_'+idx+'" value="'+format_currency(data.AcvWeek1)+'" class="acv-input numeric"></td>'+
					
					'<td class="text-center target_2"><input type="text" name="acv2[]" id="acv2_'+idx+'" value="'+format_currency(data.AcvWeek2)+'" class="acv-input numeric"></td>'+
					
					'<td class="text-center target_3"><input type="text" name="acv3[]" id="acv3_'+idx+'" value="'+format_currency(data.AcvWeek3)+'" class="acv-input numeric"></td>'+
					
					'<td class="text-center target_4"><input type="text" name="acv4[]" id="acv4_'+idx+'" value="'+format_currency(data.AcvWeek4)+'" class="acv-input numeric"></td>'+
					
					'<td class="text-center target_5"><input type="text" name="acv5[]" id="acv5_'+idx+'" value="'+format_currency(data.AcvWeek5)+'" class="acv-input numeric"></td>'+
					
					'<td class="text-center target_6"><input type="text" name="acv6[]" id="acv6_'+idx+'" value="'+format_currency(data.AcvWeek6)+'" class="acv-input numeric"></td>'+
					
					'<td><input type="text" name="acvtotal[]" value="'+format_currency(data.AcvTotal)+'" id="acvtotal_'+idx+'" class="form-input" disabled></td>'+
					'<td><input type="text" name="acvpersen[]" value="'+format_currency(data.AcvPersen)+'" id="acvpersen_'+idx+'" class="form-input" disabled></td>'+
					'<td><input type="text" name="acvbobot[]" value="'+format_currency(data.AcvBobot)+'" id="acvbobot_'+idx+'" class="form-input acvbobot" disabled></td>'+
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
				
				hitung_achievement();	
				set_kolom_minggu();
				// set_kolom_target();
				
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
	
	function cancel_achievement(KodeTarget, NoRequestAcv){
		var note = prompt("Alasan cancel request ini?", ""); 
		if (note!='' && note!=null){
			$('.loading').show();
			$.ajax({ 
				type: 'POST', 
				url: '<?php echo site_url('Achievementkpisalesman/cancel') ?>',  
				data: {
					'kode_target': KodeTarget,
					'norequestacv': NoRequestAcv,
					'note': note,
				}, 
				dataType: 'json',
				success: function (data) {
					// alert(data);
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
			bobot+= parseFloat($(this).val().replace(/\,/g,'')) || 0;
		});
		if(bobot!=100){
			msg = 'Bobot harus 100%!';
		}
		return msg;
	}
	

	function hitung_achievement(){
		var acvbobot = 0;
		$('.acvbobot').each(function(i, obj) {
			acvbobot+= parseFloat($(this).val().replace(/\,/g,'')) || 0;
		});
		
		$('#total_achievement').val(parseFloat(acvbobot).toFixed(0));
		// $('#span_total_achievement').text(parseFloat(acvbobot).toFixed(0));
	}
	
	function set_kolom_minggu(){
		if(w<5){
			$('.target_5').hide();
			$('.target_6').hide();
			}else if(w<6){
			$('.target_6').hide();
		}
		else{
			$('.target_5').show();
			$('.target_6').show();
		}	
	}
	
	function template_name(){
		for(i=0;i<template_kpi.length;i++){
			var x = template_kpi[i];
			if(x.template_id==cur_template){
				return x.template_name;
			}
		}
		return 'MANUAL';
	}
	
	// function set_kolom_target(){
		// for(i=1;i<=6;i++){
			// var jum = 0;
			// $('.target'+i).each(function(i, obj) {
				// jum+= parseFloat($(this).val().replace(/\,/g,'')) || 0;
			// });
			// if(jum==0){
				// $('.target_'+i).hide();
			// }
			// else{
				// $('.target_'+i).show();
			// }
		// }
	// }
	
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
	
	function achievement_export(){
		var wilayah =  $('#wilayah').val();
		var kajul =  $('#kajul').val();
		var kategori =  $('#kpi_kategori').val();
		var period =  $('#periode').val().split(' ');
		bl = monthNames.indexOf(period[0]);
		th = period[1];
		if(kajul!='' && kategori!='' && period!=''){
			window.open('<?php echo base_url().'Achievementkpisalesman/export?wilayah=' ?>'+wilayah.trim()+'&kajul='+kajul.trim()+'&kategori='+kategori.trim()+'&th='+th+'&bl='+bl,'_blank');
		}
	}
	
	function achievement_import(){
		var kajul = $('#kajul').val();
		if(kajul!=''){
			window.open('<?php echo base_url().'Achievementkpisalesman/import?kajul=' ?>'+kajul,'_blank');
		}
	}
	
	$(".monthpicker").datepicker( {
		format: "MM yyyy",
		viewMode: "months", 
		minViewMode: "months",
		autoclose: true,
	});
	
</script>

