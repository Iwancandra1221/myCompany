<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>
</style>
<script>
	var tipe = <?php echo(json_encode($tipe));?>;
	var tipe_merk = <?php echo(json_encode($tipe_merk));?>;
	var kd_brg = <?php echo(json_encode($kd_brg));?>;
	var kd_brg_merk = <?php echo(json_encode($kd_brg_merk));?>;
	var group_param = <?php echo(json_encode($group_param));?>;
	var LokasiQRCode = <?php echo(json_encode($LokasiQRCode));?>;
    $(document).ready(function() {
		$("#tipe").autocomplete({
			source: kd_brg
		});			
	});
</script>
<div class="container">
	<div class="form_title"  style="text-align: center;"> 
			<a href="<?php echo base_url('MsSmartQR') ?>" class="float-left">
				<i class="glyphicon glyphicon-dark glyphicon-circle-arrow-left" style="font-size:200%"></i>
			</a>
			CREATE MASTER QR CODE 
	</div>
	<br>
	<?php echo form_open('MsSmartQR/SmartQRInsert',array('id' => 'myform')); ?>
	<div class="border20 p20">
		<div class="row">
			<div class="col-3 col-m-4"><big id="IsGroupCaption">Tipe Barang</big><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<div class="border10 p10">
					
					<input type="radio" name="isgroup" value="0" class="chkIsGroup" id="is_group_0" checked>
					<label for="is_group_0" class="pointer">Tipe Barang</label>
					<input type="radio" name="isgroup" value="1" class="chkIsGroup" id="is_group_1" style="margin-left:20px">
					<label for="is_group_1" class="pointer">Group Tipe Barang</label>
					
					<div class="input-group input-group-dark">
						<span class="input-group-addon">
							<i class="glyphicon glyphicon-search"></i>
						</span>
						<input type="text" class="form-control form-control-dark" name="tipe" id="tipe" placeholder="Ketikkan Tipe Barang" required>
					</div>
					<span class="form-help" id="IsGroupKeterangan">Tipe Barang search dari master kode barang Bhakti</span>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-3 col-m-4"><big>Merk</big><br><small><em>Auto Fill</em></small></div>
			<div class="col-4 col-m-4">
				<input type="text" class="form-control form-control-dark" name="merk" id="merk" placeholder="Merk (readonly)" readonly required>
			</div>
		</div>
		
		<div class="row">
			<div class="col-3 col-m-4"><big>Lokasi QR Code</big><br><small><em>Required</em></small></div>
			<div class="col-4 col-m-4">
				<select class="form-control form-control-dark" name="lokasi_qr_code" id="lokasi_qr_code" onchange="javascript:lokasiQRCode()" required>
					<option value="">Pilih Lokasi QR Code</option>
					<?php
						foreach($LokasiQRCode as $d){
							echo "<option value='".$d['LokasiQRCode']."'>".$d['LokasiQRCode']."</option>";
						}
					?>
				</select>
			</div>
			<div class="col-3 col-md-1 additional">
				<input type="hidden" name="AddInfoParamName" id="AddInfoParamName">
				<input type="text" class="form-control form-control-dark" name="AddInfoParam" id="AddInfoParam" placeholder="Qty">
			</div>
			<div class="col-3 col-m-3 additional">
				<big><span id="AddInfo" style="line-height: 34px;"></span></big>
			</div>
		</div>
		
		<!--div class="row">
			<div class="col-3 col-m-4"><big>URL Landing Page</big><br><small><em>Optional</em></small></div>
			<div class="col-9 col-m-8">
				<input type="url" class="form-control form-control-dark" name="url_redirect" placeholder="URL Redirect">
			</div>
		</div-->
		
		<div id="param">
		</div>
		
		<div id="add_more" class="row" style="margin:0">
		</div>
		
		<div class="row">
			<div class="col-3 col-m-4"><button type="button" class="btn btn-dark" onclick="javascript:add_more()">Add Custom Field</button></div>
			<div class="col-9 col-m-8"></div>
		</div>
		
		<div class="row">
			<div class="col-3 col-m-4"></div>
			<div class="col-9 col-m-8">
				<input type="submit" name="generate" class="btn btn-dark" value="SAVE & GENERATE QR CODE">
				<!--input type="reset" name="create_new" class="btn btn-dark" value="GENERATE & ADD NEW" onclick="javascript:CreateNew()"-->
			</div>
		</div>
	</div>
	<?php echo form_close(); ?>
	
	<div id="result">
	</div>
</div>



<div id="modal_view" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true" style="background:red; padding:0px 10px">&times;</span>
				</button>
				<!--h4 class="modal-title">Information</h4-->
				<div class="msg msg-success mt30">
					<i class="glyphicon glyphicon-ok-sign"></i>
					<span id="view_result"></span>
				</div>
			</div>
			<div class="modal-body p30">
				<form class="form-horizontal">

					<div class="form-group">
						<label class="col-xs-3"></label>
						<div class="col-xs-3" id="div_qrcode">
							<img height="180px" id="view_qrcode">
						</div>
						<div class="col-xs-4">
							<button type="button" class="btn btn-dark w200px mb10 btnQRCode" onclick="javascript:copyURL()"  disabled><i class="glyphicon glyphicon-copy"></i> COPY URL</button>
							<br>
							<a href="#" id="btnDownload"><button type="button" class="btn btn-dark w200px mb10 btnQRCode" disabled><i class="glyphicon glyphicon-download"></i> DOWNLOAD</button></a>
							<br>
							<button type="button" class="btn btn-dark w200px mb10 btnQRCode" onclick="javascript:printQRCode()"  disabled><i class="glyphicon glyphicon-print"></i> PRINT</button>
							<br>
							<button type="button" class="btn btn-dark w200px btnQRCode" onclick="javascript:openURL()"  disabled><i class="glyphicon glyphicon-globe"></i> TEST URL</button>
						</div>
					</div>
					<div class="form-group">
						<label class="col-xs-3">Merk</label>
						<div class="col-xs-4">
							<input type="hidden" id="view_id">
							<input type="hidden" id="view_url">
							<input type="text" id="view_merk" class="form-control form-control-dark" style="background:#d3d3d3" disabled>
						</div>
					</div>
					<div class="form-group">
						<label class="col-xs-3">Type Barang</label>
						<div class="col-xs-4">
							<input type="text" id="view_tipe" class="form-control form-control-dark" style="background:#d3d3d3" disabled>
						</div>
					</div>
					<div class="form-group">
						<label class="col-xs-3">Lokasi QR Code</label>
						<div class="col-xs-4">
							<input type="text" id="view_lokasi_qr_code" class="form-control form-control-dark" style="background:#d3d3d3" disabled>
						</div>
						<div class="col-xs-1 view_qty">
							<input type="text" id="view_qty" class="form-control form-control-dark" style="background:#d3d3d3" disabled>
						</div>
						<label class="col-xs-3 view_qty" id="view_qty_lokasi">QTY PER </label>
					</div>
					<div class="form-group">
						<label class="col-xs-3">URL Landing Page</label>
						<div class="col-xs-9">
							
							<div class="input-group">
							<input type="text" id="view_url_redirect" class="form-control" disabled>
								<span class="input-group-addon" onclick="javascript:editViewURLLandingPage()" style="cursor:pointer">
									<i id="btn_edit_url" class="glyphicon glyphicon-edit" style="color:blue"></i>
								</span>
							</div>
						</div>
					</div>
					<div id="view_param">
					</div>
					<div class="form-group">
						<label class="col-xs-3"></label>
						<div class="col-xs-7">
							<small>
							<span id="view_created"></span><br>
							<span id="view_modified"></span>
							</small>
						</div>
						<div class="col-xs-2">
							<button type="button" class="btn btn-danger-dark" onclick="javascript:willDeleteSmartQR()"><i class="glyphicon glyphicon-trash"></i> HAPUS</button>
						</div>
					</div>
				</form>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="modal_delete" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
				<?php echo form_open('MsSmartQR/SmartQRDelete',array('id' => 'form_delete')); ?>
				<div class="modal-body"  style="text-align: center;"> 
					<i class="glyphicon glyphicon-exclamation-sign" style="font-size:400%;color:red"></i>
					<br>
					<p style="font-size:120%;color:red;font-weight:bold">APAKAH ANDA YAKIN INGIN MENGHAPUS MASTER QR CODE INI ?</p>
					<p style="font-size:120%;color:red;font-weight:bold">ALASAN MENGHAPUS ?</p>
					<input type="hidden" name="id" id="delete_id">
					<input type="hidden" name="tipe" id="delete_tipe">
					<input type="hidden" name="merk" id="delete_merk">
					<input type="hidden" name="lokasi_qr_code" id="delete_lokasi_qr_code">
					<input type="text" name="reason_deleted" id="reason_deleted" class="form-control form-control-dark" onkeypress="return event.keyCode!=13" required>
					<br>
					<button type="submit" id="btn_delete" class="btn btn-danger-dark" onclick="javascript:deleteSmartQRXXX()" >YA, HAPUS</button>
					<button type="button" class="btn btn-dark" data-dismiss="modal">CANCEL</button>
				 
				</div>
				<div class="modal-footer">
				</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
	var add_new = '	<div class="add_more row border10 mb10">'+
	'<div class="col-3 col-m-4">'+
	'<input type="text" name="param_name[]" class="form-control form-control-dark" placeholder="Name (tanpa spasi)" onkeydown="return (event.which >= 48 && event.which <= 57) ||(/[a-z]/i.test(event.key)) || event.which == 8 || event.which == 46" required>'+
	'</div>'+
	'<div class="col-9 col-m-8">'+
	'<div class="input-group input-group-dark">'+
	'<input type="text" name="param_value[]" class="form-control  form-control-dark" placeholder="Value" required>'+
	'<span class="input-group-btn">'+
	'<button class="btn btn-danger delete_add_more" type="button"><i class="glyphicon glyphicon-remove"></i></button>'+
	'</span>'+
	'</div>'+
	'</div>'+
	'</div>';
	
	$(document).ready(function() {
		$('.additional').hide();
		$("#myform").submit(function() {
			CreateNew();
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
				success   : function(data) {
					$('.loading').hide();
					if(data.result=='SUKSES'){
						$('#view_result').html('QR Code has been successfully generated');						
						$('#view_id').val(data.id);
						$('#view_merk').val(data.merk);
						$('#view_tipe').val(data.tipe);
						$('#view_url').val(data.url);
						$('#view_url_redirect').val(data.url_redirect);
						$('#view_lokasi_qr_code').val(data.lokasi_qr_code);
						
						let qty = parseInt(data.qty) || 0;
						if(qty>0){
							$('#view_qty').val(qty);
							$('#view_qty_lokasi').text('QTY PER '+data.lokasi_qr_code);
							$('.view_qty').show();
						}
						else{
							$('.view_qty').hide();
						}
					
					
						$('#view_created').text('Created on '+data.created_tgl+' | '+data.created_jam+' | '+data.created_by);
						$('#view_modified').text('Last Modified on '+data.modified_tgl+' | '+data.modified_jam+' | '+data.modified_by);
						$('#view_qrcode').attr("src", 'data:image/png;base64,' +data.qrcode);
						$('#btnDownload').attr("href", 'data:image/png;base64,' +data.qrcode);
						$('#btnDownload').attr("download", data.filename);
						
						var paramHtml = '';
						for(i=0;i<data.param.length;i++){
							paramHtml += '<div class="form-group">'+
								'<label class="col-3">'+data.param[i]['ParamName']+'</label>'+
								'<div class="col-9">'+
									'<input type="text" class="form-control form-control-dark" value="'+data.param[i]['ParamValue']+'" style="background:#d3d3d3" disabled>'+
								'</div>'+
							'</div>';
						}
						
						$('#view_param').html(paramHtml);
						$('#modal_view').modal('show');
						
						$('#myform').trigger("reset");
						$('#param').html('');
						$('#add_more').html('');
					}
					else{
						alert(data.result+'\n'+data.message.replace(/\\n/g,"\n"));
					}
					
				}
			});
			event.preventDefault(); //Prevent the default submit
		});
		
		$("#lokasi_qr_code").change(function() {
			if($(this).val()=='OTHER') {
				$("#other").attr('disabled', false);
				$("#other").focus();
			}
			else{
				$("#other").val('');
				$("#other").attr('disabled', true);
			}
		});
		
		$('#tipe').keypress(function (e) {
			var key = e.which;
			if(key == 13){
				var tipe = $('#tipe').val();
				cari_merk(tipe);
			}
		});
		
		$('#tipe').blur(function() {
			var tipe = $('#tipe').val();
			cari_merk(tipe);
		});
		
		$('.chkIsGroup').change(function(){
			let val = $(this).val();
			if(val=='0'){
				$('#IsGroupCaption').text('Tipe Barang');
				$('#IsGroupKeterangan').text('Tipe Barang search dari master kode barang Bhakti');
				$("#tipe").autocomplete({
					source: kd_brg
				});
				$('#tipe').val('');
				$('#merk').val('');
				$('#param').html('');
			}
			else{
				$('#IsGroupCaption').text('Group Tipe Barang');
				$('#IsGroupKeterangan').text('Group Tipe Barang search dari Master Group Tipe Barang');
				$("#tipe").autocomplete({
					source: tipe
				});
				$('#tipe').val('');
				$('#merk').val('');
				$('#param').html('');
			}
		});
	});
	
	$(document).on("click", ".delete_add_more" , function() {		
		$(this).closest(".add_more").remove();
	});
	
	function cari_merk(tipe){
		var checkedTipe = '';
		$('input:radio.chkIsGroup').each(function () {
			if(this.checked){
				checkedTipe = $(this).val();
			}
		});
		
		if(checkedTipe=='0'){
			var x = tipe.split(' | ');
			
			var bExist = false
			for(i=0;i<kd_brg_merk.length;i++){
				if(kd_brg_merk[i].KD_BRG==x[0]){
					bExist = true;
					$('#merk').val(kd_brg_merk[i].MERK);
					add_params(kd_brg_merk[i].group);
				}
			}
			
			if(bExist==false){
				$('#tipe').val('');
				$('#merk').val('');
			}
		}
		else{
			var bExist = false
			for(i=0;i<tipe_merk.length;i++){
				if(tipe_merk[i].tipe==tipe){
					bExist = true;
					$('#merk').val(tipe_merk[i].merk);
					// alert(tipe_merk[i].group);
					add_params(tipe_merk[i].group);
				}
			}
			
			if(bExist==false){
				$('#tipe').val('');
				$('#merk').val('');
			}
		}
	}
	
	function add_more(){
		$('#add_more').append(add_new);
	}
	
	function add_params(group){
		$('#param').html('');
		for(j=0;j<group_param.length;j++){
			if(group_param[j].group=='ALL' || group_param[j].group==group){
				add_param(group_param[j].param);
			}
		}
	}
	
	function add_param(value){
		var html='<div class="row">'+
		'<div class="col-3 col-m-4"><big>'+value+'</big><br><small><em>Optional</em></small></div>'+
		'<div class="col-9 col-m-8">'+
		'<input type="hidden" name="old_param_name[]" value="'+value+'">'+
		'<input type="text" class="form-control form-control-dark" name="old_param_value[]" placeholder="'+value+'">'+
		'</div>'+
		'</div>';
		$('#param').append(html);
	}
	
	function close_param(){
		$('#add_more').hide();
		$('#param_name').val('');
		$('#param_value').val('');
	}
	
	function lokasiQRCode(){
		$('.additional').hide();
		$('#AddInfoParamName').val("");
		$('#AddInfoParam').val("");
		$('#AddInfoParam').prop("required", false);
		var curLokasi = $('#lokasi_qr_code').val(); 
		for(i=0;i<LokasiQRCode.length;i++){
			if(curLokasi==LokasiQRCode[i].LokasiQRCode){
				if(LokasiQRCode[i].AddInfoParam!=''){
					$('.additional').show();
					$('#AddInfoParam').prop("required", true);
					$('#AddInfoParamName').val(LokasiQRCode[i].AddInfoParam);
					$('#AddInfo').text(LokasiQRCode[i].AddInfo);
				}
			}
		}
	}
	
	function CreateNew(){
		$('#result').html('');
	}
	
	function copyURL() {
		var url = document.getElementById("view_url").value;
		// if(navigator.clipboard.writeText(url)){
			// alert("URL QRCode Berhasil di-copy:\n" + url);
		// }
		// else{
			// prompt("Tekan Ctrl+C untuk copy url ", url);
		// }
		prompt("Tekan Ctrl+C untuk copy url ", url);
	}
	
	function openURL() {
		var url = document.getElementById("view_url").value;
		window.open(url, '_blank');
	}
		
	function printQRCode(){
		var divToPrint=document.getElementById('div_qrcode');
		var newWin=window.open('','Print-Window');
		newWin.document.open();
		newWin.document.write('<html><body onload="window.print()">'+divToPrint.innerHTML+'</body></html>');
		newWin.document.close();
		setTimeout(function(){newWin.close();},10);
	}
	
	function willDeleteSmartQR(){
		var id = $('#view_id').val();
		var tipe = $('#view_tipe').val();
		var merk = $('#view_merk').val();
		var lokasi_qr_code = $('#view_lokasi_qr_code').val();
		
		$('#delete_id').val(id);
		$('#delete_tipe').val(tipe);
		$('#delete_merk').val(merk);
		$('#delete_lokasi_qr_code').val(lokasi_qr_code);
		$('#modal_delete').modal('show');
	}
	
	function editViewURLLandingPage(){
		var id = $('#view_id').val();
		var url_redirect = $('#view_url_redirect').val();
		
		var cur_disabled = $('#view_url_redirect').is(':disabled');
		$('#view_url_redirect').prop('disabled', !cur_disabled);
		
		if(cur_disabled==true){
			$('#btn_edit_url').removeClass('glyphicon-edit');
			$('#btn_edit_url').addClass('glyphicon-floppy-disk');
			$('#view_url_redirect').select();
		}
		else{
			$('#btn_edit_url').removeClass('glyphicon-floppy-disk');
			$('#btn_edit_url').addClass('glyphicon-edit');
			updateURLLandingPage(id,url_redirect);
		}
	}
	
	function updateURLLandingPage(id, url_redirect) {
		$('.loading').show();
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url("MsSmartQR/SmartQRUpdate") ?>', 
			data: { id:id, url_redirect: url_redirect }, 
			dataType: 'json',
			success: function (data) {
				$('.loading').hide();
				if(data.result=='SUKSES'){
					$('.btnQRCode').prop('disabled', false);
					$('#view_result').html('URL Landing Page has been successfully updated');
				}
				else{
					alert(data.result+'\n'+data.message.replace(/\\n/g,"\n"));
				}
			}
		});
	}
	
	function deleteSmartQR(){
		var id = $('#view_id').val();
		var reason_deleted = $('#reason_deleted').val();
		var tipe = $('#view_tipe').val();
		var merk = $('#view_merk').val();
		var lokasi_qr_code = $('#view_lokasi_qr_code').val();
		if(reason_deleted==''){
			alert('Alasan menghapus wajib diisi!');
			$('#reason_deleted').focus();
			return false;
		}
		else{
			$('.loading').show();
			$('#modal_delete').modal('hide');
			$.ajax({ 
				type: 'POST', 
				url: '<?php echo site_url("MsSmartQR/SmartQRDelete") ?>', 
				data: { id: id, tipe: tipe, merk: merk, lokasi_qr_code: lokasi_qr_code, reason_deleted: reason_deleted }, 
				dataType: 'json',
				success: function (data) {
					$('.loading').hide();
					// alert(data.result+'\n'+data.message);
					if(data.result=='SUKSES'){
						window.location.href='<?php echo site_url("MsSmartQR") ?>'
					}
				}
			});
		}
	}
	
</script>

