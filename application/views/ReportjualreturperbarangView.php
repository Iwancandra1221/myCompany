<style type="text/css">
table, tr ,td, th{
	padding: 10px;
}
</style>
<div class="container">
	<div class="page-title">
		REPORT JUAL RETUR PERBARANG
	</div>
	<div class="form-container" >  

  		<div class="row">
        	<div class="col-12" align="center">
         		<table border="0" width="80%">
         			<tr>
         				<td width="200px">
         					<input type="checkbox" id="chk_pergroup" value="1" onclick="chkitemfocus()"> PerGroup Item Fokus
         				</td>
         				<td colspan="3">
         					<select id="pergroup_item_fokus_select" class="form-control" onchange="isiitemfocus()" disabled>
         						<?php
								  	foreach ($pergroup_item_fokus as $key => $pg) {
								?>
										<option value="<?php echo rtrim($pg['ProductGroup']); ?>"><?php echo $pg['ProductGroup']; ?></option>
								<?php
								  	}
								?>
         					</select>
         					<input type="hidden" id="pergroup_item_fokus">
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Periode
         				</td>
         				<td>
         					<input type="text" id="from" class="form-control" value="<?php echo date('Y-m-d'); ?>">
         				</td>
         				<td align="center" width="100px">
         					S/D
         				</td>
         				<td>
         					<input type="text" id="until" class="form-control" value="<?php echo date('Y-m-d'); ?>">
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Partner Type
         				</td>
         				<td>
         					<select id="partner_type" class="form-control">
							    <option value="TRADISIONAL">TRADISIONAL</option>
							    <option value="MODERN OUTLET">MODERN OUTLET</option>
							    <option value="MO CABANG">MO CABANG</option>
							    <option value="PROYEK">PROYEK</option>
							    <option value="COUNTER">COUNTER</option>
         					</select>
         				</td>
         				<td>Tipe Faktur</td>
         				<td>
         					<select id="tipe_faktur" class="form-control">
         						<?php
								  	foreach ($tipe_faktur as $key => $ts) {
								?>
										<option value="<?php echo rtrim($ts['Tipe_Faktur']); ?>" <?php if(rtrim($ts['Tipe_Faktur'])=='R'){ echo 'selected'; } ?>><?php echo rtrim($ts['Tipe_Faktur']); ?></option>
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
         					<select id="wilayah" class="form-control" onclick="wilayah();">
         						<?php
								  	foreach ($wilayah as $key => $w) {
								?>
										<option value="<?php echo rtrim($w['wilayah']); ?>"><?php echo $w['wilayah']; ?></option>
								<?php
								  	}
								?>
         					</select>
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Parentdiv
         				</td>
         				<td colspan="3">
         					<select id="parentdiv" class="form-control">
         						<?php
								  	foreach ($ParentDiv as $key => $pd) {
								?>
										<option value="<?php echo rtrim($pd['ParentDiv']); ?>"><?php echo $pd['ParentDiv']; ?></option>
								<?php
								  	}
								?>
         					</select>
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Divisi
         				</td>
         				<td colspan="3">
         					<select id="divisi" class="form-control">
         						<?php
								  	foreach ($Divisi as $key => $g) {
								?>
								<option value="<?php echo rtrim($g['Kd_Divisi']); ?>"><?php echo $g['Nama_Divisi']; ?></option>
								<?php
								  	}
								?>
         					</select>
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Lokal/Import
         				</td>
         				<td colspan="3">
         					<select id="local_import" class="form-control">
         						<option value="ALL">ALL</option>
								<option value="LOKAL">LOKAL</option>
								<option value="IMPORT">IMPORT</option>
         					</select>
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Produk
         				</td>
         				<td>
         					<select id="produk" class="form-control">
         						<option value="ALL">ALL</option>
								<option value="PRODUCT">Produk</option>
								<option value="SPAREPART">Sparepart</option>
         					</select>
         				</td>
         			</tr>
         			<tr>
         				<td>
         					<input type="checkbox" id="chk_dealer" value="1" onclick="chkdealer()"> Dealer
         				</td>
         				<td colspan="3">
         					<select id="dealer_select" class="form-control" onchange="isidealer()" disabled>
         						<option value="ALL">ALL</option>
         					</select>
         					<input type="hidden" id="dealer" value="ALL">
         				</td>
         			</tr>
         			<tr>
         				<td>
         					Print Out by
         				</td>
         				<td>
         					<input type="radio" id="print_out_by615" name="print_out_by" value="615" checked> JUAL - RETUR per Jenis Barang<br>
         					<input type="radio" id="print_out_by616" name="print_out_by" value="616"> JUAL - RETUR per Kode Barang
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
	$(document).ready(function() {
		<?php
			if($error=='error'){
		?>
				alert('Data tidak dapat ditemukan!!!');
		<?php
			}
		?>

		$('#from,#until').datepicker({
			format: "yyyy-mm-dd",
			autoclose: true
		});
	});

	wilayah();
	function wilayah(){
		document.getElementById('dealer_select').innerHTML='<option value="ALL">Loading...</option>'
		var data  = 'wilayah='+document.getElementById('wilayah').value;
		console.log(data);
		$.ajax({
			type 	: 'POST',	
			url 	: '<?php echo site_url('Reportjualreturperbarang/Dealer'); ?>', 
			data  	: data,
			success : function(data) {
				response = JSON.parse(data);
				var option='';
				if(response.length>1) {
					for(i = 0; i < response.length; i++){		
						option += '<option value="'+response[i].kd_plg+'">'+response[i].nm_plg+'</option>';
					}
					document.getElementById('dealer_select').innerHTML=option;
				}else{
					document.getElementById('dealer_select').innerHTML='<option value="ALL">Data tidak ditemukan dengan dealer di wilayah '+document.getElementById('wilayah').value+'</option>';
				}
				return false
			}
		})
	}

	function isidealer(){
		var data = document.getElementById("dealer_select").value;
		document.getElementById("dealer").value=data;
	}

	function isiitemfocus(){
		var data = document.getElementById("pergroup_item_fokus_select").value;
		document.getElementById("pergroup_item_fokus").value=data;
	}

	

	function print_data(e){


		var pergroup_chk = 'N';
		if(document.getElementById('chk_pergroup').checked) {
			pergroup_chk = 'Y';
		}

		var pergroup_item_fokus = document.getElementById('pergroup_item_fokus').value;
		var from = document.getElementById('from').value;
		var until = document.getElementById('until').value;
		var partner_type = document.getElementById('partner_type').value;
		var tipe_faktur = document.getElementById('tipe_faktur').value;
		var wilayah = document.getElementById('wilayah').value;
		var parentdiv = document.getElementById('parentdiv').value;
		var divisi = document.getElementById('divisi').value;
		var local_import = document.getElementById('local_import').value;
		var produk = document.getElementById('produk').value;
		var dealer = document.getElementById('dealer').value;

		var print_out_by = '';
		if(document.getElementById('print_out_by615').checked) {
			print_out_by = '615';
		}else{
			print_out_by = '616';
		}

		var dealer_chk = 'N';
		if(document.getElementById('chk_dealer').checked) {
			dealer_chk = 'Y';
		}

		window.open('<?php echo site_url('Reportjualreturperbarang'); ?>/'+e+'/'+print_out_by+'/?pergroup_item_fokus='+pergroup_item_fokus+'&from='+from+'&until='+until+'&partner_type='+partner_type+'&tipe_faktur='+tipe_faktur+'&wilayah='+wilayah+'&parentdiv='+parentdiv+'&divisi='+divisi+'&local_import='+local_import+'&produk='+produk+'&dealer='+dealer+'&dealer_chk='+dealer_chk+'&pergroup_chk='+pergroup_chk,'_blank');

	}


	function chkdealer(){
		wilayah();
		if(document.getElementById('chk_dealer').checked) {
			document.getElementById("dealer_select").disabled=false;
			document.getElementById("dealer").value='';
		}else{
			document.getElementById("dealer_select").disabled=true;
			document.getElementById("dealer").value='';
		}
	}


	function chkitemfocus(){
		if(document.getElementById('chk_pergroup').checked) {
			document.getElementById("pergroup_item_fokus_select").disabled=false;
			var data = document.getElementById("pergroup_item_fokus_select").value;
			document.getElementById("pergroup_item_fokus").value=data;
		}else{
			document.getElementById("pergroup_item_fokus_select").disabled=true;
			document.getElementById("pergroup_item_fokus").value='';
		}
	}
</script>

