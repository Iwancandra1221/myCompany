<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>
.ui-autocomplete {
    overflow-x: hidden;
    overflow-y: scroll;
    max-height: 260px;
}
</style>

<script>
	var dealers = <?php echo(json_encode($dealer));?>;
	
	$(document).ready(function() {
	    $("#dealer").autocomplete({
	      source: dealers
	    });
		
	    $('#dealer').on('change', function() {
	      var p = $("#dealer").val();
	      var sArray = p.split(" | ");
		  if(sArray[1]){
			  $("#kd_plg").val(sArray[0]);
			  $("#nm_plg").val(sArray[1]);
		  }
		  else{
			$("#dealer").val('');
			  $("#kd_plg").val('');
			  $("#nm_plg").val('');
		  }
	    });
		
	});


	
</script>


<div class="container">
	<div class="form_title"><center>MASTER PENGALI LIMIT</center></div>
	<br>
	<?php echo form_open('MsPengaliLimit/Save'); ?>
	<div class="row">
        <div class="col-3 col-m-4"></div>
        <div class="col-9 col-m-8">
			<input type="radio" name="opt" id="rad_wilayah" value="WILAYAH" checked> <label for="rad_wilayah" style="cursor:pointer">Pengali Limit By Wilayah</label>
			<input type="radio" name="opt" id="rad_toko" value="TOKO"> <label for="rad_toko" style="cursor:pointer">Pengali Limit By Toko</label>
		</div>
	</div>
	
	<div class="row">
        <div class="col-3 col-m-4">Divisi</div>
        <div class="col-9 col-m-8">
			<select name="divisi" class="form-control" required>
				<option value="MISHIRIN">MISHIRIN</option>
				<option value="SPAREPART">SPAREPART</option>
			</select>
		</div>
	</div>
	
	<div class="row div_partner_type">
        <div class="col-3 col-m-4">Partner Type</div>
        <div class="col-9 col-m-8">
			<select name="partner_type" id="partner_type" class="form-control" onchange="myFunction_Partner_Type()"  required>
				<option value="TRADISIONAL" selected>TRADISIONAL</option>
				<option value="MODERN OUTLET">MODERN OUTLET</option>
				<option value="MO CABANG">MO CABANG</option>
				<option value="PROYEK">PROYEK</option>
			</select>	
		</div>
	</div>

	<div class="row div_wilayah">
        <div class="col-3 col-m-4">Wilayah</div>
        <div class="col-9 col-m-8">
			<select class="wilayah" name="wilayah" id="wilayah" class="form-control" style="width:100%; padding:5px" required>
				<option value="ALL">ALL</option>
			</select>
		</div>
	</div>

	<div class="row div_toko">
        <div class="col-3 col-m-4">Toko</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="dealer" id="dealer" placeholder="Ketikkan Nama Toko">
		</div>
	</div>
	
	<div class="row div_toko">
        <div class="col-3 col-m-4">Kode</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="kd_plg" id="kd_plg" placeholder="Kode (readonly)" style="background:lightgray" readonly>
		</div>
	</div>
	<div class="row div_toko">
        <div class="col-3 col-m-4">Nama Toko</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control" name="nm_plg" id="nm_plg" placeholder="Nama Toko (readonly)" style="background:lightgray" readonly>
		</div>
	</div>
	
	<div class="row">
        <div class="col-3 col-m-4">Pengali Limit (%)</div>
        <div class="col-9 col-m-8">
			<input type="number" class="form-control" name="pengali" placeholder="0" step=".01" required>
		</div>
	</div>
	
	<div class="row div_toko">
        <div class="col-3 col-m-4">Max Limit (Rp)</div>
        <div class="col-9 col-m-8">
			<input type="text" class="form-control numeric" name="max_limit" placeholder="0">
		</div>
	</div>
	
	<div class="row">
        <div class="col-3 col-m-4"></div>
        <div class="col-9 col-m-8">
			<input type="submit" class="btn btn-primary" value="Submit">
			<input type="button" class="btn btn-danger" onclick="location.href = '<?php echo site_url('MsPengaliLimit') ?>';" value="Cancel">
		</div>
	</div>
	<?php echo form_close(); ?>
</div>

<script>
	$(document).ready(function() {
		
		$("#rad_wilayah").change(function() {
			$('.div_wilayah').hide();
			$('.div_toko').hide();
			$('#dealer').prop('required',false);
			if(this.checked==true){
				$('.div_wilayah').show();
				$('#wilayah').prop('required',true);
			}
		});
		
		$("#rad_toko").change(function() {
			$('.div_wilayah').hide();
			$('.div_toko').hide();
			$('#wilayah').prop('required',false);
			if(this.checked==true){
				$('.div_toko').show();
				$('#dealer').prop('required',true);
			}
		});
		$('.div_toko').hide();
	} );
</script>
<script>
	$(document).on("input", ".numeric", function() {
		this.value = addCommas(this.value.replace(/\D/g,''));
	});
	
	function addCommas(nStr) {
		nStr += '';
		var x = nStr.split('.');
		var x1 = x[0];
		var x2 = x.length > 1 ? '.' + x[1] : '';
		var rgx = /(\d+)(\d{3})/;
		while (rgx.test(x1)) {
			x1 = x1.replace(rgx, '$1' + ',' + '$2');
		}
		return x1 + x2;
	}
</script>


<script>
	
	function myFunction_Partner_Type() {
		$("#wilayah").html('<option value="ALL">ALL</option>');
                var partner_type = document.getElementById("partner_type").value;
				var wilayah='<option value="ALL">ALL</option>';
				$.ajax({
					type: "POST",
					url: '<?php echo $this->API_URL."/ConfigPenjualan/GetWilayah?api=APITES"; ?>&partner_type='+partner_type,
					success: function (data) {
					
						data = JSON.parse(data);
							for(i = 0; i < data.length; i++){
							
								wilayah +='<option value="'+data[i].WILAYAH+'">'+data[i].WILAYAH+'</option>';    

							$("#wilayah").html(wilayah);

					}
					
					}
				});
                    
            }   

	myFunction_Partner_Type();

</script>