		<div class="container" id="listBrandManager">
			<div class="title">Config Autosent</div>
			<br>
			<table id="table_configautosent" class="table table-striped table-bordered" cellspacing="0" style="font-size: 12px;" summary="Table Config Auto Send">
				<thead>
					<tr>
						<th id="Kode_Lokasi">Kode Lokasi</th>
						<th id="Nama_Lokasi">Nama Lokasi</th>
						<th id="Jumlah_Record_x">Jumlah Record</th>
						<th id="Server">Server</th>
						<th id="Database">Database</th>
						<th id="Active">Active</th>
						<th id="Action">Action</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>

		<div class="modal fade" id="view" tabindex="-1" role="dialog" aria-labelledby="viewLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-body row" style="font-size:12px">
						<div class="col-12 p-0 m-0">
							<input type="hidden" name="proses" id="proses" class="form-control" style="font-size:12px" readonly>
							<div class="col-md-3 col-sm-3 col-lg-2-10">
								Kode Lokasi
							</div>
							<div class="col-md-9 col-sm-9 col-lg-2-10">
								<select name="Kd_Lokasi" id="Kd_Lokasi" class="form-control" style="font-size:12px">
									<option value="">
										Select
									</option>
								</select>
							</div>
							<div class="col-md-3 col-sm-3 col-lg-2-10">
								Nama Lokasi
							</div>
							<div class="col-md-9 col-sm-9 col-lg-2-10">
								<input type="text" name="Nm_Lokasi" id="Nm_Lokasi" class="form-control" style="font-size:12px" readonly>
							</div>

							<div class="col-md-3 col-sm-3 col-lg-2-10">
								Record
							</div>
							<div class="col-md-9 col-sm-9 col-lg-2-10">
								<input type="text" name="Jumlah_Record" id="Jumlah_Record" class="form-control" style="font-size:12px" onkeypress="return hanyaAngka(event)">
							</div>

							<div class="col-md-3 col-sm-3 col-lg-2-10">
								Initial
							</div>
							<div class="col-md-9 col-sm-9 col-lg-2-10">
								<input type="text" name="Initial" id="Initial" class="form-control" style="font-size:12px">
							</div>
							<div class="col-md-3 col-sm-3 col-lg-2-10">
								Server
							</div>
							<div class="col-md-9 col-sm-9 col-lg-2-10">
								<input type="text" name="Db_Server" id="Db_Server" class="form-control" style="font-size:12px">
							</div>

							<div class="col-md-3 col-sm-3 col-lg-2-10">
								Database
							</div>
							<div class="col-md-9 col-sm-9 col-lg-2-10">
								<input type="text" name="Db_Database" id="Db_Database" class="form-control" style="font-size:12px">
							</div>

							<div class="col-md-3 col-sm-3 col-lg-2-10">
								API
							</div>
							<div class="col-md-9 col-sm-9 col-lg-2-10">
								<input type="text" name="Db_API" id="Db_API" class="form-control" style="font-size:12px">
							</div>

							<!-- <div style="width:100%; float: left;">
					      <div class="col-md-3 col-sm-3 col-lg-2-10">
				      		Transaction Date Started
					      </div>
					      <div class="col-md-9 col-sm-9 col-lg-2-10">
					      	<input type="text" name="TrxDate_Begin" id="TrxDate_Begin" class="form-control" style="font-size:12px">
					      </div>
					  </div>

					  <div style="width:100%; float: left;">
					      <div class="col-md-3 col-sm-3 col-lg-2-10">
				      		Transaction End Date
					      </div>
					      <div class="col-md-9 col-sm-9 col-lg-2-10">
					      	<input type="text" name="TrxDate_End" id="TrxDate_End" class="form-control" style="font-size:12px">
					      </div>
					  </div> -->

							<div class="col-md-3 col-sm-3 col-lg-2-10">
								Active
							</div>
							<div class="col-md-9 col-sm-9 col-lg-2-10">
								<input type="checkbox" name="Aktif" id="Aktif" style="font-size:12px" value="Y">
							</div>

						</div>


					</div>

					<div class="modal-footer">
						<?php
						if ($_SESSION['can_create'] == true || $_SESSION['can_update'] == true) {
						?>
							<button type="button" id="btnsave" class="btn btn-default" title="Save" style="font-weight: normal;" onclick="save_config()">
								Save
							</button>
						<?php
						}
						?>
						<button type="button" class="btn btn-default" data-dismiss="modal" title="Close" style="font-weight: normal; display: inline;">
							Close
						</button>
					</div>

				</div>
			</div>
		</div>

		<script type="text/javascript">
			<?php
			if ($_SESSION['can_create'] == true || $_SESSION['can_update'] == true) {
			?>

				function save_config() {

					var Aktif = document.getElementById('Aktif').checked;

					if (Aktif == true) {
						Aktif = 'Y';
					} else {
						Aktif = 'N';
					}

					var data = '';
					data += '&api=APITES';
					data += '&proses=' + document.getElementById('proses').value;
					data += '&Kd_Lokasi=' + document.getElementById('Kd_Lokasi').value;
					data += '&Nm_Lokasi=' + document.getElementById('Nm_Lokasi').value;
					data += '&Jumlah_Record=' + document.getElementById('Jumlah_Record').value;
					data += '&Initial=' + document.getElementById('Initial').value;
					data += '&Db_Server=' + document.getElementById('Db_Server').value;
					data += '&Db_Database=' + document.getElementById('Db_Database').value;
					data += '&Db_API=' + document.getElementById('Db_API').value;
					data += '&Aktif=' + Aktif;

					//console.log(data);
					$.ajax({
						type: 'POST',
						url: 'ConfigAutoSent/sendConfig',
						data: data,
						success: function(data) {
							if (data == 'double') {

								alert('Kode lokasi yang anda masukan sudah ada dalam list, silahkan pilih kode lokasi yang lain!!!');

							} else if (data == 'error') {

								alert('Transaksi yang anda lakukan tidak dapat di lanjutkan, silahkan coba beberapa saat lagi!!!')

							} else if (data == 'error_input') {

								alert('Data yang anda masukan belum lengkap, atau Initial tidak boleh lebih dari 2 karakter!!!');

							} else {

								window.location.href = '<?php echo site_url('ConfigAutosent') ?>';

							}

							return false
						}

					});

				}

			<?php
			}
			?>



			function add_configautosent() {
				$('#view').modal('show');

				document.getElementById('proses').value = 'new';
				document.getElementById('Kd_Lokasi').innerHTML = '';
				document.getElementById('Nm_Lokasi').value = '';
				document.getElementById('Jumlah_Record').value = '';
				document.getElementById('Initial').value = '';
				//document.getElementById('Last_Run').value ='';
				document.getElementById('Db_Server').value = '';
				document.getElementById('Db_Database').value = '';
				document.getElementById('Db_API').value = '';
				//document.getElementById('TrxDate_Begin').value ='';
				//document.getElementById('TrxDate_End').value = '';
				document.getElementById('Aktif').checked = true;

				$.ajax({
					type: 'POST',
					url: 'ConfigAutoSent/getCabang',
					dataType: 'json',
					success: function(data) {
						var html = '<option value="">Select</option>';
						for (x = 0; x < data.length; x++) {

							html += '<option value="' + data[x].Kd_Lokasi + '" data-name="' + data[x].Nm_Lokasi + '">' + data[x].Kd_Lokasi + '</option>'

						}

						document.getElementById('Kd_Lokasi').innerHTML = html;

						return false
					}

				});

				enabled_all();
				document.getElementById('btnsave').disabled = false;
			}


			<?php
			if ($_SESSION['can_update'] == true) {
			?>

				function edit_configautosend(a, b, c, d, e, f, g, h, i, j, k) {
					$('#view').modal('show');


					detail_Config(a, b, c, d, e, f, g, h, i, j, k);

					enabled_all();

					document.getElementById('proses').value = 'edit';
					document.getElementById('Kd_Lokasi').disabled = true;
					document.getElementById('Nm_Lokasi').readOnly = true;

					document.getElementById('btnsave').disabled = false;
				}
			<?php
			}
			if ($_SESSION['can_delete'] == true) {
			?>

				function delete_configautosend(a, b, c, d, e, f, g, h, i, j, k) {
					if (confirm("Apakah anda yakin ingin menghasil Lokasi dengan Kode " + a) == true) {
						var data = '';
						data += '&api=APITES';
						data += '&proses=delete';
						data += '&Kd_Lokasi=' + a;

						console.log(data);
						$.ajax({
							type: 'POST',
							url: 'ConfigAutoSent/sendConfig',
							data: data,
							success: function(data) {

								window.location.href = '<?php echo site_url('ConfigAutosent') ?>';

								return false
							}

						});
					}
				}
			<?php
			}
			?>

			$('#Kd_Lokasi').on("change", function() {
				var select = $("#Kd_Lokasi option:selected").attr('data-name');
				document.getElementById('Nm_Lokasi').value = select;
			});

			function view_configautosend(a, b, c, d, e, f, g, h, i, j, k) {

				$('#view').modal('show');

				detail_Config(a, b, c, d, e, f, g, h, i, j, k);

				disabled_all();

				document.getElementById('proses').value = 'view';
				document.getElementById('btnsave').disabled = true;
			}

			function detail_Config(a, b, c, d, e, f, g, h, i, j, k) {
				document.getElementById('Kd_Lokasi').innerHTML = '<option value="' + a + '">' + a + '</option>';
				document.getElementById('Nm_Lokasi').value = b;
				document.getElementById('Jumlah_Record').value = c;
				document.getElementById('Initial').value = d;
				//document.getElementById('Last_Run').value =e;
				document.getElementById('Db_Server').value = f;
				document.getElementById('Db_Database').value = g;
				document.getElementById('Db_API').value = h;
				//document.getElementById('TrxDate_Begin').value = i;

				/*if (j == 'null') {
					document.getElementById('TrxDate_End').value = '';
				} else {
					document.getElementById('TrxDate_End').value = j;
				}*/

				if (k == 'Y') {
					document.getElementById('Aktif').checked = true;
				} else {
					document.getElementById('Aktif').checked = false;
				}
			}


			function disabled_all() {
				document.getElementById('Kd_Lokasi').disabled = true;
				document.getElementById('Nm_Lokasi').readOnly = true;
				document.getElementById('Jumlah_Record').readOnly = true;
				document.getElementById('Initial').readOnly = true;
				//document.getElementById('Last_Run').readOnly =true;
				document.getElementById('Db_Server').readOnly = true;
				document.getElementById('Db_Database').readOnly = true;
				document.getElementById('Db_API').readOnly = true;
				//document.getElementById('TrxDate_Begin').readOnly =true;
				//document.getElementById('TrxDate_End').readOnly =true;
				document.getElementById('Aktif').disabled = true;
			}

			function enabled_all() {
				document.getElementById('Kd_Lokasi').disabled = false;
				document.getElementById('Nm_Lokasi').readOnly = true;
				document.getElementById('Jumlah_Record').readOnly = false;
				document.getElementById('Initial').readOnly = false;
				//document.getElementById('Last_Run').readOnly =false;
				document.getElementById('Db_Server').readOnly = false;
				document.getElementById('Db_Database').readOnly = false;
				document.getElementById('Db_API').readOnly = false;
				//document.getElementById('TrxDate_Begin').readOnly =false;
				//document.getElementById('TrxDate_End').readOnly =false;
				document.getElementById('Aktif').disabled = false;
			}

			$(document).ready(function() {

				function load_list() {
					table_configautosent.clear().draw();
					$.ajax({
						type: 'POST',
						url: 'ConfigAutoSent/getList',
						dataType: 'json',
						success: function(data) {
							var html = '';
							for (x = 0; x < data.length; x++) {
								var d = [];
								d[0] = data[x].Kd_Lokasi;
								d[1] = data[x].Nm_Lokasi;
								d[2] = data[x].Jumlah_Record;
								d[3] = data[x].Db_Server;
								d[4] = data[x].Db_Database;
								d[5] = data[x].Aktif;

								var aksi = '';

								var action = "'" + data[x].Kd_Lokasi + "','" + data[x].Nm_Lokasi + "','" + data[x].Jumlah_Record + "','" + data[x].Initial + "','" + data[x].Last_Run + "','" + data[x].Db_Server + "','" + data[x].Db_Database + "','" + data[x].Db_API + "','" + data[x].TrxDate_Begin + "','" + data[x].TrxDate_End + "','" + data[x].Aktif + "'";

								<?php
								if ($_SESSION['can_read'] == true) {
								?>
									aksi += '<button class="btn btn-sm btn-default" Kd_Lokasi="' + data[x].Kd_Lokasi + '" onclick="view_configautosend(' + action + ')" title="View"><i class="glyphicon glyphicon-search"></i></button> ';
								<?php
								}
								if ($_SESSION['can_update'] == true) {
								?>
									aksi += '<button class="btn btn-sm btn-default" Kd_Lokasi="' + data[x].Kd_Lokasi + '" onclick="edit_configautosend(' + action + ')" title="Edit"><i class="glyphicon glyphicon-pencil"></i></button> ';
								<?php
								}
								if ($_SESSION['can_delete'] == true) {
								?>
									aksi += '<button class="btn btn-sm btn-default" Kd_Lokasi="' + data[x].Kd_Lokasi + '" onclick="delete_configautosend(' + action + ')" title="Delete"><i class="glyphicon glyphicon-trash"></i></button> ';
								<?php
								}
								?>

								d[6] = aksi;

								table_configautosent.row.add(d);
							}
							table_configautosent.draw();
						}
					});
				}

				load_list();


				$('#Last_Run,#TrxDate_Begin,#TrxDate_End').datetimepicker({
					dateFormat: "yy-mm-dd",
					timeFormat: "hh:mm:ss",
					autoclose: true
				});

			});

			table_configautosent = $('#table_configautosent').DataTable({
				"pageLength": 10,
				"searching": true,
				"autoWidth": false,
				"columnDefs": [{
					targets: 'no-sort',
					orderable: false
				}],
				"dom": '<"top"f>rt<"bottom"ip>',
				"order": [
					[1, 'asc'],
					[2, 'asc'],
					[1, 'asc']
				],
				"language": {
					"paginate": {
						"previous": "<",
						"next": ">"
					}
				},
			});

			<?php
			if ($_SESSION['can_create'] == true) {
			?>
				$("<a href='#' class='btn btn-default' style='margin-bottom:5px' onclick='add_configautosent()'><i class='glyphicon glyphicon-plus-sign'></i> Create</a>").insertBefore('#table_configautosent');
			<?php
			}
			?>

			function hanyaAngka(evt) {
				var charCode = (evt.which) ? evt.which : event.keyCode
				if (charCode > 31 && (charCode < 48 || charCode > 57))
					return false;
				return true;
			}
		</script>