<style type="text/css">
	.row {
	line-height:30px;
	vertical-align:middle;
	clear:both;
	}
	.row-label, .row-input {
	float:left;
	}
	.row-label {
	padding-left: 15px;
	width:180px;
	}
	.row-input {
	width:420px;
	}
</style>
<script>
	$(document).ready(function() {
		$('#dp1').datepicker({
			format: "dd-M-yyyy",
			autoclose: true,
		});
	});
</script>
<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
		<div class="row">
			<?php if($_SESSION["can_create"] == 1) { ?>
			<div class="col-12">
				<button type="button" class="btn btn-dark" onclick="btn_add()">Tambah</button>
			</div>
			<?php } ?>
			<div class="col-12">
				<table id="tblKPI" class="table table-bordered" style="width:100%;">
					<thead>
						<tr>
							<th scope="col" width="20%">Kategori</th>
							<th scope="col" width="*">Nama Kategori</th>
							<th scope="col" width="10%">Aktif</th>
							<th scope="col" width="10%">Edit Oleh</th>
							<th scope="col" width="20%">Tgl Edit</th>
							<?php if($_SESSION["can_update"] == 1) { ?>
							<th scope="col" width="2%" class="no-sort">Aksi</th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
	</div>
	<!-- load form tambah kpi category -->
	<div class="modal fade" id="m-add" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<form id="form_add" action="<?=site_url('Masterkpi/KPICategoryAdd')?>" method="POST">
				<input type="hidden" name="jenis" value="<?php echo $jenis ?>">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" style="text-align: center;"><strong><?=$jenis?></strong></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					
					<div class="modal-body p30">
						<div class="row">
							<div class="col-3">Kategori</div>
							<div class="col-9">
								<select class="form-control" name="KPICategory" autocomplete="off" required>
									<?php
										if($KPICategory!=''){
											foreach($KPICategory as $value){
												echo '<option value="'.$value['id'].'">'.$value['name'].'</option>';
											}
										}
									?>
								</select>
							</div>
							<div class="col-3">Nama Kategori</div>
							<div class="col-9">
								<input type="text" class="form-control" name="KPICategoryName" autocomplete="off" required>
							</div>
							<!-- <div class="col-3">Aktif</div>
							<div class="col-1">
								<input type="checkbox" class="form-control" name="IsActive" autocomplete="off" required>
							</div> -->
						</div>
					</div>
					<div class="modal-footer">
						<input type="submit" onclick="return confirm('Apakah anda yakin ingin tambah?')" class="btn btn-danger-dark" name="btn-submit" value="SAVE">
						<button type="button" class="btn btn-dark" data-dismiss="modal">CLOSE</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	
	<!-- di load cuman buat ditarik pada saat ambil data GetMasterKpiCategory  -->
	<div class="modal fade" id="m-edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<form id="form_edit" action="<?=site_url('Masterkpi/KPICategoryUpdate')?>" method="POST">
				<input type="hidden" name="jenis" value="<?php echo $jenis ?>">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title"><?=$jenis?></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="row">
							<input type="hidden" class="form-control" name="KPICategoryID" autocomplete="off" readonly>
							<div class="col-3">Kategori</div>
							<div class="col-9">
								<input type="text" class="form-control" name="KPICategory" autocomplete="off" readonly>
							</div>
							<div class="col-3">Nama Kategori</div>
							<div class="col-9">
								<input type="text" class="form-control" name="KPICategoryName" autocomplete="off" required>
							</div>
							<div class="col-3"></div>
							<div class="col-9">
								<input type="checkbox" name="IsActive"> Aktif
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<input type="submit" onclick="return confirm('Apakah anda yakin ingin ubah?')" class="btn btn-danger-dark" name="btn-submit" value="Ubah">
						<button type="button" class="btn btn-dark" data-dismiss="modal">Close</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	
	<script>
		let tblKPI;
		tblKPI = $('#tblKPI').DataTable({
			"pageLength"    : 10,
			"searching"     : true,
			"autoWidth": false,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false }
			],
			// "dom": '<"top">rt<"bottom"ip><"clear">',
			"order": [[1, 'asc']],
		});
		
		$(document).ready(function(){
			$("#form_add").on('submit', function(e) {
				e.preventDefault();
				var form_add = $(this);
				$.ajax({
					url: form_add.attr('action'),
					type: 'post',
					data: form_add.serialize(),
					success: function(response){
						if(response == 'success') {
							alert('SUCCESS');
							location.reload();
						}
						else{
							alert('FAILED. '+response);
						}
					}
				});
			});
			$("#form_edit").on('submit', function(e) {
				e.preventDefault();
				var form_edit = $(this);
				$.ajax({
					url: form_edit.attr('action'),
					type: 'post',
					data: form_edit.serialize(),
					success: function(response){
						if(response == 'success') {
							alert('SUCCESS');
							location.reload();
						}
						else{
							alert('FAILED. '+response);
						}
					}
				});
			});
			getKpiCategory();
		});
		function btn_add(){
			$("#m-add").modal("show");
		}
		function btn_edit(col0,col1,col2,col3){
			
			$("#form_edit input[name='KPICategoryID']").val(col0);
			$("#form_edit input[name='KPICategory']").val(col1);
			$("#form_edit input[name='KPICategoryName']").val(col2);

			var isActive = col3 == 1 ? true : false;
			$("#form_edit input[name='IsActive']").prop("checked",isActive);
			$("#m-edit").modal("show");
		}
		
		function getKpiCategory(){
			$(".loading").show();
			$("#tblKPI tbody").html("");
			var formData = new FormData();
			formData.append("btn-submit",'filter');
			$.ajax({
				url: "<?=site_url('Masterkpi/KPICategoryList?is_salesman='.(($jenis=='SALESMAN')?1:0))?>",
				type: 'post',
				cache: false,
				contentType: false,
				processData: false,
				data: formData,
				success: function (str) {
					// console.log(str);
					var json = JSON.parse(str);
					if(json.code == 1){
						var data = json.data;
						var html = '';
						for(var i=0;i<data.length;i++){
							var col0 = data[i].KPICategoryID;
							var col1 = data[i].KPICategory;
							var col2 = data[i].KPICategoryName;
							var col3 = data[i].IsActive == 1 ? "<span class='label label-success'>Aktif</span>" : "<span class='label label-danger'>Non-Aktif</span>";
							var col4 = data[i].ModifiedBy;
							var col5 = data[i].ModifiedDate;
							<?php if($_SESSION["can_update"] == 1) { ?>
							var col6 = '<div><button type="button" class="btn btn-dark" onclick="btn_edit(\''+col0+'\',\''+col1+'\',\''+col2+'\',\''+data[i].IsActive+'\')"><span class="glyphicon glyphicon-pencil"></span></button></div>';
							<?php } ?>
							tblKPI.row.add([
								col1,
								col2,
								col3,
								col4,
								col5,
								<?php if($_SESSION["can_update"] == 1) { ?>
								col6
								<?php } ?>
							]);
						}
					}
					tblKPI.draw();    
					$(".loading").hide();
				}
			});
		}
	</script>
</div> <!-- /container -->