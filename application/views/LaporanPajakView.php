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
			Laporan Pajak
		</h1>
	</div>
 	
	 	<div class="form-container">

			<div class="row">
				<div class="col-3">Periode</div>
				<div class="col-4">
					<input type="text" class="form-control" name="periode_dari" id="periode_dari" value="<?php echo date('d-m-Y') ?>" required>
				</div>
				<div class="col-1 text-center">
					s/d
				</div>
				<div class="col-4">
					<input type="text" class="form-control" name="periode_sampai" id="periode_sampai" value="<?php echo date('d-m-Y') ?>" required>
				</div>
			</div>

			<div class="row">
				<div class="col-3">Tanggal Cetak</div>
				<div class="col-4">
					<input type="text" class="form-control" name="tgl_cetak" id="tgl_cetak" value="<?php echo date('d-m-Y') ?>" required>
				</div>
			</div>

			<div class="row">
				<div class="col-3">Kode Cabang</div>
				<div class="col-4">
					<input type="text" class="form-control" name="kd_cabang" id="kd_cabang" value="" required>
				</div>
				<div class="col-4" style="font-size:20px">
					010.<span class="text-danger" style="font-size:25px;"><strong>900</strong></span>-10.00000001
				</div>
			</div>
			<div class="row">
				<div class="col-4 text-center">
					<input type="checkbox" name="product" id="product" checked> Produk
				</div>
				<div class="col-4 text-center">
					<input type="checkbox" name="sparepart" id="sparepart" checked> Sparepart
				</div>
				<div class="col-4 text-center">
					<input type="checkbox" name="service" id="service" checked> Service
				</div>
			</div>

			<div class="row bg-primary">
				<div class="col-3 text-center">
					Urutkan Berdasarkan
				</div>
				<div class="col-3 text-center">
					<input type="radio" name="urut" id="urut" value="1" checked> No Faktur Pajak
				</div>
				<div class="col-3 text-center">
					<input type="radio" name="urut" id="urut" value="2"> Tanggal Faktur Pajak
				</div>
				<div class="col-3 text-center">
					<input type="radio" name="urut" id="urut" value="3"> Tanggal Edit Pajak
				</div>
			</div>

			<div class="row" style="margin-top:10px">
				<div class="col-3">Partner Type</div>
				<div class="col-9 col-md-8">
					<select name="partner_type" id="partner_type" class="form-control" onchange="getDealer()">
						<!-- <option value='ALL'>ALL</option> -->
						<option value='TRADISIONAL'>TRADISIONAL</option>
						<option value='MODERN_OUTLET'>MODERN OUTLET</option>
				  		<option value='MO_CABANG'>MO CABANG</option>
				  		<option value='PROYEK'>PROYEK</option>
				  		<option value='COUNTER'>COUNTER</option>
					</select>
				</div>
			</div>

			<div class="row">
				<div class="col-3">Wilayah</div>
				<div class="col-9 col-md-8">
					<select name="wilayah" id="wilayah" class="form-control" onchange="getDealer()">
						<!-- <option value='ALL'>ALL</option> -->
						<?php
							foreach ($Wilayah as $key => $w){
								$nw=str_replace(" ", "_", rtrim($w['Nama_Wilayah']));
						?>
								<option value="<?php echo $nw; ?>"><?php echo $w['Nama_Wilayah']; ?></option>
						<?php
							}
						?>
					</select>
				</div>
			</div>

			<div class="row">
				<div class="col-3">Dealer</div>
				<div class="col-9 col-md-8">
					<select name="dealer" id="dealer" class="form-control">
						<option value='ALL'>ALL</option>
					</select>
				</div>
			</div>

			<div class="row">
				<div class="col-3">Gudang</div>
				<div class="col-9 col-md-8">
					<select name="gudang" id="gudang" class="form-control">
						<option value='ALL'>ALL</option>
					</select>
				</div>
			</div>

			<div class="row">
				<div class="col-12"><input type="checkbox" name="keterangan" id="keterangan" value="on"> Tampilkan Keterangan dan Tipe Faktur</div>
			</div>

			<div class="row">
				<div class="col-3">Tipe Faktur</div>
				<div class="col-9 col-md-8">
					<select name="tipefaktur" id="tipefaktur" class="form-control">
						<option value='ALL'>ALL</option>
						<?php
							foreach ($TipeFaktur as $key => $w){
						?>
								<option value="<?php echo $w['Tipe_Faktur']; ?>"><?php echo $w['Tipe_Faktur']; ?></option>
						<?php
							}
						?>
					</select>
				</div>
			</div>
			
			<div class="row" align="center" style="padding-top:50px;">
				<input type="submit" class="btn btn-default" name="pdf" onclick="laporan_pajaka()" value="PDF LAPORAN PKP"/>
				<input type="submit" class="btn btn-default" name="excel" onclick="laporan_pajaka2()" value="EXCEL LAPORAN PKP"/>
				<input type="submit" class="btn btn-default" name="pdf" onclick="laporan_pajakb()" value="PDF LAPORAN PAJAK A1"/>
				<input type="submit" class="btn btn-default" name="excel" onclick="laporan_pajakb2()" value="EXCEL LAPORAN PAJAK A1"/>
				<input type="submit" class="btn btn-default" name="pdf" onclick="laporan_pajakc()" value="PDF FAKTUR BELUM DI EDIT PAJAK"/>
			</div>
	</div>
</div> 


<script type="text/javascript">
	$(document).ready(function(){

		$('#periode_dari,#periode_sampai,#tgl_cetak').datepicker({
			format: "dd-mm-yyyy",
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
	getDealer();
	function getDealer(){
		getGudang();

		var partner_type = document.getElementById('partner_type').value;
		var wilayah 	 = document.getElementById('wilayah').value;

		document.getElementById('dealer').innerHTML='<option value="">Loading</option>';

		if(wilayah=='ALL'){
			document.getElementById('dealer').innerHTML='<option value="ALL">ALL</option>';
		}else{
			var get = 'partner_type='+partner_type;
				get += '&wilayah='+wilayah;

				console.log(get);
				$.ajax({
					type 	: 'GET',	
					url 	: '<?php echo site_url('LaporanPajak/dealer'); ?>',
					data   	: get,
					success : function(dataa) {
						var hasil = JSON.parse(dataa); 	

						if(hasil.result=='sukses'){
							var tamp='<option value="ALL">ALL</option>';
								for(i = 0; i < hasil.data.length; i++){		
									var modifiedString = hasil.data[i].nama.replace(" ", "_");
									tamp +='<option value="'+modifiedString+'">'+hasil.data[i].nama+'</option>';
								}	
							document.getElementById('dealer').innerHTML=tamp;
						}else{
							document.getElementById('dealer').innerHTML='<option value="">Data Dealer Tidak Tersedia</option>';
						}
					}
				});
		}
	}

	getGudang();
	function getGudang(){
		var wilayah 	 = document.getElementById('wilayah').value;

		document.getElementById('gudang').innerHTML='<option value="">Loading</option>';

		if(wilayah=='ALL'){
			document.getElementById('gudang').innerHTML='<option value="ALL">ALL</option>';
		}else{
			var get = '&wilayah='+wilayah;

				console.log(get);
				$.ajax({
					type 	: 'GET',	
					url 	: '<?php echo site_url('LaporanPajak/getGudang'); ?>',
					data   	: get,
					success : function(dataa) {

						var hasil = JSON.parse(dataa); 	

						if(hasil.result=='sukses'){
							var tamp='<option value="ALL">ALL</option>';
								for(i = 0; i < hasil.data.length; i++){		
									var modifiedString = hasil.data[i].gdg.replace(" ", "_");
									tamp +='<option value="'+modifiedString+'">'+hasil.data[i].gdg+'</option>';
								}	
							document.getElementById('gudang').innerHTML=tamp;
						}else{
							document.getElementById('gudang').innerHTML='<option value="">Data Gudang Tidak Tersedia</option>';
						}
					}
				});
		}
	}

	//pdf
	function laporan_pajaka(){

		if (document.getElementById('product').checked == true){
			var product = 'on';
		} else {
			var product = 'off';
		}

		if (document.getElementById('sparepart').checked == true){
			var sparepart = 'on';
		} else {
			var sparepart = 'off';
		}

		if (document.getElementById('service').checked == true){
			var service = 'on';
		} else {
			var service = 'off';
		}

		var urut = document.getElementsByName('urut');
        var genValue = false;

        for(var i=0; i<urut.length;i++){
            if(urut[i].checked == true){
                genValue = urut[i].value;    
            }
        }

        if (document.getElementById('keterangan').checked == true){
			var keterangan = 'on';
		} else {
			var keterangan = 'off';
		}

		var data  = '&periode_dari='+document.getElementById('periode_dari').value;
			data += '&periode_sampai='+document.getElementById('periode_sampai').value;
			data += '&tgl_cetak='+document.getElementById('tgl_cetak').value;
			data += '&kd_cabang='+document.getElementById('kd_cabang').value;
			data += '&product='+product;
			data += '&sparepart='+sparepart;
			data += '&service='+service;
			data += '&urut='+genValue;
			data += '&partner_type='+document.getElementById('partner_type').value;
			data += '&wilayah='+document.getElementById('wilayah').value;
			data += '&dealer='+document.getElementById('dealer').value;
			data += '&gudang='+document.getElementById('gudang').value;
			data += '&keterangan='+keterangan;
			data += '&tipefaktur='+document.getElementById('tipefaktur').value;

			var parts = data.split(" ");
			var modifiedString = parts.join("_");
			var get = modifiedString.replace(" ", "_");

			window.open('<?php echo site_url('LaporanPajak/pdf_laporan_pajak?'); ?>'+get, '_blank');
			
	}

	//excel
	function laporan_pajaka2(){

		if (document.getElementById('product').checked == true){
			var product = 'on';
		} else {
			var product = 'off';
		}

		if (document.getElementById('sparepart').checked == true){
			var sparepart = 'on';
		} else {
			var sparepart = 'off';
		}

		if (document.getElementById('service').checked == true){
			var service = 'on';
		} else {
			var service = 'off';
		}

		var urut = document.getElementsByName('urut');
        var genValue = false;

        for(var i=0; i<urut.length;i++){
            if(urut[i].checked == true){
                genValue = urut[i].value;    
            }
        }

        if (document.getElementById('keterangan').checked == true){
			var keterangan = 'on';
		} else {
			var keterangan = 'off';
		}

		var data  = '&periode_dari='+document.getElementById('periode_dari').value;
			data += '&periode_sampai='+document.getElementById('periode_sampai').value;
			data += '&tgl_cetak='+document.getElementById('tgl_cetak').value;
			data += '&kd_cabang='+document.getElementById('kd_cabang').value;
			data += '&product='+product;
			data += '&sparepart='+sparepart;
			data += '&service='+service;
			data += '&urut='+genValue;
			data += '&partner_type='+document.getElementById('partner_type').value;
			data += '&wilayah='+document.getElementById('wilayah').value;
			data += '&dealer='+document.getElementById('dealer').value;
			data += '&gudang='+document.getElementById('gudang').value;
			data += '&keterangan='+keterangan;
			data += '&tipefaktur='+document.getElementById('tipefaktur').value;

			var parts = data.split(" ");
			var modifiedString = parts.join("_");
			var get = modifiedString.replace(" ", "_");

			window.open('<?php echo site_url('LaporanPajak/excel_laporan_pajak?'); ?>'+get, '_blank');
			
	}

	
	//pdf
	function laporan_pajakb(){

		if (document.getElementById('product').checked == true){
			var product = 'on';
		} else {
			var product = 'off';
		}

		if (document.getElementById('sparepart').checked == true){
			var sparepart = 'on';
		} else {
			var sparepart = 'off';
		}

		if (document.getElementById('service').checked == true){
			var service = 'on';
		} else {
			var service = 'off';
		}

		var urut = document.getElementsByName('urut');
        var genValue = false;

        for(var i=0; i<urut.length;i++){
            if(urut[i].checked == true){
                genValue = urut[i].value;    
            }
        }

        if (document.getElementById('keterangan').checked == true){
			var keterangan = 'on';
		} else {
			var keterangan = 'off';
		}

		var data  = '&periode_dari='+document.getElementById('periode_dari').value;
			data += '&periode_sampai='+document.getElementById('periode_sampai').value;
			data += '&tgl_cetak='+document.getElementById('tgl_cetak').value;
			data += '&kd_cabang='+document.getElementById('kd_cabang').value;
			data += '&product='+product;
			data += '&sparepart='+sparepart;
			data += '&service='+service;
			data += '&urut='+genValue;
			data += '&partner_type='+document.getElementById('partner_type').value;
			data += '&wilayah='+document.getElementById('wilayah').value;
			data += '&dealer='+document.getElementById('dealer').value;
			data += '&gudang='+document.getElementById('gudang').value;
			data += '&keterangan='+keterangan;
			data += '&tipefaktur='+document.getElementById('tipefaktur').value;

			var parts = data.split(" ");
			var modifiedString = parts.join("_");
			var get = modifiedString.replace(" ", "_");

			window.open('<?php echo site_url('LaporanPajak/pdf_laporan_pajak_a1?'); ?>'+data, '_blank');
			
	}

	//excel
	function laporan_pajakb2(){

		if (document.getElementById('product').checked == true){
			var product = 'on';
		} else {
			var product = 'off';
		}

		if (document.getElementById('sparepart').checked == true){
			var sparepart = 'on';
		} else {
			var sparepart = 'off';
		}

		if (document.getElementById('service').checked == true){
			var service = 'on';
		} else {
			var service = 'off';
		}

		var urut = document.getElementsByName('urut');
		var genValue = false;

		for(var i=0; i<urut.length;i++){
			if(urut[i].checked == true){
				genValue = urut[i].value;    
			}
		}

		if (document.getElementById('keterangan').checked == true){
			var keterangan = 'on';
		} else {
			var keterangan = 'off';
		}

		var data  = '&periode_dari='+document.getElementById('periode_dari').value;
			data += '&periode_sampai='+document.getElementById('periode_sampai').value;
			data += '&tgl_cetak='+document.getElementById('tgl_cetak').value;
			data += '&kd_cabang='+document.getElementById('kd_cabang').value;
			data += '&product='+product;
			data += '&sparepart='+sparepart;
			data += '&service='+service;
			data += '&urut='+genValue;
			data += '&partner_type='+document.getElementById('partner_type').value;
			data += '&wilayah='+document.getElementById('wilayah').value;
			data += '&dealer='+document.getElementById('dealer').value;
			data += '&gudang='+document.getElementById('gudang').value;
			data += '&keterangan='+keterangan;
			data += '&tipefaktur='+document.getElementById('tipefaktur').value;

			var parts = data.split(" ");
			var modifiedString = parts.join("_");
			var get = modifiedString.replace(" ", "_");

			window.open('<?php echo site_url('LaporanPajak/excel_laporan_pajak_a1?'); ?>'+get, '_blank');
			
	}

	
	//pdf
	function laporan_pajakc(){

		if (document.getElementById('product').checked == true){
			var product = 'on';
		} else {
			var product = 'off';
		}

		if (document.getElementById('sparepart').checked == true){
			var sparepart = 'on';
		} else {
			var sparepart = 'off';
		}

		if (document.getElementById('service').checked == true){
			var service = 'on';
		} else {
			var service = 'off';
		}

		var urut = document.getElementsByName('urut');
        var genValue = false;

        for(var i=0; i<urut.length;i++){
            if(urut[i].checked == true){
                genValue = urut[i].value;    
            }
        }

        if (document.getElementById('keterangan').checked == true){
			var keterangan = 'on';
		} else {
			var keterangan = 'off';
		}

		var data  = '&periode_dari='+document.getElementById('periode_dari').value;
			data += '&periode_sampai='+document.getElementById('periode_sampai').value;
			data += '&tgl_cetak='+document.getElementById('tgl_cetak').value;
			data += '&kd_cabang='+document.getElementById('kd_cabang').value;
			data += '&product='+product;
			data += '&sparepart='+sparepart;
			data += '&service='+service;
			data += '&urut='+genValue;
			data += '&partner_type='+document.getElementById('partner_type').value;
			data += '&wilayah='+document.getElementById('wilayah').value;
			data += '&dealer='+document.getElementById('dealer').value;
			data += '&gudang='+document.getElementById('gudang').value;
			data += '&keterangan='+keterangan;
			data += '&tipefaktur='+document.getElementById('tipefaktur').value;

			var parts = data.split(" ");
			var modifiedString = parts.join("_");
			var get = modifiedString.replace(" ", "_");

			window.open('<?php echo site_url('LaporanPajak/pdf_laporan_pajak_edit?'); ?>'+get, '_blank');
			
	}


</script>