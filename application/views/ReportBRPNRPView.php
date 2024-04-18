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

		$('#div_supplier').hide();
		$('#div_pdf').hide(); 
		$('#dp1').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var StartDt = $('#dp1').datepicker('getDate');
			$('#dp2').datepicker("setStartDate", StartDt);
		});
		
		$('#dp2').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var EndDt = $('#dp2').datepicker('getDate');
			$('#dp1').datepicker("setEndDate", EndDt);
		});
	} );
</script>
<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
  <?php echo form_open($formDest, array("target"=>"_blank")) ?>
	<div class="form-container">
		<div class="row">
			<div class="col-3 col-m-3">Periode PO</div>
			<div class="col-2 col-m-2 date">
				<input type="text" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1" autocomplete="off" required>
			</div>
			<div class="col-1 col-m-1">SD</div>
			<div class="col-2 col-m-2 date">
				<input type="text" class="form-control" id="dp2" placeholder="mm/dd/yyyy" name="dp2" autocomplete="off" required>
			</div>
		</div>
		<div class="row">
         <div class="col-3">
            Kategori Barang
         </div>
         <div class="col-9 col-md-8">
            <input type="radio" name="kategori" id="kategoriP" value="P"> <label for="kategoriP">Produk</label> &nbsp;&nbsp;&nbsp;
            <input type="radio" name="kategori" id="kategoriS" value="S"  checked> <label for="kategoriS">Sparepart</label>
         </div>
      </div>
      <div class="row" id="div_supplier">
			<div class="col-3">Supplier</div>
			<div class="col-8 col-m-8 date">
				<select  class="form-control" name="supplier" id="supplier" novalidate > 
					<?php 
						foreach($listsupplier as $s)
						{
							if ($s->Kode_Supplier=='ALL')
							{
								echo("<option value='".$s->Kode_Supplier."#".$s->Nama_Supplier."' selected>".$s->Nama_Supplier."</option>");
							}
							else
							{
								echo("<option value='".$s->Kode_Supplier."#".$s->Nama_Supplier."'>".$s->Nama_Supplier."</option>");
							}
						}			  
					?>
				</select>
			</div>
	  </div>  
	  <?php //echo(json_encode($gudang_sumber));?>


      <div class="row" id="div_gudang_sumber">
			<div class="col-3">Gudang Sumber</div>
			<div class="col-8 col-m-8 date">
				<select  class="form-control" name="gudang_sumber" id="gudang_sumber" novalidate> 
					<?php 
						foreach($gudang_sumber as $s)
						{
							if ($s->Kode_Gudang=='ALL')
							{
								echo("<option value='".$s->Kode_Gudang."#".$s->Nama_Gudang."' selected>".$s->Nama_Gudang."</option>");
							}
							else
							{
								echo("<option value='".$s->Kode_Gudang."#".$s->Nama_Gudang."'>".$s->Nama_Gudang."</option>");
							}
						}			  
					?>
				</select>
			</div>
		</div>  

		<div class="row" id="div_gudang_target">
			<div class="col-3">Gudang Target</div>
			<div class="col-8 col-m-8 date">
				<select  class="form-control" name="gudang_target" id="gudang_target" novalidate> 
					<?php 
						foreach($gudang_target as $s)
						{
							if ($s->Kode_Gudang=='ALL')
							{
								echo("<option value='".$s->Kode_Gudang."#".$s->Nama_Gudang."' selected>".$s->Nama_Gudang."</option>");
							}
							else
							{
								echo("<option value='".$s->Kode_Gudang."#".$s->Nama_Gudang."'>".$s->Nama_Gudang."</option>");
							} 
						}			  
					?>
				</select>
			</div>
		</div>  


		<div class="row">
         <div class="col-3">
            LAPORAN
         </div>
         <div class="col-9 col-md-8">
            <input type="radio" name="laporan" id="exportbrpnrp" value="1"  onclick="javascript:hideMenu('export_brpnrp')" checked>  
				<label for="exportbrpnrp"> BRP NRP</label> &nbsp;&nbsp;&nbsp;
            <input type="radio" name="laporan" id="laporannrp" value="2" onclick="javascript:hideMenu('laporan_nrp')"> 
            <label for="laporannrp"> NRP</label> &nbsp;&nbsp;&nbsp; 
            <input type="radio" name="laporan" id="exportnrpuntukefaktur" value="3" onclick="javascript:hideMenu('export_nrp_efaktur')"> 
            <label for="exportnrpuntukefaktur"> NRP UNTUK E-FAKTUR</label> &nbsp;&nbsp;&nbsp; 
            <input type="radio" name="laporan" id="laporansj3" value="4" onclick="javascript:hideMenu('laporan_sj3')"> 
            <label for="exportnrp"> SJ RETUR (DENGAN NO BRP)</label> 
         </div>     
      </div> 

      <div class="row" align="center" style="padding-top:50px;" id="div_excel">
         <input type = "submit" name="btnPreview" value="PREVIEW"/>
         <input type = "submit" name="btnExcel" value="EXPORT EXCEL"/>
      </div>

      <div class="row" align="center" style="padding-top:50px;" id="div_pdf"> 
         <input type = "submit" name="btnPreview" value="PREVIEW"/>
         <input type = "submit" name="btnExcel" value="EXPORT EXCEL"/>
         <input type = "submit" name="btnPdf" value="EXPORT PDF"/>
      </div>
    </div>
	<?php echo form_close(); ?>
</div> <!-- /container -->
<script type="text/javascript">
 
	function hideMenu(g){
		if(g=='export_brpnrp'){ 
			$('#div_gudang_sumber').show(); 
			$('#div_gudang_target').show(); 
			$('#div_supplier').hide(); 
			$('#div_excel').show(); 
			$('#div_pdf').hide(); 
		}
		else if (g=='laporan_nrp' || g=='laporan_sj3'){ 
			$('#div_gudang_sumber').hide(); 
			$('#div_gudang_target').hide(); 
			$('#div_supplier').show();
			$('#div_excel').hide(); 
			$('#div_pdf').show(); 
		} 
		else{ 
			$('#div_gudang_sumber').hide(); 
			$('#div_gudang_target').hide(); 
			$('#div_supplier').hide();
			$('#div_excel').show(); 
			$('#div_pdf').hide(); 
		}
	} 
</script>
	 
	  
	  
	  
 
 


