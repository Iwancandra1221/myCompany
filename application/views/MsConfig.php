<style>
	
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
	$(document).ready(function() {
        var t = $('#example').DataTable({
			"pageLength": 10,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false }
			],
			// "dom": '<"top"lf>rt<"bottom"ip><"clear">',
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
		})
		
		$("<a href='<?php echo site_url('MsConfig/Add') ?>' class='btn btn-default' style='float:right; margin-bottom:5px'><i class='glyphicon glyphicon-plus-sign'></i> Create</a>").insertBefore('#example');
	});
	
</script>
<div style="position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;">
	<div style="padding: 5px;">
		<?php
			if($this->session->flashdata('success')){
				echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>".$this->session->flashdata('success')."</div>";
			}
			
			if($this->session->flashdata('error')){
				echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>".$this->session->flashdata('error')."</div>";
			}
		?>
	</div>
</div>

<div class="container">
	<div class="form_title"><div style="text-align: center;">MASTER CONFIG</div></div>
	<br>
	<div class="row">
        <div class="col-6 col-m-6">
			CONFIG TYPE: 
			<select id="tipe" onchange="javascript:filter_tipe()">
				<option value="">ALL</option>
				<?php foreach($ConfigType as $type) { ?>
					<option value="<?php echo $type->ConfigType ?>"><?php echo $type->ConfigType ?></option>
					
				<?php } ?>
			</select>
			CONFIG NAME: 
			<select id="name" onchange="javascript:filter_name()">
				<option value="">ALL</option>
			</select>
		</div>
        <div class="col-6 col-m-6" style="text-align:right">
			Search
			<input type="text" id="cari" placeholder="Search ...">
		</div>
	</div>
	
	<table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%" summary="MASTER CONFIG">
        <?php 
			echo "<thead>";
			echo "<tr>";
			echo "<th style='width:5px' class='no-sort'>No</th>";
			echo "<th style='width:100px'>Config Type</th>";
			echo "<th style='width:100px'>Config Name</th>";
			echo "<th style='width:*'>Config Value</th>";
			echo "<th style='width:100px'>Group</th>";
			echo "<th style='width:100px'>Modified Date</th>";
			echo "<th style='width:100px'>Modified By</th>";
			echo "<th style='width:5px'>Aktif</th>";
			// if($access->can_update == 1)
            echo "<th style='width:5px' class='no-sort'>Edit</th>";
			// if($access->can_delete == 1)
            // echo "<th style='width:5px' class='no-sort'>Delete</th>";
			echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			$i = 1;
			foreach($result as $r) {
				echo "<tr>"; 
				echo "<td>".$i."</td>";
				echo "<td>".$r->ConfigType."</td>";
				echo "<td>".$r->ConfigName."</td>"; 
				echo "<td>".((strlen($r->ConfigValue) > 50) ? substr($r->ConfigValue,0,50)."..." : $r->ConfigValue)."</td>";
				echo "<td>".$r->Group."</td>";
				echo "<td>".$r->ModifiedDate."</td>";
				echo "<td>".$r->ModifiedBy."</td>";
				echo "<td><input type='checkbox' ".(($r->IsActive==1)?'checked':'')."  onclick='return false'></td>";
				if($access->can_update == 1)
                echo '<td>
				<button type="button" class="btn btn-sm btn-default" onclick="javascript:edit_config('.$r->ConfigId.')"><i class="glyphicon glyphicon-pencil"></i></button>
				
				</td>';
				// if($access->can_delete == 1)
                // echo "<td>
				// <a href = '#' data-href='MsConfig/Delete?id=".$r->ConfigId."' data-toggle='modal' data-target='#confirm-delete' data-record-title='".$r->ConfigId."'>
				// <button type='button'><i class='glyphicon glyphicon-trash'></i></button>
				// </a></td>"; 
				// echo "</tr>";
				$i += 1;
			}
		echo "</tbody>"; ?>
	</table>
	
	
	
	
	<div id="modal_edit" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Edit</h4>
				</div>
				<form class="form-horizontal">
					<div class="modal-body">
						<div class="form-group">
							<label class="col-xs-3">Config Type</label>
							<div class="col-xs-4">
								<input type="hidden" id="edit_id" value="">
								<input type="text" id="edit_type" class="form-control" disabled>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3">Config Name</label>
							<div class="col-xs-4">
								<input type="text" id="edit_name" class="form-control" disabled>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3">Config Value</label>
							<div class="col-xs-9">
								<textarea id="edit_value" class="form-control" rows="4" required></textarea>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3">GROUP</label>
							<div class="col-xs-9">
								<input type="text" id="edit_group" class="form-control" disabled>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3">Add Info Param</label>
							<div class="col-xs-4">
								<input type="text" id="edit_addinfoparam" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3">Add Info</label>
							<div class="col-xs-9">
								<input type="text" id="edit_addinfo" class="form-control">
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-xs-3"></label>
							<div class="col-xs-4">
								<input type="checkbox" id="edit_aktif" value="1"> Aktif
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-xs-12">
								<span id="view_created"></span><br>
								<span id="view_modified"></span>
							</label>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" id="btn_save" class="btn btn-primary" onclick="javascript:update_config()" >Save</button>
					</div>
				</form>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
</div> <!-- /container -->




<script>
	$('#confirm-delete').on('show.bs.modal', function(e) {
		$(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
		$('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
	});
	
	$('#confirm-delete').on('show.bs.modal', function(e) {
		var data = $(e.relatedTarget).data();
		// $('.title', this).text(data.recordTitle);
	});
	
	function filter_tipe() {
		var tipe = $('#tipe').val();
		if(tipe==''){
			$("#example").dataTable().fnFilter(tipe, 1); // LIKE
		}
		else{
			$("#example").dataTable().fnFilter("^"+tipe+"$", 1, true);  // EQUAL
		}
		
		load_name(tipe);
	}
	
	function filter_name() {
		var name = $('#name').val();
		if(name==''){
			$("#example").dataTable().fnFilter(name, 2);
		}
		else{
			$("#example").dataTable().fnFilter("^"+name+"$", 2, true);  // EQUAL
		}
	}
	
	
	function filter_branch() {
		let branch = $("#BranchId").val();
		if(branch=='JKT') {branch='DMI'}
		$("#example").dataTable().fnFilter(branch, 2);
	}
	
	
	function edit_config(id){
		$('.loading').show();
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo site_url("MsConfig/Edit?id='+id+'") ?>',
			dataType: 'json',
			success: function (data){
				$('.loading').hide();
				// alert(data);
				if(data.ConfigId){
					$('#edit_id').val(data.ConfigId);
					$('#edit_type').val(data.ConfigType);
					$('#edit_name').val(data.ConfigName);
					$('#edit_value').val(data.ConfigValue);
					$('#edit_group').val(data.Group);
					$('#edit_addinfo').val(data.AddInfo);
					$('#edit_addinfoparam').val(data.AddInfoParam);
					$('#edit_aktif').prop("checked",data.IsActive);
					$('#view_created').text('Created on '+data.CreatedDate+' By '+data.CreatedBy);
					$('#view_modified').text('Last Modified on '+data.ModifiedDate+' By '+data.ModifiedBy);
					
					$('.edit_merk').hide();
					$('.edit_qty').hide();
					if(data.ConfigType=='PARAM'){
						$('.edit_merk').show();
					}
					if(data.ConfigType=='LOKASI QRCODE'){
						$('.edit_qty').show();
					}
					$('#modal_edit').modal('show');
				}
			}
		});
	}
	
	function update_config(){
		var id = $('#edit_id').val();
		var value = $('#edit_value').val();
		var addinfoparam = $('#edit_addinfoparam').val();
		var addinfo = $('#edit_addinfo').val();
		var aktif = $('#edit_aktif').is(":checked");
		
		if(value==''){
			alert('Config value wajib diisi!');
			return false;
		}
		
		$('#modal_edit').modal('hide');
		
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url("MsConfig/Update") ?>', 
			data: { ConfigId: id, ConfigValue: value, AddInfoParam: addinfoparam, AddInfo: addinfo, IsActive: aktif }, 
			dataType: 'json',
			success: function (data) {
				// alert(data);
				alert(data.result+'\n'+data.message);
				if(data.result=='SUKSES'){
					location.reload();
				}
			}
		});
	}
	
	function load_name(type){
	
		var opt = '<option value="">ALL</option>';
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo site_url("MsConfig/GetConfigName?type='+type+'") ?>',
			dataType: 'json',
			success: function (data) {
				for(i=0;i<data.length;i++){
					opt += '<option value="'+data[i].ConfigName+'">'+data[i].ConfigName+'</option>';
				}
				$('#name').html(opt);
			}
		});
	}
	
	
</script>
