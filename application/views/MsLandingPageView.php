<?php
	if(!isset($_SESSION['logged_in'])){
		redirect('main','refresh');
	}
?>
<style>
	.table{
	font-size:10px;
	}
	
	.table tr:nth-child(even) {background-color: #f8f8f8;}
	
	table tr{
	font-size: 12px;
	}
	
	table tr td{
	padding:3px;
	}
	
	.filterDropdown {
	width:100%;
	background-color: #ffffcc;
	}
	.filterText {
	width:75%;
	background-color: #ffffcc;
	}
	.title {
	font-size : 15pt;
	font-weight: bold;
	text-align: center;
	}
	
	td, th, .datepicker {
	font-size:9pt!important;
	}
	.dataTables_wrapper .dataTables_length {
	float:left;
	}
	.dataTables_wrapper .dataTables_paginate{
	float:right;
	}
	
	table.dataTable thead .sorting, table.dataTable thead .sorting_desc, table.dataTable thead .sorting_asc {
	background:none;
	}
	
	.btn{
	width:auto;
	}
</style>
<script>
	var t;
	$(document).ready(function() {
	    t = $('#example').DataTable({
			"pageLength"    : 10,
			"searching"     : true,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false }
			],
			"dom": '<"top"l>rt<"bottom"ip><"clear">',
			"order": [[1, 'desc']],
		});
		
		
		t.on('order.dt search.dt', function () {
			let i = 1;
			
			t.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
				this.data(i++);
			});
		}).draw();
		
		
		$('#cari').keyup(function(){
			t.search($(this).val()).draw();
			search_qrcode($(this).val());
			
		})
		
		$("<a href='<?php echo site_url('MsLandingPage/Add') ?>' class='btn btn-default' style='float:right; margin-bottom:5px'><i class='glyphicon glyphicon-plus-sign'></i> Create</a>").insertBefore('#example');
	});
</script>

<div style="position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;">
	<div style="padding: 5px;">
		<?php
			if ($this->session->flashdata('success')) {
				echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>".$this->session->flashdata('success')."</div>";
			}
			
			if ($this->session->flashdata('error')) {
				echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>".$this->session->flashdata('error')."</div>";
			}
		?>
	</div>
</div>
<!-- Fixed navbar -->

<div class="container">
	<div class="title">Master Landing Page</div>
	
	<div class="row" style="margin-bottom:10px">
		<div class="col-md-6">
			Filter Merk
			<select id="filter_merk" onchange="javascript:filter_merk()">
				<option value="">ALL</option>
				<?php
					foreach($merks as $merk){
						echo "<option value='".$merk."'>".$merk."</option>";
					}
				?>
			</select>
		</div>
		<div class="col-md-6" style="text-align:right">
			
			Search
			<input type="text" id="cari" placeholder="Search ...">
			<!--img src="<?php echo base_url('/images/QRCODE.png') ?>" height="38px"-->
		</div>
	</div>
	
	
    <table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
		<?php 
			echo "<thead>";
			echo "<tr>";
			echo "<th style='width:2%' class='no-sort'>No</th>";
			echo "<th style='width:15%'>Merk</th>";
			echo "<th style='width:15%'>Tipe Barang</th>";
			echo "<th style='width:15%'>Lokasi QR Code</th>";
			echo "<th>URL Landing Page</th>";
			echo "<th class='col-hide'>URL</th>";
			echo "<th style='width:10%' class='no-sort'>Aksi</th>";
			echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			$i = 1;
			foreach($result as $r) {
				echo "<tr>"; 
				echo "<td>".$i."</td>";
				echo "<td>".$r->merk."</td>"; 
				echo "<td>".$r->tipe."</td>"; 
				echo "<td>".$r->lokasi_qr_code."</td>";
				echo "<td>".$r->url_redirect."</td>";
				echo "<td>".$r->url."</td>";
				echo '<td>
				<div class="btn-group" role="group" aria-label="...">
				<button type="button" class="btn btn-sm btn-default" onclick="javascript:view_landing_page('.$r->id.')"><i class="glyphicon glyphicon-eye-open"></i></button>
				<button type="button" class="btn btn-sm btn-default" onclick="javascript:edit_landing_page('.$r->id.')"><i class="glyphicon glyphicon-pencil"></i></button>
				</div>
				</td>'; 
				echo "</tr>";
				$i += 1;
			}
		echo "</tbody>"; ?>
	</table>
	
	<div id="modal_view" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Information</h4>
				</div>
				<div class="modal-body">
					<form class="form-horizontal">
						<div class="form-group">
							<label class="col-xs-3">Merk</label>
							<div class="col-xs-4">
								<input type="hidden" id="view_id">
								<input type="hidden" id="view_url">
								<input type="text" id="view_merk" class="form-control" disabled>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3">Type Barang</label>
							<div class="col-xs-4">
								<input type="text" id="view_tipe" class="form-control" disabled>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3">Lokasi QR Code</label>
							<div class="col-xs-4">
								<input type="text" id="view_lokasi_qr_code" class="form-control" disabled>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3">URL Landing Page</label>
							<div class="col-xs-9">
								<textarea id="view_url_redirect" class="form-control" disabled></textarea>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3">QR Code</label>
							<div class="col-xs-4">
								<img src="<?php echo base_url('/images/QRCODE.png') ?>" height="180px" id="view_qrcode">
							</div>
							<div class="col-xs-4">
								<button type="button" class="btn btn-sm" onclick="javascript:copyURL()">Copy URL</button>
								<br>
								<br>
								<a href="#" id="btnDownload" download="QRCode.png" class="btn btn-sm" onclick="javascript:downloadQRCode()">Download</a>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-12">
								<span id="view_created"></span><br>
								<span id="view_modified"></span>
							</label>
						</div>
					</form>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	
	
	<div id="modal_edit" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Edit</h4>
				</div>
				<form class="form-horizontal">
					<div class="modal-body">
						<div class="form-group">
							<label class="col-xs-3">Merk</label>
							<div class="col-xs-4">
								<input type="hidden" id="edit_id">
								<input type="text" id="edit_merk" class="form-control" disabled>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3">Type Barang</label>
							<div class="col-xs-4">
								<input type="text" id="edit_tipe" class="form-control" disabled>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3">Lokasi QR Code</label>
							<div class="col-xs-4">
								<input type="text" id="edit_lokasi_qr_code" class="form-control" disabled>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3">URL Landing Page</label>
							<div class="col-xs-9">
								<textarea id="edit_url_redirect" class="form-control"></textarea>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" id="btn_save" class="btn btn-primary" onclick="javascript:update_landing_page()" >Save</button>
					</div>
				</form>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
</div>	


<script type="text/javascript">
	function view_landing_page(id){
		$('.loading').show();
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo site_url("MsLandingPage/GetList?id='+id+'") ?>',
			dataType: 'json',
			success: function (data) {
				$('.loading').hide();
				// alert(data);
				if(data.id>0){
					$('#view_id').val(data.id);
					$('#view_merk').val(data.merk);
					$('#view_tipe').val(data.tipe);
					$('#view_url').val(data.url);
					$('#view_url_redirect').val(data.url_redirect);
					$('#view_lokasi_qr_code').val(data.lokasi_qr_code);
					$('#view_created').text('Created on '+data.created_date+' By '+data.created_by);
					$('#view_modified').text('Last Modified on '+data.modified_date+' By '+data.modified_by);
					$('#view_qrcode').attr("src", 'data:image/png;base64,' +data.qrcode);
					$('#btnDownload').attr("href", 'data:image/png;base64,' +data.qrcode);
					$('#btnDownload').attr("download", data.filename);
					$('#modal_view').modal('show');
				}
			}
		});
	}
	
	function edit_landing_page(id){
		$('.loading').show();
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo site_url("MsLandingPage/GetList?id='+id+'") ?>',
			dataType: 'json',
			success: function (data){
				$('.loading').hide();
				// alert(data);
				if(data.id>0){
					$('#edit_id').val(data.id);
					$('#edit_merk').val(data.merk);
					$('#edit_tipe').val(data.tipe);
					$('#edit_lokasi_qr_code').val(data.lokasi_qr_code);
					$('#edit_url').val(data.url);
					$('#edit_url_redirect').val(data.url_redirect);
					$('#modal_edit').modal('show');
				}
			}
		});
	}
	
	function update_landing_page(){
		$('.loading').show();
		var id = $('#edit_id').val();
		var url_redirect = $('#edit_url_redirect').val();
		// if(url_redirect==''){
		// alert('URL Landing Page wajib diisi!');
		// return false;
		// }
		$('#modal_edit').modal('hide');
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url("MsLandingPage/Update") ?>', 
			data: { id: id, url_redirect: url_redirect }, 
			dataType: 'json',
			success: function (data) {
				$('.loading').hide();
				alert(data.result+'\n'+data.message);
				if(data.result=='SUKSES'){
					location.reload();
				}
			}
		});
	}
	
	function copyURL() {
		var copyText = document.getElementById("view_url").value;
		
		// ----- hanya bisa di https -----
		// navigator.clipboard.writeText(copyText);
		// alert("URL QRCode Berhasil di-copy:\n" + copyText);
		// -------------------------------
		
		prompt("Tekan Ctrl+C untuk copy url ", copyText);
	}
	
	function downloadQRCode() {
		download($('#view_qrcode').attr('src'),"qrcode.png","image/png");
	}
	
	function filter_merk() {
		var merk = $('#filter_merk').val();
		if(merk==''){
			$("#example").dataTable().fnFilter(merk, 1);
		}
		else{
			$("#example").dataTable().fnFilter("^"+merk+"$", 1, true);
		}
	}
	
	function search_qrcode(qrcode){
		var data = t.rows().data();
		data.each(function (value, index) {
			// console.log(`For index ${index}, data value is ${value}`);
			// console.log('value5='+value[5].replace(/&amp;/g, '&'));
			// console.log('qrcode='+qrcode);
			if(value[5].replace(/&amp;/g, '&')==qrcode) { //5 = kolom URL
				alert('QRCode ditemukan!');
			}
		});
	}
</script>
