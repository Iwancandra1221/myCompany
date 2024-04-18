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
	<div class="page-title">Import Achievement KPI</div>
	<form method="POST" action="<?php echo base_url() ?>Achievementkpisalesman/import" enctype="multipart/form-data">
		<!--div class="border20 p20"-->
		<div class="row">
			<div class="col-3">File Excel</div>
			<div class="col-8 col-m-8">
				<div class="input-group">
					<input type="hidden" name="kajul" value="<?php echo $kajul ?>">
					<input type="file" name="excel" class="form-control" accept=".xlsx" required>
					<span class="input-group-btn">
						<button name="submit" class="btn btn-dark" type="submit">Preview</button>
					</span>
				</div>
			</div>
		</div>
		<!--/div-->
	</form>
	
	<?php
	if(ISSET($error)){
		echo "<center><em>".$error."</em></center>";
	}
	?>
	
	<?php if(ISSET($data)) { ?>
		<?php if(count($data)>0) { ?>
			<?php $nama_bulan = array('','JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER'); ?>
			<form id="form_import" method="POST" action="<?php echo base_url() ?>Achievementkpisalesman/import" enctype="multipart/form-data" style="margin-top:20px">
				
				<input type="hidden" name="save_import" value="1">
				<input type="hidden" name="kajul" value="<?php echo $kajul ?>">
				<input type="hidden" name="kategori" value="<?php echo $kategori ?>">
				<input type="hidden" name="wilayah" value="<?php echo $wilayah ?>">
				<input type="hidden" name="tahun" value="<?php echo $tahun ?>">
				<input type="hidden" name="bulan" value="<?php echo $bulan ?>">
				<input type='hidden' name='data' value='<?php echo json_encode($data) ?>'>
				
				<table style="border-collapse: separate; border-spacing: 5px 5px;">
					<tr><td>Wilayah</td><td>: <b><?php echo $wilayah ?></b></td></tr>
					<tr><td>Kategori</td><td>: <b><?php echo $kategori ?></b></td></tr>
					<tr><td>Periode</td><td>: <b><?php echo $nama_bulan[$bulan].' '.$tahun ?></b></td></tr>
					<tr><td>Filename</td><td>: <b><?php echo $filename ?></b></td></tr>
				</table>
				
				<div style="overflow-x:scroll">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>Kode Salesman/<br>Nama Salesman</th>
							<?php
								foreach($header as $i => $h){
								if($h=='') continue;
								if($i<7) continue;
							?>
								<th><?php echo $h ?></th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php
							// 0	Tahun
							// 1	Bulan
							// 2	Cabang
							// 3	Kode Kategori KPI
							// 4	Nama Kategori KPI
							// 5	Kode Salesman
							// 6	Nama Salesman
							// 7	Kode KPI
							// 8	Nama KPI
							// 9	Target
							// 10	Bobot
							// 11	AchievementWeek1
							// 12	AchievementWeek2
							// 13	AchievementWeek3
							// 14	AchievementWeek4
							// 15	AchievementWeek5
							foreach($data as $hd) {
								foreach($hd['dt'] as $i => $r) {
									echo '<tr>';
									if($i==0){
										echo '<td rowspan="'.count($hd['dt']).'"><b>'.$hd['kode_salesman'].'</b><br>'.$hd['nama_salesman'].'</td>';
									}
									echo '<td>'.$r['KPICode'].'</td>';
									echo '<td>'.$r['KPIName'].'</td>';
									echo '<td>'.$r['KPIUnit'].'</td>';
									echo '<td align="right">'.$r['KPITarget'].'</td>';
									echo '<td align="right">'.$r['KPIBobot'].'</td>';
									echo '<td align="right">'.number_format($r['AcvWeek1']).'</td>';
									echo '<td align="right">'.number_format($r['AcvWeek2']).'</td>';
									echo '<td align="right">'.number_format($r['AcvWeek3']).'</td>';
									echo '<td align="right">'.number_format($r['AcvWeek4']).'</td>';
									if($week>4){
										echo '<td align="right">'.number_format($r['AcvWeek5']).'</td>';
									}
									if($week>5){
										echo '<td align="right">'.number_format($r['AcvWeek6']).'</td>';
									}
									echo '</tr>';
								}
							}
						?>
					</tbody>
				</table>
				</div>
				<center><input type="submit" name="save" value="IMPORT" class="btn btn-dark" ></center>
			</form>
			<?php
			}
			else{
				echo "<center><em>Filename: ".$filename."</em><br> Tidak ada data.</center>";
			}
		}
	?>
</div>

<script>
	$(document).ready(function() {
		$("#form_import").submit(function() {
			if (confirm("Apakah data sudah benar?")){
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
					dataType  : 'json',
					success   : function(data){
						$('.loading').hide();
						console.log(JSON.stringify(data));
						// console.log(data);
						if(data.result=='success'){
							alert('SUCCESS. Achievement KPI berhasil diimport! Refresh kembali untuk data terbaru!');
							window.top.close();
						}
						else{
							alert('FAILED. '+data.error);
						}
					}
				});
			}
			event.preventDefault();
		});
		
	});
</script>
