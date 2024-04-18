<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
     
    <div id="disablingDiv" class="disablingDiv">
    </div>
    <div id="loading" class="loader">
    </div>


<div class="container">
	<div class="row">
		<div class="page-title">Master Service Kerusakan</div>
		<div class="col-12">
			<div align="right">
				<?php if($_SESSION["can_create"]==true){ ?>
				<button type="button" class="btn btn-dark" onclick="btn_add()">Tambah</button>
				<?php } ?>
				<?php if($_SESSION["can_print"]==true){ ?>
				<a target="_blank" href="<?=base_url()?>masterservice/kerusakan?export=1">
					<button type="button" class="btn btn-dark">Export</button>
				</a>
				<?php } ?>
			</div>
		</div>
		<div class="col-12">
			<table id="table" class="table table-striped table-bordered" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th scope="col">Kode Kerusakan</th>
						<th scope="col">Nama Kerusakan</th>
						<th scope="col">Is Active</th>
						<th scope="col">Last Modified By</th>
						<th scope="col">Last Modified Date</th>
						<?php if($_SESSION["can_update"]==true){ ?>
						<th scope="col">Aksi</th>
						<?php } ?>
					</tr>
				</thead>
				<tbody>

				</tbody>
			</table>
		</div>
	</div>
	<div class="modal fade" id="m-add" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<!-- <form id="f-add" action="<?php //base_url()?>masterservice/kerusakan" method="POST"> -->
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Master Service Kerusakan</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="row">
							<!-- <div class="col-3">Kode Kerusakan</div>
							<div class="col-9">
								<input type="text" name="kode" class="form-control">
							</div> -->

							<div class="col-3">Nama Kerusakan</div>
							<div class="col-9">
								<input type="text" name="nama" id="nama_add" class="form-control">
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<input type="button" name="submit" id="addbtn" class="btn btn-dark" value="Tambah">
						<button type="button" class="btn btn-dark" data-dismiss="modal">Close</button>
					</div>
				</div>
			<!-- </form> -->
		</div>
	</div>
	<div class="modal fade" id="m-edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<!-- <form id="f-edit" action="<?php //base_url()?>masterservice/kerusakan" method="POST"> -->
			<form id="f-edit" method="POST">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Master Service kerusakan</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="row">
							<input type="hidden" name="kode" id="kode" class="form-control" readonly>

							<div class="col-3">Nama Kerusakan</div>
							<div class="col-9">
								<input type="text" name="nama" id="nama_edit" class="form-control">
							</div>

							<div class="col-3"></div>
							<div class="col-3">
								<input type="checkbox" name="status" id="status" value="1"> Aktif
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<input type="button" name="submit" id="editbtn" class="btn btn-dark" value="Ubah">
						<button type="button" class="btn btn-dark" data-dismiss="modal">Close</button>
					</div>
				</div>
			<!-- </form> -->
		</div>
	</div>
</div>
<script type="text/javascript">
	// $(document).ready(function(){
	// 	$('#table').DataTable({
	// 	    "pageLength": 10
	// 	});
	// });
	function btn_add(){
		$("#m-add").modal("show");
	}
	function btn_edit(kode,nama,status){
		status = (status == '1' ? true : false);
		$("#f-edit input[name='kode']").val(kode);
		$("#f-edit input[name='nama']").val(nama);
		$("#f-edit input[name='status']").prop("checked",status);
		$("#m-edit").modal("show");
	}
</script>


			    <script>
			    	
					function filter(){
				  	 	$("#table").dataTable().fnFilter();
				    };

			    	
			        $(document).ready(function() {
			             $('#table').dataTable( {
								        "bProcessing": true,
								        "bServerSide": true,
										"columnDefs": [
										{"title":"Kode Kerusakan","targets": 0, },
										{"title":"Nama Kerusakan","targets": 1,},
										{"title":"Is Active","targets": 2,},
										{"title":"Last Modified By","targets": 3,},
										{"title":"Last Modified Date","targets": 4,"orderable": false},
										{"title":"Aksi","targets": 5, "orderable": false},
										],
								        "sAjaxSource": '<?=base_url()?>masterservice/listkerusakan',
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


			            // Handler untuk event klik pada tombol delete
			            $('#table').on('click', '.delete-btn', function() {
			                var encodedelete = $(this).data('encodedelete');
			                delete_data(encodedelete);
			            });

			            function delete_data(e) {
			                if (confirm('Apakah anda yakin ingin hapus?')) {

			                  	$("#disablingDiv").show();
      							$("#loading").show();

			                    var data = 'kode=' + e;
			                    	data += '&submit=Delete';

			                    $.ajax({
			                        type: 'post',
			                        url: '<?php echo site_url('masterservice/kerusakan'); ?>',
			                        data: data,
			                        success: function (data) {

			                            if (data == 'success') {
			                                filter();
			                            } else if (data == 'tidak_bisa_hapus') {
			                            	$("#disablingDiv").hide();
      										$("#loading").hide();
			                                Swal.fire({
			                                    title: '',
			                                    html: 'Data tidak dapat dihapus, masih dipakai di master service',
			                                    icon: 'error',
			                                    confirmButtonText: 'Close'
			                                })
			                            } else {
			                            	$("#disablingDiv").hide();
      										$("#loading").hide();
			                                Swal.fire({
			                                    title: '',
			                                    html: 'Data tidak dapat dihapus',
			                                    icon: 'error',
			                                    confirmButtonText: 'Close'
			                                })
			                            }
			                        }
			                    });
			                }
			            }

				        $('#addbtn').click(function() {
				            $("#m-add").modal("hide");
				        	$("#disablingDiv").show();
      						$("#loading").show();
				            var data='nama='+$('#nama_add').val(); 
				            	data+='&submit=Tambah'; 

				            $.ajax({
				                type: 'POST',
				                url: '<?php echo site_url('masterservice/kerusakan'); ?>',
				                data: data, 
				                success: function(response) {
				                	$('#nama_add').val(""); 
				                	filter();
				                },
				                error: function(xhr, status, error) {
				                    console.error('Error:', error);
									$("#disablingDiv").hide();
      								$("#loading").hide();
				                }
				            });

				        });

				        $('#editbtn').click(function() {
				            $("#m-edit").modal("hide");

      						var status = document.getElementById("status").checked;
      						if(status==true){
      							status = 1;
      						}else{
      							status = 0;
      						}

				            var data = 'kode=' + $('#kode').val();
							data += '&nama=' + $.trim($('#nama_edit').val());  
							data += '&status=' + status;
							data += '&submit=Ubah';


				            $.ajax({
				                type: 'POST',
				                url: '<?php echo site_url('masterservice/kerusakan'); ?>',
				                data: data, 
				                success: function(response) {
				                	if(response=='Ubah_data_gagal'){
				                		Swal.fire({
			                            	title: '',
			                            	html: 'Data tidak dapat diubah',
			                            	icon: 'error',
			                            	confirmButtonText: 'Close'
			                            })
			                            $("#disablingDiv").hide();
      									$("#loading").hide();
				                	}else{
					                	$('#nama_edit').val(""); 
					                	filter();
					                }
				                },
				                error: function(xhr, status, error) {
				                    console.error('Error:', error);
									$("#disablingDiv").hide();
      								$("#loading").hide();
				                }
				            });

				        });


			        });
			    </script>

