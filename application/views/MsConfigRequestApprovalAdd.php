 
<div class="container"> 

	<?php  
	if ($idconfig <> null || $idconfig <> '')
		echo("<div class='form_title'><center>DUPLICATE CONFIG REQUEST APPROVAL</center></div>"); 
	else 
		echo("<div class='form_title'><center>CREATE CONFIG REQUEST APPROVAL</center></div>");
	?>

	<br>
	<?php echo form_open('MsConfigRequestApproval/Insert'); ?>

	<div class="row"> 
        <div class="col-9 col-m-8">
			<input type="submit" class="btn" value="Submit" >
			<input type="button" class="btn" onclick="location.href = '<?php echo site_url('MsConfigRequestApproval') ?>';" value="Cancel">
		</div>
	</div>
	<div class="row">
		<div class="col-3 col-m-4">Event</div>
		<div class="col-4 col-m-4">
			<select name="EventId" class="form-control" id="EventId" required>
				<option value="">Pilih Event Id</option> 
				<?php foreach($ListEvent as $types) { 
					if ($idconfig <> null || $idconfig <> '')
					{
						if ($data_header->EventID== $types->ConfigValue)
							echo("<option selected value='".$types->ConfigValue."'>".$types->ConfigValue."</option>");

						else
							echo("<option value='".$types->ConfigValue."'>".$types->ConfigValue."</option>");

					}
					else
						echo("<option value='".$types->ConfigValue."'>".$types->ConfigValue."</option>");

				}?>
			</select> 	
		</div> 
	</div>
	<div class="row">
		<div class="col-3 col-m-4">Branch</div>
		<div class="col-4 col-m-4">
			<select name="BranchId" class="form-control" id="BranchId" required>
				<option value="">Pilih Branch</option> 
				<option value="ALL">ALL</option>
				<?php foreach($ListBranches as $types) { 
					if ($idconfig <> null || $idconfig <> '')
					{
						if ($data_header->BranchID== $types->BranchID)
							echo("<option selected value='".$types->BranchID."'>".$types->BranchName."</option>");

						else
							echo("<option value='".$types->BranchID."'>".$types->BranchName."</option>");

					}
					else
						echo("<option value='".$types->BranchID."'>".$types->BranchName."</option>");

				}?>
			</select>
		</div> 
	</div>

	<div class="row">
		<div class="col-3 col-m-4">AddInfo1</div>
		<div class="col-4 col-m-4">
			<select name="AddInfo1Name" class="form-control" id="AddInfo1Name" >
				<option value="">Pilih AddInfo1Name</option> 
				<?php foreach($ListInfo as $listinfo) {  
					if ($idconfig <> null || $idconfig <> '')
					{
						if ($data_header->AddInfo1Name == $listinfo->ConfigName)
							echo("<option selected value='".$listinfo->ConfigName."'>".$listinfo->ConfigName."</option>");

						else
							echo("<option value='".$listinfo->ConfigName."'>".$listinfo->ConfigName."</option>");
					}
					else
						echo("<option value='".$listinfo->ConfigName."'>".$listinfo->ConfigName."</option>");

				}?>
			</select>
		</div> 
		<div class="col-4 col-m-4">
			<?php 
				if ($idconfig <> null || $idconfig <> '')
				{	
					echo ("<select name='AddInfo1' class='form-control' id='AddInfo1' > ");
					foreach($info1 as $listinfo) { 
						if ($data_header->AddInfo1 == $listinfo->ConfigValue)
							echo("<option selected value='".$listinfo->ConfigValue."'>".$listinfo->ConfigValue."</option>");

						else
							echo("<option value='".$listinfo->ConfigValue."'>".$listinfo->ConfigValue."</option>");
					}
				} 
				else
				{
					echo ("<select name='AddInfo1' class='form-control' id='AddInfo1' disabled > ");
				}
			?> 
			</select>
		</div> 
	</div>
	<div class="row">
		<div class="col-3 col-m-4">AddInfo2</div>
		<div class="col-4 col-m-4">
			<select name="AddInfo2Name" class="form-control" id="AddInfo2Name" >
				<option value="">Pilih AddInfo2Name</option> 
				<?php foreach($ListInfo as $listinfo) {  
					if ($idconfig <> null || $idconfig <> '')
					{
						if ($data_header->AddInfo2Name == $listinfo->ConfigName)
							echo("<option selected value='".$listinfo->ConfigName."'>".$listinfo->ConfigName."</option>");

						else
							echo("<option value='".$listinfo->ConfigName."'>".$listinfo->ConfigName."</option>");
					}
					else
						echo("<option value='".$listinfo->ConfigName."'>".$listinfo->ConfigName."</option>"); 
				}?>
			</select>
		</div> 
		<div class="col-4 col-m-4">
			<?php 
				if ($idconfig <> null || $idconfig <> '')
				{	
					echo ("<select name='AddInfo2' class='form-control' id='AddInfo2' > ");
					foreach($info2 as $listinfo) { 
						if ($data_header->AddInfo2 == $listinfo->ConfigValue)
							echo("<option selected value='".$listinfo->ConfigValue."'>".$listinfo->ConfigValue."</option>");

						else
							echo("<option value='".$listinfo->ConfigValue."'>".$listinfo->ConfigValue."</option>");
					}
				} 
				else
				{
					echo ("<select name='AddInfo2' class='form-control' id='AddInfo2' disabled > ");
				}
			?>
			</select>
		</div> 
	</div>
	<div class="row">
		<div class="col-3 col-m-4">AddInfo3</div>
		<div class="col-4 col-m-4">
			<select name="AddInfo3Name" class="form-control" id="AddInfo3Name" >
				<option value="">Pilih AddInfo3Name</option> 
				<?php foreach($ListInfo as $listinfo) { 
					if ($idconfig <> null || $idconfig <> '')
					{
						if ($data_header->AddInfo3Name == $listinfo->ConfigName)
							echo("<option selected value='".$listinfo->ConfigName."'>".$listinfo->ConfigName."</option>");

						else
							echo("<option value='".$listinfo->ConfigName."'>".$listinfo->ConfigName."</option>");
					}
					else
						echo("<option value='".$listinfo->ConfigName."'>".$listinfo->ConfigName."</option>"); 
				}?>
			</select>
		</div> 
		<div class="col-4 col-m-4">
			<?php 
				if ($idconfig <> null || $idconfig <> '')
				{	
					echo ("<select name='AddInfo3' class='form-control' id='AddInfo3' > ");
					foreach($info3 as $listinfo) { 
						if ($data_header->AddInfo3 == $listinfo->ConfigValue)
							echo("<option selected value='".$listinfo->ConfigValue."'>".$listinfo->ConfigValue."</option>");

						else
							echo("<option value='".$listinfo->ConfigValue."'>".$listinfo->ConfigValue."</option>");
					}
				} 
				else
				{
					echo ("<select name='AddInfo3' class='form-control' id='AddInfo3' disabled > ");
				}
			?>
			</select>
		</div> 
	</div>
  
 	<div class="row">
		<div class="col-3 col-m-4">Active Date</div>
        <div class="col-4 col-m-4">
 			<input type="text" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1" autocomplete="off" value="<?php 
 			if ($idconfig <> null || $idconfig <> '')
			{
				$time = strtotime($data_header->ActiveDate);
				$newformat = date('m/d/Y',$time);
				echo $newformat;
			}
			else
			{
				echo date('m/d/Y');
			}
 		?>">
		</div>
	</div> 
 
 	<div class="row">
		<div class="col-3 col-m-4"> </div>
        <div class="col-4 col-m-4">
        	<?php 
        		if ($idconfig <> null || $idconfig <> '')
				{
					if ($data_header->IsActive == 1)
						echo("<input type='checkbox' name='IsActive' id='IsActive' value='1' checked> Aktif");
					else
						echo("<input type='checkbox' name='IsActive' id='IsActive' value='1' > Aktif");

				}
				else
				{ 
					echo("<input type='checkbox' name='IsActive' id='IsActive' value='1' checked> Aktif");
				}
        	?> 
			<input type="hidden" name="datatemp" id="datatemp" required>
		</div>
	</div> 

	<div class="row">  
        <div class="col-11 col-m-8"> 
	        <table id="table" class="table table-bordered" cellspacing="0" cellpadding="5px;" width="100%" > 
  				<thead class="thead-light">
	                <tr> 
	                    <th>Min Amount</th>
	                    <th>Max Amount</th>
	                    <th width="80px">Approval Level</th>
	                    <th width="80px">Approval Needed</th>
	                    <th>Approval Position</th>
	                    <th>Approval Division</th>  
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
	<?php echo form_close(); ?>
</div>
 
<script>
	var save_method; //for save method string
	var table; 
 
	function add_person()
	{
		save_method = 'add';
	    $('#form')[0].reset(); // reset form on modals
	    $('.form-group').removeClass('has-error'); // clear error class
	    $('.help-block').empty(); // clear error string
	    $('#modal_form').modal('show'); // show bootstrap modal
	    $('.modal-title').text('Add Approval'); // Set Title to Bootstrap modal title
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
 		else if ($('#ApprovalPosition').val()=="" || $('#ApprovalPosition').val()==null) 
 		{
 			alert('Approval Position is Empty'); 
            $('#btnSave').attr('disabled',false); //set button enable 
 		}
 		else if ($('#ApprovalDivision').val()=="" || $('#ApprovalDivision').val()==null) 
 		{
 			alert('Approval Division is Empty'); 
            $('#btnSave').attr('disabled',false); //set button enable 
 		}
 		else 
 		{ 
		 	$('#table').DataTable().row.add([ 
			  '<p align="right"> '+ number_format(minAmount,0)+' </p>'+'<input type="hidden" class="form-control" name="amin[]"  value="'+minAmount+'">' ,
			  '<p align="right"> '+ number_format(maxAmount,0)+' </p>'+'<input type="hidden" class="form-control" name="amax[]"  value="'+maxAmount+'">' ,
			  '<p align="right"> '+ $('#ApprovalLevel').val()+' </p>'+'<input type="hidden" class="form-control" name="alevel[]"  value="'+$('#ApprovalLevel').val()+'">' ,
			  '<p align="right"> '+ $('#ApprovalNeeded').val()+' </p>'+'<input type="hidden" class="form-control" name="aneeded[]"  value="'+$('#ApprovalNeeded').val()+'">' ,
			  $('#ApprovalPosition').val()+'<input type="hidden" class="form-control" name="aposition[]"  value="'+$('#ApprovalPosition').val()+'">' ,
			  $('#ApprovalDivision').val()+'<input type="hidden" class="form-control" name="adivision[]"  value="'+$('#ApprovalDivision').val()+'">' ,
			  '<a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus"  >Delete</a>'
			]).draw(); 
	 
		    
			$('#btnSave').attr('disabled',false); //set button enable 
			$("#datatemp").val("1"); 
	 		$("#Min_Amount").val(''); 
	 		$("#Max_Amount").val(''); 
	 		$("#ApprovalLevel").val(''); 
	 		$("#ApprovalNeeded").val(''); 
			$("#ApprovalPosition").prop('selectedIndex',-1);
			$("#ApprovalDivision").prop('selectedIndex',-1);
 		} 
 		
	}   

	function test()
	{ 

		<?php if ($idconfig<>null) 
		{
			?>
				$("#datatemp").val("1"); 
				$.ajax({ 
						type: 'GET', 
						url: '<?php echo site_url("MsConfigRequestApproval/EditDetail?id=$idconfig") ?>',
						dataType: 'json',
						success: function (data){ 
							if(data.length>0){ 
								for (var i = 0; i < data.length; i++) {    
									$('#table').DataTable().row.add([ 
									  '<p align="right"> '+ Number(parseInt(data[i].MinAmount) || 0).toFixed(2)+' </p>'+'<input type="hidden" class="form-control" name="amin[]"  value="'+data[i].MinAmount+'">' ,
									  '<p align="right"> '+ Number(parseInt(data[i].MaxAmount) || 0).toFixed(2)+' </p>'+'<input type="hidden" class="form-control" name="amax[]"  value="'+data[i].MaxAmount+'">' ,
									  '<p align="right"> '+ data[i].ApprovalLevel+' </p>'+'<input type="hidden" class="form-control" name="alevel[]"  value="'+data[i].ApprovalLevel+'">' ,
									  '<p align="right"> '+ data[i].ApprovalNeeded+' </p>'+'<input type="hidden" class="form-control" name="aneeded[]"  value="'+data[i].ApprovalNeeded+'">' ,
									  data[i].ApprovalByPosition+'<input type="hidden" class="form-control" name="aposition[]"  value="'+data[i].ApprovalByPosition+'">' ,
									  data[i].ApprovalByDivision+'<input type="hidden" class="form-control" name="adivision[]"  value="'+data[i].ApprovalByDivision+'">' ,
									  '<a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus"  >Delete</a>'
									]).draw();  
								} 
							} 
						}
					});

			<?php
		}
		?>
		
	}

	$(document).ready(function() {   
 		
 		test();

		$("#ApprovalPosition").prop('selectedIndex',-1);
		$("#ApprovalDivision").prop('selectedIndex',-1);
		$('#table').dataTable({searching: false, paging: false, info: false, order: false});

		$('#dp1').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
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

	   	$("#table").on("click", "td", function() {   
	   		if ($(this).text()=="Delete")
	   		{
		     	var index = $(this).parent().index(); 
		     	if(confirm('Are you sure delete this data?' ))
		     	{ 		
			        var table = $('#table').DataTable(); 
					table.row(index).remove().draw();
				} 
	   		} 
	   	});
	});

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

 

 

