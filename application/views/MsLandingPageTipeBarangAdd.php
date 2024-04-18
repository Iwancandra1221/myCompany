<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>
	.btn{
	width:auto;
	font-size: 14px !important;
	}
	.ui-autocomplete {
	overflow-x: hidden;
	max-height: 264px;
	}
	
</style>
<script>
</script>
<div class="container">
	<div class="form_title">
		<a href="<?php echo base_url('MsLandingPage/TipeBarang') ?>"><i class="glyphicon glyphicon-circle-arrow-left" style="font-size:200%"></i></a>
		<center>Create Tipe Barang</center>
	</div>
	<br>
	<?php echo form_open('MsLandingPage/TipeBarangInsert',array('id' => 'myform')); ?>
	<div class="row">
        <div class="col-3 col-m-4"><big>Merk</big><br><small><em>Required</em></small></div>
        <div class="col-4 col-m-3">
			<select class="form-control" name="merk" id="merk" onchange="javascript:loadBarangList()" required>
				<option value="">Pilih Merk</option>
				<?php
					foreach($merks as $merk){
						echo "<option value='".$merk."'>".$merk."</option>";
					}
				?>
			</select>
		</div>
	</div>
	<div class="row">
        <div class="col-3 col-m-4"><big>Tipe Barang di Kemasan</big><br><small><em>Required</em></small></div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="tipe" placeholder="Ketikkan Tipe Barang" required>
		</div>
	</div>
	
	<div class="row">
        <div class="col-3 col-m-4"><big>Kode Barang Internal</big><br><small><em>Optional</em></small></div>
        <div class="col-9 col-m-8">
			<div class="input-group">
				<span class="input-group-addon">
					<i class="glyphicon glyphicon-search"></i>
				</span>
				<input type="text" class="form-control" id="kd_brg" placeholder="Ketikkan Kode Barang">
			</div><!-- /input-group -->
			<br><small><em>Tekan Enter untuk memasukkan Kode Barang selanjutnya</em></small>
		</div>
	</div>
	
	<div class="row">
        <div class="col-3 col-m-4"></div>
        <div class="col-5 col-m-4">
			<ul class="list-group" id="kd_brgs">
			</ul>
		</div>
	</div>
	
	<div class="row">
        <div class="col-3 col-m-4"></div>
        <div class="col-9 col-m-8">
			<input type="submit" name="save" id="btnSubmit" class="btn btn-dark" value="SAVE & CLOSE" onclick="javascript:addNew=0">
			<input type="submit" name="create_new" class="btn btn-dark" value="SAVE & CREATE NEW" onclick="javascript:addNew=1">
		</div>
	</div>
	
	<?php echo form_close(); ?>
	
	<div id="result">
	</div>
</div>

<script>
	var addNew = 0;
	$(document).ready(function() {
		$("#myform").submit(function() {			
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
						$('#result').html('<div class="alert alert-success alert-dismissible" role="alert" id="alert">'+
						'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
						'<center>Data berhasil disimpan</center>'+
						'</div>');
						
						if(addNew==1){
							$('#myform').trigger("reset");
							$('#kd_brgs').html('');
						}
						else{
						 setTimeout(function() { 
							window.location.href = '<?php echo site_url("MsLandingPage/TipeBarang") ?>';
							}, 1000);
						}
						
					}
					else{
						alert(data.result+'\n'+data.message);
					}
					
				}
			});
			event.preventDefault(); //Prevent the default submit
		});
		
		$('#kd_brg').keypress(function (e) {
			var key = e.which;
			if(key == 13){
				var kd_brg = $('#kd_brg').val().toUpperCase().split(' | ');
				if(kd_brg!=''){
					var bExist  = false;
					$('.kd_brg').each(function(i, obj) {
						if(kd_brg[0] == $(this).val()){
							bExist = true;
						}
					});

					if(bExist==true){
						alert(kd_brg[0]+ ' sudah ada dalam list!');
						$('#kd_brg').val('');
						return false;
					}
					else{
						var add_kd_brg = ' <li class="list-group-item">'+
						'<input type="hidden" name="kd_brg[]" value="'+kd_brg[0]+'" class="kd_brg">'+kd_brg[0]+
						'<button class="btn-danger del_kd_brg" style="float:right" type="button"><i class="glyphicon glyphicon-remove"></i></button>'+
						'</li>';
						
						$('#kd_brgs').append(add_kd_brg);
						$('#kd_brg').val('');
						return false; 
					}
					return false; 
				}
				return false; 
			}
		});
	});
	
	$(document).on("click", ".del_kd_brg" , function() {		
		$(this).closest(".list-group-item").remove();
	});
	
	function loadBarangList(){
		$('.loading').show();
		var merk  = $('#merk').val();
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo site_url("MsLandingPage/GetBarangList?merk='+merk+'") ?>',
			dataType: 'json',
			success: function (data) {
			$('.loading').hide();
				$("#kd_brg").autocomplete({
					source: data
				});			
			}
		});
	}
	
	function SaveAndCreateNew(){
		$("#btnSubmit").click();
		$('#myform').trigger("reset");
		$('#kd_brgs').html('');
	}
	
	
</script>

