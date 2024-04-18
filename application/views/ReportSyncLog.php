<?php 
	if(!empty($_POST['dp1']) && !empty($_POST['dp2'])){
		$dp1=$_POST['dp1'];
		$dp2=$_POST['dp2'];
	}else{
		$dp1="";
		$dp2="";
	}
?>

<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
	<form method="post" action="" id="f-log">
		<table class="table">
			<tr>
				<td>Branch </td>
				<td>
					<select name='Cabang' id="Cabang" class="form-control">
						<option value="">Select <?php echo $filter_cabang ?></option>
						<?php 
							for($i=0;$i<count($DataBranch);$i++){
								$s = '';
								if($filter_cabang==$DataBranch[$i]["Kd_Lokasi"]){
									$s = "selected"; 
								}
								?>
								<option value="<?php echo $DataBranch[$i]['Kd_Lokasi']; ?>" <?php echo $s ?>><?php echo $DataBranch[$i]["Nm_Lokasi"].' - '.$DataBranch[$i]["Nm_Lokasi"]; ?></option>
							<?php
							}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Type of Config </td>
				<td>
					<select name="type_of_config" id="type_of_config" onchange="trk_name()" class="form-control">
						<option value="">Select All</option>
							<?php 
								for($i=0;$i<count($typeconfig);$i++){
							?>
									<option value="<?php echo $typeconfig[$i]['ConfigType']; ?>" <?php if(!empty($_POST['type_of_config']) && $_POST['type_of_config']==$typeconfig[$i]["ConfigType"]){ echo "selected";} ?>><?php echo $typeconfig[$i]["ConfigType"] ?></option>
							<?php
								}
							?>
						</select>
				</td>
			</tr>
			<tr>
				<td>Config Name </td>
				<td>
					<select name="config_name" id="config_name" class="form-control" width="100px">
						<option value="">Select All</option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="20%"></td>
				<td><button type="submit" class="btn btn-dark btn-primary">Search</button></td>
			</tr>
		</table>
	</form>
		
	</table>
	<table id="tb-log" class="table table-striped table-bordered" cellspacing="0" width="100%">
		<caption>Report Sync Log</caption>
		<thead>
			<tr>
				<th width="20px" align="center">No</th>
				<th>Type of Config</th>
				<th>Name</th>
				<th>Value</th>
				<!--th>Level</th>
				<th>Active</th-->
				<th>Branch Id</th>
				<th>Modified By</th>
				<th>Modified Date</th>
				<th>Aksi</th>
			</tr>
		</thead>
		
	</table>

</div>
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<form id="f-edit-modal">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="editModalLabel"></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
				 
						<input type="hidden" name="ConfigName">
						 <input type="hidden" name="ModifiedBy" value="<?=$ModifiedBy?>">
						<div class="form-group">
							<label>Config Value</label>
							<input type="text" name="ConfigValue" class="datepicker form-control" autocomplete="off">
						</div>
					
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<input type="button" name="btn-submit" class="btn btn-primary" value="Save">
				</div>
			</div>
		</form>
	</div>
</div>

<?php 
		
$api = 'APITES';
$url = $_SESSION["conn"]->AlamatWebService;
$svr = $_SESSION["conn"]->Server;
$db  = $_SESSION["conn"]->Database;
		
?>

<script type="text/javascript">
	var object = {};
	var formData = new FormData($("#f-log")[0]);
	formData.append("submit","filter");
	formData.forEach(function(value, key){
		object[key] = value;
	});
	TblLogData = object;

	var type_config=document.getElementById("type_of_config").value;

	if(type_config !== ''){
		trk_name()
	}
	$(document).ready(function(){
		$('#dp1').datepicker({
			format: "dd/mm/yyyy",
			autoclose: true
		});

		$('#dp2').datepicker({
			format: "dd/mm/yyyy",
			autoclose: true
		});

		tableLog = $('#tb-log').DataTable({
			searching : false,
			columnDefs: [
				{ targets: 'no-sort', orderable: false },
				{ targets: 'col-hide', visible: false }
			],
			columns: [
				{ "data": "NO" },
				{ "data": "ConfigType" },
				{ "data": "ConfigName" },
				{ "data": "ConfigValue" },
				{ "data": "BranchId" },
				{ "data": "ModifiedBy" },
				{ "data": "ModifiedDate" },
				{ "data": "aksi" },
			],
			order: [[6, 'desc']],
			dom: '<"top"l>rt<"bottom"ip><"clear">',
			processing: true,
			serverSide: true,
			ajax: {
				url: "<?=base_url()?>ReportLog/SyncLog",
				type: "POST",
				datatype: "json",
				data: function (d) {
					return $.extend(d,TblLogData);
				}
			},
			oLanguage: {
				sLengthMenu: "Menampilkan _MENU_ Data per halaman",
				sZeroRecords: "Maaf, Data tidak ada",
				sInfo: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
				sInfoEmpty: "Menampilkan 0 s/d 0 dari 0 data",
				sSearch: "",
				sInfoFiltered: "",
				oPaginate: {
					sPrevious: "Sebelumnya",
					sNext: "Berikutnya"
				}
			},
		});

	});
	function filterTblLog(){
		var object = {};
		var formData = new FormData($("#f-log")[0]);
		formData.append("submit","filter");
		formData.forEach(function(value, key){
			object[key] = value;
		});
		TblmasterserviceData = object;
		$('#tb-log').DataTable().ajax.reload(null, true);
	}

	$("#f-edit-modal input[name='btn-submit']").click(function(){
		var ConfigValue = $("#f-edit-modal input[name='ConfigValue']").val();

		if(ConfigValue==''){
			alert('Config Value belum diisi');
			return true;
		}
		var formData = new FormData($("#f-edit-modal")[0]);
		$.ajax({
			url:'<?php echo $url.API_BKT."/ReportLog/SyncLog?api=".urlencode($api)."&svr=".urlencode($svr)."&db=".urlencode($db)?>',
			method:'POST',
			data: formData,
			cache:false,
			contentType: false,
			processData: false,
			success:function(str){
				var obj = JSON.parse(str);
				alert(obj.msg);
				window.location.href="<?=base_url()?>ReportLog/SyncLog";
			}
		});  
	});
	function edit(configName){
		$("#editModalLabel").text(configName);
		$("#f-edit-modal input[name='ConfigName']").val(configName);
		$("#editModal").modal('show');

	}
	function trk_name(){

		var type_config=document.getElementById("type_of_config").value;

		$.ajax({
			url:'<?php echo $url.API_BKT."/MasterTypeConfig/GetListTypeConfigName?api=".urlencode($api)."&svr=".urlencode($svr)."&db=".urlencode($db)."&ConfigType="; ?>'+type_config,
			method:'GET',
			type: 'json',
			cache:false,
			success:function(data_json){ 

				var response = JSON.parse(data_json);
				var html='<option value="">Select All</option>';
				if (response.result='sukses'){

					let result = data_json.replace('{"result":"sukses","data":', '');
					let result2 = result.replace(',"error":""}', '');

					var response = JSON.parse(result2);

					if(response.length) {

						$.each(response, function(key,trk) {

							var selected = "";

							<?php
								if(!empty($_POST['config_name'])){
							?>
									var datapost="<?php echo $_POST['config_name']; ?>";

									if(datapost==trk.ConfigName){
										selected = "selected";
									}

							<?php
								}
							?>

							html += '<option value="'+trk.ConfigName+'" '+selected+'>'+trk.ConfigName+'</option>';

						});
					}

					$("#config_name").html(html);
				}else{
					alert("Data Tidak Ditemukan!!!")
				}

			} 

		});
	}

</script>