<script>
</script>  

<style>
</style>
<div class="container">
	<div class="page-title">SHOPBOARD</div>
	
	<!--div class="fs-8 fw-bold">Menunggu Approval - Total <span id="span_all_data" class="fw-bold">0</span> Toko</div>
	<div class="row px5 mb0">
		
		<div class="col-6">
			<span id="span_selected_data" class="fs-8 fw-bold">0</span> <span class="fs-8"> Toko Selected</span>
		</div>
		<div class="col-6 text-right">
			<button class="btn btn-danger-dark p10" onclick="reject()">
				<i class='glyphicon glyphicon-remove'></i> Reject
			</button>
			
			<button class="btn btn-primary-dark p10" onclick="approve()">
				<i class='glyphicon glyphicon-ok'></i> Approved
			</button>
		</div>
	</div-->
		<div class="row px5 mb0">
			<div class="col-6">
				<span class="fs-6" style="margin-right:20px">
					<input type="checkbox" id="chk_unprocessed" class="chk_show" value="WAITING FOR APPROVAL" checked> <label for="chk_unprocessed" class="fw-normal cs-pointer">UNPROCESSED</label>
				</span>
				<span class="fs-6" style="margin-right:20px">
					<input type="checkbox" id="chk_approved" class="chk_show" value="APPROVED"> <label for="chk_approved" class="fw-normal cs-pointer">APPROVED</label>
				</span>
				<span class="fs-6">
					<input type="checkbox" id="chk_rejected" class="chk_show" value="REJECTED"> <label for="chk_rejected" class="fw-normal cs-pointer">REJECTED</label>
				</span>
			</div>
			<div class="col-6 text-right">
				<button class="btn btn-danger-dark p10" onclick="reject()">
					<i class='glyphicon glyphicon-remove'></i> Reject
				</button>
				
				<button class="btn btn-primary-dark p10" onclick="approve()">
					<i class='glyphicon glyphicon-ok'></i> Approve
				</button>
			</div>
		</div>
		<div class="row px5 mb0">
			<div class="col-6">
				<span class="fs-8">Total <b><span id="span_all_data">0</span></b> Data</span>
			</div>
			<div class="col-6 text-right fs-8">
				<span id="span_selected_data" class="fw-bold">0</span> Selected Data
			</div>
		</div>
	
	
	<form id="form_approval" action="<?php echo base_url().'ShopboardApproval/approval' ?>" method="POST">
		<input type="hidden" name="act" id="act" value="">
		<input type="hidden" name="rejected_note" id="rejected_note" value="">
		<table id="table_shopboard" class="table table-striped table-bordered" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>Supplier</th>
					<th>Cabang</th>
					<th>Wilayah</th>
					<th>Nama Toko</th>
					<th>Alamat</th>
					<th>Kota</th>
					<th>No PO</th>
					<th>Merk</th>
					<th>Ukuran Shopboard</th>
					<th>Tgl Expired</th>
					<th></th>
					<th>Select All</th>
				</tr>
				<tr>
					<th><input type="text" class="form-control shopboard_search" data-col="0" placeholder="Cari..."></th>
					<th></th>
					<th></th>
					<th><input type="text" class="form-control shopboard_search" data-col="2" placeholder="Cari..."></th>
					<th><input type="text" class="form-control shopboard_search" data-col="3" placeholder="Cari..."></th>
					<th><input type="text" class="form-control shopboard_search" data-col="4" placeholder="Cari..."></th>
					<th><input type="text" class="form-control shopboard_search" data-col="5" placeholder="Cari..."></th>
					<th><input type="text" class="form-control shopboard_search" data-col="6" placeholder="Cari..."></th>
					<th><input type="text" class="form-control shopboard_search" data-col="7" placeholder="Cari..."></th>
					<th></th>
					<th></th>
					<th><center><input type="checkbox" id="chk_all"></center></th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</form>
	
</div> <!-- /container -->


<div class="modal fade" id="modal_view" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form class="form-horizontal">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" class="fs-6" style="color:#000">&times;</span></button>
					<h4 class="modal-title fs-5 fw-bold">History PO Shopboard</h4>
				</div>
				
				
				<div class="modal-body">
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="confirm-approve" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title fs-5 fw-bold">Approve</h4>
			</div>
			<div class="modal-body">
				<p class="fs-6">Apakah Anda yakin ingin menyetujui pengajuan PO perpanjangan ini?</p>
			</div>
			<div class="modal-footer">
				<div class="row">
					<div class="col-6">
						<button type="button" class="btn btn-light-dark w100" data-dismiss="modal">Cancel</button>
					</div>
					<div class="col-6">
						<button type="button" id="btn_approve" class="btn btn-primary-dark w100">Approve</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="confirm-reject" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title fs-5 fw-bold">Reject</h4>
			</div>
			<div class="modal-body">
				<p class="fs-6">Apakah Anda yakin ingin menolak pengajuan PO perpanjangan ini?</p>
				<br>
				<p class="fs-4">Alasan Reject:</p>
				<textarea id="confim_rejected_note" class="form-control"></textarea>
				
				
			</div>
			<div class="modal-footer">
				<div class="row">
					<div class="col-6">
						<button type="button" class="btn btn-light-dark w100" data-dismiss="modal">Cancel</button>
					</div>
					<div class="col-6">
						<button type="button" id="btn_reject" class="btn btn-danger-dark w100">Reject</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	var table_shopboard;
	var selected_status = 'WAITING FOR APPROVAL';
	$(document).ready(function() {
		
		$('#chk_all').click(function(event){
			$('.chk_pilih').prop('checked', $(this).is(":checked"));
			var checked = $('.chk_pilih:checked').length;
			$('#span_selected_data').text(checked);
		});
		
		
		$('.chk_show').click(function(event){
			var id = this.id;
			$('.chk_show').prop('checked', false);
			$(this).prop('checked', true);
			selected_status = $('#'+id).val();
			console.log(selected_status);
			table_shopboard.ajax.reload();
		});
		
		
		
		$('.shopboard_search').keyup(function(e){
			var col = $(this).attr('data-col');
			if(e.keyCode == 13) {
				table_shopboard.columns(col).search(this.value).draw();   
			}
		});
		// $('.shopboard_search').blur(function(e){
			// var col = $(this).attr('data-col');
			// table_shopboard.columns(col).search(this.value).draw();  
		// });
		
		
		table_shopboard = $('#table_shopboard').DataTable({
			"pageLength"    : 10,
			"searching"     : true,
			"lengthChange"  : false,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false }
			],
			"order": [9, 'asc'],
			"autoWidth": false,
			"processing": true,
			"ordering": false,
			"serverSide": true,
			"ajax": {
				"url": '<?php echo base_url('shopboard/datatable_shopboard_approval') ?>',
				"type": "GET",
				"datatype": "json",
				"data": function (data) {
					data.cabang = '<?php echo $_SESSION['logged_in']['branch_id'] ?>';
					data.status = selected_status;
				}
			},
			"initComplete": function() {
				$('#table_shopboard_filter input').unbind();
				$('#table_shopboard_filter input').bind('keyup', function(e) {
					if(e.keyCode == 13) {
						table_shopboard.search(this.value).draw();   
					}
				}); 
			},
			"drawCallback": function( settings) {  
				$('#span_all_data').text(table_shopboard.page.info().recordsTotal);
			},
			"dom": '<"top">rt<"text-center"p><"clear">',
			"pagingType": 'full_numbers',
			"language": {
				"paginate": {
					"first": "&#10094;&#10094;",
					"previous": "&#10094;",
					"next": "&#10095;",
					"last": "&#10095;&#10095;"
				}
			}
		});
		
		$('#btn_approve').click(function(e){
			$('#act').val('APPROVED');
			$('#rejected_note').val('');
			$('#form_approval').submit();
		});
		
		$('#btn_reject').click(function(e){
			var rejected_note = $('#confim_rejected_note').val();
			if(rejected_note==''){
				alert('Alasan reject wajib diisi!');
			}
			else{
				$('#act').val('REJECTED');
				$('#rejected_note').val(rejected_note);
				$('#form_approval').submit();
			}
		});
		
		$("#form_approval").submit(function(event) {
			var act = $(this).attr('action');
			var form_data = new FormData(this);
				$('.loading').show();
				$.ajax({
					data      	: form_data,
					url			: act,
					cache		: false,
					contentType	: false,
					processData	: false,
					type		: 'POST',
					dataType  	: 'json',
					success   	: function(res) {
						// console.log(res);
						$('.loading').hide();
						if(res.result=='success'){
							table_shopboard.ajax.reload();
							$('#confirm-approve').modal('hide');
							$('#confirm-reject').modal('hide');
						}
						else{
							alert(res.result+'\n'+res.msg.replace(/\\n/g,"\n"));
						}
						
						
					}
				});
			event.preventDefault();
		});
		
	});
	
	$(document).on("click", ".chk_pilih" , function() {		
		var checked = $('.chk_pilih:checked').length;
		if(checked>0){
			$('#chk_all').prop('checked', true);
		}
		else{
			$('#chk_all').prop('checked', false);
		}
		$('#span_selected_data').text(checked);
	});
	
	function reject(){
		var checked = $('.chk_pilih:checked').length;
		if(checked>0){
			$('#confirm-reject').modal('show');
		}
	}
	
	function approve(){
		var checked = $('.chk_pilih:checked').length;
		if(checked>0){
			$('#confirm-approve').modal('show');
		}
	}
	
	function view_data(id){
		$('.loading').show();
		$.ajax({
			url			: '<?php echo base_url() ?>shopboard/detail/'+id,
			cache		: false,
			contentType	: false,
			processData	: false,
			type		: 'GET',
			dataType  	: 'json',
			success   	: function(res) {
				console.log(res);
				$('.loading').hide();
					var html = '';
					
					html +=''+
					'</div>'+
					'<table class="w100 mb10" style="border-collapse: separate; border-spacing: 0px 5px;">'+
						'<tr><td width="35%">Cabang</td><td width="65%">: <b>'+res.header.cabang+'</b></td></tr>'+
						'<tr><td>Wilayah</td><td width="65%">: <b>'+res.header.wilayah+'</b></td></tr>'+
						'<tr><td>Nama Toko</td><td>: <b>'+res.header.nama_toko+'</b></td></tr>'+
						'<tr><td>Alamat</td><td>: <b>'+res.header.alamat+'</b> </td></tr>'+
						'<tr><td>Kota</td><td>: <b>'+res.header.kota+'</b> </td></tr>'+
					'</table>'+
					'<span class="fs-4 fw-bold color-primary">'+res.detail[0].no_po+'</span> '+
					'<table class="w100 mb10" style="border-collapse: separate; border-spacing: 0px 5px;">'+
						'<tr><td width="35%">Status Perpanjangan</td><td width="65%">: <b>'+res.detail[0].status+'</b> on '+date('d-M-Y H:i',strtotime(res.detail[0].status_date))+'</td></tr>';
						if(res.detail[0].final_status){
						html +='<tr><td width="35%"></td><td width="65%">: <b>'+res.detail[0].final_status+'</b> on '+date('d-M-Y H:i',strtotime(res.detail[0].final_date))+'</td></tr>';
						}
						html +='<tr><td>Periode Pajak Reklame</td><td>: <b>'+date('d-M-Y',strtotime(res.detail[0].periode_start))+'</b> sd <b>'+date('d-M-Y',strtotime(res.detail[0].periode_end))+'</b></td></tr>'+
						'<tr><td>Supplier</td><td>: <b>'+res.detail[0].no_po+'</b> </td></tr>'+
						'<tr><td>Merk & Ukuran</td><td>: <b>'+res.detail[0].merk1+', '+res.detail[0].ukuran1+'</b> </td></tr>';
						if(res.detail[0].merk2){
							html +='<tr><td></td><td>: <b>'+res.detail[0].merk2+', '+res.detail[0].ukuran2+'</b> </td></tr>';
						}
						if(res.detail[0].merk3){
							html +='<tr><td></td><td>: <b>'+res.detail[0].merk3+', '+res.detail[0].ukuran3+'</b> </td></tr>';
						}
						if(res.detail[0].merk4){
							html +='<tr><td></td><td>: <b>'+res.detail[0].merk4+', '+res.detail[0].ukuran4+'</b> </td></tr>';
						}
						if(res.detail[0].merk5){
							html +='<tr><td></td><td>: <b>'+res.detail[0].merk5+', '+res.detail[0].ukuran5+'</b> </td></tr>';
						}
					html +='</table>';
					
					if(res.detail.length>1){
					html +='<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">'
						for(i=1;i<res.detail.length;i++){
						html +=''+
							'<div class="panel panel-default">'+
								'<div class="panel-heading" role="tab" id="heading_'+i+'">'+
									'<h4 class="panel-title">'+
										'<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_'+i+'" aria-expanded="true" aria-controls="collapse_'+i+'">'+
											'<span class="fw-bold color-primary">'+res.detail[i].no_po+'</span>'+
										'</a>'+
									'</h4>'+
								'</div>'+
								'<div id="collapse_'+i+'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_'+i+'">'+
									'<div class="panel-body">'+
										'<table class="w100" style="border-collapse: separate; border-spacing: 0px 5px;">'+
											'<tr><td width="35%">Status Perpanjangan</td><td width="65%">: <b>'+res.detail[i].status+'</b> Updated on '+date('d-M-Y H:i',strtotime(res.detail[i].modified_date))+'</td></tr>';
											if(res.detail[i].final_status){
											html +='<tr><td width="35%"></td><td width="65%">: <b>'+res.detail[i].final_status+'</b> on '+date('d-M-Y H:i',strtotime(res.detail[i].final_date))+'</td></tr>';
											}
											html +='<tr><td>Periode Perpanjangan</td><td>: <b>'+date('d-M-Y',strtotime(res.detail[i].periode_start))+'</b> sd <b>'+date('d-M-Y',strtotime(res.detail[i].periode_end))+'</b></td></tr>'+
											'<tr><td>Supplier</td><td>: <b>'+res.detail[i].supplier+'</b> </td></tr>'+
											'<tr><td>Merk & Ukuran</td><td>: <b>'+res.detail[i].merk1+', '+res.detail[i].ukuran1+'</b> </td></tr>';
											if(res.detail[i].merk2){
												html +='<tr><td></td><td>: <b>'+res.detail[i].merk2+', '+res.detail[i].ukuran2+'</b> </td></tr>';
											}
											if(res.detail[i].merk3){
												html +='<tr><td></td><td>: <b>'+res.detail[i].merk3+', '+res.detail[i].ukuran3+'</b> </td></tr>';
											}
											if(res.detail[i].merk4){
												html +='<tr><td></td><td>: <b>'+res.detail[i].merk4+', '+res.detail[i].ukuran4+'</b> </td></tr>';
											}
											if(res.detail[i].merk5){
												html +='<tr><td></td><td>: <b>'+res.detail[i].merk5+', '+res.detail[i].ukuran5+'</b> </td></tr>';
											}
										html +=''+
										'</table>'+
									'</div>'+
								'</div>'+
							'</div>';
						}
					html +='</div>'
					}
					
					$('#modal_view .modal-body').html(html);
						
					$('#modal_view').modal('show');
						
						
						
			}
		});
		
	}
	
</script>
