<style>
	.row {
	line-height:30px;
	vertical-align:middle;
	clear:both;
	}
	/*
	td 
	{
	height: 50px; 
	width: 50px;
	}
	
	#cssTable td 
	{
	text-align: center; 
	vertical-align: middle;
	}
	
	#break-diag{
	display:none;
	}   
	.right {
	text-align: right;
	float: right;
	margin-bottom:5px;
	}
	.modal-body {
    height: 650px;
    overflow-y: scroll;
    margin-bottom:25px;
	}  
	
	.modal-dialog{
	width: 1000px;
	overflow-y: initial !important
	} 
	
	.merah { color:#c91006; }
	.hijau { color:#0ead05;}
	
	input[type=text], select {
    width: 100%;
    padding: 12px 20px;
    margin: 8px 0;
    display: inline-block;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
	}
	*/
</style>

<div class="container">
    <div class="page-title"><?php echo $title; ?></div>
	
    <div class="row">
		<div class="col-12">
			<?php if($_SESSION["can_create"] == 1) { ?>
				<a href="#" class="btn btn-dark" data-toggle='modal' data-target='#insert_new' onclick="addnewtemplate('','<?php echo $tipe ?>','Add')">New Template</a> 
			<?php } ?>
		</div>
	</div>
    <table id="TblKpi" class="table table-striped table-bordered" cellspacing="0" width="100%">
		<?php
			echo "<thead>";
			echo "<tr>"; 
			echo "<th >Template ID</th>"; 
			echo "<th >Template Name</th>";
			echo "<th >KPI Category</th>"; 
			echo "<th >Start Date</th>";  
			echo "<th >Active</th>"; 
			echo "<th >Modified By</th>";
			echo "<th >Modified Date</th>";  
			//echo "<th >kpi category name</th>";    
			echo "<th class='no-sort'>Action</th>";  
			echo "</tr>";  
			echo "</thead>"; 
			echo "<tbody id='TblKpiBody'>";  
			$x = 1;  
			foreach($list as $data) {
				$template_id = $data->template_id;
				$template_name = $data->template_name; 
				$kpi_category_id = $data->kpi_category_id;    
				$start_date = $data->start_date; 
				$is_active = $data->is_active;   
				$modified_by = $data->modified_by;  
				$modified_date = $data->modified_date;   
				
				$chkActive = ''; 
				if ($is_active == 1)
				{
					$chkActive = "<input type='checkbox'  onclick='return false;' checked >";
				}
				else
				{
					$chkActive = "<input type='checkbox' onclick='return false;'>";
				}
				
				echo "<tr>"; 
				echo "<td >".$template_id."</td>";
				echo "<td >".$template_name."</td>"; 
				echo "<td >".$kpi_category_id."</td>";  
				echo "<td >".date("d-M-Y",strtotime($start_date))."</td>";  
				echo "<td >".$chkActive."</td>";    
				echo "<td >".$modified_by."</td>";  
				echo "<td >".date("d-M-Y",strtotime($modified_date))."</td>";    
				
				$ACTION = ''; 
				if($_SESSION["can_read"] == 1)  
				$ACTION .= '<button class="btn btn-sm btn-dark" data-toggle="modal" data-target="#insert_new" onclick="addnewtemplate('."'".$template_id."','".$tipe."','View'".')"><i class="glyphicon glyphicon-search biru"></i></button> '; 
				if($_SESSION["can_update"] == 1)  
				$ACTION .= '<button class="btn btn-sm btn-dark" data-toggle="modal" data-target="#insert_new" onclick="addnewtemplate('."'".$template_id."','".$tipe."','Edit'".')"><i class="glyphicon glyphicon-edit hijau"></i></button> '; 
				if($_SESSION["can_delete"] == 1)
				$ACTION .= '<button class="btn btn-sm btn-danger-dark" onclick="delete_template('."'".$template_id."'".')"  ><i class="glyphicon glyphicon-trash merah"></i></button>';
				
				echo "<td class='hideOnMobile'>".$ACTION."</td>";
				echo "</tr>";
				$x += 1;
			}
		echo "</tbody>"; ?>
	</table>  
	
    <div class="modal fade"  id="insert_new" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
		<div class="modal-dialog modal-lg">
			<div class="modal-content" id="previewDiv">
				<div class="modal-header">
					<h4><center><b>NEW TEMPLATE</b></center></h4>
				</div>
				<div class="modal-body p20">
					<?php echo form_open('MasterTemplateKPI/insert', array('id' => 'form_save')) ?>
					<input type="hidden" name="event_submit" id="event_submit" value="">
					<input type="hidden" name="texens" id="texens" value="">
					<input type="hidden" name="listposition" id="listposition" value="">
					<input type="hidden" name="listkpi" id="listkpi" value="">
					<input type="hidden" name="listbobot" id="listbobot" value="">
					<div class="row">
						<div class="col-3">Template ID</div>
						<div class="col-9">
							<input type="text" class="form-control" name="txt_template_id" id="txt_template_id" placeholder="" required readonly>
						</div>
					</div>
					<div class="row">
						<div class="col-3">Template Name</div>
						<div class="col-9">
							<input type="text" class="form-control" name="txt_template_name" id="txt_template_name" placeholder="" required>
						</div>
					</div>
					<div class="row">
						<div class="col-3">KPI Category</div>
						<div class="col-9">
							<select name='cboKpiCategory' class='form-control' id='cboKpiCategory' onchange="clearForm()" ></select>
						</div>
					</div>
					<div class="row">
						<div class="col-3">Start Date</div>
						<div class="col-3">
							<input type="text" class="form-control" id="dtStartDate" placeholder="mm/dd/yyyy" name="dtStartDate" autocomplete="off" 
							value="<?php echo date('m/d/Y'); ?>">
							
						</div>
						<div class="col-2"><input type='checkbox' name='IsActive' id='IsActive' value='1' checked> Active </div>
						<div class="col-4" style="text-align:right"><big>TOTAL BOBOT : <b><span style="color:orange" id="totalbobot">0</span></b></big></div>
					</div>
					<div class="row">
						<div class="col-md-3">   
							<input type="button" onclick="addRow()" class="btn btn-dark" id="addrows" value="Add Row">
						</div>  
						<div class="col-md-6" style ="text-align:center;">
							
						</div>  
						<div class="col-md-3 " style ="text-align:right;">   
							<input type="submit" id="btnSubmit" name="btnSubmit" class="btn btn-dark" value="SAVE"> 
							<input type="button" class="btn btn-dark" value="CLOSE" data-dismiss="modal">
						</div> 
					</div> 
					<br> 
					<table class="table table-bordered" cellspacing="0">
						<thead id="theadBarangCampaign">
							<tr>
								<th width="15%">KPI Position</th>  
								<th width="50%" >KPI</th>  
								<th width="*">KPI Bobot</th>  
								<th width="5%" class="colDelete"></th> 
							</tr>
						</thead>
						<tbody id="tbodyBarangCampaign">
							<tr><td colspan="4"><center>Klik ADD ROW untuk isi data</center></td></tr>
						</tbody>
					</table>     
					<?php echo form_close(); ?> 
				</div>
			</div>
		</div> 
	</div> 
</div>


<script> 
	var listposition="";  
	var listkpi="";  
	var listbobot="";  
	var idx = 0; 
	var brs = 0;
	var totalbobot = 0; 
	var same_pos = 0;
	var bobot = new Array;
	var position = new Array;
	var kpi = new Array;
	
	$(document).ready(function() {
		$('#TblKpi').DataTable({
			"pageLength"    : 10,
			"searching"     : true,
			"autoWidth": false,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false }
			],
			// "dom": '<"top">rt<"bottom"ip><"clear">',
			"order": [[0, 'asc']],
		}); 
	}); 
	
	function cbo_KPI_onchange(selectObject,i,event=0) {  
		var value = selectObject.value;  
		kpi[i] = value;  
		
		hitungtotal();
		checkpostion();
		if (event!=1)
		listkpi_fill();
	} 
	
	function clearForm() { 
		listposition="";  
		listkpi="";  
		listbobot="";  
		$("#texens").val("1"); 
		$('#totalbobot').text(0);  
		same_pos = 0;
		totalbobot = 0;
		bobot = new Array;
		position = new Array;
		kpi = new Array;
		idx = 0; 
		brs = 0;
		$('#cssTable tbody').empty();
	}
	
	function addnewtemplate(templateid="",tipe="",event=""){ 
		$('.loading').show();
		tipe_login = 1;
		if (tipe=="Salesman")
		{
			tipe_login = 1;
		}
		else
		{ 
			tipe_login = 0;
		}
		
		clearForm();
		
		$('#dtStartDate').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		});
		
		$("#tbodyBarangCampaign").html(''); 
		$(".colDelete").show(); 
		
		if (event=="Add")
		{
			$('#tbodyBarangCampaign').html('<tr><td colspan="4"><center>Klik ADD ROW untuk isi data</center></td></tr>');
			document.getElementById('txt_template_name').value = "";
			document.getElementById("IsActive").checked = true;
			document.getElementById('dtStartDate').value = date("m/d/Y"); 
			
			$('#IsActive').prop('disabled', false); 
			$('#dtStartDate').prop('disabled', false); 
			$('#cboKpiCategory').prop('disabled', false); 
			$('#txt_template_name').prop('disabled', false); 
			document.getElementById('addrows').style.visibility = 'visible';
			document.getElementById('btnSubmit').style.visibility = 'visible';   
			$("#event_submit").val("1"); 
			$('#lblTemplate').text("Add New Template");  
			$("#btnSubmit").val("SAVE");
		}
		else if (event=="View")
		{
			$(".colDelete").hide(); 
			$('#IsActive').prop('disabled', true); 
			$('#dtStartDate').prop('disabled', true); 
			$('#txt_template_name').prop('disabled', true); 
			$('#cboKpiCategory').prop('disabled', true); 
			document.getElementById('addrows').style.visibility = 'hidden'; 
			document.getElementById('btnSubmit').style.visibility = 'hidden';   
			$('#lblTemplate').text("View Template");  
		// $('.loading').hide();
		} 
		else 
		{ 
			$('#IsActive').prop('disabled', false); 
			$('#dtStartDate').prop('disabled', false); 
			$('#cboKpiCategory').prop('disabled', false); 
			$('#txt_template_name').prop('disabled', false); 
			document.getElementById('addrows').style.visibility = 'visible';
			document.getElementById('btnSubmit').style.visibility = 'visible';  
			
			$("#event_submit").val("0");  
			$('#lblTemplate').text("Update Template");  
			$("#btnSubmit").val("UPDATE");
		}
		
		
		
		if (templateid!="")
		{
			$.ajax({ 
				type: 'GET', 
				url: '<?php echo $this->API_URL."/MasterTemplateKPI/Master_Template_Target_KPI_AmbilList?api=APITES";?>&jenis='+tipe_login+'&template_id='+templateid,  
				dataType: 'json',
				success: function (data){ 
					if(data.length>0){   
						document.getElementById('txt_template_id').value = data[0].template_id;
						document.getElementById('txt_template_name').value = data[0].template_name;
						$('#cboKpiCategory').empty(); 
						$.ajax({ 
							type: 'GET', 
							url: '<?php echo $this->API_URL."/MasterTemplateKPI/Master_Template_KPICategory_AmbilList?api=APITES";?>&jenis='+tipe_login, 
							dataType: 'json',
							success: function (datas){ 
								if(datas.length>0){ 
									for (var i = 0; i < datas.length; i++) {  
										$("#cboKpiCategory").append(new Option(datas[i].KPICategoryName, datas[i].KPICategory)); 
									} 
									document.getElementById('cboKpiCategory').value = data[0].kpi_category_id; 
								} 
							}
						});
						
						$.ajax({
							type: 'GET', 
							url: '<?php echo $this->API_URL."/MasterTemplateKPI/Master_Template_Target_KPI_AmbilList_Detail?api=APITES";?>&template_id='+templateid, 
							dataType: 'json',
							success: function (datas){ 
								if(datas.length>0){ 
									for (var i = 0; i < datas.length; i++) {   
										addRow(data[0].kpi_category_id,datas[i].kpi_bobot,datas[i].kpi_code,datas[i].kpi_position,event);  
									}   
									$('.loading').hide();
								} 
							}
						});  
						
						
						document.getElementById('dtStartDate').value = date("m/d/y",strtotime(data[0].start_date)); 
						
						if (data[0].is_active==1)
						{
							document.getElementById("IsActive").checked = true;
						}
						else
						{
							document.getElementById("IsActive").checked = false;
						}
					} 
				}
			}); 
		}
		else
		{  
			$.ajax({ 
				type: 'GET', 
				url: '<?php echo $this->API_URL."/MasterTemplateKPI/AutoNumberTemplateKPI?api=APITES";?>', 
				dataType: 'json',
				success: function (data){ 
					if(data.length>0){   
						document.getElementById('txt_template_id').value = data[0].TemplateID;
					} 
				}
			}); 
			
			$('#cboKpiCategory').empty(); 
			$.ajax({ 
				type: 'GET', 
				url: '<?php echo $this->API_URL."/MasterTemplateKPI/Master_Template_KPICategory_AmbilList?api=APITES";?>&jenis='+tipe_login, 
				dataType: 'json',
				success: function (data){ 
					if(data.length>0){ 
						for (var i = 0; i < data.length; i++) {  
							$("#cboKpiCategory").append(new Option(data[i].KPICategoryName, data[i].KPICategory)); 
						} 
						$('.loading').hide();
					} 
				}
			});   
		}   
		
		
	} 
	var addRow = function($kpicategory="",$bobot="",$kpicode="",$kpiposition="",$event="") 
	{   
		$('.loading').show();
		if($('.rowbrg').length==0){
			$("#tbodyBarangCampaign").html(''); 
		}
		var kpicategorys = "";
		if ($kpicategory=="")
		{
			kpicategorys = document.getElementById("cboKpiCategory").value;   
		}
		else
		{
			kpicategorys = $kpicategory;   
		}
		
		idx += 1; 
		brs += 1;  
		var i = idx; 
		
		if ($bobot=="")
		{
			bobot[i] = 0;
		}
		else
		{
			bobot[i] = $bobot;
		}
		
		if ($kpiposition=="")
		{
			position[i] = brs;
		}
		else
		{
			position[i] = $kpiposition;
		} 
		
		if ($event=="View")
		{
			var tr = "<tr class='rowbrg'  id='kolumbrg" + i + "'>";
			var td1 = "<td id='td_pos" + i + "'><input type='number' class='form-control' id='KpiPositon" + i + "' min='1' value='"+position[i]+"' readonly required ></td>";   
			var td2 = "<td id='td_kpi" + i + "'><select class='form-control' disabled onchange='cbo_KPI_onchange(this," + i + ",1)' id='cbo_KPI" + i + "'  ></td>";   
			var td3 = "<td id='td_bobot" + i + "'><input type='number' class='form-control' id='KpiBobot" + i + "' step='any' readonly min='1' max='100' oninput='limitDecimalPlaces(event, 2)'' value='"+bobot[i]+"' required></td>";       
			var tddel = "</tr>";
			$("#tbodyBarangCampaign").append(tr + td1 +td2 +td3 + tddel); 
		}
		else
		{
			var tr = "<tr class='rowbrg'  id='kolumbrg" + i + "'>";
			var td1 = "<td id='td_pos" + i + "'><input type='number' class='form-control' id='KpiPositon" + i + "' min='1' value='"+position[i]+"' required ></td>";   
			var td2 = "<td id='td_kpi" + i + "'><select class='form-control' onchange='cbo_KPI_onchange(this," + i + ")' id='cbo_KPI" + i + "'  ></td>";   
			var td3 = "<td id='td_bobot" + i + "'><input type='number' class='form-control' id='KpiBobot" + i + "' step='any' min='1' max='100' oninput='limitDecimalPlaces(event, 2)'' value='"+bobot[i]+"'  required ></td>";       
			var tddel = "<td class='colDelete'>" +
			"<button type='button' class='btn btn-sm btn-danger-dark' onclick='RemoveBarang(\"" + i + "\")' id='btnRemoveDt" + i + "' class='isdisabled btnRemoveDt" + i + "'><i class='glyphicon glyphicon-trash'></i></button>" +
			"</td></tr>";
			$("#tbodyBarangCampaign").append(tr + td1 +td2 +td3 + tddel); 
		}
		
		
		
		$('#cbo_KPI'+i).empty(); 
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo $this->API_URL."/MasterTemplateKPI/Master_Template_KPI_AmbilList?api=APITES";?>&kpicategory='+kpicategorys,
			dataType: 'json',
			success: function (data){  
					$('.loading').show();
				if(data.length>0){ 
				
					for (var x = 0; x < data.length; x++) {  
						$('#cbo_KPI'+i).append(new Option(data[x].KPIName, data[x].KPICode)); 
					} 
					if ($kpicode=="")
					{
						kpi[i] = $('#cbo_KPI'+i).find(":selected").val();
					}
					else
					{
						kpi[i] = $kpicode;
						document.getElementById('cbo_KPI'+i).value = $kpicode; 
					} 
					if ($event!="View" & $event!="Edit")
					{ 
						listkpi_fill(); 
					}
					$("#texens").val("0"); 
				} 
				$('.loading').hide();
			}
		});  
		
		const kpi_pos_txt = document.getElementById('KpiPositon'+i); 
		
		const inputHandler2 = function(e) {   
			var current_pos = e.target.value;
			if (current_pos=="")
			{
				position[i] = 0;
			}
			else
			{
				position[i] = current_pos;
			}    
			hitungtotal();
			checkpostion();
			listkpi_fill();
		}
		
		kpi_pos_txt.addEventListener('input', inputHandler2);
		kpi_pos_txt.addEventListener('propertychange', inputHandler2);
		
		const source = document.getElementById('KpiBobot'+i); 
		
		const inputHandler = function(e) {  
			if (e.target.value=="")
			{
				bobot[i] = 0;
			}
			else
			{
				bobot[i] = e.target.value;
			}
			
			hitungtotal();
			checkpostion();
			listkpi_fill();
		}
		
		source.addEventListener('input', inputHandler);
		source.addEventListener('propertychange', inputHandler);
		checkpostion();
		hitungtotal();
		
		$('.loading').hide();
	}
	
	function limitDecimalPlaces(e, count) {
		if (e.target.value.indexOf('.') == -1) { return; }
		if ((e.target.value.length - e.target.value.indexOf('.')) > count) {
			e.target.value = parseFloat(e.target.value).toFixed(count);
		}
	}
	
	var RemoveBarang = function(i) {
		$("#kolumbrg" + i).remove();
		brs = brs - 1;    
		bobot[i] = 0 ;
		position[i] = 0;  
		kpi[i] = "";  
		hitungtotal();
		checkpostion();
		listkpi_fill();
		
		if($('.rowbrg').length==0){
			$('#tbodyBarangCampaign').html('<tr><td colspan="4"><center>Klik ADD ROW untuk isi data</center></td></tr>');
		}
	}
	
	var listkpi_fill = function()
	{ 
		listkpi = ""; 
		for (var x = 0; x < idx; x++) {  
			var curr_idx_s = x+1;       
			if (kpi[curr_idx_s]!="")
			{
				if (listkpi=="")
				{
					listkpi = kpi[curr_idx_s];
				}
				else
				{
					listkpi += ";;" + kpi[curr_idx_s];
				}
			}
		} 
		$("#listkpi").val(listkpi);
		
		for (var y = 0; y < idx; y++) { 
			var curr_idx_s = y+1; 
			$('#td_kpi'+curr_idx_s).css("background-color", 'white'); 
		}
		
		for (var y = 0; y < idx; y++) { 
			var curr_idx_s = y+1;  
			for (var x = 0; x < idx; x++) {  
				var curr_idx = x+1;
				if (curr_idx_s!=curr_idx)
				{ 
					if (kpi[curr_idx_s]!=0)
					{
						if (kpi[curr_idx_s]==kpi[curr_idx])
						{ 
							$("#texens").val("3"); 
							$('#td_kpi'+curr_idx_s).css("background-color", 'red');
							$('#td_kpi'+curr_idx).css("background-color", 'red'); 
						} 
					} 
				}
			}  
		} 
	}
	
	var hitungtotal = function()
	{ 
		listbobot = "";
		totalbobot = 0;
		for (var x = 0; x < idx; x++) { 
			var curr_idx_s = x+1;  
			//number = Math.round(parseFloat(bobot[curr_idx_s]) * 100) / 100;
			number = parseFloat(bobot[curr_idx_s]);
			totalbobot = totalbobot + number; //parseInt(bobot[curr_idx_s]);        
			if (bobot[curr_idx_s]!=0)
			{
				if (listbobot=="")
				{
					listbobot = bobot[curr_idx_s];
				}
				else
				{
					listbobot += ";;" + bobot[curr_idx_s];
				}
			}
		} 
		
		$("#listbobot").val(listbobot); 
		if (totalbobot>100)
		{
			$('#totalbobot').css("color", 'red');
			$("#texens").val("1"); 
			totalbobot = parseFloat(totalbobot).toFixed(2);
		}
		else if (totalbobot<100)
		{
			$('#totalbobot').css("color", 'orange');
			$("#texens").val("1"); 
			totalbobot = parseFloat(totalbobot).toFixed(2);
		}
		else
		{
			$('#totalbobot').css("color", 'green');
			$("#texens").val("0"); 
			totalbobot = parseFloat(totalbobot).toFixed(0);
		}
		
		$('#totalbobot').text(totalbobot); 
	}
	
	var checkpostion = function()
	{   
		listposition = "";
		for (var y = 0; y < idx; y++) { 
			var curr_idx_s = y+1; 
			$('#td_pos'+curr_idx_s).css("background-color", 'white'); 
			if (position[curr_idx_s]!=0)
			{
				if (listposition=="")
				{
					listposition = position[curr_idx_s];
				}
				else
				{
					listposition += ";;" + position[curr_idx_s];
				}
			}
		}
		$("#listposition").val(listposition);  
		for (var y = 0; y < idx; y++) { 
			var curr_idx_s = y+1;  
			for (var x = 0; x < idx; x++) {  
				var curr_idx = x+1;
				if (curr_idx_s!=curr_idx)
				{ 
					if (position[curr_idx_s]!=0)
					{
						if (position[curr_idx_s]==position[curr_idx])
						{ 
							$("#texens").val("2"); 
							$('#td_pos'+curr_idx_s).css("background-color", 'red'); 
							$('#td_pos'+curr_idx).css("background-color", 'red'); 
						} 
					} 
				}
			}  
		} 
	}
	
	$(function() {
		$("#form_save").on('submit', function(e) {
			e.preventDefault();
			var form_save = $(this);
			$.ajax({
				url: form_save.attr('action'),
				type: 'post',
				data: form_save.serialize(),
				success: function(response){
					alert(response.message);
					if(response.status == 'Success') {
						$('#insert_new').modal('toggle');  
						location.reload();
					}
				}
			});
		});
	});
	
	function delete_template(template_id){
		if (confirm("Apakah anda yakin ingin menghapus Template ini?") == true) { 
			var data ='&templateid='+template_id;  
			$.ajax({
				type      : 'POST', 
				url       : '<?php echo site_url('MasterTemplateKPI/deletetemplate') ?>',
				data      : data,
				success   : function(data) { 
					var data = data.trim();
					if(data=='1'){ 
						location.reload(); 
					} 
					return false;
					
				}
				
			})
		}
	}
	
</script>  


