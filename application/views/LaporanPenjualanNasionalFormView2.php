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
</style>

<script>
    $(document).ready(function() {
		$('#dp1').datepicker({
			format: "dd/mm/yyyy",
			autoclose: true
		});
		$('#dp2').datepicker({
			format: "dd/mm/yyyy",
			autoclose: true
		});
		$('#ex_cash').prop('checked', true);
		$('#ex_bass').prop('checked', true);
		$("#grup_subkategori").prop("checked", false);
		$(".opt_produk").hide();
		$(".opt_wilayah").hide();
	});
</script>
<div style="display:<?php if(!$this->session->flashdata('error')) echo 'none';?>; position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;" id="login-alert" class="alert alert-danger col-sm-12">
  <!-- error msg here -->
  <?php 
    echo $this->session->flashdata('error'); 
    if(isset($_SESSION['error'])){
        unset($_SESSION['error']);
    }
  ?>
</div>
<div style="display:<?php if(!$this->session->flashdata('info')) echo 'none';?>; position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;" id="info-alert" class="alert alert-success col-sm-12">
  <?php 
    echo $this->session->flashdata('info'); 
    if(isset($_SESSION['info'])){
        unset($_SESSION['info']);
    }
  ?>
</div>
<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
	<?php 
        echo form_open("LaporanPenjualanNasionalv2/Proses", array("target"=>"_blank"));
	?>
	<div class="form-container">
		<div class="row">
			<div class="col-3">Jenis Laporan</div>
			<div class="col-9 col-m-8">
				<select  class="form-control" name="laporan" id="laporan" required>
					<option value=""></option>
					<?php 
						foreach($laporan as $s)
						{
							echo("<option value='".$s->kode."'>".$s->kode." - ".$s->nama."</option>");
						}			  
					?>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="col-3 col-m-3">Periode</div>
			<div class="col-3 col-m-3">
				<input type="text" class="form-control" id="dp1" placeholder="dd/mm/yyyy" name="dp1" autocomplete="off" required>
			</div>
			<div class="col-1 col-m-1" id="divSD">SD</div>
			<div class="col-3 col-m-3" id="divDp2" >
				<input type="text" class="form-control" id="dp2" placeholder="dd/mm/yyyy" name="dp2" autocomplete="off" required>
			</div>
		</div>
		<div class="row">
			<div class="col-3">Produk / Sparepart</div>
			<div class="col-9 col-m-8">
				<input type="radio" name="kategori" id="p0" value="ALL" onchange="javascript:show_exclude('a')" checked> <label for="p0">ALL</label> &nbsp;&nbsp;&nbsp;
				<input type="radio" name="kategori" id="p1" value="P" onchange="javascript:show_exclude('p')"> <label for="p1">PRODUK</label> &nbsp;&nbsp;&nbsp;
				<input type="radio" name="kategori" id="p2" value="S" onchange="javascript:show_exclude('s')"> <label for="p2"> SPAREPART</label>
			</div>
		</div>
		<div class="row">
			<div class="col-3">Divisi</div>
			<div class="col-9 col-m-8">
				<select  class="form-control" name="divisi" required>
					<option value="ALL">ALL</option>
					<?php 
						foreach($divisi as $s)
						{
							echo("<option value='".$s->divisi."'>".$s->divisi."</option>");
						}			  
					?>
				</select>
			</div>
		</div>
		
		<div class="row">
			<div class="col-3">Lokal / Import</div>
			<div class="col-9 col-m-8">
			<?php if ($_SESSION["logged_in"]["isUserPabrik"]==1) { ?>
				<input type="radio" name="type" id="q1" value="LOKAL" checked> <label for="q1">LOKAL</label> &nbsp;&nbsp;&nbsp;
			<?php } else { ?>	
				<input type="radio" name="type" id="q0" value="ALL" checked> <label for="q0">ALL</label> &nbsp;&nbsp;&nbsp;
				<input type="radio" name="type" id="q1" value="LOKAL"> <label for="q1">LOKAL</label> &nbsp;&nbsp;&nbsp;
				<input type="radio" name="type" id="q2" value="IMPORT"> <label for="q2"> IMPORT</label> &nbsp;&nbsp;&nbsp;
			<?php } ?>	
			</div>
		</div>
		
		<div class="row">
			<div class="col-2 opt_wilayah">Wilayah</div>
			<label class="col-1">&nbsp;</label> 
			<div class="col-9 col-m-8">
				<select  class="form-control opt_wilayah" name="wilayah" required>
					<option value="ALL">ALL</option>
					<?php 
						foreach($wilayah as $s)
						{
							echo("<option value='".$s->WilayahGroup."'>".$s->WilayahGroup."</option>");
						}			  
					?>
				</select>
			</div>
		</div>
		
		<div class="row" class="opt_produk">
			<div class="col-3"></div>
			<div class="col-9 col-m-8">
				<input type="checkbox" name="grup_subkategori" id="grup_subkategori" class="opt_produk" value="1"><label class="opt_produk" for="grup_subkategori">GRUP SUBKATEGORI PRODUK</label>
				<label>&nbsp;</label> 
			</div>
		</div>
		<div class="row">
			<div class="col-3"></div>
			<div class="col-9 col-m-8">
				<input type="checkbox" name="ex_cash" id="ex_cash" class="chk_exclude" value="1"><label class="chk_exclude" for="ex_cash">EXCLUDE CASH KONSUMEN</label>
				<label>&nbsp;</label> 
				<input type="checkbox" name="ex_bass" id="ex_bass" class="chk_exclude" value="1"> <label class="chk_exclude" for="ex_bass">EXCLUDE BASS</label>
			</div>
		</div>
        <div class="row" align="center" style="padding-top:0px;">
			<input type="submit" name="btnPreview" value="PREVIEW"/>
			<input type="submit" name="btnExcel" value="EXCEL"/>
		</div>
	</div>
	<?php echo form_close(); ?>
</div> <!-- /container -->

<script type="text/javascript">
	$('#laporan').on('change', function() {
		var id_laporan  = $("#laporan").val();
		if (id_laporan=='A01' || id_laporan=='A02' || id_laporan=='A03' || id_laporan=='A04' || id_laporan=='A05'){
			$("#divSD").hide();
			$("#dp2").removeAttr("required"); 
			$("#divDp2").hide();
			
			$('#dp1').val('');
			$('#dp2').val('');
			$('#dp1').datepicker('destroy');
			$('#dp1').attr("placeholder", "mm/yyyy");
			$('#dp1').datepicker({
				format: "mm/yyyy",
				autoclose: true,
				viewMode: "months", 
				minViewMode: "months"
				// changeMonth: true,
				// changeYear: true,
				// showButtonPanel: true,
				// onClose: function(dateText, inst) { 
				// $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth,1 ));
				// alert(1);
				// }
			});
			$(".opt_produk").hide();
			$(".opt_wilayah").hide();
		}
		else if (id_laporan=='C01' || id_laporan=='C02' || id_laporan=='C03'){
			
		
			$("#divSD").hide();
			$("#dp2").removeAttr("required"); 
			$("#divDp2").hide();
			
			$('#dp1').val('');
			$('#dp2').val('');
			$('#dp1').datepicker('destroy');
			$('#dp1').attr("placeholder", "yyyy");
			$('#dp1').datepicker({
				format: "yyyy",
				autoclose: true,
				viewMode: "years", 
				minViewMode: "years"
	
	
				// changeMonth: true,
				// changeYear: true,
				// showButtonPanel: true,
				// onClose: function(dateText, inst) { 
				// $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth,1 ));
				// alert(1);
				// }
			});
			$(".opt_produk").hide();
			$(".opt_wilayah").hide();
			if (id_laporan=='C01'){
			$(".opt_wilayah").show();
			}
		}
		else if (id_laporan=='D01' || id_laporan=='D02'){
			$("#divSD").show();
			$("#divDp2").show();
			$("#dp2").attr("required", true);
			
			$('#dp1').val('');
			$('#dp2').val('');
			$('#dp1').datepicker('destroy');
			$('#dp1').attr("placeholder", "dd/mm/yyyy");
			$('#dp1').datepicker({
				format: "dd/mm/yyyy",
				autoclose: true
			});

			$(".opt_produk").hide();
			$(".opt_wilayah").hide();
		}
		else{
			$("#divSD").show();
			$("#divDp2").show();
			$("#dp2").attr("required", true);
			
			$('#dp1').val('');
			$('#dp2').val('');
			$('#dp1').datepicker('destroy');
			$('#dp1').attr("placeholder", "dd/mm/yyyy");
			$('#dp1').datepicker({
				format: "dd/mm/yyyy",
				autoclose: true
			});

			$(".opt_produk").show();
			$(".opt_wilayah").hide();
		}
		
		
		
	});
	
	
	function show_exclude(val){
		var id_laporan  = $("#laporan").val();
		if(val=='a'){
			$('.chk_exclude').show();
			$('#ex_cash').prop('checked', true);
			$('#ex_bass').prop('checked', true);

			if (id_laporan=="B01" || id_laporan=="B02" || id_laporan=="B03" || id_laporan=="B04") {
				$(".opt_produk").show();
				$("#grup_subkategori").prop("checked", true);
			}
		} else if (val=='p') {
			$('.chk_exclude').hide();
			$('#ex_cash').prop('checked', false);
			$('#ex_bass').prop('checked', false);
			if (id_laporan=="B01" || id_laporan=="B02" || id_laporan=="B03" || id_laporan=="B04") {
				$(".opt_produk").show();
				$("#grup_subkategori").prop("checked", true);
			}
		} else {
			$('.chk_exclude').show();
			$('#ex_cash').prop('checked', true);
			$('#ex_bass').prop('checked', true);
			$(".opt_produk").hide();
			$("#grup_subkategori").prop("checked", false);
		}
	 }
 
 
 
</script>