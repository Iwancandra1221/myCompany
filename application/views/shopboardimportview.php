<style type="text/css">
	.row {
    line-height:30px; 
    vertical-align:middle;
    clear:both;
	}
	.row-label, .row-input {
    float:left;
	}
	/* .row-label {
    padding-left: 15px;
    width:180px;
	} */
	.row-input {
    width:420px;
	}
</style>
<div class="container">
	<div class="page-title">Import Data Shopboard</div>
	<form method="POST" action="<?php echo base_url() ?>shopboard/import" enctype="multipart/form-data">
		<!--div class="border20 p20"-->
		<div class="row">
			<div class="col-3">File Excel</div>
			<div class="col-8 col-m-8">
				<div class="input-group">
					<input type="hidden" name="kajul" value="<?php echo (ISSET($_GET['kajul'])?$_GET['kajul']:'') ?>">
					<input type="file" name="excel" class="form-control" accept=".xlsx" required>
					<span class="input-group-btn">
						<button name="submit" class="btn btn-dark" type="submit">Preview</button>
					</span>
				</div>
			</div>
		</div>
		<!--/div-->
	</form>
	
	<?php if((ISSET($error)) && ($error!='')) { ?>
			<center>
				<?php echo $error; ?>
			</center>
	<?php } elseif(ISSET($result)){ ?>
		<form id="form_import" method="POST" action="<?php echo base_url() ?>shopboard/import" enctype="multipart/form-data" style="margin-top:20px">
			
			<input type="hidden" name="save_import" value="1">
			<input type='hidden' name='data' value='<?php echo json_encode($result) ?>'>
			
			<div style="overflow-x:scroll">
			<table class="table table-bordered table-auto" style="width:1800px !important">
				<thead>
					<tr>
						<th rowspan="2" style="min-width:20px">No</th>
						<th rowspan="2" style="min-width:120px">Cabang</th>
						<th rowspan="2" style="min-width:120px">Wilayah</th>
						<th rowspan="2" style="min-width:200px">Nama Toko</th>
						<th rowspan="2" style="min-width:200px">Alamat</th>
						<th rowspan="2" style="min-width:100px">Kota</th>
						<th rowspan="2" style="min-width:200px">Supplier</th>
						<?php
							for($p=0;$p<=$jum_po-1;$p++){
								echo '<th colspan="6"><center>PO Ke-'.($p+1).'</center></th>';
							}
						?>
					</tr>
					<tr>
						<?php
							for($p=0;$p<=$jum_po-1;$p++){
								echo '<th style="min-width:100px">Merk</th>';
								echo '<th style="min-width:100px">Ukuran</th>';
								echo '<th style="min-width:100px">No PO</th>';
								echo '<th style="min-width:100px">OK.Pajak</th>';
								echo '<th style="min-width:100px">Tgl Awal</th>';
								echo '<th style="min-width:100px !important">Tgl Akhir</th>';
							}
						?>
					</tr>
				</thead>
				<tbody>
					<?php
						$no = 0;
						foreach($result as $row) {
							$no++;
							echo '<tr>';
							echo '<td>'.$no.'</td>';
							echo '<td>'.$row['cabang'].'</td>';
							echo '<td>'.$row['wilayah'].'</td>';
							echo '<td>'.$row['nama_toko'].'</td>';
							echo '<td>'.$row['alamat'].'</td>';
							echo '<td>'.$row['kota'].'</td>';
							echo '<td>'.$row['supplier'].'</td>';
							for($p=0;$p<=$jum_po-1;$p++){
								if(ISSET($row['po'][$p])){
									$merk = implode('<br>',$row['po'][$p]['merk']);
									$ukuran = implode('<br>',$row['po'][$p]['ukuran']);
									echo '<td>'.$merk.'</td>';
									echo '<td>'.$ukuran.'</td>';
									echo '<td>'.$row['po'][$p]['no_po'].'</td>';
									echo '<td>'.$row['po'][$p]['pajak'].'</td>';
									echo '<td>'.$row['po'][$p]['periode_start'].'</td>';
									echo '<td>'.$row['po'][$p]['periode_end'].'</td>';
								}
								else{
									echo '<td></td><td></td><td></td><td></td><td></td><td></td>';
								}
							}
							echo '</tr>';
						}
					?>
				</tbody>
			</table>
			</div>
			<br>
			<center>
				<big><input type="checkbox" id="checklist" onclick="javascript:chklist()"> Data telah diperiksa dan sudah benar.</big>
				<br>
				<br>
				<input type="submit" name="save" id="btn-import" value="IMPORT" class="btn btn-dark" disabled >
			</center>
		</form>
	<?php
		}
	?>
</div>

<script>
	$(document).ready(function() {
		$("#form_import").submit(function() {
			// if (confirm("Apakah data sudah benar?")){
				$('.loading').show();
				var act = $(this).attr('action');
				var data = new FormData(this);
				$.ajax({
					data      	: data,
					url			: act,
					cache		: false,
					contentType	: false,
					processData	: false,
					type		: 'POST',
					success   : function(result){
						$('.loading').hide();
						console.log(JSON.stringify(data));
						// console.log(data);
						if(result=='success'){
							alert('SUCCESS. Data shopboard berhasil diimport! Refresh kembali untuk data terbaru!');
							window.location.href = '<?php echo base_url() ?>shopboard';
						}
						else{
							alert('FAILED. '+result);
						}
					}
				});
			// }
			event.preventDefault();
		});
		
	});
	
	function chklist(){
		var c = $('#checklist').is(":checked");
		// alert(c);
		$('#btn-import').prop('disabled', !c);
	}
	
</script>
