<script>
	$(".loading").show();
	$(".loading").hide();

	$(document).ready(function() {
	  //$("#LogOmzetNasional").hide();
	} );
</script>

<style type="text/css">
  th, td { border:1px solid #000; padding: 2px 10px 2px 10px; }
</style>

<div class="container">
  <div class="page-title"><?php echo($opt);?></div>
  <?php 
	echo form_open($formURL, array("target"=>"_blank")) 
  ?>
  <div class="form-container">
  	<div class="row" style="">
		<div class="col-3 col-m-4" align="right">
			Jenis Laporan
		</div>
		<div class="col-9 col-m-8">
		  	<select name="jns_laporan" id="jns_laporan" style="width:100%;">
				<option value="A">Omzet Netto Per Dealer Per Alamat Kirim</option>
				<option value="B">Omzet Netto Per Dealer</option>
				<option value="C">Omzet Netto Summary</option>
		  </select>
		</div>
	</div>
	<div class="row PERIODE" style="">
       <div class="col-3 col-m-4" align="right">
          Tahun
       </div>
       <div class="col-9 col-m-8">
          <input type="text" name="tahun" id="Tahun" value="<?php echo(date('Y'));?>" style="width:100%;color: black;">
       </div>
    </div>
    <div class="row PERIODE" style="">
       	<div class="col-3 col-m-4" align="right">
          	Bulan
       	</div>
       	<div class="col-9 col-m-8">
          <select name="bulan" id="Bulan" style="width:100%;">
            <option value="01" <?php echo((date("m")=="01")?"selected":"")?>>JANUARI</option>
            <option value="02" <?php echo((date("m")=="02")?"selected":"")?>>FEBRUARI</option>
            <option value="03" <?php echo((date("m")=="03")?"selected":"")?>>MARET</option>
            <option value="04" <?php echo((date("m")=="04")?"selected":"")?>>APRIL</option>
            <option value="05" <?php echo((date("m")=="05")?"selected":"")?>>MEI</option>
            <option value="06" <?php echo((date("m")=="06")?"selected":"")?>>JUNI</option>
            <option value="07" <?php echo((date("m")=="07")?"selected":"")?>>JULI</option>
            <option value="08" <?php echo((date("m")=="08")?"selected":"")?>>AGUSTUS</option>
            <option value="09" <?php echo((date("m")=="09")?"selected":"")?>>SEPTEMBER</option>
            <option value="10" <?php echo((date("m")=="10")?"selected":"")?>>OKTOBER</option>
            <option value="11" <?php echo((date("m")=="11")?"selected":"")?>>NOVEMBER</option>
            <option value="12" <?php echo((date("m")=="12")?"selected":"")?>>DESEMBER</option>
            <option value="00">GABUNGAN</option>
          </select>
       </div>
    </div>
    <!--
	<div class="row PERIODE" style="">
		<div class="col-3 col-m-4" align="right">
		  Periode
		</div>
		<div class="col-3 col-m-3">
		  	<input type="date" name="periode_start" id="periode_start" value="" placeholder="mm/dd/yyyy" style="width:100%;color: black;">
		</div>
		
		<div class="col-1 col-m-2">
		  	SD
		</div>
		<div class="col-3 col-m-3">
		  	<input type="date" name="periode_end" id="periode_end" value="" placeholder="mm/dd/yyyy" style="width:100%;color: black;">
		</div>
		
	</div>
	-->
	<div class="row PERIODE" style="">
		<div class="col-3 col-m-4" align="right">
			Produk / Sparepart
		</div>
		<div class="col-9 col-m-8">
		  	<input type="radio" name="produk_sparepart" value="ALL" style="margin-left: 20px;" checked>
		  	<label>ALL</label>

		  	<input type="radio" name="produk_sparepart" value="P" style="margin-left: 20px;">
		  	<label>PRODUK</label>

		  	<input type="radio" name="produk_sparepart" value="S" style="margin-left: 20px;">
		  	<label>SPAREPARTY</label>
		</div>
	</div>
	<div class="row PERIODE" style="">
	   <div class="col-3 col-m-4" align="right">
		  Partner Type
	   </div>
	   <div class="col-9 col-m-8">
		  <select name="partner_type" id="Bulan" style="width:100%;">
		  	<option value="">All</option>
			<option value="TRADISIONAL">TRADISIONAL</option>
			<option value="MODERN OUTLET">MODERN OUTLET</option>
			<option value="MO CABANG">MO CABANG</option>
			<option value="PROYEK">PROYEK</option>
		  </select>
	   </div>
	</div>
	<div class="row" align="center">
	  <div class="col-12 col-m-12">
		<input type = "submit" name="btnExcel" value="EXCEL"/>
	  </div>
	</div>
  </div>
  <?php echo form_close(); ?>
  <div style='clear:both;height:20px;'></div>
  <?php if ($opt=="OMZET NASIONAL") { ?>
  <div class="form-container" id="LogOmzetNasional" style="height:350px;width:95%;overflow-y:scroll;background-color:#fff!important;">
	<table>
	  <thead>
		<tr>
		  <th width="25%">Wilayah</th>
		  <th width="40%" colspan="2">Proses</th>
		  <th width="15%" style="display:none;">Locked</th>
		  <th width="10%" style="display:none;">Action</th>
		</tr>
	  </thead>
	  <tbody>
	  </tbody>
	</table>
  </div>
  <?php } ?>
</div> 