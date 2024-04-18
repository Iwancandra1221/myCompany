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
		<center>
			TARGET KPI SALESMAN
		</center>
	</div>
	<br>
	<form id="form_request" class="form-horizontal" action="<?php echo site_url('Targetkpisalesman/sendrequest') ?>" target="_blank" method="POST">
		<div class="row">
			<div class="col-3">
				<label>Wilayah :</label>
				<select name="wilayah" id="wilayah" class="form-control" style="width:100%">
					<?php
						foreach($wilayah as $w) {
							echo "<option value='".$w['WILAYAH']."'>".$w['WILAYAH']."</option>";
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
				<button type="submit" id="btn_sendrequest" class="btn-primary" disabled>SEND REQUEST</button>
				<label>EMAIL ATASAN :</label>
				<input type="hidden" name="atasan_name" id="atasan_name" value="<?php echo (ISSET($atasan['ATASAN_NAMA'])) ? $atasan['ATASAN_NAMA'] : "" ?>">
				<input type="hidden" name="atasan_email" id="atasan_email" value="<?php echo (ISSET($atasan['ATASAN_EMAIL'])) ? $atasan['ATASAN_EMAIL'] : "" ?>">
				<span id="atasan_name_span"><?php echo (ISSET($atasan['ATASAN_NAMA'])) ? $atasan['ATASAN_NAMA'] : "" ?></span> [<span id="atasan_email_span"><?php echo (ISSET($atasan['ATASAN_EMAIL'])) ? $atasan['ATASAN_EMAIL'] : "" ?></span>]
				
				<table id="table_target" class="table table-bordered">
					<thead>
						<tr>
							<th scope="col" width="2%" class="no-sort"><input type="checkbox" id="pilih_semua"></th>
							<th scope="col" class="hideOnMobile">KODE SALESMAN</th>
							<th scope="col" class="hideOnMobile">NAMA SALESMAN</th>
							<th scope="col" class="hideOnMobile">POSISI</th>
							<th scope="col" class="hideOnMobile">USERID</th>
							<th scope="col" class="hideOnMobile">TOTAL BOBOT</th>
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
			<form id="form_save" class="form-horizontal" action="<?php echo site_url('Targetkpisalesman/save') ?>" method="POST" onkeydown="return event.keyCode != 13">
				<div class="modal-header form_title">
					<center>
						TARGET KPI SALESMAN
					</center>
				</div>
				<div class="modal-body p20">
					<input type="hidden" name="atasan" id="atasan">
					<input type="hidden" name="kodesalesman" id="kodesalesman">
					<input type="hidden" name="namasalesman" id="namasalesman">
					<input type="hidden" name="levelsalesman" id="levelsalesman">
					<input type="hidden" name="wilayahsalesman" id="wilayahsalesman">
					<input type="hidden" name="kategori" id="kategori">
					<input type="hidden" name="namalevel" id="namalevel">
					<input type="hidden" name="tglawal" id="tglawal">
					<input type="hidden" name="tglakhir" id="tglakhir">
					<input type="hidden" name="kodetarget" id="kodetarget">
					<input type="hidden" name="withtargetkpi" id="withtargetkpi">
					<input type="hidden" name="norequestkpi" id="norequestkpi">
					<input type="hidden" name="targetkpistatus" id="targetkpistatus">
					
					<div class="row">
						<div class="col-6">
							<table class="" style="width:100%">
								<tr><td>Kode Salesman </td>		<td><b><span id="span_kodesalesman"></span></b></td></tr>
								<tr><td>Nama </td>			<td><b><span id="span_namasalesman"></span></b></td></tr>
								<tr><td>Wilayah </td>		<td><b><span id="span_wilayahsalesman"></span></b></td></tr>
								<tr><td>Kategori </td>		<td><b><span id="span_namalevel"></span></b></td></tr>
								<tr><td>UserID </td>		<td><b><span id="span_userid"></span></b></td></tr>
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
							TEMPLATE
							<select id="template_kpi" name="template_id" class="form-input" style="min-width:300px">
								<option value="">MANUAL</option>
							</select>
						</div>
						<div class="col-1">
							CATATAN
						</div>
						<div class="col-5">
							<input type="text" id="catatan" name="catatan" class="form-input" style="width:100%">
						</div>
						<div class="col-12">
							<input type="hidden" name="kode_target" id="kode_target" value="">
							<input type="hidden" name="no_request" id="no_request" value="">
							<div style="overflow-x:scroll">
								<table id="table_kpi" class="table table-bordered table-sm table-edit">
									<thead>
										<tr>
											<th width="*">KEY PERFORMANCE INDICATOR</th>
											<th width="20%">DESKRIPSI</th>
											<th width="15%">TARGET</th>
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
	let table_target;
	
	let th;
	let bl;
	
	let master_kpi;
	let idx;
	let cur_template='';
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
		$("#template_kpi").change(function () {
			var selected_template = $(this).val();
			
			if(cur_template!=selected_template){
				if (!confirm('Ingin ganti template? Target akan direset kembali!')) {
					$(this).val(cur_template);
				} 
				else{
					if(selected_template!=''){
						master_template_kpi_detail(selected_template);
					}
					else{
						$('#table_kpi tbody').html('');
						tambah_baris();
					}
					cur_template=selected_template;
				}
			}
			$('.nontemplate').prop('disabled', (selected_template=='')?false:true);
		});
		
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
		this.value = format_currency(x.toFixed(2));
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
	
	$(document).ready(function() {
		$("#form_request").submit(function(e){
			var lanjut = true;
			$('.pilih').each(function(i, obj) {
				if($(this).is(':checked')){
					var kodesalesman = $(this).attr('data-kodesalesman');
					var userid = $(this).attr('data-userid');
					if(userid==''){
						alert('Salesman '+kodesalesman+' belum ada USERID Zen. Silahkan diisi terlebih dahulu sebelum send request!')
						lanjut = false;
					}
				}
			});
			
			if(lanjut==true){
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
							// console.log(data);
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
			}
		
			e.preventDefault();
		});
		
		$("#form_save").submit(function(e) {
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
					// console.log(data);
					// console.log(JSON.stringify(data));
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
			e.preventDefault();
		});
	});
	
	function load_kajul(){
		$('.loading').show();
		var wilayah_salesman =  $('#wilayah').val();
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url('Targetkpisalesman/GetKajul') ?>',  
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
		
		// w = weekCount(th,bl);
		// $('#week').val(w);
		
		$('#btn_sendrequest').prop('disabled',true);
		$('#pilih_semua').prop('checked',false);
		$('.loading').show();
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url('Targetkpisalesman/ListTargetKPISalesman') ?>',  
			data: {
				'kajul': kajul,
				'kategori': kategori,
				'nama_kategori': nama_kategori,
				'th': th,
				'bl': bl
			}, 
			dataType: 'json',
			success: function (result) {
				console.log(JSON.stringify(result));
				$('.loading').hide();
				table_target = $('#table_target').DataTable(); 
				table_target.clear().draw();
				for(i=0;i<result.bawahan.length;i++){
					var data = result.bawahan[i];
					
					var view_disabled = (data.TargetKPIStatus=='UNSAVED') ? 'disabled' : '';
					var edit_disabled = (data.TargetKPIStatus=='APPROVED' || data.TargetKPIStatus=='WAITING FOR APPROVAL') ? 'disabled' : '';
					var cancel_disabled = (data.TargetKPIStatus=='APPROVED' || data.TargetKPIStatus=='WAITING FOR APPROVAL') ? '' : 'disabled';
					
					var dt = '';
					dt+='data-kodesalesman="'+data.Kd_Slsman+'"';
					dt+='data-namasalesman="'+data.Nm_Slsman+'"';
					dt+='data-wilayahsalesman="'+data.Wil_Slsman+'"';
					dt+='data-levelsalesman="'+data.Level_Slsman+'"';
					dt+='data-namalevel="'+data.Nama_Level+'"';
					dt+='data-kategori="'+kategori+'"';
					dt+='data-tglawal="'+data.Tgl_Awal+'"';
					dt+='data-tglakhir="'+data.Tgl_Akhir+'"';
					dt+='data-kodetarget="'+data.Kode_Target+'"';
					dt+='data-userid="'+data.UserID+'"';
					dt+='data-withtargetkpi="'+data.WithTargetKPI+'"';
					dt+='data-norequestkpi="'+data.NoRequestKPI+'"';
					dt+='data-targetkpistatus="'+data.TargetKPIStatus+'"';
					dt+='data-catatan="'+data.Catatan+'"';
					dt+='data-templateid="'+data.template_id+'"';
					
					var check_disabled = (data.TargetKPIStatus=='SAVED' || data.TargetKPIStatus=='SAVED(MODIFIED)' || data.TargetKPIStatus=='CANCELLED' || data.TargetKPIStatus=='REJECTED') ? '' : 'disabled';
					
					var col0 = '<input type="checkbox" name="kode_target[]" value="'+data.Kode_Target+'" data-kodesalesman="'+data.Kd_Slsman+'" data-userid="'+data.UserID+'" class="pilih" '+check_disabled+'>';
					var col1 = data.Kd_Slsman;
					var col2 = data.Nm_Slsman;
					var col3 = data.Nama_Level;
					var col4 = data.UserID;
					var col5 = data.TotalBobot;
					var col6 = data.TargetKPIStatus;
					var col7 = data.NoRequestKPI;
					var col8 = '<button type="button" class="btn-success btn-view" '+dt+' '+view_disabled+'>VIEW</button> '+
							'<button type="button" class="btn-primary btn-edit" '+dt+' '+edit_disabled+'>EDIT</button> '+
							'<button type="button" class="btn-danger" onclick="javascript:cancel_target(\''+data.Kode_Target+'\',\''+data.NoRequestKPI+'\')" '+cancel_disabled+'>CANCEL</button>';
					var col9 = ''+
							'Kode Salesman: '+col1+'<br>'+
							'Nama Salesman: '+col2+'<br>'+
							'Level Salesman: '+col3+'<br>'+
							'UserID: '+col4+'<br>'+
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
				create_select_template_kpi(result.template);
				
				//atasan
				$('#atasan_name').val(result.atasan.ATASAN_NAMA);	
				$('#atasan_email').val(result.atasan.ATASAN_EMAIL);
				$('#atasan_name_span').text(result.atasan.ATASAN_NAMA);	
				$('#atasan_email_span').text(result.atasan.ATASAN_EMAIL);	
				// console.log(result.atasan.UserEmail);
			}
		});
	}
		
	function load_target(x, mode){
		var atasan = $('#kajul').val();
		
		var kodesalesman = x.attr('data-kodesalesman');
		var namasalesman = x.attr('data-namasalesman');
		var levelsalesman = x.attr('data-levelsalesman');
		var namalevel = x.attr('data-namalevel');
		var wilayahsalesman = x.attr('data-wilayahsalesman');
		var kategori = x.attr('data-kategori');
		
		// var positionid = x.attr('data-positionid');
		// var positionname = x.attr('data-positionname');
		var tglawal = x.attr('data-tglawal');
		var tglakhir = x.attr('data-tglakhir');
		var kodetarget = x.attr('data-kodetarget');
		var userid = x.attr('data-userid');
		var withtargetkpi = x.attr('data-withtargetkpi');
		var norequestkpi = x.attr('data-norequestkpi');
		var targetkpistatus = x.attr('data-targetkpistatus');
		var catatan = x.attr('data-catatan');
		var templateid = x.attr('data-templateid');
		
		var span_norequestkpi = (norequestkpi!='') ? ' ['+norequestkpi+']' : '';
		
		$('#atasan').val(atasan);
		$('#kodesalesman').val(kodesalesman);
		$('#namasalesman').val(namasalesman);
		$('#levelsalesman').val(levelsalesman);
		$('#namalevel').val(namalevel);
		$('#wilayahsalesman').val(wilayahsalesman);
		$('#kategori').val(kategori);
		$('#tglawal').val(tglawal);
		$('#tglakhir').val(tglakhir);
		$('#kodetarget').val(kodetarget);
		$('#withtargetkpi').val(withtargetkpi);
		$('#norequestkpi').val(norequestkpi);
		$('#targetkpistatus').val(targetkpistatus);
		$('#catatan').val(catatan);
		$('#template_kpi').val(templateid);
		$('#span_kodesalesman').html(kodesalesman);
		$('#span_namasalesman').html(namasalesman);
		$('#span_wilayahsalesman').html(wilayahsalesman);
		$('#span_namalevel').html(namalevel);
		$('#span_userid').html(userid);
		$('#span_targetkpistatus').html(targetkpistatus+span_norequestkpi);
		cur_template =templateid;
		
		$('.loading').show();
		idx = 0;
		$('#table_kpi tbody').html('');
		$('#table_history tbody').html('<tr><td colspan="5" align="center">Tidak ada history</td></tr>');
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url('Targetkpisalesman/TargetSalesman_KPI_AmbilTargetDetail') ?>',  
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
					'<td><input type="text" name="target[]" value="'+format_currency(data.KPITarget)+'" id="target_'+idx+'" class="form-input numeric target"></td>'+
					'<td><input type="text" name="bobot[]" value="'+format_currency(data.KPIBobot)+'" class="form-input nontemplate numeric bobot" required></td>'+
					'<td class="form-edit"><button type="button" class="btn-danger nontemplate" onclick="hapus_baris('+idx+')">X</button></td>'+
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
				url: '<?php echo site_url('Targetkpisalesman/cancel') ?>',  
				data: {
					'kode_target': KodeTarget,
					'norequestkpi': NoRequestKPI,
					'note': note,
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
	
	function master_template_kpi_detail(template_id){
		$('.loading').show();
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url('Targetkpisalesman/Master_Template_Target_KPI_AmbilList_Detail') ?>',  
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
			}
		});
	}
	
	// // function weekCount(year, month_number) {
		// // // month_number is in the range 1..12
		// // var firstOfMonth = new Date(year, month_number-1, 1);
		// // var lastOfMonth = new Date(year, month_number, 0);
		// // var used = firstOfMonth.getDay() + lastOfMonth.getDate();
		// // return Math.ceil( used / 7);
	// // }
	
	function tambah_baris(kpi_code='',bobot=0) {
		var template_id = $('#template_kpi').val();
		var disabled = (template_id!='') ? 'disabled':'';
		var tbody = '';
		idx++;
		tbody+='<tr id="baris_'+idx+'">'+
		'<td>'+create_select_master_kpi(master_kpi, kpi_code)+'</td>'+
		'<td><input type="text" name="deskripsi[]" class="justify"></td>'+
		'<td><input type="text" name="target[]" id="target_'+idx+'" class="target numeric"></td>'+
		'<td><input type="text" name="bobot[]" class="form-input numeric bobot" value="'+bobot+'" required '+disabled+'></td>'+
		'<td><button type="button" class="btn-danger" onclick="hapus_baris('+idx+')" '+disabled+'>X</button></td>'+
		'</tr>';
		$('#table_kpi tbody').append(tbody);
	}
	
	function create_select_master_kpi(data, selected=''){
		var template_id = $('#template_kpi').val();
		var disabled = (template_id!='') ? 'disabled':'';
		var z = '<select name="KPICode[]" class="form-input nontemplate w100" required '+disabled+'>';
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
			bobot+= parseFloat($(this).val().replace(/\,/g,'')) || 0;
		});
		if(bobot!=100){
			msg = 'Bobot harus 100%!';
		}
		return msg;
	}
	
	function hitung_bobot(){
		var bobot = 0;
		$('.bobot').each(function(i, obj) {
			bobot+= parseFloat($(this).val().replace(/\,/g,'')) || 0;
		});
		
		$('#total_bobot').val(parseFloat(bobot).toFixed(0));
		$('#span_total_bobot').text(parseFloat(bobot).toFixed(0));
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
	
	$(".monthpicker").datepicker( {
		format: "MM yyyy",
		viewMode: "months", 
		minViewMode: "months",
		autoclose: true,
	});
	
</script>

