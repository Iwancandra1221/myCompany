<script>
    $(document).ready(function() {
		$("#divBlacklist").hide();
		$("#tabUtama").addClass("selected-tab");
		$("#tabProses").click(function(){
			$("#tabUtama").removeClass("selected-tab");
			$("#divGenerate").hide();
			$("#tabProses").addClass("selected-tab");
			$("#divBlacklist").show();
			table_shopboard_approved.ajax.reload();
			table_shopboard_final.ajax.reload();
		});
		$("#tabUtama").click(function(){
			$("#tabProses").removeClass("selected-tab");
			$("#divBlacklist").hide();
			$("#tabUtama").addClass("selected-tab");
			$("#divGenerate").show();
			table_shopboard.ajax.reload();
		});	
		
		//1=UpdatePO, 2=FinalizePO
		$("#divFinalizePO").hide();
		$("#tabUpdatePO").addClass("selected-tab2");
		$("#tabFinalizePO").click(function(){
			$("#tabUpdatePO").removeClass("selected-tab2");
			$("#divUpdatePO").hide();
			$("#tabFinalizePO").addClass("selected-tab2");
			$("#divFinalizePO").show();
		});
		$("#tabUpdatePO").click(function(){
			$("#tabFinalizePO").removeClass("selected-tab2");
			$("#divFinalizePO").hide();
			$("#tabUpdatePO").addClass("selected-tab2");
			$("#divUpdatePO").show();
		});	
	});
</script>  

<style>
	/*
	.glyphicon { font-size:20px;margin-left:5px;margin-right:5px; }
	*/
	.merah { color:#c91006; }
	.hijau { color:#0ead05;}
	.generate-button {
	margin-left: 0px;
	margin-bottom:30px;
	}
	input { color:#000!important;}
	button { font-size: 14px; }
	.hd-tab { 
	float:left;width:200px;
	padding:10px;
	font-size:12px; 
	border-left:1px solid #ccc;border-top:1px solid #ccc;border-right:1px solid #ccc; 
	border-radius:8px 8px 0 0;
	cursor: pointer;
	font-weight:bold;
	}
	#divGenerate, #divBlacklist {
	border:1px solid #ccc;
	padding:15px;
	}
	.selected-tab {
	background-color: #2C458F;
	color: #fff;
	}
	
	.hd-tab2 { 
	padding:10px;
	font-size:12px; 
	cursor: pointer;
	font-weight:bold;
	color: #aaa;
	}
	.selected-tab2 {
	border-bottom: 3px solid #2C458F;
	color: #2C458F;
	}
	
	.select2-container .select2-selection--single, .select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 32px;
		height: 32px;
	}
	
	.select2-container ul li {
		background-color: #fff !important;
		color: #000  !important;
	}
	.select2-container ul li {
		background-color: #fff !important;
		color: #000  !important;
	}

	.select2-results__option--highlighted.select2-results__option--selectable {
		background-color: #5897fb !important;
		color: white !important;
	}

	/*
	.form-control, .btn{
		height:28px;
		line-height:28px;
	}
	.btn{
		padding-top:3px;
	}
	*/
	
</style>

<div class="container">
	<div class="page-title">SHOPBOARD</div>
	<div>
		<div id="tabUtama" class="hd-tab fs-4"><center>Data Toko</center></div>
		<div id="tabProses" class="hd-tab fs-4"><center>Dalam Proses PO <span id="span_all_data_proses" class="badge badge-primary">0</span></center></div>
	</div>
	<div style="clear:both;"></div>
	<div id="divGenerate">
		
		<div class="row px5 mb0">
			<div class="col-6">
				
				<div class="form-group">
					<label>Pilih Cabang</label>
					<select id="cabang" class="form-control select2" onchange="javascript:shopboard_filter()">
						<option value="">All Cabang</option>
						<?php
							foreach($branch as $row){
								echo '<option value="'.$row['branch_code'].'">'.$row['branch_name'].'</option>'; 
							}
						?>
					</select>
				</div>
				
			</div>
			<div class="col-6">
				<label>Filter By Expired Date</label><br>
				<?php
					$start_date = date('d-M-Y');
					$end_date = date('d-M-Y', strtotime($start_date. ' + 1 months'));
					$end_date = date('d-M-Y', strtotime($end_date. ' + 15 days'));
				?>
				
				<input type="text" id="filter_periode_start" class="form-control filter_date" style="display:inline;width:200px" value="<?php echo $start_date ?>" onblur="javascript:shopboard_filter()"> 
				sd 
				<input type="text" id="filter_periode_end" class="form-control filter_date" style="display:inline;width:200px" value="<?php echo $end_date ?>" onblur="javascript:shopboard_filter()"> 
				<!--div style="width:200px; display:inline-block">
					<div class="input-group">
					<input type="text" name="start_date" id="start_date" class="form-control dtpicker filter_date" value="<?php //echo $start_date  ?>">
					<span class="input-group-addon" id="dtbtn">
					<i class="glyphicon glyphicon-calendar"></i>
					</span>
					</div>
					</div>
					<label>
					sd
					</label>
					
					<div style="width:200px; display:inline-block">
					<div class="input-group">
					<input type="text" name="end_date" id="end_date" class="form-control dtpicker filter_date" value="<?php //echo $end_date  ?>">
					<span class="input-group-addon" id="dtbtn">
					<i class="glyphicon glyphicon-calendar"></i>
					</span>
					</div>
				</div-->
				<a href="javascript:set_default()" class="fs-3"><i class="glyphicon glyphicon-repeat fs-3"></i> Default</a>
			</div>
		</div>
		<div class="row px5 mb0">
			
			<div class="col-4">
				<span class="fs-8"><span id="span_cabang">All Cabang</span> - Total <b><span id="span_all_data">0</span></b> Data</span>
			</div>
			<div class="col-8 text-right">
				<?php if($_SESSION['logged_in']['userLevel']!='STAFF') { ?>
				<a href="<?php echo base_url() ?>shopboard/import" target="_blank" class="btn btn-light-dark">
					<i class='glyphicon glyphicon-upload'></i> Import Data
				</a>
				<?php } ?>
				<button class="btn btn-light-dark" onclick="tambah_toko()">
					<i class='glyphicon glyphicon-plus-sign'></i> Tambah Toko Baru
				</button>
				<form id="form_excel" action="<?php echo base_url() ?>shopboard/excel" method="POST" target="_blank" style="display:inline">
				<button type="button" class="btn btn-light-dark" onclick="download_excel()">
					<i class='glyphicon glyphicon glyphicon-download'></i> Download Excel
				</button>
				</form>
				<button class="btn btn-primary-dark" onclick="javascript:perpanjangan_po()">
					<i class='glyphicon glyphicon-open-file'></i> Ajukan Perpanjangan
				</button>
			</div>
		</div>
		<div class="row px5 mb0">
			
			<div class="col-6">
				<span class="fs-8" style="margin-right:20px">
					<input type="checkbox" id="chk_alldata" class="chk_show"> <label for="chk_alldata" class="fw-normal cs-pointer">Show All Data</label>
				</span>
				<span class="fs-8">
					<input type="checkbox" id="chk_inactive" class="chk_show"> <label for="chk_inactive" class="fw-normal cs-pointer">Inactive</label>
				</span>
			</div>
			<div class="col-6 text-right fs-8">
				<span id="selected_data" class="fw-bold">0</span> Selected Data
			</div>
		</div>
		
		<form id="form_pengajuan" action="<?php echo base_url() ?>shopboard/pengajuan" method="POST">
			<table id="table_shopboard" class="table table-striped table-bordered" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th>Supplier</th>
						<th>Cabang</th>
						<th>Wilayah</th>
						<th>Nama Toko</th>
						<th>Alamat</th>
						<th>Kota</th>
						<th>No PO</th>
						<th>Merk</th>
						<th>Ukuran Shopboard</th>
						<th>Tgl Expired</th>
						<th></th>
						<th>Select All</th>
					</tr>
					<tr>
						<th class="no-sort"><input type="text" class="form-control shopboard_search" data-col="0" placeholder="Cari..."></th>
						<th class="no-sort"></th>
						<th class="no-sort"></th>
						<th class="no-sort"><input type="text" class="form-control shopboard_search" data-col="2" placeholder="Cari..."></th>
						<th class="no-sort"><input type="text" class="form-control shopboard_search" data-col="3" placeholder="Cari..."></th>
						<th class="no-sort"><input type="text" class="form-control shopboard_search" data-col="4" placeholder="Cari..."></th>
						<th class="no-sort"><input type="text" class="form-control shopboard_search" data-col="5" placeholder="Cari..."></th>
						<th class="no-sort"><input type="text" class="form-control shopboard_search" data-col="6" placeholder="Cari..."></th>
						<th class="no-sort"><input type="text" class="form-control shopboard_search" data-col="7" placeholder="Cari..."></th>
						<th class="no-sort"></th>
						<th class="no-sort"></th>
						<th class="no-sort"><center><input type="checkbox" id="chk_all"></center></th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</form>
	</div>
	
	<div id="divBlacklist">
		
		
		<div class="mb10">
			<label id="tabUpdatePO" class="hd-tab2 fs-3">Update PO (<span id="span_all_data_approved">0</span>)</label>
			<label id="tabFinalizePO" class="hd-tab2 fs-3">Finalize PO (<span id="span_all_data_final">0</span>)</label>
		</div>
		
		<div style="clear:both;"></div>
		<div id="divUpdatePO">
			<table id="table_shopboard_approved" class="table table-striped table-bordered" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th>Supplier</th>
						<th>Cabang</th>
						<th>Wilayah</th>
						<th>Nama Toko</th>
						<th>Alamat</th>
						<th>Kota</th>
						<th>No PO</th>
						<th>Merk</th>
						<th>Ukuran Shopboard</th>
						<th>Tgl Expired</th>
						<th></th>
					</tr>
					<tr>
						<th><input type="text" class="form-control shopboard_approved_search" data-col="0" placeholder="Cari..."></th>
						<th></th>
						<th></th>
						<th><input type="text" class="form-control shopboard_approved_search" data-col="2" placeholder="Cari..."></th>
						<th><input type="text" class="form-control shopboard_approved_search" data-col="3" placeholder="Cari..."></th>
						<th><input type="text" class="form-control shopboard_approved_search" data-col="4" placeholder="Cari..."></th>
						<th><input type="text" class="form-control shopboard_approved_search" data-col="5" placeholder="Cari..."></th>
						<th><input type="text" class="form-control shopboard_approved_search" data-col="6" placeholder="Cari..."></th>
						<th><input type="text" class="form-control shopboard_approved_search" data-col="7" placeholder="Cari..."></th>
						<th></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
		
		<div id="divFinalizePO">
			<div class="row px5 mb0">
				<div class="col-6">
					<span id="selected_data_final" class="fs-8 fw-bold">0</span> <span class="fs-8"> Toko Selected</span>
				</div>
				<div class="col-6 text-right">
					<button class="btn btn-primary-dark fs-6" onclick="javascript:finalize_po()">
						<i class='glyphicon glyphicon-floppy-saved'></i> Finalize PO
					</button>
				</div>
			</div>
			
			
			<form id="form_finalize" action="<?php echo base_url() ?>shopboard/finalize" method="POST">
				<table id="table_shopboard_final" class="table table-striped table-bordered" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th>Supplier</th>
							<th>Cabang</th>
							<th>Wilayah</th>
							<th>Nama Toko</th>
							<th>Alamat</th>
							<th>Kota</th>
							<th>No PO</th>
							<th>Merk</th>
							<th>Ukuran Shopboard</th>
							<th>Periode Perpanjangan</th>
							<th></th>
							<th width="80px">Select All</th>
						</tr>
						<tr>
							<th><input type="text" class="form-control shopboard_final_search" data-col="0" placeholder="Cari..."></th>
							<th></th>
							<th></th>
							<th><input type="text" class="form-control shopboard_final_search" data-col="2" placeholder="Cari..."></th>
							<th><input type="text" class="form-control shopboard_final_search" data-col="3" placeholder="Cari..."></th>
							<th><input type="text" class="form-control shopboard_final_search" data-col="4" placeholder="Cari..."></th>
							<th><input type="text" class="form-control shopboard_final_search" data-col="5" placeholder="Cari..."></th>
							<th><input type="text" class="form-control shopboard_final_search" data-col="6" placeholder="Cari..."></th>
							<th><input type="text" class="form-control shopboard_final_search" data-col="7" placeholder="Cari..."></th>
							<th></th>
							<th></th>
							<th width="80px"><center><input type="checkbox" id="chk_all_final"></center></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</form>
		</div>
		
	</div>  
</div> <!-- /container -->

<div class="modal fade" id="modal_add" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="z-index:1051">
	<div class="modal-dialog">
		<div class="modal-content">
			<form id="form_add" action="<?php echo base_url().'shopboard/save' ?>" class="form-horizontal">
				<input type="hidden" name="act" id="act" value=""> 
				<input type="hidden" name="id_po" id="id_po" value=""> 
				<input type="hidden" name="new" id="new" value=""> 
				<input type="hidden" name="id_reklame" id="id_reklame" value=""> 
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" class="fs-6" style="color:#000">&times;</span></button>
					<h4 class="modal-title fs-5 fw-bold">Detail PO</h4>
				</div>				
				<div class="modal-body">
					<div class="form-group mb0">
						<label class="col-3 fw-normal">No PO</label>
						<div class="col-9">
							<input type="text" name="no_po" id="no_po" class="form-control form-edit" placeholder="No PO" required>
						</div>
					</div>
					
					<div class="form-group mb0">
						<label class="col-3 fw-normal">Periode Pajak Reklame</label>
						<div class="col-3">
							<input type="text" name="periode_start" id="periode_start" class="form-control form-edit" placeholder="dd/mm/yyyy" autocomplete="off" required>
						</div>
						<div class="col-1">
							sd
						</div>
						<div class="col-3">
							<input type="text" name="periode_end" id="periode_end" class="form-control form-edit" placeholder="dd/mm/yyyy" autocomplete="off" required>
						</div>
					</div>
					
					<div class="form-group mb0">
						<label class="col-3 fw-normal">Supplier</label>
						<div class="col-9">
							<select name="supplier" id="supplier" class="form-control select2 form-edit" style="width:100%" required>
								<option value="">Pilih Supplier</option>
								<?php
									foreach($supplier as $row){
										echo '<option value="'.$row['supplier'].'">'.$row['supplier'].'</option>'; 
									}
								?>
							</select>
						</div>
					</div>
					
					<div class="form-group mb0">
						<label class="col-3 fw-normal">Cabang</label>
						<div class="col-9">
							<select name="branchcode" id="branchcode" class="form-control select2 form-edit" style="width:100%" onchange="javascript:set_wilayah_option()" required>
								<option value="">Pilih Cabang</option>
								<?php
									foreach($branch as $row){
										echo '<option value="'.$row['branch_code'].'">'.$row['branch_name'].'</option>'; 
									}
								?>
							</select>
						</div>
					</div>
					
					<div class="form-group mb0">
						<label class="col-3 fw-normal">Wilayah</label>
						<div class="col-9">
							<select name="wilayah" id="wilayah" class="form-control select2 form-edit" style="width:100%" required>
								<option value="">Pilih Wilayah</option>
								<?php
									foreach($wilayah as $row){
										echo '<option value="'.$row['kd_lokasi'].'">'.$row['wilayah'].'</option>'; 
									}
								?>
							</select>
						</div>
					</div>
					
					<div class="form-group mb0">
						<label class="col-3 fw-normal">Nama Toko</label>
						<div class="col-9">
							<input type="text" name="nama_toko" id="nama_toko" class="form-control form-edit" placeholder="Nama Toko" required>
						</div>
					</div>
					
					<div class="form-group mb0">
						<label class="col-3 fw-normal">Alamat</label>
						<div class="col-9">
							<textarea name="alamat" id="alamat" class="form-control form-edit" required></textarea>
						</div>
					</div>
					<div class="form-group mb0">
						<label class="col-3 fw-normal">Kota</label>
						<div class="col-9">
							<input type="text" name="kota" id="kota" class="form-control form-edit" placeholder="Kota" required>
						</div>
					</div>
					<br>
					<div class="border10 p20">
						<!--div class="form-group mb0">
							<label class="col-3 fw-normal">Merk & Ukuran</label>
							<div class="col-3">
								<select name="merk[]" id="merk1" class="form-control form-edit select2merk" placeholder="Merk" required></select>
							</div>
							<div class="col-4">
								<input type="text" name="ukuran[]" id="ukuran1" class="form-control form-edit" placeholder="0 m X 0 m X 0 sisi" required>
							</div>
							<div class="col-2">
							<button type="button" class="btn btn-light-dark form-edit border10 delete_merk"><i class="glyphicon glyphicon-trash fs-4 color-danger"></i></button>
							</div>
						</div-->
						<div class="form-group mb0 px10">
							<label class="col-4 fw-normal mb0 px10">Merk</label>
							<label class="col-6 fw-normal mb0 px10">Ukuran</label>
						</div>
						<div id="div_merk_ukuran">
						</div>
						<hr>
						<center>
						<button type="button" class="btn btn-light-dark form-edit border20" onclick="add_merk()"><i class="glyphicon glyphicon-plus fs-4 color-primary"></i></button>
						</center>
						
					</div>
					
				</div>
				<div class="modal-footer">
					<button type="submit" id="btn-update" class="btn btn-primary-dark btn-edit fs-3 w100 mx0 mb10"><i class="glyphicon glyphicon-floppy-disk"></i> Update PO</button>
					<button type="submit" id="btn-save" class="btn btn-primary-dark btn-edit fs-3 w100 mx0 mb10"><i class="glyphicon glyphicon-floppy-disk"></i> Save</button>
					
					<button type="button" id="btn-edit" class="btn btn-light-dark btn-edit fs-3 w100 mx0 mb10" onclick="edit_po()"><i class="glyphicon glyphicon-pencil"></i> Edit PO</button>
					
					<button type="button" id="btn-batal" class="btn btn-danger-dark btn-edit fs-3 w100 mx0 mb10" onclick="pembatalan_po()"><i class="glyphicon glyphicon-ban-circle"></i> Batalkan PO</button>
					
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_toko" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="z-index:1051">
	<div class="modal-dialog">
		<div class="modal-content">
			<form id="form_toko" class="form-horizontal" action="<?php echo base_url() ?>shopboard/toko">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" class="fs-6" style="color:#000">&times;</span></button>
					<h4 class="modal-title fs-5 fw-bold">Data Toko</h4>
				</div>
				
				<div class="modal-body">
					<div class="form-group mb0">
						<label class="col-3 fw-normal">Cabang</label>
						<div class="col-9">
							<input type="hidden" name="id_reklame" id="toko_id_reklame" value="">
							<select name="branchcode" id="toko_cabang" class="form-control" onchange="set_wilayah_toko_option()" required>
								<option value="">Pilih Cabang</option>
								<?php
									foreach($branch as $row){
										echo '<option value="'.$row['branch_code'].'">'.$row['branch_name'].'</option>'; 
									}
								?>
							</select>
						</div>
					</div>
					
					<div class="form-group mb0">
						<label class="col-3 fw-normal">Wilayah</label>
						<div class="col-9">
							<select name="wilayah" id="toko_wilayah" class="form-control" required>
								<option value="">Pilih Wilayah</option>
							</select>
						</div>
					</div>
					
					<div class="form-group mb0">
						<label class="col-3 fw-normal">Nama Toko</label>
						<div class="col-9">
							<input type="text" name="nama_toko" id="toko_nama"  class="form-control" placeholder="Nama Toko" required>
						</div>
					</div>
					
					<div class="form-group mb0">
						<label class="col-3 fw-normal">Alamat</label>
						<div class="col-9">
							<textarea name="alamat" id="toko_alamat" class="form-control" required></textarea>
						</div>
					</div>
					<div class="form-group mb0">
						<label class="col-3 fw-normal">Kota</label>
						<div class="col-9">
							<input name="kota" id="toko_kota"  type="text" class="form-control" placeholder="Kota" required>
						</div>
					</div>
					
				</div>
				<div class="modal-footer">
					<button class="btn btn-primary-dark fs-3 w100"><i class="glyphicon glyphicon-floppy-disk"></i> Save</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_view" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="z-index:1050">
	<div class="modal-dialog">
		<div class="modal-content">
			<form class="form-horizontal">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" class="fs-6" style="color:#000">&times;</span></button>
					<h4 class="modal-title fs-5 fw-bold">History PO Shopboard</h4>
				</div>
				<div class="modal-body">
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title fs-5 fw-bold">Konfirmasi Hapus Data PO</h4>
			</div>
			<div class="modal-body">
				<p class="fs-6">Apakah Anda yakin ingin menghapus PO ini?</p>
			</div>
			<div class="modal-footer">
				<div class="row">
					<div class="col-6">
						<button type="button" class="btn btn-light-dark w100" data-dismiss="modal">Cancel</button>
					</div>
					<div class="col-6">
						<form id="form_delete" action="<?php echo base_url() ?>shopboard/delete_po">
						<input type="hidden" id="delete_id" name="id">
						<button type="submit" class="btn btn-danger-dark w100">Delete</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="confirm-batal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="z-index:1051">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title fs-5 fw-bold">Konfirmasi Pembatalan PO</h4>
			</div>
			<form id="form_batal" action="<?php echo base_url() ?>shopboard/batal_po">
			<div class="modal-body">
				<p class="fs-6">Apakah Anda yakin ingin membatalkan pengajuan PO ini?</p>
				<p class="fs-4">Alasan Batal:</p>
				<textarea name="cancelled_note" class="form-control" required></textarea>
			</div>
			<div class="modal-footer">
				<div class="row">
					<div class="col-6">
						<button type="button" class="btn btn-light-dark w100" data-dismiss="modal">Cancel</button>
					</div>
					<div class="col-6">
						<input type="hidden" id="batal_id" name="id">
						<button type="submit" class="btn btn-danger-dark w100">Batalkan PO</button>
					</div>
				</div>
			</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="confirm-perpanjangan" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title fs-5 fw-bold">Konfirmasi Pengajuan Perpanjangan</h4>
			</div>
			<div class="modal-body">
				<p class="fs-6">Apakah Anda yakin ingin melakukan pengajuan perpanjangan?</p>
				<small><em>Pengajuan PO perpanjangan akan dikirim ke email kajul cabang terkait.</em></small>
			</div>
			<div class="modal-footer">
				<div class="row">
					<div class="col-6">
						<button type="button" class="btn btn-light-dark w100" data-dismiss="modal">Cancel</button>
					</div>
					<div class="col-6">
						<button type="button" id="btn_kirim_pengajuan" class="btn btn-primary-dark w100">Kirim Pengajuan</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="confirm-deactivate" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title fs-5 fw-bold">Konfirmasi Deactivate</h4>
			</div>
			<form id="form_deactivate" action="<?php echo base_url() ?>shopboard/deactivate">
			<div class="modal-body">
				<p class="fs-6">Apakah Anda yakin ingin menonaktifkan toko ini?</p>
				<small><em>Toko yang tidak aktif tidak melakukan proses PO.</em></small>
				<input type="hidden" id="deactivate_id" name="id">
				<textarea name="catatan" class="form-control" required></textarea>
			</div>
			<div class="modal-footer">
				<div class="row">
					<div class="col-6">
						<button type="button" class="btn btn-light-dark w100" data-dismiss="modal">Cancel</button>
					</div>
					<div class="col-6">
						<button type="submit" class="btn btn-danger-dark w100">Deactivate</button>
					</div>
				</div>
			</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="confirm-reactivate" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title fs-5 fw-bold">Konfirmasi Reactivate</h4>
			</div>
			<form id="form_reactivate" action="<?php echo base_url() ?>shopboard/reactivate">
			<div class="modal-body">
				<p class="fs-6">Apakah Anda yakin ingin mengaktifkan toko ini?</p>
				<small><em>Dengan mengaktifkan toko, proses PO dapat diproses kembali.</em></small>
				<input type="hidden" id="reactivate_id" name="id">
			</div>
			<div class="modal-footer">
				<div class="row">
					<div class="col-6">
						<button type="button" class="btn btn-light-dark w100" data-dismiss="modal">Cancel</button>
					</div>
					<div class="col-6">
						<button type="submit" class="btn btn-danger-dark w100">Reactivate</button>
					</div>
				</div>
			</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="confirm-finalize" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title fs-5 fw-bold">Konfirmasi Finalize PO</h4>
			</div>
			<div class="modal-body">
				<p class="fs-6">Apakah Anda yakin ingin melakukan finalize untuk pengajuan perpanjangan ini?</p>
				<p class="fs-6">Pastikan semua data PO pada semua toko telah benar.</p>
			</div>
			<div class="modal-footer">
				<div class="row">
					<div class="col-6">
						<button type="button" class="btn btn-light-dark w100" data-dismiss="modal">Cancel</button>
					</div>
					<div class="col-6">
						<button type="button" id="btn_finalize" class="btn btn-primary-dark w100">Finalize PO</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="confirm-batal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title fs-5 fw-bold">Konfirmasi Pembatalan PO</h4>
			</div>
			<div class="modal-body">
				<p class="fs-6">Apakah Anda yakin ingin membatalkan pengajuan PO ini?</p>
			</div>
			<div class="modal-footer">
				<div class="row">
					<div class="col-6">
						<button type="button" class="btn btn-light-dark w100" data-dismiss="modal">Cancel</button>
					</div>
					<div class="col-6">
						<button class="btn btn-primary-dark w100">Batalkan PO</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	//<input type="text" name="merk[]" class="form-control" placeholder="Merk" required>
	var html_merk = '<div class="form-group mb0">'+
		'<!--label class="col-3 fw-normal">Merk & Ukuran</label-->'+
		'<div class="col-4"><select name="merk[]" class="form-control select2merk form-edit" data-value="" style="width:100%" required></select></div>'+
		'<div class="col-6"><input type="text" name="ukuran[]" class="form-control" placeholder="0 m X 0 m X 0 sisi" required></div>'+
		'<div class="col-2"><button type="button" class="btn btn-light-dark border10 delete_merk"><i class="glyphicon glyphicon-trash fs-4 color-danger"></i></button></div>'+
		'</div>';
	
	var start_date =  '<?php echo $start_date ?>';
	var end_date =  '<?php echo $end_date ?>';
	var list_merk =  <?php echo json_encode($merk) ?>;
	var list_wilayah = <?php echo json_encode($wilayah) ?>;
	
	var table_shopboard;
	var table_shopboard_approved;
	
	$(document).ready(function() {
	
		$('#periode_start').datepicker({
			format: "dd-M-yyyy",
			autoclose: true
			}).on('changeDate', function(e) { //changeDate
			var StartDt = $('#periode_start').datepicker('getDate');
			$('#periode_end').datepicker("setStartDate", StartDt);
		});
		
		$('#periode_end').datepicker({
			format: "dd-M-yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var EndDt = $('#periode_end').datepicker('getDate');
			$('#periode_start').datepicker("setEndDate", EndDt);
		});
	
		$('#filter_periode_start').datepicker({
			format: "dd-M-yyyy",
			autoclose: true
			}).on('changeDate', function(e) { //changeDate
			var StartDt = $('#filter_periode_start').datepicker('getDate');
			$('#filter_periode_end').datepicker("setStartDate", StartDt);
		});
		
		$('#filter_periode_end').datepicker({
			format: "dd-M-yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var EndDt = $('#filter_periode_end').datepicker('getDate');
			$('#filter_periode_start').datepicker("setEndDate", EndDt);
		});
		
		
		
		
		$('.chk_show').click(function(event){
			var checked = $(this).is(":checked");
			$('.chk_show').prop('checked', false);
			$(this).prop('checked', checked);
			
			$('.filter_date').prop('disabled', checked);
			shopboard_filter();
		});
		
		$('#chk_all').change(function(event){
			$('.chk_pilih').prop('checked', $(this).is(":checked"));
			var checked = $('.chk_pilih:checked').length;
			$('#selected_data').text(checked);
		});		
		
		$('#chk_all_final').click(function(event){
			$('.chk_pilih_final').prop('checked', $(this).is(":checked"));
			var checked = $('.chk_pilih_final:checked').length;
			$('#selected_data_final').text(checked);
		});
		
		$('.shopboard_search').keyup(function(e){
			var col = $(this).attr('data-col');
			if(e.keyCode == 13) {
				table_shopboard.columns(col).search(this.value).draw();   
			}
		});
		// $('.shopboard_search').blur(function(e){
			// var col = $(this).attr('data-col');
			// table_shopboard.columns(col).search(this.value).draw();  
		// });
		
		$('.shopboard_approved_search').keyup(function(e){
			var col = $(this).attr('data-col');
			if(e.keyCode == 13) {
				table_shopboard_approved.columns(col).search(this.value).draw();   
			}
		});
		
		// $('.shopboard_approved_search').blur(function(e){
			// var col = $(this).attr('data-col');
			// table_shopboard_approved.columns(col).search(this.value).draw();
		// });
		
		$('.shopboard_final_search').keyup(function(e){
			var col = $(this).attr('data-col');
			if(e.keyCode == 13) {
				table_shopboard_final.columns(col).search(this.value).draw();   
			}
		});
		
		// $('.shopboard_final_search').blur(function(e){
		// var col = $(this).attr('data-col');
			// table_shopboard_final.columns(col).search(this.value).draw();
		// });
		
		table_shopboard = $('#table_shopboard').DataTable({
			"pageLength": 10,
			"searching": true,
			"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false }
			],
			"order": [[0, 'asc'],[3, 'asc']],
			"autoWidth": false,
			"processing": true,
			// "ordering": true,
			"serverSide": true,
			"ajax": {
				"url": '<?php echo base_url('shopboard/datatable_shopboard') ?>',
				"type": "GET",
				"datatype": "json",
				"data": function (data) {
					data.is_active = ($('#chk_inactive').prop('checked'))?0:1;
					data.all_data = ($('#chk_alldata').prop('checked'))?1:0;
					data.cabang = $('#cabang').val();
					data.periode_start = $('#filter_periode_start').val();
					data.periode_end = $('#filter_periode_end').val();
				}
			},
			"initComplete": function() {
				$('#table_shopboard_filter input').unbind();
				$('#table_shopboard_filter input').bind('keyup', function(e) {
					if(e.keyCode == 13) {
						table_shopboard.search(this.value).draw();   
					}
				});
				$('#table_shopboard_filter input').bind('blur', function(e) {
					table_shopboard.search(this.value).draw(); 
				});
			},
			"drawCallback": function( settings) {  
				$('#span_all_data').text(table_shopboard.page.info().recordsTotal);
			},
			"dom": '<"top">trlp<"clear">',
			"pagingType": 'full_numbers',
			"language": {
				"paginate": {
					"first": "&#10094;&#10094;",
					"previous": "&#10094;",
					"next": "&#10095;",
					"last": "&#10095;&#10095;"
				}
			}
		});
		
		table_shopboard_approved = $('#table_shopboard_approved').DataTable({
			"pageLength"    : 10,
			"searching"     : true,
			"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false }
			],
			"order": [9, 'asc'],
			"autoWidth": false,
			"processing": true,
			"ordering": false,
			"serverSide": true,
			"ajax": {
				"url": '<?php echo base_url('shopboard/datatable_shopboard_approved') ?>',
				"type": "GET",
				"datatype": "json",
			},
			"initComplete": function() {
				$('#table_shopboard_approved_filter input').unbind();
				$('#table_shopboard_approved_filter input').bind('keyup', function(e) {
					if(e.keyCode == 13) {
						table_shopboard_approved.search(this.value).draw();   
					}
				}); 
			},
			"drawCallback": function( settings) {  
				$('#span_all_data_approved').text(table_shopboard_approved.page.info().recordsTotal);
				$('#span_all_data_proses').text( table_shopboard_approved.page.info().recordsTotal + table_shopboard_final.page.info().recordsTotal);
			},
			"dom": '<"top">trlp<"clear">',
			"pagingType": 'full_numbers',
			"language": {
				"paginate": {
					"first": "&#10094;&#10094;",
					"previous": "&#10094;",
					"next": "&#10095;",
					"last": "&#10095;&#10095;"
				}
			}
		});
		
		table_shopboard_final = $('#table_shopboard_final').DataTable({
			"pageLength"    : 10,
			"searching"     : true,
			"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false }
			],
			"order": [9, 'asc'],
			"autoWidth": false,
			"processing": true,
			"ordering": false,
			"serverSide": true,
			"ajax": {
				"url": '<?php echo base_url('shopboard/datatable_shopboard_final') ?>',
				"type": "GET",
				"datatype": "json",
			},
			"initComplete": function() {
				$('#table_shopboard_final_filter input').unbind();
				$('#table_shopboard_final_filter input').bind('keyup', function(e) {
					if(e.keyCode == 13) {
						table_shopboard_final.search(this.value).draw();   
					}
				}); 
			},
			"drawCallback": function( settings) {  
				$('#span_all_data_final').text(table_shopboard_final.page.info().recordsTotal);
				$('#span_all_data_proses').text( table_shopboard_approved.page.info().recordsTotal + table_shopboard_final.page.info().recordsTotal);
			},
			"dom": '<"top">trlp<"clear">',
			"pagingType": 'full_numbers',
			"language": {
				"paginate": {
					"first": "&#10094;&#10094;",
					"previous": "&#10094;",
					"next": "&#10095;",
					"last": "&#10095;&#10095;"
				}
			}
		});
		
		$("#form_toko").submit(function(event) {
			var act = $(this).attr('action');
			var form_data = new FormData(this);
				$('.loading').show();
				$.ajax({
					data      	: form_data,
					url			: act,
					cache		: false,
					contentType	: false,
					processData	: false,
					type		: 'POST',
					dataType  	: 'json',
					success   	: function(res) {
						console.log(JSON.stringify(res));
						$('.loading').hide();
						if(res.result=='success'){
							$('#modal_toko').modal('hide');
							$('#modal_view').modal('hide');
							table_shopboard.ajax.reload();
						}
						else{
							alert(res.result+'\n'+res.msg.replace(/\\n/g,"\n"));
							$('#modal_toko').modal('hide');
							$('#modal_view').modal('hide');
							table_shopboard.ajax.reload();
						}
					},
					error: function (request, error) {
						console.log(error);
					}
				});
			event.preventDefault();
		});
		
		$("#form_add").submit(function(event) {
			var action = $(this).attr('action');
			var act = $('#act').val();
			var form_data = new FormData(this);
			
			if(!form_data.has("merk[]")){
				alert('Merk & Ukuran wajib diisi!');
				return false;
			}
			
			$('.loading').show();
			$.ajax({
				data      	: form_data,
				url			: action,
				cache		: false,
				contentType	: false,
				processData	: false,
				type		: 'POST',
				dataType  	: 'json',
				success   	: function(res) {
					console.log(JSON.stringify(res));
					$('.loading').hide();
					if(res.result=='success'){
						if(act=='add'){
							table_shopboard.ajax.reload();
						}
						if(act=='update_po'){
							table_shopboard_approved.ajax.reload();
							table_shopboard_final.ajax.reload();
						}
						if(act=='revisi_po'){
							table_shopboard.ajax.reload();
							$('#modal_view').modal('hide');
						}
						$('#modal_add').modal('hide');
					}
					else{
						alert('GAGAL\n'+res.msg.replace(/\\n/g,"\n"));
					}
				},
				error: function (request, error) {
					console.log(error);
				}
			});
			event.preventDefault();
		});
		
		$("#form_pengajuan").submit(function(event) {
			var act = $(this).attr('action');
			var form_data = new FormData(this);
				$('.loading').show();
				$.ajax({
					data      	: form_data,
					url			: act,
					cache		: false,
					contentType	: false,
					processData	: false,
					type		: 'POST',
					dataType  	: 'json',
					success   	: function(res) {
						console.log(JSON.stringify(res));
						$('.loading').hide();
						if(res.result=='success'){
							alert('SUCCESS'+'\n'+res.msg.replace(/\\n/g,"\n"));
							$('#confirm-perpanjangan').modal('hide');
							table_shopboard.ajax.reload();
						}
						else{
							alert('FAILED'+'\n'+res.msg.replace(/\\n/g,"\n"));
							$('#confirm-perpanjangan').modal('hide');
							table_shopboard.ajax.reload();
						}
					},
					error: function (request, error) {
						console.log(error);
					}
				});
			event.preventDefault();
		});
		
		$("#form_delete").submit(function(event) {
			var act = $(this).attr('action');
			var form_data = new FormData(this);
				$('.loading').show();
				$.ajax({
					data      	: form_data,
					url			: act,
					cache		: false,
					contentType	: false,
					processData	: false,
					type		: 'POST',
					dataType  	: 'json',
					success   	: function(res) {
						console.log(JSON.stringify(res));
						$('.loading').hide();
						if(res.result=='success'){
							$('#confirm-delete').modal('hide');
							$('#modal_view').modal('hide');
							table_shopboard.ajax.reload();
						}
						else{
							alert(res.result+'\n'+res.msg.replace(/\\n/g,"\n"));
							$('#confirm-delete').modal('hide');
							$('#modal_view').modal('hide');
							table_shopboard.ajax.reload();
						}
					},
					error: function (request, error) {
						console.log(error);
					}
				});
			event.preventDefault();
		});
		
		$("#form_batal").submit(function(event) {
			var act = $(this).attr('action');
			var form_data = new FormData(this);
				$('.loading').show();
				$.ajax({
					data      	: form_data,
					url			: act,
					cache		: false,
					contentType	: false,
					processData	: false,
					type		: 'POST',
					dataType  	: 'json',
					success   	: function(res) {
						console.log(JSON.stringify(res));
						$('.loading').hide();
						if(res.result=='success'){
							table_shopboard_final.ajax.reload();
							$('#confirm-batal').modal('hide');
							$('#modal_add').modal('hide');
						}
						else{
							alert(res.result+'\n'+res.msg.replace(/\\n/g,"\n"));
							$('#confirm-batal').modal('hide');
							$('#modal_add').modal('hide');
						}
					},
					error: function (request, error) {
						console.log(error);
					}
				});
			event.preventDefault();
		});
		
		$("#form_deactivate").submit(function(event) {
			var act = $(this).attr('action');
			var form_data = new FormData(this);
				$('.loading').show();
				$.ajax({
					data      	: form_data,
					url			: act,
					cache		: false,
					contentType	: false,
					processData	: false,
					type		: 'POST',
					dataType  	: 'json',
					success   	: function(res) {
						console.log(JSON.stringify(res));
						$('.loading').hide();
						if(res.result=='success'){
							$('#confirm-deactivate').modal('hide');
							$('#modal_view').modal('hide');
							table_shopboard.ajax.reload();
						}
						else{
							alert(res.result+'\n'+res.msg.replace(/\\n/g,"\n"));
							$('#confirm-deactivate').modal('hide');
							$('#modal_view').modal('hide');
							table_shopboard.ajax.reload();
						}
					},
					error: function (request, error) {
						console.log(error);
					}
				});
			event.preventDefault();
		});
		
		$("#form_reactivate").submit(function(event) {
			var act = $(this).attr('action');
			var form_data = new FormData(this);
				$('.loading').show();
				$.ajax({
					data      	: form_data,
					url			: act,
					cache		: false,
					contentType	: false,
					processData	: false,
					type		: 'POST',
					dataType  	: 'json',
					success   	: function(res) {
						console.log(JSON.stringify(res));
						$('.loading').hide();
						if(res.result=='success'){
							$('#confirm-reactivate').modal('hide');
							$('#modal_view').modal('hide');
							table_shopboard.ajax.reload();
						}
						else{
							alert(res.result+'\n'+res.msg.replace(/\\n/g,"\n"));
							$('#confirm-reactivate').modal('hide');
							$('#modal_view').modal('hide');
							table_shopboard.ajax.reload();
						}
					},
					error: function (request, error) {
						console.log(error);
					}
				});
			event.preventDefault();
		});
		
		$("#form_finalize").submit(function(event) {
			var act = $(this).attr('action');
			var form_data = new FormData(this);
				$('.loading').show();
				$.ajax({
					data      	: form_data,
					url			: act,
					cache		: false,
					contentType	: false,
					processData	: false,
					type		: 'POST',
					dataType  	: 'json',
					success   	: function(res) {
						console.log(JSON.stringify(res));
						$('.loading').hide();
						if(res.result=='success'){
							table_shopboard_final.ajax.reload();
							$('#confirm-finalize').modal('hide');
						}
						else{
							alert(res.result+'\n'+res.msg.replace(/\\n/g,"\n"));
							table_shopboard_final.ajax.reload();
							$('#confirm-finalize').modal('hide');
						}
					},
					error: function (request, error) {
						console.log(error);
					}
				});
			event.preventDefault();
		});
		
		$('#btn_kirim_pengajuan').click(function(e){
			$('#form_pengajuan').submit();
		});
		
		$('#btn_finalize').click(function(e){
			$('#form_finalize').submit();
		});
		
		$('.select2').select2();
		
		$('.select2merk').select2({ data:list_merk, tags: true });
		console.log('selesai');
	});
		
	$(document).on("click", ".chk_pilih" , function() {		
		var checked = $('.chk_pilih:checked').length;
		if(checked>0){
			$('#chk_all').prop('checked', true);
		}
		else{
			$('#chk_all').prop('checked', false);
		}
		$('#selected_data').text(checked);
	});
	
	$(document).on("click", ".chk_pilih_final" , function() {		
		var checked = $('.chk_pilih_final:checked').length;
		if(checked>0){
			$('#chk_all_final').prop('checked', true);
		}
		else{
			$('#chk_all_final').prop('checked', false);
		}
		$('#selected_data_final').text(checked);
	});
	
	$(document).on("click", ".delete_merk" , function() {		
		$(this).closest(".form-group").remove();
	});
	
	function create_select_merk(){
		$('.select2merk').each(function(i, obj) {
		
			if (!$(obj).data('select2')){
		
			$(this).select2({ data:list_merk, placeholder: "Pilih/ketik Merk", tags: true });
			var v = $(this).attr('data-value');
			// if(v!=''){
				$(this).val(v);
				$(this).trigger('change');
			// }
			}
		});
	}
	
	function shopboard_filter(){
		table_shopboard.ajax.reload();
		$('#chk_all').prop('checked',false).trigger('change');
		$('#span_cabang').text($("#cabang option:selected").text());
	}
	
	function tambah_toko(){
		$('#act').val('add');
		$('.form-edit').prop('disabled',false);
		$('.btn-edit').hide();
		
		$('#supplier').val('').trigger('change');
		$('#branchcode').val('').trigger('change');
		$('#wilayah').val('').trigger('change');
		
		$('#form_add').trigger('reset');
		$('#btn-save').show();
		
		$('#div_merk_ukuran').html('');
		add_merk();
		$('#modal_add').modal('show');
	}
	
	function view_data(id){
		$('.loading').show();
		$.ajax({
			url			: '<?php echo base_url() ?>shopboard/detail/'+id,
			cache		: false,
			contentType	: false,
			processData	: false,
			type		: 'GET',
			dataType  	: 'json',
			success   	: function(res) {
				// console.log(res);
				// console.log(JSON.stringify(res));
				$('.loading').hide();
			
				var html = '';
				
				if(res.header.catatan){
					html +='<div class="border10 p10 mb10 color-danger">'+res.header.catatan+'</div>';
				}
				
				html +=''+
					'<div>'+
					'<span class="fs-4 fw-bold color-primary">Data Toko</span>'+
					'<a href="javascript:edit_toko(\''+res.header.id_reklame+'\',\''+res.header.branchcode+'\',\''+res.header.wilayah+'\',\''+res.header.nama_toko+'\',\''+res.header.alamat+'\',\''+res.header.kota+'\')" class="color-primary float-right mx10">Edit<i class="glyphicon glyphicon-pencil fs-1"></i></a>';
					
				if(res.header.is_active==true){
					html +='<a href="javascript:deactivate(\''+res.header.id_reklame+'\')" class="color-danger float-right mx10">Deactivate<i class="glyphicon glyphicon-ban-circle fs-1"></i></a> ';
				}
				else{
					html +='<a href="javascript:reactivate(\''+res.header.id_reklame+'\')" class="color-success float-right mx10">Reactivate<i class="glyphicon glyphicon-ban-circle fs-1"></i></a>&nbsp;&nbsp;';
				}
				
				html +=''+
				'</div>'+
				'<table class="w100 mb10" style="border-collapse: separate; border-spacing: 0px 5px;">'+
					'<tr><td width="35%">Cabang</td><td width="65%">: <b>'+res.header.cabang+'</b></td></tr>'+
					'<tr><td>Wilayah</td><td>: <b>'+res.header.wilayah+'</b></td></tr>'+
					'<tr><td>Nama Toko</td><td>: <b>'+res.header.nama_toko+'</b></td></tr>'+
					'<tr><td>Alamat</td><td>: <b>'+res.header.alamat+'</b> </td></tr>'+
					'<tr><td>Kota</td><td>: <b>'+res.header.kota+'</b> </td></tr>'+
				'</table>'+
				'<span class="fs-4 fw-bold color-primary">'+res.detail[0].no_po+'</span> '+
				'<button type="button" class="btn btn-light p5" onclick="delete_po(\''+res.detail[0].id+'\')"><i class="glyphicon glyphicon-trash fs-4 color-danger"></i></button>'+
				'<table class="w100 mb10" style="border-collapse: separate; border-spacing: 0px 5px;">'+
					'<tr><td width="35%">Status Perpanjangan</td><td width="65%">: <b>'+res.detail[0].status+'</b> on '+date('d-M-Y H:i',strtotime(res.detail[0].status_date));
					
					if(res.detail[0].approval_status==null || res.detail[0].approval_status=='REJECTED' || res.detail[0].approval_status=='OK'){
						html +='<a href="javascript:revisi_po(\''+res.detail[0].id+'\')" class="color-primary float-right mx10">Edit<i class="glyphicon glyphicon-pencil fs-1"></i></a>';
					}
					html +='</td></tr>';
					if(res.detail[0].final_status){
					html +='<tr><td width="35%"></td><td width="65%">: <b>'+res.detail[0].final_status+'</b> on '+date('d-M-Y H:i',strtotime(res.detail[0].final_date))+'</td></tr>';
					}
					html +='<tr><td>Periode Pajak Reklame</td><td>: <b>'+date('d-M-Y',strtotime(res.detail[0].periode_start))+'</b> sd <b>'+date('d-M-Y',strtotime(res.detail[0].periode_end))+'</b></td></tr>'+
					'<tr><td>Supplier</td><td>: <b>'+res.detail[0].supplier+'</b> </td></tr>'+
					'<tr><td>Merk & Ukuran</td><td>: <b>'+res.detail[0].merk1+', '+res.detail[0].ukuran1+'</b> </td></tr>';
					if(res.detail[0].merk2){
						html +='<tr><td></td><td>: <b>'+res.detail[0].merk2+', '+res.detail[0].ukuran2+'</b> </td></tr>';
					}
					if(res.detail[0].merk3){
						html +='<tr><td></td><td>: <b>'+res.detail[0].merk3+', '+res.detail[0].ukuran3+'</b> </td></tr>';
					}
					if(res.detail[0].merk4){
						html +='<tr><td></td><td>: <b>'+res.detail[0].merk4+', '+res.detail[0].ukuran4+'</b> </td></tr>';
					}
					if(res.detail[0].merk5){
						html +='<tr><td></td><td>: <b>'+res.detail[0].merk5+', '+res.detail[0].ukuran5+'</b> </td></tr>';
					}
				html +='</table>';
				
				if(res.detail.length>1){
				html +='<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">'
					for(i=1;i<res.detail.length;i++){
					html +=''+
						'<div class="panel panel-default">'+
							'<div class="panel-heading" role="tab" id="heading_'+i+'">'+
								'<h4 class="panel-title">'+
									'<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_'+i+'" aria-expanded="true" aria-controls="collapse_'+i+'">'+
										'<span class="fw-bold color-primary">'+res.detail[i].no_po+'</span>'+
									'</a>'+
								'</h4>'+
							'</div>'+
							'<div id="collapse_'+i+'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_'+i+'">'+
								'<div class="panel-body">'+
									'<table class="w100" style="border-collapse: separate; border-spacing: 0px 5px;">'+
										'<tr><td width="35%">Status Perpanjangan</td><td width="65%">: <b>'+res.detail[i].status+'</b> Updated on '+date('d-M-Y H:i',strtotime(res.detail[i].status_date))+'</td></tr>';
										if(res.detail[i].final_status){
										html +='<tr><td width="35%"></td><td width="65%">: <b>'+res.detail[i].final_status+'</b> on '+date('d-M-Y H:i',strtotime(res.detail[i].final_date))+'</td></tr>';
										}
										html +='<tr><td>Periode Pajak Reklame</td><td>: <b>'+date('d-M-Y',strtotime(res.detail[i].periode_start))+'</b> sd <b>'+date('d-M-Y',strtotime(res.detail[i].periode_end))+'</b></td></tr>'+
										'<tr><td>Supplier</td><td>: <b>'+res.detail[i].supplier+'</b> </td></tr>'+
										'<tr><td>Merk & Ukuran</td><td>: <b>'+res.detail[i].merk1+', '+res.detail[i].ukuran1+'</b> </td></tr>';
										if(res.detail[i].merk2){
											html +='<tr><td></td><td>: <b>'+res.detail[i].merk2+', '+res.detail[i].ukuran2+'</b> </td></tr>';
										}
										if(res.detail[i].merk3){
											html +='<tr><td></td><td>: <b>'+res.detail[i].merk3+', '+res.detail[i].ukuran3+'</b> </td></tr>';
										}
										if(res.detail[i].merk4){
											html +='<tr><td></td><td>: <b>'+res.detail[i].merk4+', '+res.detail[i].ukuran4+'</b> </td></tr>';
										}
										if(res.detail[i].merk5){
											html +='<tr><td></td><td>: <b>'+res.detail[i].merk5+', '+res.detail[i].ukuran5+'</b> </td></tr>';
										}
									html +=''+
									'</table>'+
								'</div>'+
							'</div>'+
						'</div>';
					}
				html +='</div>';
				}
				
				$('#modal_view .modal-body').html(html);
				$('#modal_view').modal('show');
			},
			error: function (request, error) {
				console.log(error);
			}
		});
		
	}
	
	function edit_toko(id, cabang, wilayah, nama_toko, alamat, kota){
		$('#toko_id_reklame').val(id);
		$('#toko_cabang').val(cabang).trigger('change');;
		$('#toko_wilayah').val(wilayah.toUpperCase()).trigger('change');;
		$('#toko_nama').val(nama_toko);
		$('#toko_alamat').val(alamat);
		$('#toko_kota').val(kota);
		$('#modal_view').modal('hide');
		$('#modal_toko').modal('show');
	}
	
	function delete_po(id){
		$('#delete_id').val(id);
		$('#confirm-delete').modal('show');
	}
	
	function pembatalan_po(){
		var id = $('#id_po').val();
		$('#batal_id').val(id);
		$('#confirm-batal').modal('show');
	}
	
	function perpanjangan_po(){
		var checked = $('.chk_pilih:checked').length;
		if(checked>0){
			$('#confirm-perpanjangan').modal('show');
		}
	}
	
	function deactivate(id){
		$('#deactivate_id').val(id);
		$('#confirm-deactivate').modal('show');
	}
	
	function reactivate(id){
		$('#reactivate_id').val(id);
		$('#confirm-reactivate').modal('show');
	}
	
	function revisi_po(id){
		$('.loading').show();
		$.ajax({
			url			: '<?php echo base_url() ?>shopboard/detail_po/'+id,
			cache		: false,
			contentType	: false,
			processData	: false,
			type		: 'GET',
			dataType  	: 'json',
			success   	: function(res) {
				console.log(JSON.stringify(res));
				$('.loading').hide();
				
				$('#act').val('revisi_po');
				$('#no_po').val(res.no_po);
				$('#periode_start').val(date('d-M-Y',strtotime(res.periode_start)));
				$('#periode_end').val(date('d-M-Y',strtotime(res.periode_end)));
				
				
				$('#id_reklame').val(res.id_reklame);
				$('#id_po').val(res.id);
				$('#new').val(res.new);
				
				$('#supplier').val(res.supplier).trigger('change');
				$('#branchcode').val(res.branchcode).trigger('change');
				$('#wilayah').val(res.wilayah.toUpperCase()).trigger('change');
				$('#nama_toko').val(res.nama_toko);
				$('#alamat').val(res.alamat);
				$('#kota').val(res.kota);
				// $('#merk1').val(res.merk1);
				// $('#ukuran1').val(res.ukuran1);
				
				var html = '';
				$('#div_merk_ukuran').html(html);
				if(res.merk1){
					html += '<div class="form-group mb0"><div class="col-4"><select name="merk[]" class="form-control select2merk form-edit" data-value="'+res.merk1+'" style="width:100%" required></select></div><div class="col-6"><input type="text" name="ukuran[]" value="'+res.ukuran1+'" class="form-control form-edit" placeholder="0 m X 0 m X 0 sisi" required></div><div class="col-2"><button type="button" class="btn btn-light-dark form-edit border10 delete_merk"><i class="glyphicon glyphicon-trash fs-4 color-danger"></i></button></div></div>';
				}
				if(res.merk2){
					html += '<div class="form-group mb0"><div class="col-4"><select name="merk[]" class="form-control select2merk form-edit" data-value="'+res.merk2+'" style="width:100%" required></select></div><div class="col-6"><input type="text" name="ukuran[]" value="'+res.ukuran2+'" class="form-control form-edit" placeholder="0 m X 0 m X 0 sisi" required></div><div class="col-2"><button type="button" class="btn btn-light-dark form-edit border10 delete_merk"><i class="glyphicon glyphicon-trash fs-4 color-danger"></i></button></div></div>';
				}
				if(res.merk3){
					html += '<div class="form-group mb0"><div class="col-4"><select name="merk[]" class="form-control select2merk form-edit" data-value="'+res.merk3+'"  style="width:100%" required></select></div><div class="col-6"><input type="text" name="ukuran[]" value="'+res.ukuran3+'" class="form-control form-edit" placeholder="0 m X 0 m X 0 sisi" required></div><div class="col-2"><button type="button" class="btn btn-light-dark form-edit border10 delete_merk"><i class="glyphicon glyphicon-trash fs-4 color-danger"></i></button></div></div>';
				}
				if(res.merk4){
					html += '<div class="form-group mb0"><div class="col-4"><select name="merk[]" class="form-control select2merk form-edit" data-value="'+res.merk4+'" style="width:100%" required></select></div><div class="col-6"><input type="text" name="ukuran[]" value="'+res.ukuran4+'" class="form-control form-edit" placeholder="0 m X 0 m X 0 sisi" required></div><div class="col-2"><button type="button" class="btn btn-light-dark form-edit border10 delete_merk"><i class="glyphicon glyphicon-trash fs-4 color-danger"></i></button></div></div>';
				}
				if(res.merk5){
					html += '<div class="form-group mb0"><div class="col-4"><select name="merk[]" class="form-control select2merk form-edit" data-value="'+res.merk5+'" style="width:100%" required></select></div><div class="col-6"><input type="text" name="ukuran[]" value="'+res.ukuran5+'" class="form-control form-edit" placeholder="0 m X 0 m X 0 sisi" required></div><div class="col-2"><button type="button" class="btn btn-light-dark form-edit border10 delete_merk"><i class="glyphicon glyphicon-trash fs-4 color-danger"></i></button></div></div>';
				}
				
				$('#div_merk_ukuran').append(html);
				create_select_merk();
				
				$('.form-edit').prop('disabled',false);
				$('.btn-edit').hide();
				$('#btn-update').show();
				$('#modal_add').modal('show');
				
			},
			error: function (request, error) {
				console.log(error);
			}
		});
	}
	
	function final_po(id){
		$('.loading').show();
		$.ajax({
			url			: '<?php echo base_url() ?>shopboard/detail_po/'+id,
			cache		: false,
			contentType	: false,
			processData	: false,
			type		: 'GET',
			dataType  	: 'json',
			success   	: function(res) {
				console.log(JSON.stringify(res));
				$('.loading').hide();
				
				$('#act').val('edit');
				$('#no_po').val(res.no_po);
				$('#periode_start').val(date('d-M-Y',strtotime(res.periode_start)));
				$('#periode_end').val(date('d-M-Y',strtotime(res.periode_end)));
				
				
				$('#id_reklame').val(res.id_reklame);
				$('#id_po').val(res.id);
				$('#new').val(res.new);
				
				$('#supplier').val(res.supplier).trigger('change');
				$('#branchcode').val(res.branchcode).trigger('change');
				$('#wilayah').val(res.wilayah.toUpperCase()).trigger('change');
				$('#nama_toko').val(res.nama_toko);
				$('#alamat').val(res.alamat);
				$('#kota').val(res.kota);
				$('#merk1').val(res.merk1);
				$('#ukuran1').val(res.ukuran1);
				
				var html = '';
				$('#div_merk_ukuran').html(html);
				if(res.merk1){
					html += '<div class="form-group mb0"><div class="col-4"><select name="merk[]" class="form-control select2merk form-edit" data-value="'+res.merk1+'" style="width:100%" required></select></div><div class="col-6"><input type="text" name="ukuran[]" value="'+res.ukuran1+'" class="form-control form-edit" placeholder="0 m X 0 m X 0 sisi" required></div><div class="col-2"><button type="button" class="btn btn-light-dark form-edit border10 delete_merk"><i class="glyphicon glyphicon-trash fs-4 color-danger"></i></button></div></div>';
				}
				if(res.merk2){
					html += '<div class="form-group mb0"><div class="col-4"><select name="merk[]" class="form-control select2merk form-edit" data-value="'+res.merk2+'" style="width:100%" required></select></div><div class="col-6"><input type="text" name="ukuran[]" value="'+res.ukuran2+'" class="form-control form-edit" placeholder="0 m X 0 m X 0 sisi" required></div><div class="col-2"><button type="button" class="btn btn-light-dark form-edit border10 delete_merk"><i class="glyphicon glyphicon-trash fs-4 color-danger"></i></button></div></div>';
				}
				if(res.merk3){
					html += '<div class="form-group mb0"><div class="col-4"><select name="merk[]" class="form-control select2merk form-edit" data-value="'+res.merk3+'"  style="width:100%" required></select></div><div class="col-6"><input type="text" name="ukuran[]" value="'+res.ukuran3+'" class="form-control form-edit" placeholder="0 m X 0 m X 0 sisi" required></div><div class="col-2"><button type="button" class="btn btn-light-dark form-edit border10 delete_merk"><i class="glyphicon glyphicon-trash fs-4 color-danger"></i></button></div></div>';
				}
				if(res.merk4){
					html += '<div class="form-group mb0"><div class="col-4"><select name="merk[]" class="form-control select2merk form-edit" data-value="'+res.merk4+'" style="width:100%" required></select></div><div class="col-6"><input type="text" name="ukuran[]" value="'+res.ukuran4+'" class="form-control form-edit" placeholder="0 m X 0 m X 0 sisi" required></div><div class="col-2"><button type="button" class="btn btn-light-dark form-edit border10 delete_merk"><i class="glyphicon glyphicon-trash fs-4 color-danger"></i></button></div></div>';
				}
				if(res.merk5){
					html += '<div class="form-group mb0"><div class="col-4"><select name="merk[]" class="form-control select2merk form-edit" data-value="'+res.merk5+'" style="width:100%" required></select></div><div class="col-6"><input type="text" name="ukuran[]" value="'+res.ukuran5+'" class="form-control form-edit" placeholder="0 m X 0 m X 0 sisi" required></div><div class="col-2"><button type="button" class="btn btn-light-dark form-edit border10 delete_merk"><i class="glyphicon glyphicon-trash fs-4 color-danger"></i></button></div></div>';
				}
				$('#div_merk_ukuran').append(html);
				create_select_merk();
				
				$('.form-edit').prop('disabled',true);
				$('.btn-edit').hide();
				$('#btn-edit').show();
				$('#btn-batal').show();
				
				$('#modal_add').modal('show');
			},
			error: function (request, error) {
				console.log(error);
			}
		});
	}
	
	function update_po(id){
		$('.loading').show();
		$.ajax({
			url			: '<?php echo base_url() ?>shopboard/detail_po/'+id,
			cache		: false,
			contentType	: false,
			processData	: false,
			type		: 'GET',
			dataType  	: 'json',
			success   	: function(res) {
				console.log(JSON.stringify(res));
				$('.loading').hide();
				
				$('#no_po').val('');
				$('#periode_start').val('');
				$('#periode_end').val('');
				
				$('#act').val('update_po');
				$('#id_reklame').val(res.id_reklame);
				$('#id_po').val(res.id);
				$('#new').val(res.new);
				
				$('#supplier').val(res.supplier).trigger('change');
				$('#branchcode').val(res.branchcode).trigger('change');
				$('#wilayah').val(res.wilayah.toUpperCase()).trigger('change');
				$('#nama_toko').val(res.nama_toko);
				$('#alamat').val(res.alamat);
				$('#kota').val(res.kota);
				$('#merk1').val(res.merk1);
				$('#ukuran1').val(res.ukuran1);
				
				var html = '';
				$('#div_merk_ukuran').html(html);
				if(res.merk1){
					html += '<div class="form-group mb0"><div class="col-4"><select name="merk[]" class="form-control select2merk form-edit" data-value="'+res.merk1+'" style="width:100%" required></select></div><div class="col-6"><input type="text" name="ukuran[]" value="'+res.ukuran1+'" class="form-control form-edit" placeholder="0 m X 0 m X 0 sisi" required></div><div class="col-2"><button type="button" class="btn btn-light-dark form-edit border10 delete_merk"><i class="glyphicon glyphicon-trash fs-4 color-danger"></i></button></div></div>';
				}
				if(res.merk2){
					html += '<div class="form-group mb0"><div class="col-4"><select name="merk[]" class="form-control select2merk form-edit" data-value="'+res.merk2+'" style="width:100%" required></select></div><div class="col-6"><input type="text" name="ukuran[]" value="'+res.ukuran2+'" class="form-control form-edit" placeholder="0 m X 0 m X 0 sisi" required></div><div class="col-2"><button type="button" class="btn btn-light-dark form-edit border10 delete_merk"><i class="glyphicon glyphicon-trash fs-4 color-danger"></i></button></div></div>';
				}
				if(res.merk3){
					html += '<div class="form-group mb0"><div class="col-4"><select name="merk[]" class="form-control select2merk form-edit" data-value="'+res.merk3+'"  style="width:100%" required></select></div><div class="col-6"><input type="text" name="ukuran[]" value="'+res.ukuran3+'" class="form-control form-edit" placeholder="0 m X 0 m X 0 sisi" required></div><div class="col-2"><button type="button" class="btn btn-light-dark form-edit border10 delete_merk"><i class="glyphicon glyphicon-trash fs-4 color-danger"></i></button></div></div>';
				}
				if(res.merk4){
					html += '<div class="form-group mb0"><div class="col-4"><select name="merk[]" class="form-control select2merk form-edit" data-value="'+res.merk4+'" style="width:100%" required></select></div><div class="col-6"><input type="text" name="ukuran[]" value="'+res.ukuran4+'" class="form-control form-edit" placeholder="0 m X 0 m X 0 sisi" required></div><div class="col-2"><button type="button" class="btn btn-light-dark form-edit border10 delete_merk"><i class="glyphicon glyphicon-trash fs-4 color-danger"></i></button></div></div>';
				}
				if(res.merk5){
					html += '<div class="form-group mb0"><div class="col-4"><select name="merk[]" class="form-control select2merk form-edit" data-value="'+res.merk5+'" style="width:100%" required></select></div><div class="col-6"><input type="text" name="ukuran[]" value="'+res.ukuran5+'" class="form-control form-edit" placeholder="0 m X 0 m X 0 sisi" required></div><div class="col-2"><button type="button" class="btn btn-light-dark form-edit border10 delete_merk"><i class="glyphicon glyphicon-trash fs-4 color-danger"></i></button></div></div>';
				}
				$('#div_merk_ukuran').append(html);
				create_select_merk();
				
				$('.form-edit').prop('disabled',false);
				$('.btn-edit').hide();
				$('#btn-update').show();
				$('#modal_add').modal('show');
			},
			error: function (request, error) {
				console.log(error);
			}
		});
	}
	
	function finalize_po(){
		var checked = $('.chk_pilih_final:checked').length;
		if(checked>0){
			$('#confirm-finalize').modal('show');
		}
	}
	
	function edit_po(){
		$('.form-edit').prop('disabled',false);
		$('.btn-edit').hide();
		$('#act').val('update_po');
		$('#btn-save').show();
	}
	
	function add_merk(){
		$('#div_merk_ukuran').append(html_merk);
		create_select_merk();
	}
	
	function set_default(){
		$('.chk_show').prop('checked', false);
		$('.filter_date').prop('disabled', false);
		$('#filter_periode_start').val(start_date);
		$('#filter_periode_end').val(end_date);
		shopboard_filter();
	}
	
	function download_excel(){
		var chk = '';
		$('.excel_id_po').remove();
		$('.chk_pilih').each(function(i, obj) {
			// alert(obj.value);
			if(obj.checked){
			chk += '<input type="hidden" name="id[]" class="excel_id_po" value="'+obj.value+'">';
			}
		});
		
		if(chk==''){
		}
		else{
			$('#form_excel').append(chk);
			$('#form_excel').submit();
		}
	}
	
	function set_wilayah_option(){
		var cabang = $('#branchcode').val();
		var html = '<option value="">Pilih Wilayah</option>';
		for(i=0;i<list_wilayah.length;i++){
			if(list_wilayah[i].kd_lokasi==cabang){
				html+='<option value="'+list_wilayah[i].wilayah+'">'+list_wilayah[i].wilayah+'</option>';
			}
		}
		$('#wilayah').html(html);
		$('#wilayah').trigger('change.select2'); 
	}
	
	function set_wilayah_toko_option(){
	
		console.log('set_wilayah_toko_option');
		
		var cabang = $('#toko_cabang').val();
		var html = '<option value="">Pilih Wilayah</option>';
		for(i=0;i<list_wilayah.length;i++){
			if(list_wilayah[i].kd_lokasi==cabang){
				html+='<option value="'+list_wilayah[i].wilayah+'">'+list_wilayah[i].wilayah+'</option>';
			}
		}
		$('#toko_wilayah').html(html);
		$('#toko_wilayah').trigger('change.select2'); 
	}
	
</script>
