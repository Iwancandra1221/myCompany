<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
</script>
<style>
	select option:disabled {
	color: #555;
	font-style: italic;
	}
</style>

<div class="container">
	<div class="form_title">
		<div style="text-align: center;">
			KEY PERFORMANCE INDIKATOR
		</div>
	</div>
	<div class="row">
		<div class="col-6">
			FILTER KATEGORI :
			<select id="filter_kategori" onchange="javascript:filter_kpi_kategori()">
				<option value="">ALL</option>
				<?php
					foreach($KPICategory as $r) {
						echo "<option value='".$r->KPICategoryName."'>".$r->KPICategoryName."</option>";
					}
				?>
			</select>
		</div>
		<div class="col-6">
			<?php if($_SESSION["can_create"] == 1) { ?>
				<button type="button" id="btn-add" class="btn btn-dark" style="float:right">Add New</button>
			<?php } ?>
		</div>
	</div>
	
	<div class="row">
		<div class="col-12">
			<table id="table" class="table table-bordered" summary="Table KEY PERFORMANCE INDIKATOR">
				<thead>
					<tr>
						<!-- <th class="no-sort">NO</th> -->
						<th scope="col">NAMA</th>
						<th scope="col">DESKRIPSI</th>
						<th scope="col">KATEGORI</th>
						<th scope="col">UNIT</th>
						<th scope="col" class="<?php echo ($_SESSION['logged_in']['isSalesman']==0) ? "col-hide":"" ?>">TARGET PENJUALAN</th>
						<th scope="col" class="<?php echo ($_SESSION['logged_in']['isSalesman']==0) ? "col-hide":"" ?>">DIVISI</th>
						<th scope="col" class="<?php echo ($_SESSION['logged_in']['isSalesman']==0) ? "col-hide":"" ?>">ITEM FOKUS</th>
						<th scope="col">AKTIF</th>
						<?php if($_SESSION["can_update"] == 1) { ?>
							<th scope="col" class="no-sort">EDIT</th>
						<?php } ?>
						<?php if($_SESSION["can_delete"] == 1) { ?>
							<th scope="col" class="no-sort">DELETE</th>
						<?php } ?>
					</tr>
				</thead>
				<tbody>
<!-- 					<?php
						$no = 0;
						//foreach($MasterKPI as $r) {
							//$no++;
							//$data = '';
							//$data .=' data-code="'.$r->KPICode.'"';
							//$data .=' data-name="'.$r->KPIName.'"';
							//$data .=' data-desc="'.$r->KPIDescription.'"';
							//$data .=' data-category="'.$r->KPICategory.'"';
							//$data .=' data-unit="'.$r->KPIUnit.'"';
							//$data .=' data-target="'.$r->TargetPenjualan.'"';
							//$data .=' data-divisi="'.$r->Divisi.'"';
							//$data .=' data-item="'.$r->ItemFokus.'"';
							//$data .=' data-active="'.$r->IsActive.'"';
							//$data .=' data-createdby="'.$r->CreatedBy.'"';
							//$data .=' data-createddate="'.$r->CreatedDate.'"';
							//$data .=' data-modifiedby="'.$r->ModifiedBy.'"';
							//$data .=' data-modifieddate="'.$r->ModifiedDate.'"';
							
							//$checked = ($r->IsActive==1) ? "checked" : "";
						?>
						<tr>
							<td><?php //echo $no ?></td>
							<td><?php //echo $r->KPIName ?></td>
							<td><?php //echo $r->KPIDescription ?></td>
							<td><?php //echo $r->KPICategoryName ?></td>
							<td><?php //echo $r->KPIUnit ?></td>
							<td><?php //echo $r->TargetPenjualan ?></td>
							<td><?php //echo $r->Divisi ?></td>
							<td><?php //echo $r->ItemFokus ?></td>
							<td><input type="checkbox" <?php //echo $checked ?> onclick="return false"></td>
							
							<?php //if($_SESSION["can_update"] == 1) { ?>
							<td><?php //echo '<button class="btn-edit btn btn-dark" '.$data.'><i class="glyphicon glyphicon-pencil"></i></button>' ?></td>
							<?php //} ?>
							<?php //if($_SESSION["can_delete"] == 1) { ?>
							<td><?php //echo '<button class="btn-delete btn btn-danger-dark" '.$data.'><i class="glyphicon glyphicon-trash"></i></button>' ?></td>
							<?php //} ?>
						</tr>
						<?php
							
							
					//	}
					?>
					 -->
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_edit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form id="myform" action="<?php echo site_url('KPI/Save') ?>" method="POST" target="_blank">
				<input type="hidden" name="KPICode" id="KPICode">
				<div class="modal-header form_title" style="text-align: center;">
					 EDIT KEY PERFORMANCE INDIKATOR
				</div>
				<div class="modal-body">
					<div class="col-12">
						<div class="form-group">
							<label>Kategori</label>
							<select name="KPICategory" id="KPICategory" class="form-control" required>
								<option value=""></option>
								<?php
									foreach($KPICategory as $r) {
										echo "<option value='".$r->KPICategory."'>".$r->KPICategoryName."</option>";
									}
								?>
							</select>
						</div>
						
						
						<div class="form-group">
							<label>Nama</label>
							<input type="text" name="KPIName" id="KPIName" class="form-control" required>
						</div>
						<div class="form-group">
							<label>Deskripsi</label>
							<textarea name="KPIDescription" id="KPIDescription" class="form-control" rows="5" required></textarea>
						</div>
						<div class="form-group">
							<label>Unit</label>
							<select name="KPIUnit" id="KPIUnit" class="form-control" required>
								<?php
									foreach($unit as $u) {
										echo "<option value='".$u->KPIUnit."'>".$u->KPIUnit."</option>";
									}
								?>
							</select>
						</div>
						<input type="checkbox" name="IsActive" id="IsActive" value="1" checked> Aktif
						
					</div>
					<?php if($_SESSION['logged_in']['isSalesman']==1){ ?>
						<div class="col-12">
							<input type="checkbox" name="IsTargetPenjualan" id="IsTargetPenjualan"> <label for="IsTargetPenjualan">TARGET PENJUALAN</label> <small><em>(centang untuk isi pilihan di bawah)</em></small>
						</div>
						
						<div class="col-4">
							<div class="form-group">
								<label>Target Penjualan</label>
								<select name="TargetPenjualan" id="TargetPenjualan" class="form-control" required disabled>
									<option value=""></option>
									<option value="ALL DIVISI">ALL DIVISI</option>
									<option value="PER DIVISI">PER DIVISI</option>
									<option value="ITEM FOKUS">ITEM FOKUS</option>
								</select>
							</div>
						</div>
						<div class="col-4">
							<div class="form-group">
								<label>Divisi</label>
								<select name="Divisi" id="Divisi" class="form-control" required disabled>
									<option value=""></option>
									<?php
										foreach($Divisi as $div) {
											echo "<option value='".$div->Divisi."'>".$div->Divisi."</option>";
										}
									?>
								</select>
							</div>
						</div>
						<div class="col-4">
							<div class="form-group">
								<label>Item Fokus</label>
								<select name="ItemFokus" id="ItemFokus" class="form-control" required disabled>
									<option value=""></option>
									<?php
										foreach($SellingDifficultyLevel as $level) {
											echo "<option value='".$level->level_code."'>".$level->level_code."</option>";
										}
									?>
								</select>
							</div>
						</div>
					<?php } ?>					
				</div>
				<div class="modal-footer">
					<div class="col-12">
						<small id="LastModified" style="float:left;text-align:left"></small>
						
						<button type="submit" class="btn btn-danger-dark btn-ok">SAVE</button>
						<button type="button" class="btn btn-dark" data-dismiss="modal">Cancel</button>
					</div>
				</div>
				
			</form>
		</div>
	</div>
</div>

<script>

	$(document).on("click", "#aktif_nonaktif" , function() {	
		var code 	= $(this).attr('data-code');
		var data='';
		var aktif='';
		var psn='';
		if( $('#aktif_nonaktif').is(':checked') ){
		    data = 'Mengaktifkan';
		    aktif='1';
		    psn = 'aktifkan';
		}else{
		    data = 'Menonaktifkan';
		    aktif='0';
		    psn = 'nonaktifkan';
		}

		if (confirm('Ingin '+data+' Master KPI Ini?')) {
			$.ajax({
				url			: '<?php echo site_url('KPI/AktifNonaktif') ?>?KPICode='+code+'&Aktif='+aktif,
				cache		: false,
				contentType	: false,
				processData	: false,
				type		: 'GET',
    			dataType  : 'json',
				success   : function(data) {
					$('.loading').hide();
					if(data.result=='success'){
						alert('Data berhasil '+psn+'!');
						location.reload();
					}
					else{
						alert(data.result+'\n'+data.error.replace(/\\n/g,"\n"));
					}
					
				}
			});
		}

	});


	$(document).ready(function() {
	    // t = $('#table').DataTable({
		// 	"pageLength"    : 10,
		// 	"searching"     : true,
		// 	"columnDefs": [
		// 	{ targets: 'no-sort', orderable: false },
		// 	{ targets: 'col-hide', visible: false }
		// 	],
		// 	// "dom": '<"top"l>rt<"bottom"ip><"clear">',
		// 	"order": [[1, 'desc']],
		// });
		
		// t.on('order.dt search.dt', function () {
		// 	let i = 1;
			
		// 	t.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
		// 		this.data(i++);
		// 	});
		// }).draw();
		

		$('#IsTargetPenjualan').change(function() {
			if($(this).is(':checked')){
				$("#TargetPenjualan").removeAttr("disabled");
			}
			else{
				$("#TargetPenjualan").val('');
				$("#Divisi").val('');
				$("#ItemFokus").val('');
				$("#TargetPenjualan").attr("disabled","disabled");
				$("#Divisi").attr("disabled","disabled");
				$("#ItemFokus").attr("disabled","disabled");
			}
		}); 
		
		$('#TargetPenjualan').change(function(){
			var TargetPenjualan = $(this).val();
			optionTargetPenjualan(TargetPenjualan);
		}); 
		
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
					$('.loading').hide();
					if(data.result=='success'){
						alert('SUCCESS. Data berhasil disimpan.');
						location.reload();
					}
					else{
						alert('FAILED. '+data.error);
					}
					
				}
			});
			event.preventDefault(); //Prevent the default submit
		});
	});
	
	$(document).on("click", ".btn-edit" , function() {		
		var code 	= $(this).attr('data-code');
		var name 	= $(this).attr('data-name');
		var desc 	= $(this).attr('data-desc');
		var category= $(this).attr('data-category');
		var unit 	= $(this).attr('data-unit');
		var target 	= $(this).attr('data-target');
		var divisi 	= $(this).attr('data-divisi');
		var item 	= $(this).attr('data-item');
		var active 	= $(this).attr('data-active');
		var createdby= $(this).attr('data-createdby');
		var createddate = $(this).attr('data-createddate');
		var modifiedby= $(this).attr('data-modifiedby');
		var modifieddate= $(this).attr('data-modifieddate');
		
		$('#KPICode').val(code);
		$('#KPICategory').val(category);
		$('#KPIName').val(name);
		$('#KPIDescription').val(desc);
		$('#KPIUnit').val(unit);
		$('#IsActive').prop('checked', (active==1)?true:false);
		$('#IsTargetPenjualan').prop('checked', (target!='')?true:false);
		$('#TargetPenjualan').val(target);
		
		optionTargetPenjualan(target);
		$('#Divisi').val(divisi);
		$('#ItemFokus').val(item);
		
		var created = '';
		created = 'Created on '+createddate+' By '+createdby;
		if(modifiedby!=''){
			created += '<br>Last Modified on '+modifieddate+' By '+modifiedby;
		}
		$('#LastModified').html(created);
		
		$('#modal_edit').modal('show');
	});
	
	$(document).on("click", ".btn-delete" , function() {
		var code 	= $(this).attr('data-code');
		if (confirm('Ingin Hapus Master KPI Ini?')) {
			$.ajax({
				url			: '<?php echo site_url('KPI/Delete') ?>?KPICode='+code,
				cache		: false,
				contentType	: false,
				processData	: false,
				type		: 'GET',
    			dataType  : 'json',
				success   : function(data) {
					// alert(data);
					$('.loading').hide();
					if(data.result=='success'){
						alert('Data berhasil dihapus!');
						location.reload();
					}
					else{
						alert(data.result+'\n'+data.error.replace(/\\n/g,"\n"));
					}
					
				}
			});
		}
		
	});
	
	$(document).on("click", "#btn-add" , function() {	
		$('#KPICode').val('');
		$('#KPICategory').val('');
		$('#KPIName').val('');
		$('#KPIDescription').val('');
		$('#KPIUnit').val('');
		$('#IsActive').prop('checked', true);
		$('#IsTargetPenjualan').prop('checked', false);
		$('#TargetPenjualan').val('');
		$('#TargetPenjualan').val('');
		$('#Divisi').val('');
		$('#ItemFokus').val('');
		$('#LastModified').html('');
		$('#modal_edit').modal('show');
	});
	
	function optionTargetPenjualan(TargetPenjualan){
		$('#Divisi').val('');
		$("#Divisi").attr("disabled","disabled");
		$("#Divisi option:contains('ALL')").removeAttr("disabled");
		$('#ItemFokus').val('');
		$("#ItemFokus").attr("disabled","disabled");
		
		if(TargetPenjualan!=''){
			$("#TargetPenjualan").removeAttr("disabled");
		}
		if(TargetPenjualan=='ALL DIVISI'){
			$('#Divisi').val('ALL');
		}
		
		if(TargetPenjualan=='PER DIVISI'){
			$("#Divisi").removeAttr("disabled");
			$("select#Divisi option:contains('ALL')").attr("disabled","disabled");
		}
		
		if(TargetPenjualan=='ITEM FOKUS'){
			$("#Divisi").removeAttr("disabled");
			$("#ItemFokus").removeAttr("disabled");
		}
	}
	
	// function filter_kpi_kategori() {
	// 	var kategori = $('#filter_kategori').val();
	// 	if(kategori==''){
	// 		$("#table").dataTable().fnFilter(kategori, 3);
	// 	}
	// 	else{
	// 		$("#table").dataTable().fnFilter("^"+kategori+"$", 3, true);
	// 	}
	// }


	$(document).ready(function() {
		$('#filter_kategori').change(function () {
	  	 	$("#table").dataTable().fnFilter(
	     		document.getElementById('filter_kategori').value,0,
	      	);
	    });


	    $('#table').dataTable( {
	        "bProcessing": true,
	        "bServerSide": true,
	         "columnDefs": [
		      { targets: 'no-sort', orderable: false },
		      { targets: 'col-hide', visible: false }
		      ],
	        "sAjaxSource": '<?php echo site_url('KPI/ListKPI/'.$this->uri->segment(2)) ?>',
	        "oLanguage": {
		        "sLengthMenu": "Menampilkan _MENU_ Data per halaman",
		        "sZeroRecords": "Maaf, Data tidak ada",
		        "sInfo": "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
		        "sInfoEmpty": "Menampilkan 0 s/d 0 dari 0 data",
		        "sSearch": "",
		        "sInfoFiltered": "",
		        "oPaginate": {
			       	"sPrevious": "Sebelumnya",
			        "sNext": "Berikutnya"
		    	}
		    }
	    });

	});
</script>

