<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
  <?php echo form_open($formDest, array("target"=>"_blank")) ?>
	<div class="form-container" >  

  		<div class="row">
         <div class="col-3"> Cetak Laporan
         </div>  
         <div class="col-8 col-m-8"> 
	         <select  class="form-control" name="cetaklaporan" id="cetaklaporan"  novalidate >   
	         	 <option value="rp1" selected>A. Analisa Pembelian Per Kode Barang</option> 
	         	 <option value="rp2" >B. Pembelian Per Nomor Purchase</option> 
	         	 <option value="rp3" >C. Summary Pembelian Per Gudang</option> 
	         	 <option value="rp4" >D. Analisa Pembelian Per Kode Barang Exclude PPN</option> 
				</select>
			</div> 
      </div> 

		 <div class="row">
			<div class="col-3 ">Periode</div>
			<div class="col-3 col-m-3 date">
				<input type="text" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1" autocomplete="off" required  >
			</div>
			<div class="col-1 ">SD</div>
			<div class="col-3 col-m-3 date">
				<input type="text" class="form-control" id="dp2" placeholder="mm/dd/yyyy" name="dp2" autocomplete="off" required  >
			</div>
		</div>
   
  		<div class="row">
         <div class="col-3"> Supplier
         </div>
         <div class="col-8 col-md-8">
            <input type="radio" name="radioSupplier" id="Lokal" value="" onclick="SupplierClick('')"  checked >  
				<label for="Lokal">Lokal</label> &nbsp;&nbsp;&nbsp;
            <input type="radio" name="radioSupplier" id="Import" value="IMPORT" onclick="SupplierClick('IMPORT')" > 
            <label for="Import">Import</label> &nbsp;&nbsp;&nbsp;  
         </div>     
      </div> 

		<div class="row" >
			<div class="col-3"></div>
			<div class="col-8 col-m-8 date">
				<select  class="form-control" name="supplier" id="supplier"  novalidate >  
					<?php 
						foreach($listsupplier as $s)
						{
							if ($s->Kode_Supplier=='ALL')
							{
								echo("<option value='".$s->Kode_Supplier."' selected>".$s->Nama_Supplier."</option>");
							}
							else
							{
								echo("<option value='".$s->Kode_Supplier."'>".$s->Nama_Supplier."</option>");
							}
						}			  
					?>
				</select>
			</div>
	  </div>  

  		<div class="row">
         <div class="col-3"> Kategori Barang
         </div>
         <div class="col-8 col-md-8">
            <input type="radio" name="Kategori_Barang" id="Product" value="P" onclick="KategoriClick(0)"  checked >  
				<label for="Product">Product</label> &nbsp;&nbsp;&nbsp;
            <input type="radio" name="Kategori_Barang" id="Sparepart" value="S" onclick="KategoriClick(1)"> 
            <label for="Sparepart">Sparepart</label> &nbsp;&nbsp;&nbsp;  
         </div>     
      </div> 

      <div class="row" >
			<div class="col-3"> Kode Barang</div>
			<div class="col-5 col-m-5"> 
				<input type="text" class="form-control" name="kdbrg" id="kdbrg" placeholder="Kode Barang" readonly>
			</div>
			<div class="col-1 col-m-1" id="div_search_barang"> 
         	<input type = "button"  class="button" name="btnsearch_kdbrg" id="btnsearch_kdbrg" value="Search" onclick="btnsearchbrgClick()" > 
			</div>
			<div class="col-1 col-m-1" id="div_search_sparepart"> 
         	<input type = "button"  class="button" name="btnsearch_kdbrg" id="btnsearch_kdbrg" value="Search" onclick="btnsearchsparepartClick()" > 
			</div>
			<div class="col-2 col-m-2"> 
         	<input type="checkbox" id="chk_semuabrg" name="chk_semuabrg" checked  onclick="handleClick(this)"> 
         	<label for="chk_semuabrg">Semua Barang</label> 
			</div>
	  </div>  

      <div class="row" >
			<div class="col-3"></div>
			<div class="col-8 col-m-8"> 
				<input type="text" class="form-control" style="background-color:lightgoldenrodyellow;" name="nmbrg" id="nmbrg" placeholder="Nama Barang" readonly>
			</div>
	  </div>    

	   <div class="row">
         <div class="col-3"> Gudang
         </div>    
      </div> 

		<div id="table-wrapper">
		  <div id="table-scroll">
				<table id="tablegudang" class="table table-bordered"style='background-color:white;' cellspacing="0" width="100%"> 
				<thead> 
				<tr>      
					<th scope="col" style="width:7%" >
						<input type="checkbox" id="chk_semuagudang" name="chk_semuagudang" onclick="handleClickGudang(this)"> ALL 
         		</th> 
					<th scope="col" style="width:5%" class="no-sort">No</th> 
					<th scope="col" style="width:20%">Kode Gudang</th>
					<th scope="col" style="width:68%">Gudang</th>
				</tr> 
				</thead> 
					<?php
					echo "<tbody>";
					$i = 1;
					foreach($listgudang as $r) {
						echo "<tr >"; 
						echo "<td style='color:black;'><input type='checkbox' ></td>";
						echo "<td style='color:black;'> ".$i." </td>";
						echo "<td style='color:black;'>".$r->Kode_Gudang."<input type='hidden' class='form-control' id='asd".$i."' value='".$r->Kode_Gudang."'> </td>";
						echo "<td style='color:black;'>".$r->Nama_Gudang."</td>";  
						echo "</tr>";   
						$i += 1;
					}
				echo "</tbody>"; ?>
				</table> 
		  	</div>
		</div>
 
		<div style="color:black;" id="formSearchBarang" class="modal fade" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-lg" role="document" style="width:80%;">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Master Kode Barang</h4>
					</div>
					<form class="form-horizontal"> 
					<div class="modal-body">
						<div id="table-wrapper">
						  <div id="table-scroll2">
								<table id="table_barang" class="table table-bordered"style='background-color:white;' cellspacing="0" width="100%"> 
								<thead> 
								<tr>      
									<th scope="col" style="width:5%" ></th>  
									<th scope="col" style="width:20%">Kode Barang</th>
									<th scope="col" style="width:40%">Nama Barang</th>
									<th scope="col" style="width:15%">Jenis Barang</th>
									<th scope="col" style="width:15%">Merk</th>
									<th scope="col" style="width:5%">Aktif</th>
								</tr> 
								</thead> 
								</table> 
						  	</div>
						</div>
					</div>
 
					<div class="modal-footer">
						<input type="button" class="btn" value="Close" data-dismiss="modal">
					</div>
					</form>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div> 

		<div style="color:black;" id="formSearchSparepart" class="modal fade" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-lg" role="document" style="width:100%;">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Master Kode Sparepart</h4>
					</div>
					<form class="form-horizontal">
						<div class="modal-body">

						<div id="table-wrapper">
						  <div id="table-scroll2">
								<table id="table_sparepart" class="table table-bordered"style='background-color:white;' cellspacing="0" width="100%"> 
								<thead> 
								<tr>      
									<th scope="col" style="width:5%" ></th>  
									<th scope="col" style="width:10%">Kode Sparepart</th>
									<th scope="col" style="width:40%">Nama Sparepart</th>
									<th scope="col" style="width:15%">Jenis Sparepart</th>
									<th scope="col" style="width:15%">Merk</th>
									<th scope="col" style="width:15%">Aktif</th>
								</tr> 
								</thead>  

								</table> 
						  	</div>
						</div>

						</div>
						<div class="modal-footer">
							<input type="button" class="btn" value="Close" data-dismiss="modal">
						</div>
					</form>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div>
  
      <div class="row" align="center" style="padding-top:50px;" id="div_excel"> 
         <input type = "submit" name="btnExcel" style="background-color:lightgreen;" onclick="excelClick()" value="CETAK LAYAR"/> 
      	<input type = "hidden" name="list_kode_gudang" id="list_kode_gudang" /> 
      </div> 

    </div>
	<?php echo form_close(); ?>
</div>  
<script>

    $(document).ready(function() { 

		// $('#table_barang').dataTable({searching: true, paging: true, info: true, order: true});
		$('#table_sparepart').dataTable({searching: true, paging: true, info: true, order: true});

			$('#div_search_barang').show();
			$('#div_search_sparepart').hide();  
		$("#btnsearch_kdbrg").attr('disabled', true);   
		$('#dp1').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var StartDt = $('#dp1').datepicker('getDate');
			$('#dp2').datepicker("setStartDate", StartDt);
		});
		
		$('#dp2').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var EndDt = $('#dp2').datepicker('getDate');
			$('#dp1').datepicker("setEndDate", EndDt);
		});
		
		
		
		$('#table_barang').dataTable({
			"pageLength": 5,
			"lengthMenu": [
			[5, 10, 20, 50, 100, -1],
			[5, 10, 20, 50, 100, "All"]
			],
			"processing": true,
			"serverSide": true,
			"autoWidth": false,
			"ajax": "<?php echo site_url("LaporanPembelian/GetListBarang") ?>",
			"language": {
				"lengthMenu": "Menampilkan _MENU_ Data per halaman",
				"zeroRecords": "Maaf, Data tidak ada",
				"info": "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
				"infoEmpty": "Menampilkan 0 s/d 0 dari 0 data",
				"search": "Pencarian",
				"infoFiltered": "",
				"paginate": {
					"sPrevious": "Sebelumnya",
					"sNext": "Berikutnya"
				}
			},
		});
		
		
	} );
</script>
<script type="text/javascript">  
	function SupplierClick(status) {   
		$('.loading').show();
		$('#supplier').empty();  
		$.ajax({ 
			type: 'GET',
			url: '<?php echo site_url("LaporanPembelian/GetListSupplier?status='+status+'") ?>',
			dataType: 'json',
			success: function (data){
				if(data.length>0){  
					for (var i = 0; i < data.length; i++) { 
						$("#supplier").append(new Option(data[i].Nama_Supplier, data[i].Kode_Supplier)); 
					} 
				} 
				$('.loading').hide();
			}
		}); 
	} 
	function KategoriClick(status) {  
		if (status==0)  
		{ 
			$('#div_search_barang').show();
			$('#div_search_sparepart').hide();
			$("#kdbrg").attr("placeholder", "Kode Barang");
			$("#nmbrg").attr("placeholder", "Nama Barang");
		}
		else
		{  
			$('#div_search_barang').hide();
			$('#div_search_sparepart').show();
			$("#kdbrg").attr("placeholder", "Kode Sparepart");
			$("#nmbrg").attr("placeholder", "Nama Sparepart");
		}
	} 
	function btnsearchbrgClick() {
		$('#formSearchBarang').modal('show'); 
	} 

	function btnsearchsparepartClick() { 
		var table = $('#table_sparepart').DataTable();
 		table.clear().draw();
 		table.destroy(); 

		$('.loading').show();
		$.ajax({ 
			type: 'GET',
			url: '<?php echo site_url("LaporanPembelian/GetListSparepart?") ?>',
			dataType: 'json',
			success: function (data){
				if(data.length>0){  
					for (var i = 0; i < data.length; i++) { 
						$('#table_sparepart').DataTable().row.add([ 
									  '<input type="button" value="Select" onclick="checkboxClick('+"'"+data[i].Kode_Spare_Part+"'"+','+"'"+data[i].Nama_Spare_Part+"'"+')">' , 
									  data[i].Kode_Spare_Part,
									  data[i].Nama_Spare_Part,
									  data[i].Jenis_Spare_Part,
									  data[i].Merk,
									  data[i].AKTIF,
									]).draw(); 
					} 
					$('#formSearchSparepart').modal('show');
				} 
				$('.loading').hide(); 
			}
		});   
		//$('#formSearchSparepart').modal('show');
	} 

	function handleClick(cb) {    
		$("#btnsearch_kdbrg").attr('disabled', cb.checked); 
		if (cb.checked) 
		{
			$("#kdbrg").val("");    
			$("#nmbrg").val(""); 
		}
	}  

	function handleClickGudang(cb) {   
		$('#tablegudang tbody tr td input[type="checkbox"]').each(function()
		{
			$(this).prop('checked', cb.checked);
		}); 

	} 

	function excelClick() {    
		var rowCount = $('#tablegudang tr').length;  
		var listgudang = "";
 
		for (var i = 0; i < rowCount; i++) { 
			if ($("#tablegudang input:checkbox")[i].checked)
			{   
				if (i>0)
				{
	 				if (listgudang=="")
					{	
						listgudang += "'";
						listgudang += $('#asd'+i).val();
						listgudang += "'";
					}
					else
					{
						listgudang += ",'" + $('#asd'+i).val();
						listgudang += "'";
					}  
				}
			} 
		} 
		$("#list_kode_gudang").val(listgudang);
	} 


	function checkboxClick(kd,nm) {    
		$("#kdbrg").val(kd);    
		$("#nmbrg").val(nm);
		$('#formSearchBarang').modal('hide');
		$('#formSearchSparepart').modal('hide');
	} 

</script>
<style type="text/css">
	.button {background-color: white; color: black;} 

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

	#table-wrapper {
	  position:relative;
	}
	#table-scroll {
	  height:300px;
	  overflow:auto;  
	  margin-top:20px;
	}
	#table-scroll2 {
	  height:100%;
	  overflow:auto;  
	  margin-top:20px;
	}
	#table-wrapper table {
	  width:100%;

	}
	#table-wrapper table * {
	  background:white;
	  color:black;
	}
	#table-wrapper table thead th .text {
	  position:absolute;   
	  top:0px;
	  z-index:2;
	  height:20px;
	  width:35%;
	  border:1px solid red;
	}
</style> 
	 
	  
	  
	  
 
 


