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
	<?php echo form_open('KpiCategoryMemberInclude/save', array('id' => 'form_edit')) ?>  
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
						<label >Division</label></div>
						<div class="col-10">
							<input type="hidden" name="MemberId" id="MemberId" value="">
							<select name="cboDivision" class="form-control" id="cboDivision" required> 
								<?php 
									for($i=0;$i<count($listdivision);$i++) { 
										echo '<option value="'.$listdivision[$i]["KPICategory"].';;'.$listdivision[$i]["KPICategoryName"].'">'.$listdivision[$i]["KPICategoryName"].'</option>'; 
									}
								?>
							</select>
						</div> 
					</div>
					<div class="form-group">
						<div class="col-2">
							<label>User ID</label>
						</div>
						<div class="col-2"> 
							<input type="text" class="form-control" name="txtUserID" readonly id="txtUserID" placeholder="" required>  
						</div>
						<div class="col-7"> 
							<input type="text" class="form-control" name="txtUserName" readonly id="txtUserName" placeholder="" required>
						</div>
						<div class="col-1">  
							<!--input id="btnSearch" type="button" class="btn btn-dark" value="Search" data-target='#load_user' href='#' data-href='#' data-toggle='modal'--> 
							<input id="btnSearch" type="button" class="btn btn-dark" value="Search" onclick="javascript:load_user()"> 
						</div> 
					</div> 
					<div class="form-group">
						<div class="col-2">
							<label >Tanggal Awal</label>
						</div>
						<div class="col-2"> 
							<input type="text" class="form-control" id="txtTglAwal" placeholder="mm/dd/yyyy" name="txtTglAwal" autocomplete="off" required>
						</div>
						<div class="col-2"> 
							<label >Tanggal Akhir</label>
						</div>
						<div class="col-2"> 
							<input type="text" class="form-control" id="txtTglAkhir"  placeholder="mm/dd/yyyy" name="txtTglAkhir" autocomplete="off" >
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
					<th scope="col">DIVISION ID</th>
					<th scope="col">DIVISION NAME</th>
					<th scope="col">USERID</th>
					<th scope="col">NAME</th> 
					<th scope="col">TGL AWAL</th>
					<th scope="col">TGL AKHIR</th>
					<?php if($_SESSION["can_update"] == 1 || $_SESSION["can_delete"] == 1) { ?>
					<th class='no-sort'scope="col">AKSI</th>  
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
				<div class="row">
				<h4 class="modal-title" style="text-align: center;"><strong>SEARCH USER</strong></h4>
					<div class="col-12">
						<input type="button" class="btn btn-dark" onclick="loadUserZen()" value="REFRESH LIST EMPLOYEES">
					</div>
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

<script>
	
	let TblUser;
	TblUser = $('#TblUser').DataTable({
		"pageLength"    : 10,
		"searching"     : true,
		"autoWidth": false,
		"columnDefs": [
		{ targets: 'no-sort', orderable: false },
		{ targets: 'col-hide', visible: false }
		],
		// "dom": '<"top">rt<"bottom"ip><"clear">',
		"order": [[1, 'asc']],
	});
	
	let TblUserZen;
	TblUserZen = $('#TblUserZen').DataTable({
		"pageLength"    : 10,
		"searching"     : true,
		"autoWidth": false,
		"columnDefs": [
		{ targets: 'no-sort', orderable: false },
		{ targets: 'col-hide', visible: false }
		],
		// "dom": '<"top">rt<"bottom"ip><"clear">',
		"order": [[1, 'asc']],
	});
	
	function reload(){    
		$(".loading").show();  
		$("#TblUser tbody tr").remove(); 
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo site_url('KpiCategoryMemberInclude/ListMemberInclude') ?>', 
			dataType: 'json',
			success: function (datas){  
				// console.log(JSON.stringify(datas));
				if(datas.length>0){    
					TblUser = $('#TblUser').DataTable(); 
					TblUser.clear().draw();
					for (var i = 0; i < datas.length; i++) {
						if (datas[i].EndDate==null)
						{
							var EndDate = "<td class='hideOnMobile'></td>";
						}
						else
						{
							var EndDate = "<td class='hideOnMobile'>"+date("d-M-Y",strtotime(datas[i].EndDate))+"</td>";
						}
						
						var tdaction = "<td class='hideOnMobile'>";
						
						<?php if($_SESSION["can_update"] == 1){ ?>
						tdaction =  tdaction  + "<button onclick='loadData("+'"'+datas[i]["KPICategoryID"]+";;"+datas[i]["KPICategoryName"]+'"'+',"'+datas[i]["KPICategoryName"]+'"'+',"'+datas[i]["USERID"]+'"'+',"'+datas[i]["Name"]+'"'+',"'+datas[i]["StartDate"]+'"'+',"'+datas[i]["EndDate"]+'"'+',"'+datas[i]["CreatedBy"]+" "+datas[i]["CreatedDate"]+'"'+',"'+datas[i]["MemberIncludeID"]+'"'+")' class='btn btn-sm btn-dark'> <i class='glyphicon glyphicon-edit'></i></button> ";
						<?php } ?>
						<?php if($_SESSION["can_delete"] == 1) { ?>
						tdaction =  tdaction  + '<button onclick="btnHapus_Onclick('+"'"+datas[i]["MemberIncludeID"]+"'"+')" class="btn btn-sm btn-danger-dark"><i class="glyphicon glyphicon-trash"></i></button>';
						<?php } ?>
						
						TblUser.row.add([
						datas[i].KPICategoryID,
						datas[i].KPICategoryName,
						datas[i].USERID,
						datas[i].Name,
						date("d-M-Y",strtotime(datas[i].StartDate)),
						EndDate,
						
						<?php if($_SESSION["can_update"] == 1 || $_SESSION["can_delete"] == 1){ ?>
						tdaction
						<?php } ?>
						]);
					} 
					$(".loading").hide();  
					TblUser.draw();    
				} 
			}
		});  
	}
	
	function btnHapus_Onclick(MemberIncludeID){
		if (confirm("Apakah anda yakin ingin menghapus member ini?") == true) { 
			var data ='&MemberIncludeID='+MemberIncludeID;  
			$.ajax({
				type      : 'POST', 
				url       : '<?php echo site_url('KpiCategoryMemberInclude/delete') ?>',
				data      : data,
				success   : function(data) { 
					var data = data.trim();
					if(data=='sukses'){ 
						reload();
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
	}
	
	function loadUserZen()
	{       
		$("#TblUserZen tbody tr").remove();
		$(".loading").show();
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo site_url('KpiCategoryMemberInclude/GetEmployees') ?>', 
			dataType: 'json',
			success: function (datas){   
				if(datas.length>0){
					TblUserZen = $('#TblUserZen').DataTable(); 
					TblUserZen.clear().draw();
					for (var i = 0; i < datas.length; i++) {
						TblUserZen.row.add([
						datas[i].USERID,
						datas[i].NAME,
						datas[i].DIVISIONID,
						datas[i].DIVISIONNAME,
						datas[i].EMPLEVEL,
						"<button data-dismiss='modal' type='button' onclick='SelectUser("+ '"' + datas[i].USERID + '","' +datas[i].NAME+'"'+")' class='btn btn-sm btn-dark'> <i class='glyphicon glyphicon-ok'></i></button>"
						]);
					} 
					$(".loading").hide();  
					TblUserZen.draw();  
				} 
			}
		});  
		
	}
	
	function btnBatal_Onclick()
	{  
		$("#btnTambah").attr('disabled', false);
		$("#btnSimpan").attr('disabled', true); 
		$("#btnClear").attr('disabled', true);
		$("#btnBatal").attr('disabled', true);
		
		$('#edit_createdBy').text("");  
		$("#cboDivision").attr('disabled', true);
		$("#btnSearch").attr('disabled', true);
		$("#txtTglAkhir").attr('disabled', true);
		$("#txtTglAwal").attr('disabled', true);  
		document.getElementById('txtUserID').value = "";
		document.getElementById('txtUserName').value = "";
		document.getElementById('txtTglAwal').value = "";
		document.getElementById('txtTglAkhir').value = "";
	}
	
	function btnClear_Onclick()
	{   
		$("#btnTambah").attr('disabled', true);
		$("#btnSimpan").attr('disabled', false); 
		$("#btnClear").attr('disabled', false);
		$("#btnBatal").attr('disabled', false);
		
		$('#edit_createdBy').text("");
		document.getElementById('txtUserID').value = "";
		document.getElementById('txtUserName').value = "";
		document.getElementById('txtTglAwal').value = "";
		document.getElementById('txtTglAkhir').value = "";
	}
	
	function addnew(){    
		document.getElementById('MemberId').value = "";
		$("#btnTambah").attr('disabled', true);
		$("#btnSimpan").attr('disabled', false); 
		$("#btnClear").attr('disabled', false);
		$("#btnBatal").attr('disabled', false);
		
		$("#cboDivision").attr('disabled', false);
		$("#btnSearch").attr('disabled', false);
		$("#txtTglAkhir").attr('disabled', false);
		$("#txtTglAwal").attr('disabled', false);
		$('#txtTglAwal').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
			}).on('changeDate', function(e) { //changeDate
			var StartDt = $('#txtTglAwal').datepicker('getDate');
			$('#txtTglAkhir').datepicker("setStartDate", StartDt);
		});
		
		$('#txtTglAkhir').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
			}).on('changeDate', function(e) { //changeDate
			var EndDt = $('#txtTglAkhir').datepicker('getDate');
			$('#txtTglAwal').datepicker("setEndDate", EndDt);
		});
	}
	
	var loadData = function(division,divisionname,userID,userName,tglAwal,tglAkhir,createdBy,MemberId){   
		$("#btnTambah").attr('disabled', true);
		$("#btnSimpan").attr('disabled', false); 
		$("#btnClear").attr('disabled', false);
		$("#btnBatal").attr('disabled', false);
		
		$("#cboDivision").attr('disabled', false);
		$("#btnSearch").attr('disabled', false);
		$("#txtTglAkhir").attr('disabled', false);
		$("#txtTglAwal").attr('disabled', false);
		$('#txtTglAwal').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
			}).on('changeDate', function(e) { //changeDate
			var StartDt = $('#txtTglAwal').datepicker('getDate');
			$('#txtTglAkhir').datepicker("setStartDate", StartDt);
		});
		$('#txtTglAkhir').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
			}).on('changeDate', function(e) { //changeDate
			var EndDt = $('#txtTglAkhir').datepicker('getDate');
			$('#txtTglAwal').datepicker("setEndDate", EndDt);
		});
		
		
		document.getElementById('cboDivision').value = division; 
		document.getElementById('MemberId').value = MemberId;
		document.getElementById('txtUserID').value = userID;
		document.getElementById('txtUserName').value = userName;
		document.getElementById('txtTglAwal').value = date("m/d/Y",strtotime(tglAwal));
		
		if (tglAkhir=='null')
		{ 
			document.getElementById('txtTglAkhir').value = ""; 
		}
		else
		{
			document.getElementById('txtTglAkhir').value = date("m/d/Y",strtotime(tglAkhir));
		}
		$('#edit_createdBy').text("Created : "+createdBy); 
	} 
	
	$(document).ready(function() {  
		// btnBatal_Onclick();
	});  
	
	$('#search').keyup(function() {
		var searchText = $(this).val();
		table = document.getElementById("TblUserZen");
		tr = table.getElementsByTagName("tr");
		for (i = 0; i < tr.length; i++) {
			tdUserID = tr[i].getElementsByTagName("td")[0];
			userId = tdUserID && (tdUserID.textContent || tdUserID.innerText);
			tdUserName = tr[i].getElementsByTagName("td")[1];
			userName = tdUserName && (tdUserName.textContent || tdUserName.innerText); 
			tdDivisionId = tr[i].getElementsByTagName("td")[2];
			divisionId = tdDivisionId && (tdDivisionId.textContent || tdDivisionId.innerText); 
			tdDivisionName = tr[i].getElementsByTagName("td")[3];
			divisionName = tdDivisionName && (tdDivisionName.textContent || tdDivisionName.innerText); 
			tdEmpLevel = tr[i].getElementsByTagName("td")[4];
			empLevel = tdEmpLevel && (tdEmpLevel.textContent || tdEmpLevel.innerText); 
			if (userId.toUpperCase().indexOf(searchText) > -1 ||
			userName.toUpperCase().indexOf(searchText) > -1 ||
			divisionId.toUpperCase().indexOf(searchText) > -1 ||
			divisionName.toUpperCase().indexOf(searchText) > -1 ||
			empLevel.toUpperCase().indexOf(searchText) > -1) {
				tr[i].className = "isVisible";
				} else {
				tr[i].className = "notVisible";
			}      
		}
		
	});
		
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
						reload();
						alert('SUCCESS. Data berhasil disimpan!');
					} 
					else
					{
						alert('FAILED. Error: '+response.message);
					}
					// $("#message").html(response.message);
				}
			});
		});
	});
	
	function load_user(){
		$('#load_user').modal('show');
		loadUserZen();
	}
	
	$(document).ready(function(){
		reload(); 
	});
	
</script>  		