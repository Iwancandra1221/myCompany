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
	
	var monthShortNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ];
	
	var LoadMerkList = function() {
		let divisi = $("#filterDivisi").val();
		$(".loading").show();
		 $.ajax({
            url: "<?php echo site_url('TargetFokus/GetMerkList'); ?>",
            type: 'POST',
			data:{
				divisi 	: divisi
			},
            dataType: 'JSON',
            success: function (data) {
				if (data.error != undefined) {
					$("#filterMerk").html("<option value='all'>ALL</option>");
				} else {
					var x = "<option value='all'>ALL</option>";
					for(var i=0; i<data.length; i++) {	
						x = x + "<option value='"+data[i].MERK+"'>"+data[i].MERK+"</option>";
					}
					$("#filterMerk").html(x);
				}	
				$(".loading").hide();
            },
        });
	}
	
	
	$(document).ready(function() {
		
		$('#filterDivisi').change(function () {
			$("#example").dataTable().fnFilter($(this).val(), 2);
			LoadMerkList();
			$("#example").dataTable().fnFilter('', 3);
		});
		
		$('#filterMerk').change(function () {
			$("#example").dataTable().fnFilter($(this).val(), 3);
		});
		
		$('#filterKategori').change(function () {
			$("#example").dataTable().fnFilter($(this).val(), 4); 
		});
		
		$('#filterStartDate').change(function () {
			// $("#example").dataTable().fnFilter($(this).val(), 5);
			var dt_to = new Date($(this).val());
			var d = dt_to.getDate();
			d = ((d<10) ? '0' : '') + d;
			var m =  dt_to.getMonth();
			m += 1;  // JavaScript months are 0-11
			m = ((m<10) ? '0' : '') + m;
			var y = dt_to.getFullYear();
			// console.log(y+'-'+m+'-'+d);
			$("#example").dataTable().fnFilter(y+'-'+m+'-'+d, 5);
		});
	
		$('#example').DataTable({
			"pageLength": 10,
			"lengthMenu": [
			[5, 10, 20, 50, 100, -1],
			[5, 10, 20, 50, 100, "All"]
			],
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false },
			{
                targets: [6], render: function(data) {
				  var t = new Date(data);
					return ((t.getDate()<10) ? '0' : '') + t.getDate() + ' ' + monthShortNames[t.getMonth()] + ' ' + t.getFullYear();
				}
            },
			{
                targets: [7], render: function(data) {
				  var t = new Date(data);
					return ((t.getDate()<10) ? '0' : '') + t.getDate() + ' ' + monthShortNames[t.getMonth()] + ' ' + t.getFullYear();
				}
            },
			],
			"order": [[6, 'desc']],
			"processing": true,
			"serverSide": true,
			"autoWidth": false,
			"ajax": "<?php echo site_url('TargetFokus/GetAllTargetFokus') ?>",
			"language": {
				"lengthMenu": "Menampilkan _MENU_ Data per halaman",
				"zeroRecords": "Maaf, Data tidak ada",
				"info": "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
				"infoEmpty": "Menampilkan 0 s/d 0 dari 0 data",
				"search": "Pencarian",
				"infoFiltered": "",
				"paginate": {
					"sPrevious": "Sebelumnya",
					"sNext": "Berikutnya"
				}
			},
		});
		
	});
		
</script>

<div style="position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;">
	<div style="padding: 5px;">
		<?php
			if (isset($_GET['insertsuccess'])) {
				echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Inserted <strong>Successfully !</strong></div>";
			}
			
			if (isset($_GET['updatesuccess'])) {
				echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Updated <strong>Successfully !</strong></div>";
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
	<div class="title">Item Fokus</div>
    <?php //if($access->can_create == 1) { ?>
	
	<a href="<?php echo base_url('TargetFokus/Add') ?>" class="btn btn-dark" style="float:right" target="_blank">Tambah Item Fokus</a>
	
	
	FILTER DATA
	<form action="<?php echo site_url('TargetFokus/SimpanTargetFokus') ?>" method="POST" target="_blank">
		<table cellpadding="5" width="50%">
			<tr>
				<td valign="top" width="30%">Divisi :</td>
				<td valign="top" width="*">
					<select id="filterDivisi" name="filterDivisi" required>
						<option value=''>ALL</option>
						<?php 
							for($i=0;$i<count($divisions);$i++) {
								// echo("<option value='".str_replace("&","",$divisions[$i]["DIVISI"])."'>".$divisions[$i]["DIVISI"]."</option>");
								echo("<option value='".$divisions[$i]["DIVISI"]."'>".$divisions[$i]["DIVISI"]."</option>");
							}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td valign="top">Merk : </td>
				<td valign="top"> 
					<select id="filterMerk" name="filterMerk">
						<option value=''>ALL</option>
						<?php 
							for($i=0;$i<count($merks);$i++) {
								// echo("<option value='".$merks[$i]["MERK"]."'>".$merks[$i]["MERK"]."</option>");
							} ?>
					</select>
				</td>
			</tr>
			<tr>
				<td valign="top">Group Item Fokus : </td>
				<td valign="top">
					<select id="filterKategori" name="filterKategori">
						<option value=''>ALL</option>
						<?php 
							for($i=0;$i<count($kategoris);$i++) {
								echo("<option value='".$kategoris[$i]["KATEGORI"]."'>".$kategoris[$i]["KATEGORI"]."</option>");
							}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td valign="top">Tgl Awal: </td>
				<td valign="top">
					<!--input type='text' name='dp1' id='dp1' style="width:100px" autocomplete="off" placeholder="dd-MM-yyyy" required-->
					<select id="filterStartDate" name="filterStartDate">
						<?php 
							for($i=0;$i<count($startdates);$i++) {
								echo("<option value='".$startdates[$i]["TGL_MULAI"]."'>".$startdates[$i]["TGL_MULAI"]."</option>");
							} ?>
					</select>
				</td>
			</tr>
			<tr>
				<td valign="top"></td>
				<td valign="top">
					<input type="submit" class="btn btn-dark" id="btnExport" name="export" value="Export Data" onclick="javascript:ExportData()">
				</td>
			</tr>
			<tr>
				<td valign="top" colspan="2">
					<em><small>Export data berdasarkan filter divisi dan tgl awal</small></em>
				</td>
				<td valign="top">
				</td>
			</tr>
		</table>
	</form>
	
    <?php //} ?>
    <table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
			<thead>
			<tr>
			 <th class="no-sort">No</th>
			 <th>Kode Barang</th>
			 <th>Nama Barang</th>
			 <th>Divisi</th>
			 <th>Merk</th>
			 <th>Kategori Insentif</th>
			 <th>Tgl Awal</th>
			 <th>Tgl Akhir</th>
			 <th>CreatedBy</th>
			 <th>CreatedDate</th>
			 <th class="no-sort">Delete</th>
			</tr>
			</thead>
			<tbody>
			</tbody>
	</table>
	
	
	
</div> <!-- /container -->
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Delete Confirmation
			</div>
            <div class="modal-body">
				<p>Hapus Item Fokus untuk kode <b><i class="title"></i></b> ?</p>
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
