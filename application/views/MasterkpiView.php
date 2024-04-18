<style type="text/css">
	.row {
	line-height:30px;
	vertical-align:middle;
	clear:both;
	}
	.row-label, .row-input {
	float:left;
	}
	.row-label {
	padding-left: 15px;
	width:180px;
	}
	.row-input {
	width:420px;
	}
	td input {
	width:100%;
	}
</style>
<script>
	$(document).ready(function(){
		$('#start_date_add').datepicker({
			format: "dd-M-yyyy",
			autoclose: true,
		});
	});
</script>
<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
	<form method="POST" id="form_edit" action="<?=site_url('Masterkpi/KPICategoryMemberSave')?>">
		<div class="row">
			<div class="col-2">Start Date</div>
			<div class="col-2">
				<select name="start_date" id="start_date" class="form-control" onchange="javascript:load_data()">
					<?php foreach($start_date as $d){ ?>
						<option value="<?php echo date('Y-m-d',strtotime($d['StartDate'])) ?>"><?php echo date('d-M-Y',strtotime($d['StartDate'])) ?></option>
					<?php } ?>
				</select>
				<input type="text" class="form-control" id="start_date_add" placeholder="dd-MMM-yyyy" name="start_date_add" autocomplete="off">
			</div>
			<div class="col-8">
				<div class="" style="float:right;">
					<?php if($_SESSION["can_create"] == 1) { ?>
						<button type="button" id="btn_tambah" class="btn btn-dark" onclick="javascript:tambah()">TAMBAH</button>
					<?php } ?>
					<?php if($_SESSION["can_update"] == 1) { ?>
						<button type="button" id="btn_edit" class="btn btn-dark" onclick="javascript:edit()">EDIT</button>
					<?php } ?>
					<?php if($_SESSION["can_create"] == 1 || $_SESSION["can_update"] == 1) { ?>
						<button type="submit" id="btn_save" class="btn btn-danger-dark">SAVE</button>
						<button type="button" id="btn_clear" class="btn btn-dark" onclick="javascript:batal()">CLEAR</button>
					<?php } ?>
				</div>
			</div>
			<div class="col-12">
				<table id="tb-master-kpi" class="table table-bordered" style="width:100%;">
					<thead>
						<tr>
							<th scope="col" style="width:20%;text-align:center;">Level</th>
							<th scope="col" style="width:30%;text-align:center;">Nama Level</th>
							<th scope="col" style="width:50%;text-align:center;">Kategori KPI</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</form>
</div>
<!-- di load cuman buat ditarik pada saat ambil data GetMasterKpiCategory  -->

<script>
	function load_data(){
		$('.loading').show();
		var start_date = $("#start_date").val();
		$.ajax({
			url: "<?=site_url('Masterkpi/GetMasterKpiCategory')?>",
			type: 'POST',
			cache: false,
			data: {
				start_date: start_date,
			},
			success: function (str) {
				// console.log(str);
				$("#tb-master-kpi tbody").html("");
				var json = JSON.parse(str);
				if(json.code == 1){
					
					var kpi_category = json.kpi_category;
					var master_kpi_category = json.master_kpi_category;
					var isEdit = false;
					
					var html = '';
					for(var i=0;i<master_kpi_category.length;i++){
						
						var htmlDropdown = '<select name="Grup_Level[]" style="width:100%;" class="grup_level" disabled>';
						htmlDropdown += '<option value=""></option>';
						for(var j=0;j<kpi_category.length;j++){
							var checked = '';
							if(kpi_category[j].KPICategory == master_kpi_category[i].Grup_Level){
								checked = 'selected';
								isEdit = true;
							}
							
							htmlDropdown += '<option value="'+kpi_category[j].KPICategory+'" '+checked+'>'+kpi_category[j].KPICategory+'</option>';
						}
						htmlDropdown += '</select>';
						
						var col1 = '<input type="text" name="Level_Salesman[]" value="'+master_kpi_category[i].Level_Salesman+'" readonly>';
						var col2 = '<input type="text" name="Nama_Level[]" value="'+master_kpi_category[i].Nama_Level+'" readonly>';
						var col3 = htmlDropdown;//master_kpi_category[i].Grup_Level;
						html +='<tr>'+
						'<td>'+col1+'</td>'+
						'<td>'+col2+'</td>'+
						'<td>'+col3+'</td>'+
						'</tr>';
					}
					$("#tb-master-kpi tbody").html(html);
				}
				else{
					$("#tb-master-kpi tbody").html('<tr><td colspan="3" align="center">Tidak ada data</td></tr>');
				}
				$('.loading').hide();
			}
		});
	}
	
	function tambah(){
		$('#start_date_add').attr('required',true);
		$('#btn_tambah').attr('disabled',true);
		$('#btn_edit').attr('disabled',true);
		$('#btn_save').attr('disabled',false);
		$('#btn_clear').attr('disabled',false);
		$('.grup_level').attr('disabled',false);
		$('#start_date').hide();
		$('#start_date_add').show();
	}
	
	function edit(){
		$('#start_date_add').attr('required',false);
		$('#btn_tambah').attr('disabled',true);
		$('#btn_edit').attr('disabled',true);
		$('#btn_save').attr('disabled',false);
		$('#btn_clear').attr('disabled',false);
		$('.grup_level').attr('disabled',false);
		$('#start_date').show();
		$('#start_date_add').hide();
	}
	
	function batal(){
		$('#btn_tambah').attr('disabled',false);
		$('#btn_edit').attr('disabled',false);
		$('#btn_save').attr('disabled',true)
		$('#btn_clear').attr('disabled',true)
		$('.grup_level').attr('disabled',true);
		$('#start_date').show();
		$('#start_date_add').hide();
		$('#start_date_add').val('');
	}
	
	$(document).ready(function(){
		$("#form_edit").on('submit', function(e) {
			e.preventDefault();
			var form_edit = $(this);
			$('.loading').show();
			$.ajax({
				url: form_edit.attr('action'),
				type: 'POST',
				data: form_edit.serialize(),
				// dataType: 'json',
				success: function(response){
					$('.loading').hide();
					if(response == 'success') { 
						alert('SUCCESS. Data berhasil disimpan!');
						location.reload();
					}
					else
					{
						alert('FAILED. Error: '+response);
					}
				}
			});
		});
		
		load_data();
		batal();
	});
</script>
</div> <!-- /container -->