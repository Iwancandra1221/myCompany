<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="<?php echo site_url('css/selectize.default.min.css') ?>"/>

<style type="text/css">
table, tr ,td, th{
	padding: 10px;
}
</style>
<div class="container">
	<div class="page-title">
		LAPORAN STATUS FAKTUR SPAREPART DIVISI SERVIS
	</div>
	<div class="form-container" >  

  		<div class="row">
        	<div class="col-12" align="center">
         		<table border="0" width="80%">
         			<tr>
         				<td colspan="4">
         					<b>
         						TANGGAL PERIODE
         					</b>
         				</td>
         			</tr>
         			<tr>
         				<td width="150px">
         					Periode
         				</td>
         				<td colspan="3">
         					<input type="text" id="DTPBlnThn" class="form-control" value="<?php echo date('M-Y'); ?>" onchange="change_periode();">
         				</td>
         			</tr>
         			<tr>
         				<td width="150px">
         					<b>
         						Periode Kontrol
         					</b>
         				</td>
         				<td>
         					<input type="text" id="DTPAwal" class="form-control" value="<?php echo date('01-M-Y', strtotime('-3 month')); ?>" readonly>
         				</td>
         				<td align="center" width="150px">
         					S/D
         				</td>
         				<td>
         					<input type="text" id="DTPAkhir" class="form-control" value="<?php echo date('t-M-Y'); ?>" readonly>
         				</td>
         			</tr>
         			<tr>
         				<td colspan="4" align="center">
         					<b>
         						<span>Tanggal manapun yang dipilih, tanggal awal untuk periode kontrol tetap dimulai dari tanggal 1</span>
         						<br>
         						<span style="color: #FF0000;">Periode kontrol otomatis menjadi TIGA BULAN sebelum "Pilihan Bulan"</span>
         					</b>
         				</td>
         			</tr>
         			<tr>
         				<td colspan="4">
         					<b>
         						FAKTUR SPAREPART
         					</b>
         				</td>
         			</tr>
         			<tr>
         				<td>
         					No Faktur
         				</td>
         				<td>
         					<select id="CboNoFaktur" class="form-control">
         						<?php
         							foreach ($no_faktur as $key => $nf) {
         						?>
         								<option value="<?php echo $nf['no_faktur']; ?>"><?php echo $nf['no_faktur']; ?></option>
         						<?php
         							}
         						?>
         					</select>
         				</td>
         				<td align="right">
         					Tipe Cetak
         				</td>
         				<td>
         					<select id="CboTpCtk" class="form-control" onchange="cekdisabled()">
         						<option value="Semua">Semua</option>
         						<option value="FK">FK</option>
         						<option value="FR">FR</option>
         						<option value="FS">FS</option>
         						<option value="MI">MI</option>
         						<option value="SJ">SJ</option>
         					</select>
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Gudang
         				</td>
         				<td colspan="3">
         					<select id="CboKdGdg" class="form-control" onchange="cekdisabled()">
         						<?php
         							foreach ($gudang as $key => $g) {
         								if($g['Kd_Gudang']=='Pilih'){
         						?>
         									<option value="<?php echo $g['Kd_Gudang']; ?>"><?php echo $g['Kd_Gudang']; ?></option>
         						<?php
         								}else{
         						?>
         									<option value="<?php echo $g['Kd_Gudang'].'-'.$g['Nm_Gudang']; ?>"><?php echo $g['Kd_Gudang'].' - '.$g['Nm_Gudang']; ?></option>
         						<?php
         								}
         							}
         						?>
         					</select>
         				</td>
         			</tr>
         			<tr>
         				<td colspan="4">
         					<b>
         						KATEGORI LAINNYA
         					</b>
         				</td>
         			</tr>
         		</table>
         		<table border="0" width="80%">
         			<tr>
         				<td width="10px">
         					<input type="checkbox" name="ChkSelesai" id="ChkSelesai" value="1" onchange="cekdisabled()" disabled>
         				</td>
         				<td colspan="2">
         					Selesai
         				</td>
         				<td width="10px">
         					<input type="checkbox" name="ChkSmSelesai" id="ChkSmSelesai" value="1" onchange="cekdisabled()" checked>
         				</td>
         				<td>
         					Semua
         				</td>
         				<td rowspan="5"></td>
         			</tr>
         			<tr>
         				<td></td>
         				<td width="10px">
         					<input type="radio" name="opt" id="OptSemua" value="1" disabled>
         				</td>
         				<td colspan="3">
         					Semua Faktur (FK,FR,MI)
         				</td>
         			</tr>
         			<tr>
         				<td></td>
         				<td width="10px">
         					<input type="radio" name="opt" id="OptBayar" value="1" disabled>
         				</td>
         				<td colspan="3">
         					Faktur Bayar (FK,FS,SJ)
         				</td>
         			</tr>
         			<tr>
         				<td></td>
         				<td width="10px">
         					<input type="radio" name="opt" id="OptGratis" value="1" disabled>
         				</td>
         				<td colspan="3">
         					Faktur Gratis (FR,MI)
         				</td>
         			</tr>
         			<tr>
         				<td width="10px">
         					<input type="checkbox" name="ChkBatal" id="ChkBatal" value="1" disabled>
         				</td>
         				<td colspan="2">
         					Dibatalkan
         				</td>
         				<td width="10px">
         					<input type="checkbox" name="ChkSmBatal" id="ChkSmBatal" value="1" onclick="cekdisabledbatal()" checked>
         				</td>
         				<td>
         					Semua
         				</td>
         			</tr>
         			<tr>
         				<td colspan="6">
         					<b>
         						KATEGORI PEMBAYARAN
         					</b>
         				</td>
         			</tr>
         			<tr>
         				<td colspan="6">
         					<input type="checkbox" name="ChkFK"  id="ChkFK" style="margin-right: 5px;" checked> FK 
         					<input type="checkbox" name="ChkFR" id="ChkFR" style="margin-left:10px; margin-right: 5px;" checked> FR 
         					<input type="checkbox" name="ChkMI" id="ChkMI" style="margin-left:10px; margin-right: 5px;" checked> MI 
         					<input type="checkbox" name="ChkFS" id="ChkFS" style="margin-left:10px; margin-right: 5px;" checked> FS 
         					<input type="checkbox" name="ChkSJ" id="ChkSJ" style="margin-left:10px; margin-right: 5px;" checked> SJ 
         				</td>
         			</tr>
         			<tr>
         				<td colspan="6" align="center">
         					<button class="btn btn-default" onclick="proses_data();">
         						PROSES
         					</button>
         					<button class="btn btn-default" id="pdf" onclick="print_data('pdf');" disabled>
         						PDF
         					</button>
         					<button class="btn btn-default" id="excel" onclick="print_data('excel');" disabled>
         						Excel
         					</button>
         				</td>
         			</tr>
         		</table>
        	</div> 
      </div> 
  </div>

</div>


<script type="text/javascript">
	function change_periode(){

		clear_localtorage()

		var periode = document.getElementById("DTPBlnThn").value;

		var proses_periode = moment(periode,'MMM-YYYY');

		var terakhir = proses_periode.endOf('month').format('DD-MMM-YYYY');

		var awal = proses_periode.add(-3, 'month').format('01-MMM-YYYY'); 

		document.getElementById('DTPAwal').value = awal;
		document.getElementById('DTPAkhir').value = terakhir;
	}

	function cekdisabled(){

		clear_localtorage()

		var CboTpCtk 		= document.getElementById('CboTpCtk').value;
		var ChkSmSelesai 	= document.getElementById("ChkSmSelesai");
		var ChkSelesai 		= document.getElementById("ChkSelesai");


		if(ChkSmSelesai.checked == true){

			ChkSelesai.disabled=true;
			ChkSelesai.checked = false;

			document.getElementById('OptSemua').disabled = true;
			document.getElementById('OptBayar').disabled = true;
			document.getElementById('OptGratis').disabled = true;

			document.getElementById("OptSemua").checked = false;
			document.getElementById("OptBayar").checked = false;
			document.getElementById("OptGratis").checked = false;


		}else if(ChkSmSelesai.checked == false){
			
			if(CboTpCtk=='Semua'){

				document.getElementById('OptSemua').disabled = false;
				document.getElementById('OptBayar').disabled = true;
				document.getElementById('OptGratis').disabled = true;

				document.getElementById("OptSemua").checked = true;
				document.getElementById("OptBayar").checked = false;
				document.getElementById("OptGratis").checked = false;


			}else if(CboTpCtk=='FK'){

				document.getElementById('OptSemua').disabled = true;
				document.getElementById('OptBayar').disabled = false;
				document.getElementById('OptGratis').disabled = true;

				document.getElementById("OptSemua").checked = false;
				document.getElementById("OptBayar").checked = true;
				document.getElementById("OptGratis").checked = false;

			}else{
				document.getElementById('OptSemua').disabled = true;
				document.getElementById('OptBayar').disabled = true;
				document.getElementById('OptGratis').disabled = false;

				document.getElementById("OptSemua").checked = false;
				document.getElementById("OptBayar").checked = false;
				document.getElementById("OptGratis").checked = true;
			}

			ChkSelesai.disabled=false;

		}

	}

	function cekdisabledbatal(){

		clear_localtorage()

		var ChkBatal 		= document.getElementById("ChkBatal");
		var ChkSmBatal 		= document.getElementById("ChkSmBatal");
		
		if(ChkSmBatal.checked == true){
			ChkBatal.disabled = true;
		}else{
			ChkBatal.disabled = false;
		}
		ChkBatal.checked = false;
	}

	function clear_localtorage(){
		document.getElementById('pdf').disabled=true;
		document.getElementById('excel').disabled=true;
		localStorage.setItem('report','Report');
		localStorage.setItem("usai", '');
		localStorage.setItem("acak", '');
	}

	$('#DTPBlnThn').datepicker({
  		format: 'M-yyyy',
		viewMode: "months",
		minViewMode: "months",
		autoClose: true
	});

	function proses_data(){


		var data= '&DTPAwal='+document.getElementById('DTPAwal').value;
			data += '&DTPAkhir='+document.getElementById('DTPAkhir').value;
			data += '&CboNoFaktur='+document.getElementById('CboNoFaktur').value;
			data += '&CboTpCtk='+document.getElementById('CboTpCtk').value;
			data += '&CboKdGdg='+document.getElementById('CboKdGdg').value;

		if(document.getElementById('CboKdGdg').value!=='Pilih'){

			Swal.fire({
				title: 'Proses',
				text: 'Sedang dalam Proses',
				showConfirmButton: false
			})


			var DTPAwal = document.getElementById('DTPAwal').value;
			var DTPAkhir = document.getElementById('DTPAkhir').value;
			var CboNoFaktur = document.getElementById('CboNoFaktur').value;
			var CboTpCtk = document.getElementById('CboTpCtk').value;
			var CboKdGdg = document.getElementById('CboKdGdg').value;
		
			var ChkSelesai = document.getElementById('ChkSelesai').checked;
			var ChkSmSelesai = document.getElementById('ChkSmSelesai').checked;

			var OptSemua = document.getElementById('OptSemua').checked;
			var OptBayar = document.getElementById('OptBayar').checked;
			var OptGratis = document.getElementById('OptGratis').checked;

			var ChkBatal = document.getElementById('ChkBatal').checked;
			var ChkSmBatal = document.getElementById('ChkSmBatal').checked;


         	var ChkFK = document.getElementById('ChkFK').checked;
         	var ChkFR = document.getElementById('ChkFR').checked;
         	var ChkMI = document.getElementById('ChkMI').checked;
         	var ChkFS = document.getElementById('ChkFS').checked;
         	var ChkSJ = document.getElementById('ChkSJ').checked;


			if(ChkSelesai == true){
			   	data += '&ChkSelesai=1';
			}else{
				data += '&ChkSelesai=0';
			}

			if(ChkSmSelesai == true){
			   	data += '&ChkSmSelesai=1';
			}else{
				data += '&ChkSmSelesai=0';
			}

			if(OptSemua == true){
			   	data += '&OptSemua=1';
			}else{
				data += '&OptSemua=0';
			}

			if(OptBayar == true){
			   	data += '&OptBayar=1';
			}else{
				data += '&OptBayar=0';
			}

			if(OptGratis == true){
			   	data += '&OptGratis=1';
			}else{
				data += '&OptGratis=0';
			}

			if(ChkBatal == true){
			   	data += '&ChkBatal=1';
			}else{
				data += '&ChkBatal=0';
			}

			if(ChkSmBatal == true){
			   	data += '&ChkSmBatal=1';
			}else{
				data += '&ChkSmBatal=0';
			}


         	if(ChkFK == true){
         		data += '&ChkFK=1';
         	}else{
         		data += '&ChkFK=0';
         	}

         	if(ChkFR == true){
         		data += '&ChkFR=1';
         	}else{
         		data += '&ChkFR=0';
         	}

         	if(ChkMI == true){
         		data += '&ChkMI=1';
         	}else{
         		data += '&ChkMI=0';
         	}

         	if(ChkFS == true){
         		data += '&ChkFS=1';
         	}else{
         		data += '&ChkFS=0';
         	}

         	if(ChkSJ == true){
         		data += '&ChkSJ=1';
         	}else{
         		data += '&ChkSJ=0';
         	}


	    	console.log(data);
	        $.ajax({
				type: 'post',
				url: '<?php echo site_url('Reportstatusfaktursp/proses_data'); ?>',
				data: data,
				success: function (data) {

					var hasil = JSON.parse(data);
					localStorage.setItem("usai", hasil.usai);
					localStorage.setItem("acak", hasil.acak);

					if(hasil.success=='error'){
						localStorage.setItem('report','ReportGantung');
						Swal.fire({
							title: 'Proses',
							text: hasil.proses,
							showDenyButton: false,
							showCancelButton: true,
							confirmButtonText: 'Open',
						}).then((result) => {

							localStorage.setItem('Report','ReportGantung');

							window.open('<?php echo site_url('Reportstatusfaktursp'); ?>/pdf/ReportGantung/<?php echo $_SESSION['logged_in']["userid"]; ?>/'+DTPAwal+'/'+DTPAkhir+'/'+CboNoFaktur+'/'+CboTpCtk+'/'+CboKdGdg.trim()+'/'+ChkSelesai+'/'+ChkSmSelesai+'/'+OptSemua+'/'+OptBayar+'/'+OptGratis+'/'+ChkBatal+'/'+ChkSmBatal,'_blank');

						})

					}else{
						document.getElementById('pdf').disabled=false;
						document.getElementById('excel').disabled=false;
						localStorage.setItem('report','Report');
						Swal.fire({
							title: 'Proses',
							text: hasil.proses
						})

					}

				}
			});


    	}else{

			Swal.fire({
				title: 'Error',
				text: 'Gudang harus di pilih'
			})

		}

	}

	function print_data(e){
		var usai = localStorage.getItem("usai");
		var acak = localStorage.getItem("acak");

		var DTPAwal = document.getElementById('DTPAwal').value;
		var DTPAkhir = document.getElementById('DTPAkhir').value;
		var CboNoFaktur = document.getElementById('CboNoFaktur').value;
		var CboTpCtk = document.getElementById('CboTpCtk').value;
		var CboKdGdg = document.getElementById('CboKdGdg').value;


		if(usai!=='' && acak!==''){

			if(CboKdGdg!=='Pilih'){
				var ChkSelesai = document.getElementById('ChkSelesai').checked;
				var ChkSmSelesai = document.getElementById('ChkSmSelesai').checked;

				var OptSemua = document.getElementById('OptSemua').checked;
				var OptBayar = document.getElementById('OptBayar').checked;
				var OptGratis = document.getElementById('OptGratis').checked;

				var ChkBatal = document.getElementById('ChkBatal').checked;
				var ChkSmBatal = document.getElementById('ChkSmBatal').checked;

				if(ChkSelesai == true){
				   	ChkSelesai=1;
				}else{
					ChkSelesai=0;
				}

				if(ChkSmSelesai == true){
				   	ChkSmSelesai=1;
				}else{
					ChkSmSelesai=0;
				}

				if(OptSemua == true){
				   	OptSemua=1;
				}else{
					OptSemua=0;
				}

				if(OptBayar == true){
				   	OptBayar=1;
				}else{
					OptBayar=0;
				}

				if(OptGratis == true){
				   	OptGratis=1;
				}else{
					OptGratis=0;
				}

				if(ChkBatal == true){
				   	ChkBatal=1;
				}else{
					ChkBatal=0;
				}

				if(ChkSmBatal == true){
				   	ChkSmBatal=1;
				}else{
					ChkSmBatal=0;
				}


				window.open('<?php echo site_url('Reportstatusfaktursp'); ?>/'+e+'/'+localStorage.getItem('report')+'/'+acak+'/'+DTPAwal+'/'+DTPAkhir+'/'+CboNoFaktur+'/'+CboTpCtk+'/'+CboKdGdg.trim()+'/'+ChkSelesai+'/'+ChkSmSelesai+'/'+OptSemua+'/'+OptBayar+'/'+OptGratis+'/'+ChkBatal+'/'+ChkSmBatal,'_blank');
			}else{

				Swal.fire({
					title: 'Error',
					text: 'Gudang harus di pilih'
				})

			}

		}else{

			Swal.fire({
				title: 'Error',
				text: 'Anda harus melakukan proses terlebih dahulu'
			})

		}
	}

	<?php
		if(!empty($_GET['error'])){
	?>
			alert('Data form harus diisi semua!!!');
	<?php
		}
	?>
</script>