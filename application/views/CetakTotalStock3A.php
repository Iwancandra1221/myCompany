<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style type="text/css">
	.row {
	 line-height:30px; 
	 vertical-align:middle;
	 clear:both;
	}
	.row-label, .row-input {
	 float:left;
	}
	.row-label {
	 padding-left: 15px;
	 width:180px;
	}
	.row-input {
	 width:420px;
	}
	.ui-autocomplete {
	overflow-x: hidden;
	max-height: 264px;
	}
</style>

<script>
    $(document).ready(function() {
		$('#dp1').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		});
		$('#dp2').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		});
	});
</script>


<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
  <?php echo form_open($formDest, array("target"=>"_blank")) ?>
	 <div class="form-container" style="height:500px!important;">
		<div class="row">
		  	<div class="col-3">Report</div>
			<div class="col-3">
				<select name="report" id="report" onchange="javascript:changeReport()" class="form-control">
					<option value="A">Stock Total</option>
					<option value="B">Stock Harian</option>
					<option value="C">Stock Detail</option>
				</select>
			</div>
		</div>
		<div class="row lap lapA">
		  	<div class="col-3">Periode</div>
			<div class="col-3">
	          <input type="text" name="tahun" value="<?php echo(date('Y'));?>" style="width:100%;color: black;">
       	</div>
       	<div class="col-5">
	       	<select name="bulan" style="width:100%;" class="form-control">
	            <option value="01" <?php echo((date("m")=="01")?"selected":"")?>>JANUARI</option>
	            <option value="02" <?php echo((date("m")=="02")?"selected":"")?>>FEBRUARI</option>
	            <option value="03" <?php echo((date("m")=="03")?"selected":"")?>>MARET</option>
	            <option value="04" <?php echo((date("m")=="04")?"selected":"")?>>APRIL</option>
	            <option value="05" <?php echo((date("m")=="05")?"selected":"")?>>MEI</option>
	            <option value="06" <?php echo((date("m")=="06")?"selected":"")?>>JUNI</option>
	            <option value="07" <?php echo((date("m")=="07")?"selected":"")?>>JULI</option>
	            <option value="08" <?php echo((date("m")=="08")?"selected":"")?>>AGUSTUS</option>
	            <option value="09" <?php echo((date("m")=="09")?"selected":"")?>>SEPTEMBER</option>
	            <option value="10" <?php echo((date("m")=="10")?"selected":"")?>>OKTOBER</option>
	            <option value="11" <?php echo((date("m")=="11")?"selected":"")?>>NOVEMBER</option>
	            <option value="12" <?php echo((date("m")=="12")?"selected":"")?>>DESEMBER</option>
	            <option value="00">GABUNGAN</option>
	          </select>
	       </div>
		</div>
		<div class="row lap lapA lapB lapC">
			<div class="col-3">Tipe Produk</div>
			<div class="col-3">
				<select name="tipe_produk" id="tipe_produk" onchange="javascript:filterTipeProduk()" class="form-control">
				  <option value='1'>Produk</option>
				  <option value='0'>Sparepart</option>
				</select>
			</div>
		</div>
		
		<div class="row lap lapB">
			<div class="col-3">
				Divisi
			</div>
			<div class="col-3">
				<select name="divisi" id="divisi" class="form-control">
				  <option value='ALL'>ALL</option>
				  <?php 
				  for($i=0;$i<count($divisi);$i++)
				  {
					 echo("<option value='".$divisi[$i]["DIVISI"]."'>".$divisi[$i]["DIVISI"]."</option>");
				  }
				  ?>
				</select>
			</div>
		</div>
		
		
		
		<div class="row lap lapA lapC">
			<div class="col-3">
				Merk
			</div>
			<div class="col-3">
				<select name="merk" id="merk" onchange="javascript:filterMerk()" class="form-control">
				  <option value='ALL'>ALL</option>
				  <?php 
				  for($i=0;$i<count($merk);$i++)
				  {
					 echo("<option value='".$merk[$i]["MERK"]."'>".$merk[$i]["MERK"]."</option>");
				  }
				  ?>
				</select>
			</div>
		</div>
		
		<div class="row lap lapC">
			<div class="col-3">Kode Barang/Sparepart</div>
			<div class="col-6 col-md-5">
	          <input type="text" name="kd_brg" id="kd_brg" style="width:100%;color: black;" placeholder="Ketikkan kode/nama produk/sparepart" disabled>
			</div>
			<div class="col-3"><input type="checkbox" name="chkAllProduk" id="chkAllProduk" value="1" onclick="javascript:AllProduk()" checked> <label for="chkAllProduk"> Semua Kode Barang</label> </div>
		</div>
		
		<div class="row lap lapA lapB lapC">
			<div class="col-3">Gudang</div>
			<div class="col-9 col-md-8">
				<select name="kd_gudang" id="selectGudang" class="form-control" required>
				  <option value='ALL' id="selectGudangALL">ALL</option>
				  <?php 
				  for($i=0;$i<count($gudang);$i++)
				  {
					echo("<option value='".$gudang[$i]["KD_GUDANG"]."'>".$gudang[$i]["KD_GUDANG"]." - ".$gudang[$i]["NM_GUDANG"]."</option>");
				  }
				  ?>
				</select>
			</div>
		</div>
		
		<div class="row lap lapA">
		  <div class="col-3">Grouping</div>
		  <div class="col-9 col-m-8">
				<input type="radio" name="grouping" id="groupingDG" value="gudang" onclick="javascript:group('gudang')" checked> <label for="groupingDG">Per Gudang</label> &nbsp;&nbsp;&nbsp;
				<input type="radio" name="grouping" id="groupingGD" value="cabang" onclick="javascript:group('cabang')"> <label for="groupingGD">Per Cabang</label>
		  </div>
		</div>
		
		<div class="row lap lapC">
			<div class="col-3 col-m-3">Periode</div>
			<div class="col-3 col-m-3">
				<input type="text" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1" autocomplete="off" value="<?php echo date('m/d/Y') ?>">
			</div>
			<div class="col-1 col-m-1" id="divSD">SD</div>
			<div class="col-3 col-m-3" id="divDp2" >
				<input type="text" class="form-control" id="dp2" placeholder="mm/dd/yyyy" name="dp2" autocomplete="off" value="<?php echo date('m/d/Y') ?>">
			</div>
		</div>
		
		
		<div class="row lap lapA">
			<div class="col-3"></div>
			
			<div class="col-9 col-m-8">
				<input type="checkbox" name="chk_semua_gdg" id="chk_semua_gdg" value="1"/>
				<label for="chk_semua_gdg" style="cursor:pointer"> Semua Gudang</label>
				&nbsp;&nbsp;&nbsp;
				<input type="checkbox" name="chk_gudang_aktif" id="chk_gudang_aktif" value="1" checked/>
				<label for="chk_gudang_aktif" style="cursor:pointer"> Hanya Gudang yg Aktif</label>
				&nbsp;&nbsp;&nbsp;
				<input type="checkbox" name="check_in_tanpa_harga" id="check_in_tanpa_harga" value="1" checked/>
				<label for="check_in_tanpa_harga" style="cursor:pointer"> Tanpa harga</label>
				&nbsp;&nbsp;&nbsp;
				<input type="checkbox" name="check_urut_part" id="check_urut_part" value="1"/>
				<label for="check_urut_part" style="cursor:pointer"> Sort by Kd Brg/SP</label>
			</div>
			
		</div>
		
		<div class="row lap lapB">
			<div class="col-3"></div>
			<div class="col-9 col-m-8">
				<input type="checkbox" name="chk_booking_stock_aktif" id="chk_booking_stock_aktif" value="1" checked>
				<label for="chk_booking_stock_aktif" style="cursor:pointer"> Include Booking Stock</label>

				<input type="checkbox" name="chk_hide_stok_0_aktif" id="chk_hide_stok_0_aktif" value="1" checked>
				<label for="chk_hide_stok_0_aktif" style="cursor:pointer"> Hide Stok = 0</label>
			</div>
		</div>
		
		<div class="row">
			<div class="col-3"></div>
			<div class="col-9 col-m-8">
				<input type = "submit" name="submit" value="PREVIEW"/>
				<input type = "submit" name="submit" value="EXCEL"/>
			</div>
		</div>
	 </div>
	<?php echo form_close(); ?>
</div> <!-- /container -->

<script type="text/javascript">
	
	function showDP(){
		$('#dp1').datepicker('show');
	}
	
	
	function changeReport(){
		var lap = $('#report').val();
		$('.lap').hide();
		$('.lap'+lap).show();
		
		if(lap=='C'){
			$('#selectGudangALL').prop('disabled',true);
			// $("#selectGudangALL").prop("selectedIndex",1);
			// $('#selectGudangALL option')[1].selected = true;
			// $("#selectGudangALL option:eq(2)").attr("selected", "selected");
			// $("#selectGudangALL option:(1)");
			
			
			$('#selectGudang').prop('selectedIndex', 1);
			
		}
		else{
			$('#selectGudangALL').prop('disabled',false);
		}
		
		filterMerk();
	}
	
	function filterTipeProduk(){
		var lap = $('#report').val();
		if(lap=='C'){
			loadBarangList();
		}
	}
	
	function filterMerk(){
		var lap = $('#report').val();
		if(lap=='C'){
			loadBarangList();
		}
	}
	
	function AllProduk(){
		var c = document.getElementById("chkAllProduk").checked;
		$('#kd_brg').prop('disabled', c);
		$('#kd_brg').prop('required', !c);
		if(c){
			$('#kd_brg').val('');
		}
		else{
			$('#kd_brg').focus();
		}
	
	}
	
	function group(g){
		if(g=='gudang'){
			$('#div_gudang').show();
			$('#div_cabang').hide();
		}
		else{
			$('#div_cabang').show();
			$('#div_gudang').hide();
		}
	}
	$('#div_cabang').hide();
	
	function loadBarangList(){
		$('#kd_brg').val('');
		$('#chkAllProduk').prop('checked', true);
		$('#kd_brg').prop('disabled', true);
		$('#kd_brg').prop('required', false);
		
		$('.loading').show();
		let tipe_produk  = $('#tipe_produk').val();
		let merk  = $('#merk').val();
		let url = '';
		if(tipe_produk=='1'){
			url = '<?php echo site_url("ReportStock/GetBarangList") ?>';
		}
		else{
			url = '<?php echo site_url("ReportStock/GetSparepartList") ?>';
		}
		console.log(url);
		console.log(merk);
		
		
		$.ajax({ 
			type: 'GET', 
			url: url+'?merk='+merk,
			dataType: 'json',
			success: function (data) {
				console.log(JSON.stringify(data));
			
				$('.loading').hide();
				$("#kd_brg").autocomplete({
					source: data
				});			
			}
		});
	}
	  
	changeReport();
	  
 
</script>