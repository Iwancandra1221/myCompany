<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
  <?php echo form_open($formDest, array("target"=>"_blank")) ?>
	<div class="form-container"> 
		<div class="row">
         <div class="col-3"> 
         	Kategori
         </div>
         <div class="col-9 col-md-8">
            <input type="radio" name="kategori" id="radio-produk" value="produk" checked>  
				<label>Produk</label> &nbsp;&nbsp;&nbsp;
            <input type="radio" name="kategori" id='radio-sparepart' value="sparepart" > 
            <label>Sparepart</label> &nbsp;&nbsp;&nbsp; 
         </div>     
      </div> 
      <div class="row" id="div_partner_type">
      	 <div class="col-3"> 
         </div>
			<div class="col-8 col-m-8 date">
				<select class="form-control" name="group" novalidate> 
					<option value="jenis_barang">GROUP BERDASARKAN JENIS BARANG</option>
					<option value="subkategori_barang">GROUP BERDASARKAN SUBKATEGORI BARANG</option>
				</select>
			</div>
		</div>  
		<div class="row" id="parent-jns">
			<div class="col-3">Jns Barang</div>
			<div class="col-8 col-m-8 date"> 
         	<select class="form-control" name="jns_brg" novalidate> 
					<?php
					echo '<option value="">ALL</option>';
					foreach($jnsBrg as $value){
						echo '<option value="'.$value['Jns_Brg'].'">'.$value['Jns_Brg'].'</option>';
					}
					?>
				</select>
			</div>
		</div>   
	 	<div class="row" id="div_semua_wilayah">
			<div class="col-3">Merk</div>
			<div class="col-8 col-m-8 date"> 
         	<select class="form-control" name="merk" novalidate> 
					<?php
					echo '<option value="">ALL</option>';
					foreach($merk as $value){
						echo '<option value="'.$value['Merk'].'">'.$value['Merk'].'</option>';
					}
					?>
				</select>
			</div>
		</div>   
		<div class="row" id="parent-divisi">
			<div class="col-3">Divisi</div>
			<div class="col-8 col-m-8 date"> 
         	<select class="form-control" name="divisi" novalidate> 
					<?php
					echo '<option value="">ALL</option>';
					foreach($divisi as $key => $value){
						if($key>=1){
							echo '<option value="'.trim($value['Kd_Divisi'],' ').'">'.$value['Nama_Divisi'].'</option>';
						}
						
					}
					?>
				</select>
			</div>
		</div>   
      
  		<div class="row">
         <div class="col-3">AKTIF</div>
         <div class="col-9 col-md-8">
            <input type="radio" name="status" id="radio_aktif" value="1" checked>  
				<label for="radio_aktif">Aktif</label> &nbsp;&nbsp;&nbsp;
            <input type="radio" name="status" id="radio_non_aktif" value="0" > 
            <label for="radio_non_aktif">Tidak Aktif</label> &nbsp;&nbsp;&nbsp; 
            <input type="radio" name="status" id="radio_all" value="" > 
            <label for="radio_all">ALL</label> 
         </div>     
      </div> 
     	<div class="row">
         <div class="col-3">Tanggal</div>
         <div class="col-9 col-md-8">
            <input style="color:black;" type="text" name="tgl" id="tgl" value="">  
         </div>     
      </div> 
      <div class="row" align="center" style="padding-top:50px;" id="div_excel"> 
      	<input type = "submit" name="submit" value="EXPORT EXCEL"/>
         <input type = "submit" name="submit" value="EXPORT PDF"/> 
      </div> 
    </div>
	<?php echo form_close(); ?>
</div>  
<script type="text/javascript"> 
	$(document).ready(function(){
		$('#tgl').datepicker({
        format: "mm/dd/yyyy",
        autoclose: true
      });
      $("#parent-divisi").css({"display":"none"});
      $("#parent-jns").css({"display":"none"});
      
      if($("#radio-produk").prop("checked")){
      	$("#parent-divisi").css({"display":"block"});
      	$("#parent-jns").css({"display":"block"});
      }
      $("#radio-produk").click(function(){
      	$("#parent-divisi").css({"display":"block"});
      	$("#parent-jns").css({"display":"block"});
      });
      $("#radio-sparepart").click(function(){
      	$("#parent-divisi").css({"display":"none"});
      	$("#parent-jns").css({"display":"none"});
      });
	});
</script>
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
</style> 
	 
	  
	  
	  
 
 


