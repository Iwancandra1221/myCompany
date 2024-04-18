<style type="text/css">
table, tr ,td, th{
	padding: 10px;
}
</style>
<div class="container">
	<div class="page-title">
		LAPORAN BUKU HARIAN NKND
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
         					<input type="text" id="from" class="form-control" value="<?php echo date('Y-m-d'); ?>">
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
         					Type Transaksi
         				</td>
         				<td colspan="3">
         					<select id="type_transaksi" class="form-control">
         						<option value="NK">NK</option>
         						<option value="ND">ND</option>
         						<option value="NK SVC">NK SVC</option>
         						<option value="ND SVC">ND SVC</option>
         					</select>
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Type Nota
         				</td>
         				<td colspan="3">
         					<select id="type_nota" class="form-control">
         						<?php
         							foreach ($type_nota as $key => $tn) {
         						?>
         								<option value="<?php echo $tn['type_nota']; ?>"><?php echo $tn['type_nota']; ?></option>
         						<?php
         							}
         						?>
         					</select>
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Kategori Khusus
         				</td>
         				<td colspan="3">
         					<select id="kategori_khusus" class="form-control">
         						<?php
         							foreach ($kategori_khusus as $key => $kk) {
         						?>
         								<option value="<?php echo $kk['Kategori']; ?>"><?php echo $kk['Kategori']; ?></option>
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
         								<option value="<?php echo $pt['partner_type_code']; ?>"><?php echo $pt['partner_type_name']; ?></option>
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
         					<select id="wilayah" class="form-control" onchange="wilayah();">
         						<?php
         							foreach ($wilayah as $key => $w) {
         						?>
         								<option value="<?php echo $w['wilayah']; ?>"><?php echo $w['wilayah']; ?></option>
         						<?php
         							}
         						?>
         					</select>
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Dealer
         				</td>
         				<td colspan="3">
         					<select id="dealer" class="form-control"></select>
         				</td>
         			</tr>
         			<tr>
         				<td colspan="4">
         					<input type="checkbox" id="alamat" value="1"> Beserta Alamat
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

	wilayah();

	function wilayah(){
		var data= 'wilayah='+document.getElementById('wilayah').value;
		document.getElementById('dealer').innerHTML='<option value="">Loading...</option>';
    	console.log(data);
        $.ajax({
			type: 'post',
			url: '<?php echo site_url('Reportnknd/dealer'); ?>',
			data: data,
			success: function (data) {
				var html='';
				var json = JSON.parse(data);

				for (var i = 0; i < json.length; i++) {
					html+='<option value="'+json[i].kd_plg+'">'+json[i].kd_plg+' - '+json[i].nm_plg+'</option>';
				}
				
				document.getElementById('dealer').innerHTML=html;

			}
		});
	}


	function print_data(e){
		var from = document.getElementById('from').value;
		var until = document.getElementById('until').value;
		var type_transaksi = document.getElementById('type_transaksi').value;
		var type_nota = document.getElementById('type_nota').value;
		var kategori_khusus = document.getElementById('kategori_khusus').value;
		var partner_type = document.getElementById('partner_type').value;
		var wilayah = document.getElementById('wilayah').value;
		var dealer = document.getElementById('dealer').value;
		var alamat = document.getElementById('alamat');

		if (alamat.checked == true){
		   	alamat=1;
		}else{
			alamat=0;
		}

		window.open('<?php echo site_url('Reportnknd'); ?>/'+e+'/'+from+'/'+until+'/'+type_transaksi+'/'+type_nota+'/'+kategori_khusus+'/'+partner_type+'/'+wilayah+'/'+dealer+'/'+alamat,'_blank');
	}

	<?php
		if(!empty($_GET['error'])){
	?>
			alert('Data form harus diisi semua!!!');
	<?php
		}
	?>
</script>