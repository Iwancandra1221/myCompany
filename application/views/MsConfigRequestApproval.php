<style>
	
	.dataTables_wrapper .dataTables_length {
	float:left;
	}
	.dataTables_wrapper .dataTables_paginate{
	float:right;
	}
	
	table.dataTable thead .sorting, table.dataTable thead .sorting_desc, table.dataTable thead .sorting_asc {
	background:none;
	}
	.btn{
	width:auto;
	}
</style>

<script>
	$(document).ready(function() {
		$('#dp1').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		}); 
		$("#table_detail").on("click", "td", function() {   
	   		if ($(this).text()=="Delete")
	   		{
		     	var index = $(this).parent().index(); 
		     	if(confirm('Are you sure delete this data?' ))
		     	{ 		
			        var table = $('#table_detail').DataTable(); 
					table.row(index).remove().draw();
				} 
	   		} 
	   	});
  
		$("#AddInfo1Name").change(function() { 
 			
			if ( $("#AddInfo1Name")[0].selectedIndex ==  $("#AddInfo2Name")[0].selectedIndex  || $("#AddInfo1Name")[0].selectedIndex ==  $("#AddInfo3Name")[0].selectedIndex )
			{
				$('#AddInfo1Name option')[0].selected = true;
				alert("Already select");
			} 
 
			if($(this).val()=='') { 
				$("#AddInfo1").attr('disabled', true);
			}
			else
			{
				$("#AddInfo1").attr('disabled', false);
			}

			$('#AddInfo1').empty(); 
			var conceptName = $(this).val(); 
				//$('.loading').show();
				$.ajax({ 
					type: 'GET', 
					url: '<?php echo site_url("MsConfigRequestApproval/GetListInfoDetail?id='+conceptName+'") ?>',
					dataType: 'json',
					success: function (data){
						//$('.loading').hide();
						// alert(data);
						if(data.length>0){ 
							for (var i = 0; i < data.length; i++) { 
	 							$("#AddInfo1").append(new Option(data[i].ConfigValue, data[i].ConfigValue)); 
							} 
						} 
					}
				}); 
		});

		$("#AddInfo2Name").change(function() {

			if ( $("#AddInfo2Name")[0].selectedIndex ==  $("#AddInfo1Name")[0].selectedIndex  || $("#AddInfo2Name")[0].selectedIndex ==  $("#AddInfo3Name")[0].selectedIndex )
			{
				$('#AddInfo2Name option')[0].selected = true;
				alert("Already select");
			} 


			if($(this).val()=='') { 
				$("#AddInfo2").attr('disabled', true);
			}
			else
			{
				$("#AddInfo2").attr('disabled', false);
			}

			$('#AddInfo2').empty(); 
			var conceptName = $(this).val(); 
				//$('.loading').show();
				$.ajax({ 
					type: 'GET', 
					url: '<?php echo site_url("MsConfigRequestApproval/GetListInfoDetail?id='+conceptName+'") ?>',
					dataType: 'json',
					success: function (data){
						//$('.loading').hide();
						// alert(data);
						if(data.length>0){ 
							for (var i = 0; i < data.length; i++) { 
	 							$("#AddInfo2").append(new Option(data[i].ConfigValue, data[i].ConfigValue)); 
							} 
						} 
					}
				});

		});

		$("#AddInfo3Name").change(function() {

			if ( $("#AddInfo3Name")[0].selectedIndex ==  $("#AddInfo1Name")[0].selectedIndex ||  $("#AddInfo3Name")[0].selectedIndex ==  $("#AddInfo2Name")[0].selectedIndex )
			{
				$('#AddInfo3Name option')[0].selected = true;
				alert("Already select");
			}  

			if($(this).val()=='') { 
				$("#AddInfo3").attr('disabled', true);
			}
			else
			{
				$("#AddInfo3").attr('disabled', false);
			}

			$('#AddInfo3').empty(); 
			var conceptName = $(this).val(); 
				//$('.loading').show();
				$.ajax({ 
					type: 'GET', 
					url: '<?php echo site_url("MsConfigRequestApproval/GetListInfoDetail?id='+conceptName+'") ?>',
					dataType: 'json',
					success: function (data){
						//$('.loading').hide();
						// alert(data);
						if(data.length>0){ 
							for (var i = 0; i < data.length; i++) { 
	 							$("#AddInfo3").append(new Option(data[i].ConfigValue, data[i].ConfigValue)); 
							} 
						} 
					}
				});

		});

        var t = $('#example').DataTable({
			"pageLength": 10,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false }
			],
			// "dom": '<"top"lf>rt<"bottom"ip><"clear">',
			"dom": '<"top"l>rt<"bottom"ip><"clear">',
			"order": [[3, 'asc'],[2, 'asc'],[4, 'asc'],[5, 'asc'],[6, 'asc']],
		});
		
		t.on('order.dt search.dt', function () {
			let i = 1;
			
			t.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
				this.data(i++);
			});
		}).draw();
		
		$('#cari').keyup(function(){
			t.search($(this).val()).draw();			
		})
		
		$("<a href='<?php echo site_url('MsConfigRequestApproval/Add') ?>' class='btn btn-default' style='float:right; margin-bottom:5px'><i class='glyphicon glyphicon-plus-sign'></i> Create</a>").insertBefore('#example');
	});
	
</script>
<div style="position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;">
	<div style="padding: 5px;">
		<?php
			if($this->session->flashdata('success')){
				echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>".$this->session->flashdata('success')."</div>";
			}
			
			if($this->session->flashdata('error')){
				echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>".$this->session->flashdata('error')."</div>";
			}
		?>
	</div>
</div>

<div class="container">
	<div class="form_title"><div style="text-align:center;">MASTER CONFIG REQUEST APPROVAL</div></div>
	<br>
 
	
	<table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%" summary="MASTER CONFIG REQUEST APPROVAL">
        <?php 
			echo "<thead>";
			echo "<tr>";
			echo "<th style='width:5px' class='no-sort'>No</th>";
			echo "<th style='width:100px'>Config ID</th>"; 
			echo "<th style='width:100px'>Event ID</th>";     
			echo "<th style='width:50px'>AddInfo1</th>";   
			echo "<th style='width:50px'>AddInfo2</th>";   
			echo "<th style='width:50px'>AddInfo3</th>";   
			echo "<th style='width:100px'>Branch</th>";    
			echo "<th style='width:50px'>Active Date</th>";
			echo "<th style='width:50px'>Last Modified</th>";
			echo "<th style='width:5px'>Aktif</th>"; 
            echo "<th style='width:5px' class='no-sort'>Edit</th>"; 
            echo "<th style='width:5px' class='no-sort'>Duplicate</th>"; 
            echo "<th style='width:5px' class='no-sort'>Delete</th>"; 
			echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			$i = 1;
			foreach($result as $r) {
				echo "<tr>"; 
				echo "<td>".$i."</td>";
				echo "<td><b>".$r->ConfigID."</b></td>"; 
				echo "<td><b>".$r->EventID."</b></td>";     
				echo "<td>".(($r->AddInfo1=="")?"":$r->AddInfo1Name.":<br><b>".$r->AddInfo1)."</b></td>";  
		        echo "<td>".(($r->AddInfo2=="")?"":$r->AddInfo2Name.":<br><b>".$r->AddInfo2)."</b></td>";  
		        echo "<td>".(($r->AddInfo3=="")?"":$r->AddInfo3Name.":<br><b>".$r->AddInfo3)."</b></td>"; 
				echo "<td><b>".$r->BranchID."</b></td>";   
				echo "<td>".date("d-M-Y", strtotime($r->ActiveDate))."</td>";
				echo "<td>".$r->ModifiedBy."<br>".$r->ModifiedDate."</td>";
				echo "<td><input type='checkbox' ".(($r->IsActive==1)?'checked':'')."  onclick='return false'></td>";
				if($access->can_update == 1)
                echo '<td>
				<button type="button" id="btnedit" class="btn btn-sm btn-default" onclick="javascript:edit_config('."'".$r->ConfigID."'".')"><i class="glyphicon glyphicon-pencil"></i></button> 
				</td>'; 
				if($access->can_update == 1)
                echo '<td>
				<button type="button" id="btnduplicate" class="btn btn-sm btn-default" onclick="javascript:duplicate_config('."'".$r->ConfigID."'".')"><i class="glyphicon glyphicon-duplicate"></i></button> 
				</td>'; 
				if($access->can_update == 1)
                echo '<td>
				<button type="button" id="btndelete" class="btn btn-sm btn-default" onclick="javascript:delete_config('."'".$r->ConfigID."'".')"><i class="glyphicon glyphicon-trash"></i></button> 
				</td>'; 
				$i += 1;
			}
		echo "</tbody>"; ?>
	</table>
	
	
	
	
	<div id="modal_edit" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg" role="document" style="width:1250px;">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Edit</h4>
				</div>
				<form class="form-horizontal">
					<div class="modal-body">
						<div class="row">
							<label class="col-xs-3">Config ID</label>
							<div class="col-xs-4"> 
								<input type="text" id="ConfigID" class="form-control" disabled>
							</div>
						</div>

						<div class="row">
							<label class="col-xs-3">Event</label>
							<div class="col-xs-4">
								<select name="EventId" class="form-control" id="EventId" required>
									<option value="">Pilih Event Id</option> 
									<?php foreach($ListEvent as $types) { 
										echo("<option value='".$types->ConfigValue."'>".$types->ConfigValue."</option>");
									}?>
								</select>
							</div>
						</div> 
						<div class="row">
							<label class="col-xs-3">Branch</label>
							<div class="col-xs-4">
								<select name="BranchId" class="form-control" id="BranchId" required>
									<option value="">Pilih Branch</option> 
									<option value="ALL">ALL</option>
									<?php foreach($ListBranches as $types) { 
										echo("<option value='".$types->BranchID."'>".$types->BranchName."</option>");
									}?>
								</select>
							</div>
						</div> 

						<div class="row">
							<label class="col-xs-3">AddInfo1</label>
							<div class="col-xs-4">
								<select name="AddInfo1Name" class="form-control" id="AddInfo1Name" >
									<option value="">Pilih AddInfo1Name</option> 
									<?php foreach($ListInfo as $listinfo) { 
										echo("<option value='".$listinfo->ConfigName."'>".$listinfo->ConfigName."</option>");
									}?>
								</select>
							</div>
							<div class="col-xs-4">
								<select name="AddInfo1" class="form-control" id="AddInfo1" disabled > 
								</select>
							</div>
						</div> 

						<div class="row">
							<label class="col-xs-3">AddInfo2</label>
							<div class="col-xs-4">
								<select name="AddInfo2Name" class="form-control" id="AddInfo2Name" >
									<option value="">Pilih AddInfo2Name</option> 
									<?php foreach($ListInfo as $listinfo) { 
										echo("<option value='".$listinfo->ConfigName."'>".$listinfo->ConfigName."</option>");
									}?>
								</select>
							</div>
							<div class="col-xs-4">
								<select name="AddInfo2" class="form-control" id="AddInfo2" disabled > 
								</select>
							</div>
						</div> 

						<div class="row">
							<label class="col-xs-3">AddInfo3</label>
							<div class="col-xs-4">
								<select name="AddInfo3Name" class="form-control" id="AddInfo3Name" >
									<option value="">Pilih AddInfo3Name</option> 
									<?php foreach($ListInfo as $listinfo) { 
										echo("<option value='".$listinfo->ConfigName."'>".$listinfo->ConfigName."</option>");
									}?>
								</select>
							</div>
							<div class="col-xs-4">
								<select name="AddInfo3" class="form-control" id="AddInfo3" disabled > 
								</select>
							</div>
						</div>   

					 	<div class="row"> 
							<label class="col-xs-3">Active Date</label>
							<div class="col-xs-4">
 								<input type="text" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1" autocomplete="off" value="<?php echo date('m/d/Y') ?>">
							</div>
						</div> 

						<div class="row">
							<label class="col-xs-3"></label>
							<div class="col-xs-4">
								<input type="checkbox" id="edit_aktif" value="1"> Aktif
							</div>
						</div>
						<div class="row">  
					        <div class="col-11 col-m-8"> 
						        <table id="table_detail" class="table table-bordered" cellspacing="0" cellpadding="5px;" width="100%" > 
					  				<thead class="thead-light">
						                <tr> 
						                    <th width="80px">Min Amount</th>
						                    <th width="80px">Max Amount</th>
						                    <th width="50px">Approval Level</th>
						                    <th width="50px">Approval Needed</th>
						                    <th width="80px">Approval Position</th>
						                    <th width="80px">Approval Division</th>  
						                    <th width="50px">Action</th>
						                </tr>
					  					<tr> 
						                    <th> 
													<input type="number" class="form-control" name="Min_Amount" id="Min_Amount" placeholder="0" >  
					                        </th>
						                    <th> 
													<input type="number" class="form-control" name="Max_Amount" id="Max_Amount" placeholder="0" >  
					                        </th>
						                    <th> 
													<input type="number" class="form-control" name="ApprovalLevel" id="ApprovalLevel" placeholder="0" >  
					                        </th>
						                    <th> 
													<input type="number" class="form-control" name="ApprovalNeeded" id="ApprovalNeeded" placeholder="0" >  
					                        </th>
						                    <th> 
													<select name="ApprovalPosition" class="form-control" id="ApprovalPosition" > 
														<?php foreach($ListSalesMan as $list) { 
															echo("<option value='".$list->level_slsman."'>".$list->level_slsman."</option>");
														}?> 
													</select>  
						                    </th>
						                    <th> 
													<select name="ApprovalDivision" class="form-control" id="ApprovalDivision" > 
														<?php foreach($ListDivision as $list) { 
															echo("<option value='".$list->division."'>".$list->division."</option>");
														}?>
													</select>  
						                    </th>  
						                    <th> 
                								<button type="button" id="btnSave" onclick="save()" ><span class="glyphicon glyphicon-plus"></span></button>  
					                		</th>
						                </tr>
						            </thead>
						            <tbody>
						            </tbody> 
						        </table> 
					    	</div>
					    </div> 
						
						<div class="row">
							<label class="col-xs-12">
								<span id="view_created"></span><br>
								<span id="view_modified"></span>
							</label>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" id="btn_save" class="btn btn-primary" onclick="javascript:update_config()" >Save</button>
					</div>
				</form>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
</div> <!-- /container -->




<script>
	$('#confirm-delete').on('show.bs.modal', function(e) {
		$(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
		$('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
	});
	
	$('#confirm-delete').on('show.bs.modal', function(e) {
		var data = $(e.relatedTarget).data();
		// $('.title', this).text(data.recordTitle);
	});
 	
	function duplicate_config(id)
	{
		window.location.href='<?php echo site_url("MsConfigRequestApproval/Add?id='+id+'") ?>';
	}

	function edit_config(id){
		$('.loading').show();
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo site_url("MsConfigRequestApproval/Edit?id='+id+'") ?>',
			dataType: 'json',
			success: function (data){
				// alert(data);
				if(data.ConfigID){ 

					$('#edit_id').val(data.ConfigID);
					$('#ConfigID').val(data.ConfigID); 
					$('#EventId').val(data.EventID); 
					$('#BranchId').val(data.BranchID); 
					$('#AddInfo1Name').val(data.AddInfo1Name); 
					$('#AddInfo2Name').val(data.AddInfo2Name); 
					$('#AddInfo3Name').val(data.AddInfo3Name); 
					$('#edit_aktif').prop("checked",data.IsActive);
					$('#dp1').val(date('m/d/Y',strtotime(data.ActiveDate)));
					$('#view_created').text('Created on '+data.CreatedDate+' By '+data.CreatedBy);
					$('#view_modified').text('Last Modified on '+data.ModifiedDate+' By '+data.ModifiedBy);
 
					if(data.AddInfo1Name=="") { 
						$("#AddInfo1").attr('disabled', true);
					}
					else
					{
						$("#AddInfo1").attr('disabled', false);
					}

					$('#AddInfo1').empty();  
					$.ajax({ 
						type: 'GET', 
						url: '<?php echo site_url("MsConfigRequestApproval/GetListInfoDetail?id='+data.AddInfo1Name+'") ?>',
						dataType: 'json',
						success: function (data1){ 
							if(data1.length>0){ 
								for (var i = 0; i < data1.length; i++) { 
									$("#AddInfo1").append(new Option(data1[i].ConfigValue, data1[i].ConfigValue));
								}
								$('#AddInfo1').val(data.AddInfo1);  
							}
						}
					});   

					if(data.AddInfo2Name=="") { 
						$("#AddInfo2").attr('disabled', true);
					}
					else
					{
						$("#AddInfo2").attr('disabled', false);
					}

					$('#AddInfo2').empty();  
					$.ajax({ 
						type: 'GET', 
						url: '<?php echo site_url("MsConfigRequestApproval/GetListInfoDetail?id='+data.AddInfo2Name+'") ?>',
						dataType: 'json',
						success: function (data2){ 
							if(data2.length>0){ 
								for (var i = 0; i < data2.length; i++) { 
									$("#AddInfo2").append(new Option(data2[i].ConfigValue, data2[i].ConfigValue));
								}
								$('#AddInfo2').val(data.AddInfo2);  
							}
						}
					});  

					if(data.AddInfo3Name=="") { 
						$("#AddInfo3").attr('disabled', true);
					}
					else
					{
						$("#AddInfo3").attr('disabled', false);
					}

					$('#AddInfo3').empty();  
					$.ajax({ 
						type: 'GET', 
						url: '<?php echo site_url("MsConfigRequestApproval/GetListInfoDetail?id='+data.AddInfo3Name+'") ?>',
						dataType: 'json',
						success: function (data3){ 
							if(data3.length>0){ 
								for (var i = 0; i < data3.length; i++) { 
									$("#AddInfo3").append(new Option(data3[i].ConfigValue, data3[i].ConfigValue));
								} 
								$('#AddInfo3').val(data.AddInfo3);  
							}
						}
					});  
 
					var table = $('#table_detail').DataTable();

					//clear datatable
					table.clear().draw();

					//destroy datatable
					table.destroy();
 
					$('#table_detail').dataTable({searching: false, paging: false, info: false, order: false});
					$.ajax({ 
						type: 'GET', 
						url: '<?php echo site_url("MsConfigRequestApproval/EditDetail?id='+data.ConfigID+'") ?>',
						dataType: 'json',
						success: function (data){ 
							if(data.length>0){ 
								for (var i = 0; i < data.length; i++) {   
									$('#table_detail').DataTable().row.add([ 
									  '<p align="right"> '+ number_format((parseInt(data[i].MinAmount) || 0),0)+' </p>'+'<input type="hidden" class="form-control" name="aMin[]"  value="'+parseInt(data[i].MinAmount) || 0+'">' ,
									  '<p align="right"> '+ number_format((parseInt(data[i].MaxAmount) || 0),0)+' </p>'+'<input type="hidden" class="form-control" name="aMax[]"  value="'+parseInt(data[i].MaxAmount) || 0+'">' ,
									  '<p align="right"> '+ data[i].ApprovalLevel+' </p>'+'<input type="hidden" class="form-control" name="alevel[]"  value="'+data[i].ApprovalLevel+'">' ,
									  '<p align="right"> '+ data[i].ApprovalNeeded+' </p>'+'<input type="hidden" class="form-control" name="aneeded[]"  value="'+data[i].ApprovalNeeded+'">' ,
									  data[i].ApprovalByPosition+'<input type="hidden" class="form-control" name="aposition[]"  value="'+data[i].ApprovalByPosition+'">' ,
									  data[i].ApprovalByDivision+'<input type="hidden" class="form-control" name="adivision[]"  value="'+data[i].ApprovalByDivision+'">' ,
									  '<a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus"  >Delete</a>'
									]).draw();  
								}
								$('.loading').hide();	
								$('#modal_edit').modal('show');
							}
							else
							{ 
								$('.loading').hide();	
								$('#modal_edit').modal('show');
							}
						}
					});   
				}
			}
		});
	}
	
	function delete_config(id)
	{ 
		if(confirm("Ara you sure delete this config ("+id+")"))
		{
			 $.ajax(
			 { 
			 	type: 'POST', 
			 	url: '<?php echo site_url("MsConfigRequestApproval/DeleteConfig") ?>', 
			 	data: { ConfigID: id }, 
			 	dataType: 'json',
			 	success: function (data2) { 
			 		if( data2.result != 'SUKSES' ){   
						alert(data2.result+'\n'+data2.message);
						location.reload();
			 		}
			 		else
			 		{  
						alert(data2.result+'\n'+data2.message);
						location.reload();
			 		}
			 	}
			 });   
		}
	}

	function save()
	{ 
	    $('#btnSave').attr('disabled',true); //set button disable 
	    var minAmount = 0;
	    var maxAmount = 0;
 		if ($('#Min_Amount').val()!="") 
 		{ 
 			minAmount = $('#Min_Amount').val();
 		}
 		if ($('#Max_Amount').val()!="") 
 		{ 
 			maxAmount = $('#Max_Amount').val();
 		}
 		 
 		if ($('#ApprovalLevel').val()=="") 
 		{
 			alert('Approval Level is Empty'); 
            $('#btnSave').attr('disabled',false); //set button enable 
 		}
 		else if ($('#ApprovalNeeded').val()=="") 
 		{
 			alert('Approval Needed is Empty'); 
            $('#btnSave').attr('disabled',false); //set button enable 
 		}
 		else 
 		{ 
		 	$('#table_detail').DataTable().row.add([ 
			  '<p align="right"> '+ Number(parseInt(minAmount) || 0).toFixed(2) +' </p>'+'<input type="hidden" class="form-control" name="aMin[]"  value="'+minAmount+'">' ,
			  '<p align="right"> '+ Number(parseInt(maxAmount) || 0).toFixed(2) +' </p>'+'<input type="hidden" class="form-control" name="aMax[]"  value="'+maxAmount+'">' ,
			  '<p align="right"> '+ $('#ApprovalLevel').val() +' </p>'+'<input type="hidden" class="form-control" name="alevel[]"  value="'+$('#ApprovalLevel').val()+'">' ,
			  '<p align="right"> '+ $('#ApprovalNeeded').val() +' </p>'+'<input type="hidden" class="form-control" name="aneeded[]"  value="'+$('#ApprovalNeeded').val()+'">' ,
			  $('#ApprovalPosition').val()+'<input type="hidden" class="form-control" name="aposition[]"  value="'+$('#ApprovalPosition').val()+'">' ,
			  $('#ApprovalDivision').val()+'<input type="hidden" class="form-control" name="adivision[]"  value="'+$('#ApprovalDivision').val()+'">' ,
			  '<a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus"  >Delete</a>'
			]).draw(); 
	 
		    
			$('#btnSave').attr('disabled',false); //set button enable 
 		} 
 		$("#datatemp").val("1"); 
 		$("#Min_Amount").val(''); 
 		$("#Max_Amount").val(''); 
 		$("#ApprovalLevel").val(''); 
 		$("#ApprovalNeeded").val(''); 
		$("#ApprovalPosition").prop('selectedIndex',-1);
		$("#ApprovalDivision").prop('selectedIndex',-1);
	}  

	function update_config(){    
		var ConfigID = $('#ConfigID').val();
		var EventId = $('#EventId').val();
		var BranchId = $('#BranchId').val();
		var AddInfo1Name = $('#AddInfo1Name').val();
		var AddInfo2Name = $('#AddInfo2Name').val();
		var AddInfo3Name = $('#AddInfo3Name').val(); 
		var AddInfo1 = $('#AddInfo1').val();
		var AddInfo2 = $('#AddInfo2').val();
		var AddInfo3 = $('#AddInfo3').val();  
		var aktif = $('#edit_aktif').is(":checked"); 
		var dp1 = $('#dp1').val();;

		if(EventId==''){
			alert('Config value wajib diisi!');
			return false;
		} 
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url("MsConfigRequestApproval/Update") ?>', 
			data: { ConfigID: ConfigID, EventId: EventId, BranchId: BranchId, AddInfo1Name: AddInfo1Name, AddInfo2Name: AddInfo2Name,AddInfo3Name: AddInfo3Name,AddInfo1: AddInfo1,AddInfo2: AddInfo2,AddInfo3: AddInfo3, dp1: dp1,  IsActive: aktif }, 
			dataType: 'json',
			success: function (data) { 
				if(data.result=='SUKSES'){  
						$.ajax(
						{ 
							type: 'POST', 
							url: '<?php echo site_url("MsConfigRequestApproval/DeleteDetail") ?>', 
							data: { ConfigID: ConfigID }, 
							dataType: 'json',
							success: function (data2) { 
								if(data2.result!='SUKSES'){    
									alert(data.result+'\n'+data.message);
								}
							}
						});  
						var rowCounta =$("#table_detail tbody tr").length;   
						for (var i = 0; i < rowCounta; i++) 
						{   
							var cell1 = $('#table_detail tbody tr:eq(' + i + ') td:eq(' + 0 + ')').text();
							var cell2 = $('#table_detail tbody tr:eq(' + i + ') td:eq(' + 1	+ ')').text();
							var cell3 = $('#table_detail tbody tr:eq(' + i + ') td:eq(' + 2 + ')').text();
							var cell4 = $('#table_detail tbody tr:eq(' + i + ') td:eq(' + 3 + ')').text(); 
							var cell5 = $('#table_detail tbody tr:eq(' + i + ') td:eq(' + 4 + ')').text(); 
							var cell6 = $('#table_detail tbody tr:eq(' + i + ') td:eq(' + 5 + ')').text();  
							$.ajax({ 
								type: 'POST', 
								url: '<?php echo site_url("MsConfigRequestApproval/InsertDetail") ?>',
								data: { ConfigID: ConfigID, cell1: cell1, cell2: cell2, cell3: cell3, cell4: cell4,cell5: cell5,cell6: cell6 }, 
								dataType: 'json',
								success: function (data3) { 
									if(data3.result!='SUKSES'){   
										alert(data3.result+'\n'+data3.message);
									}
								}
							});   
						}  
 
					alert(data.result+'\n'+data.message);
					location.reload();
					$('#modal_edit').modal('hide');
				}
			}
		});
	}
 
 	function number_format(number, decimals, decPoint, thousandsSep){
        decimals = decimals || 0;
        number = parseFloat(number);

        if(!decPoint || !thousandsSep){
            decPoint = '.';
            thousandsSep = ',';
        }

        var roundedNumber = Math.round( Math.abs( number ) * ('1e' + decimals) ) + '';
        // add zeros to decimalString if number of decimals indicates it
        roundedNumber = (1 > number && -1 < number && roundedNumber.length <= decimals)
                ? Array(decimals - roundedNumber.length + 1).join("0") + roundedNumber
                : roundedNumber;
        var numbersString = decimals ? roundedNumber.slice(0, decimals * -1) : roundedNumber.slice(0);
        var checknull = parseInt(numbersString) || 0;
    
        // check if the value is less than one to prepend a 0
        numbersString = (checknull == 0) ? "0": numbersString;
        var decimalsString = decimals ? roundedNumber.slice(decimals * -1) : '';
        
        var formattedNumber = "";
        while(numbersString.length > 3){
            formattedNumber = thousandsSep + numbersString.slice(-3) + formattedNumber;
            numbersString = numbersString.slice(0,-3);
        }

        return (number < 0 ? '-' : '') + numbersString + formattedNumber + (decimalsString ? (decPoint + decimalsString) : '');
    }    

</script>
