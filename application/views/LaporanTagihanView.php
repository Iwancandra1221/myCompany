<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
	<div>
		<br>
		<h1 style="text-align:center;font-weight:bold;font-size:large;">
			Laporan Tagihan Pembanding
		</h1>
	</div>
 	
	<div class="row">
		<div class="col-12"><br>
			<table class="table" border="0">
				<tr>
					<td width="150px">
						Periode
					</td>
					<td width="200px">
						<input type="text" class="form-control" name="periode_dari" id="periode_dari" value="<?php echo date('d-m-Y') ?>" required>
					</td>
					<td width="30px" align="center">
						s/d
					</td>
					<td width="200px">
						<input type="text" class="form-control" name="periode_sampai" id="periode_sampai" value="<?php echo date('d-m-Y') ?>" required>
					</td>
					<td></td>
				</tr>
				<tr>
					<td>
						Jenis Tagihan
					</td>
					<td colspan="2">
						<select class="form-control" name="jenis_tagihan" id="jenis_tagihan">
							<option value="ALL">ALL</option>
						</select>
					</td>
					<td colspan="2"></td>
				</tr>
				<tr>
					<td>
						No Tagihan
					</td>
					<td colspan="2">
						<input type="text" class="form-control" name="no_tagihan" id="no_tagihan">
					</td>
					<td colspan="2"></td>
				</tr>
				<tr>
					<td>
						Wilayah
					</td>
					<td colspan="2">
						<select class="form-control" name="wilayah" id="wilayah" onchange="getDealer();">
						</select>
					</td>
					<td colspan="2"></td>
				</tr>
				<tr>
					<td>
						Dealer
					</td>
					<td colspan="2">
						<select class="form-control" name="dealer" id="dealer">
							<option value="ALL">ALL</option>
						</select>
					</td>
					<td colspan="2"></td>
				</tr>
				<tr>
					<td colspan="5">
						<input type="submit" class="btn btn-light-dark" name="pdf" onclick="export_excel()" value="Export Excel"/>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>

<!-- <div style="margin:auto; width:90%">
	<div class="row">
		<div style="width:100%; overflow-x: auto;">
			<table class="table table-striped" style="margin:20px">
				<thead style="background-color:#eaeaea">
					<tr>
						<td width="30px" align="center">
							No
						</td>
						<td>
							No Tagihan
						</td>
						<td>
							Tgl JT
						</td>
						<td>
							Nama Dealer
						</td>
						<td>
							Kode Dealer
						</td>
						<td>
							No Penerimaan
						</td>
						<td>
							Total
						</td>
						<td>
							Penerimaan (Tagihan)
						</td>
						<td>
							Selisih Penerimaan
						</td>
						<td>
							No BBT
						</td>
						<td>
							Total
						</td>
						<td>
							BBT (Tagihan)
						</td>
						<td>
							Selisih BBT
						</td>
					</tr>
					<tbody>
						<tr>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
					</tbody>
				</thead>
			</table>
		</div>
	</div>
</div> -->

<script type="text/javascript">


	$('#periode_dari').datepicker({
		format: "dd-mm-yyyy",
		autoclose: true
	});

	$('#periode_sampai').datepicker({
		format: "dd-mm-yyyy",
		autoclose: true
	});

	function export_excel() {
		var awal = document.getElementById('periode_dari').value;
		var akhir = document.getElementById('periode_sampai').value;
		var jenis_tagihan = document.getElementById('jenis_tagihan').value;
		var no_tagihan = document.getElementById('no_tagihan').value;
		var wilayah = document.getElementById('wilayah').value;
		var dealer = document.getElementById('dealer').value;

		if(wilayah!=='' && dealer!=='' && dealer=='Loading'){
			var awal = btoa(awal).replace(/=+$/, '');
			var akhir = btoa(akhir).replace(/=+$/, '');
			var jenis_tagihan = btoa(jenis_tagihan).replace(/=+$/, '');
			var no_tagihan = btoa(no_tagihan).replace(/=+$/, '');
			var wilayah = btoa(wilayah).replace(/=+$/, '');
			var dealer = btoa(dealer).replace(/=+$/, '');

			window.open("<?php echo site_url('LaporanTagihan/'); ?>?awal=" + awal + "&akhir=" + akhir + "&jenis_tagihan=" + jenis_tagihan + "&no_tagihan=" + no_tagihan + "&wilayah=" + wilayah + "&dealer=" + dealer, "_blank");
		}else{
			Swal.fire({
  				icon: 'error',
   				text: 'Dealer harus diisi!!!'
			});
		}
	}

	jenis_tagihan();
	function jenis_tagihan() {
		document.getElementById('jenis_tagihan').innerHTML='<option>Loading</option>';
	    const selectElement = document.getElementById('jenis_tagihan');

	    fetch('<?php echo site_url('LaporanTagihan/jenis_tagihan'); ?>')
	    .then(response => {
	        if (response.ok) {
	            return response.json();
	        } else {
	            throw new Error('Gagal mengambil data JSON');
	        }
	    })
	    .then(result => {
	    	document.getElementById('jenis_tagihan').innerHTML='<option value="ALL">ALL</option>';
	    	if (result.result=='sukses'){
		        result.data.forEach(item => {
		            const option = document.createElement('option');
		            option.value = item.Jenis_Tagihan;
		            option.text = item.Jenis_Tagihan;
		            selectElement.appendChild(option);
		        });
		    }else{
		    	document.getElementById('jenis_tagihan').innerHTML='<option value="">Data tidak ada</option>';
		    }
	    })
	    .catch(error => {
	        console.error(error);
	    });
	}

	wilayah();
	function wilayah() {
		document.getElementById('wilayah').innerHTML='<option>Loading</option>';
	    const selectElement = document.getElementById('wilayah');

	    fetch('<?php echo site_url('LaporanTagihan/wilayah'); ?>')
	    .then(response => {
	        if (response.ok) {
	            return response.json();
	        } else {
	            throw new Error('Gagal mengambil data JSON');
	        }
	    })
	    .then(result => {
	    	document.getElementById('wilayah').innerHTML='<option value="ALL">ALL</option>';
	    	if (result.result=='sukses'){
		        result.data.forEach(item => {
		            const option = document.createElement('option');
		            option.value = item.Kd_Lokasi;
		            option.text = item.Wilayah;
		            option.setAttribute('data-id', item.Wilayah);
		            selectElement.appendChild(option);
		        });
		    }else{
		    	document.getElementById('wilayah').innerHTML='<option value="">Data tidak ada</option>';
		    }
	    })
	    .catch(error => {
	        console.error(error);
	    });
	}

	function getDealer(){
		

	    const selectElement = document.getElementById('dealer');

		const wilayah = document.getElementById('wilayah');
		const wilayahval = document.getElementById('wilayah').value;
		const selectedOption = wilayah.options[wilayah.selectedIndex];
		const dataIdValue = selectedOption.getAttribute('data-id');

	    const encodedDataIdValue = btoa(dataIdValue).replace(/=+$/, '');

	    if(wilayahval!=''){

	    	document.getElementById('dealer').innerHTML='<option>Loading</option>';

		    fetch('<?php echo site_url('LaporanTagihan/dealer'); ?>/'+wilayahval+'/'+encodedDataIdValue)
		    .then(response => {
		        if (response.ok) {
		            return response.json();
		        } else {
		            throw new Error('Gagal mengambil data JSON');
		        }
		    })
		    .then(result => {
		    	document.getElementById('dealer').innerHTML='<option value="ALL">ALL</option>';
		    	if (result.result=='sukses'){
			        result.data.forEach(item => {
			            const option = document.createElement('option');
			            option.value = item.kd_plg;
			            option.text = item.nm_plg;
			            selectElement.appendChild(option);
			        });
			    }else{
			    	document.getElementById('dealer').innerHTML='<option value="">Data tidak ada</option>';
			    }
		    })
		    .catch(error => {
		        console.error(error);
		    });
		}else{
			document.getElementById('dealer').innerHTML='<option>Data tidak ada</option>';
			Swal.fire({
  				icon: 'error',
   				text: 'Kode Lokasi dengan Wilayah '+dataIdValue+' tidak ada'
			});
		}
	}
</script>