<style>
	table{
	font-size:12px;
	}
	
	.table tr{
	color:black;
	font-style: normal;
	text-align:left;
	}
	
	.table tr:nth-child(even) {background-color: #f8f8f8;}
	
	.table tr td{
		padding:2px;
	}
	.loading-circle {
	    width: 50px;
	    height: 50px;
	    border-radius: 50%;
	    border: 5px solid #3498db;
	    border-top-color: transparent;
	    animation: spin 2s linear infinite;
	    position: absolute;
	    top: 50%;
	    left: 50%;
	    margin-left: -25px;
	    margin-top: -25px;
	    display: none;
	}

	@keyframes spin {
	    0% { transform: rotate(0deg); }
	    100% { transform: rotate(360deg); }
	}
</style>

<div class="container">
	<div style="overflow-y: hidden;overflow-x:scroll">
		<div style="float:right;margin-bottom: 10px;">
			<button type="button" onclick="btn_import()">Import</button>
			<button type="button" onclick="btn_export()">Export</button>
		</div>
		<div style=""></div>
		<table id="myTable" class="table table-bordered"></table>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="m-import" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<div class="">Import Master Objek Pajak <div class="loading-circle"></div></div>
			</div>
			<div class="modal-body">
				<form id="f-import" action="<?=$urlImport?>" method="post" enctype="multipart/form-data">
					<input type="file" name="excelFile" id="fileInput" accept=".xlsx, .xls">
        			<button type="button" name="submit" id="uploadButton">Upload</button>
				</form>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready( function () {
		var myTable = $('#myTable').DataTable({
			"processing": true,
            "serverSide": true,
            "ajax": {
                url: "<?=$getMasterObjekPajak?>",
                type: "POST"
            },
            "columnDefs": [
				{"title":"KODE OBJEK PAJAK","targets": 0, "orderable": true},
				{"title":"NAMA OBJEK PAJAK","targets": 1, "orderable": true},
				{"title":"PASAL PPH",		"targets": 2, "orderable": true},
				{"title":"IS ACTIVE",		"targets": 3, "orderable": false},
				{"title":"MODIFIED BY",		"targets": 4, "orderable": true},
				{"title":"MODIFIED DATE",	"targets": 5, "orderable": true},
				
			],
            "columns": [
                {"data": "kode_objek_pajak"},
                {"data": "nama_objek_pajak"},
                {"data": "pasal_pph"},
                {"data": "is_active"},
                {"data": "modified_by"},
                {"data": "modified_date"}
            ]
		});


		$("#uploadButton").click(function(){
			$(".loading-circle").css({"display":"block"});
			$("#fileInput").prop("disabled",true);
			$("#uploadButton").prop("disabled",true);

			var url = $("#f-import")[0].action;
            var fileInput = document.getElementById('fileInput');
            var file = fileInput.files[0];
            var formData = new FormData();
            formData.append('excelFile', file);
            formData.append('submit','Upload');

            $.ajax({
                url: url, // Ganti dengan URL endpoint Anda
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                	$(".loading-circle").css({"display":"none"});
                	$("#fileInput").prop("disabled",false);
					$("#uploadButton").prop("disabled",false);
					var json = JSON.parse(response);
                    // console.log(json);
                    // Tindakan lanjutan setelah berhasil mengunggah
                    alert(json.msg);
                    $("#f-import")[0].reset();
                    $('#myTable').DataTable().ajax.reload();
                    $("#m-import").modal('hide');


                },
                error: function(error) {
                	$(".loading-circle").css({"display":"none"});
                	$("#fileInput").prop("disabled",false);
					$("#uploadButton").prop("disabled",false);

                    // console.error('Gagal mengirim file: ', error);
                    alert('Gagal mengirim file: ', error);
                    // Tindakan lanjutan jika terjadi kesalahan
                }
            });
        });
	});
	function btn_import(){
		$("#m-import").modal('show');
	}
	function btn_export(){
		window.location.href="<?=$urlExport?>";
	}
	
</script>
