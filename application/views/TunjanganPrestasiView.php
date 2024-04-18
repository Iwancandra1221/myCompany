<div class="container" id="listTunjanganPrestasi">
	<div class="title">Tunjangan Prestasi</div>
	<br>
	<table id="table_tunjangan_prestasi" class="table table-striped table-bordered" cellspacing="0" style="font-size: 12px;" summary="Table Tunjangan Prestasi">
		<thead>
			<tr>
				<th id="Level_Salesman_tbl">Posisi Karyawan</th>
				<th id="Wilayah_Salesman_tbl">Cabang</th>
				<th id="Start_Date_tbl">Start Date</th>
				<th id="TP_Omzet_tbl">TP Omzet</th>
				<th id="TP_Omzet_Method_tbl">TP Omzet Method</th>
				<th id="TP_Omzet_Multiplier_tbl">TP Omzet Multiplier</th>
				<th id="TP_Omzet_Bobot_tbl">TP Omzet Bobot</th>
				<th id="TP_KPI_tbl">TP KPI</th>
				<th id="TP_KPI_Method_tbl">TP KPI Method</th>
				<th id="TP_KPI_Max_Percent_tbl">TP KPI Max Percent</th>
				<th id="TP_KPI_Multiplier_tbl">TP KPI Multiplier</th>
				<th id="TP_KPI_Bobot_tbl">TP KPI Bobot</th>
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
					<input type="hidden" name="proses" id="proses" class="form-control" readonly>
					<div class="col-md-3 col-sm-3 col-lg-2-10">
						Posisi Karyawan
					</div>
					<div class="col-md-9 col-sm-9 col-lg-2-10">
						<select name="Lvl_Salesman" id="Lvl_Salesman" class="form-control">
							<?php
							foreach ($empPosition as $key => $val) {
								echo "<option value='" . $val->PositionID . "'>" . $val->Name . "</option>";
							}
							?>
						</select>
					</div>
					<div class="col-md-3 col-sm-3 col-lg-2-10">
						Cabang
					</div>
					<div class="col-md-9 col-sm-9 col-lg-2-10">
						<select name="Wilayah_Salesman" id="Wilayah_Salesman" class="form-control">
							<?php
							foreach ($branch as $key => $val) {
								echo "<option value='" . $val->BranchCode . "'>" . $val->BranchName . "</option>";
							}
							?>
						</select>
					</div>
					<div class="col-md-3 col-sm-3 col-lg-2-10">
						Start Date
					</div>
					<div class="col-md-9 col-sm-9 col-lg-2-10">
						<input type="text" name="Start_Date" id="Start_Date" class="form-control">
					</div>
					<div class="col-6 p-0 m-0">
						<div class="col-md-12 col-sm-12 col-lg-2-10">
							<input type="checkbox" name="TP_Omzet" id="TP_Omzet" value="Y">
							<label>TP Omzet</label>
						</div>
						<div class="col-md-6 col-sm-6 col-lg-2-10">
							TP Omzet Method
						</div>
						<div class="col-md-6 col-sm-6 col-lg-2-10">
							<select name="TP_Omzet_Method" id="TP_Omzet_Method" class="form-control">
								<?php
								foreach ($omzet as $key => $val) {
									echo "<option value='" . $val->value . "'>" . $val->value . "</option>";
								}
								?>
							</select>
						</div>
						<div class="col-md-6 col-sm-5 col-lg-2-10">
							TP Omzet Multiplier
						</div>
						<div class="col-md-6 col-sm-7 col-lg-2-10">
							<input type="text" name="TP_Omzet_Multiplier" id="TP_Omzet_Multiplier" class="form-control" onkeypress="return hanyaAngka(event)">
						</div>
						<div class="col-md-6 col-sm-5 col-lg-2-10">
							TP Omzet Bobot
						</div>
						<div class="col-md-6 col-sm-7 col-lg-2-10">
							<input type="text" name="TP_Omzet_Bobot" id="TP_Omzet_Bobot" class="form-control" onkeypress="return hanyaAngka(event)">
						</div>
						<div class="col-md-12 col-sm-12 col-lg-2-10">
							<input type="checkbox" name="Skip_Pelunasan" id="Skip_Pelunasan" value="Y">
							<label>Skip Pelunasan</label>
						</div>
						<div class="col-md-12 col-sm-12 col-lg-2-10">
							<input type="checkbox" name="Potongan_Denda" id="Potongan_Denda" value="Y">
							<label>Potongan Denda</label>
						</div>
						<div class="col-md-12 col-sm-12 col-lg-2-10">
							<input type="checkbox" name="Pembayaran_Subsidi" id="Pembayaran_Subsidi" value="Y">
							<label>Pembayaran Subsidi Terpisah</label>
						</div>
					</div>
					<div class="col-6 p-0 m-0">
						<div class="col-md-12 col-sm-12 col-lg-2-10">
							<input type="checkbox" name="TP_KPI" id="TP_KPI" value="Y">
							<label>TP KPI</label>
						</div>
						<div class="col-md-6 col-sm-6 col-lg-2-10">
							TP KPI Method
						</div>
						<div class="col-md-6 col-sm-6 col-lg-2-10">
							<select name="TP_KPI_Method" id="TP_KPI_Method" class="form-control">
								<?php
								foreach ($kpi as $key => $val) {
									echo "<option value='" . $val->value . "'>" . $val->value . "</option>";
								}
								?>
							</select>
						</div>
						<div class="col-md-6 col-sm-6 col-lg-2-10">
							TP KPI Max Percent
						</div>
						<div class="col-md-6 col-sm-6 col-lg-2-10">
							<input type="text" name="TP_KPI_Max_Percent" id="TP_KPI_Max_Percent" class="form-control" onkeypress="return hanyaAngka(event)">
						</div>
						<div class="col-md-6 col-sm-6 col-lg-2-10">
							TP KPI Multiplier
						</div>
						<div class="col-md-6 col-sm-6 col-lg-2-10">
							<input type="text" name="TP_KPI_Multiplier" id="TP_KPI_Multiplier" class="form-control" onkeypress="return hanyaAngka(event)">
						</div>
						<div class="col-md-6 col-sm-6 col-lg-2-10">
							TP KPI Bobot
						</div>
						<div class="col-md-6 col-sm-6 col-lg-2-10">
							<input type="text" name="TP_KPI_Bobot" id="TP_KPI_Bobot" class="form-control" onkeypress="return hanyaAngka(event)">
						</div>
					</div>
				</div>
			</div>

			<div class="modal-footer">
				<?php
				if ($_SESSION['can_create'] == true || $_SESSION['can_update'] == true) {
				?>
					<button type="button" id="btnsave" class="btn btn-default" title="Save" style="font-weight: normal;" onclick="save()">
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

	$(document).ready(function() {

		$('#Start_Date').datetimepicker({
			format: 'd-M-Y',
			formatDate: 'd-M-Y',
			timepicker: false,
		});

	});

	table_tunjangan_prestasi = $('#table_tunjangan_prestasi').DataTable({
		"pageLength": 10,
		//"searching": true,
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
		//$_SESSION['can_create'] = true;
		if ($_SESSION['can_create'] == true) {
		?>
			$("<a href='#' class='btn btn-default' style='margin-bottom:5px' onclick='add()'><i class='glyphicon glyphicon-plus-sign'></i> Create</a>").insertBefore('#table_tunjangan_prestasi');
		<?php
		}
	?>

	<?php
	if ($_SESSION['can_create'] == true || $_SESSION['can_update'] == true) {
	?>

		function save() {

			let Omzet = document.getElementById('TP_Omzet').checked;
			Omzetx = Omzet ? 1 : 0;
			let SkipLunas = document.getElementById('Skip_Pelunasan').checked;
			SkipLunasx = SkipLunas ? 1 : 0;
			let PotDenda = document.getElementById('Potongan_Denda').checked;
			PotDendax = PotDenda ? 1 : 0;
			let ByrSubsidi = document.getElementById('Pembayaran_Subsidi').checked;
			ByrSubsidix = ByrSubsidi ? 1 : 0;
			let KPI = document.getElementById('TP_KPI').checked;
			KPIX = KPI ? 1 : 0;

			const data = {
				proses: document.getElementById('proses').value,
				Level_Slsman: document.getElementById('Lvl_Salesman').value,
				Wil_Slsman: document.getElementById('Wilayah_Salesman').value,
				Start_Date: document.getElementById('Start_Date').value,
				TP_Omzet: Omzetx,
				TP_Omzet_Method: document.getElementById('TP_Omzet_Method').value,
				TP_Omzet_Multiplier: document.getElementById('TP_Omzet_Multiplier').value,
				TP_Omzet_Bobot: document.getElementById('TP_Omzet_Bobot').value,
				SkipPelunasan: SkipLunasx,
				PotonganDenda: PotDendax,
				PembayaranSubsidi: ByrSubsidix,
				TP_KPI: KPIX,
				TP_KPI_Method: document.getElementById('TP_KPI_Method').value,
				TP_KPI_Max_Percent: document.getElementById('TP_KPI_Max_Percent').value,
				TP_KPI_Multiplier: document.getElementById('TP_KPI_Multiplier').value,
				TP_KPI_Bobot: document.getElementById('TP_KPI_Bobot').value,
			}

			//console.log(data);
			fetch('<?php echo $server; ?>TunjanganPrestasi/Save', {
					method: "POST",
					headers: {
						"Content-Type": "application/json"
					},
					body: JSON.stringify(data)
				})
				.then((res) => res.json())
				.then(() => $('#view').modal('hide'))
				.catch((err) => console.log(err))
				.finally(() => load_list())
		}

	<?php
	}
	?>



	function add() {
		$('#view').modal('show');

		document.getElementById('proses').value = 'new';
		//document.getElementById('Lvl_Salesman').innerHTML = '';
		//document.getElementById('Wilayah_Salesman').innerHTML = '';
		document.getElementById('Start_Date').value = '';
		document.getElementById('TP_Omzet').checked = true;
		//document.getElementById('TP_Omzet_Method').innerHTML = '';
		document.getElementById('TP_Omzet_Multiplier').value = 0;
		document.getElementById('TP_Omzet_Bobot').value = 0;
		document.getElementById('Skip_Pelunasan').value = '';
		document.getElementById('Potongan_Denda').value = '';
		document.getElementById('Pembayaran_Subsidi').value = '';
		document.getElementById('TP_KPI').checked = true;
		//document.getElementById('TP_KPI_Method').innerHTML = '';
		document.getElementById('TP_KPI_Max_Percent').value = 0;
		document.getElementById('TP_KPI_Multiplier').value = 0;
		document.getElementById('TP_KPI_Bobot').value = 0;

		document.getElementById('btnsave').disabled = false;
		enabled();
	}


	<?php
	if ($_SESSION['can_update'] == true) {
	?>

		function edit(a, b, c, d, e, f, g, h, i, j, k, l, m, n, o) {
			$('#view').modal('show');

			detail(a, b, c, d, e, f, g, h, i, j, k, l, m, n, o);

			enabled();

			document.getElementById('proses').value = 'edit';
			document.getElementById('Lvl_Salesman').disabled = true;
			document.getElementById('Wilayah_Salesman').disabled = true;
			document.getElementById('btnsave').disabled = false;
		}
	<?php
	}
	if ($_SESSION['can_delete'] == true) {
	?>

		function deleted(a, b) {
			if (confirm("Apakah anda yakin ingin menghapus " + a) == true) {
				const data = {
					proses: 'delete',
					Level_Slsman: a,
					Wil_Slsman: b,
					Start_Date: '',
					TP_Omzet: '',
					TP_Omzet_Method: '',
					TP_Omzet_Multiplier: '',
					TP_Omzet_Bobot: '',
					SkipPelunasan: '',
					PotonganDenda: '',
					PembayaranSubsidi: '',
					TP_KPI: '',
					TP_KPI_Method: '',
					TP_KPI_Max_Percent: '',
					TP_KPI_Multiplier: '',
					TP_KPI_Bobot: '',
				}

				fetch('<?php echo $server; ?>TunjanganPrestasi/Save', {
						method: "POST",
						headers: {
							"Content-Type": "application/json"
						},
						body: JSON.stringify(data)
					})
					.then((res) => res.json())
					.then(() => $('#view').modal('hide'))
					.catch((err) => console.log(err))
					.finally(() => load_list())
			}
		}
	<?php
	}
	?>

	function view(a, b, c, d, e, f, g, h, i, j, k, l, m, n, o) {

		$('#view').modal('show');

		detail(a, b, c, d, e, f, g, h, i, j, k, l, m, n, o);

		disabled();

		document.getElementById('proses').value = 'view';
		document.getElementById('btnsave').disabled = true;
	}

	function detail(a, b, c, d, e, f, g, h, i, j, k, l, m, n, o) {
		document.getElementById('Lvl_Salesman').innerHTML = '<option value="' + a + '">' + a + '</option>';
		document.getElementById('Wilayah_Salesman').innerHTML = '<option value="' + b + '">' + b + '</option>';
		document.getElementById('Start_Date').value = c;
		d == 1 ? document.getElementById('TP_Omzet').checked = true : document.getElementById('TP_Omzet').checked = false;
		document.getElementById('TP_Omzet_Method').innerHTML = '<option value="' + e + '">' + e + '</option>';
		document.getElementById('TP_Omzet_Multiplier').value = f;
		document.getElementById('TP_Omzet_Bobot').value = g;
		h == 1 ? document.getElementById('TP_KPI').checked = true : document.getElementById('TP_KPI').checked = false;
		document.getElementById('TP_KPI_Method').innerHTML = '<option value="' + i + '">' + i + '</option>';
		document.getElementById('TP_KPI_Max_Percent').value = j;
		document.getElementById('TP_KPI_Multiplier').value = k;
		document.getElementById('TP_KPI_Bobot').value = l;
		m == 1 ? document.getElementById('Skip_Pelunasan').checked = true : document.getElementById('Skip_Pelunasan').checked = false;
		n == 1 ? document.getElementById('Potongan_Denda').checked = true : document.getElementById('Potongan_Denda').checked = false;
		o == 1 ? document.getElementById('Pembayaran_Subsidi').checked = true : document.getElementById('Pembayaran_Subsidi').checked = false;
	}


	function disabled() {
		document.getElementById('Lvl_Salesman').disabled = true;
		document.getElementById('Wilayah_Salesman').disabled = true;
		document.getElementById('Start_Date').readOnly = true;
		document.getElementById('TP_Omzet').disabled = true;
		document.getElementById('TP_Omzet_Method').readOnly = true;
		document.getElementById('TP_Omzet_Multiplier').readOnly = true;
		document.getElementById('TP_Omzet_Bobot').readOnly = true;
		document.getElementById('Skip_Pelunasan').disabled = true;
		document.getElementById('Potongan_Denda').disabled = true;
		document.getElementById('Pembayaran_Subsidi').disabled = true;
		document.getElementById('TP_KPI').disabled = true;
		document.getElementById('TP_KPI_Method').readOnly = true;
		document.getElementById('TP_KPI_Max_Percent').readOnly = true;
		document.getElementById('TP_KPI_Multiplier').readOnly = true;
		document.getElementById('TP_KPI_Bobot').readOnly = true;
	}

	function enabled() {
		document.getElementById('Lvl_Salesman').disabled = false;
		document.getElementById('Wilayah_Salesman').disabled = false;
		document.getElementById('Start_Date').readOnly = false;
		document.getElementById('TP_Omzet').disabled = false;
		document.getElementById('TP_Omzet_Method').readOnly = false;
		document.getElementById('TP_Omzet_Multiplier').readOnly = false;
		document.getElementById('TP_Omzet_Bobot').readOnly = false;
		document.getElementById('Skip_Pelunasan').disabled = false;
		document.getElementById('Potongan_Denda').disabled = false;
		document.getElementById('Pembayaran_Subsidi').disabled = false;
		document.getElementById('TP_KPI').disabled = false;
		document.getElementById('TP_KPI_Method').readOnly = false;
		document.getElementById('TP_KPI_Max_Percent').readOnly = false;
		document.getElementById('TP_KPI_Multiplier').readOnly = false;
		document.getElementById('TP_KPI_Bobot').readOnly = false;
	}

	function load_list() {
		table_tunjangan_prestasi.clear().draw();
		$.ajax({
			type: 'POST',
			url: '<?php echo $server; ?>TunjanganPrestasi/View',
			dataType: 'json',
			success: function(data) {
				var html = '';
				for (x = 0; x < data.length; x++) {
					var rows = [];
					rows[0] = data[x].EmpPositionID;
					rows[1] = data[x].BranchCode;
					rows[2] = data[x].StartDate;
					rows[3] = data[x].TPOmzet;
					rows[4] = data[x].TPOmzetMethod;
					rows[5] = data[x].TPOmzetMultiplier;
					rows[6] = data[x].TPOmzetBobot;
					rows[7] = data[x].TPKPI;
					rows[8] = data[x].TPKPIMethod;
					rows[9] = data[x].TPKPIMaxPercent;
					rows[10] = data[x].TPKPIMultiplier;
					rows[11] = data[x].TPKPIBobot;

					var aksi = '';

					var action = "'" + data[x].EmpPositionID + "','" + data[x].BranchCode + "','" + data[x].StartDate + "','" + data[x].TPOmzet + "','" + data[x].TPOmzetMethod + "','" + data[x].TPOmzetMultiplier + "','" + data[x].TPOmzetBobot + "','" + data[x].TPKPI + "','" + data[x].TPKPIMethod + "','" + data[x].TPKPIMaxPercent + "','" + data[x].TPKPIMultiplier + "','" + data[x].TPKPIBobot + "','" + data[x].SkipPelunasan + "','" + data[x].PotonganDenda + "','" + data[x].PembayaranSubsidi + "'";
					console.log(action);
					<?php
					//$_SESSION["can_read"] = true;
					if ($_SESSION['can_read'] == true) {
					?>
						aksi += '<button class="btn btn-sm btn-default" onclick="view(' + action + ')" title="View"><i class="glyphicon glyphicon-search"></i></button> ';
					<?php
					}
					//$_SESSION["can_update"] = true;
					if ($_SESSION['can_update'] == true) {
					?>
						aksi += '<button class="btn btn-sm btn-default" onclick="edit(' + action + ')" title="Edit"><i class="glyphicon glyphicon-pencil"></i></button> ';
					<?php
					}
					//$_SESSION["can_delete"] = true;
					if ($_SESSION['can_delete'] == true) {
					?>
						aksi += '<button class="btn btn-sm btn-default" onclick="deleted(' + action + ')" title="Delete"><i class="glyphicon glyphicon-trash"></i></button> ';
					<?php
					}
					?>

					rows[12] = aksi;

					table_tunjangan_prestasi.row.add(rows);
				}
				table_tunjangan_prestasi.draw();
			}
		});
	}

	function hanyaAngka(evt) {
		var charCode = (evt.which) ? evt.which : event.keyCode
		if (charCode > 31 && (charCode < 48 || charCode > 57))
			return false;
		return true;
	}

	load_list();
</script>