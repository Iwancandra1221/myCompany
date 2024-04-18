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

	function myFunction() { 
		tipe = $("#laporan :selected").val();
		$('#div_potype').show();
		$('#div_wilayah').hide();
		$('#div_gudang').hide(); 
		$('#div_divisi').hide();
		if (tipe == "A01" || tipe == "A02") {
			$('#div_potype').hide();
		} else if (tipe == 'B01' || tipe == 'B02' || tipe == 'B03')
		{
			$('#div_excel').hide(); 
			$('#div_pdf').show();  
		}
		else if (tipe == 'D01' || tipe == 'D02' || tipe == 'D03')
		{ 
			$('#div_potype').hide();
			$('#div_wilayah').show();
			$('#div_gudang').show();
			$('#div_divisi').show();
			$('#div_excel').hide(); 
			$('#div_pdf').show();
		}
		else
		{
			$('#div_excel').show(); 
			$('#div_pdf').hide();  
		}
		
		var code = tipe.slice(0,1);
		$('.lap').hide();
		$('.lap'+code).show();
		
	} 
</script>
<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
	<?php 
        echo form_open("ReportPO/Proses", array("target"=>"_blank"));
	?>
	<div class="form-container">
		<div class="row">
			<div class="col-3">Jenis Laporan</div>
			<div class="col-8 col-m-8">
				<select  class="form-control" name="laporan" id="laporan" required  onchange="myFunction()">
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
		<div class="row lap lapA lapB lapC lapD">
			<div class="col-3 col-m-3">Periode PO</div>
			<div class="col-3 col-m-3 date">
				<input type="text" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1" autocomplete="off" required>
			</div>
			<div class="col-1 col-m-1">SD</div>
			<div class="col-3 col-m-3 date">
				<input type="text" class="form-control" id="dp2" placeholder="mm/dd/yyyy" name="dp2" autocomplete="off" required>
			</div>
		</div>
		<div class="row lap lapA lapB lapD">
			<div class="col-3">Supplier</div>
			<div class="col-8 col-m-8 date">
				<select  class="form-control" name="supplier" id="supplier">
					<option value=""></option>
					<?php 
						foreach($supplier as $s)
						{
							echo("<option value='".$s->Kode_Supplier."#".$s->Nama_Supplier."'>".$s->Nama_Supplier."</option>");
						}			  
					?>
				</select>
			</div>
		</div>
		<div class="row" id="div_potype">
			<div class="col-3">PO Type</div>
			<div class="col-8 col-m-8 date">
				<select  class="form-control" name="potype" id="potype" novalidate>
					<option value=""></option>  
						<option value='Other'>PO Barang Lokal</option> 
						<option value='IMPORT'>PO Barang Import</option> 
						<option value='MKT'>PO Barang MP</option> 
						<option value='GA'>PO Barang Umum</option> 
				</select>
			</div>
		</div>
		<div class="row" id="div_wilayah" hidden>
			<div class="col-3">Wilayah</div>
			<div class="col-8 col-m-8">
				<select  class="form-control" name="wilayah" id="wilayah" novalidate>
					<option value=""></option>
					<?php 
						foreach($wilayah as $s)
						{
							echo("<option value='".$s->Kode_Wilayah."'>".$s->Nama_Wilayah."</option>");
						}			  
					?>
				</select>
			</div>
		</div>
		<div class="row" id="div_gudang" hidden> 
			<div class="col-3">Gudang</div>
			<div class="col-8 col-m-8">
				<select  class="form-control" name="gudang" id="gudang" novalidate>
					<option value=""></option>
					<?php 
						foreach($gudang as $s)
						{
							echo("<option value='".$s->Kode_Gudang."'>".$s->Nama_Gudang."</option>");
						}			  
					?>
				</select>
			</div>
		</div>
		<div class="row" id="div_divisi" hidden> 
			<div class="col-3">Divisi</div>
			<div class="col-8 col-m-8">
				<select  class="form-control" name="divisi" id="divisi" novalidate>
					<option value=""></option>
					<?php 
						foreach($divisi as $s)
						{
							echo("<option value='".$s->Kd_Divisi."'>".$s->Nama_Divisi."</option>");
						}			  
					?>
				</select>
			</div>
		</div>
		<div class="row lap lapA lapB lapC lapD">
			<div class="col-3">Kategori</div>
			<div class="col-8 col-m-8">
				<input type="radio" name="kategori" id="p1" value="P" checked> <label for="p1">PRODUK</label> &nbsp;&nbsp;&nbsp;
				<input type="radio" name="kategori" id="p2" value="S"> <label for="p2"> SPAREPART</label>
			</div>
		</div>
		<div class="row">
			<div class="col-3"></div>
			<div class="col-8 col-m-8">
			<input type="submit" name="btnPreview" class="lap lapA lapB lapC lapD" value="PREVIEW" />
			<input type="submit" name="btnExcel" class="lap lapA lapD" value="EXCEL"/>
			</div>
		</div>
        <!--div class="row" align="center" style="padding-top:50px;"  id="div_excel" >
			<input type="submit" name="btnPreview" value="PREVIEW" style="display:none;"/>
			<input type="submit" name="btnExcel" value="EXCEL"/> 
		</div>
        <div class="row" align="center" style="padding-top:50px; display: none;"  id="div_pdf" > 
			<input type="submit" name="btnPreview" value="PREVIEW" style="display:none;"/> 
			<input type="submit" name="btnPdf" value="PDF" />
		</div-->
	</div>
	<?php echo form_close(); ?>
</div> <!-- /container -->