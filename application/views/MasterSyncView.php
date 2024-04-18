<script>
	$(document).ready(function() {
		$('#example').DataTable({
			// "dom": '<lf<t>ip>',
			"dom": '<"top"lf>rt<"bottom"ip><"clear">',
			"pageLength": 25,
			"autoWidth": false
		});
	});
	
	$('#confirm-delete').on('show.bs.modal', function(e) {
		$(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
		$('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
	});
</script>

<?php //echo(json_encode($access)); ?>

<div class="container">
	<div class="form_title"  style="text-align: center;"> MASTER CONFIG SYNC </div>
	<br>
	<div class="row">
		<div class="col-6 col-m-6">
			<table>
				<tr>
					<td width="100px">CONFIG TYPE</td>
					<td>
						<input type="radio" name="config_type_filter" value="ALL" id="type0" onclick="javascript:filter_type('')" checked> <label for="type0">ALL</label>
						<input type="radio" name="config_type_filter" value="CONFIG" id="type1" onclick="javascript:filter_type('CONFIG')"> <label for="type1">CONFIG</label>
						<input type="radio" name="config_type_filter" value="TABLE" id="type2" onclick="javascript:filter_type('TABLE')"> <label for="type2">TABLE</label>
					</td>
				</tr>
				<tr>
					<td>CABANG </td>
					<td>
						<select id="BranchId" onchange="javascript:filter_branch()" style="width:200px">
							<option value="">ALL</option>
							<?php foreach($branches as $b) { 
								echo("<option value='".$b['Kd_Lokasi']."'>".$b['Nm_Lokasi']."</option>");
							}?>
						</select>
					</td>
				</tr>
			</table>
		</div>
		<div class="col-6 col-m-6" style="text-align:right">
			<?php if($access->can_create == 1) { ?>
				<a href="MasterSync/Add?ConfigType=CONFIG" class="btn btn-dark">Tambah Config Type CONFIG</a>
				<a href="MasterSync/Add?ConfigType=TABLE" class="btn btn-dark">Tambah Config Type TABLE</a>
			<?php } ?>
		</div>
	</div>
	<table id="tb-master-sync" class="table table-striped table-bordered" cellspacing="0" width="100%">
		<?php 
			echo "<thead>";
			echo "<tr>";
			echo "<th>No</th>";
			echo "<th>Config Type</th>";
			echo "<th>Branch ID</th>";
			echo "<th>Config Name</th>";
			echo "<th>Config Value</th>";
			echo "<th>Level</th>";
			echo "<th>Aktif</th>";
			echo "<th width='60px'>Aksi</th>";
			echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			$i = 1;
			foreach($result as $r) {
				echo "<tr>"; 
				echo "<td>".$i."</td>";
				echo "<td>".$r->ConfigType."</td>";
				echo "<td>".$r->BranchId."</td>"; 
				echo "<td>".$r->ConfigName."</td>"; 
				echo "<td>".$r->ConfigValue."</td>";
				echo "<td>".$r->Level."</td>";
				echo "<td><input type='checkbox' class='js-switch' ".(($r->IsActive==1)?'checked':'')." data='".$r->ConfigId."'></td>";
				echo "<td>";
				if($access->can_update == 1)
				echo "<a href = 'MasterSync/Edit?ConfigType=".$r->ConfigType."&ConfigId=".$r->ConfigId."' class='btn btn-sm btn-dark'><i class='glyphicon glyphicon-pencil'></i></a> ";
				if($access->can_delete == 1)
				echo "<a href = '#' data-href='MasterSync/Delete?id=".$r->ConfigId."' data-toggle='modal' data-target='#confirm-delete' data-record-title='".$r->ConfigId."' class='btn btn-sm btn-danger-dark'><i class='glyphicon glyphicon-trash'></i></a>"; 
				echo "</td>";
				echo "</tr>";
				$i += 1;
			}
		echo "</tbody>"; ?>
	</table>
</div> <!-- /container -->
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				Konfirmasi Hapus Data
			</div>
			<div class="modal-body">
				<p>Data akan dihapus, dan tidak bisa dikembalikan.</p>
				<p>Lanjutkan menghapus?</p>
				<!-- <p class="debug-url"></p> -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<a class="btn btn-danger btn-ok">Delete</a>
			</div>
		</div>
	</div>
</div>
<link rel="stylesheet" href="<?=base_url()?>assets/switchery/switchery.min.css">
<script src="<?=base_url()?>assets/switchery/switchery.min.js"></script>
<script>
	var tblMasterSync;
	var switcheries = [];
	$(document).ready(function(){
		tblMasterSync = $('#tb-master-sync').DataTable({
			"createdRow": function (row, data, index) {
		        // may be something like this (again, not tested)
		        var switchElem = Array.prototype.slice.call($(row).find('.js-switch'));
		        switchElem.forEach(function (html) {
		            var switchery = new Switchery(html, { size:'small',color: '#0275D8', secondaryColor: '#dee2e6' });
		        	switchery.disable();
		        	$(switchElem).change(function() {
						var isChecked = switchery.isChecked();
						IsActive = isChecked == true ? 1 : 0;
						var ConfigId = $(this).attr("data");

						var formData = new FormData();
						formData.append('ConfigId', ConfigId);
						formData.append('IsActive', IsActive);
						$.ajax({
							url: "<?=base_url()?>MasterSync/UpdateStatus",
							type: 'post',
							cache: false,
							contentType: false,
							processData: false,
							data: formData,
							success: function (data) {
								var json = JSON.parse(data);
						    	alert(json.messages[0]);
							}
						});

					});
		        	switcheries.push(switchery);
		        });
			},
			"drawCallback": function() {

				var configTypeFilter = $('input[name="config_type_filter"]:checked').val(); 
				if(configTypeFilter=='TABLE'){
		      		for (var i = 0; i < switcheries.length; i++) {
				      var switchery = switcheries[i];
				      // Gunakan objek Switchery sesuai kebutuhan
				      switchery.enable();
				    }
				}
				else{
					for (var i = 0; i < switcheries.length; i++) {
				      var switchery = switcheries[i];
				      // Gunakan objek Switchery sesuai kebutuhan
				      switchery.disable();
				    }
				}
			}
		});
	});
	$('#confirm-delete').on('show.bs.modal', function(e) {
		$(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
		$('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
	});
	
	$('#confirm-delete').on('show.bs.modal', function(e) {
		var data = $(e.relatedTarget).data();
		// $('.title', this).text(data.recordTitle);
	});
	
	function filter_type(type) {
		$("#example").dataTable().fnFilter(type, 1);

		var configTypeFilter = $('input[name="config_type_filter"]:checked').val(); 
		if(configTypeFilter=='TABLE'){
      		for (var i = 0; i < switcheries.length; i++) {
		      var switchery = switcheries[i];
		      // Gunakan objek Switchery sesuai kebutuhan
		      switchery.enable();
		    }
		}
		else{
			for (var i = 0; i < switcheries.length; i++) {
		      var switchery = switcheries[i];
		      // Gunakan objek Switchery sesuai kebutuhan
		      switchery.disable();
		    }
		}

		
		
	}
	function filter_branch() {
		let branch = $("#BranchId").val();
		if(branch=='JKT') {branch='DMI'}
		$("#example").dataTable().fnFilter(branch, 2);
	}
</script>
