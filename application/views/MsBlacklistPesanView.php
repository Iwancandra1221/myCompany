<script>
	$(document).ready(function() {
        $('#example').DataTable({
			"pageLength": 25
		});
	});
	
	$('#confirm-delete').on('show.bs.modal', function(e) {
		$(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
		$('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
	});
</script>  


<div style="position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;">
	<div style="padding: 5px;">
		<?php
			if (isset($_GET['insertsuccess'])) {
				if($_GET['insertsuccess']==1)
				echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Inserted <strong>Successfully !</strong></div>";
				else 
				echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>Data Inserted <strong>Failed !</strong></div>";
			}
			
			if (isset($_GET['updatesuccess'])) {
				if($_GET['updatesuccess']==1)
				echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Updated <strong>Successfully !</strong></div>";
				else 
				echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>Data Updated <strong>Failed !</strong></div>";
			}
			
			if (isset($_GET['deletesuccess'])) {
				if($_GET['deletesuccess']==1)
				echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Removed <strong>Successfully !</strong></div>";
				else 
				echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>Data Removed <strong>Failed !</strong></div>";
			}
		?>
	</div>
</div>
<!-- Fixed navbar -->
<div class="container">
	<div class="form_title" style="text-align: center;">MASTER PESAN BLACKLIST</div>
	<br>
	<div class="row">
        <div class="col-12 col-m-6">
			<?php //if($access->can_create == 1) { ?>
				<a href="<?php echo site_url() ?>UniqueCodeGenerator<?php echo($version) ?>/BlacklistPesanAdd"><button>Tambah Pesan Blacklist</button></a>
			<?php //} ?>
		</div>
	</div>
	<table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <?php 
			echo "<thead>";
			echo "<tr>";
			echo "<th width='5%'>No</th>";
			echo "<th width='*'>Pesan</th>";
			echo "<th width='10%'>CreatedBy</th>";
			echo "<th width='10%'>CreatedDate</th>";
			echo "<th width='5%'>Aktif</th>";
			// if($access->can_update == 1)
            echo "<th width='5%'>Edit</th>";
			// if($access->can_delete == 1)
            echo "<th width='5%'>Delete</th>";
			echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			$i = 1;
			foreach($result as $r) {
				echo "<tr>"; 
				echo "<td>".$i."</td>";
				echo "<td>".$r->Pesan."</td>";
				echo "<td>".$r->CreatedBy."</td>";
				echo "<td>".$r->CreatedDate."</td>";
				echo "<td><input type='checkbox' ".(($r->IsActive==1)?'checked':'')."  onclick='return false'></td>";
				// if($access->can_update == 1)
                echo "<td><a href = '".site_url()."UniqueCodeGenerator".$version."/BlacklistPesanEdit/".$r->ID."'><i class='glyphicon glyphicon-pencil'></td>";
				// if($access->can_delete == 1)
                echo "<td><a href = '#' data-href='".site_url()."UniqueCodeGenerator".$version."/BlacklistPesanDelete/".$r->ID."' data-toggle='modal' data-target='#confirm-delete' data-record-title='".$r->Pesan."'><i class='glyphicon glyphicon-trash'></a></td>"; 
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
                <p>Data <b><i class="title"></i></b> akan dihapus, dan tidak bisa dikembalikan.</p>
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
<script>
	$('#confirm-delete').on('show.bs.modal', function(e) {
		$(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
		$('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
	});
	
	$('#confirm-delete').on('show.bs.modal', function(e) {
		var data = $(e.relatedTarget).data();
		$('.title', this).text(data.recordTitle);
	});
</script>
