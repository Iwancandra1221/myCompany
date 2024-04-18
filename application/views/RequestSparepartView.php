<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<style type="text/css">
	fieldset {
		background-color: #eeeeee;
	}

	legend {
		background-color: gray;
		color: white;
		padding: 5px lengthValuepx;
		font-size: 15px;
		padding: 10px;
	}

	.disablingDiv {
		z-index: 99999;

		/* make it cover the whole screen */
		position: fixed;
		top: 0%;
		left: 0%;
		width: 100%;
		height: 100%;
		overflow: hidden;
		margin: 0;
		/* make it white but fully transparent */
		background-color: white;
		opacity: 0.5;
	}

	.loader {
		position: absolute;
		left: 50%;
		top: 50%;
		z-index: 9999999;
		margin: -75px 0 0 -75px;
		border: 16px solid #f3f3f3;
		border-radius: 50%;
		border-top: 16px solid #3498db;
		width: 120px;
		height: 120px;
		-webkit-animation: spin 2s linear infinite;
		animation: spin 2s linear infinite;
	}

	@keyframes spin {
		0% {
			transform: rotate(0deg);
		}

		100% {
			transform: rotate(360deg);
		}
	}

	.filterText {
		width: 75%;
		background-color: #ffffcc;
	}

	.ui-autocomplete {
		overflow-x: hidden;
		max-height: 264px;
	}
</style>

<div id="disablingDiv" class="disablingDiv">
</div>
<div id="loading" class="loader"></div>
<form method="POST" id="prosestransaksi">
	<div class="container">
		<div class="row">
			<div class="page-title">PERMINTAAN SPAREPART</div>

			<fieldset class="form-group border p-3">
				<legend class="w-auto px-2">REQUEST</legend>
				<table class="table table-striped">
					<tr>
						<td>NO.REQUEST</td>
						<td></td>
						<td>TANGGAL</td>
						<td>MERK</td>
						<td>NOTA SERVICE</td>
						<td></td>
					</tr>
					<tr>
						<td>
							<input type="text" name="nomor_request" class="form-control" id="nomor_request" readonly>
						</td>
						<td width="50px">
							<button type="button" class="btn btn-light-dark" onclick="nomorRequestBrowse()">
								...
							</button>
						</td>

						<td width="200px">
							<input type="text" name="tanggal_transaksi" class="form-control" id="tanggal_transaksi" value="<?php echo date('d-M-Y') ?>" readonly required>
						</td>

						<td width="200px">
							<select name="merk" id="merk" class="form-control" onchange="loadSparepart();">
							</select>
						</td>

						<td>
							<input type="text" class="form-control" name="nota_service" id="nota_service" readonly>
						</td>
						<td width="50px">
							<button type="button" class="btn btn-light-dark" onclick="notaServiceBrowse()">
								...
							</button>
						</td>
					</tr>
				</table>
			</fieldset>
			<fieldset class="form-group border p-3">
				<legend class="w-auto px-2">DEALER</legend>
				<div class="col-8">
					<table class="table table-striped">
						<tr>
							<td width="100px">
								DEALER
							</td>
							<td colspan="1" width="170px">
								<input type="text" class="form-control" name="kode_dealer" id="kode_dealer" value="" readonly>
							</td>
							<td width="50px">
								<button type="button" class="btn btn-light-dark" onclick="dealerBrowse()">
									...
								</button>
							</td>
							<td colspan="3">
								<input type="text" class="form-control" name="nama_dealer" id="nama_dealer" value="" readonly>
							</td>
						</tr>
						<tr>
							<td>
								ALAMAT
							</td>
							<td colspan="5">
								<select name="alamat_dealer" id="alamat_dealer" class="form-control" readonly></select>
							</td>
						</tr>
					</table>
					<div class="col-10">
						<table class="table table-striped">
							<tr>
								<td width="100px">
									GUDANG
								</td>
								<td colspan="1">
									<input type="text" class="form-control" name="gudang_out" id="gudang_out" readonly>
								</td>
								<td width="50px">
									<button type="button" class="btn btn-light-dark" id="out" onclick="gudangBrowse('OUT')">
										...
									</button>
								</td>
							</tr>
							<tr>
								<td>
									TARGET
								</td>
								<td colspan="1">
									<input type="text" class="form-control" name="gudang_in" id="gudang_in" readonly>
								</td>
								<td width="50px">
									<button type="button" class="btn btn-light-dark" id="in" onclick="gudangBrowse('IN')">
										...
									</button>
								</td>
							</tr>
						</table>
					</div>
					<div class="col-2">
						<button type="button" class="btn btn-primary-dark" onclick="gudangBrowse('OUT')">
							Refresh<br>Gudang <br>&<br>Target
						</button>
					</div>
				</div>
				<div class="col-4">
					<table class="table table-hover">
						<tr>
							<td style="padding-top: 15px;">
								<input type="radio" name="option" id="penggantian" value="FR" onclick="optionRequest('P');" checked> Penggantian
							</td>
							<td style="padding-top: 15px;">
								<input type="radio" name="option" id="faktur" value="FK" onclick="optionRequest('F');"> Faktur
							</td>
						</tr>
						<tr>
							<td style="padding-top: 15px;">
								<input type="radio" name="option" id="melengkapi" value="MI" onclick="optionRequest('L');"> Melengkapi
							</td>
							<td style="padding-top: 15px;">
								<input type="radio" name="option" id="surat_jalan" value="SJ" onclick="optionRequest('S');"> SJ
							</td>
						</tr>
						<tr>
							<td style="padding-top: 15px;">
								<input type="radio" name="option" id="req_cabang_lain" value="IMP" onclick="optionRequest('C');"> Rek SP Ke Cabang Lain
							</td>
							<td></td>
						</tr>
						<tr>
							<td>
								<select name="lokasi" id="lokasi" class="form-control">
								</select>
							</td>
						</tr>
					</table>
				</div>
			</fieldset>
			<table class="table table-striped table-bordered" id="tableSparepart" cellspacing="0">
				<thead id="theadSparepart">
					<tr>
						<th width="15%" style="border:1px solid #ccc;">Kode Sparepart</th>
						<th width="15%" class='hideOnMobile' style="border:1px solid #ccc;">Nama Sparepart</th>
						<th width="10%" class='hideOnMobile' style="border:1px solid #ccc;">Stock</th>
						<th width="10%" style="border:1px solid #ccc;">Qty</th>
						<th width="15%" style="border:1px solid #ccc;">No Faktur</th>
						<th width="5%" style="border:1px solid #ccc;">#</th>
					</tr>
				</thead>
				<tfilter>
					<td style="background-color:#990033;" colspan="2">
						<input type='hidden' id='KdSparepart'>
						<input type='hidden' id='NmSparepart'>
						<input type='text' class='filterText' style="width:100%" name='filterSparepart' id='filterSparepart'>
					</td>
					<td style="background-color:#990033;"><input type='text' name='stock' id='stock' style="width:100px" autocomplete="off" readonly></td>
					<td style="background-color:#990033;"><input type='text' name='qty' id='qty' style="width:100px" autocomplete="off"></td>
					<td style="background-color:#990033;"><input type='text' name='faktur' id='faktur' style="width:250px" autocomplete="off"></td>
					<td class='hideOnMobile' style="background-color:#990033;">
						<button id="btnFilter" type="button">ADD</button>
					</td>
				</tfilter>
				<tbody id="tbodySparepart">
				</tbody>
			</table>
			<fieldset class="form-group border p-3">
				<table class="table table-striped">
					<tr>
						<td colspan="6">
							keterangan
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<textarea class="form-control" name="keterangan" id="keterangan" readonly></textarea>
						</td>
						<td colspan="3" align="right">

							<button type="submit" id="btnSubmit" class="btn btn-primary-dark">
								SIMPAN
							</button>

							<button type="button" id="btnCancel" class="btn btn-warning-dark">
								BATAL REQUEST
							</button>

							<button type="button" id="btnBatal" class="btn btn-danger-dark">
								BATAL
							</button>
						</td>
					</tr>
				</table>
			</fieldset>
		</div>
	</div>
</form>

<div class="modal fade" id="requestSP" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="ModalLabel"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<table class="table table-striped" id="dataRequestSP">
					<thead>
						<tr>
							<td>Tanggal</td>
							<td>Kode Request</td>
							<td>P</td>
							<td>Keterangan</td>
							<td>Nota Service</td>
							<td>Gudang</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="8">Loading...</td>
						</tr>
					</tbody>
				</table>

				<table class="table table-striped" id="dataDetailSP">
					<thead>
						<tr>
							<td>Kode Sparepart</td>
							<td>Nama Sparepart</td>
							<td>Merk</td>
							<td>Qty</td>
							<td>No BRP</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="8">Loading...</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger-dark" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="notaService" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="ModalLabel"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<table class="table table-striped" id="dataNotaService">
					<thead>
						<tr>
							<td>No Service <span id="noService"></span></td>
							<td>Tanggal Service <span id="tglService"></span></td>
							<td>Nama Pelanggan <span id="nmPel"></span></td>
							<td>Kode Barang <span id="kdBrg"></span></td>
							<td>Merk <span id="merk"></span></td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="2">Loading...</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger-dark" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="dealer" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="ModalLabel"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<table class="table table-striped" id="dataDealer">
					<thead>
						<tr>
							<td>Kode Pelanggan</td>
							<td>Nama Pelanggan</td>
							<td>Alamat Pelanggan</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="3">Loading...</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger-dark" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="mappingGudang" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="ModalLabel"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<table class="table table-striped" id="dataMappingGudang">
					<thead>
						<tr>
							<td>Nama Gudang</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="5">Loading...</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger-dark" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function() {

		GetNoRequest();
		GetMerkDropdown();
		GetLokasiDropdown();
		optionRequest('P');

		$('#tanggal_transaksi').datepicker({
			format: "dd-mm-yyyy",
			autoclose: true
		});

		$("#btnFilter").click(function() {
			addSparepart();
		});

		$('#prosestransaksi').submit(function(e) {
			Swal.fire({
				title: 'Loading...',
				showConfirmButton: false
			})

			e.preventDefault();

			let formData = $(this).serialize();

			$.ajax({
				type: 'POST',
				url: '<?php echo site_url('RequestSparepart/saveRequest'); ?>',
				data: formData,
				success: function(data) {
					const jsonObject = JSON.parse(data);
					if (jsonObject.result == 'sukses') {
						Swal.fire({
							title: 'Nomor transaksi : ' + jsonObject.data,
							text: jsonObject.result,
							icon: 'success',
							showCancelButton: false,
							confirmButtonColor: '#3085d6',
							cancelButtonColor: '#d33',
							confirmButtonText: 'Close'
						}).then((result) => {
							window.location.href = '<?php echo site_url("RequestSparepart"); ?>';
						})
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Oops...',
							text: jsonObject.error
						})
					}
				}
			});
		});

	});

	function dealerBrowse() {

		if ($.fn.DataTable.isDataTable('#dataDealer')) {
			$('#dataDealer').DataTable().destroy();
		}

		$('#dataDealer').dataTable({
			"bProcessing": true,
			"bServerSide": true,
			"columnDefs": [{
					targets: 'no-sort',
					orderable: false
				},
				{
					targets: 'col-hide',
					visible: false
				}
			],
			"sAjaxSource": '<?php echo site_url('RequestSparepart/getDealer'); ?>',
			"oLanguage": {
				"sLengthMenu": "Menampilkan _MENU_ Data per halaman",
				"sZeroRecords": "Maaf, Data tidak ada",
				"sInfo": "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
				"sInfoEmpty": "Menampilkan 0 s/d 0 dari 0 data",
				"sSearch": "",
				"sInfoFiltered": "",
				"oPaginate": {
					"sPrevious": "Sebelumnya",
					"sNext": "Berikutnya"
				}
			},
			"scrollX": true,
			"scrollX": true
		});

		$('#dealer').modal('show');
	}

	function getDetailDealerKhusus(kdDealer = '') {
		if (kdDealer !== '') {
			var data = '&kd_dealer=' + kdDealer;
		}

		$.ajax({
			type: 'POST',
			url: '<?php echo site_url('RequestSparepart/getDetailDealerKhusus'); ?>',
			data: data,
			success: function(data) {
				const jsonObject = JSON.parse(data);
				if (jsonObject.result == 'sukses') {
					let kdWilhtml = '';
					document.getElementById('kode_dealer').value = jsonObject.data.Kd_Plg;
					document.getElementById('nama_dealer').value = jsonObject.data.Nm_Plg;
					kdWilhtml += '<option value="' + jsonObject.data_Kd_Wil + '">' + jsonObject.data.Nm_Wil + '</option>';
					document.getElementById('alamat_dealer').innerHTML = kdWilhtml;


					let kdWil = document.getElementById('alamat_dealer').value;
					if (kdWil = '') {
						$.ajax({
							type: 'POST',
							url: '<?php echo site_url('RequestSparepart/getDetailDealer'); ?>',
							data: data,
							success: function(data) {
								let kdWilhtml = '';
								const jsonObject = JSON.parse(data);
								if (jsonObject.result == 'sukses') {
									document.getElementById('kode_dealer').value = jsonObject.data.Kd_Plg;
									document.getElementById('nama_dealer').value = jsonObject.data.Nm_Plg;
									kdWilhtml += '<option value="' + jsonObject.data_Kd_Wil + '">' + jsonObject.data.Nm_Wil + '</option>';
									document.getElementById('alamat_dealer').innerHTML = kdWilhtml;
								}
							}
						});
					}
				}
			}
		});
	}

	function getDetailDealer(kdDealer = '') {
		if (kdDealer !== '') {
			var data = '&kd_dealer=' + kdDealer;
		}

		$.ajax({
			type: 'POST',
			url: '<?php echo site_url('RequestSparepart/getDetailDealer'); ?>',
			data: data,
			success: function(data) {
				let merkhtml = '';
				const jsonObject = JSON.parse(data);
				if (jsonObject.result == 'sukses') {
					document.getElementById('kode_dealer').value = jsonObject.data.Kd_Plg;
					document.getElementById('nama_dealer').value = jsonObject.data.Nm_Plg;
					kdWilhtml += '<option value="' + jsonObject.data_Kd_Wil + '">' + jsonObject.data.Nm_Wil + '</option>';
					document.getElementById('alamat_dealer').innerHTML = kdWilHtml;
				}
			}
		});
	}

	function selectDealer(xkd = '', xnama = '', xalm = '') {
		let kd = atob(xkd).trim();
		let nama = atob(xnama).trim();
		let alm = atob(xalm).trim();

		document.getElementById('kode_dealer').value = kd;
		document.getElementById('nama_dealer').value = nama;
		document.getElementById('alamat_dealer').value = alm;

		$('#dealer').modal('hide');
	}

	function changeDealer() {
		let kdDealer = document.getElementById('kode_dealer').value;
		getDetailDealerKhusus(kdDealer);
		optionRequest(document.getElementById('option').checked)

	}

	function notaServiceBrowse() {

		if ($.fn.DataTable.isDataTable('#dataNotaService')) {
			$('#dataNotaService').DataTable().destroy();
		}

		$('#dataNotaService').dataTable({
			"bProcessing": true,
			"bServerSide": true,
			"columnDefs": [{
					targets: 'no-sort',
					orderable: false
				},
				{
					targets: 'col-hide',
					visible: false
				}
			],
			"sAjaxSource": '<?php echo site_url('RequestSparepart/getNotaService'); ?>',
			"oLanguage": {
				"sLengthMenu": "Menampilkan _MENU_ Data per halaman",
				"sZeroRecords": "Maaf, Data tidak ada",
				"sInfo": "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
				"sInfoEmpty": "Menampilkan 0 s/d 0 dari 0 data",
				"sSearch": "",
				"sInfoFiltered": "",
				"oPaginate": {
					"sPrevious": "Sebelumnya",
					"sNext": "Berikutnya"
				}
			},
			"scrollX": true
		});

		$('#notaService').modal('show');
	}

	function selectNota(xsvc = '', xnama = '', xmerk = '') {
		let svc = atob(xsvc).trim()
		let nama = atob(xnama).trim();
		let merk = atob(xmerk).trim();

		document.getElementById('nota_service').value = svc;
		document.getElementById('nama_dealer').value = nama;
		document.getElementById('merk').value = merk;

		$('#notaService').modal('hide');
	}

	function nomorRequestBrowse() {

		if ($.fn.DataTable.isDataTable('#dataRequestSP')) {
			$('#dataRequestSP').DataTable().destroy();
		}

		$('#dataRequestSP').dataTable({
			"bProcessing": true,
			"bServerSide": true,
			"columnDefs": [{
					targets: 'no-sort',
					orderable: false
				},
				{
					targets: 'col-hide',
					visible: false
				}
			],
			"sAjaxSource": '<?php echo site_url('RequestSparepart/getRequestSP'); ?>',
			"oLanguage": {
				"sLengthMenu": "Menampilkan _MENU_ Data per halaman",
				"sZeroRecords": "Maaf, Data tidak ada",
				"sInfo": "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
				"sInfoEmpty": "Menampilkan 0 s/d 0 dari 0 data",
				"sSearch": "",
				"sInfoFiltered": "",
				"oPaginate": {
					"sPrevious": "Sebelumnya",
					"sNext": "Berikutnya"
				}
			},
			"scrollX": true,
			"scrollX": true
		});

		$('#requestSP').modal('show');
	}

	function selectRequest(xtanggal = '', xreq = '', xpros = '', xket = '', xsvc = '', xgudang = '', xkdplg = '', xkdwil = '') {
		let req = atob(xreq).trim();
		let tgl = atob(xtanggal).trim();
		let ket = atob(xket).trim();
		let svc = atob(xsvc).trim();
		let gudang = atob(xgudang).trim();
		let kdPlg = atob(xkdplg).trim();
		let kdWil = atob(xkdwil).trim();

		document.getElementById('tanggal_transaksi').value = tgl;
		document.getElementById('nomor_request').value = req;
		document.getElementById('keterangan').value = ket;
		document.getElementById('nota_service').value = svc;
		document.getElementById('gudang_out').value = gudang;
		document.getElementById('kode_dealer').value = kdPlg;
		document.getElementById('alamat_dealer').value = kdWil;
		changeDealer()
		$('#requestSP').modal('hide');
	}

	function gudangBrowse(ButtonId) {
		let mapping_type = '';
		let action = '';
		if (ButtonId === 'OUT') {
			mapping_type = 'REQUEST SP OUT';
			action = ButtonId;
		} else {
			mapping_type = 'REQUEST SP IN';
			action = ButtonId;
		}
		console.log(action);
		if ($.fn.DataTable.isDataTable('#dataMappingGudang')) {
			$('#dataMappingGudang').DataTable().destroy();
		}

		$('#dataMappingGudang').dataTable({
			"bProcessing": true,
			"bServerSide": true,
			"columnDefs": [{
					targets: 'no-sort',
					orderable: false
				},
				{
					targets: 'col-hide',
					visible: false
				}
			],
			"sAjaxSource": '<?php echo site_url('RequestSparepart/getMappingGudang'); ?>/' + mapping_type + '/' + action,
			"oLanguage": {
				"sLengthMenu": "Menampilkan _MENU_ Data per halaman",
				"sZeroRecords": "Maaf, Data tidak ada",
				"sInfo": "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
				"sInfoEmpty": "Menampilkan 0 s/d 0 dari 0 data",
				"sSearch": "",
				"sInfoFiltered": "",
				"oPaginate": {
					"sPrevious": "Sebelumnya",
					"sNext": "Berikutnya"
				}
			},
			"scrollX": true,
			"scrollX": true
		});

		$('#mappingGudang').modal('show');
	}

	function selectMapping(xmapping = '', xaction = '') {
		let mapping = atob(xmapping).trim();
		if (xaction === 'OUT') {
			document.getElementById('gudang_out').value = mapping;
			loadSparepart();
		} else {
			document.getElementById('gudang_in').value = mapping;
		}

		$('#mappingGudang').modal('hide');
	}

	function GetNoRequest() {
		document.getElementById('nomor_request').value = 'Loading...';
		$.ajax({
			type: 'POST',
			url: '<?php echo site_url('RequestSparepart/getNoRequest'); ?>',
			success: function(data) {
				console.log(data)
				const jsonObject = JSON.parse(data);
				if (jsonObject.result == 'sukses') {
					document.getElementById('nomor_request').value = jsonObject.data;
				}
			}
		});
	}

	function GetMerkDropdown(selected = '') {

		document.getElementById('merk').innerHTML = '<option value="">Loading...</option>';

		$.ajax({
			type: 'POST',
			url: '<?php echo site_url('RequestSparepart/getMerk'); ?>',
			success: function(data) {
				console.log(data);
				const jsonObject = JSON.parse(data);
				if (jsonObject.result == 'sukses') {
					let merkhtml = '';
					merkhtml = "<option value='' selected></option>";
					for (var i = 0; i < jsonObject.data.length; i++) {
						let xselected = '';
						if (selected == jsonObject.data[i].Merk) {
							xselected = 'selected';
						}
						merkhtml += '<option value="' + jsonObject.data[i].Merk + '" ' + xselected + '>' + jsonObject.data[i].Merk + '</option>';
					}
					document.getElementById('merk').innerHTML = merkhtml;
				}
			}
		});
	}

	function GetLokasiDropdown(selected = '') {

		document.getElementById('lokasi').innerHTML = '<option value="">Loading...</option>';

		$.ajax({
			type: 'POST',
			url: '<?php echo site_url('RequestSparepart/getLokasi'); ?>',
			success: function(data) {
				console.log(data)
				const jsonObject = JSON.parse(data);
				if (jsonObject.result == 'sukses') {
					let lokasihtml = '';
					for (var i = 0; i < jsonObject.data.length; i++) {
						let xselected = '';
						if (selected == jsonObject.data[i].Lokasi) {
							xselected = 'selected';
						}
						lokasihtml += '<option value="' + jsonObject.data[i].Kd_Lokasi + '" ' + xselected + '>' + jsonObject.data[i].Lokasi + '</option>';
					}
					document.getElementById('lokasi').innerHTML = lokasihtml;
				}
			}
		});
	}

	function optionRequest(option) {
		if (option === 'P') {
			document.getElementById('lokasi').style.display = 'none';
			document.getElementById('gudang_in').style.display = 'block';
			document.getElementById('in').style.display = 'block';
		} else if (option === 'F') {
			document.getElementById('lokasi').style.display = 'none';
			document.getElementById('gudang_in').style.display = 'none';
			document.getElementById('in').style.display = 'none';
		} else if (option === 'L') {
			document.getElementById('lokasi').style.display = 'none';
			document.getElementById('gudang_in').style.display = 'block';
			document.getElementById('in').style.display = 'block';
		} else if (option === 'S') {
			document.getElementById('lokasi').style.display = 'none';
			document.getElementById('gudang_in').style.display = 'block';
			document.getElementById('in').style.display = 'block';
		} else {
			document.getElementById('lokasi').style.display = 'block';
			document.getElementById('gudang_in').style.display = 'none';
			document.getElementById('in').style.display = 'none';
		}
		keterangan(option);
	}

	function keterangan(e = '') {
		console.log("xxx", e);
		let htmlketerangan = '';
		if (e === 'P') {
			htmlketerangan += 'MENGGANTIKAN';
			htmlketerangan += ' ';
			htmlketerangan += document.getElementById('nama_dealer').value;
			htmlketerangan += ' ';
			htmlketerangan += document.getElementById('nota_service').value;
		} else if (e === 'F') {
			htmlketerangan += 'BELI';
			htmlketerangan += ' ';
			htmlketerangan += document.getElementById('nama_dealer').value;
			htmlketerangan += ' ';
			htmlketerangan += document.getElementById('nota_service').value;
		} else if (e === 'L') {
			htmlketerangan += 'MELENGKAPI';
			htmlketerangan += ' ';
			htmlketerangan += document.getElementById('nama_dealer').value;
			htmlketerangan += ' ';
			htmlketerangan += document.getElementById('nota_service').value;
		} else if (e == 'S') {
			htmlketerangan += 'PINDAH GUDANG';
			htmlketerangan += ' ';
			htmlketerangan += document.getElementById('nama_dealer').value;
			htmlketerangan += ' ';
			htmlketerangan += document.getElementById('nota_service').value;
		} else {
			htmlketerangan += 'IMPORT';
			htmlketerangan += ' ';
			htmlketerangan += document.getElementById('nama_dealer').value;
			htmlketerangan += ' ';
			htmlketerangan += document.getElementById('nota_service').value;
		}
		document.getElementById('keterangan').value = htmlketerangan;
	}

	function loadSparepart() {

		merk = $("#merk").val();
		gudangOut = $("#gudang_out").val();
		kdSparepart = $("#KdSparepart").val();

		$.ajax({
			url: "<?php echo site_url('RequestSparepart/getSparepart'); ?>",
			type: 'POST',
			data: {
				merk: merk,
				gudang: gudangOut,
				kdSparepart: kdSparepart
			},
			dataType: 'JSON',
			success: function(data) {
				if (data.error != undefined) {
					$("#tbodySparepart").html("");
				} else {
					$("#filterSparepart").autocomplete({
						source: data,
						select: function(event, ui) {
							$("#KdSparepart").val(ui.item.Kd_SparePart);
							$("#NmSparepart").val(ui.item.Nm_SparePart);
							$("#stock").val(ui.item.StockAkhir);
						}
					});
				}
			},
		});
	}

	function addSparepart() {
		let no = 0;
		let KdSparepart = $('#KdSparepart').val();
		let NmSparepart = $('#NmSparepart').val();
		let stock = $('#stock').val();
		let qty = $('#qty').val();
		let faktur = $('#faktur').val();

		if (KdSparepart == '') {
			return false;
		}

		if (checkKodeSP(KdSparepart)) {
			alert('Kode sparepart sudah ada!');
			$('#KdSparepart').val('');
			$('#NmSparepart').val('');
			$('#stock').val('');
			$('#qty').val('');
			$('#faktur').val('');
			$('#filterSparepart').val('');
			$('#filterSparepart').focus();
			return false;
		}

		no += 1;

		let x = '';
		x += '<tr id="baris_' + no + '">';
		x += '	<td colspan="2" style="border:1px solid #ccc;padding:2px;">';
		x += '		' + KdSparepart;
		x += '		<input type="hidden" name="kodeSparepart[]" id="kodeSparepart[]" class="kodeSparepart" value="' + KdSparepart + '" readonly>';
		x += '		<input type="hidden" name="stock[]" id="stock[]" value="' + stock + '" readonly>';
		x += '		<input type="hidden" name="qty[]" id="qty[]" value="' + qty + '" readonly>';
		x += '		<input type="hidden" name="faktur[]" id="faktur[]" value="' + faktur + '" readonly>';
		x += '		' + NmSparepart;
		x += '	</td>';
		x += '	<td style="border:1px solid #ccc;padding:2px;">' + stock + '</td>';
		x += '	<td style="border:1px solid #ccc;padding:2px;">' + qty + '</td>';
		x += '	<td style="border:1px solid #ccc;padding:2px;">' + faktur + '</td>';
		x += '	<td style="border:1px solid #ccc;padding:2px;text-align:center">';
		x += '		<button id="btnFilter" type="button" onclick="javascript:DelRow(' + no + ')"><b>&#10005;</b></button>';
		x += '		</td>';
		x += '</tr>';

		$("#tbodySparepart").append(x);

		$('#KdSparepart').val('');
		$('#NmSparepart').val('');
		$('#stock').val('');
		$('#qty').val('');
		$('#faktur').val('');
		$('#filterSparepart').val('');
		$('#filterSparepart').focus();

	}

	$("#loading").hide();
	$("#disablingDiv").hide();

	function checkKodeSP(kdSp) {
		let beda = false;
		$('.kodeSparepart').each(function(i, obj) {
			if ($(this).val() == kdSp) {
				beda = true;
			}
		});
		return beda;
	}

	function DelRow(no) {
		let text = "Ingin hapus baris ini?";
		if (confirm(text) == true) {
			$('#baris_' + no).remove();
		}
	}
</script>