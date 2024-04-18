<?php

	$announcement = '';
	$start_published_date = '';
	$end_published_date = '';
	$active = '';

	if(!empty($data) && $mode=='add'){
		$announcement = $data['announcement'];
		$start_published_date = $data['start_published_date'];
		$end_published_date = $data['end_published_date'];
		$active = $data['active'];
	}else if(!empty($data) && ($mode=='view' || $mode=='edit')){
		$announcement_id = $data[0]->announcement_id;
		$attachment_1 = $data[0]->attachment_1;
		$attachment_2 = $data[0]->attachment_2;
		$attachment_3 = $data[0]->attachment_3;
		$announcement = htmlspecialchars_decode($data[0]->announcement);
		$start_published_date = date_format(date_create($data[0]->start_published_date),'Y-m-d');
		$end_published_date = date_format(date_create($data[0]->end_published_date),'Y-m-d');
		$active = $data[0]->is_active;
	}


	if(!empty($active)){
		$checked = 'checked';
	}else{
		$checked = '';
	}

?>

<div class="container">
	<div class="row">
		<div class="page-title">ANNOUNCEMENT</div>
		<div class="col-12">
			<?php
				if($mode=='views'){
					if($_SESSION["can_create"] == true) { 
			?>
			      		<a href="<?php echo site_url('Announcement/add'); ?>">Insert New Annoucement</a>
			<?php
					}
			?>
					<table id="table" class="table table-striped table-bordered" cellspacing="0" width="100%" summary="table">
						<thead>
							<tr>
								<th scope="col" rowspan="2" style="text-align:center">No</th>
								<th scope="col" rowspan="2">Announcement</th>
								<th scope="col" colspan="3"><div align="center">Attachment</div></th>
								<th scope="col" colspan="2"><div align="center">Published Date</div></th>
								<th scope="col" rowspan="2" width="80px">Action</th>
							</tr>
							<tr>
								<th width="80px">1</th>
								<th width="80px">2</th>
								<th width="80px">3</th>
								<th width="100px">Start</th>
								<th width="100px">End</th>
							</tr>
						</thead>
						<tbody id="isibrach">
							<?php
								$no=1;
								foreach ($Announcement as $key => $a) {
							?>
									<tr>
										<td align="center"><?php echo $no; ?></td>
										<td>
											<?php 
												echo htmlspecialchars_decode($a->announcement); 
											?>
										</td>
										<td>
											<?php
												if(!empty($a->attachment_1)){
											?>
													<img alt="" src="<?php echo site_url('attachment/announcement/'.$a->attachment_1); ?>" width="100%">
											<?php
												}
											?>
										</td>
										<td>
											<?php
												if(!empty($a->attachment_2)){
											?>
													<img alt="" src="<?php echo site_url('attachment/announcement/'.$a->attachment_2); ?>" width="100%">
											<?php
												}
											?>
										</td>
										<td>
											<?php
												if(!empty($a->attachment_3)){
											?>
													<img alt="" src="<?php echo site_url('attachment/announcement/'.$a->attachment_3); ?>" width="100%">
											<?php
												}
											?>
										</td>
										<td>
											<?php 
												echo $a->start_published_date; 
											?>
										</td>
										<td>
											<?php 
												echo $a->end_published_date; 
											?>
										</td>
										<td>
											<table border="0">
												<tr>
													<?php
														if($_SESSION["can_update"] == true) { 
													?>
															<th scope="col">
																<a href="<?php echo site_url('Announcement/edit/'.str_replace("=", "", base64_encode($a->announcement_id))); ?>">
																	Update
																</a>
															</th>
													<?php
														}
														echo " <br> ";
														if($_SESSION["can_delete"] == true) { 
													?>
															<th scope="col">
																<a href="#" onclick="delete_announcement('<?php echo str_replace("=", "", base64_encode($a->announcement_id)); ?>')">
																	Delete
																</a>
															</th>
													<?php
														}
													?>
												</tr>
											</table>
										</td>
									</tr>
							<?php
								$no++;
								}
							?>
						</tbody>
					</table>
			<?php
				}else if(($mode=='add' && $_SESSION["can_create"] == true) || ($mode=='view' && $_SESSION["can_read"] == true) || ($mode=='edit' && $_SESSION["can_update"] == true)) { 
			?>

				    <script src="<?php echo base_url('assets/summernote/js/summernote.js'); ?>"></script>
				    <link href="<?php echo base_url('assets/summernote/css/summernote.css'); ?>" rel="stylesheet">

				    <?php
				    	if($mode=='add' || $mode=='edit'){
				    ?>
							<form method="POST" id="proses_announcement" enctype="multipart/form-data">
					<?php
						}
						if($mode=='edit'){
					?>
							<input type="hidden" name="asd" value="<?php echo str_replace("=", "", base64_encode(base64_encode($announcement_id))); ?>">
					<?php
						}
					?>
						<table class="table" border="0" summary="table">
							<tr>
								<th scope="col" width="150px">
									Announcement
								</th>
								<th scope="col">
									<textarea class="form-control" name="announcement" class="summernote" id="announcement"><?php echo $announcement; ?></textarea>
								</th>
							</tr>
							<tr>
								<th scope="col">
									Attachment 
								</th>
								<th scope="col">
									<input type="file" name="attachment[]" id="attachment" class="form-control" multiple>
									Maximum upload 3 photos
								</th>
							</tr>
							<?php
								if($mode=='view' || $mode=='edit'){
							?>
									<tr>
										<th scope="col"></th>
										<th scope="col">
											<?php
												if(!empty($attachment_1)){
											?>
													<div style="width:31%; padding:1%; float: left;" align="center">
														<img alt="" src="<?php echo site_url('upload/attachment/announcement/'.$attachment_1); ?>" width="100%">

														<?php 
															if($mode=='edit'){
														?>
																<br>
																<a href="#" onclick="hapus_img('<?php echo $attachment_1; ?>','<?php echo str_replace("=", "", base64_encode($announcement_id)); ?>')">Hapus</a>
														<?php
															}
														?>
													</div>
											<?php
												}

												if(!empty($attachment_2)){
											?>
													<div style="width:31%; padding:1%; float: left;" align="center">
														<img alt="" src="<?php echo site_url('upload/attachment/announcement/'.$attachment_2); ?>" width="100%"><br>
														
														<?php 
															if($mode=='edit'){
														?>
																<br>
																<a href="#" onclick="hapus_img('<?php echo $attachment_2; ?>','<?php echo str_replace("=", "", base64_encode($announcement_id)); ?>')">Hapus</a>
														<?php
															}
														?>

													</div>
											<?php
												}

												if(!empty($attachment_3)){
											?>
													<div style="width:31%; padding:1%; float: left;" align="center">
														<img alt="" src="<?php echo site_url('attachment/announcement/'.$attachment_3); ?>" width="100%"><br>
														
														<?php 
															if($mode=='edit'){
														?>
																<br>
																<a href="#" onclick="hapus_img('<?php echo $attachment_3; ?>','<?php echo str_replace("=", "", base64_encode($announcement_id)); ?>')">Hapus</a>
														<?php
															}
														?>
													</div>
											<?php
												}
											?>
										</th>
									</tr>
							<?php
								}
							?>
							<tr>
								<th scope="col">
									Start Published Date 
								</th>
								<th scope="col">
									<input type="text" name="start_published_date" id="start_published_date" class="form-control" value="<?php echo $start_published_date; ?>">
								</th>
							</tr>
							<tr>
								<th scope="col">
									End Published Date 
								</th>
								<th scope="col">
									<input type="text" name="end_published_date" id="end_published_date" class="form-control" value="<?php echo $end_published_date; ?>">
								</th>
							</tr>
							<tr>
								<th scope="col">
									Active
								</th>
								<th scope="col">
									<input type="checkbox" name="active" id="active" value="1" <?php echo $checked; ?>> Active
								</th>
							</tr>
							<tr>
								<th colspan="2">
									<?php
								    	if($mode=='add' || $mode=='edit'){
								    ?>
											<button type="submit" name="proses" class="btn btn-primary">
												Send
											</button>
									<?php
										}

										if($mode=='view'){
											if($_SESSION["can_create"] == true){
									?>
												<a href="<?php echo site_url('Announcement/add'); ?>">
													<button type="button" name="New" class="btn btn-primary">
														New
													</button>
												</a>
									<?php
											}
											if($_SESSION["can_update"] == true){
									?>
												<a href="<?php echo site_url('Announcement/edit/'.str_replace("=", "", base64_encode($announcement_id))); ?>">
													<button type="button" name="edit" class="btn btn-primary">
														Edit
													</button>
												</a>
									<?php
											}
										}
									?>
								</th>
							</tr>
						</table>
					<?php
				    	if($mode=='add' || $mode=='edit'){
				    ?>
							</form>
			<?php
						}
				}
			?>
		</div>
	</div>
</div>


<script>
	<?php
		if($mode=='views'){
	?>
			$('#table').DataTable({
				"pageLength": 10
			});	

	<?php
			if($_SESSION["can_delete"] == true) { 
	?>
				function delete_announcement(a){
					if (confirm("Are you sure!!!") == true) {
						var data  = 'a='+a;
						data += '&c=delete_announcement';
						console.log(data);
						$.ajax({
							type 	: 'POST',	
							url 	: '<?php echo site_url('Announcement/proses'); ?>', 
							data  	: data,
							success : function(data) {
								location.reload();
								return false
							}
						})
					}
				}
	<?php
			}

		}else if($mode!=='views'){
	?>

			$(document).ready(function() {
			  	$('#announcement').summernote({ height: 300 });
			});

			$('#start_published_date,#end_published_date').datepicker({
			    format: "yyyy-mm-dd",
			    autoclose: true
		    });

	<?php
		}

		if($mode=='view'){
	?>
			$('input').attr('disabled',!this.disabled)
			$('#announcement').summernote('disable');
	<?php
		}

		if($mode=='edit'){
	?>
			function hapus_img(a,b){

				if (confirm("Are you sure!!!") == true) {

					var data  = 'a='+a;
						data += '&b='+b;
						data += '&c=delete_img';
					console.log(data);
					$.ajax({
						type 	: 'POST',	
						url 	: '<?php echo site_url('Announcement/proses'); ?>', 
						data  	: data,
						success : function(data) {
							location.reload();
							return false
						}
					})
				}
			}
	<?php
		}

		if(!empty($error)){
	?>
			alert("Maximum upload 3 photos");
	<?php
		}
	?>
</script>