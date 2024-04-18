<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>


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
	.ui-autocomplete {
	z-index:999999;
	overflow-x: hidden;
	max-height: 264px;
	}
</style>
<script>
	var t_kdbrg;
	
	$(document).ready(function() {
	var t = $('#example').DataTable({
	"pageLength"    : 10,
	"searching"     : true,
	"columnDefs": [
	{ targets: 'no-sort', orderable: false }
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
	console.log($(this).val());
	t.search($(this).val()).draw();
	})
	
	$("<a href='<?php echo site_url('MsLandingPage/TipeBarangAdd') ?>' class='btn btn-default' style='float:right; margin-bottom:5px'><i class='glyphicon glyphicon-plus'></i> Create</a>").insertBefore('#example');
	
	t_kdbrg = $('#table_kd_brg').DataTable({
	"pageLength"    : 5,
	"bLengthChange": false,
	"ordering": false,
	"bFilter": false,
	"bAutoWidth": false
	});
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
	<div class="title">Master Tipe Barang</div>
    <?php //if($access->can_create == 1) { ?>
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
				<?php  ?>
			</select>
		</div>
		<div class="col-md-6" style="text-align:right">
			Search
			<input type="text" id="cari" placeholder="Search ...">
		</div>
	</div>
	
    <table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
		<?php 
			echo "<thead>";
			echo "<tr>";
			echo "<th style='width:2%' class='no-sort'>No</th>";
			echo "<th style='width:15%'>Merk</th>";
			echo "<th style='width:15%'>Tipe Barang</th>";
			echo "<th style='width:*'>Kode Barang</th>";
			echo "<th style='width:15%'>Last Modified Date</th>";
			echo "<th style='width:15%'>Last Modified By</th>";
			echo "<th style='width:10%' class='no-sort'>Aksi</th>";
			echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			$i = 1;
			foreach($result as $r) {				
				echo "<tr>"; 
				echo "<td>".$i."</td>";
				echo "<td>".$r->merk."</td>"; 
				echo "<td>".$r->tipe.(($r->kd_brg=='') ?"<strong>*</strong>":"")."</td>"; 
				echo "<td>".$r->kd_brg."</td>";
				echo "<td>".$r->modified_date."</td>";
				echo "<td>".$r->modified_by."</td>";
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
								<input type="text" id="view_merk" class="form-control" readonly>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3">Tipe Barang</label>
							<div class="col-xs-4">
								<input type="text" id="view_tipe" class="form-control" readonly>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3">Kode Barang</label>
							<div class="col-xs-9">
								
								<table id="table_kd_brg" class="table table-bordered">
									<thead>
										<th style="width:20px" class="no-sort">No</th>
										<th style="width:*">Kode Barang</th>
									</thead>
									<tbody></tbody>
								</table>
								
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3"></label>
							<label class="col-xs-9">
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
					<h4 class="modal-title">Information</h4>
				</div>
				<?php echo form_open('MsLandingPage/TipeBarangUpdate',array('id' => 'myform','class' => 'form-horizontal')); ?>
				<div class="modal-body">
					<div class="form-group">
						<label class="col-xs-3">Merk</label>
						<div class="col-xs-4">
							<input type="hidden" name="id" id="edit_id">
							<input type="text" id="edit_merk" class="form-control" disabled>
						</div>
					</div>
					<div class="form-group">
						<label class="col-xs-3">Tipe Barang</label>
						<div class="col-xs-4">
							<input type="text" id="edit_tipe" class="form-control" disabled>
						</div>
					</div>
					<div class="form-group">
						<label class="col-xs-3">Kode Barang</label>
						<div class="col-xs-9">
							<div class="input-group">
								<span class="input-group-addon">
									<i class="glyphicon glyphicon-search"></i>
								</span>
								<input type="text" class="form-control" id="kd_brg" placeholder="Ketikkan Kode Barang">
							</div><!-- /input-group -->
						</div>
					</div>
					<div class="form-group">
						<label class="col-xs-3"></label>
						<div class="col-xs-9">			
							<ul class="list-group" id="kd_brgs">
							</ul>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" id="btn_save" class="btn btn-primary" onclick="javascript:update_tipe_barang()" >Save</button>
				</div>
				<?php echo form_close(); ?>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
</div>


<script type="text/javascript">
	function view_landing_page(id){
		$('.loading').show();
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo site_url("MsLandingPage/GetListTipeBarang?id='+id+'") ?>',
			dataType: 'json',
			success: function (data) {
				// alert(data);
				$('.loading').hide();
				if(data.id>0){
					$('#view_id').val(data.id);
					$('#view_merk').val(data.merk);
					$('#view_tipe').val(data.tipe);
					$('#view_created').text('Created on '+data.created_date+' By '+data.created_by);
					$('#view_modified').text('Last Modified on '+data.modified_date+' By '+data.modified_by);
					
					var baris = [];
					
					$('#table_kd_brg tbody').html('');
					if(data.kd_brg!= null){
						var kd_brg = data.kd_brg.split(',');
						var no = 0;
						var isi_table = '';
						
						for(i=0;i<kd_brg.length;i++){
							no++;
							isi_table +='<tr><td>'+no+'</td><td>'+kd_brg[i]+'</td></tr>';
							baris.push([no,kd_brg[i]]);
						}
					}
					
					$('#table_kd_brg').DataTable().clear().rows.add(baris).draw();
					$('#modal_view').modal('show');
				}
			}
		});
	}
	
	function edit_landing_page(id){
		$('.loading').show();
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo site_url("MsLandingPage/GetListTipeBarang?id='+id+'") ?>',
			dataType: 'json',
			success: function (data){
				// console.log(data);
				$('.loading').hide();
				// if(data.id>0){
				$('#edit_id').val(data.id);
				$('#edit_merk').val(data.merk);
				$('#edit_tipe').val(data.tipe);
				$('#edit_created').text('Created on '+data.created_date+' By '+data.created_by);
				$('#edit_modified').text('Last Modified on '+data.modified_date+' By '+data.modified_by);
				
				// alert(data.kd_brg);
				
				if(data.kd_brg!= null){
					var kd_brg = data.kd_brg.split(',');
					var add_kd_brg = '';
					$('#kd_brgs').html('');
					for(i=0;i<kd_brg.length;i++){
						
						add_kd_brg = ' <li class="list-group-item">'+
						'<input type="hidden" name="kd_brg[]" value="'+kd_brg[i]+'" class="kd_brg">'+kd_brg[i]+
						'<button class="btn-danger del_kd_brg" style="float:right" type="button"><i class="glyphicon glyphicon-remove"></i></button>'+
						'</li>';
						
						$('#kd_brgs').append(add_kd_brg);
					}
				}
				$("#kd_brg").autocomplete({
					source: data.barang
				});
				
				$('#modal_edit').modal('show');
				// }
			}
		});
	}
	
	function update_tipe_barang(){
		$("#myform").submit(function() {
			
			$('#modal_edit').modal('hide');
			$('.loading').show();
			var act = $(this).attr('action');
			var data = new FormData(this);
			$.ajax({
				data      	: data,
				url			: act,
				cache		: false,
				contentType	: false,
				processData	: false,
				type		: 'POST',
				dataType  : 'json',
				success   : function(data) {
					$('.loading').hide();
					// alert(data);
					
					alert(data.result+'\n'+data.message);
					if(data.result=='SUKSES'){
						location.reload();
					}
				}
			});
			event.preventDefault(); //Prevent the default submit
		});
		
		
	}
	
	function copyURL() {
		var copyText = document.getElementById("view_url").value;
		navigator.clipboard.writeText(copyText);
		alert("URL QRCode Berhasil di-copy:\n" + copyText);
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
	
	$(document).on("click", ".del_kd_brg" , function() {		
		$(this).closest(".list-group-item").remove();
	});
	
	$('#kd_brg').keypress(function (e) {
		var key = e.which;
		if(key == 13){
			var kd_brg = $('#kd_brg').val().split(' | ');
			
			var bExist  = false;
			$('.kd_brg').each(function(i, obj) {
				if(kd_brg[0] == $(this).val()){
					bExist = true;
				}
			});
			
			if(bExist==true){
				alert(kd_brg[0]+ ' sudah ada dalam list!');
				$('#kd_brg').val('');
				return false;
			}
			else{
				var add_kd_brg = ' <li class="list-group-item">'+
				'<input type="hidden" name="kd_brg[]" value="'+kd_brg[0]+'" class="kd_brg">'+kd_brg[0]+
				'<button class="btn-danger del_kd_brg" style="float:right" type="button"><i class="glyphicon glyphicon-remove"></i></button>'+
				'</li>';
				
				$('#kd_brgs').append(add_kd_brg);
				$('#kd_brg').val('');
				return false; 
			}
		}
	});
	
</script>
