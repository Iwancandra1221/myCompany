<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style type="text/css">
      .disablingDiv{
        z-index:1;
         
        /* make it cover the whole screen */
        position: fixed; 
        top: 0%; 
        left: 0%; 
        width: 100%; 
        height: 100%; 
        overflow: hidden;
        margin:0;
        /* make it white but fully transparent */
        background-color: white; 
        opacity:0.5;  
      }
      .loader {
          position: absolute;
          left: 50%;
          top: 50%;
          z-index: 1;
          margin: -75px 0 0 -75px;
          border: 16px solid #f3f3f3;
          border-radius: 50%;
          border-top: 16px solid #3498db;
          width: 120px;
          height: 120px;
          -webkit-animation: spin 2s linear infinite;
          animation: spin 2s linear infinite;
      }

      @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
      }
    </style>
    <div id="disablingDiv" class="disablingDiv">
    </div>
    <div id="loading" class="loader">
    </div>

<div class="container">
	<div class="page-title">
		MASTER JENIS BARANG SERVICE
	</div>
	
	<div class="row">
	<?php
		if($module=='list'){
	?>
		<div class="col-6">
			
		</div>
		<div class="col-6 text-right">
			
			<?php if($_SESSION["can_create"]==true){ ?>
				<a href="<?php echo site_url('masterservice/JenisBarang/add') ?>">
					<button type="button" class="btn btn-dark">
						ADD
					</button>
				</a>
			<?php } ?>
			<?php if($_SESSION["can_print"]==true){ ?>
			<a href="<?php echo site_url('masterservice/rekap/JenisBarang') ?>" target="_blank">
				<button type="button" class="btn btn-dark">
					REKAP
				</button>
			</a>
			<?php } ?>
		</div>
			
		<div class="col-12">
			<table id="TblService" class="table table-bordered" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th scope="col" >Kode Jenis Barang</th>
						<th scope="col" >Nama Jenis Barang</th>
						<th scope="col" >Active</th>
						<th scope="col" >Modified By</th>
						<th scope="col" >Modified Date</th>
						<th scope="col" width="100px" align="center">Action</th>
					</tr>
				</thead>
				<tbody>
 
				</tbody>
			</table>
		</div>
	<?php
		} else if($module=='add' || $module=='edit' || $module=='view'){
		
			$merk_html = '<option value="">Pilih Merk</option>';
			if($merk['result']=='sukses'){
				foreach ($merk['data'] as $key => $m) {
					
					$merk_html .= '<option value="'.$m['Merk'].'">'.$m['Merk'].'</option>';
					
				}
			}
	?>
			<div class="col-1">
			</div>
			<div class="col-10">
				
				<?php
					$disabled='';
					if($module=='add' || $module=='edit'){
					?>
					
					<?php if(!empty($status)){ ?> 
						<script type="text/javascript">
							Swal.fire({
								title: 'Tidak Dapat Dimasukan, Silahkan Cek Data Kembali!',
								html: '<?php echo $status; ?>',
								icon: 'error',
								confirmButtonText: 'Close'
							})
						</script>
						<?php 
						}
						if($module=='add'){
							$action='add';
							}else{
							$action='edit/'.str_replace("=", "", base64_encode($detail[0]->kd_jnsbrg));
						}
					?>
					<form>
						<?php
							}else{
							$disabled = 'disabled';
						} 
						
						if($module=='edit' || $module=='view'){
							$kd_JnsBrg = $detail[0]->kd_jnsbrg;
							$JnsBrg = $detail[0]->jns_brg;
							
							if($detail[0]->is_active==1){
								$aktif = 'checked';
							}else{
								$aktif = '';
							}
						}else{
							$kd_JnsBrg = '';
							$JnsBrg = '';
							$aktif = 'checked';
						}
						
						if($module=='edit'){
							$readonly = 'readonly';
						}else{
							$readonly = '';
						}
						
						$jum=1;
						if(!empty($detail)){
						  $jum=count($detail);
						}
					  ?>
						<input id="idr" value="<?php echo $jum; ?>" type="hidden" />
					<div class="border20 p20">
					<table class="table table-striped">
						<thead>
							<?php
								if($module=='add' || $module=='edit'){
								?>
								<tr>
									<td colspan="6" align="right">
										<button type="submit" name="add" class="btn btn-dark">
											Submit
										</button>
										<a href="<?php echo site_url('masterservice/JenisBarang') ?>">
											<button type="button" class="btn btn-dark">
												Cancel
											</button>
										</a>
									</td>
								</tr>
								<?php 
									}else{
								?>
								<tr>
									<td colspan="6" align="right">
										<?php
											if($_SESSION['can_update']==true){
											?>
											<a href="<?php echo site_url('masterservice/JenisBarang/edit/'.str_replace("=", "", base64_encode($kd_JnsBrg)).'/'.str_replace("=", "", base64_encode($JnsBrg))); ?>">
												<button type="button" name="edit" class="btn btn-dark">
													Edit
												</button>
											</a>
											<?php
											}
										?>
										<a href="<?php echo site_url('masterservice/JenisBarang') ?>">
											<button type="button" class="btn btn-dark">
												Cancel
											</button>
										</a>
									</td>
								</tr>
								<?php  
								}
							?>
							<tr>
								<td>
									Kode
								</td>
								<td width="100px">
									<input type="text" class="form-control" name="kode" id="kode" maxlength="3" value="<?php echo $kd_JnsBrg; ?>" onkeyup="hurufbesar('kode')" required <?php echo $disabled.' '.$readonly; ?>>
								</td>
								<td>
									Jenis barang
								</td>
								<td>
									<input type="text" class="form-control" name="jns_brg" id="jns_brg" value="<?php echo $JnsBrg; ?>" onkeyup="hurufbesar('jns_brg')" required <?php echo $disabled; ?>>
								</td>
								<td>
									<input type="checkbox" name="aktif" value="1" <?php echo $disabled.' '.$aktif; ?>> Aktif 
								</td>
							</tr>
							<tr style="background-color: #eaeaea;">
								<td align="center">
									<button type="button" class="btn btn-sm btn-dark" onclick="addRow()" <?php echo $disabled; ?>>
										<i class="glyphicon glyphicon-plus"></i>
									</button>
								</td>
								<td colspan="2">Merk</td>
								<td colspan="2">Jenis Barang</td>
							</tr>
						</thead>
						<tbody id="tableID">
							<?php
								if($module!=='add'){
									$no=1;
									foreach ($detail as $key => $d) {
									?>
									<tr id="srow<?php echo $no; ?>">
										<td align="center">
											<button type="button" class="btn btn-sm btn-danger-dark" onclick="hapusElemen('#srow<?php echo $no; ?>'); return false;" <?php echo $disabled; ?>>
												<i class="glyphicon glyphicon-minus"></i>
											</button>
										</td>
										<td colspan="2">
											<select class="form-control" name="merk[]" id="merk<?php echo $no; ?>" onchange="merk_getjns('merk<?php echo $no; ?>','jnsbrg<?php echo $no; ?>', '<?php echo($d->jnsbrg);?>');" required <?php echo $disabled; ?>>
												<?php 
													foreach ($merk['data'] as $key => $m) {

                            
														if(strtoupper(trim($m['Merk']))===strtoupper(trim($d->merk))){
															$selected='selected';
														}else{
															$selected='';
														}
														echo '<option value="'.$m['Merk'].'" '.$selected.'>'.$m['Merk'].'</option>';
													}
												?>
											</select>
											<td colspan="3">
												<select class="form-control" name="jnsbrg[]" id="jnsbrg<?php echo $no; ?>" required <?php echo $disabled; ?>>
													<option value="">Pilih</option>
												</select>
											</td>
										</tr>
										<?php
											$no++;
										}
										}else{
									?>
									<tr id="srow1">
										<td align="center">
											<button type="button" class="btn btn-sm btn-danger-dark" onclick="hapusElemen('#srow1'); return false;" <?php echo $disabled; ?>>
												<i class="glyphicon glyphicon-minus"></i>
											</button>
										</td>
										<td colspan="2">
											<select class="form-control" name="merk[]" id="merk1" onchange="merk_getjns('merk1','jnsbrg1');" required <?php echo $disabled; ?>>
												<?php echo $merk_html; ?>
											</select>
										</td>
										<td colspan="3">
											<select class="form-control" name="jnsbrg[]" id="jnsbrg1" required <?php echo $disabled; ?>>
												<option value="">Pilih</option>
											</select>
										</td>
									</tr>
									<?php
									}
								?>
							</tbody>
						</table>
						</div>
						<?php
							if($module=='add' || $module=='edit'){
							?>
						</form>
						<?php
						}
					?>
				</div>
				
				<script language="javascript">
					<?php
						if($module!=='add'){
							$no=1;
							foreach ($detail as $key => $d) { 
					?>
							
								merk_getjns('merk<?php echo $no; ?>','jnsbrg<?php echo $no; ?>','<?php echo rtrim($d->jnsbrg); ?>');
							
					<?php
								$no++;
							}
						}
					?>
					
					function addRow() {
						var idr = document.getElementById("idr").value;
						idr++;
						var stre;
						var ub="'#srow" + idr + "'";
						var ubc="'merk"+idr+"','jnsbrg"+idr+"'";
						stre = '<tr id="srow' + idr + '"><td align="center"><button type="button" class="btn btn-sm btn-danger-dark" onclick="hapusElemen('+ub+'); return false;"><i class="glyphicon glyphicon-minus" <?php echo $disabled; ?>></i></button></td><td colspan="2"><select class="form-control" name="merk[]" id="merk'+idr+'" onchange="merk_getjns('+ubc+');" required <?php echo $disabled; ?>><?php echo $merk_html; ?></select></td><td colspan="3"><select class="form-control" name="jnsbrg[]" id="jnsbrg'+idr+'" required <?php echo $disabled; ?>><option value="">Pilih</option></select></td></tr>';
						$("#tableID").append(stre);
						document.getElementById("idr").value = idr;
					}
					function hapusElemen(idr) {
						$(idr).remove();
					}
					
					function merk_getjns(a,b,c=''){
						document.getElementById(b).innerHTML='<option value="">Loading</option>';
						var d = document.getElementById(a).value.trim();
						var data  = 'merk='+d;

						console.log(data);
						$.ajax({
							type  	: 'POST', 
							url   	: '<?php echo site_url('masterservice/GetListJenisBarang') ?>',
							data    : data,
							success : function(obj) {

								var json = JSON.parse(obj); 
								var jum = json.data.length;
								var option='<option value="">Pilih</option>';
								if(jum>0){
									for(var i=0; i<jum; i++){
										if(c!==''){
											if(json.data[i].Jns_Brg.trim()==c){
												var selected = 'selected';
											}else{
												var selected = '';
											}
										}else{
											var selected = '';
										}
										option += '<option value="'+json.data[i].Jns_Brg+'" '+selected+'>'+json.data[i].Jns_Brg+'</option>';
									}
									
									document.getElementById(b).innerHTML=option;
									$("#disablingDiv").hide();
      						$("#loading").hide();
								}else{
									document.getElementById(b).innerHTML='<option value="">Data Tidak Ditemukan</option>';
									$("#disablingDiv").hide();
      						$("#loading").hide();
								}
								return false
							}
						})
					}
					
					<?php
						if($module!=='view'){
						?>
						function hurufbesar(id) {
							var x = document.getElementById(id);
							x.value = x.value.toUpperCase();
						}
						
						$(function () {
							$('form').bind('submit', function () {
								$.ajax({
									type: 'post',
									url: '<?php echo site_url('masterservice/JenisBarang/'.$action) ?>',
									data: $('form').serialize(),
									success: function (data) {
										if(data=='error'){
											Swal.fire({

			                  title: '',
			                  html: 'Data tidak di temukan di dalam database',
			                  icon: 'error',
			                  confirmButtonText: 'Close'
			                })
										}else if(data=='double'){
											Swal.fire({
			                  title: '',
			                  html: 'Kode Jenis barang sudah ada dalam database, silahkan ubah kode jenis barang',
			                  icon: 'error',
			                  confirmButtonText: 'Close'
			                })
			              }else if(data=='double_merk_jenis_barang'){
											Swal.fire({
			                  title: '',
			                  html: 'Merk dan Jenis barang sudah ada dalam database, silahkan cek Kembali',
			                  icon: 'error',
			                  confirmButtonText: 'Close'
			                })
										}else{
											window.location.href = "<?php echo site_url('masterservice/JenisBarang/view/'); ?>/"+data;
										}
									}
								});
								return false;
							});
						});
						<?php
						}
					?>
				</script>
				<?php
				}
				?>
					<script>
						function delete_data(e){
							if(confirm('Apakah anda yakin ingin hapus?')){
								var data = 'kode='+e;
								
								console.log(data);
								$.ajax({
									type: 'post',
									url: '<?php echo site_url('masterservice/DeleteData'); ?>',
									data: data,
									success: function (data) {
										if(data=='success'){
											window.location.href = "<?php echo site_url('masterservice/JenisBarang'); ?>";
										}else{
											Swal.fire({
			                  title: '',
			                  html: 'Data tidak dapat dihapus',
			                  icon: 'error',
			                  confirmButtonText: 'Close'
			                })
										}
									}
								});
							}
						}
					</script>
				<?php
					
				if ($module == 'list'){ ?>
			    <script>
			        $(document).ready(function() {
			            var table = $('#TblService').DataTable({
			                paging: true
			            });

			            function reloadData() {
			                $.ajax({
			                    url: '<?php echo site_url('masterservice/listmasterservice'); ?>', 
			                    type: 'GET',
			                    dataType: 'json',
			                    success: function(data) {
			                        table.clear().draw();

			                        for (var i = 0; i < data.length; i++) {
			                            var encoded = btoa(data[i].kd_jnsbrg);
			                            var encode = encoded.replace(/=/g, "");
			                            var encodedelete = "'" + encode + "'";

			                            var actions = '<a href="<?php echo site_url('masterservice/JenisBarang/view'); ?>/' + encode + '"><button class="btn btn-dark"><i class="glyphicon glyphicon-search"></i></button></a>';
			                            
			                            <?php if ($_SESSION["can_update"] == true): ?>
			                                actions += '<a href="<?php echo site_url('masterservice/JenisBarang/edit'); ?>/' + encode + '"><button class="btn btn-dark"><i class="glyphicon glyphicon-pencil"></i></button></a>';
			                            <?php endif; ?>
			                            
			                            <?php if ($_SESSION["can_delete"] == true): ?>
			                                actions += '<button class="btn btn-danger-dark delete-btn" data-encodedelete="' + encode + '"><i class="glyphicon glyphicon-trash"></i></button>';
			                            <?php endif; ?>
			                            
			                            table.row.add([
			                                data[i].kd_jnsbrg,
			                                data[i].jnsbrg,
			                                data[i].active,
			                                data[i].modified_by,
			                                data[i].modified_date,
			                                actions
			                            ]).draw();
			                        }
			                      $("#disablingDiv").hide();
      											$("#loading").hide();
			                    },
			                    error: function(xhr, status, error) {
			                        console.error('Error while loading data:', error);
			                    }
			                });
			            }

			            // Load data saat halaman pertama kali dimuat
			            reloadData();

			            // Handler untuk event klik pada tombol delete
			            $('#TblService').on('click', '.delete-btn', function() {
			                var encodedelete = $(this).data('encodedelete');
			                delete_data(encodedelete);
			            });

			            function delete_data(e) {
			                if (confirm('Apakah anda yakin ingin hapus?')) {
			                    var data = 'kode=' + e;

			                    $.ajax({
			                        type: 'post',
			                        url: '<?php echo site_url('masterservice/DeleteData'); ?>',
			                        data: data,
			                        success: function (data) {
			                            if (data == 'success') {
			                                reloadData();
			                            } else if (data == 'tidak_bisa_hapus') {
			                                Swal.fire({
			                                    title: '',
			                                    html: 'Data tidak dapat dihapus, masih dipakai di master service',
			                                    icon: 'error',
			                                    confirmButtonText: 'Close'
			                                })
			                            } else {
			                                Swal.fire({
			                                    title: '',
			                                    html: 'Data tidak dapat dihapus',
			                                    icon: 'error',
			                                    confirmButtonText: 'Close'
			                                })
			                            }
			                        }
			                    });
			                }
			            }
			        });

			    </script>
			<?php
				}

				if($module=='add'){
			?>
					<script>
						$("#disablingDiv").hide();
	      				$("#loading").hide();
	      			</script>
			<?php
				}
			?>

			
		</div>
	</div>

