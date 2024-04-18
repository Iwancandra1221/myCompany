<script>
var mode = '';

$(document).ready(function(){
	$('#Aktif').on('change', function() {
		var aktif = $(this).val();
	});		
});
</script>

<div class="manualForm">
	<fieldset>
		<legend>
			<div class="actionBar">	
				<a href="<?php echo site_url('group/add');?>">
					<input type="button" value="Add"  class="btnAdd">
				</a>
			</div>
		</legend>
		<div class="clearfix" style="height:25px;"></div>
		<div id="fieldsetTab2" class="fieldsetTab">
			<div class="row">
				<div class="col-3 col-m-4">AKTIF</div>
				<div class="col-3 col-m-4">
					<select name="Aktif" id="Aktif">
						<option value="ALL">ALL</option>
						<option value="Y">AKTIF</option>
						<option value="N">TIDAK AKTIF</option>
					</select>
				</div>
				<div class="col-6 col-m-4"></div>
			</div>
		</div>

		<div class="clearfix" style="height:25px;"></div>
		<div id="fieldsetTab1" class="fieldsetTab">
			<table id="tblData" class="display" width="100%">
				<thead>
				    <tr>
				      <th id="ColHD1">Tahun</th>
				      <th id="ColHD2">Bulan Awal</th>
				      <th id="ColHD3">Bulan Akhir</th>
				      <th id="ColHD4">Flag</th>
				      <th id="ColHD5">Divisi</th>
				      <th id="ColHD6">Merk<br>Jenis Barang<br>SubKategori</th>
				      <th id="ColHD7">Kode Barang</th>
				      <th id="ColHD8">Action</th>
				    </tr>
			  </thead>
			  <tbody>
			  	<?php for($t=0;$t<count($List);$t++) {
			  		echo("<tr");
			  		echo("	<td>".$List["Tahun"][$t]."</td>");
			  		echo("	<td>".$List["BulanAwal"][$t]."</td>");
			  		echo("	<td>".$List["BulanAkhir"][$t]."</td>");
			  		echo("	<td>".$List["Flag"][$t]."</td>");
			  		echo("	<td>".$List["Divisi"][$t]."</td>");
			  		echo("	<td>".$List["Merk"][$t]."</td>");
			  		echo("	<td>".$List["KodeBarang"][$t]."</td>");
			  		echo("	<td></td>");
			  		echo("</tr>");
			  	}?>
			  </tbody>
			</table>
		</div>
	</fieldset>
</div>
<?php echo form_open(); ?>
<?php echo form_close(); ?> 
