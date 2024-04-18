<?php
	if(empty($this->uri->segment(3))){
?>
		<div class="container" id="listBrandManager">
			<div class="title">MASTER JOBS</div>
			<br>
			<table id="table_masterjobs" class="table table-striped table-bordered" cellspacing="0" style="font-size: 12px;" summary="table">
				<thead>
					<tr>
						<th scope="col" width="2%" class='no-sort'>No</th>
						<th scope="col" width="20%">ID Jobs</th>
						<th scope="col" width="*">Function Jobs</th>
						<th scope="col" width="20%">Schedule Type</th>
						<th scope="col" width="10%">Is Active</th>
						<th scope="col" width="20%">Action</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>

		<div class="modal fade" id="view" tabindex="-1" role="dialog" aria-labelledby="viewLabel" aria-hidden="true">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
		      <div class="modal-header">
		        <h5 class="modal-title" id="viewLabel"></h5>
		      </div>

		      <div class="modal-body row" style="font-size:12px">
		      	<div class="col-12 p-0 m-0">

			      	<div class="col-md-3 col-sm-3 col-lg-2-10">
			      		ID Jobs
				      </div>
				      <div class="col-md-9 col-sm-9 col-lg-2-10">
				      	<input type="text" name="jobsid" id="jobsid" class="form-control" style="font-size:12px">
				      </div>

				      <div class="col-md-3 col-sm-3 col-lg-2-10">
			      		Description
				      </div>
				      <div class="col-md-9 col-sm-9 col-lg-2-10">
				      	<input type="text" name="job_description" id="job_description" class="form-control" style="font-size:12px">
				      </div>

				      <div class="col-md-3 col-sm-3 col-lg-2-10">
			      		Function Jobs
				      </div>
				      <div class="col-md-9 col-sm-9 col-lg-2-10">
				      	<input type="text" name="function_jobs" id="function_jobs" class="form-control" style="font-size:12px">
				      </div>

				      <div class="col-md-3 col-sm-3 col-lg-2-10">
			      		Schedule Type
				      </div>
				      <div class="col-md-9 col-sm-9 col-lg-2-10">
				      	<select name="schedule_type" id="schedule_type" class="form-control" style="font-size:12px">
				      		<option value="">
				      			Select
				      		</option>
				      	</select>
				      </div>

				      <div class="col-md-3 col-sm-3 col-lg-2-10">
			      		Jobs Priority
				      </div>
				      <div class="col-md-9 col-sm-9 col-lg-2-10">
				      	<input type="text" name="job_priority" id="job_priority" class="form-control" style="font-size:12px" onkeypress="return hanyaAngka(event)">
				      </div>

				      <div class="col-md-3 col-sm-3 col-lg-2-10">
			      		Server
				      </div>
				      <div class="col-md-9 col-sm-9 col-lg-2-10">
				      	<input type="text" name="server" id="server" class="form-control" style="font-size:12px">
				      </div>

				      <div class="col-md-3 col-sm-3 col-lg-2-10">
			      		Database
				      </div>
				      <div class="col-md-9 col-sm-9 col-lg-2-10">
				      	<input type="text" name="database" id="database" class="form-control" style="font-size:12px">
				      </div>

				      <div class="col-md-3 col-sm-3 col-lg-2-10">
			      				Active
				      </div>
				      <div class="col-md-9 col-sm-9 col-lg-2-10">
				      	<input type="checkbox" name="active" id="active" value="1">
				      </div>

				      <div class="col-md-3 col-sm-3 col-lg-2-10">
			      				Custom Query
				      </div>
				      <div class="col-md-9 col-sm-9 col-lg-2-10">
				      	<input type="checkbox" name="custom_query" id="custom_query" value="1">
				      </div>

				   </div>

		      	
		      </div>

		      <div class="modal-footer">
		        	<button type="button" id="btnsave" class="btn btn-default" title="Save" style="font-weight: normal;" onclick="save_jobs()">
		        		Save
		        	</button>
		        	<button type="button" class="btn btn-default" data-dismiss="modal" title="Close" style="font-weight: normal; display: inline;">
		        		Close
		        	</button>
		      	</div>

		    	</div>
		  	</div>
		</div>

		<script type="text/javascript">
			$(document).ready(function(){

				function load_list(){
					table_masterjobs.clear().draw();
					$.ajax({
						url: '<?php echo site_url('Jobs/GetList') ?>',
						dataType: 'json',
						success: function(data) {

							var html = '';
							for(x=0;x<data.length;x++){
								var d = [];
								d[0] = '';
								d[1] = data[x].job_id;
								d[2] = data[x].job_function;
								d[3] = data[x].job_schedule_type;
								d[4] = data[x].is_active;

								var aksi = '';

								var action = "'"+data[x].job_id+"','"+data[x].job_description+"','"+data[x].job_function+"','"+data[x].job_schedule_type+"','"+data[x].is_active+"','"+data[x].is_custom_query+"','"+data[x].job_priority+"','"+data[x].server+"','"+data[x].database+"'";

								<?php 
									if($_SESSION['can_read']==true){
								?>
										aksi += '<button class="btn btn-sm btn-default" job_id="'+data[x].job_id+'" onclick="view_masterjobs('+action+')" title="View"><i class="glyphicon glyphicon-search" aria-hidden="true"></i></button> ';
								<?php 
									}

									if($_SESSION['can_update']==true){
								?>
										aksi += '<button class="btn btn-sm btn-default" job_id="'+data[x].job_id+'" onclick="edit_masterjobs('+action+')" title="Edit"><i class="glyphicon glyphicon-pencil" aria-hidden="true"></i></button> ';
								<?php 
									}
									
									if($_SESSION['can_delete']==true){
								?>
										aksi += '<button class="btn btn-sm btn-default" job_id="'+data[x].job_id+'" onclick="delete_masterjobs('+action+')" title="Delete"><i class="glyphicon glyphicon-trash" aria-hidden="true"></i></button> ';
								<?php 
									}
									
									if($_SESSION['can_read']==true){
								?>
										aksi += '<button class="btn btn-sm btn-default" job_id="'+data[x].job_id+'" onclick="schedule_masterjobs('+action+')" title="Schedule Type"><i class="glyphicon glyphicon-calendar" aria-hidden="true"></i></button> ';
								<?php 
									}
									
									if($_SESSION['can_read']==true){
								?>
										aksi += '<button class="btn btn-sm btn-default" job_id="'+data[x].job_id+'" onclick="log_masterjobs('+action+')" title="Log"><i class="glyphicon glyphicon-list-alt" aria-hidden="true"></i></button> ';
								<?php
									}
								?>

								d[5] = aksi;
							
								table_masterjobs.row.add(d);
							}
							table_masterjobs.draw();
						}
					});
				}

				load_list();
				
			});

					table_masterjobs = $('#table_masterjobs').DataTable({
						"pageLength" : 10,
						"searching" : true,
						"autoWidth": false,
						"columnDefs": [{ targets: 'no-sort', orderable: false }],
						"dom": '<"top"f>rt<"bottom"ip>',
						"order": [[1, 'asc'], [2, 'asc'], [1, 'asc']],
						"language": {
							"paginate": {
								"previous": "<",
								"next": ">"
							}
						},
					});
					
					table_masterjobs.on('order.dt search.dt', function () {
						let i = 1;
						table_masterjobs.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
							this.data(i++);
						});
					}).draw();

					<?php 
						if($_SESSION['can_create']==true){
					?>
							$("<a href='#' class='btn btn-default' style='margin-bottom:5px' onclick='add_masterjobs()'><i class='glyphicon glyphicon-plus-sign' aria-hidden="true"></i> Create</a>").insertBefore('#table_masterjobs');
					<?php
						}
					?>



					var schedule_type = ['MONTHLY','WEEKLY','HOURLY','NOT SCHEDULE'];
					var schedule_type_value = ['MONTHLY','WEEKLY','HOURLY','NONE'];

					function add_masterjobs(){
						$('#view').modal('show');
						document.getElementById('viewLabel').innerHTML='<input type="hidden" name="proses" id="proses" value="add">NEW MASTER JOBS';

						document.getElementById('jobsid').value='';
						document.getElementById('job_description').value='';
						document.getElementById('function_jobs').value='';

						var data_schedule_type='<option value="">Select</option>';

						for(var x=0; x<=schedule_type.length-1; x++){
							data_schedule_type+='<option value="'+schedule_type_value[x]+'">'+schedule_type[x]+'</option>';
						}

						document.getElementById('schedule_type').innerHTML=data_schedule_type;

						document.getElementById('active').checked=false;
						document.getElementById('custom_query').checked=false;

						document.getElementById('jobsid').readOnly =false;
						
						enable_form();
					}
					function view_masterjobs(a,b,c,d,e,f,g,h,i){
						$('#view').modal('show');
						document.getElementById('viewLabel').innerHTML='VIEW '+a;

						detail_jobs(a,b,c,d,e,f,g,h,i);

						document.getElementById('jobsid').readOnly =true;
						document.getElementById('job_description').readOnly =true;
						document.getElementById('function_jobs').readOnly =true;
						document.getElementById('schedule_type').disabled =true;
						document.getElementById('job_priority').readOnly =true;
						document.getElementById('server').readOnly =true;
						document.getElementById('database').readOnly =true;
						document.getElementById('active').disabled =true;
						document.getElementById('custom_query').disabled =true;
						document.getElementById('btnsave').style.display='none';
					}

					function edit_masterjobs(a,b,c,d,e,f,g,h,i){
						$('#view').modal('show');
						document.getElementById('viewLabel').innerHTML='<input type="hidden" name="proses" id="proses" value="edit">EDIT '+a;

						detail_jobs(a,b,c,d,e,f,g,h,i);

						enable_form();
					}

					function detail_jobs(a,b,c,d,e,f,g,h,i){
						document.getElementById('jobsid').value=a;
						document.getElementById('job_description').value=b;
						document.getElementById('function_jobs').value=c;

						var data_schedule_type='<option value="">Select</option>';

						for(var x=0; x<=schedule_type.length-1; x++){
							if(schedule_type[x]==d){
								data_schedule_type+='<option value="'+schedule_type[x]+'" selected>'+schedule_type[x]+'</option>';
							}else{
								data_schedule_type+='<option value="'+schedule_type[x]+'">'+schedule_type[x]+'</option>';
							}
						}
						document.getElementById('schedule_type').innerHTML=data_schedule_type;

						document.getElementById('job_priority').value=g;
						document.getElementById('server').value=h;
						document.getElementById('database').value=i;

						if(e=='Active'){
							document.getElementById('active').checked=true;
						}else{
							document.getElementById('active').checked=false;
						}

						if(f==1){
							document.getElementById('custom_query').checked=true;
						}else{
							document.getElementById('custom_query').checked=false;
						}

						document.getElementById('jobsid').readOnly =true;
					}

					function enable_form(){
						document.getElementById('job_description').readOnly =false;
						document.getElementById('function_jobs').readOnly =false;
						document.getElementById('schedule_type').disabled =false;
						document.getElementById('job_priority').readOnly =false;
						document.getElementById('server').readOnly =false;
						document.getElementById('database').readOnly =false;
						document.getElementById('active').disabled =false;
						document.getElementById('custom_query').disabled =false;
						document.getElementById('btnsave').style.display='inline';
					}

					<?php
						if($_SESSION['can_update']==true){
					?>
							function save_jobs(){

								document.getElementById('btnsave').disabled=true;
								if (confirm("Apakah anda yakin?") == true) {
									var data='';

									data +='&proses='+document.getElementById('proses').value;
									data +='&jobsid='+document.getElementById('jobsid').value;
									data +='&job_description='+document.getElementById('job_description').value;
									data +='&function_jobs='+document.getElementById('function_jobs').value;
									data +='&schedule_type='+document.getElementById('schedule_type').value;
									data +='&job_priority='+document.getElementById('job_priority').value;
									data +='&server='+document.getElementById('server').value;
									data +='&database='+document.getElementById('database').value;

									if (document.getElementById('active').checked) {
										data +='&active=1';
									}else{
										data +='&active=0';
									}

									if (document.getElementById('custom_query').checked) {
										data +='&custom_query=1';
									}else{
										data +='&custom_query=0';
									}
									console.log(data);
									$.ajax({
										type      : 'POST',	
										url 			: '<?php echo site_url('Jobs/Proses') ?>',
										data      : data,
										success   : function(data) {

											var data = data.trim();

											document.getElementById('btnsave').disabled=false;
											
											if(data=='sama'){

												alert("ID Jobs sudah ada, silahkan isi dengan ID Jobs yang lainya!!!");

											}else if(data=='error'){

												alert("Proses tidak ditemukan, atau user tidak memiliki akses untuk proses ini");

											}else if(data=='kosong'){

												alert("ID Jobs tidak boleh kosong!!!");

											}else if(data=='2'){

												alert("Schedule masih ada yang active, silahkan non active terlebih dahulu!!!");

											}else{

												window.location.href='<?php echo site_url('Jobs') ?>';

											}

											return false;

										}

									})

									return false;

								}else{
									
									document.getElementById('btnsave').disabled=false;

								}
							}
					<?php
						}
						if($_SESSION['can_delete']==true){
					?>

							function delete_masterjobs(e){
								if (confirm("Apakah anda yakin ingin menghapus Master Jobs ini?") == true) {

									var data ='&proses=delete';
											data +='&jobsid='+e;

										console.log(data);
											$.ajax({
												type      : 'POST',	
												url 			: '<?php echo site_url('Jobs/Proses') ?>',
												data      : data,
												success   : function(data) {

													var data = data.trim();
													if(data=='1'){

														window.location.href='<?php echo site_url('Jobs') ?>';

													}else if(data=='2'){

														alert("Schedule masih ada yang active, silahkan non active terlebih dahulu!!!");

													}else{

														alert("Proses tidak ditemukan, atau user tidak memiliki akses untuk proses ini");

													}

													return false;

												}

											})
								}
							}

					<?php
						}
					?>

					function schedule_masterjobs(e){
						window.location.href='<?php echo site_url('Jobs/Schedule'); ?>/'+e;
					}
					function log_masterjobs(e){
						window.location.href='<?php echo site_url('Jobs/JobLogs'); ?>/'+e;
					}
		</script>
		
<?php
	}else{

		if($this->uri->segment(4)=='edit' && $_SESSION['can_update']==false){
			header('Location: '.site_url('Jobs'));
		}
?>

		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

		<link rel="stylesheet" href="<?php echo site_url('css/selectize.default.min.css') ?>"/>
		<script src="<?php echo site_url('js/selectize.min.js') ?>"></script>

		<div class="container title">SCHEDULE JOBS</div>
		<div class="col-lg-12">
			<?php
				if($this->uri->segment(4)=='edit'){
			?>
					<form action="" method="POST" id="form">
			<?php
				}
			?>
						<input type="hidden" name="idjobs" id="idjobs" value="<?php echo $id; ?>">
						<input type="hidden" name="job_description" id="job_description" value="<?php echo $jobs[0]->job_description; ?>">
						<input type="hidden" name="job_function" id="job_function" value="<?php echo $jobs[0]->job_function; ?>">
						<div style="margin-bottom: 5px;">
							<?php
								if($this->uri->segment(4)=='edit'){
							?>
									
									<a href="<?php echo site_url('Jobs/Schedule/'.$id) ?>" id="back">
										<button type="button" class="btn btn-default" title="Back" style="font-weight: normal; display: inline;">
								       		Back
								       	</button>
								    </a>
							        <button type="submit" id="btnsave" class="btn btn-default" title="Save" style="font-weight: normal;">
							        	Save
							        </button>
							<?php
								}else{
							?>
									<a href="<?php echo site_url('Jobs/') ?>">
										<button type="button" class="btn btn-default" title="Back" style="font-weight: normal; display: inline;">
								       		Back
								       	</button>
								    </a>
									<a href='<?php echo site_url('Jobs/Schedule/'.$id.'/edit') ?>'>
										<button type="button" class="btn btn-default" title="Edit" style="font-weight: normal;">
							        		Edit
							        	</button>
							        </a>
							<?php
								}
							?>
						</div>

						<table class="table table-striped table-bordered" cellspacing="0" style="font-size: 12px;" summary="table">
							<thead>
								<tr>
									<th scope="col" width="2%" class='no-sort'>No</th>
									<th scope="col">Location DB</th>
									<th scope="col">Jobs Schedule Type</th>
									<th scope="col">Jobs Schedule Day</th>
									<th scope="col">Priority</th>
									<th scope="col">Server</th>
									<th scope="col">Database</th>
									<th scope="col">IsActive <input type="checkbox" <?php if($this->uri->segment(4)!=='edit'){ echo 'disabled'; }else{ echo 'id="select_all"'; }; ?>  ></th>
									<?php
										if($jobs[0]->is_custom_query==1){
									?>
											<th scope="col">Query</th>
									<?php
										}
									?>
								</tr>
							</thead>
							<tbody>
								<?php
								$no=1;
								$array_schedule_type = array('MONTHLY','WEEKLY', 'HOURLY', 'NOT SCHEDULE');
								$array_schedule_type_value = array('MONTHLY','WEEKLY', 'HOURLY', 'NONE');
								$tamp_js="";
								$tamp_id="";

									foreach ($cabang as $key => $c) {

										if(!empty($c->job_id)){
											$job_schedule_type = $c->job_schedule_type;
											$priority = $c->job_priority;
											$server = $c->srs;
											$database = $c->db;
											if($c->is_active=='1'){
												$checked_active = 'checked';
											}else{
												$checked_active = '';
											}

											if(!empty($c->job_schedule_day)){
												$jobs_schedule_day = explode(',',$c->job_schedule_day);
											}else{
												$jobs_schedule_day = array();
											}

										}else{
											$job_schedule_type = $jobs[0]->job_schedule_type;
											$priority = $jobs[0]->job_priority;

											if($jobs[0]->server!==''){
												$server = $jobs[0]->server;
											}else{
												$server = $c->srs;
											}

											if($jobs[0]->database!==''){
												$database = $jobs[0]->database;
											}else{
												$database = $c->db;
											}

											if($jobs[0]->is_active==1){
												$checked_active = 'checked';
											}else{
												$checked_active = '';
											}

											$jobs_schedule_day = array();
											
										}

										$ubah=str_replace("=", "", base64_encode($c->dbid));

								?>
										<tr>
											<td align="center">
												<?php echo $no; ?>
											</td>
											<td>
												<input type="hidden" name="dbid[]" id="dbid" value="<?php echo $ubah; ?>">
												<?php echo $c->NamaDb; ?>
											</td>
											<td>
												<select name="jobs_schedule_type[]" id="jobs_schedule_type_<?php echo $ubah; ?>" class="form-control">
													<?php
														for ($i=0; $i < count($array_schedule_type) ; $i++) { 

															if($array_schedule_type[$i]==$job_schedule_type){
																$select='selected';
															}else{
																$select='';
															}
													?>
															<option value="<?php echo $array_schedule_type_value[$i]; ?>" <?php echo $select; ?>>
																<?php echo $array_schedule_type[$i]; ?>
															</option>
													<?php
														}
													?>
												</select>
											</td>
											<td>

											 	<div class="dropdown cq-dropdown" data-name='statuses'>

											        <button class="btn btn-info btn-sm dropdown-toggle" type="button" id="btndropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="font-weight:normal; width:150px; text-align: left; background-color:#FFFFFF;">

											          	Select

											          <span class="caret"></span>

											        </button> 
											        <button class="btn btn-default" type="button" onclick="open_select('<?php echo $c->NamaDb; ?>','<?php echo $c->job_schedule_day; ?>')">
											        	<i class="glyphicon glyphicon-search" aria-hidden="true"></i>
											        </button>

											        	<?php
															if($this->uri->segment(4)=='edit'){
														?>
													        	<ul class="dropdown-menu" aria-labelledby="btndropdown" style="padding:10px; background-color: #FFFFFF; height: 150px;overflow: scroll; margin: 10px;">

													          		<?php
																		for($x=1; $x<=31; $x++){

																			$checked='';

																			for($y=0; $y<count($jobs_schedule_day); $y++){
																				if($jobs_schedule_day[$y]==$x){
																					$checked=' checked';
																				}
																			}
																	?>
																			<li>
																				<input type="checkbox" id="jobs_schedule_day" name="jobs_schedule_day_<?php echo $ubah; ?>[]" value="<?php echo $x; ?>" <?php echo $checked; ?>> <?php echo $x; ?>
																			</li>
																	<?php
																		}
																	?>
																					            
																	<li>
																		<?php for($y=0; $y<count($jobs_schedule_day); $y++){ if($jobs_schedule_day[$y]=='END OF MONTH'){ echo 'checked'; } } ?>
																		<input type="checkbox" name="jobs_schedule_day_<?php echo $ubah; ?>[]" value="END OF MONTH" <?php for($y=0; $y<count($jobs_schedule_day); $y++){ if($jobs_schedule_day[$y]=='END OF MONTH'){ echo 'checked'; } } ?>> END OF MONTH
																	</li>
																	<li>
																		<input type="checkbox" name="jobs_schedule_day_<?php echo $ubah; ?>[]" value="MONDAY" <?php for($y=0; $y<count($jobs_schedule_day); $y++){ if($jobs_schedule_day[$y]=='MONDAY'){ echo 'checked'; } } ?>> MONDAY
																	</li>
																	<li>
																		<input type="checkbox" name="jobs_schedule_day_<?php echo $ubah; ?>[]" value="TUESDAY" <?php for($y=0; $y<count($jobs_schedule_day); $y++){ if($jobs_schedule_day[$y]=='TUESDAY'){ echo 'checked'; } } ?>> TUESDAY
																	</li>
																	<li>
																		<input type="checkbox" name="jobs_schedule_day_<?php echo $ubah; ?>[]" value="WEDNESDAY" <?php for($y=0; $y<count($jobs_schedule_day); $y++){ if($jobs_schedule_day[$y]=='WEDNESDAY'){ echo 'checked'; } } ?>> WEDNESDAY
																	</li>
																	<li>
																		<input type="checkbox" name="jobs_schedule_day_<?php echo $ubah; ?>[]" value="THURSDAY" <?php for($y=0; $y<count($jobs_schedule_day); $y++){ if($jobs_schedule_day[$y]=='THURSDAY'){ echo 'checked'; } } ?>> THURSDAY 
																	</li>
																	<li>
																		<input type="checkbox" name="jobs_schedule_day_<?php echo $ubah; ?>[]" value="FRIDAY" <?php for($y=0; $y<count($jobs_schedule_day); $y++){ if($jobs_schedule_day[$y]=='FRIDAY'){ echo 'checked'; } } ?>> FRIDAY
																	</li>
																	<li>
																		<input type="checkbox" name="jobs_schedule_day_<?php echo $ubah; ?>[]" value="SATURDAY" <?php for($y=0; $y<count($jobs_schedule_day); $y++){ if($jobs_schedule_day[$y]=='SATURDAY'){ echo 'checked'; } } ?>> SATURDAY
																	</li>
																	<li>
																		<input type="checkbox" name="jobs_schedule_day_<?php echo $ubah; ?>[]" value="SUNDAY" <?php for($y=0; $y<count($jobs_schedule_day); $y++){ if($jobs_schedule_day[$y]=='SUNDAY'){ echo 'checked'; } } ?>> SUNDAY
																	</li>

													       		</ul>
													    <?php
													    	}
													    ?>

												</div>


											</td>
											<td>
												<input type="text" name="prioritas[]" id="prioritas_<?php echo $ubah; ?>" value="<?php echo $priority; ?>" class="form-control" value="<?php echo '';?>" style="width:50px" onkeypress="return hanyaAngka(event)">
											</td>
											<td>
												<input type="text" name="server[]" id="server_<?php echo $ubah; ?>" value="<?php echo $server; ?>" class="form-control" value="<?php echo '';?>">
											</td>
											<td>
												<input type="text" name="database[]" id="database_<?php echo $ubah; ?>" value="<?php echo $database; ?>" class="form-control" value="<?php echo '';?>">
											</td>
											<td align="center">
												<input type="checkbox" name="active[<?php echo $no-1; ?>]" id="active_<?php echo $ubah; ?>" class="checkbox_active" value="1" <?php echo $checked_active; ?>>
											</td>
											<?php
												if($jobs[0]->is_custom_query==1){
											?>
													<td>
														<textarea name="job_custom_query[]" id="job_custom_query_<?php echo $ubah; ?>" class="form-control"><?php echo $c->job_custom_query; ?></textarea>
													</td>
											<?php
												}
											?>
										</tr>
								<?php
										if(empty($this->uri->segment(4)) || $this->uri->segment(4)!=='edit'){
											$tamp_js .="document.getElementById('jobs_schedule_type_".$ubah."').disabled = true;
														document.getElementById('prioritas_".$ubah."').readOnly = true;
														document.getElementById('server_".$ubah."').readOnly = true;
														document.getElementById('database_".$ubah."').readOnly = true;
														document.getElementById('active_".$ubah."').disabled = true;
														document.getElementById('job_custom_query_".$ubah."').disabled = true;";
										}
										$tamp_id .="#jobs_schedule_day_".$ubah.",";
									$no++;
									}
								?>
							</tbody>
						</table>

						<div class="modal fade" id="select_schedule_type" tabindex="-1" role="dialog" aria-labelledby="select_schedule_typeLabel" aria-hidden="true">
						  <div class="modal-dialog" role="document">
						    <div class="modal-content">
						      <div class="modal-header" id="header"></div>
						      <div class="modal-body" id="detail"></div>
						      <div class="modal-footer">
						        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
						      </div>
						    </div>
						  </div>
						</div>
			<?php
				if($this->uri->segment(4)=='edit'){
			?>
					</form>
			<?php
				}
			?>
		</div>
		<script>
			<?php
				if(!empty($this->uri->segment(4)) || $this->uri->segment(4)=='edit'){
			?>
					$(document).ready(function(){
						$("#form").on("submit", function(event){	
							document.getElementById('btnsave').disabled=true;

							Swal.fire({
							  title: '',
							  text: 'Silahkan tunggu, Sedang dalam proses penyimpanan data',
							  showConfirmButton: false,
							})
	

							event.preventDefault();
	 
	       					var data= $(this).serialize();

								console.log(data);
								$.ajax({
									type      : 'POST',	

									url 	  : '<?php echo site_url('Jobs/Proses/Schedule'); ?>',

									data      : data,
									success   : function(data) {

										if(data=='error'){

											alert('Transaksi anda tidak dapat di simpan, Silahkan coba kembali!!!');

										}else{

											window.location.href='<?php echo site_url('Jobs/Schedule/'.$id) ?>';
										}
									
										document.getElementById('btnsave').disabled=false;

										return false;

									}
								})


								return false;
						});

					    $('#select_all').on('click',function(){
					        if(this.checked){
					            $('.checkbox_active').each(function(){
					                this.checked = true;
					            });
					        }else{
					             $('.checkbox_active').each(function(){
					                this.checked = false;
					            });
					        }
					    });
					});

			<?php 
				}
				if(empty($this->uri->segment(4)) || $this->uri->segment(4)!=='edit'){
			?>

				disabled_all();
				function disabled_all(){
					<?php echo $tamp_js; ?>
				}

			<?php
				}
			?>


			function open_select(a,b){
				$('#select_schedule_type').modal('show');

				var explode = b.split(",");
				var result = '<table class="table" style="font-size:12px;"><tr><td align="center">No</td><td>Jobs Schedule Day</td></tr>';

				var no=1;
				for(var i = 0; i < explode.length; i++){

					if(explode[i]!==''){
						result +='<tr><td width="30px" align="center">'+no+'.</td><td>'+explode[i]+'</td></tr>';
					}else{
						result +='<tr><td colspan="2" align="center">Jobs Schedule Day Tidak Ada</td></tr>';
					}
					no++;
				}
				result +='</table>';

				document.getElementById('header').innerHTML=a;
				document.getElementById('detail').innerHTML=result;
			}
		</script>
<?php
	}
?>

<script type="text/javascript">
	function hanyaAngka(evt) {
	  var charCode = (evt.which) ? evt.which : event.keyCode
	  if (charCode > 31 && (charCode < 48 || charCode > 57))

	    return false;
	  return true;
	}
</script>



