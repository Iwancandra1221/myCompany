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
	function listLog(){
		$('.loading').show();
		var branch = $('#branch').val();
		var dp1 = $('#dp1').val();
		var dp2 = $('#dp2').val();
		var status = $('#status').val();
		var search = $('#search').val();
		tableLog.clear().draw();
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url("Log/GetWhatsappLog") ?>',
			data:{ branch:branch, dp1:dp1, dp2:dp2, status:status, search:search },
			cache: false,
			dataType: 'json',
			success: function (response){
				// console.log(response);
				for(i=0;i<response.length;i++){
					var list = response[i];
					var status = '';
					if(list.Status=='SUKSES'){
						status = '<span class="label label-success">'+list.Status+'</span>';
					}
					else if(list.Status=='PENDING'){
						status = '<span class="label label-warning">'+list.Status+'</span>';
					}
					else if(list.Status=='GAGAL'){
						status = '<span class="label label-danger">'+list.Status+'</span>';
					}
					else{
						status = '<span class="label label-danger">'+list.Status+'</span>';
					}
					
					tableLog.row.add([i, list.BranchId, list.LogDate, list.Tanggal, list.PhoneNo, list.TanggalTerkirim, status,'<button type="button" class="btn btn-sm btn-default" onclick="javascript:viewLog('+list.LogId+')"><i class="glyphicon glyphicon-search"></i></button>']);
				}
				tableLog.draw();
				$('.loading').hide();
			}
		});
	}
	
	function viewLog(id){
		$('.loading').show();
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo site_url("Log/GetWhatsappLogDetail?id='+id+'") ?>',
			dataType: 'json',
			success: function (data) {
				$('.loading').hide();
				if(data.LogId>0){
					var msg = JSON.parse(data.MsgParam);
					// alert(msg.body);
					$('#view_logdate').html(data.LogDate);
					$('#view_phoneno').html(data.PhoneNo);
					$('#view_tanggalterkirim').html(data.TanggalTerkirim);
					$('#view_body').html(msgToDisplay(msg.body));
					
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
	
	function msgToDisplay(str){
		String.prototype.replaceAt = function(index, replacement) {
			return this.substring(0, index) + replacement + this.substring(index + 1); //1=replacement.length
		}
		str = str.replace(/\n/g, "<br />")
		
		var count = 0;
		for (var i = 0; i < str.length; i++) {
			if (str[i] === "*") {
				var index = i;
				var spanHtml = '';
				if (count % 2 == 0) {
					spanHtml = "<b>"
				} else {
					spanHtml = "</b>";
				}
				count++;
				str = str.replaceAt(index, spanHtml);
				i+= spanHtml.length -1; // correct position to account for the replacement
			}
		}
		return str;
	}
	
	$(document).ready(function() {
	    tableLog = $('#tableLog').DataTable({
			"pageLength"    : 10,
			"searching"     : true,
			 "autoWidth": false,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false }
			],
			"dom": '<"top">rt<"bottom"ip><"clear">',
			"order": [[3, 'desc']],
		});
		
		tableLog.on('order.dt search.dt', function () {
			let i = 1;
			tableLog.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
				this.data(i++);
			});
		}).draw();
		
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
	
	listLog();
	});
	
</script>
	
<!-- Fixed navbar -->

<div class="container">
	<div class="title">Log Whatsapp</div>
	<div class="row" style="margin-bottom:10px">
		<div class="col-6">
			<table style="padding:5px;border-spacing:5px;border-collapse:separate;" width="100%">
				<tr>
					<th scope="col"></th>
				</tr>
				<tr>
					<td>BRANCH</td>
					<td>
						<select id="branch">
						<option value="">ALL</option>
						<?php
							foreach($branches as $branch){
								echo "<option value='".$branch->BranchID."'>".$branch->BranchName."</option>";
							}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td>PERIODE</td>
					<td>
						<input type="text" id="dp1" style="width:120px" value="<?php echo date("d-M-Y", strtotime("-7 days")) ?>" autocomplete="off">
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
						<button type="button" class="btn btn-dark-sm btn-dark" style="width:120px" onclick="javascript:listLog()">VIEW LOG</button>
					</td>
				</tr>
			</table>
			
		</div>
	</div>
	
	
    <table id="tableLog" class="table table-striped table-bordered" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th scope="col" style="width:2%" class="no-sort">No</th>
				<th scope="col" style="width:10%">BranchID</th>
				<th scope="col" style="width:0" class="col-hide">LogDate</th>
				<th scope="col" style="width:15%">TANGGAL KIRIM</th>
				<th scope="col" style="width:20%">TO</th>
				<th scope="col" style="width:20%">TERKIRIM</th>
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
					<h4 class="modal-title" style="text-align: center;"><strong>WHATSAPP</strong></h4>
				</div>
				<div class="modal-body">
					<table class="table table-bordered" style="margin-bottom:10px" width="100%">
						<tr><th scope="col"></th></tr>
						<tr><td width="150px">STATUS</td><td id="view_status" class="font-bold"></td></tr>
						<tr><td>TANGGAL KIRIM</td><td id="view_logdate" class="font-bold"></td></tr>
						<tr><td>CABANG</td><td id="view_branch" class="font-bold"></td></tr>
						<tr><td>PHONE NUMBER</td><td id="view_phoneno" class="font-bold"></td></tr>
						<tr><td>TANGGAL TERKIRIM</td><td id="view_tanggalterkirim" class="font-bold"></td></tr>
					</table>
					<div class="col-12" id="view_body" style="padding:5px;border:1px solid #000"></div>
					<div style="clear:both"></div>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
</div>

<script type="text/javascript">
</script>
