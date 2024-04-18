<style>
	table{
	font-size:12px;
	}
	
	.table tr{
	color:black;
	font-style: normal;
	text-align:left;
	}
	
	.table tr:nth-child(even) {background-color: #f8f8f8;}
	
	.table tr td{
	padding:2px;
	}

	.img-display {
		width:auto;
		max-width: 100%;
		max-height:640px;
		margin:0;
		padding:auto;
		display: flex;
    	justify-content: center;
	}	
</style>

<div class="container">
	<h3><center><b>FOTO DISPLAY PRODUK</b></center></h3>
	<?php
		$TotalCOSAN = 0;
		$TotalMIYAKO= 0;
		$TotalMICOOK= 0;
		$TotalRINNAI= 0;
		$TotalSHIMIZU=0;

		foreach($data as $row){
			$brand = strtoupper(trim($row->Brand_display_produk));

			if($brand=="COSAN" || $brand=="COSANITARY" || $brand=="CO&SANITARY") {
				$TotalCOSAN++;
			} else if($brand=="MIYAKO") {
				$TotalMIYAKO++;
			} else if($brand=="MICOOK") {
				$TotalMICOOK++;
			} else if($brand=="RINNAI") {
				$TotalRINNAI++;
			} else if($brand=="SHIMIZU") {
				$TotalSHIMIZU++;
			}
		}
	?>
	<div class="row">
		<div class="col-sm-6">
			<table align="center" cellpadding="5" width="100%">
				<tr>
					<td valign="top" width="30%">Cabang</td>
					<td valign="top">: <?php echo $header['Cabang'] ?></td>
				</tr>
				<tr>
					<td valign="top" width="30%">Nama MD</td>
					<td valign="top">: <?php echo $header['NamaMD'] ?></td>
				</tr>
				<tr>
					<td valign="top" width="30%">Jumlah Foto Display</td>
					<td valign="top">: <?php echo $header['fd'] ?></td>
				</tr>
				<?php if($header['awal']!=''){ ?>
				<tr>
					<td valign="top" width="30%">Periode</td>
					<td valign="top">: <?php echo $header['awal'].' sd '.$header['akhir'] ?></td>
				</tr>
				<?php } ?>
			</table>
		</div>
		<div class="col-sm-6">
			<table align="center" cellpadding="5" width="100%">
				<tr>
					<td valign="top" width="30%">Total COSAN</td>
					<td valign="top">: <b><?php echo $TotalCOSAN ?></b></td>
				</tr>
				<tr>
					<td valign="top" width="30%">Total MIYAKO</td>
					<td valign="top">: <b><?php echo $TotalMIYAKO ?></b></td>
				</tr>
				<tr>
					<td valign="top" width="30%">Total MICOOK</td>
					<td valign="top">: <b><?php echo $TotalMICOOK ?></b></td>
				</tr>
				<tr>
					<td valign="top" width="30%">Total RINNAI</td>
					<td valign="top">: <b><?php echo $TotalRINNAI ?></b></td>
				</tr>
				<tr>
					<td valign="top" width="30%">Total SHIMIZU</td>
					<td valign="top">: <b><?php echo $TotalSHIMIZU ?></b></td>
				</tr>
			</table>
		</div>
	</div>
	<br>
	<small>
		<div style="overflow-y: hidden;overflow-x:scroll">
			<table id="myTable" class="table table-bordered">
				<thead>
					<tr>
						<th width="5px">NO</th>
						<th>NAMA TOKO</th>
						<th>FOTO DISPLAY<br>COSAN</th>
						<th>FOTO DISPLAY<br>MIYAKO</th>
						<th>FOTO DISPLAY<br>MICOOK</th>
						<th>FOTO DISPLAY<br>RINNAI</th>
						<th>FOTO DISPLAY<br>SHIMIZU</th>
						<th>CDATE</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$no = 0;

						foreach($data as $row){
							// print_r($row);die;
							$no++;
							$id_foto = str_replace('https://drive.google.com/open?id=','',$row->FotoTampakDepan);
							$row->NamaToko = htmlspecialchars($row->NamaToko);

							$brand = strtoupper(trim($row->Brand_display_produk));
							$array = json_decode(json_encode($row), true);

					?>
						<tr>
							<td><?php echo $no ?></td>
							
							<td><?php echo $row->NamaToko ?>
								<?php if($row->FotoTampakDepan!='') { ?>
								<a href="javascript:LihatFoto('<?php echo $row->NamaToko ?>','<?php echo $id_foto ?>')"><img src="<?php echo base_url('images/btnFoto.png') ?>"></a>
								<?php } ?>
								<?php if($row->GeoCode!='') { ?>
								<a href="https://www.google.com/maps/search/?api=1&query=<?php echo $row->GeoCode ?>" target="_blank"><img src="<?php echo base_url('images/btnMarker.png') ?>"></a>
								<?php  } ?>
							</td>
							<td>
								<?php if($brand=="COSAN" || $brand=="COSANITARY" || $brand=="CO&SANITARY") {
									foreach($cols as $c) {
										if ($array[$c->COLNAME]!=""){
											$id_foto = str_replace('https://drive.google.com/open?id=','',$array[$c->COLNAME]);
											$nm_foto = str_replace('Foto_Display_Produk_','',$c->COLNAME);
								?>
											<a href="javascript:LihatFoto('<?php echo $row->NamaToko ?>','<?php echo $id_foto ?>')"><?php echo($nm_foto)?></a>
								<?php	}
									}
								}?>
							</td>
							<td>
								<?php if($brand=="MIYAKO") {
									foreach($cols as $c) {
										if ($array[$c->COLNAME]!=""){
											$id_foto = str_replace('https://drive.google.com/open?id=','',$array[$c->COLNAME]);
											$nm_foto = str_replace('Foto_Display_Produk_','',$c->COLNAME);
								?>
											<a href="javascript:LihatFoto('<?php echo $row->NamaToko ?>','<?php echo $id_foto ?>')"><?php echo($nm_foto)?></a>
								<?php	}
									}
								}?>
							</td>
							<td>
								<?php if($brand=="MICOOK") {
									foreach($cols as $c) {
										if ($array[$c->COLNAME]!=""){
											$id_foto = str_replace('https://drive.google.com/open?id=','',$array[$c->COLNAME]);
											$nm_foto = str_replace('Foto_Display_Produk_','',$c->COLNAME);
								?>
											<a href="javascript:LihatFoto('<?php echo $row->NamaToko ?>','<?php echo $id_foto ?>')"><?php echo($nm_foto)?></a>
								<?php	}
									}
								}?>
							</td>
							<td>
								<?php if($brand=="RINNAI") {
									foreach($cols as $c) {
										if ($array[$c->COLNAME]!=""){
											$id_foto = str_replace('https://drive.google.com/open?id=','',$array[$c->COLNAME]);
											$nm_foto = str_replace('Foto_Display_Produk_','',$c->COLNAME);
								?>
											<a href="javascript:LihatFoto('<?php echo $row->NamaToko ?>','<?php echo $id_foto ?>')"><?php echo($nm_foto)?></a>
								<?php	}
									}
								}?>
							</td>
							<td>
								<?php if($brand=="SHIMIZU") {
									foreach($cols as $c) {
										if ($array[$c->COLNAME]!=""){
											$id_foto = str_replace('https://drive.google.com/open?id=','',$array[$c->COLNAME]);
											$nm_foto = str_replace('Foto_Display_Produk_','',$c->COLNAME);
								?>
											<a href="javascript:LihatFoto('<?php echo $row->NamaToko ?>','<?php echo $id_foto ?>')"><?php echo($nm_foto)?></a>
								<?php	}
									}
								}?>
							</td>
							<td><?php echo date("d-M-Y H:i:s", strtotime($row->GFormTimeStamp)) ?></td>
						</tr>
						
					<?php
						}
					?>
				</tbody>
			</table>
		</div>
	</small>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"></h4>
			</div>
			<div class="modal-body">
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready( function () {
		$('#myTable').DataTable({
			"pageLength": 20
		});
	});
	
	function LihatFoto(nama_toko,id_foto){
		$('.modal-title').html(nama_toko);
		$('.modal-body').html('Loading...');
		$('.modal-body').html('<img src="https://drive.google.com/uc?export=view&id='+id_foto+'" class="img-display">');
		$('#myModal').modal('show');
	}
</script>
