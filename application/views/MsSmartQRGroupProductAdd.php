<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>
</style>
<script>
</script>

<div id="notifikasi">
	<?php
		if(ISSET($msg)) {
			if($msg == 'success') {
				echo '
				<div class="msg msg-danger">
					<i class="glyphicon glyphicon-ok-sign"></i>
					'.$description.'
				</div>';
			}
			if($msg == 'failed') {
				echo '
				<div class="msg msg-danger">
					<i class="glyphicon glyphicon-remove-circle"></i>
					'.$description.'
				</div>';
			}
		}
		// if ($this->session->flashdata('success_insert')) {
			// echo '
			// <div class="msg msg-success">
			// <i class="glyphicon glyphicon-ok-sign"></i>
			// '.$this->session->flashdata('success_insert').'
			// </div>';
		// }
		// if ($this->session->flashdata('success_update')) {
			// echo '
			// <div class="msg msg-success">
			// <i class="glyphicon glyphicon-ok-sign"></i>
			// '.$this->session->flashdata('success_update').'
			// </div>';
		// }
	?>
</div>


<div class="container">
	<div class="form_title" style="text-align: center;"> 
			<a href="<?php echo base_url('MsSmartQR/GroupProduct') ?>" class="float-left">
				<i class="glyphicon glyphicon-dark glyphicon-circle-arrow-left" style="font-size:200%"></i>
			</a>
			CREATE MASTER GROUP TIPE BARANG 
	</div>
	<br>
	<?php echo form_open('MsSmartQR/GroupProductInsert',array('id' => 'myformXXX')); ?>
	<div class="border20 p20">
		<div class="row">
			<div class="col-3 col-m-4"><big>Group Tipe Barang</big><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="text" name="tipe" id="group_tipe_barang" class="form-control form-control-dark" placeholder="Ketik Group Tipe Barang" onkeyup="javascript:canAddBarang()" required>
			</div>
		</div>
		<div class="row">
			<div class="col-3 col-m-4"><big>Merk</big><br><small><em>Required</em></small></div>
			<div class="col-4 col-m-3">
				<select class="form-control form-control-dark" name="merk" id="merk" onchange="javascript:loadBarangList()" required>
					<option value="">Pilih Merk</option>
					<?php
						foreach($merks as $merk){
							echo "<option value='".$merk."'>".$merk."</option>";
						}
					?>
				</select>
			</div>
		</div>
		
		<hr class="hr-dark">
		
		<div class="row">
			<div class="col-12 text-right">
				<button type="button" class="btn btn-dark" id="btnBrowseBarang" onclick="javascript:AddProduct()" disabled>
					<i class="glyphicon glyphicon-plus-sign"></i> TAMBAH KD BARANG
				</button>
			</div>
		</div>
		
		<div class="row">
			<div class="col-12">
				<table id="table-add" class="table table-striped table-bordered stripe ">
					<thead>
						<tr>
							<th width="2%" class="no-sort">No</th>
							<th width="30%">KODE BARANG</th>
							<th width="*">NAMA BARANG</th>
							<th width="3%" class="no-sort"></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
		
		<div class="row"  style="text-align: center;"> 
				<input type="submit" name="save" id="btnSubmit" class="btn btn-dark btnSubmit" value="SAVE & CLOSE" onclick="javascript:addNew=0" disabled>
				<input type="submit" name="create_new" class="btn btn-dark btnSubmit" value="SAVE & CREATE NEW" onclick="javascript:addNew=1" disabled> 
		</div>
	</div>
	
	<?php echo form_close(); ?>
	
	<div id="result">
	</div>
</div>


<div id="modal_add" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true" style="background:red; padding:0px 10px">&times;</span>
				</button>
				<h4 class="modal-title" style="text-align: center;"> <strong>BARANG LIST</strong> </h4>
			</div>
			<div class="modal-body">
				<form class="form-horizontal p20">
					<div class="form-group">
						<div class="col-xs-12">
							<div class="input-group input-group-dark mb10">
								<span class="input-group-addon">
									<i class="glyphicon glyphicon-search"></i>
								</span>
								<input type="text" class="form-control form-control-dark " id="filterBarang" placeholder="Ketikkan Kode Barang">
							</div><!-- /input-group -->
							
							<table id="table-group" class="table table-striped table-bordered stripe">
								<thead>
									<tr>
										<th width="30%">KODE BARANG</th>
										<th width="68%">NAMA BARANG</th>
										<th width="2%" class="no-sort"></th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
							<br>
							<div  style="text-align: center;">
								<button type="button" class="btn btn-dark" id="btnAddBarang" onclick="javascript:addBarang()" disabled> ADD TO TABLE KD BARANG </button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->




<script>
	var tableGroup;
	var addNew = 0;
	$(document).ready(function() {
		createTableGroup();
		createTableAdd();
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
					if(data.result=='SUKSES'){
						if(addNew==1){
							window.location.href = '<?php echo site_url("MsSmartQR/GroupProductAdd") ?>';
						}
						else{
							window.location.href = '<?php echo site_url("MsSmartQR/GroupProduct") ?>';
						}
					}
					else{
						alert(data.result+'\n'+data.message);
					}
					
				}
			});
			event.preventDefault(); //Prevent the default submit
		});
		
		$('#table-add tbody').on( 'click', '.deleteBarang', function () {
			tableAdd.row($(this).parents('tr')).remove().draw();
		} );
		
		tableAdd.on('order.dt search.dt', function () {
			let i = 1;
			tableAdd.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
				this.data(i++);
			});
		}).draw();
		
		$(".msg").delay(3000).fadeOut("slow");
	});
	
	$(document).on("click", ".chkKdBrg" , function() {		
		var numberOfChecked = $('input.chkKdBrg:checked').length;
		if(numberOfChecked>0){
			$('#btnAddBarang').prop('disabled', false);
		}
		else{
			$('#btnAddBarang').prop('disabled', true);
		}
	});
	
	function loadBarangList(){
		$('.loading').show();
		tableGroup.clear().draw();
		var merk  = $('#merk').val();
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo site_url("MsSmartQR/GetBarangList?merk=") ?>'+merk,
			dataType: 'json',
			success: function (data) {
				$('.loading').hide();
				for(i=0;i<data.length-1;i++){
					tableGroup.row.add([data[i][0],data[i][1],'<input type="checkbox" class="chkKdBrg" value="'+data[i][0]+' | '+data[i][1]+'">']).draw();
				}
			}
		});
		canAddBarang();
	}
	
	function AddProduct(){
		$('#modal_add').modal("show");
	}
	
	function createTableAdd(){
		tableAdd = $('#table-add').DataTable({
			"sDom": "lrt",
			"pageLength"    : -1,
			"autoWidth": false,
			"lengthChange": false,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false },
			],
			"dom": '<"top"l>rt<"bottom"ip><"clear">',
			"order": [[1, 'desc']],
			
		});
		
		tableAdd.on( 'draw', function () {
			canSave();
		});
	}
	
	function createTableGroup(){
		tableGroup = $('#table-group').DataTable({
			"pageLength"    : 5,
			"autoWidth": false,
			"lengthChange": false,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false },
			],
			"dom": '<"top">rt<"text-center"p><"clear">',
			"order": [[1, 'asc']],
			"language": {
				"paginate": {
					"previous": "<",
					"next": ">"
				},
			},
		});
		
		$('#filterBarang').keyup(function(){
			tableGroup.search($(this).val()).draw();
		})
	}
	
	function addBarang(){
		let c = $('input.chkKdBrg:checked').length;
		if(c>0){
			$('input:checkbox.chkKdBrg').each(function () {
				if(this.checked){
					let val = $(this).val().split(' | ');
					let numRows = tableAdd.rows().count();
					let bExist = false;
					
					$('.kdBrgAdd').each(function(i, obj) {
						if(val[0] == $(this).val()){
							bExist = true;
						}
					});
					
					if(bExist == true){
						alert('Kode barang "'+val[0]+'" sudah ada dalam list!');
					}
					else{
						tableAdd.row.add([(numRows+1), '<input type="hidden" name="kd_brg[]" class="kdBrgAdd" value="'+val[0]+'">'+val[0], val[1], '<button type="button" class="btn btn-dark-sm btn-danger-dark deleteBarang"><i class="glyphicon glyphicon-remove"></i></button>']).draw();
					}
				}
			});
		}
	}
	
	function canSave(){
		let numRows = tableAdd.rows().count();
		if(numRows>0){
			$('.btnSubmit').prop('disabled', false);
		}
		else{
			$('.btnSubmit').prop('disabled', true);
		}
	}
	
	function canAddBarang(){
		let group = $('#group_tipe_barang').val();
		let merk = $('#merk').val();
		if(group!='' && merk!=''){
			$('#btnBrowseBarang').prop('disabled', false);
		}
		else{
			$('#btnBrowseBarang').prop('disabled', true);
		}
	}

</script>

