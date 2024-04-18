
<style>
	.table{
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
	
	.dataTables_length,.dataTables_filter,.dataTables_info,.dataTables_paginate{
	float:left;
	width:50%;
	}
	
	.paginate_button{
	color:blue;
	padding:3px;
	}
</style>
<div class="container">
	<center>
		<big><center>Approval Sub Dealer</center></big>
		<small>Data Survey Market yang perlu di approve setelah diedit oleh MD</small>
	</center>
	<table id="myTable" class="table table-bordered">
		<thead>
			<tr>
				<th scope="col" width="5px">NO</th>
				<th scope="col">PROVINSI</th>
				<th scope="col">KOTAMADYA/KABUPATEN</th>
				<th scope="col">KECAMATAN</th>
				<th scope="col">KELURAHAN</th>
				<th scope="col">NAMA TOKO</th>
				<th scope="col">TITLE</th>
				<th scope="col">NAMA PEMILIK</th>
				<th scope="col">NAMA PANGGILAN</th>
				<th scope="col">VIEW</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$no = 0;
				// print_r($sub_dealer);
				foreach($sub_dealer as $row){
					// print_r($row);die;
					$no++;
					
				?>
				<tr>
					<td><?php echo $no ?></td>
					
					<td><?php echo $row->Provinsi ?></td>
					<td><?php echo $row->KotamadyaKabupaten ?></td>
					<td><?php echo $row->Kecamatan ?></td>
					<td><?php echo $row->Kelurahan ?></td>
					<td><?php echo $row->NamaToko ?></td>
					<td><?php echo $row->TitleToko ?></td>
					<td><?php echo $row->NamaPemilik ?></td>
					<td><?php echo $row->NamaPanggilan ?></td>
					<td><a href="<?php echo base_url('SubDealer/Approval/'.$row->SubDealerId) ?>" style="color:blue"><b>VIEW</b></a></td>
				</tr>
				
				<?php
				}
				
				
			?>
		</tbody>
	</table>
</div>

<script>
	$(document).ready( function () {
		$('#myTable').DataTable();
	});
</script>