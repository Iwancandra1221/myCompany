<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>


<?php
	if(!isset($_SESSION['logged_in'])){
		redirect('main','refresh');
	}
?>
<style>
</style>

<div id="notifikasi">
	<?php
		if(ISSET($msg)) {
			if($msg == 'success') {
				echo '
				<div class="msg msg-danger">
					<i class="glyphicon glyphicon-ok-sign"></i>
					'.$description.'
				</div>';
			}
			if($msg == 'failed') {
				echo '
				<div class="msg msg-danger">
					<i class="glyphicon glyphicon-remove-circle"></i>
					'.$description.'
				</div>';
			}
		}
		// if ($this->session->flashdata('success_delete')) {
			// echo '
			// <div class="msg msg-danger">
				// <i class="glyphicon glyphicon-ok-sign"></i>
				// '.$this->session->flashdata('success_delete').'
			// </div>';
		// }
		// if ($this->session->flashdata('failed_delete')) {
			// echo '
			// <div class="msg msg-danger">
				// <i class="glyphicon glyphicon-remove-circle"></i>
				// '.$this->session->flashdata('failed_delete').'
			// </div>';
		// }
		// if ($this->session->flashdata('success_insert')) {
			// echo '
			// <div class="msg msg-success">
			// <i class="glyphicon glyphicon-ok-sign"></i>
			// '.$this->session->flashdata('success_insert').'
			// </div>';
		// }
		// if ($this->session->flashdata('success_update')) {
			// echo '
			// <div class="msg msg-success">
			// <i class="glyphicon glyphicon-ok-sign"></i>
			// '.$this->session->flashdata('success_update').'
			// </div>';
		// }
	?>
</div>

<div class="container">
	<div class="title">MASTER GROUP TIPE BARANG</div>
    <?php //if($access->can_create == 1) { ?>
	<div class="row" style="margin-bottom:10px">
		<div class="col-md-9">
			Merk
			<select id="filter_merk" class="form-control-dark" onchange="javascript:filter_merk()">
				<option value="">ALL</option>
				<?php
					foreach($merks as $merk){
						echo "<option value='".$merk."'>".$merk."</option>";
					}
				?>
			</select>
		</div>
		<div class="col-md-3" style="text-align:right">
			<div class="input-group input-group-dark">
				<span class="input-group-addon">
					<i class="glyphicon glyphicon-search"></i>
				</span>
				<input type="text" class="form-control form-control-dark" id="cari" placeholder="Search ...">
			</div>
		</div>
	</div>
	
    <table id="table-master" class="table table-striped table-bordered" cellspacing="0" width="100%">
		<?php 
			echo "<thead>";
			echo "<tr>";
			echo "<th style='width:2%' class='no-sort'>NO</th>";
			echo "<th style='width:15%'>GROUP TIPE BARANG</th>";
			echo "<th style='width:15%'>MERK</th>";
			echo "<th style='width:*'>KODE BARANG</th>";
			echo "<th style='width:15%'>LAST MODIFIED DATE</th>";
			echo "<th style='width:15%'>LAST MODIFIED BY</th>";
			echo "<th style='width:5%' class='no-sort'>AKSI</th>";
			echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			$i = 1;
			foreach($result as $r) {				
				echo "<tr>"; 
				echo "<td>".$i."</td>";
				echo "<td>".$r->tipe.(($r->kd_brg=='') ?"<strong>*</strong>":"")."</td>"; 
				echo "<td>".$r->merk."</td>"; 
				echo "<td>".$r->kd_brg."</td>";
				echo "<td>".date('d-M-Y',strtotime($r->modified_date))."</td>";
				echo "<td>".$r->modified_by."</td>";
				echo '<td>
				<button type="button" class="btn btn-sm btn-default" onclick="javascript:viewGroupTipeProduct('.$r->id.')"><i class="glyphicon glyphicon-search"></i></button>
				</td>'; 
				echo "</tr>";
				$i += 1;
			}
		echo "</tbody>"; ?>
	</table>
</div>

<div id="modal_view" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true" style="background:red; padding:0px 10px">&times;</span>
				</button>
				<h4 class="modal-title" style="text-align: center;"> <strong>VIEW DETAIL MASTER GROUP BARANG</strong> </h4>
			</div>
			<div class="modal-body p30">
				<?php echo form_open('MsSmartQR/GroupProductUpdate',array('id' => 'myformXXX','class' => 'form-horizontal')); ?>
					<div class="form-group">
						<label class="col-xs-3">Merk</label>
						<div class="col-xs-9">
							<input type="hidden" name="id" id="view_id">
							<input type="hidden" id="view_url">
							<input type="text" name="merk" id="view_merk" class="form-control form-control-dark" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-xs-3">Tipe Barang</label>
						<div class="col-xs-9">
							<input type="text" name="tipe" id="view_tipe" class="form-control form-control-dark" readonly>
						</div>
					</div>
					
					<hr class="hr-dark">
					
					<div class="row">
						<div class="col-12 text-right">
							<button type="button" class="btn btn-dark" id="btnBrowse" onclick="javascript:browseProduct()" disabled>
								<i class="glyphicon glyphicon-plus-sign"></i> TAMBAH KD BARANG
							</button>
						</div>
					</div>
					
					<div class="row">
						<div class="col-12">
							<table id="table-detail" class="table table-bordered">
								<thead>
									<th style="width:1%" class="no-sort">NO</th>
									<th style="width:30%">KODE BARANG</th>
									<th style="width:*">NAMA BARANG</th>
									<th style="width:2%" class="no-sort">AKSI</th>
								</thead>
								<tbody></tbody>
							</table>
						</div>
					</div>
					
					<div class="row">
						<div class="col-12 text-right">
							
							<button type="submit" class="btn btn-dark btn-hide" id="btnSave">
								<i class="glyphicon glyphicon-floppy-disk"></i> SAVE
							</button>
							
							<button type="button" class="btn btn-dark btn-hide" id="btnCancel" onclick="javascript:cancelGroupProduct()">
								<i class="glyphicon glyphicon-ban-circle"></i> CANCEL
							</button>
							<button type="button" class="btn btn-dark btn-show" id="btnEdit" onclick="javascript:editGroupProduct()">
								<i class="glyphicon glyphicon-pencil"></i> EDIT
							</button>
							
							<button type="button" class="btn btn-danger-dark btn-show" id="btnDelete" onclick="javascript:willDeleteGroupProduct()">
								<i class="glyphicon glyphicon-trash"></i> HAPUS
							</button>
							
						</div>
					</div>
					
					
					<div class="form-group">
						<div class="col-xs-3"></div>
						<div class="col-xs-9">
							<small>
							<span id="view_created"></span><br>
							<span id="view_modified"></span>
							</small>
						</div>
					</div>
				</form>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="modal_delete" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<?php echo form_open('MsSmartQR/GroupProductDelete',array('id' => 'form_delete')); ?>
				<div class="modal-body"  style="text-align: center;"> 
					<i class="glyphicon glyphicon-exclamation-sign" style="font-size:400%;color:red"></i>
					<br>
					<p style="font-size:120%;color:red;font-weight:bold">APAKAH ANDA YAKIN INGIN GROUP TIPE BARANG INI ?</p>
					<p style="font-size:120%;color:red;font-weight:bold">ALASAN MENGHAPUS ?</p>
					<input type="hidden" name="id" id="delete_id">
					<input type="hidden" name="tipe" id="delete_tipe">
					<input type="hidden" name="merk" id="delete_merk">
					<input type="text"  name="reason_deleted" id="reason_deleted" class="form-control form-control-dark" onkeypress="return event.keyCode!=13" required>
					<br>
					<button type="submit" id="btn_delete" class="btn btn-danger-dark" onclick="javascript:deleteGroupProductXXX()" >YA, HAPUS</button>
					<button type="button" class="btn btn-dark" data-dismiss="modal">CANCEL</button> 
				</div>
				<div class="modal-footer">
				</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="modal_add" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true" style="background:red; padding:0px 10px">&times;</span>
				</button>
				<h4 class="modal-title"  style="text-align: center;"> <strong>BARANG LIST</strong> </h4>
			</div>
			<div class="modal-body">
				<form class="form-horizontal p20">
					<div class="form-group">
						<div class="col-xs-12">
							<div class="input-group input-group-dark mb10">
								<span class="input-group-addon">
									<i class="glyphicon glyphicon-search"></i>
								</span>
								<input type="text" class="form-control form-control-dark " id="filterBarang" placeholder="Ketikkan Kode Barang">
							</div><!-- /input-group -->
							
							<table id="table-group" class="table table-striped table-bordered stripe">
								<thead>
									<tr>
										<th class="no-sort" width="30%">Kode Barang</th>
										<th width="68%">Nama Barang</th>
										<th class="no-sort" width="2%" class="no-sort"></th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
							<br>
							<div  style="text-align: center;">
								<button type="button" class="btn btn-dark" id="btnAddBarang" onclick="javascript:addBarang()" disabled> ADD TO TABLE KD BARANG </button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
	var tableDetail;
	var tableGroup;
	
	$(document).ready(function() {
		$("#myform").submit(function() {			
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
					// console.log(data);
					$('.loading').hide();
					if(data.result=='SUKSES'){
						window.location.href = '<?php echo site_url("MsSmartQR/GroupProduct") ?>';
					}
					else{
						alert(data.result+'\n'+data.message);
					}
					
				}
			});
			event.preventDefault(); //Prevent the default submit
		});
		
		createTableGroup();
	
		var tableMaster = $('#table-master').DataTable({
			"pageLength"    : 10,
			"searching"     : true,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false }
			],
			"dom": '<"top"l>rt<"bottom"ip>',
			"order": [[3, 'asc'], [2, 'asc'], [1, 'asc']], //order = kode, merk, tipe
			"language": {
				"paginate": {
					"previous": "<",
					"next": ">"
				}
			},
		});
		
		tableMaster.on('order.dt search.dt', function () {
			let i = 1;
			tableMaster.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
				this.data(i++);
			});
		}).draw();
		
		$('#cari').keyup(function(){
			console.log($(this).val());
			tableMaster.search($(this).val()).draw();
		})
		
		$("<a href='<?php echo site_url('MsSmartQR/GroupProductAdd') ?>' class='btn btn-dark' style='float:right; margin-bottom:5px'><i class='glyphicon glyphicon-plus'></i> CREATE NEW</a>").insertBefore('#table-master');
		
		tableDetail = $('#table-detail').DataTable({
			"pageLength"    : -1,
			"bLengthChange": false,
			"bFilter": false,
			"bAutoWidth": false,
			"columnDefs": [
				{ targets: 'no-sort', orderable: false }
			],
			"order": [[1, 'asc']],
			"language": {
				"paginate": {
					"previous": "<",
					"next": ">"
				},
			},
		});
		
		$('#table-detail tbody').on( 'click', '.btnDelete', function () {
			tableDetail.row($(this).parents('tr')).remove().draw();
		});
		
		tableDetail.on('order.dt search.dt', function () {
			let i = 1;
			
			tableDetail.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
				this.data(i++);
			});
		}).draw();
		
		$(".msg").delay(3000).fadeOut("slow");
		$('.btn-hide').hide();
	
	});
	
	$(document).on("click", ".chkKdBrg" , function() {		
		var numberOfChecked = $('input.chkKdBrg:checked').length;
		if(numberOfChecked>0){
			$('#btnAddBarang').prop('disabled', false);
		}
		else{
			$('#btnAddBarang').prop('disabled', true);
			}
	});
	
	function loadBarangList(){
		$('.loading').show();
		tableGroup.clear().draw();
		var merk  = $('#view_merk').val();
		var url = '<?php echo site_url("MsSmartQR/GetBarangList?merk=") ?>'+merk;
		$.ajax({ 
			type: 'GET', 
			url: url,
			dataType: 'json',
			success: function (data) {
				$('.loading').hide();
				for(i=0;i<data.length-1;i++){
					tableGroup.row.add([data[i][0],data[i][1],'<input type="checkbox" class="chkKdBrg" value="'+data[i][0]+' | '+data[i][1]+'">']);
				}
				tableGroup.draw();
			}
		});
	}
	
	function createTableGroup(){
		tableGroup = $('#table-group').DataTable({
			"pageLength"    : 5,
			"autoWidth": false,
			"lengthChange": false,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false },
			],
			"dom": '<"top">rt<"text-center"p><"clear">',
			"order": [[1, 'asc']],
			"language": {
				"paginate": {
					"previous": "<",
					"next": ">"
				},
			},
			
		});
		
		
		$('#filterBarang').keyup(function(){
			tableGroup.search($(this).val()).draw();
		})
	}
	
	function addBarang(){
		let c = $('input.chkKdBrg:checked').length;
		if(c>0){
			$('input:checkbox.chkKdBrg').each(function () {
				if(this.checked){
					let val = $(this).val().split(' | ');
					let numRows = tableDetail.rows().count();
					let bExist = false;
					
					$('.kdBrgAdd').each(function(i, obj) {
						if(val[0] == $(this).val()){
							bExist = true;
						}
					});
					
					if(bExist == true){
						alert('Kode barang "'+val[0]+'" sudah ada dalam list!');
					}
					else{
						tableDetail.row.add([(numRows+1), '<input type="hidden" name="kd_brg[]" class="kdBrgAdd" value="'+val[0]+'">'+val[0], val[1], '<button type="button" class="btn btn-dark-sm btn-danger-dark deleteBarang"><i class="glyphicon glyphicon-remove"></i></button>']).draw();
					}
				}
			});
		}
	}
	
	function viewGroupTipeProduct(id){
		cancelGroupProduct();
		$('.loading').show();
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo site_url("MsSmartQR/GetListGroupProduct?id=") ?>'+id,
			dataType: 'json',
			success: function (data) {
				$('.loading').hide();
				if(data.id>0){
					$('#view_id').val(data.id);
					$('#view_merk').val(data.merk);
					$('#view_tipe').val(data.tipe);
					$('#view_created').text('Created on '+data.created_tgl+' | '+data.created_jam+' | '+data.created_by);
					$('#view_modified').text('Last Modified on '+data.modified_tgl+' | '+data.modified_jam+' | '+data.modified_by);
					
					var baris = [];
					
					tableDetail.clear();
					if(data.kd_brg!= null){
						var kd_brg = data.kd_brg.split(',');
						var no = 0;
						// var isi_table = '';
						
						for(i=0;i<kd_brg.length;i++){
						
							for(j=0;j<data.barang.length;j++){
								if(data.barang[j][0]==kd_brg[i]){
									no++;
									baris.push([no,'<input type="hidden" name="kd_brg[]" class="kdBrgAdd" value="'+data.barang[j][0]+'">'+data.barang[j][0],data.barang[j][1],'<button type="button" class="btn btn-sm btn-dark-sm btn-danger-dark btnDelete" disabled><i class="glyphicon glyphicon-remove"></i></button>']);
								}
							}
						}
					}
					tableDetail.rows.add(baris).draw();
					$('#modal_view').modal('show');
				}
			}
		});
	}
	
	function copyURL() {
		var copyText = document.getElementById("view_url").value;
		navigator.clipboard.writeText(copyText);
		alert("URL QRCode Berhasil di-copy:\n" + copyText);
	}
	
	function filter_merk() {
		var merk = $('#filter_merk').val();
		if(merk==''){
			$("#table-master").dataTable().fnFilter(merk, 1);
		}
		else{
			$("#table-master").dataTable().fnFilter("^"+merk+"$", 1, true);
		}
	}
	
	$(document).on("click", ".delete_barang" , function() {		
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
				'<button class="btn-danger delete_barang" style="float:right" type="button"><i class="glyphicon glyphicon-remove"></i></button>'+
				'</li>';
				
				$('#kd_brgs').append(add_kd_brg);
				$('#kd_brg').val('');
				return false; 
			}
		}
	});
	
	function editGroupProduct(){
		$('#btnBrowse').prop('disabled',false);
		$('.btnDelete').prop('disabled',false);
		$('.btn-hide').show();
		$('.btn-show').hide();
	}
	
	function cancelGroupProduct(){
		$('#btnBrowse').prop('disabled',true);
		$('.btnDelete').prop('disabled',true);
		$('.btn-hide').hide();
		$('.btn-show').show();
	}
	
	function willDeleteGroupProduct(){
		var id = $('#view_id').val();
		var merk = $('#view_merk').val();
		var tipe = $('#view_tipe').val();
		$('#delete_id').val(id);
		$('#delete_merk').val(merk);
		$('#delete_tipe').val(tipe);
		
		$('#modal_delete').modal('show');
	}
	
	function deleteGroupProduct(){
		var id = $('#view_id').val();
		var merk = $('#view_merk').val();
		var tipe = $('#view_tipe').val();
		var reason_deleted = $('#reason_deleted').val();
		if(reason_deleted==''){
			alert('Alasan menghapus wajib diisi!');
			$('#reason_deleted').focus();
			return false;
		}
		else{
			$('.loading').show();
			$('#modal_delete').modal('hide');
			$.ajax({ 
				type: 'POST', 
				url: '<?php echo site_url("MsSmartQR/GroupProductDelete") ?>', 
				data: { id: id, merk: merk, tipe: tipe, reason_deleted: reason_deleted },
				dataType: 'json',
				success: function (data) {
					$('.loading').hide();
					// alert(data.result+'\n'+data.message);
					if(data.result=='SUKSES'){
						location.reload();
					}
					if(data.result=='FAILED'){
						location.reload();
					}
				}
			});
		}
	}
	
	function browseProduct(){
		loadBarangList();
		$('#modal_add').modal("show");
	}
	
</script>
