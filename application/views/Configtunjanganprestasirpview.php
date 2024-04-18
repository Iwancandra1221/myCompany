<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<style>
	.height-28px{
	padding-top:3px;
	height: 28px;
	line-height: 28px;
	}
	.numeric{
	text-align:right;
	}
</style>

<div class="container pb0">
	<div class="form_title">
		<center>
			KONFIGURASI TUNJANGAN PRESTASI
		<button type="button" class="btn btn-dark height-28px float-right" onclick="javascript:add_new()">Add New</button>
		</center>
	</div>
	<div style="clear:both">
	</div>
	<div class="row">
		<div class="col-md-6">
			<label>Position :</label>
			<select name="optEmpPosition" id="optEmpPosition" class="form-control select2" style="width:100%">
				<option value="">ALL</option>
				<?php
					foreach($optEmpPosition as $r) {
						echo "<option value='".$r['EmpPositionID']."'>".$r['EmpPositionID'].' - '.$r['EmpPositionName']."</option>";
					}
				?>
			</select>
		</div>
		<div class="col-md-3">
			<label>Level :</label>
			<select name="optEmpLevel" id="optEmpLevel" class="form-control select2" style="width:100%">
				<option value="">ALL</option>
				<?php
					foreach($optEmpLevel as $r) {
						echo "<option value='".$r['EmpLevelID']."'>".$r['EmpLevelID'].' - '.$r['EmpLevelName']."</option>";
					}
				?>
			</select>
		</div>
		<div class="col-md-2">
			<label>Start Date :</label>
			<select name="optStartDate" id="optStartDate" class="form-control select2" style="width:100%">
				<option value="">ALL</option>
				<?php
					foreach($optStartDate as $r) {
						echo "<option value='".$r['Start_Date']."'>".date('d-M-Y',strtotime($r['Start_Date']))."</option>";
					}
				?>
			</select>
		</div>
		
		<div class="col-md-1">
			<label>&nbsp;</label>
			<br>
			<button type="button" class="btn btn-dark height-28px w100" onclick="javascript:table_config.ajax.reload()">View</button>
		</div>
	</div>
	
	<div class="row">
		<div class="col-12">
			<table id="table_config" class="table table-bordered">
				<thead>
					<tr>
						<th width="*">POSITION ID</th>
						<th width="*">POSITION NAME</th>
						<th width="*">LEVEL ID</th>
						<th width="*">LEVEL NAME</th>
						<th width="*">START DATE</th>
						<th width="*">PERSENTASE MIN</th>
						<th width="*">SUBSIDI</th>
						<th width="20px" class="no-sort">EDIT</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="7">Loading ...</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="row">
		<div class="col-12">
		</div>
	</div>
	<hr>
</div>


<div class="container pt0" id="div_edit">
	<form id="form_save" class="form-horizontal" action="<?php echo site_url('Configtunjanganprestasirp/save') ?>" target="_blank" method="POST">
		<input type="hidden" name="act" id="act">
		<input type="hidden" name="OldEmpPositionID" id="OldEmpPositionID">
		<input type="hidden" name="OldEmpLevelID" id="OldEmpLevelID">
		<input type="hidden" name="OldStart_Date" id="OldStart_Date">
		<div class="row">
			<div class="col-md-4">
				<label>Position :</label>
				<select name="EmpPositionID" id="EmpPositionID" class="form-control select2" style="width:100%" required>
					<option value="">ALL</option>
					<?php
						foreach($emp_position as $r) {
							echo "<option value='".$r['user_position_code']."'>".$r['user_position_code'].' - '.$r['user_position_name']."</option>";
						}
					?>
				</select>
			</div>
			<div class="col-md-2">
				<label>Level :</label>
				<select name="EmpLevelID" id="EmpLevelID" class="form-control select2" style="width:100%" required>
					<option value="">ALL</option>
					<?php
						foreach($emp_level as $r) {
							echo "<option value='".$r['EmpLevelID']."'>".$r['EmpLevelID'].' - '.$r['EmpLevel']."</option>";
						}
					?>
				</select>
			</div>
			<div class="col-md-2">
				<label>Start Date :</label>
				<input type="text" name="Start_Date" id="Start_Date" class="form-control height-28px datepicker" style="width:100%" value="<?php echo date('d-M-Y') ?>" required>
			</div>
			<div class="col-md-2">
				<label>Persentase Min :</label>
				<input type="text" name="Persentase_Min" id="Persentase_Min" class="form-control numeric height-28px" required>
			</div>
			<div class="col-md-2">
				<label>Subsidi Tunj Prestasi :</label>
				<input type="text" name="Subsidi_TunjanganPrestasi" id="Subsidi_TunjanganPrestasi" class="form-control numeric height-28px" required>
			</div>
		</div>
		
		<div class="row">
			<div class="col-12">
				<table id="table_detail" class="table table-bordered">
					<thead>
						<tr>
							<th width="*">KATEGORI</th>
							<th width="*">RANGE TARGET DARI</th>
							<th width="*">RANGE TARGET SP</th>
							<th width="*">PERSENTASE MAKS</th>
							<th width="*">PEMBAGI X</th>
							<th width="*">PATOKAN RUPIAH</th>
							<th width="20px" class="no-sort"><button type="button" class="btn-primary" onclick="javascript:add_row()">+</button></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
				<div class="text-right">
					<input type="button" name="delete" id="btn-delete" class="btn btn-danger-dark height-28px" onclick="javascript:delete_config()" value="Delete">
					<input type="submit" name="submit" class="btn btn-dark height-28px" value="Save Config">
					<button type="button" class="btn btn-dark height-28px" onclick="javascript:cancel()">Cancel</button>
				</div>
			</div>
		</div>
	</form>
</div>

<script>
	let table_config;
	
	let new_row = '<tr>'+
	'<td><select name="Kategori[]" class="form-control height-28px" required><option value="TIER">TIER</option><option value="NORMAL">NORMAL</option></select></td>'+
	'<td><input type="text" name="Patokan_ST_Awal[]" class="form-control height-28px numeric st_awal" required></td>'+
	'<td><input type="text" name="Patokan_ST_Akhir[]" class="form-control height-28px numeric st_akhir" required></td>'+
	'<td><input type="text" name="Persentase_Maks[]" class="form-control height-28px numeric persentase_maks" required></td>'+
	'<td><input type="text" name="Pembagi_X[]" class="form-control height-28px numeric" required></td>'+
	'<td><input type="text" name="Patokan_Rupiah[]" class="form-control height-28px numeric" required></td>'+
	'<td><button type="button" class="btn-danger del-row">x</button></td>'+
	'</tr>';
	let blank = '<tr><td colspan="6"></td></tr>';
	
	$(document).ready(function() {
		table_config = $('#table_config').DataTable({
			"pageLength"    : 10,
			"searching"     : true,
			"lengthChange"  : false,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false }
			],
			"order": [3, 'asc'],
			"autoWidth": false,
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": '<?php echo base_url('Configtunjanganprestasirp/datatable_config') ?>',
				"type": "GET",
				"datatype": "json",
				"data": function (data) {
					data.EmpPositionID = $('#optEmpPosition').val();
					data.EmpLevelID = $('#optEmpLevel').val();
					data.StartDate = $('#optStartDate').val();
				}
			},
			"initComplete": function() {
				$('#table_config_filter input').unbind();
				$('#table_config_filter input').bind('keyup', function(e) {
					if(e.keyCode == 13) {
						table_config.search(this.value).draw();   
					}
				}); 
			},
			"dom": '<"top">rtip<"clear">',
		});
		
		$("#form_save").submit(function() {
			if(validasi_target()){
				if(confirm("Apakah data sudah benar?")){
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
								alert('SUCCESS. Data berhasil disimpan!');
								cancel();
								table_config.ajax.reload();
							}
							else{
								alert('FAILED. '+data.error);
							}
						}
					});
				}
			}
			event.preventDefault();
		});
		
		$('.select2').select2();
		add_row();
		$('#div_edit').hide();
	});
	
	function validasi_target(){
		var st_awal1 = 0;
		var st_akhir1 = 0;
		var persentase_maks1 = 0;
		var st_awal2 = 0;
		
		var next_i = 0;
		for(i=0;i<$('.st_awal').length;i++){
			next_i = i+1;
			st_awal1 = parseInt($('.st_awal').eq(i).val());
			st_akhir1 = parseInt($('.st_akhir').eq(i).val())+1;
			persentase_maks1 = parseInt($('.persentase_maks').eq(i).val());
			
			if(st_akhir1<=st_awal1){
				alert('Patokan ST Akhir tidak boleh lebih kecil dari Patokan ST Awal');
				$('.st_awal').eq(i).focus();
				return false;
			}
			if(persentase_maks1<st_awal1){
				alert(persentase_maks1);
				alert(st_awal1);
				alert('PERCENTASE MAKS tidak boleh lebih kecil dari RANGE TARGET DARI');
				$('.persentase_maks').eq(i).focus();
				return false;
			}
			if(persentase_maks1>st_akhir1){
				alert('PERCENTASE MAKS tidak boleh lebih besar dari RANGE TARGET SP');
				$('.persentase_maks').eq(i).focus();
				return false;
			}
			
			if($('.st_awal').eq(next_i).length>0){
				st_awal2 = $('.st_awal').eq(next_i).val();
				if(st_awal2!=st_akhir1){
					alert('Patokan ST Awal ini harus sama dengan '+st_akhir1.toString());
					$('.st_awal').eq(next_i).val(st_akhir1);
					$('.st_awal').eq(next_i).focus();
					return false;
				}
			}
		}
		return true;
	}
	
	function add_new(){
		$('#act').val('new');
		//param jika update, berfungsi sebagai data lama sebelum diedit, jika ada perubahan field EmpPositionID, EmpLevelID, Start_Date , maka bisa dihapus dan insert baru
		$('#OldEmpPositionID').val('');
		$('#OldEmpLevelID').val('');
		$('#OldStart_Date').val('');
		
		$('#EmpPositionID').val('').trigger('change');
		$('#EmpLevelID').val('').trigger('change');
		$('#Start_Date').val(date("d-M-Y")); 
		$('#Persentase_Min').val('');
		$('#Subsidi_TunjanganPrestasi').val('');
		$('#btn-delete').hide();
		$('#table_detail tbody').html('');
		$('#table_detail tbody').append(new_row);
		$('#div_edit').show();
	}
	
	function cancel(){
		$('#table_detail tbody').html('');
		$('#div_edit').hide();
	}
	
	function add_row(){
		$('#table_detail tbody').append(new_row);
	}
	
	$(document).on("click", ".del-row" , function() {		
		if (confirm("Ingin Hapus Baris Ini?")) {
			$(this).closest("tr").remove();
		}
	});
	
	$(document).on("input", ".numeric", function() {
		this.value = format_currency(this.value);
	});
	
	$(document).on("blur", ".numeric", function() {
		var x  = parseFloat(this.value.replace(/\,/g, '')) || 0;
		this.value = format_currency(parseFloat(x).toFixed(0));
	});
	
	function format_currency(x){
		x = x.toString();
		var minus = '';
		if (x.indexOf("-") >= 0){
			minus = '-';
		}
		var str = x.replace(/[^0-9.]/g,'');
		x = str.split('.'); 
		x1 = x[0].length > 0 ? x[0] : '0';
		// x2 = x.length > 1 ? '.' + x[1] : '';
		var rgx = /(\d+)(\d{3})/; 
		while (rgx.test(x1)) { 
			x1 = x1.replace(rgx, '$1' + ',' + '$2'); 
		} 
		return minus + x1; // + x2;
	}
	
	function edit(xEmpPositionID, xEmpLevelID, xStart_Date){
		$('.loading').show();
		$.ajax({
			data      	: {EmpPositionID:xEmpPositionID, EmpLevelID:xEmpLevelID, Start_Date:xStart_Date},
			url			: '<?php echo base_url('Configtunjanganprestasirp/get_detail') ?>',
			cache		: true,
			type		: 'POST',
			dataType  : 'json',
			success   : function(res){
				console.log(JSON.stringify(res));
				$('.loading').hide();
				if(res.result=='success'){
					var html = '';
					var list = '';
					for(i=0;i<res.data.length;i++){
						list = res.data[i];
						if(i==0){
							$('#act').val('update');
							$('#OldEmpPositionID').val(list.EmpPositionID);
							$('#OldEmpLevelID').val(list.EmpLevelID);
							$('#OldStart_Date').val(list.Start_Date);
							$('#EmpPositionID').val(list.EmpPositionID).trigger('change');
							$('#EmpLevelID').val(list.EmpLevelID).trigger('change');
							$('#Start_Date').val(date("d-M-Y",strtotime(list.Start_Date)));
							
							$('#Persentase_Min').val(format_currency(list.Persentase_Min));
							$('#Subsidi_TunjanganPrestasi').val(format_currency(list.Subsidi_TunjanganPrestasi));
							$('#btn-delete').show();
						}
						
						html +='<tr>'+
						'<td><select name="Kategori[]" class="form-control height-28px" required><option value="TIER" '+((list.Kategori=='TIER')?'selected':'')+'>TIER</option><option value="NORMAL" '+((list.Kategori=='NORMAL')?'selected':'')+'>NORMAL</option></select></td>'+
						'<td><input type="text" name="Patokan_ST_Awal[]" class="form-control height-28px numeric st_awal" value="'+format_currency(list.Patokan_ST_Awal)+'" required></td>'+
						'<td><input type="text" name="Patokan_ST_Akhir[]" class="form-control height-28px numeric st_akhir" value="'+format_currency(list.Patokan_ST_Akhir)+'" required></td>'+
						'<td><input type="text" name="Persentase_Maks[]" class="form-control height-28px numeric" value="'+format_currency(list.Persentase_Maks)+'" required></td>'+
						'<td><input type="text" name="Pembagi_X[]" class="form-control height-28px numeric" value="'+format_currency(list.Pembagi_X)+'" required></td>'+
						'<td><input type="text" name="Patokan_Rupiah[]" class="form-control height-28px numeric" value="'+format_currency(list.Patokan_Rupiah)+'" required></td>'+
						'<td><button type="button" class="btn-danger del-row">x</button></td>'+
						'</tr>';
					}
					$('#table_detail tbody').html(html);
					$('#div_edit').show();
				}
				else{
					alert('FAILED. '+data.error);
				}
			}
		});
	}
	
	function delete_config(){
		if(confirm("Ingin hapus data ini?")){
			var xEmpPositionID = $('#OldEmpPositionID').val();
			var xEmpLevelID = $('#OldEmpLevelID').val();
			var xStart_Date = $('#OldStart_Date').val();
			// alert(xEmpPositionID);
			$('.loading').show();
			$.ajax({
				data      	: {EmpPositionID:xEmpPositionID, EmpLevelID:xEmpLevelID, Start_Date:xStart_Date},
				url			: '<?php echo base_url('Configtunjanganprestasirp/delete') ?>',
				cache		: true,
				type		: 'POST',
				dataType  : 'json',
				success   : function(res){
					console.log(JSON.stringify(res));
					$('.loading').hide();
					if(res.result=='success'){
						alert('SUCCESS. Data berhasil diupdate!');
						cancel();
						table_config.ajax.reload();
					}
					else{
						alert('FAILED. '+res.error);
					}
				}
			});
		}
	}
	
	
</script>

