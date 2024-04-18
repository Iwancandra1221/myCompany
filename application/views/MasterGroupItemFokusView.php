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
</style>
<script>
	
	$(document).ready(function() {
		$('#example').DataTable();
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
	<div class="title">Master Group Item Fokus</div>
	
	<a href="<?php echo site_url('MasterGroupItemFokus/Add') ?>"><button>Tambah Group Item Fokus</button></a>
	
	
    <table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
		<?php 
			echo "<thead>";
			echo "<tr>";
			echo "<th width='5%'>No</th>";
			echo "<th width='*'>Group Item Fokus</th>";
			echo "<th width='10%'>Aktif</th>";
			echo "<th width='10%'>Created By</th>";
			echo "<th width='20%'>Created Date</th>";
			echo "<th width='5%'>Edit</th>";
			echo "<th width='5%'>Delete</th>";
			echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			$i = 1;
			foreach($result as $r) {
				echo "<tr>"; 
				echo "<td>".$i."</td>";
				echo "<td>".$r['KATEGORI']."</td>"; 
				echo "<td>".(($r['is_active']==1)?"Y":"N")."</td>";
				echo "<td>".$r['created_by']."</td>";
				echo "<td>".$r['created_date']."</td>";
				echo "<td><a href = '".site_url("MasterGroupItemFokus/Edit?level_code=".$r['KATEGORI'])."'><i class='glyphicon glyphicon-pencil'></a></td>"; 
				// echo "<td><a href = '#' data-href='".site_url('MasterGroupItemFokus/Edit?level_code='.$r['KATEGORI'])."'><i class='glyphicon glyphicon-trash'></a></td>"; 
				echo "<td><a href = '#' data-href='".site_url('MasterGroupItemFokus/Delete?level_code='.$r['KATEGORI'])."' data-toggle='modal' data-target='#confirm-delete' data-record-title='".$r['KATEGORI']."'><i class='glyphicon glyphicon-trash'></a></td>"; 
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
                Delete Confirmation
			</div>
            <div class="modal-body">
				<p>Hapus Master Group Item Fokus <b><i class="title"></i></b> ?</p>
				<p>Ingin Lanjutkan?</p>
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
