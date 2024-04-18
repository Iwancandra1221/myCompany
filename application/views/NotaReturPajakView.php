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

<div class="container">
	<div>
		<br>
		<h1 style="text-align:center;font-weight:bold;font-size:large;">
			Nota Retur Pajak
		</h1>
	</div>
 	
	 	<div class="form-container" style="">
	 		<div class="row">
				<div class="col-3">
					Cari No Retur Berdasarkan No Retur Pajak
				</div>
				<div class="col-9 col-md-8">
					<form id="search_faktur" method="POST">
						<table border="0">
							<tr>
								<td>
									<input type="text" class="form-control" name="search" id="search">
								</td>
								<td>
									<button type="submit" class="btn btn-secondary">
										Cari
									</button>
								</td>
							</tr>
						</table>
					</form>
					<table class="table">
						<tr>
							<td width="200px">No Faktur Pajak</td>
							<td width="1px">:</td>
							<td id="no_faktur_pajak"></td>
						</tr>
						<tr>
							<td>Tahun Faktur</td>
							<td>:</td>
							<td id="tahun_faktur"></td>
						</tr>
						<tr>
							<td>No Faktur</td>
							<td>:</td>
							<td id="no_faktur"></td>
						</tr>
						<tr>
							<td>Referensi</td>
							<td>:</td>
							<td id="referensi"></td>
						</tr>
					</table>
				</div>
			</div>

			<div class="row">
				<div class="col-12"><hr></div>
				<div class="col-3">
					Cetak
				</div>
				<div class="col-9 col-md-8">
					<input type="radio" name="cetak" id="cetak" value="laporan" checked> 
					<label>Laporan Nota Retur Pajak</label>
					<br>
					<input type="radio" name="cetak" id="cetak" value="retur"> 
					<label>Retur Belum diedit pajak</label>
				</div>
			</div>
			<div class="row" id="div_cabang">
				<div class="col-3">Partner Type</div>
				<div class="col-9 col-md-8">
					<select name="status" id="status" class="form-control">
						<option value='TRADISIONAL'>TRADISIONAL</option>
						<option value='MODERN_OUTLET'>MODERN OUTLET</option>
				  		<option value='MO_CABANG'>MO CABANG</option>
				  		<option value='PROYEK'>PROYEK</option>
				  		<option value='COUNTER'>COUNTER</option>
					</select>
				</div>
			</div>

			<div class="row" id="div_cabang">
				<div class="col-3">Wilayah</div>
				<div class="col-9 col-md-8">
					<select name="wilayah" id="wilayah" class="form-control">
						<option value='all'>ALL</option>
						<?php
							for ($i=0; $i < count($wilayah); $i++) { 
						?>
								<option value="<?php echo $wilayah[$i]['Nama_Wilayah']; ?>"><?php echo $wilayah[$i]['Nama_Wilayah']; ?></option>
						<?php
							}
						?>
					</select>
				</div>
			</div>

			<div class="row" id="div_cabang">
				<div class="col-3">Periode</div>
				<div class="col-4">
					<input type="text" name="periode_dari" id="periode_dari" value="<?php echo date('Y/m/d') ?>" required>
				</div>
				<div class="col-1 text-center">
					s/d
				</div>
				<div class="col-4">
					<input type="text" name="periode_sampai" id="periode_sampai" value="<?php echo date('Y/m/d') ?>" required>
				</div>
			</div>

			<div class="row" id="div_cabang">
				<div class="col-3">Kategori</div>
				<div class="col-9 col-md-8">
					<select name="kategori" id="kategori" class="form-control">
						<option value='all'>PRODUK dan SPAREPART</option>
						<option value='p'>PRODUK</option>
				  		<option value='s'>SPAREPART</option>
					</select>
				</div>
			</div>

			<div class="row" id="div_cabang">
				<div class="col-3">Tipe Faktur</div>
				<div class="col-9 col-md-8">
					<select name="TipeFaktur" id="TipeFaktur" class="form-control">
						<option value="all">ALL</option>
						<?php
							for ($i=0; $i < count($TipeFaktur); $i++) { 
						?>
								<option value="<?php echo $TipeFaktur[$i]['Tipe_Faktur']; ?>"><?php echo $TipeFaktur[$i]['Tipe_Faktur']; ?></option>
						<?php
							}
						?>
					</select>
				</div>
			</div>

			
			<div class="row" align="center" style="padding-top:50px;">
				<input type="submit" class="btn btn-default" name="pdf" onclick="pdf()" value="PDF"/>
				<input type="submit" class="btn btn-default" name="excel" onclick="excel()" value="EXCEL"/>
			</div>
	</div>
</div> 


<script type="text/javascript">
	$(document).ready(function(){

		$('#periode_dari,#periode_sampai').datepicker({
			format: "yyyy/mm/dd",
			autoclose: true
		});

		$( "#search_faktur" ).submit(function( event ) {
			var data = 'search='+document.getElementById('search').value;

			if(document.getElementById('search').value!==''){
				console.log(data);
				$.ajax({
					type 	: 'POST',	
					url 	: '<?php echo site_url('NotaReturPajak/SearchFaktur'); ?>',
					data   	: data,
					success : function(data) {


						response = JSON.parse(data);

						if(response.length) {

							$.each(response, function(key,trk) {

								document.getElementById('no_faktur_pajak').innerHTML=trk.No_FakturP;
								document.getElementById('tahun_faktur').innerHTML=trk.Year_FakturP;
								document.getElementById('no_faktur').innerHTML=trk.No_Faktur;
								document.getElementById('referensi').innerHTML=trk.No_Ref;

							});

						}else{
							document.getElementById('no_faktur_pajak').innerHTML='';
							document.getElementById('tahun_faktur').innerHTML='';
							document.getElementById('no_faktur').innerHTML='';
							document.getElementById('referensi').innerHTML='';
							alert("Nomor Faktur "+document.getElementById('search').value+" tidak tersedia!!!")
						}

					}

				});
			}else{
				alert("Anda belum mengisi pencarian!!!");
			}

			return false
		});
	});

	function pdf(){

		var cetak = $("input[name='cetak']:checked").val();

		var data  = 'cetak='+cetak;
			data += '&status='+document.getElementById('status').value;
			data += '&wilayah='+document.getElementById('wilayah').value;
			data += '&periode_dari='+document.getElementById('periode_dari').value;
			data += '&periode_sampai='+document.getElementById('periode_sampai').value;
			data += '&kategori='+document.getElementById('kategori').value;
			data += '&TipeFaktur='+document.getElementById('TipeFaktur').value;
			window.open('<?php echo site_url('NotaReturPajak/pdf?'); ?>'+data, '_blank');
			
	}

	function excel(){

		var cetak = $("input[name='cetak']:checked").val();

		var data  = 'cetak='+cetak;
			data += '&status='+document.getElementById('status').value;
			data += '&wilayah='+document.getElementById('wilayah').value;
			data += '&periode_dari='+document.getElementById('periode_dari').value;
			data += '&periode_sampai='+document.getElementById('periode_sampai').value;
			data += '&kategori='+document.getElementById('kategori').value;
			data += '&TipeFaktur='+document.getElementById('TipeFaktur').value;
			window.open('<?php echo site_url('NotaReturPajak/excel?'); ?>'+data, '_blank');
			
	}
</script>

