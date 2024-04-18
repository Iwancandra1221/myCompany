<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>  
	.notVisible{ display: none; }
	/*.glyphicon { font-size:20px;margin-left:5px;margin-right:5px; }*/
	.merah { color:#c91006; }
	.hijau { color:#0ead05;} 
</style>

<div class="container">
    <div class="page-title">KPI CATEGORY MEMBER INCLUDE</div>
	<?php if($_SESSION["can_create"] == 1 || $_SESSION["can_update"] == 1) { ?>  
	<?php echo form_open('KpiCategoryMemberIncludeV2/save', array('id' => 'form_edit')) ?>  
	<div class="row">
        <div class="col-12">  
				<input id="btnTambah" type="button" class="btn btn-dark" value="Tambah" onclick="addnew()">
				<input id="btnSimpan" type="submit" class="btn btn-dark" value="Simpan" > 
				<input id="btnClear" type="button" class="btn btn-dark" value="Clear" onclick="btnClear_Onclick()">
				<input id="btnBatal" type="button" class="btn btn-dark" value="Batal" onclick="btnBatal_Onclick()">
		</div>
        <div class="col-12"> 
			<div style="border:1px solid #000; border-radius:3px">
				<form id="f-filter" action="" method="POST" class="form-horizontal">
					<div class="form-group">
						<div class="col-2"> 
						<label >KPI Category / Division</label></div>
						<div class="col-4"> 
							<input type="hidden" name="MemberId" id="MemberId" value="">
							<input type="hidden" name="KPICategoryID" id="KPICategoryID" value="">
							<input type="hidden" name="DivisionID" id="DivisionID" value="">
							<input type="text" class="form-control" name="KPICategoryName" readonly id="KPICategoryName" placeholder="" required>  
						</div>
						<div class="col-5"> 
							<input type="text" class="form-control" name="DivisionName" readonly id="DivisionName" placeholder="" required>
						</div>
						<div class="col-1">  
							<!--input id="btnSearch" type="button" class="btn btn-dark" value="Search" data-target='#load_user' href='#' data-href='#' data-toggle='modal'--> 
							<input id="btnSearchKPICategory" type="button" class="btn btn-dark" value="Search" onclick="javascript:load_kpicategory_division()"> 
						</div> 
					</div>
					<div class="form-group">
						<div class="col-2">
							<label>User ID</label>
						</div>
						<div class="col-2"> 
							<input type="text" class="form-control" name="USERID" readonly id="txtUserID" placeholder="" required>  
						</div>
						<div class="col-7"> 
							<input type="text" class="form-control" name="NAME" readonly id="txtUserName" placeholder="" required>
						</div>
						<div class="col-1">   
							<input id="btnSearch" type="button" class="btn btn-dark" value="Search" onclick="javascript:load_user()"> 
						</div> 
					</div> 
					<div class="form-group">
						<div class="col-2">
							<label >Tanggal Awal</label>
						</div>
						<div class="col-2"> 
							<input type="text" class="form-control" id="txtTglAwal" placeholder="mm/dd/yyyy" name="StartDate" autocomplete="off" required>
						</div>
						<div class="col-2"> 
							<input type="checkbox" id="chkIsActive" name="IsActive"> Aktif
						</div>
					</div> 
					<div class="row">
					</div>
					<div class="form-group">
						<div class="col-2">
							<label></label>
						</div>
						<div class="col-10">
							<label id="edit_createdBy"> </label>  
						</div> 
					</div>
					<div class="row">
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php echo form_close(); ?>   
	<?php } ?>   
	
    <div class="row">
		<div class="col-12">
			<table id="TblUser" class="table table-striped table-bordered" cellspacing="0" width="100%">
				<thead>
				<tr>
					<th scope="col">KPI Category ID</th>
					<th scope="col">KPI Category Name</th>
					<th scope="col">Division ID</th>
					<th scope="col">Division Name</th>
					<th scope="col">USERID</th>
					<th scope="col">Name</th> 
					<th scope="col">TGL AWAL</th>
					<?php if($_SESSION["can_update"] == 1 || $_SESSION["can_delete"] == 1) { ?>
					<th class="no-sort" scope="col" width="80px">AKSI</th>  
					<?php } ?>
				</tr>
				</thead> 
				<tbody>
				</tbody>
			</table>   
		</div>
	</div>
</div>

<div class="modal modal-tall fade"  id="load_user" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-body">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true" style="background:red; padding:0px 10px">&times;</span>
				</button>
				<br>
				<div class="row">
				<h4 class="modal-title" style="text-align: center;"><strong>SEARCH USER</strong></h4>
				</div>
					<table id="TblUserZen" class="table table-stripped table-bordered"> 
					<thead>
						<tr>
							<th scope="col" style="width:10%">USERID</th>
							<th scope="col" style="width:30%">NAME</th>
							<th scope="col" style="width:15%">DIVISION ID</th>
							<th scope="col" style="width:25%">DIVISION NAME</th>
							<th scope="col" style="width:15%">EMP LEVEL</th>
							<th scope="col" style="width:5%" class="no-sort">AKSI</th> 
						</tr>
					</thead>  
						<tbody>
						</tbody>
					</table>
				<!--/div-->       
			</div>
		</div>
	</div> 
</div>


<div class="modal modal-tall fade"  id="load_kpicategory_division" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-body">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true" style="background:red; padding:0px 10px">&times;</span>
				</button>
				<br>
				<div class="row">
				<h4 class="modal-title" style="text-align: center;"><strong>SEARCH KPI CATEGORY</strong></h4>
				</div>
					<table id="tbl_kpicategorydivision" class="table table-stripped table-bordered"> 
					<thead>
						<tr>
							<th scope="col" style="width:10%">KPICategory ID</th>
							<th scope="col" style="width:30%">KPICategory Name</th>
							<th scope="col" style="width:15%">Division ID</th>
							<th scope="col" style="width:25%">Division Name</th>
							<th scope="col" style="width:5%" class="no-sort">AKSI</th> 
						</tr>
					</thead>  
						<tbody>
						</tbody>
					</table>
				<!--/div-->       
			</div>
		</div>
	</div> 
</div>

<script>
	
	let TblUser;
	TblUser = $('#TblUser').DataTable({
		"pageLength"    : 10,
		"searching"     : true,
		"lengthChange"  : false,
		"columnDefs": [
		{ targets: 'no-sort', orderable: false },
		{ targets: 'col-hide', visible: false }
		],
		"order": [3, 'asc'],
		"autoWidth": false,
		"processing": true,
		"serverSide": true,
		"ajax": '<?php echo base_url('KpiCategoryMemberIncludeV2/datatable_memberinclude') ?>',
		"initComplete": function() {
			$('#TblUser_filter input').unbind();
			$('#TblUser_filter input').bind('keyup', function(e) {
				if(e.keyCode == 13) {
					TblUser.search(this.value).draw();   
				}
			}); 
		},
	});
	
	let TblUserZen;
	TblUserZen = $('#TblUserZen').DataTable({
		"pageLength"    : 10,
		"searching"     : true,
		"lengthChange"  : false,
		"columnDefs": [
		{ targets: 'no-sort', orderable: false },
		{ targets: 'col-hide', visible: false }
		],
		"order": [3, 'asc'],
		"autoWidth": false,
		"processing": true,
		"serverSide": true,
		"ajax": '<?php echo base_url('KpiCategoryMemberIncludeV2/GetEmployees') ?>',
		"initComplete": function() {
			$('#TblUserZen_filter input').unbind();
			$('#TblUserZen_filter input').bind('keyup', function(e) {
				if(e.keyCode == 13) {
					TblUserZen.search(this.value).draw();
				}
			}); 
		},
	});
	
	let tbl_kpicategorydivision;
	tbl_kpicategorydivision = $('#tbl_kpicategorydivision').DataTable({
		"pageLength"    : 10,
		"searching"     : true,
		"lengthChange"  : false,
		"columnDefs": [
		{ targets: 'no-sort', orderable: false },
		{ targets: 'col-hide', visible: false }
		],
		"order": [3, 'asc'],
		"autoWidth": false,
		"processing": true,
		"serverSide": true,
		"ajax": '<?php echo base_url('KpiCategoryMemberIncludeV2/datatable_kpicategorydivision') ?>',
		"initComplete": function() {
			$('#tbl_kpicategorydivision_filter input').unbind();
			$('#tbl_kpicategorydivision_filter input').bind('keyup', function(e) {
				if(e.keyCode == 13) {
					tbl_kpicategorydivision.search(this.value).draw();   
				}
			}); 
		},
	});
	
	function btnHapus_Onclick(MemberIncludeID){
		if (confirm("Apakah anda yakin ingin menghapus member ini?") == true) { 
			var data ='&MemberIncludeID='+MemberIncludeID;  
			$.ajax({
				type      : 'POST', 
				url       : '<?php echo site_url('KpiCategoryMemberIncludeV2/delete') ?>',
				data      : data,
				success   : function(data) { 
					var data = data.trim();
					if(data=='sukses'){ 
						TblUser.ajax.reload();
					}
					else{
						alert('Data gagal dihapus!');
					}
					return false;
				}
				
			})
		}
	}
	
	function SelectUser(userid,username)
	{
		document.getElementById('txtUserID').value = userid;
		document.getElementById('txtUserName').value = username;
		$('#load_user').modal('hide');
	}
	
	function PilihKPICategoryDivision(kpicategoryid,kpicategoryname,divisionid,divisionname)
	{
		document.getElementById('KPICategoryID').value = kpicategoryid;
		document.getElementById('KPICategoryName').value = kpicategoryname;
		document.getElementById('DivisionID').value = divisionid;
		document.getElementById('DivisionName').value = divisionname;
		$('#load_kpicategory_division').modal('hide');
	}
	
	
	function btnBatal_Onclick()
	{  
		$("#btnTambah").attr('disabled', false);
		$("#btnSimpan").attr('disabled', true); 
		$("#btnClear").attr('disabled', true);
		$("#btnBatal").attr('disabled', true);
		
		$('#edit_createdBy').text("");  
		$("#btnSearch").attr('disabled', true);
		$("#btnSearchKPICategory").attr('disabled', true);
		$("#txtTglAwal").attr('disabled', true); 
		$("#chkIsActive").attr('disabled', true);
		
		document.getElementById('KPICategoryID').value = "";
		document.getElementById('KPICategoryName').value = "";
		document.getElementById('DivisionID').value = "";
		document.getElementById('DivisionName').value = "";
		
		document.getElementById('txtUserID').value = "";
		document.getElementById('txtUserName').value = "";
		document.getElementById('txtTglAwal').value = "";
		document.getElementById('chkIsActive').checked = false;
	}
	
	function btnClear_Onclick()
	{   
		$("#btnTambah").attr('disabled', true);
		$("#btnSimpan").attr('disabled', false); 
		$("#btnClear").attr('disabled', false);
		$("#btnBatal").attr('disabled', false);
		
		$('#edit_createdBy').text("");
		document.getElementById('KPICategoryID').value = "";
		document.getElementById('KPICategoryName').value = "";
		document.getElementById('DivisionID').value = "";
		document.getElementById('DivisionName').value = "";
		document.getElementById('txtUserID').value = "";
		document.getElementById('txtUserName').value = "";
		document.getElementById('txtTglAwal').value = "";
		document.getElementById('chkIsActive').checked = false;
	}
	
	function addnew(){    
		document.getElementById('MemberId').value = "";
		$("#btnTambah").attr('disabled', true);
		$("#btnSimpan").attr('disabled', false); 
		$("#btnClear").attr('disabled', false);
		$("#btnBatal").attr('disabled', false);
		
		$("#btnSearch").attr('disabled', false);
		$("#btnSearchKPICategory").attr('disabled', false);
		$("#txtTglAwal").attr('disabled', false);
		$('#txtTglAwal').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		});
		$("#chkIsActive").attr('disabled', false);
	}
	
	var loadData = function(kpicategoryid,kpicategoryname,divisionid,divisionname,userID,userName,tglAwal,isActive,createdBy,createdDate,MemberId){   
		$("#btnTambah").attr('disabled', true);
		$("#btnSimpan").attr('disabled', false); 
		$("#btnClear").attr('disabled', false);
		$("#btnBatal").attr('disabled', false);
		
		$("#btnSearch").attr('disabled', false);
		$("#btnSearchKPICategory").attr('disabled', false);
		$("#txtTglAwal").attr('disabled', false);
		$("#chkIsActive").attr('disabled', false);
		$('#txtTglAwal').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		});
		
		document.getElementById('MemberId').value = MemberId;
		
		document.getElementById('KPICategoryID').value = kpicategoryid;
		document.getElementById('KPICategoryName').value = kpicategoryname;
		document.getElementById('DivisionID').value = divisionid;
		document.getElementById('DivisionName').value = divisionname;
		
		document.getElementById('txtUserID').value = userID;
		document.getElementById('txtUserName').value = userName;
		document.getElementById('txtTglAwal').value = date("m/d/Y",strtotime(tglAwal));
		document.getElementById('chkIsActive').checked = (isActive=='1') ? 1 : 0;
		$('#edit_createdBy').text("Created : "+createdBy+' On '+ createdDate); 
	} 
			
	$(function() {
		$("#form_edit").on('submit', function(e) {
			e.preventDefault();
			
			var form_edit = $(this);
			$('.loading').show();
			$.ajax({
				url: form_edit.attr('action'),
				type: 'post',
				data: form_edit.serialize(),
				dataType: 'json',
				success: function(response){
					// console.log(response);
					$('.loading').hide();
					if(response.status == 'sukses') {  
						btnBatal_Onclick();  
						TblUser.ajax.reload();
						alert('SUCCESS. Data berhasil disimpan!');
					} 
					else
					{
						alert('FAILED. Error: '+response.message);
					}
				}
			});
		});
	});
	
	function load_user(){
		$('#load_user').modal('show');
	}
	function load_kpicategory_division(){
		$('#load_kpicategory_division').modal('show');
	}
	
	$(document).ready(function(){
		btnBatal_Onclick();
	});
	
</script>  		