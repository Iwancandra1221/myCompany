<?php
if($proses=='list'){
?>
<style type="text/css">
	.row {
	line-height:30px;
	vertical-align:middle;
	clear:both;
	}
	.row-label, .row-input {
	float:left;
	}
	.row-label {
	padding-left: 15px;
	width:180px;
	}
	.row-input {
	width:420px;
	}
</style>
<script>
	$(document).ready(function() {
		$('#dp1').datepicker({
			format: "dd-M-yyyy",
			autoclose: true,
		});
	});
</script>
<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
		<div class="row">
			<?php if($_SESSION["can_create"] == 1) { ?>
			<div class="col-12">
				<!-- <button type="button" class="btn btn-dark" onclick="btn_add()">Tambah</button> -->
				<a href="<?php echo site_url('Masterkpicategoryv2/add'); ?>">
					<button type="button" class="btn btn-dark">Tambah</button>
				</a>
			</div>
			<?php } ?>
			<div class="col-12">
				<table id="tblKPI" class="table table-bordered" style="width:100%;">
					<thead>
						<tr>
							<th scope="col" width="20%">Kategori</th>
							<th scope="col" width="*">Nama Kategori</th>
							<th scope="col" width="10%">Aktif</th>
							<th scope="col" width="10%">Edit Oleh</th>
							<th scope="col" width="20%">Tgl Edit</th>
							<?php if($_SESSION["can_update"] == 1) { ?>
							<th scope="col" width="2%" class="no-sort">Aksi</th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
	</div>
	<!-- load form tambah kpi category -->
	
	
	<script>
		let tblKPI;
		tblKPI = $('#tblKPI').DataTable({
			"pageLength"    : 10,
			"searching"     : true,
			"autoWidth": false,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false }
			],
			// "dom": '<"top">rt<"bottom"ip><"clear">',
			"order": [[1, 'asc']],
		});
		

	
		function btn_edit(col0,col1,col2,col3){
			
			var encodedString = btoa(col0);
				encodedString = encodedString.replace(/=+$/, '');
			window.location.href = '<?php echo site_url('Masterkpicategoryv2/edit'); ?>/'+encodedString;

		}
		$(document).ready(function(){
			getKpiCategory();
		});
		function getKpiCategory(){
			$(".loading").show();
			$("#tblKPI tbody").html("");
			var formData = new FormData();
			formData.append("btn-submit",'filter');
			$.ajax({
				url: "<?=site_url('Masterkpicategoryv2/KPICategoryList');?>",
				type: 'post',
				cache: false,
				contentType: false,
				processData: false,
				data: formData,
				success: function (str) {
					// console.log(str);
					var json = JSON.parse(str);
					if(json.code == 1){
						var data = json.data;
						var html = '';
						for(var i=0;i<data.length;i++){
							var col0 = data[i].KPICategoryID;
							var col1 = data[i].KPICategory;
							var col2 = data[i].KPICategoryName;
							var col3 = data[i].IsActive == 1 ? "<span class='label label-success'>Aktif</span>" : "<span class='label label-danger'>Non-Aktif</span>";
							var col4 = data[i].ModifiedBy;
							var col5 = data[i].ModifiedDate;
							<?php if($_SESSION["can_update"] == 1) { ?>
							var col6 = '<div><button type="button" class="btn btn-dark" onclick="btn_edit(\''+col0+'\',\''+col1+'\',\''+col2+'\',\''+data[i].IsActive+'\')"><span class="glyphicon glyphicon-pencil"></span></button></div>';
							<?php } ?>
							tblKPI.row.add([
								col1,
								col2,
								col3,
								col4,
								col5,
								<?php if($_SESSION["can_update"] == 1) { ?>
								col6
								<?php } ?>
							]);
						}
					}
					tblKPI.draw();    
					$(".loading").hide();
				}
			});
		}
	</script>
</div> <!-- /container -->
<?php
}else{
	if(empty($header)){
		$KPICategoryID = 'AUTONUMBER';
		$KPICategoryinput = '';
		$KPICategoryName = '';
		$IsActive = '';
		$jenis = '';
	}else{
		if(!empty($header[0])){
			$KPICategoryID = $header[0]['KPICategoryID'];
			$KPICategoryinput = $header[0]['KPICategory'];
			$KPICategoryName = $header[0]['KPICategoryName'];
			$IsActive = $header[0]['IsActive'];
			$jenis = $header[0]['jenis'];
			$EKPICategoryID = str_replace('=', '', base64_encode($KPICategoryID));
		}else{
			$KPICategoryID = 'AUTONUMBER';
			$KPICategoryinput = '';
			$KPICategoryName = '';
			$IsActive = '';
			$jenis = '';
			$EKPICategoryID = '';
		}
	}

	if($proses=='add'){
		$batal = site_url('Masterkpicategoryv2');
	}else if($proses=='edit' && $_SESSION["can_update"] == 1){
		$batal = site_url('Masterkpicategoryv2/view/'.$KPICategoryID);
	}else{
		$batal = site_url('Masterkpicategoryv2');
	}

?>
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
<div id="disablingDiv" class="disablingDiv"></div>
<div id="loading" class="loader"></div>
<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
	<div class="row">
		<div class="col-3">KPI Category ID</div>
		<div class="col-9"><input type="text" class="form-control" id="KPICategoryID" value="<?php echo $KPICategoryID; ?>" readonly></div>
		<div class="col-3">Kategori</div>
		<div class="col-9">
			<input type="text" class="form-control" id="KPICategory" value="<?php echo $KPICategoryinput; ?>" required>
		</div>
		<div class="col-3">Nama Kategori</div>
		<div class="col-9">
			<input type="text" class="form-control" id="KPICategoryName" value="<?php echo $KPICategoryName; ?>" required>
		</div>
		<div class="col-3">Jenis</div>
		<div class="col-9">
			<select class="form-control" id="jenis" name="jenis">
				<?php
					for($i=0; $i<2; $i++){
						$selected = '';
						if($listjenis[$i]==$jenis){
							$selected = 'selected';
						}
				?>
						<option value="<?php echo $listjenis[$i]; ?>" <?php echo $selected; ?>><?php echo $listjenis[$i]; ?></option>
				<?php
					}
				?>
			</select>
		</div>
		<div class="col-3">Aktif</div>
		<div class="col-9">
			<input type="checkbox" value="1" name="aktif" id="aktif" <?php if($IsActive==1){ echo 'checked'; }else if($proses=='add'){ echo 'checked'; } ?>>
		</div>
		<div class="col-12 p-5" id="msgerr" style="background-color: #dc3535; color: #FFFFFF; display:none"></div>
		<div class="col-12 text-right">
			<?php
				if($proses=='view'){
					if($_SESSION["can_update"] == 1){
			?>
						<a href="<?php echo site_url('Masterkpicategoryv2/edit/'.$EKPICategoryID); ?>">
							<button type="button" class="btn btn-primary-dark">
								EDIT
							</button>
						</a>
			<?php
					}
				}else{
			?>
					<button type="button" class="btn btn-primary-dark" onclick="saveData()">
						SAVE
					</button>
			<?php
				}
			?>
			<a href="<?php echo $batal; ?>" onclick="return confirm('Apakah anda yakin ingin kembali halaman kesebelumnya?')">
				<button type="button" class="btn btn-danger-dark">
					KEMBALI
				</button>
			</a>
		</div>
		<table class="table">
			<thead>
				<tr>
					<td>
						<button class="btn btn-primary-dark" id="add-element">+</button>
					</td>
					<td>
						<b>
							Division
						</b>
					</td>
					<td colspan="2" width="350px">
						<b>
							Div Head
						</b>
					</td>
					<td>
						<b>
							Start Date
						</b>
					</td>
					<td class="text-center" width="50px">
						<b>
							Active
						</b>
					</td>
				</tr>
			</thead>
			<tbody id="element-container">
				<?php
					if($proses=='view' || $proses=='edit'){
						$no=0;

						if(!empty($body)){
							foreach($body as $a){
								if($proses=='edit'){
									$urut="'tr".$no."'";
									$element='onclick="deletedetail('.$urut.')"';
								}else{
									$element='';
								}
				?>
								<tr class="element" id="tr<?php echo $no; ?>">
									<td>
										<button class="btn btn-danger-dark delete-element" <?php echo $element; ?>>-</button>
									</td>
									<td>
										<select class="form-control" name="division[]" id="division<?php echo $no; ?>" autocomplete="off" onchange="getListDivisi('<?php echo $no; ?>');" required>
											<option value="">Pilih</option>
											<?php
												if($KPICategory!='' && !empty($KPICategory)){
													foreach($KPICategory as $value){
														if($a['DivisionID']==$value['id']){
															$selected = 'selected'; 
														}else{
															$selected = '';
														}
											?>
														<option value="<?php echo $value['id']; ?>" data-userid="<?php echo $value['userid']; ?>" data-name="<?php echo $value['empname']; ?>" data-divisionname="<?php echo $value['name']; ?>" <?php echo $selected; ?> ><?php echo $value['name']; ?></option>
											<?php
													}
												}
											?>
										</select>
									</td>
									<td width="100px">
										<input type="text" class="form-control" id="userid<?php echo $no; ?>" value="" readonly>
									</td>
									<td>
										<input type="text" class="form-control" id="name<?php echo $no; ?>" value="" readonly>
									</td>
									<td>
										<input type="text" class="form-control startdate" name="startdate[]" id="startdate<?php echo $no; ?>" value="<?php echo date('Y-M-d'); ?>">
									</td>
									<td class="text-center">
										<?php
											if($a['aktif']=='1'){
												$checked = 'checked';
											}else{
												$checked = '';
											}
										?>
										<input type="checkbox" name="active[]" id="active<?php echo $no; ?>" value="" readonly <?php echo $checked; ?>>
									</td>
								</tr>
				<?php
							$no++;
							}
						}
					}
				?>
			</tbody>
		</table>
	</div>
</div>

<script type="text/javascript">
	$("#disablingDiv").hide();
	$("#loading").hide();
	
	<?php
		if($proses=='view' || $proses=='edit'){
			if($proses=='edit'){
	?>
				function deletedetail(e){
					var element = document.getElementById(e);
    				element.remove();
				}
	<?php
			}
			for($i=0; $i<count($body); $i++){
				echo 'getListDivisi('.$i.');';
			}
		}
	?>

	function getListDivisi(no=0){
		var select 	= document.getElementById('division'+no);

		var dataId 	= $(select).find(":selected").attr("data-id");
		var userid 	= $(select).find(":selected").attr("data-userid");
		var name 	= $(select).find(":selected").attr("data-name");

		document.getElementById('userid'+no).value=userid;
		document.getElementById('name'+no).value=name;
	};

<?php
	if($proses=='add' || $proses=='edit'){
?>

		const elementContainer = document.getElementById('element-container');
		const addElementButton = document.getElementById('add-element');

		<?php
			if(!empty($body) && count($body)>0){
		?>
				var id = <?php echo count($body); ?>;
		<?php
			}else{
		?>
				var id = 0;
		<?php
			}
		?>
		addElement();
		function addElement() {
			const newElement = document.createElement('tr');
			newElement.classList.add('element');
			newElement.innerHTML = `
			    		<tr>
							<td>
								<button class="btn btn-danger-dark delete-element">-</button>
							</td>
							<td>
								<select class="form-control" name="division[]" id="division`+id+`" autocomplete="off" onchange="getListDivisi('`+id+`');" required>
									<option value="">Pilih</option>
									<?php
										if($KPICategory!=''){
											foreach($KPICategory as $value){
									?>
												<option value="<?php echo $value['id']; ?>" data-userid="<?php echo $value['userid']; ?>" data-name="<?php echo $value['empname']; ?>" data-divisionname="<?php echo $value['name']; ?>"><?php echo $value['name']; ?></option>
									<?php
											}
										}
									?>
								</select>
							</td>
							<td width="100px">
								<input type="text" class="form-control" id="userid`+id+`" value="" readonly>
							</td>
							<td>
								<input type="text" class="form-control" id="name`+id+`" value="" readonly>
							</td>
							<td>
								<input type="text" class="form-control startdate" name="startdate[]" id="startdate`+id+`" value="<?php echo date('Y-M-d'); ?>">
							</td>
							<td class="text-center">
								<input type="checkbox" name="active[]" id="active`+id+`" value="1" checked readonly>
							</td>
						</tr>`;

			 	 elementContainer.appendChild(newElement);

			  	const deleteButton = newElement.querySelector('.delete-element');
			  	deleteButton.addEventListener('click', () => {
			   		elementContainer.removeChild(newElement);
			  	});

			    $('#startdate' + id).datepicker({
			        format: "yyyy-M-dd",
			        autoclose: true
			    });

			  id++;
			}

			addElementButton.addEventListener('click', addElement);


			function saveData() {
				$("#disablingDiv").show();
      			$("#loading").show();
				document.getElementById('msgerr').style.display = 'none';
			    var dataToSave = [];
			    var KPICategoryID = document.getElementById('KPICategoryID').value;
			    var KPICategory = document.getElementById('KPICategory').value;
			    var KPICategoryName = document.getElementById('KPICategoryName').value;    
			    var jenis = document.getElementById('jenis').value;    
			   	var aktif = document.getElementById('aktif');

			   	if(KPICategory=='' || KPICategoryName==''){
			   		document.getElementById('msgerr').innerHTML = 'Data tidak boleh kosong';
					document.getElementById('msgerr').style.display = 'block';
					$("#disablingDiv").hide();
	      			$("#loading").hide();
			   	}else{

					if (aktif.checked) {
						aktif = 1;
					}else{
						aktif = 0;
					}

				    $('.element').each(function() {
				        var DivisionID = $(this).find('select').val();
				        var StartDate = $(this).find('.startdate').val();
				        var DivisionName = $(this).find('select').find(":selected").attr("data-divisionname");

				        dataToSave.push({
				            KPICategoryID: KPICategoryID,
				            KPICategory: KPICategory,
				            KPICategoryName: KPICategoryName,
				            DivisionID: DivisionID,
				            DivisionName: DivisionName,
				            jenis: jenis,
				            aktif: aktif,
				            StartDate: StartDate
				        });
				    });

				    <?php
				    if($proses=='add'){
				        $linkproses = 'prosesadd';
				    }else{
				        $linkproses = 'prosesupdate';
				    }
				    ?>
				 	 $.ajax({
					    type: 'POST', 
					    url: '<?php echo site_url('Masterkpicategoryv2/'.$linkproses); ?>',
					    data: { data: JSON.stringify(dataToSave) },
					    success: function(response) {

					        var responseData = JSON.parse(response);

					        var result = '';
					        var no = 1;
					        
					        for (var i = 0; i < responseData.length; i++) {
					            if (responseData[i]['result'] === 'error') {
					                result += no + '. ' + responseData[i]['message'] + '<br>';
					                no++;
					            }
					        }
					        
					        if (result !== '') {
					            document.getElementById('msgerr').innerHTML = result;
					            document.getElementById('msgerr').style.display = 'block';
					        } else {
					            alert("Data berhasil di simpan");
					            window.location.href = '<?php echo site_url('Masterkpicategoryv2/view'); ?>/'+responseData[0]['message'];
					        }
					        $("#disablingDiv").hide();
	      					$("#loading").hide();
					    },
					    error: function(xhr, status, error) {
					    	$("#disablingDiv").hide();
	      					$("#loading").hide();
					        alert('AJAX Error:', status, error)
					    }
					});
				}

			}

<?php
	}else{
?>
		var inputElements = document.querySelectorAll('input');

		for (var i = 0; i < inputElements.length; i++) {
		    inputElements[i].readOnly = true;
		}

	    var selectElements = document.querySelectorAll('select');
	    
	    for (var i = 0; i < selectElements.length; i++) {
	        selectElements[i].disabled = true;
	    }

		var checkboxElements = document.querySelectorAll('input[type="checkbox"]');

		for (var i = 0; i < checkboxElements.length; i++) {
		    checkboxElements[i].disabled = true;
		}

<?php
	}
?>

</script>
<?php
	}
?>