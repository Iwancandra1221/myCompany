
<style>
.modal-body {
	height: 500px;
	overflow-y: scroll;
	margin-bottom:25px;
}
</style>

    <style>
        /* Styling to match the DataTable search input */
        .dataTables_filter {
            float: right;
            margin-bottom: 10px;
        }

        .dataTables_filter input {
            margin-left: 5px;
        }
    </style>

<div class="container">
	<div class="page-title">MASTER MODULE</div>
	<?php if($_SESSION["can_create"] == 1) { ?>
	<a href="#" data-toggle='modal' data-target='#insert_new'>Insert New Module</a>
	<?php } ?>	

    <div class="dataTables_filter"> 
        <input type="text" id="searchInput" placeholder="Search...">
        <button id="searchButton">Search</button>
    </div>

	<table id="TblModule" class="table table-striped table-bordered" cellspacing="0" width="100%">
		
	</table>

	<!-- model insert new -->
	<div class="modal fade" id="insert_new" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					Insert New Module
				</div>
				<div class="modal-body">
					<?php echo form_open("Module/insert"); ?>
					<!-- <form action="masterModuleCtr/insert" method="post"> -->
					<div class="form-group">
						<label>Kode Module</label>
						<input type="text" class="form-control" name="txtKodeModule" id="txtKodeModule" placeholder="" required>
					</div>
					<div class="form-group">
						<label>Nama Module</label>
						<input type="text" class="form-control" name="txtNamaModule" id="txtNamaModule" placeholder="" required>
						<!-- <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small> -->
					</div>
					<div class="form-group">
						<label>Jenis Module</label>
						<select class="form-control" name="selJenis" id="selJenis">
							<option value="PARENT">PARENT</option>
							<option value="CHILD">CHILD</option>
							<option value="GRANDCHILD">GRANDCHILD</option>
							<option value="GREAT-GRANDCHILD">GREAT-GRANDCHILD</option>
						</select>
					</div>
					<div class="form-group">
						<label>Parent Module</label>
						<select class="form-control" name="selParent" id="selParent" style="width: 100%;"></select>
					</div>
					<div class="form-group">
						<label>Posisi</label>
						<input type="text" class="form-control" name="Position" id="Position" placeholder="">
					</div>
					<div class="form-group">
						<label>Aktif</label>&nbsp;&nbsp;&nbsp;
						<input type="checkbox" class="" name="chkAktif" id="chkAktif" value="1" checked>
					</div>
					<div class="form-group">
						<label>Nama Controller</label>
						<input type="text" class="form-control" name="txtNamaCtr" id="txtNamaCtr" placeholder="">
					</div>
					<div class="form-group">
						<label>Keterangan</label>
						<textarea class="form-control" name="txtKeterangan" id="txtKeterangan"></textarea>
					</div>
					<input type="submit" class="btn btn-primary" value="Submit">
					<input type="button" class="btn btn-danger" value="Cancel" data-dismiss="modal">
					<?php echo form_close(); ?>
					<!-- </form> -->
				</div>
			</div>
		</div>
	</div>
	<!--  -->
	<!-- model update data -->
	<div class="modal fade" id="update_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					Update Data Module
				</div>
				<div class="modal-body">
					<!-- <form action="masterModuleCtr/update" method="post"> -->
					<?php echo form_open("Module/update"); ?>
					<div class="form-group">
						<label>Kode Module</label>
						<input type="text" class="form-control" name="utxtKodeModule" id="utxtKodeModule" placeholder="" required readonly>
					</div>
					<div class="form-group">
						<label>Nama Module</label>
						<input type="text" class="form-control" name="utxtNamaModule" id="utxtNamaModule" placeholder="" required>
						<!-- <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small> -->
					</div>
					<div class="form-group">
						<label>Jenis Module</label>
						<select class="form-control" name="uselJenis" id="uselJenis">
							<option value="PARENT">PARENT</option>
							<option value="CHILD">CHILD</option>
							<option value="GRANDCHILD">GRANDCHILD</option>
							<option value="GREAT-GRANDCHILD">GREAT-GRANDCHILD</option>
						</select>
					</div>
					<div class="form-group">
						<label>Parent Module</label>
						<select class="form-control" name="uselParent" id="uselParent" style="width: 100%;"></select>
					</div>
					<div class="form-group">
						<label>Posisi</label>
						<input type="text" class="form-control" name="uPosition" id="uPosition" placeholder="">
					</div>
					<div class="form-group">
						<label>Aktif</label>&nbsp;&nbsp;&nbsp;
						<input type="checkbox" class="" name="uchkAktif" id="uchkAktif" value="1">
					</div>
					<div class="form-group">
						<label>Nama Controller</label>
						<input type="text" class="form-control" name="utxtNamaCtr" id="utxtNamaCtr" placeholder="">
					</div>
					<div class="form-group">
						<label>Keterangan</label>
						<textarea class="form-control" name="utxtKeterangan" id="utxtKeterangan"></textarea>
					</div>
					
					<input type="submit" class="btn btn-primary" value="Submit">
					<input type="button" class="btn btn-danger" value="Cancel" data-dismiss="modal">
					<?php echo form_close();?>
					<!-- </form> -->
				</div>
			</div>
		</div>
	</div>
	<!--  -->
	<!-- model update data -->
	<div class="modal fade" id="update_modal_2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					Update Kode Module
				</div>
				<div class="modal-body" id="modal_update_kode">
					<!-- <form action="masterModuleCtr/update" method="post"> -->
					<?php echo form_open("Module/updateKode"); ?>
					<div class="form-group">
						<label>Kode Module</label>
						<input type="text" class="form-control" name="utxtKodeModuleOld" id="utxtKodeModuleOld" placeholder="" required readonly>
					</div>
					<div class="form-group">
						<label>Kode Baru</label>
						<input type="text" class="form-control" name="utxtNamaModuleNew" id="utxtNamaModuleNew" placeholder="" required>
						<!-- <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small> -->
					</div>
					<input type="submit" class="btn btn-primary" value="Submit">
					<input type="button" class="btn btn-danger" value="Cancel" data-dismiss="modal">
					<?php echo form_close();?>
					<!-- </form> -->
				</div>
			</div>
		</div>
	</div>
	<!--  -->
	<!-- model delete confirm -->
	<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					Delete Confirmation
				</div>
				<div class="modal-body">
					<p>You are about to delete <b><i class="title"></i></b> record, this procedure is irreversible.</p>
					<p>Do you want to proceed?</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					<a class="btn btn-danger btn-ok">Delete</a>
				</div>
			</div>
		</div>
	</div>
	<!--  -->
</div> <!-- /container -->
	 
   

<script>

	let searchInput = document.getElementById("searchInput");
	let tbModule;

	var TblModuleData = {};
	var object = {};
	var formData = new FormData();
	formData.append("submit","filter");
	formData.forEach(function(value, key){
		object[key] = value;
	});
	TblModuleData = object;
	$(document).ready(function() {
		tbModule = $('#TblModule').DataTable({
			responsive: true,
			"columnDefs": [
				{"title":"No", 				"targets": 0, "orderable": false},
				{"title":"Kode Module",		"targets": 1, "orderable": false},
				{"title":"Nama Module",		"targets": 2, "orderable": false},
				{"title":"Jenis",			"targets": 3, "orderable": false},
				{"title":"Aktif",			"targets": 4, "orderable": false},
				{"title":"Keterangan",		"targets": 5, "orderable": false},
				{"title":"Chkbox Aktif",	"targets": 6, "orderable": false},
				{"title":"Edit",			"targets": 7, "orderable": false},
				{"title":"Ganti Kode",		"targets": 8, "orderable": false},
				{"title":"Hapus",			"targets": 9, "orderable": false},
			],
			"columns": [
				{ "data": "RowNum",				"class":"hideOnMobile" },
				{ "data": "module_id_depth", 	"class":"hideOnMobile" },
				{ "data": "module_name" },
				{ "data": "module_type" },
				{ "data": "is_active", 			"class":"hideOnMobile" },
				{ "data": "description", 		"class":"hideOnMobile" },
				{ "data": "chkbox" },
				{ "data": "edit" },
				{ "data": "ganti_kode" },
				{ "data": "hapus" },

			],
			searching: false,
			processing: true,
			serverSide: true,
			ajax: {
				url: "<?=base_url()?>module/",
				type:'POST',
				data: function(d){
					d.search.value = $('#searchInput').val();
               		return $.extend(d, TblModuleData);
				},
				"dataSrc": function ( json ) {
					return json.data;
				}   
			},
		});


            // Handle manual search on Enter key press
            searchInput.addEventListener("keydown", function (event) {
                if (event.key === "Enter") {
                    const searchTerm = searchInput.value;
                    tbModule.search(searchTerm).draw();
                }
            });
                   $('#searchButton').on('click', function () {
                const searchTerm = $('#searchInput').val();
                tbModule.search(searchTerm).draw();
            });	

                       $('.dataTables_filter').prepend($('#TblModule_filter'));

		$('#confirm-delete').on('show.bs.modal', function(e) {
			$(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
			$('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
		});
		$('#confirm-delete').on('show.bs.modal', function(e) {
			$(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
			$('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
		});
		$('#confirm-delete').on('show.bs.modal', function(e) {
			var data = $(e.relatedTarget).data();
			$('.title', this).text(data.recordTitle);
		});


		$('#selJenis').on('change', function() {
		    var selectedJenis = $(this).val(); 
		    populateParentModule(selectedJenis);
		});		

		$('#uselJenis').on('change', function() {
		    var selectedJenis = $(this).val(); 
		    upopulateParentModule(selectedJenis);
		});

		function upopulateParentModule(selectedJenis) {  
		  $('.loading').show();
		  $.ajax({
		  	url: "<?=base_url()?>module/DropdownParentList",
		    method: 'GET', 
		    data: { jenis: selectedJenis }, 
		    dataType: 'json',
		    success: function(data) { 
		      $('#uselParent').empty(); 
		      $.each(data, function(index, item) {
		        $('#uselParent').append('<option value="' + item.module_id + '">' + item.module_id + ' - ' + item.module_name + '</option>');
		      });
		      $('.loading').hide();
		    },
		    error: function(xhr, status, error) {
		      console.error('AJAX Error:', error);
		      $('.loading').hide();
		    }
		  });
		}

		function populateParentModule(selectedJenis) {  
		  $('.loading').show();
		  $.ajax({
		  	url: "<?=base_url()?>module/DropdownParentList",
		    method: 'GET', 
		    data: { jenis: selectedJenis }, 
		    dataType: 'json',
		    success: function(data) { 
		      $('#selParent').empty(); 
		      $.each(data, function(index, item) {
		        $('#selParent').append('<option value="' + item.module_id + '">' + item.module_id + ' - ' + item.module_name + '</option>');
		      });
		      $('.loading').hide();
		    },
		    error: function(xhr, status, error) {
		      console.error('AJAX Error:', error);
		      $('.loading').hide();
		    }
		  });
		}

		// $('#selJenis').on('change', function() {
        //     var selectedValue = $(this).val(); // Mengambil value dari elemen selJenis

        // 	$('#selParent').select2({
		// 		tags: false,
		// 		dropdownParent: $("#insert_new"),
		// 		ajax: {
		// 			dataType:'json',
		// 			url: "<?=base_url()?>module/DropdownParentList",
		// 			data: function (params) { 
		// 	            var query = { 
        //             		jenis: selectedValue, 
		// 					q: params.term,
		// 					type: '--no parent--',
		// 					page: params.page || 1
		// 				}
		// 				return query;
		// 			},
		// 			processResults: function (data,params) {
		// 				params.page = params.page || 1;
		// 				// Transforms the top-level key of the response object from 'items' to 'results'
		// 				var json = data;
		// 				return {
		// 					results : json.data,
		// 					pagination: {
		// 						more: json.pagination.more
		// 					}
		// 				};
		// 			}
		// 		}
		// 	});
        // });


		// $('#uselParent').select2({
		// 	tags: false,
		// 	dropdownParent: $("#update_modal"),
		// 	ajax: {
		// 		dataType:'json',
		// 		url: "<?=base_url()?>module/DropdownParentList",
		// 		data: function (params) {
		// 			var selectedValue = $('#uselJenis').val(); 
		//             var query = { 
		//                 jenis: selectedValue, 
		// 				q: params.term,
		// 				type: '--no parent--',
		// 				page: params.page || 1
		// 			}
		// 			return query;
		// 		},
		// 		processResults: function (data,params) {
		// 			params.page = params.page || 1;
		// 			// Transforms the top-level key of the response object from 'items' to 'results'
		// 			var json = data;
		// 			return {
		// 				results : json.data,
		// 				pagination: {
		// 					more: json.pagination.more
		// 				}
		// 			};
		// 		},
		// 		cache: true
		// 	}
		// });
		// $('#selParent').select2({
		// 	tags: false,
		// 	dropdownParent: $("#insert_new"),
		// 	ajax: {
		// 		dataType:'json',
		// 		url: "<?=base_url()?>module/DropdownParentList",
		// 		data: function (params) {
		// 			var selectedValue = $('#uselJenis').val(); 
		//             var query = { 
		//                 jenis: selectedValue, 
		// 				q: params.term,
		// 				type: '--no parent--',
		// 				page: params.page || 1
		// 			}
		// 			return query;
		// 		},
		// 		processResults: function (data,params) {
		// 			params.page = params.page || 1;
		// 			// Transforms the top-level key of the response object from 'items' to 'results'
		// 			var json = data;
		// 			return {
		// 				results : json.data,
		// 				pagination: {
		// 					more: json.pagination.more
		// 				}
		// 			};
		// 		}
		// 	}
		// });
	});
	var popupWindow = function(id){
		if(id=='userpick' || id=='userpickforupd'){
		window.open('UserPicker?id=' + encodeURIComponent(id),'popuppage',
		'width=800,toolbar=1,resizable=1,scrollbars=yes,height=600,top=0,left=100');
		} else if(id=='dbpick' || id=='dbpickforupd') {
		window.open('DatabasePicker?id=' + encodeURIComponent(id),'popuppage',
		'width=800,toolbar=1,resizable=1,scrollbars=yes,height=600,top=0,left=100');
		}
	}
	var loadMapData = function(kdmodul,nmmodul,tipe,parent,aktif,controller,desc,pos){
		document.getElementById('utxtKodeModule').value = kdmodul;
		document.getElementById('utxtNamaModule').value = nmmodul;
		document.getElementById('uselJenis').value = tipe;
		$('#uselParent').prop('selectedIndex', -1); 
		  $.ajax({
		  	url: "<?=base_url()?>module/DropdownParentList",
		    method: 'GET', 
		    data: { jenis: tipe }, 
		    dataType: 'json',
		    success: function(data) { 
		      $('#uselParent').empty(); 
		      $.each(data, function(index, item) {
		      	if (parent==item.module_id)
		      	{
		      		$('#uselParent').append('<option value="' + item.module_id + '" selected >' + item.module_id + ' - ' + item.module_name + '</option>');
		      	}
		      	else
		      	{
		      		$('#uselParent').append('<option value="' + item.module_id + '">' + item.module_id + ' - ' + item.module_name + '</option>');	
		      	}
		      }); 
		    },
		    error: function(xhr, status, error) {
		      console.error('AJAX Error:', error); 
		    }
		  });

		//document.getElementById('uselParent').value = parent;
		// $.ajax({
		// 	url: "<?=base_url()?>module/DropdownParentList?value="+parent,
		// 	dataType: 'json',
		// 	success: function(response) {
		// 		if(response!='' && response.code==1){
		// 			var select2 = $('#uselParent');
		// 			var option = new Option(response.data.text, response.data.value, true, true);
		// 		  	select2.append(option).trigger('change');
		// 		}
				
		// 	}
		// });
		// myArray = parent.split(" | ");
		// var text = myArray[0];
		// var value = myArray[1];
		// $("#uselParent").append(new Option(text, value, true, true)).trigger('change');
		

		if(aktif == "1"){
		document.getElementById('uchkAktif').checked = true;
		} else {
		document.getElementById('uchkAktif').checked = false;
		}
		document.getElementById('utxtNamaCtr').value = controller;
		document.getElementById('utxtKeterangan').value = desc;
		document.getElementById('uPosition').value = pos;
		document.getElementById('utxtKodeModuleOld').value = kdmodul;
	}
	var loadMapData2 = function(kdmodul){
		document.getElementById('utxtKodeModuleOld').value = kdmodul;
	}
	function updateIsActive(module_id,event){

		var checkbox = event.target;
		var is_active = checkbox.checked ? 1 : 0;
		console.log(is_active);
		console.log('module '+module_id);


		var formData = new FormData();
		formData.append('submit', 'submit');
		formData.append('module_id',module_id);
		formData.append('is_active',is_active);
		$.ajax({
		  url: '<?=base_url()?>module/UpdateIsActive',
		  type: 'POST',
		  data: formData,
		  processData: false,
		  contentType: false,
		  success: function(response) {
			// Aksi yang ingin dilakukan ketika request berhasil
			var json = JSON.parse(response);
			if(json.code==1){
				refrestDataTable();
			}
			console.log(json);
			alert(json.msg);
		  },
		  error: function(xhr, status, error) {
			// Aksi yang ingin dilakukan ketika request gagal
			console.log(xhr.responseText);
		  }
		});
	}
	function refrestDataTable(){
		var object = {};
		var formData = new FormData();
		formData.append("submit","filter");
		formData.forEach(function(value, key){
		  object[key] = value;
		});
		TblMsServiceData = object;
		$('#TblModule').DataTable().ajax.reload(null, true);
	}
</script>