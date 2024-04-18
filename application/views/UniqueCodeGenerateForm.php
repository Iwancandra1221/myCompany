<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script>
	var Products = <?php echo(json_encode($products));?>;

	function initializeForm(){
		$("#tab-manual").css("background-color", "yellow");
		$("#tab-manual").css("border", "#transparent");
		$("#tab-manual").css("cursor", "none");
		$("#fieldset-import").hide();
		$("#btnExcel").css("disabled", true);
	}

	function validate() {
		var lanjut = false;
		var productId = $("#productcode").val();
		var serialMin = $("#serialnumber-min").val();
		var serialMax = $("#serialnumber-max").val();
		var csrf_bit = $("input[name=csrf_bit]").val();
		// var msg = "":

		if (serialMin!="" && serialMax!="" && serialMin > serialMax) {
			alert("Serial Number Max Harus Lebih Besar Dari Serial Number Min");
			$("#serialnumber-max").val(serialMin);
		} else if (serialMin!="" && serialMax!="") {
			// $(".loading").show();
			// $.post("<?php echo site_url('UniqueCodeGenerator/CheckLog'); ?>", {
			// 	productId 		: productId,
			// 	serialMin 		: serialMin,
			// 	serialMax 		: serialMax,
			// 	csrf_bit		: csrf_bit
			// }, function(data2){
			// 	if (data2.result=="FAILED") {
			// 		var msg="";
			// 		log = data2.logs;
			// 		msg = "Unique Code Sudah Pernah digenerate untuk Product ID "+productId+"\n";
			// 		msg+= "SerialNoMin: " + log[0].SerialNoMin + "\n";
			// 		msg+= "SerialNoMax: " + log[0].SerialNoMax + "\n";
			// 		msg+= "Digenerate Pada: "+log[0].LogDate+"\n\nLanjutkan ?";

			// 		if (confirm(msg)==true) {
			// 			// lanjut = true;
			// 			$("#FormUniqueCode").submit();
			// 		} else {
			// 			// alert("here");
			// 			lanjut = false;
			// 		}
			// 	} else {
			// 		// alert("submit");
			// 		// $("#FormUniqueCode").submit();
			// 		lanjut = true;
			// 		$("#FormUniqueCode").submit();
			// 		// $("#btnExcel").css("disabled",false);
			// 	}
			// }, 'json',errorAjax);
			// $(".loading").hide();	
		}
		// return lanjut;
	}

	function validateSave() {
		var lanjut = false;
		var productId = $("#productcode").val();
		var serialMin = $("#serialnumber-min").val();
		var serialMax = $("#serialnumber-max").val();
		var csrf_bit = $("input[name=csrf_bit]").val();
		// var msg = "":

		if (serialMin!="" && serialMax!="") {
			if (serialMin.length != serialMax.length) {
				alert("Jumlah Digit Min dan Max Berbeda\nCheck Kembali Serial Number Yang Anda Input!!");
			} else {
				$(".loading").show();
				$.post("<?php echo site_url('UniqueCodeGenerator/CheckLog'); ?>", {
					productId 		: productId,
					serialMin 		: serialMin,
					serialMax 		: serialMax,
					csrf_bit		: csrf_bit
				}, function(data2){
					if (data2.result=="FAILED") {
						var msg="";
						log = data2.logs;
						msg = "Unique Code Sudah Pernah digenerate untuk Product ID "+productId+"\n";
						msg+= "SerialNoMin: " + log[0].SerialNoMin + "\n";
						msg+= "SerialNoMax: " + log[0].SerialNoMax + "\n";
						msg+= "Digenerate Pada: "+log[0].LogDate + "\n";
						msg+= "Oleh: "+log[0].CreatedBy+"";
						// msg+= "Digenerate Pada: "+log[0].LogDate+"\n\nLanjutkan ?";
						alert(msg);
						lanjut = false;
						// if (confirm(msg)==true) {
						// 	// lanjut = true;
						// 	$("#FormUniqueCode").submit();
						// } else {
						// 	// alert("here");
						// 	lanjut = false;
						// }
					} else {
						// alert("submit");
						// $("#FormUniqueCode").submit();
						lanjut = true;
						$("#FormUniqueCode").submit();
						// $("#btnExcel").css("disabled",false);
					}
				}, 'json',errorAjax);
				$(".loading").hide();	
			}
		}
		// return lanjut;
	}

	function validateProduct(){
		var lanjut = false;
		var productId = $("#productcode").val();
		var serialMin = $("#serialnumber-min").val();
		var serialMax = $("#serialnumber-max").val();
		var csrf_bit = $("input[name=csrf_bit]").val();
		$(".loading").show();
		$.post("<?php echo site_url('UniqueCodeGenerator/CheckProductId'); ?>", {
			productId 		: productId,
			csrf_bit		: csrf_bit
		}, function(data){
			if (data.result!="SUCCESS") {
				alert("Kode Barang Tidak Terdaftar");
				$("#productid").val("");
				$("#productcode").val("");
				$("#productbrand").val("");
				$("#productpartid").val("");	
			} else {
			}

		},'json',errorAjax);		
		$(".loading").hide();				
	}

    $(document).ready(function() {
    	initializeForm();

	    $("#productid").autocomplete({
	      source: Products
	    });			

    	$(".tab").click(function(){
    		var tabId = $(this).attr("id");
    		$(".tab").css("background-color", "transparent");
    		$(".tab").css("border", "transparent");
    		$(".tab").css("cursor", "pointer");
    		
    		$(this).css("background-color", "yellow");
    		$(this).css("border", "#ccc");
    		$(this).css("cursor", "none");
	
    		var mode = $(this).attr("mode");
    		$(".fieldset").hide();
    		$("#fieldset-"+mode).show();
    	});

	    $('#productid').on('change', function() {
	      var p = $("#productid").val();
	      var sArray = p.split(" | ");
	      $("#productcode").val(sArray[0]);
	      $("#productbrand").val(sArray[1]);
	      $("#productpartid").val(sArray[2]);
	      validateProduct();
	    });

	    $("#btnSubmit").click(function(){
			var lanjut = false;
			var productId = $("#productcode").val();
			var serialMin = $("#serialnumber-min").val();
			var serialMax = $("#serialnumber-max").val();
			var csrf_bit = $("input[name=csrf_bit]").val();

	    	if (productId=="") {
				alert("Kode Barang Belum Dipilih");
			} else if (serialMin=="" || serialMax=="") {
				alert("Serial Number Belum Diisi Lengkap");
			} else {
				validateSave();
				// $("#FormUniqueCode").submit();
			}
		});
	});
</script>

<style type="text/css">
	.row {
    line-height:30px; 
    vertical-align:middle;
    clear:both;
	}
	.row-label, .row-input {
    float:left;
	}
	.row-label {
    padding-left: 15px;
    width:180px;
	}
	.row-input {
    width:420px;
	}
	.row-button {
		margin-top:20px;
		margin-bottom:25px;
	}
	.form-title{ font-size: 16pt; font-weight: bold; text-align: center;}
	.tab-bar {
		clear:both;
	}
	.tab {
		border:1px solid transparent;
		border-radius:10px;
		text-align: center;
		float:left;
		/*background-color: #fff;*/
		padding:3px;
		width: 100px;
		margin-right:10px;
		margin-bottom:20px;
		margin-top:20px;
		cursor:pointer;
	}
	#tab-import{
		width: 150px;
	}
	.form-control{
		font-size:10px;
		height:18px;
		margin-top:5px;
	}
	input, select {
		text-transform: uppercase;
		color: black;
	}
	.btn {
		font-size:13px!important;
		border:1px solid #000!important;
	}
</style>


<div class="container">
	<div class="form-container" style="height:500px!important;">
		<div class="form-title">
			GENERATE UNIQUE CODE
		</div>
		<div class="tab-bar" style="height:50px;">
		</div>
		<div class="tab-bar" style="display:none;">
			<div class="tab" id="tab-manual" mode="manual">Manual</div>
			<div class="tab" id="tab-import" mode="import">Import Excel</div>
		</div>
		<div style="clear:both;"></div>
		<fieldset class="fieldset" id="fieldset-manual">
			<?php echo form_open("UniqueCodeGenerator/Generate", array("target"=>"_blank", "id"=>"FormUniqueCode"));?>
			<div class="row">
				<div class="col-2 col-m-1"></div>
				<div class="col-2 col-m-3">User</div>
				<div class="col-8 col-m-8"><input type = "text" name = "username" value="<?php echo($_SESSION["logged_in"]["username"]);?>" style="width:500px;" readonly></div>
			</div>
			<div class="row">
				<div class="col-2 col-m-1"></div>
				<div class="col-2 col-m-3" >Product ID</div>
				<div class="col-8 col-m-8">
					<input type="text" style="width:500px;" name="productid" id="productid" placeholder="Kode Barang">
					<input type="text" style="width:500px;" name="productcode" id="productcode" placeholder="Kode Barang (readonly)" readonly>
					<input type="text" style="width:500px;" name="productbrand" id="productbrand" placeholder="Merk (readonly)" readonly>
					<input type="text" style="width:500px;" name="productpartid" id="productpartid" placeholder="PartId (readonly)" readonly>
				</div>
			</div>
			<div class="row">
				<div class="col-2 col-m-1"></div>
				<div class="col-2 col-m-3">Min Serial Number</div>
				<div class="col-8 col-m-8">
	              <input type="text" name="serialnumber-min" id="serialnumber-min" style="width:500px;" onblur="validate();" />
				</div>
			</div>
			<div class="row">
				<div class="col-2 col-m-1"></div>
				<div class="col-2 col-m-3">Max Serial Number</div>
				<div class="col-8 col-m-8">
	              <input type="text" name="serialnumber-max" id="serialnumber-max" style="width:500px;" onblur="validate();"/>
				</div>
			</div>		
	        <div class="row row-button" align="center">
				<div class="btn" id="btnSubmit" name="btnSubmit">GENERATE</div>
				<!-- <input type="submit" name="btnExcel" id="btnExcel" value="GENERATE"/> -->
				<a href="<?php echo(base_url('UniqueCodeGenerator'));?>"><input type="button" class="btn btnBack" value="KEMBALI"/></a>
			</div>
			<?php echo form_close(); ?>
		</fieldset>
		<fieldset class="fieldset" id="fieldset-import">
			<form method="post" action="<?php echo base_url('UniqueCodeGenerator/importExcel') ?>" enctype="multipart/form-data" target="_blank">
				<div class="row">
					<div class="col-4 col-m-4">User</div>
					<div class="col-8 col-m-8"><input type = "text" name = "username2" value="<?php echo($_SESSION["logged_in"]["username"]);?>" readonly></div>
				</div>
				<div class="row">
					<div class="col-4 col-m-4">Pilih File</div>
					<div class="col-8 col-m-8"><input type="file" name="file"></div>
				</div>
				<div class="row">
					<div class="col-4 col-m-4">Data Ada di Sheet ke</div>
					<div class="col-8 col-m-8"><input type="number" name="idx-sheet" value="1"></div>
				</div>
				<div class="row">
					<div class="col-4 col-m-4">Data Mulai Baris ke</div>
					<div class="col-8 col-m-8"><input type="number" name="idx-start-row" value="2"></div>
				</div>
				<div class="row">
					<div class="col-4 col-m-4">Indeks Kolom Serial Number</div>
					<div class="col-8 col-m-8"><input type="number" name="idx-col-sn" value="2"></div>
				</div>
				<div class="row">
					<div class="col-4 col-m-4">Indeks Kolom Product ID</div>
					<div class="col-8 col-m-8"><input type="number" name="idx-col-product" value="3"></div>
				</div>
				<div class="row">
					<div class="col-4 col-m-4"></div>
					<div class="col-8 col-m-8">
						<button type="submit">Import & Generate</button>
						<a href="<?php echo(base_url('UniqueCodeGenerator'));?>"><input type="button" class="btnBack" value="KEMBALI"/></a>
					</div>
				</div>
			</form>
		</fieldset>
	</div>
</div> <!-- /container -->

<script type="text/javascript">
</script>