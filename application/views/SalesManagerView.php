<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>
	.btnEdit, .btnDelete {
		cursor: pointer;
	}
	#formTitle {
		font-weight: bold;
		font-size:20pt;
	}
</style>
<script>
	// var ListEmployee = <?php //echo(json_encode($ListEmployee));?>;
	// var employees = <?php //echo(json_encode($employees));?>;
	var mode='';
	var tblBrandManager;
	var tblUser;
	$(document).ready(function(){
		$("#divSalesManager").hide();
	    // $("#TxtEmployee").autocomplete({
	    //   source: employees
	    // });			

	    // $('#TxtEmployee').on('change', function() {
	    //   var Emp = $("#TxtEmployee").val();
	    //   var sArray = Emp.split(" - ");
	    //   $("#EmployeeName").val(sArray[0]);
	    //   $("#EmployeeID").val(sArray[2]);
	    //   $("#UserID").val(sArray[1]);
	    // });

		// $("#btnAdd").click(function(){
			// $("#FormSalesManager #formTitle").html("TAMBAH SALES MANAGER");
			// $("#FormSalesManager #SaveMode").val("add");
			// $("#FormSalesManager #DivisionID").val("");
			// $("#FormSalesManager #EmployeeID").val("");
			// $("#FormSalesManager #EmailAddress").val("");
			// $("#FormSalesManager #Mobile").val("");
			// $("#FormSalesManager").show();
			// $("#tblBrandManager").hide();
			// $("#btnAdd").hide();
		// });

		// $(".btnEdit").click(function(){
			// $("#FormSalesManager #formTitle").html("EDIT SALES MANAGER");
			// $("#FormSalesManager #SaveMode").val("edit");
			// $("#FormSalesManager #DivisionID").val($(this).attr("divisi"));
			// $("#FormSalesManager #LevelSalesman").val($(this).attr("level"));
			// $("#FormSalesManager #EmployeeID").val($(this).attr("useremail"));
			// $("#FormSalesManager #EmployeeName").val($(this).attr("nama"));
			// $("#FormSalesManager #UserID").val($(this).attr("userid"));
			// $("#FormSalesManager #EmailAddress").val($(this).attr("email"));
			// $("#FormSalesManager #Mobile").val($(this).attr("mobile"));
			// $("#FormSalesManager").show();
			// $("#tblBrandManager").hide();
		// });

		// $(".btnDelete").click(function(){
			// var Divisi = $(this).attr("divisi");
			// var Level = $(this).attr("level");
			// var UserEmail = $(this).attr("userid");
			// var btn = $(this);
			// //alert(Level);

			// $(".loading").show();
			// var csrf_bit = $("input[name=csrf_bit]").val();
			// $.post("<?php echo site_url('SalesManager/Hapus'); ?>", {
				// DivisionID 		: Divisi,
				// LevelSalesman	: Level,
				// UserEmail 		: UserEmail,
				// csrf_bit		: csrf_bit
			// }, function(data){
				// if (data == "sukses") {
					// btn.parent("td").parent("tr").remove();
					// alert("Hapus Berhasil");
				// } else {
					// alert("Hapus Gagal");
				// }
			// },'json',errorAjax);		
			// $(".loading").hide();
		// });

		//$("#EmployeeName").on('click', function(e){
		//	popupWindow('pickemployee');
		//});		

		// $("#btnCancel").click(function(){
			// $("#divSalesManager").hide();
			// $("#listBrandManager").show();
			// $("#btnAdd").show();
		// });
		
		tblBrandManager = $('#tblBrandManager').DataTable({
			"pageLength" : 10,
			"searching" : true,
			"autoWidth": false,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ className:"hideOnMobile", "targets":[0,4,5] }
			],
			"dom": '<"top"f>rt<"bottom"ip>',
			"order": [[3, 'asc'], [2, 'asc'], [1, 'asc']], //order = kode, merk, tipe
			"language": {
				"paginate": {
					"previous": "<",
					"next": ">"
				}
			},
		});
		
		tblBrandManager.on('order.dt search.dt', function () {
			let i = 1;
			tblBrandManager.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
				this.data(i++);
			});
		}).draw();
		
		$("<?php if($_SESSION["can_create"]==1) { ?><button id='btnAdd' class='btn btn-dark' style='margin-right:5px'><i class='glyphicon glyphicon-plus'></i> CREATE NEW</button><?php } ?><button id='btnRefresh' class='btn btn-dark' onclick='load_list()'><i class='glyphicon glyphicon-refresh'></i> REFRESH</button>").insertAfter('.top');
		
		
	
	load_list();
	
	});

    /*function popupWindow(id){
       window.open('DataPicker/PickEmployeeJkt?id=' + encodeURIComponent(id),'popuppage',
      'width=800,toolbar=1,resizable=1,scrollbars=yes,height=600,top=0,left=100');
    }
    
    function updateValue(id, EmployeeID, EmployeeName, Email)
    {
        document.getElementById("EmployeeID").value = EmployeeID;
        document.getElementById("EmployeeName").value = EmployeeName;
        document.getElementById("EmailAddress").value = Email;
    }*/	
	
	$(document).on("click", ".btnDelete" , function() {	
		var salesman_id = $(this).attr("salesman_id");
		var btn = $(this);
		if (confirm('Ingin hapus data?')){
			$(".loading").show();
			$.ajax({
				data      	: { salesman_id:salesman_id },
				url			: '<?php echo site_url('SalesManager/Hapus'); ?>',
				type		: 'POST',
    			dataType  : 'json',
				success   : function(data) {
					// console.log(data);
					$('.loading').hide();
					if (data.result == "sukses") {
						$('.loading').hide();
						tblBrandManager.row(btn.parents('tr')).remove().draw();
						alert("Data Berhasil Dihapus!");
					} else {
						alert(data.error);
					}
				}
			});
		}
	});
	
	$(document).on("click", ".btnEdit" , function() {		
		$("#divSalesManager #formTitle").html("EDIT SALES MANAGER");
		$("#FormSalesManager #SaveMode").val("edit");
		var salesman_id = $(this).attr("salesman_id");
		$("#FormSalesManager #salesman_id").val(salesman_id);
		$("#FormSalesManager #BranchID").val($('#branch_'+salesman_id).text());
		$("#FormSalesManager #DivisionID").val($('#division_'+salesman_id).text());
		$("#FormSalesManager #LevelSalesman").val($('#level_'+salesman_id).text());
		$("#FormSalesManager #TxtEmployee").val("");
		$("#FormSalesManager #EmployeeName").val($('#name_'+salesman_id).text());
		$("#FormSalesManager #EmployeeID").val($('#useremail_'+salesman_id).text());
		$("#FormSalesManager #UserID").val($('#userid_'+salesman_id).text());
		$("#FormSalesManager #EmailAddress").val($('#email_'+salesman_id).text());
		$("#FormSalesManager #Mobile").val($('#mobile_'+salesman_id).val());
		// $("#FormSalesManager #DivisionID").prop("readonly",true);
		$("#btnSubmitNew").hide();
		
		$("#divSalesManager").show();
		$("#listBrandManager").hide();
	});
	
	$(document).on("click", "#btnAdd" , function() {
		$("#divSalesManager #formTitle").html("TAMBAH SALES MANAGER");
		if(addNew==0){
			$("#FormSalesManager #SaveMode").val("add");
			$("#FormSalesManager #BranchID").val("ALL");
			$("#FormSalesManager #DivisionID").val("");
		}
		$("#FormSalesManager #TxtEmployee").val("");
		$("#FormSalesManager #EmployeeName").val("");
		$("#FormSalesManager #EmployeeID").val("");
		$("#FormSalesManager #UserID").val("");
		$("#FormSalesManager #EmailAddress").val("");
		$("#FormSalesManager #Mobile").val("");
		$("#FormSalesManager #DivisionID").prop("readonly",false);
		$("#btnSubmitNew").show();
		$("#divSalesManager").show();
		$("#listBrandManager").hide();
	});
	
</script>

<div class="container" id="divSalesManager">
	<div class="form_title"  style="text-align: center;"> 
			SALES MANAGER 
	</div>
	<br>
	<div class="clearfix">
		<div class="border20 p20">
			<div class="overlay"></div>
			<div class="loadingItem">
				<?php echo form_open('SalesManager', array("id"=>"FormSalesManager")); ?>  
				<form class="form-popup" style="height:650px;">
					<i class="fa fa-times ClosePopUp"></i>
					<div class="popupform_title"></div>
					<div>
						<input type="hidden" id="SaveMode" name="SaveMode" readonly>
						<input type="hidden" id="salesman_id" name="salesman_id" readonly>
					</div>
					<div class="row">
						<div class="col-3 col-m-5">Cabang</div>
						<div class="col-9 col-m-7">
							<select id="BranchID" name="BranchID" class="form-control form-control-dark">
								<option value="ALL">ALL</option>
								<?php foreach($branches as $b) {
									echo("<option value='".$b->BranchID."'>".$b->BranchName."</option>");
								} ?>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-3 col-m-5">Divisi</div>
						<div class="col-9 col-m-7"><?php  
							$attr = array 
							(
								'placeholder' => 'Divisi',
								'id' => 'DivisionID',
								'class' => 'form-control form-control-dark',
								'maxlength' => '50'
							);
							echo BuildInput('text','DivisionID',$attr);
						?></div>
					</div>
					<div class="row">
						<div class="col-3 col-m-5">Jabatan</div>
						<div class="col-9 col-m-7">
							<select id="LevelSalesman" name="LevelSalesman" class="form-control form-control-dark">
								<?php foreach($jabatan as $j) {
									echo("<option value='".$j->ConfigValue."'>".$j->ConfigValue."</option>");
								} ?>
							</select>
						</div>
					</div>			
					<div class="row">
						<div class="col-3 col-m-5">Karyawan</div>
						<div class="col-9 col-m-7">
							<!--input type="text" class="form-control form-control-dark mb10" name="TxtEmployee" id="TxtEmployee" placeholder="Ketik Nama Karyawan" readonly onclick="openpopup()"-->
						
							<div class="input-group mb10">
								<input type="text" class="form-control form-control-dark" name="EmployeeName" id="EmployeeName" placeholder="Nama Karyawan" required readonly>
								<span class="input-group-btn">
										<button type="button" class="btn btn-dark" onclick="openpopup()">BROWSE</button>
								</span>
							</div>
							
							
							<input type="text" class="form-control form-control-dark mb10" name="EmployeeID" id="EmployeeID" placeholder="UserEmail Karyawan" style="width:50%;" required readonly>
							<input type="text" class="form-control form-control-dark" name="UserID" id="UserID" placeholder="ID Karyawan" style="width:20%;" required readonly>
						</div>
					</div>						
					<div class="row">
						<div class="col-3 col-m-5">Alamat Email</div>
						<div class="col-9 col-m-7"><?php  
							$attr = array 
							(
								'placeholder' => 'Alamat Email',
								'id' => 'EmailAddress',
								'class' => 'form-control form-control-dark',
								'style' => 'width:50%!important;'
							);
							echo BuildInput('text','EmailAddress',$attr);
						?></div>
					</div>			
					<div class="row">
						<div class="col-3 col-m-5">No Whatsapp</div>
						<div class="col-9 col-m-7"><?php  
							$attr = array 
							(
								'placeholder' => 'No Whatsapp [Diawali 62]',
								'id' => 'Mobile',
								'class' => 'form-control form-control-dark',
								'style' => 'width:50%!important;'
							);
							echo BuildInput('text','Mobile',$attr);
						?></div>
					</div>			
					<div class="row">
						<div class="col-3 col-m-5"></div>
						<div class="col-9 col-m-7">
							<button type="submit" id="btnSubmit" class="btn btn-dark" name="btnSubmit" onclick="AddNew(0)">SAVE & CLOSE</button>
							<button type="submit" id="btnSubmitNew" class="btn btn-dark" name="btnSubmitNew" onclick="AddNew(1)">SAVE & ADD NEW</button>
							<button type="button" id="btnCancel" class="btn btn-dark" name="btnCancel" onclick="javascript:Batal(0)">CLOSE</button>
						</div>
					</div>
				</form>
				<?php echo form_close(); ?>
			</div>
		</div>
	</div>
</div>

<div class="container" id="listBrandManager">
	<div class="title">SALES MANAGER</div>
	<br>
	<table id="tblBrandManager" class="table table-striped table-bordered" cellspacing="0">
		<thead>
			<tr>
				<th width="2%" class='hideOnMobile no-sort'>No</th>
				<th width="10%">Division</th>
				<th width="*">Employee</th>
				<th width="10%">Branch</th>
				<th width="20%" class='hideOnMobile'>Email Address</th>
				<th width="10%" class='hideOnMobile'>Level</th>
		        <?php if($_SESSION["can_update"] == 1 || $_SESSION["can_delete"] == 1) echo "<th width='9%' class='no-sort'></th>"; ?>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>



  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-lg">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Employee</h4>
        </div>
        <div class="modal-body">
          
        	<table class="table table-bordered" id="userlist" summary="table">
			
				<thead>
				<tr>
					<th width="*">USERID</th>
					<th width="*">NAMA</th>
					<th width="*">EMAIL</th>
					<th width="20px">PILIH</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		
		
        	</table>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      
    </div>
  </div>

<script type="text/javascript">
	var addNew = 0;
	$(document).ready(function() {
		$("#FormSalesManager").submit(function() {		
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
				success   : function(msg) {
					// console.log(msg);
					$('.loading').hide();
					if(msg.result=='sukses'){
						alert('Data berhasil disimpan!');
						if($('#SaveMode').val()=='edit'){
							var salesman_id = $('#salesman_id').val();
							$('#userid_'+salesman_id).html($('#UserID').val());
							$('#division_'+salesman_id).html($('#DivisionID').val());
							$('#name_'+salesman_id).html($('#EmployeeName').val());
							$('#useremail_'+salesman_id).html($('#EmployeeID').val());
							$('#email_'+salesman_id).html($('#EmailAddress').val());
							$('#mobile_'+salesman_id).val($('#Mobile').val());
							$('#branch_'+salesman_id).html($('#BranchID').val());
							$('#level_'+salesman_id).html($('#LevelSalesman').val());
						}
						else{
							var salesman_id = msg.salesman_id;
							var d = [];
							d[0] = '';
							d[1] = '<span id="division_'+salesman_id+'">'+$('#DivisionID').val()+'</span>';
							d[2] = '<span id="name_'+salesman_id+'">'+$('#EmployeeName').val()+'</span><br>'+
									'<span id="useremail_'+salesman_id+'">'+$('#EmployeeID').val()+'</span><br>'+
									'UserID: <span id="userid_'+salesman_id+'">'+$('#UserID').val()+'</span>'+
									'<input type="hidden" id="mobile_'+salesman_id+'" value="'+$('#Mobile').val()+'">';
							d[3] = '<span id="branch_'+salesman_id+'">'+$('#BranchID').val()+'</span>';
							d[4] = '<span id="email_'+salesman_id+'">'+$('#EmailAddress').val()+'</span>';
							d[5] = '<span id="level_'+salesman_id+'">'+$('#LevelSalesman').val()+'</span>';

							var aksi = '';
							<?php if($_SESSION["can_update"] == 1) { ?>
							aksi += '<button class="btn btn-sm btn-default btnEdit" salesman_id="'+salesman_id+'"><i class="glyphicon glyphicon-pencil"></i></button> ';
							<?php } ?>

							<?php if ($_SESSION["can_delete"]==1) { ?>
							aksi += '<button class="btn btn-sm btn-default btnDelete" salesman_id="'+salesman_id+'"><i class="glyphicon glyphicon-trash"></button>';
							<?php }  ?>
							d[6] = aksi;
						
							tblBrandManager.row.add(d).draw();
						}
					}else{
						alert(msg.error);
					}
					Batal(addNew);
				}
			});
			event.preventDefault(); //Prevent the default submit
		});
	});
	
	function load_list(){
		tblBrandManager.clear().draw();
		$.ajax({
			url: '<?php echo site_url('SalesManager/GetList') ?>',
			dataType: 'json',
			success: function(data) {
			// console.log(data);	
				var html = '';
				for(i=0;i<data.length;i++){
					var d = [];
					d[0] = '';
					d[1] = '<span id="division_'+data[i].salesman_id+'">'+data[i].division+'</span>';
					d[2] = '<span id="name_'+data[i].salesman_id+'">'+data[i].employee_name+'</span><br>'+
							'<span id="useremail_'+data[i].salesman_id+'">'+data[i].useremail+'</span><br>'+
							'UserID: <span id="userid_'+data[i].salesman_id+'">'+data[i].userid+'</span>'+
							'<input type="hidden" id="mobile_'+data[i].salesman_id+'" value="'+data[i].mobile+'">';
					d[3] = '<span id="branch_'+data[i].salesman_id+'">'+data[i].branch_id+'</span>';
					d[4] = '<span id="email_'+data[i].salesman_id+'">'+data[i].email_address+'</span>';
					d[5] = '<span id="level_'+data[i].salesman_id+'">'+data[i].level_slsman+'</span>';

					var aksi = '';
					<?php if($_SESSION["can_update"] == 1) { ?>
					aksi += '<button class="btn btn-sm btn-default btnEdit" salesman_id="'+data[i].salesman_id+'"><i class="glyphicon glyphicon-pencil"></i></button> ';
					<?php } ?>

					<?php if ($_SESSION["can_delete"]==1) { ?>
					aksi += '<button class="btn btn-sm btn-default btnDelete" salesman_id="'+data[i].salesman_id+'"><i class="glyphicon glyphicon-trash"></button>';
					<?php }  ?>
					d[6] = aksi;
				
					tblBrandManager.row.add(d);
				}
				tblBrandManager.draw();
			}
		});
	}
	
	function AddNew(n){
		addNew = n;
	}
	
	function Batal(n){
		addNew = n;
		$("#divSalesManager").hide();
		$("#listBrandManager").show();
		if(addNew==1){
			$('#btnAdd').click();
		}
	}

	function openpopup(){
	
		$('#myModal').modal('show');
		
	}


$(document).ready(function() {
      tblUser = $('#userlist').DataTable({
			"pageLength": 10,
			"lengthMenu": [
			[5, 10, 20, 50, 100, -1],
			[5, 10, 20, 50, 100, "All"]
			],
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false }
			],
			"order": [[2, 'desc']],
			"processing": true,
			"serverSide": true,
			"autoWidth": false,
			"ajax": {
				"url": '<?php echo site_url('SalesManager/ListUsers') ?>',
				"type": "GET",
				"datatype": "json",
			},
			"language": {
				"lengthMenu": "Menampilkan _MENU_ Data per halaman",
				"zeroRecords": "Maaf, Data tidak ada",
				"info": "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
				"infoEmpty": "Menampilkan 0 s/d 0 dari 0 data",
				"search": "Pencarian",
				"searchPlaceholder": "Tekan ENTER untuk pencarian",
				"infoFiltered": "",
				"paginate": {
					"sPrevious": "Sebelumnya",
					"sNext": "Berikutnya"
				},
			},
      });
		$('#userlist_filter input').unbind();
		$('#userlist_filter input').keyup(function (e) {
			if (e.keyCode == 13) /* if enter is pressed */ {
				tblUser.search($(this).val()).draw();
			}
		});
	  

  });

	function pilihuser(AlternateID,UserName,UserEmail){
		document.getElementById('EmployeeName').value=UserName;
		document.getElementById('EmployeeID').value=UserEmail;
		document.getElementById('UserID').value=AlternateID;
		$('#myModal').modal('hide');
	}
</script>

