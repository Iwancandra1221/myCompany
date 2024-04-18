<style type="text/css">
	.row {
    line-height:30px; 
    vertical-align:middle;
    clear:both;
	}
	.row-label, .row-input {
    float:left;
	}
	/* .row-label {
    padding-left: 15px;
    width:180px;
	} */
	.row-input {
    width:420px;
	}
</style>



<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
	<?php 
        echo form_open("ReportStockGudang/Proses", array("target"=>"_blank"));
	?>
	<div class="form-container">
		
        <div class="row">
			<div class="col-3">Pilih Database Gudang</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="dbgudang" id="dbgudang" onchange="fdbgudang()">
					<?php 
						$jum= count($dbgudang->data);
						for($i=0; $i<$jum; $i++){						
							echo "<option data-id1 = '".$dbgudang->data[$i]->Kd_Gudang."' 
                                          data-id2 = '".$dbgudang->data[$i]->Server."' 
                                          data-id3 = '".$dbgudang->data[$i]->DB."' 
                                          value='".$dbgudang->data[$i]->Kd_Gudang."'
                                >".$dbgudang->data[$i]->Kd_Gudang." --- "
                                .$dbgudang->data[$i]->Server." --- "
                                .$dbgudang->data[$i]->DB."</option>";
						}			  
					?>
				</select>
			</div>
		</div>

        <div class="row">
			<div class="col-3">Kategori Gudang</div>
			<div class="col-8 col-m-8 ">
			<input type="text" class="form-control" name="kategori_gudang1" id="kategori_gudang1" disabled >		
			<input type="hidden" class="form-control" name="kategori_gudang" id="kategori_gudang" >		
			</div>
		</div>

		<div class="row">
			<div class="col-3 col-m-3 row-label">Periode</div>
			<div class="col-3 col-m-3 ">
				<input type="text" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1" value="<?php echo date('m-d-Y'); ?>" autocomplete="off" required>
			</div>
			<div class="col-1 col-m-1">SD</div>
			<div class="col-3 col-m-3 ">
				<input type="text" class="form-control" id="dp2" placeholder="mm/dd/yyyy" name="dp2" value="<?php echo date('m-d-Y'); ?>" autocomplete="off" required>
			</div>
		</div>
				
		<div class="row">
			<div class="col-3">Divisi / Merk</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="divisi" id="divisi" onchange="fDivisi()">
					<option value='ALL' selected>ALL</option>					
				</select>
			</div>
		</div>

		<div class="row">
			<div class="col-3">Jenis Barang</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="jenis_barang" id="jenis_barang" >
					<option value='ALL' selected>ALL</option>					
				</select>
			</div>
		</div>

		<div class="row">
			<div class="col-3">Gudang</div>
			<div class="col-8" style="margin-top:-10px " >
				<div class="col-8">
					<input type="radio" name="radgudang" id="rg1" value="gudang"  onclick="fGudang('gudang')" checked> <label for="p1">Gudang</label> 
		
					<select  class="form-control" name="gudang" id="gudang" >
						<!-- <option value='ALL' selected>ALL</option>					 -->
					</select>			
				</div>
				<div class="col-4 ">
					<input type="radio" name="radgudang" id="rg2" value="grupgudang" onclick="fGudang('grupgudang')"> <label for="p2">Grup Gudang</label>
					
					<select  class="form-control" name="grupgudang" id="grupgudang"  disabled>
						<!-- <option value='ALL' selected>ALL</option>					 -->
					</select>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-3">Pilihan Laporan</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="pilihanlaporan" id="pilihanlaporan" onclick="fPilihanLaporan()" >
					<option value='A' selected>A. Laporan Faktur yang Belum Dipotong PDA (html only)</option>
					<option value='B' >B. Retur Barang</option>
					<option value='C' >C. Detail Kartu Barang</option>
					<option value='D' >D. Rekap Kartu Barang</option>					
				</select>
			</div>
		</div>

		<div class="row">
			<div class="col-3">Proses Berdasarkan</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="tanggalproses" id="tanggalproses" >
					<option value='tanggal_faktur' >Tanggal Faktur</option>	
					<option value='tanggal_keluar_barang' >Tanggal Keluar Barang</option>					
				</select>
			</div>
		</div>
		
		<div class="row">
		<div class="col-3"> </div>
			<div class="col-8">
				<input type="checkbox" id="inctransferstok" name="inctransferstok" value="Y" >
				<label for="inctransferstok">Include Transfer Stock</label>
			</div>	
		</div>	

        <div class="row" align="center" style="padding-top:50px;">
			<input type="submit" name="btnPreview" id="btnPreview" value="PREVIEW" />
			<input type="submit" name="btnExcel" id="btnExcel" value="EXCEL"/>
		</div>
	</div>

	<?php echo form_close(); ?>
</div> 


<script>
    $(document).ready(function() {

		$('#dp1').datepicker({
			format: "mm-dd-yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var StartDt = $('#dp1').datepicker('getDate');
			$('#dp2').datepicker("setStartDate", StartDt);
		});
		
		$('#dp2').datepicker({
			format: "mm-dd-yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var EndDt = $('#dp2').datepicker('getDate');
			$('#dp1').datepicker("setEndDate", EndDt);
		});
	} );


	function fdbgudang(){
		var kd_gudang = $('#dbgudang').find(':selected').data("id1");
		var server = $('#dbgudang').find(':selected').data("id2");
		var db = $('#dbgudang').find(':selected').data("id3");		

		$.ajax({
			type: "POST",
			url: '<?php echo $mainurl."/MasterGudang/GetKategoriGudang?api=APITES"; ?>
											&server='+server+'&db='+db,
			success: function (kategori_gudang) {		
				// hasil = data;

				document.getElementById("kategori_gudang1").value=kategori_gudang;	
				document.getElementById("kategori_gudang").value=kategori_gudang;	
				
				// Isi ListDivisiMerk
				var linkDivisi = '<?php echo $mainurl."/MasterBarang/GetListDivisiMerk?api=APITES"; ?>&server='+server+'&db='+db+'&kategori_gudang='+kategori_gudang;
				// alert(link);

				// $("#divisi").html('<option value="ALL">ALL</option>');	
				var divisi='<option value="ALL">ALL</option>';
				$.ajax({ 
					type: "POST",
					url: linkDivisi,
					success: function (data_divisi) {		
						// alert (data_divisi);	

						data_divisi = JSON.parse(data_divisi); 	 
						for(i = 0; i < data_divisi.data.length; i++){							
							divisi +='<option value="'+data_divisi.data[i].divisi+'">'+data_divisi.data[i].divisi+'</option>';   
							$("#divisi").html(divisi);
						}					
					}
				});   			
					
				fGudang2(kategori_gudang);
				fGudang("gudang");
				fDivisi();
			}
		});   

		return false;
	};  

	function fDivisi(){
		var kategori_gudang = document.getElementById("kategori_gudang").value;
		var divisi = document.getElementById("divisi").value;
		var server = $('#dbgudang').find(':selected').data("id2");
		var db = $('#dbgudang').find(':selected').data("id3");		

		// alert(kategori_gudang);

		// $("#jenis_barang").html('<option value="ALL">ALL</option>');	
		var jenis_barang='<option value="ALL">ALL</option>';

		// alert('<?php //echo $mainurl."/MasterBarang/GetListJenisBarang?api=APITES"; ?>&server='+server+'&db='+db+'&kategori_gudang='+kategori_gudang+'&divisi='+btoa(divisi));

		$.ajax({ 
			type: "POST",			
			url: '<?php echo $mainurl."/MasterBarang/GetListJenisBarang?api=APITES"; ?>&server='+server+'&db='+db+'&kategori_gudang='+kategori_gudang+'&divisi='+btoa(divisi),
			success: function (dataa) {			
				// alert (dataa);	
				dataa = JSON.parse(dataa); 	
				// alert (dataa.data[2].jenis_barang);	

				for(i = 0; i < dataa.data.length; i++){							
					jenis_barang +='<option value="'+dataa.data[i].jenis_barang+'">'+dataa.data[i].jenis_barang+'</option>';   
					$("#jenis_barang").html(jenis_barang);
				}					
			}
		});     
		return false;
	};  

	function fGudang(gdg) {
        var x = document.getElementById("gudang");
		var y = document.getElementById("grupgudang");	

		if (gdg == "gudang"){
			x.disabled = false;
			y.disabled = true;	
		}  
		else {
			x.disabled = true;
			y.disabled = false;
		}
		
		return false;      
    }   

	function fGudang2(kategori_gudang) {
        var server = $('#dbgudang').find(':selected').data("id2");
		var db = $('#dbgudang').find(':selected').data("id3");		
		var a = document.getElementById("rg1");
		var b = document.getElementById("rg2");
		var x = document.getElementById("gudang");
		var y = document.getElementById("grupgudang");

		if (kategori_gudang == "PRODUK") {
			a.disabled = false;
			b.disabled = false;
			x.disabled = false;
			y.disabled = false;
		}
		else if (kategori_gudang == "SPAREPART") {
			a.disabled = false;
			b.disabled = true;			
			x.disabled = false;
			y.disabled = true;

			a.checked = true;
		}
		else {
			a.disabled = true;
			b.disabled = true;
			x.disabled = true;
			y.disabled = true;
		}

		// x.selected = true;

		//// Isi ListGudang
		var linkGudang = '<?php echo $mainurl."/MasterGudang/GetListGudang2?api=APITES"; ?>&server='+server+'&db='+db;
		// alert(linkGudang);

		var gudang='<option value="ALL">ALL</option>';
		
		$.ajax({ 
			type: "POST",
			url: linkGudang,
			success: function (data_gudang) {		
				// alert (data_gudang);	

				data_gudang = JSON.parse(data_gudang); 	 
				for(i = 0; i < data_gudang.data.length; i++){							
					gudang +='<option value="'+data_gudang.data[i].Kd_Gudang+'">'+data_gudang.data[i].Nm_Gudang+' --- '+data_gudang.data[i].Kd_Gudang+'</option>';   
					$("#gudang").html(gudang);
				}					
			}
		});  				  

		//// Isi ListGrupGudang
		var linkGrupGudang = '<?php echo $mainurl."/MasterGudang/GetListGrupGudang?api=APITES"; ?>&server='+server+'&db='+db;
		// alert(linkGrupGudang);

		// var grupgudang='<option value="ALL">ALL</option>';
		var grupgudang='';

		$.ajax({ 
			type: "POST",
			url: linkGrupGudang,
			success: function (data_grupgudang) {		
				// alert (data_grupgudang);	

				data_grupgudang = JSON.parse(data_grupgudang); 	 
				for(i = 0; i < data_grupgudang.data.length; i++){							
					grupgudang +='<option value="'+data_grupgudang.data[i].GrupGdg+'">'+data_grupgudang.data[i].GrupGdg+'</option>';   
					$("#grupgudang").html(grupgudang);
				}					
			}		
		});
		
		return false;      
    }  

	function fPilihanLaporan() {
        var pilihanlaporan = document.getElementById("pilihanlaporan").value;
		var tanggalproses = document.getElementById("tanggalproses");	
		var btnPreview = document.getElementById("btnPreview");	
		var btnExcel = document.getElementById("btnExcel");	

		// alert(pilihanlaporan);

		if (pilihanlaporan == "A"){
			tanggalproses.disabled = true;
			btnPreview.disabled = false;
			btnExcel.disabled = true;		
			
			$("#tanggalproses").html('<option value="-">-</option>');	
		}  
		else if (pilihanlaporan == "B" || pilihanlaporan == "C" || pilihanlaporan == "D"){
			tanggalproses.disabled = false;
			btnPreview.disabled = false;
			btnExcel.disabled = false;	

			$("#tanggalproses").html("<option value='tanggal_faktur' >Tanggal Faktur</option> <option value='tanggal_keluar_barang' >Tanggal Keluar Barang</option>");	
		}
		
		return false;      
    }   

	fdbgudang();
	fPilihanLaporan();


</script>