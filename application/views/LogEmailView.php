<?php
	if(!isset($_SESSION['logged_in'])){
		redirect('main','refresh');
	}
?>
<style>
	.font-bold{
		font-weight:bold;	
	}
</style>
<script>
	var tableLog;
	
	function filterLog(){
		
		tableLog.column(0).search($('#branch').val());
		tableLog.column(6).search($('#status').val());
		// tableLog.columns([2,3,4]).search($('#search').val());
		tableLog.search($('#search').val());
		tableLog.draw();
	}
	
	function viewLog(id){
		$('.loading').show();
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo site_url("Log/GetEmailLogDetail?id='+id+'") ?>',
			dataType: 'json',
			success: function (data) {
				$('.loading').hide();
				// alert(data);
				if(data.LogId>0){
					$('#view_logdate').html(data.LogDate);
					$('#view_paramto').html(saringText(data.ParamTo));
					$('#view_paramcc').html(saringText(data.ParamCc));
					$('#view_paramsubject').html(data.ParamSubject);
					$('#view_parambody').html(removeHref(data.ParamBody));
					
					var status = '';
					if(data.Status=='SUKSES'){
						status = '<span class="label label-success">'+data.Status+'</span>';
					}
					else if(data.Status=='PENDING'){
						status = '<span class="label label-warning">'+data.Status+'</span>';
					}
					else if(data.Status=='GAGAL'){
						status = '<span class="label label-danger">'+data.Status+'</span>';
					}
					else{
						status = '<span class="label label-danger">'+data.Status+'</span>';
					}
					$('#view_logdate').html(data.Tanggal);
					$('#view_branch').html(data.BranchId);
					$('#view_status').html(status);
					
					$('#modal_view').modal('show');
				}
			}
		});
	}
	
	function saringText(txt){
		var newTxt = txt.replace(/([[])/g, '');
		newTxt = newTxt.replace(/(])/g, '');
		newTxt = newTxt.replace(/(")/g, '');
		newTxt = newTxt.replace(/,/g, '<br>');
		return newTxt;
	}
	
	function removeHref(txt){
		var newTxt = txt.replace(/([[])/g, '');
		newTxt = newTxt.replace(/href/g, 'title');
		return newTxt;
	}
	$(document).ready(function() {
		
		tableLog = $('#tableLog').DataTable({
			"pageLength": 10,
			"lengthMenu": [
			[5, 10, 20, 50, 100, -1],
			[5, 10, 20, 50, 100, "All"]
			],
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false }
			],
			"order": [[2, 'desc']],
			"dom": '<"top"l>rt<"bottom"ip><"clear">',
			"processing": true,
			"serverSide": true,
			"autoWidth": false,
			"ajax": {
				"url": '<?php echo site_url('Log/GetEmailLog') ?>',
				"type": "GET",
				"datatype": "json",
				"data": function (data) {
					var startDate = $('#dp1').val();
					var endDate = $('#dp2').val();
					data.startDate = startDate;
					data.endDate = endDate;
				}
			},
			"language": {
				"lengthMenu": "Menampilkan _MENU_ Data per halaman",
				"zeroRecords": "Maaf, Data tidak ada",
				"info": "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
				"infoEmpty": "Menampilkan 0 s/d 0 dari 0 data",
				"search": "Pencarian",
				"infoFiltered": "",
				"paginate": {
					"sPrevious": "Sebelumnya",
					"sNext": "Berikutnya"
				}
			},
		});
		
		
		
		$('#modal_view').on('hidden.bs.modal', function () {
		  $('#view_parambody').html('');
		})

	});
	
    $(document).ready(function() {
		$('#dp1').datepicker({
			format: "dd-M-yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var StartDt = $('#dp1').datepicker('getDate');
			$('#dp2').datepicker("setStartDate", StartDt);
		});
		
		$('#dp2').datepicker({
			format: "dd-M-yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var EndDt = $('#dp2').datepicker('getDate');
			$('#dp1').datepicker("setEndDate", EndDt);
		});
	
	// listLog();
	});
	
</script>
	
<!-- Fixed navbar -->

<div class="container">
	<div class="title">Log Email</div>
	<div class="row" style="margin-bottom:10px">
		<div class="col-6">
			<table style="padding:5px;border-spacing:5px;border-collapse:separate;" width="100%">
				<tr>
					<td>BRANCH</td>
					<td>
						<select id="branch">
						<option value="">ALL</option>
						<?php
							foreach($branches as $branch){
								echo "<option value='".$branch->BranchCode."'>".$branch->BranchName."</option>";
							}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td>PERIODE</td>
					<td>
						<input type="text" id="dp1" style="width:120px" value="<?php echo date("d-M-Y", strtotime("-1 days")) ?>" autocomplete="off">
						sd 
						<input type="text" id="dp2" style="width:120px" value="<?php echo date("d-M-Y") ?>" autocomplete="off">
					</td>
				</tr>
				<tr>
					<td>STATUS</td>
					<td>
						<select id="status" style="width:120px">
						<option value="">ALL</option>
						<option value="SUKSES">SUKSES</option>
						<option value="PENDING">PENDING</option>
						<option value="GAGAL">GAGAL</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>SEARCH</td>
					<td>
						<input type="text" id="search" style="width:100%">
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<button type="button" class="btn btn-dark-sm btn-dark" style="width:120px" onclick="javascript:filterLog()">VIEW LOG</button>
					</td>
				</tr>
			</table>
			
		</div>
	</div>
	
	
    <table id="tableLog" class="table table-striped table-bordered" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th scope="col" style="width:2%" class="no-sort">No</th>
				<th scope="col" style="width:0" class="col-hide">BranchID</th>
				<th scope="col" style="width:15%">TANGGAL KIRIM</th>
				<th scope="col" style="width:20%">TO</th>
				<th scope="col" style="width:20%">CC</th>
				<th scope="col" style="width:*">SUBJECT</th>
				<th scope="col" style="width:10%">STATUS</th>
				<th scope="col" style="width:5%" class="no-sort">VIEW</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
	
	<div id="modal_view" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" style="text-align: center;"><strong>EMAIL</strong></h4>
				</div>
				<div class="modal-body">
					<table class="table table-bordered" style="margin-bottom:10px" width="100%">
						<tr><td width="150px">STATUS</td><td id="view_status" class="font-bold"></td></tr>
						<tr><td>TANGGAL KIRIM</td><td id="view_logdate" class="font-bold"></td></tr>
						<tr><td>CABANG</td><td id="view_branch" class="font-bold"></td></tr>
						<tr><td>TO</td><td id="view_paramto" class="font-bold"></td></tr>
						<tr><td>CC</td><td id="view_paramcc" class="font-bold"></td></tr>
						<tr><td>SUBJECT</td><td id="view_paramsubject" class="font-bold"></td></tr>
					</table>
						<div class="col-12" id="view_parambody" style="padding:5px; border:1px solid #000;overflow-x: scroll">
						</div>
					<div style="clear:both"></div>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
</div>

<script type="text/javascript">
</script>
