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

</style>

<div class="container">
	<h3><center><b>FOTO DISPLAY PRODUK</b></center></h3>
	
	<div class="row">
		<div class="col-sm-6">
			<table align="center" cellpadding="5" width="100%">
				<tr>
					<td scope="col" valign="top" width="30%">Cabang</td>
					<td scope="col" valign="top">: <?php echo $header['Cabang'] ?></td>
				</tr>
				<tr>
					<td scope="col" valign="top" width="30%">Nama MD</td>
					<td scope="col" valign="top">: <?php echo $header['NamaMD'] ?></td>
				</tr>
				<tr>
					<td scope="col" valign="top" width="30%">Jumlah Foto Scan QR Code</td>
					<td scope="col" valign="top">: <?php echo $header['fd'] ?></td>
				</tr>
				<?php if($header['awal']!=''){ ?>
				<tr>
					<td scope="col" valign="top" width="30%">Periode</td>
					<td scope="col" valign="top">: <?php echo $header['awal'].' sd '.$header['akhir'] ?></td>
				</tr>
				<?php } ?>
			</table>
		</div>
		<div class="col-sm-6">
		</div>
	</div>
	<br>
	<small>
		<div style="overflow-y: hidden;overflow-x:scroll">
			<table id="myTable" class="table table-bordered">
				<thead>
					<tr>
						<th scope="col" width="5%">NO</th>
						<th scope="col" width="40%">NAMA TOKO</th>
						<th scope="col" width="25%">FOTO BUKTI<br>SCAN QR CODE</th>
						<th scope="col" width="30%">CDATE</th>
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
								<?php 
								$id_foto = str_replace('https://drive.google.com/open?id=','',$row->FotoScan);
								?>
								<a href="javascript:LihatFoto('<?php echo $row->NamaToko ?>','<?php echo $id_foto ?>')"><img src="<?php echo base_url('images/btnqrcode.png') ?>" height="20" width="20"></a>
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
			"pageLength": 50
		});
	});
	
	function LihatFoto(nama_toko,id_foto){
		$('.modal-title').html(nama_toko);
		$('.modal-body').html('Loading...');
		$('.modal-body').html('<img src="https://drive.google.com/uc?export=view&id='+id_foto+'" class="img-display" width="100%">');
		$('#myModal').modal('show');
	}
</script>
