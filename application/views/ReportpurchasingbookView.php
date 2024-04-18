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


<div class="">
	<div>
		<br>
		<h1 style="text-align:center;font-weight:bold;font-size:large;">
			Laporan Pembelian Berdasarkan Faktur Pajak
		</h1>
	</div>
	<?php echo form_open($formDest, array("target"=>"_blank","id"=>'f-report')) ?>
 	<div class="form-container">
		<div class="row">
			<div class="col-3">Supplier</div>
			<div class="col-9">
				<!-- LOCAL | IMPORT | kosong = ALL -->
				<input type="radio" name="tipe_supplier" value="LOCAL" checked>
				<label style="margin-right:10px;">Lokal</label>

				<input type="radio" name="tipe_supplier" value="IMPORT">
				<label style="margin-right:10px;">Import</label>

				<input type="radio" name="tipe_supplier" value="">
				<label style="margin-right:10px;">ALL</label>
			</div>
		</div>
		<div class="row">
			<div class="col-3">Kategori Barang</div>
			<div class="col-9">
				<input type="radio" name="kat_brg" value="P" checked>
				<label style="margin-right:10px;">Produk</label>

				<input type="radio" name="kat_brg" value="L">
				<label style="margin-right:10px;">Sparepart</label>

				<input type="radio" name="kat_brg" value="">
				<label style="margin-right:10px;">ALL</label>
			</div>
		</div>
		<div class="row">
			<div class="col-3">Periode BPB</div>
			<div class="col-9">
				<input type="text" name="tgl_bpb_start" value="<?=date('d-M-Y')?>">
				<label style="margin-left:10px;margin-right:10px;">S/D</label>

				<input type="text" name="tgl_bpb_end" value="<?=date('d-M-Y')?>">
				<label style="margin-right:10px;"></label>

				<input type="checkbox" name="is_tgl_bpb" checked>
				<label>ALL</label>
			</div>
			
		</div>
		<div class="row">
			<div class="col-3">Tipe Periode</div>
			<div class="col-9">
				<input type="radio" name="tipe_periode" value="FAKTUR_PAJAK" checked>
				<label style="margin-right:10px;">Tgl Faktur Pajak</label>

				<input type="radio" name="tipe_periode" value="INVOICE">
				<label style="margin-right:10px;">Tgl Invoice</label>
			</div>
		</div>
		<div class="row">
			<div class="col-3">Periode</div>
			<div class="col-9">
				<input type="text" name="periode_start" value="<?=date('d-M-Y')?>">
				<label style="margin-left:10px;margin-right:10px;">S/D</label>

				<input type="text" name="periode_end" value="<?=date('d-M-Y')?>">
				<label style="margin-right:10px;"></label>
			</div>
		</div>
		<div class="row">
			<div class="col-3">Supplier</div>
			<div class="col-9">
				<select name="kd_supplier" style="width: 100%;">
					<option value="">ALL</option>
					<?php
					foreach($supplier as $key => $value){
						echo <<<HTML
						<option value="{$value['Kode_Supplier']}">{$value['Nama_Supplier']} - {$value['Kode_Supplier']}</option>
HTML;
					}
					?>
				</select>

			</div>
		</div>
		<div class="row">
			<div class="col-3">Cabang</div>
			<div class="col-9">
				<select name="kd_supplier" style="width: 100%;">
					<option value="">ALL</option>
					<?php
					foreach($cabang as $value){
						echo <<<HTML
						<option value="{$value['Kd_Lokasi']}">{$value['Nm_Lokasi']} - {$value['Kd_Lokasi']}</option>
HTML;
					}
					?>
				</select>

			</div>
		</div>
		<div class="row">
			<div class="col-3">Gudang</div>
			<div class="col-9">
				<select name="kd_supplier" style="width: 100%;">
					<option value="">ALL</option>
					<?php
					foreach($gudang as $value){
						echo <<<HTML
						<option value="{$value['Kd_Gudang']}">{$value['Nm_Gudang']} - {$value['Kd_Gudang']}</option>
HTML;
					}
					?>
				</select>

			</div>
		</div>
		<div class="row">
			<div class="col-3">Cetak Laporan</div>
			<div class="col-9">
				<!-- SUMMARY | GABUNGAN | PER_SUPPLIER | PER_CABANG | BELUM_EDIT_PAJAK -->
				<input type="radio" name="tipe_laporan" value="SUMMARY" checked>
				<label style="margin-right:10px;">Summary</label>

				<input type="radio" name="tipe_laporan" value="GABUNGAN">
				<label style="margin-right:10px;">Gabungan</label>
				
				<input type="radio" name="tipe_laporan" value="PER_SUPPLIER">
				<label style="margin-right:10px;">Per Supplier</label>
				
				<input type="radio" name="tipe_laporan" value="PER_CABANG">
				<label style="margin-right:10px;">Per Cabang</label>
				
				<input type="radio" name="tipe_laporan" value="BELUM_EDIT_PAJAK">
				<label style="margin-right:10px;">Belum Edit Pajak</label>
			</div>
		</div>
		<div class="row" align="center" style="padding-top:50px;">
			<input type = "submit" name="submit" value="PDF"/>
			<!-- <input type = "submit" name="submit" value="EXCEL"/> -->
		</div>
	 </div>
	<?php echo form_close(); ?>
</div> <!-- /container -->

<script>
	$(document).ready(function() {
		checkedIsTglBpb();
		$("#f-report input[name='tgl_bpb_start']").datepicker({
			format: "dd-MM-yyyy",
			autoclose: true,
		});
		$("#f-report input[name='tgl_bpb_end']").datepicker({
			format: "dd-MM-yyyy",
			autoclose: true,
		});
		$("#f-report input[name='is_tgl_bpb']").click(function(){
			checkedIsTglBpb();
		});
	});
	function checkedIsTglBpb(){
		var isTglBpb = $("#f-report input[name='is_tgl_bpb']").prop("checked");
		if(isTglBpb){
			$("#f-report input[name='tgl_bpb_start']").prop("disabled",true);
			$("#f-report input[name='tgl_bpb_end']").prop("disabled",true);
		}
		else{
			$("#f-report input[name='tgl_bpb_start']").prop("disabled",false);
			$("#f-report input[name='tgl_bpb_end']").prop("disabled",false);
		}
	}
</script>