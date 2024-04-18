<style type="text/css">
  .disablingDiv{
    z-index:1;
     
    /* make it cover the whole screen */
    position: fixed; 
    top: 0%; 
    left: 0%; 
    width: 100%; 
    height: 100%; 
    overflow: hidden;
    margin:0;
    /* make it white but fully transparent */
    background-color: white; 
    opacity:0.5;  
  }
  .loader {
      position: absolute;
      left: 50%;
      top: 50%;
      z-index: 1;
      width: 150px;
      height: 150px;
      margin: -75px 0 0 -75px;
      border: 16px solid #f3f3f3;
      border-radius: 50%;
      border-top: 16px solid #3498db;
      width: 120px;
      height: 120px;
      -webkit-animation: spin 2s linear infinite;
      animation: spin 2s linear infinite;
  }

  @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
  }
</style>



<!-- loading div -->
<div id="disablingDiv" class="disablingDiv">
</div>
<div id="loading" class="loader">
</div>
<!-- end loading div -->


<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
		<div class="border20 p20">
			<form id="f-filter" action="" method="POST">
				<div class="row" style="margin:0px">
					<div class="col-2">Jenis Barang</div>
					<div class="col-4">
						<select name="kd_jnsbrg" id="kdjnsbrg" onchange="filterdata1();" class="form-control">
							<option value="">ALL</option>
							<?php //foreach($SelectJenisBarang as $s) { ?>
							<!-- <option value="<?php //echo $s['kd_jnsbrg'] ?>"><?php //echo $s['jns_brg'].' - '.$s['kd_jnsbrg'] ?></option> -->
							<?php //} ?>
						</select>
					</div>
					<div class="col-2">Kerusakan</div>
					<div class="col-4">
						<select name="kd_kerusakan" id="kdkerusakan" onchange="filterdata2();" class="form-control">
							<option value="">ALL</option>
							<?php //foreach($SelectKerusakan as $s) { ?>
							<option value="<?php //echo $s['kd_kerusakan'] ?>"><?php //echo $s['nm_kerusakan'].' - '.$s['kd_kerusakan'] ?></option>
							<?php //} ?>
						</select>
					</div>
				</div>
				<div class="row" style="margin:0px">
					<div class="col-2">Penyebab</div>
					<div class="col-4">
						<select name="kd_penyebab" id="kdpenyebab" onchange="filterdata3();" class="form-control">
							<option value="">ALL</option>
							<?php //foreach($SelectPenyebab as $s) { ?>
							<option value="<?php //echo $s['kd_penyebab'] ?>"><?php //echo $s['nm_penyebab'].' - '.$s['kd_penyebab'] ?></option>
							<?php //} ?>
						</select>
					</div>
					<div class="col-2">Perbaikan</div>
					<div class="col-4">
						<select name="kd_perbaikan" id="kdperbaikan" class="form-control">
							<option value="">ALL</option>
							<?php //foreach($SelectPerbaikan as $s) { ?>
							<option value="<?php //echo $s['kd_perbaikan'] ?>"><?php //echo $s['nm_perbaikan'].' - '.$s['kd_perbaikan'] ?></option>
							<?php //} ?>
						</select>
					</div>
				</div>
				<div class="row" style="margin:0px">
					<div class="col-12">
						<input class="btn btn-dark" onclick="filterdatatable()" type="button" name="submit" value="Filter" style="float: right;">
					</div>
				</div>
			</form>
		</div>
		<div class="row">
		<?php if($_SESSION["can_create"]==true){ ?>
			<div class="col-12">
				<button class="btn btn-dark" onclick="btn_add()" style="float:right;">Add New</button>
			</div>
		<?php  } ?>
		</div>
		<div class="row">
			<div class="col-12">
				<table class="table table-bordered" id="tb-master-service">
				</table>
			</div>
		</div>
	
		<!--start pop up form untuk add-->
		<div class="modal fade" id="m-add" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<form id="f-add" action="<?=base_url()?>masterservice/" method="POST">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">Master Service</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<div class="row" style="margin:0px">
								<div class="col-2">Jenis Barang</div>
								<div class="col-4">
									<select name="kd_jnsbrg" id="kdjnsbrg2" class="form-control" style="width: 100%;" required>
										<option value="">NONE</option>
										<?php //foreach($SelectJenisBarang as $s) { ?>
										<!-- <option value="<?php //echo $s['kd_jnsbrg'] ?>"><?php //echo $s['kd_jnsbrg'] ?> - <?php //echo $s['jns_brg'] ?></option> -->
										<?php //} ?>
									</select>
								</div>
								<div class="col-2">Kerusakan</div>
								<div class="col-4">
									<select name="kd_kerusakan" id="kdkerusakan2" class="form-control" style="width: 100%;" required>
										<option value="">NONE</option>
										<?php //foreach($SelectKerusakan as $s) { ?>
										<!-- <option value="<?php //echo $s['kd_kerusakan'] ?>"><?php //echo $s['nm_kerusakan'] ?></option> -->
										<?php //} ?>
									</select>
								</div>
							</div>
							<div class="row" style="margin:0px">
								<div class="col-2">Penyebab</div>
								<div class="col-4">
									<select name="kd_penyebab" id="kdpenyebab2" class="form-control" style="width: 100%;" required>
										<option value="">NONE</option>
										<?php //foreach($SelectPenyebab as $s) { ?>
										<!-- <option value="<?php //echo $s['kd_penyebab'] ?>"><?php //echo $s['nm_penyebab'] ?></option> -->
										<?php //} ?>
									</select>
								</div>
								<div class="col-2">Perbaikan</div>
								<div class="col-4">
									<select name="kd_perbaikan" id="kdperbaikan2" class="form-control" style="width: 100%;" required>
										<option value="">NONE</option>
										<?php //foreach($SelectPerbaikan as $s) { ?>
										<!-- <option value="<?php //echo $s['kd_perbaikan'] ?>"><?php //echo $s['nm_perbaikan'] ?></option> -->
										<?php //} ?>
									</select>
								</div>
							</div>
							<div class="row" style="margin:0px">
								<div class="col-2"></div>
								<div class="col-4">
									<input type="checkbox" name="is_active" value="1" checked> Aktif
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<input type="button" name="submit" class="btn btn-dark" onclick="submit_tambah()" value="Tambah">
							<button type="button" class="btn btn-dark" data-dismiss="modal">Close</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<!--end pop up form untuk add-->
		<!--start pop up form untuk edit-->
		<div class="modal fade" id="m-edit" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<form id="f-edit" action="<?=base_url()?>masterservice/" method="POST">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">Master Service</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<input type="hidden" name="ID">
							<div class="row" style="margin:0px">
								<div class="col-2">Jenis Barang</div>
								<div class="col-4">
									<select name="kd_jnsbrg" id="kdjnsbrg3" class="form-control" style="width: 100%;" required>
										<option value="">NONE</option>
										<?php //foreach($SelectJenisBarang as $s) { ?>
										<!-- <option value="<?php //echo $s['kd_jnsbrg'] ?>"><?php //echo $s['kd_jnsbrg'] ?> - <?php //echo $s['jns_brg'] ?></option> -->
										<?php //} ?>
									</select>
								</div>
								<div class="col-2">Kerusakan</div>
								<div class="col-4">
									<select name="kd_kerusakan" id="kdkerusakan3" class="form-control" style="width: 100%;" required>
										<option value="">NONE</option>
										<?php //foreach($SelectKerusakan as $s) { ?>
										<!-- <option value="<?php //echo $s['kd_kerusakan'] ?>"><?php //echo $s['nm_kerusakan'] ?></option> -->
										<?php //} ?>
									</select>
								</div>
							</div>
							<div class="row" style="margin:0px">
								<div class="col-2">Penyebab</div>
								<div class="col-4">
									<select name="kd_penyebab" id="kdpenyebab3" class="form-control" style="width: 100%;" required>
										<option value="">NONE</option>
										<?php //foreach($SelectPenyebab as $s) { ?>
										<!-- <option value="<?php //echo $s['kd_penyebab'] ?>"><?php //echo $s['nm_penyebab'] ?></option> -->
										<?php //} ?>
									</select>
								</div>
								<div class="col-2">Perbaikan</div>
								<div class="col-4">
									<select name="kd_perbaikan" id="kdperbaikan3" class="form-control" style="width: 100%;" required>
										<option value="">NONE</option>
										<?php //foreach($SelectPerbaikan as $s) { ?>
										<!-- <option value="<?php //echo $s['kd_perbaikan'] ?>"><?php //echo $s['nm_perbaikan'] ?></option> -->
										<?php //} ?>
									</select>
								</div>
							</div>
							<div class="row" style="margin:0px">
								<div class="col-2"></div>
								<div class="col-4">
									<input type="checkbox" name="is_active" value="1"> Aktif
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<?php if($_SESSION["can_delete"]==true){ ?>
							<input type="button" name="submit" onclick="submit_delete()" class="btn btn-danger-dark" value="Delete">
							<?php } ?>
							<?php //if($_SESSION["can_update"]==true){ ?>
							<input type="button" name="submit" onclick="submit_edit()"class="btn btn-dark" value="Edit">
							<?php //} ?>
							<button type="button" class="btn btn-dark" data-dismiss="modal">Close</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<!--end pop up form untuk edit-->
	</div>
</div>  
<script type="text/javascript">
	var tampklik=0;

		function filterdatatable(){
			$("#loading").show();
    		$("#disablingDiv").show();
	  	 	$("#tb-master-service").dataTable().fnFilter(
	     		document.getElementById('kdjnsbrg').value,0,
	     		document.getElementById('kdkerusakan').value,0,
	      	);
	      	$("#tb-master-service").dataTable().fnFilter(
	     		document.getElementById('kdpenyebab').value,1,
	     		document.getElementById('kdperbaikan').value,1,
	      	);
	      	$("#loading").hide();
    		$("#disablingDiv").hide();

	    };

	$(document).ready(function() {


	    $('#tb-master-service').dataTable( {
	        "bProcessing": true,
	        "bServerSide": true,
			"columnDefs": [
			{"title":"Nama Jenis Barang","targets": 0, },
			{"title":"Nama Kerusakan","targets": 1,},
			{"title":"Nama Penyebab","targets": 2,},
			{"title":"Nama Perbaikan","targets": 3,},
			{"title":"Is Active","targets": 4,"orderable": false},
			{"title":"Modified By","targets": 5,},
			{"title":"Modified Date","targets": 6,},
			{"title":"Aksi","targets": 7, "orderable": false},
			],
	        "sAjaxSource": '<?=base_url()?>masterservice/GetMasterService',
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


		$('#f-filter select[name="kd_jnsbrg"]').select2();
		$('#f-filter select[name="kd_kerusakan"]').select2();
		$('#f-filter select[name="kd_penyebab"]').select2();
		$('#f-filter select[name="kd_perbaikan"]').select2();
		
		$('#f-add select[name="kd_jnsbrg"]').select2();
		$('#f-add select[name="kd_kerusakan"]').select2();
		$('#f-add select[name="kd_penyebab"]').select2();
		$('#f-add select[name="kd_perbaikan"]').select2();
		
		$('#f-edit select[name="kd_jnsbrg"]').select2();
		$('#f-edit select[name="kd_kerusakan"]').select2();
		$('#f-edit select[name="kd_penyebab"]').select2();
		$('#f-edit select[name="kd_perbaikan"]').select2();

	});





	// $('.loading').show();
	// var TblUserData = {};
	// var object = {};
	// var formData = new FormData();
	// formData.forEach(function(value, key){
	// 	object[key] = value;
	// });
	// TblmasterserviceData = object;
	// Tblmasterservice = $('#tb-master-service').DataTable({
	// 	//"order":[[0,"desc"]],
	// 	responsive: true,
	// 	"columnDefs": [
	// 	{"title":"Nama Jenis Barang","targets": 0, },
	// 	{"title":"Nama Kerusakan","targets": 1,},
	// 	{"title":"Nama Penyebab","targets": 2,},
	// 	{"title":"Nama Perbaikan","targets": 3,},
	// 	{"title":"Is Active","targets": 4,"orderable": false},
	// 	{"title":"Modified By","targets": 5,},
	// 	{"title":"Modified Date","targets": 6,},
	// 	<?php if($_SESSION["can_update"]==true){ ?>
	// 	{"title":"Aksi","targets": 7, "orderable": false},
	// 	<?php } ?>
	// 	],
	// 	"columns": [
	// 	{ "data": "jns_brg" },
	// 	{ "data": "nm_kerusakan" },
	// 	{ "data": "nm_penyebab" },
	// 	{ "data": "nm_perbaikan" },
	// 	{ "data": "is_active" },
	// 	{ "data": "modified_by" },
	// 	{ "data": "modified_date" },
	// 	<?php if($_SESSION["can_update"]==true){ ?>
	// 	{ "data": "aksi" },
	// 	<?php } ?>
	// 	],
	// 	processing: true,
	// 	serverSide: true,
	// 	ajax: {
	// 		type:'GET',
	// 		data: function(d){
	// 			return $.extend(d,TblmasterserviceData);
	// 		},
	// 		"dataSrc": function ( json ) {
	// 			return json.data;
	// 		}   
	// 	},
	// });







	function submit_tambah(){
		
		var formData = new FormData($("#f-add")[0]);
		formData.append("submit","Tambah");
		
		var url = $("#f-add")[0].action;
		$.ajax({
			url: url,
			type: 'post',
			cache: false,
			contentType: false,
			processData: false,
			data: formData,
			success: function (data) {
				var json = JSON.parse(data);
				if(json.code=="success"){
					filterdatatable();
					$("#m-add").modal('hide');
				}
				alert(json.messages[0]);
			}
		});
	}
	function submit_delete(){
		var isConfirm = confirm('Apakah anda yakin ingin hapus?');
		if(isConfirm){
		
			var formData = new FormData($("#f-edit")[0]);
			formData.append("submit","Delete");

			var url = $("#f-edit")[0].action;
			$.ajax({
				url: url,
				type: 'post',
				cache: false,
				contentType: false,
				processData: false,
				data: formData,
				success: function (data) {
					var json = JSON.parse(data);
					if(json.code=="success"){
						filterdatatable();
						$("#m-edit").modal('hide');
					}
					alert(json.messages[0]);
				}
			});
		}
	}
	function submit_edit(){
		var isConfirm = confirm('Apakah anda yakin ingin ubah?');
		if(isConfirm){
			var formData = new FormData($("#f-edit")[0]);
			formData.append("submit","Edit");

			var url = $("#f-edit")[0].action;
			$.ajax({
				url: url,
				type: 'post',
				cache: false,
				contentType: false,
				processData: false,
				data: formData,
				success: function (data) {
					var json = JSON.parse(data);
					if(json.code=="success"){
						filterdatatable();
						$("#m-edit").modal('hide');
					}
					alert(json.messages[0]);
				}
			});
		}
	}
	function filterdata1(){
		var jenisbarang = document.getElementById('kdjnsbrg').value;  
		var kerusakan = document.getElementById('kdkerusakan').value;  
		var penyebab = document.getElementById('kdpenyebab').value;  
		Kerusakan('filter',jenisbarang);
		Penyebab('filter',jenisbarang,kerusakan);
		Perbaikan('filter',jenisbarang,kerusakan,penyebab);
	}

	function filterdata2(){
		var jenisbarang = document.getElementById('kdjnsbrg').value; 
		var kerusakan = document.getElementById('kdkerusakan').value; 
		var penyebab = document.getElementById('kdpenyebab').value; 
		Penyebab('filter',jenisbarang,kerusakan);
		Perbaikan('filter',jenisbarang,kerusakan,penyebab);
	}

	function filterdata3(){
		var jenisbarang = document.getElementById('kdjnsbrg').value; 
		var kerusakan = document.getElementById('kdkerusakan').value; 
		var penyebab = document.getElementById('kdpenyebab').value; 
		Perbaikan('filter',jenisbarang,kerusakan,penyebab);
	}

	JenisBarang('filter');
	// Kerusakan('','');
	// Penyebab('','','');
	// Perbaikan('','','','');

		function JenisBarang(aksi=''){
			if(aksi=='filter'){
				document.getElementById('kdjnsbrg').innerHTML='<option value="">Loading...</option>';
			}else{
				document.getElementById('kdjnsbrg2').innerHTML='<option value="">Loading...</option>';
				document.getElementById('kdjnsbrg3').innerHTML='<option value="">Loading...</option>';
			}
			$.ajax({
				type  : 'POST', 
				url   : '<?php echo site_url('masterservice/SelectJenisBarang') ?>',
				success : function(obj) {

					var json = JSON.parse(obj); 
					var jum = json.length;

	                var option='<option value="ALL">ALL</option>';
	                if(jum>0){
	                  	for(var i=0; i<jum; i++){
	                    	option += '<option value="'+json[i].kd_jnsbrg+'">'+json[i].kd_jnsbrg+' - '+json[i].jns_brg+'</option>';
	                  	}
	                  	if(aksi=='filter'){
		                	document.getElementById('kdjnsbrg').innerHTML=option;
		                }else{
		                	document.getElementById('kdjnsbrg2').innerHTML=option;
		                	document.getElementById('kdjnsbrg3').innerHTML=option;
		                }

	                }else{
	                	if(aksi=='filter'){
		                	document.getElementById('kdjnsbrg').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                }else{
		                	document.getElementById('kdjnsbrg2').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                	document.getElementById('kdjnsbrg3').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                }
	                }

					$("#loading").hide();
    				$("#disablingDiv").hide();
				}
			})
		}

		function Kerusakan(aksi='',jenisbarang){
			if(aksi=='filter'){
				document.getElementById('kdkerusakan').innerHTML='<option value="">Loading...</option>';
			}else{
				document.getElementById('kdkerusakan2').innerHTML='<option value="">Loading...</option>';
				document.getElementById('kdkerusakan3').innerHTML='<option value="">Loading...</option>';
			}
			$.ajax({
				type  : 'POST', 
				url   : '<?php echo site_url('masterservice/SelectKerusakan?jenisbarang=') ?>'+jenisbarang,
				success : function(obj) {

					var json = JSON.parse(obj); 
					var jum = json.length;

	                var option='<option value="ALL">ALL</option>';
	                if(jum>0){
	                  	for(var i=0; i<jum; i++){
	                    	option += '<option value="'+json[i].kd_kerusakan+'">'+json[i].kd_kerusakan+' - '+json[i].nm_kerusakan+'</option>';
	                  	}
	                  	if(aksi=='filter'){
		                	document.getElementById('kdkerusakan').innerHTML=option;
		                }else{
		                	document.getElementById('kdkerusakan2').innerHTML=option;
		                	document.getElementById('kdkerusakan3').innerHTML=option;
		                }

	                }else{
	                	if(aksi=='filter'){
		                	document.getElementById('kdkerusakan').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                }else{
		                	document.getElementById('kdkerusakan2').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                	document.getElementById('kdkerusakan3').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                }
	                }
				}
			})
		}

		function Penyebab(aksi='',jenisbarang,kerusakan){
			if(aksi=='filter'){
				document.getElementById('kdpenyebab').innerHTML='<option value="">Loading...</option>';
			}else{
				document.getElementById('kdpenyebab2').innerHTML='<option value="">Loading...</option>';
				document.getElementById('kdpenyebab3').innerHTML='<option value="">Loading...</option>';
			}
			$.ajax({
				type  : 'POST', 
				url   : '<?php echo site_url('masterservice/SelectPenyebab?jenisbarang=') ?>'+jenisbarang+'&kerusakan='+kerusakan,
				success : function(obj) {

					var json = JSON.parse(obj); 
					var jum = json.length;

	                var option='<option value="ALL">ALL</option>';
	                if(jum>0){
	                  	for(var i=0; i<jum; i++){
	                    	option += '<option value="'+json[i].kd_penyebab+'">'+json[i].kd_penyebab+' - '+json[i].nm_penyebab+'</option>';
	                  	}
	                  	if(aksi=='filter'){
		                	document.getElementById('kdpenyebab').innerHTML=option;
		                }else{
		                	document.getElementById('kdpenyebab2').innerHTML=option;
		                	document.getElementById('kdpenyebab3').innerHTML=option;
		                }

	                }else{
	                	if(aksi=='filter'){
		                	document.getElementById('kdpenyebab').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                }else{
		                	document.getElementById('kdpenyebab2').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                	document.getElementById('kdpenyebab3').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                }
	                }
				}
			})
		}

		function Perbaikan(aksi='',jenisbarang,kerusakan,penyebab){
			if(aksi=='filter'){
				document.getElementById('kdperbaikan').innerHTML='<option value="">Loading...</option>';
			}else{
				document.getElementById('kdperbaikan2').innerHTML='<option value="">Loading...</option>';
				document.getElementById('kdperbaikan3').innerHTML='<option value="">Loading...</option>';
			}
			$.ajax({
				type  : 'POST', 
				url   : '<?php echo site_url('masterservice/SelectPerbaikan?jenisbarang=') ?>'+jenisbarang+'&kerusakan='+kerusakan+'&penyebab='+penyebab,
				success : function(obj) {

					var json = JSON.parse(obj); 
					var jum = json.length;

	                var option='<option value="ALL">ALL</option>';
	                if(jum>0){
	                  	for(var i=0; i<jum; i++){
	                    	option += '<option value="'+json[i].kd_perbaikan+'">'+json[i].kd_perbaikan+' - '+json[i].nm_perbaikan+'</option>';
	                  	}
	                  	if(aksi=='filter'){
		                	document.getElementById('kdperbaikan').innerHTML=option;
		                }else{
		                	document.getElementById('kdperbaikan2').innerHTML=option;
		                	document.getElementById('kdperbaikan3').innerHTML=option;
		                }

	                }else{
	                	if(aksi=='filter'){
		                	document.getElementById('kdperbaikan').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                }else{
		                	document.getElementById('kdperbaikan2').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                	document.getElementById('kdperbaikan3').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                }
	                }

				}
			})
		}



	// function filterdatatable(){
	// 	var object = {};
	// 	var formData = new FormData($("#f-filter")[0]);
	// 	formData.forEach(function(value, key){
	// 		object[key] = value;
	// 	});
	// 	TblmasterserviceData = object;
	// 	$('#tb-master-service').DataTable().ajax.reload(null, true);
	// }

	function btn_add(){
		$("#m-add").modal("show");
		if(tampklik==0){
			JenisBarangv2('');
			Kerusakanv2('');
			Penyebabv2('');
			Perbaikanv2('');
			tampklik++;
		}
	}

	function btn_edit(ID,Nm_JnsBrg,Nm_Kerusakan,Nm_Penyebab,Nm_Perbaikan, is_active){
		is_active = (is_active == '1' ? true : false);
		$("#f-edit input[name='ID']").val(ID);
		myArray = Nm_JnsBrg.split(" - ");
		$("#f-edit select[name='kd_jnsbrg']").append(new Option(myArray[0], myArray[1], true, true)).trigger('change');
		myArray = Nm_Kerusakan.split(" - ");
		$("#f-edit select[name='kd_kerusakan']").append(new Option(myArray[0], myArray[1], true, true)).trigger('change');
		myArray = Nm_Penyebab.split(" - ");
		$("#f-edit select[name='kd_penyebab']").append(new Option(myArray[0], myArray[1], true, true)).trigger('change');
		myArray = Nm_Perbaikan.split(" - ");
		$("#f-edit select[name='kd_perbaikan']").append(new Option(myArray[0], myArray[1], true, true)).trigger('change');
		$("#f-edit input[name='is_active']").prop("checked",is_active);
		
		$("#m-edit").modal("show");

		if(tampklik==0){
			JenisBarang('');
			Kerusakan('','');
			Penyebab('','','');
			Perbaikan('','','','');
			tampklik++;
		}
	}



		function JenisBarangv2(aksi=''){
			if(aksi=='filter'){
				document.getElementById('kdjnsbrg').innerHTML='<option value="">Loading...</option>';
			}else{
				document.getElementById('kdjnsbrg2').innerHTML='<option value="">Loading...</option>';
				document.getElementById('kdjnsbrg3').innerHTML='<option value="">Loading...</option>';
			}
			$.ajax({
				type  : 'POST', 
				url   : '<?php echo site_url('masterservice/SelectJenisBarangv2') ?>',
				success : function(obj) {

					var json = JSON.parse(obj); 
					var jum = json.length;

	                var option='<option value="ALL">ALL</option>';
	                if(jum>0){
	                  	for(var i=0; i<jum; i++){
	                    	option += '<option value="'+json[i].kd_jnsbrg+'">'+json[i].kd_jnsbrg+' - '+json[i].jns_brg+'</option>';
	                  	}
	                  	if(aksi=='filter'){
		                	document.getElementById('kdjnsbrg').innerHTML=option;
		                }else{
		                	document.getElementById('kdjnsbrg2').innerHTML=option;
		                	document.getElementById('kdjnsbrg3').innerHTML=option;
		                }

	                }else{
	                	if(aksi=='filter'){
		                	document.getElementById('kdjnsbrg').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                }else{
		                	document.getElementById('kdjnsbrg2').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                	document.getElementById('kdjnsbrg3').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                }
	                }

					$("#loading").hide();
    				$("#disablingDiv").hide();
				}
			})
		}

		function Kerusakanv2(aksi=''){
			if(aksi=='filter'){
				document.getElementById('kdkerusakan').innerHTML='<option value="">Loading...</option>';
			}else{
				document.getElementById('kdkerusakan2').innerHTML='<option value="">Loading...</option>';
				document.getElementById('kdkerusakan3').innerHTML='<option value="">Loading...</option>';
			}
			$.ajax({
				type  : 'POST', 
				url   : '<?php echo site_url('masterservice/SelectKerusakanv2') ?>',
				success : function(obj) {

					var json = JSON.parse(obj); 
					var jum = json.length;

	                var option='<option value="ALL">ALL</option>';
	                if(jum>0){
	                  	for(var i=0; i<jum; i++){
	                    	option += '<option value="'+json[i].kd_kerusakan+'">'+json[i].kd_kerusakan+' - '+json[i].nm_kerusakan+'</option>';
	                  	}
	                  	if(aksi=='filter'){
		                	document.getElementById('kdkerusakan').innerHTML=option;
		                }else{
		                	document.getElementById('kdkerusakan2').innerHTML=option;
		                	document.getElementById('kdkerusakan3').innerHTML=option;
		                }

	                }else{
	                	if(aksi=='filter'){
		                	document.getElementById('kdkerusakan').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                }else{
		                	document.getElementById('kdkerusakan2').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                	document.getElementById('kdkerusakan3').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                }
	                }
				}
			})
		}

		function Penyebabv2(aksi=''){
			if(aksi=='filter'){
				document.getElementById('kdpenyebab').innerHTML='<option value="">Loading...</option>';
			}else{
				document.getElementById('kdpenyebab2').innerHTML='<option value="">Loading...</option>';
				document.getElementById('kdpenyebab3').innerHTML='<option value="">Loading...</option>';
			}
			$.ajax({
				type  : 'POST', 
				url   : '<?php echo site_url('masterservice/SelectPenyebabv2') ?>',
				success : function(obj) {

					var json = JSON.parse(obj); 
					var jum = json.length;

	                var option='<option value="ALL">ALL</option>';
	                if(jum>0){
	                  	for(var i=0; i<jum; i++){
	                    	option += '<option value="'+json[i].kd_penyebab+'">'+json[i].kd_penyebab+' - '+json[i].nm_penyebab+'</option>';
	                  	}
	                  	if(aksi=='filter'){
		                	document.getElementById('kdpenyebab').innerHTML=option;
		                }else{
		                	document.getElementById('kdpenyebab2').innerHTML=option;
		                	document.getElementById('kdpenyebab3').innerHTML=option;
		                }

	                }else{
	                	if(aksi=='filter'){
		                	document.getElementById('kdpenyebab').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                }else{
		                	document.getElementById('kdpenyebab2').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                	document.getElementById('kdpenyebab3').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                }
	                }
				}
			})
		}

		function Perbaikanv2(aksi=''){
			if(aksi=='filter'){
				document.getElementById('kdperbaikan').innerHTML='<option value="">Loading...</option>';
			}else{
				document.getElementById('kdperbaikan2').innerHTML='<option value="">Loading...</option>';
				document.getElementById('kdperbaikan3').innerHTML='<option value="">Loading...</option>';
			}
			$.ajax({
				type  : 'POST', 
				url   : '<?php echo site_url('masterservice/SelectPerbaikanv2') ?>',
				success : function(obj) {

					var json = JSON.parse(obj); 
					var jum = json.length;

	                var option='<option value="ALL">ALL</option>';
	                if(jum>0){
	                  	for(var i=0; i<jum; i++){
	                    	option += '<option value="'+json[i].kd_perbaikan+'">'+json[i].kd_perbaikan+' - '+json[i].nm_perbaikan+'</option>';
	                  	}
	                  	if(aksi=='filter'){
		                	document.getElementById('kdperbaikan').innerHTML=option;
		                }else{
		                	document.getElementById('kdperbaikan2').innerHTML=option;
		                	document.getElementById('kdperbaikan3').innerHTML=option;
		                }

	                }else{
	                	if(aksi=='filter'){
		                	document.getElementById('kdperbaikan').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                }else{
		                	document.getElementById('kdperbaikan2').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                	document.getElementById('kdperbaikan3').innerHTML='<option value="">Data Tidak Ditemukan</option>';
		                }
	                }

				}
			})
		}

	</script>	