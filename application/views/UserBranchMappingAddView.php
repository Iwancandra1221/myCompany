<script>
	var branches=<?php echo json_encode($branches); ?>;
	var branchID;
	var savedMapping = "";

	var get_users = function(branch_id='', workgroup_id='')
	{
		$(".loading").show();
		var csrf_bit = $("input[name=csrf_bit]").val();
		$.post("<?php echo site_url('UserControllers/GetByBranch'); ?>", {
			branch_id:branch_id,
			workgroup_id:workgroup_id,
			csrf_bit:csrf_bit
		}, function(data){
			if (data.error != undefined)
				$("#Employee").html('<option value="">--- PILIH USER ---</option>');
			else
			{
				var x = '<option value="">--- PILIH USER ---</option>';
				for(var i=0; i<data.length; i++)
				{	
					x = x + '<option value="'+data[i].UserEmail+'">'+data[i].UserName+' ( '+data[i].UserEmail+' ) '+'</option>';
				}
				$("#Employee").html(x);
			}
			$(".loading").hide();
		}
		,'json',errorAjax);		
	}

	var branch_selected = function(branch_id='') {
		if (branch_id!="")
		{
			$(".loading").show();
			var csrf_bit=$("input[name=csrf_bit]").val();

			$.ajax({
				type		: 'POST',
				url			: "<?php echo(site_url('Master/GetGroupByBranch')); ?>",
				data 		: { BranchID:branch_id },
				dataType	: 'json',
				csrf_bit	: csrf_bit,
				success		: function(data){

					var grupKerja = data.data;
					var jml_record = grupKerja.length;
					alert(jml_record);
					
					var str = "";

					for(var x=0; x<jml_record; x++) {
						str += "<option value='"+grupKerja[x].GroupID+"'>"+grupKerja[x].GroupName+"</option>";
					}
					$("#WorkgroupID").html(str);

					$(".loading").hide();
				}
			});
		} else {
			$("#Employee").html('<option value="">--- PILIH USER ---</option>');
			$("#WorkgroupID").html('<option value="">--- PILIH WORKGROUP ---</option>');
		}
	}

	$(document).ready(function(){
		<?php if(isset($error) && $error != '')	echo 'alert("'.$error.'");';?>
		//$("#btnMapAllCities").hide();
		branchID = $("#BranchID").val();
		if (branchID != "")
		{
			branch_selected(branchID);
		}

		$("#btnAddDetail").click(function(){
			var add = $("#tblSample").find("tr").clone();
			$("#tblDetail tbody").append(add);
		});

		$("#tblDetail").on('click','.btnDelete',function(){
			$(this).parent().parent().remove();
		});

		$("#tblDetail").on('change','.branch',function()
		{
			var val=$(this).val();
			var _this=$(this);
			for(var r in branches)
			{
				if (branches[r].branch_id==val)
				{
					var o=branches[r];
					var tr=$(this).parent().parent();
					$(tr).find('.BranchId').val(o.branch_id);
					$(tr).find('.BranchName').val(o.branch_name);
					break;
				}
			}
		});

		$('#BranchID').on('change', function() {
			branchID = this.value;
			//alert(branchID);
			workgroupID = $("#WorkgroupID").val();
			branch_selected(branchID);
			get_users(branchID, workgroupID)
		});

		$('#WorkgroupID').on('change', function() {
			branchID = $("#BranchID").val();
			workgroupID = this.value;
			get_users(branchID, workgroupID);
		});

		/*$('#Employee').on('change', function() {
			var employee_id = this.value;
			savedMapping = "";
			//alert(branch_id);
			if (employee_id!="")
			{
				$("#btnDeleteAllMap").hide();
				$("#mappedWorkgroups").html("");
				$(".loading").show();
				var csrf_bit = $("input[name=csrf_bit]").val();
				$.post("<?php echo site_url('UserWorkgroupMapping/GetWorkgroupList'); ?>", {
					user_email:employee_id,
					csrf_bit:csrf_bit
				}, function(data){
					if (data.error != undefined)
					{
					}
					else
					{
						$("#btnDeleteAllMap").show();
						savedMapping = data;

						x = '<h3>MAPPING TERSIMPAN</h3>';
						x = x + '<div class="trdetail">';
						x = x + '<table class="dataTable" id="tblDetailMapped" style="width:50%;" align="left"><thead>';
						x = x + '<tr bgcolor="#6699ff">';
						x = x + '<th width="5%">No</th><th width="10%">ID Workgroup</th><th width="35%">Nama Workgroup</th></tr>';
						x = x + '</thead><tbody>';

						for(var i=0; i<data.length; i++)
						{	
							var j = i+1;
							x = x+'<tr>';
							x = x+'<td style="text-align:center;">'+j+'</td>';
							x = x+'<td bgcolor="#aeeaea" style="text-align:center;">'+data[i].workgroup_id+'</td>';
							x = x+'<td>'+data[i].workgroup_name+'</td>';
							x = x+'</tr>';
						}
						x = x + '</tbody></table></div>';
							$("#mappedWorkgroups").html(x);
					}
					$(".loading").hide();
				}
				,'json',errorAjax);		

			} else {
			}
		});

		$('#btnMapAllWorkgroups').click(function()
		{
			var allMapped = true;

			for(var r in workgroup)
			{
				var exists = false;
				var o=workgroup[r];
				//alert(o.city_id);
				for(var i=0; i<savedMapping.length; i++)
				{	
					if (exists==false && savedMapping[i].workgroup_id==o.workgroup_id)
					{
						exists=true;
					}
				}
				if (exists==false)
				{
					var add = $("#tblSample").find("tr").clone();
					$(add).find('.workgroup').val(o.workgroup_id);
					$(add).find('.WorkgroupId').val(o.workgroup_id);
					$(add).find('.WorkgroupName').val(o.workgroup_name);
					$("#tblDetail tbody").append(add);
					allMapped = false;
				}
			}
			if (allMapped)
				alert("Tidak Ada Workgroup untuk Dimapping. Semua Workgroup Sudah dimapping");
			else 
				alert("Tekan Tombol SIMPAN untuk Menyelesaikan Proses SIMPAN");
		});

		$('#btnDeleteAllMap').click(function()
		{
			alert("Under Construction");
		});*/
	});	
</script>
<style>
	#btnSubmit, #btnMapAllBranches, #btnDeleteAllMap, #btnCancel {
		border:1px solid #ccc;
		border-radius:10px;
		background-color:#ccc;
		color: #000;
	}
	#btnSubmit:hover, #btnMapAllBranches:hover, #btnDeleteAllMap:hover, #btnCancel:hover {
		cursor:pointer;
		background-color:yellow;
	}
	
</style>
<div class="wrapper">
<div class="form_title">TAMBAH MAPPING USER-CABANG</div>
<?php echo form_open(); ?>
<div class="row">
	<div class="col-6 col-m-0"></div>
	<div class="col-1 col-m-2"><a href="<?php echo site_url('UserBranchMapping'); ?>"><div class="btn btnCancel" id="btnCancel">Batal</div></a></div>
	<div class="col-1 col-m-2"><div class="btn btnSubmit" id="btnSubmit" name="btnSubmit" onClick="document.forms[0].submit();">Simpan</div></div>
	<div class="col-2 col-m-4"><div class="btn btnFilter" id="btnMapAllBranches" name="btnMapAllBranches" style="width:250px !important;">Mapping Ke Semua Cabang</div></div>
	<div class="col-2 col-m-4"><div class="btn btnDelete" id="btnDeleteAllMap" name="btnDeleteAllMap" style="width:250px !important;">Hapus Semua Mapping</div></div>
</div>
<div class="clearfix"></div>
<div style="padding:10px;background-color:#ccc;">
	<div class="row">
		<div class="col-4 col-m-4" style="text-align:right;">Cabang</div>
		<div class="col-8 col-m-8">
			<select name="BranchID" id="BranchID">
				<option value=''>--- PILIH CABANG ---</option>
			<?php
			for($i=0;$i<count($branches);$i++) {
				echo("<option value='".$branches[$i]["branch_id"]."'>".$branches[$i]["branch_name"]."</option>");
			}
			?>
			</select>
		</div>
	</div>
	<div class="row">
		<div class="col-4 col-m-4" style="text-align:right;">Workgroup</div>
		<div class="col-8 col-m-8">
			<select name="WorkgroupID" id="WorkgroupID">
				<option value=''>--- PILIH WORKGROUP ---</option>
			</select>
		</div>
	</div>
	<div class="row">
		<div class="col-4 col-m-4" style="text-align:right;">Nama User</div>
		<div class="col-8 col-m-8">
			<select name="Employee" id="Employee">
				<option value=''>--- PILIH USER ---</option>
			</select>
		</div>
	</div>
</div>
<div class="clearfix" style="height:25px;"></div>
<div>
	<div class="clearfix"></div>
	<div id="mappedCities"></div>
	<div class="clearfix"></div>
	<h3>TAMBAH CABANG</h3>
	<div class="trdetail">
		<table class="dataTable" id="tblDetail">
			<thead>
				<tr>
					<th width="20%"><button type="button" id="btnAddDetail">Tambah Cabang</button></th>
					<th width="30%">Cabang</th>				
					<th width="25%">ID Cabang</th>				
					<th width="25%">Nama Cabang</th>
				</tr>
			</thead>
			<tbody id="tblDetailBody">
			</tbody>
		</table>
	</div>

	<div class="clearfix"></div>	
</div>

<?php echo form_close(); ?>

<table id="tblSample" style="display:none;">
	<tbody>
		<tr>
			<td align="center" style=""><button class="btnDelete"><i class="fa fa-times btnRed"></i></button></td>
			<td>
				<?php
				$arr = array(''=>"Choose Branch");
				for($i=0;$i<count($branches);$i++)
				{
					$arr[$branches[$i]["branch_id"]] =$branches[$i]["branch_id"]."-".$branches[$i]["branch_name"];
				}
				$attr = 'id="branch" ';
				echo BuildInput('dropdown','branch[]','style="width:90%" class="branch"','',$arr,$attr);
				?>
			</td>	
			<td>
				<?php
				echo BuildInput('text','BranchId[]',array('style'=>"width:90% !important;", 'readonly'=>"true", 'class'=>"BranchId"));
				?>
			</td>	
			<td>
				<?php
				echo BuildInput('text','BranchName[]',array('style'=>"width:90% !important;",'readonly'=>"true", 'class'=>"BranchName"));
				?>
			</td>		
		</tr>
	</tbody>
</table>
</div>