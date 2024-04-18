<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script>
	
	var SelectedSN = '<?php echo $SelectedSN; ?>';
	var SNList = <?php echo(json_encode($SNList));?>;
	
	function initializeForm(){
		$("#tab-manual").css("background-color", "yellow");
		$("#tab-manual").css("border", "#transparent");
		$("#tab-manual").css("cursor", "none");
		$("#fieldset-import").hide();
		$("#btnExcel").css("disabled", true);
	}
	
	function validateSN(){
		
		if($("#productcode").val()=='') return false;
		// ori
		var SNMin1 = $('#snMin').text();
		var SNMax1 = $('#snMax').text();
		
		//edited
		var SNMin2 = $('#serialnumber-min').val();
		var SNMax2 = $('#serialnumber-max').val();
		
		if(SNMin2.length!=SNMin1.length){
			alert('Angka Min SN melebihi batas karakter!');
			$('#serialnumber-min').val(SNMin1);
			return false;
		}
		
		if(SNMax2.length!=SNMax1.length){
			alert('Angka Max SN melebihi batas karakter!');
			$('#serialnumber-max').val(SNMax1);
			return false;
		}		
		
		var codeMin1 = SNMin1.replace(/[^a-zA-Z]+/g, '');
		var codeMin2 = SNMin2.replace(/[^a-zA-Z]+/g, '');
		
		var noMin1 = SNMin1.replace(/[a-zA-Z]+/g, '');
		var noMin2 = SNMin2.replace(/[a-zA-Z]+/g, '');
		noMin1 = parseInt(noMin1);
		noMin2 = parseInt(noMin2);
		
		var codeMax1 = SNMax1.replace(/[^a-zA-Z]+/g, '');
		var codeMax2 = SNMax2.replace(/[^a-zA-Z]+/g, '');
		
		var noMax1 = SNMax1.replace(/[a-zA-Z]+/g, '');
		var noMax2 = SNMax2.replace(/[a-zA-Z]+/g, '');
		noMax1 = parseInt(noMax1);
		noMax2 = parseInt(noMax2);
		
		if(codeMin1!=codeMin2){
			alert('Kode Huruf Min SN tidak sama!');
			$('#serialnumber-min').val(SNMin1);
			return false;
		}
		
		if(codeMax1!=codeMax2){
			alert('Kode Huruf Max SN tidak sama!');
			$('#serialnumber-max').val(SNMax1);
			return false;
		}
		
		if(noMin2<noMin1){
			alert('No seri jangan kurang dari No Seri '+SNMin1);
			$('#serialnumber-min').val(SNMin1);
			return false;
		}
		
		if(noMax2>noMax1){
			alert('No seri jangan lebih dari No Seri '+SNMax1);
			$('#serialnumber-max').val(SNMax1);
			return false;
		}
		
		if(noMin2>noMax2){
			alert('Min No Seri jangan lebih dari Max No Seri '+SNMax2);
			$('#serialnumber-min').val(SNMin1);
			return false;
		}
		return true;
		
	}
	
	function validateSave() {
		var serialMin = $("#serialnumber-min").val();
		var serialMax = $("#serialnumber-max").val();
		
		var noMin = serialMin.replace(/[a-zA-Z]+/g, '');
		var noMax = serialMax.replace(/[a-zA-Z]+/g, '');
		noMin = parseInt(noMin);
		noMax = parseInt(noMax);
		
		var range = noMax - noMin + 1;
		if(range > 400){
			alert('Maksimal Range adalah 400 No Seri, Range yang diinput adalah '+range+', Periksa Kembali No Seri yang Diinput!' );
			return false;
		}
		
		if (validateSN()) {
			$(".loading").show();
			$("#FormBlacklist").submit();
			$(".loading").hide();	
		}
	}
	
	function validateProduct(){
		var p = $("#productid").val();
		
		if(p=='') { return false; }
		
		if(jQuery.inArray(p, SNList) !== -1 ){
			var sArray = p.split(" | ");
			$("#snMin").text(sArray[0]);
			$("#snMax").text(sArray[1]);
			
			$("#serialnumber-min").val(sArray[0]);
			$("#serialnumber-max").val(sArray[1]);
			
			$("#productcode").val(sArray[2]);
			$("#productbrand").val(sArray[3]);
			$("#productpartid").val(sArray[4]);
		}
		else{
			alert("Kode Barang Tidak Terdaftar");
			$("#productid").val('');
			$("#snMin").text('');
			$("#snMax").text('');
			
			$("#serialnumber-min").val('');
			$("#serialnumber-max").val('');
			
			$("#productcode").val('');
			$("#productbrand").val('');
			$("#productpartid").val('');
		}			
	}
	
	function validateProductSelect(){
		var p = $("#productidSelect").find(':selected').text();
		if(p=='') { return false; }
		console.log(p);
		var sArray = p.split(" | ");
		$("#snMin").text(sArray[0]);
		$("#snMax").text(sArray[1]);
		
		$("#serialnumber-min").val(sArray[0]);
		$("#serialnumber-max").val(sArray[1]);
		
		$("#productcode").val(sArray[2]);
		$("#productbrand").val(sArray[3]);
		$("#productpartid").val(sArray[4]);		
	}
	
    $(document).ready(function() {
    	initializeForm();
		
	    $("#productid").autocomplete({
			source: SNList,
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
			validateProduct();
		});
	    $('#productidSelect').on('change', function() {
			validateProductSelect();
		});
		
	    $("#btnSubmit").click(function(){
			var lanjut = false;
			var productId = $("#productcode").val();
			var message = $("#message").val();
			var message_internal = $("#message_internal").val();
			var serialMin = $("#serialnumber-min").val();
			var serialMax = $("#serialnumber-max").val();
			var csrf_bit = $("input[name=csrf_bit]").val();
			
	    	if (productId=="") {
				alert("Kode Barang Belum Dipilih");
				}  else if (serialMin=="" || serialMax=="") {
				alert("Serial Number Belum Diisi Lengkap");
				}else if (message=="") {
				alert("Message Belum Dipilih");
				}else if (message_internal=="") {
				alert("Message Internal Belum Diisi");
				} else {
				validateSave();
				// $("#FormBlacklist").submit();
			}
		});
		
		// validateProduct();
		validateProductSelect();
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
	height:28px;
	margin-top:5px;
	}
	input, select {
	text-transform: uppercase;color:black!important;
	}
	.btn {
	font-size:13px!important;
	border:1px solid #000!important;
	}
	
	.ui-autocomplete {
    overflow-y: scroll;
    overflow-x: hidden;
    max-height: 300px;
	}
</style>


<div class="container">
	<div class="form-container" style="height:580px!important;">
		<div class="form-title">
			TAMBAH BLACKLIST NO SERI
		</div>
		<div style="clear:both;"></div>
		<fieldset class="fieldset" id="fieldset-manual">
			<legend></legend>
			<?php echo form_open($controller."/BlacklistInsert", array("target"=>"_blank", "id"=>"FormBlacklist"));?>
			<div class="row">
				<div class="col-4 col-m-4">Browse SN</div>
				<div class="col-8 col-m-8">
					<select class="form-control" name="productidSelect" id="productidSelect">
						<?php if($LogId!=''){?>
						<option value="<?php echo $LogId ?>" selected="selected"><?php echo $SelectedSN ?></option>
						<?php } ?>
					</select>
					<!--input type="text" class="form-control" style="font-size:14px; height:24px;" name="productid" id="productid" placeholder="Pilih SN" value="<?php //echo $SelectedSN ?>"-->
					<input type="text" class="form-control" name="kode_brg" id="productcode" placeholder="Kode Barang (readonly)" readonly>
					<input type="text" class="form-control" name="productbrand" id="productbrand" placeholder="Merk (readonly)" readonly>
					<input type="text" class="form-control" name="productpartid" id="productpartid" placeholder="PartId (readonly)" readonly>
				</div>
			</div>
			<div class="row">
				<div class="col-4 col-m-4">Min Serial Number</div>
				<div class="col-8 col-m-8">
					Min: <span id="snMin">-</span><br>
					<input type="text" name="range_awal" id="serialnumber-min" onblur="validateSN();" />
				</div>
			</div>
			<div class="row">
				<div class="col-4 col-m-4">Max Serial Number</div>
				<div class="col-8 col-m-8">
					Max: <span id="snMax">-</span><br>
					<input type="text" name="range_akhir" id="serialnumber-max" onblur="validateSN();"/>
					<br>
					<small>Maksimal Range No Seri adalah 400 per 1 Kali submit</small>
				</div>
			</div>
			<div class="row">
				<div class="col-4 col-m-4">Message</div>
				<div class="col-8 col-m-8">
					<select name="message" class="form-control" id="message" required >
						<option value=""></option>
						<?php
							foreach($pesan as $p) { 
								echo "<option value='".$p->Pesan."'>".$p->Pesan."</option>";
							} 
						?>
					</select>
				</div>
			</div>
			<div class="row">
				<div class="col-4 col-m-4">Message Internal</div>
				<div class="col-8 col-m-8">
					<input type="text" name="message_internal" class="form-control" id="message_internal" />
				</div>
			</div>		
	        <div class="row row-button" align="center">
				<div class="btn" id="btnSubmit" name="btnSubmit">SUBMIT</div>
				<!-- <input type="submit" name="btnExcel" id="btnExcel" value="GENERATE"/> -->
				<a href="<?php echo base_url().$controller ?>"><input type="button" class="btn btnBack" value="KEMBALI"/></a>
			</div>
			<?php echo form_close(); ?>
		</fieldset>
	</div>
</div> <!-- /container -->

<script type="text/javascript">
$(document).ready(function() {
    $('#productidSelect').select2({
		ajax: {
			url: '<?php echo base_url() ?><?=$controller ?>/GetListSNSelect',
			dataType: 'json',
			data: function (params) {
				return {
					search: params.term,
					// logid: '<?php ISSET($LogId) ? $LogId : '' ?>'
				}
			},
			processResults: function (response) {
				return {
					results: response
				};
			},
			cache: true,
		},
	});
});
</script>