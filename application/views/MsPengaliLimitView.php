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
		$('#tabel_wilayah').DataTable({
	      "pageLength": 25
	    });
		$('#tabel_toko').DataTable({
	      "pageLength": 25
	    });
		
		$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
			  if (e.target.hash == '#toko') {
				$('#tabel_toko').DataTable().columns.adjust().draw()
			  }
			})


	});
</script>

<div style="position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;">
	<div style="padding: 5px;">
	<?php if($this->session->flashdata('error')){ ?>
		<div class='alert alert-danger' id='flash-msg' style='float:auto'>
			<?php echo $this->session->flashdata('error') ?>
		</div>
	<?php } ?>
	<?php if($this->session->flashdata('success')){ ?>
		<div class='alert alert-success' id='flash-msg' style='float:auto'>
			<?php echo $this->session->flashdata('success') ?>
		</div>
	<?php } ?>
	</div>
</div>
<!-- Fixed navbar -->

<div class="container">
	
	<div class="title">Master Pengali Limit</div>
	
	<a href="<?php echo site_url('MsPengaliLimit/Export') ?>" style="float:right;margin-left:5px"><button>Export Pengali Limit</button></a>
	<a href="<?php echo site_url('MsPengaliLimit/Add') ?>" style="float:right"><button>Tambah Pengali Limit</button></a>
	
	<ul class="nav nav-tabs" style="background-color:#fff!important; color:#000!important; ">
		<li class="active" style="border-radius:10px 10px 0 0;"><a data-toggle="tab" href="#wilayah">Pengali Limit By Wilayah</a></li>
		<li style="border-radius:10px 10px 0 0;"><a data-toggle="tab" href="#toko">Pengali Limit By Toko</a></li>
	</ul>
	
	<div class="tab-content" style="padding: 10px 0;">
		<div id="wilayah" class="tab-pane fade in active">
			<table id="tabel_wilayah" class="table table-striped table-bordered" cellspacing="0" width="100%">
				<?php 
					echo "<thead>";
					echo "<tr>";
					echo "<th style='width:10px'>No</th>";
					echo "<th style='width:100px'>Divisi</th>";
					echo "<th style='width:100px'>Partner Type</th>";
					echo "<th style='width:200px'>Wilayah</th>";
					echo "<th style='width:100px'>Pengali</th>";
					echo "<th style='width:100px'>Last Modified By</th>";
					echo "<th style='width:120px'>Last Modified Date</th>";
					echo "<th style='width:40px'>Aksi</th>";
					echo "</tr>";
					echo "</thead>";
					echo "<tbody>";
					$i = 1;
					foreach($PengaliLimitByWilayah as $r) {
						echo "<tr>"; 
						echo "<td>".$i."</td>";
						echo "<td>".$r['Divisi']."</td>"; 
						echo "<td>".$r['Partner_Type']."</td>"; 
						echo "<td>".$r['Wilayah']."</td>"; 
						echo "<td>".$r['Pengali']."</td>"; 
						echo "<td>".$r['User_Name']."</td>";
						echo "<td>".$r['Entry_Time']."</td>";
						echo "<td><a href = '".site_url("MsPengaliLimit/Edit?divisi=".urlencode($r['Divisi'])."&wilayah=".urlencode($r['Wilayah'])."&partner_type=".urlencode($r['Partner_Type']))."'><button><i class='glyphicon glyphicon-pencil'></i></button></a> ";  
						echo " <a href = '#' data-href='".site_url('MsPengaliLimit/Delete?divisi='.urlencode($r['Divisi']).'&wilayah='.urlencode($r['Wilayah']).'&partner_type='.urlencode($r['Partner_Type']))."' data-toggle='modal' data-target='#confirm-delete1' data-record-title='Wilayah = ".$r['Wilayah']." - Partner Type = ".$r['Partner_Type']." - Divisi = ".$r['Divisi']." '><button><i class='glyphicon glyphicon-trash'></i></button></a></td>"; 
						echo "</tr>";
						$i += 1;
					}
				echo "</tbody>"; ?>
			</table>
		</div>
		<div id="toko" class="tab-pane fade">
			<table id="tabel_toko" class="table table-striped table-bordered" cellspacing="0" width="100%">
				<?php 
					echo "<thead>";
					echo "<tr>";
					echo "<th style='width:10px'>No</th>";
					echo "<th style='width:50px'>Kode</th>";
					echo "<th>Nama Dealer</th>";
					echo "<th style='width:50px'>Divisi</th>";
					echo "<th style='width:50px'>Pengali</th>";
					echo "<th style='width:50px'>Max Limit</th>";
					echo "<th style='width:50px'>Created By</th>";
					echo "<th style='width:100px'>Created Date</th>";
					echo "<th style='width:40px'>Aksi</th>";
					echo "</tr>";
					echo "</thead>";
					echo "<tbody>";
					$i = 1;
					foreach($PengaliLimitByToko as $r) {
						echo "<tr>"; 
						echo "<td>".$i."</td>";
						echo "<td>".$r['kd_Plg']."</td>"; 
						echo "<td>".$r['nm_plg']."</td>"; 
						echo "<td>".$r['Divisi']."</td>"; 
						echo "<td>".$r['Pengali']."</td>"; 
						echo "<td>".number_format($r['max_limit'])."</td>"; 
						echo "<td>".$r['User_Name']."</td>";
						echo "<td>".$r['Entry_Time']."</td>";
						echo "<td><a href = '".site_url("MsPengaliLimit/Edit?divisi=".urlencode($r['Divisi'])."&kd_plg=".urlencode($r['kd_Plg']))."'><button><i class='glyphicon glyphicon-pencil'></i></button></a> "; 
						echo " <a href = '#' data-href='".site_url('MsPengaliLimit/Delete?divisi='.urlencode($r['Divisi']).'&kd_plg='.urlencode($r['kd_Plg']))."' data-toggle='modal' data-target='#confirm-delete2' data-record-title='".$r['kd_Plg']." - ".$r['nm_plg']." (".$r['Divisi'].")'><button><i class='glyphicon glyphicon-trash'></i></button></a></td>"; 
						echo "</tr>";
						$i += 1;
					}
				echo "</tbody>"; ?>
			</table>
		</div>
	</div>
</div> <!-- /container -->


<div class="modal fade" id="confirm-delete1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Delete Confirmation
			</div>
            <div class="modal-body">
				<p>Hapus Pengali Limit Wilayah <b><i class="title1"></i></b> ?</p>
				<p>Ingin Lanjutkan?</p>
				<!-- <p class="debug-url"></p> -->
			</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger btn-ok1">Delete</a>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="confirm-delete2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Delete Confirmation
			</div>
            <div class="modal-body">
				<p>Hapus Pengali Limit Toko <b><i class="title2"></i></b> ?</p>
				<p>Ingin Lanjutkan?</p>
				<!-- <p class="debug-url"></p> -->
			</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger btn-ok2">Delete</a>
			</div>
		</div>
	</div>
</div>

<script>
	$('#confirm-delete1').on('show.bs.modal', function(e) {
		$(this).find('.btn-ok1').attr('href', $(e.relatedTarget).data('href'));
		$('.debug-url1').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
	});
	
	$('#confirm-delete1').on('show.bs.modal', function(e) {
		var data = $(e.relatedTarget).data();
		$('.title1', this).text(data.recordTitle);
	});
	$('#confirm-delete2').on('show.bs.modal', function(e) {
		$(this).find('.btn-ok2').attr('href', $(e.relatedTarget).data('href'));
		$('.debug-url2').html('Delete URL: <strong>' + $(this).find('.btn-ok2').attr('href') + '</strong>');
	});
	
	$('#confirm-delete2').on('show.bs.modal', function(e) {
		var data = $(e.relatedTarget).data();
		$('.title2', this).text(data.recordTitle);
	});
</script>
