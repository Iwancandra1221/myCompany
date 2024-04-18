<link rel="stylesheet" href="<?php echo site_url('assets/font-awesome/css/font-awesome.min.css'); ?>">

<div class="container">
	<div class="row">
		<div class="page-title">ActivityLog</div>
		<div class="col-12">
			<!-- <form method="POST"> -->
				<div class="row">
					<div class="col-3">
						From<br>
						<input type="text" class="form-control" name="from" id="from" value="<?php echo $from; ?>" required>
					</div>
					<div class="col-3">
						Until<br>
						<input type="text" class="form-control" name="until" id="until" value="<?php echo $until; ?>" required>
					</div>
					<div class="col-3">
						Module<br>
						<select class="form-control" name="module" id="module">
							<option value="">ALL</option>
							<?php
								foreach ($module as $key => $m) {
									$selected='';
									if($moduleselect==$m->Module){
										$selected='selected';
									}
							?>
									<option value="<?php echo rtrim($m->Module); ?>" <?php echo $selected; ?>>
										<?php echo rtrim($m->Module); ?>
									</option>
							<?php
								}
							?>
						</select>
					</div>
					<div class="col-3"><br>
						<button class="btn btn-primary-dark">
							<i class="fa fa-search" aria-hidden="true"></i>
						</button>
					</div>
				</div>
			<!-- </form> -->
		</div>
		<div class="col-12">
			<table id="table" class="table table-striped table-bordered" cellspacing="0" width="100%" summary="Table Log Activity">
				<thead>
					<tr>
						<th id="Tanggal_Log">Tanggal Log</th>
						<th id="Module">Module</th>
						<th id="Activity_Log">Activity Log</th>
						<th id="Remarks">Remarks</th>
						<th id="Tanggal_Remarks">Tanggal Remarks</th>
						<th id="Lama_Proses">Lama Proses</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>

<script type="text/javascript">

	$(document).ready(function() {
		$('#from,#until,#module').change(function () {
	      	$("#table").dataTable().fnFilter(
	      		document.getElementById('from').value,0,
	      		document.getElementById('until').value,0,
	      	);

	      	$("#table").dataTable().fnFilter(
	      		document.getElementById('module').value,1,
	      	);
	    });

	    $('#table').dataTable( {
	        "bProcessing": true,
	        "bServerSide": true,
	         "columnDefs": [
		      { targets: 'no-sort', orderable: false },
		      { targets: 'col-hide', visible: false }
		      ],
	        "sAjaxSource": '<?php echo site_url('ActivityLog/DataList') ?>',
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
		    }
	    });


		$('#from,#until').datepicker({
	    	format: "yyyy-mm-dd",
	     	autoclose: true
	    });

	});
</script>