<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>
	.btn{
	width:auto;
	font-size: 14px !important;
	}
</style>
<script>
	var tipe = <?php echo(json_encode($tipe));?>;
	var tipe_merk = <?php echo(json_encode($tipe_merk));?>;
	// var merk_group = <?php //echo(json_encode($merk_group));?>;
	var group_param = <?php echo(json_encode($group_param));?>;
	var LokasiQRCode = <?php echo(json_encode($LokasiQRCode));?>;
    $(document).ready(function() {
	$("#tipe").autocomplete({
	source: tipe
	});			
	});
</script>
<div class="container">
	<div class="form_title">
		<a href="<?php echo base_url('MsLandingPage') ?>"><i class="glyphicon glyphicon-circle-arrow-left" style="font-size:200%"></i></a>
		<center>Create QR Code</center>
	</div>
	<br>
	<?php echo form_open('MsLandingPage/Insert',array('id' => 'myform')); ?>
	<div class="row">
        <div class="col-3 col-m-4"><big>Tipe Barang</big><br><small><em>Required</em></small></div>
        <div class="col-9 col-m-8">
			<div class="input-group">
				<span class="input-group-addon">
					<i class="glyphicon glyphicon-search"></i>
				</span>
				<input type="text" class="form-control" name="tipe" id="tipe" placeholder="Ketikkan Tipe Barang" required>
			</div><!-- /input-group -->
		</div>
	</div>
	<div class="row">
        <div class="col-3 col-m-4"><big>Merk</big><br><small><em>Required</em></small></div>
        <div class="col-4 col-m-4">
			<input type="text" class="form-control" name="merk" id="merk" placeholder="Merk (readonly)" readonly required>
		</div>
	</div>
	
	<div class="row">
        <div class="col-3 col-m-4"><big>Lokasi QR Code</big><br><small><em>Required</em></small></div>
        <div class="col-4 col-m-4">
			<select class="form-control" name="lokasi_qr_code" id="lokasi_qr_code" onchange="javascript:lokasiQRCode()" required>
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
			<input type="text" class="form-control" name="AddInfoParam" id="AddInfoParam" placeholder="Qty">
		</div>
        <div class="col-3 col-m-3 additional">
			<big><span id="AddInfo" style="line-height: 34px;"></span></big>
		</div>
	</div>
	
	<div class="row">
        <div class="col-3 col-m-4"><big>URL Landing Page</big><br><small><em>Optional</em></small></div>
        <div class="col-9 col-m-8">
			<input type="url" class="form-control" name="url_redirect" placeholder="URL Redirect">
		</div>
	</div>
	<div id="param">
		<?php
			// foreach($param as $p){
			?>
			<!--div class="row">
				<div class="col-3 col-m-4"><big><?php //echo $p ?></big><br><small><em>Optional</em></small></div>
				<div class="col-9 col-m-8">
					<input type="hidden" name="old_param_name[]" value="<?php //echo $p ?>">
					<input type="text" class="form-control" name="old_param_value[]" placeholder="<?php //echo $p ?>">
				</div>
			</div-->
			<?php
			// }
		?>
	</div>
	
	<div id="add_more" class="row" style="background:grey; margin:0">
	</div>
	
	<div class="row">
        <div class="col-3 col-m-4"><button type="button" class="btn btn-default" onclick="javascript:add_more()"><i class="glyphicon glyphicon-plus-sign"></i> Add More</button></div>
        <div class="col-9 col-m-8"></div>
	</div>
	
	<div class="row">
	<div class="col-3 col-m-4"></div>
	<div class="col-9 col-m-8">
		<input type="submit" name="generate" class="btn btn-dark" value="GENERATE & CLOSE">
		<input type="reset" name="create_new" class="btn btn-dark" value="GENERATE & ADD NEW" onclick="javascript:CreateNew()">
	</div>
	</div>
	<?php echo form_close(); ?>
	
	<div id="result">
	</div>
</div>

<script>
	var add_new = '	<div class="add_more">'+
	'<div class="col-3 col-m-4">'+
	'<input type="text" name="param_name[]" class="form-control" placeholder="Name (tanpa spasi)" onkeydown="return (event.which >= 48 && event.which <= 57) ||(/[a-z]/i.test(event.key)) || event.which == 8 || event.which == 46" required>'+
	'</div>'+
	'<div class="col-9 col-m-8">'+
	'<div class="input-group">'+
	'<input type="text" name="param_value[]" class="form-control" placeholder="Value" required>'+
	'<span class="input-group-btn">'+
	'<button class="btn btn-danger del_more" type="button"><i class="glyphicon glyphicon-remove"></i></button>'+
	'</span>'+
	'</div>'+
	'</div>'+
	'</div>';
	
	$(document).ready(function() {
		// $('#add_more').hide();
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
					// alert(data);
					if(data.result=='SUKSES'){
						$('#view_qrcode').attr("src", 'data:image/png;base64,' +data.qrcode);
						$('#btnDownload').attr("href", 'data:image/png;base64,' +data.qrcode);
						
						$('#result').append('<div class="alert alert-success alert-dismissible" role="alert" id="alert">'+
						'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
						'<center>Data berhasil disimpan</center>'+
						'</div>'+
						
						'<div class="form-group">'+
						'<label class="col-xs-3"></label>'+
						'<div class="col-xs-3">'+
						'<input type="hidden" value="'+data.url+'" id="view_url">'+
						'<center>'+data.lokasi_qr_code+'</center>'+
						'<img src="data:image/png;base64,'+data.qrcode+'" height="250px" id="view_qrcode">'+
						'</div>'+
						'<div class="col-xs-4">'+
						'<button type="button" class="btn btn-sm" onclick="javascript:copyURL()">Copy URL</button>'+
						'<br>'+
						'<br>'+
						'<a href="data:image/png;base64,'+data.qrcode+'" download="'+data.filename+'" class="btn btn-sm">Download</a>'+
						'</div>'+
						'</div>'
						
						);
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
	});
	
	function cari_merk(tipe){
		// if(merk[tipe]){
		// // $('#merk').val(merk[tipe]);
		// }
		// else{
		// $('#tipe').val('');
		// $('#merk').val('');
		// }
		
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
	
	function add_more(){
		$('#add_more').append(add_new);
	}
	
	function add_params(group){
		$('#param').html('');
		
		// if(param['ALL']){
		// for(i=0;i<param['ALL'].length;i++){
		// add_param(param['ALL'][i]);
		// }
		// }
		// if(param[merk]){
		// for(i=0;i<param[merk].length;i++){
		// add_param(param[merk][i]);
		// }
		// }
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
		'<input type="text" class="form-control" name="old_param_value[]" placeholder="'+value+'">'+
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
		var copyText = document.getElementById("view_url").value;
		navigator.clipboard.writeText(copyText);
		alert("URL QRCode Berhasil di-copy:\n" + copyText);
	}
	
	$(document).on("click", ".del_more" , function() {		
		$(this).closest(".add_more").remove();
	});
	
</script>

