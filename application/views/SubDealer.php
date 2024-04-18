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
	<big><center>Sub Dealer</center></big>
	<div class="row">
		<div class="col-sm-6">
			<table align="center" cellpadding="5" width="100%">
				<tr>
					<td valign="top" width="30%">Cabang</td>
					<td valign="top">: <?php echo $header['Cabang'] ?></td>
				</tr>
				<tr>
					<td valign="top" width="30%">Provinsi</td>
					<td valign="top">: <?php echo $header['Provinsi'] ?></td>
				</tr>
				<tr>
					<td valign="top" width="30%">Kotamadya/Kabupaten</td>
					<td valign="top">: <?php echo $header['Kotamadya'] ?></td>
				</tr>
				<tr>
					<td valign="top" width="30%">Nama MD</td>
					<td valign="top">: <?php echo $header['NamaMD'] ?></td>
				</tr>
				<tr>
					<td valign="top" width="30%">Jumlah Subdealer</td>
					<td valign="top">: <?php echo $header['sd'] ?></td>
				</tr>
				<tr>
					<td valign="top" width="30%">Jumlah MarketSurvey</td>
					<td valign="top">: <?php echo $header['ms'] ?></td>
				</tr>
				<?php if($header['awal']!=''){ ?>
				<tr>
					<td valign="top" width="30%">Periode</td>
					<td valign="top">: <?php echo $header['awal'].' sd '.$header['akhir'] ?></td>
				</tr>
				<?php } ?>
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
						<th>TITLE</th>
						<th>NAMA PEMILIK</th>
						<th>NAMA PANGGILAN</th>
						<th>TERDAFTAR MISHIRIN</th>
						<th>EMAIL LOGIN MISHIRIN</th>
						<th>EMAIL TOKO</th>
						<th>NO. HP</th>
						<th>NO. WHATSAPP</th>
						<th>NO. TELP TOKO</th>
						<th>ALAMAT TOKO</th>
						<th>KELURAHAN</th>
						<th>KECAMATAN</th>
						<th>KODE POS</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$no = 0;
						// print_r($sub_dealer);
						foreach($sub_dealer as $row){
							// print_r($row);die;
							$no++;
							$id_foto = str_replace('https://drive.google.com/open?id=','',$row->FotoTampakDepan);
							$row->NamaToko = htmlspecialchars($row->NamaToko);
						?>
						<tr>
							<td><?php echo $no ?></td>
							
							<td><?php echo $row->NamaToko ?>
								<br>
								<br>
								<?php if($row->FotoTampakDepan!='') { ?>
								<a href="javascript:LihatFoto('<?php echo $row->NamaToko ?>','<?php echo $id_foto ?>')"><img src="<?php echo base_url('images/btnFoto.png') ?>"></a>
								<?php } ?>
								<?php if($row->GeoCode!='') { ?>
								<a href="https://www.google.com/maps/search/?api=1&query=<?php echo $row->GeoCode ?>" target="_blank"><img src="<?php echo base_url('images/btnMarker.png') ?>"></a>
								<?php  } ?>
								<?php if(trim($row->NamaMD)==$_SESSION["logged_in"]["username"] && $row->AllowEdit==1){ ?>
								<a href="Edit/<?php echo $row->SubDealerId ?>"><img src="<?php echo base_url('images/btnEdit.png') ?>"></a>
								<?php } ?>
							</td>
							<td><?php echo $row->TitleToko ?></td>
							<td><?php echo $row->NamaPemilik ?></td>
							<td><?php echo $row->NamaPanggilan ?></td>
							<td><?php echo $row->TerdaftarDiMishirin ?></td>
							<td><?php echo $row->EmailLoginMishirin ?></td>
							<td><?php echo $row->EmailToko ?></td>
							<td><?php echo $row->NoHP ?></td>
							<td><?php echo $row->NoWhatsapp ?></td>
							<td><?php echo $row->NoTelpToko ?></td>
							<td><?php echo $row->AlamatToko ?></td>
							<td><?php echo $row->Kelurahan ?></td>
							<td><?php echo $row->Kecamatan ?></td>
							<td><?php echo $row->KodePos ?></td>
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
		$('#myTable').DataTable();
	});
	
	function LihatFoto(nama_toko,id_foto){
		$('.modal-title').html(nama_toko);
		$('.modal-body').html('Loading...');
		$('.modal-body').html('<img src="https://drive.google.com/uc?export=view&id='+id_foto+'" width="100%">');
		$('#myModal').modal('show');
	}
</script>
