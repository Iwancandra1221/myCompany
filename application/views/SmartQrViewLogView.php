<style>
	table{
	font-size:12px;
	}
	
	.table tr{
	color:black;
	font-style: normal;
	text-align:left;
	}
	
	.table tr:nth-child(even) {background-color: #f8f8f8;}
	
	.table tr td{
		padding:2px;
	}
	.row{
		margin-bottom: 0px;
	}
</style>

<div class="container">
	<big><center>View Log</center></big>
	<button class="btn" title="tombol filter log" style="float: right;" id="btn-filter"><span class="glyphicon glyphicon-filter"></span></button>
	<div style="clear: both;"></div>
	<small>
		<div style="overflow-y: hidden;overflow-x:scroll">
			<table id="myTable" class="table table-bordered">
				<thead>
					<tr>
						<th>LOG DATE</th>
						<th>TIPE</th>
						<th>MERK</th>
						<th>LOKASI QR CODE</th>
						<th>IS GROUP</th>
						<th>VERSI</th>
						<th>RESULT</th>
						<th>AKSI</th>
						
					</tr>
				</thead>
				<tbody>
					<?php
					if($dataLog!=null){
						foreach($dataLog as $key => $value){
							$no = ($key+1);
							//$id = $value['id'];
							$isgroup = $value['isgroup'] == 1 ? 'True' : 'False' ;
							$LogDate = $value['LogDate'];
							$LogId = $value['LogId'];
							$lokasi_qr_code = $value['lokasi_qr_code'];
							$merk = $value['merk'];
							$result = $value['result'];
							$tipe = $value['tipe'];
							$url = $value['url'];
							$url_landing_page = $value['url_landing_page'];
							$ver = $value['ver'];
							echo <<<HTML
							<tr>
								<td>{$LogDate}</td>
								<td>{$tipe}</td>
								<td>{$merk}</td>
								<td>{$lokasi_qr_code}</td>
								<td>{$isgroup}</td>
								<td>{$ver}</td>
								<td>{$result}</td>
								<td></td>
							</tr>
HTML;
						}
					}
					?>
				</tbody>
			</table>
		</div>
	</small>
</div>

<div class="modal" tabindex="-1" role="dialog" id="m-filter">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Filter Log</h5>
			
				<button style="float: right;color: white !important;position: absolute;top: 8px;right: 10px;" type="button" class="btn btn-secondary" data-dismiss="modal" id="btm-filter-close">x</button>
			</div>
			<div class="modal-body">
				<form action="" method="POST" id="f-filter">
					
					<div class="form-group row">
						<div class="col-3">Merk</div>
						<div class="col-9">
							<select class="form-control" name="merk">
								<option value=''>ALL</option>
								<?php
								if($getMerk!=null){
									foreach($getMerk as $key => $value){
										echo "<option value='".$value['Merk']."'>".$value['Merk']."</option>";
									}
								}
								?>
							</select>
						</div>
					</div>
					<div class="form-group row">
						<div class="col-3">Lokasi QR Code</div>
						<div class="col-9">
							<input type="text" name="lokasi_qr_code" class="form-control">
						</div>
					</div>
					<div class="form-group row">
						<div class="col-3">Periode</div>
						<div class="col-4">
							<input type="text" name="log_date_start" class="datepicker form-control">
						</div>
						<div class="col-1">s/d</div>
						<div class="col-4">
							<input type="text" name="log_date_end" class="datepicker form-control">
						</div>
					</div>
					<div class="form-group row">
						<div class="col-3">Scan Result</div>
						<div class="col-9">
							<select name="scan_result" class="form-control">
								<option value="">ALL</option>
								<option value="SUCCESS">SUCCESS</option>
								<option value="FAILED">FAILED</option>
							</select>
						</div>
					</div>
					<div class="form-group row">
						<div class="col-3">Tipe</div>
						<div class="col-9">
							<input type="text" name="tipe" class="form-control">
						</div>
					</div>
					<div class="form-group row">
						<div class="col-12">
							<input type="submit" name="submit" class="btn" style="float: right;">
						</div>
					</div>
				</form>
			</div>
			
		</div>
	</div>
</div>
<script>
	$(document).ready( function () {
		$('#myTable').DataTable();
	});
	$("#btn-filter").click(function(){
		$("#m-filter").show();
	});
	$("#btm-filter-close").click(function(){
		$("#m-filter").hide();
	});
	// $('#f-filter input[name="log_date_start"]').datepicker({
	// 	altFormat: "dd-M-yy",
	// });
	// $('#f-filter input[name="log_date_end"]').datepicker({
	// 	altFormat: "dd-M-yy",
	// });
</script>
