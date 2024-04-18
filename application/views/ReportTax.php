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
	input{
		color: black;
	}
</style>
<script>
	$(document).ready(function() {
		$('#tgl_awal').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		});
		$('#tgl_akhir').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		});
		$('#tgl_awal2').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		});
		$('#tgl_akhir2').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		});
		
		
	});
</script>
<div class="container">
	<h3 style="text-align:center;font-size:20px;font-weight:bold;">EXPORT DATA PAJAK</h3>
	<?php echo form_open($formDest, array("target"=>"_blank")) ?>
	<div class="form-container" style="height:700px!important;">
		<div class="row" id="rowDivisi">
			<div class="col-3">
				Kriteria
			</div>
			<div class="col-9 col-md-8">
				<input type="radio" name="kriteria" value="FAKTUR" checked> <label for="">FAKTUR</label> &nbsp;&nbsp;&nbsp;
				<input type="radio" name="kriteria" value="RETUR"> <label for="">RETUR</label> &nbsp;&nbsp;&nbsp;
			</div>
		</div>
		<div class="row">
			<div class="col-3">
				FP
			</div>
			<div class="col-9 col-md-8">
				<input type="radio" name="tgl_edit_fp" value="0" checked> <label for="">TGL FP</label> &nbsp;&nbsp;&nbsp;
				<input type="radio" name="tgl_edit_fp" value="1"> <label for="">TGL EDIT FP</label> &nbsp;&nbsp;&nbsp;
			</div>
		</div>
		<div class="row">
			<div class="col-3">Tanggal FP</div>
			
			<div class="input-group col-md-3" style="float:left;">
				<input type="text" class="form-control" id="tgl_awal" placeholder="mm/dd/yyyy" name="tgl_awal" value=""  autocomplete="off">
				<span class="input-group-addon" ><span class="glyphicon glyphicon-calendar"></span></span>
			</div>
			<div style="float:left;margin-top: 10px;">s/d</div>
			<div class="input-group col-md-3" style="float:left;">
				<input type="text" class="form-control" id="tgl_akhir" placeholder="mm/dd/yyyy" name="tgl_akhir" value=""  autocomplete="off">
				<span class="input-group-addon" ><span class="glyphicon glyphicon-calendar"></span></span>
			</div>
			
		</div>
		<div class="row">
			<div class="col-3">Tanggal Edit FP</div>
			
			<div class="input-group col-md-3" style="float:left;">
				<input type="text" class="form-control" id="tgl_awal2" placeholder="mm/dd/yyyy" name="tgl_awal2" value=""  autocomplete="off">
				<span class="input-group-addon" ><span class="glyphicon glyphicon-calendar"></span></span>
			</div>
			<div style="float:left;margin-top: 10px;">s/d</div>
			<div class="input-group col-md-3" style="float:left;">
				<input type="text" class="form-control" id="tgl_akhir2" placeholder="mm/dd/yyyy" name="tgl_akhir2" value=""  autocomplete="off">
				<span class="input-group-addon" ><span class="glyphicon glyphicon-calendar"></span></span>
			</div>
			
		</div>
		<div class="row">
			<div class="col-3">
				Kategori Barang
			</div>
			<div class="col-9 col-md-8">
				<input type="checkbox" name="chk_produk" value="1" checked> <label for="kategoriP">Produk</label> &nbsp;&nbsp;&nbsp;
				<input type="checkbox" name="chk_sparepart" value="1"> <label for="kategoriS">Sparepart</label> &nbsp;&nbsp;&nbsp;
				<input type="checkbox" name="chk_service" value="1"> <label for="kategoriS">Service</label>
			</div>
		</div>
		
		<div class="row" id="rowDivisi">
			<div class="col-3">
				Wilayah
			</div>
			<div class="col-9 col-md-8">
				<select name="wilayah" class="form-control">
					<option value=''>ALL</option>
					<?php
					if(isset($wilayah)){
						for($i=0;$i<count($wilayah);$i++)
						{
							echo("<option value='".$wilayah[$i]["WILAYAH"]."'>".$wilayah[$i]["WILAYAH"]."</option>");
						}
					}
					
					?>
				</select>
			</div>
		</div>

		<div class="row">
			<div class="col-3">Kode Cabang FP</div>
			<div class="col-9 col-md-8">
				<input type="text" name="kode_cabang">
			</div>
		</div>
		<div class="row" id="rowDivisi">
			<div class="col-3">
				Tipe Faktur
			</div>
			<div class="col-9 col-md-8">
				<select name="tipe_faktur" class="form-control">
					<option value=''>ALL</option>
					<option value="R">R</option>
					<option value="P">P</option>
					<option value="H">H</option>
					<option value="O">O</option>
					<option value="A">A</option>
				</select>
			</div>
		</div>
		
		<div class="row" id="rowDivisi">
			<div class="col-3">
				Tipe PKP
			</div>
			<div class="col-9 col-md-8">
				<select name="tipe_pkp" class="form-control">
					<option value=''>ALL</option>
					<option value="PKP">PKP</option>
					<option value="NON PKP">NON PKP</option>
				</select>
			</div>
		</div>
		<div class="row" id="rowDivisi">
			<div class="col-3">
				Tipe E Faktur
			</div>
			<div class="col-9 col-md-8">
				<input type="radio" name="tipe_e_faktur" value="1" checked> <label for="">E-Faktur (H2H PAJAKKU)</label> &nbsp;&nbsp;&nbsp;
				<input type="radio" name="tipe_e_faktur" value="0"> <label for="">E-Faktur (e-SPT DJP)</label> &nbsp;&nbsp;&nbsp;
			</div>
		</div>
		<div class="row" align="center" style="padding-top:50px;">
			<input type = "submit" name="submit" value="EXCEL"/>
		</div>
	</div>
	<?php echo form_close(); ?>
	</div> <!-- /container -->
	<script type="text/javascript">
		
		
		function group(g){
			if(g=='gudang'){
				$('#div_gudang').show();
				$('#div_cabang').hide();
			}
			else{
				$('#div_cabang').show();
				$('#div_gudang').hide();
			}
		}
		$('#div_cabang').hide();
		
	</script>