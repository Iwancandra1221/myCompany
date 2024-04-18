<script>
	var mode='';
	var oTable;
	var employeeList=<?php echo json_encode($EmployeeList); ?>;

	var isiDataTable = function(flag='ALL', branch='ALL', workgroup='ALL', userDivision='ALL', active='ALL'){
		oTable = $("#tblData").dataTable({
			'aoColumns':[
			{'sWidth' : '15%', 'sClass': 'center'},
			{'sWidth' : '25%'},
			{'sWidth' : '25%', 'sClass': 'left'},
			{'sWidth' : '20%'},
			{'sWidth' : '10%'}
			],
			'aaSorting' : [[0,'asc']],
			'bProcessing' : true,
			'bServerSide' : true,
			'destroy'	  : true,
			'sAjaxSource' : site_url+'DatasourceUser/User/'+flag+'/'+branch+'/'+workgroup+'/'+userDivision+'/'+active,
			'iDisplayLength': 50,
			'aoColumnDefs' : 
			[
				{ 
				'bSortable' : false,
				'aTargets' : [0,1]
				}
			],
			'pagingType' : 'full_numbers',
			'dom' : '<"top"if<"clearfix">><l><p><"clearfix">rt'
		});			
	}

	var isiDropdownWorkgroup = function(BranchID='ALL')
	{
		if (BranchID!="")
		{
			$(".loading").show();
			var csrf_bit = $("input[name=csrf_bit]").val();

			$.post("<?php echo site_url('Workgroup/GetByBranch'); ?>", {
				branch_id:BranchID,
				csrf_bit:csrf_bit
			}, function(data){
				if (data.error != undefined)
				{
					$("#f_Workgroup").html('<option value="ALL">ALL</option>');
				}
				else
				{
					var x = '<option value="ALL">ALL</option>';
					for(var i=0; i<data.length; i++)
					{	
						x = x + '<option value="'+data[i].workgroup_id+'">'+data[i].workgroup_name+'</option>';
					}
					$("#f_Workgroup").html(x);
				}
				$(".loading").hide();
			}
			,'json',errorAjax);	

		} else {
			$("#f_Workgroup").html('<option value="ALL">ALL</option>');
		}		
	}

	var isiDropdownUserDivision = function(WorkgroupID='ALL')
	{
		if (WorkgroupID!="")
		{
			$(".loading").show();
			var csrf_bit = $("input[name=csrf_bit]").val();

			$.post("<?php echo site_url('UserDivision/Gets'); ?>", {
				workgroup:WorkgroupID,
				csrf_bit:csrf_bit
			}, function(data){
				if (data.error != undefined)
				{
					$("#f_UserDivision").html('<option value="ALL">ALL</option><option value="UNDEFINED">UNDEFINED</option>');
				}
				else
				{
					var x = '<option value="ALL">ALL</option>';
					x = x + '<option value="UNDEFINED">UNDEFINED</option>';
					for(var i=0; i<data.length; i++)
					{
						x = x + '<option value="'+data[i].user_division_id+'">'+data[i].user_division_name+'</option>';
					}
					$("#f_UserDivision").html(x);
				}
				$(".loading").hide();
			}
			,'json',errorAjax);

		} else {
			$("#f_UserDivision").html('<option value="ALL">ALL</option><option value="UNDEFINED">UNDEFINED</option>');
		}
	}

	var btnSearchClicked = function()
	{
		var Flag = $("#f_Flag").val();
		var Branch = $("#f_Branch").val();
		var Workgroup = $("#f_Workgroup").val();
		var UserDivision = $("#f_UserDivision").val();
		var Active = $("#f_Active").val();

		$(".loading").show();
		isiDataTable(Flag, Branch, Workgroup, UserDivision, Active);
		$(".loading").hide();
	}

	$(document).ready(function(){

		var session_flag = "<?php echo((!$this->session->userdata('filter_user_flag')) ? 'ALL' : $this->session->userdata('filter_user_flag')); ?>";
		var session_branch = "<?php echo((!$this->session->userdata('filter_user_branch')) ? 'ALL' : $this->session->userdata('filter_user_branch')); ?>";
		var session_workgroup = "<?php echo((!$this->session->userdata('filter_user_workgroup')) ? 'ALL' : $this->session->userdata('filter_user_workgroup')); ?>";
		var session_userdivision = "<?php echo((!$this->session->userdata('filter_user_userdivision')) ? 'ALL' : $this->session->userdata('filter_user_userdivision')); ?>";
		var session_active = "<?php echo((!$this->session->userdata('filter_user_active')) ? 'Y' : $this->session->userdata('filter_user_active')); ?>";

		isiDataTable(session_flag, session_branch, session_workgroup, session_userdivision, session_active);

		$("#tblData").on('click','.btnDelete',function(){
			if(confirm("Are you sure to delete data '" + $(this).attr('data') + "' ?"))
			{
				var data = $(this).attr('data');
				var csrf_bit = $('input[name=csrf_bit]').val();
				$(".loading").show();
				$.post
				(
					'<?php echo site_url('UserControllers/doDelete'); ?>',
					{ 
						'data' 		: data,
						'csrf_bit'  : csrf_bit
					},
					function(res)
					{
						if(res.error != undefined) {
							alert(res.error);
						} else {
							oTable.fnStandingRedraw();
						}
						$(".loading").hide();
					},'json',errorAjax
				);
			}
		});

		$("#tblData").on('click','.btnDisable',function(){
			if(confirm("Apakah Anda Akan Menonaktifkan user '" + $(this).attr('data') + "' ?"))
			{
				var data = $(this).attr('data');
				var csrf_bit = $('input[name=csrf_bit]').val();
				$(".loading").show();
				$.post
				(
					'<?php echo site_url('UserControllers/disable'); ?>',
					{ 
						'data' 		: data,
						'csrf_bit' 	: csrf_bit
					},
					function(res)
					{
						if(res.error != undefined) {
							alert(res.error);
						} else {
							oTable.fnStandingRedraw();
							alert("User Telah DiNonAktifkan");
						}
						$(".loading").hide();
					},'json',errorAjax
				);
			}
		});

		$("#btnAdd").PopUpForm({
			target:'#PopUpForm',
			after:function(){
				mode='add';
				$("#PopUpForm #formTitle").html("<?php echo($this->lang->line('AddNewUser'));?>");
				$("#PopUpForm #UserEmail").val("");
				$("#PopUpForm #UserEmail").prop('readonly',false);
				$("#PopUpForm #UserName").val("");
				$("#PopUpForm #status").prop('checked',true);
			}
		});
		
		$("#btnCancel").click(function(){
			$(".ClosePopUp").click();
		});

		$("#btnCloseFilter").click(function(){
			$(".ClosePopUp").click();
		});

		//$("#PopUpForm form").submit
		$("#btnSubmit").click
		(	
			function(e)
			{
				e.preventDefault();
				if(confirm("Are you sure to save data?"))
				{
					$(".loading").show();
					var csrf_bit=$("input[name=csrf_bit]").val();
					$.post('<?php echo site_url('UserControllers/ajaxrequest'); ?>',
						{
							'mode':mode,
							'UserEmail':$("#UserEmail").val(),
							'UserName':$("#UserName").val(),
							'BranchID':$("#BranchID").val(),
							'WorkgroupID':$("#WorkgroupID").val(),
							'DivisionID':$("#UserDivisionID").val(),
							'IsActive':$("#status").prop('checked'),
							'csrf_bit':csrf_bit
						},
						function(data)
						{
							if(data.error_arr != undefined)
							{
								var msg='';
								for(var key in data.error_arr)
								{
									if(msg != '')
										msg += '\n';
									msg	+= data.error_arr[key];
								}
								alert(msg);
							}
							else if(data.error != undefined)
							{
								alert(data.error);
							}
							else
							{
								//oTable.fnStandingRedraw();
								location.replace("<?php echo(site_url('UserControllers/Edit/"+data.success+"'));?>")
							}
							$(".loading").hide();
							$(".ClosePopUp").click();
						},
						'json',
						errorAjax
					);
				}
			}
		);

		$("#btnSubmitNew").click
		(	
			function(e)
			{
				e.preventDefault();
				if(confirm("Are you sure to save data?"))
				{
					$(".loading").show();
					var csrf_bit=$("input[name=csrf_bit]").val();
					$.post('<?php echo site_url('UserControllers/ajaxrequest'); ?>',
						{
							'mode':mode,
							'UserEmail':$("#UserEmail").val(),
							'UserName':$("#UserName").val(),
							'BranchID':$("#BranchID").val(),
							'WorkgroupID':$("#WorkgroupID").val(),
							'DivisionID':$("#UserDivisionID").val(),
							'IsActive':$("#status").prop('checked'),
							'csrf_bit':csrf_bit
						},
						function(data)
						{
							if(data.error_arr != undefined)
							{
								var msg='';
								for(var key in data.error_arr)
								{
									if(msg != '')
										msg += '\n';
									msg	+= data.error_arr[key];
								}
								alert(msg);
							}
							else if(data.error != undefined)
							{
								alert(data.error);
							}
							else
							{
								oTable.fnStandingRedraw();
								//location.replace("<?php echo(site_url('UserControllers/Edit/"+data.success+"'));?>")
							}
							$(".loading").hide();
							//$(".ClosePopUp").click();
							//$("#btnAdd").click();
							alert("Simpan Sukses");
							mode='add';
							$("#PopUpForm #formTitle").html("<?php echo($this->lang->line('AddNewUser'));?>");
							$("#PopUpForm #UserEmail").val("");
							$("#PopUpForm #UserEmail").prop('readonly',false);
							$("#PopUpForm #UserName").val("");
							$("#PopUpForm #BranchID").val("");
							$("#PopUpForm #WorkgroupID").val("");
							$("#PopUpForm #UserDivisionID").val("");
							$("#PopUpForm #status").prop('checked',true);
							$("#UserEmail").focus();
						},
						'json',
						errorAjax
					);
				}
			}
		);

		$('#UserEmail').blur(function(){
			var user_email = $("#UserEmail").val();
			if (user_email!="")
			{
				$(".loading").show();
				var csrf_bit = $("input[name=csrf_bit]").val();
				$.post("<?php echo site_url('UserControllers/Get'); ?>", {
					data:user_email,
					csrf_bit:csrf_bit
				}, function(data){
					if (data.error != undefined)
					{
						//alert(data.error);
					}
					else
					{
						alert("User Email Sudah Terdaftar");
						$("#UserEmail").val("");
						$("#UserEmail").focus();
					}
					$(".loading").hide();
				}
				,'json',errorAjax);		
			}
		});

		$('#EmployeeID').on('change', function() {
			var EmployeeID = this.value;
			//alert(EmployeeID);
			var Cabang = "";
			var Grup = "";

			if (EmployeeID!="") {
				for(var e in employeeList)
				{
					if (employeeList[e].employee_id==EmployeeID)
					{
						Cabang = employeeList[e].BranchID;
						Grup = employeeList[e].workgroup_id;
						//alert(Cabang);
						
						$("#BranchID").val(Cabang);
						$("#WorkgroupID").val(Grup);

						if (Grup!="")
						{
							$(".loading").show();
							var csrf_bit = $("input[name=csrf_bit]").val();
							$.post("<?php echo site_url('UserDivision/Gets'); ?>", {
								workgroup:Grup,
								csrf_bit:csrf_bit
							}, function(data){
								if (data.error != undefined)
								{
									//alert(data.error);
								}
								else
								{
									var x = '<option value="">--- PILIH DIVISI ---</option>';	
									for(var i=0; i<data.length; i++)
									{	
										x = x + '<option value="'+data[i].user_division_id+'">'+data[i].user_division_name+'</option>';
									}
									$("#UserDivisionID").html(x);
								}
								$(".loading").hide();
							}
							,'json',errorAjax);			
						} else {
							$("#UserDivisionID").html('<option value="">--- PILIH DIVISI ---</option>');
						}

						
						break;
					}
				}				
			}
		});


		$('#BranchID').on('change', function() {
			var branch_id = this.value;
			if (branch_id!="")
			{
				$(".loading").show();
				var csrf_bit = $("input[name=csrf_bit]").val();
				$.post("<?php echo site_url('Workgroup/GetByBranch'); ?>", {
					branch_id:branch_id,
					csrf_bit:csrf_bit
				}, function(data){
					if (data.error != undefined)
					{
						//alert(data.error);
					}
					else
					{
						var x = '<option value="">--- PILIH Workgroup ---</option>';
						for(var i=0; i<data.length; i++)
						{	
							x = x + '<option value="'+data[i].workgroup_id+'">'+data[i].workgroup_name+'</option>';
						}
						$("#WorkgroupID").html(x);
					}
					$(".loading").hide();
				}
				,'json',errorAjax);		
				$("#UserDivisionID").html('<option value="">--- PILIH DIVISI ---</option>');	
			} else {
				$("#WorkgroupID").html('<option value="">--- PILIH WORKGROUP ---</option>');
				$("#UserDivisionID").html('<option value="">--- PILIH DIVISI ---</option>');
			}
		});

		$('#WorkgroupID').on('change', function() {
			var workgroup_id = this.value;
			if (workgroup_id!="")
			{
				$(".loading").show();
				var csrf_bit = $("input[name=csrf_bit]").val();
				$.post("<?php echo site_url('UserDivision/Gets'); ?>", {
					workgroup:workgroup_id,
					csrf_bit:csrf_bit
				}, function(data){
					if (data.error != undefined)
					{
						//alert(data.error);
					}
					else
					{
						var x = '<option value="">--- PILIH DIVISI ---</option>';	
						for(var i=0; i<data.length; i++)
						{	
							x = x + '<option value="'+data[i].user_division_id+'">'+data[i].user_division_name+'</option>';
						}
						$("#UserDivisionID").html(x);
					}
					$(".loading").hide();
				}
				,'json',errorAjax);			
			} else {
				$("#UserDivisionID").html('<option value="">--- PILIH DIVISI ---</option>');
			}

		});

		$("#btnFilter").PopUpForm({
			target:'#PopUpFormFilter',
			after:function(){}
		});

		$('#f_Branch').on('change', function() {
			var BranchID = this.value;
			isiDropdownWorkgroup(BranchID);
			btnSearchClicked();
		});

		$('#f_Workgroup').on('change', function() {
			var WorkgroupID = this.value;
			isiDropdownUserDivision(WorkgroupID);
			btnSearchClicked();
		});

		$("#btnSearch").click(function(){
			btnSearchClicked();
			$(".ClosePopUp").click();		
		});			

		$("#btnResetFilter").click(function(){
			$(".loading").show();
			isiDataTable("ALL", "ALL", "ALL", "ALL", "ALL");
			$(".loading").hide();
		});			

	});
</script>

<div class="wrapper">
	<div class="form_title">USER</div>
	<div class="row">
		<div class="col-3 col-m-0"></div>
		<div class="col-3 col-m-12"><div class="btnReset" id="btnResetFilter">Reset Filter</div></div>
		<div class="col-3 col-m-12"><div class="btnFilter" id="btnFilter">Show More Filter</div></div>
		<?php if($this->session->userdata('can_add')) { ?>
		<div class="col-3 col-m-12"><div class="btnAdd" id="btnAdd">Tambah User</div></div>
		<?php } ?>
	</div>
	<div class="clearfix"></div>

	<div class="wrapper_dataTable">
		<table id="tblData" class="display" width="100%">
			<thead>
				<tr>
					<th scope="col" style="width:15%!important;"></th>
					<th scope="col" style="width:25%!important;"><?php echo($this->lang->line('UserEmail'));?></th>
					<th scope="col" style="width:25%!important;"><?php echo($this->lang->line('UserName'));?></th>
					<th scope="col" style="width:20%!important;"><?php echo($this->lang->line('Branch'));?></th>
					<th scope="col" style="width:10%!important;"><?php echo($this->lang->line('Active'));?></th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
		
		<div class="PopUpForm" id="PopUpForm">
			<div class="overlay"></div>
			<div class="loadingItem">
				<form>
					<i class="fa fa-times ClosePopUp"></i>
					<div class="popupform_title" id="formTitle"></div>
					<div class="row">
						<div class="col-3 col-m-4" for="UserEmail"><?php echo($this->lang->line('UserEmail'));?></div>
						<div class="col-9 col-m-8">
						<?php  
							$attr = array 
							(
								'placeholder' => $this->lang->line('UserEmail'),
								'id' => 'UserEmail',
								'maxlength' => '50'
							);
							echo BuildInput('email','UserEmail',$attr);
						?>
						</div>
					</div>
					<div class="row">
						<div class="col-3 col-m-4" for="UserName"><?php echo($this->lang->line('UserName'));?></div>
						<div class="col-9 col-m-8">
						<?php  
							$attr = array 
							(
								'placeholder' => $this->lang->line('UserName'),
								'id' => 'UserName',
								'maxlength' => '100'
							);
							echo BuildInput('text','UserName',$attr);
						?>
						</div>
					</div>
					<div class="row">
						<div class="col-3 col-m-4">
							Data Karyawan
						</div>
						<div class="col-9 col-m-8">
							<select id="EmployeeID" name="EmployeeID">
								<option value="">--- PILIH KARYAWAN ---</div>
							<?php 
								foreach($EmployeeList as $el)
								{
							?>
									<option value="<?php echo($el->employee_id);?>"><?php echo($el->employee_name);?></div>
							<?php
								}
							?>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-3 col-m-4">
							<?php echo($this->lang->line('Branch'));?>
						</div>
						<div class="col-9 col-m-8">
							<select id="BranchID">
								<option value="">--- PILIH CABANG ---</div>
							<?php 
								foreach($Branches as $b)
								{
							?>
									<option value="<?php echo($b->branch_id);?>"><?php echo($b->branch_name);?></div>
							<?php
								}
							?>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-3 col-m-4">
							Workgroup
						</div>
						<div class="col-9 col-m-8">
							<select id="WorkgroupID">
								<option value="">--- PILIH WORKGROUP ---</div>
							<?php 
								foreach($Workgroups as $c)
								{
							?>
									<option value="<?php echo($c->workgroup_id);?>"><?php echo($c->workgroup_name);?></div>
							<?php
								}
							?>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-3 col-m-4">
							<?php echo($this->lang->line('Division'));?>
						</div>
						<div class="col-9 col-m-8">
							<select id="UserDivisionID">
								<option value="">--- PILIH DIVISI ---</div>
							</select>
						</div>
					</div>			
					<div class="row">
						<div class="col-3 col-m-4"><?php echo($this->lang->line('Active'));?></div>
						<div class="col-9 col-m-8" style="width:0px!important;">
							<?php
							echo BuildInput('checkbox','status',array('id'=>'status'),'1');
							?>
						</div>
					</div>
					<div class="row">			
						<div class="col-1 col-m-0"></div>
						<div class="col-3 col-m-4"><div class="btnSubmit" id="btnSubmit">Simpan</div></div>
						<div class="col-4 col-m-4"><div class="btnSubmitNew" id="btnSubmitNew">Simpan dan Tambah Baru</div></div>
						<div class="col-3 col-m-4"><div class="btnCancel" id="btnCancel">Batal</div></div>
						<div class="col-1 col-m-0"></div>
					</div>
				</form>
			</div>
		</div>

		<div class="PopUpForm" id="PopUpFormFilter">
			<div class="overlay"></div>
			<div class="loadingItem">
				<form>
					<i class="fa fa-times ClosePopUp"></i>
					<div class="popupform_title" id="formTitle">MORE FILTER</div>
					<div class="row">
						<div class="col-3 col-m-4">USER BIT/NON BIT</div>
						<div class="col-9 col-m-8">
							<select id="f_Flag" name="f_Flag">
								<option value="ALL">ALL</option>
								<option value="BIT">BIT</option>
								<option value="NONBIT">NON BIT</option>
							</select>
						</div>
					</div>			
					<div class="row">
						<div class="col-3 col-m-4">CABANG</div>
						<div class="col-9 col-m-8">
							<select id="f_Branch" name="f_Branch">
								<option value="">--- PILIH CABANG ---</option>
								<option value="ALL">ALL</option>
								<!-- <option value="UNDEFINED">UNDEFINED</option> -->
								<?php 
								foreach($Branches as $b)
								{
									echo("<option value='".$b->branch_id."'>".$b->branch_name."</option>");
								}
								?>
							</select>
						</div>
					</div>			
					<div class="row">
						<div class="col-3 col-m-4">WORKGROUP</div>
						<div class="col-9 col-m-8">
							<select id="f_Workgroup" name="f_Workgroup">
								<option value="ALL">ALL</option>
							</select>
						</div>
					</div>			
					<div class="row">
						<div class="col-3 col-m-4">DIVISI</div>
						<div class="col-9 col-m-8">
							<select id="f_UserDivision" name="f_UserDivision">
								<option value="ALL">ALL</option>
								<option value="UNDEFINED">UNDEFINED</option>
							</select>
						</div>
					</div>			
					<div class="row">
						<div class="col-3 col-m-4">AKTIF</div>
						<div class="col-9 col-m-8">
							<select id="f_Active" name="f_Flag">
								<option value="ALL">ALL</option>
								<option value="Y">AKTIF</option>
								<option value="N">TIDAK AKTIF</option>
							</select>
						</div>
					</div>			
					<div class="row">
						<div class="col-3 col-m-0"></div>
						<div class="col-3 col-m-12" align="center"><div class="btnFilter" id="btnSearch">Search</div></div>
						<div class="col-3 col-m-12" align="center"><div class="btnReset" id="btnCloseFilter">Cancel</div></div>
						<div class="col-3 col-m-0"></div>
					</div>
				</form>
			</div>
		</div>
	</div>

<?php 
	echo form_open();
	echo form_close();
?>
</div>