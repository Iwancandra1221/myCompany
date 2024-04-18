<style type="text/css">
table, tr ,td, th{
	padding: 10px;
}
</style>
<div class="container">
	<div class="page-title">
		LAPORAN JUAL - RETUR DEALER (PARENT DIVISI)
	</div>
	<div class="form-container" >  

  		<div class="row">
        	<div class="col-12" align="center">
         		<table border="0" width="80%">
         			<tr>
         				<td width="150px">
         					Periode
         				</td>
         				<td>
         					<input type="text" id="from" class="form-control" value="<?php echo date('Y-m-01'); ?>">
         				</td>
         				<td align="center" width="50px">
         					S/D
         				</td>
         				<td>
         					<input type="text" id="until" class="form-control" value="<?php echo date('Y-m-d'); ?>">
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Divisi
         				</td>
         				<td colspan="3">
         					<select id="divisi" class="form-control">
         						<?php
         							foreach ($divisi as $key => $d) {
         						?>
         								<option value="<?php echo $d['ParentDiv']; ?>"><?php echo $d['ParentDiv']; ?></option>
         						<?php
         							}
         						?>
         					</select>
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Partner Type
         				</td>
         				<td colspan="3">
         					<select id="partner_type" class="form-control">
         						<?php
         							foreach ($partner_type as $key => $pt) {
         						?>
         								<option value="<?php echo $pt['partner_type_code']; ?>"><?php echo $pt['partner_type']; ?></option>
         						<?php
         							}
         						?>
         					</select>
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Wilayah
         				</td>
         				<td colspan="3">
         					<select id="wilayah" class="form-control">
         						<?php
         							foreach ($wilayah as $key => $w) {
         						?>
         								<option value="<?php echo $w['Nama_Wilayah']; ?>"><?php echo $w['Nama_Wilayah']; ?></option>
         						<?php
         							}
         						?>
         					</select>
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Kategori Barang
         				</td>
         				<td colspan="3">
         					<select id="kategori" class="form-control">
         						<option value="ALL">ALL</option>
         						<option value="PRODUCT">PRODUCT</option>
         						<option value="SPAREPART">SPAREPART</option>
         					</select>
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Tipe Faktur
         				</td>
         				<td colspan="2">
         					<select id="tipe_faktur" class="form-control">
         						<?php
         							foreach ($tipe_faktur as $key => $tp) {
         								if($tp['Tipe_Faktur']!=='ALL'){
         									if($tp['Tipe_Faktur']=='R'){
         						?>
         										<option value="<?php echo $tp['Tipe_Faktur']; ?>" selected><?php echo $tp['Tipe_Faktur']; ?></option>
         						<?php
         									}else{
         						?>
         										<option value="<?php echo $tp['Tipe_Faktur']; ?>"><?php echo $tp['Tipe_Faktur']; ?></option>
         						<?php
         									}
         								}
         							}
         						?>
         					</select>
         				</td>
         				<td>
         					<input type="checkbox" value="1" id="perkategori"> Perkategori ALL/Silver
         				</td>
         			</tr>
         			<tr>
         				<td colspan="2" align="center">
         					<input type="radio" id="printper" name="printper" value="1" checked> Dealer
         				</td>
         				<td colspan="2" align="center">
         					<input type="radio" id="printper" name="printper" value="2"> Kota
         				</td>
         			</tr>
         			<tr>
         				<td valign="top">Report</td>
         				<td colspan="3">
         					<input type="radio" id="report" name="report" value="1" checked> Laporan Dealer Per Parent Divisi<br>
         					<input type="radio" id="report" name="report" value="2"> Laporan Parent PerDealer<br>
         					<input type="radio" id="report" name="report" value="3"> Laporan Parentdiv PerDealer PerDivisi
         				</td>
         			</tr>
         			<tr>
         				<td colspan="4" align="center">
         					<button class="btn btn-default" onclick="print_data('pdf');">
         						PDF
         					</button>
         					<button class="btn btn-default" onclick="print_data('excel');">
         						Excel
         					</button>
         				</td>
         			</tr>
         		</table>
        	</div> 
      </div> 
  </div>

</div>


<script type="text/javascript">

	$('#from,#until').datepicker({
		format: "yyyy-mm-dd",
		autoclose: true
	});

	function print_data(e){
		var from = document.getElementById('from').value;
		var until = document.getElementById('until').value;
		var divisi = document.getElementById('divisi').value;
		var partner_type = document.getElementById('partner_type').value;
		var wilayah = document.getElementById('wilayah').value;
		var kategori = document.getElementById('kategori').value;
		var tipe_faktur = document.getElementById('tipe_faktur').value;

		var perkategori = document.getElementById('perkategori');
		if (perkategori.checked == true){
		   	perkategori='Y';
		}else{
			perkategori='N';
		}

		var printper = $("input[name='printper']:checked").val(); 


		var report = $("input[name='report']:checked").val(); 

		window.open('<?php echo site_url('Reportjualreturdealerparentdiv'); ?>/'+e+'/'+from+'/'+until+'/'+divisi+'/'+partner_type+'/'+wilayah+'/'+kategori+'/'+tipe_faktur+'/'+perkategori+'/'+printper+'/'+report,'_blank');
	}

</script>